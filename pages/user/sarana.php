<?php
// pages/user/sarana.php

// Filter by kondisi
$filter_kondisi = isset($_GET['kondisi']) ? $_GET['kondisi'] : '';

// Pagination
$limit = 9;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Build query
$where_clause = '';
$query_params = [];

if (!empty($filter_kondisi)) {
    $where_clause = "WHERE kondisi = $1";
    $query_params[] = $filter_kondisi;
}

// Get total records
if (!empty($query_params)) {
    $count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM sarana_prasarana $where_clause", $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (!empty($query_params)) {
    $sarana_query = pg_query_params($conn, "SELECT * FROM sarana_prasarana $where_clause ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset", $query_params);
} else {
    $sarana_query = pg_query($conn, "SELECT * FROM sarana_prasarana ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset");
}

// Get statistics
$stats_query = pg_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(jumlah) as total_item,
        COUNT(CASE WHEN kondisi = 'Baik' THEN 1 END) as baik,
        COUNT(CASE WHEN kondisi = 'Rusak Ringan' THEN 1 END) as rusak_ringan,
        COUNT(CASE WHEN kondisi = 'Rusak Berat' THEN 1 END) as rusak_berat
    FROM sarana_prasarana
");
$stats = pg_fetch_assoc($stats_query);
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item active">Fasilitas</li>
            </ol>
        </nav>
        <h1>Fasilitas Lab NCS</h1>
        <p>Sarana dan prasarana Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Statistics -->
<section class="section bg-light py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-warehouse text-primary mb-2" style="font-size: 2rem;"></i>
                        <h3 class="fw-bold text-primary mb-0"><?= number_format($stats['total']) ?></h3>
                        <small class="text-muted">Jenis Fasilitas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes text-success mb-2" style="font-size: 2rem;"></i>
                        <h3 class="fw-bold text-success mb-0"><?= number_format($stats['total_item']) ?></h3>
                        <small class="text-muted">Total Item</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                        <h3 class="fw-bold text-success mb-0"><?= number_format($stats['baik']) ?></h3>
                        <small class="text-muted">Kondisi Baik</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-tools text-warning mb-2" style="font-size: 2rem;"></i>
                        <h3 class="fw-bold text-warning mb-0"><?= number_format($stats['rusak_ringan'] + $stats['rusak_berat']) ?></h3>
                        <small class="text-muted">Perlu Perbaikan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">
        <!-- Filter -->
        <div class="text-center mb-5">
            <div class="btn-group shadow-sm" role="group">
                <a href="?page=sarana" class="btn <?= empty($filter_kondisi) ? 'btn-primary' : 'btn-outline-secondary' ?> px-4 py-2">
                    Semua
                </a>
                <a href="?page=sarana&kondisi=Baik" class="btn <?= $filter_kondisi == 'Baik' ? 'btn-primary' : 'btn-outline-secondary' ?> px-4 py-2">
                    Baik
                </a>
                <a href="?page=sarana&kondisi=Rusak Ringan" class="btn <?= $filter_kondisi == 'Rusak Ringan' ? 'btn-primary' : 'btn-outline-secondary' ?> px-4 py-2">
                    Rusak Ringan
                </a>
                <a href="?page=sarana&kondisi=Rusak Berat" class="btn <?= $filter_kondisi == 'Rusak Berat' ? 'btn-primary' : 'btn-outline-secondary' ?> px-4 py-2">
                    Rusak Berat
                </a>
            </div>
        </div>

        <!-- Fasilitas Grid -->
        <?php if (pg_num_rows($sarana_query) > 0): ?>
            <div class="row g-4">
                <?php while ($sarana = pg_fetch_assoc($sarana_query)): ?>
                    <?php
                    $original_media = !empty($sarana['gambar_path']) ? $sarana['gambar_path'] : '';
                    $media_path = "uploads/sarana/" . $original_media;

                    if (!file_exists($media_path)) {
                        $media_path = "assets/images/no-image.jpg";
                    }

                    ?>

                    <div class="col-md-4">
                        <div class="card-custom facility-card">
                            <!-- Image -->
                            <div class="facility-image">
                                <?php if (!empty($sarana['gambar_path']) && file_exists($sarana['gambar_path'])): ?>
                                    <img src="<?= htmlspecialchars($media_path) ?>"
                                        alt="<?= htmlspecialchars($sarana['nama_fasilitas']) ?>"
                                        style="width: 100%; height: 200px; object-fit: cover;">

                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-warehouse text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Kondisi Badge -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <?php
                                    $kondisi_class = '';
                                    $kondisi_icon = '';
                                    switch ($sarana['kondisi']) {
                                        case 'Baik':
                                            $kondisi_class = 'bg-success';
                                            $kondisi_icon = 'fa-check-circle';
                                            break;
                                        case 'Rusak Ringan':
                                            $kondisi_class = 'bg-warning';
                                            $kondisi_icon = 'fa-exclamation-triangle';
                                            break;
                                        case 'Rusak Berat':
                                            $kondisi_class = 'bg-danger';
                                            $kondisi_icon = 'fa-times-circle';
                                            break;
                                        default:
                                            $kondisi_class = 'bg-secondary';
                                            $kondisi_icon = 'fa-question-circle';
                                    }
                                    ?>
                                    <span class="badge <?= $kondisi_class ?>">
                                        <i class="fas <?= $kondisi_icon ?> me-1"></i>
                                        <?= htmlspecialchars($sarana['kondisi'] ?? 'N/A') ?>
                                    </span>
                                </div>

                                <!-- Jumlah Badge -->
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75">
                                        <i class="fas fa-cubes me-1"></i>
                                        <?= number_format($sarana['jumlah'] ?? 0) ?> Unit
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">
                                    <?= htmlspecialchars($sarana['nama_fasilitas']) ?>
                                </h5>

                                <?php if (!empty($sarana['deskripsi'])): ?>
                                    <p class="card-text text-muted" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars(substr($sarana['deskripsi'], 0, 100)) ?>
                                        <?= strlen($sarana['deskripsi']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=sarana<?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>&p=<?= $page_num - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=sarana<?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>&p=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=sarana<?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>&p=<?= $page_num + 1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-warehouse text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Tidak ada fasilitas</h4>
                <p class="text-muted">
                    <?php if (!empty($filter_kondisi)): ?>
                        Tidak ada fasilitas dengan kondisi "<?= htmlspecialchars($filter_kondisi) ?>"
                    <?php else: ?>
                        Data fasilitas akan ditampilkan di sini
                    <?php endif; ?>
                </p>
                <?php if (!empty($filter_kondisi)): ?>
                    <a href="?page=sarana" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Lihat Semua Fasilitas
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Info Section -->
<section class="section bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-3">Informasi Fasilitas</h3>
                <p class="text-muted mb-0">
                    Lab Network and Cyber Security dilengkapi dengan berbagai fasilitas modern untuk mendukung
                    kegiatan pembelajaran, penelitian, dan pengabdian masyarakat di bidang keamanan jaringan dan siber.
                    Semua fasilitas dikelola dengan baik dan dipelihara secara berkala untuk memastikan kondisi optimal.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <div class="p-4 bg-white rounded shadow-sm">
                    <i class="fas fa-tools text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold">Pemeliharaan Rutin</h5>
                    <p class="text-muted small mb-0">
                        Fasilitas dirawat dan diperiksa secara berkala
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>