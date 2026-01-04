<?php
// modules/penyakit_hewan/data.php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$jenis_ternak = $_GET['jenis_ternak'] ?? '';
$bulan = $_GET['bulan'] ?? date('Y-m');
$jenis_penyakit = $_GET['jenis_penyakit'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$sql = "SELECT p.*, u.fullname as petugas 
        FROM penyakit_hewan p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.bulan = ?";
$params = [$bulan];

if ($jenis_ternak) {
    $sql .= " AND p.jenis_ternak = ?";
    $params[] = $jenis_ternak;
}

if ($jenis_penyakit) {
    $sql .= " AND p.jenis_penyakit LIKE ?";
    $params[] = "%$jenis_penyakit%";
}

if ($status) {
    $sql .= " AND p.status_penanganan = ?";
    $params[] = $status;
}

$sql .= " ORDER BY p.jenis_ternak, p.minggu_ke, p.jenis_penyakit";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(DISTINCT jenis_ternak) as total_jenis_ternak,
                COUNT(*) as total_kasus,
                SUM(kasus_digital) as total_kasus_digital,
                SUM(sampel_positif) as total_positif,
                SUM(sampel_negatif) as total_negatif,
                SUM(total_sampel) as total_sampel
               FROM penyakit_hewan 
               WHERE bulan = ?";
$summary = fetchOne($summarySql, [$bulan]);

// Calculate percentages
$positif_rate = $summary['total_sampel'] > 0 ? 
    round(($summary['total_positif'] / $summary['total_sampel']) * 100, 2) : 0;
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-virus"></i> Data Penyakit Hewan Menular Strategis</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('penyakitTable', 'data-penyakit-hewan')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary" onclick="printTable('penyakitTable')">
                <i class="fas fa-print"></i> Cetak
            </button>
            <button class="btn btn-primary" onclick="window.location.href='?module=penyakit&action=input'">
                <i class="fas fa-plus"></i> Input Data Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #4CAF50;">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['total_jenis_ternak'] ?? 0; ?></h3>
                <p>Jenis Ternak</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #2196F3;">
                <i class="fas fa-virus"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_kasus'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Kasus</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #FF9800;">
                <i class="fas fa-laptop-medical"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_kasus_digital'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Kasus Gigitan Rabies</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #F44336;">
                <i class="fas fa-virus-slash"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $positif_rate; ?>%</h3>
                <p>Positif Rate</p>
                <small><?php echo $summary['total_positif'] ?? 0; ?> dari <?php echo $summary['total_sampel'] ?? 0; ?> sampel</small>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="penyakit">
            <input type="hidden" name="action" value="data">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="jenis_ternak">Jenis Ternak</label>
                    <select id="jenis_ternak" name="jenis_ternak">
                        <option value="">Semua Jenis</option>
                        <option value="sapi" <?php echo $jenis_ternak == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                        <option value="kambing" <?php echo $jenis_ternak == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                        <option value="ayam" <?php echo $jenis_ternak == 'ayam' ? 'selected' : ''; ?>>Ayam</option>
                        <option value="bebek" <?php echo $jenis_ternak == 'bebek' ? 'selected' : ''; ?>>Bebek</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bulan">Bulan</label>
                    <input type="month" id="bulan" name="bulan" 
                           value="<?php echo $bulan; ?>">
                </div>
                
                <div class="form-group">
                    <label for="jenis_penyakit">Jenis Penyakit</label>
                    <input type="text" id="jenis_penyakit" name="jenis_penyakit" 
                           value="<?php echo htmlspecialchars($jenis_penyakit); ?>"
                           placeholder="Nama penyakit...">
                </div>
                
                <div class="form-group">
                    <label for="status">Status Penanganan</label>
                    <select id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="dalam_pengawasan" <?php echo $status == 'dalam_pengawasan' ? 'selected' : ''; ?>>Dalam Pengawasan</option>
                        <option value="dalam_penanganan" <?php echo $status == 'dalam_penanganan' ? 'selected' : ''; ?>>Dalam Penanganan</option>
                        <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
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
            <table id="penyakitTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Ternak</th>
                        <th>Bulan</th>
                        <th>Minggu</th>
                        <th>Penyakit</th>
                        <th>Kasus Gigitan Rabies</th>
                        <th>Sampel (+/-)</th>
                        <th>Virus</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="12" class="text-center">Tidak ada data penyakit</td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($data as $row): ?>
                            <?php 
                            // Get icon based on animal type
                            $icon = '';
                            $color = '';
                            switch($row['jenis_ternak']) {
                                case 'sapi': $icon = 'fas fa-cow'; $color = '#795548'; break;
                                case 'kambing': $icon = 'fas fa-horse-head'; $color = '#8D6E63'; break;
                                case 'ayam': $icon = 'fas fa-kiwi-bird'; $color = '#FF9800'; break;
                                case 'bebek': $icon = 'fas fa-dove'; $color = '#2196F3'; break;
                            }
                            
                            // Status badge
                            $status_badge = '';
                            switch($row['status_penanganan']) {
                                case 'dalam_pengawasan': 
                                    $status_badge = '<span class="badge badge-warning"><i class="fas fa-eye"></i> Pengawasan</span>'; 
                                    break;
                                case 'dalam_penanganan': 
                                    $status_badge = '<span class="badge badge-danger"><i class="fas fa-first-aid"></i> Penanganan</span>'; 
                                    break;
                                case 'selesai': 
                                    $status_badge = '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Selesai</span>'; 
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $color; ?>;">
                                        <i class="<?php echo $icon; ?>"></i> 
                                        <?php echo ucfirst($row['jenis_ternak']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M Y', strtotime($row['bulan'] . '-01')); ?></td>
                                <td>
                                    <?php if ($row['minggu_ke']): ?>
                                        <span class="badge badge-info">Minggu <?php echo $row['minggu_ke']; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Bulanan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['jenis_penyakit']); ?></strong>
                                    <?php if ($row['catatan']): ?>
                                        <br><small class="text-muted"><?php echo substr($row['catatan'], 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">
                                        <i class="fas fa-laptop-medical"></i> <?php echo $row['kasus_digital']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="sample-info">
                                        <span class="positive"><?php echo $row['sampel_positif']; ?> +</span>
                                        <span class="negative"><?php echo $row['sampel_negatif']; ?> -</span>
                                        <br>
                                        <small class="text-muted">Total: <?php echo $row['total_sampel']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($row['virus_teridentifikasi'], 0, 30)); ?>...</small>
                                </td>
                                <td><?php echo $row['lokasi'] ?: '-'; ?></td>
                                <td><?php echo $status_badge; ?></td>
                                <td><?php echo $row['petugas'] ?: '-'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" 
                                                onclick="window.location.href='?module=penyakit&action=edit&id=<?php echo $row['id']; ?>'"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="modules/penyakit/delete.php?id=<?= $row['id'] ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Yakin hapus data ini?')"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <!-- <button class="btn-action btn-info" 
                                                onclick="viewDetails(<?php echo $row['id']; ?>)"
                                                title="Detail">
                                            <i class="fas fa-info-circle"></i>
                                        </button> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistics Chart -->
    <div class="statistics-card">
        <h4><i class="fas fa-chart-pie"></i> Statistik Penyakit per Jenis Ternak</h4>
        <div class="stats-grid">
            <?php
            // Get statistics by animal type
            $statsSql = "SELECT 
                        jenis_ternak,
                        SUM(kasus_digital) as total_digital,
                        SUM(sampel_positif) as total_positif,
                        SUM(total_sampel) as total_sampel,
                        COUNT(*) as jumlah_kasus
                       FROM penyakit_hewan 
                       WHERE bulan = ?
                       GROUP BY jenis_ternak";
            $statsData = fetchAll($statsSql, [$bulan]);
            
            foreach ($statsData as $stat):
                $rate = $stat['total_sampel'] > 0 ? 
                    round(($stat['total_positif'] / $stat['total_sampel']) * 100, 1) : 0;
            ?>
                <div class="stat-item">
                    <span class="stat-label"><?php echo ucfirst($stat['jenis_ternak']); ?>:</span>
                    <span class="stat-value">
                        <?php echo $stat['jumlah_kasus']; ?> kasus
                        <small>(<?php echo $stat['total_digital']; ?> digital, <?php echo $rate; ?>% positif)</small>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function resetFilter() {
    window.location.href = '?module=penyakit&action=data';
}

function viewDetails(id) {
    window.location.href = '?module=penyakit&action=detail&id=' + id;
}
</script>

<style>
.sample-info .positive {
    color: #F44336;
    font-weight: bold;
}
.sample-info .negative {
    color: #4CAF50;
    font-weight: bold;
}
</style>