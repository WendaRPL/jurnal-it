<?php
session_start();
require_once "config.php"; // koneksi ke database lu

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Update status user jadi offline
    $stmt = $conn->prepare("UPDATE user SET status = 'offline' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// Hapus semua session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect ke login
header("Location: login.php");
exit;
