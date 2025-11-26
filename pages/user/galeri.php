<?php
// pages/user/galeri.php

// Filter
$filter_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';

// Pagination
$limit = 12;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Build query
$where_clause = '';
$query_params = [];
if (!empty($filter_tipe)) {
    $where_clause = 'WHERE tipe_media = $1';
    $query_params[] = $filter_tipe;
}

// Get total records
if (!empty($filter_tipe)) {
    $count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM galeri $where_clause", $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM galeri");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (!empty($filter_tipe)) {
    $galeri_query = pg_query_params($conn, "SELECT * FROM galeri $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $query_params);
} else {
    $galeri_query = pg_query($conn, "SELECT * FROM galeri ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
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
        <div class="filter-tabs text-center mb-4">
            <a href="?page=galeri" class="filter-tab <?= empty($filter_tipe) ? 'active' : '' ?>">
                <i class="fas fa-th me-2"></i>Semua
            </a>
            <a href="?page=galeri&tipe=Foto" class="filter-tab <?= $filter_tipe == 'Foto' ? 'active' : '' ?>">
                <i class="fas fa-camera me-2"></i>Foto
            </a>
            <a href="?page=galeri&tipe=Video" class="filter-tab <?= $filter_tipe == 'Video' ? 'active' : '' ?>">
                <i class="fas fa-video me-2"></i>Video
            </a>
        </div>

        <!-- Gallery Grid -->
        <?php if (pg_num_rows($galeri_query) > 0): ?>
            <div class="row g-4">
                <?php while($galeri = pg_fetch_assoc($galeri_query)): ?>
                    <div class="col-md-4">
                        <div class="card-custom">
                            <div class="gallery-item" onclick="viewMedia('<?= htmlspecialchars($galeri['media_path']) ?>', '<?= $galeri['tipe_media'] ?>', '<?= htmlspecialchars(addslashes($galeri['judul'])) ?>')">
                                <?php if ($galeri['tipe_media'] == 'Foto' && !empty($galeri['media_path']) && file_exists($galeri['media_path'])): ?>
                                    <img src="<?= htmlspecialchars($galeri['media_path']) ?>" alt="<?= htmlspecialchars($galeri['judul']) ?>">
                                <?php elseif ($galeri['tipe_media'] == 'Video' && !empty($galeri['media_path']) && file_exists($galeri['media_path'])): ?>
                                    <video style="width: 100%; height: 100%; object-fit: cover;">
                                        <source src="<?= htmlspecialchars($galeri['media_path']) ?>" type="video/mp4">
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

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=galeri&p=<?= $page_num - 1 ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=galeri&p=<?= $i ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=galeri&p=<?= $page_num + 1 ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-images text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Belum ada galeri</h4>
                <p class="text-muted">Galeri akan ditampilkan di sini</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal View Media -->
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
        body.innerHTML = '<img src="' + path + '" class="img-fluid" style="max-height: 85vh; border-radius: 8px;">';
    } else {
        body.innerHTML = '<video class="img-fluid" style="max-height: 85vh; border-radius: 8px;" controls autoplay><source src="' + path + '" type="video/mp4"></video>';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalViewMedia'));
    modal.show();
}
</script>