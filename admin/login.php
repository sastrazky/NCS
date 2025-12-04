<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #264C70;
            height: 100vh;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(4px);
            width: 420px;
            padding: 30px 35px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }

        .form-control {
            height: 42px;
            padding-left: 40px;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 10px;
            font-size: 18px;
            color: #777;
        }

        .btn-login {
            background-color: #09BCE8;
            border: none;
            height: 40px;
            font-weight: 600;
        }

        .btn-login:hover {
            background-color: #08A8CF;
        }

        .small-links {
            font-size: 12px;
            color: #fff;
            opacity: 0.9;
        }

        .small-links:hover {
            opacity: 1;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center">

<div class="login-card">

    <h3 class="text-center mb-4 fw-bold" style="color:#fff;">Login Admin</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- Username -->
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-person"></i>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <!-- Password -->
        <div class="mb-3 position-relative">
            <i class="input-icon bi bi-eye-slash"></i>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button class="btn btn-login w-100 text-white">Sign In</button>

    </form>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</body>
</html>
