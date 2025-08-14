<?php
require_once "modules/layout/header.php";
require_once "modules/layout/navbar.php";
require_once "modules/layout/cards.php";
require_once "modules/layout/sections.php";
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
