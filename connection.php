<?php
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'flooring_app';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: 'flooring123';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'flooring_App';
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    http_response_code(500);
    echo "DB connection failed: " . mysqli_connect_error();
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');
