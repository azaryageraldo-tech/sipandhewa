<?php
session_start(); // 🔥 WAJIB
require_once __DIR__ . '/../../includes/functions.php';

// Ambil parameter
$tanggal   = $_GET['tanggal'] ?? '';
$kecamatan = $_GET['kecamatan'] ?? '';

// Validasi
if (!$tanggal || !$kecamatan) {
    $_SESSION['error_message'] = 'Parameter tidak lengkap';
    header('Location: ../../dashboard.php?module=pemotongan&action=data');
    exit;
}

try {
    // Ambil ID kecamatan
    $kecamatanId = getKecamatanId($kecamatan);

    if (!$kecamatanId) {
        $_SESSION['error_message'] = 'Kecamatan tidak valid';
    } else {
        // Ambil data untuk logging
        $data = fetchOne(
            "SELECT COUNT(*) AS jumlah, SUM(total) AS total_hewan
             FROM pemotongan
             WHERE tanggal_pemotongan = ? AND kecamatan_id = ?",
            [$tanggal, $kecamatanId]
        );

        if (!$data || $data['jumlah'] == 0) {
            $_SESSION['error_message'] = 'Data tidak ditemukan';
        } else {
            // Hapus data
            executeQuery(
                "DELETE FROM pemotongan
                 WHERE tanggal_pemotongan = ? AND kecamatan_id = ?",
                [$tanggal, $kecamatanId]
            );

            // Logging
            if (!empty($_SESSION['user_id'])) {
                executeQuery(
                    "INSERT INTO activity_logs (user_id, action, details, created_at)
                     VALUES (?, ?, ?, NOW())",
                    [
                        $_SESSION['user_id'],
                        'DELETE_PEMOTONGAN',
                        'Menghapus pemotongan ' .
                        date('d/m/Y', strtotime($tanggal)) .
                        ' Kecamatan ' . $kecamatan .
                        ' Total ' . ($data['total_hewan'] ?? 0) . ' ekor'
                    ]
                );
            }

            $_SESSION['success_message'] =
                '✅ Data pemotongan (' . $kecamatan . ' - ' .
                date('d/m/Y', strtotime($tanggal)) . ') berhasil dihapus';
        }
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = '❌ Error: ' . $e->getMessage();
}

// 🔥 Redirect HARUS ke dashboard
header('Location: ../../dashboard.php?module=pemotongan&action=data');
exit;
