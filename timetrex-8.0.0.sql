-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: timetrex
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.12.04.1

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
-- Table structure for table `absence_policy`
--

DROP TABLE IF EXISTS `absence_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absence_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0',
  `over_time` tinyint(1) NOT NULL DEFAULT '0',
  `accrual_policy_id` int(11) DEFAULT NULL,
  `premium_policy_id` int(11) DEFAULT NULL,
  `pay_stub_entry_account_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `wage_group_id` int(11) NOT NULL DEFAULT '0',
  `rate` decimal(9,4) DEFAULT NULL,
  `accrual_rate` decimal(9,4) DEFAULT NULL,
  `pay_code_id` int(11) DEFAULT '0',
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `absence_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absence_policy`
--

LOCK TABLES `absence_policy` WRITE;
/*!40000 ALTER TABLE `absence_policy` DISABLE KEYS */;
INSERT INTO `absence_policy` VALUES (2,2,'Vacation (PAID)',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,17,0,NULL),(3,2,'Vacation (UNPAID)',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,14,0,NULL),(4,2,'Sick (PAID)',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,10,0,NULL),(5,2,'Sick (UNPAID)',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,11,0,NULL),(6,2,'Jury Duty',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,15,0,NULL),(7,2,'Bereavement',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,16,0,NULL),(8,2,'Statutory Holiday',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,13,0,NULL),(9,2,'Time Bank (Withdrawal)',0,0,NULL,NULL,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,NULL,NULL,12,0,NULL);
/*!40000 ALTER TABLE `absence_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absence_policy_id_seq`
--

DROP TABLE IF EXISTS `absence_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absence_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absence_policy_id_seq`
--

LOCK TABLES `absence_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `absence_policy_id_seq` DISABLE KEYS */;
INSERT INTO `absence_policy_id_seq` VALUES (9);
/*!40000 ALTER TABLE `absence_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual`
--

DROP TABLE IF EXISTS `accrual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `accrual_policy_account_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `user_date_total_id` int(11) DEFAULT NULL,
  `time_stamp` timestamp NULL DEFAULT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `note` varchar(250) DEFAULT NULL,
  `accrual_policy_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accrual_id` (`id`),
  KEY `accrual_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual`
--

LOCK TABLES `accrual` WRITE;
/*!40000 ALTER TABLE `accrual` DISABLE KEYS */;
/*!40000 ALTER TABLE `accrual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_balance`
--

DROP TABLE IF EXISTS `accrual_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `accrual_policy_account_id` int(11) DEFAULT NULL,
  `balance` decimal(18,4) DEFAULT NULL,
  `banked_ytd` int(11) NOT NULL DEFAULT '0',
  `used_ytd` int(11) NOT NULL DEFAULT '0',
  `awarded_ytd` int(11) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accrual_balance_id` (`id`),
  KEY `accrual_balance_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_balance`
--

LOCK TABLES `accrual_balance` WRITE;
/*!40000 ALTER TABLE `accrual_balance` DISABLE KEYS */;
/*!40000 ALTER TABLE `accrual_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_balance_id_seq`
--

DROP TABLE IF EXISTS `accrual_balance_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_balance_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_balance_id_seq`
--

LOCK TABLES `accrual_balance_id_seq` WRITE;
/*!40000 ALTER TABLE `accrual_balance_id_seq` DISABLE KEYS */;
INSERT INTO `accrual_balance_id_seq` VALUES (1);
/*!40000 ALTER TABLE `accrual_balance_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_id_seq`
--

DROP TABLE IF EXISTS `accrual_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_id_seq`
--

LOCK TABLES `accrual_id_seq` WRITE;
/*!40000 ALTER TABLE `accrual_id_seq` DISABLE KEYS */;
INSERT INTO `accrual_id_seq` VALUES (1);
/*!40000 ALTER TABLE `accrual_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy`
--

DROP TABLE IF EXISTS `accrual_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `minimum_time` int(11) DEFAULT NULL,
  `maximum_time` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `apply_frequency_id` smallint(6) DEFAULT NULL,
  `apply_frequency_month` smallint(6) DEFAULT NULL,
  `apply_frequency_day_of_month` smallint(6) DEFAULT NULL,
  `apply_frequency_day_of_week` smallint(6) DEFAULT NULL,
  `milestone_rollover_hire_date` smallint(6) DEFAULT NULL,
  `milestone_rollover_month` smallint(6) DEFAULT NULL,
  `milestone_rollover_day_of_month` smallint(6) DEFAULT NULL,
  `minimum_employed_days` int(11) DEFAULT NULL,
  `minimum_employed_days_catchup` smallint(6) DEFAULT NULL,
  `enable_pay_stub_balance_display` tinyint(1) NOT NULL DEFAULT '0',
  `apply_frequency_hire_date` tinyint(1) NOT NULL DEFAULT '0',
  `contributing_shift_policy_id` int(11) DEFAULT '0',
  `length_of_service_contributing_pay_code_policy_id` int(11) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  `accrual_policy_account_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accrual_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy`
--

LOCK TABLES `accrual_policy` WRITE;
/*!40000 ALTER TABLE `accrual_policy` DISABLE KEYS */;
INSERT INTO `accrual_policy` VALUES (2,2,'Paid Time Off (PTO)',20,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10,NULL,NULL,NULL,1,NULL,NULL,0,NULL,0,0,0,0,NULL,3);
/*!40000 ALTER TABLE `accrual_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy_account`
--

DROP TABLE IF EXISTS `accrual_policy_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `enable_pay_stub_balance_display` smallint(6) DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accrual_policy_account_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy_account`
--

LOCK TABLES `accrual_policy_account` WRITE;
/*!40000 ALTER TABLE `accrual_policy_account` DISABLE KEYS */;
INSERT INTO `accrual_policy_account` VALUES (2,2,'Time Bank',NULL,1,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(3,2,'Paid Time Off (PTO)',NULL,1,1423636843,NULL,1423636843,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `accrual_policy_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy_account_id_seq`
--

DROP TABLE IF EXISTS `accrual_policy_account_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy_account_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy_account_id_seq`
--

LOCK TABLES `accrual_policy_account_id_seq` WRITE;
/*!40000 ALTER TABLE `accrual_policy_account_id_seq` DISABLE KEYS */;
INSERT INTO `accrual_policy_account_id_seq` VALUES (3);
/*!40000 ALTER TABLE `accrual_policy_account_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy_id_seq`
--

DROP TABLE IF EXISTS `accrual_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy_id_seq`
--

LOCK TABLES `accrual_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `accrual_policy_id_seq` DISABLE KEYS */;
INSERT INTO `accrual_policy_id_seq` VALUES (2);
/*!40000 ALTER TABLE `accrual_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy_milestone`
--

DROP TABLE IF EXISTS `accrual_policy_milestone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy_milestone` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accrual_policy_id` int(11) NOT NULL,
  `length_of_service` decimal(9,2) DEFAULT NULL,
  `length_of_service_unit_id` smallint(6) DEFAULT NULL,
  `length_of_service_days` decimal(9,2) DEFAULT NULL,
  `accrual_rate` decimal(18,4) DEFAULT NULL,
  `minimum_time` int(11) DEFAULT NULL,
  `maximum_time` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `rollover_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `accrual_policy_milestone_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy_milestone`
--

LOCK TABLES `accrual_policy_milestone` WRITE;
/*!40000 ALTER TABLE `accrual_policy_milestone` DISABLE KEYS */;
INSERT INTO `accrual_policy_milestone` VALUES (2,2,0.00,40,0.00,NULL,NULL,0,1423636843,NULL,1423636843,NULL,NULL,NULL,0,35996400),(3,2,5.00,40,1826.25,NULL,NULL,0,1423636843,NULL,1423636843,NULL,NULL,NULL,0,35996400);
/*!40000 ALTER TABLE `accrual_policy_milestone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accrual_policy_milestone_id_seq`
--

DROP TABLE IF EXISTS `accrual_policy_milestone_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accrual_policy_milestone_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accrual_policy_milestone_id_seq`
--

LOCK TABLES `accrual_policy_milestone_id_seq` WRITE;
/*!40000 ALTER TABLE `accrual_policy_milestone_id_seq` DISABLE KEYS */;
INSERT INTO `accrual_policy_milestone_id_seq` VALUES (3);
/*!40000 ALTER TABLE `accrual_policy_milestone_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authentication`
--

DROP TABLE IF EXISTS `authentication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authentication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(250) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `updated_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authentication_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authentication`
--

LOCK TABLES `authentication` WRITE;
/*!40000 ALTER TABLE `authentication` DISABLE KEYS */;
/*!40000 ALTER TABLE `authentication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authorizations`
--

DROP TABLE IF EXISTS `authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `authorized` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `authorizations_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authorizations`
--

LOCK TABLES `authorizations` WRITE;
/*!40000 ALTER TABLE `authorizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `authorizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authorizations_id_seq`
--

DROP TABLE IF EXISTS `authorizations_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorizations_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authorizations_id_seq`
--

LOCK TABLES `authorizations_id_seq` WRITE;
/*!40000 ALTER TABLE `authorizations_id_seq` DISABLE KEYS */;
INSERT INTO `authorizations_id_seq` VALUES (1);
/*!40000 ALTER TABLE `authorizations_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_account`
--

DROP TABLE IF EXISTS `bank_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `institution` varchar(15) NOT NULL,
  `transit` varchar(15) NOT NULL,
  `account` varchar(50) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_account_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_account`
--

LOCK TABLES `bank_account` WRITE;
/*!40000 ALTER TABLE `bank_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_account_id_seq`
--

DROP TABLE IF EXISTS `bank_account_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_account_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_account_id_seq`
--

LOCK TABLES `bank_account_id_seq` WRITE;
/*!40000 ALTER TABLE `bank_account_id_seq` DISABLE KEYS */;
INSERT INTO `bank_account_id_seq` VALUES (1);
/*!40000 ALTER TABLE `bank_account_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch`
--

DROP TABLE IF EXISTS `branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `address1` varchar(250) DEFAULT NULL,
  `address2` varchar(250) DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `postal_code` varchar(250) DEFAULT NULL,
  `work_phone` varchar(250) DEFAULT NULL,
  `fax_phone` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `manual_id` int(11) DEFAULT NULL,
  `name_metaphone` varchar(250) DEFAULT NULL,
  `longitude` decimal(15,10) DEFAULT NULL,
  `latitude` decimal(15,10) DEFAULT NULL,
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch`
--

LOCK TABLES `branch` WRITE;
/*!40000 ALTER TABLE `branch` DISABLE KEYS */;
/*!40000 ALTER TABLE `branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch_id_seq`
--

DROP TABLE IF EXISTS `branch_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branch_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch_id_seq`
--

LOCK TABLES `branch_id_seq` WRITE;
/*!40000 ALTER TABLE `branch_id_seq` DISABLE KEYS */;
INSERT INTO `branch_id_seq` VALUES (1);
/*!40000 ALTER TABLE `branch_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bread_crumb`
--

DROP TABLE IF EXISTS `bread_crumb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bread_crumb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `url` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bread_crumb_id` (`id`),
  KEY `bread_crumb_user_id_name_key` (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bread_crumb`
--

LOCK TABLES `bread_crumb` WRITE;
/*!40000 ALTER TABLE `bread_crumb` DISABLE KEYS */;
/*!40000 ALTER TABLE `bread_crumb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `break_policy`
--

DROP TABLE IF EXISTS `break_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `break_policy` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `trigger_time` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `auto_detect_type_id` int(11) NOT NULL,
  `start_window` int(11) DEFAULT NULL,
  `window_length` int(11) DEFAULT NULL,
  `minimum_punch_time` int(11) DEFAULT NULL,
  `maximum_punch_time` int(11) DEFAULT NULL,
  `include_break_punch_time` smallint(6) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `include_multiple_breaks` smallint(6) DEFAULT NULL,
  `pay_code_id` int(11) DEFAULT '0',
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `branch_id` int(11) DEFAULT '0',
  `department_id` int(11) DEFAULT '0',
  `job_id` int(11) DEFAULT '0',
  `job_item_id` int(11) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `break_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `break_policy`
--

LOCK TABLES `break_policy` WRITE;
/*!40000 ALTER TABLE `break_policy` DISABLE KEYS */;
INSERT INTO `break_policy` VALUES (2,2,'Break1',20,7200,900,20,NULL,NULL,300,1140,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL,5,0,0,0,0,0,NULL),(3,2,'Break2',20,18000,900,20,NULL,NULL,300,1140,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL,5,0,0,0,0,0,NULL);
/*!40000 ALTER TABLE `break_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `break_policy_id_seq`
--

DROP TABLE IF EXISTS `break_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `break_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `break_policy_id_seq`
--

LOCK TABLES `break_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `break_policy_id_seq` DISABLE KEYS */;
INSERT INTO `break_policy_id_seq` VALUES (3);
/*!40000 ALTER TABLE `break_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL,
  `product_edition_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `short_name` varchar(15) DEFAULT NULL,
  `address1` varchar(250) DEFAULT NULL,
  `address2` varchar(250) DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `postal_code` varchar(250) DEFAULT NULL,
  `work_phone` varchar(250) DEFAULT NULL,
  `fax_phone` varchar(250) DEFAULT NULL,
  `business_number` varchar(250) DEFAULT NULL,
  `originator_id` varchar(250) DEFAULT NULL,
  `data_center_id` varchar(250) DEFAULT NULL,
  `admin_contact` int(11) DEFAULT NULL,
  `billing_contact` int(11) DEFAULT NULL,
  `support_contact` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `enable_second_last_name` tinyint(1) NOT NULL DEFAULT '0',
  `ldap_authentication_type_id` smallint(6) DEFAULT '0',
  `ldap_host` varchar(100) DEFAULT NULL,
  `ldap_port` int(11) DEFAULT '389',
  `ldap_bind_user_name` varchar(100) DEFAULT NULL,
  `ldap_bind_password` varchar(100) DEFAULT NULL,
  `ldap_base_dn` varchar(250) DEFAULT NULL,
  `ldap_bind_attribute` varchar(100) DEFAULT NULL,
  `ldap_user_filter` varchar(250) DEFAULT NULL,
  `ldap_login_attribute` varchar(100) DEFAULT NULL,
  `ldap_group_dn` varchar(250) DEFAULT NULL,
  `ldap_group_user_attribute` varchar(100) DEFAULT NULL,
  `ldap_group_name` varchar(100) DEFAULT NULL,
  `ldap_group_attribute` varchar(250) DEFAULT NULL,
  `industry_id` int(11) DEFAULT '0',
  `password_policy_type_id` smallint(6) DEFAULT '0',
  `password_minimum_permission_level` smallint(6) DEFAULT '10',
  `password_minimum_strength` smallint(6) DEFAULT '3',
  `password_minimum_length` smallint(6) DEFAULT '8',
  `password_minimum_age` smallint(6) DEFAULT '0',
  `password_maximum_age` smallint(6) DEFAULT '365',
  `name_metaphone` varchar(250) DEFAULT NULL,
  `longitude` decimal(15,10) DEFAULT NULL,
  `latitude` decimal(15,10) DEFAULT NULL,
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  `is_setup_complete` smallint(6) NOT NULL DEFAULT '0',
  `migrate_url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES (2,0,10,10,'Test Company','','','','San Francisco','AL','US','','555 1425 548',NULL,NULL,NULL,NULL,2,2,2,1423636842,NULL,1423636865,NULL,NULL,NULL,0,0,0,NULL,389,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,10,3,8,0,365,'TSTKMPN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_deduction`
--

DROP TABLE IF EXISTS `company_deduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_deduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `calculation_id` int(11) NOT NULL,
  `calculation_order` int(11) NOT NULL DEFAULT '0',
  `country` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `district` varchar(250) DEFAULT NULL,
  `company_value1` text,
  `company_value2` text,
  `user_value1` varchar(250) DEFAULT NULL,
  `user_value2` varchar(250) DEFAULT NULL,
  `user_value3` varchar(250) DEFAULT NULL,
  `user_value4` varchar(250) DEFAULT NULL,
  `user_value5` varchar(250) DEFAULT NULL,
  `user_value6` varchar(250) DEFAULT NULL,
  `user_value7` varchar(250) DEFAULT NULL,
  `user_value8` varchar(250) DEFAULT NULL,
  `user_value9` varchar(250) DEFAULT NULL,
  `user_value10` varchar(250) DEFAULT NULL,
  `lock_user_value1` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value2` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value3` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value4` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value5` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value6` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value7` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value8` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value9` tinyint(1) NOT NULL DEFAULT '0',
  `lock_user_value10` tinyint(1) NOT NULL DEFAULT '0',
  `pay_stub_entry_account_id` int(11) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `minimum_length_of_service` decimal(11,4) DEFAULT NULL,
  `minimum_length_of_service_unit_id` smallint(6) DEFAULT NULL,
  `minimum_length_of_service_days` decimal(11,4) DEFAULT NULL,
  `maximum_length_of_service` decimal(11,4) DEFAULT NULL,
  `maximum_length_of_service_unit_id` smallint(6) DEFAULT NULL,
  `maximum_length_of_service_days` decimal(11,4) DEFAULT NULL,
  `include_account_amount_type_id` smallint(6) DEFAULT '10',
  `exclude_account_amount_type_id` smallint(6) DEFAULT '10',
  `minimum_user_age` decimal(11,4) DEFAULT NULL,
  `maximum_user_age` decimal(11,4) DEFAULT NULL,
  `company_value3` text,
  `company_value4` text,
  `company_value5` text,
  `company_value6` text,
  `company_value7` text,
  `company_value8` text,
  `company_value9` text,
  `company_value10` text,
  `apply_frequency_id` smallint(6) DEFAULT '10',
  `apply_frequency_month` smallint(6) DEFAULT NULL,
  `apply_frequency_day_of_month` smallint(6) DEFAULT NULL,
  `apply_frequency_day_of_week` smallint(6) DEFAULT NULL,
  `apply_frequency_quarter_month` smallint(6) DEFAULT NULL,
  `pay_stub_entry_description` varchar(250) DEFAULT NULL,
  `length_of_service_contributing_pay_code_policy_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_deduction_id` (`id`),
  KEY `company_deduction_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_deduction`
--

LOCK TABLES `company_deduction` WRITE;
/*!40000 ALTER TABLE `company_deduction` DISABLE KEYS */;
INSERT INTO `company_deduction` VALUES (2,2,10,20,'Loan Repayment',52,200,NULL,NULL,NULL,NULL,NULL,'25','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,32,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,30,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(3,2,10,10,'US - Federal Income Tax',100,100,'US',NULL,NULL,NULL,NULL,'0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,37,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(4,2,10,10,'US - Addl. Income Tax',20,105,NULL,NULL,NULL,NULL,NULL,'0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,37,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(5,2,10,10,'US - Federal Unemployment Insurance',15,80,NULL,NULL,NULL,NULL,NULL,'0.6','7000','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,40,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(6,2,10,10,'Social Security - Employee',84,80,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,38,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(7,2,10,10,'Social Security - Employer',85,81,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,39,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(8,2,10,10,'Medicare - Employee',82,90,NULL,NULL,NULL,NULL,NULL,'10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,41,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(9,2,10,10,'Medicare - Employer',83,91,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,42,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(10,2,10,10,'Workers Compensation - Employer',15,96,NULL,NULL,NULL,NULL,NULL,'0','0','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,45,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(11,2,10,10,'AL - State Income Tax',200,200,'US','AL',NULL,NULL,NULL,'10','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,47,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(12,2,10,10,'AL - State Addl. Income Tax',20,205,NULL,NULL,NULL,NULL,NULL,'0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,47,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(13,2,10,10,'AL - Employment Security Assessment',15,186,NULL,NULL,NULL,NULL,NULL,'0','8000','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,50,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0),(14,2,10,10,'AL - Unemployment Insurance - Employer',15,185,NULL,NULL,NULL,NULL,NULL,'0','8000','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,0,0,49,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0.0000,NULL,NULL,0.0000,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `company_deduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_deduction_id_seq`
--

DROP TABLE IF EXISTS `company_deduction_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_deduction_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_deduction_id_seq`
--

LOCK TABLES `company_deduction_id_seq` WRITE;
/*!40000 ALTER TABLE `company_deduction_id_seq` DISABLE KEYS */;
INSERT INTO `company_deduction_id_seq` VALUES (14);
/*!40000 ALTER TABLE `company_deduction_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_deduction_pay_stub_entry_account`
--

DROP TABLE IF EXISTS `company_deduction_pay_stub_entry_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_deduction_pay_stub_entry_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_deduction_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `pay_stub_entry_account_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_deduction_pay_stub_entry_account_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_deduction_pay_stub_entry_account`
--

LOCK TABLES `company_deduction_pay_stub_entry_account` WRITE;
/*!40000 ALTER TABLE `company_deduction_pay_stub_entry_account` DISABLE KEYS */;
INSERT INTO `company_deduction_pay_stub_entry_account` VALUES (2,2,10,30),(3,3,10,33),(4,3,20,31),(5,3,20,13),(6,3,20,43),(7,5,10,33),(8,5,20,31),(9,5,20,13),(10,5,20,43),(11,6,10,33),(12,6,20,31),(13,6,20,13),(14,6,20,43),(15,7,10,33),(16,7,20,31),(17,7,20,13),(18,7,20,43),(19,8,10,33),(20,8,20,31),(21,8,20,13),(22,8,20,43),(23,9,10,33),(24,9,20,31),(25,9,20,13),(26,9,20,43),(27,10,10,33),(28,10,20,31),(29,10,20,13),(30,11,10,33),(31,11,20,31),(32,11,20,13),(33,11,20,43),(34,13,10,33),(35,13,20,31),(36,13,20,13),(37,13,20,43),(38,14,10,33),(39,14,20,31),(40,14,20,13),(41,14,20,43);
/*!40000 ALTER TABLE `company_deduction_pay_stub_entry_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_deduction_pay_stub_entry_account_id_seq`
--

DROP TABLE IF EXISTS `company_deduction_pay_stub_entry_account_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_deduction_pay_stub_entry_account_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_deduction_pay_stub_entry_account_id_seq`
--

LOCK TABLES `company_deduction_pay_stub_entry_account_id_seq` WRITE;
/*!40000 ALTER TABLE `company_deduction_pay_stub_entry_account_id_seq` DISABLE KEYS */;
INSERT INTO `company_deduction_pay_stub_entry_account_id_seq` VALUES (41);
/*!40000 ALTER TABLE `company_deduction_pay_stub_entry_account_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_generic_map`
--

DROP TABLE IF EXISTS `company_generic_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_generic_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `object_type_id` int(11) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `map_id` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `company_generic_map_id` (`id`),
  KEY `company_generic_map_company_id_object_type_id_object_id` (`company_id`,`object_type_id`,`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_generic_map`
--

LOCK TABLES `company_generic_map` WRITE;
/*!40000 ALTER TABLE `company_generic_map` DISABLE KEYS */;
INSERT INTO `company_generic_map` VALUES (2,2,90,2,3),(3,2,90,3,3),(4,2,90,3,4),(5,2,90,4,3),(6,2,90,4,5),(7,2,90,5,3),(8,2,90,5,4),(9,2,90,5,5),(10,2,90,6,3),(11,2,90,6,6),(12,2,90,6,7),(13,2,90,7,3),(14,2,90,7,10),(15,2,90,7,17),(16,2,90,8,3),(17,2,90,8,4),(18,2,90,8,5),(19,2,90,8,10),(20,2,90,8,17),(21,2,90,9,3),(22,2,90,9,6),(23,2,90,9,7),(24,2,90,9,4),(25,2,90,10,3),(26,2,90,10,6),(27,2,90,10,7),(28,2,90,10,5),(29,2,90,11,3),(30,2,90,11,6),(31,2,90,11,7),(32,2,90,11,4),(33,2,90,11,5),(34,2,90,12,3),(35,2,90,12,6),(36,2,90,12,7),(37,2,90,12,10),(38,2,90,12,17),(39,2,90,13,3),(40,2,90,13,4),(41,2,90,13,5),(42,2,90,13,6),(43,2,90,13,7),(44,2,90,13,10),(45,2,90,13,17),(46,2,110,2,3),(47,2,110,2,2),(48,2,150,2,2),(49,2,150,2,3),(50,2,170,2,7),(51,2,170,2,6),(52,2,170,2,4),(53,2,170,2,5),(54,2,170,2,8),(55,2,170,2,9),(56,2,170,2,2),(57,2,170,2,3),(58,2,150,3,2),(59,2,150,3,3),(60,2,170,3,7),(61,2,170,3,6),(62,2,170,3,4),(63,2,170,3,5),(64,2,170,3,8),(65,2,170,3,9),(66,2,170,3,2),(67,2,170,3,3);
/*!40000 ALTER TABLE `company_generic_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_generic_map_id_seq`
--

DROP TABLE IF EXISTS `company_generic_map_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_generic_map_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_generic_map_id_seq`
--

LOCK TABLES `company_generic_map_id_seq` WRITE;
/*!40000 ALTER TABLE `company_generic_map_id_seq` DISABLE KEYS */;
INSERT INTO `company_generic_map_id_seq` VALUES (67);
/*!40000 ALTER TABLE `company_generic_map_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_generic_tag`
--

DROP TABLE IF EXISTS `company_generic_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_generic_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `object_type_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `name_metaphone` varchar(250) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `company_generic_tag_id` (`id`),
  UNIQUE KEY `company_generic_tag_map_id` (`id`),
  KEY `company_generic_tag_company_id` (`company_id`),
  KEY `company_generic_tag_company_id_object_type_id` (`company_id`,`object_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_generic_tag`
--

LOCK TABLES `company_generic_tag` WRITE;
/*!40000 ALTER TABLE `company_generic_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_generic_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_generic_tag_map`
--

DROP TABLE IF EXISTS `company_generic_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_generic_tag_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_type_id` int(11) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `company_generic_tag_map_id` (`id`),
  KEY `company_generic_tag_map_object_type_id_object_id` (`object_type_id`,`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_generic_tag_map`
--

LOCK TABLES `company_generic_tag_map` WRITE;
/*!40000 ALTER TABLE `company_generic_tag_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_generic_tag_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_id_seq`
--

DROP TABLE IF EXISTS `company_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_id_seq`
--

LOCK TABLES `company_id_seq` WRITE;
/*!40000 ALTER TABLE `company_id_seq` DISABLE KEYS */;
INSERT INTO `company_id_seq` VALUES (2);
/*!40000 ALTER TABLE `company_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_setting`
--

DROP TABLE IF EXISTS `company_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_setting_id` (`id`),
  KEY `company_setting_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_setting`
--

LOCK TABLES `company_setting` WRITE;
/*!40000 ALTER TABLE `company_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_user_count`
--

DROP TABLE IF EXISTS `company_user_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_user_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `active_users` int(11) NOT NULL,
  `inactive_users` int(11) NOT NULL,
  `deleted_users` int(11) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_user_count_id` (`id`),
  KEY `company_user_count_company_id_date_stamp` (`company_id`,`date_stamp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_user_count`
--

LOCK TABLES `company_user_count` WRITE;
/*!40000 ALTER TABLE `company_user_count` DISABLE KEYS */;
INSERT INTO `company_user_count` VALUES (2,2,'2015-02-11',1,0,0,1423636922);
/*!40000 ALTER TABLE `company_user_count` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_user_count_id_seq`
--

DROP TABLE IF EXISTS `company_user_count_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_user_count_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_user_count_id_seq`
--

LOCK TABLES `company_user_count_id_seq` WRITE;
/*!40000 ALTER TABLE `company_user_count_id_seq` DISABLE KEYS */;
INSERT INTO `company_user_count_id_seq` VALUES (2);
/*!40000 ALTER TABLE `company_user_count_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributing_pay_code_policy`
--

DROP TABLE IF EXISTS `contributing_pay_code_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributing_pay_code_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contributing_pay_code_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributing_pay_code_policy`
--

LOCK TABLES `contributing_pay_code_policy` WRITE;
/*!40000 ALTER TABLE `contributing_pay_code_policy` DISABLE KEYS */;
INSERT INTO `contributing_pay_code_policy` VALUES (2,2,'Regular Time',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(3,2,'Regular Time + Meal',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(4,2,'Regular Time + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(5,2,'Regular Time + Meal + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(6,2,'Regular Time + OT',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(7,2,'Regular Time + Paid Absence',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(8,2,'Regular Time + Paid Absence + Meal + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(9,2,'Regular Time + OT + Meal',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(10,2,'Regular Time + OT + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(11,2,'Regular Time + OT + Meal + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(12,2,'Regular Time + OT + Paid Absence',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(13,2,'Regular Time + OT + Paid Absence + Meal + Break',NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `contributing_pay_code_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributing_pay_code_policy_id_seq`
--

DROP TABLE IF EXISTS `contributing_pay_code_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributing_pay_code_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributing_pay_code_policy_id_seq`
--

LOCK TABLES `contributing_pay_code_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `contributing_pay_code_policy_id_seq` DISABLE KEYS */;
INSERT INTO `contributing_pay_code_policy_id_seq` VALUES (13);
/*!40000 ALTER TABLE `contributing_pay_code_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributing_shift_policy`
--

DROP TABLE IF EXISTS `contributing_shift_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributing_shift_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `contributing_pay_code_policy_id` int(11) DEFAULT NULL,
  `filter_start_date` date DEFAULT NULL,
  `filter_end_date` date DEFAULT NULL,
  `filter_start_time` time DEFAULT NULL,
  `filter_end_time` time DEFAULT NULL,
  `filter_minimum_time` int(11) DEFAULT NULL,
  `filter_maximum_time` int(11) DEFAULT NULL,
  `include_partial_shift` smallint(6) DEFAULT '0',
  `branch_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_branch` smallint(6) DEFAULT '0',
  `department_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_department` smallint(6) DEFAULT '0',
  `job_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job` smallint(6) DEFAULT '0',
  `job_item_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_item_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job_item` smallint(6) DEFAULT '0',
  `sun` smallint(6) DEFAULT '1',
  `mon` smallint(6) DEFAULT '1',
  `tue` smallint(6) DEFAULT '1',
  `wed` smallint(6) DEFAULT '1',
  `thu` smallint(6) DEFAULT '1',
  `fri` smallint(6) DEFAULT '1',
  `sat` smallint(6) DEFAULT '1',
  `include_schedule_shift_type_id` int(11) DEFAULT '0',
  `include_holiday_type_id` int(11) DEFAULT '10',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contributing_shift_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributing_shift_policy`
--

LOCK TABLES `contributing_shift_policy` WRITE;
/*!40000 ALTER TABLE `contributing_shift_policy` DISABLE KEYS */;
INSERT INTO `contributing_shift_policy` VALUES (2,2,'Regular Time',NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(3,2,'Regular Time + Break',NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(4,2,'Regular Time + Meal',NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(5,2,'Regular Time + Meal + Break',NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(6,2,'Regular Time + Paid Absence',NULL,7,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(7,2,'Regular Time + Paid Absence + Meal + Break',NULL,8,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(8,2,'Regular Time + OT',NULL,6,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(9,2,'Regular Time + OT + Meal',NULL,9,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(10,2,'Regular Time + OT + Meal + Break',NULL,11,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(11,2,'Regular Time + OT + Paid Absence',NULL,12,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(12,2,'Regular Time + OT + Paid Absence + Meal + Break',NULL,13,NULL,NULL,NULL,NULL,NULL,NULL,0,10,0,10,0,10,10,0,10,10,0,1,1,1,1,1,1,1,0,10,1423636844,NULL,1423636844,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `contributing_shift_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributing_shift_policy_id_seq`
--

DROP TABLE IF EXISTS `contributing_shift_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributing_shift_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributing_shift_policy_id_seq`
--

LOCK TABLES `contributing_shift_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `contributing_shift_policy_id_seq` DISABLE KEYS */;
INSERT INTO `contributing_shift_policy_id_seq` VALUES (12);
/*!40000 ALTER TABLE `contributing_shift_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cron`
--

DROP TABLE IF EXISTS `cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) NOT NULL DEFAULT '10',
  `name` varchar(250) NOT NULL,
  `minute` varchar(250) NOT NULL,
  `hour` varchar(250) NOT NULL,
  `day_of_month` varchar(250) NOT NULL,
  `month` varchar(250) NOT NULL,
  `day_of_week` varchar(250) NOT NULL,
  `command` varchar(250) NOT NULL,
  `last_run_date` timestamp NULL DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cron_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron`
--

LOCK TABLES `cron` WRITE;
/*!40000 ALTER TABLE `cron` DISABLE KEYS */;
INSERT INTO `cron` VALUES (1,10,'AddPayPeriod','0','0','*','*','*','AddPayPeriod.php','2015-02-11 06:43:00',1423636771,NULL,1423636921,NULL,NULL,NULL,0),(2,10,'AddUserDate','15','0','*','*','*','AddUserDate.php','2015-02-11 06:43:00',1423636771,NULL,1423636921,NULL,NULL,NULL,0),(3,10,'calcExceptions','30','0','*','*','*','calcExceptions.php','2015-02-11 06:43:00',1423636771,NULL,1423636921,NULL,NULL,NULL,0),(4,10,'AddRecurringPayStubAmendment','45','0','*','*','*','AddRecurringPayStubAmendment.php','2015-02-11 06:43:00',1423636771,NULL,1423636921,NULL,NULL,NULL,0),(5,10,'AddRecurringHoliday','55','0','*','*','*','AddRecurringHoliday.php','2015-02-11 06:43:00',1423636771,NULL,1423636921,NULL,NULL,NULL,0),(6,10,'UserCount','15','1','*','*','*','UserCount.php','2015-02-11 06:43:00',1423636771,NULL,1423636922,NULL,NULL,NULL,0),(7,10,'AddRecurringScheduleShift','20, 50','*','*','*','*','AddRecurringScheduleShift.php','2015-02-11 06:52:00',1423636771,NULL,1423637461,NULL,NULL,NULL,0),(8,10,'CheckForUpdate','35','5','*','*','*','CheckForUpdate.php','2015-02-11 06:43:00',1423636771,NULL,1423636922,NULL,NULL,NULL,0),(9,10,'AddAccrualPolicyTime','30','1','*','*','*','AddAccrualPolicyTime.php','2015-02-11 06:43:00',1423636772,NULL,1423636922,NULL,NULL,NULL,0),(10,10,'UpdateCurrencyRates','45','1','*','*','*','UpdateCurrencyRates.php','2015-02-11 06:43:00',1423636772,NULL,1423636922,NULL,NULL,NULL,0),(11,10,'TimeClockSync','*','*','*','*','*','TimeClockSync.php','2015-02-11 07:17:00',1423636774,NULL,1423638961,NULL,NULL,NULL,0),(12,10,'MiscDaily','55','1','*','*','*','MiscDaily.php','2015-02-11 06:43:00',1423636776,NULL,1423636922,NULL,NULL,NULL,0),(13,10,'MiscWeekly','55','1','*','*','0','MiscWeekly.php','2015-02-11 06:43:00',1423636776,NULL,1423636922,NULL,NULL,NULL,0),(14,10,'calcQuickExceptions','7, 22, 37, 52','*','*','*','*','calcQuickExceptions.php','2015-02-11 07:08:00',1423636777,NULL,1423638421,NULL,NULL,NULL,0),(15,10,'GeoCode','15','2','*','*','*','GeoCode.php','2015-02-11 06:43:00',1423636778,NULL,1423636922,NULL,NULL,NULL,0),(16,10,'AutoUpgrade','22','2','*','*','*','AutoUpgrade.php','2015-02-11 06:43:00',1423636781,NULL,1423636969,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cron_id_seq`
--

DROP TABLE IF EXISTS `cron_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron_id_seq`
--

LOCK TABLES `cron_id_seq` WRITE;
/*!40000 ALTER TABLE `cron_id_seq` DISABLE KEYS */;
INSERT INTO `cron_id_seq` VALUES (17);
/*!40000 ALTER TABLE `cron_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `iso_code` varchar(5) NOT NULL,
  `conversion_rate` decimal(18,10) DEFAULT NULL,
  `auto_update` smallint(6) DEFAULT NULL,
  `actual_rate` decimal(18,10) DEFAULT NULL,
  `actual_rate_updated_date` int(11) DEFAULT NULL,
  `rate_modify_percent` decimal(18,10) DEFAULT NULL,
  `is_base` smallint(6) NOT NULL DEFAULT '0',
  `is_default` smallint(6) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `round_decimal_places` smallint(6) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `currency_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency`
--

LOCK TABLES `currency` WRITE;
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
INSERT INTO `currency` VALUES (2,2,10,'USD','USD',1.0000000000,0,NULL,NULL,1.0000000000,1,1,1423636842,NULL,1423636842,NULL,NULL,NULL,0,2);
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency_id_seq`
--

DROP TABLE IF EXISTS `currency_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency_id_seq`
--

LOCK TABLES `currency_id_seq` WRITE;
/*!40000 ALTER TABLE `currency_id_seq` DISABLE KEYS */;
INSERT INTO `currency_id_seq` VALUES (2);
/*!40000 ALTER TABLE `currency_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency_rate`
--

DROP TABLE IF EXISTS `currency_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `conversion_rate` decimal(18,10) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `currency_rate_currency_id_date_stamp` (`currency_id`,`date_stamp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency_rate`
--

LOCK TABLES `currency_rate` WRITE;
/*!40000 ALTER TABLE `currency_rate` DISABLE KEYS */;
INSERT INTO `currency_rate` VALUES (2,2,'2015-02-11',1.0000000000,1423636842,NULL,1423636842,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `currency_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency_rate_id_seq`
--

DROP TABLE IF EXISTS `currency_rate_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency_rate_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency_rate_id_seq`
--

LOCK TABLES `currency_rate_id_seq` WRITE;
/*!40000 ALTER TABLE `currency_rate_id_seq` DISABLE KEYS */;
INSERT INTO `currency_rate_id_seq` VALUES (2);
/*!40000 ALTER TABLE `currency_rate_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `manual_id` int(11) DEFAULT NULL,
  `name_metaphone` varchar(250) DEFAULT NULL,
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_branch`
--

DROP TABLE IF EXISTS `department_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL DEFAULT '0',
  `department_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_branch_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_branch`
--

LOCK TABLES `department_branch` WRITE;
/*!40000 ALTER TABLE `department_branch` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_branch_id_seq`
--

DROP TABLE IF EXISTS `department_branch_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_branch_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_branch_id_seq`
--

LOCK TABLES `department_branch_id_seq` WRITE;
/*!40000 ALTER TABLE `department_branch_id_seq` DISABLE KEYS */;
INSERT INTO `department_branch_id_seq` VALUES (1);
/*!40000 ALTER TABLE `department_branch_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_branch_user`
--

DROP TABLE IF EXISTS `department_branch_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_branch_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_branch_user_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_branch_user`
--

LOCK TABLES `department_branch_user` WRITE;
/*!40000 ALTER TABLE `department_branch_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_branch_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_branch_user_id_seq`
--

DROP TABLE IF EXISTS `department_branch_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_branch_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_branch_user_id_seq`
--

LOCK TABLES `department_branch_user_id_seq` WRITE;
/*!40000 ALTER TABLE `department_branch_user_id_seq` DISABLE KEYS */;
INSERT INTO `department_branch_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `department_branch_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_id_seq`
--

DROP TABLE IF EXISTS `department_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_id_seq`
--

LOCK TABLES `department_id_seq` WRITE;
/*!40000 ALTER TABLE `department_id_seq` DISABLE KEYS */;
INSERT INTO `department_id_seq` VALUES (1);
/*!40000 ALTER TABLE `department_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ethnic_group`
--

DROP TABLE IF EXISTS `ethnic_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ethnic_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ethnic_group_id` (`id`),
  KEY `ethnic_group_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ethnic_group`
--

LOCK TABLES `ethnic_group` WRITE;
/*!40000 ALTER TABLE `ethnic_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `ethnic_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ethnic_group_id_seq`
--

DROP TABLE IF EXISTS `ethnic_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ethnic_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ethnic_group_id_seq`
--

LOCK TABLES `ethnic_group_id_seq` WRITE;
/*!40000 ALTER TABLE `ethnic_group_id_seq` DISABLE KEYS */;
INSERT INTO `ethnic_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `ethnic_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception`
--

DROP TABLE IF EXISTS `exception`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `exception_policy_id` int(11) NOT NULL,
  `punch_id` int(11) NOT NULL DEFAULT '0',
  `punch_control_id` int(11) NOT NULL DEFAULT '0',
  `type_id` smallint(6) NOT NULL,
  `enable_demerit` smallint(6) NOT NULL DEFAULT '1',
  `authorized` smallint(6) NOT NULL DEFAULT '0',
  `authorization_level` smallint(6) NOT NULL DEFAULT '99',
  `acknowledged_type_id` smallint(6) NOT NULL DEFAULT '0',
  `acknowledged_reason_id` int(11) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `note` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exception_user_id_user_date` (`user_id`,`date_stamp`),
  KEY `exception_pay_period_id` (`pay_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception`
--

LOCK TABLES `exception` WRITE;
/*!40000 ALTER TABLE `exception` DISABLE KEYS */;
/*!40000 ALTER TABLE `exception` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_id_seq`
--

DROP TABLE IF EXISTS `exception_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_id_seq`
--

LOCK TABLES `exception_id_seq` WRITE;
/*!40000 ALTER TABLE `exception_id_seq` DISABLE KEYS */;
INSERT INTO `exception_id_seq` VALUES (1);
/*!40000 ALTER TABLE `exception_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_old`
--

DROP TABLE IF EXISTS `exception_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_date_id` int(11) NOT NULL,
  `exception_policy_id` int(11) NOT NULL,
  `punch_id` int(11) DEFAULT NULL,
  `punch_control_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `enable_demerit` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `authorized` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_old`
--

LOCK TABLES `exception_old` WRITE;
/*!40000 ALTER TABLE `exception_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `exception_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_policy`
--

DROP TABLE IF EXISTS `exception_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exception_policy_control_id` int(11) NOT NULL,
  `type_id` varchar(3) NOT NULL,
  `severity_id` int(11) NOT NULL,
  `grace` int(11) DEFAULT NULL,
  `watch_window` int(11) DEFAULT NULL,
  `demerit` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `enable_authorization` tinyint(1) NOT NULL DEFAULT '0',
  `email_notification_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `exception_policy_id` (`id`),
  KEY `exception_policy_active_type_id` (`active`,`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_policy`
--

LOCK TABLES `exception_policy` WRITE;
/*!40000 ALTER TABLE `exception_policy` DISABLE KEYS */;
INSERT INTO `exception_policy` VALUES (2,2,'S1',10,0,0,25,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,100),(3,2,'S2',10,0,0,10,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,100),(4,2,'S3',10,900,7200,2,1,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,20),(5,2,'S4',25,900,7200,10,1,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,20),(6,2,'S5',20,900,7200,10,1,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,20),(7,2,'S6',10,900,7200,2,1,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,20),(8,2,'S7',10,900,0,2,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,0),(9,2,'S8',20,900,0,2,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,0),(10,2,'S9',20,900,0,5,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,100),(11,2,'SB',10,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(12,2,'O1',20,0,28800,2,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(13,2,'O2',20,0,144000,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(14,2,'M1',30,0,0,20,1,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(15,2,'M2',30,0,0,20,1,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(16,2,'M3',30,0,0,18,1,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(17,2,'M4',30,0,0,17,1,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(18,2,'L1',20,900,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,0),(19,2,'L2',20,900,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,0),(20,2,'L3',20,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(21,2,'B1',20,300,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,0),(22,2,'B2',20,300,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,0),(23,2,'B3',20,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(24,2,'B4',20,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(25,2,'B5',20,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100),(26,2,'D1',10,0,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,0),(27,2,'V1',25,172800,0,5,0,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,100);
/*!40000 ALTER TABLE `exception_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_policy_control`
--

DROP TABLE IF EXISTS `exception_policy_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_policy_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exception_policy_control_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_policy_control`
--

LOCK TABLES `exception_policy_control` WRITE;
/*!40000 ALTER TABLE `exception_policy_control` DISABLE KEYS */;
INSERT INTO `exception_policy_control` VALUES (2,2,'Default',1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `exception_policy_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_policy_control_id_seq`
--

DROP TABLE IF EXISTS `exception_policy_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_policy_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_policy_control_id_seq`
--

LOCK TABLES `exception_policy_control_id_seq` WRITE;
/*!40000 ALTER TABLE `exception_policy_control_id_seq` DISABLE KEYS */;
INSERT INTO `exception_policy_control_id_seq` VALUES (2);
/*!40000 ALTER TABLE `exception_policy_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exception_policy_id_seq`
--

DROP TABLE IF EXISTS `exception_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exception_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exception_policy_id_seq`
--

LOCK TABLES `exception_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `exception_policy_id_seq` DISABLE KEYS */;
INSERT INTO `exception_policy_id_seq` VALUES (27);
/*!40000 ALTER TABLE `exception_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `heading` varchar(250) DEFAULT NULL,
  `body` text NOT NULL,
  `keywords` varchar(250) DEFAULT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `help_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help`
--

LOCK TABLES `help` WRITE;
/*!40000 ALTER TABLE `help` DISABLE KEYS */;
/*!40000 ALTER TABLE `help` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_group`
--

DROP TABLE IF EXISTS `help_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `help_group_control_id` int(11) NOT NULL DEFAULT '0',
  `help_id` int(11) NOT NULL DEFAULT '0',
  `order_value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `help_group_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_group`
--

LOCK TABLES `help_group` WRITE;
/*!40000 ALTER TABLE `help_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `help_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_group_control`
--

DROP TABLE IF EXISTS `help_group_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_group_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `script_name` varchar(250) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `help_group_control_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_group_control`
--

LOCK TABLES `help_group_control` WRITE;
/*!40000 ALTER TABLE `help_group_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `help_group_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_group_control_id_seq`
--

DROP TABLE IF EXISTS `help_group_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_group_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_group_control_id_seq`
--

LOCK TABLES `help_group_control_id_seq` WRITE;
/*!40000 ALTER TABLE `help_group_control_id_seq` DISABLE KEYS */;
INSERT INTO `help_group_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `help_group_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_group_id_seq`
--

DROP TABLE IF EXISTS `help_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_group_id_seq`
--

LOCK TABLES `help_group_id_seq` WRITE;
/*!40000 ALTER TABLE `help_group_id_seq` DISABLE KEYS */;
INSERT INTO `help_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `help_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_id_seq`
--

DROP TABLE IF EXISTS `help_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_id_seq`
--

LOCK TABLES `help_id_seq` WRITE;
/*!40000 ALTER TABLE `help_id_seq` DISABLE KEYS */;
INSERT INTO `help_id_seq` VALUES (1);
/*!40000 ALTER TABLE `help_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_control`
--

DROP TABLE IF EXISTS `hierarchy_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hierarchy_control_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_control`
--

LOCK TABLES `hierarchy_control` WRITE;
/*!40000 ALTER TABLE `hierarchy_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_control_id_seq`
--

DROP TABLE IF EXISTS `hierarchy_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_control_id_seq`
--

LOCK TABLES `hierarchy_control_id_seq` WRITE;
/*!40000 ALTER TABLE `hierarchy_control_id_seq` DISABLE KEYS */;
INSERT INTO `hierarchy_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `hierarchy_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_level`
--

DROP TABLE IF EXISTS `hierarchy_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_level` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hierarchy_control_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `hierarchy_level_id` (`id`),
  KEY `hierarchy_level_hierarchy_control_id_user_id` (`hierarchy_control_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_level`
--

LOCK TABLES `hierarchy_level` WRITE;
/*!40000 ALTER TABLE `hierarchy_level` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_level_id_seq`
--

DROP TABLE IF EXISTS `hierarchy_level_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_level_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_level_id_seq`
--

LOCK TABLES `hierarchy_level_id_seq` WRITE;
/*!40000 ALTER TABLE `hierarchy_level_id_seq` DISABLE KEYS */;
INSERT INTO `hierarchy_level_id_seq` VALUES (1);
/*!40000 ALTER TABLE `hierarchy_level_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_object_type`
--

DROP TABLE IF EXISTS `hierarchy_object_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_object_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hierarchy_control_id` int(11) NOT NULL,
  `object_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hierarchy_object_type_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_object_type`
--

LOCK TABLES `hierarchy_object_type` WRITE;
/*!40000 ALTER TABLE `hierarchy_object_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_object_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_object_type_id_seq`
--

DROP TABLE IF EXISTS `hierarchy_object_type_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_object_type_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_object_type_id_seq`
--

LOCK TABLES `hierarchy_object_type_id_seq` WRITE;
/*!40000 ALTER TABLE `hierarchy_object_type_id_seq` DISABLE KEYS */;
INSERT INTO `hierarchy_object_type_id_seq` VALUES (1);
/*!40000 ALTER TABLE `hierarchy_object_type_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_share`
--

DROP TABLE IF EXISTS `hierarchy_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hierarchy_control_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hierarchy_share_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_share`
--

LOCK TABLES `hierarchy_share` WRITE;
/*!40000 ALTER TABLE `hierarchy_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_share_id_seq`
--

DROP TABLE IF EXISTS `hierarchy_share_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_share_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_share_id_seq`
--

LOCK TABLES `hierarchy_share_id_seq` WRITE;
/*!40000 ALTER TABLE `hierarchy_share_id_seq` DISABLE KEYS */;
INSERT INTO `hierarchy_share_id_seq` VALUES (1);
/*!40000 ALTER TABLE `hierarchy_share_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_tree`
--

DROP TABLE IF EXISTS `hierarchy_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_tree` (
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `left_id` bigint(20) NOT NULL DEFAULT '0',
  `right_id` bigint(20) NOT NULL DEFAULT '0',
  KEY `hierarchy_tree_left_id_right_id` (`left_id`,`right_id`),
  KEY `hierarchy_tree_tree_id_object_id` (`tree_id`,`object_id`),
  KEY `hierarchy_tree_tree_id_parent_id` (`tree_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_tree`
--

LOCK TABLES `hierarchy_tree` WRITE;
/*!40000 ALTER TABLE `hierarchy_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_user`
--

DROP TABLE IF EXISTS `hierarchy_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hierarchy_control_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `hierarchy_user_id` (`id`),
  KEY `hierarchy_user_hierarchy_control_id_user_id` (`hierarchy_control_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_user`
--

LOCK TABLES `hierarchy_user` WRITE;
/*!40000 ALTER TABLE `hierarchy_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarchy_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarchy_user_id_seq`
--

DROP TABLE IF EXISTS `hierarchy_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarchy_user_id_seq`
--

LOCK TABLES `hierarchy_user_id_seq` WRITE;
/*!40000 ALTER TABLE `hierarchy_user_id_seq` DISABLE KEYS */;
INSERT INTO `hierarchy_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `hierarchy_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holiday_policy`
--

DROP TABLE IF EXISTS `holiday_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `default_schedule_status_id` int(11) NOT NULL,
  `minimum_employed_days` int(11) NOT NULL,
  `minimum_worked_period_days` int(11) DEFAULT NULL,
  `minimum_worked_days` int(11) DEFAULT NULL,
  `average_time_days` int(11) DEFAULT NULL,
  `include_over_time` tinyint(1) NOT NULL DEFAULT '0',
  `include_paid_absence_time` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_time` int(11) DEFAULT NULL,
  `maximum_time` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `absence_policy_id` int(11) DEFAULT NULL,
  `round_interval_policy_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `force_over_time_policy` tinyint(1) DEFAULT '0',
  `average_time_worked_days` tinyint(1) DEFAULT '1',
  `worked_scheduled_days` smallint(6) DEFAULT '0',
  `minimum_worked_after_period_days` int(11) DEFAULT '0',
  `minimum_worked_after_days` int(11) DEFAULT '0',
  `worked_after_scheduled_days` smallint(6) DEFAULT '0',
  `paid_absence_as_worked` smallint(6) DEFAULT '0',
  `average_days` int(11) DEFAULT NULL,
  `contributing_shift_policy_id` int(11) DEFAULT '0',
  `eligible_contributing_shift_policy_id` int(11) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holiday_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday_policy`
--

LOCK TABLES `holiday_policy` WRITE;
/*!40000 ALTER TABLE `holiday_policy` DISABLE KEYS */;
INSERT INTO `holiday_policy` VALUES (2,2,'US - Statutory Holiday',10,20,30,NULL,NULL,NULL,0,0,28800,NULL,NULL,8,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,1,0,0,0,0,0,NULL,10,8,NULL);
/*!40000 ALTER TABLE `holiday_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holiday_policy_id_seq`
--

DROP TABLE IF EXISTS `holiday_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday_policy_id_seq`
--

LOCK TABLES `holiday_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `holiday_policy_id_seq` DISABLE KEYS */;
INSERT INTO `holiday_policy_id_seq` VALUES (2);
/*!40000 ALTER TABLE `holiday_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holiday_policy_recurring_holiday`
--

DROP TABLE IF EXISTS `holiday_policy_recurring_holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_policy_recurring_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_policy_id` int(11) NOT NULL DEFAULT '0',
  `recurring_holiday_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `holiday_policy_recurring_holiday_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday_policy_recurring_holiday`
--

LOCK TABLES `holiday_policy_recurring_holiday` WRITE;
/*!40000 ALTER TABLE `holiday_policy_recurring_holiday` DISABLE KEYS */;
INSERT INTO `holiday_policy_recurring_holiday` VALUES (2,2,8),(3,2,11),(4,2,4),(5,2,2),(6,2,6),(7,2,12),(8,2,5),(9,2,9),(10,2,10),(11,2,7),(12,2,3);
/*!40000 ALTER TABLE `holiday_policy_recurring_holiday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holiday_policy_recurring_holiday_id_seq`
--

DROP TABLE IF EXISTS `holiday_policy_recurring_holiday_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_policy_recurring_holiday_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday_policy_recurring_holiday_id_seq`
--

LOCK TABLES `holiday_policy_recurring_holiday_id_seq` WRITE;
/*!40000 ALTER TABLE `holiday_policy_recurring_holiday_id_seq` DISABLE KEYS */;
INSERT INTO `holiday_policy_recurring_holiday_id_seq` VALUES (12);
/*!40000 ALTER TABLE `holiday_policy_recurring_holiday_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_policy_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `name` varchar(250) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `holidays_id` (`id`),
  KEY `holidays_holiday_policy_id` (`holiday_policy_id`),
  KEY `holidays_date_stamp` (`date_stamp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (2,2,'2015-02-16','US - Presidents Day',1423636921,NULL,1423636921,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays_id_seq`
--

DROP TABLE IF EXISTS `holidays_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays_id_seq`
--

LOCK TABLES `holidays_id_seq` WRITE;
/*!40000 ALTER TABLE `holidays_id_seq` DISABLE KEYS */;
INSERT INTO `holidays_id_seq` VALUES (2);
/*!40000 ALTER TABLE `holidays_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_tax_rate`
--

DROP TABLE IF EXISTS `income_tax_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_tax_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `effective_date` int(11) NOT NULL,
  `income` decimal(10,4) NOT NULL,
  `rate` decimal(10,4) NOT NULL,
  `constant` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `income_tax_rate_id_uniq` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=475 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_tax_rate`
--

LOCK TABLES `income_tax_rate` WRITE;
/*!40000 ALTER TABLE `income_tax_rate` DISABLE KEYS */;
INSERT INTO `income_tax_rate` VALUES (1,'CA',NULL,1072944000,35000.0000,16.0000,0.0000),(2,'CA',NULL,1072944000,70000.0000,22.0000,2100.0000),(3,'CA',NULL,1072944000,113804.0000,26.0000,4900.0000),(4,'CA',NULL,1072944000,113804.0000,29.0000,8314.0000),(5,'CA','BC',1072944000,32476.0000,6.0500,0.0000),(6,'CA','BC',1072944000,64954.0000,9.1500,1007.0000),(7,'CA','BC',1072944000,74575.0000,11.7000,2663.0000),(8,'CA','BC',1072944000,90555.0000,13.7000,4155.0000),(9,'CA','BC',1072944000,90555.0000,14.7000,5060.0000),(10,'CA','BC',978336000,32476.0000,6.0500,0.0000),(11,'CA','BC',978336000,64954.0000,9.1500,1007.0000),(12,'CA','BC',978336000,74575.0000,11.7000,2663.0000),(13,'CA','BC',978336000,90555.0000,13.7000,4155.0000),(14,'CA','BC',978336000,90555.0000,14.7000,5060.0000),(15,'CA',NULL,978336000,35000.0000,16.0000,0.0000),(16,'CA',NULL,978336000,70000.0000,22.0000,2100.0000),(17,'CA',NULL,978336000,113804.0000,26.0000,4900.0000),(18,'CA',NULL,978336000,113804.0000,29.0000,8314.0000),(19,'CA','BC',1009872000,32476.0000,6.0500,0.0000),(20,'CA','BC',1009872000,64954.0000,9.1500,1007.0000),(21,'CA','BC',1009872000,74575.0000,11.7000,2663.0000),(22,'CA','BC',1009872000,90555.0000,13.7000,4155.0000),(23,'CA','BC',1009872000,90555.0000,14.7000,5060.0000),(24,'CA',NULL,1009872000,35000.0000,16.0000,0.0000),(25,'CA',NULL,1009872000,70000.0000,22.0000,2100.0000),(26,'CA',NULL,1009872000,113804.0000,26.0000,4900.0000),(27,'CA',NULL,1009872000,113804.0000,29.0000,8314.0000),(28,'CA','BC',1041408000,32476.0000,6.0500,0.0000),(29,'CA','BC',1041408000,64954.0000,9.1500,1007.0000),(30,'CA','BC',1041408000,74575.0000,11.7000,2663.0000),(31,'CA','BC',1041408000,90555.0000,13.7000,4155.0000),(32,'CA','BC',1041408000,90555.0000,14.7000,5060.0000),(33,'CA',NULL,1041408000,35000.0000,16.0000,0.0000),(34,'CA',NULL,1041408000,70000.0000,22.0000,2100.0000),(35,'CA',NULL,1041408000,113804.0000,26.0000,4900.0000),(36,'CA',NULL,1041408000,113804.0000,29.0000,8314.0000),(37,'CA','AB',1072944000,0.0000,10.0000,0.0000),(48,'CA',NULL,1104566400,35595.0000,16.0000,0.0000),(49,'CA',NULL,1104566400,71190.0000,22.0000,2136.0000),(50,'CA',NULL,1104566400,115739.0000,26.0000,4983.0000),(51,'CA',NULL,1104566400,115739.0000,29.0000,8455.0000),(52,'CA','AB',1104566400,0.0000,10.0000,0.0000),(53,'CA','BC',1104566400,33061.0000,6.0500,0.0000),(54,'CA','BC',1104566400,66123.0000,9.1500,1025.0000),(55,'CA','BC',1104566400,75917.0000,11.7000,2711.0000),(56,'CA','BC',1104566400,92185.0000,13.7000,4229.0000),(57,'CA','BC',1104566400,92185.0000,14.7000,5151.0000),(58,'CA','BC',1120201200,33061.0000,6.0500,0.0000),(59,'CA','BC',1120201200,66123.0000,9.1500,1007.0000),(60,'CA','BC',1120201200,75917.0000,11.7000,2663.0000),(61,'CA','BC',1120201200,92185.0000,13.7000,4155.0000),(62,'CA','BC',1120201200,92185.0000,14.7000,5151.0000),(63,'CA',NULL,1136102400,36378.0000,15.0000,0.0000),(64,'CA',NULL,1136102400,72756.0000,22.0000,2546.0000),(65,'CA',NULL,1136102400,118285.0000,26.0000,5457.0000),(66,'CA',NULL,1136102400,118285.0000,29.0000,9005.0000),(67,'CA','ON',1136102400,34758.0000,6.0500,0.0000),(68,'CA','ON',1136102400,69517.0000,9.1500,1077.0000),(69,'CA','ON',1136102400,69517.0000,11.1600,2475.0000),(70,'CA','SK',1136102400,37579.0000,11.0000,0.0000),(71,'CA','SK',1136102400,107367.0000,13.0000,752.0000),(72,'CA','SK',1136102400,107367.0000,15.0000,2899.0000),(73,'CA','BC',1136102400,33755.0000,6.0500,0.0000),(74,'CA','BC',1136102400,67511.0000,9.1500,1046.0000),(75,'CA','BC',1136102400,77511.0000,11.7000,2768.0000),(76,'CA','BC',1136102400,94121.0000,13.7000,4318.0000),(77,'CA','BC',1136102400,94121.0000,14.7000,5259.0000),(78,'CA','MB',1136102400,30544.0000,10.9000,0.0000),(79,'CA','MB',1136102400,65000.0000,13.5000,794.0000),(80,'CA','MB',1136102400,65000.0000,17.4000,3329.0000),(81,'CA',NULL,1151737200,36378.0000,15.5000,0.0000),(82,'CA',NULL,1151737200,72756.0000,22.0000,2365.0000),(83,'CA',NULL,1151737200,118285.0000,26.0000,5275.0000),(84,'CA',NULL,1151737200,118285.0000,29.0000,8823.0000),(85,'CA',NULL,1167638400,37178.0000,15.5000,0.0000),(86,'CA',NULL,1167638400,74357.0000,22.0000,2417.0000),(87,'CA',NULL,1167638400,120887.0000,26.0000,5391.0000),(88,'CA',NULL,1167638400,120887.0000,29.0000,9017.0000),(89,'CA','NL',1167638400,29590.0000,10.5700,0.0000),(90,'CA','NL',1167638400,59180.0000,16.1600,1654.0000),(91,'CA','NL',1167638400,59180.0000,18.0200,2755.0000),(92,'CA','NS',1167638400,29590.0000,8.7900,0.0000),(93,'CA','NS',1167638400,59180.0000,14.9500,1823.0000),(94,'CA','NS',1167638400,93000.0000,16.6700,2841.0000),(95,'CA','NS',1167638400,93000.0000,17.5000,3613.0000),(96,'CA','PE',1167638400,30754.0000,9.8000,0.0000),(97,'CA','PE',1167638400,61509.0000,13.8000,1230.0000),(98,'CA','PE',1167638400,61509.0000,16.7000,3014.0000),(99,'CA','NB',1167638400,34186.0000,9.6800,0.0000),(100,'CA','NB',1167638400,68374.0000,14.8200,1757.0000),(101,'CA','NB',1167638400,111161.0000,16.5200,2920.0000),(102,'CA','NB',1167638400,111161.0000,17.8400,4387.0000),(103,'CA','ON',1167638400,35488.0000,6.0500,0.0000),(104,'CA','ON',1167638400,70976.0000,9.1500,1100.0000),(105,'CA','ON',1167638400,70976.0000,11.1600,2527.0000),(106,'CA','MB',1167638400,30544.0000,10.9000,0.0000),(107,'CA','MB',1167638400,65000.0000,13.0000,641.0000),(108,'CA','MB',1167638400,65000.0000,17.4000,3501.0000),(109,'CA','SK',1167638400,38405.0000,11.0000,0.0000),(110,'CA','SK',1167638400,109720.0000,13.0000,768.0000),(111,'CA','SK',1167638400,109720.0000,15.0000,2963.0000),(112,'CA','BC',1167638400,34397.0000,6.0500,0.0000),(113,'CA','BC',1167638400,68794.0000,9.1500,1066.0000),(114,'CA','BC',1167638400,78984.0000,11.7000,2821.0000),(115,'CA','BC',1167638400,95909.0000,13.7000,4400.0000),(116,'CA','BC',1167638400,95909.0000,14.7000,5359.0000),(117,'CA','YU',1167638400,37178.0000,7.0400,0.0000),(118,'CA','YU',1167638400,74357.0000,9.6800,981.0000),(119,'CA','YU',1167638400,120887.0000,11.4400,2290.0000),(120,'CA','YU',1167638400,120887.0000,12.7600,3886.0000),(121,'CA','NT',1167638400,35315.0000,5.9000,0.0000),(122,'CA','NT',1167638400,70631.0000,8.6000,954.0000),(123,'CA','NT',1167638400,114830.0000,12.2000,3496.0000),(124,'CA','NT',1167638400,114830.0000,14.0500,5621.0000),(125,'CA','NU',1167638400,37178.0000,4.0000,0.0000),(126,'CA','NU',1167638400,74357.0000,7.0000,1115.0000),(127,'CA','NU',1167638400,120887.0000,9.0000,2602.0000),(128,'CA','NU',1167638400,120887.0000,11.5000,5625.0000),(129,'CA','BC',1183273200,34397.0000,5.3500,0.0000),(130,'CA','BC',1183273200,68794.0000,8.1500,963.0000),(131,'CA','BC',1183273200,78984.0000,10.5000,2580.0000),(132,'CA','BC',1183273200,95909.0000,12.3000,4001.0000),(133,'CA','BC',1183273200,95909.0000,14.7000,6303.0000),(134,'CA','NL',1183273200,30182.0000,8.7000,0.0000),(135,'CA','NL',1183273200,60364.0000,13.8000,1539.0000),(136,'CA','NL',1183273200,60364.0000,16.5000,3169.0000),(137,'CA','PE',1183273200,31984.0000,9.8000,0.0000),(138,'CA','PE',1183273200,63969.0000,13.8000,1279.0000),(139,'CA','PE',1183273200,63969.0000,16.7000,3134.0000),(140,'CA','NB',1183273200,34186.0000,10.5600,0.0000),(141,'CA','NB',1183273200,68374.0000,16.1400,1908.0000),(142,'CA','NB',1183273200,111161.0000,17.0800,2550.0000),(143,'CA','NB',1183273200,111161.0000,18.0600,3640.0000),(144,'CA',NULL,1199174400,37885.0000,15.0000,0.0000),(145,'CA',NULL,1199174400,75769.0000,22.0000,2652.0000),(146,'CA',NULL,1199174400,123184.0000,26.0000,5683.0000),(147,'CA',NULL,1199174400,123184.0000,29.0000,9378.0000),(148,'CA','NL',1199174400,30215.0000,8.7000,0.0000),(149,'CA','NL',1199174400,60429.0000,13.8000,1541.0000),(150,'CA','NL',1199174400,60429.0000,16.5000,3173.0000),(151,'CA','NB',1199174400,34836.0000,10.1200,0.0000),(152,'CA','NB',1199174400,69673.0000,15.4800,1867.0000),(153,'CA','NB',1199174400,113273.0000,16.8000,2787.0000),(154,'CA','NB',1199174400,113273.0000,17.9500,4090.0000),(155,'CA','ON',1199174400,36020.0000,6.0500,0.0000),(156,'CA','ON',1199174400,72041.0000,9.1500,1117.0000),(157,'CA','ON',1199174400,72041.0000,11.1600,2565.0000),(158,'CA','MB',1199174400,30544.0000,10.9000,0.0000),(159,'CA','MB',1199174400,66000.0000,12.7500,565.0000),(160,'CA','MB',1199174400,66000.0000,17.4000,3634.0000),(161,'CA','SK',1199174400,39135.0000,11.0000,0.0000),(162,'CA','SK',1199174400,111814.0000,13.0000,783.0000),(163,'CA','SK',1199174400,111814.0000,15.0000,3019.0000),(164,'CA','BC',1199174400,35016.0000,5.3500,0.0000),(165,'CA','BC',1199174400,70033.0000,8.1500,980.0000),(166,'CA','BC',1199174400,80406.0000,10.5000,2626.0000),(167,'CA','BC',1199174400,97636.0000,12.2900,4065.0000),(168,'CA','BC',1199174400,97636.0000,14.7000,6419.0000),(169,'CA','YU',1199174400,37885.0000,7.0400,0.0000),(170,'CA','YU',1199174400,75769.0000,9.6800,1000.0000),(171,'CA','YU',1199174400,123184.0000,11.4400,2334.0000),(172,'CA','YU',1199174400,123184.0000,12.7600,3960.0000),(173,'CA','NT',1199174400,35986.0000,5.9000,0.0000),(174,'CA','NT',1199174400,71973.0000,8.6000,972.0000),(175,'CA','NT',1199174400,117011.0000,12.2000,3563.0000),(176,'CA','NT',1199174400,117011.0000,14.0500,5727.0000),(177,'CA','NU',1199174400,37885.0000,4.0000,0.0000),(178,'CA','NU',1199174400,75770.0000,7.0000,1137.0000),(179,'CA','NU',1199174400,123184.0000,9.0000,2652.0000),(180,'CA','NU',1199174400,123184.0000,11.5000,5732.0000),(181,'CA','NL',1214895600,30215.0000,7.7000,0.0000),(182,'CA','NL',1214895600,60429.0000,12.8000,1541.0000),(183,'CA','NL',1214895600,60429.0000,15.5000,3173.0000),(184,'CA','BC',1214895600,35016.0000,5.1300,0.0000),(185,'CA','BC',1214895600,70033.0000,7.8100,938.0000),(186,'CA','BC',1214895600,80406.0000,10.5000,2822.0000),(187,'CA','BC',1214895600,97636.0000,12.2900,4262.0000),(188,'CA','BC',1214895600,97636.0000,14.7000,6615.0000),(189,'CA',NULL,1230796800,38832.0000,15.0000,0.0000),(190,'CA',NULL,1230796800,77664.0000,22.0000,2718.0000),(191,'CA',NULL,1230796800,126264.0000,26.0000,5825.0000),(192,'CA',NULL,1230796800,126264.0000,29.0000,9613.0000),(193,'CA','BC',1230796800,35716.0000,5.0600,0.0000),(194,'CA','BC',1230796800,71433.0000,7.7000,943.0000),(195,'CA','BC',1230796800,82014.0000,10.5000,2943.0000),(196,'CA','BC',1230796800,99588.0000,12.2900,4411.0000),(197,'CA','BC',1230796800,99588.0000,14.7000,6811.0000),(198,'CA','MB',1230796800,31000.0000,10.8000,0.0000),(199,'CA','MB',1230796800,67000.0000,12.7500,605.0000),(200,'CA','MB',1230796800,67000.0000,17.4000,3720.0000),(201,'CA','NB',1230796800,35707.0000,10.1200,0.0000),(202,'CA','NB',1230796800,71415.0000,15.4800,1914.0000),(203,'CA','NB',1230796800,116105.0000,16.8000,2857.0000),(204,'CA','NB',1230796800,116105.0000,17.9500,4192.0000),(205,'CA','NL',1230796800,31061.0000,7.7000,0.0000),(206,'CA','NL',1230796800,62121.0000,12.8000,1584.0000),(207,'CA','NL',1230796800,62121.0000,15.5000,3261.0000),(208,'CA','NT',1230796800,36885.0000,5.9000,0.0000),(209,'CA','NT',1230796800,73772.0000,8.6000,996.0000),(210,'CA','NT',1230796800,119936.0000,12.2000,3652.0000),(211,'CA','NT',1230796800,119936.0000,14.0500,5871.0000),(212,'CA','NU',1230796800,38832.0000,4.0000,0.0000),(213,'CA','NU',1230796800,77664.0000,7.0000,1165.0000),(214,'CA','NU',1230796800,126264.0000,9.0000,2718.0000),(215,'CA','NU',1230796800,126264.0000,11.5000,5875.0000),(216,'CA','ON',1230796800,36848.0000,6.0500,0.0000),(217,'CA','ON',1230796800,73698.0000,9.1500,1142.0000),(218,'CA','ON',1230796800,73698.0000,11.1600,2624.0000),(219,'CA','SK',1230796800,40113.0000,11.0000,0.0000),(220,'CA','SK',1230796800,114610.0000,13.0000,802.0000),(221,'CA','SK',1230796800,114610.0000,15.0000,3094.0000),(222,'CA','YU',1230796800,38832.0000,7.0400,0.0000),(223,'CA','YU',1230796800,77664.0000,9.6800,1025.0000),(224,'CA','YU',1230796800,126264.0000,11.4400,2392.0000),(225,'CA','YU',1230796800,126264.0000,12.7600,4059.0000),(226,'CA',NULL,1238569200,41200.0000,15.0000,0.0000),(227,'CA',NULL,1238569200,82399.0000,22.0000,2884.0000),(228,'CA',NULL,1238569200,126264.0000,26.0000,6180.0000),(229,'CA',NULL,1238569200,126264.0000,29.0000,9968.0000),(230,'CA','YU',1238569200,41200.0000,7.0400,0.0000),(231,'CA','YU',1238569200,82399.0000,9.6800,1088.0000),(232,'CA','YU',1238569200,126264.0000,11.4400,2538.0000),(233,'CA','YU',1238569200,126264.0000,12.7600,4205.0000),(234,'CA','NB',1246431600,35707.0000,9.1800,0.0000),(235,'CA','NB',1246431600,71415.0000,13.5300,1550.0000),(236,'CA','NB',1246431600,116105.0000,15.2000,2749.0000),(237,'CA','NB',1246431600,116105.0000,16.0500,3736.0000),(238,'CA',NULL,1262332800,40970.0000,15.0000,0.0000),(239,'CA',NULL,1262332800,81941.0000,22.0000,2868.0000),(240,'CA',NULL,1262332800,127021.0000,26.0000,6146.0000),(241,'CA',NULL,1262332800,127021.0000,29.0000,9956.0000),(242,'CA','BC',1262332800,35859.0000,5.0600,0.0000),(243,'CA','BC',1262332800,71719.0000,7.7000,947.0000),(244,'CA','BC',1262332800,82342.0000,10.5000,2955.0000),(245,'CA','BC',1262332800,99987.0000,12.2900,4429.0000),(246,'CA','BC',1262332800,99987.0000,14.7000,6838.0000),(247,'CA','NB',1262332800,36421.0000,9.3000,0.0000),(248,'CA','NB',1262332800,72843.0000,12.5000,1165.0000),(249,'CA','NB',1262332800,118427.0000,13.3000,1748.0000),(250,'CA','NB',1262332800,118427.0000,14.3000,2932.0000),(251,'CA','NL',1262332800,31278.0000,7.7000,0.0000),(252,'CA','NL',1262332800,62556.0000,12.8000,1595.0000),(253,'CA','NL',1262332800,62556.0000,15.5000,3284.0000),(254,'CA','NT',1262332800,37106.0000,5.9000,0.0000),(255,'CA','NT',1262332800,74214.0000,8.6000,1002.0000),(256,'CA','NT',1262332800,120656.0000,12.2000,3674.0000),(257,'CA','NT',1262332800,120656.0000,14.0500,5906.0000),(258,'CA','NU',1262332800,39065.0000,4.0000,0.0000),(259,'CA','NU',1262332800,78130.0000,7.0000,1172.0000),(260,'CA','NU',1262332800,127021.0000,9.0000,2735.0000),(261,'CA','NU',1262332800,127021.0000,11.5000,5910.0000),(262,'CA','ON',1262332800,37106.0000,5.0500,0.0000),(263,'CA','ON',1262332800,74214.0000,9.1500,1521.0000),(264,'CA','ON',1262332800,74214.0000,11.1600,3013.0000),(265,'CA','SK',1262332800,40354.0000,11.0000,0.0000),(266,'CA','SK',1262332800,115297.0000,13.0000,807.0000),(267,'CA','SK',1262332800,115297.0000,15.0000,3113.0000),(268,'CA','YT',1262332800,40970.0000,7.0400,0.0000),(269,'CA','YT',1262332800,81941.0000,9.6800,1082.0000),(270,'CA','YT',1262332800,127021.0000,11.4400,2524.0000),(271,'CA','YT',1262332800,127021.0000,12.7600,4200.0000),(272,'CA','NL',1277967600,31278.0000,7.7000,0.0000),(273,'CA','NL',1277967600,62556.0000,12.5000,1501.0000),(274,'CA','NL',1277967600,62556.0000,13.3000,2002.0000),(275,'CA','NS',1277967600,29590.0000,8.7900,0.0000),(276,'CA','NS',1277967600,59180.0000,14.9500,1823.0000),(277,'CA','NS',1277967600,93000.0000,16.6700,2841.0000),(278,'CA','NS',1277967600,150000.0000,17.5000,3613.0000),(279,'CA','NS',1277967600,150000.0000,24.5000,14113.0000),(280,'CA',NULL,1293868800,41544.0000,15.0000,0.0000),(281,'CA',NULL,1293868800,83088.0000,22.0000,2908.0000),(282,'CA',NULL,1293868800,128800.0000,26.0000,6232.0000),(283,'CA',NULL,1293868800,128800.0000,29.0000,10096.0000),(284,'CA','BC',1293868800,36146.0000,5.0600,0.0000),(285,'CA','BC',1293868800,72293.0000,7.7000,954.0000),(286,'CA','BC',1293868800,83001.0000,10.5000,2978.0000),(287,'CA','BC',1293868800,100787.0000,12.2900,4464.0000),(288,'CA','BC',1293868800,100787.0000,14.7000,6893.0000),(289,'CA','NB',1293868800,37150.0000,9.1000,0.0000),(290,'CA','NB',1293868800,74300.0000,12.1000,1115.0000),(291,'CA','NB',1293868800,120796.0000,12.4000,1337.0000),(292,'CA','NB',1293868800,120796.0000,12.7000,1700.0000),(293,'CA','NL',1293868800,31904.0000,7.7000,0.0000),(294,'CA','NL',1293868800,63807.0000,12.5000,1531.0000),(295,'CA','NL',1293868800,63807.0000,13.3000,2042.0000),(296,'CA','NT',1293868800,37626.0000,5.9000,0.0000),(297,'CA','NT',1293868800,75253.0000,8.6000,1016.0000),(298,'CA','NT',1293868800,122345.0000,12.2000,3725.0000),(299,'CA','NT',1293868800,122345.0000,14.0500,5988.0000),(300,'CA','NS',1293868800,29590.0000,8.7900,0.0000),(301,'CA','NS',1293868800,59180.0000,14.9500,1823.0000),(302,'CA','NS',1293868800,93000.0000,16.6700,2841.0000),(303,'CA','NS',1293868800,150000.0000,17.5000,3613.0000),(304,'CA','NS',1293868800,150000.0000,21.0000,8863.0000),(305,'CA','NU',1293868800,39612.0000,4.0000,0.0000),(306,'CA','NU',1293868800,79224.0000,7.0000,1188.0000),(307,'CA','NU',1293868800,128800.0000,9.0000,2773.0000),(308,'CA','NU',1293868800,128800.0000,11.5000,5993.0000),(309,'CA','ON',1293868800,37774.0000,5.0500,0.0000),(310,'CA','ON',1293868800,75550.0000,9.1500,1549.0000),(311,'CA','ON',1293868800,75550.0000,11.1600,3067.0000),(312,'CA','SK',1293868800,40919.0000,11.0000,0.0000),(313,'CA','SK',1293868800,116911.0000,13.0000,818.0000),(314,'CA','SK',1293868800,116911.0000,15.0000,3157.0000),(315,'CA','YT',1293868800,41544.0000,7.0400,0.0000),(316,'CA','YT',1293868800,83088.0000,9.6800,1097.0000),(317,'CA','YT',1293868800,128800.0000,11.4400,2559.0000),(318,'CA','YT',1293868800,128800.0000,12.7600,4259.0000),(319,'CA','NB',1309503600,37150.0000,9.1000,0.0000),(320,'CA','NB',1309503600,74300.0000,12.1000,1115.0000),(321,'CA','NB',1309503600,120796.0000,12.4000,1337.0000),(322,'CA','NB',1309503600,120796.0000,15.9000,1700.0000),(323,'CA',NULL,1325404800,42707.0000,15.0000,0.0000),(324,'CA',NULL,1325404800,85414.0000,22.0000,2989.0000),(325,'CA',NULL,1325404800,132406.0000,26.0000,6406.0000),(326,'CA',NULL,1325404800,132406.0000,29.0000,10378.0000),(327,'CA','BC',1325404800,37013.0000,5.0600,0.0000),(328,'CA','BC',1325404800,74028.0000,7.7000,977.0000),(329,'CA','BC',1325404800,84993.0000,10.5000,3050.0000),(330,'CA','BC',1325404800,103205.0000,12.2900,4571.0000),(331,'CA','BC',1325404800,103205.0000,14.7000,7059.0000),(332,'CA','NB',1325404800,38190.0000,9.1000,0.0000),(333,'CA','NB',1325404800,76380.0000,12.1000,1146.0000),(334,'CA','NB',1325404800,124178.0000,12.4000,1375.0000),(335,'CA','NB',1325404800,124178.0000,14.3000,3734.0000),(336,'CA','NL',1325404800,32893.0000,7.7000,0.0000),(337,'CA','NL',1325404800,65785.0000,12.5000,1579.0000),(338,'CA','NL',1325404800,65785.0000,13.3000,2105.0000),(339,'CA','NT',1325404800,38679.0000,5.9000,0.0000),(340,'CA','NT',1325404800,77360.0000,8.6000,1044.0000),(341,'CA','NT',1325404800,125771.0000,12.2000,3829.0000),(342,'CA','NT',1325404800,125771.0000,14.0500,6156.0000),(343,'CA','NU',1325404800,40721.0000,4.0000,0.0000),(344,'CA','NU',1325404800,81442.0000,7.0000,1222.0000),(345,'CA','NU',1325404800,132406.0000,9.0000,2850.0000),(346,'CA','NU',1325404800,132406.0000,11.5000,6161.0000),(347,'CA','ON',1325404800,39020.0000,5.0500,0.0000),(348,'CA','ON',1325404800,78043.0000,9.1500,1600.0000),(349,'CA','ON',1325404800,78043.0000,11.1600,3168.0000),(350,'CA','SK',1325404800,42065.0000,11.0000,0.0000),(351,'CA','SK',1325404800,120185.0000,13.0000,841.0000),(352,'CA','SK',1325404800,120185.0000,15.0000,3245.0000),(353,'CA','YT',1325404800,42707.0000,7.0400,0.0000),(354,'CA','YT',1325404800,85414.0000,9.6800,1127.0000),(355,'CA','YT',1325404800,132406.0000,11.4400,2631.0000),(356,'CA','YT',1325404800,132406.0000,12.7600,4379.0000),(357,'CA',NULL,1357027200,43561.0000,15.0000,0.0000),(358,'CA',NULL,1357027200,87123.0000,22.0000,3049.0000),(359,'CA',NULL,1357027200,135054.0000,26.0000,6534.0000),(360,'CA',NULL,1357027200,135054.0000,29.0000,10586.0000),(361,'CA','BC',1357027200,37568.0000,5.0600,0.0000),(362,'CA','BC',1357027200,75138.0000,7.7000,992.0000),(363,'CA','BC',1357027200,86268.0000,10.5000,3096.0000),(364,'CA','BC',1357027200,104754.0000,12.2900,4640.0000),(365,'CA','BC',1357027200,104754.0000,14.7000,7164.0000),(366,'CA','NB',1357027200,38954.0000,9.1000,0.0000),(367,'CA','NB',1357027200,77908.0000,12.1000,1169.0000),(368,'CA','NB',1357027200,126662.0000,12.4000,1402.0000),(369,'CA','NB',1357027200,126662.0000,14.3000,3809.0000),(370,'CA','NL',1357027200,33748.0000,7.7000,0.0000),(371,'CA','NL',1357027200,67496.0000,12.5000,1620.0000),(372,'CA','NL',1357027200,67496.0000,13.3000,2160.0000),(373,'CA','NT',1357027200,39453.0000,5.9000,0.0000),(374,'CA','NT',1357027200,78908.0000,8.6000,1065.0000),(375,'CA','NT',1357027200,128286.0000,12.2000,3906.0000),(376,'CA','NT',1357027200,128286.0000,14.0500,6279.0000),(377,'CA','NU',1357027200,41535.0000,4.0000,0.0000),(378,'CA','NU',1357027200,83071.0000,7.0000,1246.0000),(379,'CA','NU',1357027200,135054.0000,9.0000,2907.0000),(380,'CA','NU',1357027200,135054.0000,11.5000,6284.0000),(381,'CA','ON',1357027200,39723.0000,5.0500,0.0000),(382,'CA','ON',1357027200,79448.0000,9.1500,1629.0000),(383,'CA','ON',1357027200,509000.0000,11.1600,3226.0000),(384,'CA','ON',1357027200,509000.0000,13.1600,13406.0000),(385,'CA','SK',1357027200,42906.0000,11.0000,0.0000),(386,'CA','SK',1357027200,122589.0000,13.0000,858.0000),(387,'CA','SK',1357027200,122589.0000,15.0000,3310.0000),(388,'CA','YT',1357027200,43561.0000,7.0400,0.0000),(389,'CA','YT',1357027200,87123.0000,9.6800,1150.0000),(390,'CA','YT',1357027200,135054.0000,11.4400,2683.0000),(391,'CA','YT',1357027200,135054.0000,12.7600,4466.0000),(392,'CA','NB',1372662000,38954.0000,9.6800,0.0000),(393,'CA','NB',1372662000,77908.0000,14.8200,2002.0000),(394,'CA','NB',1372662000,126662.0000,16.5200,3327.0000),(395,'CA','NB',1372662000,126662.0000,17.8400,4999.0000),(396,'CA',NULL,1388563200,43953.0000,15.0000,0.0000),(397,'CA',NULL,1388563200,87907.0000,22.0000,3077.0000),(398,'CA',NULL,1388563200,136270.0000,26.0000,6593.0000),(399,'CA',NULL,1388563200,136270.0000,29.0000,10681.0000),(400,'CA','BC',1388563200,37606.0000,5.0600,0.0000),(401,'CA','BC',1388563200,75213.0000,7.7000,993.0000),(402,'CA','BC',1388563200,86354.0000,10.5000,3099.0000),(403,'CA','BC',1388563200,104858.0000,12.2900,4644.0000),(404,'CA','BC',1388563200,150000.0000,14.7000,7172.0000),(405,'CA','BC',1388563200,150000.0000,16.8000,10322.0000),(406,'CA','NB',1388563200,39305.0000,9.6800,0.0000),(407,'CA','NB',1388563200,78609.0000,14.8200,2020.0000),(408,'CA','NB',1388563200,127802.0000,16.5200,3357.0000),(409,'CA','NB',1388563200,127802.0000,17.8400,5044.0000),(410,'CA','NL',1388563200,34254.0000,7.7000,0.0000),(411,'CA','NL',1388563200,68508.0000,12.5000,1644.0000),(412,'CA','NL',1388563200,68508.0000,13.3000,2192.0000),(413,'CA','NT',1388563200,39808.0000,5.9000,0.0000),(414,'CA','NT',1388563200,79618.0000,8.6000,1075.0000),(415,'CA','NT',1388563200,129441.0000,12.2000,3941.0000),(416,'CA','NT',1388563200,129441.0000,14.0500,6336.0000),(417,'CA','NU',1388563200,41909.0000,4.0000,0.0000),(418,'CA','NU',1388563200,83818.0000,7.0000,1257.0000),(419,'CA','NU',1388563200,136270.0000,9.0000,2934.0000),(420,'CA','NU',1388563200,136270.0000,11.5000,6340.0000),(421,'CA','ON',1388563200,40120.0000,5.0500,0.0000),(422,'CA','ON',1388563200,80242.0000,9.1500,1645.0000),(423,'CA','ON',1388563200,514090.0000,11.1600,3258.0000),(424,'CA','ON',1388563200,514090.0000,13.1600,13540.0000),(425,'CA','SK',1388563200,43292.0000,11.0000,0.0000),(426,'CA','SK',1388563200,123692.0000,13.0000,866.0000),(427,'CA','SK',1388563200,123692.0000,15.0000,3340.0000),(428,'CA','YT',1388563200,43953.0000,7.0400,0.0000),(429,'CA','YT',1388563200,87907.0000,9.6800,1160.0000),(430,'CA','YT',1388563200,136270.0000,11.4400,2708.0000),(431,'CA','YT',1388563200,136270.0000,12.7600,4506.0000),(432,'CA','ON',1409554800,40120.0000,5.0500,0.0000),(433,'CA','ON',1409554800,80242.0000,9.1500,1645.0000),(434,'CA','ON',1409554800,150000.0000,11.1600,3258.0000),(435,'CA','ON',1409554800,220000.0000,14.1600,7758.0000),(436,'CA','ON',1409554800,514090.0000,17.1600,14358.0000),(437,'CA','ON',1409554800,514090.0000,13.1600,-6206.0000),(438,'CA',NULL,1420099200,44701.0000,15.0000,0.0000),(439,'CA',NULL,1420099200,89401.0000,22.0000,3129.0000),(440,'CA',NULL,1420099200,138586.0000,26.0000,6705.0000),(441,'CA',NULL,1420099200,138586.0000,29.0000,10863.0000),(442,'CA','BC',1420099200,37869.0000,5.0600,0.0000),(443,'CA','BC',1420099200,75740.0000,7.7000,1000.0000),(444,'CA','BC',1420099200,86958.0000,10.5000,3120.0000),(445,'CA','BC',1420099200,105592.0000,12.2900,4677.0000),(446,'CA','BC',1420099200,151050.0000,14.7000,7222.0000),(447,'CA','BC',1420099200,151050.0000,16.8000,10394.0000),(448,'CA','NB',1420099200,39973.0000,9.6800,0.0000),(449,'CA','NB',1420099200,79946.0000,14.8200,2055.0000),(450,'CA','NB',1420099200,129975.0000,16.5200,3414.0000),(451,'CA','NB',1420099200,129975.0000,17.8400,5129.0000),(452,'CA','NL',1420099200,35008.0000,7.7000,0.0000),(453,'CA','NL',1420099200,70015.0000,12.5000,1680.0000),(454,'CA','NL',1420099200,70015.0000,13.3000,2241.0000),(455,'CA','NT',1420099200,40484.0000,5.9000,0.0000),(456,'CA','NT',1420099200,80971.0000,8.6000,1093.0000),(457,'CA','NT',1420099200,131641.0000,12.2000,4008.0000),(458,'CA','NT',1420099200,131641.0000,14.0500,6443.0000),(459,'CA','NU',1420099200,42622.0000,4.0000,0.0000),(460,'CA','NU',1420099200,85243.0000,7.0000,1279.0000),(461,'CA','NU',1420099200,138586.0000,9.0000,2984.0000),(462,'CA','NU',1420099200,138586.0000,11.5000,6448.0000),(463,'CA','ON',1420099200,40922.0000,5.0500,0.0000),(464,'CA','ON',1420099200,81847.0000,9.1500,1678.0000),(465,'CA','ON',1420099200,150000.0000,11.1600,3323.0000),(466,'CA','ON',1420099200,220000.0000,12.1600,4823.0000),(467,'CA','ON',1420099200,220000.0000,13.1600,7023.0000),(468,'CA','SK',1420099200,44028.0000,11.0000,0.0000),(469,'CA','SK',1420099200,125795.0000,13.0000,881.0000),(470,'CA','SK',1420099200,125795.0000,15.0000,3396.0000),(471,'CA','YT',1420099200,44701.0000,7.0400,0.0000),(472,'CA','YT',1420099200,89401.0000,9.6800,1180.0000),(473,'CA','YT',1420099200,138586.0000,11.4400,2754.0000),(474,'CA','YT',1420099200,138586.0000,12.7600,4583.0000);
/*!40000 ALTER TABLE `income_tax_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_tax_rate_cr`
--

DROP TABLE IF EXISTS `income_tax_rate_cr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_tax_rate_cr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) DEFAULT NULL,
  `state` varchar(250) DEFAULT NULL,
  `district` varchar(250) DEFAULT NULL,
  `effective_date` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `income` decimal(20,4) NOT NULL,
  `rate` decimal(20,4) NOT NULL,
  `constant` decimal(20,4) DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `income_tax_rate_cr_id_uniq` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_tax_rate_cr`
--

LOCK TABLES `income_tax_rate_cr` WRITE;
/*!40000 ALTER TABLE `income_tax_rate_cr` DISABLE KEYS */;
INSERT INTO `income_tax_rate_cr` VALUES (1,'CR',NULL,NULL,1159678800,10,5616000.0000,0.0000,0.0000),(2,'CR',NULL,NULL,1159678800,10,8424000.0000,10.0000,0.0000),(3,'CR',NULL,NULL,1159678800,10,8424000.0000,15.0000,0.0000),(4,'CR',NULL,NULL,1191214800,10,6096000.0000,0.0000,0.0000),(5,'CR',NULL,NULL,1191214800,10,9144000.0000,10.0000,0.0000),(6,'CR',NULL,NULL,1191214800,10,9144000.0000,15.0000,0.0000);
/*!40000 ALTER TABLE `income_tax_rate_cr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_tax_rate_us`
--

DROP TABLE IF EXISTS `income_tax_rate_us`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_tax_rate_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) DEFAULT NULL,
  `state` varchar(250) DEFAULT NULL,
  `district` varchar(250) DEFAULT NULL,
  `effective_date` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `income` decimal(18,4) DEFAULT NULL,
  `rate` decimal(18,4) DEFAULT NULL,
  `constant` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `income_tax_rate_us_id_uniq` (`id`),
  KEY `income_tax_rate_us_state_district_status` (`state`,`district`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2517 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_tax_rate_us`
--

LOCK TABLES `income_tax_rate_us` WRITE;
/*!40000 ALTER TABLE `income_tax_rate_us` DISABLE KEYS */;
INSERT INTO `income_tax_rate_us` VALUES (15,'US','MO',NULL,1136102400,10,1000.0000,1.5000,0.0000),(16,'US','MO',NULL,1136102400,10,2000.0000,2.0000,15.0000),(17,'US','MO',NULL,1136102400,10,3000.0000,2.5000,35.0000),(18,'US','MO',NULL,1136102400,10,4000.0000,3.0000,60.0000),(19,'US','MO',NULL,1136102400,10,5000.0000,3.5000,90.0000),(20,'US','MO',NULL,1136102400,10,6000.0000,4.0000,125.0000),(21,'US','MO',NULL,1136102400,10,7000.0000,4.5000,165.0000),(22,'US','MO',NULL,1136102400,10,8000.0000,5.0000,210.0000),(23,'US','MO',NULL,1136102400,10,9000.0000,5.5000,260.0000),(24,'US','MO',NULL,1136102400,10,9000.0000,6.0000,315.0000),(25,'US','MO',NULL,1136102400,20,1000.0000,1.5000,0.0000),(26,'US','MO',NULL,1136102400,20,2000.0000,2.0000,15.0000),(27,'US','MO',NULL,1136102400,20,3000.0000,2.5000,35.0000),(28,'US','MO',NULL,1136102400,20,4000.0000,3.0000,60.0000),(29,'US','MO',NULL,1136102400,20,5000.0000,3.5000,90.0000),(30,'US','MO',NULL,1136102400,20,6000.0000,4.0000,125.0000),(31,'US','MO',NULL,1136102400,20,7000.0000,4.5000,165.0000),(32,'US','MO',NULL,1136102400,20,8000.0000,5.0000,210.0000),(33,'US','MO',NULL,1136102400,20,9000.0000,5.5000,260.0000),(34,'US','MO',NULL,1136102400,20,9000.0000,6.0000,315.0000),(35,'US','MO',NULL,1136102400,30,1000.0000,1.5000,0.0000),(36,'US','MO',NULL,1136102400,30,2000.0000,2.0000,15.0000),(37,'US','MO',NULL,1136102400,30,3000.0000,2.5000,35.0000),(38,'US','MO',NULL,1136102400,30,4000.0000,3.0000,60.0000),(39,'US','MO',NULL,1136102400,30,5000.0000,3.5000,90.0000),(40,'US','MO',NULL,1136102400,30,6000.0000,4.0000,125.0000),(41,'US','MO',NULL,1136102400,30,7000.0000,4.5000,165.0000),(42,'US','MO',NULL,1136102400,30,8000.0000,5.0000,210.0000),(43,'US','MO',NULL,1136102400,30,9000.0000,5.5000,260.0000),(44,'US','MO',NULL,1136102400,30,9000.0000,6.0000,315.0000),(45,'US','MO',NULL,1136102400,40,1000.0000,1.5000,0.0000),(46,'US','MO',NULL,1136102400,40,2000.0000,2.0000,15.0000),(47,'US','MO',NULL,1136102400,40,3000.0000,2.5000,35.0000),(48,'US','MO',NULL,1136102400,40,4000.0000,3.0000,60.0000),(49,'US','MO',NULL,1136102400,40,5000.0000,3.5000,90.0000),(50,'US','MO',NULL,1136102400,40,6000.0000,4.0000,125.0000),(51,'US','MO',NULL,1136102400,40,7000.0000,4.5000,165.0000),(52,'US','MO',NULL,1136102400,40,8000.0000,5.0000,210.0000),(53,'US','MO',NULL,1136102400,40,9000.0000,5.5000,260.0000),(54,'US','MO',NULL,1136102400,40,9000.0000,6.0000,315.0000),(55,'US','CA',NULL,1136102400,10,6319.0000,1.0000,0.0000),(56,'US','CA',NULL,1136102400,10,14979.0000,2.0000,63.1900),(57,'US','CA',NULL,1136102400,10,23641.0000,4.0000,236.3900),(58,'US','CA',NULL,1136102400,10,32819.0000,6.0000,582.8700),(59,'US','CA',NULL,1136102400,10,41476.0000,8.0000,1133.5500),(60,'US','CA',NULL,1136102400,10,999999.0000,9.3000,1826.1100),(61,'US','CA',NULL,1136102400,10,999999.0000,10.3000,90968.7500),(69,'US','CA',NULL,1136102400,20,6319.0000,1.0000,0.0000),(70,'US','CA',NULL,1136102400,20,14979.0000,2.0000,63.1900),(71,'US','CA',NULL,1136102400,20,23641.0000,4.0000,236.3900),(72,'US','CA',NULL,1136102400,20,32819.0000,6.0000,582.8700),(73,'US','CA',NULL,1136102400,20,41476.0000,8.0000,1133.5500),(74,'US','CA',NULL,1136102400,20,999999.0000,9.3000,1826.1100),(75,'US','CA',NULL,1136102400,20,999999.0000,10.3000,90968.7500),(76,'US','CA',NULL,1136102400,30,12638.0000,1.0000,0.0000),(77,'US','CA',NULL,1136102400,30,29958.0000,2.0000,126.3800),(78,'US','CA',NULL,1136102400,30,47282.0000,4.0000,472.7800),(79,'US','CA',NULL,1136102400,30,65638.0000,6.0000,1165.7400),(80,'US','CA',NULL,1136102400,30,82952.0000,8.0000,2267.1000),(81,'US','CA',NULL,1136102400,30,999999.0000,9.3000,3652.2200),(82,'US','CA',NULL,1136102400,30,999999.0000,10.3000,88937.5900),(83,'US','CA',NULL,1136102400,40,12644.0000,1.0000,0.0000),(84,'US','CA',NULL,1136102400,40,29959.0000,2.0000,126.4400),(85,'US','CA',NULL,1136102400,40,38619.0000,4.0000,472.7400),(86,'US','CA',NULL,1136102400,40,47796.0000,6.0000,819.1400),(87,'US','CA',NULL,1136102400,40,56456.0000,8.0000,1369.7600),(88,'US','CA',NULL,1136102400,40,999999.0000,9.3000,2062.5600),(89,'US','CA',NULL,1136102400,40,999999.0000,10.3000,89812.0600),(90,'US','NY',NULL,1136102400,10,8000.0000,4.0000,0.0000),(91,'US','NY',NULL,1136102400,10,11000.0000,4.5000,320.0000),(92,'US','NY',NULL,1136102400,10,13000.0000,5.2500,455.0000),(93,'US','NY',NULL,1136102400,10,20000.0000,5.9000,580.0000),(94,'US','NY',NULL,1136102400,10,90000.0000,6.8500,973.0000),(95,'US','NY',NULL,1136102400,10,100000.0000,7.6400,5768.0000),(96,'US','NY',NULL,1136102400,10,150000.0000,8.1400,6532.0000),(97,'US','NY',NULL,1136102400,10,150000.0000,7.3500,10604.0000),(98,'US','NY',NULL,1136102400,20,8000.0000,4.0000,0.0000),(99,'US','NY',NULL,1136102400,20,11000.0000,4.5000,320.0000),(100,'US','NY',NULL,1136102400,20,13000.0000,5.2500,455.0000),(101,'US','NY',NULL,1136102400,20,20000.0000,5.9000,580.0000),(102,'US','NY',NULL,1136102400,20,90000.0000,6.8500,973.0000),(103,'US','NY',NULL,1136102400,20,100000.0000,7.6400,5768.0000),(104,'US','NY',NULL,1136102400,20,150000.0000,8.1400,6532.0000),(105,'US','NY',NULL,1136102400,20,150000.0000,7.3500,10604.0000),(106,'US','NY','NYC',1136102400,10,8000.0000,1.9000,0.0000),(107,'US','NY','NYC',1136102400,10,8700.0000,2.6500,152.0000),(108,'US','NY','NYC',1136102400,10,15000.0000,3.1000,172.0000),(109,'US','NY','NYC',1136102400,10,25000.0000,3.7000,366.0000),(110,'US','NY','NYC',1136102400,10,60000.0000,3.9000,736.0000),(111,'US','NY','NYC',1136102400,10,60000.0000,4.0000,2101.0000),(112,'US','NY','NYC',1136102400,20,8000.0000,1.9000,0.0000),(113,'US','NY','NYC',1136102400,20,8700.0000,2.6500,152.0000),(114,'US','NY','NYC',1136102400,20,15000.0000,3.1000,172.0000),(115,'US','NY','NYC',1136102400,20,25000.0000,3.7000,366.0000),(116,'US','NY','NYC',1136102400,20,60000.0000,3.9000,736.0000),(117,'US','NY','NYC',1136102400,20,60000.0000,4.0000,2101.0000),(118,'US','NY','YONKERS',1136102400,10,8000.0000,4.0000,0.0000),(119,'US','NY','YONKERS',1136102400,10,11000.0000,4.5000,320.0000),(120,'US','NY','YONKERS',1136102400,10,13000.0000,5.2500,455.0000),(121,'US','NY','YONKERS',1136102400,10,20000.0000,5.9000,580.0000),(122,'US','NY','YONKERS',1136102400,10,90000.0000,6.8500,973.0000),(123,'US','NY','YONKERS',1136102400,10,100000.0000,7.6400,5768.0000),(124,'US','NY','YONKERS',1136102400,10,150000.0000,8.1400,6532.0000),(125,'US','NY','YONKERS',1136102400,10,150000.0000,7.3500,10604.0000),(126,'US','NY','YONKERS',1136102400,20,8000.0000,4.0000,0.0000),(127,'US','NY','YONKERS',1136102400,20,11000.0000,4.5000,320.0000),(128,'US','NY','YONKERS',1136102400,20,13000.0000,5.2500,455.0000),(129,'US','NY','YONKERS',1136102400,20,20000.0000,5.9000,580.0000),(130,'US','NY','YONKERS',1136102400,20,90000.0000,6.8500,973.0000),(131,'US','NY','YONKERS',1136102400,20,100000.0000,7.6400,5768.0000),(132,'US','NY','YONKERS',1136102400,20,150000.0000,8.1400,6532.0000),(133,'US','NY','YONKERS',1136102400,20,150000.0000,7.3500,10604.0000),(134,'US','OH',NULL,1136102400,0,5000.0000,0.7740,0.0000),(135,'US','OH',NULL,1136102400,0,10000.0000,1.5470,38.7000),(136,'US','OH',NULL,1136102400,0,15000.0000,3.0940,116.0500),(137,'US','OH',NULL,1136102400,0,20000.0000,3.8680,270.7500),(138,'US','OH',NULL,1136102400,0,40000.0000,4.6420,464.1500),(139,'US','OH',NULL,1136102400,0,80000.0000,5.4160,1392.5500),(140,'US','OH',NULL,1136102400,0,100000.0000,6.1890,3558.9500),(141,'US','OH',NULL,1136102400,0,100000.0000,7.7360,4796.7500),(142,'US','GA',NULL,1136102400,10,750.0000,1.0000,0.0000),(143,'US','GA',NULL,1136102400,10,2250.0000,2.0000,7.5000),(144,'US','GA',NULL,1136102400,10,3750.0000,3.0000,37.5000),(145,'US','GA',NULL,1136102400,10,5250.0000,4.0000,82.5000),(146,'US','GA',NULL,1136102400,10,7000.0000,5.0000,142.5000),(147,'US','GA',NULL,1136102400,10,7000.0000,6.0000,230.0000),(148,'US','GA',NULL,1136102400,20,500.0000,1.0000,0.0000),(149,'US','GA',NULL,1136102400,20,1500.0000,2.0000,5.0000),(150,'US','GA',NULL,1136102400,20,2500.0000,3.0000,25.0000),(151,'US','GA',NULL,1136102400,20,3500.0000,4.0000,55.0000),(152,'US','GA',NULL,1136102400,20,5000.0000,5.0000,95.0000),(153,'US','GA',NULL,1136102400,20,5000.0000,6.0000,170.0000),(154,'US','GA',NULL,1136102400,30,1000.0000,1.0000,0.0000),(155,'US','GA',NULL,1136102400,30,3000.0000,2.0000,10.0000),(156,'US','GA',NULL,1136102400,30,5000.0000,3.0000,50.0000),(157,'US','GA',NULL,1136102400,30,7000.0000,4.0000,110.0000),(158,'US','GA',NULL,1136102400,30,10000.0000,5.0000,190.0000),(159,'US','GA',NULL,1136102400,30,10000.0000,6.0000,340.0000),(160,'US','GA',NULL,1136102400,40,500.0000,1.0000,0.0000),(161,'US','GA',NULL,1136102400,40,1500.0000,2.0000,5.0000),(162,'US','GA',NULL,1136102400,40,2500.0000,3.0000,25.0000),(163,'US','GA',NULL,1136102400,40,3500.0000,4.0000,55.0000),(164,'US','GA',NULL,1136102400,40,5000.0000,5.0000,95.0000),(165,'US','GA',NULL,1136102400,40,5000.0000,6.0000,170.0000),(166,'US','GA',NULL,1136102400,50,1000.0000,1.0000,0.0000),(167,'US','GA',NULL,1136102400,50,3000.0000,2.0000,10.0000),(168,'US','GA',NULL,1136102400,50,5000.0000,3.0000,50.0000),(169,'US','GA',NULL,1136102400,50,7000.0000,4.0000,110.0000),(170,'US','GA',NULL,1136102400,50,10000.0000,5.0000,190.0000),(171,'US','GA',NULL,1136102400,50,10000.0000,6.0000,340.0000),(172,'US','NJ',NULL,1136102400,10,20000.0000,1.5000,0.0000),(173,'US','NJ',NULL,1136102400,10,35000.0000,2.0000,300.0000),(174,'US','NJ',NULL,1136102400,10,40000.0000,3.9000,600.0000),(175,'US','NJ',NULL,1136102400,10,75000.0000,6.1000,795.0000),(176,'US','NJ',NULL,1136102400,10,500000.0000,7.0000,2930.0000),(177,'US','NJ',NULL,1136102400,10,500000.0000,9.9000,32680.0000),(178,'US','NJ',NULL,1136102400,20,20000.0000,1.5000,0.0000),(179,'US','NJ',NULL,1136102400,20,50000.0000,2.0000,300.0000),(180,'US','NJ',NULL,1136102400,20,70000.0000,2.7000,900.0000),(181,'US','NJ',NULL,1136102400,20,80000.0000,3.9000,1440.0000),(182,'US','NJ',NULL,1136102400,20,150000.0000,6.1000,1830.0000),(183,'US','NJ',NULL,1136102400,20,500000.0000,7.0000,6100.0000),(184,'US','NJ',NULL,1136102400,20,500000.0000,9.9000,30600.0000),(185,'US','NJ',NULL,1136102400,30,20000.0000,1.5000,0.0000),(186,'US','NJ',NULL,1136102400,30,40000.0000,2.3000,300.0000),(187,'US','NJ',NULL,1136102400,30,50000.0000,2.8000,760.0000),(188,'US','NJ',NULL,1136102400,30,60000.0000,3.5000,1040.0000),(189,'US','NJ',NULL,1136102400,30,150000.0000,5.6000,1390.0000),(190,'US','NJ',NULL,1136102400,30,500000.0000,6.6000,6430.0000),(191,'US','NJ',NULL,1136102400,30,500000.0000,9.9000,29530.0000),(192,'US','NJ',NULL,1136102400,40,20000.0000,1.5000,0.0000),(193,'US','NJ',NULL,1136102400,40,40000.0000,2.7000,300.0000),(194,'US','NJ',NULL,1136102400,40,50000.0000,3.4000,840.0000),(195,'US','NJ',NULL,1136102400,40,60000.0000,4.3000,1180.0000),(196,'US','NJ',NULL,1136102400,40,150000.0000,5.6000,1610.0000),(197,'US','NJ',NULL,1136102400,40,500000.0000,6.5000,6650.0000),(198,'US','NJ',NULL,1136102400,40,500000.0000,9.9000,29400.0000),(199,'US','NJ',NULL,1136102400,50,20000.0000,1.5000,0.0000),(200,'US','NJ',NULL,1136102400,50,35000.0000,2.0000,300.0000),(201,'US','NJ',NULL,1136102400,50,100000.0000,5.8000,600.0000),(202,'US','NJ',NULL,1136102400,50,500000.0000,6.5000,4370.0000),(203,'US','NJ',NULL,1136102400,50,500000.0000,9.9000,30370.0000),(204,'US','NC',NULL,1136102400,10,12750.0000,6.0000,0.0000),(205,'US','NC',NULL,1136102400,10,60000.0000,7.0000,127.5000),(206,'US','NC',NULL,1136102400,10,120000.0000,7.7500,577.5000),(207,'US','NC',NULL,1136102400,10,120000.0000,8.2500,1177.5000),(208,'US','NC',NULL,1136102400,20,10625.0000,6.0000,0.0000),(209,'US','NC',NULL,1136102400,20,50000.0000,7.0000,106.2500),(210,'US','NC',NULL,1136102400,20,100000.0000,7.7500,481.2500),(211,'US','NC',NULL,1136102400,20,100000.0000,8.2500,981.2500),(212,'US','NC',NULL,1136102400,30,17000.0000,6.0000,0.0000),(213,'US','NC',NULL,1136102400,30,80000.0000,7.0000,170.0000),(214,'US','NC',NULL,1136102400,30,160000.0000,7.7500,770.0000),(215,'US','NC',NULL,1136102400,30,160000.0000,8.2500,1570.0000),(216,'US','VA',NULL,1136102400,0,3000.0000,2.0000,0.0000),(217,'US','VA',NULL,1136102400,0,5000.0000,3.0000,60.0000),(218,'US','VA',NULL,1136102400,0,17000.0000,5.0000,120.0000),(219,'US','VA',NULL,1136102400,0,17000.0000,5.7500,720.0000),(220,'US','WI',NULL,1136102400,10,4000.0000,0.0000,0.0000),(221,'US','WI',NULL,1136102400,10,10620.0000,4.6000,0.0000),(222,'US','WI',NULL,1136102400,10,11825.0000,5.1540,305.0000),(223,'US','WI',NULL,1136102400,10,18629.0000,6.8880,367.0000),(224,'US','WI',NULL,1136102400,10,43953.0000,7.2800,836.0000),(225,'US','WI',NULL,1136102400,10,115140.0000,6.5000,2680.0000),(226,'US','WI',NULL,1136102400,10,115140.0000,6.7500,7307.0000),(227,'US','WI',NULL,1136102400,20,5500.0000,0.0000,0.0000),(228,'US','WI',NULL,1136102400,20,13470.0000,4.6000,0.0000),(229,'US','WI',NULL,1136102400,20,14950.0000,6.1500,367.0000),(230,'US','WI',NULL,1136102400,20,20067.0000,7.3800,458.0000),(231,'US','WI',NULL,1136102400,20,42450.0000,7.8000,836.0000),(232,'US','WI',NULL,1136102400,20,115140.0000,6.5000,2582.0000),(233,'US','WI',NULL,1136102400,20,115140.0000,6.7500,7307.0000),(234,'US','MN',NULL,1136102400,10,1850.0000,0.0000,0.0000),(235,'US','MN',NULL,1136102400,10,22360.0000,5.3500,0.0000),(236,'US','MN',NULL,1136102400,10,69210.0000,7.0500,1097.2900),(237,'US','MN',NULL,1136102400,10,69210.0000,7.8500,4400.2200),(238,'US','MN',NULL,1136102400,20,6150.0000,0.0000,0.0000),(239,'US','MN',NULL,1136102400,20,36130.0000,5.3500,0.0000),(240,'US','MN',NULL,1136102400,20,125250.0000,7.0500,1603.9300),(241,'US','MN',NULL,1136102400,20,125250.0000,7.8500,7886.8900),(242,'US','CO',NULL,1136102400,10,1850.0000,0.0000,0.0000),(243,'US','CO',NULL,1136102400,10,1850.0000,4.6300,0.0000),(244,'US','CO',NULL,1136102400,20,7000.0000,0.0000,0.0000),(245,'US','CO',NULL,1136102400,20,7000.0000,4.6300,0.0000),(258,'US','SC',NULL,1136102400,0,2000.0000,2.0000,0.0000),(259,'US','SC',NULL,1136102400,0,4000.0000,3.0000,20.0000),(260,'US','SC',NULL,1136102400,0,6000.0000,4.0000,60.0000),(261,'US','SC',NULL,1136102400,0,8000.0000,5.0000,120.0000),(262,'US','SC',NULL,1136102400,0,10000.0000,6.0000,200.0000),(263,'US','SC',NULL,1136102400,0,10000.0000,7.0000,300.0000),(264,'US','KY',NULL,1136102400,0,3000.0000,2.0000,0.0000),(265,'US','KY',NULL,1136102400,0,4000.0000,3.0000,60.0000),(266,'US','KY',NULL,1136102400,0,5000.0000,4.0000,90.0000),(267,'US','KY',NULL,1136102400,0,8000.0000,5.0000,130.0000),(268,'US','KY',NULL,1136102400,0,75000.0000,5.8000,280.0000),(269,'US','KY',NULL,1136102400,0,75000.0000,6.0000,4166.0000),(270,'US','OR',NULL,1136102400,10,300.0000,0.0000,0.0000),(271,'US','OR',NULL,1136102400,10,8030.0000,7.0000,0.0000),(272,'US','OR',NULL,1136102400,10,8030.0000,9.0000,541.0000),(273,'US','OR',NULL,1136102400,20,2725.0000,0.0000,0.0000),(274,'US','OR',NULL,1136102400,20,16065.0000,7.0000,0.0000),(275,'US','OR',NULL,1136102400,20,16065.0000,9.0000,934.0000),(276,'US','OK',NULL,1136102400,10,2000.0000,0.0000,0.0000),(277,'US','OK',NULL,1136102400,10,3000.0000,0.5000,0.0000),(278,'US','OK',NULL,1136102400,10,4500.0000,1.0000,5.0000),(279,'US','OK',NULL,1136102400,10,5750.0000,2.0000,20.0000),(280,'US','OK',NULL,1136102400,10,6900.0000,3.0000,45.0000),(281,'US','OK',NULL,1136102400,10,9200.0000,4.0000,79.5000),(282,'US','OK',NULL,1136102400,10,10700.0000,5.0000,171.5000),(283,'US','OK',NULL,1136102400,10,12500.0000,6.0000,246.5000),(284,'US','OK',NULL,1136102400,10,12500.0000,6.2500,354.5000),(285,'US','OK',NULL,1136102400,20,3000.0000,0.0000,0.0000),(286,'US','OK',NULL,1136102400,20,5000.0000,0.5000,0.0000),(287,'US','OK',NULL,1136102400,20,8500.0000,1.0000,10.0000),(288,'US','OK',NULL,1136102400,20,10500.0000,2.0000,40.0000),(289,'US','OK',NULL,1136102400,20,12800.0000,3.0000,90.0000),(290,'US','OK',NULL,1136102400,20,15200.0000,4.0000,159.0000),(291,'US','OK',NULL,1136102400,20,18000.0000,5.0000,255.0000),(292,'US','OK',NULL,1136102400,20,24000.0000,6.0000,395.0000),(293,'US','OK',NULL,1136102400,20,24000.0000,6.2500,755.0000),(294,'US','CT',NULL,1136102400,10,0.0000,0.0000,0.0000),(295,'US','CT',NULL,1136102400,10,10000.0000,3.0000,0.0000),(296,'US','CT',NULL,1136102400,10,10000.0000,5.0000,300.0000),(297,'US','CT',NULL,1136102400,40,0.0000,0.0000,0.0000),(298,'US','CT',NULL,1136102400,40,10000.0000,3.0000,0.0000),(299,'US','CT',NULL,1136102400,40,10000.0000,5.0000,300.0000),(300,'US','CT',NULL,1136102400,60,0.0000,0.0000,0.0000),(301,'US','CT',NULL,1136102400,60,10000.0000,3.0000,0.0000),(302,'US','CT',NULL,1136102400,60,10000.0000,5.0000,300.0000),(303,'US','CT',NULL,1136102400,20,0.0000,0.0000,0.0000),(304,'US','CT',NULL,1136102400,20,16000.0000,3.0000,0.0000),(305,'US','CT',NULL,1136102400,20,16000.0000,5.0000,480.0000),(306,'US','CT',NULL,1136102400,30,0.0000,0.0000,0.0000),(307,'US','CT',NULL,1136102400,30,20000.0000,3.0000,0.0000),(308,'US','CT',NULL,1136102400,30,20000.0000,5.0000,600.0000),(309,'US','IA',NULL,1143878400,0,1300.0000,0.3600,0.0000),(310,'US','IA',NULL,1143878400,0,2600.0000,0.7200,4.6800),(311,'US','IA',NULL,1143878400,0,5200.0000,2.4300,14.0400),(312,'US','IA',NULL,1143878400,0,11700.0000,4.5000,77.2200),(313,'US','IA',NULL,1143878400,0,19500.0000,6.1200,369.7200),(314,'US','IA',NULL,1143878400,0,26000.0000,6.4800,847.0800),(315,'US','IA',NULL,1143878400,0,39000.0000,6.8000,1268.2800),(316,'US','IA',NULL,1143878400,0,58500.0000,7.9200,2152.2800),(317,'US','IA',NULL,1143878400,0,58500.0000,8.9800,3696.6800),(318,'US','MS',NULL,1136102400,0,5000.0000,3.0000,0.0000),(319,'US','MS',NULL,1136102400,0,10000.0000,4.0000,150.0000),(320,'US','MS',NULL,1136102400,0,10000.0000,5.0000,350.0000),(321,'US','AR',NULL,1136102400,0,3000.0000,1.0000,0.0000),(322,'US','AR',NULL,1136102400,0,6000.0000,2.5000,30.0000),(323,'US','AR',NULL,1136102400,0,9000.0000,3.5000,105.0000),(324,'US','AR',NULL,1136102400,0,15000.0000,4.5000,210.0000),(325,'US','AR',NULL,1136102400,0,25000.0000,6.0000,480.0000),(326,'US','AR',NULL,1136102400,0,25000.0000,7.0000,1080.0000),(327,'US','KS',NULL,1136102400,10,3000.0000,0.0000,0.0000),(328,'US','KS',NULL,1136102400,10,18000.0000,3.5000,0.0000),(329,'US','KS',NULL,1136102400,10,33000.0000,6.2500,525.0000),(330,'US','KS',NULL,1136102400,10,33000.0000,6.4500,1462.5000),(331,'US','KS',NULL,1136102400,20,6000.0000,0.0000,0.0000),(332,'US','KS',NULL,1136102400,20,36000.0000,3.5000,0.0000),(333,'US','KS',NULL,1136102400,20,66000.0000,6.2500,1050.0000),(334,'US','KS',NULL,1136102400,20,66000.0000,6.4500,2925.0000),(335,'US','UT',NULL,1136102400,10,2300.0000,0.0000,0.0000),(336,'US','UT',NULL,1136102400,10,3163.0000,2.3000,0.0000),(337,'US','UT',NULL,1136102400,10,4026.0000,3.1000,20.0000),(338,'US','UT',NULL,1136102400,10,4888.0000,4.0000,47.0000),(339,'US','UT',NULL,1136102400,10,5750.0000,4.9000,81.0000),(340,'US','UT',NULL,1136102400,10,6613.0000,5.7000,123.0000),(341,'US','UT',NULL,1136102400,10,6613.0000,6.5000,172.0000),(342,'US','UT',NULL,1136102400,20,2300.0000,0.0000,0.0000),(343,'US','UT',NULL,1136102400,20,4026.0000,2.3000,0.0000),(344,'US','UT',NULL,1136102400,20,5750.0000,3.1000,40.0000),(345,'US','UT',NULL,1136102400,20,7476.0000,4.0000,93.0000),(346,'US','UT',NULL,1136102400,20,9200.0000,4.9000,162.0000),(347,'US','UT',NULL,1136102400,20,10926.0000,5.7000,246.0000),(348,'US','UT',NULL,1136102400,20,10926.0000,6.5000,344.0000),(349,'US','NM',NULL,1136102400,10,1800.0000,0.0000,0.0000),(350,'US','NM',NULL,1136102400,10,7300.0000,1.7000,0.0000),(351,'US','NM',NULL,1136102400,10,12800.0000,3.2000,93.5000),(352,'US','NM',NULL,1136102400,10,17800.0000,4.7000,269.5000),(353,'US','NM',NULL,1136102400,10,17800.0000,5.3000,504.5000),(354,'US','NM',NULL,1136102400,20,6950.0000,0.0000,0.0000),(355,'US','NM',NULL,1136102400,20,14950.0000,1.7000,0.0000),(356,'US','NM',NULL,1136102400,20,22950.0000,3.2000,136.0000),(357,'US','NM',NULL,1136102400,20,30950.0000,4.7000,392.0000),(358,'US','NM',NULL,1136102400,20,30950.0000,5.3000,768.0000),(359,'US','WV',NULL,1136102400,10,10000.0000,3.0000,0.0000),(360,'US','WV',NULL,1136102400,10,25000.0000,4.0000,300.0000),(361,'US','WV',NULL,1136102400,10,40000.0000,4.5000,900.0000),(362,'US','WV',NULL,1136102400,10,60000.0000,6.0000,1575.0000),(363,'US','WV',NULL,1136102400,10,60000.0000,6.5000,2775.0000),(364,'US','WV',NULL,1136102400,20,6000.0000,3.0000,0.0000),(365,'US','WV',NULL,1136102400,20,15000.0000,4.0000,180.0000),(366,'US','WV',NULL,1136102400,20,24000.0000,4.5000,540.0000),(367,'US','WV',NULL,1136102400,20,36000.0000,6.0000,945.0000),(368,'US','WV',NULL,1136102400,20,36000.0000,6.5000,1665.0000),(369,'US','NE',NULL,1136102400,10,2000.0000,0.0000,0.0000),(370,'US','NE',NULL,1136102400,10,4400.0000,2.4900,0.0000),(371,'US','NE',NULL,1136102400,10,15500.0000,3.4700,54.7800),(372,'US','NE',NULL,1136102400,10,22750.0000,5.3200,439.9500),(373,'US','NE',NULL,1136102400,10,28100.0000,6.5700,825.6500),(374,'US','NE',NULL,1136102400,10,54100.0000,6.9800,1177.1500),(375,'US','NE',NULL,1136102400,10,75100.0000,7.2200,2991.9500),(376,'US','NE',NULL,1136102400,10,75100.0000,7.3600,4508.1500),(377,'US','NE',NULL,1136102400,20,5250.0000,0.0000,0.0000),(378,'US','NE',NULL,1136102400,20,8250.0000,2.4900,0.0000),(379,'US','NE',NULL,1136102400,20,22400.0000,3.4700,74.7000),(380,'US','NE',NULL,1136102400,20,35400.0000,5.3200,565.7100),(381,'US','NE',NULL,1136102400,20,42950.0000,6.5700,1257.3500),(382,'US','NE',NULL,1136102400,20,58250.0000,6.9800,1753.3500),(383,'US','NE',NULL,1136102400,20,75250.0000,7.2200,2821.2900),(384,'US','NE',NULL,1136102400,20,75250.0000,7.3600,4048.6900),(385,'US','ID',NULL,1136102400,10,1800.0000,0.0000,0.0000),(386,'US','ID',NULL,1136102400,10,2959.0000,1.6000,0.0000),(387,'US','ID',NULL,1136102400,10,4118.0000,3.6000,19.0000),(388,'US','ID',NULL,1136102400,10,5277.0000,4.1000,61.0000),(389,'US','ID',NULL,1136102400,10,6436.0000,5.1000,109.0000),(390,'US','ID',NULL,1136102400,10,7594.0000,6.1000,168.0000),(391,'US','ID',NULL,1136102400,10,10492.0000,7.1000,239.0000),(392,'US','ID',NULL,1136102400,10,24978.0000,7.4000,445.0000),(393,'US','ID',NULL,1136102400,10,24978.0000,7.8000,1517.0000),(394,'US','ID',NULL,1136102400,20,6800.0000,0.0000,0.0000),(395,'US','ID',NULL,1136102400,20,9118.0000,1.6000,0.0000),(396,'US','ID',NULL,1136102400,20,11436.0000,3.6000,37.0000),(397,'US','ID',NULL,1136102400,20,13754.0000,4.1000,120.0000),(398,'US','ID',NULL,1136102400,20,16072.0000,5.1000,215.0000),(399,'US','ID',NULL,1136102400,20,18388.0000,6.1000,333.0000),(400,'US','ID',NULL,1136102400,20,24184.0000,7.1000,474.0000),(401,'US','ID',NULL,1136102400,20,53156.0000,7.4000,886.0000),(402,'US','ID',NULL,1136102400,20,53156.0000,7.8000,3030.0000),(403,'US','ME',NULL,1136102400,10,2300.0000,0.0000,0.0000),(404,'US','ME',NULL,1136102400,10,6850.0000,2.0000,0.0000),(405,'US','ME',NULL,1136102400,10,11400.0000,4.5000,91.0000),(406,'US','ME',NULL,1136102400,10,20550.0000,7.0000,296.0000),(407,'US','ME',NULL,1136102400,10,20550.0000,8.5000,936.0000),(408,'US','ME',NULL,1136102400,20,5750.0000,0.0000,0.0000),(409,'US','ME',NULL,1136102400,20,14900.0000,2.0000,0.0000),(410,'US','ME',NULL,1136102400,20,24000.0000,4.5000,183.0000),(411,'US','ME',NULL,1136102400,20,42300.0000,7.0000,593.0000),(412,'US','ME',NULL,1136102400,20,42300.0000,8.5000,1874.0000),(413,'US','ME',NULL,1136102400,30,2875.0000,0.0000,0.0000),(414,'US','ME',NULL,1136102400,30,7450.0000,2.0000,0.0000),(415,'US','ME',NULL,1136102400,30,12000.0000,4.5000,92.0000),(416,'US','ME',NULL,1136102400,30,21150.0000,7.0000,296.0000),(417,'US','ME',NULL,1136102400,30,21150.0000,8.5000,937.0000),(418,'US','HI',NULL,1136102400,10,2000.0000,1.4000,0.0000),(419,'US','HI',NULL,1136102400,10,4000.0000,3.2000,28.0000),(420,'US','HI',NULL,1136102400,10,8000.0000,5.5000,92.0000),(421,'US','HI',NULL,1136102400,10,12000.0000,6.4000,312.0000),(422,'US','HI',NULL,1136102400,10,16000.0000,6.8000,568.0000),(423,'US','HI',NULL,1136102400,10,20000.0000,7.2000,840.0000),(424,'US','HI',NULL,1136102400,10,20000.0000,7.6000,1128.0000),(425,'US','HI',NULL,1136102400,20,4000.0000,1.4000,0.0000),(426,'US','HI',NULL,1136102400,20,8000.0000,3.2000,56.0000),(427,'US','HI',NULL,1136102400,20,16000.0000,5.5000,184.0000),(428,'US','HI',NULL,1136102400,20,24000.0000,6.4000,624.0000),(429,'US','HI',NULL,1136102400,20,32000.0000,6.8000,1136.0000),(430,'US','HI',NULL,1136102400,20,40000.0000,7.2000,1680.0000),(431,'US','HI',NULL,1136102400,20,40000.0000,7.6000,2256.0000),(432,'US','RI',NULL,1136102400,10,2650.0000,0.0000,0.0000),(433,'US','RI',NULL,1136102400,10,31500.0000,3.7500,0.0000),(434,'US','RI',NULL,1136102400,10,69750.0000,7.0000,1081.8800),(435,'US','RI',NULL,1136102400,10,151950.0000,7.7500,3759.3800),(436,'US','RI',NULL,1136102400,10,328250.0000,9.0000,10129.8800),(437,'US','RI',NULL,1136102400,10,328250.0000,9.9000,25996.8800),(438,'US','RI',NULL,1136102400,20,6450.0000,0.0000,0.0000),(439,'US','RI',NULL,1136102400,20,54750.0000,3.7500,0.0000),(440,'US','RI',NULL,1136102400,20,116600.0000,7.0000,1811.2500),(441,'US','RI',NULL,1136102400,20,187900.0000,7.7500,6140.7500),(442,'US','RI',NULL,1136102400,20,331500.0000,9.0000,11666.5000),(443,'US','RI',NULL,1136102400,20,331500.0000,9.9000,24590.5000),(444,'US','MT',NULL,1136102400,0,7000.0000,1.8000,0.0000),(445,'US','MT',NULL,1136102400,0,15000.0000,4.4000,126.0000),(446,'US','MT',NULL,1136102400,0,120000.0000,6.0000,478.0000),(447,'US','MT',NULL,1136102400,0,120000.0000,6.6000,6778.0000),(448,'US','DE',NULL,1136102400,0,2000.0000,0.0000,0.0000),(449,'US','DE',NULL,1136102400,0,5000.0000,2.2000,0.0000),(450,'US','DE',NULL,1136102400,0,10000.0000,3.9000,66.0000),(451,'US','DE',NULL,1136102400,0,20000.0000,4.8000,261.0000),(452,'US','DE',NULL,1136102400,0,25000.0000,5.2000,741.0000),(453,'US','DE',NULL,1136102400,0,60000.0000,5.5500,1001.0000),(454,'US','DE',NULL,1136102400,0,60000.0000,5.9500,2943.5000),(455,'US','ND',NULL,1136102400,10,3500.0000,0.0000,0.0000),(456,'US','ND',NULL,1136102400,10,32500.0000,2.1000,0.0000),(457,'US','ND',NULL,1136102400,10,68500.0000,3.9200,609.0000),(458,'US','ND',NULL,1136102400,10,156000.0000,4.3400,2020.2000),(459,'US','ND',NULL,1136102400,10,338100.0000,5.0400,5839.4000),(460,'US','ND',NULL,1136102400,10,338100.0000,5.5400,14987.0000),(461,'US','ND',NULL,1136102400,20,8500.0000,0.0000,0.0000),(462,'US','ND',NULL,1136102400,20,57900.0000,2.1000,0.0000),(463,'US','ND',NULL,1136102400,20,110000.0000,3.9200,1037.4000),(464,'US','ND',NULL,1136102400,20,196000.0000,4.3400,3079.7200),(465,'US','ND',NULL,1136102400,20,343200.0000,5.0400,6812.1200),(466,'US','ND',NULL,1136102400,20,343200.0000,5.5400,14231.0000),(467,'US','VT',NULL,1136102400,10,2650.0000,0.0000,0.0000),(468,'US','VT',NULL,1136102400,10,32240.0000,3.6000,0.0000),(469,'US','VT',NULL,1136102400,10,73250.0000,7.2000,1065.2400),(470,'US','VT',NULL,1136102400,10,156650.0000,8.5000,4017.9700),(471,'US','VT',NULL,1136102400,10,338400.0000,9.0000,11106.9600),(472,'US','VT',NULL,1136102400,10,338400.0000,9.5000,27464.4600),(473,'US','VT',NULL,1136102400,20,8000.0000,0.0000,0.0000),(474,'US','VT',NULL,1136102400,20,56800.0000,3.6000,0.0000),(475,'US','VT',NULL,1136102400,20,126900.0000,7.2000,1756.8000),(476,'US','VT',NULL,1136102400,20,195450.0000,8.5000,6804.0000),(477,'US','VT',NULL,1136102400,20,343550.0000,9.0000,12630.7500),(478,'US','VT',NULL,1136102400,20,343550.0000,9.5000,25959.7500),(479,'US','DC',NULL,1136102400,10,2500.0000,0.0000,0.0000),(480,'US','DC',NULL,1136102400,10,10000.0000,4.5000,0.0000),(481,'US','DC',NULL,1136102400,10,40000.0000,7.0000,337.5000),(482,'US','DC',NULL,1136102400,10,40000.0000,8.7000,2437.5000),(483,'US','DC',NULL,1136102400,20,2500.0000,0.0000,0.0000),(484,'US','DC',NULL,1136102400,20,10000.0000,4.5000,0.0000),(485,'US','DC',NULL,1136102400,20,40000.0000,7.0000,337.5000),(486,'US','DC',NULL,1136102400,20,40000.0000,8.7000,2437.5000),(487,'US','DC',NULL,1136102400,30,1250.0000,0.0000,0.0000),(488,'US','DC',NULL,1136102400,30,10000.0000,4.5000,0.0000),(489,'US','DC',NULL,1136102400,30,40000.0000,7.0000,393.7500),(490,'US','DC',NULL,1136102400,30,40000.0000,8.7000,2493.7500),(491,'US','DC',NULL,1136102400,40,2500.0000,0.0000,0.0000),(492,'US','DC',NULL,1136102400,40,10000.0000,4.5000,0.0000),(493,'US','DC',NULL,1136102400,40,40000.0000,7.0000,337.5000),(494,'US','DC',NULL,1136102400,40,40000.0000,8.7000,2437.5000),(495,'US','RI',NULL,1151218800,10,2650.0000,0.0000,0.0000),(496,'US','RI',NULL,1151218800,10,32240.0000,3.7500,0.0000),(497,'US','RI',NULL,1151218800,10,73250.0000,7.0000,1109.6300),(498,'US','RI',NULL,1151218800,10,156650.0000,7.7500,3980.3300),(499,'US','RI',NULL,1151218800,10,338400.0000,9.0000,10443.8300),(500,'US','RI',NULL,1151218800,10,338400.0000,9.9000,26801.3300),(501,'US','RI',NULL,1151218800,20,6450.0000,0.0000,0.0000),(502,'US','RI',NULL,1151218800,20,56500.0000,3.7500,0.0000),(503,'US','RI',NULL,1151218800,20,120200.0000,7.0000,1876.8800),(504,'US','RI',NULL,1151218800,20,193750.0000,7.7500,6335.8800),(505,'US','RI',NULL,1151218800,20,341850.0000,9.0000,12036.0100),(506,'US','RI',NULL,1151218800,20,341850.0000,9.9000,25365.0100),(507,'US',NULL,NULL,1136102400,10,2650.0000,0.0000,0.0000),(508,'US',NULL,NULL,1136102400,10,10000.0000,10.0000,0.0000),(509,'US',NULL,NULL,1136102400,10,32240.0000,15.0000,735.0000),(510,'US',NULL,NULL,1136102400,10,73250.0000,25.0000,4071.0000),(511,'US',NULL,NULL,1136102400,10,156650.0000,28.0000,14323.5000),(512,'US',NULL,NULL,1136102400,10,338400.0000,33.0000,37675.5000),(513,'US',NULL,NULL,1136102400,10,338400.0000,35.0000,97653.0000),(514,'US',NULL,NULL,1136102400,20,8000.0000,0.0000,0.0000),(515,'US',NULL,NULL,1136102400,20,22900.0000,10.0000,0.0000),(516,'US',NULL,NULL,1136102400,20,68040.0000,15.0000,1490.0000),(517,'US',NULL,NULL,1136102400,20,126900.0000,25.0000,8261.0000),(518,'US',NULL,NULL,1136102400,20,195450.0000,28.0000,22976.0000),(519,'US',NULL,NULL,1136102400,20,343550.0000,33.0000,42170.0000),(520,'US',NULL,NULL,1136102400,20,343550.0000,35.0000,91043.0000),(521,'US',NULL,NULL,1167638400,10,2650.0000,0.0000,0.0000),(522,'US',NULL,NULL,1167638400,10,10120.0000,10.0000,0.0000),(523,'US',NULL,NULL,1167638400,10,33520.0000,15.0000,747.0000),(524,'US',NULL,NULL,1167638400,10,77075.0000,25.0000,4257.0000),(525,'US',NULL,NULL,1167638400,10,162800.0000,28.0000,15145.7500),(526,'US',NULL,NULL,1167638400,10,351650.0000,33.0000,39148.7500),(527,'US',NULL,NULL,1167638400,10,351650.0000,35.0000,101469.2500),(528,'US',NULL,NULL,1167638400,20,8000.0000,0.0000,0.0000),(529,'US',NULL,NULL,1167638400,20,23350.0000,10.0000,0.0000),(530,'US',NULL,NULL,1167638400,20,70700.0000,15.0000,1535.0000),(531,'US',NULL,NULL,1167638400,20,133800.0000,25.0000,8637.5000),(532,'US',NULL,NULL,1167638400,20,203150.0000,28.0000,24412.5000),(533,'US',NULL,NULL,1167638400,20,357000.0000,33.0000,43830.0000),(534,'US',NULL,NULL,1167638400,20,357000.0000,35.0000,94601.0000),(535,'US','OK',NULL,1167638400,10,2750.0000,0.0000,0.0000),(536,'US','OK',NULL,1167638400,10,3750.0000,0.5000,0.0000),(537,'US','OK',NULL,1167638400,10,5250.0000,1.0000,5.0000),(538,'US','OK',NULL,1167638400,10,6500.0000,2.0000,20.0000),(539,'US','OK',NULL,1167638400,10,7650.0000,3.0000,45.0000),(540,'US','OK',NULL,1167638400,10,9950.0000,4.0000,79.5000),(541,'US','OK',NULL,1167638400,10,11450.0000,5.0000,171.5000),(542,'US','OK',NULL,1167638400,10,11450.0000,5.6500,246.5000),(543,'US','OK',NULL,1167638400,20,5500.0000,0.0000,0.0000),(544,'US','OK',NULL,1167638400,20,7500.0000,0.5000,0.0000),(545,'US','OK',NULL,1167638400,20,10500.0000,1.0000,10.0000),(546,'US','OK',NULL,1167638400,20,13000.0000,2.0000,40.0000),(547,'US','OK',NULL,1167638400,20,15300.0000,3.0000,90.0000),(548,'US','OK',NULL,1167638400,20,17700.0000,4.0000,159.0000),(549,'US','OK',NULL,1167638400,20,20500.0000,5.0000,255.0000),(550,'US','OK',NULL,1167638400,20,20500.0000,5.6500,395.0000),(551,'US','UT',NULL,1167638400,10,2630.0000,0.0000,0.0000),(552,'US','UT',NULL,1167638400,10,3630.0000,2.3000,0.0000),(553,'US','UT',NULL,1167638400,10,4630.0000,3.1000,23.0000),(554,'US','UT',NULL,1167638400,10,5630.0000,4.0000,54.0000),(555,'US','UT',NULL,1167638400,10,6630.0000,4.9000,94.0000),(556,'US','UT',NULL,1167638400,10,8130.0000,5.7000,143.0000),(557,'US','UT',NULL,1167638400,10,8130.0000,6.5000,229.0000),(558,'US','UT',NULL,1167638400,20,2630.0000,0.0000,0.0000),(559,'US','UT',NULL,1167638400,20,4630.0000,2.3000,0.0000),(560,'US','UT',NULL,1167638400,20,6630.0000,3.1000,46.0000),(561,'US','UT',NULL,1167638400,20,8630.0000,4.0000,108.0000),(562,'US','UT',NULL,1167638400,20,10630.0000,4.9000,188.0000),(563,'US','UT',NULL,1167638400,20,13630.0000,5.7000,286.0000),(564,'US','UT',NULL,1167638400,20,13630.0000,6.5000,457.0000),(565,'US','NM',NULL,1167638400,10,1900.0000,0.0000,0.0000),(566,'US','NM',NULL,1167638400,10,7400.0000,1.7000,0.0000),(567,'US','NM',NULL,1167638400,10,12900.0000,3.2000,93.5000),(568,'US','NM',NULL,1167638400,10,17900.0000,4.7000,269.5000),(569,'US','NM',NULL,1167638400,10,17900.0000,5.3000,504.5000),(570,'US','NM',NULL,1167638400,20,7250.0000,0.0000,0.0000),(571,'US','NM',NULL,1167638400,20,15250.0000,1.7000,0.0000),(572,'US','NM',NULL,1167638400,20,23250.0000,3.2000,136.0000),(573,'US','NM',NULL,1167638400,20,31250.0000,4.7000,392.0000),(574,'US','NM',NULL,1167638400,20,31250.0000,5.3000,768.0000),(575,'US','NE',NULL,1167638400,10,2200.0000,0.0000,0.0000),(576,'US','NE',NULL,1167638400,10,4400.0000,2.4300,0.0000),(577,'US','NE',NULL,1167638400,10,15500.0000,3.3800,53.4600),(578,'US','NE',NULL,1167638400,10,22750.0000,5.1900,428.6400),(579,'US','NE',NULL,1167638400,10,28100.0000,6.4100,804.9200),(580,'US','NE',NULL,1167638400,10,54100.0000,6.8100,1147.8600),(581,'US','NE',NULL,1167638400,10,75100.0000,7.0400,2918.4600),(582,'US','NE',NULL,1167638400,10,75100.0000,7.1800,4396.8600),(583,'US','NE',NULL,1167638400,20,5250.0000,0.0000,0.0000),(584,'US','NE',NULL,1167638400,20,8250.0000,2.4300,0.0000),(585,'US','NE',NULL,1167638400,20,22400.0000,3.3800,72.9000),(586,'US','NE',NULL,1167638400,20,35400.0000,5.1900,551.1700),(587,'US','NE',NULL,1167638400,20,42950.0000,6.4100,1225.8700),(588,'US','NE',NULL,1167638400,20,58250.0000,6.8100,1709.8300),(589,'US','NE',NULL,1167638400,20,75250.0000,7.0400,2751.7600),(590,'US','NE',NULL,1167638400,20,75250.0000,7.1800,3948.5600),(591,'US','MN',NULL,1167638400,10,1950.0000,0.0000,0.0000),(592,'US','MN',NULL,1167638400,10,23260.0000,5.3500,0.0000),(593,'US','MN',NULL,1167638400,10,71940.0000,7.0500,1140.0900),(594,'US','MN',NULL,1167638400,10,71940.0000,7.8500,4572.0300),(595,'US','MN',NULL,1167638400,20,7300.0000,0.0000,0.0000),(596,'US','MN',NULL,1167638400,20,38450.0000,5.3500,0.0000),(597,'US','MN',NULL,1167638400,20,131050.0000,7.0500,1666.5300),(598,'US','MN',NULL,1167638400,20,131050.0000,7.8500,8194.8300),(599,'US','HI',NULL,1167638400,10,2400.0000,1.4000,0.0000),(600,'US','HI',NULL,1167638400,10,4800.0000,3.2000,34.0000),(601,'US','HI',NULL,1167638400,10,9600.0000,5.5000,110.0000),(602,'US','HI',NULL,1167638400,10,14400.0000,6.4000,374.0000),(603,'US','HI',NULL,1167638400,10,19200.0000,6.8000,682.0000),(604,'US','HI',NULL,1167638400,10,24000.0000,7.2000,1008.0000),(605,'US','HI',NULL,1167638400,10,24000.0000,7.6000,1354.0000),(606,'US','HI',NULL,1167638400,20,4800.0000,1.4000,0.0000),(607,'US','HI',NULL,1167638400,20,9600.0000,3.2000,67.0000),(608,'US','HI',NULL,1167638400,20,19200.0000,5.5000,221.0000),(609,'US','HI',NULL,1167638400,20,28800.0000,6.4000,749.0000),(610,'US','HI',NULL,1167638400,20,38400.0000,6.8000,1363.0000),(611,'US','HI',NULL,1167638400,20,48000.0000,7.2000,2016.0000),(612,'US','HI',NULL,1167638400,20,48000.0000,7.6000,2707.0000),(613,'US','CO',NULL,1167638400,10,1900.0000,0.0000,0.0000),(614,'US','CO',NULL,1167638400,10,1900.0000,4.6300,0.0000),(615,'US','CO',NULL,1167638400,20,7200.0000,0.0000,0.0000),(616,'US','CO',NULL,1167638400,20,7200.0000,4.6300,0.0000),(617,'US','CA',NULL,1167638400,10,6622.0000,1.0000,0.0000),(618,'US','CA',NULL,1167638400,10,15698.0000,2.0000,66.2200),(619,'US','CA',NULL,1167638400,10,24776.0000,4.0000,247.7400),(620,'US','CA',NULL,1167638400,10,34394.0000,6.0000,610.8600),(621,'US','CA',NULL,1167638400,10,43467.0000,8.0000,1187.9400),(622,'US','CA',NULL,1167638400,10,999999.0000,9.3000,1913.7800),(623,'US','CA',NULL,1167638400,10,999999.0000,10.3000,90871.2600),(624,'US','CA',NULL,1167638400,20,6622.0000,1.0000,0.0000),(625,'US','CA',NULL,1167638400,20,15698.0000,2.0000,66.2200),(626,'US','CA',NULL,1167638400,20,24776.0000,4.0000,247.7400),(627,'US','CA',NULL,1167638400,20,34394.0000,6.0000,610.8600),(628,'US','CA',NULL,1167638400,20,43467.0000,8.0000,1187.9400),(629,'US','CA',NULL,1167638400,20,999999.0000,9.3000,1913.7800),(630,'US','CA',NULL,1167638400,20,999999.0000,10.3000,90871.2600),(631,'US','CA',NULL,1167638400,30,13244.0000,1.0000,0.0000),(632,'US','CA',NULL,1167638400,30,31396.0000,2.0000,132.4400),(633,'US','CA',NULL,1167638400,30,49552.0000,4.0000,495.4800),(634,'US','CA',NULL,1167638400,30,68788.0000,6.0000,1221.7200),(635,'US','CA',NULL,1167638400,30,86934.0000,8.0000,2375.8800),(636,'US','CA',NULL,1167638400,30,999999.0000,9.3000,3827.5600),(637,'US','CA',NULL,1167638400,30,999999.0000,10.3000,88742.6100),(638,'US','CA',NULL,1167638400,40,13251.0000,1.0000,0.0000),(639,'US','CA',NULL,1167638400,40,31397.0000,2.0000,132.5100),(640,'US','CA',NULL,1167638400,40,40473.0000,4.0000,495.4300),(641,'US','CA',NULL,1167638400,40,50090.0000,6.0000,858.4700),(642,'US','CA',NULL,1167638400,40,59166.0000,8.0000,1435.4900),(643,'US','CA',NULL,1167638400,40,999999.0000,9.3000,2161.5700),(644,'US','CA',NULL,1167638400,40,999999.0000,10.3000,89659.0400),(645,'US','NC',NULL,1167638400,10,12750.0000,6.0000,0.0000),(646,'US','NC',NULL,1167638400,10,60000.0000,7.0000,127.5000),(647,'US','NC',NULL,1167638400,10,120000.0000,7.7500,577.5000),(648,'US','NC',NULL,1167638400,10,120000.0000,8.0000,877.5000),(649,'US','NC',NULL,1167638400,20,10625.0000,6.0000,0.0000),(650,'US','NC',NULL,1167638400,20,50000.0000,7.0000,106.2500),(651,'US','NC',NULL,1167638400,20,100000.0000,7.7500,481.2500),(652,'US','NC',NULL,1167638400,20,100000.0000,8.0000,731.2500),(653,'US','NC',NULL,1167638400,30,17000.0000,6.0000,0.0000),(654,'US','NC',NULL,1167638400,30,80000.0000,7.0000,170.0000),(655,'US','NC',NULL,1167638400,30,160000.0000,7.7500,770.0000),(656,'US','NC',NULL,1167638400,30,160000.0000,8.0000,1170.0000),(657,'US','ND',NULL,1167638400,10,3600.0000,0.0000,0.0000),(658,'US','ND',NULL,1167638400,10,33800.0000,2.1000,0.0000),(659,'US','ND',NULL,1167638400,10,71200.0000,3.9200,634.2000),(660,'US','ND',NULL,1167638400,10,162600.0000,4.3400,2100.2800),(661,'US','ND',NULL,1167638400,10,351200.0000,5.0400,6067.0400),(662,'US','ND',NULL,1167638400,10,351200.0000,5.5400,15572.4800),(663,'US','ND',NULL,1167638400,20,8800.0000,0.0000,0.0000),(664,'US','ND',NULL,1167638400,20,60200.0000,2.1000,0.0000),(665,'US','ND',NULL,1167638400,20,114300.0000,3.9200,1079.4000),(666,'US','ND',NULL,1167638400,20,203600.0000,4.3400,3200.1200),(667,'US','ND',NULL,1167638400,20,356600.0000,5.0400,7075.7400),(668,'US','ND',NULL,1167638400,20,356600.0000,5.5400,14786.9400),(669,'US','OR',NULL,1167638400,10,2850.0000,5.0000,0.0000),(670,'US','OR',NULL,1167638400,10,7150.0000,7.0000,143.0000),(671,'US','OR',NULL,1167638400,10,7150.0000,9.0000,444.0000),(672,'US','OR',NULL,1167638400,20,5700.0000,5.0000,0.0000),(673,'US','OR',NULL,1167638400,20,14300.0000,7.0000,285.0000),(674,'US','OR',NULL,1167638400,20,14300.0000,9.0000,887.0000),(675,'US','RI',NULL,1167638400,10,2650.0000,0.0000,0.0000),(676,'US','RI',NULL,1167638400,10,33520.0000,3.7500,0.0000),(677,'US','RI',NULL,1167638400,10,77075.0000,7.0000,1157.6300),(678,'US','RI',NULL,1167638400,10,162800.0000,7.7500,4206.4800),(679,'US','RI',NULL,1167638400,10,351650.0000,9.0000,10850.1700),(680,'US','RI',NULL,1167638400,10,351650.0000,9.9000,27846.6700),(681,'US','RI',NULL,1167638400,20,6450.0000,0.0000,0.0000),(682,'US','RI',NULL,1167638400,20,58700.0000,3.7500,0.0000),(683,'US','RI',NULL,1167638400,20,124900.0000,7.0000,1959.3800),(684,'US','RI',NULL,1167638400,20,201300.0000,7.7500,6593.3800),(685,'US','RI',NULL,1167638400,20,355200.0000,9.0000,12514.3800),(686,'US','RI',NULL,1167638400,20,355200.0000,9.9000,26365.3800),(687,'US','VT',NULL,1167638400,10,2650.0000,0.0000,0.0000),(688,'US','VT',NULL,1167638400,10,33520.0000,3.6000,0.0000),(689,'US','VT',NULL,1167638400,10,77075.0000,7.2000,1111.3200),(690,'US','VT',NULL,1167638400,10,162800.0000,8.5000,4247.2800),(691,'US','VT',NULL,1167638400,10,351650.0000,9.0000,11533.9100),(692,'US','VT',NULL,1167638400,10,351650.0000,9.5000,28530.4100),(693,'US','VT',NULL,1167638400,20,8000.0000,0.0000,0.0000),(694,'US','VT',NULL,1167638400,20,58900.0000,3.6000,0.0000),(695,'US','VT',NULL,1167638400,20,133800.0000,7.2000,1832.4000),(696,'US','VT',NULL,1167638400,20,203150.0000,8.5000,7225.2000),(697,'US','VT',NULL,1167638400,20,357000.0000,9.0000,13119.9500),(698,'US','VT',NULL,1167638400,20,357000.0000,9.5000,26966.4500),(702,'US',NULL,NULL,1199174400,10,2650.0000,0.0000,0.0000),(703,'US',NULL,NULL,1199174400,10,10300.0000,10.0000,0.0000),(704,'US',NULL,NULL,1199174400,10,33960.0000,15.0000,765.0000),(705,'US',NULL,NULL,1199174400,10,79725.0000,25.0000,4314.0000),(706,'US',NULL,NULL,1199174400,10,166500.0000,28.0000,15755.2500),(707,'US',NULL,NULL,1199174400,10,359650.0000,33.0000,4052.2500),(708,'US',NULL,NULL,1199174400,10,359650.0000,35.0000,103791.7500),(709,'US',NULL,NULL,1199174400,20,8000.0000,0.0000,0.0000),(710,'US',NULL,NULL,1199174400,20,23550.0000,10.0000,0.0000),(711,'US',NULL,NULL,1199174400,20,72150.0000,15.0000,1555.0000),(712,'US',NULL,NULL,1199174400,20,137850.0000,25.0000,8845.0000),(713,'US',NULL,NULL,1199174400,20,207700.0000,28.0000,25270.0000),(714,'US',NULL,NULL,1199174400,20,365100.0000,33.0000,44828.0000),(715,'US',NULL,NULL,1199174400,20,365100.0000,35.0000,96770.0000),(716,'US','CA',NULL,1199174400,10,6827.0000,1.0000,0.0000),(717,'US','CA',NULL,1199174400,10,16185.0000,2.0000,68.2700),(718,'US','CA',NULL,1199174400,10,25544.0000,4.0000,255.4300),(719,'US','CA',NULL,1199174400,10,35460.0000,6.0000,629.7900),(720,'US','CA',NULL,1199174400,10,44814.0000,8.0000,1224.7500),(721,'US','CA',NULL,1199174400,10,999999.0000,9.3000,1973.0700),(722,'US','CA',NULL,1199174400,10,999999.0000,10.3000,90805.2800),(723,'US','CA',NULL,1199174400,20,6827.0000,1.0000,0.0000),(724,'US','CA',NULL,1199174400,20,16185.0000,2.0000,68.2700),(725,'US','CA',NULL,1199174400,20,25544.0000,4.0000,255.4300),(726,'US','CA',NULL,1199174400,20,35460.0000,6.0000,629.7900),(727,'US','CA',NULL,1199174400,20,44814.0000,8.0000,1224.7500),(728,'US','CA',NULL,1199174400,20,999999.0000,9.3000,1973.0700),(729,'US','CA',NULL,1199174400,20,999999.0000,10.3000,90805.2800),(730,'US','CA',NULL,1199174400,30,13654.0000,1.0000,0.0000),(731,'US','CA',NULL,1199174400,30,32370.0000,2.0000,136.5400),(732,'US','CA',NULL,1199174400,30,51088.0000,4.0000,510.8600),(733,'US','CA',NULL,1199174400,30,70920.0000,6.0000,1259.5800),(734,'US','CA',NULL,1199174400,30,89628.0000,8.0000,2449.5000),(735,'US','CA',NULL,1199174400,30,999999.0000,9.3000,3946.1400),(736,'US','CA',NULL,1199174400,30,999999.0000,10.3000,88610.6400),(737,'US','CA',NULL,1199174400,40,13662.0000,1.0000,0.0000),(738,'US','CA',NULL,1199174400,40,32370.0000,2.0000,136.6200),(739,'US','CA',NULL,1199174400,40,41728.0000,4.0000,510.7800),(740,'US','CA',NULL,1199174400,40,51643.0000,6.0000,885.1000),(741,'US','CA',NULL,1199174400,40,61000.0000,8.0000,1480.0000),(742,'US','CA',NULL,1199174400,40,999999.0000,9.3000,2228.5600),(743,'US','CA',NULL,1199174400,40,999999.0000,10.3000,89555.4700),(744,'US','MN',NULL,1199174400,10,1950.0000,0.0000,0.0000),(745,'US','MN',NULL,1199174400,10,23750.0000,5.3500,0.0000),(746,'US','MN',NULL,1199174400,10,73540.0000,7.0500,1166.3000),(747,'US','MN',NULL,1199174400,10,73540.0000,7.8500,4676.5000),(748,'US','MN',NULL,1199174400,20,7400.0000,0.0000,0.0000),(749,'US','MN',NULL,1199174400,20,39260.0000,5.3500,0.0000),(750,'US','MN',NULL,1199174400,20,133980.0000,7.0500,1704.5100),(751,'US','MN',NULL,1199174400,20,133980.0000,7.8500,8382.2700),(752,'US','NE',NULL,1199174400,10,2200.0000,0.0000,0.0000),(753,'US','NE',NULL,1199174400,10,4400.0000,2.3500,0.0000),(754,'US','NE',NULL,1199174400,10,15500.0000,3.2700,51.7000),(755,'US','NE',NULL,1199174400,10,22750.0000,5.0200,414.6700),(756,'US','NE',NULL,1199174400,10,29000.0000,6.2000,778.6200),(757,'US','NE',NULL,1199174400,10,55000.0000,6.5900,1166.1200),(758,'US','NE',NULL,1199174400,10,55000.0000,6.9500,2879.5200),(759,'US','NE',NULL,1199174400,20,6450.0000,0.0000,0.0000),(760,'US','NE',NULL,1199174400,20,9450.0000,2.3500,0.0000),(761,'US','NE',NULL,1199174400,20,23750.0000,3.2700,70.5000),(762,'US','NE',NULL,1199174400,20,37000.0000,5.0200,538.1100),(763,'US','NE',NULL,1199174400,20,46000.0000,6.2000,1203.2600),(764,'US','NE',NULL,1199174400,20,61000.0000,6.5900,1761.2600),(765,'US','NE',NULL,1199174400,20,61000.0000,6.9500,2749.7600),(766,'US','NM',NULL,1199174400,10,1900.0000,0.0000,0.0000),(767,'US','NM',NULL,1199174400,10,7400.0000,1.7000,0.0000),(768,'US','NM',NULL,1199174400,10,12900.0000,3.2000,93.5000),(769,'US','NM',NULL,1199174400,10,17900.0000,4.7000,269.5000),(770,'US','NM',NULL,1199174400,10,17900.0000,4.9000,504.5000),(771,'US','NM',NULL,1199174400,20,7250.0000,0.0000,0.0000),(772,'US','NM',NULL,1199174400,20,15250.0000,1.7000,0.0000),(773,'US','NM',NULL,1199174400,20,23250.0000,3.2000,136.0000),(774,'US','NM',NULL,1199174400,20,31250.0000,4.7000,392.0000),(775,'US','NM',NULL,1199174400,20,31250.0000,4.9000,768.0000),(776,'US','ND',NULL,1199174400,10,3700.0000,0.0000,0.0000),(777,'US','ND',NULL,1199174400,10,34600.0000,2.1000,0.0000),(778,'US','ND',NULL,1199174400,10,72800.0000,3.9200,648.9000),(779,'US','ND',NULL,1199174400,10,166300.0000,4.3400,2146.3400),(780,'US','ND',NULL,1199174400,10,359200.0000,5.0400,6204.2400),(781,'US','ND',NULL,1199174400,10,359200.0000,5.5400,15926.4000),(782,'US','ND',NULL,1199174400,20,9000.0000,0.0000,0.0000),(783,'US','ND',NULL,1199174400,20,61600.0000,2.1000,0.0000),(784,'US','ND',NULL,1199174400,20,116900.0000,3.9200,1104.6000),(785,'US','ND',NULL,1199174400,20,208200.0000,4.3400,3272.3600),(786,'US','ND',NULL,1199174400,20,364700.0000,5.0400,7234.7800),(787,'US','ND',NULL,1199174400,20,364700.0000,5.5400,15122.3800),(788,'US','OH',NULL,1199174400,0,5000.0000,0.6720,0.0000),(789,'US','OH',NULL,1199174400,0,10000.0000,1.3440,33.6000),(790,'US','OH',NULL,1199174400,0,15000.0000,2.6870,100.8000),(791,'US','OH',NULL,1199174400,0,20000.0000,3.3600,235.1500),(792,'US','OH',NULL,1199174400,0,40000.0000,4.0310,403.1500),(793,'US','OH',NULL,1199174400,0,80000.0000,4.7030,1209.3500),(794,'US','OH',NULL,1199174400,0,100000.0000,5.3750,3090.5500),(795,'US','OH',NULL,1199174400,0,100000.0000,6.7180,4165.5500),(796,'US','RI',NULL,1199174400,10,2650.0000,0.0000,0.0000),(797,'US','RI',NULL,1199174400,10,34500.0000,3.7500,0.0000),(798,'US','RI',NULL,1199174400,10,75500.0000,7.0000,1194.3800),(799,'US','RI',NULL,1199174400,10,166500.0000,7.7500,4064.3800),(800,'US','RI',NULL,1199174400,10,359650.0000,9.0000,11116.8800),(801,'US','RI',NULL,1199174400,10,359650.0000,9.9000,28500.3800),(802,'US','RI',NULL,1199174400,20,6450.0000,0.0000,0.0000),(803,'US','RI',NULL,1199174400,20,60000.0000,3.7500,0.0000),(804,'US','RI',NULL,1199174400,20,127750.0000,7.0000,2008.1300),(805,'US','RI',NULL,1199174400,20,205950.0000,7.7500,6750.6300),(806,'US','RI',NULL,1199174400,20,363300.0000,9.0000,12811.1300),(807,'US','RI',NULL,1199174400,20,363300.0000,9.9000,26972.6300),(808,'US','UT',NULL,1199174400,10,6600.0000,0.0000,0.0000),(809,'US','UT',NULL,1199174400,10,8200.0000,1.0000,0.0000),(810,'US','UT',NULL,1199174400,10,11000.0000,2.0000,0.0000),(811,'US','UT',NULL,1199174400,10,14700.0000,3.0000,0.0000),(812,'US','UT',NULL,1199174400,10,21100.0000,4.0000,0.0000),(813,'US','UT',NULL,1199174400,10,39800.0000,5.0000,0.0000),(814,'US','UT',NULL,1199174400,10,39800.0000,5.0000,0.0000),(815,'US','UT',NULL,1199174400,20,13200.0000,0.0000,0.0000),(816,'US','UT',NULL,1199174400,20,16400.0000,1.0000,0.0000),(817,'US','UT',NULL,1199174400,20,22000.0000,2.0000,0.0000),(818,'US','UT',NULL,1199174400,20,29400.0000,3.0000,0.0000),(819,'US','UT',NULL,1199174400,20,42200.0000,4.0000,0.0000),(820,'US','UT',NULL,1199174400,20,79600.0000,5.0000,0.0000),(821,'US','UT',NULL,1199174400,20,79600.0000,5.0000,0.0000),(822,'US','VT',NULL,1199174400,10,2650.0000,0.0000,0.0000),(823,'US','VT',NULL,1199174400,10,33960.0000,3.6000,0.0000),(824,'US','VT',NULL,1199174400,10,79725.0000,7.2000,1127.1600),(825,'US','VT',NULL,1199174400,10,166500.0000,8.5000,4422.2400),(826,'US','VT',NULL,1199174400,10,359650.0000,9.0000,11798.1200),(827,'US','VT',NULL,1199174400,10,359650.0000,9.5000,29181.6200),(828,'US','VT',NULL,1199174400,20,8000.0000,0.0000,0.0000),(829,'US','VT',NULL,1199174400,20,60200.0000,3.6000,0.0000),(830,'US','VT',NULL,1199174400,20,137850.0000,7.2000,1879.2000),(831,'US','VT',NULL,1199174400,20,207700.0000,8.5000,7470.0000),(832,'US','VT',NULL,1199174400,20,365100.0000,9.0000,13407.2500),(833,'US','VT',NULL,1199174400,20,365100.0000,9.5000,27573.2500),(834,'US',NULL,NULL,1230796800,10,2650.0000,0.0000,0.0000),(835,'US',NULL,NULL,1230796800,10,10400.0000,10.0000,0.0000),(836,'US',NULL,NULL,1230796800,10,35400.0000,15.0000,775.0000),(837,'US',NULL,NULL,1230796800,10,84300.0000,25.0000,4525.0000),(838,'US',NULL,NULL,1230796800,10,173600.0000,28.0000,16750.0000),(839,'US',NULL,NULL,1230796800,10,375000.0000,33.0000,41754.0000),(840,'US',NULL,NULL,1230796800,10,375000.0000,35.0000,108216.0000),(841,'US',NULL,NULL,1230796800,20,8000.0000,0.0000,0.0000),(842,'US',NULL,NULL,1230796800,20,23950.0000,10.0000,0.0000),(843,'US',NULL,NULL,1230796800,20,75650.0000,15.0000,1595.0000),(844,'US',NULL,NULL,1230796800,20,144800.0000,25.0000,9350.0000),(845,'US',NULL,NULL,1230796800,20,216600.0000,28.0000,26637.5000),(846,'US',NULL,NULL,1230796800,20,380700.0000,33.0000,46741.5000),(847,'US',NULL,NULL,1230796800,20,380700.0000,35.0000,100894.5000),(848,'US','NC',NULL,1230796800,10,12750.0000,6.0000,0.0000),(849,'US','NC',NULL,1230796800,10,60000.0000,7.0000,127.5000),(850,'US','NC',NULL,1230796800,10,60000.0000,7.7500,577.5000),(851,'US','NC',NULL,1230796800,20,10625.0000,6.0000,0.0000),(852,'US','NC',NULL,1230796800,20,50000.0000,7.0000,106.2500),(853,'US','NC',NULL,1230796800,20,50000.0000,7.7500,481.2500),(854,'US','NC',NULL,1230796800,30,17000.0000,6.0000,0.0000),(855,'US','NC',NULL,1230796800,30,80000.0000,7.0000,170.0000),(856,'US','NC',NULL,1230796800,30,80000.0000,7.7500,770.0000),(857,'US','ND',NULL,1230796800,10,3800.0000,0.0000,0.0000),(858,'US','ND',NULL,1230796800,10,36000.0000,2.1000,0.0000),(859,'US','ND',NULL,1230796800,10,76000.0000,3.9200,676.2000),(860,'US','ND',NULL,1230796800,10,173000.0000,4.3400,2244.2000),(861,'US','ND',NULL,1230796800,10,375000.0000,5.0400,6454.0000),(862,'US','ND',NULL,1230796800,10,375000.0000,5.5400,16634.8000),(863,'US','ND',NULL,1230796800,20,9300.0000,0.0000,0.0000),(864,'US','ND',NULL,1230796800,20,64000.0000,2.1000,0.0000),(865,'US','ND',NULL,1230796800,20,122000.0000,3.9200,1148.7000),(866,'US','ND',NULL,1230796800,20,217000.0000,4.3400,3422.3000),(867,'US','ND',NULL,1230796800,20,380000.0000,5.0400,7545.3000),(868,'US','ND',NULL,1230796800,20,380000.0000,5.5400,15760.5000),(869,'US','MN',NULL,1230796800,10,2050.0000,0.0000,0.0000),(870,'US','MN',NULL,1230796800,10,24780.0000,5.3500,0.0000),(871,'US','MN',NULL,1230796800,10,76700.0000,7.0500,1216.0600),(872,'US','MN',NULL,1230796800,10,76700.0000,7.8500,4876.4200),(873,'US','MN',NULL,1230796800,20,7750.0000,0.0000,0.0000),(874,'US','MN',NULL,1230796800,20,40970.0000,5.3500,0.0000),(875,'US','MN',NULL,1230796800,20,139720.0000,7.0500,1777.2700),(876,'US','MN',NULL,1230796800,20,139720.0000,7.8500,8739.1500),(877,'US','OK',NULL,1230796800,10,4250.0000,0.0000,0.0000),(878,'US','OK',NULL,1230796800,10,5250.0000,0.5000,0.0000),(879,'US','OK',NULL,1230796800,10,6750.0000,1.0000,5.0000),(880,'US','OK',NULL,1230796800,10,8000.0000,2.0000,20.0000),(881,'US','OK',NULL,1230796800,10,9150.0000,3.0000,45.0000),(882,'US','OK',NULL,1230796800,10,11450.0000,4.0000,79.5000),(883,'US','OK',NULL,1230796800,10,12950.0000,5.0000,171.5000),(884,'US','OK',NULL,1230796800,10,12950.0000,5.5000,246.5000),(885,'US','OK',NULL,1230796800,20,8500.0000,0.0000,0.0000),(886,'US','OK',NULL,1230796800,20,10500.0000,0.5000,0.0000),(887,'US','OK',NULL,1230796800,20,13500.0000,1.0000,10.0000),(888,'US','OK',NULL,1230796800,20,16000.0000,2.0000,40.0000),(889,'US','OK',NULL,1230796800,20,18300.0000,3.0000,90.0000),(890,'US','OK',NULL,1230796800,20,20700.0000,4.0000,159.0000),(891,'US','OK',NULL,1230796800,20,23500.0000,5.0000,255.0000),(892,'US','OK',NULL,1230796800,20,23500.0000,5.5000,395.0000),(893,'US','VT',NULL,1230796800,10,2650.0000,0.0000,0.0000),(894,'US','VT',NULL,1230796800,10,35400.0000,3.6000,0.0000),(895,'US','VT',NULL,1230796800,10,84300.0000,7.2000,1179.0000),(896,'US','VT',NULL,1230796800,10,173600.0000,8.5000,4699.8000),(897,'US','VT',NULL,1230796800,10,375000.0000,9.0000,12290.3000),(898,'US','VT',NULL,1230796800,10,375000.0000,9.5000,30416.3000),(899,'US','VT',NULL,1230796800,20,8000.0000,0.0000,0.0000),(900,'US','VT',NULL,1230796800,20,63100.0000,3.6000,0.0000),(901,'US','VT',NULL,1230796800,20,144800.0000,7.2000,1983.6000),(902,'US','VT',NULL,1230796800,20,216600.0000,8.5000,7866.0000),(903,'US','VT',NULL,1230796800,20,380700.0000,9.0000,13969.0000),(904,'US','VT',NULL,1230796800,20,380700.0000,9.5000,28738.0000),(905,'US','ME',NULL,1230796800,10,2850.0000,0.0000,0.0000),(906,'US','ME',NULL,1230796800,10,7900.0000,2.0000,0.0000),(907,'US','ME',NULL,1230796800,10,12900.0000,4.5000,101.0000),(908,'US','ME',NULL,1230796800,10,23000.0000,7.0000,326.0000),(909,'US','ME',NULL,1230796800,10,23000.0000,8.5000,1033.0000),(910,'US','ME',NULL,1230796800,20,6650.0000,0.0000,0.0000),(911,'US','ME',NULL,1230796800,20,16800.0000,2.0000,0.0000),(912,'US','ME',NULL,1230796800,20,26800.0000,4.5000,203.0000),(913,'US','ME',NULL,1230796800,20,47000.0000,7.0000,653.0000),(914,'US','ME',NULL,1230796800,20,47000.0000,8.5000,2067.0000),(915,'US','DC',NULL,1230796800,10,4200.0000,0.0000,0.0000),(916,'US','DC',NULL,1230796800,10,10000.0000,4.0000,0.0000),(917,'US','DC',NULL,1230796800,10,40000.0000,6.0000,232.0000),(918,'US','DC',NULL,1230796800,10,40000.0000,8.5000,2032.0000),(919,'US','DC',NULL,1230796800,20,4200.0000,0.0000,0.0000),(920,'US','DC',NULL,1230796800,20,10000.0000,4.0000,0.0000),(921,'US','DC',NULL,1230796800,20,40000.0000,6.0000,232.0000),(922,'US','DC',NULL,1230796800,20,40000.0000,8.5000,2032.0000),(923,'US','DC',NULL,1230796800,30,2100.0000,0.0000,0.0000),(924,'US','DC',NULL,1230796800,30,10000.0000,4.0000,0.0000),(925,'US','DC',NULL,1230796800,30,40000.0000,6.0000,316.0000),(926,'US','DC',NULL,1230796800,30,40000.0000,8.5000,2116.0000),(927,'US','DC',NULL,1230796800,40,4200.0000,0.0000,0.0000),(928,'US','DC',NULL,1230796800,40,10000.0000,4.0000,0.0000),(929,'US','DC',NULL,1230796800,40,40000.0000,6.0000,232.0000),(930,'US','DC',NULL,1230796800,40,40000.0000,8.5000,2032.0000),(931,'US','RI',NULL,1230796800,10,2650.0000,0.0000,0.0000),(932,'US','RI',NULL,1230796800,10,36000.0000,3.7500,0.0000),(933,'US','RI',NULL,1230796800,10,78700.0000,7.0000,1250.6300),(934,'US','RI',NULL,1230796800,10,173600.0000,7.7500,4239.6300),(935,'US','RI',NULL,1230796800,10,374950.0000,9.0000,11594.3800),(936,'US','RI',NULL,1230796800,10,374950.0000,9.9000,29715.8800),(937,'US','RI',NULL,1230796800,20,6450.0000,0.0000,0.0000),(938,'US','RI',NULL,1230796800,20,62600.0000,3.7500,0.0000),(939,'US','RI',NULL,1230796800,20,133200.0000,7.0000,2105.6300),(940,'US','RI',NULL,1230796800,20,214700.0000,7.7500,7047.6300),(941,'US','RI',NULL,1230796800,20,378800.0000,9.0000,13363.8800),(942,'US','RI',NULL,1230796800,20,378800.0000,9.9000,28132.8800),(943,'US','OH',NULL,1230796800,0,5000.0000,0.6380,0.0000),(944,'US','OH',NULL,1230796800,0,10000.0000,1.2760,31.9000),(945,'US','OH',NULL,1230796800,0,15000.0000,2.5520,95.7000),(946,'US','OH',NULL,1230796800,0,20000.0000,3.1900,223.3000),(947,'US','OH',NULL,1230796800,0,40000.0000,3.8280,382.8000),(948,'US','OH',NULL,1230796800,0,80000.0000,4.4660,1148.4000),(949,'US','OH',NULL,1230796800,0,100000.0000,5.1030,2934.8000),(950,'US','OH',NULL,1230796800,0,100000.0000,6.3790,3955.4000),(951,'US','CA',NULL,1230796800,10,7168.0000,1.0000,0.0000),(952,'US','CA',NULL,1230796800,10,16994.0000,2.0000,71.6800),(953,'US','CA',NULL,1230796800,10,26821.0000,4.0000,268.2000),(954,'US','CA',NULL,1230796800,10,37233.0000,6.0000,661.2800),(955,'US','CA',NULL,1230796800,10,47055.0000,8.0000,1286.0000),(956,'US','CA',NULL,1230796800,10,999999.9999,9.3000,2071.7600),(957,'US','CA',NULL,1230796800,10,999999.9999,10.3000,90695.6500),(958,'US','CA',NULL,1230796800,20,7168.0000,1.0000,0.0000),(959,'US','CA',NULL,1230796800,20,16994.0000,2.0000,71.6800),(960,'US','CA',NULL,1230796800,20,26821.0000,4.0000,268.2000),(961,'US','CA',NULL,1230796800,20,37233.0000,6.0000,661.2800),(962,'US','CA',NULL,1230796800,20,47055.0000,8.0000,1286.0000),(963,'US','CA',NULL,1230796800,20,999999.9999,9.3000,2071.7600),(964,'US','CA',NULL,1230796800,20,999999.9999,10.3000,90695.6500),(965,'US','CA',NULL,1230796800,30,14336.0000,1.0000,0.0000),(966,'US','CA',NULL,1230796800,30,33988.0000,2.0000,143.3600),(967,'US','CA',NULL,1230796800,30,53642.0000,4.0000,536.4000),(968,'US','CA',NULL,1230796800,30,74466.0000,6.0000,1322.5600),(969,'US','CA',NULL,1230796800,30,94110.0000,8.0000,2572.0000),(970,'US','CA',NULL,1230796800,30,999999.9999,9.3000,4143.5200),(971,'US','CA',NULL,1230796800,30,999999.9999,10.3000,88391.2900),(972,'US','CA',NULL,1230796800,40,14345.0000,1.0000,0.0000),(973,'US','CA',NULL,1230796800,40,33989.0000,2.0000,143.4500),(974,'US','CA',NULL,1230796800,40,43814.0000,4.0000,536.3300),(975,'US','CA',NULL,1230796800,40,54225.0000,6.0000,929.3300),(976,'US','CA',NULL,1230796800,40,64050.0000,8.0000,1553.9900),(977,'US','CA',NULL,1230796800,40,999999.9999,9.3000,2339.9900),(978,'US','CA',NULL,1230796800,40,999999.9999,10.3000,89383.3400),(979,'US','NM',NULL,1230796800,10,2050.0000,0.0000,0.0000),(980,'US','NM',NULL,1230796800,10,7550.0000,1.7000,0.0000),(981,'US','NM',NULL,1230796800,10,13050.0000,3.2000,93.5000),(982,'US','NM',NULL,1230796800,10,18050.0000,4.7000,269.5000),(983,'US','NM',NULL,1230796800,10,18050.0000,4.9000,504.5000),(984,'US','NM',NULL,1230796800,20,7750.0000,0.0000,0.0000),(985,'US','NM',NULL,1230796800,20,15750.0000,1.7000,0.0000),(986,'US','NM',NULL,1230796800,20,23750.0000,3.2000,136.0000),(987,'US','NM',NULL,1230796800,20,31750.0000,4.7000,392.0000),(988,'US','NM',NULL,1230796800,20,31750.0000,4.9000,768.0000),(989,'US','ID',NULL,1230796800,10,1950.0000,0.0000,0.0000),(990,'US','ID',NULL,1230796800,10,3222.0000,1.6000,0.0000),(991,'US','ID',NULL,1230796800,10,4494.0000,3.6000,20.0000),(992,'US','ID',NULL,1230796800,10,5766.0000,4.1000,66.0000),(993,'US','ID',NULL,1230796800,10,7038.0000,5.1000,118.0000),(994,'US','ID',NULL,1230796800,10,8310.0000,6.1000,183.0000),(995,'US','ID',NULL,1230796800,10,11490.0000,7.1000,261.0000),(996,'US','ID',NULL,1230796800,10,27391.0000,7.4000,487.0000),(997,'US','ID',NULL,1230796800,10,27391.0000,7.8000,1664.0000),(998,'US','ID',NULL,1230796800,20,7400.0000,0.0000,0.0000),(999,'US','ID',NULL,1230796800,20,9944.0000,1.6000,0.0000),(1000,'US','ID',NULL,1230796800,20,12488.0000,3.6000,41.0000),(1001,'US','ID',NULL,1230796800,20,15032.0000,4.1000,133.0000),(1002,'US','ID',NULL,1230796800,20,17576.0000,5.1000,237.0000),(1003,'US','ID',NULL,1230796800,20,20120.0000,6.1000,367.0000),(1004,'US','ID',NULL,1230796800,20,26480.0000,7.1000,522.0000),(1005,'US','ID',NULL,1230796800,20,58282.0000,7.4000,974.0000),(1006,'US','ID',NULL,1230796800,20,58282.0000,7.8000,3327.0000),(1007,'US',NULL,NULL,1238569200,10,7180.0000,0.0000,0.0000),(1008,'US',NULL,NULL,1238569200,10,10400.0000,10.0000,0.0000),(1009,'US',NULL,NULL,1238569200,10,36200.0000,15.0000,322.0000),(1010,'US',NULL,NULL,1238569200,10,66530.0000,25.0000,4192.0000),(1011,'US',NULL,NULL,1238569200,10,173600.0000,28.0000,11774.5000),(1012,'US',NULL,NULL,1238569200,10,375000.0000,33.0000,41754.1000),(1013,'US',NULL,NULL,1238569200,10,375000.0000,35.0000,108216.1000),(1014,'US',NULL,NULL,1238569200,20,15750.0000,0.0000,0.0000),(1015,'US',NULL,NULL,1238569200,20,24450.0000,10.0000,0.0000),(1016,'US',NULL,NULL,1238569200,20,75650.0000,15.0000,870.0000),(1017,'US',NULL,NULL,1238569200,20,118130.0000,25.0000,8550.0000),(1018,'US',NULL,NULL,1238569200,20,216600.0000,28.0000,19170.0000),(1019,'US',NULL,NULL,1238569200,20,380700.0000,33.0000,46741.6000),(1020,'US',NULL,NULL,1238569200,20,380700.0000,35.0000,100894.6000),(1021,'US','MD',NULL,1199174400,10,1000.0000,2.0000,0.0000),(1022,'US','MD',NULL,1199174400,10,2000.0000,3.0000,20.0000),(1023,'US','MD',NULL,1199174400,10,3000.0000,4.0000,50.0000),(1024,'US','MD',NULL,1199174400,10,150000.0000,4.7500,90.0000),(1025,'US','MD',NULL,1199174400,10,300000.0000,5.0000,7072.5000),(1026,'US','MD',NULL,1199174400,10,500000.0000,5.2500,14572.5000),(1027,'US','MD',NULL,1199174400,10,999999.9999,5.5000,25072.5000),(1028,'US','MD',NULL,1199174400,10,999999.9999,6.2500,52572.5000),(1029,'US','MD',NULL,1199174400,30,1000.0000,2.0000,0.0000),(1030,'US','MD',NULL,1199174400,30,2000.0000,3.0000,20.0000),(1031,'US','MD',NULL,1199174400,30,3000.0000,4.0000,50.0000),(1032,'US','MD',NULL,1199174400,30,150000.0000,4.7500,90.0000),(1033,'US','MD',NULL,1199174400,30,300000.0000,5.0000,7072.5000),(1034,'US','MD',NULL,1199174400,30,500000.0000,5.2500,14572.5000),(1035,'US','MD',NULL,1199174400,30,999999.9999,5.5000,25072.5000),(1036,'US','MD',NULL,1199174400,30,999999.9999,6.2500,52572.5000),(1037,'US','MD',NULL,1199174400,20,1000.0000,2.0000,0.0000),(1038,'US','MD',NULL,1199174400,20,2000.0000,3.0000,20.0000),(1039,'US','MD',NULL,1199174400,20,3000.0000,4.0000,50.0000),(1040,'US','MD',NULL,1199174400,20,200000.0000,4.7500,90.0000),(1041,'US','MD',NULL,1199174400,20,350000.0000,5.0000,9447.5000),(1042,'US','MD',NULL,1199174400,20,500000.0000,5.2500,16947.5000),(1043,'US','MD',NULL,1199174400,20,999999.9999,5.5000,24822.5000),(1044,'US','MD',NULL,1199174400,20,999999.9999,6.2500,52322.5000),(1045,'US','MD',NULL,1199174400,40,1000.0000,2.0000,0.0000),(1046,'US','MD',NULL,1199174400,40,2000.0000,3.0000,20.0000),(1047,'US','MD',NULL,1199174400,40,3000.0000,4.0000,50.0000),(1048,'US','MD',NULL,1199174400,40,200000.0000,4.7500,90.0000),(1049,'US','MD',NULL,1199174400,40,350000.0000,5.0000,9447.5000),(1050,'US','MD',NULL,1199174400,40,500000.0000,5.2500,16947.5000),(1051,'US','MD',NULL,1199174400,40,999999.9999,5.5000,24822.5000),(1052,'US','MD',NULL,1199174400,40,999999.9999,6.2500,52322.5000),(1053,'US','CA',NULL,1241161200,10,7168.0000,1.2500,0.0000),(1054,'US','CA',NULL,1241161200,10,16994.0000,2.2500,89.6000),(1055,'US','CA',NULL,1241161200,10,26821.0000,4.2500,310.6900),(1056,'US','CA',NULL,1241161200,10,37233.0000,6.2500,728.3400),(1057,'US','CA',NULL,1241161200,10,47055.0000,8.2500,1379.0900),(1058,'US','CA',NULL,1241161200,10,999999.9999,9.5500,2189.4100),(1059,'US','CA',NULL,1241161200,10,999999.9999,10.5500,93195.6600),(1060,'US','CA',NULL,1241161200,20,7168.0000,1.2500,0.0000),(1061,'US','CA',NULL,1241161200,20,16994.0000,2.2500,89.6000),(1062,'US','CA',NULL,1241161200,20,26821.0000,4.2500,310.6900),(1063,'US','CA',NULL,1241161200,20,37233.0000,6.2500,728.3400),(1064,'US','CA',NULL,1241161200,20,47055.0000,8.2500,1379.0900),(1065,'US','CA',NULL,1241161200,20,999999.9999,9.5500,2189.4100),(1066,'US','CA',NULL,1241161200,20,999999.9999,10.5500,93195.6600),(1067,'US','CA',NULL,1241161200,30,14336.0000,1.2500,0.0000),(1068,'US','CA',NULL,1241161200,30,33988.0000,2.2500,179.2000),(1069,'US','CA',NULL,1241161200,30,53642.0000,4.2500,621.3700),(1070,'US','CA',NULL,1241161200,30,74466.0000,6.2500,1456.6700),(1071,'US','CA',NULL,1241161200,30,94110.0000,8.2500,2758.1700),(1072,'US','CA',NULL,1241161200,30,999999.9999,9.5500,4378.8000),(1073,'US','CA',NULL,1241161200,30,999999.9999,10.5500,90891.3000),(1074,'US','CA',NULL,1241161200,40,14345.0000,1.2500,0.0000),(1075,'US','CA',NULL,1241161200,40,33989.0000,2.2500,179.3100),(1076,'US','CA',NULL,1241161200,40,43814.0000,4.2500,621.3000),(1077,'US','CA',NULL,1241161200,40,54225.0000,6.2500,1038.8600),(1078,'US','CA',NULL,1241161200,40,64050.0000,8.2500,1689.5500),(1079,'US','CA',NULL,1241161200,40,999999.9999,9.5500,2500.1100),(1080,'US','CA',NULL,1241161200,40,999999.9999,10.5500,91883.3400),(1081,'US','NY',NULL,1241161200,10,8000.0000,4.0000,0.0000),(1082,'US','NY',NULL,1241161200,10,11000.0000,4.5000,320.0000),(1083,'US','NY',NULL,1241161200,10,13000.0000,5.2500,455.0000),(1084,'US','NY',NULL,1241161200,10,20000.0000,5.9000,560.0000),(1085,'US','NY',NULL,1241161200,10,90000.0000,6.8500,973.0000),(1086,'US','NY',NULL,1241161200,10,100000.0000,7.6400,5768.0000),(1087,'US','NY',NULL,1241161200,10,150000.0000,8.1400,6532.0000),(1088,'US','NY',NULL,1241161200,10,200000.0000,7.3500,10602.0000),(1089,'US','NY',NULL,1241161200,10,300000.0000,8.8500,14277.0000),(1090,'US','NY',NULL,1241161200,10,350000.0000,14.8500,23127.0000),(1091,'US','NY',NULL,1241161200,10,500000.0000,8.8500,30552.0000),(1092,'US','NY',NULL,1241161200,10,550000.0000,27.3300,43827.0000),(1093,'US','NY',NULL,1241161200,10,550000.0000,11.0300,57492.0000),(1094,'US','NY',NULL,1241161200,20,8000.0000,4.0000,0.0000),(1095,'US','NY',NULL,1241161200,20,11000.0000,4.5000,320.0000),(1096,'US','NY',NULL,1241161200,20,13000.0000,5.2500,455.0000),(1097,'US','NY',NULL,1241161200,20,20000.0000,5.9000,560.0000),(1098,'US','NY',NULL,1241161200,20,90000.0000,6.8500,973.0000),(1099,'US','NY',NULL,1241161200,20,100000.0000,7.6400,5768.0000),(1100,'US','NY',NULL,1241161200,20,150000.0000,8.1400,6532.0000),(1101,'US','NY',NULL,1241161200,20,300000.0000,7.3500,10602.0000),(1102,'US','NY',NULL,1241161200,20,350000.0000,17.8500,21627.0000),(1103,'US','NY',NULL,1241161200,20,500000.0000,8.8500,30552.0000),(1104,'US','NY',NULL,1241161200,20,550000.0000,27.3300,43827.0000),(1105,'US','NY',NULL,1241161200,20,550000.0000,11.0300,57492.0000),(1106,'US','NY','YONKERS',1241161200,10,8000.0000,4.0000,0.0000),(1107,'US','NY','YONKERS',1241161200,10,11000.0000,4.5000,320.0000),(1108,'US','NY','YONKERS',1241161200,10,13000.0000,5.2500,455.0000),(1109,'US','NY','YONKERS',1241161200,10,20000.0000,5.9000,560.0000),(1110,'US','NY','YONKERS',1241161200,10,90000.0000,6.8500,973.0000),(1111,'US','NY','YONKERS',1241161200,10,100000.0000,7.6400,5768.0000),(1112,'US','NY','YONKERS',1241161200,10,150000.0000,8.1400,6532.0000),(1113,'US','NY','YONKERS',1241161200,10,200000.0000,7.3500,10602.0000),(1114,'US','NY','YONKERS',1241161200,10,300000.0000,8.8500,14277.0000),(1115,'US','NY','YONKERS',1241161200,10,350000.0000,14.8500,23127.0000),(1116,'US','NY','YONKERS',1241161200,10,500000.0000,8.8500,30552.0000),(1117,'US','NY','YONKERS',1241161200,10,550000.0000,27.3300,43827.0000),(1118,'US','NY','YONKERS',1241161200,10,550000.0000,11.0300,57492.0000),(1119,'US','NY','YONKERS',1241161200,20,8000.0000,4.0000,0.0000),(1120,'US','NY','YONKERS',1241161200,20,11000.0000,4.5000,320.0000),(1121,'US','NY','YONKERS',1241161200,20,13000.0000,5.2500,455.0000),(1122,'US','NY','YONKERS',1241161200,20,20000.0000,5.9000,560.0000),(1123,'US','NY','YONKERS',1241161200,20,90000.0000,6.8500,973.0000),(1124,'US','NY','YONKERS',1241161200,20,100000.0000,7.6400,5768.0000),(1125,'US','NY','YONKERS',1241161200,20,150000.0000,8.1400,6532.0000),(1126,'US','NY','YONKERS',1241161200,20,300000.0000,7.3500,10602.0000),(1127,'US','NY','YONKERS',1241161200,20,350000.0000,17.8500,21627.0000),(1128,'US','NY','YONKERS',1241161200,20,500000.0000,8.8500,30552.0000),(1129,'US','NY','YONKERS',1241161200,20,550000.0000,27.3300,43827.0000),(1130,'US','NY','YONKERS',1241161200,20,550000.0000,11.0300,57492.0000),(1131,'US','CA',NULL,1257058800,10,7168.0000,1.3750,0.0000),(1132,'US','CA',NULL,1257058800,10,16994.0000,2.4750,98.5600),(1133,'US','CA',NULL,1257058800,10,26821.0000,4.6750,341.7500),(1134,'US','CA',NULL,1257058800,10,37233.0000,6.8750,801.1600),(1135,'US','CA',NULL,1257058800,10,47055.0000,9.0750,1516.9900),(1136,'US','CA',NULL,1257058800,10,999999.9999,10.5050,2408.3400),(1137,'US','CA',NULL,1257058800,10,999999.9999,11.6050,102515.2100),(1138,'US','CA',NULL,1257058800,20,7168.0000,1.3750,0.0000),(1139,'US','CA',NULL,1257058800,20,16994.0000,2.4750,98.5600),(1140,'US','CA',NULL,1257058800,20,26821.0000,4.6750,341.7500),(1141,'US','CA',NULL,1257058800,20,37233.0000,6.8750,801.1600),(1142,'US','CA',NULL,1257058800,20,47055.0000,9.0750,1516.9900),(1143,'US','CA',NULL,1257058800,20,999999.9999,10.5050,2408.3400),(1144,'US','CA',NULL,1257058800,20,999999.9999,11.6050,102515.2100),(1145,'US','CA',NULL,1257058800,30,14336.0000,1.3750,0.0000),(1146,'US','CA',NULL,1257058800,30,33988.0000,2.4750,197.1200),(1147,'US','CA',NULL,1257058800,30,53642.0000,4.6750,683.5100),(1148,'US','CA',NULL,1257058800,30,74466.0000,6.8750,1602.3300),(1149,'US','CA',NULL,1257058800,30,94110.0000,9.0750,3033.9800),(1150,'US','CA',NULL,1257058800,30,999999.9999,10.5050,4816.6700),(1151,'US','CA',NULL,1257058800,30,999999.9999,11.6050,99980.4100),(1152,'US','CA',NULL,1257058800,40,14345.0000,1.3750,0.0000),(1153,'US','CA',NULL,1257058800,40,33989.0000,2.4750,197.2400),(1154,'US','CA',NULL,1257058800,40,43814.0000,4.6750,683.4300),(1155,'US','CA',NULL,1257058800,40,54225.0000,6.8750,1142.7500),(1156,'US','CA',NULL,1257058800,40,64050.0000,9.0750,1858.5100),(1157,'US','CA',NULL,1257058800,40,999999.9999,10.5050,2750.1300),(1158,'US','CA',NULL,1257058800,40,999999.9999,11.6050,101071.6800),(1159,'US','CO',NULL,1230796800,10,2050.0000,0.0000,0.0000),(1160,'US','CO',NULL,1230796800,10,2050.0000,4.6300,0.0000),(1161,'US','CO',NULL,1230796800,20,7750.0000,0.0000,0.0000),(1162,'US','CO',NULL,1230796800,20,7750.0000,4.6300,0.0000),(1163,'US','CT',NULL,1230796800,10,10000.0000,3.0000,0.0000),(1164,'US','CT',NULL,1230796800,10,500000.0000,5.0000,300.0000),(1165,'US','CT',NULL,1230796800,10,500000.0000,6.5000,24800.0000),(1166,'US','CT',NULL,1230796800,40,10000.0000,3.0000,0.0000),(1167,'US','CT',NULL,1230796800,40,500000.0000,5.0000,300.0000),(1168,'US','CT',NULL,1230796800,40,500000.0000,6.5000,24800.0000),(1169,'US','CT',NULL,1230796800,60,10000.0000,3.0000,0.0000),(1170,'US','CT',NULL,1230796800,60,500000.0000,5.0000,300.0000),(1171,'US','CT',NULL,1230796800,60,500000.0000,6.5000,24800.0000),(1172,'US','CT',NULL,1230796800,20,16000.0000,3.0000,0.0000),(1173,'US','CT',NULL,1230796800,20,800000.0000,5.0000,480.0000),(1174,'US','CT',NULL,1230796800,20,800000.0000,6.5000,39680.0000),(1175,'US','CT',NULL,1230796800,30,20000.0000,3.0000,0.0000),(1176,'US','CT',NULL,1230796800,30,999999.9999,5.0000,600.0000),(1177,'US','CT',NULL,1230796800,30,999999.9999,6.5000,49600.0000),(1178,'US','HI',NULL,1230796800,10,2400.0000,1.4000,0.0000),(1179,'US','HI',NULL,1230796800,10,4800.0000,3.2000,34.0000),(1180,'US','HI',NULL,1230796800,10,9600.0000,5.5000,110.0000),(1181,'US','HI',NULL,1230796800,10,14400.0000,6.4000,374.0000),(1182,'US','HI',NULL,1230796800,10,19200.0000,6.8000,682.0000),(1183,'US','HI',NULL,1230796800,10,24000.0000,7.2000,1008.0000),(1184,'US','HI',NULL,1230796800,10,36000.0000,7.6000,1354.0000),(1185,'US','HI',NULL,1230796800,10,36000.0000,7.9000,2266.0000),(1186,'US','HI',NULL,1230796800,20,4800.0000,1.4000,0.0000),(1187,'US','HI',NULL,1230796800,20,9600.0000,3.2000,67.0000),(1188,'US','HI',NULL,1230796800,20,19200.0000,5.5000,221.0000),(1189,'US','HI',NULL,1230796800,20,28800.0000,6.4000,749.0000),(1190,'US','HI',NULL,1230796800,20,38400.0000,6.8000,1363.0000),(1191,'US','HI',NULL,1230796800,20,48000.0000,7.2000,2016.0000),(1192,'US','HI',NULL,1230796800,20,72000.0000,7.6000,2707.0000),(1193,'US','HI',NULL,1230796800,20,72000.0000,7.9000,4531.0000),(1194,'US','MD',NULL,1230796800,10,150000.0000,4.7500,0.0000),(1195,'US','MD',NULL,1230796800,10,300000.0000,5.0000,7125.0000),(1196,'US','MD',NULL,1230796800,10,500000.0000,5.2500,14625.0000),(1197,'US','MD',NULL,1230796800,10,999999.9999,5.5000,25125.0000),(1198,'US','MD',NULL,1230796800,10,999999.9999,6.2500,52625.0000),(1199,'US','MD',NULL,1230796800,30,150000.0000,4.7500,0.0000),(1200,'US','MD',NULL,1230796800,30,300000.0000,5.0000,7125.0000),(1201,'US','MD',NULL,1230796800,30,500000.0000,5.2500,14625.0000),(1202,'US','MD',NULL,1230796800,30,999999.9999,5.5000,25125.0000),(1203,'US','MD',NULL,1230796800,30,999999.9999,6.2500,52625.0000),(1204,'US','MD',NULL,1230796800,20,200000.0000,4.7500,0.0000),(1205,'US','MD',NULL,1230796800,20,350000.0000,5.0000,9500.0000),(1206,'US','MD',NULL,1230796800,20,500000.0000,5.2500,19500.0000),(1207,'US','MD',NULL,1230796800,20,999999.9999,5.5000,30000.0000),(1208,'US','MD',NULL,1230796800,20,999999.9999,6.2500,57500.0000),(1209,'US','MD',NULL,1230796800,40,200000.0000,4.7500,0.0000),(1210,'US','MD',NULL,1230796800,40,350000.0000,5.0000,9500.0000),(1211,'US','MD',NULL,1230796800,40,500000.0000,5.2500,19500.0000),(1212,'US','MD',NULL,1230796800,40,999999.9999,5.5000,30000.0000),(1213,'US','MD',NULL,1230796800,40,999999.9999,6.2500,57500.0000),(1214,'US','LA',NULL,1246431600,10,12500.0000,2.1000,0.0000),(1215,'US','LA',NULL,1246431600,10,50000.0000,3.7000,262.5000),(1216,'US','LA',NULL,1246431600,10,50000.0000,5.0500,1650.0000),(1217,'US','LA',NULL,1246431600,20,25000.0000,2.1000,0.0000),(1218,'US','LA',NULL,1246431600,20,100000.0000,3.7500,525.0000),(1219,'US','LA',NULL,1246431600,20,100000.0000,5.1000,3337.5000),(1220,'US',NULL,NULL,1262332800,10,6050.0000,0.0000,0.0000),(1221,'US',NULL,NULL,1262332800,10,10425.0000,10.0000,0.0000),(1222,'US',NULL,NULL,1262332800,10,36050.0000,15.0000,437.5000),(1223,'US',NULL,NULL,1262332800,10,67700.0000,25.0000,4281.2500),(1224,'US',NULL,NULL,1262332800,10,84450.0000,27.0000,12193.7500),(1225,'US',NULL,NULL,1262332800,10,87700.0000,30.0000,16716.2500),(1226,'US',NULL,NULL,1262332800,10,173900.0000,28.0000,17691.2500),(1227,'US',NULL,NULL,1262332800,10,375700.0000,33.0000,41827.2500),(1228,'US',NULL,NULL,1262332800,10,375700.0000,35.0000,108421.2500),(1229,'US',NULL,NULL,1262332800,20,13750.0000,0.0000,0.0000),(1230,'US',NULL,NULL,1262332800,20,24500.0000,10.0000,0.0000),(1231,'US',NULL,NULL,1262332800,20,75750.0000,15.0000,1075.0000),(1232,'US',NULL,NULL,1262332800,20,94050.0000,25.0000,8762.5000),(1233,'US',NULL,NULL,1262332800,20,124050.0000,27.0000,13337.5000),(1234,'US',NULL,NULL,1262332800,20,145050.0000,25.0000,21437.5000),(1235,'US',NULL,NULL,1262332800,20,217000.0000,28.0000,26687.5000),(1236,'US',NULL,NULL,1262332800,20,381400.0000,33.0000,46833.5000),(1237,'US',NULL,NULL,1262332800,20,381400.0000,35.0000,101085.5000),(1238,'US','CA',NULL,1262332800,10,7060.0000,1.3750,0.0000),(1239,'US','CA',NULL,1262332800,10,16739.0000,2.4750,97.0800),(1240,'US','CA',NULL,1262332800,10,26419.0000,4.6750,336.6400),(1241,'US','CA',NULL,1262332800,10,36675.0000,6.8750,789.1800),(1242,'US','CA',NULL,1262332800,10,46349.0000,9.0750,1494.2800),(1243,'US','CA',NULL,1262332800,10,999999.9999,10.5050,2372.2000),(1244,'US','CA',NULL,1262332800,10,999999.9999,11.6050,102553.2400),(1245,'US','CA',NULL,1262332800,20,7060.0000,1.3750,0.0000),(1246,'US','CA',NULL,1262332800,20,16739.0000,2.4750,97.0800),(1247,'US','CA',NULL,1262332800,20,26419.0000,4.6750,336.6400),(1248,'US','CA',NULL,1262332800,20,36675.0000,6.8750,789.1800),(1249,'US','CA',NULL,1262332800,20,46349.0000,9.0750,1494.2800),(1250,'US','CA',NULL,1262332800,20,999999.9999,10.5050,2372.2000),(1251,'US','CA',NULL,1262332800,20,999999.9999,11.6050,102553.2400),(1252,'US','CA',NULL,1262332800,30,14120.0000,1.3750,0.0000),(1253,'US','CA',NULL,1262332800,30,33478.0000,2.4750,194.1500),(1254,'US','CA',NULL,1262332800,30,52838.0000,4.6750,673.2600),(1255,'US','CA',NULL,1262332800,30,73350.0000,6.8750,1578.3400),(1256,'US','CA',NULL,1262332800,30,92698.0000,9.0750,2988.5400),(1257,'US','CA',NULL,1262332800,30,999999.9999,10.5050,4744.3700),(1258,'US','CA',NULL,1262332800,30,999999.9999,11.6050,100056.4500),(1259,'US','CA',NULL,1262332800,40,14130.0000,1.3750,0.0000),(1260,'US','CA',NULL,1262332800,40,33479.0000,2.4750,194.2900),(1261,'US','CA',NULL,1262332800,40,43157.0000,4.6750,673.1800),(1262,'US','CA',NULL,1262332800,40,53412.0000,6.8750,1125.6300),(1263,'US','CA',NULL,1262332800,40,63089.0000,9.0750,1830.6600),(1264,'US','CA',NULL,1262332800,40,999999.9999,10.5050,2708.8500),(1265,'US','CA',NULL,1262332800,40,999999.9999,11.6050,101131.3500),(1266,'US','DE',NULL,1262332800,0,2000.0000,0.0000,0.0000),(1267,'US','DE',NULL,1262332800,0,5000.0000,2.2000,0.0000),(1268,'US','DE',NULL,1262332800,0,10000.0000,3.9000,66.0000),(1269,'US','DE',NULL,1262332800,0,20000.0000,4.8000,261.0000),(1270,'US','DE',NULL,1262332800,0,25000.0000,5.2000,741.0000),(1271,'US','DE',NULL,1262332800,0,60000.0000,5.5500,1001.0000),(1272,'US','DE',NULL,1262332800,0,60000.0000,6.9500,2943.5000),(1273,'US','ME',NULL,1262332800,10,2850.0000,0.0000,0.0000),(1274,'US','ME',NULL,1262332800,10,7800.0000,2.0000,0.0000),(1275,'US','ME',NULL,1262332800,10,12700.0000,4.5000,99.0000),(1276,'US','ME',NULL,1262332800,10,22600.0000,7.0000,320.0000),(1277,'US','ME',NULL,1262332800,10,22600.0000,8.5000,1013.0000),(1278,'US','ME',NULL,1262332800,20,6700.0000,0.0000,0.0000),(1279,'US','ME',NULL,1262332800,20,16650.0000,2.0000,0.0000),(1280,'US','ME',NULL,1262332800,20,26450.0000,4.5000,199.0000),(1281,'US','ME',NULL,1262332800,20,46250.0000,7.0000,640.0000),(1282,'US','ME',NULL,1262332800,20,46250.0000,8.5000,2026.0000),(1283,'US','MN',NULL,1262332800,10,2050.0000,0.0000,0.0000),(1284,'US','MN',NULL,1262332800,10,24820.0000,5.3500,0.0000),(1285,'US','MN',NULL,1262332800,10,76830.0000,7.0500,1218.2000),(1286,'US','MN',NULL,1262332800,10,76830.0000,7.8500,4884.9100),(1287,'US','MN',NULL,1262332800,20,7750.0000,0.0000,0.0000),(1288,'US','MN',NULL,1262332800,20,41030.0000,5.3500,0.0000),(1289,'US','MN',NULL,1262332800,20,139970.0000,7.0500,1780.4800),(1290,'US','MN',NULL,1262332800,20,139970.0000,7.8500,8755.7500),(1291,'US','NY',NULL,1262332800,10,8000.0000,4.0000,0.0000),(1292,'US','NY',NULL,1262332800,10,11000.0000,4.5000,320.0000),(1293,'US','NY',NULL,1262332800,10,13000.0000,5.2500,455.0000),(1294,'US','NY',NULL,1262332800,10,20000.0000,5.9000,560.0000),(1295,'US','NY',NULL,1262332800,10,90000.0000,6.8500,973.0000),(1296,'US','NY',NULL,1262332800,10,100000.0000,7.6400,5768.0000),(1297,'US','NY',NULL,1262332800,10,150000.0000,8.1400,6532.0000),(1298,'US','NY',NULL,1262332800,10,200000.0000,7.3500,10602.0000),(1299,'US','NY',NULL,1262332800,10,300000.0000,8.3500,14277.0000),(1300,'US','NY',NULL,1262332800,10,350000.0000,12.3500,22627.0000),(1301,'US','NY',NULL,1262332800,10,500000.0000,8.3500,28802.0000),(1302,'US','NY',NULL,1262332800,10,550000.0000,20.6700,41327.0000),(1303,'US','NY',NULL,1262332800,10,550000.0000,9.7700,51662.0000),(1304,'US','NY',NULL,1262332800,20,8000.0000,4.0000,0.0000),(1305,'US','NY',NULL,1262332800,20,11000.0000,4.5000,320.0000),(1306,'US','NY',NULL,1262332800,20,13000.0000,5.2500,455.0000),(1307,'US','NY',NULL,1262332800,20,20000.0000,5.9000,560.0000),(1308,'US','NY',NULL,1262332800,20,90000.0000,6.8500,973.0000),(1309,'US','NY',NULL,1262332800,20,100000.0000,7.6400,5768.0000),(1310,'US','NY',NULL,1262332800,20,150000.0000,8.1400,6532.0000),(1311,'US','NY',NULL,1262332800,20,300000.0000,7.3500,10602.0000),(1312,'US','NY',NULL,1262332800,20,350000.0000,14.3500,21627.0000),(1313,'US','NY',NULL,1262332800,20,500000.0000,8.3500,28802.0000),(1314,'US','NY',NULL,1262332800,20,550000.0000,20.6700,41327.0000),(1315,'US','NY',NULL,1262332800,20,550000.0000,9.7700,51662.0000),(1316,'US','NY','YONKERS',1262332800,10,8000.0000,4.0000,0.0000),(1317,'US','NY','YONKERS',1262332800,10,11000.0000,4.5000,320.0000),(1318,'US','NY','YONKERS',1262332800,10,13000.0000,5.2500,455.0000),(1319,'US','NY','YONKERS',1262332800,10,20000.0000,5.9000,560.0000),(1320,'US','NY','YONKERS',1262332800,10,90000.0000,6.8500,973.0000),(1321,'US','NY','YONKERS',1262332800,10,100000.0000,7.6400,5768.0000),(1322,'US','NY','YONKERS',1262332800,10,150000.0000,8.1400,6532.0000),(1323,'US','NY','YONKERS',1262332800,10,200000.0000,7.3500,10602.0000),(1324,'US','NY','YONKERS',1262332800,10,300000.0000,8.3500,14277.0000),(1325,'US','NY','YONKERS',1262332800,10,350000.0000,12.3500,22627.0000),(1326,'US','NY','YONKERS',1262332800,10,500000.0000,8.3500,28802.0000),(1327,'US','NY','YONKERS',1262332800,10,550000.0000,20.6700,41327.0000),(1328,'US','NY','YONKERS',1262332800,10,550000.0000,9.7700,51662.0000),(1329,'US','NY','YONKERS',1262332800,20,8000.0000,4.0000,0.0000),(1330,'US','NY','YONKERS',1262332800,20,11000.0000,4.5000,320.0000),(1331,'US','NY','YONKERS',1262332800,20,13000.0000,5.2500,455.0000),(1332,'US','NY','YONKERS',1262332800,20,20000.0000,5.9000,560.0000),(1333,'US','NY','YONKERS',1262332800,20,90000.0000,6.8500,973.0000),(1334,'US','NY','YONKERS',1262332800,20,100000.0000,7.6400,5768.0000),(1335,'US','NY','YONKERS',1262332800,20,150000.0000,8.1400,6532.0000),(1336,'US','NY','YONKERS',1262332800,20,300000.0000,7.3500,10602.0000),(1337,'US','NY','YONKERS',1262332800,20,350000.0000,14.3500,21627.0000),(1338,'US','NY','YONKERS',1262332800,20,500000.0000,8.3500,28802.0000),(1339,'US','NY','YONKERS',1262332800,20,550000.0000,20.6700,41327.0000),(1340,'US','NY','YONKERS',1262332800,20,550000.0000,9.7700,51662.0000),(1341,'US','ND',NULL,1262332800,10,3800.0000,0.0000,0.0000),(1342,'US','ND',NULL,1262332800,10,36000.0000,1.8400,0.0000),(1343,'US','ND',NULL,1262332800,10,76000.0000,3.4400,592.4800),(1344,'US','ND',NULL,1262332800,10,173000.0000,3.8100,1968.4800),(1345,'US','ND',NULL,1262332800,10,376000.0000,4.4200,5664.1800),(1346,'US','ND',NULL,1262332800,10,376000.0000,4.8600,14636.7800),(1347,'US','ND',NULL,1262332800,20,9300.0000,0.0000,0.0000),(1348,'US','ND',NULL,1262332800,20,64000.0000,1.8400,0.0000),(1349,'US','ND',NULL,1262332800,20,122000.0000,3.4400,1006.4800),(1350,'US','ND',NULL,1262332800,20,217000.0000,3.8100,3001.6800),(1351,'US','ND',NULL,1262332800,20,381000.0000,4.4200,6621.1800),(1352,'US','ND',NULL,1262332800,20,381000.0000,4.8600,13869.9800),(1353,'US','OK',NULL,1262332800,10,5700.0000,0.0000,0.0000),(1354,'US','OK',NULL,1262332800,10,6700.0000,0.5000,0.0000),(1355,'US','OK',NULL,1262332800,10,8200.0000,1.0000,5.0000),(1356,'US','OK',NULL,1262332800,10,9450.0000,2.0000,20.0000),(1357,'US','OK',NULL,1262332800,10,10600.0000,3.0000,45.0000),(1358,'US','OK',NULL,1262332800,10,12900.0000,4.0000,79.5000),(1359,'US','OK',NULL,1262332800,10,14400.0000,5.0000,171.5000),(1360,'US','OK',NULL,1262332800,10,14400.0000,5.5000,246.5000),(1361,'US','OK',NULL,1262332800,20,11400.0000,0.0000,0.0000),(1362,'US','OK',NULL,1262332800,20,13400.0000,0.5000,0.0000),(1363,'US','OK',NULL,1262332800,20,16400.0000,1.0000,10.0000),(1364,'US','OK',NULL,1262332800,20,18900.0000,2.0000,40.0000),(1365,'US','OK',NULL,1262332800,20,21200.0000,3.0000,90.0000),(1366,'US','OK',NULL,1262332800,20,23600.0000,4.0000,159.0000),(1367,'US','OK',NULL,1262332800,20,26400.0000,5.0000,255.0000),(1368,'US','OK',NULL,1262332800,20,26400.0000,5.5000,395.0000),(1369,'US','RI',NULL,1262332800,10,2650.0000,0.0000,0.0000),(1370,'US','RI',NULL,1262332800,10,36050.0000,3.7500,0.0000),(1371,'US','RI',NULL,1262332800,10,78850.0000,7.0000,1252.5000),(1372,'US','RI',NULL,1262332800,10,173900.0000,7.7500,4248.5000),(1373,'US','RI',NULL,1262332800,10,375650.0000,9.0000,11614.8800),(1374,'US','RI',NULL,1262332800,10,375650.0000,9.9000,29772.3800),(1375,'US','RI',NULL,1262332800,20,6450.0000,0.0000,0.0000),(1376,'US','RI',NULL,1262332800,20,62700.0000,3.7500,0.0000),(1377,'US','RI',NULL,1262332800,20,133450.0000,7.0000,2109.3800),(1378,'US','RI',NULL,1262332800,20,215100.0000,7.7500,7061.8800),(1379,'US','RI',NULL,1262332800,20,379500.0000,9.0000,13389.7500),(1380,'US','RI',NULL,1262332800,20,379500.0000,9.9000,28185.7500),(1381,'US','VT',NULL,1262332800,10,2650.0000,0.0000,0.0000),(1382,'US','VT',NULL,1262332800,10,36050.0000,3.5500,0.0000),(1383,'US','VT',NULL,1262332800,10,84450.0000,6.8000,1185.7000),(1384,'US','VT',NULL,1262332800,10,173900.0000,7.8000,4476.9000),(1385,'US','VT',NULL,1262332800,10,375700.0000,8.8000,11454.0000),(1386,'US','VT',NULL,1262332800,10,375700.0000,8.9500,29212.4000),(1387,'US','VT',NULL,1262332800,20,8000.0000,0.0000,0.0000),(1388,'US','VT',NULL,1262332800,20,63200.0000,3.5500,0.0000),(1389,'US','VT',NULL,1262332800,20,145050.0000,6.8000,1959.6000),(1390,'US','VT',NULL,1262332800,20,217000.0000,7.8000,7525.4000),(1391,'US','VT',NULL,1262332800,20,381400.0000,8.8000,13137.5000),(1392,'US','VT',NULL,1262332800,20,381400.0000,8.9500,27604.7000),(1393,'US','WI',NULL,1262332800,10,4000.0000,0.0000,0.0000),(1394,'US','WI',NULL,1262332800,10,10620.0000,4.6000,0.0000),(1395,'US','WI',NULL,1262332800,10,13602.0000,5.1520,304.5200),(1396,'US','WI',NULL,1262332800,10,22486.0000,6.8880,458.1500),(1397,'US','WI',NULL,1262332800,10,43953.0000,7.2800,1070.0800),(1398,'US','WI',NULL,1262332800,10,149330.0000,6.5000,2632.8800),(1399,'US','WI',NULL,1262332800,10,219200.0000,6.7500,9482.3900),(1400,'US','WI',NULL,1262332800,10,219200.0000,7.7500,14198.6200),(1401,'US','WI',NULL,1262332800,20,5500.0000,0.0000,0.0000),(1402,'US','WI',NULL,1262332800,20,14950.0000,4.6000,0.0000),(1403,'US','WI',NULL,1262332800,20,15375.0000,5.5200,434.7000),(1404,'US','WI',NULL,1262332800,20,23667.0000,7.3800,458.1600),(1405,'US','WI',NULL,1262332800,20,42450.0000,7.8000,1070.1100),(1406,'US','WI',NULL,1262332800,20,149330.0000,6.5000,2535.1800),(1407,'US','WI',NULL,1262332800,20,219200.0000,6.7500,9482.3800),(1408,'US','WI',NULL,1262332800,20,219200.0000,7.7500,14198.6100),(1409,'US','DC',NULL,1262332800,10,4000.0000,0.0000,0.0000),(1410,'US','DC',NULL,1262332800,10,10000.0000,4.0000,0.0000),(1411,'US','DC',NULL,1262332800,10,40000.0000,6.0000,240.0000),(1412,'US','DC',NULL,1262332800,10,40000.0000,8.5000,2040.0000),(1413,'US','DC',NULL,1262332800,20,4000.0000,0.0000,0.0000),(1414,'US','DC',NULL,1262332800,20,10000.0000,4.0000,0.0000),(1415,'US','DC',NULL,1262332800,20,40000.0000,6.0000,240.0000),(1416,'US','DC',NULL,1262332800,20,40000.0000,8.5000,2040.0000),(1417,'US','DC',NULL,1262332800,30,2000.0000,0.0000,0.0000),(1418,'US','DC',NULL,1262332800,30,10000.0000,4.0000,0.0000),(1419,'US','DC',NULL,1262332800,30,40000.0000,6.0000,320.0000),(1420,'US','DC',NULL,1262332800,30,40000.0000,8.5000,2120.0000),(1421,'US','DC',NULL,1262332800,40,4000.0000,0.0000,0.0000),(1422,'US','DC',NULL,1262332800,40,10000.0000,4.0000,0.0000),(1423,'US','DC',NULL,1262332800,40,40000.0000,6.0000,240.0000),(1424,'US','DC',NULL,1262332800,40,40000.0000,8.5000,2040.0000),(1425,'US','NE',NULL,1262332800,10,2400.0000,2.5600,0.0000),(1426,'US','NE',NULL,1262332800,10,17500.0000,3.5700,61.4400),(1427,'US','NE',NULL,1262332800,10,27000.0000,5.1200,600.5100),(1428,'US','NE',NULL,1262332800,10,27000.0000,6.8400,1086.9100),(1429,'US','NE',NULL,1262332800,20,4800.0000,2.5600,0.0000),(1430,'US','NE',NULL,1262332800,20,35000.0000,3.5700,122.8800),(1431,'US','NE',NULL,1262332800,20,54000.0000,5.1200,1201.0200),(1432,'US','NE',NULL,1262332800,20,54000.0000,6.8400,2173.8200),(1433,'US','NE',NULL,1262332800,30,2400.0000,2.5600,0.0000),(1434,'US','NE',NULL,1262332800,30,17500.0000,3.5700,61.4400),(1435,'US','NE',NULL,1262332800,30,27000.0000,5.1200,600.5100),(1436,'US','NE',NULL,1262332800,30,27000.0000,6.8400,1086.9100),(1437,'US','NE',NULL,1262332800,40,4500.0000,2.5600,0.0000),(1438,'US','NE',NULL,1262332800,40,28000.0000,3.5700,115.2000),(1439,'US','NE',NULL,1262332800,40,40000.0000,5.1200,954.1500),(1440,'US','NE',NULL,1262332800,40,40000.0000,6.8400,1568.5500),(1457,'US','AL',NULL,1136102400,10,500.0000,2.0000,0.0000),(1458,'US','AL',NULL,1136102400,10,3000.0000,4.0000,10.0000),(1459,'US','AL',NULL,1136102400,10,3000.0000,5.0000,110.0000),(1460,'US','AL',NULL,1136102400,20,1000.0000,2.0000,0.0000),(1461,'US','AL',NULL,1136102400,20,6000.0000,4.0000,20.0000),(1462,'US','AL',NULL,1136102400,20,6000.0000,5.0000,220.0000),(1463,'US','AL',NULL,1136102400,30,500.0000,2.0000,0.0000),(1464,'US','AL',NULL,1136102400,30,3000.0000,4.0000,10.0000),(1465,'US','AL',NULL,1136102400,30,3000.0000,5.0000,110.0000),(1466,'US','AL',NULL,1136102400,40,500.0000,2.0000,0.0000),(1467,'US','AL',NULL,1136102400,40,3000.0000,4.0000,10.0000),(1468,'US','AL',NULL,1136102400,40,3000.0000,5.0000,110.0000),(1469,'US','AL',NULL,1136102400,50,500.0000,2.0000,0.0000),(1470,'US','AL',NULL,1136102400,50,3000.0000,4.0000,10.0000),(1471,'US','AL',NULL,1136102400,50,3000.0000,5.0000,110.0000),(1472,'US',NULL,NULL,1293868800,10,2100.0000,0.0000,0.0000),(1473,'US',NULL,NULL,1293868800,10,10600.0000,10.0000,0.0000),(1474,'US',NULL,NULL,1293868800,10,36600.0000,15.0000,850.0000),(1475,'US',NULL,NULL,1293868800,10,85700.0000,25.0000,4750.0000),(1476,'US',NULL,NULL,1293868800,10,176500.0000,28.0000,17025.0000),(1477,'US',NULL,NULL,1293868800,10,381250.0000,33.0000,42449.0000),(1478,'US',NULL,NULL,1293868800,10,381250.0000,35.0000,110016.5000),(1479,'US',NULL,NULL,1293868800,20,7900.0000,0.0000,0.0000),(1480,'US',NULL,NULL,1293868800,20,24900.0000,10.0000,0.0000),(1481,'US',NULL,NULL,1293868800,20,76900.0000,15.0000,1700.0000),(1482,'US',NULL,NULL,1293868800,20,147250.0000,25.0000,9500.0000),(1483,'US',NULL,NULL,1293868800,20,220200.0000,28.0000,27087.5000),(1484,'US',NULL,NULL,1293868800,20,387050.0000,33.0000,47513.5000),(1485,'US',NULL,NULL,1293868800,20,387050.0000,35.0000,102574.0000),(1486,'US','CA',NULL,1293868800,10,7124.0000,1.1000,0.0000),(1487,'US','CA',NULL,1293868800,10,16890.0000,2.2000,78.3600),(1488,'US','CA',NULL,1293868800,10,26657.0000,4.4000,293.2100),(1489,'US','CA',NULL,1293868800,10,37005.0000,6.6000,722.9600),(1490,'US','CA',NULL,1293868800,10,46766.0000,8.8000,1405.9300),(1491,'US','CA',NULL,1293868800,10,999999.9999,10.2300,2264.9000),(1492,'US','CA',NULL,1293868800,10,999999.9999,11.3300,99780.7400),(1493,'US','CA',NULL,1293868800,20,7124.0000,1.1000,0.0000),(1494,'US','CA',NULL,1293868800,20,16890.0000,2.2000,78.3600),(1495,'US','CA',NULL,1293868800,20,26657.0000,4.4000,293.2100),(1496,'US','CA',NULL,1293868800,20,37005.0000,6.6000,722.9600),(1497,'US','CA',NULL,1293868800,20,46766.0000,8.8000,1405.9300),(1498,'US','CA',NULL,1293868800,20,999999.9999,10.2300,2264.9000),(1499,'US','CA',NULL,1293868800,20,999999.9999,11.3300,99780.7400),(1500,'US','CA',NULL,1293868800,30,14248.0000,1.1000,0.0000),(1501,'US','CA',NULL,1293868800,30,33780.0000,2.2000,156.7300),(1502,'US','CA',NULL,1293868800,30,53314.0000,4.4000,586.4300),(1503,'US','CA',NULL,1293868800,30,74010.0000,6.6000,1445.9300),(1504,'US','CA',NULL,1293868800,30,93532.0000,8.8000,2811.8700),(1505,'US','CA',NULL,1293868800,30,999999.9999,10.2300,4529.8100),(1506,'US','CA',NULL,1293868800,30,999999.9999,11.3300,97261.4900),(1507,'US','CA',NULL,1293868800,40,14257.0000,1.1000,0.0000),(1508,'US','CA',NULL,1293868800,40,33780.0000,2.2000,156.8300),(1509,'US','CA',NULL,1293868800,40,43545.0000,4.4000,586.3400),(1510,'US','CA',NULL,1293868800,40,53893.0000,6.6000,1016.0000),(1511,'US','CA',NULL,1293868800,40,63657.0000,8.8000,1698.9700),(1512,'US','CA',NULL,1293868800,40,999999.9999,10.2300,2558.2000),(1513,'US','CA',NULL,1293868800,40,999999.9999,11.3300,98346.0900),(1514,'US','CO',NULL,1293868800,10,2100.0000,0.0000,0.0000),(1515,'US','CO',NULL,1293868800,10,2100.0000,4.6300,0.0000),(1516,'US','CO',NULL,1293868800,20,7900.0000,0.0000,0.0000),(1517,'US','CO',NULL,1293868800,20,7900.0000,4.6300,0.0000),(1518,'US','ME',NULL,1293868800,10,2950.0000,0.0000,0.0000),(1519,'US','ME',NULL,1293868800,10,7950.0000,2.0000,0.0000),(1520,'US','ME',NULL,1293868800,10,12900.0000,4.5000,100.0000),(1521,'US','ME',NULL,1293868800,10,22900.0000,7.0000,323.0000),(1522,'US','ME',NULL,1293868800,10,22900.0000,8.5000,1023.0000),(1523,'US','ME',NULL,1293868800,20,6800.0000,0.0000,0.0000),(1524,'US','ME',NULL,1293868800,20,16800.0000,2.0000,0.0000),(1525,'US','ME',NULL,1293868800,20,26750.0000,4.5000,200.0000),(1526,'US','ME',NULL,1293868800,20,46700.0000,7.0000,648.0000),(1527,'US','ME',NULL,1293868800,20,46700.0000,8.5000,2045.0000),(1528,'US','MN',NULL,1293868800,10,2100.0000,0.0000,0.0000),(1529,'US','MN',NULL,1293868800,10,25200.0000,5.3500,0.0000),(1530,'US','MN',NULL,1293868800,10,77990.0000,7.0500,1235.8500),(1531,'US','MN',NULL,1293868800,10,77990.0000,7.8500,4957.5500),(1532,'US','MN',NULL,1293868800,20,5950.0000,0.0000,0.0000),(1533,'US','MN',NULL,1293868800,20,39720.0000,5.3500,0.0000),(1534,'US','MN',NULL,1293868800,20,140120.0000,7.0500,1806.7000),(1535,'US','MN',NULL,1293868800,20,140120.0000,7.8500,8884.9000),(1536,'US','NY','NYC',1293868800,10,8000.0000,1.9000,0.0000),(1537,'US','NY','NYC',1293868800,10,8700.0000,2.6500,152.0000),(1538,'US','NY','NYC',1293868800,10,15000.0000,3.1000,171.0000),(1539,'US','NY','NYC',1293868800,10,25000.0000,3.7000,366.0000),(1540,'US','NY','NYC',1293868800,10,60000.0000,3.9000,736.0000),(1541,'US','NY','NYC',1293868800,10,500000.0000,4.0000,2101.0000),(1542,'US','NY','NYC',1293868800,10,500000.0000,4.2500,19701.0000),(1543,'US','NY','NYC',1293868800,20,8000.0000,1.9000,0.0000),(1544,'US','NY','NYC',1293868800,20,8700.0000,2.6500,152.0000),(1545,'US','NY','NYC',1293868800,20,15000.0000,3.1000,171.0000),(1546,'US','NY','NYC',1293868800,20,25000.0000,3.7000,366.0000),(1547,'US','NY','NYC',1293868800,20,60000.0000,3.9000,736.0000),(1548,'US','NY','NYC',1293868800,20,500000.0000,4.0000,2101.0000),(1549,'US','NY','NYC',1293868800,20,500000.0000,4.2500,19701.0000),(1550,'US','ND',NULL,1293868800,10,3900.0000,0.0000,0.0000),(1551,'US','ND',NULL,1293868800,10,37000.0000,1.8400,0.0000),(1552,'US','ND',NULL,1293868800,10,77000.0000,3.4400,609.0400),(1553,'US','ND',NULL,1293868800,10,176000.0000,3.8100,1985.0400),(1554,'US','ND',NULL,1293868800,10,380000.0000,4.4200,5756.9400),(1555,'US','ND',NULL,1293868800,10,380000.0000,4.8600,14773.7400),(1556,'US','ND',NULL,1293868800,20,9400.0000,0.0000,0.0000),(1557,'US','ND',NULL,1293868800,20,65000.0000,1.8400,0.0000),(1558,'US','ND',NULL,1293868800,20,124000.0000,3.4400,1023.0400),(1559,'US','ND',NULL,1293868800,20,220000.0000,3.8100,3052.6400),(1560,'US','ND',NULL,1293868800,20,386000.0000,4.4200,6710.2400),(1561,'US','ND',NULL,1293868800,20,386000.0000,4.8600,14047.4400),(1562,'US','OK',NULL,1293868800,10,5800.0000,0.0000,0.0000),(1563,'US','OK',NULL,1293868800,10,6800.0000,0.5000,0.0000),(1564,'US','OK',NULL,1293868800,10,8300.0000,1.0000,5.0000),(1565,'US','OK',NULL,1293868800,10,9550.0000,2.0000,20.0000),(1566,'US','OK',NULL,1293868800,10,10700.0000,3.0000,45.0000),(1567,'US','OK',NULL,1293868800,10,13000.0000,4.0000,79.5000),(1568,'US','OK',NULL,1293868800,10,14500.0000,5.0000,171.5000),(1569,'US','OK',NULL,1293868800,10,14500.0000,5.5000,246.5000),(1570,'US','OK',NULL,1293868800,20,11600.0000,0.0000,0.0000),(1571,'US','OK',NULL,1293868800,20,13600.0000,0.5000,0.0000),(1572,'US','OK',NULL,1293868800,20,16600.0000,1.0000,10.0000),(1573,'US','OK',NULL,1293868800,20,19100.0000,2.0000,40.0000),(1574,'US','OK',NULL,1293868800,20,21400.0000,3.0000,90.0000),(1575,'US','OK',NULL,1293868800,20,23800.0000,4.0000,159.0000),(1576,'US','OK',NULL,1293868800,20,26600.0000,5.0000,255.0000),(1577,'US','OK',NULL,1293868800,20,26600.0000,5.5000,395.0000),(1578,'US','RI',NULL,1293868800,10,55000.0000,3.7500,0.0000),(1579,'US','RI',NULL,1293868800,10,125000.0000,4.7500,2063.0000),(1580,'US','RI',NULL,1293868800,10,125000.0000,5.9900,5388.0000),(1581,'US','RI',NULL,1293868800,20,55000.0000,3.7500,0.0000),(1582,'US','RI',NULL,1293868800,20,125000.0000,4.7500,2063.0000),(1583,'US','RI',NULL,1293868800,20,125000.0000,5.9900,5388.0000),(1584,'US',NULL,NULL,1325404800,10,2150.0000,0.0000,0.0000),(1585,'US',NULL,NULL,1325404800,10,10850.0000,10.0000,0.0000),(1586,'US',NULL,NULL,1325404800,10,37500.0000,15.0000,870.0000),(1587,'US',NULL,NULL,1325404800,10,87800.0000,25.0000,4867.5000),(1588,'US',NULL,NULL,1325404800,10,180800.0000,28.0000,17442.5000),(1589,'US',NULL,NULL,1325404800,10,390500.0000,33.0000,43482.5000),(1590,'US',NULL,NULL,1325404800,10,390500.0000,35.0000,112683.5000),(1591,'US',NULL,NULL,1325404800,20,8100.0000,0.0000,0.0000),(1592,'US',NULL,NULL,1325404800,20,25500.0000,10.0000,0.0000),(1593,'US',NULL,NULL,1325404800,20,78800.0000,15.0000,1740.0000),(1594,'US',NULL,NULL,1325404800,20,150800.0000,25.0000,9735.0000),(1595,'US',NULL,NULL,1325404800,20,225550.0000,28.0000,27735.0000),(1596,'US',NULL,NULL,1325404800,20,396450.0000,33.0000,48665.0000),(1597,'US',NULL,NULL,1325404800,20,396450.0000,35.0000,105062.0000),(1598,'US','AL',NULL,1325404800,10,500.0000,2.0000,0.0000),(1599,'US','AL',NULL,1325404800,10,2500.0000,4.0000,10.0000),(1600,'US','AL',NULL,1325404800,10,3000.0000,5.0000,90.0000),(1601,'US','AL',NULL,1325404800,20,1000.0000,2.0000,0.0000),(1602,'US','AL',NULL,1325404800,20,5000.0000,4.0000,20.0000),(1603,'US','AL',NULL,1325404800,20,6000.0000,5.0000,180.0000),(1604,'US','AL',NULL,1325404800,30,500.0000,2.0000,0.0000),(1605,'US','AL',NULL,1325404800,30,2500.0000,4.0000,10.0000),(1606,'US','AL',NULL,1325404800,30,3000.0000,5.0000,90.0000),(1607,'US','AL',NULL,1325404800,40,500.0000,2.0000,0.0000),(1608,'US','AL',NULL,1325404800,40,2500.0000,4.0000,10.0000),(1609,'US','AL',NULL,1325404800,40,3000.0000,5.0000,90.0000),(1610,'US','AL',NULL,1325404800,50,500.0000,2.0000,0.0000),(1611,'US','AL',NULL,1325404800,50,2500.0000,4.0000,10.0000),(1612,'US','AL',NULL,1325404800,50,3000.0000,5.0000,90.0000),(1613,'US','CA',NULL,1325404800,10,7316.0000,1.1000,0.0000),(1614,'US','CA',NULL,1325404800,10,17346.0000,2.2000,80.4800),(1615,'US','CA',NULL,1325404800,10,27377.0000,4.4000,301.1400),(1616,'US','CA',NULL,1325404800,10,38004.0000,6.6000,742.5000),(1617,'US','CA',NULL,1325404800,10,48029.0000,8.8000,1443.8800),(1618,'US','CA',NULL,1325404800,10,999999.9999,10.2300,2326.0800),(1619,'US','CA',NULL,1325404800,10,999999.9999,11.3300,99712.7100),(1620,'US','CA',NULL,1325404800,20,7316.0000,1.1000,0.0000),(1621,'US','CA',NULL,1325404800,20,17346.0000,2.2000,80.4800),(1622,'US','CA',NULL,1325404800,20,27377.0000,4.4000,301.1400),(1623,'US','CA',NULL,1325404800,20,38004.0000,6.6000,742.5000),(1624,'US','CA',NULL,1325404800,20,48029.0000,8.8000,1443.8800),(1625,'US','CA',NULL,1325404800,20,999999.9999,10.2300,2326.0800),(1626,'US','CA',NULL,1325404800,20,999999.9999,11.3300,99712.7100),(1627,'US','CA',NULL,1325404800,30,14632.0000,1.1000,0.0000),(1628,'US','CA',NULL,1325404800,30,34692.0000,2.2000,160.9500),(1629,'US','CA',NULL,1325404800,30,54754.0000,4.4000,602.2700),(1630,'US','CA',NULL,1325404800,30,76008.0000,6.6000,1485.0000),(1631,'US','CA',NULL,1325404800,30,96058.0000,8.8000,2887.7600),(1632,'US','CA',NULL,1325404800,30,999999.9999,10.2300,4652.1600),(1633,'US','CA',NULL,1325404800,30,999999.9999,11.3300,97125.4300),(1634,'US','CA',NULL,1325404800,40,14642.0000,1.1000,0.0000),(1635,'US','CA',NULL,1325404800,40,34692.0000,2.2000,161.0600),(1636,'US','CA',NULL,1325404800,40,44721.0000,4.4000,602.1600),(1637,'US','CA',NULL,1325404800,40,55348.0000,6.6000,1043.4400),(1638,'US','CA',NULL,1325404800,40,65376.0000,8.8000,1744.8200),(1639,'US','CA',NULL,1325404800,40,999999.9999,10.2300,2627.2800),(1640,'US','CA',NULL,1325404800,40,999999.9999,11.3300,98239.3200),(1641,'US','CT',NULL,1325404800,10,10000.0000,3.0000,0.0000),(1642,'US','CT',NULL,1325404800,10,50000.0000,5.0000,300.0000),(1643,'US','CT',NULL,1325404800,10,100000.0000,5.5000,2300.0000),(1644,'US','CT',NULL,1325404800,10,200000.0000,6.0000,5050.0000),(1645,'US','CT',NULL,1325404800,10,250000.0000,6.5000,11050.0000),(1646,'US','CT',NULL,1325404800,10,250000.0000,6.7000,14300.0000),(1647,'US','CT',NULL,1325404800,40,10000.0000,3.0000,0.0000),(1648,'US','CT',NULL,1325404800,40,50000.0000,5.0000,300.0000),(1649,'US','CT',NULL,1325404800,40,100000.0000,5.5000,2300.0000),(1650,'US','CT',NULL,1325404800,40,200000.0000,6.0000,5050.0000),(1651,'US','CT',NULL,1325404800,40,250000.0000,6.5000,11050.0000),(1652,'US','CT',NULL,1325404800,40,250000.0000,6.7000,14300.0000),(1653,'US','CT',NULL,1325404800,60,10000.0000,3.0000,0.0000),(1654,'US','CT',NULL,1325404800,60,50000.0000,5.0000,300.0000),(1655,'US','CT',NULL,1325404800,60,100000.0000,5.5000,2300.0000),(1656,'US','CT',NULL,1325404800,60,200000.0000,6.0000,5050.0000),(1657,'US','CT',NULL,1325404800,60,250000.0000,6.5000,11050.0000),(1658,'US','CT',NULL,1325404800,60,250000.0000,6.7000,14300.0000),(1659,'US','CT',NULL,1325404800,20,16000.0000,3.0000,0.0000),(1660,'US','CT',NULL,1325404800,20,80000.0000,5.0000,480.0000),(1661,'US','CT',NULL,1325404800,20,160000.0000,5.5000,3680.0000),(1662,'US','CT',NULL,1325404800,20,320000.0000,6.0000,8080.0000),(1663,'US','CT',NULL,1325404800,20,400000.0000,6.5000,17680.0000),(1664,'US','CT',NULL,1325404800,20,400000.0000,6.7000,22880.0000),(1665,'US','CT',NULL,1325404800,30,20000.0000,3.0000,0.0000),(1666,'US','CT',NULL,1325404800,30,100000.0000,5.0000,600.0000),(1667,'US','CT',NULL,1325404800,30,200000.0000,5.5000,4600.0000),(1668,'US','CT',NULL,1325404800,30,400000.0000,6.0000,10100.0000),(1669,'US','CT',NULL,1325404800,30,500000.0000,6.5000,22100.0000),(1670,'US','CT',NULL,1325404800,30,500000.0000,6.7000,28600.0000),(1671,'US','DC',NULL,1325404800,10,10000.0000,4.0000,0.0000),(1672,'US','DC',NULL,1325404800,10,40000.0000,6.0000,400.0000),(1673,'US','DC',NULL,1325404800,10,350000.0000,8.5000,2200.0000),(1674,'US','DC',NULL,1325404800,10,350000.0000,8.9500,28550.0000),(1675,'US','DC',NULL,1325404800,20,10000.0000,4.0000,0.0000),(1676,'US','DC',NULL,1325404800,20,40000.0000,6.0000,400.0000),(1677,'US','DC',NULL,1325404800,20,350000.0000,8.5000,2200.0000),(1678,'US','DC',NULL,1325404800,20,350000.0000,8.9500,28550.0000),(1679,'US','DC',NULL,1325404800,30,10000.0000,4.0000,0.0000),(1680,'US','DC',NULL,1325404800,30,40000.0000,6.0000,400.0000),(1681,'US','DC',NULL,1325404800,30,350000.0000,8.5000,2200.0000),(1682,'US','DC',NULL,1325404800,30,350000.0000,8.9500,28550.0000),(1683,'US','DC',NULL,1325404800,40,10000.0000,4.0000,0.0000),(1684,'US','DC',NULL,1325404800,40,40000.0000,6.0000,400.0000),(1685,'US','DC',NULL,1325404800,40,350000.0000,8.5000,2200.0000),(1686,'US','DC',NULL,1325404800,40,350000.0000,8.9500,28550.0000),(1687,'US','ID',NULL,1325404800,10,2100.0000,0.0000,0.0000),(1688,'US','ID',NULL,1325404800,10,3438.0000,1.6000,0.0000),(1689,'US','ID',NULL,1325404800,10,4776.0000,3.6000,21.0000),(1690,'US','ID',NULL,1325404800,10,6114.0000,4.1000,69.0000),(1691,'US','ID',NULL,1325404800,10,7452.0000,5.1000,124.0000),(1692,'US','ID',NULL,1325404800,10,8790.0000,6.1000,192.0000),(1693,'US','ID',NULL,1325404800,10,12135.0000,7.1000,274.0000),(1694,'US','ID',NULL,1325404800,10,28860.0000,7.4000,511.0000),(1695,'US','ID',NULL,1325404800,10,28860.0000,7.8000,1749.0000),(1696,'US','ID',NULL,1325404800,20,7900.0000,0.0000,0.0000),(1697,'US','ID',NULL,1325404800,20,10576.0000,1.6000,0.0000),(1698,'US','ID',NULL,1325404800,20,13252.0000,3.6000,43.0000),(1699,'US','ID',NULL,1325404800,20,15928.0000,4.1000,139.0000),(1700,'US','ID',NULL,1325404800,20,18604.0000,5.1000,249.0000),(1701,'US','ID',NULL,1325404800,20,21280.0000,6.1000,385.0000),(1702,'US','ID',NULL,1325404800,20,27970.0000,7.1000,548.0000),(1703,'US','ID',NULL,1325404800,20,61420.0000,7.4000,1023.0000),(1704,'US','ID',NULL,1325404800,20,61420.0000,7.8000,3498.0000),(1705,'US','ME',NULL,1325404800,10,3100.0000,0.0000,0.0000),(1706,'US','ME',NULL,1325404800,10,8200.0000,2.0000,0.0000),(1707,'US','ME',NULL,1325404800,10,13250.0000,4.5000,102.0000),(1708,'US','ME',NULL,1325404800,10,23450.0000,7.0000,329.0000),(1709,'US','ME',NULL,1325404800,10,23450.0000,8.5000,1043.0000),(1710,'US','ME',NULL,1325404800,20,9050.0000,0.0000,0.0000),(1711,'US','ME',NULL,1325404800,20,19250.0000,2.0000,0.0000),(1712,'US','ME',NULL,1325404800,20,29400.0000,4.5000,204.0000),(1713,'US','ME',NULL,1325404800,20,49750.0000,7.0000,661.0000),(1714,'US','ME',NULL,1325404800,20,49750.0000,8.5000,2085.0000),(1715,'US','MN',NULL,1325404800,10,2150.0000,0.0000,0.0000),(1716,'US','MN',NULL,1325404800,10,25820.0000,5.3500,0.0000),(1717,'US','MN',NULL,1325404800,10,79880.0000,7.0500,1266.3500),(1718,'US','MN',NULL,1325404800,10,79880.0000,7.8500,5077.5800),(1719,'US','MN',NULL,1325404800,20,6100.0000,0.0000,0.0000),(1720,'US','MN',NULL,1325404800,20,40690.0000,5.3500,0.0000),(1721,'US','MN',NULL,1325404800,20,143530.0000,7.0500,1850.5700),(1722,'US','MN',NULL,1325404800,20,143530.0000,7.8500,9100.7900),(1723,'US','NM',NULL,1325404800,10,2150.0000,0.0000,0.0000),(1724,'US','NM',NULL,1325404800,10,7650.0000,1.7000,0.0000),(1725,'US','NM',NULL,1325404800,10,13150.0000,3.2000,93.5000),(1726,'US','NM',NULL,1325404800,10,18150.0000,4.7000,269.5000),(1727,'US','NM',NULL,1325404800,10,18150.0000,4.9000,504.5000),(1728,'US','NM',NULL,1325404800,20,8100.0000,0.0000,0.0000),(1729,'US','NM',NULL,1325404800,20,16100.0000,1.7000,0.0000),(1730,'US','NM',NULL,1325404800,20,24100.0000,3.2000,136.0000),(1731,'US','NM',NULL,1325404800,20,32100.0000,4.7000,392.0000),(1732,'US','NM',NULL,1325404800,20,32100.0000,4.9000,768.0000),(1733,'US','ND',NULL,1325404800,10,4000.0000,0.0000,0.0000),(1734,'US','ND',NULL,1325404800,10,38000.0000,1.5100,0.0000),(1735,'US','ND',NULL,1325404800,10,79000.0000,2.8200,513.4000),(1736,'US','ND',NULL,1325404800,10,180000.0000,3.1300,1669.6000),(1737,'US','ND',NULL,1325404800,10,390000.0000,3.6300,4830.9000),(1738,'US','ND',NULL,1325404800,10,390000.0000,3.9900,12453.9000),(1739,'US','ND',NULL,1325404800,20,9600.0000,0.0000,0.0000),(1740,'US','ND',NULL,1325404800,20,67000.0000,1.5100,0.0000),(1741,'US','ND',NULL,1325404800,20,127000.0000,2.8200,866.7400),(1742,'US','ND',NULL,1325404800,20,225000.0000,3.1300,2558.7400),(1743,'US','ND',NULL,1325404800,20,395000.0000,3.6300,5626.1400),(1744,'US','ND',NULL,1325404800,20,395000.0000,3.9900,11797.1400),(1745,'US','OR',NULL,1325404800,10,3150.0000,5.0000,183.0000),(1746,'US','OR',NULL,1325404800,10,7950.0000,7.0000,341.0000),(1747,'US','OR',NULL,1325404800,10,50000.0000,9.0000,677.0000),(1748,'US','OR',NULL,1325404800,10,125000.0000,9.0000,494.0000),(1749,'US','OR',NULL,1325404800,10,125000.0000,9.9000,11028.0000),(1750,'US','OR',NULL,1325404800,20,6300.0000,5.0000,183.0000),(1751,'US','OR',NULL,1325404800,20,15900.0000,7.0000,498.0000),(1752,'US','OR',NULL,1325404800,20,50000.0000,9.0000,1170.0000),(1753,'US','OR',NULL,1325404800,20,250000.0000,9.0000,987.0000),(1754,'US','OR',NULL,1325404800,20,250000.0000,9.9000,22056.0000),(1755,'US','RI',NULL,1325404800,10,57150.0000,3.7500,0.0000),(1756,'US','RI',NULL,1325404800,10,129900.0000,4.7500,2143.1300),(1757,'US','RI',NULL,1325404800,10,129900.0000,5.9900,5598.7500),(1758,'US','RI',NULL,1325404800,20,57150.0000,3.7500,0.0000),(1759,'US','RI',NULL,1325404800,20,129900.0000,4.7500,2143.1300),(1760,'US','RI',NULL,1325404800,20,129900.0000,5.9900,5598.7500),(1761,'US','VT',NULL,1325404800,10,2650.0000,0.0000,0.0000),(1762,'US','VT',NULL,1325404800,10,37500.0000,3.5500,0.0000),(1763,'US','VT',NULL,1325404800,10,87800.0000,6.8000,1237.1800),(1764,'US','VT',NULL,1325404800,10,180800.0000,7.8000,4657.5800),(1765,'US','VT',NULL,1325404800,10,390500.0000,8.8000,11911.5800),(1766,'US','VT',NULL,1325404800,10,390500.0000,8.9500,30365.1800),(1767,'US','VT',NULL,1325404800,20,8000.0000,0.0000,0.0000),(1768,'US','VT',NULL,1325404800,20,65800.0000,3.5500,0.0000),(1769,'US','VT',NULL,1325404800,20,150800.0000,6.8000,2051.9000),(1770,'US','VT',NULL,1325404800,20,225550.0000,7.8000,7831.9000),(1771,'US','VT',NULL,1325404800,20,396450.0000,8.8000,13662.4000),(1772,'US','VT',NULL,1325404800,20,396450.0000,8.9500,28701.6000),(1773,'US','CA',NULL,1357027200,10,7455.0000,1.1000,0.0000),(1774,'US','CA',NULL,1357027200,10,17676.0000,2.2000,82.0100),(1775,'US','CA',NULL,1357027200,10,27897.0000,4.4000,306.8700),(1776,'US','CA',NULL,1357027200,10,38726.0000,6.6000,756.5900),(1777,'US','CA',NULL,1357027200,10,48942.0000,8.8000,1471.3000),(1778,'US','CA',NULL,1357027200,10,250000.0000,10.2300,2370.3100),(1779,'US','CA',NULL,1357027200,10,300000.0000,11.3300,22938.5400),(1780,'US','CA',NULL,1357027200,10,500000.0000,12.4300,28603.5400),(1781,'US','CA',NULL,1357027200,10,999999.9999,13.5300,53463.5400),(1782,'US','CA',NULL,1357027200,10,999999.9999,14.6300,121113.5400),(1783,'US','CA',NULL,1357027200,20,7455.0000,1.1000,0.0000),(1784,'US','CA',NULL,1357027200,20,17676.0000,2.2000,82.0100),(1785,'US','CA',NULL,1357027200,20,27897.0000,4.4000,306.8700),(1786,'US','CA',NULL,1357027200,20,38726.0000,6.6000,756.5900),(1787,'US','CA',NULL,1357027200,20,48942.0000,8.8000,1471.3000),(1788,'US','CA',NULL,1357027200,20,250000.0000,10.2300,2370.3100),(1789,'US','CA',NULL,1357027200,20,300000.0000,11.3300,22938.5400),(1790,'US','CA',NULL,1357027200,20,500000.0000,12.4300,28603.5400),(1791,'US','CA',NULL,1357027200,20,999999.9999,13.5300,53463.5400),(1792,'US','CA',NULL,1357027200,20,999999.9999,14.6300,121113.5400),(1793,'US','CA',NULL,1357027200,30,14910.0000,1.1000,0.0000),(1794,'US','CA',NULL,1357027200,30,35352.0000,2.2000,164.0100),(1795,'US','CA',NULL,1357027200,30,55794.0000,4.4000,613.7300),(1796,'US','CA',NULL,1357027200,30,77452.0000,6.6000,1513.1800),(1797,'US','CA',NULL,1357027200,30,97884.0000,8.8000,2942.6100),(1798,'US','CA',NULL,1357027200,30,500000.0000,10.2300,4740.6300),(1799,'US','CA',NULL,1357027200,30,600000.0000,11.3300,45877.1000),(1800,'US','CA',NULL,1357027200,30,999999.9999,12.4300,57207.1000),(1801,'US','CA',NULL,1357027200,30,999999.9999,14.6300,106927.1000),(1802,'US','CA',NULL,1357027200,40,14920.0000,1.1000,0.0000),(1803,'US','CA',NULL,1357027200,40,35351.0000,2.2000,164.1200),(1804,'US','CA',NULL,1357027200,40,45571.0000,4.4000,613.6000),(1805,'US','CA',NULL,1357027200,40,56400.0000,6.6000,1063.2800),(1806,'US','CA',NULL,1357027200,40,66618.0000,8.8000,1777.9900),(1807,'US','CA',NULL,1357027200,40,340000.0000,10.2300,2677.1700),(1808,'US','CA',NULL,1357027200,40,408000.0000,11.3300,30644.1500),(1809,'US','CA',NULL,1357027200,40,680000.0000,12.4300,38348.5500),(1810,'US','CA',NULL,1357027200,40,999999.9999,13.5300,72158.1500),(1811,'US','CA',NULL,1357027200,40,999999.9999,14.6300,115454.1500),(1812,'US','ID',NULL,1357027200,10,2150.0000,0.0000,0.0000),(1813,'US','ID',NULL,1357027200,10,3530.0000,1.6000,0.0000),(1814,'US','ID',NULL,1357027200,10,4910.0000,3.6000,22.0000),(1815,'US','ID',NULL,1357027200,10,6290.0000,4.1000,72.0000),(1816,'US','ID',NULL,1357027200,10,7670.0000,5.1000,129.0000),(1817,'US','ID',NULL,1357027200,10,9050.0000,6.1000,199.0000),(1818,'US','ID',NULL,1357027200,10,12500.0000,7.1000,283.0000),(1819,'US','ID',NULL,1357027200,10,12500.0000,7.4000,528.0000),(1820,'US','ID',NULL,1357027200,20,8100.0000,0.0000,0.0000),(1821,'US','ID',NULL,1357027200,20,10860.0000,1.6000,0.0000),(1822,'US','ID',NULL,1357027200,20,13620.0000,3.6000,44.0000),(1823,'US','ID',NULL,1357027200,20,16380.0000,4.1000,143.0000),(1824,'US','ID',NULL,1357027200,20,19140.0000,5.1000,256.0000),(1825,'US','ID',NULL,1357027200,20,21900.0000,6.1000,397.0000),(1826,'US','ID',NULL,1357027200,20,28800.0000,7.1000,565.0000),(1827,'US','ID',NULL,1357027200,20,28800.0000,7.4000,1055.0000),(1828,'US','KS',NULL,1357027200,10,3000.0000,0.0000,0.0000),(1829,'US','KS',NULL,1357027200,10,18000.0000,3.0000,0.0000),(1830,'US','KS',NULL,1357027200,10,18000.0000,4.9000,450.0000),(1831,'US','KS',NULL,1357027200,20,6000.0000,0.0000,0.0000),(1832,'US','KS',NULL,1357027200,20,36000.0000,3.0000,0.0000),(1833,'US','KS',NULL,1357027200,20,36000.0000,4.9000,900.0000),(1834,'US','ME',NULL,1357027200,10,8450.0000,0.0000,0.0000),(1835,'US','ME',NULL,1357027200,10,24150.0000,6.5000,0.0000),(1836,'US','ME',NULL,1357027200,10,24150.0000,7.9500,1021.0000),(1837,'US','ME',NULL,1357027200,20,17750.0000,0.0000,0.0000),(1838,'US','ME',NULL,1357027200,20,49150.0000,6.5000,0.0000),(1839,'US','ME',NULL,1357027200,20,49150.0000,7.9500,2041.0000),(1840,'US','MD',NULL,1357027200,10,100000.0000,4.7500,0.0000),(1841,'US','MD',NULL,1357027200,10,125000.0000,5.0000,4750.0000),(1842,'US','MD',NULL,1357027200,10,150000.0000,5.2500,6000.0000),(1843,'US','MD',NULL,1357027200,10,250000.0000,5.5000,7312.5000),(1844,'US','MD',NULL,1357027200,10,250000.0000,5.7500,12812.5000),(1845,'US','MD',NULL,1357027200,20,150000.0000,4.7500,0.0000),(1846,'US','MD',NULL,1357027200,20,175000.0000,5.0000,7125.0000),(1847,'US','MD',NULL,1357027200,20,225000.0000,5.2500,8375.0000),(1848,'US','MD',NULL,1357027200,20,300000.0000,5.5000,11000.0000),(1849,'US','MD',NULL,1357027200,20,300000.0000,5.7500,15125.0000),(1850,'US','MD',NULL,1357027200,30,100000.0000,4.7500,0.0000),(1851,'US','MD',NULL,1357027200,30,125000.0000,5.0000,4750.0000),(1852,'US','MD',NULL,1357027200,30,150000.0000,5.2500,6000.0000),(1853,'US','MD',NULL,1357027200,30,250000.0000,5.5000,7312.5000),(1854,'US','MD',NULL,1357027200,30,250000.0000,5.7500,12812.5000),(1855,'US','MD',NULL,1357027200,40,150000.0000,4.7500,0.0000),(1856,'US','MD',NULL,1357027200,40,175000.0000,5.0000,7125.0000),(1857,'US','MD',NULL,1357027200,40,225000.0000,5.2500,8375.0000),(1858,'US','MD',NULL,1357027200,40,300000.0000,5.5000,11000.0000),(1859,'US','MD',NULL,1357027200,40,300000.0000,5.7500,15125.0000),(1860,'US','MN',NULL,1357027200,10,2200.0000,0.0000,0.0000),(1861,'US','MN',NULL,1357027200,10,26470.0000,5.3500,0.0000),(1862,'US','MN',NULL,1357027200,10,81930.0000,7.0500,1298.4500),(1863,'US','MN',NULL,1357027200,10,81930.0000,7.8500,5208.3800),(1864,'US','MN',NULL,1357027200,20,6250.0000,0.0000,0.0000),(1865,'US','MN',NULL,1357027200,20,41730.0000,5.3500,0.0000),(1866,'US','MN',NULL,1357027200,20,147210.0000,7.0500,1898.1800),(1867,'US','MN',NULL,1357027200,20,147210.0000,7.8500,9334.5200),(1868,'US','NY',NULL,1357027200,10,8200.0000,4.0000,0.0000),(1869,'US','NY',NULL,1357027200,10,11300.0000,4.5000,328.0000),(1870,'US','NY',NULL,1357027200,10,13350.0000,5.2500,468.0000),(1871,'US','NY',NULL,1357027200,10,20550.0000,5.9000,575.0000),(1872,'US','NY',NULL,1357027200,10,77150.0000,6.4500,1000.0000),(1873,'US','NY',NULL,1357027200,10,92600.0000,6.6500,4651.0000),(1874,'US','NY',NULL,1357027200,10,102900.0000,7.5800,5678.0000),(1875,'US','NY',NULL,1357027200,10,154350.0000,8.0800,6459.0000),(1876,'US','NY',NULL,1357027200,10,205850.0000,7.1500,10616.0000),(1877,'US','NY',NULL,1357027200,10,257300.0000,8.1500,14298.0000),(1878,'US','NY',NULL,1357027200,10,999999.9999,7.3500,18491.0000),(1879,'US','NY',NULL,1357027200,10,999999.9999,49.0200,75230.0000),(1880,'US','NY',NULL,1357027200,10,999999.9999,9.6200,100475.0000),(1881,'US','NY',NULL,1357027200,20,8200.0000,4.0000,0.0000),(1882,'US','NY',NULL,1357027200,20,11300.0000,4.5000,328.0000),(1883,'US','NY',NULL,1357027200,20,13350.0000,5.2500,468.0000),(1884,'US','NY',NULL,1357027200,20,20550.0000,5.9000,575.0000),(1885,'US','NY',NULL,1357027200,20,77150.0000,6.4500,1000.0000),(1886,'US','NY',NULL,1357027200,20,92600.0000,6.6500,4651.0000),(1887,'US','NY',NULL,1357027200,20,102900.0000,7.2800,5678.0000),(1888,'US','NY',NULL,1357027200,20,154350.0000,7.7800,6428.0000),(1889,'US','NY',NULL,1357027200,20,205850.0000,8.0800,10431.0000),(1890,'US','NY',NULL,1357027200,20,308750.0000,7.1500,14592.0000),(1891,'US','NY',NULL,1357027200,20,360250.0000,8.1500,21949.0000),(1892,'US','NY',NULL,1357027200,20,999999.9999,7.3500,26147.0000),(1893,'US','NY',NULL,1357027200,20,999999.9999,7.6500,75318.0000),(1894,'US','NY',NULL,1357027200,20,999999.9999,88.4200,154059.0000),(1895,'US','NY',NULL,1357027200,20,999999.9999,9.6200,199596.0000),(1896,'US','NY','YONKERS',1357027200,10,8200.0000,4.0000,0.0000),(1897,'US','NY','YONKERS',1357027200,10,11300.0000,4.5000,328.0000),(1898,'US','NY','YONKERS',1357027200,10,13350.0000,5.2500,468.0000),(1899,'US','NY','YONKERS',1357027200,10,20550.0000,5.9000,575.0000),(1900,'US','NY','YONKERS',1357027200,10,77150.0000,6.4500,1000.0000),(1901,'US','NY','YONKERS',1357027200,10,92600.0000,6.6500,4651.0000),(1902,'US','NY','YONKERS',1357027200,10,102900.0000,7.5800,5678.0000),(1903,'US','NY','YONKERS',1357027200,10,154350.0000,8.0800,6459.0000),(1904,'US','NY','YONKERS',1357027200,10,205850.0000,7.1500,10616.0000),(1905,'US','NY','YONKERS',1357027200,10,257300.0000,8.1500,14298.0000),(1906,'US','NY','YONKERS',1357027200,10,999999.9999,7.3500,18491.0000),(1907,'US','NY','YONKERS',1357027200,10,999999.9999,49.0200,75230.0000),(1908,'US','NY','YONKERS',1357027200,10,999999.9999,9.6200,100475.0000),(1909,'US','NY','YONKERS',1357027200,20,8200.0000,4.0000,0.0000),(1910,'US','NY','YONKERS',1357027200,20,11300.0000,4.5000,328.0000),(1911,'US','NY','YONKERS',1357027200,20,13350.0000,5.2500,468.0000),(1912,'US','NY','YONKERS',1357027200,20,20550.0000,5.9000,575.0000),(1913,'US','NY','YONKERS',1357027200,20,77150.0000,6.4500,1000.0000),(1914,'US','NY','YONKERS',1357027200,20,92600.0000,6.6500,4651.0000),(1915,'US','NY','YONKERS',1357027200,20,102900.0000,7.2800,5678.0000),(1916,'US','NY','YONKERS',1357027200,20,154350.0000,7.7800,6428.0000),(1917,'US','NY','YONKERS',1357027200,20,205850.0000,8.0800,10431.0000),(1918,'US','NY','YONKERS',1357027200,20,308750.0000,7.1500,14592.0000),(1919,'US','NY','YONKERS',1357027200,20,360250.0000,8.1500,21949.0000),(1920,'US','NY','YONKERS',1357027200,20,999999.9999,7.3500,26147.0000),(1921,'US','NY','YONKERS',1357027200,20,999999.9999,7.6500,75318.0000),(1922,'US','NY','YONKERS',1357027200,20,999999.9999,88.4200,154059.0000),(1923,'US','NY','YONKERS',1357027200,20,999999.9999,9.6200,199596.0000),(1924,'US','ND',NULL,1357027200,10,4100.0000,0.0000,0.0000),(1925,'US','ND',NULL,1357027200,10,39000.0000,1.5100,0.0000),(1926,'US','ND',NULL,1357027200,10,81000.0000,2.8200,526.9900),(1927,'US','ND',NULL,1357027200,10,185000.0000,3.1300,1711.3900),(1928,'US','ND',NULL,1357027200,10,400000.0000,3.6300,4966.5900),(1929,'US','ND',NULL,1357027200,10,400000.0000,3.9900,12771.0900),(1930,'US','ND',NULL,1357027200,20,10000.0000,0.0000,0.0000),(1931,'US','ND',NULL,1357027200,20,69000.0000,1.5100,0.0000),(1932,'US','ND',NULL,1357027200,20,130000.0000,2.8200,890.9000),(1933,'US','ND',NULL,1357027200,20,231000.0000,3.1300,2611.1000),(1934,'US','ND',NULL,1357027200,20,405000.0000,3.6300,5772.4000),(1935,'US','ND',NULL,1357027200,20,405000.0000,3.9900,12088.6000),(1936,'US','OR',NULL,1357027200,10,3250.0000,5.0000,0.0000),(1937,'US','OR',NULL,1357027200,10,8150.0000,7.0000,163.0000),(1938,'US','OR',NULL,1357027200,10,125000.0000,9.0000,506.0000),(1939,'US','OR',NULL,1357027200,10,125000.0000,9.9000,11022.0000),(1940,'US','OR',NULL,1357027200,20,6500.0000,5.0000,0.0000),(1941,'US','OR',NULL,1357027200,20,16300.0000,7.0000,325.0000),(1942,'US','OR',NULL,1357027200,20,250000.0000,9.0000,1011.0000),(1943,'US','OR',NULL,1357027200,20,250000.0000,9.9000,22044.0000),(1944,'US','OK',NULL,1357027200,10,6100.0000,0.0000,0.0000),(1945,'US','OK',NULL,1357027200,10,7100.0000,0.5000,0.0000),(1946,'US','OK',NULL,1357027200,10,8600.0000,1.0000,5.0000),(1947,'US','OK',NULL,1357027200,10,9850.0000,2.0000,20.0000),(1948,'US','OK',NULL,1357027200,10,11000.0000,3.0000,45.0000),(1949,'US','OK',NULL,1357027200,10,13300.0000,4.0000,79.5000),(1950,'US','OK',NULL,1357027200,10,14800.0000,5.0000,171.5000),(1951,'US','OK',NULL,1357027200,10,14800.0000,5.2500,246.5000),(1952,'US','OK',NULL,1357027200,20,10150.0000,0.0000,0.0000),(1953,'US','OK',NULL,1357027200,20,12150.0000,0.5000,0.0000),(1954,'US','OK',NULL,1357027200,20,15150.0000,1.0000,10.0000),(1955,'US','OK',NULL,1357027200,20,17650.0000,2.0000,40.0000),(1956,'US','OK',NULL,1357027200,20,19950.0000,3.0000,90.0000),(1957,'US','OK',NULL,1357027200,20,22350.0000,4.0000,159.0000),(1958,'US','OK',NULL,1357027200,20,25150.0000,5.0000,255.0000),(1959,'US','OK',NULL,1357027200,20,25150.0000,5.2500,395.0000),(1960,'US','RI',NULL,1357027200,10,58600.0000,3.7500,0.0000),(1961,'US','RI',NULL,1357027200,10,133250.0000,4.7500,2197.5000),(1962,'US','RI',NULL,1357027200,10,133250.0000,5.9900,5743.3800),(1963,'US','RI',NULL,1357027200,20,58600.0000,3.7500,0.0000),(1964,'US','RI',NULL,1357027200,20,133250.0000,4.7500,2197.5000),(1965,'US','RI',NULL,1357027200,20,133250.0000,5.9900,5743.3800),(1966,'US',NULL,NULL,1357027200,10,2200.0000,0.0000,0.0000),(1967,'US',NULL,NULL,1357027200,10,11125.0000,10.0000,0.0000),(1968,'US',NULL,NULL,1357027200,10,38450.0000,15.0000,892.5000),(1969,'US',NULL,NULL,1357027200,10,90050.0000,25.0000,4991.2500),(1970,'US',NULL,NULL,1357027200,10,185450.0000,28.0000,17891.2500),(1971,'US',NULL,NULL,1357027200,10,400550.0000,33.0000,44603.2500),(1972,'US',NULL,NULL,1357027200,10,402200.0000,35.0000,115586.2500),(1973,'US',NULL,NULL,1357027200,10,402200.0000,39.6000,116163.7500),(1974,'US',NULL,NULL,1357027200,20,8300.0000,0.0000,0.0000),(1975,'US',NULL,NULL,1357027200,20,26150.0000,10.0000,0.0000),(1976,'US',NULL,NULL,1357027200,20,80800.0000,15.0000,1785.0000),(1977,'US',NULL,NULL,1357027200,20,154700.0000,25.0000,9982.5000),(1978,'US',NULL,NULL,1357027200,20,231350.0000,28.0000,28457.5000),(1979,'US',NULL,NULL,1357027200,20,406650.0000,33.0000,49919.5000),(1980,'US',NULL,NULL,1357027200,20,458300.0000,35.0000,107768.5000),(1981,'US',NULL,NULL,1357027200,20,458300.0000,39.6000,125846.0000),(1982,'US','ID',NULL,1369119600,10,2200.0000,0.0000,0.0000),(1983,'US','ID',NULL,1369119600,10,3609.0000,1.6000,0.0000),(1984,'US','ID',NULL,1369119600,10,5018.0000,3.6000,23.0000),(1985,'US','ID',NULL,1369119600,10,6427.0000,4.1000,74.0000),(1986,'US','ID',NULL,1369119600,10,7836.0000,5.1000,132.0000),(1987,'US','ID',NULL,1369119600,10,9245.0000,6.1000,204.0000),(1988,'US','ID',NULL,1369119600,10,12768.0000,7.1000,290.0000),(1989,'US','ID',NULL,1369119600,10,12768.0000,7.4000,540.0000),(1990,'US','ID',NULL,1369119600,20,8300.0000,0.0000,0.0000),(1991,'US','ID',NULL,1369119600,20,11118.0000,1.6000,0.0000),(1992,'US','ID',NULL,1369119600,20,13936.0000,3.6000,45.0000),(1993,'US','ID',NULL,1369119600,20,16754.0000,4.1000,146.0000),(1994,'US','ID',NULL,1369119600,20,19572.0000,5.1000,262.0000),(1995,'US','ID',NULL,1369119600,20,22390.0000,6.1000,406.0000),(1996,'US','ID',NULL,1369119600,20,29436.0000,7.1000,578.0000),(1997,'US','ID',NULL,1369119600,20,29436.0000,7.4000,1078.0000),(1998,'US','NM',NULL,1357027200,10,2200.0000,0.0000,0.0000),(1999,'US','NM',NULL,1357027200,10,7700.0000,1.7000,0.0000),(2000,'US','NM',NULL,1357027200,10,13200.0000,3.2000,93.5000),(2001,'US','NM',NULL,1357027200,10,18200.0000,4.7000,269.5000),(2002,'US','NM',NULL,1357027200,10,18200.0000,4.9000,504.5000),(2003,'US','NM',NULL,1357027200,20,8300.0000,0.0000,0.0000),(2004,'US','NM',NULL,1357027200,20,16300.0000,1.7000,0.0000),(2005,'US','NM',NULL,1357027200,20,24300.0000,3.2000,136.0000),(2006,'US','NM',NULL,1357027200,20,32300.0000,4.7000,392.0000),(2007,'US','NM',NULL,1357027200,20,32300.0000,4.9000,768.0000),(2008,'US','VT',NULL,1357027200,10,2650.0000,0.0000,0.0000),(2009,'US','VT',NULL,1357027200,10,38450.0000,3.5500,0.0000),(2010,'US','VT',NULL,1357027200,10,90050.0000,6.8000,1270.9000),(2011,'US','VT',NULL,1357027200,10,185450.0000,7.8000,4779.7000),(2012,'US','VT',NULL,1357027200,10,400550.0000,8.8000,12220.9000),(2013,'US','VT',NULL,1357027200,10,400550.0000,8.9500,31149.7000),(2014,'US','VT',NULL,1357027200,20,8000.0000,0.0000,0.0000),(2015,'US','VT',NULL,1357027200,20,67400.0000,3.5500,0.0000),(2016,'US','VT',NULL,1357027200,20,154700.0000,6.8000,2108.7000),(2017,'US','VT',NULL,1357027200,20,231350.0000,7.8000,8045.1000),(2018,'US','VT',NULL,1357027200,20,406650.0000,8.8000,14023.8000),(2019,'US','VT',NULL,1357027200,20,406650.0000,8.9500,29450.2000),(2020,'US','CO',NULL,1357027200,10,2200.0000,0.0000,0.0000),(2021,'US','CO',NULL,1357027200,10,2200.0000,4.6300,0.0000),(2022,'US','CO',NULL,1357027200,20,8300.0000,0.0000,0.0000),(2023,'US','CO',NULL,1357027200,20,8300.0000,4.6300,0.0000),(2040,'US','ND',NULL,1372662000,10,4100.0000,0.0000,0.0000),(2041,'US','ND',NULL,1372662000,10,39000.0000,1.2200,0.0000),(2042,'US','ND',NULL,1372662000,10,81000.0000,2.2700,425.7800),(2043,'US','ND',NULL,1372662000,10,185000.0000,2.5200,1379.1800),(2044,'US','ND',NULL,1372662000,10,400000.0000,2.9300,3999.9800),(2045,'US','ND',NULL,1372662000,10,400000.0000,3.2200,10299.4800),(2046,'US','ND',NULL,1372662000,20,10000.0000,0.0000,0.0000),(2047,'US','ND',NULL,1372662000,20,69000.0000,1.2200,0.0000),(2048,'US','ND',NULL,1372662000,20,130000.0000,2.2700,719.8000),(2049,'US','ND',NULL,1372662000,20,231000.0000,2.5200,2104.5000),(2050,'US','ND',NULL,1372662000,20,405000.0000,2.9300,4649.7000),(2051,'US','ND',NULL,1372662000,20,405000.0000,3.2200,9747.9000),(2052,'US','OH',NULL,1378018800,0,5000.0000,0.5810,0.0000),(2053,'US','OH',NULL,1378018800,0,10000.0000,1.1610,29.0500),(2054,'US','OH',NULL,1378018800,0,15000.0000,2.3220,87.1000),(2055,'US','OH',NULL,1378018800,0,20000.0000,2.9030,203.2000),(2056,'US','OH',NULL,1378018800,0,40000.0000,3.4830,348.3500),(2057,'US','OH',NULL,1378018800,0,80000.0000,4.0640,1044.9500),(2058,'US','OH',NULL,1378018800,0,100000.0000,4.6440,2670.5500),(2059,'US','OH',NULL,1378018800,0,100000.0000,5.8050,3599.3500),(2060,'US',NULL,NULL,1388563200,10,2250.0000,0.0000,0.0000),(2061,'US',NULL,NULL,1388563200,10,11325.0000,10.0000,0.0000),(2062,'US',NULL,NULL,1388563200,10,39150.0000,15.0000,907.5000),(2063,'US',NULL,NULL,1388563200,10,91600.0000,25.0000,5081.2500),(2064,'US',NULL,NULL,1388563200,10,188600.0000,28.0000,18193.7500),(2065,'US',NULL,NULL,1388563200,10,407350.0000,33.0000,45353.7500),(2066,'US',NULL,NULL,1388563200,10,409000.0000,35.0000,112683.5000),(2067,'US',NULL,NULL,1388563200,10,409000.0000,39.6000,118118.7500),(2068,'US',NULL,NULL,1388563200,20,8450.0000,0.0000,0.0000),(2069,'US',NULL,NULL,1388563200,20,26600.0000,10.0000,0.0000),(2070,'US',NULL,NULL,1388563200,20,82250.0000,15.0000,1815.0000),(2071,'US',NULL,NULL,1388563200,20,157300.0000,25.0000,10162.5000),(2072,'US',NULL,NULL,1388563200,20,235300.0000,28.0000,28925.0000),(2073,'US',NULL,NULL,1388563200,20,413550.0000,33.0000,50765.0000),(2074,'US',NULL,NULL,1388563200,20,466050.0000,35.0000,109587.5000),(2075,'US',NULL,NULL,1388563200,20,466050.0000,39.6000,127962.5000),(2076,'US','CA',NULL,1388563200,10,7582.0000,1.1000,0.0000),(2077,'US','CA',NULL,1388563200,10,17976.0000,2.2000,83.4000),(2078,'US','CA',NULL,1388563200,10,28371.0000,4.4000,312.0700),(2079,'US','CA',NULL,1388563200,10,39384.0000,6.6000,769.4500),(2080,'US','CA',NULL,1388563200,10,49774.0000,8.8000,1496.3100),(2081,'US','CA',NULL,1388563200,10,254250.0000,10.2300,2410.6300),(2082,'US','CA',NULL,1388563200,10,305100.0000,11.3300,23328.5200),(2083,'US','CA',NULL,1388563200,10,508500.0000,12.4300,29089.8300),(2084,'US','CA',NULL,1388563200,10,1000000.0000,13.5300,54372.4500),(2085,'US','CA',NULL,1388563200,10,1000000.0000,14.6300,120872.4000),(2086,'US','CA',NULL,1388563200,20,7582.0000,1.1000,0.0000),(2087,'US','CA',NULL,1388563200,20,17976.0000,2.2000,83.4000),(2088,'US','CA',NULL,1388563200,20,28371.0000,4.4000,312.0700),(2089,'US','CA',NULL,1388563200,20,39384.0000,6.6000,769.4500),(2090,'US','CA',NULL,1388563200,20,49774.0000,8.8000,1496.3100),(2091,'US','CA',NULL,1388563200,20,254250.0000,10.2300,2410.6300),(2092,'US','CA',NULL,1388563200,20,305100.0000,11.3300,23328.5200),(2093,'US','CA',NULL,1388563200,20,508500.0000,12.4300,29089.8300),(2094,'US','CA',NULL,1388563200,20,1000000.0000,13.5300,54372.4500),(2095,'US','CA',NULL,1388563200,20,1000000.0000,14.6300,120872.4000),(2096,'US','CA',NULL,1388563200,30,15164.0000,1.1000,0.0000),(2097,'US','CA',NULL,1388563200,30,35952.0000,2.2000,166.8000),(2098,'US','CA',NULL,1388563200,30,56742.0000,4.4000,624.1400),(2099,'US','CA',NULL,1388563200,30,78768.0000,6.6000,1538.9000),(2100,'US','CA',NULL,1388563200,30,99548.0000,8.8000,2992.6200),(2101,'US','CA',NULL,1388563200,30,508500.0000,10.2300,4821.2600),(2102,'US','CA',NULL,1388563200,30,610200.0000,11.3300,46657.0500),(2103,'US','CA',NULL,1388563200,30,1000000.0000,12.4300,58179.6600),(2104,'US','CA',NULL,1388563200,30,1017000.0000,13.5300,106631.8000),(2105,'US','CA',NULL,1388563200,30,1017000.0000,14.6300,108931.9000),(2106,'US','CA',NULL,1388563200,40,15174.0000,1.1000,0.0000),(2107,'US','CA',NULL,1388563200,40,35952.0000,2.2000,166.9100),(2108,'US','CA',NULL,1388563200,40,46346.0000,4.4000,624.0300),(2109,'US','CA',NULL,1388563200,40,57359.0000,6.6000,1081.3700),(2110,'US','CA',NULL,1388563200,40,67751.0000,8.8000,1808.2300),(2111,'US','CA',NULL,1388563200,40,345780.0000,10.2300,2722.7300),(2112,'US','CA',NULL,1388563200,40,414936.0000,11.3300,31165.1000),(2113,'US','CA',NULL,1388563200,40,691560.0000,12.4300,39000.4700),(2114,'US','CA',NULL,1388563200,40,1000000.0000,13.5300,73384.8300),(2115,'US','CA',NULL,1388563200,40,1000000.0000,14.6300,115116.7600),(2116,'US','DE',NULL,1388563200,0,2000.0000,0.0000,0.0000),(2117,'US','DE',NULL,1388563200,0,5000.0000,2.2000,0.0000),(2118,'US','DE',NULL,1388563200,0,10000.0000,3.9000,66.0000),(2119,'US','DE',NULL,1388563200,0,20000.0000,4.8000,261.0000),(2120,'US','DE',NULL,1388563200,0,25000.0000,5.2000,741.0000),(2121,'US','DE',NULL,1388563200,0,60000.0000,5.5500,1001.0000),(2122,'US','DE',NULL,1388563200,0,60000.0000,6.6000,2943.5000),(2123,'US','KS',NULL,1388563200,10,3000.0000,0.0000,0.0000),(2124,'US','KS',NULL,1388563200,10,18000.0000,2.7000,0.0000),(2125,'US','KS',NULL,1388563200,10,18000.0000,4.8000,405.0000),(2126,'US','KS',NULL,1388563200,20,6000.0000,0.0000,0.0000),(2127,'US','KS',NULL,1388563200,20,36000.0000,2.7000,0.0000),(2128,'US','KS',NULL,1388563200,20,36000.0000,4.8000,810.0000),(2129,'US','ME',NULL,1388563200,10,8550.0000,0.0000,0.0000),(2130,'US','ME',NULL,1388563200,10,24250.0000,6.5000,0.0000),(2131,'US','ME',NULL,1388563200,10,24250.0000,7.9500,1020.5000),(2132,'US','ME',NULL,1388563200,20,20000.0000,0.0000,0.0000),(2133,'US','ME',NULL,1388563200,20,51400.0000,6.5000,0.0000),(2134,'US','ME',NULL,1388563200,20,51400.0000,7.9500,2041.0000),(2135,'US','MN',NULL,1388563200,10,2250.0000,0.0000,0.0000),(2136,'US','MN',NULL,1388563200,10,26930.0000,5.3500,0.0000),(2137,'US','MN',NULL,1388563200,10,83330.0000,7.0500,1320.3800),(2138,'US','MN',NULL,1388563200,10,154790.0000,7.8500,5296.5800),(2139,'US','MN',NULL,1388563200,10,154790.0000,9.8500,10906.1900),(2140,'US','MN',NULL,1388563200,20,6400.0000,0.0000,0.0000),(2141,'US','MN',NULL,1388563200,20,42480.0000,5.3500,0.0000),(2142,'US','MN',NULL,1388563200,20,149750.0000,7.0500,1930.2800),(2143,'US','MN',NULL,1388563200,20,260640.0000,7.8500,9492.8200),(2144,'US','MN',NULL,1388563200,20,260640.0000,9.8500,18197.6900),(2145,'US','NY',NULL,1388563200,10,8300.0000,4.0000,0.0000),(2146,'US','NY',NULL,1388563200,10,11450.0000,4.5000,332.0000),(2147,'US','NY',NULL,1388563200,10,13550.0000,5.2500,474.0000),(2148,'US','NY',NULL,1388563200,10,20850.0000,5.9000,584.0000),(2149,'US','NY',NULL,1388563200,10,78400.0000,6.4500,1015.0000),(2150,'US','NY',NULL,1388563200,10,94100.0000,6.6500,4727.0000),(2151,'US','NY',NULL,1388563200,10,104600.0000,7.5800,5771.0000),(2152,'US','NY',NULL,1388563200,10,156900.0000,8.0800,6567.0000),(2153,'US','NY',NULL,1388563200,10,209250.0000,7.1500,10792.0000),(2154,'US','NY',NULL,1388563200,10,261550.0000,8.1500,14535.0000),(2155,'US','NY',NULL,1388563200,10,1046350.0000,7.3500,18798.0000),(2156,'US','NY',NULL,1388563200,10,1098700.0000,49.0200,76481.0000),(2157,'US','NY',NULL,1388563200,10,1098700.0000,9.6200,102143.0000),(2158,'US','NY',NULL,1388563200,20,8300.0000,4.0000,0.0000),(2159,'US','NY',NULL,1388563200,20,11450.0000,4.5000,332.0000),(2160,'US','NY',NULL,1388563200,20,13550.0000,5.2500,474.0000),(2161,'US','NY',NULL,1388563200,20,20850.0000,5.9000,584.0000),(2162,'US','NY',NULL,1388563200,20,78400.0000,6.4500,1015.0000),(2163,'US','NY',NULL,1388563200,20,94100.0000,6.6500,4727.0000),(2164,'US','NY',NULL,1388563200,20,104600.0000,7.2800,5771.0000),(2165,'US','NY',NULL,1388563200,20,156900.0000,7.7800,6535.0000),(2166,'US','NY',NULL,1388563200,20,209250.0000,8.0800,10604.0000),(2167,'US','NY',NULL,1388563200,20,313850.0000,7.1500,14834.0000),(2168,'US','NY',NULL,1388563200,20,366200.0000,8.1500,22313.0000),(2169,'US','NY',NULL,1388563200,20,1046350.0000,7.3500,26579.0000),(2170,'US','NY',NULL,1388563200,20,2092800.0000,7.6500,76570.0000),(2171,'US','NY',NULL,1388563200,20,2145150.0000,88.4200,156624.0000),(2172,'US','NY',NULL,1388563200,20,2145150.0000,9.6200,202912.0000),(2173,'US','NY','YONKERS',1388563200,10,8300.0000,4.0000,0.0000),(2174,'US','NY','YONKERS',1388563200,10,11450.0000,4.5000,332.0000),(2175,'US','NY','YONKERS',1388563200,10,13550.0000,5.2500,474.0000),(2176,'US','NY','YONKERS',1388563200,10,20850.0000,5.9000,584.0000),(2177,'US','NY','YONKERS',1388563200,10,78400.0000,6.4500,1015.0000),(2178,'US','NY','YONKERS',1388563200,10,94100.0000,6.6500,4727.0000),(2179,'US','NY','YONKERS',1388563200,10,104600.0000,7.5800,5771.0000),(2180,'US','NY','YONKERS',1388563200,10,156900.0000,8.0800,6567.0000),(2181,'US','NY','YONKERS',1388563200,10,209250.0000,7.1500,10792.0000),(2182,'US','NY','YONKERS',1388563200,10,261550.0000,8.1500,14535.0000),(2183,'US','NY','YONKERS',1388563200,10,1046350.0000,7.3500,18798.0000),(2184,'US','NY','YONKERS',1388563200,10,1098700.0000,49.0200,76481.0000),(2185,'US','NY','YONKERS',1388563200,10,1098700.0000,9.6200,102143.0000),(2186,'US','NY','YONKERS',1388563200,20,8300.0000,4.0000,0.0000),(2187,'US','NY','YONKERS',1388563200,20,11450.0000,4.5000,332.0000),(2188,'US','NY','YONKERS',1388563200,20,13550.0000,5.2500,474.0000),(2189,'US','NY','YONKERS',1388563200,20,20850.0000,5.9000,584.0000),(2190,'US','NY','YONKERS',1388563200,20,78400.0000,6.4500,1015.0000),(2191,'US','NY','YONKERS',1388563200,20,94100.0000,6.6500,4727.0000),(2192,'US','NY','YONKERS',1388563200,20,104600.0000,7.2800,5771.0000),(2193,'US','NY','YONKERS',1388563200,20,156900.0000,7.7800,6535.0000),(2194,'US','NY','YONKERS',1388563200,20,209250.0000,8.0800,10604.0000),(2195,'US','NY','YONKERS',1388563200,20,313850.0000,7.1500,14834.0000),(2196,'US','NY','YONKERS',1388563200,20,366200.0000,8.1500,22313.0000),(2197,'US','NY','YONKERS',1388563200,20,1046350.0000,7.3500,26579.0000),(2198,'US','NY','YONKERS',1388563200,20,2092800.0000,7.6500,76570.0000),(2199,'US','NY','YONKERS',1388563200,20,2145150.0000,88.4200,156624.0000),(2200,'US','NY','YONKERS',1388563200,20,2145150.0000,9.6200,202912.0000),(2201,'US','OK',NULL,1388563200,10,6200.0000,0.0000,0.0000),(2202,'US','OK',NULL,1388563200,10,7200.0000,0.5000,0.0000),(2203,'US','OK',NULL,1388563200,10,8700.0000,1.0000,5.0000),(2204,'US','OK',NULL,1388563200,10,9950.0000,2.0000,20.0000),(2205,'US','OK',NULL,1388563200,10,11100.0000,3.0000,45.0000),(2206,'US','OK',NULL,1388563200,10,13400.0000,4.0000,79.5000),(2207,'US','OK',NULL,1388563200,10,14900.0000,5.0000,171.5000),(2208,'US','OK',NULL,1388563200,10,14900.0000,5.2500,246.5000),(2209,'US','OK',NULL,1388563200,20,12400.0000,0.0000,0.0000),(2210,'US','OK',NULL,1388563200,20,14400.0000,0.5000,0.0000),(2211,'US','OK',NULL,1388563200,20,17400.0000,1.0000,10.0000),(2212,'US','OK',NULL,1388563200,20,19900.0000,2.0000,40.0000),(2213,'US','OK',NULL,1388563200,20,22200.0000,3.0000,90.0000),(2214,'US','OK',NULL,1388563200,20,24600.0000,4.0000,159.0000),(2215,'US','OK',NULL,1388563200,20,27400.0000,5.0000,255.0000),(2216,'US','OK',NULL,1388563200,20,27400.0000,5.2500,395.0000),(2217,'US','VT',NULL,1388563200,10,2650.0000,0.0000,0.0000),(2218,'US','VT',NULL,1388563200,10,39150.0000,3.5500,0.0000),(2219,'US','VT',NULL,1388563200,10,91600.0000,6.8000,1295.7500),(2220,'US','VT',NULL,1388563200,10,188600.0000,7.8000,4862.3500),(2221,'US','VT',NULL,1388563200,10,407350.0000,8.8000,12428.3500),(2222,'US','VT',NULL,1388563200,10,407350.0000,8.9500,31678.3500),(2223,'US','VT',NULL,1388563200,20,8000.0000,0.0000,0.0000),(2224,'US','VT',NULL,1388563200,20,68600.0000,3.5500,0.0000),(2225,'US','VT',NULL,1388563200,20,157300.0000,6.8000,2151.3000),(2226,'US','VT',NULL,1388563200,20,235300.0000,7.8000,8182.9000),(2227,'US','VT',NULL,1388563200,20,413550.0000,8.8000,14266.9000),(2228,'US','VT',NULL,1388563200,20,413550.0000,8.9500,29952.9000),(2229,'US','ND',NULL,1388563200,10,4200.0000,0.0000,0.0000),(2230,'US','ND',NULL,1388563200,10,40000.0000,1.2200,0.0000),(2231,'US','ND',NULL,1388563200,10,82000.0000,2.2700,436.7600),(2232,'US','ND',NULL,1388563200,10,188000.0000,2.5200,1390.1600),(2233,'US','ND',NULL,1388563200,10,405000.0000,2.9300,4061.3600),(2234,'US','ND',NULL,1388563200,10,405000.0000,3.2200,10416.4600),(2235,'US','ND',NULL,1388563200,20,10000.0000,0.0000,0.0000),(2236,'US','ND',NULL,1388563200,20,70000.0000,1.2200,0.0000),(2237,'US','ND',NULL,1388563200,20,132000.0000,2.2700,732.0000),(2238,'US','ND',NULL,1388563200,20,235000.0000,2.5200,2139.4000),(2239,'US','ND',NULL,1388563200,20,412000.0000,2.9300,4735.0000),(2240,'US','ND',NULL,1388563200,20,412000.0000,3.2200,9921.1000),(2241,'US','OR',NULL,1388563200,10,3300.0000,5.0000,0.0000),(2242,'US','OR',NULL,1388563200,10,8250.0000,7.0000,165.0000),(2243,'US','OR',NULL,1388563200,10,125000.0000,9.0000,512.0000),(2244,'US','OR',NULL,1388563200,10,125000.0000,9.9000,11019.0000),(2245,'US','OR',NULL,1388563200,20,6600.0000,5.0000,0.0000),(2246,'US','OR',NULL,1388563200,20,16500.0000,7.0000,330.0000),(2247,'US','OR',NULL,1388563200,20,250000.0000,9.0000,1023.0000),(2248,'US','OR',NULL,1388563200,20,250000.0000,9.9000,22038.0000),(2249,'US','RI',NULL,1388563200,10,59600.0000,3.7500,0.0000),(2250,'US','RI',NULL,1388563200,10,135500.0000,4.7500,2235.0000),(2251,'US','RI',NULL,1388563200,10,135500.0000,5.9900,5840.2500),(2252,'US','RI',NULL,1388563200,20,59600.0000,3.7500,0.0000),(2253,'US','RI',NULL,1388563200,20,135000.0000,4.7500,2235.0000),(2254,'US','RI',NULL,1388563200,20,135000.0000,5.9900,5840.2500),(2255,'US','WI',NULL,1396335600,10,5730.0000,0.0000,0.0000),(2256,'US','WI',NULL,1396335600,10,15200.0000,4.0000,0.0000),(2257,'US','WI',NULL,1396335600,10,16486.0000,4.4800,378.8000),(2258,'US','WI',NULL,1396335600,10,26227.0000,6.5408,436.4100),(2259,'US','WI',NULL,1396335600,10,62950.0000,7.0224,1073.5500),(2260,'US','WI',NULL,1396335600,10,240190.0000,6.2700,3652.3900),(2261,'US','WI',NULL,1396335600,10,240190.0000,7.6500,14765.3400),(2262,'US','WI',NULL,1396335600,20,7870.0000,0.0000,0.0000),(2263,'US','WI',NULL,1396335600,20,18780.0000,4.0000,0.0000),(2264,'US','WI',NULL,1396335600,20,21400.0000,5.8400,436.4000),(2265,'US','WI',NULL,1396335600,20,28308.0000,7.0080,589.4100),(2266,'US','WI',NULL,1396335600,20,60750.0000,7.5240,1073.5200),(2267,'US','WI',NULL,1396335600,20,240190.0000,6.2700,3514.4600),(2268,'US','WI',NULL,1396335600,20,240190.0000,7.6500,14765.3500),(2269,'US','ID',NULL,1401606000,10,2250.0000,0.0000,0.0000),(2270,'US','ID',NULL,1401606000,10,3679.0000,1.6000,0.0000),(2271,'US','ID',NULL,1401606000,10,5108.0000,3.6000,23.0000),(2272,'US','ID',NULL,1401606000,10,6537.0000,4.1000,74.0000),(2273,'US','ID',NULL,1401606000,10,7966.0000,5.1000,133.0000),(2274,'US','ID',NULL,1401606000,10,9395.0000,6.1000,206.0000),(2275,'US','ID',NULL,1401606000,10,12968.0000,7.1000,293.0000),(2276,'US','ID',NULL,1401606000,10,12968.0000,7.4000,547.0000),(2277,'US','ID',NULL,1401606000,20,8450.0000,0.0000,0.0000),(2278,'US','ID',NULL,1401606000,20,11308.0000,1.6000,0.0000),(2279,'US','ID',NULL,1401606000,20,14166.0000,3.6000,46.0000),(2280,'US','ID',NULL,1401606000,20,17024.0000,4.1000,149.0000),(2281,'US','ID',NULL,1401606000,20,19882.0000,5.1000,266.0000),(2282,'US','ID',NULL,1401606000,20,22740.0000,6.1000,412.0000),(2283,'US','ID',NULL,1401606000,20,29886.0000,7.1000,586.0000),(2284,'US','ID',NULL,1401606000,20,29886.0000,7.4000,1093.0000),(2285,'US','OH',NULL,1404198000,0,5000.0000,0.5740,0.0000),(2286,'US','OH',NULL,1404198000,0,10000.0000,1.1480,28.7000),(2287,'US','OH',NULL,1404198000,0,15000.0000,2.2970,86.1000),(2288,'US','OH',NULL,1404198000,0,20000.0000,2.8710,200.9500),(2289,'US','OH',NULL,1404198000,0,40000.0000,3.4450,344.5000),(2290,'US','OH',NULL,1404198000,0,80000.0000,4.0190,1033.5000),(2291,'US','OH',NULL,1404198000,0,100000.0000,4.5930,2641.1000),(2292,'US','OH',NULL,1404198000,0,100000.0000,5.7410,3559.7000),(2293,'US','NE',NULL,1357027200,10,2975.0000,0.0000,0.0000),(2294,'US','NE',NULL,1357027200,10,5325.0000,2.2600,0.0000),(2295,'US','NE',NULL,1357027200,10,17275.0000,3.2200,53.1100),(2296,'US','NE',NULL,1357027200,10,25025.0000,4.9100,437.9000),(2297,'US','NE',NULL,1357027200,10,31775.0000,6.2000,818.4300),(2298,'US','NE',NULL,1357027200,10,59675.0000,6.5900,1236.9300),(2299,'US','NE',NULL,1357027200,10,59675.0000,6.9500,3075.5400),(2300,'US','NE',NULL,1357027200,20,7100.0000,0.0000,0.0000),(2301,'US','NE',NULL,1357027200,20,10300.0000,2.2600,0.0000),(2302,'US','NE',NULL,1357027200,20,25650.0000,3.2200,72.3200),(2303,'US','NE',NULL,1357027200,20,39900.0000,4.9100,566.5900),(2304,'US','NE',NULL,1357027200,20,49500.0000,6.2000,1266.2700),(2305,'US','NE',NULL,1357027200,20,65650.0000,6.5900,1861.4700),(2306,'US','NE',NULL,1357027200,20,65650.0000,6.9500,2925.7600),(2307,'US','NE',NULL,1357027200,30,7100.0000,0.0000,0.0000),(2308,'US','NE',NULL,1357027200,30,10300.0000,2.2600,0.0000),(2309,'US','NE',NULL,1357027200,30,25650.0000,3.2200,72.3200),(2310,'US','NE',NULL,1357027200,30,39900.0000,4.9100,566.5900),(2311,'US','NE',NULL,1357027200,30,49500.0000,6.2000,1266.2700),(2312,'US','NE',NULL,1357027200,30,65650.0000,6.5900,1861.4700),(2313,'US','NE',NULL,1357027200,30,65650.0000,6.9500,2925.7600),(2314,'US','NE',NULL,1357027200,40,2975.0000,0.0000,0.0000),(2315,'US','NE',NULL,1357027200,40,5325.0000,2.2600,0.0000),(2316,'US','NE',NULL,1357027200,40,17275.0000,3.2200,53.1100),(2317,'US','NE',NULL,1357027200,40,25025.0000,4.9100,437.9000),(2318,'US','NE',NULL,1357027200,40,31775.0000,6.2000,818.4300),(2319,'US','NE',NULL,1357027200,40,59675.0000,6.5900,1236.9300),(2320,'US','NE',NULL,1357027200,40,59675.0000,6.9500,3075.5400),(2321,'US','NM',NULL,1388563200,10,2250.0000,0.0000,0.0000),(2322,'US','NM',NULL,1388563200,10,7750.0000,1.7000,0.0000),(2323,'US','NM',NULL,1388563200,10,13250.0000,3.2000,93.5000),(2324,'US','NM',NULL,1388563200,10,18250.0000,4.7000,269.5000),(2325,'US','NM',NULL,1388563200,10,18250.0000,4.9000,504.5000),(2326,'US','NM',NULL,1388563200,20,8450.0000,0.0000,0.0000),(2327,'US','NM',NULL,1388563200,20,16450.0000,1.7000,0.0000),(2328,'US','NM',NULL,1388563200,20,24450.0000,3.2000,136.0000),(2329,'US','NM',NULL,1388563200,20,32450.0000,4.7000,392.0000),(2330,'US','NM',NULL,1388563200,20,32450.0000,4.9000,768.0000),(2331,'US',NULL,NULL,1420099200,10,2300.0000,0.0000,0.0000),(2332,'US',NULL,NULL,1420099200,10,11525.0000,10.0000,0.0000),(2333,'US',NULL,NULL,1420099200,10,39750.0000,15.0000,922.5000),(2334,'US',NULL,NULL,1420099200,10,93050.0000,25.0000,5156.2500),(2335,'US',NULL,NULL,1420099200,10,191600.0000,28.0000,18481.2500),(2336,'US',NULL,NULL,1420099200,10,413800.0000,33.0000,46075.2500),(2337,'US',NULL,NULL,1420099200,10,415500.0000,35.0000,119401.2500),(2338,'US',NULL,NULL,1420099200,10,415500.0000,39.6000,119996.2500),(2339,'US',NULL,NULL,1420099200,20,8600.0000,0.0000,0.0000),(2340,'US',NULL,NULL,1420099200,20,27050.0000,10.0000,0.0000),(2341,'US',NULL,NULL,1420099200,20,83500.0000,15.0000,1845.0000),(2342,'US',NULL,NULL,1420099200,20,159800.0000,25.0000,10312.5000),(2343,'US',NULL,NULL,1420099200,20,239050.0000,28.0000,29387.5000),(2344,'US',NULL,NULL,1420099200,20,420100.0000,33.0000,51577.5000),(2345,'US',NULL,NULL,1420099200,20,473450.0000,35.0000,111324.0000),(2346,'US',NULL,NULL,1420099200,20,473450.0000,39.6000,129996.5000),(2347,'US','CA',NULL,1420099200,10,7749.0000,1.1000,0.0000),(2348,'US','CA',NULL,1420099200,10,18371.0000,2.2000,85.2400),(2349,'US','CA',NULL,1420099200,10,28995.0000,4.4000,318.9200),(2350,'US','CA',NULL,1420099200,10,40250.0000,6.6000,786.3800),(2351,'US','CA',NULL,1420099200,10,50869.0000,8.8000,1529.2100),(2352,'US','CA',NULL,1420099200,10,259844.0000,10.2300,2463.6800),(2353,'US','CA',NULL,1420099200,10,311812.0000,11.3300,23841.8200),(2354,'US','CA',NULL,1420099200,10,519687.0000,12.4300,29729.7900),(2355,'US','CA',NULL,1420099200,10,1000000.0000,13.5300,55568.6500),(2356,'US','CA',NULL,1420099200,10,1000000.0000,14.6300,120555.0000),(2357,'US','CA',NULL,1420099200,20,7749.0000,1.1000,0.0000),(2358,'US','CA',NULL,1420099200,20,18371.0000,2.2000,85.2400),(2359,'US','CA',NULL,1420099200,20,28995.0000,4.4000,318.9200),(2360,'US','CA',NULL,1420099200,20,40250.0000,6.6000,786.3800),(2361,'US','CA',NULL,1420099200,20,50869.0000,8.8000,1529.2100),(2362,'US','CA',NULL,1420099200,20,259844.0000,10.2300,2463.6800),(2363,'US','CA',NULL,1420099200,20,311812.0000,11.3300,23841.8200),(2364,'US','CA',NULL,1420099200,20,519687.0000,12.4300,29729.7900),(2365,'US','CA',NULL,1420099200,20,1000000.0000,13.5300,55568.6500),(2366,'US','CA',NULL,1420099200,20,1000000.0000,14.6300,120555.0000),(2367,'US','CA',NULL,1420099200,30,15498.0000,1.1000,0.0000),(2368,'US','CA',NULL,1420099200,30,36742.0000,2.2000,170.4800),(2369,'US','CA',NULL,1420099200,30,57990.0000,4.4000,637.8500),(2370,'US','CA',NULL,1420099200,30,80500.0000,6.6000,1572.7600),(2371,'US','CA',NULL,1420099200,30,101738.0000,8.8000,3058.4200),(2372,'US','CA',NULL,1420099200,30,519688.0000,10.2300,4927.3600),(2373,'US','CA',NULL,1420099200,30,623624.0000,11.3300,47683.6500),(2374,'US','CA',NULL,1420099200,30,1000000.0000,12.4300,59459.6000),(2375,'US','CA',NULL,1420099200,30,1039000.0000,13.5300,106243.1400),(2376,'US','CA',NULL,1420099200,30,1039000.0000,14.6300,111570.4400),(2377,'US','CA',NULL,1420099200,40,15498.0000,1.1000,0.0000),(2378,'US','CA',NULL,1420099200,40,36742.0000,2.2000,170.4800),(2379,'US','CA',NULL,1420099200,40,57990.0000,4.4000,637.8500),(2380,'US','CA',NULL,1420099200,40,80500.0000,6.6000,1572.7600),(2381,'US','CA',NULL,1420099200,40,101738.0000,8.8000,3058.4200),(2382,'US','CA',NULL,1420099200,40,519688.0000,10.2300,4927.3600),(2383,'US','CA',NULL,1420099200,40,623624.0000,11.3300,47683.6500),(2384,'US','CA',NULL,1420099200,40,1000000.0000,12.4300,59459.6000),(2385,'US','CA',NULL,1420099200,40,1039000.0000,13.5300,106243.1400),(2386,'US','CA',NULL,1420099200,40,1039000.0000,14.6300,111570.4400),(2387,'US','MN',NULL,1420099200,10,2300.0000,0.0000,0.0000),(2388,'US','MN',NULL,1420099200,10,27370.0000,5.3500,0.0000),(2389,'US','MN',NULL,1420099200,10,84660.0000,7.0500,1341.2500),(2390,'US','MN',NULL,1420099200,10,157250.0000,7.8500,5380.2000),(2391,'US','MN',NULL,1420099200,10,157250.0000,9.8500,11078.5200),(2392,'US','MN',NULL,1420099200,20,8600.0000,0.0000,0.0000),(2393,'US','MN',NULL,1420099200,20,45250.0000,5.3500,0.0000),(2394,'US','MN',NULL,1420099200,20,154220.0000,7.0500,1960.7800),(2395,'US','MN',NULL,1420099200,20,266860.0000,7.8500,9643.1700),(2396,'US','MN',NULL,1420099200,20,266860.0000,9.8500,18485.4100),(2397,'US','OK',NULL,1420099200,10,6300.0000,0.0000,0.0000),(2398,'US','OK',NULL,1420099200,10,7300.0000,0.5000,0.0000),(2399,'US','OK',NULL,1420099200,10,8800.0000,1.0000,5.0000),(2400,'US','OK',NULL,1420099200,10,10050.0000,2.0000,20.0000),(2401,'US','OK',NULL,1420099200,10,11200.0000,3.0000,45.0000),(2402,'US','OK',NULL,1420099200,10,13500.0000,4.0000,79.5000),(2403,'US','OK',NULL,1420099200,10,15000.0000,5.0000,171.5000),(2404,'US','OK',NULL,1420099200,10,15000.0000,5.2500,246.5000),(2405,'US','OK',NULL,1420099200,20,12600.0000,0.0000,0.0000),(2406,'US','OK',NULL,1420099200,20,14600.0000,0.5000,0.0000),(2407,'US','OK',NULL,1420099200,20,17600.0000,1.0000,10.0000),(2408,'US','OK',NULL,1420099200,20,20100.0000,2.0000,40.0000),(2409,'US','OK',NULL,1420099200,20,22400.0000,3.0000,90.0000),(2410,'US','OK',NULL,1420099200,20,24800.0000,4.0000,159.0000),(2411,'US','OK',NULL,1420099200,20,27600.0000,5.0000,255.0000),(2412,'US','OK',NULL,1420099200,20,27600.0000,5.2500,395.0000),(2413,'US','CO',NULL,1420099200,10,2300.0000,0.0000,0.0000),(2414,'US','CO',NULL,1420099200,10,2300.0000,4.6300,0.0000),(2415,'US','CO',NULL,1420099200,20,8600.0000,0.0000,0.0000),(2416,'US','CO',NULL,1420099200,20,8600.0000,4.6300,0.0000),(2417,'US','KS',NULL,1420099200,10,3000.0000,0.0000,0.0000),(2418,'US','KS',NULL,1420099200,10,18000.0000,2.7000,0.0000),(2419,'US','KS',NULL,1420099200,10,18000.0000,4.6000,405.0000),(2420,'US','KS',NULL,1420099200,20,6000.0000,0.0000,0.0000),(2421,'US','KS',NULL,1420099200,20,36000.0000,2.7000,0.0000),(2422,'US','KS',NULL,1420099200,20,36000.0000,4.6000,810.0000),(2423,'US','ME',NULL,1420099200,10,8650.0000,0.0000,0.0000),(2424,'US','ME',NULL,1420099200,10,24350.0000,6.5000,0.0000),(2425,'US','ME',NULL,1420099200,10,24350.0000,7.9500,1020.5000),(2426,'US','ME',NULL,1420099200,20,20200.0000,0.0000,0.0000),(2427,'US','ME',NULL,1420099200,20,51600.0000,6.5000,0.0000),(2428,'US','ME',NULL,1420099200,20,51600.0000,7.9500,2041.0000),(2429,'US','NY',NULL,1420099200,10,8400.0000,4.0000,0.0000),(2430,'US','NY',NULL,1420099200,10,11600.0000,4.5000,336.0000),(2431,'US','NY',NULL,1420099200,10,13750.0000,5.2500,480.0000),(2432,'US','NY',NULL,1420099200,10,21150.0000,5.9000,593.0000),(2433,'US','NY',NULL,1420099200,10,79600.0000,6.4500,1029.0000),(2434,'US','NY',NULL,1420099200,10,95550.0000,6.6500,4800.0000),(2435,'US','NY',NULL,1420099200,10,106200.0000,7.5800,5860.0000),(2436,'US','NY',NULL,1420099200,10,159350.0000,8.0800,6667.0000),(2437,'US','NY',NULL,1420099200,10,212500.0000,7.1500,10962.0000),(2438,'US','NY',NULL,1420099200,10,265600.0000,8.1500,14762.0000),(2439,'US','NY',NULL,1420099200,10,1062650.0000,7.3500,19090.0000),(2440,'US','NY',NULL,1420099200,10,1115850.0000,49.0200,77673.0000),(2441,'US','NY',NULL,1420099200,10,1115850.0000,9.6200,103752.0000),(2442,'US','NY',NULL,1420099200,20,8400.0000,4.0000,0.0000),(2443,'US','NY',NULL,1420099200,20,11600.0000,4.5000,336.0000),(2444,'US','NY',NULL,1420099200,20,13750.0000,5.2500,480.0000),(2445,'US','NY',NULL,1420099200,20,21150.0000,5.9000,593.0000),(2446,'US','NY',NULL,1420099200,20,79600.0000,6.4500,1029.0000),(2447,'US','NY',NULL,1420099200,20,95550.0000,6.6500,4800.0000),(2448,'US','NY',NULL,1420099200,20,106200.0000,7.2800,5860.0000),(2449,'US','NY',NULL,1420099200,20,159350.0000,7.7800,6635.0000),(2450,'US','NY',NULL,1420099200,20,212500.0000,8.0800,10771.0000),(2451,'US','NY',NULL,1420099200,20,318750.0000,7.1500,15065.0000),(2452,'US','NY',NULL,1420099200,20,371900.0000,8.1500,22662.0000),(2453,'US','NY',NULL,1420099200,20,1062650.0000,7.3500,26994.0000),(2454,'US','NY',NULL,1420099200,20,2125450.0000,7.6500,77764.0000),(2455,'US','NY',NULL,1420099200,20,2178650.0000,88.4200,159068.0000),(2456,'US','NY',NULL,1420099200,20,2178650.0000,9.6200,206107.0000),(2457,'US','NY','YONKERS',1420099200,10,8400.0000,4.0000,0.0000),(2458,'US','NY','YONKERS',1420099200,10,11600.0000,4.5000,336.0000),(2459,'US','NY','YONKERS',1420099200,10,13750.0000,5.2500,480.0000),(2460,'US','NY','YONKERS',1420099200,10,21150.0000,5.9000,593.0000),(2461,'US','NY','YONKERS',1420099200,10,79600.0000,6.4500,1029.0000),(2462,'US','NY','YONKERS',1420099200,10,95550.0000,6.6500,4800.0000),(2463,'US','NY','YONKERS',1420099200,10,106200.0000,7.5800,5860.0000),(2464,'US','NY','YONKERS',1420099200,10,159350.0000,8.0800,6667.0000),(2465,'US','NY','YONKERS',1420099200,10,212500.0000,7.1500,10962.0000),(2466,'US','NY','YONKERS',1420099200,10,265600.0000,8.1500,14762.0000),(2467,'US','NY','YONKERS',1420099200,10,1062650.0000,7.3500,19090.0000),(2468,'US','NY','YONKERS',1420099200,10,1115850.0000,49.0200,77673.0000),(2469,'US','NY','YONKERS',1420099200,10,1115850.0000,9.6200,103752.0000),(2470,'US','NY','YONKERS',1420099200,20,8400.0000,4.0000,0.0000),(2471,'US','NY','YONKERS',1420099200,20,11600.0000,4.5000,336.0000),(2472,'US','NY','YONKERS',1420099200,20,13750.0000,5.2500,480.0000),(2473,'US','NY','YONKERS',1420099200,20,21150.0000,5.9000,593.0000),(2474,'US','NY','YONKERS',1420099200,20,79600.0000,6.4500,1029.0000),(2475,'US','NY','YONKERS',1420099200,20,95550.0000,6.6500,4800.0000),(2476,'US','NY','YONKERS',1420099200,20,106200.0000,7.2800,5860.0000),(2477,'US','NY','YONKERS',1420099200,20,159350.0000,7.7800,6635.0000),(2478,'US','NY','YONKERS',1420099200,20,212500.0000,8.0800,10771.0000),(2479,'US','NY','YONKERS',1420099200,20,318750.0000,7.1500,15065.0000),(2480,'US','NY','YONKERS',1420099200,20,371900.0000,8.1500,22662.0000),(2481,'US','NY','YONKERS',1420099200,20,1062650.0000,7.3500,26994.0000),(2482,'US','NY','YONKERS',1420099200,20,2125450.0000,7.6500,77764.0000),(2483,'US','NY','YONKERS',1420099200,20,2178650.0000,88.4200,159068.0000),(2484,'US','NY','YONKERS',1420099200,20,2178650.0000,9.6200,206107.0000),(2485,'US','ND',NULL,1420099200,10,4300.0000,0.0000,0.0000),(2486,'US','ND',NULL,1420099200,10,41000.0000,1.2200,0.0000),(2487,'US','ND',NULL,1420099200,10,83000.0000,2.2700,447.7400),(2488,'US','ND',NULL,1420099200,10,191000.0000,2.5200,1401.1400),(2489,'US','ND',NULL,1420099200,10,411000.0000,2.9300,4122.7400),(2490,'US','ND',NULL,1420099200,10,411000.0000,3.2200,10568.0000),(2491,'US','ND',NULL,1420099200,20,10000.0000,0.0000,0.0000),(2492,'US','ND',NULL,1420099200,20,71000.0000,1.2200,0.0000),(2493,'US','ND',NULL,1420099200,20,134000.0000,2.2700,744.2000),(2494,'US','ND',NULL,1420099200,20,239000.0000,2.5200,2174.3000),(2495,'US','ND',NULL,1420099200,20,418000.0000,2.9300,4820.3000),(2496,'US','ND',NULL,1420099200,20,418000.0000,3.2200,10065.0000),(2497,'US','OR',NULL,1420099200,10,3350.0000,5.0000,0.0000),(2498,'US','OR',NULL,1420099200,10,8400.0000,7.0000,167.5000),(2499,'US','OR',NULL,1420099200,10,125000.0000,9.0000,521.0000),(2500,'US','OR',NULL,1420099200,10,125000.0000,9.9000,11015.0000),(2501,'US','OR',NULL,1420099200,20,6700.0000,5.0000,0.0000),(2502,'US','OR',NULL,1420099200,20,16800.0000,7.0000,335.0000),(2503,'US','OR',NULL,1420099200,20,250000.0000,9.0000,1042.0000),(2504,'US','OR',NULL,1420099200,20,250000.0000,9.9000,22030.0000),(2505,'US','RI',NULL,1420099200,10,60000.0000,3.7500,0.0000),(2506,'US','RI',NULL,1420099200,10,137650.0000,4.7500,2270.6300),(2507,'US','RI',NULL,1420099200,10,137650.0000,5.9900,5932.8800),(2508,'US','RI',NULL,1420099200,20,60000.0000,3.7500,0.0000),(2509,'US','RI',NULL,1420099200,20,137650.0000,4.7500,2270.6300),(2510,'US','RI',NULL,1420099200,20,137650.0000,5.9900,5932.8800),(2511,'US','AR',NULL,1420099200,0,4300.0000,0.9000,0.0000),(2512,'US','AR',NULL,1420099200,0,8400.0000,2.4000,38.7000),(2513,'US','AR',NULL,1420099200,0,12600.0000,3.4000,137.1000),(2514,'US','AR',NULL,1420099200,0,21000.0000,4.4000,279.9000),(2515,'US','AR',NULL,1420099200,0,35100.0000,5.9000,649.5000),(2516,'US','AR',NULL,1420099200,0,35100.0000,6.9000,1481.4000);
/*!40000 ALTER TABLE `income_tax_rate_us` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi`
--

DROP TABLE IF EXISTS `kpi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `minimum_rate` decimal(9,2) DEFAULT NULL,
  `maximum_rate` decimal(9,2) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_id` (`id`),
  KEY `kpi_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi`
--

LOCK TABLES `kpi` WRITE;
/*!40000 ALTER TABLE `kpi` DISABLE KEYS */;
/*!40000 ALTER TABLE `kpi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_group`
--

DROP TABLE IF EXISTS `kpi_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_group_id` (`id`),
  KEY `kpi_group_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_group`
--

LOCK TABLES `kpi_group` WRITE;
/*!40000 ALTER TABLE `kpi_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `kpi_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_group_id_seq`
--

DROP TABLE IF EXISTS `kpi_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_group_id_seq`
--

LOCK TABLES `kpi_group_id_seq` WRITE;
/*!40000 ALTER TABLE `kpi_group_id_seq` DISABLE KEYS */;
INSERT INTO `kpi_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `kpi_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_group_tree`
--

DROP TABLE IF EXISTS `kpi_group_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_group_tree` (
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `left_id` bigint(20) NOT NULL DEFAULT '0',
  `right_id` bigint(20) NOT NULL DEFAULT '0',
  KEY `kpi_group_tree_left_id_right_id` (`left_id`,`right_id`),
  KEY `kpi_group_tree_tree_id_object_id` (`tree_id`,`object_id`),
  KEY `kpi_group_tree_tree_id_parent_id` (`tree_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_group_tree`
--

LOCK TABLES `kpi_group_tree` WRITE;
/*!40000 ALTER TABLE `kpi_group_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `kpi_group_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_id_seq`
--

DROP TABLE IF EXISTS `kpi_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_id_seq`
--

LOCK TABLES `kpi_id_seq` WRITE;
/*!40000 ALTER TABLE `kpi_id_seq` DISABLE KEYS */;
INSERT INTO `kpi_id_seq` VALUES (1);
/*!40000 ALTER TABLE `kpi_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_policy`
--

DROP TABLE IF EXISTS `meal_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meal_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `trigger_time` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `start_window` int(11) DEFAULT NULL,
  `window_length` int(11) DEFAULT NULL,
  `include_lunch_punch_time` smallint(6) DEFAULT NULL,
  `auto_detect_type_id` int(11) NOT NULL DEFAULT '10',
  `minimum_punch_time` int(11) DEFAULT NULL,
  `maximum_punch_time` int(11) DEFAULT NULL,
  `pay_code_id` int(11) DEFAULT '0',
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `branch_id` int(11) DEFAULT '0',
  `department_id` int(11) DEFAULT '0',
  `job_id` int(11) DEFAULT '0',
  `job_item_id` int(11) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meal_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_policy`
--

LOCK TABLES `meal_policy` WRITE;
/*!40000 ALTER TABLE `meal_policy` DISABLE KEYS */;
INSERT INTO `meal_policy` VALUES (2,2,'30min Lunch',20,1800,18000,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL,NULL,0,20,1200,2400,4,0,0,0,0,0,NULL),(3,2,'60min Lunch',20,3600,25200,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL,NULL,0,20,2700,4500,4,0,0,0,0,0,NULL);
/*!40000 ALTER TABLE `meal_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_policy_id_seq`
--

DROP TABLE IF EXISTS `meal_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meal_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_policy_id_seq`
--

LOCK TABLES `meal_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `meal_policy_id_seq` DISABLE KEYS */;
INSERT INTO `meal_policy_id_seq` VALUES (3);
/*!40000 ALTER TABLE `meal_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `object_type_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `priority_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `status_date` int(11) DEFAULT NULL,
  `subject` varchar(250) DEFAULT NULL,
  `body` text,
  `require_ack` tinyint(1) DEFAULT '0',
  `ack` tinyint(1) DEFAULT NULL,
  `ack_date` int(11) DEFAULT NULL,
  `ack_by` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id` (`id`),
  KEY `message_created_by` (`created_by`),
  KEY `message_created_by_parent_id` (`created_by`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_control`
--

DROP TABLE IF EXISTS `message_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_control` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_type_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `require_ack` smallint(6) NOT NULL DEFAULT '0',
  `priority_id` smallint(6) NOT NULL DEFAULT '0',
  `subject` varchar(250) DEFAULT NULL,
  `body` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `message_control_id` (`id`),
  KEY `message_control_object_type_id_object_id` (`object_type_id`,`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_control`
--

LOCK TABLES `message_control` WRITE;
/*!40000 ALTER TABLE `message_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_control_id_seq`
--

DROP TABLE IF EXISTS `message_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_control_id_seq`
--

LOCK TABLES `message_control_id_seq` WRITE;
/*!40000 ALTER TABLE `message_control_id_seq` DISABLE KEYS */;
INSERT INTO `message_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `message_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_id_seq`
--

DROP TABLE IF EXISTS `message_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_id_seq`
--

LOCK TABLES `message_id_seq` WRITE;
/*!40000 ALTER TABLE `message_id_seq` DISABLE KEYS */;
INSERT INTO `message_id_seq` VALUES (1);
/*!40000 ALTER TABLE `message_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_recipient`
--

DROP TABLE IF EXISTS `message_recipient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_recipient` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message_sender_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `status_date` int(11) DEFAULT NULL,
  `ack` smallint(6) DEFAULT NULL,
  `ack_date` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `message_recipient_id` (`id`),
  KEY `message_recipient_user_id` (`user_id`),
  KEY `message_recipient_message_sender_id` (`message_sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_recipient`
--

LOCK TABLES `message_recipient` WRITE;
/*!40000 ALTER TABLE `message_recipient` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_recipient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_recipient_id_seq`
--

DROP TABLE IF EXISTS `message_recipient_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_recipient_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_recipient_id_seq`
--

LOCK TABLES `message_recipient_id_seq` WRITE;
/*!40000 ALTER TABLE `message_recipient_id_seq` DISABLE KEYS */;
INSERT INTO `message_recipient_id_seq` VALUES (1);
/*!40000 ALTER TABLE `message_recipient_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_sender`
--

DROP TABLE IF EXISTS `message_sender`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_sender` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `message_control_id` int(11) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `message_sender_id` (`id`),
  KEY `message_sender_user_id` (`user_id`),
  KEY `message_sender_message_control_id` (`message_control_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_sender`
--

LOCK TABLES `message_sender` WRITE;
/*!40000 ALTER TABLE `message_sender` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_sender` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_sender_id_seq`
--

DROP TABLE IF EXISTS `message_sender_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_sender_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_sender_id_seq`
--

LOCK TABLES `message_sender_id_seq` WRITE;
/*!40000 ALTER TABLE `message_sender_id_seq` DISABLE KEYS */;
INSERT INTO `message_sender_id_seq` VALUES (1);
/*!40000 ALTER TABLE `message_sender_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `other_field`
--

DROP TABLE IF EXISTS `other_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `other_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  `other_id6` varchar(250) DEFAULT NULL,
  `other_id7` varchar(250) DEFAULT NULL,
  `other_id8` varchar(250) DEFAULT NULL,
  `other_id9` varchar(250) DEFAULT NULL,
  `other_id10` varchar(250) DEFAULT NULL,
  `required_other_id1` tinyint(1) DEFAULT '0',
  `required_other_id2` tinyint(1) DEFAULT '0',
  `required_other_id3` tinyint(1) DEFAULT '0',
  `required_other_id4` tinyint(1) DEFAULT '0',
  `required_other_id5` tinyint(1) DEFAULT '0',
  `required_other_id6` tinyint(1) DEFAULT '0',
  `required_other_id7` tinyint(1) DEFAULT '0',
  `required_other_id8` tinyint(1) DEFAULT '0',
  `required_other_id9` tinyint(1) DEFAULT '0',
  `required_other_id10` tinyint(1) DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `other_field_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `other_field`
--

LOCK TABLES `other_field` WRITE;
/*!40000 ALTER TABLE `other_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `other_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `other_field_id_seq`
--

DROP TABLE IF EXISTS `other_field_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `other_field_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `other_field_id_seq`
--

LOCK TABLES `other_field_id_seq` WRITE;
/*!40000 ALTER TABLE `other_field_id_seq` DISABLE KEYS */;
INSERT INTO `other_field_id_seq` VALUES (1);
/*!40000 ALTER TABLE `other_field_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `over_time_policy`
--

DROP TABLE IF EXISTS `over_time_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `over_time_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `trigger_time` int(11) NOT NULL,
  `rate` decimal(9,4) DEFAULT NULL,
  `accrual_policy_id` int(11) DEFAULT NULL,
  `accrual_rate` decimal(9,4) DEFAULT NULL,
  `pay_stub_entry_account_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `wage_group_id` int(11) NOT NULL DEFAULT '0',
  `pay_code_id` int(11) DEFAULT '0',
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `contributing_shift_policy_id` int(11) DEFAULT '0',
  `branch_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_branch` smallint(6) DEFAULT '0',
  `department_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_department` smallint(6) DEFAULT '0',
  `job_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job` smallint(6) DEFAULT '0',
  `job_item_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_item_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job_item` smallint(6) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `over_time_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `over_time_policy`
--

LOCK TABLES `over_time_policy` WRITE;
/*!40000 ALTER TABLE `over_time_policy` DISABLE KEYS */;
INSERT INTO `over_time_policy` VALUES (2,2,'US - Holiday',180,0,0.0000,NULL,0.0000,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0,0,6,0,5,10,0,10,0,10,10,0,10,10,0,NULL),(3,2,'AL - Weekly >40hrs',20,144000,0.0000,NULL,0.0000,NULL,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,6,0,5,10,0,10,0,10,10,0,10,10,0,NULL);
/*!40000 ALTER TABLE `over_time_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `over_time_policy_id_seq`
--

DROP TABLE IF EXISTS `over_time_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `over_time_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `over_time_policy_id_seq`
--

LOCK TABLES `over_time_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `over_time_policy_id_seq` DISABLE KEYS */;
INSERT INTO `over_time_policy_id_seq` VALUES (3);
/*!40000 ALTER TABLE `over_time_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_code`
--

DROP TABLE IF EXISTS `pay_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `code` varchar(250) NOT NULL,
  `type_id` smallint(6) NOT NULL,
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `pay_stub_entry_account_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_code_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_code`
--

LOCK TABLES `pay_code` WRITE;
/*!40000 ALTER TABLE `pay_code` DISABLE KEYS */;
INSERT INTO `pay_code` VALUES (2,2,'UnPaid',NULL,'UNPAID',20,2,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(3,2,'Regular Time',NULL,'REG',10,3,2,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(4,2,'Lunch Time',NULL,'LNH',10,3,2,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(5,2,'Break Time',NULL,'BRK',10,3,2,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(6,2,'OverTime (1.5x)',NULL,'OT1',10,4,3,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(7,2,'OverTime (2.0x)',NULL,'OT1',10,5,4,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(8,2,'Premium 1',NULL,'PRE1',10,0,5,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(9,2,'Premium 2',NULL,'PRE2',10,0,6,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(10,2,'Sick',NULL,'SICK',10,6,8,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(11,2,'Sick (UNPAID)',NULL,'USICK',20,2,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(12,2,'Time Bank',NULL,'BANK',20,7,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(13,2,'Statutory Holiday',NULL,'STAT',10,3,7,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(14,2,'Vacation (UNPAID)',NULL,'UVAC',20,2,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(15,2,'Jury Duty',NULL,'JURY',20,2,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(16,2,'Bereavement',NULL,'BEREAV',20,2,NULL,1423636844,NULL,1423636844,NULL,NULL,NULL,0),(17,2,'Vacation',NULL,'VAC',10,8,46,1423636844,NULL,1423636844,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `pay_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_code_id_seq`
--

DROP TABLE IF EXISTS `pay_code_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_code_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_code_id_seq`
--

LOCK TABLES `pay_code_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_code_id_seq` DISABLE KEYS */;
INSERT INTO `pay_code_id_seq` VALUES (17);
/*!40000 ALTER TABLE `pay_code_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_formula_policy`
--

DROP TABLE IF EXISTS `pay_formula_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_formula_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `wage_source_type_id` smallint(6) DEFAULT '10',
  `wage_source_contributing_shift_policy_id` int(11) NOT NULL DEFAULT '0',
  `time_source_contributing_shift_policy_id` int(11) NOT NULL DEFAULT '0',
  `wage_group_id` int(11) DEFAULT NULL,
  `pay_type_id` smallint(6) DEFAULT '10',
  `rate` decimal(9,4) DEFAULT NULL,
  `custom_formula` varchar(250) DEFAULT NULL,
  `accrual_policy_account_id` int(11) DEFAULT NULL,
  `accrual_rate` decimal(9,4) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_formula_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_formula_policy`
--

LOCK TABLES `pay_formula_policy` WRITE;
/*!40000 ALTER TABLE `pay_formula_policy` DISABLE KEYS */;
INSERT INTO `pay_formula_policy` VALUES (2,2,'None ($0)',NULL,10,0,0,0,10,0.0000,NULL,NULL,0.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(3,2,'Regular',NULL,10,0,0,0,10,1.0000,NULL,NULL,0.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(4,2,'OverTime (1.5x)',NULL,10,0,0,0,10,1.5000,NULL,NULL,0.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(5,2,'OverTime (2.0x)',NULL,10,0,0,0,10,2.0000,NULL,NULL,0.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(6,2,'Sick',NULL,10,0,0,0,10,1.0000,NULL,0,1.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(7,2,'Time Bank',NULL,10,0,0,0,10,0.0000,NULL,2,1.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0),(8,2,'Vacation',NULL,10,0,0,0,10,1.0000,NULL,0,1.0000,1423636843,NULL,1423636843,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `pay_formula_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_formula_policy_id_seq`
--

DROP TABLE IF EXISTS `pay_formula_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_formula_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_formula_policy_id_seq`
--

LOCK TABLES `pay_formula_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_formula_policy_id_seq` DISABLE KEYS */;
INSERT INTO `pay_formula_policy_id_seq` VALUES (8);
/*!40000 ALTER TABLE `pay_formula_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period`
--

DROP TABLE IF EXISTS `pay_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `pay_period_schedule_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `advance_end_date` timestamp NULL DEFAULT NULL,
  `advance_transaction_date` timestamp NULL DEFAULT NULL,
  `tainted` tinyint(1) DEFAULT '0',
  `tainted_by` int(11) DEFAULT NULL,
  `tainted_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_period_id` (`id`),
  KEY `pay_period_pay_period_schedule_id` (`pay_period_schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period`
--

LOCK TABLES `pay_period` WRITE;
/*!40000 ALTER TABLE `pay_period` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_period` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_id_seq`
--

DROP TABLE IF EXISTS `pay_period_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_id_seq`
--

LOCK TABLES `pay_period_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_period_id_seq` DISABLE KEYS */;
INSERT INTO `pay_period_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_period_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_schedule`
--

DROP TABLE IF EXISTS `pay_period_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `primary_date_ldom` tinyint(1) DEFAULT NULL,
  `primary_transaction_date_ldom` tinyint(1) DEFAULT NULL,
  `primary_transaction_date_bd` tinyint(1) DEFAULT NULL,
  `secondary_date_ldom` tinyint(1) DEFAULT NULL,
  `secondary_transaction_date_ldom` tinyint(1) DEFAULT NULL,
  `secondary_transaction_date_bd` tinyint(1) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `anchor_date` timestamp NULL DEFAULT NULL,
  `primary_date` timestamp NULL DEFAULT NULL,
  `primary_transaction_date` timestamp NULL DEFAULT NULL,
  `secondary_date` timestamp NULL DEFAULT NULL,
  `secondary_transaction_date` timestamp NULL DEFAULT NULL,
  `day_start_time` int(11) DEFAULT NULL,
  `day_continuous_time` int(11) DEFAULT NULL,
  `start_week_day_id` int(11) DEFAULT NULL,
  `start_day_of_week` smallint(6) DEFAULT NULL,
  `transaction_date` smallint(6) DEFAULT NULL,
  `primary_day_of_month` smallint(6) DEFAULT NULL,
  `secondary_day_of_month` smallint(6) DEFAULT NULL,
  `primary_transaction_day_of_month` smallint(6) DEFAULT NULL,
  `secondary_transaction_day_of_month` smallint(6) DEFAULT NULL,
  `transaction_date_bd` smallint(6) DEFAULT NULL,
  `time_zone` varchar(250) DEFAULT NULL,
  `new_day_trigger_time` int(11) DEFAULT NULL,
  `maximum_shift_time` int(11) DEFAULT NULL,
  `shift_assigned_day_id` int(11) DEFAULT NULL,
  `timesheet_verify_before_end_date` int(11) DEFAULT NULL,
  `timesheet_verify_before_transaction_date` int(11) DEFAULT NULL,
  `timesheet_verify_notice_before_transaction_date` int(11) DEFAULT NULL,
  `timesheet_verify_notice_email` int(11) DEFAULT NULL,
  `annual_pay_periods` int(11) DEFAULT NULL,
  `timesheet_verify_type_id` int(11) DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_period_schedule_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_schedule`
--

LOCK TABLES `pay_period_schedule` WRITE;
/*!40000 ALTER TABLE `pay_period_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_period_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_schedule_id_seq`
--

DROP TABLE IF EXISTS `pay_period_schedule_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_schedule_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_schedule_id_seq`
--

LOCK TABLES `pay_period_schedule_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_period_schedule_id_seq` DISABLE KEYS */;
INSERT INTO `pay_period_schedule_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_period_schedule_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_schedule_user`
--

DROP TABLE IF EXISTS `pay_period_schedule_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_schedule_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_period_schedule_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_period_schedule_user_id` (`id`),
  KEY `pay_period_schedule_user_pay_period_schedule_id` (`pay_period_schedule_id`),
  KEY `pay_period_schedule_user_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_schedule_user`
--

LOCK TABLES `pay_period_schedule_user` WRITE;
/*!40000 ALTER TABLE `pay_period_schedule_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_period_schedule_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_schedule_user_id_seq`
--

DROP TABLE IF EXISTS `pay_period_schedule_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_schedule_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_schedule_user_id_seq`
--

LOCK TABLES `pay_period_schedule_user_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_period_schedule_user_id_seq` DISABLE KEYS */;
INSERT INTO `pay_period_schedule_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_period_schedule_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_time_sheet_verify`
--

DROP TABLE IF EXISTS `pay_period_time_sheet_verify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_time_sheet_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_period_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `authorized` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `authorization_level` smallint(6) DEFAULT '99',
  `user_verified` smallint(6) DEFAULT '0',
  `user_verified_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_period_time_sheet_verify_id` (`id`),
  KEY `pay_period_time_sheet_verify_user_id_pay_period_id` (`user_id`,`pay_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_time_sheet_verify`
--

LOCK TABLES `pay_period_time_sheet_verify` WRITE;
/*!40000 ALTER TABLE `pay_period_time_sheet_verify` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_period_time_sheet_verify` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_period_time_sheet_verify_id_seq`
--

DROP TABLE IF EXISTS `pay_period_time_sheet_verify_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_period_time_sheet_verify_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_period_time_sheet_verify_id_seq`
--

LOCK TABLES `pay_period_time_sheet_verify_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_period_time_sheet_verify_id_seq` DISABLE KEYS */;
INSERT INTO `pay_period_time_sheet_verify_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_period_time_sheet_verify_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub`
--

DROP TABLE IF EXISTS `pay_stub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_period_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL DEFAULT '0',
  `status_date` int(11) DEFAULT NULL,
  `status_by` int(11) DEFAULT NULL,
  `advance` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `tainted` tinyint(1) NOT NULL DEFAULT '0',
  `temp` tinyint(1) DEFAULT '0',
  `currency_id` int(11) DEFAULT NULL,
  `currency_rate` decimal(18,10) DEFAULT NULL,
  `confirm_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_stub_id` (`id`),
  KEY `pay_stub_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub`
--

LOCK TABLES `pay_stub` WRITE;
/*!40000 ALTER TABLE `pay_stub` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_stub` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_amendment`
--

DROP TABLE IF EXISTS `pay_stub_amendment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_amendment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_stub_entry_name_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '10',
  `effective_date` int(11) DEFAULT NULL,
  `rate` decimal(20,4) DEFAULT NULL,
  `units` decimal(20,4) DEFAULT NULL,
  `amount` decimal(20,4) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `authorized` tinyint(1) DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_ps_amendment_id` int(11) DEFAULT NULL,
  `ytd_adjustment` tinyint(1) DEFAULT '0',
  `type_id` int(11) NOT NULL,
  `percent_amount` decimal(20,4) DEFAULT NULL,
  `percent_amount_entry_name_id` int(11) DEFAULT NULL,
  `private_description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_stub_amendment_id` (`id`),
  KEY `pay_stub_amendment_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_amendment`
--

LOCK TABLES `pay_stub_amendment` WRITE;
/*!40000 ALTER TABLE `pay_stub_amendment` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_stub_amendment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_amendment_id_seq`
--

DROP TABLE IF EXISTS `pay_stub_amendment_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_amendment_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_amendment_id_seq`
--

LOCK TABLES `pay_stub_amendment_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_stub_amendment_id_seq` DISABLE KEYS */;
INSERT INTO `pay_stub_amendment_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_stub_amendment_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry`
--

DROP TABLE IF EXISTS `pay_stub_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_stub_id` int(11) NOT NULL,
  `rate` decimal(20,4) DEFAULT NULL,
  `units` decimal(20,4) DEFAULT NULL,
  `ytd_units` decimal(20,4) DEFAULT NULL,
  `amount` decimal(20,4) DEFAULT NULL,
  `ytd_amount` decimal(20,4) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `pay_stub_entry_name_id` int(11) NOT NULL,
  `pay_stub_amendment_id` int(11) DEFAULT NULL,
  `user_expense_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_stub_entry_id` (`id`),
  KEY `pay_stub_entry_pay_stub_id` (`pay_stub_id`),
  KEY `pay_stub_entry_name_id` (`pay_stub_entry_name_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry`
--

LOCK TABLES `pay_stub_entry` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry` DISABLE KEYS */;
/*!40000 ALTER TABLE `pay_stub_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry_account`
--

DROP TABLE IF EXISTS `pay_stub_entry_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `ps_order` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `accrual_pay_stub_entry_account_id` int(11) DEFAULT NULL,
  `debit_account` varchar(250) DEFAULT NULL,
  `credit_account` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `accrual_type_id` smallint(6) DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_stub_entry_account_id` (`id`),
  KEY `pay_stub_entry_account_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry_account`
--

LOCK TABLES `pay_stub_entry_account` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry_account` DISABLE KEYS */;
INSERT INTO `pay_stub_entry_account` VALUES (2,2,10,10,100,'Regular Time',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(3,2,10,10,120,'Over Time 1',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(4,2,10,10,121,'Over Time 2',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(5,2,10,10,130,'Premium 1',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(6,2,10,10,131,'Premium 2',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(7,2,10,10,140,'Statutory Holiday',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(8,2,10,10,142,'Sick',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(9,2,10,10,145,'Bereavement',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(10,2,10,10,146,'Jury Duty',NULL,NULL,NULL,1423636842,NULL,1423636842,NULL,NULL,NULL,0,10),(11,2,10,10,150,'Tips',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(12,2,10,10,152,'Commission',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(13,2,10,10,154,'Expense Reimbursement',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(14,2,10,10,156,'Bonus',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(15,2,10,10,160,'Severance',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(16,2,10,10,170,'Advance',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(17,2,10,20,250,'Health Benefits Plan',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(18,2,10,20,255,'Dental Benefits Plan',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(19,2,10,20,256,'Life Insurance',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(20,2,10,20,257,'Long Term Disability',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(21,2,10,20,258,'Accidental Death & Dismemberment',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(22,2,10,20,280,'Advance Paid',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(23,2,10,20,282,'Union Dues',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(24,2,10,20,289,'Garnishment',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(25,2,10,30,340,'Health Benefits Plan',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(26,2,10,30,341,'Dental Benefits Plan',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(27,2,10,30,346,'Life Insurance',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(28,2,10,30,347,'Long Term Disability',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(29,2,10,30,348,'Accidental Death & Dismemberment',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(30,2,10,50,497,'Loan Balance',0,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(31,2,10,10,197,'Loan',30,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(32,2,10,20,297,'Loan Repayment',30,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(33,2,10,40,199,'Total Gross',NULL,'','',1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(34,2,10,40,298,'Total Deductions',NULL,'','',1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(35,2,10,40,299,'Net Pay',NULL,'','',1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(36,2,10,40,399,'Employer Total Contributions',NULL,'','',1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(37,2,10,20,200,'US - Federal Income Tax',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(38,2,10,20,202,'Social Security (FICA)',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(39,2,10,30,302,'Social Security (FICA)',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(40,2,10,30,303,'US - Federal Unemployment Insurance',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(41,2,10,20,203,'Medicare',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(42,2,10,30,303,'Medicare',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(43,2,10,20,230,'401(k)',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(44,2,10,30,330,'401(k)',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(45,2,10,30,305,'Workers Compensation - Employer',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(46,2,10,10,181,'Vacation',NULL,NULL,NULL,1423636843,NULL,1423636843,NULL,NULL,NULL,0,10),(47,2,10,20,204,'AL - State Income Tax',NULL,NULL,NULL,1423636845,NULL,1423636845,NULL,NULL,NULL,0,10),(48,2,10,20,206,'AL - District Income Tax',NULL,NULL,NULL,1423636845,NULL,1423636845,NULL,NULL,NULL,0,10),(49,2,10,30,306,'AL - Unemployment Insurance',NULL,NULL,NULL,1423636845,NULL,1423636845,NULL,NULL,NULL,0,10),(50,2,10,30,310,'AL - Employment Security Assessment',NULL,NULL,NULL,1423636845,NULL,1423636845,NULL,NULL,NULL,0,10);
/*!40000 ALTER TABLE `pay_stub_entry_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry_account_id_seq`
--

DROP TABLE IF EXISTS `pay_stub_entry_account_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry_account_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry_account_id_seq`
--

LOCK TABLES `pay_stub_entry_account_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry_account_id_seq` DISABLE KEYS */;
INSERT INTO `pay_stub_entry_account_id_seq` VALUES (50);
/*!40000 ALTER TABLE `pay_stub_entry_account_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry_account_link`
--

DROP TABLE IF EXISTS `pay_stub_entry_account_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry_account_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `total_gross` int(11) DEFAULT NULL,
  `total_employee_deduction` int(11) DEFAULT NULL,
  `total_employer_deduction` int(11) DEFAULT NULL,
  `total_net_pay` int(11) DEFAULT NULL,
  `regular_time` int(11) DEFAULT NULL,
  `monthly_advance` int(11) DEFAULT NULL,
  `monthly_advance_deduction` int(11) DEFAULT NULL,
  `employee_cpp` int(11) DEFAULT NULL,
  `employee_ei` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_stub_entry_account_link_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry_account_link`
--

LOCK TABLES `pay_stub_entry_account_link` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry_account_link` DISABLE KEYS */;
INSERT INTO `pay_stub_entry_account_link` VALUES (2,2,33,34,36,35,2,NULL,NULL,NULL,NULL,1423636843,NULL,1423636845,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `pay_stub_entry_account_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry_account_link_id_seq`
--

DROP TABLE IF EXISTS `pay_stub_entry_account_link_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry_account_link_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry_account_link_id_seq`
--

LOCK TABLES `pay_stub_entry_account_link_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry_account_link_id_seq` DISABLE KEYS */;
INSERT INTO `pay_stub_entry_account_link_id_seq` VALUES (2);
/*!40000 ALTER TABLE `pay_stub_entry_account_link_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_entry_id_seq`
--

DROP TABLE IF EXISTS `pay_stub_entry_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_entry_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_entry_id_seq`
--

LOCK TABLES `pay_stub_entry_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_stub_entry_id_seq` DISABLE KEYS */;
INSERT INTO `pay_stub_entry_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_stub_entry_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_stub_id_seq`
--

DROP TABLE IF EXISTS `pay_stub_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_stub_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_stub_id_seq`
--

LOCK TABLES `pay_stub_id_seq` WRITE;
/*!40000 ALTER TABLE `pay_stub_id_seq` DISABLE KEYS */;
INSERT INTO `pay_stub_id_seq` VALUES (1);
/*!40000 ALTER TABLE `pay_stub_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_control_id` int(11) NOT NULL,
  `section` varchar(250) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `value` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `permission_permission_control_id` (`permission_control_id`)
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission`
--

LOCK TABLES `permission` WRITE;
/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` VALUES (1,2,'system','login','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(2,2,'user','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(3,2,'user','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(4,2,'user','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(5,2,'user','edit_own_password','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(6,2,'user','edit_own_phone_password','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(7,2,'user','edit_own_bank','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(8,2,'user','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(9,2,'user','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(10,2,'user','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(11,2,'user','edit_advanced','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(12,2,'user','enroll_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(13,2,'user','edit_pay_period_schedule','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(14,2,'user','edit_permission_group','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(15,2,'user','edit_policy_group','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(16,2,'user','edit_hierarchy','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(17,2,'user','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(18,2,'user','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(19,2,'user','enroll','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(20,2,'user','edit_bank','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(21,2,'user','view_sin','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(22,2,'user','timeclock_admin','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(23,2,'user_preference','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(24,2,'user_preference','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(25,2,'user_preference','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(26,2,'user_preference','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(27,2,'user_preference','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(28,2,'user_preference','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(29,2,'user_preference','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(30,2,'user_preference','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(31,2,'user_preference','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(32,2,'request','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(33,2,'request','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(34,2,'request','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(35,2,'request','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(36,2,'request','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(37,2,'request','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(38,2,'request','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(39,2,'request','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(40,2,'request','authorize','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(41,2,'request','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(42,2,'request','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(43,2,'request','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(44,2,'message','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(45,2,'message','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(46,2,'message','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(47,2,'message','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(48,2,'message','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(49,2,'message','add_advanced','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(50,2,'message','send_to_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(51,2,'message','send_to_any','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(52,2,'schedule','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(53,2,'schedule','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(54,2,'schedule','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(55,2,'schedule','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(56,2,'schedule','view_open','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(57,2,'schedule','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(58,2,'schedule','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(59,2,'schedule','edit_branch','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(60,2,'schedule','edit_department','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(61,2,'schedule','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(62,2,'schedule','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(63,2,'schedule','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(64,2,'accrual','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(65,2,'accrual','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(66,2,'accrual','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(67,2,'accrual','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(68,2,'accrual','edit_own','0',1423636842,NULL,NULL,NULL,NULL,NULL,0),(69,2,'accrual','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(70,2,'accrual','delete_own','0',1423636842,NULL,NULL,NULL,NULL,NULL,0),(71,2,'accrual','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(72,2,'accrual','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(73,2,'accrual','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(74,2,'accrual','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(75,2,'absence','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(76,2,'absence','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(77,2,'absence','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(78,2,'absence','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(79,2,'absence','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(80,2,'absence','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(81,2,'absence','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(82,2,'absence','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(83,2,'absence','edit_branch','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(84,2,'absence','edit_department','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(85,2,'absence','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(86,2,'absence','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(87,2,'absence','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(88,2,'punch','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(89,2,'punch','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(90,2,'punch','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(91,2,'punch','verify_time_sheet','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(92,2,'punch','punch_in_out','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(93,2,'punch','edit_transfer','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(94,2,'punch','edit_branch','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(95,2,'punch','edit_department','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(96,2,'punch','edit_note','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(97,2,'punch','edit_other_id1','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(98,2,'punch','edit_other_id2','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(99,2,'punch','edit_other_id3','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(100,2,'punch','edit_other_id4','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(101,2,'punch','edit_other_id5','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(102,2,'punch','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(103,2,'punch','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(104,2,'punch','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(105,2,'punch','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(106,2,'punch','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(107,2,'punch','authorize','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(108,2,'punch','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(109,2,'punch','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(110,2,'punch','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(111,2,'pay_stub','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(112,2,'pay_stub','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(113,2,'pay_stub','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(114,2,'pay_stub','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(115,2,'pay_stub','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(116,2,'pay_stub','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(117,2,'authorization','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(118,2,'authorization','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(119,2,'report','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(120,2,'report','view_user_information','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(121,2,'report','view_user_detail','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(122,2,'report','view_user_barcode','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(123,2,'report','view_schedule_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(124,2,'report','view_accrual_balance_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(125,2,'report','view_active_shift','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(126,2,'report','view_timesheet_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(127,2,'report','view_punch_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(128,2,'report','view_exception_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(129,2,'report','view_system_log','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(130,2,'report','view_employee_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(131,2,'report','view_pay_stub_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(132,2,'report','view_payroll_export','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(133,2,'report','view_remittance_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(134,2,'report','view_wages_payable_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(135,2,'report','view_t4_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(136,2,'report','view_generic_tax_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(137,2,'report','view_form941','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(138,2,'report','view_form940','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(139,2,'report','view_form940ez','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(140,2,'report','view_form1099misc','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(141,2,'report','view_formW2','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(142,2,'report','view_affordable_care','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(143,2,'report','view_general_ledger_summary','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(144,2,'report_custom_column','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(145,2,'report_custom_column','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(146,2,'report_custom_column','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(147,2,'report_custom_column','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(148,2,'report_custom_column','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(149,2,'report_custom_column','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(150,2,'report_custom_column','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(151,2,'report_custom_column','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(152,2,'recurring_schedule_template','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(153,2,'recurring_schedule_template','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(154,2,'recurring_schedule_template','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(155,2,'recurring_schedule_template','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(156,2,'recurring_schedule_template','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(157,2,'recurring_schedule_template','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(158,2,'recurring_schedule_template','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(159,2,'recurring_schedule_template','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(160,2,'recurring_schedule','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(161,2,'recurring_schedule','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(162,2,'recurring_schedule','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(163,2,'recurring_schedule','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(164,2,'recurring_schedule','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(165,2,'recurring_schedule','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(166,2,'recurring_schedule','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(167,2,'recurring_schedule','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(168,2,'user_contact','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(169,2,'user_contact','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(170,2,'user_contact','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(171,2,'user_contact','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(172,2,'user_contact','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(173,2,'user_contact','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(174,2,'user_contact','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(175,2,'user_contact','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(176,2,'qualification','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(177,2,'qualification','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(178,2,'qualification','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(179,2,'qualification','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(180,2,'qualification','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(181,2,'qualification','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(182,2,'qualification','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(183,2,'qualification','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(184,2,'qualification','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(185,2,'qualification','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(186,2,'user_education','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(187,2,'user_education','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(188,2,'user_education','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(189,2,'user_education','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(190,2,'user_education','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(191,2,'user_education','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(192,2,'user_education','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(193,2,'user_education','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(194,2,'user_education','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(195,2,'user_education','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(196,2,'user_license','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(197,2,'user_license','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(198,2,'user_license','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(199,2,'user_license','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(200,2,'user_license','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(201,2,'user_license','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(202,2,'user_license','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(203,2,'user_license','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(204,2,'user_license','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(205,2,'user_license','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(206,2,'user_skill','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(207,2,'user_skill','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(208,2,'user_skill','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(209,2,'user_skill','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(210,2,'user_skill','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(211,2,'user_skill','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(212,2,'user_skill','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(213,2,'user_skill','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(214,2,'user_skill','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(215,2,'user_skill','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(216,2,'user_membership','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(217,2,'user_membership','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(218,2,'user_membership','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(219,2,'user_membership','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(220,2,'user_membership','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(221,2,'user_membership','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(222,2,'user_membership','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(223,2,'user_membership','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(224,2,'user_membership','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(225,2,'user_membership','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(226,2,'user_language','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(227,2,'user_language','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(228,2,'user_language','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(229,2,'user_language','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(230,2,'user_language','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(231,2,'user_language','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(232,2,'user_language','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(233,2,'user_language','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(234,2,'user_language','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(235,2,'user_language','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(236,2,'kpi','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(237,2,'kpi','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(238,2,'kpi','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(239,2,'kpi','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(240,2,'kpi','delete_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(241,2,'kpi','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(242,2,'kpi','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(243,2,'kpi','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(244,2,'kpi','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(245,2,'kpi','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(246,2,'user_review','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(247,2,'user_review','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(248,2,'user_review','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(249,2,'user_review','view_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(250,2,'user_review','edit_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(251,2,'user_review','delete_child','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(252,2,'user_review','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(253,2,'user_review','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(254,2,'user_review','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(255,2,'hr_report','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(256,2,'hr_report','user_qualification','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(257,2,'hr_report','user_review','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(258,2,'company','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(259,2,'company','view_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(260,2,'company','edit_own','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(261,2,'company','edit_own_bank','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(262,2,'wage','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(263,2,'wage','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(264,2,'wage','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(265,2,'wage','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(266,2,'wage','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(267,2,'pay_period_schedule','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(268,2,'pay_period_schedule','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(269,2,'pay_period_schedule','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(270,2,'pay_period_schedule','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(271,2,'pay_period_schedule','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(272,2,'pay_period_schedule','assign','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(273,2,'pay_code','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(274,2,'pay_code','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(275,2,'pay_code','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(276,2,'pay_code','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(277,2,'pay_code','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(278,2,'pay_formula_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(279,2,'pay_formula_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(280,2,'pay_formula_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(281,2,'pay_formula_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(282,2,'pay_formula_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(283,2,'user_tax_deduction','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(284,2,'user_tax_deduction','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(285,2,'user_tax_deduction','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(286,2,'user_tax_deduction','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(287,2,'user_tax_deduction','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(288,2,'roe','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(289,2,'roe','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(290,2,'roe','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(291,2,'roe','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(292,2,'roe','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(293,2,'company_tax_deduction','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(294,2,'company_tax_deduction','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(295,2,'company_tax_deduction','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(296,2,'company_tax_deduction','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(297,2,'company_tax_deduction','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(298,2,'pay_stub_account','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(299,2,'pay_stub_account','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(300,2,'pay_stub_account','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(301,2,'pay_stub_account','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(302,2,'pay_stub_account','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(303,2,'pay_stub_amendment','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(304,2,'pay_stub_amendment','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(305,2,'pay_stub_amendment','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(306,2,'pay_stub_amendment','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(307,2,'pay_stub_amendment','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(308,2,'policy_group','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(309,2,'policy_group','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(310,2,'policy_group','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(311,2,'policy_group','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(312,2,'policy_group','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(313,2,'contributing_pay_code_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(314,2,'contributing_pay_code_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(315,2,'contributing_pay_code_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(316,2,'contributing_pay_code_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(317,2,'contributing_pay_code_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(318,2,'contributing_shift_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(319,2,'contributing_shift_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(320,2,'contributing_shift_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(321,2,'contributing_shift_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(322,2,'contributing_shift_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(323,2,'regular_time_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(324,2,'regular_time_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(325,2,'regular_time_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(326,2,'regular_time_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(327,2,'regular_time_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(328,2,'schedule_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(329,2,'schedule_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(330,2,'schedule_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(331,2,'schedule_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(332,2,'schedule_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(333,2,'meal_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(334,2,'meal_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(335,2,'meal_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(336,2,'meal_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(337,2,'meal_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(338,2,'break_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(339,2,'break_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(340,2,'break_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(341,2,'break_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(342,2,'break_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(343,2,'over_time_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(344,2,'over_time_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(345,2,'over_time_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(346,2,'over_time_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(347,2,'over_time_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(348,2,'premium_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(349,2,'premium_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(350,2,'premium_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(351,2,'premium_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(352,2,'premium_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(353,2,'accrual_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(354,2,'accrual_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(355,2,'accrual_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(356,2,'accrual_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(357,2,'accrual_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(358,2,'absence_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(359,2,'absence_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(360,2,'absence_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(361,2,'absence_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(362,2,'absence_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(363,2,'round_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(364,2,'round_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(365,2,'round_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(366,2,'round_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(367,2,'round_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(368,2,'exception_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(369,2,'exception_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(370,2,'exception_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(371,2,'exception_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(372,2,'exception_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(373,2,'holiday_policy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(374,2,'holiday_policy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(375,2,'holiday_policy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(376,2,'holiday_policy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(377,2,'holiday_policy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(378,2,'currency','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(379,2,'currency','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(380,2,'currency','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(381,2,'currency','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(382,2,'currency','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(383,2,'branch','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(384,2,'branch','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(385,2,'branch','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(386,2,'branch','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(387,2,'branch','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(388,2,'department','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(389,2,'department','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(390,2,'department','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(391,2,'department','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(392,2,'department','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(393,2,'department','assign','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(394,2,'station','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(395,2,'station','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(396,2,'station','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(397,2,'station','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(398,2,'station','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(399,2,'station','assign','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(400,2,'hierarchy','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(401,2,'hierarchy','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(402,2,'hierarchy','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(403,2,'hierarchy','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(404,2,'hierarchy','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(405,2,'other_field','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(406,2,'other_field','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(407,2,'other_field','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(408,2,'other_field','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(409,2,'other_field','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(410,2,'permission','enabled','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(411,2,'permission','view','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(412,2,'permission','add','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(413,2,'permission','edit','1',1423636842,NULL,NULL,NULL,NULL,NULL,0),(414,2,'permission','delete','1',1423636842,NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_control`
--

DROP TABLE IF EXISTS `permission_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_control` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `level` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `permission_control_id` (`id`),
  KEY `permission_control_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_control`
--

LOCK TABLES `permission_control` WRITE;
/*!40000 ALTER TABLE `permission_control` DISABLE KEYS */;
INSERT INTO `permission_control` VALUES (2,2,'Administrator','',1423636842,NULL,1423636842,NULL,NULL,NULL,0,25),(3,2,'Regular Employee (Punch In/Out)','',1423642642,3,1423642642,3,NULL,NULL,0,1),(4,2,'Regular Employee (Manual Entry)','',1423642642,3,1423642642,3,NULL,NULL,0,2),(5,2,'Supervisor (Subordinates Only)','',1423642642,3,1423642642,3,NULL,NULL,0,10),(9,2,'Supervisor (All Employees)','',1423642642,3,1423642642,3,NULL,NULL,0,15),(7,2,'HR Manager','',1423642642,3,1423642642,3,NULL,NULL,0,18),(8,2,'Payroll Administrator','',1423642642,3,1423642642,3,NULL,NULL,0,20);
/*!40000 ALTER TABLE `permission_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_control_id_seq`
--

DROP TABLE IF EXISTS `permission_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_control_id_seq`
--

LOCK TABLES `permission_control_id_seq` WRITE;
/*!40000 ALTER TABLE `permission_control_id_seq` DISABLE KEYS */;
INSERT INTO `permission_control_id_seq` VALUES (2);
/*!40000 ALTER TABLE `permission_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_id_seq`
--

DROP TABLE IF EXISTS `permission_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_id_seq`
--

LOCK TABLES `permission_id_seq` WRITE;
/*!40000 ALTER TABLE `permission_id_seq` DISABLE KEYS */;
INSERT INTO `permission_id_seq` VALUES (415);
/*!40000 ALTER TABLE `permission_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_user`
--

DROP TABLE IF EXISTS `permission_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_control_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `permission_user_id` (`id`),
  KEY `permission_user_permission_control_id` (`permission_control_id`),
  KEY `permission_user_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_user`
--

LOCK TABLES `permission_user` WRITE;
/*!40000 ALTER TABLE `permission_user` DISABLE KEYS */;
INSERT INTO `permission_user` VALUES (2,2,2);
/*!40000 ALTER TABLE `permission_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_user_id_seq`
--

DROP TABLE IF EXISTS `permission_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_user_id_seq`
--

LOCK TABLES `permission_user_id_seq` WRITE;
/*!40000 ALTER TABLE `permission_user_id_seq` DISABLE KEYS */;
INSERT INTO `permission_user_id_seq` VALUES (2);
/*!40000 ALTER TABLE `permission_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_group`
--

DROP TABLE IF EXISTS `policy_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `exception_policy_control_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `accrual_policy_id` int(11) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_group_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_group`
--

LOCK TABLES `policy_group` WRITE;
/*!40000 ALTER TABLE `policy_group` DISABLE KEYS */;
INSERT INTO `policy_group` VALUES (2,2,'AL - Hourly (OT Non-Exempt)',2,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL),(3,2,'AL - Salary (OT Exempt)',2,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `policy_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_group_id_seq`
--

DROP TABLE IF EXISTS `policy_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_group_id_seq`
--

LOCK TABLES `policy_group_id_seq` WRITE;
/*!40000 ALTER TABLE `policy_group_id_seq` DISABLE KEYS */;
INSERT INTO `policy_group_id_seq` VALUES (3);
/*!40000 ALTER TABLE `policy_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_group_user`
--

DROP TABLE IF EXISTS `policy_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy_group_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_group_user_id` (`id`),
  KEY `policy_group_user_policy_group_id` (`policy_group_id`),
  KEY `policy_group_user_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_group_user`
--

LOCK TABLES `policy_group_user` WRITE;
/*!40000 ALTER TABLE `policy_group_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `policy_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_group_user_id_seq`
--

DROP TABLE IF EXISTS `policy_group_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy_group_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_group_user_id_seq`
--

LOCK TABLES `policy_group_user_id_seq` WRITE;
/*!40000 ALTER TABLE `policy_group_user_id_seq` DISABLE KEYS */;
INSERT INTO `policy_group_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `policy_group_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy`
--

DROP TABLE IF EXISTS `premium_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type_id` int(11) NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `sun` tinyint(1) NOT NULL DEFAULT '0',
  `mon` tinyint(1) NOT NULL DEFAULT '0',
  `tue` tinyint(1) NOT NULL DEFAULT '0',
  `wed` tinyint(1) NOT NULL DEFAULT '0',
  `thu` tinyint(1) NOT NULL DEFAULT '0',
  `fri` tinyint(1) NOT NULL DEFAULT '0',
  `sat` tinyint(1) NOT NULL DEFAULT '0',
  `pay_type_id` int(11) NOT NULL,
  `rate` decimal(9,4) DEFAULT NULL,
  `accrual_policy_id` int(11) DEFAULT NULL,
  `accrual_rate` decimal(9,4) DEFAULT NULL,
  `pay_stub_entry_account_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `daily_trigger_time` int(11) DEFAULT NULL,
  `weekly_trigger_time` int(11) DEFAULT NULL,
  `minimum_time` int(11) DEFAULT NULL,
  `maximum_time` int(11) DEFAULT NULL,
  `include_meal_policy` smallint(6) DEFAULT NULL,
  `exclude_default_branch` smallint(6) DEFAULT NULL,
  `exclude_default_department` smallint(6) DEFAULT NULL,
  `branch_selection_type_id` smallint(6) DEFAULT NULL,
  `department_selection_type_id` smallint(6) DEFAULT NULL,
  `job_selection_type_id` smallint(6) DEFAULT NULL,
  `job_group_selection_type_id` smallint(6) DEFAULT NULL,
  `job_item_selection_type_id` smallint(6) DEFAULT NULL,
  `job_item_group_selection_type_id` smallint(6) DEFAULT NULL,
  `maximum_no_break_time` int(11) DEFAULT NULL,
  `minimum_break_time` int(11) DEFAULT NULL,
  `include_partial_punch` tinyint(1) NOT NULL DEFAULT '0',
  `wage_group_id` int(11) NOT NULL DEFAULT '0',
  `include_break_policy` smallint(6) DEFAULT '0',
  `minimum_time_between_shift` int(11) DEFAULT NULL,
  `minimum_first_shift_time` int(11) DEFAULT NULL,
  `minimum_shift_time` int(11) DEFAULT NULL,
  `include_holiday_type_id` int(11) DEFAULT '10',
  `maximum_daily_trigger_time` int(11) DEFAULT '0',
  `maximum_weekly_trigger_time` int(11) DEFAULT '0',
  `pay_code_id` int(11) DEFAULT '0',
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `contributing_shift_policy_id` int(11) DEFAULT '0',
  `exclude_default_job` smallint(6) DEFAULT '0',
  `exclude_default_job_item` smallint(6) DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `premium_policy_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy`
--

LOCK TABLES `premium_policy` WRITE;
/*!40000 ALTER TABLE `premium_policy` DISABLE KEYS */;
/*!40000 ALTER TABLE `premium_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy_branch`
--

DROP TABLE IF EXISTS `premium_policy_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy_branch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `premium_policy_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `premium_policy_branch_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy_branch`
--

LOCK TABLES `premium_policy_branch` WRITE;
/*!40000 ALTER TABLE `premium_policy_branch` DISABLE KEYS */;
/*!40000 ALTER TABLE `premium_policy_branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy_branch_id_seq`
--

DROP TABLE IF EXISTS `premium_policy_branch_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy_branch_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy_branch_id_seq`
--

LOCK TABLES `premium_policy_branch_id_seq` WRITE;
/*!40000 ALTER TABLE `premium_policy_branch_id_seq` DISABLE KEYS */;
INSERT INTO `premium_policy_branch_id_seq` VALUES (1);
/*!40000 ALTER TABLE `premium_policy_branch_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy_department`
--

DROP TABLE IF EXISTS `premium_policy_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy_department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `premium_policy_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `premium_policy_department_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy_department`
--

LOCK TABLES `premium_policy_department` WRITE;
/*!40000 ALTER TABLE `premium_policy_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `premium_policy_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy_department_id_seq`
--

DROP TABLE IF EXISTS `premium_policy_department_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy_department_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy_department_id_seq`
--

LOCK TABLES `premium_policy_department_id_seq` WRITE;
/*!40000 ALTER TABLE `premium_policy_department_id_seq` DISABLE KEYS */;
INSERT INTO `premium_policy_department_id_seq` VALUES (1);
/*!40000 ALTER TABLE `premium_policy_department_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_policy_id_seq`
--

DROP TABLE IF EXISTS `premium_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_policy_id_seq`
--

LOCK TABLES `premium_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `premium_policy_id_seq` DISABLE KEYS */;
INSERT INTO `premium_policy_id_seq` VALUES (1);
/*!40000 ALTER TABLE `premium_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punch`
--

DROP TABLE IF EXISTS `punch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `punch_control_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `time_stamp` timestamp NULL DEFAULT NULL,
  `original_time_stamp` timestamp NULL DEFAULT NULL,
  `actual_time_stamp` timestamp NULL DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `transfer` tinyint(1) DEFAULT '0',
  `longitude` decimal(15,10) DEFAULT NULL,
  `latitude` decimal(15,10) DEFAULT NULL,
  `position_accuracy` int(11) DEFAULT NULL,
  `has_image` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `punch_id` (`id`),
  KEY `punch_punch_control_id` (`punch_control_id`),
  KEY `punch_has_image` (`has_image`),
  KEY `punch_punch_control_status_id` (`punch_control_id`,`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `punch`
--

LOCK TABLES `punch` WRITE;
/*!40000 ALTER TABLE `punch` DISABLE KEYS */;
/*!40000 ALTER TABLE `punch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punch_control`
--

DROP TABLE IF EXISTS `punch_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punch_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `branch_id` int(11) NOT NULL DEFAULT '0',
  `department_id` int(11) NOT NULL DEFAULT '0',
  `job_id` int(11) NOT NULL DEFAULT '0',
  `job_item_id` int(11) NOT NULL DEFAULT '0',
  `quantity` decimal(9,2) NOT NULL DEFAULT '0.00',
  `bad_quantity` decimal(9,2) NOT NULL DEFAULT '0.00',
  `total_time` int(11) NOT NULL DEFAULT '0',
  `actual_total_time` int(11) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  `note` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `punch_control_user_id_user_date` (`user_id`,`date_stamp`),
  KEY `punch_control_pay_period_id` (`pay_period_id`),
  KEY `punch_control_branch_id` (`branch_id`),
  KEY `punch_control_department_id` (`department_id`),
  KEY `punch_control_job_id` (`job_id`),
  KEY `punch_control_job_item_id` (`job_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `punch_control`
--

LOCK TABLES `punch_control` WRITE;
/*!40000 ALTER TABLE `punch_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `punch_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punch_control_id_seq`
--

DROP TABLE IF EXISTS `punch_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punch_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `punch_control_id_seq`
--

LOCK TABLES `punch_control_id_seq` WRITE;
/*!40000 ALTER TABLE `punch_control_id_seq` DISABLE KEYS */;
INSERT INTO `punch_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `punch_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punch_control_old`
--

DROP TABLE IF EXISTS `punch_control_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punch_control_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_date_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `job_item_id` int(11) DEFAULT NULL,
  `quantity` decimal(9,2) DEFAULT NULL,
  `bad_quantity` decimal(9,2) DEFAULT NULL,
  `total_time` int(11) NOT NULL DEFAULT '0',
  `actual_total_time` int(11) NOT NULL DEFAULT '0',
  `meal_policy_id` int(11) DEFAULT NULL,
  `overlap` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `other_id1` varchar(255) DEFAULT NULL,
  `other_id2` varchar(255) DEFAULT NULL,
  `other_id3` varchar(255) DEFAULT NULL,
  `other_id4` varchar(255) DEFAULT NULL,
  `other_id5` varchar(255) DEFAULT NULL,
  `note` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `punch_control_old`
--

LOCK TABLES `punch_control_old` WRITE;
/*!40000 ALTER TABLE `punch_control_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `punch_control_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punch_id_seq`
--

DROP TABLE IF EXISTS `punch_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punch_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `punch_id_seq`
--

LOCK TABLES `punch_id_seq` WRITE;
/*!40000 ALTER TABLE `punch_id_seq` DISABLE KEYS */;
INSERT INTO `punch_id_seq` VALUES (1);
/*!40000 ALTER TABLE `punch_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualification`
--

DROP TABLE IF EXISTS `qualification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `name_metaphone` varchar(250) DEFAULT NULL,
  `description` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qualification_id` (`id`),
  KEY `qualification_company_id` (`company_id`),
  KEY `qualification_type_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualification`
--

LOCK TABLES `qualification` WRITE;
/*!40000 ALTER TABLE `qualification` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualification_group`
--

DROP TABLE IF EXISTS `qualification_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualification_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qualification_group_id` (`id`),
  KEY `qualification_group_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualification_group`
--

LOCK TABLES `qualification_group` WRITE;
/*!40000 ALTER TABLE `qualification_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualification_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualification_group_id_seq`
--

DROP TABLE IF EXISTS `qualification_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualification_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualification_group_id_seq`
--

LOCK TABLES `qualification_group_id_seq` WRITE;
/*!40000 ALTER TABLE `qualification_group_id_seq` DISABLE KEYS */;
INSERT INTO `qualification_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `qualification_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualification_group_tree`
--

DROP TABLE IF EXISTS `qualification_group_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualification_group_tree` (
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `left_id` bigint(20) NOT NULL DEFAULT '0',
  `right_id` bigint(20) NOT NULL DEFAULT '0',
  KEY `qualification_group_tree_left_id_right_id` (`left_id`,`right_id`),
  KEY `qualification_group_tree_id_object_id` (`tree_id`,`object_id`),
  KEY `qualification_group_tree_id_parent_id` (`tree_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualification_group_tree`
--

LOCK TABLES `qualification_group_tree` WRITE;
/*!40000 ALTER TABLE `qualification_group_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualification_group_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualification_id_seq`
--

DROP TABLE IF EXISTS `qualification_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualification_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualification_id_seq`
--

LOCK TABLES `qualification_id_seq` WRITE;
/*!40000 ALTER TABLE `qualification_id_seq` DISABLE KEYS */;
INSERT INTO `qualification_id_seq` VALUES (1);
/*!40000 ALTER TABLE `qualification_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_holiday`
--

DROP TABLE IF EXISTS `recurring_holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `special_day` smallint(1) DEFAULT NULL,
  `week_interval` int(11) DEFAULT NULL,
  `day_of_week` int(11) DEFAULT NULL,
  `day_of_month` int(11) DEFAULT NULL,
  `month_int` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `pivot_day_direction_id` int(11) DEFAULT NULL,
  `always_week_day_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_holiday_id` (`id`),
  KEY `recurring_holiday_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_holiday`
--

LOCK TABLES `recurring_holiday` WRITE;
/*!40000 ALTER TABLE `recurring_holiday` DISABLE KEYS */;
INSERT INTO `recurring_holiday` VALUES (2,2,10,'US - New Years Day',0,NULL,NULL,1,1,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(3,2,30,'US - Memorial Day',0,NULL,1,24,5,1423636843,NULL,1423636843,NULL,NULL,NULL,0,20,3),(4,2,10,'US - Independence Day',0,NULL,NULL,4,7,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(5,2,20,'US - Labour Day',0,1,1,NULL,9,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(6,2,10,'US - Veterans Day',0,NULL,NULL,11,11,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(7,2,20,'US - Thanksgiving Day',0,4,4,NULL,11,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(8,2,10,'US - Christmas Day',0,NULL,NULL,25,12,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(9,2,20,'US - Martin Luther King Day',0,3,1,NULL,1,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(10,2,20,'US - Presidents Day',0,3,1,NULL,2,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(11,2,10,'US - Christmas Eve',0,NULL,NULL,24,12,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(12,2,20,'US - Columbus Day',0,2,1,NULL,10,1423636843,NULL,1423636843,NULL,NULL,NULL,0,NULL,3),(13,2,10,'AL - Day After Christmas',0,NULL,NULL,26,12,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,3),(14,2,10,'AL - New Years Eve',0,NULL,NULL,31,12,1423636845,NULL,1423636845,NULL,NULL,NULL,0,NULL,3);
/*!40000 ALTER TABLE `recurring_holiday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_holiday_id_seq`
--

DROP TABLE IF EXISTS `recurring_holiday_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_holiday_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_holiday_id_seq`
--

LOCK TABLES `recurring_holiday_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_holiday_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_holiday_id_seq` VALUES (14);
/*!40000 ALTER TABLE `recurring_holiday_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_ps_amendment`
--

DROP TABLE IF EXISTS `recurring_ps_amendment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_ps_amendment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '10',
  `start_date` int(11) NOT NULL,
  `end_date` int(11) DEFAULT NULL,
  `frequency_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `pay_stub_entry_name_id` int(11) NOT NULL,
  `rate` decimal(20,4) DEFAULT NULL,
  `units` decimal(20,4) DEFAULT NULL,
  `amount` decimal(20,4) DEFAULT NULL,
  `percent_amount` decimal(20,4) DEFAULT NULL,
  `percent_amount_entry_name_id` int(11) DEFAULT NULL,
  `ps_amendment_description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_ps_amendment_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_ps_amendment`
--

LOCK TABLES `recurring_ps_amendment` WRITE;
/*!40000 ALTER TABLE `recurring_ps_amendment` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_ps_amendment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_ps_amendment_id_seq`
--

DROP TABLE IF EXISTS `recurring_ps_amendment_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_ps_amendment_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_ps_amendment_id_seq`
--

LOCK TABLES `recurring_ps_amendment_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_ps_amendment_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_ps_amendment_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_ps_amendment_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_ps_amendment_user`
--

DROP TABLE IF EXISTS `recurring_ps_amendment_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_ps_amendment_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recurring_ps_amendment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_ps_amendment_user_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_ps_amendment_user`
--

LOCK TABLES `recurring_ps_amendment_user` WRITE;
/*!40000 ALTER TABLE `recurring_ps_amendment_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_ps_amendment_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_ps_amendment_user_id_seq`
--

DROP TABLE IF EXISTS `recurring_ps_amendment_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_ps_amendment_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_ps_amendment_user_id_seq`
--

LOCK TABLES `recurring_ps_amendment_user_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_ps_amendment_user_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_ps_amendment_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_ps_amendment_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_control`
--

DROP TABLE IF EXISTS `recurring_schedule_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `recurring_schedule_template_control_id` int(11) NOT NULL,
  `start_week` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `auto_fill` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_schedule_control_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_control`
--

LOCK TABLES `recurring_schedule_control` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_schedule_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_control_id_seq`
--

DROP TABLE IF EXISTS `recurring_schedule_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_control_id_seq`
--

LOCK TABLES `recurring_schedule_control_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_control_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_schedule_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_schedule_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_template`
--

DROP TABLE IF EXISTS `recurring_schedule_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recurring_schedule_template_control_id` int(11) NOT NULL,
  `week` int(11) NOT NULL,
  `sun` tinyint(1) NOT NULL DEFAULT '0',
  `mon` tinyint(1) NOT NULL DEFAULT '0',
  `tue` tinyint(1) NOT NULL DEFAULT '0',
  `wed` tinyint(1) NOT NULL DEFAULT '0',
  `thu` tinyint(1) NOT NULL DEFAULT '0',
  `fri` tinyint(1) NOT NULL DEFAULT '0',
  `sat` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `schedule_policy_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `job_item_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL DEFAULT '10',
  `absence_policy_id` int(11) NOT NULL DEFAULT '0',
  `open_shift_multiplier` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_schedule_template_id` (`id`),
  KEY `recurring_schedule_template_schedule_template_control_id` (`recurring_schedule_template_control_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_template`
--

LOCK TABLES `recurring_schedule_template` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_schedule_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_template_control`
--

DROP TABLE IF EXISTS `recurring_schedule_template_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_template_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_schedule_template_control_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_template_control`
--

LOCK TABLES `recurring_schedule_template_control` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_template_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_schedule_template_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_template_control_id_seq`
--

DROP TABLE IF EXISTS `recurring_schedule_template_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_template_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_template_control_id_seq`
--

LOCK TABLES `recurring_schedule_template_control_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_template_control_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_schedule_template_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_schedule_template_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_template_id_seq`
--

DROP TABLE IF EXISTS `recurring_schedule_template_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_template_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_template_id_seq`
--

LOCK TABLES `recurring_schedule_template_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_template_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_schedule_template_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_schedule_template_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_user`
--

DROP TABLE IF EXISTS `recurring_schedule_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recurring_schedule_control_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `recurring_schedule_id` (`id`),
  KEY `recurring_schedule_recurring_schedule_control_id` (`recurring_schedule_control_id`),
  KEY `recurring_schedule_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_user`
--

LOCK TABLES `recurring_schedule_user` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_schedule_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_schedule_user_id_seq`
--

DROP TABLE IF EXISTS `recurring_schedule_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_schedule_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_schedule_user_id_seq`
--

LOCK TABLES `recurring_schedule_user_id_seq` WRITE;
/*!40000 ALTER TABLE `recurring_schedule_user_id_seq` DISABLE KEYS */;
INSERT INTO `recurring_schedule_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `recurring_schedule_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regular_time_policy`
--

DROP TABLE IF EXISTS `regular_time_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regular_time_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `contributing_shift_policy_id` int(11) NOT NULL,
  `calculation_order` int(11) DEFAULT NULL,
  `pay_formula_policy_id` int(11) DEFAULT '0',
  `pay_code_id` int(11) DEFAULT NULL,
  `branch_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_branch` smallint(6) DEFAULT '0',
  `department_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_department` smallint(6) DEFAULT '0',
  `job_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job` smallint(6) DEFAULT '0',
  `job_item_group_selection_type_id` smallint(6) DEFAULT '10',
  `job_item_selection_type_id` smallint(6) DEFAULT '10',
  `exclude_default_job_item` smallint(6) DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `regular_time_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regular_time_policy`
--

LOCK TABLES `regular_time_policy` WRITE;
/*!40000 ALTER TABLE `regular_time_policy` DISABLE KEYS */;
INSERT INTO `regular_time_policy` VALUES (2,2,'Regular Time',NULL,2,9999,0,3,10,0,10,0,10,10,0,10,10,0,1423636844,NULL,1423636844,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `regular_time_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regular_time_policy_id_seq`
--

DROP TABLE IF EXISTS `regular_time_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regular_time_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regular_time_policy_id_seq`
--

LOCK TABLES `regular_time_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `regular_time_policy_id_seq` DISABLE KEYS */;
INSERT INTO `regular_time_policy_id_seq` VALUES (2);
/*!40000 ALTER TABLE `regular_time_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `type_id` smallint(6) NOT NULL,
  `status_id` smallint(6) NOT NULL,
  `authorized` smallint(6) NOT NULL DEFAULT '0',
  `authorization_level` smallint(6) NOT NULL DEFAULT '99',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `request_user_id_user_date` (`user_id`,`date_stamp`),
  KEY `request_pay_period_id` (`pay_period_id`),
  KEY `request_type_id` (`type_id`),
  KEY `request_status_id` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request`
--

LOCK TABLES `request` WRITE;
/*!40000 ALTER TABLE `request` DISABLE KEYS */;
/*!40000 ALTER TABLE `request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_id_seq`
--

DROP TABLE IF EXISTS `request_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_id_seq`
--

LOCK TABLES `request_id_seq` WRITE;
/*!40000 ALTER TABLE `request_id_seq` DISABLE KEYS */;
INSERT INTO `request_id_seq` VALUES (1);
/*!40000 ALTER TABLE `request_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_old`
--

DROP TABLE IF EXISTS `request_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_date_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `authorized` tinyint(4) DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `authorization_level` smallint(6) DEFAULT '99',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_old`
--

LOCK TABLES `request_old` WRITE;
/*!40000 ALTER TABLE `request_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roe`
--

DROP TABLE IF EXISTS `roe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_type_id` int(11) NOT NULL,
  `code_id` varchar(250) NOT NULL,
  `first_date` int(11) DEFAULT NULL,
  `last_date` int(11) DEFAULT NULL,
  `pay_period_end_date` int(11) DEFAULT NULL,
  `recall_date` int(11) DEFAULT NULL,
  `insurable_hours` decimal(9,2) NOT NULL,
  `insurable_earnings` decimal(9,2) NOT NULL,
  `vacation_pay` decimal(9,2) DEFAULT NULL,
  `serial` varchar(250) DEFAULT NULL,
  `comments` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roe_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roe`
--

LOCK TABLES `roe` WRITE;
/*!40000 ALTER TABLE `roe` DISABLE KEYS */;
/*!40000 ALTER TABLE `roe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roe_id_seq`
--

DROP TABLE IF EXISTS `roe_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roe_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roe_id_seq`
--

LOCK TABLES `roe_id_seq` WRITE;
/*!40000 ALTER TABLE `roe_id_seq` DISABLE KEYS */;
INSERT INTO `roe_id_seq` VALUES (1);
/*!40000 ALTER TABLE `roe_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round_interval_policy`
--

DROP TABLE IF EXISTS `round_interval_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round_interval_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `punch_type_id` int(11) NOT NULL,
  `round_type_id` int(11) NOT NULL,
  `round_interval` int(11) NOT NULL,
  `strict` tinyint(1) NOT NULL DEFAULT '0',
  `grace` int(11) DEFAULT NULL,
  `minimum` int(11) DEFAULT NULL,
  `maximum` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `condition_type_id` smallint(6) DEFAULT '0',
  `condition_static_time` time DEFAULT '08:00:00',
  `condition_static_total_time` int(11) DEFAULT '3600',
  `condition_start_window` int(11) DEFAULT '900',
  `condition_stop_window` int(11) DEFAULT '900',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `round_interval_policy_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round_interval_policy`
--

LOCK TABLES `round_interval_policy` WRITE;
/*!40000 ALTER TABLE `round_interval_policy` DISABLE KEYS */;
/*!40000 ALTER TABLE `round_interval_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round_interval_policy_id_seq`
--

DROP TABLE IF EXISTS `round_interval_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round_interval_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round_interval_policy_id_seq`
--

LOCK TABLES `round_interval_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `round_interval_policy_id_seq` DISABLE KEYS */;
INSERT INTO `round_interval_policy_id_seq` VALUES (1);
/*!40000 ALTER TABLE `round_interval_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `status_id` smallint(6) NOT NULL DEFAULT '10',
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `schedule_policy_id` int(11) NOT NULL DEFAULT '0',
  `absence_policy_id` int(11) NOT NULL DEFAULT '0',
  `branch_id` int(11) NOT NULL DEFAULT '0',
  `department_id` int(11) NOT NULL DEFAULT '0',
  `job_id` int(11) NOT NULL DEFAULT '0',
  `job_item_id` int(11) NOT NULL DEFAULT '0',
  `total_time` int(11) NOT NULL DEFAULT '0',
  `replaced_id` int(11) NOT NULL DEFAULT '0',
  `recurring_schedule_template_control_id` int(11) NOT NULL DEFAULT '0',
  `auto_fill` smallint(6) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  `note` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_user_id_user_date` (`user_id`,`date_stamp`),
  KEY `schedule_pay_period_id` (`pay_period_id`),
  KEY `schedule_company_id` (`company_id`),
  KEY `schedule_branch_id` (`branch_id`),
  KEY `schedule_department_id` (`department_id`),
  KEY `schedule_job_id` (`job_id`),
  KEY `schedule_job_item_id` (`job_item_id`),
  KEY `schedule_company_recurring_schedule_template_control_id` (`recurring_schedule_template_control_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_id_seq`
--

DROP TABLE IF EXISTS `schedule_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_id_seq`
--

LOCK TABLES `schedule_id_seq` WRITE;
/*!40000 ALTER TABLE `schedule_id_seq` DISABLE KEYS */;
INSERT INTO `schedule_id_seq` VALUES (1);
/*!40000 ALTER TABLE `schedule_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_old`
--

DROP TABLE IF EXISTS `schedule_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_old` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `user_date_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `replaced_id` int(11) NOT NULL DEFAULT '0',
  `recurring_schedule_template_control_id` int(11) NOT NULL DEFAULT '0',
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `schedule_policy_id` int(11) DEFAULT NULL,
  `absence_policy_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `job_item_id` int(11) DEFAULT NULL,
  `total_time` int(11) DEFAULT NULL,
  `note` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_old`
--

LOCK TABLES `schedule_old` WRITE;
/*!40000 ALTER TABLE `schedule_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_policy`
--

DROP TABLE IF EXISTS `schedule_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `meal_policy_id` int(11) DEFAULT NULL,
  `over_time_policy_id` int(11) DEFAULT NULL,
  `absence_policy_id` int(11) DEFAULT NULL,
  `start_stop_window` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_policy_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_policy`
--

LOCK TABLES `schedule_policy` WRITE;
/*!40000 ALTER TABLE `schedule_policy` DISABLE KEYS */;
INSERT INTO `schedule_policy` VALUES (2,2,'No Lunch',NULL,NULL,NULL,7200,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL),(3,2,'30min Lunch',NULL,NULL,NULL,7200,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL),(4,2,'60min Lunch',NULL,NULL,NULL,7200,1423636844,NULL,1423636844,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `schedule_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_policy_id_seq`
--

DROP TABLE IF EXISTS `schedule_policy_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_policy_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_policy_id_seq`
--

LOCK TABLES `schedule_policy_id_seq` WRITE;
/*!40000 ALTER TABLE `schedule_policy_id_seq` DISABLE KEYS */;
INSERT INTO `schedule_policy_id_seq` VALUES (4);
/*!40000 ALTER TABLE `schedule_policy_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station`
--

DROP TABLE IF EXISTS `station`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `station_id` varchar(250) NOT NULL,
  `source` varchar(250) DEFAULT NULL,
  `description` varchar(250) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `allowed_date` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `time_zone` varchar(250) DEFAULT NULL,
  `user_group_selection_type_id` smallint(6) DEFAULT NULL,
  `branch_selection_type_id` smallint(6) DEFAULT NULL,
  `department_selection_type_id` smallint(6) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `user_name` varchar(250) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `poll_frequency` int(11) DEFAULT NULL,
  `push_frequency` int(11) DEFAULT NULL,
  `last_punch_time_stamp` timestamp NULL DEFAULT NULL,
  `last_poll_date` int(11) DEFAULT NULL,
  `last_poll_status_message` varchar(250) DEFAULT NULL,
  `last_push_date` int(11) DEFAULT NULL,
  `last_push_status_message` varchar(250) DEFAULT NULL,
  `user_value_1` varchar(250) DEFAULT NULL,
  `user_value_2` varchar(250) DEFAULT NULL,
  `user_value_3` varchar(250) DEFAULT NULL,
  `user_value_4` varchar(250) DEFAULT NULL,
  `user_value_5` varchar(250) DEFAULT NULL,
  `partial_push_frequency` int(11) DEFAULT NULL,
  `last_partial_push_date` int(11) DEFAULT NULL,
  `last_partial_push_status_message` varchar(250) DEFAULT NULL,
  `pull_start_time` timestamp NULL DEFAULT NULL,
  `pull_end_time` timestamp NULL DEFAULT NULL,
  `push_start_time` timestamp NULL DEFAULT NULL,
  `push_end_time` timestamp NULL DEFAULT NULL,
  `partial_push_start_time` timestamp NULL DEFAULT NULL,
  `partial_push_end_time` timestamp NULL DEFAULT NULL,
  `enable_auto_punch_status` tinyint(1) NOT NULL DEFAULT '0',
  `mode_flag` bigint(20) NOT NULL DEFAULT '1',
  `work_code_definition` varchar(250) DEFAULT NULL,
  `job_id` int(11) DEFAULT '0',
  `job_item_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `station_id` (`id`),
  KEY `station_company_id` (`company_id`),
  KEY `station_company_id_station_id` (`company_id`,`station_id`),
  KEY `station_company_id_status_id_type_id` (`company_id`,`status_id`,`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station`
--

LOCK TABLES `station` WRITE;
/*!40000 ALTER TABLE `station` DISABLE KEYS */;
INSERT INTO `station` VALUES (2,2,20,10,'ANY','ANY','All desktop computers',1423636842,NULL,1423636842,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,10,10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,0,0);
/*!40000 ALTER TABLE `station` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_branch`
--

DROP TABLE IF EXISTS `station_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_branch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `station_branch_id` (`id`),
  KEY `station_branch_station_id` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_branch`
--

LOCK TABLES `station_branch` WRITE;
/*!40000 ALTER TABLE `station_branch` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_branch_id_seq`
--

DROP TABLE IF EXISTS `station_branch_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_branch_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_branch_id_seq`
--

LOCK TABLES `station_branch_id_seq` WRITE;
/*!40000 ALTER TABLE `station_branch_id_seq` DISABLE KEYS */;
INSERT INTO `station_branch_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_branch_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_department`
--

DROP TABLE IF EXISTS `station_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `station_department_id` (`id`),
  KEY `station_department_station_id` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_department`
--

LOCK TABLES `station_department` WRITE;
/*!40000 ALTER TABLE `station_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_department_id_seq`
--

DROP TABLE IF EXISTS `station_department_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_department_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_department_id_seq`
--

LOCK TABLES `station_department_id_seq` WRITE;
/*!40000 ALTER TABLE `station_department_id_seq` DISABLE KEYS */;
INSERT INTO `station_department_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_department_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_exclude_user`
--

DROP TABLE IF EXISTS `station_exclude_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_exclude_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `station_exclude_user_id` (`id`),
  KEY `station_exclude_user_station_id` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_exclude_user`
--

LOCK TABLES `station_exclude_user` WRITE;
/*!40000 ALTER TABLE `station_exclude_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_exclude_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_exclude_user_id_seq`
--

DROP TABLE IF EXISTS `station_exclude_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_exclude_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_exclude_user_id_seq`
--

LOCK TABLES `station_exclude_user_id_seq` WRITE;
/*!40000 ALTER TABLE `station_exclude_user_id_seq` DISABLE KEYS */;
INSERT INTO `station_exclude_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_exclude_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_id_seq`
--

DROP TABLE IF EXISTS `station_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_id_seq`
--

LOCK TABLES `station_id_seq` WRITE;
/*!40000 ALTER TABLE `station_id_seq` DISABLE KEYS */;
INSERT INTO `station_id_seq` VALUES (2);
/*!40000 ALTER TABLE `station_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_include_user`
--

DROP TABLE IF EXISTS `station_include_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_include_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `station_include_user_id` (`id`),
  KEY `station_include_user_station_id` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_include_user`
--

LOCK TABLES `station_include_user` WRITE;
/*!40000 ALTER TABLE `station_include_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_include_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_include_user_id_seq`
--

DROP TABLE IF EXISTS `station_include_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_include_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_include_user_id_seq`
--

LOCK TABLES `station_include_user_id_seq` WRITE;
/*!40000 ALTER TABLE `station_include_user_id_seq` DISABLE KEYS */;
INSERT INTO `station_include_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_include_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_user`
--

DROP TABLE IF EXISTS `station_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `station_user_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_user`
--

LOCK TABLES `station_user` WRITE;
/*!40000 ALTER TABLE `station_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_user_group`
--

DROP TABLE IF EXISTS `station_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_user_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `station_user_group_id` (`id`),
  KEY `station_user_group_station_id` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_user_group`
--

LOCK TABLES `station_user_group` WRITE;
/*!40000 ALTER TABLE `station_user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_user_group_id_seq`
--

DROP TABLE IF EXISTS `station_user_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_user_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_user_group_id_seq`
--

LOCK TABLES `station_user_group_id_seq` WRITE;
/*!40000 ALTER TABLE `station_user_group_id_seq` DISABLE KEYS */;
INSERT INTO `station_user_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_user_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_user_id_seq`
--

DROP TABLE IF EXISTS `station_user_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_user_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_user_id_seq`
--

LOCK TABLES `station_user_id_seq` WRITE;
/*!40000 ALTER TABLE `station_user_id_seq` DISABLE KEYS */;
INSERT INTO `station_user_id_seq` VALUES (1);
/*!40000 ALTER TABLE `station_user_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_log`
--

DROP TABLE IF EXISTS `system_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `table_name` varchar(250) DEFAULT NULL,
  `action_id` int(11) DEFAULT NULL,
  `description` text,
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_log_id` (`id`),
  KEY `system_log_user_id_table_name_action_id` (`user_id`,`table_name`,`action_id`),
  KEY `system_log_user_id_date` (`user_id`,`date`),
  KEY `system_log_object_id_table_name` (`object_id`,`table_name`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_log`
--

LOCK TABLES `system_log` WRITE;
/*!40000 ALTER TABLE `system_log` DISABLE KEYS */;
INSERT INTO `system_log` VALUES (1,0,1,'cron',10,'Cron Job',1423636771),(2,0,2,'cron',10,'Cron Job',1423636771),(3,0,3,'cron',10,'Cron Job',1423636771),(4,0,4,'cron',10,'Cron Job',1423636771),(5,0,5,'cron',10,'Cron Job',1423636771),(6,0,6,'cron',10,'Cron Job',1423636771),(7,0,7,'cron',10,'Cron Job',1423636771),(8,0,8,'cron',10,'Cron Job',1423636771),(9,0,1,'system_setting',10,'System Setting - Name: schema_version_group_A Value: 1000A',1423636771),(10,0,2,'system_setting',10,'System Setting - Name: tax_data_version Value: 20060701.0',1423636771),(11,0,3,'system_setting',10,'System Setting - Name: tax_engine_version Value: 1.0.1',1423636771),(12,0,4,'system_setting',10,'System Setting - Name: schema_version_group_T Value: 1000T',1423636771),(13,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1001A',1423636771),(14,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20060701.1',1423636771),(15,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.2',1423636771),(16,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1001T',1423636771),(17,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1002A',1423636771),(18,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20070101.0',1423636771),(19,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.3',1423636771),(20,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1002T',1423636771),(21,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1003A',1423636771),(22,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20070101.1',1423636771),(23,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.4',1423636771),(24,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1003T',1423636771),(25,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1004A',1423636771),(26,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20070701.1',1423636771),(27,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.5',1423636771),(28,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1004T',1423636771),(29,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1005A',1423636771),(30,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20071001',1423636771),(31,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.6',1423636771),(32,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1005T',1423636771),(33,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1006A',1423636772),(34,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20080101',1423636772),(35,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.7',1423636772),(36,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1006T',1423636772),(37,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1007A',1423636772),(38,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20080701',1423636772),(39,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.8',1423636772),(40,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1007T',1423636772),(41,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1008A',1423636772),(42,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20090101',1423636772),(43,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.9',1423636772),(44,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1008T',1423636772),(45,0,9,'cron',10,'Cron Job',1423636772),(46,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1009A',1423636772),(47,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20090401',1423636772),(48,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.10',1423636772),(49,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1009T',1423636772),(50,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1010A',1423636772),(51,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20090501',1423636772),(52,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.11',1423636772),(53,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1010T',1423636772),(54,0,10,'cron',10,'Cron Job',1423636772),(55,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1011A',1423636772),(56,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20090501',1423636772),(57,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.12',1423636772),(58,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1011T',1423636772),(59,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1012A',1423636772),(60,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20091101',1423636772),(61,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.12',1423636772),(62,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1012T',1423636772),(63,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1013A',1423636772),(64,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20100101',1423636772),(65,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.13',1423636772),(66,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1013T',1423636772),(67,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1014A',1423636774),(68,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20100101',1423636774),(69,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.14',1423636774),(70,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1014T',1423636774),(71,0,11,'cron',10,'Cron Job',1423636774),(72,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1015A',1423636774),(73,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20100701',1423636774),(74,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.15',1423636774),(75,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1015T',1423636774),(76,0,1,'cron',20,'Cron Job',1423636774),(77,0,2,'cron',20,'Cron Job',1423636774),(78,0,3,'cron',20,'Cron Job',1423636774),(79,0,4,'cron',20,'Cron Job',1423636774),(80,0,5,'cron',20,'Cron Job',1423636774),(81,0,6,'cron',20,'Cron Job',1423636774),(82,0,7,'cron',20,'Cron Job',1423636774),(83,0,8,'cron',20,'Cron Job',1423636774),(84,0,9,'cron',20,'Cron Job',1423636774),(85,0,10,'cron',20,'Cron Job',1423636774),(86,0,11,'cron',20,'Cron Job',1423636774),(87,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1016A',1423636774),(88,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20110101',1423636774),(89,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.16',1423636774),(90,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1016T',1423636774),(91,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1017A',1423636775),(92,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.17',1423636775),(93,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1017T',1423636775),(94,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1018A',1423636775),(95,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20110701',1423636775),(96,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.18',1423636775),(97,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1018T',1423636775),(98,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1019A',1423636775),(99,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20120101',1423636775),(100,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.19',1423636775),(101,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1019T',1423636775),(102,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1020A',1423636775),(103,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20130101',1423636775),(104,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.20',1423636775),(105,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1020T',1423636775),(106,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1021A',1423636775),(107,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20130104',1423636775),(108,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.21',1423636775),(109,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1021T',1423636775),(110,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1022A',1423636775),(111,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20130701',1423636775),(112,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.22',1423636775),(113,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1022T',1423636775),(114,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1023A',1423636775),(115,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20140101',1423636775),(116,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.23',1423636775),(117,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1023T',1423636775),(118,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1024A',1423636775),(119,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20140101',1423636775),(120,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.24',1423636775),(121,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1024T',1423636775),(122,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1025A',1423636775),(123,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20140401',1423636775),(124,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.25',1423636775),(125,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1025T',1423636775),(126,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1026A',1423636775),(127,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20140701',1423636775),(128,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.26',1423636775),(129,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1026T',1423636776),(130,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1027A',1423636776),(131,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20140901',1423636776),(132,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.27',1423636776),(133,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1027T',1423636776),(134,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1028A',1423636776),(135,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1029A',1423636776),(136,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1030A',1423636776),(137,0,12,'cron',10,'Cron Job',1423636776),(138,0,13,'cron',10,'Cron Job',1423636776),(139,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1031A',1423636776),(140,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1032A',1423636777),(141,0,2,'system_setting',20,'System Setting - Name: tax_data_version Value: 20150101',1423636777),(142,0,3,'system_setting',20,'System Setting - Name: tax_engine_version Value: 1.0.28',1423636777),(143,0,4,'system_setting',20,'System Setting - Name: schema_version_group_T Value: 1032T',1423636777),(144,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1033A',1423636777),(145,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1034A',1423636777),(146,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1035A',1423636777),(147,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1036A',1423636777),(148,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1037A',1423636777),(149,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1038A',1423636777),(150,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1039A',1423636777),(151,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1040A',1423636777),(152,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1041A',1423636777),(153,0,14,'cron',10,'Cron Job',1423636777),(154,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1042A',1423636777),(155,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1043A',1423636777),(156,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1044A',1423636777),(157,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1045A',1423636777),(158,0,15,'cron',10,'Cron Job',1423636778),(159,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1046A',1423636778),(160,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1047A',1423636778),(161,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1049A',1423636778),(162,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1050A',1423636779),(163,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1051A',1423636779),(164,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1052A',1423636779),(165,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1053A',1423636779),(166,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1054A',1423636779),(167,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1055A',1423636779),(168,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1056A',1423636779),(169,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1058A',1423636780),(170,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1059A',1423636780),(171,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1060A',1423636781),(172,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1061A',1423636781),(173,0,16,'cron',10,'Cron Job',1423636781),(174,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1062A',1423636781),(175,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1063A',1423636781),(176,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1064A',1423636782),(177,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1065A',1423636782),(178,0,1,'system_setting',20,'System Setting - Name: schema_version_group_A Value: 1066A',1423636782),(180,0,6,'system_setting',10,'System Setting - Name: system_version Value: 8.0.0',1423636783),(181,0,7,'system_setting',10,'System Setting - Name: system_version_install_date Value: 1423636783',1423636783),(182,0,8,'system_setting',10,'System Setting - Name: update_notify Value: 0',1423636811),(183,0,9,'system_setting',10,'System Setting - Name: anonymous_update_notify Value: 0',1423636811),(184,0,10,'system_setting',10,'System Setting - Name: registration_key Value: a7c337db1c4770d6c668509ca09171e5',1423636811),(185,0,2,'company',10,'Company Information',1423636842),(186,0,2,'currency',10,'Currency: USD Rate: 1.00',1423636842),(187,0,2,'currency_rate',10,'Currency Rate: USD Rate: 1.00',1423636842),(188,0,2,'permission_control',10,'Permission Group: Administrator',1423636842),(189,0,2,'permission_control',20,'Applying Permission Preset: Administrator',1423636842),(190,0,2,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(191,0,3,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(192,0,4,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(193,0,5,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(194,0,6,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(195,0,7,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(196,0,8,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(197,0,9,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(198,0,10,'pay_stub_entry_account',10,'Pay Stub Account',1423636842),(199,0,11,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(200,0,12,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(201,0,13,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(202,0,14,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(203,0,15,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(204,0,16,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(205,0,17,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(206,0,18,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(207,0,19,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(208,0,20,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(209,0,21,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(210,0,22,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(211,0,23,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(212,0,24,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(213,0,25,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(214,0,26,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(215,0,27,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(216,0,28,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(217,0,29,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(218,0,30,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(219,0,31,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(220,0,32,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(221,0,33,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(222,0,34,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(223,0,35,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(224,0,36,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(225,0,2,'pay_stub_entry_account_link',10,'Pay Stub Account Links',1423636843),(226,0,2,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Loan Balance',1423636843),(227,0,2,'company_deduction',10,'Tax / Deduction',1423636843),(228,0,37,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(229,0,38,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(230,0,39,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(231,0,40,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(232,0,41,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(233,0,42,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(234,0,43,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(235,0,44,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(236,0,45,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(237,0,46,'pay_stub_entry_account',10,'Pay Stub Account',1423636843),(238,0,2,'pay_stub_entry_account_link',20,'Pay Stub Account Links',1423636843),(239,0,3,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(240,0,3,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(241,0,3,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(242,0,3,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(243,0,3,'company_deduction',10,'Tax / Deduction',1423636843),(244,0,4,'company_deduction',10,'Tax / Deduction',1423636843),(245,0,5,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(246,0,5,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(247,0,5,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(248,0,5,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(249,0,5,'company_deduction',10,'Tax / Deduction',1423636843),(250,0,6,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(251,0,6,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(252,0,6,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(253,0,6,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(254,0,6,'company_deduction',10,'Tax / Deduction',1423636843),(255,0,7,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(256,0,7,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(257,0,7,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(258,0,7,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(259,0,7,'company_deduction',10,'Tax / Deduction',1423636843),(260,0,8,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(261,0,8,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(262,0,8,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(263,0,8,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(264,0,8,'company_deduction',10,'Tax / Deduction',1423636843),(265,0,9,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(266,0,9,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(267,0,9,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(268,0,9,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636843),(269,0,9,'company_deduction',10,'Tax / Deduction',1423636843),(270,0,10,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636843),(271,0,10,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636843),(272,0,10,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636843),(273,0,10,'company_deduction',10,'Tax / Deduction',1423636843),(274,0,2,'recurring_holiday',10,'Recurring Holiday',1423636843),(275,0,3,'recurring_holiday',10,'Recurring Holiday',1423636843),(276,0,4,'recurring_holiday',10,'Recurring Holiday',1423636843),(277,0,5,'recurring_holiday',10,'Recurring Holiday',1423636843),(278,0,6,'recurring_holiday',10,'Recurring Holiday',1423636843),(279,0,7,'recurring_holiday',10,'Recurring Holiday',1423636843),(280,0,8,'recurring_holiday',10,'Recurring Holiday',1423636843),(281,0,9,'recurring_holiday',10,'Recurring Holiday',1423636843),(282,0,10,'recurring_holiday',10,'Recurring Holiday',1423636843),(283,0,11,'recurring_holiday',10,'Recurring Holiday',1423636843),(284,0,12,'recurring_holiday',10,'Recurring Holiday',1423636843),(285,0,2,'accrual_policy_account',10,'Accrual Account',1423636843),(286,0,3,'accrual_policy_account',10,'Accrual Account',1423636843),(287,0,2,'accrual_policy',10,'Accrual Policy',1423636843),(288,0,2,'accrual_policy_milestone',10,'Accrual Policy Milestone (ID: 2)',1423636843),(289,0,2,'accrual_policy_milestone',10,'Accrual Policy Milestone (ID: 3)',1423636843),(290,0,2,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(291,0,3,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(292,0,4,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(293,0,5,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(294,0,6,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(295,0,7,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(296,0,8,'pay_formula_policy',10,'Pay Formula Policy',1423636843),(297,0,2,'pay_code',10,'Pay Code',1423636843),(298,0,3,'pay_code',10,'Pay Code',1423636843),(299,0,4,'pay_code',10,'Pay Code',1423636844),(300,0,5,'pay_code',10,'Pay Code',1423636844),(301,0,6,'pay_code',10,'Pay Code',1423636844),(302,0,7,'pay_code',10,'Pay Code',1423636844),(303,0,8,'pay_code',10,'Pay Code',1423636844),(304,0,9,'pay_code',10,'Pay Code',1423636844),(305,0,10,'pay_code',10,'Pay Code',1423636844),(306,0,11,'pay_code',10,'Pay Code',1423636844),(307,0,12,'pay_code',10,'Pay Code',1423636844),(308,0,13,'pay_code',10,'Pay Code',1423636844),(309,0,14,'pay_code',10,'Pay Code',1423636844),(310,0,15,'pay_code',10,'Pay Code',1423636844),(311,0,16,'pay_code',10,'Pay Code',1423636844),(312,0,17,'pay_code',10,'Pay Code',1423636844),(313,0,2,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(314,0,3,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(315,0,4,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(316,0,5,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(317,0,6,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(318,0,7,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(319,0,8,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(320,0,9,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(321,0,10,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(322,0,11,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(323,0,12,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(324,0,13,'contributing_pay_code_policy',10,'Contributing Time Policy',1423636844),(325,0,2,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(326,0,3,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(327,0,4,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(328,0,5,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(329,0,6,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(330,0,7,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(331,0,8,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(332,0,9,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(333,0,10,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(334,0,11,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(335,0,12,'contributing_shift_policy',10,'Contributing Shift Policy',1423636844),(336,0,2,'absence_policy',10,'Absence Policy',1423636844),(337,0,3,'absence_policy',10,'Absence Policy',1423636844),(338,0,4,'absence_policy',10,'Absence Policy',1423636844),(339,0,5,'absence_policy',10,'Absence Policy',1423636844),(340,0,6,'absence_policy',10,'Absence Policy',1423636844),(341,0,7,'absence_policy',10,'Absence Policy',1423636844),(342,0,8,'absence_policy',10,'Absence Policy',1423636844),(343,0,9,'absence_policy',10,'Absence Policy',1423636844),(344,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Christmas Day',1423636844),(345,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Christmas Eve',1423636844),(346,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Independence Day',1423636844),(347,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - New Years Day',1423636844),(348,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Veterans Day',1423636844),(349,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Columbus Day',1423636844),(350,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Labour Day',1423636844),(351,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Martin Luther King Day',1423636844),(352,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Presidents Day',1423636844),(353,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Thanksgiving Day',1423636844),(354,0,2,'holiday_policy_recurring_holiday',10,'Recurring Holiday: US - Memorial Day',1423636844),(355,0,2,'holiday_policy',10,'Holiday Policy',1423636844),(356,0,2,'regular_time_policy',10,'Regular Time Policy',1423636844),(357,0,2,'over_time_policy',10,'OverTime Policy',1423636844),(358,0,2,'meal_policy',10,'Meal Policy',1423636844),(359,0,3,'meal_policy',10,'Meal Policy',1423636844),(360,0,2,'break_policy',10,'Break Policy',1423636844),(361,0,3,'break_policy',10,'Break Policy',1423636844),(362,0,2,'schedule_policy',10,'Schedule Policy',1423636844),(363,0,3,'schedule_policy',10,'Schedule Policy',1423636844),(364,0,4,'schedule_policy',10,'Schedule Policy',1423636844),(365,0,2,'exception_policy_control',10,'Exception Policy',1423636844),(366,0,2,'exception_policy',10,'Exception Policy - Type: Unscheduled Absence',1423636844),(367,0,2,'exception_policy',10,'Exception Policy - Type: Not Scheduled',1423636844),(368,0,2,'exception_policy',10,'Exception Policy - Type: In Early',1423636844),(369,0,2,'exception_policy',10,'Exception Policy - Type: In Late',1423636844),(370,0,2,'exception_policy',10,'Exception Policy - Type: Out Early',1423636844),(371,0,2,'exception_policy',10,'Exception Policy - Type: Out Late',1423636844),(372,0,2,'exception_policy',10,'Exception Policy - Type: Over Daily Scheduled Time',1423636844),(373,0,2,'exception_policy',10,'Exception Policy - Type: Under Daily Scheduled Time',1423636844),(374,0,2,'exception_policy',10,'Exception Policy - Type: Over Weekly Scheduled Time',1423636845),(375,0,2,'exception_policy',10,'Exception Policy - Type: Not Scheduled Branch or Department',1423636845),(376,0,2,'exception_policy',10,'Exception Policy - Type: Over Daily Time',1423636845),(377,0,2,'exception_policy',10,'Exception Policy - Type: Over Weekly Time',1423636845),(378,0,2,'exception_policy',10,'Exception Policy - Type: Missing In Punch',1423636845),(379,0,2,'exception_policy',10,'Exception Policy - Type: Missing Out Punch',1423636845),(380,0,2,'exception_policy',10,'Exception Policy - Type: Missing Lunch In/Out Punch',1423636845),(381,0,2,'exception_policy',10,'Exception Policy - Type: Missing Break In/Out Punch',1423636845),(382,0,2,'exception_policy',10,'Exception Policy - Type: Long Lunch',1423636845),(383,0,2,'exception_policy',10,'Exception Policy - Type: Short Lunch',1423636845),(384,0,2,'exception_policy',10,'Exception Policy - Type: No Lunch',1423636845),(385,0,2,'exception_policy',10,'Exception Policy - Type: Long Break',1423636845),(386,0,2,'exception_policy',10,'Exception Policy - Type: Short Break',1423636845),(387,0,2,'exception_policy',10,'Exception Policy - Type: Too Many Breaks',1423636845),(388,0,2,'exception_policy',10,'Exception Policy - Type: Too Few Breaks',1423636845),(389,0,2,'exception_policy',10,'Exception Policy - Type: No Break',1423636845),(390,0,2,'exception_policy',10,'Exception Policy - Type: No Branch or Department',1423636845),(391,0,2,'exception_policy',10,'Exception Policy - Type: TimeSheet Not Verified',1423636845),(392,0,47,'pay_stub_entry_account',10,'Pay Stub Account',1423636845),(393,0,48,'pay_stub_entry_account',10,'Pay Stub Account',1423636845),(394,0,49,'pay_stub_entry_account',10,'Pay Stub Account',1423636845),(395,0,50,'pay_stub_entry_account',10,'Pay Stub Account',1423636845),(396,0,2,'pay_stub_entry_account_link',20,'Pay Stub Account Links',1423636845),(397,0,11,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636845),(398,0,11,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636845),(399,0,11,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636845),(400,0,11,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636845),(401,0,11,'company_deduction',10,'Tax / Deduction',1423636845),(402,0,12,'company_deduction',10,'Tax / Deduction',1423636845),(403,0,13,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636845),(404,0,13,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636845),(405,0,13,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636845),(406,0,13,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636845),(407,0,13,'company_deduction',10,'Tax / Deduction',1423636845),(408,0,14,'company_deduction_pay_stub_entry_account',10,'Include Pay Stub Account: Total Gross',1423636845),(409,0,14,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Loan',1423636845),(410,0,14,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: Expense Reimbursement',1423636845),(411,0,14,'company_deduction_pay_stub_entry_account',10,'Exclude Pay Stub Account: 401(k)',1423636845),(412,0,14,'company_deduction',10,'Tax / Deduction',1423636845),(413,0,13,'recurring_holiday',10,'Recurring Holiday',1423636845),(414,0,14,'recurring_holiday',10,'Recurring Holiday',1423636845),(415,0,3,'over_time_policy',10,'OverTime Policy',1423636845),(416,0,2,'policy_group',10,'Overtime Policy: AL - Weekly >40hrs',1423636845),(417,0,2,'policy_group',10,'Overtime Policy: US - Holiday',1423636845),(418,0,2,'policy_group',10,'Meal Policy: 30min Lunch',1423636845),(419,0,2,'policy_group',10,'Meal Policy: 60min Lunch',1423636845),(420,0,2,'policy_group',10,'Absence Policy: Bereavement',1423636845),(421,0,2,'policy_group',10,'Absence Policy: Jury Duty',1423636845),(422,0,2,'policy_group',10,'Absence Policy: Sick (PAID)',1423636845),(423,0,2,'policy_group',10,'Absence Policy: Sick (UNPAID)',1423636845),(424,0,2,'policy_group',10,'Absence Policy: Statutory Holiday',1423636845),(425,0,2,'policy_group',10,'Absence Policy: Time Bank (Withdrawal)',1423636845),(426,0,2,'policy_group',10,'Absence Policy: Vacation (PAID)',1423636845),(427,0,2,'policy_group',10,'Absence Policy: Vacation (UNPAID)',1423636845),(428,0,2,'policy_group',10,'Policy Group',1423636845),(429,0,3,'policy_group',10,'Meal Policy: 30min Lunch',1423636845),(430,0,3,'policy_group',10,'Meal Policy: 60min Lunch',1423636845),(431,0,3,'policy_group',10,'Absence Policy: Bereavement',1423636845),(432,0,3,'policy_group',10,'Absence Policy: Jury Duty',1423636845),(433,0,3,'policy_group',10,'Absence Policy: Sick (PAID)',1423636845),(434,0,3,'policy_group',10,'Absence Policy: Sick (UNPAID)',1423636845),(435,0,3,'policy_group',10,'Absence Policy: Statutory Holiday',1423636845),(436,0,3,'policy_group',10,'Absence Policy: Time Bank (Withdrawal)',1423636845),(437,0,3,'policy_group',10,'Absence Policy: Vacation (PAID)',1423636845),(438,0,3,'policy_group',10,'Absence Policy: Vacation (UNPAID)',1423636845),(439,0,3,'policy_group',10,'Policy Group',1423636845),(440,0,2,'user_default',10,'Employee Default Information',1423636845),(441,0,2,'users',10,'Employee: Admin Admin',1423636865),(442,0,2,'permission_user',10,'Employee: Admin Admin',1423636865),(443,0,2,'user_identification',10,'Employee Identification - Employee: Admin Admin Type: Password History',1423636865),(444,0,2,'user_preference',10,'Employee Preferences: Admin Admin',1423636865),(445,0,2,'company',20,'Company Information',1423636865),(446,0,11,'system_setting',10,'System Setting - Name: new_version Value: 0',1423636890),(447,0,12,'system_setting',10,'System Setting - Name: valid_install_requirements Value: 1',1423636890),(448,0,13,'system_setting',10,'System Setting - Name: auto_upgrade_failed Value: 0',1423636890),(449,0,1,'cron',500,'Executing Cron Job: 1 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddPayPeriod.php\" Return Code: 0',1423636921),(450,0,2,'cron',500,'Executing Cron Job: 2 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddUserDate.php\" Return Code: 0',1423636921),(451,0,3,'cron',500,'Executing Cron Job: 3 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/calcExceptions.php\" Return Code: 0',1423636921),(452,0,4,'cron',500,'Executing Cron Job: 4 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddRecurringPayStubAmendment.php\" Return Code: 0',1423636921),(453,0,2,'holidays',10,'Holiday',1423636921),(454,0,5,'cron',500,'Executing Cron Job: 5 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddRecurringHoliday.php\" Return Code: 0',1423636921),(455,0,6,'cron',500,'Executing Cron Job: 6 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/UserCount.php\" Return Code: 0',1423636922),(456,0,7,'cron',500,'Executing Cron Job: 7 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddRecurringScheduleShift.php\" Return Code: 0',1423636922),(457,0,8,'cron',500,'Executing Cron Job: 8 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/CheckForUpdate.php\" Return Code: 0',1423636922),(458,0,9,'cron',500,'Executing Cron Job: 9 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddAccrualPolicyTime.php\" Return Code: 0',1423636922),(459,0,10,'cron',500,'Executing Cron Job: 10 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/UpdateCurrencyRates.php\" Return Code: 0',1423636922),(460,0,12,'cron',500,'Executing Cron Job: 12 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/MiscDaily.php\" Return Code: 0',1423636922),(461,0,12,'system_setting',20,'System Setting - Name: valid_install_requirements Value: 1',1423636922),(462,0,13,'cron',500,'Executing Cron Job: 13 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/MiscWeekly.php\" Return Code: 0',1423636922),(463,0,14,'cron',500,'Executing Cron Job: 14 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/calcQuickExceptions.php\" Return Code: 0',1423636922),(464,0,13,'system_setting',20,'System Setting - Name: auto_upgrade_failed Value: 0',1423636969),(465,0,16,'cron',500,'Executing Cron Job: 16 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AutoUpgrade.php\" Return Code: 0',1423636969),(466,0,7,'cron',500,'Executing Cron Job: 7 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/AddRecurringScheduleShift.php\" Return Code: 0',1423637461),(467,0,14,'cron',500,'Executing Cron Job: 14 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/calcQuickExceptions.php\" Return Code: 0',1423637522),(468,0,14,'cron',500,'Executing Cron Job: 14 Command: \"/usr/bin/php\" \"/home/bruno/Workspace/timetrex/maint/calcQuickExceptions.php\" Return Code: 0',1423638421);
/*!40000 ALTER TABLE `system_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_log_detail`
--

DROP TABLE IF EXISTS `system_log_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_log_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `system_log_id` int(11) NOT NULL,
  `field` varchar(75) DEFAULT NULL,
  `new_value` text,
  `old_value` text,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `system_log_detail_id` (`id`),
  KEY `system_log_detail_system_log_id` (`system_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1028 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_log_detail`
--

LOCK TABLES `system_log_detail` WRITE;
/*!40000 ALTER TABLE `system_log_detail` DISABLE KEYS */;
INSERT INTO `system_log_detail` VALUES (1,185,'status_id','10',NULL),(2,185,'product_edition_id','10',NULL),(3,185,'name','Test Company',NULL),(4,185,'industry_id','0',NULL),(5,185,'city','San Francisco',NULL),(6,185,'country','US',NULL),(7,185,'province','AL',NULL),(8,185,'work_phone','555 1425 548',NULL),(9,186,'status_id','10',NULL),(10,186,'name','USD',NULL),(11,186,'iso_code','USD',NULL),(12,186,'conversion_rate','1.00',NULL),(13,186,'is_base','1',NULL),(14,186,'is_default','1',NULL),(15,186,'rate_modify_percent','1',NULL),(16,187,'currency_id','2',NULL),(17,187,'date_stamp','1423636842',NULL),(18,187,'conversion_rate','1.00',NULL),(19,188,'name','Administrator',NULL),(20,188,'level','25',NULL),(21,189,'permission_control_id','2',NULL),(22,190,'status_id','10',NULL),(23,190,'type_id','10',NULL),(24,190,'name','Regular Time',NULL),(25,190,'ps_order','100',NULL),(26,190,'accrual_type_id','10',NULL),(27,191,'status_id','10',NULL),(28,191,'type_id','10',NULL),(29,191,'name','Over Time 1',NULL),(30,191,'ps_order','120',NULL),(31,191,'accrual_type_id','10',NULL),(32,192,'status_id','10',NULL),(33,192,'type_id','10',NULL),(34,192,'name','Over Time 2',NULL),(35,192,'ps_order','121',NULL),(36,192,'accrual_type_id','10',NULL),(37,193,'status_id','10',NULL),(38,193,'type_id','10',NULL),(39,193,'name','Premium 1',NULL),(40,193,'ps_order','130',NULL),(41,193,'accrual_type_id','10',NULL),(42,194,'status_id','10',NULL),(43,194,'type_id','10',NULL),(44,194,'name','Premium 2',NULL),(45,194,'ps_order','131',NULL),(46,194,'accrual_type_id','10',NULL),(47,195,'status_id','10',NULL),(48,195,'type_id','10',NULL),(49,195,'name','Statutory Holiday',NULL),(50,195,'ps_order','140',NULL),(51,195,'accrual_type_id','10',NULL),(52,196,'status_id','10',NULL),(53,196,'type_id','10',NULL),(54,196,'name','Sick',NULL),(55,196,'ps_order','142',NULL),(56,196,'accrual_type_id','10',NULL),(57,197,'status_id','10',NULL),(58,197,'type_id','10',NULL),(59,197,'name','Bereavement',NULL),(60,197,'ps_order','145',NULL),(61,197,'accrual_type_id','10',NULL),(62,198,'status_id','10',NULL),(63,198,'type_id','10',NULL),(64,198,'name','Jury Duty',NULL),(65,198,'ps_order','146',NULL),(66,198,'accrual_type_id','10',NULL),(67,199,'status_id','10',NULL),(68,199,'type_id','10',NULL),(69,199,'name','Tips',NULL),(70,199,'ps_order','150',NULL),(71,199,'accrual_type_id','10',NULL),(72,200,'status_id','10',NULL),(73,200,'type_id','10',NULL),(74,200,'name','Commission',NULL),(75,200,'ps_order','152',NULL),(76,200,'accrual_type_id','10',NULL),(77,201,'status_id','10',NULL),(78,201,'type_id','10',NULL),(79,201,'name','Expense Reimbursement',NULL),(80,201,'ps_order','154',NULL),(81,201,'accrual_type_id','10',NULL),(82,202,'status_id','10',NULL),(83,202,'type_id','10',NULL),(84,202,'name','Bonus',NULL),(85,202,'ps_order','156',NULL),(86,202,'accrual_type_id','10',NULL),(87,203,'status_id','10',NULL),(88,203,'type_id','10',NULL),(89,203,'name','Severance',NULL),(90,203,'ps_order','160',NULL),(91,203,'accrual_type_id','10',NULL),(92,204,'status_id','10',NULL),(93,204,'type_id','10',NULL),(94,204,'name','Advance',NULL),(95,204,'ps_order','170',NULL),(96,204,'accrual_type_id','10',NULL),(97,205,'status_id','10',NULL),(98,205,'type_id','20',NULL),(99,205,'name','Health Benefits Plan',NULL),(100,205,'ps_order','250',NULL),(101,205,'accrual_type_id','10',NULL),(102,206,'status_id','10',NULL),(103,206,'type_id','20',NULL),(104,206,'name','Dental Benefits Plan',NULL),(105,206,'ps_order','255',NULL),(106,206,'accrual_type_id','10',NULL),(107,207,'status_id','10',NULL),(108,207,'type_id','20',NULL),(109,207,'name','Life Insurance',NULL),(110,207,'ps_order','256',NULL),(111,207,'accrual_type_id','10',NULL),(112,208,'status_id','10',NULL),(113,208,'type_id','20',NULL),(114,208,'name','Long Term Disability',NULL),(115,208,'ps_order','257',NULL),(116,208,'accrual_type_id','10',NULL),(117,209,'status_id','10',NULL),(118,209,'type_id','20',NULL),(119,209,'name','Accidental Death & Dismemberment',NULL),(120,209,'ps_order','258',NULL),(121,209,'accrual_type_id','10',NULL),(122,210,'status_id','10',NULL),(123,210,'type_id','20',NULL),(124,210,'name','Advance Paid',NULL),(125,210,'ps_order','280',NULL),(126,210,'accrual_type_id','10',NULL),(127,211,'status_id','10',NULL),(128,211,'type_id','20',NULL),(129,211,'name','Union Dues',NULL),(130,211,'ps_order','282',NULL),(131,211,'accrual_type_id','10',NULL),(132,212,'status_id','10',NULL),(133,212,'type_id','20',NULL),(134,212,'name','Garnishment',NULL),(135,212,'ps_order','289',NULL),(136,212,'accrual_type_id','10',NULL),(137,213,'status_id','10',NULL),(138,213,'type_id','30',NULL),(139,213,'name','Health Benefits Plan',NULL),(140,213,'ps_order','340',NULL),(141,213,'accrual_type_id','10',NULL),(142,214,'status_id','10',NULL),(143,214,'type_id','30',NULL),(144,214,'name','Dental Benefits Plan',NULL),(145,214,'ps_order','341',NULL),(146,214,'accrual_type_id','10',NULL),(147,215,'status_id','10',NULL),(148,215,'type_id','30',NULL),(149,215,'name','Life Insurance',NULL),(150,215,'ps_order','346',NULL),(151,215,'accrual_type_id','10',NULL),(152,216,'status_id','10',NULL),(153,216,'type_id','30',NULL),(154,216,'name','Long Term Disability',NULL),(155,216,'ps_order','347',NULL),(156,216,'accrual_type_id','10',NULL),(157,217,'status_id','10',NULL),(158,217,'type_id','30',NULL),(159,217,'name','Accidental Death & Dismemberment',NULL),(160,217,'ps_order','348',NULL),(161,217,'accrual_type_id','10',NULL),(162,218,'status_id','10',NULL),(163,218,'type_id','50',NULL),(164,218,'name','Loan Balance',NULL),(165,218,'ps_order','497',NULL),(166,218,'accrual_type_id','10',NULL),(167,219,'status_id','10',NULL),(168,219,'type_id','10',NULL),(169,219,'name','Loan',NULL),(170,219,'ps_order','197',NULL),(171,219,'accrual_pay_stub_entry_account_id','30',NULL),(172,219,'accrual_type_id','10',NULL),(173,220,'status_id','10',NULL),(174,220,'type_id','20',NULL),(175,220,'name','Loan Repayment',NULL),(176,220,'ps_order','297',NULL),(177,220,'accrual_pay_stub_entry_account_id','30',NULL),(178,220,'accrual_type_id','10',NULL),(179,221,'status_id','10',NULL),(180,221,'type_id','40',NULL),(181,221,'name','Total Gross',NULL),(182,221,'ps_order','199',NULL),(183,221,'accrual_type_id','10',NULL),(184,222,'status_id','10',NULL),(185,222,'type_id','40',NULL),(186,222,'name','Total Deductions',NULL),(187,222,'ps_order','298',NULL),(188,222,'accrual_type_id','10',NULL),(189,223,'status_id','10',NULL),(190,223,'type_id','40',NULL),(191,223,'name','Net Pay',NULL),(192,223,'ps_order','299',NULL),(193,223,'accrual_type_id','10',NULL),(194,224,'status_id','10',NULL),(195,224,'type_id','40',NULL),(196,224,'name','Employer Total Contributions',NULL),(197,224,'ps_order','399',NULL),(198,224,'accrual_type_id','10',NULL),(199,227,'status_id','10',NULL),(200,227,'type_id','20',NULL),(201,227,'name','Loan Repayment',NULL),(202,227,'calculation_id','52',NULL),(203,227,'calculation_order','200',NULL),(204,227,'user_value1','25',NULL),(205,227,'user_value2','0',NULL),(206,227,'pay_stub_entry_account_id','32',NULL),(207,227,'include_account_amount_type_id','30',NULL),(208,227,'minimum_length_of_service_days','0',NULL),(209,227,'maximum_length_of_service_days','0',NULL),(210,227,'apply_frequency_id','10',NULL),(211,228,'status_id','10',NULL),(212,228,'type_id','20',NULL),(213,228,'name','US - Federal Income Tax',NULL),(214,228,'ps_order','200',NULL),(215,228,'accrual_type_id','10',NULL),(216,229,'status_id','10',NULL),(217,229,'type_id','20',NULL),(218,229,'name','Social Security (FICA)',NULL),(219,229,'ps_order','202',NULL),(220,229,'accrual_type_id','10',NULL),(221,230,'status_id','10',NULL),(222,230,'type_id','30',NULL),(223,230,'name','Social Security (FICA)',NULL),(224,230,'ps_order','302',NULL),(225,230,'accrual_type_id','10',NULL),(226,231,'status_id','10',NULL),(227,231,'type_id','30',NULL),(228,231,'name','US - Federal Unemployment Insurance',NULL),(229,231,'ps_order','303',NULL),(230,231,'accrual_type_id','10',NULL),(231,232,'status_id','10',NULL),(232,232,'type_id','20',NULL),(233,232,'name','Medicare',NULL),(234,232,'ps_order','203',NULL),(235,232,'accrual_type_id','10',NULL),(236,233,'status_id','10',NULL),(237,233,'type_id','30',NULL),(238,233,'name','Medicare',NULL),(239,233,'ps_order','303',NULL),(240,233,'accrual_type_id','10',NULL),(241,234,'status_id','10',NULL),(242,234,'type_id','20',NULL),(243,234,'name','401(k)',NULL),(244,234,'ps_order','230',NULL),(245,234,'accrual_type_id','10',NULL),(246,235,'status_id','10',NULL),(247,235,'type_id','30',NULL),(248,235,'name','401(k)',NULL),(249,235,'ps_order','330',NULL),(250,235,'accrual_type_id','10',NULL),(251,236,'status_id','10',NULL),(252,236,'type_id','30',NULL),(253,236,'name','Workers Compensation - Employer',NULL),(254,236,'ps_order','305',NULL),(255,236,'accrual_type_id','10',NULL),(256,237,'status_id','10',NULL),(257,237,'type_id','10',NULL),(258,237,'name','Vacation',NULL),(259,237,'ps_order','181',NULL),(260,237,'accrual_type_id','10',NULL),(261,243,'status_id','10',NULL),(262,243,'type_id','10',NULL),(263,243,'name','US - Federal Income Tax',NULL),(264,243,'calculation_id','100',NULL),(265,243,'calculation_order','100',NULL),(266,243,'country','US',NULL),(267,243,'user_value1','0',NULL),(268,243,'pay_stub_entry_account_id','37',NULL),(269,243,'minimum_length_of_service_days','0',NULL),(270,243,'maximum_length_of_service_days','0',NULL),(271,243,'apply_frequency_id','10',NULL),(272,244,'status_id','10',NULL),(273,244,'type_id','10',NULL),(274,244,'name','US - Addl. Income Tax',NULL),(275,244,'calculation_id','20',NULL),(276,244,'calculation_order','105',NULL),(277,244,'user_value1','0',NULL),(278,244,'pay_stub_entry_account_id','37',NULL),(279,244,'minimum_length_of_service_days','0',NULL),(280,244,'maximum_length_of_service_days','0',NULL),(281,244,'apply_frequency_id','10',NULL),(282,249,'status_id','10',NULL),(283,249,'type_id','10',NULL),(284,249,'name','US - Federal Unemployment Insurance',NULL),(285,249,'calculation_id','15',NULL),(286,249,'calculation_order','80',NULL),(287,249,'user_value1','0.6',NULL),(288,249,'user_value2','7000',NULL),(289,249,'user_value3','0',NULL),(290,249,'pay_stub_entry_account_id','40',NULL),(291,249,'minimum_length_of_service_days','0',NULL),(292,249,'maximum_length_of_service_days','0',NULL),(293,249,'apply_frequency_id','10',NULL),(294,254,'status_id','10',NULL),(295,254,'type_id','10',NULL),(296,254,'name','Social Security - Employee',NULL),(297,254,'calculation_id','84',NULL),(298,254,'calculation_order','80',NULL),(299,254,'pay_stub_entry_account_id','38',NULL),(300,254,'minimum_length_of_service_days','0',NULL),(301,254,'maximum_length_of_service_days','0',NULL),(302,254,'apply_frequency_id','10',NULL),(303,259,'status_id','10',NULL),(304,259,'type_id','10',NULL),(305,259,'name','Social Security - Employer',NULL),(306,259,'calculation_id','85',NULL),(307,259,'calculation_order','81',NULL),(308,259,'pay_stub_entry_account_id','39',NULL),(309,259,'minimum_length_of_service_days','0',NULL),(310,259,'maximum_length_of_service_days','0',NULL),(311,259,'apply_frequency_id','10',NULL),(312,264,'status_id','10',NULL),(313,264,'type_id','10',NULL),(314,264,'name','Medicare - Employee',NULL),(315,264,'calculation_id','82',NULL),(316,264,'calculation_order','90',NULL),(317,264,'user_value1','10',NULL),(318,264,'pay_stub_entry_account_id','41',NULL),(319,264,'minimum_length_of_service_days','0',NULL),(320,264,'maximum_length_of_service_days','0',NULL),(321,264,'apply_frequency_id','10',NULL),(322,269,'status_id','10',NULL),(323,269,'type_id','10',NULL),(324,269,'name','Medicare - Employer',NULL),(325,269,'calculation_id','83',NULL),(326,269,'calculation_order','91',NULL),(327,269,'pay_stub_entry_account_id','42',NULL),(328,269,'minimum_length_of_service_days','0',NULL),(329,269,'maximum_length_of_service_days','0',NULL),(330,269,'apply_frequency_id','10',NULL),(331,273,'status_id','10',NULL),(332,273,'type_id','10',NULL),(333,273,'name','Workers Compensation - Employer',NULL),(334,273,'calculation_id','15',NULL),(335,273,'calculation_order','96',NULL),(336,273,'user_value1','0',NULL),(337,273,'user_value2','0',NULL),(338,273,'user_value3','0',NULL),(339,273,'pay_stub_entry_account_id','45',NULL),(340,273,'minimum_length_of_service_days','0',NULL),(341,273,'maximum_length_of_service_days','0',NULL),(342,273,'apply_frequency_id','10',NULL),(343,274,'special_day','0',NULL),(344,274,'type_id','10',NULL),(345,274,'name','US - New Years Day',NULL),(346,274,'day_of_month','1',NULL),(347,274,'month_int','1',NULL),(348,274,'always_week_day_id','3',NULL),(349,275,'special_day','0',NULL),(350,275,'type_id','30',NULL),(351,275,'pivot_day_direction_id','20',NULL),(352,275,'name','US - Memorial Day',NULL),(353,275,'day_of_week','1',NULL),(354,275,'day_of_month','24',NULL),(355,275,'month_int','5',NULL),(356,275,'always_week_day_id','3',NULL),(357,276,'special_day','0',NULL),(358,276,'type_id','10',NULL),(359,276,'name','US - Independence Day',NULL),(360,276,'day_of_month','4',NULL),(361,276,'month_int','7',NULL),(362,276,'always_week_day_id','3',NULL),(363,277,'special_day','0',NULL),(364,277,'type_id','20',NULL),(365,277,'name','US - Labour Day',NULL),(366,277,'week_interval','1',NULL),(367,277,'day_of_week','1',NULL),(368,277,'month_int','9',NULL),(369,277,'always_week_day_id','3',NULL),(370,278,'special_day','0',NULL),(371,278,'type_id','10',NULL),(372,278,'name','US - Veterans Day',NULL),(373,278,'day_of_month','11',NULL),(374,278,'month_int','11',NULL),(375,278,'always_week_day_id','3',NULL),(376,279,'special_day','0',NULL),(377,279,'type_id','20',NULL),(378,279,'name','US - Thanksgiving Day',NULL),(379,279,'week_interval','4',NULL),(380,279,'day_of_week','4',NULL),(381,279,'month_int','11',NULL),(382,279,'always_week_day_id','3',NULL),(383,280,'special_day','0',NULL),(384,280,'type_id','10',NULL),(385,280,'name','US - Christmas Day',NULL),(386,280,'day_of_month','25',NULL),(387,280,'month_int','12',NULL),(388,280,'always_week_day_id','3',NULL),(389,281,'special_day','0',NULL),(390,281,'type_id','20',NULL),(391,281,'name','US - Martin Luther King Day',NULL),(392,281,'week_interval','3',NULL),(393,281,'day_of_week','1',NULL),(394,281,'month_int','1',NULL),(395,281,'always_week_day_id','3',NULL),(396,282,'special_day','0',NULL),(397,282,'type_id','20',NULL),(398,282,'name','US - Presidents Day',NULL),(399,282,'week_interval','3',NULL),(400,282,'day_of_week','1',NULL),(401,282,'month_int','2',NULL),(402,282,'always_week_day_id','3',NULL),(403,283,'special_day','0',NULL),(404,283,'type_id','10',NULL),(405,283,'name','US - Christmas Eve',NULL),(406,283,'day_of_month','24',NULL),(407,283,'month_int','12',NULL),(408,283,'always_week_day_id','3',NULL),(409,284,'special_day','0',NULL),(410,284,'type_id','20',NULL),(411,284,'name','US - Columbus Day',NULL),(412,284,'week_interval','2',NULL),(413,284,'day_of_week','1',NULL),(414,284,'month_int','10',NULL),(415,284,'always_week_day_id','3',NULL),(416,285,'name','Time Bank',NULL),(417,285,'enable_pay_stub_balance_display','1',NULL),(418,286,'name','Paid Time Off (PTO)',NULL),(419,286,'enable_pay_stub_balance_display','1',NULL),(420,287,'type_id','20',NULL),(421,287,'accrual_policy_account_id','3',NULL),(422,287,'name','Paid Time Off (PTO)',NULL),(423,287,'apply_frequency_id','10',NULL),(424,287,'milestone_rollover_hire_date','1',NULL),(425,288,'accrual_policy_id','2',NULL),(426,288,'length_of_service_unit_id','40',NULL),(427,288,'maximum_time','0',NULL),(428,288,'rollover_time','35996400',NULL),(429,288,'length_of_service_days','0.00',NULL),(430,289,'accrual_policy_id','2',NULL),(431,289,'length_of_service','5',NULL),(432,289,'length_of_service_unit_id','40',NULL),(433,289,'maximum_time','0',NULL),(434,289,'rollover_time','35996400',NULL),(435,289,'length_of_service_days','1826.25',NULL),(436,290,'name','None ($0)',NULL),(437,290,'pay_type_id','10',NULL),(438,290,'wage_group_id','0',NULL),(439,291,'name','Regular',NULL),(440,291,'pay_type_id','10',NULL),(441,291,'rate','1',NULL),(442,291,'wage_group_id','0',NULL),(443,292,'name','OverTime (1.5x)',NULL),(444,292,'pay_type_id','10',NULL),(445,292,'rate','1.5',NULL),(446,292,'wage_group_id','0',NULL),(447,293,'name','OverTime (2.0x)',NULL),(448,293,'pay_type_id','10',NULL),(449,293,'rate','2',NULL),(450,293,'wage_group_id','0',NULL),(451,294,'name','Sick',NULL),(452,294,'pay_type_id','10',NULL),(453,294,'rate','1',NULL),(454,294,'wage_group_id','0',NULL),(455,294,'accrual_rate','1',NULL),(456,295,'name','Time Bank',NULL),(457,295,'pay_type_id','10',NULL),(458,295,'wage_group_id','0',NULL),(459,295,'accrual_rate','1',NULL),(460,295,'accrual_policy_account_id','2',NULL),(461,296,'name','Vacation',NULL),(462,296,'pay_type_id','10',NULL),(463,296,'rate','1',NULL),(464,296,'wage_group_id','0',NULL),(465,296,'accrual_rate','1',NULL),(466,297,'name','UnPaid',NULL),(467,297,'code','UNPAID',NULL),(468,297,'type_id','20',NULL),(469,297,'pay_formula_policy_id','2',NULL),(470,298,'name','Regular Time',NULL),(471,298,'code','REG',NULL),(472,298,'type_id','10',NULL),(473,298,'pay_formula_policy_id','3',NULL),(474,298,'pay_stub_entry_account_id','2',NULL),(475,299,'name','Lunch Time',NULL),(476,299,'code','LNH',NULL),(477,299,'type_id','10',NULL),(478,299,'pay_formula_policy_id','3',NULL),(479,299,'pay_stub_entry_account_id','2',NULL),(480,300,'name','Break Time',NULL),(481,300,'code','BRK',NULL),(482,300,'type_id','10',NULL),(483,300,'pay_formula_policy_id','3',NULL),(484,300,'pay_stub_entry_account_id','2',NULL),(485,301,'name','OverTime (1.5x)',NULL),(486,301,'code','OT1',NULL),(487,301,'type_id','10',NULL),(488,301,'pay_formula_policy_id','4',NULL),(489,301,'pay_stub_entry_account_id','3',NULL),(490,302,'name','OverTime (2.0x)',NULL),(491,302,'code','OT1',NULL),(492,302,'type_id','10',NULL),(493,302,'pay_formula_policy_id','5',NULL),(494,302,'pay_stub_entry_account_id','4',NULL),(495,303,'name','Premium 1',NULL),(496,303,'code','PRE1',NULL),(497,303,'type_id','10',NULL),(498,303,'pay_stub_entry_account_id','5',NULL),(499,304,'name','Premium 2',NULL),(500,304,'code','PRE2',NULL),(501,304,'type_id','10',NULL),(502,304,'pay_stub_entry_account_id','6',NULL),(503,305,'name','Sick',NULL),(504,305,'code','SICK',NULL),(505,305,'type_id','10',NULL),(506,305,'pay_formula_policy_id','6',NULL),(507,305,'pay_stub_entry_account_id','8',NULL),(508,306,'name','Sick (UNPAID)',NULL),(509,306,'code','USICK',NULL),(510,306,'type_id','20',NULL),(511,306,'pay_formula_policy_id','2',NULL),(512,307,'name','Time Bank',NULL),(513,307,'code','BANK',NULL),(514,307,'type_id','20',NULL),(515,307,'pay_formula_policy_id','7',NULL),(516,308,'name','Statutory Holiday',NULL),(517,308,'code','STAT',NULL),(518,308,'type_id','10',NULL),(519,308,'pay_formula_policy_id','3',NULL),(520,308,'pay_stub_entry_account_id','7',NULL),(521,309,'name','Vacation (UNPAID)',NULL),(522,309,'code','UVAC',NULL),(523,309,'type_id','20',NULL),(524,309,'pay_formula_policy_id','2',NULL),(525,310,'name','Jury Duty',NULL),(526,310,'code','JURY',NULL),(527,310,'type_id','20',NULL),(528,310,'pay_formula_policy_id','2',NULL),(529,311,'name','Bereavement',NULL),(530,311,'code','BEREAV',NULL),(531,311,'type_id','20',NULL),(532,311,'pay_formula_policy_id','2',NULL),(533,312,'name','Vacation',NULL),(534,312,'code','VAC',NULL),(535,312,'type_id','10',NULL),(536,312,'pay_formula_policy_id','8',NULL),(537,312,'pay_stub_entry_account_id','46',NULL),(538,313,'name','Regular Time',NULL),(539,314,'name','Regular Time + Meal',NULL),(540,315,'name','Regular Time + Break',NULL),(541,316,'name','Regular Time + Meal + Break',NULL),(542,317,'name','Regular Time + OT',NULL),(543,318,'name','Regular Time + Paid Absence',NULL),(544,319,'name','Regular Time + Paid Absence + Meal + Break',NULL),(545,320,'name','Regular Time + OT + Meal',NULL),(546,321,'name','Regular Time + OT + Break',NULL),(547,322,'name','Regular Time + OT + Meal + Break',NULL),(548,323,'name','Regular Time + OT + Paid Absence',NULL),(549,324,'name','Regular Time + OT + Paid Absence + Meal + Break',NULL),(550,325,'name','Regular Time',NULL),(551,325,'contributing_pay_code_policy_id','2',NULL),(552,325,'branch_selection_type_id','10',NULL),(553,325,'department_selection_type_id','10',NULL),(554,325,'job_group_selection_type_id','10',NULL),(555,325,'job_selection_type_id','10',NULL),(556,325,'job_item_group_selection_type_id','10',NULL),(557,325,'job_item_selection_type_id','10',NULL),(558,326,'name','Regular Time + Break',NULL),(559,326,'contributing_pay_code_policy_id','4',NULL),(560,326,'branch_selection_type_id','10',NULL),(561,326,'department_selection_type_id','10',NULL),(562,326,'job_group_selection_type_id','10',NULL),(563,326,'job_selection_type_id','10',NULL),(564,326,'job_item_group_selection_type_id','10',NULL),(565,326,'job_item_selection_type_id','10',NULL),(566,327,'name','Regular Time + Meal',NULL),(567,327,'contributing_pay_code_policy_id','3',NULL),(568,327,'branch_selection_type_id','10',NULL),(569,327,'department_selection_type_id','10',NULL),(570,327,'job_group_selection_type_id','10',NULL),(571,327,'job_selection_type_id','10',NULL),(572,327,'job_item_group_selection_type_id','10',NULL),(573,327,'job_item_selection_type_id','10',NULL),(574,328,'name','Regular Time + Meal + Break',NULL),(575,328,'contributing_pay_code_policy_id','5',NULL),(576,328,'branch_selection_type_id','10',NULL),(577,328,'department_selection_type_id','10',NULL),(578,328,'job_group_selection_type_id','10',NULL),(579,328,'job_selection_type_id','10',NULL),(580,328,'job_item_group_selection_type_id','10',NULL),(581,328,'job_item_selection_type_id','10',NULL),(582,329,'name','Regular Time + Paid Absence',NULL),(583,329,'contributing_pay_code_policy_id','7',NULL),(584,329,'branch_selection_type_id','10',NULL),(585,329,'department_selection_type_id','10',NULL),(586,329,'job_group_selection_type_id','10',NULL),(587,329,'job_selection_type_id','10',NULL),(588,329,'job_item_group_selection_type_id','10',NULL),(589,329,'job_item_selection_type_id','10',NULL),(590,330,'name','Regular Time + Paid Absence + Meal + Break',NULL),(591,330,'contributing_pay_code_policy_id','8',NULL),(592,330,'branch_selection_type_id','10',NULL),(593,330,'department_selection_type_id','10',NULL),(594,330,'job_group_selection_type_id','10',NULL),(595,330,'job_selection_type_id','10',NULL),(596,330,'job_item_group_selection_type_id','10',NULL),(597,330,'job_item_selection_type_id','10',NULL),(598,331,'name','Regular Time + OT',NULL),(599,331,'contributing_pay_code_policy_id','6',NULL),(600,331,'branch_selection_type_id','10',NULL),(601,331,'department_selection_type_id','10',NULL),(602,331,'job_group_selection_type_id','10',NULL),(603,331,'job_selection_type_id','10',NULL),(604,331,'job_item_group_selection_type_id','10',NULL),(605,331,'job_item_selection_type_id','10',NULL),(606,332,'name','Regular Time + OT + Meal',NULL),(607,332,'contributing_pay_code_policy_id','9',NULL),(608,332,'branch_selection_type_id','10',NULL),(609,332,'department_selection_type_id','10',NULL),(610,332,'job_group_selection_type_id','10',NULL),(611,332,'job_selection_type_id','10',NULL),(612,332,'job_item_group_selection_type_id','10',NULL),(613,332,'job_item_selection_type_id','10',NULL),(614,333,'name','Regular Time + OT + Meal + Break',NULL),(615,333,'contributing_pay_code_policy_id','11',NULL),(616,333,'branch_selection_type_id','10',NULL),(617,333,'department_selection_type_id','10',NULL),(618,333,'job_group_selection_type_id','10',NULL),(619,333,'job_selection_type_id','10',NULL),(620,333,'job_item_group_selection_type_id','10',NULL),(621,333,'job_item_selection_type_id','10',NULL),(622,334,'name','Regular Time + OT + Paid Absence',NULL),(623,334,'contributing_pay_code_policy_id','12',NULL),(624,334,'branch_selection_type_id','10',NULL),(625,334,'department_selection_type_id','10',NULL),(626,334,'job_group_selection_type_id','10',NULL),(627,334,'job_selection_type_id','10',NULL),(628,334,'job_item_group_selection_type_id','10',NULL),(629,334,'job_item_selection_type_id','10',NULL),(630,335,'name','Regular Time + OT + Paid Absence + Meal + Break',NULL),(631,335,'contributing_pay_code_policy_id','13',NULL),(632,335,'branch_selection_type_id','10',NULL),(633,335,'department_selection_type_id','10',NULL),(634,335,'job_group_selection_type_id','10',NULL),(635,335,'job_selection_type_id','10',NULL),(636,335,'job_item_group_selection_type_id','10',NULL),(637,335,'job_item_selection_type_id','10',NULL),(638,336,'name','Vacation (PAID)',NULL),(639,336,'pay_code_id','17',NULL),(640,337,'name','Vacation (UNPAID)',NULL),(641,337,'pay_code_id','14',NULL),(642,338,'name','Sick (PAID)',NULL),(643,338,'pay_code_id','10',NULL),(644,339,'name','Sick (UNPAID)',NULL),(645,339,'pay_code_id','11',NULL),(646,340,'name','Jury Duty',NULL),(647,340,'pay_code_id','15',NULL),(648,341,'name','Bereavement',NULL),(649,341,'pay_code_id','16',NULL),(650,342,'name','Statutory Holiday',NULL),(651,342,'pay_code_id','13',NULL),(652,343,'name','Time Bank (Withdrawal)',NULL),(653,343,'pay_code_id','12',NULL),(654,355,'type_id','10',NULL),(655,355,'name','US - Statutory Holiday',NULL),(656,355,'default_schedule_status_id','20',NULL),(657,355,'minimum_employed_days','30',NULL),(658,355,'minimum_time','28800',NULL),(659,355,'contributing_shift_policy_id','10',NULL),(660,355,'eligible_contributing_shift_policy_id','8',NULL),(661,355,'absence_policy_id','8',NULL),(662,356,'name','Regular Time',NULL),(663,356,'contributing_shift_policy_id','2',NULL),(664,356,'pay_code_id','3',NULL),(665,356,'calculation_order','9999',NULL),(666,357,'type_id','180',NULL),(667,357,'name','US - Holiday',NULL),(668,357,'contributing_shift_policy_id','5',NULL),(669,357,'pay_code_id','6',NULL),(670,358,'type_id','20',NULL),(671,358,'name','30min Lunch',NULL),(672,358,'trigger_time','18000',NULL),(673,358,'amount','1800',NULL),(674,358,'auto_detect_type_id','20',NULL),(675,358,'minimum_punch_time','1200',NULL),(676,358,'maximum_punch_time','2400',NULL),(677,358,'pay_code_id','4',NULL),(678,359,'type_id','20',NULL),(679,359,'name','60min Lunch',NULL),(680,359,'trigger_time','25200',NULL),(681,359,'amount','3600',NULL),(682,359,'auto_detect_type_id','20',NULL),(683,359,'minimum_punch_time','2700',NULL),(684,359,'maximum_punch_time','4500',NULL),(685,359,'pay_code_id','4',NULL),(686,360,'type_id','20',NULL),(687,360,'name','Break1',NULL),(688,360,'trigger_time','7200',NULL),(689,360,'amount','900',NULL),(690,360,'auto_detect_type_id','20',NULL),(691,360,'minimum_punch_time','300',NULL),(692,360,'maximum_punch_time','1140',NULL),(693,360,'pay_code_id','5',NULL),(694,361,'type_id','20',NULL),(695,361,'name','Break2',NULL),(696,361,'trigger_time','18000',NULL),(697,361,'amount','900',NULL),(698,361,'auto_detect_type_id','20',NULL),(699,361,'minimum_punch_time','300',NULL),(700,361,'maximum_punch_time','1140',NULL),(701,361,'pay_code_id','5',NULL),(702,362,'name','No Lunch',NULL),(703,362,'start_stop_window','7200',NULL),(704,363,'name','30min Lunch',NULL),(705,363,'start_stop_window','7200',NULL),(706,364,'name','60min Lunch',NULL),(707,364,'start_stop_window','7200',NULL),(708,365,'name','Default',NULL),(709,366,'exception_policy_control_id','2',NULL),(710,366,'type_id','S1',NULL),(711,366,'severity_id','10',NULL),(712,366,'watch_window','0',NULL),(713,366,'grace','0',NULL),(714,366,'demerit','25',NULL),(715,366,'email_notification_id','100',NULL),(716,367,'exception_policy_control_id','2',NULL),(717,367,'type_id','S2',NULL),(718,367,'severity_id','10',NULL),(719,367,'watch_window','0',NULL),(720,367,'grace','0',NULL),(721,367,'demerit','10',NULL),(722,367,'email_notification_id','100',NULL),(723,368,'exception_policy_control_id','2',NULL),(724,368,'type_id','S3',NULL),(725,368,'severity_id','10',NULL),(726,368,'watch_window','7200',NULL),(727,368,'grace','900',NULL),(728,368,'demerit','2',NULL),(729,368,'email_notification_id','20',NULL),(730,368,'active','1',NULL),(731,369,'exception_policy_control_id','2',NULL),(732,369,'type_id','S4',NULL),(733,369,'severity_id','25',NULL),(734,369,'watch_window','7200',NULL),(735,369,'grace','900',NULL),(736,369,'demerit','10',NULL),(737,369,'email_notification_id','20',NULL),(738,369,'active','1',NULL),(739,370,'exception_policy_control_id','2',NULL),(740,370,'type_id','S5',NULL),(741,370,'severity_id','20',NULL),(742,370,'watch_window','7200',NULL),(743,370,'grace','900',NULL),(744,370,'demerit','10',NULL),(745,370,'email_notification_id','20',NULL),(746,370,'active','1',NULL),(747,371,'exception_policy_control_id','2',NULL),(748,371,'type_id','S6',NULL),(749,371,'severity_id','10',NULL),(750,371,'watch_window','7200',NULL),(751,371,'grace','900',NULL),(752,371,'demerit','2',NULL),(753,371,'email_notification_id','20',NULL),(754,371,'active','1',NULL),(755,372,'exception_policy_control_id','2',NULL),(756,372,'type_id','S7',NULL),(757,372,'severity_id','10',NULL),(758,372,'watch_window','0',NULL),(759,372,'grace','900',NULL),(760,372,'demerit','2',NULL),(761,373,'exception_policy_control_id','2',NULL),(762,373,'type_id','S8',NULL),(763,373,'severity_id','20',NULL),(764,373,'watch_window','0',NULL),(765,373,'grace','900',NULL),(766,373,'demerit','2',NULL),(767,374,'exception_policy_control_id','2',NULL),(768,374,'type_id','S9',NULL),(769,374,'severity_id','20',NULL),(770,374,'watch_window','0',NULL),(771,374,'grace','900',NULL),(772,374,'demerit','5',NULL),(773,374,'email_notification_id','100',NULL),(774,375,'exception_policy_control_id','2',NULL),(775,375,'type_id','SB',NULL),(776,375,'severity_id','10',NULL),(777,375,'watch_window','0',NULL),(778,375,'grace','0',NULL),(779,375,'demerit','5',NULL),(780,375,'email_notification_id','100',NULL),(781,376,'exception_policy_control_id','2',NULL),(782,376,'type_id','O1',NULL),(783,376,'severity_id','20',NULL),(784,376,'watch_window','28800',NULL),(785,376,'grace','0',NULL),(786,376,'demerit','2',NULL),(787,376,'email_notification_id','100',NULL),(788,377,'exception_policy_control_id','2',NULL),(789,377,'type_id','O2',NULL),(790,377,'severity_id','20',NULL),(791,377,'watch_window','144000',NULL),(792,377,'grace','0',NULL),(793,377,'demerit','5',NULL),(794,377,'email_notification_id','100',NULL),(795,378,'exception_policy_control_id','2',NULL),(796,378,'type_id','M1',NULL),(797,378,'severity_id','30',NULL),(798,378,'watch_window','0',NULL),(799,378,'grace','0',NULL),(800,378,'demerit','20',NULL),(801,378,'email_notification_id','100',NULL),(802,378,'active','1',NULL),(803,379,'exception_policy_control_id','2',NULL),(804,379,'type_id','M2',NULL),(805,379,'severity_id','30',NULL),(806,379,'watch_window','0',NULL),(807,379,'grace','0',NULL),(808,379,'demerit','20',NULL),(809,379,'email_notification_id','100',NULL),(810,379,'active','1',NULL),(811,380,'exception_policy_control_id','2',NULL),(812,380,'type_id','M3',NULL),(813,380,'severity_id','30',NULL),(814,380,'watch_window','0',NULL),(815,380,'grace','0',NULL),(816,380,'demerit','18',NULL),(817,380,'email_notification_id','100',NULL),(818,380,'active','1',NULL),(819,381,'exception_policy_control_id','2',NULL),(820,381,'type_id','M4',NULL),(821,381,'severity_id','30',NULL),(822,381,'watch_window','0',NULL),(823,381,'grace','0',NULL),(824,381,'demerit','17',NULL),(825,381,'email_notification_id','100',NULL),(826,381,'active','1',NULL),(827,382,'exception_policy_control_id','2',NULL),(828,382,'type_id','L1',NULL),(829,382,'severity_id','20',NULL),(830,382,'watch_window','0',NULL),(831,382,'grace','900',NULL),(832,382,'demerit','5',NULL),(833,383,'exception_policy_control_id','2',NULL),(834,383,'type_id','L2',NULL),(835,383,'severity_id','20',NULL),(836,383,'watch_window','0',NULL),(837,383,'grace','900',NULL),(838,383,'demerit','5',NULL),(839,384,'exception_policy_control_id','2',NULL),(840,384,'type_id','L3',NULL),(841,384,'severity_id','20',NULL),(842,384,'watch_window','0',NULL),(843,384,'grace','0',NULL),(844,384,'demerit','5',NULL),(845,384,'email_notification_id','100',NULL),(846,385,'exception_policy_control_id','2',NULL),(847,385,'type_id','B1',NULL),(848,385,'severity_id','20',NULL),(849,385,'watch_window','0',NULL),(850,385,'grace','300',NULL),(851,385,'demerit','5',NULL),(852,386,'exception_policy_control_id','2',NULL),(853,386,'type_id','B2',NULL),(854,386,'severity_id','20',NULL),(855,386,'watch_window','0',NULL),(856,386,'grace','300',NULL),(857,386,'demerit','5',NULL),(858,387,'exception_policy_control_id','2',NULL),(859,387,'type_id','B3',NULL),(860,387,'severity_id','20',NULL),(861,387,'watch_window','0',NULL),(862,387,'grace','0',NULL),(863,387,'demerit','5',NULL),(864,387,'email_notification_id','100',NULL),(865,388,'exception_policy_control_id','2',NULL),(866,388,'type_id','B4',NULL),(867,388,'severity_id','20',NULL),(868,388,'watch_window','0',NULL),(869,388,'grace','0',NULL),(870,388,'demerit','5',NULL),(871,388,'email_notification_id','100',NULL),(872,389,'exception_policy_control_id','2',NULL),(873,389,'type_id','B5',NULL),(874,389,'severity_id','20',NULL),(875,389,'watch_window','0',NULL),(876,389,'grace','0',NULL),(877,389,'demerit','5',NULL),(878,389,'email_notification_id','100',NULL),(879,390,'exception_policy_control_id','2',NULL),(880,390,'type_id','D1',NULL),(881,390,'severity_id','10',NULL),(882,390,'watch_window','0',NULL),(883,390,'grace','0',NULL),(884,390,'demerit','5',NULL),(885,391,'exception_policy_control_id','2',NULL),(886,391,'type_id','V1',NULL),(887,391,'severity_id','25',NULL),(888,391,'watch_window','0',NULL),(889,391,'grace','172800',NULL),(890,391,'demerit','5',NULL),(891,391,'email_notification_id','100',NULL),(892,392,'status_id','10',NULL),(893,392,'type_id','20',NULL),(894,392,'name','AL - State Income Tax',NULL),(895,392,'ps_order','204',NULL),(896,392,'accrual_type_id','10',NULL),(897,393,'status_id','10',NULL),(898,393,'type_id','20',NULL),(899,393,'name','AL - District Income Tax',NULL),(900,393,'ps_order','206',NULL),(901,393,'accrual_type_id','10',NULL),(902,394,'status_id','10',NULL),(903,394,'type_id','30',NULL),(904,394,'name','AL - Unemployment Insurance',NULL),(905,394,'ps_order','306',NULL),(906,394,'accrual_type_id','10',NULL),(907,395,'status_id','10',NULL),(908,395,'type_id','30',NULL),(909,395,'name','AL - Employment Security Assessment',NULL),(910,395,'ps_order','310',NULL),(911,395,'accrual_type_id','10',NULL),(912,401,'status_id','10',NULL),(913,401,'type_id','10',NULL),(914,401,'name','AL - State Income Tax',NULL),(915,401,'calculation_id','200',NULL),(916,401,'calculation_order','200',NULL),(917,401,'country','US',NULL),(918,401,'province','AL',NULL),(919,401,'user_value1','10',NULL),(920,401,'user_value2','0',NULL),(921,401,'pay_stub_entry_account_id','47',NULL),(922,401,'minimum_length_of_service_days','0',NULL),(923,401,'maximum_length_of_service_days','0',NULL),(924,401,'apply_frequency_id','10',NULL),(925,402,'status_id','10',NULL),(926,402,'type_id','10',NULL),(927,402,'name','AL - State Addl. Income Tax',NULL),(928,402,'calculation_id','20',NULL),(929,402,'calculation_order','205',NULL),(930,402,'user_value1','0',NULL),(931,402,'pay_stub_entry_account_id','47',NULL),(932,402,'minimum_length_of_service_days','0',NULL),(933,402,'maximum_length_of_service_days','0',NULL),(934,402,'apply_frequency_id','10',NULL),(935,407,'status_id','10',NULL),(936,407,'type_id','10',NULL),(937,407,'name','AL - Employment Security Assessment',NULL),(938,407,'calculation_id','15',NULL),(939,407,'calculation_order','186',NULL),(940,407,'user_value1','0',NULL),(941,407,'user_value2','8000',NULL),(942,407,'user_value3','0',NULL),(943,407,'pay_stub_entry_account_id','50',NULL),(944,407,'minimum_length_of_service_days','0',NULL),(945,407,'maximum_length_of_service_days','0',NULL),(946,407,'apply_frequency_id','10',NULL),(947,412,'status_id','10',NULL),(948,412,'type_id','10',NULL),(949,412,'name','AL - Unemployment Insurance - Employer',NULL),(950,412,'calculation_id','15',NULL),(951,412,'calculation_order','185',NULL),(952,412,'user_value1','0',NULL),(953,412,'user_value2','8000',NULL),(954,412,'user_value3','0',NULL),(955,412,'pay_stub_entry_account_id','49',NULL),(956,412,'minimum_length_of_service_days','0',NULL),(957,412,'maximum_length_of_service_days','0',NULL),(958,412,'apply_frequency_id','10',NULL),(959,413,'special_day','0',NULL),(960,413,'type_id','10',NULL),(961,413,'name','AL - Day After Christmas',NULL),(962,413,'day_of_month','26',NULL),(963,413,'month_int','12',NULL),(964,413,'always_week_day_id','3',NULL),(965,414,'special_day','0',NULL),(966,414,'type_id','10',NULL),(967,414,'name','AL - New Years Eve',NULL),(968,414,'day_of_month','31',NULL),(969,414,'month_int','12',NULL),(970,414,'always_week_day_id','3',NULL),(971,415,'type_id','20',NULL),(972,415,'name','AL - Weekly >40hrs',NULL),(973,415,'trigger_time','144000',NULL),(974,415,'contributing_shift_policy_id','5',NULL),(975,415,'pay_code_id','6',NULL),(976,428,'name','AL - Hourly (OT Non-Exempt)',NULL),(977,428,'exception_policy_control_id','2',NULL),(978,439,'name','AL - Salary (OT Exempt)',NULL),(979,439,'exception_policy_control_id','2',NULL),(980,440,'city','San Francisco',NULL),(981,440,'country','US',NULL),(982,440,'province','AL',NULL),(983,440,'work_phone','555 1425 548',NULL),(984,440,'language','en',NULL),(985,440,'items_per_page','50',NULL),(986,440,'date_format','d-M-y',NULL),(987,440,'time_format','g:i A',NULL),(988,440,'time_unit_format','10',NULL),(989,440,'start_week_day','0',NULL),(990,440,'policy_group_id','2',NULL),(991,440,'currency_id','2',NULL),(992,440,'time_zone','CST6CDT',NULL),(993,440,'enable_email_notification_exception','1',NULL),(994,440,'enable_email_notification_message','1',NULL),(995,440,'enable_email_notification_pay_stub','1',NULL),(996,440,'enable_email_notification_home','1',NULL),(997,441,'status_id','10',NULL),(998,441,'user_name','admin',NULL),(999,441,'employee_number','1',NULL),(1000,441,'first_name','Admin',NULL),(1001,441,'last_name','Admin',NULL),(1002,441,'work_email','admin@test.com',NULL),(1003,441,'country','US',NULL),(1004,441,'province','AL',NULL),(1005,441,'city','San Francisco',NULL),(1006,441,'work_phone','555 1425 548',NULL),(1007,441,'home_phone','555 1425 548',NULL),(1008,441,'currency_id','2',NULL),(1009,441,'sex_id','5',NULL),(1010,444,'user_id','2',NULL),(1011,444,'language','en',NULL),(1012,444,'date_format','d-M-y',NULL),(1013,444,'time_format','g:i A',NULL),(1014,444,'time_unit_format','10',NULL),(1015,444,'time_zone','CST6CDT',NULL),(1016,444,'items_per_page','50',NULL),(1017,444,'start_week_day','0',NULL),(1018,444,'enable_email_notification_exception','1',NULL),(1019,444,'enable_email_notification_message','1',NULL),(1020,444,'enable_email_notification_pay_stub','1',NULL),(1021,444,'enable_email_notification_home','1',NULL),(1022,445,'admin_contact','2',NULL),(1023,445,'billing_contact','2',NULL),(1024,445,'support_contact','2',NULL),(1025,453,'holiday_policy_id','2',NULL),(1026,453,'date_stamp','1424048400',NULL),(1027,453,'name','US - Presidents Day',NULL);
/*!40000 ALTER TABLE `system_log_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_log_detail_id_seq`
--

DROP TABLE IF EXISTS `system_log_detail_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_log_detail_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_log_detail_id_seq`
--

LOCK TABLES `system_log_detail_id_seq` WRITE;
/*!40000 ALTER TABLE `system_log_detail_id_seq` DISABLE KEYS */;
INSERT INTO `system_log_detail_id_seq` VALUES (1);
/*!40000 ALTER TABLE `system_log_detail_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_log_id_seq`
--

DROP TABLE IF EXISTS `system_log_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_log_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_log_id_seq`
--

LOCK TABLES `system_log_id_seq` WRITE;
/*!40000 ALTER TABLE `system_log_id_seq` DISABLE KEYS */;
INSERT INTO `system_log_id_seq` VALUES (468);
/*!40000 ALTER TABLE `system_log_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_setting`
--

DROP TABLE IF EXISTS `system_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_setting_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_setting`
--

LOCK TABLES `system_setting` WRITE;
/*!40000 ALTER TABLE `system_setting` DISABLE KEYS */;
INSERT INTO `system_setting` VALUES (1,'schema_version_group_A','1066A'),(2,'tax_data_version','20150101'),(3,'tax_engine_version','1.0.28'),(4,'schema_version_group_T','1032T'),(6,'system_version','8.0.0'),(7,'system_version_install_date','1423636783'),(8,'update_notify','0'),(9,'anonymous_update_notify','0'),(10,'registration_key','a7c337db1c4770d6c668509ca09171e5'),(11,'new_version','0'),(12,'valid_install_requirements','1'),(13,'auto_upgrade_failed','0');
/*!40000 ALTER TABLE `system_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_setting_id_seq`
--

DROP TABLE IF EXISTS `system_setting_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_setting_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_setting_id_seq`
--

LOCK TABLES `system_setting_id_seq` WRITE;
/*!40000 ALTER TABLE `system_setting_id_seq` DISABLE KEYS */;
INSERT INTO `system_setting_id_seq` VALUES (13);
/*!40000 ALTER TABLE `system_setting_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_contact`
--

DROP TABLE IF EXISTS `user_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `ethnic_group_id` int(11) NOT NULL,
  `first_name` varchar(250) DEFAULT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) DEFAULT NULL,
  `sex_id` int(11) DEFAULT NULL,
  `address1` varchar(250) DEFAULT NULL,
  `address2` varchar(250) DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `postal_code` varchar(250) DEFAULT NULL,
  `work_phone` varchar(250) DEFAULT NULL,
  `work_phone_ext` varchar(250) DEFAULT NULL,
  `home_phone` varchar(250) DEFAULT NULL,
  `mobile_phone` varchar(250) DEFAULT NULL,
  `fax_phone` varchar(250) DEFAULT NULL,
  `home_email` varchar(250) DEFAULT NULL,
  `work_email` varchar(250) DEFAULT NULL,
  `birth_date` int(11) DEFAULT NULL,
  `sin` varchar(250) DEFAULT NULL,
  `note` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_contact_id` (`id`),
  KEY `user_contact_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_contact`
--

LOCK TABLES `user_contact` WRITE;
/*!40000 ALTER TABLE `user_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_contact_id_seq`
--

DROP TABLE IF EXISTS `user_contact_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contact_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_contact_id_seq`
--

LOCK TABLES `user_contact_id_seq` WRITE;
/*!40000 ALTER TABLE `user_contact_id_seq` DISABLE KEYS */;
INSERT INTO `user_contact_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_contact_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_date`
--

DROP TABLE IF EXISTS `user_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_date_id` (`id`),
  UNIQUE KEY `user_date_user_id_user_date_deleted` (`user_id`,`date_stamp`,`deleted`),
  KEY `user_date_date_stamp` (`date_stamp`),
  KEY `user_date_pay_period_id` (`pay_period_id`),
  KEY `user_date_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_date`
--

LOCK TABLES `user_date` WRITE;
/*!40000 ALTER TABLE `user_date` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_date` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_date_id_seq`
--

DROP TABLE IF EXISTS `user_date_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_date_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_date_id_seq`
--

LOCK TABLES `user_date_id_seq` WRITE;
/*!40000 ALTER TABLE `user_date_id_seq` DISABLE KEYS */;
INSERT INTO `user_date_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_date_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_date_total`
--

DROP TABLE IF EXISTS `user_date_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_date_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `date_stamp` date NOT NULL,
  `object_type_id` smallint(6) NOT NULL,
  `src_object_id` int(11) NOT NULL DEFAULT '0',
  `pay_code_id` int(11) NOT NULL DEFAULT '0',
  `punch_control_id` int(11) NOT NULL DEFAULT '0',
  `branch_id` int(11) NOT NULL DEFAULT '0',
  `department_id` int(11) NOT NULL DEFAULT '0',
  `job_id` int(11) NOT NULL DEFAULT '0',
  `job_item_id` int(11) NOT NULL DEFAULT '0',
  `quantity` decimal(9,2) NOT NULL DEFAULT '0.00',
  `bad_quantity` decimal(9,2) NOT NULL DEFAULT '0.00',
  `start_type_id` smallint(6) DEFAULT NULL,
  `start_time_stamp` timestamp NULL DEFAULT NULL,
  `end_type_id` smallint(6) DEFAULT NULL,
  `end_time_stamp` timestamp NULL DEFAULT NULL,
  `total_time` int(11) NOT NULL DEFAULT '0',
  `actual_total_time` int(11) DEFAULT '0',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `currency_rate` decimal(18,10) NOT NULL DEFAULT '1.0000000000',
  `base_hourly_rate` decimal(18,4) DEFAULT '0.0000',
  `hourly_rate` decimal(18,4) DEFAULT '0.0000',
  `total_time_amount` decimal(18,4) DEFAULT '0.0000',
  `hourly_rate_with_burden` decimal(18,4) DEFAULT '0.0000',
  `total_time_amount_with_burden` decimal(18,4) DEFAULT '0.0000',
  `override` smallint(6) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `note` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_date_total_user_id_user_date` (`user_id`,`date_stamp`),
  KEY `user_date_total_object_type_id` (`object_type_id`),
  KEY `user_date_total_pay_code_id` (`pay_code_id`),
  KEY `user_date_total_pay_period_id` (`pay_period_id`),
  KEY `user_date_total_branch_id` (`branch_id`),
  KEY `user_date_total_department_id` (`department_id`),
  KEY `user_date_total_job_id` (`job_id`),
  KEY `user_date_total_job_item_id` (`job_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_date_total`
--

LOCK TABLES `user_date_total` WRITE;
/*!40000 ALTER TABLE `user_date_total` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_date_total` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_date_total_id_seq`
--

DROP TABLE IF EXISTS `user_date_total_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_date_total_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_date_total_id_seq`
--

LOCK TABLES `user_date_total_id_seq` WRITE;
/*!40000 ALTER TABLE `user_date_total_id_seq` DISABLE KEYS */;
INSERT INTO `user_date_total_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_date_total_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_date_total_old`
--

DROP TABLE IF EXISTS `user_date_total_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_date_total_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_date_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `punch_control_id` int(11) DEFAULT NULL,
  `over_time_policy_id` int(11) DEFAULT NULL,
  `absence_policy_id` int(11) DEFAULT NULL,
  `premium_policy_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `job_item_id` int(11) DEFAULT NULL,
  `quantity` decimal(9,2) DEFAULT NULL,
  `bad_quantity` decimal(9,2) DEFAULT NULL,
  `start_time_stamp` timestamp NULL DEFAULT NULL,
  `end_time_stamp` timestamp NULL DEFAULT NULL,
  `total_time` int(11) NOT NULL DEFAULT '0',
  `override` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `actual_total_time` int(11) DEFAULT '0',
  `meal_policy_id` int(11) DEFAULT NULL,
  `break_policy_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_date_total_old`
--

LOCK TABLES `user_date_total_old` WRITE;
/*!40000 ALTER TABLE `user_date_total_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_date_total_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_deduction`
--

DROP TABLE IF EXISTS `user_deduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_deduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_deduction_id` int(11) NOT NULL,
  `user_value1` varchar(250) DEFAULT NULL,
  `user_value2` varchar(250) DEFAULT NULL,
  `user_value3` varchar(250) DEFAULT NULL,
  `user_value4` varchar(250) DEFAULT NULL,
  `user_value5` varchar(250) DEFAULT NULL,
  `user_value6` varchar(250) DEFAULT NULL,
  `user_value7` varchar(250) DEFAULT NULL,
  `user_value8` varchar(250) DEFAULT NULL,
  `user_value9` varchar(250) DEFAULT NULL,
  `user_value10` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_deduction_id` (`id`),
  KEY `user_deduction_company_deduction_id` (`company_deduction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_deduction`
--

LOCK TABLES `user_deduction` WRITE;
/*!40000 ALTER TABLE `user_deduction` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_deduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_deduction_id_seq`
--

DROP TABLE IF EXISTS `user_deduction_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_deduction_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_deduction_id_seq`
--

LOCK TABLES `user_deduction_id_seq` WRITE;
/*!40000 ALTER TABLE `user_deduction_id_seq` DISABLE KEYS */;
INSERT INTO `user_deduction_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_deduction_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_default`
--

DROP TABLE IF EXISTS `user_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_default` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `pay_period_schedule_id` int(11) DEFAULT NULL,
  `policy_group_id` int(11) DEFAULT NULL,
  `employee_number` varchar(250) DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `work_email` varchar(250) DEFAULT NULL,
  `work_phone` varchar(250) DEFAULT NULL,
  `work_phone_ext` varchar(250) DEFAULT NULL,
  `hire_date` int(11) DEFAULT NULL,
  `title_id` int(11) DEFAULT NULL,
  `default_branch_id` int(11) DEFAULT NULL,
  `default_department_id` int(11) DEFAULT NULL,
  `date_format` varchar(250) DEFAULT NULL,
  `time_format` varchar(250) DEFAULT NULL,
  `time_unit_format` varchar(250) DEFAULT NULL,
  `time_zone` varchar(250) DEFAULT NULL,
  `items_per_page` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `start_week_day` int(11) DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `permission_control_id` int(11) DEFAULT NULL,
  `enable_email_notification_exception` tinyint(1) NOT NULL DEFAULT '0',
  `enable_email_notification_message` tinyint(1) NOT NULL DEFAULT '0',
  `enable_email_notification_home` tinyint(1) NOT NULL DEFAULT '0',
  `enable_email_notification_pay_stub` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_default_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_default`
--

LOCK TABLES `user_default` WRITE;
/*!40000 ALTER TABLE `user_default` DISABLE KEYS */;
INSERT INTO `user_default` VALUES (2,2,NULL,2,NULL,'San Francisco','AL','US',NULL,'555 1425 548',NULL,NULL,NULL,NULL,NULL,'d-M-y','g:i A','10','CST6CDT',50,1423636845,NULL,1423636845,NULL,NULL,NULL,0,0,'en',2,NULL,1,1,1,1);
/*!40000 ALTER TABLE `user_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_default_company_deduction`
--

DROP TABLE IF EXISTS `user_default_company_deduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_default_company_deduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_default_id` int(11) NOT NULL,
  `company_deduction_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_default_company_deduction_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_default_company_deduction`
--

LOCK TABLES `user_default_company_deduction` WRITE;
/*!40000 ALTER TABLE `user_default_company_deduction` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_default_company_deduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_default_company_deduction_id_seq`
--

DROP TABLE IF EXISTS `user_default_company_deduction_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_default_company_deduction_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_default_company_deduction_id_seq`
--

LOCK TABLES `user_default_company_deduction_id_seq` WRITE;
/*!40000 ALTER TABLE `user_default_company_deduction_id_seq` DISABLE KEYS */;
INSERT INTO `user_default_company_deduction_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_default_company_deduction_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_default_id_seq`
--

DROP TABLE IF EXISTS `user_default_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_default_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_default_id_seq`
--

LOCK TABLES `user_default_id_seq` WRITE;
/*!40000 ALTER TABLE `user_default_id_seq` DISABLE KEYS */;
INSERT INTO `user_default_id_seq` VALUES (2);
/*!40000 ALTER TABLE `user_default_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_education`
--

DROP TABLE IF EXISTS `user_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_education` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `qualification_id` int(11) NOT NULL,
  `institute` varchar(100) NOT NULL,
  `major` varchar(100) NOT NULL,
  `minor` varchar(100) NOT NULL,
  `graduate_date` int(11) DEFAULT NULL,
  `grade_score` varchar(50) DEFAULT NULL,
  `start_date` int(11) DEFAULT NULL,
  `end_date` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_education_id` (`id`),
  KEY `user_education_user_id` (`user_id`),
  KEY `user_education_qualification_id` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_education`
--

LOCK TABLES `user_education` WRITE;
/*!40000 ALTER TABLE `user_education` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_education` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_education_id_seq`
--

DROP TABLE IF EXISTS `user_education_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_education_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_education_id_seq`
--

LOCK TABLES `user_education_id_seq` WRITE;
/*!40000 ALTER TABLE `user_education_id_seq` DISABLE KEYS */;
INSERT INTO `user_education_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_education_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_generic_data`
--

DROP TABLE IF EXISTS `user_generic_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_generic_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `script` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `data` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_generic_data_id` (`id`),
  KEY `user_generic_data_company_id` (`company_id`),
  KEY `user_generic_data_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_generic_data`
--

LOCK TABLES `user_generic_data` WRITE;
/*!40000 ALTER TABLE `user_generic_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_generic_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_generic_data_id_seq`
--

DROP TABLE IF EXISTS `user_generic_data_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_generic_data_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_generic_data_id_seq`
--

LOCK TABLES `user_generic_data_id_seq` WRITE;
/*!40000 ALTER TABLE `user_generic_data_id_seq` DISABLE KEYS */;
INSERT INTO `user_generic_data_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_generic_data_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_generic_status`
--

DROP TABLE IF EXISTS `user_generic_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_generic_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `label` varchar(1024) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `link` varchar(1024) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_generic_status_id` (`id`),
  KEY `user_generic_status_user_id_batch_id` (`user_id`,`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_generic_status`
--

LOCK TABLES `user_generic_status` WRITE;
/*!40000 ALTER TABLE `user_generic_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_generic_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_generic_status_id_seq`
--

DROP TABLE IF EXISTS `user_generic_status_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_generic_status_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_generic_status_id_seq`
--

LOCK TABLES `user_generic_status_id_seq` WRITE;
/*!40000 ALTER TABLE `user_generic_status_id_seq` DISABLE KEYS */;
INSERT INTO `user_generic_status_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_generic_status_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_group_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group_tree`
--

DROP TABLE IF EXISTS `user_group_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_tree` (
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `left_id` bigint(20) NOT NULL DEFAULT '0',
  `right_id` bigint(20) NOT NULL DEFAULT '0',
  KEY `user_group_tree_left_id_right_id` (`left_id`,`right_id`),
  KEY `user_group_tree_tree_id_object_id` (`tree_id`,`object_id`),
  KEY `user_group_tree_tree_id_parent_id` (`tree_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group_tree`
--

LOCK TABLES `user_group_tree` WRITE;
/*!40000 ALTER TABLE `user_group_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_identification`
--

DROP TABLE IF EXISTS `user_identification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_identification` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `value` text,
  `extra_value` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `user_identification_id` (`id`),
  KEY `user_identification_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_identification`
--

LOCK TABLES `user_identification` WRITE;
/*!40000 ALTER TABLE `user_identification` DISABLE KEYS */;
INSERT INTO `user_identification` VALUES (2,2,5,0,'2:bae5d1334bd8d6a295ab294801a185ff30d5d826230c759d403f35efd0d0d65607de516b808b65660897a063eb54cc91c9a90eaecfdd41425df20779e8548395',NULL,1423636865,NULL,1423636865,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `user_identification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_identification_id_seq`
--

DROP TABLE IF EXISTS `user_identification_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_identification_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_identification_id_seq`
--

LOCK TABLES `user_identification_id_seq` WRITE;
/*!40000 ALTER TABLE `user_identification_id_seq` DISABLE KEYS */;
INSERT INTO `user_identification_id_seq` VALUES (2);
/*!40000 ALTER TABLE `user_identification_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_language`
--

DROP TABLE IF EXISTS `user_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `qualification_id` int(11) NOT NULL,
  `fluency_id` int(11) NOT NULL DEFAULT '0',
  `competency_id` int(11) NOT NULL DEFAULT '0',
  `description` varchar(100) NOT NULL DEFAULT '',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_language_id` (`id`),
  KEY `user_language_user_id` (`user_id`),
  KEY `user_language_qualification_id` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_language`
--

LOCK TABLES `user_language` WRITE;
/*!40000 ALTER TABLE `user_language` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_language_id_seq`
--

DROP TABLE IF EXISTS `user_language_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_language_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_language_id_seq`
--

LOCK TABLES `user_language_id_seq` WRITE;
/*!40000 ALTER TABLE `user_language_id_seq` DISABLE KEYS */;
INSERT INTO `user_language_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_language_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_license`
--

DROP TABLE IF EXISTS `user_license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `qualification_id` int(11) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_issued_date` int(11) DEFAULT NULL,
  `license_expiry_date` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_license_id` (`id`),
  KEY `user_license_user_id` (`user_id`),
  KEY `user_license_qualification_id` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_license`
--

LOCK TABLES `user_license` WRITE;
/*!40000 ALTER TABLE `user_license` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_license` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_license_id_seq`
--

DROP TABLE IF EXISTS `user_license_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_license_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_license_id_seq`
--

LOCK TABLES `user_license_id_seq` WRITE;
/*!40000 ALTER TABLE `user_license_id_seq` DISABLE KEYS */;
INSERT INTO `user_license_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_license_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_membership`
--

DROP TABLE IF EXISTS `user_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_membership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `qualification_id` int(11) NOT NULL,
  `ownership_id` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency_id` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `renewal_date` int(11) NOT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_membership_id` (`id`),
  KEY `user_membership_user_id` (`user_id`),
  KEY `user_membership_qualification_id` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_membership`
--

LOCK TABLES `user_membership` WRITE;
/*!40000 ALTER TABLE `user_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_membership_id_seq`
--

DROP TABLE IF EXISTS `user_membership_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_membership_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_membership_id_seq`
--

LOCK TABLES `user_membership_id_seq` WRITE;
/*!40000 ALTER TABLE `user_membership_id_seq` DISABLE KEYS */;
INSERT INTO `user_membership_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_membership_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_pay_period_total`
--

DROP TABLE IF EXISTS `user_pay_period_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_pay_period_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_period_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_total_time` int(11) DEFAULT NULL,
  `schedule_bank_time` int(11) DEFAULT NULL,
  `schedule_sick_time` int(11) DEFAULT NULL,
  `schedule_vacation_time` int(11) DEFAULT NULL,
  `schedule_statutory_time` int(11) DEFAULT NULL,
  `schedule_over_time_1` int(11) DEFAULT NULL,
  `schedule_over_time_2` int(11) DEFAULT NULL,
  `actual_total_time` int(11) DEFAULT NULL,
  `total_time` int(11) DEFAULT NULL,
  `bank_time` int(11) DEFAULT NULL,
  `sick_time` int(11) DEFAULT NULL,
  `vacation_time` int(11) DEFAULT NULL,
  `statutory_time` int(11) DEFAULT NULL,
  `over_time_1` int(11) DEFAULT NULL,
  `over_time_2` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_bank_time_2` int(11) DEFAULT NULL,
  `schedule_bank_time_3` int(11) DEFAULT NULL,
  `bank_time_2` int(11) DEFAULT NULL,
  `bank_time_3` int(11) DEFAULT NULL,
  `schedule_regular_time` int(11) DEFAULT NULL,
  `schedule_payable_time` int(11) DEFAULT NULL,
  `regular_time` int(11) DEFAULT NULL,
  `payable_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_pay_period_total_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_pay_period_total`
--

LOCK TABLES `user_pay_period_total` WRITE;
/*!40000 ALTER TABLE `user_pay_period_total` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_pay_period_total` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preference`
--

DROP TABLE IF EXISTS `user_preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_preference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date_format` varchar(250) NOT NULL,
  `time_format` varchar(250) NOT NULL,
  `time_unit_format` varchar(250) NOT NULL,
  `time_zone` varchar(250) NOT NULL,
  `items_per_page` int(11) DEFAULT NULL,
  `timesheet_view` int(11) DEFAULT NULL,
  `start_week_day` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(5) DEFAULT NULL,
  `enable_email_notification_exception` tinyint(1) NOT NULL DEFAULT '0',
  `enable_email_notification_message` tinyint(1) NOT NULL DEFAULT '0',
  `enable_email_notification_home` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_icalendar_type_id` smallint(6) NOT NULL DEFAULT '1',
  `schedule_icalendar_event_name` int(11) NOT NULL DEFAULT '0',
  `schedule_icalendar_alarm1_working` int(11) NOT NULL DEFAULT '3600',
  `schedule_icalendar_alarm2_working` int(11) NOT NULL DEFAULT '0',
  `schedule_icalendar_alarm1_absence` int(11) NOT NULL DEFAULT '0',
  `schedule_icalendar_alarm2_absence` int(11) NOT NULL DEFAULT '0',
  `schedule_icalendar_alarm1_modified` int(11) NOT NULL DEFAULT '7200',
  `schedule_icalendar_alarm2_modified` int(11) NOT NULL DEFAULT '3600',
  `enable_save_timesheet_state` smallint(6) NOT NULL DEFAULT '1',
  `enable_always_blank_timesheet_rows` smallint(6) NOT NULL DEFAULT '1',
  `enable_auto_context_menu` smallint(6) NOT NULL DEFAULT '1',
  `enable_report_open_new_window` smallint(6) NOT NULL DEFAULT '1',
  `user_full_name_format` smallint(6) NOT NULL DEFAULT '10',
  `shortcut_key_sequence` varchar(250) DEFAULT 'CTRL+ALT',
  `enable_email_notification_pay_stub` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preference_id` (`id`),
  UNIQUE KEY `user_preference_user_id_ukey` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preference`
--

LOCK TABLES `user_preference` WRITE;
/*!40000 ALTER TABLE `user_preference` DISABLE KEYS */;
INSERT INTO `user_preference` VALUES (2,2,'d-M-y','g:i A','10','CST6CDT',50,NULL,0,1423636865,NULL,1423636865,NULL,NULL,NULL,0,'en',1,1,1,1,0,3600,0,0,0,7200,3600,1,1,1,1,10,'CTRL+ALT',1);
/*!40000 ALTER TABLE `user_preference` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preference_id_seq`
--

DROP TABLE IF EXISTS `user_preference_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_preference_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preference_id_seq`
--

LOCK TABLES `user_preference_id_seq` WRITE;
/*!40000 ALTER TABLE `user_preference_id_seq` DISABLE KEYS */;
INSERT INTO `user_preference_id_seq` VALUES (2);
/*!40000 ALTER TABLE `user_preference_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_report_data`
--

DROP TABLE IF EXISTS `user_report_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_report_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `script` varchar(250) NOT NULL,
  `is_default` smallint(6) NOT NULL DEFAULT '0',
  `description` text,
  `data` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `user_report_data_id` (`id`),
  KEY `user_report_data_company_id` (`company_id`),
  KEY `user_report_data_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_report_data`
--

LOCK TABLES `user_report_data` WRITE;
/*!40000 ALTER TABLE `user_report_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_report_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_report_data_id_seq`
--

DROP TABLE IF EXISTS `user_report_data_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_report_data_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_report_data_id_seq`
--

LOCK TABLES `user_report_data_id_seq` WRITE;
/*!40000 ALTER TABLE `user_report_data_id_seq` DISABLE KEYS */;
INSERT INTO `user_report_data_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_report_data_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_review`
--

DROP TABLE IF EXISTS `user_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_review_control_id` int(11) NOT NULL,
  `kpi_id` int(11) NOT NULL,
  `rating` decimal(9,2) DEFAULT NULL,
  `note` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_review_id` (`id`),
  KEY `user_review_kpi_id` (`kpi_id`),
  KEY `user_review_control_id` (`user_review_control_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_review`
--

LOCK TABLES `user_review` WRITE;
/*!40000 ALTER TABLE `user_review` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_review_control`
--

DROP TABLE IF EXISTS `user_review_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_review_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `reviewer_user_id` int(11) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `severity_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `due_date` int(11) NOT NULL,
  `rating` decimal(9,2) DEFAULT NULL,
  `note` text,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_review_control_id` (`id`),
  KEY `user_review_control_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_review_control`
--

LOCK TABLES `user_review_control` WRITE;
/*!40000 ALTER TABLE `user_review_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_review_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_review_control_id_seq`
--

DROP TABLE IF EXISTS `user_review_control_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_review_control_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_review_control_id_seq`
--

LOCK TABLES `user_review_control_id_seq` WRITE;
/*!40000 ALTER TABLE `user_review_control_id_seq` DISABLE KEYS */;
INSERT INTO `user_review_control_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_review_control_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_review_id_seq`
--

DROP TABLE IF EXISTS `user_review_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_review_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_review_id_seq`
--

LOCK TABLES `user_review_id_seq` WRITE;
/*!40000 ALTER TABLE `user_review_id_seq` DISABLE KEYS */;
INSERT INTO `user_review_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_review_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_setting`
--

DROP TABLE IF EXISTS `user_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_setting_id` (`id`),
  KEY `user_setting_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_setting`
--

LOCK TABLES `user_setting` WRITE;
/*!40000 ALTER TABLE `user_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_skill`
--

DROP TABLE IF EXISTS `user_skill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `qualification_id` int(11) NOT NULL,
  `proficiency_id` int(11) NOT NULL,
  `experience` int(11) NOT NULL DEFAULT '0',
  `description` varchar(100) NOT NULL DEFAULT '',
  `first_used_date` int(11) DEFAULT NULL,
  `last_used_date` int(11) DEFAULT NULL,
  `enable_calc_experience` smallint(6) NOT NULL DEFAULT '0',
  `expiry_date` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skill_id` (`id`),
  KEY `user_skill_user_id` (`user_id`),
  KEY `user_skill_qualification_id` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_skill`
--

LOCK TABLES `user_skill` WRITE;
/*!40000 ALTER TABLE `user_skill` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_skill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_skill_id_seq`
--

DROP TABLE IF EXISTS `user_skill_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_skill_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_skill_id_seq`
--

LOCK TABLES `user_skill_id_seq` WRITE;
/*!40000 ALTER TABLE `user_skill_id_seq` DISABLE KEYS */;
INSERT INTO `user_skill_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_skill_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tax`
--

DROP TABLE IF EXISTS `user_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `federal_claim` decimal(9,2) NOT NULL,
  `provincial_claim` decimal(9,2) NOT NULL,
  `federal_additional_deduction` decimal(9,2) NOT NULL,
  `wcb_rate` decimal(9,2) NOT NULL,
  `ei_exempt` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `cpp_exempt` tinyint(1) DEFAULT '0',
  `federal_tax_exempt` tinyint(1) DEFAULT '0',
  `provincial_tax_exempt` tinyint(1) DEFAULT '0',
  `vacation_rate` decimal(9,2) NOT NULL,
  `release_vacation` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_tax_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tax`
--

LOCK TABLES `user_tax` WRITE;
/*!40000 ALTER TABLE `user_tax` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tax` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_title`
--

DROP TABLE IF EXISTS `user_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_title` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_title_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_title`
--

LOCK TABLES `user_title` WRITE;
/*!40000 ALTER TABLE `user_title` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_title` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_title_id_seq`
--

DROP TABLE IF EXISTS `user_title_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_title_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_title_id_seq`
--

LOCK TABLES `user_title_id_seq` WRITE;
/*!40000 ALTER TABLE `user_title_id_seq` DISABLE KEYS */;
INSERT INTO `user_title_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_title_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_wage`
--

DROP TABLE IF EXISTS `user_wage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_wage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `wage` decimal(20,4) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `weekly_time` int(11) DEFAULT NULL,
  `labor_burden_percent` decimal(9,2) DEFAULT NULL,
  `note` text,
  `wage_group_id` int(11) NOT NULL DEFAULT '0',
  `hourly_rate` decimal(20,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_wage_id` (`id`),
  KEY `user_wage_user_id_effective_date` (`user_id`,`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_wage`
--

LOCK TABLES `user_wage` WRITE;
/*!40000 ALTER TABLE `user_wage` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_wage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_wage_id_seq`
--

DROP TABLE IF EXISTS `user_wage_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_wage_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_wage_id_seq`
--

LOCK TABLES `user_wage_id_seq` WRITE;
/*!40000 ALTER TABLE `user_wage_id_seq` DISABLE KEYS */;
INSERT INTO `user_wage_id_seq` VALUES (1);
/*!40000 ALTER TABLE `user_wage_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `password_reset_key` varchar(250) DEFAULT NULL,
  `password_reset_date` int(11) DEFAULT NULL,
  `phone_id` varchar(250) DEFAULT NULL,
  `phone_password` varchar(250) DEFAULT NULL,
  `first_name` varchar(250) DEFAULT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) DEFAULT NULL,
  `address1` varchar(250) DEFAULT NULL,
  `address2` varchar(250) DEFAULT NULL,
  `city` varchar(250) DEFAULT NULL,
  `province` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `postal_code` varchar(250) DEFAULT NULL,
  `work_phone` varchar(250) DEFAULT NULL,
  `work_phone_ext` varchar(250) DEFAULT NULL,
  `home_phone` varchar(250) DEFAULT NULL,
  `mobile_phone` varchar(250) DEFAULT NULL,
  `fax_phone` varchar(250) DEFAULT NULL,
  `home_email` varchar(250) DEFAULT NULL,
  `work_email` varchar(250) DEFAULT NULL,
  `birth_date` int(11) DEFAULT NULL,
  `hire_date` int(11) DEFAULT NULL,
  `sin` varchar(250) DEFAULT NULL,
  `sex_id` int(11) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `labor_standard_industry` int(11) DEFAULT '0',
  `title_id` int(11) DEFAULT NULL,
  `default_branch_id` int(11) DEFAULT NULL,
  `default_department_id` int(11) DEFAULT NULL,
  `employee_number` varchar(250) DEFAULT NULL,
  `termination_date` int(11) DEFAULT NULL,
  `note` text,
  `other_id1` varchar(250) DEFAULT NULL,
  `other_id2` varchar(250) DEFAULT NULL,
  `other_id3` varchar(250) DEFAULT NULL,
  `other_id4` varchar(250) DEFAULT NULL,
  `other_id5` varchar(250) DEFAULT NULL,
  `group_id` int(11) DEFAULT '0',
  `currency_id` int(11) DEFAULT NULL,
  `second_last_name` varchar(250) DEFAULT NULL,
  `longitude` decimal(15,10) DEFAULT NULL,
  `latitude` decimal(15,10) DEFAULT NULL,
  `first_name_metaphone` varchar(250) DEFAULT NULL,
  `last_name_metaphone` varchar(250) DEFAULT NULL,
  `password_updated_date` int(11) DEFAULT NULL,
  `last_login_date` int(11) DEFAULT NULL,
  `ethnic_group_id` int(11) DEFAULT '0',
  `default_job_id` int(11) DEFAULT '0',
  `default_job_item_id` int(11) DEFAULT '0',
  `work_email_is_valid` smallint(6) DEFAULT '1',
  `work_email_is_valid_key` varchar(250) DEFAULT NULL,
  `work_email_is_valid_date` int(11) DEFAULT NULL,
  `home_email_is_valid` smallint(6) DEFAULT '1',
  `home_email_is_valid_key` varchar(250) DEFAULT NULL,
  `home_email_is_valid_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`id`),
  KEY `users_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,2,10,'admin','2:bae5d1334bd8d6a295ab294801a185ff30d5d826230c759d403f35efd0d0d65607de516b808b65660897a063eb54cc91c9a90eaecfdd41425df20779e8548395','',NULL,NULL,NULL,'Admin',NULL,'Admin','','','San Francisco','AL','US','','555 1425 548',NULL,'555 1425 548',NULL,NULL,NULL,'admin@test.com',NULL,NULL,NULL,5,1423636865,NULL,1423636865,NULL,NULL,NULL,0,0,NULL,0,0,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,2,NULL,NULL,NULL,'ATMN','ATMN',1423636865,NULL,0,0,0,1,NULL,NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_id_seq`
--

DROP TABLE IF EXISTS `users_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_id_seq`
--

LOCK TABLES `users_id_seq` WRITE;
/*!40000 ALTER TABLE `users_id_seq` DISABLE KEYS */;
INSERT INTO `users_id_seq` VALUES (2);
/*!40000 ALTER TABLE `users_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wage_group`
--

DROP TABLE IF EXISTS `wage_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wage_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `created_date` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `wage_group_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wage_group`
--

LOCK TABLES `wage_group` WRITE;
/*!40000 ALTER TABLE `wage_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `wage_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wage_group_id_seq`
--

DROP TABLE IF EXISTS `wage_group_id_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wage_group_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wage_group_id_seq`
--

LOCK TABLES `wage_group_id_seq` WRITE;
/*!40000 ALTER TABLE `wage_group_id_seq` DISABLE KEYS */;
INSERT INTO `wage_group_id_seq` VALUES (1);
/*!40000 ALTER TABLE `wage_group_id_seq` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-02-11 18:16:44
