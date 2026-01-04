<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';
$dataGroup = [];

// Ambil parameter
$tanggal = $_GET['tanggal'] ?? '';
$kecamatan = $_GET['kecamatan'] ?? '';

if (!$tanggal || !$kecamatan) {
    $_SESSION['error_message'] = 'Parameter tidak lengkap';
    header('Location: ?module=pemotongan&action=data');
    exit();
}

// Ambil data kecamatan ID
$kecamatanId = getKecamatanId($kecamatan);

// Ambil data yang akan diedit
$sql = "SELECT p.*, k.nama_kecamatan 
        FROM pemotongan p 
        JOIN kecamatan k ON p.kecamatan_id = k.id 
        WHERE p.tanggal_pemotongan = ? AND p.kecamatan_id = ?
        ORDER BY p.jenis_hewan";
$dataGroup = fetchAll($sql, [$tanggal, $kecamatanId]);

if (empty($dataGroup)) {
    $error = "Data pemotongan tidak ditemukan";
}

// Ambil data kecamatan untuk dropdown
$sqlKecamatan = "SELECT id, nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan";
$kecamatanList = fetchAll($sqlKecamatan);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kecamatan_id = $_POST['kecamatan'] ?? '';
    $tanggal_pemotongan = $_POST['tanggal_pemotongan'] ?? '';
    
    // Collect animal slaughter data
    $data = [];
    $animalTypes = ['sapi', 'kerbau', 'ayam_pedaging', 'ayam_petelur', 'ayam_buras', 'itik', 'kambing', 'domba', 'babi'];
    
    foreach ($animalTypes as $type) {
        $jantan = $_POST[$type . '_jantan'] ?? 0;
        $betina = $_POST[$type . '_betina'] ?? 0;
        $total = $jantan + $betina;
        
        if ($total > 0) {
            $data[] = [
                'jenis_hewan' => $type,
                'jantan' => $jantan,
                'betina' => $betina,
                'total' => $total
            ];
        }
    }
    
    // Validasi
    if (empty($kecamatan_id)) {
        $error = "Kecamatan harus dipilih";
    } elseif (!validateDate($tanggal_pemotongan)) {
        $error = "Tanggal pemotongan tidak valid";
    } elseif (empty($data)) {
        $error = "Minimal satu jenis hewan harus memiliki data pemotongan";
    } else {
        try {
            // Begin transaction
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // Delete existing data
            $deleteSql = "DELETE FROM pemotongan 
                          WHERE tanggal_pemotongan = ? AND kecamatan_id = ?";
            executeQuery($deleteSql, [$tanggal, $kecamatanId]);
            
            // Insert updated data
            foreach ($data as $animalData) {
                $sql = "INSERT INTO pemotongan 
                        (kecamatan_id, jenis_hewan, jantan, betina, total, tanggal_pemotongan, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $params = [
                    $kecamatan_id,
                    $animalData['jenis_hewan'],
                    $animalData['jantan'],
                    $animalData['betina'],
                    $animalData['total'],
                    $tanggal_pemotongan,
                    $_SESSION['user_id']
                ];
                
                executeQuery($sql, $params);
            }
            
            $pdo->commit();
            
            // Get kecamatan name for success message
            $sqlKec = "SELECT nama_kecamatan FROM kecamatan WHERE id = ?";
            $kecamatan_new = fetchOne($sqlKec, [$kecamatan_id]);
            
            $success = "✅ Data pemotongan hewan berhasil diperbarui untuk kecamatan " . ($kecamatan_new['nama_kecamatan'] ?? '');
            
            // Update displayed data
            $kecamatanId = $kecamatan_id;
            $tanggal = $tanggal_pemotongan;
            
            // Reload data
            $sql = "SELECT p.*, k.nama_kecamatan 
                    FROM pemotongan p 
                    JOIN kecamatan k ON p.kecamatan_id = k.id 
                    WHERE p.tanggal_pemotongan = ? AND p.kecamatan_id = ?
                    ORDER BY p.jenis_hewan";
            $dataGroup = fetchAll($sql, [$tanggal, $kecamatanId]);
            
        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

// Convert data group to array for easier access
$animalData = [];
foreach ($dataGroup as $item) {
    $animalData[$item['jenis_hewan']] = [
        'jantan' => $item['jantan'],
        'betina' => $item['betina']
    ];
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-edit"></i> Edit Data Pemotongan</h2>
        <p>Tanggal: <strong><?php echo date('d F Y', strtotime($tanggal)); ?></strong> | 
           Kecamatan: <strong><?php echo htmlspecialchars($kecamatan); ?></strong></p>
    </div>

    <?php if ($error && empty($dataGroup)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
            <br><br>
            <a href="?module=pemotongan&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Pemotongan
            </a>
        </div>
    <?php else: ?>
    
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="" id="pemotonganForm">
                <div class="form-section">
                    <h3><i class="fas fa-map"></i> Wilayah dan Periode</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kecamatan"><i class="fas fa-landmark"></i> Kecamatan *</label>
                            <select id="kecamatan" name="kecamatan" required>
                                <option value="">Pilih Kecamatan</option>
                                <?php foreach ($kecamatanList as $kec): ?>
                                    <option value="<?php echo $kec['id']; ?>" 
                                        <?php echo ($kecamatanId == $kec['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kec['nama_kecamatan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_pemotongan"><i class="fas fa-calendar"></i> Tanggal Pemotongan *</label>
                            <input type="date" id="tanggal_pemotongan" name="tanggal_pemotongan" 
                                   value="<?php echo $tanggal; ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-paw"></i> Data Pemotongan Hewan</h3>
                    <p class="form-description">Masukkan jumlah hewan yang dipotong berdasarkan jenis dan jenis kelamin</p>
                    
                    <div class="pemotongan-grid">
                        <!-- Sapi -->
                        <div class="animal-card">
                            <h4><i class="fas fa-cow"></i> Sapi</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="sapi_jantan">Jantan</label>
                                    <input type="number" id="sapi_jantan" name="sapi_jantan" 
                                           value="<?php echo $animalData['sapi']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('sapi')">
                                </div>
                                <div class="input-group">
                                    <label for="sapi_betina">Betina</label>
                                    <input type="number" id="sapi_betina" name="sapi_betina" 
                                           value="<?php echo $animalData['sapi']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('sapi')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="sapi_total">
                                        <?php echo ($animalData['sapi']['jantan'] ?? 0) + ($animalData['sapi']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kerbau -->
                        <div class="animal-card">
                            <h4><i class="fas fa-bull"></i> Kerbau</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="kerbau_jantan">Jantan</label>
                                    <input type="number" id="kerbau_jantan" name="kerbau_jantan" 
                                           value="<?php echo $animalData['kerbau']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('kerbau')">
                                </div>
                                <div class="input-group">
                                    <label for="kerbau_betina">Betina</label>
                                    <input type="number" id="kerbau_betina" name="kerbau_betina" 
                                           value="<?php echo $animalData['kerbau']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('kerbau')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="kerbau_total">
                                        <?php echo ($animalData['kerbau']['jantan'] ?? 0) + ($animalData['kerbau']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ayam Pedaging -->
                        <div class="animal-card">
                            <h4><i class="fas fa-drumstick-bite"></i> Ayam Ras Pedaging</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="ayam_pedaging_jantan">Jantan</label>
                                    <input type="number" id="ayam_pedaging_jantan" name="ayam_pedaging_jantan" 
                                           value="<?php echo $animalData['ayam_pedaging']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('ayam_pedaging')">
                                </div>
                                <div class="input-group">
                                    <label for="ayam_pedaging_betina">Betina</label>
                                    <input type="number" id="ayam_pedaging_betina" name="ayam_pedaging_betina" 
                                           value="<?php echo $animalData['ayam_pedaging']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('ayam_pedaging')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="ayam_pedaging_total">
                                        <?php echo ($animalData['ayam_pedaging']['jantan'] ?? 0) + ($animalData['ayam_pedaging']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ayam Petelur -->
                        <div class="animal-card">
                            <h4><i class="fas fa-egg"></i> Ayam Ras Petelur</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="ayam_petelur_jantan">Jantan</label>
                                    <input type="number" id="ayam_petelur_jantan" name="ayam_petelur_jantan" 
                                           value="<?php echo $animalData['ayam_petelur']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('ayam_petelur')">
                                </div>
                                <div class="input-group">
                                    <label for="ayam_petelur_betina">Betina</label>
                                    <input type="number" id="ayam_petelur_betina" name="ayam_petelur_betina" 
                                           value="<?php echo $animalData['ayam_petelur']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('ayam_petelur')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="ayam_petelur_total">
                                        <?php echo ($animalData['ayam_petelur']['jantan'] ?? 0) + ($animalData['ayam_petelur']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ayam Buras -->
                        <div class="animal-card">
                            <h4><i class="fas fa-dove"></i> Ayam Buras</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="ayam_buras_jantan">Jantan</label>
                                    <input type="number" id="ayam_buras_jantan" name="ayam_buras_jantan" 
                                           value="<?php echo $animalData['ayam_buras']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('ayam_buras')">
                                </div>
                                <div class="input-group">
                                    <label for="ayam_buras_betina">Betina</label>
                                    <input type="number" id="ayam_buras_betina" name="ayam_buras_betina" 
                                           value="<?php echo $animalData['ayam_buras']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('ayam_buras')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="ayam_buras_total">
                                        <?php echo ($animalData['ayam_buras']['jantan'] ?? 0) + ($animalData['ayam_buras']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Itik -->
                        <div class="animal-card">
                            <h4><i class="fas fa-kiwi-bird"></i> Itik/Bebek</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="itik_jantan">Jantan</label>
                                    <input type="number" id="itik_jantan" name="itik_jantan" 
                                           value="<?php echo $animalData['itik']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('itik')">
                                </div>
                                <div class="input-group">
                                    <label for="itik_betina">Betina</label>
                                    <input type="number" id="itik_betina" name="itik_betina" 
                                           value="<?php echo $animalData['itik']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('itik')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="itik_total">
                                        <?php echo ($animalData['itik']['jantan'] ?? 0) + ($animalData['itik']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kambing -->
                        <div class="animal-card">
                            <h4><i class="fas fa-sheep"></i> Kambing</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="kambing_jantan">Jantan</label>
                                    <input type="number" id="kambing_jantan" name="kambing_jantan" 
                                           value="<?php echo $animalData['kambing']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('kambing')">
                                </div>
                                <div class="input-group">
                                    <label for="kambing_betina">Betina</label>
                                    <input type="number" id="kambing_betina" name="kambing_betina" 
                                           value="<?php echo $animalData['kambing']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('kambing')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="kambing_total">
                                        <?php echo ($animalData['kambing']['jantan'] ?? 0) + ($animalData['kambing']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Domba -->
                        <div class="animal-card">
                            <h4><i class="fas fa-sheep"></i> Domba</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="domba_jantan">Jantan</label>
                                    <input type="number" id="domba_jantan" name="domba_jantan" 
                                           value="<?php echo $animalData['domba']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('domba')">
                                </div>
                                <div class="input-group">
                                    <label for="domba_betina">Betina</label>
                                    <input type="number" id="domba_betina" name="domba_betina" 
                                           value="<?php echo $animalData['domba']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('domba')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="domba_total">
                                        <?php echo ($animalData['domba']['jantan'] ?? 0) + ($animalData['domba']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Babi -->
                        <div class="animal-card">
                            <h4><i class="fas fa-piggy-bank"></i> Babi</h4>
                            <div class="animal-inputs">
                                <div class="input-group">
                                    <label for="babi_jantan">Jantan</label>
                                    <input type="number" id="babi_jantan" name="babi_jantan" 
                                           value="<?php echo $animalData['babi']['jantan'] ?? 0; ?>"
                                           min="0" class="jantan-input" oninput="calculateTotal('babi')">
                                </div>
                                <div class="input-group">
                                    <label for="babi_betina">Betina</label>
                                    <input type="number" id="babi_betina" name="babi_betina" 
                                           value="<?php echo $animalData['babi']['betina'] ?? 0; ?>"
                                           min="0" class="betina-input" oninput="calculateTotal('babi')">
                                </div>
                                <div class="input-group total-display">
                                    <label>Total</label>
                                    <span id="babi_total">
                                        <?php echo ($animalData['babi']['jantan'] ?? 0) + ($animalData['babi']['betina'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="summary-card">
                        <h4><i class="fas fa-calculator"></i> Ringkasan Total</h4>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <span class="summary-label">Total Jantan:</span>
                                <span class="summary-value" id="total_jantan_all">
                                    <?php 
                                    $totalJantan = 0;
                                    foreach ($animalData as $data) {
                                        $totalJantan += $data['jantan'] ?? 0;
                                    }
                                    echo $totalJantan;
                                    ?>
                                </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Total Betina:</span>
                                <span class="summary-value" id="total_betina_all">
                                    <?php 
                                    $totalBetina = 0;
                                    foreach ($animalData as $data) {
                                        $totalBetina += $data['betina'] ?? 0;
                                    }
                                    echo $totalBetina;
                                    ?>
                                </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Grand Total:</span>
                                <span class="summary-value grand-total" id="grand_total_all">
                                    <?php echo $totalJantan + $totalBetina; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data Pemotongan
                    </button>
                    <a href="?module=pemotongan&action=data" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Hapus Data
                    </button>
                </div>
            </form>
        </div>
        
    <?php endif; ?>
</div>

<script>
const animalTypes = ['sapi', 'kerbau', 'ayam_pedaging', 'ayam_petelur', 'ayam_buras', 'itik', 'kambing', 'domba', 'babi'];

function calculateTotal(animalType) {
    const jantan = parseInt(document.getElementById(`${animalType}_jantan`).value) || 0;
    const betina = parseInt(document.getElementById(`${animalType}_betina`).value) || 0;
    const total = jantan + betina;
    
    document.getElementById(`${animalType}_total`).textContent = total;
    calculateAllTotals();
}

function calculateAllTotals() {
    let totalJantan = 0;
    let totalBetina = 0;
    let grandTotal = 0;
    
    animalTypes.forEach(type => {
        const jantan = parseInt(document.getElementById(`${type}_jantan`).value) || 0;
        const betina = parseInt(document.getElementById(`${type}_betina`).value) || 0;
        
        totalJantan += jantan;
        totalBetina += betina;
        grandTotal += jantan + betina;
    });
    
    document.getElementById('total_jantan_all').textContent = totalJantan.toLocaleString('id-ID');
    document.getElementById('total_betina_all').textContent = totalBetina.toLocaleString('id-ID');
    document.getElementById('grand_total_all').textContent = grandTotal.toLocaleString('id-ID');
}

function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus semua data pemotongan ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/pemotongan/delete.php?tanggal=<?php echo $tanggal; ?>&kecamatan=<?php echo urlencode($kecamatan); ?>';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Calculate initial totals
    animalTypes.forEach(type => {
        calculateTotal(type);
    });
});
</script>
<style>
.pemotongan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.animal-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}

.animal-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.animal-card h4 {
    margin-bottom: 15px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 10px;
}

.animal-inputs {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.animal-inputs .input-group {
    flex: 1;
}

.animal-inputs label {
    display: block;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
}

.animal-inputs input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.animal-inputs .total-display {
    text-align: center;
    background: white;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #28a745;
}

.animal-inputs .total-display label {
    color: #28a745;
    font-weight: bold;
}

.animal-inputs .total-display span {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

.summary-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    border: 2px solid #007bff;
}

.summary-card h4 {
    color: #007bff;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.summary-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.summary-label {
    display: block;
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 5px;
}

.summary-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
}

.summary-value.grand-total {
    color: #28a745;
    font-size: 28px;
}

@media (max-width: 768px) {
    .pemotongan-grid {
        grid-template-columns: 1fr;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>