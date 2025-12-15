<?php
// pages/user/sarana.php

// Pagination
$limit = 6;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Get total records
$count_query = pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana");
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
$sarana_query = pg_query(
    $conn,
    "SELECT * FROM sarana_prasarana ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset"
);

// Get statistics
$stats_query = pg_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(jumlah) as total_item
    FROM sarana_prasarana
");
$stats = pg_fetch_assoc($stats_query);
?>

<head>
    <link href="assets/css/sarana.css" rel="stylesheet">
</head>
<section class="page-header-sarana">
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

<section class="section-sarana">
    <div class="container">
        <?php if (pg_num_rows($sarana_query) > 0): ?>
            <div class="row g-4">
                <?php while ($sarana = pg_fetch_assoc($sarana_query)): ?>

                    <?php
                    // START media logic
                    $original_media = !empty($sarana['gambar_path']) ? $sarana['gambar_path'] : '';
                    $media_path = $original_media ? "admin/" . $original_media : "assets/images/no-image.jpg";
                    if (!file_exists($media_path)) {
                        $media_path = "assets/images/no-image.jpg";
                    }
                    // END media logic
                    ?>

                    <div class="col-md-4">
                        <div class="card-custom facility-card"
                            data-bs-toggle="modal"
                            data-bs-target="#detailModal<?= $sarana['id_sarana'] ?>"
                            style="cursor: pointer;">

                            <div class="facility-image">
                                <?php if (!empty($original_media) && $media_path !== "assets/images/no-image.jpg"): ?>
                                    <img src="<?= htmlspecialchars($media_path) ?>"
                                        alt="<?= htmlspecialchars($sarana['nama_fasilitas']) ?>"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-warehouse text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75">
                                        <i class="fas fa-cubes me-1"></i>
                                        <?= number_format($sarana['jumlah'] ?? 0) ?> Unit
                                    </span>
                                </div>
                            </div>

                            <div class="card-body-sarana">
                                <h5 class="card-title-sarana">
                                    <?= htmlspecialchars($sarana['nama_fasilitas']) ?>
                                </h5>

                                <?php if (!empty($sarana['deskripsi'])): ?>
                                    <p class="desc-sarana">
                                        <?= htmlspecialchars($sarana['deskripsi']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                    <div class="modal fade" id="detailModal<?= $sarana['id_sarana'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-md modal-dialog-centered"> <div class="modal-content">

                                <div class="modal-header py-2">
                                    <h5 class="modal-title fw-bold">
                                        <?= htmlspecialchars($sarana['nama_fasilitas']) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body p-3">

                                    <div class="detail-image-wrapper mb-3">
                                        <?php if (!empty($original_media) && $media_path !== "assets/images/no-image.jpg"): ?>
                                            <img src="<?= htmlspecialchars($media_path) ?>"
                                                class="detail-img shadow-sm rounded">
                                        <?php else: ?>
                                            <div class="detail-placeholder shadow-sm rounded">
                                                <i class="fas fa-warehouse text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <p class="mb-1"><strong>Jumlah Unit:</strong> <?= $sarana['jumlah'] ?> Unit</p>

                                        <p class="fw-bold mb-1 mt-2">Deskripsi:</p>
                                        <p class="detail-desc mb-0">
                                            <?= nl2br(htmlspecialchars($sarana['deskripsi'] ?: '- Tidak ada deskripsi -')) ?>
                                        </p>
                                    </div>

                                </div>

                                <div class="modal-footer py-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>

                            </div>
                        </div>
                    </div>


                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=sarana&p=<?= $page_num - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=sarana&p=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=sarana&p=<?= $page_num + 1 ?>">
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
                <p class="text-muted">Data fasilitas akan ditampilkan di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-sarana bg-light py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h5 class="text-muted mb-2">Total Fasilitas</h5>
                        <h3 class="fw-bold text-primary mb-0">
                            <?= number_format($stats['total']) ?> Fasilitas
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>