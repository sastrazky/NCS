<?php
// pages/galeri.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// =========================================
// LOAD DATA EDIT
// =========================================
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_galeri = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM galeri WHERE id_galeri = $1", [$id_galeri]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// =========================================
// DELETE GALERI
// =========================================
if (isset($_GET['delete'])) {
    $id_galeri = (int)$_GET['delete'];

    $file_query = pg_query_params($conn, "SELECT media_path FROM galeri WHERE id_galeri = $1", [$id_galeri]);

    if ($file_row = pg_fetch_assoc($file_query)) {
        if (!empty($file_row['media_path']) && file_exists($file_row['media_path'])) {
            unlink($file_row['media_path']);
        }

        $delete_result = pg_query_params($conn, "DELETE FROM galeri WHERE id_galeri = $1", [$id_galeri]);

        if ($delete_result) {
            $success_msg = "Galeri berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus galeri!";
        }
    }

    header("Location: ?page=galeri&success=" . urlencode($success_msg));
    exit();
}

// =========================================
// HANDLE ADD / EDIT
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_galeri = isset($_POST['id_galeri']) ? (int)$_POST['id_galeri'] : 0;
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tipe_media = $_POST['tipe_media'];
    $tanggal_kegiatan = !empty($_POST['tanggal_kegiatan']) ? $_POST['tanggal_kegiatan'] : null;
    $id_admin = $_SESSION['id_admin'];

    if (empty($judul)) {
        $error_msg = "Judul harus diisi!";
    } else {
        $upload_dir = 'uploads/galeri/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $media_path = '';

        // Upload File
        if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'wmv'];
            $file = $_FILES['media'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Format file tidak didukung!";
            } else if ($file['size'] > 50 * 1024 * 1024) {
                $error_msg = "Ukuran file maksimal 50MB!";
            } else {
                $tipe_media = in_array($file_ext, ['jpg','jpeg','png','gif']) ? 'Foto' : 'Video';
                $new_name = "galeri_" . time() . "_" . uniqid() . "." . $file_ext;
                $media_path = $upload_dir . $new_name;

                if (!move_uploaded_file($file['tmp_name'], $media_path)) {
                    $error_msg = "Gagal upload file!";
                    $media_path = '';
                }
            }
        }

        if (empty($error_msg)) {
            // ============================
            // UPDATE
            // ============================
            if ($id_galeri > 0) {

                if (!empty($media_path)) {
                    $old_file_query = pg_query_params($conn, "SELECT media_path FROM galeri WHERE id_galeri = $1", [$id_galeri]);
                    if ($old = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old['media_path']) && file_exists($old['media_path'])) {
                            unlink($old['media_path']);
                        }
                    }

                    $update = pg_query_params($conn,
                        "UPDATE galeri SET judul=$1, deskripsi=$2, media_path=$3, tipe_media=$4, tanggal_kegiatan=$5 WHERE id_galeri=$6",
                        [$judul, $deskripsi, $media_path, $tipe_media, $tanggal_kegiatan, $id_galeri]
                    );
                } else {
                    $update = pg_query_params($conn,
                        "UPDATE galeri SET judul=$1, deskripsi=$2, tipe_media=$3, tanggal_kegiatan=$4 WHERE id_galeri=$5",
                        [$judul, $deskripsi, $tipe_media, $tanggal_kegiatan, $id_galeri]
                    );
                }

                if ($update) {
                    header("Location: ?page=galeri&success=" . urlencode("Galeri berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui galeri!";
                }

            } else {
                // ============================
                // INSERT BARU
                // ============================
                if (empty($media_path)) {
                    $error_msg = "File media wajib diupload!";
                } else {
                    $insert = pg_query_params($conn,
                        "INSERT INTO galeri (judul, deskripsi, media_path, tipe_media, tanggal_kegiatan, id_admin, created_at)
                         VALUES ($1,$2,$3,$4,$5,$6,NOW())",
                        [$judul, $deskripsi, $media_path, $tipe_media, $tanggal_kegiatan, $id_admin]
                    );

                    if ($insert) {
                        header("Location: ?page=galeri&success=" . urlencode("Galeri berhasil ditambahkan!"));
                        exit();
                    } else {
                        $error_msg = "Gagal menambahkan galeri!";
                    }
                }
            }
        }
    }
}

// =========================================
// MESSAGE
// =========================================
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

// =========================================
// PAGINATION + SEARCH + FILTER (OPTIMAL)
// =========================================
$limit = 3;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;

$offset = ($page_num - 1) * $limit;

$where = [];
$params = [];
$idx = 1;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $where[] = "(judul ILIKE $" . $idx . " OR deskripsi ILIKE $" . $idx . ")";
    $params[] = "%" . $search . "%";
    $idx++;
}

$filter_tipe = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';
if ($filter_tipe !== '') {
    $where[] = "tipe_media = $" . $idx;
    $params[] = $filter_tipe;
    $idx++;
}

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_sql = "SELECT COUNT(*) FROM galeri $where_sql";
$count_res = pg_query_params($conn, $count_sql, $params);
$total_records = pg_fetch_result($count_res, 0, 0);
$total_pages = max(1, ceil($total_records / $limit));

$data_sql = "
    SELECT * FROM galeri
    $where_sql
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
";
$galeri_result = pg_query_params($conn, $data_sql, $params);
?>

<!-- NOTIF -->
<?php if (!empty($success_msg)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_msg) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_msg) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Galeri</h4>
        <small class="text-muted">Kelola foto dan video kegiatan NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalGaleri">
        <i class="fas fa-plus me-2"></i>Tambah Media
    </button>
</div>

<!-- SEARCH -->
<div class="card mb-4" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET">
            <input type="hidden" name="page" value="galeri">
            <div class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari galeri..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <select name="tipe" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="Foto" <?= $filter_tipe=='Foto'?'selected':'' ?>>Foto</option>
                            <option value="Video" <?= $filter_tipe=='Video'?'selected':'' ?>>Video</option>
                        </select>
                        <button class="btn btn-primary-custom"><i class="fas fa-search"></i></button>
                        <?php if ($search || $filter_tipe): ?>
                            <a href="?page=galeri" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- GALERI GRID -->
<div class="row g-4 mb-4">
<?php if (pg_num_rows($galeri_result) > 0): ?>
<?php while($row = pg_fetch_assoc($galeri_result)): ?>
    <div class="col-md-3">
        <div class="card h-100 galeri-card">
            <div class="position-relative">

                <?php if ($row['tipe_media']=='Foto' && file_exists($row['media_path'])): ?>
                    <img src="<?= $row['media_path'] ?>" class="card-img-top galeri-img"
                         style="height:200px;object-fit:cover;cursor:pointer"
                         onclick="viewMedia('<?= $row['media_path'] ?>','foto','<?= addslashes($row['judul']) ?>')">

                <?php elseif ($row['tipe_media']=='Video' && file_exists($row['media_path'])): ?>
                    <div style="height:200px;background:#000;cursor:pointer" onclick="viewMedia('<?= $row['media_path'] ?>','video','<?= addslashes($row['judul']) ?>')">
                        <video style="width:100%;height:100%;object-fit:cover;pointer-events:none;">
                            <source src="<?= $row['media_path'] ?>" type="video/mp4">
                        </video>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="fas fa-play-circle text-white" style="font-size:3rem;opacity:.8"></i>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center"
                         style="height:200px">
                        <i class="fas fa-image text-muted" style="font-size:3rem"></i>
                    </div>
                <?php endif; ?>

                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge <?= $row['tipe_media']=='Foto'?'bg-primary':'bg-danger' ?>">
                        <i class="fas fa-<?= $row['tipe_media']=='Foto'?'camera':'video' ?>"></i>
                        <?= $row['tipe_media'] ?>
                    </span>
                </div>
            </div>

            <div class="card-body">
                <h6 class="fw-bold"><?= htmlspecialchars($row['judul']) ?></h6>
                <?php if ($row['deskripsi']): ?>
                    <p class="text-muted" style="font-size:.85rem;">
                        <?= htmlspecialchars(substr($row['deskripsi'],0,80)) ?><?= strlen($row['deskripsi'])>80?'...':'' ?>
                    </p>
                <?php endif; ?>

                <?php if ($row['tanggal_kegiatan']): ?>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        <?= date("d M Y", strtotime($row['tanggal_kegiatan'])) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-between">
                <a href="?page=galeri&edit=<?= $row['id_galeri'] ?>" class="btn btn-sm btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button onclick="if(confirm('Yakin hapus?')) window.location.href='?page=galeri&delete=<?= $row['id_galeri'] ?>'"
                        class="btn btn-sm btn-delete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php else: ?>
    <div class="col-12 text-center py-5">
        <i class="fas fa-images" style="font-size:4rem;color:#bbb"></i>
        <p class="mt-3 text-muted">Tidak ada data galeri</p>
    </div>
<?php endif; ?>
</div>

<!-- PAGINATION OPTIMIZED -->
<?php if ($total_pages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        
        <!-- Tombol Previous -->
        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=galeri&p=<?= max(1, $page_num - 1) ?>&search=<?= urlencode($search) ?>&tipe=<?= urlencode($filter_tipe) ?>">
                &laquo;
            </a>
        </li>

        <?php
        // LOGIKA PAGINATION DENGAN ELLIPSIS
        $range = 2; // Jumlah halaman di kiri/kanan halaman aktif
        $start = max(1, $page_num - $range);
        $end = min($total_pages, $page_num + $range);
        
        // Tampilkan halaman pertama
        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=galeri&p=1&search=' . urlencode($search) . '&tipe=' . urlencode($filter_tipe) . '">1</a></li>';
            if ($start > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Tampilkan range halaman
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page_num) ? 'active' : '';
            echo '<li class="page-item ' . $active . '">
                    <a class="page-link" href="?page=galeri&p=' . $i . '&search=' . urlencode($search) . '&tipe=' . urlencode($filter_tipe) . '">' . $i . '</a>
                  </li>';
        }
        
        // Tampilkan halaman terakhir
        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?page=galeri&p=' . $total_pages . '&search=' . urlencode($search) . '&tipe=' . urlencode($filter_tipe) . '">' . $total_pages . '</a></li>';
        }
        ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=galeri&p=<?= min($total_pages, $page_num + 1) ?>&search=<?= urlencode($search) ?>&tipe=<?= urlencode($filter_tipe) ?>">
                &raquo;
            </a>
        </li>

    </ul>
    
    <!-- Info Pagination -->
    <div class="text-center text-muted mt-2">
        <small>
            Halaman <?= $page_num ?> dari <?= $total_pages ?> 
            (Total: <?= $total_records ?> item)
        </small>
    </div>
</nav>
<?php endif; ?>


<!-- Modal + JS Preview + CSS -->
<!-- (Sama seperti codinganmu sebelumnya — tidak aku cut biar tetap jalan) -->
