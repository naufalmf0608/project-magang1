<?php
require '../config.php';

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?status=invalid_method");
    exit();
}

// Validasi input
$id_kegiatan = filter_input(INPUT_POST, 'id_kegiatan', FILTER_VALIDATE_INT);
$judul_kegiatan = trim(htmlspecialchars($_POST['judul_kegiatan']));
$tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];

if (!$id_kegiatan || empty($judul_kegiatan) || empty($tanggal) || empty($jam)) {
    header("Location: index.php?status=invalid_input");
    exit();
}

// Update data
try {
    $stmt = $koneksi->prepare("UPDATE kegiatan SET judul_kegiatan = ?, tanggal = ?, jam = ? WHERE id_kegiatan = ?");
    $stmt->bind_param("sssi", $judul_kegiatan, $tanggal, $jam, $id_kegiatan);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: index.php?status=updated_success");
    } else {
        header("Location: index.php?status=updated_error");
    }
} catch (Exception $e) {
    error_log("Error updating kegiatan: " . $e->getMessage());
    header("Location: index.php?status=updated_error");
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($koneksi)) $koneksi->close();
}
?>