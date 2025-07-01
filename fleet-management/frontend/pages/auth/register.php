<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Fleet Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animations.css"> </head>
<body>
    <div class="loading-overlay" style="display: none;"><div class="spinner"></div></div> <div class="auth-container">
        <div class="auth-card">
            <div class="auth-icon" style="background-color: var(--secondary-color);"><i class="fas fa-user-plus"></i></div>
            <h2 class="auth-title">Buat Akun Baru</h2>
            <p class="auth-subtitle">Daftarkan diri Anda untuk mengakses sistem</p>
            
            <form id="registerForm" action="/fleet-management/backend/api/auth.php" method="POST" data-ajax="true">
                <input type="hidden" name="action" value="register">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="phone" class="form-control" placeholder="Nomor Telepon (Opsional)">
                </div>
                <div class="mb-4">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Daftar</button> </form>

            <div class="text-center mt-4">
                <span class="text-muted">Sudah punya akun? </span>
                <a href="login.php" class="text-decoration-none">Login di sini</a>
            </div>
        </div>
    </div>
    <?php include '../../components/footer.php'; ?>
</body>
</html>