<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db.php';

function validate($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    if ($type === 'number' && !is_numeric($data)) {
        return false;
    }
    return $data;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if (isset($_GET['id'])) {
        $id = validate($_GET['id'], 'number');
        if (!$id) {
            echo json_encode(["error" => "Неверный ID"]);
            exit;
        }
        $result = $conn->query("SELECT * FROM products WHERE id = $id");
        echo json_encode($result->fetch_assoc());
    } else {
        $result = $conn->query("SELECT * FROM products");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
}

elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $name = validate($data['name'] ?? '');
    $price = validate($data['price'] ?? '', 'number');
    $description = validate($data['description'] ?? '');

    if (!$name || !$price) {
        echo json_encode(["error" => "Некорректные данные"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $price, $description);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Товар добавлен", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["error" => "Ошибка добавления"]);
    }
}

elseif ($method == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = validate($data['id'] ?? '', 'number');
    $name = validate($data['name'] ?? '');
    $price = validate($data['price'] ?? '', 'number');
    $description = validate($data['description'] ?? '');

    if (!$id || !$name || !$price) {
        echo json_encode(["error" => "Некорректные данные"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=? WHERE id=?");
    $stmt->bind_param("sdsi", $name, $price, $description, $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Товар обновлен"]);
    } else {
        echo json_encode(["error" => "Ошибка обновления"]);
    }
}

elseif ($method == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = validate($data['id'] ?? '', 'number');

    if (!$id) {
        echo json_encode(["error" => "Некорректный ID"]);
        exit;
    }

    if ($conn->query("DELETE FROM products WHERE id = $id")) {
        echo json_encode(["message" => "Товар удален"]);
    } else {
        echo json_encode(["error" => "Ошибка удаления"]);
    }
}

$conn->close();
?>