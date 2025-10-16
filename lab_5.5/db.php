<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "test_api";

// JWT secret (замените на свой длинный случайный ключ!)
$JWT_SECRET = "random_secret_key_32_chars_min";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Ошибка подключения к БД: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
?>