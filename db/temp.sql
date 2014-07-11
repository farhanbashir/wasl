/*
SQLyog Enterprise - MySQL GUI v7.14 
MySQL - 5.5.34-0ubuntu0.12.10.1 : Database - wasl
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`wasl` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `wasl`;

/*Table structure for table `event_statuses` */

DROP TABLE IF EXISTS `event_statuses`;

CREATE TABLE `event_statuses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `status` text,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `event_statuses` */

insert  into `event_statuses`(`id`,`user_id`,`event_id`,`status`,`datetime`) values (1,372,1,' asdf sda fsda fsa sfaf ',NULL),(2,372,1,'hello how are you all guys','2014-07-12 01:43:47');

/*Table structure for table `events` */

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `latitude` float(10,6) DEFAULT NULL,
  `longitude` float(10,6) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `fulltext` (`name`,`description`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `events` */

insert  into `events`(`id`,`name`,`description`,`start_date`,`end_date`,`latitude`,`longitude`,`image`,`address`,`user_id`,`created_date`) values (1,'event1','asd asd asdf asdf ads fas fdaf adsf sdaf das fasd f',NULL,NULL,10.000000,10.000000,NULL,'asd af asd fsdaf daf ',NULL,NULL),(2,'event2',' asd dsa fasd fsda fsadf asd fsda fasdf sda fas fsda ',NULL,NULL,11.000000,11.000000,NULL,'assdfa sdf sdaf ',NULL,NULL),(3,'test','asdf sad fas fsa dafs dsaf ','2014-07-01 09:07:57','2014-07-01 09:07:57',NULL,NULL,NULL,'lalukhet',NULL,'2014-07-01 09:12:01'),(4,'farhan event','hello how are you','2014-07-01 09:07:57','2014-07-01 09:07:57',NULL,NULL,'images/aosm.jpg','15/11 b area liaquatabad',NULL,'2014-07-03 12:09:55');

/*Table structure for table `followers` */

DROP TABLE IF EXISTS `followers`;

CREATE TABLE `followers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `followers` */

insert  into `followers`(`id`,`follower_id`,`user_id`,`datetime`) values (1,372,373,'2014-07-12 02:07:06'),(2,374,373,NULL);

/*Table structure for table `user_events` */

DROP TABLE IF EXISTS `user_events`;

CREATE TABLE `user_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `is_checkedIn` tinyint(1) DEFAULT '0' COMMENT '0=not checkedIn, 1=checkedIn',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `user_events` */

insert  into `user_events`(`id`,`user_id`,`event_id`,`datetime`,`is_checkedIn`) values (1,372,1,NULL,1),(2,372,2,NULL,1),(3,373,1,NULL,1),(4,373,2,'2014-06-11 09:56:17',1);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT '',
  `last_name` varchar(255) DEFAULT '',
  `username` varchar(50) NOT NULL COMMENT 'username is basically email address',
  `password` varchar(255) NOT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `phone` varchar(30) DEFAULT '',
  `user_image` varchar(255) DEFAULT '',
  `ip_address` int(10) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `status` enum('active','disabled','deleted','suspended') NOT NULL DEFAULT 'active',
  `type` tinyint(1) DEFAULT '0' COMMENT '0=email,1=linkedin',
  `personal_email` varchar(75) DEFAULT NULL,
  `company_email` varchar(75) DEFAULT NULL,
  `date_of_birth` varchar(20) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `office_no` varchar(30) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`id`,`first_name`,`last_name`,`username`,`password`,`gender`,`phone`,`user_image`,`ip_address`,`created`,`modified`,`status`,`type`,`personal_email`,`company_email`,`date_of_birth`,`designation`,`office_no`,`company_name`) values (372,'farhan','bashir','farhan@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL,NULL),(373,'farhan','bashir','farhan1','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL,NULL),(374,'farhan','bashir','farhan1@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'0345-2534488','images/bayt-logo2-en.png',NULL,NULL,NULL,'active',0,NULL,'fbashir@folio3.com','30-04-1986','sr developer','4133941','folio3');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
