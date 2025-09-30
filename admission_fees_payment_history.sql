-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 06:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admission_fees_payment_history`
--

CREATE TABLE `admission_fees_payment_history` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `payment_amount` decimal(10,0) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `full_paid_admission_payment_ids` text DEFAULT NULL,
  `partial_payment_ids_backup` varchar(20) DEFAULT NULL,
  `unpaid_admission_fees_rows_backup` text DEFAULT NULL,
  `wallet_affected_balance` decimal(10,0) DEFAULT NULL,
  `wallet_transaction_id` varchar(50) DEFAULT NULL,
  `method` varchar(20) DEFAULT 'cash',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admission_fees_payment_history`
--
ALTER TABLE `admission_fees_payment_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admission_fees_payment_history`
--
ALTER TABLE `admission_fees_payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
