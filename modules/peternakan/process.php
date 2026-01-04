<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'get_details':
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            try {
                $sql = "SELECT p.*, k.nama_kecamatan, d.nama_desa, u.fullname as petugas 
                        FROM peternakan p 
                        LEFT JOIN kecamatan k ON p.kecamatan_id = k.id 
                        LEFT JOIN desa d ON p.desa_id = d.id 
                        LEFT JOIN users u ON p.created_by = u.id 
                        WHERE p.id = ?";
                
                $data = fetchOne($sql, [$id]);
                
                if ($data) {
                    $response['success'] = true;
                    $response['data'] = $data;
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
        
    case 'get_statistics_by_kecamatan':
        try {
            $sql = "SELECT 
                    k.nama_kecamatan,
                    COUNT(p.id) as jumlah_peternakan,
                    SUM(p.kapasitas_kandang) as total_kapasitas,
                    SUM(p.jumlah_populasi) as total_populasi,
                    AVG(CASE WHEN p.kapasitas_kandang > 0 
                        THEN (p.jumlah_populasi / p.kapasitas_kandang) * 100 
                        ELSE 0 END) as rata_utilisasi
                    FROM peternakan p
                    JOIN kecamatan k ON p.kecamatan_id = k.id
                    GROUP BY k.nama_kecamatan
                    ORDER BY k.nama_kecamatan";
            
            $data = fetchAll($sql);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_growth_trend':
        $year = $_GET['year'] ?? date('Y');
        
        try {
            $sql = "SELECT 
                    DATE_FORMAT(p.created_at, '%Y-%m') as bulan,
                    COUNT(p.id) as peternakan_baru,
                    SUM(p.jumlah_populasi) as populasi_baru
                    FROM peternakan p
                    WHERE YEAR(p.created_at) = ?
                    GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
                    ORDER BY bulan";
            
            $data = fetchAll($sql, [$year]);
            
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
                $sql = "DELETE FROM peternakan WHERE id = ?";
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
        
        if ($id > 0) {
            try {
                $fields = [
                    'nama_unit_usaha' => $_POST['nama_unit_usaha'] ?? '',
                    'jenis_peternakan' => $_POST['jenis_peternakan'] ?? '',
                    'alamat' => $_POST['alamat'] ?? '',
                    'telepon' => $_POST['telepon'] ?? '',
                    'kapasitas_kandang' => $_POST['kapasitas_kandang'] ?? 0,
                    'jumlah_populasi' => $_POST['jumlah_populasi'] ?? 0,
                    'kepemilikan' => $_POST['kepemilikan'] ?? '',
                    'bulan_panen' => $_POST['bulan_panen'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Update desa if changed
                if (isset($_POST['desa']) && isset($_POST['kecamatan'])) {
                    $kecamatanId = getKecamatanId($_POST['kecamatan']);
                    $desaId = getOrCreateDesa($_POST['desa'], $kecamatanId);
                    $fields['desa_id'] = $desaId;
                    $fields['kecamatan_id'] = $kecamatanId;
                }
                
                // Build SQL
                $setParts = [];
                $params = [];
                foreach ($fields as $key => $value) {
                    $setParts[] = "$key = ?";
                    $params[] = $value;
                }
                $params[] = $id;
                
                $sql = "UPDATE peternakan SET " . implode(', ', $setParts) . " WHERE id = ?";
                $result = updateData($sql, $params);
                
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