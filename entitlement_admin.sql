# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table app_ids
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_ids`;

CREATE TABLE `app_ids` (
  `guid` varchar(255) default '',
  `app_id` varchar(255) default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table csrf_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `csrf_tokens`;

CREATE TABLE `csrf_tokens` (
  `guid` varchar(255) default NULL,
  `token` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table folios_for_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `folios_for_groups`;

CREATE TABLE `folios_for_groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `product_id` varchar(255) default NULL,
  `group_id` int(11) default NULL,
  `guid` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table folios_for_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `folios_for_users`;

CREATE TABLE `folios_for_users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `product_id` varchar(255) default NULL,
  `user_id` int(11) default NULL,
  `guid` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `guid` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table groups_for_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `groups_for_users`;

CREATE TABLE `groups_for_users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `group_id` int(11) default NULL,
  `guid` varchar(255) default NULL,
  `user_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table requests_by_app_id
# ------------------------------------------------------------

DROP TABLE IF EXISTS `requests_by_app_id`;

CREATE TABLE `requests_by_app_id` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `app_id` varchar(255) default NULL,
  `request_count` int(11) default '1',
  `request_limit` int(11) default '1000',
  `start_date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `guid` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `auth_token` varchar(255) default NULL,
  `salt` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
