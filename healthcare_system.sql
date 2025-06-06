-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 04, 2025 lúc 07:28 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `healthcare_system`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `AvailabilityID` int(11) DEFAULT NULL,
  `NurseID` int(11) DEFAULT NULL,
  `AppointmentDateTime` datetime NOT NULL,
  `ReasonForVisit` text DEFAULT NULL,
  `Status` enum('Scheduled','Confirmed','Completed','CancelledByPatient','CancelledByClinic','NoShow','Rescheduled') DEFAULT 'Scheduled',
  `Notes` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `AvailabilityID`, `NurseID`, `AppointmentDateTime`, `ReasonForVisit`, `Status`, `Notes`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 1, 4, NULL, '2025-06-02 09:00:00', 'addictive', 'Completed', NULL, '2025-06-02 16:05:23', '2025-06-03 03:19:04'),
(2, 1, 1, 8, NULL, '2025-06-07 08:30:00', 'sick', 'Scheduled', NULL, '2025-06-03 04:11:46', '2025-06-03 04:11:46'),
(4, 1, 1, 9, NULL, '2025-06-10 07:50:00', 're-examination', 'CancelledByPatient', NULL, '2025-06-03 09:24:07', '2025-06-03 10:39:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctoravailability`
--

CREATE TABLE `doctoravailability` (
  `AvailabilityID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `AvailableDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `IsBooked` tinyint(1) DEFAULT 0,
  `SlotType` enum('Working','Break','Holiday','Blocked') DEFAULT 'Working',
  `Notes` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `doctoravailability`
--

INSERT INTO `doctoravailability` (`AvailabilityID`, `DoctorID`, `AvailableDate`, `StartTime`, `EndTime`, `IsBooked`, `SlotType`, `Notes`, `CreatedAt`, `UpdatedAt`) VALUES
(4, 1, '2025-06-02', '09:00:00', '09:30:00', 1, 'Working', NULL, '2025-06-02 15:37:39', '2025-06-02 16:05:23'),
(5, 1, '2025-06-02', '10:00:00', '10:30:00', 0, 'Working', NULL, '2025-06-02 15:37:39', '2025-06-02 15:37:39'),
(6, 1, '2025-06-03', '14:00:00', '14:30:00', 1, 'Working', NULL, '2025-06-02 15:37:39', '2025-06-03 08:35:47'),
(8, 1, '2025-06-07', '08:30:00', '09:30:00', 1, 'Working', NULL, '2025-06-03 03:38:18', '2025-06-03 04:11:46'),
(9, 1, '2025-06-10', '07:50:00', '08:35:00', 0, 'Working', NULL, '2025-06-03 03:46:16', '2025-06-03 10:39:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctornurseassignments`
--

CREATE TABLE `doctornurseassignments` (
  `AssignmentID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `NurseID` int(11) NOT NULL,
  `AssignedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctors`
--

CREATE TABLE `doctors` (
  `DoctorID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `SpecializationID` int(11) DEFAULT NULL,
  `Bio` text DEFAULT NULL,
  `ExperienceYears` int(11) DEFAULT 0,
  `ConsultationFee` decimal(10,2) DEFAULT 0.00,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `doctors`
--

INSERT INTO `doctors` (`DoctorID`, `UserID`, `SpecializationID`, `Bio`, `ExperienceYears`, `ConsultationFee`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 2, 1, 'Dr. Duc is a renowned cardiologist with over 15 years of experience in treating various heart conditions. He is dedicated to providing compassionate and comprehensive care to his patients.', 15, 150.00, '2025-06-02 15:36:44', '2025-06-03 02:58:59'),
(2, 8, 1, 'an ba to com', 2, 3.00, '2025-06-04 01:59:57', '2025-06-04 01:59:57'),
(4, 11, 2, '123', 20, 0.05, '2025-06-04 02:48:56', '2025-06-04 02:48:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicalrecords`
--

CREATE TABLE `medicalrecords` (
  `RecordID` int(11) NOT NULL,
  `AppointmentID` int(11) DEFAULT NULL,
  `PatientID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `VisitDate` datetime NOT NULL,
  `Symptoms` text DEFAULT NULL,
  `Diagnosis` text DEFAULT NULL,
  `TreatmentPlan` text DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `NextAppointmentDate` date DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `medicalrecords`
--

INSERT INTO `medicalrecords` (`RecordID`, `AppointmentID`, `PatientID`, `DoctorID`, `VisitDate`, `Symptoms`, `Diagnosis`, `TreatmentPlan`, `Notes`, `NextAppointmentDate`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 2, 1, 1, '2025-06-07 08:30:00', 'addictive', 'game addictive', 'soi ech', 'An ba to com 1', NULL, '2025-06-03 04:15:34', '2025-06-03 08:34:46'),
(2, NULL, 1, 1, '2025-06-03 14:00:00', 'tired', 'lack of vitamin C', 'eat foods that help improve the quantity of vitamin C', '', NULL, '2025-06-03 08:42:01', '2025-06-03 08:42:01'),
(3, 4, 1, 1, '2025-06-10 07:50:00', '', '', '', '', NULL, '2025-06-03 09:26:03', '2025-06-03 09:26:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicines`
--

CREATE TABLE `medicines` (
  `MedicineID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Manufacturer` varchar(100) DEFAULT NULL,
  `Unit` varchar(50) DEFAULT NULL,
  `StockQuantity` int(11) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `medicines`
--

INSERT INTO `medicines` (`MedicineID`, `Name`, `Description`, `Manufacturer`, `Unit`, `StockQuantity`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Paracetamol 500mg', 'Reduces pain and fever.', 'Generic Pharma', 'tablet', 1000, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(2, 'Amoxicillin 250mg/5ml Suspension', 'Antibiotic for bacterial infections.', 'MediCorp', 'bottle 60ml', 500, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(3, 'Ibuprofen 200mg', 'Anti-inflammatory, pain relief.', 'HealthWell', 'tablet', 750, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(4, 'Loratadine 10mg', 'Antihistamine for allergies.', 'AllergyFree Ltd.', 'tablet', 300, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(5, 'Omeprazole 20mg', 'Proton pump inhibitor for stomach ulcers.', 'GastroCare', 'capsule', 400, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(6, 'Salbutamol Inhaler 100mcg/dose', 'Bronchodilator for asthma.', 'Respira Solutions', 'inhaler', 150, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(7, 'Metformin 500mg', 'Medication for type 2 diabetes.', 'DiabControl Inc.', 'tablet', 600, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(8, 'Aspirin 81mg (Low Dose)', 'Antiplatelet agent, cardiovascular disease prevention.', 'CardioSafe', 'tablet', 800, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(9, 'Vitamin C 500mg Effervescent', 'Vitamin C supplement, immune booster.', 'VitaBoost', 'effervescent tablet', 250, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(10, 'Cetirizine 10mg', 'Antihistamine for allergies.', 'Generic Pharma', 'tablet', 350, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(11, 'Atorvastatin 20mg', 'Lowers cholesterol levels.', 'LipidLow', 'tablet', 450, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(12, 'Panadol Extra (Paracetamol & Caffeine)', 'Pain relief, fever reducer, with caffeine.', 'GSK Pharma', 'tablet', 550, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(13, 'Berberine 10mg', 'Treats diarrhea, intestinal infections.', 'HerbalMed Co.', 'capsule', 700, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(14, 'B Complex Vitamins', 'Supplement for B-group vitamins.', 'NeuroCare Ltd.', 'tablet', 320, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(15, 'Iron Supplement (Ferrous Sulfate 325mg)', 'Iron supplement for anemia.', 'HemaPlus Corp.', 'tablet', 280, '2025-06-03 05:06:59', '2025-06-03 05:06:59'),
(16, 'Povidone-Iodine Solution 10%', 'Antiseptic solution for wounds.', 'Antisept Co.', 'bottle 100ml', 200, '2025-06-03 05:07:24', '2025-06-03 05:07:24'),
(17, 'Clotrimazole Cream 1%', 'Topical antifungal cream.', 'DermaHeal Inc.', 'tube 20g', 180, '2025-06-03 05:07:24', '2025-06-03 05:07:24'),
(18, 'Normal Saline Solution 0.9%', 'Sterile saline solution for wound rinsing, eye/nasal drops.', 'PharmaSolution', 'bottle 500ml', 900, '2025-06-03 05:07:24', '2025-06-03 05:07:24'),
(19, 'Hydrocortisone Cream 1%', 'Topical corticosteroid for skin inflammation and itching.', 'SkinRelief Co.', 'tube 15g', 220, '2025-06-03 05:07:24', '2025-06-03 05:07:24'),
(20, 'Multivitamin Syrup for Children', 'Daily multivitamin supplement for children.', 'PediaWell', 'bottle 120ml', 180, '2025-06-03 05:07:24', '2025-06-03 05:07:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nurses`
--

CREATE TABLE `nurses` (
  `NurseID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `patientfeedbacks`
--

CREATE TABLE `patientfeedbacks` (
  `FeedbackID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DoctorID` int(11) DEFAULT NULL,
  `AppointmentID` int(11) DEFAULT NULL,
  `Rating` tinyint(4) DEFAULT NULL CHECK (`Rating` >= 1 and `Rating` <= 5),
  `Comments` text DEFAULT NULL,
  `IsPublished` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `patients`
--

CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Gender` enum('Male','Female','Other') DEFAULT NULL,
  `BloodType` varchar(5) DEFAULT NULL,
  `InsuranceInfo` varchar(255) DEFAULT NULL,
  `MedicalHistorySummary` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `patients`
--

INSERT INTO `patients` (`PatientID`, `UserID`, `DateOfBirth`, `Gender`, `BloodType`, `InsuranceInfo`, `MedicalHistorySummary`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, '2004-06-08', 'Male', 'A+', '', '', '2025-06-02 15:14:13', '2025-06-04 11:44:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `prescriptions`
--

CREATE TABLE `prescriptions` (
  `PrescriptionID` int(11) NOT NULL,
  `RecordID` int(11) NOT NULL,
  `MedicineID` int(11) NOT NULL,
  `Dosage` varchar(100) DEFAULT NULL,
  `Frequency` varchar(100) DEFAULT NULL,
  `Duration` varchar(100) DEFAULT NULL,
  `Instructions` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `prescriptions`
--

INSERT INTO `prescriptions` (`PrescriptionID`, `RecordID`, `MedicineID`, `Dosage`, `Frequency`, `Duration`, `Instructions`, `CreatedAt`, `UpdatedAt`) VALUES
(4, 1, 2, '1 tablet', 'Twice a day', '7 days', 'After Meal', '2025-06-03 08:34:46', '2025-06-03 08:34:46'),
(5, 1, 12, '1 tablet', 'Once a day', '10 days', 'Before Meal', '2025-06-03 08:34:46', '2025-06-03 08:34:46'),
(6, 2, 9, '1 tablet', 'Twice a day', '7 days', 'After Meal', '2025-06-03 08:42:01', '2025-06-03 08:42:01'),
(7, 3, 4, '1 tablet', 'Twice a day', '7 days', 'After Meal', '2025-06-03 09:26:03', '2025-06-03 09:26:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `specializations`
--

CREATE TABLE `specializations` (
  `SpecializationID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `specializations`
--

INSERT INTO `specializations` (`SpecializationID`, `Name`, `Description`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Cardiology', 'Deals with disorders of the heart as well as some parts of the circulatory system.', '2025-06-02 15:35:34', '2025-06-03 14:43:21'),
(2, 'Dermatology', 'Deals with the skin, nails, hair and its diseases.', '2025-06-02 15:35:34', '2025-06-02 15:35:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `systemsettings`
--

CREATE TABLE `systemsettings` (
  `SettingKey` varchar(50) NOT NULL,
  `SettingValue` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `systemsettings`
--

INSERT INTO `systemsettings` (`SettingKey`, `SettingValue`, `Description`, `UpdatedAt`) VALUES
('clinic_logo_url', '/images/default_logo.png', 'Đường dẫn đến file logo', '2025-06-02 10:49:31'),
('clinic_name', 'Phòng Khám Đa Khoa Hasagi', 'Tên của phòng khám/bệnh viện', '2025-06-02 10:49:31'),
('theme_color_primary', '#4A90E2', 'Màu chủ đạo của website', '2025-06-02 10:49:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Role` enum('Admin','Doctor','Nurse','Patient') NOT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `Status` enum('Active','Inactive','Pending') DEFAULT 'Pending',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`UserID`, `Username`, `PasswordHash`, `Email`, `FullName`, `PhoneNumber`, `Address`, `Role`, `Avatar`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'long', '$2y$10$2aGN0CIBibP2VbrT7gePEuooikAbcfO5F9iDAWlkr88Mo33ywuDgC', 'duycomok@gmail.com', 'Mạc Long', '0327999555', '', 'Patient', 'uploads/avatars/avatar_1_684031987ffb86.31078388.jpg', 'Active', '2025-06-02 15:14:13', '2025-06-04 11:44:24'),
(2, 'dr.duc', '$2y$15$wUg83y/z0dOOMpz2eDtqL.8Kj0bcV4a95yluLW6w/2HMT7dleFory', 'dr.duc@gmail.com', 'Dr. Duc', '0901234567', '123 Nguyen Trai, Ha Noi', 'Doctor', NULL, 'Active', '2025-06-02 15:36:12', '2025-06-03 14:54:30'),
(3, 'admin', '$2y$10$UBDO6aXM8tNN5TxZyNrjsOFpCp310RjowfiEDX0BBWTnsoQR5aLBe', 'duykhoadd1@gmail.com', 'Administrator', '03279037024', '1234 Admin Street, System City', 'Admin', '', 'Active', '2025-06-03 14:40:13', '2025-06-04 15:15:25'),
(4, 'thien', '$2y$10$qclOr1BPlo69cmXRCdZ5X.UsyYGbRODrK69rmbkRCRcZGlDYPnebK', 'thien@gmail.com', 'Vũ Văn Thiện', '', '', 'Nurse', NULL, 'Active', '2025-06-03 16:23:15', '2025-06-03 16:23:15'),
(8, 'ngocanh', '$2y$10$YAmc6LrCcSPJudGmis8nnO2E7SwlQpFHS0duaGeipWlKrhl2.kmLq', 'ngocanh@dakhoa.com', 'Ngọc Anh', '1', '', 'Doctor', NULL, 'Pending', '2025-06-04 01:59:57', '2025-06-04 11:41:14'),
(11, 'nguyenduyuc', '$2y$10$fBCltWXTkWH6sTZ0CF/iHO/SuWvbELiOBtqgLFeAzYXMq.BeRuO1u', 'nguyenduyuc@dakhoa.com', 'Nguyễn Duy Đức', '0222333444', '', 'Doctor', NULL, 'Inactive', '2025-06-04 02:48:56', '2025-06-04 11:41:35'),
(12, 'mailephuongloan', '$2y$10$bxQ4VUq1QtA8xVGn3Rkq..SN9KhxqC23/33u4S7p.C0xvqUOhY9Rq', 'mailephuongloan@dakhoa.com', 'Mai Lê Phương Loan', '0777222555', '', 'Nurse', NULL, 'Inactive', '2025-06-04 03:11:45', '2025-06-04 03:41:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vitalsigns`
--

CREATE TABLE `vitalsigns` (
  `VitalSignID` int(11) NOT NULL,
  `AppointmentID` int(11) DEFAULT NULL,
  `PatientID` int(11) NOT NULL,
  `RecordedByUserID` int(11) DEFAULT NULL,
  `RecordedAt` datetime DEFAULT current_timestamp(),
  `HeartRate` int(11) DEFAULT NULL,
  `Temperature` decimal(4,1) DEFAULT NULL,
  `BloodPressureSystolic` int(11) DEFAULT NULL,
  `BloodPressureDiastolic` int(11) DEFAULT NULL,
  `RespiratoryRate` int(11) DEFAULT NULL,
  `Weight` decimal(5,2) DEFAULT NULL,
  `Height` decimal(5,2) DEFAULT NULL,
  `OxygenSaturation` int(11) DEFAULT NULL,
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vitalsigns`
--

INSERT INTO `vitalsigns` (`VitalSignID`, `AppointmentID`, `PatientID`, `RecordedByUserID`, `RecordedAt`, `HeartRate`, `Temperature`, `BloodPressureSystolic`, `BloodPressureDiastolic`, `RespiratoryRate`, `Weight`, `Height`, `OxygenSaturation`, `Notes`) VALUES
(1, 2, 1, 4, '2025-06-05 00:24:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD UNIQUE KEY `AvailabilityID` (`AvailabilityID`),
  ADD KEY `NurseID` (`NurseID`),
  ADD KEY `idx_appointment_doctor` (`DoctorID`),
  ADD KEY `idx_appointment_patient` (`PatientID`),
  ADD KEY `idx_appointment_datetime` (`AppointmentDateTime`),
  ADD KEY `idx_appointment_status` (`Status`);

--
-- Chỉ mục cho bảng `doctoravailability`
--
ALTER TABLE `doctoravailability`
  ADD PRIMARY KEY (`AvailabilityID`),
  ADD UNIQUE KEY `unique_doctor_time_slot` (`DoctorID`,`AvailableDate`,`StartTime`);

--
-- Chỉ mục cho bảng `doctornurseassignments`
--
ALTER TABLE `doctornurseassignments`
  ADD PRIMARY KEY (`AssignmentID`),
  ADD UNIQUE KEY `unique_doctor_nurse` (`DoctorID`,`NurseID`),
  ADD KEY `NurseID` (`NurseID`);

--
-- Chỉ mục cho bảng `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`DoctorID`),
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD KEY `idx_doctor_specialization` (`SpecializationID`);

--
-- Chỉ mục cho bảng `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD PRIMARY KEY (`RecordID`),
  ADD UNIQUE KEY `AppointmentID` (`AppointmentID`),
  ADD KEY `idx_medicalrecord_patient` (`PatientID`),
  ADD KEY `idx_medicalrecord_doctor` (`DoctorID`),
  ADD KEY `idx_medicalrecord_visitdate` (`VisitDate`);

--
-- Chỉ mục cho bảng `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`MedicineID`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Chỉ mục cho bảng `nurses`
--
ALTER TABLE `nurses`
  ADD PRIMARY KEY (`NurseID`),
  ADD UNIQUE KEY `UserID` (`UserID`);

--
-- Chỉ mục cho bảng `patientfeedbacks`
--
ALTER TABLE `patientfeedbacks`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `AppointmentID` (`AppointmentID`);

--
-- Chỉ mục cho bảng `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`),
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD KEY `idx_patient_dob` (`DateOfBirth`);

--
-- Chỉ mục cho bảng `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`PrescriptionID`),
  ADD KEY `RecordID` (`RecordID`),
  ADD KEY `MedicineID` (`MedicineID`);

--
-- Chỉ mục cho bảng `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`SpecializationID`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Chỉ mục cho bảng `systemsettings`
--
ALTER TABLE `systemsettings`
  ADD PRIMARY KEY (`SettingKey`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `PhoneNumber` (`PhoneNumber`),
  ADD KEY `idx_email` (`Email`),
  ADD KEY `idx_role` (`Role`);

--
-- Chỉ mục cho bảng `vitalsigns`
--
ALTER TABLE `vitalsigns`
  ADD PRIMARY KEY (`VitalSignID`),
  ADD UNIQUE KEY `AppointmentID` (`AppointmentID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `RecordedByUserID` (`RecordedByUserID`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `doctoravailability`
--
ALTER TABLE `doctoravailability`
  MODIFY `AvailabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `doctornurseassignments`
--
ALTER TABLE `doctornurseassignments`
  MODIFY `AssignmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `doctors`
--
ALTER TABLE `doctors`
  MODIFY `DoctorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `medicalrecords`
--
ALTER TABLE `medicalrecords`
  MODIFY `RecordID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `medicines`
--
ALTER TABLE `medicines`
  MODIFY `MedicineID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `nurses`
--
ALTER TABLE `nurses`
  MODIFY `NurseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `patientfeedbacks`
--
ALTER TABLE `patientfeedbacks`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `specializations`
--
ALTER TABLE `specializations`
  MODIFY `SpecializationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `vitalsigns`
--
ALTER TABLE `vitalsigns`
  MODIFY `VitalSignID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`AvailabilityID`) REFERENCES `doctoravailability` (`AvailabilityID`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`NurseID`) REFERENCES `nurses` (`NurseID`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `doctoravailability`
--
ALTER TABLE `doctoravailability`
  ADD CONSTRAINT `doctoravailability_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `doctornurseassignments`
--
ALTER TABLE `doctornurseassignments`
  ADD CONSTRAINT `doctornurseassignments_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctornurseassignments_ibfk_2` FOREIGN KEY (`NurseID`) REFERENCES `nurses` (`NurseID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`SpecializationID`) REFERENCES `specializations` (`SpecializationID`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD CONSTRAINT `medicalrecords_ibfk_1` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE SET NULL,
  ADD CONSTRAINT `medicalrecords_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicalrecords_ibfk_3` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nurses`
--
ALTER TABLE `nurses`
  ADD CONSTRAINT `nurses_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `patientfeedbacks`
--
ALTER TABLE `patientfeedbacks`
  ADD CONSTRAINT `patientfeedbacks_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `patientfeedbacks_ibfk_2` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE SET NULL,
  ADD CONSTRAINT `patientfeedbacks_ibfk_3` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`RecordID`) REFERENCES `medicalrecords` (`RecordID`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`MedicineID`) REFERENCES `medicines` (`MedicineID`);

--
-- Các ràng buộc cho bảng `vitalsigns`
--
ALTER TABLE `vitalsigns`
  ADD CONSTRAINT `vitalsigns_ibfk_1` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `vitalsigns_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `vitalsigns_ibfk_3` FOREIGN KEY (`RecordedByUserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
CREATE TABLE `password_resets` (
  `ResetID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Token` varchar(255) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ResetID`),
  KEY `idx_token` (`Token`), -- Index for faster token lookup
  KEY `idx_email` (`Email`), -- Index for email lookup if needed
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `NotificationID` INT AUTO_INCREMENT PRIMARY KEY,
  `UserID` INT NOT NULL, -- UserID của người nhận (DoctorID sẽ được lấy từ UserID này)
  `RelatedEntityID` INT NULL, -- ID của đối tượng liên quan (ví dụ: AppointmentID)
  `EntityType` VARCHAR(50) NULL, -- Loại của RelatedEntityID (ví dụ: 'appointment')
  `Type` VARCHAR(100) NOT NULL, -- Loại thông báo (e.g., 'new_appointment_doctor', 'appointment_cancelled_by_patient', 'appointment_updated_by_admin')
  `Message` TEXT NOT NULL, -- Nội dung chi tiết của thông báo
  `ShortMessage` VARCHAR(255) NULL, -- Nội dung tóm tắt để hiển thị trong danh sách
  `Link` VARCHAR(255) NULL, -- Link để nhấp vào xem chi tiết
  `IsRead` BOOLEAN NOT NULL DEFAULT FALSE,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
  -- Không cần RelatedUserID nữa nếu Link đã đủ thông tin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `medicalrecords`
ADD COLUMN `NursingNotes` TEXT NULL DEFAULT NULL AFTER `NextAppointmentDate`; 
-- Hoặc đặt ở vị trí khác tùy ý cậu
CREATE TABLE `leaverequests` (
  `LeaveRequestID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique ID for the leave request',
  `DoctorID` int(11) NOT NULL COMMENT 'Foreign Key: Links to doctors.DoctorID',
  `StartDate` date NOT NULL COMMENT 'Start date of the leave period',
  `EndDate` date NOT NULL COMMENT 'End date of the leave period',
  `Reason` text DEFAULT NULL COMMENT 'Reason for the leave request (optional)',
  `Status` enum('Pending','Approved','Rejected','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending' COMMENT 'Status of the leave request',
  `AdminNotes` text DEFAULT NULL COMMENT 'Notes from the admin who reviewed the request (optional)',
  `RequestedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the request was submitted',
  `ReviewedByUserID` int(11) DEFAULT NULL COMMENT 'Foreign Key: Links to users.UserID (Admin who reviewed)',
  `ReviewedAt` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when the request was reviewed',
  PRIMARY KEY (`LeaveRequestID`),
  KEY `idx_leaverequest_doctor_id` (`DoctorID`),
  KEY `idx_leaverequest_status` (`Status`),
  KEY `idx_leaverequest_reviewed_by` (`ReviewedByUserID`),
  CONSTRAINT `fk_leaverequest_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_leaverequest_reviewed_by_user` FOREIGN KEY (`ReviewedByUserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores leave requests submitted by doctors';

INSERT INTO `nurses` (`UserID`, `CreatedAt`, `UpdatedAt`)
SELECT
    u.`UserID`,
    u.`CreatedAt`,
    u.`UpdatedAt` 
FROM
    `users` u
WHERE
    u.`Role` = 'Nurse'
AND NOT EXISTS (
    SELECT 1
    FROM `nurses` n
    WHERE n.`UserID` = u.`UserID`
);

