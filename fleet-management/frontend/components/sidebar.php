<?php
// /fleet-management/frontend/components/sidebar.php
// Sidebar ini sekarang menampilkan menu yang berbeda sesuai role pengguna.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'guest';

// Helper function untuk menandai link yang aktif
if (!function_exists('isActive')) {
    function isActive($page, $current_page) {
        return $page === $current_page ? 'active' : '';
    }
}

$admin_links = [
    'dashboard.php' => ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
    'trucks.php'    => ['icon' => 'fa-truck', 'label' => 'Management Truck'], // Added trucks.php link
    'drivers.php'   => ['icon' => 'fa-id-card-alt', 'label' => 'Management Supir'],
    'employees.php' => ['icon' => 'fa-users', 'label' => 'Management Employee'],
    'reports.php'   => ['icon' => 'fa-chart-bar', 'label' => 'Reports & Analytics'],
    'settings.php'  => ['icon' => 'fa-cog', 'label' => 'Settings']
];

$employee_links = [
    'dashboard.php' => ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
    'my-trucks.php' => ['icon' => 'fa-truck-moving', 'label' => 'My Trucks'], // Moved here for employee
    'schedule.php'  => ['icon' => 'fa-calendar-alt', 'label' => 'Schedule'],
    'profile.php'   => ['icon' => 'fa-user', 'label' => 'My Profile']
];

$links_to_use = ($user_role === 'admin' || $user_role === 'manager') ? $admin_links : $employee_links;
$base_path = ($user_role === 'admin' || $user_role === 'manager') ? '/fleet-management/frontend/pages/admin/' : '/fleet-management/frontend/pages/employee/';

?>

<div class="d-flex flex-column justify-content-between h-100 bg-dark text-white sidebar">
    <ul class="nav flex-column pt-2">
        <?php foreach ($links_to_use as $page => $details): ?>
            <li class="nav-item">
                <a class="nav-link <?= isActive($page, $current_page) ?>" href="<?= $base_path . $page ?>">
                    <i class="fas <?= $details['icon'] ?>"></i>
                    <span><?= $details['label'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Quick Actions
        </div>

        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="fas fa-question-circle"></i>
                <span>Help & Support</span>
            </a>
        </li>
        
        <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="fas fa-server"></i>
                <span>System Status</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-profile-box">
        <div class="d-flex align-items-center">
            <img src="https://placehold.co/50x50/4C63D2/FFFFFF?text=<?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>" alt="User" class="rounded-circle me-3">
            <div>
                <div class="fw-bold text-white"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                <small class="text-white-50"><?= htmlspecialchars(ucfirst($user_role)) ?></small>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">TrakPoint</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="d-flex flex-column justify-content-between h-100 bg-dark text-white">
            <ul class="nav flex-column pt-2">
                <?php foreach ($links_to_use as $page => $details): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= isActive($page, $current_page) ?>" href="<?= $base_path . $page ?>">
                            <i class="fas <?= $details['icon'] ?>"></i>
                            <span><?= $details['label'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Quick Actions</div>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-question-circle"></i>
                        <span>Help & Support</span>
                    </a>
                </li>
                <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-server"></i>
                        <span>System Status</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="sidebar-profile-box">
                <div class="d-flex align-items-center">
                    <img src="https://placehold.co/50x50/4C63D2/FFFFFF?text=<?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>" alt="User" class="rounded-circle me-3">
                    <div>
                        <div class="fw-bold text-white"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                        <small class="text-white-50"><?= htmlspecialchars(ucfirst($user_role)) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>