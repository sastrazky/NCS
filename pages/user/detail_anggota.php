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
$query_anggota = pg_query_params($conn, "SELECT * FROM anggota WHERE id_anggota = $1", [$id_anggota]);
$data_anggota = pg_fetch_assoc($query_anggota);

// 3. Query data detail anggota
$query_detail = pg_query_params($conn, "SELECT * FROM detail_anggota WHERE id_anggota = $1", [$id_anggota]);
$data_detail = pg_fetch_assoc($query_detail);

if (!$data_anggota) {
    // Redirect jika anggota tidak ditemukan
    header("Location: ?page=anggota");
    exit();
}

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

    // b. Memisahkan data multiline berdasarkan baris baru
    $keahlian_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['keahlian']));
    $pendidikan_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['pendidikan']));
    $sertifikasi_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['sertifikasi']));
    $matkul_list = array_filter(preg_split('/\r\n|\r|\n/', $data_detail['mata_kuliah']));
}

// c. Path Foto
$foto_path = (!empty($data_anggota['foto_path'])) ? "admin/" . $data_anggota['foto_path'] : "assets/images/no-image.jpg";
if (!file_exists($foto_path)) $foto_path = "assets/images/no-image.jpg";

$gelar_display = $data_anggota['gelar'] ?? ''; 
// Mengambil NIDN dari database
$nidn_display = $data_anggota['nidn'] ?? '-';
$program_studi_display = 'Teknik Informatika'; // Placeholder/Contoh

?>

<head>
    <link href="assets/css/detail_anggota.css" rel="stylesheet">
    <link href="assets/css/anggota.css" rel="stylesheet">
</head>
<section class="page-header-anggota">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item"><a href="?page=anggota">Anggota Tim</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($data_anggota['nama_lengkap']) ?></li>
            </ol>
        </nav>
        </div>
</section>

<section class="section-detail-anggota">
    <div class="container">
        <div class="row">

            <div class="col-md-3">
                <div class="sidebar-info">
                    <img src="<?= htmlspecialchars($foto_path) ?>" alt="<?= htmlspecialchars($data_anggota['nama_lengkap']) ?>" class="profile-photo mb-3">
                    
                    <h5 class="fw-bold"><?= htmlspecialchars($data_anggota['nama_lengkap']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($data_anggota['jabatan']) ?></p>

                    <hr>

                    <h6>NIP</h6>
                    <p><?= htmlspecialchars($data_anggota['nip'] ?? '-') ?></p>

                    <h6>NIDN</h6>
                    <p><?= htmlspecialchars($nidn_display) ?></p> 
                    
                    <h6>Program Studi</h6>
                    <p><?= htmlspecialchars($program_studi_display) ?></p>

                    <h6>Jabatan</h6>
                    <p><?= htmlspecialchars($data_anggota['jabatan'] ?? '-') ?></p>

                    <hr>

                    <h6>Kontak</h6>
                    <p>Email: <?= htmlspecialchars($data_anggota['email'] ?? '-') ?></p>
                </div>
            </div>

            <div class="col-md-9">
                <div class="main-content">
                    
                    <div class="profile-header">
                        <hr><h2 class="mb-2 profile-title"><?= htmlspecialchars($data_anggota['nama_lengkap']) . ' ' . htmlspecialchars($gelar_display) ?></h2>
                    </div>

                    <div class="mb-4 tag-list">
                        <?php if (!empty($keahlian_list)): ?>
                            <?php foreach ($keahlian_list as $keahlian): ?>
                                <span class="badge bg-primary-custom me-2 mb-2"><?= htmlspecialchars($keahlian) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-5 link-list">
                        <?php if (!empty($links)): ?>
                            <?php foreach ($links as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                    <i class="fas fa-link me-1"></i> <?= htmlspecialchars($link['nama']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <h4 class="fw-bold mb-4">Pendidikan & Sertifikasi</h4>
                    <div class="row">
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="fw-bold text-primary mb-3">Pendidikan</h5>
                                    <ul class="detail-no-bullet">
                                        <?php if (!empty($pendidikan_list)): ?>
                                            <?php foreach ($pendidikan_list as $pendidikan): ?>
                                                <li><?= nl2br(htmlspecialchars($pendidikan)) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><span class="text-muted">- Data belum tersedia -</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="fw-bold text-success mb-3">Sertifikasi</h5>
                                    <ul class="detail-no-bullet">
                                        <?php if (!empty($sertifikasi_list)): ?>
                                            <?php foreach ($sertifikasi_list as $sertifikasi): ?>
                                                <li><?= nl2br(htmlspecialchars($sertifikasi)) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><span class="text-muted">- Data belum tersedia -</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold mb-3 mt-3">Mata Kuliah yang Diampu</h4>
                    <ul class="detail-no-bullet">
                        <?php if (!empty($matkul_list)): ?>
                            <?php foreach ($matkul_list as $matkul): ?>
                                <li><?= nl2br(htmlspecialchars($matkul)) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span class="text-muted">- Data belum tersedia -</span></li>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>
