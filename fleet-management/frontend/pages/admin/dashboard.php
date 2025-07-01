<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    // Hanya admin dan manager yang bisa mengakses dashboard ini
    $auth->requireRole(['admin', 'manager']);
    $currentUser = $auth->getCurrentUser();
?>
<?php include '../../components/header.php'; ?>
<!-- Leaflet CSS for Interactive Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<title>Dashboard - Fleet Management</title>

<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="dashboard-header">
                <h1 class="h3 mb-4 text-gray-800">Dashboard Monitoring</h1>
                <div id="last-updated" class="text-muted"></div>
            </div>

            <!-- Baris Kartu KPI (Key Performance Indicators) -->
            <div class="row" id="kpi-cards-container">
                <!-- Kartu KPI akan dimuat di sini oleh JavaScript -->
            </div>

            <!-- Baris Utama: Peta dan Grafik -->
            <div class="row">
                <!-- Kolom Peta -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Peta Lokasi Armada</h6>
                        </div>
                        <div class="card-body">
                            <div id="live-map" style="height: 500px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Grafik -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Status Armada</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4">
                                <canvas id="armada-status-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris Baru: Persetujuan Perjalanan & Aktivitas Terbaru -->
            <div class="row">
                <!-- Kolom Persetujuan Perjalanan -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Persetujuan Perjalanan</h6>
                        </div>
                        <div class="card-body" id="trip-requests-container">
                            <!-- Daftar permintaan akan dimuat di sini oleh JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Kolom Aktivitas Terbaru -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Aktivitas & Peringatan Terbaru</h6>
                        </div>
                        <div class="card-body" id="activity-feed-container">
                             <!-- Feed aktivitas akan dimuat di sini oleh JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Leaflet JS for Interactive Map -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Chart.js for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Main App JS -->
<script src="/fleet-management/frontend/assets/js/app.js"></script>

<?php include '../../components/footer.php'; ?>
