<?php
// /fleet-management/backend/models/User.php

class User {
    private $conn;
    private $table_user = "tbl_user";
    private $table_login = "tbl_login";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $role;
    public $session_token;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($password_input) {
        $query = "SELECT id, name, email, role, password FROM " . $this->table_user . " WHERE email = :email LIMIT 1";
    
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if($row && password_verify($password_input, $row['password'])) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
    
            $this->session_token = bin2hex(random_bytes(32));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
            // Insert or update tbl_login for the new session
            $query_login = "INSERT INTO tbl_login (user_id, session_token, ip_address, expire_time)
                            VALUES (:user_id, :session_token, :ip_address, DATE_ADD(NOW(), INTERVAL 30 DAY))
                            ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), ip_address = VALUES(ip_address), login_time = NOW(), expire_time = VALUES(expire_time)";
            
            $stmt_login = $this->conn->prepare($query_login);
    
            $stmt_login->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt_login->bindParam(':session_token', $this->session_token);
            $stmt_login->bindParam(':ip_address', $ip_address);
    
            $stmt_login->execute();
            
            return true;
        }
    
        return false;
    }

    public function register($password_input) {
        // This is for self-registration via /auth/register.php
        // Checks if email already exists
        $check_query = "SELECT id FROM " . $this->table_user . " WHERE email = :email LIMIT 1";
        $stmt_check = $this->conn->prepare($check_query);
        $stmt_check->bindParam(":email", $this->email);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
            return false; // Email already in use
        }

        $query = "INSERT INTO " . $this->table_user . " SET name=:name, email=:email, password=:password, phone=:phone, role=:role";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User registration error: " . $e->getMessage());
            return false;
        }
    }

    // New method for admin to create users (different from self-registration)
    public function createUser($data) {
        // Checks if email already exists
        $check_query = "SELECT id FROM " . $this->table_user . " WHERE email = :email LIMIT 1";
        $stmt_check = $this->conn->prepare($check_query);
        $email = htmlspecialchars(strip_tags($data['email']));
        $stmt_check->bindParam(":email", $email);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $query = "INSERT INTO " . $this->table_user . " SET name=:name, email=:email, password=:password, phone=:phone, role=:role";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $phone = htmlspecialchars(strip_tags($data['phone'] ?? ''));
        $role = htmlspecialchars(strip_tags($data['role'] ?? 'viewer'));
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the provided password

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":role", $role);

        try {
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Pengguna baru berhasil ditambahkan.'];
            }
            return ['success' => false, 'message' => 'Gagal menambahkan pengguna.'];
        } catch (PDOException $e) {
            error_log("Admin create user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menambahkan pengguna.'];
        }
    }
    
    public function validateSession($token) {
        $query = "SELECT u.id, u.name, u.email, u.role FROM " . $this->table_login . " l JOIN " . $this->table_user . " u ON l.user_id = u.id WHERE l.session_token = :token AND l.expire_time > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
    
    public function deleteSession($token) {
        $query = "DELETE FROM " . $this->table_login . " WHERE session_token = :token";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User session deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers($limit = 10, $offset = 0) {
        $query = "SELECT id, name, email, phone, role, created_at FROM " . $this->table_user . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (User::getAllUsers): " . $e->getMessage());
            return false;
        }
    }

    public function getTotalUserCount() {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_user;
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total_count'];
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (User::getTotalUserCount): " . $e->getMessage());
            return 0;
        }
    }
    
    public function deleteUser($id) {
        // Prevent deleting user if they have active sessions (optional, but good for data integrity)
        // Or simply delete associated sessions first
        try {
            $this->conn->beginTransaction();

            // Delete associated login sessions
            $query_delete_sessions = "DELETE FROM " . $this->table_login . " WHERE user_id = :user_id";
            $stmt_delete_sessions = $this->conn->prepare($query_delete_sessions);
            $stmt_delete_sessions->bindParam(':user_id', $id, PDO::PARAM_INT);
            $stmt_delete_sessions->execute();

            // Then delete the user
            $query = "DELETE FROM " . $this->table_user . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("DATABASE ERROR (User::deleteUser): " . $e->getMessage());
            return false;
        }
    }
}