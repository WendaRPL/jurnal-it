<?php
session_start();

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // pastiin script berhenti
}


ob_start(); // mulai output buffering
require_once "cards.php";
require_once "sections.php";

$content = ob_get_clean(); // ambil output buffering
require_once "modules/layout/template.php";
?>
