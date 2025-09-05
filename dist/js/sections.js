function showDetails(userName, missingHours) {
        const modalTitle = document.getElementById("modalTitle");
        const tbody = document.querySelector("#missingHoursList tbody");

        modalTitle.innerText = "Detail Jam Tidak Terpenuhi User: " + userName;
        tbody.innerHTML = "";

        if (missingHours.length === 0) {
            tbody.innerHTML = "<tr><td colspan='4' class='text-center'>Semua laporan terpenuhi✅</td></tr>";
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