-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 05:35 AM
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
-- Database: `ev`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password_hash`) VALUES
(0, 'admin', 'admin@example.com', '$2y$10$0aJnJMRY2dHrc2WKgDUEu.UlQ.hiusVREzKhdBimQiU8y9ey5agBm');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `charger_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `booking_status` enum('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `charger_id`, `booking_date`, `start_time`, `end_time`, `booking_status`) VALUES
(1, 1, 1, '2026-10-25', '09:00:00', '10:00:00', 'Pending'),
(2, 2, 2, '2026-10-25', '10:00:00', '11:00:00', 'Confirmed'),
(3, 3, 3, '2026-10-25', '11:00:00', '12:00:00', 'Cancelled'),
(4, 4, 4, '2026-10-18', '14:00:00', '15:00:00', 'Completed'),
(5, 5, 5, '2026-10-26', '09:00:00', '10:00:00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `chargers`
--

CREATE TABLE `chargers` (
  `charger_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `charger_type` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chargers`
--

INSERT INTO `chargers` (`charger_id`, `station_id`, `charger_type`, `status`) VALUES
(1, 1, 'Level 2', 'Available'),
(2, 1, 'Level 2', 'In Use'),
(3, 4, 'Level 2', 'Available'),
(4, 5, 'Level 2', 'Out Of Service'),
(5, 5, 'Level 2', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `facility_name` varchar(50) NOT NULL,
  `facility_type` varchar(30) NOT NULL,
  `address` varchar(100) NOT NULL,
  `district` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_id`, `facility_name`, `facility_type`, `address`, `district`, `status`) VALUES
(6, 'Cuci Xpress Tungku Link', 'Car Wash', 'Eng Ho Complex, Tungku Link Highway', 'Brunei-Muara', 'Open'),
(7, 'Cuci Xpress Salar', 'Car Wash', 'Salar Light Industrial, Jalan Muara', 'Brunei-Muara', 'Open'),
(8, 'Shell Beribi Petrol Station', 'Petrol Station', 'Bandar Seri Begawan BE1118', 'Brunei-Muara', 'Open'),
(9, 'Shell Mentiri - Ikatan Teluk Mewah', 'Petrol Station', 'Jalan Kota Batu, Kampong Mentiri', 'Brunei-Muara', 'Open'),
(10, 'Tyremart Mentiri', 'Air Pump', 'Jalan Kota Batu, Mentiri BU1929', 'Brunei-Muara', 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `review_date` date NOT NULL,
  `review_status` enum('Visible','Hidden') NOT NULL DEFAULT 'Visible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `station_id`, `facility_id`, `rating`, `comment`, `review_date`, `review_status`) VALUES
(1, 1, 1, NULL, 5, 'Charging location was easy to find.', '2026-10-15', 'Visible'),
(2, 2, 4, NULL, 4, 'Good location, but access restrictions should be clearer.', '2026-10-16', 'Visible'),
(3, 3, NULL, 6, 4, 'Car wash service was satisfactory.', '2026-10-17', 'Visible'),
(4, 4, NULL, 8, 3, 'Facility information could be improved.', '2026-10-18', 'Hidden'),
(5, 5, 5, NULL, 2, 'The charging availability information needs updating.', '2026-10-19', 'Visible');

-- --------------------------------------------------------

--
-- Table structure for table `stations`
--

CREATE TABLE `stations` (
  `station_id` int(11) NOT NULL,
  `station_name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `districts` varchar(50) NOT NULL,
  `opening_hours` varchar(50) NOT NULL,
  `station_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`station_id`, `station_name`, `address`, `districts`, `opening_hours`, `station_status`) VALUES
(1, 'After Chukkas Restaurante Asador', 'Royal Brunei Polo and Riding Club, Jerudong', 'Brunei-Muara', '15:00–23:00', 'Closed'),
(4, 'Porsche Centre Brunei', 'Lot 60, Tapak Perindustrian Beribi, Bandar Seri Begawan BE1118', 'Brunei-Muara', '8:00–17:00', 'Open'),
(5, 'Shell Recharge Charging Station', 'Jalan Utama Berakas, Bandar Seri Begawan BB1714', 'Brunei-Muara', '00:00–23:59', 'Unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password_hash`) VALUES
(1, 'Aiman Haziq', 'aiman01', 'aiman01@example.com', 'TEMP_HASH_1'),
(2, 'Nur Aisyah', 'aisyah02', 'aisyah02@example.com', 'TEMP_HASH_2'),
(3, 'Muhammad Danish', 'danish03', 'danish03@example.com', 'TEMP_HASH_3'),
(4, 'Siti Nurin', 'nurin04', 'nurin04@example.com', 'TEMP_HASH_4'),
(5, 'Adam Firdaus', 'adam05', 'adam05@example.com', 'TEMP_HASH_5');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `charger_id` (`charger_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chargers`
--
ALTER TABLE `chargers`
  ADD PRIMARY KEY (`charger_id`),
  ADD KEY `station_id` (`station_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`station_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chargers`
--
ALTER TABLE `chargers`
  MODIFY `charger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`charger_id`) REFERENCES `chargers` (`charger_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `chargers`
--
ALTER TABLE `chargers`
  ADD CONSTRAINT `chargers_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`station_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`station_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
