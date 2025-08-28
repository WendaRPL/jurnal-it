<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $tanggal    = $_POST['tanggal'];
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];
    $deskripsi  = $_POST['deskripsi'];
    $tipe_id    = $_POST['tipe_id'];
    $terencana  = $_POST['terencana'];
    $status     = $_POST['status'];

    $now = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO transaksi_harian 
        (user_id, date, start_time, end_time, deskripsi, tipe_id, terencana, status, input_datetime, last_modified_datetime, approved) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");

    $stmt->bind_param("issssissss",
        $user_id, $tanggal, $start_time, $end_time, 
        $deskripsi, $tipe_id, $terencana, $status,
        $now, $now
    );

    if ($stmt->execute()) {
    header("Location: ../input_report.php?msg=created");
    exit;
} else {
    header("Location: ../input_report.php?msg=error");
    exit;
}
}
?>
