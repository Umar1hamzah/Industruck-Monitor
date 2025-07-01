/**
* =================================================================
* TrakPoint Fleet Management - V2 Professional (app.js)
* =================================================================
* - Logika dashboard terpusat & modern.
* - Peta Interaktif (Leaflet.js).
* - Grafik (Chart.js).
* - Alur kerja persetujuan perjalanan.
* - Refresh data otomatis.
* =================================================================
*/

console.log('app.js loaded'); // Debugging: Confirm app.js is loaded

const FleetApp = {
    map: null,
    truckMarkers: {},
    armadaChart: null,
    dashboardInterval: null,
    
    // Pagination state for trucks.php
    currentTrucksPage: 1,
    trucksPerPage: 10,
    totalTrucksPages: 1,

    // Pagination state for drivers.php
    currentDriversPage: 1,
    driversPerPage: 10,
    totalDriversPages: 1,

    // Pagination state for employees.php
    currentUsersPage: 1,
    usersPerPage: 10,
    totalUsersPages: 1,

    // Pagination state for reports.php (recent trips)
    currentTripsPage: 1,
    tripsPerPage: 5,
    totalTripsPages: 1,

    // Fungsi inisialisasi utama
    init: function() {
        console.log('FleetApp initialized'); // Debugging: Confirm init() is called
        this.setupEventListeners();
        this.initializePageComponents();
    },

    // Mendaftarkan semua event listener
    setupEventListeners: function() {
        // Menangani semua form dengan atribut 'data-ajax'
        $(document).on('submit', 'form[data-ajax="true"]', e => {
            e.preventDefault();
            this.handleAjaxForm(e.currentTarget);
        });

        // Menangani klik pada tombol persetujuan/penolakan
        $(document).on('click', '[data-action="handle-trip-request"]', e => {
            const button = $(e.currentTarget);
            const requestId = button.data('id');
            const action = button.data('type');
            this.handleTripRequest(requestId, action);
        });

        // Event listeners for trucks.php pagination
        $(document).on('click', '#prev-page-trucks', () => {
            if (this.currentTrucksPage > 1) {
                this.currentTrucksPage--;
                this.loadTrucksPageData(this.currentTrucksPage);
            }
        });
        $(document).on('click', '#next-page-trucks', () => {
            if (this.currentTrucksPage < this.totalTrucksPages) {
                this.currentTrucksPage++;
                this.loadTrucksPageData(this.currentTrucksPage);
            }
        });

        // Event listeners for drivers.php pagination
        $(document).on('click', '#prev-page-drivers', () => {
            if (this.currentDriversPage > 1) {
                this.currentDriversPage--;
                this.loadDriversPageData(this.currentDriversPage);
            }
        });
        $(document).on('click', '#next-page-drivers', () => {
            if (this.currentDriversPage < this.totalDriversPages) {
                this.currentDriversPage++;
                this.loadDriversPageData(this.currentDriversPage);
            }
        });

        // Event listeners for employees.php pagination
        $(document).on('click', '#prev-page-users', () => {
            if (this.currentUsersPage > 1) {
                this.currentUsersPage--;
                this.loadUsersPageData(this.currentUsersPage);
            }
        });
        $(document).on('click', '#next-page-users', () => {
            if (this.currentUsersPage < this.totalUsersPages) {
                this.currentUsersPage++;
                this.loadUsersPageData(this.currentUsersPage);
            }
        });

        // Event listeners for reports.php (recent trips) pagination
        $(document).on('click', '#prev-page-trips', () => {
            if (this.currentTripsPage > 1) {
                this.currentTripsPage--;
                this.loadReportsPageData(this.currentTripsPage); // Reload reports data
            }
        });
        $(document).on('click', '#next-page-trips', () => {
            if (this.currentTripsPage < this.totalTripsPages) {
                this.currentTripsPage++;
                this.loadReportsPageData(this.currentTripsPage); // Reload reports data
            }
        });
    },

    // Menjalankan fungsi yang sesuai dengan halaman yang dibuka
    initializePageComponents: function() {
        const path = window.location.pathname;
        if (path.includes('dashboard.php')) {
            this.initDashboard();
        }
        if (path.includes('trucks.php')) {
            this.loadTrucksPageData(this.currentTrucksPage);
        }
        if (path.includes('drivers.php')) {
            this.loadDriversPageData(this.currentDriversPage);
        }
        if (path.includes('employees.php')) {
            this.loadUsersPageData(this.currentUsersPage);
        }
        if (path.includes('reports.php')) {
            this.loadReportsPageData(this.currentTripsPage);
        }
        // Halaman lain bisa ditambahkan di sini nanti
    },

    // ==========================================================
    // FUNGSI UI & UTILITY
    // ==========================================================

    showLoading: () => {
        console.log('Showing loading overlay...');
        $('.loading-overlay').stop().fadeIn(200);
    },
    hideLoading: () => {
        console.log('Hiding loading overlay...');
        $('.loading-overlay').stop().fadeOut(200);
    },
    showNotification: function(type, message) {
        console.log(`Showing ${type} notification: ${message}`);
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        const notification = $(`<div class="alert ${alertClass} alert-dismissible fade show notification-popup" role="alert"><i class="fas ${iconClass} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        $('body').append(notification);
        setTimeout(() => notification.fadeOut(500, () => notification.remove()), 5000);
    },
    showSkeletonLoader: function(tableBodyId) {
        const $tableBody = $(tableBodyId);
        if (!$tableBody.length) return;
        $tableBody.addClass('loading').empty();
        const colCount = $tableBody.closest('table').find('thead th').length;
        for (let i = 0; i < 5; i++) { // Generate 5 rows of skeleton loaders
            let cells = '';
            for (let j = 0; j < colCount; j++) {
                cells += `<td><div class="skeleton-text skeleton-loader"></div></td>`;
            }
            $tableBody.append(`<tr>${cells}</tr>`);
        }
    },
    escapeHtml: str => String(str || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]),

    // ==========================================================
    // FUNGSI AJAX & AUTENTIKASI
    // ==========================================================
    
    handleAjaxForm: function(form) {
        this.showLoading();
        const $form = $(form);
        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData(form),
            processData: false, contentType: false, dataType: 'json',
            success: response => {
                this.hideLoading();
                this.showNotification('success', response.message); // Notifikasi sukses
                if (response.redirect) {
                    console.log('Login successful, redirecting to:', response.redirect); // Added console.log
                    window.location.href = response.redirect; // Redirect instan
                } else {
                    if ($form.closest('.modal').length) {
                        const modalId = $form.closest('.modal').attr('id');
                        if(modalId === 'driverModal') this.driverModal.hide();
                        // Add other modal hides here if needed
                    }
                    // Refresh data based on current page
                    const path = window.location.pathname;
                    if (path.includes('employees.php')) this.loadUsersPageData(this.currentUsersPage); // Refresh users page data
                    if (path.includes('drivers.php')) this.loadDriversPageData(this.currentDriversPage); // Refresh drivers page data
                    if (path.includes('dashboard.php')) {
                        this.loadDashboardData();
                    }
                    if (path.includes('trucks.php')) {
                        this.loadTrucksPageData(this.currentTrucksPage); // Refresh trucks page data
                    }
                }
            },
            error: jqXHR => {
                this.hideLoading();
                this.showNotification('error', jqXHR.responseJSON?.message || 'Operasi gagal.'); // Notifikasi error
            }
        });
    },
    logout: function() {
        this.showLoading();
        $.ajax({
            url: '/fleet-management/backend/api/auth.php?action=logout',
            success: response => { 
                if (response.success) {
                    // Clear session/local storage if needed, though PHP session_destroy should handle it
                    window.location.href = response.redirect; 
                }
            },
            error: () => this.showNotification('error', 'Gagal logout.')
        });
    },

    // ==========================================================
    // LOGIKA KHUSUS HALAMAN DASHBOARD
    // ==========================================================

    initDashboard: function() {
        this.initMap();
        this.loadDashboardData();

        // Hentikan refresh otomatis jika pengguna meninggalkan halaman
        $(window).on('unload', () => {
            if (this.dashboardInterval) clearInterval(this.dashboardInterval);
        });
    },

    loadDashboardData: function() {
        console.log('Refreshing dashboard data...');
        this.showLoading(); // Pastikan loading ditampilkan saat mulai request
        $.ajax({
            url: '/fleet-management/backend/api/dashboard.php',
            method: 'GET',
            dataType: 'json',
            success: response => {
                console.log('Dashboard data received successfully.', response);
                this.hideLoading(); // Sembunyikan loading setelah data diterima
                if (response.success) {
                    const data = response.data;
                    this.updateKpiCards(data.kpi);
                    this.updateTruckMarkers(data.truck_locations);
                    this.updateArmadaChart(data.armada_status);
                    this.renderTripRequests(data.trip_requests);
                    this.renderActivityFeed(data.recent_activity);
                    $('#last-updated').text(`Last updated: ${new Date().toLocaleTimeString()}`);
                } else {
                    console.error('Failed to load dashboard data:', response.message);
                    this.showNotification('error', response.message || 'Gagal memuat data dashboard.');
                }
            },
            error: (jqXHR) => {
                console.error('AJAX Error loading dashboard data:', jqXHR.responseJSON?.message || 'Could not connect to API.', jqXHR);
                this.hideLoading(); // Sembunyikan loading meskipun ada error
                this.showNotification('error', jqXHR.responseJSON?.message || 'Terjadi kesalahan saat memuat dashboard.');
            },
            complete: () => {
                console.log('Dashboard data request complete.');
                // Atur refresh otomatis setelah panggilan pertama selesai
                if (!this.dashboardInterval) {
                    this.dashboardInterval = setInterval(() => this.loadDashboardData(), 30000); // Refresh setiap 30 detik
                }
            }
        });
    },

    updateKpiCards: function(kpi) {
        const container = $('#kpi-cards-container');
        container.empty();
        container.append(`
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Truk</div><div class="h5 mb-0 font-weight-bold text-gray-800">${kpi.total_trucks}</div></div><div class="col-auto"><i class="fas fa-truck fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Beroperasi</div><div class="h5 mb-0 font-weight-bold text-gray-800">${kpi.on_trip_trucks}</div></div><div class="col-auto"><i class="fas fa-route fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Idle</div><div class="h5 mb-0 font-weight-bold text-gray-800">${kpi.idle_trucks}</div></div><div class="col-auto"><i class="fas fa-pause-circle fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pelanggaran (Hari Ini)</div><div class="h5 mb-0 font-weight-bold text-gray-800">${kpi.violations_today}</div></div><div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div></div></div></div></div>
        `);
    },

    initMap: function() {
        if (this.map) return;
        this.map = L.map('live-map').setView([-2.5489, 118.0149], 5); // Center of Indonesia
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);
    },

    updateTruckMarkers: function(trucks) {
        trucks.forEach(truck => {
            const iconHtml = `<div class="truck-marker-icon status-${truck.status}"><i class="fas fa-truck"></i></div>`;
            const customIcon = L.divIcon({ html: iconHtml, className: '' });

            const popupContent = `
                <b>${truck.no_polisi}</b><br>
                Supir: ${truck.supir_nama || 'N/A'}<br>
                Status: ${truck.status}<br>
                Kecepatan: ${truck.kecepatan} km/j
            `;

            if (this.truckMarkers[truck.id]) {
                this.truckMarkers[truck.id].setLatLng([truck.latitude, truck.longitude]).setIcon(customIcon).setPopupContent(popupContent);
            } else {
                this.truckMarkers[truck.id] = L.marker([truck.latitude, truck.longitude], { icon: customIcon })
                    .addTo(this.map)
                    .bindPopup(popupContent);
            }
        });
    },

    updateArmadaChart: function(statusData) {
        const ctx = document.getElementById('armada-status-chart').getContext('2d');
        const labels = statusData.map(d => d.status);
        const data = statusData.map(d => d.count);

        if (this.armadaChart) {
            this.armadaChart.data.labels = labels;
            this.armadaChart.data.datasets[0].data = data;
            this.armadaChart.update();
        } else {
            this.armadaChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: { enabled: true },
                    legend: { display: true, position: 'bottom' },
                    cutoutPercentage: 80,
                },
            });
        }
    },

    renderTripRequests: function(requests) {
        const container = $('#trip-requests-container');
        container.empty();
        if (requests.length === 0) {
            container.html('<div class="text-center text-muted p-3">Tidak ada permintaan perjalanan baru.</div>');
            return;
        }
        requests.forEach(req => {
            container.append(`
                <div class="trip-request-item">
                    <div class="request-info">
                        <p><strong>${req.supir_nama} (${req.no_polisi})</strong></p>
                        <small class="text-muted">Tujuan: ${req.usulan_tujuan}</small>
                    </div>
                    <div class="request-actions">
                        <button class="btn btn-success btn-sm" data-action="handle-trip-request" data-id="${req.id}" data-type="approve"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-sm" data-action="handle-trip-request" data-id="${req.id}" data-type="reject"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `);
        });
    },

    handleTripRequest: function(requestId, action) {
        if (!confirm(`Yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} permintaan ini?`)) return;

        $.ajax({
            url: '/fleet-management/backend/api/trip_request_action.php',
            method: 'POST',
            data: { request_id: requestId, action: action },
            dataType: 'json',
            success: response => {
                if (response.success) {
                    alert(response.message);
                    this.loadDashboardData(); // Refresh seluruh dashboard
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: () => alert('Terjadi kesalahan. Tidak dapat menghubungi server.')
        });
    },

    renderActivityFeed: function(activities) {
        const container = $('#activity-feed-container');
        container.empty();
        if (activities.length === 0) {
            container.html('<div class="text-center text-muted p-3">Tidak ada aktivitas terbaru.</div>');
            return;
        }
        activities.forEach(act => {
            const icon = act.activity_type === 'violation' ? '<i class="fas fa-exclamation-triangle text-danger"></i>' : '<i class="fas fa-route text-primary"></i>';
            container.append(`
                <div class="activity-feed-item">
                    <div class="feed-icon">${icon}</div>
                    <div class="feed-text">
                        <p>${act.activity_text}</p>
                        <small class="text-muted">${new Date(act.activity_time).toLocaleString()}</small>
                    </div>
                </div>
            `);
        });
    },

    // ==========================================================
    // LOGIKA KHUSUS HALAMAN TRUCKS (MANAGEMENT TRUCK)
    // ==========================================================

    loadTrucksPageData: function(page) {
        this.showSkeletonLoader('#truck-table-body');
        $.ajax({
            url: `/fleet-management/backend/api/trucks.php?page=${page}&limit=${this.trucksPerPage}`,
            method: 'GET',
            dataType: 'json',
            success: response => {
                this.hideLoading();
                if (response.success) {
                    this.updateTruckTable(response.trucks);
                    this.currentTrucksPage = response.page;
                    this.totalTrucksPages = Math.ceil(response.total_trucks / this.trucksPerPage);
                    this.updateTrucksPaginationControls();
                } else {
                    this.showNotification('error', response.message || 'Gagal memuat data truk.');
                    $('#truck-table-body').removeClass('loading').html(`<tr><td colspan="7" class="text-center p-4 text-danger">Gagal memuat data truk.</td></tr>`);
                }
            },
            error: jqXHR => {
                this.hideLoading();
                this.showNotification('error', jqXHR.responseJSON?.message || 'Terjadi kesalahan saat memuat truk.');
                $('#truck-table-body').removeClass('loading').html(`<tr><td colspan="7" class="text-center p-4 text-danger">Gagal memuat data truk.</td></tr>`);
            }
        });
    },

    updateTrucksPaginationControls: function() {
        $('#page-info-trucks').text(`Page ${this.currentTrucksPage} of ${this.totalTrucksPages}`);
        $('#prev-page-trucks').prop('disabled', this.currentTrucksPage === 1);
        $('#next-page-trucks').prop('disabled', this.currentTrucksPage === this.totalTrucksPages);
    },

    updateTruckTable: function(trucks) {
        const $tableBody = $('#truck-table-body');
        $tableBody.removeClass('loading').empty();
        if (!trucks || trucks.length === 0) {
            $tableBody.html(`<tr><td colspan="7" class="text-center p-4">Tidak ada data truk.</td></tr>`);
            return;
        }
        trucks.forEach(truck => {
            const actions = 
                `<button class="btn btn-sm btn-outline-danger" onclick="FleetApp.deleteTruck(${truck.id}, '${this.escapeHtml(truck.no_polisi)}')"><i class="fas fa-trash"></i></button>`;

            $tableBody.append(`<tr>
                <td><strong>${this.escapeHtml(truck.no_polisi)}</strong></td>
                <td>${this.escapeHtml(truck.supir || 'N/A')}</td>
                <td><span class="status-badge status-${truck.status.toLowerCase()}">${this.escapeHtml(truck.status)}</span></td>
                <td>${this.escapeHtml(truck.kecepatan || 0)} km/j</td>
                <td>${this.escapeHtml(truck.tujuan || 'N/A')}</td>
                <td>
                    <a href="https://www.google.com/maps/search/?api=1&query=${truck.latitude},${truck.longitude}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-map-marker-alt me-1"></i> Lihat
                    </a>
                </td>
                <td>${actions}</td>
            </tr>`);
        });
    },

    // ==========================================================
    // LOGIKA KHUSUS HALAMAN DRIVERS (MANAGEMENT SUPIR)
    // ==========================================================

    loadDriversPageData: function(page) {
        this.showSkeletonLoader('#driver-table-body');
        $.ajax({
            url: `/fleet-management/backend/api/drivers.php?action=get_all&page=${page}&limit=${this.driversPerPage}`,
            method: 'GET',
            dataType: 'json',
            success: response => {
                this.hideLoading();
                if (response.success) {
                    this.renderDriverTable(response.drivers);
                    this.currentDriversPage = response.page;
                    this.totalDriversPages = Math.ceil(response.total_drivers / this.driversPerPage);
                    this.updateDriversPaginationControls();
                } else {
                    this.showNotification('error', response.message || 'Gagal memuat data supir.');
                    $('#driver-table-body').removeClass('loading').html(`<tr><td colspan="6" class="text-center p-4 text-danger">Gagal memuat data supir.</td></tr>`);
                }
            },
            error: jqXHR => {
                this.hideLoading();
                this.showNotification('error', jqXHR.responseJSON?.message || 'Terjadi kesalahan saat memuat supir.');
                $('#driver-table-body').removeClass('loading').html(`<tr><td colspan="6" class="text-center p-4 text-danger">Gagal memuat data supir.</td></tr>`);
            }
        });
    },

    updateDriversPaginationControls: function() {
        $('#page-info-drivers').text(`Page ${this.currentDriversPage} of ${this.totalDriversPages}`);
        $('#prev-page-drivers').prop('disabled', this.currentDriversPage === 1);
        $('#next-page-drivers').prop('disabled', this.currentDriversPage === this.totalDriversPages);
    },

    renderDriverTable: function(drivers) {
        const $tableBody = $('#driver-table-body');
        $tableBody.removeClass('loading').empty();
        if (!drivers || drivers.length === 0) {
            $tableBody.html(`<tr><td colspan="6" class="text-center p-4">Belum ada data supir.</td></tr>`);
            return;
        }
        drivers.forEach(driver => {
            const statusBadge = `status-${driver.status.toLowerCase()}`;
            const truckInfo = driver.assigned_truck ? `<span class="badge bg-primary">${this.escapeHtml(driver.assigned_truck)}</span>` : `<span class="text-muted fst-italic">N/A</span>`;
            $tableBody.append(`<tr>
                                   <td><strong>${this.escapeHtml(driver.nama)}</strong></td>
                                   <td>${this.escapeHtml(driver.phone)}</td>
                                   <td>${this.escapeHtml(driver.no_sim)}</td>
                                   <td><span class="status-badge ${statusBadge}">${this.escapeHtml(driver.status)}</span></td>
                                   <td>${truckInfo}</td>
                                   <td>
                                        <button class="btn btn-sm btn-outline-info me-1" onclick="FleetApp.editDriver(${driver.id})"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="FleetApp.deleteDriver(${driver.id}, '${this.escapeHtml(driver.nama)}')"><i class="fas fa-trash"></i></button>
                                   </td>
                               </tr>`);
        });
    },

    // ==========================================================
    // LOGIKA UNTUK MANAJEMEN PENGGUNA (EMPLOYEE)
    // ==========================================================
    
    loadUsersPageData: function(page) {
        this.showSkeletonLoader('#user-table-body');
        $.ajax({
            url: `/fleet-management/backend/api/users.php?page=${page}&limit=${this.usersPerPage}`,
            method: 'GET',
            dataType: 'json',
            success: response => {
                this.hideLoading();
                if (response.success) {
                    this.renderUserTable(response.users);
                    this.currentUsersPage = response.page;
                    this.totalUsersPages = Math.ceil(response.total_users / this.usersPerPage);
                    this.updateUsersPaginationControls();
                } else {
                    this.showNotification('error', response.message || 'Gagal memuat data pengguna.');
                    $('#user-table-body').removeClass('loading').html(`<tr><td colspan="6" class="text-center p-4 text-danger">Gagal memuat data pengguna.</td></tr>`);
                }
            },
            error: jqXHR => {
                this.hideLoading();
                this.showNotification('error', jqXHR.responseJSON?.message || 'Terjadi kesalahan saat memuat pengguna.');
                $('#user-table-body').removeClass('loading').html(`<tr><td colspan="6" class="text-center p-4 text-danger">Gagal memuat data pengguna.</td></tr>`);
            }
        });
    },

    updateUsersPaginationControls: function() {
        $('#page-info-users').text(`Page ${this.currentUsersPage} of ${this.totalUsersPages}`);
        $('#prev-page-users').prop('disabled', this.currentUsersPage === 1);
        $('#next-page-users').prop('disabled', this.currentUsersPage === this.totalUsersPages);
    },

    renderUserTable: function(users) {
        const $tableBody = $('#user-table-body');
        $tableBody.removeClass('loading').empty();
        if (!users || users.length === 0) {
            $tableBody.html('<tr><td colspan="6" class="text-center p-4">Belum ada pengguna. Klik "Tambah Pengguna" untuk memulai.</td></tr>');
            return;
        }
        const currentUserId = parseInt($('#current-user-id').data('id'));
        users.forEach(user => {
            const roleBadge = user.role === 'admin' ? 'bg-primary' : 
                              user.role === 'manager' ? 'bg-info' : 
                              user.role === 'operator' ? 'bg-warning' : 'bg-secondary';
            const deleteButton = user.id != currentUserId ? `<button class="btn btn-sm btn-outline-danger" onclick="FleetApp.deleteUser(${user.id}, '${this.escapeHtml(user.name)}')"><i class="fas fa-trash"></i></button>` : `<span class="badge bg-light text-dark">Ini Anda</span>`;
            $tableBody.append(`<tr><td>${user.id}</td><td><strong>${this.escapeHtml(user.name)}</strong></td><td>${this.escapeHtml(user.email)}</td><td><span class="badge rounded-pill ${roleBadge}">${this.escapeHtml(user.role)}</span></td><td>${new Date(user.created_at).toLocaleDateString('id-ID')}</td><td>${deleteButton}</td></tr>`);
        });
    },
    openUserModal: function() {
        $('#user-form')[0].reset();
        $('#user-action').val('add');
        $('#userModalLabel').text('Tambah Pengguna Baru');
        $('#user-id').val(''); // Clear ID for new user
        this.userModal.show();
    },
    deleteUser: function(id, name) {
        if (!confirm(`Yakin ingin menghapus pengguna: ${name}?`)) return;
        this.showLoading();
        $.ajax({
            url: '/fleet-management/backend/api/users.php', method: 'POST', data: { action: 'delete', id: id }, dataType: 'json',
            success: response => {
                this.hideLoading(); this.showNotification('success', response.message); this.loadUsersPageData(this.currentUsersPage); // Refresh users page data
            },
            error: jqXHR => {
                this.hideLoading(); this.showNotification('error', jqXHR.responseJSON?.message);
            }
        });
    },

    // ==========================================================
    // LOGIKA UNTUK LAPORAN (REPORTS)
    // ==========================================================
    loadReportsPageData: function(page) {
        this.showLoading();
        $.ajax({
            url: `/fleet-management/backend/api/report.php?page=${page}&limit=${this.tripsPerPage}`,
            method: 'GET',
            dataType: 'json',
            success: response => {
                this.hideLoading();
                if (response.success) {
                    this.renderReportSummary(response.summary);
                    this.renderViolationsChart(response.violations);
                    this.renderRecentTripsTable(response.recent_trips);
                    this.currentTripsPage = response.page;
                    this.totalTripsPages = Math.ceil(response.total_trips / this.tripsPerPage);
                    this.updateTripsPaginationControls();
                } else {
                    this.showNotification('error', response.message || 'Gagal memuat data laporan.');
                }
            },
            error: jqXHR => {
                this.hideLoading();
                this.showNotification('error', jqXHR.responseJSON?.message || 'Terjadi kesalahan saat memuat laporan.');
            }
        });
    },
    updateTripsPaginationControls: function() {
        $('#page-info-trips').text(`Page ${this.currentTripsPage} of ${this.totalTripsPages}`);
        $('#prev-page-trips').prop('disabled', this.currentTripsPage === 1);
        $('#next-page-trips').prop('disabled', this.currentTripsPage === this.totalTripsPages);
    },
    renderReportSummary: function(summary) {
        const $summaryContainer = $('#report-summary');
        $summaryContainer.empty();
        if (!summary) {
            $summaryContainer.html('<p class="text-center text-muted">Data ringkasan tidak tersedia.</p>');
            return;
        }

        $summaryContainer.append(`
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Truk</div><div class="h5 mb-0 font-weight-bold text-gray-800">${summary.total_trucks || 0}</div></div><div class="col-auto"><i class="fas fa-truck fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Drivers</div><div class="h5 mb-0 font-weight-bold text-gray-800">${summary.total_drivers || 0}</div></div><div class="col-auto"><i class="fas fa-id-card-alt fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ongoing Trips</div><div class="h5 mb-0 font-weight-bold text-gray-800">${summary.ongoing_trips || 0}</div></div><div class="col-auto"><i class="fas fa-route fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Violations</div><div class="h5 mb-0 font-weight-bold text-gray-800">${summary.total_violations || 0}</div></div><div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div></div></div></div></div>
        `);
    },
    renderViolationsChart: function(violations) {
        const ctx = document.getElementById('violationsChart');
        if (!ctx) return;

        if (this.violationsChartInstance) {
            this.violationsChartInstance.destroy(); // Destroy previous chart instance
        }

        const labels = violations.map(v => this.escapeHtml(v.jenis_pelanggaran));
        const data = violations.map(v => v.jumlah);
        const backgroundColors = [
            'rgba(255, 99, 132, 0.6)', // Red
            'rgba(54, 162, 235, 0.6)', // Blue
            'rgba(255, 206, 86, 0.6)', // Yellow
            'rgba(75, 192, 192, 0.6)', // Green
            'rgba(153, 102, 255, 0.6)',// Purple
            'rgba(255, 159, 64, 0.6)'  // Orange
        ];
        const borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
        ];

        this.violationsChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '# of Violations',
                    data: data,
                    backgroundColor: backgroundColors.slice(0, data.length),
                    borderColor: borderColors.slice(0, data.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0 // Ensure integer ticks for count
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // No need for legend in simple bar chart
                    }
                }
            }
        });
    },
    renderRecentTripsTable: function(trips) {
        const $tableBody = $('#recent-trips-table');
        $tableBody.removeClass('loading').empty();
        if (!trips || trips.length === 0) {
            $tableBody.html(`<tr><td colspan="3" class="text-center p-4">Tidak ada perjalanan terbaru.</td></tr>`);
            return;
        }

        trips.forEach(trip => {
            const statusBadgeClass = trip.status === 'ongoing' ? 'bg-warning' :
                                     trip.status === 'completed' ? 'bg-success' : 'bg-secondary';
            $tableBody.append(`
                <tr>
                    <td>${this.escapeHtml(trip.no_polisi)}</td>
                    <td>${this.escapeHtml(trip.alamat_tujuan)}</td>
                    <td><span class="badge ${statusBadgeClass}">${this.escapeHtml(trip.status)}</span></td>
                </tr>
            `);
        });
    }
};

// Jalankan aplikasi setelah DOM siap
$(document).ready(() => FleetApp.init());