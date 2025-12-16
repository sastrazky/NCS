<?php
// ambil data profil
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);

// logika logo
if ($profil && !empty($profil['logo_path'])) {
    $logo_path = "admin/" . $profil['logo_path'];
} else {
    $logo_path = "assets/images/logo-ncs.png";
}

// Jika file tidak ada → fallback
if (!file_exists($logo_path)) {
    $logo_path = "assets/images/logo-ncs.png";
}
?>

<head>
    <link href="assets/css/sejarah.css" rel="stylesheet">
</head>

<!-- Page Header -->
<section class="page-header-sejarah">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active">Sejarah</li>
            </ol>
        </nav>
        <h1>Sejarah Lab NCS</h1>
        <p>Perjalanan Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section-sejarah">
    <div class="container">
        <div class="row">

            <!-- Main Content -->
            <div class="col-lg-9 mx-auto">
                <?php if ($profil): ?>
                    <div class="content-box-sejarah">
                        <!-- Logo  -->
                        <div class="text-center mb-4">
                            <img src="<?= htmlspecialchars($logo_path) ?>"
                                alt="Logo NCS"
                                class="img-fluid"
                                style="max-height: 300px;"
                                onerror="this.src='assets/images/logo-ncs.png'">
                        </div>

                        <div style="text-align: justify; line-height: 2;">
                            <?= nl2br(htmlspecialchars($profil['sejarah'])) ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi sejarah belum tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>