<?php
header('Content-Type: application/json');
session_start();

// ===== CONFIG =====
require_once __DIR__ . '/../../direct/config.php'; // path config

if (!$conn) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection not available"
    ]);
    exit;
}

$action = $_GET['action'] ?? 'get';
$userId = $_SESSION['user_id'] ?? 0;
$roleId = $_SESSION['role_id'] ?? 0;

try {
    if ($action === 'get') {
        $stmt = $conn->prepare("
            SELECT i.id, i.user_id, i.target_user_id, i.target_role_id, 
                   i.input_datetime, i.keterangan, i.title, i.type,
                   i.is_read
            FROM inbox i
            WHERE 
                i.target_user_id = ? 
                OR i.target_role_id = ? 
                OR (i.target_user_id IS NULL AND i.target_role_id IS NULL)
            ORDER BY i.input_datetime DESC
            LIMIT 50
        ");
        $stmt->bind_param("ii", $userId, $roleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } elseif ($action === 'mark-read') {
        $raw = file_get_contents("php://input");
        $json = json_decode($raw, true);
        $notifId = intval($json['notif_id'] ?? 0);

        $stmt = $conn->prepare("
            UPDATE inbox
            SET is_read = 1 
            WHERE id = ? 
              AND (target_user_id = ? OR target_user_id IS NULL)
        ");
        $stmt->bind_param("ii", $notifId, $userId);
        $stmt->execute();

        echo json_encode(["success" => true]);
        exit;

    } elseif ($action === 'mark-all-read') {
        $stmt = $conn->prepare("
            UPDATE inbox
            SET is_read = 1 
            WHERE (target_user_id = ? OR target_user_id IS NULL)
              AND is_read = 0
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        echo json_encode(["success" => true]);
        exit;

    } elseif ($action === 'unread-count') {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM inbox i
            WHERE (i.target_user_id = ? 
                   OR i.target_role_id = ? 
                   OR (i.target_user_id IS NULL AND i.target_role_id IS NULL))
              AND i.is_read = 0
        ");
        $stmt->bind_param("ii", $userId, $roleId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            "success" => true,
            "unread_count" => $result['unread_count'] ?? 0
        ]);
        exit;

    } else {
        echo json_encode([
            "success" => false,
            "error" => "Unknown action"
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
    exit;
}
