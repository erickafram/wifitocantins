-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 23/09/2025 às 16:58
-- Versão do servidor: 8.4.5-5
-- Versão do PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tocantinstransportewifi`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `devices`
--

CREATE TABLE `devices` (
  `id` bigint UNSIGNED NOT NULL,
  `mac_address` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_seen` timestamp NOT NULL,
  `last_seen` timestamp NOT NULL,
  `total_connections` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `instagram_engagements`
--

CREATE TABLE `instagram_engagements` (
  `id` bigint UNSIGNED NOT NULL,
  `mac_address` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_spent_seconds` int NOT NULL,
  `verification_score` int NOT NULL,
  `claimed_successfully` tinyint(1) NOT NULL DEFAULT '0',
  `instagram_visit_start` timestamp NOT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `user_agent` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_02_160120_create_payments_table', 1),
(5, '2025_09_02_160124_create_vouchers_table', 1),
(6, '2025_09_02_160129_create_devices_table', 1),
(7, '2025_09_02_160132_create_sessions_table', 1),
(8, '2025_09_02_161019_add_wifi_fields_to_users_table', 1),
(9, '2025_09_02_170213_create_instagram_engagements_table', 1),
(10, '2025_09_04_155223_add_user_info_to_users_table', 1),
(11, '2025_09_04_155801_add_phone_and_registered_at_to_users_table', 1),
(12, '2025_09_04_160415_add_phone_to_users_table', 1),
(13, '2025_09_04_162140_add_role_to_users_table', 1),
(14, '2025_09_16_191813_add_pix_fields_to_payments_table', 2),
(15, '2025_09_16_194112_add_gateway_payment_id_to_payments_table', 3),
(16, '2025_09_22_025710_create_mikrotik_mac_reports_table', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `mikrotik_mac_reports`
--

CREATE TABLE `mikrotik_mac_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mac_address` varchar(17) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mikrotik_ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `mikrotik_mac_reports`
--

INSERT INTO `mikrotik_mac_reports` (`id`, `ip_address`, `mac_address`, `transaction_id`, `mikrotik_ip`, `reported_at`, `created_at`, `updated_at`) VALUES
(25, '10.10.10.107', '4a:24:2c:27:7e:86', NULL, '189.72.217.241', '2025-09-22 18:23:31', '2025-09-22 18:08:18', '2025-09-22 18:23:31'),
(27, '10.10.10.100', 'd6:de:c4:66:f2:84', NULL, '189.72.217.241', '2025-09-22 23:29:36', '2025-09-22 23:28:37', '2025-09-22 23:29:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('pix','card') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_emv_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pix_location` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_data` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('aXLGyfNLbBewbFZOW4fGY1kBuFH7oZ6663hwhuBn', NULL, '189.72.217.241', 'Mozilla/5.0 (Linux; Android 14; 2203129G Build/UKQ1.231003.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.51 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia2ttdU9LZnBvN0RaU0tkNWxpVEJxUlBjdGdHM0htcGs3bHg0UzFOZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTA6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5ici9sb2dpbj9kc3Q9aHR0cCUzQSUyRiUyRnd3dy5nb29nbGUuY29tJTJGZ2VuXzIwNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758587957),
('7ZBzF4JF4DLyG0XYXglqfQBZ9GYcptT78PiYUX40', NULL, '54.208.119.170', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFhpdkpCOUMxcnJnWms0bmJOcU9iOGYwSWNpbElodVFoR1ZneDlzNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758581318),
('b6P6IbmI5r6nY0f8HOIpCDJOf637o6fQ3VJ9udGX', NULL, '54.208.119.170', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoienNqUjFqa0RxbFhUYU1PNjBJQTNRMUloUGVGbXQyWFJkVmdFbUJvRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758581359),
('Q1jDTvUd57RQCVFBb9v5vthHbp4JHZmGNc6mb5fL', NULL, '13.220.238.56', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib2pWU3JucTc3SFlSS3JPTXBSdEQxcTRJUVoxSmkwZENacGNneXM0UiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAwOiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIvbG9naW4/ZHN0PWh0dHAlM0ElMkYlMkZ3d3cubXNmdGNvbm5lY3R0ZXN0LmNvbSUyRnJlZGlyZWN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758581359),
('VRRV3l9FEzYqctgiaazNYJGggDflWXxWkUORN0U1', NULL, '34.96.50.85', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOFZXSmw5VEVMZWlwaXpWNU5UVklzYTJaQjVKYjl6NHQ1VWJ5YmN6ZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758582562),
('L4jLfEw1l4eOmxgQwdCtIt8nLMb9I0dBUg3pY4aS', NULL, '34.34.234.141', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibjRFZkxsczZ2V1hWUFU4WHp1WkMzMzhobG9zbk9TNzRpR2E3czVaWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758583410),
('p8iDxIstAOZYwdq8VIOL5W69Z9PH8oVmV9DGBOCO', NULL, '34.34.234.141', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGlhd09JdVBpUjcxaUhISVF3STdEYWdrQ0M1YlNNTnZIbFF4STBxMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758583410),
('9SukPrHnJr6kwQ0kFKwHwOACMqfYL7d7CftEgo8w', NULL, '34.96.51.34', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVhjYmtscjMzNTVnU1dsZHVkcjdIbGNvRlY0R2VlaHd2eXYxcFdwWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758583916),
('B4y5ffgCzBjzuy3A7FJr4j7TNkqMno80n9g83bft', NULL, '34.96.51.34', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid0FROWZjY0MxZ0YwTERBUnBoZTVIc05ZU0ZNeWpleXBvdkYzUkxSQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758583916),
('LgyXQk6R7WWQaIYxGJlhfP92ncHQs3dhwYsakZ0x', NULL, '13.220.238.56', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWVwY3h4TEJaVWh6SHk4dTFJNUtBSUlNN0pEVGpNd2JBQjMzREhWMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758584465),
('xJMSkfFfmsmaW3bX8o9VkseHryZekPyhzZ9aPyVI', NULL, '189.72.217.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYlU0dktMT1pCRHJadDR5ZVd5QzhWdmt0RXk4Q1ZOd0JPMFpZT3VMdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758587814),
('O0bsMpWReQGoJC7wn2fvE5HYpstcBFSesLiO0dkE', NULL, '189.72.217.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGhOTWFLWkVSTzQzMkQxekgxNm9YQ0ZJNXBxNmdYdGtZNnE3VWVSRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758587830),
('hnnnIJcONQwRsIT9iv0qeWYiQgg35X0rfBgxlK8t', NULL, '128.201.17.234', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWVqam5MUVJaUXlWUTFpVm1ORUlHNkxrWW40ZHBNUTh6bHlDd3hlSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758627662),
('OarABfsyKYewenU6FJ99jCBDXEjlXaoWI9zguf0z', NULL, '34.139.66.163', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2taTkZGeVNXTXZacGRXYnJacVRoc010MjZSY3dHcUhsdDFBdUk3ViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758631493),
('DLMGAq7wDHlDnS7q2UUTk2tl86R3SRlJrM9H1T2I', NULL, '34.147.13.14', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEZJQWlTaUQxUUxuWnVwTkswUk9sRllDUk04ZzRxTEd4NFA4RlJsWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758633537),
('3krRtZgvgMJjRVb16u5hzWUILHqZCaTrqBT8p21i', NULL, '189.72.217.241', 'RouterOS 7.20rc3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUXNMZFBRTTBMbkNHdXgxaGxPWXBnS3RSWFA4VGJCS3Y2MlRyZ01tdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580551),
('Bj7YUY3Mf8M3Qp3w5DPoKMvG4QyA4wYj2Rgx6ytu', NULL, '34.96.50.85', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieUFpOFVXbU9YcE1IYzZVSFd6bnBOdTRtc0drNkpCdk1SbTVra1ZxViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580578),
('fskJTCjkmKEtPumq7MExTMUF9y0HVcJMj2xbYNy1', NULL, '34.96.50.85', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid2pxRDFGT0x3RW92Zm42c1J2MDk3bm9MOHNEZjFxdXR1RlpnZXpKRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580578),
('l1jNpgZhu2tjJwYPJlD5YcOCF5suzUCrXE0hMdSx', NULL, '189.72.217.241', 'Mozilla/5.0 (Linux; Android 14; 2203129G Build/UKQ1.231003.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.51 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidE8yaGFncmRveklIN2lGM2tMNFcyUVFOYjY2WWV3dFAxbkdIOFZ3TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTA6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5ici9sb2dpbj9kc3Q9aHR0cCUzQSUyRiUyRnd3dy5nb29nbGUuY29tJTJGZ2VuXzIwNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580898),
('XN3dA2lwQifbj4DtyVzFCmqvii1XxLwKrLELIuJl', NULL, '189.72.217.241', 'RouterOS 7.20rc3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmQ3alZqdXdkQ3dTbFlXQzhyd291Wm1GUnVKOUZIc3RmWDNrNVM2dSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580256),
('EkIL0dNo8he5hWjeYazSmYodhJzXgiT8DrmgqNYy', NULL, '54.208.119.170', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidGRia204aVJuQUZtRWhkRnY4YU1hQnA5WXpOUmFXb0pYMnhGbTVzbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580148),
('YjK0ewm8RAjbhyTY6gx7Gm4Ypa1lCqGClP9aZxNT', NULL, '189.72.217.241', 'RouterOS 7.20rc3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3hxNWRmd1BYMTZjd01xMXlRbjR5bWVJRXplY0RzcmU0Y2hlTWN4cSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580112),
('Iouw2DaL22IWXsCmXHpiXVoFAg4GzEpY0sOomfEU', NULL, '13.220.238.56', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRU43dlRYV1dKSVlaa1pvWUpQSnNGSHVXU0ZIYmVRb2xiRjYweDhyOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580054),
('RyzK6Nv9Jmt4LxUXhYaNlDiIc17WAWNLBeLTGgV6', NULL, '189.72.217.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSXRnYWVwUWFuVFVhU0lETDZwMllySmpHa3MyMXV6U3BYN0l5TmJLNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580021),
('SsLerNhgPSARU4iqEZ5XUGCyZzKm09gIyLQS2WUP', NULL, '189.72.217.241', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSjNJY2VTMWJ6dllnVG56eWJxSzZXNDVROFlNd280RHhLZFo1Q1lUUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758589181),
('SzVt7qM2O4sW5JHhwO1N0JUmUci2kJAl5r11Rjh2', NULL, '34.96.50.85', 'Mozilla/5.0 (compatible; WarpBot/1.0)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUVGNk9ROXRzeW9HUzN3dFM3TTgydDloYlpwQXJxTG1Eb2NWU0RJeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580486),
('OVTW8aQmwk6t1vb0NQBGMTBfVWN3lwnkCC6FPRGN', NULL, '13.220.238.56', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOXJ2eFBEWjVQMXFnV3ZIUnJkeEd2eFViWTZXQWNMTklIMVV5M1Y3SCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758573890),
('mwzBt3g7e4QO9oXpzEPB1FRY9KzFRY77kdBiKxTI', NULL, '189.72.217.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUlg5VnJFeFdtN1RrOW9rTDZKcUliN0o3TTlNaDlNMmxOcUhuc0MzRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAwOiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIvbG9naW4/ZHN0PWh0dHAlM0ElMkYlMkZ3d3cubXNmdGNvbm5lY3R0ZXN0LmNvbSUyRnJlZGlyZWN0Ijt9fQ==', 1758587791),
('FN2RD1VH1bO0LeTCoCLBBmthvWHWWf9KTYiTv7Ro', NULL, '13.220.238.56', 'got (https://github.com/sindresorhus/got)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFdqZk5sNVFEclY4eEhXR1JBRFlUN1hsakFKOUtySERodDI1QVJlUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758574323),
('71nZmxt3PTBkyTDppmlFy40g09iPWgHvdqV5Vt9M', NULL, '189.72.217.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMmV3cWdySHlIVHpDRWpiSkV0aDVJNUtyN09Rb1VldTMxTmhrU3VMbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3LnRvY2FudGluc3RyYW5zcG9ydGV3aWZpLmNvbS5iciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758580016);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `mac_address` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `data_used` int NOT NULL DEFAULT '0',
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `role` enum('user','manager','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `registered_at`, `mac_address`, `ip_address`, `device_name`, `connected_at`, `expires_at`, `data_used`, `status`, `role`) VALUES
(1, 'Administrador WiFi Tocantins', 'admin@wifitocantins.com.br', NULL, NULL, '$2y$12$lYCO2S0fN33Xggr8/ITUEOx58rlsM7S7gLT2dJVwNcaxMg63USvxS', NULL, '2025-09-04 19:33:14', '2025-09-04 19:33:14', '2025-09-04 19:33:14', NULL, NULL, NULL, NULL, NULL, 0, 'active', 'admin'),
(2, 'Gestor WiFi Tocantins', 'gestor@wifitocantins.com.br', NULL, NULL, '$2y$12$.kPHfPHzQAy0ap5UU6atcOaQq6WjXtCjTfCGsZQSB4SKAc..CS9bq', NULL, '2025-09-04 19:33:14', '2025-09-04 19:33:14', '2025-09-04 19:33:14', NULL, NULL, NULL, NULL, NULL, 0, 'active', 'manager');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` decimal(8,2) DEFAULT NULL,
  `discount_percent` int DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `max_uses` int NOT NULL DEFAULT '1',
  `used_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `wifi_sessions`
--

CREATE TABLE `wifi_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `data_used` int NOT NULL DEFAULT '0',
  `session_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `devices_mac_address_unique` (`mac_address`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `instagram_engagements`
--
ALTER TABLE `instagram_engagements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instagram_engagements_mac_address_index` (`mac_address`);

--
-- Índices de tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices de tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `mikrotik_mac_reports`
--
ALTER TABLE `mikrotik_mac_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mikrotik_mac_reports_ip_address_mac_address_unique` (`ip_address`,`mac_address`),
  ADD KEY `mikrotik_mac_reports_reported_at_index` (`reported_at`),
  ADD KEY `mikrotik_mac_reports_ip_address_index` (`ip_address`),
  ADD KEY `mikrotik_mac_reports_mac_address_index` (`mac_address`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_user_id_foreign` (`user_id`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_mac_address_unique` (`mac_address`);

--
-- Índices de tabela `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_code_unique` (`code`);

--
-- Índices de tabela `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wifi_sessions_user_id_foreign` (`user_id`),
  ADD KEY `wifi_sessions_payment_id_foreign` (`payment_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instagram_engagements`
--
ALTER TABLE `instagram_engagements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `mikrotik_mac_reports`
--
ALTER TABLE `mikrotik_mac_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de tabela `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
