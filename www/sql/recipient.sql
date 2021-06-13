-- 수급자관리-메모 테이블

DROP TABLE IF EXISTS `recipient_memo`;
CREATE TABLE IF NOT EXISTS `recipient_memo` (
  `me_id` int(11) NOT NULL auto_increment, -- 메모 id (PRI, AI)
  `penId` varchar(255) NOT NULL default '',
  `memo` text NOT NULL,
  `me_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `me_updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`me_id`),
  KEY `penId` (`penId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
