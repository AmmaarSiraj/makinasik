-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jan 2026 pada 05.17
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `makinasik`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aturan_periode`
--

CREATE TABLE `aturan_periode` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `periode` varchar(7) NOT NULL,
  `batas_honor` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `aturan_periode`
--

INSERT INTO `aturan_periode` (`id`, `periode`, `batas_honor`, `created_at`, `updated_at`) VALUES
(6, '2025', 2500000.00, NULL, '2025-12-05 00:23:16'),
(9, '2027', 3000000.00, NULL, '2025-12-05 00:38:25'),
(10, '2026', 3500000.00, NULL, '2025-12-05 00:59:44'),
(11, '2028', 2500000.00, NULL, '2025-12-08 00:16:11'),
(12, '2029', 2000000.00, '2025-12-15 02:12:02', '2025-12-15 02:49:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `honorarium`
--

CREATE TABLE `honorarium` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_subkegiatan` varchar(50) NOT NULL,
  `kode_jabatan` varchar(50) NOT NULL,
  `tarif` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_satuan` int(11) NOT NULL,
  `basis_volume` int(11) NOT NULL DEFAULT 1,
  `beban_anggaran` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `honorarium`
--

INSERT INTO `honorarium` (`id`, `id_subkegiatan`, `kode_jabatan`, `tarif`, `id_satuan`, `basis_volume`, `beban_anggaran`, `created_at`, `updated_at`) VALUES
(8, 'sub10', 'PETA-S01', 12000.00, 8, 1, NULL, NULL, NULL),
(9, 'sub10', 'IT-02', 120000.00, 5, 1, NULL, NULL, NULL),
(10, 'sub11', 'SUP-B01', 3000.00, 8, 1, NULL, NULL, NULL),
(11, 'sub12', 'SUP-B01', 2000.00, 8, 1, NULL, NULL, NULL),
(12, 'sub2', 'ED-01', 200000.00, 5, 60, NULL, NULL, NULL),
(13, 'sub2', 'PML-01', 400000.00, 8, 100, NULL, NULL, NULL),
(14, 'sub3', 'VAL-01', 50000.00, 5, 30, NULL, NULL, NULL),
(15, 'sub3', 'ANL-01', 1000000.00, 8, 120, NULL, NULL, NULL),
(34, 'sub4', 'IT-02', 12000.00, 5, 13, '2903.BMA.009.005.521213', NULL, NULL),
(51, 'sub4', 'PPL-01', 100000.00, 5, 130, '2903.BMA.009.005.521213', NULL, NULL),
(52, 'sub5', 'PML-092', 80000.00, 5, 130, '2903.BMA.009.005.521213', NULL, NULL),
(53, 'sub6', 'ENT-01', 10000.00, 5, 130, '2903.BMA.009.005.521213', NULL, NULL),
(54, 'sub7', 'SUP-B01', 10000.00, 5, 130, '2903.BMA.009.005.521213', NULL, NULL),
(55, 'sub8', 'PPL-02', 120000.00, 5, 40, '20905', NULL, NULL),
(56, 'sub8', 'PML-092', 20000.00, 5, 40, '20905', NULL, NULL),
(57, 'sub9', 'PML-092', 100000.00, 5, 10000, NULL, NULL, NULL),
(58, 'sub13', 'ANL-01', 1230000.00, 5, 120, '2903', '2025-12-14 19:16:09', '2025-12-14 19:16:09'),
(59, 'sub14', 'SUP-B01', 120.00, 5, 1000, '2093', '2025-12-14 19:16:10', '2025-12-14 19:16:10'),
(60, 'sub14', 'KOSEKA-02', 200000.00, 5, 123, NULL, '2025-12-14 19:16:11', '2025-12-14 19:16:11'),
(61, 'sub15', 'PML-01', 120000.00, 5, 3, '890', '2025-12-15 01:43:54', '2025-12-15 01:43:54'),
(62, 'sub15', 'ppl-03', 220000.00, 5, 12, NULL, '2025-12-15 01:43:54', '2025-12-15 01:43:54'),
(63, 'sub16', 'PML-01', 120.00, 8, 120, NULL, '2025-12-18 06:13:56', '2025-12-18 06:13:56'),
(64, 'sub16', 'PML-092', 120.00, 8, 120, NULL, '2025-12-18 06:13:56', '2025-12-18 06:13:56'),
(65, 'sub16', 'PML Survei', 120.00, 8, 120, NULL, '2025-12-18 06:13:57', '2025-12-18 06:13:57'),
(66, 'sub16', 'PPL Survei', 120.00, 8, 120, NULL, '2025-12-18 06:13:57', '2025-12-18 06:13:57'),
(67, 'sub17', 'PPL Survei', 55000.00, 5, 1, '054.01.019021.521213', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
(68, 'sub17', 'PML Survei', 19000.00, 5, 1, '054.01.019021.521213', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
(69, 'sub18', 'PPL Survei', 173000.00, 8, 1, '054.01.019021.521213', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
(70, 'sub18', 'PML Survei', 57000.00, 8, 1, '054.01.019021.521213', '2025-12-30 18:26:24', '2025-12-30 18:26:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan_mitra`
--

CREATE TABLE `jabatan_mitra` (
  `kode_jabatan` varchar(50) NOT NULL,
  `nama_jabatan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `jabatan_mitra`
--

INSERT INTO `jabatan_mitra` (`kode_jabatan`, `nama_jabatan`, `created_at`, `updated_at`) VALUES
('ANL-01', 'Analis Data Ekonomi', NULL, NULL),
('CB-01', 'Coba 1', '2025-12-15 02:05:12', '2025-12-15 02:05:12'),
('DRF-01', 'Drafter Laporan', NULL, NULL),
('ED-01', 'Editor Dokumen', NULL, NULL),
('ENT-01', 'Operator Data Entry', NULL, NULL),
('ENUM-B01', 'Enumerator Ikan', NULL, NULL),
('IT-02', 'Operator Data Entry', NULL, NULL),
('KOSEKA-02', 'Koordinator Sensus Kecamatan', NULL, NULL),
('PETA-B01', 'Petugas Pemetaan', NULL, NULL),
('PETA-S01', 'Petugas Pemetaan', NULL, NULL),
('PML Survei', 'Petugas Pemeriksaan Lapangan', '2025-12-18 05:53:31', '2025-12-18 05:53:31'),
('PML-01', 'Petugas Pemeriksa ', NULL, NULL),
('PML-092', 'Petugas Pemeriksaan', NULL, NULL),
('PPL Survei', 'Petugas Pendataan Lapangan', '2025-12-18 05:46:20', '2025-12-18 05:46:20'),
('PPL-01', 'Petugas Pencacah Lapangan', NULL, NULL),
('PPL-02', 'Petugas Pendataan', NULL, NULL),
('ppl-03', 'Petugas Pendataan', NULL, NULL),
('SRV-B01', 'Surveyor Perikanan', NULL, NULL),
('SUP-B01', 'Supervisor Pemetaan', NULL, NULL),
('VAL-01', 'Validator Data', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kegiatan` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama_kegiatan`, `deskripsi`, `created_at`, `updated_at`) VALUES
(6, 'survei penduduk', 'survei kependudukan', '2025-11-27 06:40:26', NULL),
(7, 'Sensus Ekonomi', 'sensus ekonomi', '2025-11-28 07:03:02', NULL),
(8, 'SAKERNAS februari 2026', '', '2025-12-04 20:31:42', NULL),
(9, 'sensus penduduk', '', '2025-12-11 19:57:40', NULL),
(10, '(SAKERNAS26-TW) SURVEI ANGKATAN KERJA NASIONAL (SAKERNAS) TAHUN 2026', 'Imported via Excel', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
(11, 'perikanan', 'perikanan', '2026-01-22 21:11:14', '2026-01-22 21:11:14'),
(12, 'perikanan', 'perikanan', '2026-01-22 21:12:02', '2026-01-22 21:12:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelompok_penugasan`
--

CREATE TABLE `kelompok_penugasan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_penugasan` bigint(20) UNSIGNED NOT NULL,
  `id_mitra` bigint(20) UNSIGNED NOT NULL,
  `kode_jabatan` varchar(50) DEFAULT NULL,
  `volume_tugas` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kelompok_penugasan`
--

INSERT INTO `kelompok_penugasan` (`id`, `id_penugasan`, `id_mitra`, `kode_jabatan`, `volume_tugas`, `created_at`) VALUES
(11, 24, 188, 'VAL-01', 53, '2025-12-04 07:35:43'),
(17, 26, 188, 'ENT-01', 20, '2025-12-04 21:02:33'),
(22, 26, 322, 'ENT-01', 40, '2025-12-11 04:42:49'),
(24, 26, 194, 'ENT-01', 1, '2025-12-11 02:41:23'),
(25, 26, 257, 'ENT-01', 14, '2025-12-11 02:50:28'),
(28, 26, 320, 'ENT-01', 12, '2025-12-11 02:51:37'),
(29, 27, 349, 'PPL-01', 12, '2025-12-11 20:00:02'),
(30, 27, 320, 'PPL-01', 11, '2025-12-11 20:00:02'),
(31, 27, 257, 'IT-02', 12, '2025-12-11 20:00:27'),
(32, 26, 186, 'ENT-01', 123, '2025-12-11 20:35:59'),
(33, 28, 286, 'PML-092', 10, '2025-12-11 20:36:55'),
(34, 29, 349, 'PML-092', 2, '2025-12-11 20:41:45'),
(35, 29, 320, 'PML-092', 15, '2025-12-14 05:58:41'),
(36, 29, 236, 'PML-092', 3, '2025-12-14 06:30:56'),
(38, 29, 233, 'PML-092', 7, '2025-12-14 21:17:11'),
(39, 30, 349, 'SUP-B01', 3, '2025-12-14 21:40:24'),
(41, 30, 208, 'SUP-B01', 126, '2025-12-14 22:46:21'),
(44, 30, 320, 'SUP-B01', 1, '2025-12-15 05:58:12'),
(46, 33, 233, 'ppl-03', 3, '2025-12-16 19:57:45'),
(47, 34, 349, 'KOSEKA-02', 2, '2025-12-17 08:21:49'),
(48, 35, 349, 'PML Survei', 1, '2026-01-07 08:22:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelompok_perencanaan`
--

CREATE TABLE `kelompok_perencanaan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_perencanaan` bigint(20) UNSIGNED NOT NULL,
  `id_mitra` bigint(20) UNSIGNED NOT NULL,
  `kode_jabatan` varchar(50) DEFAULT NULL,
  `volume_tugas` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kelompok_perencanaan`
--

INSERT INTO `kelompok_perencanaan` (`id`, `id_perencanaan`, `id_mitra`, `kode_jabatan`, `volume_tugas`, `created_at`) VALUES
(11, 24, 188, 'ED-01', 5, '2025-12-04 07:35:43'),
(33, 28, 286, 'PML-092', 44, '2025-12-11 20:36:55'),
(35, 30, 200, 'ANL-01', 1, '2025-12-16 21:40:49'),
(36, 30, 194, 'ANL-01', 1, '2025-12-16 21:41:22'),
(37, 31, 349, 'PPL-01', 20, '2025-12-17 05:31:09'),
(38, 31, 235, 'IT-02', 132, '2025-12-17 05:31:09'),
(39, 31, 292, 'PPL-01', 13, '2025-12-17 05:31:09'),
(40, 31, 184, 'IT-02', 30, '2025-12-17 05:31:09'),
(41, 32, 349, 'ppl-03', 13, '2025-12-17 05:32:01'),
(42, 33, 349, 'KOSEKA-02', 4, '2025-12-17 05:59:39'),
(45, 30, 180, 'ANL-01', 1, '2025-12-18 21:20:43'),
(46, 30, 349, 'ANL-01', 4, '2025-12-19 05:07:52'),
(49, 36, 344, 'PPL Survei', 1, '2025-12-30 18:28:13'),
(50, 36, 278, 'PML Survei', 13, '2025-12-30 18:28:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_template_spk`
--

CREATE TABLE `master_template_spk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_template` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Template Default/Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `master_template_spk`
--

INSERT INTO `master_template_spk` (`id`, `nama_template`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'template 2025', 0, '2025-12-08 09:32:49', NULL),
(2, 'template 2026', 0, '2025-12-08 22:41:14', NULL),
(3, 'template coba', 0, '2025-12-16 00:14:25', '2025-12-16 00:14:25'),
(4, 'tes', 0, '2025-12-16 00:15:14', '2025-12-16 00:15:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_13_044249_create_personal_access_tokens_table', 1),
(5, '2025_12_13_051917_create_user_table', 1),
(6, '2025_12_13_070032_create_kegiatan_table', 1),
(7, '2025_12_13_070032_create_subkegiatan_table', 1),
(8, '2025_12_13_084630_create_honorarium_dan_jabatan_table', 2),
(10, '2025_12_13_091349_create_aturan_periode_table', 4),
(11, '2025_12_13_092858_create_mitra_table', 5),
(14, '2025_12_13_130725_create_satuan_kegiatan_table', 7),
(15, '2025_12_13_131220_create_satuan_kegiatan_table', 8),
(17, '2025_12_13_133338_create_template_spk_tables', 10),
(18, '2025_12_13_085916_create_tahun_aktif_table', 11),
(20, '2025_12_13_093149_create_penugasan_tables', 12),
(21, '2025_12_13_094411_create_perencanaan_tables', 13),
(22, '2025_12_13_132351_create_spk_setting_table', 14),
(23, '2025_12_18_051746_add_status_to_penugasan_table', 15),
(24, '2025_12_20_083416_create_system_settings_table', 16);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mitra`
--

CREATE TABLE `mitra` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nik` varchar(50) NOT NULL,
  `sobat_id` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` varchar(10) DEFAULT NULL,
  `pendidikan` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `deskripsi_pekerjaan_lain` text DEFAULT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `mitra`
--

INSERT INTO `mitra` (`id`, `nama_lengkap`, `nik`, `sobat_id`, `alamat`, `jenis_kelamin`, `pendidikan`, `pekerjaan`, `deskripsi_pekerjaan_lain`, `nomor_hp`, `email`, `created_at`, `updated_at`) VALUES
(180, 'iuyruPraSganctsnim ', '6785111106208270', '337323110139', 'Jalan Argoloyo, RT 03/RW 11', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Freshgraduate', '+62 856-4992-8554', 'iraurnssicngyapmut@gmail.com', '2025-12-05 06:43:04', NULL),
(181, 'taraiwiKun nayAir', '7477580909333743', '337322090202', 'Jalan Ja\'far Shodiq', 'Pr', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 314-4815-820', 'aa0ngt207uanbe@gmail.com', '2025-12-05 06:43:04', NULL),
(182, 'jianrisaASa r nA', '2669562007198438', '337322040032', 'Pulutan kidul rt 3 rw 3', 'Pr', 'Tamat SMS/Sederajat', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 085-7999-01262', 'adad.dnbiqun@gmail.com', '2025-12-05 06:43:04', NULL),
(183, 'Frihiita as', '5679621102183587', '337322090179', 'Nobowetan Rt 4 Rw 5', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 859-1599-08434', 'rai277asifith@gmail.com', '2025-12-05 06:43:04', NULL),
(184, 'D jirAtiineaans', '4918523002559811', '337322090216', 'Perum Argomulyo Blok C94 RT 05 RW 10', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-2223-9697', 'l.fkayrndriahaiasa@gmail.com', '2025-12-05 06:43:04', NULL),
(185, 'esM liaelHneaar', '8560613005895391', '337322110006', 'Dsn. Gumukan RT 06 RW 02', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'Belum Bekerja', '+62 838-4276-0212', 'amil4eaeral72eshn@gmail.com', '2025-12-05 06:43:04', NULL),
(186, ' IIE GNAYOASMCSRREAG', '1148193002933798', '337322110009', 'JADI, RT 001/RW 004', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'BELUM BEKERJA', '+62 812-2698-0757', 'smcyar@gmail.com', '2025-12-05 06:43:04', NULL),
(187, 'fSbudariodMrn iaiRa ', '3817540606239302', '337324100021', 'Jl.Karang Taruna RT 05 RW 07 Turusan Kidul Salatiga', 'Lk', 'Tamat SMS/Sederajat', 'Lainnya', 'Kurir Paket dan Aktif Karang Taruna', '+62 858-5035-1972', 'daru7naamifr1sdi@gmail.com', '2025-12-05 06:43:04', NULL),
(188, 'AhW idunnyai', '2834742602101508', '337324100008', 'Jalan Abdul Syukur Cabean Rt03 rw14', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 878-3450-1382', 'Wa5hn3yiianniddiu@gmail.com', '2025-12-05 06:43:04', NULL),
(189, 'AIMCA RTISUAMS ALMA', '2452301905383764', '337322010010', 'JADI, RT. 001 RW. 004', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'MENGURUS RUMAH TANGGA', '+62 822-2671-3996', 'aido7r5iwma@gmail.com', '2025-12-05 06:43:04', NULL),
(190, 'ogisurtrntuarnislhyu oguo  ', '7924611101577285', '337322030007', 'Jl. Argowismo No 14 RT. 04 RW. 01', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 856-2730-434', 'ugv03grtuiann@gmail.com', '2025-12-05 06:43:04', NULL),
(191, 'AIAYRTINS', '9664151610066187', '337322010026', 'KLUMPIT RT 02 RW 01', 'Pr', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 088-9012-73651', 'iaSrwinytaNe@gmail.com', '2025-12-05 06:43:04', NULL),
(192, 'NIAADNFIAAP RUNR', '5073290502438774', '337322030005', 'Geneng RT.20 / RW.05, Kelurahan Medayu, Kecamatan Suruh, Kabupaten Semarang', 'Pr', 'Tamat D4/S1', 'Wiraswasta', 'Membantu Usaha Catering makanan', '+62 857-4179-3769', 'nparadnrau15@gmail.com', '2025-12-05 06:43:04', NULL),
(193, 'itial owtaiiinfAnR', '3233391105067700', '337322030003', 'Jl. Mayangsari I/5 RT 02 RW 02 Karangduwet Salatiga', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 856-4170-1269', '1in7i94r0er@gmail.com', '2025-12-05 06:43:05', NULL),
(194, ' DNASURSRII', '4185952110302261', '337322040025', 'JL.RANDUSARI II RT 03 RW 02', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 082-1357-04128', 'adeusncraiimrinsral@gmail.com', '2025-12-05 06:43:05', NULL),
(195, 'KA WAT SINSUAYMEIR TAUONTUKNYG', '6059301503818299', '337322090062', 'Jl. Karang taruna no18 turusan', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'Ibu rumah tangga', '+62 896-8789-1881', 'et2okary9un@gmail.com', '2025-12-05 06:43:05', NULL),
(196, 'ANOOSOOR MUYTHU', '5163801809085092', '337323030003', 'SUGIHWARAS NO. 43 RT 02 RW 05', 'Lk', 'Tamat D1/D2/D3', 'Lainnya', 'Freelance', '+62 856-4124-0785', 'y.65yroahrano@gmail.com', '2025-12-05 06:43:05', NULL),
(197, 'HNMSAADI', '7872391201869942', '337322110020', 'PERUM. GRIYA PATRA NO. E2 JL.YUDISTIRA GROGOL BARU RT.04 RW.08 DUKUH SIDOMUKTI SALATIGA', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 813-9048-6066', '_sdiimennaam@yahoo.com', '2025-12-05 06:43:05', NULL),
(198, 'uzuruMdoNhigmohmtrakaCiRa   ', '2438921407938546', '337323110133', 'Jalan Kaliagung, Kalilondo RT 02 04', 'Lk', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 851-5638-7295', 'i.izcrunrtka@gmail.com', '2025-12-05 06:43:05', NULL),
(199, 'aMvitSga itaY aaowhaeinrA ', '7329420912371139', '337323080020', 'Jl Jambu No.08 RT.02 RW.03 Kalicacing, Sidomukti, Salatiga', 'Pr', 'Tamat D4/S1', 'Wiraswasta', '', '+62 882-0070-90211', 'ras9a8y7i02a5m@gmail.com', '2025-12-05 06:43:05', NULL),
(200, 'iaraanummam hahbhaluasduiu l nhmdsdhy', '8383272508321302', '337323080026', 'Dliko sari 1, blotongan, sidorejo, salatiga', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 822-4224-1776', 'ialrrfysidasaa@gmail.com', '2025-12-05 06:43:05', NULL),
(201, 'irR feoSatnit', '5029591311309774', '337323080045', 'Belon RT.001/RW.010, Kelurahan Kumpulrejo, Kecamatan Argomulyo, Salatiga', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Guru honorer RA', '+62 882-2659-7922', '9i0ira7tfs9nteor@gmail.com', '2025-12-05 06:43:05', NULL),
(202, ' yanaikImData', '6565932104456393', '337324100010', 'Dusun Cabean Kulon RT 29 RW 06', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 851-5955-6956', 'ni40ta2damyk2aia@gmail.com', '2025-12-05 06:43:05', NULL),
(203, 'lukroastFhu hartaM', '4628991810638443', '337322010006', 'Jl Damarjati no 92, Rt 006 Rw 004', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 812-2895-8984', '3ahtktF0lau@gmail.com', '2025-12-05 06:43:05', NULL),
(204, 'wlziT dF aiowlnaaA', '7448880707265040', '337324100015', 'Jalan Argowismo 53 RT 007/001', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Belum Bekerja', '+62 856-4139-9560', 'adilzwafwa@gmail.com', '2025-12-05 06:43:05', NULL),
(205, 'anauiLc', '2789262112895106', '337322040006', 'Ngemplak RT 04 RW 09', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 823-2277-2642', 'ari4cwlu1an.@gmail.com', '2025-12-05 06:43:05', NULL),
(206, 'Ptnutast iawuiKr', '6953412105921275', '337322090072', 'Jl. Amarta Randuares', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 856-4166-8843', 'aw.ttnuitsrpuaik@gmail.com', '2025-12-05 06:43:05', NULL),
(207, 'RotitYaeSs aSrp t anoit', '6516951504011763', '337322090083', 'Jl. Imam Bonjol no. 26, Salatiga', 'Pr', 'Tamat D1/D2/D3', 'Wiraswasta', '', '+62 812-2879-541', 'erai6tsn2t@gmail.com', '2025-12-05 06:43:05', NULL),
(208, ' MhadFaauhimm', '3616912906828597', '337323110157', 'JL. ARGOWISMO RT 007 RW 001', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 877-4549-4954', '8mhuda78maf@gmail.com', '2025-12-05 06:43:05', NULL),
(209, 'eiLsgiurrT Nh aina', '1543432610725994', '337324100004', 'Jl. Purbaya IV/8 Karangalit', 'Pr', 'Tamat D4/S1', 'Pelajar / Mahasiswa', '', '+62 857-4338-9666', 'agarni8hniteusirl@gmail.com', '2025-12-05 06:43:05', NULL),
(210, 'ELWYL CR IN OAHTNBMPIAOLR', '2457301709362639', '337324100024', 'Jl. pundungsari GG.2 No.07 RT 01 RW 11', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Sedang mencari pekerjaan, jual pulsa', '+62 895-6200-99688', 'ehpm.nirblnyacl@gmail.com', '2025-12-05 06:43:05', NULL),
(211, 'AtiudonyR radi', '7397221802656401', '332222090951', 'Dusun Golo, RT 35/ RW 08.', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 881-2479-654', 'adariuyodtri0n1@gmail.com', '2025-12-05 06:43:05', NULL),
(212, 'WIRASSU', '9635250403797330', '337322040023', 'JL KEMIRI RAYA NO 11 RT 02 RW 09 KEMIRI SALATIGA', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-2748-1288', '.ua671srwsi@gmail.com', '2025-12-05 06:43:05', NULL),
(213, 'NMTRAW AAAIEHV', '2652282808059353', '337322010007', 'Jalan joyo imron no 8 rt 03 rw 01 cabean', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 815-6133-767', 'eranmhVa@gmail.com', '2025-12-05 06:43:05', NULL),
(214, 'mKornhraam gusounruiB', '7453471004281249', '337322040001', 'Jln Raden Fatah no 02 kalibening', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Guru', '+62 085-7419-83827', '6rni791gn@gmail.com', '2025-12-05 06:43:05', NULL),
(215, 'ianaazArh nAi rdavSf', '6946701808671131', '337324100001', 'Jalan canden RT 09/RW 03', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 813-9179-7761', 'aadin5r6nvahAz@gmail.com', '2025-12-05 06:43:05', NULL),
(216, 'akyoauuRtbhtRiii ', '2787542806089571', '337322090026', 'Jl Kiai Condro No 2', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 081-6633-866', 'tuiotbiykh.iararu@gmail.com', '2025-12-05 06:43:05', NULL),
(217, 'u ktimantiSr', '2513322205867689', '337322090063', 'Grogol blotongan rt 02 rw 07', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'Ibu rumah tangga', '+62 838-4254-9955', 'onmotax4@gmail.com', '2025-12-05 06:43:05', NULL),
(218, 'oirnaamshu poun ulM', '5831530502437858', '337322090059', 'Winong, Rt.02, Rw.01', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 813-8935-6737', '2lui5ausManh@gmail.com', '2025-12-05 06:43:05', NULL),
(219, 'a uuSNghegoutnfsro Ang', '4286192909164088', '337322090094', 'Jl.Merapi No 22b RT 02 RW 05', 'Lk', 'Tamat D1/D2/D3', 'Lainnya', 'Freelance', '+62 085-7127-99891', 'af9susggtenu1na@gmail.com', '2025-12-05 06:43:05', NULL),
(220, 'peiSsaWuyh tai', '8186662609651304', '337322090180', 'Jalan Perengrejo no 23B rt 09 rw 03', 'Pr', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 831-0102-7312', 'iaauly0ve7@gmail.com', '2025-12-05 06:43:05', NULL),
(221, 'ahauNnahKrs ', '4821662003143220', '337322090163', 'Perum Manunggal II Blok B6 Rt.002/007', 'Pr', 'Tamat SMS/Sederajat', 'Lainnya', 'Driver Jeggboy & girl', '+62 895-4212-91199', '7irn7n8unkd@gmail.com', '2025-12-05 06:43:05', NULL),
(222, 'Nlcwvaaanooaksi si teyn', '3169170911034015', '337323110163', 'Jl jambu no8 , rt2 rw3', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 821-3389-6623', 'oancvoikY@gmail.com', '2025-12-05 06:43:05', NULL),
(223, ' nni asaAuriiDniKwsra', '9227942008154692', '337323110158', 'Sukoharjo RT 01 RW 06', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 831-6257-2975', 'sink0ada8n@gmail.com', '2025-12-05 06:43:05', NULL),
(224, 'intaamEmaDraw  vibaA', '6541851503987322', '337323110078', 'Dusun Banaran', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 882-3819-1707', 've7amadain@gmail.com', '2025-12-05 06:43:05', NULL),
(225, 'arEtZtnkuiPa iiaw ', '8826101306028863', '332223110790', 'Jl. Arjuna RT 4 RW 5 Dukuh, Sidomukti, Salatiga', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Mengurus Rumah Tangga', '+62 838-9396-3686', 'r8naiii9ewt@gmail.com', '2025-12-05 06:43:05', NULL),
(226, 'nyiWStei aiij', '6452982602188258', '337322030010', 'Druju RT 01 RW 03', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-2737-3473', '3al2siqqfbaiy@gmail.com', '2025-12-05 06:43:05', NULL),
(227, 'tzfailum auddMU', '2273522212015968', '337322090061', 'Isep-Isep RT 01 RW 03 Cebongan Argomulyo', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 882-0067-64609', 'uatlafmdduizmu@gmail.com', '2025-12-05 06:43:05', NULL),
(228, 'u ditiikYiuatWsl', '7397572607672031', '337322040028', 'Sugihwaras RT 2 RW 5', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Guru TK', '+62 856-4086-1997', 'd0tuiti4iklwiyuas@gmail.com', '2025-12-05 06:43:05', NULL),
(229, 'MAIUDN', '6117191607219848', '337322090155', 'Jl. Sardulo Macanan', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 896-6566-6716', 'acmalpunf.ola3s@gmail.com', '2025-12-05 06:43:05', NULL),
(230, 'Sptahurniu', '8233810408778536', '337322090136', 'Jl ngadisari Rt 08 Rw 04 Tegalrejo', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 897-6636-341', '83iuaSutp0hnr@gmail.com', '2025-12-05 06:43:05', NULL),
(231, 'rlnhatareyi alt siiDaf', '5089902510655940', '337322090078', 'Canden rt05 rw03', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 082-3281-08030', '10nLifaa1adnyf5@gmail.com', '2025-12-05 06:43:05', NULL),
(232, 'htFwiaIarj aadn', '1317550604565905', '337322090112', 'Jl sawo gang durian RT 01 RW 04', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 858-6523-3169', 'o0i31hahrdgn2nou@gmail.com', '2025-12-05 06:43:05', NULL),
(233, ' iirstKiantnwSaa', '6128821211531558', '337322090206', 'Jl. Argoyuwono 31b rt. 08 rw.01 ringinawe ledok', 'Pr', 'Tamat D4/S1', 'Wiraswasta', '', '+62 838-3885-1264', 'sn4krSina@gmail.com', '2025-12-05 06:43:05', NULL),
(234, 'ruYlura mIila aminAna', '6883932912444895', '337323110121', 'Perum Sehati, Blok C.82, RT 08/RW 14', 'Pr', 'Tamat D4/S1', 'Lainnya', 'fresh graduate', '+62 856-4238-3257', 'emailyunniea@gmail.com', '2025-12-05 06:43:05', NULL),
(235, 'A THJDNORRAUI', '5957631410138946', '337322010022', 'Jalan Kota baru 3/176 Salatiga', 'Lk', 'Tamat D4/S1', 'Lainnya', '_', '+62 815-6639-080', 'dahtjrinroua@gmail.com', '2025-12-05 06:43:05', NULL),
(236, 'adAinailNo tYv ui', '2946920605213170', '337322040046', 'Pamot', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 822-6506-5068', 'oyaulivdna@gmail.com', '2025-12-05 06:43:05', NULL),
(237, 'lmayyeiea kgnAM aRina', '9084792007965243', '337323110006', 'Jalan Mayangsari Gang I/5 RT 02 RW 02', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 895-6059-13534', 'raemiynk@gmail.com', '2025-12-05 06:43:05', NULL),
(238, ' iodPo mKaasosuu LmawtnioHr', '6533361807688557', '337322040039', 'Jalan Pantirejo 66', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 858-0067-4284', 'koonmmuslsuao@gmail.com', '2025-12-05 06:43:05', NULL),
(239, 'noaaHartythoci yiRa', '4289351607676989', '337322090131', 'Jl.Tempel Sari No.01 RT.003 RW.007 Nanggulan', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 838-2241-4157', 'kxdlo1lnaiox@gmail.com', '2025-12-05 06:43:05', NULL),
(240, 'jAiyait', '6827103003985903', '337322090141', 'Rejosari Rt 02 Rw 01', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 857-1329-0913', 'idaainaajhmryte@gmail.com', '2025-12-05 06:43:05', NULL),
(241, 'lymSIahaeA ynntael ', '6843760109221452', '337323080007', 'Jl. Arjuna no 86 Karangalit', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Crew EO & LO Freelance', '+62 811-2515-551', 'lnnaay9Stieh@gmial.com', '2025-12-05 06:43:05', NULL),
(242, 'lmro uLknhiii', '4238862006947464', '337323110011', 'Grogol rt4/7 Blotongan', 'Pr', 'Tamat D1/D2/D3', 'Lainnya', 'Jualan makanan kecil', '+62 856-4010-3101', 'll1iimkle2i@gmail.com', '2025-12-05 06:43:05', NULL),
(243, 'otasiherr PMiu', '5484783001602943', '337323110018', 'Jalan Dipomenggolo, RT.01/RW.04, Pulutan, Sidorejo', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 856-4017-7311', 'rpte57my02ui@gmail.com', '2025-12-05 06:43:05', NULL),
(244, 'haohranArinatF dhukm', '6366890906283364', '337324100017', 'Jalan Raden Rahmad', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 831-7452-3604', '40aafniadn@gmail.com', '2025-12-05 06:43:05', NULL),
(245, 'timir ntiD uaDaFarasti', '5983891611766856', '337324100003', 'jl argoyuwono no 15 RT 08 RW 01 kelurahan ledok kecamatan argomulyo kota Salatiga Jawa Tengah', 'Pr', 'Tamat D1/D2/D3', 'Wiraswasta', '', '+62 812-3666-6058', '48aiirfdt5@gmail.com', '2025-12-05 06:43:05', NULL),
(246, 'iuum rjopSasitn', '8461970408054648', '337324100009', 'Kalilondo rt 04 rw 04', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 812-1573-2669', 'nsrir6ooost@gmail.com', '2025-12-05 06:43:05', NULL),
(247, 'oauhtSr', '3120350708238269', '337324100023', 'Watubelang', 'Lk', 'Tamat D4/S1', 'Lainnya', 'OJol', '+62 812-2701-0706', 'ai.ae3thr8st@gmail.com', '2025-12-05 06:43:05', NULL),
(248, 'ERNAIAUITWLY FN', '3958921412654938', '330822101682', 'Pakel, RT 05/RW 01, Desa Ketawang, Kecamatan Grabag', 'Pr', 'Tamat SMS/Sederajat', 'Pegawai / Guru Honorer', '', '+62 812-2613-5043', 'ateiyirfnanulw@gmail.com', '2025-12-05 06:43:05', NULL),
(249, 's wiroksooE', '4367450805329891', '332422100039', 'Dusun saribaru Rt04/Rw15, Ds. Gempolsewu, Kec. Rowosari, Kab. Kendal', 'Lk', 'Tamat SMS/Sederajat', 'Lainnya', 'Driver', '+62 895-3972-67066', 'kooo9swrsiE@gmail.com', '2025-12-05 06:43:05', NULL),
(250, 'umaMimaZnaa  muldaMdhh', '5931400309769289', '332222090370', 'Lestri RT 32 RW 06', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Sedang mencari pekerjaan', '+62 857-2936-3433', '2udma5hmda3Mmuamh@gmail.com', '2025-12-05 06:43:05', NULL),
(251, 'usiDr Kharca Saaiin', '7048822301887222', '337322090071', 'Sidoharjo Gang II no 17 RT 01 RW 04', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 858-7517-5800', 'adknarsiar@gmail.com', '2025-12-05 06:43:05', NULL),
(252, 'it wdwaodinWii', '5016420612019477', '337322040004', 'Jl ngadisari', 'Lk', 'Tamat SMS/Sederajat', 'Lainnya', 'Ojek online', '+62 895-2447-1958', 'i8iwitaoidwWn9d@gmail.com', '2025-12-05 06:43:05', NULL),
(253, 'ihda naaaaParRaadtnm mrA', '6772821001601826', '337322090028', 'Jl. Simonegoro', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Freelance', '+62 857-2238-8267', 'aaariannamr.dd@gmail.com', '2025-12-05 06:43:05', NULL),
(254, 'oiaVnt', '5595680611663552', '337322090171', 'Jl. Argotunggal rt.7 rw.3', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 088-8252-5866', 'Pni72p71a@gmail.com', '2025-12-05 06:43:05', NULL),
(255, 'rigtani biiNanF', '6034402602769622', '337323030001', 'Jl. Argorumekso II gang 2 RT 09 RW 01 Ringinawe Ledok Argomulyo', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 895-3953-46322', 'nifni2rianbtaig7.3@gmail.com', '2025-12-05 06:43:05', NULL),
(256, 'ulnhkasiM', '3539123011726519', '337323030005', 'Perum Graha Asri No 14 Nobowetn RT 005 RW 005', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 857-9977-1733', 'Snal47aya@gmail.com', '2025-12-05 06:43:05', NULL),
(257, 'dAhn rirsKau ooiwiaaNgtnDi', '9633951602151491', '337323110013', 'Jl.Kartini2/488', 'Lk', 'Tamat D4/S1', 'Wiraswasta', NULL, '+62 856-4070-4434', '7k9iadann@gmail.com', '2025-12-05 06:43:05', '2025-12-13 22:28:44'),
(258, 'aaitwitRadk eane w', '2475171702495120', '337323110169', 'Jalan Sidomulyo RT 06 RW 15', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 851-7959-4877', 'aan7ar26nt@gmail.com', '2025-12-05 06:43:05', NULL),
(259, 'N Arytaunri', '9985260605713913', '337324100012', 'Perum Bumi Tingkir Hati Beriman No.27 RT05 RW07', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 853-2520-5100', 'annu4w2ar@gmail.com', '2025-12-05 06:43:05', NULL),
(260, 'ooiieStok Tny', '4649710907551893', '337323030004', 'Desa sraten rt 002 rw 003', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Serabutan/ freelance', '+62 821-5153-5454', 'tyyt2o5on@gmail.com', '2025-12-05 06:43:05', NULL),
(261, 'anVianiddaiiL ', '1878160102928217', '337322040009', 'Jl. Argotunggal 41 RT 02 RW XI', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 088-8065-03698', 'sflianea.mik@gmail.com', '2025-12-05 06:43:05', NULL),
(262, 'gnreuhaiNrEin ', '8529782404542217', '337322040026', 'Jl. Gunung Payung III no. 60 RT 04 RW 03 Tegalombo Blotongan', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 085-6432-83279', 'aheenrinrguni@gmail.com', '2025-12-05 06:43:05', NULL),
(263, 'oitsnii ukRiumkw e', '2173100704102936', '337322040013', 'Perum manunggal 2 Rt 03 Rw 07 no 14C kauman kidul', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 085-7406-60628', 'sounikir76@gmail.com', '2025-12-05 06:43:05', NULL),
(264, 'to dawtytSuiaii narBetH', '9299752108222973', '337322040021', 'Jalan Cemara I No.16 A Rt07/Rw09', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 857-2684-4386', 'hwituairtiatdbsoyntea@gmail.com', '2025-12-05 06:43:05', NULL),
(265, 'AahltS iifi', '2133411308297490', '337322040037', 'Sukoharjo rt 01 rw 05', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 831-6302-5695', 'unhgslufiatacii@gmail.com', '2025-12-05 06:43:05', NULL),
(266, 'anaiwtihaRacmSt ', '3280282806252544', '337322090093', 'JL.Domas No.18 RT 03/RW 08 Kelurahan Salatiga, Kecamatan Sidorejo Kota Salatiga', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 858-0228-3675', '6air9cm.naawithast@gmail.com', '2025-12-05 06:43:05', NULL),
(267, 'aswmDieatiR u uK', '9681732303395088', '337322090080', 'Jl Veteran No 24 RT 2 RW 1', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 813-2560-2714', 'aumiuwtsarekd@gmail.com', '2025-12-05 06:43:05', NULL),
(268, 'dacetiPoyra Whsi', '3270431201016509', '337322090058', 'Pulutan Lor', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 813-2912-3099', 'w1sai.dthapyr9o@gmail.com', '2025-12-05 06:43:05', NULL),
(269, 'il aE kwainEdaaDtr', '9880390410464599', '337322090125', 'Jl Imam Bonjol no 868b RT 09 RW 08', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 856-4281-0885', 'aliwieatndekdr..aa@gmail.com', '2025-12-05 06:43:05', NULL),
(270, 'iumPWJrioonuan s t', '2999151205226077', '337322090174', 'Jl. Kalisombo No. 22 RT. 004 RW. 005', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Freelance', '+62 857-2707-1512', 'noiwsupajrmutino@gmail.com', '2025-12-05 06:43:05', NULL),
(271, 'iuankt uhomlhsK', '5177132504212228', '337324100014', 'RT 001 RW 007 Dusun Mlandong', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 821-6750-4538', '31huslknku2@gmail.com', '2025-12-05 06:43:05', NULL),
(272, 'iuklYuAi nmf RsAi ', '6742311010742834', '337324100005', 'Krajan, Rt. 001 Rw. 005, kel. Tingkir lor, kec. Tingkir, kota salatiga', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 896-2776-2427', 'lrziloiiaga5kh@gmail.com', '2025-12-05 06:43:05', NULL),
(273, 'NaW fhusatylailiu Nhaaz', '2951810107415321', '332222091195', 'Dsn. Padaan, DS. Gedangan RT:1/RW:7', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 628-3103-73734', 'nlaiilhfaaunszta@gmail.com', '2025-12-05 06:43:05', NULL),
(274, 'ySnuYyAar  lty', '8116132804052135', '337322040019', 'Jl. Margosari I/51 Salatiga', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 889-8087-6293', 'yuaya6stn2lyyr@gmail.com', '2025-12-05 06:43:05', NULL),
(275, 'IRS SWTITAI', '8447920610416900', '337322010012', 'JL. SINOMAN TEMPEL 270', 'Pr', 'Tamat D1/D2/D3', 'Lainnya', 'PENGEMUDI OJOL', '+62 858-6808-0979', 'elavtadriiao@gmail.com', '2025-12-05 06:43:05', NULL),
(276, 'ithnati sRiu', '3245520410475243', '337322030008', 'Isep- isep rt 02 rw 03 cebongan argomulyo salatiga', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 896-6905-0265', 'usr212ai3htntii@gmail.com', '2025-12-05 06:43:05', NULL),
(277, 'ldyfurBiyaaaotiS h', '3055412512077824', '337322090012', 'Ngawen Tegalsari RT 002 RW 008', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 857-4068-9965', 'iaadyffsior@gmail.com', '2025-12-05 06:43:05', NULL),
(278, 'RIYUTTDAASTIYIW NI', '2998890212245503', '337322040036', 'CEBONGAN, RT05 RW 01', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 852-2888-4059', 'diwriaittuaiyystn@gmail.com', '2025-12-05 06:43:05', NULL),
(279, 'tausaudadog ulwa\'uiiycannhh hWlla', '1776212303847461', '337322090091', 'Jl ja\'far shodiq tegalsari 3/2', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 822-2220-9563', 'Wlwlanuuaada@yahoo.com', '2025-12-05 06:43:05', NULL),
(280, 'NYIRAONAYGOSNINAT NNKAT O', '5338142403907359', '337322090115', 'JL. Siranda 2 No. 2 A RT 004/RW 012', 'Lk', 'Tamat D1/D2/D3', 'Wiraswasta', '', '+62 856-4161-0901', 'nYoantnnaaa.@gmail.com', '2025-12-05 06:43:05', NULL),
(281, 'mmRH iu daiFzanhaaa', '9191570910675122', '337323060001', 'Ngawen RT 5 RW 15, Kelurahan Mangunsari, Kecamatan Sidomukti, Kota Salatiga', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Konsultan', '+62 882-2182-1963', 'mm5hah2dani2ar@gmail.com', '2025-12-05 06:43:05', NULL),
(282, 'MiniiidW ayAta asnanrten', '2590141402648630', '337323080025', 'Jalan Hasanudin No. 58 Rt. 06 Rw. 07 Salatiga', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Belum bekerja', '+62 857-4295-8999', 'eaaim3tnr5n@gmail.com', '2025-12-05 06:43:05', NULL),
(283, 'IEHA OUDMFATBMYNAMR', '1870143002346389', '337323110048', 'REKESAN RT. 07 / RW. 03', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 823-2382-4428', '8unraFesbi0@gmail.com', '2025-12-05 06:43:05', NULL),
(284, 'IHAARDIYN', '6649181211435593', '332223030104', 'Jl.Karimunjawa Kalisari II Jombor Tuntang', 'Pr', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 821-3331-1963', 'lhysnaka6aa8k@gmail.com', '2025-12-05 06:43:05', NULL),
(285, 'AimniSthai ', '5469702303086899', '337322040040', 'Grogol Rt.002/007 no.49', 'Pr', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 857-2734-8324', 'a47tsaihm36iin@gmail.com', '2025-12-05 06:43:05', NULL),
(286, 'IaKn ntm nIhdusuaa', '2653421402041763', '337322040015', 'Jl.Prahu no.5 RT 02 RW 05', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 857-2786-9636', 'k7msuniua9ant@gmail.com', '2025-12-05 06:43:05', NULL),
(287, 'iCr saiVnirohttKycnuro at', '6173683002292107', '337322090006', 'Jalan KH Ahmad Dahlan RT 04 RW 15', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 851-5800-5744', 'irinkuoc7atv0r5@gmail.com', '2025-12-05 06:43:05', NULL),
(288, 'rtay inW aFidiahyAtrui', '1490850607848589', '337322090024', 'Jl. Argoyuwono No.34', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 822-3300-4843', 'tdaeiw.prpa@gmail.com', '2025-12-05 06:43:05', NULL),
(289, 'hSaatkaahiksheMuynl no hSi', '6934810704337344', '337322090068', 'Canden RT 002 RW 003', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 898-6458-339', 'san8mkn1utahsiiay@gmail.com', '2025-12-05 06:43:05', NULL),
(290, 'se iLalariaiM', '1724993011742073', '337322090119', 'Jalan Mawar Gang Pace, Tegalsari RT 03 RW 02', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 877-0035-2315', 'lsailirema35ai6@gmail.com', '2025-12-05 06:43:05', NULL),
(291, 'tPaeangatrw isnae SyuAg', '2790550808926638', '337322090133', 'Jl.Kridanggo no.473, RT/02, RW/01', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 823-1452-9300', 'aayatenpgn9aetrigw9sa9us@gmail.com', '2025-12-05 06:43:05', NULL),
(292, 'ajcaaFu ahmYtdarona Rr', '3240680705516872', '337322090173', 'Salatiga Permai III/31 RT02 RW12', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 881-2802-375', 'aryarujarafnto@gmail.com', '2025-12-05 06:43:05', NULL),
(293, 'mleKmiMmuafAa Edn r iahd', '7422552703246876', '337323080019', 'Bulu RT 002 RW 004 Kel. Dadapayam Kec. Suruh Kab. Semarang', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 085-7428-84397', 'i49m15r02@gmail.com', '2025-12-05 06:43:05', NULL),
(294, 'nrtyuai pidnmgrSaeaeteHalinS n', '7760351002562596', '337322030001', 'CANDEN RT 009 RW 003', 'Pr', 'Tamat D1/D2/D3', 'Wiraswasta', '', '+62 857-1364-4353', 'assdleseregan@gmail.com', '2025-12-05 06:43:05', NULL),
(295, 'radn hCicaraaaKnaatwmi R', '3492642608452295', '337322090030', 'Pungkursari no. 1221 RT. 5 RW. 3', 'Pr', 'Tamat D1/D2/D3', 'Lainnya', 'Ibu rumah tangga', '+62 857-2714-7575', 'aachrdntnarimawaairack@gmail.com', '2025-12-05 06:43:05', NULL),
(296, 'tBiK iarinuakewyt', '3940600108892528', '337322090176', 'Surowangsan RT 01 RW 05 Kauman kidul', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 815-7549-1985', 'ni0ari28dhazana0@gmail.com', '2025-12-05 06:43:05', NULL),
(297, 'tiK aniaAauwsn', '4522311704141944', '337322090065', 'Pandansari', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 856-4076-3897', 'ky78aiku@gmail.com', '2025-12-05 06:43:05', NULL),
(298, 'riJoknataoHoy ', '4560272003157056', '337322100005', 'Jl. Tritis Asri No. 2. Dk. Klumpit, RT:02/RW:01', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 896-8415-4567', 'h2kojoy3@gmail.com', '2025-12-05 06:43:05', NULL),
(299, 'iaaP mtarTiwt', '7924482205687473', '337322090208', 'Perum Taman Mutiara, Jl. Intan 2 Blok E2 No.35 RT.01 RW.10', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 895-3594-49397', '0aifr9mtta2.8@gmail.com', '2025-12-05 06:43:05', NULL),
(300, 'aitzAa imnhTm', '5034292505694549', '337324100016', 'JL.SIRANDA III NO.1 RT 004 RW 012', 'Lk', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 895-6123-66766', 'ahizmitamadn@gmail.com', '2025-12-05 06:43:05', NULL),
(301, 'aaetNketrnsLaiai S y', '2642470804072410', '337323110170', 'Jagalan RT 001/ RW 005', 'Pr', 'Tamat D4/S1', 'Lainnya', '-', '+62 878-2612-1352', 'l2rtaknniaase015i@gmail.com', '2025-12-05 06:43:05', NULL),
(302, 'etiarows DaIlRmai ', '7787801102789126', '337324100018', 'Jalan Karang Taruna no 63 rt 8 rw 5', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 081-7244-540', 'aeiaritiwlradosm@gmail.com', '2025-12-05 06:43:05', NULL),
(303, 'yotnaaS rAn anipaEtuk', '4099921302852658', '337324100007', 'Gg.Merpati 3, Banjaran RT 02/RW07. Kel.Mangunsari, Kec.Sidomukti , Kota Salatiga', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Karyawan Swasta', '+62 859-1601-30385', '1aeaaktann3@gmail.com', '2025-12-05 06:43:05', NULL),
(304, 'd SnaaAmal uasYkidm', '8960441307198241', '337324100013', 'Jl. Ki Suropati, Tingkir Lor, Kec. Tingkir, Kota Salatiga, Jawa Tengah 50746', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 877-3356-4825', 'amak07dmaa6ls@gmail.com', '2025-12-05 06:43:05', NULL),
(305, 'IOK LMLBAOHOASA OHRDORHKRABNLAA ', '8358611112868378', '337324100011', 'JL. KAUMAN GG VII/NO.67 RT001/RW002', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 856-4085-8020', 'rlkanoorhaaokm@gmail.com', '2025-12-05 06:43:05', NULL),
(306, 'u uDn SwimtsKDaohuamaeri ', '6414232004141104', '337325050001', 'Mlandong RT 01 RW 07', 'Pr', 'Tamat D4/S1', 'Pegawai / Guru Honorer', '', '+62 822-4347-9691', '0ew19ih5tdd8i5a@gmail.com', '2025-12-05 06:43:05', NULL),
(307, 'Asainiiqn rs', '7625762602358323', '332222090052', 'RT 2 RW 4 DESA NGRONGGO', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-1343-4011', 'Aisirq33@gmail.com', '2025-12-05 06:43:05', NULL),
(308, 'PMSERKI GAAILNAHDRAN ISUN', '7136462812345777', '337322010030', 'Jl. Kartini no. 26 Salatiga', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 895-0881-9966', '4isnEkdam7luar@gmail.com', '2025-12-05 06:43:05', NULL),
(309, 'ihMgsu', '7573130810527068', '337322090148', 'Jl Argotunggal RT 03 RW 07 ledok', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 085-7406-42495', 'sdokhuelgmi.@gmail.com', '2025-12-05 06:43:05', NULL),
(310, 'anaiRy', '4288860711743714', '337322090089', 'Robyong rt 001/006', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 895-4154-92755', 'dnraaayia@gmail.com', '2025-12-05 06:43:05', NULL),
(311, 'AiadalunLi ', '2644511011568257', '337322090015', 'BONOREJO RT 01 RW 05', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 815-4216-5169', '8aai1uilld4an0@gmail.com', '2025-12-05 06:43:05', NULL),
(312, 'ty AumphmaMaar daulIsaS thdmaiir', '9419152604792077', '337322090035', 'Perum permata gunungsari 04/06', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Freelance', '+62 857-1303-7383', 'teaprhasyeadrAut@gmail.com', '2025-12-05 06:43:05', NULL),
(313, 'aat iiaRYstntlnua', '5487532007503676', '337322090076', 'Jalan Sidomulyo Ngawen RT05 RW15', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 858-6504-1118', 'tiatstnlnyriaaua@gmail.com', '2025-12-05 06:43:05', NULL),
(314, 'ia IoiabMmnnhrs', '8197870207667137', '337322100002', 'Jl. Tritis Asri No. 2. Dk. Klumpit, RT:02/RW:01', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 882-0066-73459', 'inh8iamib7asonrm@gmail.com', '2025-12-05 06:43:05', NULL),
(315, 'aDrrTa ianhiaa T', '7342432011978079', '337422100310', 'Kridanggo RT 002 RW 001, Kel. Kalicacing, Kec. Sidomukti, Kota Salatiga', 'Pr', 'Tamat D1/D2/D3', 'Lainnya', 'Kader TPK', '+62 857-2650-4690', 'niatarhdat@gmail.com', '2025-12-05 06:43:05', NULL),
(316, 'I RWAATDAHA MRSPII', '6588842701729282', '337323030002', 'JL. KH. ISHOM RT 05 RW 05 BANCAAN TENGAH SALATIGA', 'Pr', 'Tamat D4/S1', 'Lainnya', 'IBU RUMAH TANGGA', '+62 815-6639-920', 'arpmtais@gmail.com', '2025-12-05 06:43:05', NULL),
(317, 'hi eaahAtserinTLusg ', '5225471303552392', '337323110156', 'Panti Asuhan Aisyiyah Jalan Imam Bonjol no 45 A Sinoman', 'Pr', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 082-2886-49100', 'saiturnlsiehtgeaa@gmail.com', '2025-12-05 06:43:05', NULL),
(319, 'TNPRIASEAWAT AEII', '3421962706853619', '337322030013', 'Jalan Kartini No 26 RT 04 RW 01 Salatiga', 'Pr', 'Tamat D4/S1', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 857-2758-9723', 'eiaraittaiwapnes@gmail.com', '2025-12-05 06:43:05', NULL),
(320, ' dcamoeriAhch', '7197120207466174', '337322010013', 'Bejiwetan RT 03/RW 04.Kalibeji', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 896-6578-3296', 'Ceh85ior@gmail.com', '2025-12-05 06:43:05', NULL),
(321, 'riaata oeeRssd p', '8089660310618357', '337322090007', 'Tetep rt 5 rw 8', 'Lk', 'Tamat D1/D2/D3', 'Wiraswasta', '', '+62 821-3476-8415', 'prsieota.ar@gmail.com', '2025-12-05 06:43:05', NULL),
(322, 'DIEYDATWYANTE  NOIS', '5378640909478254', '337322010038', 'Jalan Karimunjawa Kalisari II Jombor Tuntang', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 823-2485-1922', 'daid01ywen8@gmail.com', '2025-12-05 06:43:05', NULL),
(323, 'lN uMSiinauaitdtir y', '4247330408066771', '337322040005', 'Jalan argopratiwi Tegalsari RT 2 RW 2 Kalibening, Tingkir, Salatiga', 'Pr', 'Tamat D4/S1', 'Wiraswasta', '', '+62 856-4155-2949', 'iNuluiartmnyda@gmail.com', '2025-12-05 06:43:05', NULL),
(324, 'sorEuhiNKndgo aAot r otkii', '9238791511498542', '337322090070', 'Tegalombo RT03 RW03 Blotongan - Salatiga', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '', '+62 085-7270-4985', '_tosi2e1tikkonr00a@yahoo.com', '2025-12-05 06:43:05', NULL),
(325, 'invnoadteayD aW', '1938381805635098', '337322090151', 'Perum Argomulyo C 39 RT 003 RW 010', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Freelance Pengajar Bimbel', '+62 856-2684-694', 'adppelodo_h@yahoo.com', '2025-12-05 06:43:05', NULL),
(326, 'aKhsanh ayayabDzhi', '7068151112199870', '337322090049', 'Jalan NanggulanNo. 40/58', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-2640-7760', 'oydiazbhahsnaykha@gmail.com', '2025-12-05 06:43:05', NULL),
(327, ' uyudtPiiarw WaiStahit', '3510200911381424', '337322090105', 'Perum Prajamukti Blok B152 Rt.003 Rw.007', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 856-4705-1639', 'ypa8raowithe3iw@gmail.com', '2025-12-05 06:43:05', NULL),
(328, 'nyruatnadI', '7060601307952210', '337322090139', 'Jl. Tanggul Rejo RT 04 RW 02 No 49', 'Pr', 'Tamat D1/D2/D3', 'Mengurus Rumah Tangga', '', '+62 857-3615-8143', 'antu5rIday.n197@gmail.com', '2025-12-05 06:43:05', NULL),
(329, 'rtNt  aMeDwuiriuisk', '7262991303319587', '337322090153', 'Macanan RT.01 RW.02', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 838-6203-7165', 'Ntrmu6wtduirisk6aie@gmail.com', '2025-12-05 06:43:05', NULL),
(330, 'WsiAunaumaad lrad Kal', '5716643002962718', '337322110019', 'Jalan Melatisari No. 29 RT004/001 Butuh Kutowinangun Lor Kota Salatiga', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Freelance', '+62 896-7322-3782', 'aauasdilkmlu@gmail.com', '2025-12-05 06:43:05', NULL),
(331, 'uaytD aiahsyuagn ', '3918650703632956', '337323110021', 'Jalan simonegoro rt 4 rw 5 duren kecandran sidomukti salatiga jateng', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 857-2774-2071', 'd9nt8yhuaysaga@gmail.com', '2025-12-05 06:43:05', NULL),
(332, ' pirtusaanwNi', '8927350709128849', '337324100006', 'Tetep Wates Rt2 Rw6 Kumpulrejo', 'Pr', 'Tamat D1/D2/D3', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 856-4016-5016', 'nsnaliai85ab@gmail.com', '2025-12-05 06:43:05', NULL),
(333, 'IORFI', '8540972310481171', '337322040020', 'RT 3 RW 2 PULUTAN LOR', 'Lk', 'Tamat SMS/Sederajat', 'Lainnya', 'BURUH HARIAN LEPAS', '+62 813-9059-9451', '7i6iof7froiri@gmail.com', '2025-12-05 06:43:05', NULL),
(334, 'Fkisiantrrat iiw', '3322452704169143', '337322040012', 'Jl Cemara IV no 31 Salatiga', 'Pr', 'Tamat D4/S1', 'Mengurus Rumah Tangga', '', '+62 819-1008-2488', 'rikfnia6rtis@gmail.com', '2025-12-05 06:43:05', NULL),
(335, 'saKaoiAf niNunhs ri', '1326830701571898', '337322090040', 'Pondok Pesantren Mahasiswi Baitusy Syukur Kota Salatiga', 'Pr', 'Tamat S2', 'Lainnya', 'Belum Bekerja', '+62 823-2585-1767', 'ais2ifnsnaa@gmail.com', '2025-12-05 06:43:05', NULL),
(336, 'Mimq ahdzaumiR', '4618780410122525', '337322090025', 'JL. Argosari No. 12 Rt. 02 Rw. 04 Kel. Ledok Kec. Argomulyo Salatiga', 'Lk', 'Tamat D4/S1', 'Lainnya', 'Belum Bekerja', '+62 896-7466-5655', 'razyau9igiqmriin4nr@gmail.com', '2025-12-05 06:43:05', NULL),
(337, 'oooSurtpJk koaE ', '5592750105564446', '337322090109', 'Jl ngadisari 2 Tegalrejo', 'Lk', 'Tamat SMS/Sederajat', 'Wiraswasta', '', '+62 085-6411-50474', '2kDo9g1o@gmail.com', '2025-12-05 06:43:05', NULL),
(338, 'rtln hadbSaurueiiwsi  iy', '6256900506128970', '337322090053', 'Jl.osamaliki rt 004 rw 010', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 877-6550-5693', 'n0Suyi1whra002i0@gmail.com', '2025-12-05 06:43:05', NULL),
(339, 'INtoviiyiart na', '7137591909381084', '337322090111', 'Rumah dinas PDAM Ngaglik jln argotirto no 4 RT 04 RW 12 Ledok argomulyo', 'Pr', 'Tamat SMS/Sederajat', 'Kader PKK / Karang Taruna / Kader Lainnya', '', '+62 895-3673-55730', 'am7m1rpubaa@gmail.com', '2025-12-05 06:43:05', NULL),
(340, 'rilnvd oiatNnAa napA', '7422390606185476', '337323080018', 'Jl padas sari no 18', 'Lk', 'Tamat D4/S1', 'Pelajar / Mahasiswa', '', '+62 812-2705-8012', '1liannv7d7ana1@gmail.com', '2025-12-05 06:43:05', NULL),
(341, 'tammaAhw ainiRa Uii', '5378180503581074', '337323110168', 'Perum Lembah Asri Blok C No 1 A', 'Pr', 'Tamat D4/S1', 'Wiraswasta', '', '+62 895-4010-04793', 'iaiinamu@gmail.com', '2025-12-05 06:43:05', NULL),
(342, 'itoaaheygaa amdsaMh ', '5720350612148215', '337324100020', 'Gg,Merdeka, Ringinawe, RT02, RW01, Ledok, Argomulyo, Kota Salatiga', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 889-8344-1754', 'agoymhsahea@gmail.com', '2025-12-05 06:43:05', NULL),
(343, 'Ri uPatnoewtij', '5255910302913801', '337322040017', 'Jalan Sidomulyo GG II', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 858-7628-1850', 'ujrtormainewait@gmail.com', '2025-12-05 06:43:05', NULL),
(344, 'Yisiien antnTatHa iirur', '7635582906135660', '337322040034', 'Jl. Mayangsari Rt 2 Rw 2', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 878-2019-8500', 'iisthtre.aaiinnr@gmail.com', '2025-12-05 06:43:05', NULL),
(345, 'OSAKUC BIAWSGNWAO', '5552742409233641', '337323080024', 'Jalan turen gang 1 no 47a', 'Lk', 'Tamat SMS/Sederajat', 'Pelajar / Mahasiswa', '', '+62 898-4281-676', 'rAllmwk@gmail.com', '2025-12-05 06:43:05', NULL),
(346, 'ASNTI', '6792790512713837', '337324100002', 'Jl. Muria No 148 RT 4/RW 5', 'Pr', 'Tamat D4/S1', 'Lainnya', 'Les Privat dari rumah ke rumah', '+62 895-3282-75242', 'an2tstaisi2n@gmail.com', '2025-12-05 06:43:05', NULL),
(347, 'aRkaYi iitFt nra', '3822912805099971', '337323110167', 'Nogosari, rt.01/rw.05', 'Pr', 'Tamat SMS/Sederajat', 'Mengurus Rumah Tangga', '', '+62 856-0034-8281', 'Fiahtkhtraa@gmail.com', '2025-12-05 06:43:05', NULL),
(348, 'HUmmailytula ', '4293450110927736', '337324100019', 'Promasan, RT 03 RW 02', 'Pr', 'Tamat D4/S1', 'Lainnya', 'fresh graduated', '+62 882-2879-0914_', 'mlu3mt5aulhay5i@gmail.com', '2025-12-05 06:43:05', NULL),
(349, 'Rizky Pambudi', '3322122703210091', '323298', 'Langensari Barat RT.09 / Rw.05', 'Lk', 'Tamat D4/S1', 'Mahasiswa', '', '089993533333', 'coba@gmail.com', '2025-12-05 22:38:39', '2026-01-07 08:19:49'),
(350, 'budi', '12133', '2233', 'Langensari Barat, Rt.09 Rw.05 Kec.Ungaran Barat Kab. Semarang', 'Lk', 'Tamat D4/S1', 'Mahasiswa', 'sdjsdjs', '08979257718', 'ammarsirajnomor1pl@gmail.com', '2025-12-11 20:02:10', NULL),
(352, 'ali', '76487367247624762746276487', NULL, 'Langensari Barat, Rt.09 Rw.05 Kec.Ungaran Barat Kab. Semarang', 'Lk', 'Tamat D4/S1', 'Wiraswasta', NULL, '08223451729', 'Petani@gmail.com', '2025-12-13 22:42:24', '2025-12-13 22:42:24'),
(355, 'Contoh Nama Mitra', '1234567890123490', '12345', 'Jl. Merdeka No. 45', 'Lk', 'Tamat D4/S1', 'Wiraswasta', '-', '081234567890', 'contoh@email.com', '2025-12-13 23:37:01', '2025-12-13 23:37:01'),
(356, 'Ammaar', '736726372', '228882828', 'langensari barat', 'Lk', 'Tamat D4/S1', 'wewe', 'wewe', '228288282', 'ditemaditema@gmail.com', '2026-01-04 07:49:12', '2026-01-04 07:49:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penugasan`
--

CREATE TABLE `penugasan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_subkegiatan` varchar(50) NOT NULL,
  `id_pengawas` bigint(20) UNSIGNED NOT NULL,
  `status_penugasan` varchar(255) NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penugasan`
--

INSERT INTO `penugasan` (`id`, `id_subkegiatan`, `id_pengawas`, `status_penugasan`, `created_at`, `updated_at`) VALUES
(24, 'sub2', 1, 'disetujui', '2025-12-04 06:56:43', '2025-12-18 20:47:04'),
(26, 'sub3', 1, 'disetujui', '2025-12-04 21:02:33', '2025-12-18 20:47:04'),
(27, 'sub4', 1, 'disetujui', '2025-12-11 20:00:02', '2026-01-05 20:20:10'),
(28, 'sub5', 1, 'disetujui', '2025-12-11 20:36:55', '2026-01-05 20:20:10'),
(29, 'sub9', 1, 'disetujui', '2025-12-11 20:41:45', '2026-01-08 03:32:19'),
(30, 'sub7', 1, 'disetujui', '2025-12-14 21:40:24', '2026-01-08 03:32:19'),
(33, 'sub15', 12, 'disetujui', '2025-12-16 19:57:45', '2026-01-08 03:32:18'),
(34, 'sub14', 1, 'disetujui', '2025-12-17 08:21:49', '2026-01-08 03:32:17'),
(35, 'sub17', 1, 'disetujui', '2026-01-07 08:22:24', '2026-01-07 08:22:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perencanaan`
--

CREATE TABLE `perencanaan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_subkegiatan` varchar(50) NOT NULL,
  `id_pengawas` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `perencanaan`
--

INSERT INTO `perencanaan` (`id`, `id_subkegiatan`, `id_pengawas`, `created_at`, `updated_at`) VALUES
(24, 'sub2', 1, '2025-12-04 06:56:43', '2025-12-04 06:56:43'),
(28, 'sub8', 1, '2025-12-11 20:36:55', '2025-12-11 20:36:55'),
(30, 'sub13', 1, '2025-12-16 21:40:49', '2025-12-16 21:40:49'),
(31, 'sub4', 1, '2025-12-17 05:31:09', '2025-12-17 05:31:09'),
(32, 'sub15', 1, '2025-12-17 05:32:01', '2025-12-17 05:32:01'),
(33, 'sub14', 1, '2025-12-17 05:59:39', '2025-12-17 05:59:39'),
(36, 'sub18', 12, '2025-12-30 18:28:13', '2025-12-30 18:28:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '61fe225735b1540a32037a202ed6f3c052586a9138d17cc1568b32aab02b3901', '[\"*\"]', '2025-12-13 06:17:33', NULL, '2025-12-13 01:39:32', '2025-12-13 06:17:33'),
(2, 'App\\Models\\User', 1, 'auth_token', 'ad7b658b21f084d32db5af0ec06252d9e838b24d39940d82650f9a34b319ec20', '[\"*\"]', NULL, NULL, '2025-12-13 06:57:54', '2025-12-13 06:57:54'),
(3, 'App\\Models\\User', 1, 'auth_token', '9940f2eb53fb3e56404d88ef40d6808af439687933a131b3ff2a4fdc34bbe610', '[\"*\"]', NULL, NULL, '2025-12-13 07:00:12', '2025-12-13 07:00:12'),
(4, 'App\\Models\\User', 1, 'auth_token', '6409851f88ce558259284c92540192757d491f2d563a5671c9fcc1afe84fc263', '[\"*\"]', NULL, NULL, '2025-12-13 07:10:03', '2025-12-13 07:10:03'),
(5, 'App\\Models\\User', 1, 'auth_token', 'aa68c411801f6708036dc0830a73fe4160dfa36854c2dd462f9fb45582ad7e79', '[\"*\"]', NULL, NULL, '2025-12-13 07:16:36', '2025-12-13 07:16:36'),
(6, 'App\\Models\\User', 1, 'auth_token', '49216483b0a1498982ddbc46f99fddac25a36f0b4f1655f3f11840c6ed903814', '[\"*\"]', NULL, NULL, '2025-12-13 07:26:24', '2025-12-13 07:26:24'),
(7, 'App\\Models\\User', 1, 'auth_token', '6389131a5d72eb187d08445ff9ecef232c92913fac15c66bfbe0ab5bf7334be3', '[\"*\"]', NULL, NULL, '2025-12-13 07:26:27', '2025-12-13 07:26:27'),
(8, 'App\\Models\\User', 1, 'auth_token', '16e73406ee603c65be835280fc6a67231cb8969e41cd15f25bbef2b34460c57e', '[\"*\"]', NULL, NULL, '2025-12-13 07:26:43', '2025-12-13 07:26:43'),
(9, 'App\\Models\\User', 1, 'auth_token', '6e04670d41c93baccdda69d6ee0a521e8f3add27477c720e3d09868306a6adb8', '[\"*\"]', NULL, NULL, '2025-12-13 07:32:27', '2025-12-13 07:32:27'),
(10, 'App\\Models\\User', 1, 'auth_token', 'a3877cc14ae7aae8b687d84357c1f4e8ea153fe96e7c6fa0af952742e174223b', '[\"*\"]', NULL, NULL, '2025-12-13 07:35:01', '2025-12-13 07:35:01'),
(11, 'App\\Models\\User', 1, 'auth_token', 'd842818a88b5a5414a9d8183ebc5e11ad309b7ea075deb4d5f13305ffc37ba2c', '[\"*\"]', '2025-12-13 07:41:12', NULL, '2025-12-13 07:41:06', '2025-12-13 07:41:12'),
(12, 'App\\Models\\User', 1, 'auth_token', '6bab1fba88f07443a8d1717bbe0b2eb9981c0eb30285adc00a87944c548eda97', '[\"*\"]', '2025-12-13 22:28:44', NULL, '2025-12-13 16:29:10', '2025-12-13 22:28:44'),
(13, 'App\\Models\\User', 1, 'auth_token', '0ed70d80e2c3e67d50a8ed4bf637433aec9a56759a4ed453aac51d0a5b620ffb', '[\"*\"]', '2025-12-16 20:46:59', NULL, '2025-12-13 18:36:30', '2025-12-16 20:46:59'),
(14, 'App\\Models\\User', 1, 'auth_token', '28ff68fafe9f46669320f678824f302604722d77e0666f6cb81b6a2721a4a18e', '[\"*\"]', '2025-12-13 22:33:03', NULL, '2025-12-13 22:28:48', '2025-12-13 22:33:03'),
(15, 'App\\Models\\User', 1, 'auth_token', '01bf530e78c4f7c36c71b0166a6e5d8621976cac1b59cb27b08ca24a9574b0f3', '[\"*\"]', '2025-12-13 22:33:30', NULL, '2025-12-13 22:33:14', '2025-12-13 22:33:30'),
(16, 'App\\Models\\User', 1, 'auth_token', 'a93a677658dc138c095398e0bb7b3645c90151c58cc5d35798fbed48909dceac', '[\"*\"]', '2025-12-13 22:34:18', NULL, '2025-12-13 22:33:34', '2025-12-13 22:34:18'),
(17, 'App\\Models\\User', 1, 'auth_token', '0f9104b94c9e63706834c160e2f4f3b359219a0b0533cf0145fb6b78f1fe9a7b', '[\"*\"]', '2025-12-14 08:17:04', NULL, '2025-12-13 22:34:23', '2025-12-14 08:17:04'),
(18, 'App\\Models\\User', 1, 'auth_token', '706f8ca829cecd4e8adfd19aa734d5bf5c7026159945faa9ece63a6a6c211a98', '[\"*\"]', '2025-12-14 21:32:25', NULL, '2025-12-14 17:29:43', '2025-12-14 21:32:25'),
(19, 'App\\Models\\User', 1, 'auth_token', 'dee724b1bb1a12c4718588d7e5f0db3aca192c207db6632db7430a9ef86bf8df', '[\"*\"]', '2025-12-14 20:25:02', NULL, '2025-12-14 19:04:48', '2025-12-14 20:25:02'),
(20, 'App\\Models\\User', 1, 'auth_token', '6be1d0086f471fafbe24f266d88f29ae99b491f8a37c9d891256cd95e15d164a', '[\"*\"]', '2025-12-15 07:29:34', NULL, '2025-12-14 21:32:40', '2025-12-15 07:29:34'),
(21, 'App\\Models\\User', 1, 'auth_token', 'c240398dca1dad13986f23d1c8b92c90b2595c0070fc9fd8a133993491b2bc7d', '[\"*\"]', '2025-12-15 20:16:11', NULL, '2025-12-15 18:46:25', '2025-12-15 20:16:11'),
(22, 'App\\Models\\User', 1, 'auth_token', '0b18a86239154008dd7d4fad707985c8f2762f15eaca2fb24d2f28463ca2ae66', '[\"*\"]', '2025-12-15 22:05:38', NULL, '2025-12-15 20:16:12', '2025-12-15 22:05:38'),
(23, 'App\\Models\\User', 1, 'auth_token', 'ba09ef5b5208a1cbbb63e069977fc49b02b8e91732271737db9ef429332707c6', '[\"*\"]', '2025-12-16 00:25:47', NULL, '2025-12-16 00:13:13', '2025-12-16 00:25:47'),
(24, 'App\\Models\\User', 1, 'auth_token', '2ee31386b79b7c8fe7021797c8e6fec0dbab447a2f8bd4dc2c5a483d9b68b0d0', '[\"*\"]', '2025-12-16 00:32:16', NULL, '2025-12-16 00:26:19', '2025-12-16 00:32:16'),
(25, 'App\\Models\\User', 1, 'auth_token', '5514c6cee0d0ffde092db0bd44c6f3cfeaa366a7a791028fccf62550521a370a', '[\"*\"]', '2025-12-16 00:32:48', NULL, '2025-12-16 00:32:31', '2025-12-16 00:32:48'),
(26, 'App\\Models\\User', 1, 'auth_token', '79cea07dca2acd8d86e1384d12fa2e15611325633cbc2dab69710987a150fdbd', '[\"*\"]', '2025-12-16 00:33:49', NULL, '2025-12-16 00:33:02', '2025-12-16 00:33:49'),
(27, 'App\\Models\\User', 1, 'auth_token', '5b13daad178746d4f247bce8e261c33fab51131402d4743821b43c126d78684a', '[\"*\"]', '2025-12-16 07:53:13', NULL, '2025-12-16 00:34:07', '2025-12-16 07:53:13'),
(28, 'App\\Models\\User', 1, 'auth_token', '958892bbb054da031e8838cb18a075c296e18839c580afcc25b0dafa142233e0', '[\"*\"]', '2025-12-16 08:25:00', NULL, '2025-12-16 08:09:15', '2025-12-16 08:25:00'),
(29, 'App\\Models\\User', 12, 'auth_token', '503adf4f310db661044d6a89724307c033439c849c59549123b47493bd4311bb', '[\"*\"]', '2025-12-16 09:47:13', NULL, '2025-12-16 08:25:32', '2025-12-16 09:47:13'),
(30, 'App\\Models\\User', 12, 'auth_token', '73f75da1244ae48daa924877ce0029f5e2b0e236464d78eecfd8a5a68edfe0be', '[\"*\"]', '2025-12-16 18:45:55', NULL, '2025-12-16 18:41:55', '2025-12-16 18:45:55'),
(31, 'App\\Models\\User', 12, 'auth_token', '538e218aa80392b00881d044a5a824fb095690de87e31df034ca094a82c8728d', '[\"*\"]', '2025-12-16 19:31:25', NULL, '2025-12-16 18:46:49', '2025-12-16 19:31:25'),
(32, 'App\\Models\\User', 12, 'auth_token', 'f5f9e3498fa8e20bccef3660940b88ea8e9ea217ab9b9f2a51f55e9a9ded4e4f', '[\"*\"]', '2025-12-16 19:33:20', NULL, '2025-12-16 19:32:33', '2025-12-16 19:33:20'),
(33, 'App\\Models\\User', 12, 'auth_token', 'e43b4132d1cd8d7807dcd37d5bc473db34a911b32ccb8b29931595318e420a9c', '[\"*\"]', '2025-12-16 19:34:24', NULL, '2025-12-16 19:33:40', '2025-12-16 19:34:24'),
(34, 'App\\Models\\User', 12, 'auth_token', '5bf14c0075e42cf425db39ef2ff7f93f073503d097dac778a498ac48d9c2f883', '[\"*\"]', '2025-12-16 19:44:48', NULL, '2025-12-16 19:35:49', '2025-12-16 19:44:48'),
(35, 'App\\Models\\User', 12, 'auth_token', 'dee5a4958a03e29b0f7a7b26e2f7058cf8690bd34f4ddc81830830b6996fcce1', '[\"*\"]', '2025-12-16 19:49:21', NULL, '2025-12-16 19:44:51', '2025-12-16 19:49:21'),
(36, 'App\\Models\\User', 12, 'auth_token', '700f3b30a144afc5cf4553f1f70563289c4cd72a0e75cc0d6acd2b6442bf52f2', '[\"*\"]', '2025-12-16 19:54:53', NULL, '2025-12-16 19:49:28', '2025-12-16 19:54:53'),
(37, 'App\\Models\\User', 12, 'auth_token', 'e5042edb8177ca066d4ccfd85049c73d4c7469542a1645e8f0f57f72d723ea2d', '[\"*\"]', '2025-12-16 19:59:20', NULL, '2025-12-16 19:55:04', '2025-12-16 19:59:20'),
(38, 'App\\Models\\User', 1, 'auth_token', '2b2d03c8b05dd56a58a53b3ba93dd95b32b882708f31e782431078e113d09555', '[\"*\"]', '2025-12-17 01:29:56', NULL, '2025-12-16 20:43:26', '2025-12-17 01:29:56'),
(39, 'App\\Models\\User', 1, 'auth_token', 'c8bdb40ef6fc5fc9b933eb5f3000b997b050a21a09dac404976eeb2a17f00f8f', '[\"*\"]', '2025-12-17 08:39:58', NULL, '2025-12-17 03:00:52', '2025-12-17 08:39:58'),
(40, 'App\\Models\\User', 1, 'auth_token', '705a927ed9479018d2d5ff0f70d0cb627af135bd80889b2f7643ce43abd2f4a9', '[\"*\"]', '2025-12-18 07:07:42', NULL, '2025-12-17 21:44:29', '2025-12-18 07:07:42'),
(41, 'App\\Models\\User', 1, 'auth_token', '3f3add890e733c565daf5728a9d44f8d60b6213ad62df92896a3392edb0a2249', '[\"*\"]', '2025-12-18 00:26:06', NULL, '2025-12-18 00:06:37', '2025-12-18 00:26:06'),
(42, 'App\\Models\\User', 1, 'auth_token', '62f58128fe44e3924da1532b32190b269fa2c1fc6b1fb9acbb3d9f6f14c58a8d', '[\"*\"]', '2025-12-18 20:49:30', NULL, '2025-12-18 19:35:18', '2025-12-18 20:49:30'),
(43, 'App\\Models\\User', 12, 'auth_token', 'd68a5e3963ef23b8183515013235bd4c5f1e852f01fd4a618449394880456f50', '[\"*\"]', '2025-12-18 20:49:53', NULL, '2025-12-18 20:49:47', '2025-12-18 20:49:53'),
(44, 'App\\Models\\User', 1, 'auth_token', 'eca2f0e6a5a28bb6710dc904590275659e8a541abfa7fa414a72f13c92f33a09', '[\"*\"]', '2025-12-18 22:09:58', NULL, '2025-12-18 20:50:12', '2025-12-18 22:09:58'),
(45, 'App\\Models\\User', 12, 'auth_token', '5a50852070caf3f09648cc97cb84f40b9109db7199a06601ad2d05f2d7e3587e', '[\"*\"]', '2025-12-18 23:11:02', NULL, '2025-12-18 22:17:48', '2025-12-18 23:11:02'),
(46, 'App\\Models\\User', 12, 'auth_token', '05a09c9dc8fa82692c27b4b3f32b7c93fec2e51a1819c1034c874454a73ae4e5', '[\"*\"]', '2025-12-18 23:22:06', NULL, '2025-12-18 23:17:39', '2025-12-18 23:22:06'),
(47, 'App\\Models\\User', 12, 'auth_token', 'f4a0008cb12250bd2d5d120648d37a29ef655de056c47d9c242271a4977a0f26', '[\"*\"]', '2025-12-18 23:31:04', NULL, '2025-12-18 23:22:03', '2025-12-18 23:31:04'),
(48, 'App\\Models\\User', 12, 'auth_token', '1822a06aa35506b352719f74cce2c0ac3eb00637290f81fdb4435066ff464dca', '[\"*\"]', '2025-12-18 23:38:19', NULL, '2025-12-18 23:31:05', '2025-12-18 23:38:19'),
(49, 'App\\Models\\User', 12, 'auth_token', '8de2e4a4f886feba6c93ae5c470be373908e574ce2aa8e42fcf05537e247bf77', '[\"*\"]', '2025-12-18 23:43:09', NULL, '2025-12-18 23:38:20', '2025-12-18 23:43:09'),
(50, 'App\\Models\\User', 12, 'auth_token', 'f3d7e60764e99f594be12b501d0c17a830434a986e5507fa8759569bf7bffd49', '[\"*\"]', '2025-12-19 00:07:34', NULL, '2025-12-18 23:43:26', '2025-12-19 00:07:34'),
(51, 'App\\Models\\User', 12, 'auth_token', 'c1bab59b32ac50de7f7ed3d0272a30ef3e44ae9abde32f28ea03c986da795bdf', '[\"*\"]', '2025-12-19 02:01:26', NULL, '2025-12-19 01:53:14', '2025-12-19 02:01:26'),
(52, 'App\\Models\\User', 12, 'auth_token', 'af15f5211f82a8d828c1c471c76c72551d52f400fcb4901dda6a0a0530e02a5a', '[\"*\"]', '2025-12-19 02:32:18', NULL, '2025-12-19 02:01:52', '2025-12-19 02:32:18'),
(53, 'App\\Models\\User', 12, 'auth_token', 'ec12adb7c00af8b6551b8039c478bae7391ac373ae7bea54b219d036f2066046', '[\"*\"]', '2025-12-19 02:35:57', NULL, '2025-12-19 02:33:14', '2025-12-19 02:35:57'),
(54, 'App\\Models\\User', 12, 'auth_token', '7ea10eaf9c87e8e57fd721e8d7ed24cc67dd0fd65bb4652066aa058ff54202ac', '[\"*\"]', '2025-12-19 02:46:11', NULL, '2025-12-19 02:46:05', '2025-12-19 02:46:11'),
(55, 'App\\Models\\User', 12, 'auth_token', '661acc883348e11fdb2595a17f8eb224440f27e74a6f82695e5fefbbc4da7726', '[\"*\"]', '2025-12-19 02:46:23', NULL, '2025-12-19 02:46:12', '2025-12-19 02:46:23'),
(56, 'App\\Models\\User', 12, 'auth_token', 'fd9b5de078e8d1fd5f0e4fe0c03e3f039f0f55fa3196893c8876ae9cf24ec067', '[\"*\"]', '2025-12-19 02:46:45', NULL, '2025-12-19 02:46:33', '2025-12-19 02:46:45'),
(57, 'App\\Models\\User', 12, 'auth_token', 'effd77707d55a0d584f6980212a49f91056275991c4219b7553caa7a6bd4aaf2', '[\"*\"]', '2025-12-19 02:53:56', NULL, '2025-12-19 02:47:35', '2025-12-19 02:53:56'),
(58, 'App\\Models\\User', 12, 'auth_token', 'eb4a7c95792835851dc4c98c0870709436231df719a2383fb044fde7d781cbdc', '[\"*\"]', '2025-12-19 03:08:20', NULL, '2025-12-19 02:58:14', '2025-12-19 03:08:20'),
(59, 'App\\Models\\User', 12, 'auth_token', 'f63508b31af01bd218ee2d591f065db9d29ac5a10ff5c563638e33e3475a4d49', '[\"*\"]', '2025-12-19 03:47:01', NULL, '2025-12-19 03:09:12', '2025-12-19 03:47:01'),
(60, 'App\\Models\\User', 12, 'auth_token', '8e85fecc76f75678382ff6791dc32911c72d2dcda5015e5cfbe0fea0f787e6d8', '[\"*\"]', '2025-12-19 04:43:30', NULL, '2025-12-19 03:47:33', '2025-12-19 04:43:30'),
(61, 'App\\Models\\User', 1, 'auth_token', 'b3e5416d57e0a7cbc25b3d8d281ad9d88393b1b7b66c1ad7a8cac18e47cee348', '[\"*\"]', '2025-12-19 05:30:37', NULL, '2025-12-19 04:43:50', '2025-12-19 05:30:37'),
(62, 'App\\Models\\User', 1, 'auth_token', 'e0992c18279a2173de88d50f5b78a0ca57fe66e0b47b2e3dec6f6f65fc4549ad', '[\"*\"]', '2025-12-19 05:54:18', NULL, '2025-12-19 05:54:14', '2025-12-19 05:54:18'),
(63, 'App\\Models\\User', 12, 'auth_token', 'e033f459d704181fa45cd79a8b301fddf3cfd5f0daea74c1fd0a8485047486e9', '[\"*\"]', '2025-12-19 08:44:01', NULL, '2025-12-19 05:55:03', '2025-12-19 08:44:01'),
(64, 'App\\Models\\User', 12, 'auth_token', '7dba9464d025edab531d4cddc68664765cc702b81b938691c2ba54656ea709ab', '[\"*\"]', '2025-12-19 21:39:55', NULL, '2025-12-19 19:38:21', '2025-12-19 21:39:55'),
(65, 'App\\Models\\User', 1, 'auth_token', 'e062070f0bc169aee2ea85294b52b883aa0697c7212db9348810d5579bc10e50', '[\"*\"]', '2025-12-19 21:47:11', NULL, '2025-12-19 21:42:21', '2025-12-19 21:47:11'),
(66, 'App\\Models\\User', 12, 'auth_token', '7f05d8655a2fbc55405a43c40ed16e8251c4ede38fb1bb34f68fe520962dff83', '[\"*\"]', '2025-12-20 01:54:21', NULL, '2025-12-19 21:47:37', '2025-12-20 01:54:21'),
(67, 'App\\Models\\User', 1, 'auth_token', 'fa089d7380b1e33929732d602b62dbe69e124f5433c32182e351dd974e67fe74', '[\"*\"]', '2025-12-20 01:54:37', NULL, '2025-12-20 01:54:23', '2025-12-20 01:54:37'),
(68, 'App\\Models\\User', 1, 'auth_token', '9065ea2f0902ef9eea2b7b5a320bce48dd7aa505b02d0ba3115c17cc442af823', '[\"*\"]', '2025-12-20 03:00:19', NULL, '2025-12-20 01:54:46', '2025-12-20 03:00:19'),
(69, 'App\\Models\\User', 12, 'auth_token', 'ce885f54b7b25ca03985c9363436f2e4e9777f1429eb14743ec740979a1f3864', '[\"*\"]', '2025-12-20 03:24:52', NULL, '2025-12-20 03:09:54', '2025-12-20 03:24:52'),
(70, 'App\\Models\\User', 12, 'auth_token', '0fa51967ee236d20e704e81e963af6a8a31a55586ce46d906c2ac8e453b7fc3c', '[\"*\"]', '2025-12-20 03:28:21', NULL, '2025-12-20 03:28:13', '2025-12-20 03:28:21'),
(71, 'App\\Models\\User', 12, 'auth_token', '7aa52f8527de74f35fc367aa27e6f4eb752049af5e2aa355e2d4e6ef841ec6e8', '[\"*\"]', '2025-12-20 04:32:41', NULL, '2025-12-20 04:22:56', '2025-12-20 04:32:41'),
(72, 'App\\Models\\User', 12, 'auth_token', '84afac5eba5aacc94a54a15915026b753cdec1b1b40d26e6a4f8a3a911b198cf', '[\"*\"]', '2025-12-20 06:11:25', NULL, '2025-12-20 04:38:59', '2025-12-20 06:11:25'),
(73, 'App\\Models\\User', 12, 'auth_token', 'b7f2088a50c9fdc055a29a11f326c47c25592f32b4b9b937b251ad40d829636e', '[\"*\"]', '2025-12-20 06:33:04', NULL, '2025-12-20 06:12:58', '2025-12-20 06:33:04'),
(74, 'App\\Models\\User', 1, 'auth_token', 'c2836b6ecf7de3d56da4c6eb38af607fb0be36d7b6cc3174447a9dcdb620f89b', '[\"*\"]', '2025-12-20 06:34:54', NULL, '2025-12-20 06:34:18', '2025-12-20 06:34:54'),
(75, 'App\\Models\\User', 1, 'auth_token', '60eaa01e0a76dcc6173bd9e35eb7eb7100a620cb306414ba4c6553c91f74f3d2', '[\"*\"]', '2025-12-23 08:05:00', NULL, '2025-12-23 08:05:00', '2025-12-23 08:05:00'),
(76, 'App\\Models\\User', 1, 'auth_token', '336274adc053afee297a96acaf86d9074fda3a3b3f49016f5b55f4d3a60b0406', '[\"*\"]', '2025-12-25 19:56:17', NULL, '2025-12-25 19:50:55', '2025-12-25 19:56:17'),
(77, 'App\\Models\\User', 1, 'auth_token', '7c84db679ad4ac83e22741bd327f1e0206628e716c44da04e0e436ef0b56d1c7', '[\"*\"]', NULL, NULL, '2025-12-25 20:19:13', '2025-12-25 20:19:13'),
(78, 'App\\Models\\User', 1, 'auth_token', 'e1abcd41ef1382385acaf401859bee7e3e730f7ee15d0f613f325d6a2f3cd7cf', '[\"*\"]', NULL, NULL, '2025-12-25 20:19:32', '2025-12-25 20:19:32'),
(79, 'App\\Models\\User', 1, 'auth_token', '31d7c2028ea9d8a0da88682874f250f203c73c7c05e42f122604250b06fe2a86', '[\"*\"]', NULL, NULL, '2025-12-25 20:27:58', '2025-12-25 20:27:58'),
(80, 'App\\Models\\User', 1, 'auth_token', 'ee543e54b3778044e42a918828bba1f6c71a39ee757b6ecc1f30b81525de5bd9', '[\"*\"]', NULL, NULL, '2025-12-25 20:28:26', '2025-12-25 20:28:26'),
(81, 'App\\Models\\User', 1, 'auth_token', 'cabd026d5e9b6891421f1d9eeb41ef0c23926bdf89aee9c37c663837c88d107c', '[\"*\"]', NULL, NULL, '2025-12-25 20:29:16', '2025-12-25 20:29:16'),
(82, 'App\\Models\\User', 1, 'auth_token', 'ee228da7955135209d5c27f94ee6e47e87abb2c4a0498a29db6476c87902af71', '[\"*\"]', NULL, NULL, '2025-12-25 20:35:48', '2025-12-25 20:35:48'),
(83, 'App\\Models\\User', 1, 'auth_token', 'd25c582de8e244d31e68b473da0322388574937065be047fd7c827b45b4be58d', '[\"*\"]', '2025-12-25 20:45:18', NULL, '2025-12-25 20:45:07', '2025-12-25 20:45:18'),
(84, 'App\\Models\\User', 13, 'auth_token', '87c722bb2a0773eb9262997c0bceebb704220ba11ea57ada0191d37e09ff8ca5', '[\"*\"]', NULL, NULL, '2025-12-25 20:46:04', '2025-12-25 20:46:04'),
(85, 'App\\Models\\User', 1, 'auth_token', 'c691661d13da21d6f5cd807e63fd1b82d399ed4ec2139df7848415c41472c635', '[\"*\"]', '2025-12-28 03:04:32', NULL, '2025-12-28 03:04:31', '2025-12-28 03:04:32'),
(86, 'App\\Models\\User', 1, 'auth_token', 'c2150040312dea749935aa8616d3ca7fe96124e28b684306c6b197117e899136', '[\"*\"]', '2025-12-28 03:09:54', NULL, '2025-12-28 03:05:07', '2025-12-28 03:09:54'),
(87, 'App\\Models\\User', 1, 'auth_token', '0a226726807e4efa3b1ea8d1ebef60df90ae5a5e904ab88bd2bf5c23f115d4e8', '[\"*\"]', NULL, NULL, '2025-12-30 18:24:45', '2025-12-30 18:24:45'),
(88, 'App\\Models\\User', 1, 'auth_token', 'c876672529a44e4ee01c0e0340b57673fd5ba118d9708c7bdabe19d61d781344', '[\"*\"]', '2025-12-30 18:26:45', NULL, '2025-12-30 18:25:11', '2025-12-30 18:26:45'),
(89, 'App\\Models\\User', 12, 'auth_token', 'a6f5652deafdef8b86fc02b3223618ddee08b996b2fd22940bae1be8b9b58945', '[\"*\"]', '2025-12-30 18:28:49', NULL, '2025-12-30 18:27:18', '2025-12-30 18:28:49'),
(90, 'App\\Models\\User', 12, 'auth_token', '90c324eb278e6f8bbb8fb528aa65704a2d04fb14878d08475134cd8e6c755cb0', '[\"*\"]', '2025-12-31 01:43:44', NULL, '2025-12-31 01:43:44', '2025-12-31 01:43:44'),
(91, 'App\\Models\\User', 1, 'auth_token', '8f7cba389a33b60679cced3cbc77bc3279e298e7c71a1188a709d0df94ca6a81', '[\"*\"]', '2025-12-31 03:20:51', NULL, '2025-12-31 01:44:27', '2025-12-31 03:20:51'),
(92, 'App\\Models\\User', 1, 'auth_token', '69d735772a443c370f955883b9f05d6d9bd84277c284e202dea71a43ba3f3688', '[\"*\"]', '2025-12-31 03:47:35', NULL, '2025-12-31 03:21:12', '2025-12-31 03:47:35'),
(93, 'App\\Models\\User', 1, 'auth_token', 'bcff4695513790834008d6f8396175e84a83f8feab796e93f3100241eea631f1', '[\"*\"]', '2025-12-31 03:48:33', NULL, '2025-12-31 03:48:19', '2025-12-31 03:48:33'),
(94, 'App\\Models\\User', 12, 'auth_token', '9b748b7d6812c256801eb3e320b9e46179933849101972cbffc56c65a7a28d32', '[\"*\"]', '2025-12-31 03:59:07', NULL, '2025-12-31 03:48:46', '2025-12-31 03:59:07'),
(95, 'App\\Models\\User', 1, 'auth_token', 'c37519191fda07cda2b986d7082e35287b59ab0bb912ffbc1ddc7e262432e3ef', '[\"*\"]', '2025-12-31 03:59:35', NULL, '2025-12-31 03:59:25', '2025-12-31 03:59:35'),
(96, 'App\\Models\\User', 12, 'auth_token', '6f5e165e9adee195de52b8ce627dddb6d92ffc5301d9f702e8a533bbf65eec6b', '[\"*\"]', '2025-12-31 04:37:20', NULL, '2025-12-31 04:06:25', '2025-12-31 04:37:20'),
(97, 'App\\Models\\User', 12, 'auth_token', 'e52cae90af138905774d09ed0de37f770e82397b2b69601cada1c1b3ddd822f2', '[\"*\"]', '2026-01-01 21:51:38', NULL, '2026-01-01 21:51:38', '2026-01-01 21:51:38'),
(98, 'App\\Models\\User', 1, 'auth_token', '59c16d7bb67732567fb42b1854c3cd98b671472b730368e6d16a9ba9762bc8ba', '[\"*\"]', '2026-01-02 22:41:05', NULL, '2026-01-02 22:40:32', '2026-01-02 22:41:05'),
(99, 'App\\Models\\User', 1, 'auth_token', 'c19e0086ba53761983fbef233a8aabe86addfedf9db30adcb6916b1a503fb3f5', '[\"*\"]', '2026-01-04 07:46:54', NULL, '2026-01-04 07:46:45', '2026-01-04 07:46:54'),
(100, 'App\\Models\\User', 1, 'auth_token', '6ba980d2e337f1c4603f8ddaa6a6160b8aea2d0614d7a228ac3b7bb434742269', '[\"*\"]', '2026-01-04 07:49:15', NULL, '2026-01-04 07:48:23', '2026-01-04 07:49:15'),
(101, 'App\\Models\\User', 1, 'auth_token', 'c1e6a6bf7fcca5e4a47867bc65890f0b4b154726d5222e2676da3ae5bbc18e99', '[\"*\"]', '2026-01-05 20:27:52', NULL, '2026-01-05 19:16:37', '2026-01-05 20:27:52'),
(102, 'App\\Models\\User', 1, 'auth_token', 'ca25308837913b28c5d7a25ac7d37d3549a5a4317609a14f914e130a56dc8e26', '[\"*\"]', '2026-01-06 07:42:37', NULL, '2026-01-06 07:26:25', '2026-01-06 07:42:37'),
(103, 'App\\Models\\User', 1, 'auth_token', '1a17f9ff6bdc9689432131495d9ab9b7d5db46619b48b4e382c61c518abab9e7', '[\"*\"]', '2026-01-07 01:30:59', NULL, '2026-01-06 21:25:13', '2026-01-07 01:30:59'),
(104, 'App\\Models\\User', 1, 'auth_token', '1bb5ffcc885767332e148ffc7992f920eed43118124b5cc02ca1406b0fdd7ae1', '[\"*\"]', '2026-01-07 02:43:02', NULL, '2026-01-07 02:42:10', '2026-01-07 02:43:02'),
(105, 'App\\Models\\User', 1, 'auth_token', '69d02332d8ab9e7658393ef2f862483aea8707a93417836c43aa67a9133b2961', '[\"*\"]', '2026-01-07 08:22:48', NULL, '2026-01-07 06:28:50', '2026-01-07 08:22:48'),
(106, 'App\\Models\\User', 1, 'auth_token', 'ee18627473a4af23f688994d14a3a12f2751937a905c8eb56c509f70b98914eb', '[\"*\"]', '2026-01-07 19:44:49', NULL, '2026-01-07 19:43:45', '2026-01-07 19:44:49'),
(107, 'App\\Models\\User', 1, 'auth_token', 'e1bfd8a57b57460ac1412a452aa8d8fd89fc79197197ad0d0a90038f08bd889d', '[\"*\"]', '2026-01-07 21:30:49', NULL, '2026-01-07 21:28:27', '2026-01-07 21:30:49'),
(108, 'App\\Models\\User', 1, 'auth_token', '144b428c9a7c8df74efe741ea0a3c4f4031ecadc1f9b9140dba842ed34ec6e25', '[\"*\"]', '2026-01-08 03:32:42', NULL, '2026-01-07 22:40:32', '2026-01-08 03:32:42'),
(109, 'App\\Models\\User', 1, 'auth_token', '42cb5dbf3e612b433e82703dac6dd970b6ac7548760640f9f54cec1852f47107', '[\"*\"]', '2026-01-13 03:40:04', NULL, '2026-01-12 21:26:50', '2026-01-13 03:40:04'),
(110, 'App\\Models\\User', 1, 'auth_token', 'f83db8ce8b833b134624d86ddbf55d147682596f18b66e3227751e32cfe5558f', '[\"*\"]', NULL, NULL, '2026-01-22 20:13:25', '2026-01-22 20:13:25'),
(111, 'App\\Models\\User', 1, 'auth_token', 'dbb0c3745d0c08d4f07e88eb0004ead1bdf90f6854143e2214835e742a223911', '[\"*\"]', '2026-01-22 21:13:35', NULL, '2026-01-22 21:06:00', '2026-01-22 21:13:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `satuan_kegiatan`
--

CREATE TABLE `satuan_kegiatan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_satuan` varchar(50) NOT NULL,
  `alias` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `satuan_kegiatan`
--

INSERT INTO `satuan_kegiatan` (`id`, `nama_satuan`, `alias`, `created_at`, `updated_at`) VALUES
(1, 'Orang Bulan', 'OB', NULL, NULL),
(2, 'Orang Hari', 'OH', NULL, NULL),
(3, 'Orang Jam', 'OJ', NULL, NULL),
(4, 'Orang Kegiatan', 'OK', NULL, NULL),
(5, 'Dokumen', 'Dok', NULL, NULL),
(6, 'Responden', 'Resp', NULL, NULL),
(7, 'Rumah Tangga', 'RT', NULL, NULL),
(8, 'Blok Sensus', 'BS', NULL, NULL),
(9, 'Perusahaan', 'Perus', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('iS8hkV8t27Djpi1DBmrwy2xGfqPQ9mZv8Gv0i87K', NULL, '103.164.115.46', 'WinHTTP Example/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYnFmZk9IZEY0QVZPbzZldk41Q1JUbjN1bHFsM0l5cXE3ZDAwZDlaMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767840194),
('lfodQWS3Hmt5K4NzIQXll8HjjjzvIteMQnqkP0og', NULL, '103.162.205.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0x5NVJaQ1pZZzZVQTVCYllQRlNRQXo0b0FwVElVTkdFdTdJOWdzQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767792527),
('roFgRrAVosDjJo2xUZJwpxITEKAbsYlqgMcetbax', NULL, '103.162.205.220', 'WinHTTP Example/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVmNoRXlnRjRhS0Iwc2hkelhzYWZnME5pcGY1QTNjaFZPT214emd2biI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767792528),
('sqguqrpdAenE77Hl27smAChSugEY6Gw3E1aItskI', NULL, '103.164.115.46', 'WinHTTP Example/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidUc3OXBaMUlOQmxkRWkxV3pOTGlGU3FRdHBjQ05GWHRDZzBxRlFUdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767840222),
('tMwWdDECi8QfScVfiQRWASl3xx1aLV7lhbhcKkBd', NULL, '103.164.115.46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3BTZTZmTTdrWW14QTZQTGNpMVNneTJWMnoxNDFjYzE5VnVzcEl0ZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767846649),
('v8GZ7VT74FlFEwM67wib3TVR1uJindeW9j972yMC', NULL, '103.162.205.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZklFdXJYOGVRaEpZOGlMa0pNMHBRSkpIYzJnMmNzczZsVDFzb2Z1bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbWFraW5hc2lrLnNpZG9tZS5pZC9sb2dvLnBuZyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpXakZwUXNBbUk3Sk01cGFnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767799368);

-- --------------------------------------------------------

--
-- Struktur dari tabel `spk_setting`
--

CREATE TABLE `spk_setting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `periode` varchar(7) NOT NULL,
  `nomor_surat_format` varchar(100) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `nama_ppk` varchar(100) DEFAULT NULL,
  `nip_ppk` varchar(50) DEFAULT NULL,
  `jabatan_ppk` varchar(100) NOT NULL DEFAULT 'Pejabat Pembuat Komitmen',
  `komponen_honor` text DEFAULT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `spk_setting`
--

INSERT INTO `spk_setting` (`id`, `periode`, `nomor_surat_format`, `tanggal_surat`, `nama_ppk`, `nip_ppk`, `jabatan_ppk`, `komponen_honor`, `template_id`, `created_at`, `updated_at`) VALUES
(1, '2025-11', '000/33730/SPK.MITRA/11/2025', '2025-11-30', 'Ninik Sri L, S.ST, M.Si', '837987983729837429837498', 'Pejabat Pembuat Komitmen', 'biaya pajak, bea materai, dan jasa pelayanan keuangan', NULL, '2025-11-30 06:14:30', '2025-11-30 06:14:30'),
(2, '2025-12', '000/33730/SPK.MITRA/12/2025', '2025-11-26', 'Ninik Sri L, S.ST, M.Si', '837987983729837429837498', 'Pejabat Pembuat Komitmen', 'biaya pajak, bea materai, dan jasa pelayanan keuangan', 1, '2025-12-04 08:02:17', '2025-12-16 06:44:43'),
(5, '2026-02', '000/33730/SPK.MITRA/MM/YYYY', '2025-12-05', 'Ninik Sri L, S.ST, M.Si', '837987983729837429837498', 'Pejabat Pembuat Komitmen', 'biaya pajak, bea materai, dan jasa pelayanan keuangan', NULL, '2025-12-04 21:05:16', '2025-12-04 21:05:16'),
(6, '2025-07', '000/33730/SPK.MITRA/MM/YYYY', '2025-12-16', NULL, NULL, 'Pejabat Pembuat Komitmen', 'biaya pajak, bea materai, dan jasa pelayanan keuangan', NULL, '2025-12-15 19:32:39', '2025-12-15 19:32:39'),
(7, '2026-01', '000/33730/SPK.MITRA/MM/YYYY', '2026-01-06', 'tes', '24136123465132651', 'Pejabat Pembuat Komitmen', 'biaya pajak, bea materai, dan jasa pelayanan keuangan', NULL, '2026-01-05 20:25:24', '2026-01-05 20:25:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subkegiatan`
--

CREATE TABLE `subkegiatan` (
  `id` varchar(50) NOT NULL,
  `id_kegiatan` bigint(20) UNSIGNED NOT NULL,
  `nama_sub_kegiatan` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `subkegiatan`
--

INSERT INTO `subkegiatan` (`id`, `id_kegiatan`, `nama_sub_kegiatan`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`) VALUES
('sub10', 6, 'lapangan', 'lapangan', '2025-12-07', '2025-12-31', 'pending', '2025-12-07 04:10:19', NULL),
('sub11', 9, 'pencacah lapangan', '', '2025-12-04', '2025-12-30', 'pending', '2025-12-11 19:57:40', NULL),
('sub12', 9, 'pengolahan', '', '2025-12-03', '2025-12-02', 'pending', '2025-12-11 19:57:40', NULL),
('sub13', 8, 'tes', 'tes', '2025-12-15', '2025-12-31', 'pending', '2025-12-14 19:16:09', '2025-12-14 19:16:09'),
('sub14', 8, 'tes2', 'tes', '2025-12-15', '2025-12-31', 'pending', '2025-12-14 19:16:10', '2025-12-14 19:16:10'),
('sub15', 8, 'tes 3', NULL, '2025-12-01', '2025-12-31', 'pending', '2025-12-15 01:43:53', '2025-12-15 01:43:53'),
('sub16', 8, 'tes 4', NULL, '2025-12-01', '2025-12-31', 'pending', '2025-12-18 06:13:55', '2025-12-18 06:13:55'),
('sub17', 10, 'PENDATAAN - TRIWULAN I', 'Kegiatan Pemeriksaan Lapangan Sakernas', '2026-01-01', '2026-01-01', 'aktif', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
('sub18', 10, 'UPDATING/LISTING - TRIWULAN I', NULL, '2026-01-01', '2026-02-28', 'aktif', '2025-12-30 18:26:24', '2025-12-30 18:26:24'),
('sub2', 6, 'pendataan penduduk', 'mendata jumlah penduduk', '2025-11-01', '2025-11-26', 'pending', '2025-11-27 06:40:26', NULL),
('sub3', 6, 'pendataan umur', 'mendata umur', '2025-10-31', '2025-11-27', 'pending', '2025-11-27 06:40:26', NULL),
('sub4', 7, 'Pencacahan Industri', 'Pemetaan industri di kota salatiga', '2025-06-04', '2025-06-30', 'pending', '2025-11-28 07:03:02', NULL),
('sub5', 7, 'Pendataan Pendapatan Masyarakat', 'Pendataan', '2025-12-01', '2025-12-30', 'pending', '2025-12-04 01:13:22', NULL),
('sub6', 7, 'Pendataan Penerimaan Bantuan', 'Pendataan', '2025-11-01', '2025-11-30', 'pending', '2025-12-04 01:13:22', NULL),
('sub7', 8, 'Pendataan', 'pendataan', '2025-12-23', '2026-01-27', 'pending', '2025-12-04 20:31:42', NULL),
('sub8', 8, 'pengolahan', 'pengolahan', '2026-01-27', '2026-02-19', 'pending', '2025-12-04 21:00:58', NULL),
('sub9', 8, 'pencacahan', 'pencacahan', '2026-01-01', '2026-01-29', 'pending', '2025-12-06 18:03:17', NULL);

--
-- Trigger `subkegiatan`
--
DELIMITER $$
CREATE TRIGGER `tg_subkegiatan_before_insert` BEFORE INSERT ON `subkegiatan` FOR EACH ROW BEGIN
                DECLARE max_id INT DEFAULT 0;
                
                -- Ambil angka tertinggi dari ID yang ada (misal sub10 -> 10)
                SELECT MAX(CAST(SUBSTRING(id, 4) AS UNSIGNED)) INTO max_id FROM subkegiatan;
                
                IF max_id IS NULL THEN
                    SET max_id = 0;
                END IF;
                
                -- Set ID baru (misal sub11)
                SET NEW.id = CONCAT("sub", max_id + 1);
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'app_logo', 'uploads/system/cmB0dOEcGv3EpOhtwixU5w94hqCCjPosh7Rvmm9J.png', '2025-12-20 02:53:58', '2025-12-20 02:53:58'),
(2, 'home_background', 'uploads/system/dLEnzGV6oNHZ2fAyeMm1PGmuuZ8LWdpElua9qTTf.jpg', '2025-12-20 03:00:19', '2025-12-20 03:00:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_aktif`
--

CREATE TABLE `tahun_aktif` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `status` enum('aktif','non-aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tahun_aktif`
--

INSERT INTO `tahun_aktif` (`id`, `user_id`, `tahun`, `status`, `created_at`, `updated_at`) VALUES
(1, 180, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(2, 181, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(3, 182, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(4, 183, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(5, 184, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(6, 185, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(7, 186, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(8, 187, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(9, 188, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(10, 189, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(11, 190, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(12, 191, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(13, 192, '2025', 'aktif', '2025-12-05 06:43:04', NULL),
(14, 193, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(15, 194, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(16, 195, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(17, 196, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(18, 197, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(19, 198, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(20, 199, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(21, 200, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(22, 201, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(23, 202, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(24, 203, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(25, 204, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(26, 205, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(27, 206, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(28, 207, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(29, 208, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(30, 209, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(31, 210, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(32, 211, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(33, 212, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(34, 213, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(35, 214, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(36, 215, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(37, 216, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(38, 217, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(39, 218, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(40, 219, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(41, 220, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(42, 221, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(43, 222, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(44, 223, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(45, 224, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(46, 225, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(47, 226, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(48, 227, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(49, 228, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(50, 229, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(51, 230, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(52, 231, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(53, 232, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(54, 233, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(55, 234, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(56, 235, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(57, 236, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(58, 237, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(59, 238, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(60, 239, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(61, 240, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(62, 241, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(63, 242, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(64, 243, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(65, 244, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(66, 245, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(67, 246, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(68, 247, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(69, 248, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(70, 249, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(71, 250, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(72, 251, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(73, 252, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(74, 253, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(75, 254, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(76, 255, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(77, 256, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(78, 257, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(79, 258, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(80, 259, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(81, 260, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(82, 261, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(83, 262, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(84, 263, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(85, 264, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(86, 265, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(87, 266, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(88, 267, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(89, 268, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(90, 269, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(91, 270, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(92, 271, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(93, 272, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(94, 273, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(95, 274, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(96, 275, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(97, 276, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(98, 277, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(99, 278, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(100, 279, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(101, 280, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(102, 281, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(103, 282, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(104, 283, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(105, 284, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(106, 285, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(107, 286, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(108, 287, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(109, 288, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(110, 289, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(111, 290, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(112, 291, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(113, 292, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(114, 293, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(115, 294, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(116, 295, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(117, 296, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(118, 297, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(119, 298, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(120, 299, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(121, 300, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(122, 301, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(123, 302, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(124, 303, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(125, 304, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(126, 305, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(127, 306, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(128, 307, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(129, 308, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(130, 309, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(131, 310, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(132, 311, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(133, 312, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(134, 313, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(135, 314, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(136, 315, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(137, 316, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(138, 317, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(140, 319, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(141, 320, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(142, 321, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(143, 322, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(144, 323, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(145, 324, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(146, 325, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(147, 326, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(148, 327, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(149, 328, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(150, 329, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(151, 330, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(152, 331, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(153, 332, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(154, 333, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(155, 334, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(156, 335, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(157, 336, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(158, 337, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(159, 338, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(160, 339, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(161, 340, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(162, 341, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(163, 342, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(164, 343, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(165, 344, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(166, 345, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(167, 346, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(168, 347, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(169, 348, '2025', 'aktif', '2025-12-05 06:43:05', NULL),
(170, 180, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(171, 181, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(172, 182, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(173, 183, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(174, 184, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(175, 185, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(176, 186, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(177, 187, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(178, 188, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(179, 189, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(180, 190, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(181, 191, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(182, 192, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(183, 193, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(184, 194, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(185, 195, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(186, 196, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(187, 197, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(188, 198, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(189, 199, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(190, 200, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(191, 201, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(192, 202, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(193, 203, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(194, 204, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(195, 205, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(196, 206, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(197, 207, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(198, 208, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(199, 209, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(200, 210, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(201, 211, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(202, 212, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(203, 213, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(204, 214, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(205, 215, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(206, 216, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(207, 217, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(208, 218, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(209, 219, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(210, 220, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(211, 221, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(212, 222, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(213, 223, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(214, 224, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(215, 225, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(216, 226, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(217, 227, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(218, 228, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(219, 229, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(220, 230, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(221, 231, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(222, 232, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(223, 233, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(224, 234, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(225, 235, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(226, 236, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(227, 237, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(228, 238, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(229, 239, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(230, 240, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(231, 241, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(232, 242, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(233, 243, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(234, 244, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(235, 245, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(236, 246, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(237, 247, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(238, 248, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(239, 249, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(240, 250, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(241, 251, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(242, 252, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(243, 253, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(244, 254, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(245, 255, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(246, 256, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(247, 257, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(248, 258, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(249, 259, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(250, 260, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(251, 261, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(252, 262, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(253, 263, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(254, 264, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(255, 265, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(256, 266, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(257, 267, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(258, 268, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(259, 269, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(260, 270, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(261, 271, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(262, 272, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(263, 273, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(264, 274, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(265, 275, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(266, 276, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(267, 277, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(268, 278, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(269, 279, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(270, 280, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(271, 281, '2026', 'aktif', '2026-12-05 06:45:54', NULL),
(272, 282, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(273, 283, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(274, 284, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(275, 285, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(276, 286, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(277, 287, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(278, 288, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(279, 289, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(280, 290, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(281, 291, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(282, 292, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(283, 293, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(284, 294, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(285, 295, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(286, 296, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(287, 297, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(288, 298, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(289, 299, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(290, 300, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(291, 301, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(292, 302, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(293, 303, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(294, 304, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(295, 305, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(296, 306, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(297, 307, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(298, 308, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(299, 309, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(300, 310, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(301, 311, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(302, 312, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(303, 313, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(304, 314, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(305, 315, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(306, 316, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(307, 317, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(309, 319, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(310, 320, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(311, 321, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(312, 322, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(313, 323, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(314, 324, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(315, 325, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(316, 326, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(317, 327, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(318, 328, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(319, 329, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(320, 330, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(321, 331, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(322, 332, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(323, 333, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(324, 334, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(325, 335, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(326, 336, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(327, 337, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(328, 338, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(329, 339, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(330, 340, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(331, 341, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(332, 342, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(333, 343, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(334, 344, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(335, 345, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(336, 346, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(337, 347, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(338, 348, '2026', 'aktif', '2026-12-05 06:45:55', NULL),
(339, 349, '2025', 'aktif', '2025-12-05 22:38:39', NULL),
(340, 350, '2025', 'aktif', '2025-12-11 20:02:10', NULL),
(342, 352, '2025', 'aktif', '2025-12-13 22:42:24', '2025-12-13 22:42:24'),
(345, 355, '2025', 'aktif', '2025-12-13 23:37:01', '2025-12-13 23:37:01'),
(346, 355, '2026', 'aktif', '2025-12-13 23:37:42', '2025-12-13 23:37:42'),
(347, 356, '2026', 'aktif', '2026-01-04 07:49:12', '2026-01-04 07:49:12'),
(348, 349, '2026', 'aktif', '2026-01-07 08:19:49', '2026-01-07 08:19:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `template_bagian_teks`
--

CREATE TABLE `template_bagian_teks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_bagian` enum('pembuka','pihak_pertama','pihak_kedua','kesepakatan','penutup') NOT NULL,
  `isi_teks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `template_bagian_teks`
--

INSERT INTO `template_bagian_teks` (`id`, `template_id`, `jenis_bagian`, `isi_teks`, `created_at`, `updated_at`) VALUES
(1, 1, 'pembuka', 'Pada hari ini {{TANGGAL_TERBILANG}}, bertempat di BPS Kota Salatiga, yang bertandatangan di bawah ini:', NULL, NULL),
(2, 1, 'pihak_pertama', '{{NAMA_PPK}}, {{JABATAN_PPK}} Badan Pusat Statistik Kota Salatiga, berkedudukan di BPS Kota Salatiga, bertindak untuk dan atas nama Badan Pusat Statistik Kota Salatiga, selanjutnya disebut sebagai PIHAK PERTAMA.', NULL, NULL),
(3, 1, 'pihak_kedua', '{{NAMA_MITRA}}, Mitra Statistik, berkedudukan di {{ALAMAT_MITRA}}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut PIHAK KEDUA.', NULL, NULL),
(4, 1, 'kesepakatan', 'Bahwa PIHAK PERTAMA dan PIHAK KEDUA yang secara bersama-sama disebut PARA PIHAK, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Statistik pada Badan Pusat Statistik Kota Salatiga, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:', NULL, NULL),
(5, 1, 'penutup', 'Demikian Perjanjian ini dibuat dan ditandatangani oleh PARA PIHAK dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari PIHAK manapun dan untuk dilaksanakan oleh PARA PIHAK.\n\n{{Lampiran}}', NULL, NULL),
(6, 2, 'pembuka', 'Pada hari ini {{TANGGAL_TERBILANG}}, bertempat di BPS Kota Salatiga, yang bertandatangan di bawah ini:', NULL, NULL),
(7, 2, 'pihak_pertama', '{{NAMA_PPK}}, {{JABATAN_PPK}} Badan Pusat Statistik Kota Salatiga, berkedudukan di BPS Kota Salatiga, bertindak untuk dan atas nama Badan Pusat Statistik Kota Salatiga, selanjutnya disebut sebagai PIHAK PERTAMA.', NULL, NULL),
(8, 2, 'pihak_kedua', '{{NAMA_MITRA}}, Mitra Statistik, berkedudukan di {{ALAMAT_MITRA}}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut PIHAK KEDUA.', NULL, NULL),
(9, 2, 'kesepakatan', 'Bahwa PIHAK PERTAMA dan PIHAK KEDUA yang secara bersama-sama disebut PARA PIHAK, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Statistik pada Badan Pusat Statistik Kota Salatiga, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:', NULL, NULL),
(10, 2, 'penutup', 'Demikian Perjanjian ini dibuat dan ditandatangani oleh PARA PIHAK dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari PIHAK manapun dan untuk dilaksanakan oleh PARA PIHAK.\n\n{{Lampiran}}', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `template_pasal`
--

CREATE TABLE `template_pasal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `nomor_pasal` int(11) NOT NULL,
  `judul_pasal` varchar(255) DEFAULT NULL,
  `isi_pasal` text DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `template_pasal`
--

INSERT INTO `template_pasal` (`id`, `template_id`, `nomor_pasal`, `judul_pasal`, `isi_pasal`, `urutan`, `created_at`, `updated_at`) VALUES
(49, 1, 1, '', 'PIHAK PERTAMA memberikan pekerjaan kepada PIHAK KEDUA dan PIHAK KEDUA menerima pekerjaan dari PIHAK PERTAMA sebagai Petugas Pendataan Lapangan Kegiatan Statistik pada Badan Pusat Statistik Kota Salatiga, dengan lingkup pekerjaan yang ditetapkan oleh PIHAK PERTAMA. {{TOTAL_HONOR}}', 1, NULL, NULL),
(50, 1, 2, '', 'Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian, Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Statistik pada Badan Pusat Statistik Kota Salatiga, dan ketentuan-ketentuan yang ditetapkan oleh PIHAK PERTAMA.', 2, NULL, NULL),
(51, 1, 3, '', 'Jangka Waktu Perjanjian terhitung sejak tanggal {{TANGGAL_SURAT}} sampai dengan selesainya periode kegiatan bulan ini. {{Break_Space}}', 3, NULL, NULL),
(52, 1, 4, '', 'PIHAK KEDUA berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh PIHAK PERTAMA sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2 di wilayah kerja masing-masing.', 4, NULL, NULL),
(53, 1, 5, '', '(1) PIHAK KEDUA berhak untuk mendapatkan honorarium petugas dari PIHAK PERTAMA sebesar {{TOTAL_HONOR}} ({{TERBILANG}}) untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, termasuk biaya pajak, bea materai, dan jasa pelayanan keuangan.\n(2) PIHAK KEDUA tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau terdapat tambahan waktu pelaksanaan pekerjaan lapangan.', 5, NULL, NULL),
(54, 1, 6, '', '(1) Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah PIHAK KEDUA menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada PIHAK PERTAMA.\n(2) Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh PIHAK PERTAMA kepada PIHAK KEDUA sesuai dengan ketentuan peraturan perundang-undangan.', 6, NULL, NULL),
(55, 1, 7, '', 'Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh PARA PIHAK.', 7, NULL, NULL),
(56, 1, 8, '', 'PIHAK PERTAMA dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal PIHAK KEDUA tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.', 8, NULL, NULL),
(57, 1, 9, '', '(1) Apabila PIHAK KEDUA mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak menyelesaikan pekerjaan yang menjadi tanggungjawabnya, maka PIHAK PERTAMA akan memberikan Surat Pemutusan Perjanjian Kerja kepada PIHAK KEDUA.\n(2) Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (1), PIHAK PERTAMA membayarkan honorarium kepada PIHAK KEDUA secara proporsional sesuai pekerjaan yang telah dilaksanakan. {{Break_Space}}', 9, NULL, NULL),
(58, 1, 10, '', '(1) Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, PIHAK KEDUA memberitahukan kepada PIHAK PERTAMA dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.\n(2) Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh PIHAK KEDUA dihentikan sementara dan dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan, PIHAK KEDUA berhak menerima honorarium secara proporsional sesuai pekerjaan yang telah dilaksanakan.', 10, NULL, NULL),
(59, 1, 11, '', 'Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian tambahan/addendum dan merupakan bagian tidak terpisahkan dari perjanjian ini.', 11, NULL, NULL),
(60, 1, 12, '', '(1) Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat.\n(2) Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), PARA PIHAK sepakat menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kota Salatiga.', 12, NULL, NULL),
(61, 2, 1, 'RUANG LINGKUP PEKERJAAN', 'PIHAK PERTAMA memberikan pekerjaan kepada PIHAK KEDUA dan PIHAK KEDUA menerima pekerjaan dari PIHAK PERTAMA sebagai Petugas Pendataan Lapangan Kegiatan Statistik pada Badan Pusat Statistik Kota Salatiga, dengan lingkup pekerjaan yang ditetapkan oleh PIHAK PERTAMA.', 1, NULL, NULL),
(62, 2, 2, '', 'Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian.', 2, NULL, NULL),
(63, 2, 3, '', 'Jangka Waktu Perjanjian terhitung sejak tanggal {{TANGGAL_SURAT}} sampai dengan selesainya periode kegiatan bulan ini.{{Break_Space}}', 3, NULL, NULL),
(64, 2, 4, 'HAK DAN KEWAJIBAN', 'PIHAK KEDUA berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh PIHAK PERTAMA sampai selesai.', 4, NULL, NULL),
(65, 2, 5, '', '(1) PIHAK KEDUA berhak untuk mendapatkan honorarium petugas dari PIHAK PERTAMA sebesar {{TOTAL_HONOR}} ({{TERBILANG}}).\n(2) PIHAK KEDUA tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal.', 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin_user', 'admin@example.com', '$2y$12$/adhAuKvE9.6gSCv86JzmuICKt3AZQ2VZod1S9d5abA.x5knbzlxG', 'admin', '2025-11-16 01:32:44', '2025-12-13 01:39:08'),
(2, 'user_biasa_UPDATED', 'user_updated@example.com', '$2b$10$CG6KHz2M1DFddRImZaH9K.l2cgTr/yfz4BR9gYmnD1XaaLhJLjbWW', 'user', '2025-11-16 01:34:31', NULL),
(4, 'pambudi', 'pambudi@gmail.com', '$2b$10$xQ/6v2vjXarFU/ZHruDeJe7B.3Iku9/439lx59wau5l2D2xmXglQu', 'user', '2025-11-17 23:25:08', NULL),
(5, 'user2', 'user2@gmail.com', '$2b$10$uvdh5kM0yj3D1o5Nt0C4d.CAjqxOznmROxyP3yGHw4Ce4D.VQD2eC', 'user', '2025-11-18 08:50:12', NULL),
(6, 'ammaar', 'user12@gmail.com', '$2b$10$6cpdEXyweGSwh8HD2Lq.pOWzmVSAE6vrr3phoJ3RQXpjMcwt.vQcq', 'user', '2025-11-23 03:38:00', NULL),
(7, 'siraj', 'admin2@gmail.com', '$2b$10$XoluQu6wsi5WZVmcA2xL.uttaXD35lXePZ5Ol7wsdUqO30opSRu8K', 'user', '2025-11-23 03:38:00', NULL),
(8, 'al', 'al@yahoo.com', '$2b$10$QjXIxq9t/infUkNeAM53Mu1omoaZhBu55HMEPdikCmlvD/hU/4QRC', 'user', '2025-11-23 03:38:00', NULL),
(10, 'citra_lestari', 'citra.lestari@email.com', '$2y$12$hRHA3UfdDMiFVdoePArTHOPwxViICqiQFz5pn1UvV3uYyNs/ItJBu', 'user', '2025-12-13 19:25:34', '2025-12-13 19:25:34'),
(11, 'dimas_admin', 'dimas.pratama@kantor.com', '$2y$12$se0ITNUeM9QxSN792EZCney71EgyxiMdj3NTE7UtOrIqO5GRrm6Re', 'admin', '2025-12-13 19:25:34', '2025-12-13 19:25:34'),
(12, 'user', 'user@gmail.com', '$2y$12$topiPqMAR7SvhJRi9zil3.k1RiGF2qjK3.q8VqCou7hY3mJE7rJYe', 'user', '2025-12-16 08:24:59', '2025-12-16 08:24:59'),
(13, 'tes', 'tes@gmail.com', '$2y$12$b6k.9Oe.Zqfr5UwEz53umujQLIkyYxW0vJIJzKJ44PzH0ZWmMIpdG', 'user', '2025-12-25 20:46:04', '2025-12-25 20:46:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `aturan_periode`
--
ALTER TABLE `aturan_periode`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aturan_periode_periode_unique` (`periode`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `honorarium`
--
ALTER TABLE `honorarium`
  ADD PRIMARY KEY (`id`),
  ADD KEY `honorarium_id_subkegiatan_foreign` (`id_subkegiatan`),
  ADD KEY `honorarium_kode_jabatan_foreign` (`kode_jabatan`);

--
-- Indeks untuk tabel `jabatan_mitra`
--
ALTER TABLE `jabatan_mitra`
  ADD PRIMARY KEY (`kode_jabatan`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelompok_penugasan`
--
ALTER TABLE `kelompok_penugasan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_penugasan_mitra` (`id_penugasan`,`id_mitra`),
  ADD KEY `kelompok_penugasan_id_mitra_foreign` (`id_mitra`),
  ADD KEY `kelompok_penugasan_kode_jabatan_foreign` (`kode_jabatan`);

--
-- Indeks untuk tabel `kelompok_perencanaan`
--
ALTER TABLE `kelompok_perencanaan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_perencanaan_mitra` (`id_perencanaan`,`id_mitra`),
  ADD KEY `kelompok_perencanaan_id_mitra_foreign` (`id_mitra`),
  ADD KEY `kelompok_perencanaan_kode_jabatan_foreign` (`kode_jabatan`);

--
-- Indeks untuk tabel `master_template_spk`
--
ALTER TABLE `master_template_spk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mitra`
--
ALTER TABLE `mitra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mitra_nik_unique` (`nik`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `penugasan`
--
ALTER TABLE `penugasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penugasan_id_subkegiatan_foreign` (`id_subkegiatan`),
  ADD KEY `penugasan_id_pengawas_foreign` (`id_pengawas`);

--
-- Indeks untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perencanaan_id_subkegiatan_foreign` (`id_subkegiatan`),
  ADD KEY `perencanaan_id_pengawas_foreign` (`id_pengawas`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `satuan_kegiatan`
--
ALTER TABLE `satuan_kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `spk_setting`
--
ALTER TABLE `spk_setting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `spk_setting_periode_unique` (`periode`),
  ADD KEY `spk_setting_template_id_foreign` (`template_id`);

--
-- Indeks untuk tabel `subkegiatan`
--
ALTER TABLE `subkegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subkegiatan_id_kegiatan_foreign` (`id_kegiatan`);

--
-- Indeks untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indeks untuk tabel `tahun_aktif`
--
ALTER TABLE `tahun_aktif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_aktif_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `template_bagian_teks`
--
ALTER TABLE `template_bagian_teks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_bagian_teks_template_id_foreign` (`template_id`);

--
-- Indeks untuk tabel `template_pasal`
--
ALTER TABLE `template_pasal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_pasal_template_id_foreign` (`template_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_username_unique` (`username`),
  ADD UNIQUE KEY `user_email_unique` (`email`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aturan_periode`
--
ALTER TABLE `aturan_periode`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `honorarium`
--
ALTER TABLE `honorarium`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `kelompok_penugasan`
--
ALTER TABLE `kelompok_penugasan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT untuk tabel `kelompok_perencanaan`
--
ALTER TABLE `kelompok_perencanaan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `master_template_spk`
--
ALTER TABLE `master_template_spk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `mitra`
--
ALTER TABLE `mitra`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=357;

--
-- AUTO_INCREMENT untuk tabel `penugasan`
--
ALTER TABLE `penugasan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT untuk tabel `satuan_kegiatan`
--
ALTER TABLE `satuan_kegiatan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `spk_setting`
--
ALTER TABLE `spk_setting`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tahun_aktif`
--
ALTER TABLE `tahun_aktif`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=349;

--
-- AUTO_INCREMENT untuk tabel `template_bagian_teks`
--
ALTER TABLE `template_bagian_teks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `template_pasal`
--
ALTER TABLE `template_pasal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `honorarium`
--
ALTER TABLE `honorarium`
  ADD CONSTRAINT `honorarium_id_subkegiatan_foreign` FOREIGN KEY (`id_subkegiatan`) REFERENCES `subkegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `honorarium_kode_jabatan_foreign` FOREIGN KEY (`kode_jabatan`) REFERENCES `jabatan_mitra` (`kode_jabatan`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelompok_penugasan`
--
ALTER TABLE `kelompok_penugasan`
  ADD CONSTRAINT `kelompok_penugasan_id_mitra_foreign` FOREIGN KEY (`id_mitra`) REFERENCES `mitra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kelompok_penugasan_id_penugasan_foreign` FOREIGN KEY (`id_penugasan`) REFERENCES `penugasan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kelompok_penugasan_kode_jabatan_foreign` FOREIGN KEY (`kode_jabatan`) REFERENCES `jabatan_mitra` (`kode_jabatan`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelompok_perencanaan`
--
ALTER TABLE `kelompok_perencanaan`
  ADD CONSTRAINT `kelompok_perencanaan_id_mitra_foreign` FOREIGN KEY (`id_mitra`) REFERENCES `mitra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kelompok_perencanaan_id_perencanaan_foreign` FOREIGN KEY (`id_perencanaan`) REFERENCES `perencanaan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kelompok_perencanaan_kode_jabatan_foreign` FOREIGN KEY (`kode_jabatan`) REFERENCES `jabatan_mitra` (`kode_jabatan`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penugasan`
--
ALTER TABLE `penugasan`
  ADD CONSTRAINT `penugasan_id_pengawas_foreign` FOREIGN KEY (`id_pengawas`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penugasan_id_subkegiatan_foreign` FOREIGN KEY (`id_subkegiatan`) REFERENCES `subkegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  ADD CONSTRAINT `perencanaan_id_pengawas_foreign` FOREIGN KEY (`id_pengawas`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `perencanaan_id_subkegiatan_foreign` FOREIGN KEY (`id_subkegiatan`) REFERENCES `subkegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `spk_setting`
--
ALTER TABLE `spk_setting`
  ADD CONSTRAINT `spk_setting_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `master_template_spk` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `subkegiatan`
--
ALTER TABLE `subkegiatan`
  ADD CONSTRAINT `subkegiatan_id_kegiatan_foreign` FOREIGN KEY (`id_kegiatan`) REFERENCES `kegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tahun_aktif`
--
ALTER TABLE `tahun_aktif`
  ADD CONSTRAINT `tahun_aktif_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `mitra` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `template_bagian_teks`
--
ALTER TABLE `template_bagian_teks`
  ADD CONSTRAINT `template_bagian_teks_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `master_template_spk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `template_pasal`
--
ALTER TABLE `template_pasal`
  ADD CONSTRAINT `template_pasal_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `master_template_spk` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
