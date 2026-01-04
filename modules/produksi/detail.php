<?php
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: ?module=produksi&action=data');
    exit();
}

// Fetch data
$sql = "SELECT p.*, u.fullname as petugas 
        FROM produksi p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='?module=produksi&action=data';</script>";
    exit();
}

// Format data
$jenis_peternakan_text = ucwords(str_replace('_', ' ', $data['jenis_peternakan']));
$tanggal_produksi_formatted = date('d/m/Y', strtotime($data['tanggal_produksi']));
$created_at_formatted = date('d/m/Y H:i', strtotime($data['created_at']));

// Production summary
$produksi_items = [];
if ($data['produksi_susu'] > 0) $produksi_items[] = number_format($data['produksi_susu'], 1) . ' L Susu';
if ($data['produksi_daging'] > 0) $produksi_items[] = number_format($data['produksi_daging'], 1) . ' kg Daging';
if ($data['produksi_telur'] > 0) $produksi_items[] = number_format($data['produksi_telur'], 0) . ' butir Telur';
$produksi_text = !empty($produksi_items) ? implode(', ', $produksi_items) : 'Tidak ada produksi';

// Profit indicator
$keuntungan = $data['keuntungan'];
if ($keuntungan > 0) {
    $profit_class = 'profit-positive';
    $profit_icon = 'fa-arrow-up';
    $profit_text = 'Untung';
} elseif ($keuntungan < 0) {
    $profit_class = 'profit-negative';
    $profit_icon = 'fa-arrow-down';
    $profit_text = 'Rugi';
} else {
    $profit_class = 'profit-neutral';
    $profit_icon = 'fa-equals';
    $profit_text = 'Impas';
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-info-circle"></i> Detail Data Produksi</h2>
        <p>Informasi lengkap data produksi peternakan</p>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="window.location.href='?module=produksi&action=data'">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <button class="btn btn-primary" onclick="window.location.href='?module=produksi&action=edit&id=<?php echo $id; ?>'">
                <i class="fas fa-edit"></i> Edit Data
            </button>
            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>)">
                <i class="fas fa-trash"></i> Hapus Data
            </button>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-header">
            <div class="detail-title">
                <h3>Produksi #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></h3>
                <p>Tanggal Produksi: <?php echo $tanggal_produksi_formatted; ?></p>
            </div>
            <div class="detail-status <?php echo $profit_class; ?>">
                <i class="fas <?php echo $profit_icon; ?>"></i>
                <span style="color: #fff"><?php echo $profit_text; ?>: Rp <?php echo number_format($keuntungan, 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-user-tag"></i>
                    <h4>Informasi Peternak</h4>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Nama Peternak:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['nama_peternak']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jenis Peternakan:</span>
                        <span class="detail-value"><?php echo $jenis_peternakan_text; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jenis Pakan:</span>
                        <span class="detail-value"><?php echo $data['jenis_pakan'] ? htmlspecialchars($data['jenis_pakan']) : '-'; ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-boxes"></i>
                    <h4>Hasil Produksi</h4>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Total Produksi:</span>
                        <span class="detail-value"><?php echo $produksi_text; ?></span>
                    </div>
                    <?php if ($data['produksi_susu'] > 0): ?>
                    <div class="detail-item">
                        <span class="detail-label">Produksi Susu:</span>
                        <span class="detail-value"><?php echo number_format($data['produksi_susu'], 1); ?> Liter</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($data['produksi_daging'] > 0): ?>
                    <div class="detail-item">
                        <span class="detail-label">Produksi Daging:</span>
                        <span class="detail-value"><?php echo number_format($data['produksi_daging'], 1); ?> kg</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($data['produksi_telur'] > 0): ?>
                    <div class="detail-item">
                        <span class="detail-label">Produksi Telur:</span>
                        <span class="detail-value"><?php echo number_format($data['produksi_telur'], 0); ?> butir</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-chart-line"></i>
                    <h4>Analisis Keuangan</h4>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Biaya Produksi:</span>
                        <span class="detail-value">Rp <?php echo number_format($data['biaya_produksi'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Harga Jual:</span>
                        <span class="detail-value">Rp <?php echo number_format($data['harga_jual'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Keuntungan:</span>
                        <span class="detail-value <?php echo $profit_class; ?>">
                            <i class="fas <?php echo $profit_icon; ?>"></i>
                            Rp <?php echo number_format($keuntungan, 0, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Margin Keuntungan:</span>
                        <span class="detail-value">
                            <?php 
                            $margin = $data['harga_jual'] > 0 ? ($keuntungan / $data['harga_jual']) * 100 : 0;
                            $margin_class = $margin > 0 ? 'text-success' : ($margin < 0 ? 'text-danger' : 'text-muted');
                            ?>
                            <span class="<?php echo $margin_class; ?>">
                                <?php echo number_format($margin, 1); ?>%
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-history"></i>
                    <h4>Informasi Sistem</h4>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">ID Data:</span>
                        <span class="detail-value">#<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Dibuat Oleh:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['petugas'] ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Dibuat:</span>
                        <span class="detail-value"><?php echo $created_at_formatted; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Terakhir Diubah:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-actions">
            <button class="btn btn-primary" onclick="window.location.href='?module=produksi&action=edit&id=<?php echo $id; ?>'">
                <i class="fas fa-edit"></i> Edit Data
            </button>
            <button class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
            <button class="btn btn-info" onclick="exportData(<?php echo $id; ?>)">
                <i class="fas fa-file-export"></i> Export Data
            </button>
            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>)">
                <i class="fas fa-trash"></i> Hapus Data
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
        fetch('modules/produksi/process.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Data berhasil dihapus');
                window.location.href = '?module=produksi&action=data';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}

function exportData(id) {
    // Simple CSV export for single record
    const data = <?php echo json_encode($data); ?>;
    
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Header
    csvContent += "Detail Data Produksi Peternakan\n\n";
    csvContent += "ID," + data.id + "\n";
    csvContent += "Nama Peternak," + data.nama_peternak + "\n";
    csvContent += "Jenis Peternakan," + data.jenis_peternakan + "\n";
    csvContent += "Jenis Pakan," + (data.jenis_pakan || '') + "\n";
    csvContent += "Tanggal Produksi," + data.tanggal_produksi + "\n";
    csvContent += "Produksi Susu," + data.produksi_susu + "\n";
    csvContent += "Produksi Daging," + data.produksi_daging + "\n";
    csvContent += "Produksi Telur," + data.produksi_telur + "\n";
    csvContent += "Biaya Produksi," + data.biaya_produksi + "\n";
    csvContent += "Harga Jual," + data.harga_jual + "\n";
    csvContent += "Keuntungan," + data.keuntungan + "\n";
    csvContent += "Petugas," + data.petugas + "\n";
    csvContent += "Dibuat Pada," + data.created_at + "\n";
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `produksi_${data.id}_${data.tanggal_produksi}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style>
.detail-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #28a745 0%;
    color: white;
}

.detail-title h3 {
    margin: 0;
    font-size: 1.5em;
}

.detail-title p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}

.detail-status {
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.detail-card {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

.detail-card-header {
    background: #e9ecef;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #dee2e6;
}

.detail-card-header h4 {
    margin: 0;
    color: #495057;
}

.detail-card-body {
    padding: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    color: #212529;
    text-align: right;
    max-width: 200px;
    word-wrap: break-word;
}

.profit-positive {
    color: #28a745 !important;
}

.profit-negative {
    color: #dc3545 !important;
}

.profit-neutral {
    color: #6c757d !important;
}

.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-muted { color: #6c757d; }

.detail-actions {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

@media print {
    .module-header,
    .header-actions,
    .detail-actions {
        display: none !important;
    }
    
    .detail-container {
        box-shadow: none;
    }
    
    body {
        background: white !important;
    }
}
</style>