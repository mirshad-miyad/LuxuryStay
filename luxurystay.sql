-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 10:44 AM
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
-- Database: `luxurystay`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `address`, `profile_image`, `password`, `created_at`) VALUES
(1, 'System Admin', 'admin@luxurystay.lk', '', '', NULL, '$2y$10$NzMC07ObRKHPu1V15wYSheynfYw/E7ucyxl7eyHwD3biPAm3QDCwC', '2026-08-05 15:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-check'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `icon`) VALUES
(1, 'WiFi', 'bi-wifi'),
(2, 'Pool', 'bi-water'),
(3, 'AC', 'bi-snow'),
(4, 'Parking', 'bi-car-front'),
(5, 'Breakfast', 'bi-cup-hot'),
(6, 'Beach access', 'bi-umbrella'),
(7, 'Spa', 'bi-heart-pulse'),
(8, 'Restaurant', 'bi-shop'),
(9, 'Gym', 'bi-bicycle'),
(10, 'Room Service', 'bi-bell');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `property_id`, `check_in`, `check_out`, `guests`, `total_amount`, `status`, `payment_status`, `payment_method`, `special_requests`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-08-12', '2026-08-15', 2, 135000.00, 'confirmed', 'paid', 'card', NULL, '2026-08-05 15:31:47', '2026-08-05 15:31:47'),
(2, 1, 7, 5, '2026-07-06', '2026-07-09', 2, 105000.00, 'completed', 'paid', 'bank', NULL, '2026-08-05 15:31:47', '2026-08-05 15:31:47'),
(3, 2, 8, 6, '2026-08-19', '2026-08-21', 2, 24000.00, 'pending', 'paid', 'card', NULL, '2026-08-05 15:31:47', '2026-08-05 15:31:47'),
(4, 1, 1, 1, '2026-08-12', '2026-08-15', 2, 135000.00, 'confirmed', 'paid', 'card', NULL, '2026-08-05 15:31:48', '2026-08-05 15:31:48'),
(5, 1, 7, 5, '2026-07-06', '2026-07-08', 2, 70000.00, 'completed', 'paid', 'bank', NULL, '2026-08-05 15:31:48', '2026-08-05 15:31:48'),
(6, 2, 3, 3, '2026-08-19', '2026-08-21', 2, 130000.00, 'pending', 'paid', 'card', NULL, '2026-08-05 15:31:48', '2026-08-05 15:31:48'),
(7, 1, 13, 11, '2026-08-21', '2026-08-22', 2, 85000.00, 'pending', 'pending', NULL, '', '2026-08-20 14:59:28', '2026-08-20 14:59:28'),
(8, 1, 13, 11, '2026-08-21', '2026-08-22', 2, 85000.00, 'pending', 'pending', NULL, '', '2026-08-20 15:05:29', '2026-08-20 15:05:29'),
(9, 1, 6, 4, '2026-08-21', '2026-08-22', 2, 150000.00, 'completed', 'paid', 'card', '', '2026-08-20 15:06:32', '2026-08-20 15:18:48'),
(10, 4, 2, 1, '2026-08-27', '2026-08-28', 2, 120000.00, 'completed', 'paid', 'card', '', '2026-08-25 15:17:50', '2026-08-25 15:20:16'),
(11, 4, 2, 1, '2026-08-27', '2026-08-28', 2, 120000.00, 'completed', 'paid', 'card', '', '2026-08-25 16:23:46', '2026-08-25 16:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `featured_destinations`
--

CREATE TABLE `featured_destinations` (
  `id` int(11) NOT NULL,
  `district` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `featured_destinations`
--

INSERT INTO `featured_destinations` (`id`, `district`, `title`, `description`, `image_path`, `sort_order`) VALUES
(1, 'Colombo', 'Colombo City', 'Experience the vibrant capital with luxury hotels and colonial charm.', 'assets/images/destinations/colombo.jpg', 1),
(2, 'Kandy', 'Kandy Hills', 'Sacred city surrounded by misty mountains and tea plantations.', 'assets/images/destinations/kandy.jpg', 2),
(3, 'Galle', 'Galle Fort', 'UNESCO heritage coastal fortress with boutique stays.', 'assets/images/destinations/galle.jpg', 3),
(4, 'Mirissa', 'Mirissa Beach', 'Golden beaches and whale watching on the south coast.', 'assets/images/destinations/mirissa.jpg', 4),
(5, 'Ella', 'Ella Mountains', 'Scenic hill country with iconic Nine Arch Bridge views.', 'assets/images/destinations/ella.jpg', 5),
(6, 'Nuwara Eliya', 'Little England', 'Cool climate tea country with colonial elegance.', 'assets/images/destinations/nuwaraeliya.jpg', 6);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `owner_id`, `admin_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, NULL, NULL, 1, 'New Booking Request', 'Booking #7 for Weligama Bay Boutique Stay', 'info', 0, 'http://localhost/LuxuryStay/admin/bookings.php', '2026-08-20 14:59:28'),
(2, NULL, 1, NULL, 'New Booking', 'You have a new booking request for Weligama Bay Boutique Stay', 'booking', 0, 'http://localhost/LuxuryStay/owner/bookings.php', '2026-08-20 14:59:28'),
(3, 1, NULL, NULL, 'Booking Created', 'Your booking #7 is awaiting confirmation.', 'info', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-20 14:59:28'),
(4, NULL, NULL, 1, 'New Booking Request', 'Booking #8 for Weligama Bay Boutique Stay', 'info', 0, 'http://localhost/LuxuryStay/admin/bookings.php', '2026-08-20 15:05:30'),
(5, NULL, 1, NULL, 'New Booking', 'You have a new booking request for Weligama Bay Boutique Stay', 'booking', 0, 'http://localhost/LuxuryStay/owner/bookings.php', '2026-08-20 15:05:30'),
(6, 1, NULL, NULL, 'Booking Created', 'Your booking #8 is awaiting confirmation.', 'info', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-20 15:05:30'),
(7, NULL, NULL, 1, 'New Booking Request', 'Booking #9 for Cape Weligama', 'info', 0, 'http://localhost/LuxuryStay/admin/bookings.php', '2026-08-20 15:06:32'),
(8, NULL, 2, NULL, 'New Booking', 'You have a new booking request for Cape Weligama', 'booking', 0, 'http://localhost/LuxuryStay/owner/bookings.php', '2026-08-20 15:06:32'),
(9, 1, NULL, NULL, 'Booking Created', 'Your booking #9 is awaiting confirmation.', 'info', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-20 15:06:32'),
(10, 1, NULL, NULL, 'Payment Received', 'Payment for booking #9 received.', 'success', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-20 15:12:08'),
(11, 1, NULL, NULL, 'Booking Completed', 'Your booking #9 has been completed.', 'booking', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-20 15:18:48'),
(12, NULL, NULL, 1, 'New Booking Request', 'Booking #10 for Cinnamon Grand Colombo', 'info', 0, 'http://localhost/LuxuryStay/admin/bookings.php', '2026-08-25 15:17:50'),
(13, NULL, 1, NULL, 'New Booking', 'You have a new booking request for Cinnamon Grand Colombo', 'booking', 0, 'http://localhost/LuxuryStay/owner/bookings.php', '2026-08-25 15:17:50'),
(14, 4, NULL, NULL, 'Booking Created', 'Your booking #10 is awaiting confirmation.', 'info', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 15:17:51'),
(15, 4, NULL, NULL, 'Payment Received', 'Payment for booking #10 received.', 'success', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 15:17:59'),
(16, 4, NULL, NULL, 'Booking Confirmed', 'Your booking #10 has been confirmed.', 'booking', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 15:20:14'),
(17, 4, NULL, NULL, 'Booking Completed', 'Your booking #10 has been completed.', 'booking', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 15:20:16'),
(18, NULL, NULL, 1, 'New Booking Request', 'Booking #11 for Cinnamon Grand Colombo', 'info', 0, 'http://localhost/LuxuryStay/admin/bookings.php', '2026-08-25 16:23:47'),
(19, NULL, 1, NULL, 'New Booking', 'You have a new booking request for Cinnamon Grand Colombo', 'booking', 0, 'http://localhost/LuxuryStay/owner/bookings.php', '2026-08-25 16:23:47'),
(20, 4, NULL, NULL, 'Booking Created', 'Your booking #11 is awaiting confirmation.', 'info', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 16:23:47'),
(21, 4, NULL, NULL, 'Payment Received', 'Payment for booking #11 received.', 'success', 1, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 16:23:49'),
(22, 4, NULL, NULL, 'Booking Confirmed', 'Your booking #11 has been confirmed.', 'booking', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 16:24:34'),
(23, 4, NULL, NULL, 'Booking Completed', 'Your booking #11 has been completed.', 'booking', 0, 'http://localhost/LuxuryStay/user/bookings.php', '2026-08-25 16:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `property_id`, `title`, `description`, `discount_percent`, `valid_from`, `valid_to`, `status`) VALUES
(1, 1, 'Weekend Special', '15% off weekend stays in Colombo', 15.00, '2026-08-05', '2026-11-03', 'active'),
(2, 6, 'Beach Getaway', '20% off 3+ night stays at Mirissa', 20.00, '2026-08-05', '2026-10-04', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `business_description` text DEFAULT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `email`, `phone`, `address`, `profile_image`, `password`, `company_name`, `business_description`, `status`, `created_at`) VALUES
(1, 'Kamal Perera', 'kamalperera@luxurystay.lk', '+94771234567', NULL, 'assets/images/default-avatar.svg', '$2y$10$sEqmxhuJalyeHyDIiB0A2.0ufBNTtgdHqomPHSiYGcIfExxdMA05O', 'Ceylon Hospitality Group', NULL, 'active', '2026-08-05 15:31:46'),
(2, 'Nimal Fernando', 'nimalfernando@luxurystay.lk', '+94772345678', NULL, 'assets/images/default-avatar.svg', '$2y$10$HbOdqDJMZ35kstb3tLb6G.v1IUy.xkQ00KM6SwfhwVYciF4mXeXfS', 'Paradise Resorts Ltd', NULL, 'active', '2026-08-05 15:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `role` enum('user','owner','admin') NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `role`, `expires_at`, `created_at`) VALUES
(2, 'mohamedmirshad8888@gmail.com', '$2y$10$N2SSVFdj21LR6H9GpcYSA.pvt6ta3kqewxry4Y4ePRv9u8ztUx2gG', 'user', '2026-08-20 20:21:28', '2026-08-20 13:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) NOT NULL,
  `property_type` enum('Hotel','Villa','Resort','Guest House') NOT NULL,
  `map_iframe` text DEFAULT NULL,
  `contact_phone` varchar(30) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `policies` text DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `owner_id`, `name`, `description`, `address`, `city`, `province`, `district`, `property_type`, `map_iframe`, `contact_phone`, `contact_email`, `latitude`, `longitude`, `policies`, `featured`, `status`, `is_active`, `deleted_at`, `avg_rating`, `review_count`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cinnamon Grand Colombo', 'Iconic 5-star luxury hotel in the heart of Colombo offering world-class dining, spa, and panoramic city views.', '77 Galle Road, Colombo 03', NULL, NULL, 'Colombo', 'Hotel', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.798!2d79.848!3d6.927!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', NULL, NULL, NULL, NULL, 'Check-in 2PM, Check-out 12PM. No smoking in rooms.', 1, 'approved', 1, NULL, 4.50, 3, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(2, 1, 'Heritance Kandalama', 'Geoffrey Bawa masterpiece nestled against a cliff overlooking the Kandalama reservoir.', 'Kandalama, Dambulla', NULL, NULL, 'Dambulla', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', NULL, NULL, NULL, NULL, 'Eco-friendly resort. Children welcome.', 1, 'approved', 1, NULL, 5.00, 1, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(3, 2, 'Jetwing Lighthouse Galle', 'Stunning clifftop resort within Galle Fort with infinity pool and ocean views.', 'Dadella, Galle', NULL, NULL, 'Galle', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', NULL, NULL, NULL, NULL, 'Beach access. Pets not allowed.', 1, 'approved', 1, NULL, 4.00, 1, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(4, 2, 'Cape Weligama', 'Exclusive cliff-top villa resort on the southern tip with private coves.', 'Weligama Bay, Weligama', NULL, NULL, 'Matara', 'Villa', 'https://www.google.com/maps/embed?pb=!1m18', NULL, NULL, NULL, NULL, 'Minimum 2 nights. All-inclusive options.', 1, 'approved', 1, NULL, 4.50, 2, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(5, 1, '98 Acres Resort Ella', 'Boutique resort on a tea estate with breathtaking Ella Gap views.', 'Ella, Badulla', NULL, NULL, 'Ella', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', NULL, NULL, NULL, NULL, 'Hill country weather - bring warm clothes.', 0, 'approved', 1, NULL, 5.00, 3, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(6, 2, 'Mirissa Hills Guest House', 'Charming family-run guest house steps from Mirissa beach.', 'Mirissa Road, Mirissa', NULL, NULL, 'Mirissa', 'Guest House', 'https://www.google.com/maps/embed?pb=!1m18', NULL, NULL, NULL, NULL, 'Breakfast included. Quiet hours 10PM-7AM.', 0, 'approved', 1, NULL, 4.00, 1, '2026-08-05 15:31:46', '2026-08-20 14:09:23'),
(7, 1, 'Galle Fort Courtyard Hotel', 'A refined heritage hotel inside Galle Fort, with elegant rooms and a peaceful inner courtyard.', '18, Church Street, Galle Fort', 'Galle', 'Southern', 'Galle', 'Hotel', NULL, '+94 91 224 5501', 'stay@gallecourtyard.lk', 6.02610000, 80.21700000, NULL, 0, 'approved', 1, NULL, 4.50, 2, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(8, 1, 'Mirissa Cliffside Villa', 'An intimate ocean-view villa with an infinity pool, tropical gardens and whale-watching access.', '42, Harbour Road, Mirissa', 'Mirissa', 'Southern', 'Matara', 'Villa', NULL, '+94 41 226 8190', 'hello@mirissacliffside.lk', 5.94910000, 80.47160000, NULL, 0, 'approved', 1, NULL, 5.00, 1, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(9, 1, 'Wilpattu Wilderness Camp', 'A comfortable safari camp on the edge of Wilpattu, designed for unforgettable wildlife escapes.', '25, Wilpattu Junction, Nochchiyagama', 'Wilpattu', 'North Western', 'Puttalam', 'Resort', NULL, '+94 32 225 7144', 'reservations@wilpattucamp.lk', 8.45540000, 80.06410000, NULL, 0, 'approved', 1, NULL, 4.00, 1, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(10, 1, 'Jaffna Lagoon House', 'A welcoming lagoon-side guest house where northern Sri Lankan culture and comfort meet.', '61, Lagoon View Road, Jaffna', 'Jaffna', 'Northern', 'Jaffna', 'Guest House', NULL, '+94 21 222 4678', 'stay@jaffnalagoon.lk', 9.66150000, 80.02550000, NULL, 0, 'approved', 1, NULL, 4.50, 2, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(11, 1, 'Weligama Bay Boutique Stay', 'A chic coastal retreat just steps from Weligama Bay, ideal for surf trips and relaxed getaways.', '8, Bay View Lane, Weligama', 'Weligama', 'Southern', 'Matara', 'Hotel', NULL, '+94 41 225 1092', 'book@weligamabay.lk', 5.97420000, 80.42980000, NULL, 0, 'approved', 1, NULL, 5.00, 1, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(12, 1, 'Knuckles Mountain Retreat', 'A secluded highland retreat with misty mountain views, guided hikes and fireside dining.', '16, Riverston Road, Matale', 'Matale', 'Central', 'Matale', 'Resort', NULL, '+94 66 224 7810', 'escape@knucklesretreat.lk', 7.53120000, 80.79460000, NULL, 0, 'approved', 1, NULL, 4.00, 1, '2026-08-20 14:04:40', '2026-08-20 14:09:23'),
(14, 1, 'Kalpitiya Kite Beach Resort', 'A breezy beach resort overlooking Kalpitiya Lagoon, perfect for kite-surfing and sunset escapes.', '34, Lagoon Drive, Kalpitiya', 'Kalpitiya', 'North Western', 'Puttalam', 'Resort', NULL, '+94 32 226 5090', 'stay@kalpitiyakite.lk', 8.23320000, 79.76670000, NULL, 0, 'approved', 1, NULL, 5.00, 1, '2026-08-20 14:06:22', '2026-08-20 14:09:23'),
(15, 1, 'Haputale Tea Garden Villa', 'A cosy villa surrounded by tea gardens, with valley views and freshly prepared local cuisine.', '10, Station Road, Haputale', 'Haputale', 'Uva', 'Badulla', 'Villa', NULL, '+94 57 226 4300', 'welcome@haputalevilla.lk', 6.76590000, 80.95180000, NULL, 0, 'approved', 1, NULL, 4.00, 1, '2026-08-20 14:06:22', '2026-08-20 14:09:23'),
(16, 1, 'Colombo Skyline Suites', 'Contemporary city suites with skyline views, rooftop dining and easy access to Colombo attractions.', '72, Galle Road, Colombo 03', 'Colombo', 'Western', 'Colombo', 'Hotel', NULL, '+94 11 245 7012', 'reservations@colomboskyline.lk', 6.90610000, 79.85390000, NULL, 0, 'approved', 1, NULL, 4.50, 2, '2026-08-20 14:06:22', '2026-08-20 14:09:23'),
(17, 1, 'Dambulla Lakeview Resort', 'A serene lakeside resort near Dambulla, offering spacious rooms, birdwatching and sunset dining.', '29, Kandalama Road, Dambulla', 'Dambulla', 'Central', 'Matale', 'Resort', NULL, '+94 66 228 4015', 'stay@dambullalakeview.lk', 7.87420000, 80.65110000, NULL, 0, 'approved', 1, NULL, 5.00, 1, '2026-08-20 14:07:36', '2026-08-20 14:09:23'),
(18, 1, 'Ratnapura Rainforest Lodge', 'An eco-friendly lodge surrounded by rainforest greenery, with guided walks and quiet river views.', '45, Forest Edge Road, Ratnapura', 'Ratnapura', 'Sabaragamuwa', 'Ratnapura', 'Guest House', NULL, '+94 45 223 6781', 'welcome@ratnapuralodge.lk', 6.68280000, 80.39920000, NULL, 0, 'approved', 1, NULL, 4.00, 1, '2026-08-20 14:07:36', '2026-08-20 14:09:23'),
(19, 1, 'Batticaloa Lagoon Villa', 'A relaxed private villa overlooking Batticaloa Lagoon, with fresh seafood and peaceful water views.', '14, Lagoon Park, Batticaloa', 'Batticaloa', 'Eastern', 'Batticaloa', 'Villa', NULL, '+94 65 222 9154', 'book@batticaloalagoon.lk', 7.72900000, 81.69760000, NULL, 0, 'approved', 1, NULL, 4.50, 2, '2026-08-20 14:07:36', '2026-08-20 14:09:23');

-- --------------------------------------------------------

--
-- Table structure for table `property_amenities`
--

CREATE TABLE `property_amenities` (
  `property_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_amenities`
--

INSERT INTO `property_amenities` (`property_id`, `amenity_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 8),
(1, 9),
(2, 1),
(2, 2),
(2, 3),
(2, 6),
(2, 7),
(2, 8),
(3, 1),
(3, 2),
(3, 3),
(3, 6),
(3, 7),
(3, 8),
(4, 1),
(4, 2),
(4, 3),
(4, 5),
(4, 6),
(4, 7),
(5, 1),
(5, 3),
(5, 5),
(5, 8),
(6, 1),
(6, 5),
(6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(1, 1, 'uploads/properties/1/106858444.jpg', 1, 0),
(2, 2, 'uploads/properties/img_6a340ff4af41e5.00617472.webp', 1, 0),
(3, 3, 'uploads/properties/stock-city-hotel.jpg', 1, 0),
(4, 4, 'uploads/properties/img_6a348a92ab12b8.17141509.jpg', 1, 0),
(5, 5, 'uploads/properties/98-acres-resort-ella.jpg', 1, 0),
(6, 6, 'uploads/properties/img_6a348b119e0d32.36687006.jpg', 1, 0),
(7, 1, 'uploads/properties/1/e93eb03e.avif', 0, 1),
(8, 1, 'uploads/properties/1/images (1).jpg', 0, 2),
(9, 1, 'uploads/properties/1/images (2).jpg', 0, 3),
(10, 1, 'uploads/properties/1/images.jpg', 0, 4),
(11, 1, 'uploads/properties/1/image_b6d0d60b2a.jpg', 0, 5),
(12, 1, 'uploads/properties/1/unnamed.webp', 0, 6),
(13, 7, 'uploads/properties/galle-fort-courtyard-hotel.jpg', 1, 0),
(14, 8, 'uploads/properties/3/img_6a4a9d040aabc4.05966654.jpg', 1, 0),
(15, 9, 'uploads/properties/9/img_6a4a9f0b799364.97217717.jpg', 1, 0),
(16, 10, 'uploads/properties/jaffna-lagoon-house.jpg', 1, 0),
(17, 11, 'uploads/properties/12/img_6a4a9ffa25b688.08760128.jpg', 1, 0),
(18, 12, 'uploads/properties/13/img_6a4b06f55657e9.01726926.jpg', 1, 0),
(20, 14, 'uploads/properties/14/img_6a4b0eb6d82814.84705688.jpg', 1, 0),
(21, 15, 'uploads/properties/15/img_6a4b2cb4eba5b9.39600898.jpg', 1, 0),
(22, 16, 'uploads/properties/10/img_6a4a99c9c2f0c3.04528371.jpg', 1, 0),
(23, 17, 'uploads/properties/1/images (1).jpg', 1, 0),
(24, 18, 'uploads/properties/1/images (2).jpg', 1, 0),
(25, 19, 'uploads/properties/batticaloa-lagoon-villa-v2.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recently_viewed`
--

INSERT INTO `recently_viewed` (`id`, `user_id`, `property_id`, `viewed_at`) VALUES
(1, 1, 11, '2026-08-20 15:05:44'),
(3, 1, 4, '2026-08-20 15:06:21'),
(4, 4, 2, '2026-08-25 15:16:19'),
(7, 4, 1, '2026-08-25 16:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `property_id`, `booking_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(1, 1, 5, 2, 5, 'Absolutely stunning views of Ella Gap. Tea estate atmosphere was magical!', 'approved', '2026-08-05 15:31:48'),
(2, 1, 1, 2, 5, 'Exceptional service and stunning city views. Highly recommended!', 'approved', '2026-08-05 15:31:48'),
(3, 1, 5, NULL, 5, 'The Ella Gap view from the cottage was breathtaking.', 'approved', '2026-08-05 15:31:48'),
(4, 1, 1, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(5, 1, 2, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(6, 1, 5, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(7, 1, 7, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(8, 1, 8, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(9, 1, 9, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(10, 1, 10, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(11, 1, 11, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(12, 1, 12, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(13, 1, 14, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(14, 1, 15, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(15, 1, 16, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(16, 1, 17, NULL, 5, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(17, 1, 18, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(18, 1, 19, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(19, 1, 3, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(20, 1, 4, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(21, 1, 6, NULL, 4, 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.', 'approved', '2026-08-20 14:09:22'),
(35, 2, 1, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23'),
(36, 2, 7, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23'),
(37, 2, 10, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23'),
(38, 2, 16, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23'),
(39, 2, 19, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23'),
(40, 2, 4, NULL, 5, 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.', 'approved', '2026-08-20 14:09:23');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(12,2) NOT NULL,
  `weekend_price` decimal(12,2) DEFAULT NULL,
  `max_guests` int(11) NOT NULL DEFAULT 2,
  `inventory` int(11) NOT NULL DEFAULT 1,
  `bed_type` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `property_id`, `name`, `description`, `price_per_night`, `weekend_price`, `max_guests`, `inventory`, `bed_type`, `status`) VALUES
(1, 1, 'Deluxe King Room', 'Spacious room with city view and king bed.', 75000.00, 85000.00, 2, 1, 'King', 'active'),
(2, 1, 'Executive Suite', 'Luxury suite with lounge and butler service.', 120000.00, 130000.00, 3, 1, 'King', 'active'),
(3, 2, 'Panoramic Room', 'Lake view room with private balcony.', 110000.00, 120000.00, 2, 1, 'Queen', 'active'),
(4, 2, 'Suite with Plunge Pool', 'Premium suite with private plunge pool.', 150000.00, 150000.00, 2, 1, 'King', 'active'),
(5, 3, 'Ocean View Room', 'Elegant room overlooking the Indian Ocean.', 115000.00, 125000.00, 2, 1, 'King', 'active'),
(6, 4, 'Cape Suite', 'Clifftop suite with private terrace.', 150000.00, 150000.00, 2, 1, 'King', 'active'),
(7, 5, 'Tea Estate Cottage', 'Cozy cottage surrounded by tea fields.', 55000.00, 65000.00, 2, 1, 'Double', 'active'),
(8, 6, 'Beach View Double', 'Comfortable double room with sea breeze.', 25000.00, 35000.00, 2, 1, 'Double', 'active'),
(9, 7, 'Deluxe Room', 'Comfortable stay with premium amenities.', 80000.00, 90000.00, 2, 2, 'King', 'active'),
(10, 8, 'Deluxe Room', 'Comfortable stay with premium amenities.', 95000.00, 105000.00, 2, 2, 'King', 'active'),
(11, 9, 'Deluxe Room', 'Comfortable stay with premium amenities.', 75000.00, 85000.00, 2, 2, 'King', 'active'),
(12, 10, 'Deluxe Room', 'Comfortable stay with premium amenities.', 35000.00, 45000.00, 2, 2, 'King', 'active'),
(13, 11, 'Deluxe Room', 'Comfortable stay with premium amenities.', 85000.00, 95000.00, 2, 2, 'King', 'active'),
(14, 12, 'Deluxe Room', 'Comfortable stay with premium amenities.', 65000.00, 75000.00, 2, 2, 'King', 'active'),
(16, 14, 'Deluxe Room', 'Comfortable stay with premium amenities.', 70000.00, 80000.00, 2, 2, 'King', 'active'),
(17, 15, 'Deluxe Room', 'Comfortable stay with premium amenities.', 55000.00, 65000.00, 2, 2, 'King', 'active'),
(18, 16, 'Deluxe Room', 'Comfortable stay with premium amenities.', 75000.00, 85000.00, 2, 2, 'King', 'active'),
(19, 17, 'Deluxe Room', 'Comfortable stay with premium amenities.', 70000.00, 80000.00, 2, 2, 'King', 'active'),
(20, 18, 'Deluxe Room', 'Comfortable stay with premium amenities.', 45000.00, 55000.00, 2, 2, 'King', 'active'),
(21, 19, 'Deluxe Room', 'Comfortable stay with premium amenities.', 80000.00, 90000.00, 2, 2, 'King', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `room_amenities`
--

CREATE TABLE `room_amenities` (
  `room_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_availability`
--

CREATE TABLE `room_availability` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `custom_price` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_images`
--

CREATE TABLE `room_images` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `profile_image`, `password`, `status`, `created_at`) VALUES
(1, 'John Silva', 'johnsilva@luxurystay.lk', '+94773456789', NULL, 'assets/images/default-avatar.svg', '$2y$10$oLFHGj6pVZeZ/1bHsM6xq.KPFS6J6RYo/aT8IGRLzxFXwwR2TIgGi', 'active', '2026-08-05 15:31:46'),
(2, 'Sarah Jayawardena', 'sarahjayawardena@luxurystay.lk', '+94774567890', NULL, 'assets/images/default-avatar.svg', '$2y$10$QjS2SZaZNeCNvbhtplGJn.mvb86eErvgPRug37VFEuQJqVwLngBSi', 'active', '2026-08-05 15:31:46'),
(4, 'user', 'user@gmail.com', '', NULL, NULL, '$2y$10$Vk6W3M9UWrdQmyRyCM3HV./ux63yU43inE7jgZRzh9zvCQrVXJDly', 'active', '2026-08-25 15:13:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `featured_destinations`
--
ALTER TABLE `featured_destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD PRIMARY KEY (`property_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_view` (`user_id`,`property_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD PRIMARY KEY (`room_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `room_availability`
--
ALTER TABLE `room_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room_date` (`room_id`,`date`);

--
-- Indexes for table `room_images`
--
ALTER TABLE `room_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `featured_destinations`
--
ALTER TABLE `featured_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `room_availability`
--
ALTER TABLE `room_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_images`
--
ALTER TABLE `room_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD CONSTRAINT `property_amenities_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `recently_viewed_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recently_viewed_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD CONSTRAINT `room_amenities_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_availability`
--
ALTER TABLE `room_availability`
  ADD CONSTRAINT `room_availability_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_images`
--
ALTER TABLE `room_images`
  ADD CONSTRAINT `room_images_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
