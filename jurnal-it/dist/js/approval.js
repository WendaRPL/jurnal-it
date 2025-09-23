// dist/js/approval.js
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
        // expects 'yyyy-mm-dd'
        const parts = str.split('-');
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function hhmmToMin(hhmm) {
        if (!hhmm) return null;
        const [h, m] = hhmm.split(':').map(Number);
        return h * 60 + (m || 0);
    }

    // safe date compare (ignore time)
    function isBefore(a, b) {
        if (!a || !b) return false;
        const da = parseDate(a), db = parseDate(b);
        return da < db;
    }
    function isAfter(a, b) {
        if (!a || !b) return false;
        const da = parseDate(a), db = parseDate(b);
        return da > db;
    }

    // ==================================================
    // INITIAL VARS / URL PARAMS
    // ==================================================
    const urlParams = new URLSearchParams(window.location.search);
    const filterUser = urlParams.get('user'); // if present, it's starting focus on user
    const today = todayStr();

    // central filter state used by customFilter
    const filters = {
        dateFrom: '',
        dateTo: '',
        timeFrom: '',
        timeTo: '',
        type: '',
        planned: [] // array of strings: ['Iya','Tidak']
    };

    // ==================================================
    // INIT DATATABLES
    // ==================================================
    // Approval table (laporan harian)
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
            // data array -> columns as in template
            const start = data[3], end = data[4];
            let durasiText = '-';
            if (start && end) {
                const minutes = hhmmToMin(end) - hhmmToMin(start);
                if (!isNaN(minutes) && minutes >= 0) {
                    const h = String(Math.floor(minutes / 60)).padStart(2, '0');
                    const m = String(minutes % 60).padStart(2, '0');
                    durasiText = h + ':' + m;
                }
            }
            $('td:eq(5)', row).html(durasiText);
        }
    });

    // catatan table (catatan khusus)
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

    // assign proper IDs to datatable length/search selects/inputs
    $("#approvalTable_length select").attr({ id: "approvalTable_length_select", name: "approvalTable_length" });
    $("#approvalTable_filter input").attr({ id: "approvalTable_search", name: "approvalTable_search" });

    $("#catatanTable_length select").attr({ id: "catatanTable_length_select", name: "catatanTable_length" });
    $("#catatanTable_filter input").attr({ id: "catatanTable_search", name: "catatanTable_search" });

    // ==================================================
    // SET DEFAULT DATE RANGE FOR APPROVAL FILTER (only when NO user param)
    // ==================================================
    $('#filterDateStart,#filterDateEnd').val('');
    filters.dateFrom = filters.dateTo = '';

    // ==================================================
    // CUSTOM FILTER FOR APPROVAL TABLE (global filters)
    // ==================================================
    function approvalCustomFilter(settings, data, dataIndex) {
        // only apply to approvalTable
        if (settings.nTable.id !== 'approvalTable') return true;
        const rowData = table.row(dataIndex).data();
        if (!rowData) return true;

        const tanggal = rowData[2]; // expect yyyy-mm-dd
        const start = rowData[3];
        const end = rowData[4];
        const tipe = (rowData[7] || '').toString().trim();
        const terencana = (rowData[8] || '').toString().trim().toLowerCase();
        const planned = filters.planned.map(v => v.toLowerCase());

        // DATE filter: only enforce if user _set_ dateFrom/dateTo in filters (not auto-locked)
        if (filters.dateFrom && parseDate(tanggal) < parseDate(filters.dateFrom)) return false;
        if (filters.dateTo && parseDate(tanggal) > parseDate(filters.dateTo)) return false;

        // TIME filter
        if (filters.timeFrom && start) {
            if (hhmmToMin(start) < hhmmToMin(filters.timeFrom)) return false;
        }
        if (filters.timeTo && end) {
            if (hhmmToMin(end) > hhmmToMin(filters.timeTo)) return false;
        }

        // Tipe filter
        if (filters.type && tipe !== filters.type) return false;

        // Terencana filter
        if (planned.length && !planned.includes(terencana)) return false;

        return true;
    }

    if (!$.fn.dataTable.ext.search._approvalAdded) {
        $.fn.dataTable.ext.search.push(approvalCustomFilter);
        $.fn.dataTable.ext.search._approvalAdded = true;
    }

    // ==================================================
    // STATS UPDATE (based on currently displayed approval table rows)
    // ==================================================
    function updateStats() {
        const rows = table.rows({ search: 'applied' }).nodes();
        $('#stat-total').text(rows.length);
        $('#stat-pending').text($(rows).find('.status-pending').length);
        $('#stat-approved').text($(rows).find('.status-approved').length);
        $('#stat-rejected').text($(rows).find('.status-rejected').length);
    }
    updateStats();
    table.on('draw', updateStats);

    // ==================================================
    // FILTER PANEL HANDLERS (apply / reset)
    // ==================================================
    $('#applyFilter').on('click', function () {
        // read filters from inputs
        filters.dateFrom = $('#filterDateStart').val() || '';
        filters.dateTo = $('#filterDateEnd').val() || '';
        filters.timeFrom = $('#filterTimeStart').val() || '';
        filters.timeTo = $('#filterTimeEnd').val() || '';
        filters.type = $('#filterTipe').val() || '';
        filters.planned = $('.filter-terencana:checked').map(function () { return $(this).val(); }).get() || [];

        // redraw approval table (will call approvalCustomFilter)
        table.draw();
    });

    // reset filter
    $('#resetFilter').on('click', function () {
        $('#filterDateStart,#filterDateEnd,#filterTimeStart,#filterTimeEnd,#filterTipe').val('');
        $('.filter-terencana').prop('checked', false);

        // balik ke kosong (semua data), bukan today
        filters.dateFrom = filters.dateTo = '';
        filters.timeFrom = filters.timeTo = filters.type = '';
        filters.planned = [];

        // hapus user param dari URL lalu reload
        const url = new URL(window.location.href);
        url.searchParams.delete("user"); 
        window.location.href = url.toString();
    });

    // ==================================================
    // INLINE EDIT (dblclick to edit textarea)
    // ==================================================
    $(document).on('dblclick', '.editable', function () {
        const cell = table.cell(this);
        if ($(this).find('textarea').length) return;
        const original = cell.data();
        const input = $('<textarea>').val(original).css({
            width: '100%', minHeight: '36px', background: '#2f2f2f', color: 'white',
            border: '1px solid #00FFFF', borderRadius: '3px', padding: '3px', fontSize: '10px'
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
    // CATATAN DATE FILTER WIDGET (render & handlers)
    // ==================================================
    $("div.catatan-date-filter").html(`
        <label style="margin-left:10px;">Dari: <input type="date" id="catatanDateStart"></label>
        <label style="margin-left:5px;">s/d <input type="date" id="catatanDateEnd"></label>
        <button id="catatanReset" class="btn-reset-inline">Reset</button>
    `);

    // default behavior for catatan date inputs:
    // - if no user param -> default today (like approval)
    // - if user param -> keep empty so user can expand filter
    $('#catatanDateStart,#catatanDateEnd').val('');


    // custom filter function for catatan table
    if (!$.fn.dataTable.ext.search._catatanAdded) {
        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (settings.nTable.id !== 'catatanTable') return true;

            const tanggal = data[2]; // expected 'yyyy-mm-dd'
            if (!tanggal) return true;

            const min = $('#catatanDateStart').val();
            const max = $('#catatanDateEnd').val();
            const d = new Date(tanggal);

            // Only apply restrictions if the inputs actually have values.
            // This ensures that when user param exists and inputs are empty, filter won't force-limit rows.
            if (min && d < new Date(min)) return false;
            if (max && d > new Date(max)) return false;

            return true;
        });
        $.fn.dataTable.ext.search._catatanAdded = true;
    }

    // redraw on date change
    $('#catatanDateStart,#catatanDateEnd').on('change', function () {
        if (catatanTable) catatanTable.draw();
    });

    // catatan reset button
  $('#catatanReset').on('click', function () {
      $('#catatanDateStart,#catatanDateEnd').val('');   // kosong, bukan today
  
      const url = new URL(window.location.href);
      url.searchParams.delete("user");
      window.history.replaceState({}, "", url);
  
      if (catatanTable) catatanTable.draw();
  });

    // ==================================================
    // TAB SWITCHING
    // ==================================================
    $('.tab-btn').on('click', function () {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-section').removeClass('active').hide();

        const targetSelector = $(this).data('target');
        const $target = $(targetSelector);
        $target.addClass('active').show();

        if (targetSelector === '#catatanSection') {
            $('#toggleFilterBtn').hide();
            $('#filterPanel').addClass('closed');
            if (catatanTable) { catatanTable.columns.adjust().responsive.recalc().draw(false); }
        } else {
            $('#toggleFilterBtn').show();
        }
    });

    // open filter panel toggle
    const panel = $('#filterPanel');
    $('#toggleFilterBtn').on('click', function (e) { e.stopPropagation(); panel.toggleClass('closed'); });
    $('.modal-close').on('click', function (e) { e.stopPropagation(); panel.addClass('closed'); });

    // buka panel otomatis kalau default tab aktif = Approval
    if ($('#approvalSection').hasClass('active')) {
        panel.removeClass('closed');
        $('#toggleFilterBtn').show();
    } else {
        panel.addClass('closed');
        $('#toggleFilterBtn').hide();
    }

    // ==================================================
    // REJECT MODAL
    // ==================================================
    function openRejectModal(id, type = 'laporan', name = '') {
        $('#rejectId').val(id);
        $('#rejectType').val(type);
        if (name) {
            if ($('#rejectName').length === 0) { $('<p>').attr('id', 'rejectName').insertAfter($('#rejectModal h2')); }
            $('#rejectName').text('Menolak laporan dari: ' + name);
        }
        $('#rejectModal').css('display', 'flex').show();
        setTimeout(() => $('#alasan').focus(), 100);
    }
    function closeRejectModal() {
        $('#rejectModal').hide();
        $('#alasan').val('');
        $('#rejectName').remove(); // clean up optional name paragraph
    }

    $('.close').on('click', closeRejectModal);
    $(document).on('click', function (e) { if ($(e.target).hasClass('modal')) closeRejectModal(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape' && $('#rejectModal').is(':visible')) closeRejectModal(); });

    // bind reject buttons (static & catatan)
    $(document).on('click', '.btn-reject-static', function () {
        openRejectModal($(this).data('id'), 'laporan', $(this).data('name') || '');
    });
    $(document).on('click', '.btn-reject-catatan', function () {
        openRejectModal($(this).data('id'), 'catatan', $(this).data('name') || '');
    });

    // ==================================================
    // APPROVE / REJECT ACTIONS (AJAX posts to update_status.php)
    // ==================================================
    $(document).on('click', '.btn-approve-static', function () {
        const id = $(this).data('id');
        $.post('update_status.php', { id, type: 'laporan' }, () => location.reload());
    });

    $(document).on('click', '.btn-approve-catatan', function () {
        const id = $(this).data('id');
        $.post('update_status.php', { id, type: 'catatan' }, () => location.reload());
    });

    $('#rejectForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#rejectId').val(), type = $('#rejectType').val(), alasan = $('#alasan').val();
        if (!alasan || !alasan.trim()) return alert('Harap isi alasan penolakan');
        $.post('update_status.php', { id, type, alasan }, () => { closeRejectModal(); location.reload(); });
    });

    // ==================================================
    // DEFAULT DRAW (ensure everything draws correctly on load)
    // ==================================================
    table.draw();
    if (catatanTable) catatanTable.draw();
});



