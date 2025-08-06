<?php
// Pastikan file config.php sudah ada dan terhubung dengan benar
require 'config.php';

// Aktifkan pelaporan error untuk membantu proses debugging.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone agar waktu lokal sesuai
date_default_timezone_set('Asia/Jakarta');

$token = $_GET['token'] ?? '';
$kegiatan = null;
$error_message = '';
$button_status = [
    'disabled' => false,
    'message' => '',
    'class' => 'btn-presensi'
];
$batas_mulai_absen = null;
$batas_akhir_absen = null;

if (!empty($token)) {
    // Menggunakan prepared statement untuk mencegah SQL Injection
    $stmt = $koneksi->prepare("SELECT * FROM kegiatan WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $kegiatan = $result->fetch_assoc();
    $stmt->close();
}

if (!$kegiatan) {
    // Jika kegiatan tidak ditemukan
    $kegiatan = [
        'judul_kegiatan' => 'Kegiatan Tidak Ditemukan',
        'token' => 'N/A',
        'tanggal' => null,
        'jam' => null
    ];
    $error_message = 'Maaf, kegiatan yang Anda cari tidak ditemukan atau token tidak valid.';
} else {
    // Logika untuk mengatur durasi absen
    $full_waktu_mulai = $kegiatan['tanggal'] . ' ' . $kegiatan['jam'];

    // Pastikan format tanggal dan jam dari database valid
    $waktu_mulai_kegiatan = strtotime($full_waktu_mulai);
    $waktu_sekarang = time();

    // Pastikan waktu mulai kegiatan valid sebelum melakukan perhitungan
    if ($waktu_mulai_kegiatan === false) {
        $error_message = 'Format tanggal atau jam pada database tidak valid.';
        $kegiatan['judul_kegiatan'] = 'Error Waktu';
    } else {
        $batas_mulai_absen = $waktu_mulai_kegiatan - (2 * 3600); // 2 jam sebelum
        $batas_akhir_absen = $waktu_mulai_kegiatan + (1 * 3600); // 1 jam setelah

        if ($waktu_sekarang < $batas_mulai_absen) {
            $button_status['disabled'] = true;
            $button_status['message'] = 'Absen belum dibuka. Silakan kembali nanti.';
            $button_status['class'] = 'btn-secondary';
        } elseif ($waktu_sekarang > $batas_akhir_absen) {
            $button_status['disabled'] = true;
            $button_status['message'] = 'Waktu absen telah berakhir.';
            $button_status['class'] = 'btn-danger';
        } else {
            $button_status['message'] = 'Silakan lakukan presensi.';
            $button_status['class'] = 'btn-success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Presensi - <?= htmlspecialchars($kegiatan['judul_kegiatan']) ?></title>
    <link rel="icon" href="../magang1/gambar/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    /* Mengatur font Inter untuk seluruh body */
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(to right bottom, #6f42c1, #a1c4fd);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        color: #333;
        text-align: center;
    }

    /* Styling untuk navbar */
    .navbar {
        background-color: #4a0072;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        color: #ffffff !important;
        font-weight: 600;
    }

    /* Konten utama yang akan mengambil sisa ruang */
    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    /* Styling untuk card utama yang membungkus seluruh elemen presensi */
    .main-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 25px;
        padding: 40px 30px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .clock {
        font-size: 3.5rem;
        font-weight: 700;
        color: #4a0072;
        margin-bottom: 5px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .date {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .logo {
        max-width: 150px;
        height: auto;
        margin-bottom: 15px;
    }

    .system-name {
        font-size: 1.8rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 25px;
    }

    /* Styling untuk card kegiatan di dalam card utama */
    .activity-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 20px;
        width: 90%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        color: #333;
    }

    .activity-card h2 {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #4a0072;
    }

    .activity-card p {
        font-size: 1rem;
        color: #555;
        margin-bottom: 0;
    }

    /* Styling untuk tombol presensi */
    .btn-presensi,
    .btn-success,
    .btn-danger,
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 25px;
        padding: 15px 35px;
        font-size: 1.2rem;
        font-weight: 600;
        color: #fff;
        border: none;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        cursor: pointer;
    }

    .btn-presensi {
        background-color: #6f42c1;
    }

    .btn-success {
        background-color: #198754;
    }

    .btn-danger {
        background-color: #dc3545;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-presensi:hover,
    .btn-success:hover {
        background-color: #5a35a1;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-danger:hover,
    .btn-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-danger:disabled,
    .btn-secondary:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-presensi i,
    .btn-success i,
    .btn-danger i,
    .btn-secondary i {
        margin-right: 10px;
    }

    .message {
        margin-top: 10px;
        font-size: 1rem;
        font-weight: 500;
        color: #dc3545;
    }

    /* Styling untuk footer */
    .footer {
        background-color: #343a40;
        color: #ffffff;
        padding: 2rem 0;
        text-align: center;
    }

    .footer p {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 0;
    }

    /* Media queries untuk responsivitas */
    @media (max-width: 768px) {
        .main-card {
            padding: 30px 20px;
            max-width: 90%;
        }

        .clock {
            font-size: 2.8rem;
        }

        .system-name {
            font-size: 1.5rem;
        }

        .btn-presensi,
        .btn-success,
        .btn-danger,
        .btn-secondary {
            padding: 12px 30px;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 576px) {
        .main-card {
            padding: 25px 15px;
        }

        .clock {
            font-size: 2.2rem;
        }

        .date {
            font-size: 0.9rem;
        }

        .logo {
            max-width: 100px;
        }

        .system-name {
            font-size: 1.2rem;
        }

        .activity-card h2 {
            font-size: 1.1rem;
        }

        .activity-card p {
            font-size: 0.9rem;
        }

        .btn-presensi,
        .btn-success,
        .btn-danger,
        .btn-secondary {
            padding: 10px 25px;
            font-size: 1rem;
        }
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check-fill me-2"></i> Sistem Absensi
            </a>
        </div>
    </nav>
    <div class="main-content">
        <?php if (isset($error_message) && !empty($error_message)): ?>
        <div class="alert alert-danger text-center main-card" role="alert">
            <?= htmlspecialchars($error_message) ?>
            <br><a href="index.php" class="btn btn-primary mt-3"><i class="bi bi-house-fill me-2"></i> Kembali ke
                Halaman Utama</a>
        </div>
        <?php else: ?>
        <div class="main-card">
            <div class="clock" id="clock">00:00:00</div>
            <div class="date" id="date">Tanggal</div>
            <img src="../magang1/gambar/logo.png" alt="Logo Instansi" class="logo">
            <div class="system-name">E-Presensi</div>
            <div class="activity-card">
                <h2>Kegiatan</h2>
                <p><?= htmlspecialchars($kegiatan['judul_kegiatan']) ?></p>
            </div>

            <?php if ($batas_mulai_absen && $batas_akhir_absen): ?>
            <div class="alert alert-info mt-3" role="alert">
                Waktu presensi dibuka: <?= date('H:i', $batas_mulai_absen) ?> sampai
                <?= date('H:i', $batas_akhir_absen) ?>
            </div>
            <?php endif; ?>

            <button type="button" id="presensiBtn" class="btn-presensi <?= $button_status['class'] ?>"
                <?= $button_status['disabled'] ? 'disabled' : '' ?>>
                <i class="bi bi-person-check-fill"></i> Presensi
            </button>

            <?php if ($button_status['disabled']): ?>
            <div class="message text-danger mt-2">
                <?= htmlspecialchars($button_status['message']) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date("Y"); ?> Sistem Absensi. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
    function updateClock() {
        const now = new Date();
        const jam = String(now.getHours()).padStart(2, '0');
        const menit = String(now.getMinutes()).padStart(2, '0');
        const detik = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('clock').textContent = `${jam}:${menit}:${detik}`;

        const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];
        const tanggal = `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
        document.getElementById('date').textContent = tanggal;
    }
    setInterval(updateClock, 1000);
    updateClock(); // Initial call

    document.addEventListener('DOMContentLoaded', function() {
        const presensiBtn = document.getElementById('presensiBtn');
        const isButtonDisabled = presensiBtn.disabled;
        const token = '<?= urlencode($token) ?>';
        const message = '<?= htmlspecialchars($button_status['message']) ?>';
        const actionUrl = `index.php?token=${token}`;

        // Jika tombol tidak disabled, kita berikan event listener untuk redirect
        if (!isButtonDisabled) {
            presensiBtn.addEventListener('click', function() {
                window.location.href = actionUrl;
            });
        }

        // Tampilkan SweetAlert jika tombol disabled
        if (isButtonDisabled) {
            Swal.fire({
                icon: 'warning',
                title: 'Presensi Tidak Tersedia',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        // Menampilkan SweetAlert jika kegiatan tidak ditemukan
        <?php if (isset($error_message) && !empty($error_message)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: '<?= htmlspecialchars($error_message) ?>',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>
    });
    </script>
</body>

</html>