-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 07:42 AM
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
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_id`, `action`, `module`, `description`, `created_at`) VALUES
(1, NULL, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-06 10:17:57'),
(2, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 10:27:35'),
(3, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 10:28:47'),
(4, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:16:39'),
(5, 19, 'logout', 'Authentication', 'User logged out', '2026-05-06 11:19:42'),
(6, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:22:14'),
(7, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:25:07'),
(8, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:25:48'),
(9, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:32:05'),
(10, NULL, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-06 11:40:02'),
(11, NULL, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-06 11:40:17'),
(12, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-06 11:48:40'),
(13, 23, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 11:49:56'),
(14, 23, 'update', 'Logistics Orders', 'Order #12 status updated to shipped', '2026-05-06 11:50:11'),
(15, 23, 'update', 'Logistics Orders', 'Order #9 status updated to out_for_delivery', '2026-05-06 11:50:18'),
(16, 23, 'update', 'Logistics Orders', 'Order #9 status updated to delivered', '2026-05-06 11:51:10'),
(17, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-06 11:56:24'),
(18, 19, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-06 12:22:42'),
(19, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 12:23:55'),
(20, 20, 'logout', 'Authentication', 'User logged out', '2026-05-06 12:24:52'),
(21, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 12:33:13'),
(22, 23, 'logout', 'Authentication', 'User logged out', '2026-05-06 12:51:26'),
(23, 20, 'logout', 'Authentication', 'User logged out', '2026-05-06 13:01:52'),
(24, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 13:21:11'),
(25, 18, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 13:23:35'),
(26, 18, 'logout', 'Authentication', 'User logged out', '2026-05-06 13:30:47'),
(27, 1, 'logout', 'Authentication', 'User logged out', '2026-05-06 14:13:10'),
(28, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 14:13:34'),
(29, 20, 'logout', 'Authentication', 'User logged out', '2026-05-06 14:34:28'),
(30, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-06 14:56:07'),
(31, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 14:56:29'),
(32, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-06 14:57:26'),
(33, 18, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 15:03:04'),
(34, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:20'),
(35, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:22'),
(36, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:23'),
(37, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:25'),
(38, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:26'),
(39, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:28'),
(40, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:29'),
(41, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:31'),
(42, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:33'),
(43, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:34'),
(44, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:36'),
(45, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:38'),
(46, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:40'),
(47, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:12:42'),
(48, NULL, 'update', 'Seller Shop Image', 'User updated their shop image', '2026-05-06 15:17:51'),
(49, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-06 15:21:33'),
(50, 18, 'logout', 'Authentication', 'User logged out', '2026-05-06 15:33:21'),
(51, 1, 'logout', 'Authentication', 'User logged out', '2026-05-06 16:54:12'),
(52, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 17:00:11'),
(53, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 17:37:00'),
(54, 19, 'logout', 'Authentication', 'User logged out', '2026-05-06 17:37:22'),
(55, 18, 'login', 'Authentication', 'User logged in successfully', '2026-05-06 17:37:40'),
(56, 1, 'logout', 'Authentication', 'User logged out', '2026-05-06 18:51:06'),
(57, 18, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 08:19:23'),
(58, 18, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-07 08:19:36'),
(59, 18, 'logout', 'Authentication', 'User logged out', '2026-05-07 08:34:26'),
(60, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 08:35:26'),
(61, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 08:36:22'),
(62, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 08:36:36'),
(63, 23, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 08:40:37'),
(64, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 09:00:43'),
(65, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 09:03:13'),
(66, 38, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 09:43:53'),
(67, 19, 'logout', 'Authentication', 'User logged out', '2026-05-07 09:44:15'),
(68, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 09:44:56'),
(69, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 10:02:11'),
(70, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 10:12:40'),
(71, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 10:13:11'),
(72, 19, 'update', 'Customer Profile', 'User updated their profile information', '2026-05-07 10:47:30'),
(73, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 11:08:43'),
(74, 39, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 11:10:50'),
(75, 39, 'logout', 'Authentication', 'User logged out', '2026-05-07 11:12:03'),
(76, 39, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 11:12:38'),
(77, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 11:32:07'),
(78, 39, 'logout', 'Authentication', 'User logged out', '2026-05-07 11:49:22'),
(79, 20, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 11:56:22'),
(80, 1, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:01:52'),
(81, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:02:14'),
(82, 20, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:06:27'),
(83, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:07:17'),
(84, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:07:21'),
(85, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:08:15'),
(86, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:08:25'),
(87, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:20:18'),
(88, NULL, 'otp_request', 'Seller Security', 'User requested password change OTP', '2026-05-07 12:20:33'),
(89, NULL, 'otp_verify', 'Seller Security', 'User verified password change OTP', '2026-05-07 12:20:46'),
(90, NULL, 'update', 'Seller Security', 'User changed their password', '2026-05-07 12:20:56'),
(91, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:21:03'),
(92, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:21:27'),
(93, NULL, 'otp_request', 'Seller Security', 'User requested password change OTP', '2026-05-07 12:21:45'),
(94, NULL, 'otp_verify', 'Seller Security', 'User verified password change OTP', '2026-05-07 12:21:59'),
(95, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:22:19'),
(96, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:23:26'),
(97, 19, 'otp_request', 'Customer Profile', 'User requested password change OTP', '2026-05-07 12:23:40'),
(98, 19, 'otp_request', 'Customer Profile', 'User requested password change OTP', '2026-05-07 12:24:35'),
(99, 19, 'otp_verify', 'Customer Profile', 'User verified password change OTP', '2026-05-07 12:24:52'),
(100, 19, 'password_change', 'Customer Profile', 'User changed their password', '2026-05-07 12:25:06'),
(101, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:25:22'),
(102, 19, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:25:24'),
(103, 20, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:25:48'),
(104, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:34:14'),
(105, 1, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:39:48'),
(106, 1, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 12:40:08'),
(107, 19, 'logout', 'Authentication', 'User logged out', '2026-05-07 12:52:46'),
(108, 1, 'approve', 'Seller Applications', 'Admin approved seller application for shop: DTIP SHOP', '2026-05-07 13:13:53'),
(109, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 13:13:58'),
(110, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 13:15:40'),
(111, NULL, 'otp_request', 'Seller Security', 'User requested password change OTP', '2026-05-07 13:16:05'),
(112, NULL, 'otp_verify', 'Seller Security', 'User verified password change OTP', '2026-05-07 13:16:21'),
(113, NULL, 'update', 'Seller Security', 'User changed their password', '2026-05-07 13:16:30'),
(114, NULL, 'logout', 'Authentication', 'User logged out', '2026-05-07 13:16:36'),
(115, NULL, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 13:17:07'),
(119, 41, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 13:20:35'),
(120, 19, 'login', 'Authentication', 'User logged in successfully', '2026-05-07 13:22:19'),
(121, 1, 'approve', 'Seller Applications', 'Admin approved seller application for shop: BELLABOO SHOP', '2026-05-07 13:32:43');

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
(6, 2, 18, 1, '2026-04-28 21:57:53', '2026-04-28 21:57:53'),
(7, 2, 20, 2, '2026-04-28 21:58:54', '2026-04-28 22:01:21'),
(8, 2, 15, 3, '2026-04-28 21:58:57', '2026-04-28 22:08:16');

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
-- Table structure for table `couriers`
--

CREATE TABLE `couriers` (
  `courier_id` int(11) NOT NULL,
  `logistics_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_number` varchar(16) NOT NULL,
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
(18, 6, 'Jojana Jean Baglan Garabillo', 'KM87KOVcLT9LjfTM', '', '', '', ''),
(17, 7, 'Leonor Rivera', '0101', '', '', '', ''),
(19, 8, 'Pamela One', 'QsQqdKdOpwJ2r1HX', 'rtcwZJZDFxmrGiXKZnqs8ljscIFtEdFjQ1tGcUQBcnByyqur6p0+UM25dvo0lELi', 'PASIG', '5', '1234'),
(20, 9, 'Leonor Rivera', '2345678', '', '', '', ''),
(6, 10, 'RHOANNE NICOLE ANTONIO', 'YP26jXkRbUv5EIh0', 'n7mmJgnn72lCJ7DlXUiyxLouAUpKYGbrQ44/6e8NH/s+26B6Sa9wT+wfNqMYlQhAgxIaXACX6cqNbAHsR/4lsg==', 'PASIG', 'Metro Manila', '1609'),
(25, 12, 'Kathryn Bernardo', 'I/8+ehKhulzSvZEI', '', '', '', ''),
(35, 13, 'Bella Swan', '3QTIwKphkN7u5hZl', '', '', '', ''),
(40, 14, 'Bella Swan', 'IppLRDw99Xp2VA1t', '', '', '', ''),
(41, 15, 'Bella Swan', '/GpL/2LXSykCQ8Mt', '', '', '', '');

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
  `driver_id` int(11) DEFAULT NULL,
  `tracking_number` varchar(120) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_tracking`
--

INSERT INTO `delivery_tracking` (`delivery_tracking_id`, `order_id`, `status`, `status_note`, `location`, `courier_name`, `logistic_user_id`, `driver_id`, `tracking_number`, `updated_by_user_id`, `created_at`) VALUES
(2, 9, 'delivered', NULL, NULL, NULL, 23, NULL, '', 23, '2026-04-28 22:09:08'),
(3, 12, 'picked_up', NULL, NULL, NULL, 23, NULL, NULL, 23, '2026-05-06 17:49:02'),
(4, 13, 'delivered', NULL, NULL, NULL, 23, NULL, NULL, 38, '2026-05-07 15:45:08'),
(5, 14, 'delivered', NULL, NULL, NULL, 23, NULL, NULL, 38, '2026-05-07 16:37:21'),
(6, 15, 'delivered', NULL, NULL, NULL, 23, NULL, NULL, 38, '2026-05-07 17:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `logistics_id` int(11) NOT NULL COMMENT 'ID of logistics admin who added them',
  `driver_license` varchar(50) NOT NULL,
  `license_expiry` date NOT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(30) DEFAULT NULL,
  `vehicle_assigned` varchar(50) DEFAULT NULL,
  `license_plate` varchar(20) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `hire_date` date DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 5.0,
  `total_deliveries` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `user_id`, `logistics_id`, `driver_license`, `license_expiry`, `emergency_contact`, `emergency_phone`, `vehicle_assigned`, `license_plate`, `vehicle_type`, `status`, `hire_date`, `rating`, `total_deliveries`, `created_at`, `updated_at`) VALUES
(3, 38, 23, '12346789', '2029-06-07', 'Jojana jean Garabillo', '1234567', 'Beat', 'ABC-123', 'motorcycle', 'active', '2026-05-07', 5.0, 3, '2026-05-07 15:42:22', '2026-05-07 17:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `driver_assignment`
--

CREATE TABLE `driver_assignment` (
  `assignment_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `logistic_user_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','picked_up','in_transit','delivered','cancelled') DEFAULT 'pending',
  `assigned_at` datetime DEFAULT current_timestamp(),
  `accepted_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_assignment`
--

INSERT INTO `driver_assignment` (`assignment_id`, `driver_id`, `order_id`, `assigned_by`, `logistic_user_id`, `status`, `assigned_at`, `accepted_at`, `delivered_at`, `notes`) VALUES
(1, 3, 13, 23, NULL, 'delivered', '2026-05-07 15:45:24', '2026-05-07 15:45:55', '2026-05-07 15:47:59', ''),
(2, 3, 14, 23, NULL, 'delivered', '2026-05-07 16:40:59', '2026-05-07 16:44:21', '2026-05-07 16:44:50', ''),
(3, 3, 13, 23, NULL, 'delivered', '2026-05-07 16:54:31', '2026-05-07 16:54:54', '2026-05-07 16:59:11', ''),
(4, 3, 15, 23, NULL, 'delivered', '2026-05-07 17:06:10', '2026-05-07 17:06:39', '2026-05-07 17:32:36', '');

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
-- Table structure for table `locked_accs`
--

CREATE TABLE `locked_accs` (
  `locked_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attempts` int(11) NOT NULL,
  `date_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locked_accs`
--

INSERT INTO `locked_accs` (`locked_id`, `user_id`, `attempts`, `date_time`) VALUES
(1, 6, 3, '2026-05-03 10:56:38');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`login_id`, `user_id`, `login_time`, `logout_time`, `status`) VALUES
(1, 1, '2026-05-03 10:55:53', NULL, 'success'),
(2, 6, '2026-05-03 10:56:31', NULL, 'failed'),
(3, 6, '2026-05-03 10:56:35', NULL, 'failed'),
(4, 6, '2026-05-03 10:56:38', NULL, 'failed'),
(5, 1, '2026-05-03 10:56:49', NULL, 'success'),
(6, 1, '2026-05-03 10:58:48', '2026-05-03 10:58:52', 'success'),
(7, 1, '2026-05-03 10:59:05', '2026-05-03 10:59:10', 'success'),
(8, 1, '2026-05-03 10:59:58', '2026-05-03 11:01:27', 'success'),
(9, 6, '2026-05-03 11:01:41', '2026-05-03 11:02:01', 'success'),
(10, 1, '2026-05-03 11:02:12', '2026-05-03 11:05:27', 'success'),
(11, 6, '2026-05-03 11:15:16', NULL, 'failed'),
(12, 6, '2026-05-03 11:15:35', NULL, 'success'),
(13, 6, '2026-05-03 11:18:38', '2026-05-03 11:37:14', 'success'),
(14, 6, '2026-05-03 11:37:34', '2026-05-03 11:37:57', 'success'),
(15, 7, '2026-05-03 11:38:14', '2026-05-03 11:39:32', 'success'),
(16, NULL, '2026-05-06 16:15:47', NULL, 'failed'),
(17, NULL, '2026-05-06 16:17:28', '2026-05-06 17:56:24', 'success'),
(18, 20, '2026-05-06 16:27:35', NULL, 'success'),
(19, 1, '2026-05-06 16:28:23', NULL, 'failed'),
(20, 1, '2026-05-06 16:28:47', NULL, 'success'),
(21, 19, '2026-05-06 17:16:39', '2026-05-06 17:19:42', 'success'),
(22, 1, '2026-05-06 17:22:14', NULL, 'success'),
(23, 19, '2026-05-06 17:25:07', NULL, 'success'),
(24, NULL, '2026-05-06 17:25:48', '2026-05-06 17:48:40', 'success'),
(25, 20, '2026-05-06 17:32:05', '2026-05-06 18:24:52', 'success'),
(26, 23, '2026-05-06 17:49:56', '2026-05-06 18:51:26', 'success'),
(27, 20, '2026-05-06 18:23:55', '2026-05-06 19:01:52', 'success'),
(28, 1, '2026-05-06 18:33:13', '2026-05-06 20:13:10', 'success'),
(29, 20, '2026-05-06 19:21:11', '2026-05-06 20:34:28', 'success'),
(30, 18, '2026-05-06 19:23:35', '2026-05-06 19:30:47', 'success'),
(31, 1, '2026-05-06 20:13:34', NULL, 'success'),
(32, 1, '2026-05-06 20:56:29', '2026-05-06 22:54:12', 'success'),
(33, NULL, '2026-05-06 20:58:15', '2026-05-06 21:21:33', 'success'),
(34, 18, '2026-05-06 21:03:04', '2026-05-06 21:33:21', 'success'),
(35, 1, '2026-05-06 23:00:11', '2026-05-07 00:51:06', 'success'),
(36, NULL, '2026-05-06 23:01:38', NULL, 'success'),
(37, 19, '2026-05-06 23:37:00', '2026-05-06 23:37:22', 'success'),
(38, 18, '2026-05-06 23:37:40', NULL, 'success'),
(39, 18, '2026-05-07 14:19:23', '2026-05-07 14:34:26', 'success'),
(40, NULL, '2026-05-07 14:35:26', '2026-05-07 14:36:22', 'success'),
(41, NULL, '2026-05-07 14:36:36', '2026-05-07 15:00:43', 'success'),
(42, 23, '2026-05-07 14:40:37', NULL, 'success'),
(43, 19, '2026-05-07 15:02:31', NULL, 'failed'),
(44, 19, '2026-05-07 15:03:13', '2026-05-07 15:44:15', 'success'),
(45, 38, '2026-05-07 15:43:53', NULL, 'success'),
(46, 20, '2026-05-07 15:44:56', NULL, 'success'),
(47, 20, '2026-05-07 16:02:11', NULL, 'success'),
(48, 20, '2026-05-07 16:12:40', NULL, 'success'),
(49, 19, '2026-05-07 16:13:11', '2026-05-07 18:52:46', 'success'),
(50, 1, '2026-05-07 17:08:28', NULL, ''),
(51, 1, '2026-05-07 17:08:43', '2026-05-07 18:01:52', 'success'),
(52, 39, '2026-05-07 17:10:50', '2026-05-07 17:12:03', 'success'),
(53, 39, '2026-05-07 17:12:38', '2026-05-07 17:49:22', 'success'),
(54, 20, '2026-05-07 17:32:07', '2026-05-07 18:25:48', 'success'),
(55, 20, '2026-05-07 17:56:22', '2026-05-07 18:06:27', 'success'),
(56, 1, '2026-05-07 18:02:14', '2026-05-07 18:39:48', 'success'),
(57, NULL, '2026-05-07 18:07:17', '2026-05-07 18:07:21', 'success'),
(58, NULL, '2026-05-07 18:08:15', '2026-05-07 18:08:25', 'success'),
(59, NULL, '2026-05-07 18:20:09', NULL, ''),
(60, NULL, '2026-05-07 18:20:18', '2026-05-07 18:21:03', 'success'),
(61, NULL, '2026-05-07 18:21:27', '2026-05-07 18:22:19', 'success'),
(62, 19, '2026-05-07 18:23:26', NULL, 'success'),
(63, 19, '2026-05-07 18:25:22', '2026-05-07 18:25:24', 'success'),
(64, NULL, '2026-05-07 18:34:14', '2026-05-07 19:13:58', 'success'),
(65, 1, '2026-05-07 18:40:08', NULL, 'success'),
(66, NULL, '2026-05-07 19:14:10', NULL, 'failed'),
(67, NULL, '2026-05-07 19:14:37', NULL, 'failed'),
(68, NULL, '2026-05-07 19:15:40', '2026-05-07 19:16:36', 'success'),
(69, NULL, '2026-05-07 19:17:07', NULL, 'success'),
(70, 41, '2026-05-07 19:20:35', NULL, 'success'),
(71, 19, '2026-05-07 19:22:18', NULL, 'success');

-- --------------------------------------------------------

--
-- Table structure for table `logistics`
--

CREATE TABLE `logistics` (
  `logistics_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `company_logo` longblob DEFAULT NULL,
  `business_permit` longblob DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `authorized_person` varchar(150) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `operating_hours` text DEFAULT NULL,
  `service_type` enum('standard','express','both') DEFAULT 'both',
  `coverage_areas` text DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `rating` decimal(2,1) DEFAULT 5.0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logistics`
--

INSERT INTO `logistics` (`logistics_id`, `user_id`, `company_name`, `company_logo`, `business_permit`, `business_address`, `contact_number`, `contact_email`, `license_number`, `authorized_person`, `website`, `operating_hours`, `service_type`, `coverage_areas`, `status`, `rating`, `created_at`, `updated_at`) VALUES
(1, 23, '  Logistics', NULL, NULL, NULL, NULL, 'logistics@example.com', NULL, NULL, NULL, NULL, 'both', NULL, 'active', 5.0, '2026-05-07 17:48:47', '2026-05-07 17:48:47'),
(2, 39, '  Logistics', NULL, NULL, NULL, NULL, 'janajean925@gmail.com', NULL, NULL, NULL, NULL, 'both', NULL, 'active', 5.0, '2026-05-07 17:49:12', '2026-05-07 17:49:12');

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
(7, 19, 'Order Status Update - Order #ORD-69F0BF213BC61', 'Your order #ORD-69F0BF213BC61 has been picked up and is now in transit to your location.', 'order_update', 9, 0, '2026-04-28 22:32:37', NULL),
(8, 19, 'Order Status Update - Order #ORD-69FB0E19756F9', 'Great news! Your order #ORD-69FB0E19756F9 has been assigned to our logistics partner (JJRS) and is now being processed for delivery. You can track your order status in your account.', '0', 12, 0, '2026-05-06 17:49:02', NULL),
(9, 19, 'Order Status Update - Order #ORD-69FB0E19756F9', 'Your order #ORD-69FB0E19756F9 has been picked up and is now in transit to your location.', 'order_update', 12, 0, '2026-05-06 17:50:11', NULL),
(10, 19, 'Order Status Update - Order #ORD-69F0BF213BC61', 'Your order #ORD-69F0BF213BC61 is out for delivery! Your package will arrive soon.', 'order_update', 9, 0, '2026-05-06 17:50:18', NULL),
(11, 19, 'Order Status Update - Order #ORD-69F0BF213BC61', 'Your order #ORD-69F0BF213BC61 has been delivered. Thank you for shopping with us!', 'order_update', 9, 0, '2026-05-06 17:51:10', NULL),
(14, 19, 'Order Status Update - Order #ORD-69FC393864806', 'Great news! Your order #ORD-69FC393864806 has been assigned to our logistics partner (JJRS) and is now being processed for delivery. You can track your order status in your account.', '0', 13, 0, '2026-05-07 15:45:08', NULL),
(15, 19, 'Order Status Update - Order #ORD-69FC49B0DA926', 'Great news! Your order #ORD-69FC49B0DA926 has been assigned to our logistics partner (JJRS) and is now being processed for delivery. You can track your order status in your account.', '0', 14, 0, '2026-05-07 16:37:21', NULL),
(16, 19, 'Order Status Update - Order #ORD-69FC49B0DA926', 'Your order #ORD-69FC49B0DA926 has been accepted by driver Pat Lacerna and is now out for delivery!', '0', 14, 0, '2026-05-07 16:44:21', NULL),
(17, 19, 'Order Delivered - Order #ORD-69FC49B0DA926', 'Your order #ORD-69FC49B0DA926 has been successfully delivered by Pat Lacerna. Thank you for shopping with us!', '0', 14, 0, '2026-05-07 16:44:50', NULL),
(18, 19, 'Driver Assigned - Order #ORD-69FC393864806', 'Good news! A driver (Pat Lacerna) has been assigned to deliver your order #ORD-69FC393864806. You can track your delivery status in real-time.', '0', 13, 0, '2026-05-07 16:54:35', NULL),
(19, 19, 'Order Status Update - Order #ORD-69FC393864806', 'Your order #ORD-69FC393864806 has been accepted by driver Pat Lacerna and is now out for delivery!', '0', 13, 0, '2026-05-07 16:54:54', NULL),
(20, 38, 'Status Updated - Order #ORD-69FC393864806', 'Order #ORD-69FC393864806 has been marked as in transit.', '0', 3, 0, '2026-05-07 16:58:52', NULL),
(21, 38, 'Delivery Completed! - Order #ORD-69FC393864806', 'Congratulations! You have successfully delivered Order #ORD-69FC393864806 to pamcustomer. Great job!', '0', 3, 0, '2026-05-07 16:59:11', NULL),
(22, 19, 'Order Delivered - Order #ORD-69FC393864806', 'Your order #ORD-69FC393864806 has been successfully delivered by Pat Lacerna. Thank you for shopping with us!', '0', 13, 0, '2026-05-07 16:59:11', NULL),
(23, 19, 'Order Status Update - Order #ORD-69FC51B03E568', 'Great news! Your order #ORD-69FC51B03E568 has been assigned to our logistics partner (JJRS) and is now being processed for delivery. You can track your order status in your account.', '0', 15, 0, '2026-05-07 17:05:34', NULL),
(24, 19, 'Driver Assigned - Order #ORD-69FC51B03E568', 'Good news! A driver (Pat Lacerna) has been assigned to deliver your order #ORD-69FC51B03E568. You can track your delivery status in real-time.', '0', 15, 0, '2026-05-07 17:06:14', NULL),
(25, 38, 'Delivery Accepted - Order #ORD-69FC51B03E568', 'You have successfully accepted delivery for Order #ORD-69FC51B03E568 to pamcustomer. Please proceed to pick up the package.', '0', 4, 0, '2026-05-07 17:06:39', NULL),
(26, 19, 'Order Status Update - Order #ORD-69FC51B03E568', 'Your order #ORD-69FC51B03E568 has been accepted by driver Pat Lacerna and is now out for delivery!', '0', 15, 0, '2026-05-07 17:06:39', NULL),
(27, 38, 'Status Updated - Order #ORD-69FC51B03E568', 'Order #ORD-69FC51B03E568 has been marked as picked up.', '0', 4, 0, '2026-05-07 17:06:41', NULL),
(28, 38, 'Status Updated - Order #ORD-69FC51B03E568', 'Order #ORD-69FC51B03E568 has been marked as in transit.', '0', 4, 0, '2026-05-07 17:32:31', NULL),
(29, 38, 'Delivery Completed! - Order #ORD-69FC51B03E568', 'Congratulations! You have successfully delivered Order #ORD-69FC51B03E568 to pamcustomer. Great job!', '0', 4, 0, '2026-05-07 17:32:36', NULL),
(30, 19, 'Order Delivered - Order #ORD-69FC51B03E568', 'Your order #ORD-69FC51B03E568 has been successfully delivered by Pat Lacerna. Thank you for shopping with us!', '0', 15, 0, '2026-05-07 17:32:36', NULL),
(32, 41, 'Seller Application Approved - Account Upgraded to Dual Role', 'Congratulations! Your account has been upgraded to Dual Role. You can now switch between Customer and Seller modes using the \'Switch Role\' button.', NULL, NULL, 0, '2026-05-07 19:32:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `setting_id` int(11) NOT NULL,
  `logistics_id` int(11) NOT NULL,
  `customer_sms` tinyint(1) DEFAULT 1,
  `customer_email` tinyint(1) DEFAULT 1,
  `seller_alerts` tinyint(1) DEFAULT 1,
  `driver_notifications` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`setting_id`, `logistics_id`, `customer_sms`, `customer_email`, `seller_alerts`, `driver_notifications`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, '2026-05-07 17:48:47', '2026-05-07 17:48:47'),
(2, 2, 1, 1, 1, 1, '2026-05-07 17:49:12', '2026-05-07 17:49:12');

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
(9, 'ORD-69F0BF213BC61', 19, 'delivered', 'unpaid', 12.00, 0.00, 0.00, 12.00, '', '', '', '', '', '', '2026-04-28 22:07:29', '2026-05-06 17:51:10'),
(10, 'ORD-69F0BF35B770B', 19, 'pending', 'unpaid', 13.00, 0.00, 0.00, 13.00, '', '', '', '', '', '', '2026-04-28 22:07:49', '2026-04-28 22:07:49'),
(11, 'ORD-69F0BF570C6A5', 18, 'pending', 'unpaid', 19.00, 0.00, 0.00, 19.00, 'Jojana Jean Baglan Garabillo', '0202', '', '', '', '', '2026-04-28 22:08:23', '2026-04-28 22:08:23'),
(12, 'ORD-69FB0E19756F9', 19, 'shipped', 'paid', 2.00, 0.00, 0.00, 2.00, '', '', '', '', '', '', '2026-05-06 17:47:05', '2026-05-06 17:50:11'),
(13, 'ORD-69FC393864806', 19, 'delivered', 'paid', 13.00, 0.00, 0.00, 13.00, 'Pamela One', 'Jn5qstWpVWmXCQ/8', '', '', '', '', '2026-05-07 15:03:20', '2026-05-07 16:59:11'),
(14, 'ORD-69FC49B0DA926', 19, 'delivered', 'paid', 3.00, 0.00, 0.00, 3.00, 'Pamela One', 'Jn5qstWpVWmXCQ/8', '', '', '', '', '2026-05-07 16:13:36', '2026-05-07 16:44:50'),
(15, 'ORD-69FC51B03E568', 19, 'delivered', 'paid', 3.00, 0.00, 0.00, 3.00, 'Pamela One', 'QsQqdKdOpwJ2r1HX', 'BLK 1 MANGGA ST, PASIG CITY', 'PASIG', '5', '1234', '2026-05-07 16:47:44', '2026-05-07 17:32:36');

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
(8, 11, 10, 15, 7, 3, 5.00, 15.00, '2026-04-28 22:08:23'),
(9, 12, 12, 20, 7, 2, 1.00, 2.00, '2026-05-06 17:47:05'),
(10, 13, 9, 12, 6, 1, 10.00, 10.00, '2026-05-07 15:03:20'),
(11, 13, 11, 18, 6, 1, 2.00, 2.00, '2026-05-07 15:03:20'),
(12, 13, 12, 21, 7, 1, 1.00, 1.00, '2026-05-07 15:03:20'),
(13, 14, 12, 20, 7, 1, 1.00, 1.00, '2026-05-07 16:13:36'),
(14, 14, 11, 18, 6, 1, 2.00, 2.00, '2026-05-07 16:13:36'),
(15, 15, 12, 20, 7, 1, 1.00, 1.00, '2026-05-07 16:47:44'),
(16, 15, 11, 18, 6, 1, 2.00, 2.00, '2026-05-07 16:47:44');

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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `transaction_reference`, `amount`, `created_at`) VALUES
(1, 12, 'GCash', 'paid', NULL, 2.00, '2026-05-06 17:48:14'),
(2, 13, 'GCash', 'paid', NULL, 13.00, '2026-05-07 15:04:09'),
(3, 14, 'GCash', 'paid', NULL, 3.00, '2026-05-07 16:14:06'),
(4, 15, 'GCash', 'paid', NULL, 3.00, '2026-05-07 16:48:04');

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
(9, 6, 'Women Short', 'dfghjkl', 'Women', 0.00, -1, 'active', '2026-04-28 20:10:16', '2026-05-07 15:03:20'),
(10, 7, 'Pants for Men', 'GHPWSMAKND', 'Men', 0.00, 0, 'active', '2026-04-28 20:26:31', '2026-04-28 20:26:31'),
(11, 6, 'Skirt', 'Skirts for women', 'Women', 0.00, -3, 'active', '2026-04-28 20:59:19', '2026-05-07 16:47:44'),
(12, 7, 'Long Pants', 'long pants po', 'Men', 0.00, -5, 'active', '2026-04-28 21:03:40', '2026-05-07 16:47:44');

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
(12, 9, 'SKU-9-83A663-0', 'S', 'White', 10.00, 9, '2026-04-28 20:10:16', '2026-05-07 15:03:20', 'uploads/products/product_9_variant_0_1777378216.jpg'),
(13, 9, 'SKU-9-83B200-1', 'M', 'Khaki', 10.01, 10, '2026-04-28 20:10:16', '2026-04-28 20:10:16', 'uploads/products/product_9_variant_1_1777378216.jpg'),
(14, 9, 'SKU-9-543F2A92', 'L', 'Black', 10.00, 12, '2026-04-28 20:17:07', '2026-04-28 20:40:45', 'uploads/products/product_9_variant_1777378627.jpg'),
(15, 10, 'SKU-10-78B3AD-0', 'S', 'WHITE', 5.00, 10, '2026-04-28 20:26:31', '2026-04-28 20:26:31', 'uploads/products/product_10_variant_0_1777379191.jpg'),
(16, 10, 'SKU-10-79081AAB', 'M', 'WHITE', 5.00, 5, '2026-04-28 20:26:56', '2026-04-28 20:26:56', 'uploads/products/product_10_variant_1777379216.jpg'),
(17, 10, 'SKU-10-EDE1CF3C', 'M', 'WHITE', 5.00, 5, '2026-04-28 20:58:06', '2026-04-28 20:58:06', 'uploads/products/product_10_variant_1777381086.jpg'),
(18, 11, 'SKU-11-72269A-0', 'S', 'Yellow', 2.00, 12, '2026-04-28 20:59:19', '2026-05-07 16:47:44', 'uploads/products/product_11_variant_0_1777381159.jpg'),
(19, 11, 'SKU-11-F430D5A6', 'M', 'Yellow', 2.00, 10, '2026-04-28 20:59:47', '2026-04-28 21:00:13', 'uploads/products/product_11_variant_1777381187.jpg'),
(20, 12, 'SKU-12-CA02F4-0', 'S', 'White', 1.00, 16, '2026-04-28 21:03:40', '2026-05-07 16:47:44', 'uploads/products/product_12_variant_0_1777381420.jpg'),
(21, 12, 'SKU-12-CA0966-1', 'M', 'White', 1.00, 9, '2026-04-28 21:03:40', '2026-05-07 15:03:20', 'uploads/products/product_12_variant_1_1777381420.jpg');

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
(6, 'Driver'),
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
  `business_category` varchar(50) DEFAULT NULL,
  `tin_id` varchar(255) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `shop_address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `business_permit` varchar(255) DEFAULT NULL,
  `valid_id` varchar(255) DEFAULT NULL,
  `shop_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`seller_id`, `user_id`, `full_name`, `shop_name`, `shop_description`, `business_category`, `tin_id`, `age`, `additional_info`, `shop_address`, `contact_number`, `is_approved`, `created_at`, `updated_at`, `business_permit`, `valid_id`, `shop_image`) VALUES
(1, 7, '', 'sapme seller', 'seller sells products yes', NULL, NULL, NULL, NULL, '123 Pasig City', '1234355678', 1, '2026-04-22 19:58:21', '2026-04-22 19:58:21', NULL, NULL, NULL),
(6, 18, 'Jojana Jean Baglan Garabillo', 'BANANA SHOP', 'Business Category: Men | TIN ID: 0999999 | Age: 33', 'Men', '0999999', 33, NULL, NULL, '0202', 1, '2026-04-23 19:09:33', '2026-05-07 14:34:07', NULL, NULL, NULL),
(7, 20, 'Leonor Rivera', 'STRAWBERRY SHOP', 'Business Category: Women | TIN ID: 09876543 | Age: 33', 'Women', '09876543', 33, NULL, 'Pasig Citty', '2345678', 1, '2026-04-25 08:22:01', '2026-05-06 21:13:41', NULL, NULL, NULL),
(14, 41, 'Bella Swan', 'BELLABOO SHOP', '', 'Men', 'HVVjbjHS94EHw/Kg28qqx+c17drY21F9YJIC8tW7+XA=', 25, '', 'Pasig City', 'zTbpm7HTBPrV9TVpUp8dEYdmJDJEqA', 1, '2026-05-07 19:21:28', '2026-05-07 19:32:38', 'uploads/seller_docs/business_permit_picture_1778152888_bcd6f6e6.jpg', 'uploads/seller_docs/valid_id_picture_1778152888_e883eb33.png', 'uploads/seller_docs/shop_image_1778152888_14150226.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `seller_backup`
--

CREATE TABLE `seller_backup` (
  `seller_id` int(11) NOT NULL DEFAULT 0,
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
-- Dumping data for table `seller_backup`
--

INSERT INTO `seller_backup` (`seller_id`, `user_id`, `full_name`, `shop_name`, `shop_description`, `shop_address`, `contact_number`, `is_approved`, `created_at`, `updated_at`, `business_permit`, `valid_id`, `shop_image`) VALUES
(1, 7, '', 'sapme seller', 'seller sells products yes', '123 Pasig City', '1234355678', 1, '2026-04-22 19:58:21', '2026-04-22 19:58:21', NULL, NULL, NULL),
(6, 18, 'Leonor Rivera', 'BANANA SHOP', 'Business Category: Men | TIN ID: 0999999 | Age: 33', NULL, '0202', 1, '2026-04-23 19:09:33', '2026-05-06 19:42:25', NULL, NULL, NULL),
(7, 20, 'Leonor Rivera', 'STRAWBERRY SHOP', 'Business Category: Women | TIN ID: 09876543 | Age: 33', 'Pasig Citty', '2345678', 1, '2026-04-25 08:22:01', '2026-05-06 19:42:25', NULL, NULL, NULL);
INSERT INTO `seller_backup` (`seller_id`, `user_id`, `full_name`, `shop_name`, `shop_description`, `shop_address`, `contact_number`, `is_approved`, `created_at`, `updated_at`, `business_permit`, `valid_id`, `shop_image`) VALUES
(8, 25, 'Kathryn Bernardo', 'SLAY SHOP', '{\"full_name\":\"Kathryn Bernardo\",\"email\":\"jojanajeangarabillo@gmail.com\",\"age\":26,\"tin_id\":\"RHwlX42H\\/2BdQC+LzlCTjTSm1VKQHxPfEiOBEXxLF0o=\",\"business_category\":\"Women\",\"business_permit_picture\":\"uploads\\/seller_docs\\/business_permit_picture_1778072157_15341340.png\",\"valid_id_picture\":\"uploads\\/seller_docs\\/valid_id_picture_1778072157_fe8e7f3f.png\",\"shop_image\":\"uploads\\/seller_docs\\/shop_image_1778072157_9c1ed295.jpg\",\"registration_date\":\"2026-05-06 14:55:57\"}', 'Pasig City', 'JvbceQ29PH9XM0IyBcum1vH7uqSZKs', 1, '2026-05-06 20:55:57', '2026-05-06 21:12:20', NULL, NULL, 0xffd8ffe000104a46494600010101010201030000ffdb00430006040506050406060506070706080a100a0a09090a140e0f0c1017141818171416161a1d251f1a1b231c1616202c20232627292a29191f2d302d283025282928ffdb0043010707070a080a130a0a13281a161a2828282828282828282828282828282828282828282828282828282828282828282828282828282828282828282828282828ffc200110801db02df03012200021101031101ffc4001b00000105010100000000000000000000000200010304050607ffc4001801010101010100000000000000000000000001020304ffda000c0301000210031000000194ab4bc7a9a7704d9910c8967dee5ec474501499d518aec1a99d0e8d2b090c846c32208cad50a99c8c85836261dc484938c9d8662713380c446023442d388c519098d84ec40a261989814ea1930d3399808c416240bb38cec43a7433bb8ce806092c2a95adcaac46594b95150b1c5496331219dde85de35781045791092cb5cc9d09233a6a388ce55b9cf9c74519d85ceadab9e55695acaed3c48e91808d500cac412b8c21955014289980433190072433a7193b82890212205d88889444c80490466131300e68644c33130cc6c0a240b38c1b898c9c963331121baaacb166be50d3b1d3958ce909d3d3268478d450e0e24739d525744292132678a51d3ab1c5ca51d3cd637a2ad733a860b2165552c7646f220512416915449c808ec0cac46f51a912451d844246e46f2b008dc8d1b00a472119d1114888d1a0239d4b1123b2224e3234009b91b48c46d2911148c0a2447289cad14e6a53b2cd2cb6af62775acb3bba8bbbc30300a317233504219ee558ad56c14c35ea113466112626384c34ef4c9d036eb28d31af6f3a8c52a2056aca812dd8cd7d073364bec536bc76541b768cb6d0733db4919725f4678e8c86336e918326db98c5aec643ea1194da8e652d363316ac2663e8b9971ec398cdb226549a30155eca2995a45456da5aafa1059554eeb118066ddc960d6409cac0724a2ef1c3c6d18f19a5689a5486dcc058b19312e0caf2ca7ab9d359a94cada674b3d609c4c79213264054eecf0ad4139b7167166cb2d065b96f21d360331569acc51a73e2956c8e4a37031dcd858e8dd0c6736cf05d36df112ee3e1126ec9cfab36d61a5db0c669769f1156d064349bb064b56bb643cbacf908daab9cc692cd12fbe72344f2d8bb2e62345f38cbb8b22a811244c9a9d980408604800269ac1109545ad5ae578a756fd5ceb4f335b26ef635b8b9ee3b9b7c9eb26c65e9dbb9e562d380ae690e260a650124cf13d29e2789e307127719d2a4e909243a671d338924274c3a4874c8228dd0d0384c295d32474c87664ae924492193b2a4ce264c3bb38c9d0ce289626600d30e858318da5312106290d21b656e2668f3d6ed5ccc79ad7c7ad34bb2f24b372f3eb57a71c01e93273bcdeab9aec4db9ebcfbe3c1cd9b4b3beba5e6f50b0d2d7b1443565ba50192bc45533c641b0b21b0a09020d4689146895010685068504e0e12171d2424c8266412643b26090b8ec98742c12112451a2478d123c6e1b83868587616098594e3180b1353949641889a853ce8b9dbf9d7a4d4f87d165d9e7c72dae5da9d9d70d62f4fcbd5d672ed576d67535b9fbb9bbb7f174d77a7ceb5ac7119ba797cbd1b9b5e61b1acfa2c595d6b1c4e1f4b825c390c84d3121c522931110b5874aad71a5a6f686abab0e95df4add984fd0555c95661051ba0393094f7172dba04980ba084c5572b28b12414e23a7b6b49b6a44c48f633c814ca589e52585e6446e4c2178c211108512474f46807a99db64b547969ad9c1c4d76adfa0707de5c5acb973358a35e7ccceabd62a8475b4777534798ec70f7cf94dc87431ba9a793a1cfa6eec65686f19d8d554d73b7ba4aeb4fd4bcfbbc4e571f7b22e6fd8574e228e8602e904658eb703774d7889ba1bbae78306e60e76c16268c839f737cefdc8ed6f1c6573dcc74e32af5a072f2435fa73bb13f5b9d72da5b5733bc3dbe7ba3bcf2a85fd36b8bafdb412f2677b0fa73b91d5eae6b074f52c6379bb956767a3f3cf44e6378e764b87cfad756a8e777e9ebe86b1c90f497ce56b751cf67a57286beb9dfe8390f44d733c4e83145b19bba61723ddf3d9ebcb74d673b58dbdff39f41b9a795b3858b462d3965a176ddbde5435e42e737bfcff4e5435727573bac634b975edece66a6f9f0b4ba7c8c7486fe64d6743b9cbeddc8e6ebe655ea57a0ae6737ba73cfacf7879e9cccfd1489e6da5dbbeb1cd64f78d9d714bb415f35b3dfc7ac3d80b1672d7b5e1cef9e8f5a297cf21f466e9cb81ed2e4b9dd5b72dcceb94eda39b5cf3f33a18d702bed57c6f8bc2f4c0e9cfcd7addd39736d5eb79e90e66f95c1f1dd865eb1e75b9d59e77ccd4ecd4d7356f71ae7cd7a8e89ede7733b48f1ae04bb80ae1faeb4af29eada885a74056d623e6caa8bd5b03d2b9ae9b598aacb9d9d4f89a1926d393541355b511e78374e55cace2aebe56be4f2edd7ed616f59cbd2b7172df3ba94d75e5d5e9e6df53cfd2cf4bed25a5e1f3756631e6de558e766c6354e7dfb3acf33076b9e7343d6553998fab80629ebd9ccc38bacb52be7fa46b1c12abd3d614917492e269637438d3edf0fd6d0d18e396a434f6359a03a18a97652d32a5ec3d99a935399e8337a8f36f48f32e9ce6b03af9d653583b29c935d4ab5fa7ad9e9cdd6e8b35bad16c666b843d2731dae75af9fab983c162956762856de695bacfacf5fd572bd4e354b3ef56c6e8d6b1587dbc9d1b2bdea3a99d7256b375ba73a58f496a75d89ab97c7b757d2717d8e5c4e862edcbcf4176a74e7d2eb626db5733b5332cb905dca92e2e52857765c295774fc311dbb7188ecd716e768b8a63b50e2d93b8a9191c7a9695647b079bfa25c79874591b359ba553473be6bbbf33f4ce7d39cea39ae8f58a72c11af35d1733d45ce264e8e5d6d6c52bd1c8771c7768b029a197a0ab6fce6e7bf7f3b51e8ade7a55e82bcf5cf405e7e32fa0af3e13d0979eb1e883c06c474cd6ab8d8fb187a985434b2b78ac6325765d2f3fd0e3546395f1bc8a3a9985a36ae68e8d4b35c76bd0bd73c53da55af9fab99cbaeaf46d7638fd16c28969c73f4c6eee656bcb6f334b3759d1942c1c0f3dd8642dacee9b9b2fcbd2d38c5956a99b25a8b59ae366a2d8a57ebc9a899ab9ec7dbc3b2df7de5fea373c45b8150ec731d4e37e7feade5de999d737d2607416510966395dac1e8759e6eb5a84dcb1194d733dbf0fdeac50d9824dce17b9f3ca9af66bb61b58ba578e8e743a19eef9dafcfb12aa90eb075352a595fd1388ed39ef6e868e7d8385bb8d59183bd89bc559a29ebb3e8f077b1aab0d88f3bcbceb95b34a78e53442222ad58b2f7ce2906eb460acf2e9d64f158e98c3e67abc8c6b9ad2c2ea77893661e926ab6669e5dcde8ed62c68b713975e92bcf48f425e7f29ddbf0875dc2e21ceddb8718ee43861b3bea2c460e16f60eb39fe93e6bdd98f476b24c9ef388ebf3ae57b7e2fb8c74cfdbcbd8d6685eada09e7bd3f2fd827295aec56ed4b435b2e43bde43aeba08a604d3a96fceae7bc2f3e15f445e7c93d04bcf9cf406f3e697d0979d4567a5bf993afa61799efe5d58d8af4b2f4ea59cc66ec73dbcc53d50aeefa4f2bf52e761826a79de643a50e6d5ad72a834028e9b78f72dd957695c87b6364b92b16b3978fb58dc7af29d064e974e7bfd0f3dbd6c593af909a7201a70f8fb9af35ce52ebb116bcbb96139d966d4325f6702538a3b910c7ad3ea4af5ee6b3cf60747cfeb391df709dd91e2f43cb15ba6e5bb1cef03ade6a4e7bd7d8c2dcde20e53acc7ac9b870cb990ee16a646cbb668e954b85d4445cf3bf44f28d635e7cd52cda942eeb31c15accba3566ce964a8a1b2fc5a19c56f41e17b5ceb773f433d1a392558a0bd055082f57cdc2ecb92eb88aae954b29437ebcb46a6953971eb5d8d65bd4f4ecb36dee235a3b5ac80dc8938bcdccd5c7519ecea5cc9af15eb9cfc9d6ca5baf2e3969b8bc73d1cb801b7d1a4f3d824f4397cf2dd76b070d21da971671d88f235cef0b376ece639beb9ece0b5a2deb398b3d5c52f371777626b88bdab992d9dac3da21c7d7c8193191a94509cb51732d57b25eb15efd8199a7e6c9dd2e1ed1d7bf1d1af68dc2b9dd2e4823b25c5553d00bcd0acf48b3e59d44bd6c534235ea5b095a1d1af59b574e9cd73bd761f488abe9c3665d6bd972b503c1241b7b053ba98bd367359b36305ce98b9bdb4e075799b59df4b7b99b87597b95d3b93c7d2cd5d438e74e1b336abe9057d9c296f3e7dae3eb9face6fd037e6f3bb14f6ca75f5716c96969d7e98d6e879cdccdcbb04c73b26eb18fa9238b5729cdbe07a3731360ed99795d340bcdd6eb5ae791b9d0bcb06c66a4b58da0eb1e9d19c1f30f4ff0031b2cdbad2cb2497259df226a5adae1633ed418d866ede5dd951d2877e7abdef11d9e37bb42fd107730dd36eae1e72f439dcf52cdecf6bc9fd4b79bf4e8d70303780e65ba86b1ef67c92c6101c4cc868a58da26d9c4daaf3aa5d7c78de4ea684c0e8d7bfacd4cdd4ca2fe7ead22843839076adca859d78e1c135d13f3893a27e42e574adce0c74e1815abb9d1c1dc4a82843616244282704482241dba7681864804e2c1b0a09c1c74c8927ad38b1b67cf57ab2e5ac4742dceb1d0bf2d22f50b044e8e3c5ce4eb0f81bb67612f9df6b2edbdba8090db333277b325c2a3d2e69cefacf9a7a56b15232acace8090504590ad64a8d230ceef4886685b18db15839b622cdd2dbe3a4b7abb9cb69259c9d2cd35278264e4707a2e525bb0a89770b7adcbc7dacf2cd9cabee6a65c5af8b641580353b1dbc3db4aa2cc4d08106e2821644ed5e40edd3b64714908611a2470412643942f134d5e6a7f32f4cf34ab1a39d7a5753cd3ad26866bc6c0c459d8d0d8ab75542ed1df19fbef3fed71ad8a5768a0e8e7446865c19f2dbc1b73db83e95c9f5779d582606a067408c8c093c850243128a1a9198e24d9c9d4ae02c68c85ba1d1664ada7574cad9ba9929a28b396c51c3cd3b33e3a959e81071d2cbd747cb31dd3f9fb59df17000bd659e202cf42b187b715521085dc6468146224886b30ca0432b009d848d20291282401cc06365ea79d57637f8073b73e20a5efe9f1b2a75b6f89297ae1e4e857751715a09d6daf3de8a5e961961414ad18f535284d51a3b51185e85cb74e95aadda76469054c08608865aa8a5404ace11d632edca56ce76f5ba86a418340ed2c717a51af957f3cd6b15ad9c7646dd39aad99d573e589b7a45e6e6bf25ce04b776f3ae7f37bce4e2857e8a9ee686de4eadcd57484884749c64ec27760a6864045d817742490ece86490a5031fcc3d3bce35237b612d4bc36ec8edd5e879fa70e0e8b06f38a8dbadae467a79d62ef782ee31bd9cfd0ce46b3573cd9a98702ecd78344cee9619124a5733e9c639088666237322948cd12bc6f52b0484f72a5a3cd296941654bb57463736b1fa195b3af66d6a4b5009eba12cd8cd72f499ac68351566c4796a5d27cc44a00c582ac6331b24469d493c24b1e3ed105e84ec6b50d196c326aa76f2a24e85b36e4b3b4684d238e4cc1e668c652d4848a457155cce994b1db85159ec3b3154d1720b91a59ea945040f2564f29dd79c53c338eb1a5e8783b9cba151bf4f50198421611c919524050642f406444f62204e6a6df4b8d78e8574153998e2c529a315e8c890aa3179e93d5e7a6e5b7a2e975a8b179a982dc0acc46a4512eee46a4b6e5562680e6348a1bce73b1f4ee7218be903673916c5b30b4ed04b4e3b712d58ec57b0284b4d00016a45a1514681e71d979e819795225b8f4592eb5245c0ac2b642bbc470db8c0e9702c1d40f356aac50d2b052d1439494b4a8d5691c4667104dd142c1a1a511253a39a6fd1c080d5833c6adc70a2538ec0013c3129808021b66558ec0ab917eb9a3925d5bd7225d89a7171f78470563b6238983bdac71b7703533ab96736e4b727ccb92df2cd234e4c973547242b5e1a0316a3a2ac9203a6495ef531a39eb2485b1a9a9c847d91571a5d809c92eb48e3d760271cbb24718bb363865da738674f5ac4588650093b912682ac3d346a5ac4b11d4c9cd5935c6acc08581299da8cab1f3d5eb669d31894631a9ca12226764264e14d1cb2b886b1475f626a192331dc506c322497eacc04826272304ca4518eaf079bd0ae4d6b3ddae3dd3b3a199d5cbcadddf9e5e661e860cef30761273f634e739b0e8b22a1cfd0a12dba76aaa3d6b154ded4cabfbcced1151946e138b84e091d321d0a54e292b73fd411c836fe14a9a33228a48864ce1588260990124903d68dee7e38ea27e56d182f1bd9228dc9146e4cf1a58c5d9138e8147537efad4b60911243bb38927193897ed73f60da3ceb25a28e7544c5263713e9bc1cd65e77653d9e793fa1d9b3cf29fa86b5cf1fd5ea46b32a5062e9ac4ab5d0e7e33ac9997b3e6a854b1531ab75a4ad6495248ace8ecd5977998c641a41492207a4eea133b093209850e2e8442266e3f5a271c1b186a6d1b92cf5a624894638b006d189288345474fa8c9dc1774138d88ad67a1d6337501824ee327719d389261d0306222aee24844248e4c4b2c95c8b2f548b4f55d6cb4089445e05ddc6624a289806311b334b1a5ceaf4db16f5682e6a54d6bd7f5986629750643788dc902d22a04987494324874ce267701c9806918504a8e632bbda4bcac875a2789c6859c0742c10212b24d61b010a5dbdd32768422516211338892127719d089908e908a31816e1c668640684424a442e11092a7671dd9c44cf0e92124953250c2f1d2c9d1a11916f7ad598b2ed4b6624faa253398561198489a4444d289184cc903b10c9d84930ec90c92132426740bb8879778ce42bf6f9073a32828b3a199d2522ee2c5737bd62384c061491392a8dc249c49d874cc12171d338a0b10904529813d662e9c264a511592bc652c8f19123c44b23c4e48f1344eab8969a98adb8e88172ab4c95adbd8b23e818e501315063040136a8c640018c4009040626210923093b2326424cc3b261d9d02e6446a532bc5a964c13daac99781d78af9b0fa523cd17a622349ec721354cd28272485152b9129c880a6a84ea772ae7894ae4a149c4a55cd7bf989658c6cacac94b54ac3032c43165f3e235561c8bb0d9b31662290815a56547b488cd2a53eb5c8cfd0652a17118485599d9004e2a4c90226203261864ce4d052c4252bd46f21442d3a2069d88a44a84485059d879a09c2af630a5d1adc491d58f1f52cee970027fffc400311000010303020503040300030101010000010002030411121321051014223120323315232441303442253540444345ffda000801010001050206fe90516f28663196b839a88442213dbe82acacb10b15721037e47f8cadcac5582b045816e103cff7ebfd72b958dd62159582b2bf31eb250174d16413232506c6d59a9ea1dcbcaf1ea26c8dddc81f48288bf28a43198de1ed44221109c2ce082fdfa4b503cc7f0dbd6421c8a1fc05590f511fc24a0dba1b209bb2dcf29e6bf21bfadceb7227983eab64bc2638b1d14a1ed455ae656ef6e4e1bfa88bf3217857f40e7fafe2f1e8baf3cedfc206d6f57941aae9bba6b500bc29a5cb901eb2ebaf0afcadcc207d3e55ac9a4b4c355a8b5234f94d8b5e7d16fe33641ae5672b382c87f20e448562558a21c87f35d5d0cb96fcca02e9ad411d84d2e7c80f51d91dd128ab22e40d96ce16b7307d44590514b972216e880bc2f2adfc3e501e822eb1b21fc76bab7a6d643f8ec4ac7d23c726b5044d84b26655bd44f22795804e37405d3e35e10722de610f4044728a5e47910adcade972b6d64d1bdbd56bab7aecacacacacacadcedb5b91f5d95bd7fa508bc8459120091e5e578f51dd7844ab5d1759794d65d358ac0a73139964090b672b5b983e9b728dfe91bf32b172c0ac5dcecad718d8e2b158ac5595b6b722c2568bd68b90610acb158ec5bbe2b158ac562addb8ab72c0ad372c1c82b2b270b2015959595906a3c8349463c57516123cbddcade826dc895e56cd4e75d6374c8d06a0405aa9b3ab07074688b20e56e60fad8fb22c5bf206c651dc5451dd81ab158ac162118484d189d305b858e0b4d69a11a2cdb4eeb4acb4d69ad35a6b4d695969ad35a6b4d69ad34224e6dc88ae8c602c160b0582d2c860837795bf73158ac5629b127b93632492c622f7b916e009bfac9e44a0dba2eb2f29ac41a003285ac8ce83ca0f29929698ea1af4e62732cbc204156b7307d50c8589b1b1ed744d4621770b92d509ed23d31bac9de46cb2b8e77d93763ea3c823cc226eacaf61e8b21b0237b2778e65f883772606ddf77a640d52e10b5c723ea279795b3539c4a02e9ac4f95ac4f90b8b9e8bd3dfb6914d6211a1128f3621629cc45b640af3eb6a7345a3a82c6f504ad52b54ad52b59cb5dcb5dcb59cb59cb5dcb5dcba872ea1cba872ea1cba872ea5cba972ea9cbab72eadcbab72eadcbab72eadcbab72eb1cbab72eadcbab72ea9cbaa72ea9cbaa72ea9cbaa72ea9cba972ea5cba872ea5cba972ea1cb5dcb5dcb5cad62b58ad62b54ad52b54aea5c148f2f3616f41e455965c9ac56b27e4e4634634e8d6938a7c0f5297e947358c550c29b64d01089a51a5244b13984b55adcadea3756282d9582b0560ac1582b0588588588560acd566ac5aacd566ab3559aacd566ab3559aacc5662b31598acc5662b3159aacd5daacd566ab3559aacd566ab0560ac1582b0560ac1582b056565b2d91441f4df9f847741aa38cb8b69ac8b404eb27296a18d52cc4987330bb3527c4e01c2ce0a299cd3154072638a84b8aaaf8433b0b5597857ba3cb257575757e5757e5757ffd36ff00c77e575757e575757e575641a800a11b48084eba9666b14b392ace726b43541f0e25c9f0473b26825856d6203959c170d693042aafe39ea1ccaa654a648d7221157455fd56ff00cb75757575757e57575757e57575757ffc4796c179402739ac525427d4bb28fdf20556dfb3dce4d686adad0c124ca38990b35634d914558e09d0d35429a9668502a8bfad1aaaf8f8842eea9b9261728a472f28adc2d5055c2b8590570b20b20b20b20ae15c2b8590590570ae15c2d976aed5dabb576aed5dabb576aed5dabb576aed5dabb576aed5dabb576aed5dabb55daae16416415c2b8570ae15c2b8570b2592c95d5d5c2c93de0263ae1a827c8427b8a764a285d24b17be452fb4f88a9e5990869e994958e29d227cc8489b220e5054c8d4f6d3d42823d38e353fc75ae709daf54d346e630469b1c454b477046d18eeb6d65656565656565656589589589589589589423251610b12b12b172c5cb172c1cb172c5cb172c1cb172c1cb072c1cb172c5cb172c4ac5cb12b12b12b12b12b4cac4ac4ac4ac4ab156565656e565656e765378a7f8e08daf45b104ed352c90b53a4c940f7174013d49ba0da7a752d53de894e7a73d1c9ea68dd1c8d6125ac7a6b4a646e2a264a13448119736551fc873416cbbcf0cae895155c7296376aa6891cd0506dc1163759ad45a8b5166b2592c964b35aab556aa8c1731ac6a2c8d3b16a75431abab6aeac2ea82eac2eb5a9b54d29af6940469cc694047671604fa8605d631758175417541758d42ada53246b93430a2d8ad8396a2d55aab5166b2592cd66b516a2d55a8b357562a406f4ec04462e6a4b2113cef7a9b67c6c04c4778e40c8e4d678919214e893c27b5c9f1905b4f238c549835f1b678b49ce8e074cf7bdd8b98e702c91ea195e8b896c8227a9684a918e655900aa263fab1e1de183781b77d6c6a797447541c5cf2b37a6b2a8a77551a6d6aeb13759cd2f75ef2147a80ba9c4f5ad0b86bf5292affaefae0c7babaea4a8256a3909ecba95d45d6a39326784cac210e24d5c26ac5454565708eb0d75c3ea1e56a3d6b10ba946a16a38a6ca428eaecbea002a17eac87cbab5b99ab098f99ebef05a8e09baae0fa9c1c6b13279665a7569c656a1214e9f4d47541efa6664ea96d94a1537b6987dce30d722cb2aa177c34521119a68930d9b348f4f7b948493904d867915270d8c979b3cf9b29fb6a6201b2c8d6c8194e632d1654ecb22aaa4b55b64b11501ed7d1b25146d7b2b87b5c3660de0f32383871767db8dae0991e6f6daf134348738291cd9ea3e9b1a92390a998d69633b375348f4fe18c54910820aafea5137fe4659117ac9548fc8b2a4edaacd0913245344c7d5708a5d19b88d10eaa99d8d1ba459ae26ebb2c88501b439a63d5733a8a4e0e8f9fa6b33aa8fa499a5e563916b6ee8626a6d1be66d4c1151a124ae6c96552dee2cec735e45233f329c06298e4250a9876c2deee2972de916b470892673c99bbbf52b720f0726d25cd98c57b434efbd3b9df9676a80abff00b4fb32a9bb8a9f6c4eb285d9b1cab207bab2cf6161299751be50a2964b35ed963628fc3a45c4e4fb30c8d51cb898dcdbb24bad55c426b9a2e24f6a9aa03def93b9afed2e0aaa4eda3e232054cece2aafeaf0db2794e2dbecaa5d6a8d554cfbd4a059a8c2a200d4402d15734114bb52c85b6245f8a9016a045e0aa423a605b68c8c59dd1708de53e6b2b8c21b52e92763c20fb1cc031c96151c474e3354e92a639ae1cf534813a418b646e9d3483ac6c8a3376481538ec8a632899b2a920712f893a229b1174bfa79b063aeaff72a1df608c69b87ff004dc475751b48d390e23fd8ac3888778eb47d8a675c511fb6e537c950cbc825d83ace89c542e29a0064680ed05b7b30ac235a71ad38d60c5a4c5a11ad08d68b1693160c58b562c45ac4cf0e2c07388274cd09d5ac0bea0c5d744bae890af897d4189b5cd29b52c2849120e6125f1a32c613aad811ae6a35cc2bad8d7591a15ac4dab694266a12350312779c98b289651aca3578d5e35f696312b46bedafb689895e2578d17b1aa2ddb22a7f61002913ca966644a5bbd43b347b652a93b8467baa2faf52408f876d416fcda8febd0bf3a4e23f355372a6a27dd56ff5e93cd17b5caaa4b4b55b29c5ab2964d4422dda3667c71a8bdd57102aaa5740c1c41e532be47215d295d5cb7eae50995733dd2beae2333aae05d5ce855cc57572a35932eb65b504865a6e27b42678da64aa6b5924b1ca84590b3562159aad641cd0a39d8d02aa35c2e563e6aba88d955d4c653a46391b2d8925a176a616a6d4c4d5d4b1a7aa8d5190e9ddeed77ba564b23889a55a92951c92bc992429c2764ae926639f532353aa5e11ab7ab39d046f26286a9f2c947102e905849e29bd97ee9ea18279a4bbaa3e495df75be07b5feee1a7684fdc9bfec38b6f4943ff005f07755e3943c1dff6789fb8773684daa6a065146cd29a813d3c015730bc557b4f4fedcaf1d3baed6fb2250fba7b63c4a3bc3c2e1d1a6e11088dadd2c629a2708248e414c216cb5003ebaa5ec920a76b8323660e2d600f01b137ba9a2b61c57fa8db9a89ac81b2a3a51070e215542de869e132cdc6580534306a9afa76b28d8e2b82df5788e42b385b0c957382e9b8c3317f0c8728a166727151f910d39fa3d23339ab585b5556d3a5c1c6eef32b1f1d74cd31ccd9874ae797ced8cc52b8fe455e44b5c097b5b24b332d53c45bb53b5cea5a289cd50445b51462c65dd3fc53fb2f6747fd8a9da49fdd3fbe3f68f6c9efa41898be79ff00bbc4ff00ab407f068c7df62e1a719f8b0b96d446d1986d754fc2c712fa13691fe2593fe5ff00cd77984fe3c07ecd27c63c45e3ff00c98e6b85d5f9dcadf95bd17574537c713fea5036f257bbf31bbcb6baa71f9956b863b27f16de875060d687c2cb075159565b578763a333c364e2c18d87823bf2e63854718166f06f819db23bb85537ed70ad93bce765aa16a85aa16a05a8b35985985985a816a85aa16a859b540e0f5278a7f64a6c9aaa3e4914a3b989bed93df08fb518fbd59b4bc43fabc2cfe3520fb8df30fbebb71ffe87625e24a5805d40e2013936a6df58fd7111f6a2f345bb6997ea1f11f9ac82cf9ea24819f547afa948bea722fa9c8bea922fa9cabea72afaa48bea72afa9c8bea722fa9c8bea722a094cf4bc4bfafc3bcd61fcce17167c44792db718aa5c2580475c32a3b8c61f8e4b75744ab7dfc2cf6548038af1bffaee09fd8ad1f99c71bf8bc2bdb57b577f8a8f8b87a3ee7d7bc3faf7aeb9ebae9175f22fa848bea122ebe45d7c8baf9175d22eb9ebaf728eb9ef7c2c74ae6b34e393c53fb2a7d81bd927b9ca5f9224cf6cea3f8dbf257f8aff00eaf0cf34e3bcdda69077d4ef011f71ea36fda87c45e295f94156c0388ed6e20716d3773606694b08effd43e23f350db8e2ccb41042647fd265352d6a8a02f8e580c6e6b42c026b5a9ed62858dcdf1b726511953a9088684634bc4fe0e1e3baabfb1c29d8f131e5e3fe62b3c70975e2977840eda40cd27c6195947e2b7ddc31bdb5f6fab71aff00aee0df3c832e2bc6ff00a7c33ddc44fe7ff8a8f8a851f74b1b2345d1362b47a5a92369628ee0b636b092160db3efacf70d52d691a2f90b5864651b3f2a8e352787f8a7f8eabe28b763fde9e3ee3147ed9c5d426f14641756eefac765c3e81962d1dd51714f4e3baa88d168bbcb7bdadb1885d36063d49b0ab6fdca89e66bebae23a1f6bfe583dcdf6c1e23f32f8e2789a7a395914ade2422ac96a1a6a787c61b49c5dda72c579d54b5f24f5afc658c831b6da724a1537116b629388b05250bb3a5e29f070ef351f2c4fd2ac5316b38854491cace12fb4eff0063c59d49bc2efedd2f8abf7508b52d76fc578d0ff8fe0df2b7fee38c7f4785fbb88ff75e2d1cff00151f9ff53cc256485ae8ab1e0c21cd9e9a9ea1af88d435d25508c36a318d365fbf1b99a866d25c3e56e14f68e6a6219574afde4f1278a7f8ea47db87db2fcadf79f7dac99ed9546dc22a71bd5b727d4b6f47432b5b1be46827372863c2373c18a2f988fc879c5b4cdfb90f99429c5d4b8892b1cd95f450e2a4384b40d9331e20f0ef89ae0e1b2d9582c41e445d5acacadcf65b22026f8e27f050789bccab87c865a3e30cb368db78202593bf78e41f934333752416aba7f155e69db6a5aaff00b5e3bfd2e0deff00ff00afc676a1e1437e25bd74bf1cdf1d223e7b5762fb6bb1762ec5dabb5598bb5762fb6bedafb6aed513838c9e29be3a8da380f7540b4ccf791dcc9e1c1be25dd6a1034c8763b906d6ddd23180f11998aa9ae8a9592c8f6336a8c4ea9649a9440eb42a4537b1ed1a92ed574bedc4195a02fd43e22f3594e1aeaa9258582b6a1759520f5b52856d52eaead75957615758baaac5d456215156499eac2ea2b175356a81cf7d2712f828fd93f6879b8e1271a7e302f050ff005e01f930fc2e1f9741e65fec44aa86d07c137fd87186e54bc1db625bff0031c57275070e1655d0b9f54eb69cdf1d2aff004f96ab3d4ab2b56a56a5558495456a55aceb16ad52d5aa466a90ba99d7553a35550d5154ccf92084c858c11c4ff14dec7b7255304d4f16a5d3802844e2a264b984f4e451454df1db67b77e215ad9410ebc6db283dc026a894a9fec77995bf9948a020be2f6b943e23550db8e2acb414d4934913a8e59e511163e28b34d6669aded6b6c304d07268764ef12432c4f30ccc34443a96bfe1a6f8eabc3970e1f81c43ba9283fad07f759f24cdfcaa0f2ede787c4ea6a7ca693b6496a04cc804a19087c75557249715778e91d285129be3a65fe9d31ea0bddd4170b174a29626b9b1cf2631c86761b773b2d491d93dc058c43a0ec9a9a8d97aba4684f5278a6f67fb29c9c11537ce13d1088442214e3ed22ddd918b08751cd85a9ac013426851852f877b1ea5c5b590ee80b3604f50f88bcd47c75ee069a86d170ce0e636338890ce234d1c90505344f86929c6b3ab8ba4ade26ff00cd7d5470ca6ae38e4a7c5f271794155c5a63e186f415a3ec43f1d42917096ea70eabc194d4530644c20d688b255d8e718982a76e2d8bc4de5fe48d8058ac161741bb851a97e3a7f1fe9eeb54caf689a59418e32d9a9a9e61240f99a64a8764d320bc52b5d387b44d2bb4d5316be928c00e81c055d2bf77f87f8a6f601b909c1109c14c3efb53c22110884429c7da56ee6fb601dad09ad4d626b1342946d515ec8ddaf9192164a6929444628028998a7283c1368ef907411383628d8ce9e2c9f0c723cb4166f8b236c67fdb981ee75342e269a027a78918222dd08af1b4319522f037db52a61db48667504904cd14e230d91ed89ced799f1533016dd33cc3e2a0eef250df9658a1217ad42d4d37e51f997e3a71d9fbe969c934b015d25395d25385d2c0ba4a75d253a14b005d253a34b0397494eba3a65d25321494c088d8130925fe29be368dcb5109cd4e0a7fec44362116a2d45a885503ec85fbfd528ec6b535a9a1342014c3b2b3fec9a131aa36a8da80d8a83c46aaa9834d7671c6d9277191d3b1e24a84d35253a4a90637d5173cce1c24a941d51688cee3f7b278942bcd6e1a5c685c2f0b07dc968a9deba6baa38044c9636b990d2c213e929b1fa7425b250cb046137cc1e27f2ff0df37195826816700850cab76ba3527c74e3ed7fb767aad0f2444fb69bd612a3aca3d4c9b1c962c91612278951328521903607c8e9608332c8c45149e297e36b510884fb04eb23146f92985e3c538295ec8d3eb298292ba06a7569728296599b1f0f605d22823d36828480213b50a96265430a9c8c2a28a67d5881c9b19099b263988399679bba0f11f9a8171c559f629a1204f092190c97734b209212d1a7288fe9ec2f75339b3414f93e282f2163827b0a746fbf0f045132da581d73e5d148131f286ba596ccc6e2dab4ce8db1c8f8dd13239b164525e2638367638a74522747529d15628d956d21b2ae1e5ec804aa47b9c58d72782594d6d21efc73ada365eac6a9731993238f2a331171a8a48995058e6cb2095cf9db26a4b1ca1434cd9208180d252c65b5946027a93c52fc6c7b519189d2354b8bc381b8bda95c046f95ad4ea862ae1d435dc3de57d2a42be90e54c0c3109106b51632d8b2f8b6d8b6e1ad4db0329ede21555115632aa54d9e54d9644c7b9309b0f14fe23f326ede205fa304124747511482898c9c1d29f16c53aa289cea98e40e94b66325330329a2669b4472c8da863e47d5e524fc39a5942d47d010410410411f81ff1ff000fee15fecc5336a1e256d4cb148a81cf63299c74e26543259cb9f1cee7491d23246baa1df91c42374cea4d56c50b1f1aa6648ca8a5b873fc49eda5f8c92d73df227cd2a7d4cf7355516eb2ab561f131fb9b2b058b562db86b539adb3dadb3064d257ed38e2816a0a6f1c5e0dd8a35185184df0df6d3f822f19a98989dc429dabea74a8f11a607ea34abafa62bea14cbea14abafa65f51a55f50a55f50a6478853026ba9c2fa85328646cb0b515fbcf7bee873cf717d408fc0ff614d24372dbf5cf2b0b92079857fafa8530238853235f4e00afa723ea14cbea54a8711a672ebe9d75f4cbaea75f53a45f54a4478a520438a5215d7d3a81c1ea4f6d2fc6e1bbc294294620822203ee44a5f92ed41d915fb09edc584e51e017ebf7c804d53789c073208602c8a381e194a5345901b7814fe23f359082788478c30457754c7f7041bb2118e92d2463166c3be9832e9a7c5791ecd8b170d16a16adedbf2dd5d0e5bf2174dbaffe77fb77badfd049e62f78bc8f79677b58a465d8c8fede9ec2151456769a11a11852c28c2aa22bb29597a8a6a7ded8b64f6d2fc78a91ab1ee37b4ff2476262537bf9fed1604ff6f94725dd7b156280400bcde1f64c04c14c3b81b2ff006d22c4dd53f88fcc82e38b33f1dae6c4a5735d2e65454b3e9c74751306bac1cd91936996cd0b0c8f630bcbed774cc275321c38de85bcafbdb7b8c821c8b83480bc108fc0ff006fec6e1ee0c67a187369d87ee25feeee755d339ef9d8fbb21983a28e4d489ff1bc98a3dc477367197296ed5a52b853e461a26deba9c04f527b297e36383d48d254914a1a5a73a8706c74bed894c0676562ac5775fb91bd9e7b423e3f7fabab20a6f13f10a781ff00566a3c4dd76d64ae4c9257265ec3db4fe23f336d1f1490748e9217b32873d573e7ae735bc238639bd2cd388d3ab63d4d724d2895b4b47a8d6994e8fe32d48d8b879ca85abf4b1087a6c820bffc1fed288561e82ac15905179ff61f6aa8dc20ae95f8a8ecea307f0e49ae9cf63d6b8061983e4ac0c8aa789b34551381a3a4f8a924fcda67eef527b697e37dc3df24ad4fad99abeaaf09bc6421c469257c4a5f92dcff00613bc38f6b241892bf688c93459354fe38853fe6d252b1ef8a9626a9630ca98c260d9beda7f1962c2fcc611a7d2d3bcb218186464123236c51c2d869db3cf153ce1f4d4af41f180e7b4a2e6959311a2a5bc31410b7f4d406d8ac562162b158ac162b141abff9dfbac562b058ac562162b158ac545eeff52d153ca5d434cf6b2389ad34d4e66e9e9f5ad1214d4e1e69a9cbb08568437e9a0bba8e99c7e9f4c9949031a238c20fc8c9eca5f8de37902b2744d29d4cc5d3343e2537bd345b97ec152bc69ee62e47c9215d0ba8d8499d578da905a7685503f22309a36fd53f860c954b2461ac9aa228a1a9aa79a8a8a86bbabaabb6a2a56bd42d6a95ad52b5ead1a8aa0ba9a95d4d52ea6ad1abaa0b87bdd250b50f0ecaf96fbfa0acb7e5ff00ceee57b0c8fa6e427171628fddfedf5b55abd5d5aeaea90aaab5d5555ba9aa4c9eaefd4542ea67c9d51501a6b6ac2eb6b14d5154190d655be587a8726330649eda5f8f152056564422378fccec396eae810879e4ef18b960b06a1b738fcd42ab03529c77353a0ca56c6d08bdac6659369fc45e676dd7146fe352b2e6485d24c19696969df3c94d03a74c192d2ba6b6e20a7d79a4a27450e2b40e9cb0b843c37fa0d40ed75757575757575757575ffcee5757575757575757575751fb87bf02faa85ae79d837178a7c0b628d991929a58dd242619d8c2f95fdf2cb609b4ef31c196851dbaca56dd49e24f6d2fc714a1cf9a1cc3e091164811b8590bc7e66e677583560ac57711e8933c4171747e6a10c7192b20893f8b44bea923de1f33d44dda3f8e9fc45e65f671075e9a8a293468e391ee1ac5f48c91b43c3e37369a8c3f568b535409170a6b9aabb6a3c665235c0499b69e8b2e91a878beeae2fcee072beebff9cf204104803d0d208240099ee1ef6bf0ab81c20ada8d40a32d929e3932a7a773c54712764de29bb698b84ad7835323c89299c3a1a5feb5265af48eee9149eda5f64edbc8736235f2c45bc5d8995f4f22ed708fccc8b884ccfd2102f007a2e14677a85c51ee358d600b72a38da553fb62099eca7f0d760d32870fb7774703934c4d5ab1a0e84264b13039f0b9d9437ea589f346f0268c2d68d34401ec640c73c828143c100ab2000f43b128968740c614e70bb8c4d824763238bda3338e176fa3101620f269eeff0052d0d2cae7d052bd359035bd05209994348c98e8102969c3a2a782373db4ef6b6929839b474c247d2d3388e1d4c0c74d0b220d8c2d4cd49eda5f8e46eef0aa18249440739232c752d3bc319ee98ef71e92f791102d62c82b95bab21b28fcceabe17495d3465913553fba898667b6273146fce2a73b1f8bf5fcdfe7fd6cac15959594ccce3755b219a9f8ad13d1aa1326d1bdd1c94d22a57991dc3dc618df5af91cfd68436bcba38ea63b870772bf2b2d93acbfd7f3b3cc9eda4f8db799afa498291c5b3094a74ce4d662c8fccde4ac56eb7590e41623d123f0687ddccf33799294cb56385c6e5f4ba60a3a1a762becea98d8a6a89dcfb3dcc10abc8aef5dde8b8f5ee8e49f1dcb1ae6266ab9cc6d806a0d41ab058a7d344f52709a57a14b5b09a97f12c60383e7e254a289bc4e9dd1d3d29cecac8b559108a9720b2911cdc1b084dbdb75badd772dd6fe8b95772c9eb27aca452b0c89ac3198e5a860a7aa1a6d784f6b244fa0a37a7f088422d376297c975931f9fa31085f9037f434263494fd9cd3b1374e9d8c5255b94933dc83d03b029a55d66b502cc2cc2c95d5d5d5d5ca255d5d5d5d5c28bc45334a6b82087ac8040a1a505ad6b4228a2894e704f99a11765caeaeb259ac82c82c964b259acd6a2cd66b3592051e4c22c1c83ec5b52426d4b4a0e0b24ddc49194e1ea1ba0cb730d5da164513753d43636b6b1e63748e7a2564e593902e5fa09a51b238ac8265deed19d68ce8433ad1a85a150b42a10827469e75d3d42e9ea53686a4a968e68d74d394c759b71a6c049ca461150f08551085485d531752c2b59ab59ab58233846a5811ab623529d5256bbca73a4288280017ec4523d74f3234d32e9e75d3ccb4265a13ad1a85a150b42a1182a168542d09d68d4224837e4d3cda4f2d95dab26a12e28559028eaf5181c022f2bb0a2c288e459751b431982bb42b95b27ced8d3eb13e67bd6d6babababa0815b72164eb2b17ba9f8694c6b636dd6eb75dcaeb1720d58ac562b1459716734c2fbc41c99204d70283bbb22e0ef0db2b0becb069583426d91684ed947b87047ca72fd1f3c3be15bf3badf9dd6fcb7575342c984f43246814d57e4d2ae112117059859209a135ce6a6ce9b3872165772bb0ad3565b5df33589f5613a691dcb141a8a3e8ba056fc9bb98a85cf5146c89b972b72ba68ba0c685fb087a004e9226a92828274d9386532faad35c55c13a7d3b1c2481e230cac9108e563325920538a61dd48a2f053d7e9ebf47cf0ff00895d5d597857e561caeb7e7e102aa29a3994b4d24281079057479d906a00a26cb2574d91cd4da84d9439022fa8f4f9e57f3b2b72175b7a415745c1414523d430b2117e575b72dd3372afc82039d9574d340c9e399f50e30dad03432a40523b5151359aba4c5a80bba77cac3c351e14e4386bc23c39e9bc3240be9ce4fe16489206c08a7f9fd3f91f3446d0795b057e5b2bab9e5da15d6fcacae396fca6a463d4b1be240ac82255f96cac85d1e570b2592253677b536b1beb09c11f4d3d14922829e381785b95e05c2bf2b72b96a8e46bd595bd5c46f1aa8e22ea7a98f89764b26b97ba3d522688b6b5d4eea9e255339e1b03ab20828c47e973dac5255b429247c8aabda9e8152722553f6c3b72b9e42cb75badb9efcac395c2bf2baf2a5a3053da6327d0104423fc775740a2e28f2ba829659953d2c70f2b9e57570ae16cac15b93bc26caf6a6d4213c6839a50e6e687b5dc0a6350382ca533814610e03457fa2d085f48a70a0a48625b04656046a6308d5846a9e53a591de8abf6272ba7145c99dd282821cf7416dcf75bf2b2d95c2bf3dd5d3da1e27a27224837e410449572ae55cabb964564b2f45d5f90bf22a0a696754f45143cafcb259057571cec15b91f587bd6ac8b5a45af22d7916bc8b5e45ad22d4915dc7f8ab7e3c95d39c8b939ca9a1704d6a087a770af75bf3b2b2db95d5c2b857e57e5342c984f4524681416dc8f3bf33e8b0e7750c32cea0a18e3e44d979fe1b2b2b23ffa4ae246d0ea2cd5dd228e92572829991203901756f51082b2b2b2b7f0d95f94f4d14ea7a692041dfc1bfa2eaea264933a0a16b57e89b2dcac42c42b0560ac1582c42b0560ac16216211016d7ffd0557c5ad1b6898994b1b50672009422721180acacadea22e82b0560ac1582b0560ac1582b056560ac1582dc206eaea7a48e453452426eaeaff00c0c6be4741c3c04d01ad57286cae55cadd6eb75badf9595b9591b2ed4d3de3ff003dd39e00ccc8a3a6c80a50853b02d3685656fe3779d9582b2b2b72dd6eb75badd6eb75ba3bab91ce6a20548d746ef4dd3417ba0e1e98d6b1aaeade8b8590570aeaeae56eac559596211c6e55bb58ec87fe2bac822f45c9d2808465c5441d9febf99e9b6b582c5595958add6eae55d5c2b857f410afc9ed6c8d9e80846ed392c964a4e1d4af4ca6646dd37237088250055959621587a2e15d5d6eb75bab2c51b02e29bed747bea6281b8e775757575757570b2592cd66b32b2574e91a166e2b4cb93581a14513a551c6236ff3bd36cb6560acb75bab95759057e58b8ad22b442113504d29c8f3b95939665647d3b2c1a508988c2c034dab4dab4dab458b4635a4c5a6d5a4c4e644c6deeb75895884714f4cf0ac8c4d56902cdc16b31091a55fd575985acc5ac164f2ad215a20a6b1ade6c8def51d301ff8596722c69418d5a6d5a6d5a6d5a6d5a6d5a6d5a6d5a6d5837d61145794f918c5d553aeae9d7574ebaba7f50413fc7efd152e2d1ca724cb1f82b229ad1ca4f637dfe9fde0d28c2c5a6d45839009b1b4ad1659b0c6b103d7044cd2fe63e86fccae757f9c272af99f107cd2c8a03752b40710acacbfffc40023110002010401050101010000000000000000011102101220213031404150035161ffda0008010301013f01d27e7c93f067e6cf831f3a7ab1e3c0c823e424409595502698fb7c7652cec6477d5761291f1a43316418b317bf3d18662cc59898b317a2a64a4fd0568ba76627050e0fd344acd8acc5dc48c47252a4c4652a4c47c329e4c6c98ad5381736ab4a5a48cb9e07762b522191c5a34cff00c331b933331b9764e0c872ca7832194f06455cb92994643b2aa0ccaaa9154663aa7481b8266c862b2292a238171b49249249ec6c926ca7492744c6c6ca792aa61122bcd95d8ac8a4a8a7943efd08208b55a51c8ed3662bf16e36774316b40d8a10fb8f663ec2926d55d147b18aecf565c98409b12fe0d3569b21dd0c5ad231ff004a46f78922f55e928f6315a2deba2b55ac69e85aa5662b40ca856a44e07d0426547a249d9f472b488578b41176a6cb9b2ddde2d04596aed264644e899045ea7c1490a0a862291dd5a6f02b2bd247a92aee53054b8d6043b41046ab68b45a3c28b4102d67a29da345691b1310faaca4c46ca1c8e49da3c18208f0659041c902d5f4e36aaae491327acc4c4ca5f057c1913acf4974315d08e9410761f3b3b3f1979133e32f9b8b891f1dec99919128944fc655ba7843a9bd60817d0927e6c11f2a7e3c1045e7c9ffc400251100020202010501000203000000000000000110110220211230314050410351226061ffda0008010201013f01d2bfd46be6a5e8dcdfa76597eb51514576eb7a2a68aedd97f22cb8a28a14dfc4435150cbd3c0b4b2d165a2fd1b2cb2cbd2c68c47abe4b10ccb9318a2862a3a468a129b290f82e1962a687c170c68a121a8454b5c95c43284386642f0755b8bd3a4e98e93a45345a32e4a86509997254be4e91219d2569624dcbd19978312f918a54538a28a848e9a2a1d5d14514522868a2ac68a122a3a50e7a63295a6461fd19f0cfc14abec6238fd3f9381454b9e4e4e4e4e4a67885396aa3331434d897f88871ff07085e47538994333fc147e9fb0f4685150e14e5a58a33e44232128625a78d313211919bf029becf2723e214bd117a7e8f4b1b843117189908c878d8b7431a313f4a287aaecbc754ad6b729d43e0b1f6ae6e1f1aa9e93a75659738a32453108fe87e347adea8ca311f997e66e2a2cb2f56cad178388b38f4ee781f79a2cbda98931a1f791942435c945697dc68438428bf46b4b97c4d42edde94518ae0a1a2bbc86868a1228a1e95db62dba9f6bcf72f550bd67dcaee557c15f0ba95d1e61a28a28ad57c2786393b62c5238d9fce62284be75fcaaf8d6596596515ecfffc40045100001020304080208040405040203000001000211213103101241202232335161719181a10413233042527292406282b1a2c1d1e1345063b2f01424537305836093f1ffda0008010100063f02d1e4a22ee2d511a310a3a3454522a7ef0e953f053d097bc80ba4b82f98a93405858ebe5a7c17353d295d2eca23461c7fcef95f253bb0b69eea26f9e9f353510a55cc5f24dff3b8052d0836974f4e5a12f733ba2d335aedb38adda83031bd5476ba29fbe905fdd7f7536fe0242efeea63dfc82980a8a82f805014be6a02974f4e74d097bbe574940d743829fe065fe4dd6f9ae5ee24b9df3d19a97b988ba0eefa32ff00223f8689a2e5ee657cab7c94bb7bf83b479df4592c9502a5f1efa60f1be4155562a979d3eb7d2eaaa474219fb9905ace6b4ad683fc1701c3dc4ef9ae5a507cc2d5d617cd4bdd41d45114d088cc46e8f130d1d697317454a9fb29e8b47e0b89d1f0b82775d18bbfb050677e377ccee4b80e014fdc4b4356f9c82d5d191815ed354f15fcfdef16ac428b35255b9cd750a8e8c0cc70ba4a04684af1f853d21a7a8d1e2b58dd87659c05d1cf20a274f95f3d0d5d62a663a14d0d5a705c0f0d097b88850f7f95d92c964b2592c964b2592c964b2592c964b2592c964b2592c964b2592cbddca0a27dd4b43968482d94e9354048fca56b6a9d0d5507085f3d3e7fe6d9694b4a0d04ad6d080d62a06bf284c9344b8aa35394d4b58702a0d3fa4a84709552aa540e6845d13ffe0f2bea9a0ed7159aaaac4f05027f485f28f3524d5208faa740ad76cbe6174c454b587340ba3ca3733aab46f0bb81be7efa8a9a14d3c95153dc53f193adf3af0b9bd536e7964714325f28f352bb51b2f98d1345a3a254ca92d6d60b53d9bf92a636f16dd65f4dcd4f7b4d72be151c3ddd3ddd555555555556d2aaaada5b4aaaaaaaaaaaaaaaaaaaaaaaaabf8495f29295cd89808a6f5b9dd2e90837895ed0e37ad4d51a55c439a8b8607f14d64630108dc13a516a8b0a8da1631d1a45542a851b278e87f0f454545454545454545454545454ba8a9f89d6b46b7966b682a84759b1194545d21c13700946a536e217cef52d51cb43544561b5b311e2d9456a99f0cd514e4a4549d2520a05b0213a281ce2a7d17b377e92835fecdfe46e712b56a80cd4eec964b2592c964b2592c964a8151aa8144417b530e8a46ea0540b65ab65ab65ab65aa82ff006508f35ac75b3ba81502d96ad96ad96ad96aa0540aaa44c518e182a0540a8164b2592c964b2592c964b2592c94e8a70828a828bbb23f0378040853ac229884a254c4b82990070550a5359283ce1e59a85959c4f17a18df8ad38f0e88b2d7c08c9398fa8a21024f5401c56678542aada2b68ad6aa85a40a8d83f10f94a83da5a639a2782b36c23ad71b99d54545d45001c4f4539292d5683e2b5ec2d3c04566b358bd5b8378ba4a0a4b76e3d141c1c0f30b34c7f127f751e056038b1720a8ff00b5483bb2a3aecd66a8e547765b2fecbe2ec9ed119323e6add93d5790a41fd951dd951ca7159acd66a8eeca8feca78c78269f1b9c27559ad5b37f65310f1bb55b89617b5ed770217c4bd8d9da3fa053b3c3d4ad685dac1c835b18dcce9778dccc2df15ad3314d0d1127241d6a7d50f3506f742155b4e5b45554dc4f26ff0055f0d93792c76ded0f0c9017bbba7b3815eaed5b10882f88cae8bab75a05aa60b0dbb03c227d19ff00a5c99eb1a418e80b87d4a2cda4d6e224b880a392818c4735a9687f508a6595bd8b7d63f66d199dc7feaad0c6326b0c0410867cd62e705aaf70418f68b40643089f6b9b66328fee9ebd64e1c96cda7dab65fd96cbfb27433baccf35b0fecb65fd96cda7656d3f893ed066c879ab6b42769d1562d01e602110152d3ed5b369d95988384f317b0343a006416cbfb2d9b4ecb080e107466392e7085c4f34c6d9d9c4b848e4b5ed3ed9287f34d0732b55efb33c43907fa561f594970c931eeb2f5988e10d866b59cd60f959fd54cb8f528748a63a738a81316266878dcc02ab1dbbc59b7cd43d1ecc7d4e5ac629bd420b9ac309a8daf64086808151e6a1c9347189b874463470cae61e684135dc6eb473735070542a51542e53638f82c6cd97095ddee6cfe25509ae0445a4143e58d14635554dd6983c5617da621cca2628284738aaa93bcd617bf1708940a7754e3ccdcf98e4b24f98aaa8567315bb2870bad69b66f68eabf5294210565319aa8550ace6288c609b0e09e9dd4dc6042c569698bc5542053791554031c2304c73ed314f8aaaaa1c9344688cc5532045c6ef147d4b0981c315511e6a2e7c7aa915b41321ad31442e7f182013f9268e0029fcc5168136b444ab1b4e04fec81e2133a2b377092078af11778dce40d9cad0851778c0cc2a9553dd54f75aa20db8f42a064781530decb659f6ad967daa8cecbe1ecbe1eca8cfb42d967da151bd951bf6aa37b2d96765b167f685b167f68b863300b570f654695bb62dd316e2cbb2dc58f60b7165d82dd316ed8b65aa8d1e0b50c54ccd48356c316ed8a763647c16e2c7b2dc58f65bab35bb62d96ac96a981e42e9b587a85b165d82d9b3eca8ceca8ceca8ceca967d96cd9fda1519d97c3d9519d96cd9f65b367d949aceca784271842ef14704b8c0aa9508a9ccaf684c4e49a10b9ff4a8ab4b3c9c043ba79260134f189f35e91f4840ab33c959439ff24ee534e678a29c8f5bb0b27687c958bb8395a43aaf576a2920690430f6d06dd16cf28152679a933cd6efcd0219ab9a8e1d529ac6d9c5ce9011507d840cbe2aa1eb2c30839e25bbf35bbf35bbf35baf351f55e69af708131978a69e6a07d22ce3f4944b6d6cde7e500a8e26b7f494e736d810dac015fe21bd8adf8fb4aff10dfb4a11b691fca56f9bf694236cd11e2d2bfc4597dae4fc36d66fd5a34156c0dbd9021d420a80b7b33c835cbfc4307814236cd11e2d2a02d81e8d2b7ed3e054eddbd8a8fac1019e12a4f69f02b5ad6cda7816b97f88b2fb5c9907874782f14e6868a9cd1c2c12acd6c2959c7c53b0d9ecf3506b462349ab1b3859e27a81605acd829b4775b28da42934e7b2786a833662a353c4a6f4bbc5142cc4dd9f250374d350455ac727415b0f95cac79ff54ee462aca1c3f9af49f045a9cce0558f52a1c910784139a888c62229d75a712179ac5c5ab12251067784550c23905e91e90190b5841a1c62bd22d44b57088f151c34e4b66dc35b89d02dec80c369068a39a9a6186142536346b47ee7fa2c608736392b5b590c40601c93dd20d2c92d9c95935db2db389fa95832d06b86c5c20861a7f74791548a0c7d986c42e2ad043da39863d9482637e3b368314d64259ab0c22103053d568a9564e60d892dd84f26c9add4a8eaada164cdaaf15acd000e09f01f12f476b69eae3e6bd25d0d6c0437b2634d094d8097ab6ab580d77345ac3947fa04d0e68c352b56c838100c50f6587a26726af156a432d08c468d4db46b6d08b518c80ca724c7faa7c690c2b1063c080f851db22d35f655945afd4cf0d5595ac2766e8c846216b36b309a5a2180fca984c622b16c51181ef747fe64a87a11056f6785dca4a566f10e5542485de28a7b8e4d370f047aa6f8208ab5e6e0bd27a85e8c79c15a262f483d114ee70563f52d774fa2c4d32253c8e081714398b9cdfc90fe77591e450e69dd10e73bdff49fd96aaaf9aaf9acbbaaf9aaf9aaf9afeeb2eebfbac964abe777f7b9ca3cd1e480e25381cd31ae149281cd8565880c903c1c14b3922d750a20c220c11e89d44f7013c49cd01b8b1c3cd7a3bc8121826ad3a2c228c74158986d361d97e9fe69ede16904c3a1b5e6b6bcd6d79adaf35b5e6b6bcd6d79adaf35b5e6b6bcd6d79adaf35b5e6b6bcd555a16f1fe5778a7f429ff0048b99d0228217733356c78c3f9af463fea056bd111c0ab6f0454958754eea827438200a07309a4661186427dae6f18c1019044203c2f08b9b251daeab77e6b77e6b75e6b75e6b73e6b73e6b73e6b73e6b73e6b73e6b73e6b73e6b73e6996844231978def3f994fe08bee78fce4afd2ad1f997415a0e4826ab487145395b8fce13c7fa914ce4e09ff004ab502b89591193a1e4a1f915b8ff5137c2e1d578a70c19f15bbf35bbf35bbf35bbf35bbf35bbf35bbf35bbf35bbf35b1e6b63cd6c79a0d800b58929ad1c1147aab4e8ad3c11eaacbc11ea9a85d0e12569e0acbeb0adbe94e1c81568a3f0e68f251e0e09dd4dce1c45d1a4462080f949083cd0b66a4accfe65ce29cdd0177ea4d6b66498221ae6801b1eaa754e7344404310add31e4a6c510028100a325ab2826da4a07c9307d5fba0bc53fa95f50371fa503c8ab46fe64e170c5e0ad8032c77156a7f347c937a02bc5aad3e850ff5421ffb3f923f4ab7fad33c2e6af14f73b8a169f09e48da48b408a2f70b389329510c61b1ce0a6d0ace361ace7420a83b2c2c60206d14d631a2988c5491c10c22a9eed5835345c1147aab54ee704eeaac978a6a08758227892ad21c55988e72568e19ff0054f3c0009e8c2534e3cd06f1213faa675bdb88470882680814d6ea88f00981cf73a3c503c2699cc680b862f9936d0d9ecfca9ceb4612c7368325687d5b445e5d327faab18c03ad0c7fe782b26968a62f3586cd8d8f18a364d024253a268223aaad1e5b26892b47426d1113aa8968ee53acdac22d08dac82f51eadd8c4a39269a4ddfba0875bac9fc08bb1bcc1b03fba1eacc61c939bc422ad470720ad7eab8a8f1251e411ead4ffa50ff00d8bf584fe817a4f272b3b9a8754e06030bd59eb021b592910e0e6c2344f646128f659538a68936755676913ecdf8f8c507366deaad08f88510b4983b1ad245ae67dc9c011889a455b59388d6a26c601c239a17147aab5e971563e0a7c4210410eaa027556c78bca64e7c9168e29c1ee01ea21eb6a214faa7da7c260d67f5569d53078ae664101cee08764d2f0e3948267ab34889e48b9cec5895933100dc2605c8b9f6a1ed8500bdff0049fd949d15303b2a792a0ecb647659a988f50a4df25454f254f254f254f254f2541d97fce285cfea82b3276a134d3f9884eea9ae1918a255af55ea60e886c63092b5faae2acc7e556bff0032088fcc13fe9fe6bffb50e6f0ad3a05e97f52b3b9b751bd951bf6aa37ed546f6546f6546f6597659765b2dfb54c0eca8deca8dfb551bf6aa33ed592b4818fff00cb8f55684d3095047a2b253a454ded8ddd0c54a0898d74224c79050b390fcd356ad6b8c2324c0f3aad569d5021b19416370901aa021223c340f74ffa904d95144008de4b443a28b5e6ab6fc940b847a2da1f6ada1f6ada6fdaa389bf6ada6fdab69bf6aab7ed508b7ed556fdaaadecaadec986d76e7fbdc53b9a09bc209c783c27fd4a09ede460ba84e56a79846175911f2a71ccc55a729a7b8c861fe69df503fb2f4777e604ab4e8bd270889c41081061705e29f08422b2551d9465d94a1d964be1551d95476551d96d0ecb6876551d90697ad624f541a2e3d539a7392c56707fd2d9ad6af352214a69b1a4457ae9b91bb0588966e2b0b8dd3d20ad7adc5d96571d0f146d2cd81c1bf9a08ffd386bf0fe682731e20e6981470824ad51184eef0bb55a1ce34506b0172e6a16ac0d1096b450368c0d61a1c514d2da45dfbdce5e3703f955b748a77d48754d6f188f25607884e56df55f1c461d536d0c7813e09feafd6c1e63ac5607c302c5815982e259f2c6489b20e8e519273de759f5bc21d516002189606012ac53dd904fb4739b034e49a6d204f25a9b6e90561678d98c9ac2aa06a80b302558a6b590a4d144e1989c512369904d1a07ae90f04349c8dd158a2b3521a22e7974a2a0dcd31ad5e178454fe6568e18621a764c66834b9a1c68099956e30372747c15b5b600c79a4f256f685b82367c7926b0304dc015eab08c9ade4142008c2209b8c969aea12802e70761848f14c0e0c0c137389a04d7360e1377922c8b4b98c988ab3901b5fee37390410134f61708c2026b0b9348a6258b30bd1f09075b24e85a4bb287bd08754f3f9936d27ac9dab2709714fb3cb0fecb2324c2e837094cb4ff00c6e8d7258a00f8a7433144d74eb86724e696f75843819515bd97912b28fee85e7ae9764349da1e3a66cb03896addb9195735115e4aa513537bc8ae12a662a0eb36908b18c6861a80a3ea9b1e3045cfb36b9c6a485808d5e0b0fc34828b1a1bd1177c473589cd05dc545d62c27a289b1647a2ddb506fab6c06489f56d8a0d68809c85ce4d4d3c9165838b4e39f351b53004a932678cd4ac993cf3537407928cdc79a3a7159284b445d1362ceca764c2b72c52b16765b96765b9b3ecb7167d94ac6cfb2dc59f65ad64c3d56e58b7167d96e6cd4458d9c549a02b49d3fa5c7ae90f04349fd343c748f456be1fb691bcc028b5ce13c8adeda7dc501ebad3ee5bdb5fb947d6da43ea5016d69f714236b6b0fa8a95b5ad3e62b7f69f715beb4a7cc56b5ada7dc56fad3ee287b6b5fbcadf5a7dc55997924eb4cf54510a7643b95373a0a0d24ce28c446486393545d663f9ad56c0f228bdc5a40ac1142e37c2222a77522b570f4443840f0378e88754ef6afa9f896f5ff72de5a7dcb7969f72debfee5bd7c3ea4315b3a069aca76b69f72deda7dcb7b69f72debfee5bd7c7ea40b6d5c47d480368ff00b94d06b442e3d742776238a285fed1ed6fd460b7ccf02b681f14f0f7b6068004d735ec6b4cc466bdadada3be9680b6ff008540cfc150aa15b2e547af8bb23d13ed1b870bb9e84ca334e8684be6462d23aa0436616eddd902442704dd599430d9ba31e09b114131f3273431c407422a0fd4d589502612c4a3809544e830c864159e2041d691ea9c23344e5c5052b371e887fdadb7647fed6dfb269759bb101f2a717e284250508c21c53db88188214ec9f14351ca6149a56edc86063bb3549aff00b42d7b2b423a05b9b4ecad220b5e5d9c16d94d11c4c8ce2053c50d5374099e143aa22d3e65698b6a2422703808413ad31988618040824922304c63da4625e8d6703eadce2daa6b5a096c1606021c4c932cd9b46b029cdc04c0d4275a98e28273fe20e814d0e6915ade51eaab4aaaaaa82934fdcbe54d8acd51c861716c3fe715bd1f69feab7adec56f87d898d33c22110a8eee84b8e6a9f0f15b39aa64a8a88497827b596af6b442016f5fdd6f1cb6caa94675bc5d0631ce74720a184e271a41386071890600454ec2d3ff00d656eadb6a3b056eed61f49562db4b278662898b609fd608936369b67e029cec36b8de26dc3458e1685e590861a20df54f11e2d2a0d63f006c045aa22c6d66df90ab30e6969d6911cd3fe94667baaba9c555d4e2aaea714267ba137774267ba6cdddd09bbba137774da99f143aa1d1199a2a9a715534e2aa69c554d38aa9eeaaea2cd67442bdd67443aa7bbd55a8d63f015eb6ceced309cf0156a6cec6d087428c2b0da59da37ab0cd1b2b5b27b729b0cd35dff4d6a61f90a186ced4bda43802c2a2db3b42ee06ccc428da585a43e82accb7d1ed0e133c3646887aaf47b488e16457abb5b1b76f3f5653daeb3b4f54fcc30addda61e38550dc51ea8e130c555b456d95bc723eded3ba67b7b481233e699f523d55151538ad9cc2a6451d5c82b49669a45111c978dd48cd56ef04eb61957dc3c0a969507177daa6f23f4ade7f0a9bcfdab79fc2b6fc96f3f856f3c96dff000ade7f0ade1fb56df9281b4f25b67b2db3d935f6662d31fdd3be946ed8f3bc20a486a79a8fc39043aa1d50e88a38f58f11254b8df48a908141782f1441b43d96f0f65379eca21e61d16d9ecb79e4a569e4b6fc96d9ecb6cf65bdfe15bdfe153b53f6ade9fb56d3bed4f788e130c9147ae850c4f9286131714d1f99aacfeb4eea5551e5778a03aa713482791c4212e2bc178e8f804f6911885b2deb05aac1d6105550b8df151e6a682a2774bfc10ea9c6f1759f577fb93be954542a8550a122a884950aa14245091aa1d50e8a868a8a8a85515152ea2a2f043aa77537f8fb91c90082039228f5be6a6edae49f3425f12b3fad3ba9d0f15e08cb20ad3a8420d39ad934541dd64aa3b2aaa95e011e6ac84f0892035ae8cbba2780448b85dfa8218b358455a84d59dabb00b030c4633826becbd5fabce254408f250385d68610e48b6d082e06a13b0367c02d51184d453c08ea5544092b3fd5fee29ff4a2873b888cc56e0826e2308980ba087543aa1d2e889a2e79837462d310a282f043aa384c3593e3411927e2c93e3f0a8d0f24d85498289ac4362a0ea8cd184ce4acecc8188d446aa078a75b078c205145cec4c261cc26f437947aa701f0d54a1dd183232c8840fabb4fb0a771354ceaacfeb46b555551d964b2eeb6724754d02b488757820bc178af053bff0048458e8e315831498ff1700a1eac7895f00f05b4a64e81409e2102ff0059eb32c2249e586d31678a898c6064d1c241692d1106554c69733d643116c53dae849f865d535d89c4628c7358a443b32aded30c1ce1a8792b67b9b06166af354118704e81b681da881e4bd8c4b4fced56665f17fb93fe9d2178410ea87543e9f77e08754f3f9f2ea8f7459ab344636ecc218545ae64a12c0132386463109ec756b0408f884792b28b9ad9ab0b5a00f9a05b4a6b2731b356b67989c14e00c0a17147aa91223c149e57c27ab54d8cf0256b31fe0f431b35ccb5acc1567f523d4ddd1782f15e08f40ad3a84dc6087c382e505e374d549bbf4841df30f3443c6511352b36f64f0345cee0095ac16ec225d667ee5ba8f5722d758b6078142c9b67a80c61897ae165af5db280b4b012fce53317a38d4a0c454ac58b625f529b3f896edbdd1f6267f9cac3676501f52908277d2b3552aa554aa9552aa554aa9552aa53537a2a9552b3552aa554aa9552aa554a3d2e8bd863c9c9a1cd74b3c680f571ea50b4f57adf52f5beab5bea92dcb7ba0e6b0887c38e4a381c27180b4302b72deea381dc26fa21a8e80a0c725bb20f16bd0d57c4678e6a0191ea549be69e3e5451eba13685b29b2cc2b3fad3ba95522ff15c93fd5825f094427631074a2105e17554812a8aa02f00ac9dcc85ff00d7fceef0179ba06864b55ee82c4db57d54ed9f0430db396f9ea76ce5be7adf3d6f9eb7ee5bf72df3d6f9eb7cf5be7ab373ce271c53f14ffa6e6e1c30ce2b64e8c96c95cae1d537a5d31d9534663b2d4003bf35c7a21d53c0b67556fde8fb77adfbd6fde87fdc3a256b5bbe0a1ebdd150f5ef8a8fae7aff0010f5bf7ad5b77ac26ddea76cfeea64926a4a28f5d20acfeb510553b2983d948af15e08f40ad3aada8740a64aa2968782b1261477f245dca171717c23c96f3c91319819e689b85de214020c6c314d16e611659889135a8282279209c385c1a0c3328bdc448c1782f5b2c3182f592c24c9597eaff714ff00a50bb359acefceecd669bd537a2cd66b359acfb2cefcd668f443aa706fccad08ca324e3c91762914dc644f3585a6792b1b32f6e27191507d6a8e13b23bac14962ea8a36ad70c305889c4c261cc21d0de51ea9ec738370c21cd1c16b85df99a8420e8702a7647c94ecdfd951dd8ab3faf427354522e551e213a9df47d9bb0ba355c2ef0bb5ad183c62bd98b47f92c2d6b1be6b5ad1dfb680454a671513ed1c30ca11a417ac2c80e25038212ff00c63fa2f4ab4816bdd490a0ff00857a4bcb0b62c809051b4030811d9013bd68a88ecc1525ff00ac7f456b696b08096cc106c0e297350c33ff00d63fa2b1b22358540e29ac86b87924000a662103ad94334efa6e022226e222223404489dd0888f0b8754de97441054490073d188208512602e3d10ea9c5c3e34e044a29ecc1abc700a764f64cca325013756898eb518591f900567690ddda35cb1d988b87e50539cf69070f084531cd98a1585a227305a116013cc7056accc18a0e70f18410b8a3d6ed4739bd0a83dcc3f52d76b87d262b78cf1928cfad559fd57714ec663132e4348091e7a35542bc02b46973b0884046e9515259a02b0d02ef9412b59a5470b8648e216a44630c4a9687c56c396c5a5635460c777588b6d386d22705a1fd4b767c940b5f0e4b65fdd6c391786dae23f99620db48fd48413be9b8445154f7460266ba138182c388e2f94224801e728cd6a025bf309a66bc71430015285990e3690d86889f1e0a7e8f6c3c5a7f9a0eb1b1b57b5d305900df1e0a16cd9e6d33d1d5d5e925adadd6ef0bb11f5ad3c9c86236f887c5882030bcf38cd7ac6faf6f26bc417ac6faf138e1c42054ecc95277a4e0f9318826c1de905ada30b84141d64e8754f27d73b14a04841fed9d010839c0a886da31df335c810ef48c5c710583da11ce0a4d4e1f2a28f5bdc63c906c408e6516d7a2b3b46b9cd3c8f3567f5aa691121f98649a1cec44099e377f454555c6ff00ad48a28caaa6a29deae70cd6b3991e11511ceeb4fa5dfb2aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa084614556f9ada0ab796c484cc589cd18a4d6c21c140b830fe70a36581965ac71340c640ad6413751a06d0697499cce6e29f8ed71b8b08697360627f9275bfa836767e8f63ea817710a603bd76be273a0e3fd543d118dc2368bbf9277a45afa559d9932f6767377509bab6b1021b38bc63fc96b8b6eb80ad427c442fda0b682a8596575555555555555555555555555555555af87ec8a3d545a5a32d6305303ba7e5ac73595cc1c20acfeabf8745554d0e3a11c2e76500b56ef00ad5cf043729af8fbad62ffb94acc9fadcab2e0241401f06a7fab7e0647245a6d1d0286b15b656d15b6e5b4e5b4eeeb6dddd6d1eeaa554aa95b47badaf35b456d15b6544b8c7aad5791e2a02d0aac79e96bd930fe95aac36678b0af63e97eb5bf25aa859d886bbe76ba2b07ff2cdb736426279a018f7b18f9063721e144db3b0f47b67c3e0709236d6e6368728c9bcb4e2d715bc7281b472a9eeb6cf75b656d95b456d1eeb68ada3dd6d1eeb69cb69ddd6d3bbadb2b6cadb72de3d45cf714703de23cd1c16a7a14d0f915a848e945ed2caced3c26b76d69ec8101c3c65759fd42e9a741ae10309e7a35ba9a47583ba2935bd4ad624fecaa3c16a37c4ad624e857469a79e9442e7eea06614458322a0c000e5a755cb4aaaba14f7192a2a2a294bc54e6a67ba8b496f4536b5dcc49344434c633f71240026032d0ac7a2900deab58972336e2e0845ad0ecd6b3948aadf5f2d2c366d738f2115b9b4fb56e5fd96e9fd96e9fd96e9fd96e9fd96e9fd96edfd96e9fd96e5cb65adeae4d9638fcb92dcbae0088a93a0b6815453610b355523a3555542a4d2a905553712a97458c738725ba72dcbd6e1fd96e1fd96e5fd96e9fd96e9fd96e9ff006add3fb2dd3fb2dcbfb2dcbfb2dcda7644381079e8d564b2d0d5305300af69843f82d52e674530d7f455c3d74206303920d6c9a24144d171e8a81bd54e2e53700b55bdd4dcb2f735555859acee0147d20fe90a0c01ad525b5e4b6bc97f65b5e4bfb299f2d19554ff0064de975557429154174da149a340e9bfea55d0adf5beb74e4a168d8a8d9fb46f9e9d55555555564a456b0ecaa0f22a44b4f2540ee8be5eaa22974e2e2a64356a363cca9bbb7ba90595d06d546d754701550b3681771baaa9ee887bdbcc4546c4b6ccfe5b4fe4a189b6aefb96ab1e4f182d6c6d3f9c28b433aaf6723cf591c364d7019ecac5681b1e0d31d1379d37fd57c87b9ae86b083be60be667cc165ee78aa5d4522b58788550e5104b4a986da05b584701a75f711b4f66df350b36f8e774fdf34d85936d22606261046ded2c0b85a3b19167392248b4039b7aff0065163ec5d08eac20b76d510eb461e4508fa43e24c3671127f60a6dc5f598a8038ddc0221c435a784d4ad7f856fc7dab7adecb78dec8fb56f65bd1d96fe1fa56ada6389e10d3ea6fa2ae857dc4ee8b751dca8b5c78e5ee6ba15bab1eab5839a7df45d16379ad46cf89addc95346aa2b81e1ee2ceda27d5b64f1cb8ab5b33664106ad7fec80f578f8ca3dd37d9dada34525441acb0734c6108af85a0ca0e7c535e4593dc0c755cb6f08e0c4f2f9c1d2ec14dee3d0e8eb3805a8095ac7c137ae914c1cae92a2add455ba9a14d0929a9d146c8e13c0a83c40fbaa2a5f4f79186167cc54408bfe6374bbaa159dd5bf355be47bad66f6558755270d02d750a2f16ec8651192d7f4d3fa5a847d26dfc0c145deb1c79b94aca1e2b5437ed0b5583c54e016d8558f82930a90014de741bd7480d291538df4baaaba54be0e00851b131fca541c083c0fb9cbdeea8837e62a275dfc4dd29a9c742b7657e7ee369ddd6d15b5e4abe4aa3b2af92daf25b6b6dddd4dc7bfba6f5d1eab1ba47f6f7395d559df96966a7742d1be2a367ed1be7f83f6625f31a28bfda3f9df3ff002667d574d6a34bba05ac437cd484f89afba95540c7dec94ee8b841df3051da67cc3dfc2c99151b6d73c325014535c079aa68d3fc8983116c1d1929971f15aac6df20b82e3ef68a8a8a9a74529a95d16ea3bc96bb65c72f7786cc171e4a36e717e50a0d000e576a8f15424aa2a5d92c95557c95554aa959fe367454835445a34a9b8f65995268fc154aaaaaaacb428a8a862a7dee9d146c754f0c961b4041d3c2c188f251b73fa42c2c01ade574a6a73d3a15454592aaa959dd9292956aa5f8780d67700a36bf6ddecf694ff013595d9aaaaac9515150dd555d0d55c0dd85e22146c0c7f29502083d167d967d967d96ef0fd260a165068ba855405b5e4b68aa9ba9a15ba8552eaacf4028b4c1dc57b410e79297e0a642d56f75aeef00a42174a9c5407e04df52ab75167a142b25354d3a9552aa554e8cd6c854545454bb3eea8a8a8a8a2e526ac9554eea684448f25221dd56b599f09a9c4750a4e1ee2a3bada0a41c7c14acfbada03a2d625ca400bf55abda4f9297e039a9dd4d1a5d45b23ddebbda3c56f98b7cceeb7f67dd6fecfbfbe661309deee54bebeee602d959f759f7553dd67dd67dd6cad86a901a61d8447f07e3743287e0b50c16bda388eaa6a5a1ffc400291000020102050402030101010000000000000111213141516171911081a1b1c1f0d1e1f1203040ffda0008010100013f215ad3f9d23ad08750e409914752e8526ca635fe38df05470b3be8220449c62432445dd06fba14e112281165f21092a84e3caff686accd4f46855387412ac08ac9704d83818231e50874b3e9163e0254ff004ef5c0c8243691238484f70595c90c8d05d05270efefa3bd6caa2e2f1ff10474c2449c0cf32142e44ac236f42ef41709d02ca5723d2c6ad50b5590de1585839758ea8496e836805574a0c3ea8e907e05d415e1dc92d69ddacc923bcdd0fad665b12582705c7c85fe224c818af2d794278a1ac50dc7fa575bf47492a75efa682ff3043556c961b0f83ecf320b932cff0011d23c0c6c15caaaf9132ff2c9b775ee272a4788fa47f8c05c8ed1ec2258165d464944fafd8f112590c9b753ccb2a88dea68ac4748174b05563bcb5c0ceb6485513dda906a84e552dd604e1ca1a608c85584f98649a84b9812854275a1bd2522891592e306210d7f8ee30624d3fb5155125ae4bbd091d9f5742b738103530b315cf312abff00710e16e8554352a1932aff0044e6dd5c05212843d2e2facc488eb04198d5b4290622dfe61882e38b825fa24d8b5eb8da497443f0dee5892b61ebfcb7033e5255913e87551f71683c62605a7036546603a3ff0034d7c18d9e1896e24b34256d1799aec3c147d87d92b1831a3686776c3a3846b71a91aa0c2eb1d1a224887d30a8ad7190721ac3316cc58d2ba312a47f8820833d86aab72ce96571b16f766bf920662099aaaa55744a5b191d63aa55d3ab4449dd63e4fb93a792ea760bdc4877389e8fbcc912a5841931b4218d63dc6e373103e91d592549554643e998dfc14aff00a156a87be8203609cdf916bd05d20484e90ebe85d2aa1a1cb39150a3ee3e8b7086e564cc2072110a57a1758e8c76dfa397f0bf22158823a22e436a8360e9f224454820823a40d11086d728d048b0e9040d4dea5694c69c04aa63d20820820d86e357d0245fe1f92a9bbd8b094c2dcc828240899a10c895328f4142f731eb1d21a54f11d2f5136e5dd391d696e8476c57c869bd68c9e871184e2e2d381b21a7fc37ed7405e4921f5c3a8d74278a18ab46a18c43ff000c90a92d3a204d8b99041040d74942e41053fc2e85fb7f90823a44232121e8c44106c410367420ad5208208e896644406a842224a4f2e8b19212c89c10b9758e8e8ab619d0a886d27b1c1ec4f75108a6bcc55b363ea67ec0ef293cc2dd221e4c7b5381e09a35256584e7f22e0cc20820f6395a8c4439183c864f4684c3fb1041551485a4efd125396cee5716425d2b600e93fc0d84e607a4241381a3e4d62761467f3474020c5dec5576e8dbd6ada43bd98550e034f0913ba248d00d574ee32c4745db11732ffe9c638a55d9091455cd89663fa8c95291ad57059bab58932696025917b0965d1047446ec872ebda8c0f055a95d48a97e4334e413506857210af7c8cadb12bc443506b6239965e544d6afb1bf918768d5d45a8d0b920d509cf5dc75bdc54b9f616864489b6286930e90a2d258504b22171325d8a1d796027ba25f4a160869db27b0d84390daf8bd06f0257acc4ad10e46acc847db31502ea52ba352bc8e631e4e8c74ada1ee2ea18a15a377d115c4a12a2d074213adb518c24b330359b7a5872734cca0826b4a1a05d45d0939c8a0e0a67e4fc0aac8e6ef711e87397db0f6152cd45dca1cf61fe8a93b10e696cbe884b8ff381c8b6af33c9e465f44229a9e63963199228eb0b5285e46888b1ea34d1da04024d1d8a52f8589597a136f9685058db60989d7524dc9ebe516d8690ea5d08cd6d84ff006229b345536235442cc84d60c99caeef16eb2c7518b026854e87f9819d4d0c540de502258eb88acd0c82918eab428e924b10865111b74a362b72a857a50e5d4768597442c55dd8d56a493d2a21da04d22a113a9cb12208281ae61eabdc602d54a59ab91e0d735bb21dd8b786fdc1b2a3d44b3fe7f86e14b18f40d956a18cd5282e8b24347db0289b9c113c983060fc442a5d04aba6cc85baddb1068b59611fae91eaf5442a8635e89e7cf545df0208c1f554bc0776c79a6b9ae25626b23511b46d1b06d12646c1b5d36df44bf98fb91f5234781a3c0d1e068f034f81a7c0d1e069f034f81a3c0d3e069f034f81a7c0d3e069f034781f42e834868f427a06921e81b26d9b64b975340b166e8953564599fb319bbf5fe1a371e6ee3155e886294ea660952b2f442221d322183ba0d69913c8755c20d9e96963a658312bbdb749de935d2746485b6a659209c842294262dc09a7d5995e330b19a29bb5e473575e47a46d75ada36bff0002bd157aabfe20002c7fd6b50aa3b06c742243412d1e4985d792bb3f219b342a285fe1cbe421d6c38baac97e04c2f3668862cc53c8b3108c871c2db9254ce04fdf23b2c321b33fa2c7e0210b2877ae439274567f4f43a4cd92dd04dfc8467b8935a9524de14c0aba21ae0660e257f0126ee89166ff00353d10257524924924927fdc1047fc23a23a47fa924927ac92493d13fe048d10e57e84a95b0e8c0b3eab1c888621ca69aff0c7b90d99c8e5a6c9c0cafa49eeb15c6d2f960288583c362c7de854a1eb0aae0aec9bd5fa136baa2c8803b9685dc90134db86411283ca7a16c7465e873acc3d4a3bda767d158726514587905254ae443c84b446c446c70764763b23b0ec3b23b227444e8256489592256489d113a227444ac84ac84ac84324432443244324432443244ac910c84324432443244ac910c910c910c91b113a23b23b1d91d91d8ec763b2382361ad88d882bd59a13ad05aac84bbba179ec5c62592c90c2261355e45e7288f4920a3111617d9c164deeca2ea952e0813d15dbc1b2e486269b34d64517d47c955797678b128dc0d6f049151b883d8ab75fa2359d12d85055c18b5d0824db8252ca9d9dd0a3724584dd557417d98b299a0c594c590cd266a0d066f9be6f9ba6e9be6f9be279c9090dde3a3be6e9bc6f9be6f1bdc1f5827309cdff3ebbe4e6139c4e61398361a854373fc952d637cde378864c864c864c8e4c8e4c79434cbc9764c6ce8921044ae28c2cf12b60c9e6e53d3579b75165faba21de7ae8573b36bf64038bc1d7c7e4a4a7b8aadb6db78b640ae3b33585b20d3d0b7c9752e1a4fc3ee58aad1122c0b3bafd143f4ad7fd90ba178a1112109790ea4e8bb39b9f9585b5ebcef93bd8b1336e6375104c26244c98dfa1a0681a0681a5d29958d134fa4d67f9824d6740fa41a8ff024d2eba699a23c93447963521651a0681a1d29931312218c3e841039312ad859ee24a196608e130cb6082b51803ab52ac82196a0e4cb1244dab16454887569aa170a7dff0048a5379dc91918dcc4be26780fcb2f4fa39e4fc090a46c54e1f8e98a5af70b2686e46c1a195b776aa241e3292ec95e8c5099528c935550d583e6e358a253125cb487e06255a260ae68aaa16a1c9126ed2f250aec871c8a425094a4303131c8c047231c8c06210376872e9919e0e88a774754a87fe70f488ff60b13a07e1e872ef81443b5ef0c7612554b82d258ba08d472f42a217d62b52e812b96ca855d81229bcbdc8ff523fd48e5e625c8c4c4c32e8da16a5a74556035889a695226f0341c194b98154980f0dabb22ab226daef0f763a58c9ee4cde6493da4550d3d9333e024541abb050b240729a3b155cd6c31b09d445559eee06abc518ed65dc6b8fc6870c9c094ebdc734673b4cb08243e4d5f296e4c0726a736cefc9294eb67429bda211ef166731c45d13b5bad86935339aaeff927fa25122458ca41b9c4c8f95b46235424ed3e9bb08fc93b6887483728d4481a18208d8d84c757912ced2bf23ae61ac7e0127ea4225ae247a3e52a092d9d45db16e65efb912606a4c9b109840c815f116b2d73a321619773b0d1213090527ce19b02bfc03686df41b342ab21bdd3898d49193c911998b1adec28c087bdce8087ccd276731cd39c66710ad4bbae86a8bc49fb0f810e3cd21d28d623ebb110912516a29734cada8f7ea541f59b82f2645b86398931da135f235ea0bc062d61c0e691ed723865a9a3f91a4376326d6a3a1193b38a15de334351352147087492be832594e5352744486de0455da646ba28522ab2002aacfd411ad6dda996fbb30925479121cf2159ec21d3625bade0e8fad8ad2e05f37191e90f044fe4886613b1532c66828b441d986da415184993f9252198eb9a780da971d2c3518d5f0475b4590f6d8aa766bd0f91eda18aa7b556a4745b4d129d8bb2cec3d4552d0a0161994b222c85eacd3e484a1084c73e9459cd984383aec5c108b85744f242fc133d3150b762bb8b1a0c8a286706b31a8f2b2589d8da277e9250be457925b1592e3eb02ad50b67c9cb312519b1ea9b2269425ba121aac4acce7eff00221b6caba193a98c9b31366fdea548ae5c99dacae6a3ec25c147aa630ad5ca16fdf15764572362c90c4dd81219029e281805373da2bb40d0c4be448693b01a13713e8e5b31d5e2daa12b995164e49012a566b9b09a61c0d913b954767286f8f1ab1a3a9eb022654aa87aa69c8b5a5898eefc13aaae7f82052d692baba78175094a8e21a7f88339194dd32939b7e840b321f4289522099015b93b09dba81309d54428e4710ef3f92793bd0c48955f722db8c5e5a5997e34a55271ddafea1452d284d2af3734bc49a5a44eda0fcc0ff4442320f1754e0dca0346fd04c598c4a6634a87025da399165216306c3dc6a4b6b0e5c4acaa8c5f008d67023134969545b73726214d51214a1b6106226a2598e8c65a957733fd0327255554b9e890d925a5d468959c8986921cad07ba0a2908d34b1d053a954e8d6a29ac5734bbb1656722d5ab9a3561091f8aa15fe667d9e304b954c417387243e49984e387dd09cc13829b121fb84e46e2369a70d168a331d96149aaa1e8deef516a8161b8194d2a7c9912db52a53a4172d073152d8376c7f48a2798719c812908e5439e4a94b36f5fd115362101dbe53dc4011cbbb2b66877a50e2f9844e2a6b745ecda91291363c9100c66c707658a702110bc896ace462fad13385ff23d0513bbc5b9fc15a7783f9a31d05e68ee6f82915824227332a2686eeacd540f0936721930a7b15b83a922687534bac4f6b2b4364562bf23a4da2462ccfc99305d27c0e9bc85e709266ae63c11cb03eedf8230b21943f06decd49368f43a4ae6127d06630a996a30ec2d6c67e07b1a45158927833b4f809c8c2bb8651fb041ac7b8862a07096028e6a57684350744a8ef1ee8c5faf0bf4817e8a2fe6099fa496eedc91b3fd0483f18fe1cfe2479f7d32302a7f4c08a8a84ab6dcaa99a5cfc191746cd2f70422e6baf67e0fb57c122636b15f805fcd3068c2ce0ad7559211e6b62a045b52a5d34849e821ed1bd1fe68119fc4fe0fe67f06092b4486710f6433facc6c44da81ad516a4f715e36a18bed5e85fa29fc313fe10bf4a21eeced3ea1f041f8237fc437fad11bf0476fc114b29bd14513a0252156e92a2682518b58972366dd4225fb0ad2e4c869d0b3fc1015409d56df0e0a0655a16679ac9a5648eeb01cd5b0a7a24c417b05124e2dc21205a69a6bcd5f82a77de115e41a6663a54c9cb8822710d52aca2f02532125f92ad11a8ebd22d5b16bac56199b124a785ca18b515f2a89584e8872ee169cd478b020896f66289435143709ad8914d53523d32d4de231ec364972dc236a512e592463b8df564c66fb8874d668ca87ed16b4db827cfb0a9e84c70a7dc324a350e36e214c01d9b16334188f244069270a891e2046a8c9d85be509a4e6a485904d88a72734d686743a10d5590ac3a0cafcc0c0c0836703705b3953826aab31c1d29cdae10ea47a832bcb7471264892ae45805cd41e8b12859bc9cc309cbbd425e4f34f1c0648468cd4fd321aaaea956979e8133f45471c4df373cf61ac1c8df4a53d824eea1b0bb520d05e79d65c38fe8e548c9ba8925299c9050a6c1a53668c49776d2897c11d0c89a08025726e52fc1bcc8620951742e5088db264ac2d0de94315e2c628e626edd5a7e4f687e232239c2a05b944fab20bb5febc8896a14ee1a5e1474048d951aff009332a3abb8fbfaa7260d4ab8ea288940f2936658b61d5f447e89fd3d1904e4fe46874cc5a0148c70a15d891d8fa2e1d912544fb9249b606d48dec3d06ab30a4bc15edd8485710f2f1376226674925c37e0789646335d14bd4afeed5a86dec3ce88f418e08c5952c9b7271ec451257e9f22549e831cb589b5f03e8b24b8ce6fc50618aeca40b2553695ee7f22ad28609bc3c90c18a82cbb21892d5fb0939deaaab195c29603f2b28ed63f85864e82696e2d11413d8689a55ab0443823e0b61cd5449bb4d8513d4c673d4a986ee65c1675142d34ddcefaae4951357825fe1328712c946f1fb10ab2eab5d4bd2429f4b514c9fe0b7a84db785d899d52752980826dd18ca822f68e59c157e096c9f0fb7c0fbe39e5724dd3a299d1936e62b6f58ab98f012beb709d3b12a2e05354a8c157455472d35e13ee329e4d59224a8e88493a1c5f268f81ceb2a8864a834a6e257bb6332b3a28353f21435bc545b538b86a165469366ea63b0a0d54aada24af6192196e88a41a62d65e1e6c9375585dd9f24bb41ab7dd4437bc4bb284b6a951bc16cf3399fc0f06927b6c9d2b0a7926cc7e42d5056542376aabd8555d9336870f9e1f5fd8ee41a73aa1fa1c28210f2be4b1b1583a28380d4c741630490cdc4479a998976e813c7a4d4a12912cb65028d950a9d92265d5c84bcbd4661e687d206356a47d5512b3e4886411cd7234e2858d9e3a8f473153039ab4a5c2f91532530fb172a0e873baa321db4f097916c4d243a30278938435d28a6484692c685a840c2a5016466c4582a15c78f63833235c062990d92bd325766d4fbd88c8b20d604af495cb2fa7e0a52edb4e2cf6c85b1701368add29868435a157b247b04b450123afc7a0e240e39389a0e2693811e0e24784bc9f43484f1939113f2a0a605c2f8a8e409469feb234fde45689c9fc8ad4613b1ed17a6c5ce23611185b27dc2cf7e4adff5488998bfc8524a9a55af915bcdd547bc11c81652c18e1cd12f409d2458a985aac04d9522199ead3fa1c8bd0b062a77128f0474e2a9ca63d748255742883314a1562a8caab1988a1f22c80584a61e872153b09d29793336137683a22f2c348272387b1d1696a2d3b9515a5e18a561a0d53a7b1db78284538113f235551dea87e02ea38707822b7e6e46785af78459b0f38a673bc110690e50fe455835be0687c7db46761f966507dee0a52da3da166143afef23ae07e9d3a778b3b08cd70cb91382fee1f63117ee3ed6353c89ff0071f637f80ae8f00462663322f44658084385d0bce187a545a72f53bbfc0a96c189fa926a8f7b74d4c3dc4882e090ccff002161b9179619accf8e41d915592a5ea9a47c8d6f72a8295bfa20d41442db75d11d356458c0d0f5c09c3e83fc88625e5d91f925b209df0562c3174089a61553645b5c8abdbd2524170c13d4322d4483af3ac34c41854a369a7832c213bd2c8462d349a54856505094c67884a1a1275aa2d8d46472bc441a2b62462a0ace2f0435967862b0b5164fda79c7982bd477962529e19713f0526b9a9f8833aaa27833394bc09b1408bf0704569a54497a469ee93f9286d84e722c23f11546c63b7a9613ba2fb9aa2aec7d88596649ec3f6557a2a7f544351b5e820487bcb0152749522aea32d5b3127c093780358a297a93cc789a4f61a0ecdf7b0921596d78d46e40587a4d9481e62c6751a0e2ec21d0adb677127b154b926e4428103c63cc0bc1f226d5e8587d50bbed33583d889391ed1008c5593ece3e06134c41ce623a8954995196d11497045afa296ac510483c2b123bb6783599793ba2509e52d6dc109a588e9ed0a984d0a95125a13e423cd3f020af757b1dd81db78283c3d666c34478f5ce23f2549b1e8ea6bb23c8a901ee21a619b6df7624c3953b5d2e258a44583722013eaa4e6ef5c6029e896a9bbd198acaf1564a2c5dc831972708fdc052720912de7a08b0b12757dc77a7dc008b6c29f4a8d6f08bbe5926863283a92ef25335410b7ac4f23a2e26acc8165ed889598fab3156918ec135a26cb4fc74baea161cbe0572b516f8452db0b23ab9df23c2c95e3f6452dfe6424bfeaa2de2b333fb6cc58fb344702f42502f20c18882e38b1fade2a8deb78edfb1854842294aad953c111f64f5aa061deac2a95898688dce9cb167534ddb9e0d472ae49a7591499b6a7af1b53c12c637424547c8e53ad4bd32bbb9542832548c49d264512d4574d0a04c963a2f3849b8212ca10b09ee548118e0f985a34e7d235bbcc864a776dfc98950990de1690fa228f64d542be2628578444beea5410954b751756c9194fd761252ad32511b0e26f37d5612e59266c8eaee33bf595aa2fd928c490da712f8890d4591322d6376c22466631094725fefd0c1d18965a36b91cee37911813a08da96c6f166b48b0641b996320b28da04a5b94bc6073abe621445990693bcb711894c6599254854942ad2c3ca2896522cb3148caded1c6e0897eaff0030438ef25e0ba64cdb1192cb3448ace44af6922d7394fc22f6c29e677e4c79764c28df3c96ba04c4d6083c0c97916f4eaf76542d4fa47985a147b489b9e28a3f8df82cf85f834f07f07d4a47eb4a9444059111c87f15f81cdcb3bab3eb5fc11fa6fc1ab85f83ea4fc11d543b0d58e9a5798170f2842c406094697845522b4e85f3c432fb94c5a4b19ac8f08b7128ab0382496a390aeaa6e535a8d852a6da1ab69b6a4b4b6e0d2048b2451444e248649e02623ab681282cde099d91d6580fd671c1cbbb6352404dd590905ab6624b922855a0aa6a1e4ab546d2484e41e315153b9d3b05720d8282fb2b519a2f10d350d8953419ab0a355127d84b16b3250a9b6e443c69e9a7d53dc4749ce16b4549b946d319319887553455148e47e693e056ed7f08924d683cb987806330d8d29d8555d554bb08938290891f3ac92bc2c7850495e1f9108d378db0eb0824de6298149cfdcb435a4aff005ec733e5a9b2265482aa6d52c0b07006b13e29848ac3c469751415a21f29561371e22cce1d349c5549ba829c868481426ea29423c09e4843a8e6912ec2892f9e704e18ad8ae2689a64351cb2ef3ca191f1bb9602d95114ca4e7da1adbbea28a209c1d34864cdb58c4ec845ac30c098a83a48ad742b0a2162d98946a5ee5289b43e0864a26d0488a9c36660e5d46f5b882a8aca4b3ca652c1989212e2b9e3a143a15a90e89590e686c126b0598996a5c142ccae5094a6a54d441153046c4a815e894684dbb852598dd77c83378aa11b7a6956ae68fea2095aee51de1680b55b740168ab4ad2f6398d8e88a3b0289d7f7264368a3e05abb0cbecd121a6bad87ab55b40ea172a6425d2881573053e05b348b3c2c889e896b4c9023b4dbbd140e4be4cb02054abd9707e8af5122551604a14455db2269dd9558508255fe09717ca4b88274e370322b79b1d27e464779570654a893b5652da90822c1e44afb9179ca91aabd8d803b114531b6fe0a2141277320abe8be5816f62e1e704a02086878104e4f72fdc2d7fcc4702453774bae13912ea3a24299bb96817f872c6cc52bbb8cf5b1138d07516a6a8a31783548f3477173dbae1a1d898f6a0e317a124ddfb50146c2889a56955c7dd8b10e5ca3cc8f41984a95eeafc15798856426d64a735c54d14dfe3b8ff0002bbb29f2253432b248d60f402e7433aad31307f1c24ddbafd9284bc55a3ec1355e25ce1a58939b9a15824ee123699ee6243d8442d1aa4a75154b33a1c9b1fd0ef32a29bf44167c1b552e452227a34c5bf4459e9a94a886ebe413886c29940951504a091c172dba4a6bc416de95516f629249269e088e1507212c84d43d6af82031023abc52136545b4d897909a15a469b3865b1b06ee5a40f264b3d8afd0e8c1dc5c598f6678d55ae043c54675931a909ab8816528aa6a8f12f65064b45e3ca12ff0092e50fddc59ee25455ff001d7122535d7a490db159fe44509e2b194a29bb21bae14a6f2301a37a83bc37a948283292b644d6b4cc60699dba2e78846a795092e91e6633121a2c985547811a2c388b8a2c2e21c541881217b46d9de5b29608260ccd08a1584e35652b944954c0c14b1299ccc385aa6ef41c0c1a161dc2a65c5e6e27dae8dc924b71636c7462cd4bc980240e204c2c25eaf782408541bb83faa526d6974d07befac2b05b7d48a8b38e43b48a4792cb048777588274927b8c2a93615aacc49b2cc5ec58e87123f72699b6e58f278a4c43f7522b0739049984cde2e2a4d6351e21b32c3a12e25ae86c9924de951268497296f4162695a842db3a5fb3c4d094724ca34178f23feb18ac2feea48ed5ff00b6af15a5bb2025e049fe8a56b1a849fbaceaf6cb1d12c434e9d4d2761d4a4b41906fcca14929c969b6aee3cd7b9f92b45a2acbfe46d624a6cd01c124beab922ba20f34e28ad364d3b2a254158a83333d0b7264b759df857e9353663a558f1488f2460b44922d0723620970689c040f0425770871524ab3426bb932be0ae53da7459d45f31e0ae8e161f4960e1c3428b111148244cd9648421c92a7046834cf2f0219827ad04096444c4a4b11a14ad5d4e8333f30a78db881d61dc22c52aa5c8973189d7c8e7b988fe78e9fb0253ef7e4a62ee0336c5586b8ff0094f0911906f7174255705c3c8ff8ba04b248c59a5c9d0a911110e095efec7d0ab94eed08bdbfb8229d242544122de00b0ac0acd5224e3b7c95cd33e51924a3e9c92ccba70095d08d4bf08afe22871dc1e5a0909a28b5d90b60b945f92e45c42d8234e8c252c530d2b0e9d8dcaac14d09941b0f16482aa43925268a0eeccd193a6903a31ca316c4f951287713973a325daf91981c88ae87d612e96130463d5ecba4d7e514a4ac90fc56f6c85d0c2cc2a2be672443bc9de4a938791042bf60ae927122e9d1905eb95691a52b597f222ae10ac9c08d20a2c8f3215456a6363b0b464de8462a429a1502bb1326a569d464db09d46e238055a2eb35279af91c757f4cc7cfbdb015b951b492911b5dc6d266c6ad7a2be62a583dc57a91d888a55c882012d93a744a5ce141ab6229767051ceff0084649a103ac8c15a222258ba5b810de66ab492b7ac5747477e4bf449dfc8888fe0e153b8dad4ec170f3ae43785d0aa9f744bbab272d0bfa690ab488c89a529942e504e25ab6e8bca8ea6924c76194f42a8ab8d4b258c923d95b46cef5b8a3c4e1723baae07e82436658a75e109bbc26c551b386ccafacc9067a72279925a3fa3623cf0e2121a4ae3f65791fbfbcce75e2f21087a731222c8c595b7b7d144f9074b0c89ec7ee382e93aa6d090cbb9475056c328462448e3524488b68d69b1230a16db82926ddff80e2a99d8a2a0850df09875c4c3eac6a52b78ce07439509278abcae7c1a66e3254b8c06915026aa1d950a65e4ece924edb091bed14c92cec3b244e49445322ab9026de148a9d1b4a4d57816ec7b6436b0be434ab18010be86e4557d0c486c42abec04c6dce6e154a4d9e6173974f311bd95879854aa76ae6e46aacf46b2d88703493c0c2fe46af1f2842a89487f53133137b232f205856ccc5857b828a8edfe8a316ae65f40ac2122741d44da19297871b0b6c56786635bec3a7c05c4d6aad8d5eb1714495785348c867216a9ca7556c8c652ab81545452c3f50c5735504d96c38a58d36826b81e186b78bbd0753962622332eaeb6a789190944683274741e409f789518c0b02d649445d9649140b24fc09099953a320d0bfba92519aaf8d0895987a27be058b29bd3899f9618ea5e632cf6fb2dedb66f2321462a71121f6ec4d16df2424e5a1b0dd95e886aafb89eb8d6edb9253c8ac4b16cb028bbb3e9396a50a59d04d00d6a114aedc3cdf222893dc6b790946d3c2d63c76e46b398e1bed370b3fc8ad1537156f9de4f827bc4ca6a6b3fbd2ce4471a8f5bdb1a81b6961a8574f115d944502668e12502c06098974a3b8aaaa5920cf5127885553c5ce238fd5d07e0fc0d5d884ea299b560b0429429a56a4e2c89f01e3b0f1d84d039a306389466b7230357b87550e4809b4eb1a7491eb26ca2de73f24a195acd7f31d43dbca635457ccfed4a581bc68bb2ee23c7c880812a5543484c7b84fb3bb889b220dc26878479126e92a3a0d813efd2cfcb31b4111822a07599725bb1ecfb0c8b330d18a809a5049bc46efb330ec1d4b2ed20fc04f81369b6782bbdc12c05fdb61da997c98f7f8109ca856b0ea57dc7a74398c944e12927141f1233505a1505661749274ae88cc40dcbc509b8d4889dc4908a0a6d264760ad649e012356425ad1132e980591de02336a48a923215d8130a841be923d2f6c752bb54bab60534f409a35944852a5429c42e500a6978ee01db38665ad4514ec4ac37a7b0dc9941cd6ab1b910562b60ad6ab0dbad562a2ab15c9d8ae6b140a5629b293e808fa57109a12c9588850484272844aa62c5261165ad3ec5e43929b4a82dd94915d3551743108277c98a722a0d12d9747f3054d3229123676683b6e167e06989a8ab4a64be4982b93da3d0f647ec58a36a74f42b2d90ecf662f5114ee203e01c0926d2429823d990aa315f92309ae3fa0e1ae1ce4afafe9b8ca4be16428adb6d967d3ade54a478149521324b2fe18b895935443ae4c8964716137aaa47190a5b06a85f3c8eb1cba52d0a1151d8c396772186cd2ef338b4507c17e50b7b50a456c04138422cd61c2051d04c5a4909c843ea7c528c109734ccb7a2e59d70123adb49266e9507a1ed8ec83481b4b06a27a31489974765918362e0984da6eac5e43652bb2c35d3b743f4743eeec3bb6424d4166236a955b1cd8bcec6d919ec218858a665ca12b48bc05dde2234da6655b5c9e49f698cb825108a940f4449d5473284b69b0e523349c38f2d47464583497897c4d279aa2b3f053762b112d2137413645492d06c813263b8a44a6e9f5258c2187a5f30421731af516fb60285ccd926e57917797bfe01c154a4a5385064ebd7f33d0f6311b4abd0d668f85d68714beaa4535b5e8461b5504b32ec57e4825450b48e50b519973e52eed2597c9ec689cb24a62a2b13b52a5cbee25b0416d809f9638e5a5980da22dbb4e5a1724ba7045bb5dcbfb32f96c79030e129bc3808938a36d7d8eec9a1551b899027130a3791091f90d9466a5520815949e85271ba8569fa122b554656a812956de55471a8a5a40a289a5d88c12b4f84a6beb92ab459391327823a0386c241c17614a7db029824ba128c47a9ed9596c349bc2c6e5b31127649c19098574426d4a912cb7c94d19f48febd0fa9b173d8439db012a542881cc53233d87f062d87950a30122c30306c5ede224fea6b6f240b9f0dc96f3f91f0252aa49269159cc02a87675b89b71192a12cea43a2b2223091fca789a4418e48d2a750d2698d2536fa89ace019b984dd59360cdcaed8aaa65a693b6458936e4b8ad5234a040a226514a2de9fc8932e2159449ef31726a0c6bd9082a3d864a1c891d430a9e8fb3e9b22b88b53d12a5a4d3a13307b89f10ee16e3320f0339849494e24bd11ba76f9154aca9f818ca56a5d95c7dc4b0087068f6d6885f28924c22a8bb6be082f702c08533ca9eb8bbb32f1251575cd10a4a5b5948dbc3124f54dcb874b844912b2b4cc806a87405099c8ceeaea4e9a9b95a9516d87ea2ad4205f269220946a90809249425242a742ca508769d8b29c21ba8c8cb6a5c4d4835cb967ec6d3581725b9e97b621acbb33233f211fda7f648fed12afca47f610fda457e523fb0426a283cc5e65e84a15dbd88dfc847f68d33e625fda47f68ff7247f690fda4412273e4120b8772e615e3f81e06edb9e84f68632b63f2f1c9d27bac44bef15cb583fa8309ae46f133ce0819aa1b68ea6a14f90e2098d269361541949ad0ec2d383b389a2f2ad2ad9f04f6553333e606331bd187314a210f395278a79124e8dcd444a282e23071268ad7792ed8f67d064396d45859e82670aae25e6c9a3d9fb27c042d5150e762cd0e0c49bc04df298e0552707e42b4074aa78892aa0a6f88423a56eed153ec1494a491434d3e62fd1294c1ee5081734be8bab9d13a32f08a3991bb8d097c152024e0ae20f28477535989f027f4798a8fbc781cc37a7905191686e1a730d7255ba55373b126c6b2fd8f6cb1b0d2ae6995b4ef07db426cdb692587f866b07733bb04b6dd12c0a4c45e37a2efbe1d318b78091523bff009897c5d9055b3c75540411e93a4bb94413f25d04d4c09b474c104e6e9e2f6b87545f7144ac8135225268bc751739b1dea48cd4362fd3f991daf42bb4426ec84dcd4317c57b3c57b16641b6db8686973eed0a620dae1b981dc64e91a6a1aa13c68997487d2a45ddd22f02cacea22b096ca0422d9f097f8a5c9a66d87bcfc8d921f169a630173e2532ac34a28292d1b525ee890a47b89aa4d598630ab85424eb90402536c1259b62275d4f3b2094c2d89ee268f92f1053110d8aad225aca2594c4f234778a8695b986b57fa121ecd557ed04856a0f53db330b646d7c10fe087f043f823af0475e0fb410d7817d10aab7013e27a1e3ef91b7810cb833ed025cb81108e5c08e4f82193e08e5c0dbc069e9525724e77dc7d09c59ecc84e429c48a169d6eaa29e1b104ad04f217deb243713749f6149cb438622eb2189ad26112090b5c48ae5632ad068999a8ad35a4f02a0cb1b1e39e64b08a12a64b52e27b555e0850e6175dd1ea0b6f912d60d089a9cabe76c788f65e35d11585b90d987b508e09daf238dc3a7e941a510f2d3ff002468aa154a69352a84913bc96cc1b45446d36f1a58b976553c165b228f91d446e14b9986fc93f809542ee5cd9973a66860a8accd2296415d834663f8e08868e152c6b2322b25d322ee3231221c592ce432f4c9d12926e896e4449e8b98f81e45d99b4f28a8b36a37020522959d6dd8690636148263488c095bbdc24eaf188acd3be686e968c4cce68862c511eb7b658d8689845049bbf44e41971276ff000d844250a5dfa467495307515c4f85e8fb7b7489213894c89f176d1d249e9829094e48dacc6e09ebb923359ba5aa368276a6729fe4582924caa66ec281c34ca5a90aa1ad25a1ce954c841339a1c2ad25a0b9c2c02cacfb18925e81bd531b4964d0d12c0a57d5b29a572b8dc8a9c3d3f298e34dc917c841c171554546688a8695139274265a78e79d1533ad20f1328702668b486fba165de20f172292932ae5aa8c9418dcbecbc7545c8a8cbad828d0ebf15ea6cdb5249bbabd4486de2efd17489d50a18ee50b913a8b2015108258dd048629f2a31d53c1ec2280db244bb940a1f663565119a0eca44e90b2930ec458d508c38602a49aa66e61a9fd54246a09b2aaa2623b72e8a95c9c50920af25451a41fdd109a7ba648c41dd06ef95a2cb8125fa578b328569459ee295d74f6cb3b1490ee53817e187eca5718bdfacabca898ef96e2429664a9c1fe469a162cbd1773121365a70989f945abce2aab6af10acb37d86653928e5b44dfc0b5cfc1959e0ee636bc9664694a349132471239e33996cd426274c1755914fe47d644bc24264a04266c64913ce815bb8e2876ed5f28956e44fc41395ac4b215415baa86f024624728a13c973bcb55282106ab3d6c351390ccbe06e73751511e12ca0bc2f645c741404b891496b44926262170a9f11043b5132d2999c86d2e4f34c7627677953d2f992491584eea9f02e14c584b5636110c6a43c5b69d8a0f11ec74a5bb546e71287d5dd3c50afb6b50995d432a2458baba6439e4265b9b835a2d911779dcc55628d8b25e8910aa955bd113a602b8ca56031b4551ff004921e0928c8ea1be20543349aa195054a224d2516c8ae4e0aff0767047d23b382bfc15fe0faa1f5421ff00043fe0ece073fc15c9c0b7efec70b9849115027fd1279589b16262569b8a9f81aaaed9b699293c12be32305a10af3fb11bb9c64d21958ce2eae908cdbb12fb266122bcd502250d6147a054a515197b369d55c31e3032d6e4dcad614a6ae3492628e863c89629adf5165592cba3f27772351494597b68f92fc56a312c5a8493fd824fe847f58943adce6cc9a7710faa15fe0aff00057f82bfc15fe0aff07d50aff057f82a2bfc1515fe0569cdccb0789ba0cc556e180a65031b1cc540964331172e5de4ae6a138348483707df42abd46984ee11851ee86d2fc5c9a91bd3ab317754c3ad5e84a832eac4c1070cb43ca13d8989284d1187f6051adb052225aade5c14c251d105701bc891392f4245318d4d86650beb09e22cefb99fce278825fee13253104195a89fee7de48fefd05730afee1afdf327947398309cad5e0476b4de046a96a624bd148b415dea7923426a4589463c14de2f7249d12e2bd5e036aae44da4d8bcc4b95a637897488bfea231e67c124167234609237cc7d23e9243d2358a1bec8eff00a2759a7a8950cca8a8a09fee09fee1f732ec013fb64852c53fd06df40c785ca3c0e51fefcfe90e240ad232e010c50cb929d461752aca9524494bae7818f86297d879da1a7ea394b4ca8988fb3a943faaa5414f813d04b446d1c97f8c951b504c0fdd492a1556772aac8efd519087d4541d4a9c945f1042b01e2f9f043a2e4a8336b2ad9550259b2c6bda2684fa6eca896858b0d521ae9c99902ca46804ad584b954da7d54faa97a9c1b21a6fdc85a111d0b70359134d1bfc0c626320e2a35d301214748e903d25328d352408732468a39206ba11410142d8a45c107ce51b3688e684b832e10d27490789dcc9e4f91b66e4dafaad862bc10c78225907aaf662553088a37232693682c54bb89e20d3f314b8b1794366a97b821478c9a15189a9bacd0dc50c7d1b8fc0d51cb492c5b1e86c9a754d0944154295108486bd89fdc0580d9d67f3b917b16d7d4ded4a8b645fb6b2c38189505c1a0e09715c1924fb13901908d381e230772887d302e63fb814234ba7baea43b304f5e9e289f4e52e3b63e044895ee8b6311c240a13505a088b23dcc13f92e12d157f0304b7a4fe06158662aee858836359f06a781e7be0ccbe0be40c1abee8544a712bd21b94ee7d66054b93f500b2209d429c2891fc324fa11fd1893f93fe9af8803812bcc4ff005e255b9024a1ba486886bc09ac8d24374bf825ff00042c9b8e1dc5188b1abd871d3e49b5b6c8c933146a82111b671a3d8ac30d5419b06743fbd86d973654f9b0829bacc6218846c6a4e5462768306484d16333a18097d1f934567531b6bdfa9c71aab17599bccdfd8c95094820f89b50b4a14c715171b8558a3313151b1729e04a52f01151888bafed91feb05f249e459be81928bb4f6098133180d9b8222e820e5ca360eb1880b767a342ad1276725a11f72276af22806e832548f89036c9288b21704ea8ec36a83b124a84641749aa3a0f1dc352ba58ba3ba0f8e886dabf812fb6a8ae7e0979f81f7363b12d8ae7e0ae4e0d2e7b1bd2ec57f81ea5c146a5b220a216766b6648f6154f6c7b0a9b2a113b46d09541e33e9dfc13023c093042ac02461ae83158de8192533658e8e75b0ce4f3a1960db6c97e46d17332aa1b21de6cc310d3f2c4c40fb05e44b2a46b41864d8925444e4528964884a35aaa1559134225ac9623897967e08d674bb1a270aaf3c050eec43095b10f23dcd4dd99294b845ad791d1ac54b012448812280f242ae25371b34f55c69729f81da64346d7e74e054d68426bb3237205679b0f15ebff0003e4d6654e1512ee2ccd825277642d7318771d03abfc6148e3f29617745e2b86ac952a4fc50a16af51a255655f2155dc94d0684d893c5c6c54cf7252b91c2bb13912dd95cdda842c86f035190976e0abf615fecacb5f42ab0105c31e06830c8dfa721be45160137311c88b5591467464ea8af557705b4d03b88ea5931254e64b3e51f001f22559c73217436219a8395d98e30244c4f5153565974294997925246bde462ed810509e2d56ee354e2ef244b76b8c95886dd886cbb119366e4c955580909c8ac89d04848424b69d8c25836692924d6f4642ce9c651626e32f6271f2c15a9f91747713af9926acf90796a2092fa24fa0b723e7ea590e85157953e10d43ec454e7eee4b756ffb180fa773103b87ff0038692aba858e3706d13ef314ef4a5ec6a749745586acc3ee89bc496884de4ddb912ddf80a18234cb3412dc89bb76a1b04704dad89c89772b8be10d319ee438a1b4f07d894b29989e29d09becaabb0d61d182d5bb89fa86c588acc7a89a5c861360ccff0003670299891990fa86825326741cd82afa3bf4a12ba27a1d86c0953b1dba595c877e6ee7b22bdcd836ae1bb49add5fd0a20923585b1909dd221aa21e1e03869129940eeba1048484ba21ab06d77f7547c901815039cdb26a286e9a52769df50c7dccf021a5fc8f54888d39d32106f89288efe85380a266588c956d63e7a74eec9568ef247b60b0625fe7cf03288d7374435c2c963ea684d0f9749892f1954dd49a136c04a2ee4fd8156e61b29ec55645bb1ac50d8799cf71461e04bcb937782336cfe8252c60d621a8d5dab4fc0adb18e869136ba753d722fd131deed86583f03638299977e88d288c848a301bd3a9d87b0fb8910c98eab4d00cc91aa23d257d52c483e44f6c89c58dd6f70a988f33670371763485593e0d222126b17744af88d882f0b59547e55872edb05f8f713aac895c34c4ce4af1b5084989924528510c4584d2f195e85317aaddf12d7d9d42669b72497d9a1440babca599b60c74ddc1f9a3300b9270475fb5a1341fd8ba2b826a6ebd6ebe909dd14620cb148da989ac9f04bfd99d847f023510c64a3235110cc9593e06e55e5185326c52b31972ed8a4c7773b318d477a0cdc27d12fa8c40f48da1fd10f406c12c609684b249ea4b3507388e95ca8f6f57ec78a97876d912dba8e364bd09cd559ed621f5742399a41359a21643411ddc8d6a27156c5fe2054b53610b117e685fcc341c3a634dc0fb909f17086e23bc3b8823fcbe8c78faecc740c4089196d2abb351e34cb449e020b882d48ead4dd1935426b04665d887fc11a8eee486446412b4219a351743ea8369e12cd125638ce3a4143cb12ee4cf6954f6c4439ae820b40cd84ac86d6a43a31345d3b141053024a061df50111db145b23e04b45de48aa61592fc8964415e91d2992e0693c10d32443223af250a93cf54210842ff006bfe69c7d518e58915568dc95dea80f29ba55f82ad2637484ae8fd998a2a840d11d65ad01e6a4b721af247eb21910c910b2442c91054a9257a4af47a160a1925706dafbe659907d5ac0909356389b31c6438cabd2721cff9d244c6226f29764bb90ee5a2a3f22850212c84248587f33f025d90d01651a46974cb20d21e52348d13404702528891744210842ffbbebce501270623e6a03abace27d8842505f07d86af02ed56bd468411fe2f0835555348d0e83ec7d64d0340d02391a0680dca1c87a064c4fd29d129db555d8a12c8b712c90d8964763b13a0dd3ab34d11837c88a9acc85ce22128f0484663762780976a0c6cc42fea13cab9e96fe046570409a79f8e89ff00411f4c8ea2aee386476103085d5085d10bfe8c6a87d7425db25d323377a94f8689d04bd21725b8c566bb0c30d0c68688e9047481625dc875341fd99159f24337c9f4321e723f81191c119b81b91b132722e49c8e49fe8624581a2452d2123aa6a0da8d327dcb33f506ef2f1fcf48ff001011196e0831c77efb7f816b2613a5d092f0577cb2c0440dc1ac8d532a5f6e9258799a2e7a1a0e3a68e2f91a3e7a046d0135788152ec69dd6826262e884c5d57fc5a2bb1e719086bc4710b0119ca964b2fc89421954cd66425089a5e70474631a1a20820631f46588b085c96c21af26f12c06838e94e5727d2cfb119de8d010c877eb0b9a1e5831570d3c8e8c28bf0689a757bfd993abcba6e8d9c86ce436f20e66beb8895b0b8d193d988aabf645c90cae20acec2411ccee2c5465210960ba6e41808e0e763484b7e4c5b511fa0d4dbb893216e9086e968153a181c0d31dd09e8dbebfa0a919359a112262e840810cc874a04753612c90c1b3bb7d18bac8c4fbd28fd21220a4d3a34c189ac7265bc4635d18c92837d1f46ba4105840e5157890f90cb4047ec3b599836f03508668bd85f2244b7482c470847f262a56490ccd92ac37c92cd8dea36456e622fce7f5c7fb6ff000a47909f631c1fd4151cb93ea67d0c9ae9f268720bfb58976535dc9ace470542dee4ddb894e643e424bbf6466b9623a8a4e88572a596e5e5910312a079cf07a398f285ef2a0a4a9ba88b91ee2478ae95eb5c8a2e34dccd7f112de3418c96f831e9e893d8e743c0674b17d1acdd115669e4b0a892484b01f47d18c635d5f47d207993b2e581e44144506d2b0d3e4d07c9a0f9349f27dccd37cf4497f84b5a8318fa21edd68c952b7ba87fbb1ff93630876e9674d918c45d6fe8a8771138b99c682fee3b4a84988715549674153791da8e9d174818ec1dc2ec2aa522d86e9a72085bce394e9ca24bbe62e1e52faaddb622d7a05643b0d47fa52499dd7fe0c631b7fe4fabb490fa31ac48ff00931f47d15cb9188c89b24c790cd858324aa48644902a6c34c884d8ffda000c0301000200030000001032ad4085fb43a89f4176dc78d753598a5049ea659317952d755d6df5ce1d34fcc1832d321edd9f324623ed1f6f069379f4c2b606ad75c5156610585f8d23a4f6e0a6a2c31c7058be076140eebd97c590a8c6d5714ebe4925f3a9353751d75276438b8583b6ebacebda35b9bcd3de8cb3e19ea78759d5544981466d135e6505916640cb98a681cff25a3c4bc59d04b14e2db88375f22e88f29785bb9858cbc16a6687e54394f1a1190edde4852e19bda449ca3343bba92461821c93022befadcbeb3af2e96220a3c5293b03c77ca3e334e2c35e696ca9c51d453adf4d75eb7ebedf0d70d7ff00f3c7be33c901493cd8890f5fce4fc9873c39443cf1ccd6d66d64d2f9c6483f15268061ea30b6d54c8cd3a519687ba412ff003143890caf641afa45d3c42cde6984f69f93344f87b8e5fe07afd4e1008bf660a25aad5924d66e0dc8bbeb596430865f073f90e3dc8d971dd454d8edeb1b14f916810dac5da8fcfa7fbbee789d34f7fbc1b688303640f6f06e1fa0dcaab9effb54caf57e06a235ca3b53a001f31461fa5831ca1da95db5c9fe091c3d7d9266ab376b64bd1d6240acca23168fe90d90df424a1207b1af48a9bd9610f318959b2ad26065981639e45a2ad4ecf25f4514f2d5c22c713d7dd67d882af8831382dcc099d37942fc2ce93494135c384128d94dec3b828db4062b2341c75fadc06ec8b045ac9bda95309db3255f943cfb6522e63a86ff00000fa81ea0ea512caa8358f5888a2323456a127404bff8708c0f241141a49998d91d29c86c7f1a7cdb6cf81c139bef166012aafdd7032623a7b3132d5cce560ee6e2d18d9e88206106840b486e813dd41059da155da65d78ebf6a0b0563cd1c57b9712ff003a9fd92ccd3e4e32fc248a4c259159449a0136537f17c624e2b3d4b2d8d742fb0d3319b9ee0538e27c96f57baf93c69f7d7dc6098ed5420c3cb2fcea6470a39ec4528f53f1121f609a2c7b34ac5868ab812e34db8df5ab8de486c84cb47f03e5ab3ec4f8b50b58c7b039abd33c1e79a453a6ae966ffd7561ee45d77d64813b4100ae79bfb201ef4361b53a4425bdc59f48ef20effa20ba1b871eaf45afad5b43a9636b3fd04a957833760e5a2338582514832aef116235c4944856fdabe67f6bfb9fbc508d247300924b480afc3d92b06714dd738a10a59fceafe8b444c75f02e5378ff21de98e6eb39b57aa82e8a6901923db4e4aefb3bdd7ff00baa5e3727fea5b06d5833ed94d628e5ad7eefdefc50738230716a7df535d72fbefbce5683c8aefd563ed897536ac2c61bef164bfcf2efde3f3e234b8e5de96f0efcfb8e7040cbbc860bdb7f57c2914c32cb019686ff7ddfa9a29689b3f84a3a47e451db4b6afa8bff6605a2d8a3636af05b56187b55d7ceb0ae31f30bae961f33cf4cea006904105ab09f17acf30d83dd06062fa81c239610615e230a79ea25b595b5d5dffc4002111000300030003010101010100000000000001111021312041513061405071ffda0008010301013f10f058344c52f9bfd97941220f5cfcd0a3a42794fca13cd10984243fd49e1a1aff0052261fe09e68b70988420f44210e131a34884cc2108420b6221ff9e330f62570d667827851c6528e32ae0a22a34871ba5431d327d29a62884922945a74a51c6684d2e0df84c512fa704df0437197d9ff000a087e02b23c2a5ec67585d690dee8d6a933737fd4f30d621839d0fe477b16d90db507d12a4157056d1e52b98b3f810f434d612634f1b27c158a91fcc6c8c7482770fe43f814cfe443a420a41db115a3d0463afb84d8dfb2489ed0e34251a7ec6ad62c2b6c821ad685ad0daa36c78c6a856ba57a6323831697f4d7433d86df4bd0236e89be8d4a88758f768af01dec8d88d2a5736274b109159ab30dbac4e9541dc2fa3e0da1f4246424644c6989595a748823e15704894834f86e121747fa17782328ed768bf85b758cf785fc2a01dae97f0675b82258d11f0f8072f424f82d2243ac5a25168206c1f0e0e4e113438c968ec63b0784c76f46d11f7d0e4d50944ac4fd315b13a1365951bd09e90db1b0c27a13d15a8cdbb368d1d324cde29a1bfa19cd89960dde3dc7c17041b4390413d8bba38248188490da4e0e1113061222c1ee2b626da1e231fd09e86c7a45538170d7b3f83451a29a124cd1c619ee702686aa17c1ad42684e8a7a191074f5ca91745b6d09ee0d0a548766d3f583ee0b84d9b212f504dbc1f19e909b105c16a0dd197463a54a29136deca4c512d791e04886b44d8d8b86b58db13486ceb3d185b1b488849bd8d7d3460925a58ed086251343b78347a21f59205c13292fa2b3bd3988786a8d1188fd9dccba2112160dece9d06842124f62196684112db227c269d1d60d1c0bd06b59743e446ee19c43d21af47dd9a3d17a42df65d2ea8b0858a6213174a363651349878128dc708e8e364bb36e8996961ec6351a698824361f0f436909a1b485b383d6c4be1f635bd8b0c42dc3131634d08424be089318f453184262169d1ada25bbd70d22436865b6caa8e84f0d06e8990d9743ae9a0d56683747c1e3d8ebe07c876945a669a37689c42252d09343520d19247bf0f622e8d8d88490d36ca434c6dd12776702507af27e695445c124209218a135bc585c6cdfe1eca690d3185c10dc546d6c9469ec488746acebc9f9b443dd0d96e0fa8568c59dae1698dde625708d1b1437f83169684c6ca2549e87f08f0a2ba4f63f19f837e89f0feb8a75b1bfac129c2ec45fc8c4b0421bd09a7d1b20d18d251305eb07fa3c27a636ec53b5e8f58a0d19758f624d1a0dd1650f3ecf6bf89309e6364626fa58f436d75744d35d16ba42629048409f099ec6c84597dc2091087e6db6498b4bf836e0842e27c1aaeb15f6248841a3857ef309e146c84836e843ff0005294a309dc412221a1336420fbe17f1bfadf18421c0e14d05431154487f6c491f083c3eff008ef8dfc68aa9a66d98efb2315231308fc0c78652ff00c04bf05d1e0f0489fec7f84ca1b19b613c67e0bc28bf3a7784fa5cd294b984374587e53caf8dca59e14ee2660b453a45e0241b851592932fc5970bcd0f14ffc4002111000300030003010101010100000000000001111021312030415161407150ffda0008010201013f105e0f04c584b37c2666217d33cdb2d127f7c5bf26bf0827e8a5f4df19978b86e0f6c5e85c2c41aa6d09f93ff2bc377d04c35e4f6704295614a5294a5294d94a5294a52f80a52d12f1b9a27e89fa460935850934436c8464641262a88ca646466d9083554c8941a63ae89178d20d96f06c50d304cef8c266108421084f6c2783cd1e17be9b706ac6cb86cf62e1c207ff0083c2a20d5455f05f6cd23458268d0f6f6350afd1a8f58a8947f420fec4953c386b1a14c68ab3566a248209123194ac7b4649267d8c8a84d367cd0854c68e511ca25d31346be0b28cd90920827d2123e0ab04a246924369212af8262a3a7e11f82832127e094129c1afe16c110821164217471f05560ff03504a96d0d913c23426bb123c17459b17747d0a80db4273a58698d1fd17f425a1d7d3fec84a0f834a1bfd12141135424fd14e10c69fa25283211fa29f46d0b5516fe90d09475f4488a90f6587eb00d4d0fa25625f46f6cfa2d12d0d5087657064a224869d362d7d1542a1b9435a13a2040d2e08a0913c268a8c9360f8207e02582470c4850d8bbd0d095d0910b87c08fb821377637f06886d36112209dbc18ea1195846466f159b3a8d0ec4921a812d3425d936255a22acf88e87d1df84104104fc14f836da346ce878f8122ec4e317e8d5d2ec68b636d1ee862d86c215c834d8f94db64b862a24d7d27c11d1d09e8d1d1c7033e0fa2e91d0fb86d084887d134dc1e9a268d98ceb3d21746c4f844106f672484d0d1b1e28b0319ae0c6aa231ff0059b1362be5e1b78e70e07c0e50cbb346e0fe31bacfa6fe0d3137fa4671da6df4ff00a19e8cf87452a377a1e6174725a2425a1b83662d0c6aa2d28321066c76d419a7c2d211c9b23886c3a1fe091426c9ba51b4c9f4fa2562089a365b207a6b0d9ec78a75ef063431f0684848a2ad110636d236035a16d8f5a2c1d4a38d09106ca98c150c23e89364624d8c5d17f06f0e70a57e9b1db1f0587165294c6d09e0b23121b0c68a52520d4edfa5bd88dbd8d70854999cc270d85a1aa420a07b13887b10db12ed11438d94824a9f21b06584a1b4d90b68826458db2b1a9213b12831d1104c544162a2a2fbdbfd2905627b1b41e388f84a86848f85cdf1fab2512c63e095706bf0a7e08796c62826b5ef28d2f86aac7aa127c1d284dec67112c518dfa11d2084c1b86ca8b50e2383589f093c9792157097a41b8b05b1b9aa6c3a2da186877f7d084862fc0fa312ace345174aba26ad0cba3ab7dea70a2829e9b68cb468e8f8711694c4a6113c9b5a48d041f7c542620c58a2686ff08be892e1f07de0f7c2c2dcd295e39856318b82b2b19b6ee5f921ef097a621b7e144da510e7e6689e23e7a22123852365e33c2794c2c42618ca52b365194a538ff0a213cd2f2a363451ec512930ea86c509869e2e38f44ff5403b3449b18abe0f95e263810b0b9ea9ea9ed9e37c3911899e1e7f9297d105e0fd1445c3b9beb9ee98b3be6f10ff0082662490f2bc6faa527a1e2f8354887a28ac68a15124436377c1785c2f15e144e90fffc4002710010002010303050101010101000000000100112131415161719181a1b1d1f0c1e110f120ffda0008010100013f10be3ee253087fc5530b3e9de160d9b3b7ee77de10641c09bf4971c332b1597e4ebfbb0c4c6bfdbcbc964a5d266b35e1de294603d9e23a30250e5322e66b780331202f73c435f3f3511527b8302a0762bfc97d6745d7f9ed01824e74ff0019642ea587c4eecf132ca9d6f879851b95b71335319ff9affc08989ac3a24b9da0b75c9f010cf5b838177a06af6948e6b4658f3bc3adb4380ba23aa7ae6d32408ed59f9fe54c17076727f9eb88788c3a3b9c8c7269003d0d7918991d7f30a910d88934ff00a8c85825558e6b3ddd602dec60805ad6d51c52bce72cc83bfbc409a5f294181e804773c54f9235bcaf8357f75949a8b476fa33ccaa4c0add66075727f845b6e5dca85984506b1977f7edd63e61de31ff00887481c896040d016cc9a03570d7774f78369db64765c1e2e28a54ab4b57c7b4a548e1f4ec57cca0680697362200692f75f7f97495dfbbbfd4a8454a8d8c0e62b59e1ba5e2ddbb4a2373f3fecb740ebb30950e529da2268c3aea1fdff904cfca6e44f82eb0db0aafd0beb055ee1a9e18476a97f129610e16b03fb1a93185f58e9be3334cdb57d266cbdde09b7fe24aade505348ec970d3d837eced30645ad9e537ee4b8144d9da1ad36e88ee7100a1bb68ff001812a51d638c4a2f1730a37fe5956ba640f9fb8809d02e09ae8a5ae1f086900a0c541677ff00957182c0699753aa15441b2c36f310eb8bdf860aad4363ebfbde6d70d4c9ff002ae611a32e76a825ce4c9eaec7ee905b14c23c9dbbc35856bb93a71282851c10b8b3789734172b63014e837fdd651f22e1b33ab0a98d01afe20627540ff8e0ccb9b68f02614cdaaeb0301d835629635c0d57776f7655053d2bedea7d22a6f068681e9a46ae6835b65e917a3ddc1c12c276625a7023a3846ad65fdb712959c4ab218411310303a3b1de05163553f87f75ed36026abebd563064b0f27d440b54e8f9894af269d9dc860acbfe511b4624479bd7bc2e81abd5da360f276ef0cb835bd1384fd5117ae87575229b97de22e1a776d35b7795855155f51fe54214c6804ad6a0a7b6de216e9d4f78048bb4a7795c44b20e4e0e86a4d936e536ea3862a131f24b6b416bc3de5ca6f5d7ceff0030bc166a6f35712b98bd5c4bf04b9d95e3b4b6ab6b117329992b93e9a7dca2c9529952baccc354d1e4974a03c6ee7ee62a1ad480c163158977abf2ef045a1275974c25adbc1ab12a9a1bf1d0eb0400c10ba6a40a51c5f97596b6affb04128de2625238caa56b451294de025fa4a4de25c3e2595875f5e207130cd217574d471329c69ef0e9829e26256ede82590eceaf4f48005e61069142005abb11c514d5dfbfa45336962df10e9144a0ada5ebfb986c71a56843fe59005b83971300d0718e7d0fec6d170d55c7ab763294be7fb112d070e6eaefa87f65b9b3effec1aa70f4d198ba2f969e911557209f32ef01cf69572a541e7fd96408363fb5fccaf3838da67bca8d3efb454088c16e68acf98bd535b31f762770725e861f5b25fc75d8abd9cbe215d09a82aa00c4b68fa318434e1e3a46de75dc8172b93fe2bfe0d5d75be22ace75fb809bc42cd137bd26b69b5347ef12e147a0fbdb16d12f61fc82140f364668a70d983ea14d5677276646c97fa879873b33ccaef2dc421a44ab771358cd2f720a07561984b57d266a0c4ba9085ae5e2ff00268c5bd5f115041f278ffd8aa5f03b40b22506ada20040280e3da1409a398915025799b82c7bb12f1ceb2e8a9702e7832c73bac38f69936d694afd6e5c6ea7a40fccd9f785a28df0cc58d398a0e0f2aece65dc81caeefabc40200741acae0e402d58a15174dfad9b4cc077b4399651d8d0e3f7fecaff00825420efd035651540e58f5e5f6855590715bfd096509d8ff6221a01a1b3ee01a6dcb2b9af0e258255c4e914e38940b38d06a4580af7317f51029b4370cf88ac21988769438878ff002423d578de28105c2349f509581a3a1dbc3d201ef09bc1e21852f4ca1db8f48a54abc91e00a7217299076458d3e8cd599566257fc5414b08875a578c90ba1acc144d150ac7e6096a79395eeffc5a569a7a4dd2ad1dcf599052dc87f4d25e836744d3fd4c8625efd5bf5de187fc5bfe2b98db6bbc465d32e9e252e540e9164a07a09595e5b4ace72c3a2767986300758f213abcec3f7aca1942349c33fb26557c87b5ca9d52918765666183d589788054086c3a77e26f1a38307fb2ae8bee4aa81aca874af51ac61e81b7f18b45d6a2f37c0d8f3008b5dfbf1291161075599201a2dfabcf698582d7559828bd01c42f272f2ffc096e20621daa0f41ddfe4c96d7f4f3c11163237d8edcc075adebf580aa0bb5e5ef2f43b71118ae52b485b9347ea5bedb09a306801c3b40ac05dfee22aa9d36658e7d4e7d26ed037fdb420d5d9a8ea4aeb33a430d8e6217408f67d2537a1bdedd18af36d328c71a8326fdfac5b182e5f730d449b80a9fc441bcdca3a9d209bd7f624ae6106e99c4a9da991c351c12a74380f77fc8d68323981e657fe6b2dd3588d977ceff00ec409643775f5954ad229e07e60da9c26cca188540b68a9652eda475632c136700bed340056f5c4ed8f44ecff9708d82acc1e65496de2ae634ea235e15ec3faa2185b894db3285a56f78d7bf336f0458026f7b90050b6bd2fee18e5ccf4c6184a2d8aa529d5e90a0776ea5d486b96a0056a97fe45329612f71358e168394dde0e5e9333e01a1d7bc52d2fdc1b29e7b1fec29baf3cc0585081108a01aae845db6bcba35d7882055c6f7b1fd982aa9681ac1ba036da5f1f23da2d86df9d2e2805f574252082eb568769627a40f4e3d22a8fc6c7eef2807ada508badc768caab90c77646db9080dfa3fb0225b6d93796518ba5ed1830852162234317cf780f075506fd4e932c356efb2472229c296f43c9f11277e8c4bbc73994661aa054c0e8388d5a0c75d4e9316f48ae29b29e966d6889857997246a52c428591e4f301319b8a1baed0c6aa1a26b5cfa31acd3a52688e8f683e25f896e22a0b037d259432177e60c319495a156d68173100bcd1ef0af917fc8de760608603e3156929798b575ed2d8d9b1e8fe62015a1074ff00c1e897e20cc3563e229bcec6f3330d4e1c5998699288df06f2b2bd44f5890f40b7c45bb06a10b9fd852d57aab9e91fbc1ff274313b7fe2f16198a2cb53d7bbd3aef095d6d791949701871d31af688696b052ddac9eb13d28912f7babf422a2ac5907a75eb11358906ab1377ea1c704ad1d2025424bcad06afee6341c383f86f2fc01363a7af33aa1c5b47a4ad65ae56bde240f10378c944388250db83fbc47c2afb13389b012c27516bac080e0e47dfb24ed3a1fc3d1cf100adc9b861de3f7a86c97002f220036bbc1520ece8c78a2c3b6ff00ec6019f7cfdc4236364a99975c384da03005b7d97f8c4aad98bfe30a84af4acf54e9d3c4ac1359904e49a8518ad6489430dd8c120a19c2998a3929fa820d76e1c83d724674a3b92dc32f5a3e654d847a4a95a68167afd2557248df75cf4730a08d611dfa30768729d6daf51b3b6f14343947885f621d128ca4b95605cf633f2798d49494385cd7a106a913015aaf1286a875b4dc6b5d343bb2b069d0d20c0ef109514b49b861fb88e9554974689c9f50051d1d0d3865a880be89a792e031e713f8807fe455142e4faef7740f59754851c3ee36380567e87560243d763b73eb8e906e45b0ac2b8985c12e08565de8693dfda29d55edfdb828dc9782c80b04c8afddc98f134e91e8834a09a016af695d06f9bfa88d5e07996e98b987f0e08b8d5ca329d5dbd530ef11286ebbfd3cc3d09fa07befea6602dba1aac45d91783b4706e8335d25868f4a52ff00911d1f6ff9501d0b62aeee8f0edccb2e89b8b43ee58b77d5bfd214a69d6f43bb0e78459f1059d5e5da2a681cb1fb075565e9fe6b01abae94bd36ef0841d7ac0d9f301bc0553981ce3cba26f130344f9fad7ae215540726a0e5cfbe9d48c8d09f552ae186e8268bd66486e2d2c73bf5ef1721a19ce461e1a70fa9d92fa434d047525159ee6e7f901a6fcbeba4a54aaf74e51d9f6f98122366a9e2ab1ff186e4052b9c74996e0001a00507820a19e3a4bba6ad5cb461ba510ee1dd3beb12b760bccec408d4231d63296664bc2f068f31b163c43b9065614f21b7a4b1a373a9123640291a0de71ac2b9bcf32aa6415d37ad656a6045d5bb8146208842d066ef3a9d188aa94df444d6f1c74897453c1222a3ed2f94b798cadfa7598ab2dba7f79651ad2b720882f6ad82642ad75969696ef0085665d6b85f2cc9b42b07695d570c983abaca5567aca4d67744bbb04c953296ff00ceed748d8af4680ec684b52b16e4a703b77d6351dbd5f90caa30f4a80d25d827aba1d63cb0be3b1d2731d80d5e9cb130a390dbabbffcda5c164a20a8f6377f7136c1ae0d6baf48b901e1d8ed2b513d8d59e80307306383686e405374c3ee268838bfb6efe9e65865345e03b1b4a4978e273e1d65cc145032aed14129ea691174898c5a2db42d7d7a454ae35c89e9fafa475954f2fe3170ccc0c142ca4fd980e00be36818c9673bca77d6d023a67fe2e30c054ab1d7fb2151787f890f1515d1fbe66784ec4b967b623fe267cc6901ee42258c3a915ef1d680b47c66fbdb33e5f19569ed871784ab59ab4f64d1043a10a3404130c973405f11161ccc3375cee86397fecad6b26c13511284564471caddbc21fe64ab4f0c596cf08a74f198bcb88edbc67e44e7f64fd08e630dfb4ab721f542b4c4a02694d8d65dbfc88ef601cfd0fdae225259716181c1d3e65f996ed01d26882f87df12f765ece09b8b9748ab3da5ca8a146ea474d5a776641b2f06f2e210daf99a7e7b7ebcccfb2712e202d5104bcd628d36de312fd38fb98105e04bd94a94764c86bd8dfb91015d8b59bd1dbd62a1748e97bff9d663d0cc3741db3a4c125b7d93814a0cbd47465368f5fb8204c3b3b30b014f1b310d3271b9db98ea53affb04fe48458fa3b4126be91d81f503dfef5870cedf584fef41fe8210e17e655ffa874bde1c47bc3f466dbf29d03de60dbde1b95ef2b743de748f79d23cb3a1f30dcfecedfbc2fd0f78707cce8fcce9fcce9be59d37cb3f2586bb7e5870be58dfa3ef2b347de3c2fbcdefb9fbb66aebe5875fcb14ff00d63a5f70d9f9666c1f32ae3de75bdd9d7f7676fde76fde1c47bc7a7ef316def0fd99fb5c5b8f79d53de57bfba5acb20e9eec153a88d31bce616d32eaae53f9da27a4a81acce30e7eb2860bad57761550c98bd8efcc39d2be634a74e1a44c9d3da2417a163fe1de599e05d3bb0baa153a08b508d92bd9e0f5fe08457c8cf8fec63a0160158d8af105357f7c4272feb1c06fc5e6ba9c427d71a074ddebe65a0666d95fb9c22a3cedd76747d69954aa2907951d0c900b5ee5b3d62b7eed36338c6f8d6545d3b9a32d557a73da006321aad4825dd0df4f24c1a71c26f3175a4dc8574c1e4d180e481e4f301c8402ea7981e4f301c9e60db9e6758f329c9e6539256b09e60793ccaf27994e4f303c9e606f53cc0727996727998e7de60dcf331b2799e3cc2b9f794e4f32bc9e61becf3283ff006576f331c9e601d3cc43a798874f300d83cceaaf329d3ccaf4f328bd4f30cb521fb3317afbcd773cca39f78d7279893795e495e6279965ea41392225095e62792539f789e4f328bac46f019a89e619962e181b07f636ab4ad8daff00b19b62797e65f4a0f0f88a5a06df2fd12d6aaf74d8ef28aa8186ae9d825ebb842966f17a8314748dba5cd98a7abca6b078bc9ddd0f58fec16e7ba6afad45b3797c8eba3d19eb29e4ae6b55e55cb11f83565d154d55a1eb2933a41935cd84f48faf1d327ddd7b10f59ae8790c3220c97b767682fa9d5e9ddebe60fe841cbb53a4a80011801bfc9881e8aaf92c54ea06432a6a7dcb6e8ededeb1ae91bd87f92d03498b3fb2b5457c32e2f23dc8d60b0c4b8f10cb43c4a5cbd3fd8707c4ff00c487fe09734f644ad97a3f73bf81fb9e8f0fdcfd67ee7e29fb97ff0087fb35fd7f663adebf729fafb95ffd3ee1ff00b5f70ffdbfb9fadfb94afb7ee53ff7fb89fdfdc3ff006bee7fecbf70ff0051fb947fbfdcfcefdc0f5f7bee63fbbee5ff00edf7157eefb87fef7dccff0067dc7ff5bee7e87ee7e07ee7e87ee34fddf71a7eefb87feb7dc46def7dc4bf8fecb1fd7f65f4fbfdcfd7e67a50be1e253c3c406f4f89f8a817b7841bfe261d7c22afe915d3c44e8f12de9e264e3c4d2107b47402d8d472f40fea8b2f41b6c44a16ceb59f480558b4cebaaedfb59406adc638245491d49fd98eebbf0c25592fb10936b25d75ab96ad2fb322fd74f73d486209a91b5eeeac6ee4f4e87763828bd796a777b0c30175928b6b8cbde054027060a9a4941549db795226f37f59f596bbd152ef579772a19bea06875d67bc0b808e8f3da02efe2e66a4b2cac7cd831ebd39a030e8e9a328be08153a6e25c2613d354eced1d596e15f64cf8876146970cdd4b2280a351fea21fea4adfd27fba87fa7168b07a52f17be54ff00a9c5f28a7fea3cf053afba75fdd1e0dc1eac46af5f107d5f8667af9902bafc32c7d4cbff00332f778187fe261ff898ff00e0657fa11ae8df543fc6634ff061d5f0ceef8677fc33bde197fe063fe330ff00098ff8cc7fc660f7f863761fbc4697efff00161dfde5fcfbceec37c670e0bb4838200da2b93fef46dfe917fb23fee4e53cce5b102214d30cd720812e3760168037e3b4a1979bafea1dd49caeaf78ef57d2381e0b300341b69318e3b9cef0eb44215fda8802d055aca6b47da86be8c758f6ab00c1e87077509c44a129af03541da2ba94ac2f7758773be12aac4b6ac4a8473b232810b4b09d0d514e54a5adad3d85cd1e50d00deb695876e6769a3e52dfb4457128bb1d18da9e8c27acea963d11fdd62310309601a1c837a7483a40b0083a3bc2c11f4621e1f9a25f6fb4010e9b3bacb8bbbbf08641d76ef0e860bf546fd53acf24e07b93a4f9229a5f9237e9ee4d98fb4b1d3dc8f33c93aff24399e48f3fc93aff0024b8a01bde505b63518737c905dfe6702f24ff00d0262d5e49d57921b6bc93aef24ffd8264d5e6729e616558eecd5f90943abc93acf24ebbc93a9f24795e49d479203abf24ebfc901bfc9399f9223719e92c881dfbc4853df73998f579217fd89d479266c9ee40b6f721769ee4e17b902dbdc9def244bff489faa2bf54659c2d5e2fde61f390e2185a6117fc8e26ecacdd8d0eecb73cdeed65cd20f52290e89495e3bcbcd311a07aae5f9e8404c6c3a345a6a55bb44a5cd3d005a991353ea32ab12facab695350aa6e1ee65eb2cc3a14ad1d70f6a837b7babaccea6e394f9429aab9d23d60fc3860e009476b6ccc23769f9dafa987bc9ad4709071eb228f30258d8e4457aab390f1ae4367a402406f5a715a3998ea179a629e63950b1ba2a9b1e33ed168161d498d7ae580f811f5646cf5c4452906747dde8e7ab3380e3794554589495dbb1e26e4ae1ccb85800de06c0c54a83aa330b6fbe0751eb187dbdf1b5f6fb8cdbddf73a5eefb8717833b87ac38fc589fd0c4f5f1b11fa5fb8725e8c135f0bf72e27d55b4dcaad6e60ef2415d75835bfb846d4110a7d41fb99f1eadfdc1745f3f72bff7fb8ea3f6ef2cd7a0bfb8f183cfdc14b3ed35dcf442240827c14d7494b62a24ebd5e6bbcb9c5ee44db4ae47ee3142f7bfb868bddfb96e9eefdc75def7dc034fb9f715af1dfdcda03b32ba83b04cb1e94517c442c2565cb1d95a868a461c43b9e3fb4b76e05fae107f0674bc19d2f0625feb106dee9fb1fb9f919c7f7fdca3a0fccfc0ca6e1e626e678e8b4b33ca55019195d628b705674f585ead4256b1d82576fd6ae3ab29c48e6be8f173a4b0f000a27bb520aac543715034330c54b68d6ffde2a24599f58fed2253164d1f5bcb2f7b1a9eaeac35573855778d4206bb3ce90c8595409b5e8444378429d8dbc6b5ac10c3aa54be8a30772f48e8018f11c4e072e4f4d8d00d5d45253ebb5d6fa4b780c7355b51d9a0f309ce46f0709b6b6a8a02de83d3375d831bb095db7ccf1191184401c8c8bc90d6ae56c2d4a8bd5a905b1b407a070be5d6355bc611464744c392e3a96ad630d8f58fd53706c4cec7a1b4358a8a8757fa4c1ef101b1e2060af738da0045d02c35bcd3ed1697de6ee4aa5c9e2e1e0edd2e93282e819f847a46b539ead26658935ab7a4af60fe6b31196cb4d1130eb551f8e186ac7986e970d7f91f749f691bf688a496b43d1a6180fefbcb93ac299ff122ac447b22b236d5dfb566283d04262477bb2296d2eb8f20a3495a43a97a4c892d016be92e104017576505177a342454611767bc5215ef49dd7598a909618f5808c56082778d901d425cdf9185589a65214dfc23235453b47119c8921e5f49413c8de9255b6cab7179e7b32d526b41457fa4a42f24f78ded1f38aeb0450275ff007031074b1765032cd7a00dfb5c05655d1fec2976d065f20f78f26e970ea8ba6e730442e0bd1f712c3ee11dc60f5488ec72be04c6023c2bfc9814aef8862c28215ea3f30200e860304756b7774f10d0685f72ff0062518c57f663eb77f228718a7f90871c68533935be846c1a08a0b601c60204ce512b6e8195ed0da6595722929c617ae7a4b6775a8fa1c1e84af2a926a2b3bc7111314fc65b84212ccd5e9dff00d97135402177b476a03be933b884c5bb855bf4495bd781ef952eeb66aeb9854e08e275af11566b5e1acbd55d96dcd4f14a5bb05b3aa3081c1c7196f2f5c4c283d03003b874834a3a815f2c69b42c1cf55c7219993ddde3e0bba03883e31828b77549085b745367438bea530c1a06c0f0ebe6ceb12daf5cae66fa7797d1a8329573fecce56f07ff003c663c5388076c2c768b740a51a5e1f65837d8258a0ba2b4bbeb501361a81d31cfa16dfdc494b201fad5f10cbfc5038c3be6602ab4d72eac0d4696260186f3d3fb0634a801ed6666817783b4131cd6d1955aab2a091c4084c02b7b8be3ac4b545a027885fb928ae2810b065776f2891976ed159170afc1981c129e2ff9163035707691135f87797b246a3fea5e2b520dbd263554db78d9b95ce5f0cb08d85055500f2c06b11a47447fbea4239dbc7eb09c1790a46ac273771c29153f83a42b173ef2c5d17cd897537d63675f65f66b18b4d9afcb96183750a2eb2ca465d0840ab264c41498bdc935cdf30ac487e73375cdfb70d020bd5add5b215aa5e237f04b970596a2e85add5808f35e221d1af4d7cc1b20d8f91b5ef89ba8caa300ade7a4594014e8219a362efac317622aef54ed599411fd38c50d04ad0c0e22c96a42cc82c1a3e8de10d5687d5e89e190abb00bf07b40da6c19581016fa5af84820d6a596230de6ec7aa91c3c93ab18b8ad2b3b08b2a98055de47b4b0743fb3c9ff0092aab58604da1036ad11fe889140bb0ebcb923e59c8353c8e8a1c12b58b4b31d8687ed604f0371ad945aa717bb1517f9030f93be2e2ea1bcc1a0e72b3b5b5979a81d226386a9fc1624c155fd9d6111b327c91dc5ad6e8d05ee933d3a620ed56c4ad8b0aba9cbf9f3123a8ad89762b69804d4572369724ae592b9db2108c34837cf58608c843a374fc4c1ee4c4c0765ba26d93c4521252a3c84500fbd1bccd113856789786165a1b9186b5572a47b530c6b8a94d6753699c4205c6b47b4ad9393981600364101d68e8807023642cb27b8436bf1570823bbdfbfada198c8d59a4ff44152d64097483803c1520c6eb36b2c5ee87ad9ca85aa298ea4c3e2cc15003c06bbdb2e68f3663da8c218f62371bce140bd97bcc6ab33bbbcc8a970564d8c4ac689441b691f92b7aa67a4c1c729455423b34c695a5eb93794cebac75bcfeeb3869edfb85ef6ef32abea508966d71e54c99261d0fadb53344d99b358decbb6d2f34937c4a102976b5338942c4e9411a9ac4dd1f748dbaf251c347c94f48ab332cd0da0c4aaf6c30439bd3040969d1cb085edf6a320e9586facc30d1bd99b65e11e903446b9e977f52a04d8691dc6ccad0e9818a40439a81dd80a9c8602e887e90e82175ed901c09ea2c326b98031496df29ef04d5b59691012100e3072c20434a864e99c4424deb418cb03d913a9a9ac5b741d297c8eb15420ec6db07a561d6a5c0f96d83348741774a41b5ccb4add28e06dfd9757aabf918b3a2b1a7e3748cdf0315d8554008f7157af49648f9d07aff26a616a4b63f712f4c2998035aa8f42de92ad3ead587896b809acdbc1b184a318975feb87434567468fe11c1f922b7ef70d8b016bb081f10643a60629d00af5259e2d6875287bc058a934f39947ae9bc0fb958d610ae84b07411f3d613805634fdb7a4d82e18f9422a387476d48d6387dc871dc8a9cb010e708d7146ac3b5eca7366622da0d06d68a86591bc6da847a441d4548392ebc2f4d62f67f8de32c819cfd92a1101e8d5a1317eeb01fbac1a552809519c7cf46a275165100ddd7ce12e140f4fae3ab5cd36fda0742edf5cc7a0eeffe736a5dfe88e67761fc82c9673a3ed03bbfbe22adfd7f2448a0ff001c479eeff5c6ebdc3453b01b84c110e480d07a21138da2a88eed6fc4aedc0e52d7c10e52a741fe470281caff008962d17b07f221369aabafac51102160602487bfa7b47b22780fa85d9db1f48a51cad6faf88cc20ba541e92f77a9203c6657e9f6fdaa3805d343ea1771ea1f50631da623c9ff003f5a6d455036c000f408555afc693687d3e917786ea1598475dd602ee9ccbf7522e920bca7a935a7a123f42edf4ca3523f7b435d59a1a7ed11f8fda346f2a8b5fe4bb37c94b01d87f90ba8bf1c40db6bcff8cd011d7eb94e8fd3fce568c688d3da0d125ab016f0159ec44af1b700516b533ce7fe55dd3752de10002bab4357125c8757f70321cd2d7cff00b1b096d2f4326d72d5d5a1d19dc0c35b42851de874bd669e97b0721ce2dc4a5a687f4973996252b4796dfbcccaa33db746bcba535cb3e4458b969f22eee330c7261c2c4530513202f545fec389bfee1fd83797e8914fc46960a1b7a34f6b8ee5045b0515e85b396f7802d001a178f74564d83ef173773e63be02de4879aed11c95cd910f461a1aad4b6873975a42fd50f31f0534234d030f5584499a00b40d89dcc7aca71305995d1dfb6b2fc14069bc2e473f64fd7ab1106f7f72209c6e9c2f9efd665f0a675f531d652e4a0d969baa55ba6da6fa9152aa17cae38d0830a877bfd4b1253755d87072c766bac13e905001d437563cc3e43b3161405a0d1d597640143b268e8346f453996170954a869c1e8f965682e88be95a75da09191c9aebdbacb3028d15c46f2a5d58ddae5858b34b6de93232d8bc91f1ef35640a8a1a56938aac6df62a1f2e60b2e51a30669cd10652f9a0dfa399a8688a1cd35df1348c45d4c213b68bc9dcbf53cc1da32c461c2bd415a537749b45b2c6d7c38458421a6b69eed3e94b3f140dd6521ae398b5192dc745a14e988f592d09740c1997239cd0abdbf115e34620dea634966b0b138316bbd17c450a055dbfe6a221c331e0ac590c4ac1e8ad50a0d2d867920bd196ded751e1982dd02cb013479890d25d5db140e421819d00ed331c20f78be32285845776a08e0f005b8c32c2b602ec72cf3aab198bbb7f6b017e6a9a5350cea416ce0a00bbbd083876ca7896674d115e1c85c0abc7ed6660ad59fbcebb2ad1b81d6b0e631633d3d6b37d25fe4594056a879d254cb396e01740eb00b41ab5c7505d0e851d2088a067ccf05fd830f5c6a2f7109a22a0105b9d5743a5ca8d0db0e3fd81525a25fa91ab09733a1997311419d6c38ebe91f647e4c643a3dc8c95a80adb29ed5141d606f9067c63d20d0d6c5e417ee3d8baa037680f984554022e58516880076c07f228c7367c8cba4aa01c0e4f7b8d2ea90bae11f12c2ed02b0833eadbe9e91822bde265ef72f76cc6eace194ae0983374fc4e45af6520d2e7504d5433bb45be62ad4ad07a0ff3de01238a1db2fe45754851e11b81616854f3512482aa2eddac34a981e3fa416573f6c00a022aad0055ff00670c1b05f128e6308c5b4035f6e628adda150a5a2a4a06cce62f8d0b916610d380ac35ac299bd81aad8d5d8bcc137622caca12aaf45b5459a319e769785ba50b8ae4ed041c3159e031ae5cf101eabc19044ba6031dd33f0a1675b3497d9507238109ba829753ed05494d0722bc0553dd2b21290d1618b763bc43f15aecd17f2ea17c1bb49396966aecc2380d42ab36bc4eaab741b3ea2a52ae0c39b69641a6972d678ad20ad08cadfe9eb325daf15ba29c85b0e4798456e8178581e6a2c28e053954a56f9f5a9940f7298adced7a5eca32b4621814e21d3118c2de36381b976db599dfd61dd00bd56cd9b0dd5129e1d49cfde0b3b5076f82b4eba6d2f0e2c8ea197fe3b4afd1640a330dd961b325b599aa2a438c98de8b7ab130880759155e941c6304b46810d8284adedaf682729a906af606bad4e36a302d6c60d34fe4624a0b958a35a9621b37500b03a9816aee53d6e3a394d135735dbe52f4e06df02df3114e371cd07f58e856a0a82c666f46d8ac565cc462b00dac17c0734e748ec336de4c07555956f46a370c16303869d6fd7a416c1d284b287b0842bb81cf16182a81fe4df13e01e3e02956c5613516b174d319ce65e1a1bd4d2b2b8afbbbc54c483c245322183a631645088e6b5d6ea075ae97bcd27216fc158135dcf58c2530385b45c92ff35067005500a0d691dbacd50acd87ae63b0006e91ab37e2359d21cfd710de2f4ef57291ecadc897eccb1038b75f27f92ae22c5f1192dd8a0f541e165966b8cf99e5bf29446b51b05b21dfc92e34297eeed10b3b02dbdebea2a75a13f7a46ad2dd35a397f65c777553b3735657398aab476e37a7e6522da93829a61d534230e8e41d1c545e262bb2a09f2f582bd00d419601437c395e56c32c35d8f26a9f0c5dd57a622b5c6875a2f62682c23e23d01dcc48859ad46d7925c2ec566f20c6d1085ddff2a669d5f0c3f9dd8f74120abcda2dd536020ab43907dc459df22fe620150d142fcc3fc8fdc12b86987dcb5a97b8fb9e1eb4e9e668ab26bf8636d97c917e6e616eedce6f980ec3fade5593c0fb89684f43f73457efa3ee173b6a9796e96715f19fe42016b002e0ef4afec7cc5b647416b62c21b78b6173785f9971b311630bd39bf151d0d41aa428e2f15afadd474f14d1610cf82015ba38292fd2042d208ba520a048c75c473598e2ec69f897320bb3b90ab137ba0c594e47316a8bae414bf35ba0df376de09dd8746b71d3dc9a8c8e3655d3ec46bac11a3175fa12fabb93345a1dc92cb41ad6ead476f9a04a28638702f111a851a179098c8ffe402e28a3e6e3edc65821474a9fd9b305d6867de165d6fb7ee5fa1f0fb9c59b5ffd274de1f70b7176b7fe91cab9ff001ac777f37595683383ec802800e07dc4b947a97fb173210d93f71c6cbd2ff652b506b83fd81031c52d3283c7fd83ca9ae6ac5dd047a22c151abb4bf92f96a82807007d98ed6329f0460af7ea5c107c85e809d3af3b684cf78bfea0a4191ec6b00242cabe56bdaa18ace5c6b47da57ab57a53fd9856d77a5c15590ac682c7d98248da1f4ff535828e63d90ab47744e94d35b97d7bcb363405af5618e6856bbc47af533a62331605c2f7f87d25022cf519f22bcc72eef5d12e008118eaa0f81e26473784da3a837401ef0d5acd5f2b69ed50d911d833b517815fc941f03f10dfef31f24d0f27f2e2a24aedf8358159a7a52fa35f58cfb71d847aa16acd9d7030695df038a3ea9a47912e50c1ae506a661ad50401eb2fa83bb26b5469b73754135ef6353b1ae70933d02b5f411d23458f895256f3de5a2606765fe107ce10d560f6a5f49ba242bbca1694262854ebd07884dc8505d85ab9f2456e516e325796e2085a5bd19bf685a1ada3c9321d113e25065881b1613e1f32a507ea4a65e5335340873addf6256cd999e0beea1d1b0134e150fb306a9661d0ccc4a11000de8af7609457f572ebe0c5503456b383ec8c5016014682431a14a41de4e3fb69b6d724036e90b682f859a47f26d0797d45b459e3fc20467497f821acf43f446eca3f1c429fd1e254b127e368a7e9f114adef57d45b7f94c76fb91367cd99a19d444b854e53f0d21781780ded832f4fecd571fca606ad45faccedde1df8cbaebaa42a1c97bcc8f7667e7598b578a5fc7582fb89eecb1d2c7b425b23653b01f71586d59763ed36385786e22f78b3cfe6527ad834aa3f24775a88e9db58c2374d30d9bb73ee423d2a6ca7230072eeeb9c8fea50d4d92bbb5eff00314082ad5d5b95da2d2ebb479f04fe20aa882c36ff006b17d66b2a81d934679b46360949c00b59fa04cc1a380825847317b4b094a047870fb426caa632dd5fbdc1c2225af93ff63b1e57c418fedd9e96b19d91010d87de21500f74d07beb095de65baa6b4c8daec8f247aadd69c0d23d4600508b7529ea359f50d520acc0d87447af301c65e482d2998400726324042506111dcbda28693980532a852a506b0cc12b4606b2d20d89ad9a18de5ef082147d867a731ca2c31d5be63afdf4989a57f6990c5f92936947ad81f9c77f2240498cf6ead2acfa4619c4240d63c35f685a6633a23fb054d1bbd71fd8a547554d68fd4b26d0b73a6994e4088a0d8a3665abd1e20b3cff006405ddd4b1ac25035c17ef50c60ba93501bf01e215b8b65e7423c9b5a60990b67657e19a800aaee3fd8098a855ef9fa45078bbd4fa253bf54b89d1f8941deafbcbfa2f99816529645501bb7087d312b3729c6f0b85c22f05ff009de542297a56ae874c75331578fa353578bda93a43d8ada0dbc1eac260da5e6d59706d21815062c147881352805896076a2e01aeff000b3186e865871027255546e8102d9d5aa97c075cea37566fa6b16b353e3134058e6691b090fe9c907eae25d656bcfa211aac15d5989c11b9d76b8309e0e9d622c45a57dc471428995bad3959ea2fe4cc2f36fc820c452d706efba8e641b042837af78f4b24a5d8b1f540f108b34f3abfe061eb23ceea5afb47a696023a2e60c524ab1b3bb98b38d37f5c3ee00620a56b80df9f92617a9f34bc4d4139de0a363a8b80a8b70d477cc43284080ac21a9091310005760c10953095f005fa6bf48fd9c4410699439ec10360552942a834cb0cdd8974336c370b68751f4ca3cd5a3d8cdc19f7fc4fcfab3df3307b0546801de5d2d668e2f4bda3ff00b0d3a5068067734bf4ceb12e6f60a0756dd74a995a181102c2974e680976521d191ab2d00d684021d7db4c8b2601a06b31f845517064a2d595d65c24d725415c82d9b1c57587093532677a8c6f2e6f30694a6a71cd4a6936b03538b6cd36616180aba9f2da25eb6f2b1425aacb1655eb64a70091591ae8631e856b357a0954c192cba03ad7151799052fe649e7937e7fb05a41e5dad5ec5861b9a41429d722680f58c37ebb51135cf682db442f555b5e8fb4c2fd28cf4b22062d503bff0091e8583934d1100ddf561a31f013f1b92667cb2916b1ea203e26a561ebd707ea03f5bcdd10abea7ea13b47f961aabd4f7496a9743f2bea531b11835bd68be8e5dd8fe4a3b2f9991747cc0959e82d80377e3e3108685ad054b141a467bc01d9150a28a8da3f330baf03d1454a1e74f595b2803ba811458982a308bd408374435af5a9620cfbca17b9c631a41742d1921a04dadd34ce98858808054dd2f968d16facb02e411600b4ab1ba35accc402513fa502566e53a68ef15c5ebe768442c5264217a5e4f10aa60020f0ddc239a308e8564f542e582b056b3d87d4fd2e92bce97f8a7f90301c1eb25b4c008a14a7921e6d457ac415194b5a14941a1a6ef963e7a4c4babf2cc1ff00b01280f405bdafba8ca8a007416b58644d41ae5308e1b364dae08e54cd8357f250084eb485097cefda25134a1c3a6dd58045fec01a66c0bdf5951a759c55ab9e318f48cd0058d459f5229d0c3e00bb5cac76b6f40afec4d5b38fe18cb39ed117982c2cb00d44bf6357d25079a86dd885fcf72ef68f32b66a180ef138f7c00d52ef82a25340205607859df3825606438bb61c4a63d8f8317ef763474e091aacb783604d53284680d2a87b92928a25204bf4ad3a435cf0fea20d4d055eea4a2d0365095cd711505284337c59a47cace97fcc40e8f810c53bd5cb34979b53c90d23d0c3e2020202aadc78c458a45e33fe224234f140f888b0cf35fa805352a1b96dfd99f710185a9f8ffc9eb2b36acfcc164dd9ef06720b5dda34679a07d66f61cd73abc4ccd0dee307425f1d5a371a3d4b22922971a56b2a7b1234f28ff654f58cda600ea75d2b0e612aaae19afcb5972562d9685fc8adf52cd70076a285068c5675c1af8991b76f7443e3019eb47f61606c6f44ff00254fa5c5c16c0a029f34724a69d7b60609665d4f98f0ed86b9edd5adf133b05ec4038020c860182bb3c3555b188d5aebbded2cb6fbdd6fe662dd3f0ad71a54baf1b829a3a5573305717e7f085c9dd561f24ac450282f11d2353d1b81b8df1745616f659ce5f2419075aa57b40176b152d474ff008f48feaa5145450cb559801671cf1669ce6568e5b2bdd81b6a520e9564308f3c628a5af4ab8ea62156a600c1aab3bcd97980ee000adda4ccc28985b67dfe20f4b6b74055d84f287f1ffb1c25396df799f858569443a03d53e9a1eac618ff00d24050aed33d184dae30bdb5036ed13ac24ad3159ac60bf7e65c296546c74a4e9f7154661ccc6f275974e90bc06073b4517982a31d4e5b9606617c9d4192d1ea44d89ab568a6def2e0588fe8afdcaa39a5960220c64cdeb2890792447560f4ff0008abf3962a5ea9dcc5cb6615dd9792a0d2814a8df9810a578916620e06ef4cdeb31cb453d3eb2dadd7eef2b105e98e7de31140bd17f30714581868fac517978afdcce174d8fdc68bb57a4fecdb7d78b7f65954f65e9d3cc45359c68fb8fe55718c03d89e4515fabaf858a4170886f8861082df440fe4278c870c5e7c4d6208fa82f9940192d591d714d2ddbf7b414158ce86899ae7497d03ea25b34616e9d63d028b16db39ff0035881512c1bd17ef7f30960d1681b6abee04d02d6aa1b34f5fa9970b62fb1f4f98884b7523256afdc467056aa1b717e90ed038f59dd7dc76e9a033927cacc38a566d56ff00ea312d87d4a97da3a426015c868306a713ca9f312cf91f31a9a0363a106f1c04de87ee362e6d56bf78999f6b97cc045ecd1fd9a06fedd9a778968fc7be25157987c7d628065fcde16382cabff7129b3f0ef1cc5575dfe610b84dc5f599ce3bac7fb2b0b19bc78d3da04cb0a8acb3f5ef15ce24437e70951b3d8540e5c91c7342ca415182f50cc205015554f9962d56737e041806240a19325ed0af5d393d58b2cd4cb261673da41cb8afec956c337afa40d1cc5fc6e96ec358ce6dabdae2b5ce915abff63500074fecd2e1b74e2655697b46c5b3ecff00862598c13a84f9208b636bba402caa1d34a9e9fdf00af999a5ae15c434758fc4d2effd83d75fc885486e16353e6e5d9b1663734b6d676cc34f811eb019ea692c5028dbe90a9752656b94340dded29ed112caeecec759532af73835c3dbac00550228534b2f53da0adcd00535cf69a272240e5ce1c72cb3eeeb9dcba0db57521c72221abb7ec4bbf3527758135afb3092c3c40088365dacdbd38b8d0efb5c3f93683d04e06b7579ff00251af98055ae5de861e64da61a10cc1d14e12f14a1d2850f526a809e55ea46bd22854867d967b92c50dd53ac644bc691e1765bbcfa475bb852dd828f6c44cb4285354e1cd6d8d203ac52de13471d77849d6005285eead17758ab34402ad04b6c757c6b0016310d55ebaca66e05249d6c1ad209a86f5cb4a1c692f2de81c6d89a1d75f6acb381a76d14b6e9d55bcc1311a7f9078254fda6ab81f32b5d0c6c55b1e8ff00214e7490b4bc3d956e33c4ba2a95d91c778d704055ab6d29fe6b15946d68880e6b343263582082c944556e9d68f7a9ad818973047464957071864433af7cc45431cba218a6f4ddf4633226f2a38805b92d940c19de16ebaa0b6cf5b1653c1c4b39588d65548f4bd38f58fc233ae22f506544d1e88f13f3ef3f6b98ab7a36caba3d25ba82f29706dc3c239758ec0476cd63c83abfb1458973ff003d79d289a1fa90f5bfc4b3af48b0ec10ae8c782220d8ba0dfb2789afdbcb4f89ad03ab5962de7b4a53128081ecc3355b331f254b8dae56ac26bb554ac96759fb688016001d79f794db0614ae21c3b7d1998eff00d9ef999fab519eb1838842e4a70eed118818c5b16f6a36d53097e24282c0b6eae92e77190828b54c370781751a872b6707b2a1895572860c5ad9aa723009c9d2baca16a90728620ed0694b20baba556744ac419a02c14178a1c4d5f391251a5ce4d71d211ac688009c5b1b71c409a619b1c870b682ef235b8f67883a723a8cbdee0bb1794bc51b34a957ddbefa564cdb9af32a2bf6a50343fb4cc6dd29eecb8edf79762aba54b74f301aec4a45146ae8411acd3eae3aaae8b1f4891981ed9a7bcb14fd02a5e4bf76622d8e0956baf66b056aa96694d14d7be26ed25abcbbeb311d4cc3796ac053bf486c209c3da0daa19da5f4075624d56fa32f0b51aa0c362146804509555aa698e0c3ec6657a0cccb92f797e46f32e45fc4451542338ac074d7ac033ba48d56d975ae34ae223a8f296256ef9c2e5989531857469b30540e35e21911bb5c1467cc4844c601319c5129500ae13237675dc84a9a129bc18d55c3195db465121b2b2ad791db3c6b509ad11650441a208eb98f00ae30029beb57d619d0468eabd5cdf41e92ad5c58945b6f40bb635e2105c14f599d5b8be66b66b38f9a67934b977fc334d7c40cb360babe59967595e07fc33b356519e8fb9378298689af6e93bc132e5a3367c115a0d25f5734266166112b69b7c1284a4144b07187981e8724707390822206cea31b3c625759192812b775cfb4ac93764c1c3bef29dca808c70fa54b2fabe19c5fb2c4a4516a8689d6c22488968c45f48b2f2d433ceb332d5a8c7a8ec04a6cbbd40ed0f442a671d020d516c3e22a138fa4476ce32b71a691aaa948458329825250080b5a5295d67214074343b4ad2819368d08e94d1d6d941ec1e22769837e47e41ee11050e9b117747afccd0c31aafb12da580502b5e5b67487d6535c031e21a8176ab50c0c94bc8fe4ad3050dd7696f4ceda44a855118b4468c739e9009c6f710f5ea0e931c730403b01c6d5d62e7412802f14565aa89f8291a81e688c808180039996dcee471bd88062d058558e7b47910ab481b36b95dcc34d12dae868ec8416b55dbaf114c8c978818ba26b2b60870eccad3f711214d6ba4bc8b3392aebaf78417c68ba3de22316016d0d8cb2e39260bef4c45b3c47ab39f5966a46ef77bc090bc5344b38c45d504b2130edac0b41e94fb97641bb66f4dde3111ee6c281c19c43401a34a7addc58ea06b3d6ee00366ccd18545c34469ef14b1d3765ac9e663337130ac99ae5e389accc0f330261d5fbd237ea2d88cd76bfe599b94cbc435fe88e863fd236bb88e77af88adc08f6268e2171ff001d3a94cd1da7c12c3c9941974de5890ef09594238f19a88d8d17c338ff006580016944ac2443c15a152a43a7d13083935fa62d48a6a5e7794f54975301640d1ba3b4090835ff004964d1c8fba5574133536c22895b019bde3ede306e5bd663871d02fde080ce86a7bcc745a43a98eb0589b4b7fed00c70900aed72d545b2581f88181a0858946d2a100d5a54e4004454d69900d68b22f04e85a6910a9a26305dab6e203411aa29bd237bf7266e805ce82b9718e522f5182d6599d15d1e61c3ab4d56c029db4bb9912222bb6582b7349afbcc211bd121c044719d46e12858009af596035942c78ef9c91e814baced2ce5c957ce6068d868aed55beb83995350515cba252aef18d3795b3f176073ac79fd6f159a267139fb4561d1f31895bf9e301704343a2cb7399aecdb6e3f986ab2107b3d625e1b5c3fd8f57386c60b9d788ad3e3011ec1ca3c508a1ffac7b1a1bfdb362b5bff00de5a042f1733a31aa8b2b19ab94c7c2700d4d65bb81565c7795205d6ebf32ad11a956d54d7cca6e01ada617135a0ba5216ef71914a83816a05b46ee3480df553d413097bed390aede531c1a7102bb295876b73e92dad46a83dc41b94d190e378c8d7085ed8b596b565e0d6544ec623d115bcaea028e5b5cb2a505c5651b42b2b5d8e00e7a427f94a6bf4027c183f72b049edfb8b50d7825b1ddd71c8ccdff14851647b43ed76f693b66f47dc16571ceb28285d4805072701ab0a228a8e298f2fdbb0e5fcd202ab4756e0c0d55b2bbb11195dfa87d31a47ea804056ce8130506feacedd619b106d972717cfa90848d05a35f83f5c5487c00aaf372e02ce59133c016d58d0ac4b94428a3023d37ed2bb06c54874b3a2efb7acc5a20de41a38d4e8ca71ccced73a661faf161a8b07cfc438984de0d33ab8835ca5016e595b9e92a8a6c14e157dae06cac2b7306f137a1926719835895565d3d20725569a0719ebd23818aaab34814065ec686aeb68476a525bd1a550fa414f6012072de575cc72d50dacaa3cc2dd5559db45c6f36374899fda2d1d9fd8634a89bf113b017357e213165ab595bbc387f12f8b5d9f99e610d0ef72ef151500810cd357f915945a92059a530eb5d2306da6ab0f954633d62a99b45aec5f1174f5977a32c0aa55e1a929d10c85deca88cfa56fcc596d69541a7a9cc289b8a2dd23b2dea7308ab48b4013e79657bd9268746f778edac6a320d7934173869ceb99556f61c00d5fbe788e1ac9bd4091385fb6d1711c21a261557c6fae3986b8422b96aa318ce7d7699fe329e6360a77c75c1054f18bbec7a1ef0a9b5c0ae9a18f8836c404ad24d023bf12a80128a06a7ee219233a21c15b83fe2c622ad103d4597e90177e0622e8f899d8a20034d76ef0c01ce1027b37f6850582e85b6f68b0994be837545035e90a5cde93fb2fbfb1bc401d5e975f92ec209dcfc83106c7a17c5229a10e8575d6f0d67b4426a57613db1fec0646bd75ef038d8f549d75d66b69546bef40809322f5df545009cb6c6746b1f0356b2ad0ace731986a84d6a7473b8d3cc62c55eb6baae31dca1004ad63766a7e23fc8417dd7f89596bdd3ea51da637ff00c8810c3e475231a7730ed8c1357f6ec664c05b774460cad309999035071dd601dd9989323406455ba71af31754f802f817756b88c5d418e6a94571cf686156284b0642b4a3ea09590b368714942f9c41d614a51168061099a858e8ad861518bd1126d5446c9af9151c80959ca832ce1ceac57be678568d56d5bb133a19454da968157d5c11cb938551864b6a685eb7a2c142eca0ba33438cfb4568d888b8d812ca74c913e8122054b5e0be8d6632c3565879d35a962dd4d31bff00af6942a2d1ab5f46bac0f3cb56f53ce9ac72145c6f57571ac0bcd841c8c2e35ed300204b3ec5c6bd650e9c81d2c55e3bc04620ec4d36bc7bcd21c8151b671962a355456eae5ac6192e12b02d64945562336d6b2c65eb08a59086ad99d35961b13dcdeba6b13015366a7d1aca2f2c17ab9d35850553ad8e0bcb8d6097b0ddade74d6569e735f3e7bc74d94158eb767bc35d6346f5699635cc00d4e45cad39f7821b58fc9295ce2e174cd053adc3ee5aa2c6ad56eae615fb58482d86ccb7cb6c50956b568e4cb6067a4765dbc8fd9d12fa6b03a4a74cb587777ed0f1778e0d5b10b71e9c3b9455e9e8cdaf62ec3ad61635e854daeea241429698538c5b316e46ac5557860d62a27505d1d8e8efa4c204578e162ac1372575baa0c529ca5527c5ed2a9c5bb0802c386eb687c1fd87f17118fb908285c6c4bdab7d3ea5afa027d42019760f2e23c75f2f6959842922504411c70c018d81e745fb410b4055670fbb7e91a5981ced6f53dcbcc4650a8dd1bdde1f48a585ccec5bde541463d28efda619223eb63789a4156f7efbf0b1ee1e7c3abd1778ed1e86b61761b47de641129c51abf51327261a3199f591b92826a32cc704f6b2e602864847c7f4652ca96810027414b34cac785175d23396203ca52e12b4f4852efc11667ed59d6b508581e58604808d98c43880d66889bf95662d588366667fc3e231785bb440a6a33f9235b757eb6830b4343b1e252c64fce904c670a7f9300ce7f589d0007f9c70a414e28eb1a5fbc445354a8c80e1ea3160c2050d106ae5f201de0efb66555369298962070056bcc45be0759a82b46699dd604eb2d8b2d06ca02c78abcc62d4b6a2a8c55759618ea23f1be52a3daef78a5b3fe44648f511b93394285deb48a483c93a8f4de391d408679563166d99581c16f88802d7ba68f5826a2b7307007482a3c1fc8160dbfb4c09b820a01d0f48e4d3a42437409e75555bad2d9d6abab32a4d464ffe53bb71fe70eb1ecfae64c3659deaf91837eaf69425205c95a6b4cfd3035e0fced371a4394f1022c6811a16d78cd0028334ea45a7acf17fa3fe33bc55cc4cb80cb88d31da986d8bea760bd62db15e2db777c2fc2c3a080344442377b23e48b215fe888d5abff6c1ac7a5572e3fb0036f84393c400e67cf37b5af8a014a138bceb1e5002a762cb24ae6697f085129458535759d3a815d98d297b1878f5081ced46de2d018a50b152a4d9bc3c2bf430f01ebd2cae4c8ed667a9357a136102ed6e75cdc0a55e99b7468c75d2f6668be2d0e50d3398e834949c3311c91e923e11febab15b3f3135e166570505e5829c81d20bca5031d348960b64eb2d3b42272e348791c3a4c450d5e9168c3337a198d70b2ba3b590da68346f227d4d503a9de145e0ea41e12ac69d677c2007a115e4b2886ca1c8d31a475c43e11258586004a6c554b7619da3774dce2a56e86dda0d055dd4c770b63133656758ed8945da6eb7c622921372d4cae302102aeccffb8d14323669a4af21c75343116ca56e12dbc8833b6512cd1ee285236e2e7e99b82d30c43757b854b596130bbeb32e6a7cc75c654f74430f68661ba69a0243c4bef6e8d3bc6ad777c478a5a735128c286aee630c15ed19e4b684644ece113306a6892cd6526156eda91072da782559a20b3563e67b4fec5194898a66038960d57224b0b53c108597448ada8e2d26da4165a31d04059cde171a55692939a038be6d3bc7bfa8faa30595711294a1ac2e70a34040da2a0783de2b01a7f088bae6cdf302b0b4b7d13f7cc78b0af8bfd9a50403fd0414b0315f3c2aa8293774e8a28d866e1533d8831ba9450d85e18d6c66d9d6e1ab06c60a6a306f2800ba2ab6f6658b5c117d9043d55e056a9592087377662669315bebd63004add2a230ea745ad66ccc562dab2d02ddb330cec6dad547f8eac58bbbe2167e21e614b7798d1c0dacaefd331c58caeb07679890a1c98ade2e1211018260591d607da02f4da503ad2c2fbb18c68c86a954569abbef1aacc661758a6adbc62d7608d32e1e80a4c1e62d75343437d28e339629696b5d3aaca9d8583d119c350055298772b31581634d2a6339acc5062719a74238e6d4c60f5c40bf021716d65156aa26310a6720339960cb08bd14675299851b2b15975385a583ae1883822b8463901988a525f1e0457db7ca745a76eb0afd5b1d08b550302612134c58a0cd4a2d71bc12c6b6477c272f52085b95df830929922512658958741eba47767fa68c75d87cc2e891752d94f46e132859aa8bb58f577b88f9a59f08ba4da1ecace1bc26d0bd93488d764fd631a329eab45f75631de1c156f40a0d1c67bd92f30e799306ba94f9827b933d0cb47986da660a2ecb412b4df30a4cb80719095c3bc2a9bb2bb6c18f5ef1738152d4b46cd1bd97f90898ddeb50c5c70d4452e892de87f62cbfac471c5343056ca89ecd787e50056cb7450d5b9d2172870d0c181703d65f8a200546c4c420a5b116971da79f73ba62b01d4d6fd37c4c0a6dd0c9550c1aae328264d5c2cef021a3d2cb4ea4b9ae3a3cfd63589627274b89428d081adf11b5bb06fcd46d855d177d664097433e91c0acdc1b8ca951751c4158384d842d4fc0ca30d9a912cbd6c7897428d21476bfa9bdc20dfa8b943d3fa32b2f0e0022485a52602477d0d0d349ec3facf72fc4ca5546b1ac86a4168afc4d44c51eef34deb5876225c6b09e7ac5ef6c5ea4608015505cad0019ce310304a60c681a441979e5d8b1ad5ba4acd6f2cc00292d992b6ca3f0533140dbb5557a4c18d12ab17e8eda427183cb03d4df2c2f0d1016ca56a003fc4c52cb458e387a4d653ac9b02075bd6f42eb306d15605ae40548c675db625e491d33a375a7acb209c3617637e90058eb0b2def3255e74c38032195157de69732a33d653a46b4f292c248b7247a96f4c294a0065a443af6dc7eb0f94422b4a1f78dae5ae3d08096cc2c497e2581c0aafcc49f4617a75ab2f6a55134a4b1d00bedcbda1db8e5691e73a61213cdf3330d6c1ee43093662c3c1082b5d1695b0f13aaa9114555ae942c34d0014b0682e4eaba37281ba8bb95419db37af7845c48086564f14ebebb427518ae60d66f666c436781c53ae1ec930c04290a868e5b2dd62a1e286b8f73310b32382a654358bbf62b1046bed725cd8e2a98c2bd1bd651577bf49b427181b8739f9989b659bca270ef53d97f498fecd234fe5922851668facb6427004f88ab48d5b3dc6597178c8f8d082055dcb3d1818940a590c39d6bb4c42fe94cfaf8245a253676344bb418edd26cf5992edacf3140a35cde93993abeb135a8dfdb5211c50a9bdf1c4767c17829a5dc1b07788da01042b3abaf9f68a59de9f1307ba2aa8d7e29b4c35914b53259cc2c078326181d4ac3a33e3074620f2628068057fea61c38bb3de00bae40a0a295eb280c4c8c6d31b7f1717ebab28f81668557f91c8a050cae98cc5b3556a79bdef3833d08cc86bbc8636218bc16a7db470fa8c32dca575d10b3ccc59d3e92cdef9573e08006c86948eb465473e8db0294da65f585b55764d75406de7fb2b7dc1428d31505a21002a33a573129200c00680005425406cd5bebb44c86341d977ce22138926ba5dfa22d9d1a7b0cefcebde3a96a4058d3a0800abf48d003a2b9459cda60e33476349080c1a48392d8cb7649502834228ad31b9ad4e4cde69f52acb7ba10b80d5b573fe46db0e03b659b5fbd226d5daa809581c4992b42a156b6554ab76c4153410d48f94fa8682215c25145abeadf32f24812027225ebb8a7866f45ddaf5b5577c69da53802aff5cb597ac36d71ca46040ae19e9c46074da651295a56b7e732828a6f4f734d60b196866b8b9833a0843d7aadfc840acef54bbdc0d5c088d4df4d20dc8761701d9db1a4c74552efc62b46b8ad2660b731dad3fc6c8ad80fbc10dad835e2aba4219c29555b568ce7c4c6b59bc84f680e0841ea4cfc1d49fb9d22df5b592ebc6d324299eeef145230e0bf351f92ee2e5ad0d81d0b1753ec460f7ad12402042543afe606aa1575bbcb1b2ef27ab5f144cc86797e8eb72fd7da908557616af88d1eaaba8816ec814619ceb3cb1060e9f3ccb454035ed0296110cef707092adba69ce90257a326c9d95f6867a4d9bfd311e69411797475619b614ef296bf1f6880a82c9da997d974ee34fb406c1a9afe1534b12dc4ceb074ef823fd756158841c829f660f9cb43491889d8a3ab2fbf6c94fa81c9cb29f50b7ba3ea0eabb5b3ea240d95c3ea12a80eb63ea542698642fdba41421376cfa802d4ee7d4cee6f47d4da905ea7d4180e6ea7d4d7d0e89f508591ada19e022813e9e1f107780b586ad5cc0f24c598360ff51410d03f32e5c194c848e45ac2b4a5be3f706f8a0c86fe21a4eb3f1b94500b9672dcdb8f51621d2f75fd32eab106398d1ae904ec6b851fda8d8c34b5a5978577575745c52dabadae7ba7f6691d29f30cb37928041b42b200f53ea63949b5b3ea241b47b7d4c3aa15627f4f88996d94639c6d306da3a27d42c650ab2eafb7110b005a4566dcdbb39969c2f71f52a0ca86a66fd20ff26e3d4e255bc8ad034f486f79cc25c0c5c1a147b45e3fec59dfc54b306baa63832a23669393d6567eeec453f311dfe0ca6110106a56a5df889da32bc358a6ad468049454af336595a9eb0d3908d3b4c2502e8d7965dafa9ee7f9189ad746f6945ac29d4bf541c2df42aec10b9537296fbdb2d95f5c5f08ef5f77ee0bb4f5cce9e25adf67d1816c33b195d8df4b8454281a5d52d9f953414beb8b946c0b0d0000bf495b735a80bf5ef1d42165e0c01ae62101241a188bf5d58efa4bfc881de1ba0617bc07c939d80d57c9e615886c058b57fc759804e231b619684fed0f8b1a65002d6ef00cb26c1e01355dfa06b4cd33b790b79d222ae7d2ba65c689e6588c2aa96e71da543e8701c0e2d500ef1791089a6e377772ae33be2e06c41aee674ed13f53daacce42b873b336870b2559755a5a07a4e7c98a1874b4b45c4a7f5bccda7960c69ea73a5e59d0f3ce879e2134f2c2ed3d2e0f1e5834ad4e72cd8fb1055ade82e2a6be4c59cc207143ae50fe98707927fe867fb2e2dcbdf3ff4329fe98742f56a5732be9be4982278e8b580d730b488776e4b4ec69bc3e604a4cbb7bc2df024b696963d5b3d233e820a58034def49de6fa13b234fc352e80745a88560b5e9d2228d5baa115a2fa8c34569a4a4ea2f098b37226f6c06846b7d2b1468b2d468b21a8deb8baf8898e7b455edb619946b2aa22eac68f78668325d082800cef31e82fec7fa731fece22e950a13b1415b4e312a58c75e5236bd2f8945d714c1ea1a7497a777fcc4b9a6aebf8581606711a378c3cc7aa2cff92cc86bbf31fd50c03ba4fcc5ad346c6d47929945b486cd54ed490615b070504591d7b60c15a52cc15745e97896629112c6f52d2fd98309953c94d408aea59d2386f002e88d9588f6eb31e5f1f0b0af61418e5cb8e21d83756f4b5412b4d91f3ea83992223b8e682c76bc891f162d69bb256fd663c5eb8662ef45fefab194757f937e10d20b2720eaaef071ac6d03acb6b44033c1ce1531d16b8c1188ce2b7ccab8836499ce46f1cc029414aee501568052db8c07cd9b1835574c9778aa823c514f0a335c96e8dc50f1d68eac3a8d355d3a4c4880b2dacd5fe5ca49eb9402e20528ade22465852c6f22257388806a069b9eb4f5da3a03545addd23828b02b6d596bc6492ca0b0aa1831b5448d9ae6daad20035bd675622aba4aafa6f88bc811228cd17abafa11bb4e35ad0951d00c54ec9b7aeb2e5cb23d268c0b7072f480585dae811a6a010503a286979abe188a77961fe2d1977808c628231349a8f08e123aa45480b8b5ae3a43fe03687b4cb8850a8984c6e3848c89104405683e3cc61799fd8bc6f9220832a5685cc006dacb2f50b34f68d7e2263268660148b4d30db51244a7a9cbae978f5d22350c5380045434abab311eed9ab0a23415a33bd426ad95a0b4d7a34a711268a7662e6c6e02dd0965eb1c18cc06cbc00b19a0c82ed128cba40148b5d0519633d101f5b5b83b35b94c4406648ada135a4c971834cab68a5ae2ecf988c841908d2c0174f090001b766e2c78462fc398a452102b2266d71b83f83ea4673b9357bd1f1b40f597484bd551502791d7f38f785e4c2e115da91cc6d0b3b3dd167c6e42b50b2ee88788337c1e8829e0b6c5beb163339d005b3547f2633a63d6722d36de2c618568558e6e77015b7621ff000646b3324174d7db1173277c3e7afa45b39fe0c4a821565d22fac46601ab52804ccf29fb694f59ac153aa37dd7c409112d9f5f63ff00603a76a811bf92778ff61248a43545a7b4bb839c55eb1d226aae05b76dd0f042c173a1056680e0d652065bb02b6e868b6d3ccd1c09c7d3065424de06c5ad72b8eb0daf28c8e5c6651d3440ced5a96d3b4bc1cd2e00baac635cd6b8b98c0acd363da3f16258c256a17e26a0ace6dfb104a50dacff0025281a9e91e3c198aed68da2b5d0c436dd816d2919f594b40803993ddd9f11e69ab45af4b3869497bd1ad8529aba8994c16debfe1968cf689160d5d95cbe8d65ef94155d21e90757a15163b06f951d17418898c0728ba3c956e945b4acad598c0d2d81ad2581701a598c8f913c18d0739a15a13a4c18633189ac00f210b7b74d84e94a13c80b8b5c2a1600bb7074156cb06a90ad8c8e56a5e9477225cb09730d2e393a2a62b72a86ec71b1653088d8e7ac12e5876816c2f2a844e1d630efa0a25de885461c0d51325aa2f2e8047811a5d4dd50d7b4a2378f9a48764cc0968a2bbbb419d74b2e2165a1a21bc25675844321600ec0b72b6aeba46284151b6d687217571a3070404d11322724c24c5705decadef9732cea6ac056f6631958312282ce1c2274448b0954cd0dbc2adaa984dceefd764b0676c7109ddf740cfa40a20a16c247b5ef3f13825a51c101411b223a81a2aa2860c8299b9b0591f015562bb631a3151ef1c3b55c51ef2b84a88189e734ddc0dc49946a3a92c1695d62f3ac582c15aa3b4664e419341da282620886e334beb3aa95d60ea5b3502cf42e099f1b90f6b7cd405545b677df0fa43557ebb5db88062ed545a11df27f061c0ac24bada83568be8ae8edc63e20d556acb5128a34c5e851f7ed002a6758ecb421940c99da7fec041a27cd46d514a04ab1f725c46b5fd973285a2dea841a40fca8f0fd3b44e1fced297f0f896e1f9da03f87f20707e7681aa9f9da52ea3f3b4a787e769d0fe769d07e7694fe1fc8387f3b44fc9fc8d02ec5ec2b377e234b41e3adae3300230146abf694317f4ff3100aed87ea1ad44e8d479bcd3d1db868451d54986930c16221a94060662aeced5310c07a118eeb3fa976cc1c4c551082c6c817ebdcab9ba84ddb6c6a30cb15a5e7215881511675d610f953cbd7181b6d2cd39b8aaa3035765100070d0b785b664779036d382b85039a68955bf2c74a0468d70a6a2dda3421c6c50337c9a184c79f0533b9d53cba41efff00018423539cf89a168ea989fd0bfc4b1f3ff8811aff00bd2510c70a6fa20aceebda141a3af175af480f0fced291a3f3b4ebfd7a43a39fcda068afe7689a295f9c40e0fced01e1f9da5707e76814d3f9da570f8ff21d380469f12b83f3b43e168515800cb8d2ab5cd6baf88d4cd55e68840b07526caee3d615cd340b1e6aa0420511046ed39b1e8e9a30c86145a4300604a342d713715b7ea00fb8cd0b457dd165080000cd25902ade7550b896fbe9f8ab986bdd71e1ce69f172d41c8d015af05d5c31f1cd465907cde75ba3df1e083a360a0e20da5cb855c1b6405d705daf12ed52289625e4bdb8bdb598deca05e28ed863a2a59e6871b9d6380b3d9c7865610adfbc0618e02b4b83ad6afb38943ac34104f4ac4a60da58df5742590097199a286edb76d65a310b11baf50b3cca407c4fb7315aa2f384d8a3ccaffa206da877c4df93541da6bf368e8bae581745ea3853fd1c7ff5623c1ea8f0f3a67a43671e949aa8203a265698250c3c8052f880e137028ddd26694195b7ab6f695b4596d6dd252c90b6130d3a70e482d59f31f35704a759ec9b169d0c416d19020446939cd56f18865a8146ac5143569788a80a4bc05111bd629c2ba4ab65486568a44c5359beb2978972434570573a0d085cd1979e89401e65c6d7a2c6d97cc0cde608e91c5aa60058d31bb21d510ed8f68d089d011a4dda54b3c46155f1ff009838a4a2e2b4e8fb7070af365d0d5dc94fd99c911540aadde93a22e87828d20fbc8f09ecfea6e81e9fe2223a6f43fc84d5793c84c5368c87c891a18c5a37569fb12a6690deee44d2e06c075ef559fd822efe257a066b64df2765579a815f04b8a74dcaf58144c45a6abcc762f06a42b338866e5003edbcb58196a301b3733af462e231c88e894f5251a78eff008187d619d3ef52fe4a90712881c732c0b601b4cadd136a07d69d20e21151894b933c418bd32bb1ddda0ea870a95376e9bed14823524bd303e65c5332d45e851eb5eb2d4009a5e64bf996ff001c00c1e58811aec541d831ed10d81d1011726f22e61ec2b2f31668baaab9afcb81e5446429ea62f7fd6e175d17d66080f5713368f72cade3bb30bb9008716aabba0541ade2d11f7b24d125bac24002faff00b1033973701d4ef046bcac464956e68781f32c91a56d600b874632f6a82d8a810036803b12a310a94bef09fde084764dc8e179b68abe8caf17b31ed01c4a13a0475a5833682e41d586e03debfdf12d8a68470be9fd62e60a31ac04c8112dd9026a8374e0e6e15ffe90b30fe7fb0d227ccd94769cc43e3ff82161a7dd96dcabeb896741f788d22538f98e0cde6383577b888d577d6329d4ef645808da501dc15873aa94653753a07c98f68086b86cf41f72c766acbdcdfd6e2fbb0e5a5e5029f047569afa8ca5f3d25561cfd8faff00b2c53fac364188c00e2dcd582fa5b839bbf7802045a0a0e5b86041b0b13a418041ba0d0ed410551ed19d085dd50d5d8eee9076e9a87e783dded025ea8befe1a3d987ab0dae8f625650d9b6765d3677c4626036539b192f4e561af471af1329e575146f6afe825ca5decfa9be0728fe12eab03a0cc15e6dea128e7de88c83d2e31b4c1c5c060018cbac69495d6075a312a7f67886497bcb823417c29a9f3a2b649cd782785ef0cb09d0951090fc5a4432ef8817de51e7f9d5ee87009584040b50b5cedb7595a82f79ef285c06c3c15fc851a0cd647a31f191ad50ec3881b9adc1f2bb3c114abfdc3ee25e4beabe44d319bd89f352bc74e7ee9f149704367be55bfca6c68ee813faa59985bb8f9976adbd6f78aa63a5bf031686bc2cf9a3de3f1470cfe317b6f6d4fc2a581636aaa78d7d6236075bacf9cca436ba88ae8b1283c7b9e65b62e7573283774af799ff00a1350cb236cfd5f730603a91c5eab966be54cfad7b5a34d16e1d5da02f0af41fd45988a615ed74c5c35dee0d60e365fba01c59daee359adcdc05b5bf5447286f148c40d1326b48c7017d331c544078604a45f557f536078875766d87d265b428aeeb17a8e6b88480d5882d9b73c0e331ebcf765fa7fec09bb67dfdac7951d7607e0bf2a86082e8323d930cd40601c5435f4e8a25276940f303746840f7d563cb43e91552ea07de8576197966c29f263da3762b960f4d0f04c13a6efb25a7ac0239075ec40689aefa9e87f59a4435a0af8885157dd2b574f765990de8c4d81d60ee96f189a97ba25dad693362ced598434f70c556ee8a08d146537a9edd5c1195499ce7b6cfa799afec2203d56af76512bc07af63ee57178c00c7b32d309c8fc310d50784138ab6b241da9f0021828a3ac0388073ff0003832d14239d1bf9802dd8b14facce4585a6f359f760d6b185fb4801c6cc196bf5a0d4b76530ccb62534901d3734e25c5334cdca733a4a4b57d915523801fc8211b84fd4498f2b152f581d8a84c810ad2e05a5d3478819a2525630bf30ed98beb32bf11570ebed2a14832a8cf2a27a60cc35cc2d9639fa65060be994ba1e0160b46fa817178fc628d40ec96de7d0fcc17585c6512ba0f4cc18bba989404b3270bb3cf49b59c3f0cc9dbda6909cd623ae8eef08c589555754f09cf4945501bc00683ad372f26ef784362fac28db4f2ff92960870167c417dffc47a2211fc510bdaacc2146ad44ccd931cacf1296e9c96f0e2716743c3fe2c5ea5aa94f7ad7d6e58597e01f1f52cf63007a2fdc25de4f3788772c3d62f91aedac5ab9ea2be5b7d0a9eb1c0b7a6b14a593568fa6585532daafcdfbc14c51ed11baa0ed096cbd65b529d089e424d91ef0dd3c4596d5c31c9b4619174cb1d80f12ec2be529031836ae86ac02dbac41ee3a7ca592b6d42a72ae57aaf898421aac0e8bfc3596449193051d8fbcf69b8139c25263b06628da7249f70d2ca979a6a5b5c5567540870a61b04b1ef2e52113a41e2aa2ed52fead584b7ae07b1b766a2294b716d72031b003695c5bb357b670f782060c061dca3d4c44195770e946f2e2220ba8dba052fd50ea477cddc02539bcc0dd27588691aad1a60a4d73a45c045a2072b0210be660e6126619a47f0ff6667331e3796835ff00921b4cc1959bfec1f29b46b71596f289acc812d995bbea2cc30907565b595ce105aae83047a5ec99beb9a1e625b0ec67cb02b55baae21c80eb896855ed455c705b2537357182079cef5bcc0d786f7d8fa9ac1bd5584f4606bebc17976f57a246579376ad7a17f094e2c74d3309779c753594b289bd92a299d9c90d133ed3b3da22dcf96203bfacb91a4f3a25311391a6394349606cef88d450d13583f70fe21d3d12522ecddf86a428177a1ffa974cf7c7b94f99ce98459df2bea3de5a594fcb57bc1c8da736b5f56355001d2394de4a98939207458417f932bdd0e0e7a912f59d4416463a431d91fba4a4175a2c1e944b0019b98bbbe59e92c03185ea57c42377bb35db6f599f94ddb5e35f88521906834a8697e2f8952eb776e59c1e8aa0063ab6a2313bca39dccf5992576bc4c98992f787583fef35c882a5568c66dbdcd3ac3a4f0f6a2cd800cef157c73a894023632eb4ec9adc903b61a13464c7fa4724c2ef760f4a84501bce6bbe95d2a5be60f80d0afa4e4f185b46689cf931f48a966a15294ead81c5a12fb300de6b9c034c85c1dcb4e07f88f56ef0b274b076bfb05683bfd91d9385575210c0e693fd828a655d2774d4082e2384c6d95e9ac0d2c4a1393e6251da2a62a5e933701152ac6b0d63f932893d5f334016eb9b66f8ae54355d71841434757594bc13a105349cab82d50e2159103d732e1f0412caaee2df129b4bda89aa0bba1820bc128d2d34469d96556d78b29dcfaf10500d8d4768ae75950b3aff00653df487c66d03d0d3d9a857573b1699878188ec8aeb8afe4b1c65d4afe44aac6388b32d9ef09fddb940547aa2547a0c5ff22ad5f8a993cbab11df3d2059b5cd52b8dc2a2b8a7b5cbebac0344b653fd7b4fcc3040d7da5ec31b511a354f485628f698323bcc1ba41bc5ab6c3a47343e667ff0053d5e61b6b2cd867172ce8669a3f29ddaed2987eb8ecede950c1419a2f778ad7d2295a2ac17fe7c8e91b809ca05f9e60bbefa8c07442373d940b457a2d434bc81228a363473c890a027e0a77867b403687c40e21569003680da148a911624c0dd4ec0378d98e2b7414060c9a3d21193b42ea8800e318d752049c16669b4b21c265340421893b688200a297773a7d915585f2bc86837126854522aaa5853a73156e8c13b719d5db58db48fc2e580303d1bdc373f4007be66a63497de7c4ae908b66ac02dec6acb4d91ffd66105dd7a47d37f58c1ff72994aa07a3e65052ed2c6bac082748028c05b2c6703656e5f766a94ed89aca33b2225343d51993763116455cff00a965ee07c4d95d8a9caaea98eb9ba5460c11ea88f68705be66bcbecd7c4bdb429be5f328500703510d47bb2c70778441d473a5bb9fd3312c0e32ec7b3bc261441013848f246bc96de8ebee270e904bf537f48f6e9f385683ed51132bdab10cb57894b67d18d927a309b41dc60d82a60a4a778ada8c3a4bc651da297abd623b7a4ad65742e216e598e51a8adfb83156c2b9d209da02aafe22361ce2e296e7360b6a01bad4a13cca6acf27b08d51464043bd34f467ac50341cae203ac38d03d0d5f699ad60ab52d7f0e8416f004e5ee2272fad037fcc83381e9894688eccc15907ac56da8c438ac7b42ebb04fbc7c1f22f461a5970a7c5ca5ec951f0c67218e6a1ac3485b59d211818ed36cc050a1838a3887a680b42b16e68db3b6f0800200495839698b73d659252c36b95ab586c402b2dfb8eb0aa7de4b15e9bf582ec9da3ea1544bce01ca0547d83d0bf1180eef3f350b627b243ee1ded4bf797221701ed52b968b72f5ef2b1518e9bf72959b5f47cc006f625e9288dcb05c362958af4189428b86dad5b5302b4bf31d297cd532892870eb12d7282d7c41b59d41994b17f4a45b4770b7c7cb32647a4752f6659a177656b1ea95681eb2fd1fa5b3ac4da0a0ea21982945b89b3b3f70869239c5277e22f61b775d4dc7b4b584ddd3c6e767cc618340a3d1f9d217fe2e256f12d05fa4552871dd000031bb51ad19748ecd1e2a51dafc21b7c1c35f30acb5d6e44dbd0dc63a5abb402cb82dd83e91468876209dc49655506d6424ce3c504a3bbb4caa2aad6f495cfbf37fa26be82002e468973a277cbc33504df00e6b4ee74f99d4094ba5d0feb99415490069f12f6ea4bb93bcff4305cc2e351e928e814e17dc5dbd4801b02e88456c206765ed00ff36f100ba12eadab95534555d50fbdd83f241683f1d20759debf5428cf81314c54c03b7d711553d8be09aafa28f897b9be4656f25f7cc0990257495d095e913a41379a6a5d2031d32f4ef058ba58c36fac71b4aa0655c01ab1908fe48eaefc77d1416b650557b4b2b50aed2a9d2059a43181eb59801b1d9d616d2c6cab10d9ec665d517dff001e929bdbba9ce3d598b10a4ca7d494fdd1ff00d2953a9e194dafb5a21654d8a4ff003a41dec907736fda412af5366e5b021831dbd47c4a434e6a8f85dc788f5b21b1c3c4269d62e87558f181aef1014faee54ea9b23d72c719ef1a741beb98b6cdf1ac0acf731704bb4c7685300dc8fa3105a3eac68d2f18800a88fe2ea56753b07d755e80c6f12c885fe1f56e0a06d4a3e881abba15a9db63ab88198e26d7bee7b7bca00000501820b7b81cbde03d60b88077f48c4fa0a2592e693b6251b537c8841f71434083ff004d717fc6a9bcac4206257fc14c4ff958cca9512a2448cc12668ad12d858e9996c0574bd5c153355628f55a0f561377807d1b70f7960e7dcae97b1d0941410acd6fccd3cadcb487565d65c946dff150b0a8e9bb629d633a23b687b4e67cf1d4cbdd4d67e4cacfe50daf1c0b69ed1e15e204606f1558b8a8ce7fe20d83dcdddcda6502fd0723d9dfe7a10ab8bbf7230d33455fc5ea964ded8c073addda758c75699a72fb6b1077230840ed3828c4f55dc4b2b1f115b922d047a520dd7dc06f4faca4c1ed72dd5f4886d8d358a508695b7d747a6b0eda59a37ae7d5518c24a0a00ec63c4088071adbd0dde80c47a9f77ff01ef09430b762daf2e730aae97ddfb9baf7b01302fbb0de1e59d2797ee234afabf712db7ddfb88d4bd5fb8d7927abf70a3eefdc7b3eefdc2e9bdf57ee1a40bab1f98554737dc58ceb341ff0ff00e05c21affc349ac0950254495cc7fe16620cad7200138ab3de382aee527d0260086eb7cda1208b5b4a941511f17695af6cb6f899db06e2bda350d089bab9637b9aa34959c47d6676951a64dc82a1d3ab923ffa5fb9eb3bbf71abecfdcfc57dcd0d7eac7ff63f713bfedfb9c1ee7ee2da1f2fdcc3f67ee74be58f17cb2b8070db00daaf732765d7b3e618a9c611295c26a410dd964533fcae67a8f92a2ca0da5afa9b3d1af58e5d4ef296824535f08b7f6582de8f9ff826acf682724be358c06eae1bdc7f90d3d4c5d8d5c27b8cfb3d608f0a081e84305b0544e9874fb2f5f88497a6b52f6360ed340ed293803bc58f962dbb7bac04e87aa6f5719ab3f47dca7a5d8c5bafb04a4d48cf4616651fab05a405cbfec24a89e06d630e80da36bd3da5d970c56ffc0ed2f78a3c4599b11c5985acac4212e5cbff008b162c51eabb7a45de95a28226e11b66baa363a6bf11cb760b8ba882328047341fd9ee8987b54d60b96deec24034e209cd4eec1d22711389ab4c443888bd2238f789b57bc55684c18bd87c903808ef514e9e234563d5ff00054bd18e0f569891abc275ceff00ee3fef2623eedcb2fb252753fde90daf4a52c7c5014a263541c753d20c1b6298f5e3e201046ce49434251589c26fda3abfce7b7675f71da71d1816750e83b3329437eb15b04142ab3d639e095406330d619a58e2e3e0c07566242b3fccfc7940ca3421e79eec16f9beb33549c20e0f57f30c09069b3d8dfbb98352ac986be200ca1dd81ad9fa9321775e90a30ed29d3eb901cf94451c07e7133e1e8cc990ec7ee5beaa25ce49d5468cae72a1b94b5d65b97e881ca1317e685f3c2f090f5c53454bc8d9f9ed995f3093ac7798398b5851660a906f5562975065c1832e5cb6a6809dd8637762708776e6b4ba0622160d2bbeeba0778da16b6b7f5e5d5f4098c0c7cc574b7280dd76a8c10652282f436ed1a6d1e2881c53035b313884ed1e8958980180e20e6ea3328698e77990051559a1b7fc929a7828ae2aea469a54ea0c7657bfd200317f563c8787cc4757e818adce3692778b8c04134cbd66ae965e7314db2e9a5fb444475942fa07c9ea4c5d641184e89860bcbce8cd71e58de8ecf681fe70b77b28f563715d2c1ed12ebe41f72353f7789a357f1c44a2db7772f6cce67c867d4baaf566683f26f50d5c38caff009e59ddc66d3ddc7831de18010c5181db89cf9b8a3f907d7d560336f75606fb086987d217a05762a51f2cb1c8cbd1d22e64ec5cb6e3b950c22900db1bb7ab9ccfd112ff000135b47aeb1a08f152acc552f4f69a2600677ad7de12d9585160e1309fae316b8dabebafabcc0cf7a258fac508ecb28da077bf30eb4399f1090e78736675df11d917a446ca3b45d6e7343b13525eb197aba21b7c137fc77bfda02ce8ea15f57566e4d107ef7b957d4e91ad6b784edcb2b8b6e754a098a24a6f286d10c5dd098cd51397fe0d56621623bc404aa6f0c763c0ad5b6635ec77da6d8aeca4c5c03bdfcc41c5bb2583ee88616ba246864bd51b3357acbcc5dde5ab52f4ccd198ec7cc346ef35fcfb96ecd7b1373af573ef287a65150406075175874174b54e34eeb179798bcbe656a01c0cfec198f37ee2c97e4fdc5e9e5fdc33086938b0428c8f5b4a6363a15f118d2ebba18ac7ae24a5bca02eaf29f69e59ac8099bf76fecc00bb350731e4413ecc665a20255c06f2cf6d5198777982b1f2c3f90112ca33a8b068d8cc302aac6197c5367a6b36621a52092c55b3957beded0d136c5afc74982abc99e30ca03f7331337d90ca8a4ec8ccdb87c4ec7c4196dd53e23c17888d40eed4d2d77137bd742cb07a70e7cff26d8791f84b15decfcb306ff5a73c0a84b71c8a7cc03a54532db99446fd2f718a1fae45d5f880151500a0ed0759a4a3be62ccb69a3483bc43689d227583accdb6e223923510ed9bc41488a8075392085afaa2b0ecdd2aeb06c83b35f105a33be14d59ea87fbc8afdc98b5794ffdeca9cdfbac4046794af96006a8380c459cfccb7fc5e63ccc90f5976eb58688bb087d08c34d9e85b01f67ea075f3bea7fe87ea0583c9fa843566b2638c399a73513ddbf135a1aa6a82c25b95329b916158b2d874ee7cc717b04e83a10fbc8e554c540d0cbb1aeb00a00762526bc6f2925a689eb37226d956e650704d5eb09439134627586dc4ba93a93fb2e8d869475ef2eec3f3cc44293f8d60b6d7eb99a35fe79888565ff00ea3140279adcc4095a02102acf58056928ff008e93665d292e9d5d0704168e38d88ac18eb1d7feea9a3fe390c5ccde3acd0cd4c752047562d8f6b6192646d3c177affc210d21ff0018ff00f03a7fc33427be9a16e40283b3fec186b5c0dfb0c101ac802c16bbc1222b425b754e3a1b24ffd9);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `zone_id` int(11) NOT NULL,
  `logistics_id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `areas_covered` text NOT NULL,
  `base_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additional_per_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','image','json','array') DEFAULT 'text',
  `setting_group` enum('general','categories','about','contact','social','features','hero') DEFAULT 'general',
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'J3RS Shop Co.', 'text', 'general', 1, '2026-05-06 16:11:13', '2026-05-06 16:32:51'),
(2, 'site_logo', 'uploads/logo_1778056371.png', 'image', 'general', 2, '2026-05-06 16:11:13', '2026-05-06 16:32:51'),
(3, 'site_favicon', 'favicon.ico', 'image', 'general', 3, '2026-05-06 16:11:13', NULL),
(4, 'default_language', 'English (US)', 'text', 'general', 4, '2026-05-06 16:11:13', '2026-05-06 16:32:51'),
(5, 'default_currency', 'PHP (₱)', 'text', 'general', 5, '2026-05-06 16:11:13', '2026-05-06 16:32:51'),
(6, 'timezone', 'Asia/Manila', 'text', 'general', 6, '2026-05-06 16:11:13', '2026-05-06 16:32:51'),
(7, 'hero_title', 'Curated essentials for <br> <span>modern living.</span>', 'textarea', 'hero', 1, '2026-05-06 16:11:13', '2026-05-06 16:35:16'),
(8, 'hero_subtitle', 'Discover our handpicked selection of premium products designed to elevate your everyday experience. Quality meets aesthetics.', 'textarea', 'hero', 2, '2026-05-06 16:11:13', '2026-05-06 16:35:16'),
(9, 'hero_button_text', 'Shop Collection \Z', 'text', 'hero', 3, '2026-05-06 16:11:13', '2026-05-06 16:35:16'),
(10, 'hero_background_image', 'labg.jpg', 'image', 'hero', 4, '2026-05-06 16:11:13', NULL),
(11, 'categories', '[\"All\",\"Women\",\"Men\"]', 'json', 'categories', 1, '2026-05-06 16:11:13', '2026-05-06 16:32:01'),
(12, 'about_title', 'About J3RS Shop Co.', 'text', 'about', 1, '2026-05-06 16:11:13', '2026-05-06 16:35:02'),
(13, 'about_content', 'J3RS Shop Co. is an online clothing store dedicated to bringing you stylish, trendy, and affordable fashion for everyday wear. We aim to make it easy for everyone to express their style without spending too much.<br><br>Our mission is to provide high quality clothing inspired by the latest trends while keeping prices accessible. We continuously update our collections to ensure you always have something new to discover.<br><br>At J3RS Shop Co., we value customer satisfaction. That\'s why we offer fast and reliable shipping, easy returns, and a smooth shopping experience from browsing to checkout.<br><br>Whether you\'re looking for casual outfits, statement pieces, or everyday essentials, J3RS Shop Co. is here to help you find your perfect style.', 'textarea', 'about', 2, '2026-05-06 16:11:13', '2026-05-06 16:35:02'),
(14, 'contact_email', 'support@j3rsshopco.com', 'text', 'contact', 1, '2026-05-06 16:11:13', NULL),
(15, 'contact_phone', '+63 912 345 6789', 'text', 'contact', 2, '2026-05-06 16:11:13', NULL),
(16, 'contact_location', 'Philippines, Pasig City', 'text', 'contact', 3, '2026-05-06 16:11:13', NULL),
(17, 'contact_phone_hours', 'Mon-Sat, 9AM - 6PM', 'text', 'contact', 4, '2026-05-06 16:11:13', NULL),
(18, 'contact_email_response', 'We\'ll respond within 24 hours', 'text', 'contact', 5, '2026-05-06 16:11:13', NULL),
(19, 'facebook_url', 'https://facebook.com/j3rsshopco', 'text', 'social', 1, '2026-05-06 16:11:13', NULL),
(20, 'instagram_url', 'https://instagram.com/j3rsshopco', 'text', 'social', 2, '2026-05-06 16:11:13', NULL),
(21, 'twitter_url', 'https://twitter.com/j3rsshopco', 'text', 'social', 3, '2026-05-06 16:11:13', NULL),
(22, 'tiktok_url', 'https://tiktok.com/@j3rsshopco', 'text', 'social', 4, '2026-05-06 16:11:13', NULL),
(23, 'features', '[{\"icon\":\"\\ud83c\\udd93\\ud83d\\ude9a\",\"title\":\"Free Shipping\",\"description\":\"On all orders over ?2,000. Fast and reliable delivery nationwide.\"},{\"icon\":\"\\ud83d\\udd10\",\"title\":\"Secure Payments\",\"description\":\"Your transactions are protected with strong encryption.\"},{\"icon\":\"\\ud83d\\udd04\",\"title\":\"Easy Returns\",\"description\":\"Return items within 30 days for a full refund, no questions asked.\"}]', 'json', 'features', 1, '2026-05-06 16:11:13', '2026-05-06 16:36:14'),
(24, 'why_choose_us', '[{\"icon\":\"\\ud83c\\udff7\\ufe0f\",\"title\":\"Affordable Prices\",\"description\":\"Premium quality at prices everyone can enjoy\"},{\"icon\":\"\\ud83d\\ude9a\",\"title\":\"Fast & Reliable Shipping\",\"description\":\"Quick delivery right to your doorstep\"},{\"icon\":\"\\ud83d\\ude0e\",\"title\":\"Trendy Styles\",\"description\":\"Always updated with latest fashion trends\"}]', 'json', 'about', 3, '2026-05-06 16:11:13', '2026-05-06 16:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `max_login_attempts` int(11) NOT NULL DEFAULT 3,
  `password_min_length` int(11) NOT NULL DEFAULT 12,
  `require_uppercase` tinyint(1) NOT NULL DEFAULT 1,
  `require_lowercase` tinyint(1) NOT NULL DEFAULT 1,
  `require_number` tinyint(1) NOT NULL DEFAULT 1,
  `require_special_char` tinyint(1) NOT NULL DEFAULT 1,
  `session_timeout_minutes` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `max_login_attempts`, `password_min_length`, `require_uppercase`, `require_lowercase`, `require_number`, `require_special_char`, `session_timeout_minutes`) VALUES
(1, 3, 12, 1, 1, 1, 1, 30);

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
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `first_name`, `last_name`, `email`, `password`, `otp_code`, `otp_expiry`, `verification_token`, `token_expiry`, `is_activated`, `mfa_secret`, `last_failed_login`, `attempts`, `is_locked`, `role_id`, `is_active`, `created_at`) VALUES
(1, 'admin', NULL, NULL, 'antonio_rhoannenicole@plpasig.edu.ph', '$2y$10$LdRoPWBEOsVfBfmTI3GO5.1JMD/eYXapDUljcxJtQN4G6ji0VtFfG', '', '2026-04-22 14:54:03', '', '2026-04-22 14:54:03', 1, 'NPHOGQXCEBULYTDN', '2026-04-22 20:55:17', 0, 0, 1, 1, '2026-05-03 10:46:33'),
(6, 'test', NULL, NULL, 'n0305933@gmail.com', '$2y$10$.Y40Fpy2G.188piUPEVFzO.pd6QJD9rukbiTbHaAfBKyYYq0hXG6m', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '3DBWVGAURVBVXHJM', '2026-04-19 22:07:08', 0, 0, 2, 1, '2026-05-03 10:46:33'),
(7, 'seller', NULL, NULL, 'testsubjectschool155@gmail.com', '$2y$10$pGCiO7zY1RaL.xixVgU51edLMxbuomIIVdvUbfnLIEoPHDwF4mq5y', '', '2026-04-20 17:11:55', '', '2026-04-20 17:11:55', 1, 'QVNQBCXKNKHOKB4A', '2026-04-20 23:12:40', 0, 0, 3, 1, '2026-05-03 10:46:33'),
(11, 'customer', NULL, NULL, 'n42710140@gmail.com', '$2y$10$AzlQdqE3kZLviGUOCOlgiu6aB..qABPjcXFzEOgn3o2Z5ASem0Rqe', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '', '2026-04-23 15:59:21', 0, 0, 2, 1, '2026-05-03 10:46:33'),
(18, 'jean', 'Jojana', 'Jean Baglan Garabillo', 'garabillo_jojanajean@plpasig.edu.ph', '$2y$10$HZ1Tcng4oooB7QDMAS/uN.3B2msIJqrJwgIPJS6BeHXDwV8U04iA2', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'UEEQ52YZDW3QGUMU', NULL, 0, 0, 4, 1, '2026-05-03 10:46:33'),
(19, 'pamcustomer', NULL, NULL, 'pam066198@gmail.com', '$2y$10$Zpne9C8MEjTnL46PSOVfO.CQHSuxEPNXu3LSVVZKbPisS9f7.Pbom', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '4AP3DFAQT5SNFFQO', '2026-04-23 18:57:23', 0, 0, 2, 1, '2026-05-03 10:46:33'),
(20, 'leonorrivera', 'Leonor', 'Rivera', 'ruberducky032518@gmail.com', '$2y$10$HZ1Tcng4oooB7QDMAS/uN.3B2msIJqrJwgIPJS6BeHXDwV8U04iA2', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, '4FUCJJRBO4TIAUS7', '2026-04-25 08:22:01', 0, 0, 3, 1, '2026-05-03 10:46:33'),
(23, 'JJRS', NULL, NULL, 'logistics@example.com', '$2y$10$SvIFRXNS0yCLCRMQr4Ej..1sPudPdQG9pzM5ruJuTDZVhweoChYkK', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'WL2ZQOXBWN4QX74V', NULL, 0, 0, 5, 1, '2026-05-03 10:46:33'),
(38, 'pat.lacerna682', 'Pat', 'Lacerna', 'opat09252005@gmail.com', '$2y$10$mUXv4PgjJjj8LO3hLy4CauZ6P8EDSVe3P3yRwqyoerxgHAfohUsq6', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'OMRZEXRM5SQP2T7V', '2026-05-07 15:42:22', 0, 0, 6, 1, '2026-05-07 15:42:22'),
(39, 'GMA', NULL, NULL, 'janajean925@gmail.com', '$2y$10$IOJsAyQTbf.ZhFk9CoyShuaO5Bnc1tHAuxlu5VatGHDaKIrW1MxN6', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'V4QXUZ35NYG5WGCF', '2026-05-07 17:10:06', 0, 0, 5, 1, '2026-05-07 17:10:06'),
(41, 'bellaboo', NULL, NULL, 'jojanajeangarabillo@gmail.com', '$2y$10$iVeBTZchDURAkqsbDxZFzOh0pG.uJxF9fRp7zPFDeMHcWuSsgIZ2a', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 'EBQSVHH3MZMAGOSO', '2026-05-07 19:19:29', 0, 0, 4, 1, '2026-05-07 19:19:29');

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
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`courier_id`),
  ADD KEY `logistics_id` (`logistics_id`);

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
  ADD KEY `idx_logistic_user` (`logistic_user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `logistics_id` (`logistics_id`);

--
-- Indexes for table `driver_assignment`
--
ALTER TABLE `driver_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `logistic_user_id` (`logistic_user_id`);

--
-- Indexes for table `inventory_restock`
--
ALTER TABLE `inventory_restock`
  ADD PRIMARY KEY (`restock_id`),
  ADD KEY `idx_restock_variant` (`variant_id`),
  ADD KEY `idx_restock_seller` (`seller_id`);

--
-- Indexes for table `locked_accs`
--
ALTER TABLE `locked_accs`
  ADD PRIMARY KEY (`locked_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `idx_login_history_user` (`user_id`),
  ADD KEY `idx_login_history_status` (`status`);

--
-- Indexes for table `logistics`
--
ALTER TABLE `logistics`
  ADD PRIMARY KEY (`logistics_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

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
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD KEY `logistics_id` (`logistics_id`);

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
  ADD UNIQUE KEY `uk_seller_user_id` (`user_id`),
  ADD KEY `idx_business_category` (`business_category`),
  ADD KEY `idx_age` (`age`),
  ADD KEY `idx_is_approved` (`is_approved`);

--
-- Indexes for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD KEY `logistics_id` (`logistics_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_setting_group` (`setting_group`);

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
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `courier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `delivery_tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `driver_assignment`
--
ALTER TABLE `driver_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_restock`
--
ALTER TABLE `inventory_restock`
  MODIFY `restock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locked_accs`
--
ALTER TABLE `locked_accs`
  MODIFY `locked_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `logistics`
--
ALTER TABLE `logistics`
  MODIFY `logistics_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `security_settings`
--
ALTER TABLE `security_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
-- Constraints for table `couriers`
--
ALTER TABLE `couriers`
  ADD CONSTRAINT `couriers_ibfk_1` FOREIGN KEY (`logistics_id`) REFERENCES `logistics` (`logistics_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `delivery_tracking_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  ADD CONSTRAINT `fk_delivery_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delivery_updated_by_user` FOREIGN KEY (`updated_by_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `driver`
--
ALTER TABLE `driver`
  ADD CONSTRAINT `driver_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_ibfk_2` FOREIGN KEY (`logistics_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `driver_assignment`
--
ALTER TABLE `driver_assignment`
  ADD CONSTRAINT `driver_assignment_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  ADD CONSTRAINT `driver_assignment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `driver_assignment_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `driver_assignment_ibfk_4` FOREIGN KEY (`logistic_user_id`) REFERENCES `user` (`user_id`);

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
-- Constraints for table `logistics`
--
ALTER TABLE `logistics`
  ADD CONSTRAINT `logistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`logistics_id`) REFERENCES `logistics` (`logistics_id`) ON DELETE CASCADE;

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
-- Constraints for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD CONSTRAINT `shipping_zones_ibfk_1` FOREIGN KEY (`logistics_id`) REFERENCES `logistics` (`logistics_id`) ON DELETE CASCADE;

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
