-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 04:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `j3rs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('login','logout','create','update','delete','view') NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 19, '2026-04-25 08:44:06', '2026-04-25 08:44:06'),
(2, 18, '2026-04-28 21:57:53', '2026-04-28 21:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_item_id`, `cart_id`, `variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
(4, 1, 12, 1, '2026-04-28 20:19:12', '2026-04-28 20:19:12'),
(5, 1, 18, 1, '2026-04-28 21:11:06', '2026-04-28 21:11:06'),
(6, 2, 18, 1, '2026-04-28 21:57:53', '2026-04-28 21:57:53'),
(7, 2, 20, 2, '2026-04-28 21:58:54', '2026-04-28 22:01:21'),
(8, 2, 15, 3, '2026-04-28 21:58:57', '2026-04-28 22:08:16'),
(9, 1, 20, 1, '2026-04-28 22:07:39', '2026-04-28 22:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `conversation_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_number` varchar(12) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`user_id`, `customer_id`, `full_name`, `contact_number`, `address_line`, `city`, `region`, `postal_code`) VALUES
(16, 5, 'Jojana Baglan Garabillo', '0365632', 'bl1 lot 11', 'taytay', '4', '1920'),
(18, 6, 'Jojana Jean Baglan Garabillo', '0202', '', '', '', ''),
(17, 7, 'Leonor Rivera', '0101', '', '', '', ''),
(19, 8, '', '', '', '', '', ''),
(20, 9, 'Leonor Rivera', '2345678', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `delivery_tracking_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending_pickup','picked_up','in_transit','arrived_hub','out_for_delivery','delivered','delivery_failed','returned') NOT NULL DEFAULT 'pending_pickup',
  `status_note` varchar(255) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `courier_name` varchar(120) DEFAULT NULL,
  `logistic_user_id` int(11) DEFAULT NULL,
  `tracking_number` varchar(120) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_tracking`
--

INSERT INTO `delivery_tracking` (`delivery_tracking_id`, `order_id`, `status`, `status_note`, `location`, `courier_name`, `logistic_user_id`, `tracking_number`, `updated_by_user_id`, `created_at`) VALUES
(2, 9, 'picked_up', NULL, NULL, NULL, 23, '', 23, '2026-04-28 22:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_restock`
--

CREATE TABLE `inventory_restock` (
  `restock_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `restock_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_restock`
--

INSERT INTO `inventory_restock` (`restock_id`, `variant_id`, `seller_id`, `quantity`, `notes`, `restock_date`) VALUES
(1, 3, 1, 50, '', '2026-04-22 21:13:27'),
(2, 3, 1, 30, '', '2026-04-22 21:13:51'),
(3, 5, 7, 24, 'added', '2026-04-25 08:33:26'),
(4, 14, 6, 2, '', '2026-04-28 20:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `message_body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(80) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `reference_id`, `is_read`, `created_at`, `read_at`) VALUES
(3, 18, 'Seller Application Not Approved', 'We regret to inform you that your seller application was not approved. Please check your email for more details.', NULL, NULL, 0, '2026-04-23 19:06:40', NULL),
(4, 18, 'Seller Application Approved - Account Upgraded to Dual Role', 'Congratulations! Your account has been upgraded to Dual Role. You can now switch between Customer and Seller modes using the \'Switch Role\' button.', NULL, NULL, 0, '2026-04-23 19:09:54', NULL),
(5, 20, 'Seller Application Approved', 'Congratulations! Your seller account has been approved. You can now start selling on J3RS Marketplace.', NULL, NULL, 0, '2026-04-25 08:23:08', NULL),
(6, 19, 'Order Status Update - Order #ORD-69F0BF213BC61', 'Great news! Your order #ORD-69F0BF213BC61 has been assigned to our logistics partner (JJRS) and is now being processed for delivery. You can track your order status in your account.', '0', 9, 0, '2026-04-28 22:09:08', NULL),
(7, 19, 'Order Status Update - Order #ORD-69F0BF213BC61', 'Your order #ORD-69F0BF213BC61 has been picked up and is now in transit to your location.', 'order_update', 9, 0, '2026-04-28 22:32:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_status` enum('pending','paid','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded','failed') NOT NULL DEFAULT 'unpaid',
  `subtotal_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_full_name` varchar(150) DEFAULT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `shipping_address_line` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(120) DEFAULT NULL,
  `shipping_region` varchar(120) DEFAULT NULL,
  `shipping_postal_code` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `customer_id`, `order_status`, `payment_status`, `subtotal_amount`, `shipping_amount`, `discount_amount`, `total_amount`, `shipping_full_name`, `shipping_phone`, `shipping_address_line`, `shipping_city`, `shipping_region`, `shipping_postal_code`, `created_at`, `updated_at`) VALUES
(9, 'ORD-69F0BF213BC61', 19, 'shipped', 'unpaid', 12.00, 0.00, 0.00, 12.00, '', '', '', '', '', '', '2026-04-28 22:07:29', '2026-04-28 22:32:36'),
(10, 'ORD-69F0BF35B770B', 19, 'pending', 'unpaid', 13.00, 0.00, 0.00, 13.00, '', '', '', '', '', '', '2026-04-28 22:07:49', '2026-04-28 22:07:49'),
(11, 'ORD-69F0BF570C6A5', 18, 'pending', 'unpaid', 19.00, 0.00, 0.00, 19.00, 'Jojana Jean Baglan Garabillo', '0202', '', '', '', '', '2026-04-28 22:08:23', '2026-04-28 22:08:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `product_id`, `variant_id`, `seller_id`, `quantity`, `unit_price`, `line_total`, `created_at`) VALUES
(1, 9, 9, 12, 6, 1, 10.00, 10.00, '2026-04-28 22:07:29'),
(2, 9, 11, 18, 6, 1, 2.00, 2.00, '2026-04-28 22:07:29'),
(3, 10, 9, 12, 6, 1, 10.00, 10.00, '2026-04-28 22:07:49'),
(4, 10, 11, 18, 6, 1, 2.00, 2.00, '2026-04-28 22:07:49'),
(5, 10, 12, 20, 7, 1, 1.00, 1.00, '2026-04-28 22:07:49'),
(6, 11, 11, 18, 6, 1, 2.00, 2.00, '2026-04-28 22:08:23'),
(7, 11, 12, 20, 7, 2, 1.00, 2.00, '2026-04-28 22:08:23'),
(8, 11, 10, 15, 7, 3, 5.00, 15.00, '2026-04-28 22:08:23');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('PayPal','Stripe','GCash','Maya') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_reference` varchar(150) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `category_gender` enum('Men','Women') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `status` enum('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `seller_id`, `name`, `description`, `category_gender`, `price`, `qty`, `status`, `created_at`, `updated_at`) VALUES
(3, 1, 'Tshirt', 'asdwa', 'Men', 10.50, 40, 'active', '2026-04-22 21:12:38', '2026-04-22 21:12:38'),
(5, 7, 'Baggy Pants', 'Baggy pants for men made with cotton', 'Men', 300.00, 25, 'active', '2026-04-25 08:33:05', '2026-04-25 08:33:05'),
(9, 6, 'Women Short', 'dfghjkl', 'Women', 0.00, 0, 'active', '2026-04-28 20:10:16', '2026-04-28 20:10:16'),
(10, 7, 'Pants for Men', 'GHPWSMAKND', 'Men', 0.00, 0, 'active', '2026-04-28 20:26:31', '2026-04-28 20:26:31'),
(11, 6, 'Skirt', 'Skirts for women', 'Women', 0.00, 0, 'active', '2026-04-28 20:59:19', '2026-04-28 20:59:19'),
(12, 7, 'Long Pants', 'long pants po', 'Men', 0.00, 0, 'active', '2026-04-28 21:03:40', '2026-04-28 21:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE `product_variant` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`variant_id`, `product_id`, `sku`, `size`, `color`, `price`, `stock_qty`, `created_at`, `updated_at`, `image_path`) VALUES
(3, 3, 'PROD-3-DBDC', 'Standard', 'Default', 10.50, 120, '2026-04-22 21:12:38', '2026-04-22 21:13:51', NULL),
(5, 5, 'PROD-5-F6D5', 'Standard', 'Default', 300.00, 49, '2026-04-25 08:33:05', '2026-04-25 08:33:26', NULL),
(12, 9, 'SKU-9-83A663-0', 'S', 'White', 10.00, 10, '2026-04-28 20:10:16', '2026-04-28 20:10:16', 'uploads/products/product_9_variant_0_1777378216.jpg'),
(13, 9, 'SKU-9-83B200-1', 'M', 'Khaki', 10.01, 10, '2026-04-28 20:10:16', '2026-04-28 20:10:16', 'uploads/products/product_9_variant_1_1777378216.jpg'),
(14, 9, 'SKU-9-543F2A92', 'L', 'Black', 10.00, 12, '2026-04-28 20:17:07', '2026-04-28 20:40:45', 'uploads/products/product_9_variant_1777378627.jpg'),
(15, 10, 'SKU-10-78B3AD-0', 'S', 'WHITE', 5.00, 10, '2026-04-28 20:26:31', '2026-04-28 20:26:31', 'uploads/products/product_10_variant_0_1777379191.jpg'),
(16, 10, 'SKU-10-79081AAB', 'M', 'WHITE', 5.00, 5, '2026-04-28 20:26:56', '2026-04-28 20:26:56', 'uploads/products/product_10_variant_1777379216.jpg'),
(17, 10, 'SKU-10-EDE1CF3C', 'M', 'WHITE', 5.00, 5, '2026-04-28 20:58:06', '2026-04-28 20:58:06', 'uploads/products/product_10_variant_1777381086.jpg'),
(18, 11, 'SKU-11-72269A-0', 'S', 'Yellow', 2.00, 15, '2026-04-28 20:59:19', '2026-04-28 20:59:19', 'uploads/products/product_11_variant_0_1777381159.jpg'),
(19, 11, 'SKU-11-F430D5A6', 'M', 'Yellow', 2.00, 10, '2026-04-28 20:59:47', '2026-04-28 21:00:13', 'uploads/products/product_11_variant_1777381187.jpg'),
(20, 12, 'SKU-12-CA02F4-0', 'S', 'White', 1.00, 20, '2026-04-28 21:03:40', '2026-04-28 21:03:40', 'uploads/products/product_12_variant_0_1777381420.jpg'),
(21, 12, 'SKU-12-CA0966-1', 'M', 'White', 1.00, 10, '2026-04-28 21:03:40', '2026-04-28 21:03:40', 'uploads/products/product_12_variant_1_1777381420.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `review_status` enum('active','hidden','flagged') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Customer'),
(4, 'Dual'),
(5, 'Logistic'),
(3, 'Seller');

-- --------------------------------------------------------

--
-- Table structure for table `security_settings`
--

CREATE TABLE `security_settings` (
  `setting_id` int(11) NOT NULL,
  `captcha_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `mfa_required` tinyint(1) NOT NULL DEFAULT 1,
  `email_verification_required` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `seller_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `shop_name` varchar(150) NOT NULL,
  `shop_description` text DEFAULT NULL,
  `shop_address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `business_permit` longblob DEFAULT NULL,
  `valid_id` longblob DEFAULT NULL,
  `shop_image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`seller_id`, `user_id`, `full_name`, `shop_name`, `shop_description`, `shop_address`, `contact_number`, `is_approved`, `created_at`, `updated_at`, `business_permit`, `valid_id`, `shop_image`) VALUES
(1, 7, '', 'sapme seller', 'seller sells products yes', '123 Pasig City', '1234355678', 1, '2026-04-22 19:58:21', '2026-04-22 19:58:21', NULL, NULL, NULL),
(6, 18, 'Leonor Rivera', 'BANANA SHOP', '{\"full_name\":\"Leonor Rivera\",\"email\":\"garabillo_jojanajean@plpasig.edu.ph\",\"age\":33,\"tin_id\":\"0999999\",\"business_category\":\"Men\",\"business_permit_picture\":\"uploads\\/seller_docs\\/business_permit_picture_1776942573_a30692aa.png\",\"valid_id_picture\":\"uploads\\/seller_docs\\/valid_id_picture_1776942573_5c2e4eb9.png\",\"shop_image\":\"uploads\\/seller_docs\\/shop_image_1776942573_0d39ea20.png\",\"registration_date\":\"2026-04-23 13:09:33\",\"application_type\":\"customer_upgrade\"}', NULL, '0202', 1, '2026-04-23 19:09:33', '2026-04-23 19:09:50', NULL, NULL, NULL),
(7, 20, 'Leonor Rivera', 'STRAWBERRY SHOP', '{\"full_name\":\"Leonor Rivera\",\"email\":\"ruberducky032518@gmail.com\",\"age\":33,\"tin_id\":\"09876543\",\"business_category\":\"Women\",\"business_permit_picture\":\"uploads\\/seller_docs\\/business_permit_picture_1777076521_9af12874.jpg\",\"valid_id_picture\":\"uploads\\/seller_docs\\/valid_id_picture_1777076521_c352e6be.jpg\",\"shop_image\":\"uploads\\/seller_docs\\/shop_image_1777076521_526141fb.png\",\"registration_date\":\"2026-04-25 02:22:01\"}', 'Pasig Citty', '2345678', 1, '2026-04-25 08:22:01', '2026-04-25 08:22:46', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `max_login_attempts` int(11) NOT NULL DEFAULT 3,
  `password_min_length` int(11) NOT NULL DEFAULT 12,
  `require_uppercase` tinyint(1) NOT NULL DEFAULT 1,
  `require_number` tinyint(1) NOT NULL DEFAULT 1,
  `require_special_char` tinyint(1) NOT NULL DEFAULT 1,
  `session_timeout_minutes` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(100) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `verification_token` varchar(100) NOT NULL,
  `token_expiry` datetime NOT NULL,
  `is_activated` int(11) NOT NULL DEFAULT 0,
  `mfa_secret` varchar(100) NOT NULL,
  `last_failed_login` datetime DEFAULT current_timestamp(),
  `attempts` int(11) NOT NULL DEFAULT 0,
  `is_locked` int(11) NOT NULL DEFAULT 0,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `first_name`, `last_name`, `email`, `password`, `otp_code`, `otp_expiry`, `verification_token`, `token_expiry`, `is_activated`, `mfa_secret`, `last_failed_login`, `attempts`, `is_locked`, `role_id`, `is_active`) VALUES
(1, 'admin', NULL, NULL, 'antonio_rhoannenicole@plpasig.edu.ph', '$2y$10$LdRoPWBEOsVfBfmTI3GO5.1JMD/eYXapDUljcxJtQN4G6ji0VtFfG', '', '2026-04-22 14:54:03', '', '2026-04-22 14:54:03', 1, 'NPHOGQXCEBULYTDN', '2026-04-22 20:55:17', 0, 0, 1, 1),
(6, 'test', NULL, NULL, 'n0305933@gmail.com', '$2y$10$.Y40Fpy2G.188piUPEVFzO.pd6QJD9rukbiTbHaAfBKyYYq0hXG6m', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '3DBWVGAURVBVXHJM', '2026-04-19 22:07:08', 0, 0, 2, 1),
(7, 'seller', NULL, NULL, 'testsubjectschool155@gmail.com', '$2y$10$pGCiO7zY1RaL.xixVgU51edLMxbuomIIVdvUbfnLIEoPHDwF4mq5y', '', '2026-04-20 17:11:55', '', '2026-04-20 17:11:55', 1, 'QVNQBCXKNKHOKB4A', '2026-04-20 23:12:40', 0, 0, 3, 1),
(11, 'customer', NULL, NULL, 'n42710140@gmail.com', '$2y$10$AzlQdqE3kZLviGUOCOlgiu6aB..qABPjcXFzEOgn3o2Z5ASem0Rqe', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '', '2026-04-23 15:59:21', 0, 0, 2, 1),
(18, 'jean', NULL, NULL, 'garabillo_jojanajean@plpasig.edu.ph', '$2y$10$HZ1Tcng4oooB7QDMAS/uN.3B2msIJqrJwgIPJS6BeHXDwV8U04iA2', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'UEEQ52YZDW3QGUMU', NULL, 0, 0, 4, 1),
(19, 'pamcustomer', NULL, NULL, 'pam066198@gmail.com', '$2y$10$HZ1Tcng4oooB7QDMAS/uN.3B2msIJqrJwgIPJS6BeHXDwV8U04iA2', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '4AP3DFAQT5SNFFQO', '2026-04-23 18:57:23', 0, 0, 2, 1),
(20, 'leonorrivera', NULL, NULL, 'ruberducky032518@gmail.com', '$2y$10$HZ1Tcng4oooB7QDMAS/uN.3B2msIJqrJwgIPJS6BeHXDwV8U04iA2', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '4FUCJJRBO4TIAUS7', '2026-04-25 08:22:01', 0, 0, 3, 1),
(23, 'JJRS', NULL, NULL, 'logistics@example.com', '$2y$10$SvIFRXNS0yCLCRMQr4Ej..1sPudPdQG9pzM5ruJuTDZVhweoChYkK', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'WL2ZQOXBWN4QX74V', NULL, 0, 0, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_item`
--

CREATE TABLE `wishlist_item` (
  `wishlist_item_id` int(11) NOT NULL,
  `wishlist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_module` (`module`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `uk_cart_user` (`user_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uk_cart_variant` (`cart_id`,`variant_id`),
  ADD KEY `idx_cart_item_variant` (`variant_id`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`conversation_id`),
  ADD UNIQUE KEY `uk_conversation_customer_seller` (`customer_id`,`seller_id`),
  ADD KEY `idx_conversation_seller` (`seller_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`delivery_tracking_id`),
  ADD KEY `idx_delivery_order` (`order_id`),
  ADD KEY `idx_delivery_updated_by` (`updated_by_user_id`),
  ADD KEY `idx_logistic_user` (`logistic_user_id`);

--
-- Indexes for table `inventory_restock`
--
ALTER TABLE `inventory_restock`
  ADD PRIMARY KEY (`restock_id`),
  ADD KEY `idx_restock_variant` (`variant_id`),
  ADD KEY `idx_restock_seller` (`seller_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `idx_login_history_user` (`user_id`),
  ADD KEY `idx_login_history_status` (`status`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_message_conversation` (`conversation_id`),
  ADD KEY `idx_message_sender` (`sender_user_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notification_user` (`user_id`),
  ADD KEY `idx_notification_is_read` (`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `uk_order_number` (`order_number`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_status` (`order_status`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order_item_order` (`order_id`),
  ADD KEY `idx_order_item_product` (`product_id`),
  ADD KEY `idx_order_item_variant` (`variant_id`),
  ADD KEY `idx_order_item_seller` (`seller_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_order` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_product_seller` (`seller_id`),
  ADD KEY `idx_product_category` (`category_gender`),
  ADD KEY `idx_product_status` (`status`);

--
-- Indexes for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `uk_variant_sku` (`sku`),
  ADD KEY `idx_variant_product` (`product_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_review_product` (`product_id`),
  ADD KEY `idx_review_customer` (`customer_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `uk_role_name` (`role_name`);

--
-- Indexes for table `security_settings`
--
ALTER TABLE `security_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`seller_id`),
  ADD UNIQUE KEY `uk_seller_user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uk_user_username` (`username`),
  ADD UNIQUE KEY `uk_user_email` (`email`),
  ADD KEY `idx_user_role` (`role_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `uk_wishlist_user` (`user_id`);

--
-- Indexes for table `wishlist_item`
--
ALTER TABLE `wishlist_item`
  ADD PRIMARY KEY (`wishlist_item_id`),
  ADD UNIQUE KEY `uk_wishlist_product` (`wishlist_id`,`product_id`),
  ADD KEY `idx_wishlist_item_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `delivery_tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_restock`
--
ALTER TABLE `inventory_restock`
  MODIFY `restock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `security_settings`
--
ALTER TABLE `security_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist_item`
--
ALTER TABLE `wishlist_item`
  MODIFY `wishlist_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `fk_cart_item_cart` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`variant_id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `fk_conversation_customer` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversation_seller` FOREIGN KEY (`seller_id`) REFERENCES `seller` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `fk_delivery_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delivery_updated_by_user` FOREIGN KEY (`updated_by_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_restock`
--
ALTER TABLE `inventory_restock`
  ADD CONSTRAINT `fk_restock_seller` FOREIGN KEY (`seller_id`) REFERENCES `seller` (`seller_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restock_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`variant_id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `fk_login_history_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `fk_order_item_seller` FOREIGN KEY (`seller_id`) REFERENCES `seller` (`seller_id`),
  ADD CONSTRAINT `fk_order_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`variant_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `seller` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller`
--
ALTER TABLE `seller`
  ADD CONSTRAINT `fk_seller_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_item`
--
ALTER TABLE `wishlist_item`
  ADD CONSTRAINT `fk_wishlist_item_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_item_wishlist` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlist` (`wishlist_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
