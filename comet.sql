-- MySQL dump 10.13  Distrib 5.1.31, for apple-darwin9.5.0 (i386)
--
-- Host: localhost    Database: comet
-- ------------------------------------------------------
-- Server version	5.1.31

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
-- Table structure for table `actionlog`
--

DROP TABLE IF EXISTS `actionlog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `actionlog` (
  `actionID` int(11) NOT NULL AUTO_INCREMENT,
  `action` text NOT NULL,
  `userID` int(3) NOT NULL,
  `runtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`actionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='History of SQL actions';
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `details`
--

DROP TABLE IF EXISTS `details`;
/*!50001 DROP VIEW IF EXISTS `details`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `details` (
  `cardNo` int(6),
  `address` varchar(200),
  `phone` varchar(18),
  `city` varchar(30),
  `state` varchar(3),
  `zip` varchar(10),
  `email` varchar(50),
  `nextPayment` date,
  `paymentPlan` int(2),
  `joined` date,
  `sharePrice` decimal(6,2),
  `startDate` date,
  `endDate` date,
  `userID` int(3)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `lists`
--

DROP TABLE IF EXISTS `lists`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `lists` (
  `listID` int(3) NOT NULL AUTO_INCREMENT,
  `description` varchar(256) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `userID` int(3) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Mailing list descriptions';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `notes` (
  `note` text NOT NULL,
  `threadID` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `cardNo` int(6) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `userID` tinyint(3) NOT NULL,
  PRIMARY KEY (`threadID`),
  KEY `parentID` (`parentID`,`cardNo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Notes about members and responses.';
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `owners`
--

DROP TABLE IF EXISTS `owners`;
/*!50001 DROP VIEW IF EXISTS `owners`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `owners` (
  `cardNo` int(6),
  `personNum` tinyint(1),
  `firstName` varchar(50),
  `lastName` varchar(50),
  `discount` tinyint(2),
  `memType` tinyint(2),
  `staff` tinyint(2),
  `chargeOk` tinyint(1),
  `writeChecks` tinyint(1),
  `startDate` date,
  `endDate` date,
  `userID` int(3)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `paymentPlans`
--

DROP TABLE IF EXISTS `paymentPlans`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `paymentPlans` (
  `planID` int(2) NOT NULL AUTO_INCREMENT,
  `frequency` int(1) NOT NULL,
  `amount` double(5,2) NOT NULL,
  PRIMARY KEY (`planID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `memo` text,
  `amount` decimal(6,2) NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(12) DEFAULT NULL,
  `userID` tinyint(3) NOT NULL,
  `cardNo` int(6) NOT NULL,
  PRIMARY KEY (`paymentID`),
  KEY `cardNo` (`cardNo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Table to track equity payments.';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `raw_details`
--

DROP TABLE IF EXISTS `raw_details`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `raw_details` (
  `cardNo` int(6) NOT NULL,
  `address` varchar(200) NOT NULL,
  `phone` varchar(18) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nextPayment` date DEFAULT NULL,
  `paymentPlan` int(2) NOT NULL,
  `joined` date NOT NULL,
  `sharePrice` decimal(6,2) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `userID` int(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COMMENT='Details table for versioning';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `raw_owners`
--

DROP TABLE IF EXISTS `raw_owners`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `raw_owners` (
  `cardNo` int(6) NOT NULL,
  `personNum` tinyint(1) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `discount` tinyint(2) DEFAULT NULL,
  `memType` tinyint(2) NOT NULL,
  `staff` tinyint(2) NOT NULL,
  `chargeOk` tinyint(1) NOT NULL,
  `writeChecks` tinyint(1) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `userID` int(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Raw info about owners. Versioning enabled.';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subscriptions` (
  `cardNo` int(6) NOT NULL,
  `listID` int(3) NOT NULL,
  `userID` int(3) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`cardNo`,`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='List of subscriptions for members.';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `user` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `level` int(2) NOT NULL,
  `userID` int(3) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `details`
--

/*!50001 DROP TABLE `details`*/;
/*!50001 DROP VIEW IF EXISTS `details`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `details` AS select `raw_details`.`cardNo` AS `cardNo`,`raw_details`.`address` AS `address`,`raw_details`.`phone` AS `phone`,`raw_details`.`city` AS `city`,`raw_details`.`state` AS `state`,`raw_details`.`zip` AS `zip`,`raw_details`.`email` AS `email`,`raw_details`.`nextPayment` AS `nextPayment`,`raw_details`.`paymentPlan` AS `paymentPlan`,`raw_details`.`joined` AS `joined`,`raw_details`.`sharePrice` AS `sharePrice`,`raw_details`.`startDate` AS `startDate`,`raw_details`.`endDate` AS `endDate`,`raw_details`.`userID` AS `userID` from `raw_details` where isnull(`raw_details`.`endDate`) group by `raw_details`.`cardNo` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `owners`
--

/*!50001 DROP TABLE `owners`*/;
/*!50001 DROP VIEW IF EXISTS `owners`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `owners` AS select `raw_owners`.`cardNo` AS `cardNo`,`raw_owners`.`personNum` AS `personNum`,`raw_owners`.`firstName` AS `firstName`,`raw_owners`.`lastName` AS `lastName`,`raw_owners`.`discount` AS `discount`,`raw_owners`.`memType` AS `memType`,`raw_owners`.`staff` AS `staff`,`raw_owners`.`chargeOk` AS `chargeOk`,`raw_owners`.`writeChecks` AS `writeChecks`,`raw_owners`.`startDate` AS `startDate`,`raw_owners`.`endDate` AS `endDate`,`raw_owners`.`userID` AS `userID` from `raw_owners` where isnull(`raw_owners`.`endDate`) group by `raw_owners`.`cardNo`,`raw_owners`.`personNum` */;
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

-- Dump completed on 2009-08-12 10:51:42
