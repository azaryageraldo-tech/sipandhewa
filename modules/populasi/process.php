<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'get_kecamatan_summary':
        $tahun = $_GET['tahun'] ?? date('Y');
        $bulan = $_GET['bulan'] ?? date('m');
        
        try {
            $sql = "SELECT 
                    k.nama_kecamatan,
                    COUNT(DISTINCT p.desa_id) as jumlah_desa,
                    SUM(p.total_populasi) as total_populasi,
                    SUM(p.jantan) as total_jantan,
                    SUM(p.betina) as total_betina
                    FROM populasi_ternak p
                    JOIN kecamatan k ON p.kecamatan_id = k.id
                    WHERE p.bulan = ? AND p.tahun = ?
                    GROUP BY k.nama_kecamatan
                    ORDER BY k.nama_kecamatan";
            
            $data = fetchAll($sql, [$bulan, $tahun]);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_species_distribution':
        $tahun = $_GET['tahun'] ?? date('Y');
        
        try {
            $sql = "SELECT 
                    jenis_ternak,
                    SUM(total_populasi) as total,
                    SUM(jantan) as jantan,
                    SUM(betina) as betina
                    FROM populasi_ternak
                    WHERE tahun = ?
                    GROUP BY jenis_ternak
                    ORDER BY total DESC";
            
            $data = fetchAll($sql, [$tahun]);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_monthly_trend':
        $tahun = $_GET['tahun'] ?? date('Y');
        $kecamatan = $_GET['kecamatan'] ?? '';
        
        try {
            $sql = "SELECT 
                    bulan,
                    SUM(total_populasi) as total
                    FROM populasi_ternak p
                    JOIN kecamatan k ON p.kecamatan_id = k.id
                    WHERE p.tahun = ?";
            
            $params = [$tahun];
            
            if ($kecamatan) {
                $sql .= " AND k.nama_kecamatan = ?";
                $params[] = $kecamatan;
            }
            
            $sql .= " GROUP BY bulan ORDER BY bulan";
            
            $data = fetchAll($sql, $params);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'delete_data':
        $id = $_POST['id'] ?? 0;
        
        if ($id > 0) {
            try {
                $sql = "DELETE FROM populasi_ternak WHERE id = ?";
                $result = deleteData($sql, [$id]);
                
                if ($result) {
                    $response['success'] = true;
                    $response['message'] = 'Data berhasil dihapus';
                } else {
                    $response['message'] = 'Data tidak ditemukan';
                }
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'ID tidak valid';
        }
        break;
        
    case 'update_data':
        $id = $_POST['id'] ?? 0;
        $jantan = $_POST['jantan'] ?? 0;
        $betina = $_POST['betina'] ?? 0;
        $induk = $_POST['induk'] ?? 0;
        
        if ($id > 0) {
            try {
                $total = $jantan + $betina + $induk;
                
                $sql = "UPDATE populasi_ternak 
                        SET jantan = ?, betina = ?, induk = ?, total_populasi = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $result = updateData($sql, [$jantan, $betina, $induk, $total, $id]);
                
                if ($result) {
                    $response['success'] = true;
                    $response['message'] = 'Data berhasil diupdate';
                } else {
                    $response['message'] = 'Data tidak ditemukan';
                }
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'ID tidak valid';
        }
        break;
        
    default:
        $response['message'] = 'Action tidak valid';
}

echo json_encode($response);
exit();