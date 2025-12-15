<?php
// pages/user/arsip.php

// Filter - default ke Penelitian
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'Penelitian';

// Pagination
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Build query based on kategori
$where_clause = "WHERE kategori = $1"; // <-- Diperbarui
$query_params = [$filter_kategori];     // <-- Diperbarui

// Get total records
// Menggunakan pg_query_params karena query_params selalu ada
$count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM arsip $where_clause", $query_params);
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
// Menggunakan pg_query_params karena query_params selalu ada
$arsip_query = pg_query_params($conn, "SELECT * FROM arsip $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $query_params);

// Get statistics
// PERBAIKAN - Get statistics - Total dokumen
$total_query = pg_query($conn, "SELECT COUNT(*) as total FROM arsip");
$total_docs = pg_fetch_assoc($total_query)['total'];

// Get year statistics - Dinamis untuk 3 tahun terakhir berdasarkan field tanggal
$year_stats_query = pg_query($conn, "
    SELECT 
        EXTRACT(YEAR FROM tanggal) as tahun,
        COUNT(*) as jumlah
    FROM arsip
    WHERE tanggal IS NOT NULL
    GROUP BY EXTRACT(YEAR FROM tanggal)
    ORDER BY tahun DESC
    LIMIT 3
");

$year_stats = [];
while ($row = pg_fetch_assoc($year_stats_query)) {
    $year_stats[(int)$row['tahun']] = (int)$row['jumlah'];
}

// Jika tidak ada data, set default 3 tahun terakhir dengan nilai 0
if (empty($year_stats)) {
    $current_year = date('Y');
    $year_stats[$current_year] = 0;
    $year_stats[$current_year - 1] = 0;
    $year_stats[$current_year - 2] = 0;
}

// Ambil 3 tahun terakhir yang ada datanya
$years = array_keys($year_stats);
$year1 = $years[0] ?? date('Y');
$year2 = $years[1] ?? (date('Y') - 1);
$year3 = $years[2] ?? (date('Y') - 2);

$count1 = $year_stats[$year1] ?? 0;
$count2 = $year_stats[$year2] ?? 0;
$count3 = $year_stats[$year3] ?? 0;
?>

<head>
    <link href="assets/css/arsip.css" rel="stylesheet">
</head>
<!-- Page Header -->
<section class="page-header-arsip">
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
<section class="section-arsip">
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
                <th style="width: 50px;" class="text-center">No</th>
                <th style="width: 40%;">Dokumen</th>
                <th style="width: 180px;">Penulis</th>
                <th style="width: 120px;" class="text-center">Tanggal</th>
                <th style="width: 100px;" class="text-center">Ukuran</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
                <?php $no = $offset + 1; ?>
                <?php while($arsip = pg_fetch_assoc($arsip_query)): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-file-pdf text-danger me-3 mt-1" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div style="min-width: 0; flex: 1;">
                                    <strong class="d-block" style="
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        display: -webkit-box;
                                        -webkit-line-clamp: 2;
                                        -webkit-box-orient: vertical;
                                        line-height: 1.4;
                                        word-break: break-word;
                                    "><?= htmlspecialchars($arsip['judul_dokumen']) ?></strong>
                                    <?php if (!empty($arsip['deskripsi'])): ?>
                                        <small class="text-muted" style="
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 1;
                                            -webkit-box-orient: vertical;
                                        ">
                                            <?= htmlspecialchars($arsip['deskripsi']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted" style="
                                overflow: hidden;
                                text-overflow: ellipsis;
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                word-break: break-word;
                            ">
                                <?= nl2br(htmlspecialchars($arsip['penulis'] ?? 'Admin Lab NCS')) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary" style="white-space: nowrap;">
                               <?= !empty($arsip['tanggal']) ? date('d-m-Y', strtotime($arsip['tanggal'])) : date('d-m-Y', strtotime($arsip['created_at'])) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <small class="text-muted" style="white-space: nowrap;">
                                <?= sprintf('%.2f', (float)$arsip['ukuran_file_mb']) ?> MB
                            </small>
                        </td>
                        <td class="text-center">
                        <a href="/NCS/admin/pages_admin/download.php?id=<?= $arsip['id_arsip'] ?>" 
   class="btn btn-sm btn-primary">
   <i class="fas fa-download me-1"></i> Download
</a>
                    </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

          <!-- Pagination -->
            <?php if ($total_pages > 0): ?>
                <div class="d-flex justify-content-center align-items-center mt-4">
                    <nav>
                        <ul class="pagination mb-0">
                            <!-- Previous Button -->
                            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $page_num - 1 ?>" 
                                   style="border-radius: 8px 0 0 8px; border-right: none;">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php
                            // Pagination logic - show max 5 pages
                            $start_page = max(1, $page_num - 2);
                            $end_page = min($total_pages, $page_num + 2);
                            
                            // Adjust if at beginning
                            if ($page_num <= 3) {
                                $end_page = min(5, $total_pages);
                            }
                            
                            // Adjust if at end
                            if ($page_num > $total_pages - 3) {
                                $start_page = max(1, $total_pages - 4);
                            }
                            
                            // First page
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Page Numbers -->
                            <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $i ?>" 
                                       style="<?= $i == $page_num ? 'background-color: #2563eb; border-color: #2563eb; color: white;' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Last page -->
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $total_pages ?>"><?= $total_pages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Next Button -->
                            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=arsip&kategori=<?= $filter_kategori ?>&p=<?= $page_num + 1 ?>" 
                                   style="border-radius: 0 8px 8px 0; border-left: none;">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
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
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted mb-2">Total Dokumen</h5>
                <h2 class="fw-bold text-primary mb-0"><?= $total_docs ?></h2>
                <small class="text-muted">Semua Kategori</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted mb-2">Publikasi <?= $year1 ?></h5>
                <h2 class="fw-bold text-primary mb-0"><?= $count1 ?></h2>
                <small class="text-muted">Dokumen</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted mb-2">Publikasi <?= $year2 ?></h5>
                <h2 class="fw-bold text-primary mb-0"><?= $count2 ?></h2>
                <small class="text-muted">Dokumen</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted mb-2">Publikasi <?= $year3 ?></h5>
                <h2 class="fw-bold text-primary mb-0"><?= $count3 ?></h2>
                <small class="text-muted">Dokumen</small>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>