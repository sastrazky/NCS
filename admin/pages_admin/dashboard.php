<?php
// pages_admin/dashboard.php

// Pastikan koneksi ($conn) sudah tersedia dari index.php
if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.");
}

// ===============================================
// 1. Get statistics from database (STAT CARDS)
// ===============================================
$stats = [];

// Total Profil
$profil_result = pg_query($conn, "SELECT COUNT(*) as total FROM profil");
$stats['profil'] = pg_fetch_assoc($profil_result)['total'];

// Total Anggota
$anggota_result = pg_query($conn, "SELECT COUNT(*) as total FROM anggota");
$stats['anggota'] = pg_fetch_assoc($anggota_result)['total'];

// Total Layanan
$layanan_result = pg_query($conn, "SELECT COUNT(*) as total FROM layanan");
$stats['layanan'] = pg_fetch_assoc($layanan_result)['total'];

// Total Sarana Prasarana
$sarana_result = pg_query($conn, "SELECT COUNT(*) as total FROM sarana_prasarana");
$stats['sarana'] = pg_fetch_assoc($sarana_result)['total'];

// Total Agenda
$agenda_result = pg_query($conn, "SELECT COUNT(*) as total FROM agenda");
$stats['agenda'] = pg_fetch_assoc($agenda_result)['total'];

// Total Galeri
$galeri_result = pg_query($conn, "SELECT COUNT(*) as total FROM galeri");
$stats['galeri'] = pg_fetch_assoc($galeri_result)['total'];

// Total Arsip PDF
$arsip_result = pg_query($conn, "SELECT COUNT(*) as total FROM arsip");
$stats['arsip'] = pg_fetch_assoc($arsip_result)['total'];

// Total Link Eksternal
$link_result = pg_query($conn, "SELECT COUNT(*) as total FROM link_eksternal");
$stats['link'] = pg_fetch_assoc($link_result)['total'];

// ===============================================
// 2. Get recent activities (AKTIVITAS LOG)
// ===============================================

$activity_query = "
    SELECT 
        al.item_type AS type,
        al.item_title AS title,
        al.action_time AS date,
        al.action AS action,
        a.nama_lengkap AS admin_name
    FROM
        aktivitas_log al
    LEFT JOIN
        admin a ON al.id_admin = a.id_admin
    ORDER BY
        al.action_time DESC 
    LIMIT 10
";

$activities_result = pg_query($conn, $activity_query);
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Profil</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['profil']) ?></h3>
                    <small class="text-success">
                        <i class="fas fa-check-circle"></i> Aktif
                    </small>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Anggota</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['anggota']) ?></h3>
                    <small class="text-primary">
                        <i class="fas fa-users"></i> Member
                    </small>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Layanan</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['layanan']) ?></h3>
                    <small class="text-success">
                        <i class="fas fa-box"></i> Layanan
                    </small>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Sarana Prasarana</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['sarana']) ?></h3>
                    <small class="text-warning">
                        <i class="fas fa-warehouse"></i> Items
                    </small>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Agenda</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['agenda']) ?></h3>
                    <small class="text-danger">
                        <i class="fas fa-calendar"></i> Event
                    </small>
                </div>
                <div class="stat-icon pink">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Galeri</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['galeri']) ?></h3>
                    <small class="text-warning">
                        <i class="fas fa-images"></i> Foto
                    </small>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-images"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Arsip PDF</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['arsip']) ?></h3>
                    <small class="text-primary">
                        <i class="fas fa-file-pdf"></i> Dokumen
                    </small>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-file-pdf"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Link Eksternal</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['link']) ?></h3>
                    <small class="text-info">
                        <i class="fas fa-link"></i> Links
                    </small>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-link"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="activity-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1 fw-bold">Aktivitas Terkini</h5>
            <small class="text-muted">10 konten terbaru yang ditambahkan, diperbarui, atau dihapus</small>
        </div>
        <span class="badge bg-primary"><?= pg_num_rows($activities_result) ?> aktivitas</span>
    </div>
    
    <?php if (pg_num_rows($activities_result) > 0): ?>
        <?php while($activity = pg_fetch_assoc($activities_result)): ?>
            <?php
                // Set badge and dot color based on type
                $badge_class = 'badge-' . $activity['type'];
                $dot_color = '';
                switch($activity['type']) {
                    case 'profil': $dot_color = 'blue'; break;
                    case 'anggota': $dot_color = 'purple'; break;
                    case 'layanan': $dot_color = 'green'; break; 
                    case 'sarana': $dot_color = 'orange'; break;
                    case 'agenda': $dot_color = 'pink'; break;
                    case 'galeri': $dot_color = 'blue'; break;
                    case 'arsip': $dot_color = 'blue'; break;
                    case 'link': $dot_color = 'green'; break;
                    default: $dot_color = 'secondary';
                }
                
                // Format date
                if (!empty($activity['date'])) {
                    $date = new DateTime($activity['date']);
                    $now = new DateTime();
                    $diff = $now->diff($date);
                    
                    if ($diff->days == 0) {
                        if ($diff->h == 0) {
                            $time_ago = $diff->i . ' menit yang lalu';
                        } else {
                            $time_ago = $diff->h . ' jam yang lalu';
                        }
                    } else if ($diff->days == 1) {
                        $time_ago = 'Kemarin';
                    } else if ($diff->days < 7) {
                        $time_ago = $diff->days . ' hari yang lalu';
                    } else {
                        $time_ago = $date->format('d M Y');
                    }
                } else {
                    $time_ago = '-';
                }
            ?>
            <div class="activity-item">
                <div class="activity-dot <?= $dot_color ?>"></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                            <small class="text-muted">
                                <span class="badge badge-category <?= $badge_class ?>">
                                    <?= strtoupper(str_replace('_', ' ', $activity['type'])) ?> 
                                </span>
                                <span class="ms-2"><?= ucfirst($activity['action']) ?></span>
                                <?php if (!empty($activity['admin_name'])): ?>
                                    <span class="ms-2 text-dark">oleh <?= htmlspecialchars($activity['admin_name']) ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <small class="text-muted"><?= $time_ago ?></small>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada aktivitas</p>
        </div>
    <?php endif; ?>
</div>