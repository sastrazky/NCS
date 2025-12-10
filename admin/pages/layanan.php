<?php
// admin/pages/layanan.php

// Handle success/error messages
$success_msg = '';
$error_msg = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_layanan = (int)$_GET['delete'];
    
    $delete_result = pg_query_params($conn, "DELETE FROM layanan WHERE id_layanan = $1", [$id_layanan]);
    
    if ($delete_result) {
        $success_msg = "Pesan berhasil dihapus!";
    } else {
        $error_msg = "Gagal menghapus pesan!";
    }
    
    header("Location: ?page=layanan&success=" . urlencode($success_msg));
    exit();
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

// Pagination
$limit = 15;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

$where_conditions = [];
$query_params = [];
$param_count = 0;

if (!empty($search)) {
    $param_count++;
    $where_conditions[] = "(nama_lengkap ILIKE $$param_count OR email ILIKE $$param_count OR judul_pesan ILIKE $$param_count)";
    $query_params[] = '%' . $search . '%';
}

if (!empty($filter_kategori) && $filter_kategori != 'semua') {
    $param_count++;
    $where_conditions[] = "kategori = $$param_count";
    $query_params[] = $filter_kategori;
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records
if (count($query_params) > 0) {
    $count_query_str = "SELECT COUNT(*) as total FROM layanan $where_clause";
    $count_query_str = str_replace('$param_count', '$' . $param_count, $count_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $count_query_str = str_replace('$param_count', '$' . $i, $count_query_str);
    }
    $count_query = pg_query_params($conn, $count_query_str, $query_params);
} else {
    $count_query = pg_query($conn, "SELECT COUNT(*) as total FROM layanan");
}
$total_records = pg_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get data
if (count($query_params) > 0) {
    $layanan_query_str = "SELECT * FROM layanan $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $layanan_query_str = str_replace('$param_count', '$' . $param_count, $layanan_query_str);
    for ($i = $param_count - 1; $i >= 1; $i--) {
        $layanan_query_str = str_replace('$param_count', '$' . $i, $layanan_query_str);
    }
    $layanan_result = pg_query_params($conn, $layanan_query_str, $query_params);
} else {
    $layanan_result = pg_query($conn, "SELECT * FROM layanan ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

// Categories
$categories = ["Permintaan Layanan", "Pengaduan", "Saran", "Lainnya"];
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
        <h4 class="mb-1 fw-bold">Daftar Pesan Layanan</h4>
        <small class="text-muted">Kelola pesan dari pengguna</small>
    </div>
    <div>
        <span class="badge bg-primary" style="font-size: 1rem; padding: 0.5rem 1rem;">
            <i class="fas fa-envelope me-2"></i><?= number_format($total_records) ?> Pesan
        </span>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="layanan">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama, email, atau judul pesan..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" name="kategori" onchange="this.form.submit()">
                            <option value="semua" <?= $filter_kategori == 'semua' || empty($filter_kategori) ? 'selected' : '' ?>>Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $filter_kategori == $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary-custom" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_kategori)): ?>
                            <a href="?page=layanan" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>Judul Pesan</th>
                <th>Kategori</th>
                <th>Isi Pesan</th>
                <th>Tanggal</th>
                <th style="width: 100px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (pg_num_rows($layanan_result) > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while($row = pg_fetch_assoc($layanan_result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong>
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="fas fa-envelope me-1"></i>
                                <?= htmlspecialchars($row['email']) ?>
                            </small>
                        </td>
                        <td>
                            <?php if (!empty($row['nomor_hp'])): ?>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($row['nomor_hp']) ?>
                                </span>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 0.9rem;">
                                <?= htmlspecialchars($row['judul_pesan']) ?>
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-category badge-link">
                                <?= htmlspecialchars($row['kategori'] ?: 'Tidak ada') ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-height: 80px; overflow-y: auto; font-size: 0.85rem;" 
                                 title="<?= htmlspecialchars($row['isi_pesan']) ?>">
                                <?php
                                $preview = htmlspecialchars($row['isi_pesan']);
                                if (strlen($preview) > 150) {
                                    $preview = substr($preview, 0, 150) . '...';
                                }
                                echo nl2br($preview);
                                ?>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                                <small><?= date('H:i', strtotime($row['created_at'])) ?></small>
                            </small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-delete" 
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus pesan ini?')) window.location.href='?page=layanan&delete=<?= $row['id_layanan'] ?>'" 
                                    title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada pesan layanan</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page_num <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=layanan&p=<?= $page_num - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                    <a class="page-link" href="?page=layanan&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page_num >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=layanan&p=<?= $page_num + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_kategori) ? '&kategori=' . urlencode($filter_kategori) : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>