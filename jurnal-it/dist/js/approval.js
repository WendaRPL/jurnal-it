$(document).ready(function () {
    // ==================================================
    // HELPER FUNCTIONS
    // ==================================================
    function todayStr() {
        const t = new Date();
        return t.toISOString().split('T')[0]; // yyyy-mm-dd
    }

    function parseDate(str) {
        if (!str) return null;
        const parts = str.split('-');
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function hhmmToMin(hhmm) {
        if (!hhmm) return null;
        const [h, m] = hhmm.split(':').map(Number);
        return h * 60 + (m || 0);
    }

    // ==================================================
    // INIT APPROVAL TABLE
    // ==================================================
    const table = $('#approvalTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[2, 'desc']],
        dom: "<'controls-row'<'dataTables_length'l><'dataTables_filter'f>>" +
             "rt<'bottom'<'dataTables_info'i><'dataTables_paginate'p>>",
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
            paginate: { first: "Pertama", last: "Terakhir", next: "›", previous: "‹" }
        },
        columnDefs: [{ targets: [0, 10], orderable: false }],
        createdRow: function (row, data) {
            // hitung durasi otomatis
            const start = data[3], end = data[4];
            let durasiText = '-';
            if (start && end && typeof start === 'string' && typeof end === 'string') {
                const [sh, sm] = start.split(':').map(Number);
                const [eh, em] = end.split(':').map(Number);
                if (!isNaN(sh) && !isNaN(eh)) {
                    let minutes = (eh * 60 + (em || 0)) - (sh * 60 + (sm || 0));
                    if (minutes < 0) minutes = 0;
                    const h = String(Math.floor(minutes / 60)).padStart(2, '0');
                    const m = String(minutes % 60).padStart(2, '0');
                    durasiText = h + ':' + m;
                }
            }
            $('td:eq(5)', row).html(durasiText);
        }
    });

    $("#approvalTable_length select").attr({ id: "approvalTable_length_select", name: "approvalTable_length" });
    $("#approvalTable_filter input").attr({ id: "approvalTable_search", name: "approvalTable_search" });

    // ==================================================
    // INIT CATATAN TABLE
    // ==================================================
    let catatanTable = null;
    try {
        catatanTable = $('#catatanTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[2, 'desc']],
            dom: "<'controls-row'<'dataTables_length'l><'catatan-date-filter'><'dataTables_filter'f>>" +
                 "rt<'bottom'<'dataTables_info'i><'dataTables_paginate'p>>",
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                paginate: { first: "Pertama", last: "Terakhir", next: "›", previous: "‹" }
            },
            columnDefs: [{ targets: [0, 4], orderable: false }]
        });
    } catch (err) {
        console.warn("catatanTable init failed:", err);
    }

    $("#catatanTable_length select").attr({ id: "catatanTable_length_select", name: "catatanTable_length" });
    $("#catatanTable_filter input").attr({ id: "catatanTable_search", name: "catatanTable_search" });

    // ==================================================
    // FILTER LOGIC (APPROVAL)
    // ==================================================
    const filters = {
        dateFrom: '',
        dateTo: '',
        timeFrom: '',
        timeTo: '',
        type: '',
        planned: []
    };

    // ambil user dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const filterUser = urlParams.get('user'); // user ID dari URL
    const today = todayStr();

    // set default filter tanggal
    if (!filterUser) {
        // kalau tidak ada user → default today
        $('#filterDateStart,#filterDateEnd').val(today);
        filters.dateFrom = filters.dateTo = today;
    } else {
        // kalau ada user → kosongkan tanggal, tampil semua
        $('#filterDateStart,#filterDateEnd').val('');
        filters.dateFrom = filters.dateTo = '';
    }

    function customFilter(settings, data, dataIndex) {
        if (settings.nTable.id !== 'approvalTable') return true;

        const rowData = table.row(dataIndex).data();
        if (!rowData) return true;

        const tanggal = rowData[2], start = rowData[3], end = rowData[4];
        const tipe = (rowData[7] || '').toString().trim();
        const terencana = (rowData[8] || '').toString().trim().toLowerCase();
        const planned = filters.planned.map(v => v.toLowerCase());

        // =====================
        // FILTER DATE (hanya kalau tidak filter user)
        // =====================
        if (!filterUser) {
            if (filters.dateFrom) {
                const d = parseDate(tanggal), f = parseDate(filters.dateFrom);
                if (d && f && d < f) return false;
            }
            if (filters.dateTo) {
                const d = parseDate(tanggal), t = parseDate(filters.dateTo);
                if (d && t && d > t) return false;
            }
        }

        // FILTER TIME
        if (filters.timeFrom) {
            const s = hhmmToMin(start), f = hhmmToMin(filters.timeFrom);
            if (s !== null && f !== null && s < f) return false;
        }
        if (filters.timeTo) {
            const e = hhmmToMin(end), t = hhmmToMin(filters.timeTo);
            if (e !== null && t !== null && e > t) return false;
        }

        // FILTER TIPE
        if (filters.type && tipe !== filters.type) return false;

        // FILTER TERENCANA
        if (planned.length > 0 && !planned.includes(terencana)) return false;

        return true;
    }

    if (!$.fn.dataTable.ext.search._customFilterAdded) {
        $.fn.dataTable.ext.search.push(customFilter);
        $.fn.dataTable.ext.search._customFilterAdded = true;
    }

    // ==================================================
    // UPDATE STATS (laporan harian)
    // ==================================================
    function updateStats() {
        const rows = table.rows({ search: 'applied' }).nodes();
        $('#stat-total').text(rows.length);
        $('#stat-pending').text($(rows).find('.status-pending').length || 0);
        $('#stat-approved').text($(rows).find('.status-approved').length || 0);
        $('#stat-rejected').text($(rows).find('.status-rejected').length || 0);
    }
    updateStats();
    table.on('draw', updateStats);

    // ==================================================
    // FILTER PANEL APPROVAL
    // ==================================================
    const panel = $('#filterPanel');
    const toggleBtn = $('#toggleFilterBtn');
    const closeBtn = $('.modal-close');

    toggleBtn.click(e => { e.stopPropagation(); panel.toggleClass('closed'); });
    closeBtn.click(e => { e.stopPropagation(); panel.addClass('closed'); });

    $('#applyFilter').click(function () {
        filters.dateFrom = $('#filterDateStart').val();
        filters.dateTo = $('#filterDateEnd').val();
        filters.timeFrom = $('#filterTimeStart').val();
        filters.timeTo = $('#filterTimeEnd').val();
        filters.type = $('#filterTipe').val();
        filters.planned = $('.filter-terencana:checked').map(function () {
            return $(this).val();
        }).get();
        table.draw();
    });

    $('#resetFilter').click(function () {
        $('#filterDateStart,#filterDateEnd,#filterTimeStart,#filterTimeEnd,#filterTipe').val('');
        $('.filter-terencana').prop('checked', false);
        if (!filterUser) {
            // default today
            $('#filterDateStart,#filterDateEnd').val(today);
            filters.dateFrom = filters.dateTo = today;
        } else {
            // kosongkan semua
            $('#filterDateStart,#filterDateEnd').val('');
            filters.dateFrom = filters.dateTo = '';
        }
        filters.timeFrom = filters.timeTo = filters.type = '';
        filters.planned = [];
        table.draw();
    });

    // ==================================================
    // INLINE EDIT
    // ==================================================
    $(document).on('dblclick', '.editable', function () {
        const cell = table.cell(this);
        if ($(this).find('textarea').length) return;

        const original = cell.data();
        const input = $('<textarea>')
            .val(original)
            .css({
                width: '100%',
                minHeight: '36px',
                background: '#2f2f2f',
                color: 'white',
                border: '1px solid #00FFFF',
                borderRadius: '3px',
                padding: '3px',
                fontSize: '10px'
            });

        $(this).empty().append(input);
        input.focus();

        function save() { cell.data(input.val()).draw(); }
        function cancel() { cell.data(original).draw(); }

        input.on('blur', save);
        input.on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); save(); }
            else if (e.key === 'Escape') { cancel(); }
        });
    });

    // ==================================================
    // CATATAN DATE FILTER
    // ==================================================
    $("div.catatan-date-filter").html(`
        <label style="margin-left:10px;">Dari: <input type="date" id="catatanDateStart"></label>
        <label style="margin-left:5px;">s/d <input type="date" id="catatanDateEnd"></label>
        <button id="catatanReset" class="btn-reset-inline">Reset</button>
    `);

    if (!$.fn.dataTable.ext.search._catatanAdded) {
        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (settings.nTable.id !== 'catatanTable') return true;
            const min = $('#catatanDateStart').val();
            const max = $('#catatanDateEnd').val();
            const tanggal = data[2];

            if (!min && !max) return true;
            const d = new Date(tanggal);
            if (min && d < new Date(min)) return false;
            if (max && d > new Date(max)) return false;
            return true;
        });
        $.fn.dataTable.ext.search._catatanAdded = true;
    }

    $('#catatanDateStart,#catatanDateEnd').on('change', function () {
        if (catatanTable) catatanTable.draw();
    });

    $('#catatanReset').on('click', function () {
        if (!filterUser) {
            $('#catatanDateStart,#catatanDateEnd').val(today);
        } else {
            $('#catatanDateStart,#catatanDateEnd').val('');
        }
        if (catatanTable) catatanTable.draw();
    });

    // ==================================================
    // DEFAULT DRAW TABLES
    // ==================================================
    table.draw();
    if (catatanTable) catatanTable.draw();

    // ==================================================
    // TAB SWITCHING
    // ==================================================
    $('.tab-btn').click(function () {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.tab-section').removeClass('active').hide();
        const targetSelector = $(this).data('target');
        const $target = $(targetSelector);
        $target.add
                $target.addClass('active').show();

        if (targetSelector === '#catatanSection') {
            $('#toggleFilterBtn').hide();
            panel.addClass('closed');
            if (catatanTable) {
                try {
                    catatanTable.columns.adjust();
                    if (catatanTable.responsive && typeof catatanTable.responsive.recalc === 'function') {
                        catatanTable.responsive.recalc();
                    }
                    catatanTable.draw(false);
                } catch (err) {
                    console.warn('Error adjusting catatanTable:', err);
                }
            }
        } else {
            $('#toggleFilterBtn').show();
        }
    });

    // ==================================================
    // MODAL PENOLAKAN
    // ==================================================
    function openRejectModal(id, type = 'laporan', username = '') {
        $('#rejectId').val(id);
        $('#rejectType').val(type);
        if (username) {
            if ($('#rejectUsername').length === 0) {
                $('<p>').attr('id', 'rejectUsername').insertAfter($('#rejectModal h2'));
            }
            $('#rejectUsername').text('Menolak laporan dari: ' + username);
        }
        $('#rejectModal').css('display', 'flex').show();
        setTimeout(() => $('#alasan').focus(), 100);
    }

    function closeRejectModal() {
        $('#rejectModal').hide();
        $('#alasan').val('');
    }

    $('.close').on('click', closeRejectModal);
    $(document).on('click', e => {
        if ($(e.target).hasClass('modal')) closeRejectModal();
    });
    $(document).on('keydown', e => {
        if (e.key === 'Escape' && $('#rejectModal').is(':visible')) closeRejectModal();
    });

    // ==================================================
    // EVENT HANDLERS (Approve / Reject)
    // ==================================================
    $(document).on('click', '.btn-approve-static', function () {
        const id = $(this).data('id');
        $.post('update_status.php', { id, type: 'laporan' }, res => {
            alert('Data laporan berhasil di-approve');
            location.reload();
        });
    });

    $(document).on('click', '.btn-approve-catatan', function () {
        const id = $(this).data('id');
        $.post('update_status.php', { id, type: 'catatan' }, res => {
            alert('Data catatan berhasil di-approve');
            location.reload();
        });
    });

    $(document).on('click', '.btn-reject-static', function () {
        openRejectModal($(this).data('id'), 'laporan', $(this).data('username') || '');
    });

    $(document).on('click', '.btn-reject-catatan', function () {
        openRejectModal($(this).data('id'), 'catatan', $(this).data('username') || '');
    });

    $('#rejectForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#rejectId').val(),
              type = $('#rejectType').val(),
              alasan = $('#alasan').val();

        if (!alasan.trim()) return alert('Harap isi alasan penolakan');

        $.post('update_status.php', { id, type, alasan }, res => {
            alert('Data berhasil ditolak');
            closeRejectModal();
            location.reload();
        });
    });

});
