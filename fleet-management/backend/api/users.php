<?php
// /fleet-management/backend/api/users.php

header('Content-Type: application/json');

require_once '../controllers/AuthController.php';
require_once '../models/User.php';
require_once '../config/database.php'; // Added Database include

$auth = new AuthController();
$auth->requireAdmin(); // Hanya admin yang bisa akses API ini

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $users = $userModel->getAllUsers($limit, $offset);
        $total_users = $userModel->getTotalUserCount();

        echo json_encode([
            'success' => true,
            'users' => $users,
            'total_users' => $total_users,
            'page' => $page,
            'limit' => $limit
        ]);
        break;

    case 'POST': // Menggunakan POST untuk delete agar lebih simpel
        $action = $_POST['action'] ?? null;
        if ($action === 'delete') {
            if (isset($_POST['id'])) {
                if ($_POST['id'] == $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun Anda sendiri.']);
                    exit();
                }
                if ($userModel->deleteUser($_POST['id'])) {
                    echo json_encode(['success' => true, 'message' => 'User berhasil dihapus.', 'refresh' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus user.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID user tidak ditemukan.']);
            }
        } elseif ($action === 'register') { // Added register action for user management
            if (isset($_POST['name'], $_POST['email'], $_POST['password'])) {
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'phone' => $_POST['phone'] ?? null,
                    'role' => $_POST['role'] ?? 'viewer' // Default to viewer
                ];
                $result = $userModel->createUser($data); // New method in User.php
                if ($result['success']) {
                    echo json_encode(['success' => true, 'message' => $result['message'], 'refresh' => true]);
                } else {
                    http_response_code(409); // Conflict
                    echo json_encode(['success' => false, 'message' => $result['message']]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Data registrasi tidak lengkap.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
        break;
}
?>