<?php
// Urutkan dari urutan ASC (1,2,3,...)
$query = pg_query($conn, "SELECT * FROM anggota ORDER BY urutan ASC");
?>

<section class="page-header-anggota">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active">Anggota Tim</li>
            </ol>
        </nav>
        <h1>Anggota Tim</h1>
        <p>Para Anggota TIM Lab Network and Cyber Security</p>
    </div>
</section>

<section class="section-anggota">
    <div class="container">
        <div class="row justify-content-center">

            <?php while ($row = pg_fetch_assoc($query)) : ?>

                <?php
                $foto = $row['foto_path'];
                $file_path = (!empty($foto)) ? "admin/" . $foto : "assets/images/no-image.jpg";
                if (!file_exists($file_path)) $file_path = "assets/images/no-image.jpg";
                ?>

                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="member-card">
                        
                        <div class="image-wrapper">
                            <img src="<?= htmlspecialchars($file_path) ?>"
                                alt="Foto Anggota"
                                class="member-photo">
                        </div>

                        <div class="card-body text-center">
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($row['nama_lengkap']) ?></h5>
                            <p class="mb-1"><strong>Jabatan:</strong> <?= htmlspecialchars($row['jabatan']) ?></p>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>
    </div>
</section>
