<?php
// modules/survei_pasar/data.php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$lokasi = $_GET['lokasi'] ?? '';
$komoditas = $_GET['komoditas'] ?? '';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-d', strtotime('-1 month'));
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

// Build query
$sql = "SELECT s.*, u.fullname as petugas 
        FROM survei_pasar s 
        LEFT JOIN users u ON s.created_by = u.id 
        WHERE s.tanggal_survei BETWEEN ? AND ?";
$params = [$tanggal_mulai, $tanggal_akhir];

if ($lokasi) {
    $sql .= " AND s.lokasi_pasar = ?";
    $params[] = $lokasi;
}

if ($komoditas) {
    $sql .= " AND s.komoditas = ?";
    $params[] = $komoditas;
}

$sql .= " ORDER BY s.tanggal_survei DESC, s.lokasi_pasar, s.komoditas";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(DISTINCT lokasi_pasar) as total_lokasi,
                COUNT(*) as total_survei,
                ROUND(AVG(
                    CASE 
                        WHEN komoditas = 'Daging Ayam' THEN COALESCE(harga_ayam_utuh, 0)
                        WHEN komoditas = 'Daging Babi' THEN COALESCE(harga_babi_utuh, 0)
                        WHEN komoditas = 'Daging Sapi' THEN COALESCE(harga_sapi_isi, 0)
                    END
                ), 0) as avg_harga
               FROM survei_pasar 
               WHERE tanggal_survei BETWEEN ? AND ?";
$summary = fetchOne($summarySql, [$tanggal_mulai, $tanggal_akhir]);

// Get data for charts
$chartSql = "SELECT 
                komoditas,
                lokasi_pasar,
                DATE(tanggal_survei) as tanggal,
                COALESCE(harga_ayam_utuh, harga_babi_utuh, harga_sapi_isi) as harga
             FROM survei_pasar 
             WHERE tanggal_survei BETWEEN ? AND ?
             ORDER BY tanggal_survei";
$chartData = fetchAll($chartSql, [$tanggal_mulai, $tanggal_akhir]);
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-chart-bar"></i> Data Survei Harga Pasar</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('surveiTable', 'data-survei-pasar')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary" onclick="printTable('surveiTable')">
                <i class="fas fa-print"></i> Cetak
            </button>
            <button class="btn btn-primary" onclick="window.location.href='?module=survei_pasar&action=input'">
                <i class="fas fa-plus"></i> Input Survei Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #4CAF50;">
                <i class="fas fa-store"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['total_lokasi'] ?? 0; ?></h3>
                <p>Lokasi Pasar</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #2196F3;">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_survei'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Survei</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #FF9800;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="summary-content">
                <h3>Rp<?php echo number_format($summary['avg_harga'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Rata-rata Harga</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #9C27B0;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo date('d/m/Y', strtotime($tanggal_mulai)); ?></h3>
                <p><?php echo date('d/m/Y', strtotime($tanggal_akhir)); ?></p>
                <small>Periode Survei</small>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="survei_pasar">
            <input type="hidden" name="action" value="data">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="lokasi">Lokasi Pasar</label>
                    <select id="lokasi" name="lokasi">
                        <option value="">Semua Pasar</option>
                        <option value="Pasar Banyuasri" <?php echo $lokasi == 'Pasar Banyuasri' ? 'selected' : ''; ?>>Pasar Banyuasri</option>
                        <option value="Pasar Anyar" <?php echo $lokasi == 'Pasar Anyar' ? 'selected' : ''; ?>>Pasar Anyar</option>
                        <option value="Pasar Buleleng" <?php echo $lokasi == 'Pasar Buleleng' ? 'selected' : ''; ?>>Pasar Buleleng</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="komoditas">Komoditas</label>
                    <select id="komoditas" name="komoditas">
                        <option value="">Semua Komoditas</option>
                        <option value="Daging Ayam" <?php echo $komoditas == 'Daging Ayam' ? 'selected' : ''; ?>>Daging Ayam</option>
                        <option value="Daging Babi" <?php echo $komoditas == 'Daging Babi' ? 'selected' : ''; ?>>Daging Babi</option>
                        <option value="Daging Sapi" <?php echo $komoditas == 'Daging Sapi' ? 'selected' : ''; ?>>Daging Sapi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" 
                           value="<?php echo $tanggal_mulai; ?>">
                </div>
                
                <div class="form-group">
                    <label for="tanggal_akhir">Tanggal Akhir</label>
                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" 
                           value="<?php echo $tanggal_akhir; ?>">
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
            <table id="surveiTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Lokasi Pasar</th>
                        <th>Komoditas</th>
                        <th>Harga (Rp/kg)</th>
                        <th>Surveilens</th>
                        <th>HP</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data survei</td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_survei'])); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-store"></i> <?php echo $row['lokasi_pasar']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $icon = '';
                                    $harga = '';
                                    
                                    switch($row['komoditas']) {
                                        case 'Daging Ayam':
                                            $icon = 'fas fa-drumstick-bite';
                                            $harga = $row['harga_ayam_utuh'] > 0 ? 'Ayam Utuh: Rp' . number_format($row['harga_ayam_utuh'], 0, ',', '.') : 
                                                    ($row['harga_dada_ayam'] > 0 ? 'Dada: Rp' . number_format($row['harga_dada_ayam'], 0, ',', '.') : '-');
                                            break;
                                        case 'Daging Babi':
                                            $icon = 'fas fa-bacon';
                                            if ($row['harga_babi_utuh'] > 0) $harga .= 'Utuh: Rp' . number_format($row['harga_babi_utuh'], 0, ',', '.') . '<br>';
                                            if ($row['harga_balung_babi'] > 0) $harga .= 'Balung: Rp' . number_format($row['harga_balung_babi'], 0, ',', '.') . '<br>';
                                            if ($row['harga_babi_isi'] > 0) $harga .= 'Isi: Rp' . number_format($row['harga_babi_isi'], 0, ',', '.');
                                            if (!$harga) $harga = '-';
                                            break;
                                        case 'Daging Sapi':
                                            $icon = 'fas fa-drumstick-bite';
                                            if ($row['harga_balung_sapi'] > 0) $harga .= 'Balung: Rp' . number_format($row['harga_balung_sapi'], 0, ',', '.') . '<br>';
                                            if ($row['harga_sapi_isi'] > 0) $harga .= 'Isi: Rp' . number_format($row['harga_sapi_isi'], 0, ',', '.');
                                            if (!$harga) $harga = '-';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-secondary">
                                        <i class="<?php echo $icon; ?>"></i> <?php echo $row['komoditas']; ?>
                                    </span>
                                </td>
                                <td style="text-align: left;"><?php echo $harga; ?></td>
                                <td><?php echo $row['nama_surveilens'] ?: '-'; ?></td>
                                <td><?php echo $row['nomor_hp'] ?: '-'; ?></td>
                                <td><?php echo $row['petugas'] ?: '-'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" 
                                                onclick="window.location.href='?module=survei_pasar&action=edit&id=<?php echo $row['id']; ?>'"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                      <a href="modules/survei_pasar/delete.php?id=<?= $row['id'] ?>" class="btn-action btn-delete"
                                        onclick="return confirm('Yakin hapus data ini?')"
                                        class="btn btn-danger btn-sm">
                                              <i class="fas fa-trash"></i>
                                      </a>

                                     
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistics -->
    <div class="statistics-card">
        <h4><i class="fas fa-chart-line"></i> Statistik Harga</h4>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Ayam Utuh:</span>
                <span class="stat-value">
                    <?php 
                    $sql_ayam = "SELECT ROUND(AVG(harga_ayam_utuh), 0) as avg_harga FROM survei_pasar 
                                WHERE tanggal_survei BETWEEN ? AND ? AND komoditas = 'Daging Ayam' AND harga_ayam_utuh > 0";
                    $avg_ayam = fetchOne($sql_ayam, [$tanggal_mulai, $tanggal_akhir]);
                    echo 'Rp' . number_format($avg_ayam['avg_harga'] ?? 0, 0, ',', '.');
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Babi Utuh:</span>
                <span class="stat-value">
                    <?php 
                    $sql_babi = "SELECT ROUND(AVG(harga_babi_utuh), 0) as avg_harga FROM survei_pasar 
                                WHERE tanggal_survei BETWEEN ? AND ? AND komoditas = 'Daging Babi' AND harga_babi_utuh > 0";
                    $avg_babi = fetchOne($sql_babi, [$tanggal_mulai, $tanggal_akhir]);
                    echo 'Rp' . number_format($avg_babi['avg_harga'] ?? 0, 0, ',', '.');
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Sapi Isi:</span>
                <span class="stat-value">
                    <?php 
                    $sql_sapi = "SELECT ROUND(AVG(harga_sapi_isi), 0) as avg_harga FROM survei_pasar 
                                WHERE tanggal_survei BETWEEN ? AND ? AND komoditas = 'Daging Sapi' AND harga_sapi_isi > 0";
                    $avg_sapi = fetchOne($sql_sapi, [$tanggal_mulai, $tanggal_akhir]);
                    echo 'Rp' . number_format($avg_sapi['avg_harga'] ?? 0, 0, ',', '.');
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteData(id, module) {
    if (!confirm('Yakin ingin menghapus data ini?')) {
        return;
    }

    window.location.href =
        '?module=' + module +
        '&action=delete&id=' + id;
}
function resetFilter() {
    window.location.href = '?module=survei_pasar&action=data';
}

function viewDetails(id) {
    // Redirect to detail page or show modal
    window.location.href = '?module=survei_pasar&action=detail&id=' + id;
}
</script>