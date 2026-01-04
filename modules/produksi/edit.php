<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';
$data = null;

// Get ID from URL
$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: ?module=produksi&action=data');
    exit();
}

// BUAT FUNGSI KHUSUS UNTUK UPDATE DI FILE INI SAJA
function executeUpdateQuery($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement");
        }
        
        // Bind parameters
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $paramType = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_float($value)) {
                    // Float tetap pakai PARAM_STR untuk PDO
                    $paramType = PDO::PARAM_STR;
                } elseif (is_bool($value)) {
                    $paramType = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $paramType = PDO::PARAM_NULL;
                }
                
                // Bind dengan index 1-based
                $stmt->bindValue($key + 1, $value, $paramType);
            }
        }
        
        $success = $stmt->execute();
        
        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Query failed: " . $errorInfo[2]);
        }
        
        // Kembalikan jumlah baris yang terpengaruh
        return $stmt->rowCount();
        
    } catch (Exception $e) {
        error_log("executeUpdateQuery error: " . $e->getMessage());
        throw $e;
    }
}

// Fetch existing data
$sql = "SELECT * FROM produksi WHERE id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    $error = "❌ Data produksi tidak ditemukan!";
    $data = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_peternak = sanitizeInput($_POST['nama_peternak'] ?? '');
    $jenis_peternakan = $_POST['jenis_peternakan'] ?? '';
    $jenis_pakan = sanitizeInput($_POST['jenis_pakan'] ?? '');
    $tanggal_produksi = $_POST['tanggal_produksi'] ?? '';
    
    // Production data
    $produksi_susu = floatval($_POST['produksi_susu'] ?? 0);
    $produksi_daging = floatval($_POST['produksi_daging'] ?? 0);
    $produksi_telur = intval($_POST['produksi_telur'] ?? 0);
    
    // Cost and price data
    $biaya_produksi = floatval($_POST['biaya_produksi'] ?? 0);
    $harga_jual = floatval($_POST['harga_jual'] ?? 0);
    $keuntungan = $harga_jual - $biaya_produksi;
    
    // Validate
    $errors = [];
    if (empty($nama_peternak)) {
        $errors[] = "Nama peternak harus diisi";
    }
    if (!validateDate($tanggal_produksi)) {
        $errors[] = "Tanggal produksi tidak valid";
    }
    if ($biaya_produksi < 0 || $harga_jual < 0) {
        $errors[] = "Biaya dan harga tidak boleh negatif";
    }
    
    if (empty($errors)) {
        try {
            // Debug: Tampilkan data yang akan diupdate
            error_log("Updating data for ID: $id");
            error_log("Nama: $nama_peternak, Jenis: $jenis_peternakan");
            error_log("Keuntungan: $keuntungan");
            
            // Update data - GUNAKAN FUNGSI KHUSUS
            $updateSql = "UPDATE produksi SET 
                    nama_peternak = ?,
                    jenis_peternakan = ?,
                    jenis_pakan = ?,
                    produksi_susu = ?,
                    produksi_daging = ?,
                    produksi_telur = ?,
                    biaya_produksi = ?,
                    harga_jual = ?,
                    keuntungan = ?,
                    tanggal_produksi = ?
                    WHERE id = ?";
            
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
                $id
            ];
            
            // Debug: Tampilkan SQL dan parameter
            error_log("SQL: $updateSql");
            error_log("Params: " . print_r($params, true));
            
            // GUNAKAN FUNGSI KHUSUS executeUpdateQuery
            // Menjadi:
            $affectedRows = updateData($updateSql, $params);
            error_log("Affected rows: $affectedRows");
            
            if ($affectedRows > 0) {
                $success = "✅ Data produksi berhasil diperbarui!";
                
                // Refresh data
                $sql = "SELECT * FROM produksi WHERE id = ?";
                $data = fetchOne($sql, [$id]);
                if ($data) {
                    $_POST = $data;
                }
            } else {
                // Check if data is actually different
                $currentData = $data;
                $isDifferent = 
                    $currentData['nama_peternak'] != $nama_peternak ||
                    $currentData['jenis_peternakan'] != $jenis_peternakan ||
                    $currentData['jenis_pakan'] != $jenis_pakan ||
                    $currentData['produksi_susu'] != $produksi_susu ||
                    $currentData['produksi_daging'] != $produksi_daging ||
                    $currentData['produksi_telur'] != $produksi_telur ||
                    $currentData['biaya_produksi'] != $biaya_produksi ||
                    $currentData['harga_jual'] != $harga_jual ||
                    $currentData['tanggal_produksi'] != $tanggal_produksi;
                
                if ($isDifferent) {
                    $error = "❌ Gagal memperbarui data. Silakan coba lagi.";
                } else {
                    $error = "⚠️ Tidak ada perubahan data.";
                }
            }
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
            error_log("Update error: " . $e->getMessage());
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Jika tidak ada data dari POST (saat pertama kali load), gunakan data dari database
if ($_SERVER['REQUEST_METHOD'] != 'POST' && $data) {
    $_POST = $data;
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-edit"></i> Edit Data Produksi</h2>
        <p>ID: <?php echo $id; ?> | 
           Terakhir diubah: <?php echo !empty($data['created_at']) ? date('d/m/Y H:i', strtotime($data['created_at'])) : '-'; ?></p>
        
        <div class="header-actions">
            <a href="?module=produksi&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Data
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <script>
                setTimeout(function() {
                    window.location.href = '?module=produksi&action=data';
                }, 2000);
            </script>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!$data && !$error): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Data tidak ditemukan.
        </div>
    <?php else: ?>
        <div class="form-card">
            <form method="POST" action="" id="produksiForm">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Informasi Peternak</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_peternak"><i class="fas fa-user-tag"></i> Nama Peternak *</label>
                            <input type="text" id="nama_peternak" name="nama_peternak" 
                                   value="<?php echo htmlspecialchars($_POST['nama_peternak'] ?? ''); ?>"
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
                                   value="<?php echo htmlspecialchars($_POST['jenis_pakan'] ?? ''); ?>"
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
                                   value="<?php echo floatval($_POST['produksi_susu'] ?? 0); ?>"
                                   min="0"  placeholder="0.0">
                        </div>
                        
                        <div class="form-group" id="dagingField">
                            <label for="produksi_daging"><i class="fas fa-drumstick-bite"></i> Produksi Daging (kg)</label>
                            <input type="number" id="produksi_daging" name="produksi_daging" 
                                   value="<?php echo floatval($_POST['produksi_daging'] ?? 0); ?>"
                                   min="0"  placeholder="0.0">
                        </div>
                        
                        <div class="form-group" id="telurField">
                            <label for="produksi_telur"><i class="fas fa-egg"></i> Produksi Telur (butir)</label>
                            <input type="number" id="produksi_telur" name="produksi_telur" 
                                   value="<?php echo intval($_POST['produksi_telur'] ?? 0); ?>"
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
                                   value="<?php echo floatval($_POST['biaya_produksi'] ?? 0); ?>"
                                   min="0"  placeholder="0" required
                                   oninput="calculateProfit()">
                            <small class="form-text">Total biaya produksi</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="harga_jual"><i class="fas fa-tag"></i> Harga Jual (Rp) *</label>
                            <input type="number" id="harga_jual" name="harga_jual" 
                                   value="<?php echo floatval($_POST['harga_jual'] ?? 0); ?>"
                                   min="0"  placeholder="0" required
                                   oninput="calculateProfit()">
                            <small class="form-text">Total pendapatan dari penjualan</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="keuntungan_display"><i class="fas fa-chart-line"></i> Keuntungan (Rp)</label>
                            <input type="text" id="keuntungan_display" readonly
                                   class="form-control" style="background-color: #f8f9fa;"
                                   value="<?php 
                                        $keuntungan = (floatval($_POST['harga_jual'] ?? 0)) - (floatval($_POST['biaya_produksi'] ?? 0));
                                        echo 'Rp ' . number_format($keuntungan, 0, ',', '.'); 
                                   ?>">
                            <input type="hidden" id="keuntungan" name="keuntungan" 
                                   value="<?php echo (floatval($_POST['harga_jual'] ?? 0)) - (floatval($_POST['biaya_produksi'] ?? 0)); ?>">
                        </div>
                    </div>
                    
                    <div class="profit-indicator" id="profitIndicator">
                        <div class="profit-label">
                            <i class="fas fa-info-circle"></i>
                            <span>Status Keuntungan:</span>
                        </div>
                        <div class="profit-value" id="profitValue">
                            <?php
                            $keuntungan = (floatval($_POST['harga_jual'] ?? 0)) - (floatval($_POST['biaya_produksi'] ?? 0));
                            if ($keuntungan > 0) {
                                echo '<span style="color: #28a745;"><i class="fas fa-arrow-up"></i> Untung: Rp ' . number_format($keuntungan, 0, ',', '.') . '</span>';
                            } elseif ($keuntungan < 0) {
                                echo '<span style="color: #dc3545;"><i class="fas fa-arrow-down"></i> Rugi: Rp ' . number_format($keuntungan, 0, ',', '.') . '</span>';
                            } else {
                                echo '<span style="color: #6c757d;"><i class="fas fa-equals"></i> Impas: Rp ' . number_format($keuntungan, 0, ',', '.') . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-redo"></i> Reset ke Asli
                    </button>
                    <button type="button" class="btn btn-info" onclick="calculateProfit()">
                        <i class="fas fa-calculator"></i> Hitung Ulang
                    </button>
                    <a href="?module=produksi&action=data" class="btn btn-danger">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
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
    // Get original values from PHP
    const originalValues = {
        nama_peternak: "<?php echo addslashes(htmlspecialchars($_POST['nama_peternak'] ?? '')); ?>",
        jenis_peternakan: "<?php echo $_POST['jenis_peternakan'] ?? ''; ?>",
        jenis_pakan: "<?php echo addslashes(htmlspecialchars($_POST['jenis_pakan'] ?? '')); ?>",
        tanggal_produksi: "<?php echo $_POST['tanggal_produksi'] ?? ''; ?>",
        produksi_susu: <?php echo floatval($_POST['produksi_susu'] ?? 0); ?>,
        produksi_daging: <?php echo floatval($_POST['produksi_daging'] ?? 0); ?>,
        produksi_telur: <?php echo intval($_POST['produksi_telur'] ?? 0); ?>,
        biaya_produksi: <?php echo floatval($_POST['biaya_produksi'] ?? 0); ?>,
        harga_jual: <?php echo floatval($_POST['harga_jual'] ?? 0); ?>
    };
    
    document.getElementById('nama_peternak').value = originalValues.nama_peternak;
    document.getElementById('jenis_peternakan').value = originalValues.jenis_peternakan;
    document.getElementById('jenis_pakan').value = originalValues.jenis_pakan;
    document.getElementById('tanggal_produksi').value = originalValues.tanggal_produksi;
    document.getElementById('produksi_susu').value = originalValues.produksi_susu;
    document.getElementById('produksi_daging').value = originalValues.produksi_daging;
    document.getElementById('produksi_telur').value = originalValues.produksi_telur;
    document.getElementById('biaya_produksi').value = originalValues.biaya_produksi;
    document.getElementById('harga_jual').value = originalValues.harga_jual;
    
    updateProductionFields();
    calculateProfit();
    
    alert('Form telah direset ke nilai asli!');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateProductionFields();
    calculateProfit();
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