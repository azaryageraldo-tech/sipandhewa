<?php
// modules/survei_pasar/process.php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => null];

switch ($action) {
    case 'get_data':
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            try {
                $sql = "SELECT s.*, u.fullname as petugas 
                        FROM survei_pasar s 
                        LEFT JOIN users u ON s.created_by = u.id 
                        WHERE s.id = ?";
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
        
    case 'delete':
        $id = $_POST['id'] ?? 0;
        
        if ($id > 0) {
            try {
                // Check if data exists
                $checkSql = "SELECT id FROM survei_pasar WHERE id = ?";
                $exists = fetchOne($checkSql, [$id]);
                
                if ($exists) {
                    $sql = "DELETE FROM survei_pasar WHERE id = ?";
                    $stmt = getDBConnection()->prepare($sql);
                    $result = $stmt->execute([$id]);
                    
                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = 'Data survei berhasil dihapus';
                    } else {
                        $response['message'] = 'Gagal menghapus data';
                    }
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
        
    case 'update':
        $id = $_POST['id'] ?? 0;
        $tanggal = $_POST['tanggal_survei'] ?? '';
        $lokasi = $_POST['lokasi_pasar'] ?? '';
        $komoditas = $_POST['komoditas'] ?? '';
        
        if ($id > 0 && !empty($tanggal) && !empty($lokasi) && !empty($komoditas)) {
            try {
                $updateData = [
                    'tanggal_survei' => $tanggal,
                    'lokasi_pasar' => $lokasi,
                    'komoditas' => $komoditas,
                    'nama_surveilens' => $_POST['nama_surveilens'] ?? '',
                    'nomor_hp' => $_POST['nomor_hp'] ?? '',
                    'harga_ayam_utuh' => floatval($_POST['harga_ayam_utuh'] ?? 0),
                    'harga_dada_ayam' => floatval($_POST['harga_dada_ayam'] ?? 0),
                    'harga_babi_utuh' => floatval($_POST['harga_babi_utuh'] ?? 0),
                    'harga_balung_babi' => floatval($_POST['harga_balung_babi'] ?? 0),
                    'harga_babi_isi' => floatval($_POST['harga_babi_isi'] ?? 0),
                    'harga_balung_sapi' => floatval($_POST['harga_balung_sapi'] ?? 0),
                    'harga_sapi_isi' => floatval($_POST['harga_sapi_isi'] ?? 0),
                    'catatan' => $_POST['catatan'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $sql = "UPDATE survei_pasar SET 
                        tanggal_survei = :tanggal_survei,
                        lokasi_pasar = :lokasi_pasar,
                        komoditas = :komoditas,
                        nama_surveilens = :nama_surveilens,
                        nomor_hp = :nomor_hp,
                        harga_ayam_utuh = :harga_ayam_utuh,
                        harga_dada_ayam = :harga_dada_ayam,
                        harga_babi_utuh = :harga_babi_utuh,
                        harga_balung_babi = :harga_balung_babi,
                        harga_babi_isi = :harga_babi_isi,
                        harga_balung_sapi = :harga_balung_sapi,
                        harga_sapi_isi = :harga_sapi_isi,
                        catatan = :catatan,
                        updated_at = :updated_at
                        WHERE id = :id";
                
                $updateData['id'] = $id;
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($updateData);
                
                if ($result) {
                    $response['success'] = true;
                    $response['message'] = 'Data berhasil diupdate';
                } else {
                    $response['message'] = 'Gagal mengupdate data';
                }
                
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Data tidak valid';
        }
        break;
        
    case 'get_chart_data':
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $komoditas = $_GET['komoditas'] ?? '';
        
        try {
            $sql = "SELECT 
                    DATE(tanggal_survei) as tanggal,
                    lokasi_pasar,
                    komoditas,
                    CASE 
                        WHEN komoditas = 'Daging Ayam' THEN COALESCE(harga_ayam_utuh, 0)
                        WHEN komoditas = 'Daging Babi' THEN COALESCE(harga_babi_utuh, 0)
                        WHEN komoditas = 'Daging Sapi' THEN COALESCE(harga_sapi_isi, 0)
                    END as harga
                    FROM survei_pasar 
                    WHERE tanggal_survei BETWEEN ? AND ?";
            
            $params = [$startDate, $endDate];
            
            if ($komoditas) {
                $sql .= " AND komoditas = ?";
                $params[] = $komoditas;
            }
            
            $sql .= " ORDER BY tanggal_survei, lokasi_pasar";
            
            $data = fetchAll($sql, $params);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_statistics':
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        try {
            $sql = "SELECT 
                    komoditas,
                    COUNT(*) as jumlah_survei,
                    ROUND(AVG(
                        CASE 
                            WHEN komoditas = 'Daging Ayam' THEN COALESCE(harga_ayam_utuh, 0)
                            WHEN komoditas = 'Daging Babi' THEN COALESCE(harga_babi_utuh, 0)
                            WHEN komoditas = 'Daging Sapi' THEN COALESCE(harga_sapi_isi, 0)
                        END
                    ), 0) as avg_harga,
                    MIN(
                        CASE 
                            WHEN komoditas = 'Daging Ayam' THEN COALESCE(harga_ayam_utuh, 0)
                            WHEN komoditas = 'Daging Babi' THEN COALESCE(harga_babi_utuh, 0)
                            WHEN komoditas = 'Daging Sapi' THEN COALESCE(harga_sapi_isi, 0)
                        END
                    ) as min_harga,
                    MAX(
                        CASE 
                            WHEN komoditas = 'Daging Ayam' THEN COALESCE(harga_ayam_utuh, 0)
                            WHEN komoditas = 'Daging Babi' THEN COALESCE(harga_babi_utuh, 0)
                            WHEN komoditas = 'Daging Sapi' THEN COALESCE(harga_sapi_isi, 0)
                        END
                    ) as max_harga
                    FROM survei_pasar 
                    WHERE tanggal_survei BETWEEN ? AND ?
                    GROUP BY komoditas";
            
            $data = fetchAll($sql, [$startDate, $endDate]);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    default:
        $response['message'] = 'Action tidak valid';
}

echo json_encode($response);
exit();