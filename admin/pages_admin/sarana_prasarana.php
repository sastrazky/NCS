<?php
// pages/sarana_prasarana.php

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
    $id_sarana = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_sarana = (int)$_GET['delete'];

    // --- LANGKAH 1 (DELETE): Ambil Judul Item (Nama Fasilitas) untuk Log ---
    $file_query = pg_query_params($conn, "SELECT nama_fasilitas, gambar_path FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
    $item_title = 'Sarana/Prasarana ID ' . $id_sarana; // Default title

    if ($file_row = pg_fetch_assoc($file_query)) {
        $item_title = $file_row['nama_fasilitas']; // Ambil nama fasilitas untuk log

        // Delete file if exists
        if (!empty($file_row['gambar_path']) && file_exists($file_row['gambar_path'])) {
            @unlink($file_row['gambar_path']); // Gunakan @ agar error path tidak menghentikan proses
        }

        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);

        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'sarana', $safe_item_title, 'dihapus')
            ";

            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Sarana/Prasarana berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus sarana/prasarana!";
        }
    } else {
        $error_msg = "Gagal mengambil data sarana/prasarana untuk dihapus!";
    }

    // Perbaikan: Redirect menggunakan success/error message
    header("Location: ?page=sarana_prasarana" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sarana = isset($_POST['id_sarana']) ? (int)$_POST['id_sarana'] : 0;
    $nama_fasilitas = trim($_POST['nama_fasilitas']);
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah = (int)$_POST['jumlah'];
    // KOLOM KONDISI DIHAPUS
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
                $new_file_name = 'sarana_' . time() . '_' . uniqid() . '.' . $file_ext;
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
            if ($id_sarana > 0) {
                // Update
                $update_query_fields = "nama_fasilitas = $1, deskripsi = $2, jumlah = $3, updated_at = NOW()";
                $params = [$nama_fasilitas, $deskripsi, $jumlah, $id_sarana]; // Parameter diset tanpa gambar_path

                if ($new_gambar_uploaded) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT gambar_path FROM sarana_prasarana WHERE id_sarana = $1", [$id_sarana]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['gambar_path']) && file_exists($old_file_row['gambar_path'])) {
                            @unlink($old_file_row['gambar_path']);
                        }
                    }

                    // Update query dan params dengan gambar_path baru
                    $update_query_fields = "nama_fasilitas = $1, deskripsi = $2, jumlah = $3, gambar_path = $4, updated_at = NOW()";
                    $params = [$nama_fasilitas, $deskripsi, $jumlah, $gambar_path, $id_sarana];
                }

                // Eksekusi Update
                $update_result = pg_query_params(
                    $conn,
                    "UPDATE sarana_prasarana SET $update_query_fields WHERE id_sarana = $" . count($params),
                    $params
                );

                if ($update_result) {
                    // --- LOGGING UPDATE ---
                    $safe_item_title = pg_escape_literal($conn, $nama_fasilitas);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'sarana', $safe_item_title, 'diperbarui')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------

                    header("Location: ?page=sarana_prasarana&success=" . urlencode("Sarana/Prasarana berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui sarana/prasarana!";
                }
            } else {
                // Insert
                // QUERY INSERT
                $insert_result = pg_query_params(
                    $conn,
                    "INSERT INTO sarana_prasarana (nama_fasilitas, deskripsi, jumlah, gambar_path, id_admin) VALUES ($1, $2, $3, $4, $5)",
                    [$nama_fasilitas, $deskripsi, $jumlah, $gambar_path, $id_admin]
                );

                if ($insert_result) {
                    // --- LOGGING INSERT ---
                    $safe_item_title = pg_escape_literal($conn, $nama_fasilitas);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'sarana', $safe_item_title, 'ditambahkan')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------

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
// Get error message from URL (ditambahkan untuk menangani error dari redirect delete)
if (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
}


// Pagination
$limit = 4;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// FILTER KONDISI DIHAPUS

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(nama_fasilitas ILIKE $$param_count OR deskripsi ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

// FILTER KONDISI DIHAPUS

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
if (count($query_params) > 0) {
    $count_query_str = "SELECT COUNT(*) as total FROM sarana_prasarana $where_clause";
    // Logika penggantian $param_count harus disesuaikan karena hanya ada 1 parameter ($search)
    $count_query_str = str_replace('$param_count', '$1', $count_query_str);
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $sarana_query_str = "SELECT * FROM sarana_prasarana $where_clause ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset";
    // Logika penggantian $param_count harus disesuaikan
    $sarana_query_str = str_replace('$param_count', '$1', $sarana_query_str);
    $sarana_result = pg_query_params($conn, $sarana_query_str, $query_params);
} else {
    $sarana_result = pg_query($conn, "SELECT * FROM sarana_prasarana ORDER BY nama_fasilitas ASC LIMIT $limit OFFSET $offset");
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
        <h4 class="mb-1 fw-bold">Sarana & Prasarana</h4>
        <small class="text-muted">Kelola data sarana dan prasarana NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalSarana">
        <i class="fas fa-plus me-2"></i>Tambah Sarana/Prasarana
    </button>
</div>

<div class="card mb-3" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body p-3">
        <form method="GET" action="">
            <input type="hidden" name="page" value="sarana_prasarana">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <input type="text" class="form-control" name="search"
                        placeholder="Cari sarana/prasarana..."
                        value="<?= htmlspecialchars($search) ?>"
                        style="height: 45px;">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary-custom px-4" type="submit" style="height: 45px;">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="?page=sarana_prasarana" class="btn btn-secondary ms-2" style="height: 45px;" title="Reset">
                            <i class="fas fa-redo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="width: 50px; padding: 12px;" class="text-center">No</th>
                        <th style="width: 100px; padding: 12px;" class="text-center">Gambar</th>
                        <th style="padding: 12px;">Nama Fasilitas</th>
                        <th style="padding: 12px;">Deskripsi</th>
                        <th style="width: 100px; padding: 12px;" class="text-center">Jumlah</th>
                        <th style="width: 120px; padding: 12px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (pg_num_rows($sarana_result) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = pg_fetch_assoc($sarana_result)): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++ ?></td>
                                <td class="text-center align-middle">
                                    <?php if (!empty($row['gambar_path']) && file_exists($row['gambar_path'])): ?>
                                        <img src="<?= htmlspecialchars($row['gambar_path']) ?>"
                                            class="img-thumbnail"
                                            style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                                            onclick="showImageModal('<?= htmlspecialchars($row['gambar_path']) ?>')">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                            style="width: 70px; height: 70px; margin: 0 auto;">
                                            <i class="fas fa-warehouse text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <strong class="d-block" style="word-wrap: break-word; word-break: break-word; white-space: normal; max-width: 250px;">
                                        <?= htmlspecialchars($row['nama_fasilitas']) ?>
                                    </strong>
                                </td>
                                <td class="align-middle">
                                    <small class="text-muted d-block" style="line-height: 1.4; word-wrap: break-word; word-break: break-word; white-space: normal; max-width: 300px;">
                                        <?= htmlspecialchars(substr($row['deskripsi'] ?? 'Tidak ada deskripsi', 0, 100)) ?>
                                        <?= strlen($row['deskripsi'] ?? '') > 100 ? '...' : '' ?>
                                    </small>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-primary px-3 py-2"><?= number_format($row['jumlah'] ?? 0) ?> Unit</span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="?page=sarana_prasarana&edit=<?= $row['id_sarana'] ?>"
                                            class="btn btn-sm btn-edit" title="Edit">
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
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-warehouse"></i>
                                    <p>Tidak ada data sarana/prasarana</p>
                                    <?php if (!empty($search)): ?>
                                        <small class="text-muted">Coba ubah kata kunci pencarian</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=sarana_prasarana&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php
            // Pagination logic untuk menampilkan max 5 halaman
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);

            if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=sarana_prasarana&p=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=sarana_prasarana&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="?page=sarana_prasarana&p=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $total_pages ?></a></li>
            <?php endif; ?>

            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=sarana_prasarana&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

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
                            <img id="preview-gambar"
                                src="<?= $edit_data && !empty($edit_data['gambar_path'])
                                            ? htmlspecialchars($edit_data['gambar_path'])
                                            : '' ?>"
                                class="img-fluid mb-2 <?= $edit_data && !empty($edit_data['gambar_path']) ? '' : 'd-none' ?>"
                                style="max-height: 200px; object-fit: cover;">

                            <?php if (!$edit_data || empty($edit_data['gambar_path'])): ?>
                                <div id="placeholder-icon">
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
                        <div class="col-md-12 mb-3"> <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah"
                                value="<?= $edit_data ? $edit_data['jumlah'] : '1' ?>" min="0">
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

<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img id="fullImage" src="" class="img-fluid w-100" alt="Full Image">
            </div>
        </div>
    </div>
</div>


<?php if ($edit_data): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan bootstrap dimuat sebelum membuat modal
            if (typeof bootstrap !== 'undefined') {
                var modalElement = document.getElementById('modalSarana');
                // Pastikan modal belum ditampilkan
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
        const placeholder = document.getElementById('placeholder-icon');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none'); // tampilkan gambar
                if (placeholder) placeholder.style.display = 'none'; // sembunyikan icon
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Fungsi tambahan untuk menampilkan gambar dalam modal (diperlukan karena ada onclick di tabel)
    function showImageModal(imagePath) {
        if (typeof bootstrap !== 'undefined') {
            document.getElementById('fullImage').src = imagePath;
            var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        } else {
            alert("Bootstrap JS tidak dimuat. Gambar tidak dapat ditampilkan dalam modal.");
        }
    }
</script>

<style>
    .btn-primary-custom {
        color: #fff;
        background-color: #0d6efd;
        /* Warna default Bootstrap Primary */
        border-color: #0d6efd;
    }

    .btn-primary-custom:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    .btn-edit {
        color: #0d6efd;
        border: none;
        background: transparent;
    }

    .btn-edit:hover {
        color: #0b5ed7;
        background-color: #f1f5f9;
        text-decoration: none;
    }

    .btn-delete {
        color: #dc3545;
        border: none;
        background: transparent;
    }

    .btn-delete:hover {
        color: #bb2d3b;
        background-color: #f1f5f9;
        text-decoration: none;
    }

    .card:hover {
        /* Hapus efek hover pada card container agar tabel lebih konsisten */
    }

    .card-footer {
        padding: 0.75rem 1rem;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
        color: #adb5bd;
    }

    .empty-state i {
        font-size: 3rem;
        color: #adb5bd;
    }

    .bg-light {
        background-color: #f8fafc !important;
    }

    .modal-backdrop.show {
        opacity: 0.5 !important;
    }
</style>