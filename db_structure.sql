--
-- Host: localhost    Database: OpenVPN_Webadmin
-- ------------------------------------------------------
-- Server version       8.0.32

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
-- Table structure for table `API_Keys`
--

DROP TABLE IF EXISTS `API_Keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `API_Keys` (
  `api_key_ID` int NOT NULL AUTO_INCREMENT,
  `server_ID` int NOT NULL,
  `api_key` varchar(128) NOT NULL,
  `user_ID` int NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`api_key_ID`),
  KEY `API_Keys_User` (`user_ID`),
  KEY `API_Keys_VPN_Servers` (`server_ID`),
  CONSTRAINT `API_Keys_User` FOREIGN KEY (`user_ID`) REFERENCES `User` (`user_ID`),
  CONSTRAINT `API_Keys_VPN_Servers` FOREIGN KEY (`server_ID`) REFERENCES `VPN_Servers` (`server_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Forwarded_Ports`
--

DROP TABLE IF EXISTS `Forwarded_Ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Forwarded_Ports` (
  `port_forward_ID` int NOT NULL AUTO_INCREMENT,
  `server_ID` int NOT NULL,
  `forwarded_port` int NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`port_forward_ID`),
  KEY `Forwarded_Ports_VPN_Servers` (`server_ID`),
  CONSTRAINT `Forwarded_Ports_VPN_Servers` FOREIGN KEY (`server_ID`) REFERENCES `VPN_Servers` (`server_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Login_Attempts`
--

DROP TABLE IF EXISTS `Login_Attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Login_Attempts` (
  `attempt_ID` int NOT NULL AUTO_INCREMENT,
  `user_ID` int NOT NULL,
  `ip_address` bigint NOT NULL,
  `login_time` bigint NOT NULL,
  `login_successful` tinyint(1) NOT NULL,
  PRIMARY KEY (`attempt_ID`),
  KEY `Login_Attempts_User` (`user_ID`),
  CONSTRAINT `Login_Attempts_User` FOREIGN KEY (`user_ID`) REFERENCES `User` (`user_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User` (
  `user_ID` int NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(75) NOT NULL,
  `usertype_ID` int NOT NULL,
  PRIMARY KEY (`user_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `VPN_Clients`
--

DROP TABLE IF EXISTS `VPN_Clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VPN_Clients` (
  `client_ID` int NOT NULL AUTO_INCREMENT,
  `server_ID` int NOT NULL,
  `user_ID` int NOT NULL,
  `client_name` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`client_ID`),
  KEY `VPN_Clients_User` (`user_ID`),
  KEY `VPN_Clients_VPN_Servers` (`server_ID`),
  CONSTRAINT `VPN_Clients_User` FOREIGN KEY (`user_ID`) REFERENCES `User` (`user_ID`),
  CONSTRAINT `VPN_Clients_VPN_Servers` FOREIGN KEY (`server_ID`) REFERENCES `VPN_Servers` (`server_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `VPN_Keys`
--

DROP TABLE IF EXISTS `VPN_Keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VPN_Keys` (
  `vpn_key_ID` int NOT NULL AUTO_INCREMENT,
  `client_ID` int NOT NULL,
  `vpn_ca` text NOT NULL,
  `vpn_cert` text NOT NULL,
  `vpn_priv_key` text NOT NULL,
  `vpn_tls_crypt` text NOT NULL,
  PRIMARY KEY (`vpn_key_ID`),
  KEY `VPN_Keys_VPN_Clients` (`client_ID`),
  CONSTRAINT `VPN_Keys_VPN_Clients` FOREIGN KEY (`client_ID`) REFERENCES `VPN_Clients` (`client_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `VPN_Server_Settings`
--

DROP TABLE IF EXISTS `VPN_Server_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VPN_Server_Settings` (
  `settings_ID` int NOT NULL AUTO_INCREMENT,
  `server_ID` int NOT NULL,
  `vpn_port` int NOT NULL,
  `vpn_protocol` varchar(16) NOT NULL,
  `vpn_dns` bigint NOT NULL,
  `webserver_port` int NOT NULL,
  `tls_crypt_on` tinyint(1) NOT NULL,
  `portforwarding_on` tinyint(1) NOT NULL,
  PRIMARY KEY (`settings_ID`),
  KEY `VPN_Server_Settings_VPN_Servers` (`server_ID`),
  CONSTRAINT `VPN_Server_Settings_VPN_Servers` FOREIGN KEY (`server_ID`) REFERENCES `VPN_Servers` (`server_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `VPN_Servers`
--

DROP TABLE IF EXISTS `VPN_Servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VPN_Servers` (
  `server_ID` int NOT NULL AUTO_INCREMENT,
  `ip_address` bigint NOT NULL,
  `domain_name` varchar(256) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`server_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-05-02 23:56:54
