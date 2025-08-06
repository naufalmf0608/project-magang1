<?php
// Bagian 1: Pengaturan Awal dan Koneksi Database

// Aktifkan pelaporan error untuk membantu proses debugging.
// PENTING: Komentari atau hapus baris ini saat aplikasi sudah dalam tahap produksi (live)!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // Asumsi: config.php berada di direktori yang sama dengan simpan.php

// Inisialisasi variabel status dan redirect untuk SweetAlert,
// agar tidak ada error 'undefined variable' jika ada die() sebelumnya.
$status = 'error'; // Default status jika ada masalah
$redirect = 'index.php'; // Default redirect jika ada masalah (atau sesuaikan ke halaman utama Anda)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = $_POST['nama'] ?? '';
    $pangkat      = $_POST['pangkat'] ?? '';
    $unit         = $_POST['unit'] ?? '';
    $token        = $_POST['token'] ?? '';
    $tanda_tangan = $_POST['tanda_tangan'] ?? ''; // Ini adalah data base64 dari tanda tangan

    // Validasi input wajib
    if (empty($nama) || empty($unit) || empty($tanda_tangan) || empty($token)) {
        $status = 'form_tidak_lengkap';
        // Tidak langsung die(), tapi set status untuk SweetAlert
    } else {
        // Cek id_kegiatan dari token
        $stmtKeg = $koneksi->prepare("SELECT id_kegiatan FROM kegiatan WHERE token = ?");
        $stmtKeg->bind_param("s", $token);
        $stmtKeg->execute();
        $resKeg = $stmtKeg->get_result();
        $rowKeg = $resKeg->fetch_assoc();
        $stmtKeg->close(); // Tutup statement

        if (!$rowKeg) {
            $status = 'kegiatan_tidak_ditemukan';
        } else {
            $id_kegiatan = $rowKeg['id_kegiatan'];

            // === PENTING: Pastikan folder ttd berada di level yang sama dengan simpan.php ===
            // Jika simpan.php di magang1/ dan ttd di magang1/ttd/, maka cukup 'ttd'.
            // Jika simpan.php di magang1/admin/ dan ttd di magang1/ttd/, maka '../ttd'.
            // Sesuaikan ini berdasarkan lokasi folder 'ttd' RELATIF terhadap 'simpan.php'.
            $ttd_folder = 'ttd'; 

            // Buat folder jika belum ada
            if (!is_dir($ttd_folder)) {
                // mkdir() dengan recursive = true dan permissions yang tepat
                if (!mkdir($ttd_folder, 0775, true)) {
                    $status = 'error_folder_ttd';
                }
            }

            if ($status !== 'error_folder_ttd') {
                // Simpan tanda tangan
                $nama_file = 'ttd_' . time() . '_' . rand(100, 999) . '.png';
                $path = $ttd_folder . '/' . $nama_file; // Path lengkap untuk menyimpan file

                $data = explode(',', $tanda_tangan);
                // === Perbaikan untuk PHP < 8.0: Ganti str_starts_with() dengan substr() ===
                if (count($data) === 2 && substr($data[0], 0, strlen('data:image/png;base64')) === 'data:image/png;base64') {
                    if (file_put_contents($path, base64_decode($data[1]))) {
                        // File tanda tangan berhasil disimpan

                        // Cek duplikat berdasarkan nama dan id_kegiatan
                        $cek = $koneksi->prepare("SELECT id_absensi FROM absensi WHERE nama = ? AND id_kegiatan = ?");
                        $cek->bind_param("si", $nama, $id_kegiatan);
                        $cek->execute();
                        $hasil = $cek->get_result();
                        $cek->close(); // Tutup statement

                        if ($hasil->num_rows > 0) {
                            $status = 'duplikat';
                            // Opsional: Hapus file TTD yang baru disimpan jika itu duplikat
                            // unlink($path); 
                        } else {
                            // Insert data ke database
                            $stmt = $koneksi->prepare("INSERT INTO absensi (id_kegiatan, nama, pangkat, unit, tanda_tangan, token) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("isssss", $id_kegiatan, $nama, $pangkat, $unit, $nama_file, $token);
                            
                            if ($stmt->execute()) {
                                $status = 'berhasil';
                            } else {
                                $status = 'error_insert_db';
                                // Opsional: Hapus file TTD jika insert ke DB gagal
                                // unlink($path);
                            }
                            $stmt->close(); // Tutup statement
                        }
                    } else {
                        $status = 'error_simpan_ttd';
                    }
                } else {
                    $status = 'format_ttd_tidak_valid';
                }
            }
        }
    }
    // Set redirect URL hanya jika token tersedia
    if (!empty($token)) {
        $redirect = "user.php?token=" . urlencode($token);
    } else {
        $redirect = "index.php"; // Redirect ke halaman utama jika token tidak ada
    }
} else {
    // Jika bukan metode POST, langsung redirect atau tampilkan pesan error
    $status = 'metode_tidak_valid';
    $redirect = "index.php"; // Kembali ke halaman utama
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Absensi</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php 
        // Menggunakan switch case untuk penanganan status yang lebih rapi
        switch ($status):
            case 'berhasil': ?>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Absensi Anda telah dikirim.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'duplikat': ?>
        Swal.fire({
            title: 'Sudah Absen!',
            text: 'Anda sudah melakukan presensi untuk kegiatan ini.',
            icon: 'warning',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'form_tidak_lengkap': ?>
        Swal.fire({
            title: 'Gagal!',
            text: 'Form tidak lengkap. Mohon isi semua kolom.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'kegiatan_tidak_ditemukan': ?>
        Swal.fire({
            title: 'Gagal!',
            text: 'Kegiatan tidak ditemukan atau token tidak valid.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'error_folder_ttd': ?>
        Swal.fire({
            title: 'Error!',
            text: 'Gagal membuat folder tanda tangan. Periksa izin direktori.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'format_ttd_tidak_valid': ?>
        Swal.fire({
            title: 'Error!',
            text: 'Format tanda tangan tidak valid.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'error_simpan_ttd': ?>
        Swal.fire({
            title: 'Error!',
            text: 'Gagal menyimpan file tanda tangan.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'error_insert_db': ?>
        Swal.fire({
            title: 'Error!',
            text: 'Gagal menyimpan data absensi ke database.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            case 'metode_tidak_valid': ?>
        Swal.fire({
            title: 'Akses Ditolak!',
            text: 'Akses langsung ke halaman ini tidak diizinkan.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
            default: // Menangkap error yang tidak terduga
                if (isset($status) && !empty($status)) { // Check if status was set but not handled
                    $debug_text = 'Status tidak dikenal: ' . htmlspecialchars($status);
                } else {
                    $debug_text = 'Terjadi kesalahan tidak terduga. Silakan coba lagi.';
                }
                ?>
        Swal.fire({
            title: 'Error Tidak Dikenal!',
            text: '<?= $debug_text ?>',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
        <?php break;
        endswitch; ?>
    });
    </script>
</body>

</html>