<?php
// modules/penyakit_hewan/process.php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => null];

switch ($action) {
    case 'get_data':
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            try {
                $sql = "SELECT p.*, u.fullname as petugas 
                        FROM penyakit_hewan p 
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
        
    case 'delete':
        $id = $_POST['id'] ?? 0;
        
        if ($id > 0) {
            try {
                $checkSql = "SELECT id FROM penyakit_hewan WHERE id = ?";
                $exists = fetchOne($checkSql, [$id]);
                
                if ($exists) {
                    $sql = "DELETE FROM penyakit_hewan WHERE id = ?";
                    $stmt = getDBConnection()->prepare($sql);
                    $result = $stmt->execute([$id]);
                    
                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = 'Data penyakit berhasil dihapus';
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
        
    case 'get_statistics':
        $bulan = $_GET['bulan'] ?? date('Y-m');
        
        try {
            // Overall statistics
            $sql = "SELECT 
                    COUNT(*) as total_kasus,
                    SUM(kasus_digital) as total_digital,
                    SUM(sampel_positif) as total_positif,
                    SUM(sampel_negatif) as total_negatif,
                    SUM(total_sampel) as total_sampel
                   FROM penyakit_hewan 
                   WHERE bulan = ?";
            $overall = fetchOne($sql, [$bulan]);
            
            // Statistics by animal type
            $byAnimalSql = "SELECT 
                           jenis_ternak,
                           COUNT(*) as jumlah_kasus,
                           SUM(kasus_digital) as kasus_digital,
                           SUM(sampel_positif) as sampel_positif,
                           SUM(sampel_negatif) as sampel_negatif
                          FROM penyakit_hewan 
                          WHERE bulan = ?
                          GROUP BY jenis_ternak";
            $byAnimal = fetchAll($byAnimalSql, [$bulan]);
            
            // Statistics by disease
            $byDiseaseSql = "SELECT 
                            jenis_penyakit,
                            COUNT(*) as jumlah_kasus,
                            SUM(kasus_digital) as kasus_digital
                           FROM penyakit_hewan 
                           WHERE bulan = ?
                           GROUP BY jenis_penyakit
                           ORDER BY jumlah_kasus DESC
                           LIMIT 10";
            $byDisease = fetchAll($byDiseaseSql, [$bulan]);
            
            $response['success'] = true;
            $response['data'] = [
                'overall' => $overall,
                'by_animal' => $byAnimal,
                'by_disease' => $byDisease
            ];
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_monthly_trend':
        $startMonth = $_GET['start_month'] ?? date('Y-m', strtotime('-5 months'));
        $endMonth = $_GET['end_month'] ?? date('Y-m');
        $jenis_ternak = $_GET['jenis_ternak'] ?? '';
        
        try {
            $sql = "SELECT 
                    bulan,
                    jenis_ternak,
                    SUM(kasus_digital) as total_digital,
                    SUM(sampel_positif) as total_positif,
                    SUM(total_sampel) as total_sampel
                   FROM penyakit_hewan 
                   WHERE bulan BETWEEN ? AND ?";
            
            $params = [$startMonth, $endMonth];
            
            if ($jenis_ternak) {
                $sql .= " AND jenis_ternak = ?";
                $params[] = $jenis_ternak;
            }
            
            $sql .= " GROUP BY bulan, jenis_ternak 
                     ORDER BY bulan, jenis_ternak";
            
            $data = fetchAll($sql, $params);
            
            $response['success'] = true;
            $response['data'] = $data;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'check_duplicate':
        $jenis_ternak = $_POST['jenis_ternak'] ?? '';
        $bulan = $_POST['bulan'] ?? '';
        $minggu_ke = $_POST['minggu_ke'] ?? null;
        $jenis_penyakit = $_POST['jenis_penyakit'] ?? '';
        $exclude_id = $_POST['exclude_id'] ?? 0;
        
        if ($jenis_ternak && $bulan && $jenis_penyakit) {
            try {
                $sql = "SELECT id FROM penyakit_hewan 
                       WHERE jenis_ternak = ? 
                       AND bulan = ? 
                       AND jenis_penyakit = ?";
                $params = [$jenis_ternak, $bulan, $jenis_penyakit];
                
                if ($minggu_ke) {
                    $sql .= " AND minggu_ke = ?";
                    $params[] = $minggu_ke;
                } else {
                    $sql .= " AND minggu_ke IS NULL";
                }
                
                if ($exclude_id > 0) {
                    $sql .= " AND id != ?";
                    $params[] = $exclude_id;
                }
                
                $exists = fetchOne($sql, $params);
                
                $response['success'] = true;
                $response['data'] = ['exists' => !!$exists];
                
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Parameter tidak lengkap';
        }
        break;
        
    default:
        $response['message'] = 'Action tidak valid';
}

echo json_encode($response);
exit();