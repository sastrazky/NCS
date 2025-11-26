<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah sudah login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}
?>
