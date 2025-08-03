-- Studio Foto Cekrek Database Export
-- Generated on: 2025-07-12 08:53:43

SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for `gallery`
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` enum('wedding','portrait','event','family','corporate') NOT NULL,
  `photographer_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `photographer_id` (`photographer_id`),
  CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `packages`
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `packages`
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19', 'Solo', '1 orang, waktu 10 menit, free all soft file (GDRIVE)', '30000.00', '0', '10', '999', '1', 'Free all soft file (GDRIVE)', 'individual', NULL, '1', '2025-07-09 23:46:49', '2025-07-09 23:46:53', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20', 'Duo', '2 orang, waktu 15 menit, free all soft file (GDRIVE)', '50000.00', '0', '15', '999', '2', 'Free all soft file (GDRIVE)', 'couple', NULL, '1', '2025-07-09 23:46:50', '2025-07-09 23:46:53', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21', 'Trio', '3 orang, waktu 15 menit, free all soft file (GDRIVE)', '70000.00', '0', '15', '999', '3', 'Free all soft file (GDRIVE)', 'group', NULL, '1', '2025-07-09 23:46:50', '2025-07-09 23:46:53', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22', 'Grup', '4-8 orang, waktu 25 menit, free all soft file (GDRIVE), Rp 20.000/orang', '20000.00', '0', '25', '999', '8', 'Free all soft file (GDRIVE), Harga per orang', 'group', NULL, '1', '2025-07-09 23:46:51', '2025-07-09 23:46:53', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23', 'Photobox', 'Max 6 orang, waktu 15 menit, free all soft file (GDRIVE), Rp 20.000/orang', '20000.00', '0', '15', '999', '6', 'Free all soft file (GDRIVE), Harga per orang', 'group', NULL, '1', '2025-07-09 23:46:51', '2025-07-09 23:46:53', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31', 'Formal', 'max 30 orang', '25000.00', '1', '40', '50', '30', 'Harga per orang', 'group', NULL, '1', '2025-07-10 02:15:50', '2025-07-10 02:15:50', NULL);

-- Table structure for `payments`
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','credit_card','e_wallet') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `payments`
INSERT INTO `payments` (`id`, `reservation_id`, `amount`, `payment_method`, `payment_date`, `transaction_id`, `payment_proof`, `notes`, `status`) VALUES ('7', '10', '20000.00', 'cash', '2025-07-10 02:12:40', 'TRX20250709201240928', NULL, '', 'pending');

-- Table structure for `reservations`
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `package_id` (`package_id`),
  KEY `photographer_id` (`photographer_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `reservations`
INSERT INTO `reservations` (`id`, `customer_id`, `package_id`, `photographer_id`, `reservation_date`, `start_time`, `end_time`, `location`, `special_requests`, `total_amount`, `number_of_people`, `status`, `payment_status`, `created_at`, `updated_at`) VALUES ('10', '4', '23', NULL, '2025-07-11', '13:00:00', '13:15:00', NULL, '', '20000.00', '1', 'confirmed', 'paid', '2025-07-10 02:12:14', '2025-07-10 02:13:48');

-- Table structure for `schedules`
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photographer_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_photographer_datetime` (`photographer_id`,`date`,`start_time`),
  CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `settings`
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('1', 'studio_name', 'Studio Foto Cekrek', 'Nama studio foto', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('2', 'studio_address', 'Jl. Contoh No. 123, Jakarta', 'Alamat studio', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('3', 'studio_phone', '021-12345678', 'Nomor telepon studio', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('4', 'studio_email', 'info@studiofotocekrek.com', 'Email studio', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('5', 'booking_advance_days', '7', 'Minimal hari booking di muka', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('6', 'working_hours_start', '09:00', 'Jam buka studio', '2025-07-07 15:57:36');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('7', 'working_hours_end', '18:00', 'Jam tutup studio', '2025-07-07 15:57:36');

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `profile_image`, `created_at`, `updated_at`, `is_active`) VALUES ('1', 'admin', 'admin@studiofotocekrek.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, NULL, 'admin', NULL, '2025-07-07 15:57:36', '2025-07-07 15:57:36', '1');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `profile_image`, `created_at`, `updated_at`, `is_active`) VALUES ('4', 'shahrins', 'shlmroktavn@gmail.com', '$2y$10$KrSpfO4UrkRY/jJ2ttTIVuT62JifnX6CvBcRwM7G.FyyJ3MHr4fdS', 'shahrins algieba', '085709982869', 'indonesia', 'customer', NULL, '2025-07-10 02:10:58', '2025-07-10 02:10:58', '1');

SET FOREIGN_KEY_CHECKS = 1;
