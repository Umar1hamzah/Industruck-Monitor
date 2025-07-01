<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']);
?>
<?php include '../../components/header.php'; ?>
<title>Management Employee - Fleet Management</title>
<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid">
            <div id="current-user-id" data-id="<?= $_SESSION['user_id'] ?? 0 ?>" class="d-none"></div>

            <h1 class="h3 mb-4 text-gray-800">Management Employee</h1>
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Pengguna</h5>
                    <button class="btn btn-primary btn-sm btn-add-user">
                        <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Bergabung Sejak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            </tbody>
                    </table>
                </div>
                <div id="pagination-controls-users" class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-secondary" id="prev-page-users" disabled>Previous</button>
                    <span id="page-info-users">Page 1 of 1</span>
                    <button class="btn btn-secondary" id="next-page-users">Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="user-form" action="/fleet-management/backend/api/users.php" method="POST" data-ajax="true">
                <input type="hidden" name="action" value="register"> <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama Lengkap*</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email*</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password Sementara*</label><input type="password" class="form-control" name="password" required></div>
                    <div class="mb-3"><label class="form-label">Nomor Telepon</label><input type="text" class="form-control" name="phone"></div>
                    <div class="mb-3"><label class="form-label">Role*</label><select class="form-select" name="role" required><option value="viewer">Viewer</option><option value="operator">Operator</option><option value="manager">Manager</option><option value="admin">Admin</option></select></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../../components/footer.php'; ?>