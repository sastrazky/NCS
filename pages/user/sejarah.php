<?php
// pages/user/sejarah.php
$profil_query = pg_query($conn, "SELECT * FROM profil LIMIT 1");
$profil = pg_fetch_assoc($profil_query);
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item"><a href="?page=sejarah">Profil</a></li>
                <li class="breadcrumb-item active">Sejarah</li>
            </ol>
        </nav>
        <h1>Sejarah Lab NCS</h1>
        <p>Perjalanan Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">
        <div class="row">
        
            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if ($profil): ?>
                    <h3 class="text-primary mb-4">
                            <i class="fas fa-history me-2"></i>Sejarah Lab Network and Cyber Security
                        </h3>
                        <div class="content-box">
                        <?php if (!empty($profil['logo_path'])): ?>
                            <div class="text-center mb-4">
                                <img src="<?= htmlspecialchars($profil['logo_path']) ?>" 
                                     alt="Logo NCS" 
                                     class="img-fluid" 
                                     style="max-height: 300px;">
                            </div>
                        <?php endif; ?>
                        
                        <div style="text-align: justify; line-height: 2;">
                            <?= nl2br(htmlspecialchars($profil['sejarah'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi sejarah belum tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>  