<?php

//  profil data
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);

//  latest agenda
$agenda_query = pg_query($conn, "SELECT * FROM agenda WHERE tanggal_mulai >= CURRENT_DATE ORDER BY tanggal_mulai ASC LIMIT 3");

$galeri_query = pg_query($conn, "SELECT * FROM galeri ORDER BY created_at DESC LIMIT 4");

//  stats
$stats = [
    'anggota' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM anggota"))['total'],
    'layanan' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM layanan"))['total'],
    'sarana' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana"))['total'],
    'arsip' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM arsip"))['total']
];
?>

<section class="hero">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 ">
                <h1><?= htmlspecialchars($profil['nama_profil'] ?? "Lab Network and Cyber Security") ?></h1>
                <p><?= nl2br(htmlspecialchars($profil['deskripsi_singkat'] ?? "Teknologi Informasi Polinema")) ?></p>
                <div class="mt-4">
                </div>
            </div>
        </div>
    </div>
</section>



<section class="stats-section section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?= number_format($stats['anggota']) ?></div>
                    <div class="stat-label">Anggota Tim</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-number"><?= number_format($stats['layanan']) ?></div>
                    <div class="stat-label">Layanan</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-number"><?= number_format($stats['sarana']) ?></div>
                    <div class="stat-label">Fasilitas</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="stat-number"><?= number_format($stats['arsip']) ?></div>
                    <div class="stat-label">Dokumen Arsip</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($profil): ?>
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Tentang Lab NCS</h2>
                <p>Mengenal lebih dekat Lab Network and Cyber Security</p>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="about-image-container">
                        <img src="assets/image/LABNCS.jpg" 
                            alt="Lab NCS"
                            class="img-fluid">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="content-box">
                        <h3>Sejarah</h3>
                        <p style="text-align: justify;">
                            <?= nl2br(htmlspecialchars(substr($profil['sejarah'] ?? '', 0, 400))) ?>...
                        </p>
                        <a href="?page=sejarah" class="btn btn-primary">
                            Selengkapnya <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
<?php endif; ?>

<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Jadwal Agenda Lab NCS</h2>
            <p>Lihat semua agenda, baik yang akan datang maupun yang sedang berjalan.</p>
        </div>

        <div class="row g-4">
            <?php if (pg_num_rows($agenda_query) > 0): ?>
                <?php while ($agenda = pg_fetch_assoc($agenda_query)): ?>
                    <?php
                    $tgl_mulai = new DateTime($agenda['tanggal_mulai']);
                    $tgl_selesai = !empty($agenda['tanggal_selesai'])
                        ? new DateTime($agenda['tanggal_selesai'])
                        : null;
                    ?>
                    <div class="col-md-4">
                        <div class="agenda-card">
                            <div class="agenda-date">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?= $tgl_mulai->format('d M Y') ?>
                            </div>

                            <h5 class="fw-bold"><?= htmlspecialchars($agenda['judul_agenda']) ?></h5>

                            <?php if (!empty($agenda['lokasi'])): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($agenda['lokasi']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($agenda['deskripsi'])): ?>
                                <p class="text-muted">
                                    <?= htmlspecialchars(substr($agenda['deskripsi'], 0, 100)) ?>...
                                </p>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Belum ada agenda mendatang</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="?page=galeri&tab=agenda" class="btn btn-primary">
                Lihat Semua Agenda <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>

    </div>
</section>

<section class="section-galeri">
    <div class="container">
        <div class="section-title">
            <h2>Galeri Kegiatan</h2>
            <p>Dokumentasi kegiatan Lab NCS</p>
        </div>

        <div class="row g-4">
            <?php if (pg_num_rows($galeri_query) > 0): ?>
                <?php while ($galeri = pg_fetch_assoc($galeri_query)): ?>
                    <?php
                    // Path media asli dari database
                    $original_media = !empty($galeri['media_path']) ? $galeri['media_path'] : '';
                    
                    // Path yang digunakan untuk tampilan (default ke no-image.jpg jika media_path kosong atau file tidak ada)
                    $media_path_display = $original_media ? "admin/" . $original_media : "assets/images/no-image.jpg";
                    if ($original_media && !file_exists($media_path_display)) {
                        $media_path_display = "assets/images/no-image.jpg";
                    }

                    // Tentukan link yang akan diklik (bisa ke modal atau langsung ke file)
                    $media_link = $original_media ? "admin/" . $original_media : "#";

                    // Tentukan ikon/teks jika media tidak ditemukan atau bukan foto
                    $icon_class = 'fas fa-image'; // Default icon
                    $media_is_photo = ($galeri['tipe_media'] === 'Foto' && !empty($original_media) && file_exists($media_path_display));
                    
                    if ($galeri['tipe_media'] === 'Video') {
                        $icon_class = 'fas fa-video'; 
                    }
                    ?>

                    <div class="col-md-3 col-sm-6">
                        <div class="gallery-item">
                            <a href="<?= htmlspecialchars($media_link) ?>" 
                                title="<?= htmlspecialchars($galeri['judul']) ?>" 
                                target="_blank" 
                                class="d-block"> 

                                <?php if ($media_is_photo): ?>
                                    <img src="<?= htmlspecialchars($media_path_display) ?>"
                                        alt="<?= htmlspecialchars($galeri['judul']) ?>"
                                        class="img-fluid" style="object-fit: cover; width: 100%; height: 200px;"> 

                                <?php else: ?>
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px; width: 100%;">
                                        <i class="<?= $icon_class ?> text-white" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="p-2">
                                <h5 class="mb-0 text-truncate"><?= htmlspecialchars($galeri['judul']) ?></h5>
                                <small class="text-muted"><?= htmlspecialchars($galeri['tipe_media']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Belum ada galeri</p>
                </div>
            <?php endif; ?>

        </div>

        <div class="text-center mt-4">
            <a href="?page=galeri&tab=kegiatan" class="btn btn-primary">
                Lihat Semua Galeri <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>

    </div>
</section>