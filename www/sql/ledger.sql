-- 거래처원장 테이블
DROP TABLE IF EXISTS `ledger_content`;
CREATE TABLE IF NOT EXISTS `ledger_content` (
  `lc_id` int(11) NOT NULL auto_increment, -- 원장 id (PRI, AI)
  `mb_id` varchar(255) NOT NULL default '',
  `lc_type` tinyint(1) NOT NULL COMMENT '0: 입금 / 1: 출금',
  `lc_amount` int(11) NOT NULL DEFAULT 0,
  `lc_memo` varchar(255) NOT NULL,
  `lc_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `lc_updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`lc_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
