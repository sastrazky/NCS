<?php
// pages/user/sejarah.php
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);
?>

<!-- Page Header -->
<section class="page-header">
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
<section class="section">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="content-box">
                    <h5 class="fw-bold mb-3">Menu Profil</h5>
                    <div class="list-group list-group-flush">
                        <a href="?page=sejarah" class="list-group-item list-group-item-action active">
                            <i class="fas fa-history me-2"></i>Sejarah
                        </a>
                        <a href="?page=visi-misi" class="list-group-item list-group-item-action">
                            <i class="fas fa-bullseye me-2"></i>Visi & Misi
                        </a>
                        <a href="?page=anggota" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Anggota Tim
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if ($profil): ?>
                    <div class="content-box">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-history me-2"></i>Sejarah Lab Network and Cyber Security
                        </h3>
                        
                        <?php if (!empty($profil['logo_path'])): ?>
                            <div class="text-center mb-4">
                                <img src="<?= htmlspecialchars($profil['logo_path']) ?>" 
                                     alt="Logo NCS" 
                                     class="img-fluid" 
                                     style="max-height: 300px;">
                            </div>
                        <?php endif; ?>
                        
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

<?php
// ====================================
// pages/user/visi-misi.php
// ====================================
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);
?>

<!-- Page Header -->
<section class="page-header">
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
<section class="section">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="content-box">
                    <h5 class="fw-bold mb-3">Menu Profil</h5>
                    <div class="list-group list-group-flush">
                        <a href="?page=sejarah" class="list-group-item list-group-item-action">
                            <i class="fas fa-history me-2"></i>Sejarah
                        </a>
                        <a href="?page=visi-misi" class="list-group-item list-group-item-action active">
                            <i class="fas fa-bullseye me-2"></i>Visi & Misi
                        </a>
                        <a href="?page=anggota" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Anggota Tim
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if ($profil): ?>
                    <!-- Visi -->
                    <div class="content-box">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-eye me-2"></i>Visi
                        </h3>
                        <div style="text-align: justify; line-height: 2;">
                            <?= nl2br(htmlspecialchars($profil['visi'])) ?>
                        </div>
                    </div>

                    <!-- Misi -->
                    <div class="content-box">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-bullseye me-2"></i>Misi
                        </h3>
                        <?php
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

<?php
// ====================================
// pages/user/anggota.php
// ====================================
$anggota_query = pg_query($conn, "SELECT * FROM anggota WHERE status = 'Aktif' ORDER BY urutan ASC, nama_lengkap ASC");
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active">Anggota Tim</li>
            </ol>
        </nav>
        <h1>Anggota Tim</h1>
        <p>Tim Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="content-box">
                    <h5 class="fw-bold mb-3">Menu Profil</h5>
                    <div class="list-group list-group-flush">
                        <a href="?page=sejarah" class="list-group-item list-group-item-action">
                            <i class="fas fa-history me-2"></i>Sejarah
                        </a>
                        <a href="?page=visi-misi" class="list-group-item list-group-item-action">
                            <i class="fas fa-bullseye me-2"></i>Visi & Misi
                        </a>
                        <a href="?page=anggota" class="list-group-item list-group-item-action active">
                            <i class="fas fa-users me-2"></i>Anggota Tim
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="section-title text-start">
                    <h2>Tim Kami</h2>
                    <p>Anggota Lab Network and Cyber Security</p>
                </div>

                <?php if (pg_num_rows($anggota_query) > 0): ?>
                    <div class="row g-4">
                        <?php while($anggota = pg_fetch_assoc($anggota_query)): ?>
                            <div class="col-md-4">
                                <div class="team-member">
                                    <?php if (!empty($anggota['foto_path']) && file_exists($anggota['foto_path'])): ?>
                                        <img src="<?= htmlspecialchars($anggota['foto_path']) ?>" 
                                             alt="<?= htmlspecialchars($anggota['nama_lengkap']) ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light" 
                                             style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 1rem;">
                                            <i class="fas fa-user text-muted" style="font-size: 4rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h5><?= htmlspecialchars($anggota['nama_lengkap']) ?></h5>
                                    
                                    <?php if (!empty($anggota['jabatan'])): ?>
                                        <div class="position"><?= htmlspecialchars($anggota['jabatan']) ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="nip">NIP/NIM: <?= htmlspecialchars($anggota['nip_nim']) ?></div>
                                    
                                    <?php if (!empty($anggota['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($anggota['email']) ?>" 
                                           class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada data anggota.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>