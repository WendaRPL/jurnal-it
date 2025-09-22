<?php
require_once "direct/config.php"; // koneksi database

    global $conn; // koneksi dari config.php

    $role_id = $_SESSION['role_id'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;
?>

<link rel="stylesheet" href="dist/css/cards.css">
<link rel="stylesheet" href="dist/css/reports.css">

<div class="card-container">

    <?php
    // Cek role user login
    if ($role_id == 1 || $role_id == 2): 

        // Tentukan target role yang harus direview
        $target_role_id = null;
        $card_title = "";

        if ($role_id == 1) {
            // Admin mereview Supervisor
            $target_role_id = 2;
            $card_title = "Pending Review Supervisor";
        } elseif ($role_id == 2) {
            // Supervisor mereview Staff
            $target_role_id = 3;
            $card_title = "Pending Review Staff";
        }

        // ================================
        // Hitung total user dengan pending
        // ================================
        $query_total = "
            SELECT COUNT(DISTINCT u.id) AS total_pending_user
            FROM user u
            WHERE u.role_id = ?
            AND (
                EXISTS (
                    SELECT 1 FROM transaksi_harian th 
                    WHERE th.user_id = u.id AND th.approved IS NULL
                )
                OR EXISTS (
                    SELECT 1 FROM transaksi_catatan tc 
                    WHERE tc.user_id = u.id AND tc.approved IS NULL
                )
            )
        ";
        $stmt_total = $conn->prepare($query_total);
        $stmt_total->bind_param("i", $target_role_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result()->fetch_assoc();
        $total_pending_user = $result_total['total_pending_user'];

        // ================================
        // Ambil detail per user pending
        // ================================
        $query_user = "
            SELECT 
                u.id,                         
                u.username,
                u.name AS user_name,
                COUNT(DISTINCT th.id) AS total_laporan,
                COUNT(DISTINCT tc.id) AS total_catatan,
                GREATEST(
                    IFNULL(MAX(th.date), '0000-00-00'),
                    IFNULL(MAX(tc.date), '0000-00-00')
                ) AS last_date
            FROM user u
            LEFT JOIN transaksi_harian th 
                ON u.id = th.user_id AND th.approved IS NULL
            LEFT JOIN transaksi_catatan tc 
                ON u.id = tc.user_id AND tc.approved IS NULL
            WHERE u.role_id = ?
            GROUP BY u.id, u.username, u.name
            HAVING total_laporan > 0 OR total_catatan > 0
            ORDER BY last_date DESC
        ";
        $stmt_user = $conn->prepare($query_user);
        $stmt_user->bind_param("i", $target_role_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
    ?>
 
    <!-- Card Pending (Accordion) -->
    <div class="card-outstanding card">
        <div class="card-header">
            <div class="text-pending"><?php echo $card_title; ?></div>
            <div class="text-pending-total">
                Total User: <?php echo $total_pending_user; ?>
            </div>
        </div>
        <div class="pending-cards-container">
            <?php while ($row = $result_user->fetch_assoc()): ?>
                <div class="pending-card-accordion">
                    <div class="accordion-header">
                        <span class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></span>
                        <button class="accordion-toggle">+</button>
                    </div>
                    <div class="accordion-content">
                        <div>Total Laporan Harian: <span class="highlight-cyan"><?php echo $row['total_laporan']; ?></span></div>
                        <div>Total Catatan Khusus: <span class="highlight-yellow"><?php echo $row['total_catatan']; ?></span></div>
                        <div class="accordion-footer">
                            <a href="approval.php?user=<?php echo $row['id']; ?>" class="review-btn">
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>  
                </div>
            <?php endwhile; ?>
        </div>
        <a href="approval.php" class="card-footer">
            <div style="padding-left: 6px">Lihat Semua Pending</div>
            <div style="padding-right: 8px">➜</div>
        </a>
    </div>

    <script>
    document.querySelectorAll('.pending-card-accordion').forEach(card => {
        const header = card.querySelector('.accordion-header');
        const toggle = header.querySelector('.accordion-toggle');
        const content = card.querySelector('.accordion-content');

        // Klik di header mana saja
        header.addEventListener('click', () => {
            content.classList.toggle('open');
            toggle.classList.toggle('active');
            toggle.textContent = toggle.classList.contains('active') ? '−' : '+';
        });
    });
    </script>
    <?php endif; ?>


    <!-- Card Laporan Terbaru (Hanya untuk SPV & User) -->
<?php if ($role_id == 2 || $role_id == 3): ?>
<div class="card-report card">
    <div class="card-header">
        Laporan Terbaru (7 Terbaru)<abbr style="font-size: 14px;"><?php echo htmlspecialchars($_SESSION['name']); ?></abbr>
    </div>
    <div class="reports-container">
        <?php
        // Ambil 7 laporan terbaru milik user ini
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

        $reports = [];
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($res) {
                    $reports = $res->fetch_all(MYSQLI_ASSOC);
                }
            }
            $stmt->close();
        }

        // Filter: cuma NULL (Pending) & 1 (Approved)
        $validReports = array_filter($reports, function($r) {
            return is_null($r['approved']) || (int)$r['approved'] === 1;
        });

        if (empty($validReports)) {
            echo '<div class="empty">Tidak ada laporan terbaru.</div>';
        } else {
            foreach ($validReports as $report):
                $approved = $report['approved'];

                if (is_null($approved)) {
                    $status = 'Pending';
                    $statusClass = 'pending';
                } else {
                    $status = 'Approved';
                    $statusClass = 'approved';
                }
        ?>
        <div class="report-item">
            <div class="report-title"><?= htmlspecialchars($report['deskripsi']) ?></div>
            <div class="report-meta">
                <span class="report-date">
                    <?= htmlspecialchars($report['formatted_date']) ?> |
                    <?= htmlspecialchars($report['start_time']) ?> - <?= htmlspecialchars($report['end_time']) ?>
                </span>
                <span class="report-type"><?= htmlspecialchars($report['tipe_pekerjaan']) ?></span>
            </div>
            <div class="report-footer">
                <span class="report-status <?= htmlspecialchars($statusClass) ?>">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>
        </div>
        <?php
            endforeach;
        }
        ?>
    </div>
</div>
<?php endif; ?>



    <!-- Card Statistik Cepat (Hanya SPV & User) -->
    <?php if ($role_id == 2 || $role_id == 3): ?>
    <?php
        // Ambil statistik pakai mysqli
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN approved = 1 THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN approved IS NULL OR approved = 0 THEN 1 ELSE 0 END) AS pending
            FROM transaksi_harian
            WHERE user_id = $user_id
        ";
        $result = mysqli_query($conn, $sql);
        $stats = mysqli_fetch_assoc($result);
    ?>
    <div class= "row-section">
        <div class="card-stats card">
            <div class="card-header">Statistik Cepat <abbr style="font-size: 14px;"><?php echo $_SESSION['name']; ?></abbr></div>
            <div class="stats-card">
                <div class="stats-labels">
                    <div>Total Input</div>
                    <div>Approved</div>
                    <div>Pending</div>
                </div>
                <div class="stats-values">
                    <div><?= $stats['total'] ?? 0 ?></div>
                    <div><?= $stats['approved'] ?? 0 ?></div>
                    <div><?= $stats['pending'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($role_id == 2 || $role_id == 3): ?>
        <div class="vertical-buttons">
            <a href="history.php" class="card-link"  >
                <div class="search-card compact">
                    <i class="fas fa-search"></i>
                    <div class="label">Lihat Semua Laporan</div>
                </div>
            </a>
            
            <a href="input_report.php" class="card-link" >
                <div class="search-card compact">
                    <i class="fas fa-plus"></i>
                    <div class="label">Buat Laporan</div>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <!-- User Management & User Online (Khusus Admin) -->
     <?php if ($role_id == 1): ?>
    <?php
        // Update status user sebelum ambil data
        $conn->query("
            UPDATE User 
            SET status = 'idle' 
            WHERE last_online_datetime < NOW() - INTERVAL 5 MINUTE 
            AND last_online_datetime >= NOW() - INTERVAL 15 MINUTE
        ");
        $conn->query("
            UPDATE User 
            SET status = 'offline' 
            WHERE last_online_datetime < NOW() - INTERVAL 15 MINUTE
        ");

        // Ambil statistik user online/idle/offline
        $resultUser = $conn->query("
            SELECT 
                SUM(status='online')  AS online_users,
                SUM(status='idle')    AS idle_users,
                SUM(status='offline') AS offline_users
            FROM User
        ");
        $userStats = $resultUser->fetch_assoc();
    ?>
    <div class="row-section">
        <div class="card-status card">
            <div class="card-header">User Status</div>
            <div class="stats-card">
                <div class="stats-labels">
                    <div>Online</div>
                    <div>Idle</div>
                    <div>Offline</div>
                </div>
                <div class="stats-values">
                    <div><?= $userStats['online_users'] ?? 0 ?></div>
                    <div><?= $userStats['idle_users'] ?? 0 ?></div>
                    <div><?= $userStats['offline_users'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <a href="history.php" class="card-link" aria-label="Kelola Pengguna">
            <div class="card search-card" style="font-weight: 600">Report History</div>
        </a>
    <?php endif; ?>
    </div>
</div>

    



