-- phpMyAdmin SQL Dump
-- version 3.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2009 at 11:14 PM
-- Server version: 5.1.32
-- PHP Version: 5.2.9-1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `comet`
--

-- --------------------------------------------------------

--
-- Table structure for table `actionlog`
--

CREATE TABLE IF NOT EXISTS `actionlog` (
  `actionID` int(11) NOT NULL AUTO_INCREMENT,
  `action` text NOT NULL,
  `userID` int(3) NOT NULL,
  `runtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`actionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='History of SQL actions' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `actionlog`
--


-- --------------------------------------------------------

--
-- Stand-in structure for view `details`
--
CREATE TABLE IF NOT EXISTS `details` (
`cardNo` int(6)
,`address1` varchar(50)
,`address2` int(50)
,`phone` varchar(18)
,`city` varchar(30)
,`state` varchar(3)
,`zip` varchar(10)
,`email` varchar(50)
,`nextPayment` date
,`sharePrice` decimal(6,2)
,`paymentPlan` int(2)
,`userID` int(3)
,`modified` datetime
,`joined` date
);
-- --------------------------------------------------------

--
-- Table structure for table `lists`
--

CREATE TABLE IF NOT EXISTS `lists` (
  `listID` int(3) NOT NULL AUTO_INCREMENT,
  `description` varchar(256) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `userID` int(3) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Mailing list descriptions' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lists`
--


-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `note` text NOT NULL,
  `threadID` int(11) NOT NULL,
  `parentID` int(11) DEFAULT NULL,
  `cardNo` int(6) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `userID` tinyint(3) NOT NULL,
  PRIMARY KEY (`threadID`),
  KEY `parentID` (`parentID`,`cardNo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Notes about members and responses.';

--
-- Dumping data for table `notes`
--


-- --------------------------------------------------------

--
-- Stand-in structure for view `owners`
--
CREATE TABLE IF NOT EXISTS `owners` (
`cardNo` int(6)
,`personNum` tinyint(1)
,`firstName` varchar(50)
,`lastName` varchar(50)
,`discount` tinyint(2)
,`memType` tinyint(2)
,`staff` tinyint(2)
,`chargeOk` tinyint(1)
,`writeChecks` tinyint(1)
,`userID` int(3)
,`modified` datetime
);
-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `memo` text,
  `amount` decimal(6,2) NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(12) NOT NULL,
  `userID` tinyint(3) NOT NULL,
  `cardNo` int(6) NOT NULL,
  PRIMARY KEY (`paymentID`),
  KEY `cardNo` (`cardNo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table to track equity payments.' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `payments`
--


-- --------------------------------------------------------

--
-- Table structure for table `raw_details`
--

CREATE TABLE IF NOT EXISTS `raw_details` (
  `cardNo` int(6) NOT NULL,
  `address1` varchar(50) NOT NULL,
  `address2` int(50) DEFAULT NULL,
  `phone` varchar(18) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nextPayment` date DEFAULT NULL,
  `paymentPlan` int(2) NOT NULL,
  `modified` datetime NOT NULL,
  `joined` date NOT NULL,
  `sharePrice` decimal(6,2) NOT NULL,
  `userID` int(3) NOT NULL,
  PRIMARY KEY (`cardNo`,`modified`,`userID`),
  KEY `cardNo` (`cardNo`,`zip`,`nextPayment`,`modified`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Details table for versioning';

--
-- Dumping data for table `raw_details`
--


-- --------------------------------------------------------

--
-- Table structure for table `raw_owners`
--

CREATE TABLE IF NOT EXISTS `raw_owners` (
  `cardNo` int(6) NOT NULL,
  `personNum` tinyint(1) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `discount` tinyint(2) DEFAULT NULL,
  `memType` tinyint(2) NOT NULL,
  `staff` tinyint(2) NOT NULL,
  `chargeOk` tinyint(1) NOT NULL,
  `writeChecks` tinyint(1) NOT NULL,
  `userID` int(3) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`cardNo`,`userID`,`modified`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Raw info about owners. Versioning enabled.';

--
-- Dumping data for table `raw_owners`
--


-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `cardNo` int(6) NOT NULL,
  `listID` int(3) NOT NULL,
  `userID` int(3) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`cardNo`,`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='List of subscriptions for members.';

--
-- Dumping data for table `subscriptions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `level` int(2) NOT NULL,
  `userID` int(3) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users`
--


-- --------------------------------------------------------

--
-- Structure for view `details`
--
DROP TABLE IF EXISTS `details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `details` AS select `raw_details`.`cardNo` AS `cardNo`,`raw_details`.`address1` AS `address1`,`raw_details`.`address2` AS `address2`,`raw_details`.`phone` AS `phone`,`raw_details`.`city` AS `city`,`raw_details`.`state` AS `state`,`raw_details`.`zip` AS `zip`,`raw_details`.`email` AS `email`,`raw_details`.`nextPayment` AS `nextPayment`,`raw_details`.`sharePrice` AS `sharePrice`,`raw_details`.`paymentPlan` AS `paymentPlan`,`raw_details`.`userID` AS `userID`,`raw_details`.`modified` AS `modified`,`raw_details`.`joined` AS `joined` from `raw_details` group by `raw_details`.`cardNo`,`raw_details`.`modified` having max(`raw_details`.`modified`);

-- --------------------------------------------------------

--
-- Structure for view `owners`
--
DROP TABLE IF EXISTS `owners`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `owners` AS select `raw_owners`.`cardNo` AS `cardNo`,`raw_owners`.`personNum` AS `personNum`,`raw_owners`.`firstName` AS `firstName`,`raw_owners`.`lastName` AS `lastName`,`raw_owners`.`discount` AS `discount`,`raw_owners`.`memType` AS `memType`,`raw_owners`.`staff` AS `staff`,`raw_owners`.`chargeOk` AS `chargeOk`,`raw_owners`.`writeChecks` AS `writeChecks`,`raw_owners`.`userID` AS `userID`,`raw_owners`.`modified` AS `modified` from `raw_owners` group by `raw_owners`.`cardNo`,`raw_owners`.`modified` having max(`raw_owners`.`modified`);
