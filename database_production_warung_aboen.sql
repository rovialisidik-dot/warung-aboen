-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: warung_aboen
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_entity_id_index` (`entity_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'login',NULL,NULL,NULL,NULL,'127.0.0.1','Pengguna Pak Aboen (Pemilik) berhasil masuk ke sistem.','2026-10-21 10:30:56','2026-10-21 10:30:56');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `capital_transactions`
--

DROP TABLE IF EXISTS `capital_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `capital_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` enum('modal_masuk','penarikan_pribadi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `transaction_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `capital_transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `capital_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `capital_transactions`
--

LOCK TABLES `capital_transactions` WRITE;
/*!40000 ALTER TABLE `capital_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `capital_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_transactions`
--

DROP TABLE IF EXISTS `cash_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_after` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_transactions_user_id_foreign` (`user_id`),
  KEY `cash_transactions_reference_id_index` (`reference_id`),
  CONSTRAINT `cash_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_transactions`
--

LOCK TABLES `cash_transactions` WRITE;
/*!40000 ALTER TABLE `cash_transactions` DISABLE KEYS */;
INSERT INTO `cash_transactions` VALUES (1,1,'in','capital_in',NULL,1500000.00,1500000.00,'Modal Awal Kas Warung Aboen','2026-10-11 16:52:24','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,1,'out','expense',1,150000.00,1350000.00,'Beban: Token Listrik Warung','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25'),(3,1,'out','expense',2,35000.00,1315000.00,'Beban: Kantong Plastik Kresek 1 Pack','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25'),(4,1,'out','expense',3,25000.00,1290000.00,'Beban: Sapu & Pembersih Lantai Toko','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25'),(5,2,'in','sale',1,129500.00,1419500.00,'Penjualan Nota INV-20261020-0001','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25'),(6,2,'in','sale',2,82000.00,1501500.00,'Penjualan Nota INV-20261021-0002','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25'),(7,2,'in','sale',3,44000.00,1545500.00,'Penjualan Nota INV-20261022-0003','2026-10-21 16:52:25','2026-10-21 09:52:25','2026-10-21 09:52:25');
/*!40000 ALTER TABLE `cash_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Sembako','sembako','Beras, Minyak, Gula, Terigu & Telur','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,'Minuman','minuman','Kopi, Teh, Susu & Air Mineral','2026-10-21 09:52:24','2026-10-21 09:52:24'),(3,'Makanan Ringan & Mie','makanan-mie','Aneka Mie Instan & Cemilan','2026-10-21 09:52:24','2026-10-21 09:52:24'),(4,'Bumbu & Dapur','bumbu-dapur','Kecap, Saus, Penyedap & Garam','2026-10-21 09:52:24','2026-10-21 09:52:24'),(5,'Kebutuhan Mandi & Cuci','mandi-cuci','Sabun, Sampo, Deterjen & Pasta Gigi','2026-10-21 09:52:24','2026-10-21 09:52:24'),(6,'Rokok','rokok','Rokok Segel Pabrik','2026-10-21 09:52:24','2026-10-21 09:52:24');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Operasional',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `expense_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,'Listrik & Air','Token Listrik Warung',150000.00,'2026-10-18','Pengeluaran operasional','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,1,'Plastik & Kemasan','Kantong Plastik Kresek 1 Pack',35000.00,'2026-10-19','Pengeluaran operasional','2026-10-21 09:52:25','2026-10-21 09:52:25'),(3,1,'Operasional','Sapu & Pembersih Lantai Toko',25000.00,'2026-10-20','Pengeluaran operasional','2026-10-21 09:52:25','2026-10-21 09:52:25');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_10_21_000001_create_categories_table',1),(6,'2026_10_21_000002_create_products_table',1),(7,'2026_10_21_000003_create_sales_tables',1),(8,'2026_10_21_000004_create_purchases_tables',1),(9,'2026_10_21_000005_create_expenses_table',1),(10,'2026_10_21_000006_create_cash_transactions_table',1),(11,'2026_10_21_000007_create_stock_movements_table',1),(12,'2026_10_21_000008_create_capital_transactions_table',1),(13,'2026_10_21_000009_create_audit_logs_table',1),(14,'2026_10_21_000010_create_settings_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `purchase_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `min_stock` int NOT NULL DEFAULT '5',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_barcode_index` (`barcode`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'8991001','Beras Ramos Super 5kg','karung',67000.00,74000.00,24,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(2,1,'8991002','Minyak Goreng Bimoli Pouch 2L','pouch',33500.00,38000.00,17,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(3,1,'8991003','Gula Pasir Gulaku Kuning 1kg','bungkus',16500.00,18500.00,33,8,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(4,1,'8991004','Tepung Terigu Segitiga Biru 1kg','bungkus',11500.00,13500.00,30,6,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(5,1,'8991005','Telur Ayam Negeri Segar 1kg','kg',26500.00,29500.00,21,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(6,2,'8992001','Kopi Kapal Api Spesial Mix (1 Renceng)','renceng',13500.00,15500.00,19,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(7,2,'8992002','Teh Celup Sariwangi Kotak isi 25','kotak',6500.00,8000.00,15,4,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(8,2,'8992003','Susu Kental Manis Frisian Flag Cokelat 370g','kaleng',11800.00,14000.00,16,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(9,2,'8992004','Aqua Air Mineral Botol 600ml','botol',3200.00,4500.00,46,10,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(10,2,'8992005','Teh Pucuk Harum 350ml','botol',3300.00,4500.00,36,10,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(11,3,'8993001','Indomie Goreng Original 85g','bungkus',2850.00,3500.00,115,20,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(12,3,'8993002','Indomie Rasa Ayam Bawang 69g','bungkus',2850.00,3500.00,90,20,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(13,3,'8993003','Sedap Mie Kuah Soto 75g','bungkus',2800.00,3500.00,60,15,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(14,3,'8993004','Biskuit Roma Kelapa 300g','bungkus',9500.00,11500.00,14,4,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(15,4,'8994001','Kecap Manis Bango Pouch 520ml','pouch',21500.00,25000.00,12,4,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(16,4,'8994002','Royco Rasa Ayam 8g (1 Renceng)','renceng',4800.00,6000.00,25,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(17,4,'8994003','Garam Beryodium Cap Daun 250g','bungkus',2500.00,3500.00,30,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(18,5,'8995001','Sunlight Jeruk Nipis 650ml','pouch',13500.00,16000.00,15,4,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(19,5,'8995002','Deterjen Rinso Molto Anti Noda 770g','bungkus',20000.00,23500.00,10,3,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(20,5,'8995003','Sabun Mandi Lifebuoy Total 10 (85g)','batang',3800.00,5000.00,40,8,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(21,5,'8995004','Pasta Gigi Pepsodent 120g','kotak',11500.00,14000.00,12,4,1,'2026-10-21 09:52:24','2026-10-21 09:52:24'),(22,6,'8996001','Sampoerna Mild 16','bungkus',32000.00,35000.00,19,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:25'),(23,6,'8996002','Djarum Super 12','bungkus',22500.00,25000.00,15,5,1,'2026-10-21 09:52:24','2026-10-21 09:52:24');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `unit_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Supplier Umum',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `status` enum('received','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_purchase_number_unique` (`purchase_number`),
  KEY `purchases_user_id_foreign` (`user_id`),
  CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `unit_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `profit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,1,'Beras Ramos Super 5kg','karung',67000.00,74000.00,1,74000.00,7000.00,'2026-10-19 17:52:25','2026-10-19 17:52:25'),(2,1,2,'Minyak Goreng Bimoli Pouch 2L','pouch',33500.00,38000.00,1,38000.00,4500.00,'2026-10-19 17:52:25','2026-10-19 17:52:25'),(3,1,11,'Indomie Goreng Original 85g','bungkus',2850.00,3500.00,5,17500.00,3250.00,'2026-10-19 17:52:25','2026-10-19 17:52:25'),(4,2,3,'Gula Pasir Gulaku Kuning 1kg','bungkus',16500.00,18500.00,2,37000.00,4000.00,'2026-10-20 18:52:25','2026-10-20 18:52:25'),(5,2,5,'Telur Ayam Negeri Segar 1kg','kg',26500.00,29500.00,1,29500.00,3000.00,'2026-10-20 18:52:25','2026-10-20 18:52:25'),(6,2,6,'Kopi Kapal Api Spesial Mix (1 Renceng)','renceng',13500.00,15500.00,1,15500.00,2000.00,'2026-10-20 18:52:25','2026-10-20 18:52:25'),(7,3,9,'Aqua Air Mineral Botol 600ml','botol',3200.00,4500.00,2,9000.00,2600.00,'2026-10-21 19:52:25','2026-10-21 19:52:25'),(8,3,22,'Sampoerna Mild 16','bungkus',32000.00,35000.00,1,35000.00,3000.00,'2026-10-21 19:52:25','2026-10-21 19:52:25');
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Pelanggan Umum',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `gross_profit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `change_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancelled_reason` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_number_unique` (`invoice_number`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_cancelled_by_foreign` (`cancelled_by`),
  CONSTRAINT `sales_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'INV-20261020-0001',2,'Bu Siti (Tetangga)',129500.00,0.00,129500.00,114750.00,14750.00,'cash',150000.00,20500.00,'completed',NULL,NULL,NULL,NULL,'2026-10-19 17:52:25','2026-10-19 17:52:25'),(2,'INV-20261021-0002',2,'Pak Budi',82000.00,0.00,82000.00,73000.00,9000.00,'cash',100000.00,18000.00,'completed',NULL,NULL,NULL,NULL,'2026-10-20 18:52:25','2026-10-20 18:52:25'),(3,'INV-20261022-0003',2,'Mas Doni',44000.00,0.00,44000.00,38400.00,5600.00,'cash',50000.00,6000.00,'completed',NULL,NULL,NULL,NULL,'2026-10-21 19:52:25','2026-10-21 19:52:25');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'app_name','Warung Aboen','general','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,'store_tagline','Warung Sembako & Kebutuhan Sehari-hari Lengkap','general','2026-10-21 09:52:24','2026-10-21 09:52:24'),(3,'store_address','Kp Cigarunggang RT 27 RW 13, Desa Sumberjaya, Kecamatan Cihaurbeuti, Kabupaten Ciamis, Provinsi Jawa Barat','general','2026-10-21 09:52:24','2026-10-21 11:24:26'),(4,'store_phone','082262438494','general','2026-10-21 09:52:24','2026-10-21 11:12:20'),(5,'receipt_footer','Terima kasih telah berbelanja di Warung Aboen! Semoga berkah dan sehat selalu.','general','2026-10-21 09:52:24','2026-10-21 09:52:24'),(6,'initial_cash_balance','1500000','general','2026-10-21 09:52:24','2026-10-21 09:52:24');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` enum('in','out','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'adjustment',
  `reference_id` bigint unsigned DEFAULT NULL,
  `quantity` int NOT NULL,
  `stock_before` int NOT NULL,
  `stock_after` int NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_user_id_foreign` (`user_id`),
  KEY `stock_movements_reference_id_index` (`reference_id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,1,'in','initial',NULL,25,0,25,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,2,1,'in','initial',NULL,18,0,18,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(3,3,1,'in','initial',NULL,35,0,35,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(4,4,1,'in','initial',NULL,30,0,30,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(5,5,1,'in','initial',NULL,22,0,22,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(6,6,1,'in','initial',NULL,20,0,20,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(7,7,1,'in','initial',NULL,15,0,15,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(8,8,1,'in','initial',NULL,16,0,16,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(9,9,1,'in','initial',NULL,48,0,48,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(10,10,1,'in','initial',NULL,36,0,36,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(11,11,1,'in','initial',NULL,120,0,120,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(12,12,1,'in','initial',NULL,90,0,90,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(13,13,1,'in','initial',NULL,60,0,60,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(14,14,1,'in','initial',NULL,14,0,14,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(15,15,1,'in','initial',NULL,12,0,12,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(16,16,1,'in','initial',NULL,25,0,25,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(17,17,1,'in','initial',NULL,30,0,30,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(18,18,1,'in','initial',NULL,15,0,15,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(19,19,1,'in','initial',NULL,10,0,10,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(20,20,1,'in','initial',NULL,40,0,40,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(21,21,1,'in','initial',NULL,12,0,12,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(22,22,1,'in','initial',NULL,20,0,20,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24'),(23,23,1,'in','initial',NULL,15,0,15,'Stok awal pembukaan toko','2026-10-21 09:52:24','2026-10-21 09:52:24');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('owner','cashier') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cashier',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Pak Aboen (Pemilik)','admin','aboen@warungku.test',NULL,'$2y$12$nFKDNThEebA9GPL4zJVSY.xKEk92ZYJ2ftXtydNxKpGluEUVtDQTm','owner','081234567890',1,'dRGPpSsj0pStwW5q7FQdsL1gIbKQ5cUe5Fu9L64vBERpSV52EBaHQqXoCMDY','2026-10-21 09:52:24','2026-10-21 09:52:24'),(2,'Siti (Kasir / Keluarga)','kasir','siti@warungku.test',NULL,'$2y$12$mWu8Lk1gejcrsoSu8aj2S.nWy0yLmFaZ03/LprzpePh01yGmoeIue','cashier','089876543210',1,NULL,'2026-10-21 09:52:24','2026-10-21 09:52:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22  1:49:20
