<?php
require_once __DIR__ . '/../../includes/functions.php';

// Cek apakah ada parameter ID
$id = $_GET['id'] ?? 0;

if (!$id) {
    $_SESSION['error_message'] = 'ID data tidak valid';
    header('Location: ?module=peternakan&action=data');
    exit();
}

try {
    // Ambil data untuk logging
    $sql = "SELECT p.*, k.nama_kecamatan FROM peternakan p 
            JOIN kecamatan k ON p.kecamatan_id = k.id 
            WHERE p.id = ?";
    $data = fetchOne($sql, [$id]);
    
    if (!$data) {
        $_SESSION['error_message'] = 'Data tidak ditemukan';
        header('Location: ?module=peternakan&action=data');
        exit();
    }
    
    // Hapus data
    $sql = "DELETE FROM peternakan WHERE id = ?";
    $result = deleteData($sql, [$id]);
    
    if ($result) {
        // Logging aktivitas
        if (isset($_SESSION['user_id'])) {
            $logSql = "INSERT INTO activity_logs (user_id, action, details, created_at) 
                      VALUES (?, ?, ?, NOW())";
            $logDetails = "Menghapus data peternakan ID: {$id} - " . 
                         "Nama: " . ($data['nama_unit_usaha'] ?? 'Unknown') . " - " .
                         "Kecamatan: " . ($data['nama_kecamatan'] ?? 'Unknown');
            insertData($logSql, [$_SESSION['user_id'], 'DELETE_PETERNAKAN', $logDetails]);
        }
        
        $_SESSION['success_message'] = '✅ Data peternakan berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = '❌ Gagal menghapus data';
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = '❌ Error: ' . $e->getMessage();
}

// Redirect kembali ke halaman data
header("Location: ../../dashboard.php?module=peternakan&action=data");
exit;