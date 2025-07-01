<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireLogin();
?>
<?php include '../../components/header.php'; ?>
<title>Fleet Tracking Dashboard - Fleet Management</title>

<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Fleet Tracking Dashboard</h1>

            <div class="row" id="dashboard-stats-cards">
                <div class="col-xl-3 col-md-6 mb-4"><div class="stats-card"><div class="d-flex justify-content-between align-items-center"><div><div class="stats-label">Total Truck</div><div class="stats-number" id="total-trucks">...</div></div><i class="fas fa-truck fa-2x stats-icon text-primary"></i></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="stats-card border-success"><div class="d-flex justify-content-between align-items-center"><div><div class="stats-label">Dalam Perjalanan</div><div class="stats-number" id="bergerak-trucks">...</div></div><i class="fas fa-route fa-2x stats-icon text-success"></i></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="stats-card border-warning"><div class="d-flex justify-content-between align-items-center"><div><div class="stats-label">Idle</div><div class="stats-number" id="idle-trucks">...</div></div><i class="fas fa-pause-circle fa-2x stats-icon text-warning"></i></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="stats-card border-danger"><div class="d-flex justify-content-between align-items-center"><div><div class="stats-label">Pelanggaran</div><div class="stats-number" id="pelanggaran-trucks">...</div></div><i class="fas fa-exclamation-triangle fa-2x stats-icon text-danger"></i></div></div></div>
            </div>

            <div class="dashboard-grid mb-4">
                <div class="map-container">
                    <h5 class="mb-3">Live Tracking Map</h5>
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt fa-4x mb-3"></i>
                        <p class="h5">Peta Interaktif</p>
                        <small>Fitur ini dalam pengembangan</small>
                    </div>
                </div>
                <div class="activity-feed">
                    <h5 class="mb-3">Laporan Terbaru</h5>
                    <div class="feed-content" id="activity-feed-container">
                        </div>
                </div>
            </div>

            <div class="table-container fade-in">
                <h5 class="mb-3">Daftar Truck</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nomor Polisi</th>
                                <th>Supir</th>
                                <th>Status</th>
                                <th>Kecepatan</th>
                                <th>Tujuan</th>
                            </tr>
                        </thead>
                        <tbody id="truck-table-body">
                                </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../components/footer.php'; ?>