<?php
// /fleet-management/backend/api/auth.php

// Set header ke JSON
header('Content-Type: application/json');

require_once '../controllers/AuthController.php';

$auth = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
   if (isset($_POST['action'])) {
       switch ($_POST['action']) {
           case 'login':
               if (isset($_POST['email'], $_POST['password'])) {
                   $result = $auth->login($_POST['email'], $_POST['password']);
                   if ($result['success']) {
                       $redirect_url = $auth->mapRoleToDashboard($result['role']);
                       echo json_encode(['success' => true, 'message' => 'Login berhasil!', 'redirect' => $redirect_url]);
                   } else {
                       http_response_code(401); // Unauthorized
                       echo json_encode(['success' => false, 'message' => $result['message']]);
                   }
               } else {
                   http_response_code(400); // Bad Request
                   echo json_encode(['success' => false, 'message' => 'Email dan password dibutuhkan.']);
               }
               break;
          
           case 'register':
               if (isset($_POST['name'], $_POST['email'], $_POST['password'])) {
                   $data = [
                       'name' => $_POST['name'],
                       'email' => $_POST['email'],
                       'password' => $_POST['password'],
                       'phone' => $_POST['phone'] ?? null,
                       'role' => $_POST['role'] ?? 'viewer'
                   ];
                   $result = $auth->register($data);
                   if ($result['success']) {
                       echo json_encode(['success' => true, 'message' => $result['message'], 'redirect' => '/fleet-management/frontend/pages/auth/login.php']);
                   } else {
                       http_response_code(409); // Conflict
                       echo json_encode(['success' => false, 'message' => $result['message']]);
                   }
               } else {
                   http_response_code(400); // Bad Request
                   echo json_encode(['success' => false, 'message' => 'Data registrasi tidak lengkap.']);
               }
               break;

           default:
               http_response_code(400);
               echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
               break;
       }
   } else {
       http_response_code(400);
       echo json_encode(['success' => false, 'message' => 'Aksi tidak ditentukan.']);
   }
} elseif ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
   $auth->logout();
   echo json_encode(['success' => true, 'message' => 'Anda telah logout.', 'redirect' => '/fleet-management/frontend/pages/auth/login.php']);
} else {
   http_response_code(405); // Method Not Allowed
   echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
}
?>