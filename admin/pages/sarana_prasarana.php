<?php
// pages/sarana_prasarana.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_sarana = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_sarana = (int)$_GET['delete'];
    
    // Get file path before deleting
    $file_query = pg_query_params($conn, "SELECT gambar_path FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
    
    if ($file_row = pg_fetch_assoc($file_query)) {
        // Delete file if exists
        if (!empty($file_row['gambar_path']) && file_exists($file_row['gambar_path'])) {
            unlink($file_row['gambar_path']);
        }
        
        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
        
        if ($delete_result) {
            $success_msg = "Sarana/Prasarana berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus sarana/prasarana!";
        }
    }
    
    header("Location: ?page=sarana_prasarana&success=" . urlencode($success_msg));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sarana = isset($_POST['id_sarana']) ? (int)$_POST['id_sarana'] : 0;
    $nama_fasilitas = trim($_POST['nama_fasilitas']);
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah = (int)$_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    $id_admin = $_SESSION['id_admin'];
    
    // Validation
    if (empty($nama_fasilitas)) {
        $error_msg = "Nama fasilitas harus diisi!";
    } else {
        $upload_dir = 'uploads/sarana/';
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
                $new_file_name = 'sarana_' . time() . '_' . uniqid() . '.' . $file_ext;
                $gambar_path = $upload_dir . $new_file_name;
                
                if (!move_uploaded_file($file_tmp, $gambar_path)) {
                    $error_msg = "Gagal mengupload gambar!";
                    $gambar_path = '';
                }
            }
        }
        
        if (empty($error_msg)) {
            if ($id_sarana > 0) {
                // Update
                if (!empty($gambar_path)) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT gambar_path FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['gambar_path']) && file_exists($old_file_row['gambar_path'])) {
                            unlink($old_file_row['gambar_path']);
                        }
                    }
                    
                    $update_result = pg_query_params($conn, 
                        "UPDATE sarana_prasarana SET nama_fasilitas = $1, deskripsi = $2, jumlah = $3, kondisi = $4, gambar_path = $5, updated_at = NOW() WHERE id_sarana = $6",
                        [$nama_fasilitas, $deskripsi, $jumlah, $kondisi, $gambar_path, $id_sarana]
                    );
                } else {
                    $update_result = pg_query_params($conn, 
                        "UPDATE sarana_prasarana SET nama_fasilitas = $1, deskripsi = $2, jumlah = $3, kondisi = $4, updated_at = NOW() WHERE id_sarana = $5",
                        [$nama_fasilitas, $deskripsi, $jumlah, $kondisi, $id_sarana]
                    );
                }
                
                if ($update_result) {
                    header("Location: ?page=sarana_prasarana&success=" . urlencode("Sarana/Prasarana berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui sarana/prasarana!";
                }
            } else {
                // Insert
                $insert_result = pg_query_params($conn, 
                    "INSERT INTO sarana_prasarana (nama_fasilitas, deskripsi, jumlah, kondisi, gambar_path, id_admin, updated_at) VALUES ($1, $2, $3, $4, $5, $6, NOW())",
                    [$nama_fasilitas, $deskripsi, $jumlah, $kondisi, $gambar_path, $id_admin]
                );
                
                if ($insert_result) {
                    header("Location: ?page=sarana_prasarana&success=" . urlencode("Sarana/Prasarana berhasil ditambahkan!"));
                    exit();
                } else {
                    $error_msg = "Gagal menambahkan sarana/prasarana!";
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
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kondisi = isset($_GET['kondisi']) ? trim($_GET['kondisi']) : '';

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(nama_fasilitas ILIKE $$param_count OR deskripsi ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

if (!empty($filter_kondisi)) {
    $param_count++;
    $where_conditions[] = "kondisi = $$param_count";
    $query_params[] = $filter_kondisi;
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
if (count($query_params) > 0) {
    $count_query_str = "SELECT COUNT(*) as total FROM sarana_prasarana $where_clause";
    $count_query_str = str_replace('$param_count', '$' . $param_count, $count_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $count_query_str = str_replace('$param_count', '$' . $i, $count_query_str);
    }
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $sarana_query_str = "SELECT * FROM sarana_prasarana $where_clause ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset";
    $sarana_query_str = str_replace('$param_count', '$' . $param_count, $sarana_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $sarana_query_str = str_replace('$param_count', '$' . $i, $sarana_query_str);
    }
    $sarana_result = pg_query_params($conn, $sarana_query_str, $query_params);
} else {
    $sarana_result = pg_query($conn, "SELECT * FROM sarana_prasarana ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset");
}
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
        <h4 class="mb-1 fw-bold">Sarana & Prasarana</h4>
        <small class="text-muted">Kelola data sarana dan prasarana NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalSarana">
        <i class="fas fa-plus me-2"></i>Tambah Sarana/Prasarana
    </button>
</div>

<!-- Search & Filter -->
<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="sarana_prasarana">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Cari sarana/prasarana..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" name="kondisi">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" <?= $filter_kondisi == 'Baik' ? 'selected' : '' ?>>Baik</option>
                            <option value="Rusak Ringan" <?= $filter_kondisi == 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                            <option value="Rusak Berat" <?= $filter_kondisi == 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_kondisi)): ?>
                            <a href="?page=sarana_prasarana" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sarana Table -->
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 100px;">Gambar</th>
                <th>Nama Fasilitas</th>
                <th>Deskripsi</th>
                <th style="width: 80px;">Jumlah</th>
                <th style="width: 130px;">Kondisi</th>
                <th style="width: 180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (pg_num_rows($sarana_result) > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while($row = pg_fetch_assoc($sarana_result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($row['gambar_path']) && file_exists($row['gambar_path'])): ?>
                                <img src="<?= htmlspecialchars($row['gambar_path']) ?>" 
                                     class="img-thumbnail" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-warehouse text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_fasilitas']) ?></strong>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= htmlspecialchars(substr($row['deskripsi'] ?? '-', 0, 80)) ?>
                                <?= strlen($row['deskripsi'] ?? '') > 80 ? '...' : '' ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= number_format($row['jumlah'] ?? 0) ?></span>
                        </td>
                        <td>
                            <?php
                            $kondisi_class = '';
                            switch($row['kondisi']) {
                                case 'Baik':
                                    $kondisi_class = 'bg-success';
                                    break;
                                case 'Rusak Ringan':
                                    $kondisi_class = 'bg-warning';
                                    break;
                                case 'Rusak Berat':
                                    $kondisi_class = 'bg-danger';
                                    break;
                                default:
                                    $kondisi_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?= $kondisi_class ?>">
                                <?= htmlspecialchars($row['kondisi'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?page=sarana_prasarana&edit=<?= $row['id_sarana'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-delete" 
                                        onclick="if(confirm('Apakah Anda yakin ingin menghapus data ini?')) window.location.href='?page=sarana_prasarana&delete=<?= $row['id_sarana'] ?>'" 
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-warehouse"></i>
                            <p>Tidak ada data sarana/prasarana</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=sarana_prasarana&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=sarana_prasarana&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=sarana_prasarana&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kondisi) ? '&kondisi=' . urlencode($filter_kondisi) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalSarana" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Sarana/Prasarana' : 'Tambah Sarana/Prasarana' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=sarana_prasarana'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_sarana" value="<?= $edit_data ? $edit_data['id_sarana'] : '' ?>">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Gambar Fasilitas</label>
                        <div class="border rounded p-3 bg-light">
                            <?php if ($edit_data && !empty($edit_data['gambar_path'])): ?>
                                <img src="<?= htmlspecialchars($edit_data['gambar_path']) ?>" 
                                     class="img-fluid mb-2" 
                                     style="max-height: 200px; object-fit: cover;"
                                     id="preview-gambar">
                            <?php else: ?>
                                <div id="preview-gambar" class="mb-2">
                                    <i class="fas fa-warehouse text-muted" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="gambar" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted">Format: JPG, PNG, GIF | Max: 5MB</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_fasilitas" class="form-label">Nama Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_fasilitas" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['nama_fasilitas']) : '' ?>" 
                               placeholder="Contoh: Komputer Lab, Ruang Server, dll" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="4" 
                                  placeholder="Deskripsikan fasilitas..."><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" 
                                   value="<?= $edit_data ? $edit_data['jumlah'] : '1' ?>" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="kondisi" class="form-label">Kondisi</label>
                            <select class="form-select" name="kondisi">
                                <option value="Baik" <?= ($edit_data && $edit_data['kondisi'] == 'Baik') ? 'selected' : '' ?>>Baik</option>
                                <option value="Rusak Ringan" <?= ($edit_data && $edit_data['kondisi'] == 'Rusak Ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="Rusak Berat" <?= ($edit_data && $edit_data['kondisi'] == 'Rusak Berat') ? 'selected' : '' ?>>Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=sarana_prasarana" class="btn btn-secondary">Batal</a>
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
        var myModal = new bootstrap.Modal(document.getElementById('modalSarana'));
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