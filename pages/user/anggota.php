<?php
// Query sesuai struktur tabel
$query = pg_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC");
?>

<div class="container my-4">
    <h3 class="mb-4 text-center fw-bold">Anggota Tim</h3>

    <div class="row">
        <?php while ($row = pg_fetch_assoc($query)) : ?>

            <?php
            // Ambil path foto dari database
            $foto = $row['foto_path'];

            // Path foto untuk user
            // Admin upload → admin/uploads/anggota/xxx.jpg
            // User akses → admin/uploads/anggota/xxx.jpg (karena index.php ada di root)
            if (!empty($foto)) {
                $file_path = "admin/" . $foto;
            } else {
                $file_path = "assets/images/no-image.jpg";
            }

            // Jika file tidak ada
            if (!file_exists($file_path)) {
                $file_path = "assets/images/no-image.jpg";
            }
            ?>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0" style="min-height: 450px;">

                    <img src="<?= htmlspecialchars($file_path) ?>"
                         alt="Foto Anggota"
                         class="card-img-top"
                         style="height: 250px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                        </h5>

                        <p class="card-text mb-1">
                            <strong>NIP/NIM:</strong> <?= htmlspecialchars($row['nip_nim']) ?>
                        </p>

                        <p class="card-text mb-1">
                            <strong>Jabatan:</strong> <?= htmlspecialchars($row['jabatan']) ?>
                        </p>

                        <p class="card-text mb-1">
                            <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?>
                        </p>

                        <p class="card-text">
                            <strong>Urutan:</strong> <?= htmlspecialchars($row['urutan']) ?>
                        </p>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    </div>
</div>
