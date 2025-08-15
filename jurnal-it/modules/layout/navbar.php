<?php
function renderNavbar() {
  $role_id = $_SESSION['role_id'] ?? 0; // Cek role user
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
      

      <!-- User Dropdown -->
      <div class="user-dropdown">
        <div class="user-avatar" onclick="toggleUserMenu()">
          <i class="fas fa-user"></i>
        </div>
        <div class="user-menu" id="userMenu">
          <p>Halo, <?php echo $_SESSION['name'] ?? 'User'; ?></p>
          <a href="profile.php"><i class="fas fa-id-card"></i> Profil</a>
          <hr>
          <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </nav>
</div>

<style>
.user-dropdown {
  position: relative;
}
.user-avatar {
  background: var(--light-gray);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--cyan);
  cursor: pointer;
}
.user-menu {
  display: none;
  position: absolute;
  right: 0;
  top: 50px;
  background: var(--gray);
  border: 1px solid var(--light-gray);
  border-radius: 6px;
  min-width: 180px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  z-index: 100;
}
.user-menu a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  color: var(--white);
  text-decoration: none;
  transition: background 0.2s;
}
.user-menu a:hover {
  background: rgba(0,255,255,0.1);
}
.user-menu hr {
  margin: 5px 0;
  border: none;
  border-top: 1px solid var(--light-gray);
}
.user-menu a.logout {
  color: #ff4d4d;
}
.user-menu p {
  margin: 0;
  padding: 10px;
  border-bottom: 1px solid var(--light-gray);
  font-size: 0.9rem;
  color: var(--cyan);
}
.show-menu {
  display: block;
}
</style>

<script>
function toggleUserMenu() {
  document.getElementById("userMenu").classList.toggle("show-menu");
}
document.addEventListener("click", function(e) {
  if (!e.target.closest(".user-dropdown")) {
    document.getElementById("userMenu").classList.remove("show-menu");
  }
});
</script>
<?php
}
?>
