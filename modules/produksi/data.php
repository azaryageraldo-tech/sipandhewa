<?php
require_once __DIR__ . '/../../includes/functions.php';

// Filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$jenis_peternakan = $_GET['jenis_peternakan'] ?? '';

// Build query
$sql = "SELECT p.*, u.fullname as petugas 
        FROM produksi p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.tanggal_produksi BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($jenis_peternakan) {
    $sql .= " AND p.jenis_peternakan = ?";
    $params[] = $jenis_peternakan;
}

$sql .= " ORDER BY p.tanggal_produksi DESC, p.created_at DESC";

$data = fetchAll($sql, $params);

// Get summary statistics
$summarySql = "SELECT 
                COUNT(*) as total_data,
                SUM(produksi_susu) as total_susu,
                SUM(produksi_daging) as total_daging,
                SUM(produksi_telur) as total_telur,
                SUM(biaya_produksi) as total_biaya,
                SUM(harga_jual) as total_pendapatan,
                SUM(keuntungan) as total_keuntungan
               FROM produksi 
               WHERE tanggal_produksi BETWEEN ? AND ?";
$summary = fetchOne($summarySql, [$start_date, $end_date]);

// Get profit statistics
$profitStatsSql = "SELECT 
                    COUNT(CASE WHEN keuntungan > 0 THEN 1 END) as untung,
                    COUNT(CASE WHEN keuntungan < 0 THEN 1 END) as rugi,
                    COUNT(CASE WHEN keuntungan = 0 THEN 1 END) as impas
                   FROM produksi 
                   WHERE tanggal_produksi BETWEEN ? AND ?";
$profitStats = fetchOne($profitStatsSql, [$start_date, $end_date]);
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-chart-line"></i> Data Produksi Peternakan</h2>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel('produksiTable', 'data-produksi')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
             <button class="btn btn-primary" onclick="window.location.href='?module=produksi&action=input'">
                <i class="fas fa-plus"></i> Input Data Baru
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_data'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Data</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="summary-content">
                <h3>Rp <?php echo number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="summary-content">
                <h3>Rp <?php echo number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Keuntungan</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="summary-content">
                <h3>
                    <?php 
                    $totalPendapatan = $summary['total_pendapatan'] ?? 1;
                    $totalKeuntungan = $summary['total_keuntungan'] ?? 0;
                    $margin = $totalPendapatan > 0 ? ($totalKeuntungan / $totalPendapatan) * 100 : 0;
                    echo number_format($margin, 1) . '%';
                    ?>
                </h3>
                <p>Margin Keuntungan</p>
            </div>
        </div>
    </div>

    <!-- Profit Statistics -->
    <div class="stats-cards">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <h3><?php echo $profitStats['untung'] ?? 0; ?></h3>
                <p>Produksi Untung</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
        
        <div class="stat-card stat-danger">
            <div class="stat-content">
                <h3><?php echo $profitStats['rugi'] ?? 0; ?></h3>
                <p>Produksi Rugi</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        
        <div class="stat-card stat-warning">
            <div class="stat-content">
                <h3><?php echo $profitStats['impas'] ?? 0; ?></h3>
                <p>Produksi Impas</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-equals"></i>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="module" value="produksi">
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
                    <label for="jenis_peternakan">Jenis Peternakan</label>
                    <select id="jenis_peternakan" name="jenis_peternakan">
                        <option value="">Semua Jenis</option>
                        <option value="sapi_perah" <?php echo $jenis_peternakan == 'sapi_perah' ? 'selected' : ''; ?>>Sapi Perah</option>
                        <option value="sapi_potong" <?php echo $jenis_peternakan == 'sapi_potong' ? 'selected' : ''; ?>>Sapi Potong</option>
                        <option value="ayam_petelur" <?php echo $jenis_peternakan == 'ayam_petelur' ? 'selected' : ''; ?>>Ayam Petelur</option>
                        <option value="ayam_pedaging" <?php echo $jenis_peternakan == 'ayam_pedaging' ? 'selected' : ''; ?>>Ayam Pedaging</option>
                        <option value="kambing" <?php echo $jenis_peternakan == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                        <option value="babi" <?php echo $jenis_peternakan == 'babi' ? 'selected' : ''; ?>>Babi</option>
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

    <!-- Production Summary -->
    <div class="summary-table">
        <h3><i class="fas fa-chart-bar"></i> Ringkasan Produksi</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Susu (L)</th>
                    <th>Daging (kg)</th>
                    <th>Telur (butir)</th>
                    <th>Biaya (Rp)</th>
                    <th>Pendapatan (Rp)</th>
                    <th>Keuntungan (Rp)</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right"><?php echo number_format($summary['total_susu'] ?? 0, 1, ',', '.'); ?> L</td>
                    <td class="text-right"><?php echo number_format($summary['total_daging'] ?? 0, 1, ',', '.'); ?> kg</td>
                    <td class="text-right"><?php echo number_format($summary['total_telur'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($summary['total_biaya'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right">
                        <?php 
                        $keuntungan = $summary['total_keuntungan'] ?? 0;
                        $keuntunganClass = $keuntungan > 0 ? 'text-success' : ($keuntungan < 0 ? 'text-danger' : 'text-muted');
                        ?>
                        <span class="<?php echo $keuntunganClass; ?>">
                            Rp <?php echo number_format($keuntungan, 0, ',', '.'); ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <?php 
                        $margin = $summary['total_pendapatan'] > 0 ? ($summary['total_keuntungan'] / $summary['total_pendapatan']) * 100 : 0;
                        $marginClass = $margin > 0 ? 'text-success' : ($margin < 0 ? 'text-danger' : 'text-muted');
                        ?>
                        <span class="<?php echo $marginClass; ?>">
                            <?php echo number_format($margin, 1); ?>%
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Data Table -->
    <div class="data-card">
        <div class="table-responsive">
            <table id="produksiTable" class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Peternak</th>
                        <th>Jenis</th>
                        <th>Hasil Produksi</th>
                        <th>Biaya (Rp)</th>
                        <th>Pendapatan (Rp)</th>
                        <th>Keuntungan (Rp)</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data produksi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $index => $row): ?>
                            <?php 
                            $keuntunganClass = $row['keuntungan'] > 0 ? 'text-success' : 
                                              ($row['keuntungan'] < 0 ? 'text-danger' : 'text-muted');
                            
                            // Format production output
                            $produksi = [];
                            if ($row['produksi_susu'] > 0) $produksi[] = number_format($row['produksi_susu'], 1) . ' L susu';
                            if ($row['produksi_daging'] > 0) $produksi[] = number_format($row['produksi_daging'], 1) . ' kg daging';
                            if ($row['produksi_telur'] > 0) $produksi[] = number_format($row['produksi_telur'], 0) . ' butir telur';
                            $produksiText = !empty($produksi) ? implode(', ', $produksi) : '-';
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_produksi'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_peternak']); ?></td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo ucwords(str_replace('_', ' ', $row['jenis_peternakan'])); ?>
                                    </span>
                                    <?php if ($row['jenis_pakan']): ?>
                                        <br><small><?php echo htmlspecialchars($row['jenis_pakan']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $produksiText; ?></td>
                                <td class="text-right">Rp <?php echo number_format($row['biaya_produksi'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                <td class="text-right">
                                    <span class="<?php echo $keuntunganClass; ?>">
                                        <strong>Rp <?php echo number_format($row['keuntungan'], 0, ',', '.'); ?></strong>
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
                                    <a href="modules/produksi/delete.php?id=<?= $row['id'] ?>"
                                    onclick="return confirm('Yakin hapus data ini?')"
                                    class="btn-action btn-delete">
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
                <?php if (!empty($data)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong>Rp <?php echo number_format($summary['total_biaya'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>Rp <?php echo number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td class="text-right">
                                <strong class="<?php echo $keuntunganClass; ?>">
                                    Rp <?php echo number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.'); ?>
                                </strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Pagination -->
        <!-- <div class="pagination">
            <span>Menampilkan <?php echo count($data); ?> data produksi</span>
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
    const today = new Date().toISOString().split('T')[0];
    const firstDay = today.substring(0, 8) + '01';
    
    document.getElementById('start_date').value = firstDay;
    document.getElementById('end_date').value = today;
    document.getElementById('jenis_peternakan').value = '';
    document.querySelector('.filter-form').submit();
}

function generateReport() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const jenis = document.getElementById('jenis_peternakan').value;
    
    const params = new URLSearchParams({
        action: 'generate_report',
        start_date: startDate,
        end_date: endDate,
        jenis_peternakan: jenis
    });
    
    window.open('modules/produksi/process.php?' + params.toString(), '_blank');
}

function editData(id) {
    window.location.href = '?module=produksi&action=edit&id=' + id;
}

function viewDetails(id) {
     window.location.href = '?module=produksi&action=detail&id=' + id;

}
function deleteData(id) {
 if (confirm('Yakin ingin menghapus data produksi ini?')) {
        window.location.href = 
            '?module=produksi&action=delete&id=' + id;
    }
    return false;
}

function showDetailModal(data) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle"></i> Detail Produksi</h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="detail-row">
                        <strong>Peternak:</strong> ${data.nama_peternak}
                    </div>
                    <div class="detail-row">
                        <strong>Jenis Peternakan:</strong> ${data.jenis_peternakan}
                    </div>
                    <div class="detail-row">
                        <strong>Jenis Pakan:</strong> ${data.jenis_pakan || '-'}
                    </div>
                    <div class="detail-row">
                        <strong>Tanggal Produksi:</strong> ${new Date(data.tanggal_produksi).toLocaleDateString('id-ID')}
                    </div>
                    <hr>
                    <div class="detail-row">
                        <strong>Produksi Susu:</strong> ${parseFloat(data.produksi_susu).toFixed(1)} L
                    </div>
                    <div class="detail-row">
                        <strong>Produksi Daging:</strong> ${parseFloat(data.produksi_daging).toFixed(1)} kg
                    </div>
                    <div class="detail-row">
                        <strong>Produksi Telur:</strong> ${parseInt(data.produksi_telur)} butir
                    </div>
                    <hr>
                    <div class="detail-row">
                        <strong>Biaya Produksi:</strong> Rp ${parseInt(data.biaya_produksi).toLocaleString('id-ID')}
                    </div>
                    <div class="detail-row">
                        <strong>Pendapatan:</strong> Rp ${parseInt(data.harga_jual).toLocaleString('id-ID')}
                    </div>
                    <div class="detail-row">
                        <strong>Keuntungan:</strong> 
                        <span style="color: ${data.keuntungan > 0 ? 'green' : data.keuntungan < 0 ? 'red' : 'gray'}">
                            Rp ${parseInt(data.keuntungan).toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
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

// Add modal styles
const modalStyle = document.createElement('style');
modalStyle.textContent = `
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
    }
    .modal-body {
        padding: 20px;
    }
    .detail-row {
        margin-bottom: 10px;
        padding: 8px 0;
    }
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #dee2e6;
        text-align: right;
    }
`;
document.head.appendChild(modalStyle);
</script>