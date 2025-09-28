-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 28/09/2025 às 22:51
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

--
-- Despejando dados para a tabela `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('tocantinstransportwifi-cache-mikrotik_commands_D6:DE:C4:66:F2:84', 'a:10:{i:0;a:4:{s:4:\"type\";s:11:\"remove_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:4:\"name\";s:17:\"d6:de:c4:66:f2:84\";}}i:1;a:4:{s:4:\"type\";s:8:\"add_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:4:\"name\";s:17:\"d6:de:c4:66:f2:84\";s:11:\"mac-address\";s:17:\"d6:de:c4:66:f2:84\";s:7:\"profile\";s:7:\"default\";s:7:\"comment\";s:43:\"Liberado automaticamente - 28/09/2025 17:09\";}}i:2;a:4:{s:4:\"type\";s:14:\"remove_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"d6:de:c4:66:f2:84\";}}i:3;a:4:{s:4:\"type\";s:11:\"add_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:11:\"mac-address\";s:17:\"d6:de:c4:66:f2:84\";s:7:\"address\";s:11:\"10.5.50.248\";s:4:\"type\";s:8:\"bypassed\";s:7:\"comment\";s:47:\"Pago - Amanda campos - Expira: 29/09/2025 05:09\";}}i:4;a:4:{s:4:\"type\";s:11:\"remove_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:4:\"name\";s:17:\"D6:DE:C4:66:F2:84\";}}i:5;a:4:{s:4:\"type\";s:8:\"add_user\";s:4:\"path\";s:16:\"/ip/hotspot/user\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:4:\"name\";s:17:\"D6:DE:C4:66:F2:84\";s:11:\"mac-address\";s:17:\"D6:DE:C4:66:F2:84\";s:7:\"profile\";s:7:\"default\";s:7:\"comment\";s:43:\"Liberado automaticamente - 28/09/2025 17:09\";}}i:6;a:4:{s:4:\"type\";s:14:\"remove_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"D6:DE:C4:66:F2:84\";}}i:7;a:4:{s:4:\"type\";s:11:\"add_binding\";s:4:\"path\";s:22:\"/ip/hotspot/ip-binding\";s:6:\"action\";s:3:\"add\";s:6:\"params\";a:4:{s:11:\"mac-address\";s:17:\"D6:DE:C4:66:F2:84\";s:7:\"address\";s:11:\"10.5.50.248\";s:4:\"type\";s:8:\"bypassed\";s:7:\"comment\";s:47:\"Pago - Amanda campos - Expira: 29/09/2025 05:09\";}}i:8;a:4:{s:4:\"type\";s:13:\"remove_active\";s:4:\"path\";s:18:\"/ip/hotspot/active\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"d6:de:c4:66:f2:84\";}}i:9;a:4:{s:4:\"type\";s:13:\"remove_active\";s:4:\"path\";s:18:\"/ip/hotspot/active\";s:6:\"action\";s:6:\"remove\";s:5:\"where\";a:1:{s:11:\"mac-address\";s:17:\"D6:DE:C4:66:F2:84\";}}}', 1759090771);

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
(18, '2025_09_26_000001_create_system_settings_table', 6);

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
(104, '10.5.50.248', 'D6:DE:C4:66:F2:84', 'AUTO_LIBERATED_139', '200.163.8.89', '2025-09-28 20:50:59', '2025-09-28 20:06:39', '2025-09-28 20:50:59');

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
(128, 139, 0.05, 'pix', 'completed', NULL, 'TXN_1759090047_5F6E3D04', '00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/62851899-beb1-49b0-97ce-51d3911854cf52040000530398654040.055802BR592357732545_ERICK_VINICIUS6009Sao_Paulo6229052561a63589fa2a4c728c65878156304AD39', 'TXN_1759090047_5F6E3D04', 'Q2hhcmdlOjY4ZDk5NTgwNDRmZGIzZDEwOWQwOTBmYg==', '{\"pix\": {\"time\": \"2025-09-28T20:08:05.000Z\", \"type\": \"PAYMENT\", \"payer\": {\"name\": \"KAUANY NERES DE NAZARE\", \"taxID\": {\"type\": \"BR:CPF\", \"taxID\": \"07886155130\"}, \"correlationID\": \"6a0bdb7f-f510-4757-8d4f-0e81cd89891f\"}, \"value\": 5, \"charge\": {\"fee\": 85, \"type\": \"DYNAMIC\", \"value\": 5, \"brCode\": \"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/62851899-beb1-49b0-97ce-51d3911854cf52040000530398654040.055802BR592357732545_ERICK_VINICIUS6009Sao_Paulo6229052561a63589fa2a4c728c65878156304AD39\", \"pixKey\": \"acd05342-22f3-49c2-8598-45daaf15744b\", \"status\": \"ACTIVE\", \"comment\": \"WiFi Tocantins Express - Internet Premium\", \"customer\": {\"name\": \"ERICK VINICIUS RODRIGUES\", \"email\": \"cliente@wifitocantins.com.br\", \"phone\": \"+556399999999\", \"taxID\": {\"type\": \"BR:CNPJ\", \"taxID\": \"57732545000100\"}, \"correlationID\": \"3c91229a-c993-46ee-8765-a0fa227a1491\"}, \"discount\": 0, \"globalID\": \"Q2hhcmdlOjY4ZDk5NTgwNDRmZGIzZDEwOWQwOTBmYg==\", \"createdAt\": \"2025-09-28T20:07:28.059Z\", \"expiresIn\": 3600, \"updatedAt\": \"2025-09-28T20:07:28.059Z\", \"identifier\": \"61a63589fa2a4c728c65878156f10114\", \"expiresDate\": \"2025-09-28T21:07:28.017Z\", \"qrCodeImage\": \"https://api.openpix.com.br/openpix/charge/brcode/image/0a217663-9f26-4aa1-a99f-21fd3da5559c.png\", \"correlationID\": \"TXN_1759090047_5F6E3D04\", \"paymentLinkID\": \"0a217663-9f26-4aa1-a99f-21fd3da5559c\", \"transactionID\": \"61a63589fa2a4c728c65878156f10114\", \"additionalInfo\": [], \"paymentLinkUrl\": \"https://openpix.com.br/pay/0a217663-9f26-4aa1-a99f-21fd3da5559c\", \"ensureSameTaxID\": false, \"valueWithDiscount\": 5}, \"status\": \"CONFIRMED\", \"customer\": {\"name\": \"ERICK VINICIUS RODRIGUES\", \"email\": \"cliente@wifitocantins.com.br\", \"phone\": \"+556399999999\", \"taxID\": {\"type\": \"BR:CNPJ\", \"taxID\": \"57732545000100\"}, \"correlationID\": \"3c91229a-c993-46ee-8765-a0fa227a1491\"}, \"globalID\": \"UGl4VHJhbnNhY3Rpb246NjhkOTk1ZTIzZmQ1Y2QzNjJlY2U2MGJm\", \"createdAt\": \"2025-09-28T20:09:06.014Z\", \"debitParty\": {\"psp\": {\"id\": \"22896431\", \"name\": \"PICPAY\"}, \"holder\": {\"name\": \"KAUANY NERES DE NAZARE\", \"taxID\": {\"type\": \"BR:CPF\", \"taxID\": \"07886155130\"}}, \"account\": {\"branch\": \"0001\", \"account\": \"1039136990\", \"accountType\": \"CACC\"}}, \"endToEndId\": \"E2289643120250928200872xNxuFS3SS\", \"creditParty\": {\"psp\": {\"id\": \"54811417\", \"name\": \"WOOVI IP LTDA.\"}, \"holder\": {\"taxID\": {\"type\": \"BR:CNPJ\", \"taxID\": \"57732545000100\"}}, \"pixKey\": {\"type\": \"RANDOM\", \"pixKey\": \"acd05342-22f3-49c2-8598-45daaf15744b\"}, \"account\": {\"branch\": \"0001\", \"account\": \"1235400\", \"accountType\": \"TRAN\"}}, \"transactionID\": \"61a63589fa2a4c728c65878156f10114\"}, \"event\": \"OPENPIX:CHARGE_COMPLETED\", \"charge\": {\"fee\": 85, \"type\": \"DYNAMIC\", \"value\": 5, \"brCode\": \"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/62851899-beb1-49b0-97ce-51d3911854cf52040000530398654040.055802BR592357732545_ERICK_VINICIUS6009Sao_Paulo6229052561a63589fa2a4c728c65878156304AD39\", \"pixKey\": \"acd05342-22f3-49c2-8598-45daaf15744b\", \"status\": \"ACTIVE\", \"comment\": \"WiFi Tocantins Express - Internet Premium\", \"customer\": {\"name\": \"ERICK VINICIUS RODRIGUES\", \"email\": \"cliente@wifitocantins.com.br\", \"phone\": \"+556399999999\", \"taxID\": {\"type\": \"BR:CNPJ\", \"taxID\": \"57732545000100\"}, \"correlationID\": \"3c91229a-c993-46ee-8765-a0fa227a1491\"}, \"discount\": 0, \"globalID\": \"Q2hhcmdlOjY4ZDk5NTgwNDRmZGIzZDEwOWQwOTBmYg==\", \"createdAt\": \"2025-09-28T20:07:28.059Z\", \"expiresIn\": 3600, \"updatedAt\": \"2025-09-28T20:07:28.059Z\", \"identifier\": \"61a63589fa2a4c728c65878156f10114\", \"expiresDate\": \"2025-09-28T21:07:28.017Z\", \"qrCodeImage\": \"https://api.openpix.com.br/openpix/charge/brcode/image/0a217663-9f26-4aa1-a99f-21fd3da5559c.png\", \"correlationID\": \"TXN_1759090047_5F6E3D04\", \"paymentLinkID\": \"0a217663-9f26-4aa1-a99f-21fd3da5559c\", \"transactionID\": \"61a63589fa2a4c728c65878156f10114\", \"additionalInfo\": [], \"paymentLinkUrl\": \"https://openpix.com.br/pay/0a217663-9f26-4aa1-a99f-21fd3da5559c\", \"paymentMethods\": {\"pix\": {\"fee\": 85, \"txId\": \"61a63589fa2a4c728c65878156f10114\", \"value\": 5, \"brCode\": \"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/62851899-beb1-49b0-97ce-51d3911854cf52040000530398654040.055802BR592357732545_ERICK_VINICIUS6009Sao_Paulo6229052561a63589fa2a4c728c65878156304AD39\", \"method\": \"PIX_COB\", \"status\": \"ACTIVE\", \"identifier\": \"61a63589fa2a4c728c65878156f10114\", \"qrCodeImage\": \"https://api.openpix.com.br/openpix/charge/brcode/image/0a217663-9f26-4aa1-a99f-21fd3da5559c.png\", \"transactionID\": \"61a63589fa2a4c728c65878156f10114\"}}, \"ensureSameTaxID\": false, \"valueWithDiscount\": 5}, \"account\": [], \"company\": {\"id\": \"68caca98941631e25550170c\", \"name\": \"57.732.545 ERICK VINICIUS RODRIGUES\", \"taxID\": \"57732545000100\"}, \"authorization\": null}', '2025-09-28 20:09:21', '2025-09-28 20:07:27', '2025-09-28 20:09:21');

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
('NEKLPz8WHp0l0iUNt9lz1rsPLLbTVfav0o3ptIdR', NULL, '200.163.8.89', 'Mozilla/5.0 (Linux; Android 14; 2203129G Build/UKQ1.231003.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.156 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidHFERlJIN1hpUWRzSm1BdzdiU2c4eHIzODBkNDZFU01mb2Z3bzVqeSI7czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjExOToiaHR0cHM6Ly93d3cudG9jYW50aW5zdHJhbnNwb3J0ZXdpZmkuY29tLmJyLz9jYXB0aXZlPXRydWUmaXA9MTAuNS41MC4yNDgmbWFjPUQ2JTNBREUlM0FDNCUzQTY2JTNBRjIlM0E4NCZzb3VyY2U9bWlrcm90aWsiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1759090019),
('GXQaEpgXi1lGjAwtYcUJjtM7wN2rBrcNPPSqA2P9', 1, '170.239.227.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaG1TWktleDZWQlFvamQ2QTQwQWluNGhpUm9BSmpoWWhhRkdlUEhYTSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU4OiJodHRwczovL3d3dy50b2NhbnRpbnN0cmFuc3BvcnRld2lmaS5jb20uYnIvYWRtaW4vdXNlcnMvMTM5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1759091862),
('czxLcypeGnc7IIJnGXWe1BJbz08rUnhyhbMVEd44', NULL, '200.163.8.89', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMHJWTWxROW9hUnp0VVdCUW5OWkhoZHR1ZnV5ZUxSaFJyU3Q1NFFWcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoyNToibWlrcm90aWtfY29udGV4dF92ZXJpZmllZCI7YjoxO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjExOToiaHR0cHM6Ly93d3cudG9jYW50aW5zdHJhbnNwb3J0ZXdpZmkuY29tLmJyLz9jYXB0aXZlPXRydWUmaXA9MTAuNS41MC4yNDgmbWFjPUQ2JTNBREUlM0FDNCUzQTY2JTNBRjIlM0E4NCZzb3VyY2U9bWlrcm90aWsiO319', 1759090030);

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
(1, 'pix_gateway', 'woovi', '2025-09-26 16:45:10', '2025-09-27 21:57:52');

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
(1, 'Administrador WiFi Tocantins', 'admin@wifitocantins.com.br', NULL, NULL, '$2y$12$lYCO2S0fN33Xggr8/ITUEOx58rlsM7S7gLT2dJVwNcaxMg63USvxS', NULL, '2025-09-04 19:33:14', '2025-09-26 05:45:16', '2025-09-04 19:33:14', 'D4:01:C3:C6:29:4A', '10.5.50.42', NULL, NULL, NULL, 0, 'offline', 'admin'),
(2, 'Gestor WiFi Tocantins', 'gestor@wifitocantins.com.br', NULL, NULL, '$2y$12$.kPHfPHzQAy0ap5UU6atcOaQq6WjXtCjTfCGsZQSB4SKAc..CS9bq', NULL, '2025-09-04 19:33:14', '2025-09-04 19:33:14', '2025-09-04 19:33:14', NULL, NULL, NULL, NULL, NULL, 0, 'active', 'manager'),
(139, 'Amanda campos', 'amandasilvarsr@gmail.com', '63981015422', NULL, '$2y$12$GImfH7M1GeOhocMkc7gvqO2gXT0o77malzaHtyXJByqX5p5pF8/1a', NULL, '2025-09-28 20:07:25', '2025-09-28 20:37:00', '2025-09-28 20:07:25', 'D6:DE:C4:66:F2:84', '10.5.50.248', NULL, NULL, NULL, 0, 'offline', 'user');

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
-- Despejando dados para a tabela `wifi_sessions`
--

INSERT INTO `wifi_sessions` (`id`, `user_id`, `payment_id`, `started_at`, `ended_at`, `data_used`, `session_status`, `created_at`, `updated_at`) VALUES
(86, 139, 128, '2025-09-28 20:09:21', '2025-09-28 20:37:00', 0, 'ended', '2025-09-28 20:09:21', '2025-09-28 20:37:00');

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `mikrotik_mac_reports`
--
ALTER TABLE `mikrotik_mac_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT de tabela `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de tabela `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
