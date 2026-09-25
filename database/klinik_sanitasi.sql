-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 21, 2026 at 01:59 PM
-- Server version: 8.0.30
-- PHP Version: 8.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `klinik_sanitasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `desa`
--

CREATE TABLE `desa` (
  `id_desa` int NOT NULL,
  `nama_desa` varchar(100) NOT NULL,
  `kode_pos` varchar(10) DEFAULT '68175',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edukasi_stbm`
--

CREATE TABLE `edukasi_stbm` (
  `id_edukasi` int NOT NULL,
  `id_author` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `kategori` enum('air_bersih','stbm','mhm') NOT NULL,
  `konten` text NOT NULL,
  `gambar_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_ikl`
--

CREATE TABLE `inspeksi_ikl` (
  `id_inspeksi` int NOT NULL,
  `id_jadwal` int NOT NULL,
  `jenis_sarana_air` enum('sumur_bor','sumur_terlindung','pdam','mata_air','lainnya') NOT NULL,
  `jarak_sumber_pencemar` int NOT NULL,
  `p1_dinding_sumur_retak` tinyint(1) DEFAULT '0',
  `p2_penutup_tidak_rapat` tinyint(1) DEFAULT '0',
  `p3_lantai_becek_retak` tinyint(1) DEFAULT '0',
  `p4_spal_tersumbat` tinyint(1) DEFAULT '0',
  `p5_air_keruh_berbau` tinyint(1) DEFAULT '0',
  `total_skor_ya` int DEFAULT '0',
  `kategori_risiko` enum('aman','risiko_sedang','risiko_tinggi') NOT NULL,
  `rekomendasi_sanitarian` text NOT NULL,
  `tanggal_inspeksi` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_inspeksi`
--

CREATE TABLE `jadwal_inspeksi` (
  `id_jadwal` int NOT NULL,
  `id_laporan` int DEFAULT NULL,
  `id_operator` int NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jenis_kunjungan` enum('ikl_laporan_warga','ikl_rutin_rt') NOT NULL DEFAULT 'ikl_laporan_warga',
  `status_kunjungan` enum('terjadwal','selesai','batal') DEFAULT 'terjadwal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_warga`
--

CREATE TABLE `laporan_warga` (
  `id_laporan` int NOT NULL,
  `kode_tiket` varchar(20) NOT NULL,
  `nama_pelapor` varchar(150) NOT NULL,
  `nik_pelapor` char(16) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `id_desa` int NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `kategori_laporan` enum('air_masalah','sanitasi_lingkungan') NOT NULL DEFAULT 'air_masalah',
  `deskripsi` text NOT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `status_laporan` enum('menunggu','dijadwalkan','selesai','ditolak') DEFAULT 'menunggu',
  `alasan_penolakan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_notifikasi_wa`
--

CREATE TABLE `log_notifikasi_wa` (
  `id_log` int NOT NULL,
  `id_laporan` int NOT NULL,
  `no_wa_tujuan` varchar(20) NOT NULL,
  `pesan_terkirim` text NOT NULL,
  `status_kirim` enum('pending','terkirim','gagal') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_21_134233_create_cache_table', 0),
(5, '2026_09_21_134233_create_cache_locks_table', 0),
(6, '2026_09_21_134233_create_desa_table', 0),
(7, '2026_09_21_134233_create_edukasi_stbm_table', 0),
(8, '2026_09_21_134233_create_failed_jobs_table', 0),
(9, '2026_09_21_134233_create_inspeksi_ikl_table', 0),
(10, '2026_09_21_134233_create_jadwal_inspeksi_table', 0),
(11, '2026_09_21_134233_create_job_batches_table', 0),
(12, '2026_09_21_134233_create_jobs_table', 0),
(13, '2026_09_21_134233_create_laporan_warga_table', 0),
(14, '2026_09_21_134233_create_log_notifikasi_wa_table', 0),
(15, '2026_09_21_134233_create_password_reset_tokens_table', 0),
(16, '2026_09_21_134233_create_sessions_table', 0),
(17, '2026_09_21_134233_create_users_table', 0),
(18, '2026_09_21_134236_add_foreign_keys_to_edukasi_stbm_table', 0),
(19, '2026_09_21_134236_add_foreign_keys_to_inspeksi_ikl_table', 0),
(20, '2026_09_21_134236_add_foreign_keys_to_jadwal_inspeksi_table', 0),
(21, '2026_09_21_134236_add_foreign_keys_to_laporan_warga_table', 0),
(22, '2026_09_21_134236_add_foreign_keys_to_log_notifikasi_wa_table', 0);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `role` enum('sanitarian','staf_backup_kluster4','kepala_puskesmas','admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `desa`
--
ALTER TABLE `desa`
  ADD PRIMARY KEY (`id_desa`);

--
-- Indexes for table `edukasi_stbm`
--
ALTER TABLE `edukasi_stbm`
  ADD PRIMARY KEY (`id_edukasi`),
  ADD KEY `id_author` (`id_author`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `inspeksi_ikl`
--
ALTER TABLE `inspeksi_ikl`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD UNIQUE KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `jadwal_inspeksi`
--
ALTER TABLE `jadwal_inspeksi`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_operator` (`id_operator`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laporan_warga`
--
ALTER TABLE `laporan_warga`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `id_desa` (`id_desa`);

--
-- Indexes for table `log_notifikasi_wa`
--
ALTER TABLE `log_notifikasi_wa`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `desa`
--
ALTER TABLE `desa`
  MODIFY `id_desa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edukasi_stbm`
--
ALTER TABLE `edukasi_stbm`
  MODIFY `id_edukasi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_ikl`
--
ALTER TABLE `inspeksi_ikl`
  MODIFY `id_inspeksi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_inspeksi`
--
ALTER TABLE `jadwal_inspeksi`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_warga`
--
ALTER TABLE `laporan_warga`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_notifikasi_wa`
--
ALTER TABLE `log_notifikasi_wa`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `edukasi_stbm`
--
ALTER TABLE `edukasi_stbm`
  ADD CONSTRAINT `edukasi_stbm_ibfk_1` FOREIGN KEY (`id_author`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `inspeksi_ikl`
--
ALTER TABLE `inspeksi_ikl`
  ADD CONSTRAINT `inspeksi_ikl_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_inspeksi` (`id_jadwal`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_inspeksi`
--
ALTER TABLE `jadwal_inspeksi`
  ADD CONSTRAINT `jadwal_inspeksi_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan_warga` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_inspeksi_ibfk_2` FOREIGN KEY (`id_operator`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `laporan_warga`
--
ALTER TABLE `laporan_warga`
  ADD CONSTRAINT `laporan_warga_ibfk_1` FOREIGN KEY (`id_desa`) REFERENCES `desa` (`id_desa`) ON DELETE CASCADE;

--
-- Constraints for table `log_notifikasi_wa`
--
ALTER TABLE `log_notifikasi_wa`
  ADD CONSTRAINT `log_notifikasi_wa_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan_warga` (`id_laporan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;