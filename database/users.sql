-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 04:04 PM
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
-- Database: `rag_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'mahasiswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'bambang1', 'bambang@gmail.com', NULL, '$2y$12$IjYt3qJWZ0sdFq6oZOVHyOfsU1XowMT6ZgIA6wwgJS3Hu6tfvRH3a', 'MFbuqZLCGlQ26HkBq9yaXu8cXfsedcSlw3EEgDDZvQjD6Ay4fTrqqSxrdXRI', '2026-04-26 03:30:25', '2026-04-26 03:30:25', 'mahasiswa'),
(2, 'bambang', 'stokccgs27@gmail.com', NULL, '$2y$12$ejQHpiJo6gvA9htv2pI9/ucZxTv4kOYRZZ22ItDE24MUAJR26rZOW', 'FVcqTOdRIrw1l97fsPg3StJxIutkV469oxqIKMOEWAG6LYQKLX8AqmGk9v2Q', '2026-04-26 08:44:47', '2026-04-26 08:44:47', 'mahasiswa'),
(5, 'aero', 'admin@if.local', NULL, '$2y$12$45ssZ14dPOyVxst6PERzCOXXeb297n.y443K5vYtFHW2B7pYcQu6S', NULL, '2026-05-30 10:37:57', '2026-05-30 10:37:57', 'admin'),
(6, 'admin', 'admin@gmail.com', NULL, '$2y$12$AveUJQ1r0oleCcHMZ6H.YeWOFIoi4cINz5hOc6tf0uJwR7aHeMnIW', 'vQ5voWF0hWBKpHtURGuw3IqbE1sUlERypsiEYOF3GhbWAN9QPg82XYEnZ9JN', '2026-06-15 08:00:13', '2026-06-15 08:00:13', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
