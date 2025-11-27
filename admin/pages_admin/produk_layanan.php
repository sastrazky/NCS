<?php
// pages/produk_layanan.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_produk = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_produk = (int)$_GET['delete'];
    
    // Get file path before deleting
    $file_query = pg_query_params($conn, "SELECT gambar_path FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
    
    if ($file_row = pg_fetch_assoc($file_query)) {
        // Delete file if exists
        if (!empty($file_row['gambar_path']) && file_exists($file_row['gambar_path'])) {
            unlink($file_row['gambar_path']);
        }
        
        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
        
        if ($delete_result) {
            $success_msg = "Produk/Layanan berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus produk/layanan!";
        }
    }
    
    header("Location: ?page=produk_layanan&success=" . urlencode($success_msg));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = isset($_POST['id_produk_layanan']) ? (int)$_POST['id_produk_layanan'] : 0;
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = $_POST['kategori'];
    $id_admin = $_SESSION['id_admin'];
    
    // Validation
    if (empty($judul)) {
        $error_msg = "Judul harus diisi!";
    } else {
        $upload_dir = 'uploads/produk/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $gambar_path = '';
        
        // Handle file upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan!";
            } else if ($file_size > 5 * 1024 * 1024) { // 5MB max
                $error_msg = "Ukuran file maksimal 5MB!";
            } else {
                $new_file_name = 'produk_' . time() . '_' . uniqid() . '.' . $file_ext;
                $gambar_path = $upload_dir . $new_file_name;
                
                if (!move_uploaded_file($file_tmp, $gambar_path)) {
                    $error_msg = "Gagal mengupload gambar!";
                    $gambar_path = '';
                }
            }
        }
        
        if (empty($error_msg)) {
            if ($id_produk > 0) {
                // Update
                if (!empty($gambar_path)) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT gambar_path FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['gambar_path']) && file_exists($old_file_row['gambar_path'])) {
                            unlink($old_file_row['gambar_path']);
                        }
                    }
                    
                    $update_result = pg_query_params($conn, 
                        "UPDATE produk_layanan SET judul = $1, deskripsi = $2, kategori = $3, gambar_path = $4, updated_at = NOW() WHERE id_produk_layanan = $5",
                        [$judul, $deskripsi, $kategori, $gambar_path, $id_produk]
                    );
                } else {
                    $update_result = pg_query_params($conn, 
                        "UPDATE produk_layanan SET judul = $1, deskripsi = $2, kategori = $3, updated_at = NOW() WHERE id_produk_layanan = $4",
                        [$judul, $deskripsi, $kategori, $id_produk]
                    );
                }
                
                if ($update_result) {
                    header("Location: ?page=produk_layanan&success=" . urlencode("Produk/Layanan berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui produk/layanan!";
                }
            } else {
                // Insert
                $insert_result = pg_query_params($conn, 
                    "INSERT INTO produk_layanan (judul, deskripsi, kategori, gambar_path, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, NOW())",
                    [$judul, $deskripsi, $kategori, $gambar_path, $id_admin]
                );
                
                if ($insert_result) {
                    header("Location: ?page=produk_layanan&success=" . urlencode("Produk/Layanan berhasil ditambahkan!"));
                    exit();
                } else {
                    $error_msg = "Gagal menambahkan produk/layanan!";
                }
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

// Pagination
$limit = 9;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(judul ILIKE $$param_count OR deskripsi ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

if (!empty($filter_kategori)) {
    $param_count++;
    $where_conditions[] = "kategori = $$param_count";
    $query_params[] = $filter_kategori;
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
if (count($query_params) > 0) {
    $count_query_str = "SELECT COUNT(*) as total FROM produk_layanan $where_clause";
    $count_query_str = str_replace('$param_count', '$' . $param_count, $count_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $count_query_str = str_replace('$param_count', '$' . $i, $count_query_str);
    }
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM produk_layanan");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $produk_query_str = "SELECT * FROM produk_layanan $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $produk_query_str = str_replace('$param_count', '$' . $param_count, $produk_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $produk_query_str = str_replace('$param_count', '$' . $i, $produk_query_str);
    }
    $produk_result = pg_query_params($conn, $produk_query_str, $query_params);
} else {
    $produk_result = pg_query($conn, "SELECT * FROM produk_layanan ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

// Get kategori for filter
$kategori_result = pg_query($conn, "SELECT DISTINCT kategori FROM produk_layanan WHERE kategori IS NOT NULL ORDER BY kategori");
?>

<!-- Success/Error Messages -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Produk & Layanan</h4>
        <small class="text-muted">Kelola produk dan layanan NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalProduk">
        <i class="fas fa-plus me-2"></i>Tambah Produk/Layanan
    </button>
</div>

<!-- Search & Filter -->
<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="produk_layanan">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Cari produk/layanan..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" name="kategori">
                            <option value="">Semua Kategori</option>
                            <?php while($kat = pg_fetch_assoc($kategori_result)): ?>
                                <option value="<?= htmlspecialchars($kat['kategori']) ?>" <?= $filter_kategori == $kat['kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_kategori)): ?>
                            <a href="?page=produk_layanan" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Produk Cards -->
<div class="row g-4 mb-4">
    <?php if (pg_num_rows($produk_result) > 0): ?>
        <?php while($row = pg_fetch_assoc($produk_result)): ?>
            <div class="col-md-4">
                <div class="card h-100" style="transition: all 0.3s;">
                    <?php if (!empty($row['gambar_path']) && file_exists($row['gambar_path'])): ?>
                        <img src="<?= htmlspecialchars($row['gambar_path']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($row['judul']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-box text-muted" style="font-size: 4rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title fw-bold mb-0"><?= htmlspecialchars($row['judul']) ?></h6>
                            <?php if (!empty($row['kategori'])): ?>
                                <span class="badge badge-category badge-produk">
                                    <?= htmlspecialchars($row['kategori']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted" style="font-size: 0.9rem;">
                            <?= htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 120)) ?>
                            <?= strlen($row['deskripsi'] ?? '') > 120 ? '...' : '' ?>
                        </p>
                    </div>
                    
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between">
                            <a href="?page=produk_layanan&edit=<?= $row['id_produk_layanan'] ?>" class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-delete" 
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus produk/layanan ini?')) window.location.href='?page=produk_layanan&delete=<?= $row['id_produk_layanan'] ?>'">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-box"></i>
                        <p>Tidak ada data produk/layanan</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=produk_layanan&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=produk_layanan&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=produk_layanan&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalProduk" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Produk/Layanan' : 'Tambah Produk/Layanan' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=produk_layanan'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_produk_layanan" value="<?= $edit_data ? $edit_data['id_produk_layanan'] : '' ?>">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Gambar Produk/Layanan</label>
                        <div class="border rounded p-3 bg-light">
                            <?php if ($edit_data && !empty($edit_data['gambar_path'])): ?>
                                <img src="<?= htmlspecialchars($edit_data['gambar_path']) ?>" 
                                     class="img-fluid mb-2" 
                                     style="max-height: 200px; object-fit: cover;"
                                     id="preview-gambar">
                            <?php else: ?>
                                <div id="preview-gambar" class="mb-2">
                                    <i class="fas fa-box text-muted" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="gambar" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted">Format: JPG, PNG, GIF | Max: 5MB</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['judul']) : '' ?>" 
                               placeholder="Nama produk/layanan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select" name="kategori">
                            <option value="">Pilih Kategori</option>
                            <option value="Produk" <?= ($edit_data && $edit_data['kategori'] == 'Produk') ? 'selected' : '' ?>>Produk</option>
                            <option value="Layanan" <?= ($edit_data && $edit_data['kategori'] == 'Layanan') ? 'selected' : '' ?>>Layanan</option>
                            <option value="Software" <?= ($edit_data && $edit_data['kategori'] == 'Software') ? 'selected' : '' ?>>Software</option>
                            <option value="Hardware" <?= ($edit_data && $edit_data['kategori'] == 'Hardware') ? 'selected' : '' ?>>Hardware</option>
                            <option value="Konsultasi" <?= ($edit_data && $edit_data['kategori'] == 'Konsultasi') ? 'selected' : '' ?>>Konsultasi</option>
                            <option value="Lainnya" <?= ($edit_data && $edit_data['kategori'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="5" 
                                  placeholder="Deskripsikan produk/layanan secara detail..."><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=produk_layanan" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($edit_data): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('modalProduk'));
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview-gambar');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}

.card-footer {
    padding: 0.75rem 1rem;
}
</style>