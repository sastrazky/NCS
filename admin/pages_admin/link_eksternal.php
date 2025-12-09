<?php
// pages/link_eksternal.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';
// Ambil ID Admin yang sedang login (Diasumsikan SESSION sudah dimulai)
$id_admin = isset($_SESSION['id_admin']) ? (int)$_SESSION['id_admin'] : 0;

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_link = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM link_eksternal WHERE id_link = $1", [$id_link]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_link = (int)$_GET['delete'];
    
    // --- LANGKAH 1 (DELETE): Ambil Judul Item untuk Log ---
    $link_query = pg_query_params($conn, "SELECT nama_link FROM link_eksternal WHERE id_link = $1", [$id_link]);
    $item_title = 'Link ID ' . $id_link; // Default title

    if ($link_row = pg_fetch_assoc($link_query)) {
        $item_title = $link_row['nama_link']; // Ambil nama link untuk log

        $delete_result = pg_query_params($conn, "DELETE FROM link_eksternal WHERE id_link = $1", [$id_link]);
        
        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'link', $safe_item_title, 'dihapus')
            ";
            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Link berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus link!";
        }
    } else {
        $error_msg = "Gagal mengambil data link untuk dihapus!";
    }
    
    // Perbaikan: Redirect menggunakan success/error message
    header("Location: ?page=link_eksternal" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_link = isset($_POST['id_link']) ? (int)$_POST['id_link'] : 0;
    $nama_link = trim($_POST['nama_link']);
    $uri = trim($_POST['uri']);
    $kategori = $_POST['kategori'];
    $urutan = (int)$_POST['urutan'];
    
    // Validation
    if (empty($nama_link) || empty($uri)) {
        $error_msg = "Nama link dan URL harus diisi!";
    } else if (!filter_var($uri, FILTER_VALIDATE_URL)) {
        $error_msg = "Format URL tidak valid!";
    } else {
        if ($id_link > 0) {
            // Update
            $update_result = pg_query_params($conn, 
                "UPDATE link_eksternal SET nama_link = $1, uri = $2, kategori = $3, urutan = $4, id_admin = $5, updated_at = NOW() WHERE id_link = $6",
                [$nama_link, $uri, $kategori, $urutan, $id_admin, $id_link]
            );
            
            if ($update_result) {
                // --- LOGGING UPDATE ---
                $safe_item_title = pg_escape_literal($conn, $nama_link);
                $log_query = "
                    INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                    VALUES ($id_admin, 'link', $safe_item_title, 'diperbarui')
                ";
                pg_query($conn, $log_query);
                // ----------------------
                header("Location: ?page=link_eksternal&success=" . urlencode("Link berhasil diperbarui!"));
                exit();
            } else {
                $error_msg = "Gagal memperbarui link!";
            }
        } else {
            // Insert
            $insert_result = pg_query_params($conn, 
                "INSERT INTO link_eksternal (nama_link, uri, kategori, urutan, created_at, id_admin) VALUES ($1, $2, $3, $4, NOW(), $5)",
                [$nama_link, $uri, $kategori, $urutan, $id_admin] 
            );
            
            if ($insert_result) {
                // --- LOGGING INSERT ---
                $safe_item_title = pg_escape_literal($conn, $nama_link);
                $log_query = "
                    INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                    VALUES ($id_admin, 'link', $safe_item_title, 'ditambahkan')
                ";
                pg_query($conn, $log_query);
                // ----------------------
                header("Location: ?page=link_eksternal&success=" . urlencode("Link berhasil ditambahkan!"));
                exit();
            } else {
                $error_msg = "Gagal menambahkan link!";
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}
// Get error message from URL
if (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
}

// Pagination
$limit = 15;
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
    $where_conditions[] = "(nama_link ILIKE $$param_count OR uri ILIKE $$param_count)";
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

// Fungsi pembantu untuk penomoran parameter PostgreSQL
function apply_pg_params($query_str, $query_params) {
    if (empty($query_params)) return $query_str;
    
    $j = 1;
    $temp_str = $query_str;
    // Ganti semua $$param_count secara berurutan dengan $1, $2, ...
    foreach ($query_params as $param) {
        $pos = strpos($temp_str, '$$param_count');
        if ($pos !== false) {
            $temp_str = substr_replace($temp_str, '$' . $j, $pos, strlen('$$param_count'));
            $j++;
        }
    }
    return $temp_str;
}

// Get total records
$count_query_str = "SELECT COUNT(*) as total FROM link_eksternal $where_clause";
$count_query_str_fixed = apply_pg_params($count_query_str, $query_params);

if (count($query_params) > 0) {
    $count_query = pg_query_params($conn, $count_query_str_fixed, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM link_eksternal $where_clause");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
$link_query_str = "SELECT * FROM link_eksternal $where_clause ORDER BY urutan ASC, created_at DESC LIMIT $limit OFFSET $offset";
$link_query_str_fixed = apply_pg_params($link_query_str, $query_params);

if (count($query_params) > 0) {
    $link_result = pg_query_params($conn, $link_query_str_fixed, $query_params);
} else {
    $link_result = pg_query($conn, "SELECT * FROM link_eksternal $where_clause ORDER BY urutan ASC, created_at DESC LIMIT $limit OFFSET $offset");
}

// Get kategori for filter
$kategori_result = pg_query($conn, "SELECT DISTINCT kategori FROM link_eksternal WHERE kategori IS NOT NULL ORDER BY kategori");
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
        <h4 class="mb-1 fw-bold">Link Eksternal</h4>
        <small class="text-muted">Kelola link eksternal dan referensi NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalLink">
        <i class="fas fa-plus me-2"></i>Tambah Link
    </button>
</div>

<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="link_eksternal">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Cari link..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" name="kategori">
                            <option value="">Semua Kategori</option>
                            <?php 
                            pg_result_seek($kategori_result, 0); // Reset pointer
                            while($kat = pg_fetch_assoc($kategori_result)): 
                            ?>
                                <option value="<?= htmlspecialchars($kat['kategori']) ?>" <?= $filter_kategori == $kat['kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_kategori)): ?>
                            <a href="?page=link_eksternal" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Link</th>
                <th>URL</th>
                <th>Kategori</th>
                <th style="width: 80px;">Urutan</th>
                <th style="width: 180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (pg_num_rows($link_result) > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while($row = pg_fetch_assoc($link_result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-link text-primary"></i>
                                <strong><?= htmlspecialchars($row['nama_link']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <a href="<?= htmlspecialchars($row['uri']) ?>" target="_blank" class="text-decoration-none">
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr($row['uri'], 0, 50)) ?>
                                    <?= strlen($row['uri']) > 50 ? '...' : '' ?>
                                    <i class="fas fa-external-link-alt ms-1"></i>
                                </small>
                            </a>
                        </td>
                        <td>
                            <?php if (!empty($row['kategori'])): ?>
                                <span class="badge badge-category badge-link">
                                    <?= htmlspecialchars($row['kategori']) ?>
                                </span>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= $row['urutan'] ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?page=link_eksternal&edit=<?= $row['id_link'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-delete" 
                                        onclick="if(confirm('Apakah Anda yakin ingin menghapus link ini?')) window.location.href='?page=link_eksternal&delete=<?= $row['id_link'] ?>'" 
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-link"></i>
                            <p>Tidak ada data link eksternal</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=link_eksternal&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=link_eksternal&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=link_eksternal&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<div class="modal fade" id="modalLink" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Link Eksternal' : 'Tambah Link Eksternal' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=link_eksternal'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_link" value="<?= $edit_data ? $edit_data['id_link'] : '' ?>">
                    
                    <div class="mb-3">
                        <label for="nama_link" class="form-label">Nama Link <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_link" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['nama_link']) : '' ?>" 
                               placeholder="Contoh: Website Resmi, Portal Mahasiswa, dll" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="uri" class="form-label">URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" name="uri" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['uri']) : '' ?>" 
                               placeholder="https://example.com" required>
                        <small class="text-muted">Contoh: https://www.example.com</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" name="kategori">
                                <option value="">Pilih Kategori</option>
                                <option value="Website Resmi" <?= ($edit_data && $edit_data['kategori'] == 'Website Resmi') ? 'selected' : '' ?>>Website Resmi</option>
                                <option value="Portal" <?= ($edit_data && $edit_data['kategori'] == 'Portal') ? 'selected' : '' ?>>Portal</option>
                                <option value="Dokumentasi" <?= ($edit_data && $edit_data['kategori'] == 'Dokumentasi') ? 'selected' : '' ?>>Dokumentasi</option>
                                <option value="Referensi" <?= ($edit_data && $edit_data['kategori'] == 'Referensi') ? 'selected' : '' ?>>Referensi</option>
                                <option value="Tools" <?= ($edit_data && $edit_data['kategori'] == 'Tools') ? 'selected' : '' ?>>Tools</option>
                                <option value="Social Media" <?= ($edit_data && $edit_data['kategori'] == 'Social Media') ? 'selected' : '' ?>>Social Media</option>
                                <option value="Lainnya" <?= ($edit_data && $edit_data['kategori'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="urutan" class="form-label">Urutan</label>
                            <input type="number" class="form-control" name="urutan" 
                                       value="<?= $edit_data ? $edit_data['urutan'] : '0' ?>" min="0">
                            <small class="text-muted">Urutan tampil</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=link_eksternal" class="btn btn-secondary">Batal</a>
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
            var modalElement = document.getElementById('modalLink');
            if (!modalElement.classList.contains('show')) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        }
    });
</script>
<?php endif; ?>