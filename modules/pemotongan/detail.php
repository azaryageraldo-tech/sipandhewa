<?php
require_once __DIR__ . '/../../includes/functions.php';

// Ambil parameter
$tanggal = $_GET['tanggal'] ?? '';
$kecamatan = $_GET['kecamatan'] ?? '';

if (!$tanggal || !$kecamatan) {
    echo '<div class="alert alert-danger">Parameter tidak lengkap</div>';
    exit();
}

try {
    // Ambil kecamatan ID
    $kecamatanId = getKecamatanId($kecamatan);
    
    // Query untuk mendapatkan data lengkap
    $sql = "SELECT p.*, 
                   k.nama_kecamatan, 
                   u.fullname as petugas_input
            FROM pemotongan p 
            JOIN kecamatan k ON p.kecamatan_id = k.id 
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.tanggal_pemotongan = ? AND p.kecamatan_id = ?
            ORDER BY p.jenis_hewan";
    
    $data = fetchAll($sql, [$tanggal, $kecamatanId]);
    
    if (empty($data)) {
        echo '<div class="alert alert-danger">Data pemotongan tidak ditemukan</div>';
        exit();
    }
    
    // Format tanggal
    $tanggal_formatted = date('d F Y', strtotime($tanggal));
    
    // Hitung total
    $totalJantan = 0;
    $totalBetina = 0;
    $grandTotal = 0;
    
    foreach ($data as $row) {
        $totalJantan += $row['jantan'];
        $totalBetina += $row['betina'];
        $grandTotal += $row['total'];
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    exit();
}

// Ikon untuk jenis hewan
$animal_icons = [
    'sapi' => 'fa-cow',
    'kerbau' => 'fa-bull',
    'ayam_pedaging' => 'fa-drumstick-bite',
    'ayam_petelur' => 'fa-egg',
    'ayam_buras' => 'fa-dove',
    'itik' => 'fa-kiwi-bird',
    'kambing' => 'fa-sheep',
    'domba' => 'fa-sheep',
    'babi' => 'fa-piggy-bank'
];

// Nama hewan lengkap
$animal_names = [
    'sapi' => 'Sapi',
    'kerbau' => 'Kerbau',
    'ayam_pedaging' => 'Ayam Ras Pedaging',
    'ayam_petelur' => 'Ayam Ras Petelur',
    'ayam_buras' => 'Ayam Buras',
    'itik' => 'Itik/Bebek',
    'kambing' => 'Kambing',
    'domba' => 'Domba',
    'babi' => 'Babi'
];
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-eye"></i> Detail Data Pemotongan</h2>
        <div class="header-actions">
            <a href="?module=pemotongan&action=edit&tanggal=<?php echo $tanggal; ?>&kecamatan=<?php echo urlencode($kecamatan); ?>" 
               class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="?module=pemotongan&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>

    <div class="detail-container">
        <!-- Header dengan informasi -->
        <div class="detail-header">
            <div class="detail-info">
                <h3>Data Pemotongan Hewan</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span><strong>Tanggal:</strong> <?php echo $tanggal_formatted; ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-landmark"></i>
                        <span><strong>Kecamatan:</strong> <?php echo htmlspecialchars($kecamatan); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user-md"></i>
                        <span><strong>Petugas:</strong> <?php echo htmlspecialchars($data[0]['petugas_input'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-summary">
                <div class="summary-item">
                    <span class="summary-label">Total Jantan</span>
                    <span class="summary-value male"><?php echo number_format($totalJantan, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Betina</span>
                    <span class="summary-value female"><?php echo number_format($totalBetina, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Grand Total</span>
                    <span class="summary-value grand"><?php echo number_format($grandTotal, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <!-- Tabel Detail -->
        <div class="data-card">
            <h4><i class="fas fa-list"></i> Rincian per Jenis Hewan</h4>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Jenis Hewan</th>
                            <th class="text-center">Jantan</th>
                            <th class="text-center">Betina</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">% dari Total</th>
                            <th>Distribusi Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <?php 
                            $percentage = $grandTotal > 0 ? ($row['total'] / $grandTotal) * 100 : 0;
                            $genderRatio = $row['total'] > 0 ? ($row['jantan'] / $row['total']) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="animal-type">
                                        <i class="fas <?php echo $animal_icons[$row['jenis_hewan']] ?? 'fa-paw'; ?>"></i>
                                        <strong><?php echo $animal_names[$row['jenis_hewan']] ?? ucfirst(str_replace('_', ' ', $row['jenis_hewan'])); ?></strong>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo number_format($row['jantan'], 0, ',', '.'); ?></td>
                                <td class="text-center"><?php echo number_format($row['betina'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <strong><?php echo number_format($row['total'], 0, ',', '.'); ?></strong>
                                </td>
                                <td class="text-center">
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?php echo $percentage; ?>%">
                                            <span><?php echo number_format($percentage, 1); ?>%</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="gender-distribution">
                                        <div class="gender-bar">
                                            <div class="gender-male" style="width: <?php echo $genderRatio; ?>%">
                                                <?php if ($genderRatio > 10): ?>
                                                    <span><?php echo number_format($genderRatio, 0); ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="gender-female" style="width: <?php echo 100 - $genderRatio; ?>%">
                                                <?php if ((100 - $genderRatio) > 10): ?>
                                                    <span><?php echo number_format(100 - $genderRatio, 0); ?>%</span>
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
                    <tfoot>
                        <tr>
                            <td><strong>TOTAL</strong></td>
                            <td class="text-center"><strong><?php echo number_format($totalJantan, 0, ',', '.'); ?></strong></td>
                            <td class="text-center"><strong><?php echo number_format($totalBetina, 0, ',', '.'); ?></strong></td>
                            <td class="text-center"><strong><?php echo number_format($grandTotal, 0, ',', '.'); ?></strong></td>
                            <td class="text-center">100%</td>
                            <td>
                                <?php 
                                $totalGenderRatio = $grandTotal > 0 ? ($totalJantan / $grandTotal) * 100 : 0;
                                ?>
                                <div class="gender-distribution">
                                    <div class="gender-bar">
                                        <div class="gender-male" style="width: <?php echo $totalGenderRatio; ?>%">
                                            <span><?php echo number_format($totalGenderRatio, 1); ?>%</span>
                                        </div>
                                        <div class="gender-female" style="width: <?php echo 100 - $totalGenderRatio; ?>%">
                                            <span><?php echo number_format(100 - $totalGenderRatio, 1); ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Chart Ringkasan -->
        <div class="stats-card">
            <h4><i class="fas fa-chart-bar"></i> Ringkasan Statistik</h4>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-content">
                        <h5>Rata-rata per Jenis</h5>
                        <p><?php echo number_format($grandTotal / max(count($data), 1), 1); ?> ekor/jenis</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-venus-mars"></i>
                    </div>
                    <div class="stat-content">
                        <h5>Rasio Gender</h5>
                        <p>J:B = <?php echo number_format($totalJantan, 0); ?>:<?php echo number_format($totalBetina, 0); ?></p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h5>% Jantan</h5>
                        <p><?php echo $grandTotal > 0 ? number_format(($totalJantan / $grandTotal) * 100, 1) : 0; ?>%</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="stat-content">
                        <h5>Jenis Hewan</h5>
                        <p><?php echo count($data); ?> jenis</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <a href="?module=pemotongan&action=edit&tanggal=<?php echo $tanggal; ?>&kecamatan=<?php echo urlencode($kecamatan); ?>" 
               class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Data
            </a>
          
            <a href="?module=pemotongan&action=data" class="btn btn-secondary">
                <i class="fas fa-list"></i> Lihat Semua Data
            </a>
           
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus data pemotongan ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/pemotongan/delete.php?tanggal=<?php echo $tanggal; ?>&kecamatan=<?php echo urlencode($kecamatan); ?>';
    }
}

function exportToExcel() {
    const today = new Date().toISOString().split('T')[0];
    const fileName = `Pemotongan_<?php echo str_replace(' ', '_', $kecamatan); ?>_<?php echo $tanggal; ?>_${today}.xlsx`;
    
    // Get table data
    const table = document.querySelector('.data-table');
    exportTableToExcel(table, fileName);
}

function exportTableToExcel(table, filename = 'data-pemotongan.xlsx') {
    // Implementasi export Excel di sini
    alert('Fitur export Excel akan diimplementasikan');
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
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.detail-info h3 {
    color: #333;
    margin-bottom: 15px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.info-item i {
    color: #007bff;
    width: 20px;
}

.detail-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.summary-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.summary-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.summary-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
}

.summary-value.male {
    color: #007bff;
}

.summary-value.female {
    color: #e83e8c;
}

.summary-value.grand {
    color: #28a745;
    font-size: 28px;
}

.animal-type {
    display: flex;
    align-items: center;
    gap: 10px;
}

.animal-type i {
    color: #007bff;
    font-size: 1.2em;
}

.percentage-bar {
    width: 100%;
    height: 20px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.percentage-fill {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #6610f2);
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.percentage-fill span {
    color: white;
    font-size: 11px;
    font-weight: bold;
    text-shadow: 0 1px 1px rgba(0,0,0,0.3);
}

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
    display: flex;
    align-items: center;
    justify-content: center;
}

.gender-female {
    background-color: #e83e8c;
    color: white;
    text-align: center;
    font-size: 12px;
    line-height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
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

.stats-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
}

.stats-card h4 {
    color: #495057;
    margin-bottom: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon i {
    font-size: 1.2em;
}

.stat-content h5 {
    margin: 0 0 5px 0;
    color: #495057;
    font-size: 14px;
}

.stat-content p {
    margin: 0;
    color: #007bff;
    font-weight: bold;
    font-size: 18px;
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
    
    .stats-card,
    .gender-distribution,
    .percentage-bar {
        break-inside: avoid;
    }
}
</style>