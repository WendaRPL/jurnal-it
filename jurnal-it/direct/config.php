<?php
// ==================== KONFIGURASI DATABASE ====================
$host = "localhost";
$dbname = "jurnal_it";
$username = "root";
$password = "";

// ==================== ERROR HANDLING SETTINGS ====================
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// ==================== CUSTOM ERROR HANDLER ====================
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log error detail ke file log
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    
    // Tampilkan pesan aman ke user
    if (ini_get('display_errors')) {
        echo "<div style='padding: 15px; margin: 10px; border: 1px solid #f5c6cb; background: #f8d7da; color: #721c24; border-radius: 5px;'>
                <strong>System Error</strong>: Terjadi kesalahan sistem. Silakan hubungi administrator.
              </div>";
    }
    return true; // Prevent default PHP error handler
}

set_error_handler("customErrorHandler");

// ==================== CUSTOM EXCEPTION HANDLER ====================
function customExceptionHandler($exception) {
    // Log exception detail ke file log
    error_log("PHP Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // Tampilkan pesan aman ke user
    if (ini_get('display_errors')) {
        echo "<div style='padding: 15px; margin: 10px; border: 1px solid #f5c6cb; background: #f8d7da; color: #721c24; border-radius: 5px;'>
                <strong>System Error</strong>: Terjadi kesalahan sistem. Silakan hubungi administrator.
              </div>";
    }
}

set_exception_handler("customExceptionHandler");

// ==================== DATABASE CONNECTION DENGAN ERROR HANDLING ====================
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        // Log error detail tapi tampilkan pesan umum
        error_log("Database Connection Failed: " . $conn->connect_error . " [Error No: " . $conn->connect_errno . "]");
        throw new Exception("Database connection failed");
    }
    
    // Set charset untuk prevent SQL injection
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database Connection Exception: " . $e->getMessage());
    die("<div style='padding: 20px; margin: 20px; border: 2px solid #dc3545; background: #f8d7da; color: #721c24; border-radius: 8px; text-align: center;'>
            <h3>⚠️ System Maintenance</h3>
            <p>System sedang dalam perawatan. Silakan coba lagi beberapa saat.</p>
            <small>Jika masalah berlanjut, hubungi administrator sistem.</small>
         </div>");
}

// ==================== SESSION & USER ACTIVITY TRACKING ====================
// Pastikan session sudah start sebelum akses session variable
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    try {
        // Update last_online_datetime tiap kali ada aktivitas
        $stmt = $conn->prepare("UPDATE User SET last_online_datetime = NOW() WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $stmt->close();
        }
        
        // Update status jadi online
        $stmt = $conn->prepare("UPDATE User SET status = 'online' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $stmt->close();
        }
        
    } catch (Exception $e) {
        error_log("User Activity Update Error: " . $e->getMessage());
        // Jangan tampilkan error ke user, biarkan proses continue
    }
}

// ==================== DATABASE ERROR HANDLING UNTUK QUERY ====================
// Function untuk safe query execution
function executeQuery($conn, $sql, $params = [], $types = "") {
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        return $stmt;
        
    } catch (Exception $e) {
        error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// ==================== SECURITY HEADERS ====================
// Prevent sensitive information leakage
header('X-Powered-By: PHP');
header('Server: Apache');
?>