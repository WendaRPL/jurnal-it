<?php
session_start();
require_once __DIR__ . "/config.php";
date_default_timezone_set("Asia/Jakarta");

header("Content-Type: application/json");

$userId = $_SESSION["user_id"] ?? 0;
if (!$userId) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$currentPassword = $data["current_password"] ?? "";
$newPassword     = $data["new_password"] ?? "";

if (!$currentPassword || !$newPassword) {
    echo json_encode(["success" => false, "error" => "Missing data"]);
    exit;
}

// Cek password lama
$stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($hashed);
$stmt->fetch();
$stmt->close();

if (!password_verify($currentPassword, $hashed)) {
    echo json_encode(["success" => false, "error" => "Password lama salah"]);
    exit;
}

$newHashed  = password_hash($newPassword, PASSWORD_BCRYPT);
$modifiedAt = date("Y-m-d H:i:s");
$modifiedBy = $userId;

$sql = "UPDATE user 
        SET password = ?, last_modified_datetime = ?, modified_by = ? 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "error"   => $conn->error,
        "sql"     => $sql
    ]);
    exit;
}

$stmt->bind_param("ssii", $newHashed, $modifiedAt, $modifiedBy, $userId);
if ($stmt->execute()) {
    echo json_encode([
        "success"      => true,
        "modified_at"  => $modifiedAt,
        "modified_by"  => $modifiedBy
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error"   => $stmt->error,
        "sql"     => $sql
    ]);
}

$stmt->close();
$conn->close();
?>