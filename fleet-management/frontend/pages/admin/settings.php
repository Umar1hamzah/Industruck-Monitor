<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireRole(['admin', 'manager']);
?>
<?php include '../../components/header.php'; ?>
<title>Settings - Fleet Management</title>
<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content"><div class="container-fluid"><h1 class="h3 mb-4">Settings</h1><div class="table-container"><p>Halaman ini untuk pengaturan sistem. Fitur sedang dalam pengembangan.</p></div></div></main>
</div>
<?php include '../../components/footer.php'; ?>