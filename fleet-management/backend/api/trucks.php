<?php
// /fleet-management/backend/api/trucks.php
header('Content-Type: application/json');
require_once '../controllers/AuthController.php';
require_once '../models/Truck.php';
require_once '../config/database.php'; // Added Database include

$auth = new AuthController();
$auth->requireLogin();

$database = new Database();
$db = $database->getConnection();
$truckModel = new Truck($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $trucks = $truckModel->getTruckData($limit, $offset);
    $total_trucks = $truckModel->getTotalTruckCount();

    echo json_encode([
        'success' => true,
        'trucks' => $trucks,
        'total_trucks' => $total_trucks,
        'page' => $page,
        'limit' => $limit
    ]);
} elseif ($method === 'POST') {
    $auth->requireAdmin(); // Only admin can add/delete trucks
    $action = $_POST['action'] ?? null;
   
    if ($action === 'add') {
        // Ensure necessary data is present. supir_id is now optional (can be null).
        if (isset($_POST['no_polisi'], $_POST['status'])) {
            if ($truckModel->addTruck($_POST)) {
                echo json_encode(['success' => true, 'message' => 'Truk berhasil ditambahkan.', 'refresh' => true]);
            } else {
                http_response_code(409); // Conflict, e.g., duplicate no_polisi
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan truk. No. Polisi mungkin sudah ada atau data tidak valid.']);
            }
        } else {
             http_response_code(400); // Bad Request
             echo json_encode(['success' => false, 'message' => 'Data tidak lengkap (Nomor Polisi dan Status wajib).']);
        }
    } elseif ($action === 'delete') {
        if (isset($_POST['id']) && $truckModel->deleteTruck($_POST['id'])) {
            echo json_encode(['success' => true, 'message' => 'Truk berhasil dihapus.', 'refresh' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus truk.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
}
?>