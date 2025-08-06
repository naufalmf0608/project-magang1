<?php
require 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Tambahkan ini paling atas sebelum buat DateTime apapun
date_default_timezone_set('Asia/Jakarta');


$token = $_GET['token'] ?? '';

$kegiatan = null;
$error_message = '';
$form_disabled = true;

if (!empty($token)) {
    $stmt = $koneksi->prepare("SELECT * FROM kegiatan WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $kegiatan = $result->fetch_assoc();
    $stmt->close();
}

if ($kegiatan) {
    $waktu_kegiatan = new DateTime($kegiatan['tanggal'] . ' ' . $kegiatan['jam']);
    $waktu_sekarang = new DateTime();

    $waktu_mulai_absen = clone $waktu_kegiatan;
    $waktu_mulai_absen->modify('-2 hours');

    $waktu_selesai_absen = clone $waktu_kegiatan;
    $waktu_selesai_absen->modify('+1 hour');

    if ($waktu_sekarang >= $waktu_mulai_absen && $waktu_sekarang <= $waktu_selesai_absen) {
        $form_disabled = false;
    } elseif ($waktu_sekarang < $waktu_mulai_absen) {
        $error_message = 'Absensi belum dimulai. Silakan kembali pada ' . $waktu_mulai_absen->format('d M Y H:i') . ' WIB.';
    } else {
        $error_message = 'Absensi telah berakhir pada ' . $waktu_selesai_absen->format('d M Y H:i') . ' WIB.';
    }
} else {
    if (empty($token)) {
        $error_message = 'Token kegiatan tidak disediakan.';
    } else {
        $error_message = 'Kegiatan tidak ditemukan atau token tidak valid.';
    }

    $kegiatan = [
        'judul_kegiatan' => 'Kegiatan Tidak Ditemukan',
        'token' => 'N/A'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Presensi - <?= htmlspecialchars($kegiatan['judul_kegiatan']) ?></title>
    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Fonts & Plugin -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.0/dist/signature_pad.umd.min.js"></script>

    <!-- Style -->
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(to right bottom, #6f42c1, #a1c4fd);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        color: #333;
        text-align: center;
    }

    .navbar {
        background-color: #4a0072;
    }

    .navbar-brand {
        color: #fff !important;
        font-weight: 600;
    }

    .main-content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .main-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 25px;
        margin: 1rem auto;
        padding: 2rem;
        width: 95%;
        max-width: 550px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .system-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .activity-info {
        background-color: #f8f9fa;
        border-left: 5px solid #6f42c1;
        padding: 15px;
        border-radius: 8px;
        text-align: left;
        margin-bottom: 20px;
    }

    .signature-pad-container {
        border: 2px dashed #ccc;
        border-radius: 10px;
        background-color: #fff;
        width: 100%;
        height: 200px;
        margin-bottom: 15px;
        cursor: crosshair;
        touch-action: none;
        /* Penting untuk touch devices */
    }

    .signature-pad-wrapper {
        position: relative;
        width: 100%;
        padding-top: 40%;
        /* Rasio 5:2 */
        background-color: #fff;
        border: 2px dashed #ccc;
        border-radius: 8px;
        margin-bottom: 10px;
    }


    canvas {
        display: block;
    }

    .btn-action {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        padding: 0.75rem;
    }

    .btn-primary {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .btn-primary:hover {
        background-color: #5a35a1;
        border-color: #5a35a1;
        transform: translateY(-2px);
    }

    .btn-clear {
        padding: 8px 16px;
        font-size: 0.9rem;
        border-radius: 20px;
        width: auto;
        display: inline-block;
    }

    .footer {
        background-color: #343a40;
        color: #fff;
        padding: 2rem 0;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
    }

    .signature-section {
        margin-bottom: 1.5rem;
    }

    .signature-container {
        width: 100%;
    }

    .signature-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        touch-action: none;
        /* Penting untuk touch devices */
    }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .signature-pad-wrapper {
            padding-top: 50%;
            /* Rasio 2:1 */
        }
    }

    @media (max-width: 768px) {
        .main-card {
            padding: 1.5rem;
            border-radius: 20px;
        }

        .system-name {
            font-size: 1.5rem;
        }

        .signature-pad-container {
            height: 180px;
        }

        .signature-pad-wrapper {
            padding-top: 60%;
            /* Rasio 5:3 */
        }

        .btn-clear {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .main-card {
            padding: 1.25rem;
            margin: 1rem auto;
            border-radius: 15px;
        }

        .system-name {
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        .signature-pad-container {
            height: 150px;
        }

        .signature-pad-wrapper {
            padding-top: 70%;
            /* Rasio 10:7 */
        }

        form-control {
            padding: 0.5rem;
        }

        .main-card {
            padding: 1.5rem;
        }

        .btn-action {
            padding: 0.5rem;
        }

        .btn-clear-signature {
            font-size: 0.85rem;
            max-width: 100%;
            width: 100%;
            padding: 8px;
        }

        .form-text {
            font-size: 0.8rem;
        }

    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="user.php?token=<?= urlencode($token) ?>">
                <i class="bi bi-calendar-check-fill me-2"></i> Sistem Absensi
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="main-card">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger w-100">
                <?= htmlspecialchars($error_message) ?><br>
                <a href="user.php?token=<?= urlencode($token) ?>" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left-circle me-2"></i> Kembali
                </a>
            </div>
            <?php else: ?>
            <div class="system-name">Form Presensi</div>
            <div class="activity-info">
                <strong>Kegiatan:</strong> <?= htmlspecialchars($kegiatan['judul_kegiatan']) ?><br>
                <small class="text-muted">Token: <?= htmlspecialchars($kegiatan['token']) ?></small>
            </div>

            <form id="presensiForm" action="simpan.php" method="POST"
                <?= $form_disabled ? 'onsubmit="return false;"' : '' ?>>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="tanda_tangan" id="tanda_tangan_data">

                <div class="mb-3 text-start">
                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" id="nama" required
                        <?= $form_disabled ? 'disabled' : '' ?>>
                </div>
                <div class="mb-3 text-start">
                    <label for="pangkat" class="form-label">Pangkat/Jabatan (Opsional)</label>
                    <input type="text" class="form-control" name="pangkat" id="pangkat"
                        <?= $form_disabled ? 'disabled' : '' ?>>
                </div>
                <div class="mb-3 text-start">
                    <label for="unit" class="form-label">Unit Kerja / Instansi <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="unit" id="unit" required
                        <?= $form_disabled ? 'disabled' : '' ?>>
                </div>

                <div class="mb-3 text-start signature-section">
                    <label for="signature" class="form-label">Tanda Tangan <span class="text-danger">*</span></label>
                    <div class="signature-container">
                        <div class="signature-pad-wrapper">
                            <canvas id="signaturePad" class="signature-canvas"></canvas>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-clear mt-2" id="clear"
                            <?= $form_disabled ? 'disabled' : '' ?>>
                            <i class="bi bi-eraser-fill me-2"></i> Bersihkan Tanda Tangan
                        </button>
                    </div>
                    <small class="form-text text-muted d-block mt-1">Gunakan jari/stylus untuk menandatangani</small>
                </div>

                <button type="submit" class="btn btn-primary btn-action w-100 mt-4" id="submitBtn"
                    <?= $form_disabled ? 'disabled' : '' ?>>
                    <i class="bi bi-send-fill me-2"></i> Kirim Presensi
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Sistem Absensi. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signaturePad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        // Fungsi untuk resize canvas
        function resizeCanvas() {
            const wrapper = canvas.parentElement;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            // Set canvas dimensions
            canvas.width = wrapper.offsetWidth * ratio;
            canvas.height = wrapper.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            // Clear and redraw signature if exists
            signaturePad.clear();
        }

        // Initial resize
        resizeCanvas();

        // Handle window resize with debounce
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                resizeCanvas();
            }, 200);
        });

        // Clear button functionality
        document.getElementById('clear').addEventListener('click', function() {
            signaturePad.clear();
        });

        document.getElementById('presensiForm').addEventListener('submit', function(e) {
            if (<?= json_encode($form_disabled) ?>) {
                e.preventDefault();
                return;
            }

            if (signaturePad.isEmpty()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Kosong!',
                    text: 'Mohon berikan tanda tangan Anda.',
                    confirmButtonText: 'OK'
                });
            } else {
                document.getElementById('tanda_tangan_data').value = signaturePad.toDataURL(
                    'image/png');
            }
        });

        <?php if (!empty($error_message)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Akses Dibatasi!',
            text: '<?= htmlspecialchars($error_message) ?>',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>
    });
    </script>

</body>

</html>