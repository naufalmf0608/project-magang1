<?php
require '../config.php';
require '../vendor/autoload.php';

use Mpdf\Mpdf;

// Konfigurasi MPDF
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'tempDir' => __DIR__ . '/../temp',
    'margin_top' => 15,
    'margin_bottom' => 60,
]);

$mpdf->allow_html_optional_endtags = true;
$mpdf->showImageErrors = true;
$mpdf->debug = true;

$ttd_base_path = __DIR__ . '/../ttd/';

// Validasi token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Token kegiatan tidak ditemukan.");
}

// Ambil data kegiatan
$stmt = $koneksi->prepare("SELECT * FROM kegiatan WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$kegiatan = $stmt->get_result()->fetch_assoc();

if (!$kegiatan) {
    die("Kegiatan tidak ditemukan untuk token ini.");
}

$id_kegiatan = $kegiatan['id_kegiatan'];
$judul_kegiatan = $kegiatan['judul_kegiatan'];
$tanggal = date('d-m-Y', strtotime($kegiatan['tanggal']));
$jam = substr($kegiatan['jam'], 0, 5);

// Ambil data absensi
$absenStmt = $koneksi->prepare("SELECT * FROM absensi WHERE id_kegiatan = ? ORDER BY waktu_absen");
$absenStmt->bind_param("i", $id_kegiatan);
$absenStmt->execute();
$absenResult = $absenStmt->get_result();

// HTML Header
$headerHTML = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 9pt;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 12pt;
            margin: 3px 0;
            font-weight: bold;
        }
        .header p {
            font-size: 8pt;
            margin: 1px 0;
        }
        .header h2 {
            font-size: 10pt;
            margin-top: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kegiatan-info {
            text-align: center;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        .kegiatan-info strong {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1px solid black;
        }
        th, td {
            border: 1px solid black;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
            min-height: 25px;
            margin: 0;
            box-sizing: border-box;
        }
        th {
            background-color: #f0f0f0;
            color: black;
            font-weight: bold;
        }
        .ttd-container {
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            min-height: 40px;
        }
        .ttd-img {
            max-width: 60px;
            height: auto;
        }
        .ttd-number {
            position: absolute;
            top: 2px;
            left: 2px;
            font-size: 7px;
            color: #666;
            z-index: 10;
        }
        td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../gambar/logo.png" alt="Logo Instansi" style="width:100px; height:auto;">
        <h1>KEPOLISIAN DAERAH JAWA TIMUR</h1>
        <h1>RS. BHAYANGKARA TK. II HASTA BRATA BATU</h1>
        <p>Jl. R.A. Kartini No. 1, Batu, Jawa Timur, Indonesia</p>
        <p>Telepon: (0341) 591067 | Email: hastabrata@gmail.com</p>
        <h2>DAFTAR HADIR</h2>
    </div>

    <div class="kegiatan-info">
        <p><strong>Kegiatan:</strong> {$judul_kegiatan}</p>
        <p><strong>Tanggal:</strong> {$tanggal} &nbsp;&nbsp; <strong>Jam:</strong> {$jam} &nbsp;&nbsp;
            <strong>Token:</strong> {$token}
        </p>
    </div>
HTML;

// HTML Footer
$footerHTML = '
<div style="position: fixed; bottom: 60px; right: 60px; text-align: center; width: 200px;">
    <div style="margin-bottom: 5px;">Batu, ' . date('d F Y') . '</div>
    <br><br><br><br><br><br><br>
    <div style="border-bottom: 1px solid #000; width: 180px; margin: 0 auto 3px;"></div>
    <div style="font-weight: bold;">Dr. dr. Ananingati, Sp.OG (K)</div>
    <div style="margin-top: 2px;">Pimpinan</div>
</div>';

// Fungsi untuk generate tabel data
function generateTableHTML($pesertas, $start, $end, $ttd_base_path)
{
    $html = '<table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 25%;">NAMA PESERTA</th>
                <th style="width: 20%;">PANGKAT/NRP/NIP</th>
                <th style="width: 15%;">UNIT</th>
                <th style="width: 17.5%;">TANDA TANGAN</th>
                <th style="width: 17.5%;">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>';

    for ($i = $start; $i < $end; $i++) {
        if ($i >= count($pesertas)) break;

        $row = $pesertas[$i];
        $no = $i + 1;
        $ttd_file = $ttd_base_path . htmlspecialchars($row['tanda_tangan']);
        $ttd_src = (file_exists($ttd_file) && !empty($row['tanda_tangan'])) ? $ttd_file : '';

        $html .= '<tr>
            <td>' . $no . '</td>
            <td>' . htmlspecialchars($row['nama']) . '</td>
            <td>' . htmlspecialchars($row['pangkat']) . '</td>
            <td>' . htmlspecialchars($row['unit']) . '</td>';

        if ($no % 2 != 0) {
            $html .= '<td>';
            if (!empty($ttd_src)) {
                $html .= '<div class="ttd-container">
                    <span class="ttd-number">' . $no . '</span>
                    <img src="' . $ttd_src . '" class="ttd-img" alt="Tanda Tangan">
                </div>';
            }
            $html .= '</td><td></td>';
        } else {
            $html .= '<td></td><td>';
            if (!empty($ttd_src)) {
                $html .= '<div class="ttd-container">
                    <span class="ttd-number">' . $no . '</span>
                    <img src="' . $ttd_src . '" class="ttd-img" alt="Tanda Tangan">
                </div>';
            }
            $html .= '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    return $html;
}

// Proses data dan pembuatan PDF
$pesertas = $absenResult->fetch_all(MYSQLI_ASSOC);
$total_peserta = count($pesertas);
$rows_per_page = 15; // ★ UBAH NILAI INI SESUAI KEBUTUHAN ★

if ($total_peserta > 0) {
    $num_pages = ceil($total_peserta / $rows_per_page);

    // Halaman pertama dengan header lengkap
    $mpdf->WriteHTML($headerHTML);
    $tableHTML = generateTableHTML($pesertas, 0, min($rows_per_page, $total_peserta), $ttd_base_path);
    $mpdf->WriteHTML($tableHTML);

    // Halaman berikutnya tanpa header
    for ($page = 1; $page < $num_pages; $page++) {
        $start = $page * $rows_per_page;
        $end = min(($page + 1) * $rows_per_page, $total_peserta);

        $mpdf->AddPage();
        $tableHTML = generateTableHTML($pesertas, $start, $end, $ttd_base_path);
        $mpdf->WriteHTML($tableHTML);
    }

    // Set footer hanya di halaman terakhir
    $mpdf->SetHTMLFooter($footerHTML);
} else {
    $mpdf->WriteHTML($headerHTML);
    $mpdf->WriteHTML('<table><tbody><tr><td colspan="6">Tidak ada data absensi untuk kegiatan ini.</td></tr></tbody></table>');
    $mpdf->SetHTMLFooter($footerHTML);
}

// Output PDF
$mpdf->Output("Daftar_Hadir_" . str_replace(' ', '_', $judul_kegiatan) . ".pdf", "I");
$koneksi->close();
