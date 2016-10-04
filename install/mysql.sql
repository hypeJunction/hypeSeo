CREATE TABLE IF NOT EXISTS `prefix_sef_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `sef_path`  varchar(255) NOT NULL,
  `entity_guid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `custom` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sef_path` (`sef_path`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_sef_aliases` (
  `route_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_sef_data` (
  `route_id` int(11) NOT NULL,
  `title` text,
  `description` text,
  `keywords` text,
  `metatags` mediumblob,
  UNIQUE KEY `route_id` (`route_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;