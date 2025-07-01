<?php
// /fleet-management/backend/controllers/AuthController.php

// Pastikan session hanya dimulai sekali
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        if (!$this->db) {
            // Gagal konek ke DB, hentikan proses.
            // Pesan error sudah ditangani di class Database.
            exit();
        }
        $this->user = new User($this->db);

        // Auto-login jika ada cookie token yang valid
        if (isset($_COOKIE['session_token']) && !isset($_SESSION['user_id'])) {
            if ($this->user->validateSession($_COOKIE['session_token'])) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['name'] = $this->user->name;
                $_SESSION['email'] = $this->user->email;
                $_SESSION['role'] = $this->user->role;
            } else {
                setcookie('session_token', '', time() - 3600, '/');
            }
        }
    }

    public function login($email, $password) {
        $this->user->email = $email;
        if($this->user->login($password)) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['name'] = $this->user->name;
            $_SESSION['email'] = $this->user->email;
            $_SESSION['role'] = $this->user->role;
            
            // Perbarui waktu login_time dan tambahkan IP address
            $login_time = date('Y-m-d H:i:s');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            
            // Pastikan session_token sudah dibuat di model login()
            if (empty($this->user->session_token)) {
                $this->user->session_token = bin2hex(random_bytes(32));
                // Update tbl_login or create new entry if not done in User::login
                // For simplicity, let's assume User::login handles the tbl_login entry.
                // If not, you might need to insert here:
                /*
                $query_update_login = "UPDATE tbl_login SET ip_address = :ip_address, login_time = :login_time WHERE user_id = :user_id AND session_token = :session_token";
                $stmt_update_login = $this->db->prepare($query_update_login);
                $stmt_update_login->bindParam(':ip_address', $ip_address);
                $stmt_update_login->bindParam(':login_time', $login_time);
                $stmt_update_login->bindParam(':user_id', $this->user->id);
                $stmt_update_login->bindParam(':session_token', $this->user->session_token);
                $stmt_update_login->execute();
                */
            }

            setcookie('session_token', $this->user->session_token, time() + (86400 * 30), "/"); // Cookie 30 hari

            return ['success' => true, 'role' => $this->user->role];
        }
        return ['success' => false, 'message' => 'Email atau password salah.'];
    }

    public function register($data) {
        // This register function in AuthController should ideally just call the User model's register/createUser method.
        // It seems your /backend/api/auth.php already handles passing data to user->register
        // This is primarily for the auth page registration, not admin user creation.
        $this->user->name = htmlspecialchars(strip_tags($data['name']));
        $this->user->email = htmlspecialchars(strip_tags($data['email']));
        $this->user->phone = htmlspecialchars(strip_tags($data['phone'] ?? ''));
        $this->user->role = $data['role'] ?? 'viewer'; // Default to viewer for self-registration

        if($this->user->register($data['password'])) {
            return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
        }
        return ['success' => false, 'message' => 'Gagal mendaftar. Email mungkin sudah digunakan.'];
    }

    public function logout() {
        if (isset($_COOKIE['session_token'])) {
            $this->user->deleteSession($_COOKIE['session_token']);
            setcookie('session_token', '', time() - 3600, '/');
        }
        session_unset();
        session_destroy();
        return ['success' => true];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /fleet-management/frontend/pages/auth/login.php');
            exit();
        }
    }

    public function mapRoleToDashboard($role) {
        switch ($role) {
            case 'admin':
            case 'manager':
                return '/fleet-management/frontend/pages/admin/dashboard.php';
            case 'operator':
            case 'viewer':
                return '/fleet-management/frontend/pages/employee/dashboard.php';
            default:
                return '/fleet-management/frontend/pages/auth/login.php';
        }
    }

    public function requireRole($required_roles) {
        $this->requireLogin();
        // Ensure required_roles is always an array for in_array check
        if (!is_array($required_roles)) {
            $required_roles = [$required_roles];
        }
        if (!in_array($_SESSION['role'], (array)$required_roles)) {
            // Jika tidak diizinkan, kembalikan ke dashboard mereka sendiri
            header('Location: ' . $this->mapRoleToDashboard($_SESSION['role']));
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireRole('admin');
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['name'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
}