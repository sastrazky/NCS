<?php
// pages/produk_layanan.php

// Pastikan $conn tersedia dan SESSION dimulai
if (!isset($conn) || !isset($_SESSION['id_admin'])) {
    // Diasumsikan koneksi dan session sudah dicek di index.php
}

// Handle success/error messages
$success_msg = '';
$error_msg = '';
$id_admin = (int)$_SESSION['id_admin']; // Ambil ID Admin yang sedang login

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
    
    // --- LANGKAH 1 (DELETE): Ambil Judul Item untuk Log ---
    $file_query = pg_query_params($conn, "SELECT judul, gambar_path FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
    $item_title = 'Produk/Layanan ID ' . $id_produk; // Default title
    
    if ($file_row = pg_fetch_assoc($file_query)) {
        $item_title = $file_row['judul']; // Ambil judul untuk log

        // Delete file if exists
        if (!empty($file_row['gambar_path']) && file_exists($file_row['gambar_path'])) {
            @unlink($file_row['gambar_path']); // Gunakan @ agar error path tidak menghentikan proses
        }
        
        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
        
        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'produk', $safe_item_title, 'dihapus')
            ";
            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Produk/Layanan berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus produk/layanan!";
        }
    } else {
        $error_msg = "Gagal mengambil data produk/layanan untuk dihapus!";
    }
    
    // Perbaikan: Redirect menggunakan success/error message
    header("Location: ?page=produk_layanan" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
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
        $new_gambar_uploaded = false;
        
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
                } else {
                    $new_gambar_uploaded = true;
                }
            }
        }
        
        if (empty($error_msg)) {
            if ($id_produk > 0) {
                // Update
                $update_query_fields = "judul = $1, deskripsi = $2, kategori = $3, updated_at = NOW()";
                $params = [$judul, $deskripsi, $kategori, $id_produk]; // Parameter diset tanpa gambar_path

                if ($new_gambar_uploaded) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT gambar_path FROM produk_layanan WHERE id_produk_layanan = $1", [$id_produk]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['gambar_path']) && file_exists($old_file_row['gambar_path'])) {
                            @unlink($old_file_row['gambar_path']);
                        }
                    }
                    
                    // Update query dan params dengan gambar_path baru
                    $update_query_fields = "judul = $1, deskripsi = $2, kategori = $3, gambar_path = $4, updated_at = NOW()";
                    $params = [$judul, $deskripsi, $kategori, $gambar_path, $id_produk];
                }
                
                // Eksekusi Update
                $update_result = pg_query_params($conn, 
                    "UPDATE produk_layanan SET $update_query_fields WHERE id_produk_layanan = $" . count($params),
                    $params
                );
                
                if ($update_result) {
                    // --- LOGGING UPDATE ---
                    $safe_item_title = pg_escape_literal($conn, $judul);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'produk', $safe_item_title, 'diperbarui')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------

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
                    // --- LOGGING INSERT ---
                    $safe_item_title = pg_escape_literal($conn, $judul);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'produk', $safe_item_title, 'ditambahkan')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------

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
// Get error message from URL (ditambahkan untuk menangani error dari redirect delete)
if (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
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
    // Penyesuaian loop penamaan parameter untuk menghandle $param_count saat query string dibuat
    $count_query_str = "SELECT COUNT(*) as total FROM produk_layanan $where_clause";
    
    // Perbaikan logika penomoran parameter PostgreSQL
    $temp_str = $count_query_str;
    $j = 1;
    if (!empty($search)) {
        $temp_str = str_replace_once('$$param_count', '$' . $j, $temp_str);
        $j++;
    }
    if (!empty($filter_kategori)) {
        $temp_str = str_replace_once('$$param_count', '$' . $j, $temp_str);
    }
    $count_query = pg_query_params($conn, $temp_str, $query_params);

} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM produk_layanan");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $produk_query_str = "SELECT * FROM produk_layanan $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    
    // Perbaikan logika penomoran parameter PostgreSQL
    $temp_str = $produk_query_str;
    $j = 1;
    if (!empty($search)) {
        $temp_str = str_replace_once('$$param_count', '$' . $j, $temp_str);
        $j++;
    }
    if (!empty($filter_kategori)) {
        $temp_str = str_replace_once('$$param_count', '$' . $j, $temp_str);
    }
    $produk_result = pg_query_params($conn, $temp_str, $query_params);
} else {
    $produk_result = pg_query($conn, "SELECT * FROM produk_layanan ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

// Get kategori for filter
$kategori_result = pg_query($conn, "SELECT DISTINCT kategori FROM produk_layanan WHERE kategori IS NOT NULL ORDER BY kategori");

// Fungsi pembantu untuk mengganti hanya kemunculan pertama (diperlukan karena menggunakan $$param_count dua kali)
function str_replace_once($search, $replace, $text) {
    $pos = strpos($text, $search);
    return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

?>

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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Produk & Layanan</h4>
        <small class="text-muted">Kelola produk dan layanan NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalProduk">
        <i class="fas fa-plus me-2"></i>Tambah Produk/Layanan
    </button>
</div>

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
                            <?php 
                            $gambar_preview_content = '';
                            $initial_image_path = ($edit_data && !empty($edit_data['gambar_path'])) ? htmlspecialchars($edit_data['gambar_path']) : 'assets/img/no-image.png';

                            if ($edit_data && !empty($edit_data['gambar_path'])): 
                                $gambar_preview_content = '<img src="' . $initial_image_path . '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">';
                            else: 
                                $gambar_preview_content = '<i class="fas fa-box text-muted" style="font-size: 4rem;"></i>';
                            endif;
                            ?>
                            <div id="preview-gambar" class="mb-2">
                                <?= $gambar_preview_content ?>
                            </div>
                            
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
        if (typeof bootstrap !== 'undefined') {
            var modalElement = document.getElementById('modalProduk');
            if (!modalElement.classList.contains('show')) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        }
    });
</script>
<?php endif; ?>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview-gambar');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Gunakan tag img, bukan hanya icon
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        // Jika file dibatalkan, kembalikan ke icon/gambar default
        preview.innerHTML = '<?php echo $edit_data && !empty($edit_data['gambar_path']) ? '<img src="' . htmlspecialchars($edit_data['gambar_path']) . '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">' : '<i class="fas fa-box text-muted" style="font-size: 4rem;"></i>'; ?>';
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