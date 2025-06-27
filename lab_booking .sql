-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 11:30 AM
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
-- Database: `lab_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `Instructor_ID` int(11) NOT NULL,
  `Instructor_Name` varchar(100) DEFAULT NULL,
  `Instructor_Email` varchar(100) DEFAULT NULL,
  `password` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`Instructor_ID`, `Instructor_Name`, `Instructor_Email`, `password`) VALUES
(1, 'Kamali', 'kamali@eng.jfn.ac.lk', '1234'),
(2, 'Deshani Thilakarathne', 'deshani@gmail.com', '1236'),
(3, 'Devinda Dassanayake', 'devinda@gmail.com', '7896'),
(4, 'Akash Rajapaksha', 'akash@gmail.com', '8766'),
(5, 'Rajiw Subramaniyam', 'rajiw@gmail.com', '5678');

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

CREATE TABLE `lab` (
  `Lab_ID` int(11) NOT NULL,
  `Lab_Name` varchar(100) DEFAULT NULL,
  `Lab_Type` enum('Software','Hardware','Network') DEFAULT NULL,
  `To_ID` int(11) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `Availability` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab`
--

INSERT INTO `lab` (`Lab_ID`, `Lab_Name`, `Lab_Type`, `To_ID`, `Capacity`, `Availability`) VALUES
(1, 'Lab 01 EC5080', 'Software', 1, 50, 'available'),
(2, 'EC5070', 'Software', 4, 50, 'available'),
(3, 'Lab 01 EC5110', 'Network', 5, 40, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `lab_booking`
--

CREATE TABLE `lab_booking` (
  `Booking_ID` int(11) NOT NULL,
  `Request_Date` date DEFAULT NULL,
  `Start_time` time NOT NULL,
  `End_time` time NOT NULL,
  `Semester` int(11) NOT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Instructor_ID` int(11) DEFAULT NULL,
  `Lab_ID` int(11) DEFAULT NULL,
  `Lect_ID` int(11) DEFAULT NULL,
  `Lab_Type` enum('Software','Hardware','Network') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_booking`
--

INSERT INTO `lab_booking` (`Booking_ID`, `Request_Date`, `Start_time`, `End_time`, `Semester`, `Status`, `Instructor_ID`, `Lab_ID`, `Lect_ID`, `Lab_Type`) VALUES
(15, '2025-06-02', '08:00:00', '11:00:00', 5, 'Approved', 1, 1, 1, 'Software'),
(16, '2025-07-01', '08:30:00', '23:29:00', 5, 'Approved', 1, 1, 2, 'Software'),
(17, '2025-07-07', '13:30:00', '16:30:00', 5, 'Approved', 1, 2, 4, 'Software'),
(18, '2025-06-17', '12:29:00', '15:30:00', 5, 'Rejected', 2, 3, 5, 'Hardware'),
(19, '2025-07-16', '20:47:00', '23:47:00', 5, 'Approved', 2, 3, 1, 'Network'),
(20, '2025-07-03', '12:48:00', '16:48:00', 5, 'Approved', 3, 2, 3, 'Software'),
(21, '2025-07-14', '12:49:00', '15:49:00', 5, 'Rejected', 4, 2, 1, 'Software'),
(22, '2025-07-21', '10:30:00', '13:30:00', 5, 'Approved', 5, 2, 5, 'Software'),
(23, '2025-07-02', '17:02:00', '21:02:00', 2, 'Approved', 2, 2, 4, 'Software'),
(24, '2025-07-28', '08:00:00', '11:00:00', 5, 'Approved', 3, 3, 5, 'Network'),
(25, '2025-07-29', '13:30:00', '16:30:00', 5, 'Pending', 4, 2, 3, 'Software');

-- --------------------------------------------------------

--
-- Table structure for table `lab_equipment`
--

CREATE TABLE `lab_equipment` (
  `Equipment_ID` int(11) NOT NULL,
  `TO_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Lab_ID` int(11) DEFAULT NULL,
  `Equip_Condition` varchar(100) DEFAULT NULL,
  `Equipment_Name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_equipment`
--

INSERT INTO `lab_equipment` (`Equipment_ID`, `TO_ID`, `Quantity`, `Lab_ID`, `Equip_Condition`, `Equipment_Name`) VALUES
(1, 1, 60, 1, 'Excellent', 'inteliji IDEA with computers'),
(2, 2, 50, 2, 'Excellent', 'Workbench with computers'),
(3, 4, 50, 3, 'Excellent', 'Qurtusprime with computers');

-- --------------------------------------------------------

--
-- Table structure for table `lab_shedule`
--

CREATE TABLE `lab_shedule` (
  `Shedule_ID` int(11) NOT NULL,
  `Lab_ID` int(11) DEFAULT NULL,
  `Semester` int(11) NOT NULL,
  `Start_time` time DEFAULT NULL,
  `End_time` time NOT NULL,
  `Date` date DEFAULT NULL,
  `Instructor_ID` int(11) DEFAULT NULL,
  `Lect_ID` int(11) DEFAULT NULL,
  `Status` varchar(20) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_shedule`
--

INSERT INTO `lab_shedule` (`Shedule_ID`, `Lab_ID`, `Semester`, `Start_time`, `End_time`, `Date`, `Instructor_ID`, `Lect_ID`, `Status`) VALUES
(8, 1, 5, '08:00:00', '11:00:00', '2025-06-02', 1, 1, 'Approved'),
(9, 3, 5, '20:47:00', '23:47:00', '2025-07-16', 2, 1, 'Approved'),
(10, 1, 5, '08:30:00', '23:29:00', '2025-07-01', 1, 2, 'Approved'),
(11, 2, 5, '12:48:00', '16:48:00', '2025-07-03', 3, 3, 'Approved'),
(12, 2, 5, '13:30:00', '16:30:00', '2025-07-07', 1, 4, 'Approved'),
(13, 2, 5, '10:30:00', '13:30:00', '2025-07-21', 5, 5, 'Approved'),
(14, 2, 2, '17:02:00', '21:02:00', '2025-07-02', 2, 4, 'Approved'),
(15, 3, 5, '08:00:00', '11:00:00', '2025-07-28', 3, 5, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `lab_usage_log`
--

CREATE TABLE `lab_usage_log` (
  `Log_ID` int(11) NOT NULL,
  `Semester` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Entry_Time` time DEFAULT NULL,
  `Exit_Time` time DEFAULT NULL,
  `Booking_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_usage_log`
--

INSERT INTO `lab_usage_log` (`Log_ID`, `Semester`, `Date`, `Entry_Time`, `Exit_Time`, `Booking_ID`) VALUES
(372, 5, '2025-06-02', '08:00:00', '11:00:00', 8);

-- --------------------------------------------------------

--
-- Table structure for table `lecture`
--

CREATE TABLE `lecture` (
  `Lect_ID` int(11) NOT NULL,
  `Lect_Name` varchar(100) DEFAULT NULL,
  `Lect_Email` varchar(100) DEFAULT NULL,
  `password` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecture`
--

INSERT INTO `lecture` (`Lect_ID`, `Lect_Name`, `Lect_Email`, `password`) VALUES
(1, 'Prageeth Rajapaksha', 'prageeth@eng.jfn.ac.lk', '1234'),
(2, 'Suneera Silva', 'suneera@eng.jfn.ac.lk', '1278'),
(3, 'Naveen Kumar', 'naveen@eng.jfn.ac.lk', '9742'),
(4, 'Sulochana Alwis', 'sulochana@eng.jfn.ac.lk', '7896'),
(5, 'Ram Mahendran', 'ram@eng.jfn.ac.lk', '9976');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Stu_ID` varchar(8) NOT NULL,
  `Stu_Email` varchar(100) DEFAULT NULL,
  `Stu_Name` varchar(100) DEFAULT NULL,
  `password` varchar(4) NOT NULL,
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Stu_ID`, `Stu_Email`, `Stu_Name`, `password`, `semester`) VALUES
('1', '2021e033@eng.jfn.ac.lk', 'D.M. Wimalasena', '1234', 5),
('2', '2021e009@eng.jfn.ac.lk', 'A.Y.I.D.Perera', '2027', 7),
('3', '2021e103@eng.jfn.ac.lk', 'Gimhani Dilmika', '4566', 5),
('4', '2021e144@eng.jfn.ac.lk', 'Schintha Nimesh', '7890', 5),
('5', '2022e001@eng.jfn.ac.lk', 'A.B.C.Silva', '1692', 5),
('6', '2022e005@eng.jfn.ac.lk', 'Adithya Waliwatte', '6122', 5),
('7', '2021e110@eng.jfn.ac.lk', 'Kanchna Ariyasinghe', '6782', 7);

-- --------------------------------------------------------

--
-- Table structure for table `to`
--

CREATE TABLE `to` (
  `To_ID` int(11) NOT NULL,
  `To_Name` varchar(100) DEFAULT NULL,
  `To_Email` varchar(100) DEFAULT NULL,
  `password` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `to`
--

INSERT INTO `to` (`To_ID`, `To_Name`, `To_Email`, `password`) VALUES
(1, 'Balachandran', 'Balachandran@eng.jfn.ac.lk', '1234'),
(2, 'Sisira Jayathilake', 'sisira@eng.jfn.ac.lk', '7890'),
(3, 'Lahiru Subasinghe', 'lahiru@eng.jfn.ac.lk', '1457'),
(4, 'Jenarthan M', 'jenarthan@eng.jfn.ac.lk', '2166'),
(5, 'Kalum Aberathne', 'kalum@eng.jfn.ac.lk', '9922');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`Instructor_ID`) USING BTREE;

--
-- Indexes for table `lab`
--
ALTER TABLE `lab`
  ADD PRIMARY KEY (`Lab_ID`);

--
-- Indexes for table `lab_booking`
--
ALTER TABLE `lab_booking`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `Instructor_ID` (`Instructor_ID`),
  ADD KEY `Lab_ID` (`Lab_ID`),
  ADD KEY `Lect_ID` (`Lect_ID`);

--
-- Indexes for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD PRIMARY KEY (`Equipment_ID`),
  ADD KEY `TO_ID` (`TO_ID`),
  ADD KEY `Lab_ID` (`Lab_ID`);

--
-- Indexes for table `lab_shedule`
--
ALTER TABLE `lab_shedule`
  ADD PRIMARY KEY (`Shedule_ID`),
  ADD KEY `Lab_ID` (`Lab_ID`),
  ADD KEY `Instructor_ID` (`Instructor_ID`);

--
-- Indexes for table `lab_usage_log`
--
ALTER TABLE `lab_usage_log`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Booking_ID` (`Booking_ID`);

--
-- Indexes for table `lecture`
--
ALTER TABLE `lecture`
  ADD PRIMARY KEY (`Lect_ID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Stu_ID`);

--
-- Indexes for table `to`
--
ALTER TABLE `to`
  ADD PRIMARY KEY (`To_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lab_booking`
--
ALTER TABLE `lab_booking`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  MODIFY `Equipment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_shedule`
--
ALTER TABLE `lab_shedule`
  MODIFY `Shedule_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `lab_usage_log`
--
ALTER TABLE `lab_usage_log`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=373;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lab`
--
ALTER TABLE `lab`
  ADD CONSTRAINT `lab_ibfk_1` FOREIGN KEY (`To_ID`) REFERENCES `to` (`To_ID`);

--
-- Constraints for table `lab_booking`
--
ALTER TABLE `lab_booking`
  ADD CONSTRAINT `lab_booking_ibfk_2` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`),
  ADD CONSTRAINT `lab_booking_ibfk_3` FOREIGN KEY (`Lab_ID`) REFERENCES `lab` (`Lab_ID`),
  ADD CONSTRAINT `lab_booking_ibfk_4` FOREIGN KEY (`Lect_ID`) REFERENCES `lecture` (`Lect_ID`);

--
-- Constraints for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD CONSTRAINT `lab_equipment_ibfk_1` FOREIGN KEY (`TO_ID`) REFERENCES `to` (`To_ID`),
  ADD CONSTRAINT `lab_equipment_ibfk_2` FOREIGN KEY (`Lab_ID`) REFERENCES `lab` (`Lab_ID`);

--
-- Constraints for table `lab_shedule`
--
ALTER TABLE `lab_shedule`
  ADD CONSTRAINT `lab_shedule_ibfk_1` FOREIGN KEY (`Lab_ID`) REFERENCES `lab` (`Lab_ID`),
  ADD CONSTRAINT `lab_shedule_ibfk_2` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `insert_lab_usage_log` ON SCHEDULE EVERY 1 SECOND STARTS '2025-06-25 17:44:27' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO lab_usage_log (Semester, Date, Entry_Time, Exit_Time, Booking_ID)
    SELECT 
        Semester,
        Date,
        Start_Time,
        End_Time,
        Shedule_ID
    FROM Lab_Shedule
    WHERE Date < CURDATE()
    AND Shedule_ID NOT IN (
        SELECT Booking_ID FROM lab_usage_log
    );
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
