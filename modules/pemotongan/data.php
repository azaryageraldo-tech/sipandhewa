<?php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$kecamatan = $_GET['kecamatan'] ?? '';
$jenis_hewan = $_GET['jenis_hewan'] ?? '';

// Build query
$sql = "SELECT p.*, k.nama_kecamatan, u.fullname as petugas 
        FROM pemotongan p 
        LEFT JOIN kecamatan k ON p.kecamatan_id = k.id 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.tanggal_pemotongan BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($kecamatan) {
    $sql .= " AND k.nama_kecamatan = ?";
    $params[] = $kecamatan;
}

if ($jenis_hewan) {
    $sql .= " AND p.jenis_hewan = ?";
    $params[] = $jenis_hewan;
}

$sql .= " ORDER BY p.tanggal_pemotongan DESC, k.nama_kecamatan";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(*) as total_record,
                SUM(jantan) as total_jantan,
                SUM(betina) as total_betina,
                SUM(total) as total_hewan,
                COUNT(DISTINCT kecamatan_id) as kecamatan_terdata
               FROM pemotongan 
               WHERE tanggal_pemotongan BETWEEN ? AND ?";
$summary = fetchOne($summarySql, [$start_date, $end_date]);

// Get statistics by animal type
$statsByAnimalSql = "SELECT jenis_hewan, 
                     SUM(jantan) as total_jantan,
                     SUM(betina) as total_betina,
                     SUM(total) as total
                     FROM pemotongan 
                     WHERE tanggal_pemotongan BETWEEN ? AND ?
                     GROUP BY jenis_hewan 
                     ORDER BY total DESC";
$statsByAnimal = fetchAll($statsByAnimalSql, [$start_date, $end_date]);
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-chart-bar"></i> Data Pemotongan Hewan</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('pemotonganTable', 'data-pemotongan')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary" onclick="window.location.href='?module=pemotongan&action=input'">
                <i class="fas fa-plus"></i> Input  Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_record'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Record</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_hewan'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Hewan Dipotong</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-venus"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_jantan'] ?? 0, 0, ',', '.'); ?> J</h3>
                <p><?php echo number_format($summary['total_betina'] ?? 0, 0, ',', '.'); ?> B</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['kecamatan_terdata'] ?? 0; ?></h3>
                <p>Kecamatan</p>
            </div>
        </div>
    </div>

    <!-- Animal Type Statistics -->
    <div class="stats-table">
        <h3><i class="fas fa-chart-pie"></i> Statistik per Jenis Hewan</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis Hewan</th>
                    <th>Jantan</th>
                    <th>Betina</th>
                    <th>Total</th>
                    <th>% dari Total</th>
                    <th>Distribusi Gender</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statsByAnimal as $stat): ?>
                    <?php 
                    $percentage = $summary['total_hewan'] > 0 ? ($stat['total'] / $summary['total_hewan']) * 100 : 0;
                    $genderRatio = $stat['total'] > 0 ? ($stat['total_jantan'] / $stat['total']) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <strong>
                                <?php echo ucwords(str_replace('_', ' ', $stat['jenis_hewan'])); ?>
                            </strong>
                        </td>
                        <td class="text-center"><?php echo number_format($stat['total_jantan'], 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo number_format($stat['total_betina'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <strong><?php echo number_format($stat['total'], 0, ',', '.'); ?></strong>
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                    <?php echo number_format($percentage, 1); ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="gender-distribution">
                                <div class="gender-bar">
                                    <div class="gender-male" style="width: <?php echo $genderRatio; ?>%">
                                        <?php if ($genderRatio > 10): ?>
                                            <?php echo number_format($genderRatio, 0); ?>%
                                        <?php endif; ?>
                                    </div>
                                    <div class="gender-female" style="width: <?php echo 100 - $genderRatio; ?>%">
                                        <?php if ((100 - $genderRatio) > 10): ?>
                                            <?php echo number_format(100 - $genderRatio, 0); ?>%
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="gender-labels">
                                    <span class="gender-label male">J</span>
                                    <span class="gender-label female">B</span>
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
            <input type="hidden" name="module" value="pemotongan">
            <input type="hidden" name="action" value="data">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">Tanggal Akhir</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                
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
                    <label for="jenis_hewan">Jenis Hewan</label>
                    <select id="jenis_hewan" name="jenis_hewan">
                        <option value="">Semua Jenis</option>
                        <option value="sapi" <?php echo $jenis_hewan == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                        <option value="kerbau" <?php echo $jenis_hewan == 'kerbau' ? 'selected' : ''; ?>>Kerbau</option>
                        <option value="ayam_pedaging" <?php echo $jenis_hewan == 'ayam_pedaging' ? 'selected' : ''; ?>>Ayam Pedaging</option>
                        <option value="ayam_petelur" <?php echo $jenis_hewan == 'ayam_petelur' ? 'selected' : ''; ?>>Ayam Petelur</option>
                        <option value="ayam_buras" <?php echo $jenis_hewan == 'ayam_buras' ? 'selected' : ''; ?>>Ayam Buras</option>
                        <option value="itik" <?php echo $jenis_hewan == 'itik' ? 'selected' : ''; ?>>Itik/Bebek</option>
                        <option value="kambing" <?php echo $jenis_hewan == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                        <option value="domba" <?php echo $jenis_hewan == 'domba' ? 'selected' : ''; ?>>Domba</option>
                        <option value="babi" <?php echo $jenis_hewan == 'babi' ? 'selected' : ''; ?>>Babi</option>
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
            <table id="pemotonganTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kecamatan</th>
                        <th>Jenis Hewan</th>
                        <th>Jantan</th>
                        <th>Betina</th>
                        <th>Total</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data pemotongan</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $groupedData = [];
                        foreach ($data as $row) {
                            $key = $row['tanggal_pemotongan'] . '_' . $row['nama_kecamatan'];
                            if (!isset($groupedData[$key])) {
                                $groupedData[$key] = [
                                    'tanggal' => $row['tanggal_pemotongan'],
                                    'kecamatan' => $row['nama_kecamatan'],
                                    'petugas' => $row['petugas'],
                                    'animals' => []
                                ];
                            }
                            $groupedData[$key]['animals'][] = $row;
                        }
                        
                        $counter = 0;
                        foreach ($groupedData as $group):
                            $animals = $group['animals'];
                            $animalCount = count($animals);
                            $counter++;
                        ?>
                            <?php foreach ($animals as $index => $row): ?>
                                <tr>
                                    <?php if ($index === 0): ?>
                                        <td rowspan="<?php echo $animalCount; ?>">
                                            <?php echo $counter; ?>
                                        </td>
                                        <td rowspan="<?php echo $animalCount; ?>">
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_pemotongan'])); ?>
                                        </td>
                                        <td rowspan="<?php echo $animalCount; ?>">
                                            <strong><?php echo $row['nama_kecamatan']; ?></strong>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo ucwords(str_replace('_', ' ', $row['jenis_hewan'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?php echo $row['jantan']; ?></td>
                                    <td class="text-center"><?php echo $row['betina']; ?></td>
                                    <td class="text-center">
                                        <strong><?php echo $row['total']; ?></strong>
                                    </td>
                                    
                                    <?php if ($index === 0): ?>
                                        <td rowspan="<?php echo $animalCount; ?>">
                                            <?php echo htmlspecialchars($row['petugas'] ?: '-'); ?>
                                        </td>
                                        <td rowspan="<?php echo $animalCount; ?>">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit" 
                                                        onclick="editGroupData('<?php echo $row['tanggal_pemotongan']; ?>', '<?php echo $row['nama_kecamatan']; ?>')"
                                                        title="Edit Group">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-action btn-delete" 
                                                        onclick="deleteGroupData('<?php echo $row['tanggal_pemotongan']; ?>', '<?php echo $row['nama_kecamatan']; ?>')"
                                                        title="Hapus Group">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn-action btn-view" 
                                                        onclick="viewGroupDetails('<?php echo $row['tanggal_pemotongan']; ?>', '<?php echo $row['nama_kecamatan']; ?>')"
                                                        title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($data)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                            <td class="text-center"><strong><?php echo number_format($summary['total_jantan'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td class="text-center"><strong><?php echo number_format($summary['total_betina'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td class="text-center"><strong><?php echo number_format($summary['total_hewan'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Statistics Summary -->
        <div class="statistics-summary">
            <h4><i class="fas fa-chart-line"></i> Analisis Data</h4>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label">Rata-rata per Hari:</span>
                    <span class="stat-value">
                        <?php 
                        $days = date_diff(date_create($start_date), date_create($end_date))->days + 1;
                        $avgPerDay = $days > 0 ? ($summary['total_hewan'] ?? 0) / $days : 0;
                        echo number_format($avgPerDay, 1);
                        ?>
                    </span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Rasio Jantan:Betina:</span>
                    <span class="stat-value">
                        <?php 
                        $jantan = $summary['total_jantan'] ?? 0;
                        $betina = $summary['total_betina'] ?? 0;
                        echo number_format($jantan, 0) . ':' . number_format($betina, 0);
                        ?>
                    </span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">% Jantan:</span>
                    <span class="stat-value">
                        <?php 
                        $total = $summary['total_hewan'] ?? 1;
                        $jantanPercentage = $total > 0 ? ($jantan / $total) * 100 : 0;
                        echo number_format($jantanPercentage, 1) . '%';
                        ?>
                    </span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Hewan Terbanyak:</span>
                    <span class="stat-value">
                        <?php 
                        if (!empty($statsByAnimal)) {
                            $topAnimal = $statsByAnimal[0];
                            echo ucwords(str_replace('_', ' ', $topAnimal['jenis_hewan'])) . ' (' . number_format($topAnimal['total'], 0, ',', '.') . ')';
                        } else {
                            echo '-';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetFilter() {
    const today = new Date().toISOString().split('T')[0];
    const firstDay = today.substring(0, 8) + '01';
    
    document.getElementById('start_date').value = firstDay;
    document.getElementById('end_date').value = today;
    document.getElementById('kecamatan').value = '';
    document.getElementById('jenis_hewan').value = '';
    document.querySelector('.filter-form').submit();
}

function generateSlaughterReport() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const kecamatan = document.getElementById('kecamatan').value;
    
    const params = new URLSearchParams({
        action: 'generate_report',
        start_date: startDate,
        end_date: endDate,
        kecamatan: kecamatan
    });
    
    window.open('modules/pemotongan/process.php?' + params.toString(), '_blank');
}

function editGroupData(tanggal, kecamatan) {
    window.location.href = '?module=pemotongan&action=edit&tanggal=' + tanggal + '&kecamatan=' + encodeURIComponent(kecamatan);
}
function deleteGroupData(tanggal, kecamatan) {
    if (confirm(
        'Apakah Anda yakin ingin menghapus semua data pemotongan untuk ' +
        kecamatan + ' pada tanggal ' + tanggal + '?'
    )) {
        window.location.href =
            'modules/pemotongan/delete.php?' +
            'tanggal=' + encodeURIComponent(tanggal) +
            '&kecamatan=' + encodeURIComponent(kecamatan);
    }
}

function viewGroupDetails(tanggal, kecamatan) {
    window.location.href = '?module=pemotongan&action=detail&tanggal=' + tanggal + '&kecamatan=' + encodeURIComponent(kecamatan);
}
function showGroupDetailModal(data, tanggal, kecamatan) {
    let animalRows = '';
    let totalJantan = 0;
    let totalBetina = 0;
    let totalAll = 0;
    
    data.forEach(item => {
        totalJantan += parseInt(item.jantan);
        totalBetina += parseInt(item.betina);
        totalAll += parseInt(item.total);
        
        animalRows += `
            <tr>
                <td>${item.jenis_hewan.replace(/_/g, ' ')}</td>
                <td class="text-center">${parseInt(item.jantan).toLocaleString('id-ID')}</td>
                <td class="text-center">${parseInt(item.betina).toLocaleString('id-ID')}</td>
                <td class="text-center"><strong>${parseInt(item.total).toLocaleString('id-ID')}</strong></td>
            </tr>
        `;
    });
    
    const modalHtml = `
        <div class="modal-overlay">
            <div class="modal modal-lg">
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle"></i> Detail Pemotongan</h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="detail-header">
                        <div class="detail-item">
                            <strong>Kecamatan:</strong> ${kecamatan}
                        </div>
                        <div class="detail-item">
                            <strong>Tanggal:</strong> ${new Date(tanggal).toLocaleDateString('id-ID')}
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Jenis Hewan</th>
                                    <th>Jantan</th>
                                    <th>Betina</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${animalRows}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-center"><strong>${totalJantan.toLocaleString('id-ID')}</strong></td>
                                    <td class="text-center"><strong>${totalBetina.toLocaleString('id-ID')}</strong></td>
                                    <td class="text-center"><strong>${totalAll.toLocaleString('id-ID')}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="summary-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Jantan:</span>
                            <span class="stat-value">${totalJantan.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Betina:</span>
                            <span class="stat-value">${totalBetina.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Grand Total:</span>
                            <span class="stat-value grand">${totalAll.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Rasio J:B:</span>
                            <span class="stat-value">
                                ${((totalJantan / totalAll) * 100).toFixed(1)}% : ${((totalBetina / totalAll) * 100).toFixed(1)}%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="editGroupData('${tanggal}', '${kecamatan}')">
                        <i class="fas fa-edit"></i> Edit Data
                    </button>
                    <button class="btn btn-secondary" onclick="closeModal()">Tutup</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}
</script>

<style>
.gender-distribution {
    margin-top: 5px;
}

.gender-bar {
    height: 20px;
    display: flex;
    border-radius: 4px;
    overflow: hidden;
}

.gender-male {
    background-color: #007bff;
    color: white;
    text-align: center;
    font-size: 12px;
    line-height: 20px;
}

.gender-female {
    background-color: #e83e8c;
    color: white;
    text-align: center;
    font-size: 12px;
    line-height: 20px;
}

.gender-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 2px;
    font-size: 11px;
    color: #6c757d;
}

.gender-label.male {
    color: #007bff;
    font-weight: bold;
}

.gender-label.female {
    color: #e83e8c;
    font-weight: bold;
}

.statistics-summary {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.statistics-summary h4 {
    margin-bottom: 15px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border-left: 4px solid #007bff;
}

.stat-label {
    font-weight: 500;
    color: #495057;
}

.stat-value {
    font-weight: bold;
    color: #007bff;
}

.stat-value.grand {
    color: #28a745;
    font-size: 1.1em;
}

.modal-lg {
    max-width: 800px;
}

.detail-header {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-item {
    font-size: 16px;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 20px;
    padding: 15px;
    background: #e9ecef;
    border-radius: 6px;
}
</style>