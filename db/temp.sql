/*
SQLyog Enterprise - MySQL GUI v7.02 
MySQL - 5.6.12-log : Database - wasl
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `event_statuses` */

insert  into `event_statuses`(`id`,`user_id`,`event_id`,`status`,`datetime`) values (1,372,1,' asdf sda fsda fsa sfaf ',NULL);

/*Table structure for table `events` */

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `datetime` datetime DEFAULT NULL,
  `latitude` float(10,6) DEFAULT NULL,
  `longitude` float(10,6) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `fulltext` (`name`,`description`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `events` */

insert  into `events`(`id`,`name`,`description`,`datetime`,`latitude`,`longitude`,`image`,`address`) values (1,'event1','asd asd asdf asdf ads fas fdaf adsf sdaf das fasd f',NULL,10.000000,10.000000,NULL,'asd af asd fsdaf daf '),(2,'event2',' asd dsa fasd fsda fsadf asd fsda fasdf sda fas fsda ',NULL,11.000000,11.000000,NULL,'assdfa sdf sdaf ');

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=374 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`id`,`first_name`,`last_name`,`username`,`password`,`gender`,`phone`,`user_image`,`ip_address`,`created`,`modified`,`status`,`type`,`personal_email`,`company_email`,`date_of_birth`,`designation`,`office_no`) values (372,'farhan','bashir','farhan@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL),(373,'farhan','bashir','farhan1','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
