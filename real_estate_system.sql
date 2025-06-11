-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 11, 2025 at 11:34 AM
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
-- Database: `real_estate_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assetable_id` bigint(20) UNSIGNED NOT NULL COMMENT 'معرف السجل المرتبط به الأصل (عقار أو وحدة)',
  `assetable_type` varchar(255) NOT NULL COMMENT 'نوع السجل (Property, Unit)',
  `name` varchar(255) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action_type` varchar(50) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, LOGIN',
  `loggable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loggable_type` varchar(255) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documentable_id` bigint(20) UNSIGNED NOT NULL,
  `documentable_type` varchar(255) NOT NULL COMMENT 'Lease, Unit, Property...',
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by_user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text NOT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `recorded_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approved_by_owner_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inspectable_id` bigint(20) UNSIGNED NOT NULL,
  `inspectable_type` varchar(255) NOT NULL COMMENT 'Property or Unit',
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `inspector_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Scheduled','InProgress','Completed') NOT NULL,
  `inspection_date` datetime NOT NULL,
  `results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'نتائج التفتيش المعبأة بصيغة JSON' CHECK (json_valid(`results`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_templates`
--

CREATE TABLE `inspection_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `form_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'هيكل النموذج بصيغة JSON' CHECK (json_valid(`form_structure`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lease_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_type` varchar(100) NOT NULL DEFAULT 'Rent',
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Unpaid','Paid','PartiallyPaid','Overdue') NOT NULL DEFAULT 'Unpaid',
  `vat_percentage` decimal(5,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `status` enum('New','Contacted','ViewingScheduled','Closed') NOT NULL DEFAULT 'New',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leases`
--

CREATE TABLE `leases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_user_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rent_amount` decimal(15,2) NOT NULL,
  `payment_frequency` enum('Monthly','Quarterly','Annually') NOT NULL,
  `security_deposit` decimal(15,2) DEFAULT NULL,
  `status` enum('Active','Expired','Terminated','Draft') NOT NULL DEFAULT 'Draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leases`
--

INSERT INTO `leases` (`id`, `unit_id`, `tenant_user_id`, `start_date`, `end_date`, `rent_amount`, `payment_frequency`, `security_deposit`, `status`) VALUES
(1, 1, 4, '2024-08-15', '2025-08-14', 25000.00, 'Quarterly', NULL, 'Active'),
(2, 3, 5, '2024-09-01', '2025-08-31', 120000.00, 'Annually', NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by_tenant_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `status` enum('New','Assigned','InProgress','Completed','Cancelled') NOT NULL DEFAULT 'New',
  `assigned_to_user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'فني داخلي',
  `assigned_to_vendor_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'شركة خارجية'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE `parking_spots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `spot_number` varchar(50) NOT NULL,
  `status` enum('Available','Assigned') NOT NULL DEFAULT 'Available',
  `assigned_unit_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `payment_date` datetime NOT NULL,
  `amount_paid` decimal(15,2) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `received_by_user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `name`, `property_type`, `address`, `city`, `country`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'برج النخيل', 'مبنى سكني', 'شارع الملك فهد، حي العليا', 'الرياض', 'المملكة العربية السعودية', NULL, NULL, '2025-06-11 08:49:16', '2025-06-11 08:49:16'),
(2, 'مجمع الياسمين السكني', 'مجمع فلل', 'شارع الأمير سلطان، حي النهضة', 'جدة', 'المملكة العربية السعودية', NULL, NULL, '2025-06-11 08:49:16', '2025-06-11 08:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `property_owners`
--

CREATE TABLE `property_owners` (
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `owner_user_id` bigint(20) UNSIGNED NOT NULL,
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_owners`
--

INSERT INTO `property_owners` (`property_id`, `owner_user_id`, `ownership_percentage`) VALUES
(1, 3, 100.00),
(2, 3, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'اسم الدور (SystemAdmin, Accountant, Owner, Tenant)',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'SystemAdmin', 'مدير النظام الأعلى صلاحية'),
(2, 'Accountant', 'المحاسب المسؤول عن المالية'),
(3, 'Owner', 'مالك عقار أو أكثر'),
(4, 'Tenant', 'مستأجر لوحدة عقارية');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `unit_number` varchar(50) NOT NULL,
  `unit_type` varchar(100) NOT NULL,
  `status` enum('Vacant','Occupied','UnderMaintenance') NOT NULL DEFAULT 'Vacant',
  `floor_number` int(11) DEFAULT NULL,
  `area_sqm` decimal(10,2) DEFAULT NULL,
  `bedrooms` int(10) UNSIGNED DEFAULT NULL,
  `bathrooms` int(10) UNSIGNED DEFAULT NULL,
  `market_rent` decimal(15,2) DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'تخزين وسائل الراحة ككائن JSON' CHECK (json_valid(`amenities`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `property_id`, `unit_number`, `unit_type`, `status`, `floor_number`, `area_sqm`, `bedrooms`, `bathrooms`, `market_rent`, `amenities`, `created_at`, `updated_at`) VALUES
(1, 1, 'A-101', 'شقة', 'Occupied', NULL, NULL, 2, NULL, 25000.00, NULL, '2025-06-11 08:49:16', '2025-06-11 08:49:16'),
(2, 1, 'A-102', 'شقة', 'Vacant', NULL, NULL, 3, NULL, 35000.00, NULL, '2025-06-11 08:49:16', '2025-06-11 08:49:16'),
(3, 2, 'فيلا 23', 'فيلا', 'Occupied', NULL, NULL, 5, NULL, 120000.00, NULL, '2025-06-11 08:49:16', '2025-06-11 08:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL COMMENT 'معرف الدور الوظيفي للمستخدم',
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone_number`, `password_hash`, `avatar_url`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'مدير النظام', 'admin@app.com', '0510000001', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:49:16', '2025-06-11 08:59:14'),
(2, 2, 'علي المحاسب', 'accountant@app.com', '0510000002', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:49:16', '2025-06-11 08:59:18'),
(3, 3, 'محمد عبدالله', 'owner@app.com', '0510000003', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:49:16', '2025-06-11 08:59:21'),
(4, 4, 'خالد يوسف', 'tenant1@app.com', '0510000004', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:49:16', '2025-06-11 08:59:23'),
(5, 4, 'سارة محمود', 'tenant2@app.com', '0510000005', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:49:16', '2025-06-11 08:59:25'),
(6, 4, 'Amr Sameh', 'itforless@outlook.com', '01091968252', '$2y$10$DtlrlgiIS9gHJqoBb/bazOA.A9hGzBk2W706YURBE7IHEnoxtiTsS', NULL, 1, NULL, '2025-06-11 08:58:27', '2025-06-11 08:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assetable` (`assetable_id`,`assetable_type`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documentable` (`documentable_id`,`documentable_type`),
  ADD KEY `uploaded_by_user_id` (`uploaded_by_user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `recorded_by_user_id` (`recorded_by_user_id`),
  ADD KEY `approved_by_owner_id` (`approved_by_owner_id`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `inspector_id` (`inspector_id`),
  ADD KEY `idx_inspectable` (`inspectable_id`,`inspectable_type`);

--
-- Indexes for table `inspection_templates`
--
ALTER TABLE `inspection_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lease_id` (`lease_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leases`
--
ALTER TABLE `leases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `tenant_user_id` (`tenant_user_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `requested_by_tenant_id` (`requested_by_tenant_id`),
  ADD KEY `assigned_to_user_id` (`assigned_to_user_id`),
  ADD KEY `assigned_to_vendor_id` (`assigned_to_vendor_id`);

--
-- Indexes for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_spot_in_property` (`property_id`,`spot_number`),
  ADD KEY `assigned_unit_id` (`assigned_unit_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `received_by_user_id` (`received_by_user_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD PRIMARY KEY (`property_id`,`owner_user_id`),
  ADD KEY `owner_user_id` (`owner_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_unit_in_property` (`property_id`,`unit_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_templates`
--
ALTER TABLE `inspection_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leases`
--
ALTER TABLE `leases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`approved_by_owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `inspections_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `inspection_templates` (`id`),
  ADD CONSTRAINT `inspections_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leases`
--
ALTER TABLE `leases`
  ADD CONSTRAINT `leases_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `leases_ibfk_2` FOREIGN KEY (`tenant_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`requested_by_tenant_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_3` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_4` FOREIGN KEY (`assigned_to_vendor_id`) REFERENCES `vendors` (`id`);

--
-- Constraints for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD CONSTRAINT `parking_spots_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parking_spots_ibfk_2` FOREIGN KEY (`assigned_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD CONSTRAINT `property_owners_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_owners_ibfk_2` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
