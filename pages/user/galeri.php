<?php
// pages/user/galeri.php

// Filter - default ke Agenda
$filter_tab = isset($_GET['tab']) ? (string)$_GET['tab'] : 'agenda'; // Tambahkan (string)
// Pagination
$limit = 3;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

if ($filter_tab == 'agenda') {
    // Get Agenda data
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM agenda");
    $total_records = pg_fetch_assoc($count_query)['total'];
    $total_pages = ceil($total_records / $limit);

    $data_query = pg_query($conn, "SELECT * FROM agenda ORDER BY tanggal_mulai DESC LIMIT $limit OFFSET $offset");
} else {
    // Get Kegiatan (Galeri) data
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM galeri");
    $total_records = pg_fetch_assoc($count_query)['total'];
    $total_pages = ceil($total_records / $limit);

    $data_query = pg_query($conn, "SELECT * FROM galeri ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

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

<section class="page-header-galeri">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item active">Galeri</li>
            </ol>
        </nav>
        <h1>Galeri Lab NCS</h1>
        <p>Dokumentasi agenda dan kegiatan Lab Network and Cyber Security</p>
    </div>
</section>

<section class="section-galeri">
    <div class="container">
<div class="text-center mb-5">
    <div class="btn-group shadow-sm" role="group">
        <a href="?page=galeri&tab=agenda" class="btn <?= $filter_tab == 'agenda' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
            <i class="fas fa-calendar-alt me-2"></i> Agenda
        </a>
        <a href="?page=galeri&tab=kegiatan" class="btn <?= $filter_tab == 'kegiatan' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
            <i class="fas fa-images me-2"></i> Kegiatan
        </a>
    </div>
</div>

        <?php if ($filter_tab == 'agenda'): ?>
            <?php if (pg_num_rows($data_query) > 0): ?>
                <div class="row g-4 justify-content-center">
                    <?php while ($agenda = pg_fetch_assoc($data_query)): ?>
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
                    <h4 class="mt-3 text-muted">Belum ada agenda</h4>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <?php if (pg_num_rows($data_query) > 0): ?>
                <div class="row g-4">
                    <?php while ($galeri = pg_fetch_assoc($data_query)): ?>
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
                    <h4 class="mt-3 text-muted">Belum ada kegiatan</h4>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($total_pages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=galeri&tab=<?= $filter_tab ?>&p=<?= $page_num - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                            <a class="page-link" href="?page=galeri&tab=<?= $filter_tab ?>&p=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=galeri&tab=<?= $filter_tab ?>&p=<?= $page_num + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="modalViewMedia" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 pt-0 pb-2">
                <h5 class="modal-title text-white fw-bold text-shadow" id="viewMediaTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0" id="viewMediaBody">
                </div>
        </div>
    </div>
</div>

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
                <p id="galeriModalDescription" class="text-muted"></p>
            </div>
            
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script>
    // Fungsi MODAL LAMA (Hanya Media Besar) - Dijaga untuk kompatibilitas
    function viewMedia(path, type, title) {
        document.getElementById('viewMediaTitle').textContent = title;
        const body = document.getElementById('viewMediaBody');

        if (type === 'Foto') {
            body.innerHTML = `<img src="${path}" class="img-fluid shadow-lg rounded" style="max-height: 85vh;">`;
        } else if (type === 'Video') {
            body.innerHTML = `
            <video controls autoplay class="shadow-lg rounded" style="max-height: 85vh; max-width: 100%;">
                <source src="${path}" type="video/mp4">
                Browser kamu ga support video 😢
            </video>`;
        }
        const myModal = new bootstrap.Modal(document.getElementById('modalViewMedia'));
        myModal.show();
    }
    
    // Fungsi MODAL BARU (Detail Card, sesuai permintaan)
    function showGaleriDetail(judul, deskripsi, tanggal, mediaPath, tipeMedia) {
        // Update Modal content
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
                <video controls style="width: 100%; height: 100%; object-fit: cover;">
                    <source src="${mediaPath}" type="video/mp4">
                    Browser kamu ga support video 😢
                </video>
            `;
        } else {
            viewer.innerHTML = `<img src="${mediaPath}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
        }

        // Show Modal
        const myModal = new bootstrap.Modal(document.getElementById('modalGaleriDetail'));
        myModal.show();
    }
</script>