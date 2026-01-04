<?php
// modules/penyakit_hewan/detail.php
require_once __DIR__ . '/../../includes/functions.php';

// Get ID from URL
$id = $_GET['id'] ?? 0;

// Fetch existing data
$sql = "SELECT p.*, u.fullname as petugas 
        FROM penyakit_hewan p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
    echo '<a href="?module=penyakitn&action=data" class="btn btn-primary">Kembali ke Data</a>';
    exit();
}

// Calculate rates
$positif_rate = $data['total_sampel'] > 0 ? 
    round(($data['sampel_positif'] / $data['total_sampel']) * 100, 2) : 0;
$negatif_rate = $data['total_sampel'] > 0 ? 
    round(($data['sampel_negatif'] / $data['total_sampel']) * 100, 2) : 0;
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-file-medical-alt"></i> Detail Data Penyakit Hewan</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.location.href='?module=penyakit&action=data'">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <button class="btn btn-warning" onclick="window.location.href='?module=penyakit&action=edit&id=<?php echo $id; ?>'">
                <i class="fas fa-edit"></i> Edit Data
            </button>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-header">
            <h3><?php echo htmlspecialchars($data['jenis_penyakit']); ?></h3>
            <div class="detail-subtitle">
                <span class="badge" style="background-color: 
                    <?php 
                    switch($data['jenis_ternak']) {
                        case 'sapi': echo '#795548'; break;
                        case 'kambing': echo '#8D6E63'; break;
                        case 'ayam': echo '#FF9800'; break;
                        case 'bebek': echo '#2196F3'; break;
                    }
                    ?>;">
                    <i class="fas fa-<?php echo $data['jenis_ternak'] == 'ayam' ? 'kiwi-bird' : ($data['jenis_ternak'] == 'bebek' ? 'dove' : ($data['jenis_ternak'] == 'sapi' ? 'cow' : 'horse-head')); ?>"></i>
                    <?php echo ucfirst($data['jenis_ternak']); ?>
                </span>
                <span class="badge badge-info">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('F Y', strtotime($data['bulan'] . '-01')); ?>
                    <?php if ($data['minggu_ke']): ?>
                        - Minggu <?php echo $data['minggu_ke']; ?>
                    <?php endif; ?>
                </span>
                <span class="badge badge-<?php echo $data['status_penanganan'] == 'selesai' ? 'success' : ($data['status_penanganan'] == 'dalam_penanganan' ? 'danger' : 'warning'); ?>">
                    <?php 
                    switch($data['status_penanganan']) {
                        case 'dalam_pengawasan': echo '<i class="fas fa-eye"></i> Dalam Pengawasan'; break;
                        case 'dalam_penanganan': echo '<i class="fas fa-first-aid"></i> Dalam Penanganan'; break;
                        case 'selesai': echo '<i class="fas fa-check-circle"></i> Selesai'; break;
                    }
                    ?>
                </span>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-section">
                <h4><i class="fas fa-chart-line"></i> Statistik Kasus</h4>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-laptop-medical"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($data['kasus_digital'], 0, ',', '.'); ?></h3>
                            <p>Kasus Gigitan Rabies</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #F44336;">
                            <i class="fas fa-virus"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($data['sampel_positif'], 0, ',', '.'); ?></h3>
                            <p>Sampel Positif</p>
                            <small><?php echo $positif_rate; ?>% dari total</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-virus-slash"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($data['sampel_negatif'], 0, ',', '.'); ?></h3>
                            <p>Sampel Negatif</p>
                            <small><?php echo $negatif_rate; ?>% dari total</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FF9800;">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($data['total_sampel'], 0, ',', '.'); ?></h3>
                            <p>Total Sampel</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h4><i class="fas fa-info-circle"></i> Informasi Detail</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-dna"></i> Virus Teridentifikasi:</span>
                        <span class="info-value"><?php echo htmlspecialchars($data['virus_teridentifikasi'] ?: '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Lokasi:</span>
                        <span class="info-value"><?php echo htmlspecialchars($data['lokasi'] ?: '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user-md"></i> Petugas Input:</span>
                        <span class="info-value"><?php echo htmlspecialchars($data['petugas'] ?: '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-clock"></i> Terakhir Update:</span>
                        <span class="info-value">
                            <?php echo $data['updated_at'] ? date('d/m/Y H:i', strtotime($data['updated_at'])) : '-'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($data['catatan']): ?>
            <div class="detail-section">
                <h4><i class="fas fa-sticky-note"></i> Catatan dan Rekomendasi</h4>
                <div class="note-box">
                    <?php echo nl2br(htmlspecialchars($data['catatan'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>