<?php
session_start();
require_once __DIR__ . "/../../direct/config.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT id, title, keterangan, type, input_datetime, is_read
        FROM `log`
        WHERE target_user_id IS NULL OR target_user_id = ?
        ORDER BY input_datetime DESC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

header('Content-Type: application/json');
echo json_encode($notifications);
