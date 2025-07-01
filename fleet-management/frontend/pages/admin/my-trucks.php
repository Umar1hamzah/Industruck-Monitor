<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']); // Keep this for now, but usually this is for employee role
?>
<?php include '../../components/header.php'; ?>
<title>My Trucks - Fleet Management</title>
<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content"><div class="container-fluid"><h1 class="h3 mb-4">My Trucks</h1><div class="table-container"><p>Halaman ini akan menampilkan truk yang ditugaskan kepada Anda. Fitur sedang dalam pengembangan.</p></div></div></main>
</div>
<?php include '../../components/footer.php'; ?>