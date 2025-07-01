<?php
// /fleet-management/backend/config/database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'fleet_management';
    private $username = 'root'; // Ganti dengan username database Anda
    private $password = '';      // Ganti dengan password database Anda
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Jangan tampilkan error detail di produksi
            // Cukup log error dan tampilkan pesan umum
            error_log("Connection error: " . $exception->getMessage());
            // Hentikan eksekusi jika koneksi gagal
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit();
        }
        return $this->conn;
    }
}
?>