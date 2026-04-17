<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "root";
$password = "";
$database = "j3rs_db";
$port = 3306;

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
} catch (mysqli_sql_exception $e) {
    try {
        // Fallback to IPv4 literal
        $conn = new mysqli("127.0.0.1", $username, $password, $database, $port);
    } catch (mysqli_sql_exception $e2) {
        http_response_code(500);
        die("Database connection failed: " . $e2->getMessage());
    }
}
?>