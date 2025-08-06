-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 06, 2025 at 06:00 AM
-- Server version: 8.0.30
-- PHP Version: 7.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magang1`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id_absensi` int NOT NULL,
  `id_kegiatan` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `pangkat` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `tanda_tangan` varchar(255) NOT NULL,
  `waktu_absen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id_absensi`, `id_kegiatan`, `nama`, `pangkat`, `unit`, `tanda_tangan`, `waktu_absen`, `token`) VALUES
(1, 2, 'Muchamad Alfan Agusti Putra', 'mahasiswa', 'magang', 'ttd_1754268912_720.png', '2025-08-04 00:55:12', 'aa7aeb6a'),
(2, 2, 'nopal', 'mahasiswa', 'magang', 'ttd_1754268932_482.png', '2025-08-04 00:55:32', 'aa7aeb6a'),
(3, 2, 'arifin', 'mahasiswa', 'magang', 'ttd_1754268954_572.png', '2025-08-04 00:55:54', 'aa7aeb6a'),
(4, 2, 'jevin dwi jayanto', 'sistem informasi', 'magang', 'ttd_1754268979_976.png', '2025-08-04 00:56:19', 'aa7aeb6a'),
(5, 2, 'farhan', 'mahasiswa', 'magang', 'ttd_1754269006_230.png', '2025-08-04 00:56:46', 'aa7aeb6a'),
(6, 2, 'agus santoso', 'mbo', 'qed', 'ttd_1754355856_216.png', '2025-08-05 01:04:16', 'aa7aeb6a'),
(7, 2, 'iwfbiwe', '', 'wyuefgue', 'ttd_1754357714_201.png', '2025-08-05 01:35:14', 'aa7aeb6a'),
(8, 2, 'uwIWRB', '', 'EF', 'ttd_1754357726_161.png', '2025-08-05 01:35:26', 'aa7aeb6a'),
(9, 2, 'UDUDU', '', 'SAFEQW', 'ttd_1754357738_204.png', '2025-08-05 01:35:38', 'aa7aeb6a'),
(10, 2, 'SWIQU', '', 'W4R', 'ttd_1754357754_742.png', '2025-08-05 01:35:54', 'aa7aeb6a'),
(11, 2, 'QAIEUFQIE', '', 'UOEQGQG', 'ttd_1754357786_660.png', '2025-08-05 01:36:26', 'aa7aeb6a'),
(12, 2, 'QWW', 'Q', 'QWWWWW', 'ttd_1754357798_252.png', '2025-08-05 01:36:38', 'aa7aeb6a'),
(13, 2, 'QQ', '', 'QQ', 'ttd_1754357807_665.png', '2025-08-05 01:36:47', 'aa7aeb6a'),
(14, 2, 'QQQ', '', 'QQQ', 'ttd_1754357816_774.png', '2025-08-05 01:36:56', 'aa7aeb6a'),
(15, 2, 'QQQQQ', 'QQQ', 'QQQQ', 'ttd_1754357829_251.png', '2025-08-05 01:37:09', 'aa7aeb6a'),
(17, 2, 'NAMA SAYA', 'galowg', 'TI', 'ttd_1754450954_823.png', '2025-08-06 03:29:14', 'aa7aeb6a'),
(18, 3, 'NFL', 'work', 'work', 'ttd_1754455337_273.png', '2025-08-06 04:42:17', '962d081b'),
(19, 2, 'asd', '123d', 'asdf', 'ttd_1754459908_239.png', '2025-08-06 05:58:28', 'aa7aeb6a'),
(20, 2, 'ghdg', '', 'sfer', 'ttd_1754459919_439.png', '2025-08-06 05:58:39', 'aa7aeb6a'),
(21, 2, 'ght', '32', 'dsgttr', 'ttd_1754459932_437.png', '2025-08-06 05:58:52', 'aa7aeb6a'),
(22, 2, 'fgyh', 'dfs', 'hkijds', 'ttd_1754459943_538.png', '2025-08-06 05:59:03', 'aa7aeb6a'),
(23, 2, 'errtg', 'k', 'gibg', 'ttd_1754459952_916.png', '2025-08-06 05:59:12', 'aa7aeb6a');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `roles` varchar(50) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `email`, `password`, `roles`) VALUES
(1, 'admin', 'algojo@gmail.com', '$2y$10$fOrdT79XHBi5wPYOBVxH9uH.Rewz.m9MRww6qhNCDZR4N63Zei/zG', 'admin'),
(2, 'papan', 'puserangin71@gmail.com', '$2y$10$6tEgUW949l9gcDV9FKNS4ePIQqLl.ov2gKbCILs37bExn/y/ouVym', 'superadmin'),
(3, 'admin-2', 'arifsmpn04@gmail.com', '$2y$10$jeqVKT10GjC6QAqTL08CrOCZGiFHD80uOgitaJccBqsS3Aas3RDoa', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id_kegiatan` int NOT NULL,
  `judul_kegiatan` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `token` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id_kegiatan`, `judul_kegiatan`, `tanggal`, `jam`, `token`) VALUES
(2, 'apel pagi', '2025-08-06', '14:00:00', 'aa7aeb6a'),
(3, 'rapat bpjs', '2025-08-06', '13:00:00', '962d081b');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `id_kegiatan` (`id_kegiatan`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_kegiatan`) REFERENCES `kegiatan` (`id_kegiatan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
