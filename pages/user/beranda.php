<?php

// Get profil data
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);

// Get latest agenda (hanya yang akan datang atau berlangsung)
$agenda_query = pg_query($conn, "SELECT * FROM agenda WHERE tanggal_mulai >= CURRENT_DATE ORDER BY tanggal_mulai ASC LIMIT 3");

// Get latest galeri
$galeri_query = pg_query($conn, "SELECT * FROM galeri ORDER BY created_at DESC LIMIT 3");

// Get stats
$stats = [
    'anggota' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM anggota"))['total'],
    'layanan' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM layanan"))['total'],
    'sarana' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana"))['total'],
    'arsip' => pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) as total FROM arsip"))['total']
];

// Helper function to get agenda status
function getAgendaStatus($tanggal_mulai, $tanggal_selesai)
{
    $today = date('Y-m-d');
    $end_date = !empty($tanggal_selesai) ? $tanggal_selesai : $tanggal_mulai;

    if ($tanggal_mulai > $today) {
        return ['status' => 'Akan Datang', 'class' => 'bg-primary'];
    } else if ($end_date < $today) {
        return ['status' => 'Selesai', 'class' => 'bg-secondary'];
    } else {
        return ['status' => 'Berlangsung', 'class' => 'bg-success'];
    }
}
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

<!-- AGENDA SECTION - Updated Design -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Jadwal Agenda Lab NCS</h2>
            <p>Lihat semua agenda, baik yang akan datang maupun yang sedang berjalan.</p>
        </div>

        <?php if (pg_num_rows($agenda_query) > 0): ?>
            <div class="row g-4 justify-content-center">
                <?php while ($agenda = pg_fetch_assoc($agenda_query)): ?>
                    <?php
                    $status_info = getAgendaStatus($agenda['tanggal_mulai'], $agenda['tanggal_selesai']);
                    $tgl_mulai = new DateTime($agenda['tanggal_mulai']);
                    ?>
                    <div class="col-lg-10">
                        <div class="card-custom agenda-card h-100">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-auto mb-3 mb-md-0">
                                        <div class="date-box shadow-sm">
                                            <div class="date-day"><?= $tgl_mulai->format('d') ?></div>
                                            <div class="date-month"><?= $tgl_mulai->format('M') ?></div>
                                            <div class="date-year"><?= $tgl_mulai->format('Y') ?></div>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($agenda['judul_agenda']) ?></h5>
                                            <span class="badge rounded-pill <?= $status_info['class'] ?>">
                                                <?= $status_info['status'] ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($agenda['deskripsi'])): ?>
                                            <p class="text-muted mb-3 desc-agenda">
                                                <?= htmlspecialchars($agenda['deskripsi']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="d-flex align-items-center gap-4 text-secondary small">
                                            <?php if (!empty($agenda['lokasi'])): ?>
                                                <span>
                                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                                    <?= htmlspecialchars($agenda['lokasi']) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($agenda['waktu_mulai'])): ?>
                                                <span>
                                                    <i class="far fa-clock me-2 text-primary"></i>
                                                    <?= date('H:i', strtotime($agenda['waktu_mulai'])) ?> WIB
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Belum ada agenda mendatang</h4>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php?page=galeri&tab=agenda" class="btn btn-primary">
                Lihat Semua Agenda <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- GALERI SECTION - Updated Design -->
<section class="section-galeri">
    <div class="container">
        <div class="section-title">
            <h2>Galeri Kegiatan</h2>
            <p>Dokumentasi kegiatan Lab NCS</p>
        </div>

        <?php if (pg_num_rows($galeri_query) > 0): ?>
            <div class="row g-4">
                <?php 
                $count = 0;
                while ($galeri = pg_fetch_assoc($galeri_query)): 
                    if ($count >= 6) break; // Limit to 6 items
                    $count++;
                ?>
                    <?php
                    // Media Logic
                    $original_media = !empty($galeri['media_path']) ? $galeri['media_path'] : '';
                    $media_path = $original_media ? "admin/" . $original_media : "assets/images/no-image.jpg";
                    if (!file_exists($media_path)) {
                        $media_path = "assets/images/no-image.jpg";
                    }
                    
                    $is_video = ($galeri['tipe_media'] == 'Video');
                    ?>

                    <div class="col-md-4">
                        <div class="card-custom galeri-card h-100" 
                            onclick="showGaleriDetail(
                                '<?= htmlspecialchars(addslashes($galeri['judul'])) ?>', 
                                '<?= htmlspecialchars(addslashes($galeri['deskripsi'])) ?>', 
                                '<?= htmlspecialchars($galeri['tanggal_kegiatan'] ?? $galeri['created_at']) ?>',
                                '<?= htmlspecialchars($media_path) ?>',
                                '<?= $galeri['tipe_media'] ?>'
                            )"
                            style="cursor: pointer;">
                            
                            <div class="galeri-image-wrapper">
                                <?php if ($is_video): ?>
                                    <div class="video-overlay-icon">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <video class="galeri-img-content">
                                        <source src="<?= htmlspecialchars($media_path) ?>#t=0.5" type="video/mp4">
                                    </video>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($media_path) ?>" 
                                        alt="<?= htmlspecialchars($galeri['judul']) ?>" 
                                        class="galeri-img-content">
                                <?php endif; ?>

                                <div class="galeri-content-overlay">
                                    <small class="text-white d-block mb-1 text-shadow">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('d M Y', strtotime($galeri['tanggal_kegiatan'] ?? $galeri['created_at'])) ?>
                                    </small>
                                    
                                    <h5 class="card-title-galeri text-white text-shadow mb-0">
                                        <?= htmlspecialchars($galeri['judul']) ?>
                                    </h5>
                                </div>
                                <div class="position-absolute bottom-0 end-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75">
                                        <i class="fas <?= $is_video ? 'fa-video' : 'fa-camera' ?> me-1"></i>
                                        <?= $galeri['tipe_media'] ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-body-galeri">
                                <?php if (!empty($galeri['deskripsi'])): ?>
                                    <p class="desc-galeri">
                                        <?= htmlspecialchars($galeri['deskripsi']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-images text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Belum ada galeri</h4>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php?page=galeri&tab=kegiatan" class="btn btn-primary">
                Lihat Semua Galeri <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Modal Galeri Detail -->
<div class="modal fade" id="modalGaleriDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="galeriModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body pt-0">
                <div class="text-center mb-3">
                    <div id="galeriMediaViewer" class="rounded shadow-sm" style="max-height: 400px; overflow: hidden; margin: 0 auto; width: 100%;">
                    </div>
                </div>
                
                <h6 class="fw-bold mb-1 mt-3">Tanggal Kegiatan:</h6>
                <p id="galeriModalDate" class="mb-3 text-muted"></p>

                <h6 class="fw-bold mb-1">Deskripsi:</h6>
                <div id="descWrapper" class="desc-scroll">
                    <p id="galeriModalDescription" class="text-muted"></p>
                </div>
            </div>
            
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showGaleriDetail(judul, deskripsi, tanggal, mediaPath, tipeMedia) {
        document.getElementById('galeriModalTitle').textContent = judul;
        document.getElementById('galeriModalDescription').textContent = deskripsi;
        
        // Format Tanggal
        const dateObj = new Date(tanggal);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('galeriModalDate').textContent = dateObj.toLocaleDateString('id-ID', options);

        // Update Media Viewer
        const viewer = document.getElementById('galeriMediaViewer');
        
        if (tipeMedia === 'Video') {
            viewer.innerHTML = `
                <video controls style="width: 100%; height: auto; max-height: 400px; object-fit: contain; border-radius: 8px;">
                    <source src="${mediaPath}" type="video/mp4">
                    Browser kamu ga support video 😢
                </video>
            `;
        } else {
            viewer.innerHTML = `<img src="${mediaPath}" class="img-fluid" style="width: 100%; height: auto; max-height: 400px; object-fit: contain; border-radius: 8px;">`;
        }

        // Show Modal
        const myModal = new bootstrap.Modal(document.getElementById('modalGaleriDetail'));
        myModal.show();
    }
</script>