<?php
session_start();

require_once "config.php";
$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1) {
   header("Location: home.php");
   exit;
}
ob_start(); // mulai output buffering

// Ambil data user dari database
$search = $_GET['search'] ?? '';
$query = "SELECT u.id, u.username, u.name, u.role_id, u.initial, r.description AS role
          FROM user u
          LEFT JOIN role r ON u.role_id = r.id
          WHERE u.role_id != 1";

if (!empty($search)) {
    $search = "%".$conn->real_escape_string($search)."%";
    $stmt = $conn->prepare("SELECT u.id, u.username, u.name, u.role_id, r.description AS role
                             FROM user u
                             LEFT JOIN role r ON u.role_id = r.id
                             WHERE u.role_id != 1 AND (u.username LIKE ? OR u.name LIKE ?)");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query($query);
}
?>

<link rel="stylesheet" href="modules/css/user-management.css">

<button type="button" class="btn btn-outline-info mb-2">+ Create User</button>

<!-- Card User Management -->
<section class="unfulfilled-section">
  <div class="section-header">
    <ol class="user-breadcrumb-flex">
      <li class="breadcrumb-item current">
        <span>User Management</span>
      </li>
      <li class="search-item">
        <form action="" method="get" class="user-management-search">
          <input type="text" id="searchInput" name="search" 
                 placeholder="Cari user..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <button type="submit">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </li>
    </ol>
  </div>

  <div class="accordion-container">
    <?php if ($users->num_rows > 0): ?>
      <?php while($row = $users->fetch_assoc()): ?>
        <div class="accordion-item">
          <button class="accordion-header">
            <span><?= htmlspecialchars($row['username']) ?> - <?= htmlspecialchars($row['role'] ?? 'Unknown') ?></span>
            <span class="icon">+</span>
          </button>
          <div class="accordion-content">
            <div class="accordion-body">
              <p><strong>Nama:</strong> <?= htmlspecialchars($row['name']) ?></p>
              <p><strong>Role:</strong> <?= htmlspecialchars($row['role'] ?? 'Unknown') ?></p>
              <p><strong>Initial:</strong> <?= htmlspecialchars($row['initial']) ?></p>
              <div class="actions">
                <a href="edit_user.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">Edit</a>
                <a href="delete_user.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Yakin mau hapus user ini?')">Delete</a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center">Tidak ada user ditemukan</p>
    <?php endif; ?>
  </div>
</section> 

<!-- Modal Create User -->
<div id="createUserModal" class="modal hidden">
  <div class="modal-content">
    <span id="closeCreateModal" class="close">&times;</span>
    <h2>Create User</h2>
    <!-- Arahkan ke create_user.php -->
    <form action="create_user.php" method="post">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role_id" required>
          <option value="2">Supervisor</option>
          <option value="3">Staff</option>
        </select>
      </div>
      <div class="form-group">
        <label>Initial</label>
        <input type="text" name="initial" required>
      </div>

      <!-- Auto Need Approval -->
      <input type="hidden" name="need_apv" value="1">

      <button type="submit" class="btn btn-primary">Create User</button>
    </form>
  </div>
</div>


<script src="modules/js/user-manage.js"></script>

<?php
$content = ob_get_clean(); // ambil output buffering
require_once "modules/layout/template.php";
?>
