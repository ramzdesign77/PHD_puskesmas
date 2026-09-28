-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 28, 2026 at 01:07 PM
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
-- Table structure for table `desa`
--

CREATE TABLE `desa` (
  `id_desa` int NOT NULL,
  `nama_desa` varchar(100) NOT NULL,
  `kode_pos` varchar(10) DEFAULT '68175',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `desa`
--

INSERT INTO `desa` (`id_desa`, `nama_desa`, `kode_pos`, `created_at`) VALUES
(1, 'Ajung', '68175', '2026-09-26 09:11:50'),
(2, 'Pancakarya', '68175', '2026-09-26 09:11:50'),
(3, 'Klanceng', '68175', '2026-09-26 09:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `educations`
--

CREATE TABLE `educations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `educations`
--

INSERT INTO `educations` (`id`, `title`, `slug`, `category`, `excerpt`, `content`, `image`, `created_at`, `updated_at`) VALUES
(3, 'andjnnas', 'andjnnas', 'Sanitasi', 'uuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuu', 'uuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuu', NULL, '2026-09-21 22:22:09', '2026-09-21 22:22:09');

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

--
-- Dumping data for table `inspeksi_ikl`
--

INSERT INTO `inspeksi_ikl` (`id_inspeksi`, `id_jadwal`, `jenis_sarana_air`, `jarak_sumber_pencemar`, `p1_dinding_sumur_retak`, `p2_penutup_tidak_rapat`, `p3_lantai_becek_retak`, `p4_spal_tersumbat`, `p5_air_keruh_berbau`, `total_skor_ya`, `kategori_risiko`, `rekomendasi_sanitarian`, `tanggal_inspeksi`) VALUES
(1, 1, 'sumur_terlindung', 7, 1, 1, 1, 0, 1, 4, 'risiko_tinggi', 'Berikan bubuk kaporit untuk disinfeksi air sumur, edukasi KIE pengendapan air, dan perbaiki lantai sumur yang retak.', '2026-09-11'),
(2, 2, 'sumur_bor', 13, 1, 1, 1, 1, 1, 5, 'risiko_tinggi', 'hfjfghfgcvbcfdjhgdcg dt4ytfukv8ujm', '2026-09-26');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_inspeksi`
--

CREATE TABLE `jadwal_inspeksi` (
  `id_jadwal` int NOT NULL,
  `id_laporan` int DEFAULT NULL,
  `id_operator` int NOT NULL,
  `id_paket` bigint UNSIGNED DEFAULT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `jenis_kunjungan` enum('ikl_laporan_warga','ikl_rutin_rt') NOT NULL DEFAULT 'ikl_laporan_warga',
  `status_kunjungan` enum('terjadwal','selesai','batal') DEFAULT 'terjadwal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal_inspeksi`
--

INSERT INTO `jadwal_inspeksi` (`id_jadwal`, `id_laporan`, `id_operator`, `id_paket`, `tanggal_kunjungan`, `jam_mulai`, `jam_selesai`, `jenis_kunjungan`, `status_kunjungan`, `created_at`) VALUES
(1, 1, 1, NULL, '2026-09-11', NULL, NULL, 'ikl_laporan_warga', 'selesai', '2026-09-10 03:00:00'),
(2, 2, 1, NULL, '2026-09-28', NULL, NULL, 'ikl_laporan_warga', 'selesai', '2026-09-13 01:00:00'),
(3, NULL, 2, NULL, '2026-09-29', NULL, NULL, 'ikl_rutin_rt', 'terjadwal', '2026-09-26 16:04:11'),
(4, NULL, 1, NULL, '2026-09-30', NULL, NULL, 'ikl_rutin_rt', 'batal', '2026-09-26 16:24:48'),
(5, NULL, 2, NULL, '2026-09-30', NULL, NULL, 'ikl_rutin_rt', 'batal', '2026-09-26 16:25:05'),
(6, NULL, 2, NULL, '2026-09-30', NULL, NULL, 'ikl_rutin_rt', 'batal', '2026-09-26 16:25:17'),
(7, NULL, 2, NULL, '2026-09-30', '03:08:00', NULL, 'ikl_rutin_rt', 'terjadwal', '2026-09-26 17:05:21'),
(8, NULL, 2, NULL, '2026-09-30', '02:05:00', NULL, 'ikl_rutin_rt', 'terjadwal', '2026-09-26 17:05:49'),
(9, NULL, 2, NULL, '2026-09-30', '18:47:00', '21:50:00', 'ikl_rutin_rt', 'terjadwal', '2026-09-27 11:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_warga`
--

CREATE TABLE `laporan_warga` (
  `id_laporan` int NOT NULL,
  `kode_tiket` varchar(20) NOT NULL,
  `nama_pelapor` varchar(150) NOT NULL,
  `nik_pelapor` varchar(255) DEFAULT NULL,
  `no_wa` varchar(255) DEFAULT NULL,
  `id_desa` int NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `kategori_laporan` enum('air_masalah','sanitasi_lingkungan') NOT NULL DEFAULT 'air_masalah',
  `deskripsi` text NOT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `status_laporan` enum('menunggu','dibaca','diterima','ditolak','dijadwalkan','selesai') NOT NULL DEFAULT 'menunggu',
  `alasan_penolakan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan_warga`
--

INSERT INTO `laporan_warga` (`id_laporan`, `kode_tiket`, `nama_pelapor`, `nik_pelapor`, `no_wa`, `id_desa`, `rt`, `rw`, `kategori_laporan`, `deskripsi`, `foto_bukti`, `status_laporan`, `alasan_penolakan`, `created_at`) VALUES
(1, 'TKT-202609-001', 'Budi Santoso', '3509011203850001', '081234567890', 1, '002', '005', 'air_masalah', 'Air sumur berubah warna cokelat keruh dan berbau tanah setelah hujan deras/banjir semalam.', 'bukti/air_keruh_01.jpg', 'selesai', NULL, '2026-09-10 01:30:00'),
(2, 'TKT-202609-002', 'Siti Aminah', '3509014506920003', '085211223344', 1, '001', '002', 'air_masalah', 'Permintaan uji kualitas air sumur bor rumah tangga karena berbau besi/karat dan meninggalkan bercak kuning.', 'bukti/air_kuning_02.jpg', 'selesai', NULL, '2026-09-12 03:15:00'),
(3, 'TKT-202609-003', 'Rina Agustin', '3509015808900002', '081987654321', 2, '004', '001', 'sanitasi_lingkungan', 'Ditemukan genangan air tempat berkembang biak jentik nyamuk DBD pada penampungan limbah warga.', 'bukti/jentik_03.jpg', 'ditolak', 'afsdg', '2026-09-18 07:00:00'),
(4, 'TKT-202609-004', 'Ahmad Fauzi', '3509012211880004', '083812345678', 3, '003', '008', 'air_masalah', 'Laporan air keruh pipa PDAM warga.', 'bukti/pdam_04.jpg', 'ditolak', 'Layanan air minum jaringan pipa PDAM merupakan wewenang teknis PDAM, bukan ranah penanganan air sumur/sanitasi lingkungan Puskesmas.', '2026-09-22 02:45:00');

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

--
-- Dumping data for table `log_notifikasi_wa`
--

INSERT INTO `log_notifikasi_wa` (`id_log`, `id_laporan`, `no_wa_tujuan`, `pesan_terkirim`, `status_kirim`, `sent_at`) VALUES
(1, 1, '081234567890', 'Halo Budi Santoso, laporan Anda dengan kode tiket TKT-202609-001 telah selesai ditangani oleh tim Sanitarian Puskesmas.', 'terkirim', '2026-09-11 07:00:00'),
(2, 2, '085211223344', 'Halo Siti Aminah, laporan Anda dengan kode tiket TKT-202609-002 telah dijadwalkan untuk kunjungan IKL pada tanggal 2026-09-28.', 'terkirim', '2026-09-13 01:05:00'),
(3, 4, '083812345678', 'Halo Ahmad Fauzi, laporan Anda dengan kode tiket TKT-202609-004 ditolak. Alasan: Layanan air minum jaringan pipa PDAM merupakan wewenang teknis PDAM.', 'terkirim', '2026-09-22 03:00:00');

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
(22, '2026_09_21_134236_add_foreign_keys_to_log_notifikasi_wa_table', 0),
(23, '2026_09_21_131152_create_education_table', 2),
(24, '2026_09_21_150000_add_profile_fields_to_users_table', 3),
(25, '2026_09_21_160000_remove_area_from_users_table', 3),
(26, '2026_09_22_010000_create_petugas_table', 4),
(27, '2026_09_22_010100_create_paket_alat_table', 4),
(28, '2026_09_22_010200_add_id_paket_to_jadwal_inspeksi_table', 4),
(29, '2026_09_22_010300_alter_status_laporan_enum_laporan_warga', 4),
(30, '2026_09_22_020000_make_nik_no_wa_nullable_laporan_warga', 4),
(31, '2026_09_26_152345_add_nip_nik_to_users_table', 5),
(32, '2026_09_26_163810_add_time_to_jadwal_inspeksi_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `paket_alat`
--

CREATE TABLE `paket_alat` (
  `id_paket` bigint UNSIGNED NOT NULL,
  `nama_paket` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_laporan` enum('Air Bersih','Sanitasi','Sampah','Jentik Nyamuk','Limbah','PHBS','Lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_alat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('XRKJCoZ1Z5el0se1fT8ULEmqg57Yxxa2zDn6wj36', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI3ZURCbTA4dHdrSW5ueEd1YWZSVjZSYmJCcERhUnVCZjFKVlM4YVdwIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo0LCJyb2xlIjoiYWRtaW4iLCJ1c2VyX2lkIjo0LCJ1c2VyX25hbWUiOiJBZG1pbmlzdHJhdG9yIiwidXNlcm5hbWUiOiJhZG1pbiJ9', 1790600181);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `wilayah_kerja` varchar(150) DEFAULT NULL,
  `alamat` text,
  `foto` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `email`, `password`, `no_telepon`, `role`, `wilayah_kerja`, `alamat`, `foto`, `is_active`, `created_at`, `deleted_at`) VALUES
(1, 'klamfkdvklnd', '123456', 'ab@gmail.com', '$2y$12$dNvqKL8SQ5FRAu7b1lIRfOtM53urhw3TDS.8awdCo8X5DxqsBDDoK', '0821773943', 'sanitarian', 'sumber sari', 'lsknvs', NULL, 1, '2026-09-22 06:17:43', NULL),
(2, '127_ok', '222222', '22@gmail.com', '$2y$12$TNUFmL3Jg0CsgT5XLpwqN.0eQwXKjCu.mZVNHzsuONFY6u3hgn542', '2222', 'sanitarian', '222', '2222', 'petugas/4mDbaOiPG3FKirc0coIdO9B5IvG2opWvqLduCCZH.png', 1, '2026-09-22 06:49:32', NULL),
(3, '127_ok', '22223', '222@gmail.com', '$2y$12$DTY4OyNzpxxKC97sr5SguOVYqmqy9Pt/IjPFNiwUtxJsbewYlj.fq', '222', 'sanitarian', '22222', '222', 'petugas/uOS13VY6Jm27a8VVF7bOA40aZvSQqd2VJ1VNslmH.png', 1, '2026-09-22 06:51:06', NULL),
(4, 'Administrator', 'admin', 'admin@puskesmas.go.id', '$2y$12$friZE/aDobAMVOr5NRyvOeoke2xTpfgXNxOGp2/hJ6q3DiaJqOX8O', NULL, 'admin', NULL, NULL, NULL, 1, '2026-09-27 13:06:45', NULL),
(5, 'Petugas Kesling', 'petugas1', 'petugas@puskesmas.go.id', '$2y$12$CLm0TcbnueTFQePw0.1fKupHjNP0iNPOvlkuTof/4mwPbp42xZ6t.', NULL, 'petugas', NULL, NULL, NULL, 1, '2026-09-27 13:06:45', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `desa`
--
ALTER TABLE `desa`
  ADD PRIMARY KEY (`id_desa`);

--
-- Indexes for table `educations`
--
ALTER TABLE `educations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `educations_slug_unique` (`slug`);

--
-- Indexes for table `edukasi_stbm`
--
ALTER TABLE `edukasi_stbm`
  ADD PRIMARY KEY (`id_edukasi`),
  ADD KEY `id_author` (`id_author`);

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
-- Indexes for table `paket_alat`
--
ALTER TABLE `paket_alat`
  ADD PRIMARY KEY (`id_paket`);

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
  MODIFY `id_desa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `educations`
--
ALTER TABLE `educations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `edukasi_stbm`
--
ALTER TABLE `edukasi_stbm`
  MODIFY `id_edukasi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_ikl`
--
ALTER TABLE `inspeksi_ikl`
  MODIFY `id_inspeksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jadwal_inspeksi`
--
ALTER TABLE `jadwal_inspeksi`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `laporan_warga`
--
ALTER TABLE `laporan_warga`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `log_notifikasi_wa`
--
ALTER TABLE `log_notifikasi_wa`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `paket_alat`
--
ALTER TABLE `paket_alat`
  MODIFY `id_paket` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
