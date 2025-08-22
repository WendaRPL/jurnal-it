<?php
    $role_id = $_SESSION['role_id'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;

    // Tentuin siapa aja yg mau diambil
    if ($role_id == 1) {
        // Admin -> lihat semua (kecuali superadmin)
        $users = mysqli_query($conn, "SELECT id, name FROM user WHERE role_id != 1");
    } elseif ($role_id == 2) {
        // SPV -> hanya lihat staff yang punya spv_id = $user_id
        $stmt = $conn->prepare("SELECT id, name FROM user WHERE role_id = 3");
        $stmt->execute();
        $users = $stmt->get_result();
    } elseif ($role_id == 3) {
        // Staff -> hanya lihat diri sendiri
        $stmt = $conn->prepare("SELECT id, name FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $users = $stmt->get_result();
    } else {
        return; // role lain ga boleh akses     
    }

    $today = new DateTime(); // ini harus ada, tadi lupa
    $start = (clone $today)->modify("-7 days");

    // Jam kerja default (misal 08:00 - 17:00)
    $workStart = "08:00:00";
    $workEnd = "17:00:00";
    ?>

    <link rel="stylesheet" href="modules/css/unfullfiled-section.css">
    <link rel="stylesheet" href="modules/css/modal.css">


    
    <section class="unfulfilled-section">
      <div class="section-header">Belum Terpenuhi</div>
      <div class="user-cards">
    <?php
    while ($u = $users->fetch_assoc()) {  // ga usah mysqli_fetch_assoc()
        $missingDates = [];
        $period = new DatePeriod($start, new DateInterval('P1D'), (clone $today)->modify('+1 day'));

        foreach ($period as $day) {
            if ($day->format("D") == "Sun") continue;
            $dateStr = $day->format("Y-m-d");

            $stmt = $conn->prepare("
                SELECT start_time, end_time 
                FROM transaksi_harian 
                WHERE user_id=? AND DATE(date)=?
                ORDER BY start_time ASC
            ");
            $stmt->bind_param("is", $u['id'], $dateStr);
            $stmt->execute();
            $res = $stmt->get_result();

            $slots = [];
            while ($row = $res->fetch_assoc()) {
                $slots[] = ["start" => $row['start_time'], "end" => $row['end_time']];
            }

            if (empty($slots)) {
                // full day kosong
                $missingDates[] = ["date" => $day->format("d M Y"), "start" => $workStart, "end" => $workEnd, "gap" => "Full day"];
            } else {
                $prevEnd = $workStart;
                foreach ($slots as $slot) {
                    if (strtotime($slot['start']) > strtotime($prevEnd)) {
                        $gap = (strtotime($slot['start']) - strtotime($prevEnd))/3600;
                        $missingDates[] = [
                            "date" => $day->format("d M Y"),
                            "start" => $prevEnd,
                            "end" => $slot['start'],
                            "gap" => round($gap, 2)." jam kosong"
                        ];
                    }
                    $prevEnd = $slot['end'];
                }
                if (strtotime($prevEnd) < strtotime($workEnd)) {
                    $gap = (strtotime($workEnd) - strtotime($prevEnd))/3600;
                    $missingDates[] = [
                        "date" => $day->format("d M Y"),
                        "start" => $prevEnd,
                        "end" => $workEnd,
                        "gap" => round($gap,2)." jam kosong"
                    ];
                }
            }
        }

        if (!empty($missingDates)) {
            echo '<div class="user-card">';
            echo '<div class="user-header">'.htmlspecialchars($u['name']). '</div>';
            echo '<div class="user-dates">';
            foreach ($missingDates as $d) {
                echo '<div class="user-date">'.$d['date'].'</div>';
            }
            echo '</div>';
            echo '<button class="see-more-btn" onclick=\'showDetails("'.htmlspecialchars($u['name'], ENT_QUOTES).'", '.json_encode($missingDates).')\'>Click to see more</button>';
            echo '</div>';
        }
    }
    ?>
      </div>
    </section>

    <!-- Modal -->
    <div id="detailModal" class="modal hidden">
      <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Detail Jam Tidak terpenuhi</h2>
        <table id="missingHoursList" class="table-missing">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Gap</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <script src="modules/js/sections.js"></script>

