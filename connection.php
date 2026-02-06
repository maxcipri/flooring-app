<?php
// PostgreSQL connection for Render
$host = "dpg-d62tf60nputs73eneue0-a";
$port = "5432";
$database = "flooring_app";
$username = "flooring_app_user";
$password = "zvpHmpT45Cv3FJPt5Kr4ec9QFpPNBAjg";

// Create PostgreSQL connection
$conn_string = "host=$host port=$port dbname=$database user=$username password=$password sslmode=require";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("PostgreSQL connection failed: " . pg_last_error());
}

// Function to convert PostgreSQL results to MySQL-style for compatibility
function mysqli_query($conn, $query) {
    return pg_query($conn, $query);
}

function mysqli_num_rows($result) {
    return pg_num_rows($result);
}

function mysqli_fetch_assoc($result) {
    return pg_fetch_assoc($result);
}

function mysqli_real_escape_string($conn, $string) {
    return pg_escape_string($conn, $string);
}

function mysqli_error($conn) {
    return pg_last_error($conn);
}
?>
