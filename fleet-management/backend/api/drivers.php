<?php
header('Content-Type: application/json');
require_once '../controllers/AuthController.php';
require_once '../models/Driver.php';
require_once '../config/database.php'; // Make sure Database is included

$auth = new AuthController();
$auth->requireRole(['admin', 'manager']);

$database = new Database();
$db = $database->getConnection();
$driverModel = new Driver($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'get_all_for_select') { // New action to get all drivers for select
        $drivers = $driverModel->getAllSimple(); // Create this new method in Driver.php
        if ($drivers !== false) {
            echo json_encode(['success' => true, 'drivers' => $drivers]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil data supir untuk select.']);
        }
    } elseif ($action === 'get_single') {
        if (isset($_GET['id'])) {
            $driver = $driverModel->getSingle($_GET['id']); // Implement getSingle in Driver.php
            if ($driver) {
                echo json_encode(['success' => true, 'driver' => $driver]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Supir tidak ditemukan.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID Supir dibutuhkan.']);
        }
    } elseif ($action === 'get_available_trucks') { // Action to get available trucks for driver modal
        $driver_id = $_GET['driver_id'] ?? null;
        $trucks = $driverModel->getAvailableTrucks($driver_id);
        if ($trucks !== false) {
            echo json_encode(['success' => true, 'trucks' => $trucks]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil truk yang tersedia.']);
        }
    } else { // Original GET logic for drivers.php table
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $drivers = $driverModel->getAll($limit, $offset);
        $total_drivers = $driverModel->getTotalDriverCount();

        if ($drivers !== false) {
            echo json_encode([
                'success' => true,
                'drivers' => $drivers,
                'total_drivers' => $total_drivers,
                'page' => $page,
                'limit' => $limit
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil data supir.']);
        }
    }
}
elseif ($method === 'POST') {
    switch($action) {
        case 'add':
            if ($driverModel->add($_POST)) {
                echo json_encode(['success' => true, 'message' => 'Supir berhasil ditambahkan.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan supir. Pastikan No. KTP unik.']);
            }
            break;
        case 'update': // New action for updating a driver
            if (isset($_POST['id']) && $driverModel->update($_POST)) { // Implement update in Driver.php
                echo json_encode(['success' => true, 'message' => 'Data supir berhasil diperbarui.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data supir.']);
            }
            break;
        case 'delete':
            if (isset($_POST['id']) && $driverModel->delete($_POST['id'])) {
                echo json_encode(['success' => true, 'message' => 'Supir berhasil dihapus.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus supir.']);
            }
            break;

        case 'assign_truck': // This action is currently not used directly from the driver modal but kept
            if (isset($_POST['id'], $_POST['truck_id']) && $driverModel->assignTruck($_POST['id'], $_POST['truck_id'])) {
                echo json_encode(['success' => true, 'message' => 'Penugasan truk berhasil diperbarui.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menugaskan truk.']);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
            break;
    }
} else {
   http_response_code(405); // Method Not Allowed
   echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
}
?>