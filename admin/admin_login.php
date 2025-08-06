<?php
session_start();


require '../config.php'; // Pastikan path ke config.php benar

$error_message = '';
$status_message = '';
$status_type = '';

// 1. Logika untuk Mendeteksi Status Logout dari URL
if (isset($_GET['status']) && $_GET['status'] === 'logout_success') {
    $status_message = 'Anda telah berhasil logout.';
    $status_type = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "Username dan password harus diisi.";
    } else {
        $stmt = $koneksi->prepare("SELECT id_admin, username, email, password, roles FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_roles'] = $admin['roles'];

            // Set session untuk notifikasi SweetAlert di halaman berikutnya
            $_SESSION['login_status'] = 'success';
            $_SESSION['login_message'] = 'Login berhasil! Selamat datang, ' . htmlspecialchars($admin['username']) . '.';

            header("Location: index.php");
            exit();
        } else {
            $error_message = "Username atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #6a5acd 0%, #8a2be2 50%, #7b68ee 100%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            overflow-y: auto;
        }

        .login-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .login-card h2 {
            margin-bottom: 25px;
            color: #4a0072;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 0.95em;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #4a0072;
            box-shadow: 0 0 0 0.25rem rgba(74, 0, 114, 0.25);
            outline: none;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #4a0072;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #6a0099;
            transform: translateY(-2px);
        }

        .error-message {
            color: #dc3545;
            margin-top: 15px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="../gambar/logo.png" alt="Logo Institusi" class="img-fluid">
        </div>
        <h2>Admin Login</h2>

        <?php if ($error_message): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 3. Tambahkan logika JavaScript untuk menampilkan SweetAlert
        <?php if (!empty($status_message)): ?>
            Swal.fire({
                icon: '<?= $status_type ?>',
                title: '<?= $status_message ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            // Hapus parameter URL setelah notifikasi ditampilkan
            if (history.replaceState) {
                const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({
                    path: url
                }, '', url);
            }
        <?php endif; ?>
    </script>
</body>

</html>