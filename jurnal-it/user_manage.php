<?php
session_start();
require_once "direct/config.php";
$role_id = $_SESSION['role_id'] ?? 0;
if ($role_id != 1) {
   header("Location: home.php");
   exit;
}
ob_start();

// ================= QUERY TUNGGAL DENGAN SORTING =================
$search = $_GET['search'] ?? '';

// Default sorting: Role DESC (SPV dulu), kemudian username ASC
$sort_field = 'u.role_id';
$sort_order = 'DESC';
$secondary_sort = 'u.username ASC';

// Query sorting :
$sql = "SELECT 
          u.id, u.username, u.name, u.role_id, u.initial, 
          u.last_modified_datetime, u.modified_by,
          r.description AS role,
          rm.description AS modified_by_role
        FROM user u
        LEFT JOIN role r ON u.role_id = r.id
        LEFT JOIN role rm ON u.modified_by = rm.id
        WHERE u.role_id != 1";

// Handle search
if (!empty($search)) {
    $search_term = "%" . $conn->real_escape_string($search) . "%";
    $sql .= " AND (u.username LIKE '$search_term' OR u.name LIKE '$search_term')";
}

// Pakai CASE statement untuk urutan role yang benar
$sql .= " ORDER BY 
          CASE 
            WHEN u.role_id = 2 THEN 1  -- SPV pertama
            WHEN u.role_id = 1 THEN 2  -- Staff kedua
            ELSE 3
          END,
          u.username ASC";

// Eksekusi query
$users = $conn->query($sql);
if (!$users) {
    die("Query error: " . $conn->error);
}
?>

<link rel="stylesheet" href="dist/css/user-management.css">

<?php if (isset($_GET['msg'])): ?>
  <div class="notif <?= htmlspecialchars($_GET['msg']) ?>">
    <?php if ($_GET['msg'] === 'created'): ?>
      <i class="fas fa-user-plus"></i> User berhasil dibuat.
    <?php elseif ($_GET['msg'] === 'updated'): ?>
      <i class="fas fa-save"></i> User berhasil diperbarui.
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
      <i class="fas fa-user-slash"></i> User berhasil dihapus.
    <?php elseif ($_GET['msg'] === 'invalid_request'): ?>
      <i class="fas fa-exclamation-triangle"></i> Permintaan tidak valid.
    <?php endif; ?>
  </div>
<?php endif; ?>

<button type="button" class="btn btn-outline-info mb-2">+ Create User</button>

<!-- Card User Management -->
<section class="unfulfilled-section">
  <div class="section-header">
    <ol class="user-breadcrumb-flex">
      <li class="user-breadcrumb-item current">
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
      <div class="accordion-item" data-id="<?= $row['id'] ?>">
        <button class="accordion-header">
          <span class="user-username">
            <?= htmlspecialchars($row['username']) ?> - <?= htmlspecialchars($row['role'] ?? 'Unknown') ?>
          </span>
          <span class="icon">+</span>
        </button>
        <div class="accordion-content">
          <div class="accordion-body">
            <p><strong>Nama:</strong> <span class="user-name"><?= htmlspecialchars($row['name']) ?></span></p>
            <p><strong>Role:</strong> <span class="user-role"><?= htmlspecialchars($row['role'] ?? 'Unknown') ?></span></p>
            <p><strong>Initial:</strong> <span class="user-initial"><?= htmlspecialchars($row['initial']) ?></span></p>
            <p><strong>Last Modified:</strong> 
              <?= $row['last_modified_datetime'] ? date("d M Y H:i", strtotime($row['last_modified_datetime'])) : '-' ?>
            </p>
            <p><strong>Modified By:</strong> <?= htmlspecialchars($row['modified_by_role'] ?? '-') ?></p>

            <div class="actions">
              <a href="#"
                 class="action-btn edit-btn edit-user-btn"
                 data-id="<?= $row['id'] ?>"
                 data-username="<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>"
                 data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                 data-role="<?= $row['role_id'] ?>"
                 data-initial="<?= htmlspecialchars($row['initial'], ENT_QUOTES) ?>">
                Edit
              </a>
              <a href="direct/delete_user.php?id=<?= $row['id'] ?>"
                 class="action-btn delete-btn"
                 onclick="return confirm('Anda yakin ingin menghapus user ini?')">Delete</a>
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
    <form id="createUserForm" action="direct/create_user.php" method="POST">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Nama:</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Role:</label>
            <select name="role_id" required>
                <option value="3">Staff</option>
                <option value="2">SPV</option>
            </select>
        </div>
        <div class="form-group">
            <label>Initial:</label>
            <input type="text" name="initial" required>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-actions">
            <button type="submit">Buat</button>
        </div>
    </form>
  </div>
</div>

<!-- Modal Edit User -->
<div id="editUserModal" class="modal hidden">
  <div class="modal-content">
    <span id="closeEditModal" class="close">&times;</span>
    <h2>Edit User</h2>
    <form id="editUserForm" action="direct/edit_user.php" method="POST">
        <input type="hidden" name="id" id="editUserId">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" id="editUsername" required>
        </div>
        <div class="form-group">
            <label>Nama:</label>
            <input type="text" name="name" id="editUserName" required>
        </div>
        <div class="form-group">
            <label>Role:</label>
            <select name="role_id" id="editUserRole" required>
                <option value="3">Staff</option>
                <option value="2">SPV</option>
            </select>
        </div>
        <div class="form-group">
            <label>Initial:</label>
            <input type="text" name="initial" id="editInitial" required>
        </div>
        <div class="form-group">
            <label>Password (biarkan kosong jika tidak diubah):</label>
            <input type="password" name="password" id="editPassword">
        </div>
        <div class="form-actions">
            <button type="submit">Simpan</button>
        </div>
    </form>
  </div>
</div>

<script src="dist/js/user-manage.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>