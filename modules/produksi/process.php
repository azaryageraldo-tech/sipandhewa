<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_details':
        getDetails();
        break;
    case 'update':
        updateData();
        break;
    case 'delete':
        deleteData();
        break;
    case 'get_edit_data':
        getEditData();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}

function getDetails() {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }
        
        $sql = "SELECT p.*, u.fullname as petugas 
                FROM produksi p 
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE p.id = ?";
        $data = fetchOne($sql, [$id]);
        
        if ($data) {
            // Format data
            $data['jenis_peternakan_text'] = ucwords(str_replace('_', ' ', $data['jenis_peternakan']));
            $data['tanggal_produksi_formatted'] = date('d/m/Y', strtotime($data['tanggal_produksi']));
            $data['created_at_formatted'] = date('d/m/Y H:i', strtotime($data['created_at']));
            
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getEditData() {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }
        
        $sql = "SELECT * FROM produksi WHERE id = ?";
        $data = fetchOne($sql, [$id]);
        
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateData() {
    try {
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }
        
        // Get and sanitize input
        $nama_peternak = sanitizeInput($_POST['nama_peternak']);
        $jenis_peternakan = $_POST['jenis_peternakan'];
        $jenis_pakan = sanitizeInput($_POST['jenis_pakan']);
        $tanggal_produksi = $_POST['tanggal_produksi'];
        
        // Production data
        $produksi_susu = $_POST['produksi_susu'] ?? 0;
        $produksi_daging = $_POST['produksi_daging'] ?? 0;
        $produksi_telur = $_POST['produksi_telur'] ?? 0;
        
        // Cost and price data
        $biaya_produksi = $_POST['biaya_produksi'] ?? 0;
        $harga_jual = $_POST['harga_jual'] ?? 0;
        $keuntungan = $harga_jual - $biaya_produksi;
        
        // Validate
        if (empty($nama_peternak)) {
            echo json_encode(['success' => false, 'message' => 'Nama peternak harus diisi']);
            return;
        }
        
        if (!validateDate($tanggal_produksi)) {
            echo json_encode(['success' => false, 'message' => 'Tanggal produksi tidak valid']);
            return;
        }
        
        // Update data
        $sql = "UPDATE produksi SET 
                nama_peternak = ?,
                jenis_peternakan = ?,
                jenis_pakan = ?,
                produksi_susu = ?,
                produksi_daging = ?,
                produksi_telur = ?,
                biaya_produksi = ?,
                harga_jual = ?,
                keuntungan = ?,
                tanggal_produksi = ?
                WHERE id = ?";
        
        $params = [
            $nama_peternak,
            $jenis_peternakan,
            $jenis_pakan,
            $produksi_susu,
            $produksi_daging,
            $produksi_telur,
            $biaya_produksi,
            $harga_jual,
            $keuntungan,
            $tanggal_produksi,
            $id
        ];
        
        $affectedRows = executeQuery($sql, $params);
        
        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data yang diubah']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteData() {
    try {
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }
        
        // Check if data exists
        $checkSql = "SELECT id FROM produksi WHERE id = ?";
        $exists = fetchOne($checkSql, [$id]);
        
        if (!$exists) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
        
        // Delete data
        $sql = "DELETE FROM produksi WHERE id = ?";
        $affectedRows = executeQuery($sql, [$id]);
        
        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>