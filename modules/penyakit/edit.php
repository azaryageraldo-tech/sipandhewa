<?php
// modules/penyakit_hewan/edit.php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

// Get ID from URL
$id = $_GET['id'] ?? 0;

// Fetch existing data
$sql = "SELECT * FROM penyakit_hewan WHERE id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
    echo '<a href="?module=penyakit&action=data" class="btn btn-primary">Kembali ke Data</a>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Clean and validate input
    $jenis_ternak = $_POST['jenis_ternak'] ?? '';
    $bulan = $_POST['bulan'] ?? '';
    $minggu_ke = $_POST['minggu_ke'] ?? null;
    $jenis_penyakit = $_POST['jenis_penyakit'] ?? '';
    $kasus_digital = $_POST['kasus_digital'] ?? 0;
    $sampel_positif = $_POST['sampel_positif'] ?? 0;
    $sampel_negatif = $_POST['sampel_negatif'] ?? 0;
    $virus_teridentifikasi = $_POST['virus_teridentifikasi'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $status_penanganan = $_POST['status_penanganan'] ?? 'dalam_pengawasan';
    $catatan = $_POST['catatan'] ?? '';
    
    // Calculate total samples
    $total_sampel = $sampel_positif + $sampel_negatif;
    
    // Validation
    $errors = [];
    
    if (empty($jenis_ternak)) {
        $errors[] = "Jenis ternak harus dipilih";
    }
    if (empty($bulan)) {
        $errors[] = "Bulan harus diisi";
    }
    if (empty($jenis_penyakit)) {
        $errors[] = "Jenis penyakit harus diisi";
    }
    if ($minggu_ke && ($minggu_ke < 1 || $minggu_ke > 4)) {
        $errors[] = "Minggu ke harus antara 1-4";
    }
    if ($sampel_positif < 0 || $sampel_negatif < 0 || $kasus_digital < 0) {
        $errors[] = "Jumlah tidak boleh negatif";
    }
    
    if (empty($errors)) {
        try {
            // Check for duplicate entry (excluding current record)
            $checkSql = "SELECT id FROM penyakit_hewan 
                        WHERE jenis_ternak = ? 
                        AND bulan = ? 
                        AND jenis_penyakit = ?
                        AND id != ?";
            $checkParams = [$jenis_ternak, $bulan, $jenis_penyakit, $id];
            
            if ($minggu_ke) {
                $checkSql .= " AND minggu_ke = ?";
                $checkParams[] = $minggu_ke;
            } else {
                $checkSql .= " AND minggu_ke IS NULL";
            }
            
            $existing = fetchOne($checkSql, $checkParams);
            
            if ($existing) {
                $error = "Data penyakit untuk periode ini sudah ada. Silakan gunakan data yang sudah ada.";
            } else {
                // Update data
                $updateData = [
                    'jenis_ternak' => $jenis_ternak,
                    'bulan' => $bulan,
                    'minggu_ke' => $minggu_ke ?: null,
                    'jenis_penyakit' => $jenis_penyakit,
                    'kasus_digital' => $kasus_digital,
                    'sampel_positif' => $sampel_positif,
                    'sampel_negatif' => $sampel_negatif,
                    'total_sampel' => $total_sampel,
                    'virus_teridentifikasi' => $virus_teridentifikasi,
                    'lokasi' => $lokasi,
                    'status_penanganan' => $status_penanganan,
                    'catatan' => $catatan,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Build SQL query
                $sql = "UPDATE penyakit_hewan SET 
                        jenis_ternak = :jenis_ternak,
                        bulan = :bulan,
                        minggu_ke = :minggu_ke,
                        jenis_penyakit = :jenis_penyakit,
                        kasus_digital = :kasus_digital,
                        sampel_positif = :sampel_positif,
                        sampel_negatif = :sampel_negatif,
                        total_sampel = :total_sampel,
                        virus_teridentifikasi = :virus_teridentifikasi,
                        lokasi = :lokasi,
                        status_penanganan = :status_penanganan,
                        catatan = :catatan,
                        updated_at = :updated_at
                        WHERE id = :id";
                
                $updateData['id'] = $id;
                
                // Execute update
                $pdo = getDBConnection();
                $stmt = $pdo->prepare($sql);
                $stmt->execute($updateData);
                
                $success = "✅ Data penyakit hewan berhasil diupdate!";
                
                // Refresh data
                $data = fetchOne("SELECT * FROM penyakit_hewan WHERE id = ?", [$id]);
                
            }
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ " . implode("<br>", $errors);
    }
}
?>

<div class="module-container">
    <div class="module-header">
        <h2><i class="fas fa-edit"></i> Edit Data Penyakit Hewan</h2>
        <p>Edit data penyakit hewan menular strategis</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <button class="btn btn-sm" onclick="window.location.href='?module=penyakit&action=data'">
                <i class="fas fa-list"></i> Kembali ke Data
            </button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>



    <div class="form-card">
        <form method="POST" action="" id="editForm">
            <div class="form-section">
                <h3><i class="fas fa-calendar-alt"></i> Periode dan Jenis</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_ternak">Jenis Ternak *</label>
                        <select id="jenis_ternak" name="jenis_ternak" required>
                            <option value="">Pilih Jenis Ternak</option>
                            <option value="sapi" <?php echo $data['jenis_ternak'] == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                            <option value="kambing" <?php echo $data['jenis_ternak'] == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                            <option value="ayam" <?php echo $data['jenis_ternak'] == 'ayam' ? 'selected' : ''; ?>>Ayam</option>
                            <option value="bebek" <?php echo $data['jenis_ternak'] == 'bebek' ? 'selected' : ''; ?>>Bebek</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bulan">Bulan *</label>
                        <input type="month" id="bulan" name="bulan" 
                               value="<?php echo $data['bulan']; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="minggu_ke">Minggu Ke (opsional)</label>
                        <select id="minggu_ke" name="minggu_ke">
                            <option value="">Pilih Minggu</option>
                            <option value="1" <?php echo $data['minggu_ke'] == 1 ? 'selected' : ''; ?>>Minggu 1</option>
                            <option value="2" <?php echo $data['minggu_ke'] == 2 ? 'selected' : ''; ?>>Minggu 2</option>
                            <option value="3" <?php echo $data['minggu_ke'] == 3 ? 'selected' : ''; ?>>Minggu 3</option>
                            <option value="4" <?php echo $data['minggu_ke'] == 4 ? 'selected' : ''; ?>>Minggu 4</option>
                        </select>
                        <small class="form-text">Kosongkan untuk data bulanan</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_penyakit">Jenis Penyakit *</label>
                        <input type="text" id="jenis_penyakit" name="jenis_penyakit" 
                               value="<?php echo htmlspecialchars($data['jenis_penyakit']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="virus_teridentifikasi">Virus Teridentifikasi</label>
                        <input type="text" id="virus_teridentifikasi" name="virus_teridentifikasi" 
                               value="<?php echo htmlspecialchars($data['virus_teridentifikasi']); ?>"
                               placeholder="Nama virus/patogen">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-chart-bar"></i> Data Kasus dan Sampel</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kasus_digital">Jumlah Kasus Gigitan Rabies</label>
                        <div class="input-with-icon">
                            <i class="fas fa-laptop-medical"></i>
                            <input type="number" id="kasus_digital" name="kasus_digital" 
                                   value="<?php echo $data['kasus_digital']; ?>"
                                   min="0" required onchange="calculateTotal()">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sampel_positif">Sampel Positif</label>
                        <div class="input-with-icon">
                            <i class="fas fa-virus"></i>
                            <input type="number" id="sampel_positif" name="sampel_positif" 
                                   value="<?php echo $data['sampel_positif']; ?>"
                                   min="0" onchange="calculateTotal()">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sampel_negatif">Sampel Negatif</label>
                        <div class="input-with-icon">
                            <i class="fas fa-virus-slash"></i>
                            <input type="number" id="sampel_negatif" name="sampel_negatif" 
                                   value="<?php echo $data['sampel_negatif']; ?>"
                                   min="0" onchange="calculateTotal()">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Sampel</label>
                        <div class="readonly-field" id="totalSampelDisplay">
                            <?php echo $data['total_sampel']; ?>
                        </div>
                        <input type="hidden" id="total_sampel" name="total_sampel" value="<?php echo $data['total_sampel']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Positif Rate</label>
                        <div class="readonly-field" id="positifRateDisplay">
                            <?php 
                            $rate = $data['total_sampel'] > 0 ? 
                                round(($data['sampel_positif'] / $data['total_sampel']) * 100, 1) : 0;
                            echo $rate . '%';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Lokasi dan Status</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="lokasi">Lokasi Kejadian</label>
                        <input type="text" id="lokasi" name="lokasi" 
                               value="<?php echo htmlspecialchars($data['lokasi']); ?>"
                               placeholder="Contoh: Kecamatan Buleleng">
                    </div>
                    
                    <div class="form-group">
                        <label for="status_penanganan">Status Penanganan</label>
                        <select id="status_penanganan" name="status_penanganan">
                            <option value="dalam_pengawasan" <?php echo $data['status_penanganan'] == 'dalam_pengawasan' ? 'selected' : ''; ?>>Dalam Pengawasan</option>
                            <option value="dalam_penanganan" <?php echo $data['status_penanganan'] == 'dalam_penanganan' ? 'selected' : ''; ?>>Dalam Penanganan</option>
                            <option value="selesai" <?php echo $data['status_penanganan'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="catatan">Catatan/Rekomendasi</label>
                    <textarea id="catatan" name="catatan" rows="3"><?php echo htmlspecialchars($data['catatan']); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Data
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='?module=penyakit&action=data'">
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
function calculateTotal() {
    const positif = parseInt(document.getElementById('sampel_positif').value) || 0;
    const negatif = parseInt(document.getElementById('sampel_negatif').value) || 0;
    const total = positif + negatif;
    
    document.getElementById('totalSampelDisplay').textContent = total;
    document.getElementById('total_sampel').value = total;
    
    const rate = total > 0 ? ((positif / total) * 100).toFixed(1) : 0;
    document.getElementById('positifRateDisplay').textContent = rate + '%';
}

function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus data penyakit ini?')) {
        window.location.href = '?module=penyakit&action=delete&id=<?php echo $id; ?>';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>