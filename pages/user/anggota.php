<?php
// ====================================
// pages/user/anggota.php
// ====================================

// Ambil anggota aktif
$anggota_query = pg_query($conn,
    "SELECT * FROM anggota 
     WHERE status = 'Aktif' 
     ORDER BY urutan ASC, nama_lengkap ASC"
);
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

                                    <!-- Foto -->
                                    <?php
                                    $foto = htmlspecialchars($anggota['foto_path']);
                                    ?>

                                    <?php if (!empty($foto)): ?>
                                        <img src="<?= $foto ?>"
                                            alt="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                            class="img-member">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light"
                                            style="width:150px;height:150px;border-radius:50%;margin:0 auto 1rem;">
                                            <i class="fas fa-user text-muted" style="font-size:4rem;"></i>
                                        </div>
                                    <?php endif; ?>




                                    <!-- Nama -->
                                    <h5><?= htmlspecialchars($anggota['nama_lengkap']) ?></h5>

                                    <!-- Jabatan -->
                                    <?php if (!empty($anggota['jabatan'])): ?>
                                        <div class="position">
                                            <?= htmlspecialchars($anggota['jabatan']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- NIP/NIM -->
                                    <div class="nip">
                                        NIP/NIM: <?= htmlspecialchars($anggota['nip_nim']) ?>
                                    </div>

                                    <!-- Email -->
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
