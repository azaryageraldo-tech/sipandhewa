<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    try {
        // Ambil data dulu (buat pesan)
        $data = fetchOne(
            "SELECT id, nama_peternak, tanggal_produksi 
             FROM produksi 
             WHERE id = ?",
            [$id]
        );

        if ($data) {
            $result = executeQuery(
                "DELETE FROM produksi WHERE id = ?",
                [$id]
            );

            $_SESSION['success_message'] = $result
                ? "✅ Data produksi atas nama <b>{$data['nama_peternak']}</b> berhasil dihapus"
                : "❌ Gagal menghapus data produksi";
        } else {
            $_SESSION['error_message'] = "❌ Data produksi tidak ditemukan";
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = "❌ " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "❌ ID tidak valid";
}

// balik ke halaman data
header("Location: ../../dashboard.php?module=produksi&action=data");
exit;
