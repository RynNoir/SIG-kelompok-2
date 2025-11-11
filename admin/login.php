<?php
session_start();
require_once '../koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = MD5($_POST['password']); // MD5 sederhana untuk tugas kuliah
    
    $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">Login Admin</h3>
                            <p class="text-muted">Sistem Zonasi SMP Padang</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required autofocus>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="../index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Kembali ke Website
                            </a>
                        </div>

                        <div class="alert alert-info mt-4 small mb-0">
                            <strong>Demo Login:</strong><br>
                            Username: <code>admin</code><br>
                            Password: <code>admin123</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>