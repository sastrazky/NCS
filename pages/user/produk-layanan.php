<?php 

// Ambil data produk layanan
$query = pg_query($conn, "SELECT * FROM produk_layanan ORDER BY id_produk_layanan DESC");
?>

<div class="container py-5">
    <h2 class="text-center fw-bold mb-4">Produk & Layanan</h2>
    <p class="text-center text-muted mb-5">
        Berikut merupakan daftar produk dan layanan yang tersedia di laboratorium kami.
    </p>

    <div class="row g-4">

        <?php if (pg_num_rows($query) > 0): ?>
            <?php while ($row = pg_fetch_assoc($query)): ?>

                <?php
                // ---------- START: media path logic (tiru dari galeri) ----------
                // Di DB kolomnya: gambar_path, isinya misal: "uploads/produk/produk_1764....jpg"
                $original_media = !empty($row['gambar_path']) ? $row['gambar_path'] : '';

                // Prefix "admin/" karena file fisik ada di folder admin/
                $media_path = $original_media ? "admin/" . $original_media : "assets/images/no-image.jpg";

                // Jika file fisik tidak ditemukan, fallback ke no-image
                if (!file_exists($media_path)) {
                    $media_path = "assets/images/no-image.jpg";
                }
                // ---------- END: media path logic ----------
                ?>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">

                        <?php if (!empty($row['tipe_media']) && $row['tipe_media'] == 'Video' && !empty($row['gambar_path']) && file_exists($media_path)): ?>
                            <div class="position-relative" style="height:220px;">
                                <video style="width: 100%; height: 100%; object-fit: cover;">
                                    <source src="<?= htmlspecialchars($media_path) ?>" type="video/mp4">
                                </video>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <i class="fas fa-play-circle" style="font-size: 3rem; color: white; opacity: 0.8;"></i>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Default: anggap foto (atau fallback no-image) -->
                            <img src="<?= htmlspecialchars($media_path) ?>" 
                                 alt="<?= htmlspecialchars($row['judul']) ?>"
                                 class="card-img-top"
                                 style="height:220px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body">
                            <span class="badge bg-primary px-3 py-2 mb-2">
                                <?= htmlspecialchars($row['kategori']) ?>
                            </span>

                            <h5 class="fw-bold"><?= htmlspecialchars($row['judul']) ?></h5>
                            <p class="text-muted" style="font-size: 0.9rem;">
                                <?= nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 120))) ?>...
                            </p>

                            <!-- Tombol detail -->
                            <button class="btn btn-primary-custom w-100 mt-2" data-bs-toggle="modal"
                                    data-bs-target="#detail<?= $row['id_produk_layanan']; ?>">
                                Lihat Detail
                            </button>
                        </div>

                    </div>
                </div>

                <!-- MODAL DETAIL -->
                <div class="modal fade" id="detail<?= $row['id_produk_layanan']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title fw-bold"><?= htmlspecialchars($row['judul']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <?php if (!empty($row['tipe_media']) && $row['tipe_media'] == 'Video' && !empty($row['gambar_path']) && file_exists($media_path)): ?>
                                    <div class="position-relative mb-3">
                                        <video style="width: 100%; height: 100%; object-fit: cover;" controls>
                                            <source src="<?= htmlspecialchars($media_path) ?>" type="video/mp4">
                                        </video>
                                    </div>

                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($media_path) ?>" 
                                         alt="<?= htmlspecialchars($row['judul']) ?>"
                                         class="w-100 rounded mb-3">
                                <?php endif; ?>

                                <p class="text-muted">
                                    <strong>Kategori:</strong> 
                                    <?= htmlspecialchars($row['kategori']); ?>
                                </p>

                                <p style="white-space: pre-line;">
                                    <?= htmlspecialchars($row['deskripsi']); ?>
                                </p>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END MODAL -->

            <?php endwhile; ?>

        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa fa-folder-open text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">Belum ada produk layanan.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
