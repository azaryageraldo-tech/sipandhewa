<?php
// modules/getDesaByKecamatan/data.php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$kecamatan_id = $_GET['kecamatan_id'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Build query untuk summary per kecamatan
$sql = "SELECT 
        k.nama_kecamatan,
        COUNT(DISTINCT p.desa_id) as jumlah_desa,
        SUM(p.total_semua) as total_populasi,
        SUM(p.sapi_bali_total) as total_sapi_bali,
        SUM(p.kerbau_total) as total_kerbau,
        SUM(p.kuda_total) as total_kuda,
        SUM(p.babi_bali_total) as total_babi_bali,
        SUM(p.babi_landrace_total) as total_babi_landrace,
        SUM(p.kambing_total) as total_kambing,
        SUM(p.unggas_total) as total_unggas,
        SUM(p.anjing_total) as total_anjing
        FROM populasi_ternak p
        JOIN kecamatan k ON p.kecamatan_id = k.id
        WHERE p.bulan = ? AND p.tahun = ?";
$params = [$bulan, $tahun];

if ($kecamatan_id) {
    $sql .= " AND p.kecamatan_id = ?";
    $params[] = $kecamatan_id;
}

$sql .= " GROUP BY k.nama_kecamatan ORDER BY k.nama_kecamatan";

$data = fetchAll($sql, $params);

// Query overall summary - PERBAIKI INI:
// PERBAIKI QUERY SUMMARY (line 39-55 di data.php):
$summarySql = "SELECT 
                COUNT(DISTINCT kecamatan_id) as total_kecamatan,
                COUNT(DISTINCT desa_id) as total_desa,
                SUM(total_semua) as total_semua_hewan,
                SUM(sapi_bali_total) as total_sapi_bali,
                SUM(sapi_lain_total) as total_sapi_lain,
                SUM(kerbau_total) as total_kerbau,
                SUM(kuda_total) as total_kuda,
                SUM(babi_bali_total) as total_babi_bali,
                SUM(babi_landrace_total) as total_babi_landrace,
                SUM(kambing_total) as total_kambing,
                SUM(unggas_total) as total_unggas,
                SUM(anjing_total) as total_anjing
               FROM populasi_ternak 
               WHERE bulan = ? AND tahun = ?";
$summary = fetchOne($summarySql, [$bulan, $tahun]);

// Get chart data
// $chartSql = "SELECT 
//                 jenis,
//                 total
//              FROM (
//                 SELECT 'Sapi Bali' as jenis, SUM(sapi_bali_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Kerbau' as jenis, SUM(kerbau_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Kuda' as jenis, SUM(kuda_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Babi Bali' as jenis, SUM(babi_bali_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Babi Landrace' as jenis, SUM(babi_landrace_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Kambing' as jenis, SUM(kambing_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Unggas' as jenis, SUM(unggas_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//                 UNION ALL
//                 SELECT 'Anjing' as jenis, SUM(anjing_total) as total FROM populasi_ternak WHERE bulan = ? AND tahun = ?
//              ) as chart_data
//              WHERE total > 0";
// $chartData = fetchAll($chartSql, array_fill(0, 16, $bulan) + array_fill(16, 16, $tahun));
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-chart-pie"></i> Sistem Populasi Ternak</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('populasiTable', 'data-populasi-ternak')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <!-- <button class="btn btn-primary" onclick="printTable('populasiTable')">
                <i class="fas fa-print"></i> Cetak Laporan
            </button> -->
            <button class="btn btn-primary" onclick="window.location.href='?module=populasi&action=input'">
                <i class="fas fa-plus"></i> Input Data Baru
            </button>
         
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #4CAF50;">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['total_kecamatan'] ?? 0; ?></h3>
                <p>Kecamatan</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #2196F3;">
                <i class="fas fa-village"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['total_desa'] ?? 0; ?></h3>
                <p>Desa</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #FF9800;">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_semua_hewan'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Hewan</p>
            </div>
        </div>
        
        <!-- <div class="summary-card">
            <div class="summary-icon" style="background-color: #9C27B0;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo DateTime::createFromFormat('!m', $bulan)->format('F'); ?></h3>
                <p><?php echo $tahun; ?></p>
                <small>Periode</small>
            </div>
        </div> -->
    </div>

    <!-- Filter Form -->
    <div class="filter-card" style="margin-top: 20px;">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="populasi">
            <input type="hidden" name="action" value="data">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="kecamatan_id">Kecamatan</label>
                    <select id="kecamatan_id" name="kecamatan_id">
                        <option value="">Semua Kecamatan</option>
                        <?php 
                        $kecamatanList = fetchAll("SELECT id, nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan");
                        foreach ($kecamatanList as $kec): ?>
                            <option value="<?php echo $kec['id']; ?>" 
                                <?php echo $kecamatan_id == $kec['id'] ? 'selected' : ''; ?>>
                                <?php echo $kec['nama_kecamatan']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bulan">Bulan</label>
                    <select id="bulan" name="bulan">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" 
                                <?php echo $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                <?php echo DateTime::createFromFormat('!m', $i)->format('F'); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tahun">Tahun</label>
                    <input type="number" id="tahun" name="tahun" 
                           value="<?php echo $tahun; ?>" min="2020" max="2030">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Chart Distribution -->
    <!-- <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> Distribusi Populasi Ternak</h3>
        <div class="chart-container" style="height: 300px;">
            <canvas id="populationChart"></canvas>
        </div>
    </div> -->

    <!-- Data Table -->
    <div class="data-card">
        <div class="table-responsive">
            <table id="populasiTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kecamatan</th>
                        <th>Jml Desa</th>
                        <th>Sapi Bali</th>
                        <th>Kerbau</th>
                        <th>Kuda</th>
                        <th>Babi Bali</th>
                        <th>Babi Landrace</th>
                        <th>Kambing</th>
                        <th>Unggas</th>
                        <th>Anjing</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="13" class="text-center">Tidak ada data populasi untuk periode ini</td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td>
                                    <strong><?php echo $row['nama_kecamatan']; ?></strong>
                                </td>
                                <td class="text-center"><?php echo $row['jumlah_desa']; ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?php echo number_format($row['total_sapi_bali'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?php echo number_format($row['total_kerbau'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?php echo number_format($row['total_kuda'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger"><?php echo number_format($row['total_babi_bali'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger"><?php echo number_format($row['total_babi_landrace'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success"><?php echo number_format($row['total_kambing'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?php echo number_format($row['total_unggas'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-dark"><?php echo number_format($row['total_anjing'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center">
                                    <strong><?php echo number_format($row['total_populasi'], 0, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" 
                                                onclick="window.location.href='?module=populasi&action=detail&kecamatan=<?php echo $row['nama_kecamatan']; ?>&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>'"
                                                title="Detail Desa">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn-action btn-edit" 
                                                onclick="window.location.href='?module=populasi&action=edit&kecamatan_id=<?php echo getKecamatanIdByName($row['nama_kecamatan']); ?>&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>'"
                                                title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </button>
<a class="btn-action btn-delete"
   onclick="return confirm('Yakin ingin menghapus SEMUA data populasi <?= $row['nama_kecamatan'] ?> bulan <?= $bulan ?>/<?= $tahun ?>? (Semua desa akan terhapus)')"
   href="?module=populasi&action=delete&kecamatan_id=<?= getKecamatanIdByName($row['nama_kecamatan']); ?>&bulan=<?= $bulan; ?>&tahun=<?= $tahun; ?>"
   title="Hapus Data">
   <i class="fas fa-trash"></i>
</a>


                                        <!-- <button class="btn-action btn-report" 
                                                onclick="window.location.href='?module=populasi&action=report&kecamatan=<?php echo urlencode($row['nama_kecamatan']); ?>'"
                                                title="Laporan">
                                            <i class="fas fa-chart-bar"></i>
                                        </button> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="2">TOTAL</td>
                        <td class="text-center"><?php echo $summary['total_desa'] ?? 0; ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_sapi'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_kerbau'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_kuda'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center" colspan="2"><?php echo number_format($summary['total_babi'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_kambing'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_unggas'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_anjing'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($summary['total_semua_hewan'] ?? 0, 0, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
// Chart.js untuk distribusi populasi
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('populationChart').getContext('2d');
    const chartData = {
        labels: [
            <?php foreach ($chartData as $item): ?>
                '<?php echo $item['jenis']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($chartData as $item): ?>
                    <?php echo $item['total']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#8AC926', '#1982C4'
            ],
            borderWidth: 1
        }]
    };
    
    const populationChart = new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.toLocaleString('id-ID') + ' ekor';
                            return label;
                        }
                    }
                }
            }
        }
    });
});

function resetFilter() {
    window.location.href = '?module=populasi&action=data';
}

// Helper function untuk get kecamatan id
function getKecamatanIdByName(name) {
    const kecamatanList = <?php echo json_encode($kecamatanList); ?>;
    const kecamatan = kecamatanList.find(k => k.nama_kecamatan === name);
    return kecamatan ? kecamatan.id : 0;
}
</script>

<?php
// Helper function
function getKecamatanIdByName($nama) {
    $sql = "SELECT id FROM kecamatan WHERE nama_kecamatan = ?";
    $result = fetchOne($sql, [$nama]);
    return $result['id'] ?? 0;
}
?>