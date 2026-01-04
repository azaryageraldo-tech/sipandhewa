<?php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$kecamatan = $_GET['kecamatan'] ?? '';
$jenis_hewan = $_GET['jenis_hewan'] ?? '';
$jenis_vaksin = $_GET['jenis_vaksin'] ?? '';

// Build query
$sql = "SELECT v.*, k.nama_kecamatan, d.nama_desa, u.fullname as petugas 
        FROM vaksinasi v 
        LEFT JOIN kecamatan k ON v.kecamatan_id = k.id 
        LEFT JOIN desa d ON v.desa_id = d.id 
        LEFT JOIN users u ON v.created_by = u.id 
        WHERE v.tanggal_vaksinasi BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($kecamatan) {
    $sql .= " AND k.nama_kecamatan = ?";
    $params[] = $kecamatan;
}

if ($jenis_hewan) {
    $sql .= " AND v.jenis_hewan = ?";
    $params[] = $jenis_hewan;
}

if ($jenis_vaksin) {
    $sql .= " AND v.jenis_vaksin = ?";
    $params[] = $jenis_vaksin;
}

$sql .= " ORDER BY v.tanggal_vaksinasi DESC, k.nama_kecamatan, d.nama_desa";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(*) as total_vaksinasi,
                COUNT(DISTINCT nama_pemilik) as total_pemilik,
                COUNT(DISTINCT kecamatan_id) as kecamatan_terjangkau,
                COUNT(DISTINCT jenis_hewan) as jenis_hewan_divaksin
               FROM vaksinasi 
               WHERE tanggal_vaksinasi BETWEEN ? AND ?";
$summary = fetchOne($summarySql, [$start_date, $end_date]);

// Get vaccination statistics by animal type
$statsByAnimalSql = "SELECT jenis_hewan, COUNT(*) as jumlah,
                     GROUP_CONCAT(DISTINCT jenis_vaksin) as jenis_vaksin
                     FROM vaksinasi 
                     WHERE tanggal_vaksinasi BETWEEN ? AND ?
                     GROUP BY jenis_hewan 
                     ORDER BY jumlah DESC";
$statsByAnimal = fetchAll($statsByAnimalSql, [$start_date, $end_date]);

// Get vaccination statistics by vaccine type
$statsByVaccineSql = "SELECT jenis_vaksin, COUNT(*) as jumlah,
                      GROUP_CONCAT(DISTINCT jenis_hewan) as jenis_hewan
                      FROM vaksinasi 
                      WHERE tanggal_vaksinasi BETWEEN ? AND ?
                      GROUP BY jenis_vaksin 
                      ORDER BY jumlah DESC";
$statsByVaccine = fetchAll($statsByVaccineSql, [$start_date, $end_date]);
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-list"></i> Data Vaksinasi Hewan</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('vaksinasiTable', 'data-vaksinasi')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <!-- <button class="btn btn-primary" onclick="generateVaccinationReport()">
                <i class="fas fa-file-medical-alt"></i> Laporan Vaksinasi
            </button> -->
            <button class="btn btn-primary" onclick="window.location.href='?module=vaksinasi&action=input'">
                <i class="fas fa-plus"></i> Input Data Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-syringe"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_vaksinasi'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Vaksinasi</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_pemilik'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Pemilik Terlayani</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['kecamatan_terjangkau'] ?? 0; ?></h3>
                <p>Kecamatan Terjangkau</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['jenis_hewan_divaksin'] ?? 0; ?></h3>
                <p>Jenis Hewan</p>
            </div>
        </div>
    </div>

    <!-- Statistics Tables -->
    <div class="stats-row">
        <div class="stats-card">
            <h4><i class="fas fa-dog"></i> Vaksinasi per Jenis Hewan</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jenis Hewan</th>
                        <th>Jumlah</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statsByAnimal as $stat): ?>
                        <?php 
                        $percentage = $summary['total_vaksinasi'] > 0 ? 
                                    ($stat['jumlah'] / $summary['total_vaksinasi']) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo ucfirst($stat['jenis_hewan']); ?></strong>
                                <br><small><?php echo $stat['jenis_vaksin']; ?></small>
                            </td>
                            <td class="text-center"><?php echo $stat['jumlah']; ?></td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
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
        
        <div class="stats-card">
            <h4><i class="fas fa-vial"></i> Vaksinasi per Jenis Vaksin</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jenis Vaksin</th>
                        <th>Jumlah</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statsByVaccine as $stat): ?>
                        <?php 
                        $percentage = $summary['total_vaksinasi'] > 0 ? 
                                    ($stat['jumlah'] / $summary['total_vaksinasi']) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo ucfirst($stat['jenis_vaksin']); ?></strong>
                                <br><small><?php echo $stat['jenis_hewan']; ?></small>
                            </td>
                            <td class="text-center"><?php echo $stat['jumlah']; ?></td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
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
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="vaksinasi">
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
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="jenis_hewan">Jenis Hewan</label>
                    <select id="jenis_hewan" name="jenis_hewan">
                        <option value="">Semua Hewan</option>
                        <option value="anjing" <?php echo $jenis_hewan == 'anjing' ? 'selected' : ''; ?>>Anjing</option>
                        <option value="kucing" <?php echo $jenis_hewan == 'kucing' ? 'selected' : ''; ?>>Kucing</option>
                        <option value="sapi" <?php echo $jenis_hewan == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                        <option value="kambing" <?php echo $jenis_hewan == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                        <option value="ayam" <?php echo $jenis_hewan == 'ayam' ? 'selected' : ''; ?>>Ayam</option>
                        <option value="babi" <?php echo $jenis_hewan == 'babi' ? 'selected' : ''; ?>>Babi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jenis_vaksin">Jenis Vaksin</label>
                    <select id="jenis_vaksin" name="jenis_vaksin">
                        <option value="">Semua Vaksin</option>
                        <option value="rabies" <?php echo $jenis_vaksin == 'rabies' ? 'selected' : ''; ?>>Rabies</option>
                        <option value="antraks" <?php echo $jenis_vaksin == 'antraks' ? 'selected' : ''; ?>>Antraks</option>
                        <option value="pmk" <?php echo $jenis_vaksin == 'pmk' ? 'selected' : ''; ?>>PMK</option>
                        <option value="newcastle" <?php echo $jenis_vaksin == 'newcastle' ? 'selected' : ''; ?>>Newcastle</option>
                        <option value="gumboro" <?php echo $jenis_vaksin == 'gumboro' ? 'selected' : ''; ?>>Gumboro</option>
                        <option value="brucellosis" <?php echo $jenis_vaksin == 'brucellosis' ? 'selected' : ''; ?>>Brucellosis</option>
                        <option value="lainnya" <?php echo $jenis_vaksin == 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
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
            <table id="vaksinasiTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pemilik</th>
                        <th>Lokasi</th>
                        <th>Hewan</th>
                        <th>Vaksin</th>
                        <th>Umur</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data vaksinasi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $index => $row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_vaksinasi'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_pemilik']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['nama_desa']); ?>, 
                                    <?php echo htmlspecialchars($row['nama_kecamatan']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo ucfirst($row['jenis_hewan']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php echo ucfirst($row['jenis_vaksin']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['umur_hewan'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['petugas'] ?: '-'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" 
                                                onclick="editData(<?php echo $row['id']; ?>)"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                      <a href="modules/vaksinasi/delete.php?id=<?= $row['id'] ?>" class="btn-action btn-delete"
                                        onclick="return confirm('Yakin hapus data ini?')"
                                        class="btn btn-danger btn-sm">
                                              <i class="fas fa-trash"></i>
                                      </a>
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
    document.getElementById('jenis_vaksin').value = '';
    document.querySelector('.filter-form').submit();
}

function generateVaccinationReport() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const kecamatan = document.getElementById('kecamatan').value;
    
    const params = new URLSearchParams({
        action: 'generate_report',
        start_date: startDate,
        end_date: endDate,
        kecamatan: kecamatan
    });
    
    window.open('modules/vaksinasi/process.php?' + params.toString(), '_blank');
}

function editData(id) {
    window.location.href = '?module=vaksinasi&action=edit&id=' + id;
}

function viewDetails(id) {
    window.location.href = '?module=vaksinasi&action=detail&id=' + id;
}
</script>

<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stats-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stats-card h4 {
    margin-bottom: 15px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>