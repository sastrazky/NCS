<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';
$step = 1; // Default step verifikasi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. LOGIKA LOGIN UTAMA
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :u LIMIT 1");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_admin'] = $user['id_admin'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } 
    
    // 2. TAHAP 1 RESET: VERIFIKASI DATA
    elseif (isset($_POST['cek_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("SELECT id_admin FROM admin WHERE username = :u AND email = :e LIMIT 1");
        $stmt->execute(['u' => $username, 'e' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_id'] = $user['id_admin']; 
            $step = 2; // Lanjut ke form input password baru
        } else {
            $error = "Username atau Email tidak ditemukan!";
        }
    }

    // 3. TAHAP 2 RESET: UPDATE PASSWORD & REDIRECT KE LOGIN
    elseif (isset($_POST['update_password_final'])) {
        if (isset($_SESSION['reset_id'])) {
            $new_password = $_POST['new_password'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update = $pdo->prepare("UPDATE admin SET password = :p WHERE id_admin = :id");
            $update->execute(['p' => $hashed_password, 'id' => $_SESSION['reset_id']]);
            
            unset($_SESSION['reset_id']); 

            // Redirect ke halaman login murni (membersihkan URL ?action=forgot)
            header("Location: login.php?reset=success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="icon" type="image/jpeg" href="../assets/image/LOGOC.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #264C70; height: 100vh; }
        .login-card { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(4px); width: 420px; padding: 30px 35px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.25); }
        .form-control { height: 42px; padding-left: 40px; }
        .input-icon { position: absolute; left: 12px; top: 10px; font-size: 18px; color: #777; z-index: 10; }
        .btn-custom { border: none; height: 40px; font-weight: 600; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center">

<div class="login-card">
    <h3 class="text-center mb-4 fw-bold" style="color:#fff;">
        <?= ($step == 2) ? 'Ganti Password' : 'Admin Login' ?>
    </h3>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2" style="font-size: 14px;"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
        <div class="alert alert-success text-center py-2" style="font-size: 14px;">Password berhasil diperbarui!</div>
    <?php endif; ?>

    <?php if (!isset($_GET['action']) && $step == 1): ?>
    <form method="POST">
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-person"></i>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-lock"></i>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" name="login" class="btn btn-info w-100 mb-3 text-white btn-custom">Sign In</button>
        <div class="text-center">
            <a href="?action=forgot" class="text-dark fw-bold small text-decoration-none">Lupa Password?</a>
        </div>
    </form>

    <?php elseif (isset($_GET['action']) && $_GET['action'] == 'forgot' && $step == 1): ?>
    <form method="POST">
        <p class="text-center small mb-3">Verifikasi data admin untuk mereset.</p>
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-person"></i>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-envelope"></i>
            <input type="email" name="email" class="form-control" placeholder="Email Terdaftar" required>
        </div>
        <button type="submit" name="cek_user" class="btn btn-warning w-100 mb-3 btn-custom">Cek Akun</button>
        <div class="text-center">
            <a href="login.php" class="text-danger fw-bold small text-decoration-none">Kembali ke Login</a>
        </div>
    </form>

    <?php elseif ($step == 2): ?>
    <form method="POST">
        <p class="text-center small mb-3 text-primary fw-bold">Data Terverifikasi!</p>
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-key"></i>
            <input type="password" name="new_password" class="form-control" placeholder="Ketik Password Baru" required minlength="5">
        </div>
        <button type="submit" name="update_password_final" class="btn btn-success w-100 mb-3 btn-custom text-white">Update & Login</button>
    </form>
    <?php endif; ?>

</div>

</body>
</html>