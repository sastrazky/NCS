<?php
// pages/user/layanan.php

// Variabel untuk status
$success_msg = '';
$error_msg = '';

// PROSES FORM - Pindahkan ke atas sebelum ada output HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama_lengkap']);
    $email      = trim($_POST['email']);
    $hp         = trim($_POST['nomor_hp']);
    $judul      = trim($_POST['judul_pesan']);
    $kategori   = trim($_POST['kategori']);
    $pesan      = trim($_POST['isi_pesan']);

    if (empty($nama) || empty($email) || empty($judul) || empty($pesan)) {
        $error_msg = "Harap isi semua field yang wajib.";
    } else {
        $sql = "INSERT INTO layanan 
                (nama_lengkap, email, nomor_hp, judul_pesan, kategori, isi_pesan, created_at)
                VALUES ($1, $2, $3, $4, $5, $6, NOW())";
        $result = pg_query_params($conn, $sql, [$nama, $email, $hp, $judul, $kategori, $pesan]);

        if ($result) {
            // Gunakan JavaScript redirect untuk menghindari header error
            echo "<script>window.location.href='?page=layanan&success=1';</script>";
            exit;
        } else {
            $error_msg = "Gagal mengirim pesan. Silakan coba lagi.";
        }
    }
}

// Cek parameter GET untuk pesan sukses
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_msg = "Pesan berhasil dikirim! Kami akan segera menghubungi Anda.";
}
?>

<!-- Page Header Section -->
<section class="page-header-sarana">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item active">Layanan</li>
            </ol>
        </nav>
        <h1>Form Layanan</h1>
        <p>Silahkan kirim permintaan layanan Anda melalui form di bawah ini.</p>
    </div>
</section>

<!-- Form Section -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Success Message -->
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" 
                         style="border-radius: 12px; border-left: 4px solid #28a745;">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Berhasil!</strong> <?= htmlspecialchars($success_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" 
                         style="border-radius: 12px; border-left: 4px solid #dc3545;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error!</strong> <?= htmlspecialchars($error_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card shadow-lg border-0" 
                     style="border-radius: 20px; transition: all 0.3s;">
                    <div class="card-body p-5">
                        <form method="POST" novalidate>
                            
                            <!-- Row 1: Nama Lengkap & Email -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="nama_lengkap" class="form-label fw-semibold" 
                                           style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                        name="nama_lengkap" 
                                        id="nama_lengkap" 
                                        class="form-control form-control-lg"
                                        style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;"
                                        placeholder="Rani Putri"
                                        value="<?= htmlspecialchars($nama ?? '') ?>" 
                                        required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold" 
                                           style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control form-control-lg"
                                           style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;"
                                           placeholder="rani@example.com"
                                           value="<?= htmlspecialchars($email ?? '') ?>" 
                                           required>
                                </div>
                            </div>

                            <!-- Row 2: Nomor HP & Judul Pesan -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="nomor_hp" class="form-label fw-semibold" 
                                           style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                        Nomor HP
                                    </label>
                                    <input type="number" 
                                           name="nomor_hp" 
                                           id="nomor_hp" 
                                           class="form-control form-control-lg"
                                           style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;"
                                           placeholder="081234567890"
                                           value="<?= htmlspecialchars($hp ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="judul_pesan" class="form-label fw-semibold" 
                                           style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                        Judul Pesan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="judul_pesan" 
                                           id="judul_pesan" 
                                           class="form-control form-control-lg"
                                           style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;"
                                           placeholder="Permintaan Layanan Keamanan Jaringan"
                                           value="<?= htmlspecialchars($judul ?? '') ?>" 
                                           required>
                                </div>
                            </div>

                            <!-- Row 3: Kategori -->
                            <div class="mb-4">
                                <label for="kategori" class="form-label fw-semibold" 
                                       style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                    Kategori
                                </label>
                                <select name="kategori" 
                                        id="kategori" 
                                        class="form-select form-select-lg"
                                        style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;">
                                        <option value="" disabled selected>Pilih kategori</option>
                                    <?php
                                    $categories = ["Laporan Masalah", "Konsultasi", "Saran & Masukan", "Lainnya"];
                                    foreach ($categories as $cat) {
                                        $sel = (isset($kategori) && $kategori === $cat) ? 'selected' : '';
                                        echo "<option value=\"$cat\" $sel>$cat</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Row 4: Isi Pesan -->
                            <div class="mb-4">
                                <label for="isi_pesan" class="form-label fw-semibold" 
                                       style="color: #495057; margin-bottom: 0.5rem; font-size: 0.95rem;">
                                    Isi Pesan 
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(Max. 300 Karakter)</small>
                                </label>
                                <textarea name="isi_pesan" 
                                          id="isi_pesan" 
                                          class="form-control form-control-lg" 
                                          rows="6"
                                          maxlength="300" 
                                          style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; font-size: 1rem;"
                                          placeholder="Tulis kebutuhan keamanan jaringan atau layanan yang Anda butuhkan"
                                          required><?= htmlspecialchars($pesan ?? '') ?></textarea>
                                <div class="form-text">
                                    <span id="charCount">0</span>/300 karakter
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="btn btn-primary btn-lg w-100 shadow-sm" 
                                    style="padding: 15px 40px; font-size: 1.1rem; font-weight: 600; border-radius: 10px; transition: all 0.3s;">
                                <i class="fas fa-paper-plane me-2"></i>
                                Kirim Pesan
                            </button>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
/* Focus Effects */
.form-control:focus, 
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(59, 89, 152, 0.1);
}

/* Placeholder Styling */
.form-control::placeholder,
.form-select::placeholder {
    font-style: italic;
    color: #b8b8b8;
}

/* Button Hover Effect */
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 89, 152, 0.3);
}

/* Alert Animation */
.alert {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Character Counter for Textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('isi_pesan');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        // Initial count
        charCount.textContent = textarea.value.length;
        
        // Update on input
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Form Validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Harap isi semua field yang wajib!');
        }
    });
    
    // Auto-hide success/error alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>