<?php
session_start();
require_once "modules/layout/header.php";
require_once "modules/layout/navbar.php";
require_once "cards.php";
require_once "sections.php";
require_once "modules/layout/footer.php";

renderHeader("Home - Jurnal IT");
?>
<body>
  <?php renderNavbar(); ?>
  <main>
    <?php renderCards(); ?>
    <?php renderUnfulfilledSection(); ?>
  </main>
  <?php renderFooter(); ?>
</body>
</html>
