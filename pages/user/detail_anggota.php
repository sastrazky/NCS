<?php
// pages/detail_anggota.php

// Pastikan koneksi ($conn) dan sesi tersedia
if (!isset($conn)) {
    // Handle error atau redirect
    exit('Koneksi database tidak tersedia.');
}

// 1. Ambil ID dari URL
$id_anggota = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_anggota == 0) {
    // Redirect jika ID tidak valid
    header("Location: ?page=anggota");
    exit();
}

// 2. Query data utama anggota
// Menggunakan parameterisasi untuk keamanan
$query_anggota = pg_query_params($conn, "SELECT * FROM anggota WHERE id_anggota = $1", [$id_anggota]);
$data_anggota = pg_fetch_assoc($query_anggota);

if (!$data_anggota) {
    // Redirect jika anggota tidak ditemukan
    header("Location: ?page=anggota");
    exit();
}

// 3. Query data detail anggota
$query_detail = pg_query_params($conn, "SELECT * FROM detail_anggota WHERE id_anggota = $1", [$id_anggota]);
$data_detail = pg_fetch_assoc($query_detail);

// 4. Pengolahan Data Detail
$links = [];
$keahlian_list = [];
$pendidikan_list = [];
$sertifikasi_list = [];
$matkul_list = [];

if ($data_detail) {
    // a. Decode data JSON Link Publikasi
    if (!empty($data_detail['link_publikasi'])) {
        $decoded = json_decode($data_detail['link_publikasi'], true);
        if (is_array($decoded)) {
            $links = $decoded;
        }
    }

    // b. Memisahkan data multiline berdasarkan baris baru dan membersihkan entri kosong
    $keahlian_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['keahlian']));
    $pendidikan_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['pendidikan']));
    $sertifikasi_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['sertifikasi']));
    $matkul_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['mata_kuliah']));
}

// c. Path Foto
$foto_path = (!empty($data_anggota['foto_path'])) ? "admin/" . $data_anggota['foto_path'] : "assets/images/no-image.jpg";
if (!file_exists($foto_path)) $foto_path = "assets/images/no-image.jpg";

$nidn_display = htmlspecialchars($data_anggota['nidn'] ?? '-');

$nip_display = htmlspecialchars($data_anggota['nip'] ?? '-');
$jabatan_display = htmlspecialchars($data_anggota['jabatan'] ?? '-');
$email_display = htmlspecialchars($data_anggota['email'] ?? '-');
$nama_lengkap_display = htmlspecialchars($data_anggota['nama_lengkap']);
?>

<head>
    <link href="assets/css/detail_anggota.css" rel="stylesheet">
    <link href="assets/css/anggota.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<section class="page-header-anggota">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item"><a href="?page=anggota">Anggota Tim</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $nama_lengkap_display ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="section-detail-anggota py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm h-100 sidebar-card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="<?= htmlspecialchars($foto_path) ?>" alt="<?= $nama_lengkap_display ?>" class="profile-photo-lg mb-3">
                            <h4 class="fw-bold mb-0 text-primary-custom"><?= $nama_lengkap_display ?></h4>
                            <?= $jabatan_display ?>
                        </div>

                        <hr class="my-1">

                        <h6 class="text-uppercase fw-bold text-secondary mb-3 text-center profile-section-title"> Detail Institusi</h6>

                        <div class="info-group">
                            <p class="info-label"> NIP</p>
                            <p class="info-value"><?= $nip_display ?></p>
                        </div>

                        <div class="info-group">
                            <p class="info-label"> NIDN</p>
                            <p class="info-value"><?= $nidn_display ?></p>
                        </div>

            
                        <div class="info-group">
                            <p class="info-label"> Jabatan</p>
                            <p class="info-value"><?= $jabatan_display ?></p>
                        </div>

                        <hr class="my-3">

                        <h6 class="text-uppercase fw-bold text-secondary mb-3 text-center profile-section-title"> Kontak</h6>

                        <div class="info-group">
                            <p class="info-label"> Email</p>
                            <p class="info-value"><a href="mailto:<?= $email_display ?>" class="text-decoration-none fw-medium"><?= $email_display ?></a></p>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7">
                <div class="main-content-area">

                    <?php if (!empty($keahlian_list)): ?>
                        <h4 class="fw-bold mb-4 section-title">Bidang Keahlian Dan Link Publikasi</h4>
                        <div class="mb-2 tag-list d-flex flex-wrap">
                            <?php foreach ($keahlian_list as $keahlian): ?>
                                <span class="badge rounded-pill bg-primary-custom me-2 mb-2 p-2"><?= htmlspecialchars($keahlian) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($links)): ?>
                        <div class="mb-1 link-list d-flex flex-wrap">
                                    <?php foreach ($links as $link):
                                    ?>
                                        <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="btn btn-outline-primary-simple btn-sm me-2 mb-2">
                                            <i class="<?= $icon_class ?> me-1"></i> <?= htmlspecialchars($link['nama']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <hr class="my-4">

                    <h4 class="fw-bold mb-4 section-title">Riwayat Akademik</h4>
                    <div class="row">

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm custom-card-list border-start border-primary border-4">
                                <div class="card-body">
                                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-book me-2"></i> Pendidikan</h5>
                                    <ul class="detail-no-bullet">
                                        <?php if (!empty($pendidikan_list)): ?>
                                            <?php foreach ($pendidikan_list as $pendidikan): ?>
                                                <li><?= nl2br(htmlspecialchars(trim($pendidikan))) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><span class="text-muted fst-italic">- Data belum tersedia -</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm custom-card-list border-start border-success border-4">
                                <div class="card-body">
                                    <h5 class="fw-bold text-success mb-3"><i class="fas fa-certificate me-2"></i> Sertifikasi</h5>
                                    <ul class="detail-no-bullet">
                                        <?php if (!empty($sertifikasi_list)): ?>
                                            <?php foreach ($sertifikasi_list as $sertifikasi): ?>
                                                <li><?= nl2br(htmlspecialchars(trim($sertifikasi))) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><span class="text-muted fst-italic">- Data belum tersedia -</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-3 mt-4 section-title">Mata Kuliah yang Diampu</h4>
                    <ul class="detail-no-bullet list-group list-group-flush border rounded p-3">
                        <?php if (!empty($matkul_list)): ?>
                            <?php foreach ($matkul_list as $matkul): ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="far fa-dot-circle text-info me-3"></i>
                                    <span><?= nl2br(htmlspecialchars(trim($matkul))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item"><span class="text-muted fst-italic">- Data belum tersedia -</span></li>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>