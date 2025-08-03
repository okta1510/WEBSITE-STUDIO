-- Studio Foto Cekrek - Clean Database Export
-- Generated on: 2025-07-12 10:03:08
-- Contains ORIGINAL packages only (Solo, Duo, Trio, Grup, Photobox)
-- NO test data, NO customer data

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `packages`
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'Solo', 'Paket foto untuk 1 orang', '30000.00', '0', '10', '25', '1', 'Foto digital, makeup ringan', 'individual', NULL, '1', '2025-07-12 15:57:28', '2025-07-12 15:57:28', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'Duo', 'Paket foto untuk 2 orang', '50000.00', '0', '15', '40', '2', 'Foto digital, makeup ringan', 'couple', NULL, '1', '2025-07-12 15:57:28', '2025-07-12 15:57:28', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', 'Trio', 'Paket foto untuk 3 orang', '70000.00', '0', '15', '50', '3', 'Foto digital, makeup ringan', 'group', NULL, '1', '2025-07-12 15:57:28', '2025-07-12 15:57:28', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', 'Grup', 'Paket foto untuk grup 4-8 orang', '20000.00', '0', '25', '75', '8', 'Foto digital, makeup ringan, Harga per orang', 'group', NULL, '1', '2025-07-12 15:57:28', '2025-07-12 15:57:28', NULL);
INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration_hours`, `duration_minutes`, `max_photos`, `max_people`, `includes`, `category`, `image`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', 'Photobox', 'Paket foto photobox untuk 1-6 orang', '20000.00', '0', '15', '50', '6', 'Foto digital, props photobox, Harga per orang', '', NULL, '1', '2025-07-12 15:57:28', '2025-07-12 15:57:28', NULL);

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

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
