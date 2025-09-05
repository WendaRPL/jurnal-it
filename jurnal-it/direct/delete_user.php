<?php
session_start();
require_once "config.php";

// Hanya admin boleh hapus
$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1) {
    header("Location: home.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // safety: jangan hapus superadmin
    if ($id === 1) {
        header("Location: ../user_manage.php?msg=cannot_delete_admin");
        exit;
    }

    // query delete
    $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../user_manage.php?msg=deleted");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: ../user_manage.php?msg=invalid_request");
    exit;
}
?>
