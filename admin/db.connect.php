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

// Encryption key - In a production environment, store this in a secure config file or environment variable
define('ENCRYPTION_KEY', 'your_secure_32_byte_key_here_123456789'); // 32 bytes for AES-256
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encrypt_data($data) {
    if (empty($data)) return $data;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt_data($encrypted_data) {
    if (empty($encrypted_data)) return $encrypted_data;
    $data = base64_decode($encrypted_data);
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?>