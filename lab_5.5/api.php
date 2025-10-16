<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'db.php';

/** ======================= Утилиты валидации ======================= */
function validate($data, $type = 'string') {
    if ($data === null) { return false; }
    if (is_string($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    if ($type === 'number') {
        if ($data === '' || !is_numeric($data)) { return false; }
        return 0 + $data;
    }
    return $data;
}

/** ======================= JWT (HS256) ======================= */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode($payload, $secret, $expSeconds = 3600) {
    $header = ['typ'=>'JWT', 'alg'=>'HS256'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $expSeconds;
    $segments = [
        base64url_encode(json_encode($header, JSON_UNESCAPED_UNICODE)),
        base64url_encode(json_encode($payload, JSON_UNESCAPED_UNICODE))
    ];
    $signing_input = implode('.', $segments);
    $signature = hash_hmac('sha256', $signing_input, $secret, true);
    $segments[] = base64url_encode($signature);
    return implode('.', $segments);
}

function jwt_decode($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) { return [false, "Неверный формат токена"]; }
    list($headb64, $payloadb64, $sigb64) = $parts;
    $signing_input = $headb64 . '.' . $payloadb64;
    $signature = base64url_decode($sigb64);
    $expected = hash_hmac('sha256', $signing_input, $secret, true);
    if (!hash_equals($expected, $signature)) {
        return [false, "Неверная подпись токена"];
    }
    $payload_json = base64url_decode($payloadb64);
    $payload = json_decode($payload_json, true);
    if (!$payload) { return [false, "Невалидная полезная нагрузка"]; }
    if (!isset($payload['exp']) || time() >= $payload['exp']) {
        return [false, "Срок действия токена истек"];
    }
    return [true, $payload];
}

function get_bearer_token() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if ($auth && stripos($auth, 'Bearer ') === 0) {
        return trim(substr($auth, 7));
    }
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }
    }
    return null;
}

function require_admin($secret) {
    $token = get_bearer_token();
    if (!$token) {
        http_response_code(401);
        echo json_encode(["error" => "Требуется токен авторизации (Bearer)"]);
        exit;
    }
    list($ok, $data) = jwt_decode($token, $secret);
    if (!$ok || ($data['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Недостаточно прав или неверный токен"]);
        exit;
    }
    return $data;
}

/** ======================= Роутинг ======================= */
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$body = json_decode(file_get_contents("php://input"), true) ?? [];

$script = $_SERVER['SCRIPT_NAME'];
$base = rtrim(dirname($script), '/');
if (strpos($path, $script) === 0) {
    $path = substr($path, strlen($script));
} elseif ($base && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
$path = '/' . ltrim($path, '/');

// ======================= Админ: регистрация и вход =======================
// POST /admin/register
if ($method === 'POST' && $path === '/admin/register') {
    $username = validate($body['username'] ?? '');
    $password = $body['password'] ?? '';
    if (!$username || !$password || strlen($password) < 6) {
        http_response_code(422);
        echo json_encode(["error" => "Укажите username и password (мин. 6 символов)"]);
        exit;
    }
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Пользователь уже существует"]);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hash);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Администратор зарегистрирован"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Ошибка регистрации"]);
    }
    exit;
}

// POST /admin/login
if ($method === 'POST' && $path === '/admin/login') {
    global $JWT_SECRET;
    $username = validate($body['username'] ?? '');
    $password = $body['password'] ?? '';
    if (!$username || !$password) {
        http_response_code(422);
        echo json_encode(["error" => "Укажите username и password"]);
        exit;
    }
    $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row || !password_verify($password, $row['password_hash'])) {
        http_response_code(401);
        echo json_encode(["error" => "Неверные учетные данные"]);
        exit;
    }
    $token = jwt_encode(['sub'=>$row['id'], 'username'=>$username, 'role'=>'admin'], $JWT_SECRET, 3600);
    echo json_encode(["token" => $token, "token_type" => "Bearer", "expires_in" => 3600]);
    exit;
}

// ======================= Products CRUD =======================
// READ (public): GET /products or GET /products/{id}
if ($method === 'GET' && preg_match('#^/products(?:/(\d+))?$#', $path, $m)) {
    if (!empty($m[1])) {
        $id = validate($m[1], 'number');
        if (!$id) { http_response_code(400); echo json_encode(["error"=>"Неверный ID"]); exit; }
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) { http_response_code(404); echo json_encode(["error"=>"Не найдено"]); exit; }
        echo json_encode($row);
    } else {
        $res = $conn->query("SELECT * FROM products ORDER BY id ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    }
    exit;
}

// CREATE (admin): POST /products
if ($method === 'POST' && $path === '/products') {
    global $JWT_SECRET;
    require_admin($JWT_SECRET);

    $name = validate($body['name'] ?? '');
    $price = validate($body['price'] ?? '', 'number');
    $description = validate($body['description'] ?? '');

    if (!$name || $price === false) {
        http_response_code(422);
        echo json_encode(["error" => "Некорректные данные"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $price, $description);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Товар добавлен", "id" => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Ошибка добавления"]);
    }
    exit;
}

// UPDATE (admin): PUT /products/{id}
if ($method === 'PUT' && preg_match('#^/products/(\d+)$#', $path, $m)) {
    global $JWT_SECRET;
    require_admin($JWT_SECRET);

    $id = validate($m[1], 'number');
    $name = validate($body['name'] ?? '');
    $price = validate($body['price'] ?? '', 'number');
    $description = validate($body['description'] ?? '');

    if (!$id || !$name || $price === false) {
        http_response_code(422);
        echo json_encode(["error" => "Некорректные данные"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=? WHERE id=?");
    $stmt->bind_param("sdsi", $name, $price, $description, $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Товар обновлен"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Ошибка обновления"]);
    }
    exit;
}

// DELETE (admin): DELETE /products/{id}
if ($method === 'DELETE' && preg_match('#^/products/(\d+)$#', $path, $m)) {
    global $JWT_SECRET;
    require_admin($JWT_SECRET);

    $id = validate($m[1], 'number');
    if (!$id) { http_response_code(422); echo json_encode(["error"=>"Некорректный ID"]); exit; }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Товар удален"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Ошибка удаления"]);
    }
    exit;
}

// Not found
http_response_code(404);
echo json_encode(["error" => "Маршрут не найден", "method"=>$method, "path"=>$path]);
?>