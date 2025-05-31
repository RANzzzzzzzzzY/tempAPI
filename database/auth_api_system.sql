-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 01:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auth_api_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_clients`
--

CREATE TABLE `api_clients` (
  `id` int(11) NOT NULL,
  `dev_id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_clients`
--

INSERT INTO `api_clients` (`id`, `dev_id`, `system_name`, `api_key`, `created_at`, `is_active`) VALUES
(1, 1, 'booking', 'ak_40c9fb3082016b3cb0c3d9e97d49325a', '2025-05-29 14:00:29', 1),
(2, 2, 'Stocks', 'ak_c509d626fa6fa62f37eac5cd354b9cd9', '2025-05-29 14:03:25', 1),
(3, 3, 'test', 'ak_134a18afbc7c1040ad3badd16bf3e3bc', '2025-05-29 14:32:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `api_users`
--

CREATE TABLE `api_users` (
  `id` int(11) NOT NULL,
  `dev_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `is_email_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_users`
--

INSERT INTO `api_users` (`id`, `dev_id`, `email`, `password_hash`, `created_at`, `is_active`, `is_email_verified`) VALUES
(13, 2, 'makiemorales2@gmail.com', '$2y$10$8UDCAGtkyxQhFI0GkrC1..qRkpFPxbOkZCmGOYNwhslNe4lzDyoOO', '2025-05-29 19:05:55', 1, 1),
(14, 2, 'makiemorales1@gmail.com', '$2y$10$86kbsH7CXqXSQKpGPpEWFOFM/LcylbQXfVRmBIoZiDSnav.EgpJFu', '2025-05-29 19:23:37', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(16, 13, '41c1d45738df39d7659ec13c8ab0846cb7835d5ae5178de0c7478b5e4becb620', '2025-05-30 13:16:32', '2025-05-29 19:16:32'),
(17, 14, 'c447e22e44a8eaf89d2084905907853f4aa869e90eb6c8473bd4baaf266bfdc4', '2025-05-30 13:23:37', '2025-05-29 19:23:37'),
(18, 14, 'becfefbcde840e6f5ba562e21d35e1ca468f62ba8c56dc281e182e46e7d3607c', '2025-05-30 13:33:07', '2025-05-29 19:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `dev_accounts`
--

CREATE TABLE `dev_accounts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_email_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dev_accounts`
--

INSERT INTO `dev_accounts` (`id`, `email`, `password_hash`, `full_name`, `created_at`, `is_email_verified`) VALUES
(1, 'makiemorales2@gmail.com', '$2y$10$E7pUugKNc6X9u/MtgNN4i.V4SPgEjEO7hbY.rWoTxjjs3TP9tGqM.', 'Rans Mark A. Morales', '2025-05-29 14:00:29', 1),
(2, 'lorencapuyan@gmail.com', '$2y$10$ULT2wFD.Hs7lpVjggFw/Eee/wuz49gJdoWQU8q03VjBdpVfaXL2OG', 'Loren Capuyan', '2025-05-29 14:03:25', 1),
(3, 'testemail@gmail.com', '$2y$10$5OsdNjUQXRdDxGdk8859a.vRE71zm6Abq0GeJvekVIUUIyTuaWCvO', 'Loren Capuyan', '2025-05-29 14:32:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `email_otps`
--

CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(7) NOT NULL,
  `purpose` enum('verification','password_reset') NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `dev_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL,
  `api_user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_clients`
--
ALTER TABLE `api_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `dev_id` (`dev_id`),
  ADD KEY `idx_api_key` (`api_key`);

--
-- Indexes for table `api_users`
--
ALTER TABLE `api_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_per_dev` (`email`,`dev_id`),
  ADD KEY `dev_id` (`dev_id`),
  ADD KEY `idx_api_user_email` (`email`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dev_accounts`
--
ALTER TABLE `dev_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_dev_email` (`email`);

--
-- Indexes for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dev_id` (`dev_id`),
  ADD KEY `fk_email` (`email`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_user_id` (`api_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_clients`
--
ALTER TABLE `api_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `api_users`
--
ALTER TABLE `api_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `dev_accounts`
--
ALTER TABLE `dev_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_clients`
--
ALTER TABLE `api_clients`
  ADD CONSTRAINT `api_clients_ibfk_1` FOREIGN KEY (`dev_id`) REFERENCES `dev_accounts` (`id`);

--
-- Constraints for table `api_users`
--
ALTER TABLE `api_users`
  ADD CONSTRAINT `api_users_ibfk_1` FOREIGN KEY (`dev_id`) REFERENCES `dev_accounts` (`id`);

--
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `api_users` (`id`);

--
-- Constraints for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD CONSTRAINT `fk_dev_id` FOREIGN KEY (`dev_id`) REFERENCES `api_clients` (`dev_id`),
  ADD CONSTRAINT `fk_email` FOREIGN KEY (`email`) REFERENCES `api_users` (`email`);

--
-- Constraints for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD CONSTRAINT `password_reset_otps_ibfk_1` FOREIGN KEY (`api_user_id`) REFERENCES `api_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
