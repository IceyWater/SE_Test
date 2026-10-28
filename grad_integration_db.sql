-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 03:48 AM
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
-- Database: `grad_integration_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aa_payment_batches`
--

CREATE TABLE `aa_payment_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','sent','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aa_payment_verifications`
--

CREATE TABLE `aa_payment_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advisers`
--

CREATE TABLE `advisers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coordinator_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `status` enum('inactive','active') NOT NULL DEFAULT 'inactive',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adviser_coordinator`
--

CREATE TABLE `adviser_coordinator` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adviser_id` bigint(20) UNSIGNED NOT NULL,
  `coordinator_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adviser_settings`
--

CREATE TABLE `adviser_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adviser_id` bigint(20) UNSIGNED NOT NULL,
  `auto_accept_students` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adviser_student`
--

CREATE TABLE `adviser_student` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adviser_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'accepted',
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comprehensive_exams`
--

CREATE TABLE `comprehensive_exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `school_year` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `office_address` varchar(255) DEFAULT NULL,
  `mobile_no` varchar(255) DEFAULT NULL,
  `telephone_no` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `subjects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`subjects`)),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'private',
  `title` varchar(255) DEFAULT NULL,
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`participants`)),
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_programs`
--

CREATE TABLE `coordinator_programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coordinator_id` bigint(20) UNSIGNED NOT NULL,
  `program` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_program_assignments`
--

CREATE TABLE `coordinator_program_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coordinator_user_id` bigint(20) UNSIGNED NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_access_audits`
--

CREATE TABLE `data_access_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(64) NOT NULL,
  `target_system` varchar(64) NOT NULL DEFAULT 'legacy_portal',
  `purpose` varchar(128) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'success',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_requests`
--

CREATE TABLE `defense_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `thesis_title` varchar(255) NOT NULL,
  `date_of_defense` date DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `scheduled_end_time` time DEFAULT NULL,
  `defense_duration_minutes` int(11) DEFAULT NULL,
  `formatted_time_range` varchar(255) DEFAULT NULL,
  `defense_mode` varchar(255) DEFAULT NULL,
  `defense_venue` varchar(255) DEFAULT NULL,
  `scheduling_notes` text DEFAULT NULL,
  `panels_assigned_at` datetime DEFAULT NULL,
  `panels_assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `schedule_set_at` datetime DEFAULT NULL,
  `schedule_set_by` bigint(20) UNSIGNED DEFAULT NULL,
  `adviser_notified_at` datetime DEFAULT NULL,
  `student_notified_at` datetime DEFAULT NULL,
  `panels_notified_at` datetime DEFAULT NULL,
  `scheduling_status` enum('pending-panels','panels-assigned','scheduled','confirmed','in-progress','completed','rescheduled') NOT NULL DEFAULT 'pending-panels',
  `mode_defense` varchar(255) DEFAULT NULL,
  `defense_type` varchar(255) NOT NULL,
  `advisers_endorsement` varchar(255) DEFAULT NULL,
  `manuscript_proposal` varchar(255) DEFAULT NULL,
  `similarity_index` varchar(255) DEFAULT NULL,
  `rec_endorsement` varchar(255) DEFAULT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `avisee_adviser_attachment` varchar(255) DEFAULT NULL,
  `ai_detection_certificate` varchar(255) DEFAULT NULL,
  `endorsement_form` varchar(255) DEFAULT NULL,
  `defense_adviser` varchar(255) NOT NULL,
  `adviser_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coordinator_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workflow_state` varchar(255) NOT NULL DEFAULT 'submitted',
  `adviser_comments` text DEFAULT NULL,
  `adviser_reviewed_at` timestamp NULL DEFAULT NULL,
  `adviser_reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `coordinator_comments` text DEFAULT NULL,
  `coordinator_reviewed_at` timestamp NULL DEFAULT NULL,
  `coordinator_assigned_at` timestamp NULL DEFAULT NULL COMMENT 'When the coordinator was automatically assigned',
  `coordinator_assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `coordinator_manually_assigned` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'True if super admin manually reassigned, false if auto-assigned',
  `coordinator_assignment_notes` text DEFAULT NULL COMMENT 'Notes about coordinator assignment or reassignment',
  `coordinator_reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `workflow_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`workflow_history`)),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `defense_chairperson` varchar(255) DEFAULT NULL,
  `defense_panelist1` varchar(255) DEFAULT NULL,
  `defense_panelist2` varchar(255) DEFAULT NULL,
  `defense_panelist3` varchar(255) DEFAULT NULL,
  `defense_panelist4` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `priority` varchar(255) NOT NULL DEFAULT 'Medium',
  `last_status_updated_at` timestamp NULL DEFAULT NULL,
  `last_status_updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `adviser_status` varchar(255) NOT NULL DEFAULT 'Pending',
  `coordinator_status` varchar(255) NOT NULL DEFAULT 'Pending',
  `correction_notes` text DEFAULT NULL,
  `correction_stage` varchar(20) DEFAULT NULL,
  `correction_requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `correction_requested_at` timestamp NULL DEFAULT NULL,
  `correction_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `panel_affiliations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`panel_affiliations`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_request_cancellations`
--

CREATE TABLE `defense_request_cancellations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `cancelled_by` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_request_status_logs`
--

CREATE TABLE `defense_request_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_schedule_mutex`
--

CREATE TABLE `defense_schedule_mutex` (
  `id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `defense_schedule_mutex`
--

INSERT INTO `defense_schedule_mutex` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `defense_service_records`
--

CREATE TABLE `defense_service_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `snapshot_hash` varchar(64) NOT NULL,
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`participants`)),
  `completed_on` date NOT NULL,
  `confirmed_by` bigint(20) UNSIGNED NOT NULL,
  `confirmed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`history`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `defense_type` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `page_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields`)),
  `fields_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields_meta`)),
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_templates`
--

INSERT INTO `document_templates` (`id`, `name`, `code`, `defense_type`, `file_path`, `page_count`, `version`, `fields`, `fields_meta`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Endorsement Form (Prefinal)', 'endorsement-form-prefinal-6ae00ed062267', NULL, 'templates/evRF19Y5e8PeJzSgV91BOvnZk4EuzheW1sqixEt7.pdf', 1, 1, NULL, NULL, 7, '2026-10-26 18:39:12', '2026-10-26 18:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_at` timestamp NULL DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL DEFAULT 'general',
  `color` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `visibility` varchar(255) NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `start_at`, `end_at`, `all_day`, `type`, `color`, `created_by`, `visibility`, `created_at`, `updated_at`) VALUES
(1, 'defense', 'Masters Defense', '2026-10-30 00:00:00', '2026-10-30 09:00:00', 0, 'general', '#ef4444', 7, 'public', '2026-10-26 18:36:06', '2026-10-26 18:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `exam_application`
--

CREATE TABLE `exam_application` (
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `program` varchar(255) NOT NULL,
  `permit_status` varchar(255) NOT NULL DEFAULT 'pending',
  `permit_DATE` datetime DEFAULT NULL,
  `permit_reason` text DEFAULT NULL,
  `final_approval_status` varchar(255) NOT NULL DEFAULT 'pending',
  `final_approval_DATE` datetime DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `telephone_number` varchar(255) DEFAULT NULL,
  `office_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_application_subject`
--

CREATE TABLE `exam_application_subject` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `subject_code` varchar(255) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_document_submissions`
--

CREATE TABLE `finance_document_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('payment_receipt','panel_breakdown') NOT NULL,
  `status` enum('locked','ready','submitted','verified','rejected') NOT NULL DEFAULT 'ready',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `finance_notes` text DEFAULT NULL,
  `ready_on` date DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_honorarium_submissions`
--

CREATE TABLE `finance_honorarium_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `submitted_by` bigint(20) UNSIGNED NOT NULL,
  `panel_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`panel_names`)),
  `status` enum('submitted','verified','rejected') NOT NULL DEFAULT 'submitted',
  `finance_notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generated_documents`
--

CREATE TABLE `generated_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `document_template_id` bigint(20) UNSIGNED NOT NULL,
  `template_version_used` int(10) UNSIGNED NOT NULL,
  `output_path` varchar(255) NOT NULL,
  `sha256` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` varchar(255) NOT NULL DEFAULT 'generated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honorarium_activities`
--

CREATE TABLE `honorarium_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_document_submission_id` bigint(20) UNSIGNED DEFAULT NULL,
  `activity_type` varchar(60) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honorarium_disbursements`
--

CREATE TABLE `honorarium_disbursements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_key` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_role` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'prepared',
  `requires_review` tinyint(1) NOT NULL DEFAULT 0,
  `payment_reference` varchar(150) DEFAULT NULL,
  `disbursed_on` date DEFAULT NULL,
  `claimed_on` date DEFAULT NULL,
  `history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`history`)),
  `prepared_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `recipient_affiliation` varchar(20) DEFAULT NULL,
  `service_evidence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_evidence`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honorarium_payments`
--

CREATE TABLE `honorarium_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `defense_request_id` bigint(20) UNSIGNED NOT NULL,
  `panelist_id` bigint(20) UNSIGNED NOT NULL,
  `panelist_type` varchar(255) NOT NULL DEFAULT 'Panelist',
  `role` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_academic_records`
--

CREATE TABLE `legacy_academic_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_clearance_data`
--

CREATE TABLE `legacy_clearance_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `legacy_scraped_data_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` varchar(255) NOT NULL,
  `semester_label` varchar(255) NOT NULL,
  `clearance_id` int(11) NOT NULL,
  `clearance_area_id` int(11) NOT NULL,
  `clearance_area_label` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`requirements`)),
  `total_requirements` int(11) NOT NULL DEFAULT 0,
  `cleared_requirements` int(11) NOT NULL DEFAULT 0,
  `completion_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `all_cleared` tinyint(1) NOT NULL DEFAULT 0,
  `scraped_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_clearance_statuses`
--

CREATE TABLE `legacy_clearance_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_credentials`
--

CREATE TABLE `legacy_credentials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `legacy_username` varchar(255) NOT NULL,
  `encrypted_password` text NOT NULL,
  `last_validated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'valid',
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_grades_data`
--

CREATE TABLE `legacy_grades_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `legacy_scraped_data_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` varchar(255) NOT NULL,
  `semester_label` varchar(255) NOT NULL,
  `course_code` varchar(255) NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `rating_show` varchar(255) DEFAULT NULL,
  `rating_numeric` decimal(5,2) DEFAULT NULL,
  `is_incomplete` tinyint(1) NOT NULL DEFAULT 0,
  `section` varchar(255) DEFAULT NULL,
  `unit_type` varchar(255) DEFAULT NULL,
  `prelim` decimal(5,2) DEFAULT NULL,
  `midterm` decimal(5,2) DEFAULT NULL,
  `finals` decimal(5,2) DEFAULT NULL,
  `raw_grade_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_grade_data`)),
  `scraped_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_record_caches`
--

CREATE TABLE `legacy_record_caches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(32) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `fetched_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_scraped_data`
--

CREATE TABLE `legacy_scraped_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` varchar(255) DEFAULT NULL,
  `current_semester_id` varchar(255) DEFAULT NULL,
  `semesters_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`semesters_data`)),
  `student_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`student_info`)),
  `init_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`init_params`)),
  `scraping_success` tinyint(1) NOT NULL DEFAULT 0,
  `scraping_errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scraping_errors`)),
  `scraped_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_student_info`
--

CREATE TABLE `legacy_student_info` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_participants`
--

CREATE TABLE `message_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_read_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_07_11_183740_add_role_to_users_table', 1),
(5, '2025_07_12_122238_add_program_to_users_table', 1),
(6, '2025_07_12_124636_add_school_id_to_users_table', 1),
(7, '2025_07_12_131038_drop_name_from_users_table', 1),
(8, '2025_07_12_131328_split_name_columns_in_users_table', 1),
(9, '2025_07_14_033954_rename_middle_initial_to_middle_name_in_users_table', 1),
(10, '2025_07_20_151828_create_defense_requests_table', 1),
(11, '2025_07_26_042929_add_tracking_fields_to_defense_requests_table', 1),
(12, '2025_07_26_042951_create_defense_request_status_logs_table', 1),
(13, '2025_07_29_120000_create_conversations_table', 1),
(14, '2025_07_29_120001_create_messages_table', 1),
(15, '2025_07_29_120002_create_message_participants_table', 1),
(16, '2025_08_06_084533_create_notifications_table', 1),
(17, '2025_08_08_142433_create_panelists_table', 1),
(18, '2025_08_19_164358_create_personal_access_tokens_table', 1),
(19, '2025_08_21_074200_create_defense_requirements_table', 1),
(20, '2025_08_24_072901_add_submitted_by_to_defense_requests_table', 1),
(21, '2025_08_28_120003_create_program_records_table', 1),
(22, '2025_08_28_120004_create_student_records_table', 1),
(23, '2025_09_01_164544_create_payment_records_table', 1),
(24, '2025_09_03_000001_create_legacy_credentials_table', 1),
(25, '2025_09_03_000002_create_legacy_record_caches_table', 1),
(26, '2025_09_03_000003_create_data_access_audits_table', 1),
(27, '2025_09_03_100000_add_student_number_to_users_table', 1),
(28, '2025_09_03_110000_ensure_defense_request_tracking_columns', 1),
(29, '2025_09_04_000000_add_google_verified_at_to_users_table', 1),
(30, '2025_09_04_000001_add_extra_role_title_to_users_table', 1),
(31, '2025_09_04_120000_create_roles_and_role_user_tables', 1),
(32, '2025_09_05_000000_add_workflow_fields_to_defense_requests_table', 1),
(33, '2025_09_05_090000_add_employee_profile_fields_to_users_table', 1),
(34, '2025_09_06_000000_create_comprehensive_exams_table', 1),
(35, '2025_09_06_075615_make_defense_request_fields_nullable', 1),
(36, '2025_09_06_082704_migrate_defense_requirements_to_defense_requests', 1),
(37, '2025_09_06_082950_consolidate_defense_tables', 1),
(38, '2025_09_06_083524_drop_defense_requirements_table', 1),
(39, '2025_09_06_090034_enhance_defense_requests_workflow_system', 1),
(40, '2025_09_06_150000_add_defense_scheduling_fields', 1),
(41, '2025_09_07_000002_add_role_id_to_users_table', 1),
(42, '2025_09_07_000003_create_coordinator_programs_table', 1),
(43, '2025_09_07_094855_add_defense_duration_fields_to_defense_requests_table', 1),
(44, '2025_09_07_144219_create_exam_application_table', 1),
(45, '2025_09_07_144318_create_exam_application_subject_table', 1),
(46, '2025_09_19_012438_create_defense_request_cancellations_table', 1),
(47, '2025_09_22_062041_create_events_table', 1),
(48, '2025_09_22_102822_add_schedule_fields_to_events_table', 1),
(49, '2025_09_22_120000_add_schedule_fields_to_events_table', 1),
(50, '2025_09_23_011252_init_document_e_signatures', 1),
(51, '2025_09_27_072543_create_settings_table', 1),
(52, '2025_09_28_034057_create_adviser_student_table', 1),
(53, '2025_09_28_035117_add_adviser_code_to_users_table', 1),
(54, '2025_09_28_075242_create_adviser_settings_table', 1),
(55, '2025_09_29_112639_increase_program_column_length_in_users_table', 1),
(56, '2025_09_30_023805_create_legacy_scraped_data_table', 1),
(57, '2025_09_30_023815_create_legacy_clearance_data_table', 1),
(58, '2025_09_30_023823_create_legacy_grades_data_table', 1),
(59, '2025_10_01_160057_create_panelist_honorarium_specs_table', 1),
(60, '2025_10_02_063522_add_avisee_adviser_attachment_to_defense_requests_table', 1),
(61, '2025_10_02_133730_create_adviser_coordinator_table', 1),
(62, '2025_10_02_135358_add_coordinator_code_to_users_table', 1),
(63, '2025_10_04_140415_add_ai_detection_and_endorsement_form_to_defense_requests_table', 1),
(64, '2025_10_04_140436_create_legacy_student_info_table', 1),
(65, '2025_10_04_140527_create_legacy_academic_records_table', 1),
(66, '2025_10_04_140709_create_legacy_clearance_statuses_table', 1),
(67, '2025_10_04_154922_add_fields_meta_to_document_templates_table', 1),
(68, '2025_10_04_165829_add_legacy_account_id_to_users_table', 1),
(69, '2025_10_05_084616_add_adviser_and_coordinator_status_to_defense_requests', 1),
(70, '2025_10_05_174922_create_honorarium_payments_table', 1),
(71, '2025_10_06_043833_add_category_to_program_records_table', 1),
(72, '2025_10_06_074309_add_coordinator_assignment_to_defense_requests_table', 1),
(73, '2025_10_06_082119_create_coordinator_program_assignments_table', 1),
(74, '2025_10_10_172004_add_amount_to_defense_requests_table', 1),
(75, '2025_10_11_074058_create_payment_rates_table', 1),
(76, '2025_10_12_191853_create_aa_payment_batches_table', 1),
(77, '2025_10_12_191945_create_aa_payment_verifications_table', 1),
(78, '2025_10_16_172038_create_advisers_table', 1),
(79, '2025_10_17_193218_add_status_requested_by_to_adviser_student', 1),
(80, '2025_10_20_000000_add_unique_index_on_payment_rates', 1),
(81, '2026_10_22_000001_rename_faculty_role_to_adviser', 1),
(82, 'payment_submissions', 1),
(83, '2026_10_22_000002_add_google_id_to_users_table', 2),
(84, '2026_10_23_000001_add_manual_role_assignment_to_users', 3),
(85, '2026_10_23_000002_add_demo_account_flag_to_users', 4),
(86, '2026_10_23_000003_add_notification_event_tracking', 5),
(87, '2026_10_23_000004_add_defense_corrections', 6),
(88, '2026_10_10_000001_create_finance_honorarium_submissions_table', 7),
(89, '2026_10_17_000001_create_finance_document_submissions_table', 7),
(90, '2026_10_17_000002_create_honorarium_activities_table', 7),
(91, '2026_10_24_000001_add_finance_role', 7),
(92, '2026_10_24_000002_add_exam_subject_schedule_fields', 8),
(93, '2026_10_24_000003_create_honorarium_disbursements', 9),
(94, '2026_10_24_000004_create_defense_schedule_mutex', 9),
(95, '2026_10_26_000001_create_defense_service_records', 10);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_key` varchar(64) DEFAULT NULL,
  `defense_request_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panelists`
--

CREATE TABLE `panelists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Chairperson','Panel Member') NOT NULL DEFAULT 'Panel Member',
  `status` enum('Assigned','Not Assigned') NOT NULL DEFAULT 'Not Assigned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panelist_honorarium_specs`
--

CREATE TABLE `panelist_honorarium_specs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('Chairperson','Panel Member') NOT NULL,
  `defense_type` enum('Proposal','Prefinal','Final') NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_rates`
--

CREATE TABLE `payment_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_level` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `defense_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_rates`
--

INSERT INTO `payment_rates` (`id`, `program_level`, `type`, `defense_type`, `amount`, `created_at`, `updated_at`) VALUES
(1, 'Masteral', 'Adviser', 'Proposal', 4000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(2, 'Masteral', 'Adviser', 'Pre-final', 3700.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(3, 'Masteral', 'Adviser', 'Final', 2000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(4, 'Masteral', 'Panel Chair', 'Proposal', 2000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(5, 'Masteral', 'Panel Chair', 'Pre-final', 2500.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(6, 'Masteral', 'Panel Chair', 'Final', 1000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(7, 'Masteral', 'Panel Member 1', 'Proposal', 1200.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(8, 'Masteral', 'Panel Member 1', 'Pre-final', 1500.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(9, 'Masteral', 'Panel Member 1', 'Final', 1000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(10, 'Masteral', 'Panel Member 2', 'Proposal', 1200.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(11, 'Masteral', 'Panel Member 2', 'Pre-final', 1500.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(12, 'Masteral', 'Panel Member 2', 'Final', 1000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(13, 'Masteral', 'Panel Member 3', 'Proposal', 1200.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(14, 'Masteral', 'Panel Member 3', 'Pre-final', 1500.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(15, 'Masteral', 'Panel Member 3', 'Final', 1000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(16, 'Masteral', 'REC Fee', 'Proposal', 2200.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(17, 'Masteral', 'REC Fee', 'Pre-final', 800.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(18, 'Masteral', 'REC Fee', 'Final', 0.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(19, 'Masteral', 'School Share', 'Proposal', 450.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(20, 'Masteral', 'School Share', 'Pre-final', 1280.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43'),
(21, 'Masteral', 'School Share', 'Final', 1000.00, '2026-10-26 17:57:43', '2026-10-26 17:57:43');

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_record_id` bigint(20) UNSIGNED NOT NULL,
  `school_year` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `defense_status` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_submissions`
--

CREATE TABLE `payment_submissions` (
  `payment_id` int(10) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `or_number` varchar(50) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_type` enum('exam') NOT NULL DEFAULT 'exam',
  `receipt_image` varchar(255) DEFAULT NULL,
  `checked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_records`
--

CREATE TABLE `program_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `program` varchar(50) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `time_last_opened` varchar(20) NOT NULL,
  `date_edited` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `recently_updated` timestamp NULL DEFAULT '2026-10-22 02:28:35'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Student', NULL, NULL),
(2, 'Coordinator', NULL, NULL),
(3, 'Dean', NULL, NULL),
(4, 'Administrative Assistant', NULL, NULL),
(5, 'Registrar', NULL, NULL),
(6, 'Adviser', NULL, NULL),
(7, 'Finance', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`role_id`, `user_id`) VALUES
(1, 3),
(2, 5),
(3, 7),
(4, 6),
(6, 1),
(6, 4),
(7, 8);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('hW8X6Cr3mRhDc7f5hIlhJDe2xq52qwPZbhnkM43t', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYlg5ZjVNclh5VEJPblhDOWY1Y0F0amVqQlpLT2lyOFBhbFVQSXIxQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9ub3RpZmljYXRpb25zL2ZlZWQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O30=', 1793069310),
('Q5C3JMYoCqyuyS5zCMaJYnzMuqL1BG5cQM1qV3kL', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRkFPMkFwZnZjUFd3eVZ1dXJxVU54YVVQOWF2TUpkbEpQR01RU1hqRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MDoiaHR0cDovLzEyNy4wLjAuMTo4MDAxL25vdGlmaWNhdGlvbnMvZmVlZCI7fX0=', 1793069153),
('QYEsxWa89HERxmtBAc9hNVKnkNcfYKxiQrevJq2I', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNVc2cGw1SzBIRmNmb2dUUUJjNERxaHVjVkE2MzZWVXhWR1Z3MUcwQSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvbm90aWZpY2F0aW9ucy9mZWVkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Nzt9', 1793068923),
('XptcILX65vvNtdO4d3zYan71SujtQI5gPnaReOME', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNHdXSDNqdktQb2VyM0dVd0tOR2lpWTFZZVMybm96U29yV3I5V3BVbyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAxL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1793063302);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_records`
--

CREATE TABLE `student_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `course_section` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `academic_status` varchar(255) DEFAULT NULL,
  `or_number` varchar(255) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_number` varchar(32) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `google_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'Student',
  `extra_role_title` varchar(255) DEFAULT NULL,
  `program` text DEFAULT NULL,
  `degree_code` varchar(255) DEFAULT NULL COMMENT 'Degree code from legacy system',
  `degree_program_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Legacy degree program ID',
  `year_level` varchar(255) DEFAULT NULL COMMENT 'Year level from legacy system',
  `balance` varchar(255) DEFAULT NULL COMMENT 'Current balance from clearance API (e.g., "Php 51968.54")',
  `clearance_statuscode` int(10) UNSIGNED DEFAULT NULL COMMENT 'Clearance statuscode from API (3300=has balance, etc.)',
  `school_id` varchar(255) DEFAULT NULL,
  `legacy_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_number_legacy` varchar(255) DEFAULT NULL COMMENT 'Legacy student number from clearance API',
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_department_code` varchar(32) DEFAULT NULL,
  `employee_photo_url` varchar(255) DEFAULT NULL,
  `employee_profile_fetched_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `adviser_code` varchar(12) DEFAULT NULL,
  `coordinator_code` varchar(255) DEFAULT NULL,
  `legacy_data_synced_at` timestamp NULL DEFAULT NULL COMMENT 'Last time legacy account data was synced',
  `google_id` varchar(255) DEFAULT NULL,
  `role_manually_assigned` tinyint(1) NOT NULL DEFAULT 0,
  `is_demo_account` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `student_number`, `first_name`, `middle_name`, `last_name`, `email`, `email_verified_at`, `google_verified_at`, `password`, `role`, `extra_role_title`, `program`, `degree_code`, `degree_program_id`, `year_level`, `balance`, `clearance_statuscode`, `school_id`, `legacy_account_id`, `student_number_legacy`, `employee_id`, `employee_department_code`, `employee_photo_url`, `employee_profile_fetched_at`, `remember_token`, `created_at`, `updated_at`, `adviser_code`, `coordinator_code`, `legacy_data_synced_at`, `google_id`, `role_manually_assigned`, `is_demo_account`) VALUES
(1, NULL, NULL, 'New', NULL, 'User', 'lfuentes_240000000631@uic.edu.ph', '2026-10-22 08:14:26', '2026-10-22 08:14:26', '$2y$12$ZRkl6DRPJugUxpC7T1oYGewOoE5bozOxjyprmdJ0Bgkhp7E/8nJ/q', 'Adviser', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 02:31:50', '2026-10-22 08:14:26', '792D7867', NULL, NULL, '109194871523926642783', 0, 0),
(2, NULL, NULL, 'Super', NULL, 'Admin', 'superadmin@uic.edu.ph', NULL, NULL, '$2y$12$1rWcslvM6WN32h4shAHin.96GhyToeeqY8EuXxuLjjQ4Wg.r8zM2C', 'Super Admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ADMIN001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 06:48:35', '2026-10-22 06:48:35', NULL, NULL, NULL, NULL, 0, 0),
(3, 1, NULL, 'Demo', NULL, 'Student', 'student@uic.edu.ph', '2026-10-22 08:27:19', NULL, '$2y$12$WaByuLhOHLokidGioh902uZTZ26pMerGmKBJOb11EksVuHJGYC8rK', 'Student', NULL, 'Master of Arts in Education', NULL, NULL, NULL, NULL, NULL, 'DEMO-STUDENT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 08:27:19', '2026-10-22 08:27:19', NULL, NULL, NULL, NULL, 1, 1),
(4, 6, NULL, 'Demo', NULL, 'Adviser', 'adviser@uic.edu.ph', '2026-10-22 08:27:19', NULL, '$2y$12$xP4kiLSNF54h4I75u7tt1O7HW90G05/i/fe4Pyvol00c64T6XRktS', 'Adviser', NULL, 'Master of Arts in Education', NULL, NULL, NULL, NULL, NULL, 'DEMO-ADVISER', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 08:27:19', '2026-10-22 08:28:10', 'D0FA5C87', NULL, NULL, NULL, 1, 1),
(5, 2, NULL, 'Demo', NULL, 'Coordinator', 'acadcoor@uic.edu.ph', '2026-10-22 08:27:19', NULL, '$2y$12$CyHf/0hUxWrFn/yjRNIY/uTl9dhlRZi3PXincQcm3byM6H82xnjOC', 'Coordinator', NULL, 'Master of Arts in Education', NULL, NULL, NULL, NULL, NULL, 'DEMO-COORDINATOR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 08:27:19', '2026-10-22 08:27:19', NULL, '4F03458D', NULL, NULL, 1, 1),
(6, 4, NULL, 'Demo', NULL, 'Assistant', 'aa@uic.edu.ph', '2026-10-22 08:27:19', NULL, '$2y$12$oIG7RO6TqbI2oefgUvwkIeoY4zLnfPOx/Q7fddbNmTzAyyLGwQKc.', 'Administrative Assistant', NULL, 'Master of Arts in Education', NULL, NULL, NULL, NULL, NULL, 'DEMO-AA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 08:27:19', '2026-10-22 08:27:19', NULL, NULL, NULL, NULL, 1, 1),
(7, 3, NULL, 'Demo', NULL, 'Dean', 'dean@uic.edu.ph', '2026-10-22 08:27:20', NULL, '$2y$12$xwmnowHUD7bNhOWbi/47VuhfS2UrY8dPY2Wbyk1BwXtCuyEioj.ua', 'Dean', NULL, 'Master of Arts in Education', NULL, NULL, NULL, NULL, NULL, 'DEMO-DEAN', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 08:27:20', '2026-10-22 08:27:20', NULL, NULL, NULL, NULL, 1, 1),
(8, 7, NULL, 'Demo', NULL, 'Finance', 'finance@uic.edu.ph', '2026-10-24 04:38:02', NULL, '$2y$12$wI3/YulAn6prqzBTJnLPReIbK.xFwjn3HSFhquzKwZq/qz70QgEUm', 'Finance', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DEMO-FINANCE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-24 04:38:02', '2026-10-24 04:38:02', NULL, NULL, NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_signatures`
--

CREATE TABLE `user_signatures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'Primary',
  `image_path` varchar(255) NOT NULL,
  `natural_width` int(10) UNSIGNED DEFAULT NULL,
  `natural_height` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_signatures`
--

INSERT INTO `user_signatures` (`id`, `user_id`, `label`, `image_path`, `natural_width`, `natural_height`, `active`, `created_at`, `updated_at`) VALUES
(1, 7, 'Drawn Signature', 'signatures/7/drawn_6ae00d987ef2c.png', 690, 300, 1, '2026-10-26 18:34:00', '2026-10-26 18:34:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aa_payment_batches`
--
ALTER TABLE `aa_payment_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aa_payment_verifications`
--
ALTER TABLE `aa_payment_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aa_payment_verifications_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `aa_payment_verifications_assigned_to_foreign` (`assigned_to`),
  ADD KEY `aa_payment_verifications_batch_id_foreign` (`batch_id`);

--
-- Indexes for table `advisers`
--
ALTER TABLE `advisers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advisers_coordinator_id_email_unique` (`coordinator_id`,`email`),
  ADD KEY `advisers_user_id_foreign` (`user_id`);

--
-- Indexes for table `adviser_coordinator`
--
ALTER TABLE `adviser_coordinator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adviser_coordinator_adviser_id_coordinator_id_unique` (`adviser_id`,`coordinator_id`),
  ADD KEY `adviser_coordinator_coordinator_id_foreign` (`coordinator_id`);

--
-- Indexes for table `adviser_settings`
--
ALTER TABLE `adviser_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adviser_settings_adviser_id_unique` (`adviser_id`);

--
-- Indexes for table `adviser_student`
--
ALTER TABLE `adviser_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adviser_student_adviser_id_foreign` (`adviser_id`),
  ADD KEY `adviser_student_student_id_foreign` (`student_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `comprehensive_exams`
--
ALTER TABLE `comprehensive_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comprehensive_exams_user_id_foreign` (`user_id`),
  ADD KEY `comprehensive_exams_school_id_index` (`school_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversations_last_message_at_index` (`last_message_at`);

--
-- Indexes for table `coordinator_programs`
--
ALTER TABLE `coordinator_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coordinator_programs_coordinator_id_program_unique` (`coordinator_id`,`program`);

--
-- Indexes for table `coordinator_program_assignments`
--
ALTER TABLE `coordinator_program_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coord_prog_unique` (`coordinator_user_id`,`program_name`),
  ADD KEY `coordinator_program_assignments_assigned_by_foreign` (`assigned_by`),
  ADD KEY `coord_prog_name_idx` (`program_name`),
  ADD KEY `coord_active_idx` (`coordinator_user_id`,`is_active`);

--
-- Indexes for table `data_access_audits`
--
ALTER TABLE `data_access_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_access_audits_user_id_action_index` (`user_id`,`action`);

--
-- Indexes for table `defense_requests`
--
ALTER TABLE `defense_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_requests_last_status_updated_by_foreign` (`last_status_updated_by`),
  ADD KEY `defense_requests_updated_by_foreign` (`updated_by`),
  ADD KEY `defense_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `defense_requests_workflow_state_index` (`workflow_state`),
  ADD KEY `defense_requests_assigned_to_user_id_index` (`assigned_to_user_id`),
  ADD KEY `defense_requests_adviser_reviewed_by_foreign` (`adviser_reviewed_by`),
  ADD KEY `defense_requests_coordinator_reviewed_by_foreign` (`coordinator_reviewed_by`),
  ADD KEY `defense_requests_panels_assigned_by_foreign` (`panels_assigned_by`),
  ADD KEY `defense_requests_schedule_set_by_foreign` (`schedule_set_by`),
  ADD KEY `defense_requests_coordinator_assigned_by_foreign` (`coordinator_assigned_by`),
  ADD KEY `idx_defense_requests_program_state` (`program`,`workflow_state`),
  ADD KEY `idx_defense_requests_coord_state` (`coordinator_user_id`,`workflow_state`);

--
-- Indexes for table `defense_request_cancellations`
--
ALTER TABLE `defense_request_cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_request_cancellations_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `defense_request_cancellations_cancelled_by_foreign` (`cancelled_by`);

--
-- Indexes for table `defense_request_status_logs`
--
ALTER TABLE `defense_request_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_request_status_logs_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `defense_request_status_logs_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `defense_schedule_mutex`
--
ALTER TABLE `defense_schedule_mutex`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_service_records`
--
ALTER TABLE `defense_service_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `defense_service_records_defense_request_id_unique` (`defense_request_id`),
  ADD KEY `defense_service_records_confirmed_by_foreign` (`confirmed_by`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_templates_code_unique` (`code`),
  ADD KEY `document_templates_created_by_foreign` (`created_by`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_created_by_foreign` (`created_by`);

--
-- Indexes for table `exam_application`
--
ALTER TABLE `exam_application`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `exam_application_student_id_index` (`student_id`),
  ADD KEY `exam_application_program_index` (`program`),
  ADD KEY `exam_application_created_at_index` (`created_at`),
  ADD KEY `exam_application_final_approval_status_index` (`final_approval_status`),
  ADD KEY `exam_application_permit_status_index` (`permit_status`);

--
-- Indexes for table `exam_application_subject`
--
ALTER TABLE `exam_application_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_application_subject_application_id_status_index` (`application_id`,`status`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `finance_document_submissions`
--
ALTER TABLE `finance_document_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_doc_request_type_unique` (`defense_request_id`,`document_type`),
  ADD KEY `finance_document_submissions_submitted_by_foreign` (`submitted_by`),
  ADD KEY `finance_document_submissions_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `finance_document_submissions_document_type_status_ready_on_index` (`document_type`,`status`,`ready_on`);

--
-- Indexes for table `finance_honorarium_submissions`
--
ALTER TABLE `finance_honorarium_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_honorarium_submissions_defense_request_id_unique` (`defense_request_id`),
  ADD KEY `finance_honorarium_submissions_submitted_by_foreign` (`submitted_by`),
  ADD KEY `finance_honorarium_submissions_verified_by_foreign` (`verified_by`),
  ADD KEY `finance_honorarium_submissions_status_submitted_at_index` (`status`,`submitted_at`);

--
-- Indexes for table `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_documents_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `generated_documents_document_template_id_foreign` (`document_template_id`);

--
-- Indexes for table `honorarium_activities`
--
ALTER TABLE `honorarium_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `honorarium_activities_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `honorarium_activities_user_id_foreign` (`user_id`),
  ADD KEY `honorarium_activities_finance_document_submission_id_foreign` (`finance_document_submission_id`),
  ADD KEY `honorarium_activities_occurred_at_activity_type_index` (`occurred_at`,`activity_type`);

--
-- Indexes for table `honorarium_disbursements`
--
ALTER TABLE `honorarium_disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `honorarium_disbursements_prepared_by_foreign` (`prepared_by`),
  ADD KEY `honorarium_disbursements_defense_request_id_status_index` (`defense_request_id`,`status`);

--
-- Indexes for table `honorarium_payments`
--
ALTER TABLE `honorarium_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `honorarium_payments_defense_request_id_foreign` (`defense_request_id`),
  ADD KEY `honorarium_payments_panelist_id_foreign` (`panelist_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legacy_academic_records`
--
ALTER TABLE `legacy_academic_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legacy_clearance_data`
--
ALTER TABLE `legacy_clearance_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_clearance_data_legacy_scraped_data_id_foreign` (`legacy_scraped_data_id`),
  ADD KEY `legacy_clearance_data_user_id_semester_id_index` (`user_id`,`semester_id`),
  ADD KEY `legacy_clearance_data_clearance_id_clearance_area_id_index` (`clearance_id`,`clearance_area_id`),
  ADD KEY `legacy_clearance_data_all_cleared_index` (`all_cleared`),
  ADD KEY `legacy_clearance_data_scraped_at_index` (`scraped_at`);

--
-- Indexes for table `legacy_clearance_statuses`
--
ALTER TABLE `legacy_clearance_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legacy_credentials`
--
ALTER TABLE `legacy_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_credentials_user_id_unique` (`user_id`);

--
-- Indexes for table `legacy_grades_data`
--
ALTER TABLE `legacy_grades_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_semester_course` (`user_id`,`semester_id`,`course_code`),
  ADD KEY `legacy_grades_data_legacy_scraped_data_id_foreign` (`legacy_scraped_data_id`),
  ADD KEY `legacy_grades_data_user_id_semester_id_index` (`user_id`,`semester_id`),
  ADD KEY `legacy_grades_data_course_code_index` (`course_code`),
  ADD KEY `legacy_grades_data_is_incomplete_index` (`is_incomplete`),
  ADD KEY `legacy_grades_data_rating_show_index` (`rating_show`),
  ADD KEY `legacy_grades_data_scraped_at_index` (`scraped_at`);

--
-- Indexes for table `legacy_record_caches`
--
ALTER TABLE `legacy_record_caches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_record_caches_user_id_type_unique` (`user_id`,`type`);

--
-- Indexes for table `legacy_scraped_data`
--
ALTER TABLE `legacy_scraped_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_scraped_data_user_id_scraped_at_index` (`user_id`,`scraped_at`),
  ADD KEY `legacy_scraped_data_school_id_index` (`school_id`),
  ADD KEY `legacy_scraped_data_current_semester_id_index` (`current_semester_id`);

--
-- Indexes for table `legacy_student_info`
--
ALTER TABLE `legacy_student_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  ADD KEY `messages_user_id_index` (`user_id`);

--
-- Indexes for table `message_participants`
--
ALTER TABLE `message_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `message_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  ADD KEY `message_participants_user_id_index` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notifications_event_key_unique` (`event_key`),
  ADD KEY `notifications_user_id_foreign` (`user_id`),
  ADD KEY `notifications_defense_request_id_index` (`defense_request_id`);

--
-- Indexes for table `panelists`
--
ALTER TABLE `panelists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `panelists_email_unique` (`email`);

--
-- Indexes for table `panelist_honorarium_specs`
--
ALTER TABLE `panelist_honorarium_specs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `panelist_honorarium_specs_role_defense_type_unique` (`role`,`defense_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_rates`
--
ALTER TABLE `payment_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_rates_unique_combo` (`program_level`,`type`,`defense_type`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_records_student_record_id_foreign` (`student_record_id`);

--
-- Indexes for table `payment_submissions`
--
ALTER TABLE `payment_submissions`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `payment_submissions_student_id_payment_type_unique` (`student_id`,`payment_type`),
  ADD KEY `payment_submissions_checked_by_foreign` (`checked_by`),
  ADD KEY `payment_submissions_status_payment_type_index` (`status`,`payment_type`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `program_records`
--
ALTER TABLE `program_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`role_id`,`user_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `student_records`
--
ALTER TABLE `student_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_records_student_id_unique` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_student_number_unique` (`student_number`),
  ADD UNIQUE KEY `users_adviser_code_unique` (`adviser_code`),
  ADD UNIQUE KEY `users_coordinator_code_unique` (`coordinator_code`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_legacy_account_id_index` (`legacy_account_id`),
  ADD KEY `users_student_number_legacy_index` (`student_number_legacy`);

--
-- Indexes for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_signatures_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aa_payment_batches`
--
ALTER TABLE `aa_payment_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aa_payment_verifications`
--
ALTER TABLE `aa_payment_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `advisers`
--
ALTER TABLE `advisers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adviser_coordinator`
--
ALTER TABLE `adviser_coordinator`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adviser_settings`
--
ALTER TABLE `adviser_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adviser_student`
--
ALTER TABLE `adviser_student`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comprehensive_exams`
--
ALTER TABLE `comprehensive_exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinator_programs`
--
ALTER TABLE `coordinator_programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinator_program_assignments`
--
ALTER TABLE `coordinator_program_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_access_audits`
--
ALTER TABLE `data_access_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_requests`
--
ALTER TABLE `defense_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_request_cancellations`
--
ALTER TABLE `defense_request_cancellations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_request_status_logs`
--
ALTER TABLE `defense_request_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_service_records`
--
ALTER TABLE `defense_service_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_application`
--
ALTER TABLE `exam_application`
  MODIFY `application_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_application_subject`
--
ALTER TABLE `exam_application_subject`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_document_submissions`
--
ALTER TABLE `finance_document_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_honorarium_submissions`
--
ALTER TABLE `finance_honorarium_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generated_documents`
--
ALTER TABLE `generated_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `honorarium_activities`
--
ALTER TABLE `honorarium_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `honorarium_disbursements`
--
ALTER TABLE `honorarium_disbursements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `honorarium_payments`
--
ALTER TABLE `honorarium_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_academic_records`
--
ALTER TABLE `legacy_academic_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_clearance_data`
--
ALTER TABLE `legacy_clearance_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_clearance_statuses`
--
ALTER TABLE `legacy_clearance_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_credentials`
--
ALTER TABLE `legacy_credentials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_grades_data`
--
ALTER TABLE `legacy_grades_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_record_caches`
--
ALTER TABLE `legacy_record_caches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_scraped_data`
--
ALTER TABLE `legacy_scraped_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_student_info`
--
ALTER TABLE `legacy_student_info`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_participants`
--
ALTER TABLE `message_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panelists`
--
ALTER TABLE `panelists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panelist_honorarium_specs`
--
ALTER TABLE `panelist_honorarium_specs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_rates`
--
ALTER TABLE `payment_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_submissions`
--
ALTER TABLE `payment_submissions`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_records`
--
ALTER TABLE `program_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_records`
--
ALTER TABLE `student_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_signatures`
--
ALTER TABLE `user_signatures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aa_payment_verifications`
--
ALTER TABLE `aa_payment_verifications`
  ADD CONSTRAINT `aa_payment_verifications_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aa_payment_verifications_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `aa_payment_batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aa_payment_verifications_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advisers`
--
ALTER TABLE `advisers`
  ADD CONSTRAINT `advisers_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advisers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `adviser_coordinator`
--
ALTER TABLE `adviser_coordinator`
  ADD CONSTRAINT `adviser_coordinator_adviser_id_foreign` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adviser_coordinator_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adviser_settings`
--
ALTER TABLE `adviser_settings`
  ADD CONSTRAINT `adviser_settings_adviser_id_foreign` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adviser_student`
--
ALTER TABLE `adviser_student`
  ADD CONSTRAINT `adviser_student_adviser_id_foreign` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adviser_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comprehensive_exams`
--
ALTER TABLE `comprehensive_exams`
  ADD CONSTRAINT `comprehensive_exams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coordinator_programs`
--
ALTER TABLE `coordinator_programs`
  ADD CONSTRAINT `coordinator_programs_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coordinator_program_assignments`
--
ALTER TABLE `coordinator_program_assignments`
  ADD CONSTRAINT `coordinator_program_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coordinator_program_assignments_coordinator_user_id_foreign` FOREIGN KEY (`coordinator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `data_access_audits`
--
ALTER TABLE `data_access_audits`
  ADD CONSTRAINT `data_access_audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `defense_requests`
--
ALTER TABLE `defense_requests`
  ADD CONSTRAINT `defense_requests_adviser_reviewed_by_foreign` FOREIGN KEY (`adviser_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_coordinator_assigned_by_foreign` FOREIGN KEY (`coordinator_assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_coordinator_reviewed_by_foreign` FOREIGN KEY (`coordinator_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_coordinator_user_id_foreign` FOREIGN KEY (`coordinator_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_last_status_updated_by_foreign` FOREIGN KEY (`last_status_updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `defense_requests_panels_assigned_by_foreign` FOREIGN KEY (`panels_assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_schedule_set_by_foreign` FOREIGN KEY (`schedule_set_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_requests_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `defense_request_cancellations`
--
ALTER TABLE `defense_request_cancellations`
  ADD CONSTRAINT `defense_request_cancellations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `defense_request_cancellations_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `defense_request_status_logs`
--
ALTER TABLE `defense_request_status_logs`
  ADD CONSTRAINT `defense_request_status_logs_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `defense_request_status_logs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `defense_service_records`
--
ALTER TABLE `defense_service_records`
  ADD CONSTRAINT `defense_service_records_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `defense_service_records_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`);

--
-- Constraints for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD CONSTRAINT `document_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_application_subject`
--
ALTER TABLE `exam_application_subject`
  ADD CONSTRAINT `exam_application_subject_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `exam_application` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_document_submissions`
--
ALTER TABLE `finance_document_submissions`
  ADD CONSTRAINT `finance_document_submissions_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finance_document_submissions_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_document_submissions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_honorarium_submissions`
--
ALTER TABLE `finance_honorarium_submissions`
  ADD CONSTRAINT `finance_honorarium_submissions_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finance_honorarium_submissions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finance_honorarium_submissions_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD CONSTRAINT `generated_documents_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `generated_documents_document_template_id_foreign` FOREIGN KEY (`document_template_id`) REFERENCES `document_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `honorarium_activities`
--
ALTER TABLE `honorarium_activities`
  ADD CONSTRAINT `honorarium_activities_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `honorarium_activities_finance_document_submission_id_foreign` FOREIGN KEY (`finance_document_submission_id`) REFERENCES `finance_document_submissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `honorarium_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `honorarium_disbursements`
--
ALTER TABLE `honorarium_disbursements`
  ADD CONSTRAINT `honorarium_disbursements_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`),
  ADD CONSTRAINT `honorarium_disbursements_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `honorarium_payments`
--
ALTER TABLE `honorarium_payments`
  ADD CONSTRAINT `honorarium_payments_defense_request_id_foreign` FOREIGN KEY (`defense_request_id`) REFERENCES `defense_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `honorarium_payments_panelist_id_foreign` FOREIGN KEY (`panelist_id`) REFERENCES `panelists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_clearance_data`
--
ALTER TABLE `legacy_clearance_data`
  ADD CONSTRAINT `legacy_clearance_data_legacy_scraped_data_id_foreign` FOREIGN KEY (`legacy_scraped_data_id`) REFERENCES `legacy_scraped_data` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `legacy_clearance_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_credentials`
--
ALTER TABLE `legacy_credentials`
  ADD CONSTRAINT `legacy_credentials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_grades_data`
--
ALTER TABLE `legacy_grades_data`
  ADD CONSTRAINT `legacy_grades_data_legacy_scraped_data_id_foreign` FOREIGN KEY (`legacy_scraped_data_id`) REFERENCES `legacy_scraped_data` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `legacy_grades_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_record_caches`
--
ALTER TABLE `legacy_record_caches`
  ADD CONSTRAINT `legacy_record_caches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_scraped_data`
--
ALTER TABLE `legacy_scraped_data`
  ADD CONSTRAINT `legacy_scraped_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_participants`
--
ALTER TABLE `message_participants`
  ADD CONSTRAINT `message_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD CONSTRAINT `payment_records_student_record_id_foreign` FOREIGN KEY (`student_record_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_submissions`
--
ALTER TABLE `payment_submissions`
  ADD CONSTRAINT `payment_submissions_checked_by_foreign` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD CONSTRAINT `user_signatures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
