<?php
// Safe DB connector: if DB env vars are missing, do NOT fatal.
// App can run in "no DB" mode temporarily.

$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: '';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

$conn = null;

if ($host !== '' && $user !== '' && $db !== '') {
    $conn = @mysqli_connect($host, $user, $pass, $db, $port);
    if ($conn) {
        mysqli_set_charset($conn, 'utf8mb4');
    } else {
        // Keep app running even if DB connect fails
        $conn = null;
    }
}
