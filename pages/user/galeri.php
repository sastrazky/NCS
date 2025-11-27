<?php
// pages/user/galeri.php

// Filter - default ke Agenda
$filter_tab = isset($_GET['tab']) ? $_GET['tab'] : 'agenda';

// Pagination
$limit = 10;
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

    if ($tanggal_mulai > $today) {
        return ['status' => 'Akan Datang', 'class' => 'badge-upcoming'];
    } else if ($tanggal_mulai <= $today && (empty($tanggal_selesai) || $tanggal_selesai >= $today)) {
        return ['status' => 'Sedang Berlangsung', 'class' => 'badge-ongoing'];
    } else {
        return ['status' => 'Selesai', 'class' => 'badge-completed'];
    }
}
?>

<!-- Page Header -->
<section class="page-header">
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

<!-- Content -->
<section class="section">
    <div class="container">
        <!-- Filter Tabs -->
        <div class="text-center mb-5">
            <div class="btn-group shadow-sm" role="group">
                <a href="?page=galeri&tab=agenda" class="btn <?= $filter_tab == 'agenda' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
                    Agenda
                </a>
                <a href="?page=galeri&tab=kegiatan" class="btn <?= $filter_tab == 'kegiatan' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
                    Kegiatan
                </a>
            </div>
        </div>

        <?php if ($filter_tab == 'agenda'): ?>
            <!-- AGENDA VIEW -->
            <?php if (pg_num_rows($data_query) > 0): ?>
                <div class="row g-4">
                    <?php while ($agenda = pg_fetch_assoc($data_query)): ?>
                        <?php
                        $status_info = getAgendaStatus($agenda['tanggal_mulai'], $agenda['tanggal_selesai']);
                        $tgl_mulai = new DateTime($agenda['tanggal_mulai']);
                        $tgl_selesai = !empty($agenda['tanggal_selesai']) ? new DateTime($agenda['tanggal_selesai']) : null;
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Date Box -->
                                        <div class="col-auto">
                                            <div class="date-box">
                                                <div class="date-day"><?= $tgl_mulai->format('d') ?></div>
                                                <div class="date-month"><?= $tgl_mulai->format('M') ?></div>
                                                <div class="date-year"><?= $tgl_mulai->format('Y') ?></div>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="col">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($agenda['judul_agenda']) ?></h5>
                                                <span class="badge badge-custom <?= $status_info['class'] ?>">
                                                    <?= $status_info['status'] ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($agenda['deskripsi'])): ?>
                                                <p class="text-muted mb-2">
                                                    <?= htmlspecialchars(substr($agenda['deskripsi'], 0, 150)) ?>
                                                    <?= strlen($agenda['deskripsi']) > 150 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="d-flex align-items-center gap-3 text-muted small">
                                                <span>
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= $tgl_mulai->format('H:i') ?> - <?= $tgl_selesai ? $tgl_selesai->format('H:i') : '16:00' ?> WIB
                                                </span>
                                                <?php if (!empty($agenda['lokasi'])): ?>
                                                    <span>
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= htmlspecialchars($agenda['lokasi']) ?>
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
            <!-- KEGIATAN VIEW -->
            <?php if (pg_num_rows($data_query) > 0): ?>
                <div class="row g-4">
                    <?php while ($galeri = pg_fetch_assoc($data_query)): ?>
                        <?php
                        // ---------- START: media path logic (minimal, non-destructive) ----------
                        $original_media = !empty($galeri['media_path']) ? $galeri['media_path'] : '';
                        $media_path = $original_media ? "admin/" . $original_media : "assets/images/no-image.jpg";

                        // Jika file di server tidak ada, fallback ke no-image
                        if (!file_exists($media_path)) {
                            $media_path = "assets/images/no-image.jpg";
                        }
                        // ---------- END: media path logic ----------
                        ?>

                        <div class="col-md-4">
                            <div class="card-custom">
                                <div class="gallery-item" onclick="viewMedia('<?= htmlspecialchars($media_path) ?>', '<?= $galeri['tipe_media'] ?>', '<?= htmlspecialchars(addslashes($galeri['judul'])) ?>')">

                                    <?php if ($galeri['tipe_media'] == 'Foto' && !empty($galeri['media_path']) && file_exists($media_path)): ?>
                                        <img src="<?= htmlspecialchars($media_path) ?>" alt="<?= htmlspecialchars($galeri['judul']) ?>">
                                    <?php elseif ($galeri['tipe_media'] == 'Video' && !empty($galeri['media_path']) && file_exists($media_path)): ?>
                                        <video style="width: 100%; height: 100%; object-fit: cover;">
                                            <source src="<?= htmlspecialchars($media_path) ?>" type="video/mp4">
                                        </video>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fas fa-play-circle" style="font-size: 3rem; color: white; opacity: 0.8;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 100%;">
                                            <i class="fas fa-image text-white" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="gallery-overlay">
                                        <i class="fas fa-search-plus"></i>
                                    </div>

                                    <div class="gallery-info">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($galeri['judul']) ?></h6>
                                                <?php if (!empty($galeri['tanggal_kegiatan'])): ?>
                                                    <small>
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('d M Y', strtotime($galeri['tanggal_kegiatan'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge <?= $galeri['tipe_media'] == 'Foto' ? 'bg-primary' : 'bg-danger' ?>">
                                                <?= $galeri['tipe_media'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($galeri['deskripsi'])): ?>
                                    <div class="card-body">
                                        <p class="card-text text-muted mb-0" style="font-size: 0.9rem;">
                                            <?= htmlspecialchars(substr($galeri['deskripsi'], 0, 100)) ?>
                                            <?= strlen($galeri['deskripsi']) > 100 ? '...' : '' ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
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

        <!-- Pagination -->
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

<!-- Modal View Media (untuk Kegiatan) -->
<div class="modal fade" id="modalViewMedia" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="viewMediaTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="viewMediaBody">
            </div>
        </div>
    </div>
</div>

<script>
    function viewMedia(path, type, title) {
        document.getElementById('viewMediaTitle').textContent = title;
        const body = document.getElementById('viewMediaBody');

        if (type === 'Foto') {
            body.innerHTML = `
            <img src="${path}" class="img-fluid" 
                 style="max-height: 85vh; border-radius: 8px;">
        `;
        } else if (type === 'Video') {
            body.innerHTML = `
            <video controls autoplay style="max-height: 85vh; width: 100%; border-radius: 8px;">
                <source src="${path}" type="video/mp4">
                Browser kamu ga support video 😢
            </video>
        `;
        } else {
            body.innerHTML = `
            <div class="text-white">Media tidak tersedia 🤷‍♂️</div>
        `;
        }

        const myModal = new bootstrap.Modal(document.getElementById('modalViewMedia'));
        myModal.show();
    }
</script>