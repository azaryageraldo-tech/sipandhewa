<?php
// modules/populasi/detail.php
require_once __DIR__ . '/../../includes/functions.php';

// Get parameters
$kecamatan = $_GET['kecamatan'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Validate input
if (empty($kecamatan)) {
    echo '<div class="alert alert-danger">Kecamatan tidak ditemukan</div>';
    echo '<a href="?module=populasi&action=data" class="btn btn-primary">Kembali ke Data</a>';
    exit();
}

try {
    // Get kecamatan data
    $kecamatanData = fetchOne("SELECT id, nama_kecamatan FROM kecamatan WHERE nama_kecamatan = ?", [$kecamatan]);
    
    if (!$kecamatanData) {
        echo '<div class="alert alert-danger">Kecamatan tidak ditemukan</div>';
        exit();
    }
    
    $kecamatanId = $kecamatanData['id'];
    
    // Get population data per desa
    $sql = "SELECT 
            p.*,
            d.nama_desa,
            u.fullname as petugas
            FROM populasi_ternak p
            JOIN desa d ON p.desa_id = d.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.kecamatan_id = ? 
            AND p.bulan = ? 
            AND p.tahun = ?
            ORDER BY d.nama_desa";
    
    $data = fetchAll($sql, [$kecamatanId, $bulan, $tahun]);
    
    // Get summary for this kecamatan
    $summarySql = "SELECT 
                    COUNT(*) as total_desa,
                    SUM(total_semua) as total_populasi,
                    SUM(sapi_bali_total) as total_sapi,
                    SUM(kerbau_total) as total_kerbau,
                    SUM(kuda_total) as total_kuda,
                    SUM(babi_bali_total + babi_landrace_total) as total_babi,
                    SUM(kambing_total) as total_kambing,
                    SUM(unggas_total) as total_unggas,
                    SUM(anjing_total) as total_anjing
                   FROM populasi_ternak 
                   WHERE kecamatan_id = ? 
                   AND bulan = ? 
                   AND tahun = ?";
    
    $summary = fetchOne($summarySql, [$kecamatanId, $bulan, $tahun]);
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    exit();
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-info-circle"></i> Detail Populasi per Desa</h2>
        <p class="subtitle">
            Kecamatan <strong><?php echo $kecamatan; ?></strong> - 
            Periode <strong><?php echo DateTime::createFromFormat('!m', $bulan)->format('F'); ?> <?php echo $tahun; ?></strong>
        </p>
        
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <button class="btn btn-success" onclick="exportDetailToExcel()">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #4CAF50;">
                <i class="fas fa-village"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo $summary['total_desa'] ?? 0; ?></h3>
                <p>Desa</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #2196F3;">
                <i class="fas fa-cow"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_sapi'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Sapi Bali</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #FF9800;">
                <i class="fas fa-hippo"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_kerbau'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Kerbau</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #F44336;">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_babi'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Babi</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #9C27B0;">
                <i class="fas fa-sheep"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_kambing'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Kambing</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #00BCD4;">
                <i class="fas fa-egg"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_unggas'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Unggas</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #607D8B;">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo number_format($summary['total_populasi'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Semua</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-navigation">
        <button class="tab-btn active" onclick="showTab('all', this)">Semua Data</button>
        <button class="tab-btn" onclick="showTab('sapi', this)">Sapi & Kerbau</button>
        <button class="tab-btn" onclick="showTab('babi', this)">Babi</button>
        <button class="tab-btn" onclick="showTab('kambing', this)">Kambing</button>
        <button class="tab-btn" onclick="showTab('unggas', this)">Unggas & Anjing</button>
    </div>

    <!-- All Data Tab -->
    <div class="tab-content active" id="allTab">
        <div class="table-container">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th rowspan="2">Desa</th>
                        <th colspan="3">Sapi Bali</th>
                        <th colspan="2">Kerbau</th>
                        <th colspan="2">Kuda</th>
                        <th colspan="4">Babi Bali</th>
                        <th colspan="4">Babi Landrace</th>
                        <th colspan="3">Kambing Potong</th>
                        <th colspan="3">Kambing Perah</th>
                        <th rowspan="2">Total Kambing</th>
                        <th colspan="4">Ayam</th>
                        <th colspan="3">Bebek</th>
                        <th rowspan="2">Anjing</th>
                        <th rowspan="2">Total Desa</th>
                    </tr>
                    <tr>
                        <!-- Sapi Bali -->
                        <th>J</th><th>B</th><th>T</th>
                        <!-- Kerbau -->
                        <th>J</th><th>B</th>
                        <!-- Kuda -->
                        <th>J</th><th>B</th>
                        <!-- Babi Bali -->
                        <th>I</th><th>B</th><th>J</th><th>T</th>
                        <!-- Babi Landrace -->
                        <th>I</th><th>B</th><th>J</th><th>T</th>
                        <!-- Kambing Potong -->
                        <th>J</th><th>B</th><th>T</th>
                        <!-- Kambing Perah -->
                        <th>J</th><th>B</th><th>T</th>
                        <!-- Ayam -->
                        <th>Buras</th><th>Petelur</th><th>Pedaging</th><th>T</th>
                        <!-- Bebek -->
                        <th>Itik</th><th>Manila</th><th>T</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="45" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-database"></i>
                                    <h4>Tidak ada data untuk periode ini</h4>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="desa-name">
                                    <strong><?php echo $row['nama_desa']; ?></strong>
                                    <?php if ($row['petugas']): ?>
                                        <br><small class="text-muted">Petugas: <?php echo $row['petugas']; ?></small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Sapi Bali -->
                                <td><?php echo $row['sapi_bali_jantan']; ?></td>
                                <td><?php echo $row['sapi_bali_betina']; ?></td>
                                <td class="total-cell"><?php echo $row['sapi_bali_total']; ?></td>
                                
                                <!-- Kerbau -->
                                <td><?php echo $row['kerbau_jantan']; ?></td>
                                <td><?php echo $row['kerbau_betina']; ?></td>
                                <td class="total-cell"><?php echo $row['kerbau_total']; ?></td>
                                
                                <!-- Kuda -->
                                <td><?php echo $row['kuda_jantan']; ?></td>
                                <td><?php echo $row['kuda_betina']; ?></td>
                                <td class="total-cell"><?php echo $row['kuda_total']; ?></td>
                                
                                <!-- Babi Bali -->
                                <td><?php echo $row['babi_bali_induk']; ?></td>
                                <td><?php echo $row['babi_bali_betina']; ?></td>
                                <td><?php echo $row['babi_bali_jantan']; ?></td>
                                <td class="total-cell"><?php echo $row['babi_bali_total']; ?></td>
                                
                                <!-- Babi Landrace -->
                                <td><?php echo $row['babi_landrace_induk']; ?></td>
                                <td><?php echo $row['babi_landrace_betina']; ?></td>
                                <td><?php echo $row['babi_landrace_jantan']; ?></td>
                                <td class="total-cell"><?php echo $row['babi_landrace_total']; ?></td>
                                
                                <!-- Kambing Potong -->
                                <td><?php echo $row['kambing_potong_jantan']; ?></td>
                                <td><?php echo $row['kambing_potong_betina']; ?></td>
                                <td class="total-cell"><?php echo $row['kambing_potong_total']; ?></td>
                                
                                <!-- Kambing Perah -->
                                <td><?php echo $row['kambing_perah_jantan']; ?></td>
                                <td><?php echo $row['kambing_perah_betina']; ?></td>
                                <td class="total-cell"><?php echo $row['kambing_perah_total']; ?></td>
                                
                                <!-- Total Kambing -->
                                <td class="total-cell"><?php echo $row['kambing_total']; ?></td>
                                
                                <!-- Ayam -->
                                <td><?php echo $row['ayam_buras']; ?></td>
                                <td><?php echo $row['ayam_petelur']; ?></td>
                                <td><?php echo $row['ayam_pedaging']; ?></td>
                                <td class="total-cell"><?php echo $row['ayam_total']; ?></td>
                                
                                <!-- Bebek -->
                                <td><?php echo $row['bebek_itik']; ?></td>
                                <td><?php echo $row['bebek_manila']; ?></td>
                                <td class="total-cell"><?php echo $row['bebek_total']; ?></td>
                                
                                <!-- Anjing -->
                                <td><?php echo $row['anjing_total']; ?></td>
                                
                                <!-- Total Desa -->
                                <td class="total-cell grand-total">
                                    <strong><?php echo $row['total_semua']; ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($data)): ?>
                <tfoot>
                    <tr class="footer-total">
                        <td><strong>TOTAL</strong></td>
                        <!-- Sapi Bali -->
                        <td><?php echo array_sum(array_column($data, 'sapi_bali_jantan')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'sapi_bali_betina')); ?></td>
                        <td><strong><?php echo $summary['total_sapi']; ?></strong></td>
                        <!-- Kerbau -->
                        <td><?php echo array_sum(array_column($data, 'kerbau_jantan')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'kerbau_betina')); ?></td>
                        <td><strong><?php echo $summary['total_kerbau']; ?></strong></td>
                        <!-- Kuda -->
                        <td><?php echo array_sum(array_column($data, 'kuda_jantan')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'kuda_betina')); ?></td>
                        <td><strong><?php echo $summary['total_kuda']; ?></strong></td>
                        <!-- Babi Bali -->
                        <td><?php echo array_sum(array_column($data, 'babi_bali_induk')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'babi_bali_betina')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'babi_bali_jantan')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'babi_bali_total')); ?></strong></td>
                        <!-- Babi Landrace -->
                        <td><?php echo array_sum(array_column($data, 'babi_landrace_induk')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'babi_landrace_betina')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'babi_landrace_jantan')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'babi_landrace_total')); ?></strong></td>
                        <!-- Kambing Potong -->
                        <td><?php echo array_sum(array_column($data, 'kambing_potong_jantan')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'kambing_potong_betina')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'kambing_potong_total')); ?></strong></td>
                        <!-- Kambing Perah -->
                        <td><?php echo array_sum(array_column($data, 'kambing_perah_jantan')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'kambing_perah_betina')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'kambing_perah_total')); ?></strong></td>
                        <!-- Total Kambing -->
                        <td><strong><?php echo $summary['total_kambing']; ?></strong></td>
                        <!-- Ayam -->
                        <td><?php echo array_sum(array_column($data, 'ayam_buras')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'ayam_petelur')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'ayam_pedaging')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'ayam_total')); ?></strong></td>
                        <!-- Bebek -->
                        <td><?php echo array_sum(array_column($data, 'bebek_itik')); ?></td>
                        <td><?php echo array_sum(array_column($data, 'bebek_manila')); ?></td>
                        <td><strong><?php echo array_sum(array_column($data, 'bebek_total')); ?></strong></td>
                        <!-- Anjing -->
                        <td><strong><?php echo $summary['total_anjing']; ?></strong></td>
                        <!-- Total Semua -->
                        <td class="grand-total"><strong><?php echo $summary['total_populasi']; ?></strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Sapi & Kerbau Tab -->
    <div class="tab-content" id="sapiTab">
        <div class="table-container">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th>Desa</th>
                        <th colspan="3">Sapi Bali</th>
                        <th colspan="2">Kerbau</th>
                        <th colspan="2">Kuda</th>
                        <th>Total</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Jantan</th><th>Betina</th><th>Total</th>
                        <th>Jantan</th><th>Betina</th>
                        <th>Jantan</th><th>Betina</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td class="desa-name"><strong><?php echo $row['nama_desa']; ?></strong></td>
                            <td><?php echo $row['sapi_bali_jantan']; ?></td>
                            <td><?php echo $row['sapi_bali_betina']; ?></td>
                            <td class="total-cell"><?php echo $row['sapi_bali_total']; ?></td>
                            <td><?php echo $row['kerbau_jantan']; ?></td>
                            <td><?php echo $row['kerbau_betina']; ?></td>
                            <td><?php echo $row['kuda_jantan']; ?></td>
                            <td><?php echo $row['kuda_betina']; ?></td>
                            <td class="grand-total">
                                <strong><?php echo $row['sapi_bali_total'] + $row['kerbau_total'] + $row['kuda_total']; ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Babi Tab -->
    <div class="tab-content" id="babiTab">
        <div class="table-container">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th>Desa</th>
                        <th colspan="4">Babi Bali</th>
                        <th colspan="4">Babi Landrace</th>
                        <th>Total Babi</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Induk</th><th>Betina</th><th>Jantan</th><th>Total</th>
                        <th>Induk</th><th>Betina</th><th>Jantan</th><th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td class="desa-name"><strong><?php echo $row['nama_desa']; ?></strong></td>
                            <td><?php echo $row['babi_bali_induk']; ?></td>
                            <td><?php echo $row['babi_bali_betina']; ?></td>
                            <td><?php echo $row['babi_bali_jantan']; ?></td>
                            <td class="total-cell"><?php echo $row['babi_bali_total']; ?></td>
                            <td><?php echo $row['babi_landrace_induk']; ?></td>
                            <td><?php echo $row['babi_landrace_betina']; ?></td>
                            <td><?php echo $row['babi_landrace_jantan']; ?></td>
                            <td class="total-cell"><?php echo $row['babi_landrace_total']; ?></td>
                            <td class="grand-total">
                                <strong><?php echo $row['babi_bali_total'] + $row['babi_landrace_total']; ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kambing Tab -->
    <div class="tab-content" id="kambingTab">
        <div class="table-container">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th>Desa</th>
                        <th colspan="3">Kambing Potong</th>
                        <th colspan="3">Kambing Perah</th>
                        <th>Total Kambing</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Jantan</th><th>Betina</th><th>Total</th>
                        <th>Jantan</th><th>Betina</th><th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td class="desa-name"><strong><?php echo $row['nama_desa']; ?></strong></td>
                            <td><?php echo $row['kambing_potong_jantan']; ?></td>
                            <td><?php echo $row['kambing_potong_betina']; ?></td>
                            <td class="total-cell"><?php echo $row['kambing_potong_total']; ?></td>
                            <td><?php echo $row['kambing_perah_jantan']; ?></td>
                            <td><?php echo $row['kambing_perah_betina']; ?></td>
                            <td class="total-cell"><?php echo $row['kambing_perah_total']; ?></td>
                            <td class="grand-total">
                                <strong><?php echo $row['kambing_total']; ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Unggas & Anjing Tab -->
    <div class="tab-content" id="unggasTab">
        <div class="table-container">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th>Desa</th>
                        <th colspan="4">Ayam</th>
                        <th colspan="3">Bebek</th>
                        <th>Anjing</th>
                        <th>Total Unggas</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Buras</th><th>Petelur</th><th>Pedaging</th><th>Total</th>
                        <th>Itik</th><th>Manila</th><th>Total</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td class="desa-name"><strong><?php echo $row['nama_desa']; ?></strong></td>
                            <td><?php echo $row['ayam_buras']; ?></td>
                            <td><?php echo $row['ayam_petelur']; ?></td>
                            <td><?php echo $row['ayam_pedaging']; ?></td>
                            <td class="total-cell"><?php echo $row['ayam_total']; ?></td>
                            <td><?php echo $row['bebek_itik']; ?></td>
                            <td><?php echo $row['bebek_manila']; ?></td>
                            <td class="total-cell"><?php echo $row['bebek_total']; ?></td>
                            <td><?php echo $row['anjing_total']; ?></td>
                            <td class="grand-total">
                                <strong><?php echo $row['unggas_total']; ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-section">
        <h3><i class="fas fa-chart-bar"></i> Grafik Distribusi Populasi</h3>
        <div class="chart-row">
            <div class="chart-container">
                <canvas id="desaChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Tab management
function showTab(tabName, button) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + 'Tab').classList.add('active');
    
    // Add active class to clicked button
    button.classList.add('active');
}

// Export to Excel
function exportDetailToExcel() {
    const table = document.querySelector('.data-table');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('th, td');
        
        cells.forEach(cell => {
            // Remove HTML tags and get text content
            let text = cell.textContent.trim();
            // Remove extra spaces and newlines
            text = text.replace(/\s+/g, ' ').replace(/\n/g, ' ');
            // Add quotes if contains comma
            if (text.includes(',')) {
                text = '"' + text + '"';
            }
            rowData.push(text);
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "detail-populasi-<?php echo $kecamatan; ?>-<?php echo $bulan; ?>-<?php echo $tahun; ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Chart for desa distribution
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('desaChart');
    if (!ctx) return;
    
    const desaNames = <?php echo json_encode(array_column($data, 'nama_desa')); ?>;
    const totalValues = <?php echo json_encode(array_column($data, 'total_semua')); ?>;
    
    // Sort by total (descending)
    const combined = desaNames.map((name, index) => ({
        name: name,
        total: totalValues[index] || 0
    })).sort((a, b) => b.total - a.total);
    
    const sortedNames = combined.map(item => item.name);
    const sortedTotals = combined.map(item => item.total);
    
    const chart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: sortedNames,
            datasets: [{
                label: 'Total Populasi',
                data: sortedTotals,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Total: ${context.parsed.y.toLocaleString('id-ID')} ekor`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah (ekor)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Nama Desa'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
});
</script>

<style>
.subtitle {
    color: #6c757d;
    margin-top: 5px;
    font-size: 1.1rem;
}

.tabs-navigation {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
}

.tab-btn {
    padding: 10px 20px;
    background: #f8f9fa;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: #007bff;
    color: white;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s;
}

.tab-content.active {
    display: block;
}

.table-container {
    overflow-x: auto;
    margin: 20px 0;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.data-table.striped tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.data-table th {
    background-color: #e9ecef;
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
}

.data-table td {
    padding: 8px;
    border: 1px solid #dee2e6;
    text-align: center;
}

.data-table .desa-name {
    text-align: left;
    font-weight: 500;
    min-width: 150px;
    background-color: #f8f9fa;
}

.data-table .total-cell {
    font-weight: 500;
    background-color: #e9ecef;
}

.data-table .grand-total {
    font-weight: 600;
    color: #dc3545;
    background-color: #fff3cd;
}

.footer-total {
    background-color: #f8f9fa !important;
    font-weight: 600;
}

.footer-total td {
    background-color: #e9ecef !important;
}

.chart-section {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.chart-row {
    display: flex;
    gap: 20px;
}

.chart-container {
    flex: 1;
    height: 300px;
    position: relative;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #dee2e6;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media print {
    .header-actions, .tabs-navigation, .chart-section {
        display: none !important;
    }
    
    .data-table {
        font-size: 10pt;
    }
}
</style>