<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']);
?>
<?php include '../../components/header.php'; ?>
<title>Management Truck - Fleet Management</title>

<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Management Truck</h1>
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Semua Truck</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTruckModal"><i class="fas fa-plus me-2"></i>Tambah Truck</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nomor Polisi</th>
                                <th>Supir</th>
                                <th>Status</th>
                                <th>Kecepatan</th>
                                <th>Tujuan</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="truck-table-body">
                            </tbody>
                    </table>
                </div>
                <div id="pagination-controls" class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-secondary" id="prev-page-trucks" disabled>Previous</button>
                    <span id="page-info-trucks">Page 1 of 1</span>
                    <button class="btn btn-secondary" id="next-page-trucks">Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="addTruckModal" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addTruckModalLabel">Tambah Truk Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="addTruckForm" action="/fleet-management/backend/api/trucks.php" method="POST" data-ajax="true">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nomor Polisi</label><input type="text" class="form-control" name="no_polisi" required></div>
                    <div class="mb-3">
                        <label class="form-label">Tugaskan Supir (Opsional)</label>
                        <select class="form-select" name="supir_id" id="add-truck-supir-select">
                            <option value="none">-- Pilih Supir --</option>
                            </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Merk Truk</label><input type="text" class="form-control" name="merk" value="N/A"></div>
                    <div class="mb-3"><label class="form-label">Model Truk</label><input type="text" class="form-control" name="model" value="N/A"></div>
                    <div class="mb-3"><label class="form-label">Tahun Truk</label><input type="number" class="form-control" name="tahun" value="2024"></div>
                    <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="idle">Idle</option><option value="bergerak">Bergerak</option><option value="maintenance">Maintenance</option><option value="pelanggaran">Pelanggaran</option></select></div>
                    <div class="mb-3"><label class="form-label">Kecepatan (km/j)</label><input type="number" step="0.1" class="form-control" name="kecepatan" value="0" required></div>
                    <div class="mb-3"><label class="form-label">Tujuan</label><input type="text" class="form-control" name="tujuan"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>