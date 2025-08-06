<?php
require '../config.php';

// Status message handling
$status_message = '';
$status_type = '';

// Form submission handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_kegiatan = trim(htmlspecialchars($_POST['judul_kegiatan']));
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $jam = $_POST['jam'] ?? '08:00';
    $token = substr(md5(uniqid(rand(), true)), 0, 8);

    if (!empty($judul_kegiatan)) {
        try {
            $stmt = $koneksi->prepare("INSERT INTO kegiatan (judul_kegiatan, tanggal, jam, token) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $judul_kegiatan, $tanggal, $jam, $token);
            
            if ($stmt->execute()) {
                header("Location: index.php?status=added_success");
                exit();
            } else {
                $status_message = 'Gagal menambahkan kegiatan: ' . $stmt->error;
                $status_type = 'error';
            }
        } catch (Exception $e) {
            $status_message = 'Terjadi kesalahan: ' . $e->getMessage();
            $status_type = 'error';
        } finally {
            if (isset($stmt)) $stmt->close();
        }
    } else {
        $status_message = 'Judul Kegiatan tidak boleh kosong!';
        $status_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kegiatan - Sistem Absensi</title>
    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

    .datepicker-input {
        background-color: #fff;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check-fill me-2"></i> Sistem Absensi
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tambah Kegiatan Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Judul Kegiatan</label>
                        <input type="text" class="form-control" name="judul_kegiatan" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="text" class="form-control datepicker-input" name="tanggal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam</label>
                            <input type="time" class="form-control" name="jam" required value="08:00">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Simpan
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Kembali
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
    // Initialize datepicker
    flatpickr(".datepicker-input", {
        dateFormat: "Y-m-d",
        locale: "id",
        defaultDate: "today"
    });

    // Status notification
    <?php if (!empty($status_message)): ?>
    Swal.fire({
        icon: '<?= $status_type ?>',
        title: '<?= $status_message ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    <?php endif; ?>
    </script>
</body>

</html>