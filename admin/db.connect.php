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

// Encryption key - Must be exactly 32 bytes for AES-256-CBC
// Use a proper 32-byte key (you can generate one via: echo bin2hex(random_bytes(32));)
define('ENCRYPTION_KEY', 'my_secure_32_byte_key_1234567');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encrypt_data($data) {
    if (empty($data)) return $data;
    try {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
        $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            error_log("Encryption failed for data: " . substr($data, 0, 100));
            return $data; // Return original if encryption fails
        }
        return base64_encode($iv . $encrypted);
    } catch (Exception $e) {
        error_log("Encryption error: " . $e->getMessage());
        return $data;
    }
}

function decrypt_data($encrypted_data) {
    if (empty($encrypted_data)) return $encrypted_data;
    
    try {
        $data = base64_decode($encrypted_data);
        if ($data === false) {
            // Not valid base64 - probably plain text
            return $encrypted_data;
        }
        
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        
        // Check if this looks like encrypted data (has IV + encrypted content)
        if (strlen($data) < $iv_length) {
            // Too short to be encrypted data - return as is
            return $encrypted_data;
        }
        
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        $decrypted = openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            // Decryption failed - might be plain text or old key
            error_log("Decryption failed for data: " . substr($encrypted_data, 0, 100));
            return $encrypted_data;
        }
        
        return $decrypted;
    } catch (Exception $e) {
        error_log("Decryption error: " . $e->getMessage());
        return $encrypted_data;
    }
}

function log_audit_action($action, $module, $description = '') {
    global $conn;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $created_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("
        INSERT INTO audit_log (user_id, action, module, description, created_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $user_id, $action, $module, $description, $created_at);
    $stmt->execute();
    $stmt->close();
}
?>