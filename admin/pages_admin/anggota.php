<?php
// pages/anggota.php (Modifikasi untuk detail_anggota + Multi-Link JSON + NIDN + NIP Only)

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
// Ambil data detail juga jika mode edit
$edit_detail_data = null; 
if (isset($_GET['edit'])) {
    $id_anggota = (int)$_GET['edit'];
    // Mengambil data utama anggota
    $edit_query = pg_query_params($conn, "SELECT * FROM anggota WHERE id_anggota = $1", [$id_anggota]);
    $edit_data = pg_fetch_assoc($edit_query);

    // MENGAMBIL DATA DETAIL TAMBAHAN
    $detail_query = pg_query_params($conn, "SELECT * FROM detail_anggota WHERE id_anggota = $1", [$id_anggota]);
    $edit_detail_data = pg_fetch_assoc($detail_query);
}

// Handle Delete (TIDAK ADA PERUBAHAN SIGNIFIKAN KARENA ON DELETE CASCADE SUDAH DITERAPKAN DI DB)
if (isset($_GET['delete'])) {
    $id_anggota = (int)$_GET['delete'];
    
    // --- LANGKAH 1 (DELETE): Ambil Judul Item untuk Log ---
    $file_query = pg_query_params($conn, "SELECT nama_lengkap, foto_path FROM anggota WHERE id_anggota = $1", [$id_anggota]);
    $item_title = 'Anggota ID ' . $id_anggota; // Default title

    if ($file_row = pg_fetch_assoc($file_query)) {
        $item_title = $file_row['nama_lengkap']; // Ambil nama lengkap untuk log

        // Delete file if exists
        if (!empty($file_row['foto_path']) && file_exists($file_row['foto_path'])) {
            @unlink($file_row['foto_path']); // Gunakan @ agar error path tidak menghentikan proses
        }

        // Delete from database
        // Karena ada ON DELETE CASCADE, menghapus dari `anggota` akan otomatis menghapus dari `detail_anggota`.
        $delete_result = pg_query_params($conn, "DELETE FROM anggota WHERE id_anggota = $1", [$id_anggota]);

        if ($delete_result) {
            // --- LANGKAH 2 (DELETE): Catat Log Aktivitas ---
            $safe_item_title = pg_escape_literal($conn, $item_title);
            $log_query = "
                INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                VALUES ($id_admin, 'anggota', $safe_item_title, 'dihapus')
            ";
            pg_query($conn, $log_query);
            // ------------------------------------------------
            $success_msg = "Anggota berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus anggota!";
        }
    } else {
        $error_msg = "Gagal mengambil data anggota untuk dihapus!";
    }

    header("Location: ?page=anggota" . (!empty($success_msg) ? "&success=" . urlencode($success_msg) : "") . (!empty($error_msg) ? "&error=" . urlencode($error_msg) : ""));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota = isset($_POST['id_anggota']) ? (int)$_POST['id_anggota'] : 0;
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nip = trim($_POST['nip']); 
    $nidn = trim($_POST['nidn']); 
    $jabatan = trim($_POST['jabatan']);
    $email = trim($_POST['email']);
    $urutan = (int)$_POST['urutan'];
    $status = $_POST['status'];
    $id_admin = $_SESSION['id_admin'];

    // Data Detail Anggota Baru
    $keahlian = trim($_POST['keahlian']);
    $pendidikan = trim($_POST['pendidikan']);
    $sertifikasi = trim($_POST['sertifikasi']);
    $mata_kuliah = trim($_POST['mata_kuliah']);
    
    // --- KELOLA MULTI-LINK JSON ---
    $link_publikasi_array = [];
    if (isset($_POST['link_nama']) && isset($_POST['link_url']) && is_array($_POST['link_nama'])) {
        foreach ($_POST['link_nama'] as $index => $nama) {
            $url = trim($_POST['link_url'][$index] ?? '');
            $nama = trim($nama);

            // Hanya simpan jika nama dan URL tidak kosong
            if (!empty($nama) && !empty($url)) {
                $link_publikasi_array[] = [
                    'nama' => $nama,
                    'url' => $url
                ];
            }
        }
    }
    // Encode array menjadi JSON string untuk disimpan di database
    $link_publikasi = json_encode($link_publikasi_array);
    // ------------------------------
    
    // Validation
    if (empty($nama_lengkap) || empty($nip)) { // Menggunakan $nip
        $error_msg = "Nama lengkap dan NIP harus diisi!";
    } else {
        $upload_dir = 'uploads/anggota/'; // Path untuk DB dan file_exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $foto_path = '';
        $new_foto_uploaded = false;

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
                } else {
                    $new_foto_uploaded = true;
                }
            }
        } else {
            // Jika tidak ada upload baru, ambil path lama saat Edit
            if ($id_anggota > 0) {
                 $old_data_query = pg_query_params($conn, "SELECT foto_path, nip FROM anggota WHERE id_anggota = $1", [$id_anggota]);
                 $old_data = pg_fetch_assoc($old_data_query);
                 $foto_path = $old_data['foto_path'] ?? '';
            } else {
                // FOTO WAJIB SAAT INSERT (jika tidak ada file diupload, ini akan menjadi error)
                $error_msg = "Foto wajib diunggah saat menambah anggota!";
            }
            
            // Jika insert dan foto tidak ada, kita keluar
            if ($id_anggota == 0 && empty($foto_path)) {
                // error_msg sudah diset di atas
            }
        }


        if (empty($error_msg)) {
            if ($id_anggota > 0) {
                // Update
                $update_query_fields = "nama_lengkap = $1, nip = $2, nidn = $3, jabatan = $4, email = $5, urutan = $6, status = $7, updated_at = NOW()";
                // Urutan parameter: 1=nama, 2=nip, 3=nidn, 4=jabatan, 5=email, 6=urutan, 7=status, 8=id_anggota
                $params = [$nama_lengkap, $nip, $nidn, $jabatan, $email, $urutan, $status, $id_anggota]; 
                
                if ($new_foto_uploaded) {
                    // Jika ada foto baru, tambahkan foto_path sebagai $8 dan id_anggota sebagai $9
                    $update_query_fields .= ", foto_path = $" . (count($params) + 1);
                    $params = [$nama_lengkap, $nip, $nidn, $jabatan, $email, $urutan, $status, $foto_path, $id_anggota];
                }
                
                // Eksekusi Update Anggota Utama
                $update_result = pg_query_params(
                    $conn,
                    "UPDATE anggota SET $update_query_fields WHERE id_anggota = $" . count($params),
                    $params
                );

                // --- UPDATE DETAIL ANGGOTA ---
                $detail_exists_query = pg_query_params($conn, "SELECT id_detail FROM detail_anggota WHERE id_anggota = $1", [$id_anggota]);
                
                if (pg_num_rows($detail_exists_query) > 0) {
                    // Jika detail sudah ada, lakukan UPDATE
                    $detail_update_params = [
                        $keahlian, $pendidikan, $sertifikasi, $mata_kuliah, $link_publikasi, $id_anggota
                    ];
                    pg_query_params(
                        $conn,
                        "UPDATE detail_anggota SET keahlian = $1, pendidikan = $2, sertifikasi = $3, mata_kuliah = $4, link_publikasi = $5 WHERE id_anggota = $6",
                        $detail_update_params
                    );
                } else {
                    // Jika detail belum ada, lakukan INSERT
                    $detail_insert_params = [
                        $id_anggota, $keahlian, $pendidikan, $sertifikasi, $mata_kuliah, $link_publikasi
                    ];
                    pg_query_params(
                        $conn,
                        "INSERT INTO detail_anggota (id_anggota, keahlian, pendidikan, sertifikasi, mata_kuliah, link_publikasi) VALUES ($1, $2, $3, $4, $5, $6)",
                        $detail_insert_params
                    );
                }
                // -----------------------------


                if ($update_result) {
                    // --- LOGGING UPDATE ---
                    $safe_item_title = pg_escape_literal($conn, $nama_lengkap);
                    $log_query = "
                        INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                        VALUES ($id_admin, 'anggota', $safe_item_title, 'diperbarui')
                    ";
                    pg_query($conn, $log_query);
                    // ----------------------
                    header("Location: ?page=anggota&success=" . urlencode("Anggota berhasil diperbarui!"));
                    exit();
                } else {
                    $error_msg = "Gagal memperbarui anggota!";
                }
            } else {
                // Insert
                
                // Mulai Transaksi untuk memastikan kedua tabel terisi
                pg_query($conn, "BEGIN");
                
                // Insert Anggota Utama
                $insert_result = pg_query_params(
                    $conn,
                    "INSERT INTO anggota (nama_lengkap, nip, nidn, jabatan, email, foto_path, urutan, status, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW()) RETURNING id_anggota",
                    [$nama_lengkap, $nip, $nidn, $jabatan, $email, $foto_path, $urutan, $status, $id_admin]
                );
                
                if ($insert_result) {
                    $new_id_anggota = pg_fetch_result($insert_result, 0, 'id_anggota');

                    // --- INSERT DETAIL ANGGOTA ---
                    $detail_insert_params = [
                        $new_id_anggota, $keahlian, $pendidikan, $sertifikasi, $mata_kuliah, $link_publikasi
                    ];
                    $detail_insert_result = pg_query_params(
                        $conn,
                        "INSERT INTO detail_anggota (id_anggota, keahlian, pendidikan, sertifikasi, mata_kuliah, link_publikasi) VALUES ($1, $2, $3, $4, $5, $6)",
                        $detail_insert_params
                    );
                    // -----------------------------

                    if ($detail_insert_result) {
                        // Commit Transaksi
                        pg_query($conn, "COMMIT");

                        // --- LOGGING INSERT ---
                        $safe_item_title = pg_escape_literal($conn, $nama_lengkap);
                        $log_query = "
                            INSERT INTO aktivitas_log (id_admin, item_type, item_title, action)
                            VALUES ($id_admin, 'anggota', $safe_item_title, 'ditambahkan')
                        ";
                        pg_query($conn, $log_query);
                        // ----------------------
                        
                        header("Location: ?page=anggota&success=" . urlencode("Anggota berhasil ditambahkan!"));
                        exit();
                    } else {
                        // Rollback jika insert detail gagal
                        pg_query($conn, "ROLLBACK");
                        $error_msg = "Gagal menambahkan detail anggota!";
                    }
                } else {
                    // Rollback jika insert anggota utama gagal
                    pg_query($conn, "ROLLBACK");
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
    $where_clause = "WHERE nama_lengkap ILIKE $1 OR nip ILIKE $1 OR jabatan ILIKE $1"; 
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
$anggota_query_base = "SELECT *, nip AS nip FROM anggota $where_clause ORDER BY urutan ASC, nama_lengkap ASC LIMIT $limit OFFSET $offset";

if (!empty($search)) {
    // Clone parameters for LIMIT and OFFSET
    $exec_params = $query_params;
    $anggota_result = pg_query_params($conn, $anggota_query_base, $exec_params);
} else {
    $anggota_result = pg_query($conn, $anggota_query_base);
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
        <h4 class="mb-1 fw-bold">Data Anggota</h4>
        <small class="text-muted">Kelola data anggota NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalAnggota">
        <i class="fas fa-plus me-2"></i>Tambah Anggota
    </button>
</div>

<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="anggota">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari anggota (nama, NIP, jabatan)..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary-custom" type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="?page=anggota" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
            <small class="text-muted d-block mt-2"></small>
        </div>
</div>

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
                                <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($row['nip']) ?>
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

<div class="modal fade" id="modalAnggota" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Anggota' : 'Tambah Anggota' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=anggota'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" value="<?= $edit_data ? $edit_data['id_anggota'] : '' ?>">
                    
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="data-utama-tab" data-bs-toggle="tab" data-bs-target="#data-utama" type="button" role="tab" aria-controls="data-utama" aria-selected="true">Data Utama</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="data-detail-tab" data-bs-toggle="tab" data-bs-target="#data-detail" type="button" role="tab" aria-controls="data-detail" aria-selected="false">Data Detail</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content pt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="data-utama" role="tabpanel" aria-labelledby="data-utama-tab">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <label class="form-label fw-bold">Foto <span class="text-danger">*</span></label>
                                    <div class="border rounded p-2 bg-light text-center">
                                        <img id="preview-foto"
                                            src="<?= ($edit_data && !empty($edit_data['foto_path']) && file_exists($edit_data['foto_path']))
                                                     ? htmlspecialchars($edit_data['foto_path'])
                                                     : 'assets/img/no-user.png' ?>"
                                            class="img-fluid mb-2"
                                            style="max-height: 200px; object-fit: cover;">
                                        <input type="file" class="form-control form-control-sm"
                                            name="foto" accept="image/*" onchange="previewImage(this)"
                                            <?= $edit_data ? '' : 'required' ?>>
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
                                        <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nip"
                                            value="<?= $edit_data ? htmlspecialchars($edit_data['nip'] ?? '') : '' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nidn" class="form-label">NIDN</label>
                                        <input type="text" class="form-control" name="nidn"
                                            value="<?= $edit_data ? htmlspecialchars($edit_data['nidn'] ?? '') : '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="jabatan" class="form-label">Jabatan</label>
                                        <select name="jabatan" class="form-control" style="max-height: 120px; overflow-y: auto;">
                                            <option value="">-- Pilih Jabatan --</option>
                                            <option value="Peneliti" <?php if ($edit_data && $edit_data['jabatan'] == 'Peneliti') echo 'selected'; ?>>Peneliti</option>
                                            <option value="Ketua Lab" <?php if ($edit_data && $edit_data['jabatan'] == 'Ketua Lab') echo 'selected'; ?>>Ketua Lab</option>
                                            </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?= $edit_data ? htmlspecialchars($edit_data['email']) : '' ?>"
                                            placeholder="email@example.com">
                                    </div>
                                </div>
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
                        
                        <div class="tab-pane fade" id="data-detail" role="tabpanel" aria-labelledby="data-detail-tab">
                            <div class="mb-3">
                                <label for="keahlian" class="form-label">Keahlian (Pisahkan dengan koma/baris baru)</label>
                                <textarea class="form-control" name="keahlian" rows="3"><?= $edit_detail_data ? htmlspecialchars($edit_detail_data['keahlian']) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="pendidikan" class="form-label">Pendidikan (Riwayat pendidikan - Pisahkan dengan baris baru)</label>
                                <textarea class="form-control" name="pendidikan" rows="3"><?= $edit_detail_data ? htmlspecialchars($edit_detail_data['pendidikan']) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="sertifikasi" class="form-label">Sertifikasi (Pisahkan dengan baris baru)</label>
                                <textarea class="form-control" name="sertifikasi" rows="2"><?= $edit_detail_data ? htmlspecialchars($edit_detail_data['sertifikasi']) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="mata_kuliah" class="form-label">Mata Kuliah (yang diampu/dikuasai - Pisahkan dengan baris baru)</label>
                                <textarea class="form-control" name="mata_kuliah" rows="2"><?= $edit_detail_data ? htmlspecialchars($edit_detail_data['mata_kuliah']) : '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Link Publikasi (Nama & URL)</label>
                                <div id="link-publikasi-container">
                                    <?php
                                    $links_array = [];
                                    if ($edit_data && !empty($edit_detail_data['link_publikasi'])) {
                                        // Coba decode JSON yang sudah ada
                                        $decoded_links = json_decode($edit_detail_data['link_publikasi'], true);
                                        if (is_array($decoded_links)) {
                                            $links_array = $decoded_links;
                                        }
                                    }
                                    
                                    // Render existing links if editing
                                    if (!empty($links_array)):
                                        foreach ($links_array as $link):
                                    ?>
                                        <div class="row g-2 mb-2 link-item">
                                            <div class="col-4">
                                                <input type="text" class="form-control" name="link_nama[]" placeholder="Nama (e.g., SINTA)" value="<?= htmlspecialchars($link['nama'] ?? '') ?>">
                                            </div>
                                            <div class="col-7">
                                                <input type="url" class="form-control" name="link_url[]" placeholder="URL Lengkap" value="<?= htmlspecialchars($link['url'] ?? '') ?>">
                                            </div>
                                            <div class="col-1 d-flex align-items-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-link"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-link">
                                    <i class="fas fa-plus me-1"></i> Tambah Link
                                </button>
                                <small class="text-muted d-block mt-2">Simpan sebagai format Nama Link dan URL-nya.</small>
                            </div>
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
            // Cek apakah bootstrap dimuat
            if (typeof bootstrap !== 'undefined') {
                var modalElement = document.getElementById('modalAnggota');
                // Pastikan modal belum ditampilkan
                if (!modalElement.classList.contains('show')) {
                    var myModal = new bootstrap.Modal(modalElement);
                    myModal.show();
                }
            } else {
                // Fallback sederhana jika bootstrap.js tidak dimuat
                console.error("Bootstrap JS not loaded. Cannot show modal.");
            }
        });
    </script>
<?php endif; ?>

<script>
    function previewImage(input) {
        const preview = document.getElementById('preview-foto');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('link-publikasi-container');
        const addButton = document.getElementById('add-link');

        // Function to create a new link input row
        function createLinkRow(nama = '', url = '') {
            const row = document.createElement('div');
            row.classList.add('row', 'g-2', 'mb-2', 'link-item');
            row.innerHTML = `
                <div class="col-4">
                    <input type="text" class="form-control" name="link_nama[]" placeholder="Nama (e.g., SINTA)" value="${nama}">
                </div>
                <div class="col-7">
                    <input type="url" class="form-control" name="link_url[]" placeholder="URL Lengkap" value="${url}">
                </div>
                <div class="col-1 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-danger remove-link"><i class="fas fa-times"></i></button>
                </div>
            `;
            // Add removal listener to the new button
            row.querySelector('.remove-link').addEventListener('click', function() {
                row.remove();
            });
            return row;
        }

        // Add button listener
        addButton.addEventListener('click', function () {
            container.appendChild(createLinkRow());
        });

        // Initialize removal listeners for existing (PHP generated) rows
        container.querySelectorAll('.remove-link').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.link-item').remove();
            });
        });

        // Add one empty row if no data exists (for new entry)
        const isEditMode = document.querySelector('input[name="id_anggota"]').value > 0;
        if (!isEditMode && container.children.length === 0) {
            container.appendChild(createLinkRow());
        }
    });
</script>

<style>
    .btn-primary-custom {
        color: #fff;
        background-color: #0d6efd; /* Warna default Bootstrap Primary */
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
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
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