<?php
// pages/arsip.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Ambil data edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_arsip = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM arsip WHERE id_arsip = $1", [$id_arsip]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_arsip = (int)$_GET['delete'];

    // Ambil file path sebelum hapus
    $file_query = pg_query_params($conn, "SELECT file_pdf_path FROM arsip WHERE id_arsip = $1", [$id_arsip]);

    if ($file_row = pg_fetch_assoc($file_query)) {
        // Hapus file jika ada
        if (file_exists($file_row['file_pdf_path'])) {
            unlink($file_row['file_pdf_path']);
        }

        // Hapus dari database
        $delete_result = pg_query_params($conn, "DELETE FROM arsip WHERE id_arsip = $1", [$id_arsip]);

        if ($delete_result) {
            $success_msg = "Arsip berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus arsip!";
        }
    }

    header("Location: ?page=arsip&success=" . urlencode($success_msg));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_arsip = isset($_POST['id_arsip']) ? (int)$_POST['id_arsip'] : 0;
    $judul_dokumen = trim($_POST['judul_dokumen']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = trim($_POST['kategori']);
    $penulis = trim($_POST['penulis']);
    $tanggal = trim($_POST['tanggal']);
    $id_admin = $_SESSION['id_admin'];

    // Validasi
    if (empty($judul_dokumen)) {
        $error_msg = "Judul dokumen harus diisi!";
    } else {
        $upload_dir = 'uploads/arsip/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_pdf_path = '';
        $ukuran_file_mb = 0;

        // Handle file upload
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0) {
            $allowed_ext = ['pdf'];
            $file_name = $_FILES['file_pdf']['name'];
            $file_size = $_FILES['file_pdf']['size'];
            $file_tmp = $_FILES['file_pdf']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                $error_msg = "Hanya file PDF yang diperbolehkan!";
            } else if ($file_size > 5 * 1024 * 1024) { // 5MB max
                $error_msg = "Ukuran file maksimal 5MB!";
            } else {
                $new_file_name = 'arsip_' . time() . '_' . uniqid() . '.pdf';
                $file_pdf_path = $upload_dir . $new_file_name;
                $ukuran_file_mb = round($file_size / (1024 * 1024), 2);

                if (!move_uploaded_file($file_tmp, $file_pdf_path)) {
                    $error_msg = "Gagal mengupload file!";
                    $file_pdf_path = '';
                }
            }
        }

        if (empty($error_msg)) {
            if ($id_arsip > 0) {
                // Update
                if (!empty($file_pdf_path)) {
                    // Ambil file lama dan hapus
                    $old_file_query = pg_query_params($conn, "SELECT file_pdf_path FROM arsip WHERE id_arsip = $1", [$id_arsip]);
                    if ($old_file_row = pg_fetch_assoc($old_file_query)) {
                        if (file_exists($old_file_row['file_pdf_path'])) {
                            unlink($old_file_row['file_pdf_path']);
                        }
                    }

                    $update_result = pg_query_params(
                        $conn,
                        "UPDATE arsip SET judul_dokumen = $1, deskripsi = $2, kategori = $3, file_pdf_path = $4, ukuran_file_mb = $5, penulis = $6, tanggal = $7, updated_at = NOW() WHERE id_arsip = $8",
                        [$judul_dokumen, $deskripsi, $kategori, $file_pdf_path, $ukuran_file_mb, $penulis, $tanggal, $id_arsip]
                    );
                } else {
                    $update_result = pg_query_params(
                        $conn,
                        "UPDATE arsip SET judul_dokumen = $1, deskripsi = $2, kategori = $3, penulis = $4, tanggal = $5, updated_at = NOW() WHERE id_arsip = $6",
                        [$judul_dokumen, $deskripsi, $kategori, $penulis, $tanggal, $id_arsip]
                    );
                }

                if ($update_result) {
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
                    $insert_result = pg_query_params(
                        $conn,
                        "INSERT INTO arsip (judul_dokumen, deskripsi, file_pdf_path, ukuran_file_mb, kategori, penulis, tanggal, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())",
                        [$judul_dokumen, $deskripsi, $file_pdf_path, $ukuran_file_mb, $kategori, $penulis, $tanggal, $id_admin]
                    );

                    if ($insert_result) {
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
        if (file_exists($download_row['file_pdf_path'])) {
            // Force download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($download_row['judul_dokumen']) . '.pdf"');
            header('Content-Length: ' . filesize($download_row['file_pdf_path']));
            readfile($download_row['file_pdf_path']);
            exit();
        }
    }
}

// Pesan success dari URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
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
    $where_clause = "WHERE judul_dokumen ILIKE $1 OR kategori ILIKE $1";
    $query_params[] = '%' . $search . '%';
}

// Total records
if (!empty($search)) {
    $count_query = pg_query_params($conn, "SELECT COUNT(*) as total FROM arsip $where_clause", $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM arsip");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Ambil data
if (!empty($search)) {
    $arsip_query = "SELECT a.*, ad.username 
                    FROM arsip a 
                    LEFT JOIN admin ad ON a.id_admin = ad.id_admin 
                    $where_clause
                    ORDER BY a.created_at DESC 
                    LIMIT $limit OFFSET $offset";
    $arsip_result = pg_query_params($conn, $arsip_query, $query_params);
} else {
    $arsip_query = "SELECT a.*, ad.username 
                    FROM arsip a 
                    LEFT JOIN admin ad ON a.id_admin = ad.id_admin 
                    ORDER BY a.created_at DESC 
                    LIMIT $limit OFFSET $offset";
    $arsip_result = pg_query($conn, $arsip_query);
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
        <h4 class="mb-1 fw-bold">Arsip PDF</h4>
        <small class="text-muted">Kelola dokumen dan file PDF</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalArsip">
        <i class="fas fa-plus me-2"></i>Tambah Arsip
    </button>
</div>

<!-- Search -->
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

<!-- Arsip Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th style="width: 30%;">Dokumen</th>
                <th style="width: 150px;" class="text-center">Penulis</th>
                <th style="width: 100px;" class="text-center">Kategori</th>
                <th style="width: 80px;" class="text-center">Ukuran</th>
                <th style="width: 100px;" class="text-center">Tanggal</th>
                <th style="width: 150px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (pg_num_rows($arsip_result) > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while ($row = pg_fetch_assoc($arsip_result)): ?>
                    <tr>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
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
                        <td class="text-center align-middle" style="vertical-align: middle;">
                            <div style="
        max-width: 160px;
        margin: 0 auto;
        text-align: center;
        word-wrap: break-word;
        line-height: 1.6;
        padding: 8px 4px;">
                                <small class="text-muted" style="font-size: 0.85rem;">
                                    <?php
                                    $penulis = $row['penulis'] ?? 'Admin Lab NCS';
                                    // Pisahkan berdasarkan enter/newline
                                    $penulis_array = array_filter(array_map('trim', explode("\n", $penulis)));

                                    // Jika lebih dari 2 nama, tampilkan 2 nama + "dan X lainnya"
                                    if (count($penulis_array) > 2) {
                                        echo htmlspecialchars($penulis_array[0]) . '<br>';
                                        echo htmlspecialchars($penulis_array[1]) . '<br>';
                                        echo '<span style="font-style: italic; color: #6c757d;">dan ' . (count($penulis_array) - 2) . ' lainnya</span>';
                                    } else {
                                        // Tampilkan semua jika 2 atau kurang
                                        echo nl2br(htmlspecialchars($penulis));
                                    }
                                    ?>
                                </small>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-category badge-arsip">
                                <?= htmlspecialchars($row['kategori'] ?? 'Umum') ?>
                            </span>
                        </td>
                        <td class="text-center"><?= sprintf('%.2f', (float)$row['ukuran_file_mb']) ?> MB</td>
                        <td class="text-center">
                            <small style="white-space: nowrap;">
                                <?= !empty($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : date('d M Y', strtotime($row['created_at'])) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="?page=arsip&download=<?= $row['id_arsip'] ?>" class="btn btn-sm btn-download" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="?page=arsip&edit=<?= $row['id_arsip'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus arsip \'<?= htmlspecialchars(addslashes($row['judul_dokumen'])) ?>\'?')"
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

<!-- Pagination -->
<?php if ($total_pages > 0): ?>
    <div class="d-flex justify-content-center align-items-center mt-4">
        <nav>
            <ul class="pagination mb-0">
                <!-- Previous Button -->
                <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=arsip&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        style="border-radius: 8px 0 0 8px; border-right: none;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>

                <?php
                // Pagination logic - show max 5 pages
                $start_page = max(1, $page_num - 2);
                $end_page = min($total_pages, $page_num + 2);

                // Adjust if at beginning
                if ($page_num <= 3) {
                    $end_page = min(5, $total_pages);
                }

                // Adjust if at end
                if ($page_num > $total_pages - 3) {
                    $start_page = max(1, $total_pages - 4);
                }

                // First page
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=arsip&p=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                        <a class="page-link" href="?page=arsip&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                            style="<?= $i == $page_num ? 'background-color: #2563eb; border-color: #2563eb; color: white;' : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Last page -->
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=arsip&p=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $total_pages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next Button -->
                <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=arsip&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        style="border-radius: 0 8px 8px 0; border-left: none;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- Modal Add/Edit -->
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
                            value="<?= $edit_data ? htmlspecialchars($edit_data['judul_dokumen']) : '' ?>"
                            maxlength="150" required>
                        <small class="text-muted">
                            <span id="judul-char-count">0</span>/150 karakter
                            <span id="judul-char-warning" class="text-danger fw-bold" style="display: none;"> - Maksimal 150 karakter tercapai!</span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="penulis" class="form-label">Penulis <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="penulis" name="penulis" rows="4" maxlength="200"
                            placeholder="Contoh:&#10;Dr. Ahmad Syahputra, M.Kom&#10;Dr. Budi Santoso, M.T"
                            required><?= $edit_data ? htmlspecialchars($edit_data['penulis']) : '' ?></textarea>
                        <small class="text-muted">Maksimal 200 karakter</small>
                        <span id="char-count">0</span>/200 karakter
                        <span id="char-warning" class="text-danger fw-bold" style="display: none;"> - Maksimal 200 karakter tercapai!</span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Publikasi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal"
                            value="<?= $edit_data ? htmlspecialchars($edit_data['tanggal']) : date('Y-m-d') ?>" required>
                        <small class="text-muted">Tanggal publikasi dokumen</small>
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
                        <small class="text-muted" id="file-size-label">Format: PDF | Maksimal: 5MB</small>

                        <?php if ($edit_data): ?>
                            <div class="mt-2 alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    File saat ini: <strong><?= htmlspecialchars($edit_data['judul_dokumen']) ?>.pdf</strong>
                                    (<?= sprintf('%.2f', (float)$edit_data['ukuran_file_mb']) ?> MB)
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
            var myModal = new bootstrap.Modal(document.getElementById('modalArsip'));
            myModal.show();
        });
    </script>
<?php endif; ?>

<script>
    // Handle delete button click
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (confirm("Yakin ingin menghapus arsip ini?")) {
                window.location.href = this.getAttribute('data-href');
            }
        });
    });

    // Validasi ukuran file & tampilkan ukuran real-time
    document.getElementById('file_pdf').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        const fileLabel = document.getElementById('file-size-label');

        if (file) {
            const fileSize = file.size;
            const fileSizeMB = (fileSize / (1024 * 1024)).toFixed(2);

            // Update label dengan ukuran file
            fileLabel.innerHTML = `Format: PDF | Maksimal: 5MB | <strong class="text-success">File terpilih: ${fileSizeMB} MB</strong>`;

            // Validasi ukuran
            if (fileSize > maxSize) {
                alert('Ukuran file terlalu besar!\n\nUkuran file: ' + fileSizeMB + ' MB\nMaksimal: 5 MB\n\nSilakan pilih file yang lebih kecil.');
                e.target.value = ''; // Reset input file
                fileLabel.innerHTML = 'Format: PDF | Maksimal: 5MB';
            }
        } else {
            // Reset label jika tidak ada file
            fileLabel.innerHTML = 'Format: PDF | Maksimal: 5MB';
        }
    });

    // Counter karakter JUDUL DOKUMEN dengan peringatan
    const judulInput = document.getElementById('judul_dokumen');
    const judulCharCount = document.getElementById('judul-char-count');
    const judulCharWarning = document.getElementById('judul-char-warning');

    function updateJudulCharCount() {
        const currentLength = judulInput.value.length;
        judulCharCount.textContent = currentLength;

        // Tampilkan warning jika sudah mencapai 150 karakter
        if (currentLength >= 150) {
            judulCharWarning.style.display = 'inline';
            judulInput.classList.add('is-invalid');
        } else {
            judulCharWarning.style.display = 'none';
            judulInput.classList.remove('is-invalid');
        }
    }

    // Update counter saat halaman dimuat (untuk mode edit)
    updateJudulCharCount();

    // Update counter saat mengetik
    judulInput.addEventListener('input', updateJudulCharCount);
    judulInput.addEventListener('keyup', updateJudulCharCount);

    // Counter karakter PENULIS dengan peringatan
    const penulisTextarea = document.getElementById('penulis');
    const charCount = document.getElementById('char-count');
    const charWarning = document.getElementById('char-warning');

    function updateCharCount() {
        const currentLength = penulisTextarea.value.length;
        charCount.textContent = currentLength;

        // Tampilkan warning jika sudah mencapai 200 karakter
        if (currentLength >= 200) {
            charWarning.style.display = 'inline';
            penulisTextarea.classList.add('is-invalid');
        } else {
            charWarning.style.display = 'none';
            penulisTextarea.classList.remove('is-invalid');
        }
    }

    // Update counter saat halaman dimuat (untuk mode edit)
    updateCharCount();

    // Update counter saat mengetik
    penulisTextarea.addEventListener('input', updateCharCount);
    penulisTextarea.addEventListener('keyup', updateCharCount);
</script>