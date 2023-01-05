-- 파트너 설치결과 보고서 테이블
DROP TABLE IF EXISTS `partner_install_report`;
CREATE TABLE IF NOT EXISTS `partner_install_report` (
  `ct_id` int(11) NOT NULL default 0,
  `mb_id` varchar(30) NOT NULL default '',
  `ir_issue` text NOT NULL,
  `ir_cert_name` varchar(255) NOT NULL default '' COMMENT '설치확인서 파일명',
  `ir_cert_url` varchar(255) NOT NULL default '' COMMENT '설치확인서 파일경로',
  `ir_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `ir_updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`ct_id`, `mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 파트너 설치결과 보고서 설치사진 업로드 테이블
DROP TABLE IF EXISTS `partner_install_photo`;
CREATE TABLE IF NOT EXISTS `partner_install_photo` (
  `ip_id` int(11) NOT NULL auto_increment,
  `ct_id` int(11) NOT NULL default 0,
  `mb_id` varchar(30) NOT NULL default '',
  `img_type` varchar(255) NULL default '설치사진',
  `ip_photo_name` varchar(255) NOT NULL default '',
  `ip_photo_url` varchar(255) NOT NULL default '',
  `ip_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`ip_id`),
  KEY `ct_id_mb_id` (`ct_id`, `mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 파트너 수금등록 테이블
DROP TABLE IF EXISTS `partner_ledger`;
CREATE TABLE IF NOT EXISTS `partner_ledger` (
  `pl_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(30) NOT NULL default '',
  `pl_type` tinyint(1) NOT NULL COMMENT '1: 입금 / 2: 출금',
  `pl_amount` int(11) NOT NULL DEFAULT 0,
  `pl_memo` varchar(255) NOT NULL DEFAULT '',
  `pl_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `pl_created_by` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`pl_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
