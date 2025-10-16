<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "test_api";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Ошибка подключения к БД: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
?>