<?php
// pages/anggota.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_anggota = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM anggota WHERE id_anggota = $1", [$id_anggota]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_anggota = (int)$_GET['delete'];

    // Get file path before deleting
    $file_query = pg_query_params($conn, "SELECT foto_path FROM anggota WHERE id_anggota = $1", [$id_anggota]);

    if ($file_row = pg_fetch_assoc($file_query)) {
        // Delete file if exists
        if (!empty($file_row['foto_path']) && file_exists($file_row['foto_path'])) {
            unlink($file_row['foto_path']);
        }

        // Delete from database
        $delete_result = pg_query_params($conn, "DELETE FROM anggota WHERE id_anggota = $1", [$id_anggota]);

        if ($delete_result) {
            $success_msg = "Anggota berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus anggota!";
        }
    }

    header("Location: ?page=anggota&success=" . urlencode($success_msg));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota = isset($_POST['id_anggota']) ? (int)$_POST['id_anggota'] : 0;
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nip_nim = trim($_POST['nip_nim']);
    $jabatan = trim($_POST['jabatan']);
    $email = trim($_POST['email']);
    $urutan = (int)$_POST['urutan'];
    $status = $_POST['status'];
    $id_admin = $_SESSION['id_admin'];

    // Validation
    if (empty($nama_lengkap) || empty($nip_nim)) {
        $error_msg = "Nama lengkap dan NIP/NIM harus diisi!";
    } else {
        $upload_dir = 'uploads/anggota/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $foto_path = '';

        // Handle file upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan!";
            } else if ($file_size > 5 * 1024 * 1024) {
                $error_msg = "Ukuran file maksimal 5MB!";
            } else {
                $new_file_name = 'anggota_' . time() . '_' . uniqid() . '.' . $file_ext;
                $foto_path = $upload_dir . $new_file_name;

                if (!move_uploaded_file($file_tmp, $foto_path)) {
                    $error_msg = "Gagal mengupload foto!";
                    $foto_path = '';
                }
            }
        } else {

            // FOTO WAJIB SAAT INSERT
            if ($id_anggota == 0) {
                $error_msg = "Foto wajib diunggah saat menambah anggota!";
            }

            // Saat edit → foto boleh tidak diganti
            $foto_path = "";
        }


        if (empty($error_msg)) {
            if ($id_anggota > 0) {
                // Update
                if (!empty($foto_path)) {
                    // Get old file and delete it
                    $old_file_query = pg_query_params($conn, "SELECT foto_path FROM anggota WHERE id_anggota = $1", [$id_anggota]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (!empty($old_file_row['foto_path']) && file_exists($old_file_row['foto_path'])) {
                            unlink($old_file_row['foto_path']);
                        }
                    }

                    $update_result = pg_query_params(
                        $conn,
                        "UPDATE anggota SET nama_lengkap = $1, nip_nim = $2, jabatan = $3, email = $4, foto_path = $5, urutan = $6, status = $7, updated_at = NOW() WHERE id_anggota = $8",
                        [$nama_lengkap, $nip_nim, $jabatan, $email, $foto_path, $urutan, $status, $id_anggota]
                    );
                } else {
                    $update_result = pg_query_params(
                        $conn,
                        "UPDATE anggota SET nama_lengkap = $1, nip_nim = $2, jabatan = $3, email = $4, urutan = $5, status = $6, updated_at = NOW() WHERE id_anggota = $7",
                        [$nama_lengkap, $nip_nim, $jabatan, $email, $urutan, $status, $id_anggota]
                    );
                }

                if ($update_result) {
                    header("Location: ?page=anggota&success=" . urlencode("Anggota berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui anggota!";
                }
            } else {
                // Insert
                $insert_result = pg_query_params(
                    $conn,
                    "INSERT INTO anggota (nama_lengkap, nip_nim, jabatan, email, foto_path, urutan, status, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())",
                    [$nama_lengkap, $nip_nim, $jabatan, $email, $foto_path, $urutan, $status, $id_admin]
                );

                if ($insert_result) {
                    header("Location: ?page=anggota&success=" . urlencode("Anggota berhasil ditambahkan!"));
                    exit();
                } else {
                    $error_msg = "Gagal menambahkan anggota!";
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
$limit = 12;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$query_params = [];

if (!empty($search)) {
    $where_clause = "WHERE nama_lengkap ILIKE $1 OR nip_nim ILIKE $1 OR jabatan ILIKE $1";
    $query_params[] = '%' . $search . '%';
}

// Get total records
if (!empty($search)) {
    $count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM anggota $where_clause", $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM anggota");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (!empty($search)) {
    $anggota_query = "SELECT * FROM anggota $where_clause ORDER BY urutan ASC, nama_lengkap ASC LIMIT $limit OFFSET $offset";
    $anggota_result = pg_query_params($conn, $anggota_query, $query_params);
} else {
    $anggota_query = "SELECT * FROM anggota ORDER BY urutan ASC, nama_lengkap ASC LIMIT $limit OFFSET $offset";
    $anggota_result = pg_query($conn, $anggota_query);
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
        <h4 class="mb-1 fw-bold">Data Anggota</h4>
        <small class="text-muted">Kelola data anggota NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalAnggota">
        <i class="fas fa-plus me-2"></i>Tambah Anggota
    </button>
</div>

<!-- Search -->
<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="anggota">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari anggota (nama, NIP/NIM, jabatan)..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary-custom" type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="?page=anggota" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Anggota Cards -->
<div class="row g-4 mb-4">
    <?php if (pg_num_rows($anggota_result) > 0): ?>
        <?php while ($row = pg_fetch_assoc($anggota_result)): ?>
            <div class="col-md-3">
                <div class="card h-100" style="transition: all 0.3s;">
                    <div class="position-relative">
                        <?php if (!empty($row['foto_path']) && file_exists($row['foto_path'])): ?>
                            <img src="<?= htmlspecialchars($row['foto_path']) ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                                style="height: 250px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                <i class="fas fa-user text-muted" style="font-size: 5rem;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge <?= $row['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($row['status'] ?? 'Aktif') ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($row['nama_lengkap']) ?></h6>
                        <p class="card-text">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($row['nip_nim']) ?>
                            </small>
                            <?php if (!empty($row['jabatan'])): ?>
                                <small class="text-primary d-block mb-1">
                                    <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($row['jabatan']) ?>
                                </small>
                            <?php endif; ?>
                            <?php if (!empty($row['email'])): ?>
                                <small class="text-muted d-block">
                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($row['email']) ?>
                                </small>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between">
                            <a href="?page=anggota&edit=<?= $row['id_anggota'] ?>" class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-delete"
                                onclick="if(confirm('Apakah Anda yakin ingin menghapus anggota ini?')) window.location.href='?page=anggota&delete=<?= $row['id_anggota'] ?>'">
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
                        <i class="fas fa-users"></i>
                        <p>Tidak ada data anggota</p>
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
                <a class="page-link" href="?page=anggota&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=anggota&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=anggota&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalAnggota" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Anggota' : 'Tambah Anggota' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=anggota'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" value="<?= $edit_data ? $edit_data['id_anggota'] : '' ?>">

                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <label class="form-label fw-bold">Foto <span class="text-danger">*</span></label>
                            <div class="border rounded p-2 bg-light">
                                <?php if ($edit_data && !empty($edit_data['foto_path'])): ?>
                                    <img src="<?= htmlspecialchars($edit_data['foto_path']) ?>"
                                        class="img-fluid mb-2"
                                        style="max-height: 200px; object-fit: cover;"
                                        id="preview-foto">
                                <?php else: ?>
                                    <div id="preview-foto" class="mb-2">
                                        <i class="fas fa-user text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control form-control-sm" name="foto" accept="image/*" onchange="previewImage(this)" required>
                                <small class="text-muted">Max: 5MB</small>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_lengkap"
                                    value="<?= $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="nip_nim" class="form-label">NIP/NIM <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="nip_nim"
                                    value="<?= $edit_data ? htmlspecialchars($edit_data['nip_nim']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan"
                                    value="<?= $edit_data ? htmlspecialchars($edit_data['jabatan']) : '' ?>"
                                    placeholder="Contoh: Ketua, Sekretaris, Anggota">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                            value="<?= $edit_data ? htmlspecialchars($edit_data['email']) : '' ?>"
                            placeholder="email@example.com">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="urutan" class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" name="urutan"
                                value="<?= $edit_data ? $edit_data['urutan'] : '0' ?>" min="0">
                            <small class="text-muted">Angka lebih kecil akan tampil lebih dahulu</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Aktif" <?= ($edit_data && $edit_data['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Non-Aktif" <?= ($edit_data && $edit_data['status'] == 'Non-Aktif') ? 'selected' : '' ?>>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=anggota" class="btn btn-secondary">Batal</a>
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
            var myModal = new bootstrap.Modal(document.getElementById('modalAnggota'));
            myModal.show();
        });
    </script>
<?php endif; ?>

<script>
    function previewImage(input) {
        const preview = document.getElementById('preview-foto');

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
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .card-footer {
        padding: 0.75rem 1rem;
    }
</style>