<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']);
?>
<?php include '../../components/header.php'; ?>
<title>Management Supir - Fleet Management</title>
<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Management Supir</h1>
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Semua Supir</h5>
                    <button class="btn btn-primary btn-sm btn-add-driver"><i class="fas fa-plus me-2"></i>Tambah Supir</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Supir</th>
                                <th>No. Telepon</th>
                                <th>No. SIM</th>
                                <th>Status</th>
                                <th>Truk Ditugaskan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="driver-table-body">
                            </tbody>
                    </table>
                </div>
                <div id="pagination-controls-drivers" class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-secondary" id="prev-page-drivers" disabled>Previous</button>
                    <span id="page-info-drivers">Page 1 of 1</span>
                    <button class="btn btn-secondary" id="next-page-drivers">Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="driverModalLabel">Data Supir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="driver-form" action="/fleet-management/backend/api/drivers.php" method="POST" data-ajax="true">
                <div class="modal-body">
                    <input type="hidden" name="action" id="driver-action" value="add">
                    <input type="hidden" name="id" id="driver-id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Nama Lengkap*</label><input type="text" class="form-control" name="nama" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Status*</label><select class="form-select" name="status" required><option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option><option value="terminated">Terminated</option></select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Nomor KTP*</label><input type="text" class="form-control" name="no_ktp" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Nomor SIM*</label><input type="text" class="form-control" name="no_sim" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Nomor Telepon*</label><input type="text" class="form-control" name="phone" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Nomor WhatsApp</label><input type="text" class="form-control" name="wa_number"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat" rows="2"></textarea></div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Tugaskan Truk (Opsional)</label>
                        <select class="form-select" name="truck_id" id="assign-truck-select">
                            <option value="none">Tidak Ditugaskan</option>
                            </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>