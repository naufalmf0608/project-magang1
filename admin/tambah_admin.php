<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

// PENTING: Proteksi Halaman ini!
// Hanya izinkan admin yang sudah login untuk mengakses halaman ini.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Opsional: Proteksi lebih lanjut berdasarkan peran (role).
// Misalnya, hanya 'superadmin' yang boleh menambah admin.
// if ($_SESSION['admin_roles'] !== 'superadmin') {
//     echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini.'); window.location.href='index.php';</script>";
//     exit();
// }

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // Roles secara otomatis diset ke 'admin'
    $roles = 'admin'; // Hapus $_POST['roles'] karena kita tidak ingin pengguna memilihnya

    // Validasi Input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua kolom harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah terdaftar
        $stmt_check = $koneksi->prepare("SELECT id_admin FROM admin WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $error_message = "Username atau Email sudah terdaftar.";
        } else {
            // Hash password sebelum menyimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data admin baru
            // Pastikan bind_param sesuai dengan urutan kolom: username, email, password, roles
            $stmt_insert = $koneksi->prepare("INSERT INTO admin (username, email, password, roles) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $roles); // $roles sekarang sudah pasti 'admin'

            if ($stmt_insert->execute()) {
                $success_message = "Akun admin '$username' berhasil didaftarkan dengan role 'admin'!";
                // Kosongkan input setelah sukses
                $_POST = array(); // Untuk mengosongkan formulir setelah submit sukses
            } else {
                $error_message = "Gagal mendaftarkan akun: " . $koneksi->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun Admin</title>
    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f2f5;
    }

    .navbar {
        background-color: #4a0072;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-calendar-check-fill me-2"></i> Panel Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            Selamat Datang, **<?= htmlspecialchars($_SESSION['admin_username']) ?>**
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Akun Admin Baru</h5>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success_message) ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">Minimal 6 karakter.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Akun Admin</button>
                    <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>