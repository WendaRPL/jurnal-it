// ======================
// TAB SWITCH
// ======================
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.remove('active');
            c.style.display = 'none';
        });
        this.classList.add('active');
        const id = 'tab-' + this.dataset.tab;
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('active');
            el.style.display = 'block';
        }
    });
});

// ======================
// ACCORDION TOGGLE
// ======================
document.addEventListener('click', function(e) {
    if (e.target.closest('.user-header')) {
        const header = e.target.closest('.user-header');
        const accordion = header.parentElement;
        header.classList.toggle('active');
        const content = accordion.querySelector('.user-content');
        if (content) content.classList.toggle('open');
    }
});

// ======================
// Warna chart per tipe_deskripsi
// ======================
const tipeColors = {
    "Maintenance Hardware": "#3498db",
    "Maintenance Software": "#2ecc71",
    "Service/Repair Hardware": "#9b59b6",
    "Service/Repair Software": "#f1c40f",
    "Development": "#e67e22",
    "Administrasi": "#e74c3c",
    "Other Activity": "#1abc9c"
};

// ======================
// Render Progress Bar + Donut Chart per User
// ======================
function renderUserProgress() {
    document.querySelectorAll('.user-accordion').forEach(acc => {
        const uid = acc.dataset.userid;
        const chartContainer = acc.querySelector('.chart-container');
        const progressContainer = acc.querySelector('.progress-container');
        if (!progressContainer || !chartContainer) return;

        // Reset
        progressContainer.innerHTML = "";
        chartContainer.innerHTML = `<canvas id="chart-${uid}"></canvas>`;

        const rows = acc.querySelectorAll('table.user-table tbody tr:not([style*="display: none"])');
        const tipeCount = {};

        rows.forEach(row => {
            const tipe = row.cells[5]?.innerText.trim() || "Tidak Diketahui";
            tipeCount[tipe] = (tipeCount[tipe] || 0) + 1;
        });

        const total = Object.values(tipeCount).reduce((a, b) => a + b, 0);

        if (total === 0) {
            progressContainer.innerHTML = "<p><em>Tidak ada data</em></p>";
            chartContainer.innerHTML = "<p><em>Tidak ada chart</em></p>";
            return;
        }

        // Render progress bar
        Object.keys(tipeCount).forEach(label => {
            const count = tipeCount[label];
            const percent = ((count / total) * 100).toFixed(2);
            const color = tipeColors[label] || "#7f8c8d";

            const wrapper = document.createElement("div");
            wrapper.className = "progress-group";
            wrapper.innerHTML = `
                <span class="progress-text">${label}</span>
                <span class="progress-number">${percent}%</span>
                <div class="progress sm">
                    <div class="progress-bar" style="width:${percent}%; background:${color}"></div>
                </div>
            `;
            progressContainer.appendChild(wrapper);
        });

        // Render donut chart
        renderUserDonut(uid, tipeCount, total);
    });
}

// ======================
// Render Donut Chart (Chart.js)
// ======================
function renderUserDonut(uid, tipeCount, total) {
    const ctx = document.getElementById(`chart-${uid}`).getContext("2d");

    const labels = Object.keys(tipeCount);
    const data = Object.values(tipeCount);
    const colors = labels.map(label => tipeColors[label] || "#7f8c8d");

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const val = ctx.raw;
                            const percent = ((val / total) * 100).toFixed(2);
                            return `${ctx.label}: ${percent}% (${val})`;
                        }
                    }
                }
            },
            cutout: "70%"
        }
    });
}

function afterFilterUpdateCharts() { 
    renderUserProgress(); 
}

// ======================
// Render Donut Chart (Chart.js)
// ======================
function renderUserDonut(uid, tipeCount, total) {
    const ctx = document.getElementById(`chart-${uid}`).getContext("2d");

    const labels = Object.keys(tipeCount);
    const data = Object.values(tipeCount);
    const colors = labels.map(label => tipeColors[label] || "#7f8c8d");

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const val = ctx.raw;
                            const percent = ((val / total) * 100).toFixed(2);
                            return `${ctx.label}: ${percent}% (${val})`;
                        }
                    }
                }
            },
            cutout: "70%"
        }
    });
}

function afterFilterUpdateCharts() { 
    renderUserProgress(); 
}

// ======================
// EXPORT / PRINT
// ======================
async function exportData(format, section) {
    try {
        switch (format) {
            case 'copy': await exportToClipboard(); break;
            case 'csv': await exportToCSV(); break;
            case 'excel': await exportToExcel(); break;
            case 'pdf': await exportToPDF(); break;
            case 'print': await exportToPrint(); break;
            default: alert('Format export tidak dikenali');
        }
    } catch (error) {
        console.error('Export error:', error);
        showNotif('Error saat export: ' + error.message, 'error');
    }
}

// ----------------------
// COPY TO CLIPBOARD
// ----------------------
function copyTable() {
    const activeTab = document.querySelector('.tab-content.active');
    if (!activeTab) {
        alert('Pilih tab terlebih dahulu!');
        return;
    }

    const rows = activeTab.querySelectorAll('tbody tr');
    if (!rows.length) {
        alert('Tidak ada data untuk dicopy!');
        return;
    }

    let data = [];
    let headers = [];

    // ambil header
    const headerCells = activeTab.querySelectorAll('thead th');
    headers = Array.from(headerCells).map(th => th.textContent.trim());
    data.push(headers.join("\t"));

    // ambil isi tabel
    rows.forEach(row => {
        if (row.style.display === 'none') return; // skip yg ke-hide filter
        const cols = row.querySelectorAll('td');
        const rowData = Array.from(cols).map(td => td.textContent.trim());
        data.push(rowData.join("\t"));
    });

    if (data.length <= 1) {
        alert('Tidak ada data yang bisa dicopy!');
        return;
    }

    const textToCopy = data.join("\n");
    navigator.clipboard.writeText(textToCopy).then(() => {
        showNotif("success", "Data berhasil dicopy ke clipboard!");
    }).catch(() => {
        showNotif("error", "Gagal copy data ke clipboard.");
    });
}

// ----------------------
// EXPORT TABLE DATA
// ----------------------
function exportTable(format) {
    const activeTab = document.querySelector('.tab-content.active');
    if (!activeTab) {
        alert('Pilih tab terlebih dahulu!');
        return;
    }

    const rows = activeTab.querySelectorAll('tbody tr');
    if (!rows.length) {
        alert('Tidak ada data untuk diexport!');
        return;
    }

    let data = [];
    let headers = [];

    // ambil header tabel
    const headerCells = activeTab.querySelectorAll('thead th');
    headers = Array.from(headerCells).map(th => th.textContent.trim());
    data.push(headers);

    // ambil isi tabel
    rows.forEach(row => {
        if (row.style.display === 'none') return; // skip yg ke-hide filter
        const cols = row.querySelectorAll('td');
        const rowData = Array.from(cols).map(td => td.textContent.trim());
        data.push(rowData);
    });

    if (data.length <= 1) {
        showNotif('error', 'Tidak ada data yang bisa diexport!');
        return;
    }

    // proses sesuai format
    if (format === 'csv') {
        showNotif('success', 'Ekspor ke CSV berhasil!');
        let csvContent = data.map(e => e.join(",")).join("\n");
        downloadFile(csvContent, 'export.csv', 'text/csv');
    } 
    else if (format === 'excel') {
        showNotif('success', 'Ekspor ke Excel berhasil!');
        let excelContent = `
            <table border="1">
                ${data.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
            </table>
        `;
        downloadFile(excelContent, 'export.xls', 'application/vnd.ms-excel');
    } 
    else if (format === 'pdf') {
    const checked = Array.from(document.querySelectorAll('#tab-laporan .print-checkbox:checked'));
    
    function printData(userIds) {
        const wrapper = document.createElement('div');

        userIds.forEach((uid, idx) => {
            const laporanAcc = document.querySelector(`#tab-laporan .user-accordion[data-userid="${uid}"]`);
            const catatanAcc = document.querySelector(`#tab-catatan .user-accordion[data-userid="${uid}"]`);
            const block = document.createElement('div');
            block.classList.add('user-print-block');

            let uname = laporanAcc?.querySelector('.user-name')?.textContent.trim() 
                     || catatanAcc?.querySelector('.user-name')?.textContent.trim() 
                     || "User " + uid;

            const laporanRows = laporanAcc ? laporanAcc.querySelectorAll('.user-table tbody tr').length : 0;
            const catatanRows = catatanAcc ? catatanAcc.querySelectorAll('.user-table tbody tr').length : 0;
            const totalRows = laporanRows + catatanRows;

            const printDate = new Date().toLocaleString("id-ID", {
                day: "2-digit", month: "2-digit", year: "numeric",
                hour: "2-digit", minute: "2-digit"
            });

            block.innerHTML += `
                <table class="user-info">
                    <tr><th>Nama User</th><td>${uname}</td></tr>
                    <tr><th>Total Laporan</th><td>${laporanRows}</td></tr>
                    <tr><th>Total Catatan</th><td>${catatanRows}</td></tr>
                    <tr><th>Total Data</th><td>${totalRows}</td></tr>
                    <tr><th>Tanggal Cetak</th><td>${printDate}</td></tr>
                </table>
            `;

            if (laporanAcc) {
                const table = laporanAcc.querySelector('.user-table');
                block.innerHTML += `<h3>Laporan</h3>`;
                if (table) {
                    const clonedTable = table.cloneNode(true);
                    block.appendChild(clonedTable);
                } else {
                    block.innerHTML += `<p><em>Tidak ada laporan.</em></p>`;
                }
            }

            // Clone progress bar asli dari accordion
            const progressOrig = laporanAcc?.querySelector('.progress-container') 
                                || catatanAcc?.querySelector('.progress-container');
            if (progressOrig) {
                block.appendChild(progressOrig.cloneNode(true));
            }

            if (catatanAcc) {
                const table = catatanAcc.querySelector('.user-table');
                block.innerHTML += `<h3>Catatan</h3>`;
                if (table) {
                    const clonedTable = table.cloneNode(true);
                    block.appendChild(clonedTable);
                } else {
                    block.innerHTML += `<p><em>Tidak ada catatan.</em></p>`;
                }
            }

            wrapper.appendChild(block);
            if (idx < userIds.length - 1) {
                wrapper.appendChild(document.createElement('hr'));
            }
        });

        const w = window.open('', 'printWindow');
        w.document.write(`<html><head><title>Export PDF</title>
            <style>
                body{font-family:"Segoe UI",Arial; margin:30px; color:#000; line-height:1.6;}
                h2{text-align:center;margin-bottom:30px;font-size:20px;text-transform:uppercase;}
                h3{margin:20px 0 10px;font-size:16px;color:#333;}
                table{width:100%;border-collapse:collapse;margin-bottom:15px;}
                table th, table td{border:1px solid #444;padding:8px;text-align:left;font-size:13px;}
                table th{background:#00f7ff;color:#000;}
                .user-info{margin-bottom:20px;}
                .user-info th{width:180px;background:#f2f2f2;}
                .user-print-block{margin-bottom:40px;page-break-inside:avoid;}
                hr{border:none;border-top:2px solid #888;margin:40px 0;}
                @media print {
                    body { margin: 15px; }
                    .user-print-block { page-break-inside: avoid; }
                    hr { page-break-after: always; }
                }
            </style>
        </head><body>`);
        w.document.write(`<h2>Laporan & Catatan</h2>`);
        w.document.write(wrapper.innerHTML);
        w.document.write('</body></html>');
        w.document.close();
        
        // Auto print setelah window terbuka
        setTimeout(() => {
            w.print();
            // Tunggu sebentar sebelum menutup window
            setTimeout(() => {
                w.close();
                showNotif('success', 'PDF berhasil dibuat dan sedang dicetak');
            }, 500);
        }, 500);
    }

    if (!checked.length) {
        // Konfirmasi dulu
        if (confirm('Tidak ada user dicentang. Apakah Anda ingin mencetak semua data?')) {
            const allAcc = document.querySelectorAll('#tab-laporan .user-accordion, #tab-catatan .user-accordion');
            const userIds = [...new Set(Array.from(allAcc).map(acc => acc.dataset.userid).filter(Boolean))];
            printData(userIds);
        } else {
            showNotif('error', 'Proses cetak dibatalkan.');
        }
    } else {
        const userIds = [...new Set(checked.map(el => {
            const accordion = el.closest('.user-accordion');
            return accordion ? accordion.dataset.userid : null;
        }).filter(Boolean))];
        
        printData(userIds);
    }
}


}

function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

// ----------------------
// PRINT SEMUA (baru, gabung Laporan + Catatan)
// ----------------------
function printSemua() {
    const checked = Array.from(document.querySelectorAll('#tab-laporan .print-checkbox:checked'));

    // -------------------
    // Konfirmasi jika ga ada user dicentang
    // -------------------
    if (!checked.length) {
        if (!confirm('Tidak ada user dicentang. Apakah Anda ingin mencetak semua data?')) {
            showNotif('error', 'Proses cetak dibatalkan.');
            return;
        }
    }

    // -------------------
    // Ambil userIds sesuai pilihan / semua
    // -------------------
    const userIds = !checked.length
        ? [...new Set(Array.from(document.querySelectorAll('#tab-laporan .user-accordion, #tab-catatan .user-accordion'))
            .map(acc => acc.dataset.userid).filter(Boolean))]
        : [...new Set(checked.map(el => el.closest('.user-accordion')?.dataset?.userid).filter(Boolean))];

    if (!userIds.length) {
        showNotif('error', 'Tidak ada data untuk dicetak.');
        return;
    }

    // -------------------
    // Generate konten print
    // -------------------
    function printData(userIds) {
        const wrapper = document.createElement('div');

        userIds.forEach((uid, idx) => {
            const laporanAcc = document.querySelector(`#tab-laporan .user-accordion[data-userid="${uid}"]`);
            const catatanAcc = document.querySelector(`#tab-catatan .user-accordion[data-userid="${uid}"]`);

            if (!laporanAcc && !catatanAcc) return;

            const block = document.createElement('div');
            block.classList.add('user-print-block');

            let uname = laporanAcc?.querySelector('.user-name')?.textContent.trim() 
                     || catatanAcc?.querySelector('.user-name')?.textContent.trim() 
                     || "User " + uid;

            const laporanRows = laporanAcc ? laporanAcc.querySelectorAll('.user-table tbody tr').length : 0;
            const catatanRows = catatanAcc ? catatanAcc.querySelectorAll('.user-table tbody tr').length : 0;
            const totalRows = laporanRows + catatanRows;

            const printDate = new Date().toLocaleString("id-ID", {
                day: "2-digit", month: "2-digit", year: "numeric",
                hour: "2-digit", minute: "2-digit"
            });

            // Info user
            block.innerHTML += `
                <table class="user-info">
                    <tr><th>Nama User</th><td>${uname}</td></tr>
                    <tr><th>Total Laporan</th><td>${laporanRows}</td></tr>
                    <tr><th>Total Catatan</th><td>${catatanRows}</td></tr>
                    <tr><th>Total Data</th><td>${totalRows}</td></tr>
                    <tr><th>Tanggal Cetak</th><td>${printDate}</td></tr>
                </table>
            `;

            // Laporan
            block.innerHTML += `<h3>Laporan</h3>`;
            if (laporanAcc) {
                const table = laporanAcc.querySelector('.user-table');
                if (table) block.appendChild(table.cloneNode(true));
                else block.innerHTML += `<p><em>Tidak ada laporan.</em></p>`;
            } else block.innerHTML += `<p><em>Tidak ada laporan.</em></p>`;

            // Clone progress bar asli dari accordion
            const progressOrig = laporanAcc?.querySelector('.progress-container') 
                                || catatanAcc?.querySelector('.progress-container');
            if (progressOrig) {
                block.appendChild(progressOrig.cloneNode(true));
            }

            // Catatan
            block.innerHTML += `<h3>Catatan</h3>`;
            if (catatanAcc) {
                const table = catatanAcc.querySelector('.user-table');
                if (table) block.appendChild(table.cloneNode(true));
                else block.innerHTML += `<p><em>Tidak ada catatan.</em></p>`;
            } else block.innerHTML += `<p><em>Tidak ada catatan.</em></p>`;

            wrapper.appendChild(block);
            if (idx < userIds.length - 1) wrapper.appendChild(document.createElement('hr'));
        });

        return wrapper;
    }

    // -------------------
    // Run print
    // -------------------
    function runPrint(userIds) {
        const wrapper = printData(userIds);

        const w = window.open('', 'printWindow');
        w.document.write(`<html><head><title>Print Semua</title>
            <style>
                body{font-family:"Segoe UI",Arial; margin:30px; color:#000; line-height:1.6;}
                h2{text-align:center;margin-bottom:30px;font-size:20px;text-transform:uppercase;}
                h3{margin:20px 0 10px;font-size:16px;color:#333;}
                table{width:100%;border-collapse:collapse;margin-bottom:15px;}
                table th, table td{border:1px solid #444;padding:8px;text-align:left;font-size:13px;}
                table th{background:#00f7ff;color:#000;}
                .user-info{margin-bottom:20px;}
                .user-info th{width:180px;background:#f2f2f2;}
                .user-print-block{margin-bottom:40px;page-break-inside:avoid;}
                hr{border:none;border-top:2px solid #888;margin:40px 0;}
                .progress-container{margin:10px 0;}
            </style>
        </head><body>`);
        w.document.write(`<h2>Laporan & Catatan</h2>`);
        w.document.body.appendChild(wrapper);
        w.document.close();

        // Auto print & close
        setTimeout(() => {
            w.focus();
            w.print();
            w.close();
        }, 100);
    }

    runPrint(userIds);
}





// ----------------------
// EXPORT TO PRINT (lama, hanya laporan)
// ----------------------
async function exportToPrint() {
    const printSection = document.querySelector('.history-container').cloneNode(true);

    const controls = printSection.querySelector('.table-controls');
    if (controls) controls.remove();

    printSection.querySelectorAll('.user-accordion').forEach(acc => {
        const checkbox = acc.querySelector('.print-checkbox');
        if (!checkbox || !checkbox.checked) {
            acc.remove();
        } else {
            checkbox.remove();
            const content = acc.querySelector('.user-content');
            if (content) {
                content.style.display = 'block';
                content.style.maxHeight = 'none';
            }
            const header = acc.querySelector('.user-header');
            if (header) {
                const uname = header.querySelector('.user-name')?.textContent.trim() || '';
                const totalRows = acc.querySelectorAll('.user-table tbody tr').length;
                const printDate = new Date().toLocaleString("id-ID", {
                    day: "2-digit", month: "2-digit", year: "numeric",
                    hour: "2-digit", minute: "2-digit"
                });
                header.outerHTML = `
                    <table class="user-info">
                        <tr><th>Nama User</th><td>${uname}</td></tr>
                        <tr><th>Total Laporan</th><td>${totalRows}</td></tr>
                        <tr><th>Tanggal Cetak</th><td>${printDate}</td></tr>
                    </table>
                `;
            }
        }
    });

    const w = window.open('', 'printWindow');
    w.document.write(`<html><head><title>Print</title>
        <style>
            body{font-family:"Segoe UI",Arial; margin:20px; color:#222;}
            h2{text-align:center;margin-bottom:20px}
            table{width:100%;border-collapse:collapse;margin-bottom:12px}
            table th, table td{border:1px solid #ddd;padding:6px;text-align:left;font-size:12px}
            .user-info th{background:#f4f4f4;padding:6px;width:160px}
            .user-accordion{page-break-inside:avoid;margin-bottom:20px}
        </style>
    </head><body>`);
    w.document.write(`<h2>Laporan</h2>`);
    w.document.write(printSection.innerHTML);
    w.document.write('</body></html>');
    w.document.close();
    setTimeout(()=>{ w.print(); w.close(); }, 400);
}

// ======================
// NOTIFIKASI EXPORT
// ======================
function showNotif(type, message) {
    // pastikan container ada
    let container = document.querySelector('.notif-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notif-container';
        document.body.appendChild(container);
    }

    // hapus notif lama dengan tipe sama biar ga numpuk
    container.querySelectorAll(`.notif.${type}`).forEach(el => el.remove());

    // buat notif baru
    const notif = document.createElement('div');
    notif.className = `notif ${type}`;
    notif.textContent = message;

    container.appendChild(notif);

    // auto remove setelah animasi
    setTimeout(() => {
        notif.remove();
        // hapus container kalau kosong
        if (!container.querySelector('.notif')) {
            container.remove();
        }
    }, 4000);
}


// ======================
// FILTER TANGGAL + SEARCH (final dengan default today)
// ======================
function resetFilter() {
    // Kosongkan input tanggal
    document.getElementById("startDate").value = "";
    document.getElementById("endDate").value = "";

    // Kosongkan global search
    const searchInput = document.getElementById('searchInputStaff');
    if (searchInput) searchInput.value = "";

    // Kosongkan local search per table
    document.querySelectorAll('.table-search').forEach(input => {
        input.value = "";
    });

    // Hide semua data
    hideAllData();

    // Notif sekali
    showNotif('success', 'Filter berhasil direset!');
}

function hideAllData() {
    document.querySelectorAll('.user-accordion').forEach(acc => acc.style.display = 'none');
    document.querySelectorAll('.user-table tbody tr').forEach(row => row.style.display = 'none');
    document.querySelectorAll('.user-header .report-count').forEach(el => el.textContent = '0 Data');
    updateSectionHeaders(0);
}

function filterTable() {
    const searchText = document.getElementById('searchInputStaff')?.value.toLowerCase() || '';
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    let totalVisibleRows = 0;

    document.querySelectorAll('.user-accordion').forEach(accordion => {
        const userName = accordion.querySelector('.user-name')?.textContent.toLowerCase() || '';
        let userMatch = !searchText || userName.includes(searchText);

        if (!userMatch) {
            accordion.style.display = 'none';
            accordion.querySelector('.report-count').textContent = '0 Data';
            return;
        }

        const localSearch = accordion.querySelector('.table-search')?.value.toLowerCase() || '';
        const rows = accordion.querySelectorAll('tbody tr');
        let visibleRows = 0;

        rows.forEach(row => {
            let dateCell;
            if (accordion.closest('#tab-laporan')) {
                dateCell = row.querySelector('td:nth-child(1)');
            } else if (accordion.closest('#tab-catatan')) {
                dateCell = row.querySelector('td:nth-child(2)');
            } else {
                dateCell = row.querySelector('td:first-child');
            }

            const rowDate = dateCell ? dateCell.textContent.trim() : '';
            const dateInRange = (!startDate || rowDate >= startDate) && (!endDate || rowDate <= endDate);

            const rowText = row.textContent.toLowerCase();
            const match = (!localSearch || rowText.includes(localSearch)) && dateInRange && rowText.includes(searchText);

            if (match) {
                row.style.display = '';
                visibleRows++;
                totalVisibleRows++;
            } else {
                row.style.display = 'none';
            }
        });

        const countElement = accordion.querySelector('.user-header .report-count');
        if (countElement) {
            let countText = visibleRows + ' ';
            if (accordion.closest('#tab-laporan')) countText += 'Laporan';
            else if (accordion.closest('#tab-catatan')) countText += 'Catatan';
            else countText += 'Data';
            countElement.textContent = countText;
        }

        accordion.style.display = visibleRows > 0 ? 'block' : 'none';
    });

    updateSectionHeaders(totalVisibleRows);
    afterFilterUpdateCharts();
}

function updateSectionHeaders(totalVisibleRows = null) {
    if (totalVisibleRows === null) {
        totalVisibleRows = 0;
        document.querySelectorAll('.user-accordion').forEach(acc => {
            if (acc.style.display !== 'none') {
                const rows = acc.querySelectorAll('tbody tr:not([style*="display: none"])');
                totalVisibleRows += rows.length;
            }
        });
    }

    document.querySelectorAll('.section-header .total-reports').forEach(el => {
        el.textContent = totalVisibleRows + ' Data';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // set default date ke hari ini
    const today = new Date();
    const todayFormatted = formatDate(today);
    document.getElementById('startDate').value = todayFormatted;
    document.getElementById('endDate').value = todayFormatted;

    // langsung apply filter pertama kali
    filterTable();

    // live search global
    document.getElementById('searchInputStaff')?.addEventListener('input', filterTable);

    // live search per table
    document.querySelectorAll('.table-search').forEach(input => {
        input.addEventListener('input', filterTable);
    });

    // realtime filter date
    document.getElementById('startDate')?.addEventListener('change', filterTable);
    document.getElementById('endDate')?.addEventListener('change', filterTable);

    // tombol reset
    document.querySelectorAll('.date-range-btn').forEach(btn => {
        if (btn.textContent.includes('Reset')) {
            btn.addEventListener('click', resetFilter);
        }
    });
});

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}





// ======================
// TABLE SCROLL MOBILE
// ======================
function enhanceTableScrolling() {
    const containers = document.querySelectorAll('.table-scroll-container');
    containers.forEach(c => {
        let startX, scrollLeft, isDown=false;
        c.addEventListener('touchstart', e => {
            isDown=true; startX=e.touches[0].pageX - c.offsetLeft; scrollLeft=c.scrollLeft;
        });
        c.addEventListener('touchmove', e => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.touches[0].pageX - c.offsetLeft;
            const walk = (x - startX) * 2;
            c.scrollLeft = scrollLeft - walk;
        });
        c.addEventListener('touchend', ()=>isDown=false);
        c.addEventListener('touchcancel', ()=>isDown=false);
    });
}

// ======================
// INIT
// ======================
document.addEventListener('DOMContentLoaded', function() {
    enhanceTableScrolling();
    document.querySelectorAll('.export-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('exporting');
            setTimeout(()=>this.classList.remove('exporting'), 1000);
        });
    });
});