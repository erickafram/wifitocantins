-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 06/12/2025 às 13:25
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
-- Banco de dados: `tocantinstransporte`
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

--
-- Despejando dados para a tabela `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('tocantinstransportwifi-cache-wifi_price', 's:4:\"5.99\";', 1765026512),
('tocantinstransportwifi-cache-mikrotik_commands_7A:08:B9:18:2E:69', 'a:10:{i:0;a:4:{s:4:\"type\";s:11:\"remove_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:4:\"name\";s:17:\"7a:08:b9:18:2e:69\";}}i:1;a:4:{s:4:\"type\";s:8:\"add_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:4:\"name\";s:17:\"7a:08:b9:18:2e:69\";s:11:\"mac-address\";s:17:\"7a:08:b9:18:2e:69\";s:7:\"profile\";s:7:\"default\";s:7:\"comment\";s:43:\"Liberado automaticamente - 06/12/2025 03:42\";}}i:2;a:4:{s:4:\"type\";s:14:\"remove_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"7a:08:b9:18:2e:69\";}}i:3;a:4:{s:4:\"type\";s:11:\"add_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:11:\"mac-address\";s:17:\"7a:08:b9:18:2e:69\";s:7:\"address\";s:11:\"10.5.50.231\";s:4:\"type\";s:8:\"bypassed\";s:7:\"comment\";s:41:\"Pago - Jackson - Expira: 06/12/2025 15:41\";}}i:4;a:4:{s:4:\"type\";s:11:\"remove_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:4:\"name\";s:17:\"7A:08:B9:18:2E:69\";}}i:5;a:4:{s:4:\"type\";s:8:\"add_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:4:\"name\";s:17:\"7A:08:B9:18:2E:69\";s:11:\"mac-address\";s:17:\"7A:08:B9:18:2E:69\";s:7:\"profile\";s:7:\"default\";s:7:\"comment\";s:43:\"Liberado automaticamente - 06/12/2025 03:42\";}}i:6;a:4:{s:4:\"type\";s:14:\"remove_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"7A:08:B9:18:2E:69\";}}i:7;a:4:{s:4:\"type\";s:11:\"add_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:11:\"mac-address\";s:17:\"7A:08:B9:18:2E:69\";s:7:\"address\";s:11:\"10.5.50.231\";s:4:\"type\";s:8:\"bypassed\";s:7:\"comment\";s:41:\"Pago - Jackson - Expira: 06/12/2025 15:41\";}}i:8;a:4:{s:4:\"type\";s:13:\"remove_active\";s:4:\"path\";s:18:\"/ip/hotspot/active\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"7a:08:b9:18:2e:69\";}}i:9;a:4:{s:4:\"type\";s:13:\"remove_active\";s:4:\"path\";s:18:\"/ip/hotspot/active\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"7A:08:B9:18:2E:69\";}}}', 1765003923);

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
(16, '2025_09_22_025710_create_mikrotik_mac_reports_table', 4),
(17, '2025_01_25_000000_create_payment_settings_table', 5),
(18, '2025_09_26_000001_create_system_settings_table', 6),
(19, '2025_11_11_200000_update_vouchers_table_for_drivers', 7),
(20, '2025_11_13_232302_add_voucher_fields_to_users_table', 8),
(21, '2025_11_15_000835_add_driver_phone_to_vouchers_table', 8),
(22, '2025_11_15_004707_add_reactivation_interval_to_vouchers_table', 9),
(23, '2025_11_15_004805_add_activation_interval_to_vouchers_table', 9),
(24, '2025_11_13_194300_create_voucher_sessions_table', 10),
(25, '2025_12_06_000001_create_whatsapp_tables', 11);

-- --------------------------------------------------------

--
-- Estrutura para tabela `mikrotik_mac_reports`
--

CREATE TABLE `mikrotik_mac_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mac_address` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mikrotik_ip` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `mikrotik_mac_reports`
--

INSERT INTO `mikrotik_mac_reports` (`id`, `ip_address`, `mac_address`, `transaction_id`, `mikrotik_ip`, `reported_at`, `created_at`, `updated_at`) VALUES
(246, '10.5.50.248', '4A:24:2C:27:7E:86', NULL, NULL, '2025-11-15 17:56:28', '2025-11-15 15:48:57', '2025-11-15 17:56:28'),
(247, '10.5.50.247', '5C:CD:5B:2F:B9:3F', NULL, NULL, '2025-12-03 00:58:42', '2025-11-15 15:54:28', '2025-12-03 00:58:42'),
(250, '10.5.50.249', '4A:24:2C:27:7E:86', NULL, NULL, '2025-11-16 12:50:27', '2025-11-16 12:20:32', '2025-11-16 12:50:27'),
(251, '10.5.50.250', '4A:24:2C:27:7E:86', 'AUTO_LIBERATED_209', '179.255.56.217', '2025-12-04 13:11:21', '2025-11-16 20:24:57', '2025-12-04 13:11:21'),
(252, '10.5.50.249', '6E:5E:71:C9:27:8A', 'AUTO_LIBERATED_210', '170.239.226.94', '2025-11-16 21:31:28', '2025-11-16 20:31:59', '2025-11-16 21:31:28'),
(254, '10.5.50.249', 'CA:E5:FF:59:04:32', 'AUTO_LIBERATED_211', '148.227.83.109', '2025-11-28 16:32:58', '2025-11-17 18:28:35', '2025-11-28 16:32:58'),
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

--
-- Despejando dados para a tabela `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_type`, `status`, `payment_id`, `transaction_id`, `pix_emv_string`, `pix_location`, `gateway_payment_id`, `payment_data`, `paid_at`, `created_at`, `updated_at`) VALUES
(446, 295, 1.00, 'pix', 'completed', NULL, 'TXN_1764623843_B4CEC9301188', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/D997DDEF-810B-40AB-A587-A419B384BAA327600016BR.COM.PAGSEGURO0136D997DDEF-810B-40AB-A587-A419B384BAA352044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63044BFE', 'TXN_1764623843_B4CEC9301188', 'ORDE_5DC87FB9-8FEE-4F74-BDCF-6C219A8A1291', '{\"id\": \"ORDE_5DC87FB9-8FEE-4F74-BDCF-6C219A8A1291\", \"items\": [{\"name\": \"WiFi Tocantins Express - Internet Premium\", \"quantity\": 1, \"unit_amount\": 100, \"reference_id\": \"TXN_1764623843_B4CEC9301188\"}], \"links\": [{\"rel\": \"SELF\", \"href\": \"https://api.pagseguro.com/orders/ORDE_5DC87FB9-8FEE-4F74-BDCF-6C219A8A1291\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"PAY\", \"href\": \"https://api.pagseguro.com/orders/ORDE_5DC87FB9-8FEE-4F74-BDCF-6C219A8A1291/pay\", \"type\": \"POST\", \"media\": \"application/json\"}], \"charges\": [{\"id\": \"CHAR_D997DDEF-810B-40AB-A587-A419B384BAA3\", \"links\": [{\"rel\": \"SELF\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_D997DDEF-810B-40AB-A587-A419B384BAA3\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"CHARGE.CANCEL\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_D997DDEF-810B-40AB-A587-A419B384BAA3/cancel\", \"type\": \"POST\", \"media\": \"application/json\"}], \"amount\": {\"value\": 100, \"summary\": {\"paid\": 100, \"total\": 100, \"refunded\": 0, \"incremented\": 0}, \"currency\": \"BRL\"}, \"status\": \"PAID\", \"paid_at\": \"2025-12-01T18:18:02.339-03:00\", \"metadata\": [], \"created_at\": \"2025-12-01T18:18:02.026-03:00\", \"reference_id\": \"TXN_1764623843_B4CEC9301188\", \"payment_method\": {\"pix\": {\"holder\": {\"name\": \"ERICK VINICIUS RODRIGUES\", \"tax_id\": \"***588481**\"}, \"end_to_end_id\": \"E0000020820251201211758358985525\", \"notification_id\": \"NTF_8C7AF447-682D-4A6A-BD1C-40CF01E97ECB\"}, \"type\": \"PIX\"}, \"payment_response\": {\"code\": \"20000\", \"message\": \"SUCESSO\"}}], \"customer\": {\"name\": \"Cliente WiFi Tocantins\", \"email\": \"cliente.wifi@tocantinstransportewifi.com.br\", \"phones\": [{\"area\": \"63\", \"type\": \"MOBILE\", \"number\": \"999999999\", \"country\": \"55\"}], \"tax_id\": \"12345678909\"}, \"qr_codes\": [{\"id\": \"QRCO_D997DDEF-810B-40AB-A587-A419B384BAA3\", \"text\": \"00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/D997DDEF-810B-40AB-A587-A419B384BAA327600016BR.COM.PAGSEGURO0136D997DDEF-810B-40AB-A587-A419B384BAA352044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63044BFE\", \"links\": [{\"rel\": \"QRCODE.PNG\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_D997DDEF-810B-40AB-A587-A419B384BAA3/png\", \"type\": \"GET\", \"media\": \"image/png\"}, {\"rel\": \"QRCODE.BASE64\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_D997DDEF-810B-40AB-A587-A419B384BAA3/base64\", \"type\": \"GET\", \"media\": \"text/plain\"}], \"amount\": {\"value\": 100}, \"arrangements\": [\"PIX\"], \"expiration_date\": \"2025-12-02T23:59:59.000-03:00\"}], \"created_at\": \"2025-12-01T18:17:24.442-03:00\", \"reference_id\": \"TXN_1764623843_B4CEC9301188\", \"notification_urls\": [\"https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank\"]}', '2025-12-01 18:18:02', '2025-12-01 18:17:23', '2025-12-01 18:18:03'),
(445, 260, 1.00, 'pix', 'completed', NULL, 'TXN_1764623169_C31DFEBCAB7D', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B27600016BR.COM.PAGSEGURO0136B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B52044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63049406', 'TXN_1764623169_C31DFEBCAB7D', 'ORDE_A064FA11-BF78-41EC-AFA9-3A4C277BFFE0', '{\"id\": \"ORDE_A064FA11-BF78-41EC-AFA9-3A4C277BFFE0\", \"items\": [{\"name\": \"WiFi Tocantins Express - Internet Premium\", \"quantity\": 1, \"unit_amount\": 100, \"reference_id\": \"TXN_1764623169_C31DFEBCAB7D\"}], \"links\": [{\"rel\": \"SELF\", \"href\": \"https://api.pagseguro.com/orders/ORDE_A064FA11-BF78-41EC-AFA9-3A4C277BFFE0\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"PAY\", \"href\": \"https://api.pagseguro.com/orders/ORDE_A064FA11-BF78-41EC-AFA9-3A4C277BFFE0/pay\", \"type\": \"POST\", \"media\": \"application/json\"}], \"charges\": [{\"id\": \"CHAR_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B\", \"links\": [{\"rel\": \"SELF\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"CHARGE.CANCEL\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B/cancel\", \"type\": \"POST\", \"media\": \"application/json\"}], \"amount\": {\"value\": 100, \"summary\": {\"paid\": 100, \"total\": 100, \"refunded\": 0, \"incremented\": 0}, \"currency\": \"BRL\"}, \"status\": \"PAID\", \"paid_at\": \"2025-12-01T18:07:08.637-03:00\", \"metadata\": [], \"created_at\": \"2025-12-01T18:07:08.370-03:00\", \"reference_id\": \"TXN_1764623169_C31DFEBCAB7D\", \"payment_method\": {\"pix\": {\"holder\": {\"name\": \"ERICK VINICIUS RODRIGUES\", \"tax_id\": \"***588481**\"}, \"end_to_end_id\": \"E0000020820251201210704461457525\", \"notification_id\": \"NTF_9A3A4F3C-7A23-47D5-93E3-5DBA6EF066C4\"}, \"type\": \"PIX\"}, \"payment_response\": {\"code\": \"20000\", \"message\": \"SUCESSO\"}}], \"customer\": {\"name\": \"Cliente WiFi Tocantins\", \"email\": \"cliente.wifi@tocantinstransportewifi.com.br\", \"phones\": [{\"area\": \"63\", \"type\": \"MOBILE\", \"number\": \"999999999\", \"country\": \"55\"}], \"tax_id\": \"12345678909\"}, \"qr_codes\": [{\"id\": \"QRCO_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B\", \"text\": \"00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B27600016BR.COM.PAGSEGURO0136B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B52044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63049406\", \"links\": [{\"rel\": \"QRCODE.PNG\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B/png\", \"type\": \"GET\", \"media\": \"image/png\"}, {\"rel\": \"QRCODE.BASE64\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_B53C78B6-85E8-49BB-AB3A-AB90AC0AAE8B/base64\", \"type\": \"GET\", \"media\": \"text/plain\"}], \"amount\": {\"value\": 100}, \"arrangements\": [\"PIX\"], \"expiration_date\": \"2025-12-02T23:59:59.000-03:00\"}], \"created_at\": \"2025-12-01T18:06:09.317-03:00\", \"reference_id\": \"TXN_1764623169_C31DFEBCAB7D\", \"notification_urls\": [\"https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank\"]}', '2025-12-01 18:07:08', '2025-12-01 18:06:09', '2025-12-01 18:07:10'),
(289, 209, 1.00, 'pix', 'completed', NULL, 'TXN_1763335658_7E774B0BADBB', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/825EE19A-A93D-4D16-8C71-5615189A937427600016BR.COM.PAGSEGURO0136825EE19A-A93D-4D16-8C71-5615189A937452044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63041132', 'TXN_1763335658_7E774B0BADBB', 'ORDE_0D064ADC-5D6E-4E6F-BCDD-F386E80742DC', '{\"id\": \"ORDE_0D064ADC-5D6E-4E6F-BCDD-F386E80742DC\", \"items\": [{\"name\": \"WiFi Tocantins Express - Internet Premium\", \"quantity\": 1, \"unit_amount\": 100, \"reference_id\": \"TXN_1763335658_7E774B0BADBB\"}], \"links\": [{\"rel\": \"SELF\", \"href\": \"https://api.pagseguro.com/orders/ORDE_0D064ADC-5D6E-4E6F-BCDD-F386E80742DC\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"PAY\", \"href\": \"https://api.pagseguro.com/orders/ORDE_0D064ADC-5D6E-4E6F-BCDD-F386E80742DC/pay\", \"type\": \"POST\", \"media\": \"application/json\"}], \"charges\": [{\"id\": \"CHAR_825EE19A-A93D-4D16-8C71-5615189A9374\", \"links\": [{\"rel\": \"SELF\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_825EE19A-A93D-4D16-8C71-5615189A9374\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"CHARGE.CANCEL\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_825EE19A-A93D-4D16-8C71-5615189A9374/cancel\", \"type\": \"POST\", \"media\": \"application/json\"}], \"amount\": {\"value\": 100, \"summary\": {\"paid\": 100, \"total\": 100, \"refunded\": 0, \"incremented\": 0}, \"currency\": \"BRL\"}, \"status\": \"PAID\", \"paid_at\": \"2025-11-16T20:28:02.743-03:00\", \"metadata\": [], \"created_at\": \"2025-11-16T20:28:02.311-03:00\", \"reference_id\": \"TXN_1763335658_7E774B0BADBB\", \"payment_method\": {\"pix\": {\"holder\": {\"name\": \"KAUANY NERES DE NAZARE\", \"tax_id\": \"***861551**\"}, \"end_to_end_id\": \"E22896431202511162327qjuSkFuePV9\", \"notification_id\": \"NTF_4D83022B-45B3-4D91-A347-4612445243DE\"}, \"type\": \"PIX\"}, \"payment_response\": {\"code\": \"20000\", \"message\": \"SUCESSO\"}}], \"customer\": {\"name\": \"Cliente WiFi Tocantins\", \"email\": \"cliente.wifi@tocantinstransportewifi.com.br\", \"phones\": [{\"area\": \"63\", \"type\": \"MOBILE\", \"number\": \"999999999\", \"country\": \"55\"}], \"tax_id\": \"12345678909\"}, \"qr_codes\": [{\"id\": \"QRCO_825EE19A-A93D-4D16-8C71-5615189A9374\", \"text\": \"00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/825EE19A-A93D-4D16-8C71-5615189A937427600016BR.COM.PAGSEGURO0136825EE19A-A93D-4D16-8C71-5615189A937452044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63041132\", \"links\": [{\"rel\": \"QRCODE.PNG\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_825EE19A-A93D-4D16-8C71-5615189A9374/png\", \"type\": \"GET\", \"media\": \"image/png\"}, {\"rel\": \"QRCODE.BASE64\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_825EE19A-A93D-4D16-8C71-5615189A9374/base64\", \"type\": \"GET\", \"media\": \"text/plain\"}], \"amount\": {\"value\": 100}, \"arrangements\": [\"PIX\"], \"expiration_date\": \"2025-11-17T23:59:59.000-03:00\"}], \"created_at\": \"2025-11-16T20:27:38.507-03:00\", \"reference_id\": \"TXN_1763335658_7E774B0BADBB\", \"notification_urls\": [\"https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank\"]}', '2025-11-16 20:28:02', '2025-11-16 20:27:38', '2025-11-16 20:28:04'),
(447, 260, 5.99, 'pix', 'pending', NULL, 'TXN_1764624125_794E86107341', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/52A0EA8A-B5C9-4BC2-ACDE-E86AB8814CCD27600016BR.COM.PAGSEGURO013652A0EA8A-B5C9-4BC2-ACDE-E86AB8814CCD52044131530398654045.995802BR5922Tocantins Transporte e6006Palmas62070503***63045443', 'TXN_1764624125_794E86107341', 'ORDE_EA9F8971-4CED-4800-9BB7-78B31DB94502', NULL, NULL, '2025-12-01 18:22:05', '2025-12-01 18:22:06'),
(291, 211, 1.00, 'pix', 'completed', NULL, 'TXN_1764182584_856A6023892D', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/892E33C1-C952-4127-8DC4-252F2BFCAA2227600016BR.COM.PAGSEGURO0136892E33C1-C952-4127-8DC4-252F2BFCAA2252044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63042D07', 'TXN_1764182584_856A6023892D', 'ORDE_09AF0CC1-2A95-421A-BB26-4695D9A0F451', '{\"id\": \"ORDE_09AF0CC1-2A95-421A-BB26-4695D9A0F451\", \"items\": [{\"name\": \"WiFi Tocantins Express - Internet Premium\", \"quantity\": 1, \"unit_amount\": 100, \"reference_id\": \"TXN_1764182584_856A6023892D\"}], \"links\": [{\"rel\": \"SELF\", \"href\": \"https://api.pagseguro.com/orders/ORDE_09AF0CC1-2A95-421A-BB26-4695D9A0F451\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"PAY\", \"href\": \"https://api.pagseguro.com/orders/ORDE_09AF0CC1-2A95-421A-BB26-4695D9A0F451/pay\", \"type\": \"POST\", \"media\": \"application/json\"}], \"charges\": [{\"id\": \"CHAR_892E33C1-C952-4127-8DC4-252F2BFCAA22\", \"links\": [{\"rel\": \"SELF\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_892E33C1-C952-4127-8DC4-252F2BFCAA22\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"CHARGE.CANCEL\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_892E33C1-C952-4127-8DC4-252F2BFCAA22/cancel\", \"type\": \"POST\", \"media\": \"application/json\"}], \"amount\": {\"value\": 100, \"summary\": {\"paid\": 100, \"total\": 100, \"refunded\": 0, \"incremented\": 0}, \"currency\": \"BRL\"}, \"status\": \"PAID\", \"paid_at\": \"2025-11-26T15:44:50.258-03:00\", \"metadata\": [], \"created_at\": \"2025-11-26T15:44:49.919-03:00\", \"reference_id\": \"TXN_1764182584_856A6023892D\", \"payment_method\": {\"pix\": {\"holder\": {\"name\": \"VICTOR GABRIEL LOPES DE MENESES\", \"tax_id\": \"***576951**\"}, \"end_to_end_id\": \"E00416968202511261844HQVWsc1Emxc\", \"notification_id\": \"NTF_07B30871-A58E-4EDC-9F71-102F087707CF\"}, \"type\": \"PIX\"}, \"payment_response\": {\"code\": \"20000\", \"message\": \"SUCESSO\"}}], \"customer\": {\"name\": \"Cliente WiFi Tocantins\", \"email\": \"cliente.wifi@tocantinstransportewifi.com.br\", \"phones\": [{\"area\": \"63\", \"type\": \"MOBILE\", \"number\": \"999999999\", \"country\": \"55\"}], \"tax_id\": \"12345678909\"}, \"qr_codes\": [{\"id\": \"QRCO_892E33C1-C952-4127-8DC4-252F2BFCAA22\", \"text\": \"00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/892E33C1-C952-4127-8DC4-252F2BFCAA2227600016BR.COM.PAGSEGURO0136892E33C1-C952-4127-8DC4-252F2BFCAA2252044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***63042D07\", \"links\": [{\"rel\": \"QRCODE.PNG\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_892E33C1-C952-4127-8DC4-252F2BFCAA22/png\", \"type\": \"GET\", \"media\": \"image/png\"}, {\"rel\": \"QRCODE.BASE64\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_892E33C1-C952-4127-8DC4-252F2BFCAA22/base64\", \"type\": \"GET\", \"media\": \"text/plain\"}], \"amount\": {\"value\": 100}, \"arrangements\": [\"PIX\"], \"expiration_date\": \"2025-11-27T23:59:59.000-03:00\"}], \"created_at\": \"2025-11-26T15:43:23.929-03:00\", \"reference_id\": \"TXN_1764182584_856A6023892D\", \"notification_urls\": [\"https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank\"]}', '2025-11-26 15:44:50', '2025-11-26 15:43:04', '2025-11-26 15:44:51'),
(292, 208, 1.00, 'pix', 'pending', NULL, 'TXN_1764182733_6D144D9F3059', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/15C66BE2-7534-4F29-9827-781E3A08BFE527600016BR.COM.PAGSEGURO013615C66BE2-7534-4F29-9827-781E3A08BFE552044131530398654041.005802BR5922Tocantins Transporte e6006Palmas62070503***630487BC', 'TXN_1764182733_6D144D9F3059', 'ORDE_258C1BAC-E323-43CA-B64A-EC64C7945ED5', NULL, NULL, '2025-11-26 15:45:33', '2025-11-26 15:45:34'),
(293, 213, 5.99, 'pix', 'completed', NULL, 'TXN_1764195758_C1882515BEAF', '00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/0E684B54-02DF-44AA-BABC-7CFB16D5907827600016BR.COM.PAGSEGURO01360E684B54-02DF-44AA-BABC-7CFB16D5907852044131530398654045.995802BR5922Tocantins Transporte e6006Palmas62070503***630424B8', 'TXN_1764195758_C1882515BEAF', 'ORDE_EC651794-E955-4C72-BC21-3E90C795CF79', '{\"id\": \"ORDE_EC651794-E955-4C72-BC21-3E90C795CF79\", \"items\": [{\"name\": \"WiFi Tocantins Express - Internet Premium\", \"quantity\": 1, \"unit_amount\": 599, \"reference_id\": \"TXN_1764195758_C1882515BEAF\"}], \"links\": [{\"rel\": \"SELF\", \"href\": \"https://api.pagseguro.com/orders/ORDE_EC651794-E955-4C72-BC21-3E90C795CF79\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"PAY\", \"href\": \"https://api.pagseguro.com/orders/ORDE_EC651794-E955-4C72-BC21-3E90C795CF79/pay\", \"type\": \"POST\", \"media\": \"application/json\"}], \"charges\": [{\"id\": \"CHAR_0E684B54-02DF-44AA-BABC-7CFB16D59078\", \"links\": [{\"rel\": \"SELF\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_0E684B54-02DF-44AA-BABC-7CFB16D59078\", \"type\": \"GET\", \"media\": \"application/json\"}, {\"rel\": \"CHARGE.CANCEL\", \"href\": \"https://internal.api.pagseguro.com/charges/CHAR_0E684B54-02DF-44AA-BABC-7CFB16D59078/cancel\", \"type\": \"POST\", \"media\": \"application/json\"}], \"amount\": {\"value\": 599, \"summary\": {\"paid\": 599, \"total\": 599, \"refunded\": 0, \"incremented\": 0}, \"currency\": \"BRL\"}, \"status\": \"PAID\", \"paid_at\": \"2025-11-26T19:23:13.411-03:00\", \"metadata\": [], \"created_at\": \"2025-11-26T19:23:13.147-03:00\", \"reference_id\": \"TXN_1764195758_C1882515BEAF\", \"payment_method\": {\"pix\": {\"holder\": {\"name\": \"Flávia Vicente Godinho\", \"tax_id\": \"***150811**\"}, \"end_to_end_id\": \"E18236120202511262222s198978acc8\", \"notification_id\": \"NTF_392FF711-ADA8-4DE0-A01E-06A8EBF16ECF\"}, \"type\": \"PIX\"}, \"payment_response\": {\"code\": \"20000\", \"message\": \"SUCESSO\"}}], \"customer\": {\"name\": \"Cliente WiFi Tocantins\", \"email\": \"cliente.wifi@tocantinstransportewifi.com.br\", \"phones\": [{\"area\": \"63\", \"type\": \"MOBILE\", \"number\": \"999999999\", \"country\": \"55\"}], \"tax_id\": \"12345678909\"}, \"qr_codes\": [{\"id\": \"QRCO_0E684B54-02DF-44AA-BABC-7CFB16D59078\", \"text\": \"00020101021226830014br.gov.bcb.pix2561api.pagseguro.com/pix/v2/0E684B54-02DF-44AA-BABC-7CFB16D5907827600016BR.COM.PAGSEGURO01360E684B54-02DF-44AA-BABC-7CFB16D5907852044131530398654045.995802BR5922Tocantins Transporte e6006Palmas62070503***630424B8\", \"links\": [{\"rel\": \"QRCODE.PNG\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_0E684B54-02DF-44AA-BABC-7CFB16D59078/png\", \"type\": \"GET\", \"media\": \"image/png\"}, {\"rel\": \"QRCODE.BASE64\", \"href\": \"https://api.pagseguro.com/qrcode/QRCO_0E684B54-02DF-44AA-BABC-7CFB16D59078/base64\", \"type\": \"GET\", \"media\": \"text/plain\"}], \"amount\": {\"value\": 599}, \"arrangements\": [\"PIX\"], \"expiration_date\": \"2025-11-27T23:59:59.000-03:00\"}], \"created_at\": \"2025-11-26T19:22:38.680-03:00\", \"reference_id\": \"TXN_1764195758_C1882515BEAF\", \"notification_urls\": [\"https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank\"]}', '2025-11-26 19:23:13', '2025-11-26 19:22:38', '2025-11-26 19:23:14'),
-- --------------------------------------------------------

--
-- Estrutura para tabela `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `provider_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_type` enum('woovi','santander','pagseguro','mercadopago') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `webhook_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `client_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `environment` enum('sandbox','production') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
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
('VOfvLFiFcPrcC8p48i8MfFEwIVuWs2CPdIyzY5xg', NULL, '94.247.172.129', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOXJVMzQ5VGtUb05nTHJ1NFBJVUtFQmxiZTVGeGNoMG5qSm92Wm9MdyI7czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765011024),
('JYHgBaEbXQukz1hBAqulImmHX4dy4r53irIVlVHd', 1, '170.239.226.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiMVN2WE1qZzQ3elhJMGxTalV2RDVkcXZndUp0dnRWdGpJV2szMThTViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTE5OiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIvP2NhcHRpdmU9dHJ1ZSZpcD0xMC41LjUwLjI0OSZtYWM9RDYlM0FERSUzQUM0JTNBNjYlM0FGMiUzQTgxJnNvdXJjZT1taWtyb3RpayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6MTI6Im1pa3JvdGlrX21hYyI7czoxNzoiRDY6REU6QzQ6NjY6RjI6ODQiO3M6MTE6Im1pa3JvdGlrX2lwIjtzOjExOiIxMC41LjUwLjI0OSI7fQ==', 1764994352),
('9zR575jJJgDS6EcsNYuxgBCr15FOga7X5Jcyee95', NULL, '148.227.90.202', 'Mozilla/5.0 (Linux; Android 12; M2102J20SG Build/SKQ1.211006.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.106 Mobile Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoicXB2Y1NVeDNlbUtZVGl2ZWh6UXdmeEZOMlNZR25RV3pGcXp4QlZrNiI7czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjExOToiaHR0cHM6Ly93d3cudG9jYW50aW5zdHJhbnNwb3J0ZXdpZmkuY29tLmJyLz9jYXB0aXZlPXRydWUmaXA9MTAuNS41MC4yMzEmbWFjPTdBJTNBMDglM0FCOSUzQTE4JTNBMkUlM0E2OSZzb3VyY2U9bWlrcm90aWsiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjEyOiJtaWtyb3Rpa19tYWMiO3M6MTc6IjdBOjA4OkI5OjE4OjJFOjY5IjtzOjExOiJtaWtyb3Rpa19pcCI7czoxMToiMTAuNS41MC4yMzEiO30=', 1765003266),
('iqWNZSYFAF4SPl99nWpFkjzjpeyXoE3lMy9nPxvJ', NULL, '195.178.110.242', 'Mozilla/5.0 (CentOS; Linux i686; rv:125.0) Gecko/20100101 Firefox/125.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNUJFSUZHR2Vub1ZpR1ZNd0o1bmtJUldNQVZWMmhKaDROamliOEtYUCI7czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765006418),
-- --------------------------------------------------------

--
-- Estrutura para tabela `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'pix_gateway', 'pagbank', '2025-09-26 16:45:10', '2025-10-22 16:02:32'),
(2, 'wifi_price', '5.99', '2025-11-10 23:07:37', '2025-12-05 18:38:36'),
(3, 'session_duration', '24', '2025-11-10 23:07:37', '2025-12-05 22:48:07');

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
  `role` enum('user','manager','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `voucher_id` bigint UNSIGNED DEFAULT NULL,
  `driver_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voucher_activated_at` timestamp NULL DEFAULT NULL,
  `voucher_last_connection` timestamp NULL DEFAULT NULL,
  `voucher_daily_minutes_used` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `registered_at`, `mac_address`, `ip_address`, `device_name`, `connected_at`, `expires_at`, `data_used`, `status`, `role`, `voucher_id`, `driver_phone`, `voucher_activated_at`, `voucher_last_connection`, `voucher_daily_minutes_used`) VALUES
(1, 'Administrador WiFi Tocantins', 'admin@wifitocantins.com.br', '63981013050', NULL, '$2y$12$lYCO2S0fN33Xggr8/ITUEOx58rlsM7S7gLT2dJVwNcaxMg63USvxS', NULL, '2025-09-04 19:33:14', '2025-11-13 22:42:07', '2025-09-04 19:33:14', '5C:CD:5B:2F:B9:3F', '10.5.50.244', NULL, NULL, NULL, 0, 'offline', 'admin', NULL, NULL, NULL, NULL, 0),
(2, 'Gestor WiFi Tocantins', 'gestor@wifitocantins.com.br', NULL, NULL, '$2y$12$TLjKv788dvq3GYKCnKfA6OigTmP6.H7z.mR.vFOSVBV/SSBGHyBNW', NULL, '2025-09-04 19:33:14', '2025-11-27 13:24:17', '2025-09-04 19:33:14', NULL, NULL, NULL, NULL, NULL, 0, 'active', 'manager', NULL, NULL, NULL, NULL, 0),
(209, 'Erick Vinicius', 'kauanyneres9@gmail.com', '62992004700', NULL, '$2y$12$lNwGduvytlx4BTP0kz0creJVioJTfPb1WjsBJyt65OJBCsPHnqfZ.', NULL, '2025-11-16 20:27:35', '2025-12-06 09:23:57', '2025-11-16 20:27:35', '4A:24:2C:27:7E:86', '10.5.50.247', NULL, NULL, '2025-12-04 21:17:33', 0, 'expired', 'user', 18, '62992004700', '2025-12-04 17:17:33', '2025-12-04 17:17:33', 0),
(211, 'Victor', 'victor.positividade@hotmail.com', '62993423535', NULL, '$2y$12$mdK7jCLYWBJZ088Kr3pTr.k5sLN.ZyIkqTBUUdM94tvMtjbo4sKjC', NULL, '2025-11-26 15:43:01', '2025-12-06 09:23:57', '2025-11-26 15:43:01', 'C2:ED:B7:AF:A9:6D', '10.5.50.242', NULL, NULL, '2025-11-28 13:17:41', 0, 'expired', 'user', 13, '62993423535', '2025-11-28 11:17:41', '2025-11-28 11:17:41', 0),
(212, 'Victor Gabriel Lopes de Meneses', 'vglm.tocantinstransporte@gmail.com', '(63) 9342-3535', '2025-11-26 15:51:21', '$2y$12$if8JldIcdVx4.eb8yo4ZTu5UrrLM4dvngbpooWMhmHpkGc6bdfMMe', NULL, '2025-11-26 15:51:21', '2025-11-26 15:51:21', '2025-11-26 15:51:21', NULL, NULL, NULL, NULL, NULL, 0, 'active', 'manager', NULL, NULL, NULL, NULL, 0),
(213, 'Flávia Vicente Godinho', 'flaviavicent12@gmail.com', '63985035107', NULL, '$2y$12$6754CNr6V4ogXgVqW60yMe7ZWOPhS8EB61hsapm0zwPZBewD0QkTm', NULL, '2025-11-26 19:22:36', '2025-12-06 09:23:57', '2025-11-26 19:22:36', 'FE:A2:32:C4:B2:3E', '10.5.50.247', NULL, NULL, '2025-11-27 07:23:14', 0, 'expired', 'user', NULL, NULL, NULL, NULL, 0),
-- --------------------------------------------------------

--
-- Estrutura para tabela `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_document` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daily_hours` decimal(5,2) NOT NULL DEFAULT '24.00',
  `activation_interval_hours` decimal(5,2) NOT NULL DEFAULT '24.00',
  `daily_hours_used` decimal(5,2) NOT NULL DEFAULT '0.00',
  `last_used_date` date DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `voucher_type` enum('unlimited','limited') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'limited',
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `driver_name`, `driver_document`, `driver_phone`, `daily_hours`, `activation_interval_hours`, `daily_hours_used`, `last_used_date`, `expires_at`, `activated_at`, `is_active`, `voucher_type`, `description`, `created_at`, `updated_at`) VALUES
(8, 'WIFI-ZOSY-1CWY', 'Valdemir Canuto da Silva', '342.321.501-10', '63992444504', 2.00, 24.00, 0.00, '2025-12-01', NULL, '2025-11-26 20:29:55', 1, 'limited', NULL, '2025-11-26 18:01:51', '2025-12-01 20:26:09'),
(14, 'WIFI-A4PR-LVCC', 'Geraldo Cardoso Braga Neto', '00682685135', '63984546628', 2.00, 24.00, 0.00, '2025-12-04', NULL, '2025-11-28 17:52:00', 1, 'limited', NULL, '2025-11-28 17:49:05', '2025-12-04 17:15:56'),
(15, 'WIFI-SHAX-ZHIM', 'Yuri Felipe Paz dos Santos Lourenço', '03329892161', '63984965958', 2.00, 24.00, 0.00, '2025-12-02', NULL, '2025-11-29 02:40:12', 1, 'limited', NULL, '2025-11-28 17:50:40', '2025-12-02 17:10:43'),
(17, 'WIFI-8FRF-ACAZ', 'Valquíria', NULL, '62994014785', 24.00, 24.00, 0.00, NULL, NULL, NULL, 1, 'unlimited', NULL, '2025-12-01 20:36:18', '2025-12-01 20:36:18'),
(19, 'WIFI-DYWO-0SWC', 'erick vinicius', '01758848111', '63981015878', 2.00, 24.00, 0.00, '2025-12-05', NULL, '2025-12-05 17:20:43', 1, 'limited', NULL, '2025-12-05 17:19:20', '2025-12-05 17:20:43'),
(20, 'WIFI-G8YR-WPVT', 'Mauri Almeida', '63426137100', '62994426935', 24.00, 24.00, 0.00, '2025-12-05', NULL, '2025-12-05 18:39:53', 1, 'limited', NULL, '2025-12-05 18:39:32', '2025-12-05 18:39:53'),
(21, 'WIFI-6QQK-OOEL', 'Jackson', '73242195191', '63992890756', 24.00, 24.00, 0.00, '2025-12-05', NULL, '2025-12-05 18:42:08', 1, 'limited', NULL, '2025-12-05 18:41:33', '2025-12-05 18:42:08'),
(22, 'WIFI-AN89-CM66', 'Cilton', '90672348187', '63984551578', 24.00, 24.00, 0.00, '2025-12-05', NULL, '2025-12-05 18:44:19', 1, 'limited', NULL, '2025-12-05 18:42:55', '2025-12-05 18:44:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `voucher_sessions`
--

CREATE TABLE `voucher_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `voucher_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `mac_address` varchar(17) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `hours_granted` int NOT NULL DEFAULT '0',
  `minutes_used` int NOT NULL DEFAULT '0',
  `status` enum('active','expired','disconnected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `mikrotik_response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_messages`
--

CREATE TABLE `whatsapp_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `payment_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','sent','failed','delivered','read') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `message_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `whatsapp_messages`
--

INSERT INTO `whatsapp_messages` (`id`, `user_id`, `payment_id`, `phone`, `message`, `status`, `error_message`, `message_id`, `sent_at`, `delivered_at`, `read_at`, `created_at`, `updated_at`) VALUES
(2, 247, 362, '5563992410056', 'Olá! 👋\n\nVocê ainda não efetuou seu pagamento.\n\nPara navegar durante sua viagem, pague apenas *R$ 5,99* e tenha internet à vontade! 🚀\n\n📱 Acesse: http://10.5.50.1/login\n\nWiFi Tocantins - Internet na sua viagem!', 'sent', NULL, '3EB00E557BA93E1B2D6319', '2025-12-06 00:13:07', NULL, NULL, '2025-12-06 00:13:06', '2025-12-06 00:13:07'),
(3, 347, 554, '5562982478316', 'Olá! 👋\n\nVocê ainda não efetuou seu pagamento.\n\nPara navegar durante sua viagem, pague apenas *R$ 5,99* e tenha internet à vontade! 🚀\n\n📱 Acesse: http://10.5.50.1/login\n\nWiFi Tocantins - Internet na sua viagem!', 'sent', NULL, '3EB0DDA5502CDCD9F6647E', '2025-12-06 00:14:09', NULL, NULL, '2025-12-06 00:14:08', '2025-12-06 00:14:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_settings`
--

CREATE TABLE `whatsapp_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `whatsapp_settings`
--

INSERT INTO `whatsapp_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'is_connected', 'true', '2025-12-05 23:57:51', '2025-12-06 00:11:17'),
(2, 'connected_phone', '556392901378', '2025-12-05 23:57:51', '2025-12-06 00:11:17'),
(3, 'auto_send_enabled', 'true', '2025-12-05 23:57:51', '2025-12-05 23:57:51'),
(4, 'pending_minutes', '5', '2025-12-05 23:57:51', '2025-12-06 00:20:47'),
(5, 'message_template', 'Olá! 👋\r\n\r\nPercebemos que o pagamento ainda não foi concluído.\r\n\r\nPor apenas R$ 5,99, você garante internet ilimitada durante toda a sua viagem! 🚀\r\n\r\nAcesse para ativar:\r\n🔗 http://10.5.50.1/login\r\n\r\nou\r\n🔗 https://www.tocantinstransportewifi.com.br\r\n\r\nWiFi Tocantins — Internet rápida e segura na sua viagem!', '2025-12-05 23:57:51', '2025-12-06 00:17:30'),
(6, 'last_qr_code', 'data:image/png;base64,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', '2025-12-05 23:57:51', '2025-12-06 00:10:58'),
(7, 'connection_status', 'connected', '2025-12-05 23:57:51', '2025-12-06 00:11:17');

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
-- Despejando dados para a tabela `wifi_sessions`
--

INSERT INTO `wifi_sessions` (`id`, `user_id`, `payment_id`, `started_at`, `ended_at`, `data_used`, `session_status`, `created_at`, `updated_at`) VALUES
(288, 341, 589, '2025-12-06 03:41:53', NULL, 0, 'active', '2025-12-06 03:41:53', '2025-12-06 03:41:53'),
(287, 349, 556, '2025-12-05 22:06:54', NULL, 0, 'active', '2025-12-05 22:06:54', '2025-12-05 22:06:54'),
(286, 348, 555, '2025-12-05 21:48:59', NULL, 0, 'active', '2025-12-05 21:48:59', '2025-12-05 21:48:59'),
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
-- Índices de tabela `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_settings_provider_type_is_active_index` (`provider_type`,`is_active`),
  ADD KEY `payment_settings_is_active_index` (`is_active`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices de tabela `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_mac_address_unique` (`mac_address`),
  ADD KEY `users_voucher_id_index` (`voucher_id`),
  ADD KEY `users_driver_phone_index` (`driver_phone`);

--
-- Índices de tabela `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_code_unique` (`code`),
  ADD KEY `vouchers_driver_phone_index` (`driver_phone`);

--
-- Índices de tabela `voucher_sessions`
--
ALTER TABLE `voucher_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_messages_status_created_at_index` (`status`,`created_at`),
  ADD KEY `whatsapp_messages_phone_index` (`phone`),
  ADD KEY `whatsapp_messages_user_id_index` (`user_id`),
  ADD KEY `whatsapp_messages_payment_id_index` (`payment_id`);

--
-- Índices de tabela `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_settings_key_unique` (`key`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `mikrotik_mac_reports`
--
ALTER TABLE `mikrotik_mac_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=587;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=590;

--
-- AUTO_INCREMENT de tabela `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352;

--
-- AUTO_INCREMENT de tabela `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `voucher_sessions`
--
ALTER TABLE `voucher_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;
COMMIT;

