<?php
// modules/dashboard/main.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Get database connection
$pdo = getDBConnection();

// Fungsi untuk mendapatkan data dashboard yang diperbaiki
function getDashboardData($pdo)
{
    $data = [];

    // 1. Total Data per Modul
    $tables = [
        'survei_pasar' => 'Survei Pasar',
        'populasi_ternak' => 'Populasi Ternak',
        'produksi' => 'Produksi',
        'peternakan' => 'Peternakan',
        'pemotongan' => 'Pemotongan',
        'vaksinasi' => 'Vaksinasi',
        'penyakit_hewan' => 'Penyakit'
    ];

    foreach ($tables as $table => $name) {
        try {
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            $data['total_data'][$name] = $row['total'] ?? 0;
        } catch (Exception $e) {
            $data['total_data'][$name] = 0;
        }
    }

    // 2. Data Populasi Ternak Terbaru (diperbaiki)
    $data['populasi_detail'] = getLatestPopulationDetails($pdo);

    // 3. Data Produksi Terbaru
    $data['produksi'] = getLatestProduction($pdo);

    // 4. Data Survei Harga Terbaru (diperbaiki sesuai struktur tabel)
    $data['harga'] = getLatestPrices($pdo);

    // 5. Data Statistik per Kecamatan
    $data['statistik_kecamatan'] = getKecamatanStats($pdo);

    // 6. Trend Bulanan
    $data['trend_bulanan'] = getMonthlyTrend($pdo);

    // 7. Total Populasi Keseluruhan (diperbaiki)
    $data['total_populasi'] = getTotalPopulation($pdo);

    // 8. Vaksinasi Bulan Ini
    $data['vaksinasi_bulan_ini'] = getVaccinationThisMonth($pdo);

    // 9. Data Penyakit Terbaru
    $data['penyakit_terbaru'] = getLatestDiseases($pdo);

    // 10. Data Produksi Detail untuk Chart
    $data['produksi_chart'] = getProductionChartData($pdo);

    return $data;
}

// Fungsi yang diperbaiki untuk mendapatkan detail populasi
function getLatestPopulationDetails($pdo)
{
    // Ambil data berdasarkan struktur tabel yang benar
    $sql = "SELECT 
                kecamatan_id,
                bulan,
                tahun,
                (sapi_bali_total + sapi_lain_total) as total_sapi,
                kambing_total as total_kambing,
                ayam_total as total_ayam,
                bebek_total as total_bebek,
                total_semua as total_all
            FROM populasi_ternak 
            WHERE tahun = YEAR(CURDATE())
            ORDER BY tahun DESC, bulan DESC
            LIMIT 5";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi untuk mendapatkan data distribusi ternak per jenis
function getLivestockDistribution($pdo)
{
    $sql = "SELECT 
                'Sapi Bali' as jenis,
                COALESCE(SUM(sapi_bali_total), 0) as jumlah
            FROM populasi_ternak
            WHERE tahun = YEAR(CURDATE())
            UNION ALL
            SELECT 
                'Sapi Lain' as jenis,
                COALESCE(SUM(sapi_lain_total), 0) as jumlah
            FROM populasi_ternak
            WHERE tahun = YEAR(CURDATE())
            UNION ALL
            SELECT 
                'Kambing' as jenis,
                COALESCE(SUM(kambing_total), 0) as jumlah
            FROM populasi_ternak
            WHERE tahun = YEAR(CURDATE())
            UNION ALL
            SELECT 
                'Ayam' as jenis,
                COALESCE(SUM(ayam_total), 0) as jumlah
            FROM populasi_ternak
            WHERE tahun = YEAR(CURDATE())
            UNION ALL
            SELECT 
                'Bebek' as jenis,
                COALESCE(SUM(bebek_total), 0) as jumlah
            FROM populasi_ternak
            WHERE tahun = YEAR(CURDATE())";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi produksi terbaru (diperbaiki)
function getLatestProduction($pdo)
{
    $sql = "SELECT 
                jenis_peternakan,
                SUM(COALESCE(produksi_susu, 0)) as total_susu,
                SUM(COALESCE(produksi_daging, 0)) as total_daging,
                SUM(COALESCE(produksi_telur, 0)) as total_telur,
                SUM(COALESCE(keuntungan, 0)) as total_keuntungan,
                COUNT(*) as jumlah
            FROM produksi 
            WHERE MONTH(tanggal_produksi) = MONTH(CURDATE())
            AND YEAR(tanggal_produksi) = YEAR(CURDATE())
            GROUP BY jenis_peternakan
            ORDER BY total_keuntungan DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi untuk data chart produksi
function getProductionChartData($pdo)
{
    $sql = "SELECT 
                jenis_peternakan as label,
                COALESCE(SUM(produksi_susu), 0) as susu,
                COALESCE(SUM(produksi_daging), 0) as daging,
                COALESCE(SUM(produksi_telur), 0) as telur
            FROM produksi 
            WHERE MONTH(tanggal_produksi) = MONTH(CURDATE())
            AND YEAR(tanggal_produksi) = YEAR(CURDATE())
            GROUP BY jenis_peternakan";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi harga terbaru (diperbaiki sesuai struktur tabel survei_pasar)
function getLatestPrices($pdo)
{
    $sql = "SELECT 
                komoditas,
                lokasi_pasar,
                CASE 
                    WHEN komoditas = 'Daging Babi' THEN AVG(COALESCE(harga_babi_utuh, 0))
                    WHEN komoditas = 'Daging Sapi' THEN AVG(COALESCE(harga_sapi_isi, 0))
                    WHEN komoditas = 'Daging Ayam' THEN AVG(COALESCE(harga_ayam_utuh, 0))
                END as harga_rata,
                MAX(tanggal_survei) as tanggal_terakhir,
                COUNT(*) as jumlah_survei
            FROM survei_pasar 
            WHERE tanggal_survei >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY komoditas, lokasi_pasar
            ORDER BY komoditas, harga_rata DESC
            LIMIT 5";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi statistik kecamatan (diperbaiki)
function getKecamatanStats($pdo)
{
    $sql = "SELECT 
                k.nama_kecamatan, 
                COUNT(DISTINCT p.id) as total_peternakan,
                COALESCE(SUM(pt.total_semua), 0) as total_ternak,
                COUNT(DISTINCT v.id) as total_vaksinasi
            FROM kecamatan k
            LEFT JOIN peternakan p ON k.id = p.kecamatan_id
            LEFT JOIN populasi_ternak pt ON k.id = pt.kecamatan_id AND pt.tahun = YEAR(CURDATE())
            LEFT JOIN vaksinasi v ON k.id = v.kecamatan_id AND MONTH(v.tanggal_vaksinasi) = MONTH(CURDATE())
            GROUP BY k.id, k.nama_kecamatan
            ORDER BY total_ternak DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi trend bulanan
function getMonthlyTrend($pdo)
{
    $currentYear = date('Y');
    $months = [];

    // Inisialisasi array untuk 12 bulan
    for ($i = 1; $i <= 12; $i++) {
        $months[$i] = [
            'bulan' => $i,
            'nama_bulan' => date('F', mktime(0, 0, 0, $i, 1)),
            'survei' => 0,
            'populasi' => 0,
            'vaksinasi' => 0,
            'produksi' => 0
        ];
    }

    // Data Survei Pasar
    $sql = "SELECT MONTH(tanggal_survei) as bulan, COUNT(*) as total
            FROM survei_pasar 
            WHERE YEAR(tanggal_survei) = ?
            GROUP BY MONTH(tanggal_survei)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentYear]);
        $surveiData = $stmt->fetchAll();

        foreach ($surveiData as $row) {
            $months[$row['bulan']]['survei'] = $row['total'];
        }
    } catch (Exception $e) {
        // Tangani error
    }

    // Data Populasi
    $sql = "SELECT bulan, COUNT(*) as total
            FROM populasi_ternak 
            WHERE tahun = ?
            GROUP BY bulan";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentYear]);
        $populasiData = $stmt->fetchAll();

        foreach ($populasiData as $row) {
            $months[$row['bulan']]['populasi'] = $row['total'];
        }
    } catch (Exception $e) {
        // Tangani error
    }

    // Data Produksi
    $sql = "SELECT MONTH(tanggal_produksi) as bulan, COUNT(*) as total
            FROM produksi 
            WHERE YEAR(tanggal_produksi) = ?
            GROUP BY MONTH(tanggal_produksi)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentYear]);
        $produksiData = $stmt->fetchAll();

        foreach ($produksiData as $row) {
            $months[$row['bulan']]['produksi'] = $row['total'];
        }
    } catch (Exception $e) {
        // Tangani error
    }

    // Data Vaksinasi
    $sql = "SELECT MONTH(tanggal_vaksinasi) as bulan, COUNT(*) as total
            FROM vaksinasi 
            WHERE YEAR(tanggal_vaksinasi) = ?
            GROUP BY MONTH(tanggal_vaksinasi)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentYear]);
        $vaksinasiData = $stmt->fetchAll();

        foreach ($vaksinasiData as $row) {
            $months[$row['bulan']]['vaksinasi'] = $row['total'];
        }
    } catch (Exception $e) {
        // Tangani error
    }

    return array_values($months);
}

// Fungsi total populasi (diperbaiki)
function getTotalPopulation($pdo)
{
    $sql = "SELECT COALESCE(SUM(total_semua), 0) as total 
            FROM populasi_ternak 
            WHERE tahun = YEAR(CURDATE())";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Fungsi vaksinasi bulan ini
function getVaccinationThisMonth($pdo)
{
    $sql = "SELECT COUNT(*) as total 
            FROM vaksinasi 
            WHERE MONTH(tanggal_vaksinasi) = MONTH(CURDATE()) 
            AND YEAR(tanggal_vaksinasi) = YEAR(CURDATE())";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Fungsi penyakit terbaru
function getLatestDiseases($pdo)
{
    $sql = "SELECT 
                jenis_ternak,
                jenis_penyakit,
                kasus_digital,
                sampel_positif,
                status_penanganan,
                lokasi
            FROM penyakit_hewan 
            WHERE bulan = DATE_FORMAT(CURDATE(), '%Y-%m')
            ORDER BY kasus_digital DESC
            LIMIT 3";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Mendapatkan data distribusi ternak untuk chart
$livestockDistribution = getLivestockDistribution($pdo);

// Mendapatkan data dashboard
$dashboardData = getDashboardData($pdo);

// Mendapatkan data user dari session
// session_start();
// $user = $_SESSION['user'] ?? ['name' => 'Pengguna', 'role' => 'User'];

// Role Check Helper
$userRole = $user['role'] ?? 'user';
function isVisible($feature, $role) {
    if ($role === 'admin') return true;
    
    $permissions = [
        'petugas_kesmavet' => ['survei_pasar', 'produksi', 'pemotongan'],
        'petugas_keswan' => ['vaksinasi', 'penyakit'],
        'petugas_bitpro' => ['peternakan', 'populasi'],
        // Add other roles as needed
    ];
    
    if (isset($permissions[$role])) {
        return in_array($feature, $permissions[$role]);
    }
    
    return true; // Default allow all if not defined
}
?>

<!-- ========== DASHBOARD CONTENT ========== -->
<div class="dashboard-content">

    <!-- ========== HEADER SECTION ========== -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard SI TERNAK</h1>
            <p class="welcome-text">
                <i class="fas fa-user-circle"></i>
                Selamat datang, <strong><?php echo htmlspecialchars($user['name']); ?></strong> |
                <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
            </p>
        </div>
        <div class="header-info">
            <div class="date-time">
                <i class="fas fa-calendar-day"></i>
                <span id="currentDate"><?php echo date('l, d F Y'); ?></span>
            </div>
            <div class="live-time">
                <i class="fas fa-clock"></i>
                <span id="currentTime"><?php echo date('H:i:s'); ?></span>
            </div>
        </div>
    </div>

    <!-- ========== KPI CARDS SECTION ========== -->
    <div class="kpi-section">
        <h2 class="section-title">
            <i class="fas fa-chart-bar"></i> Statistik Utama
        </h2>
        <div class="kpi-grid">
            <?php if ($userRole === 'petugas_kesmavet'): ?>
                <!-- KPI untuk Petugas Kesmavet -->
                <!-- Card 1: Produksi -->
                <div class="kpi-card card-primary">
                    <div class="kpi-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Data Produksi</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Produksi'] ?? 0); ?></p>
                        <p class="kpi-label">Total Record</p>
                    </div>
                </div>

                <!-- Card 2: Survei Pasar -->
                <div class="kpi-card card-info">
                    <div class="kpi-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Survei Pasar</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Survei Pasar'] ?? 0); ?></p>
                        <p class="kpi-label">Data Survei</p>
                    </div>
                </div>

                <!-- Card 3: Pemotongan -->
                <div class="kpi-card card-warning">
                    <div class="kpi-icon">
                        <i class="fas fa-cut"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Pemotongan</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Pemotongan'] ?? 0); ?></p>
                        <p class="kpi-label">Data Pemotongan</p>
                    </div>
                </div>
            <?php elseif ($userRole === 'petugas_keswan'): ?>
                <!-- KPI untuk Petugas Keswan -->
                <!-- Card 1: Vaksinasi -->
                <div class="kpi-card card-warning">
                    <div class="kpi-icon">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Vaksinasi</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Vaksinasi'] ?? 0); ?></p>
                        <p class="kpi-label">Total Record</p>
                    </div>
                </div>

                <!-- Card 2: Penyakit -->
                <div class="kpi-card card-primary">
                    <div class="kpi-icon">
                        <i class="fas fa-virus"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Penyakit</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Penyakit'] ?? 0); ?></p>
                        <p class="kpi-label">Kasus Tercatat</p>
                    </div>
                </div>

                <!-- Card 3: Vaksinasi Bulan Ini -->
                <div class="kpi-card card-info">
                    <div class="kpi-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Vaksinasi Baru</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['vaksinasi_bulan_ini'] ?? 0); ?></p>
                        <p class="kpi-label">Bulan Ini</p>
                    </div>
                </div>
            <?php elseif ($userRole === 'petugas_bitpro'): ?>
                <!-- KPI untuk Petugas Bitpro -->
                <!-- Card 1: Total Populasi -->
                <div class="kpi-card card-primary">
                    <div class="kpi-icon">
                        <i class="fas fa-cow"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Total Populasi</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_populasi'] ?? 0); ?></p>
                        <p class="kpi-label">Ekor (<?php echo date('Y'); ?>)</p>
                    </div>
                </div>

                <!-- Card 2: Peternakan -->
                <div class="kpi-card card-info">
                    <div class="kpi-icon">
                        <i class="fas fa-tractor"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Peternakan</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Peternakan'] ?? 0); ?></p>
                        <p class="kpi-label">Unit Terdaftar</p>
                    </div>
                </div>

                <!-- Card 3: Kecamatan Terdata -->
                <div class="kpi-card card-success">
                    <div class="kpi-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Wilayah</h3>
                        <p class="kpi-value"><?php echo count($dashboardData['statistik_kecamatan'] ?? []); ?></p>
                        <p class="kpi-label">Kecamatan Terdata</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Default KPI for Admin & Others -->
                <!-- Card 1: Total Populasi -->
                <div class="kpi-card card-primary">
                    <div class="kpi-icon">
                        <i class="fas fa-cow"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Total Populasi</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_populasi']); ?></p>
                        <p class="kpi-label">Ekor (<?php echo date('Y'); ?>)</p>
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>+12%</span>
                    </div>
                </div>

                <!-- Card 2: Peternakan -->
                <div class="kpi-card card-info">
                    <div class="kpi-icon">
                        <i class="fas fa-tractor"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Peternakan</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['total_data']['Peternakan']); ?></p>
                        <p class="kpi-label">Unit Terdaftar</p>
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-plus"></i>
                        <span><?php echo $dashboardData['total_data']['Peternakan'] > 0 ? '+5%' : 'Baru'; ?></span>
                    </div>
                </div>

                <!-- Card 3: Vaksinasi -->
                <div class="kpi-card card-warning">
                    <div class="kpi-icon">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Vaksinasi</h3>
                        <p class="kpi-value"><?php echo number_format($dashboardData['vaksinasi_bulan_ini']); ?></p>
                        <p class="kpi-label">Bulan <?php echo date('F'); ?></p>
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-heartbeat"></i>
                        <span><?php echo $dashboardData['vaksinasi_bulan_ini'] > 0 ? 'Aktif' : 'Belum'; ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Card 4: Total Data (Always Visible) -->
            <div class="kpi-card card-success">
                <div class="kpi-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="kpi-content">
                    <h3>Total Data</h3>
                    <p class="kpi-value"><?php echo number_format(array_sum($dashboardData['total_data'])); ?></p>
                    <p class="kpi-label">Entri Sistem</p>
                </div>
                <div class="kpi-trend">
                    <i class="fas fa-chart-line"></i>
                    <span>+<?php echo count($dashboardData['total_data']); ?> Modul</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== CHARTS SECTION ========== -->
    <div class="charts-section">
        <div class="chart-row">
            <!-- Chart 1: Distribusi Populasi -->
            <?php if (isVisible('populasi', $userRole)): ?>
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie"></i> Distribusi Populasi Ternak</h3>
                    <p class="chart-subtitle">Tahun <?php echo date('Y'); ?></p>
                </div>
                <div class="chart-body">
                    <canvas id="distributionChart" height="250"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <!-- Chart 2: Produksi -->
            <?php if (isVisible('produksi', $userRole)): ?>
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-industry"></i> Produksi Bulan <?php echo date('F'); ?></h3>
                    <p class="chart-subtitle">Per Jenis Peternakan</p>
                </div>
                <div class="chart-body">
                    <canvas id="productionChart" height="250"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="chart-row">
            <!-- Chart 3: Trend Data -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> Trend Data Tahun <?php echo date('Y'); ?></h3>
                    <p class="chart-subtitle">Perkembangan Bulanan</p>
                </div>
                <div class="chart-body">
                    <canvas id="trendChart" height="250"></canvas>
                </div>
            </div>

            <!-- Chart 4: Kecamatan -->
            <?php if (isVisible('populasi', $userRole)): ?>
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-map"></i> Peternakan per Kecamatan</h3>
                    <p class="chart-subtitle">Distribusi Wilayah</p>
                </div>
                <div class="chart-body">
                    <canvas id="kecamatanChart" height="250"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== DATA TABLES SECTION ========== -->
    <div class="tables-section">
        <div class="table-row">
            <!-- Table 1: Harga Pasar -->
            <?php if (isVisible('survei_pasar', $userRole)): ?>
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fas fa-shopping-cart"></i> Harga Pasar Terkini</h3>
                    <a href="dashboard.php?module=survei_pasar" class="btn-view-all">
                        <i class="fas fa-external-link-alt"></i> Lihat Semua
                    </a>
                </div>
                <div class="table-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Komoditas</th>
                                    <th>Lokasi</th>
                                    <th>Harga Rata-rata</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dashboardData['harga'])): ?>
                                    <?php foreach ($dashboardData['harga'] as $harga): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-<?php echo $harga['komoditas'] == 'Daging Babi' ? 'piggy-bank' : ($harga['komoditas'] == 'Daging Sapi' ? 'cow' : 'drumstick-bite'); ?>"></i>
                                                <?php echo htmlspecialchars($harga['komoditas']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($harga['lokasi_pasar']); ?></td>
                                            <td class="price-cell"><?php echo formatCurrency($harga['harga_rata'] ?? 0); ?></td>
                                            <td class="date-cell"><?php echo date('d/m', strtotime($harga['tanggal_terakhir'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                            <p>Belum ada data survei harga</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Table 2: Penyakit Terbaru -->
            <?php if (isVisible('penyakit', $userRole)): ?>
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fas fa-virus"></i> Monitoring Penyakit</h3>
                    <span class="status-badge <?php echo !empty($dashboardData['penyakit_terbaru']) ? 'badge-warning' : 'badge-success'; ?>">
                        <?php echo count($dashboardData['penyakit_terbaru']); ?> Kasus
                    </span>
                </div>
                <div class="table-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Jenis Ternak</th>
                                    <th>Penyakit</th>
                                    <th>Kasus</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dashboardData['penyakit_terbaru'])): ?>
                                    <?php foreach ($dashboardData['penyakit_terbaru'] as $penyakit): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-<?php echo $penyakit['jenis_ternak'] == 'sapi' ? 'cow' : ($penyakit['jenis_ternak'] == 'kambing' ? 'goat' : 'feather-alt'); ?>"></i>
                                                <?php echo ucfirst($penyakit['jenis_ternak']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($penyakit['jenis_penyakit']); ?></td>
                                            <td class="text-center"><?php echo $penyakit['kasus_digital']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $penyakit['status_penanganan']; ?>">
                                                    <?php echo str_replace('_', ' ', $penyakit['status_penanganan']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                            <p>Tidak ada kasus penyakit aktif</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Table 3: Statistik Kecamatan -->
        <?php if (isVisible('populasi', $userRole)): ?>
        <div class="table-card full-width">
            <div class="table-header">
                <h3><i class="fas fa-map-marked-alt"></i> Statistik Per Kecamatan</h3>
                <span class="region-count"><?php echo count($dashboardData['statistik_kecamatan']); ?> Kecamatan</span>
            </div>
            <div class="table-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kecamatan</th>
                                <th class="text-center">Peternakan</th>
                                <th class="text-center">Populasi Ternak</th>
                                <th class="text-center">Vaksinasi (Bln Ini)</th>
                                <th class="text-center">Kepadatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dashboardData['statistik_kecamatan'])): ?>
                                <?php foreach ($dashboardData['statistik_kecamatan'] as $stat): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($stat['nama_kecamatan']); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="data-count"><?php echo $stat['total_peternakan']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="data-count"><?php echo number_format($stat['total_ternak']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="data-count"><?php echo $stat['total_vaksinasi']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stat['total_peternakan'] > 0): ?>
                                                <?php
                                                $density = round($stat['total_ternak'] / $stat['total_peternakan'], 1);
                                                $densityClass = $density > 100 ? 'high' : ($density > 50 ? 'medium' : 'low');
                                                ?>
                                                <span class="density-badge density-<?php echo $densityClass; ?>">
                                                    <?php echo $density; ?> ekor/unit
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-map fa-2x"></i>
                                        <p>Belum ada data wilayah</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========== QUICK ACTIONS SECTION ========== -->
    <div class="quick-actions-section">
        <h3 class="section-title">
            <i class="fas fa-bolt"></i> Aksi Cepat
        </h3>
        <div class="actions-grid">
            <?php if (isVisible('survei_pasar', $userRole)): ?>
            <a href="dashboard.php?module=survei_pasar&action=tambah" class="action-card">
                <div class="action-icon" style="background: #4CAF50;">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-content">
                    <h4>Input Survei Pasar</h4>
                    <p>Catat harga pasar terkini</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('populasi', $userRole)): ?>
            <a href="dashboard.php?module=populasi_ternak&action=tambah" class="action-card">
                <div class="action-icon" style="background: #2196F3;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="action-content">
                    <h4>Input Populasi</h4>
                    <p>Update data populasi ternak</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('vaksinasi', $userRole)): ?>
            <a href="dashboard.php?module=vaksinasi&action=tambah" class="action-card">
                <div class="action-icon" style="background: #FF9800;">
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="action-content">
                    <h4>Input Vaksinasi</h4>
                    <p>Catat kegiatan vaksinasi</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('produksi', $userRole)): ?>
            <a href="dashboard.php?module=produksi&action=tambah" class="action-card">
                <div class="action-icon" style="background: #9C27B0;">
                    <i class="fas fa-industry"></i>
                </div>
                <div class="action-content">
                    <h4>Input Produksi</h4>
                    <p>Catat hasil produksi</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('pemotongan', $userRole)): ?>
            <a href="dashboard.php?module=pemotongan&action=tambah" class="action-card">
                <div class="action-icon" style="background: #F44336;">
                    <i class="fas fa-cut"></i>
                </div>
                <div class="action-content">
                    <h4>Input Pemotongan</h4>
                    <p>Catat data pemotongan</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('penyakit', $userRole)): ?>
            <a href="dashboard.php?module=penyakit&action=tambah" class="action-card">
                <div class="action-icon" style="background: #795548;">
                    <i class="fas fa-virus"></i>
                </div>
                <div class="action-content">
                    <h4>Lapor Penyakit</h4>
                    <p>Laporkan kasus penyakit</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if (isVisible('peternakan', $userRole)): ?>
            <a href="dashboard.php?module=peternakan&action=tambah" class="action-card">
                <div class="action-icon" style="background: #00BCD4;">
                    <i class="fas fa-tractor"></i>
                </div>
                <div class="action-content">
                    <h4>Tambah Peternakan</h4>
                    <p>Daftarkan peternakan baru</p>
                </div>
            </a>
            <?php endif; ?>

            <a href="javascript:void(0)" onclick="window.print()" class="action-card">
                <div class="action-icon" style="background: #607D8B;">
                    <i class="fas fa-print"></i>
                </div>
                <div class="action-content">
                    <h4>Cetak Dashboard</h4>
                    <p>Print laporan dashboard</p>
                </div>
            </a>
        </div>
    </div>

    <!-- ========== SYSTEM INFO SECTION ========== -->
    <div class="system-info">
        <div class="info-card">
            <i class="fas fa-info-circle"></i>
            <div class="info-content">
                <h4>System Information</h4>
                <p>SI TERNAK v1.0 | Last updated: <?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>
        <div class="refresh-info">
            <i class="fas fa-sync-alt"></i>
            <span>Auto refresh in: <span id="refreshCountdown">120</span>s</span>
        </div>
    </div>
</div>

<!-- ========== DASHBOARD STYLES ========== -->
<style>
    /* Dashboard Container */
    .dashboard-content {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 70px);
    }

    /* Header Styles */
    .dashboard-header {
        background: white;
        border-radius: 12px;
        padding: 25px 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 5px solid #2e7d32;
    }

    .header-content h1 {
        margin: 0;
        color: #2e7d32;
        font-size: 28px;
        font-weight: 600;
    }

    .header-content h1 i {
        margin-right: 15px;
        color: #4CAF50;
    }

    .welcome-text {
        margin: 10px 0 0 0;
        color: #666;
        font-size: 15px;
    }

    .welcome-text i {
        margin-right: 8px;
        color: #2196F3;
    }

    .role-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin-left: 10px;
    }

    .header-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .date-time,
    .live-time {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f5f7fa;
        padding: 10px 15px;
        border-radius: 8px;
        min-width: 200px;
    }

    .date-time i,
    .live-time i {
        color: #666;
        font-size: 16px;
    }

    #currentDate,
    #currentTime {
        font-weight: 500;
        color: #333;
    }

    /* KPI Cards */
    .kpi-section {
        margin-bottom: 30px;
    }

    .section-title {
        color: #444;
        font-size: 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #4CAF50;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .kpi-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .card-primary::before {
        background: #4CAF50;
    }

    .card-info::before {
        background: #2196F3;
    }

    .card-warning::before {
        background: #FF9800;
    }

    .card-success::before {
        background: #2e7d32;
    }

    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
    }

    .card-primary .kpi-icon {
        background: #4CAF50;
    }

    .card-info .kpi-icon {
        background: #2196F3;
    }

    .card-warning .kpi-icon {
        background: #FF9800;
    }

    .card-success .kpi-icon {
        background: #2e7d32;
    }

    .kpi-content {
        flex: 1;
    }

    .kpi-content h3 {
        margin: 0 0 5px 0;
        font-size: 16px;
        color: #666;
        font-weight: 500;
    }

    .kpi-value {
        margin: 0;
        font-size: 32px;
        font-weight: 700;
        color: #333;
        line-height: 1;
    }

    .kpi-label {
        margin: 5px 0 0 0;
        font-size: 14px;
        color: #888;
    }

    .kpi-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        color: #666;
        padding: 5px 10px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 20px;
    }

    .trend-up {
        color: #4CAF50;
    }

    /* Charts Section */
    .charts-section {
        margin-bottom: 30px;
    }

    .chart-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .chart-header h3 {
        margin: 0;
        font-size: 18px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-header h3 i {
        color: #4CAF50;
    }

    .chart-subtitle {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 14px;
    }

    .chart-body {
        padding: 20px;
        height: 300px;
    }

    /* Tables Section */
    .tables-section {
        margin-bottom: 30px;
    }

    .table-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table-card.full-width {
        grid-column: 1 / -1;
    }

    .table-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header h3 {
        margin: 0;
        font-size: 18px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-view-all {
        background: #4CAF50;
        color: white;
        padding: 8px 15px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-view-all:hover {
        background: #388E3C;
        transform: translateY(-2px);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .table-body {
        padding: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        font-size: 14px;
    }

    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        font-size: 14px;
    }

    .data-table tbody tr:hover {
        background: #f8f9fa;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .text-center {
        text-align: center;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .price-cell {
        font-weight: 600;
        color: #2e7d32;
    }

    .date-cell {
        color: #666;
        font-size: 13px;
    }

    .data-count {
        font-weight: 600;
        color: #2196F3;
        font-size: 16px;
    }

    .density-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .density-high {
        background: #f8d7da;
        color: #721c24;
    }

    .density-medium {
        background: #fff3cd;
        color: #856404;
    }

    .density-low {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-dalam_pengawasan {
        background: #fff3cd;
        color: #856404;
    }

    .status-dalam_penanganan {
        background: #f8d7da;
        color: #721c24;
    }

    .status-selesai {
        background: #d4edda;
        color: #155724;
    }

    /* Quick Actions */
    .quick-actions-section {
        margin-bottom: 30px;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
    }

    .action-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        color: #333;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #4CAF50;
    }

    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
    }

    .action-content h4 {
        margin: 0 0 5px 0;
        font-size: 16px;
        color: #333;
    }

    .action-content p {
        margin: 0;
        font-size: 13px;
        color: #666;
    }

    /* System Info */
    .system-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .info-card {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .info-card i {
        font-size: 24px;
        color: #4CAF50;
    }

    .info-content h4 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .info-content p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .refresh-info {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
        font-size: 14px;
    }

    .refresh-info i {
        color: #FF9800;
    }

    #refreshCountdown {
        font-weight: 600;
        color: #2196F3;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .chart-row {
            grid-template-columns: 1fr;
        }

        .table-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .header-info {
            width: 100%;
        }

        .date-time,
        .live-time {
            justify-content: center;
        }

        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .chart-row,
        .table-row {
            gap: 15px;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .system-info {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .kpi-card {
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }
    }
</style>

<!-- ========== DASHBOARD SCRIPTS ========== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Update real-time clock
    function updateClock() {
        const now = new Date();
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        const timeString = now.toLocaleTimeString('id-ID', {
            hour12: false
        });

        document.getElementById('currentDate').textContent = dateString;
        document.getElementById('currentTime').textContent = timeString;
    }

    // Countdown for auto-refresh
    let refreshCountdown = 120;
    const countdownElement = document.getElementById('refreshCountdown');

    function updateCountdown() {
        refreshCountdown--;
        countdownElement.textContent = refreshCountdown;

        if (refreshCountdown <= 0) {
            location.reload();
        }
    }

    // Initialize clocks and countdowns
    updateClock();
    setInterval(updateClock, 1000);
    setInterval(updateCountdown, 1000);

    // Data for charts
    const livestockData = <?php echo json_encode($livestockDistribution); ?>;
    const productionData = <?php echo json_encode($dashboardData['produksi_chart']); ?>;
    const trendData = <?php echo json_encode($dashboardData['trend_bulanan']); ?>;
    const kecamatanData = <?php echo json_encode($dashboardData['statistik_kecamatan']); ?>;

    // Initialize charts when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Distribution Chart
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: livestockData.map(item => item.jenis),
                datasets: [{
                    data: livestockData.map(item => item.jumlah),
                    backgroundColor: [
                        '#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#00BCD4',
                        '#F44336', '#795548', '#607D8B'
                    ],
                    borderWidth: 2,
                    borderColor: 'white'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label;
                                const value = context.raw.toLocaleString();
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${label}: ${value} ekor (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // 2. Production Chart
        if (productionData && productionData.length > 0) {
            const productionCtx = document.getElementById('productionChart').getContext('2d');
            new Chart(productionCtx, {
                type: 'bar',
                data: {
                    labels: productionData.map(item => item.label),
                    datasets: [{
                            label: 'Susu (Liter)',
                            data: productionData.map(item => item.susu),
                            backgroundColor: '#4CAF50',
                            borderWidth: 1
                        },
                        {
                            label: 'Daging (Kg)',
                            data: productionData.map(item => item.daging),
                            backgroundColor: '#2196F3',
                            borderWidth: 1
                        },
                        {
                            label: 'Telur (Butir)',
                            data: productionData.map(item => item.telur),
                            backgroundColor: '#FF9800',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Produksi'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Jenis Peternakan'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label;
                                    const value = context.parsed.y.toLocaleString();
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(item => item.nama_bulan),
                datasets: [{
                        label: 'Survei Pasar',
                        data: trendData.map(item => item.survei),
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Populasi',
                        data: trendData.map(item => item.populasi),
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Vaksinasi',
                        data: trendData.map(item => item.vaksinasi),
                        borderColor: '#FF9800',
                        backgroundColor: 'rgba(255, 152, 0, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Produksi',
                        data: trendData.map(item => item.produksi),
                        borderColor: '#9C27B0',
                        backgroundColor: 'rgba(156, 39, 176, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Data'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });

        // 4. Kecamatan Chart
        if (kecamatanData && kecamatanData.length > 0) {
            const kecamatanCtx = document.getElementById('kecamatanChart').getContext('2d');
            new Chart(kecamatanCtx, {
                type: 'bar',
                data: {
                    labels: kecamatanData.map(item => item.nama_kecamatan),
                    datasets: [{
                        label: 'Jumlah Peternakan',
                        data: kecamatanData.map(item => item.total_peternakan),
                        backgroundColor: kecamatanData.map((_, index) => ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#00BCD4'][index % 5]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Peternakan'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Kecamatan'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Peternakan: ${context.parsed.y} unit`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            // Charts will automatically resize with Chart.js responsive option
        });
    });

    // Auto refresh dashboard every 2 minutes
    setTimeout(() => {
        location.reload();
    }, 120000);
</script>