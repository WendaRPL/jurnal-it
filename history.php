<?php
session_start();
require_once "direct/config.php";

$role_id = $_SESSION['role_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? ''; // Asumsikan nama user disimpan di session
ob_start();
?>

<link rel="stylesheet" href="dist/css/history.css">
<link rel="stylesheet" href="dist/css/unfullfiled-section.css">

<div class="section-text">History Laporan</div>

<?php
// Tentukan section mana yang akan ditampilkan berdasarkan role
if ($role_id == 1 || $role_id == 2 || $role_id == 3): // Admin atau SPV bisa melihat data staff
?>
<!-- ========== SECTION STAFF ========== -->
<section class="unfullfiled-section staff-section">
    <div class="section-header">
        <span>Data Staff</span>
        <span class="total-reports staff-total">3 Laporan</span>
    </div>

    <!-- Export & Search -->
    <div class="table-controls">
        <div class="export-buttons">
            <button class="export-btn" onclick="exportData('copy', 'staff')">Copy</button>
            <button class="export-btn" onclick="exportData('csv', 'staff')">CSV</button>
            <button class="export-btn" onclick="exportData('excel', 'staff')">Excel</button>
            <button class="export-btn" onclick="exportData('pdf', 'staff')">PDF</button>
            <button class="export-btn" onclick="exportData('print', 'staff')">Print</button>
            <button class="export-btn" onclick="toggleColumnVisibility('staff', event)">
                <i class="fas fa-eye"></i> Column visibility
            </button>
        </div>
        <div class="search-container">
            <?php if ($role_id == 2): ?>
            <input type="text" id="searchInputStaff" class="search-box" placeholder="Cari nama staff...">
            <?php endif; ?>
            <div class="date-wrapper" onclick="document.getElementById('dateSearchStaff').showPicker()">
                <input type="date" id="dateSearchStaff" class="search-date">
            </div> 
        </div>
    </div>

    <!-- Tab Nav Staff -->
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="pending">Pending</button>
        <button class="tab-btn" data-tab="approved">Approved</button>
        <?php if ($role_id == 2): // Tidak perlu tampil All karena cukup dari Approved saja ?>
        <button class="tab-btn" data-tab="all">All</button>
        <?php endif; ?>
    </div>

    <div class="section-content staff-content">
        <?php if ($role_id == 2): // Hanya SPV yang bisa approve/reject ?>
        <div class="bulk-actions" id="bulkActions">
        <button class="btn-approve-selected" id="approveSelected"><i class="fas fa-check-circle"></i> Approve Selected</button>
        <button class="btn-reject-selected" id="rejectSelected"><i class="fas fa-times-circle"></i> Reject Selected</button>
        </div>  
        <?php endif; ?>

        <!-- Tab Pending -->
        <div class="tab-content active" id="pending">
            <div class="priority-section">
                <div class="priority-title">Pending</div>
                <div class="user-accordion">
                    <div class="user-header pending" onclick="toggleAccordion(this)">
                        <div class="user-checklist">
                            <input type="checkbox" class="select-all-accordion" onclick="event.stopPropagation()">
                            <span class="user-name">Karina</span>
                        </div>
                        <span class="report-date">23-08-2025</span>
                    </div>
                    <div class="user-content">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width:40px" class="col-checkbox">✓</th>
                                    <th class="col-start">Start Time</th>
                                    <th class="col-end">End Time</th>
                                    <th class="col-duration">Duration</th>
                                    <th class="col-deskripsi">Deskripsi</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-terencana">Terencana</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-checkbox"><input type="checkbox" class="row-checkbox"></td>
                                    <td class="col-start">12.00</td>
                                    <td class="col-end">13.00</td>
                                    <td class="col-duration">01.00</td>
                                    <td class="col-deskripsi">Makan siang</td>
                                    <td class="col-type">OTH</td>
                                    <td class="col-terencana">Tidak</td>
                                    <td class="col-status">Menunggu</td>
                                    <td class="col-action">
                                        <?php if ($role_id == 2): // Hanya SPV yang bisa approve/reject ?>
                                        <button class="btn-approve">Approve</button>
                                        <button class="btn-reject">Reject</button>
                                        <?php else: ?>
                                        <span style="color: yellow;">Pending</span> 
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <p class="noResultMessage" style="display:none;"></p>
                    <div class="pagination">
                        <span></span>
                        <div class="pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Approved -->
        <div class="tab-content" id="approved">
            <div class="priority-section">
                <div class="priority-title">Approved</div>
                <div class="user-accordion">
                    <div class="user-header approved" onclick="toggleAccordion(this)">
                        <div class="user-checklist">
                            <input type="checkbox" class="select-all-accordion" onclick="event.stopPropagation()">
                            <span class="user-name">Karina</span>
                        </div>
                        <span class="report-date">22-08-2025</span>
                    </div>
                    <div class="user-content">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width:40px" class="col-checkbox">✓</th>
                                    <th class="col-start">Start Time</th>
                                    <th class="col-end">End Time</th>
                                    <th class="col-duration">Duration</th>
                                    <th class="col-deskripsi">Deskripsi</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-terencana">Terencana</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-checkbox"><input type="checkbox" class="row-checkbox"></td>
                                    <td class="col-start">12.00</td>
                                    <td class="col-end">13.00</td>
                                    <td class="col-duration">01.00</td>
                                    <td class="col-deskripsi">Makan siang</td>
                                    <td class="col-type">OTH</td>
                                    <td class="col-terencana">Tidak</td>
                                    <td class="col-status">Menunggu</td>
                                    <td style="color: greenyellow;" class="col-action">Approved</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-wrapper">
                    <p class="noResultMessage" style="display:none;"></p>
                    <div class="pagination">
                        <span></span>
                        <div class="pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab All -->
        <div class="tab-content" id="all">
            <div class="priority-section">
                <div class="priority-title">Semua Laporan</div>
                
                <div class="user-accordion">
                    <div class="user-header" onclick="toggleAccordion(this)">
                        <div class="user-checklist">
                            <input type="checkbox" class="select-all-accordion" onclick="event.stopPropagation()">
                            <span class="user-name">Danurwenda</span>
                        </div>
                        <span class="report-date">23-08-2025</span>
                    </div>
                    <div class="user-content">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width:40px" class="col-checkbox">✓</th>
                                    <th class="col-start">Start Time</th>
                                    <th class="col-end">End Time</th>
                                    <th class="col-duration">Duration</th>
                                    <th class="col-deskripsi">Deskripsi</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-terencana">Terencana</th>
                                    <th class="col-status">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-checkbox"><input type="checkbox" class="row-checkbox"></td>
                                    <td class="col-start">12.00</td>
                                    <td class="col-end">13.00</td>
                                    <td class="col-duration">01.00</td>
                                    <td class="col-deskripsi">Makan siang</td>
                                    <td class="col-type">OTH</td>
                                    <td class="col-terencana">Tidak</td>
                                    <td class="col-status">Menunggu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-wrapper">
                    <p class="noResultMessage" style="display:none;"></p>
                    <div class="pagination">
                        <span></span>
                        <div class="pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Tampilkan section SPV untuk Admin dan SPV
if ($role_id == 1 || $role_id == 2): // Admin atau SPV (lihat data sendiri)
?>
<!-- ========== SECTION SPV ========== -->
<section class="unfullfiled-section spv-section">
    <div class="section-header">
        <span>Data SPV</span>
        <span class="total-reports spv-total">2 Laporan</span>
    </div>

    <!-- Export & Calender -->
    <div class="table-controls">
        <div class="export-buttons">
            <button class="export-btn" onclick="exportData('copy', 'spv')">Copy</button>
            <button class="export-btn" onclick="exportData('csv', 'spv')">CSV</button>
            <button class="export-btn" onclick="exportData('excel', 'spv')">Excel</button>
            <button class="export-btn" onclick="exportData('pdf', 'spv')">PDF</button>
            <button class="export-btn" onclick="exportData('print', 'spv')">Print</button>
            <button class="export-btn" onclick="toggleColumnVisibility('spv', event)">
                <i class="fas fa-eye"></i> Column visibility
            </button>
        </div>
        <div class="search-container">
            <div class="date-wrapper" onclick="document.getElementById('dateSearchSpv').showPicker()">
                <input type="date" id="dateSearchSpv" class="search-date">
            </div> 
        </div>
    </div>

    <!-- Tab Nav SPV -->
    <div class="tab-nav spv-nav">
        <button class="tab-btn-spv active" data-tab="spv-pending">Pending</button>
        <button class="tab-btn-spv" data-tab="spv-approved">Approved</button>
    </div>

    <div class="section-content spv-content">
        <!-- Tab Pending SPV -->
        <div class="tab-content-spv active" id="spv-pending">
            <div class="priority-section">
                <div class="priority-title">Pending</div>

                <div class="user-accordion">
                    <div class="user-header pending" onclick="toggleAccordion(this)">
                        <div class="user-checklist">
                            <input type="checkbox" class="select-all-accordion-spv" onclick="event.stopPropagation()">
                            <span class="user-name">Reza</span>
                        </div>
                        <span class="report-date">23-08-2025</span>
                    </div>
                    <div class="user-content">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width:40px" class="col-checkbox-spv">✓</th>
                                    <th class="col-start-spv">Start Time</th>
                                    <th class="col-end-spv">End Time</th>
                                    <th class="col-duration-spv">Duration</th>
                                    <th class="col-deskripsi-spv">Deskripsi</th>
                                    <th class="col-type-spv">Type</th>
                                    <th class="col-terencana-spv">Terencana</th>
                                    <th class="col-status-spv">Status</th>
                                    <th class="col-action-spv">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-checkbox-spv"><input type="checkbox" class="row-checkbox-spv"></td>
                                    <td class="col-start-spv">12.00</td>
                                    <td class="col-end-spv">13.00</td>
                                    <td class="col-duration-spv">01.00</td>
                                    <td class="col-deskripsi-spv">Makan siang</td>
                                    <td class="col-type-spv">OTH</td>
                                    <td class="col-terencana-spv">Tidak</td>
                                    <td class="col-status-spv">Menunggu</td>
                                    <td style="color: yellow" class="col-action-spv">Pending</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <p class="noResultMessage" style="display:none;"></p>
                    <div class="pagination">
                        <span></span>
                        <div class="pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Approved SPV -->
        <div class="tab-content-spv" id="spv-approved">
            <div class="priority-section">
                <div class="priority-title">Approved</div>

                <div class="user-accordion">
                    <div class="user-header approved" onclick="toggleAccordion(this)">
                        <div class="user-checklist">
                            <input type="checkbox" class="select-all-accordion-spv" onclick="event.stopPropagation()">
                            <span class="user-name">Reza</span>
                        </div>
                        <span class="report-date">22-08-2025</span>
                    </div>
                    <div class="user-content">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width:40px" class="col-checkbox-spv">✓</th>
                                    <th class="col-start-spv">Start Time</th>
                                    <th class="col-end-spv">End Time</th>
                                    <th class="col-duration-spv">Duration</th>
                                    <th class="col-deskripsi-spv">Deskripsi</th>
                                    <th class="col-type-spv">Type</th>
                                    <th class="col-terencana-spv">Terencana</th>
                                    <th class="col-status-spv">Status</th>
                                    <th class="col-action-spv">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-checkbox-spv"><input type="checkbox" class="row-checkbox-spv"></td>
                                    <td class="col-start-spv">12.00</td>
                                    <td class="col-end-spv">13.00</td>
                                    <td class="col-duration-spv">01.00</td>
                                    <td class="col-deskripsi-spv">Makan siang</td>
                                    <td class="col-type-spv">OTH</td>
                                    <td class="col-terencana-spv">Tidak</td>
                                    <td class="col-status-spv">Menunggu</td>
                                    <td style="color: greenyellow" class="col-action-spv">Approved</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <p class="noResultMessage" style="display:none;"></p>
                    <div class="pagination">
                        <span></span>
                        <div class="pagination-nav">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>  
<?php endif; ?>

<!-- Untuk SheetJS (Excel) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Untuk jsPDF (PDF) -->
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>

<script>
// Simpan informasi role di JavaScript
const userRoleId = <?php echo $role_id; ?>;
const userId = <?php echo $user_id; ?>;
const userName = "<?php echo $user_name; ?>";
</script>

<script src="dist/js/history.js"></script>

<?php 
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>
