<?php
function renderNavbar() {
?>
<div class="header">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <ol>
      <li>
        <a href="home.php" class="breadcrumb-item active">
          <i class="fas fa-home"></i>
          <span>HOME</span>
        </a>
      </li>
    </ol>
    <div class="nav-icons">
      <div class="notification">
        <i class="fas fa-bell fa-lg"></i>
        <span class="notification-badge">1</span>
      </div>
      <button class="btn-report">Buat Laporan</button>
      <div class="user-avatar">
        <i class="fas fa-user"></i>
      </div>
    </div>
  </nav>
</div>
<?php
}
?>
