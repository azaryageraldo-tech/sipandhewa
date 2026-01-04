<?php
// modules/penyakit_hewan/delete.php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    try {
        $data = fetchOne(
            "SELECT id, jenis_penyakit FROM penyakit_hewan WHERE id = ?",
            [$id]
        );

        if ($data) {
            $result = executeQuery(
                "DELETE FROM penyakit_hewan WHERE id = ?",
                [$id]
            );

            $_SESSION['success_message'] = $result
                ? "Data penyakit '{$data['jenis_penyakit']}' berhasil dihapus"
                : "Gagal menghapus data";
        } else {
            $_SESSION['error_message'] = "Data tidak ditemukan";
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "ID tidak valid";
}

// Redirect back to data page
header("Location: ../../dashboard.php?module=penyakit&action=data");
exit;