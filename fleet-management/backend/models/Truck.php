<?php
// /fleet-management/backend/models/Truck.php
class Truck {
    private $conn;
    private $table_name = "tbl_kendaraan";

    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Mengambil semua data truk dengan nama supir dengan pagination
    public function getTruckData($limit = 10, $offset = 0) {
        $query = "SELECT k.id, k.no_polisi, k.merk, k.model, k.tahun, k.status, k.kecepatan, k.tujuan, k.latitude, k.longitude, s.nama AS supir
                    FROM " . $this->table_name . " k
                    LEFT JOIN tbl_supir s ON k.supir_id = s.id
                    ORDER BY k.updated_at DESC
                    LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Truck::getTruckData): " . $e->getMessage());
            return false;
        }
    }

    // Mengambil total jumlah truk
    public function getTotalTruckCount() {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_name;
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total_count'];
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Truck::getTotalTruckCount): " . $e->getMessage());
            return 0;
        }
    }
    
    // Menghitung statistik truk berdasarkan status
    public function getStats() {
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = ['total' => 0, 'bergerak' => 0, 'idle' => 0, 'maintenance' => 0, 'pelanggaran' => 0];
            foreach ($results as $row) {
                if (array_key_exists($row['status'], $stats)) {
                    $stats[$row['status']] = (int)$row['count'];
                }
                $stats['total'] += (int)$row['count'];
            }
            return $stats;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Truck::getStats): " . $e->getMessage());
            return false;
        }
    }

    // Mengambil 5 aktivitas truk terakhir
    public function getRecentActivity() {
        $query = "SELECT no_polisi, status, tujuan, updated_at
                    FROM " . $this->table_name . "
                    ORDER BY updated_at DESC
                    LIMIT 5";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Truck::getRecentActivity): " . $e->getMessage());
            return false;
        }
    }
    
    // Menambah truk baru
    public function addTruck($data) {
        $query = "INSERT INTO " . $this->table_name . " (no_polisi, merk, model, tahun, status, tujuan, kecepatan, supir_id)
                    VALUES (:no_polisi, :merk, :model, :tahun, :status, :tujuan, :kecepatan, :supir_id)";
        $stmt = $this->conn->prepare($query);
        
        $merk = htmlspecialchars(strip_tags($data['merk'] ?? 'N/A'));
        $model = htmlspecialchars(strip_tags($data['model'] ?? 'N/A'));
        $tahun = htmlspecialchars(strip_tags($data['tahun'] ?? 2024));
        // Use null for supir_id if 'none' or empty, otherwise cast to int
        $supir_id = (isset($data['supir_id']) && $data['supir_id'] !== 'none' && !empty($data['supir_id'])) ? (int)$data['supir_id'] : null;

        $stmt->bindParam(':no_polisi', $data['no_polisi']);
        $stmt->bindParam(':merk', $merk);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':tujuan', $data['tujuan']);
        $stmt->bindParam(':kecepatan', $data['kecepatan'], PDO::PARAM_STR); // Changed to STR for decimal, or use PDO::PARAM_INT if it's always int
        $stmt->bindParam(':supir_id', $supir_id, PDO::PARAM_INT); // Bind as INT or NULL

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add Truck Error: " . $e->getMessage()); // Log the error
            return false;
        }
    }
    
    // Menghapus truk
    public function deleteTruck($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Truck::deleteTruck): " . $e->getMessage());
            return false;
        }
    }
}