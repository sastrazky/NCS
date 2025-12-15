<?php
// ====================================
// pages/user/visi-misi.php
// ====================================

// Ambil data dari tabel profil
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);
?>

<head>
    <link href="assets/css/visi-misi.css" rel="stylesheet">
</head>

<!-- Page Header -->
<section class="page-header-visi-misi">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active">Visi & Misi</li>
            </ol>
        </nav>
        <h1>Visi & Misi</h1>
        <p>Arah dan tujuan Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section-visi-misi">
    <div class="container">
        <div class="row">

            <!-- Main Content -->
            <div class="col-lg-9 mx-auto">
                <?php if ($profil): ?>

                    <!-- VISI -->
                    <div class="content-box-visi-misi">
                        <h3 class="text-primary mb-4">
                           Visi
                        </h3>

                        <div style="text-align: justify; line-height: 2;">
                            <?= nl2br(htmlspecialchars($profil['visi'])) ?>
                        </div>
                    </div>

                    <!-- MISI -->
                    <div class="content-box-visi-misi">
                        <h3 class="text-primary mb-4">
                            Misi
                        </h3>

                        <?php
                            // Pecah misi berdasarkan enter
                            $misi_list = explode("\n", $profil['misi']);
                            $misi_list = array_filter(array_map('trim', $misi_list));
                        ?>

                        <?php if (count($misi_list) > 0): ?>
                            <ol style="line-height: 2;">
                                <?php foreach ($misi_list as $misi_item): ?>
                                    <?php if (!empty($misi_item)): ?>
                                        <li class="mb-3"><?= htmlspecialchars($misi_item) ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        <?php else: ?>
                            <p class="text-muted fst-italic">Belum ada data misi.</p>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi visi dan misi belum tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
