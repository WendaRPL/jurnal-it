<?php
session_start();
require_once "config.php";

// Cek role (hanya Admin / Supervisor boleh bikin user)
$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1 && $role_id != 2) {
    die("❌ Akses ditolak.");
}

// Cek apakah form dikirim dengan method POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $role_id    = $_POST['role_id'] ?? 3;  // default Staff
    $initial    = trim($_POST['initial'] ?? '');
    $need_apv   = $_POST['need_apv'] ?? 1;
    $input_datetime = date("Y-m-d H:i:s");

    // Validasi input wajib
    if (empty($username) || empty($password) || empty($name) || empty($initial)) {
        die("⚠️ Semua field wajib diisi.");
    }

    // Cek duplicate username
    $check = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        header("Location: user_manage.php?msg=duplicate");
        exit;
    }
    $check->close();

    // Hash password
    $hashed_pw = password_hash($password, PASSWORD_BCRYPT);

    // Insert user baru
    $stmt = $conn->prepare("INSERT INTO user (username, password, name, role_id, initial, need_apv, input_datetime) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisis", $username, $hashed_pw, $name, $role_id, $initial, $need_apv, $input_datetime);

    if ($stmt->execute()) {
        header("Location: user_manage.php?msg=created");
        exit;
    } else {
        die("❌ Error: " . $conn->error);
    }
} else {
    die("⚠️ Invalid request.");
}
