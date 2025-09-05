<?php
session_start();
require_once "config.php";

// Cek role (hanya Admin yang boleh bikin user)
$current_role_id = $_SESSION['role_id'] ?? 0;
if ($current_role_id != 1) { // HANYA ROLE 1 (ADMIN) YANG BOLEH
    header("Location: ../user_manage.php?msg=unauthorized");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $role_id    = intval($_POST['role_id'] ?? 3);  // DEFAULT STAFF (3)
    $initial    = trim($_POST['initial'] ?? '');
    $input_datetime = date("Y-m-d H:i:s");

    // Validasi input wajib
    if (empty($username) || empty($password) || empty($name) || empty($initial)) {
        header("Location: ../user_manage.php?msg=incomplete_data");
        exit;
    }

    // VALIDASI ROLE_ID: Hanya boleh 2 (SPV) atau 3 (Staff), TIDAK BOLEH 1 (Admin)
    if ($role_id !== 2 && $role_id !== 3) {
        $role_id = 3; // Fallback ke Staff
    }

    // Cek duplicate username
    $check = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        header("Location: ../user_manage.php?msg=duplicate");
        exit;
    }
    $check->close();

    // Hash password
    $hashed_pw = password_hash($password, PASSWORD_BCRYPT);

    // Insert user baru
    $stmt = $conn->prepare("INSERT INTO user (username, password, name, role_id, initial, input_datetime) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $username, $hashed_pw, $name, $role_id, $initial, $input_datetime);

    if ($stmt->execute()) {
        header("Location: ../user_manage.php?msg=created");
        exit;
    } else {
        header("Location: ../user_manage.php?msg=error");
        exit;
    }
} else {
    header("Location: ../user_manage.php?msg=invalid_request");
    exit;
}