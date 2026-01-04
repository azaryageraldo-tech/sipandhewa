<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_peternak = sanitizeInput($_POST['nama_peternak']);
    $jenis_peternakan = $_POST['jenis_peternakan'];
    $jenis_pakan = sanitizeInput($_POST['jenis_pakan']);
    $tanggal_produksi = $_POST['tanggal_produksi'];
    
    // Production data
    $produksi_susu = $_POST['produksi_susu'] ?? 0;
    $produksi_daging = $_POST['produksi_daging'] ?? 0;
    $produksi_telur = $_POST['produksi_telur'] ?? 0;
    
    // Cost and price data
    $biaya_produksi = $_POST['biaya_produksi'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $keuntungan = $harga_jual - $biaya_produksi;
    
    // Validate
    if (empty($nama_peternak)) {
        $error = "Nama peternak harus diisi";
    } elseif (!validateDate($tanggal_produksi)) {
        $error = "Tanggal produksi tidak valid";
    } elseif ($biaya_produksi < 0 || $harga_jual < 0) {
        $error = "Biaya dan harga tidak boleh negatif";
    } else {
        try {
            // Insert data
            $sql = "INSERT INTO produksi 
                    (nama_peternak, jenis_peternakan, jenis_pakan, 
                     produksi_susu, produksi_daging, produksi_telur,
                     biaya_produksi, harga_jual, keuntungan, tanggal_produksi, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $nama_peternak,
                $jenis_peternakan,
                $jenis_pakan,
                $produksi_susu,
                $produksi_daging,
                $produksi_telur,
                $biaya_produksi,
                $harga_jual,
                $keuntungan,
                $tanggal_produksi,
                $_SESSION['user_id']
            ];
            
            $id = insertData($sql, $params);
            $success = "✅ Data produksi berhasil disimpan! ID: " . $id;
            
            // Reset form
            $_POST = [];
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

// Get production types based on livestock type
function getProductionFields($jenis) {
    switch ($jenis) {
        case 'sapi_perah':
            return ['susu' => true, 'daging' => false, 'telur' => false];
        case 'sapi_potong':
            return ['susu' => false, 'daging' => true, 'telur' => false];
        case 'ayam_petelur':
            return ['susu' => false, 'daging' => false, 'telur' => true];
        case 'ayam_pedaging':
            return ['susu' => false, 'daging' => true, 'telur' => false];
        case 'kambing':
            return ['susu' => true, 'daging' => true, 'telur' => false];
        default:
            return ['susu' => false, 'daging' => true, 'telur' => false];
    }
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-industry"></i> Sistem Produksi Peternakan</h2>
        <p>Input data produksi harian dan analisis keuntungan</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="" id="produksiForm">
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Informasi Peternak</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_peternak"><i class="fas fa-user-tag"></i> Nama Peternak *</label>
                        <input type="text" id="nama_peternak" name="nama_peternak" 
                               value="<?php echo $_POST['nama_peternak'] ?? ''; ?>"
                               placeholder="Nama peternak atau unit usaha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_peternakan"><i class="fas fa-paw"></i> Jenis Peternakan *</label>
                        <select id="jenis_peternakan" name="jenis_peternakan" required onchange="updateProductionFields()">
                            <option value="">Pilih Jenis Peternakan</option>
                            <option value="sapi_perah" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'sapi_perah' ? 'selected' : ''; ?>>Sapi Perah</option>
                            <option value="sapi_potong" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'sapi_potong' ? 'selected' : ''; ?>>Sapi Potong</option>
                            <option value="ayam_petelur" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'ayam_petelur' ? 'selected' : ''; ?>>Ayam Petelur (Layer)</option>
                            <option value="ayam_pedaging" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'ayam_pedaging' ? 'selected' : ''; ?>>Ayam Pedaging (Broiler)</option>
                            <option value="kambing" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'kambing' ? 'selected' : ''; ?>>Kambing/Domba</option>
                            <option value="babi" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'babi' ? 'selected' : ''; ?>>Babi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_pakan"><i class="fas fa-seedling"></i> Jenis Pakan</label>
                        <input type="text" id="jenis_pakan" name="jenis_pakan" 
                               value="<?php echo $_POST['jenis_pakan'] ?? ''; ?>"
                               placeholder="Jenis pakan yang digunakan">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal_produksi"><i class="fas fa-calendar"></i> Tanggal Produksi *</label>
                        <input type="date" id="tanggal_produksi" name="tanggal_produksi" 
                               value="<?php echo $_POST['tanggal_produksi'] ?? date('Y-m-d'); ?>"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-boxes"></i> Hasil Produksi</h3>
                
                <div class="form-row">
                    <div class="form-group" id="susuField">
                        <label for="produksi_susu"><i class="fas fa-wine-bottle"></i> Produksi Susu (Liter)</label>
                        <input type="number" id="produksi_susu" name="produksi_susu" 
                               value="<?php echo $_POST['produksi_susu'] ?? 0; ?>"
                               min="0" placeholder="0.0">
                    </div>
                    
                    <div class="form-group" id="dagingField">
                        <label for="produksi_daging"><i class="fas fa-drumstick-bite"></i> Produksi Daging (kg)</label>
                        <input type="number" id="produksi_daging" name="produksi_daging" 
                               value="<?php echo $_POST['produksi_daging'] ?? 0; ?>"
                               min="0" placeholder="0.0">
                    </div>
                    
                    <div class="form-group" id="telurField">
                        <label for="produksi_telur"><i class="fas fa-egg"></i> Produksi Telur (butir)</label>
                        <input type="number" id="produksi_telur" name="produksi_telur" 
                               value="<?php echo $_POST['produksi_telur'] ?? 0; ?>"
                               min="0" placeholder="0">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-calculator"></i> Analisis Keuangan</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="biaya_produksi"><i class="fas fa-money-bill-wave"></i> Biaya Produksi (Rp) *</label>
                        <input type="number" id="biaya_produksi" name="biaya_produksi" 
                               value="<?php echo $_POST['biaya_produksi'] ?? 0; ?>"
                               min="0"  placeholder="0" required
                               oninput="calculateProfit()">
                        <small class="form-text">Total biaya produksi</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_jual"><i class="fas fa-tag"></i> Harga Jual (Rp) *</label>
                        <input type="number" id="harga_jual" name="harga_jual" 
                               value="<?php echo $_POST['harga_jual'] ?? 0; ?>"
                               min="0"  placeholder="0" required
                               oninput="calculateProfit()">
                        <small class="form-text">Total pendapatan dari penjualan</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="keuntungan_display"><i class="fas fa-chart-line"></i> Keuntungan (Rp)</label>
                        <input type="text" id="keuntungan_display" readonly
                               class="form-control" style="background-color: #f8f9fa;">
                        <input type="hidden" id="keuntungan" name="keuntungan">
                    </div>
                </div>
                
                <div class="profit-indicator" id="profitIndicator">
                    <div class="profit-label">
                        <i class="fas fa-info-circle"></i>
                        <span>Status Keuntungan:</span>
                    </div>
                    <div class="profit-value" id="profitValue">
                        Menunggu input data
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Data Produksi
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset Form
                </button>
                <button type="button" class="btn btn-info" onclick="calculateProfit()">
                    <i class="fas fa-calculator"></i> Hitung Keuntungan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateProductionFields() {
    const jenis = document.getElementById('jenis_peternakan').value;
    const susuField = document.getElementById('susuField');
    const dagingField = document.getElementById('dagingField');
    const telurField = document.getElementById('telurField');
    
    // Reset all fields
    susuField.style.display = 'block';
    dagingField.style.display = 'block';
    telurField.style.display = 'block';
    
    switch (jenis) {
        case 'sapi_perah':
            dagingField.style.display = 'none';
            telurField.style.display = 'none';
            break;
        case 'sapi_potong':
            susuField.style.display = 'none';
            telurField.style.display = 'none';
            break;
        case 'ayam_petelur':
            susuField.style.display = 'none';
            dagingField.style.display = 'none';
            break;
        case 'ayam_pedaging':
            susuField.style.display = 'none';
            telurField.style.display = 'none';
            break;
        case 'kambing':
            telurField.style.display = 'none';
            break;
        case 'babi':
            susuField.style.display = 'none';
            telurField.style.display = 'none';
            break;
        default:
            // Show all fields
    }
}

function calculateProfit() {
    const biaya = parseFloat(document.getElementById('biaya_produksi').value) || 0;
    const harga = parseFloat(document.getElementById('harga_jual').value) || 0;
    const keuntungan = harga - biaya;
    
    document.getElementById('keuntungan').value = keuntungan;
    document.getElementById('keuntungan_display').value = formatRupiah(keuntungan);
    
    const profitValue = document.getElementById('profitValue');
    const profitIndicator = document.getElementById('profitIndicator');
    
    profitIndicator.className = 'profit-indicator';
    
    if (keuntungan > 0) {
        profitValue.innerHTML = `<span style="color: #28a745;"><i class="fas fa-arrow-up"></i> Untung: ${formatRupiah(keuntungan)}</span>`;
        profitIndicator.classList.add('profit-positive');
    } else if (keuntungan < 0) {
        profitValue.innerHTML = `<span style="color: #dc3545;"><i class="fas fa-arrow-down"></i> Rugi: ${formatRupiah(keuntungan)}</span>`;
        profitIndicator.classList.add('profit-negative');
    } else {
        profitValue.innerHTML = `<span style="color: #6c757d;"><i class="fas fa-equals"></i> Impas: ${formatRupiah(keuntungan)}</span>`;
        profitIndicator.classList.add('profit-neutral');
    }
}

function formatRupiah(angka) {
    if (isNaN(angka)) return 'Rp 0';
    
    let number_string = Math.abs(angka).toString();
    let sisa = number_string.length % 3;
    let rupiah = number_string.substr(0, sisa);
    let ribuan = number_string.substr(sisa).match(/\d{3}/g);
    
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    return 'Rp ' + (angka < 0 ? '-' : '') + rupiah;
}

function resetForm() {
    document.getElementById('produksiForm').reset();
    updateProductionFields();
    calculateProfit();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateProductionFields();
    calculateProfit();
    
    // Set default date
    const tanggalInput = document.getElementById('tanggal_produksi');
    if (tanggalInput && !tanggalInput.value) {
        tanggalInput.valueAsDate = new Date();
    }
});
</script>

<style>
.profit-indicator {
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f8f9fa;
}

.profit-positive {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
}

.profit-negative {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
}

.profit-neutral {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
}

.profit-label {
    font-weight: bold;
}

.profit-value {
    font-size: 1.2em;
    font-weight: bold;
}
</style>