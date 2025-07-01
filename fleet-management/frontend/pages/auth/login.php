<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TrakPoint Fleet Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animations.css"> </head>
<body>
    <div class="loading-overlay" style="display: none;"><div class="spinner"></div></div> <div class="auth-container">
        <div class="auth-card">
            <div class="auth-icon"><i class="fas fa-lock"></i></div>
            <h2 class="auth-title">Selamat Datang Kembali</h2>
            <p class="auth-subtitle">Masuk untuk melanjutkan ke sistem</p>
            <form class="auth-form" action="/fleet-management/backend/api/auth.php" method="POST" data-ajax="true">
                <input type="hidden" name="action" value="login">
                <div class="form-group mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="form-group mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <button type="submit" class="btn btn-primary w-100">Login</button> </form>
            <div class="auth-footer-link text-center mt-3"> <span class="text-muted">Belum punya akun? </span>
                <a href="register.php" class="text-decoration-none">Daftar di sini</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js" defer></script>
</body>
</html>