<?php
require_once __DIR__ . '/../../includes/functions.php';

$kecamatan_id = $_GET['kecamatan_id'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

if (!$kecamatan_id || !$bulan || !$tahun) {
    $_SESSION['error_message'] = 'Parameter tidak lengkap';
    header('Location: dashboard.php?module=populasi&action=data');
    exit;
}

try {
    // Hitung total data yang akan dihapus
    $sql_count = "SELECT COUNT(*) as total, 
                         SUM(total_semua) as total_hewan,
                         GROUP_CONCAT(DISTINCT desa_id) as desa_list
                  FROM populasi_ternak
                  WHERE kecamatan_id = ? AND bulan = ? AND tahun = ?";
    $data = fetchOne($sql_count, [$kecamatan_id, $bulan, $tahun]);
    
    if ($data['total'] == 0) {
        $_SESSION['error_message'] = '❌ Data tidak ditemukan';
        header('Location: dashboard.php?module=populasi&action=data');
        exit;
    }
    
    // Hapus data
    $sql_delete = "DELETE FROM populasi_ternak
                   WHERE kecamatan_id = ? AND bulan = ? AND tahun = ?";
    $result = executeQuery($sql_delete, [$kecamatan_id, $bulan, $tahun]);

    if ($result) {
        $_SESSION['success_message'] = '✅ Data populasi berhasil dihapus';
        $_SESSION['success_message'] .= '<br>📊 Total data dihapus: ' . $data['total'] . ' desa';
        $_SESSION['success_message'] .= '<br>🐄 Total hewan dihapus: ' . ($data['total_hewan'] ?: 0);
    } else {
        $_SESSION['error_message'] = '❌ Gagal menghapus data';
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = '❌ Error: ' . $e->getMessage();
}

echo '<script>window.location.href = "dashboard.php?module=populasi&action=data";</script>';
exit;
?>