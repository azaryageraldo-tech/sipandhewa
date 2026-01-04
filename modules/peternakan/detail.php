<?php
require_once __DIR__ . '/../../includes/functions.php';

// Ambil data berdasarkan ID
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: ?module=peternakan&action=data');
    exit();
}

// Query untuk mendapatkan data lengkap
$sql = "SELECT p.*, 
               k.nama_kecamatan, 
               d.nama_desa, 
               u.fullname as petugas_input,
               u2.fullname as petugas_update
        FROM peternakan p 
        LEFT JOIN kecamatan k ON p.kecamatan_id = k.id 
        LEFT JOIN desa d ON p.desa_id = d.id 
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN users u2 ON p.created_by = u2.id
        WHERE p.id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo '<div class="alert alert-danger">Data peternakan tidak ditemukan</div>';
    exit();
}

// Format tanggal
$created_at = date('d F Y H:i', strtotime($data['created_at']));
$updated_at = date('d F Y H:i', strtotime($data['updated_at']));

// Hitung utilisasi
$utilisasi = 0;
if ($data['kapasitas_kandang'] > 0) {
    $utilisasi = ($data['jumlah_populasi'] / $data['kapasitas_kandang']) * 100;
}

// Warna untuk utilisasi
$utilisasi_class = '';
$utilisasi_text = '';
if ($utilisasi > 100) {
    $utilisasi_class = 'text-danger';
    $utilisasi_text = 'Over Capacity!';
} elseif ($utilisasi > 80) {
    $utilisasi_class = 'text-warning';
    $utilisasi_text = 'Tinggi';
} elseif ($utilisasi > 50) {
    $utilisasi_class = 'text-info';
    $utilisasi_text = 'Sedang';
} else {
    $utilisasi_class = 'text-success';
    $utilisasi_text = 'Rendah';
}

// Ikon untuk jenis peternakan
$peternakan_icons = [
    'ayam_ras_pedaging' => 'fa-drumstick-bite',
    'ayam_ras_petelur' => 'fa-egg',
    'sapi' => 'fa-cow',
    'babi' => 'fa-piggy-bank',
    'kambing_domba' => 'fa-sheep',
    'unggas_lain' => 'fa-dove'
];

$peternakan_icon = $peternakan_icons[$data['jenis_peternakan']] ?? 'fa-paw';

// Nama bulan
$bulan_list = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$bulan_panen_text = isset($bulan_list[$data['bulan_panen']]) ? 
                   'Bulan ' . $bulan_list[$data['bulan_panen']] : '-';
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-eye"></i> Detail Data Peternakan</h2>
        <div class="header-actions">
            <a href="?module=peternakan&action=edit&id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="?module=peternakan&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>

    <div class="detail-container">
        <!-- Header dengan ID -->
        <div class="detail-header">
            <div class="detail-id">
                <span class="id-badge">ID: #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></span>
                <span class="status-badge badge-success">Aktif</span>
            </div>
            <div class="detail-type">
                <i class="fas <?php echo $peternakan_icon; ?>"></i>
                <h3><?php echo htmlspecialchars($data['nama_unit_usaha']); ?></h3>
            </div>
        </div>

        <div class="detail-grid">
            <!-- Informasi Unit Usaha -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-building"></i>
                    <h3>Informasi Unit Usaha</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Nama Unit Usaha</span>
                        <span class="detail-value">
                            <strong><?php echo htmlspecialchars($data['nama_unit_usaha']); ?></strong>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jenis Peternakan</span>
                        <span class="detail-value">
                            <span class="badge badge-primary">
                                <?php echo ucwords(str_replace('_', ' ', $data['jenis_peternakan'])); ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kepemilikan</span>
                        <span class="detail-value">
                            <span class="badge badge-secondary">
                                <?php echo htmlspecialchars($data['kepemilikan']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bulan Panen</span>
                        <span class="detail-value">
                            <?php echo $bulan_panen_text; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Lokasi Peternakan -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Lokasi Peternakan</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Alamat</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['alamat']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Desa/Kelurahan</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['nama_desa']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kecamatan</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['nama_kecamatan']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Telepon/HP</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['telepon'] ?: '-'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kapasitas dan Populasi -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Kapasitas & Populasi</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Kapasitas Kandang</span>
                        <span class="detail-value">
                            <?php echo number_format($data['kapasitas_kandang'], 0, ',', '.'); ?> ekor
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Populasi Saat Ini</span>
                        <span class="detail-value">
                            <?php echo number_format($data['jumlah_populasi'], 0, ',', '.'); ?> ekor
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Sisa Kapasitas</span>
                        <span class="detail-value">
                            <?php echo number_format(max(0, $data['kapasitas_kandang'] - $data['jumlah_populasi']), 0, ',', '.'); ?> ekor
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Utilisasi</span>
                        <span class="detail-value">
                            <span class="<?php echo $utilisasi_class; ?> font-weight-bold">
                                <?php echo number_format($utilisasi, 1); ?>%
                            </span>
                            <small class="text-muted">(<?php echo $utilisasi_text; ?>)</small>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Utilisasi -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Utilisasi Kandang</h3>
                </div>
                <div class="detail-card-body">
                    <div class="utilization-container">
                        <div class="utilization-info">
                            <span class="utilization-label"><?php echo number_format($data['jumlah_populasi'], 0, ',', '.'); ?> / <?php echo number_format($data['kapasitas_kandang'], 0, ',', '.'); ?> ekor</span>
                            <span class="utilization-percentage <?php echo $utilisasi_class; ?>">
                                <?php echo number_format($utilisasi, 1); ?>%
                            </span>
                        </div>
                        <div class="utilization-bar">
                            <div class="utilization-fill" style="width: <?php echo min(100, $utilisasi); ?>%"></div>
                        </div>
                        <div class="utilization-markers">
                            <span class="marker marker-low">0%</span>
                            <span class="marker marker-medium">50%</span>
                            <span class="marker marker-high">80%</span>
                            <span class="marker marker-full">100%</span>
                        </div>
                        <div class="utilization-zones">
                            <div class="zone zone-optimal" style="width: 50%">
                                <span>Optimal</span>
                            </div>
                            <div class="zone zone-warning" style="width: 30%">
                                <span>Perhatian</span>
                            </div>
                            <div class="zone zone-danger" style="width: 20%">
                                <span>Over</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Petugas -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-user-md"></i>
                    <h3>Informasi Petugas</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Petugas Input</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['petugas_input'] ?: '-'); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Waktu Input</span>
                        <span class="detail-value">
                            <?php echo $created_at; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Terakhir Diupdate</span>
                        <span class="detail-value">
                            <?php echo $updated_at; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Status Peternakan -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>Status Peternakan</h3>
                </div>
                <div class="detail-card-body">
                    <div class="status-indicators">
                        <div class="status-item <?php echo $utilisasi > 80 ? 'status-warning' : 'status-success'; ?>">
                            <i class="fas <?php echo $utilisasi > 80 ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                            <span>Kepadatan Kandang</span>
                            <small><?php echo $utilisasi > 80 ? 'Perlu Perhatian' : 'Normal'; ?></small>
                        </div>
                        <div class="status-item status-success">
                            <i class="fas fa-check-circle"></i>
                            <span>Data Terdaftar</span>
                            <small>Aktif</small>
                        </div>
                        <div class="status-item status-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Kelas Usaha</span>
                            <small>
                                <?php 
                                if ($data['jumlah_populasi'] > 1000) echo 'Besar';
                                elseif ($data['jumlah_populasi'] > 100) echo 'Menengah';
                                else echo 'Kecil';
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <a href="?module=peternakan&action=edit&id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Data
            </a>
            <button onclick="confirmDelete(<?php echo $id; ?>)" class="btn btn-danger">
                <i class="fas fa-trash"></i> Hapus Data
            </button>
            <a href="?module=peternakan&action=data" class="btn btn-secondary">
                <i class="fas fa-list"></i> Lihat Semua Data
            </a>
            <button onclick="generateReport(<?php echo $id; ?>)" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Laporan PDF
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data peternakan ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/peternakan/delete.php?id=' + id;
    }
}

function generateReport(id) {
    window.open('modules/peternakan/report.php?id=' + id, '_blank');
}
</script>

<style>
.detail-container {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.detail-id .id-badge {
    background: #007bff;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    margin-right: 10px;
}

.detail-id .status-badge {
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
}

.detail-type {
    display: flex;
    align-items: center;
    gap: 15px;
}

.detail-type i {
    font-size: 2em;
    color: #28a745;
}

.detail-type h3 {
    margin: 0;
    color: #333;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.detail-card {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.detail-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-card-header i {
    font-size: 1.2em;
}

.detail-card-header h3 {
    margin: 0;
    font-size: 1.1em;
}

.detail-card-body {
    padding: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.detail-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    text-align: right;
    font-weight: 500;
}

/* Utilization Bar Styles */
.utilization-container {
    padding: 15px;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.utilization-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.utilization-label {
    font-weight: bold;
    color: #495057;
}

.utilization-percentage {
    font-weight: bold;
    font-size: 1.2em;
}

.utilization-bar {
    height: 25px;
    background: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    margin-bottom: 5px;
}

.utilization-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.5s ease;
}

.utilization-markers {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 11px;
    color: #6c757d;
}

.marker {
    position: relative;
}

.marker-low:before {
    content: '';
    position: absolute;
    top: -15px;
    left: 0;
    width: 1px;
    height: 10px;
    background: #6c757d;
}

.marker-medium:before {
    content: '';
    position: absolute;
    top: -15px;
    left: 50%;
    width: 1px;
    height: 10px;
    background: #6c757d;
}

.marker-high:before {
    content: '';
    position: absolute;
    top: -15px;
    left: 80%;
    width: 1px;
    height: 10px;
    background: #6c757d;
}

.marker-full:before {
    content: '';
    position: absolute;
    top: -15px;
    right: 0;
    width: 1px;
    height: 10px;
    background: #6c757d;
}

.utilization-zones {
    display: flex;
    height: 15px;
    border-radius: 3px;
    overflow: hidden;
}

.zone {
    text-align: center;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px;
}

.zone-optimal {
    background: #28a745;
}

.zone-warning {
    background: #ffc107;
}

.zone-danger {
    background: #dc3545;
}

/* Status Indicators */
.status-indicators {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
}

.status-item {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background: white;
}

.status-item i {
    font-size: 2em;
    margin-bottom: 10px;
}

.status-item span {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.status-item small {
    font-size: 11px;
    color: #6c757d;
}

.status-success {
    color: #28a745;
    border: 2px solid #28a745;
}

.status-warning {
    color: #ffc107;
    border: 2px solid #ffc107;
}

.status-info {
    color: #17a2b8;
    border: 2px solid #17a2b8;
}

/* Action Buttons */
.detail-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

/* Print Styles */
@media print {
    .module-header .header-actions,
    .detail-actions {
        display: none;
    }
    
    .detail-container {
        box-shadow: none;
        padding: 0;
    }
    
    .detail-card {
        break-inside: avoid;
    }
}
</style>