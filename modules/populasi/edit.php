<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';
$kecamatan_id = $_GET['kecamatan_id'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Validasi input
if (empty($kecamatan_id) || empty($bulan) || empty($tahun)) {
    $_SESSION['error_message'] = 'Parameter tidak lengkap';
    header('Location: ?module=populasi&action=data');
    exit();
}

// Data desa per kecamatan (sama dengan input.php)
$desaData = [
    'Tejakula' => ['Tejakula', 'Sembiran', 'Pacung', 'Bondalem', 'Penuktukan'],
    'Kubutambahan' => ['Kubutambahan', 'Menyali', 'Bila', 'Bengkala', 'Gunungsari'],
    'Sawan' => ['Sawan', 'Sangsit', 'Giri Emas', 'Lemukih', 'Sudaji'],
    'Buleleng' => ['Banyuasri', 'Banyuning', 'Kaliuntu', 'Pancasari', 'Kampung Anyar'],
    'Sukasada' => ['Sukasada', 'Pegayaman', 'Bebetin', 'Sambangan', 'Gitgit'],
    'Banjar' => ['Banjar', 'Banyuatis', 'Banyuseri', 'Cempaga', 'Dencarik'],
    'Seririt' => ['Seririt', 'Joanyar', 'Kalianget', 'Pangkungparuk', 'Patemon'],
    'Gerokgak' => ['Gerokgak', 'Pengulon', 'Patas', 'Penyabangan', 'Sumberklampok'],
    'Busung Biu' => ['Busung Biu', 'Bengkel', 'Bongancina', 'Kedis', 'Kekeran']
];

// Get kecamatan name
$kecamatanData = fetchOne("SELECT id, nama_kecamatan FROM kecamatan WHERE id = ?", [$kecamatan_id]);
if (!$kecamatanData) {
    $_SESSION['error_message'] = 'Kecamatan tidak ditemukan';
    header('Location: ?module=populasi&action=data');
    exit();
}

$kecamatan_name = $kecamatanData['nama_kecamatan'];
$kecamatan = $kecamatan_name;

// Get existing data
$existingData = [];
$sql = "SELECT p.*, d.nama_desa 
        FROM populasi_ternak p
        JOIN desa d ON p.desa_id = d.id
        WHERE p.kecamatan_id = ? 
        AND p.bulan = ? 
        AND p.tahun = ?
        ORDER BY d.nama_desa";
        
$dataRows = fetchAll($sql, [$kecamatan_id, $bulan, $tahun]);

// Organize data by desa
foreach ($dataRows as $row) {
    $existingData[$row['nama_desa']] = $row;
}

// Simpan bulan/tahun lama sebelum perubahan
$old_bulan = $bulan;
$old_tahun = $tahun;
// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_kecamatan = $_POST['kecamatan'] ?? '';
    $new_bulan = $_POST['bulan'] ?? $bulan;
    $new_tahun = $_POST['tahun'] ?? $tahun;
    
        $periode_berubah = ($new_bulan != $bulan) || ($new_tahun != $tahun);
    
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        
        $totalDesaDiperbarui = 0;
        
        
        // Loop melalui setiap desa di kecamatan
        foreach ($desaData[$kecamatan] as $desa) {
            $desaId = getDesaId($desa, $kecamatan_id);
            
            if ($desaId == 0) {
                continue; // Skip jika desa tidak ditemukan
            }
            
            // Prepare data untuk desa ini
            $data = [
                // ===== SAPA BALI =====
                'sapi_bali_jantan' => intval($_POST[$desa . '_sapi_bali_jantan'] ?? 0),
                'sapi_bali_betina' => intval($_POST[$desa . '_sapi_bali_betina'] ?? 0),
                'sapi_bali_total' => 0,
                
                // ===== KERBAU =====
                'kerbau_jantan' => intval($_POST[$desa . '_kerbau_jantan'] ?? 0),
                'kerbau_betina' => intval($_POST[$desa . '_kerbau_betina'] ?? 0),
                'kerbau_total' => 0,
                
                // ===== KUDA =====
                'kuda_jantan' => intval($_POST[$desa . '_kuda_jantan'] ?? 0),
                'kuda_betina' => intval($_POST[$desa . '_kuda_betina'] ?? 0),
                'kuda_total' => 0,
                
                // ===== BABI BALI =====
                'babi_bali_induk' => intval($_POST[$desa . '_babi_bali_induk'] ?? 0),
                'babi_bali_betina' => intval($_POST[$desa . '_babi_bali_betina'] ?? 0),
                'babi_bali_jantan' => intval($_POST[$desa . '_babi_bali_jantan'] ?? 0),
                'babi_bali_total' => 0,
                
                // ===== BABI LANDRACE =====
                'babi_landrace_induk' => intval($_POST[$desa . '_babi_landrace_induk'] ?? 0),
                'babi_landrace_betina' => intval($_POST[$desa . '_babi_landrace_betina'] ?? 0),
                'babi_landrace_jantan' => intval($_POST[$desa . '_babi_landrace_jantan'] ?? 0),
                'babi_landrace_total' => 0,
                
                // ===== KAMBING POTONG =====
                'kambing_potong_jantan' => intval($_POST[$desa . '_kambing_potong_jantan'] ?? 0),
                'kambing_potong_betina' => intval($_POST[$desa . '_kambing_potong_betina'] ?? 0),
                'kambing_potong_total' => 0,
                
                // ===== KAMBING PERAH =====
                'kambing_perah_jantan' => intval($_POST[$desa . '_kambing_perah_jantan'] ?? 0),
                'kambing_perah_betina' => intval($_POST[$desa . '_kambing_perah_betina'] ?? 0),
                'kambing_perah_total' => 0,
                'kambing_total' => 0,
                
                // ===== UNGGAS - AYAM =====
                'ayam_buras' => intval($_POST[$desa . '_ayam_buras'] ?? 0),
                'ayam_petelur' => intval($_POST[$desa . '_ayam_petelur'] ?? 0),
                'ayam_pedaging' => intval($_POST[$desa . '_ayam_pedaging'] ?? 0),
                'ayam_total' => 0,
                
                // ===== UNGGAS - BEBEK =====
                'bebek_itik' => intval($_POST[$desa . '_bebek_itik'] ?? 0),
                'bebek_manila' => intval($_POST[$desa . '_bebek_manila'] ?? 0),
                'bebek_total' => 0,
                'unggas_total' => 0,
                
                // ===== ANJING =====
                'anjing_total' => intval($_POST[$desa . '_anjing_total'] ?? 0),
                
                'total_semua' => 0
            ];
            
            // Calculate totals
            $data['sapi_bali_total'] = $data['sapi_bali_jantan'] + $data['sapi_bali_betina'];
            $data['kerbau_total'] = $data['kerbau_jantan'] + $data['kerbau_betina'];
            $data['kuda_total'] = $data['kuda_jantan'] + $data['kuda_betina'];
            $data['babi_bali_total'] = $data['babi_bali_induk'] + $data['babi_bali_betina'] + $data['babi_bali_jantan'];
            $data['babi_landrace_total'] = $data['babi_landrace_induk'] + $data['babi_landrace_betina'] + $data['babi_landrace_jantan'];
            $data['kambing_potong_total'] = $data['kambing_potong_jantan'] + $data['kambing_potong_betina'];
            $data['kambing_perah_total'] = $data['kambing_perah_jantan'] + $data['kambing_perah_betina'];
            $data['kambing_total'] = $data['kambing_potong_total'] + $data['kambing_perah_total'];
            $data['ayam_total'] = $data['ayam_buras'] + $data['ayam_petelur'] + $data['ayam_pedaging'];
            $data['bebek_total'] = $data['bebek_itik'] + $data['bebek_manila'];
            $data['unggas_total'] = $data['ayam_total'] + $data['bebek_total'];
            
            // Calculate grand total
            $data['total_semua'] = 
                $data['sapi_bali_total'] + 
                $data['kerbau_total'] + 
                $data['kuda_total'] + 
                $data['babi_bali_total'] + 
                $data['babi_landrace_total'] + 
                $data['kambing_total'] + 
                $data['unggas_total'] + 
                $data['anjing_total'];

                
            
            // Check if data exists for this desa, bulan, tahun
            $checkSql = "SELECT id FROM populasi_ternak 
                        WHERE kecamatan_id = ? AND desa_id = ? AND bulan = ? AND tahun = ?";
            $existing = fetchOne($checkSql, [
                $kecamatan_id, 
                $desaId, 
                $bulan, 
                $tahun
            ]);
            
            if ($existing) {
                // Update existing data
                $sql = "UPDATE populasi_ternak SET 
                        bulan = ?, tahun = ?,
                        sapi_bali_jantan = ?, sapi_bali_betina = ?, sapi_bali_total = ?,
                        kerbau_jantan = ?, kerbau_betina = ?, kerbau_total = ?,
                        kuda_jantan = ?, kuda_betina = ?, kuda_total = ?,
                        babi_bali_induk = ?, babi_bali_betina = ?, babi_bali_jantan = ?, babi_bali_total = ?,
                        babi_landrace_induk = ?, babi_landrace_betina = ?, babi_landrace_jantan = ?, babi_landrace_total = ?,
                        kambing_potong_jantan = ?, kambing_potong_betina = ?, kambing_potong_total = ?,
                        kambing_perah_jantan = ?, kambing_perah_betina = ?, kambing_perah_total = ?,
                        kambing_total = ?,
                        ayam_buras = ?, ayam_petelur = ?, ayam_pedaging = ?, ayam_total = ?,
                        bebek_itik = ?, bebek_manila = ?, bebek_total = ?,
                        unggas_total = ?,
                        anjing_total = ?,
                        total_semua = ?,
                        updated_at = NOW()
                        WHERE id = ?";
                
                $params = [
                    $bulan, $tahun,
                    $data['sapi_bali_jantan'], $data['sapi_bali_betina'], $data['sapi_bali_total'],
                    $data['kerbau_jantan'], $data['kerbau_betina'], $data['kerbau_total'],
                    $data['kuda_jantan'], $data['kuda_betina'], $data['kuda_total'],
                    $data['babi_bali_induk'], $data['babi_bali_betina'], $data['babi_bali_jantan'], $data['babi_bali_total'],
                    $data['babi_landrace_induk'], $data['babi_landrace_betina'], $data['babi_landrace_jantan'], $data['babi_landrace_total'],
                    $data['kambing_potong_jantan'], $data['kambing_potong_betina'], $data['kambing_potong_total'],
                    $data['kambing_perah_jantan'], $data['kambing_perah_betina'], $data['kambing_perah_total'],
                    $data['kambing_total'],
                    $data['ayam_buras'], $data['ayam_petelur'], $data['ayam_pedaging'], $data['ayam_total'],
                    $data['bebek_itik'], $data['bebek_manila'], $data['bebek_total'],
                    $data['unggas_total'],
                    $data['anjing_total'],
                    $data['total_semua'],
                    $existing['id']
                ];
            } else {
                // Insert new data (shouldn't happen in edit, but just in case)
                $sql = "INSERT INTO populasi_ternak 
                        (kecamatan_id, desa_id, bulan, tahun,
                        sapi_bali_jantan, sapi_bali_betina, sapi_bali_total,
                        kerbau_jantan, kerbau_betina, kerbau_total,
                        kuda_jantan, kuda_betina, kuda_total,
                        babi_bali_induk, babi_bali_betina, babi_bali_jantan, babi_bali_total,
                        babi_landrace_induk, babi_landrace_betina, babi_landrace_jantan, babi_landrace_total,
                        kambing_potong_jantan, kambing_potong_betina, kambing_potong_total,
                        kambing_perah_jantan, kambing_perah_betina, kambing_perah_total,
                        kambing_total,
                        ayam_buras, ayam_petelur, ayam_pedaging, ayam_total,
                        bebek_itik, bebek_manila, bebek_total,
                        unggas_total,
                        anjing_total,
                        total_semua, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $params = [
                    $kecamatan_id, $desaId, $new_bulan, $new_tahun,
                    $data['sapi_bali_jantan'], $data['sapi_bali_betina'], $data['sapi_bali_total'],
                    $data['kerbau_jantan'], $data['kerbau_betina'], $data['kerbau_total'],
                    $data['kuda_jantan'], $data['kuda_betina'], $data['kuda_total'],
                    $data['babi_bali_induk'], $data['babi_bali_betina'], $data['babi_bali_jantan'], $data['babi_bali_total'],
                    $data['babi_landrace_induk'], $data['babi_landrace_betina'], $data['babi_landrace_jantan'], $data['babi_landrace_total'],
                    $data['kambing_potong_jantan'], $data['kambing_potong_betina'], $data['kambing_potong_total'],
                    $data['kambing_perah_jantan'], $data['kambing_perah_betina'], $data['kambing_perah_total'],
                    $data['kambing_total'],
                    $data['ayam_buras'], $data['ayam_petelur'], $data['ayam_pedaging'], $data['ayam_total'],
                    $data['bebek_itik'], $data['bebek_manila'], $data['bebek_total'],
                    $data['unggas_total'],
                    $data['anjing_total'],
                    $data['total_semua'],
                    $_SESSION['user_id']
                ];
            }
            
            executeQuery($sql, $params);
            $totalDesaDiperbarui++;
        }
        
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

        
        $success = "✅ Data populasi berhasil diperbarui untuk kecamatan $kecamatan ($totalDesaDiperbarui desa)";
        
        // Reload data after update
       $reloadSql = "SELECT pt.*, d.nama_desa
              FROM populasi_ternak pt
              JOIN desa d ON pt.desa_id = d.id
              WHERE pt.kecamatan_id = ?
                AND pt.bulan = ?
                AND pt.tahun = ?";

        $dataRows = fetchAll($reloadSql, [$kecamatan_id, $bulan, $tahun]);

        $existingData = [];
        foreach ($dataRows as $row) {
            $existingData[$row['nama_desa']] = $row;
        }
        
    } catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = "❌ Error: " . $e->getMessage();
}

}

// Helper function
function getDesaId($nama, $kecamatanId) {
    $sql = "SELECT id FROM desa WHERE nama_desa = ? AND kecamatan_id = ?";
    $result = fetchOne($sql, [$nama, $kecamatanId]);
    return $result['id'] ?? 0;
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-edit"></i> Edit Data Populasi Ternak</h2>
        <p class="subtitle">
            Kecamatan: <strong><?php echo $kecamatan; ?></strong> | 
            Periode: <strong><?php echo DateTime::createFromFormat('!m', $bulan)->format('F'); ?> <?php echo $tahun; ?></strong>
        </p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <?php echo $success; ?>
            <br>
            <a href="?module=populasi&action=data" class="btn btn-sm btn-primary mt-2">
                <i class="fas fa-list"></i> Kembali ke Data Populasi
            </a>
            <a href="?module=populasi&action=detail&kecamatan=<?php echo urlencode($kecamatan); ?>&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" 
               class="btn btn-sm btn-info mt-2">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> 
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="" id="populasiForm">
            <!-- Form Header untuk periode -->
            <div class="form-section">
                <h3><i class="fas fa-calendar"></i> Periode Data</h3>
                <p class="form-description">Periode data yang akan diedit</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kecamatan">
                            <i class="fas fa-landmark"></i> Kecamatan
                        </label>
                        <input type="text" id="kecamatan" name="kecamatan" 
                               value="<?php echo $kecamatan; ?>" readonly
                               class="form-control readonly-input">
                        <small class="form-text">Kecamatan tidak dapat diubah</small>
                    </div>
                    
                 <div class="form-group">
                    <label for="bulan">
                        <i class="fas fa-calendar"></i> Bulan
                    </label>
                    <!-- Select hanya untuk tampilan -->
                    <select id="bulan_display" disabled class="form-control">
                        <?php 
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        foreach ($months as $num => $name): 
                            $monthNum = str_pad($num, 2, '0', STR_PAD_LEFT);
                        ?>
                            <option value="<?php echo $monthNum; ?>" 
                                <?php echo $bulan == $monthNum ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Hidden input agar nilai tetap dikirim saat submit -->
                    <input type="hidden" name="bulan" value="<?php echo $bulan; ?>">
                </div>

                    
                    <div class="form-group">
                        <label for="tahun">
                            <i class="fas fa-calendar-alt"></i> Tahun
                        </label>
                        <input type="number" id="tahun" name="tahun" 
                               value="<?php echo $tahun; ?>"
                               min="2020" max="2030" readonly
                               class="form-control">
                    </div>
                </div>
            </div>

            <!-- Container untuk data desa -->
            <div id="desaDataContainer" class="desa-container">
                <?php if (empty($desaData[$kecamatan])): ?>
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Data desa tidak ditemukan</h4>
                        <p>Tidak ada data desa untuk kecamatan <?php echo $kecamatan; ?></p>
                    </div>
                <?php else: ?>
                    <div class="form-section">
                        <h3><i class="fas fa-list-ol"></i> Edit Data Populasi per Desa</h3>
                        <p class="form-description">Edit data populasi untuk setiap desa di Kecamatan <?php echo $kecamatan; ?></p>
                        
                        <!-- Navigation Tabs -->
                        <div class="tabs-navigation">
                            <button type="button" class="tab-btn active" onclick="showTab('sapi', this)">
                                <i class="fas fa-cow"></i> Sapi, Kerbau & Kuda
                            </button>
                            <button type="button" class="tab-btn" onclick="showTab('babi', this)">
                                <i class="fas fa-piggy-bank"></i> Babi
                            </button>
                            <button type="button" class="tab-btn" onclick="showTab('kambing', this)">
                                <i class="fas fa-sheep"></i> Kambing
                            </button>
                            <button type="button" class="tab-btn" onclick="showTab('unggas', this)">
                                <i class="fas fa-egg"></i> Unggas & Anjing
                            </button>
                        </div>
                        
                        <!-- Tab Contents -->
                        <div class="tab-contents">
                            <!-- Tab 1: Sapi, Kerbau, Kuda -->
                            <div class="tab-content active" id="sapiTab">
                                <div class="table-container">
                                    <table class="data-table striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Nama Desa</th>
                                                <th colspan="3" class="text-center">Sapi Bali</th>
                                                <th colspan="3" class="text-center">Kerbau</th>
                                                <th colspan="3" class="text-center">Kuda</th>
                                                <th rowspan="2" class="total-col">Total Desa</th>
                                            </tr>
                                            <tr>
                                                <th>Jantan</th>
                                                <th>Betina</th>
                                                <th>Total</th>
                                                <th>Jantan</th>
                                                <th>Betina</th>
                                                <th>Total</th>
                                                <th>Jantan</th>
                                                <th>Betina</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($desaData[$kecamatan] as $desa): ?>
                                                <?php 
                                                $desaId = $desa;
                                                $data = $existingData[$desa] ?? [];
                                                ?>
                                                <tr data-desa="<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                    <td class="desa-name">
                                                        <strong><?php echo $desa; ?></strong>
                                                    </td>
                                                    
                                                    <!-- Sapi Bali -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_sapi_bali_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['sapi_bali_jantan'] ?? 0; ?>" 
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'sapi')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_sapi_bali_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['sapi_bali_betina'] ?? 0; ?>" 
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'sapi')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_sapi_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['sapi_bali_jantan'] ?? 0) + ($data['sapi_bali_betina'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Kerbau -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kerbau_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kerbau_jantan'] ?? 0; ?>"
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'kerbau')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kerbau_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kerbau_betina'] ?? 0; ?>"
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'kerbau')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_kerbau_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['kerbau_jantan'] ?? 0) + ($data['kerbau_betina'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Kuda -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kuda_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kuda_jantan'] ?? 0; ?>"
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'kuda')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kuda_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kuda_betina'] ?? 0; ?>"
                                                               oninput="calculateRowTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'kuda')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_kuda_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['kuda_jantan'] ?? 0) + ($data['kuda_betina'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Total Desa -->
                                                    <td class="total-cell grand-total">
                                                        <strong><span id="total_desa_sapi_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php 
                                                            $totalSapi = ($data['sapi_bali_jantan'] ?? 0) + ($data['sapi_bali_betina'] ?? 0);
                                                            $totalKerbau = ($data['kerbau_jantan'] ?? 0) + ($data['kerbau_betina'] ?? 0);
                                                            $totalKuda = ($data['kuda_jantan'] ?? 0) + ($data['kuda_betina'] ?? 0);
                                                            echo $totalSapi + $totalKerbau + $totalKuda;
                                                            ?>
                                                        </span></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab 2: Babi -->
                            <div class="tab-content" id="babiTab">
                                <div class="table-container">
                                    <table class="data-table striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Nama Desa</th>
                                                <th colspan="4" class="text-center">Babi Bali</th>
                                                <th colspan="4" class="text-center">Babi Landrace</th>
                                                <th rowspan="2" class="total-col">Total Babi</th>
                                            </tr>
                                            <tr>
                                                <th>Induk</th>
                                                <th>Betina</th>
                                                <th>Jantan</th>
                                                <th>Total</th>
                                                <th>Induk</th>
                                                <th>Betina</th>
                                                <th>Jantan</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($desaData[$kecamatan] as $desa): ?>
                                                <?php $data = $existingData[$desa] ?? []; ?>
                                                <tr data-desa="<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                    <td class="desa-name">
                                                        <strong><?php echo $desa; ?></strong>
                                                    </td>
                                                    
                                                    <!-- Babi Bali -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_bali_induk" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_bali_induk'] ?? 0; ?>" 
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'bali')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_bali_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_bali_betina'] ?? 0; ?>" 
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'bali')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_bali_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_bali_jantan'] ?? 0; ?>" 
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'bali')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_babi_bali_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['babi_bali_induk'] ?? 0) + ($data['babi_bali_betina'] ?? 0) + ($data['babi_bali_jantan'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Babi Landrace -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_landrace_induk" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_landrace_induk'] ?? 0; ?>"
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'landrace')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_landrace_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_landrace_betina'] ?? 0; ?>"
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'landrace')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_babi_landrace_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['babi_landrace_jantan'] ?? 0; ?>"
                                                               oninput="calculateBabiTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'landrace')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_babi_landrace_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['babi_landrace_induk'] ?? 0) + ($data['babi_landrace_betina'] ?? 0) + ($data['babi_landrace_jantan'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Total Babi -->
                                                    <td class="total-cell grand-total">
                                                        <strong><span id="total_desa_babi_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php 
                                                            $totalBali = ($data['babi_bali_induk'] ?? 0) + ($data['babi_bali_betina'] ?? 0) + ($data['babi_bali_jantan'] ?? 0);
                                                            $totalLandrace = ($data['babi_landrace_induk'] ?? 0) + ($data['babi_landrace_betina'] ?? 0) + ($data['babi_landrace_jantan'] ?? 0);
                                                            echo $totalBali + $totalLandrace;
                                                            ?>
                                                        </span></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab 3: Kambing -->
                            <div class="tab-content" id="kambingTab">
                                <div class="table-container">
                                    <table class="data-table striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Nama Desa</th>
                                                <th colspan="3" class="text-center">Kambing Potong</th>
                                                <th colspan="3" class="text-center">Kambing Perah</th>
                                                <th rowspan="2" class="total-col">Total Kambing</th>
                                            </tr>
                                            <tr>
                                                <th>Jantan</th>
                                                <th>Betina</th>
                                                <th>Total</th>
                                                <th>Jantan</th>
                                                <th>Betina</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($desaData[$kecamatan] as $desa): ?>
                                                <?php $data = $existingData[$desa] ?? []; ?>
                                                <tr data-desa="<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                    <td class="desa-name">
                                                        <strong><?php echo $desa; ?></strong>
                                                    </td>
                                                    
                                                    <!-- Kambing Potong -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kambing_potong_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kambing_potong_jantan'] ?? 0; ?>" 
                                                               oninput="calculateKambingTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'potong')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kambing_potong_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kambing_potong_betina'] ?? 0; ?>" 
                                                               oninput="calculateKambingTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'potong')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_kambing_potong_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['kambing_potong_jantan'] ?? 0) + ($data['kambing_potong_betina'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Kambing Perah -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kambing_perah_jantan" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kambing_perah_jantan'] ?? 0; ?>"
                                                               oninput="calculateKambingTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'perah')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_kambing_perah_betina" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['kambing_perah_betina'] ?? 0; ?>"
                                                               oninput="calculateKambingTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>', 'perah')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_kambing_perah_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['kambing_perah_jantan'] ?? 0) + ($data['kambing_perah_betina'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Total Kambing -->
                                                    <td class="total-cell grand-total">
                                                        <strong><span id="total_desa_kambing_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php 
                                                            $totalPotong = ($data['kambing_potong_jantan'] ?? 0) + ($data['kambing_potong_betina'] ?? 0);
                                                            $totalPerah = ($data['kambing_perah_jantan'] ?? 0) + ($data['kambing_perah_betina'] ?? 0);
                                                            echo $totalPotong + $totalPerah;
                                                            ?>
                                                        </span></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab 4: Unggas & Anjing -->
                            <div class="tab-content" id="unggasTab">
                                <div class="table-container">
                                    <table class="data-table striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Nama Desa</th>
                                                <th colspan="4" class="text-center">Ayam</th>
                                                <th colspan="3" class="text-center">Bebek</th>
                                                <th rowspan="2">Anjing</th>
                                                <th rowspan="2" class="total-col">Total Unggas</th>
                                            </tr>
                                            <tr>
                                                <th>Buras</th>
                                                <th>Petelur</th>
                                                <th>Pedaging</th>
                                                <th>Total</th>
                                                <th>Itik</th>
                                                <th>Manila</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($desaData[$kecamatan] as $desa): ?>
                                                <?php $data = $existingData[$desa] ?? []; ?>
                                                <tr data-desa="<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                    <td class="desa-name">
                                                        <strong><?php echo $desa; ?></strong>
                                                    </td>
                                                    
                                                    <!-- Ayam -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_ayam_buras" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['ayam_buras'] ?? 0; ?>" 
                                                               oninput="calculateAyamTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_ayam_petelur" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['ayam_petelur'] ?? 0; ?>" 
                                                               oninput="calculateAyamTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_ayam_pedaging" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['ayam_pedaging'] ?? 0; ?>" 
                                                               oninput="calculateAyamTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_ayam_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['ayam_buras'] ?? 0) + ($data['ayam_petelur'] ?? 0) + ($data['ayam_pedaging'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Bebek -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_bebek_itik" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['bebek_itik'] ?? 0; ?>"
                                                               oninput="calculateBebekTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_bebek_manila" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['bebek_manila'] ?? 0; ?>"
                                                               oninput="calculateBebekTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    <td class="total-cell">
                                                        <span class="total-value" id="total_bebek_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php echo ($data['bebek_itik'] ?? 0) + ($data['bebek_manila'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Anjing -->
                                                    <td>
                                                        <input type="number" 
                                                               name="<?php echo $desa; ?>_anjing_total" 
                                                               class="form-input small-number" 
                                                               min="0" 
                                                               value="<?php echo $data['anjing_total'] ?? 0; ?>"
                                                               oninput="calculateUnggasTotal('<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>')"
                                                               placeholder="0">
                                                    </td>
                                                    
                                                    <!-- Total Unggas -->
                                                    <td class="total-cell grand-total">
                                                        <strong><span id="total_desa_unggas_<?php echo htmlspecialchars(str_replace(' ', '_', strtolower($desa))); ?>">
                                                            <?php 
                                                            $totalAyam = ($data['ayam_buras'] ?? 0) + ($data['ayam_petelur'] ?? 0) + ($data['ayam_pedaging'] ?? 0);
                                                            $totalBebek = ($data['bebek_itik'] ?? 0) + ($data['bebek_manila'] ?? 0);
                                                            echo $totalAyam + $totalBebek;
                                                            ?>
                                                        </span></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Summary Section -->
                        <div class="summary-section" id="summarySection">
                            <h4><i class="fas fa-chart-bar"></i> Ringkasan Total Kecamatan</h4>
                            <div class="summary-grid" id="summaryGrid">
                                <!-- Summary akan diisi oleh JavaScript -->
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Section -->
            <div class="form-section submit-section">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Update Data Populasi
                    </button>
                    <a href="?module=populasi&action=data" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="button" class="btn btn-danger btn-lg" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Hapus Data
                    </button>
                    <button type="button" class="btn btn-info btn-lg" onclick="calculateAllTotals()">
                        <i class="fas fa-calculator"></i> Hitung Total
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript sama dengan input.php -->
<script>
// Data desa per kecamatan
const desaData = <?php echo json_encode($desaData[$kecamatan]); ?>;
const kecamatanName = "<?php echo $kecamatan; ?>";

// Tab management
let currentTab = 'sapi';

// Fungsi-fungsi kalkulasi sama dengan input.php
function calculateRowTotal(desaId, jenis) {
    const jantan = parseInt(document.getElementsByName(`${desaId}_${jenis}_jantan`)[0]?.value) || 0;
    const betina = parseInt(document.getElementsByName(`${desaId}_${jenis}_betina`)[0]?.value) || 0;
    const total = jantan + betina;
    
    document.getElementById(`total_${jenis}_${desaId}`).textContent = total;
    
    if (jenis === 'sapi' || jenis === 'kerbau' || jenis === 'kuda') {
        calculateSapiDesaTotal(desaId);
    }
    
    calculateGrandTotals();
}

function calculateBabiTotal(desaId, jenisBabi) {
    const induk = parseInt(document.getElementsByName(`${desaId}_babi_${jenisBabi}_induk`)[0]?.value) || 0;
    const betina = parseInt(document.getElementsByName(`${desaId}_babi_${jenisBabi}_betina`)[0]?.value) || 0;
    const jantan = parseInt(document.getElementsByName(`${desaId}_babi_${jenisBabi}_jantan`)[0]?.value) || 0;
    const total = induk + betina + jantan;
    
    document.getElementById(`total_babi_${jenisBabi}_${desaId}`).textContent = total;
    
    const totalBali = parseInt(document.getElementById(`total_babi_bali_${desaId}`).textContent) || 0;
    const totalLandrace = parseInt(document.getElementById(`total_babi_landrace_${desaId}`).textContent) || 0;
    document.getElementById(`total_desa_babi_${desaId}`).textContent = totalBali + totalLandrace;
    
    calculateGrandTotals();
}

function calculateKambingTotal(desaId, jenisKambing) {
    const jantan = parseInt(document.getElementsByName(`${desaId}_kambing_${jenisKambing}_jantan`)[0]?.value) || 0;
    const betina = parseInt(document.getElementsByName(`${desaId}_kambing_${jenisKambing}_betina`)[0]?.value) || 0;
    const total = jantan + betina;
    
    document.getElementById(`total_kambing_${jenisKambing}_${desaId}`).textContent = total;
    
    const totalPotong = parseInt(document.getElementById(`total_kambing_potong_${desaId}`).textContent) || 0;
    const totalPerah = parseInt(document.getElementById(`total_kambing_perah_${desaId}`).textContent) || 0;
    document.getElementById(`total_desa_kambing_${desaId}`).textContent = totalPotong + totalPerah;
    
    calculateGrandTotals();
}

function calculateAyamTotal(desaId) {
    const buras = parseInt(document.getElementsByName(`${desaId}_ayam_buras`)[0]?.value) || 0;
    const petelur = parseInt(document.getElementsByName(`${desaId}_ayam_petelur`)[0]?.value) || 0;
    const pedaging = parseInt(document.getElementsByName(`${desaId}_ayam_pedaging`)[0]?.value) || 0;
    const total = buras + petelur + pedaging;
    
    document.getElementById(`total_ayam_${desaId}`).textContent = total;
    calculateUnggasTotal(desaId);
}

function calculateBebekTotal(desaId) {
    const itik = parseInt(document.getElementsByName(`${desaId}_bebek_itik`)[0]?.value) || 0;
    const manila = parseInt(document.getElementsByName(`${desaId}_bebek_manila`)[0]?.value) || 0;
    const total = itik + manila;
    
    document.getElementById(`total_bebek_${desaId}`).textContent = total;
    calculateUnggasTotal(desaId);
}

function calculateUnggasTotal(desaId) {
    const totalAyam = parseInt(document.getElementById(`total_ayam_${desaId}`).textContent) || 0;
    const totalBebek = parseInt(document.getElementById(`total_bebek_${desaId}`).textContent) || 0;
    document.getElementById(`total_desa_unggas_${desaId}`).textContent = totalAyam + totalBebek;
    
    calculateGrandTotals();
}

function calculateSapiDesaTotal(desaId) {
    const totalSapi = parseInt(document.getElementById(`total_sapi_${desaId}`).textContent) || 0;
    const totalKerbau = parseInt(document.getElementById(`total_kerbau_${desaId}`).textContent) || 0;
    const totalKuda = parseInt(document.getElementById(`total_kuda_${desaId}`).textContent) || 0;
    document.getElementById(`total_desa_sapi_${desaId}`).textContent = totalSapi + totalKerbau + totalKuda;
}

// Hitung semua grand total
function calculateAllTotals() {
    let grandTotals = {
        // Sapi Section
        sapi_jantan: 0, sapi_betina: 0, sapi_total: 0,
        kerbau_jantan: 0, kerbau_betina: 0, kerbau_total: 0,
        kuda_jantan: 0, kuda_betina: 0, kuda_total: 0,
        sapi_section_total: 0,
        
        // Babi Section
        babi_bali_induk: 0, babi_bali_betina: 0, babi_bali_jantan: 0, babi_bali_total: 0,
        babi_landrace_induk: 0, babi_landrace_betina: 0, babi_landrace_jantan: 0, babi_landrace_total: 0,
        babi_section_total: 0,
        
        // Kambing Section
        kambing_potong_jantan: 0, kambing_potong_betina: 0, kambing_potong_total: 0,
        kambing_perah_jantan: 0, kambing_perah_betina: 0, kambing_perah_total: 0,
        kambing_section_total: 0,
        
        // Unggas Section
        ayam_buras: 0, ayam_petelur: 0, ayam_pedaging: 0, ayam_total: 0,
        bebek_itik: 0, bebek_manila: 0, bebek_total: 0,
        anjing_total: 0,
        unggas_section_total: 0,
        
        // Overall
        total_semua: 0
    };
    
    // Hitung untuk setiap desa
    desaData.forEach((desa) => {
        const desaId = desa.replace(/\s+/g, '_').toLowerCase();
        
        // Sapi Section
        const sapi_j = parseInt(document.getElementsByName(`${desa}_sapi_bali_jantan`)[0]?.value) || 0;
        const sapi_b = parseInt(document.getElementsByName(`${desa}_sapi_bali_betina`)[0]?.value) || 0;
        grandTotals.sapi_jantan += sapi_j;
        grandTotals.sapi_betina += sapi_b;
        grandTotals.sapi_total += (sapi_j + sapi_b);
        
        // Kerbau
        const kerbau_j = parseInt(document.getElementsByName(`${desa}_kerbau_jantan`)[0]?.value) || 0;
        const kerbau_b = parseInt(document.getElementsByName(`${desa}_kerbau_betina`)[0]?.value) || 0;
        grandTotals.kerbau_jantan += kerbau_j;
        grandTotals.kerbau_betina += kerbau_b;
        grandTotals.kerbau_total += (kerbau_j + kerbau_b);
        
        // Kuda
        const kuda_j = parseInt(document.getElementsByName(`${desa}_kuda_jantan`)[0]?.value) || 0;
        const kuda_b = parseInt(document.getElementsByName(`${desa}_kuda_betina`)[0]?.value) || 0;
        grandTotals.kuda_jantan += kuda_j;
        grandTotals.kuda_betina += kuda_b;
        grandTotals.kuda_total += (kuda_j + kuda_b);
        
        // Babi Bali
        const babi_bali_i = parseInt(document.getElementsByName(`${desa}_babi_bali_induk`)[0]?.value) || 0;
        const babi_bali_b = parseInt(document.getElementsByName(`${desa}_babi_bali_betina`)[0]?.value) || 0;
        const babi_bali_j = parseInt(document.getElementsByName(`${desa}_babi_bali_jantan`)[0]?.value) || 0;
        grandTotals.babi_bali_induk += babi_bali_i;
        grandTotals.babi_bali_betina += babi_bali_b;
        grandTotals.babi_bali_jantan += babi_bali_j;
        grandTotals.babi_bali_total += (babi_bali_i + babi_bali_b + babi_bali_j);
        
        // Babi Landrace
        const babi_landrace_i = parseInt(document.getElementsByName(`${desa}_babi_landrace_induk`)[0]?.value) || 0;
        const babi_landrace_b = parseInt(document.getElementsByName(`${desa}_babi_landrace_betina`)[0]?.value) || 0;
        const babi_landrace_j = parseInt(document.getElementsByName(`${desa}_babi_landrace_jantan`)[0]?.value) || 0;
        grandTotals.babi_landrace_induk += babi_landrace_i;
        grandTotals.babi_landrace_betina += babi_landrace_b;
        grandTotals.babi_landrace_jantan += babi_landrace_j;
        grandTotals.babi_landrace_total += (babi_landrace_i + babi_landrace_b + babi_landrace_j);
        
        // Kambing Potong
        const kambing_potong_j = parseInt(document.getElementsByName(`${desa}_kambing_potong_jantan`)[0]?.value) || 0;
        const kambing_potong_b = parseInt(document.getElementsByName(`${desa}_kambing_potong_betina`)[0]?.value) || 0;
        grandTotals.kambing_potong_jantan += kambing_potong_j;
        grandTotals.kambing_potong_betina += kambing_potong_b;
        grandTotals.kambing_potong_total += (kambing_potong_j + kambing_potong_b);
        
        // Kambing Perah
        const kambing_perah_j = parseInt(document.getElementsByName(`${desa}_kambing_perah_jantan`)[0]?.value) || 0;
        const kambing_perah_b = parseInt(document.getElementsByName(`${desa}_kambing_perah_betina`)[0]?.value) || 0;
        grandTotals.kambing_perah_jantan += kambing_perah_j;
        grandTotals.kambing_perah_betina += kambing_perah_b;
        grandTotals.kambing_perah_total += (kambing_perah_j + kambing_perah_b);
        
        // Unggas
        const ayam_buras = parseInt(document.getElementsByName(`${desa}_ayam_buras`)[0]?.value) || 0;
        const ayam_petelur = parseInt(document.getElementsByName(`${desa}_ayam_petelur`)[0]?.value) || 0;
        const ayam_pedaging = parseInt(document.getElementsByName(`${desa}_ayam_pedaging`)[0]?.value) || 0;
        grandTotals.ayam_buras += ayam_buras;
        grandTotals.ayam_petelur += ayam_petelur;
        grandTotals.ayam_pedaging += ayam_pedaging;
        grandTotals.ayam_total += (ayam_buras + ayam_petelur + ayam_pedaging);
        
        const bebek_itik = parseInt(document.getElementsByName(`${desa}_bebek_itik`)[0]?.value) || 0;
        const bebek_manila = parseInt(document.getElementsByName(`${desa}_bebek_manila`)[0]?.value) || 0;
        grandTotals.bebek_itik += bebek_itik;
        grandTotals.bebek_manila += bebek_manila;
        grandTotals.bebek_total += (bebek_itik + bebek_manila);
        
        // Anjing
        const anjing = parseInt(document.getElementsByName(`${desa}_anjing_total`)[0]?.value) || 0;
        grandTotals.anjing_total += anjing;
    });
    
    // Hitung section totals
    grandTotals.sapi_section_total = grandTotals.sapi_total + grandTotals.kerbau_total + grandTotals.kuda_total;
    grandTotals.babi_section_total = grandTotals.babi_bali_total + grandTotals.babi_landrace_total;
    grandTotals.kambing_section_total = grandTotals.kambing_potong_total + grandTotals.kambing_perah_total;
    grandTotals.unggas_section_total = grandTotals.ayam_total + grandTotals.bebek_total;
    
    // Hitung total semua
    grandTotals.total_semua = 
        grandTotals.sapi_section_total + 
        grandTotals.babi_section_total + 
        grandTotals.kambing_section_total + 
        grandTotals.unggas_section_total + 
        grandTotals.anjing_total;
    
    // Update summary section
    updateSummarySection(grandTotals);
}

function updateSummarySection(totals) {
    const summaryGrid = document.getElementById('summaryGrid');
    if (!summaryGrid) return;
    
    summaryGrid.innerHTML = `
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #4CAF50;">
                <i class="fas fa-cow"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.sapi_total)}</h4>
                <p>Sapi Bali</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #2196F3;">
                <i class="fas fa-hippo"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.kerbau_total)}</h4>
                <p>Kerbau</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #FF9800;">
                <i class="fas fa-horse"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.kuda_total)}</h4>
                <p>Kuda</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #F44336;">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.babi_section_total)}</h4>
                <p>Babi</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #9C27B0;">
                <i class="fas fa-sheep"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.kambing_section_total)}</h4>
                <p>Kambing</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #00BCD4;">
                <i class="fas fa-egg"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.unggas_section_total)}</h4>
                <p>Unggas</p>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background-color: #795548;">
                <i class="fas fa-dog"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.anjing_total)}</h4>
                <p>Anjing</p>
            </div>
        </div>
        <div class="summary-item total-summary">
            <div class="summary-icon" style="background-color: #607D8B;">
                <i class="fas fa-paw"></i>
            </div>
            <div class="summary-content">
                <h4>${formatNumber(totals.total_semua)}</h4>
                <p><strong>TOTAL SEMUA TERNAK</strong></p>
            </div>
        </div>`;
}

// ========== HELPER FUNCTIONS ==========

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

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
    currentTab = tabName;
    
    // Recalculate totals for current tab
    calculateAllTotals();
}

function calculateGrandTotals() {
    calculateAllTotals();
}

// Konfirmasi delete
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus semua data populasi untuk kecamatan ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/populasi/delete.php?kecamatan_id=<?php echo $kecamatan_id; ?>&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Calculate initial totals
    calculateAllTotals();
});
</script>

<!-- CSS (sama dengan input.php) -->
<style>
.readonly-input {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.subtitle {
    color: #6c757d;
    margin-top: 5px;
    font-size: 1.1rem;
}

/* Sama dengan CSS dari input.php */
.tabs-navigation {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 12px 24px;
    background: #f8f9fa;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: #007bff;
    color: white;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.tab-contents {
    margin-top: 20px;
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
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.data-table.striped tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.data-table th {
    background-color: #e9ecef;
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
}

.data-table td {
    padding: 10px 8px;
    border: 1px solid #dee2e6;
    text-align: center;
}

.data-table .desa-name {
    text-align: left;
    font-weight: 500;
    background-color: #f8f9fa;
    min-width: 150px;
}

.data-table .total-col {
    background-color: #e9ecef;
    font-weight: 600;
}

.data-table .total-cell {
    font-weight: 500;
    color: #495057;
    min-width: 80px;
}

.data-table .grand-total {
    font-size: 1.1em;
    color: #dc3545;
}

.form-input.small-number {
    width: 80px;
    padding: 6px 8px;
    text-align: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-input.small-number:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.summary-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    transition: transform 0.2s;
}

.summary-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.summary-item.total-summary {
    grid-column: 1 / -1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.summary-content h4 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.summary-content p {
    margin: 5px 0 0 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.total-summary .summary-content p {
    color: rgba(255,255,255,0.9);
}

.form-description {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.submit-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #dee2e6;
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #dee2e6;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (max-width: 768px) {
    .tabs-navigation {
        flex-direction: column;
    }
    
    .tab-btn {
        width: 100%;
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-lg {
        width: 100%;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: 0.8rem;
    }
    
    .form-input.small-number {
        width: 60px;
    }
}
</style>