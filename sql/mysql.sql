SET FOREIGN_KEY_CHECKS=0;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


--
-- `ezkaltura`
--

CREATE TABLE IF NOT EXISTS `ezkaltura` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `contentobject_attribute_id` int(11) unsigned NOT NULL,
  `version` int(11) unsigned NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- `ezkaltura_movie`
--
CREATE TABLE IF NOT EXISTS `ezkaltura_movie` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ezkaltura_id` int(11) unsigned NOT NULL,
  `entry_id` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `height` int(4) unsigned default NULL,
  `width` int(4) unsigned default NULL,
  `download_path` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `serialized_metadata` text NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`), 
  FOREIGN KEY (`ezkaltura_id`) REFERENCES ezkaltura(`id`) on delete cascade
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- `ezkaltura_thumbnail`
--
CREATE TABLE IF NOT EXISTS `ezkaltura_thumbnail` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ezkaltura_movie_id` int(11) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`), 
  FOREIGN KEY (`ezkaltura_movie_id`) REFERENCES ezkaltura_movie(`id`) on delete cascade
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- `ezkaltura_config
--
CREATE TABLE IF NOT EXISTS `ezkaltura_config` (
  `server_url` varchar(255) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `admin_secret` varchar(255) NOT NULL,
  `serialize_user` text NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

