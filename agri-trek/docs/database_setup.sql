-- ============================================================
-- Agri-Trek: Complete Database Setup
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `agritrek`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `agritrek`;

-- -------------------------------------------------------
-- Table: users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`                     VARCHAR(255)    NOT NULL,
  `email`                    VARCHAR(255)    NOT NULL UNIQUE,
  `phone`                    VARCHAR(15)     NULL,
  `password`                 VARCHAR(255)    NOT NULL,
  `role`                     ENUM('admin','farmer','expert') NOT NULL DEFAULT 'farmer',
  `farmer_id`                BIGINT UNSIGNED NULL,
  `is_active`                TINYINT(1)      NOT NULL DEFAULT 1,
  `email_verified`           TINYINT(1)      NOT NULL DEFAULT 0,
  `email_verify_token`       VARCHAR(64)     NULL,
  `password_reset_token`     VARCHAR(64)     NULL,
  `password_reset_expires_at` TIMESTAMP      NULL,
  `profile_photo`            VARCHAR(255)    NULL,
  `bio`                      TEXT            NULL,
  `organization`             VARCHAR(100)    NULL,
  `remember_token`           VARCHAR(100)    NULL,
  `created_at`               TIMESTAMP       NULL,
  `updated_at`               TIMESTAMP       NULL,
  `deleted_at`               TIMESTAMP       NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: farmers
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `farmers` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100)    NOT NULL,
  `mobile`       VARCHAR(10)     NOT NULL UNIQUE,
  `address`      TEXT            NULL,
  `village`      VARCHAR(100)    NOT NULL,
  `district`     VARCHAR(100)    NULL,
  `aadhaar`      VARCHAR(12)     NULL UNIQUE,
  `dob`          DATE            NULL,
  `bank_account` VARCHAR(20)     NULL,
  `ifsc_code`    VARCHAR(15)     NULL,
  `notes`        TEXT            NULL,
  `deleted_at`   TIMESTAMP       NULL,
  `created_at`   TIMESTAMP       NULL,
  `updated_at`   TIMESTAMP       NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: lands
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lands` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `farmer_id`       BIGINT UNSIGNED NOT NULL,
  `area`            DECIMAL(8,2)    NOT NULL,
  `soil_type`       ENUM('Clay','Sandy','Loamy','Silty','Peaty','Chalky','Black Cotton') NOT NULL DEFAULT 'Loamy',
  `crop_type`       VARCHAR(100)    NOT NULL,
  `latitude`        DECIMAL(10,7)   NULL,
  `longitude`       DECIMAL(10,7)   NULL,
  `irrigation_type` ENUM('Canal','Drip','Sprinkler','Rainfed','Borewell','Pond') NOT NULL DEFAULT 'Rainfed',
  `survey_number`   VARCHAR(50)     NULL,
  `description`     TEXT            NULL,
  `created_at`      TIMESTAMP       NULL,
  `updated_at`      TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: schemes
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schemes` (
  `id`              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(150)     NOT NULL,
  `description`     TEXT             NULL,
  `eligibility`     TEXT             NULL,
  `subsidy_amount`  DECIMAL(12,2)    NOT NULL DEFAULT 0,
  `start_date`      DATE             NULL,
  `end_date`        DATE             NULL,
  `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
  `department`      VARCHAR(100)     NULL,
  `created_at`      TIMESTAMP        NULL,
  `updated_at`      TIMESTAMP        NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: scheme_applications
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `scheme_applications` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `farmer_id`     BIGINT UNSIGNED NOT NULL,
  `scheme_id`     BIGINT UNSIGNED NOT NULL,
  `status`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `remarks`       TEXT            NULL,
  `applied_date`  DATE            NULL,
  `approved_date` DATE            NULL,
  `created_at`    TIMESTAMP       NULL,
  `updated_at`    TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farmer_scheme_unique` (`farmer_id`,`scheme_id`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`scheme_id`) REFERENCES `schemes`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: drones
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drones` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `drone_id`    VARCHAR(50)     NOT NULL UNIQUE,
  `model`       VARCHAR(100)    NULL,
  `description` TEXT            NULL,
  `status`      ENUM('active','idle','offline') NOT NULL DEFAULT 'idle',
  `created_at`  TIMESTAMP       NULL,
  `updated_at`  TIMESTAMP       NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: drone_logs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drone_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `drone_id`   BIGINT UNSIGNED NOT NULL,
  `latitude`   DECIMAL(10,7)   NOT NULL,
  `longitude`  DECIMAL(10,7)   NOT NULL,
  `speed`      DECIMAL(6,2)    NOT NULL DEFAULT 0,
  `altitude`   DECIMAL(8,2)    NOT NULL DEFAULT 0,
  `direction`  DECIMAL(6,2)    NOT NULL DEFAULT 0,
  `extra_data` JSON            NULL,
  `created_at` TIMESTAMP       NULL,
  `updated_at` TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  KEY `drone_logs_drone_id_created_at_index` (`drone_id`, `created_at`),
  FOREIGN KEY (`drone_id`) REFERENCES `drones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: waypoints
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `waypoints` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)    NOT NULL,
  `route_name` VARCHAR(100)    NULL,
  `drone_id`   BIGINT UNSIGNED NULL,
  `latitude`   DECIMAL(10,7)   NOT NULL,
  `longitude`  DECIMAL(10,7)   NOT NULL,
  `sequence`   INT             NOT NULL DEFAULT 1,
  `altitude`   DECIMAL(8,2)    NOT NULL DEFAULT 50,
  `speed`      DECIMAL(6,2)    NOT NULL DEFAULT 30,
  `notes`      TEXT            NULL,
  `is_reached` TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP       NULL,
  `updated_at` TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`drone_id`) REFERENCES `drones`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: vision_analyses
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vision_analyses` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mode`         VARCHAR(100)    NOT NULL,
  `object_count` INT             NOT NULL DEFAULT 0,
  `healthy_pct`  INT             NOT NULL DEFAULT 0,
  `affected_pct` INT             NOT NULL DEFAULT 0,
  `result_json`  JSON            NULL,
  `image_path`   VARCHAR(255)    NULL,
  `created_at`   TIMESTAMP       NULL,
  `updated_at`   TIMESTAMP       NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: migrations (required by Laravel)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: sessions (required by Laravel session driver)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255)    NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45)     NULL,
  `user_agent`    TEXT            NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: cache (required by Laravel cache)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEMO DATA
-- ============================================================

-- Admin user (password: password)
INSERT INTO `users` (`name`,`email`,`password`,`role`,`is_active`,`email_verified`,`organization`,`created_at`,`updated_at`) VALUES
('Admin User',   'admin@agritrek.com',  '$2y$10$Wt8/1wVqUnRa7z3jDm5SZeXB/j/0VYE24zLBbPngNgAHXSiHkWe/y', 'admin',  1, 1, 'Agri-Trek HQ', NOW(), NOW()),
('Dr. Priya Expert','expert@agritrek.com','$2y$10$Wt8/1wVqUnRa7z3jDm5SZeXB/j/0VYE24zLBbPngNgAHXSiHkWe/y','expert', 1, 1, 'State Agriculture Research Institute', NOW(), NOW());

-- Farmers
INSERT INTO `farmers` (`name`,`mobile`,`address`,`village`,`district`,`aadhaar`,`created_at`,`updated_at`) VALUES
('Ramesh Patel',   '9876543210','Anand Nagar, Near Temple','Anand',   'Anand',      '123456789012',NOW(),NOW()),
('Suresh Kumar',   '9876543211','MG Road, Opp School',     'Nadiad',  'Kheda',      '234567890123',NOW(),NOW()),
('Priya Sharma',   '9876543212','Civil Lines',              'Mehsana', 'Mehsana',    '345678901234',NOW(),NOW()),
('Dinesh Yadav',   '9876543213','Station Road',             'Unjha',   'Mehsana',    '456789012345',NOW(),NOW()),
('Lakshmi Devi',   '9876543214','Patel Street',             'Vijapur', 'Mehsana',    '567890123456',NOW(),NOW()),
('Mohan Singh',    '9876543215','Gandhi Chowk',             'Kadi',    'Mehsana',    '678901234567',NOW(),NOW()),
('Anita Verma',    '9876543216','NH-48, Village Road',      'Kalol',   'Gandhinagar','789012345678',NOW(),NOW()),
('Vijay Patil',    '9876543217','Opposite Market',          'Deesa',   'Banaskantha','890123456789',NOW(),NOW()),
('Sunita Joshi',   '9876543218','Near River',               'Patan',   'Patan',      '901234567890',NOW(),NOW()),
('Rajan Mehta',    '9876543219','Old Town',                 'Palanpur','Banaskantha','012345678901',NOW(),NOW());

-- Farmer user account (farmer_id=1, password: password)
INSERT INTO `users` (`name`,`email`,`password`,`role`,`farmer_id`,`is_active`,`email_verified`,`created_at`,`updated_at`) VALUES
('Ramesh Patel','farmer@agritrek.com','$2y$10$Wt8/1wVqUnRa7z3jDm5SZeXB/j/0VYE24zLBbPngNgAHXSiHkWe/y','farmer',1,1,1,NOW(),NOW());

-- Lands
INSERT INTO `lands` (`farmer_id`,`area`,`soil_type`,`crop_type`,`latitude`,`longitude`,`irrigation_type`,`survey_number`,`created_at`,`updated_at`) VALUES
(1, 3.50,'Loamy',      'Wheat',     23.4912, 72.5234,'Canal',     'SY-101',NOW(),NOW()),
(1, 1.75,'Black Cotton','Cotton',   23.5100, 72.4890,'Drip',      'SY-102',NOW(),NOW()),
(2, 5.00,'Sandy',      'Rice',      23.4456, 72.5678,'Borewell',  'SY-201',NOW(),NOW()),
(3, 2.25,'Loamy',      'Sugarcane', 23.5670, 72.4120,'Canal',     'SY-301',NOW(),NOW()),
(4, 4.00,'Silty',      'Maize',     23.5001, 72.5001,'Rainfed',   'SY-401',NOW(),NOW()),
(5, 6.50,'Clay',       'Groundnut', 23.4789, 72.4567,'Sprinkler', 'SY-501',NOW(),NOW()),
(6, 1.50,'Loamy',      'Wheat',     23.5234, 72.5432,'Drip',      'SY-601',NOW(),NOW()),
(7, 3.00,'Sandy',      'Soybean',   23.5500, 72.4800,'Rainfed',   'SY-701',NOW(),NOW()),
(8, 7.00,'Black Cotton','Cotton',   23.4300, 72.5900,'Canal',     'SY-801',NOW(),NOW()),
(9, 2.00,'Loamy',      'Mustard',   23.5123, 72.4456,'Borewell',  'SY-901',NOW(),NOW());

-- Schemes
INSERT INTO `schemes` (`name`,`description`,`eligibility`,`subsidy_amount`,`department`,`start_date`,`end_date`,`is_active`,`created_at`,`updated_at`) VALUES
('PM Kisan Samman Nidhi','Direct income support to farmer families.','Small & marginal farmers owning up to 2 hectares',6000,'Ministry of Agriculture','2024-01-01','2025-12-31',1,NOW(),NOW()),
('Pradhan Mantri Fasal Bima','Crop insurance scheme against natural calamities.','All farmers growing notified crops',15000,'Ministry of Agriculture','2024-04-01','2025-03-31',1,NOW(),NOW()),
('Micro Irrigation Fund','Support for switching to drip/sprinkler irrigation.','Farmers switching to efficient irrigation',8000,'NABARD','2024-01-01','2025-06-30',1,NOW(),NOW()),
('Soil Health Card Scheme','Free soil testing and health card for all farmers.','All farmers',500,'State Agriculture Dept','2024-01-01','2025-12-31',1,NOW(),NOW()),
('Rashtriya Krishi Vikas Yojana','Financial assistance for agriculture development.','Farmers with 5+ acres',25000,'Ministry of Agriculture','2024-01-01','2025-12-31',1,NOW(),NOW()),
('Kisan Credit Card','Short term credit for agricultural needs.','All farmers with land documents',50000,'NABARD & Banks','2024-01-01','2026-12-31',1,NOW(),NOW());

-- Scheme Applications
INSERT INTO `scheme_applications` (`farmer_id`,`scheme_id`,`status`,`applied_date`,`created_at`,`updated_at`) VALUES
(1,1,'approved','2024-02-15',NOW(),NOW()),
(1,3,'pending', '2024-03-10',NOW(),NOW()),
(2,1,'approved','2024-01-20',NOW(),NOW()),
(2,2,'rejected','2024-02-28',NOW(),NOW()),
(3,4,'approved','2024-03-05',NOW(),NOW()),
(4,5,'pending', '2024-04-01',NOW(),NOW()),
(5,6,'approved','2024-01-15',NOW(),NOW()),
(6,1,'pending', '2024-03-20',NOW(),NOW()),
(7,2,'approved','2024-02-10',NOW(),NOW()),
(8,3,'pending', '2024-04-15',NOW(),NOW());

-- Drones
INSERT INTO `drones` (`name`,`drone_id`,`model`,`status`,`description`,`created_at`,`updated_at`) VALUES
('AgriHawk-01','DRONE-001','DJI Agras T30',   'active','Primary crop monitoring drone',NOW(),NOW()),
('SkyEye-02',  'DRONE-002','DJI Phantom 4',   'active','High-resolution imaging drone',NOW(),NOW()),
('FieldBot-03','DRONE-003','Parrot Bluegrass', 'active','Multispectral field analysis drone',NOW(),NOW()),
('CropScan-04','DRONE-004','senseFly eBee',    'idle',  'Fixed-wing survey drone',NOW(),NOW()),
('TerraView-05','DRONE-005','AgEagle RX-60',   'offline','Backup surveillance drone',NOW(),NOW());

-- Drone Logs (trajectory data for clustering)
INSERT INTO `drone_logs` (`drone_id`,`latitude`,`longitude`,`speed`,`altitude`,`direction`,`created_at`,`updated_at`) VALUES
(1,23.5000,72.5000,45,80, 0,  DATE_SUB(NOW(),INTERVAL 150 MINUTE),DATE_SUB(NOW(),INTERVAL 150 MINUTE)),
(1,23.5010,72.5010,48,82, 45, DATE_SUB(NOW(),INTERVAL 145 MINUTE),DATE_SUB(NOW(),INTERVAL 145 MINUTE)),
(1,23.5020,72.5020,50,85, 90, DATE_SUB(NOW(),INTERVAL 140 MINUTE),DATE_SUB(NOW(),INTERVAL 140 MINUTE)),
(1,23.5030,72.5015,47,83, 135,DATE_SUB(NOW(),INTERVAL 135 MINUTE),DATE_SUB(NOW(),INTERVAL 135 MINUTE)),
(1,23.5025,72.5005,44,80, 180,DATE_SUB(NOW(),INTERVAL 130 MINUTE),DATE_SUB(NOW(),INTERVAL 130 MINUTE)),
(1,23.5015,72.4995,46,82, 225,DATE_SUB(NOW(),INTERVAL 125 MINUTE),DATE_SUB(NOW(),INTERVAL 125 MINUTE)),
(1,23.5005,72.5000,49,84, 270,DATE_SUB(NOW(),INTERVAL 120 MINUTE),DATE_SUB(NOW(),INTERVAL 120 MINUTE)),
(2,23.5100,72.5100,55,90, 0,  DATE_SUB(NOW(),INTERVAL 100 MINUTE),DATE_SUB(NOW(),INTERVAL 100 MINUTE)),
(2,23.5110,72.5110,57,92, 45, DATE_SUB(NOW(),INTERVAL 95 MINUTE), DATE_SUB(NOW(),INTERVAL 95 MINUTE)),
(2,23.5120,72.5120,60,95, 90, DATE_SUB(NOW(),INTERVAL 90 MINUTE), DATE_SUB(NOW(),INTERVAL 90 MINUTE)),
(2,23.5130,72.5115,58,93, 135,DATE_SUB(NOW(),INTERVAL 85 MINUTE), DATE_SUB(NOW(),INTERVAL 85 MINUTE)),
(2,23.5125,72.5105,55,90, 180,DATE_SUB(NOW(),INTERVAL 80 MINUTE), DATE_SUB(NOW(),INTERVAL 80 MINUTE)),
(2,23.5115,72.5095,52,88, 225,DATE_SUB(NOW(),INTERVAL 75 MINUTE), DATE_SUB(NOW(),INTERVAL 75 MINUTE)),
(3,23.4900,72.4900,35,60, 0,  DATE_SUB(NOW(),INTERVAL 60 MINUTE), DATE_SUB(NOW(),INTERVAL 60 MINUTE)),
(3,23.4910,72.4910,37,62, 45, DATE_SUB(NOW(),INTERVAL 55 MINUTE), DATE_SUB(NOW(),INTERVAL 55 MINUTE)),
(3,23.4920,72.4920,40,65, 90, DATE_SUB(NOW(),INTERVAL 50 MINUTE), DATE_SUB(NOW(),INTERVAL 50 MINUTE)),
(3,23.4930,72.4915,38,63, 135,DATE_SUB(NOW(),INTERVAL 45 MINUTE), DATE_SUB(NOW(),INTERVAL 45 MINUTE)),
(3,23.4925,72.4905,36,61, 180,DATE_SUB(NOW(),INTERVAL 40 MINUTE), DATE_SUB(NOW(),INTERVAL 40 MINUTE)),
(3,23.4915,72.4895,35,60, 225,DATE_SUB(NOW(),INTERVAL 35 MINUTE), DATE_SUB(NOW(),INTERVAL 35 MINUTE)),
(1,23.5008,72.5008,50,85, 0,  DATE_SUB(NOW(),INTERVAL 30 MINUTE), DATE_SUB(NOW(),INTERVAL 30 MINUTE)),
(2,23.5118,72.5118,58,92, 45, DATE_SUB(NOW(),INTERVAL 20 MINUTE), DATE_SUB(NOW(),INTERVAL 20 MINUTE)),
(3,23.4918,72.4918,38,63, 90, DATE_SUB(NOW(),INTERVAL 10 MINUTE), DATE_SUB(NOW(),INTERVAL 10 MINUTE)),
(1,23.5012,72.5012,47,83, 0,  NOW(),NOW()),
(2,23.5122,72.5122,55,90, 45, NOW(),NOW()),
(3,23.4922,72.4922,36,61, 90, NOW(),NOW());

-- Waypoints
INSERT INTO `waypoints` (`name`,`route_name`,`drone_id`,`latitude`,`longitude`,`sequence`,`altitude`,`speed`,`is_reached`,`created_at`,`updated_at`) VALUES
('North Field WP-1','North Field Survey',1,23.5100,72.4800,1,60,35,1,NOW(),NOW()),
('North Field WP-2','North Field Survey',1,23.5200,72.4900,2,65,40,1,NOW(),NOW()),
('North Field WP-3','North Field Survey',1,23.5300,72.5000,3,70,40,0,NOW(),NOW()),
('North Field WP-4','North Field Survey',1,23.5300,72.5200,4,65,35,0,NOW(),NOW()),
('North Field WP-5','North Field Survey',1,23.5200,72.5300,5,60,30,0,NOW(),NOW()),
('North Field WP-6','North Field Survey',1,23.5100,72.5200,6,55,30,0,NOW(),NOW()),
('South Field WP-1','South Field Patrol',2,23.4800,72.4800,1,50,30,1,NOW(),NOW()),
('South Field WP-2','South Field Patrol',2,23.4700,72.5000,2,55,35,1,NOW(),NOW()),
('South Field WP-3','South Field Patrol',2,23.4600,72.5200,3,60,35,0,NOW(),NOW()),
('South Field WP-4','South Field Patrol',2,23.4700,72.5400,4,55,30,0,NOW(),NOW()),
('South Field WP-5','South Field Patrol',2,23.4800,72.5500,5,50,25,0,NOW(),NOW()),
('Perimeter WP-1',  'Perimeter Check',   3,23.5000,72.4500,1,80,45,1,NOW(),NOW()),
('Perimeter WP-2',  'Perimeter Check',   3,23.5500,72.4500,2,80,45,0,NOW(),NOW()),
('Perimeter WP-3',  'Perimeter Check',   3,23.5500,72.5500,3,80,45,0,NOW(),NOW()),
('Perimeter WP-4',  'Perimeter Check',   3,23.5000,72.5500,4,80,45,0,NOW(),NOW());


-- -------------------------------------------------------
-- Add cross-table foreign keys (after all tables created)
-- -------------------------------------------------------
ALTER TABLE `users`
  ADD CONSTRAINT `users_farmer_id_foreign`
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL;

-- Mark migrations as run
INSERT INTO `migrations` (`migration`,`batch`) VALUES
('2024_01_01_000001_create_users_table',1),
('2024_01_01_000002_create_farmers_table',1),
('2024_01_01_000003_create_lands_table',1),
('2024_01_01_000004_create_schemes_tables',1),
('2024_01_01_000005_create_drones_tables',1),
('2024_01_01_000006_create_vision_analyses_table',1);
