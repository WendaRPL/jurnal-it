document.addEventListener("DOMContentLoaded", () => {
    const ITEMS_PER_PAGE = 10;

    // State halaman aktif per tab
    let currentPage = { pending: 1, approved: 1, all: 1, spvPending: 1, spvApproved: 1 };

    // Toggle Accordion
    function toggleAccordion(element) {
        const accordion = element.closest('.user-accordion');
        const content = accordion.querySelector('.user-content');
        const isActive = accordion.classList.contains('active');

        // Tutup semua accordion lain
        document.querySelectorAll('.user-accordion').forEach(acc => {
            acc.classList.remove('active');
            const c = acc.querySelector('.user-content');
            if (c) c.style.maxHeight = 0;
        });

        // Buka accordion yang diklik
        if (!isActive) {
            accordion.classList.add('active');
            if (content) content.style.maxHeight = content.scrollHeight + 'px';
        }
    }
    window.toggleAccordion = toggleAccordion;

    // Bulk Actions
    function initBulkActions() {
        document.getElementById('approveSelected')?.addEventListener('click', () => {
            const checkedBoxes = document.querySelectorAll('.tab-content.active .row-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih setidaknya satu data untuk approve');
                return;
            }
            alert(`Approve ${checkedBoxes.length} data terpilih`);
        });

        document.getElementById('rejectSelected')?.addEventListener('click', () => {
            const checkedBoxes = document.querySelectorAll('.tab-content.active .row-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih setidaknya satu data untuk reject');
                return;
            }
            alert(`Reject ${checkedBoxes.length} data terpilih`);
        });
    }

    // Fungsi ini untuk menampilkan atau menyembunyikan tombol bulk actions
    // berdasarkan tab yang sedang aktif
    function toggleBulkActions() {
        // Mendapatkan tab yang sedang aktif (data-tab value)
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        // Mendapatkan elemen bulk actions
        const bulkActions = document.getElementById('bulkActions');
        
        // Jika tab aktif adalah 'pending', tampilkan bulk actions
        if (activeTab === 'pending') {
            bulkActions.style.display = 'flex';
        } else {
            // Untuk tab lain (approved/all), sembunyikan bulk actions
            bulkActions.style.display = 'none';
        }
    }

    // KODE SWITCH TAB
    // Fungsi untuk beralih antara tab-tab yang berbeda
    function switchTab(tabName) {
        // Update status aktif pada semua tombol tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                // Tambahkan class active pada tab yang diklik
                btn.classList.add('active');
            } else {
                // Hapus class active dari tab lainnya
                btn.classList.remove('active');
            }
        });
        
        // Update konten tab yang ditampilkan
        document.querySelectorAll('.tab-content').forEach(content => {
            if (content.id === tabName) {
                // Tampilkan konten tab yang sesuai
                content.classList.add('active');
            } else {
                // Sembunyikan konten tab lainnya
                content.classList.remove('active');
            }
        });
        
        // Panggil fungsi untuk menampilkan/sembunyikan bulk actions
        toggleBulkActions();
    }

    // PADA EVENT LISTENER TAB BUTTON:
    // Menambahkan event click pada semua tombol tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Ketika tombol tab diklik, panggil fungsi switchTab
            // dengan parameter data-tab value dari tombol yang diklik
            switchTab(btn.dataset.tab);
        });
    });

    // INISIALISASI SAAT HALAMAN DIMUAT
    // Menjalankan kode ketika halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Pastikan bulk actions dalam state yang benar saat halaman pertama dimuat
        toggleBulkActions();
        // Inisialisasi fungsi bulk actions (approve/reject selected)
        initBulkActions();
    });

    // Konfigurasi kolom untuk setiap section dengan class yang benar
    const columnConfig = {
        staff: [
            { id: 'checkbox', name: 'Checkbox', default: true, class: 'col-checkbox' },
            { id: 'start', name: 'Start Time', default: true, class: 'col-start' },
            { id: 'end', name: 'End Time', default: true, class: 'col-end' },
            { id: 'duration', name: 'Duration', default: true, class: 'col-duration' },
            { id: 'deskripsi', name: 'Deskripsi', default: true, class: 'col-deskripsi' },
            { id: 'type', name: 'Type', default: true, class: 'col-type' },
            { id: 'terencana', name: 'Terencana', default: true, class: 'col-terencana' },
            { id: 'status', name: 'Status', default: true, class: 'col-status' },
            { id: 'action', name: 'Action', default: true, class: 'col-action' }
        ],
        spv: [
            { id: 'checkbox', name: 'Checkbox', default: true, class: 'col-checkbox-spv' },
            { id: 'start', name: 'Start Time', default: true, class: 'col-start-spv' },
            { id: 'end', name: 'End Time', default: true, class: 'col-end-spv' },
            { id: 'duration', name: 'Duration', default: true, class: 'col-duration-spv' },
            { id: 'deskripsi', name: 'Deskripsi', default: true, class: 'col-deskripsi-spv' },
            { id: 'type', name: 'Type', default: true, class: 'col-type-spv' },
            { id: 'terencana', name: 'Terencana', default: true, class: 'col-terencana-spv' },
            { id: 'status', name: 'Status', default: true, class: 'col-status-spv' },
            { id: 'action', name: 'Action', default: true, class: 'col-action-spv' }
        ]
    };

    // Fungsi toggle column visibility untuk section tertentu
    window.toggleColumnVisibility = function(section, event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Hapus dropdown sebelumnya jika ada
        const existingDropdown = document.querySelector('.column-visibility-dropdown');
        if (existingDropdown) {
            existingDropdown.remove();
            return;
        }

        const button = event.currentTarget;
        
        // Buat dropdown menu
        const dropdownMenu = document.createElement('div');
        dropdownMenu.className = 'column-visibility-dropdown';
        dropdownMenu.style.position = 'absolute';
        dropdownMenu.style.backgroundColor = '#222';
        dropdownMenu.style.border = '1px solid #00f7ff';
        dropdownMenu.style.borderRadius = '6px';
        dropdownMenu.style.padding = '10px';
        dropdownMenu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
        dropdownMenu.style.zIndex = '1000';
        dropdownMenu.style.minWidth = '180px';
        dropdownMenu.style.color = 'white';
        dropdownMenu.dataset.section = section;

        // Header dropdown
        const header = document.createElement('div');
        header.textContent = `Tampilkan Kolom (${section.toUpperCase()}):`;
        header.style.fontWeight = 'bold';
        header.style.marginBottom = '8px';
        header.style.paddingBottom = '5px';
        header.style.borderBottom = '1px solid #00f7ff';
        header.style.color = '#00f7ff';
        header.style.fontSize = '13px';
        dropdownMenu.appendChild(header);

        // Container untuk checkbox
        const checkboxContainer = document.createElement('div');
        checkboxContainer.style.maxHeight = '200px';
        checkboxContainer.style.overflowY = 'auto';
        checkboxContainer.style.marginBottom = '10px';

        // Tambahkan checkbox untuk setiap kolom section ini
        columnConfig[section].forEach(col => {
            const isVisible = isColumnVisible(section, col.class);
            
            const label = document.createElement('label');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.marginBottom = '6px';
            label.style.cursor = 'pointer';
            label.style.fontSize = '12px';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = isVisible;
            checkbox.dataset.section = section;
            checkbox.dataset.column = col.class;
            checkbox.style.marginRight = '8px';
            checkbox.style.cursor = 'pointer';
            checkbox.style.accentColor = '#00f7ff';

            // Event listener untuk toggle kolom
            checkbox.addEventListener('change', function() {
                toggleSectionColumn(section, col.class, this.checked);
                saveColumnPreference(section, col.class, this.checked);
            });

            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(col.name));
            checkboxContainer.appendChild(label);
        });

        dropdownMenu.appendChild(checkboxContainer);

        // Tombol aksi
        const actionDiv = document.createElement('div');
        actionDiv.style.display = 'flex';
        actionDiv.style.justifyContent = 'space-between';
        actionDiv.style.gap = '5px';

        const showAllBtn = document.createElement('button');
        showAllBtn.textContent = 'All';
        showAllBtn.style.padding = '4px 8px';
        showAllBtn.style.background = 'transparent';
        showAllBtn.style.color = '#ffee32';
        showAllBtn.style.border = '1px solid #00f7ff';
        showAllBtn.style.borderRadius = '4px';
        showAllBtn.style.cursor = 'pointer';
        showAllBtn.style.fontSize = '11px';
        showAllBtn.onmouseover = function() {
            this.style.background = 'rgba(255, 238, 50, 0.2)';
        };
        showAllBtn.onmouseout = function() {
            this.style.background = 'transparent';
        };
        showAllBtn.onclick = function() {
            columnConfig[section].forEach(col => {
                toggleSectionColumn(section, col.class, true);
                saveColumnPreference(section, col.class, true);
                const checkbox = dropdownMenu.querySelector(`input[data-column="${col.class}"]`);
                if (checkbox) checkbox.checked = true;
            });
        };

        const hideAllBtn = document.createElement('button');
        hideAllBtn.textContent = 'None';
        hideAllBtn.style.padding = '4px 8px';
        hideAllBtn.style.background = 'transparent';
        hideAllBtn.style.color = '#ffee32';
        hideAllBtn.style.border = '1px solid #00f7ff';
        hideAllBtn.style.borderRadius = '4px';
        hideAllBtn.style.cursor = 'pointer';
        hideAllBtn.style.fontSize = '11px';
        hideAllBtn.onmouseover = function() {
            this.style.background = 'rgba(255, 238, 50, 0.2)';
        };
        hideAllBtn.onmouseout = function() {
            this.style.background = 'transparent';
        };
        hideAllBtn.onclick = function() {
            columnConfig[section].forEach(col => {
                toggleSectionColumn(section, col.class, false);
                saveColumnPreference(section, col.class, false);
                const checkbox = dropdownMenu.querySelector(`input[data-column="${col.class}"]`);
                if (checkbox) checkbox.checked = false;
            });
        };

        actionDiv.appendChild(showAllBtn);
        actionDiv.appendChild(hideAllBtn);
        dropdownMenu.appendChild(actionDiv);

        // Tambahkan dropdown ke body
        document.body.appendChild(dropdownMenu);

        // Posisikan dropdown di bawah tombol
        const rect = button.getBoundingClientRect();
        dropdownMenu.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        dropdownMenu.style.left = (rect.left + window.scrollX) + 'px';

        // Tutup dropdown ketika klik di luar
        const closeDropdown = function(e) {
            if (!dropdownMenu.contains(e.target) && e.target !== button) {
                dropdownMenu.remove();
                document.removeEventListener('click', closeDropdown);
            }
        };

        // Event listener untuk tutup dropdown
        document.addEventListener('click', closeDropdown);

        // Hentikan propagasi event pada dropdown
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    };

    // Fungsi untuk mengecek visibilitas kolom di section tertentu
    function isColumnVisible(section, columnClass) {
        // Cari section berdasarkan class (karena HTML menggunakan class, bukan ID)
        const sectionElement = document.querySelector(`.unfullfiled-section`);
        
        if (!sectionElement) return true;
        
        const firstCell = sectionElement.querySelector(`.${columnClass}`);
        if (!firstCell) return true;
        
        return window.getComputedStyle(firstCell).display !== 'none';
    }

    // Fungsi untuk toggle visibility kolom di section tertentu
    function toggleSectionColumn(section, columnClass, isVisible) {
        // Cari semua section
        const sections = document.querySelectorAll('.unfullfiled-section');
        
        sections.forEach(sectionElement => {
            const cells = sectionElement.querySelectorAll(`.${columnClass}`);
            cells.forEach(cell => {
                cell.style.display = isVisible ? '' : 'none';
            });
        });
    }

    // Simpan preferensi kolom ke localStorage
    function saveColumnPreference(section, columnClass, isVisible) {
        const key = `columnPreferences_${section}`;
        const preferences = JSON.parse(localStorage.getItem(key) || '{}');
        preferences[columnClass] = isVisible;
        localStorage.setItem(key, JSON.stringify(preferences));
    }

    // Load preferensi kolom dari localStorage
    function loadColumnPreferences(section) {
        const key = `columnPreferences_${section}`;
        const preferences = JSON.parse(localStorage.getItem(key) || '{}');
        
        Object.keys(preferences).forEach(columnClass => {
            toggleSectionColumn(section, columnClass, preferences[columnClass]);
        });
    }

    // Inisialisasi kolom untuk semua section saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Load preferensi untuk semua section yang ada
        ['staff', 'spv'].forEach(section => {
            loadColumnPreferences(section);
        });
        
        // Set default untuk kolom yang belum ada preferensinya
        initializeDefaultColumns();
    });

    // Fungsi untuk inisialisasi kolom default
    function initializeDefaultColumns() {
        ['staff', 'spv'].forEach(section => {
            const preferences = JSON.parse(localStorage.getItem(`columnPreferences_${section}`) || '{}');
            
            columnConfig[section].forEach(col => {
                if (preferences[col.class] === undefined) {
                    toggleSectionColumn(section, col.class, col.default);
                    saveColumnPreference(section, col.class, col.default);
                }
            });
        });
    }

    // CSS untuk dropdown
    const style = document.createElement('style');
    style.textContent = `
    .column-visibility-dropdown {
        font-family: inherit;
    }

    .column-visibility-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    .column-visibility-dropdown::-webkit-scrollbar-track {
        background: #333;
        border-radius: 3px;
    }
    .column-visibility-dropdown::-webkit-scrollbar-thumb {
        background: #00f7ff;
        border-radius: 3px;
    }
    .column-visibility-dropdown::-webkit-scrollbar-thumb:hover {
        background: #ffee32;
    }
    `;
    document.head.appendChild(style);

    // ==========================
    // FUNGSI EXPORT YANG DIPERBAIKI
    // ==========================

    // 1. Fungsi Export Utama dengan Perbaikan Struktur Kolom
    window.exportData = function(format, section) {
        const activeTab = document.querySelector(`.${section}-content .tab-content.active, .${section}-content .tab-content-spv.active`);
        if (!activeTab) {
            alert('Tidak ada data untuk diexport');
            return;
        }

        // Perbaikan selector checkbox untuk SPV
        const checkboxClass = section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox';
        const selectedRows = activeTab.querySelectorAll(`${checkboxClass}:checked`);
        
        if (selectedRows.length === 0) {
            if (!confirm('Tidak ada data yang dipilih. Export semua data?')) {
                return;
            }
            exportAllDataWithAdditionalColumns(format, section, activeTab);
            return;
        }

        switch(format) {
            case 'copy':
                exportSelectedWithAdditionalColumns(selectedRows, section, 'clipboard');
                break;
            case 'csv':
                exportSelectedWithAdditionalColumns(selectedRows, section, 'csv');
                break;
            case 'excel':
                exportSelectedWithAdditionalColumns(selectedRows, section, 'excel');
                break;
            case 'pdf':
                exportSelectedWithAdditionalColumns(selectedRows, section, 'pdf');
                break;
            case 'print':
                exportSelectedWithAdditionalColumns(selectedRows, section, 'print');
                break;
            default:
                alert('Format export tidak dikenali');
        }
    };

    // 2. Fungsi utama untuk export dengan perbaikan struktur kolom
    function exportSelectedWithAdditionalColumns(selectedRows, section, format) {
        let content = '';
        const dataRows = [];
        
        // Header dengan kolom tambahan
        const table = selectedRows[0].closest('table');
        const headers = table.querySelectorAll('th');
        
        // Buat header baru dengan tambahan "Nama" dan "Tanggal Laporan"
        const headerArray = ['Nama', 'Tanggal Laporan']; // Kolom tambahan di awal
        
        // Ambil header yang benar (tidak termasuk checkbox dan action)
        Array.from(headers).forEach(header => {
            // Skip kolom checkbox dan action
            if (!header.querySelector('input[type="checkbox"]') && 
                !header.textContent.includes('Action') &&
                !header.classList.contains('col-checkbox') &&
                !header.classList.contains('col-checkbox-spv') &&
                !header.classList.contains('col-action') &&
                !header.classList.contains('col-action-spv')) {
                headerArray.push(header.textContent.trim());
            }
        });
        
        // Format header berdasarkan format export
        if (format === 'csv' || format === 'excel') {
            content += headerArray.map(header => `"${header}"`).join(',') + '\r\n';
        } else {
            content += headerArray.join('\t') + '\n';
        }
        
        // Proses setiap baris yang dipilih
        selectedRows.forEach(checkbox => {
            const row = checkbox.closest('tr');
            
            // CARI ELEMEN YANG BENAR UNTUK NAMA DAN TANGGAL - DIPERBAIKI
            let userName = '';
            let reportDate = '';
            
            // Cari elemen accordion terdekat
            const accordion = row.closest('.user-accordion');
            if (accordion) {
                // Jika menggunakan accordion structure
                userName = accordion.querySelector('.user-name')?.textContent.trim() || '';
                reportDate = accordion.querySelector('.report-date')?.textContent.trim() || '';
            } else {
                // Fallback jika tidak ditemukan
                userName = 'Unknown';
                reportDate = new Date().toLocaleDateString('id-ID');
            }
            
            // Data baris dengan kolom tambahan
            const rowData = [userName, reportDate]; // Tambahkan nama dan tanggal di awal
            
            // Ambil data dari sel tabel - DIPERBAIKI untuk menghindari kolom kosong
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Skip checkbox dan tombol action
                if (!cell.querySelector('input[type="checkbox"]') && 
                    !cell.querySelector('.btn-approve') && 
                    !cell.querySelector('.btn-reject') &&
                    !cell.classList.contains('col-checkbox') &&
                    !cell.classList.contains('col-checkbox-spv') &&
                    !cell.classList.contains('col-action') &&
                    !cell.classList.contains('col-action-spv')) {
                    
                    // Untuk struktur ini, ambil teks langsung dari sel
                    rowData.push(cell.textContent.trim());
                }
            });
            
            dataRows.push(rowData);
            
            // Format baris berdasarkan format export
            if (format === 'csv' || format === 'excel') {
                content += rowData.map(cell => `"${cell}"`).join(',') + '\r\n';
            } else {
                content += rowData.join('\t') + '\n';
            }
        });
        
        // Export berdasarkan format yang diminta
        switch(format) {
            case 'clipboard':
                exportContentToClipboard(content, selectedRows.length, section);
                break;
            case 'csv':
                exportContentToCSV(content, selectedRows.length, section, 'csv');
                break;
            case 'excel':
                exportContentToCSV(content, selectedRows.length, section, 'xls');
                break;
            case 'pdf':
                exportContentToPDF(content, headerArray, dataRows, selectedRows.length, section);
                break;
            case 'print':
                exportContentToPrint(content, headerArray, dataRows, selectedRows.length, section);
                break;
        }
    }

    // 3. Export ke PDF dengan perbaikan tampilan
    function exportContentToPDF(content, headers, dataRows, count, section) {
        // Buat window untuk print (user bisa save as PDF)
        const printWindow = window.open('', '_blank', 'width=1000,height=600');
        
        // Buat konten HTML untuk PDF
        let pdfContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan ${section.toUpperCase()} - PDF</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 20px; 
                        font-size: 14px;
                    }
                    h2 { color: #333; text-align: center; }
                    .pdf-info { 
                        margin-bottom: 20px; 
                        padding-bottom: 10px;
                        border-bottom: 1px solid #ddd;
                        text-align: center;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 20px;
                        font-size: 12px;
                    }
                    th, td { 
                        border: 1px solid #ddd; 
                        padding: 8px; 
                        text-align: left; 
                    }
                    th { 
                        background-color: #f2f2f2; 
                        font-weight: bold;
                    }
                    @media print {
                        body { margin: 15mm; }
                        table { font-size: 10px; }
                        .no-print { display: none; }
                    }
                    .pdf-footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <h2>LAPORAN ${section.toUpperCase()}</h2>
                <div class="pdf-info">
                    <p><strong>Tanggal Export:</strong> ${new Date().toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    })}</p>
                    <p><strong>Jumlah Data:</strong> ${count} laporan</p>
                    <p><strong>Diexport oleh:</strong> ${userName}</p>
                </div>
                <table>
                    <thead>
                        <tr>
        `;
        
        // Header
        headers.forEach(header => {
            pdfContent += `<th>${header}</th>`;
        });
        
        pdfContent += `
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Data rows
        dataRows.forEach(rowData => {
            pdfContent += `<tr>`;
            rowData.forEach(cell => {
                pdfContent += `<td>${cell}</td>`;
            });
            pdfContent += `</tr>`;
        });
        
        pdfContent += `
                    </tbody>
                </table>
                <div class="pdf-footer">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                </div>
                <div class="no-print" style="margin-top: 30px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        🖨️ Cetak / Save sebagai PDF
                    </button>
                    <p style="color: #666; font-size: 12px; margin-top: 10px;">
                        Klik tombol di atas lalu pilih "Save as PDF" di dialog print browser Anda.
                    </p>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(pdfContent);
        printWindow.document.close();
        
        // Auto print setelah window terbuka
        setTimeout(() => {
            printWindow.print();
        }, 1000);
    }

    // 4. Export ke Print dengan perbaikan tampilan
    function exportContentToPrint(content, headers, dataRows, count, section) {
        // Buat window print
        const printWindow = window.open('', '_blank', 'width=1000,height=600');
        
        // Buat konten HTML untuk print
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak Laporan ${section}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 15mm; 
                        font-size: 14px;
                    }
                    h2 { color: #333; text-align: center; }
                    .print-info { 
                        margin-bottom: 20px; 
                        padding-bottom: 10px;
                        border-bottom: 1px solid #ddd;
                        text-align: center;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 20px;
                        font-size: 12px;
                    }
                    th, td { 
                        border: 1px solid #ddd; 
                        padding: 6px; 
                        text-align: left; 
                    }
                    th { 
                        background-color: #f2f2f2; 
                        font-weight: bold;
                    }
                    @media print {
                        body { margin: 15mm; }
                        table { font-size: 10px; }
                        .no-print { display: none; }
                    }
                    .print-footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <h2>LAPORAN ${section.toUpperCase()}</h2>
                <div class="print-info">
                    <p><strong>Tanggal Export:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
                    <p><strong>Jumlah Data:</strong> ${count} laporan</p>
                    <p><strong>Diexport oleh:</strong> ${userName}</p>
                </div>
                <table>
                    <thead>
                        <tr>
        `;
        
        // Header
        headers.forEach(header => {
            printContent += `<th>${header}</th>`;
        });
        
        printContent += `
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Data rows
        dataRows.forEach(rowData => {
            printContent += `<tr>`;
            rowData.forEach(cell => {
                printContent += `<td>${cell}</td>`;
            });
            printContent += `</tr>`;
        });
        
        printContent += `
                    </tbody>
                </table>
                <div class="print-footer">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                </div>
                <div class="no-print" style="margin-top: 30px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        🖨️ Cetak Sekarang
                    </button>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Auto print setelah window terbuka
        setTimeout(() => {
            printWindow.print();
        }, 1000);
    }

    // 5. Fungsi untuk export semua data dengan kolom tambahan
    function exportAllDataWithAdditionalColumns(format, section, activeTab) {
        // Perbaikan selector checkbox untuk SPV
        const checkboxClass = section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox';
        const allRows = activeTab.querySelectorAll(`${checkboxClass}`);
        
        // Panggil fungsi export dengan semua data
        let exportFormat = 'csv';
        if (format === 'excel') exportFormat = 'excel';
        if (format === 'pdf') exportFormat = 'pdf';
        if (format === 'print') exportFormat = 'print';
        
        exportSelectedWithAdditionalColumns(allRows, section, exportFormat);
    }

    // 6. Export ke Print dengan kolom tambahan (DIPERBAIKI)
    function exportContentToPrint(content, headers, dataRows, count, section) {
        // Buat window print
        const printWindow = window.open('', '_blank', 'width=1000,height=600');
        
        // Buat konten HTML untuk print
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak Laporan ${section}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 15mm; 
                        font-size: 14px;
                    }
                    h2 { color: #333; text-align: center; }
                    .print-info { 
                        margin-bottom: 20px; 
                        padding-bottom: 10px;
                        border-bottom: 1px solid #ddd;
                        text-align: center;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 20px;
                        font-size: 12px;
                    }
                    th, td { 
                        border: 1px solid #ddd; 
                        padding: 6px; 
                        text-align: left; 
                    }
                    th { 
                        background-color: #f2f2f2; 
                        font-weight: bold;
                    }
                    @media print {
                        body { margin: 15mm; }
                        table { font-size: 10px; }
                        .no-print { display: none; }
                    }
                    .print-footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <h2>LAPORAN ${section.toUpperCase()}</h2>
                <div class="print-info">
                    <p><strong>Tanggal Export:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
                    <p><strong>Jumlah Data:</strong> ${count} laporan</p>
                    <p><strong>Diexport oleh:</strong> ${userName}</p>
                </div>
                <table>
                    <thead>
                        <tr>
        `;
        
        // Header
        headers.forEach(header => {
            printContent += `<th>${header}</th>`;
        });
        
        printContent += `
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Data rows
        dataRows.forEach(rowData => {
            printContent += `<tr>`;
            rowData.forEach(cell => {
                printContent += `<td>${cell}</td>`;
            });
            printContent += `</tr>`;
        });
        
        printContent += `
                    </tbody>
                </table>
                <div class="print-footer">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                </div>
                <div class="no-print" style="margin-top: 30px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        🖨️ Cetak Sekarang
                    </button>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Auto print setelah window terbuka
        setTimeout(() => {
            printWindow.print();
        }, 1000);
    }

    // 7. Fungsi untuk export semua data dengan kolom tambahan
    function exportAllDataWithAdditionalColumns(format, section, activeTab) {
        // Perbaikan selector checkbox untuk SPV
        const checkboxClass = section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox';
        const allRows = activeTab.querySelectorAll(`${checkboxClass}`);
        
        // Panggil fungsi export dengan semua data
        let exportFormat = 'csv';
        if (format === 'excel') exportFormat = 'excel';
        if (format === 'pdf') exportFormat = 'pdf';
        if (format === 'print') exportFormat = 'print';
        
        exportSelectedWithAdditionalColumns(allRows, section, exportFormat);
    }

    // ==========================
    // FUNGSI ORIGINAL (untuk kompatibilitas)
    // ==========================

    function exportToClipboard(activeTab, section) {
        const allRows = activeTab.querySelectorAll(section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox');
        if (allRows.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }
        exportSelectedWithAdditionalColumns(allRows, section, 'clipboard');
    }

    function exportToCSV(activeTab, section) {
        const allRows = activeTab.querySelectorAll(section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox');
        if (allRows.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }
        exportSelectedWithAdditionalColumns(allRows, section, 'csv');
    }

    function exportToExcel(activeTab, section) {
        const allRows = activeTab.querySelectorAll(section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox');
        if (allRows.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }
        exportSelectedWithAdditionalColumns(allRows, section, 'excel');
    }

    function exportToPDF(activeTab, section) {
        const allRows = activeTab.querySelectorAll(section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox');
        if (allRows.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }
        exportSelectedWithAdditionalColumns(allRows, section, 'pdf');
    }

    function exportToPrint(activeTab, section) {
        const allRows = activeTab.querySelectorAll(section === 'spv' ? '.row-checkbox-spv' : '.row-checkbox');
        if (allRows.length === 0) {
            alert('Tidak ada data untuk dicetak');
            return;
        }
        exportSelectedWithAdditionalColumns(allRows, section, 'print');
    }


    // ======================================================
    // ================= STAFF SECTION ======================
    // ======================================================

    // === Inisialisasi checkbox: pilih semua di dalam accordion (STAFF)
    function initCheckboxes(tab) {
        // Header select-all staff - UNTUK SEMUA ROLE
        tab.querySelectorAll(".select-all-accordion").forEach(selectAll => {
            selectAll.onchange = function () {
                const accordion = this.closest(".user-accordion");
                const checkboxes = accordion.querySelectorAll(".row-checkbox");
                checkboxes.forEach(cb => cb.checked = this.checked);
            };
        });

        // Sinkronisasi row → header staff - UNTUK SEMUA ROLE
        tab.querySelectorAll(".user-accordion").forEach(accordion => {
            const headerSelectAll = accordion.querySelector(".select-all-accordion");
            const rowCheckboxes = accordion.querySelectorAll(".row-checkbox");
            rowCheckboxes.forEach(cb => {
                cb.onchange = () => {
                    if (!headerSelectAll) return;
                    const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                    headerSelectAll.checked = allChecked;
                };
            });
        });
    }

    // === Update hasil filter + pagination untuk STAFF
    function updateResults() {
        const searchValue = document.getElementById('searchInputStaff')?.value.toLowerCase() || "";
        const selectedDate = document.getElementById('dateSearchStaff')?.value || "";

        document.querySelectorAll('.staff-content .tab-content').forEach(tab => {
            const accordions = Array.from(tab.querySelectorAll('.user-accordion'));
            const noResultMessage = tab.querySelector('.noResultMessage');
            const pagination = tab.querySelector('.pagination');
            const paginationInfo = pagination?.querySelector('span');

            // Filter nama + tanggal
            let filtered = accordions.filter(acc => {
                const userName = acc.querySelector('.user-name')?.textContent.toLowerCase() || "";
                const reportDate = acc.querySelector('.report-date')?.textContent.trim() || "";
                let matchName = !searchValue || userName.includes(searchValue);
                let matchDate = true;
                if (selectedDate) {
                    const [year, month, day] = selectedDate.split("-");
                    const formattedDate = `${day}-${month}-${year}`;
                    matchDate = (reportDate === formattedDate);
                }
                
                // Filter berdasarkan role
                if (userRoleId == 3) { // Staff hanya bisa melihat laporannya sendiri
                    return matchName && matchDate && userName === userName.toLowerCase();
                }
                
                return matchName && matchDate;
            });

            // Sembunyikan semua accordion dulu
            accordions.forEach(acc => acc.style.display = "none");

            // Jika tidak ada data yang cocok
            if (filtered.length === 0) {
                if (noResultMessage) {
                    const tabName = tab.id.charAt(0).toUpperCase() + tab.id.slice(1);
                    if (searchValue && selectedDate) {
                        const [year, month, day] = selectedDate.split("-");
                        noResultMessage.textContent = `Data user "${searchValue}" pada ${day}/${month}/${year} tidak tersedia di ${tabName}`;
                    } else if (searchValue) {
                        noResultMessage.textContent = `Data user "${searchValue}" tidak tersedia di ${tabName}`;
                    } else if (selectedDate) {
                        const [year, month, day] = selectedDate.split("-");
                        noResultMessage.textContent = `Data pada ${day}/${month}/${year} tidak tersedia di ${tabName}`;
                    } else {
                        noResultMessage.textContent = `Tidak ada data di ${tabName}`;
                    }
                    noResultMessage.style.display = "block";
                }
                if (pagination) pagination.style.display = "none";
                return;
            } else {
                if (noResultMessage) noResultMessage.style.display = "none";
                if (pagination) pagination.style.display = "flex";
            }

            // Hitung pagination
            const tabId = tab.id;
            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            if (currentPage[tabId] > totalPages) currentPage[tabId] = 1;

            const startIndex = (currentPage[tabId] - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;

            filtered.slice(startIndex, endIndex).forEach(acc => acc.style.display = "block");

            // Update teks info
            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalItems)} of ${totalItems} entries`;
            }

            // Update tombol navigasi pagination
            const nav = pagination?.querySelector('.pagination-nav');
            if (nav) {
                nav.innerHTML = "";

                // tombol Prev
                const prev = document.createElement("a");
                prev.textContent = "Previous";
                prev.href = "#";
                if (currentPage[tabId] === 1) prev.classList.add("disabled");
                prev.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[tabId] > 1) {
                        currentPage[tabId]--;
                        updateResults();
                    }
                });
                nav.appendChild(prev);

                // angka halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement("a");
                    pageLink.textContent = i;
                    pageLink.href = "#";
                    if (i === currentPage[tabId]) pageLink.classList.add("active");
                    pageLink.addEventListener("click", e => {
                        e.preventDefault();
                        currentPage[tabId] = i;
                        updateResults();
                    });
                    nav.appendChild(pageLink);
                }

                // tombol Next
                const next = document.createElement("a");
                next.textContent = "Next";
                next.href = "#";
                if (currentPage[tabId] === totalPages) next.classList.add("disabled");
                next.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[tabId] < totalPages) {
                        currentPage[tabId]++;
                        updateResults();
                    }
                });
                nav.appendChild(next);
            }

            // Aktifkan ulang checkbox staff
            initCheckboxes(tab);
        });
    }

    // ==========================
    // FUNGSI UPDATE RESULTS DENGAN PAGINATION YANG TETAP MUNCUL
    // ==========================

    function updateResults() {
        const searchValue = document.getElementById('searchInputStaff')?.value.toLowerCase() || "";
        const selectedDate = document.getElementById('dateSearchStaff')?.value || "";

        document.querySelectorAll('.staff-content .tab-content').forEach(tab => {
            const accordions = Array.from(tab.querySelectorAll('.user-accordion'));
            const noResultMessage = tab.querySelector('.noResultMessage');
            const pagination = tab.querySelector('.pagination');
            const paginationInfo = pagination?.querySelector('span');
            const paginationNav = pagination?.querySelector('.pagination-nav');

            // Filter nama + tanggal
            let filtered = accordions.filter(acc => {
                const userName = acc.querySelector('.user-name')?.textContent.toLowerCase() || "";
                const reportDate = acc.querySelector('.report-date')?.textContent.trim() || "";
                let matchName = !searchValue || userName.includes(searchValue);
                let matchDate = true;
                
                if (selectedDate) {
                    const [year, month, day] = selectedDate.split("-");
                    const formattedDate = `${day}-${month}-${year}`;
                    matchDate = (reportDate === formattedDate);
                }
                
                return matchName && matchDate;
            });

            // Sembunyikan semua accordion dulu
            accordions.forEach(acc => acc.style.display = "none");

            // Jika tidak ada data yang cocok
            if (filtered.length === 0) {
                if (noResultMessage) {
                    const tabName = tab.id.charAt(0).toUpperCase() + tab.id.slice(1);
                    if (searchValue && selectedDate) {
                        const [year, month, day] = selectedDate.split("-");
                        noResultMessage.textContent = `Data user "${searchValue}" pada ${day}/${month}/${year} tidak tersedia di ${tabName}`;
                    } else if (searchValue) {
                        noResultMessage.textContent = `Data user "${searchValue}" tidak tersedia di ${tabName}`;
                    } else if (selectedDate) {
                        const [year, month, day] = selectedDate.split("-");
                        noResultMessage.textContent = `Data pada ${day}/${month}/${year} tidak tersedia di ${tabName}`;
                    } else {
                        noResultMessage.textContent = `Tidak ada data di ${tabName}`;
                    }
                    noResultMessage.style.display = "block";
                }
                
                // TETAP TAMPILKAN PAGINATION MESKI TIDAK ADA DATA
                if (pagination) {
                    pagination.style.display = "flex";
                    if (paginationInfo) {
                        paginationInfo.textContent = `Showing 0 to 0 of 0 entries`;
                    }
                    if (paginationNav) {
                        paginationNav.innerHTML = `
                            <a href="#" class="disabled" style="pointer-events: none; opacity: 0.5;">Previous</a>
                            <a href="#" class="active" style="font-weight: bold;">1</a>
                            <a href="#" class="disabled" style="pointer-events: none; opacity: 0.5;">Next</a>
                        `;
                    }
                }
                return;
            } else {
                if (noResultMessage) noResultMessage.style.display = "none";
                if (pagination) pagination.style.display = "flex";
            }

            // Hitung pagination
            const tabId = tab.id;
            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            if (currentPage[tabId] > totalPages) currentPage[tabId] = 1;

            const startIndex = (currentPage[tabId] - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;

            filtered.slice(startIndex, endIndex).forEach(acc => acc.style.display = "block");

            // Update teks info
            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalItems)} of ${totalItems} entries`;
            }

            // Update tombol navigasi pagination
            if (paginationNav) {
                paginationNav.innerHTML = "";

                // tombol Prev
                const prev = document.createElement("a");
                prev.href = "#";
                prev.textContent = "Previous";
                if (currentPage[tabId] === 1) {
                    prev.classList.add("disabled");
                    prev.style.pointerEvents = "none";
                    prev.style.opacity = "0.5";
                }
                prev.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[tabId] > 1) {
                        currentPage[tabId]--;
                        updateResults();
                    }
                });
                paginationNav.appendChild(prev);

                // angka halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement("a");
                    pageLink.href = "#";
                    pageLink.textContent = i;
                    if (i === currentPage[tabId]) {
                        pageLink.classList.add("active");
                        pageLink.style.fontWeight = "bold";
                    }
                    pageLink.addEventListener("click", e => {
                        e.preventDefault();
                        currentPage[tabId] = i;
                        updateResults();
                    });
                    paginationNav.appendChild(pageLink);
                }

                // tombol Next
                const next = document.createElement("a");
                next.href = "#";
                next.textContent = "Next";
                if (currentPage[tabId] === totalPages) {
                    next.classList.add("disabled");
                    next.style.pointerEvents = "none";
                    next.style.opacity = "0.5";
                }
                next.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[tabId] < totalPages) {
                        currentPage[tabId]++;
                        updateResults();
                    }
                });
                paginationNav.appendChild(next);
            }

            // Aktifkan ulang checkbox staff
            initCheckboxes(tab);
        });
    }

    // Tab Switching untuk STAFF
    const tabButtons = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".staff-content .tab-content");
    tabButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            tabButtons.forEach(b => b.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));
            btn.classList.add("active");
            document.getElementById(btn.dataset.tab).classList.add("active");
            updateResults();
        });
    });

    // Event Listener untuk search staff
    document.getElementById('searchInputStaff')?.addEventListener('input', () => {
        currentPage.pending = 1;
        currentPage.approved = 1;
        currentPage.all = 1;
        updateResults();
    });
    document.getElementById('dateSearchStaff')?.addEventListener('input', () => {
        currentPage.pending = 1;
        currentPage.approved = 1;
        currentPage.all = 1;
        updateResults();
    });

    // ======================================================
    // ================== SPV SECTION =======================
    // ======================================================

    // === Inisialisasi checkbox: pilih semua di dalam accordion (SPV)
    function initSpvCheckboxes(tab) {
        // Header select-all SPV
        tab.querySelectorAll(".select-all-accordion-spv").forEach(selectAll => {
            selectAll.onchange = function () {
                const accordion = this.closest(".user-accordion");
                const checkboxes = accordion.querySelectorAll(".row-checkbox-spv"); // Perbaikan di sini
                checkboxes.forEach(cb => cb.checked = this.checked);
            };
        });

        // Sinkronisasi row → header SPV
        tab.querySelectorAll(".user-accordion").forEach(accordion => {
            const headerSelectAll = accordion.querySelector(".select-all-accordion-spv");
            const rowCheckboxes = accordion.querySelectorAll(".row-checkbox-spv"); // Perbaikan di sini
            
            rowCheckboxes.forEach(cb => {
                cb.onchange = () => {
                    if (!headerSelectAll) return;
                    const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                    headerSelectAll.checked = allChecked;
                };
            });
        });
    }

    // === Update hasil filter + pagination untuk SPV
    function updateSpvResults() {
        const selectedDate = document.getElementById('dateSearchSpv')?.value || "";

        document.querySelectorAll('.spv-content .tab-content-spv').forEach(tab => {
            const accordions = Array.from(tab.querySelectorAll('.user-accordion'));
            const noResultMessage = tab.querySelector('.noResultMessage');
            const pagination = tab.querySelector('.pagination');
            const paginationInfo = pagination?.querySelector('span');

            // Filter hanya tanggal
            let filtered = accordions.filter(acc => {
                const reportDate = acc.querySelector('.report-date')?.textContent.trim() || "";
                if (!selectedDate) return true;
                const [year, month, day] = selectedDate.split("-");
                const formattedDate = `${day}-${month}-${year}`;
                
                // Filter berdasarkan role
                if (userRoleId == 2) { // SPV hanya bisa melihat laporannya sendiri
                    const userName = acc.querySelector('.user-name')?.textContent.toLowerCase() || "";
                    return reportDate === formattedDate && userName === userName.toLowerCase();
                }
                
                return reportDate === formattedDate;
            });

            // Sembunyikan semua accordion dulu
            accordions.forEach(acc => acc.style.display = "none");

            if (filtered.length === 0) {
                if (noResultMessage) {
                    const tabName = tab.id.includes("pending") ? "Pending" : "Approved";
                    const [year, month, day] = selectedDate.split("-");
                    noResultMessage.textContent = `Tidak ada laporan SPV pada ${day}-${month}-${year} di ${tabName}`;
                    noResultMessage.style.display = "block";
                }
                if (pagination) pagination.style.display = "none";
                return;
            } else {
                if (noResultMessage) noResultMessage.style.display = "none";
                if (pagination) pagination.style.display = "flex";
            }

            // Hitung pagination
            const tabId = tab.id;
            const key = tabId === "spv-pending" ? "spvPending" : "spvApproved";
            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            if (currentPage[key] > totalPages) currentPage[key] = 1;

            const startIndex = (currentPage[key] - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;

            filtered.slice(startIndex, endIndex).forEach(acc => acc.style.display = "block");

            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalItems)} of ${totalItems} entries`;
            }

            // Navigasi pagination
            const nav = pagination?.querySelector('.pagination-nav');
            if (nav) {
                nav.innerHTML = "";

                // tombol Prev
                const prev = document.createElement("a");
                prev.textContent = "Previous";
                prev.href = "#";
                if (currentPage[key] === 1) prev.classList.add("disabled");
                prev.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[key] > 1) {
                        currentPage[key]--;
                        updateSpvResults();
                    }
                });
                nav.appendChild(prev);

                // angka halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement("a");
                    pageLink.textContent = i;
                    pageLink.href = "#";
                    if (i === currentPage[key]) pageLink.classList.add("active");
                    pageLink.addEventListener("click", e => {
                        e.preventDefault();
                        currentPage[key] = i;
                        updateSpvResults();
                    });
                    nav.appendChild(pageLink);
                }

                // tombol Next
                const next = document.createElement("a");
                next.textContent = "Next";
                next.href = "#";
                if (currentPage[key] === totalPages) next.classList.add("disabled");
                next.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[key] < totalPages) {
                        currentPage[key]++;
                        updateSpvResults();
                    }
                });
                nav.appendChild(next);
            }

            // Aktifkan ulang checkbox SPV
            initSpvCheckboxes(tab);
        });
    }


    // UPDATE SPV RESULTS DENGAN PAGINATION YANG TETAP MUNCUL
    function updateSpvResults() {
        const selectedDate = document.getElementById('dateSearchSpv')?.value || "";

        document.querySelectorAll('.spv-content .tab-content-spv').forEach(tab => {
            const accordions = Array.from(tab.querySelectorAll('.user-accordion'));
            const noResultMessage = tab.querySelector('.noResultMessage');
            const pagination = tab.querySelector('.pagination');
            const paginationInfo = pagination?.querySelector('span');
            const paginationNav = pagination?.querySelector('.pagination-nav');

            // Filter hanya tanggal
            let filtered = accordions.filter(acc => {
                const reportDate = acc.querySelector('.report-date')?.textContent.trim() || "";
                if (!selectedDate) return true;
                const [year, month, day] = selectedDate.split("-");
                const formattedDate = `${day}-${month}-${year}`;
                return reportDate === formattedDate;
            });

            // Sembunyikan semua accordion dulu
            accordions.forEach(acc => acc.style.display = "none");

            if (filtered.length === 0) {
                if (noResultMessage) {
                    const tabName = tab.id.includes("pending") ? "Pending" : "Approved";
                    const [year, month, day] = selectedDate.split("-");
                    noResultMessage.textContent = `Tidak ada laporan SPV pada ${day}-${month}-${year} di ${tabName}`;
                    noResultMessage.style.display = "block";
                }
                
                // TETAP TAMPILKAN PAGINATION MESKI TIDAK ADA DATA
                if (pagination) {
                    pagination.style.display = "flex";
                    if (paginationInfo) {
                        paginationInfo.textContent = `Showing 0 to 0 of 0 entries`;
                    }
                    if (paginationNav) {
                        paginationNav.innerHTML = `
                            <a href="#" class="disabled" style="pointer-events: none; opacity: 0.5;">Previous</a>
                            <a href="#" class="active" style="font-weight: bold;">1</a>
                            <a href="#" class="disabled" style="pointer-events: none; opacity: 0.5;">Next</a>
                        `;
                    }
                }
                return;
            } else {
                if (noResultMessage) noResultMessage.style.display = "none";
                if (pagination) pagination.style.display = "flex";
            }

            // Hitung pagination
            const tabId = tab.id;
            const key = tabId === "spv-pending" ? "spvPending" : "spvApproved";
            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            if (currentPage[key] > totalPages) currentPage[key] = 1;

            const startIndex = (currentPage[key] - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;

            filtered.slice(startIndex, endIndex).forEach(acc => acc.style.display = "block");

            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalItems)} of ${totalItems} entries`;
            }

            // Navigasi pagination
            if (paginationNav) {
                paginationNav.innerHTML = "";

                // tombol Prev
                const prev = document.createElement("a");
                prev.href = "#";
                prev.textContent = "Previous";
                if (currentPage[key] === 1) {
                    prev.classList.add("disabled");
                    prev.style.pointerEvents = "none";
                    prev.style.opacity = "0.5";
                }
                prev.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[key] > 1) {
                        currentPage[key]--;
                        updateSpvResults();
                    }
                });
                paginationNav.appendChild(prev);

                // angka halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement("a");
                    pageLink.href = "#";
                    pageLink.textContent = i;
                    if (i === currentPage[key]) {
                        pageLink.classList.add("active");
                        pageLink.style.fontWeight = "bold";
                    }
                    pageLink.addEventListener("click", e => {
                        e.preventDefault();
                        currentPage[key] = i;
                        updateSpvResults();
                    });
                    paginationNav.appendChild(pageLink);
                }

                // tombol Next
                const next = document.createElement("a");
                next.href = "#";
                next.textContent = "Next";
                if (currentPage[key] === totalPages) {
                    next.classList.add("disabled");
                    next.style.pointerEvents = "none";
                    next.style.opacity = "0.5";
                }
                next.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentPage[key] < totalPages) {
                        currentPage[key]++;
                        updateSpvResults();
                    }
                });
                paginationNav.appendChild(next);
            }

            // Aktifkan ulang checkbox SPV
            initSpvCheckboxes(tab);
        });
    }

    // Tab Switching untuk SPV
    const tabButtonsSpv = document.querySelectorAll(".tab-btn-spv");
    const tabContentsSpv = document.querySelectorAll(".spv-content .tab-content-spv");
    tabButtonsSpv.forEach(btn => {
        btn.addEventListener("click", () => {
            tabButtonsSpv.forEach(b => b.classList.remove("active"));
            tabContentsSpv.forEach(c => c.classList.remove("active"));
            btn.classList.add("active");
            document.getElementById(btn.dataset.tab).classList.add("active");
            updateSpvResults();
        });
    });

    // Event Listener untuk filter tanggal SPV
    document.getElementById('dateSearchSpv')?.addEventListener('input', () => {
        currentPage.spvPending = 1;
        currentPage.spvApproved = 1;
        updateSpvResults();
    });

    // Inisialisasi awal
    initBulkActions(); // Inisialisasi bulk actions
    updateResults();    // staff
    updateSpvResults(); // spv
});


    