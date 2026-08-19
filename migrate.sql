-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260729.a0d1231b75
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 19, 2026 at 09:05 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `settings_admit_card`
--

CREATE TABLE `settings_admit_card` (
  `id` bigint NOT NULL,
  `admit_card_design` varchar(50) DEFAULT 'design-1',
  `colors` json DEFAULT NULL,
  `school_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `school_contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings_admit_card`
--

INSERT INTO `settings_admit_card` (`id`, `admit_card_design`, `colors`, `school_address`, `school_contact`, `instructions`) VALUES
(5, 'design-1', '{\"dark\": \"#2b3a5a\", \"light\": \"#f0f5ff\", \"primary\": \"#3a4a6d\", \"background\": \"#ffffff\", \"school_name\": \"#eff0f2\", \"school_address\": \"#ffffff\"}', 'Teghari Bazar, Rajput Teghari, Raghunathganj, Murshidabad, 742213', 'Phone: 8348313317 / 9083156928', '# Examination Instructions\r\n\r\n1. Bring this Admit Card to the examination hall.\r\n2. Report at least **30 minutes before** the exam.\r\n3. Bring your required stationery.\r\n4. Mobile phones and electronic devices are not allowed.\r\n5. Follow the invigilator’s instructions.\r\n6. Use of unfair means will lead to disqualification.\r\n\r\n**Best of Luck!**\r\n');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `settings_admit_card`
--
ALTER TABLE `settings_admit_card`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings_admit_card`
--
ALTER TABLE `settings_admit_card`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
