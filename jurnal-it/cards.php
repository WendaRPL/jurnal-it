<?php
require_once "config.php"; // koneksi database

function renderCards() {
    global $conn; // koneksi dari config.php

    $role_id = $_SESSION['role_id'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;
?>
<div class="card-container">

    <?php if ($role_id == 2 || $role_id == 3): ?>
    <!-- Card Laporan Terbaru (Hanya untuk SPV & User) -->
    <div class="card">
        <div class="card-header">Laporan Terbaru</div>
        <div class="reports-container">
            <?php
            // Ambil 5 laporan terbaru milik user ini
            $sql = "
                SELECT 
                    th.id,
                    th.date,
                    DATE_FORMAT(th.date, '%d %b %Y')   AS formatted_date,
                    TIME_FORMAT(th.start_time,'%H:%i') AS start_time,
                    TIME_FORMAT(th.end_time,  '%H:%i') AS end_time,
                    th.deskripsi,
                    th.approved,
                    tp.description AS tipe_pekerjaan
                FROM transaksi_harian th
                LEFT JOIN tipe_pekerjaan tp ON th.tipe_id = tp.id
                WHERE th.user_id = ?
                ORDER BY th.date DESC, th.start_time DESC
                LIMIT 7
            ";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $reports = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (empty($reports)) {
                echo '<div class="empty">Tidak ada laporan terbaru.</div>';
            } else {
                foreach ($reports as $report):
                    $status = $report['approved'] ? 'Approved' : 'Pending';
            ?>
                <div class="report-item">
                    <div class="report-title"><?= htmlspecialchars($report['deskripsi']) ?></div>
                    <div class="report-meta">
                        <span class="report-date">
                            <?= $report['formatted_date'] ?> | 
                            <?= $report['start_time'] ?> - <?= $report['end_time'] ?>
                        </span>
                        <span class="report-type"><?= htmlspecialchars($report['tipe_pekerjaan']) ?></span>
                    </div>
                    <div class="report-footer">
                        <span class="report-status <?= strtolower($status) ?>"><?= $status ?></span>
                    </div>
                </div>
            <?php
                endforeach;
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Card Statistik Cepat (Semua Role) -->
     <?php if ($role_id == 2 || $role_id == 3): ?>
    <div class="card">
        <div class="card-header">Statistik Cepat</div>
        <div class="stats-card">
            <div class="stats-labels">
                <div class="py-1">Total Input</div>
                <div class="py-1">Approved</div>
                <div class="py-1">Pending</div>
            </div>
            <div class="stats-values">
                <div class="py-1">50</div>
                <div class="py-1">35</div>
                <div class="py-1">15</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Button Vertikal untuk SPV & User -->
    <?php if ($role_id == 2 || $role_id == 3): ?>
    <div class="vertical-buttons">
        <a href="history.php" class="card-link" aria-label="Lihat semua laporan">
            <div class="search-card compact">
                <i class="fas fa-search"></i>
                <div class="label">Lihat Semua Laporan</div>
            </div>
        </a>
        
        <a href="input.php" class="card-link" aria-label="Buat Laporan">
            <div class="search-card compact">
                <i class="fas fa-plus"></i>
                <div class="label">Buat Laporan</div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- User Management (Khusus Admin) -->
    <?php if ($role_id == 1): ?>
    <a href="user_management.php" class="card-link" aria-label="Kelola Pengguna">
        <div class="card search-card">User Management</div>
    </a>
    <?php endif; ?>

</div>
<?php
}
?>
