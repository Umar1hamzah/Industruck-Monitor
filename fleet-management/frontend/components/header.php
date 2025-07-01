<?php
// /fleet-management/frontend/components/header.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$user_name = $_SESSION['name'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'guest';
$role_display = ucfirst($user_role);
$role_badge_class = '';
switch ($user_role) {
    case 'admin':
        $role_badge_class = 'bg-primary';
        break;
    case 'manager':
        $role_badge_class = 'bg-info';
        break;
    case 'operator':
        $role_badge_class = 'bg-warning';
        break;
    case 'viewer':
        $role_badge_class = 'bg-secondary';
        break;
    default:
        $role_badge_class = 'bg-light text-dark';
        break;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="/fleet-management/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/fleet-management/frontend/assets/css/animations.css"> 
</head>
<body class="dashboard-body">

    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <nav class="navbar fixed-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-lg-none text-white me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="fas fa-bars"></i>
                </button>
                <!-- di sini namabah logo -->
                <div class="navbar-brand-logo d-none d-lg-flex align-items-center"> 
                    <div class="icon-bg"><i class="fas fa-truck fa-lg"></i></div>
                    <div>
                        <h5>TrakPoint</h5>
                        <small>Fleet Management</small>
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none d-md-inline me-2"><?= htmlspecialchars($user_name) ?></span>
                    <img src="https://placehold.co/40x40/7E8FFC/FFFFFF?text=<?= strtoupper(substr($user_name, 0, 1)) ?>" alt="User" class="rounded-circle">
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="px-3 py-2">
                            <div class="fw-bold"><?= htmlspecialchars($user_name) ?></div>
                            <div class="text-muted"><span class="badge <?= $role_badge_class ?>"><?= $role_display ?></span></div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" data-action="logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>