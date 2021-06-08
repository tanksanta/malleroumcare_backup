-- 청구관리 테이블

DROP TABLE IF EXISTS `claim_management`;
CREATE TABLE IF NOT EXISTS `claim_management` (
  `cl_id` int(11) NOT NULL auto_increment, -- 청구 id (PRI, AI)
  `cl_status` varchar(30) NOT NULL default '0',
  `mb_id` varchar(30) NOT NULL, -- 멤버 id (FK)
  `penId` varchar(255) NOT NULL default '',
  `penNm` varchar(255) NOT NULL default '',
  `penLtmNum` varchar(255) NOT NULL default '',
  -- 장기요양등급
  `penRecGraCd` varchar(255) NOT NULL default '', -- 02
  `penRecGraNm` varchar(255) NOT NULL default '', -- 2등급
  -- 본인부담금율
  `penTypeCd` varchar(255) NOT NULL default '', -- 02
  `penTypeNm` varchar(255) NOT NULL default '', -- 감경 6%
  `start_date` date NOT NULL, -- 급여 시작일
  `total_price` int(11) NOT NULL default 0, -- 급여비용총액
  `total_price_pen` int(11) NOT NULL default 0, -- 본인부담금
  `total_price_ent` int(11) NOT NULL default 0, -- 청구액
  `selected_month` date NOT NULL, -- 청구관리에서 선택된 달 (값: 선택된 달의 1일)
  PRIMARY KEY (`cl_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
