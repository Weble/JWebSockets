CREATE TABLE IF NOT EXISTS `#__ws_items` (
  `ws_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` mediumtext,
  `due` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `asset_id` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ws_item_id`)
) DEFAULT CHARSET=utf8;