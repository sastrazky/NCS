<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    $id = $_SESSION['id_admin'];

    $stmt = $pdo->prepare("SELECT password FROM admin WHERE id_admin = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!password_verify($old, $row['password'])) {
        $err = "Password lama salah!";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE admin SET password=? WHERE id_admin=?");
        $upd->execute([$hash, $id]);
        $msg = "Password berhasil diubah!";
    }
}
?>

<div class="card p-4">
    <h4>Ubah Password</h4>
    <form method="POST">
        <label>Password Lama</label>
        <input type="password" name="old_password" class="form-control mb-2" required>

        <label>Password Baru</label>
        <input type="password" name="new_password" class="form-control mb-3" required>

        <button class="btn btn-primary">Update Password</button>
    </form>

    <?php if(isset($err)) echo "<p class='text-danger mt-2'>$err</p>"; ?>
    <?php if(isset($msg)) echo "<p class='text-success mt-2'>$msg</p>"; ?>
</div>
