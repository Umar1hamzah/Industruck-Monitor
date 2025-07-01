<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']);
?>
<?php include '../../components/header.php'; ?>
<title>Reports & Analytics - Fleet Management</title>

<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Reports & Analytics</h1>

            <div class="row" id="report-summary">
                </div>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="table-container h-100">
                        <h5 class="mb-3">Analisis Pelanggaran</h5>
                        <canvas id="violationsChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-5 mb-4">
                    <div class="table-container h-100">
                        <h5 class="mb-3">Perjalanan Terbaru</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No. Polisi</th>
                                        <th>Tujuan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-trips-table">
                                    </tbody>
                            </table>
                        </div>
                        <div id="pagination-controls-trips" class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-secondary" id="prev-page-trips" disabled>Previous</button>
                            <span id="page-info-trips">Page 1 of 1</span>
                            <button class="btn btn-secondary" id="next-page-trips">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../components/footer.php'; ?>