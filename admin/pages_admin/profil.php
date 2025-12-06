<?php
// pages/profil.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Check if profil exists
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil_data = pg_fetch_assoc($profil_query);

// Get data for edit (same as view since profil is single record)
$edit_data = null;
if (isset($_GET['edit']) && $profil_data) {
    $edit_data = $profil_data;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sejarah = trim($_POST['sejarah']);
    $visi = trim($_POST['visi']);
    $misi = trim($_POST['misi']);
    $id_admin = $_SESSION['id_admin'];
    
    // Inisialisasi logo_path dengan path lama (jika ada)
    $logo_path = $profil_data['logo_path'] ?? '';
    $new_logo_uploaded = false;
    
    // Handle logo upload HANYA jika file baru diupload dan tidak ada error
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0 && !empty($_FILES['logo']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['logo']['name'];
        $file_size = $_FILES['logo']['size'];
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $error_msg = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan!";
        } else if ($file_size > 5 * 1024 * 1024) { // 5MB max
            $error_msg = "Ukuran file maksimal 5MB!";
        } else {
            $upload_dir = '../uploads/profil/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_file_name = 'logo_' . time() . '.' . $file_ext;
            $temp_logo_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $temp_logo_path)) {
                $logo_path = $temp_logo_path; // Ganti path lama dengan path baru
                $new_logo_uploaded = true;
            } else {
                $error_msg = "Gagal mengupload logo!";
                $logo_path = $profil_data['logo_path'] ?? ''; // Pertahankan path lama jika upload gagal
            }
        }
    }
    
    if (empty($error_msg)) {
        if ($profil_data) {
            // Update existing profil
            
            if ($new_logo_uploaded) {
                // Hapus logo lama jika upload logo baru berhasil
                if (!empty($profil_data['logo_path']) && file_exists($profil_data['logo_path'])) {
                    unlink($profil_data['logo_path']);
                }
                
                // Update dengan logo baru
                $update_result = pg_query_params($conn, 
                    "UPDATE profil SET sejarah = $1, visi = $2, misi = $3, logo_path = $4, id_admin = $5, updated_at = NOW() WHERE id_profil = $6",
                    [$sejarah, $visi, $misi, $logo_path, $id_admin, $profil_data['id_profil']]
                );
            } else {
                // Update tanpa mengubah logo_path (menggunakan path lama yang sudah diinisialisasi)
                $update_result = pg_query_params($conn, 
                    "UPDATE profil SET sejarah = $1, visi = $2, misi = $3, id_admin = $4, updated_at = NOW() WHERE id_profil = $5",
                    [$sejarah, $visi, $misi, $id_admin, $profil_data['id_profil']]
                );
            }
            
            if ($update_result) {
                header("Location: ?page=profil&success=" . urlencode("Profil berhasil diperbarui!"));
                exit();
            } else {
                $error_msg = "Gagal memperbarui profil!";
            }
        } else {
            // Insert new profil (Hanya jalankan jika ada logo_path baru atau kosong jika tidak ada)
            $insert_result = pg_query_params($conn, 
                "INSERT INTO profil (sejarah, visi, misi, logo_path, id_admin, updated_at) VALUES ($1, $2, $3, $4, $5, NOW())",
                [$sejarah, $visi, $misi, $logo_path, $id_admin]
            );
            
            if ($insert_result) {
                header("Location: ?page=profil&success=" . urlencode("Profil berhasil ditambahkan!"));
                exit();
            } else {
                $error_msg = "Gagal menambahkan profil!";
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

// Refresh profil data after operations
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil_data = pg_fetch_assoc($profil_query);
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
        <h4 class="mb-1 fw-bold">Profil Organisasi</h4>
        <small class="text-muted">Kelola informasi profil NCS</small>
    </div>
    <?php if ($profil_data): ?>
        <a href="?page=profil&edit=1" class="btn btn-primary-custom">
            <i class="fas fa-edit me-2"></i>Edit Profil
        </a>
    <?php else: ?>
        <a href="?page=profil&edit=1" class="btn btn-primary-custom">
            <i class="fas fa-plus me-2"></i>Tambah Profil
        </a>
    <?php endif; ?>
</div>

<?php if (!$profil_data && !isset($_GET['edit'])): ?>
    <!-- Empty State -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <i class="fas fa-building"></i>
                <h5 class="mt-3">Belum Ada Profil</h5>
                <p class="text-muted">Silakan tambahkan profil organisasi terlebih dahulu</p>
                <a href="?page=profil&edit=1" class="btn btn-primary-custom mt-3">
                    <i class="fas fa-plus me-2"></i>Tambah Profil
                </a>
            </div>
        </div>
    </div>
<?php elseif (isset($_GET['edit'])): ?>
<!-- Form Edit/Add -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-edit me-2 text-primary"></i>
            <?= $profil_data ? 'Edit Profil' : 'Tambah Profil' ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">

                <!-- Logo Section -->
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <label class="form-label fw-bold">Logo Organisasi</label>
                        <div class="border rounded p-3 bg-light">

                            <!-- FIX PREVIEW WRAPPER -->
                            <div id="preview-wrapper" class="mb-3 text-center">
                                <?php if ($profil_data && !empty($profil_data['logo_path'])): ?>
                                    <img src="<?= htmlspecialchars($profil_data['logo_path']) ?>" 
                                        alt="Logo" 
                                        class="img-fluid" 
                                        style="max-height: 200px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="text-muted">
                                        <i class="fas fa-image" style="font-size: 4rem;"></i>
                                        <p class="mt-2">Belum ada logo</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- FILE INPUT -->
                            <input type="file" 
                                   class="form-control" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <small class="text-muted d-block mt-2">Format: JPG, PNG, GIF | Max: 5MB</small>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="col-md-8">

                    <!-- Sejarah -->
                    <div class="mb-4">
                        <label for="sejarah" class="form-label fw-bold">
                            <i class="fas fa-history text-primary me-2"></i>Sejarah
                        </label>
                        <textarea class="form-control" 
                                  id="sejarah" 
                                  name="sejarah" 
                                  rows="5" 
                                  placeholder="Tuliskan sejarah organisasi..."><?= $profil_data ? htmlspecialchars($profil_data['sejarah']) : '' ?></textarea>
                    </div>

                    <!-- Visi -->
                    <div class="mb-4">
                        <label for="visi" class="form-label fw-bold">
                            <i class="fas fa-eye text-primary me-2"></i>Visi
                        </label>
                        <textarea class="form-control" 
                                  id="visi" 
                                  name="visi" 
                                  rows="4" 
                                  placeholder="Tuliskan visi organisasi..."><?= $profil_data ? htmlspecialchars($profil_data['visi']) : '' ?></textarea>
                    </div>

                    <!-- Misi -->
                    <div class="mb-4">
                        <label for="misi" class="form-label fw-bold">
                            <i class="fas fa-bullseye text-primary me-2"></i>Misi
                        </label>
                        <textarea class="form-control" 
                                  id="misi" 
                                  name="misi" 
                                  rows="6" 
                                  placeholder="Tuliskan misi organisasi (gunakan enter untuk pemisah)..."><?= $profil_data ? htmlspecialchars($profil_data['misi']) : '' ?></textarea>
                        <small class="text-muted">Tips: Pisahkan setiap misi dengan baris baru</small>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-top pt-3 mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="?page=profil" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Simpan Profil
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
    <!-- View Mode -->
    <div class="row g-4">
        <!-- Logo Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-image text-primary me-2"></i>Logo
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($profil_data['logo_path'])): ?>
                        <img src="<?= htmlspecialchars($profil_data['logo_path']) ?>" 
                             alt="Logo NCS" 
                             class="img-fluid rounded" 
                             style="max-height: 300px; object-fit: contain;">
                    <?php else: ?>
                        <div class="empty-state" style="padding: 2rem;">
                            <i class="fas fa-image"></i>
                            <p class="text-muted mt-2">Logo belum tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>Informasi Profil
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Sejarah -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-history me-2"></i>Sejarah
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <?php if (!empty($profil_data['sejarah'])): ?>
                                <p class="mb-0" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(htmlspecialchars($profil_data['sejarah'])) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted mb-0"><em>Belum ada sejarah</em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Visi -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-eye me-2"></i>Visi
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <?php if (!empty($profil_data['visi'])): ?>
                                <p class="mb-0" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(htmlspecialchars($profil_data['visi'])) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted mb-0"><em>Belum ada visi</em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Misi -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-bullseye me-2"></i>Misi
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <?php if (!empty($profil_data['misi'])): ?>
                                <?php
                                $misi_list = explode("\n", $profil_data['misi']);
                                $misi_list = array_filter(array_map('trim', $misi_list));
                                ?>
                                <?php if (count($misi_list) > 0): ?>
                                    <ol class="mb-0" style="line-height: 1.8;">
                                        <?php foreach ($misi_list as $misi_item): ?>
                                            <?php if (!empty($misi_item)): ?>
                                                <li class="mb-2"><?= htmlspecialchars($misi_item) ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="text-muted mb-0"><em>Belum ada misi</em></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0"><em>Belum ada misi</em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Last Updated -->
                    <?php if (!empty($profil_data['updated_at'])): ?>
                        <div class="border-top pt-3 mt-4">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Terakhir diperbarui: <?= date('d F Y, H:i', strtotime($profil_data['updated_at'])) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function previewImage(input) {
    const wrapper = document.getElementById('preview-wrapper');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            wrapper.innerHTML = `
                <img src="${e.target.result}" 
                     class="img-fluid" 
                     style="max-height: 200px; object-fit: contain;" 
                     alt="Preview">
            `;
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>


<style>
.card-header {
    border-bottom: 2px solid #f1f5f9;
}

.bg-light {
    background-color: #f8fafc !important;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-label.fw-bold {
    color: #1e293b;
    font-size: 0.95rem;
}

ol li {
    padding-left: 0.5rem;
}
</style>