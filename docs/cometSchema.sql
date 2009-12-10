-- MySQL dump 10.13  Distrib 5.1.37, for apple-darwin10.0.0 (i386)
--
-- Host: localhost    Database: comet_structure
-- ------------------------------------------------------
-- Server version	5.1.37

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
  `noMail` tinyint(1),
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
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `note` text NOT NULL,
  `threadID` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `cardNo` int(6) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `userID` tinyint(3) NOT NULL,
  PRIMARY KEY (`threadID`),
  KEY `parentID` (`parentID`,`cardNo`)
) ENGINE=InnoDB AUTO_INCREMENT=2120 DEFAULT CHARSET=latin1 COMMENT='Notes about members and responses.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `value` varchar(1500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options`
--

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` VALUES (1,'pastDueDays','15'),(2,'comingDueDays','15'),(3,'inactiveDays','270'),(4,'comingDueMsg','Dear [firstName] [lastName],\n\nI am writing to let you know that it\'s time for your next payment towards your membership share.  Your next payment is due on [dueDate].  You are currently on the [paymentPlan] payment plan.  The remaining balance on your share is [balance].  \n\nIf you have any questions or feedback regarding your membership, please get in touch.  If you need a more flexible payment plan we provide several options, please see the Membership Application for a full list.  Thanks for being a part of the Co-op!\n\nSincerely, \nMember Services Coordinator'),(5,'pastDueMsg','Hello  [firstName] [lastName],  \n\nThis is a friendly reminder that your next payment on your Co-op membership share is past due.  Your last payment was due on [dueDate].  We will continue to honor your membership for 30 days after the due date to give you time to make your payment.  The remaining balance on your share is [balance], and you are currently on the [paymentPlan] payment plan.  \n\nRemember, if you need a more flexible payment plan, we provide several options which are listed on our Membership Application.  If you have any questions or concerns about your membership please don\'t hesitate to contact me.  If you would like to end your Co-op membership please let me know so that I can send you an equity refund/donation form.  Thank you for being a part of the Co-op!\n\nSincerely,\nMember Services Coordinator'),(6,'inactiveMsg','Hello [firstName] [lastName],  \n\nThis is a reminder that your last Co-op membership payment has been past due for 9 months.  Your most recent renewal payment was due on [dueDate]. The remaining balance on your share is [balance].  \n\nYou have been moved to â€œinactiveâ€ status meaning that you are not currently eligible for any of the benefits of membership.  You may make another payment at any time to reactivate your membership.   Please remember that we do provide a variety of payment plan options, see our Membership Application for a full list.  \n\nIf you have questions or concerns about your Co-op membership please contact me.  If you would like to end your membership please let me know so that I can send you a refund/donation form (this is important for us because we cannot use your equity unless you are a current member or have donated it to the Co-op).  Thank you for being a part of the Co-op community, I hope to hear from you soon!\n\nSincerely, \nMember Services Coordinator'),(7,'smtpUser','memberServices'),(8,'smtpPass','password'),(9,'smtpHost','smtp'),(14,'logHost','localhost'),(10,'opHost','localhost'),(11,'opUser','root'),(12,'opPass','password'),(13,'opDB','ACG_is4c_op'),(15,'logUser','root'),(16,'logPass','password'),(17,'logDB','ACG_is4c_log'),(18,'houseHoldSize','2'),(19,'discounts','0,2,5,15'),(20,'sharePrice','180.00'),(21,'defaultPayment','45.00'),(22,'defaultPlan','1'),(23,'defaultState','OR'),(24,'defaultStaff','0'),(25,'defaultMemType','2'),(26,'defaultCheck','1'),(27,'defaultDiscount','2'),(28,'systemUser','comet'),(29,'systemPass','password'),(30,'syncURL','http://fannie/sync/reload.php?table=custdata'),(31,'reminderEmail','memberServices@somewhere.com'),(32,'reminderFrom','Member Services');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentPlans` (
  `planID` int(2) NOT NULL AUTO_INCREMENT,
  `frequency` int(1) NOT NULL,
  `amount` double(5,2) NOT NULL,
  PRIMARY KEY (`planID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentPlans`
--

LOCK TABLES `paymentPlans` WRITE;
/*!40000 ALTER TABLE `paymentPlans` DISABLE KEYS */;
INSERT INTO `paymentPlans` VALUES (1,1,45.00),(2,1,90.00),(3,1,180.00),(4,1,30.00),(5,2,15.00),(6,1,50.00),(7,1,36.00),(8,2,22.50);
/*!40000 ALTER TABLE `paymentPlans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2123 DEFAULT CHARSET=latin1 COMMENT='Table to track equity payments.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raw_details`
--

DROP TABLE IF EXISTS `raw_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raw_details` (
  `cardNo` int(6) NOT NULL,
  `address` varchar(200) NOT NULL,
  `phone` varchar(18) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `noMail` tinyint(1) DEFAULT '0',
  `nextPayment` date DEFAULT NULL,
  `paymentPlan` int(2) NOT NULL,
  `joined` date NOT NULL,
  `sharePrice` decimal(6,2) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `userID` int(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19274 DEFAULT CHARSET=latin1 COMMENT='Details table for versioning';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raw_details`
--

LOCK TABLES `raw_details` WRITE;
/*!40000 ALTER TABLE `raw_details` DISABLE KEYS */;
INSERT INTO `raw_details` VALUES (1,'something','9999999999','something','OR','97211','jjj',0,NULL,1,'2009-12-09','180.00','2009-12-09',NULL,7,19270),(2,'123 First Ave','5031234567','Portland','OR','97211','johnDoe@gmail.com',0,NULL,1,'2009-12-09','180.00','2009-12-09',NULL,7,19271),(3,'Test Address','5419999999','Eugene','OR','97288','testing@something.com',0,NULL,1,'2009-12-09','180.00','2009-12-09',NULL,7,19272),(4,'Address','9999999999','City','OR','999999999','email@address.com',0,NULL,1,'2009-12-09','180.00','2009-12-09',NULL,7,19273);
/*!40000 ALTER TABLE `raw_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raw_owners`
--

DROP TABLE IF EXISTS `raw_owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2182 DEFAULT CHARSET=latin1 COMMENT='Raw info about owners. Versioning enabled.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raw_owners`
--

LOCK TABLES `raw_owners` WRITE;
/*!40000 ALTER TABLE `raw_owners` DISABLE KEYS */;
INSERT INTO `raw_owners` VALUES (1,1,'test','test',2,0,0,0,1,'2009-12-09',NULL,7,2174),(1,2,'test','test',2,0,0,0,1,'2009-12-09',NULL,7,2175),(2,1,'John','Doe',2,0,0,0,1,'2009-12-09',NULL,7,2176),(2,2,'Jane','Doe',2,0,0,0,1,'2009-12-09',NULL,7,2177),(3,1,'Testing','Testing',2,0,0,0,1,'2009-12-09',NULL,7,2178),(3,2,'Testing','Testing',2,0,0,0,1,'2009-12-09',NULL,7,2179),(4,1,'First','Last',2,0,0,0,1,'2009-12-09',NULL,7,2180),(4,2,'First','Last',2,0,0,0,1,'2009-12-09',NULL,7,2181);
/*!40000 ALTER TABLE `raw_owners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `level` int(2) NOT NULL,
  `userID` int(3) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('admin','21232f297a57a5a743894a0e4a801fc3',5,7,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

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
/*!50001 VIEW `details` AS select `raw_details`.`cardNo` AS `cardNo`,`raw_details`.`address` AS `address`,`raw_details`.`phone` AS `phone`,`raw_details`.`city` AS `city`,`raw_details`.`state` AS `state`,`raw_details`.`zip` AS `zip`,`raw_details`.`email` AS `email`,`raw_details`.`noMail` AS `noMail`,`raw_details`.`nextPayment` AS `nextPayment`,`raw_details`.`paymentPlan` AS `paymentPlan`,`raw_details`.`joined` AS `joined`,`raw_details`.`sharePrice` AS `sharePrice`,`raw_details`.`startDate` AS `startDate`,`raw_details`.`endDate` AS `endDate`,`raw_details`.`userID` AS `userID` from `raw_details` where isnull(`raw_details`.`endDate`) group by `raw_details`.`cardNo` */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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

-- Dump completed on 2009-12-09 23:28:18
