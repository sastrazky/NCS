<?php
session_start();
require_once __DIR__ . '/../config.php';

// ----- OPSIONAL: DEBUG PASSWORD -----
// aktifkan jika mau test
// echo password_hash("admin", PASSWORD_BCRYPT);
// exit;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Ambil user dari database
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :u LIMIT 1");
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug (aktifkan jika mau lihat value)
//     echo "<pre>";
//     var_dump($user);
//     echo "</pre>";
//     exit;

    // Cek user dan password
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
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="width: 380px;">
    <h4 class="text-center mb-3">Login Admin</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="off">
        </div>

        <button class="btn btn-primary w-100">Masuk</button>
    </form>
</div>

</body>
</html>
