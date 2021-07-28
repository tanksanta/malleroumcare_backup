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


-- 수급자활동알림
DROP TABLE IF EXISTS `recipient_noti`;
CREATE TABLE IF NOT EXISTS `recipient_noti` (
  `rn_id` int(11) NOT NULL auto_increment,
  `rn_type` enum('eform', 'upload') NOT NULL,
  `dc_id` binary(16),
  `sd_id` int(11),
  `mb_id` varchar(30) NOT NULL,
  `penNm` varchar(50) NOT NULL DEFAULT '',
  `penLtmNum` varchar(50) NOT NULL DEFAULT '',
  `ca_id` varchar(10) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `end_date` date NOT NULL,
  `rn_checked_yn` enum('N', 'Y') NOT NULL default 'N',
  PRIMARY KEY (`rn_id`),
  KEY `eform_dc_id` (`rn_type`, `dc_id`),
  KEY `upload_sd_id` (`rn_type`, `sd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
