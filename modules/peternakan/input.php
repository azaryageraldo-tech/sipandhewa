<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_unit_usaha = sanitizeInput($_POST['nama_unit_usaha']);
    $jenis_peternakan = $_POST['jenis_peternakan'];
    $alamat = sanitizeInput($_POST['alamat']);
    $desa = sanitizeInput($_POST['desa']);
    $kecamatan = $_POST['kecamatan'];
    $telepon = sanitizeInput($_POST['telepon']);
    $kapasitas_kandang = $_POST['kapasitas_kandang'] ?? 0;
    $jumlah_populasi = $_POST['jumlah_populasi'] ?? 0;
    $kepemilikan = $_POST['kepemilikan'];
    $bulan_panen = $_POST['bulan_panen'];
    
    // Validasi
    if (empty($nama_unit_usaha)) {
        $error = "Nama unit usaha harus diisi";
    } elseif (!validateKecamatan($kecamatan)) {
        $error = "Kecamatan tidak valid";
    } elseif ($kapasitas_kandang < 0 || $jumlah_populasi < 0) {
        $error = "Kapasitas dan populasi tidak boleh negatif";
    } else {
        try {
            // Get kecamatan_id
            $kecamatanId = getKecamatanId($kecamatan);
            
            // Insert data
            $sql = "INSERT INTO peternakan 
                    (nama_unit_usaha, jenis_peternakan, alamat, desa_id, kecamatan_id, 
                     telepon, kapasitas_kandang, jumlah_populasi, kepemilikan, bulan_panen, 
                     created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            // Get desa_id (create if not exists)
            $desaId = getOrCreateDesa($desa, $kecamatanId);
            
            $params = [
                $nama_unit_usaha,
                $jenis_peternakan,
                $alamat,
                $desaId,
                $kecamatanId,
                $telepon,
                $kapasitas_kandang,
                $jumlah_populasi,
                $kepemilikan,
                $bulan_panen,
                $_SESSION['user_id']
            ];
            
            $id = insertData($sql, $params);
            $success = "✅ Data peternakan berhasil disimpan! ID: " . $id;
            
            // Reset form
            $_POST = [];
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

// Helper functions
function getOrCreateDesa($namaDesa, $kecamatanId) {
    // Cek apakah desa sudah ada
    $sql = "SELECT id FROM desa WHERE nama_desa = ? AND kecamatan_id = ?";
    $existing = fetchOne($sql, [$namaDesa, $kecamatanId]);
    
    if ($existing) {
        return $existing['id'];
    }
    
    // Buat desa baru
    $sql = "INSERT INTO desa (kecamatan_id, nama_desa) VALUES (?, ?)";
    return insertData($sql, [$kecamatanId, $namaDesa]);
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-farm"></i> Sistem Peternakan</h2>
        <p>Input data peternakan di Kabupaten Buleleng</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="" id="peternakanForm">
            <div class="form-section">
                <h3><i class="fas fa-building"></i> Informasi Unit Usaha</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_unit_usaha"><i class="fas fa-signature"></i> Nama Unit Usaha/Peternak *</label>
                        <input type="text" id="nama_unit_usaha" name="nama_unit_usaha" 
                               value="<?php echo $_POST['nama_unit_usaha'] ?? ''; ?>"
                               placeholder="Contoh: Peternakan Sari Murni" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_peternakan"><i class="fas fa-paw"></i> Jenis Peternakan *</label>
                        <select id="jenis_peternakan" name="jenis_peternakan" required>
                            <option value="">Pilih Jenis</option>
                            <option value="ayam_ras_pedaging" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'ayam_ras_pedaging' ? 'selected' : ''; ?>>Ayam Ras Pedaging (Broiler)</option>
                            <option value="ayam_ras_petelur" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'ayam_ras_petelur' ? 'selected' : ''; ?>>Ayam Ras Petelur (Layer)</option>
                            <option value="sapi" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                            <option value="babi" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'babi' ? 'selected' : ''; ?>>Babi</option>
                            <option value="kambing_domba" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'kambing_domba' ? 'selected' : ''; ?>>Kambing/Domba</option>
                            <option value="unggas_lain" <?php echo ($_POST['jenis_peternakan'] ?? '') == 'unggas_lain' ? 'selected' : ''; ?>>Unggas Lain</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Lokasi Peternakan</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="alamat"><i class="fas fa-road"></i> Alamat Lengkap *</label>
                        <textarea id="alamat" name="alamat" rows="2" 
                                  placeholder="Jl. Raya No. 123, RT/RW ..." required><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="desa"><i class="fas fa-home"></i> Desa/Kelurahan *</label>
                        <input type="text" id="desa" name="desa" 
                               value="<?php echo $_POST['desa'] ?? ''; ?>"
                               placeholder="Nama desa/kelurahan" required>
                    </div>
                    
                   <div class="form-group">
                        <label for="kecamatan"><i class="fas fa-landmark"></i> Kecamatan *</label>
                        <select id="kecamatan" name="kecamatan" required>
                            <option value="">Pilih Kecamatan</option>
                            <?php 
                            try {
                                $kecamatanList = getKecamatanList();
                                if (empty($kecamatanList)) {
                                    echo '<option value="" disabled>ERROR: Data kecamatan tidak ditemukan</option>';
                                } else {
                                    foreach ($kecamatanList as $kec): ?>
                                        <option value="<?php echo htmlspecialchars($kec); ?>" <?php echo ($_POST['kecamatan'] ?? '') == $kec ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kec); ?>
                                        </option>
                                    <?php endforeach;
                                }
                            } catch (Exception $e) {
                                echo '<option value="" disabled>ERROR: ' . htmlspecialchars($e->getMessage()) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telepon"><i class="fas fa-phone"></i> Telepon/HP</label>
                        <input type="tel" id="telepon" name="telepon" 
                               value="<?php echo $_POST['telepon'] ?? ''; ?>"
                               placeholder="0812xxxxxxx">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-chart-bar"></i> Kapasitas dan Populasi</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kapasitas_kandang"><i class="fas fa-home"></i> Kapasitas Kandang *</label>
                        <input type="number" id="kapasitas_kandang" name="kapasitas_kandang" 
                               value="<?php echo $_POST['kapasitas_kandang'] ?? 0; ?>"
                               min="0" placeholder="0" required>
                        <small class="form-text">Jumlah maksimal ternak yang dapat ditampung</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_populasi"><i class="fas fa-crow"></i> Jumlah Populasi Saat Ini *</label>
                        <input type="number" id="jumlah_populasi" name="jumlah_populasi" 
                               value="<?php echo $_POST['jumlah_populasi'] ?? 0; ?>"
                               min="0" placeholder="0" required
                               oninput="checkCapacity()">
                        <small class="form-text">Jumlah ternak yang ada saat ini</small>
                    </div>
                </div>
                
                <div class="capacity-indicator" id="capacityIndicator">
                    <div class="capacity-label">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Utilisasi Kandang:</span>
                    </div>
                    <div class="capacity-value" id="capacityValue">
                        0%
                    </div>
                    <div class="capacity-bar">
                        <div class="capacity-fill" id="capacityFill" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-business-time"></i> Informasi Bisnis</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kepemilikan"><i class="fas fa-handshake"></i> Kepemilikan/Kemitraan *</label>
                        <select id="kepemilikan" name="kepemilikan" required>
                            <option value="">Pilih Kepemilikan</option>
                            <option value="Pribadi" <?php echo ($_POST['kepemilikan'] ?? '') == 'Pribadi' ? 'selected' : ''; ?>>Pribadi</option>
                            <option value="Kemitraan" <?php echo ($_POST['kepemilikan'] ?? '') == 'Kemitraan' ? 'selected' : ''; ?>>Kemitraan</option>
                            <option value="Kelompok" <?php echo ($_POST['kepemilikan'] ?? '') == 'Kelompok' ? 'selected' : ''; ?>>Kelompok Tani</option>
                            <option value="Koperasi" <?php echo ($_POST['kepemilikan'] ?? '') == 'Koperasi' ? 'selected' : ''; ?>>Koperasi</option>
                            <option value="Perusahaan" <?php echo ($_POST['kepemilikan'] ?? '') == 'Perusahaan' ? 'selected' : ''; ?>>Perusahaan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bulan_panen"><i class="fas fa-calendar-check"></i> Bulan Panen</label>
                        <select id="bulan_panen" name="bulan_panen">
                            <option value="">Pilih Bulan</option>
                            <?php 
                            $bulan = [
                                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];
                            foreach ($bulan as $index => $nama): 
                                $value = $index + 1;
                            ?>
                                <option value="<?php echo $value; ?>" <?php echo ($_POST['bulan_panen'] ?? '') == $value ? 'selected' : ''; ?>>
                                    <?php echo $nama; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Bulan utama panen/produksi</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Data Peternakan
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset Form
                </button>
                <button type="button" class="btn btn-info" onclick="checkCapacity()">
                    <i class="fas fa-calculator"></i> Hitung Utilisasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function checkCapacity() {
    const kapasitas = parseInt(document.getElementById('kapasitas_kandang').value) || 0;
    const populasi = parseInt(document.getElementById('jumlah_populasi').value) || 0;
    
    let utilization = 0;
    if (kapasitas > 0) {
        utilization = (populasi / kapasitas) * 100;
    }
    
    const capacityValue = document.getElementById('capacityValue');
    const capacityFill = document.getElementById('capacityFill');
    const capacityIndicator = document.getElementById('capacityIndicator');
    
    capacityValue.textContent = utilization.toFixed(1) + '%';
    capacityFill.style.width = Math.min(utilization, 100) + '%';
    
    // Set color based on utilization
    capacityIndicator.className = 'capacity-indicator';
    if (utilization > 100) {
        capacityIndicator.classList.add('capacity-danger');
        capacityValue.innerHTML = `<span style="color: #dc3545;">${utilization.toFixed(1)}% (OVER CAPACITY!)</span>`;
    } else if (utilization > 80) {
        capacityIndicator.classList.add('capacity-warning');
        capacityValue.innerHTML = `<span style="color: #ffc107;">${utilization.toFixed(1)}% (Tinggi)</span>`;
    } else if (utilization > 50) {
        capacityIndicator.classList.add('capacity-info');
        capacityValue.innerHTML = `<span style="color: #17a2b8;">${utilization.toFixed(1)}% (Sedang)</span>`;
    } else {
        capacityIndicator.classList.add('capacity-success');
        capacityValue.innerHTML = `<span style="color: #28a745;">${utilization.toFixed(1)}% (Rendah)</span>`;
    }
    
    // Check if population exceeds capacity
    if (populasi > kapasitas && kapasitas > 0) {
        alert(`⚠️ PERINGATAN: Populasi (${populasi}) melebihi kapasitas kandang (${kapasitas})!`);
    }
}

function resetForm() {
    document.getElementById('peternakanForm').reset();
    checkCapacity();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    checkCapacity();
    
    // Auto-suggest desa based on kecamatan
    const kecamatanSelect = document.getElementById('kecamatan');
    const desaInput = document.getElementById('desa');
    
    kecamatanSelect.addEventListener('change', function() {
        const kecamatan = this.value;
        if (kecamatan) {
            // You can implement autocomplete here
            // For now, just clear desa input
            desaInput.value = '';
            desaInput.focus();
        }
    });
});
</script>

<style>
.capacity-indicator {
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
    background-color: #f8f9fa;
}

.capacity-success {
    background-color: #d4edda !important;
    border: 1px solid #c3e6cb;
}

.capacity-info {
    background-color: #d1ecf1 !important;
    border: 1px solid #bee5eb;
}

.capacity-warning {
    background-color: #fff3cd !important;
    border: 1px solid #ffeaa7;
}

.capacity-danger {
    background-color: #f8d7da !important;
    border: 1px solid #f5c6cb;
}

.capacity-label {
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.capacity-value {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.capacity-bar {
    height: 10px;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.capacity-fill {
    height: 100%;
    background-color: #28a745;
    transition: width 0.3s ease;
}
</style>