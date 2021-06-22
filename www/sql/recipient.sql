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


-- 수급자연결관리-수급자사업소 relationship 테이블
DROP TABLE IF EXISTS `recipient_link_rel`;
CREATE TABLE IF NOT EXISTS `recipient_link_rel` (
  `rl_id` int(11) NOT NULL,
  `mb_id` varchar(30) NOT NULL,
  `status` varchar(255) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`rl_id`, `mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
