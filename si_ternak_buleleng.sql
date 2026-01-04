-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 22, 2025 at 01:57 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `si_ternak_buleleng`
--

-- --------------------------------------------------------

--
-- Table structure for table `desa`
--

CREATE TABLE `desa` (
  `id` int NOT NULL,
  `kecamatan_id` int DEFAULT NULL,
  `nama_desa` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desa`
--

INSERT INTO `desa` (`id`, `kecamatan_id`, `nama_desa`, `created_at`) VALUES
(1, 1, 'Tejakula', '2025-12-21 18:44:14'),
(2, 1, 'Sembiran', '2025-12-21 18:44:14'),
(3, 1, 'Pacung', '2025-12-21 18:44:14'),
(4, 1, 'Bondalem', '2025-12-21 18:44:14'),
(5, 1, 'Penuktukan', '2025-12-21 18:44:14'),
(6, 2, 'Kubutambahan', '2025-12-21 18:44:14'),
(7, 2, 'Menyali', '2025-12-21 18:44:14'),
(8, 2, 'Bila', '2025-12-21 18:44:14'),
(9, 2, 'Bengkala', '2025-12-21 18:44:14'),
(10, 2, 'Gunungsari', '2025-12-21 18:44:14'),
(11, 3, 'Sawan', '2025-12-21 18:44:14'),
(12, 3, 'Sangsit', '2025-12-21 18:44:14'),
(13, 3, 'Giri Emas', '2025-12-21 18:44:14'),
(14, 3, 'Lemukih', '2025-12-21 18:44:14'),
(15, 3, 'Sudaji', '2025-12-21 18:44:14'),
(16, 4, 'Banyuasri', '2025-12-21 18:44:14'),
(17, 4, 'Banyuning', '2025-12-21 18:44:14'),
(18, 4, 'Kaliuntu', '2025-12-21 18:44:14'),
(19, 4, 'Pancasari', '2025-12-21 18:44:14'),
(20, 4, 'Kampung Anyar', '2025-12-21 18:44:14'),
(21, 5, 'Sukasada', '2025-12-21 18:44:14'),
(22, 5, 'Pegayaman', '2025-12-21 18:44:14'),
(23, 5, 'Bebetin', '2025-12-21 18:44:14'),
(24, 5, 'Sambangan', '2025-12-21 18:44:14'),
(25, 5, 'Gitgit', '2025-12-21 18:44:14'),
(26, 6, 'Banjar', '2025-12-21 18:44:14'),
(27, 6, 'Banyuatis', '2025-12-21 18:44:14'),
(28, 6, 'Banyuseri', '2025-12-21 18:44:14'),
(29, 6, 'Cempaga', '2025-12-21 18:44:14'),
(30, 6, 'Dencarik', '2025-12-21 18:44:14'),
(31, 7, 'Seririt', '2025-12-21 18:44:14'),
(32, 7, 'Joanyar', '2025-12-21 18:44:14'),
(33, 7, 'Kalianget', '2025-12-21 18:44:14'),
(34, 7, 'Pangkungparuk', '2025-12-21 18:44:14'),
(35, 7, 'Patemon', '2025-12-21 18:44:14'),
(36, 8, 'Gerokgak', '2025-12-21 18:44:14'),
(37, 8, 'Pengulon', '2025-12-21 18:44:14'),
(38, 8, 'Patas', '2025-12-21 18:44:14'),
(39, 8, 'Penyabangan', '2025-12-21 18:44:14'),
(40, 8, 'Sumberklampok', '2025-12-21 18:44:14'),
(41, 9, 'Busung Biu', '2025-12-21 18:44:14'),
(42, 9, 'Bengkel', '2025-12-21 18:44:14'),
(43, 9, 'Bongancina', '2025-12-21 18:44:14'),
(44, 9, 'Kedis', '2025-12-21 18:44:14'),
(45, 9, 'Kekeran', '2025-12-21 18:44:14'),
(46, 6, 'mandasari', '2025-12-22 02:25:22'),
(47, 4, 'Bandung', '2025-12-22 02:31:20'),
(48, 4, 'test', '2025-12-22 02:55:53'),
(49, 4, 'jawabarat', '2025-12-22 03:38:30'),
(50, 6, 'waduh', '2025-12-22 03:46:05'),
(51, 4, 'bulanda', '2025-12-22 04:34:49'),
(52, 4, 'Sawan', '2025-12-22 13:53:33'),
(53, 6, 'Desa Sawan', '2025-12-22 13:54:37');

-- --------------------------------------------------------

--
-- Table structure for table `kecamatan`
--

CREATE TABLE `kecamatan` (
  `id` int NOT NULL,
  `nama_kecamatan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kecamatan`
--

INSERT INTO `kecamatan` (`id`, `nama_kecamatan`, `created_at`) VALUES
(1, 'Tejakula', '2025-12-21 16:41:20'),
(2, 'Kubutambahan', '2025-12-21 16:41:20'),
(3, 'Sawan', '2025-12-21 16:41:20'),
(4, 'Buleleng', '2025-12-21 16:41:20'),
(5, 'Sukasada', '2025-12-21 16:41:20'),
(6, 'Banjar', '2025-12-21 16:41:20'),
(7, 'Seririt', '2025-12-21 16:41:20'),
(8, 'Gerokgak', '2025-12-21 16:41:20'),
(9, 'Busung Biu', '2025-12-21 16:41:20');

-- --------------------------------------------------------

--
-- Table structure for table `pemotongan`
--

CREATE TABLE `pemotongan` (
  `id` int NOT NULL,
  `kecamatan_id` int DEFAULT NULL,
  `jenis_hewan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jantan` int DEFAULT '0',
  `betina` int DEFAULT '0',
  `total` int DEFAULT '0',
  `tanggal_pemotongan` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemotongan`
--

INSERT INTO `pemotongan` (`id`, `kecamatan_id`, `jenis_hewan`, `jantan`, `betina`, `total`, `tanggal_pemotongan`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 9, 'sapi', 10, 0, 10, '2025-12-22', 7, '2025-12-22 13:54:03', '2025-12-22 13:54:03'),
(9, 9, 'kerbau', 0, 10, 10, '2025-12-22', 7, '2025-12-22 13:54:03', '2025-12-22 13:54:03'),
(10, 9, 'kambing', 0, 5, 5, '2025-12-22', 7, '2025-12-22 13:54:03', '2025-12-22 13:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `penyakit_hewan`
--

CREATE TABLE `penyakit_hewan` (
  `id` int NOT NULL,
  `jenis_ternak` enum('sapi','kambing','ayam','bebek') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bulan` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Format: YYYY-MM',
  `minggu_ke` tinyint DEFAULT NULL COMMENT '1-4 (opsional)',
  `jenis_penyakit` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kasus_digital` int DEFAULT '0' COMMENT 'Jumlah kasus digital',
  `sampel_positif` int DEFAULT '0' COMMENT 'Jumlah sampel positif',
  `sampel_negatif` int DEFAULT '0' COMMENT 'Jumlah sampel negatif',
  `total_sampel` int DEFAULT '0' COMMENT 'Total sampel (positif+negatif)',
  `virus_teridentifikasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Data virus yang berkembang',
  `lokasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_penanganan` enum('dalam_pengawasan','dalam_penanganan','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'dalam_pengawasan',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penyakit_hewan`
--

INSERT INTO `penyakit_hewan` (`id`, `jenis_ternak`, `bulan`, `minggu_ke`, `jenis_penyakit`, `kasus_digital`, `sampel_positif`, `sampel_negatif`, `total_sampel`, `virus_teridentifikasi`, `lokasi`, `status_penanganan`, `catatan`, `created_by`, `created_at`, `updated_at`) VALUES
(14, 'kambing', '2025-12', 2, 'Rabies', 30, 20, 10, 30, 'Rabies', 'buleleng', 'dalam_pengawasan', '', 7, '2025-12-22 13:55:29', '2025-12-22 13:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `peternakan`
--

CREATE TABLE `peternakan` (
  `id` int NOT NULL,
  `nama_unit_usaha` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_peternakan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `desa_id` int DEFAULT NULL,
  `kecamatan_id` int DEFAULT NULL,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kapasitas_kandang` int DEFAULT NULL,
  `jumlah_populasi` int DEFAULT NULL,
  `kepemilikan` enum('Pribadi','Kemitraan','Kelompok') COLLATE utf8mb4_general_ci DEFAULT 'Pribadi',
  `bulan_panen` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peternakan`
--

INSERT INTO `peternakan` (`id`, `nama_unit_usaha`, `jenis_peternakan`, `alamat`, `desa_id`, `kecamatan_id`, `telepon`, `kapasitas_kandang`, `jumlah_populasi`, `kepemilikan`, `bulan_panen`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'Peternakan sari murni', 'sapi', 'kabupaten buleleng', 52, 4, '0832498234', 30, 20, 'Kemitraan', '11', 7, '2025-12-22 13:53:33', '2025-12-22 13:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `populasi_ternak`
--

CREATE TABLE `populasi_ternak` (
  `id` int NOT NULL,
  `kecamatan_id` int DEFAULT NULL,
  `desa_id` int DEFAULT NULL,
  `bulan` int NOT NULL,
  `tahun` year NOT NULL,
  `sapi_bali_jantan` int DEFAULT '0',
  `sapi_bali_betina` int DEFAULT '0',
  `sapi_bali_total` int DEFAULT '0',
  `sapi_lain_jantan` int DEFAULT '0',
  `sapi_lain_betina` int DEFAULT '0',
  `sapi_lain_total` int DEFAULT '0',
  `kerbau_jantan` int DEFAULT '0',
  `kerbau_betina` int DEFAULT '0',
  `kerbau_total` int DEFAULT '0',
  `kuda_jantan` int DEFAULT '0',
  `kuda_betina` int DEFAULT '0',
  `kuda_total` int DEFAULT '0',
  `babi_bali_induk` int DEFAULT '0',
  `babi_bali_betina` int DEFAULT '0',
  `babi_bali_jantan` int DEFAULT '0',
  `babi_bali_total` int DEFAULT '0',
  `babi_landrace_induk` int DEFAULT '0',
  `babi_landrace_betina` int DEFAULT '0',
  `babi_landrace_jantan` int DEFAULT '0',
  `babi_landrace_total` int DEFAULT '0',
  `kambing_potong_jantan` int DEFAULT '0',
  `kambing_potong_betina` int DEFAULT '0',
  `kambing_potong_total` int DEFAULT '0',
  `kambing_perah_jantan` int DEFAULT '0',
  `kambing_perah_betina` int DEFAULT '0',
  `kambing_perah_total` int DEFAULT '0',
  `kambing_total` int DEFAULT '0',
  `ayam_buras` int DEFAULT '0',
  `ayam_petelur` int DEFAULT '0',
  `ayam_pedaging` int DEFAULT '0',
  `ayam_total` int DEFAULT '0',
  `bebek_itik` int DEFAULT '0',
  `bebek_manila` int DEFAULT '0',
  `bebek_total` int DEFAULT '0',
  `unggas_total` int DEFAULT '0',
  `anjing_total` int DEFAULT '0',
  `total_semua` int DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `populasi_ternak`
--

INSERT INTO `populasi_ternak` (`id`, `kecamatan_id`, `desa_id`, `bulan`, `tahun`, `sapi_bali_jantan`, `sapi_bali_betina`, `sapi_bali_total`, `sapi_lain_jantan`, `sapi_lain_betina`, `sapi_lain_total`, `kerbau_jantan`, `kerbau_betina`, `kerbau_total`, `kuda_jantan`, `kuda_betina`, `kuda_total`, `babi_bali_induk`, `babi_bali_betina`, `babi_bali_jantan`, `babi_bali_total`, `babi_landrace_induk`, `babi_landrace_betina`, `babi_landrace_jantan`, `babi_landrace_total`, `kambing_potong_jantan`, `kambing_potong_betina`, `kambing_potong_total`, `kambing_perah_jantan`, `kambing_perah_betina`, `kambing_perah_total`, `kambing_total`, `ayam_buras`, `ayam_petelur`, `ayam_pedaging`, `ayam_total`, `bebek_itik`, `bebek_manila`, `bebek_total`, `unggas_total`, `anjing_total`, `total_semua`, `created_by`, `created_at`, `updated_at`) VALUES
(46, 3, 11, 12, '2025', 30, 0, 30, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 30, 7, '2025-12-22 13:51:12', '2025-12-22 13:51:12'),
(47, 3, 12, 12, '2025', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, '2025-12-22 13:51:12', '2025-12-22 13:51:12'),
(48, 3, 13, 12, '2025', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, '2025-12-22 13:51:12', '2025-12-22 13:51:12'),
(49, 3, 14, 12, '2025', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, '2025-12-22 13:51:12', '2025-12-22 13:51:12'),
(50, 3, 15, 12, '2025', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, '2025-12-22 13:51:12', '2025-12-22 13:51:12');

--
-- Triggers `populasi_ternak`
--
DELIMITER $$
CREATE TRIGGER `calculate_totals_before_insert` BEFORE INSERT ON `populasi_ternak` FOR EACH ROW BEGIN
    -- Hitung total sapi bali
    SET NEW.sapi_bali_total = NEW.sapi_bali_jantan + NEW.sapi_bali_betina;
    
    -- Hitung total sapi lain
    SET NEW.sapi_lain_total = NEW.sapi_lain_jantan + NEW.sapi_lain_betina;
    
    -- Hitung total kerbau
    SET NEW.kerbau_total = NEW.kerbau_jantan + NEW.kerbau_betina;
    
    -- Hitung total kuda
    SET NEW.kuda_total = NEW.kuda_jantan + NEW.kuda_betina;
    
    -- Hitung total babi bali
    SET NEW.babi_bali_total = NEW.babi_bali_induk + NEW.babi_bali_betina + NEW.babi_bali_jantan;
    
    -- Hitung total babi landrace
    SET NEW.babi_landrace_total = NEW.babi_landrace_induk + NEW.babi_landrace_betina + NEW.babi_landrace_jantan;
    
    -- Hitung total kambing potong
    SET NEW.kambing_potong_total = NEW.kambing_potong_jantan + NEW.kambing_potong_betina;
    
    -- Hitung total kambing perah
    SET NEW.kambing_perah_total = NEW.kambing_perah_jantan + NEW.kambing_perah_betina;
    
    -- Hitung total kambing
    SET NEW.kambing_total = NEW.kambing_potong_total + NEW.kambing_perah_total;
    
    -- Hitung total ayam
    SET NEW.ayam_total = NEW.ayam_buras + NEW.ayam_petelur + NEW.ayam_pedaging;
    
    -- Hitung total bebek
    SET NEW.bebek_total = NEW.bebek_itik + NEW.bebek_manila;
    
    -- Hitung total unggas
    SET NEW.unggas_total = NEW.ayam_total + NEW.bebek_total;
    
    -- Hitung total semua
    SET NEW.total_semua = NEW.sapi_bali_total + NEW.sapi_lain_total + NEW.kerbau_total + 
                         NEW.kuda_total + NEW.babi_bali_total + NEW.babi_landrace_total + 
                         NEW.kambing_total + NEW.unggas_total + NEW.anjing_total;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_totals_before_update` BEFORE UPDATE ON `populasi_ternak` FOR EACH ROW BEGIN
    -- Hitung total sapi bali
    SET NEW.sapi_bali_total = NEW.sapi_bali_jantan + NEW.sapi_bali_betina;
    
    -- Hitung total sapi lain
    SET NEW.sapi_lain_total = NEW.sapi_lain_jantan + NEW.sapi_lain_betina;
    
    -- Hitung total kerbau
    SET NEW.kerbau_total = NEW.kerbau_jantan + NEW.kerbau_betina;
    
    -- Hitung total kuda
    SET NEW.kuda_total = NEW.kuda_jantan + NEW.kuda_betina;
    
    -- Hitung total babi bali
    SET NEW.babi_bali_total = NEW.babi_bali_induk + NEW.babi_bali_betina + NEW.babi_bali_jantan;
    
    -- Hitung total babi landrace
    SET NEW.babi_landrace_total = NEW.babi_landrace_induk + NEW.babi_landrace_betina + NEW.babi_landrace_jantan;
    
    -- Hitung total kambing potong
    SET NEW.kambing_potong_total = NEW.kambing_potong_jantan + NEW.kambing_potong_betina;
    
    -- Hitung total kambing perah
    SET NEW.kambing_perah_total = NEW.kambing_perah_jantan + NEW.kambing_perah_betina;
    
    -- Hitung total kambing
    SET NEW.kambing_total = NEW.kambing_potong_total + NEW.kambing_perah_total;
    
    -- Hitung total ayam
    SET NEW.ayam_total = NEW.ayam_buras + NEW.ayam_petelur + NEW.ayam_pedaging;
    
    -- Hitung total bebek
    SET NEW.bebek_total = NEW.bebek_itik + NEW.bebek_manila;
    
    -- Hitung total unggas
    SET NEW.unggas_total = NEW.ayam_total + NEW.bebek_total;
    
    -- Hitung total semua
    SET NEW.total_semua = NEW.sapi_bali_total + NEW.sapi_lain_total + NEW.kerbau_total + 
                         NEW.kuda_total + NEW.babi_bali_total + NEW.babi_landrace_total + 
                         NEW.kambing_total + NEW.unggas_total + NEW.anjing_total;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produksi`
--

CREATE TABLE `produksi` (
  `id` int NOT NULL,
  `nama_peternak` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_peternakan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_pakan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `produksi_susu` decimal(10,2) DEFAULT NULL,
  `produksi_daging` decimal(10,2) DEFAULT NULL,
  `produksi_telur` int DEFAULT NULL,
  `biaya_produksi` decimal(12,2) DEFAULT NULL,
  `harga_jual` decimal(12,2) DEFAULT NULL,
  `keuntungan` decimal(12,2) DEFAULT NULL,
  `tanggal_produksi` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produksi`
--

INSERT INTO `produksi` (`id`, `nama_peternak`, `jenis_peternakan`, `jenis_pakan`, `produksi_susu`, `produksi_daging`, `produksi_telur`, `biaya_produksi`, `harga_jual`, `keuntungan`, `tanggal_produksi`, `created_by`, `created_at`) VALUES
(8, 'Sadam Bany', 'ayam_petelur', 'Pur ayam', 0.00, 0.00, 50, 10000.00, 30000.00, 20000.00, '2025-12-22', 7, '2025-12-22 13:52:21');

-- --------------------------------------------------------

--
-- Table structure for table `survei_pasar`
--

CREATE TABLE `survei_pasar` (
  `id` int NOT NULL,
  `tanggal_survei` date NOT NULL,
  `lokasi_pasar` enum('Pasar Banyuasri','Pasar Anyar','Pasar Buleleng') COLLATE utf8mb4_general_ci NOT NULL,
  `komoditas` enum('Daging Babi','Daging Sapi','Daging Ayam') COLLATE utf8mb4_general_ci NOT NULL,
  `harga_ayam_utuh` decimal(10,2) DEFAULT '0.00',
  `harga_dada_ayam` decimal(10,2) DEFAULT '0.00',
  `harga_babi_utuh` decimal(10,2) DEFAULT '0.00',
  `harga_balung_babi` decimal(10,2) DEFAULT '0.00',
  `harga_babi_isi` decimal(10,2) DEFAULT '0.00',
  `harga_balung_sapi` decimal(10,2) DEFAULT '0.00',
  `harga_sapi_isi` decimal(10,2) DEFAULT '0.00',
  `nama_surveilens` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survei_pasar`
--

INSERT INTO `survei_pasar` (`id`, `tanggal_survei`, `lokasi_pasar`, `komoditas`, `harga_ayam_utuh`, `harga_dada_ayam`, `harga_babi_utuh`, `harga_balung_babi`, `harga_babi_isi`, `harga_balung_sapi`, `harga_sapi_isi`, `nama_surveilens`, `nomor_hp`, `catatan`, `created_by`, `created_at`, `updated_at`) VALUES
(9, '2025-12-22', 'Pasar Banyuasri', 'Daging Ayam', 40000.00, 30000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Abdullah', '08324234', 'Ayam segar dan bagus', 7, '2025-12-22 13:50:11', '2025-12-22 06:50:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `username`, `password`, `phone`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@siternak.com', 'admin', '$2y$10$beGZ33hCB85H6ufQLGrUpejObINTKI2A8bTISd7rvte3FAXMjbvsC', NULL, 'admin', 1, '2025-12-21 15:47:13', '2025-12-22 08:19:40'),
(7, 'sadam', 'sadam@gmail.com', 'sadam', '$2y$10$4NBuaD4peyKlp7wQDYWf6ejjR1ksO6LSVWTixhVx3Wes1z03oz7wS', '08432498234', 'user', 1, '2025-12-22 13:49:21', '2025-12-22 13:49:28');

-- --------------------------------------------------------

--
-- Table structure for table `users_contoh`
--

CREATE TABLE `users_contoh` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user','peternak') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_contoh`
--

INSERT INTO `users_contoh` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@siternak.com', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin', '2025-12-21 16:41:21', '2025-12-21 16:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `vaksinasi`
--

CREATE TABLE `vaksinasi` (
  `id` int NOT NULL,
  `nama_pemilik` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `desa_id` int DEFAULT NULL,
  `kecamatan_id` int DEFAULT NULL,
  `jenis_hewan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `umur_hewan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_vaksinasi` date DEFAULT NULL,
  `jenis_vaksin` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaksinasi`
--

INSERT INTO `vaksinasi` (`id`, `nama_pemilik`, `desa_id`, `kecamatan_id`, `jenis_hewan`, `umur_hewan`, `tanggal_vaksinasi`, `jenis_vaksin`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 'Sadam bany', 53, 6, 'sapi', '3 tahun', '2025-12-22', 'lainnya', 7, '2025-12-22 13:54:37', '2025-12-22 13:54:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `desa`
--
ALTER TABLE `desa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kecamatan_id` (`kecamatan_id`);

--
-- Indexes for table `kecamatan`
--
ALTER TABLE `kecamatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kecamatan` (`nama_kecamatan`);

--
-- Indexes for table `pemotongan`
--
ALTER TABLE `pemotongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kecamatan_id` (`kecamatan_id`),
  ADD KEY `idx_pemotongan_tanggal` (`tanggal_pemotongan`),
  ADD KEY `pemotongan_ibfk_2` (`created_by`);

--
-- Indexes for table `penyakit_hewan`
--
ALTER TABLE `penyakit_hewan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_penyakit_bulan` (`jenis_ternak`,`bulan`,`minggu_ke`,`jenis_penyakit`),
  ADD KEY `idx_bulan` (`bulan`),
  ADD KEY `idx_jenis_ternak` (`jenis_ternak`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- Indexes for table `peternakan`
--
ALTER TABLE `peternakan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `desa_id` (`desa_id`),
  ADD KEY `kecamatan_id` (`kecamatan_id`),
  ADD KEY `peternakan_ibfk_3` (`created_by`);

--
-- Indexes for table `populasi_ternak`
--
ALTER TABLE `populasi_ternak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_populasi` (`kecamatan_id`,`desa_id`,`bulan`,`tahun`),
  ADD KEY `idx_periode` (`tahun`,`bulan`),
  ADD KEY `idx_wilayah` (`kecamatan_id`,`desa_id`),
  ADD KEY `idx_kecamatan` (`kecamatan_id`),
  ADD KEY `idx_desa` (`desa_id`),
  ADD KEY `populasi_ternak_ibfk_3` (`created_by`);

--
-- Indexes for table `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produksi_ibfk_1` (`created_by`);

--
-- Indexes for table `survei_pasar`
--
ALTER TABLE `survei_pasar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal_survei`),
  ADD KEY `idx_lokasi` (`lokasi_pasar`),
  ADD KEY `idx_komoditas` (`komoditas`),
  ADD KEY `survei_pasar_ibfk_1` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users_contoh`
--
ALTER TABLE `users_contoh`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vaksinasi`
--
ALTER TABLE `vaksinasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `desa_id` (`desa_id`),
  ADD KEY `kecamatan_id` (`kecamatan_id`),
  ADD KEY `vaksinasi_ibfk_3` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `desa`
--
ALTER TABLE `desa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `kecamatan`
--
ALTER TABLE `kecamatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pemotongan`
--
ALTER TABLE `pemotongan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `penyakit_hewan`
--
ALTER TABLE `penyakit_hewan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `peternakan`
--
ALTER TABLE `peternakan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `populasi_ternak`
--
ALTER TABLE `populasi_ternak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `survei_pasar`
--
ALTER TABLE `survei_pasar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users_contoh`
--
ALTER TABLE `users_contoh`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vaksinasi`
--
ALTER TABLE `vaksinasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `desa`
--
ALTER TABLE `desa`
  ADD CONSTRAINT `desa_ibfk_1` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`);

--
-- Constraints for table `pemotongan`
--
ALTER TABLE `pemotongan`
  ADD CONSTRAINT `pemotongan_ibfk_1` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`),
  ADD CONSTRAINT `pemotongan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `penyakit_hewan`
--
ALTER TABLE `penyakit_hewan`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `peternakan`
--
ALTER TABLE `peternakan`
  ADD CONSTRAINT `peternakan_ibfk_1` FOREIGN KEY (`desa_id`) REFERENCES `desa` (`id`),
  ADD CONSTRAINT `peternakan_ibfk_2` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`),
  ADD CONSTRAINT `peternakan_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `populasi_ternak`
--
ALTER TABLE `populasi_ternak`
  ADD CONSTRAINT `populasi_ternak_ibfk_1` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`),
  ADD CONSTRAINT `populasi_ternak_ibfk_2` FOREIGN KEY (`desa_id`) REFERENCES `desa` (`id`),
  ADD CONSTRAINT `populasi_ternak_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `produksi_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `survei_pasar`
--
ALTER TABLE `survei_pasar`
  ADD CONSTRAINT `survei_pasar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vaksinasi`
--
ALTER TABLE `vaksinasi`
  ADD CONSTRAINT `vaksinasi_ibfk_1` FOREIGN KEY (`desa_id`) REFERENCES `desa` (`id`),
  ADD CONSTRAINT `vaksinasi_ibfk_2` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`),
  ADD CONSTRAINT `vaksinasi_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
