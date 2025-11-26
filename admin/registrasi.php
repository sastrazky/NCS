<?php
session_start();
require_once __DIR__ . '/../config.php';

// Buat pesan error / sukses
$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama_lengkap']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    // Validasi password cocok
    if ($password !== $confirm) {
        $pesan = "<div class='alert alert-danger'>Password tidak sama!</div>";
    } else {

        // Cek username sudah ada atau belum
        $stmt = $pdo->prepare("SELECT username FROM admin WHERE username = :u");
        $stmt->execute(['u' => $username]);

        if ($stmt->rowCount() > 0) {
            $pesan = "<div class='alert alert-danger'>Username sudah digunakan!</div>";
        } else {

            // Hash password
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert ke database
            $sql = "INSERT INTO admin (username, password, nama_lengkap, email, created_at)
                    VALUES (:u, :p, :n, :e, NOW())";

            $insert = $pdo->prepare($sql);

            if ($insert->execute([
                'u' => $username,
                'p' => $hash,
                'n' => $nama,
                'e' => $email
            ])) {

                // Redirect ke login
                header("Location: login.php?msg=register_success");
                exit;

            } else {
                $pesan = "<div class='alert alert-danger'>Gagal registrasi!</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="width: 420px;">
    <h4 class="text-center mb-3">Registrasi Admin</h4>

    <?= $pesan ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm" class="form-control" required autocomplete="off">
        </div>

        <button class="btn btn-primary w-100">Daftar</button>
        <a href="login.php" class="d-block text-center mt-3">Sudah punya akun? Login</a>

    </form>
</div>

</body>
</html>
