<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    require_once "header.php";
    ?>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="page-wrapper">
        <?php require_once "navbar.php"; ?>
        <main class="main-content">
            <?= $content ?>
        </main>
        <footer class="footer">
        <?php require_once "footer.php"; ?>
        </footer>
    <?php require_once "script.php"; ?>
    </div>
</body>
</html>