-- 보유재고: 판매/대여 자료 업로드 테이블
DROP TABLE IF EXISTS `stock_data_upload`;
CREATE TABLE IF NOT EXISTS `stock_data_upload` (
  `sd_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(30) NOT NULL,
  `sd_status` tinyint(1) NOT NULL default 0 COMMENT '0: 매칭대기 / 1: 매칭완료',
  `sd_gubun` varchar(30) NOT NULL COMMENT '00: 판매 / 01: 대여',
  `sd_pen_nm` varchar(255) NOT NULL default '' COMMENT '수급자 이름',
  `sd_pen_ltm_num` varchar(255) NOT NULL default '' COMMENT '수급자 장기요양번호',
  `sd_pen_jumin` varchar(255) NOT NULL default '' COMMENT '수급자 주민등록번호',
  `sd_ca_name` varchar(255) NOT NULL default '' COMMENT '품목명',
  `sd_it_name` varchar(255) NOT NULL default '' COMMENT '제품명',
  `sd_it_code` varchar(255) NOT NULL default '' COMMENT '제품코드',
  `sd_it_barcode` varchar(255) NOT NULL default '' COMMENT '바코드값',
  `sd_contract_date` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '계약일',
  `sd_sale_date` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '판매일자/대여시작기간',
  `sd_rent_date` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '대여종료기간',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`sd_id`),
  KEY `mb_id` (`mb_id`),
  KEY `code_barcode` (`sd_it_code`, `sd_it_barcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
