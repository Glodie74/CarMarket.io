-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 21, 2025 at 05:11 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crudoperation`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_products`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `active_products`;
CREATE TABLE IF NOT EXISTS `active_products` (
`body_type` enum('Sedan','SUV','Hatchback','Coupe','Convertible','Truck','Van','Wagon')
,`brand` varchar(50)
,`category_id` int
,`condition_type` enum('New','Used','Certified Pre-Owned')
,`created_at` timestamp
,`description` text
,`doors` int
,`engine_size` varchar(20)
,`exterior_color` varchar(30)
,`favorite_count` bigint
,`favorites_count` int
,`featured` tinyint(1)
,`features` text
,`fuel_type` enum('Gasoline','Diesel','Hybrid','Electric','LPG')
,`id` int
,`interior_color` varchar(30)
,`license_plate` varchar(20)
,`location_city` varchar(50)
,`location_state` varchar(50)
,`location_zip` varchar(10)
,`mileage` int
,`model` varchar(50)
,`price` decimal(12,2)
,`primary_image` varchar(255)
,`seats` int
,`seller_email` varchar(100)
,`seller_name` varchar(50)
,`seller_phone` varchar(20)
,`status` enum('active','sold','pending','inactive')
,`title` varchar(200)
,`transmission` enum('Manual','Automatic','CVT')
,`updated_at` timestamp
,`user_id` int
,`views` int
,`vin` varchar(50)
,`year` int
);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sedan', 'sedan', 'Four-door passenger cars', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(2, 'SUV', 'suv', 'Sport Utility Vehicles', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(3, 'Hatchback', 'hatchback', 'Compact cars with rear door', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(4, 'Coupe', 'coupe', 'Two-door sports cars', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(5, 'Truck', 'truck', 'Pickup trucks and commercial vehicles', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(6, 'Convertible', 'convertible', 'Cars with retractable roofs', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(7, 'Van', 'van', 'Multi-purpose vehicles', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(8, 'Wagon', 'wagon', 'Station wagons and estate cars', NULL, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorite` (`user_id`,`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `target` enum('_self','_blank') DEFAULT '_self',
  `permissions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `url`, `icon`, `parent_id`, `sort_order`, `status`, `target`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'Home', '/', 'fas fa-home', NULL, 1, 'active', '_self', '[\"admin\", \"seller\", \"buyer\"]', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(2, 'Browse Cars', '/products.php', 'fas fa-car', NULL, 2, 'active', '_self', '[\"admin\", \"seller\", \"buyer\"]', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(3, 'Sell Your Car', '/sell.php', 'fas fa-plus-circle', NULL, 3, 'active', '_self', '[\"admin\", \"seller\"]', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(4, 'About', '/about.php', 'fas fa-info-circle', NULL, 4, 'active', '_self', '[\"admin\", \"seller\", \"buyer\"]', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(5, 'Contact', '/contact.php', 'fas fa-envelope', NULL, 5, 'active', '_self', '[\"admin\", \"seller\", \"buyer\"]', '2025-06-17 12:27:09', '2025-06-17 12:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sender_id` (`sender_id`),
  KEY `idx_receiver_id` (`receiver_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `total_amount` decimal(10,2) NOT NULL,
  `STATUS` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`STATUS`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `mileage` int DEFAULT NULL,
  `fuel_type` enum('Gasoline','Diesel','Hybrid','Electric','LPG') DEFAULT 'Gasoline',
  `transmission` enum('Manual','Automatic','CVT') DEFAULT 'Manual',
  `engine_size` varchar(20) DEFAULT NULL,
  `body_type` enum('Sedan','SUV','Hatchback','Coupe','Convertible','Truck','Van','Wagon') DEFAULT 'Sedan',
  `exterior_color` varchar(30) DEFAULT NULL,
  `interior_color` varchar(30) DEFAULT NULL,
  `doors` int DEFAULT '4',
  `seats` int DEFAULT '5',
  `price` decimal(12,2) NOT NULL,
  `condition_type` enum('New','Used','Certified Pre-Owned') DEFAULT 'Used',
  `vin` varchar(50) DEFAULT NULL,
  `license_plate` varchar(20) DEFAULT NULL,
  `features` text,
  `location_city` varchar(50) DEFAULT NULL,
  `location_state` varchar(50) DEFAULT NULL,
  `location_zip` varchar(10) DEFAULT NULL,
  `views` int DEFAULT '0',
  `favorites_count` int DEFAULT '0',
  `status` enum('active','sold','pending','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_price` (`price`),
  KEY `idx_brand_model` (`brand`,`model`),
  KEY `idx_year` (`year`),
  KEY `idx_location` (`location_city`,`location_state`),
  KEY `idx_products_search` (`brand`,`model`,`year`,`price`,`status`),
  KEY `idx_products_location` (`location_city`,`location_state`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `description`, `brand`, `model`, `year`, `mileage`, `fuel_type`, `transmission`, `engine_size`, `body_type`, `exterior_color`, `interior_color`, `doors`, `seats`, `price`, `condition_type`, `vin`, `license_plate`, `features`, `location_city`, `location_state`, `location_zip`, `views`, `favorites_count`, `status`, `featured`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2020 Toyota Camry LE', 'Well-maintained sedan with low mileage. Perfect for daily commuting.', 'Toyota', 'Camry', 2020, 35000, 'Gasoline', 'Automatic', NULL, 'Sedan', 'Silver', NULL, 4, 5, 24999.00, 'Used', NULL, NULL, NULL, 'Los Angeles', 'CA', NULL, 0, 0, 'active', 0, '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(2, 1, 2, '2019 Honda CR-V EX', 'Reliable SUV with excellent fuel economy. Great for families.', 'Honda', 'CR-V', 2019, 42000, 'Gasoline', 'CVT', NULL, 'SUV', 'White', NULL, 4, 5, 27500.00, 'Used', NULL, NULL, NULL, 'Miami', 'FL', NULL, 0, 0, 'active', 0, '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(3, 1, 3, '2021 BMW 3 Series', 'Luxury sedan with premium features and sporty performance.', 'BMW', '3 Series', 2021, 15000, 'Gasoline', 'Automatic', NULL, 'Sedan', 'Black', NULL, 4, 5, 38999.00, 'Used', NULL, NULL, NULL, 'New York', 'NY', NULL, 0, 0, 'active', 0, '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(4, 2, NULL, 'Honda Jazz', 'beautiful car', 'Honda', NULL, 2015, NULL, 'Gasoline', 'Manual', NULL, 'Sedan', NULL, NULL, 4, 5, 13000.00, 'Used', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', 0, '2025-06-21 16:20:51', '2025-06-21 16:20:51'),
(5, 2, NULL, 'merc', 'beautifile white car', 'Mercedes-Benz', NULL, 2022, NULL, 'Gasoline', 'Manual', NULL, 'Sedan', NULL, NULL, 4, 5, 150000.00, 'Used', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', 0, '2025-06-21 16:54:49', '2025-06-21 16:54:49');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

DROP TABLE IF EXISTS `product_views`;
CREATE TABLE IF NOT EXISTS `product_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_views`
--

INSERT INTO `product_views` (`id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 2, 2, '2025-06-20 08:38:33'),
(2, 2, 1, '2025-06-20 08:39:06'),
(3, 3, 1, '2025-06-20 11:36:02'),
(4, 3, 2, '2025-06-20 11:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reviewer_id` int NOT NULL,
  `reviewed_user_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `review_text` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review` (`reviewer_id`,`reviewed_user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_reviewed_user_id` (`reviewed_user_id`),
  KEY `idx_rating` (`rating`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `search_term` varchar(255) DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `results_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_search_term` (`search_term`(250))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Eden\'s CarShop', 'Website name', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(2, 'site_description', 'Find Your Perfect Car', 'Website description', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(3, 'contact_email', 'contact@edensshop.com', 'Contact email address', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(4, 'contact_phone', '+1 (555) 123-4567', 'Contact phone number', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(5, 'max_images_per_product', '10', 'Maximum images allowed per product', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(6, 'featured_products_limit', '8', 'Number of featured products to display', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(7, 'allow_user_registration', '1', 'Allow new user registration', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(8, 'require_email_verification', '0', 'Require email verification for new accounts', '2025-06-17 12:27:09', '2025-06-17 12:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','buyer') DEFAULT 'buyer',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `bio` text,
  `country` varchar(50) DEFAULT 'United States',
  `profile_image` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_role` (`role`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `address`, `city`, `state`, `zip_code`, `bio`, `country`, `profile_image`, `email_verified`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', NULL, 'admin@edensshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, NULL, 'United States', NULL, 0, 'active', '2025-06-17 12:27:09', '2025-06-17 12:27:09'),
(2, 'glodi1', NULL, 'Gbnkongolo@gmail.com', '$2y$10$4JIMruY7eO9v1YqoIS9Nmu.Wx1pG3pW.szmsEhDaiulipWbXsdXHa', 'seller', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'United States', NULL, 0, 'active', '2025-06-19 13:03:18', '2025-06-19 13:03:18'),
(3, 'Elmayo', 'Elisee Mayombo', 'qdqv9wm74@vossie.net', '$2y$10$JNX/W8lyoaYiZUF7a8hWxeoVqXWmdNoeGns9tmBLDHEJedjiMWgI2', 'buyer', NULL, NULL, '+27680973892', '25', 'Bruma (2026)', 'GP', '2026', 'iam 6 feet tall', 'United States', 'profile_3_1750406163.jpg', 0, 'active', '2025-06-20 06:22:30', '2025-06-20 07:57:03');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_stats`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_stats`;
CREATE TABLE IF NOT EXISTS `user_stats` (
`active_products` bigint
,`avg_rating` decimal(14,4)
,`id` int
,`review_count` bigint
,`role` enum('admin','seller','buyer')
,`total_favorites` bigint
,`username` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure for view `active_products`
--
DROP TABLE IF EXISTS `active_products`;

DROP VIEW IF EXISTS `active_products`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_products`  AS SELECT `p`.`id` AS `id`, `p`.`user_id` AS `user_id`, `p`.`category_id` AS `category_id`, `p`.`title` AS `title`, `p`.`description` AS `description`, `p`.`brand` AS `brand`, `p`.`model` AS `model`, `p`.`year` AS `year`, `p`.`mileage` AS `mileage`, `p`.`fuel_type` AS `fuel_type`, `p`.`transmission` AS `transmission`, `p`.`engine_size` AS `engine_size`, `p`.`body_type` AS `body_type`, `p`.`exterior_color` AS `exterior_color`, `p`.`interior_color` AS `interior_color`, `p`.`doors` AS `doors`, `p`.`seats` AS `seats`, `p`.`price` AS `price`, `p`.`condition_type` AS `condition_type`, `p`.`vin` AS `vin`, `p`.`license_plate` AS `license_plate`, `p`.`features` AS `features`, `p`.`location_city` AS `location_city`, `p`.`location_state` AS `location_state`, `p`.`location_zip` AS `location_zip`, `p`.`views` AS `views`, `p`.`favorites_count` AS `favorites_count`, `p`.`status` AS `status`, `p`.`featured` AS `featured`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `u`.`username` AS `seller_name`, `u`.`phone` AS `seller_phone`, `u`.`email` AS `seller_email`, (select count(0) from `favorites` `f` where (`f`.`product_id` = `p`.`id`)) AS `favorite_count`, (select `pi`.`image_url` from `product_images` `pi` where ((`pi`.`product_id` = `p`.`id`) and (`pi`.`is_primary` = 1)) limit 1) AS `primary_image` FROM (`products` `p` join `users` `u` on((`p`.`user_id` = `u`.`id`))) WHERE ((`p`.`status` = 'active') AND (`u`.`status` = 'active')) ;

-- --------------------------------------------------------

--
-- Structure for view `user_stats`
--
DROP TABLE IF EXISTS `user_stats`;

DROP VIEW IF EXISTS `user_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_stats`  AS SELECT `u`.`id` AS `id`, `u`.`username` AS `username`, `u`.`role` AS `role`, (select count(0) from `products` `p` where ((`p`.`user_id` = `u`.`id`) and (`p`.`status` = 'active'))) AS `active_products`, (select count(0) from (`favorites` `f` join `products` `p` on((`f`.`product_id` = `p`.`id`))) where (`p`.`user_id` = `u`.`id`)) AS `total_favorites`, (select avg(`r`.`rating`) from `reviews` `r` where ((`r`.`reviewed_user_id` = `u`.`id`) and (`r`.`status` = 'approved'))) AS `avg_rating`, (select count(0) from `reviews` `r` where ((`r`.`reviewed_user_id` = `u`.`id`) and (`r`.`status` = 'approved'))) AS `review_count` FROM `users` AS `u` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
