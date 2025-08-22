<?php
session_start();

require_once "config.php";
$role_id = $_SESSION['role_id'] ?? 0;
ob_start(); // mulai output buffering
?>

<link rel="stylesheet" href="modules/css/input-report.css">
<link rel="stylesheet" href="modules/css/unfullfiled-section.css">

<section class="container">
    <div class="row">
        <!-- Form Input Laporan -->
        <div class="col-lg-8 col-md-6 ">
            <div class="unfulfilled-section">
                <div class="section-header">Form Input Laporan</div>
                <div class="section-content px-3 py-3">
                    <form id="laporanForm">
                        <div  class="row mb-3">
                            <div class="col-md-4">
                                <label for="inputTanggal" class="form-label">Tanggal</label>
                                <div class="input-group" onclick="focusDateInput('inputTanggal')" style="cursor: pointer;">
                                    <span class="input-group-text" style="background-color: #000000; border: 1px solid #00ffff;">
                                        <i class="fas fa-calendar" style="color: #00ffff;"></i>
                                    </span>
                                    <input type="date" class="form-control" id="inputTanggal" style="background-color: #000000; color: #ffffff;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="inputStartTime" class="form-label">Start Time</label>
                                <div class="input-group" onclick="focusDateInput('inputStartTime')" style="cursor: pointer;">
                                    <span class="input-group-text" style="background-color: #000000; border: 1px solid #00ffff;">
                                        <i class="fas fa-clock" style="color: #00ffff;"></i>
                                    </span>
                                    <input type="time" class="form-control" id="inputStartTime" style="background-color: #000000; color: #ffffff;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="inputEndTime" class="form-label">End Time</label>
                                <div class="input-group" onclick="focusDateInput('inputEndTime')" style="cursor: pointer;">
                                    <span class="input-group-text" style="background-color: #000000; border: 1px solid #00ffff;">
                                        <i class="fas fa-clock" style="color: #00ffff;"></i>
                                    </span>
                                    <input type="time" class="form-control" id="inputEndTime" style="background-color: #000000; color: #ffffff;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="inputDeskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="inputDeskripsi" rows="4" placeholder="Isi deskripsi.."></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="inputTerencana" class="form-label">Terencana</label>
                                <select class="form-select" id="inputTerencana">
                                    <option class="placeholder-option" value="" selected disabled>-- Pilih Rencana --</option>
                                    <option class="Yes">Ya</option>
                                    <option class="No">Tidak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputType" class="form-label">Type</label>
                                <select class="form-select" id="inputType">
                                    <option class="placeholder-option" value="" selected disabled>-- Pilih Tipe Pekerjaan --</option>
                                    <option value="MTCH" class="option-type option-mtch">MTCH: Maintenance Hardware</option>
                                    <option value="MTCS" class="option-type option-mtcs">MTCS: Maintenance Software</option>
                                    <option value="SVCH" class="option-type option-svch">SVCH: Service/Repair Hardware</option>
                                    <option value="SVCS" class="option-type option-svcs">SVCS: Service/Repair Software</option>
                                    <option value="DEV" class="option-type option-dev">DEV: Development</option>
                                    <option value="ADM" class="option-type option-adm">ADM: Administrasi</option>
                                    <option value="OTH" class="option-type option-oth">OTH: Other activity</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="inputStatus" class="form-label ">Status</label>
                                <input type="text" class="form-control" id="inputStatus" placeholder="Contoh: Terkendali, tetapi butuh pemantauan lebih lanjut">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Form Input Catatan Khusus -->
        <div class="col-lg-4 col-md-3">
            <div class="unfulfilled-section">
                <div class="section-header">Form Input Catatan Khusus</div>
                <div class="section-content px-3 py-3">
                    <form id="catatanForm">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="inputCatatanTanggal" class="form-label">Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #000000; border: 1px solid #00ffff;">
                                        <i class="fas fa-calendar" style="color: #00ffff;"></i>
                                    </span>
                                    <input type="date" class="form-control" id="inputCatatanTanggal">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="inputCatatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="inputCatatan" rows="4" 
                                placeholder="Contoh: Saya sebelumnya mengganti tugas yang berawal pagi menjadi siang"></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>  
</section>

<script src="modules/js/input-report.js"></script>

<?php 
$content = ob_get_clean(); // ambil output buffering
require_once "modules/layout/template.php";
?>
