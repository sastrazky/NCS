<?php
// pages/arsip.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';
$id_admin = (int)$_SESSION['id_admin']; // Ambil ID Admin yang sedang login

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_arsip = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM arsip WHERE id_arsip = $1", [$id_arsip]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_arsip = (int)$_GET['delete'];
    
    // --- LANGKAH 1 (DELETE): Ambil Judul Item untuk Log ---
    $file_query = pg_query_params($conn, "SELECT judul_dokumen, file_pdf_path FROM arsip WHERE id_arsip = $1", [$id_arsip]);
    $item_title = 'Arsip ID ' . $id_arsip; // Default title
    
    if ($file_row = pg_fetch_assoc($file_query)) {
        $item_title = $file_row['judul_dokumen']; // Ambil judul dokumen untuk log

        // Delete file if exists
        if (file_exists($file_row['file_pdf_path'])) {
            @unlink($file_row['file_pdf_path']);
        }
        
        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM arsip WHERE id_arsip = $1", [$id_arsip]);
        
        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'arsip', $safe_item_title, 'dihapus')
            ";
            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Arsip berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus arsip!";
        }
    } else {
        $error_msg = "Gagal mengambil data arsip untuk dihapus!";
    }
    
    // Perbaikan: Redirect menggunakan success/error message
    header("Location: ?page=arsip" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_arsip = isset($_POST['id_arsip']) ? (int)$_POST['id_arsip'] : 0;
    $judul_dokumen = trim($_POST['judul_dokumen']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = trim($_POST['kategori']);
    // $id_admin sudah didefinisikan di atas
    
    // Validation
    if (empty($judul_dokumen)) {
        $error_msg = "Judul dokumen harus diisi!";
    } else {
        $upload_dir = 'uploads/arsip/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_pdf_path = '';
        $ukuran_file_mb = 0;
        $new_file_uploaded = false;
        
        // Handle file upload
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0) {
            $allowed_ext = ['pdf'];
            $file_size = $_FILES['file_pdf']['size'];
            $file_tmp = $_FILES['file_pdf']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // $file_name tidak terdefinisi di sini

            // Perbaikan: Ambil nama file dari $_FILES
            $uploaded_file_name = $_FILES['file_pdf']['name'];
            $file_ext = strtolower(pathinfo($uploaded_file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Hanya file PDF yang diperbolehkan!";
            } else if ($file_size > 50 * 1024 * 1024) { // 50MB max
                $error_msg = "Ukuran file maksimal 50MB!";
            } else {
                $new_file_name = 'arsip_' . time() . '_' . uniqid() . '.pdf';
                $file_pdf_path = $upload_dir . $new_file_name;
                $ukuran_file_mb = round($file_size / (1024 * 1024), 2);
                
                if (!move_uploaded_file($file_tmp, $file_pdf_path)) {
                    $error_msg = "Gagal mengupload file!";
                    $file_pdf_path = '';
                } else {
                    $new_file_uploaded = true;
                }
            }
        }
        
        if (empty($error_msg)) {
            if ($id_arsip > 0) {
                // Update
                $update_query_fields = "judul_dokumen = $1, deskripsi = $2, kategori = $3";
                $params = [$judul_dokumen, $deskripsi, $kategori, $id_arsip]; // 4 params

                if ($new_file_uploaded) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT file_pdf_path FROM arsip WHERE id_arsip = $1", [$id_arsip]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (file_exists($old_file_row['file_pdf_path'])) {
                            @unlink($old_file_row['file_pdf_path']);
                        }
                    }
                    
                    // Update query dan params dengan file_pdf_path baru
                    $update_query_fields = "judul_dokumen = $1, deskripsi = $2, kategori = $3, file_pdf_path = $4, ukuran_file_mb = $5";
                    $params = [$judul_dokumen, $deskripsi, $kategori, $file_pdf_path, $ukuran_file_mb, $id_arsip]; // 6 params
                }
                
                // Eksekusi Update
                $update_result = pg_query_params($conn, 
                    "UPDATE arsip SET $update_query_fields, updated_at = NOW() WHERE id_arsip = $" . count($params),
                    $params
                );

                if ($update_result) {
                    // --- LOGGING UPDATE ---
                    $safe_item_title = pg_escape_literal($conn, $judul_dokumen);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'arsip', $safe_item_title, 'diperbarui')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------
                    header("Location: ?page=arsip&success=" . urlencode("Arsip berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui arsip!";
                }
            } else {
                // Insert
                if (empty($file_pdf_path)) {
                    $error_msg = "File PDF harus diupload!";
                } else {
                    $insert_result = pg_query_params($conn, 
                        "INSERT INTO arsip (judul_dokumen, deskripsi, file_pdf_path, ukuran_file_mb, kategori, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, NOW())",
                        [$judul_dokumen, $deskripsi, $file_pdf_path, $ukuran_file_mb, $kategori, $id_admin]
                    );
                    
                    if ($insert_result) {
                        // --- LOGGING INSERT ---
                        $safe_item_title = pg_escape_literal($conn, $judul_dokumen);
                        $log_query = "
                            INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                            VALUES ($id_admin, 'arsip', $safe_item_title, 'ditambahkan')
                        ";
                        pg_query($conn, $log_query);
                        // ----------------------
                        header("Location: ?page=arsip&success=" . urlencode("Arsip berhasil ditambahkan!"));
                        exit();
                    } else {
                        $error_msg = "Gagal menambahkan arsip!";
                    }
                }
            }
        }
    }
}

// Handle Download
if (isset($_GET['download'])) {
    $id_arsip = (int)$_GET['download'];
    
    $download_query = pg_query_params($conn, "SELECT file_pdf_path, judul_dokumen FROM arsip WHERE id_arsip = $1", [$id_arsip]);
    
    if ($download_row = pg_fetch_assoc($download_query)) {
        $file_path = $download_row['file_pdf_path'];
        $file_name_clean = preg_replace("/[^a-zA-Z0-9_\-]/", "_", $download_row['judul_dokumen']); // Sanitasi nama file

        if (file_exists($file_path)) {
            // Update download count
            pg_query_params($conn, "UPDATE arsip SET jumlah_download = jumlah_download + 1 WHERE id_arsip = $1", [$id_arsip]);
            
            // Force download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $file_name_clean . '.pdf"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        }
    }
    // Jika gagal download, tambahkan error msg dan redirect
    $error_msg = "File tidak ditemukan atau gagal diakses.";
    header("Location: ?page=arsip&error=" . urlencode($error_msg));
    exit();
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}
// Get error message from URL (ditambahkan untuk menangani error dari redirect delete/download)
if (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
}

// Pagination
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$query_params = [];

if (!empty($search)) {
    // Penggunaan $1 untuk pg_query_params
    $where_clause = "WHERE judul_dokumen ILIKE $1 OR kategori ILIKE $1";
    $query_params[] = '%' . $search . '%';
}

// Get total records
if (!empty($search)) {
    // Ganti $1 dengan placeholder $1 dalam string SQL
    $count_query_str = str_replace('$1', '$1', "SELECT COUNT(*) as total FROM arsip $where_clause");
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM arsip");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
$arsip_query_base = "SELECT a.*, ad.username 
                     FROM arsip a 
                     LEFT JOIN admin ad ON a.id_admin = ad.id_admin 
                     $where_clause
                     ORDER BY a.created_at DESC 
                     LIMIT $limit OFFSET $offset";

if (!empty($search)) {
    // Ganti $1 dengan placeholder $1 dalam string SQL
    $arsip_query_str = str_replace('$1', '$1', $arsip_query_base);
    $arsip_result = pg_query_params($conn, $arsip_query_str, $query_params);
} else {
    $arsip_result = pg_query($conn, $arsip_query_base);
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
        <h4 class="mb-1 fw-bold">Arsip PDF</h4>
        <small class="text-muted">Kelola dokumen dan file PDF</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalArsip">
        <i class="fas fa-plus me-2"></i>Tambah Arsip
    </button>
</div>

<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="arsip">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari arsip..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary-custom" type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="?page=arsip" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Dokumen</th>
                <th>Kategori</th>
                <th>Ukuran</th>
                <th>Download</th>
                <th>Tanggal</th>
                <th style="width: 200px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (pg_num_rows($arsip_result) > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while($row = pg_fetch_assoc($arsip_result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="file-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <strong class="d-block"><?= htmlspecialchars($row['judul_dokumen']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-category badge-arsip">
                                <?= htmlspecialchars($row['kategori'] ?? 'Umum') ?>
                            </span>
                        </td>
                        <td><?= number_format($row['ukuran_file_mb'], 2) ?> MB</td>
                        <td>
                            <span class="badge bg-success">
                                <i class="fas fa-download me-1"></i><?= number_format($row['jumlah_download']) ?>
                            </span>
                        </td>
                        <td>
                            <small><?= date('d M Y', strtotime($row['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?page=arsip&download=<?= $row['id_arsip'] ?>" class="btn btn-sm btn-download" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="?page=arsip&edit=<?= $row['id_arsip'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-delete" 
                                        onclick="if(confirm('Apakah Anda yakin ingin menghapus arsip \'<?= htmlspecialchars(addslashes($row['judul_dokumen'])) ?>\'?')) window.location.href='?page=arsip&delete=<?= $row['id_arsip'] ?>'" 
                                        data-href="?page=arsip&delete=<?= $row['id_arsip'] ?>" title="Hapus">
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
                            <i class="fas fa-file-pdf"></i>
                            <p>Tidak ada data arsip</p>
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
                <a class="page-link" href="?page=arsip&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=arsip&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=arsip&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<div class="modal fade" id="modalArsip" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="formArsip">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Arsip PDF' : 'Tambah Arsip PDF' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=arsip'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_arsip" value="<?= $edit_data ? $edit_data['id_arsip'] : '' ?>">
                    
                    <div class="mb-3">
                        <label for="judul_dokumen" class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul_dokumen" name="judul_dokumen" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['judul_dokumen']) : '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori" name="kategori">
                            <option value="Penelitian" <?= ($edit_data && $edit_data['kategori'] == 'Penelitian') ? 'selected' : '' ?>>Penelitian</option>
                            <option value="Pengabdian" <?= ($edit_data && $edit_data['kategori'] == 'Pengabdian') ? 'selected' : '' ?>>Pengabdian</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file_pdf" class="form-label">
                            File PDF 
                            <?php if (!$edit_data): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="file" class="form-control" id="file_pdf" name="file_pdf" accept=".pdf" <?= !$edit_data ? 'required' : '' ?>>
                        <small class="text-muted">Format: PDF | Maksimal: 50MB</small>
                        
                        <?php if ($edit_data): ?>
                            <div class="mt-2 alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    File saat ini: <strong><?= htmlspecialchars($edit_data['judul_dokumen']) ?>.pdf</strong> 
                                    (<?= number_format($edit_data['ukuran_file_mb'], 2) ?> MB)
                                    <br>Kosongkan jika tidak ingin mengubah file
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=arsip" class="btn btn-secondary">Batal</a>
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
    // Auto show modal when edit mode
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap !== 'undefined') {
            var modalElement = document.getElementById('modalArsip');
            if (!modalElement.classList.contains('show')) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        }
    });
</script>
<?php endif; ?>

<script>
// Handle delete button click
document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
    });
});
</script>