<?php
// /fleet-management/backend/models/Trip.php

class Trip {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Menyetujui permintaan perjalanan.
     * Ini adalah proses multi-langkah yang dibungkus dalam transaksi database.
     */
    public function approveTripRequest($request_id, $manager_id) {
        // Ambil detail permintaan
        $query_req = "SELECT * FROM tbl_trip_requests WHERE id = :id AND status = 'pending'";
        $stmt_req = $this->conn->prepare($query_req);
        $stmt_req->bindParam(':id', $request_id, PDO::PARAM_INT);
        $stmt_req->execute();
        $request = $stmt_req->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            throw new Exception("Permintaan tidak ditemukan atau sudah diproses.");
        }

        $this->conn->beginTransaction();

        try {
            // 1. Update status permintaan perjalanan
            $query1 = "UPDATE tbl_trip_requests SET status = 'approved', direspons_oleh = :manager_id, waktu_respons = NOW() WHERE id = :request_id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
            $stmt1->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt1->execute();

            // 2. Buat entri baru di tabel perjalanan utama
            $query2 = "INSERT INTO tbl_perjalanan (request_id, kendaraan_id, supir_id, alamat_tujuan, status, waktu_mulai) VALUES (:request_id, :kendaraan_id, :supir_id, :alamat_tujuan, 'ongoing', NOW())";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt2->bindParam(':kendaraan_id', $request['kendaraan_id'], PDO::PARAM_INT);
            $stmt2->bindParam(':supir_id', $request['supir_id'], PDO::PARAM_INT);
            $stmt2->bindParam(':alamat_tujuan', $request['usulan_tujuan']);
            $stmt2->execute();

            // 3. Update status kendaraan menjadi 'bergerak'
            $query3 = "UPDATE tbl_kendaraan SET status = 'bergerak' WHERE id = :kendaraan_id";
            $stmt3 = $this->conn->prepare($query3);
            $stmt3->bindParam(':kendaraan_id', $request['kendaraan_id'], PDO::PARAM_INT);
            $stmt3->execute();

            // 4. Update status supir menjadi 'on_trip'
            $query4 = "UPDATE tbl_supir SET status = 'on_trip' WHERE id = :supir_id";
            $stmt4 = $this->conn->prepare($query4);
            $stmt4->bindParam(':supir_id', $request['supir_id'], PDO::PARAM_INT);
            $stmt4->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Approve Trip Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Menolak permintaan perjalanan.
     */
    public function rejectTripRequest($request_id, $manager_id) {
        $query = "UPDATE tbl_trip_requests SET status = 'rejected', direspons_oleh = :manager_id, waktu_respons = NOW() WHERE id = :request_id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $stmt->rowCount() > 0; // Berhasil jika ada baris yang terpengaruh
        }
        return false;
    }
}
?>