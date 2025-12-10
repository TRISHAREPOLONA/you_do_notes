-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 12:43 PM
-- Server version: 10.11.14-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `you_do_notes_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `note_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `quantity` int(11) DEFAULT 1,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'Pending',
  `gcash_number` varchar(20) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `platform_commission` decimal(10,2) DEFAULT NULL,
  `seller_earnings` decimal(10,2) DEFAULT NULL,
  `order_ref` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `status`, `created_at`, `quantity`, `total_amount`, `payment_method`, `payment_status`, `gcash_number`, `seller_id`, `platform_commission`, `seller_earnings`, `order_ref`) VALUES
(10, 22, 1, 'Completed', '2025-09-25 12:56:50', 1, 80.00, 'GCash', 'Paid', '09123434561', NULL, 16.00, 64.00, 'ORD68d53c126fe0b'),
(11, 22, 1, 'Completed', '2025-09-28 11:42:55', 1, 80.00, 'GCash', 'Paid', '09123434561', NULL, 16.00, 64.00, 'ORD68d91f3f8f365'),
(12, 22, 1, 'Completed', '2025-09-29 01:40:38', 1, 80.00, 'GCash', 'Paid', '09123434561', NULL, 16.00, 64.00, 'ORD68d9e396a93a0'),
(13, 22, 1, 'Completed', '2025-09-29 02:02:54', 1, 80.00, 'GCash', 'Paid', '09123434561', NULL, 16.00, 64.00, 'ORD68d9e8ce1bd66'),
(14, 22, 4, 'Completed', '2025-09-29 02:02:54', 1, 50.00, 'GCash', 'Paid', '09123434561', NULL, 10.00, 40.00, 'ORD68d9e8ce1bd66'),
(15, 25, 3, 'Completed', '2025-09-29 03:01:17', 1, 60.00, 'GCash', 'Paid', '0916391836171', NULL, 12.00, 48.00, 'ORD68d9f67d5464a'),
(16, 24, 1, 'Completed', '2025-09-29 03:01:49', 1, 80.00, 'Maya', 'Paid', '09199628658', NULL, 16.00, 64.00, 'ORD68d9f69d938b0'),
(17, 24, 1, 'Completed', '2025-09-29 03:02:32', 1, 80.00, 'Maya', 'Paid', '09199628658', NULL, 16.00, 64.00, 'ORD68d9f6c89145f'),
(18, 25, 1, 'Completed', '2025-09-29 03:03:43', 1, 80.00, 'GCash', 'Paid', 'Owisjssj', NULL, 16.00, 64.00, 'ORD68d9f70f45a74'),
(19, 22, 10, 'Completed', '2025-10-03 13:56:11', 1, 95.00, 'GCash', 'Paid', '09123434561', NULL, 19.00, 76.00, 'ORD68dfd5fb0ce40'),
(20, 22, 10, 'Completed', '2025-10-03 14:00:41', 1, 95.00, 'GCash', 'Paid', '09123434561', NULL, 19.00, 76.00, 'ORD68dfd709c34e9'),
(21, 22, 10, 'Completed', '2025-10-03 15:14:42', 1, 95.00, 'GCash', 'Paid', '09123434561', NULL, 19.00, 76.00, 'ORD68dfe86223a53'),
(22, 22, 12, 'Completed', '2025-10-03 15:21:57', 1, 85.00, 'GCash', 'Paid', '09123434561', NULL, 17.00, 68.00, 'ORD68dfea1573f4c'),
(23, 22, 12, 'Completed', '2025-10-06 01:19:40', 1, 85.00, 'GCash', 'Paid', '09123434561', NULL, 17.00, 68.00, 'ORD68e3192c64ab7'),
(24, 31, 16, 'Completed', '2025-12-08 06:40:54', 1, 150.00, 'GCash', 'Paid', '09352470855', NULL, 30.00, 120.00, 'ORD693672f6f3092'),
(25, 22, 3, 'Completed', '2025-12-09 07:50:57', 1, 60.00, 'GCash', 'Paid', '09123456789', NULL, 12.00, 48.00, 'ORD6937d4e1514ef');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `seller_email` varchar(255) NOT NULL,
  `course` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `note_link` varchar(500) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `seller_email`, `course`, `file_path`, `note_link`, `status`) VALUES
(1, 'Database Systems Reviewer', 'Comprehensive reviewer for relational databases and SQL queries.', 80.00, 'admin@youdo.com', 'BSIS', 'uploads/notes/db_reviewer.pdf\r\n', NULL, 'approved'),
(2, 'Systems Analysis Notes', 'Key concepts and diagrams for system analysis and design.', 75.00, 'admin@youdo.com', 'BSIS', 'uploads/notes/sa_notes.pdf', NULL, 'approved'),
(3, 'Networking Fundamentals', 'Covers OSI model, subnetting, and basic protocols.', 60.00, 'admin@youdo.com', 'BSIT', 'uploads/notes/networking.pdf', NULL, 'approved'),
(4, 'Web Development Cheat Sheet', 'HTML, CSS, and JS essentials for IT students.', 50.00, 'admin@youdo.com', 'BSIT', 'uploads/notes/WebDev.pdf', NULL, 'approved'),
(5, 'Data Structures & Algorithms', 'Summaries of sorting, searching, and complexity analysis.', 100.00, 'admin@youdo.com', 'BSCS', 'uploads/notes/dsa_notes.pdf', NULL, 'approved'),
(6, 'Artificial Intelligence Notes', 'AI basics, search algorithms, and machine learning intro.', 120.00, 'admin@youdo.com', 'BSCS', 'uploads/notes/ai_notes.pdf\r\n', NULL, 'approved'),
(7, 'Anatomy & Physiology Reviewer', 'Essential body systems with diagrams.', 90.00, 'admin@youdo.com', 'BSNURSING', 'uploads/notes/anatomy.pdf', NULL, 'approved'),
(8, 'Pharmacology Notes', 'Drug classifications, dosages, and nursing responsibilities.', 110.00, 'admin@youdo.com', 'BSNURSING', 'uploads/notes/pharmacology.pdf', NULL, 'approved'),
(9, 'Basic Electrical Circuits', 'Notes on Ohm’s Law, Kirchhoff’s rules, and AC/DC.', 70.00, 'admin@youdo.com', 'BSELECTRICALTECH', 'uploads/notes/circuits.pdf\r\n', NULL, 'approved'),
(10, 'Power Systems Reviewer', 'Generation, transmission, and distribution concepts.', 95.00, 'admin@youdo.com', 'BSELECTRICALTECH', 'https://docs.google.com/document/d/1HGKTa69buN0MbHRgJ47c857GCo-XqlFmtCczEB7OHo0/edit?usp=sharing', NULL, 'approved'),
(11, 'Educational Psychology Notes', 'Learning theories and classroom applications.', 65.00, 'admin@youdo.com', 'BSEDUC', 'uploads/notes/psychology.pdf', NULL, 'approved'),
(12, 'Curriculum Development Reviewer', 'Frameworks and models for designing effective curriculum.', 85.00, 'admin@youdo.com', 'BSEDUC', 'https://docs.google.com/document/d/1kOmnSs4qEw8DrVFMJNgq-FNmvjwuqaOGDFdRbvI0VRM/edit?usp=sharing', NULL, 'approved'),
(13, 'SAMPLE', 'This is just a Sample upload, no need to Download it', 0.00, 'guest@bicol-u.edu.ph', '', 'https://docs.google.com/document/d/1PEKgFF4PvkQqRbhaFeBs9He3l4HWLF74Ujp1LWYclIA/edit?usp=sharing', '', 'approved'),
(14, 'Kunwari', 'This is an example ', 100.00, 'buds@bicol-u.edu.ph', '', 'uploads/notes/platform_modification_Villanueva_Ondis_Repolona_Honradez.pdf', NULL, 'approved'),
(16, 'Mama mo blue', 'Activity siya basta ', 150.00, 'cjo@bicol-u.edu.ph', '', 'https://docs.google.com/document/d/1-cTafewaGpTUy7rJlktn6DGXT4q3kqpn6Jy86iO0rcc/edit?usp=sharing', NULL, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `study_guides`
--

CREATE TABLE `study_guides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_guides`
--

INSERT INTO `study_guides` (`id`, `title`, `description`, `price`, `file_path`, `uploaded_at`) VALUES
(1, 'Sample Guide', 'Test description', 100.00, 'uploads/test.pdf', '2025-09-14 13:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `pin` varchar(20) NOT NULL,
  `gcash_number` int(20) DEFAULT NULL,
  `paymaya_number` int(20) DEFAULT NULL,
  `payment_method` enum('GCash','PayMaya') DEFAULT 'GCash',
  `balance` decimal(10,2) DEFAULT 0.00,
  `loyalty_points` int(11) DEFAULT 0,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `member_tier` enum('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `address`, `contact`, `role`, `pin`, `gcash_number`, `paymaya_number`, `payment_method`, `balance`, `loyalty_points`, `total_spent`, `member_tier`) VALUES
(22, 'Elena Gilbert', 'guest@bicol-u.edu.ph', '$2y$10$G634fCjXgYNmY0LJJ/qwROzwkYDAfjUvW1a48D.keQC2B3lyHa2SS', 'California,', '09123456789', 'user', '123123', 2147483647, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(24, 'Jem', 'guest3@bicol-u.edu.ph', '$2y$10$q4ggItj55ohIIeWwT0sVIuHqEYdt.eqLBnY39LHHppZM4N9M1Whki', 'Banao Guinobatan', '09199628658', 'user', '0000', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(25, 'Guest99', 'guest99@bicol-u.edu.ph', '$2y$10$lTNbslXQajHa9S7iXtkGHeBlWWeqP6S9GKsn1TyYB6BF9YGEcEPVC', 'Guinobatan ', '0910826271', 'user', '0000', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(26, 'Hehe', 'buds@bicol-u.edu.ph', '$2y$10$AfCFjQQbDyB20hXrEVoGCucnB8jA6UASJwIiK2nzFYkRHkCFR7cZy', '', '', 'user', '0000', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(27, 'Christine Joy Lotino Bande', 'cjlb@bicol-u.edu.ph', '$2y$10$xxaFPMtnhOTtFxNXmXgJhu3tmDbm0QQe6pgNpI65YmvLKokC4vk5m', '', '', 'user', '123456', 2147483647, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(29, 'Christine Jade Ondis', 'cjo@bicol-u.edu.ph', '$2y$10$Hkdt5zjGJZ78WoXXaC/5KuEywlmeR4vLqaYRi14vOhfBwE.HAD2oq', '', '', 'user', '123456', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(30, 'admin', 'admin@bicol-u.edu.ph', 'adminrole', 'Polangui Albay', '09123456789', 'admin', '1234567', 912345678, 912345678, 'GCash', 100.00, 0, 0.00, 'Gold'),
(31, 'rose', 'rose@bicol-u.edu.ph', '12345678', NULL, '09352470855', 'user', '1111', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(37, '4', 'rosea@bicol-u.edu.ph', '$2y$10$vhXIklOcYP2doX.IZd5PheUfzhSL07o91.Z3ZMFfeYs1hWFhy/7Ui', '', '', 'user', '1111', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(39, '6', 'rose1@bicol-u.edu.ph', '$2y$10$fvhBdf4bcCY8IxvHik1KXuQeP2Zi2UpOzwQAdId6MslocOtzHdssu', '', '', 'user', '1111', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze'),
(40, 'Rose ', 'rose0@bicol-u.edu.ph', '$2y$10$/xJtMwlosLodifJhSTw20eMIhKwwwD4fVQJVLaTnq98BGk1jO1xny', '', '', 'user', '1234', NULL, NULL, 'GCash', 0.00, 0, 0.00, 'Bronze');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_guides`
--
ALTER TABLE `study_guides`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `study_guides`
--
ALTER TABLE `study_guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
