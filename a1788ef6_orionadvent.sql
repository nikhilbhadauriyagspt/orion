-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 14, 2026 at 12:22 AM
-- Server version: 8.0.46-37
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `a1788ef6_orionadvent`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','editor') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin Manager', 'admin@orionadvent.com', '$2y$10$KbcuH6/Wj3cJk/QFTVhaEu4zGEnoAfjfBmgtFb4TGD1ouhS0b.Zqa', 'superadmin', 'active', '2026-10-05 18:17:32', '2026-10-05 18:17:32');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `booking_code` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `package_title` varchar(255) NOT NULL,
  `travel_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('confirmed','pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `booking_type` enum('package','hotel','flight_inquiry','cruise') DEFAULT 'package',
  `advance_amount` decimal(10,2) DEFAULT '0.00',
  `balance_amount` decimal(10,2) DEFAULT '0.00',
  `payment_gateway` varchar(50) DEFAULT 'razorpay',
  `payment_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `booking_details` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `customer_name`, `customer_email`, `customer_phone`, `package_title`, `travel_date`, `amount`, `status`, `created_at`, `user_id`, `booking_type`, `advance_amount`, `balance_amount`, `payment_gateway`, `payment_id`, `order_id`, `booking_details`) VALUES
(1, 'GF-1001', 'Arjun Mehta', 'arjun.m@example.com', '+91 9876543210', 'Dubai Desert Safari & Burj Khalifa Experience', '2026-10-18', 1450.00, 'confirmed', '2026-10-05 18:17:32', NULL, 'package', 0.00, 0.00, 'razorpay', NULL, NULL, NULL),
(2, 'GF-1002', 'Sophia Martinez', 'sophia.m@gmail.com', '+1 555-0199', 'Bali Luxury Villa 7D/6N Tropical Getaway', '2026-11-02', 2100.00, 'pending', '2026-10-05 18:17:32', NULL, 'package', 0.00, 0.00, 'razorpay', NULL, NULL, NULL),
(3, 'GF-1003', 'David Wilson', 'david.w@corp.org', '+44 20 7946 0912', 'Swiss Alps Ski & Scenic Train Tour', '2026-12-15', 3850.00, 'confirmed', '2026-10-05 18:17:32', NULL, 'package', 0.00, 0.00, 'razorpay', NULL, NULL, NULL),
(4, 'GF-1004', 'Ananya Verma', 'ananya.v@yahoo.com', '+91 9123456780', 'Goa Beachside Sunset Resort & Watersports', '2026-10-25', 780.00, 'completed', '2026-10-05 18:17:32', NULL, 'package', 0.00, 0.00, 'razorpay', NULL, NULL, NULL),
(5, 'GFX-D0D69C4F', 'Nikhil singh', 'nikhilbhadauriya.gspt@gmail.com', '7317788940', 'Kashmir Paradise & Dal Lake Luxury Houseboat (2 Adults)', '2026-10-13', 29998.00, 'confirmed', '2026-10-06 16:35:30', 5, 'package', 8999.40, 20998.60, 'razorpay', 'PAY_1197B78573', NULL, '{\"package_id\":\"PKG-DB-1\",\"adults\":2,\"children\":0,\"per_person_price\":15000,\"advance_type\":\"percentage\",\"advance_percent\":30,\"special_notes\":\"\",\"gateway\":\"razorpay\",\"booked_at\":\"2026-10-06 16:35:30\"}'),
(6, 'HTL-9A3FCFBE', 'madhav', 'rajatkumarkhurja@gmail.com', '0987654321', 'Delta Residency - Deluxe Ocean View Room (1 Room, 2 Guests)', '2026-10-10', 4928.00, 'confirmed', '2026-10-07 05:25:51', 6, 'hotel', 1478.40, 3449.60, 'razorpay', 'PAY_A56CC694FB', NULL, '{\"hotel_id\":\"HTL-DB-7\",\"hotel_name\":\"Delta Residency\",\"room_name\":\"Deluxe Ocean View Room\",\"check_in\":\"2026-10-10\",\"check_out\":\"2026-10-12\",\"rooms_count\":1,\"guests_count\":2,\"advance_type\":\"percentage\",\"gateway\":\"razorpay\",\"booked_at\":\"2026-10-07 05:25:51\"}'),
(7, 'FLT-D65345FF', 'madhav', 'rajatkumarkhurja@gmail.com', '0987654321', 'Air India Express IX-1056 (DEL → BOM, 1 Pax, Flexi Plus)', '2026-10-30', 7218.00, 'pending', '2026-10-07 06:33:51', 6, 'flight_inquiry', 0.00, 7218.00, 'inquiry', NULL, NULL, '{\"airline\":\"Air India Express\",\"flight_number\":\"IX-1056\",\"origin\":\"DEL\",\"destination\":\"BOM\",\"departure_date\":\"2026-10-30\",\"passengers_count\":1,\"travel_class\":\"Flexi Plus\",\"quoted_fare\":7218,\"inquiry_date\":\"2026-10-07 06:33:51\"}'),
(8, 'GFX-6ABAEF74', 'Nikhil Singh Bhadauriya', 'nikhilbhadauriya.gsspt@gmail.com', '7317788940', 'Goa Beach Escape – 5 Days / 4 Nights (3 Adults)', '2026-10-17', 24000.00, 'confirmed', '2026-10-10 05:45:18', 7, 'package', 7200.00, 16800.00, 'razorpay', 'PAY_A72306773C', NULL, '{\"package_id\":\"PKG-DB-6\",\"adults\":3,\"children\":0,\"per_person_price\":15000,\"advance_type\":\"percentage\",\"advance_percent\":30,\"special_notes\":\"\",\"gateway\":\"razorpay\",\"booked_at\":\"2026-10-10 05:45:18\"}'),
(9, 'HTL-A8C51B50', 'Nikhil Singh Bhadauriya', 'nikhilbhadauriya.gsspt@gmail.com', '7317788940', 'Delta Residency - Deluxe Ocean View Room (1 Room, 2 Guests)', '2026-10-13', 4928.00, 'confirmed', '2026-10-10 05:47:21', 7, 'hotel', 1478.40, 3449.60, 'razorpay', 'PAY_5B6B6F07A6', NULL, '{\"hotel_id\":\"HTL-DB-7\",\"hotel_name\":\"Delta Residency\",\"room_name\":\"Deluxe Ocean View Room\",\"check_in\":\"2026-10-13\",\"check_out\":\"2026-10-15\",\"rooms_count\":1,\"guests_count\":2,\"advance_type\":\"percentage\",\"gateway\":\"razorpay\",\"booked_at\":\"2026-10-10 05:47:21\"}'),
(10, 'GFX-6E6670FB', 'Nikhil singh', 'nikhilrura5@gmail.com', '+916676767677', 'Goa Beach Escape – 5 Days / 4 Nights (4 Adults)', '2026-10-18', 32000.00, 'confirmed', '2026-10-11 18:49:24', 8, 'package', 9600.00, 22400.00, 'razorpay', 'pay_TmhojMBh2mTWO8', NULL, '{\"package_id\":\"PKG-DB-6\",\"adults\":4,\"children\":0,\"per_person_price\":8000,\"advance_type\":\"percentage\",\"advance_percent\":30,\"special_notes\":\"\",\"gateway\":\"razorpay\",\"booked_at\":\"2026-10-11 18:49:24\"}');

-- --------------------------------------------------------

--
-- Table structure for table `cruises`
--

CREATE TABLE `cruises` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci,
  `cruise_line` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cordelia Cruises',
  `ship_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'The Empress',
  `departure_port` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Mumbai',
  `destination_ports` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Mumbai, High Seas, Goa',
  `duration_nights` int NOT NULL DEFAULT '3',
  `duration_days` int NOT NULL DEFAULT '4',
  `duration_text` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '3 Nights / 4 Days',
  `starting_price` decimal(10,2) NOT NULL DEFAULT '19999.00',
  `original_price` decimal(10,2) DEFAULT '26999.00',
  `token_advance` decimal(10,2) DEFAULT '3000.00',
  `badge` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Bestseller',
  `rating` decimal(2,1) DEFAULT '4.9',
  `reviews_count` int DEFAULT '380',
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` longtext COLLATE utf8mb4_unicode_ci,
  `highlights` longtext COLLATE utf8mb4_unicode_ci,
  `itinerary` longtext COLLATE utf8mb4_unicode_ci,
  `cabins` longtext COLLATE utf8mb4_unicode_ci,
  `inclusions` longtext COLLATE utf8mb4_unicode_ci,
  `exclusions` longtext COLLATE utf8mb4_unicode_ci,
  `policies` longtext COLLATE utf8mb4_unicode_ci,
  `category` enum('domestic','international') COLLATE utf8mb4_unicode_ci DEFAULT 'domestic',
  `sailing_dates` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Weekly Departures • Friday & Monday',
  `status` enum('active','draft') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `star_rating` tinyint DEFAULT '4',
  `property_type` varchar(100) DEFAULT 'Luxury Resort',
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `starting_price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percent` int DEFAULT '0',
  `amenities` text,
  `description` text,
  `policies` text,
  `checkin_time` varchar(20) DEFAULT '02:00 PM',
  `checkout_time` varchar(20) DEFAULT '11:00 AM',
  `badge` varchar(50) DEFAULT 'Popular',
  `status` enum('active','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `slug`, `star_rating`, `property_type`, `city`, `state`, `country`, `address`, `latitude`, `longitude`, `featured_image`, `starting_price`, `original_price`, `discount_percent`, `amenities`, `description`, `policies`, `checkin_time`, `checkout_time`, `badge`, `status`, `created_at`, `updated_at`) VALUES
(7, 'Delta Residency', 'delta-residency', 3, '5★ Beachfront Resort', 'goa', 'goa', 'India', '151/20 Beach Road, near St. Anthony Chapel, Gauravaddo, Calangute, Goa 403516', NULL, NULL, 'uploads/hotels/1791427486_cover_images.jpeg', 2200.00, 3500.00, 37, '[{\"name\":\"Free Breakfast\",\"icon\":\"fa-solid fa-mug-saucer\",\"desc\":\"Daily buffet or continental breakfast\"},{\"name\":\"Swimming Pool\",\"icon\":\"fa-solid fa-water-ladder\",\"desc\":\"Infinity or outdoor pool access\"},{\"name\":\"Free High-Speed Wi-Fi\",\"icon\":\"fa-solid fa-wifi\",\"desc\":\"High speed internet in all rooms\"},{\"name\":\"Beachfront \\/ Ocean View\",\"icon\":\"fa-solid fa-umbrella-beach\",\"desc\":\"Direct walking access to beach\"},{\"name\":\"Fine Dining Restaurant\",\"icon\":\"fa-solid fa-utensils\",\"desc\":\"Multi-cuisine in-house chef restaurant\"},{\"name\":\"Bar & Lounge\",\"icon\":\"fa-solid fa-martini-glass-citrus\",\"desc\":\"Sunset cocktail bar & lounge\"},{\"name\":\"Air Conditioning\",\"icon\":\"fa-solid fa-snowflake\",\"desc\":\"Individual climate control AC\"},{\"name\":\"24x7 Room Service\",\"icon\":\"fa-solid fa-bell-concierge\",\"desc\":\"Around the clock food & butler support\"},{\"name\":\"Free Valet Parking\",\"icon\":\"fa-solid fa-square-parking\",\"desc\":\"Secure on-premise vehicle parking\"}]', 'If you are planning a beach vacation, this property is a standout budget choice.\r\n• Location: Tucked away in Gaura Vaddo near St. Anthony\'s Chapel, roughly a 5 to 6-minute walk from Calangute Beach.', '{\"summary\":\"Free Cancellation up to 48 hours before check-in. Kids under 6 stay free.\",\"rules\":[{\"timeline\":\"15+ Days Before Check-in\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Check-in\",\"refund\":\"70% Refund\",\"deduction\":\"30% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Check-in\",\"refund\":\"30% Refund\",\"deduction\":\"70% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable\",\"badge_type\":\"no_refund\"}],\"child_policy\":\"Children up to 6 years stay complimentary in existing bedding.\",\"id_proof\":\"Government-issued photo identification (Aadhar\\/Passport) required for all adult guests.\"}', '02:00 PM', '11:00 AM', '3★ Luxury', 'active', '2026-10-07 05:19:05', '2026-10-08 02:44:46'),
(8, 'Taj Hotel', 'taj-hotel', 5, '5★ Luxury Resort', 'Mumbai', 'Maharashtra', 'India', '1 b mumbai maharashtra 287636', NULL, NULL, '', 7500.00, 9500.00, 21, '[{\"name\":\"Free Breakfast\",\"icon\":\"fa-solid fa-mug-saucer\",\"desc\":\"Daily buffet or continental breakfast\"},{\"name\":\"Swimming Pool\",\"icon\":\"fa-solid fa-water-ladder\",\"desc\":\"Infinity or outdoor pool access\"},{\"name\":\"Free High-Speed Wi-Fi\",\"icon\":\"fa-solid fa-wifi\",\"desc\":\"High speed internet in all rooms\"},{\"name\":\"Fine Dining Restaurant\",\"icon\":\"fa-solid fa-utensils\",\"desc\":\"Multi-cuisine in-house chef restaurant\"},{\"name\":\"Bar & Lounge\",\"icon\":\"fa-solid fa-martini-glass-citrus\",\"desc\":\"Sunset cocktail bar & lounge\"},{\"name\":\"Air Conditioning\",\"icon\":\"fa-solid fa-snowflake\",\"desc\":\"Individual climate control AC\"},{\"name\":\"24x7 Room Service\",\"icon\":\"fa-solid fa-bell-concierge\",\"desc\":\"Around the clock food & butler support\"},{\"name\":\"Jacuzzi \\/ Bathtub\",\"icon\":\"fa-solid fa-hot-tub-person\",\"desc\":\"Private in-room jacuzzi or bathtub\"}]', '', '{\"summary\":\"Free Cancellation up to 48 hours before check-in. Kids under 6 stay free.\",\"rules\":[{\"timeline\":\"15+ Days Before Check-in\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Check-in\",\"refund\":\"70% Refund\",\"deduction\":\"30% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Check-in\",\"refund\":\"30% Refund\",\"deduction\":\"70% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable\",\"badge_type\":\"no_refund\"}],\"child_policy\":\"Children up to 6 years stay complimentary in existing bedding.\",\"id_proof\":\"Government-issued photo identification (Aadhar\\/Passport) required for all adult guests.\"}', '02:00 PM', '11:00 AM', '5★ Luxury', 'active', '2026-10-13 06:12:17', '2026-10-13 06:12:17'),
(9, 'Radisson Blue', 'radisson-blue', 4, '7★ Luxury Resort', 'Delhi', 'Delhi', 'India', 'Ring Road', NULL, NULL, '', 7500.00, 9500.00, 21, '[{\"name\":\"Free Breakfast\",\"icon\":\"fa-solid fa-mug-saucer\",\"desc\":\"Daily buffet or continental breakfast\"},{\"name\":\"Swimming Pool\",\"icon\":\"fa-solid fa-water-ladder\",\"desc\":\"Infinity or outdoor pool access\"},{\"name\":\"Free High-Speed Wi-Fi\",\"icon\":\"fa-solid fa-wifi\",\"desc\":\"High speed internet in all rooms\"},{\"name\":\"Spa & Wellness\",\"icon\":\"fa-solid fa-spa\",\"desc\":\"Ayurvedic treatments & massage center\"},{\"name\":\"Beachfront \\/ Ocean View\",\"icon\":\"fa-solid fa-umbrella-beach\",\"desc\":\"Direct walking access to beach\"},{\"name\":\"Mountain \\/ Valley View\",\"icon\":\"fa-solid fa-mountain-sun\",\"desc\":\"Balcony with alpine snow\\/valley view\"},{\"name\":\"Fitness Center \\/ Gym\",\"icon\":\"fa-solid fa-dumbbell\",\"desc\":\"Cardio equipment & weights room\"},{\"name\":\"Fine Dining Restaurant\",\"icon\":\"fa-solid fa-utensils\",\"desc\":\"Multi-cuisine in-house chef restaurant\"},{\"name\":\"Bar & Lounge\",\"icon\":\"fa-solid fa-martini-glass-citrus\",\"desc\":\"Sunset cocktail bar & lounge\"},{\"name\":\"Airport Shuttle Service\",\"icon\":\"fa-solid fa-van-shuttle\",\"desc\":\"Scheduled airport pickup and drop\"},{\"name\":\"Air Conditioning\",\"icon\":\"fa-solid fa-snowflake\",\"desc\":\"Individual climate control AC\"},{\"name\":\"24x7 Room Service\",\"icon\":\"fa-solid fa-bell-concierge\",\"desc\":\"Around the clock food & butler support\"},{\"name\":\"Free Valet Parking\",\"icon\":\"fa-solid fa-square-parking\",\"desc\":\"Secure on-premise vehicle parking\"},{\"name\":\"Jacuzzi \\/ Bathtub\",\"icon\":\"fa-solid fa-hot-tub-person\",\"desc\":\"Private in-room jacuzzi or bathtub\"}]', 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', '{\"summary\":\"Free Cancellation up to 48 hours before check-in. Kids under 6 stay free.\",\"rules\":[{\"timeline\":\"15+ Days Before Check-in\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Check-in\",\"refund\":\"70% Refund\",\"deduction\":\"30% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Check-in\",\"refund\":\"30% Refund\",\"deduction\":\"70% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable\",\"badge_type\":\"no_refund\"}],\"child_policy\":\"Children up to 6 years stay complimentary in existing bedding.\",\"id_proof\":\"Government-issued photo identification (Aadhar\\/Passport) required for all adult guests.\"}', '02:00 PM', '11:00 AM', '5★ Luxury Beachfront', 'active', '2026-10-13 08:43:13', '2026-10-13 08:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_images`
--

CREATE TABLE `hotel_images` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(150) DEFAULT NULL,
  `sort_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hotel_images`
--

INSERT INTO `hotel_images` (`id`, `hotel_id`, `image_url`, `caption`, `sort_order`) VALUES
(4, 7, 'uploads/hotels/1791427486_gal_0_images3.jpeg', 'images (3)', 0),
(5, 7, 'uploads/hotels/1791427486_gal_1_images2.jpeg', 'images (2)', 1),
(6, 7, 'uploads/hotels/1791427486_gal_2_images1.jpeg', 'images (1)', 2),
(7, 7, 'uploads/hotels/1791427486_gal_3_images.jpeg', 'images', 3);

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

CREATE TABLE `hotel_rooms` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `room_name` varchar(150) NOT NULL,
  `room_type` varchar(100) DEFAULT 'Deluxe Room',
  `price_per_night` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `max_adults` int DEFAULT '2',
  `max_children` int DEFAULT '1',
  `bed_type` varchar(100) DEFAULT '1 King Bed',
  `room_size` varchar(50) DEFAULT '350 sq.ft',
  `meal_plan` varchar(100) DEFAULT 'Free Breakfast',
  `image_url` varchar(255) DEFAULT NULL,
  `amenities` text,
  `status` enum('available','sold_out') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hotel_rooms`
--

INSERT INTO `hotel_rooms` (`id`, `hotel_id`, `room_name`, `room_type`, `price_per_night`, `original_price`, `max_adults`, `max_children`, `bed_type`, `room_size`, `meal_plan`, `image_url`, `amenities`, `status`) VALUES
(2, 7, 'Deluxe Ocean View Room', 'Deluxe Room', 2200.00, 2800.00, 2, 1, '1 King Bed', '420 sq ft', 'Free Breakfast Included', 'https://finchskills.com/orionadvent/uploads/hotels/1791350345_room_0_images1.jpg', NULL, 'available'),
(3, 8, 'Deluxe Ocean View Room', 'Deluxe Room', 7500.00, 9500.00, 2, 1, '1 King Bed', '420 sq ft', 'Free Breakfast Included', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80', NULL, 'available'),
(4, 9, 'Delux', 'Deluxe Room', 7500.00, 9500.00, 2, 1, '1 King Bed', '420 sq ft', 'Free Breakfast Included', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80', NULL, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL DEFAULT 'Remote / On-site',
  `job_type` enum('Full-time','Part-time','Contract','Remote','Internship') NOT NULL DEFAULT 'Full-time',
  `experience_level` varchar(100) DEFAULT '1-3 Years',
  `salary_range` varchar(100) DEFAULT 'Best in Industry',
  `openings` int DEFAULT '1',
  `short_description` text,
  `description` longtext NOT NULL,
  `requirements` longtext,
  `benefits` longtext,
  `status` enum('active','closed','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `candidate_name` varchar(200) NOT NULL,
  `candidate_email` varchar(200) NOT NULL,
  `candidate_phone` varchar(50) NOT NULL,
  `experience_years` decimal(4,1) DEFAULT '0.0',
  `current_location` varchar(150) DEFAULT NULL,
  `notice_period` varchar(100) DEFAULT 'Immediate',
  `portfolio_url` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) NOT NULL,
  `cover_letter` text,
  `status` enum('pending','reviewed','shortlisted','selected','rejected') DEFAULT 'pending',
  `admin_notes` text,
  `email_sent` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `meta_data` longtext,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `title`, `message`, `link`, `meta_data`, `is_read`, `created_at`) VALUES
(1, 'booking', 'New Tour Booking & Payment', 'Arjun Mehta booked \"Dubai Desert Safari\" - Advance token ₹2,500 received via Razorpay.', 'bookings.php?search=GF-1001', NULL, 0, '2026-10-06 16:50:53'),
(2, 'flight_inquiry', 'New Flight Inquiry', 'Rohan Verma submitted inquiry for DEL → BOM (Air India AI-101) - Est. ₹4,500.', 'bookings.php?filter=flight', NULL, 0, '2026-10-06 16:20:53'),
(3, 'user_login', 'Traveler Logged In', 'Priya Patel (priya.p@example.com) logged into the portal.', 'users.php', NULL, 1, '2026-10-06 15:05:53'),
(4, 'user_register', 'New Traveler Registration', 'madhav (rajatkumarkhurja@gmail.com) registered a new traveler account.', 'users.php', '{\"user_id\":6,\"name\":\"madhav\",\"email\":\"rajatkumarkhurja@gmail.com\"}', 0, '2026-10-07 05:25:24'),
(5, 'booking', 'New Hotel Stay Reservation', 'madhav reserved \"Delta Residency - Deluxe Ocean View Room\" - Token ₹1,478 received via Razorpay.', 'bookings.php?code=HTL-9A3FCFBE', '{\"booking_code\":\"HTL-9A3FCFBE\",\"amount\":4928,\"advance\":1478.40000000000009094947017729282379150390625,\"gateway\":\"razorpay\"}', 0, '2026-10-07 05:25:51'),
(6, 'user_login', 'Traveler Logged In', 'madhav (rajatkumarkhurja@gmail.com) logged into the portal.', 'users.php', '{\"user_id\":6,\"name\":\"madhav\",\"email\":\"rajatkumarkhurja@gmail.com\"}', 0, '2026-10-07 06:33:19'),
(7, 'flight_inquiry', 'New Flight Booking Inquiry', 'madhav submitted flight inquiry for DEL → BOM (Air India Express IX-1056) - Est. ₹7,218', 'bookings.php?filter=flight', '{\"booking_code\":\"FLT-D65345FF\",\"origin\":\"DEL\",\"destination\":\"BOM\",\"fare\":7218}', 0, '2026-10-07 06:33:51'),
(8, 'user_register', 'New Traveler Registration', 'Nikhil Singh Bhadauriya (nikhilbhadauriya.gsspt@gmail.com) registered a new traveler account.', 'users.php', '{\"user_id\":7,\"name\":\"Nikhil Singh Bhadauriya\",\"email\":\"nikhilbhadauriya.gsspt@gmail.com\"}', 0, '2026-10-10 05:44:26'),
(9, 'booking', 'New Tour Booking & Payment', 'Nikhil Singh Bhadauriya booked \"Goa Beach Escape – 5 Days / 4 Nights\" - Advance token ₹7,200 received via Razorpay.', 'bookings.php?code=GFX-6ABAEF74', '{\"booking_code\":\"GFX-6ABAEF74\",\"amount\":24000,\"advance\":7200,\"gateway\":\"razorpay\"}', 0, '2026-10-10 05:45:18'),
(10, 'booking', 'New Hotel Stay Reservation', 'Nikhil Singh Bhadauriya reserved \"Delta Residency - Deluxe Ocean View Room\" - Token ₹1,478 received via Razorpay.', 'bookings.php?code=HTL-A8C51B50', '{\"booking_code\":\"HTL-A8C51B50\",\"amount\":4928,\"advance\":1478.40000000000009094947017729282379150390625,\"gateway\":\"razorpay\"}', 0, '2026-10-10 05:47:21'),
(11, 'user_register', 'New Traveler Registration', 'Nikhil singh (nikhilrura5@gmail.com) registered a new traveler account.', 'users.php', '{\"user_id\":8,\"name\":\"Nikhil singh\",\"email\":\"nikhilrura5@gmail.com\"}', 0, '2026-10-11 18:48:19'),
(12, 'booking', 'New Tour Booking & Payment', 'Nikhil singh booked \"Goa Beach Escape – 5 Days / 4 Nights\" - Advance token ₹9,600 received via Razorpay.', 'bookings.php?code=GFX-6E6670FB', '{\"booking_code\":\"GFX-6E6670FB\",\"amount\":32000,\"advance\":9600,\"gateway\":\"razorpay\"}', 0, '2026-10-11 18:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subtitle` text,
  `location` varchar(150) NOT NULL,
  `state_country` varchar(150) DEFAULT NULL,
  `circuit_type` varchar(100) DEFAULT 'Holiday Circuit',
  `travel_mode` varchar(50) DEFAULT 'flight',
  `departure_city` varchar(100) DEFAULT 'All Major Cities',
  `duration` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `token_advance` decimal(10,2) DEFAULT '2500.00',
  `badge` varchar(50) DEFAULT 'Bestseller',
  `rating` decimal(2,1) DEFAULT '4.9',
  `reviews_count` int DEFAULT '450',
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` longtext,
  `highlights` longtext,
  `itinerary` longtext,
  `inclusions` longtext,
  `exclusions` longtext,
  `hotel_ids` longtext,
  `custom_stays` longtext,
  `policies` longtext,
  `category` enum('domestic','international','adventure','luxury') DEFAULT 'international',
  `duration_nights` int DEFAULT '4',
  `duration_days` int DEFAULT '5',
  `duration_text` varchar(50) DEFAULT '4 Nights / 5 Days',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `title`, `slug`, `subtitle`, `location`, `state_country`, `circuit_type`, `travel_mode`, `departure_city`, `duration`, `price`, `original_price`, `token_advance`, `badge`, `rating`, `reviews_count`, `featured_image`, `gallery`, `highlights`, `itinerary`, `inclusions`, `exclusions`, `hotel_ids`, `custom_stays`, `policies`, `category`, `duration_nights`, `duration_days`, `duration_text`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Kashmir', 'kashmir', '', 'Srinagar, Gulmarg, Pahalgam', 'Kashmir, India', 'Scenic Valley & Lake Circuit', 'flight', 'All Major Cities', '', 14999.00, 19500.00, 2500.00, 'Bestseller', 4.9, 350, 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=800&q=80', '[]', '[]', '[{\"day\":\"Day 1\",\"title\":\"Arrival Srinagar & Sunset Shikara Ride\",\"desc\":\"Meet our tour executive at Srinagar Airport. Transfer to luxury Dal Lake houseboat, freshen up, and enjoy a calming sunset Shikara ride across the floating flower markets.\",\"meals\":\"Dinner Included\",\"stay\":\"Cedar Houseboat Dal Lake\"},{\"day\":\"Day 2\",\"title\":\"Srinagar to Gulmarg Meadows & Gondola\",\"desc\":\"Drive through apple orchards to Gulmarg. Board the famous Gulmarg Gondola to reach snowy slopes of Apharwat peak.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"Alpine Resort Gulmarg\"},{\"day\":\"Day 3\",\"title\":\"Gulmarg to Pahalgam Valley of Shepherds\",\"desc\":\"Scenic transfer along the Lidder River to Pahalgam. Visit saffron fields and explore Betaab Valley.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"Pine View Hotel Pahalgam\"},{\"day\":\"Day 4\",\"title\":\"Pahalgam Local Sightseeing & Baisaran Valley\",\"desc\":\"Pony trek up to mini Switzerland (Baisaran) and relax by the crystal clear trout streams.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"Pine View Hotel Pahalgam\"},{\"day\":\"Day 5\",\"title\":\"Pahalgam to Srinagar Mughal Gardens\",\"desc\":\"Return to Srinagar city. Visit world-heritage Mughal gardens Nishat Bagh and Shalimar Bagh followed by souvenir shopping.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"4★ Srinagar City Hotel\"},{\"day\":\"Day 6\",\"title\":\"Airport Drop & Fond Memories\",\"desc\":\"Enjoy breakfast overlooking Shankaracharya hill. Transfer to Srinagar Airport with unforgettable memories.\",\"meals\":\"Breakfast Included\",\"stay\":\"Tour Concludes\"}]', '[\"5 Nights Luxury Accommodation (1N Houseboat + 4N Hotels)\",\"Daily Buffet Breakfast & Chef-Curated Kashmiri Dinners\",\"Private Dedicated AC Sedan for entire tour & airport transfers\",\"1-Hour Sunset Shikara Ride on Dal Lake Srinagar\",\"All Tolls, Parking, Fuel, Driver Allowances & State Permits\"]', '[\"Airfare to and from Srinagar Airport (SXR)\",\"Gulmarg Gondola cable car ticket Phase 2 (Optional Add-on)\",\"Personal pony rides, snow clothing rentals, or camera fees\",\"5% GST on total booking invoice\"]', '[]', 'Heritage Cedarwood Houseboat (Dal Lake) + 4★ Pine View Resort (Pahalgam)', '{\"summary\":\"100% Refund if cancelled 15 days before travel date. 50% refund within 7-14 days.\",\"rules\":[{\"timeline\":\"15+ Days Before Tour Departure\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Token Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Departure\",\"refund\":\"80% Refund\",\"deduction\":\"20% Cancellation \\/ Reschedule Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Departure\",\"refund\":\"50% Refund\",\"deduction\":\"50% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable Cutoff\",\"badge_type\":\"no_refund\"}],\"reschedule_policy\":\"Flexible date reschedule permitted up to 7 days before tour departure without penalty.\",\"medical_policy\":\"100% token refund on medical emergencies with verified certificate.\"}', 'domestic', 5, 6, '5 Nights / 6 Days', NULL, 'active', '2026-10-05 20:30:58', '2026-10-13 05:47:58'),
(7, 'Dubai', 'dubai', 'Burj Khalifa 124th floor, 4x4 red dune desert safari with BBQ, Marina yacht dinner', 'Dubai, Downtown, Marina, Palm Jumeirah', 'Dubai, UAE', 'Futuristic Metropolis & Desert', 'flight', 'All Major Cities', '', 38999.00, 49500.00, 5000.00, 'Visa on Arrival', 4.9, 350, 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80', '[]', '[]', '[{\"day\":\"Day 01\",\"title\":\"Arrival & Welcome Reception\",\"desc\":\"Pick-up from Airport\\/Railway station by dedicated private AC cab. Transfer to your pre-booked luxury stay, check in, and enjoy evening leisure with welcome tea.\",\"meals\":\"Dinner Included\",\"stay\":\"4★ Luxury Resort\"}]', '[\"Pick-up and drop from Airport \\/ Station in private dedicated AC vehicle\",\"Luxury hotel stays in verified 4-Star \\/ 5-Star properties\",\"Daily buffet breakfast and chef-curated dinner included\",\"All destination sightseeing excursions as per itinerary\",\"All interstate toll taxes, state parking charges, fuel, and driver night allowances\",\"24\\/7 on-ground tour manager support\"]', '[\"International Flights to Dubai (DXB\\/DWC)\",\"Tourism Dirham Fee payable directly at hotel check-in\"]', '[]', 'Atlantis The Palm Island Resort / 5★ Luxury Downtown Dubai Hotel', '{\"summary\":\"Free cancellation up to 14 days prior to travel date.\",\"rules\":[{\"timeline\":\"15+ Days Before Tour Departure\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Token Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Departure\",\"refund\":\"80% Refund\",\"deduction\":\"20% Cancellation \\/ Reschedule Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Departure\",\"refund\":\"50% Refund\",\"deduction\":\"50% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable Cutoff\",\"badge_type\":\"no_refund\"}],\"reschedule_policy\":\"Flexible date reschedule permitted up to 7 days before tour departure without penalty.\",\"medical_policy\":\"100% token refund on medical emergencies with verified certificate.\"}', 'international', 5, 6, '5 Nights / 6 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-13 05:50:06'),
(8, 'Bali Tropical Jungle Villa, Ubud Swings & Nusa Penida', 'bali-tropical-jungle-villa-nusa-penida', 'Private pool villa, giant Ubud swings, Tanah Lot temple & Nusa Penida island cruise', 'Ubud, Seminyak, Kuta, Nusa Penida', 'Bali, Indonesia', 'Tropical Island & Cultural Sanctuary', 'flight', 'All Major Cities', NULL, 32499.00, 42000.00, 4000.00, 'Visa Free Entry', 4.9, 440, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=80', NULL, '[{\"icon\":\"fa-solid fa-person-swimming\",\"title\":\"Private Pool Villa Stay\",\"desc\":\"Luxury floating breakfast included with floral pool setup\"},{\"icon\":\"fa-solid fa-camera\",\r\n  \"title\":\"Nusa Penida West Island Tour\",\"desc\":\"Fast speedboat tickets to Kelingking T-Rex cliff and Broken beach\"},{\"icon\":\"fa-solid fa-tree\",\"title\":\"Aloha Ubud Jungle Swings\",\r\n  \"desc\":\"Adrenaline jungle swing pass overlooking emerald rice terraces\"}]', '[{\"day\":\"Day 1\",\"title\":\"Arrival Denpasar & Transfer to Ubud Jungle Villa\",\"desc\":\"Warm Balinese welcome at DPS airport. Check into your romantic private pool villa.\",\r\n  \"meals\":\"Welcome Drink\",\"stay\":\"Ubud Private Pool Villa\"},{\"day\":\"Day 2\",\"title\":\"Tegalalang Rice Terraces & Ubud Jungle Swing\",\"desc\":\"Snap iconic photos on the giant swings and\r\n  visit coffee plantation for Luwak tasting.\",\"meals\":\"Floating Breakfast\",\"stay\":\"Ubud Private Pool Villa\"},{\"day\":\"Day 3\",\"title\":\"Kintamani Volcano & Tegenungan Waterfall\",\r\n  \"desc\":\"Witness active Mount Batur volcanic caldera and swim beneath Tegenungan waterfall.\",\"meals\":\"Breakfast & Buffet Lunch\",\"stay\":\"Ubud Private Pool Villa\"},{\"day\":\"Day 4\",\r\n  \"title\":\"Transfer to Seminyak & Tanah Lot Sunset\",\"desc\":\"Relocate to beachside Seminyak. Witness breathtaking sunset at Tanah Lot ocean temple.\",\"meals\":\"Breakfast Included\",\r\n  \"stay\":\"Seminyak Beach Resort\"},{\"day\":\"Day 5\",\"title\":\"Full-Day Nusa Penida Island Tour\",\"desc\":\"Speedboat to Nusa Penida. Visit Kelingking Beach, Angel\'s Billabong, and Crystal\r\n  Bay.\",\"meals\":\"Breakfast & Local Lunch\",\"stay\":\"Seminyak Beach Resort\"},{\"day\":\"Day 6\",\"title\":\"Uluwatu Cliff Temple & Kecak Fire Dance\",\"desc\":\"Perched on high ocean cliffs,\r\n  watch the hypnotic sunset Kecak fire dance performance.\",\"meals\":\"Breakfast & Jimbaran Seafood\",\"stay\":\"Seminyak Beach Resort\"},{\"day\":\"Day 7\",\"title\":\"Souvenir Shopping & Airport\r\n  Departure\",\"desc\":\"Final shopping before airport drop.\",\"meals\":\"Breakfast Included\",\"stay\":\"Tour Concludes\"}]', '[\"6 Nights Stays (3N Ubud Private Pool Villa + 3N Seminyak Resort)\",\"Daily Floating Breakfasts & Welcome Flower Garlands\",\"Fast Speedboat transfers to Nusa Penida Island\",\r\n  \"Private Chauffeur Car with English speaking guide\"]', '[\"International Flights to Denpasar (DPS)\",\"Indonesia Tourist Visa on Arrival fee ($35 USD paid directly)\"]', NULL, 'Ubud Private Pool Jungle Villa (3N) + Seminyak Beachfront Resort (3N)', 'Free cancellation up to 10 days before flight date.', 'international', 6, 7, '6 Nights / 7 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-11 19:01:39'),
(9, 'Thailand Phi Phi Islands, Krabi Sunset & Bangkok Explorer', 'thailand-phuket-krabi-island-getaway', 'Island hopping on speedboats, Maya Bay snorkeling, James Bond island, and authentic Thai street food tours.', 'Phuket, Krabi, Phi Phi, Bangkok', 'Phuket, Thailand', 'Tropical Islands & Cultural Circuit', 'flight', 'All Major Cities', NULL, 29999.00, 38500.00, 3500.00, 'Visa Free For Indians', 4.8, 345, 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?auto=format&fit=crop&w=900&q=80', NULL, '[{\"icon\":\"fa-solid fa-ship\",\"title\":\"Phi Phi Speedboat Safari\",\"desc\":\"Fast speedboat transfers to Maya Bay and Viking Cave\"},{\"icon\":\"fa-solid fa-water\",\"title\":\"Coral Reef\r\n  Snorkeling\",\"desc\":\"Crystal clear water snorkeling with equipment provided\"},{\"icon\":\"fa-solid fa-mountain\",\"title\":\"James Bond Island\",\"desc\":\"Scenic sea canoe through Phang Nga\r\n  Bay limestone caves\"}]', '[{\"day\":\"Day 1\",\"title\":\"Arrival at Phuket & Beachfront Hotel Check-In\",\"desc\":\"Welcome at Phuket Airport. Transfer to your 4-star resort.\",\"meals\":\"Dinner Included\",\r\n  \"stay\":\"4★ Resort in Phuket\"},{\"day\":\"Day 2\",\"title\":\"Full-Day Phi Phi & Maya Bay Speedboat Cruise\",\"desc\":\"Luxury speedboat journey to Phi Phi Don and Phi Phi Leh.\",\r\n  \"meals\":\"Breakfast & Island Lunch\",\"stay\":\"4★ Resort in Phuket\"},{\"day\":\"Day 3\",\"title\":\"Phuket to Krabi by Scenic Coastal Drive\",\"desc\":\"Drive along Andaman coastline to Krabi.\r\n  Sunset over limestone cliffs.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"4★ Resort in Krabi\"},{\"day\":\"Day 4\",\"title\":\"Krabi 4-Island Tour & Emerald Pool\",\"desc\":\"Visit Tup Island,\r\n  Chicken Island, and Poda Island.\",\"meals\":\"Breakfast & Lunch\",\"stay\":\"4★ Resort in Krabi\"},{\"day\":\"Day 5\",\"title\":\"Krabi to Bangkok Flight & Chao Phraya Dinner Cruise\",\r\n  \"desc\":\"Flight to Bangkok. Chao Phraya River Princess dinner cruise.\",\"meals\":\"Breakfast & Dinner Cruise\",\"stay\":\"4★ Downtown Hotel in Bangkok\"},{\"day\":\"Day 6\",\"title\":\"Chatuchak\r\n  Shopping & Departure Drop\",\"desc\":\"Shopping at Pratunam markets before airport drop.\",\"meals\":\"Breakfast Included\",\"stay\":\"Tour Concludes\"}]', '[\"5 Nights 4★ Hotel Accommodation (Phuket 2N + Krabi 2N + Bangkok 1N)\",\"Daily Lavish Buffet Breakfasts & 3 Dinners\",\"Full-Day Phi Phi Islands Speedboat Tour with National\r\n  Park permits\",\"Chao Phraya River Luxury Princess Dinner Cruise tickets\",\"All private airport and intercity transfers\"]', '[\"International Airfare to Thailand\",\"Personal watersports activities\"]', NULL, 'Dusit Thani Krabi Beach Resort / Centara Grand Beach Resort Phuket', 'Free cancellation up to 7 days before departure date.', 'international', 5, 6, '5 Nights / 6 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-11 19:01:39'),
(10, 'Singapore Futuristic City, Sentosa & Universal Studios Adventure', 'singapore-sentosa-universal-studios', 'Marina Bay Sands SkyPark, Gardens by the Bay Supertrees, Universal Studios Hollywood rides, and Night Safari.', 'Marina Bay, Sentosa Island, Clarke Quay', 'Singapore City, Singapore', 'Futuristic Metropolis & Theme Parks', 'flight', 'All Major Cities', NULL, 42999.00, 54000.00, 5000.00, 'Fast 48h E-Visa', 4.9, 290, 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=900&q=80', NULL, '[{\"icon\":\"fa-solid fa-film\",\"title\":\"Universal Studios Full-Day\",\"desc\":\"All-access passes to 24 thrilling rides and themed zones\"},{\"icon\":\"fa-solid fa-tree\",\r\n  \"title\":\"Gardens by the Bay\",\"desc\":\"Cloud Forest glass dome and Supertree Grove light show\"},{\"icon\":\"fa-solid fa-building\",\"title\":\"Marina Bay SkyPark\",\"desc\":\"Observation deck\r\n  passes with 360-degree skyline views\"}]', '[{\"day\":\"Day 1\",\"title\":\"Arrival in Singapore & Night Safari Tram Experience\",\"desc\":\"Changi Airport welcome. Singapore Night Safari tram ride.\",\"meals\":\"Dinner Included\",\r\n  \"stay\":\"4★ Hotel in Singapore\"},{\"day\":\"Day 2\",\"title\":\"Singapore City Tour & Gardens by the Bay Light Show\",\"desc\":\"Merlion Park, Chinatown, Marina Bay Sands SkyPark, Flower \r\n  Dome.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"4★ Hotel in Singapore\"},{\"day\":\"Day 3\",\"title\":\"Full-Day Thrill at Universal Studios Singapore (Sentosa)\",\"desc\":\"Cable car ride to\r\n  Sentosa. Full day at Universal Studios Singapore.\",\"meals\":\"Breakfast & Lunch Voucher\",\"stay\":\"4★ Hotel in Singapore\"},{\"day\":\"Day 4\",\"title\":\"Sentosa S.E.A. Aquarium & Wings of\r\n  Time Water Show\",\"desc\":\"S.E.A. Aquarium and Wings of Time laser fountain show.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"4★ Hotel in Singapore\"},{\"day\":\"Day 5\",\"title\":\"Jewel Changi\r\n  Rain Vortex & Flight Departure\",\"desc\":\"Visit Rain Vortex waterfall at Jewel Changi before departure.\",\"meals\":\"Breakfast Included\",\"stay\":\"Tour Concludes\"}]', '[\"4 Nights stay in 4★ Central Singapore Hotel\",\"Daily Gourmet Buffet Breakfasts\",\"Full-Day Universal Studios Singapore entry passes\",\"Gardens by the Bay (Flower Dome & Cloud\r\n  Forest) + Marina Bay SkyPark\",\"All return airport & sightseeing transfers in private vehicle\"]', '[\"International Flights to Singapore (SIN)\",\"Singapore Tourist E-Visa fee\"]', NULL, 'Hotel Grand Pacific / Swissotel The Stamford Singapore', 'Free cancellation up to 10 days before flight departure.', 'international', 4, 5, '4 Nights / 5 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-11 19:01:39'),
(11, 'Maldives Luxury Overwater Pool Villa & Coral Reef Safari', 'maldives-luxury-overwater-villa', 'Overwater villa with direct ocean lagoon access, sunset dolphin cruise, romantic beach dinners, and complimentary speedboats.', 'North Malé Atoll, South Ari Atoll', 'Maldives Atolls', 'Luxury Island Sanctuary & Honeymoon', 'flight', 'All Major Cities', NULL, 59999.00, 75000.00, 8000.00, 'Free 30-Day Visa', 4.9, 380, 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=900&q=80', NULL, '[{\"icon\":\"fa-solid fa-water\",\"title\":\"Overwater Lagoon Villa\",\"desc\":\"Direct private sundeck access into turquoise crystal lagoon\"},{\"icon\":\"fa-solid fa-sailboat\",\r\n  \"title\":\"Sunset Dolphin Cruise\",\"desc\":\"Luxury dhoni boat cruise spotting wild spinner dolphin pods\"},{\"icon\":\"fa-solid fa-utensils\",\"title\":\"All Meals & Premium Drinks\",\r\n  \"desc\":\"All-inclusive buffet spreads, cocktails, and candlelight dinner\"}]', '[{\"day\":\"Day 1\",\"title\":\"Arrival at Velana Airport & Speedboat Transfer\",\"desc\":\"Speedboat transfer to luxury island resort. Check into Overwater Villa.\",\"meals\":\"Dinner\r\n  Included\",\"stay\":\"5★ Luxury Overwater Villa\"},{\"day\":\"Day 2\",\"title\":\"Coral Reef Snorkeling & Afternoon Spa Pampering\",\"desc\":\"Guided house reef snorkel alongside turtles and\r\n  marine life. Balinese couple massage.\",\"meals\":\"All Meals Included\",\"stay\":\"5★ Luxury Overwater Villa\"},{\"day\":\"Day 3\",\"title\":\"Watersports Fun & Sunset Dolphin Safari Cruise\",\r\n  \"desc\":\"Complimentary paddleboarding and traditional sunset dolphin cruise.\",\"meals\":\"All Meals Included\",\"stay\":\"5★ Luxury Overwater Villa\"},{\"day\":\"Day 4\",\"title\":\"Private\r\n  Sandbank Picnic & Romantic Candlelight Dinner\",\"desc\":\"5-Course romantic candlelight beach dinner under the stars.\",\"meals\":\"All Meals & Candlelight Dinner\",\"stay\":\"5★ Luxury\r\n  Overwater Villa\"},{\"day\":\"Day 5\",\"title\":\"Floating Breakfast in Villa & Speedboat Drop\",\"desc\":\"Signature floating breakfast before speedboat transfer to Malé Airport.\",\r\n  \"meals\":\"Floating Breakfast\",\"stay\":\"Tour Concludes\"}]', '[\"4 Nights in Luxury 5★ Overwater Lagoon Villa with private plunge pool\",\"All-Inclusive Meal Plan (Breakfast, Lunch, Afternoon High Tea & Dinner)\",\"Unlimited selected\r\n  cocktails, wines and mocktails\",\"Return Speedboat Transfers between Velana Airport & Resort Island\",\"Complimentary Sunset Dolphin Cruise & Guided Snorkel Safari\"]', '[\"International Airfare to Malé (MLE)\",\"Green Tax ($6 USD per person per night)\"]', NULL, 'Adaaran Prestige Vadoo / Centara Ras Fushi Resort & Spa Maldives', 'Free cancellation up to 14 days before arrival date.', 'international', 4, 5, '4 Nights / 5 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-11 19:01:39'),
(12, 'Vietnam Highlights: Halong Bay Luxury Cruise & Da Nang Golden Hands Bridge', 'vietnam-halong-bay-golden-bridge', 'Overnight 5-star Halong Bay limestone karst cruise, Ba Na Hills giant hands bridge, ancient Hoi An lantern town, and Hanoi old quarter.', 'Hanoi, Halong Bay, Da Nang, Hoi An', 'Hanoi & Da Nang, Vietnam', 'Heritage Wonder & Coastal Explorer', 'flight', 'All Major Cities', NULL, 31999.00, 41000.00, 4000.00, 'Instant E-Visa (24h)', 4.8, 260, 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80', NULL, '[{\"icon\":\"fa-solid fa-ship\",\"title\":\"Overnight Halong Cruise\",\"desc\":\"5-Star luxury cruise cabin through UNESCO limestone karsts\"},{\"icon\":\"fa-solid fa-hand-holding\",\r\n  \"title\":\"Golden Hands Bridge\",\"desc\":\"Cable car ride to the world-famous giant hands bridge in Da Nang\"},{\"icon\":\"fa-solid fa-lightbulb\",\"title\":\"Hoi An Lantern Town\",\r\n  \"desc\":\"Night river boat ride releasing floating paper candle lanterns\"}]', '[{\"day\":\"Day 1\",\"title\":\"Arrival in Hanoi & French Quarter Street Food Walk\",\"desc\":\"Hanoi Old Quarter walking tour and egg coffee tasting.\",\"meals\":\"Dinner Included\",\r\n  \"stay\":\"4★ Boutique Hotel in Hanoi\"},{\"day\":\"Day 2\",\"title\":\"Hanoi to Halong Bay & Board 5-Star Luxury Karst Cruise\",\"desc\":\"Board luxury cruise through limestone karsts. Seafood\r\n  buffet lunch.\",\"meals\":\"Lunch & Dinner\",\"stay\":\"5★ Cruise Cabin on Halong Bay\"},{\"day\":\"Day 3\",\"title\":\"Tai Chi Sunrise, Halong Kayaking & Flight to Da Nang\",\"desc\":\"Morning Tai\r\n  Chi and kayaking through Luon Cave. Flight to Da Nang.\",\"meals\":\"Brunch Included\",\"stay\":\"4★ Beachfront Hotel in Da Nang\"},{\"day\":\"Day 4\",\"title\":\"Ba Na Hills & The World-Famous\r\n  Golden Hands Bridge\",\"desc\":\"Walk across the iconic Golden Hands Bridge in Ba Na Hills.\",\"meals\":\"Breakfast & Buffet Lunch\",\"stay\":\"4★ Beachfront Hotel in Da Nang\"},{\"day\":\"Day \r\n  5\",\"title\":\"Cam Thanh Coconut Boat Basket & Hoi An Ancient Town\",\"desc\":\"Bamboo basket boats and Hoi An colorful silk lantern town.\",\"meals\":\"Breakfast & Dinner\",\"stay\":\"4★\r\n  Beachfront Hotel in Da Nang\"},{\"day\":\"Day 6\",\"title\":\"Marble Mountains, Dragon Bridge & Souvenir Shopping\",\"desc\":\"Visit Marble Mountains and shopping at Han Market.\",\r\n  \"meals\":\"Breakfast & Dinner\",\"stay\":\"4★ Beachfront Hotel in Da Nang\"},{\"day\":\"Day 7\",\"title\":\"Leisure Morning on My Khe Beach & Airport Drop\",\"desc\":\"Relax on My Khe Beach before\r\n  airport transfer.\",\"meals\":\"Breakfast Included\",\"stay\":\"Tour Concludes\"}]', '[\"5 Nights 4★ Hotel Accommodation + 1 Night 5★ Luxury Cruise Cabin\",\"Daily Gourmet Buffet Breakfasts & All Meals on Halong Cruise\",\"Ba Na Hills Cable Car Tickets & Golden\r\n  Bridge Access Pass\",\"Cam Thanh Round Bamboo Basket Boat Ride Experience\",\"All private airport and intercity transfers with English speaking guide\"]', '[\"International Airfare from India to Vietnam\",\"Vietnam E-Visa processing fee\"]', NULL, 'Mon Cheri Halong Cruise / Belle Maison Parosand Da Nang Hotel', 'Free cancellation up to 7 days before tour departure.', 'international', 6, 7, '6 Nights / 7 Days', NULL, 'active', '2026-10-11 19:01:39', '2026-10-11 19:01:39'),
(13, 'Kerala', 'kerala', 'God\'s Own Country', 'Kochi → Munnar → Thekkady → Alleppey → Kochi', '', 'Holiday Circuit', 'land_only', 'All Major Cities', '5 Nights / 6 Days', 17999.00, 22999.00, 2500.00, 'Bestseller 2026', 4.9, 350, '', '[]', '[]', '[{\"day\":\"Day 01\",\"title\":\"Arrival & Welcome Reception\",\"desc\":\"Pick-up from Airport\\/Railway station by dedicated private AC cab. Transfer to your pre-booked luxury stay, check in, and enjoy evening leisure with welcome tea.\",\"meals\":\"Dinner Included\",\"stay\":\"4★ Luxury Resort\"}]', '[\"Pick-up and drop from Airport \\/ Station in private dedicated AC vehicle\",\"Luxury hotel stays in verified 4-Star \\/ 5-Star properties\",\"Daily buffet breakfast and chef-curated dinner included\",\"All destination sightseeing excursions as per itinerary\",\"All interstate toll taxes, state parking charges, fuel, and driver night allowances\",\"24\\/7 on-ground tour manager support\"]', '[\"Personal expenses, laundry, and telephone calls\",\"Optional adventure activity charges (e.g. Paragliding, Scuba, Cable car)\",\"Monument entry tickets not mentioned in inclusions\"]', '[]', NULL, '{\"summary\":\"Free Cancellation up to 7 days before tour departure. 100% token refund on emergency medical cancellations.\",\"rules\":[{\"timeline\":\"15+ Days Before Tour Departure\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Token Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Departure\",\"refund\":\"80% Refund\",\"deduction\":\"20% Cancellation \\/ Reschedule Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Departure\",\"refund\":\"50% Refund\",\"deduction\":\"50% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable Cutoff\",\"badge_type\":\"no_refund\"}],\"reschedule_policy\":\"Flexible date reschedule permitted up to 7 days before tour departure without penalty.\",\"medical_policy\":\"100% token refund on medical emergencies with verified certificate.\"}', 'domestic', 5, 6, '4 Nights / 5 Days', NULL, 'active', '2026-10-13 06:02:30', '2026-10-13 06:02:30'),
(14, 'Himachal', 'himachal', '', 'chandighar new delhi', '', 'Holiday Circuit', 'flight', 'All Major Cities', '5 Nights / 6 Days', 17999.00, 22999.00, 2500.00, 'Bestseller 2026', 4.9, 350, '', '[]', '[]', '[{\"day\":\"Day 01\",\"title\":\"Arrival & Welcome Reception\",\"desc\":\"Pick-up from Airport\\/Railway station by dedicated private AC cab. Transfer to your pre-booked luxury stay, check in, and enjoy evening leisure with welcome tea.\",\"meals\":\"Dinner Included\",\"stay\":\"4★ Luxury Resort\"}]', '[\"Pick-up and drop from Airport \\/ Station in private dedicated AC vehicle\",\"Luxury hotel stays in verified 4-Star \\/ 5-Star properties\",\"Daily buffet breakfast and chef-curated dinner included\",\"All destination sightseeing excursions as per itinerary\",\"All interstate toll taxes, state parking charges, fuel, and driver night allowances\",\"24\\/7 on-ground tour manager support\"]', '[\"Personal expenses, laundry, and telephone calls\",\"Optional adventure activity charges (e.g. Paragliding, Scuba, Cable car)\",\"Monument entry tickets not mentioned in inclusions\"]', '[]', NULL, '{\"summary\":\"Free Cancellation up to 7 days before tour departure. 100% token refund on emergency medical cancellations.\",\"rules\":[{\"timeline\":\"15+ Days Before Tour Departure\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Token Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Departure\",\"refund\":\"80% Refund\",\"deduction\":\"20% Cancellation \\/ Reschedule Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Departure\",\"refund\":\"50% Refund\",\"deduction\":\"50% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable Cutoff\",\"badge_type\":\"no_refund\"}],\"reschedule_policy\":\"Flexible date reschedule permitted up to 7 days before tour departure without penalty.\",\"medical_policy\":\"100% token refund on medical emergencies with verified certificate.\"}', 'domestic', 5, 6, '4 Nights / 5 Days', NULL, 'active', '2026-10-13 08:26:16', '2026-10-13 08:26:16'),
(15, 'Meghalya', 'meghalya', 'jshfhdshjhdsjhjhdfsjbsdbhbbsdhusdjnjsdufhdfshfjsbdvcgsdgdfsbshbdsb', 'Meghalaya', 'India', 'Holiday Circuit', 'flight', 'All Major Cities', '5 Nights / 6 Days', 17999.00, 22999.00, 2500.00, 'Bestseller 2026', 4.9, 350, '', '[]', '[]', '[{\"day\":\"Day 01\",\"title\":\"Arrival & Welcome Reception\",\"desc\":\"Pick-up from Airport\\/Railway station by dedicated private AC cab. Transfer to your pre-booked luxury stay, check in, and enjoy evening leisure with welcome tea.\",\"meals\":\"Dinner Included\",\"stay\":\"4★ Luxury Resort\"}]', '[\"Pick-up and drop from Airport \\/ Station in private dedicated AC vehicle\",\"Luxury hotel stays in verified 4-Star \\/ 5-Star properties\",\"Daily buffet breakfast and chef-curated dinner included\",\"All destination sightseeing excursions as per itinerary\",\"All interstate toll taxes, state parking charges, fuel, and driver night allowances\",\"24\\/7 on-ground tour manager support\"]', '[\"Personal expenses, laundry, and telephone calls\",\"Optional adventure activity charges (e.g. Paragliding, Scuba, Cable car)\",\"Monument entry tickets not mentioned in inclusions\"]', '[\"7\",\"8\"]', NULL, '{\"summary\":\"Free Cancellation up to 7 days before tour departure. 100% token refund on emergency medical cancellations.\",\"rules\":[{\"timeline\":\"15+ Days Before Tour Departure\",\"refund\":\"100% Refund\",\"deduction\":\"0% Deduction (Full Token Refund)\",\"badge_type\":\"full_refund\"},{\"timeline\":\"7 to 14 Days Before Departure\",\"refund\":\"80% Refund\",\"deduction\":\"20% Cancellation \\/ Reschedule Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"3 to 6 Days Before Departure\",\"refund\":\"50% Refund\",\"deduction\":\"50% Cancellation Fee\",\"badge_type\":\"partial_refund\"},{\"timeline\":\"Within 48 Hours \\/ No-Show\",\"refund\":\"0% (Non-Refundable)\",\"deduction\":\"100% Non-Refundable Cutoff\",\"badge_type\":\"no_refund\"}],\"reschedule_policy\":\"Flexible date reschedule permitted up to 7 days before tour departure without penalty.\",\"medical_policy\":\"100% token refund on medical emergencies with verified certificate.\"}', 'domestic', 5, 6, '4 Nights / 5 Days', NULL, 'active', '2026-10-13 08:35:45', '2026-10-13 08:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'honeymoon',
  `tour` varchar(200) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `quote` text NOT NULL,
  `rating` tinyint DEFAULT '5',
  `status` enum('active','hidden') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `city`, `avatar`, `category`, `tour`, `headline`, `quote`, `rating`, `status`, `created_at`) VALUES
(1, 'Sneha & Rohan Sharma', 'New Delhi, India', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', 'honeymoon', 'Maldives 5N/6D Villa', 'The best honeymoon we could have dreamed of!', 'From the private pool villa upgrade to the seamless speedboat transfers in Male, everything was effortless. Our coordinator was on WhatsApp 24/7. Truly a 5-star experience from start to finish.', 5, 'active', '2026-10-06 16:56:13'),
(2, 'Aditya & Varun Rao', 'Bangalore, India', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'adventure', 'Ladakh 7N/8D Biking', 'Flawless Enfield bikes and heated Pangong camps!', 'Crossing Khardung La at 18,000 ft was incredible. The backup vehicle with oxygen kits and dedicated mechanic support gave us complete peace of mind through rugged passes.', 5, 'active', '2026-10-06 16:56:13'),
(3, 'Dr. Rajesh & Sunita Mehra', 'Mumbai, India', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'family', 'Dubai 4N/5D Atlantis', 'Traveling with elderly parents was 100% stress-free!', 'Our private chauffeur was punctual, polite, and very helpful. Fast-track tickets to Burj Khalifa and Aquaventure saved us from standing in long queues with the kids.', 5, 'active', '2026-10-06 16:56:13'),
(4, 'Vikram & Ananya Sen', 'Kolkata, India', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80', 'honeymoon', 'Kashmir 5N/6D Gulmarg', 'Waking up on a Dal Lake houseboat was pure magic.', 'Our heritage cedarwood houseboat was spotless. Orion arranged Phase 2 Gulmarg Gondola passes when they were completely sold out everywhere else. Unforgettable trip!', 5, 'active', '2026-10-06 16:56:13'),
(5, 'Kavita & Arvind Singhania', 'Ahmedabad, India', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80', 'luxury', 'Rajasthan 5N/6D Palaces', 'Treated like royalty from airport pickup to checkout.', 'The private boat entry at Lake Pichola Udaipur and vintage car ride in Jaipur made our anniversary feel truly majestic. Transparent billing with zero hidden fees.', 5, 'active', '2026-10-06 16:56:13'),
(6, 'Pooja & Sameer Deshmukh', 'Pune, India', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80', 'family', 'Kerala 4N/5D Alleppey', 'The private chef on our backwater houseboat was outstanding.', 'Cruising along the silent palm backwaters while the chef prepared fresh local river fish was the highlight of our vacation. The kids loved every moment!', 5, 'active', '2026-10-06 16:56:13');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Orion Advent', '2026-10-08 23:31:00'),
(2, 'site_tagline', 'Your Passport to Adventure', '2026-10-05 18:18:09'),
(3, 'site_phone', '+91 9717996559', '2026-10-10 05:36:09'),
(4, 'site_email', 'info@orionadvent.com', '2026-10-10 05:36:09'),
(5, 'site_whatsapp', '+91 9717996559', '2026-10-10 05:36:09'),
(6, 'site_address', 'NX-ONE, Tech Zone IV, Amrapali Dream Valley, Greater Noida, Uttar Pradesh 201318', '2026-10-10 05:36:09'),
(7, 'site_logo', 'assets/images/logo/logo.webp', '2026-10-05 18:18:09'),
(8, 'mail_mode', 'live', '2026-10-12 08:13:00'),
(9, 'testing_otp', '123456', '2026-10-05 18:18:09'),
(10, 'smtp_host', 'mail.finchskills.com', '2026-10-12 08:13:00'),
(11, 'smtp_port', '465', '2026-10-05 18:18:09'),
(12, 'smtp_user', 'support@finchskills.com', '2026-10-12 08:13:00'),
(13, 'smtp_pass', 'Finc@1234$', '2026-10-12 08:13:00'),
(14, 'smtp_encryption', 'ssl', '2026-10-05 18:18:09'),
(15, 'smtp_from_name', 'Orion Advent', '2026-10-12 08:13:00'),
(16, 'smtp_from_email', 'hello@finchskills.com', '2026-10-12 08:13:00'),
(17, 'social_facebook', '', '2026-10-10 05:37:34'),
(18, 'social_instagram', 'https://instagram.com', '2026-10-05 18:18:09'),
(19, 'social_twitter', 'https://twitter.com', '2026-10-05 18:18:09'),
(20, 'social_youtube', '', '2026-10-10 05:37:34'),
(29, 'google_login_active', '1', '2026-10-05 18:45:25'),
(30, 'google_client_id', '', '2026-10-05 18:45:25'),
(31, 'google_client_secret', '', '2026-10-05 18:45:25'),
(52, 'social_linkedin', 'https://linkedin.com', '2026-10-10 05:37:34'),
(53, 'payment_gateway_provider', 'razorpay', '2026-10-10 05:42:43'),
(54, 'payment_mode', 'test', '2026-10-10 05:42:43'),
(55, 'booking_advance_type', 'percentage', '2026-10-10 05:42:43'),
(56, 'booking_advance_percent', '30', '2026-10-10 05:42:43'),
(57, 'booking_advance_fixed', '0', '2026-10-10 05:42:43'),
(58, 'razorpay_key_id', 'rzp_test_Tldpk2BO12Wkf9', '2026-10-10 05:42:43'),
(59, 'razorpay_key_secret', 'N3ELG67v1VK1Dl6WfcbmrlFY', '2026-10-10 05:42:43'),
(60, 'stripe_publishable_key', '', '2026-10-10 05:42:43'),
(61, 'stripe_secret_key', '', '2026-10-10 05:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT 'email',
  `oauth_uid` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `avatar`, `oauth_provider`, `oauth_uid`, `is_verified`, `otp_code`, `otp_expires_at`, `status`, `created_at`, `last_login`) VALUES
(1, 'Rohan Verma', 'rohan.v@example.com', '+91 9876500111', '$2y$10$3.VlpGyAxLAlpJOlMQMd/.IcsHEWR7uDPR994LNvEbVuPBRRut2ji', NULL, 'email', NULL, 1, NULL, NULL, 'active', '2026-09-30 18:18:09', '2026-10-05 21:48:09'),
(2, 'Priya Patel', 'priya.p@example.com', '+91 9811223344', '$2y$10$3.VlpGyAxLAlpJOlMQMd/.IcsHEWR7uDPR994LNvEbVuPBRRut2ji', NULL, 'email', NULL, 1, NULL, NULL, 'active', '2026-09-23 18:18:09', '2026-10-04 23:48:09'),
(3, 'Amitabh Singh', 'amitabh.s@example.com', '+91 9988776655', '$2y$10$3.VlpGyAxLAlpJOlMQMd/.IcsHEWR7uDPR994LNvEbVuPBRRut2ji', NULL, 'email', NULL, 0, NULL, NULL, 'active', '2026-10-05 17:18:09', NULL),
(4, 'Nikhil singh', 'admin@orionadvent.com', '6676767677', '$2y$10$Q957nm4trPU0jaJgm8v/zOcDW3MAmn7wAjFQDFDA/AhCSdTJ8.CZS', NULL, 'email', NULL, 1, NULL, NULL, 'active', '2026-10-05 18:25:20', '2026-10-05 23:55:28'),
(5, 'Nikhil Singh', 'nikhilbhadauriya.gspt@gmail.com', NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIkJAuR0S2iKdkUjPueFqZmu2xSX1YoUf_Yvtv65mK5V-bZeCw=s96-c', 'google', '112081495439939830062', 1, NULL, NULL, 'active', '2026-10-06 16:28:37', '2026-10-06 22:05:03'),
(7, 'Nikhil Singh Bhadauriya', 'nikhilbhadauriya.gsspt@gmail.com', '7317788940', '$2y$10$7DkpuukYEIt71yk8sAspcuzK57AKM.ci1ljyKLpIoS0rxsHFpmK.a', NULL, 'email', NULL, 1, NULL, NULL, 'active', '2026-10-10 05:44:20', '2026-10-10 11:14:26'),
(8, 'Nikhil singh', 'nikhilrura5@gmail.com', '+916676767677', '$2y$10$qo2RoEZWc4sG88Eh50Gihu1bJCeeLysLVh9elQARGNEsyOfKFXDya', NULL, 'email', NULL, 1, NULL, NULL, 'active', '2026-10-11 18:48:12', '2026-10-12 00:18:19');

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
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`);

--
-- Indexes for table `cruises`
--
ALTER TABLE `cruises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `department` (`department`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cruises`
--
ALTER TABLE `cruises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hotel_images`
--
ALTER TABLE `hotel_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD CONSTRAINT `hotel_images_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD CONSTRAINT `hotel_rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
