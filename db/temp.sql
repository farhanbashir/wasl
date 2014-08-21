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

/*Table structure for table `business_card` */

DROP TABLE IF EXISTS `business_card`;

CREATE TABLE `business_card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

/*Data for the table `business_card` */

insert  into `business_card`(`id`,`from`,`to`,`datetime`) values (1,1,12,'2014-08-21 10:01:30'),(2,1,13,'2014-08-21 10:01:30'),(3,1,14,'2014-08-21 10:01:30'),(4,1,12,'2014-08-21 10:04:16'),(5,1,13,'2014-08-21 10:04:16'),(6,1,14,'2014-08-21 10:04:16');

/*Table structure for table `devices` */

DROP TABLE IF EXISTS `devices`;

CREATE TABLE `devices` (
  `device_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `uid` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '0=iphone,1=android',
  PRIMARY KEY (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `devices` */

insert  into `devices`(`device_id`,`user_id`,`uid`,`type`) values (1,372,'321321322121',0),(2,372,'373',1),(3,375,'373',1),(4,381,'373',1);

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

/*Data for the table `events` */

insert  into `events`(`id`,`name`,`description`,`start_date`,`end_date`,`latitude`,`longitude`,`image`,`address`,`user_id`,`created_date`) values (1,'event1','asd asd asdf asdf ads fas fdaf adsf sdaf das fasd f',NULL,NULL,10.000000,10.000000,NULL,'asd af asd fsdaf daf ',373,NULL),(2,'event2',' asd dsa fasd fsda fsadf asd fsda fasdf sda fas fsda ',NULL,NULL,11.000000,11.000000,NULL,'assdfa sdf sdaf ',NULL,NULL),(3,'test','asdf sad fas fsa dafs dsaf ','2014-07-01 09:07:57','2014-07-01 09:07:57',NULL,NULL,NULL,'lalukhet',NULL,'2014-07-01 09:12:01'),(4,'farhan event','hello how are you','2014-07-01 09:07:57','2014-07-01 09:07:57',NULL,NULL,'images/aosm.jpg','15/11 b area liaquatabad',NULL,'2014-07-03 12:09:55'),(5,'ooe','dsfad','2014-07-14 05:19:14','2014-07-14 05:19:14',29.878937,29.878937,'','sdf',372,'2014-07-14 05:19:14'),(6,'ooee','dsfad','2014-07-14 05:19:44','2014-07-14 05:19:44',29.878937,29.878937,'','sdf',372,'2014-07-14 05:19:44');

/*Table structure for table `followers` */

DROP TABLE IF EXISTS `followers`;

CREATE TABLE `followers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `event_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `followers` */

insert  into `followers`(`id`,`follower_id`,`user_id`,`datetime`,`event_id`) values (3,372,373,'2014-08-19 11:45:32',5);

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message` text,
  `from` int(11) unsigned NOT NULL,
  `to` int(11) unsigned NOT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `messages` */

insert  into `messages`(`id`,`message`,`from`,`to`,`datetime`) values (1,'hello',372,373,'2014-07-14 08:09:12');

/*Table structure for table `notification_events` */

DROP TABLE IF EXISTS `notification_events`;

CREATE TABLE `notification_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `notification_events` */

insert  into `notification_events`(`id`,`event_name`) values (1,'Event Join'),(2,'Share Business Card'),(3,'Follow User');

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Data for the table `notifications` */

insert  into `notifications`(`notification_id`,`from`,`to`,`event_id`,`message`,`datetime`) values (3,373,372,5,'joined the event','2014-08-19 11:39:38'),(4,372,373,5,'following the user','2014-08-19 11:45:32'),(5,1,12,0,'share business card with you','2014-08-21 10:04:16'),(6,1,13,0,'share business card with you','2014-08-21 10:04:16'),(7,1,14,0,'share business card with you','2014-08-21 10:04:16');

/*Table structure for table `user_events` */

DROP TABLE IF EXISTS `user_events`;

CREATE TABLE `user_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `is_checkedIn` tinyint(1) DEFAULT '0' COMMENT '0=not checkedIn, 1=checkedIn',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

/*Data for the table `user_events` */

insert  into `user_events`(`id`,`user_id`,`event_id`,`datetime`,`is_checkedIn`) values (1,372,1,NULL,1),(2,372,2,NULL,1),(3,373,1,NULL,1),(4,373,2,'2014-06-11 09:56:17',1),(5,372,6,'2014-07-14 05:19:44',0),(8,373,5,'2014-08-19 11:39:38',0);

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
  `verified` tinyint(1) DEFAULT '0',
  `linkedin_id` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=382 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`id`,`first_name`,`last_name`,`username`,`password`,`gender`,`phone`,`user_image`,`ip_address`,`created`,`modified`,`status`,`type`,`personal_email`,`company_email`,`date_of_birth`,`designation`,`office_no`,`company_name`,`verified`,`linkedin_id`,`token`) values (372,'farhan','bashir','farhan@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(373,'farhan','bashir','farhan1','f4a5666799f91651381ec4396103ad0d',NULL,'','',NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(374,'farhan','bashir','farhan1@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'0345-2534488','images/bayt-logo2-en.png',NULL,NULL,NULL,'active',0,NULL,'fbashir@folio3.com','30-04-1986','sr developer','4133941','folio3',0,NULL,NULL),(375,'1','','1','1',NULL,'','',NULL,NULL,NULL,'active',1,NULL,NULL,NULL,NULL,NULL,NULL,1,'1','2'),(381,'adnan','bashir','salman@bashir.com','f4a5666799f91651381ec4396103ad0d',NULL,'651667','',NULL,NULL,NULL,'active',1,NULL,'sharff@gmail.com','1986-04-30','chairman','657421','sharff',1,'46','654654asdf');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
