<?php
session_start();
require_once "config.php"; // file koneksi DB

$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1 && $role_id != 2) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "User ID tidak valid"]);
    exit;
}

$today = new DateTime();
$start = (clone $today)->modify("-7 days");

$missing_hours = [];

$period = new DatePeriod($start, new DateInterval('P1D'), (clone $today)->modify('+1 day'));
foreach ($period as $day) {
    if ($day->format("D") == "Sun") continue;
    $dateStr = $day->format("Y-m-d");

    $stmt = $conn->prepare("SELECT login, logout, TIMEDIFF(logout, login) as gap FROM transaksi_harian WHERE user_id=? AND DATE(date)=?");
    $stmt->bind_param("is", $user_id, $dateStr);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        $missing_hours[] = [
            "date" => $day->format("d M Y"),
            "login" => "-",
            "logout" => "-",
            "gap" => "Full day"
        ];
    } else {
        while ($row = $res->fetch_assoc()) {
            $missing_hours[] = [
                "date" => $day->format("d M Y"),
                "login" => $row['login'],
                "logout" => $row['logout'],
                "gap" => $row['gap']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(["missing_hours" => $missing_hours]);
exit;
?>
