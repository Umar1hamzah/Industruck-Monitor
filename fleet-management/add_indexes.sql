CREATE INDEX idx_kendaraan_status ON tbl_kendaraan(status);
CREATE INDEX idx_pelanggaran_waktu_status ON tbl_pelanggaran(waktu_kejadian, status);
CREATE INDEX idx_perjalanan_waktu ON tbl_perjalanan(waktu_mulai);
CREATE INDEX idx_trip_requests_waktu_status ON tbl_trip_requests(waktu_pengajuan, status);