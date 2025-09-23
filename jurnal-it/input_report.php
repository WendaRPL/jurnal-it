<?php
session_start();
require_once "direct/config.php";

// Cek login (misal user/staff login bawa session user_id)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

ob_start();
?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="dist/css/input-laporan.css">

<?php if (isset($_GET['msg'])): ?>
<div class="notif-container">
  <div class="notif <?= htmlspecialchars($_GET['msg']) ?>">
    <?php if ($_GET['msg'] === 'created'): ?>
      <i class="fas fa-check-circle"></i> Laporan berhasil dibuat.
    <?php elseif ($_GET['msg'] === 'error'): ?>
      <i class="fas fa-times-circle"></i> Gagal menyimpan laporan.
    <?php elseif ($_GET['msg'] === 'updated'): ?>
      <i class="fas fa-save"></i> Laporan berhasil diperbarui.
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
      <i class="fas fa-trash-alt"></i> Laporan berhasil dihapus.
    <?php elseif ($_GET['msg'] === 'invalid_request'): ?>
      <i class="fas fa-exclamation-triangle"></i> Permintaan tidak valid.
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>


<section class="container">
    <div class="row">
        <!-- Form Input Laporan -->
        <div class="col-lg-8 col-md-6">
            <div class="input-section">
                <div class="section-header d-flex align-items-center">
                    <span class="ms-2">Form Input Laporan</span>
                </div>
                <div class="section-content px-3 py-3">
                    <form action="direct/input_laporan_process.php" method="POST" id="laporanForm">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal</label>
                                <div class="input-group date-group" onclick="focusInput('tanggal')">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control flatpickr-date" id="tanggal" name="tanggal" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Time</label>
                                <div class="input-group date-group" onclick="focusInput('start_time')">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="text" class="form-control flatpickr-time" id="start_time" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Time</label>
                                <div class="input-group date-group" onclick="focusInput('end_time')">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="text" class="form-control flatpickr-time" id="end_time" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Terencana</label>
                                <select class="form-select" name="terencana" required>
                                    <option value="" disabled selected>-- Pilih Rencana --</option>
                                    <option value="Ya">Ya</option>
                                    <option value="Tidak">Tidak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="tipe_id" required>
                                    <option value="" disabled selected>-- Pilih Tipe Pekerjaan --</option>
                                    <option value="1">MTCH: Maintenance Hardware</option>
                                    <option value="2">MTCS: Maintenance Software</option>
                                    <option value="3">SVCH: Service/Repair Hardware</option>
                                    <option value="4">SVCS: Service/Repair Software</option>
                                    <option value="5">DEV: Development</option>
                                    <option value="6">ADM: Administrasi</option>
                                    <option value="7">OTH: Other activity</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" name="status" placeholder="Contoh: Terkendali, tetapi butuh pemantauan lebih lanjut" required>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Form Input Catatan Khusus -->
        <div class="col-lg-4 col-md-3">
            <div class="input-section">
                <div class="section-header d-flex align-items-center">
                    <span class="ms-2">Form Input Catatan Khusus</span>
                </div>
                <div class="section-content px-3 py-3">
                    <form action="direct/input_catatan.php" method="POST" id="catatanForm">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <div class="input-group date-group" onclick="focusInput('tanggal_catatan')">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="text" class="form-control flatpickr-date" id="tanggal_catatan" name="tanggal_catatan" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="catatan" rows="4" placeholder="Contoh: Saya sebelumnya mengganti tugas yang berawal pagi menjadi siang" required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>  
</section>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function focusInput(id) {
    document.getElementById(id).focus();
}

flatpickr(".flatpickr-date", {
    dateFormat: "Y-m-d",
    allowInput: true
});

flatpickr(".flatpickr-time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true,
    allowInput: true
});
</script>

<?php 
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>
