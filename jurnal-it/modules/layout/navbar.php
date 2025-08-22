<?php
    $role_id = $_SESSION['role_id'] ?? 0;

    // Mapping role id → nama dan warna
    $roles = [
        1 => ["name" => "Admin", "color" => "#ff4d4d"],    // merah
        2 => ["name" => "Supervisor", "color" => "#4d79ff"],  // biru
        3 => ["name" => "Staff", "color" => "#33cc33"],    // hijau
    ];
    $roleName  = $roles[$role_id]['name'] ?? "User";
    $roleColor = $roles[$role_id]['color'] ?? "#aaaaaa";

    // === Breadcrumb Dinamis ===
    $current_page = basename($_SERVER['PHP_SELF'], ".php");
    $page_titles = [
        "home"      => "Home",
        "User Manage"  => "User Manage",
        "users"     => "Manajemen User",
        "reports"   => "Laporan",
        "settings"  => "Pengaturan",
        "profile"   => "Profil",
    ];
    $breadcrumb_title = $page_titles[$current_page] ?? ucfirst($current_page);
?>

<div class="header">
  <nav class="navbar" aria-label="Breadcrumb">
    <!-- Breadcrumb -->
    <div class="breadcrumb-wrapper">
      <ol class="breadcrumb">
        <li>
          <a href="home.php" class="breadcrumb-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
          </a>
        </li>
        <?php if ($current_page !== "home"): ?>
          <li class="breadcrumb-separator"> / </li>
          <li class="breadcrumb-item active">
            <span><?= $breadcrumb_title ?></span>
          </li>
        <?php endif; ?>
      </ol>
    </div>

    <!-- Nav Icons -->
    <div class="nav-icons">
      <div class="notification">
        <i class="fas fa-bell fa-lg"></i>
        <span class="notification-badge">1</span>
      </div>

      <!-- User info -->
      <div class="user-info" style="display:flex; align-items:center; gap:10px;">
        <span style="color:<?= $roleColor ?>; font-weight:bold;"><?= $roleName ?></span>
        <div class="user-dropdown">
          <div class="user-avatar" onclick="toggleUserMenu()">
            <i class="fas fa-user"></i>
          </div>
          <div class="user-menu" id="userMenu">
            <p>Halo, <?= $_SESSION['name'] ?? 'User'; ?></p>
            <a href="profile.php"><i class="fas fa-id-card"></i> Profil</a>
            <hr>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</div>

<script src="script.php"></script>
