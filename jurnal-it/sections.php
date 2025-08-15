<?php
function renderUnfulfilledSection() {
    // Cek role user yang login
    $role_id = $_SESSION['role_id'] ?? 0;
    
    // Hanya tampilkan jika role adalah Admin (1) atau SPV (2)
    if ($role_id == 1 || $role_id == 2) {
?>
<section class="unfulfilled-section">
  <div class="section-header">Belum Terpenuhi</div>
  <div class="user-cards">
    <div class="user-card">
      <div class="user-header">Nama User</div>
      <div class="user-date">10 Aug 2025</div>
      <div class="user-date">08 Aug 2025</div>
      <div class="user-date">07 Aug 2025</div>
      <div class="user-date">04 Aug 2025</div>
    </div>
    <div class="user-card">
      <div class="user-header">Nama User</div>
      <div class="user-date">09 Aug 2025</div>
      <div class="user-date">07 Aug 2025</div>
      <div class="user-date">06 Aug 2025</div>
    </div>
    <div class="user-card">
      <div class="user-header">Nama User</div>
      <div class="user-date">07 Aug 2025</div>
      <div class="user-date">03 Aug 2025</div>
    </div>
    <div class="user-card">
      <div class="user-header">Nama User</div>
      <div class="user-date">09 Aug 2025</div>
      <div class="user-date">08 Aug 2025</div>
      <div class="user-date">06 Aug 2025</div>
      <div class="user-date">05 Aug 2025</div>
    </div>
    <div class="user-card">
      <div class="user-header">Nama User</div>
      <div class="user-date">10 Aug 2025</div>
    </div>
  </div>
</section>
<?php
    }
}
?>