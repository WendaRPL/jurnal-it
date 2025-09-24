<?php
session_start();
require_once "direct/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
ob_start();

$role_id = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

// ===================
// FILTER USER DARI CARD / QUERY
// ===================
$filter_user = isset($_GET['user']) ? intval($_GET['user']) : null;

// ===================
// BATASAN ROLE
// ===================
$whereRole = "";
if ($role_id == 3) {
    // Staff (role 3) tidak boleh masuk approval
    header("Location: riwayat.php");
    exit;
} 

// ===================
// QUERY USER + TOTAL PENDING + LAST DATE
// ===================
$sql_user = "
    SELECT u.id, u.name,
           MAX(th.date) AS last_date,
           COUNT(DISTINCT th.id) AS total_laporan,
           COUNT(DISTINCT tc.id) AS total_catatan
    FROM user u
    LEFT JOIN transaksi_harian th 
        ON th.user_id = u.id AND th.approved IS NULL
    LEFT JOIN transaksi_catatan tc 
        ON tc.user_id = u.id AND tc.approved IS NULL
    WHERE 1=1 {$whereRole}
";

if ($filter_user) {
    $sql_user .= " AND u.id = ?";
}

$sql_user .= " GROUP BY u.id, u.name ORDER BY last_date DESC";

if ($filter_user) {
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("i", $filter_user);
    $stmt->execute();
    $result_user = $stmt->get_result();
} else {
    $result_user = $conn->query($sql_user);
}

// ===================
// TOTAL SEMUA PENDING
// ===================
$total_laporan_pending = 0;
$total_catatan_pending = 0;

if ($result_user) {
    while ($row = $result_user->fetch_assoc()) {
        $total_laporan_pending += $row['total_laporan'];
        $total_catatan_pending += $row['total_catatan'];
    }
}

$total_pending = $total_laporan_pending + $total_catatan_pending;

// ===================
// QUERY DETAIL LAPORAN HARIAN
// ===================
$rows_laporan = [];

$sql_laporan = "
    SELECT th.id, u.name, th.date, th.start_time, th.end_time, 
           th.deskripsi, th.tipe_id, th.terencana, th.status, th.approved
    FROM transaksi_harian th
    JOIN user u ON th.user_id = u.id
    WHERE th.approved IS NULL {$whereRole}
";

if ($filter_user) {
    $sql_laporan .= " AND u.id = ?";
}

$sql_laporan .= " ORDER BY th.date DESC, th.start_time DESC";

if ($filter_user) {
    $stmtRowsLaporan = $conn->prepare($sql_laporan);
    $stmtRowsLaporan->bind_param("i", $filter_user);
    $stmtRowsLaporan->execute();
    $result_laporan = $stmtRowsLaporan->get_result();
} else {
    $result_laporan = $conn->query($sql_laporan);
}

while ($result_laporan && $row = $result_laporan->fetch_assoc()) {
    $rows_laporan[] = $row;
}

// ===================
// QUERY CATATAN KHUSUS
// ===================
$rows_catatan = [];

$sql_catatan = "
    SELECT tc.id, u.name, tc.date, tc.deskripsi, tc.approved
    FROM transaksi_catatan tc
    JOIN user u ON tc.user_id = u.id
    WHERE tc.approved IS NULL {$whereRole}
";

if ($filter_user) {
    $sql_catatan .= " AND u.id = ?";
}

$sql_catatan .= " ORDER BY tc.date DESC";

if ($filter_user) {
    $stmtRowsCatatan = $conn->prepare($sql_catatan);
    $stmtRowsCatatan->bind_param("i", $filter_user);
    $stmtRowsCatatan->execute();
    $result_catatan = $stmtRowsCatatan->get_result();
} else {
    $result_catatan = $conn->query($sql_catatan);
}

while ($result_catatan && $row = $result_catatan->fetch_assoc()) {
    $rows_catatan[] = $row;
}
?>


<link rel="stylesheet" href="dist/css/approval.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<div class="container">

    <!-- ==== HEADER ==== -->
    <header class="header">
        <h1><i class="fas fa-clipboard-check"></i>Approval Jurnal IT</h1>
        <div class="user-info">
            <div class="user-name">Hai, <strong><?php echo $_SESSION['name']; ?></strong></div>
        </div>
    </header>

    <!-- ==== STATS ==== -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-value"><?= $total_laporan_pending ?></div>
            <div class="stat-label">Pending Laporan Harian</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_catatan_pending ?></div>
            <div class="stat-label">Pending Catatan Khusus</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_pending ?></div>
            <div class="stat-label">Total Pending</div>
        </div>
    </section>


    <div class="second-container">

        <!-- ==== TABS ==== -->
        <div class="tabs">
            <button class="tab-btn active" data-target="#approvalSection">
                <i class="fas fa-clipboard-check"></i> Laporan Harian
            </button>
            <button class="tab-btn" data-target="#catatanSection">
                <i class="fas fa-sticky-note"></i> Catatan Khusus
            </button>
        </div>

        <!-- ==== FILTER TOGGLE ==== -->
        <div class="filter-btn">
            <button class="btn-action" id="toggleFilterBtn"><i class="fas fa-filter"></i> Filter</button>
        </div>

        <!-- ==== FILTER PANEL ==== -->
        <div id="filterPanel" class="filter-panel closed">
            <div class="filter-header">
                <h3><i class="fas fa-filter"></i> Filter Laporan</h3>
                <button class="modal-close">✖</button>
            </div>
            <div class="filter-body">

                <!-- Date Range -->
                <div class="filter-row date-range-field">
                    <label for="filterDateStart">Tanggal</label>
                    <div class="inline-inputs"> 
                        <input type="date" id="filterDateStart">
                        <span class="separator">s/d</span>
                        <input type="date" id="filterDateEnd">
                    </div>
                </div>

                <!-- Time Range -->
                <div class="filter-row time-range-field">
                    <label for="filterTimeStart">Waktu</label>
                    <div class="inline-inputs">
                        <input type="time" id="filterTimeStart">
                        <span class="separator">s/d</span>
                        <input type="time" id="filterTimeEnd">
                    </div>
                </div>

                <!-- Tipe Dropdown -->
                <div class="filter-row">
                    <label for="filterTipe">Tipe</label>
                    <select id="filterTipe">
                        <option value="">Semua Tipe</option>
                        <option value="MTCH">MTCH (Maintenance Hardware)</option>
                        <option value="MTCS">MTCS (Maintenance Software)</option>
                        <option value="SVCH">SVCH (Service/Repair Hardware)</option>
                        <option value="SVCS">SVCS (Service/Repair Software)</option>
                        <option value="DEV">DEV (Development)</option>
                        <option value="ADM">ADM (Administrasi)</option>
                        <option value="OTH">OTH (Other Activity)</option>
                    </select>
                </div>

                <!-- Terencana Checkbox -->
                <div class="filter-row">
                    <label>Terencana</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" class="filter-terencana" value="Iya"> Iya</label>
                        <label><input type="checkbox" class="filter-terencana" value="Tidak"> Tidak</label>
                    </div>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="filter-actions">
                <button class="btn-action" id="applyFilter">Terapkan</button>
                <button class="btn-action" id="resetFilter">Reset</button>
            </div>
        </div>

        <!-- ==== APPROVAL TABLE ==== -->
        <div id="approvalSection" class="tab-section active">
            <div class="table-container">
                <table id="approvalTable" class="display approval-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Tanggal</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Durasi</th>
                            <th>Deskripsi</th>
                            <th>Tipe</th>
                            <th>Terencana</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    foreach ($rows_laporan as $row) {
                        $tipe_text = match((int)$row['tipe_id']) {
                            1 => 'MTCH',
                            2 => 'MTCS',
                            3 => 'SVCH',
                            4 => 'SVCS',
                            5 => 'DEV',
                            6 => 'ADM',
                            7 => 'OTH',
                            default => '-'
                        };
                        $terencana_text = $row['terencana'] ? 'Iya' : 'Tidak';

                        echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['date']}</td>
                            <td>{$row['start_time']}</td>
                            <td>{$row['end_time']}</td>
                            <td>-</td>
                            <td><span class='deskripsi-text'>{$row['deskripsi']}</span></td>
                            <td>{$tipe_text}</td>
                            <td>{$terencana_text}</td>
                            <td><span class='status-text'>{$row['status']}</span></td>
                            <td>
                                <button class='btn-action btn-approve-static' data-type='laporan' data-id='{$row['id']}'>✔ Setuju</button>
                                <button class='btn-action btn-reject-static' data-type='laporan' data-id='{$row['id']}'>✖ Tolak</button>
                            </td>
                        </tr>";
                        $no++;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==== CATATAN KHUSUS TABLE ==== -->
        <div id="catatanSection" class="tab-section">
            <div class="table-container">
                <table id="catatanTable" class="display catatan-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($rows_catatan as $row) {
                            echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['deskripsi']}</td>
                                <td>
                                    <button class='btn-action btn-approve' data-id='<?= $row[id] ?>' data-type='<?= $type ?>'>✔ Approve</button>
                                    <button class='btn-action btn-reject' data-id='<?= $row[id] ?>' data-type='<?= $type ?>'>✖ Tolak</button>
                                </td>
                            </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tolak -->
<div id="rejectModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span style="color:white;" class="close">&times;</span>
    <h2>Alasan Penolakan</h2>
    <form class="form-reject" id="rejectForm">
        <input type="hidden" id="rejectId" name="id">
        <input type="hidden" id="rejectType" name="type">
        <textarea id="alasan" name="alasan" placeholder="Tuliskan alasan penolakan..." required></textarea>
        <button type="submit" class="btn-action">Kirim</button>
    </form>
  </div>
</div>

<form id="actionForm" method="POST" action="direct/update_approved.php" style="display:none;">
    <input type="hidden" name="id" id="formId">
    <input type="hidden" name="type" id="formType">
    <input type="hidden" name="action" id="formAction">
    <input type="hidden" name="alasan" id="formAlasan">
</form>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="dist/js/approval.js"></script>

<?php
$content = ob_get_clean();  // <- ini wajib biar template nge-echo kontennya
require_once "../jurnal-it/modules/layout/template.php";
?>