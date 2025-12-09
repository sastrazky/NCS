<?php
include '../../config.php';

if (!isset($_GET['id'])) {
    die("ID arsip tidak ditemukan.");
}

$id = (int)$_GET['id'];

$query = pg_query_params($conn, "SELECT * FROM arsip WHERE id_arsip = $1", [$id]);
$data = pg_fetch_assoc($query);

if (!$data) {
    die("Data arsip tidak ditemukan.");
}

// Ambil kolom file_pdf_path
$file_path_db = $data['file_pdf_path'];

// Hilangkan slash awal "/"
$file_path_db = ltrim($file_path_db, '/');

// Build path sesuai struktur folder
$filepath = "../" . $file_path_db;

// Debug sementara
// echo "Mencari file di: " . realpath($filepath);
// exit;

if (!file_exists($filepath)) {
    die("File tidak ditemukan di server: " . $filepath);
}

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"" . $data['judul_dokumen'] . ".pdf\"");
header("Content-Length: " . filesize($filepath));

readfile($filepath);
exit;
?>