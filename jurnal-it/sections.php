<?php
function renderUnfulfilledSection($conn) {
    $role_id = $_SESSION['role_id'] ?? 0;
    if ($role_id != 1 && $role_id != 2) return;

    $users = mysqli_query($conn, "SELECT id, name FROM user WHERE role_id != 1");
    $today = new DateTime();
    $start = (clone $today)->modify("-7 days");

    // Jam kerja default (misal 08:00 - 17:00)
    $workStart = "08:00:00";
    $workEnd = "17:00:00";
    ?>
    <section class="unfulfilled-section">
      <div class="section-header">Belum Terpenuhi</div>
      <div class="user-cards">
    <?php
    while ($u = mysqli_fetch_assoc($users)) {
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

            // Kalau tidak ada kegiatan sama sekali
            if (empty($slots)) {
                $missingDates[] = ["date" => $day->format("d M Y"), "start" => $workStart, "end" => $workEnd, "gap" => "Full day"];
            } else {
                $prevEnd = $workStart;
                foreach ($slots as $slot) {
                    // Jika ada gap sebelum kegiatan berikutnya
                    if (strtotime($slot['start']) > strtotime($prevEnd)) {
                        $gap = (strtotime($slot['start']) - strtotime($prevEnd))/3600;
                        $missingDates[] = [
                            "date" => $day->format("d M Y"),
                            "start" => $prevEnd,
                            "end" => $slot['start'],
                            "gap" => round($gap, 2)." jam bolong"
                        ];
                    }
                    $prevEnd = $slot['end'];
                }
                // Gap setelah kegiatan terakhir
                if (strtotime($prevEnd) < strtotime($workEnd)) {
                    $gap = (strtotime($workEnd) - strtotime($prevEnd))/3600;
                    $missingDates[] = [
                        "date" => $day->format("d M Y"),
                        "start" => $prevEnd,
                        "end" => $workEnd,
                        "gap" => round($gap,2)." jam bolong"
                    ];
                }
            }
        }

        if (!empty($missingDates)) {
            echo '<div class="user-card" onclick=\'showDetails("'.htmlspecialchars($u['name'], ENT_QUOTES).'", '.json_encode($missingDates).')\'>';
            echo '<div class="user-header">'.htmlspecialchars($u['name']).'</div>';
            foreach ($missingDates as $d) echo '<div class="user-date">'.$d['date'].'</div>';
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
        <h2 id="modalTitle">Detail Jam Bolong</h2>
        <table id="missingHoursList" class="table-missing">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Durasi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <script>
    function showDetails(userName, missingHours) {
        const modalTitle = document.getElementById("modalTitle");
        const tbody = document.querySelector("#missingHoursList tbody");

        modalTitle.innerText = "Detail Jam Bolong User: " + userName;
        tbody.innerHTML = "";

        if (missingHours.length === 0) {
            tbody.innerHTML = "<tr><td colspan='4' class='text-center'>Tidak ada jam bolong ✅</td></tr>";
        } else {
            missingHours.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.date}</td>
                        <td>${item.start}</td>
                        <td>${item.end}</td>
                        <td>${item.gap}</td>
                    </tr>`;
            });
        }

        document.getElementById("detailModal").classList.remove("hidden");
    }

    function closeModal() {
        document.getElementById("detailModal").classList.add("hidden");
    }

    window.addEventListener("click", function(e) {
        const modal = document.getElementById("detailModal");
        if (e.target === modal) closeModal();
    });
    </script>
<?php
}
?>
