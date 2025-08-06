<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require '../config.php';

$admin_username = $_SESSION['admin_username'];

$status_message = '';
$status_type = '';

// Ambil notifikasi login yang disimpan dari halaman login
if (isset($_SESSION['login_status']) && $_SESSION['login_status'] === 'success') {
    $status_message = $_SESSION['login_message'];
    $status_type = 'success';
    // Hapus variabel session agar notifikasi tidak muncul lagi saat refresh
    unset($_SESSION['login_status']);
    unset($_SESSION['login_message']);
}
// Ambil notifikasi dari operasi lain (tambah, ubah, hapus)
else if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted_success':
            $status_message = 'Kegiatan berhasil dihapus!';
            $status_type = 'success';
            break;
        case 'deleted_error':
            $status_message = 'Gagal menghapus kegiatan.';
            $status_type = 'error';
            break;
        case 'updated_success':
            $status_message = 'Kegiatan berhasil diperbarui!';
            $status_type = 'success';
            break;
        case 'updated_error':
            $status_message = 'Gagal memperbarui kegiatan.';
            $status_type = 'error';
            break;
        case 'added_success':
            $status_message = 'Kegiatan berhasil ditambahkan!';
            $status_type = 'success';
            break;
        case 'added_error':
            $status_message = 'Gagal menambahkan kegiatan.';
            $status_type = 'error';
            break;
        case 'invalid_id':
            $status_message = 'ID kegiatan tidak valid.';
            $status_type = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Sistem Absensi</title>
    <link rel="icon" href="../gambar/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        /* Gaya Khusus untuk tampilan mobile */
        @media (max-width: 767.98px) {
            .table thead {
                display: none;
            }

            .table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .table td {
                display: block;
                border: none;
                position: relative;
                padding-left: 140px;
                text-align: right;
                border-bottom: 1px solid #dee2e6;
            }

            .table td:last-child {
                border-bottom: none;
            }

            .table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 120px;
                text-align: left;
                font-weight: 600;
                color: #4a0072;
            }

            .table td.table-actions {
                text-align: left;
                padding: 15px;
            }

            .table td.table-actions:before {
                content: "Aksi";
                display: block;
                position: static;
                width: auto;
                margin-bottom: 8px;
            }

            .table td.table-actions .d-flex {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 5px;
            }

            .table td.table-actions .btn {
                width: auto;
            }

            .card-header .d-flex {
                flex-direction: column;
                gap: 8px;
            }

            .card-header .btn {
                width: 100%;
            }

            /* CSS untuk menyesuaikan SweetAlert di mobile */
            .swal2-container.swal2-top-end>.swal2-toast {
                width: 90%;
                font-size: 0.9em;
                margin-right: 10px;
                left: auto !important;
                right: 5px;
            }

            .swal2-popup.swal2-toast .swal2-title {
                font-size: 1em;
            }
        }

        /* CSS untuk memastikan tombol aksi tetap satu baris pada desktop */
        @media (min-width: 768px) {
            .table-actions .d-flex {
                flex-wrap: nowrap;
                justify-content: center;
            }

            .table td {
                vertical-align: middle;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check-fill me-2"></i> Sistem Absensi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <span class="navbar-text text-white me-lg-3 my-2 my-lg-0">
                            Selamat Datang, <?= htmlspecialchars($admin_username) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="#" id="logout-btn">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="mb-2 mb-md-0">Daftar Kegiatan</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="kegiatan_add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Kegiatan
                    </a>
                    <a href="tambah_admin.php" class="btn btn-success">
                        <i class="bi bi-person-plus me-2"></i> Tambah Akun Admin
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Token</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM kegiatan ORDER BY tanggal DESC, jam DESC";
                            $result = $koneksi->query($query);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td data-label='Judul'>" . htmlspecialchars($row['judul_kegiatan']) . "</td>
                                            <td data-label='Tanggal'>" . htmlspecialchars($row['tanggal']) . "</td>
                                            <td data-label='Jam'>" . htmlspecialchars(substr($row['jam'], 0, 5)) . "</td>
                                            <td data-label='Token'>" . htmlspecialchars($row['token']) . "</td>
                                            <td class='table-actions'>
                                                <div class='d-flex flex-nowrap gap-1'>
                                                    <button class='btn btn-warning btn-sm edit-btn'
                                                        data-id='" . $row['id_kegiatan'] . "'
                                                        data-judul='" . htmlspecialchars($row['judul_kegiatan']) . "'
                                                        data-tanggal='" . $row['tanggal'] . "'
                                                        data-jam='" . $row['jam'] . "'>
                                                        <i class='bi bi-pencil'></i> Edit
                                                    </button>
                                                    <a href='laporan.php?id_kegiatan=" . $row['id_kegiatan'] . "' class='btn btn-info btn-sm'>
                                                        <i class='bi bi-eye'></i> Lihat
                                                    </a>
                                                    <button class='btn btn-success btn-sm copy-btn' data-token='" . $row['token'] . "'>
                                                        <i class='bi bi-link-45deg'></i> Salin Link
                                                    </button>
                                                    <button class='btn btn-secondary btn-sm qr-btn' data-token='" . $row['token'] . "' data-bs-toggle='modal' data-bs-target='#qrModal'>
                                                        <i class='bi bi-qr-code'></i> QR
                                                    </button>
                                                    <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id_kegiatan'] . "'>
                                                        <i class='bi bi-trash'></i> Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tidak ada kegiatan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" action="kegiatan_update.php" method="POST">
                    <input type="hidden" name="id_kegiatan" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan</label>
                            <input type="text" class="form-control" name="judul_kegiatan" id="editJudul" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="text" class="form-control datepicker" name="tanggal" id="editTanggal"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam</label>
                                <input type="time" class="form-control" name="jam" id="editJam" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Link Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid mb-2">
                    <p class="text-muted small" id="qrLinkText"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            locale: "id"
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                document.getElementById('editId').value = this.dataset.id;
                document.getElementById('editJudul').value = this.dataset.judul;
                document.getElementById('editTanggal').value = this.dataset.tanggal;
                document.getElementById('editJam').value = this.dataset.jam;
                modal.show();
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus Kegiatan?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `kegiatan_delete.php?id_kegiatan=${this.dataset.id}`;
                    }
                });
            });
        });

        // Logika untuk menyalin link
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function(event) {
                event.preventDefault();
                const token = this.dataset.token;
                const domain = window.location.origin;
                const link = `${domain}/magang1/user.php?token=${token}`;

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Link disalin!',
                            text: 'Link absensi berhasil disalin ke clipboard',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }).catch(err => {
                        console.error('Gagal menyalin link: ', err);
                        fallbackCopyTextToClipboard(link);
                    });
                } else {
                    fallbackCopyTextToClipboard(link);
                }
            });
        });

        // Fungsi fallback untuk menyalin teks di browser lama
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                const successful = document.execCommand('copy');
                const msg = successful ? 'berhasil' : 'gagal';
                Swal.fire({
                    icon: successful ? 'success' : 'error',
                    title: `Link ${msg} disalin!`,
                    text: 'Link absensi telah disalin ke clipboard',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            } catch (err) {
                console.error('Fallback: Gagal menyalin teks ', err);
            }
            document.body.removeChild(textArea);
        }

        document.querySelectorAll('.qr-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const token = this.dataset.token;
                const link = `${window.location.origin}/magang1/user.php?token=${token}`;
                const qrUrl = `https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(link)}`;

                document.getElementById('qrImage').src = qrUrl;
                document.getElementById('qrLinkText').textContent = link;
            });
        });

        // 1. Ambil tombol logout berdasarkan ID
        const logoutBtn = document.getElementById('logout-btn');

        // 2. Tambahkan event listener untuk klik
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah navigasi default dari tag <a>

            Swal.fire({
                title: 'Apakah Anda yakin ingin keluar?',
                text: "Anda akan keluar dari panel admin.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna mengklik "Ya", arahkan ke halaman logout
                    window.location.href = 'logout.php';
                }
            });
        });

        // SweetAlert untuk notifikasi lain (login, update, delete)
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