<?php
require_once "direct/config.php"; // koneksi ke DB

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['id'] ?? 0);
    $type   = $_POST['type'] ?? '';
    $alasan = trim($_POST['alasan'] ?? '');

    if ($id <= 0 || !$type) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit;
    }

    // Tentukan tabel berdasarkan type
    $table = $type === 'laporan' ? 'transaksi_harian' : 'transaksi_catatan';

    // Kalau ada alasan → berarti reject
    if ($alasan !== '') {
        // Simpan alasan ke tabel log (opsional, bikin tabel baru kalau mau riwayat penolakan)
        // Misal: INSERT ke tabel `riwayat_penolakan`
        /*
        $sql_log = "INSERT INTO riwayat_penolakan (transaksi_id, type, alasan, tanggal) VALUES (?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("iss", $id, $type, $alasan);
        $stmt_log->execute();
        $stmt_log->close();
        */

        // Hapus data yang ditolak
        $sql = "DELETE FROM $table WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => $success, 'action' => 'reject']);
        exit;
    }

    // Kalau tidak ada alasan → berarti approve
    $sql = "UPDATE $table SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $success, 'action' => 'approve']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
