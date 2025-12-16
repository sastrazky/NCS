<?php
// Tentukan parameter pagination di awal
$limit = 12;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
// Pastikan offset tidak negatif
$offset = max(0, ($page_num - 1) * $limit);

// 1. Ambil Total Record (Diperlukan untuk Link Pagination)
$count_query = pg_query($conn, "SELECT COUNT(*) as total FROM anggota");
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// 2. Ambil Data Anggota menggunakan LIMIT dan OFFSET (Query yang Benar)
$anggota_query_base = "
    SELECT * FROM anggota 
    ORDER BY urutan ASC, nama_lengkap ASC 
    LIMIT $1 
    OFFSET $2
";

$query = pg_query_params($conn, $anggota_query_base, [$limit, $offset]);

?>

<head>
    <link href="assets/css/anggota.css" rel="stylesheet">
</head>

<section class="page-header-anggota">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Anggota Tim</li>
            </ol>
        </nav>
        <h1 class="header-title">Anggota Tim</h1>
        <p class="header-subtitle">Para Anggota TIM Lab Network and Cyber Security</p>
    </div>
</section>

<section class="section-anggota">
    <div class="container">
        <div class="row justify-content-center">

            <?php if (pg_num_rows($query) > 0): ?>
                <?php while ($row = pg_fetch_assoc($query)) : ?>

                    <?php
                    $id_anggota = $row['id_anggota'];
                    $foto = $row['foto_path'];
                    // Penanganan path foto yang lebih bersih
                    $file_path = (!empty($foto) && file_exists("admin/" . $foto)) ? "admin/" . $foto : "assets/images/no-image.jpg";
                    // Jika masih tidak ada, pastikan fallback-nya
                    if (!file_exists($file_path) && $file_path != "assets/images/no-image.jpg") $file_path = "assets/images/no-image.jpg";
                    ?>

                    <div class="col-lg-4 col-md-6 col-sm-12 mb-5">
                        <a href="?page=detail_anggota&id=<?= htmlspecialchars($id_anggota) ?>" class="member-card-link">
                            <div class="member-card">

                                <div class="image-wrapper">
                                    <img src="<?= htmlspecialchars($file_path) ?>"
                                        alt="Foto Anggota <?= htmlspecialchars($row['nama_lengkap']) ?>"
                                        class="member-photo">
                                </div>

                                <div class="card-body text-center">
                                    <h5 class="member-name"><?= htmlspecialchars($row['nama_lengkap']) ?></h5>
                                    <p class="member-position"><?= htmlspecialchars($row['jabatan']) ?></p>

                                    <div class="member-details mt-1">
                                        <?php if (!empty($row['nip'])): ?>
                                            <small class="detail-item">
                                                <strong>NIP:</strong> <?= htmlspecialchars($row['nip']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($row['nidn'])): ?>
                                            <small class="detail-item">
                                                <strong>NIDN:</strong> <?= htmlspecialchars($row['nidn']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Tidak ada data anggota yang ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=anggota&p=<?= max(1, $page_num - 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                            <a class="page-link" href="?page=anggota&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=anggota&p=<?= min($total_pages, $page_num + 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>