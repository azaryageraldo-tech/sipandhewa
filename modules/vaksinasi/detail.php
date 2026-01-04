<?php
require_once __DIR__ . '/../../includes/functions.php';

// Ambil data berdasarkan ID
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: ?module=vaksinasi&action=data');
    exit();
}

// Query untuk mendapatkan data lengkap
$sql = "SELECT v.*, 
               k.nama_kecamatan, 
               d.nama_desa, 
               u.fullname as petugas_input,
               u2.fullname as petugas_update
        FROM vaksinasi v 
        LEFT JOIN kecamatan k ON v.kecamatan_id = k.id 
        LEFT JOIN desa d ON v.desa_id = d.id 
        LEFT JOIN users u ON v.created_by = u.id
        LEFT JOIN users u2 ON v.created_by = u2.id
        WHERE v.id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo '<div class="alert alert-danger">Data vaksinasi tidak ditemukan</div>';
    exit();
}

// Format tanggal
$tanggal_vaksinasi = date('d F Y', strtotime($data['tanggal_vaksinasi']));
$created_at = date('d F Y H:i', strtotime($data['created_at']));
$updated_at = date('d F Y H:i', strtotime($data['updated_at']));

// Ikon untuk jenis hewan
$animal_icons = [
    'anjing' => 'fa-dog',
    'kucing' => 'fa-cat',
    'sapi' => 'fa-cow',
    'kambing' => 'fa-sheep',
    'ayam' => 'fa-kiwi-bird',
    'babi' => 'fa-piggy-bank'
];

$animal_icon = $animal_icons[$data['jenis_hewan']] ?? 'fa-paw';

// Warna badge untuk jenis vaksin
$vaccine_colors = [
    'rabies' => 'danger',
    'antraks' => 'warning',
    'pmk' => 'primary',
    'newcastle' => 'info',
    'gumboro' => 'success',
    'brucellosis' => 'secondary',
    'lainnya' => 'dark'
];

$vaccine_color = $vaccine_colors[$data['jenis_vaksin']] ?? 'primary';
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-eye"></i> Detail Data Vaksinasi</h2>
        <div class="header-actions">
            <a href="?module=vaksinasi&action=edit&id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="?module=vaksinasi&action=data" class="btn btn-secondary">
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
                <span class="status-badge badge-success">Tervaksinasi</span>
            </div>
            <div class="detail-date">
                <i class="fas fa-calendar"></i> <?php echo $tanggal_vaksinasi; ?>
            </div>
        </div>

        <div class="detail-grid">
            <!-- Informasi Pemilik -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-user"></i>
                    <h3>Informasi Pemilik</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Nama Pemilik</span>
                        <span class="detail-value">
                            <strong><?php echo htmlspecialchars($data['nama_pemilik']); ?></strong>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Lokasi</span>
                        <span class="detail-value">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($data['nama_desa']); ?>, 
                            <?php echo htmlspecialchars($data['nama_kecamatan']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Informasi Hewan -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas <?php echo $animal_icon; ?>"></i>
                    <h3>Informasi Hewan</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Jenis Hewan</span>
                        <span class="detail-value">
                            <span class="badge badge-primary">
                                <?php echo ucfirst($data['jenis_hewan']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Umur Hewan</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($data['umur_hewan'] ?: '-'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Vaksinasi -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-syringe"></i>
                    <h3>Detail Vaksinasi</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Jenis Vaksin</span>
                        <span class="detail-value">
                            <span class="badge badge-<?php echo $vaccine_color; ?>">
                                <?php echo ucfirst($data['jenis_vaksin']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Vaksinasi</span>
                        <span class="detail-value">
                            <i class="fas fa-calendar-check"></i>
                            <?php echo $tanggal_vaksinasi; ?>
                        </span>
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
        </div>

        <!-- Catatan Tambahan -->
        <div class="detail-notes">
            <h4><i class="fas fa-sticky-note"></i> Catatan</h4>
            <p>Data vaksinasi ini telah tercatat dalam sistem dan dapat digunakan untuk pelaporan dan monitoring program vaksinasi hewan.</p>
            
            <div class="notes-info">
                <div class="note-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <span>Vaksinasi telah dilakukan sesuai standar</span>
                </div>
                <div class="note-item">
                    <i class="fas fa-database text-info"></i>
                    <span>Data tersimpan dalam database sistem</span>
                </div>
                <div class="note-item">
                    <i class="fas fa-file-pdf text-danger"></i>
                    <span>Laporan tersedia untuk dicetak/didownload</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <a href="?module=vaksinasi&action=edit&id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Data
            </a>
            <button onclick="confirmDelete(<?php echo $id; ?>)" class="btn btn-danger">
                <i class="fas fa-trash"></i> Hapus Data
            </button>
            <a href="?module=vaksinasi&action=data" class="btn btn-secondary">
                <i class="fas fa-list"></i> Lihat Semua Data
            </a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data vaksinasi ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/vaksinasi/delete.php?id=' + id;
    }
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
    padding-bottom: 15px;
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

.detail-date {
    font-size: 1.1em;
    color: #666;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

.detail-notes {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.detail-notes h4 {
    color: #856404;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.notes-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.note-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9em;
    color: #666;
}

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