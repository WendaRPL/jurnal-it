<?php
function renderCards() {
?>
<div class="card-container">
  <div class="card">
    <div class="card-header">Laporan Terbaru</div>
    <div class="card-item">Laporan</div>
    <div class="card-item">Laporan</div>
    <div class="card-item">Laporan</div>
  </div>

  <div class="card">
    <div class="card-header">Statistik Cepat</div>
    <div class="stats-card">
      <div class="stats-labels">
        <div class="py-1">Total Input</div>
        <div class="py-1">Approved</div>
        <div class="py-1">Pending</div>
      </div>
      <div class="stats-values">
        <div class="py-1">50</div>
        <div class="py-1">35</div>
        <div class="py-1">15</div>
      </div>
    </div>
  </div>

  <a href="history.php" class="card-link" aria-label="Lihat semua laporan">
    <div class="card search-card">
      <i class="fas fa-search"></i>
      <div class="label">Lihat<br>Semua<br>Laporan</div>
    </div>
  </a>
</div>
<?php
}
?>
