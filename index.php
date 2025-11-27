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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/user-style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="top-bar-left">
                    <a href="#" class="text-white text-decoration-none me-3">
                        <i class="fas fa-envelope me-1"></i> ncs@polinema.ac.id
                    </a>
                    <a href="#" class="text-white text-decoration-none">
                        <i class="fas fa-phone me-1"></i> (0341) 404424
                    </a>
                </div>
                <div class="top-bar-right">
                    <a href="admin/" class="text-white text-decoration-none">
                        <i class="fas fa-lock me-1"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <?php
            // Get logo from database
            $logo_query = pg_query($conn, "SELECT logo_path FROM profil LIMIT 1");
            $logo_data = pg_fetch_assoc($logo_query);
            $logo_path = !empty($logo_data['logo_path']) ? $logo_data['logo_path'] : 'assets/images/logo-ncs.png';
            ?>
            <a class="navbar-brand" href="?page=beranda">
                <?php if (file_exists($logo_path)): ?>
                    <img src="<?= htmlspecialchars($logo_path) ?>" alt="NCS Logo" height="50" class="d-inline-block align-text-top me-2">
                <?php else: ?>
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--primary-color);"></i>
                <?php endif; ?>
                <span class="brand-text">
                    <strong>NCS</strong>
                    <small class="d-block text-muted" style="font-size: 0.7rem;">Network and Cyber Security</small>
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
                        <a class="nav-link dropdown-toggle <?= in_array($page, ['sejarah', 'visi-misi', 'anggota']) ? 'active' : '' ?>" 
                           href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Profil
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=sejarah">Sejarah</a></li>
                            <li><a class="dropdown-item" href="?page=visi-misi">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="?page=anggota">Anggota Tim</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'produk' ? 'active' : '' ?>" href="?page=produk">Produk & Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'sarana' ? 'active' : '' ?>" href="?page=sarana">Fasilitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'galeri' ? 'active' : '' ?>" href="?page=galeri">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'arsip' ? 'active' : '' ?>" href="?page=arsip">Arsip</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php
        switch($page) {
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
            case 'produk':
                include 'pages/user/produk.php';
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
            default:
                include 'pages/user/beranda.php';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Lab Network and Cyber Security</h5>
                    <p class="text-light">Teknologi Informasi Polinema</p>
                    <div class="social-links mt-3">
                        <?php
                        // Get social media links
                        $social_query = pg_query($conn, "SELECT * FROM link_eksternal WHERE kategori = 'Social Media' ORDER BY urutan ASC LIMIT 5");
                        while($social = pg_fetch_assoc($social_query)):
                        ?>
                            <a href="<?= htmlspecialchars($social['uri']) ?>" target="_blank" class="text-white me-3">
                                <i class="fab fa-<?= strtolower(str_replace(' ', '-', $social['nama_link'])) ?>"></i>
                            </a>
                        <?php endwhile; ?>
                    </div>
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
                    <h5 class="text-white mb-3">Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="?page=beranda" class="text-light text-decoration-none">Beranda</a></li>
                        <li><a href="?page=sejarah" class="text-light text-decoration-none">Profil</a></li>
                        <li><a href="?page=galeri" class="text-light text-decoration-none">Galeri</a></li>
                        <li><a href="?page=arsip" class="text-light text-decoration-none">Arsip</a></li>
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