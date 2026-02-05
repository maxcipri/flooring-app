<?php
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: '';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

if ($host === '' || $user === '' || $db === '') {
    http_response_code(500);
    echo "Database not configured. Set MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT in Render.";
    exit;
}

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    http_response_code(500);
    echo "DB connection failed: " . mysqli_connect_error();
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');
