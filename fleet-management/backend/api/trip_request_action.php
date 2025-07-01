<?php
// /fleet-management/backend/api/trip_request_action.php
header('Content-Type: application/json');
require_once '../controllers/AuthController.php';
require_once '../models/Trip.php'; // Model baru untuk mengelola perjalanan
require_once '../config/database.php';

$auth = new AuthController();
$auth->requireRole(['admin', 'manager']);
$currentUser = $auth->getCurrentUser();

$database = new Database();
$db = $database->getConnection();
$tripModel = new Trip($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$request_id || !$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Request ID dan Aksi dibutuhkan.']);
        exit;
    }

    try {
        $result = false;
        if ($action === 'approve') {
            $result = $tripModel->approveTripRequest($request_id, $currentUser['id']);
            $message = 'Permintaan perjalanan berhasil disetujui.';
        } elseif ($action === 'reject') {
            $result = $tripModel->rejectTripRequest($request_id, $currentUser['id']);
            $message = 'Permintaan perjalanan ditolak.';
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
            exit;
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal memproses permintaan.']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan internal.', 'error' => $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
}
?>