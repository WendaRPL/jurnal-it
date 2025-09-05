<?php
session_start();
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user_manage.php?msg=invalid_request");
    exit;
}

$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1) {
    header("Location: home.php?msg=unauthorized");
    exit;
}

$id       = intval($_POST['id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$name     = trim($_POST['name'] ?? '');
$role     = intval($_POST['role_id'] ?? 0);
$initial  = trim($_POST['initial'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$id || !$username || !$name) {
    header("Location: ../user_manage.php?msg=incomplete_data");
    exit;
}

$modified_by = $role_id;
$modified_at = date("Y-m-d H:i:s");

if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE user 
        SET username=?, name=?, role_id=?, initial=?, password=?, 
            last_modified_datetime=?, modified_by=? 
        WHERE id=?");
    $stmt->bind_param("ssisssii", $username, $name, $role, $initial, $hashed, $modified_at, $modified_by, $id);
} else {
    $stmt = $conn->prepare("UPDATE user 
        SET username=?, name=?, role_id=?, initial=?, 
            last_modified_datetime=?, modified_by=? 
        WHERE id=?");
    $stmt->bind_param("ssissii", $username, $name, $role, $initial, $modified_at, $modified_by, $id);
}

if ($stmt->execute()) {
header("Location: ../user_manage.php?msg=updated");
} else {
    header("Location: ../user_manage.php?msg=invalid_request");
}
exit;
?>