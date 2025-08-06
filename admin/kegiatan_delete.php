<?php
// Memuat file konfigurasi database. Pastikan path ini benar sesuai struktur folder Anda.
// Contoh: Jika kegiatan_delete.php ada di 'admin/', dan config.php ada di root, maka pathnya '../config.php'.
require '../config.php';

// Aktifkan pelaporan error untuk membantu proses debugging.
// PENTING: Komentari atau hapus baris ini saat aplikasi sudah dalam tahap produksi!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pastikan request datang dengan metode GET dan memiliki parameter id_kegiatan
if (isset($_GET['id_kegiatan']) && is_numeric($_GET['id_kegiatan'])) {
    // Mengambil dan membersihkan ID kegiatan dari URL
    $id_kegiatan = (int)$_GET['id_kegiatan'];

    // Mendefinisikan path dasar untuk folder tanda tangan
    $ttd_base_path = __DIR__ . '/../ttd/';

    // Mulai transaksi database
    // Ini penting untuk memastikan bahwa semua operasi penghapusan (absensi, file, kegiatan)
    // berhasil atau tidak sama sekali. Jika ada satu yang gagal, semuanya akan dibatalkan.
    $koneksi->begin_transaction();

    try {
        // 1. Hapus file tanda tangan terkait dengan absensi kegiatan ini
        // Pertama, ambil nama file tanda tangan dari tabel 'absensi'
        $stmt_select_ttd = $koneksi->prepare("SELECT tanda_tangan FROM absensi WHERE id_kegiatan = ?");
        $stmt_select_ttd->bind_param("i", $id_kegiatan);
        $stmt_select_ttd->execute();
        $result_ttd = $stmt_select_ttd->get_result();

        // Loop melalui setiap baris absensi untuk mendapatkan nama file tanda tangan
        while ($row_ttd = $result_ttd->fetch_assoc()) {
            $ttd_filename = $row_ttd['tanda_tangan'];
            $full_ttd_filepath = $ttd_base_path . $ttd_filename;

            // Periksa apakah nama file tidak kosong dan file benar-benar ada di server
            if (!empty($ttd_filename) && file_exists($full_ttd_filepath)) {
                // Hapus file dari sistem file server
                unlink($full_ttd_filepath);
            }
        }
        $stmt_select_ttd->close(); // Tutup statement setelah selesai

        // 2. Hapus semua data absensi yang terkait dengan id_kegiatan ini
        $stmt_delete_absensi = $koneksi->prepare("DELETE FROM absensi WHERE id_kegiatan = ?");
        $stmt_delete_absensi->bind_param("i", $id_kegiatan);
        $stmt_delete_absensi->execute();
        $stmt_delete_absensi->close(); // Tutup statement setelah selesai

        // 3. Hapus kegiatan itu sendiri dari tabel 'kegiatan'
        $stmt_delete_kegiatan = $koneksi->prepare("DELETE FROM kegiatan WHERE id_kegiatan = ?");
        $stmt_delete_kegiatan->bind_param("i", $id_kegiatan);
        $stmt_delete_kegiatan->execute();
        $stmt_delete_kegiatan->close(); // Tutup statement setelah selesai

        // Jika semua operasi di atas berhasil, commit transaksi
        $koneksi->commit();

        // Redirect kembali ke index.php dengan pesan sukses
        header("Location: index.php?status=deleted_success");
        exit();
    } catch (Exception $e) {
        // Jika terjadi kesalahan pada salah satu operasi, rollback transaksi
        $koneksi->rollback();
        // Catat error ke log server untuk debugging
        error_log("Error deleting activity (ID: $id_kegiatan): " . $e->getMessage());
        // Redirect kembali ke index.php dengan pesan error
        header("Location: index.php?status=deleted_error");
        exit();
    } finally {
        // Pastikan koneksi database ditutup
        $koneksi->close();
    }
} else {
    // Jika id_kegiatan tidak valid atau tidak ada dalam request, redirect dengan pesan error
    header("Location: index.php?status=invalid_id");
    exit();
}
