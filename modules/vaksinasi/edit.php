<?php
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';
$data = [];

// Ambil data berdasarkan ID
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: ?module=vaksinasi&action=data');
    exit();
}

// Ambil data yang akan diedit
$sql = "SELECT v.*, k.nama_kecamatan, d.nama_desa 
        FROM vaksinasi v 
        LEFT JOIN kecamatan k ON v.kecamatan_id = k.id 
        LEFT JOIN desa d ON v.desa_id = d.id 
        WHERE v.id = ?";
$data = fetchOne($sql, [$id]);

if (!$data) {
    $error = "Data vaksinasi tidak ditemukan";
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pemilik = sanitizeInput($_POST['nama_pemilik']);
    $desa = sanitizeInput($_POST['desa']);
    $kecamatan = $_POST['kecamatan'];
    $jenis_hewan = $_POST['jenis_hewan'];
    $umur_hewan = $_POST['umur_hewan'];
    $tanggal_vaksinasi = $_POST['tanggal_vaksinasi'];
    $jenis_vaksin = $_POST['jenis_vaksin'];
    
    // Validasi
    if (empty($nama_pemilik)) {
        $error = "Nama pemilik harus diisi";
    } elseif (!validateKecamatan($kecamatan)) {
        $error = "Kecamatan tidak valid";
    } elseif (!validateDate($tanggal_vaksinasi)) {
        $error = "Tanggal vaksinasi tidak valid";
    } else {
        try {
            $kecamatanId = getKecamatanId($kecamatan);
            $desaId = getOrCreateDesa($desa, $kecamatanId);
            
            $sql = "UPDATE vaksinasi SET
                    nama_pemilik = ?,
                    desa_id = ?,
                    kecamatan_id = ?,
                    jenis_hewan = ?,
                    umur_hewan = ?,
                    tanggal_vaksinasi = ?,
                    jenis_vaksin = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $params = [
                $nama_pemilik,
                $desaId,
                $kecamatanId,
                $jenis_hewan,
                $umur_hewan,
                $tanggal_vaksinasi,
                $jenis_vaksin,
                $id
            ];
            
            $result = updateData($sql, $params);
            
            if ($result) {
                $success = "✅ Data vaksinasi berhasil diperbarui!";
                
                // Update data yang ditampilkan
                $data = array_merge($data, [
                    'nama_pemilik' => $nama_pemilik,
                    'nama_desa' => $desa,
                    'nama_kecamatan' => $kecamatan,
                    'jenis_hewan' => $jenis_hewan,
                    'umur_hewan' => $umur_hewan,
                    'tanggal_vaksinasi' => $tanggal_vaksinasi,
                    'jenis_vaksin' => $jenis_vaksin
                ]);
            } else {
                $error = "❌ Gagal memperbarui data";
            }
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

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
        <h2><i class="fas fa-edit"></i> Edit Data Vaksinasi</h2>
        <p>ID: <strong>#<?php echo $id; ?></strong></p>
    </div>

    <?php if ($error && !$data): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
            <br><br>
            <a href="?module=vaksinasi&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Vaksinasi
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
            <form method="POST" action="" id="vaksinasiForm">
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Informasi Pemilik</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_pemilik"><i class="fas fa-user-tag"></i> Nama Pemilik *</label>
                            <input type="text" id="nama_pemilik" name="nama_pemilik" 
                                   value="<?php echo htmlspecialchars($data['nama_pemilik'] ?? ''); ?>"
                                   placeholder="Nama pemilik hewan" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="desa"><i class="fas fa-home"></i> Desa/Kelurahan *</label>
                            <input type="text" id="desa" name="desa" 
                                   value="<?php echo htmlspecialchars($data['nama_desa'] ?? ''); ?>"
                                   placeholder="Nama desa/kelurahan" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="kecamatan"><i class="fas fa-landmark"></i> Kecamatan *</label>
                            <select id="kecamatan" name="kecamatan" required>
                                <option value="">Pilih Kecamatan</option>
                                <?php foreach (getKecamatanList() as $kec): ?>
                                    <option value="<?php echo htmlspecialchars($kec); ?>" 
                                        <?php echo ($data['nama_kecamatan'] ?? '') == $kec ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kec); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-paw"></i> Informasi Hewan</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jenis_hewan"><i class="fas fa-dog"></i> Jenis Hewan *</label>
                            <select id="jenis_hewan" name="jenis_hewan" required>
                                <option value="">Pilih Jenis Hewan</option>
                                <option value="anjing" <?php echo ($data['jenis_hewan'] ?? '') == 'anjing' ? 'selected' : ''; ?>>Anjing</option>
                                <option value="kucing" <?php echo ($data['jenis_hewan'] ?? '') == 'kucing' ? 'selected' : ''; ?>>Kucing</option>
                                <option value="sapi" <?php echo ($data['jenis_hewan'] ?? '') == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                                <option value="kambing" <?php echo ($data['jenis_hewan'] ?? '') == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                                <option value="ayam" <?php echo ($data['jenis_hewan'] ?? '') == 'ayam' ? 'selected' : ''; ?>>Ayam</option>
                                <option value="babi" <?php echo ($data['jenis_hewan'] ?? '') == 'babi' ? 'selected' : ''; ?>>Babi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="umur_hewan"><i class="fas fa-birthday-cake"></i> Umur Hewan</label>
                            <input type="text" id="umur_hewan" name="umur_hewan" 
                                   value="<?php echo htmlspecialchars($data['umur_hewan'] ?? ''); ?>"
                                   placeholder="Contoh: 2 tahun, 6 bulan">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-syringe"></i> Detail Vaksinasi</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal_vaksinasi"><i class="fas fa-calendar"></i> Tanggal Vaksinasi *</label>
                            <input type="date" id="tanggal_vaksinasi" name="tanggal_vaksinasi" 
                                   value="<?php echo htmlspecialchars($data['tanggal_vaksinasi'] ?? date('Y-m-d')); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_vaksin"><i class="fas fa-vial"></i> Jenis Vaksin *</label>
                            <select id="jenis_vaksin" name="jenis_vaksin" required>
                                <option value="">Pilih Jenis Vaksin</option>
                                <option value="rabies" <?php echo ($data['jenis_vaksin'] ?? '') == 'rabies' ? 'selected' : ''; ?>>Rabies</option>
                                <option value="antraks" <?php echo ($data['jenis_vaksin'] ?? '') == 'antraks' ? 'selected' : ''; ?>>Antraks</option>
                                <option value="pmk" <?php echo ($data['jenis_vaksin'] ?? '') == 'pmk' ? 'selected' : ''; ?>>Penyakit Mulut dan Kuku (PMK)</option>
                                <option value="newcastle" <?php echo ($data['jenis_vaksin'] ?? '') == 'newcastle' ? 'selected' : ''; ?>>Newcastle Disease</option>
                                <option value="gumboro" <?php echo ($data['jenis_vaksin'] ?? '') == 'gumboro' ? 'selected' : ''; ?>>Gumboro</option>
                                <option value="brucellosis" <?php echo ($data['jenis_vaksin'] ?? '') == 'brucellosis' ? 'selected' : ''; ?>>Brucellosis</option>
                                <option value="lainnya" <?php echo ($data['jenis_vaksin'] ?? '') == 'lainnya' ? 'selected' : ''; ?>>Vaksin Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data Vaksinasi
                    </button>
                    <a href="?module=vaksinasi&action=data" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>)">
                        <i class="fas fa-trash"></i> Hapus Data
                    </button>
                </div>
            </form>
        </div>
        
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default date jika kosong
    const tanggalInput = document.getElementById('tanggal_vaksinasi');
    if (tanggalInput && !tanggalInput.value) {
        tanggalInput.valueAsDate = new Date();
    }
});

function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data vaksinasi ini?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = 'modules/vaksinasi/delete.php?id=' + id;
    }
}
</script>