<?php
// pages/galeri.php

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
    $id_galeri = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM galeri WHERE id_galeri = $1", [$id_galeri]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_galeri = (int)$_GET['delete'];
    
    // --- LANGKAH 1 (DELETE): Ambil Judul Item untuk Log ---
    $file_query = pg_query_params($conn, "SELECT judul, media_path FROM galeri WHERE id_galeri = $1", [$id_galeri]);
    $item_title = 'Galeri ID ' . $id_galeri; // Default title

    if ($file_row = pg_fetch_assoc($file_query)) {
        $item_title = $file_row['judul']; // Ambil judul media untuk log

        // Delete file if exists
        if (!empty($file_row['media_path']) && file_exists($file_row['media_path'])) {
            @unlink($file_row['media_path']);
        }
        
        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM galeri WHERE id_galeri = $1", [$id_galeri]);
        
        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'galeri', $safe_item_title, 'dihapus')
            ";
            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Galeri berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus galeri!";
        }
    } else {
        $error_msg = "Gagal mengambil data galeri untuk dihapus!";
    }
    
    // Perbaikan: Redirect menggunakan success/error message
    header("Location: ?page=galeri" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_galeri = isset($_POST['id_galeri']) ? (int)$_POST['id_galeri'] : 0;
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tipe_media = $_POST['tipe_media']; // Nilai default, akan di-overwrite oleh tipe file
    $tanggal_kegiatan = !empty($_POST['tanggal_kegiatan']) ? $_POST['tanggal_kegiatan'] : null;
    $id_admin = $_SESSION['id_admin'];
    
    // Validation
    if (empty($judul)) {
        $error_msg = "Judul harus diisi!";
    } else {
        $upload_dir = 'uploads/galeri/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $media_path = '';
        $new_media_uploaded = false;
        
        // Handle file upload
        if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'wmv'];
            $file_name = $_FILES['media']['name'];
            $file_size = $_FILES['media']['size'];
            $file_tmp = $_FILES['media']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Hanya file gambar (JPG, PNG, GIF) atau video (MP4, AVI, MOV, WMV) yang diperbolehkan!";
            } else if ($file_size > 50 * 1024 * 1024) { // 50MB max
                $error_msg = "Ukuran file maksimal 50MB!";
            } else {
                // Detect media type from extension
                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $tipe_media = 'Foto';
                } else {
                    $tipe_media = 'Video';
                }
                
                $new_file_name = 'galeri_' . time() . '_' . uniqid() . '.' . $file_ext;
                $media_path = $upload_dir . $new_file_name;
                
                if (!move_uploaded_file($file_tmp, $media_path)) {
                    $error_msg = "Gagal mengupload media!";
                    $media_path = '';
                } else {
                    $new_media_uploaded = true;
                }
            }
        } else if ($id_galeri > 0) {
             // Jika Edit tapi tidak upload file baru, ambil tipe media lama
            $old_data_query = pg_query_params($conn, "SELECT tipe_media FROM galeri WHERE id_galeri = $1", [$id_galeri]);
            $old_data = pg_fetch_assoc($old_data_query);
            $tipe_media = $old_data['tipe_media'] ?? $_POST['tipe_media'];
        }
        
        if (empty($error_msg)) {
            if ($id_galeri > 0) {
                // Update
                $update_query_fields = "judul = $1, deskripsi = $2, tipe_media = $3, tanggal_kegiatan = $4";
                $params = [$judul, $deskripsi, $tipe_media, $tanggal_kegiatan, $id_galeri]; // 5 params

                if ($new_media_uploaded) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT media_path FROM galeri WHERE id_galeri = $1", [$id_galeri]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['media_path']) && file_exists($old_file_row['media_path'])) {
                            @unlink($old_file_row['media_path']);
                        }
                    }
                    
                    // Update query dan params dengan media_path baru
                    $update_query_fields = "judul = $1, deskripsi = $2, media_path = $3, tipe_media = $4, tanggal_kegiatan = $5";
                    $params = [$judul, $deskripsi, $media_path, $tipe_media, $tanggal_kegiatan, $id_galeri]; // 6 params
                }
                
                // Eksekusi Update
                $update_result = pg_query_params($conn, 
                    "UPDATE galeri SET $update_query_fields, updated_at = NOW() WHERE id_galeri = $" . count($params),
                    $params
                );
                
                if ($update_result) {
                    // --- LOGGING UPDATE ---
                    $safe_item_title = pg_escape_literal($conn, $judul);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'galeri', $safe_item_title, 'diperbarui')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------
                    header("Location: ?page=galeri&success=" . urlencode("Galeri berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui galeri!";
                }
            } else {
                // Insert
                if (empty($media_path)) {
                    $error_msg = "File media harus diupload!";
                } else {
                    $insert_result = pg_query_params($conn, 
                        "INSERT INTO galeri (judul, deskripsi, media_path, tipe_media, tanggal_kegiatan, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, NOW())",
                        [$judul, $deskripsi, $media_path, $tipe_media, $tanggal_kegiatan, $id_admin]
                    );
                    
                    if ($insert_result) {
                        // --- LOGGING INSERT ---
                        $safe_item_title = pg_escape_literal($conn, $judul);
                        $log_query = "
                            INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                            VALUES ($id_admin, 'galeri', $safe_item_title, 'ditambahkan')
                        ";
                        pg_query($conn, $log_query);
                        // ----------------------
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

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}
// Get error message from URL
if (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
}

// Fungsi pembantu untuk mengganti hanya kemunculan pertama (diperlukan karena menggunakan $$param_count)
function str_replace_once($search, $replace, $text) {
    $pos = strpos($text, $search);
    return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}


// Pagination
$limit = 4;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_tipe = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(judul ILIKE $$param_count OR deskripsi ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

if (!empty($filter_tipe)) {
    $param_count++;
    $where_conditions[] = "tipe_media = $$param_count";
    $query_params[] = $filter_tipe;
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Penomoran Parameter PostgreSQL (diperbaiki agar sesuai)
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
$count_query_str = "SELECT COUNT(*) as total FROM galeri $where_clause";
$count_query_str_fixed = apply_pg_params($count_query_str, $query_params);

if (count($query_params) > 0) {
    $count_query = pg_query_params($conn, $count_query_str_fixed, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM galeri $where_clause");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
$galeri_query_str = "SELECT * FROM galeri $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$galeri_query_str_fixed = apply_pg_params($galeri_query_str, $query_params);

if (count($query_params) > 0) {
    $galeri_result = pg_query_params($conn, $galeri_query_str_fixed, $query_params);
} else {
    $galeri_result = pg_query($conn, "SELECT * FROM galeri $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
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
        <h4 class="mb-1 fw-bold">Galeri</h4>
        <small class="text-muted">Kelola foto dan video kegiatan NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalGaleri">
        <i class="fas fa-plus me-2"></i>Tambah Media
    </button>
</div>

<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="galeri">
            <div class="row g-3">
                <div class="col-md-9">
                    <input type="text" class="form-control" name="search" placeholder="Cari galeri..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <select class="form-select" name="tipe">
                            <option value="">Semua Tipe</option>
                            <option value="Foto" <?= $filter_tipe == 'Foto' ? 'selected' : '' ?>>Foto</option>
                            <option value="Video" <?= $filter_tipe == 'Video' ? 'selected' : '' ?>>Video</option>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_tipe)): ?>
                            <a href="?page=galeri" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php if (pg_num_rows($galeri_result) > 0): ?>
        <?php while($row = pg_fetch_assoc($galeri_result)): ?>
            <div class="col-md-3">
                <div class="card h-100 galeri-card">
                    <div class="position-relative">
                        <?php if ($row['tipe_media'] == 'Foto' && !empty($row['media_path']) && file_exists($row['media_path'])): ?>
                            <img src="<?= htmlspecialchars($row['media_path']) ?>" 
                                 class="card-img-top galeri-img" 
                                 alt="<?= htmlspecialchars($row['judul']) ?>"
                                 style="height: 200px; object-fit: cover; cursor: pointer;"
                                 onclick="viewMedia('<?= htmlspecialchars($row['media_path']) ?>', 'foto', '<?= htmlspecialchars(addslashes($row['judul'])) ?>')">
                        <?php elseif ($row['tipe_media'] == 'Video' && !empty($row['media_path']) && file_exists($row['media_path'])): ?>
                            <div class="video-thumbnail" style="height: 200px; background: #000; position: relative; cursor: pointer;"
                                 onclick="viewMedia('<?= htmlspecialchars($row['media_path']) ?>', 'video', '<?= htmlspecialchars(addslashes($row['judul'])) ?>')">
                                <video style="width: 100%; height: 100%; object-fit: cover;">
                                    <source src="<?= htmlspecialchars($row['media_path']) ?>" type="video/mp4">
                                </video>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <i class="fas fa-play-circle text-white" style="font-size: 3rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge <?= $row['tipe_media'] == 'Foto' ? 'bg-primary' : 'bg-danger' ?>">
                                <i class="fas fa-<?= $row['tipe_media'] == 'Foto' ? 'camera' : 'video' ?> me-1"></i>
                                <?= htmlspecialchars($row['tipe_media']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($row['judul']) ?></h6>
                        <?php if (!empty($row['deskripsi'])): ?>
                            <p class="card-text text-muted" style="font-size: 0.85rem;">
                                <?= htmlspecialchars(substr($row['deskripsi'], 0, 80)) ?>
                                <?= strlen($row['deskripsi']) > 80 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($row['tanggal_kegiatan'])): ?>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d M Y', strtotime($row['tanggal_kegiatan'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between">
                            <a href="?page=galeri&edit=<?= $row['id_galeri'] ?>" class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-delete" 
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus media ini?')) window.location.href='?page=galeri&delete=<?= $row['id_galeri'] ?>'">
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
                        <i class="fas fa-images"></i>
                        <p>Tidak ada data galeri</p>
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
                <a class="page-link" href="?page=galeri&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=galeri&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=galeri&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_tipe) ? '&tipe=' . urlencode($filter_tipe) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<div class="modal fade" id="modalGaleri" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Galeri' : 'Tambah Galeri' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=galeri'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_galeri" value="<?= $edit_data ? $edit_data['id_galeri'] : '' ?>">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Upload Foto/Video <?= !$edit_data ? '<span class="text-danger">*</span>' : '' ?></label>
                        <div class="border rounded p-3 bg-light">
                            <?php 
                            $initial_preview_content = '';
                            if ($edit_data && !empty($edit_data['media_path'])): 
                                $path = htmlspecialchars($edit_data['media_path']);
                                if ($edit_data['tipe_media'] == 'Foto') {
                                    $initial_preview_content = '<img src="' . $path . '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">';
                                } else {
                                    $initial_preview_content = '<video class="img-fluid mb-2" style="max-height: 200px;" controls><source src="' . $path . '" type="video/mp4"></video>';
                                }
                            else: 
                                $initial_preview_content = '<i class="fas fa-images text-muted" style="font-size: 4rem;"></i>';
                            endif;
                            ?>
                            <div id="preview-media" class="mb-2">
                                <?= $initial_preview_content ?>
                            </div>
                            <input type="file" class="form-control" name="media" accept="image/*,video/*" onchange="previewMedia(this)" <?= !$edit_data ? 'required' : '' ?>>
                            <small class="text-muted">Format: JPG, PNG, GIF, MP4, AVI, MOV | Max: 50MB</small>
                        </div>
                    </div>
                    
                    <input type="hidden" name="tipe_media" value="<?= $edit_data ? $edit_data['tipe_media'] : 'Foto' ?>">
                    
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['judul']) : '' ?>" 
                               placeholder="Judul foto/video" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" 
                                     placeholder="Deskripsikan media..."><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                        <input type="date" class="form-control" name="tanggal_kegiatan" 
                               value="<?= $edit_data ? $edit_data['tanggal_kegiatan'] : '' ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=galeri" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalViewMedia" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMediaTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="viewMediaBody">
            </div>
        </div>
    </div>
</div>

<?php if ($edit_data): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap !== 'undefined') {
            var modalElement = document.getElementById('modalGaleri');
            if (!modalElement.classList.contains('show')) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        }
    });
</script>
<?php endif; ?>

<script>
function previewMedia(input) {
    const preview = document.getElementById('preview-media');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Update the hidden input for tipe_media based on file type
            let tipeMedia;
            let previewContent;

            if (file.type.startsWith('image/')) {
                tipeMedia = 'Foto';
                previewContent = '<img src="' + e.target.result + '" class="img-fluid mb-2" style="max-height: 200px; object-fit: cover;">';
            } else if (file.type.startsWith('video/')) {
                tipeMedia = 'Video';
                previewContent = '<video class="img-fluid mb-2" style="max-height: 200px;" controls><source src="' + e.target.result + '" type="' + file.type + '"></video>';
            } else {
                tipeMedia = 'Lainnya'; // Fallback
                previewContent = '<i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i><p>Tipe file tidak didukung</p>';
            }

            document.querySelector('input[name="tipe_media"]').value = tipeMedia;
            preview.innerHTML = previewContent;
        }
        
        reader.readAsDataURL(file);
    } else {
        // Jika file dibatalkan/dihapus, kembalikan preview ke konten awal (atau icon default jika Insert)
        preview.innerHTML = '<?php echo $initial_preview_content; ?>';
        // Reset hidden type field
        document.querySelector('input[name="tipe_media"]').value = '<?= $edit_data ? $edit_data['tipe_media'] : 'Foto' ?>';
    }
}

function viewMedia(path, type, title) {
    document.getElementById('viewMediaTitle').textContent = title;
    const body = document.getElementById('viewMediaBody');
    
    // Stop any currently playing video before replacing content
    const existingMedia = body.querySelector('video');
    if (existingMedia) {
        existingMedia.pause();
    }

    if (type === 'foto') {
        body.innerHTML = '<img src="' + path + '" class="img-fluid" style="max-height: 80vh;">';
    } else {
        // Ensure video tag has appropriate type for playback
        body.innerHTML = '<video class="img-fluid" style="max-height: 80vh;" controls autoplay><source src="' + path + '" type="video/mp4"></video>';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalViewMedia'));
    modal.show();
}
</script>

<style>
.pagination {
    margin-top: 1.5rem;
    gap: 0.25rem;
}

.pagination .page-link {
    color: var(--primary-color);
    background-color: white;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
    margin: 0 3px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    min-width: 40px;
    text-align: center;
}

.pagination .page-link:hover {
    background-color: #f1f5f9;
    border-color: var(--primary-color);
    color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
}

.pagination .page-link:focus {
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
    outline: none;
    z-index: 3;
}

.pagination .page-link:active {
    background-color: #e0e7ff;
    border-color: var(--primary-color);
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-color), #2563eb);
    border-color: var(--primary-color);
    color: white;
    font-weight: 600;
    box-shadow: 0 3px 8px rgba(59, 130, 246, 0.25);
    transform: translateY(-1px);
    z-index: 3;
}

.pagination .page-item.active .page-link:hover {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    transform: translateY(-1px);
}

.pagination .page-item.disabled .page-link {
    color: #cbd5e1;
    pointer-events: none;
    cursor: not-allowed;
    background-color: #f8fafc;
    border-color: #e2e8f0;
    opacity: 0.6;
}

/* Ellipsis styling */
.pagination .page-item.disabled .page-link[disabled] {
    background-color: transparent;
    border-color: transparent;
}

/* Icon arrows */
.pagination .page-link i {
    font-size: 0.85rem;
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 576px) {
    .pagination .page-link {
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
        min-width: 36px;
    }
    
    .pagination {
        gap: 0.15rem;
    }
}
.galeri-card {
    transition: all 0.3s;
}

.galeri-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

.galeri-img {
    transition: transform 0.3s;
}

.galeri-card:hover .galeri-img {
    transform: scale(1.05);
}

.video-thumbnail video {
    pointer-events: none;
}
</style>