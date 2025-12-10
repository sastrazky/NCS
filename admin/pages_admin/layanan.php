<?php
// pages_admin/layanan.php
if (!isset($_SESSION['id_admin'])) die("Akses ditolak!");

// --- 1. LOGIKA HAPUS (DELETE) ---
$success_msg = '';
$error_msg = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    
    // Query Hapus
    $delete_query = pg_query_params($conn, "DELETE FROM layanan WHERE id_layanan = $1", [$id_to_delete]);
    
    if ($delete_query) {
        $success_msg = "Pesan layanan berhasil dihapus!";
        // Redirect untuk membersihkan parameter action/id dari URL
        header("Location: index.php?page=layanan&success=" . urlencode($success_msg));
        exit;
    } else {
        $error_msg = "Gagal menghapus pesan: " . pg_last_error($conn);
    }
}

// --- 2. FILTER & SEARCH SETUP (LOGIKA TETAP) ---
// Ambil pesan sukses/error dari redirect
if (isset($_GET['success'])) {
    $success_msg = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_msg = htmlspecialchars($_GET['error']);
}

$kategori_filter = $_GET['kategori'] ?? 'semua';
$search = $_GET['search'] ?? "";

$where_conditions = [];
$params = [];
$param_count = 1;

if ($kategori_filter !== 'semua') {
    $where_conditions[] = "kategori = $" . $param_count++;
    $params[] = $kategori_filter;
}

if ($search != "") {
    $where_conditions[] = "(nama_lengkap ILIKE $" . $param_count . 
                             " OR email ILIKE $" . $param_count . 
                             " OR judul_pesan ILIKE $" . $param_count . ")";
    $params[] = "%$search%";
}

$where_sql = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// --- 3. EXECUTE QUERY (LOGIKA TETAP) ---
if (count($params) > 0) {
    $result = pg_query_params(
        $conn,
        "SELECT * FROM layanan $where_sql ORDER BY created_at DESC",
        $params
    );
} else {
    $result = pg_query($conn, "SELECT * FROM layanan ORDER BY created_at DESC");
}

$categories = ["Permintaan Layanan", "Pengaduan", "Saran", "Lainnya"];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Daftar Pesan Layanan</h4>
        <small class="text-muted">Kelola pesan dari pengguna.</small>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= $error_msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4" style="border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="layanan">
            <div class="row g-3">
                
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari nama, email, atau judul pesan..." 
                               value="<?= htmlspecialchars($search) ?>">
                        
                        <button class="btn btn-primary-custom" type="submit" title="Cari Pesan">
                            <i class="fas fa-search"></i>
                        </button>

                        <?php if ($search || $kategori_filter !== 'semua'): ?>
                            <a href="?page=layanan" class="btn btn-secondary" title="Reset Filter"><i class="fas fa-redo"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div> 
                        <select class="form-select" name="kategori" onchange="this.form.submit()">
                            <option value="semua" <?= $kategori_filter === 'semua' ? 'selected' : '' ?>>▼ Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $kategori_filter === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 120px;">Nama</th>
                <th style="width: 160px;">Email</th>
                <th style="width: 100px;">No. HP</th>
                <th style="width: 180px;">Judul Pesan</th>
                <th style="width: 100px;">Kategori</th>
                <th style="width: 180px;">Isi Pesan</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 80px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (pg_num_rows($result) > 0):
                while ($row = pg_fetch_assoc($result)): 
            ?>
                <tr data-row-id="<?= $row['id_layanan'] ?>">
                    <td><?= $no++ ?></td>
                    
                    <td>
                        <span class="fw-semibold text-truncate d-block" title="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                        </span>
                    </td>

                    <td>
                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($row['email']) ?>">
                            <?= htmlspecialchars($row['email']) ?>
                        </small>
                    </td>
                    
                    <td>
                        <?= htmlspecialchars($row['nomor_hp'] ?: '-') ?>
                    </td>
                    
                    <td>
                        <strong class="text-truncate d-block" style="font-size: 0.85rem;" title="<?= htmlspecialchars($row['judul_pesan']) ?>">
                            <?php 
                            $judul = htmlspecialchars($row['judul_pesan']);
                            echo strlen($judul) > 30 ? substr($judul, 0, 30) . '...' : $judul;
                            ?>
                        </strong>
                    </td>
                    
                    <td>
                        <span class="badge badge-category badge-layanan">
                            <?= htmlspecialchars($row['kategori'] ?: 'Tidak ada') ?>
                        </span>
                    </td>
                    
                    <td>
                        <div class="message-preview-clean" title="<?= htmlspecialchars($row['isi_pesan']) ?>">
                            <?php 
                            // Batasi teks menjadi 200 karakter
                            $preview_text = htmlspecialchars($row['isi_pesan']);
                            $max_length = 200;

                            if (strlen($preview_text) > $max_length) {
                                $preview_text = substr($preview_text, 0, $max_length) . '...';
                            }
                            echo nl2br($preview_text);
                            ?>
                        </div>
                    </td>
                    
                    <td>
                        <small class="d-block text-muted" style="font-size: 0.75rem; font-weight: 500;">
                            <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </small>
                    </td>
                    
                    <td class="text-center">
                        <a href="?page=layanan&action=delete&id=<?= $row['id_layanan'] ?>" 
                           class="btn btn-sm btn-danger-custom" 
                           style="padding: 5px 8px; font-size: 0.75rem; border-radius: 6px;"
                           onclick="return confirm('❗ Anda yakin ingin MENGHAPUS permanen pesan ini?')"
                           title="Hapus Pesan">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
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

<style>
/* --- CSS DIKOMPAK KAN DAN FOKUS PADA TABEL --- */

/* Reset dan Base */
.card { border-radius: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.btn { border-radius: 8px; font-weight: 500; transition: all 0.3s ease; }
.form-select, .form-control { border: 2px solid #e9ecef; border-radius: 10px; padding: 10px 15px; }

/* Custom Button (Hapus) */
.btn-primary-custom {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}
.btn-primary-custom:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.btn-danger-custom {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}
.btn-danger-custom:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: white;
}

/* Table Styling - Lebih Kompak */
.table { font-size: 0.8rem; margin-bottom: 0; }
.table thead th { 
    font-weight: 600; text-transform: uppercase; 
    font-size: 0.7rem; letter-spacing: 0.2px; 
    padding: 0.6rem 0.4rem; border: none; 
    background-color: #f8f9fa;
}
.table tbody td { 
    padding: 0.6rem 0.4rem; vertical-align: middle; 
    border-bottom: 1px solid #f0f0f0;
}
.table-hover tbody tr:hover { background-color: #f8f9fa; }

/* Message Preview - Fixed Layout */
.message-preview-clean {
    max-height: 70px; 
    overflow-y: auto;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.75rem;
    line-height: 1.4;
    word-wrap: break-word;
    word-break: break-word;
    display: flex;
    align-items: flex-start;
    margin: 0;
}
.message-preview-clean * {
    margin: 0 !important;
    padding: 0 !important;
}
.message-preview-clean::-webkit-scrollbar { width: 4px; }
.message-preview-clean::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

/* Badge & Empty State */
.badge-category { padding: 3px 6px; border-radius: 5px; font-size: 0.65rem; }
.badge-layanan { 
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); 
    color: white; 
}
.empty-state { text-align: center; padding: 2rem 1rem; color: #6c757d; }
.empty-state i { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5; }
</style>