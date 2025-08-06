<?php
// Konfigurasi dan koneksi database
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config.php';

$ttd_base_path = __DIR__ . '/../ttd/';

$selected_id_kegiatan = null;
$kegiatanStmt = null;
$token_to_print = ''; // Variabel untuk menyimpan token yang akan dicetak

// Ambil data kegiatan termasuk tanggal dan jam
if (isset($_GET['id_kegiatan']) && is_numeric($_GET['id_kegiatan'])) {
    $selected_id_kegiatan = (int)$_GET['id_kegiatan'];
    $kegiatanStmt = $koneksi->prepare("
        SELECT id_kegiatan, judul_kegiatan, tanggal, jam, token 
        FROM kegiatan 
        WHERE id_kegiatan = ?
    ");
    $kegiatanStmt->bind_param("i", $selected_id_kegiatan);
} else {
    $kegiatanStmt = $koneksi->prepare("
        SELECT DISTINCT k.id_kegiatan, k.judul_kegiatan, k.tanggal, k.jam, k.token 
        FROM kegiatan k
        JOIN absensi a ON k.id_kegiatan = a.id_kegiatan
        ORDER BY k.id_kegiatan DESC
    ");
}

$kegiatanStmt->execute();
$kegiatanResult = $kegiatanStmt->get_result();

// Jika ada kegiatan spesifik yang dipilih dan ditemukan, ambil tokennya untuk tombol cetak
if ($selected_id_kegiatan !== null && $kegiatanResult->num_rows > 0) {
    $kegiatanResult->data_seek(0); // Reset pointer untuk mengambil baris pertama
    $first_kegiatan = $kegiatanResult->fetch_assoc();
    $token_to_print = $first_kegiatan['token'];
    $kegiatanResult->data_seek(0); // Reset pointer lagi agar loop di bawah berjalan dengan benar
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Sistem Absensi</title>

    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f2f5;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .navbar {
        background-color: #4a0072;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        color: #ffffff !important;
        font-weight: 600;
    }

    .main-content {
        flex: 1;
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid #e9ecef;
        padding: 0.8rem;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .card-body {
        padding: 1rem;
    }

    /* STYLE TABEL UTAMA */
    .table {
        font-size: 8pt !important;
        border-collapse: collapse;
        border: 1px solid black !important;
        margin-top: 0.2rem;
        width: 100%;
    }

    .table thead th {
        padding: 3px 5px !important;
        border: 1px solid black !important;
        height: 35px !important;
        background-color: #343a40;
        color: white;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }

    .table tbody td {
        padding: 0.25rem 0.3rem;
        border: 1px solid black !important;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        position: relative;
        min-height: 25px !important;
        /* Added min-height */
    }

    .ttd-img {
        max-width: 70px;
        height: auto;
        background: transparent !important;
    }

    .ttd-container {
        position: relative;
        min-height: 40px;
        /* Changed to min-height */
    }

    .ttd-number {
        position: absolute;
        top: 2px;
        left: 2px;
        font-size: 8px;
        color: #666;
    }

    .kegiatan-header {
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-radius: 4px;
        margin-bottom: 0.3rem;
    }

    .kegiatan-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }

    .kegiatan-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* LEBAR KOLOM YANG OPTIMAL */
    .col-no {
        width: 4%;
        text-align: center;
    }

    .col-nama {
        width: 30%;
    }

    .col-pangkat {
        width: 20%;
    }

    .col-unit {
        width: 12%;
    }

    .col-ttd {
        width: 12%;
        text-align: center;
    }

    /* STYLE CETAK */
    .print-header,
    .print-footer {
        display: none;
    }

    @media print {
        body {
            margin: 0;
            padding: 5px;
            font-size: 9pt;
            background-color: #fff !important;
            line-height: 1.2;
        }

        .no-print {
            display: none !important;
        }

        .print-header,
        .print-footer {
            display: block !important;
        }

        .print-header {
            text-align: center;
            margin-bottom: 3px;
            /* Mengurangi margin-bottom */
            padding-bottom: 3px;
            border-bottom: 1px solid #000;
        }

        .print-header img {
            max-width: 5px;
            /* Ukuran logo disesuaikan */
        }

        .print-header h1 {
            font-size: 12pt;
            margin: 3px 0;
            font-weight: bold;
        }

        .print-header p {
            font-size: 8pt;
            margin: 1px 0;
        }

        .print-header h2 {
            font-size: 10pt;
            margin-top: 5px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .kegiatan-title {
            font-size: 10pt;
            font-weight: bold;
        }

        .kegiatan-meta {
            font-size: 9pt;
        }

        .card,
        .card-body {
            padding: 0;
            margin: 0;
            box-shadow: none;
            border: none;
        }

        table {
            font-size: 7.5pt !important;
            width: 100% !important;
            margin-top: 3px;
            border-collapse: collapse !important;
            border: 1px solid black !important;
        }

        th,
        td {
            padding: 1px 2px !important;
            /* Mengurangi padding */
            border: 1px solid black !important;
            height: 25px !important;
            /* Mengurangi tinggi baris */
            min-height: 25px !important;
            /* Added min-height for print */
            vertical-align: middle !important;
            /* text-align: center !important; - Dihapus karena beberapa kolom membutuhkan text-align berbeda */
        }

        /* Mengembalikan text-align center untuk kolom ttd */
        .table thead th,
        .table tbody td.col-no,
        .table tbody td.col-pangkat,
        .table tbody td.col-unit,
        .table tbody td:nth-child(5),
        .table tbody td:nth-child(6) {
            text-align: center !important;
        }

        /* Memastikan kolom nama tetap rata kiri */
        .table tbody td.col-nama {
            text-align: left !important;
        }


        .ttd-img {
            max-width: 60px;
            /* Adjusted to 60px */
            filter: none !important;
        }

        .print-section {
            margin-bottom: 5px;
            page-break-inside: avoid;
        }

        .kegiatan-header {
            background-color: transparent;
            padding: 0;
            margin-bottom: 2px;
        }

        .print-footer {
            margin-top: 30px;
            /* Mengurangi margin-top */
            text-align: right;
            /* Memastikan footer rata kanan */
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            margin-top: 30px;
            /* margin: 0 auto; - Dihapus untuk memposisikan ke kanan */
        }

        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin: 0 auto;
        }

        .table,
        .table-bordered {
            border: 1px solid black !important;
        }

        .table-responsive {
            overflow-x: auto;
            border: none !important;
        }
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg no-print">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check-fill me-2"></i> Sistem Absensi
            </a>
        </div>
    </nav>

    <div class="main-content container">
        <div class="print-header text-center">
            <img src="../gambar/logo.png" alt="Logo Instansi" style="margin-bottom: 10px;">
            <h1 style="margin: 5px 0;">KEPOLISIAN DAERAH JAWA TIMUR</h1>
            <h1 style="margin: 5px 0;">RS. BHAYANGKARA TK. II HASTA BRATA BATU</h1>
            <p style="margin: 3px 0;">Jl. R.A. Kartini No. 1, Batu, Jawa Timur, Indonesia</p>
            <p style="margin: 3px 0;">Telepon: (0341) 591067 | Email: hastabrata@gmail.com</p>
            <h2 style="margin: 10px 0 15px 0;">DAFTAR HADIR</h2>
        </div>

        <div class="card">
            <div class="card-header no-print">
                Laporan Absensi
            </div>
            <div class="card-body">
                <div class="no-print mb-2 d-flex justify-content-between align-items-center">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left-circle-fill me-1"></i> Kembali
                    </a>
                    <?php if ($selected_id_kegiatan !== null && !empty($token_to_print)): ?>
                    <a href="cetak.php?token=<?= htmlspecialchars($token_to_print) ?>" target="_blank"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-file-pdf-fill me-1"></i> Cetak PDF
                    </a>
                    <?php else: ?>
                    <button class="btn btn-primary btn-sm" disabled title="Pilih kegiatan untuk mencetak PDF">
                        <i class="bi bi-file-pdf-fill me-1"></i> Cetak PDF
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($kegiatanResult->num_rows > 0): ?>
                <?php while ($kegiatan = $kegiatanResult->fetch_assoc()): 
                    // Format tanggal dan jam
                    $tanggal = date('d-m-Y', strtotime($kegiatan['tanggal']));
                    $jam = substr($kegiatan['jam'], 0, 5); // Format HH:MM
                ?>
                <div class="print-section">
                    <div class="kegiatan-header text-center">
                        <h5 class="kegiatan-title mb-1">
                            <strong>Kegiatan: </strong><?= htmlspecialchars($kegiatan['judul_kegiatan']) ?>
                        </h5>
                        <div class="kegiatan-meta">
                            <span>Tanggal: <?= $tanggal ?></span> |
                            <span>Jam: <?= $jam ?></span> |
                            <span>Token: <?= htmlspecialchars($kegiatan['token']) ?></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" style="border: 1px solid #000;">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th class="col-nama">NAMA
                                        PESERTA</th>
                                    <th class="col-pangkat">
                                        PANGKAT/NRP/NIP</th>
                                    <th class="col-unit">UNIT</th>
                                    <th class="col-ttd">TANDA TANGAN
                                    </th>
                                    <th class="col-ttd">TANDA TANGAN
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $absenStmt = $koneksi->prepare("SELECT * FROM absensi WHERE id_kegiatan = ? ORDER BY waktu_absen");
                                $absenStmt->bind_param("i", $kegiatan['id_kegiatan']);
                                $absenStmt->execute();
                                $absenResult = $absenStmt->get_result();
                                
                                if ($absenResult->num_rows > 0):
                                    $no = 1;
                                    while ($row = $absenResult->fetch_assoc()):
                                ?>
                                <tr style="height: 35px;">
                                    <td class="col-no"><?= $no ?></td>
                                    <td class="col-nama">
                                        <?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="col-pangkat">
                                        <?= htmlspecialchars($row['pangkat']) ?></td>
                                    <td class="col-unit">
                                        <?= htmlspecialchars($row['unit']) ?></td>
                                    <td style="text-align: center; vertical-align: middle; height: 50px;">
                                        <?php if ($no % 2 == 0 && !empty($row['tanda_tangan']) && file_exists($ttd_base_path . $row['tanda_tangan'])): ?>
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                            <span style="font-size: 8px; align-self: flex-start;"><?= $no ?></span>
                                            <img src="../ttd/<?= htmlspecialchars($row['tanda_tangan']) ?>"
                                                class="ttd-img">
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; height: 50px;">
                                        <?php if ($no % 2 == 1 && !empty($row['tanda_tangan']) && file_exists($ttd_base_path . $row['tanda_tangan'])): ?>
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                            <span style="font-size: 8px; align-self: flex-start;"><?= $no ?></span>
                                            <img src="../ttd/<?= htmlspecialchars($row['tanda_tangan']) ?>"
                                                class="ttd-img">
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                            $no++;
                                        endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; vertical-align: middle;">Tidak ada data
                                        absensi</td>
                                </tr>
                                <?php
                                endif;
                                $absenStmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="alert alert-info text-center py-2">
                    <?= $selected_id_kegiatan !== null ? 
                        "Kegiatan dengan ID $selected_id_kegiatan tidak ditemukan" : 
                        "Tidak ada kegiatan dengan data absensi" ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="print-footer">
        <div>Batu, <?= date('d F Y') ?></div>
        <br>
        <br>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p style="margin-top: 3px;">Dr. dr. Ananingati, Sp.OG (K)</p>
            <p>Pimpinan</p>
        </div>
    </div>

    <footer class="footer no-print mt-auto py-2">
        <div class="container text-center">
            <small>&copy; <?= date('Y') ?> Sistem Absensi. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
<?php $koneksi->close(); ?>