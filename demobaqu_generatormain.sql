-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 07, 2025 at 02:20 PM
-- Server version: 10.6.21-MariaDB-cll-lve-log
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demobaqu_generatormain`
--

-- --------------------------------------------------------

--
-- Table structure for table `demon_audit_logs`
--

CREATE TABLE `demon_audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'e.g. user.login, user.password_reset',
  `details` text DEFAULT NULL COMMENT 'JSON or human-readable explanation',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_audit_logs`
--

INSERT INTO `demon_audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'user.created', 'Initial admin account created', '127.0.0.1', '2025-06-03 20:08:07'),
(2, 1, 'user.bulk_activate', '{\"user_ids\":[26],\"count\":1}', '95.146.14.61', '2025-06-04 15:01:51'),
(3, 1, 'user.bulk_activate', '{\"user_ids\":[23],\"count\":1}', '95.146.14.61', '2025-06-04 15:02:03'),
(4, 1, 'user.bulk_ban', '{\"user_ids\":[23],\"count\":1}', '95.146.14.61', '2025-06-04 15:02:07'),
(5, 1, 'user.create', '{\"new_user_id\":27,\"username\":\"supa\",\"email\":\"testiu@gmail.com\",\"role_id\":7}', '95.146.14.61', '2025-06-04 15:14:50'),
(6, 1, 'user.create', '{\"new_user_id\":28,\"username\":\"supasupa\",\"email\":\"supasupa@gmail.com\",\"role_id\":4}', '95.146.14.61', '2025-06-04 15:15:22'),
(7, 1, 'user.bulk_activate', '{\"user_ids\":[27],\"count\":1}', '95.146.14.61', '2025-06-04 15:15:33'),
(8, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 15:30:07'),
(9, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 15:31:08'),
(10, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 15:31:52'),
(11, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 15:34:12'),
(12, NULL, 'user.password_reset', '{\"user_id\":\"22\"}', '95.146.14.61', '2025-06-04 15:34:22'),
(13, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 15:35:11'),
(14, 1, 'user.bulk_delete', '{\"user_ids\":[22],\"count\":1}', '95.146.14.61', '2025-06-04 15:35:16'),
(15, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 15:35:19'),
(16, NULL, 'user.register', '{\"username\":\"test\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-04 15:35:48'),
(17, NULL, 'user.activate_success', '{\"user_id\":\"29\"}', '95.146.14.61', '2025-06-04 15:36:07'),
(18, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 15:36:25'),
(20, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 18:03:23'),
(21, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 18:03:29'),
(22, NULL, 'user.register', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-04 21:27:20'),
(23, NULL, 'user.activate_success', '{\"user_id\":\"30\"}', '95.146.14.61', '2025-06-04 21:27:42'),
(24, NULL, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-04 21:27:52'),
(25, 1, 'user.bulk_delete', '{\"user_ids\":[30],\"count\":1}', '95.146.14.61', '2025-06-04 21:32:43'),
(28, 31, 'user.register', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-04 21:33:40'),
(29, 31, 'user.activate_resend', '{\"user_id\":\"31\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-04 21:34:16'),
(30, 31, 'user.activate_success', '{\"user_id\":\"31\"}', '95.146.14.61', '2025-06-04 21:34:35'),
(31, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-04 21:34:44'),
(32, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 22:22:50'),
(33, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 22:31:49'),
(34, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 22:31:56'),
(35, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 23:27:10'),
(36, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 23:27:22'),
(37, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 23:32:19'),
(38, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 23:32:30'),
(39, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-04 23:34:47'),
(40, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-04 23:34:54'),
(41, NULL, 'user.register', '{\"username\":\"shidouri\",\"email\":\"shiddydev@gmail.com\"}', '188.29.111.104', '2025-06-05 00:03:04'),
(42, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 00:09:26'),
(43, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 00:09:37'),
(44, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 00:13:34'),
(45, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 00:16:42'),
(46, 33, 'user.register', '{\"username\":\"shiddy\",\"email\":\"shidouridazzle@gmail.com\"}', '188.29.111.104', '2025-06-05 00:27:04'),
(47, 33, 'user.activate_failed', '{\"user_id\":\"33\",\"reason\":\"invalid_code\",\"code_entered\":\"249342\"}', '188.29.111.104', '2025-06-05 00:27:50'),
(48, 33, 'user.activate_failed', '{\"user_id\":\"33\",\"reason\":\"code_expired\"}', '188.29.111.104', '2025-06-05 00:28:08'),
(49, 33, 'user.activate_resend', '{\"user_id\":\"33\",\"email\":\"shidouridazzle@gmail.com\"}', '188.29.111.104', '2025-06-05 00:28:13'),
(50, 33, 'user.activate_resend', '{\"user_id\":\"33\",\"email\":\"shidouridazzle@gmail.com\"}', '188.29.111.104', '2025-06-05 00:28:27'),
(51, 33, 'user.activate_success', '{\"user_id\":\"33\"}', '188.29.111.104', '2025-06-05 00:28:50'),
(52, 33, 'user.login', '{\"username\":\"shiddy\",\"email\":\"shidouridazzle@gmail.com\"}', '188.29.111.104', '2025-06-05 00:29:03'),
(53, 1, 'user.create', '{\"new_user_id\":34,\"username\":\"TestingAccount\",\"email\":\"TestingAccount@gmail.com\",\"role_id\":1}', '95.146.14.61', '2025-06-05 00:51:01'),
(54, 34, 'user.login', '{\"username\":\"TestingAccount\",\"email\":\"TestingAccount@gmail.com\"}', '95.146.14.61', '2025-06-05 00:51:08'),
(55, 31, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 00:54:36'),
(56, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-05 00:54:41'),
(57, 31, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 03:20:49'),
(58, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-05 03:20:53'),
(59, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 09:15:24'),
(60, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 09:15:32'),
(61, 35, 'user.register', '{\"username\":\"Simpy\",\"email\":\"joker32789@gmail.com\"}', '68.229.23.143', '2025-06-05 09:21:35'),
(62, 35, 'user.activate_failed', '{\"user_id\":\"35\",\"reason\":\"code_expired\"}', '68.229.23.143', '2025-06-05 09:22:16'),
(63, 35, 'user.activate_resend', '{\"user_id\":\"35\",\"email\":\"joker32789@gmail.com\"}', '68.229.23.143', '2025-06-05 09:22:26'),
(64, 35, 'user.activate_success', '{\"user_id\":\"35\"}', '68.229.23.143', '2025-06-05 09:22:51'),
(65, 35, 'user.login', '{\"username\":\"Simpy\",\"email\":\"joker32789@gmail.com\"}', '68.229.23.143', '2025-06-05 09:23:01'),
(66, 35, 'user.logout', '{\"ip\":\"68.229.23.143\"}', '68.229.23.143', '2025-06-05 09:35:51'),
(67, 35, 'user.login', '{\"username\":\"Simpy\",\"email\":\"joker32789@gmail.com\"}', '68.229.23.143', '2025-06-05 09:35:58'),
(68, 1, 'user.bulk_delete', '{\"user_ids\":[32],\"count\":1}', '95.146.14.61', '2025-06-05 10:05:17'),
(69, 1, 'user.bulk_delete', '{\"user_ids\":[29,28,27,26,23],\"count\":5}', '95.146.14.61', '2025-06-05 10:05:39'),
(70, 31, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 13:19:45'),
(71, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-05 13:19:50'),
(72, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 14:35:57'),
(73, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 14:36:04'),
(74, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 15:22:18'),
(75, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 15:22:38'),
(76, 34, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 15:32:48'),
(77, NULL, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:34:43'),
(78, NULL, 'user.activate_failed', '{\"user_id\":\"36\",\"reason\":\"code_expired\"}', '95.146.14.61', '2025-06-05 15:35:01'),
(79, NULL, 'user.activate_resend', '{\"user_id\":\"36\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:35:07'),
(80, NULL, 'user.activate_success', '{\"user_id\":\"36\"}', '95.146.14.61', '2025-06-05 15:35:17'),
(81, NULL, 'user.login', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:35:29'),
(84, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 15:40:37'),
(85, NULL, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:40:49'),
(86, NULL, 'user.activate_failed', '{\"user_id\":\"37\",\"reason\":\"code_expired\"}', '95.146.14.61', '2025-06-05 15:41:04'),
(87, NULL, 'user.activate_resend', '{\"user_id\":\"37\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:41:06'),
(88, NULL, 'user.activate_resend', '{\"user_id\":\"37\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:41:11'),
(89, NULL, 'user.activate_resend', '{\"user_id\":\"37\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:41:16'),
(90, NULL, 'user.activate_success', '{\"user_id\":\"37\"}', '95.146.14.61', '2025-06-05 15:41:25'),
(91, NULL, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:43:45'),
(92, NULL, 'user.activate_resend', '{\"user_id\":\"38\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:43:54'),
(93, NULL, 'user.activate_success', '{\"user_id\":\"38\"}', '95.146.14.61', '2025-06-05 15:44:03'),
(94, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 15:49:47'),
(95, NULL, 'user.register', '{\"username\":\"hcfgjxf\",\"email\":\"hthfdtgh@gmail.com\"}', '95.146.14.61', '2025-06-05 15:50:34'),
(96, NULL, 'user.register', '{\"username\":\"dfgbzdfbzxdfnbzdn\",\"email\":\"dfgbzdfbzxdfnbzdn@gmail.com\"}', '95.146.14.61', '2025-06-05 15:53:50'),
(97, 1, 'user.login', '{\"username\":\"admin\",\"email\":\"admin@example.com\"}', '95.146.14.61', '2025-06-05 15:54:23'),
(98, NULL, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:59:23'),
(99, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 15:59:58'),
(100, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:04'),
(101, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:10'),
(102, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:15'),
(103, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:20'),
(104, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:25'),
(105, NULL, 'user.activate_resend', '{\"user_id\":\"41\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:00:31'),
(106, NULL, 'user.activate_success', '{\"user_id\":\"41\"}', '95.146.14.61', '2025-06-05 16:00:41'),
(111, NULL, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:12:08'),
(112, 47, 'user.register', '{\"username\":\"Refertest1\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:22:39'),
(113, 47, 'user.activate_resend', '{\"user_id\":\"47\",\"email\":\"Refertest1@gmail.com\"}', '95.146.14.61', '2025-06-05 16:22:58'),
(114, 47, 'user.activate_success', '{\"user_id\":47}', '95.146.14.61', '2025-06-05 16:23:08'),
(115, 1, 'credits.transfer', '{\"from_user\":\"1\",\"to_user\":31,\"amount\":50}', '95.146.14.61', '2025-06-05 16:32:49'),
(116, 1, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 16:41:59'),
(117, 48, 'user.register', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 16:42:52'),
(118, 48, 'user.activate_resend', '{\"user_id\":\"48\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 16:43:08'),
(119, 48, 'user.activate_resend', '{\"user_id\":\"48\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 16:43:21'),
(120, 48, 'user.activate_success', '{\"user_id\":48}', '95.146.14.61', '2025-06-05 16:43:35'),
(121, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 16:43:43'),
(122, 48, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 16:45:01'),
(123, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 16:45:07'),
(124, 48, 'user.logout', '{\"ip\":\"95.146.14.61\"}', '95.146.14.61', '2025-06-05 18:03:28'),
(125, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 18:03:36'),
(126, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 18:48:32'),
(127, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 18:48:39'),
(128, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 18:48:42'),
(129, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 18:49:06'),
(130, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 18:59:35'),
(131, NULL, 'user.register', '{\"username\":\"testtesttesttest\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:02:39'),
(132, NULL, 'user.activate_resend', '{\"user_id\":\"49\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:03:40'),
(133, NULL, 'user.activate_success', '{\"user_id\":49}', '95.146.14.61', '2025-06-05 19:03:50'),
(134, NULL, 'user.activate_skipped', '{\"email\":\"owencroft137@gmail.com\",\"reason\":\"already_active\"}', '95.146.14.61', '2025-06-05 19:03:53'),
(135, NULL, 'user.activate_skipped', '{\"email\":\"owencroft137@gmail.com\",\"reason\":\"already_active\"}', '95.146.14.61', '2025-06-05 19:04:08'),
(136, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 19:06:24'),
(137, NULL, 'user.logout', '{\"ip\":\"89.37.63.105\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '89.37.63.105', '2025-06-05 19:20:11'),
(138, NULL, 'user.logout', '{\"ip\":\"89.37.63.105\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '89.37.63.105', '2025-06-05 19:20:18'),
(139, NULL, 'user.logout', '{\"ip\":\"217.146.82.225\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko\\/20100101 Firefox\\/139.0\"}', '217.146.82.225', '2025-06-05 19:22:38'),
(140, NULL, 'user.login_failed', '{\"credential\":\"Drax\",\"reason\":\"no such user\"}', '71.161.193.146', '2025-06-05 19:22:39'),
(141, NULL, 'user.login_failed', '{\"credential\":\"dboyington32@gmail.com\",\"reason\":\"no such user\"}', '71.161.193.146', '2025-06-05 19:23:01'),
(142, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 19:25:40'),
(143, NULL, 'user.register', '{\"username\":\"sssssssssssssssss\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:26:03'),
(144, NULL, 'user.activate_resend', '{\"user_id\":\"50\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:26:18'),
(145, NULL, 'user.activate_resend', '{\"user_id\":\"50\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:30:59'),
(146, NULL, 'user.activate_failed', '{\"user_id\":\"50\",\"reason\":\"invalid_code\",\"code_entered\":\"215214\"}', '95.146.14.61', '2025-06-05 19:31:11'),
(147, NULL, 'user.activate_resend', '{\"user_id\":\"50\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:31:13'),
(148, NULL, 'user.activate_failed', '{\"user_id\":\"50\",\"reason\":\"invalid_code\",\"code_entered\":\"178753\"}', '95.146.14.61', '2025-06-05 19:32:31'),
(149, NULL, 'user.activate_resend', '{\"user_id\":\"50\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:32:33'),
(150, NULL, 'user.activate_resend', '{\"user_id\":\"50\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:32:51'),
(151, NULL, 'user.activate_success', '{\"user_id\":50}', '95.146.14.61', '2025-06-05 19:32:59'),
(152, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 19:33:06'),
(153, 51, 'user.register', '{\"username\":\"Sti444\",\"email\":\"someonelovedie@gmail.com\"}', '178.72.88.86', '2025-06-05 19:33:08'),
(154, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 19:35:26'),
(155, 52, 'user.register', '{\"username\":\"vcdiff\",\"email\":\"resultingg@outlook.com\"}', '76.50.120.215', '2025-06-05 19:35:29'),
(156, 53, 'user.register', '{\"username\":\"owencroft137\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:35:33'),
(157, 53, 'user.activate_resend', '{\"user_id\":\"53\",\"email\":\"owencroft137@gmail.com\"}', '95.146.14.61', '2025-06-05 19:35:42'),
(158, 52, 'user.activate_failed', '{\"user_id\":\"52\",\"reason\":\"code_expired\"}', '76.50.120.215', '2025-06-05 19:35:54'),
(159, 53, 'user.activate_success', '{\"user_id\":53}', '95.146.14.61', '2025-06-05 19:35:58'),
(160, 52, 'user.activate_resend', '{\"user_id\":\"52\",\"email\":\"resultingg@outlook.com\"}', '76.50.120.215', '2025-06-05 19:36:03'),
(161, 52, 'user.activate_success', '{\"user_id\":52}', '76.50.120.215', '2025-06-05 19:36:42'),
(162, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 19:36:44'),
(163, NULL, 'user.logout', '{\"ip\":\"20.115.49.134\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/114.0.0.0 Safari\\/537.36\"}', '20.115.49.134', '2025-06-05 19:36:46'),
(164, 52, 'user.login', '{\"username\":\"vcdiff\",\"email\":\"resultingg@outlook.com\"}', '76.50.120.215', '2025-06-05 19:36:51'),
(165, 51, 'user.activate_failed', '{\"user_id\":\"51\",\"reason\":\"code_expired\"}', '178.72.88.86', '2025-06-05 19:37:01'),
(166, 51, 'user.activate_resend', '{\"user_id\":\"51\",\"email\":\"someonelovedie@gmail.com\"}', '178.72.88.86', '2025-06-05 19:37:14'),
(167, 51, 'user.activate_success', '{\"user_id\":51}', '178.72.88.86', '2025-06-05 19:38:39'),
(168, 51, 'user.login_failed', '{\"reason\":\"wrong_password\",\"attempts\":1}', '178.72.88.86', '2025-06-05 19:39:00'),
(169, 51, 'user.login', '{\"username\":\"Sti444\",\"email\":\"someonelovedie@gmail.com\"}', '178.72.88.86', '2025-06-05 19:39:18'),
(170, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36 Edg\\/137.0.0.0\"}', '95.146.14.61', '2025-06-05 19:39:28'),
(171, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-05 19:43:04'),
(172, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 19:43:10'),
(173, 54, 'user.register', '{\"username\":\"Accurzzy\",\"email\":\"kevin.willy31@gmail.com\"}', '172.59.99.34', '2025-06-05 20:31:30'),
(174, 54, 'user.activate_failed', '{\"user_id\":\"54\",\"reason\":\"code_expired\"}', '172.59.99.34', '2025-06-05 20:31:43'),
(175, 54, 'user.activate_failed', '{\"user_id\":\"54\",\"reason\":\"code_expired\"}', '172.59.99.34', '2025-06-05 20:31:52'),
(176, 54, 'user.activate_resend', '{\"user_id\":\"54\",\"email\":\"kevin.willy31@gmail.com\"}', '172.59.99.34', '2025-06-05 20:31:54'),
(177, 54, 'user.activate_success', '{\"user_id\":54}', '172.59.99.34', '2025-06-05 20:32:03'),
(178, 54, 'user.login', '{\"username\":\"Accurzzy\",\"email\":\"kevin.willy31@gmail.com\"}', '172.59.99.34', '2025-06-05 20:32:13'),
(179, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-05 22:10:01'),
(180, NULL, 'user.logout', '{\"ip\":\"77.199.242.122\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '77.199.242.122', '2025-06-05 22:19:03'),
(181, NULL, 'user.logout', '{\"ip\":\"77.199.242.122\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '77.199.242.122', '2025-06-05 22:20:52'),
(182, NULL, 'user.logout', '{\"ip\":\"35.237.4.214\",\"agent\":\"Mozilla\\/5.0 (compatible; Discordbot\\/2.0; +https:\\/\\/discordapp.com)\"}', '35.237.4.214', '2025-06-05 22:42:41'),
(183, NULL, 'user.logout', '{\"ip\":\"172.113.155.20\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/133.0.0.0 Safari\\/537.36 OPR\\/118.0.0.0\"}', '172.113.155.20', '2025-06-05 22:45:09'),
(184, NULL, 'user.logout', '{\"ip\":\"137.184.20.94\",\"agent\":\"python-requests\\/2.32.3\"}', '137.184.20.94', '2025-06-05 22:45:23'),
(185, NULL, 'user.logout', '{\"ip\":\"24.180.183.108\",\"agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.5 Safari\\/605.1.15\"}', '24.180.183.108', '2025-06-05 23:14:51'),
(186, 55, 'user.register', '{\"username\":\"Gealuvuh\",\"email\":\"Gealuvuh115@gmail.com\"}', '24.180.183.108', '2025-06-05 23:16:25'),
(187, 55, 'user.activate_failed', '{\"user_id\":\"55\",\"reason\":\"code_expired\"}', '24.180.183.108', '2025-06-05 23:16:40'),
(188, 55, 'user.activate_resend', '{\"user_id\":\"55\",\"email\":\"Gealuvuh115@gmail.com\"}', '24.180.183.108', '2025-06-05 23:16:43'),
(189, 55, 'user.activate_success', '{\"user_id\":55}', '24.180.183.108', '2025-06-05 23:17:08'),
(190, 55, 'user.login', '{\"username\":\"Gealuvuh\",\"email\":\"Gealuvuh115@gmail.com\"}', '24.180.183.108', '2025-06-05 23:17:19'),
(191, NULL, 'user.logout', '{\"ip\":\"206.237.119.215\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/131.0.0.0 Safari\\/537.36\"}', '206.237.119.215', '2025-06-05 23:23:24'),
(192, NULL, 'user.logout', '{\"ip\":\"193.37.32.81\",\"agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Mobile Safari\\/537.36\"}', '193.37.32.81', '2025-06-05 23:50:25'),
(193, NULL, 'user.logout', '{\"ip\":\"24.177.250.77\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko\\/20100101 Firefox\\/139.0\"}', '24.177.250.77', '2025-06-06 01:12:56'),
(194, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 06:03:59'),
(195, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 06:04:07'),
(196, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 06:57:34'),
(197, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 06:57:41'),
(198, 56, 'user.register', '{\"username\":\"AUniqueBot\",\"email\":\"blueyaaron@gmail.com\"}', '193.37.32.11', '2025-06-06 08:31:08'),
(199, 56, 'user.activate_failed', '{\"user_id\":\"56\",\"reason\":\"code_expired\"}', '193.37.32.9', '2025-06-06 08:31:44'),
(200, 56, 'user.activate_resend', '{\"user_id\":\"56\",\"email\":\"blueyaaron@gmail.com\"}', '193.37.32.20', '2025-06-06 08:31:52'),
(201, 56, 'user.activate_success', '{\"user_id\":56}', '193.37.32.31', '2025-06-06 08:32:21'),
(202, 56, 'user.login', '{\"username\":\"AUniqueBot\",\"email\":\"blueyaaron@gmail.com\"}', '193.37.32.44', '2025-06-06 08:32:44'),
(203, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 10:31:22'),
(204, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 10:31:29'),
(205, NULL, 'user.logout', '{\"ip\":\"164.92.186.55\",\"agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.0.0 Safari\\/537.36\"}', '164.92.186.55', '2025-06-06 10:46:53'),
(206, NULL, 'user.logout', '{\"ip\":\"211.7.113.176\",\"agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.5 Mobile\\/15E148 Safari\\/604.1\"}', '211.7.113.176', '2025-06-06 12:27:52'),
(207, NULL, 'user.logout', '{\"ip\":\"211.7.113.176\",\"agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.5 Mobile\\/15E148 Safari\\/604.1\"}', '211.7.113.176', '2025-06-06 12:28:02'),
(208, 31, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 12:45:13'),
(209, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-06 12:45:17'),
(210, NULL, 'user.logout', '{\"ip\":\"45.249.116.248\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko\\/20100101 Firefox\\/139.0\"}', '45.249.116.248', '2025-06-06 14:20:22'),
(211, NULL, 'user.logout', '{\"ip\":\"45.249.116.248\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko\\/20100101 Firefox\\/139.0\"}', '45.249.116.248', '2025-06-06 14:20:32'),
(212, NULL, 'user.logout', '{\"ip\":\"66.249.68.7\",\"agent\":\"Mozilla\\/5.0 (Linux; Android 6.0.1; Nexus 5X Build\\/MMB29P) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/99.0.4844.84 Mobile Safari\\/537.36 (compatible; Googlebot\\/2.1; +http:\\/\\/www.google.com\\/bot.html)\"}', '66.249.68.7', '2025-06-06 14:47:56'),
(213, NULL, 'user.logout', '{\"ip\":\"66.249.68.7\",\"agent\":\"Mozilla\\/5.0 (compatible; Googlebot\\/2.1; +http:\\/\\/www.google.com\\/bot.html)\"}', '66.249.68.7', '2025-06-06 14:47:57'),
(214, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 14:50:09'),
(215, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 14:50:16'),
(216, NULL, 'user.logout', '{\"ip\":\"185.182.52.138\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 OPR\\/119.0.0.0 (Edition std-1)\"}', '185.182.52.138', '2025-06-06 15:09:06'),
(217, NULL, 'user.logout', '{\"ip\":\"185.182.52.138\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 OPR\\/119.0.0.0 (Edition std-1)\"}', '185.182.52.138', '2025-06-06 15:09:08'),
(218, NULL, 'user.logout', '{\"ip\":\"62.192.154.50\",\"agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '62.192.154.50', '2025-06-06 16:28:29'),
(219, NULL, 'user.logout', '{\"ip\":\"62.192.154.1\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko\\/20100101 Firefox\\/139.0\"}', '62.192.154.1', '2025-06-06 16:40:14'),
(220, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 18:10:52'),
(221, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 18:10:59'),
(222, NULL, 'user.logout', '{\"ip\":\"73.162.109.199\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '73.162.109.199', '2025-06-06 19:57:22'),
(223, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 19:57:56'),
(224, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 19:58:04'),
(225, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36 Edg\\/137.0.0.0\"}', '95.146.14.61', '2025-06-06 19:58:35'),
(226, NULL, 'user.logout', '{\"ip\":\"98.113.246.119\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 OPR\\/119.0.0.0\"}', '98.113.246.119', '2025-06-06 21:00:01'),
(227, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 21:05:41'),
(228, NULL, 'user.logout', '{\"ip\":\"172.213.21.155\",\"agent\":\"Mozilla\\/5.0 AppleWebKit\\/537.36 (KHTML, like Gecko); compatible; ChatGPT-User\\/1.0; +https:\\/\\/openai.com\\/bot\"}', '172.213.21.155', '2025-06-06 21:06:20'),
(229, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 21:10:07'),
(230, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 21:10:14'),
(231, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-06 22:10:01'),
(232, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-06 22:10:07'),
(233, NULL, 'user.logout', '{\"ip\":\"178.24.235.70\",\"agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.5 Mobile\\/15E148 Safari\\/604.1\"}', '178.24.235.70', '2025-06-06 23:43:24'),
(234, NULL, 'user.logout', '{\"ip\":\"54.215.236.208\",\"agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/51.0.2704.103 Safari\\/537.36\"}', '54.215.236.208', '2025-06-06 23:51:58'),
(235, NULL, 'user.logout', '{\"ip\":\"20.171.207.208\",\"agent\":\"Mozilla\\/5.0 AppleWebKit\\/537.36 (KHTML, like Gecko; compatible; GPTBot\\/1.2; +https:\\/\\/openai.com\\/gptbot)\"}', '20.171.207.208', '2025-06-07 03:18:00'),
(236, 57, 'user.register', '{\"username\":\"Timppa\",\"email\":\"Konaukko1337@gmail.com\"}', '176.93.254.33', '2025-06-07 03:18:51'),
(237, 57, 'user.activate_failed', '{\"user_id\":\"57\",\"reason\":\"code_expired\"}', '176.93.254.33', '2025-06-07 03:19:09'),
(238, 57, 'user.activate_failed', '{\"user_id\":\"57\",\"reason\":\"code_expired\"}', '176.93.254.33', '2025-06-07 03:19:23'),
(239, 57, 'user.activate_resend', '{\"user_id\":\"57\",\"email\":\"Konaukko1337@gmail.com\"}', '176.93.254.33', '2025-06-07 03:19:27'),
(240, 57, 'user.activate_success', '{\"user_id\":57}', '176.93.254.33', '2025-06-07 03:19:46'),
(241, 57, 'user.login', '{\"username\":\"Timppa\",\"email\":\"Konaukko1337@gmail.com\"}', '176.93.254.33', '2025-06-07 03:20:10'),
(242, NULL, 'user.logout', '{\"ip\":\"20.171.207.166\",\"agent\":\"Mozilla\\/5.0 AppleWebKit\\/537.36 (KHTML, like Gecko; compatible; GPTBot\\/1.2; +https:\\/\\/openai.com\\/gptbot)\"}', '20.171.207.166', '2025-06-07 03:34:58'),
(243, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 10:46:13'),
(244, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-07 10:46:19'),
(245, NULL, 'user.logout', '{\"ip\":\"209.99.152.150\",\"agent\":\"Mozilla\\/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/115.0.0.0 Safari\\/537.36\"}', '209.99.152.150', '2025-06-07 13:42:41'),
(246, NULL, 'user.logout', '{\"ip\":\"205.169.39.11\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/106.0.0.0 Safari\\/537.36\"}', '205.169.39.11', '2025-06-07 13:45:45'),
(247, NULL, 'user.logout', '{\"ip\":\"95.61.222.252\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.61.222.252', '2025-06-07 14:28:58'),
(248, NULL, 'user.logout', '{\"ip\":\"95.61.222.252\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.61.222.252', '2025-06-07 14:29:01'),
(249, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36 Edg\\/137.0.0.0\"}', '95.146.14.61', '2025-06-07 14:46:18'),
(250, 48, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 14:48:59'),
(251, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 14:49:16'),
(252, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-07 14:49:22'),
(253, NULL, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 17:03:13'),
(254, 48, 'user.login', '{\"username\":\"OwenC137\",\"email\":\"owencroft0@gmail.com\"}', '95.146.14.61', '2025-06-07 17:03:58'),
(255, 31, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 17:11:41'),
(256, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-07 17:11:46'),
(257, 31, 'user.logout', '{\"ip\":\"95.146.14.61\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/137.0.0.0 Safari\\/537.36\"}', '95.146.14.61', '2025-06-07 17:11:58'),
(258, 31, 'user.login', '{\"username\":\"AnnaMai\",\"email\":\"123annamai@gmail.com\"}', '95.146.14.61', '2025-06-07 17:12:02'),
(259, NULL, 'user.logout', '{\"ip\":\"24.180.183.108\",\"agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.5 Safari\\/605.1.15\"}', '24.180.183.108', '2025-06-07 17:38:17'),
(260, 55, 'user.login', '{\"username\":\"Gealuvuh\",\"email\":\"Gealuvuh115@gmail.com\"}', '24.180.183.108', '2025-06-07 17:38:27'),
(261, NULL, 'user.logout', '{\"ip\":\"192.34.58.133\",\"agent\":\"python-requests\\/2.32.3\"}', '192.34.58.133', '2025-06-07 17:38:44');

-- --------------------------------------------------------

--
-- Table structure for table `demon_chat_settings`
--

CREATE TABLE `demon_chat_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_credit_logs`
--

CREATE TABLE `demon_credit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `change_amount` int(11) NOT NULL COMMENT 'Positive for grant, negative for deduction',
  `type` enum('earn','spend') NOT NULL DEFAULT 'earn',
  `reason` varchar(255) NOT NULL COMMENT 'Why credits were given (e.g. "Daily Reward")',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quest_key` varchar(100) DEFAULT NULL COMMENT 'quest_key from demon_quests if this credit was awarded as quest reward'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_credit_logs`
--

INSERT INTO `demon_credit_logs` (`id`, `user_id`, `change_amount`, `type`, `reason`, `description`, `created_at`, `quest_key`) VALUES
(4, 1, 100, 'earn', 'Daily Reward', NULL, '2025-06-05 12:30:29', NULL),
(5, 31, 100, 'earn', 'Daily Reward', NULL, '2025-06-05 13:19:58', NULL),
(7, 1, 100, 'earn', 'Referral Bonus', 'Activated user #47', '2025-06-05 16:23:08', NULL),
(8, 1, -50, 'spend', 'Transfer to AnnaMai', 'Sent 50 credits to AnnaMai', '2025-06-05 16:32:49', NULL),
(9, 31, 50, 'earn', 'Transfer from admin', 'Received 50 credits from admin', '2025-06-05 16:32:49', NULL),
(10, 48, 100, 'earn', 'Daily Reward', NULL, '2025-06-05 16:43:55', NULL),
(11, 48, 100, 'earn', 'Referral Bonus', 'Activated user #52', '2025-06-05 19:36:42', NULL),
(12, 48, 100, 'earn', 'Referral Bonus', 'Activated user #51', '2025-06-05 19:38:39', NULL),
(13, 48, 100, 'earn', 'Referral Bonus', 'Activated user #54', '2025-06-05 20:32:03', NULL),
(14, 54, 100, 'earn', 'Daily Reward', NULL, '2025-06-05 20:33:53', NULL),
(15, 48, 100, 'earn', 'Referral Bonus', 'Activated user #55', '2025-06-05 23:17:08', NULL),
(21, 48, 50, 'earn', 'Quest:complete_profile', 'Completed Profile Quest', '2025-06-06 07:45:24', NULL),
(22, 48, 100, 'earn', 'Referral Bonus', 'Activated user #56', '2025-06-06 08:32:21', NULL),
(23, 48, 50, 'earn', 'Quest:complete_profile', 'Completed Profile Quest', '2025-06-06 11:45:08', NULL),
(24, 48, 25, 'earn', 'Quest:chat_50_times', 'Sent 7 chat messages', '2025-06-06 11:51:07', NULL),
(25, 48, 50, 'earn', 'Wheel Spin', 'Wheel Spin: +50 credits', '2025-06-06 12:04:33', NULL),
(26, 48, 0, 'earn', 'Wheel Spin', 'Wheel Spin: No reward', '2025-06-06 12:23:28', NULL),
(27, 48, 20, 'earn', 'Wheel Spin', 'Wheel Spin: +20 credits', '2025-06-06 12:23:37', NULL),
(28, 48, 10, 'earn', 'Wheel Spin', 'Wheel Spin: +10 credits', '2025-06-06 12:27:07', NULL),
(29, 48, 0, 'earn', 'Wheel Spin', 'Wheel Spin: No reward', '2025-06-06 12:34:14', NULL),
(30, 48, 20, 'earn', 'Wheel Spin', 'Wheel Spin: +20 credits', '2025-06-06 12:34:55', NULL),
(31, 48, 5, 'earn', 'Wheel Spin', 'Wheel Spin: +5 credits', '2025-06-06 12:35:06', NULL),
(32, 48, 50, 'earn', 'Wheel Spin', 'Wheel Spin: +50 credits', '2025-06-06 12:35:14', NULL),
(33, 31, 100, 'earn', 'Daily Reward', NULL, '2025-06-06 12:45:24', NULL),
(34, 31, 50, 'earn', 'Wheel Spin', 'Wheel Spin: +50 credits', '2025-06-06 12:45:44', NULL),
(35, 31, 50, 'earn', 'Quest:complete_profile', 'Completed Profile Quest', '2025-06-06 12:48:11', NULL),
(36, 31, 25, 'earn', 'Quest:chat_50_times', 'Sent 50 chat messages', '2025-06-06 12:53:11', NULL),
(37, 48, 5, 'earn', 'Wheel Spin', 'Wheel Spin: +5 credits', '2025-06-06 13:03:50', NULL),
(38, 31, 5, 'earn', 'Wheel Spin', 'Wheel Spin: +5 credits', '2025-06-06 13:04:23', NULL),
(39, 31, 50, 'earn', 'Wheel Spin', 'Wheel Spin: +50 credits', '2025-06-06 13:04:36', NULL),
(40, 31, 20, 'earn', 'Wheel Spin', 'Wheel Spin: +20 credits', '2025-06-06 13:04:46', NULL),
(41, 31, 100, 'earn', 'Wheel Spin', 'Wheel Spin: +100 credits', '2025-06-06 13:04:57', NULL),
(42, 31, 100, 'earn', 'Wheel Spin', 'Wheel Spin: +100 credits', '2025-06-06 13:05:08', NULL),
(43, 31, 10, 'earn', 'Wheel Spin', 'Wheel Spin: +10 credits', '2025-06-06 13:05:16', NULL),
(44, 31, 10, 'earn', 'Wheel Spin', 'Wheel Spin: +10 credits', '2025-06-06 13:05:23', NULL),
(45, 31, 5, 'earn', 'Wheel Spin', 'Wheel Spin: +5 credits', '2025-06-06 13:05:30', NULL),
(46, 31, 10, 'earn', 'Wheel Spin', 'Wheel Spin: +10 credits', '2025-06-06 13:05:36', NULL),
(47, 31, 0, 'earn', 'Wheel Spin', 'Wheel Spin: No reward', '2025-06-06 13:05:43', NULL),
(48, 48, 100, 'earn', 'Wheel Spin', 'Wheel Spin: +100 credits', '2025-06-06 13:11:11', NULL),
(49, 48, 0, 'earn', 'Wheel Spin', 'Wheel Spin: No reward', '2025-06-06 13:11:18', NULL),
(50, 48, 20, 'earn', 'Wheel Spin', 'Wheel Spin: +20 credits', '2025-06-06 13:21:15', NULL),
(51, 48, 100, 'earn', 'Wheel Spin', 'Wheel Spin: +100 credits', '2025-06-06 13:21:23', NULL),
(52, 48, 100, 'earn', 'Daily Reward', NULL, '2025-06-06 13:23:05', NULL),
(53, 57, 100, 'earn', 'Daily Reward', NULL, '2025-06-07 03:22:36', 'daily_reward'),
(54, 57, 0, 'earn', 'Wheel Spin', 'Wheel Spin: No reward', '2025-06-07 03:22:39', NULL),
(55, 48, 100, 'earn', 'Daily Reward', NULL, '2025-06-07 14:23:46', 'daily_reward'),
(56, 48, 20, 'earn', 'Wheel Spin', 'Wheel Spin: +20 credits', '2025-06-07 14:23:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `demon_credit_transactions`
--

CREATE TABLE `demon_credit_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `amount` bigint(20) NOT NULL COMMENT 'Positive = earned, Negative = spent',
  `type` varchar(50) NOT NULL COMMENT 'e.g. daily_reward, purchase, game_bonus, admin_adjust',
  `description` varchar(255) NOT NULL COMMENT 'Human-readable note, e.g. "Daily login bonus", "Bought Sword"',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_donations`
--

CREATE TABLE `demon_donations` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(20) NOT NULL,
  `gateway_reference` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_follows`
--

CREATE TABLE `demon_follows` (
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'The profile being followed',
  `follower_id` int(10) UNSIGNED NOT NULL COMMENT 'The user who follows',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_follows`
--

INSERT INTO `demon_follows` (`user_id`, `follower_id`, `created_at`) VALUES
(1, 31, '2025-06-04 22:25:49'),
(1, 34, '2025-06-05 00:53:04'),
(1, 35, '2025-06-05 09:38:32'),
(31, 1, '2025-06-05 13:36:47'),
(34, 1, '2025-06-05 00:53:39'),
(35, 1, '2025-06-05 09:44:08'),
(48, 51, '2025-06-05 20:26:04'),
(51, 48, '2025-06-05 19:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `demon_login_attempts`
--

CREATE TABLE `demon_login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `username_or_email` varchar(255) DEFAULT NULL COMMENT 'What they tried to log in as',
  `ip_address` varchar(45) NOT NULL,
  `was_successful` tinyint(1) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_login_attempts`
--

INSERT INTO `demon_login_attempts` (`id`, `user_id`, `username_or_email`, `ip_address`, `was_successful`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin', '127.0.0.1', 1, 'initial-setup', '2025-06-03 20:08:41'),
(2, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-03 20:09:58'),
(3, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-03 20:12:37'),
(4, 1, 'admin', '90.214.100.136', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-03 20:13:19'),
(5, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-06-03 20:14:40'),
(6, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-06-03 20:15:13'),
(7, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-03 20:21:04'),
(8, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-03 20:26:09'),
(9, 1, 'admin', '95.146.14.61', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-03 20:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `demon_messages`
--

CREATE TABLE `demon_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_messages`
--

INSERT INTO `demon_messages` (`id`, `user_id`, `content`, `created_at`, `is_pinned`, `parent_id`, `edited_at`) VALUES
(4, 48, 'er', '2025-06-05 16:20:14', 0, NULL, NULL),
(5, 48, 'This is a test', '2025-06-05 16:20:46', 0, NULL, NULL),
(6, 48, 'This is a second test', '2025-06-05 16:28:08', 0, NULL, NULL),
(7, 48, 'cfgjcxfyj', '2025-06-05 16:36:59', 0, NULL, NULL),
(8, 48, 'rgzdrhsdrh', '2025-06-05 16:37:01', 0, NULL, NULL),
(9, 48, 'zdrhzdr', '2025-06-05 16:37:02', 0, NULL, NULL),
(10, 48, 'zdhzdrhzdrh', '2025-06-05 16:37:04', 0, NULL, NULL),
(11, 48, 'zdrhzdrhzdrh', '2025-06-05 16:37:11', 0, NULL, NULL),
(12, 48, 'zdrhzdrhzdrh', '2025-06-05 16:37:13', 0, NULL, NULL),
(13, 48, 'zdrhzdrhzrh', '2025-06-05 16:37:14', 0, NULL, NULL),
(14, 48, 'zdrhzdrhhr', '2025-06-05 16:37:16', 0, NULL, NULL),
(15, 48, '❤️', '2025-06-05 16:42:39', 0, NULL, NULL),
(16, 48, '💯🎉🙌💯😂❤️👍😎😢🥳🔥😢💯🙌🎉😂❤️', '2025-06-05 16:43:47', 0, NULL, NULL),
(17, 48, 'can u see this', '2025-06-05 16:44:48', 0, NULL, NULL),
(18, 48, 'is this live?', '2025-06-05 16:44:56', 0, NULL, NULL),
(19, 31, 'YES', '2025-06-05 16:44:58', 0, NULL, NULL),
(20, 48, 'yay', '2025-06-05 16:45:03', 0, NULL, NULL),
(21, 48, 'yay', '2025-06-05 16:45:06', 0, NULL, NULL),
(22, 48, '😎', '2025-06-05 16:45:13', 0, NULL, NULL),
(23, 48, 'test', '2025-06-05 17:05:52', 0, NULL, NULL),
(24, 35, 'what is CoD', '2025-06-05 18:36:06', 0, NULL, NULL),
(25, 48, 'Yooo', '2025-06-05 18:42:50', 0, NULL, NULL),
(26, 55, '😎', '2025-06-05 19:18:59', 0, NULL, NULL),
(27, 48, 'dddd', '2025-06-06 07:14:40', 0, NULL, NULL),
(28, 48, 'ddddd', '2025-06-06 07:14:42', 0, NULL, NULL),
(29, 48, 'ssss', '2025-06-06 07:20:17', 0, NULL, NULL),
(30, 48, 'ssss', '2025-06-06 07:25:29', 0, NULL, NULL),
(31, 48, 'ssaaa', '2025-06-06 07:25:34', 0, NULL, NULL),
(32, 48, 'srgrgr', '2025-06-06 07:26:16', 0, NULL, NULL),
(33, 48, 'srgsrgzdthdth', '2025-06-06 07:26:20', 0, NULL, NULL),
(34, 48, 'ESFSEFsef', '2025-06-06 07:28:22', 0, NULL, NULL),
(35, 48, 'ZFDGZDFGZDFG', '2025-06-06 07:28:25', 0, NULL, NULL),
(36, 48, 'aefsefSef', '2025-06-06 07:28:26', 0, NULL, NULL),
(37, 48, 'sefsefSef', '2025-06-06 07:28:28', 0, NULL, NULL),
(38, 48, 'SefseFSEF', '2025-06-06 07:28:30', 0, NULL, NULL),
(39, 48, 'SFsefSef', '2025-06-06 07:28:31', 0, NULL, NULL),
(40, 48, 'Sefsefsef', '2025-06-06 07:28:33', 0, NULL, NULL),
(41, 48, 'Sefsefsef', '2025-06-06 07:28:34', 0, NULL, NULL),
(42, 48, 'sefsefsef', '2025-06-06 07:28:36', 0, NULL, NULL),
(43, 48, 'SefsefSFE', '2025-06-06 07:28:38', 0, NULL, NULL),
(44, 48, 'SfseFsef', '2025-06-06 07:28:39', 0, NULL, NULL),
(45, 48, 'sefseSfe', '2025-06-06 07:28:41', 0, NULL, NULL),
(46, 48, 'sssss', '2025-06-06 07:43:45', 0, NULL, NULL),
(47, 48, 'sssssss', '2025-06-06 07:45:30', 0, NULL, NULL),
(48, 48, 'ssssssss', '2025-06-06 07:49:50', 0, NULL, NULL),
(49, 48, 'sfSDGSDGzfhb', '2025-06-06 07:49:53', 0, NULL, NULL),
(50, 48, 'ZDVXDBXFB', '2025-06-06 07:50:48', 0, NULL, NULL),
(51, 48, 'XFBXFBXFB', '2025-06-06 07:50:50', 0, NULL, NULL),
(52, 48, 'DVSXDBVZSFDBZFB', '2025-06-06 07:51:04', 0, NULL, NULL),
(53, 48, 'ZDVBZXFBFB', '2025-06-06 07:51:07', 0, NULL, NULL),
(54, 48, 'this is a chat test', '2025-06-06 07:52:41', 0, NULL, NULL),
(55, 31, 'chat', '2025-06-06 08:48:31', 0, NULL, NULL),
(56, 31, 'chat', '2025-06-06 08:48:32', 0, NULL, NULL),
(57, 31, 'chat', '2025-06-06 08:48:35', 0, NULL, NULL),
(58, 31, 'chat', '2025-06-06 08:48:37', 0, NULL, NULL),
(59, 31, 'chat', '2025-06-06 08:48:39', 0, NULL, NULL),
(60, 31, 'chat', '2025-06-06 08:48:40', 0, NULL, NULL),
(61, 31, 'chat', '2025-06-06 08:48:42', 0, NULL, NULL),
(62, 31, 'chat', '2025-06-06 08:48:44', 0, NULL, NULL),
(63, 31, 'chat', '2025-06-06 08:48:45', 0, NULL, NULL),
(64, 31, 'chitty chat', '2025-06-06 08:49:03', 0, NULL, NULL),
(65, 31, 'chitty chat', '2025-06-06 08:49:04', 0, NULL, NULL),
(66, 31, 'chitty chat', '2025-06-06 08:49:06', 0, NULL, NULL),
(67, 31, 'chitty chat', '2025-06-06 08:49:07', 0, NULL, NULL),
(68, 31, 'chitty chat', '2025-06-06 08:49:08', 0, NULL, NULL),
(69, 31, 'chitty chat', '2025-06-06 08:49:10', 0, NULL, NULL),
(70, 31, 'chitty chat', '2025-06-06 08:49:11', 0, NULL, NULL),
(71, 31, 'chitty chat', '2025-06-06 08:49:12', 0, NULL, NULL),
(72, 31, 'chitty chat', '2025-06-06 08:49:13', 0, NULL, NULL),
(73, 31, 'chitty chitty chat', '2025-06-06 08:49:29', 0, NULL, NULL),
(74, 31, 'chitty chitty chat', '2025-06-06 08:49:30', 0, NULL, NULL),
(75, 31, 'chitty chitty chat', '2025-06-06 08:49:31', 0, NULL, NULL),
(76, 31, 'chitty chitty chat', '2025-06-06 08:49:33', 0, NULL, NULL),
(77, 31, 'chitty chitty chat', '2025-06-06 08:49:34', 0, NULL, NULL),
(78, 31, 'chitty chitty chat', '2025-06-06 08:49:35', 0, NULL, NULL),
(79, 31, 'chitty chitty chat', '2025-06-06 08:49:37', 0, NULL, NULL),
(80, 31, 'chitty chitty chat', '2025-06-06 08:49:38', 0, NULL, NULL),
(81, 31, 'chitty chitty chat', '2025-06-06 08:49:39', 0, NULL, NULL),
(82, 31, 'chitty chitty chat', '2025-06-06 08:49:40', 0, NULL, NULL),
(83, 31, 'chitty chitty chat', '2025-06-06 08:49:42', 0, NULL, NULL),
(84, 31, 'chitty chitty chat', '2025-06-06 08:49:43', 0, NULL, NULL),
(85, 31, 'chitty chitty chat', '2025-06-06 08:49:44', 0, NULL, NULL),
(86, 31, 'chitty chitty chat', '2025-06-06 08:49:46', 0, NULL, NULL),
(87, 31, 'chitty chitty chat', '2025-06-06 08:49:47', 0, NULL, NULL),
(88, 31, 'chitty chitty chat', '2025-06-06 08:49:48', 0, NULL, NULL),
(89, 31, 'chitty chitty chat', '2025-06-06 08:49:49', 0, NULL, NULL),
(90, 31, 'chitty chitty chat', '2025-06-06 08:49:50', 0, NULL, NULL),
(91, 31, 'chitty chitty chat chat', '2025-06-06 08:52:31', 0, NULL, NULL),
(92, 31, 'chitty chitty chat chat', '2025-06-06 08:52:32', 0, NULL, NULL),
(93, 31, 'chitty chitty chat chat', '2025-06-06 08:52:34', 0, NULL, NULL),
(94, 31, 'chitty chitty chat chat', '2025-06-06 08:52:35', 0, NULL, NULL),
(95, 31, 'chitty chitty chat chat', '2025-06-06 08:52:43', 0, NULL, NULL),
(96, 31, 'chitty chitty chat chat', '2025-06-06 08:52:44', 0, NULL, NULL),
(97, 31, 'chitty chitty chat chat', '2025-06-06 08:52:45', 0, NULL, NULL),
(98, 31, 'chitty chitty chat chat', '2025-06-06 08:52:47', 0, NULL, NULL),
(99, 31, 'chitty chitty chat chat', '2025-06-06 08:52:48', 0, NULL, NULL),
(100, 31, 'chitty chitty chat chat', '2025-06-06 08:52:49', 0, NULL, NULL),
(101, 31, 'chitty chitty chat chat', '2025-06-06 08:52:50', 0, NULL, NULL),
(102, 31, 'chitty chitty chat chat', '2025-06-06 08:52:51', 0, NULL, NULL),
(103, 31, 'chat chitty chitty chat chitty chat chat', '2025-06-06 08:53:10', 0, NULL, NULL),
(104, 31, 'chat chitty chitty chat chitty chat chat', '2025-06-06 08:53:11', 0, NULL, NULL),
(105, 31, 'chat chitty chitty chat chitty chat chat', '2025-06-06 08:53:13', 0, NULL, NULL),
(106, 31, 'chat chitty chitty chat chitty chat chat', '2025-06-06 08:53:14', 0, NULL, NULL),
(107, 48, 'Test', '2025-06-06 17:57:16', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `demon_message_reactions`
--

CREATE TABLE `demon_message_reactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `message_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reaction` varchar(32) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_message_reactions`
--

INSERT INTO `demon_message_reactions` (`id`, `message_id`, `user_id`, `reaction`, `created_at`) VALUES
(8, 2, 48, '👍', '2025-06-05 16:15:03'),
(11, 5, 51, '👍', '2025-06-05 16:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `demon_notifications`
--

CREATE TABLE `demon_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL COMMENT 'Optional URL the notification points to',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_notifications`
--

INSERT INTO `demon_notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 'Welcome to DemonGen! Let’s get started.', 'dashboard.php', 1, '2025-06-03 20:33:20'),
(47, 31, 'Welcome to DemonGen! Let’s get started.', 'dashboard.php', 1, '2025-06-04 21:33:40'),
(48, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-04 21:45:49'),
(49, 1, 'AnnaMai started following you.', 'profile.php?uid=31', 1, '2025-06-04 21:46:01'),
(50, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-04 21:50:18'),
(51, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-04 21:50:36'),
(52, 1, 'AnnaMai unfollowed you.', 'profile.php?uid=31', 1, '2025-06-04 22:10:08'),
(53, 1, 'AnnaMai started following you.', 'profile.php?uid=31', 1, '2025-06-04 22:25:49'),
(54, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-1', 1, '2025-06-04 22:55:13'),
(55, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-2', 1, '2025-06-04 22:58:47'),
(56, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-3', 1, '2025-06-04 23:00:40'),
(57, 1, 'AnnaMai liked your post.', '/profile.php?uid=1#post-6', 1, '2025-06-04 23:17:32'),
(58, 1, 'AnnaMai loved your post.', '/profile.php?uid=1#post-4', 1, '2025-06-04 23:20:59'),
(59, 1, 'AnnaMai found your post funny.', '/profile.php?uid=1#post-2', 1, '2025-06-04 23:21:01'),
(60, 1, 'AnnaMai didn&#039;t like your post.', '/profile.php?uid=1#post-1', 1, '2025-06-04 23:21:02'),
(61, 1, 'AnnaMai liked your post.', '/profile.php?uid=1#post-8', 1, '2025-06-04 23:43:24'),
(62, 1, 'AnnaMai found your post funny.', '/profile.php?uid=1#post-7', 1, '2025-06-04 23:43:32'),
(64, 33, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 00:27:04'),
(65, 1, ' liked your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:42:09'),
(66, 1, ' did not like your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:42:41'),
(67, 31, 'admin liked your post.', '/profile.php?uid=31#post-9', 1, '2025-06-05 00:44:23'),
(68, 31, 'admin did not like your post.', '/profile.php?uid=31#post-9', 1, '2025-06-05 00:44:52'),
(69, 31, 'admin found your post funny.', '/profile.php?uid=31#post-9', 1, '2025-06-05 00:46:36'),
(70, 31, 'admin liked your post.', '/profile.php?uid=31#post-9', 1, '2025-06-05 00:46:47'),
(71, 1, ' loved your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:48:19'),
(72, 1, ' liked your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:48:30'),
(73, 34, 'Your account has been created by an administrator.', 'dashboard.php', 1, '2025-06-05 00:51:01'),
(74, 1, 'TestingAccount loved your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:52:10'),
(75, 1, 'TestingAccount did not like your post.', '/profile.php?uid=1#post-12', 1, '2025-06-05 00:52:24'),
(76, 1, 'TestingAccount started following you.', 'profile.php?uid=34', 1, '2025-06-05 00:53:04'),
(77, 34, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 00:53:39'),
(78, 1, 'AnnaMai did not like your post.', '/profile.php?uid=1#post-13', 1, '2025-06-05 00:54:54'),
(79, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-16', 1, '2025-06-05 01:02:43'),
(80, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-16', 1, '2025-06-05 01:02:43'),
(81, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-17', 1, '2025-06-05 01:04:30'),
(82, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-17', 1, '2025-06-05 01:04:30'),
(83, 1, 'AnnaMai has posted on their profile.', '/profile.php?uid=31#post-18', 1, '2025-06-05 01:06:14'),
(84, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-19', 1, '2025-06-05 01:11:26'),
(85, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-19', 1, '2025-06-05 01:11:26'),
(86, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-20', 1, '2025-06-05 01:15:17'),
(87, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-20', 1, '2025-06-05 01:15:17'),
(88, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-21', 1, '2025-06-05 01:15:17'),
(89, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-21', 1, '2025-06-05 01:15:17'),
(90, 1, 'AnnaMai found your post funny.', '/profile.php?uid=1#post-20', 1, '2025-06-05 01:16:06'),
(91, 1, 'TestingAccount has posted on their profile.', '/profile.php?uid=34#post-22', 1, '2025-06-05 01:22:01'),
(92, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-23', 1, '2025-06-05 01:22:09'),
(93, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-23', 1, '2025-06-05 01:22:09'),
(94, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-24', 1, '2025-06-05 01:22:55'),
(95, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-24', 1, '2025-06-05 01:22:55'),
(96, 1, 'TestingAccount has posted on their profile.', '/profile.php?uid=34#post-25', 1, '2025-06-05 01:23:05'),
(97, 1, 'AnnaMai has posted on their profile.', '/profile.php?uid=31#post-26', 1, '2025-06-05 01:23:31'),
(98, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-27', 1, '2025-06-05 01:27:43'),
(99, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-27', 1, '2025-06-05 01:27:43'),
(100, 1, 'AnnaMai has posted on their profile.', '/profile.php?uid=31#post-28', 1, '2025-06-05 01:30:36'),
(101, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-29', 1, '2025-06-05 01:30:55'),
(102, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-29', 1, '2025-06-05 01:30:55'),
(103, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-30', 1, '2025-06-05 01:34:01'),
(104, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-30', 1, '2025-06-05 01:34:01'),
(105, 1, 'AnnaMai has posted on their profile.', '/profile.php?uid=31#post-31', 1, '2025-06-05 01:34:14'),
(106, 31, 'admin liked your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 01:35:33'),
(107, 1, 'TestingAccount found your post funny.', '/profile.php?uid=1#post-30', 1, '2025-06-05 01:36:01'),
(108, 35, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 09:21:35'),
(109, 35, 'admin started following you.', 'profile.php?uid=1', 0, '2025-06-05 09:24:49'),
(110, 1, 'Simpy started following you.', 'profile.php?uid=35', 1, '2025-06-05 09:38:32'),
(111, 35, 'admin unfollowed you.', 'profile.php?uid=1', 0, '2025-06-05 09:44:07'),
(112, 35, 'admin started following you.', 'profile.php?uid=1', 0, '2025-06-05 09:44:08'),
(113, 31, 'admin did not like your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:43:27'),
(114, 31, 'admin did not like your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:43:29'),
(115, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-32', 1, '2025-06-05 12:53:32'),
(116, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-32', 0, '2025-06-05 12:53:32'),
(117, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-32', 0, '2025-06-05 12:53:32'),
(118, 31, 'admin liked your post.', '/profile.php?uid=31#post-26', 1, '2025-06-05 12:54:12'),
(119, 31, 'admin liked your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:55:26'),
(120, 31, 'admin loved your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:55:27'),
(121, 31, 'admin found your post funny.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:55:27'),
(122, 31, 'admin did not like your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:55:28'),
(123, 31, 'admin liked your post.', '/profile.php?uid=31#post-18', 1, '2025-06-05 12:55:32'),
(124, 31, 'admin liked your post.', '/profile.php?uid=31#post-28', 1, '2025-06-05 12:55:33'),
(125, 31, 'admin liked your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 12:55:34'),
(126, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-33', 1, '2025-06-05 12:57:20'),
(127, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-33', 0, '2025-06-05 12:57:20'),
(128, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-33', 0, '2025-06-05 12:57:20'),
(129, 31, 'admin found your post funny.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:04:08'),
(130, 31, 'admin found your post funny.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:04:09'),
(131, 31, 'admin found your post funny.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:04:09'),
(132, 31, 'admin loved your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:05:22'),
(133, 31, 'admin found your post funny.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:05:26'),
(134, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-34', 1, '2025-06-05 13:07:22'),
(135, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-34', 0, '2025-06-05 13:07:22'),
(136, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-34', 0, '2025-06-05 13:07:22'),
(137, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-35', 1, '2025-06-05 13:07:27'),
(138, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-35', 0, '2025-06-05 13:07:27'),
(139, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-35', 0, '2025-06-05 13:07:27'),
(140, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-36', 1, '2025-06-05 13:07:37'),
(141, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-36', 0, '2025-06-05 13:07:37'),
(142, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-36', 0, '2025-06-05 13:07:37'),
(143, 31, 'admin liked your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:07:44'),
(144, 31, 'admin loved your post.', '/profile.php?uid=31#post-28', 1, '2025-06-05 13:08:33'),
(145, 31, 'admin loved your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:08:34'),
(146, 31, 'admin did not like your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:08:37'),
(147, 31, 'admin liked your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:14:30'),
(148, 31, 'admin did not like your post.', '/profile.php?uid=31#post-26', 1, '2025-06-05 13:14:32'),
(149, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-37', 1, '2025-06-05 13:14:49'),
(150, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-37', 0, '2025-06-05 13:14:49'),
(151, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-37', 0, '2025-06-05 13:14:49'),
(152, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:28:09'),
(153, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:28:14'),
(154, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:28:51'),
(155, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:28:55'),
(156, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:29:34'),
(157, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:29:37'),
(158, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:29:38'),
(159, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:29:40'),
(160, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:32:44'),
(161, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:32:48'),
(162, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:32:53'),
(163, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:32:56'),
(164, 31, 'admin loved your post.', '/profile.php?uid=31#post-9', 1, '2025-06-05 13:33:48'),
(165, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:30'),
(166, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:33'),
(167, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:35'),
(168, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:36'),
(169, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:37'),
(170, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:38'),
(171, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:39'),
(172, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:41'),
(173, 31, 'admin unfollowed you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:44'),
(174, 31, 'admin started following you.', 'profile.php?uid=1', 1, '2025-06-05 13:36:47'),
(175, 31, 'admin did not like your post.', '/profile.php?uid=31#post-31', 1, '2025-06-05 13:37:04'),
(176, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-38', 1, '2025-06-05 13:49:33'),
(177, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-38', 0, '2025-06-05 13:49:33'),
(178, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-38', 0, '2025-06-05 13:49:33'),
(179, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-39', 1, '2025-06-05 14:02:15'),
(180, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-39', 0, '2025-06-05 14:02:15'),
(181, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-39', 0, '2025-06-05 14:02:15'),
(193, 47, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 16:22:39'),
(194, 31, 'admin has posted on their profile.', '/profile.php?uid=1#post-40', 1, '2025-06-05 16:30:18'),
(195, 34, 'admin has posted on their profile.', '/profile.php?uid=1#post-40', 0, '2025-06-05 16:30:18'),
(196, 35, 'admin has posted on their profile.', '/profile.php?uid=1#post-40', 0, '2025-06-05 16:30:18'),
(197, 31, 'admin has sent you 50 credits.', 'credits.php', 1, '2025-06-05 16:32:49'),
(198, 48, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 1, '2025-06-05 16:42:52'),
(201, 51, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 19:33:08'),
(202, 52, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 19:35:29'),
(203, 53, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-05 19:35:33'),
(204, 51, 'OwenC137 started following you.', 'profile.php?uid=48', 0, '2025-06-05 19:40:03'),
(205, 48, 'Sti444 started following you.', 'profile.php?uid=51', 1, '2025-06-05 20:26:04'),
(206, 54, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 1, '2025-06-05 20:31:30'),
(207, 55, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 1, '2025-06-05 23:16:25'),
(210, 48, 'Congratulations! You completed your profile and earned 50 credits.', 'credits.php', 1, '2025-06-06 07:45:24'),
(211, 56, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 0, '2025-06-06 08:31:08'),
(212, 48, 'Congratulations! You completed your profile and earned 50 credits.', 'credits.php', 1, '2025-06-06 11:45:08'),
(214, 31, 'Congratulations! You completed your profile and earned 50 credits.', 'credits.php', 1, '2025-06-06 12:48:11'),
(215, 31, 'Congrats! You’ve sent 50 messages and earned 25 credits.', 'credits.php', 1, '2025-06-06 12:53:11'),
(216, 31, 'OwenC137 started following you.', 'profile.php?uid=48', 1, '2025-06-06 14:04:40'),
(217, 31, 'OwenC137 unfollowed you.', 'profile.php?uid=48', 1, '2025-06-06 14:04:51'),
(218, 31, 'OwenC137 started following you.', 'profile.php?uid=48', 1, '2025-06-06 14:07:19'),
(219, 31, 'OwenC137 unfollowed you.', 'profile.php?uid=48', 1, '2025-06-06 14:07:57'),
(220, 31, 'OwenC137 started following you.', 'profile.php?uid=48', 1, '2025-06-06 14:08:25'),
(221, 31, 'OwenC137 unfollowed you.', 'profile.php?uid=48', 1, '2025-06-06 14:09:12'),
(222, 31, 'OwenC137 started following you.', 'profile.php?uid=48', 1, '2025-06-06 14:10:36'),
(223, 31, 'OwenC137 unfollowed you.', 'profile.php?uid=48', 1, '2025-06-06 14:11:19'),
(224, 57, 'Welcome to CODDB! Let’s get started.', 'dashboard.php', 1, '2025-06-07 03:18:51'),
(225, 57, 'You’ve claimed your daily reward of 100 credits! Come back tomorrow for more.', 'credits.php', 0, '2025-06-07 03:22:36'),
(226, 48, 'You’ve claimed your daily reward of 100 credits! Come back tomorrow for more.', 'credits.php', 1, '2025-06-07 14:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `demon_password_history`
--

CREATE TABLE `demon_password_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_password_history`
--

INSERT INTO `demon_password_history` (`id`, `user_id`, `password_hash`, `created_at`) VALUES
(1, 1, '$2y$12$e0MYzXyjpJS7Pd0RVvHwHeFXe2hx2VnMatYmFl55j6fI0Vj9E./a', '2025-06-03 20:08:21');

-- --------------------------------------------------------

--
-- Table structure for table `demon_password_resets`
--

CREATE TABLE `demon_password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reset_token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_quests`
--

CREATE TABLE `demon_quests` (
  `id` int(10) UNSIGNED NOT NULL,
  `quest_key` varchar(100) NOT NULL COMMENT 'Machine‐readable key, e.g. "complete_profile", "refer_3_friends"',
  `name` varchar(100) NOT NULL COMMENT 'Human‐readable title',
  `description` text NOT NULL COMMENT 'Detailed instructions for the user',
  `long_description` text DEFAULT NULL,
  `reward_amount` int(11) NOT NULL COMMENT 'Credits to award upon completion',
  `is_repeatable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = one‐time only, 1 = can be earned once per user per trigger',
  `threshold_count` int(11) NOT NULL DEFAULT 50 COMMENT 'Number of units required to complete this quest',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=disabled, 1=enabled',
  `extra_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'optionally store more metadata' CHECK (json_valid(`extra_parameters`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_quests`
--

INSERT INTO `demon_quests` (`id`, `quest_key`, `name`, `description`, `long_description`, `reward_amount`, `is_repeatable`, `threshold_count`, `created_at`, `updated_at`, `is_active`, `extra_parameters`) VALUES
(1, 'complete_profile', 'Complete Your Profile', 'Fill out your first name, last name, and upload a profile picture, about me, date of birth, gender, cover photo', NULL, 50, 0, 1, '2025-06-06 06:46:36', '2025-06-06 11:49:21', 1, NULL),
(7, 'chat_50_times', 'Chat 50 Times', 'Post 50 messages in the chat to earn credits.', NULL, 25, 0, 50, '2025-06-06 11:44:56', '2025-06-06 11:51:40', 1, NULL),
(8, 'spin_wheel', 'Spin the Wheel', 'Spin once per day (or use extra spins) to earn credits', NULL, 0, 1, 50, '2025-06-06 12:01:14', '2025-06-06 12:01:14', 1, NULL),
(9, 'follow_5_users', 'Get Out There: Follow 5 Users', 'Follow five different users in the community.', NULL, 40, 0, 5, '2025-06-06 14:02:27', '2025-06-06 14:02:27', 1, NULL),
(10, 'be_followed_10', 'Become Popular: 10 Followers', 'Get ten different users to follow you.', NULL, 75, 0, 10, '2025-06-06 14:02:27', '2025-06-06 14:02:27', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `demon_referrals`
--

CREATE TABLE `demon_referrals` (
  `id` int(10) UNSIGNED NOT NULL,
  `referrer_id` int(10) UNSIGNED NOT NULL COMMENT 'Who did the inviting?',
  `referred_user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Filled once they register',
  `referral_code` varchar(50) NOT NULL COMMENT 'Copy of referrer’s code at time of invite',
  `invited_email` varchar(255) DEFAULT NULL COMMENT 'Email address you sent the invitation to',
  `status` enum('pending','registered','credited') NOT NULL DEFAULT 'pending' COMMENT '\r\n       ​pending   → Email sent, but user has not clicked/ref’d link yet  \r\n       registered → They signed up via this code (but maybe haven’t done the “first action” you award)  \r\n       credited   → You gave the referral bonus to the referrer\r\n     ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `credited_at` timestamp NULL DEFAULT NULL COMMENT 'When you actually granted the referral bonus'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_referrals`
--

INSERT INTO `demon_referrals` (`id`, `referrer_id`, `referred_user_id`, `referral_code`, `invited_email`, `status`, `created_at`, `credited_at`) VALUES
(8, 1, 47, 'b0803499', 'Refertest1@gmail.com', 'credited', '2025-06-05 16:22:39', '2025-06-05 16:23:08'),
(9, 48, 51, '1F6AD784', 'someonelovedie@gmail.com', 'credited', '2025-06-05 19:33:08', '2025-06-05 19:38:39'),
(10, 48, 52, '1F6AD784', 'resultingg@outlook.com', 'credited', '2025-06-05 19:35:29', '2025-06-05 19:36:42'),
(11, 48, 54, '1F6AD784', 'kevin.willy31@gmail.com', 'credited', '2025-06-05 20:31:30', '2025-06-05 20:32:03'),
(12, 48, 55, '1F6AD784', 'Gealuvuh115@gmail.com', 'credited', '2025-06-05 23:16:25', '2025-06-05 23:17:08'),
(13, 48, 56, '1F6AD784', 'blueyaaron@gmail.com', 'credited', '2025-06-06 08:31:08', '2025-06-06 08:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `demon_registration_logs`
--

CREATE TABLE `demon_registration_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL COMMENT 'IPv4 or IPv6',
  `email` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_registration_logs`
--

INSERT INTO `demon_registration_logs` (`id`, `ip_address`, `email`, `user_agent`, `referrer`, `created_at`) VALUES
(1, '127.0.0.1', 'admin@example.com', 'initial-setup', NULL, '2025-06-03 20:08:14'),
(5, '95.146.14.61', 'owencroft0@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-03 23:16:51'),
(6, '95.146.14.61', 'owencroft0@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-03 23:17:42'),
(7, '95.146.14.61', 'test@test.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 13:44:38'),
(8, '95.146.14.61', 'zdrgzdfg@zdrgzdfg.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 14:33:14'),
(9, '95.146.14.61', 'ssssss@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 14:41:49'),
(10, '95.146.14.61', 'hgftyhg@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 14:49:51'),
(12, '95.146.14.61', '123annamai@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 21:27:20'),
(13, '95.146.14.61', '123annamai@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-04 21:33:40'),
(14, '188.29.111.104', 'shiddydev@gmail.com', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'https://bo3.coddb.net/register.php', '2025-06-05 00:03:04'),
(15, '188.29.111.104', 'shidouridazzle@gmail.com', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'https://bo3.coddb.net/register.php', '2025-06-05 00:27:04'),
(16, '68.229.23.143', 'joker32789@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-05 09:21:35'),
(17, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:34:43'),
(18, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:40:49'),
(19, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:43:45'),
(20, '95.146.14.61', 'hthfdtgh@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:50:34'),
(21, '95.146.14.61', 'dfgbzdfbzxdfnbzdn@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:53:50'),
(22, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 15:59:23'),
(27, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 16:12:08'),
(28, '95.146.14.61', 'Refertest1@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'https://bo3.coddb.net/register.php?ref=b0803499', '2025-06-05 16:22:39'),
(29, '95.146.14.61', 'owencroft0@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-05 16:42:52'),
(30, '95.146.14.61', 'owencroft137@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-05 19:02:39'),
(31, '95.146.14.61', 'owencroft137@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-05 19:26:03'),
(32, '178.72.88.86', 'someonelovedie@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php?ref=1F6AD784', '2025-06-05 19:33:08'),
(33, '76.50.120.215', 'resultingg@outlook.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php?ref=1F6AD784', '2025-06-05 19:35:29'),
(34, '95.146.14.61', 'owencroft137@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-05 19:35:33'),
(35, '172.59.99.34', 'kevin.willy31@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php?ref=1F6AD784', '2025-06-05 20:31:30'),
(36, '24.180.183.108', 'Gealuvuh115@gmail.com', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', 'https://bo3.coddb.net/register.php?ref=1F6AD784', '2025-06-05 23:16:25'),
(37, '193.37.32.11', 'blueyaaron@gmail.com', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'https://bo3.coddb.net/register.php?ref=1F6AD784', '2025-06-06 08:31:08'),
(38, '176.93.254.33', 'Konaukko1337@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'https://bo3.coddb.net/register.php', '2025-06-07 03:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `demon_roles`
--

CREATE TABLE `demon_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(50) NOT NULL COMMENT 'e.g. "admin", "registered", "subscriber"',
  `description` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'admin user_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_roles`
--

INSERT INTO `demon_roles` (`id`, `level`, `name`, `description`, `permissions`, `is_default`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 5, 'admin', 'Full administrative access', NULL, 0, 1, '2025-06-03 19:38:38', '2025-06-04 06:43:28'),
(2, 2, 'Registered', 'Default role for newly registered users', NULL, 0, 1, '2025-06-03 19:38:38', '2025-06-04 14:54:44'),
(3, 1, 'Subscriber', 'Limited access, can log in but few privileges', NULL, 0, 1, '2025-06-03 19:38:38', '2025-06-04 14:54:39'),
(4, 3, 'Premium', 'Premium member with extra perks', NULL, 0, 1, '2025-06-04 06:40:05', '2025-06-04 14:54:36'),
(5, 4, 'Moderator', 'Can moderate content and users', NULL, 0, 1, '2025-06-04 06:40:05', '2025-06-04 14:54:33'),
(6, 0, 'Banned', 'Users who are banned have no access', NULL, 0, 1, '2025-06-04 14:24:30', '2025-06-04 14:54:30'),
(7, 6, 'Awaiting Activation', 'Users who have registered but not yet activated their account', NULL, 0, 1, '2025-06-04 14:37:31', '2025-06-04 14:54:23'),
(8, 7, 'Donator', 'New role for anyone who donates', NULL, 0, 1, '2025-06-07 11:49:07', '2025-06-07 11:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `demon_shop_items`
--

CREATE TABLE `demon_shop_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Short name of the item, e.g. "Sword of Light"',
  `description` text DEFAULT NULL COMMENT 'Longer description or tooltip text',
  `price` bigint(20) UNSIGNED NOT NULL COMMENT 'Cost in credits to buy one unit',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Optional image (e.g. "/uploads/items/sword.png")',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = hidden/unavailable, 1 = visible in shop',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_shop_purchases`
--

CREATE TABLE `demon_shop_purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `price` bigint(20) UNSIGNED NOT NULL,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_site_settings`
--

CREATE TABLE `demon_site_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT '',
  `site_tagline` varchar(255) NOT NULL DEFAULT '',
  `site_description` text DEFAULT NULL,
  `site_author` varchar(255) NOT NULL DEFAULT '',
  `site_robots` varchar(255) NOT NULL DEFAULT 'index, follow',
  `mail_driver` varchar(50) NOT NULL DEFAULT 'smtp',
  `smtp_host` varchar(255) NOT NULL DEFAULT '',
  `smtp_port` smallint(6) NOT NULL DEFAULT 587,
  `smtp_encryption` varchar(10) NOT NULL DEFAULT 'tls',
  `smtp_user` varchar(255) NOT NULL DEFAULT '',
  `smtp_pass` varchar(255) NOT NULL DEFAULT '',
  `mail_from_address` varchar(255) NOT NULL DEFAULT '',
  `mail_from_name` varchar(255) NOT NULL DEFAULT '',
  `mail_reply_to` varchar(255) NOT NULL DEFAULT '',
  `allow_registration` tinyint(1) NOT NULL DEFAULT 1,
  `require_email_confirm` tinyint(1) NOT NULL DEFAULT 1,
  `default_user_role` varchar(50) NOT NULL DEFAULT 'registered',
  `password_min_length` tinyint(3) NOT NULL DEFAULT 8,
  `account_expiry_days` smallint(6) NOT NULL DEFAULT 0,
  `max_accounts_per_ip` smallint(6) NOT NULL DEFAULT 10,
  `captcha_site_key` varchar(255) NOT NULL DEFAULT '',
  `captcha_secret_key` varchar(255) NOT NULL DEFAULT '',
  `force_https` tinyint(1) NOT NULL DEFAULT 0,
  `csp_policy` text DEFAULT NULL,
  `hsts_max_age` int(11) NOT NULL DEFAULT 0,
  `session_timeout` smallint(6) NOT NULL DEFAULT 30,
  `enable_2fa` tinyint(1) NOT NULL DEFAULT 0,
  `ip_whitelist` text DEFAULT NULL,
  `ip_blacklist` text DEFAULT NULL,
  `ga_tracking_id` varchar(50) NOT NULL DEFAULT '',
  `gtm_id` varchar(50) NOT NULL DEFAULT '',
  `chat_widget_code` text DEFAULT NULL,
  `social_facebook` varchar(255) NOT NULL DEFAULT '',
  `social_twitter` varchar(255) NOT NULL DEFAULT '',
  `social_linkedin` varchar(255) NOT NULL DEFAULT '',
  `enable_page_cache` tinyint(1) NOT NULL DEFAULT 0,
  `cache_duration` int(11) NOT NULL DEFAULT 300,
  `cdn_base_url` varchar(255) NOT NULL DEFAULT '',
  `terms_of_service_url` varchar(512) NOT NULL DEFAULT '',
  `privacy_policy_url` varchar(512) NOT NULL DEFAULT '',
  `welcome_email_subject` varchar(255) NOT NULL DEFAULT '',
  `welcome_email_body` text DEFAULT NULL,
  `reset_password_email_subject` varchar(255) NOT NULL DEFAULT '',
  `reset_password_email_body` text DEFAULT NULL,
  `logo_url` varchar(512) NOT NULL DEFAULT '',
  `favicon_url` varchar(512) NOT NULL DEFAULT '',
  `admin_email` varchar(255) NOT NULL DEFAULT '',
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `default_language` varchar(10) NOT NULL DEFAULT 'en_US',
  `site_url` varchar(512) NOT NULL DEFAULT '',
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `maintenance_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_site_settings`
--

INSERT INTO `demon_site_settings` (`id`, `site_name`, `site_tagline`, `site_description`, `site_author`, `site_robots`, `mail_driver`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_user`, `smtp_pass`, `mail_from_address`, `mail_from_name`, `mail_reply_to`, `allow_registration`, `require_email_confirm`, `default_user_role`, `password_min_length`, `account_expiry_days`, `max_accounts_per_ip`, `captcha_site_key`, `captcha_secret_key`, `force_https`, `csp_policy`, `hsts_max_age`, `session_timeout`, `enable_2fa`, `ip_whitelist`, `ip_blacklist`, `ga_tracking_id`, `gtm_id`, `chat_widget_code`, `social_facebook`, `social_twitter`, `social_linkedin`, `enable_page_cache`, `cache_duration`, `cdn_base_url`, `terms_of_service_url`, `privacy_policy_url`, `welcome_email_subject`, `welcome_email_body`, `reset_password_email_subject`, `reset_password_email_body`, `logo_url`, `favicon_url`, `admin_email`, `timezone`, `default_language`, `site_url`, `maintenance_mode`, `maintenance_message`, `created_at`, `updated_at`) VALUES
(1, 'CODDB', 'Call of duty repo', 'This is a test', 'Owen', 'index, follow', 'smtp', 'bo3.coddb.net', 465, 'ssl', 'support@bo3.coddb.net', 'demongenemailpass1234?!', 'support@bo3.coddb.net', 'DemonGen Team', 'support@bo3.coddb.net', 1, 1, 'Banned', 8, 0, 10, '6LdiS1UrAAAAACGv94ZBEg61_xD3nbNML39siFyJ', '6LdiS1UrAAAAAGgRTOI5qO2JvM0VSULIEI-MhPd6', 1, '', 0, 30, 0, '', '', '', '', '', '', '', '', 0, 300, '', '', '', '', '', '', '', '/assets/demongen_logo.png', '/assets/demongen_favicon.png', '', '', 'en_US', 'https://bo3.coddb.net/', 1, '', '2025-06-03 19:09:30', '2025-06-05 09:27:29');

-- --------------------------------------------------------

--
-- Table structure for table `demon_spin_history`
--

CREATE TABLE `demon_spin_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `quest_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to demon_quests if you want to tie spins to a “wheel” quest',
  `prize_key` varchar(50) NOT NULL,
  `reward_amount` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_spin_history`
--

INSERT INTO `demon_spin_history` (`id`, `user_id`, `quest_id`, `prize_key`, `reward_amount`, `created_at`) VALUES
(1, 48, NULL, '6', 0, '2025-06-06 08:34:14'),
(2, 48, NULL, '3', 20, '2025-06-06 08:34:55'),
(3, 48, NULL, '1', 5, '2025-06-06 08:35:06'),
(4, 48, NULL, '4', 50, '2025-06-06 08:35:14'),
(5, 31, NULL, '4', 50, '2025-06-06 08:45:44'),
(6, 48, NULL, '1', 5, '2025-06-06 09:03:50'),
(7, 31, NULL, '1', 5, '2025-06-06 09:04:23'),
(8, 31, NULL, '4', 50, '2025-06-06 09:04:36'),
(9, 31, NULL, '3', 20, '2025-06-06 09:04:46'),
(10, 31, NULL, '5', 100, '2025-06-06 09:04:57'),
(11, 31, NULL, '5', 100, '2025-06-06 09:05:08'),
(12, 31, NULL, '2', 10, '2025-06-06 09:05:16'),
(13, 31, NULL, '2', 10, '2025-06-06 09:05:23'),
(14, 31, NULL, '1', 5, '2025-06-06 09:05:30'),
(15, 31, NULL, '2', 10, '2025-06-06 09:05:36'),
(16, 31, NULL, '6', 0, '2025-06-06 09:05:43'),
(17, 48, NULL, '5', 100, '2025-06-06 09:11:11'),
(18, 48, NULL, '6', 0, '2025-06-06 09:11:18'),
(19, 48, NULL, '3', 20, '2025-06-06 09:21:15'),
(20, 48, NULL, '5', 100, '2025-06-06 09:21:23'),
(21, 57, NULL, '6', 0, '2025-06-06 23:22:39'),
(22, 48, NULL, '3', 20, '2025-06-07 10:23:52');

-- --------------------------------------------------------

--
-- Table structure for table `demon_spin_segments`
--

CREATE TABLE `demon_spin_segments` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL COMMENT 'Text shown on the wheel slice',
  `value` int(11) NOT NULL DEFAULT 0 COMMENT 'Credits awarded when landed on this slice (0 = no reward)',
  `weight` int(11) NOT NULL DEFAULT 0 COMMENT 'Order/priority; lower weights render first'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_spin_segments`
--

INSERT INTO `demon_spin_segments` (`id`, `label`, `value`, `weight`) VALUES
(1, '5 Credits', 5, 10),
(2, '10 Credits', 10, 20),
(3, '20 Credits', 20, 30),
(4, '50 Credits', 50, 40),
(5, '100 Credits', 100, 50),
(6, 'Try Again', 0, 60);

-- --------------------------------------------------------

--
-- Table structure for table `demon_typing_status`
--

CREATE TABLE `demon_typing_status` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `last_typing_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_typing_status`
--

INSERT INTO `demon_typing_status` (`user_id`, `last_typing_at`) VALUES
(35, '2025-06-05 18:36:03'),
(31, '2025-06-06 08:53:13'),
(48, '2025-06-06 17:57:15');

-- --------------------------------------------------------

--
-- Table structure for table `demon_users`
--

CREATE TABLE `demon_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt/argon2 hash',
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT 2 COMMENT 'references demon_roles.id (default = registered)',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = not activated, 1 = active',
  `email_verified_at` datetime DEFAULT NULL,
  `status` enum('active','pending','suspended','banned','deleted') NOT NULL DEFAULT 'pending',
  `password_reset_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `login_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `mfa_backup_codes` text DEFAULT NULL,
  `pin_code_hash` varchar(255) DEFAULT NULL,
  `profile_picture_url` varchar(255) DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL,
  `referrer_id` int(10) UNSIGNED DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT NULL,
  `oauth_id` varchar(100) DEFAULT NULL,
  `email_change_token` varchar(100) DEFAULT NULL,
  `email_change_new` varchar(255) DEFAULT NULL,
  `email_change_expires` datetime DEFAULT NULL,
  `preferred_language` varchar(10) DEFAULT NULL,
  `preferred_timezone` varchar(100) DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `password_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`password_history`)),
  `activation_token` varchar(100) DEFAULT NULL COMMENT 'filled when email confirmation is required',
  `activation_expires` datetime DEFAULT NULL COMMENT 'expiry for activation token',
  `last_login` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL COMMENT 'for "remember me" functionality',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `credit_balance` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User’s current credit balance',
  `last_daily_reward` datetime DEFAULT NULL COMMENT 'Timestamp of last claimed daily credit',
  `last_daily_claim` date DEFAULT NULL COMMENT 'Date when daily reward was last claimed',
  `spin_allowance` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of spins this user may take immediately (decrements each spin)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_users`
--

INSERT INTO `demon_users` (`id`, `username`, `email`, `password_hash`, `role_id`, `is_active`, `email_verified_at`, `status`, `password_reset_attempts`, `login_attempts`, `lockout_until`, `two_factor_secret`, `two_factor_enabled`, `mfa_backup_codes`, `pin_code_hash`, `profile_picture_url`, `referral_code`, `referrer_id`, `oauth_provider`, `oauth_id`, `email_change_token`, `email_change_new`, `email_change_expires`, `preferred_language`, `preferred_timezone`, `last_password_change`, `password_history`, `activation_token`, `activation_expires`, `last_login`, `remember_token`, `created_at`, `updated_at`, `last_activity`, `credit_balance`, `last_daily_reward`, `last_daily_claim`, `spin_allowance`) VALUES
(1, 'admin', 'admin@example.com', '$2b$12$FkNrpRPIUF9rryKzhKPDGOAca.dhtcE/Pafbo8ItKgqoQnflOjsK.', 1, 1, '2025-06-03 16:04:02', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, '/assets/media/avatars/opm.png', 'b0803499', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-03 16:04:02', NULL, NULL, NULL, '2025-06-05 11:54:23', NULL, '2025-06-03 20:02:58', '2025-06-05 16:41:40', '2025-06-05 12:41:40', 250, NULL, '2025-06-05', 0),
(31, 'AnnaMai', '123annamai@gmail.com', '$2y$10$rUyy6BAGkcUxOf1pURFPDO5jpB3eqPn0.E2s2KP/azzZEHjP4eaEu', 2, 1, '2025-06-04 17:34:35', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, '/assets/media/avatars/dva.png', 'b08037ca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-07 13:12:02', NULL, '2025-06-04 21:33:40', '2025-06-07 17:12:02', '2025-06-07 13:12:02', 685, NULL, '2025-06-06', 0),
(33, 'shiddy', 'shidouridazzle@gmail.com', '$2y$10$EnaYTdsl7.wm6Nt7HYBOXuEO8.XinD7CZtinBoykZtLwLWO3wH6TK', 2, 1, '2025-06-04 20:28:50', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'b0803895', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-04 20:29:03', NULL, '2025-06-05 00:27:04', '2025-06-05 15:28:11', NULL, 0, NULL, NULL, 0),
(34, 'TestingAccount', 'TestingAccount@gmail.com', '$2y$10$Lq4vNw1gbKcXr186ipYNJuncz.dl6KVZeoE7sEm8Z9ZUSRThYqweK', 1, 1, NULL, 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'b0803932', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-04 20:51:08', NULL, '2025-06-05 00:51:01', '2025-06-05 15:32:46', '2025-06-05 11:32:46', 0, NULL, NULL, 0),
(35, 'Simpy', 'joker32789@gmail.com', '$2y$10$0syk5pudQQE/h8wDeC7O0ur.YHzcOCBoYLdtAUBp8w8AO51lATGqC', 1, 1, '2025-06-05 05:22:51', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'b08039bb', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 05:35:58', NULL, '2025-06-05 09:21:35', '2025-06-05 22:44:28', '2025-06-05 18:44:28', 0, NULL, NULL, 0),
(47, 'Refertest1', 'Refertest1@gmail.com', '$2y$10$SRPAJBLOi2GlHWgScSyabuCP6vDUyWDjRIVrYPWhY0/euy/QLc27e', 2, 1, '2025-06-05 12:23:08', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '06C75761', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 16:22:39', '2025-06-05 16:23:08', NULL, 0, NULL, NULL, 0),
(48, 'OwenC137', 'owencroft0@gmail.com', '$2y$10$LtouHr0Gv7/fdG0wUe/vO.14/sqRvVcRmjAFH0E8zjw7r4zDODK3C', 1, 1, '2025-06-05 12:43:35', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '1F6AD784', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-07 13:03:58', NULL, '2025-06-05 16:42:52', '2025-06-07 18:09:33', '2025-06-07 14:09:33', 1575, NULL, '2025-06-07', 0),
(51, 'Sti444', 'someonelovedie@gmail.com', '$2y$10$wjvYbblHO0ZSklo3Zkq3ie9M.K/fYomtwxzQSSgPKfw7j0KEaMzeS', 2, 1, '2025-06-05 15:38:39', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '1AF8661B', 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 15:39:18', NULL, '2025-06-05 19:33:08', '2025-06-06 02:36:12', '2025-06-05 22:36:12', 0, NULL, NULL, 0),
(52, 'vcdiff', 'resultingg@outlook.com', '$2y$10$VaJwd4Sta71ANJkLSECsg.eYBPAlA9xoEcnLTuDzl.Q8yhz4PeWgK', 2, 1, '2025-06-05 15:36:42', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '2F35C7A0', 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 15:36:51', NULL, '2025-06-05 19:35:29', '2025-06-05 20:11:57', '2025-06-05 16:11:57', 0, NULL, NULL, 0),
(53, 'owencroft137', 'owencroft137@gmail.com', '$2y$10$lepyIumayiHhAO2ytfnRquPdOSKTOqsvIoIwzOhl19rigegLByZAa', 2, 1, '2025-06-05 15:35:58', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'B17052F3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 19:35:33', '2025-06-05 19:35:58', NULL, 0, NULL, NULL, 0),
(54, 'Accurzzy', 'kevin.willy31@gmail.com', '$2y$10$bh5q1NmP5.kOJWmS5x394ejptFgoJaWoNwi1SUUo7ZrWxQdFDSBCC', 2, 1, '2025-06-05 16:32:03', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '9AD1E444', 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 16:32:13', NULL, '2025-06-05 20:31:30', '2025-06-05 20:35:17', '2025-06-05 16:35:17', 100, NULL, '2025-06-05', 0),
(55, 'Gealuvuh', 'Gealuvuh115@gmail.com', '$2y$10$PokYud4jn8OrEREZIz8fmOtZZa.85DsRwPUerqEj0fv9v7Pv8Axnm', 2, 1, '2025-06-05 19:17:08', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'B3D01164', 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-07 13:38:27', NULL, '2025-06-05 23:16:25', '2025-06-07 17:38:34', '2025-06-07 13:38:34', 0, NULL, NULL, 0),
(56, 'AUniqueBot', 'blueyaaron@gmail.com', '$2y$10$lRikdMcEGxH4WbyEpZJ3Me413PcOVL3EUOPXLOcnniEECYAq.beZq', 2, 1, '2025-06-06 04:32:21', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, '00713893', 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-06 04:32:44', NULL, '2025-06-06 08:31:08', '2025-06-06 08:32:44', '2025-06-06 04:32:44', 0, NULL, NULL, 0),
(57, 'Timppa', 'Konaukko1337@gmail.com', '$2y$10$eNehGPA1XJ4CMhnZMbpfveN4hYTvgDfTcHEs9NFyTdlOlipaJvpre', 2, 1, '2025-06-06 23:19:46', 'active', 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 'BFD84BDE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-06 23:20:10', NULL, '2025-06-07 03:18:51', '2025-06-07 03:23:07', '2025-06-06 23:23:07', 100, NULL, '2025-06-07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_profiles`
--

CREATE TABLE `demon_user_profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL COMMENT 'User''s personal timezone',
  `locale` varchar(10) DEFAULT NULL,
  `newsletter_subscribed` tinyint(1) NOT NULL DEFAULT 0,
  `discord_username` varchar(100) DEFAULT NULL COMMENT 'e.g. DiscordUser#1234',
  `website_url` varchar(255) DEFAULT NULL COMMENT 'Personal website or blog',
  `steam_profile_url` varchar(255) DEFAULT NULL COMMENT 'Full URL to Steam profile',
  `github_username` varchar(100) DEFAULT NULL COMMENT 'GitHub handle only',
  `twitter_handle` varchar(100) DEFAULT NULL COMMENT 'Twitter username, e.g. @someone',
  `profile_picture_url` varchar(255) DEFAULT NULL COMMENT 'URL to avatar or profile pic',
  `about_me` text DEFAULT NULL COMMENT 'Freeform bio or description',
  `date_of_birth` date DEFAULT NULL COMMENT 'User''s birthdate',
  `gender` varchar(20) DEFAULT NULL COMMENT 'Optional: male, female, other, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_profile_update` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `cover_photo_url` varchar(255) DEFAULT NULL,
  `followers_only` tinyint(1) NOT NULL DEFAULT 0,
  `cover_position` tinyint(3) UNSIGNED NOT NULL DEFAULT 50 COMMENT 'Vertical offset % for cover (0 = top, 100 = bottom)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_user_profiles`
--

INSERT INTO `demon_user_profiles` (`user_id`, `first_name`, `last_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `timezone`, `locale`, `newsletter_subscribed`, `discord_username`, `website_url`, `steam_profile_url`, `github_username`, `twitter_handle`, `profile_picture_url`, `about_me`, `date_of_birth`, `gender`, `created_at`, `updated_at`, `last_profile_update`, `metadata`, `is_public`, `cover_photo_url`, `followers_only`, `cover_position`) VALUES
(1, 'Owen', 'Croft', NULL, NULL, NULL, '', NULL, NULL, '', NULL, NULL, 0, 'Discord Test', 'https://coddb.com', 'https://SteamTest.com', 'github test', '@TwitterTest', '/assets/media/avatars/avatar_1_1749076552_189bf8a5.png', 'This is a test', '1998-05-27', 'Male', '2025-06-03 20:02:58', '2025-06-05 13:38:01', '2025-06-03 20:07:17', NULL, 1, '/assets/media/covers/cover_1_1749130681_5d4842bb.jpg', 0, 50),
(31, 'Anna', 'Finney', NULL, NULL, NULL, 'mine', NULL, NULL, 'mine', NULL, NULL, 0, '', '', '', '', '', '/assets/media/avatars/avatar_31_1749214085_dcc4427e.png', 'I have a pink chair', '1989-06-01', 'Female', '2025-06-04 21:33:40', '2025-06-06 12:48:05', NULL, NULL, 1, '/assets/media/covers/file_283x178_003132.jpg', 0, 50),
(33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 00:27:04', '2025-06-05 00:27:04', NULL, NULL, 1, NULL, 0, 50),
(34, '', '', NULL, NULL, NULL, '', NULL, NULL, '', NULL, NULL, 0, '', '', '', '', '', '/assets/media/avatars/avatar_34_1749084713_0ed24b75.png', '', NULL, '', '2025-06-05 00:51:01', '2025-06-05 00:51:53', NULL, NULL, 1, '/assets/media/covers/cover_34_1749084713_8673a7d1.jfif', 0, 50),
(35, 'Simpy', 'The Simp', NULL, NULL, NULL, 'Las Vegas', NULL, NULL, 'USA', NULL, NULL, 0, 'imsimpy', '', 'https://steamcommunity.com/profiles/76561198867234115/', 'ImSimpy', '', '/assets/media/avatars/avatar_35_1749163116_53bfe4a0.png', 'Just a Guy who is a 3D Artist and A Modder that loves Game Assets....', '2002-06-01', 'Male', '2025-06-05 09:21:35', '2025-06-05 22:38:36', NULL, NULL, 1, '/assets/media/covers/cover_35_1749163009_874eb1ff.gif', 0, 50),
(47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 16:22:39', '2025-06-05 16:22:39', NULL, NULL, 1, NULL, 0, 50),
(48, 'Owen', 'Croft', NULL, NULL, NULL, 'Non ya business', NULL, NULL, 'Uk', NULL, NULL, 0, 'owenc137', 'https://bo3.coddb.net/', 'https://steamcommunity.com/id/owenc137/', 'N/A', 'N/A', '/assets/media/avatars/avatar_48_1749194615_741a59d8.png', 'Bleh', '1998-05-27', 'Male', '2025-06-05 16:42:52', '2025-06-06 21:57:34', NULL, NULL, 1, '/assets/media/covers/cover_48_1749142560_62795253.png', 0, 50),
(51, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 19:33:08', '2025-06-05 19:33:08', NULL, NULL, 1, NULL, 0, 50),
(52, '', '', NULL, NULL, NULL, '', NULL, NULL, '', NULL, NULL, 0, '', '', '', '', '', '', '', NULL, '', '2025-06-05 19:35:29', '2025-06-05 20:11:54', NULL, NULL, 0, '', 0, 50),
(53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 19:35:33', '2025-06-05 19:35:33', NULL, NULL, 1, NULL, 0, 50),
(54, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-05 20:31:30', '2025-06-05 20:31:30', NULL, NULL, 1, NULL, 0, 50),
(55, '', '', NULL, NULL, NULL, '', NULL, NULL, 'United States', NULL, NULL, 0, 'Gealuvuh.', '', 'https://steamcommunity.com/profiles/76561199237883952', 'Gealuvuh', '@Gealuvuh', '/assets/media/avatars/avatar_55_1749165732_fa154ed5.jpeg', 'not much', NULL, 'Male', '2025-06-05 23:16:25', '2025-06-05 23:33:33', NULL, NULL, 1, '/assets/media/covers/cover_55_1749166208_9b7379f7.jpeg', 0, 50),
(56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-06 08:31:08', '2025-06-06 08:31:08', NULL, NULL, 1, NULL, 0, 50),
(57, '', '', NULL, NULL, NULL, '', NULL, NULL, '', NULL, NULL, 0, 'timppaw', '', '', '', '', '/assets/media/avatars/avatar_57_1749266478_f425d4dc.jpg', '', NULL, '', '2025-06-07 03:18:51', '2025-06-07 03:21:18', NULL, NULL, 1, '', 0, 50);

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_profile_posts`
--

CREATE TABLE `demon_user_profile_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_user_profile_posts`
--

INSERT INTO `demon_user_profile_posts` (`id`, `user_id`, `title`, `message`, `created_at`, `updated_at`) VALUES
(9, 31, 'kkk', 'ljkni;jn', '2025-06-04 23:43:47', '2025-06-04 23:43:47'),
(15, 34, 'sss', 'sssss', '2025-06-05 00:54:15', '2025-06-05 00:54:15'),
(18, 31, 'yuy', 'blah ba dee bloo', '2025-06-05 01:06:14', '2025-06-05 01:06:14'),
(22, 34, 'AWDAWDAWD', 'AAWFAFAWF', '2025-06-05 01:22:01', '2025-06-05 01:22:01'),
(25, 34, 'ssssssss', 'ssssssssssss', '2025-06-05 01:23:05', '2025-06-05 01:23:05'),
(26, 31, 'blib', 'blob', '2025-06-05 01:23:31', '2025-06-05 01:23:31'),
(28, 31, 'jipjip', 'jipjippji', '2025-06-05 01:30:36', '2025-06-05 01:30:36'),
(31, 31, 'kl; mkpol\'', 'mlp\'mlp\',mlp\r\n]', '2025-06-05 01:34:14', '2025-06-05 01:34:14'),
(39, 1, 'ss', 'sssss', '2025-06-05 14:02:15', '2025-06-05 14:02:15'),
(40, 1, 'dthgddddddddd', 'dthxfth', '2025-06-05 16:30:18', '2025-06-05 16:30:21'),
(41, 48, 'My First Post', 'This is my first profile post.', '2025-06-05 18:49:36', '2025-06-05 18:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_profile_reacts`
--

CREATE TABLE `demon_user_profile_reacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `react_type` enum('like','love','laugh','angry') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_user_profile_reacts`
--

INSERT INTO `demon_user_profile_reacts` (`id`, `post_id`, `user_id`, `react_type`, `created_at`) VALUES
(21, 26, 1, 'angry', '2025-06-05 12:54:12'),
(22, 18, 1, 'like', '2025-06-05 12:55:32'),
(23, 28, 1, 'love', '2025-06-05 12:55:33'),
(25, 31, 1, 'angry', '2025-06-05 13:04:09'),
(26, 9, 1, 'love', '2025-06-05 13:33:48');

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_purchases`
--

CREATE TABLE `demon_user_purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_price` bigint(20) NOT NULL COMMENT 'Snapshot of (price * quantity) at time of purchase',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_quests`
--

CREATE TABLE `demon_user_quests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → demon_users.id',
  `quest_key` varchar(100) NOT NULL,
  `quest_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → demon_quests.id',
  `progress_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'How many units of progress have been achieved (if applicable)',
  `is_completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = not yet completed, 1 = completed and reward given',
  `completed_at` datetime DEFAULT NULL COMMENT 'Timestamp when quest was completed',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demon_user_quests`
--

INSERT INTO `demon_user_quests` (`id`, `user_id`, `quest_key`, `quest_id`, `progress_count`, `is_completed`, `completed_at`, `last_updated`) VALUES
(8, 48, 'complete_profile', 1, 1, 1, '2025-06-06 07:45:08', '2025-06-06 11:45:08'),
(10, 48, 'chat_50_times', 7, 2, 0, NULL, '2025-06-06 21:57:16'),
(11, 48, 'spin_wheel', 8, 5, 1, '2025-06-07 10:24:08', '2025-06-07 14:24:08'),
(12, 31, 'spin_wheel', 8, 3, 1, '2025-06-06 09:05:54', '2025-06-06 13:05:54'),
(13, 31, 'complete_profile', 1, 1, 1, '2025-06-06 08:48:11', '2025-06-06 12:48:11'),
(14, 31, 'chat_50_times', 7, 50, 1, '2025-06-06 08:53:11', '2025-06-06 12:53:11'),
(15, 48, 'follow_5_users', 9, 1, 0, NULL, '2025-06-07 18:09:33'),
(16, 48, 'be_followed_10', 10, 1, 0, NULL, '2025-06-07 18:09:33'),
(17, 57, 'follow_5_users', 9, 0, 0, NULL, '2025-06-07 03:27:14'),
(18, 57, 'be_followed_10', 10, 0, 0, NULL, '2025-06-07 03:27:14'),
(19, 57, 'spin_wheel', 8, 1, 1, '2025-06-06 23:23:02', '2025-06-07 03:23:02'),
(20, 31, 'follow_5_users', 9, 1, 0, NULL, '2025-06-07 17:12:02'),
(21, 31, 'be_followed_10', 10, 1, 0, NULL, '2025-06-07 17:12:02'),
(22, 55, 'follow_5_users', 9, 0, 0, NULL, '2025-06-07 17:38:34'),
(23, 55, 'be_followed_10', 10, 0, 0, NULL, '2025-06-07 17:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_roles`
--

CREATE TABLE `demon_user_roles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_user_roles`
--

INSERT INTO `demon_user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(31, 7),
(33, 7),
(35, 7),
(47, 7),
(48, 7),
(51, 7),
(52, 7),
(53, 7),
(54, 7),
(55, 7),
(56, 7),
(57, 7);

-- --------------------------------------------------------

--
-- Table structure for table `demon_user_sessions`
--

CREATE TABLE `demon_user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `session_type` varchar(50) NOT NULL DEFAULT 'remember_me',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demon_user_sessions`
--

INSERT INTO `demon_user_sessions` (`id`, `user_id`, `session_token`, `session_type`, `ip_address`, `user_agent`, `created_at`, `expires_at`) VALUES
(8, 33, '7068eb74e14b1461287b9fa21374393bd304d4723cbcb2f8c3ddaaa87648e685', 'remember_me', '188.29.111.104', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-06-05 00:29:03', '2025-07-05 00:29:03'),
(13, 35, '3feae604b593dbc70d66baf28442a4ca2660becac81cc3ee2bba1246bc24508c', 'remember_me', '68.229.23.143', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-05 09:35:58', '2025-07-05 09:35:58'),
(27, 51, 'ed1d279061d17f1c1930d4149656414bedc0e4f974b3d691afd3b7a3687b480b', 'remember_me', '178.72.88.86', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-05 19:39:18', '2025-07-05 19:39:18'),
(28, 48, '1ec732f97c857ad44ad767c7aedd976b365094a3134018dbf3900edc33141b86', 'remember_me', '95.146.14.61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-05 19:43:10', '2025-07-05 19:43:10'),
(29, 54, '9080e0bddec4bb8c7662c97d0d87c91e01794989cc48493c6add9ecf14bdf8c0', 'remember_me', '172.59.99.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-05 20:32:13', '2025-07-05 20:32:13'),
(33, 56, '1b4eed6b1d4276695d9529f4052ef236abc389957f57661c9fb5966a7ba38e4a', 'remember_me', '193.37.32.44', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-06-06 08:32:44', '2025-07-06 08:32:44'),
(41, 57, '6dc7207318bdcb86616c8fad7d336556a6a0f3e4af8156b7e7db534735d78f55', 'remember_me', '176.93.254.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-07 03:20:10', '2025-07-07 03:20:10'),
(44, 48, 'eaf96ccd6f5ceda3291cbae24b6afa71190360b4d0b375e887a9d722f94e600c', 'remember_me', '95.146.14.61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-07 17:03:58', '2025-07-07 17:03:58'),
(46, 31, '76a845fc99cdb2480aba37aa85cd317ce5543245cd310d8f66e3f1806cb0032c', 'remember_me', '95.146.14.61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-07 17:12:02', '2025-07-07 17:12:02'),
(47, 55, 'c2050095326fd13126a89639cede3bed0c142462b59dbb8f5dc7a500bbbe85d7', 'remember_me', '24.180.183.108', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', '2025-06-07 17:38:27', '2025-07-07 17:38:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `demon_audit_logs`
--
ALTER TABLE `demon_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`);

--
-- Indexes for table `demon_chat_settings`
--
ALTER TABLE `demon_chat_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `demon_credit_logs`
--
ALTER TABLE `demon_credit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demon_credit_transactions`
--
ALTER TABLE `demon_credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_credit_tx_user` (`user_id`);

--
-- Indexes for table `demon_donations`
--
ALTER TABLE `demon_donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donations_user_id` (`user_id`);

--
-- Indexes for table `demon_follows`
--
ALTER TABLE `demon_follows`
  ADD PRIMARY KEY (`user_id`,`follower_id`),
  ADD KEY `idx_follower` (`follower_id`);

--
-- Indexes for table `demon_login_attempts`
--
ALTER TABLE `demon_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_user` (`user_id`);

--
-- Indexes for table `demon_messages`
--
ALTER TABLE `demon_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `is_pinned` (`is_pinned`);

--
-- Indexes for table `demon_message_reactions`
--
ALTER TABLE `demon_message_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_msg_user_reaction` (`message_id`,`user_id`,`reaction`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demon_notifications`
--
ALTER TABLE `demon_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `demon_password_history`
--
ALTER TABLE `demon_password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ph_user` (`user_id`);

--
-- Indexes for table `demon_password_resets`
--
ALTER TABLE `demon_password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `demon_quests`
--
ALTER TABLE `demon_quests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quest_key` (`quest_key`);

--
-- Indexes for table `demon_referrals`
--
ALTER TABLE `demon_referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_referrer` (`referrer_id`),
  ADD KEY `idx_referred_user` (`referred_user_id`),
  ADD KEY `idx_referral_code` (`referral_code`);

--
-- Indexes for table `demon_registration_logs`
--
ALTER TABLE `demon_registration_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_created` (`ip_address`,`created_at`);

--
-- Indexes for table `demon_roles`
--
ALTER TABLE `demon_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_roles_created_by` (`created_by`);

--
-- Indexes for table `demon_shop_items`
--
ALTER TABLE `demon_shop_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demon_shop_purchases`
--
ALTER TABLE `demon_shop_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `demon_site_settings`
--
ALTER TABLE `demon_site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demon_spin_history`
--
ALTER TABLE `demon_spin_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demon_spin_segments`
--
ALTER TABLE `demon_spin_segments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demon_typing_status`
--
ALTER TABLE `demon_typing_status`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `last_typing_at` (`last_typing_at`);

--
-- Indexes for table `demon_users`
--
ALTER TABLE `demon_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_activation_token` (`activation_token`),
  ADD KEY `fk_demon_users_referrer` (`referrer_id`);

--
-- Indexes for table `demon_user_profiles`
--
ALTER TABLE `demon_user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `demon_user_profile_posts`
--
ALTER TABLE `demon_user_profile_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demon_user_profile_reacts`
--
ALTER TABLE `demon_user_profile_reacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_post_user` (`post_id`,`user_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demon_user_purchases`
--
ALTER TABLE `demon_user_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_purchase_user` (`user_id`),
  ADD KEY `idx_purchase_item` (`item_id`);

--
-- Indexes for table `demon_user_quests`
--
ALTER TABLE `demon_user_quests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_user_quest` (`user_id`,`quest_id`),
  ADD UNIQUE KEY `user_quest_unique` (`user_id`,`quest_key`),
  ADD KEY `quest_id` (`quest_id`);

--
-- Indexes for table `demon_user_roles`
--
ALTER TABLE `demon_user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_ur_role` (`role_id`);

--
-- Indexes for table `demon_user_sessions`
--
ALTER TABLE `demon_user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `demon_audit_logs`
--
ALTER TABLE `demon_audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `demon_credit_logs`
--
ALTER TABLE `demon_credit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `demon_credit_transactions`
--
ALTER TABLE `demon_credit_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demon_donations`
--
ALTER TABLE `demon_donations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `demon_login_attempts`
--
ALTER TABLE `demon_login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `demon_messages`
--
ALTER TABLE `demon_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `demon_message_reactions`
--
ALTER TABLE `demon_message_reactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `demon_notifications`
--
ALTER TABLE `demon_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- AUTO_INCREMENT for table `demon_password_history`
--
ALTER TABLE `demon_password_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `demon_password_resets`
--
ALTER TABLE `demon_password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demon_quests`
--
ALTER TABLE `demon_quests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `demon_referrals`
--
ALTER TABLE `demon_referrals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `demon_registration_logs`
--
ALTER TABLE `demon_registration_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `demon_roles`
--
ALTER TABLE `demon_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `demon_shop_items`
--
ALTER TABLE `demon_shop_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demon_shop_purchases`
--
ALTER TABLE `demon_shop_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demon_site_settings`
--
ALTER TABLE `demon_site_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `demon_spin_history`
--
ALTER TABLE `demon_spin_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `demon_spin_segments`
--
ALTER TABLE `demon_spin_segments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `demon_users`
--
ALTER TABLE `demon_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `demon_user_profile_posts`
--
ALTER TABLE `demon_user_profile_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `demon_user_profile_reacts`
--
ALTER TABLE `demon_user_profile_reacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `demon_user_purchases`
--
ALTER TABLE `demon_user_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demon_user_quests`
--
ALTER TABLE `demon_user_quests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `demon_user_sessions`
--
ALTER TABLE `demon_user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `demon_audit_logs`
--
ALTER TABLE `demon_audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `demon_credit_transactions`
--
ALTER TABLE `demon_credit_transactions`
  ADD CONSTRAINT `fk_credit_tx_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_donations`
--
ALTER TABLE `demon_donations`
  ADD CONSTRAINT `fk_donations_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_follows`
--
ALTER TABLE `demon_follows`
  ADD CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_follows_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_login_attempts`
--
ALTER TABLE `demon_login_attempts`
  ADD CONSTRAINT `fk_la_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `demon_notifications`
--
ALTER TABLE `demon_notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_password_history`
--
ALTER TABLE `demon_password_history`
  ADD CONSTRAINT `fk_ph_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_password_resets`
--
ALTER TABLE `demon_password_resets`
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_referrals`
--
ALTER TABLE `demon_referrals`
  ADD CONSTRAINT `fk_referrals_referred_user` FOREIGN KEY (`referred_user_id`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_referrals_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_roles`
--
ALTER TABLE `demon_roles`
  ADD CONSTRAINT `fk_roles_created_by` FOREIGN KEY (`created_by`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `demon_shop_purchases`
--
ALTER TABLE `demon_shop_purchases`
  ADD CONSTRAINT `demon_shop_purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demon_shop_purchases_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `demon_shop_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_users`
--
ALTER TABLE `demon_users`
  ADD CONSTRAINT `fk_demon_users_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `demon_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `demon_roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `demon_user_profiles`
--
ALTER TABLE `demon_user_profiles`
  ADD CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_user_profile_posts`
--
ALTER TABLE `demon_user_profile_posts`
  ADD CONSTRAINT `fk_profile_posts_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_user_profile_reacts`
--
ALTER TABLE `demon_user_profile_reacts`
  ADD CONSTRAINT `fk_reacts_post` FOREIGN KEY (`post_id`) REFERENCES `demon_user_profile_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reacts_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_user_purchases`
--
ALTER TABLE `demon_user_purchases`
  ADD CONSTRAINT `fk_purchase_item` FOREIGN KEY (`item_id`) REFERENCES `demon_shop_items` (`id`),
  ADD CONSTRAINT `fk_purchase_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demon_user_quests`
--
ALTER TABLE `demon_user_quests`
  ADD CONSTRAINT `demon_user_quests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demon_user_quests_ibfk_2` FOREIGN KEY (`quest_id`) REFERENCES `demon_quests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_quests_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_user_roles`
--
ALTER TABLE `demon_user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `demon_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `demon_user_sessions`
--
ALTER TABLE `demon_user_sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `demon_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
