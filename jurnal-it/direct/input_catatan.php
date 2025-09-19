<?php
session_start();
require_once "config.php"; // pastikan path benar

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = $_SESSION['user_id'];
    $tanggal   = $_POST['tanggal_catatan'] ?? '';
    $deskripsi = $_POST['catatan'] ?? '';

    // Validasi basic
    if (empty($tanggal) || empty($deskripsi)) {
        header("Location: ../input.php?msg=invalid_request");
        exit;
    }

    // Query insert
    $stmt = $conn->prepare("
        INSERT INTO transaksi_catatan (user_id, date, deskripsi, input_datetime, last_modified_datetime, approved) 
        VALUES (?, ?, ?, NOW(), NOW(), 0)
    ");
    $stmt->bind_param("iss", $user_id, $tanggal, $deskripsi);

    if ($stmt->execute()) {
        header("Location: ../input_report.php?msg=created");
    } else { 
        header("Location: ../input_report.php?msg=error");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../input_report.php?msg=invalid_request");
    exit;
}
