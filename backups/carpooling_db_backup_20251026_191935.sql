-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: carpooling_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passenger_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `seats_requested` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','rejected','cancelled') DEFAULT 'pending',
  `total_cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_active_reservation` (`passenger_id`,`ride_id`,`status`),
  KEY `idx_passenger` (`passenger_id`),
  KEY `idx_ride` (`ride_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_passenger_status` (`passenger_id`,`status`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,5,1,2,'pending',3000.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(2,6,5,1,'accepted',1200.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(3,7,7,1,'pending',1800.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(4,5,3,1,'accepted',1500.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(5,5,5,2,'pending',2400.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(6,6,7,1,'accepted',1800.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(7,6,8,1,'rejected',1800.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(8,7,2,2,'cancelled',3000.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(9,7,6,1,'accepted',1200.00,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(10,5,10,2,'accepted',4000.00,'2025-10-09 16:00:00','2025-10-18 02:40:22'),(11,6,10,1,'cancelled',2000.00,'2025-10-09 17:00:00','2025-10-18 02:40:22'),(12,7,11,2,'rejected',3600.00,'2025-10-10 15:00:00','2025-10-18 02:40:22');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER before_reservation_insert
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE available INT;
    
    SELECT available_seats INTO available
    FROM rides
    WHERE id = NEW.ride_id;
    
    IF NEW.seats_requested > available THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay suficientes asientos disponibles';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_reservation_insert
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    IF NEW.status = 'pending' OR NEW.status = 'accepted' THEN
        UPDATE rides
        SET available_seats = available_seats - NEW.seats_requested
        WHERE id = NEW.ride_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_reservation_update
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    -- Si se cancela o rechaza, devolver los asientos
    IF (OLD.status = 'pending' OR OLD.status = 'accepted') 
       AND (NEW.status = 'cancelled' OR NEW.status = 'rejected') THEN
        UPDATE rides
        SET available_seats = available_seats + OLD.seats_requested
        WHERE id = OLD.ride_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `rides`
--

DROP TABLE IF EXISTS `rides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `ride_name` varchar(255) NOT NULL,
  `departure_location` varchar(255) NOT NULL,
  `arrival_location` varchar(255) NOT NULL,
  `ride_date` date NOT NULL,
  `ride_time` time NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `cost_per_seat` decimal(10,2) NOT NULL,
  `available_seats` int(11) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_driver` (`driver_id`),
  KEY `idx_vehicle` (`vehicle_id`),
  KEY `idx_date` (`ride_date`),
  KEY `idx_locations` (`departure_location`,`arrival_location`),
  KEY `idx_active` (`is_active`),
  KEY `idx_available_seats` (`available_seats`),
  KEY `idx_rides_search` (`departure_location`,`arrival_location`,`ride_date`,`is_active`),
  CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rides`
--

LOCK TABLES `rides` WRITE;
/*!40000 ALTER TABLE `rides` DISABLE KEYS */;
INSERT INTO `rides` VALUES (1,2,1,'San José - Cartago Mañana','San José Centro','Cartago Centro','2025-10-20','07:00:00','Monday',1500.00,2,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(2,2,1,'San José - Cartago Tarde','San José Centro','Cartago Centro','2025-10-20','17:00:00','Monday',1500.00,4,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(3,2,1,'Cartago - San José Mañana','Cartago Centro','San José Centro','2025-10-21','06:30:00','Tuesday',1500.00,3,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(4,2,1,'Cartago - San José Tarde','Cartago Centro','San José Centro','2025-10-21','18:00:00','Tuesday',1500.00,4,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(5,3,3,'San José - Heredia Expreso','San José Centro','Heredia Centro','2025-10-22','07:30:00','Wednesday',1200.00,2,5,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(6,3,3,'Heredia - San José Retorno','Heredia Centro','San José Centro','2025-10-22','17:30:00','Wednesday',1200.00,4,5,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(7,4,5,'Alajuela - San José Diario','Alajuela Centro','San José Centro','2025-10-23','06:00:00','Thursday',1800.00,2,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(8,4,5,'San José - Alajuela Retorno','San José Centro','Alajuela Centro','2025-10-23','18:30:00','Thursday',1800.00,4,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(9,2,1,'San José - Cartago Pasado','San José Centro','Cartago Centro','2025-10-10','07:00:00','Friday',1500.00,2,4,0,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(10,3,3,'Heredia - San José Pasado','Heredia Centro','San José Centro','2025-10-11','17:00:00','Saturday',1200.00,1,5,0,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(11,2,2,'Prueba Reservas Múltiples','San José','Puntarenas','2025-10-25','08:00:00','Saturday',3500.00,4,4,1,'2025-10-18 02:40:22','2025-10-18 02:40:22');
/*!40000 ALTER TABLE `rides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('admin','driver','passenger') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `activation_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_cedula` (`cedula`),
  KEY `idx_status` (`status`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_activation_token` (`activation_token`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Admin','System','000000000','1990-01-01','admin@carpooling.com','8888-8888',NULL,'$2y$10$GXJ5phIgXtMTu010qvY5XusMzE07SW4VCFT255SM9qkbV.yJzH6Ei','active',NULL,'2025-10-18 02:40:22','2025-10-25 05:42:33'),(2,'driver','Juan','Pérez','101110111','1985-03-15','juan.perez@email.com','8888-1111',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(3,'driver','María','García','202220222','1990-07-22','maria.garcia@email.com','8888-2222',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(4,'driver','Carlos','Rodríguez','303330333','1988-11-30','carlos.rodriguez@email.com','8888-3333',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(5,'passenger','Ana','Martínez','404440444','1995-02-14','ana.martinez@email.com','8888-4444',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(6,'passenger','Pedro','López','505550555','1992-08-25','pedro.lopez@email.com','8888-5555',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(7,'passenger','Lucía','Fernández','606660666','1998-12-05','lucia.fernandez@email.com','8888-6666',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(8,'passenger','Usuario','Pendiente','707770777','2000-05-10','pendiente@email.com','8888-7777',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','inactive','test_activation_token_123456','2025-10-18 02:40:22','2025-10-26 02:38:05'),(9,'passenger','Usuario','Inactivo','808880888','1987-09-18','inactivo@email.com','8888-8888',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','inactive',NULL,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(13,'passenger','Pedro','Mendez','205560455','1990-10-01','pedrom@email.com','45453423',NULL,'$2y$10$CnWiS7Wu.BJjDe8NthZtQ.YhsndNtQ092xJQd/e8fajLm3vcQn4zO','active','1b9263c2f6a557e1869a4dfdd12bb78db023caf61c7c9bd9dc58ecad950cd77c','2025-10-26 07:09:41','2025-10-26 07:09:41');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_reservations_complete`
--

DROP TABLE IF EXISTS `v_reservations_complete`;
/*!50001 DROP VIEW IF EXISTS `v_reservations_complete`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_reservations_complete` AS SELECT
 1 AS `id`,
  1 AS `passenger_id`,
  1 AS `ride_id`,
  1 AS `seats_requested`,
  1 AS `status`,
  1 AS `total_cost`,
  1 AS `created_at`,
  1 AS `updated_at`,
  1 AS `passenger_first_name`,
  1 AS `passenger_last_name`,
  1 AS `passenger_email`,
  1 AS `passenger_phone`,
  1 AS `passenger_photo`,
  1 AS `ride_name`,
  1 AS `departure_location`,
  1 AS `arrival_location`,
  1 AS `ride_date`,
  1 AS `ride_time`,
  1 AS `driver_id`,
  1 AS `driver_first_name`,
  1 AS `driver_last_name`,
  1 AS `driver_email`,
  1 AS `driver_phone`,
  1 AS `vehicle_brand`,
  1 AS `vehicle_model`,
  1 AS `vehicle_plate` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_rides_complete`
--

DROP TABLE IF EXISTS `v_rides_complete`;
/*!50001 DROP VIEW IF EXISTS `v_rides_complete`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_rides_complete` AS SELECT
 1 AS `id`,
  1 AS `driver_id`,
  1 AS `vehicle_id`,
  1 AS `ride_name`,
  1 AS `departure_location`,
  1 AS `arrival_location`,
  1 AS `ride_date`,
  1 AS `ride_time`,
  1 AS `day_of_week`,
  1 AS `cost_per_seat`,
  1 AS `available_seats`,
  1 AS `total_seats`,
  1 AS `is_active`,
  1 AS `created_at`,
  1 AS `updated_at`,
  1 AS `driver_first_name`,
  1 AS `driver_last_name`,
  1 AS `driver_email`,
  1 AS `driver_phone`,
  1 AS `driver_photo`,
  1 AS `plate_number`,
  1 AS `vehicle_color`,
  1 AS `vehicle_brand`,
  1 AS `vehicle_model`,
  1 AS `vehicle_year`,
  1 AS `vehicle_photo`,
  1 AS `reserved_seats` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `color` varchar(50) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `seats_capacity` int(11) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `idx_driver` (`driver_id`),
  KEY `idx_plate` (`plate_number`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (1,2,'ABC-123','Rojo','Toyota','Corolla',2020,4,NULL,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(2,2,'DEF-456','Azul','Honda','Civic',2019,4,NULL,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(3,3,'GHI-789','Blanco','Mazda','CX-5',2021,5,NULL,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(4,3,'JKL-012','Negro','Nissan','Sentra',2018,4,NULL,0,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(5,4,'MNO-345','Gris','Hyundai','Elantra',2022,4,NULL,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(6,4,'PQR-678','Verde','Kia','Sportage',2020,5,NULL,1,'2025-10-18 02:40:22','2025-10-18 02:40:22'),(10,4,'FAR-534','Verde','Toyota','Rav4',2006,5,NULL,1,'2025-10-26 07:10:38','2025-10-26 07:10:38');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `v_reservations_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_reservations_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reservations_complete` AS select `res`.`id` AS `id`,`res`.`passenger_id` AS `passenger_id`,`res`.`ride_id` AS `ride_id`,`res`.`seats_requested` AS `seats_requested`,`res`.`status` AS `status`,`res`.`total_cost` AS `total_cost`,`res`.`created_at` AS `created_at`,`res`.`updated_at` AS `updated_at`,`u`.`first_name` AS `passenger_first_name`,`u`.`last_name` AS `passenger_last_name`,`u`.`email` AS `passenger_email`,`u`.`phone` AS `passenger_phone`,`u`.`photo_path` AS `passenger_photo`,`r`.`ride_name` AS `ride_name`,`r`.`departure_location` AS `departure_location`,`r`.`arrival_location` AS `arrival_location`,`r`.`ride_date` AS `ride_date`,`r`.`ride_time` AS `ride_time`,`r`.`driver_id` AS `driver_id`,`d`.`first_name` AS `driver_first_name`,`d`.`last_name` AS `driver_last_name`,`d`.`email` AS `driver_email`,`d`.`phone` AS `driver_phone`,`v`.`brand` AS `vehicle_brand`,`v`.`model` AS `vehicle_model`,`v`.`plate_number` AS `vehicle_plate` from ((((`reservations` `res` join `users` `u` on(`res`.`passenger_id` = `u`.`id`)) join `rides` `r` on(`res`.`ride_id` = `r`.`id`)) join `users` `d` on(`r`.`driver_id` = `d`.`id`)) join `vehicles` `v` on(`r`.`vehicle_id` = `v`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_rides_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_rides_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_rides_complete` AS select `r`.`id` AS `id`,`r`.`driver_id` AS `driver_id`,`r`.`vehicle_id` AS `vehicle_id`,`r`.`ride_name` AS `ride_name`,`r`.`departure_location` AS `departure_location`,`r`.`arrival_location` AS `arrival_location`,`r`.`ride_date` AS `ride_date`,`r`.`ride_time` AS `ride_time`,`r`.`day_of_week` AS `day_of_week`,`r`.`cost_per_seat` AS `cost_per_seat`,`r`.`available_seats` AS `available_seats`,`r`.`total_seats` AS `total_seats`,`r`.`is_active` AS `is_active`,`r`.`created_at` AS `created_at`,`r`.`updated_at` AS `updated_at`,`u`.`first_name` AS `driver_first_name`,`u`.`last_name` AS `driver_last_name`,`u`.`email` AS `driver_email`,`u`.`phone` AS `driver_phone`,`u`.`photo_path` AS `driver_photo`,`v`.`plate_number` AS `plate_number`,`v`.`color` AS `vehicle_color`,`v`.`brand` AS `vehicle_brand`,`v`.`model` AS `vehicle_model`,`v`.`year` AS `vehicle_year`,`v`.`photo_path` AS `vehicle_photo`,`r`.`total_seats` - `r`.`available_seats` AS `reserved_seats` from ((`rides` `r` join `users` `u` on(`r`.`driver_id` = `u`.`id`)) join `vehicles` `v` on(`r`.`vehicle_id` = `v`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-26 19:19:35
