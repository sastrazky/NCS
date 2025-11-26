<?php
// actions/agenda_add.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_agenda = $_POST['judul_agenda'];
    $deskripsi = $_POST['deskripsi'];
    $lokasi = $_POST['lokasi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $id_admin = $_SESSION['id_admin'];
    
    $stmt = $pdo->prepare("
        INSERT INTO agenda (judul_agenda, deskripsi, lokasi, tanggal_mulai, tanggal_selesai, id_admin) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$judul_agenda, $deskripsi, $lokasi, $tanggal_mulai, $tanggal_selesai, $id_admin])) {
        header('Location: ../index.php?page=agenda&success=1');
    } else {
        header('Location: ../index.php?page=agenda&error=1');
    }
    exit();
}
?>