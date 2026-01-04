<?php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$kecamatan = $_GET['kecamatan'] ?? '';
$jenis_peternakan = $_GET['jenis_peternakan'] ?? '';
$kepemilikan = $_GET['kepemilikan'] ?? '';

// Build query
$sql = "SELECT p.*, k.nama_kecamatan, d.nama_desa, u.fullname as petugas 
        FROM peternakan p 
        LEFT JOIN kecamatan k ON p.kecamatan_id = k.id 
        LEFT JOIN desa d ON p.desa_id = d.id 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE 1=1";
$params = [];

if ($kecamatan) {
    $sql .= " AND k.nama_kecamatan = ?";
    $params[] = $kecamatan;
}

if ($jenis_peternakan) {
    $sql .= " AND p.jenis_peternakan = ?";
    $params[] = $jenis_peternakan;
}

if ($kepemilikan) {
    $sql .= " AND p.kepemilikan = ?";
    $params[] = $kepemilikan;
}

$sql .= " ORDER BY k.nama_kecamatan, d.nama_desa, p.nama_unit_usaha";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(*) as total_peternakan,
                SUM(kapasitas_kandang) as total_kapasitas,
                SUM(jumlah_populasi) as total_populasi,
                COUNT(DISTINCT kecamatan_id) as kecamatan_terdata,
                COUNT(DISTINCT jenis_peternakan) as jenis_ternak
               FROM peternakan";
$summary = fetchOne($summarySql);

// Get statistics by jenis peternakan
$statsByJenisSql = "SELECT jenis_peternakan, 
                    COUNT(*) as jumlah,
                    SUM(kapasitas_kandang) as kapasitas,
                    SUM(jumlah_populasi) as populasi
                    FROM peternakan 
                    GROUP BY jenis_peternakan 
                    ORDER BY jumlah DESC";
$statsByJenis = fetchAll($statsByJenisSql);
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-list"></i> Data Peternakan Terdaftar</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('peternakanTable', 'data-peternakan')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary" onclick="window.location.href='?module=peternakan&action=input'">
                <i class="fas fa-plus"></i> Input Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_peternakan'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Peternakan</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-expand-arrows-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_kapasitas'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Kapasitas</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-crow"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_populasi'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Populasi</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="summary-content">
                <h3>
                    <?php 
                    $kapasitas = $summary['total_kapasitas'] ?? 1;
                    $populasi = $summary['total_populasi'] ?? 0;
                    $utilisasi = $kapasitas > 0 ? ($populasi / $kapasitas) * 100 : 0;
                    echo number_format($utilisasi, 1) . '%';
                    ?>
                </h3>
                <p>Utilisasi</p>
            </div>
        </div>
    </div>

    <!-- Statistics by Type -->
    <div class="stats-table">
        <h3><i class="fas fa-chart-pie"></i> Distribusi Jenis Peternakan</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis Peternakan</th>
                    <th>Jumlah Unit</th>
                    <th>Kapasitas</th>
                    <th>Populasi</th>
                    <th>Utilisasi</th>
                    <th>% dari Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statsByJenis as $stat): ?>
                    <?php 
                    $utilisasi = $stat['kapasitas'] > 0 ? ($stat['populasi'] / $stat['kapasitas']) * 100 : 0;
                    $percentage = $summary['total_peternakan'] > 0 ? ($stat['jumlah'] / $summary['total_peternakan']) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <strong>
                                <?php echo ucwords(str_replace('_', ' ', $stat['jenis_peternakan'])); ?>
                            </strong>
                        </td>
                        <td class="text-center"><?php echo $stat['jumlah']; ?></td>
                        <td class="text-center"><?php echo number_format($stat['kapasitas'], 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($stat['populasi'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <span class="<?php echo $utilisasi > 100 ? 'text-danger' : ($utilisasi > 80 ? 'text-warning' : 'text-success'); ?>">
                                <?php echo number_format($utilisasi, 1); ?>%
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                    <?php echo number_format($percentage, 1); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="peternakan">
            <input type="hidden" name="action" value="data">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="kecamatan">Kecamatan</label>
                    <select id="kecamatan" name="kecamatan">
                        <option value="">Semua Kecamatan</option>
                        <?php foreach (getKecamatanList() as $kec): ?>
                            <option value="<?php echo $kec; ?>" 
                                <?php echo $kecamatan == $kec ? 'selected' : ''; ?>>
                                <?php echo $kec; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jenis_peternakan">Jenis Peternakan</label>
                    <select id="jenis_peternakan" name="jenis_peternakan">
                        <option value="">Semua Jenis</option>
                        <option value="ayam_ras_pedaging" <?php echo $jenis_peternakan == 'ayam_ras_pedaging' ? 'selected' : ''; ?>>Ayam Ras Pedaging</option>
                        <option value="ayam_ras_petelur" <?php echo $jenis_peternakan == 'ayam_ras_petelur' ? 'selected' : ''; ?>>Ayam Ras Petelur</option>
                        <option value="sapi" <?php echo $jenis_peternakan == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                        <option value="babi" <?php echo $jenis_peternakan == 'babi' ? 'selected' : ''; ?>>Babi</option>
                        <option value="kambing_domba" <?php echo $jenis_peternakan == 'kambing_domba' ? 'selected' : ''; ?>>Kambing/Domba</option>
                        <option value="unggas_lain" <?php echo $jenis_peternakan == 'unggas_lain' ? 'selected' : ''; ?>>Unggas Lain</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kepemilikan">Kepemilikan</label>
                    <select id="kepemilikan" name="kepemilikan">
                        <option value="">Semua Kepemilikan</option>
                        <option value="Pribadi" <?php echo $kepemilikan == 'Pribadi' ? 'selected' : ''; ?>>Pribadi</option>
                        <option value="Kemitraan" <?php echo $kepemilikan == 'Kemitraan' ? 'selected' : ''; ?>>Kemitraan</option>
                        <option value="Kelompok" <?php echo $kepemilikan == 'Kelompok' ? 'selected' : ''; ?>>Kelompok Tani</option>
                        <option value="Koperasi" <?php echo $kepemilikan == 'Koperasi' ? 'selected' : ''; ?>>Koperasi</option>
                        <option value="Perusahaan" <?php echo $kepemilikan == 'Perusahaan' ? 'selected' : ''; ?>>Perusahaan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                    <i class="fas fa-redo"></i> Reset Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-card">
        <div class="table-responsive">
            <table id="peternakanTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Unit Usaha</th>
                        <th>Jenis</th>
                        <th>Lokasi</th>
                        <th>Kontak</th>
                        <th>Kapasitas</th>
                        <th>Populasi</th>
                        <th>Utilisasi</th>
                        <th>Kepemilikan</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada data peternakan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $index => $row): ?>
                            <?php 
                            $utilisasi = $row['kapasitas_kandang'] > 0 ? 
                                        ($row['jumlah_populasi'] / $row['kapasitas_kandang']) * 100 : 0;
                            $utilisasiClass = $utilisasi > 100 ? 'text-danger' : 
                                            ($utilisasi > 80 ? 'text-warning' : 'text-success');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_unit_usaha']); ?></strong>
                                    <?php if ($row['bulan_panen']): ?>
                                        <br><small>Panen: Bulan <?php echo $row['bulan_panen']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo ucwords(str_replace('_', ' ', $row['jenis_peternakan'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['alamat']); ?><br>
                                    <small>
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo $row['nama_desa']; ?>, <?php echo $row['nama_kecamatan']; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($row['telepon']): ?>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['telepon']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo number_format($row['kapasitas_kandang'], 0, ',', '.'); ?></td>
                                <td class="text-center"><?php echo number_format($row['jumlah_populasi'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <span class="<?php echo $utilisasiClass; ?>">
                                        <strong><?php echo number_format($utilisasi, 1); ?>%</strong>
                                    </span>
                                    <?php if ($utilisasi > 100): ?>
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Over</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo $row['kepemilikan']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['petugas'] ?: '-'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" 
                                                onclick="editData(<?php echo $row['id']; ?>)"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-delete" 
                                                onclick="deleteData(<?php echo $row['id']; ?>, 'peternakan')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="btn-action btn-view" 
                                                onclick="viewDetails(<?php echo $row['id']; ?>)"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <!-- <div class="pagination">
            <span>Menampilkan <?php echo count($data); ?> peternakan</span>
            <div class="pagination-controls">
                <a href="#" class="page-link disabled">&laquo; Prev</a>
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link">2</a>
                <a href="#" class="page-link">3</a>
                <a href="#" class="page-link">Next &raquo;</a>
            </div>
        </div> -->
    </div>
</div>

<script>
function resetFilter() {
    document.getElementById('kecamatan').value = '';
    document.getElementById('jenis_peternakan').value = '';
    document.getElementById('kepemilikan').value = '';
    document.querySelector('.filter-form').submit();
}

function editData(id) {
    window.location.href = '?module=peternakan&action=edit&id=' + id;
}

function deleteData(id, type) {
    if (confirm('Apakah Anda yakin ingin menghapus data peternakan ini?')) {
        window.location.href = 'modules/peternakan/delete.php?id=' + id;
    }
}

function viewDetails(id) {
    window.location.href = '?module=peternakan&action=detail&id=' + id;
}

function calculateUtilization(capacity, population) {
    capacity = parseInt(capacity) || 0;
    population = parseInt(population) || 0;
    if (capacity > 0) {
        return ((population / capacity) * 100).toFixed(1);
    }
    return '0.0';
}

function calculateUtilizationColor(capacity, population) {
    const utilization = parseFloat(calculateUtilization(capacity, population));
    if (utilization > 100) return '#dc3545';
    if (utilization > 80) return '#ffc107';
    if (utilization > 50) return '#17a2b8';
    return '#28a745';
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}
</script>

<style>
.progress {
    height: 20px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background-color: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: width 0.3s ease;
}
</style>