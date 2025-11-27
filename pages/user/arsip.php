<?php
// pages/user/arsip.php

// Filter - default ke Penelitian
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'Penelitian';

// Pagination
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Build query based on kategori
$where_clause = '';
$query_params = [];

if (!empty($filter_kategori)) {
    // Map kategori display ke kategori database
    $kategori_map = [
        'Penelitian' => ['Laporan', 'Proposal'],
        'Pengabdian' => ['Dokumentasi', 'Surat']
    ];
    
    if (isset($kategori_map[$filter_kategori])) {
        $where_clause = "WHERE kategori = ANY($1)";
        $query_params[] = '{' . implode(',', $kategori_map[$filter_kategori]) . '}';
    }
}

// Get total records
if (!empty($query_params)) {
    $count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM arsip $where_clause", $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM arsip");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (!empty($query_params)) {
    $arsip_query = pg_query_params($conn, "SELECT * FROM arsip $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $query_params);
} else {
    $arsip_query = pg_query($conn, "SELECT * FROM arsip ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

// Get statistics
$stats_query = pg_query($conn, "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN kategori IN ('Laporan', 'Proposal') THEN 1 END) as penelitian,
        COUNT(CASE WHEN kategori IN ('Dokumentasi', 'Surat') THEN 1 END) as pengabdian,
        COUNT(CASE WHEN EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) THEN 1 END) as tahun_ini
    FROM arsip
");
$stats = pg_fetch_assoc($stats_query);

// Get year statistics
$year_query = pg_query($conn, "
    SELECT 
        EXTRACT(YEAR FROM created_at) as tahun,
        COUNT(*) as jumlah
    FROM arsip
    GROUP BY EXTRACT(YEAR FROM created_at)
    ORDER BY tahun DESC
");
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item active">Arsip</li>
            </ol>
        </nav>
        <h1>Arsip Dokumen</h1>
        <p>Koleksi dokumen penelitian dan pengabdian masyarakat Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">
        <!-- Filter Tabs -->
        <div class="text-center mb-5">
            <div class="btn-group shadow-sm" role="group">
                <a href="?page=arsip&kategori=Penelitian" class="btn <?= $filter_kategori == 'Penelitian' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
                    Penelitian
                </a>
                <a href="?page=arsip&kategori=Pengabdian" class="btn <?= $filter_kategori == 'Pengabdian' ? 'btn-primary' : 'btn-outline-secondary' ?> px-5 py-2">
                    Pengabdian
                </a>
            </div>
        </div>

        <!-- Table -->
        <?php if (pg_num_rows($arsip_query) > 0): ?>
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Judul Dokumen</th>
                            <th style="width: 200px;">Penulis</th>
                            <th style="width: 100px;">Tahun</th>
                            <th style="width: 100px;">Ukuran</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1; ?>
                        <?php while($arsip = pg_fetch_assoc($arsip_query)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger me-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong class="d-block"><?= htmlspecialchars($arsip['judul_dokumen']) ?></strong>
                                            <?php if (!empty($arsip['deskripsi'])): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($arsip['deskripsi'], 0, 80)) ?>
                                                    <?= strlen($arsip['deskripsi']) > 80 ? '...' : '' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php
                                        // Simulate author from description or use placeholder
                                        $authors = ['Dr. Ahmad Syahputra, M.Kom', 'Budi Santoso, S.Kom, M.T', 'Siti Rahayu, S.T, M.Sc', 'Prof. Dr. Andi Wijaya'];
                                        echo $authors[array_rand($authors)];
                                        ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?= date('Y', strtotime($arsip['created_at'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <?= number_format($arsip['ukuran_file_mb'], 1) ?> MB
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="download.php?id=<?= $arsip['id_arsip'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $page_num - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $page_num + 1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-pdf text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Belum ada dokumen</h4>
                <p class="text-muted">Dokumen <?= $filter_kategori ?> akan ditampilkan di sini</p>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mt-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Total Dokumen</h5>
                        <h2 class="fw-bold text-primary mb-0"><?= $stats['total'] ?> Dokumen</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Publikasi <?= date('Y') ?></h5>
                        <h2 class="fw-bold text-primary mb-0"><?= $stats['tahun_ini'] ?> Dokumen</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Publikasi <?= date('Y') - 1 ?></h5>
                        <h2 class="fw-bold text-primary mb-0">
                            <?php
                            $last_year = 0;
                            pg_result_seek($year_query, 0);
                            while($year = pg_fetch_assoc($year_query)) {
                                if ($year['tahun'] == date('Y') - 1) {
                                    $last_year = $year['jumlah'];
                                    break;
                                }
                            }
                            echo $last_year;
                            ?> Dokumen
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>