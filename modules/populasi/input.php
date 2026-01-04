<?php
// modules/populasi/input.php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

// Data desa per kecamatan (9 kecamatan di Buleleng)
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

// Helper functions


function getDesaId($nama, $kecamatanId) {
    $sql = "SELECT id FROM desa WHERE nama_desa = ? AND kecamatan_id = ?";
    $result = fetchOne($sql, [$nama, $kecamatanId]);
    return $result['id'] ?? 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kecamatan = $_POST['kecamatan'] ?? '';
    $bulan = $_POST['bulan'] ?? date('m');
    $tahun = $_POST['tahun'] ?? date('Y');
    
    // Validasi
    if (empty($kecamatan)) {
        $error = "Kecamatan harus dipilih";
    } elseif (!array_key_exists($kecamatan, $desaData)) {
        $error = "Kecamatan tidak valid";
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            $totalDesaDiproses = 0;
            $errorsDesa = [];
            
            // Loop melalui setiap desa di kecamatan
            foreach ($desaData[$kecamatan] as $desa) {
                $kecamatanId = getKecamatanId($kecamatan);
                $desaId = getDesaId($desa, $kecamatanId);
                
                if ($desaId == 0) {
                    $errorsDesa[] = "Desa $desa tidak ditemukan dalam database";
                    continue;
                }
                
                // Prepare data untuk desa ini
                $data = [
                    'kecamatan_id' => $kecamatanId,
                    'desa_id' => $desaId,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    
                    // ===== SAPA BALI =====
                    'sapi_bali_jantan' => intval($_POST[$desa . '_sapi_bali_jantan'] ?? 0),
                    'sapi_bali_betina' => intval($_POST[$desa . '_sapi_bali_betina'] ?? 0),
                    
                    // ===== KERBAU =====
                    'kerbau_jantan' => intval($_POST[$desa . '_kerbau_jantan'] ?? 0),
                    'kerbau_betina' => intval($_POST[$desa . '_kerbau_betina'] ?? 0),
                    
                    // ===== KUDA =====
                    'kuda_jantan' => intval($_POST[$desa . '_kuda_jantan'] ?? 0),
                    'kuda_betina' => intval($_POST[$desa . '_kuda_betina'] ?? 0),
                    
                    // ===== BABI BALI =====
                    'babi_bali_induk' => intval($_POST[$desa . '_babi_bali_induk'] ?? 0),
                    'babi_bali_betina' => intval($_POST[$desa . '_babi_bali_betina'] ?? 0),
                    'babi_bali_jantan' => intval($_POST[$desa . '_babi_bali_jantan'] ?? 0),
                    
                    // ===== BABI LANDRACE =====
                    'babi_landrace_induk' => intval($_POST[$desa . '_babi_landrace_induk'] ?? 0),
                    'babi_landrace_betina' => intval($_POST[$desa . '_babi_landrace_betina'] ?? 0),
                    'babi_landrace_jantan' => intval($_POST[$desa . '_babi_landrace_jantan'] ?? 0),
                    
                    // ===== KAMBING POTONG =====
                    'kambing_potong_jantan' => intval($_POST[$desa . '_kambing_potong_jantan'] ?? 0),
                    'kambing_potong_betina' => intval($_POST[$desa . '_kambing_potong_betina'] ?? 0),
                    
                    // ===== KAMBING PERAH =====
                    'kambing_perah_jantan' => intval($_POST[$desa . '_kambing_perah_jantan'] ?? 0),
                    'kambing_perah_betina' => intval($_POST[$desa . '_kambing_perah_betina'] ?? 0),
                    
                    // ===== UNGGAS - AYAM =====
                    'ayam_buras' => intval($_POST[$desa . '_ayam_buras'] ?? 0),
                    'ayam_petelur' => intval($_POST[$desa . '_ayam_petelur'] ?? 0),
                    'ayam_pedaging' => intval($_POST[$desa . '_ayam_pedaging'] ?? 0),
                    
                    // ===== UNGGAS - BEBEK =====
                    'bebek_itik' => intval($_POST[$desa . '_bebek_itik'] ?? 0),
                    'bebek_manila' => intval($_POST[$desa . '_bebek_manila'] ?? 0),
                    
                    // ===== ANJING =====
                    'anjing_total' => intval($_POST[$desa . '_anjing_total'] ?? 0),
                    
                    'created_by' => $_SESSION['user_id']
                ];
                
                // Check if data exists for this desa, bulan, tahun
                $checkSql = "SELECT id FROM populasi_ternak 
                            WHERE kecamatan_id = ? AND desa_id = ? AND bulan = ? AND tahun = ?";
                $existing = fetchOne($checkSql, [
                    $data['kecamatan_id'], 
                    $data['desa_id'], 
                    $bulan, 
                    $tahun
                ]);
                
            if ($existing) {
                // Update existing data
                $sql = "UPDATE populasi_ternak SET 
                        sapi_bali_jantan = ?, sapi_bali_betina = ?,
                        kerbau_jantan = ?, kerbau_betina = ?,
                        kuda_jantan = ?, kuda_betina = ?,
                        babi_bali_induk = ?, babi_bali_betina = ?, babi_bali_jantan = ?,
                        babi_landrace_induk = ?, babi_landrace_betina = ?, babi_landrace_jantan = ?,
                        kambing_potong_jantan = ?, kambing_potong_betina = ?,
                        kambing_perah_jantan = ?, kambing_perah_betina = ?,
                        ayam_buras = ?, ayam_petelur = ?, ayam_pedaging = ?,
                        bebek_itik = ?, bebek_manila = ?,
                        anjing_total = ?,
                        updated_at = NOW()
                        WHERE id = ?";
                
                $params = [
                    $data['sapi_bali_jantan'], $data['sapi_bali_betina'],
                    $data['kerbau_jantan'], $data['kerbau_betina'],
                    $data['kuda_jantan'], $data['kuda_betina'],
                    $data['babi_bali_induk'], $data['babi_bali_betina'], $data['babi_bali_jantan'],
                    $data['babi_landrace_induk'], $data['babi_landrace_betina'], $data['babi_landrace_jantan'],
                    $data['kambing_potong_jantan'], $data['kambing_potong_betina'],
                    $data['kambing_perah_jantan'], $data['kambing_perah_betina'],
                    $data['ayam_buras'], $data['ayam_petelur'], $data['ayam_pedaging'],
                    $data['bebek_itik'], $data['bebek_manila'],
                    $data['anjing_total'],
                    $existing['id']
                ];
            } else {
                // Insert new data
                $sql = "INSERT INTO populasi_ternak 
                        (kecamatan_id, desa_id, bulan, tahun,
                        sapi_bali_jantan, sapi_bali_betina,
                        kerbau_jantan, kerbau_betina,
                        kuda_jantan, kuda_betina,
                        babi_bali_induk, babi_bali_betina, babi_bali_jantan,
                        babi_landrace_induk, babi_landrace_betina, babi_landrace_jantan,
                        kambing_potong_jantan, kambing_potong_betina,
                        kambing_perah_jantan, kambing_perah_betina,
                        ayam_buras, ayam_petelur, ayam_pedaging,
                        bebek_itik, bebek_manila,
                        anjing_total, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $params = [
                    $data['kecamatan_id'], $data['desa_id'], $data['bulan'], $data['tahun'],
                    $data['sapi_bali_jantan'], $data['sapi_bali_betina'],
                    $data['kerbau_jantan'], $data['kerbau_betina'],
                    $data['kuda_jantan'], $data['kuda_betina'],
                    $data['babi_bali_induk'], $data['babi_bali_betina'], $data['babi_bali_jantan'],
                    $data['babi_landrace_induk'], $data['babi_landrace_betina'], $data['babi_landrace_jantan'],
                    $data['kambing_potong_jantan'], $data['kambing_potong_betina'],
                    $data['kambing_perah_jantan'], $data['kambing_perah_betina'],
                    $data['ayam_buras'], $data['ayam_petelur'], $data['ayam_pedaging'],
                    $data['bebek_itik'], $data['bebek_manila'],
                    $data['anjing_total'],
                    $data['created_by']
                ];
            }

            executeQuery($sql, $params);
                $totalDesaDiproses++;
            }
            
            $pdo->commit();
            
            if (!empty($errorsDesa)) {
                $success = "✅ Data berhasil disimpan untuk $totalDesaDiproses desa, namun ada masalah: <br>" . 
                          implode("<br>", $errorsDesa);
            } else {
                $success = "✅ Data populasi berhasil disimpan untuk kecamatan $kecamatan ($totalDesaDiproses desa)";
            }
            
        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-users"></i> Sistem Populasi Ternak - Input Data</h2>
        <p>Input data populasi hewan ternak per desa di Kabupaten Buleleng</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <?php echo $success; ?>
            <br>
            <a href="?module=populasi&action=data" class="btn btn-sm btn-primary mt-2">
                <i class="fas fa-list"></i> Lihat Data Populasi
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
            <!-- Form Header untuk pilih kecamatan dan periode -->
            <div class="form-section">
                <h3><i class="fas fa-map"></i> Wilayah dan Periode</h3>
                <p class="form-description">Pilih kecamatan dan periode untuk input data populasi</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kecamatan">
                            <i class="fas fa-landmark"></i> Kecamatan *
                        </label>
                        <select id="kecamatan" name="kecamatan" required onchange="loadDesaData()">
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach (array_keys($desaData) as $kec): ?>
                                <option value="<?php echo $kec; ?>" 
                                    <?php echo ($_POST['kecamatan'] ?? '') == $kec ? 'selected' : ''; ?>>
                                    <?php echo $kec; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bulan">
                            <i class="fas fa-calendar"></i> Bulan *
                        </label>
                        <select id="bulan" name="bulan" required>
                            <option value="">-- Pilih Bulan --</option>
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
                                    <?php echo ($_POST['bulan'] ?? date('m')) == $monthNum ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tahun">
                            <i class="fas fa-calendar-alt"></i> Tahun *
                        </label>
                        <input type="number" id="tahun" name="tahun" 
                               value="<?php echo $_POST['tahun'] ?? date('Y'); ?>"
                               min="2020" max="2030" required
                               class="form-control">
                    </div>
                </div>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <span>Data akan disimpan untuk semua desa di kecamatan yang dipilih</span>
                </div>
            </div>

            <!-- Container untuk data desa (akan diisi oleh JavaScript) -->
            <div id="desaDataContainer" class="desa-container">
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>Pilih Kecamatan Terlebih Dahulu</h4>
                    <p>Silakan pilih kecamatan untuk menampilkan form input data</p>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="form-section submit-section">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan Semua Data
                    </button>
                    <button type="reset" class="btn btn-secondary btn-lg">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                    <button type="button" class="btn btn-info btn-lg" onclick="calculateAllTotals()">
                        <i class="fas fa-calculator"></i> Hitung Total
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.location.href='?module=populasi&action=data'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                
                <div class="form-note">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Tips:</strong> Isi data untuk setiap desa. Total akan dihitung otomatis.
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript untuk form interaktif -->
<script>
// Data desa per kecamatan
const desaData = <?php echo json_encode($desaData); ?>;

// Tab management
let currentTab = 'sapi';

// Load data desa ketika kecamatan dipilih
function loadDesaData() {
    const kecamatan = document.getElementById('kecamatan').value;
    const container = document.getElementById('desaDataContainer');
    
    if (!kecamatan || !desaData[kecamatan]) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <h4>Pilih Kecamatan Terlebih Dahulu</h4>
                <p>Silakan pilih kecamatan untuk menampilkan form input data</p>
            </div>`;
        return;
    }
    
    // Show loading
    container.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Menyiapkan form untuk kecamatan ${kecamatan}...</p>
        </div>`;
    
    // Generate form after a short delay
    setTimeout(() => {
        generateDesaForm(kecamatan);
    }, 300);
}

// Generate form untuk semua desa
function generateDesaForm(kecamatan) {
    const container = document.getElementById('desaDataContainer');
    
    let html = `
        <div class="form-section">
            <h3><i class="fas fa-list-ol"></i> Data Populasi per Desa</h3>
            <p class="form-description">Isi data populasi untuk setiap desa di Kecamatan ${kecamatan}</p>
            
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
                    ${generateSapiTable(kecamatan)}
                </div>
                
                <!-- Tab 2: Babi -->
                <div class="tab-content" id="babiTab">
                    ${generateBabiTable(kecamatan)}
                </div>
                
                <!-- Tab 3: Kambing -->
                <div class="tab-content" id="kambingTab">
                    ${generateKambingTable(kecamatan)}
                </div>
                
                <!-- Tab 4: Unggas & Anjing -->
                <div class="tab-content" id="unggasTab">
                    ${generateUnggasTable(kecamatan)}
                </div>
            </div>
            
            <!-- Summary Section -->
            <div class="summary-section" id="summarySection">
                <h4><i class="fas fa-chart-bar"></i> Ringkasan Total</h4>
                <div class="summary-grid" id="summaryGrid">
                    <!-- Summary akan diisi oleh JavaScript -->
                </div>
            </div>
        </div>`;
    
    container.innerHTML = html;
    currentTab = 'sapi';
    calculateAllTotals();
}

// ========== GENERATE TABLES ==========

// Table untuk Sapi, Kerbau, Kuda
function generateSapiTable(kecamatan) {
    let html = `
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
                <tbody>`;
    
    desaData[kecamatan].forEach((desa) => {
        const desaId = desa.replace(/\s+/g, '_').toLowerCase();
        html += `
            <tr data-desa="${desaId}">
                <td class="desa-name">
                    <strong>${desa}</strong>
                </td>
                
                <!-- Sapi Bali -->
                <td>
                    <input type="number" 
                           name="${desa}_sapi_bali_jantan" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateRowTotal('${desaId}', 'sapi')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_sapi_bali_betina" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateRowTotal('${desaId}', 'sapi')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_sapi_${desaId}">0</span>
                </td>
                
                <!-- Kerbau -->
                <td>
                    <input type="number" 
                           name="${desa}_kerbau_jantan" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateRowTotal('${desaId}', 'kerbau')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_kerbau_betina" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateRowTotal('${desaId}', 'kerbau')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_kerbau_${desaId}">0</span>
                </td>
                
                <!-- Kuda -->
                <td>
                    <input type="number" 
                           name="${desa}_kuda_jantan" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateRowTotal('${desaId}', 'kuda')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_kuda_betina" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateRowTotal('${desaId}', 'kuda')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_kuda_${desaId}">0</span>
                </td>
                
                <!-- Total Desa -->
                <td class="total-cell grand-total">
                    <strong><span id="total_desa_sapi_${desaId}">0</span></strong>
                </td>
            </tr>`;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="footer-total">
                        <td><strong>TOTAL KECAMATAN</strong></td>
                        <td><span id="grand_jantan_sapi">0</span></td>
                        <td><span id="grand_betina_sapi">0</span></td>
                        <td><strong><span id="grand_total_sapi">0</span></strong></td>
                        <td><span id="grand_jantan_kerbau">0</span></td>
                        <td><span id="grand_betina_kerbau">0</span></td>
                        <td><strong><span id="grand_total_kerbau">0</span></strong></td>
                        <td><span id="grand_jantan_kuda">0</span></td>
                        <td><span id="grand_betina_kuda">0</span></td>
                        <td><strong><span id="grand_total_kuda">0</span></strong></td>
                        <td><strong class="grand-total"><span id="grand_total_sapi_section">0</span></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    
    return html;
}

// Table untuk Babi
function generateBabiTable(kecamatan) {
    let html = `
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
                <tbody>`;
    
    desaData[kecamatan].forEach((desa) => {
        const desaId = desa.replace(/\s+/g, '_').toLowerCase();
        html += `
            <tr data-desa="${desaId}">
                <td class="desa-name">
                    <strong>${desa}</strong>
                </td>
                
                <!-- Babi Bali -->
                <td>
                    <input type="number" 
                           name="${desa}_babi_bali_induk" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateBabiTotal('${desaId}', 'bali')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_babi_bali_betina" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateBabiTotal('${desaId}', 'bali')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_babi_bali_jantan" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateBabiTotal('${desaId}', 'bali')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_babi_bali_${desaId}">0</span>
                </td>
                
                <!-- Babi Landrace -->
                <td>
                    <input type="number" 
                           name="${desa}_babi_landrace_induk" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateBabiTotal('${desaId}', 'landrace')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_babi_landrace_betina" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateBabiTotal('${desaId}', 'landrace')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_babi_landrace_jantan" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateBabiTotal('${desaId}', 'landrace')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_babi_landrace_${desaId}">0</span>
                </td>
                
                <!-- Total Babi -->
                <td class="total-cell grand-total">
                    <strong><span id="total_desa_babi_${desaId}">0</span></strong>
                </td>
            </tr>`;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="footer-total">
                        <td><strong>TOTAL KECAMATAN</strong></td>
                        <td><span id="grand_induk_bali">0</span></td>
                        <td><span id="grand_betina_bali">0</span></td>
                        <td><span id="grand_jantan_bali">0</span></td>
                        <td><strong><span id="grand_total_bali">0</span></strong></td>
                        <td><span id="grand_induk_landrace">0</span></td>
                        <td><span id="grand_betina_landrace">0</span></td>
                        <td><span id="grand_jantan_landrace">0</span></td>
                        <td><strong><span id="grand_total_landrace">0</span></strong></td>
                        <td><strong class="grand-total"><span id="grand_total_babi_section">0</span></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    
    return html;
}

// Table untuk Kambing
function generateKambingTable(kecamatan) {
    let html = `
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
                <tbody>`;
    
    desaData[kecamatan].forEach((desa) => {
        const desaId = desa.replace(/\s+/g, '_').toLowerCase();
        html += `
            <tr data-desa="${desaId}">
                <td class="desa-name">
                    <strong>${desa}</strong>
                </td>
                
                <!-- Kambing Potong -->
                <td>
                    <input type="number" 
                           name="${desa}_kambing_potong_jantan" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateKambingTotal('${desaId}', 'potong')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_kambing_potong_betina" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateKambingTotal('${desaId}', 'potong')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_kambing_potong_${desaId}">0</span>
                </td>
                
                <!-- Kambing Perah -->
                <td>
                    <input type="number" 
                           name="${desa}_kambing_perah_jantan" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateKambingTotal('${desaId}', 'perah')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_kambing_perah_betina" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateKambingTotal('${desaId}', 'perah')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_kambing_perah_${desaId}">0</span>
                </td>
                
                <!-- Total Kambing -->
                <td class="total-cell grand-total">
                    <strong><span id="total_desa_kambing_${desaId}">0</span></strong>
                </td>
            </tr>`;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="footer-total">
                        <td><strong>TOTAL KECAMATAN</strong></td>
                        <td><span id="grand_jantan_potong">0</span></td>
                        <td><span id="grand_betina_potong">0</span></td>
                        <td><strong><span id="grand_total_potong">0</span></strong></td>
                        <td><span id="grand_jantan_perah">0</span></td>
                        <td><span id="grand_betina_perah">0</span></td>
                        <td><strong><span id="grand_total_perah">0</span></strong></td>
                        <td><strong class="grand-total"><span id="grand_total_kambing_section">0</span></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    
    return html;
}

// Table untuk Unggas & Anjing
function generateUnggasTable(kecamatan) {
    let html = `
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
                <tbody>`;
    
    desaData[kecamatan].forEach((desa) => {
        const desaId = desa.replace(/\s+/g, '_').toLowerCase();
        html += `
            <tr data-desa="${desaId}">
                <td class="desa-name">
                    <strong>${desa}</strong>
                </td>
                
                <!-- Ayam -->
                <td>
                    <input type="number" 
                           name="${desa}_ayam_buras" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateAyamTotal('${desaId}')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_ayam_petelur" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateAyamTotal('${desaId}')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_ayam_pedaging" 
                           class="form-input small-number" 
                           min="0" value="0" 
                           oninput="calculateAyamTotal('${desaId}')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_ayam_${desaId}">0</span>
                </td>
                
                <!-- Bebek -->
                <td>
                    <input type="number" 
                           name="${desa}_bebek_itik" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateBebekTotal('${desaId}')"
                           placeholder="0">
                </td>
                <td>
                    <input type="number" 
                           name="${desa}_bebek_manila" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateBebekTotal('${desaId}')"
                           placeholder="0">
                </td>
                <td class="total-cell">
                    <span class="total-value" id="total_bebek_${desaId}">0</span>
                </td>
                
                <!-- Anjing -->
                <td>
                    <input type="number" 
                           name="${desa}_anjing_total" 
                           class="form-input small-number" 
                           min="0" value="0"
                           oninput="calculateUnggasTotal('${desaId}')"
                           placeholder="0">
                </td>
                
                <!-- Total Unggas -->
                <td class="total-cell grand-total">
                    <strong><span id="total_desa_unggas_${desaId}">0</span></strong>
                </td>
            </tr>`;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="footer-total">
                        <td><strong>TOTAL KECAMATAN</strong></td>
                        <td><span id="grand_ayam_buras">0</span></td>
                        <td><span id="grand_ayam_petelur">0</span></td>
                        <td><span id="grand_ayam_pedaging">0</span></td>
                        <td><strong><span id="grand_total_ayam">0</span></strong></td>
                        <td><span id="grand_bebek_itik">0</span></td>
                        <td><span id="grand_bebek_manila">0</span></td>
                        <td><strong><span id="grand_total_bebek">0</span></strong></td>
                        <td><strong><span id="grand_total_anjing">0</span></strong></td>
                        <td><strong class="grand-total"><span id="grand_total_unggas_section">0</span></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    
    return html;
}

// ========== CALCULATION FUNCTIONS ==========

// Hitung total per jenis di setiap row
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
    
    // Update total babi per desa
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
    
    // Update total kambing per desa
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
    const kecamatan = document.getElementById('kecamatan').value;
    if (!kecamatan) return;
    
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
    desaData[kecamatan].forEach((desa) => {
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
    
    // Update UI untuk Sapi Section
    if (document.getElementById('grand_jantan_sapi')) {
        document.getElementById('grand_jantan_sapi').textContent = grandTotals.sapi_jantan;
        document.getElementById('grand_betina_sapi').textContent = grandTotals.sapi_betina;
        document.getElementById('grand_total_sapi').textContent = grandTotals.sapi_total;
        document.getElementById('grand_jantan_kerbau').textContent = grandTotals.kerbau_jantan;
        document.getElementById('grand_betina_kerbau').textContent = grandTotals.kerbau_betina;
        document.getElementById('grand_total_kerbau').textContent = grandTotals.kerbau_total;
        document.getElementById('grand_jantan_kuda').textContent = grandTotals.kuda_jantan;
        document.getElementById('grand_betina_kuda').textContent = grandTotals.kuda_betina;
        document.getElementById('grand_total_kuda').textContent = grandTotals.kuda_total;
        document.getElementById('grand_total_sapi_section').textContent = grandTotals.sapi_section_total;
    }
    
    // Update UI untuk Babi Section
    if (document.getElementById('grand_induk_bali')) {
        document.getElementById('grand_induk_bali').textContent = grandTotals.babi_bali_induk;
        document.getElementById('grand_betina_bali').textContent = grandTotals.babi_bali_betina;
        document.getElementById('grand_jantan_bali').textContent = grandTotals.babi_bali_jantan;
        document.getElementById('grand_total_bali').textContent = grandTotals.babi_bali_total;
        document.getElementById('grand_induk_landrace').textContent = grandTotals.babi_landrace_induk;
        document.getElementById('grand_betina_landrace').textContent = grandTotals.babi_landrace_betina;
        document.getElementById('grand_jantan_landrace').textContent = grandTotals.babi_landrace_jantan;
        document.getElementById('grand_total_landrace').textContent = grandTotals.babi_landrace_total;
        document.getElementById('grand_total_babi_section').textContent = grandTotals.babi_section_total;
    }
    
    // Update UI untuk Kambing Section
    if (document.getElementById('grand_jantan_potong')) {
        document.getElementById('grand_jantan_potong').textContent = grandTotals.kambing_potong_jantan;
        document.getElementById('grand_betina_potong').textContent = grandTotals.kambing_potong_betina;
        document.getElementById('grand_total_potong').textContent = grandTotals.kambing_potong_total;
        document.getElementById('grand_jantan_perah').textContent = grandTotals.kambing_perah_jantan;
        document.getElementById('grand_betina_perah').textContent = grandTotals.kambing_perah_betina;
        document.getElementById('grand_total_perah').textContent = grandTotals.kambing_perah_total;
        document.getElementById('grand_total_kambing_section').textContent = grandTotals.kambing_section_total;
    }
    
    // Update UI untuk Unggas Section
    if (document.getElementById('grand_ayam_buras')) {
        document.getElementById('grand_ayam_buras').textContent = grandTotals.ayam_buras;
        document.getElementById('grand_ayam_petelur').textContent = grandTotals.ayam_petelur;
        document.getElementById('grand_ayam_pedaging').textContent = grandTotals.ayam_pedaging;
        document.getElementById('grand_total_ayam').textContent = grandTotals.ayam_total;
        document.getElementById('grand_bebek_itik').textContent = grandTotals.bebek_itik;
        document.getElementById('grand_bebek_manila').textContent = grandTotals.bebek_manila;
        document.getElementById('grand_total_bebek').textContent = grandTotals.bebek_total;
        document.getElementById('grand_total_anjing').textContent = grandTotals.anjing_total;
        document.getElementById('grand_total_unggas_section').textContent = grandTotals.unggas_section_total;
    }
    
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

// Format input numbers
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format number inputs
    document.addEventListener('blur', function(e) {
        if (e.target.type === 'number' && e.target.value) {
            const value = parseInt(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value;
            }
        }
    }, true);
    
    // Set current month as default
    const currentMonth = new Date().getMonth() + 1;
    const bulanSelect = document.getElementById('bulan');
    if (bulanSelect && !bulanSelect.value) {
        bulanSelect.value = currentMonth.toString().padStart(2, '0');
    }
    
    // Load desa data if kecamatan is already selected
    const kecamatan = document.getElementById('kecamatan');
    if (kecamatan && kecamatan.value) {
        loadDesaData();
    }
});
</script>

<!-- CSS khusus untuk form populasi -->
<style>
/* Container styling */
.desa-container {
    margin-top: 20px;
    min-height: 200px;
}

.empty-state, .loading-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i, .loading-state i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #dee2e6;
}

.loading-state i.fa-spinner {
    color: #007bff;
}

/* Tabs Navigation */
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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Table Styling */
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

/* Form Input Styling */
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

/* Footer Row */
.footer-total {
    background-color: #f8f9fa !important;
    font-weight: 600;
}

.footer-total td {
    padding: 12px 8px;
    background-color: #e9ecef;
}

/* Summary Section */
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

/* Info Box */
.info-box {
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    padding: 12px 15px;
    margin-top: 15px;
    border-radius: 4px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.info-box i {
    color: #007bff;
    margin-top: 2px;
}

.info-box span {
    color: #0062cc;
    font-size: 0.9rem;
}

/* Form Description */
.form-description {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

/* Submit Section */
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

.form-note {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 4px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Responsive Design */
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