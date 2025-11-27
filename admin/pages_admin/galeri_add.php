<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tipe_media = $_POST['tipe_media'];
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'];

    // Handle upload media
    $media_path = '';
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $target_dir = "../uploads/galeri/";
        $target_file = $target_dir . basename($_FILES["media"]["name"]);
        move_uploaded_file($_FILES["media"]["tmp_name"], $target_file);
        $media_path = $target_file;
    }

    $pdo->prepare("INSERT INTO galeri (judul, deskripsi, media_path, tipe_media, tanggal_kegiatan, id_admin) VALUES (?, ?, ?, ?, ?, ?)")
         ->execute([$judul, $deskripsi, $media_path, $tipe_media, $tanggal_kegiatan, $_SESSION['admin_id']]);
    header('Location: ../pages/galeri.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Galeri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container p-4">
        <h1>Tambah Galeri</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Judul</label>
                <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Tipe Media</label>
                <select name="tipe_media" class="form-control">
                    <option value="image">Gambar</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Tanggal Kegiatan</label>
                <input type="date" name="tanggal_kegiatan" class="form-control">
            </div>
            <div class="mb-3">
                <label>Media</label>
                <input type="file" name="media" class="form-control" accept="image/*,video/*">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="../pages/galeri.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>