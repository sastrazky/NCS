<?php
// index.php (User Frontend)
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Network and Cyber Security - Teknologi Informasi Polinema</title>
    <link rel="icon" type="image/jpeg" href="assets/image/LOGOC.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/user-style.css" rel="stylesheet">
    <link href="assets/css/footer.css" rel="stylesheet">
    <link href="assets/css/galeri.css" rel="stylesheet">
    <link href="assets/css/agenda.css" rel="stylesheet">
</head>

<body>
    <div class="top-header-bar ">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-links">
                    <?php
                    // Get external links
                    $link_query = pg_query($conn, "
                        SELECT * FROM link_eksternal
                        ORDER BY urutan ASC
                    ");

                    $links = [];
                    while ($link = pg_fetch_assoc($link_query)) {
                        $links[] = $link;
                    }

                    // Display links with separators
                    foreach ($links as $index => $link):
                    ?>
                        <a href="<?= htmlspecialchars($link['uri']) ?>" target="_blank">
                            <?= htmlspecialchars($link['nama_link']) ?>
                        </a>
                        <?php if ($index < count($links) - 1): ?>
                            <span class="separator">|</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <?php
            // Get profil data to use logo path
            $profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
            $profil_data = pg_fetch_assoc($profil_query);
            $logo_path = !empty($profil_data['logo_path']) ? "admin/" . $profil_data['logo_path'] : 'assets/images/logo-ncs.png';

            // Check if file exists, if not use default
            if (!file_exists($logo_path)) {
                $logo_path = 'assets/images/logo-ncs.png';
            }
            ?>
            <a class="navbar-brand" href="?page=beranda">
                <?php if (file_exists($logo_path)): ?>
                    <img src="<?= htmlspecialchars($logo_path) ?>"
                        alt="NCS Logo"
                        height="50"
                        class="d-inline-block align-text-top me-2"
                        onerror="this.style.display='none';">
                <?php else: ?>
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--primary-color);"></i>
                <?php endif; ?>
                <span class="brand-text">
                    <strong>NCS</strong>
                    <small class="d-block text-muted" style="font-size: 1rem;">Network and Cyber Security</small>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'beranda' ? 'active' : '' ?>" href="?page=beranda">Beranda</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($page, ['sejarah', 'visi-misi', 'anggota', 'detail_anggota']) ? 'active' : '' ?>"
                            href="#" id="navbarDropdownProfil" role="button" data-bs-toggle="dropdown">
                            Profil
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=sejarah">Sejarah</a></li>
                            <li><a class="dropdown-item" href="?page=visi-misi">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="?page=anggota">Anggota Tim</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'galeri' ? 'active' : '' ?>" href="?page=galeri">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'arsip' ? 'active' : '' ?>" href="?page=arsip">Arsip</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($page, ['sarana', 'layanan']) ? 'active' : '' ?>"
                            href="#" id="navbarDropdownLayanan" role="button" data-bs-toggle="dropdown">
                            Layanan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=sarana">Sarana & Prasarana</a></li>
                            <li><a class="dropdown-item" href="?page=layanan">Layanan Konsultatif</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?php
        switch ($page) {
            case 'beranda':
                include 'pages/user/beranda.php';
                break;
            case 'sejarah':
                include 'pages/user/sejarah.php';
                break;
            case 'visi-misi':
                include 'pages/user/visi-misi.php';
                break;
            case 'anggota':
                include 'pages/user/anggota.php';
                break;
            case 'detail_anggota':
                include 'pages/user/detail_anggota.php';
                break;
            case 'sarana':
                include 'pages/user/sarana.php';
                break;
            case 'galeri':
                include 'pages/user/galeri.php';
                break;
            case 'arsip':
                include 'pages/user/arsip.php';
                break;
            case 'layanan':
                include 'pages/user/layanan.php';
                break;
            default:
                include 'pages/user/beranda.php';
        }
        ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-white mb-1">Lab Network and Cyber Security</h5>
                    <p class="text-light mb-3" style="font-size: 0.9rem;">Teknologi Informasi Polinema</p>

                    <div class="footer-logo"></div>
                </div>

                <div class="col-md-4">
                    <h5 class="text-white mb-3">Kontak</h5>
                    <p class="text-light mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Jl. Soekarno Hatta No.9, Malang
                    </p>
                    <p class="text-light mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        ncs@polinema.ac.id
                    </p>
                    <p class="text-light mb-2">
                        <i class="fas fa-phone me-2"></i>
                        (0341) 404424
                    </p>
                </div>

                <div class="col-md-4">
                    <h5 class="text-white mb-3">Nama Anggota Kelompok</h5>
                    <ul class="list-unstyled">
                        <li>Akhmad Ghozali - 244107060112</li>
                        <li>Atha Maulidia - 244107060080</li>
                        <li>M Wildan Wibisono - 244107060118</li>
                        <li>Mutiara Inayah M. - 244107060041</li>
                        <li>Sastra Maheva Zaky - 244107060116</li>
                    </ul>
                </div>
            </div>

            <hr class="border-light my-4">

            <div class="text-center text-light">
                <p class="mb-0">© 2025 Lab Network and Cyber Security - Polinema. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>