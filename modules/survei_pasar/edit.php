<?php
// modules/survei_pasar/edit.php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

// Get ID from URL
$id = $_GET['id'] ?? 0;

// Fetch existing data
$sql = "SELECT * FROM survei_pasar WHERE id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
    echo '<a href="?module=survei_pasar&action=data" class="btn btn-primary">Kembali ke Data</a>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Clean and validate input
    $tanggal = $_POST['tanggal_survei'] ?? '';
    $lokasi = $_POST['lokasi_pasar'] ?? '';
    $komoditas = $_POST['komoditas'] ?? '';
    $nama_surveilens = $_POST['nama_surveilens'] ?? '';
    $nomor_hp = $_POST['nomor_hp'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    // Get harga values
    $harga_ayam_utuh = $_POST['harga_ayam_utuh'] ?? 0;
    $harga_dada_ayam = $_POST['harga_dada_ayam'] ?? 0;
    $harga_babi_utuh = $_POST['harga_babi_utuh'] ?? 0;
    $harga_balung_babi = $_POST['harga_balung_babi'] ?? 0;
    $harga_babi_isi = $_POST['harga_babi_isi'] ?? 0;
    $harga_balung_sapi = $_POST['harga_balung_sapi'] ?? 0;
    $harga_sapi_isi = $_POST['harga_sapi_isi'] ?? 0;
    
    // Validation
    if (empty($tanggal)) {
        $error = "Tanggal survei harus diisi";
    } elseif (empty($lokasi)) {
        $error = "Lokasi pasar harus dipilih";
    } elseif (empty($komoditas)) {
        $error = "Komoditas harus dipilih";
    } else {
        try {
            // Update data
            $updateData = [
                'tanggal_survei' => $tanggal,
                'lokasi_pasar' => $lokasi,
                'komoditas' => $komoditas,
                'nama_surveilens' => $nama_surveilens,
                'nomor_hp' => $nomor_hp,
                'harga_ayam_utuh' => $harga_ayam_utuh ? floatval($harga_ayam_utuh) : 0,
                'harga_dada_ayam' => $harga_dada_ayam ? floatval($harga_dada_ayam) : 0,
                'harga_babi_utuh' => $harga_babi_utuh ? floatval($harga_babi_utuh) : 0,
                'harga_balung_babi' => $harga_balung_babi ? floatval($harga_balung_babi) : 0,
                'harga_babi_isi' => $harga_babi_isi ? floatval($harga_babi_isi) : 0,
                'harga_balung_sapi' => $harga_balung_sapi ? floatval($harga_balung_sapi) : 0,
                'harga_sapi_isi' => $harga_sapi_isi ? floatval($harga_sapi_isi) : 0,
                'catatan' => $catatan,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Build SQL query
            $sql = "UPDATE survei_pasar SET 
                    tanggal_survei = :tanggal_survei,
                    lokasi_pasar = :lokasi_pasar,
                    komoditas = :komoditas,
                    nama_surveilens = :nama_surveilens,
                    nomor_hp = :nomor_hp,
                    harga_ayam_utuh = :harga_ayam_utuh,
                    harga_dada_ayam = :harga_dada_ayam,
                    harga_babi_utuh = :harga_babi_utuh,
                    harga_balung_babi = :harga_balung_babi,
                    harga_babi_isi = :harga_babi_isi,
                    harga_balung_sapi = :harga_balung_sapi,
                    harga_sapi_isi = :harga_sapi_isi,
                    catatan = :catatan,
                    updated_at = :updated_at
                    WHERE id = :id";
            
            $updateData['id'] = $id;
            
            // Execute update
            $pdo = getDBConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateData);
            
            $success = "✅ Data survei berhasil diupdate!";
            
            // Refresh data
            $data = fetchOne("SELECT * FROM survei_pasar WHERE id = ?", [$id]);
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-edit"></i> Edit Data Survei Pasar</h2>
        <p>Edit data survei harga produk peternakan</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <button class="btn btn-sm" onclick="window.location.href='?module=survei_pasar&action=data'">
                <i class="fas fa-list"></i> Kembali ke Data
            </button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Data Info -->
    <!-- <div class="info-card">
        <h4><i class="fas fa-info-circle"></i> Informasi Data</h4>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID Survei:</span>
                <span class="info-value"><?php echo $data['id']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Dibuat oleh:</span>
                <span class="info-value">
                    <?php 
                    $user_sql = "SELECT fullname FROM users WHERE id = ?";
                    $user = fetchOne($user_sql, [$data['created_by']]);
                    echo $user['fullname'] ?? '-';
                    ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Input:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($data['created_at'])); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Terakhir Update:</span>
                <span class="info-value">
                    <?php echo $data['updated_at'] ? date('d/m/Y H:i', strtotime($data['updated_at'])) : 'Belum pernah'; ?>
                </span>
            </div>
        </div>
    </div> -->

    <div class="form-card">
        <form method="POST" action="" id="editForm">
            <div class="form-section">
                <h3><i class="fas fa-calendar"></i> Informasi Survei</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_survei">Tanggal Survei *</label>
                        <input type="date" id="tanggal_survei" name="tanggal_survei" 
                               value="<?php echo date('Y-m-d', strtotime($data['tanggal_survei'])); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lokasi_pasar">Lokasi Pasar *</label>
                        <select id="lokasi_pasar" name="lokasi_pasar" required>
                            <option value="">Pilih Pasar</option>
                            <option value="Pasar Banyuasri" <?php echo $data['lokasi_pasar'] == 'Pasar Banyuasri' ? 'selected' : ''; ?>>Pasar Banyuasri</option>
                            <option value="Pasar Anyar" <?php echo $data['lokasi_pasar'] == 'Pasar Anyar' ? 'selected' : ''; ?>>Pasar Anyar</option>
                            <option value="Pasar Buleleng" <?php echo $data['lokasi_pasar'] == 'Pasar Buleleng' ? 'selected' : ''; ?>>Pasar Buleleng</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="komoditas">Komoditas *</label>
                        <select id="komoditas" name="komoditas" required onchange="showHargaFields()">
                            <option value="">Pilih Komoditas</option>
                            <option value="Daging Ayam" <?php echo $data['komoditas'] == 'Daging Ayam' ? 'selected' : ''; ?>>Daging Ayam</option>
                            <option value="Daging Babi" <?php echo $data['komoditas'] == 'Daging Babi' ? 'selected' : ''; ?>>Daging Babi</option>
                            <option value="Daging Sapi" <?php echo $data['komoditas'] == 'Daging Sapi' ? 'selected' : ''; ?>>Daging Sapi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_surveilens">Nama Surveilens/Penjual</label>
                        <input type="text" id="nama_surveilens" name="nama_surveilens" 
                               value="<?php echo htmlspecialchars($data['nama_surveilens']); ?>"
                               placeholder="Nama penjual/pedagang">
                    </div>
                    
                    <div class="form-group">
                        <label for="nomor_hp">Nomor HP/Telepon</label>
                        <input type="tel" id="nomor_hp" name="nomor_hp" 
                               value="<?php echo htmlspecialchars($data['nomor_hp']); ?>"
                               placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </div>

            <!-- Harga Ayam -->
            <div class="form-section" id="hargaAyamSection" style="display: <?php echo $data['komoditas'] == 'Daging Ayam' ? 'block' : 'none'; ?>;">
                <h3><i class="fas fa-drumstick-bite"></i> Harga Daging Ayam (Rp/kg)</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_ayam_utuh">Harga Ayam Utuh</label>
                        <div class="input-with-icon">
                            <i class="fas fa-money-bill"></i>
                            <input type="number" id="harga_ayam_utuh" name="harga_ayam_utuh" 
                                   value="<?php echo $data['harga_ayam_utuh']; ?>"
                                   min="0"  placeholder="Contoh: 35000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_dada_ayam">Harga Dada Ayam</label>
                        <div class="input-with-icon">
                            <i class="fas fa-money-bill"></i>
                            <input type="number" id="harga_dada_ayam" name="harga_dada_ayam" 
                                   value="<?php echo $data['harga_dada_ayam']; ?>"
                                   min="0"  placeholder="Contoh: 45000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Harga Babi -->
            <div class="form-section" id="hargaBabiSection" style="display: <?php echo $data['komoditas'] == 'Daging Babi' ? 'block' : 'none'; ?>;">
                <h3><i class="fas fa-bacon"></i> Harga Daging Babi (Rp/kg)</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_babi_utuh">Harga Babi Utuh</label>
                        <div class="input-with-icon">
                            <i class="fas fa-money-bill"></i>
                            <input type="number" id="harga_babi_utuh" name="harga_babi_utuh" 
                                   value="<?php echo $data['harga_babi_utuh']; ?>"
                                   min="0"  placeholder="Contoh: 50000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_balung_babi">Harga Balung Babi</label>
                        <div class="input-with-icon">
                            <i class="fas fa-bone"></i>
                            <input type="number" id="harga_balung_babi" name="harga_balung_babi" 
                                   value="<?php echo $data['harga_balung_babi']; ?>"
                                   min="0"  placeholder="Contoh: 25000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_babi_isi">Harga Babi Isi</label>
                        <div class="input-with-icon">
                            <i class="fas fa-bacon"></i>
                            <input type="number" id="harga_babi_isi" name="harga_babi_isi" 
                                   value="<?php echo $data['harga_babi_isi']; ?>"
                                   min="0"  placeholder="Contoh: 60000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Harga Sapi -->
            <div class="form-section" id="hargaSapiSection" style="display: <?php echo $data['komoditas'] == 'Daging Sapi' ? 'block' : 'none'; ?>;">
                <h3><i class="fas fa-drumstick-bite"></i> Harga Daging Sapi (Rp/kg)</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_balung_sapi">Harga Balung Sapi</label>
                        <div class="input-with-icon">
                            <i class="fas fa-bone"></i>
                            <input type="number" id="harga_balung_sapi" name="harga_balung_sapi" 
                                   value="<?php echo $data['harga_balung_sapi']; ?>"
                                   min="0"  placeholder="Contoh: 40000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_sapi_isi">Harga Sapi Isi</label>
                        <div class="input-with-icon">
                            <i class="fas fa-drumstick-bite"></i>
                            <input type="number" id="harga_sapi_isi" name="harga_sapi_isi" 
                                   value="<?php echo $data['harga_sapi_isi']; ?>"
                                   min="0"  placeholder="Contoh: 120000">
                            <span class="input-suffix">/kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-sticky-note"></i> Catatan Tambahan</h3>
                <div class="form-group">
                    <textarea id="catatan" name="catatan" rows="3" 
                              placeholder="Catatan tambahan mengenai survei..."><?php echo htmlspecialchars($data['catatan']); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Data
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='?module=survei_pasar&action=data'">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Hapus Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showHargaFields() {
    const komoditas = document.getElementById('komoditas').value;
    
    document.getElementById('hargaAyamSection').style.display = 'none';
    document.getElementById('hargaBabiSection').style.display = 'none';
    document.getElementById('hargaSapiSection').style.display = 'none';
    
    if (komoditas === 'Daging Ayam') {
        document.getElementById('hargaAyamSection').style.display = 'block';
    } else if (komoditas === 'Daging Babi') {
        document.getElementById('hargaBabiSection').style.display = 'block';
    } else if (komoditas === 'Daging Sapi') {
        document.getElementById('hargaSapiSection').style.display = 'block';
    }
}

function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus data survei ini?')) {
        window.location.href = '?module=survei_pasar&action=delete&id=<?php echo $id; ?>';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Already initialized by PHP display logic
});
</script>