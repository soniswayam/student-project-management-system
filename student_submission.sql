-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 07, 2026 at 12:47 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_submission`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignment_no` smallint UNSIGNED DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Theory',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `attachment_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_faculty_id_foreign` (`faculty_id`),
  KEY `assignments_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `faculty_id`, `department_id`, `subject`, `assignment_no`, `type`, `title`, `description`, `attachment_path`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'OS', 1, 'Theory', 'Unit 1 Assignment', NULL, NULL, '2026-06-07 06:30:00', '2026-07-04 06:15:53', '2026-07-04 06:15:53'),
(2, 1, 4, 'Angular JS', 1, 'Theory', 'Angular Unit 1 Assingment', NULL, NULL, '2026-06-07 12:30:00', '2026-07-06 01:35:39', '2026-07-06 01:35:39'),
(3, 2, 4, 'Angular JS', 1, 'Theory', 'Angular Unit 1 Assingment', NULL, NULL, '2026-09-07 12:30:00', '2026-07-07 06:19:08', '2026-07-07 06:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

DROP TABLE IF EXISTS `assignment_submissions`;
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `checked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_submissions_assignment_id_student_id_unique` (`assignment_id`,`student_id`),
  KEY `assignment_submissions_student_id_foreign` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `file_path`, `remarks`, `submitted_at`, `status`, `feedback`, `checked_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'assignment_submissions/assignment_2/Gld8ITltC6mZDef9Gzp12OcYQMWvJnEFZFEtTLVI.zip', NULL, '2026-07-06 01:36:22', 'submitted', NULL, NULL, '2026-07-06 01:36:22', '2026-07-06 01:36:22'),
(2, 3, 1, 'assignment_submissions/assignment_3/trsy5rqbUZMlnxJsfB6fBpAbFnhkKEsMa8jue79b.pdf', NULL, '2026-07-07 06:19:54', 'checked', NULL, '2026-07-07 06:20:45', '2026-07-07 06:19:54', '2026-07-07 06:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `college_settings`
--

DROP TABLE IF EXISTS `college_settings`;
CREATE TABLE IF NOT EXISTS `college_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Your College Name',
  `tagline` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `affiliation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `college_settings`
--

INSERT INTO `college_settings` (`id`, `name`, `tagline`, `address`, `affiliation`, `email`, `phone`, `website`, `logo_path`, `created_at`, `updated_at`) VALUES
(1, 'National Computer College', 'Affiliated to Saurashtra University and Approved by AICTE', 'Airforce2 Rd, Sundaram Colony, Satyam Colony, Jamnagar, Gujarat 361004', 'Saurasta University', 'national1313@gmail.com', '0288 271 1338', 'https://nationalcollege.org.in/', 'settings/FZtQInsCdYKbTtWC3Ze11LpOVdWrOn7P01lp2VqO.png', '2026-07-04 06:23:21', '2026-07-07 03:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_semesters` tinyint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `total_semesters`, `created_at`, `updated_at`) VALUES
(1, 'B.C.A', 'BCA', NULL, '2026-07-04 02:01:14', '2026-07-04 02:01:14'),
(2, 'B.COM', 'BCOM', NULL, '2026-07-04 02:01:26', '2026-07-04 02:01:26'),
(3, 'B.B.A', 'BBA', NULL, '2026-07-04 02:01:36', '2026-07-04 02:01:36'),
(4, 'MSC(IT & CA)', 'MSCIT', NULL, '2026-07-04 02:03:01', '2026-07-04 02:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

DROP TABLE IF EXISTS `faculties`;
CREATE TABLE IF NOT EXISTS `faculties` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `designation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faculties_user_id_foreign` (`user_id`),
  KEY `faculties_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `user_id`, `department_id`, `designation`, `phone`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Professor', '1234567890', '2026-07-04 02:08:36', '2026-07-04 02:08:36'),
(2, 4, 1, 'Professor', '1234567890', '2026-07-04 06:26:09', '2026-07-06 07:51:25');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_assignments`
--

DROP TABLE IF EXISTS `faculty_assignments`;
CREATE TABLE IF NOT EXISTS `faculty_assignments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `faculty_id` bigint UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_assignments_project_id_unique` (`project_id`),
  KEY `faculty_assignments_faculty_id_foreign` (`faculty_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_assignments`
--

INSERT INTO `faculty_assignments` (`id`, `project_id`, `faculty_id`, `assigned_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-07-07 04:10:59', '2026-07-07 04:10:51', '2026-07-07 04:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_department`
--

DROP TABLE IF EXISTS `faculty_department`;
CREATE TABLE IF NOT EXISTS `faculty_department` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_department_faculty_id_department_id_unique` (`faculty_id`,`department_id`),
  KEY `faculty_department_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_department`
--

INSERT INTO `faculty_department` (`id`, `faculty_id`, `department_id`) VALUES
(1, 1, 1),
(3, 1, 4),
(4, 2, 1),
(2, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000001_create_departments_table', 1),
(5, '2026_01_01_000002_create_students_table', 1),
(6, '2026_01_01_000003_create_faculties_table', 1),
(7, '2026_01_01_000004_create_projects_table', 1),
(8, '2026_01_01_000005_create_project_members_table', 1),
(9, '2026_01_01_000006_create_faculty_assignments_table', 1),
(10, '2026_01_01_000007_create_project_submissions_table', 1),
(11, '2026_01_01_000008_create_project_reviews_table', 1),
(12, '2026_01_01_000009_create_notifications_table', 1),
(13, '2026_01_02_000001_create_roles_table', 1),
(14, '2026_01_02_000002_create_college_settings_table', 1),
(15, '2026_01_02_000003_change_users_role_to_string', 1),
(16, '2026_01_03_000001_create_assignments_table', 1),
(17, '2026_01_03_000002_create_assignment_submissions_table', 1),
(18, '2026_01_03_000003_add_subject_to_assignments', 1),
(19, '2026_01_04_000001_add_rejected_status_to_projects', 1),
(20, '2026_01_05_000001_add_status_to_users_table', 2),
(21, '2026_01_05_000002_add_semester_to_students_table', 2),
(22, '2026_01_06_000001_add_total_semesters_to_departments_table', 3),
(23, '2026_01_05_000001_create_faculty_department_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://localhost:8000/student/assignments', '2026-07-06 01:46:48', '2026-07-06 01:35:39', '2026-07-06 01:46:48'),
(2, 3, 'Assignment submitted', 'A student submitted \"Angular Unit 1 Assingment\".', 'http://localhost:8000/faculty/assignments/2', NULL, '2026-07-06 01:36:22', '2026-07-06 01:36:22'),
(3, 4, 'New project assigned', 'You have been assigned to review the project \"ElectroSpark\".', 'http://localhost:8000/faculty/projects/1', '2026-07-07 04:14:39', '2026-07-07 04:10:51', '2026-07-07 04:14:39'),
(4, 4, 'New project assigned', 'You have been assigned to review the project \"ElectroSpark\".', 'http://localhost:8000/faculty/projects/1', '2026-07-07 06:17:28', '2026-07-07 04:11:00', '2026-07-07 06:17:28'),
(5, 5, 'Synopsis Approved', 'Your synopsis for \"ElectroSpark\" was marked: approved.', 'http://localhost:8000/student/project', '2026-07-07 05:04:22', '2026-07-07 04:14:54', '2026-07-07 05:04:22'),
(6, 161, 'Synopsis Approved', 'Your synopsis for \"ElectroSpark\" was marked: approved.', 'http://localhost:8000/student/project', NULL, '2026-07-07 04:14:54', '2026-07-07 04:14:54'),
(8, 4, 'Final project submitted', 'Final files submitted for \"ElectroSpark\". Ready for review.', 'http://localhost:8000/faculty/projects/1', '2026-07-07 06:17:31', '2026-07-07 05:42:39', '2026-07-07 06:17:31'),
(9, 4, 'Final project submitted', 'Final files submitted for \"ElectroSpark\". Ready for review.', 'http://127.0.0.1:8000/faculty/projects/1', '2026-07-07 06:17:31', '2026-07-07 05:47:10', '2026-07-07 06:17:31'),
(10, 5, 'Final project reviewed', 'Your project \"ElectroSpark\" was reviewed.', 'http://127.0.0.1:8000/student/project', NULL, '2026-07-07 06:17:55', '2026-07-07 06:17:55'),
(11, 161, 'Final project reviewed', 'Your project \"ElectroSpark\" was reviewed.', 'http://127.0.0.1:8000/student/project', NULL, '2026-07-07 06:17:56', '2026-07-07 06:17:56'),
(12, 5, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(13, 158, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(14, 159, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(15, 160, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(16, 161, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(17, 162, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:08', '2026-07-07 06:19:08'),
(18, 163, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(19, 164, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(20, 165, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(21, 166, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(22, 167, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(23, 168, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(24, 169, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(25, 170, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(26, 171, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(27, 172, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(28, 173, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(29, 174, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(30, 175, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(31, 176, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:09', '2026-07-07 06:19:09'),
(32, 177, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(33, 178, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(34, 179, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(35, 180, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(36, 181, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(37, 182, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(38, 183, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(39, 184, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(40, 185, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(41, 186, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(42, 187, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(43, 188, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(44, 189, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(45, 190, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(46, 191, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:10', '2026-07-07 06:19:10'),
(47, 192, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(48, 193, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(49, 194, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(50, 195, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(51, 196, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(52, 197, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(53, 198, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(54, 199, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(55, 200, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(56, 201, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(57, 202, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(58, 203, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(59, 204, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(60, 205, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:11', '2026-07-07 06:19:11'),
(61, 206, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:12', '2026-07-07 06:19:12'),
(62, 207, 'New assignment: Angular Unit 1 Assingment', 'A new assignment \"Angular Unit 1 Assingment\" has been posted for your department.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:19:12', '2026-07-07 06:19:12'),
(63, 4, 'Assignment submitted', 'A student submitted \"Angular Unit 1 Assingment\".', 'http://127.0.0.1:8000/faculty/assignments/3', '2026-07-07 06:23:01', '2026-07-07 06:19:54', '2026-07-07 06:23:01'),
(64, 5, 'Assignment checked', 'Your submission for \"Angular Unit 1 Assingment\" has been checked.', 'http://127.0.0.1:8000/student/assignments', NULL, '2026-07-07 06:20:45', '2026-07-07 06:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_type` enum('single','group') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_student_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `frontend_tech` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `backend_tech` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abstract` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Synopsis Pending','Synopsis Under Review','Synopsis Approved','Correction Required','Rejected','Final Submitted','Final Reviewed','Completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Synopsis Under Review',
  `marks` smallint UNSIGNED DEFAULT NULL,
  `final_remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_leader_student_id_unique` (`leader_student_id`),
  KEY `projects_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_type`, `name`, `leader_student_id`, `department_id`, `frontend_tech`, `backend_tech`, `abstract`, `status`, `marks`, `final_remarks`, `created_at`, `updated_at`) VALUES
(1, 'group', 'ElectroSpark', 1, 4, 'HTML, CSS, BOOTSRAP', 'Laravel, MySQL', 'This project is a ecommerce website.', 'Final Reviewed', NULL, NULL, '2026-07-07 03:46:59', '2026-07-07 06:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
CREATE TABLE IF NOT EXISTS `project_members` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `role_in_project` enum('leader','partner') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_members_student_id_unique` (`student_id`),
  KEY `project_members_project_id_foreign` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `student_id`, `role_in_project`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'leader', '2026-07-07 03:47:00', '2026-07-07 03:47:00'),
(2, 1, 157, 'partner', '2026-07-07 03:47:00', '2026-07-07 03:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_reviews`
--

DROP TABLE IF EXISTS `project_reviews`;
CREATE TABLE IF NOT EXISTS `project_reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `faculty_id` bigint UNSIGNED NOT NULL,
  `stage` enum('synopsis','final') COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('approved','rejected','correction','reviewed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `marks` smallint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_reviews_project_id_foreign` (`project_id`),
  KEY `project_reviews_faculty_id_foreign` (`faculty_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_reviews`
--

INSERT INTO `project_reviews` (`id`, `project_id`, `faculty_id`, `stage`, `action`, `comments`, `marks`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'synopsis', 'approved', NULL, NULL, '2026-07-07 04:14:53', '2026-07-07 04:14:53'),
(2, 1, 2, 'final', 'reviewed', NULL, NULL, '2026-07-07 06:17:55', '2026-07-07 06:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `project_submissions`
--

DROP TABLE IF EXISTS `project_submissions`;
CREATE TABLE IF NOT EXISTS `project_submissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `report_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_zip_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ppt_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `screenshots` json DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_submissions_project_id_unique` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_submissions`
--

INSERT INTO `project_submissions` (`id`, `project_id`, `report_path`, `source_zip_path`, `ppt_path`, `screenshots`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'submissions/project_1/pnfbF7lwArIyduVyHOmLHaKT3cBLRAVj3hXFtdcS.pdf', 'submissions/project_1/i091hIyloer3ig4uxBIeuFNbFDQeEGoaU5E8JVqF.rar', NULL, NULL, '2026-07-07 05:47:09', '2026-07-07 05:42:37', '2026-07-07 05:47:10');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` json DEFAULT NULL,
  `is_staff` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `label`, `permissions`, `is_staff`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Admin', '[\"*\"]', 1, 1, '2026-07-04 01:37:48', '2026-07-04 01:37:48'),
(2, 'admin', 'Admin', '[\"dashboard.view\", \"students.view\", \"students.create\", \"students.edit\", \"faculty.view\", \"departments.view\", \"projects.view\", \"projects.assign\", \"projects.export\", \"assignments.view\", \"assignments.manage\", \"reports.view\"]', 1, 1, '2026-07-04 01:37:49', '2026-07-04 01:37:49'),
(3, 'faculty', 'Faculty', '[]', 0, 1, '2026-07-04 01:37:49', '2026-07-04 01:37:49'),
(4, 'student', 'Student', '[]', 0, 1, '2026-07-04 01:37:49', '2026-07-04 01:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('53CMgZgCz5b4cWI5sHWgusl8ujjdyctZlCboKi93', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'eyJfdG9rZW4iOiI3TEFTR0VrbHh4enpoODNKbjIxelF3dXg1Qkl0eHRxeGw2UFZkalBXIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3N0dWRlbnRcL2Rhc2hib2FyZCIsInJvdXRlIjoic3R1ZGVudC5kYXNoYm9hcmQifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjV9', 1783424851),
('Ikp2VKXfZvNcp0BpCRbxgFfdOdqza8YirKnkdmvN', NULL, '127.0.0.1', 'curl/8.16.0', 'eyJfdG9rZW4iOiJSblJOQWx4R2RITjRFTVJESFcyY1VpOUVxZTlPUE1oOHR0UUhhNVI2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783422573),
('R95G3WVo6P1At6ZJzsBA5lAvL9LVplvV3lgoTF0S', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'eyJfdG9rZW4iOiJsSGJHT0lFaW1FaDl2T2VMdXVwRlBjaG9UbW1UVmh5NllJMGkwcGVhIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL25vdGlmaWNhdGlvbnMiLCJyb3V0ZSI6Im5vdGlmaWNhdGlvbnMuaW5kZXgifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjR9', 1783425182),
('StAiIxPoS63Dl6URSMKVzqKZJZurnBn6Z5QmoxRq', NULL, '127.0.0.1', 'curl/8.16.0', 'eyJfdG9rZW4iOiIwQ2hEbEJmazU3bWwyQndNbFhkNktjQTRuNUVwdEo1N1RkSnN6c1pOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MTIzXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783424721),
('wp4wu5lUJg8RJ0UAjy3MfPLUJDCi48wbHNRMZLPV', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJKNVowa1FTaWlnZEp4UmJFdEE5enJZWlFHY1h0UDZST0JmbEdrOWJtIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3N0dWRlbnRcL3N1Ym1pc3Npb25cL2NyZWF0ZSIsInJvdXRlIjoic3R1ZGVudC5zdWJtaXNzaW9uLmNyZWF0ZSJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NX0=', 1783421097),
('ycMkcDK25G5sHV86JrSPZYxvbx6GS1Fp80jGdJ56', NULL, '127.0.0.1', 'curl/8.16.0', 'eyJfdG9rZW4iOiJCMXRhQkRwaHNudW82eXdQcTlvaEJLcHVPVmRkOW0zRUNiSm82MVN3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MTIzXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783424720);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `roll_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_roll_no_unique` (`roll_no`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `department_id`, `roll_no`, `semester`, `phone`, `created_at`, `updated_at`) VALUES
(1, 5, 4, '12345', '4', '1234567890', '2026-07-04 06:37:01', '2026-07-06 05:52:30'),
(4, 8, 1, 'BCA001', '1', '9000000001', '2026-07-06 06:53:28', '2026-07-06 06:53:28'),
(5, 9, 1, 'BCA002', '2', '9000000002', '2026-07-06 06:53:29', '2026-07-06 06:53:29'),
(6, 10, 1, 'BCA003', '3', '9000000003', '2026-07-06 06:53:29', '2026-07-06 06:53:29'),
(7, 11, 1, 'BCA004', '4', '9000000004', '2026-07-06 06:53:30', '2026-07-06 06:53:30'),
(8, 12, 1, 'BCA005', '5', '9000000005', '2026-07-06 06:53:30', '2026-07-06 06:53:30'),
(9, 13, 1, 'BCA006', '6', '9000000006', '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(10, 14, 1, 'BCA007', '1', '9000000007', '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(11, 15, 1, 'BCA008', '2', '9000000008', '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(12, 16, 1, 'BCA009', '3', '9000000009', '2026-07-06 06:53:32', '2026-07-06 06:53:32'),
(13, 17, 1, 'BCA010', '4', '9000000010', '2026-07-06 06:53:33', '2026-07-06 06:53:33'),
(14, 18, 1, 'BCA011', '5', '9000000011', '2026-07-06 06:53:33', '2026-07-06 06:53:33'),
(15, 19, 1, 'BCA012', '6', '9000000012', '2026-07-06 06:53:34', '2026-07-06 06:53:34'),
(16, 20, 1, 'BCA013', '1', '9000000013', '2026-07-06 06:53:34', '2026-07-06 06:53:34'),
(17, 21, 1, 'BCA014', '2', '9000000014', '2026-07-06 06:53:35', '2026-07-06 06:53:35'),
(18, 22, 1, 'BCA015', '3', '9000000015', '2026-07-06 06:53:35', '2026-07-06 06:53:35'),
(19, 23, 1, 'BCA016', '4', '9000000016', '2026-07-06 06:53:36', '2026-07-06 06:53:36'),
(20, 24, 1, 'BCA017', '5', '9000000017', '2026-07-06 06:53:36', '2026-07-06 06:53:36'),
(21, 25, 1, 'BCA018', '6', '9000000018', '2026-07-06 06:53:37', '2026-07-06 06:53:37'),
(22, 26, 1, 'BCA019', '1', '9000000019', '2026-07-06 06:53:38', '2026-07-06 06:53:38'),
(23, 27, 1, 'BCA020', '2', '9000000020', '2026-07-06 06:53:38', '2026-07-06 06:53:38'),
(24, 28, 1, 'BCA021', '3', '9000000021', '2026-07-06 06:53:39', '2026-07-06 06:53:39'),
(25, 29, 1, 'BCA022', '4', '9000000022', '2026-07-06 06:53:39', '2026-07-06 06:53:39'),
(26, 30, 1, 'BCA023', '5', '9000000023', '2026-07-06 06:53:40', '2026-07-06 06:53:40'),
(27, 31, 1, 'BCA024', '6', '9000000024', '2026-07-06 06:53:40', '2026-07-06 06:53:40'),
(28, 32, 1, 'BCA025', '1', '9000000025', '2026-07-06 06:53:41', '2026-07-06 06:53:41'),
(29, 33, 1, 'BCA026', '2', '9000000026', '2026-07-06 06:53:41', '2026-07-06 06:53:41'),
(30, 34, 1, 'BCA027', '3', '9000000027', '2026-07-06 06:53:42', '2026-07-06 06:53:42'),
(31, 35, 1, 'BCA028', '4', '9000000028', '2026-07-06 06:53:43', '2026-07-06 06:53:43'),
(32, 36, 1, 'BCA029', '5', '9000000029', '2026-07-06 06:53:43', '2026-07-06 06:53:43'),
(33, 37, 1, 'BCA030', '6', '9000000030', '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(34, 38, 1, 'BCA031', '1', '9000000031', '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(35, 39, 1, 'BCA032', '2', '9000000032', '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(36, 40, 1, 'BCA033', '3', '9000000033', '2026-07-06 06:53:45', '2026-07-06 06:53:45'),
(37, 41, 1, 'BCA034', '4', '9000000034', '2026-07-06 06:53:45', '2026-07-06 06:53:45'),
(38, 42, 1, 'BCA035', '5', '9000000035', '2026-07-06 06:53:46', '2026-07-06 06:53:46'),
(39, 43, 1, 'BCA036', '6', '9000000036', '2026-07-06 06:53:46', '2026-07-06 06:53:46'),
(40, 44, 1, 'BCA037', '1', '9000000037', '2026-07-06 06:53:47', '2026-07-06 06:53:47'),
(41, 45, 1, 'BCA038', '2', '9000000038', '2026-07-06 06:53:47', '2026-07-06 06:53:47'),
(42, 46, 1, 'BCA039', '3', '9000000039', '2026-07-06 06:53:48', '2026-07-06 06:53:48'),
(43, 47, 1, 'BCA040', '4', '9000000040', '2026-07-06 06:53:48', '2026-07-06 06:53:48'),
(44, 48, 1, 'BCA041', '5', '9000000041', '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(45, 49, 1, 'BCA042', '6', '9000000042', '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(46, 50, 1, 'BCA043', '1', '9000000043', '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(47, 51, 1, 'BCA044', '2', '9000000044', '2026-07-06 06:53:50', '2026-07-06 06:53:50'),
(48, 52, 1, 'BCA045', '3', '9000000045', '2026-07-06 06:53:50', '2026-07-06 06:53:50'),
(49, 53, 1, 'BCA046', '4', '9000000046', '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(50, 54, 1, 'BCA047', '5', '9000000047', '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(51, 55, 1, 'BCA048', '6', '9000000048', '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(52, 56, 1, 'BCA049', '1', '9000000049', '2026-07-06 06:53:52', '2026-07-06 06:53:52'),
(53, 57, 1, 'BCA050', '2', '9000000050', '2026-07-06 06:53:52', '2026-07-06 06:53:52'),
(54, 58, 3, 'BBA001', '1', '9000000051', '2026-07-06 06:53:53', '2026-07-06 06:53:53'),
(55, 59, 3, 'BBA002', '2', '9000000052', '2026-07-06 06:53:53', '2026-07-06 06:53:53'),
(56, 60, 3, 'BBA003', '3', '9000000053', '2026-07-06 06:53:54', '2026-07-06 06:53:54'),
(57, 61, 3, 'BBA004', '4', '9000000054', '2026-07-06 06:53:54', '2026-07-06 06:53:54'),
(58, 62, 3, 'BBA005', '5', '9000000055', '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(59, 63, 3, 'BBA006', '6', '9000000056', '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(60, 64, 3, 'BBA007', '1', '9000000057', '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(61, 65, 3, 'BBA008', '2', '9000000058', '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(62, 66, 3, 'BBA009', '3', '9000000059', '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(63, 67, 3, 'BBA010', '4', '9000000060', '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(64, 68, 3, 'BBA011', '5', '9000000061', '2026-07-06 06:53:57', '2026-07-06 06:53:57'),
(65, 69, 3, 'BBA012', '6', '9000000062', '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(66, 70, 3, 'BBA013', '1', '9000000063', '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(67, 71, 3, 'BBA014', '2', '9000000064', '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(68, 72, 3, 'BBA015', '3', '9000000065', '2026-07-06 06:53:59', '2026-07-06 06:53:59'),
(69, 73, 3, 'BBA016', '4', '9000000066', '2026-07-06 06:53:59', '2026-07-06 06:53:59'),
(70, 74, 3, 'BBA017', '5', '9000000067', '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(71, 75, 3, 'BBA018', '6', '9000000068', '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(72, 76, 3, 'BBA019', '1', '9000000069', '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(73, 77, 3, 'BBA020', '2', '9000000070', '2026-07-06 06:54:01', '2026-07-06 06:54:01'),
(74, 78, 3, 'BBA021', '3', '9000000071', '2026-07-06 06:54:01', '2026-07-06 06:54:01'),
(75, 79, 3, 'BBA022', '4', '9000000072', '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(76, 80, 3, 'BBA023', '5', '9000000073', '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(77, 81, 3, 'BBA024', '6', '9000000074', '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(78, 82, 3, 'BBA025', '1', '9000000075', '2026-07-06 06:54:03', '2026-07-06 06:54:03'),
(79, 83, 3, 'BBA026', '2', '9000000076', '2026-07-06 06:54:03', '2026-07-06 06:54:03'),
(80, 84, 3, 'BBA027', '3', '9000000077', '2026-07-06 06:54:04', '2026-07-06 06:54:04'),
(81, 85, 3, 'BBA028', '4', '9000000078', '2026-07-06 06:54:04', '2026-07-06 06:54:04'),
(82, 86, 3, 'BBA029', '5', '9000000079', '2026-07-06 06:54:05', '2026-07-06 06:54:05'),
(83, 87, 3, 'BBA030', '6', '9000000080', '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(84, 88, 3, 'BBA031', '1', '9000000081', '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(85, 89, 3, 'BBA032', '2', '9000000082', '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(86, 90, 3, 'BBA033', '3', '9000000083', '2026-07-06 06:54:07', '2026-07-06 06:54:07'),
(87, 91, 3, 'BBA034', '4', '9000000084', '2026-07-06 06:54:07', '2026-07-06 06:54:07'),
(88, 92, 3, 'BBA035', '5', '9000000085', '2026-07-06 06:54:08', '2026-07-06 06:54:08'),
(89, 93, 3, 'BBA036', '6', '9000000086', '2026-07-06 06:54:08', '2026-07-06 06:54:08'),
(90, 94, 3, 'BBA037', '1', '9000000087', '2026-07-06 06:54:09', '2026-07-06 06:54:09'),
(91, 95, 3, 'BBA038', '2', '9000000088', '2026-07-06 06:54:09', '2026-07-06 06:54:09'),
(92, 96, 3, 'BBA039', '3', '9000000089', '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(93, 97, 3, 'BBA040', '4', '9000000090', '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(94, 98, 3, 'BBA041', '5', '9000000091', '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(95, 99, 3, 'BBA042', '6', '9000000092', '2026-07-06 06:54:11', '2026-07-06 06:54:11'),
(96, 100, 3, 'BBA043', '1', '9000000093', '2026-07-06 06:54:11', '2026-07-06 06:54:11'),
(97, 101, 3, 'BBA044', '2', '9000000094', '2026-07-06 06:54:12', '2026-07-06 06:54:12'),
(98, 102, 3, 'BBA045', '3', '9000000095', '2026-07-06 06:54:12', '2026-07-06 06:54:12'),
(99, 103, 3, 'BBA046', '4', '9000000096', '2026-07-06 06:54:13', '2026-07-06 06:54:13'),
(100, 104, 3, 'BBA047', '5', '9000000097', '2026-07-06 06:54:13', '2026-07-06 06:54:13'),
(101, 105, 3, 'BBA048', '6', '9000000098', '2026-07-06 06:54:14', '2026-07-06 06:54:14'),
(102, 106, 3, 'BBA049', '1', '9000000099', '2026-07-06 06:54:14', '2026-07-06 06:54:14'),
(103, 107, 3, 'BBA050', '2', '9000000100', '2026-07-06 06:54:15', '2026-07-06 06:54:15'),
(104, 108, 2, 'BCOM001', '1', '9000000101', '2026-07-06 06:54:15', '2026-07-06 06:54:15'),
(105, 109, 2, 'BCOM002', '2', '9000000102', '2026-07-06 06:54:16', '2026-07-06 06:54:16'),
(106, 110, 2, 'BCOM003', '3', '9000000103', '2026-07-06 06:54:16', '2026-07-06 06:54:16'),
(107, 111, 2, 'BCOM004', '4', '9000000104', '2026-07-06 06:54:17', '2026-07-06 06:54:17'),
(108, 112, 2, 'BCOM005', '5', '9000000105', '2026-07-06 06:54:17', '2026-07-06 06:54:17'),
(109, 113, 2, 'BCOM006', '6', '9000000106', '2026-07-06 06:54:18', '2026-07-06 06:54:18'),
(110, 114, 2, 'BCOM007', '1', '9000000107', '2026-07-06 06:54:18', '2026-07-06 06:54:18'),
(111, 115, 2, 'BCOM008', '2', '9000000108', '2026-07-06 06:54:19', '2026-07-06 06:54:19'),
(112, 116, 2, 'BCOM009', '3', '9000000109', '2026-07-06 06:54:19', '2026-07-06 06:54:19'),
(113, 117, 2, 'BCOM010', '4', '9000000110', '2026-07-06 06:54:20', '2026-07-06 06:54:20'),
(114, 118, 2, 'BCOM011', '5', '9000000111', '2026-07-06 06:54:20', '2026-07-06 06:54:20'),
(115, 119, 2, 'BCOM012', '6', '9000000112', '2026-07-06 06:54:21', '2026-07-06 06:54:21'),
(116, 120, 2, 'BCOM013', '1', '9000000113', '2026-07-06 06:54:21', '2026-07-06 06:54:21'),
(117, 121, 2, 'BCOM014', '2', '9000000114', '2026-07-06 06:54:22', '2026-07-06 06:54:22'),
(118, 122, 2, 'BCOM015', '3', '9000000115', '2026-07-06 06:54:23', '2026-07-06 06:54:23'),
(119, 123, 2, 'BCOM016', '4', '9000000116', '2026-07-06 06:54:24', '2026-07-06 06:54:24'),
(120, 124, 2, 'BCOM017', '5', '9000000117', '2026-07-06 06:54:25', '2026-07-06 06:54:25'),
(121, 125, 2, 'BCOM018', '6', '9000000118', '2026-07-06 06:54:26', '2026-07-06 06:54:26'),
(122, 126, 2, 'BCOM019', '1', '9000000119', '2026-07-06 06:54:26', '2026-07-06 06:54:26'),
(123, 127, 2, 'BCOM020', '2', '9000000120', '2026-07-06 06:54:27', '2026-07-06 06:54:27'),
(124, 128, 2, 'BCOM021', '3', '9000000121', '2026-07-06 06:54:27', '2026-07-06 06:54:27'),
(125, 129, 2, 'BCOM022', '4', '9000000122', '2026-07-06 06:54:28', '2026-07-06 06:54:28'),
(126, 130, 2, 'BCOM023', '5', '9000000123', '2026-07-06 06:54:28', '2026-07-06 06:54:28'),
(127, 131, 2, 'BCOM024', '6', '9000000124', '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(128, 132, 2, 'BCOM025', '1', '9000000125', '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(129, 133, 2, 'BCOM026', '2', '9000000126', '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(130, 134, 2, 'BCOM027', '3', '9000000127', '2026-07-06 06:54:30', '2026-07-06 06:54:30'),
(131, 135, 2, 'BCOM028', '4', '9000000128', '2026-07-06 06:54:30', '2026-07-06 06:54:30'),
(132, 136, 2, 'BCOM029', '5', '9000000129', '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(133, 137, 2, 'BCOM030', '6', '9000000130', '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(134, 138, 2, 'BCOM031', '1', '9000000131', '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(135, 139, 2, 'BCOM032', '2', '9000000132', '2026-07-06 06:54:32', '2026-07-06 06:54:32'),
(136, 140, 2, 'BCOM033', '3', '9000000133', '2026-07-06 06:54:32', '2026-07-06 06:54:32'),
(137, 141, 2, 'BCOM034', '4', '9000000134', '2026-07-06 06:54:33', '2026-07-06 06:54:33'),
(138, 142, 2, 'BCOM035', '5', '9000000135', '2026-07-06 06:54:34', '2026-07-06 06:54:34'),
(139, 143, 2, 'BCOM036', '6', '9000000136', '2026-07-06 06:54:34', '2026-07-06 06:54:34'),
(140, 144, 2, 'BCOM037', '1', '9000000137', '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(141, 145, 2, 'BCOM038', '2', '9000000138', '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(142, 146, 2, 'BCOM039', '3', '9000000139', '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(143, 147, 2, 'BCOM040', '4', '9000000140', '2026-07-06 06:54:36', '2026-07-06 06:54:36'),
(144, 148, 2, 'BCOM041', '5', '9000000141', '2026-07-06 06:54:36', '2026-07-06 06:54:36'),
(145, 149, 2, 'BCOM042', '6', '9000000142', '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(146, 150, 2, 'BCOM043', '1', '9000000143', '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(147, 151, 2, 'BCOM044', '2', '9000000144', '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(148, 152, 2, 'BCOM045', '3', '9000000145', '2026-07-06 06:54:38', '2026-07-06 06:54:38'),
(149, 153, 2, 'BCOM046', '4', '9000000146', '2026-07-06 06:54:38', '2026-07-06 06:54:38'),
(150, 154, 2, 'BCOM047', '5', '9000000147', '2026-07-06 06:54:39', '2026-07-06 06:54:39'),
(151, 155, 2, 'BCOM048', '6', '9000000148', '2026-07-06 06:54:39', '2026-07-06 06:54:39'),
(152, 156, 2, 'BCOM049', '1', '9000000149', '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(153, 157, 2, 'BCOM050', '2', '9000000150', '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(154, 158, 4, 'MSCITCA001', '1', '9000000151', '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(155, 159, 4, 'MSCITCA002', '2', '9000000152', '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(156, 160, 4, 'MSCITCA003', '3', '9000000153', '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(157, 161, 4, 'MSCITCA004', '4', '9000000154', '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(158, 162, 4, 'MSCITCA005', '1', '9000000155', '2026-07-06 06:54:42', '2026-07-06 06:54:42'),
(159, 163, 4, 'MSCITCA006', '2', '9000000156', '2026-07-06 06:54:42', '2026-07-06 06:54:42'),
(160, 164, 4, 'MSCITCA007', '3', '9000000157', '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(161, 165, 4, 'MSCITCA008', '4', '9000000158', '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(162, 166, 4, 'MSCITCA009', '1', '9000000159', '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(163, 167, 4, 'MSCITCA010', '2', '9000000160', '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(164, 168, 4, 'MSCITCA011', '3', '9000000161', '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(165, 169, 4, 'MSCITCA012', '4', '9000000162', '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(166, 170, 4, 'MSCITCA013', '1', '9000000163', '2026-07-06 06:54:45', '2026-07-06 06:54:45'),
(167, 171, 4, 'MSCITCA014', '2', '9000000164', '2026-07-06 06:54:45', '2026-07-06 06:54:45'),
(168, 172, 4, 'MSCITCA015', '3', '9000000165', '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(169, 173, 4, 'MSCITCA016', '4', '9000000166', '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(170, 174, 4, 'MSCITCA017', '1', '9000000167', '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(171, 175, 4, 'MSCITCA018', '2', '9000000168', '2026-07-06 06:54:47', '2026-07-06 06:54:47'),
(172, 176, 4, 'MSCITCA019', '3', '9000000169', '2026-07-06 06:54:47', '2026-07-06 06:54:47'),
(173, 177, 4, 'MSCITCA020', '4', '9000000170', '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(174, 178, 4, 'MSCITCA021', '1', '9000000171', '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(175, 179, 4, 'MSCITCA022', '2', '9000000172', '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(176, 180, 4, 'MSCITCA023', '3', '9000000173', '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(177, 181, 4, 'MSCITCA024', '4', '9000000174', '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(178, 182, 4, 'MSCITCA025', '1', '9000000175', '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(179, 183, 4, 'MSCITCA026', '2', '9000000176', '2026-07-06 06:54:50', '2026-07-06 06:54:50'),
(180, 184, 4, 'MSCITCA027', '3', '9000000177', '2026-07-06 06:54:50', '2026-07-06 06:54:50'),
(181, 185, 4, 'MSCITCA028', '4', '9000000178', '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(182, 186, 4, 'MSCITCA029', '1', '9000000179', '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(183, 187, 4, 'MSCITCA030', '2', '9000000180', '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(184, 188, 4, 'MSCITCA031', '3', '9000000181', '2026-07-06 06:54:52', '2026-07-06 06:54:52'),
(185, 189, 4, 'MSCITCA032', '4', '9000000182', '2026-07-06 06:54:52', '2026-07-06 06:54:52'),
(186, 190, 4, 'MSCITCA033', '1', '9000000183', '2026-07-06 06:54:53', '2026-07-06 06:54:53'),
(187, 191, 4, 'MSCITCA034', '2', '9000000184', '2026-07-06 06:54:53', '2026-07-06 06:54:53'),
(188, 192, 4, 'MSCITCA035', '3', '9000000185', '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(189, 193, 4, 'MSCITCA036', '4', '9000000186', '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(190, 194, 4, 'MSCITCA037', '1', '9000000187', '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(191, 195, 4, 'MSCITCA038', '2', '9000000188', '2026-07-06 06:54:55', '2026-07-06 06:54:55'),
(192, 196, 4, 'MSCITCA039', '3', '9000000189', '2026-07-06 06:54:55', '2026-07-06 06:54:55'),
(193, 197, 4, 'MSCITCA040', '4', '9000000190', '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(194, 198, 4, 'MSCITCA041', '1', '9000000191', '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(195, 199, 4, 'MSCITCA042', '2', '9000000192', '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(196, 200, 4, 'MSCITCA043', '3', '9000000193', '2026-07-06 06:54:57', '2026-07-06 06:54:57'),
(197, 201, 4, 'MSCITCA044', '4', '9000000194', '2026-07-06 06:54:57', '2026-07-06 06:54:57'),
(198, 202, 4, 'MSCITCA045', '1', '9000000195', '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(199, 203, 4, 'MSCITCA046', '2', '9000000196', '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(200, 204, 4, 'MSCITCA047', '3', '9000000197', '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(201, 205, 4, 'MSCITCA048', '4', '9000000198', '2026-07-06 06:54:59', '2026-07-06 06:54:59'),
(202, 206, 4, 'MSCITCA049', '1', '9000000199', '2026-07-06 06:54:59', '2026-07-06 06:54:59'),
(203, 207, 4, 'MSCITCA050', '2', '9000000200', '2026-07-06 06:54:59', '2026-07-06 06:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@spss.test', NULL, '$2y$12$ZJafq20ypEnDIIWKIvYDjexUrNQCyKOSM51IWFJdBat21qv.Ra9uq', 'super_admin', 'active', NULL, '2026-07-04 02:00:14', '2026-07-06 03:47:15'),
(2, 'Admin', 'admin@spss.test', NULL, '$2y$12$LVpHmJcasBS..8yZ8HL7xO1ww9KUkJmE5G9URzrkzW8hkDEvSjyam', 'admin', 'active', NULL, '2026-07-04 02:00:14', '2026-07-04 02:00:14'),
(3, 'Pro. Piyush Pitroda', 'piyush.ncc@gmail.com', NULL, '$2y$12$QbDAlsTNVb6gMw46meoBtu8tKqGJSOY6mNL7OXZNKJupI8ljl8rMq', 'faculty', 'active', NULL, '2026-07-04 02:08:36', '2026-07-04 06:28:10'),
(4, 'Pro. Chirag Mehta', 'chirag.ncc@gmail.com', NULL, '$2y$12$yrPOO6AuEoOljMXECXJDueBrok/FevRgqvKFSxQcESl5FCmCLoi3O', 'faculty', 'active', NULL, '2026-07-04 06:26:07', '2026-07-04 06:26:07'),
(5, 'Harsh H Makwana', 'harsh@gmail.com', NULL, '$2y$12$QWsjlZ1wmRF677qEY68ycul.VNUILWuzRXn2oJugIOKu71kcwW9vW', 'student', 'active', NULL, '2026-07-04 06:37:01', '2026-07-07 00:55:41'),
(8, 'Atharv Prajapati', 'atharv.prajapati@gmail.com', NULL, '$2y$12$t5dpuCLqtqVK/mvykr/gdupunKMaVE4c2QGL7Au2qBIuvskrZ1h7y', 'student', 'active', NULL, '2026-07-06 06:53:28', '2026-07-06 06:53:28'),
(9, 'Kabir Pandya', 'kabir.pandya@gmail.com', NULL, '$2y$12$mL09i/LTIH44l2D8ywNjp.pvJjWUyegYNwTT/2x8bUQrWr6m/uTy2', 'student', 'active', NULL, '2026-07-06 06:53:29', '2026-07-06 06:53:29'),
(10, 'Anay Joshi', 'anay.joshi@gmail.com', NULL, '$2y$12$X.NdwrvvSlSY8t6jZzxuOuo2hc7LH.fbWtThpVnyy22940H2tDvke', 'student', 'active', NULL, '2026-07-06 06:53:29', '2026-07-06 06:53:29'),
(11, 'Yuvan Verma', 'yuvan.verma@gmail.com', NULL, '$2y$12$QtTcKGj77RZv7aPfffFRhup/LmoU9G4MLVkPmybm4VKBjsOIjT/PW', 'student', 'active', NULL, '2026-07-06 06:53:30', '2026-07-06 06:53:30'),
(12, 'Rohan Sharma', 'rohan.sharma@gmail.com', NULL, '$2y$12$jGEwZzwnbMNOag8cYgtx0e7aJ.YN2TpczxXPQzs/Q441LrNdwGsYC', 'student', 'active', NULL, '2026-07-06 06:53:30', '2026-07-06 06:53:30'),
(13, 'Diya Prajapati', 'diya.prajapati@gmail.com', NULL, '$2y$12$QqG4tnq./AHRMZ/Ru.iDQ.pzF8uEmYzGa5/bjjKenvkXOkXpqg4P2', 'student', 'active', NULL, '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(14, 'Myra Pandya', 'myra.pandya@gmail.com', NULL, '$2y$12$YkzZrIf4/XKzSDFs7jngK.aflVyye4gi/s2wO1AKVTlGnpQAHur6C', 'student', 'active', NULL, '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(15, 'Sara Joshi', 'sara.joshi@gmail.com', NULL, '$2y$12$lWXadFYDk3F0BIyC/oS3f.FpZs3bj5UVzVfiXUAYvxeYVgipbAoGe', 'student', 'active', NULL, '2026-07-06 06:53:31', '2026-07-06 06:53:31'),
(16, 'Riya Verma', 'riya.verma@gmail.com', NULL, '$2y$12$17zg89a5y/0wcMzTyNDrd.MqcY2Ni03IsUM.7wgrkvkDfRj3xPmGy', 'student', 'active', NULL, '2026-07-06 06:53:32', '2026-07-06 06:53:32'),
(17, 'Aadhya Sharma', 'aadhya.sharma@gmail.com', NULL, '$2y$12$Xm2WLMxTOHQ0A8qcXquPz.JfbGDYyWE1etR5tb/9sQPKus496t.UG', 'student', 'active', NULL, '2026-07-06 06:53:33', '2026-07-06 06:53:33'),
(18, 'Avni Prajapati', 'avni.prajapati@gmail.com', NULL, '$2y$12$CCDwTdvQwXaOGGMJmD955eGmXzdh8GJHNDIYuvhatCxNQhVIpg8FS', 'student', 'active', NULL, '2026-07-06 06:53:33', '2026-07-06 06:53:33'),
(19, 'Anvi Pandya', 'anvi.pandya@gmail.com', NULL, '$2y$12$vgXan9gKtOZOZ1t6AOwav.52H4RbfwDD0fA73m3fYbtM53JVB.roi', 'student', 'active', NULL, '2026-07-06 06:53:34', '2026-07-06 06:53:34'),
(20, 'Meera Joshi', 'meera.joshi@gmail.com', NULL, '$2y$12$P6n7QJLLfWVLWsjmdD4FfO6n9pYYuiIINPiC0Eo2nxFYtcfktFueu', 'student', 'active', NULL, '2026-07-06 06:53:34', '2026-07-06 06:53:34'),
(21, 'Nisha Verma', 'nisha.verma@gmail.com', NULL, '$2y$12$XVamxpmvOkOkXUDK5KiEiOsFiTcBhlTqT9DyrGLAarj7WHzDbJzCK', 'student', 'active', NULL, '2026-07-06 06:53:35', '2026-07-06 06:53:35'),
(22, 'Sneha Sharma', 'sneha.sharma@gmail.com', NULL, '$2y$12$aUbDUqc.q2X.4MXvaSLzcOL2xs/DqgjSGm6pC2B4QUB7n66NqyCwq', 'student', 'active', NULL, '2026-07-06 06:53:35', '2026-07-06 06:53:35'),
(23, 'Karan Prajapati', 'karan.prajapati@gmail.com', NULL, '$2y$12$5dWOOrCPUhd5shTeRQwz7.JpUbin42oyeDLoXRp6p0oxuCV6vH3Oy', 'student', 'active', NULL, '2026-07-06 06:53:36', '2026-07-06 06:53:36'),
(24, 'Harsh Pandya', 'harsh.pandya@gmail.com', NULL, '$2y$12$4BktZWTIlYsKMntlSw17s.TbluAYZUsHfkWmjCJ.HdUmAOhrszhyq', 'student', 'active', NULL, '2026-07-06 06:53:36', '2026-07-06 06:53:36'),
(25, 'Yash Joshi', 'yash.joshi@gmail.com', NULL, '$2y$12$2DN2vIVxSbREX4mh.2o39ORzoJax8d7.0wcxwBW3p/g6YjPxsJkwe', 'student', 'active', NULL, '2026-07-06 06:53:37', '2026-07-06 06:53:37'),
(26, 'Jay Verma', 'jay.verma@gmail.com', NULL, '$2y$12$O3JakHi9PfneJpZxJ5R8We2WNG7m3IRLIPE/cmOnC.3wLc5W1nl0S', 'student', 'active', NULL, '2026-07-06 06:53:38', '2026-07-06 06:53:38'),
(27, 'Darshan Sharma', 'darshan.sharma@gmail.com', NULL, '$2y$12$wz5QNQlHWDG8URW/yZaRO.fwYUVjrj8bNX6ET9qnUreL6cdBNFgQ2', 'student', 'active', NULL, '2026-07-06 06:53:38', '2026-07-06 06:53:38'),
(28, 'Neha Prajapati', 'neha.prajapati@gmail.com', NULL, '$2y$12$dGDM0SsKl/rfIuuZ2wF3EOXINkefOCqvHE97Fc1P9YevuxFINmEde', 'student', 'active', NULL, '2026-07-06 06:53:39', '2026-07-06 06:53:39'),
(29, 'Jiya Pandya', 'jiya.pandya@gmail.com', NULL, '$2y$12$PJzKbufZFyKatW28m8pBve4p.u4MlXpLdE7Om1hVkf94BStA1M7sO', 'student', 'active', NULL, '2026-07-06 06:53:39', '2026-07-06 06:53:39'),
(30, 'Mansi Joshi', 'mansi.joshi@gmail.com', NULL, '$2y$12$RiVDjLw3hqt6PA.YwAWEquEPMkwlGu3EuPiMRrixU6UrqvsUe7XyO', 'student', 'active', NULL, '2026-07-06 06:53:40', '2026-07-06 06:53:40'),
(31, 'Payal Verma', 'payal.verma@gmail.com', NULL, '$2y$12$2tlLvNNB/eFZHxt5xkqYqOQ70oZHoGECHewzhx8oQr68SYqigQjh.', 'student', 'active', NULL, '2026-07-06 06:53:40', '2026-07-06 06:53:40'),
(32, 'Bhumi Sharma', 'bhumi.sharma@gmail.com', NULL, '$2y$12$vzMSxOZp5NWkFb9wW0tktOEtf6ZY9vQC/.lUOZeKYNN84q5o.a2ry', 'student', 'active', NULL, '2026-07-06 06:53:41', '2026-07-06 06:53:41'),
(33, 'Vivaan Prajapati', 'vivaan.prajapati@gmail.com', NULL, '$2y$12$bktbhYq2YKRmbgyuwwtaM.mCEEg5YPrjU7IwfPdHXqEzgaCmT3fyO', 'student', 'active', NULL, '2026-07-06 06:53:41', '2026-07-06 06:53:41'),
(34, 'Vihaan Pandya', 'vihaan.pandya@gmail.com', NULL, '$2y$12$moOXXQ5hS4FB67rz4t2.l.CIUwPki.mBf9gdNGduPYv5Wrg9uE93u', 'student', 'active', NULL, '2026-07-06 06:53:42', '2026-07-06 06:53:42'),
(35, 'Sai Joshi', 'sai.joshi@gmail.com', NULL, '$2y$12$ibHDSkQ32XOfUkX5dnpId.e79O2m8elCRAs3BHM0IFp0yUX4lq8MW', 'student', 'active', NULL, '2026-07-06 06:53:43', '2026-07-06 06:53:43'),
(36, 'Ayaan Verma', 'ayaan.verma@gmail.com', NULL, '$2y$12$aJazyx0S/aVGDxyyw9YYU.vQB9xzBCaZD.Yk/omslz0BQlRyTF0NO', 'student', 'active', NULL, '2026-07-06 06:53:43', '2026-07-06 06:53:43'),
(37, 'Ishaan Sharma', 'ishaan.sharma@gmail.com', NULL, '$2y$12$KJvrn6/YIfuhWHicV00esOU.r2wqBh8yxMjoCeQEjtkLoMQsy/DzO', 'student', 'active', NULL, '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(38, 'Atharv Prajapati 31', 'atharv.prajapati.31@gmail.com', NULL, '$2y$12$gN2r662DsVNEePq7iG0xzOrDBwj3v23CwqDNePbUnukR/zHJ5JjVG', 'student', 'active', NULL, '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(39, 'Kabir Pandya 32', 'kabir.pandya.32@gmail.com', NULL, '$2y$12$fxljKt5xQ6kcFbTPVMh4uudwE32sInWJA9ccqRlbFbplYPninbgeW', 'student', 'active', NULL, '2026-07-06 06:53:44', '2026-07-06 06:53:44'),
(40, 'Anay Joshi 33', 'anay.joshi.33@gmail.com', NULL, '$2y$12$AWWntgJkVtUP/p0xymNUvukIADBtig3LbJBqWQC4UogIFnMK0QecW', 'student', 'active', NULL, '2026-07-06 06:53:45', '2026-07-06 06:53:45'),
(41, 'Yuvan Verma 34', 'yuvan.verma.34@gmail.com', NULL, '$2y$12$Zd8JzY76DODgDpgvtvgusuN2/Kbm227oaA2YqTQaPrgAlFk.NfJMO', 'student', 'active', NULL, '2026-07-06 06:53:45', '2026-07-06 06:53:45'),
(42, 'Rohan Sharma 35', 'rohan.sharma.35@gmail.com', NULL, '$2y$12$2.WYZWlI6yUKavbU3./cLecfxtxN.iakYWuv9SCnzWH9gNS6LL9GC', 'student', 'active', NULL, '2026-07-06 06:53:46', '2026-07-06 06:53:46'),
(43, 'Diya Prajapati 36', 'diya.prajapati.36@gmail.com', NULL, '$2y$12$MrPnwNOif5csSuzET9b.g.DqJuPpgO.Rd5VwLh2R7nUskpVMEG0Ne', 'student', 'active', NULL, '2026-07-06 06:53:46', '2026-07-06 06:53:46'),
(44, 'Myra Pandya 37', 'myra.pandya.37@gmail.com', NULL, '$2y$12$UiM0oS75ic1kAvnHxbvPeeedmsjF3wZNPmVCmobX27vkeCnTwEE5a', 'student', 'active', NULL, '2026-07-06 06:53:47', '2026-07-06 06:53:47'),
(45, 'Sara Joshi 38', 'sara.joshi.38@gmail.com', NULL, '$2y$12$aQbo2ZvyblDKN6.JSTy57et/I.7H/3A7z.8262zcS8KjkJ3SgF/j6', 'student', 'active', NULL, '2026-07-06 06:53:47', '2026-07-06 06:53:47'),
(46, 'Riya Verma 39', 'riya.verma.39@gmail.com', NULL, '$2y$12$QZRowm7yg9z2JrhdL9yKJ.QeYlALHTgclXCZ7C7qj4HUDpLa2rYaO', 'student', 'active', NULL, '2026-07-06 06:53:48', '2026-07-06 06:53:48'),
(47, 'Aadhya Sharma 40', 'aadhya.sharma.40@gmail.com', NULL, '$2y$12$U/RWOakAZgewgMl.EHpTg.KDARFv1JcyZHFbiNmHTAHad.e76ziDa', 'student', 'active', NULL, '2026-07-06 06:53:48', '2026-07-06 06:53:48'),
(48, 'Avni Prajapati 41', 'avni.prajapati.41@gmail.com', NULL, '$2y$12$d/OcpZnx6k/.zJJ8BK5vYu0b6S/F6hCZc67lGqeAtsx.doqZJ3u3O', 'student', 'active', NULL, '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(49, 'Anvi Pandya 42', 'anvi.pandya.42@gmail.com', NULL, '$2y$12$bFEljrMWqX7nf0eTScYUmufzXTrffBNmhr0SDU1kRWnSl9E1c/auG', 'student', 'active', NULL, '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(50, 'Meera Joshi 43', 'meera.joshi.43@gmail.com', NULL, '$2y$12$yM4OH9MmtQqgYXyAFjJMUOGg7hBew.XnHypOlL2JdsoUUvHreuiMK', 'student', 'active', NULL, '2026-07-06 06:53:49', '2026-07-06 06:53:49'),
(51, 'Nisha Verma 44', 'nisha.verma.44@gmail.com', NULL, '$2y$12$EdMvifN19O6qgZXaPBGR0.bfyGe1ZkEppTcsz4bQELqBqWG8JOz8u', 'student', 'active', NULL, '2026-07-06 06:53:50', '2026-07-06 06:53:50'),
(52, 'Sneha Sharma 45', 'sneha.sharma.45@gmail.com', NULL, '$2y$12$/qaD/ZUor61QA0zpIwkGrunhCmNhNnfsSbkqHF4G2zEFppfSwBQq.', 'student', 'active', NULL, '2026-07-06 06:53:50', '2026-07-06 06:53:50'),
(53, 'Karan Prajapati 46', 'karan.prajapati.46@gmail.com', NULL, '$2y$12$RRPBYOzh2dCxuzdR0dtQUuneTbSAEJFCH3zsvfkxX9iBJF3TkwDfu', 'student', 'active', NULL, '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(54, 'Harsh Pandya 47', 'harsh.pandya.47@gmail.com', NULL, '$2y$12$d.sgg/cQ37xEECDSG6C5NuZVjT0OIA.CWNc0534YfBjHTEzGP3rF2', 'student', 'active', NULL, '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(55, 'Yash Joshi 48', 'yash.joshi.48@gmail.com', NULL, '$2y$12$MIEGm9jGIYB.bUAtSJTVKuF.rKZHFEsezM3yS8v92CHXcP6tWszjK', 'student', 'active', NULL, '2026-07-06 06:53:51', '2026-07-06 06:53:51'),
(56, 'Jay Verma 49', 'jay.verma.49@gmail.com', NULL, '$2y$12$UFF1rKeyrbSmhIbJ9bFW4.nHfUTsseN1rxETQ4lRUpymbK4Ole4Ae', 'student', 'active', NULL, '2026-07-06 06:53:52', '2026-07-06 06:53:52'),
(57, 'Darshan Sharma 50', 'darshan.sharma.50@gmail.com', NULL, '$2y$12$IuUuXoEjVAbGDgEmvt2rC..r1ZAMYnYy7sG4U.vtDC6mwWroQsO9C', 'student', 'active', NULL, '2026-07-06 06:53:52', '2026-07-06 06:53:52'),
(58, 'Vivaan Solanki', 'vivaan.solanki@gmail.com', NULL, '$2y$12$kG403gFftpSHRWfkUSR8B.zhLa6PT3jkkvYMaRJ6CvJJ.L.mHLKJi', 'student', 'active', NULL, '2026-07-06 06:53:53', '2026-07-06 06:53:53'),
(59, 'Vihaan Desai', 'vihaan.desai@gmail.com', NULL, '$2y$12$Sg5WDuW1FJw1nLemCyA4HO0.ft2V.G/I2wNCpVI7fn3Ch/BOW0fVy', 'student', 'active', NULL, '2026-07-06 06:53:53', '2026-07-06 06:53:53'),
(60, 'Sai Gupta', 'sai.gupta@gmail.com', NULL, '$2y$12$EEebYCqcBNqxtn6mTqYAwugXIchJ9K4.7Y91pUWkdozC/Q1WR3NI.', 'student', 'active', NULL, '2026-07-06 06:53:54', '2026-07-06 06:53:54'),
(61, 'Ayaan Mehta', 'ayaan.mehta@gmail.com', NULL, '$2y$12$GToh8sPAoo267X9Ioob6.OIlsgaXSBCqVP1d21zZUa/4AYm6tAaU6', 'student', 'active', NULL, '2026-07-06 06:53:54', '2026-07-06 06:53:54'),
(62, 'Ishaan Vyas', 'ishaan.vyas@gmail.com', NULL, '$2y$12$KNUyvbAbEBwREKHclMdr2ObBqByZtsBlVIxRoRieM/xGW.Q5VEHDa', 'student', 'active', NULL, '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(63, 'Atharv Solanki', 'atharv.solanki@gmail.com', NULL, '$2y$12$c91DrmmXAp3txxDe1SvmKuLvlRx3r1B/xUGo/zn1V7Ge1GAv92qIW', 'student', 'active', NULL, '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(64, 'Kabir Desai', 'kabir.desai@gmail.com', NULL, '$2y$12$a1TF5sx3iTGP0.dAjX3abuRznWh8PWpT7TNBlKFw0.Ry.dkFPI8xi', 'student', 'active', NULL, '2026-07-06 06:53:55', '2026-07-06 06:53:55'),
(65, 'Anay Gupta', 'anay.gupta@gmail.com', NULL, '$2y$12$YP4bC8WV1G/rEbtpFMo77OPr6vg1rhyjTrQeyJhezxr6Teg9gJwiW', 'student', 'active', NULL, '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(66, 'Yuvan Mehta', 'yuvan.mehta@gmail.com', NULL, '$2y$12$dWW397UzwzmFvavLVshAC.dtxpOTavOkoY2.dA5NqJelf46D9F3O6', 'student', 'active', NULL, '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(67, 'Rohan Vyas', 'rohan.vyas@gmail.com', NULL, '$2y$12$svSf4Y7SZxlOSumUsVMzXeeTmitgRPXabT5saBCIy9ysIxV/XYiYy', 'student', 'active', NULL, '2026-07-06 06:53:56', '2026-07-06 06:53:56'),
(68, 'Diya Solanki', 'diya.solanki@gmail.com', NULL, '$2y$12$CqnhomNOgmsUy.jdo/KXhew3p1g5eVgfqUzpx0WaBAunmMcgVyMOq', 'student', 'active', NULL, '2026-07-06 06:53:57', '2026-07-06 06:53:57'),
(69, 'Myra Desai', 'myra.desai@gmail.com', NULL, '$2y$12$UJkac1JEBJsNaxQxjTneW.hPrwdck3DsqwaA8kR5FDD8L00MSzTU6', 'student', 'active', NULL, '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(70, 'Sara Gupta', 'sara.gupta@gmail.com', NULL, '$2y$12$Wu.cqwQsUIBzZJYInYP0c.3re17OtOWd/ZeRboc4SFXP4KY9PwAY.', 'student', 'active', NULL, '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(71, 'Riya Mehta', 'riya.mehta@gmail.com', NULL, '$2y$12$ieQmi4Ha6WFyLfD4SsR5qeCKjeWY4gL7UQVaDGHsXzbu.sSxzCQ.e', 'student', 'active', NULL, '2026-07-06 06:53:58', '2026-07-06 06:53:58'),
(72, 'Aadhya Vyas', 'aadhya.vyas@gmail.com', NULL, '$2y$12$7q7BQBjZz32jhNRmKhxi5OWZwYB0VW9oFOz5lzVfyaA6Xxn1/6QI2', 'student', 'active', NULL, '2026-07-06 06:53:59', '2026-07-06 06:53:59'),
(73, 'Avni Solanki', 'avni.solanki@gmail.com', NULL, '$2y$12$D6TWFH.cCOCv61GFiVvExOH9vyv7erTX63p5tDFKSw2DTpZemCppK', 'student', 'active', NULL, '2026-07-06 06:53:59', '2026-07-06 06:53:59'),
(74, 'Anvi Desai', 'anvi.desai@gmail.com', NULL, '$2y$12$d6yHFxN8tquOn3RNUq1/FOTBmdNG5v41yQenhedgSaD2BXj/jzwZe', 'student', 'active', NULL, '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(75, 'Meera Gupta', 'meera.gupta@gmail.com', NULL, '$2y$12$mJ0Hz2xQt1wQYz8o8Mo0.uzDcCPqbmSKKIWKcW0w57EX5.FbKYNZW', 'student', 'active', NULL, '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(76, 'Nisha Mehta', 'nisha.mehta@gmail.com', NULL, '$2y$12$aFVqJCawhDyF90KpFVyyEeucaglAVxsY3cMXLAOOlclnihubT6SWG', 'student', 'active', NULL, '2026-07-06 06:54:00', '2026-07-06 06:54:00'),
(77, 'Sneha Vyas', 'sneha.vyas@gmail.com', NULL, '$2y$12$K8xX4GPEFxmqJQjb2ln/h.yK.3GzozORWdb5Qohnec9j/gdzVVqWy', 'student', 'active', NULL, '2026-07-06 06:54:01', '2026-07-06 06:54:01'),
(78, 'Karan Solanki', 'karan.solanki@gmail.com', NULL, '$2y$12$tE1t2VbPjobjwASaDSdo7uo67lMe01Zfp7RSZ1kEOahUGYtMfpi8e', 'student', 'active', NULL, '2026-07-06 06:54:01', '2026-07-06 06:54:01'),
(79, 'Harsh Desai', 'harsh.desai@gmail.com', NULL, '$2y$12$uJ0fZrrR3KA.t0n0N2Bl5ed1H7xmADd.8eDGTWvx3YH7l6TiOpxiq', 'student', 'active', NULL, '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(80, 'Yash Gupta', 'yash.gupta@gmail.com', NULL, '$2y$12$Tly/aMlLZFP3fejGgNr1wOKh/KasJE635UhQlOT13iwCWwNNgFZTO', 'student', 'active', NULL, '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(81, 'Jay Mehta', 'jay.mehta@gmail.com', NULL, '$2y$12$Q5hu2hy1pQHm3AyicENTde7eFFNLlA9YoXPalf.7Ege/P9PZbpLG.', 'student', 'active', NULL, '2026-07-06 06:54:02', '2026-07-06 06:54:02'),
(82, 'Darshan Vyas', 'darshan.vyas@gmail.com', NULL, '$2y$12$xWmEa6czQa7FX0rpwlLbq.xOFZwmQwHfgoxrr2QS83ot6rSnMj65S', 'student', 'active', NULL, '2026-07-06 06:54:03', '2026-07-06 06:54:03'),
(83, 'Neha Solanki', 'neha.solanki@gmail.com', NULL, '$2y$12$VcoTW79v2jt5e9dNzuhlYe.NvyMIEeQdSJwfACta5TJ5YKhSwhQba', 'student', 'active', NULL, '2026-07-06 06:54:03', '2026-07-06 06:54:03'),
(84, 'Jiya Desai', 'jiya.desai@gmail.com', NULL, '$2y$12$ykjpa4Zg0ugwsYjeFQC9rurdsoInCKUhNN94tJnhQim294qIGjzSW', 'student', 'active', NULL, '2026-07-06 06:54:04', '2026-07-06 06:54:04'),
(85, 'Mansi Gupta', 'mansi.gupta@gmail.com', NULL, '$2y$12$4euaygXlejM.u7mrINKGkumZh8dgtoimO6N7w4PTeS/c6I1KXvU82', 'student', 'active', NULL, '2026-07-06 06:54:04', '2026-07-06 06:54:04'),
(86, 'Payal Mehta', 'payal.mehta@gmail.com', NULL, '$2y$12$/QH5YmTdSjsuKl.Rquru3enKN005dwc8BkZla.mODy/C4BI0zuXZi', 'student', 'active', NULL, '2026-07-06 06:54:05', '2026-07-06 06:54:05'),
(87, 'Bhumi Vyas', 'bhumi.vyas@gmail.com', NULL, '$2y$12$ei7lK0kwCYovJJuvcOBNdeJ8kSSDuDhDA4pj6De53SPny/PEOSTpS', 'student', 'active', NULL, '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(88, 'Vivaan Solanki 31', 'vivaan.solanki.31@gmail.com', NULL, '$2y$12$AI6SHm9IUcHNSJ4jg8wEW.e8VQRFfExpiVMNPzzCq002LdnsUxvDO', 'student', 'active', NULL, '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(89, 'Vihaan Desai 32', 'vihaan.desai.32@gmail.com', NULL, '$2y$12$jfsOGnB/ji7G7LQ7h9nGj.tSFZydMZSe0dw0AmxZ9EzFlc9lUXVkC', 'student', 'active', NULL, '2026-07-06 06:54:06', '2026-07-06 06:54:06'),
(90, 'Sai Gupta 33', 'sai.gupta.33@gmail.com', NULL, '$2y$12$UHE1Ox7dqUusZyaELBMvGOga84HJvxGED1Ifa4JNZUSxWZajJ6aWK', 'student', 'active', NULL, '2026-07-06 06:54:07', '2026-07-06 06:54:07'),
(91, 'Ayaan Mehta 34', 'ayaan.mehta.34@gmail.com', NULL, '$2y$12$knuHIDf2sSG1FvWbN8/iLe9rOyP6L6OilkZEH8zeKOLNHcFvdFBCa', 'student', 'active', NULL, '2026-07-06 06:54:07', '2026-07-06 06:54:07'),
(92, 'Ishaan Vyas 35', 'ishaan.vyas.35@gmail.com', NULL, '$2y$12$OV/JBDz2pJmE6aGtgB29oeysymctpPi5.zwVQGNyt9n82PhPvVCNC', 'student', 'active', NULL, '2026-07-06 06:54:08', '2026-07-06 06:54:08'),
(93, 'Atharv Solanki 36', 'atharv.solanki.36@gmail.com', NULL, '$2y$12$R4Xu9JtXzQ0gAnfMihnrIOEjkZH5qPE6Fq9uIJgzYRlUArP22GrkG', 'student', 'active', NULL, '2026-07-06 06:54:08', '2026-07-06 06:54:08'),
(94, 'Kabir Desai 37', 'kabir.desai.37@gmail.com', NULL, '$2y$12$o2QNon.21bEnrHYOMHiBvuNwnKy5foe3ueYWjxvcIM3GRt./rwXoe', 'student', 'active', NULL, '2026-07-06 06:54:09', '2026-07-06 06:54:09'),
(95, 'Anay Gupta 38', 'anay.gupta.38@gmail.com', NULL, '$2y$12$bKTTljQYHWMX2F4UtnvsuO1qdta2pJwhi1mwemKhNUF9qrW/PinlO', 'student', 'active', NULL, '2026-07-06 06:54:09', '2026-07-06 06:54:09'),
(96, 'Yuvan Mehta 39', 'yuvan.mehta.39@gmail.com', NULL, '$2y$12$sBrsJs1j8pXGs22sIiTkku.Nr9/r.au2MFNfmd5/tr/MjC60blSVO', 'student', 'active', NULL, '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(97, 'Rohan Vyas 40', 'rohan.vyas.40@gmail.com', NULL, '$2y$12$M.rsLlJBGRV8eMg7dHUPg.mCmPg2WKoO1ecb4OuZHevrpjx0pJ2dm', 'student', 'active', NULL, '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(98, 'Diya Solanki 41', 'diya.solanki.41@gmail.com', NULL, '$2y$12$eCQTuUG0lXcs31TAoKhiPupIsX/YKdmmD.uA7sNrNc/YPJiFKWp9a', 'student', 'active', NULL, '2026-07-06 06:54:10', '2026-07-06 06:54:10'),
(99, 'Myra Desai 42', 'myra.desai.42@gmail.com', NULL, '$2y$12$hpO0A3c22e37knSW8et5geB/QGv3RLRKxVkPYD78NAHc0m6gAfWju', 'student', 'active', NULL, '2026-07-06 06:54:11', '2026-07-06 06:54:11'),
(100, 'Sara Gupta 43', 'sara.gupta.43@gmail.com', NULL, '$2y$12$oPvczIvqTST6HwyX6Nfbq.FW06vynsIGcGei6GgwQ8aNxiZu3IKoW', 'student', 'active', NULL, '2026-07-06 06:54:11', '2026-07-06 06:54:11'),
(101, 'Riya Mehta 44', 'riya.mehta.44@gmail.com', NULL, '$2y$12$6RcjBIxlxawtBK5xnsBwJehFeep4n4iIRKMbsKtJH3fPEaLHIiTEi', 'student', 'active', NULL, '2026-07-06 06:54:12', '2026-07-06 06:54:12'),
(102, 'Aadhya Vyas 45', 'aadhya.vyas.45@gmail.com', NULL, '$2y$12$A/NcCrfrljc2Lw8jc0Oz2O3Wk79s.hxdxdZNC6FaMGRz6cMHvkg6O', 'student', 'active', NULL, '2026-07-06 06:54:12', '2026-07-06 06:54:12'),
(103, 'Avni Solanki 46', 'avni.solanki.46@gmail.com', NULL, '$2y$12$.QLrJ9bXbDkGLv8EdUqxO.pFZqFTtPvDsCj5j84/KrafYCQfzjwWu', 'student', 'active', NULL, '2026-07-06 06:54:13', '2026-07-06 06:54:13'),
(104, 'Anvi Desai 47', 'anvi.desai.47@gmail.com', NULL, '$2y$12$jyo/kV2XBRI0lrlQGEhhjelnQPpv5RiQ1cZqWXKXZcJgR5ZC.89EO', 'student', 'active', NULL, '2026-07-06 06:54:13', '2026-07-06 06:54:13'),
(105, 'Meera Gupta 48', 'meera.gupta.48@gmail.com', NULL, '$2y$12$yhErAWYr5gvVav30zCVOMuNQxL.yC0v9gMlHfMUJ5Kg975Qx82jI.', 'student', 'active', NULL, '2026-07-06 06:54:13', '2026-07-06 06:54:13'),
(106, 'Nisha Mehta 49', 'nisha.mehta.49@gmail.com', NULL, '$2y$12$0pufaE48d0Ocx.hWxgBtkuNdpnk7OJX1BD6u..lAxGL1ZChP66fcS', 'student', 'active', NULL, '2026-07-06 06:54:14', '2026-07-06 06:54:14'),
(107, 'Sneha Vyas 50', 'sneha.vyas.50@gmail.com', NULL, '$2y$12$/b0xQ/sqbYoT9Vb.w.3XBuG2DnTNnUiiYDYBfHExz147Msmo7wx5y', 'student', 'active', NULL, '2026-07-06 06:54:15', '2026-07-06 06:54:15'),
(108, 'Payal Prajapati', 'payal.prajapati@gmail.com', NULL, '$2y$12$Y.K89m5kVzIF8z3PBqx56uF1Z32FWY1XeS49cguiEm8PdDTJBskfC', 'student', 'active', NULL, '2026-07-06 06:54:15', '2026-07-06 06:54:15'),
(109, 'Bhumi Pandya', 'bhumi.pandya@gmail.com', NULL, '$2y$12$/pCgkZngcV12EbRNtG7i3ufNblvmKjJOxafeDEAtnXgMfbTEQdclC', 'student', 'active', NULL, '2026-07-06 06:54:16', '2026-07-06 06:54:16'),
(110, 'Vivaan Joshi', 'vivaan.joshi@gmail.com', NULL, '$2y$12$Sje.bIQ/dvBqiAjoQpC89.902f25AmGYi6Lz7pB92xe7BVdL960zW', 'student', 'active', NULL, '2026-07-06 06:54:16', '2026-07-06 06:54:16'),
(111, 'Vihaan Verma', 'vihaan.verma@gmail.com', NULL, '$2y$12$T9ulHgyvWPnnUmM67ZTOHu05zCxRLylmtpCMCnWvkhl6Y.KEGDaq2', 'student', 'active', NULL, '2026-07-06 06:54:17', '2026-07-06 06:54:17'),
(112, 'Sai Sharma', 'sai.sharma@gmail.com', NULL, '$2y$12$EQWwEPZdNaW2FiHKGBH0XOVNg5fbB0k3RwteOP4NlDzWeXV3UPefW', 'student', 'active', NULL, '2026-07-06 06:54:17', '2026-07-06 06:54:17'),
(113, 'Ayaan Prajapati', 'ayaan.prajapati@gmail.com', NULL, '$2y$12$zkNPS27qcZBGbLvO0YQF9.kFJ8h5O23.2h3yMFBqOLER0o3Xy0b26', 'student', 'active', NULL, '2026-07-06 06:54:18', '2026-07-06 06:54:18'),
(114, 'Ishaan Pandya', 'ishaan.pandya@gmail.com', NULL, '$2y$12$j2.gz6jubVWe3F97LSwqXuyz44bCDaQeUyDio6sj20mUZB2zrLWmq', 'student', 'active', NULL, '2026-07-06 06:54:18', '2026-07-06 06:54:18'),
(115, 'Atharv Joshi', 'atharv.joshi@gmail.com', NULL, '$2y$12$AZnKxscAHn2WMGYgRbOW9.Ry4ZunpXUB1kDgkC5lK/DIeO/g1qO8e', 'student', 'active', NULL, '2026-07-06 06:54:19', '2026-07-06 06:54:19'),
(116, 'Kabir Verma', 'kabir.verma@gmail.com', NULL, '$2y$12$48nJ481VlfMpPzlplRh2auohiOe1./iHtuAWbqXgU6bdozHtrrQCy', 'student', 'active', NULL, '2026-07-06 06:54:19', '2026-07-06 06:54:19'),
(117, 'Anay Sharma', 'anay.sharma@gmail.com', NULL, '$2y$12$JTt53vIMBtTLRHp8WI8P5uKv1VjQdc5BeyZE5g2rSOlIWUqeuKLYm', 'student', 'active', NULL, '2026-07-06 06:54:20', '2026-07-06 06:54:20'),
(118, 'Yuvan Prajapati', 'yuvan.prajapati@gmail.com', NULL, '$2y$12$s/rE2teEBnUhf2o5.keVCej2DfPqsLG2bl2x8lyX1qp5b1f.USKuG', 'student', 'active', NULL, '2026-07-06 06:54:20', '2026-07-06 06:54:20'),
(119, 'Rohan Pandya', 'rohan.pandya@gmail.com', NULL, '$2y$12$DBE7qPsuzZJQIo/dxRi/4.w8TIrn296Q5ncqvmvWstCRkWKwpT6kG', 'student', 'active', NULL, '2026-07-06 06:54:21', '2026-07-06 06:54:21'),
(120, 'Diya Joshi', 'diya.joshi@gmail.com', NULL, '$2y$12$tbOwqshi2c3X7jVZ2jpGwuNvinjRvIFbrtQSXFznRzWXQz7bxDJfq', 'student', 'active', NULL, '2026-07-06 06:54:21', '2026-07-06 06:54:21'),
(121, 'Myra Verma', 'myra.verma@gmail.com', NULL, '$2y$12$jaQYPnfp5MjEPgCu1vGYOOt69hy9dgeMzhosDD1h03a6s8NQ.45qC', 'student', 'active', NULL, '2026-07-06 06:54:22', '2026-07-06 06:54:22'),
(122, 'Sara Sharma', 'sara.sharma@gmail.com', NULL, '$2y$12$EFVnUgBSPhdQjiRqjWb1OOdWO0iRX09Jph9/Zeq8D4Gp4G3eW1Aky', 'student', 'active', NULL, '2026-07-06 06:54:23', '2026-07-06 06:54:23'),
(123, 'Riya Prajapati', 'riya.prajapati@gmail.com', NULL, '$2y$12$KiJllkhgQw4XfYQ4VlocEeWIzmL8JE2JcCWyDY51PejxYoNCZOXB2', 'student', 'active', NULL, '2026-07-06 06:54:24', '2026-07-06 06:54:24'),
(124, 'Aadhya Pandya', 'aadhya.pandya@gmail.com', NULL, '$2y$12$N9U6dOIvQ.MkuTlLw.tj5ehjsu.QICT0owSQGkt5oizx62kEYrT5e', 'student', 'active', NULL, '2026-07-06 06:54:25', '2026-07-06 06:54:25'),
(125, 'Avni Joshi', 'avni.joshi@gmail.com', NULL, '$2y$12$HNjgDrOd187FQRB5ob.Mf.Xx8oBkKlmP8QRjTQsTXMl83qqsNGMEm', 'student', 'active', NULL, '2026-07-06 06:54:26', '2026-07-06 06:54:26'),
(126, 'Anvi Verma', 'anvi.verma@gmail.com', NULL, '$2y$12$IJSGYk2tvxL17WMCpGelHe.yxYV4WwYtKUR./9.YahUEd5gRzbTjK', 'student', 'active', NULL, '2026-07-06 06:54:26', '2026-07-06 06:54:26'),
(127, 'Meera Sharma', 'meera.sharma@gmail.com', NULL, '$2y$12$/bE0H44B17QIWzDJDyF5yeb7ZunLapNzMkcCQtNkPtu8K/p1Ach7K', 'student', 'active', NULL, '2026-07-06 06:54:27', '2026-07-06 06:54:27'),
(128, 'Nisha Prajapati', 'nisha.prajapati@gmail.com', NULL, '$2y$12$5FhTS89UB2TSLwF8a2FMS.1MBoGh5TuUbwSwCREDnY2yNDjNXbBES', 'student', 'active', NULL, '2026-07-06 06:54:27', '2026-07-06 06:54:27'),
(129, 'Sneha Pandya', 'sneha.pandya@gmail.com', NULL, '$2y$12$WnKH72hNZz001504jjYO9OxrSh2dQU/3O9PM5Q4Az.sum74NiGpty', 'student', 'active', NULL, '2026-07-06 06:54:28', '2026-07-06 06:54:28'),
(130, 'Karan Joshi', 'karan.joshi@gmail.com', NULL, '$2y$12$og90JR8cpdpR9SQ.4NIvt.u/yHq2wQUKPu9ABoNgqKuisKog7wUFG', 'student', 'active', NULL, '2026-07-06 06:54:28', '2026-07-06 06:54:28'),
(131, 'Harsh Verma', 'harsh.verma@gmail.com', NULL, '$2y$12$nKpgqkiE82xOGkgcSGfk7O0hqPQUGFXXb.NEaXyBPrKxVNJoFc6cC', 'student', 'active', NULL, '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(132, 'Yash Sharma', 'yash.sharma@gmail.com', NULL, '$2y$12$O.vsjhoBOFQztwSMDHRH3Ofj0mIKOYZ1uZRY4/EKh0JOFLthVwL.a', 'student', 'active', NULL, '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(133, 'Jay Prajapati', 'jay.prajapati@gmail.com', NULL, '$2y$12$NFbgcTQMa/a5tuLPhFzspuPa92rPDFD7JHoPC9gJ38npSgFq1QAVO', 'student', 'active', NULL, '2026-07-06 06:54:29', '2026-07-06 06:54:29'),
(134, 'Darshan Pandya', 'darshan.pandya@gmail.com', NULL, '$2y$12$iKpwPyadSMtR4FzhxwU4Z.yw7Stqcsm.oSq6Q4fUvWkPnIhuQdgii', 'student', 'active', NULL, '2026-07-06 06:54:30', '2026-07-06 06:54:30'),
(135, 'Neha Joshi', 'neha.joshi@gmail.com', NULL, '$2y$12$Eq1Rl.GOermdlIV5wKu2xOjDKzjRHWt0IzfcdtKYgtobAtl.4AVSy', 'student', 'active', NULL, '2026-07-06 06:54:30', '2026-07-06 06:54:30'),
(136, 'Jiya Verma', 'jiya.verma@gmail.com', NULL, '$2y$12$735zH5B3232Mv6m6jDSKHetG/.ngdI.c/jkB2GAd5mtecW7/oUczG', 'student', 'active', NULL, '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(137, 'Mansi Sharma', 'mansi.sharma@gmail.com', NULL, '$2y$12$CpraFbuQZhaIjBbqKFAWHe9KFiQXaq9hUH2.KfZyokAXCFvKuH.WO', 'student', 'active', NULL, '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(138, 'Payal Prajapati 31', 'payal.prajapati.31@gmail.com', NULL, '$2y$12$56.aCNRJvxzN8P.5awSGEOqAL5aU5YECdSzspxIOyHgCVfVA25fJa', 'student', 'active', NULL, '2026-07-06 06:54:31', '2026-07-06 06:54:31'),
(139, 'Bhumi Pandya 32', 'bhumi.pandya.32@gmail.com', NULL, '$2y$12$PLjSPBwcMuxJ82vOFzGxqeTsm.R9iGa8JooIkBdzJhqslVpt1W952', 'student', 'active', NULL, '2026-07-06 06:54:32', '2026-07-06 06:54:32'),
(140, 'Vivaan Joshi 33', 'vivaan.joshi.33@gmail.com', NULL, '$2y$12$bA5oqt5F4aozms.zx9KsM.191JJjsqLVwIbeyzCQCMGzrFpxxAIRO', 'student', 'active', NULL, '2026-07-06 06:54:32', '2026-07-06 06:54:32'),
(141, 'Vihaan Verma 34', 'vihaan.verma.34@gmail.com', NULL, '$2y$12$chy9vkUConaWT2.tPOfk3OOjt2KAC6M5ZN4IOzDW8QGo9wbSOIS5W', 'student', 'active', NULL, '2026-07-06 06:54:33', '2026-07-06 06:54:33'),
(142, 'Sai Sharma 35', 'sai.sharma.35@gmail.com', NULL, '$2y$12$VyVP/rTq3cvYae1Bzwiq8OHRkrPFsYwTnfvmoq3EV4WJ5lcOPwR.6', 'student', 'active', NULL, '2026-07-06 06:54:34', '2026-07-06 06:54:34'),
(143, 'Ayaan Prajapati 36', 'ayaan.prajapati.36@gmail.com', NULL, '$2y$12$u0wJThnsOdycrEhHJL1Go.94rypIL9mSb5MqaP3Hm0rHoW29WEr/S', 'student', 'active', NULL, '2026-07-06 06:54:34', '2026-07-06 06:54:34'),
(144, 'Ishaan Pandya 37', 'ishaan.pandya.37@gmail.com', NULL, '$2y$12$cUpGfOJVAMW5Wb79Zx/2aeIQm2Sku2/cdQ0mTv.bVkx0Kj15/Wmq.', 'student', 'active', NULL, '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(145, 'Atharv Joshi 38', 'atharv.joshi.38@gmail.com', NULL, '$2y$12$CMfRaX77MD1P2ycLbDggCupuKq2SKCXL0vkBvvQp22zQPnnIrwtpO', 'student', 'active', NULL, '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(146, 'Kabir Verma 39', 'kabir.verma.39@gmail.com', NULL, '$2y$12$5Fxw1m6Vr7mfXtnNkc/CSeimewFI89jrtgTfKIdnJAIt1AJaUFAlq', 'student', 'active', NULL, '2026-07-06 06:54:35', '2026-07-06 06:54:35'),
(147, 'Anay Sharma 40', 'anay.sharma.40@gmail.com', NULL, '$2y$12$qNOgohOCvCLORt0QeZd3MuaavLq9TLnvwhupyOmNf2ouO7JBX80tW', 'student', 'active', NULL, '2026-07-06 06:54:36', '2026-07-06 06:54:36'),
(148, 'Yuvan Prajapati 41', 'yuvan.prajapati.41@gmail.com', NULL, '$2y$12$F4p44KrVbKpgWmsbPujCiOkw1INy/b0j8V2Qb/AxV8DxFEkWymsLO', 'student', 'active', NULL, '2026-07-06 06:54:36', '2026-07-06 06:54:36'),
(149, 'Rohan Pandya 42', 'rohan.pandya.42@gmail.com', NULL, '$2y$12$E.JJb8XlgaJHyqaV4UO7leVku.nU2ZvrSsGA.qoZkV9y2ZzwcOM.a', 'student', 'active', NULL, '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(150, 'Diya Joshi 43', 'diya.joshi.43@gmail.com', NULL, '$2y$12$QybJRBIq98AKgpbrV8IrnuSI3fQyigL0dKUCH7NbCb/tGsSiNQc7K', 'student', 'active', NULL, '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(151, 'Myra Verma 44', 'myra.verma.44@gmail.com', NULL, '$2y$12$NOS0AzKre0VthUeNeZgSOezpZIPtzxA/IHSFjO1HdAorGpnOaPH/O', 'student', 'active', NULL, '2026-07-06 06:54:37', '2026-07-06 06:54:37'),
(152, 'Sara Sharma 45', 'sara.sharma.45@gmail.com', NULL, '$2y$12$0JoIMxlf/NA4mo/J1Pv1BuKzYAtyizb89bjQ3sqPkL/7Y.d9w/2gK', 'student', 'active', NULL, '2026-07-06 06:54:38', '2026-07-06 06:54:38'),
(153, 'Riya Prajapati 46', 'riya.prajapati.46@gmail.com', NULL, '$2y$12$DqmBSzW/sH6AyqKCJwnWw.CVtZBdRq9aPWygEtIR9ZWFWG9eVb556', 'student', 'active', NULL, '2026-07-06 06:54:38', '2026-07-06 06:54:38'),
(154, 'Aadhya Pandya 47', 'aadhya.pandya.47@gmail.com', NULL, '$2y$12$xL9xZOJb4AzD9mu78BBMDud3FSj8CTSfuj6c.9tB7nzyHe6h3Xtou', 'student', 'active', NULL, '2026-07-06 06:54:39', '2026-07-06 06:54:39'),
(155, 'Avni Joshi 48', 'avni.joshi.48@gmail.com', NULL, '$2y$12$JtViysUfPxp/MgS2FOYeUuXkuWCYmXIBGWhQhaaY8ZvdkLMuPUSki', 'student', 'active', NULL, '2026-07-06 06:54:39', '2026-07-06 06:54:39'),
(156, 'Anvi Verma 49', 'anvi.verma.49@gmail.com', NULL, '$2y$12$Hx3SdEU1ObCs/im70OWqLe.NvMQH2Sc7cUDmNB6PhRbVDyLWaPSxq', 'student', 'active', NULL, '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(157, 'Meera Sharma 50', 'meera.sharma.50@gmail.com', NULL, '$2y$12$ihWGVTkHmEdnlxi8jqW2cOClH73P7cS2ECiOKYa1DD3Kn3hhJAhgy', 'student', 'active', NULL, '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(158, 'Krishna Solanki', 'krishna.solanki@gmail.com', NULL, '$2y$12$4lOwLPy5EcJ4voO4EkC9Qu57svx5KyxP0XNl/eLfVrS1OWguvdUvy', 'student', 'active', NULL, '2026-07-06 06:54:40', '2026-07-06 06:54:40'),
(159, 'Shaurya Desai', 'shaurya.desai@gmail.com', NULL, '$2y$12$sNyCcYRsMWwuES1xL0eQ0eNkRbAvZZuwxjbiwzr17YY9zdTSgPnFK', 'student', 'active', NULL, '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(160, 'Dhruv Gupta', 'dhruv.gupta@gmail.com', NULL, '$2y$12$H.SD3GAAGhMY3v2Gzigj3O8vHjySvM9Q0GW4uBCgtoUNByy3R3eHO', 'student', 'active', NULL, '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(161, 'Rudra Mehta', 'rudra.mehta@gmail.com', NULL, '$2y$12$XyfuSPLbKLrD91N3VbyR7O9hemcB8cFFFEHYBEyM/SL9oQWNGa2l6', 'student', 'active', NULL, '2026-07-06 06:54:41', '2026-07-06 06:54:41'),
(162, 'Dev Vyas', 'dev.vyas@gmail.com', NULL, '$2y$12$KHbRc9Da3177e/d0fPB/8Omnimkafy0RV4IBaBoHW2P8ej8u1vSg.', 'student', 'active', NULL, '2026-07-06 06:54:42', '2026-07-06 06:54:42'),
(163, 'Parth Solanki', 'parth.solanki@gmail.com', NULL, '$2y$12$xgTHSPS1/U2aknrJk.dpbuZ4zSaEOmA1T4/P541KvVU8LZs/1HyyC', 'student', 'active', NULL, '2026-07-06 06:54:42', '2026-07-06 06:54:42'),
(164, 'Aanya Desai', 'aanya.desai@gmail.com', NULL, '$2y$12$OHuqpzJ0vCwtm0Z2jqGn4eDLMha4dFiCFaSE/x2jubACatnY7IHEi', 'student', 'active', NULL, '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(165, 'Ira Gupta', 'ira.gupta@gmail.com', NULL, '$2y$12$OTXT4UlAaNttXDfGUZOxcu58wLgZ0rK9ZHoc2d5oqxQAN1SiqidTu', 'student', 'active', NULL, '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(166, 'Anika Mehta', 'anika.mehta@gmail.com', NULL, '$2y$12$OFnF.B.YqwRCFSBAlZORP.KO3N3q6oPJlyySnDpWdSlzxFtdlmrVC', 'student', 'active', NULL, '2026-07-06 06:54:43', '2026-07-06 06:54:43'),
(167, 'Kiara Vyas', 'kiara.vyas@gmail.com', NULL, '$2y$12$1de/7v5hwUImjpyC76zn3uhPSRee/g9b3F5W1u5sLvFfYJtKErmHG', 'student', 'active', NULL, '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(168, 'Navya Solanki', 'navya.solanki@gmail.com', NULL, '$2y$12$ehfqjJQyiWRlbndpEK7kxe/M9y1EmptrXUhrSlznhJZzuOdO7q6n6', 'student', 'active', NULL, '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(169, 'Prisha Desai', 'prisha.desai@gmail.com', NULL, '$2y$12$PWTC1jvVRletfQXejXaXHe76Nud8HppAXAq3sVcZSNZkO/QTHFHpC', 'student', 'active', NULL, '2026-07-06 06:54:44', '2026-07-06 06:54:44'),
(170, 'Ishita Gupta', 'ishita.gupta@gmail.com', NULL, '$2y$12$/ZMyZwhVlcE./uXWis7JgO3IPGdH/W21QEAeTX5IBY3.gvQHvqBq6', 'student', 'active', NULL, '2026-07-06 06:54:45', '2026-07-06 06:54:45'),
(171, 'Tara Mehta', 'tara.mehta@gmail.com', NULL, '$2y$12$joXmkv0tLDFUyNBKropRWuWZbX7GgVJNBddB5pJwgyv86Yqgt2al.', 'student', 'active', NULL, '2026-07-06 06:54:45', '2026-07-06 06:54:45'),
(172, 'Kavya Vyas', 'kavya.vyas@gmail.com', NULL, '$2y$12$O5.EdmHVtkpRjzs5AKr1temGw7vHoiU8NZbe2bfXwHqZmAzAitxKW', 'student', 'active', NULL, '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(173, 'Pooja Solanki', 'pooja.solanki@gmail.com', NULL, '$2y$12$x6uL6fo39JFiWhD34y0xle9VxjiWDGozmefqx3g1BJ7hI8oMKOCH.', 'student', 'active', NULL, '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(174, 'Rahul Desai', 'rahul.desai@gmail.com', NULL, '$2y$12$XJSEyOl4FjhHO2g9NoLPGekkPaRtA2ms8.U4PshW72ewCVXc4Aupm', 'student', 'active', NULL, '2026-07-06 06:54:46', '2026-07-06 06:54:46'),
(175, 'Nikhil Gupta', 'nikhil.gupta@gmail.com', NULL, '$2y$12$HkVpvl5fvA1bQt9ttH7FtekOKVo3kmgnM920A4b8QZqF1Z42Vm/1m', 'student', 'active', NULL, '2026-07-06 06:54:47', '2026-07-06 06:54:47'),
(176, 'Manav Mehta', 'manav.mehta@gmail.com', NULL, '$2y$12$AboDl3akQHYZ.okK23ddxOfLinQKAqyibipqRyHlV5chjOtEti/EO', 'student', 'active', NULL, '2026-07-06 06:54:47', '2026-07-06 06:54:47'),
(177, 'Meet Vyas', 'meet.vyas@gmail.com', NULL, '$2y$12$UsR.rxYef4N00D0YPPKXse.5WNBGmah08/y9Ka6PTpaTJLn.MKAHO', 'student', 'active', NULL, '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(178, 'Om Solanki', 'om.solanki@gmail.com', NULL, '$2y$12$aOI9M/NE8Jh.Oxkqc..1YO1ovJ6S5SkdIvh4vIb/w6bek58eCb./6', 'student', 'active', NULL, '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(179, 'Priya Desai', 'priya.desai@gmail.com', NULL, '$2y$12$dYdVIVrfPN4Ez0W0fMj3xuYpqoce8ah3n4FquD.gVMCY2.XMxPR.a', 'student', 'active', NULL, '2026-07-06 06:54:48', '2026-07-06 06:54:48'),
(180, 'Hetal Gupta', 'hetal.gupta@gmail.com', NULL, '$2y$12$z06M7WYCOqoJKyx5iaBeyOdxr6/9iUjsyzlZcLJmpQU7h6PyNx02S', 'student', 'active', NULL, '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(181, 'Khushi Mehta', 'khushi.mehta@gmail.com', NULL, '$2y$12$VS1A3/0./T6rz6xQTqO9NOd5PanBk0iNbLKFTtzLuWPiBVegPjcXa', 'student', 'active', NULL, '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(182, 'Rutu Vyas', 'rutu.vyas@gmail.com', NULL, '$2y$12$XDLef7ySfFzGBNHCyBEMf.XOi/HIQNxHLI3DgLX0SNPvOme8w9u5W', 'student', 'active', NULL, '2026-07-06 06:54:49', '2026-07-06 06:54:49'),
(183, 'Krupa Solanki', 'krupa.solanki@gmail.com', NULL, '$2y$12$b.JOmk5izgHsG5m8NzQAKeQ0lH0IFUqvWuJ2GwiUzfQ/BLb1frsv.', 'student', 'active', NULL, '2026-07-06 06:54:50', '2026-07-06 06:54:50'),
(184, 'Aarav Desai', 'aarav.desai@gmail.com', NULL, '$2y$12$cPuy9e3nWMfRdVyyayRxruFq688AJh.3yA3dyY.CkcJx3lzBSsR1S', 'student', 'active', NULL, '2026-07-06 06:54:50', '2026-07-06 06:54:50'),
(185, 'Aditya Gupta', 'aditya.gupta@gmail.com', NULL, '$2y$12$2iRBJgtAJ6MtOp8wBaSA0O9Sabm1GpvObw01ugUtCgWtguTWllfdC', 'student', 'active', NULL, '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(186, 'Arjun Mehta', 'arjun.mehta@gmail.com', NULL, '$2y$12$DIpJsYIGXmr3R5QTktq0c.BixTzOxlPwW7zPReYmcDagfgUS4Tlty', 'student', 'active', NULL, '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(187, 'Reyansh Vyas', 'reyansh.vyas@gmail.com', NULL, '$2y$12$ka3E5bjpLvzpfvZJDX5dxeUMC4ISw2mg/RSDAvGEcJTNSGwVNbQWa', 'student', 'active', NULL, '2026-07-06 06:54:51', '2026-07-06 06:54:51'),
(188, 'Krishna Solanki 31', 'krishna.solanki.31@gmail.com', NULL, '$2y$12$ZFyZSmU9QIGjLpMijWYpD.1Aqqz5.38YjIPOFXwldUV9wcbtpnnqu', 'student', 'active', NULL, '2026-07-06 06:54:52', '2026-07-06 06:54:52'),
(189, 'Shaurya Desai 32', 'shaurya.desai.32@gmail.com', NULL, '$2y$12$xpZwOt1Qm76oJRyfUV78jucd6lJ8wCri921PQ9Sh8fOZvAldwvABG', 'student', 'active', NULL, '2026-07-06 06:54:52', '2026-07-06 06:54:52'),
(190, 'Dhruv Gupta 33', 'dhruv.gupta.33@gmail.com', NULL, '$2y$12$qAsnorkh9F3DXMC.2CtDaOFBcnAXr9rGyXMjp2V1piEbL06kNOcIK', 'student', 'active', NULL, '2026-07-06 06:54:53', '2026-07-06 06:54:53'),
(191, 'Rudra Mehta 34', 'rudra.mehta.34@gmail.com', NULL, '$2y$12$J/TjyIpXSAQuQRjlyWT0Nu/ZH82x7JdEjjeHZRZ8bI1ezrwPn0KjS', 'student', 'active', NULL, '2026-07-06 06:54:53', '2026-07-06 06:54:53'),
(192, 'Dev Vyas 35', 'dev.vyas.35@gmail.com', NULL, '$2y$12$NhJ5E.8QJhvggWJVOqIOseuINg0gRsislIafiC/ZwHPoNKMezcYp6', 'student', 'active', NULL, '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(193, 'Parth Solanki 36', 'parth.solanki.36@gmail.com', NULL, '$2y$12$wskSv02BlVpgiB1APSNM8ujxiPdY6UBd4kRRWh4emMREI0tvqsSk.', 'student', 'active', NULL, '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(194, 'Aanya Desai 37', 'aanya.desai.37@gmail.com', NULL, '$2y$12$LZNJ6yf5uZG5nMr3Oyzq7eb4yJT8ceJH7hDntqMSpw9z.whvwR.uq', 'student', 'active', NULL, '2026-07-06 06:54:54', '2026-07-06 06:54:54'),
(195, 'Ira Gupta 38', 'ira.gupta.38@gmail.com', NULL, '$2y$12$o/fJRh7DqR1xHJvMceaUMeIRd2e9xrHWBIZVNGIgFQHBe8nEgbjNu', 'student', 'active', NULL, '2026-07-06 06:54:55', '2026-07-06 06:54:55'),
(196, 'Anika Mehta 39', 'anika.mehta.39@gmail.com', NULL, '$2y$12$qcD5s7wMWmW0FTUR/weqneLWH1OW37bmxyjMHnYPwWOmfp4A/60JO', 'student', 'active', NULL, '2026-07-06 06:54:55', '2026-07-06 06:54:55'),
(197, 'Kiara Vyas 40', 'kiara.vyas.40@gmail.com', NULL, '$2y$12$gBiWOYrMDbo0v8VOI8xGLuOfER5922.UbivH87awdMXwDXwQp6yb.', 'student', 'active', NULL, '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(198, 'Navya Solanki 41', 'navya.solanki.41@gmail.com', NULL, '$2y$12$iMIvFQ233VAkJohWU.uck.RWSNuhaOK1JblfJzE3YJ2GH8VFJyMpq', 'student', 'active', NULL, '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(199, 'Prisha Desai 42', 'prisha.desai.42@gmail.com', NULL, '$2y$12$OnZFBo5NUa2Y2VjyAbngUur663ny.lwm/XF00BZRm22Ktdq9vPNCe', 'student', 'active', NULL, '2026-07-06 06:54:56', '2026-07-06 06:54:56'),
(200, 'Ishita Gupta 43', 'ishita.gupta.43@gmail.com', NULL, '$2y$12$T7SbQMBybQVjeAj8zfVdAOtYp7XV6/4oW1Ym7gSmpxZ9GwFvLRbu2', 'student', 'active', NULL, '2026-07-06 06:54:57', '2026-07-06 06:54:57'),
(201, 'Tara Mehta 44', 'tara.mehta.44@gmail.com', NULL, '$2y$12$c045j/Ok46lJ2fXU5WMSRu.vmKfKxDe2gv/uKgGOjvFT9eVUI7vU2', 'student', 'active', NULL, '2026-07-06 06:54:57', '2026-07-06 06:54:57'),
(202, 'Kavya Vyas 45', 'kavya.vyas.45@gmail.com', NULL, '$2y$12$XpniWFGNthzWVziLZpGJy.3hGslE5MLzBH3EPtyctrdjLO6SVeuVO', 'student', 'active', NULL, '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(203, 'Pooja Solanki 46', 'pooja.solanki.46@gmail.com', NULL, '$2y$12$6gV6aSr6LRg.Skwm7HCcweJlvBdsK8wIScMDu.Xjl0dXeKA5tZtG6', 'student', 'active', NULL, '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(204, 'Rahul Desai 47', 'rahul.desai.47@gmail.com', NULL, '$2y$12$WrOGDpl2/9BNPNNczJ7FNuRfNxWhr7JHbP8T9J03I8Y1Z06nEHntO', 'student', 'active', NULL, '2026-07-06 06:54:58', '2026-07-06 06:54:58'),
(205, 'Nikhil Gupta 48', 'nikhil.gupta.48@gmail.com', NULL, '$2y$12$mWclEsPDDdOovKY3IE9onulA.3ZqamLGytmuoG01QzBufjj7OceK6', 'student', 'active', NULL, '2026-07-06 06:54:59', '2026-07-06 06:54:59'),
(206, 'Manav Mehta 49', 'manav.mehta.49@gmail.com', NULL, '$2y$12$E0/gybtvilJ6LnFhrr.4lehWoZloROZHiK3xCeLykIFL2U4ETR97C', 'student', 'active', NULL, '2026-07-06 06:54:59', '2026-07-06 06:54:59'),
(207, 'Meet Vyas 50', 'meet.vyas.50@gmail.com', NULL, '$2y$12$5jID/NSHmoTTwps8acs1PewYdfUb5THmkXxHT4SzZfjKPyyg7w7Yu', 'student', 'active', NULL, '2026-07-06 06:54:59', '2026-07-06 06:54:59');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculties`
--
ALTER TABLE `faculties`
  ADD CONSTRAINT `faculties_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `faculties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_assignments`
--
ALTER TABLE `faculty_assignments`
  ADD CONSTRAINT `faculty_assignments_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_assignments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_department`
--
ALTER TABLE `faculty_department`
  ADD CONSTRAINT `faculty_department_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_department_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_leader_student_id_foreign` FOREIGN KEY (`leader_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_reviews`
--
ALTER TABLE `project_reviews`
  ADD CONSTRAINT `project_reviews_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_reviews_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_submissions`
--
ALTER TABLE `project_submissions`
  ADD CONSTRAINT `project_submissions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
