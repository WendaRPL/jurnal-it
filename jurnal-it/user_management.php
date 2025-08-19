<?php
session_start();
require_once "modules/layout/header.php";
require_once "modules/layout/navbar.php"; 
require_once "cards.php";
require_once "modules/layout/footer.php";

renderHeader("User Management - Jurnal IT");
?>
<body>
  <?php renderNavbar(); ?>
  <main>
  <button type="button" class="btn btn-outline-info mb-2" style="display: flex;">+ Create User</button>
      <!-- Card User Management -->
<section class="unfulfilled-section">
  <div class="section-header">
      <ol>
        <li class="breadcrumb-item current" style="margin-left: -20px; margin-bottom: -7px">
        <span class="" style="margin-bottom: -8px">User Management</span>
        <div class="search-item" style="margin-left: auto; height: 18px;">
            <form action="" method="get" style="display: flex;">
              <input type="text" class="search-input" placeholder="Cari user...">
                <button type="submit" class="search-btn" style="height: 23.5px; width: 28px; background: var(--cyan); border: none; color: var(--dark);">
                <i class="fas fa-search" style="font-size:medium"></i>
            </button>
            </form>
        </div>
        </li>
      </ol>
  </div>
  <div class="reports-container">
    <table class="user-management-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th class="action-column">Edit</th>
          <th class="action-column">Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>001</td>
          <td>Tjut Karina Aisyairah</td>
          <td class="action-cell">
            <button class="action-btn edit-btn">Edit</button>
          </td>
          <td class="action-cell">
            <button class="action-btn delete-btn">Delete</button>
          </td>
        </tr>
        
        <tr>
          <td>002</td>
          <td>Danurwenda</td>
          <td class="action-cell">
            <button class="action-btn edit-btn">Edit</button>
          </td>
          <td class="action-cell">
            <button class="action-btn delete-btn">Delete</button>
          </td>
        </tr>
        
        <tr>
          <td>003</td>
          <td>Aditya Aditya</td>
          <td class="action-cell">
            <button class="action-btn edit-btn">Edit</button>
          </td>
          <td class="action-cell">
            <button class="action-btn delete-btn">Delete</button>
          </td>
        </tr>

        <tr>
          <td>004</td>
          <td>Missy</td>
          <td class="action-cell">
            <button class="action-btn edit-btn">Edit</button>
          </td>
          <td class="action-cell">
            <button class="action-btn delete-btn">Delete</button>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</section> 
  </main>
  <?php renderFooter(); ?>
</body>
</html>


