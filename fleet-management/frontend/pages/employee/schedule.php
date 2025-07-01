<?php
    require_once '../../../backend/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->requireLogin();
?>
<?php include '../../components/header.php'; ?>
<title>Schedule - Fleet Management</title>
<div class="dashboard-container">
    <?php include '../../components/sidebar.php'; ?>
    <main class="main-content"><div class="container-fluid"><h1 class="h3 mb-4">My Schedule</h1><div class="table-container"><p>Halaman ini akan menampilkan jadwal perjalanan Anda. Fitur sedang dalam pengembangan.</p></div></div></main>
</div>
<?php include '../../components/footer.php'; ?>

