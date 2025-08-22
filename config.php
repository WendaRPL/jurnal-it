<?php
$host = "localhost";
$dbname = "jurnal_it";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    // Update last_online_datetime tiap kali ada aktivitas
    $stmt = $conn->prepare("UPDATE User SET last_online_datetime = NOW() WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();

    // Update status jadi online
    $stmt = $conn->prepare("UPDATE User SET status = 'online' WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();
}


if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
