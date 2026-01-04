<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'get_group_details':
        $tanggal = $_GET['tanggal'] ?? '';
        $kecamatan = $_GET['kecamatan'] ?? '';
        
        if ($tanggal && $kecamatan) {
            try {
                $kecamatanId = getKecamatanId($kecamatan);
                
                $sql = "SELECT p.* FROM pemotongan p
                        WHERE p.tanggal_pemotongan = ? AND p.kecamatan_id = ?
                        ORDER BY p.jenis_hewan";
                
                $data = fetchAll($sql, [$tanggal, $kecamatanId]);
                
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
            $response['message'] = 'Parameter tidak lengkap';
        }
        break;
        
    case 'delete_group':
        $tanggal = $_GET['tanggal'] ?? '';
        $kecamatan = $_GET['kecamatan'] ?? '';
        
        if ($tanggal && $kecamatan) {
            try {
                $kecamatanId = getKecamatanId($kecamatan);
                
                $sql = "DELETE FROM pemotongan 
                        WHERE tanggal_pemotongan = ? AND kecamatan_id = ?";
                
                $result = deleteData($sql, [$tanggal, $kecamatanId]);
                
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
            $response['message'] = 'Parameter tidak lengkap';
        }
        break;
        
    case 'get_statistics':
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? '';
        
        try {
            $sql = "SELECT 
                    DATE_FORMAT(tanggal_pemotongan, '%Y-%m') as bulan,
                    SUM(total) as total_pemotongan,
                    SUM(jantan) as total_jantan,
                    SUM(betina) as total_betina
                    FROM pemotongan 
                    WHERE YEAR(tanggal_pemotongan) = ?";
            
            $params = [$year];
            
            if ($month) {
                $sql .= " AND MONTH(tanggal_pemotongan) = ?";
                $params[] = $month;
            }
            
            $sql .= " GROUP BY DATE_FORMAT(tanggal_pemotongan, '%Y-%m')
                     ORDER BY bulan";
            
            $data = fetchAll($sql, $params);
            
            // Get top kecamatan
            $topSql = "SELECT k.nama_kecamatan, SUM(p.total) as total
                      FROM pemotongan p
                      JOIN kecamatan k ON p.kecamatan_id = k.id
                      WHERE YEAR(p.tanggal_pemotongan) = ?
                      GROUP BY k.nama_kecamatan
                      ORDER BY total DESC
                      LIMIT 5";
            
            $topKecamatan = fetchAll($topSql, [$year]);
            
            $response['success'] = true;
            $response['monthly_data'] = $data;
            $response['top_kecamatan'] = $topKecamatan;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'generate_report':
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $kecamatan = $_GET['kecamatan'] ?? '';
        
        try {
            // Generate HTML report
            ob_start();
            include __DIR__ . '/report_template.php';
            $html = ob_get_clean();
            
            $response['success'] = true;
            $response['html'] = $html;
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    default:
        $response['message'] = 'Action tidak valid';
}

echo json_encode($response);
exit();