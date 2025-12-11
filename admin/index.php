<!-- index.php -->
<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$admin_name = 'Admin'; // Default jika gagal
$initial = 'A'; // Default initial

$id_admin = (int)$_SESSION['id_admin'];

if ($id_admin > 0 && isset($conn)) {
    $query = pg_query_params($conn, "SELECT nama_lengkap FROM admin WHERE id_admin = $1", [$id_admin]);
    if ($row = pg_fetch_assoc($query)) {
        $admin_name = htmlspecialchars($row['nama_lengkap']);
        
        $name_parts = explode(' ', $row['nama_lengkap']);
        $first_name = $name_parts[0];
        $initial = strtoupper(substr($first_name, 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCS Admin Panel - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text">
                    <h5>NCS</h5>
                    <small>Admin Panel</small>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="?page=profil" class="<?= $page == 'profil' ? 'active' : '' ?>">
                <i class="fas fa-building"></i>
                <span>Profil</span>
            </a>
            <a href="?page=anggota" class="<?= $page == 'anggota' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Anggota</span>
            </a>
            <a href="?page=agenda" class="<?= $page == 'agenda' ? 'active' : '' ?>">
                <i class="fas fa-calendar"></i>
                <span>Agenda</span>
            </a>
            <a href="?page=galeri" class="<?= $page == 'galeri' ? 'active' : '' ?>">
                <i class="fas fa-images"></i>
                <span>Galeri</span>
            </a>
            <a href="?page=arsip" class="<?= $page == 'arsip' ? 'active' : '' ?>">
                <i class="fas fa-file-pdf"></i>
                <span>Arsip PDF</span>
            </a>
            <a href="?page=sarana_prasarana" class="<?= $page == 'sarana_prasarana' ? 'active' : '' ?>">
                <i class="fas fa-warehouse"></i>
                <span>Sarana Prasarana</span>
            </a>
            <a href="?page=layanan" class="<?= $page == 'layanan' ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Layanan</span>
            </a>
            <a href="?page=link_eksternal" class="<?= $page == 'link_eksternal' ? 'active' : '' ?>">
                <i class="fas fa-link"></i>
                <span>Link Eksternal</span>
            </a>
        </div>
        
        <div class="position-absolute bottom-0 w-100 p-3"">
        
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="navbar-title">
                    <h4>Dashboard Admin</h4>
                    <small>Network and Cyber Security Management</small>
                </div>
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" style="cursor:pointer;">
                        <?= $initial ?>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small">Logged in as <?= $admin_name ?></span></li>
                        <li><a class="dropdown-item" href="?page=change_password"><i class="fas fa-key me-2"></i>Change Password</a></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php
            // Include page based on parameter
            switch($page) {
                case 'dashboard':
                    include 'pages_admin/dashboard.php';
                    break;
                case 'profil':
                    include 'pages_admin/profil.php';
                    break;
                case 'anggota':
                    include 'pages_admin/anggota.php';
                    break;
                case 'layanan':
                    include 'pages_admin/layanan.php';
                    break;
                case 'sarana_prasarana':
                    include 'pages_admin/sarana_prasarana.php';
                    break;
                case 'agenda':
                    include 'pages_admin/agenda.php';
                    break;
                case 'galeri':
                    include 'pages_admin/galeri.php';
                    break;
                case 'arsip':
                    include 'pages_admin/arsip.php';
                    break;
                case 'link_eksternal':
                    include 'pages_admin/link_eksternal.php';
                    break;
                case 'change_password':
                    include 'change_password.php';
                    break;
                default:
                    include 'pages_admin/dashboard.php';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>