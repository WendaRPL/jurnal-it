<?php
require_once 'config.php'; // koneksi

// Update status user sebelum ambil data
$conn->query("
    UPDATE User 
    SET status = 'idle' 
    WHERE last_online_datetime < NOW() - INTERVAL 5 MINUTE 
      AND last_online_datetime >= NOW() - INTERVAL 15 MINUTE
");

// Ambil statistik user
$result = $conn->query("
    SELECT 
        SUM(status='online') as online_users,
        SUM(status='idle') as idle_users,
        SUM(status='offline') as offline_users
    FROM User
");
$stats = $result->fetch_assoc();

// Tampilan
echo "Online: " . ($stats['online_users'] ?? 0) . "<br>";
echo "Idle: "   . ($stats['idle_users'] ?? 0) . "<br>";
echo "Offline: ". ($stats['offline_users'] ?? 0) . "<br>";
?>
