<?php
$host = "localhost";
$port = "5432";
$dbname = "db_ncs";
$user = "postgres";
$pass = "241005";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");

if (!$conn) {
    die("Failed to connect to PostgreSQL: " . pg_last_error());
}

pg_query($conn, "SET TIMEZONE='Asia/Jakarta'");
date_default_timezone_set('Asia/Jakarta');

if (isset($_GET['download'])) {

    $id_arsip = (int)$_GET['download'];

    // Ambil data file
    $q = pg_query_params($conn, "
        SELECT file_pdf_path, judul_dokumen 
        FROM arsip 
        WHERE id_arsip = $1
    ", [$id_arsip]);

    $data = pg_fetch_assoc($q);

    if ($data) {

        $filepath = $data['file_pdf_path'];

        // Cek apakah file ada
        if (file_exists($filepath)) {

            // Header download
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=\"" . $data['judul_dokumen'] . ".pdf\"");
            header("Content-Length: " . filesize($filepath));

            readfile($filepath);
            exit;
        }
    }

    echo "<script>alert('File tidak ditemukan!'); history.back();</script>";
    exit;
}
?>