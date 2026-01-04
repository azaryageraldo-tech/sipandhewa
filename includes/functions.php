<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php
/**
 * Common Functions
 */

// Include database config
require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Get current user info
 */
function getUserInfo()
{
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'User',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }
    return null;
}

/**
 * Generate unique ID
 */
function generateUniqueId($prefix = 'TRN')
{
    $date = date('ymd');
    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . '-' . $date . $random;
}

/**
 * Get current date/time
 */
function getCurrentDateTime()
{
    return date('Y-m-d H:i:s');
}

/**
 * Validate date
 */
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Calculate percentage
 */
function calculatePercentage($part, $total)
{
    if ($total == 0) return 0;
    return round(($part / $total) * 100, 2);
}

/**
 * Get Kecamatan list
 */


/**
 * Get Komoditas list for survei pasar
 */
function getKomoditasList()
{
    return [
        'daging_babi' => 'Daging Babi',
        'daging_sapi' => 'Daging Sapi',
        'daging_ayam' => 'Daging Ayam',
        'ayam_utuh' => 'Ayam Utuh',
        'dada_ayam' => 'Dada Ayam',
        'balung_babi' => 'Balung Babi',
        'babi_isi' => 'Daging Babi Isi',
        'balung_sapi' => 'Balung Sapi',
        'sapi_isi' => 'Daging Sapi Isi'
    ];
}

/**
 * Get Jenis Ternak list
 */
function getJenisTernakList()
{
    return [
        'sapi_potong' => 'Sapi Potong',
        'sapi_perah' => 'Sapi Perah',
        'ayam_petelur' => 'Ayam Petelur',
        'ayam_pedaging' => 'Ayam Pedaging/Broiler',
        'kambing' => 'Kambing/Domba',
        'babi_bali' => 'Babi Bali',
        'babi_landrace' => 'Babi Landrace',
        'kerbau' => 'Kerbau',
        'kuda' => 'Kuda',
        'unggas' => 'Unggas Lain'
    ];
}

/**
 * Display success message
 */
function showSuccess($message)
{
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display error message
 */
function showError($message)
{
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

// Tambahkan di functions.php setelah fungsi yang sudah ada


function getSurveiData($id)
{
    return getDataById('survei_pasar', $id);
}

function validateSurveiData($data)
{
    $errors = [];

    if (empty($data['tanggal_survei'])) {
        $errors[] = "Tanggal survei harus diisi";
    }

    if (empty($data['lokasi_pasar'])) {
        $errors[] = "Lokasi pasar harus dipilih";
    }

    if (empty($data['komoditas'])) {
        $errors[] = "Komoditas harus dipilih";
    }

    // Validate harga based on komoditas
    if ($data['komoditas'] == 'Daging Ayam') {
        if (empty($data['harga_ayam_utuh']) && empty($data['harga_dada_ayam'])) {
            $errors[] = "Minimal satu harga harus diisi untuk Daging Ayam";
        }
    }

    return $errors;
}
// Helper functions untuk populasi
// function getKecamatanId($nama) {
//     $sql = "SELECT id FROM kecamatan WHERE nama_kecamatan = ?";
//     $result = fetchOne($sql, [$nama]);
//     return $result['id'] ?? 0;
// }

// function getDesaId($nama, $kecamatanId) {
//     $sql = "SELECT id FROM desa WHERE nama_desa = ? AND kecamatan_id = ?";
//     $result = fetchOne($sql, [$nama, $kecamatanId]);
//     return $result['id'] ?? 0;
// }

function getKecamatanList()
{
    try {
        // Ganti 'kecamatan' dengan nama tabel yang sesuai
        $sql = "SELECT nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan";
        $result = fetchAll($sql);

        $kecamatanList = [];
        foreach ($result as $row) {
            $kecamatanList[] = $row['nama_kecamatan'];
        }

        // Jika tabel tidak ada, return list default kecamatan Buleleng
        if (empty($kecamatanList)) {
            return [
                'Sukasada',
                'Buleleng',
                'Banjar',
                'Seririt',
                'Busungbiu',
                'Sawan',
                'Kubutambahan',
                'Tejakula'
            ];
        }

        return $kecamatanList;
    } catch (Exception $e) {
        // Return list default jika terjadi error
        return [
            'Sukasada',
            'Buleleng',
            'Banjar',
            'Seririt',
            'Busungbiu',
            'Sawan',
            'Kubutambahan',
            'Tejakula'
        ];
    }
}

// Juga pastikan fungsi validateKecamatan() ada
function validateKecamatan($kecamatan)
{
    $kecamatanList = getKecamatanList();
    return in_array($kecamatan, $kecamatanList);
}

function getKecamatanId($namaKecamatan)
{
    try {
        $sql = "SELECT id FROM kecamatan WHERE nama_kecamatan = ?";
        $result = fetchOne($sql, [$namaKecamatan]);

        if ($result) {
            return $result['id'];
        } else {
            // Jika tidak ditemukan, cari berdasarkan ID jika input adalah angka
            if (is_numeric($namaKecamatan)) {
                $sql = "SELECT id FROM kecamatan WHERE id = ?";
                $result = fetchOne($sql, [$namaKecamatan]);
                if ($result) {
                    return $result['id'];
                }
            }
            throw new Exception("Kecamatan tidak ditemukan");
        }
    } catch (Exception $e) {
        throw new Exception("Error getting kecamatan ID: " . $e->getMessage());
    }
}
function getDesaByKecamatan($kecamatanId)
{
    $sql = "SELECT id, nama_desa FROM desa WHERE kecamatan_id = ? ORDER BY nama_desa";
    return fetchAll($sql, [$kecamatanId]);
}

function getPopulasiSummary($bulan, $tahun)
{
    $sql = "SELECT 
            k.nama_kecamatan,
            COUNT(DISTINCT p.desa_id) as jumlah_desa,
            SUM(p.total_semua) as total,
            SUM(p.sapi_bali_total) as sapi,
            SUM(p.kerbau_total) as kerbau,
            SUM(p.kuda_total) as kuda,
            SUM(p.babi_bali_total + p.babi_landrace_total) as babi,
            SUM(p.kambing_total) as kambing,
            SUM(p.unggas_total) as unggas,
            SUM(p.anjing_total) as anjing
            FROM populasi_ternak p
            JOIN kecamatan k ON p.kecamatan_id = k.id
            WHERE p.bulan = ? AND p.tahun = ?
            GROUP BY k.nama_kecamatan
            ORDER BY k.nama_kecamatan";

    return fetchAll($sql, [$bulan, $tahun]);
}
?>