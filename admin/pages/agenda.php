<?php
// pages/agenda.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_agenda = (int)$_GET['edit'];
    $edit_query = pg_query_params($conn, "SELECT * FROM agenda WHERE id_agenda = $1", [$id_agenda]);
    $edit_data = pg_fetch_assoc($edit_query);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_agenda = (int)$_GET['delete'];
    
    $delete_result = pg_query_params($conn, "DELETE FROM agenda WHERE id_agenda = $1", [$id_agenda]);
    
    if ($delete_result) {
        $success_msg = "Agenda berhasil dihapus!";
    } else {
        $error_msg = "Gagal menghapus agenda!";
    }
    
    header("Location: ?page=agenda&success=" . urlencode($success_msg));
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_agenda = isset($_POST['id_agenda']) ? (int)$_POST['id_agenda'] : 0;
    $judul_agenda = trim($_POST['judul_agenda']);
    $deskripsi = trim($_POST['deskripsi']);
    $lokasi = trim($_POST['lokasi']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $id_admin = $_SESSION['id_admin'];
    
    // Validation
    if (empty($judul_agenda) || empty($tanggal_mulai)) {
        $error_msg = "Judul agenda dan tanggal mulai harus diisi!";
    } else if (!empty($tanggal_selesai) && $tanggal_selesai < $tanggal_mulai) {
        $error_msg = "Tanggal selesai tidak boleh lebih awal dari tanggal mulai!";
    } else {
        if ($id_agenda > 0) {
            // Update
            $update_result = pg_query_params($conn, 
                "UPDATE agenda SET judul_agenda = $1, deskripsi = $2, lokasi = $3, tanggal_mulai = $4, tanggal_selesai = $5, updated_at = NOW() WHERE id_agenda = $6",
                [$judul_agenda, $deskripsi, $lokasi, $tanggal_mulai, $tanggal_selesai, $id_agenda]
            );
            
            if ($update_result) {
                header("Location: ?page=agenda&success=" . urlencode("Agenda berhasil diperbarui!"));
                exit();
            } else {
                $error_msg = "Gagal memperbarui agenda!";
            }
        } else {
            // Insert
            $insert_result = pg_query_params($conn, 
                "INSERT INTO agenda (judul_agenda, deskripsi, lokasi, tanggal_mulai, tanggal_selesai, id_admin, created_at) VALUES ($1, $2, $3, $4, $5, $6, NOW())",
                [$judul_agenda, $deskripsi, $lokasi, $tanggal_mulai, $tanggal_selesai, $id_admin]
            );
            
            if ($insert_result) {
                header("Location: ?page=agenda&success=" . urlencode("Agenda berhasil ditambahkan!"));
                exit();
            } else {
                $error_msg = "Gagal menambahkan agenda!";
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

// Pagination
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(judul_agenda ILIKE $$param_count OR lokasi ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

if (!empty($filter_bulan)) {
    $param_count++;
    $where_conditions[] = "EXTRACT(MONTH FROM tanggal_mulai) = $$param_count";
    $query_params[] = $filter_bulan;
}

if (!empty($filter_status)) {
    if ($filter_status == 'akan_datang') {
        $where_conditions[] = "tanggal_mulai > CURRENT_DATE";
    } else if ($filter_status == 'berlangsung') {
        $where_conditions[] = "tanggal_mulai <= CURRENT_DATE AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURRENT_DATE)";
    } else if ($filter_status == 'selesai') {
        $where_conditions[] = "tanggal_selesai < CURRENT_DATE";
    }
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
if (count($query_params) > 0) {
    $count_query_str = "SELECT COUNT(*) as total FROM agenda $where_clause";
    $count_query_str = str_replace('$param_count', '$' . $param_count, $count_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $count_query_str = str_replace('$param_count', '$' . $i, $count_query_str);
    }
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM agenda $where_clause");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $agenda_query_str = "SELECT * FROM agenda $where_clause ORDER BY tanggal_mulai DESC LIMIT $limit OFFSET $offset";
    $agenda_query_str = str_replace('$param_count', '$' . $param_count, $agenda_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $agenda_query_str = str_replace('$param_count', '$' . $i, $agenda_query_str);
    }
    $agenda_result = pg_query_params($conn, $agenda_query_str, $query_params);
} else {
    $agenda_result = pg_query($conn, "SELECT * FROM agenda $where_clause ORDER BY tanggal_mulai DESC LIMIT $limit OFFSET $offset");
}

// Helper function to get status
function getAgendaStatus($tanggal_mulai, $tanggal_selesai) {
    $today = date('Y-m-d');
    
    if ($tanggal_mulai > $today) {
        return ['status' => 'Akan Datang', 'class' => 'bg-info'];
    } else if ($tanggal_mulai <= $today && (empty($tanggal_selesai) || $tanggal_selesai >= $today)) {
        return ['status' => 'Sedang Berlangsung', 'class' => 'bg-success'];
    } else {
        return ['status' => 'Selesai', 'class' => 'bg-secondary'];
    }
}

// Indonesian month names
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
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
        <h4 class="mb-1 fw-bold">Agenda Kegiatan</h4>
        <small class="text-muted">Kelola jadwal agenda dan kegiatan NCS</small>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalAgenda">
        <i class="fas fa-plus me-2"></i>Tambah Agenda
    </button>
</div>

<!-- Search & Filter -->
<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="agenda">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Cari agenda..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="bulan">
                        <option value="">Semua Bulan</option>
                        <?php foreach($bulan_indo as $num => $nama): ?>
                            <option value="<?= $num ?>" <?= $filter_bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="akan_datang" <?= $filter_status == 'akan_datang' ? 'selected' : '' ?>>Akan Datang</option>
                            <option value="berlangsung" <?= $filter_status == 'berlangsung' ? 'selected' : '' ?>>Sedang Berlangsung</option>
                            <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_bulan) || !empty($filter_status)): ?>
                            <a href="?page=agenda" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Agenda List -->
<div class="row g-4 mb-4">
    <?php if (pg_num_rows($agenda_result) > 0): ?>
        <?php while($row = pg_fetch_assoc($agenda_result)): ?>
            <?php
            $status_info = getAgendaStatus($row['tanggal_mulai'], $row['tanggal_selesai']);
            $tgl_mulai = new DateTime($row['tanggal_mulai']);
            $tgl_selesai = !empty($row['tanggal_selesai']) ? new DateTime($row['tanggal_selesai']) : null;
            ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="card-title fw-bold mb-0"><?= htmlspecialchars($row['judul_agenda']) ?></h6>
                            <span class="badge <?= $status_info['class'] ?>">
                                <?= $status_info['status'] ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <small class="text-muted">
                                    <strong><?= $tgl_mulai->format('d M Y') ?></strong>
                                    <?php if ($tgl_selesai): ?>
                                        - <strong><?= $tgl_selesai->format('d M Y') ?></strong>
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <?php if (!empty($row['lokasi'])): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <small class="text-muted"><?= htmlspecialchars($row['lokasi']) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($row['deskripsi'])): ?>
                            <p class="card-text text-muted" style="font-size: 0.9rem;">
                                <?= htmlspecialchars(substr($row['deskripsi'], 0, 150)) ?>
                                <?= strlen($row['deskripsi']) > 150 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between">
                            <a href="?page=agenda&edit=<?= $row['id_agenda'] ?>" class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-delete" 
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus agenda ini?')) window.location.href='?page=agenda&delete=<?= $row['id_agenda'] ?>'">
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
                        <i class="fas fa-calendar"></i>
                        <p>Tidak ada data agenda</p>
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
                <a class="page-link" href="?page=agenda&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_bulan) ? '&bulan=' . urlencode($filter_bulan) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=agenda&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_bulan) ? '&bulan=' . urlencode($filter_bulan) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=agenda&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_bulan) ? '&bulan=' . urlencode($filter_bulan) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalAgenda" tabindex="-1" <?= $edit_data ? 'data-bs-show="true"' : '' ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $edit_data ? 'Edit Agenda' : 'Tambah Agenda' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='?page=agenda'"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_agenda" value="<?= $edit_data ? $edit_data['id_agenda'] : '' ?>">
                    
                    <div class="mb-3">
                        <label for="judul_agenda" class="form-label">Judul Agenda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_agenda" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['judul_agenda']) : '' ?>" 
                               placeholder="Contoh: Workshop Cybersecurity" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="4" 
                                  placeholder="Deskripsikan agenda secara detail..."><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" 
                               value="<?= $edit_data ? htmlspecialchars($edit_data['lokasi']) : '' ?>" 
                               placeholder="Contoh: Lab Komputer Gedung A">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" 
                                   value="<?= $edit_data ? $edit_data['tanggal_mulai'] : '' ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" 
                                   value="<?= $edit_data ? $edit_data['tanggal_selesai'] : '' ?>">
                            <small class="text-muted">Kosongkan jika agenda 1 hari</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?page=agenda" class="btn btn-secondary">Batal</a>
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
        var myModal = new bootstrap.Modal(document.getElementById('modalAgenda'));
        myModal.show();
    });
</script>
<?php endif; ?>