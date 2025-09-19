<?php
session_start();
require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

date_default_timezone_set("Asia/Jakarta");

$userId   = $_SESSION["user_id"] ?? 0;
$roleId   = $_SESSION["role_id"] ?? 0;

if (!$userId) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// Ambil data JSON dari fetch
$data  = json_decode(file_get_contents("php://input"), true);
$field = $data["field"] ?? "";
$value = trim($data["value"] ?? "");

// Daftar field yang boleh diupdate
$allowedFields = [
    "name"    => "name",
    "initial" => "initial"
];

if (!isset($allowedFields[$field])) {
    echo json_encode(["success" => false, "error" => "Invalid field"]);
    exit;
}

$column        = $allowedFields[$field];
$modified_by   = $userId; // user sendiri
$modified_at   = date("Y-m-d H:i:s");

// Query update (mirip edit_user.php, tapi cuma field yg dipilih)
$sql = "UPDATE user 
        SET {$column} = ?, last_modified_datetime = ?, modified_by = ? 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$stmt->bind_param("ssii", $value, $modified_at, $modified_by, $userId);
$ok = $stmt->execute();

if ($ok) {
    // Update session biar realtime ke-refresh di UI
    $_SESSION[$field] = $value;

    echo json_encode([
        "success"   => true,
        "field"     => $field,
        "new_value" => $value,
        "modified_at" => $modified_at,
        "modified_by" => $modified_by
    ]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
