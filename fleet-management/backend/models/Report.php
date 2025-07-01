<?php
// /fleet-management/backend/models/Report.php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Mengambil data KPI (Key Performance Indicators) utama.
     */
    public function getKpiSummary() {
        $start_time = microtime(true);
        // Query ini lebih efisien karena menghindari subquery yang tidak perlu dan menggunakan range scan untuk tanggal.
        // Pastikan ada index pada tbl_kendaraan(status) dan tbl_pelanggaran(waktu_kejadian, status).
        $query = "
            SELECT
                COUNT(k.id) AS total_trucks,
                SUM(CASE WHEN k.status = 'bergerak' THEN 1 ELSE 0 END) AS on_trip_trucks,
                SUM(CASE WHEN k.status = 'idle' THEN 1 ELSE 0 END) AS idle_trucks,
                SUM(CASE WHEN k.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_trucks,
                (SELECT COUNT(*) FROM tbl_pelanggaran WHERE status = 'new' AND waktu_kejadian >= CURDATE() AND waktu_kejadian < CURDATE() + INTERVAL 1 DAY) AS violations_today
            FROM tbl_kendaraan k
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getKpiSummary executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getKpiSummary): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil lokasi, status, dan info dasar semua truk untuk ditampilkan di peta.
     */
    public function getAllTruckLocations() {
        $start_time = microtime(true);
        $query = "
            SELECT 
                k.id, k.no_polisi, k.status, k.latitude, k.longitude, k.kecepatan, 
                s.nama as supir_nama
            FROM tbl_kendaraan k
            LEFT JOIN tbl_supir s ON k.supir_id = s.id
            WHERE k.latitude IS NOT NULL AND k.longitude IS NOT NULL
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getAllTruckLocations executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getAllTruckLocations): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil distribusi status armada untuk grafik donat.
     */
    public function getArmadaStatusDistribution() {
        $start_time = microtime(true);
        $query = "SELECT status, COUNT(*) as count FROM tbl_kendaraan GROUP BY status";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getArmadaStatusDistribution executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getArmadaStatusDistribution): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil permintaan perjalanan yang masih menunggu persetujuan.
     */
    public function getPendingTripRequests() {
        $start_time = microtime(true);
        $query = "
            SELECT 
                tr.id, tr.usulan_tujuan, tr.waktu_pengajuan,
                s.nama as supir_nama,
                k.no_polisi
            FROM tbl_trip_requests tr
            JOIN tbl_supir s ON tr.supir_id = s.id
            JOIN tbl_kendaraan k ON tr.kendaraan_id = k.id
            WHERE tr.status = 'pending'
            ORDER BY tr.waktu_pengajuan ASC
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getPendingTripRequests executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getPendingTripRequests): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil aktivitas & peringatan terbaru (misal: perjalanan dimulai, pelanggaran baru).
     */
    public function getRecentTrips($limit = 5, $offset = 0) {
        $start_time = microtime(true);
        $query = "
            SELECT p.id, k.no_polisi, s.nama as supir, p.alamat_tujuan, p.status, p.waktu_mulai
            FROM tbl_perjalanan p
            JOIN tbl_kendaraan k ON p.kendaraan_id = k.id
            JOIN tbl_supir s ON p.supir_id = s.id
            ORDER BY p.waktu_mulai DESC
            LIMIT :limit OFFSET :offset
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getRecentTrips executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getRecentTrips): " . $e->getMessage());
            return false;
        }
    }

    public function getTotalTripsCount() {
        $start_time = microtime(true);
        $query = "SELECT COUNT(*) as total_count FROM tbl_perjalanan";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getTotalTripsCount executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $row['total_count'];
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getTotalTripsCount): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mengambil aktivitas terbaru dari berbagai tabel untuk timeline.
     */
    public function getRecentActivities($limit = 5) {
        $start_time = microtime(true);
        // Query ini menggabungkan beberapa aktivitas menjadi satu timeline.
        // Pastikan ada index pada waktu_mulai, waktu_kejadian, dan waktu_pengajuan.
        $query = "
            (
                SELECT 'trip_started' as type, p.id, k.no_polisi as title, p.waktu_mulai as event_time, s.nama as details
                FROM tbl_perjalanan p
                JOIN tbl_kendaraan k ON p.kendaraan_id = k.id
                JOIN tbl_supir s ON p.supir_id = s.id
                ORDER BY p.waktu_mulai DESC
                LIMIT :limit
            )
            UNION ALL
            (
                SELECT 'violation' as type, v.id, v.jenis_pelanggaran as title, v.waktu_kejadian as event_time, k.no_polisi as details
                FROM tbl_pelanggaran v
                JOIN tbl_kendaraan k ON v.kendaraan_id = k.id
                ORDER BY v.waktu_kejadian DESC
                LIMIT :limit
            )
            UNION ALL
            (
                SELECT 'trip_request' as type, tr.id, tr.usulan_tujuan as title, tr.waktu_pengajuan as event_time, s.nama as details
                FROM tbl_trip_requests tr
                JOIN tbl_supir s ON tr.supir_id = s.id
                WHERE tr.status = 'pending'
                ORDER BY tr.waktu_pengajuan DESC
                LIMIT :limit
            )
            ORDER BY event_time DESC
            LIMIT :limit;
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $end_time = microtime(true);
            error_log("Report::getRecentActivities executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $result;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR (Report::getRecentActivities): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil semua data dashboard dengan memanggil fungsi-fungsi individual.
     */
    public function getDashboardSummary() {
        $start_time = microtime(true);
        try {
            $dashboard_data = [
                'kpi' => $this->getKpiSummary(),
                'truck_locations' => $this->getAllTruckLocations(),
                'armada_status' => $this->getArmadaStatusDistribution(),
                'trip_requests' => $this->getPendingTripRequests(),
                'recent_activity' => $this->getRecentActivities()
            ];

            // Periksa apakah ada data yang gagal diambil
            foreach ($dashboard_data as $key => $value) {
                if ($value === false) {
                    throw new Exception("Gagal mengambil data untuk: " . $key);
                }
            }

            $end_time = microtime(true);
            error_log("Report::getDashboardSummary executed in " . round(($end_time - $start_time) * 1000, 2) . " ms");
            return $dashboard_data;
        } catch (Exception $e) {
            error_log("ERROR (Report::getDashboardSummary): " . $e->getMessage());
            return false;
        }
    }
}
?>