<?php
class Driver {
    private $conn;
    private $table_supir = "tbl_supir";
    private $table_kendaraan = "tbl_kendaraan";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT
                    s.id, s.nama, s.phone, s.wa_number, s.no_ktp, s.no_sim, s.status, s.alamat,
                    k.id as assigned_truck_id, k.no_polisi as assigned_truck
                 FROM
                    " . $this->table_supir . " s
                 LEFT JOIN
                    " . $this->table_kendaraan . " k ON s.id = k.supir_id
                 ORDER BY s.nama ASC
                 LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Driver::getAll): " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalDriverCount() {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_supir;
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total_count'];
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Driver::getTotalDriverCount): " . $e->getMessage());
            return 0;
        }
    }
    
    // New method to get simple list of drivers (ID and Name) for dropdowns
    public function getAllSimple() {
        $query = "SELECT id, nama FROM " . $this->table_supir . " ORDER BY nama ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Driver::getAllSimple): " . $e->getMessage());
            return false;
        }
    }

    // New method to get a single driver by ID
    public function getSingle($id) {
        $query = "SELECT
                    s.id, s.nama, s.phone, s.wa_number, s.no_ktp, s.no_sim, s.status, s.alamat,
                    k.id as assigned_truck_id, k.no_polisi as assigned_truck
                 FROM
                    " . $this->table_supir . " s
                 LEFT JOIN
                    " . $this->table_kendaraan . " k ON s.id = k.supir_id
                 WHERE
                    s.id = :id
                 LIMIT 0,1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Driver::getSingle): " . $e->getMessage());
            return false;
        }
    }

    public function add($data) {
        $query = "INSERT INTO " . $this->table_supir . "
                    SET nama=:nama, no_ktp=:no_ktp, no_sim=:no_sim, phone=:phone, wa_number=:wa_number, alamat=:alamat, status=:status";
        $stmt = $this->conn->prepare($query);

        $nama = htmlspecialchars(strip_tags($data['nama']));
        $no_ktp = htmlspecialchars(strip_tags($data['no_ktp']));
        $no_sim = htmlspecialchars(strip_tags($data['no_sim']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $wa_number = htmlspecialchars(strip_tags($data['wa_number'] ?? ''));
        $alamat = htmlspecialchars(strip_tags($data['alamat'] ?? ''));
        $status = htmlspecialchars(strip_tags($data['status']));

        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':no_ktp', $no_ktp);
        $stmt->bindParam(':no_sim', $no_sim);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':wa_number', $wa_number);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':status', $status);
        
        try {
            if ($stmt->execute()) {
                $driver_id = $this->conn->lastInsertId();
                // Tugaskan truk jika dipilih (this happens via assignTruck API call, or during update)
                // This 'add' function is mostly for creating the driver, assignment can be separate.
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Check for duplicate entry error code (e.g., SQLSTATE 23000 for unique constraint violation)
            if ($e->getCode() == '23000') {
                error_log("DATABASE ERROR: Duplicate entry for driver No. KTP or No. SIM. " . $e->getMessage());
                // You might want to throw an exception or return a specific error code
            } else {
                error_log("DATABASE ERROR (Driver::add): " . $e->getMessage());
            }
            return false;
        }
    }

    // New method to update driver data
    public function update($data) {
        $query = "UPDATE " . $this->table_supir . "
                    SET nama=:nama, no_ktp=:no_ktp, no_sim=:no_sim, phone=:phone, wa_number=:wa_number, alamat=:alamat, status=:status
                    WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($data['id']));
        $nama = htmlspecialchars(strip_tags($data['nama']));
        $no_ktp = htmlspecialchars(strip_tags($data['no_ktp']));
        $no_sim = htmlspecialchars(strip_tags($data['no_sim']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $wa_number = htmlspecialchars(strip_tags($data['wa_number'] ?? ''));
        $alamat = htmlspecialchars(strip_tags($data['alamat'] ?? ''));
        $status = htmlspecialchars(strip_tags($data['status']));

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':no_ktp', $no_ktp);
        $stmt->bindParam(':no_sim', $no_sim);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':wa_number', $wa_number);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':status', $status);
        
        try {
            $this->conn->beginTransaction();
            if ($stmt->execute()) {
                // Handle truck assignment/unassignment here as part of driver update
                $truck_id = ($data['truck_id'] === 'none' || empty($data['truck_id'])) ? null : (int)$data['truck_id'];
                $this->assignTruck($id, $truck_id); // Re-assign or unassign truck
                $this->conn->commit();
                return true;
            }
            $this->conn->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("DATABASE ERROR (Driver::update): " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();
            // Unassign any truck currently assigned to this driver
            $query_unassign = "UPDATE " . $this->table_kendaraan . " SET supir_id = NULL WHERE supir_id = :supir_id";
            $stmt_unassign = $this->conn->prepare($query_unassign);
            $stmt_unassign->bindParam(':supir_id', $id, PDO::PARAM_INT);
            $stmt_unassign->execute();

            $query_delete = "DELETE FROM " . $this->table_supir . " WHERE id = :id";
            $stmt_delete = $this->conn->prepare($query_delete);
            $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_delete->execute();
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("DATABASE ERROR (Driver::delete): " . $e->getMessage());
            return false;
        }
    }
    
    public function getAvailableTrucks($driver_id = null) {
        // This function should return trucks that are NULL for supir_id
        // OR trucks that are currently assigned to the given $driver_id.
        $query = "SELECT id, no_polisi FROM " . $this->table_kendaraan . " WHERE supir_id IS NULL";
        if ($driver_id) {
            $query .= " OR supir_id = :driver_id";
        }
        $query .= " ORDER BY no_polisi ASC";

        try {
            $stmt = $this->conn->prepare($query);
            if ($driver_id) {
                $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Driver::getAvailableTrucks): " . $e->getMessage());
            return false;
        }
    }
    
    public function assignTruck($driver_id, $truck_id) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Unassign the truck from ANY driver if it's currently assigned to someone else
            if ($truck_id !== null) { // Only if a specific truck is selected
                $stmt_unassign_other = $this->conn->prepare("UPDATE " . $this->table_kendaraan . " SET supir_id = NULL WHERE id = :truck_id AND supir_id IS NOT NULL AND supir_id != :driver_id");
                $stmt_unassign_other->bindParam(':truck_id', $truck_id, PDO::PARAM_INT);
                $stmt_unassign_other->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                $stmt_unassign_other->execute();
            }

            // 2. Unassign any truck currently assigned to THIS driver
            $stmt_unassign_current = $this->conn->prepare("UPDATE " . $this->table_kendaraan . " SET supir_id = NULL WHERE supir_id = :driver_id");
            $stmt_unassign_current->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
            $stmt_unassign_current->execute();
            
            // 3. Assign the selected truck to this driver (if a truck was selected)
            if ($truck_id !== null) {
                $stmt_assign = $this->conn->prepare("UPDATE " . $this->table_kendaraan . " SET supir_id = :driver_id WHERE id = :truck_id");
                $stmt_assign->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                $stmt_assign->bindParam(':truck_id', $truck_id, PDO::PARAM_INT);
                $stmt_assign->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("DATABASE ERROR (Driver::assignTruck): " . $e->getMessage());
            return false;
        }
    }
}