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
  `cl_penRecGraCd` varchar(255) NOT NULL default '', -- 02
  `cl_penRecGraNm` varchar(255) NOT NULL default '', -- 2등급
  -- 본인부담금율
  `penTypeCd` varchar(255) NOT NULL default '', -- 02
  `penTypeNm` varchar(255) NOT NULL default '', -- 감경 6%
  `cl_penTypeCd` varchar(255) NOT NULL default '', -- 02
  `cl_penTypeNm` varchar(255) NOT NULL default '', -- 감경 6%
  -- 급여시작일
  `start_date` date NOT NULL, -- 급여 시작일
  `cl_start_date` date NOT NULL, -- 급여 시작일
  -- 급여비용
  `total_price` int(11) NOT NULL default 0, -- 급여비용총액
  `total_price_pen` int(11) NOT NULL default 0, -- 본인부담금
  `total_price_ent` int(11) NOT NULL default 0, -- 청구액
  `cl_total_price` int(11) NOT NULL default 0, -- 급여비용총액
  `cl_total_price_pen` int(11) NOT NULL default 0, -- 본인부담금
  `cl_total_price_ent` int(11) NOT NULL default 0, -- 청구액
  `selected_month` date NOT NULL, -- 청구관리에서 선택된 달 (값: 선택된 달의 1일)
  PRIMARY KEY (`cl_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 청구관리: 건보자료 업로드 테이블
DROP TABLE IF EXISTS `claim_nhis_upload`;
CREATE TABLE IF NOT EXISTS `claim_nhis_upload` (
  `cu_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(30) NOT NULL, -- 멤버 id
  `selected_month` date NOT NULL, -- 청구관리에서 선택된 달 (값: 선택된 달의 1일)
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`cu_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 청구관리: 건보자료 업로드 내용 테이블
DROP TABLE IF EXISTS `claim_nhis_content`;
CREATE TABLE IF NOT EXISTS `claim_nhis_content` (
  `cc_id` int(11) NOT NULL auto_increment,
  `cu_id` int(11) NOT NULL, -- 건보자료 업로드 id
  `penType` varchar(30) NOT NULL default '', -- 일반/의료
  `penNm` varchar(255) NOT NULL default '', -- 수급자 이름
  `penLtmNum` varchar(255) NOT NULL default '', -- 장기요양인정번호 ex) L123456****
  -- 장기요양등급
  `penRecGraNm` varchar(255) NOT NULL default '', -- 2등급
  -- 본인부담금율
  `penRate` int(11) NOT NULL default 0, -- 본인부담률 ex) 15 (퍼센트)
  `penTypeCd` varchar(255) NOT NULL default '', -- 02
  `penTypeNm` varchar(255) NOT NULL default '', -- 감경 6%
  `start_date` date NOT NULL, -- 급여 시작일
  `total_price` int(11) NOT NULL default 0, -- 급여비용총액
  `total_price_pen` int(11) NOT NULL default 0, -- 본인부담금
  `total_price_ent` int(11) NOT NULL default 0, -- 청구액
  PRIMARY KEY (`cc_id`),
  KEY `cu_id` (`cu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
