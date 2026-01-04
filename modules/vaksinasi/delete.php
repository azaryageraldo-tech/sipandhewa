<?php
require_once __DIR__ . '/../../includes/functions.php';

// Cek apakah ada parameter ID
$id = $_GET['id'] ?? 0;



try {
    // Ambil data untuk logging (opsional)
    $sql = "SELECT * FROM vaksinasi WHERE id = ?";
    $data = fetchOne($sql, [$id]);
    
    // Hapus data
    $sql = "DELETE FROM vaksinasi WHERE id = ?";
    $result = deleteData($sql, [$id]);
    
    if ($result) {
        // Logging aktivitas (opsional)
        if (isset($_SESSION['user_id'])) {
            $logSql = "INSERT INTO activity_logs (user_id, action, details, created_at) 
                      VALUES (?, ?, ?, NOW())";
            $logDetails = "Menghapus data vaksinasi ID: {$id} - Pemilik: " . ($data['nama_pemilik'] ?? 'Unknown');
            insertData($logSql, [$_SESSION['user_id'], 'DELETE_VAKSINASI', $logDetails]);
        }
        
        $_SESSION['success_message'] = '✅ Data vaksinasi berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = '❌ Data tidak ditemukan';
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = '❌ Error: ' . $e->getMessage();
}

// Redirect kembali ke halaman data
header("Location: ../../dashboard.php?module=vaksinasi&action=data");

exit();