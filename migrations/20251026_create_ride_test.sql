-- Migration: create ride_test table for maps feature
-- Up: create table
CREATE TABLE IF NOT EXISTS `ride_test` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ride_name` VARCHAR(255) DEFAULT NULL,
  `driver_id` INT UNSIGNED DEFAULT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `departure_location` VARCHAR(500) DEFAULT NULL,
  `arrival_location` VARCHAR(500) DEFAULT NULL,
  `departure_lat` DECIMAL(10,7) DEFAULT NULL,
  `departure_lng` DECIMAL(10,7) DEFAULT NULL,
  `arrival_lat` DECIMAL(10,7) DEFAULT NULL,
  `arrival_lng` DECIMAL(10,7) DEFAULT NULL,
  `ride_date` DATE DEFAULT NULL,
  `ride_time` TIME DEFAULT NULL,
  `total_seats` INT DEFAULT NULL,
  `available_seats` INT DEFAULT NULL,
  `cost_per_seat` DECIMAL(10,2) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_driver` (`driver_id`),
  KEY `idx_vehicle` (`vehicle_id`),
  KEY `idx_ride_date` (`ride_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Down: drop table
-- DROP TABLE IF EXISTS `ride_test`;
