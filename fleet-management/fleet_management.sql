-- Gemini Fleet Management SQL Dump - V2 (Professional)
-- Generation Time: Jun 24, 2024 at 11:00 AM
--
-- Visi: Standar Industri dengan Alur Persetujuan Perjalanan
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

--
-- Database: `fleet_management`
--
CREATE DATABASE IF NOT EXISTS `fleet_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `fleet_management`;

-- --------------------------------------------------------

--
-- Tabel: `tbl_user` (Pengguna Sistem/Dashboard)
--
CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','manager','viewer') NOT NULL DEFAULT 'viewer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_user` (`id`, `name`, `email`, `password`, `phone`, `role`) VALUES
(1, 'Admin Utama', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'admin'),
(2, 'Manager Operasional', 'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', 'manager');

-- --------------------------------------------------------

--
-- Tabel: `tbl_login` (Sesi Login Pengguna)
--
CREATE TABLE `tbl_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_time` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabel: `tbl_supir` (Data Master Supir)
--
CREATE TABLE `tbl_supir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `wa_number` varchar(20) DEFAULT NULL,
  `no_ktp` varchar(30) NOT NULL,
  `no_sim` varchar(30) NOT NULL,
  `status` enum('available','on_trip','unavailable') NOT NULL DEFAULT 'available',
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_ktp` (`no_ktp`),
  UNIQUE KEY `no_sim` (`no_sim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_supir` (`id`, `nama`, `phone`, `wa_number`, `no_ktp`, `no_sim`, `status`, `alamat`) VALUES
(1, 'Budi Santoso', '081111111111', '6281111111111', '3201010101800001', '800101010101', 'on_trip', 'Jl. Merdeka No. 1, Jakarta'),
(2, 'Agus Setiawan', '082222222222', '6282222222222', '3201010101850002', '850101010102', 'available', 'Jl. Sudirman No. 2, Bandung'),
(3, 'Cecep Firmansyah', '083333333333', '6283333333333', '3201010101900003', '900101010103', 'unavailable', 'Jl. Gatot Subroto No. 3, Surabaya');

-- --------------------------------------------------------

--
-- Tabel: `tbl_kendaraan` (Data Master Kendaraan/Truk)
--
CREATE TABLE `tbl_kendaraan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_polisi` varchar(15) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `tahun` year(4) DEFAULT NULL,
  `status` enum('bergerak','idle','maintenance') NOT NULL DEFAULT 'idle',
  `supir_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `kecepatan` decimal(5,2) DEFAULT 0.00,
  `odometer` int(11) DEFAULT 0 COMMENT 'Total kilometer kendaraan',
  `fuel_level` decimal(5,2) DEFAULT 0.00 COMMENT 'Level bahan bakar dalam persen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_polisi` (`no_polisi`),
  KEY `supir_id` (`supir_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_kendaraan` (`id`, `no_polisi`, `merk`, `model`, `tahun`, `status`, `supir_id`, `latitude`, `longitude`, `kecepatan`, `odometer`, `fuel_level`) VALUES
(1, 'B 1234 ABC', 'Mitsubishi', 'Fuso Canter', 2022, 'bergerak', 1, -6.208763, 106.845599, 65.50, 150234, 75.5),
(2, 'D 5678 DEF', 'Hino', 'Dutro', 2021, 'idle', 2, -6.303363, 107.141532, 0.00, 89012, 90.0),
(3, 'L 9012 GHI', 'Isuzu', 'Traga', 2023, 'maintenance', NULL, -7.257471, 112.752088, 0.00, 45678, 20.0),
(4, 'F 3456 JKL', 'Mitsubishi', 'Colt Diesel', 2020, 'idle', NULL, -6.732002, 108.552205, 0.00, 123456, 55.0);

-- --------------------------------------------------------

--
-- Tabel: `tbl_trip_requests` (Permintaan Perjalanan dari Supir)
--
CREATE TABLE `tbl_trip_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supir_id` int(11) NOT NULL,
  `kendaraan_id` int(11) NOT NULL,
  `usulan_tujuan` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `waktu_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `direspons_oleh` int(11) DEFAULT NULL COMMENT 'User ID yang merespons',
  `waktu_respons` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supir_id` (`supir_id`),
  KEY `kendaraan_id` (`kendaraan_id`),
  KEY `direspons_oleh` (`direspons_oleh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_trip_requests` (`id`, `supir_id`, `kendaraan_id`, `usulan_tujuan`, `status`) VALUES
(1, 2, 2, 'Kirim barang elektronik ke Gudang Semarang', 'pending');

-- --------------------------------------------------------

--
-- Tabel: `tbl_perjalanan` (Data Perjalanan yang Sudah Disetujui)
--
CREATE TABLE `tbl_perjalanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL COMMENT 'Referensi ke permintaan awal',
  `kendaraan_id` int(11) NOT NULL,
  `supir_id` int(11) NOT NULL,
  `alamat_asal` varchar(255) DEFAULT 'Pool',
  `alamat_tujuan` varchar(255) NOT NULL,
  `jarak_tempuh` decimal(10,2) DEFAULT 0.00,
  `status` enum('ongoing','completed','cancelled') NOT NULL DEFAULT 'ongoing',
  `waktu_mulai` timestamp NULL DEFAULT NULL,
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kendaraan_id` (`kendaraan_id`),
  KEY `supir_id` (`supir_id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_perjalanan` (`id`, `kendaraan_id`, `supir_id`, `alamat_tujuan`, `status`, `waktu_mulai`) VALUES
(1, 1, 1, 'Gudang Surabaya', 'ongoing', NOW() - INTERVAL 1 DAY);

-- --------------------------------------------------------

--
-- Tabel: `tbl_pelanggaran` (Catatan Pelanggaran)
--
CREATE TABLE `tbl_pelanggaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perjalanan_id` int(11) NOT NULL,
  `jenis_pelanggaran` enum('kecepatan','rem_mendadak','area_terlarang') NOT NULL,
  `status` enum('new','acknowledged') NOT NULL DEFAULT 'new',
  `waktu_kejadian` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `perjalanan_id` (`perjalanan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_pelanggaran` (`id`, `perjalanan_id`, `jenis_pelanggaran`, `status`, `waktu_kejadian`, `latitude`, `longitude`, `keterangan`) VALUES
(1, 1, 'kecepatan', 'new', NOW() - INTERVAL 5 HOUR, -6.9925, 107.6182, 'Melebihi batas kecepatan di tol Cipularang');

-- --------------------------------------------------------

--
-- Tabel: `tbl_maintenance` (Riwayat Perawatan Kendaraan)
--
CREATE TABLE `tbl_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kendaraan_id` int(11) NOT NULL,
  `jenis_perawatan` varchar(255) NOT NULL COMMENT 'e.g., Ganti Oli, Servis Rem',
  `biaya` decimal(12,2) DEFAULT 0.00,
  `tanggal_servis` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kendaraan_id` (`kendaraan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_maintenance` (`id`, `kendaraan_id`, `jenis_perawatan`, `biaya`, `tanggal_servis`, `keterangan`) VALUES
(1, 3, 'Servis Rutin 50,000 KM', 1500000.00, NOW() - INTERVAL 2 DAY, 'Ganti oli mesin, filter oli, dan pengecekan rem.');

-- --------------------------------------------------------

--
-- Tabel: `tbl_geofence` (Area Virtual di Peta)
--
CREATE TABLE `tbl_geofence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_area` varchar(100) NOT NULL,
  `tipe_area` enum('gudang','terlarang','pool') NOT NULL,
  `koordinat_polygon` text NOT NULL COMMENT 'JSON array of lat/lng pairs',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- AUTO_INCREMENT for tables
--
ALTER TABLE `tbl_user` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `tbl_login` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_supir` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `tbl_kendaraan` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `tbl_trip_requests` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tbl_perjalanan` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tbl_pelanggaran` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tbl_maintenance` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tbl_geofence` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for tables
--
ALTER TABLE `tbl_login` ADD CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_kendaraan` ADD CONSTRAINT `fk_kendaraan_supir` FOREIGN KEY (`supir_id`) REFERENCES `tbl_supir` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `tbl_trip_requests` ADD CONSTRAINT `fk_request_supir` FOREIGN KEY (`supir_id`) REFERENCES `tbl_supir` (`id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_request_kendaraan` FOREIGN KEY (`kendaraan_id`) REFERENCES `tbl_kendaraan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_request_user` FOREIGN KEY (`direspons_oleh`) REFERENCES `tbl_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `tbl_perjalanan` ADD CONSTRAINT `fk_perjalanan_request` FOREIGN KEY (`request_id`) REFERENCES `tbl_trip_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE, ADD CONSTRAINT `fk_perjalanan_kendaraan` FOREIGN KEY (`kendaraan_id`) REFERENCES `tbl_kendaraan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_perjalanan_supir` FOREIGN KEY (`supir_id`) REFERENCES `tbl_supir` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_pelanggaran` ADD CONSTRAINT `fk_pelanggaran_perjalanan` FOREIGN KEY (`perjalanan_id`) REFERENCES `tbl_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_maintenance` ADD CONSTRAINT `fk_maintenance_kendaraan` FOREIGN KEY (`kendaraan_id`) REFERENCES `tbl_kendaraan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;