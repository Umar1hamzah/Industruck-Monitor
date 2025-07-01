<?php
// /fleet-management/backend/api/dashboard.php
header('Content-Type: application/json');
require_once '../controllers/AuthController.php';
require_once '../models/Report.php';
require_once '../config/database.php';

$auth = new AuthController();
$auth->requireRole(['admin', 'manager']);

$database = new Database();
$db = $database->getConnection();
$reportModel = new Report($db);

$cache_file = __DIR__ . '/../cache/dashboard_cache.json';
$cache_time = 30; // Waktu cache dalam detik

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Cek cache
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        echo file_get_contents($cache_file);
        exit();
    }

    try {
        // Mengambil semua data dashboard dalam satu panggilan
        $dashboard_data = $reportModel->getDashboardSummary();

        if ($dashboard_data === false) {
            throw new Exception("Gagal mengambil data summary dashboard.");
        }

        $response_data = [
            'success' => true,
            'data' => $dashboard_data
        ];
        
        $json_response = json_encode($response_data);

        // Simpan ke cache
        file_put_contents($cache_file, $json_response);

        echo $json_response;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan internal saat mengambil data dashboard.', 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
}
?>