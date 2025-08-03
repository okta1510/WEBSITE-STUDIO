-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Jul 2025 pada 06.31
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
-- Database: `studio_foto_cekrek`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` enum('wedding','portrait','event','family','corporate') NOT NULL,
  `photographer_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_hours` int(11) NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `max_photos` int(11) DEFAULT NULL,
  `max_people` int(11) DEFAULT 1,
  `includes` text DEFAULT NULL,
  `category` enum('individual','couple','group','family','corporate','wedding','portrait','event') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(19, 'Solo', '1 orang, waktu 10 menit, free all soft file (GDRIVE)', 30000.00, 0, 10, 999, 1, 'Free all soft file (GDRIVE)', 'individual', NULL, 1, '2025-07-09 15:46:49', '2025-07-09 15:46:53', NULL),
(20, 'Duo', '2 orang, waktu 15 menit, free all soft file (GDRIVE)', 50000.00, 0, 15, 999, 2, 'Free all soft file (GDRIVE)', 'couple', NULL, 1, '2025-07-09 15:46:50', '2025-07-09 15:46:53', NULL),
(21, 'Trio', '3 orang, waktu 15 menit, free all soft file (GDRIVE)', 70000.00, 0, 15, 999, 3, 'Free all soft file (GDRIVE)', 'group', NULL, 1, '2025-07-09 15:46:50', '2025-07-09 15:46:53', NULL),
(22, 'Grup', '4-8 orang, waktu 25 menit, free all soft file (GDRIVE), Rp 20.000/orang', 20000.00, 0, 25, 999, 8, 'Free all soft file (GDRIVE), Harga per orang', 'group', NULL, 1, '2025-07-09 15:46:51', '2025-07-09 15:46:53', NULL),
(23, 'Photobox', 'Max 6 orang, waktu 15 menit, free all soft file (GDRIVE), Rp 20.000/orang', 20000.00, 0, 15, 999, 6, 'Free all soft file (GDRIVE), Harga per orang', 'group', NULL, 1, '2025-07-09 15:46:51', '2025-07-09 15:46:53', NULL),
(31, 'Formal', 'max 30 orang', 25000.00, 1, 40, 50, 30, 'Harga per orang', 'group', NULL, 1, '2025-07-09 18:15:50', '2025-07-09 18:15:50', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','credit_card','e_wallet') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

-- No payment data - fresh installation

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `photographer_id` int(11) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `number_of_people` int(11) DEFAULT 1,
  `status` enum('pending','confirmed','in_progress','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reservations`
--

-- No reservation data - fresh installation

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `photographer_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'studio_name', 'Studio Foto Cekrek', 'Nama studio foto', '2025-07-07 07:57:36'),
(2, 'studio_address', 'Jl. Contoh No. 123, Jakarta', 'Alamat studio', '2025-07-07 07:57:36'),
(3, 'studio_phone', '021-12345678', 'Nomor telepon studio', '2025-07-07 07:57:36'),
(4, 'studio_email', 'info@studiofotocekrek.com', 'Email studio', '2025-07-07 07:57:36'),
(5, 'booking_advance_days', '7', 'Minimal hari booking di muka', '2025-07-07 07:57:36'),
(6, 'working_hours_start', '08:00', 'Jam buka studio', '2025-07-07 07:57:36'),
(7, 'working_hours_end', '21:00', 'Jam tutup studio', '2025-07-07 07:57:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin','photographer') DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

-- Admin user only - fresh installation
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `profile_image`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', 'admin@studiofotocekrek.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, NULL, 'admin', NULL, '2025-07-07 07:57:36', '2025-07-07 07:57:36', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `photographer_id` (`photographer_id`);

--
-- Indeks untuk tabel `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indeks untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `photographer_id` (`photographer_id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_photographer_datetime` (`photographer_id`,`date`,`start_time`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
