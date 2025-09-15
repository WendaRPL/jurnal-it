<?php
session_start();
require_once "direct/config.php";

// Validasi session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit;
}

$role_id   = intval($_SESSION['role_id'] ?? 0);
$user_id   = intval($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? '';

ob_start();
?>
<link rel="stylesheet" href="dist/css/history.css">

<div class="history-container">
    <h2 class="section-text">History Laporan</h2>

    <!-- TAB SWITCH -->
    <div class="tab-switch" style="display:flex; gap:10px; justify-content:center; margin-bottom:18px;">
        <button class="tab-btn active" data-tab="laporan">Laporan</button>
        <button class="tab-btn" data-tab="catatan">Catatan</button>
    </div>

    <!-- Export & Search -->
    <div class="table-controls">
  <div class="export-buttons">
    <button class="export-btn" onclick="copyTable()">Copy</button>
    <button class="export-btn" onclick="exportTable('excel')">Export Excel</button>
    <button class="export-btn" onclick="exportTable('csv')">Export CSV</button>
    <button class="export-btn" onclick="exportTable('pdf')">Export PDF</button>
    <button class="export-btn" onclick="printSemua()">Print Semua</button>
  </div>
  <!-- 🔍 Live Search -->
  <div class="search-wrapper">
    <input 
      type="text" 
      id="searchInputStaff" 
      class="search-box" 
      placeholder="Cari staff / nama user..."
    >
  </div>
  <div class="date-range-wrapper">
    <input type="date" id="startDate" class="search-date">
    <span class="date-range-separator">-</span>
    <input type="date" id="endDate" class="search-date">
    <button class="date-range-btn" onclick="resetFilter()">Reset</button>
  </div>
</div>


<?php
// ============================
// QUERY USER SESUAI ROLE
// ============================
switch ($role_id) {
    case 1: // Admin
        $userQuery = "SELECT id, username, name, role_id FROM user WHERE role_id IN (2,3) ORDER BY role_id DESC, username ASC";
        break;
    case 2: // SPV
        $userQuery = "SELECT id, username, name, role_id FROM user WHERE role_id = 3 OR id = $user_id ORDER BY role_id DESC, username ASC";
        break;
    case 3: // Staff
        $userQuery = "SELECT id, username, name, role_id FROM user WHERE id = $user_id ORDER BY username ASC";
        break;
    default:
        echo "<p class='error'>Role tidak dikenali.</p>";
        exit;
}

$userResult = $conn->query($userQuery);
if (!$userResult) {
    echo "<p class='error'>Error query user: " . htmlspecialchars($conn->error) . "</p>";
    exit;
}

// Bagi user jadi SPV dan Staff
$spvUsers = [];
$staffUsers = [];
while ($u = $userResult->fetch_assoc()) {
    $uid = $u['id'];
    $base = [
        'user_info' => [
            'id' => $uid,
            'username' => $u['username'] ?? '',
            'full_name' => $u['name'] ?? $u['username'],
            'role_id' => $u['role_id'] ?? 0
        ],
        'reports' => [],
        'catatan' => []
    ];
    if ($u['role_id'] == 2) $spvUsers[$uid] = $base;
    if ($u['role_id'] == 3) $staffUsers[$uid] = $base;
}

// ============================
// QUERY LAPORAN
// ============================
switch ($role_id) {
    case 1:
        $reportQuery = "
            SELECT th.*, tp.description AS tipe_deskripsi, u.username, u.name AS full_name, u.role_id
            FROM transaksi_harian th
            LEFT JOIN user u ON th.user_id = u.id
            LEFT JOIN tipe_pekerjaan tp ON th.tipe_id = tp.id
            WHERE th.date IS NOT NULL
            ORDER BY u.role_id DESC, u.username ASC, th.date DESC, th.start_time DESC
        ";
        break;
    case 2:
        $reportQuery = "
            SELECT th.*, tp.description AS tipe_deskripsi, u.username, u.name AS full_name, u.role_id
            FROM transaksi_harian th
            LEFT JOIN user u ON th.user_id = u.id
            LEFT JOIN tipe_pekerjaan tp ON th.tipe_id = tp.id
            WHERE th.date IS NOT NULL AND (u.role_id = 3 OR th.user_id = $user_id)
            ORDER BY u.role_id DESC, u.username ASC, th.date DESC, th.start_time DESC
        ";
        break;
    case 3:
        $reportQuery = "
            SELECT th.*, tp.description AS tipe_deskripsi, u.username, u.name AS full_name, u.role_id
            FROM transaksi_harian th
            LEFT JOIN user u ON th.user_id = u.id
            LEFT JOIN tipe_pekerjaan tp ON th.tipe_id = tp.id
            WHERE th.date IS NOT NULL AND th.user_id = $user_id
            ORDER BY th.date DESC, th.start_time DESC
        ";
        break;
}

$rRes = $conn->query($reportQuery);
if ($rRes && $rRes->num_rows > 0) {
    while ($rep = $rRes->fetch_assoc()) {
        $uid = $rep['user_id'];
        if (isset($spvUsers[$uid])) $spvUsers[$uid]['reports'][] = $rep;
        if (isset($staffUsers[$uid])) $staffUsers[$uid]['reports'][] = $rep;
    }
}

// ============================
// TAB LAPORAN
// ============================
echo '<div id="tab-laporan" class="tab-content active">';

// SPV Section
echo '<h3 class="role-section-title">Supervisor</h3>';
foreach ($spvUsers as $uid => $ud) {
    $userInfo = $ud['user_info'];
    $reports = $ud['reports'];
    echo '<div class="user-accordion" data-userid="'.$uid.'">';
    echo '<input type="checkbox" class="print-checkbox" onclick="event.stopPropagation()">';
    echo '<div class="user-header"><span class="user-name">'.$userInfo['full_name'].'</span>';
    echo '<span class="report-count">'.count($reports).' Laporan</span>';
    echo '<span class="accordion-icon">▼</span></div>';
    echo '<div class="user-content"><div class="table-scroll-container">';
    if ($reports) {
        echo '<table class="user-table"><thead><tr>
                <th>Tanggal</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Durasi</th>
                <th>Deskripsi</th><th>Tipe</th><th>Terencana</th><th>Status</th><th>Approved</th>
              </tr></thead><tbody>';
        foreach ($reports as $r) {
            $start = new DateTime($r['start_time'] ?: '00:00:00');
            $end = new DateTime($r['end_time'] ?: '00:00:00');
            $dur = $start->diff($end)->format('%H:%I');
            echo '<tr>
                    <td>'.$r['date'].'</td>
                    <td>'.$r['start_time'].'</td>
                    <td>'.$r['end_time'].'</td>
                    <td>'.$dur.'</td>
                    <td>'.$r['deskripsi'].'</td>
                    <td>'.$r['tipe_deskripsi'].'</td>
                    <td>'.($r['terencana'] ? 'Ya':'Tidak').'</td>
                    <td>'.$r['status'].'</td>
                    <td>'.($r['approved']?'Approved':'Pending').'</td>
                  </tr>';
        }
        echo '</tbody></table>';
        // Chart container per user
        echo '<div class="chart-container">';
        echo '<canvas id="chart-'.$uid.'"></canvas>';
        echo '</div>';
    } else {
        echo '<p class="no-data-user">Tidak ada laporan untuk supervisor ini.</p>';
    }
    echo '</div></div></div>';
}

// Staff Section
echo '<h3 class="role-section-title">Staff</h3>';
foreach ($staffUsers as $uid => $ud) {
    $userInfo = $ud['user_info'];
    $reports = $ud['reports'];
    echo '<div class="user-accordion" data-userid="'.$uid.'">';
    echo '<input type="checkbox" class="print-checkbox" onclick="event.stopPropagation()">';
    echo '<div class="user-header"><span class="user-name">'.$userInfo['full_name'].'</span>';
    echo '<span class="report-count">'.count($reports).' Laporan</span>';
    echo '<span class="accordion-icon">▼</span></div>';
    echo '<div class="user-content"><div class="table-scroll-container">';
    if ($reports) {
        echo '<table class="user-table"><thead><tr>
                <th>Tanggal</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Durasi</th>
                <th>Deskripsi</th><th>Tipe</th><th>Terencana</th><th>Status</th><th>Approved</th>
              </tr></thead><tbody>';
        foreach ($reports as $r) {
            $start = new DateTime($r['start_time'] ?: '00:00:00');
            $end = new DateTime($r['end_time'] ?: '00:00:00');
            $dur = $start->diff($end)->format('%H:%I');
            echo '<tr>
                    <td>'.$r['date'].'</td>
                    <td>'.$r['start_time'].'</td>
                    <td>'.$r['end_time'].'</td>
                    <td>'.$dur.'</td>
                    <td>'.$r['deskripsi'].'</td>
                    <td>'.$r['tipe_deskripsi'].'</td>
                    <td>'.($r['terencana'] ? 'Ya':'Tidak').'</td>
                    <td>'.$r['status'].'</td>
                    <td>'.($r['approved']?'Approved':'Pending').'</td>
                  </tr>';
        }
        echo '</tbody></table>';
        // Chart container per user
        echo '<div class="chart-container">';
        echo '<canvas id="chart-'.$uid.'"></canvas>';
        echo '</div>';
    } else {
        echo '<p class="no-data-user">Tidak ada laporan untuk staff ini.</p>';
    }
    echo '</div></div></div>';
}

echo '</div>'; // end laporan


// ============================
// QUERY CATATAN
// ============================
switch ($role_id) {
    case 1:
        $catatanQuery = "
            SELECT tc.*, u.username, u.name AS full_name, u.role_id
            FROM transaksi_catatan tc
            LEFT JOIN user u ON tc.user_id = u.id
            WHERE tc.date IS NOT NULL
            ORDER BY u.role_id DESC, u.username ASC, tc.date DESC
        ";
        break;
    case 2:
        $catatanQuery = "
            SELECT tc.*, u.username, u.name AS full_name, u.role_id
            FROM transaksi_catatan tc
            LEFT JOIN user u ON tc.user_id = u.id
            WHERE tc.date IS NOT NULL AND (u.role_id = 3 OR tc.user_id = $user_id)
            ORDER BY u.role_id DESC, u.username ASC, tc.date DESC
        ";
        break;
    case 3:
        $catatanQuery = "
            SELECT tc.*, u.username, u.name AS full_name, u.role_id
            FROM transaksi_catatan tc
            LEFT JOIN user u ON tc.user_id = u.id
            WHERE tc.date IS NOT NULL AND tc.user_id = $user_id
            ORDER BY tc.date DESC
        ";
        break;
}

$cRes = $conn->query($catatanQuery);
if ($cRes && $cRes->num_rows > 0) {
    while ($c = $cRes->fetch_assoc()) {
        $uid = $c['user_id'];
        if (isset($spvUsers[$uid])) $spvUsers[$uid]['catatan'][] = $c;
        if (isset($staffUsers[$uid])) $staffUsers[$uid]['catatan'][] = $c;
    }
}

// ============================
// TAB CATATAN
// ============================
echo '<div id="tab-catatan" class="tab-content" style="display:none;">';

// SPV Section
echo '<h3 class="role-section-title">Supervisor</h3>';
foreach ($spvUsers as $uid => $ud) {
    $userInfo = $ud['user_info'];
    $notes = $ud['catatan'];
    echo '<div class="user-accordion" data-userid="'.$uid.'">';
    echo '<div class="user-header"><span class="user-name">'.$userInfo['full_name'].'</span>';
    echo '<span class="report-count">'.count($notes).' Catatan</span>';
    echo '<span class="accordion-icon">▼</span></div>';
    echo '<div class="user-content"><div class="table-scroll-container">';
    if ($notes) {
        echo '<table class="user-table"><thead><tr>
                <th>ID</th><th>Tanggal</th><th>Deskripsi</th><th>Approved</th><th>Last Modified</th>
              </tr></thead><tbody>';
        foreach ($notes as $n) {
            echo '<tr>
                    <td>'.$n['id'].'</td>
                    <td>'.$n['date'].'</td>
                    <td>'.$n['deskripsi'].'</td>
                    <td>'.($n['approved']?'Yes':'No').'</td>
                    <td>'.($n['last_modified_datetime'] ?: $n['input_datetime']).'</td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="no-data-user">Tidak ada catatan untuk supervisor ini.</p>';
    }
    echo '</div></div></div>';
}

// Staff Section
echo '<h3 class="role-section-title">Staff</h3>';
foreach ($staffUsers as $uid => $ud) {
    $userInfo = $ud['user_info'];
    $notes = $ud['catatan'];
    echo '<div class="user-accordion" data-userid="'.$uid.'">';
    echo '<div class="user-header"><span class="user-name">'.$userInfo['full_name'].'</span>';
    echo '<span class="report-count">'.count($notes).' Catatan</span>';
    echo '<span class="accordion-icon">▼</span></div>';
    echo '<div class="user-content"><div class="table-scroll-container">';
    if ($notes) {
        echo '<table class="user-table"><thead><tr>
                <th>ID</th><th>Tanggal</th><th>Deskripsi</th><th>Approved</th><th>Last Modified</th>
              </tr></thead><tbody>';
        foreach ($notes as $n) {
            echo '<tr>
                    <td>'.$n['id'].'</td>
                    <td>'.$n['date'].'</td>
                    <td>'.$n['deskripsi'].'</td>
                    <td>'.($n['approved']?'Yes':'No').'</td>
                    <td>'.($n['last_modified_datetime'] ?: $n['input_datetime']).'</td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="no-data-user">Tidak ada catatan untuk staff ini.</p>';
    }
    echo '</div></div></div>';
}

echo '</div>'; // end catatan
?>

</div> <!-- end .history-container -->

<!-- Untuk jsPDF (PDF) & html2canvas (tetap seperti file asli) -->
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="dist/js/history.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>