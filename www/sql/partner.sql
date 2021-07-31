-- 파트너 설치결과 보고서 테이블
DROP TABLE IF EXISTS `partner_install_report`;
CREATE TABLE IF NOT EXISTS `partner_install_report` (
  `od_id` bigint(20) NOT NULL default 0,
  `mb_id` varchar(30) NOT NULL default '',
  `ir_issue` text NOT NULL,
  `ir_cert_url` varchar(255) NOT NULL default '',
  `ir_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `ir_updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`od_id`, `mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 파트너 설치결과 보고서 설치사진 업로드 테이블
DROP TABLE IF EXISTS `partner_install_photo`;
CREATE TABLE IF NOT EXISTS `partner_install_photo` (
  `ip_id` int(11) NOT NULL auto_increment,
  `od_id` bigint(20) NOT NULL default 0,
  `mb_id` varchar(30) NOT NULL default '',
  `ip_photo_url` varchar(255) NOT NULL default '',
  `ip_created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`ip_id`),
  KEY `od_id_mb_id` (`od_id`, `mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
