-- MySQL dump 10.13  Distrib 5.5.43, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: binidini
-- ------------------------------------------------------
-- Server version	5.5.43-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bid`
--

DROP TABLE IF EXISTS `bid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4AF2B3F34887F3F8` (`shipping_id`),
  KEY `IDX_4AF2B3F3A76ED395` (`user_id`),
  KEY `IDX_4AF2B3F3F624B39D` (`sender_id`),
  CONSTRAINT `FK_4AF2B3F34887F3F8` FOREIGN KEY (`shipping_id`) REFERENCES `shipping` (`id`),
  CONSTRAINT `FK_4AF2B3F3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_4AF2B3F3F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bid`
--

LOCK TABLES `bid` WRITE;
/*!40000 ALTER TABLE `bid` DISABLE KEYS */;
/*!40000 ALTER TABLE `bid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ext_log_entries`
--

DROP TABLE IF EXISTS `ext_log_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ext_log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  `object_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`),
  KEY `log_version_lookup_idx` (`object_id`,`object_class`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ext_log_entries`
--

LOCK TABLES `ext_log_entries` WRITE;
/*!40000 ALTER TABLE `ext_log_entries` DISABLE KEYS */;
INSERT INTO `ext_log_entries` VALUES (1,'create','2015-07-07 16:09:06','1','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:200;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(2,'create','2015-07-07 16:23:49','2','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:350;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(3,'create','2015-07-07 16:29:22','3','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(4,'create','2015-07-07 16:29:24','4','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(5,'create','2015-07-07 16:29:25','5','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:250;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(6,'create','2015-07-07 16:29:26','6','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:150;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(7,'create','2015-07-07 16:29:26','7','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:150;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(8,'create','2015-07-07 16:29:28','8','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:250;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(9,'create','2015-07-07 16:29:29','9','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:250;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(10,'create','2015-07-07 16:29:30','10','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:200;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(11,'create','2015-07-07 16:29:30','11','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:350;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(12,'create','2015-07-07 16:29:32','12','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:200;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(13,'create','2015-07-07 16:29:32','13','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(14,'create','2015-07-07 16:54:40','14','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:200;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(15,'create','2015-07-07 16:54:43','15','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(16,'create','2015-07-07 16:54:44','16','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:200;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(17,'create','2015-07-07 16:54:46','17','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(18,'create','2015-07-07 16:55:36','18','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:150;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(19,'create','2015-07-07 16:55:37','19','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(20,'create','2015-07-07 16:55:38','20','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:350;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(21,'create','2015-07-07 16:55:39','21','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:150;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1'),(22,'create','2015-07-07 16:55:40','22','Binidini\\CoreBundle\\Entity\\Shipping',1,'a:3:{s:13:\"deliveryPrice\";i:300;s:5:\"state\";s:3:\"new\";s:7:\"carrier\";N;}','1');
/*!40000 ALTER TABLE `ext_log_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gcm_token`
--

DROP TABLE IF EXISTS `gcm_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gcm_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EC39EBAA76ED395` (`user_id`),
  CONSTRAINT `FK_EC39EBAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gcm_token`
--

LOCK TABLES `gcm_token` WRITE;
/*!40000 ALTER TABLE `gcm_token` DISABLE KEYS */;
INSERT INTO `gcm_token` VALUES (1,1,'f2HADR6pI0I:APA91bGQUtb5EMLu_Yc0RMYFOTQX5464aUwGSSnqEj9bnpwmKzTWNdkjchcJiNDvH2YT0uxNRquvbtBWZot45aZiwaC2Ux_W-pDxfe5564L4TTfbsXJjpwMyu346UTk5O172DdAoTpgq','2015-07-07 17:45:24');
/*!40000 ALTER TABLE `gcm_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `shipping_id` int(11) NOT NULL,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B6BD307FA76ED395` (`user_id`),
  KEY `IDX_B6BD307F4887F3F8` (`shipping_id`),
  CONSTRAINT `FK_B6BD307F4887F3F8` FOREIGN KEY (`shipping_id`) REFERENCES `shipping` (`id`),
  CONSTRAINT `FK_B6BD307FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_access_token`
--

DROP TABLE IF EXISTS `oauth_access_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F7FA86A45F37A13B` (`token`),
  KEY `IDX_F7FA86A419EB6921` (`client_id`),
  KEY `IDX_F7FA86A4A76ED395` (`user_id`),
  CONSTRAINT `FK_F7FA86A419EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`id`),
  CONSTRAINT `FK_F7FA86A4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_token`
--

LOCK TABLES `oauth_access_token` WRITE;
/*!40000 ALTER TABLE `oauth_access_token` DISABLE KEYS */;
INSERT INTO `oauth_access_token` VALUES (1,4,NULL,'MDY5YjM0MGFjY2E5ZDJkYzVjNzlmODE4MjM0YjZlMGI2OTI3ODNmYTBhMTZkODVjNTM3Y2NlMmMxMDc5NzIwZg',1751645309,NULL),(2,4,1,'NzQ1MzA1NzZhZGM4NzkzYWEyYTkyNmFmNmZhOGU1N2FhZjdhNGFlMjc5NjQyY2YyOTYyMTc4ZTVhZTgzMjA5NQ',1751645319,NULL),(3,4,1,'Nzc0YjNmMzYxMmE4Zjc5NjYyNDZjNWJjZTgzM2RlZjY3ZjBlNmQ0YzJkMWE3ZjhiYjNmZDM4MzdhMWUzN2MyMw',1751645739,NULL),(4,4,NULL,'ZDJjYzVjOWNmZTIxMTFmNGJmZmJkOTRmNzVjYjU4OWZlMGI4MmU2YjQ4MDM4MmVhMTA3NzQ0OTA5M2U1ZGM1NQ',1751645768,NULL),(5,4,1,'MTdmYTBlOTllNjkxMGZiODBiNjU4ZWEzNTNlNzVlMzA5NmE3ZTdlNTIzZWU5Nzg0NWM5N2Q1MTUwOTY1MjFmOQ',1751645790,NULL),(6,4,1,'ZmZjNmQzMzAwZjBkZDc2YTliYmIyNWZiNjczMzY1NGZmNzgxNjg1ODA3ODZkMmQyZmEyNjQyOGJmZGRhZDg2ZA',1751647580,NULL),(7,4,1,'YWU3MWQ4Yzg3MDBkYThjOTU1MjE5N2M1N2IwOGY2NWU1ZWZiYzFkYzVkNTE1YmUxYzUxZWY3ZWJiY2I0MTBhMw',1751648481,NULL),(8,4,1,'Zjk4MmJjMmRiMzk0NzVhZTE3ZGQ1YTNhZTJiNDlmMzgwMDJlYzQ3NGMzZDk2OTNmMzgyYzFjZDM0NjgyNWNiZQ',1751649309,NULL),(9,4,1,'ZDdkMjkxMDU4ZWE5ODZmMmY5OGMzZDQ5NjRmMDNhNTI4ZjQ2ZTkxNTQ5NzA5YTExM2RlNDg3MzI0OTI2YWQ1ZQ',1751649540,NULL),(10,4,NULL,'MzZjMGZjNGJiNWE3MGM5MThlZDJjZjIwYmZkOTJhNDFiOWQ0YWZhMmYxMzc3MjJmYTVkNTllMzY0YWE2YjdjNQ',1751649630,NULL),(11,4,NULL,'NDRjMjgyNTBlOTRkOGNiYmUxMjU3N2VhOGZjOGQ0ODA3ZjQzZTkxNmY5NTYwMGMxM2RkODMwYTNmNTZhZjhiNw',1751650103,NULL),(12,4,1,'ODNmZjM0OTU5MzI1ODNiNWIzYTBlMDczMmE1OTAzMmJlNDhjMWE0ZWI4NDEyZDA2NzVlZGYwNWRlZTM3YzQxMw',1751650139,NULL),(13,4,NULL,'MjczMTI2ODgxM2NhNjk5MDRmYzFjNDFlNTgzYjM3MmVkOTM0YTRlOGM5NWJiOGNlZTBhNWY5OGY3OTQ4OTg2Yg',1751650330,NULL),(14,4,NULL,'NGRjYzUxYjFmZjk1ODk0MDk1MDNlZTUyNDgyZmRhNmRkYTc0MTYyNmQ4NjYxZGMzN2E5MmUyMzZmM2UyMjBhMQ',1751650404,NULL),(15,4,1,'YjdiMTNkYTMxMTcwNmRjMGE3MzBjOTZiYWU0NmRhYTc5NTQ0ZTZlMzM3YWVkZDA4MmFhNjRjYmIxYTcwMzY5ZA',1751650478,NULL),(16,4,NULL,'MDJkMWM5NzBlMmM0NjIzNzczOTc0ZGVhMzBkNjlhMjRiYzI1YTZlODgxMzBlZTNiNWFjY2NkZDllMmNkM2ZlYw',1751651121,NULL),(17,4,NULL,'NTQ1YmE4MTM5ZTgzNjJjOWY2OGYyNjZmZmUyNGIyNGUzMGYxNWJmYjhjMWUwMTUyZWJhZGIzOTQyNWVmNzIzNw',1751651506,NULL),(18,4,NULL,'ZTI4ZjU1NjMyNjk3MGJmMTg2MWMyYjcxNjg4Yjc0OTE5NzNlZWExNDMwY2E4ZmQxZWUxZWNlZjQzYWQ0MjM0NQ',1751652042,NULL),(19,4,NULL,'YzBiOWY3MDdmMWUzYWExNzMwMzZhY2FjMzkxZTUyODVkODNmNjhiNzdhOTFmMmYwYTdhOWE1Y2NlYTk3MDBkNw',1751652701,NULL),(20,4,NULL,'YTJjMmI1NTQ0MDViOGZlMzUzZjgzODBkYjRiZjBmMmU2OGQ4MTU5ZTgwYTM4YWZiOTYxMTJlNGU4MWFhMzJjZg',1751652768,NULL),(21,4,NULL,'MDdkMWM2N2E5YTEyZmVmNzk1YTQxMGMzMWRlODMwNjliZmE5NTQzYjg0MmRmYzRmNmI1ODkxMmUyMjM5ZDI5Yw',1751653139,NULL),(22,4,NULL,'ZTJjYWQ4YTc3YjM4MmZkMWNhZjZlNDQxNGZiY2M4NDZkMjAxNDk5NzUxNzBiMTEwNGVhYzczZTY5ZTg3YWMyMg',1751653267,NULL),(23,4,NULL,'NTE5ODU4MGYwZjdkNjk4YTdkODQyZDQ1Y2M2Nzg1YmE3ZjNmNzdmMGRlYzY1NTUyMTBlYTEwZTU0ZWNiZTZmZg',1751653451,NULL),(24,4,NULL,'YjY1ZmY3NjYzYmFmOTlhZGRmMWRlYjE4Nzg4ZjVlZjJmMTQ3YTY1MDI1MDhhNzI0MTIyYzA4ZGY4ZGZmYmJmMA',1751653670,NULL),(25,4,NULL,'MjQzOTk1MWIwYzg3NzdiM2M4NDI5ZWFiY2QxZTZjMWMyNDU5ODNmNDA3YzM5MzVjZDNlMmRkOTg5YzgwMDQyNQ',1751654234,NULL),(26,4,NULL,'NjEwMTBiYjViNTU4YWM3ZTNhMTg3OTE5Y2I0OGVkNWRjNjUwNzFkMDQzODdiMjNmODk5OGEyZmE2NWM1MWNjNw',1751654515,NULL),(27,4,NULL,'ZGUyMTJkMmVkOGI1ZjQ4ZTQ2N2QxM2NhMGZmMTA5MjYwZWUyN2Y0ZTk4Njc5MWU2MTQxMzBhNDMyNmUyNTM1OQ',1751654729,NULL),(28,4,NULL,'NGZlNjlmNjkxN2VkNTU4MjAxZjdjMWQ5MjAzMjA4ZGM3ZjBjZjMzNWRkNjM5MmEyZTVjMmZiNWI5ZGIwNjk2Ng',1751655254,NULL);
/*!40000 ALTER TABLE `oauth_access_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_auth_code`
--

DROP TABLE IF EXISTS `oauth_auth_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_auth_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4D12F0E05F37A13B` (`token`),
  KEY `IDX_4D12F0E019EB6921` (`client_id`),
  KEY `IDX_4D12F0E0A76ED395` (`user_id`),
  CONSTRAINT `FK_4D12F0E019EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`id`),
  CONSTRAINT `FK_4D12F0E0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_auth_code`
--

LOCK TABLES `oauth_auth_code` WRITE;
/*!40000 ALTER TABLE `oauth_auth_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_client`
--

DROP TABLE IF EXISTS `oauth_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_client`
--

LOCK TABLES `oauth_client` WRITE;
/*!40000 ALTER TABLE `oauth_client` DISABLE KEYS */;
INSERT INTO `oauth_client` VALUES (4,'5d9wagvs4ewws00sw0kk80wowokg444w8cwowg0k8c0k80k0wc','a:0:{}','62iggnys1qosc4884gc8c40s8scgwcsc4cck4ocsg8kg0488kk','a:2:{i:0;s:18:\"client_credentials\";i:1;s:8:\"password\";}');
/*!40000 ALTER TABLE `oauth_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_token`
--

DROP TABLE IF EXISTS `oauth_refresh_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_refresh_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_55DCF7555F37A13B` (`token`),
  KEY `IDX_55DCF75519EB6921` (`client_id`),
  KEY `IDX_55DCF755A76ED395` (`user_id`),
  CONSTRAINT `FK_55DCF75519EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`id`),
  CONSTRAINT `FK_55DCF755A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_token`
--

LOCK TABLES `oauth_refresh_token` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_token` DISABLE KEYS */;
INSERT INTO `oauth_refresh_token` VALUES (1,4,1,'NGM3NTIxZTQ2Mzc0OWRlNWQ3MWEzNjQ4ZTI2NzcxYjRiNGMzMTQxMjI4YjEwNTZlOGYxYjkxMjdlNzFiMDQyMg',1437494919,NULL),(2,4,1,'OTdhMmIwNTkxOWMzZjJkYzY4NWU1ZGI2NTE4YmZlZTAwNzBiZWE0NjNmZTRlMDFiZGM0NmQyMDM4NDBlNmEyZA',1437495339,NULL),(3,4,1,'NjY4NjIwMDMyNTEzYzUzYTkwNTg2NjFjNjkzZWZjOGExYjNjZWU5ZmM5ZTg4ODllNjQwNDRiOGU2MzQ4ZjhhMg',1437495390,NULL),(4,4,1,'Nzc2ZGJiZGZhZTY2YTYxMjE4YTE1Mjc4M2JjNjA3Y2M0ZDIyZmE2YmUxMDRjODU3YTJiMTM2NjFmOWNkY2ZlNw',1437497180,NULL),(5,4,1,'N2IwMmZjM2FkYjZhNzhiMzY5YTdmMjg2MzliZDdjMDI5ODU1YmNkYWMxNmJmNTYwYjIyYzdkYzgyNjNhOTY3Zg',1437498081,NULL),(6,4,1,'ZDkzNDJmMjQxZGQ2YTM5YTFlYTc0YzcyM2NmYTEwYjYwODY5OTk2Y2EyZjcwNmFiOTBjYjY1MmY3YWJiZDAxMQ',1437498909,NULL),(7,4,1,'ZjQ4NTU5MDQzOTU5Yjk4YzExZjg1N2NmNTViZGM4M2Y4MzJhYWRiNjI1MmRlNTBmMTU2ZWI3NDJkZWVkMTllZQ',1437499140,NULL),(8,4,1,'YzljMTE1NTRjN2M2MDBjY2VhZTMwZTA4MjRhNWE5MWRjNDU4YTg4N2QxNGZjOTAxMjk4MTRmZmQ4Y2ZmNGFiZA',1437499739,NULL),(9,4,1,'OGMzZTM3Y2U2OTFkODI1NDRiM2U3ZWFlM2QxOGMwM2M1OGNmZTdmNWE2YjE4NzI0ZDA4YTcyZmFmMDFiMDIyMA',1437500078,NULL);
/*!40000 ALTER TABLE `oauth_refresh_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `balance` int(11) DEFAULT NULL,
  `flag_credit_debit` int(11) NOT NULL,
  `type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ref` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6D28840DA76ED395` (`user_id`),
  CONSTRAINT `FK_6D28840DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_to_id` int(11) NOT NULL,
  `shipping_id` int(11) NOT NULL,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_794381C6A76ED395` (`user_id`),
  KEY `IDX_794381C6D2F7B13D` (`user_to_id`),
  KEY `IDX_794381C64887F3F8` (`shipping_id`),
  CONSTRAINT `FK_794381C64887F3F8` FOREIGN KEY (`shipping_id`) REFERENCES `shipping` (`id`),
  CONSTRAINT `FK_794381C6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_794381C6D2F7B13D` FOREIGN KEY (`user_to_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `review`
--

LOCK TABLES `review` WRITE;
/*!40000 ALTER TABLE `review` DISABLE KEYS */;
/*!40000 ALTER TABLE `review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping`
--

DROP TABLE IF EXISTS `shipping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `carrier_id` int(11) DEFAULT NULL,
  `name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `weight` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `z` int(11) DEFAULT NULL,
  `delivery_price` int(11) NOT NULL DEFAULT '0',
  `payment_guarantee` tinyint(1) NOT NULL DEFAULT '0',
  `insurance` int(11) NOT NULL DEFAULT '0',
  `pickup_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pickup_longitude` decimal(12,8) DEFAULT NULL,
  `pickup_latitude` decimal(12,8) DEFAULT NULL,
  `delivery_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_longitude` decimal(12,8) DEFAULT NULL,
  `delivery_latitude` decimal(12,8) DEFAULT NULL,
  `pickup_datetime` datetime DEFAULT NULL,
  `delivery_datetime` datetime NOT NULL,
  `state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `has_user_review` tinyint(1) NOT NULL DEFAULT '0',
  `has_carrier_review` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_2D1C1724A76ED395` (`user_id`),
  KEY `IDX_2D1C172421DFC797` (`carrier_id`),
  CONSTRAINT `FK_2D1C172421DFC797` FOREIGN KEY (`carrier_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_2D1C1724A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping`
--

LOCK TABLES `shipping` WRITE;
/*!40000 ALTER TABLE `shipping` DISABLE KEYS */;
INSERT INTO `shipping` VALUES (18,1,NULL,'Радужные розы',NULL,NULL,NULL,NULL,NULL,150,0,2000,'Санкт-Петербург, пр. Чернышевского, д. 9',NULL,NULL,'Санкт-Петербург, Малый пр. П.С., 48',NULL,NULL,NULL,'2015-07-07 19:00:00','new','2015-07-07 16:55:36','2015-07-07 16:55:36',0,0),(19,1,NULL,'Нежно - розовые розы',NULL,NULL,NULL,NULL,NULL,300,0,2000,'Ломоносов г. Ломоносов, ул. Владимирская, д. 25',NULL,NULL,'Санкт-Петербург, Колокольная ул., 9',NULL,NULL,NULL,'2015-07-08 01:30:00','new','2015-07-07 16:55:37','2015-07-07 16:55:37',0,0),(20,1,NULL,'Радужные розы',NULL,NULL,NULL,NULL,NULL,350,0,0,'Санкт-Петербург, ул. Типанова, д. 3',NULL,NULL,'Санкт-Петербург, Казанская ул., 2',NULL,NULL,NULL,'2015-07-07 21:15:00','new','2015-07-07 16:55:38','2015-07-07 16:55:38',0,0),(21,1,NULL,'Нежно - розовые розы',NULL,NULL,NULL,NULL,NULL,150,1,1000,'Выборг г. Выборг, ул. Северная, д. 6',NULL,NULL,'Санкт-Петербург, Славы пр., 40',NULL,NULL,NULL,'2015-07-08 00:45:00','new','2015-07-07 16:55:39','2015-07-07 16:55:39',0,0),(22,1,NULL,'Красные розы',NULL,NULL,NULL,NULL,NULL,300,1,0,'Санкт-Петербург, Наличная ул., д. 49',NULL,NULL,'Санкт-Петербург, Колокольная ул., 9',NULL,NULL,NULL,'2015-07-07 21:15:00','new','2015-07-07 16:55:40','2015-07-07 16:55:40',0,0);
/*!40000 ALTER TABLE `shipping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `first_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `patronymic` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `img_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `balance` int(11) NOT NULL DEFAULT '0',
  `hold_amount` int(11) NOT NULL DEFAULT '0',
  `company_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `profile_type` int(11) NOT NULL DEFAULT '0',
  `about_me` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_mask` int(11) NOT NULL DEFAULT '0',
  `email_mask` int(11) NOT NULL DEFAULT '0',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `sender_rating` double NOT NULL DEFAULT '0',
  `sender_rating_amount` int(11) NOT NULL DEFAULT '0',
  `sender_rating_count` int(11) NOT NULL DEFAULT '0',
  `carrier_rating` double NOT NULL DEFAULT '0',
  `carrier_rating_amount` int(11) NOT NULL DEFAULT '0',
  `carrier_rating_count` int(11) NOT NULL DEFAULT '0',
  `recover_password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recover_salt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `sender_count` int(11) NOT NULL DEFAULT '0',
  `carrier_count` int(11) NOT NULL DEFAULT '0',
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D64992FC23A8` (`username_canonical`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'9052043263','9052043263','1',1,'atn8h9zlp20cg0s8ww0os40k4wogcc0','MxFkL/WjPwv8Rcq8T1EjWFvMG+pd81ywfMTuUZO39pXOTlM/fXlhXYpqLdkV0vf5AiLnzuP/qMdKp3Mu35x1Yw==','2015-07-07 16:48:17',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'profile/32.jpg',0,0,NULL,'1',0,NULL,NULL,33554431,33554431,0,0,0,0,0,0,0,NULL,NULL,'2015-07-07 15:44:38',0,0,'1'),(2,'9600161345','9600161345','1',1,'nu73ygj7ni840c0cw84kkg80gwcocos','6VxLjgf+dzUIKGGfOydnQAS+E5b3o81G69levZ5X+9peIiiehcIeTx9rXrMJXj8AsT5B/k/dOROpcd4Wa1Ch1A==',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'profile/24.jpg',0,0,NULL,'1',0,NULL,NULL,33554431,33554431,0,0,0,0,0,0,0,NULL,NULL,'2015-07-07 15:44:54',0,0,'1');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-08 23:17:17
