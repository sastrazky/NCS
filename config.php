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

?>
