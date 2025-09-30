-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for Linux (x86_64)
--
-- Host: studdb.csc.liv.ac.uk    Database: sgnsee
-- ------------------------------------------------------
-- Server version	10.5.27-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `train_time`
--

DROP TABLE IF EXISTS `train_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `train_time` (
  `topic` varchar(50) NOT NULL,
  `time` varchar(50) NOT NULL,
  PRIMARY KEY (`topic`,`time`),
  CONSTRAINT `train_time_ibfk_1` FOREIGN KEY (`topic`) REFERENCES `train_capacity` (`topic`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `train_time`
--

LOCK TABLES `train_time` WRITE;
/*!40000 ALTER TABLE `train_time` DISABLE KEYS */;
INSERT INTO `train_time` VALUES ('Email','Tuesday, 12:00'),('Email','Wednesday, 10:00'),('Library Use','Wednesday, 11:00'),('Presentation Software','Monday, 10:00'),('Presentation Software','Thursday, 12:00'),('Spreadsheets','Friday, 12:00'),('Spreadsheets','Tuesday, 11:00'),('Word Processing','Friday, 12:00'),('Word Processing','Monday, 10:00'),('Word Processing','Wednesday, 11:00');
/*!40000 ALTER TABLE `train_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `train_capacity`
--

DROP TABLE IF EXISTS `train_capacity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `train_capacity` (
  `topic` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  PRIMARY KEY (`topic`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `train_capacity`
--

LOCK TABLES `train_capacity` WRITE;
/*!40000 ALTER TABLE `train_capacity` DISABLE KEYS */;
INSERT INTO `train_capacity` VALUES ('Email',3),('Library Use',2),('Presentation Software',2),('Spreadsheets',3),('Word Processing',4);
/*!40000 ALTER TABLE `train_capacity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `email` varchar(320) NOT NULL,
  `name` varchar(50) NOT NULL,
  `topic` varchar(50) NOT NULL,
  `time` varchar(50) NOT NULL,
  PRIMARY KEY (`email`,`topic`,`time`),
  KEY `topic` (`topic`,`time`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`topic`, `time`) REFERENCES `train_time` (`topic`, `time`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-06 14:55:24
