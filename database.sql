-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2020 at 04:49 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spsdaurm_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `dhruv_patient_bio`
--

CREATE TABLE `dhruv_patient_bio` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `age` int(11) NOT NULL,
  `sex` varchar(1) NOT NULL,
  `phone` bigint(11) NOT NULL,
  `op_number` varchar(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `files` text DEFAULT NULL,
  `comments` text NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Name Age Phone number ';

-- --------------------------------------------------------

--
-- Table structure for table `dhruv_procedures`
--

CREATE TABLE `dhruv_procedures` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `patient_op_number` varchar(11) NOT NULL,
  `procedure_done` text NOT NULL,
  `misc_details` text NOT NULL,
  `next_appointment` date NOT NULL,
  `files` text DEFAULT NULL,
  `doctor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dhruv_users`
--

CREATE TABLE `dhruv_users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` text NOT NULL,
  `name` text NOT NULL,
  `phone` text NOT NULL,
  `regno` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dhruv_patient_bio`
--
ALTER TABLE `dhruv_patient_bio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `op_number` (`op_number`);

--
-- Indexes for table `dhruv_procedures`
--
ALTER TABLE `dhruv_procedures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dhruv_users`
--
ALTER TABLE `dhruv_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dhruv_patient_bio`
--
ALTER TABLE `dhruv_patient_bio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dhruv_procedures`
--
ALTER TABLE `dhruv_procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dhruv_users`
--
ALTER TABLE `dhruv_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
