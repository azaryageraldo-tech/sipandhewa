<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    try {
        $data = fetchOne(
            "SELECT id FROM survei_pasar WHERE id = ?",
            [$id]
        );

        if ($data) {
            $result = executeQuery(
                "DELETE FROM survei_pasar WHERE id = ?",
                [$id]
            );

            $_SESSION['success_message'] = $result
                ? "Data survei berhasil dihapus"
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

// 🔥 redirect langsung ke dashboard
header("Location: ../../dashboard.php?module=survei_pasar&action=data");
exit;
