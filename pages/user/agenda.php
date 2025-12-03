<?php
// pages/user/agenda.php

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;

// Pagination
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Build query
$where_conditions = [];
if (!empty($filter_status)) {
    if ($filter_status == 'akan_datang') {
        $where_conditions[] = "tanggal_mulai > CURRENT_DATE";
    } else if ($filter_status == 'berlangsung') {
        $where_conditions[] = "tanggal_mulai <= CURRENT_DATE AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURRENT_DATE)";
    } else if ($filter_status == 'selesai') {
        $where_conditions[] = "tanggal_selesai < CURRENT_DATE";
    }
}

if (!empty($filter_bulan)) {
    $where_conditions[] = "EXTRACT(MONTH FROM tanggal_mulai) = $filter_bulan";
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
$count_query = pg_query($conn, "SELECT COUNT(*) as total FROM agenda $where_clause");
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
$agenda_query = pg_query($conn, "SELECT * FROM agenda $where_clause ORDER BY tanggal_mulai DESC LIMIT $limit OFFSET $offset");

// Helper function to get status
function getAgendaStatus($tanggal_mulai, $tanggal_selesai) {
    $today = date('Y-m-d');
    
    if ($tanggal_mulai > $today) {
        return ['status' => 'Akan Datang', 'class' => 'badge-upcoming'];
    } else if ($tanggal_mulai <= $today && (empty($tanggal_selesai) || $tanggal_selesai >= $today)) {
        return ['status' => 'Sedang Berlangsung', 'class' => 'badge-ongoing'];
    } else {
        return ['status' => 'Selesai', 'class' => 'badge-completed'];
    }
}

// Indonesian month names
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=home">Beranda</a></li>
                <li class="breadcrumb-item active">Agenda</li>
            </ol>
        </nav>
        <h1>Agenda Lab NCS</h1>
        <p>Jadwal kegiatan dan event Lab Network and Cyber Security</p>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">
        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="filter-tabs">
                    <a href="?page=agenda" class="filter-tab <?= empty($filter_status) ? 'active' : '' ?>">
                        <i class="fas fa-th me-1"></i>Semua
                    </a>
                    <a href="?page=agenda&status=akan_datang" class="filter-tab <?= $filter_status == 'akan_datang' ? 'active' : '' ?>">
                        <i class="fas fa-clock me-1"></i>Akan Datang
                    </a>
                    <a href="?page=agenda&status=berlangsung" class="filter-tab <?= $filter_status == 'berlangsung' ? 'active' : '' ?>">
                        <i class="fas fa-play-circle me-1"></i>Berlangsung
                    </a>
                    <a href="?page=agenda&status=selesai" class="filter-tab <?= $filter_status == 'selesai' ? 'active' : '' ?>">
                        <i class="fas fa-check-circle me-1"></i>Selesai
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="agenda">
                    <?php if (!empty($filter_status)): ?>
                        <input type="hidden" name="status" value="<?= $filter_status ?>">
                    <?php endif; ?>
                    <select class="form-select" name="bulan" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        <?php foreach($bulan_indo as $num => $nama): ?>
                            <option value="<?= $num ?>" <?= $filter_bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Agenda List -->
        <?php if (pg_num_rows($agenda_query) > 0): ?>
            <div class="row g-4">
                <?php while($agenda = pg_fetch_assoc($agenda_query)): ?>
                    <?php
                    $status_info = getAgendaStatus($agenda['tanggal_mulai'], $agenda['tanggal_selesai']);
                    $tgl_mulai = new DateTime($agenda['tanggal_mulai']);
                    $tgl_selesai = !empty($agenda['tanggal_selesai']) ? new DateTime($agenda['tanggal_selesai']) : null;
                    ?>
                    <div class="col-md-6">
                        <div class="card-custom">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="agenda-date" style="font-size: 0.9rem;">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <strong><?= $tgl_mulai->format('d M Y') ?></strong>
                                        <?php if ($tgl_selesai): ?>
                                            - <strong><?= $tgl_selesai->format('d M Y') ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-custom <?= $status_info['class'] ?>">
                                        <?= $status_info['status'] ?>
                                    </span>
                                </div>
                                
                                <h5 class="fw-bold mb-3"><?= htmlspecialchars($agenda['judul_agenda']) ?></h5>
                                
                                <?php if (!empty($agenda['lokasi'])): ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <?= htmlspecialchars($agenda['lokasi']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($agenda['deskripsi'])): ?>
                                    <p class="text-muted" style="text-align: justify;">
                                        <?= nl2br(htmlspecialchars($agenda['deskripsi'])) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Countdown untuk agenda akan datang -->
                                <?php if ($status_info['status'] == 'Akan Datang'): ?>
                                    <?php
                                    $now = new DateTime();
                                    $diff = $now->diff($tgl_mulai);
                                    $days_left = $diff->days;
                                    ?>
                                    <div class="alert alert-info mb-0 mt-3">
                                        <i class="fas fa-hourglass-half me-2"></i>
                                        <strong><?= $days_left ?> hari</strong> lagi
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=agenda&p=<?= $page_num - 1 ?><?= !empty($filter_status) ? '&status=' . $filter_status : '' ?><?= !empty($filter_bulan) ? '&bulan=' . $filter_bulan : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=agenda&p=<?= $i ?><?= !empty($filter_status) ? '&status=' . $filter_status : '' ?><?= !empty($filter_bulan) ? '&bulan=' . $filter_bulan : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=agenda&p=<?= $page_num + 1 ?><?= !empty($filter_status) ? '&status=' . $filter_status : '' ?><?= !empty($filter_bulan) ? '&bulan=' . $filter_bulan : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">Belum ada agenda</h4>
                <p class="text-muted">
                    <?php if (!empty($filter_status)): ?>
                        Tidak ada agenda dengan status yang dipilih
                    <?php else: ?>
                        Agenda kegiatan akan ditampilkan di sini
                    <?php endif; ?>
                </p>
                <?php if (!empty($filter_status) || !empty($filter_bulan)): ?>
                    <a href="?page=agenda" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>