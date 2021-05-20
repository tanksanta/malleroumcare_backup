-- --------------------------------------------------------

--
-- Table structure for table `eform_document`
--

-- 계약서 테이블

DROP TABLE IF EXISTS `eform_document`;
CREATE TABLE IF NOT EXISTS `eform_document` (
  `dc_id` binary(16) NOT NULL, -- 문서 ID, PRI, UNHEX(REPLACE(UUID(),'-',''))
  `dc_subject` varchar(255) NOT NULL DEFAULT '', -- 문서 제목
  `dc_status` varchar(255) NOT NULL DEFAULT '', -- 서명 상태 (서명 대기, 서명 완료...) → todo: 생략 가능한가?
  `od_id` BIGINT(20) unsigned NOT NULL, -- 주문 ID (FK)
  -- ---------
  -- 사업소
  -- ---------
  -- 사업소 ID
  `entId` varchar(255), -- ENT2020070900001
  -- 사업소명
  `entNm` varchar(255), -- 이로움사업소1
  -- 사업자등록번호
  `entCrn` varchar(255), -- 사업자번호 todo: system.eroumcare.com 이랑 연동할지 알아봐야함
  -- 사업소이메일
  `entMail` varchar(255),
  -- 대표자명
  `entCeoNm` varchar(255),
  -- 특약사항1
  `entConAcc01` longtext, -- 본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.
  -- 특약사항2
  `entConAcc02` longtext, -- 본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.
  -- ---------
  -- 수급자
  -- ---------
  -- 수급자 ID
  `penId` varchar(255), -- PENID_20210111094719
  -- 수급자 이름
  `penNm` varchar(255), -- 테스트트
  -- 수급자 전화번호
  `penConNum` varchar(255), -- 010-8748-7796
  -- 수급자 생년월일
  `penBirth` varchar(255), -- 20210111
  -- 장기요양인정번호
  `penLtmNum` varchar(255), -- L123123123123
  -- 장기요양등급
  `penRecGraCd` varchar(255), -- 02
  `penRecGraNm` varchar(255), -- 2등급
  -- 본인부담금율
  `penTypeCd` varchar(255), -- 02
  `penTypeNm` varchar(255), -- 감경 6%
  -- 이용기간
  `penExpiDtm` varchar(255), -- 2021-01-11 ~ 2022-01-10
  -- ---------
  -- 계약서 생성 정보
  -- ---------
  -- 계약서 생성 시간
  `dc_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  -- 계약서 생성시 IP
  `dc_ip` varchar(255) NOT NULL default '',
  -- 계약서 생성 브라우저 정보
  `dc_browser` text NOT NULL,
  -- 직인 파일명
  `dc_signUrl` varchar(255) NOT NULL default '',
  -- ---------
  -- 계약서 작성 정보
  -- ---------
  -- 계약서 작성 시간
  `dc_sign_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  -- 계약서 작성시 IP
  `dc_sign_ip` varchar(255) NOT NULL default '',
  -- 계약서 작성 브라우저 정보
  `dc_sign_browser` text NOT NULL,
  -- 사인 파일명
  `dc_sign_signUrl` varchar(255) NOT NULL default '',
  PRIMARY KEY (`dc_id`),
  UNIQUE KEY `index1` (`dc_id`, `od_id`),
  KEY `dc_subject` (`dc_subject`),
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eform_document_item`
--

-- 계약서 구매/대여 물품 테이블

DROP TABLE IF EXISTS `eform_document_item`;
CREATE TABLE IF NOT EXISTS `eform_document_item` (
  `it_id` int(11) NOT NULL auto_increment, -- 물품 id (PRI, AI)
  `dc_id` binary(16) NOT NULL, -- 문서 아이디, FK
  `gubun` varchar(255) NOT NULL, -- 판매재고 00 / 대여재고 01
  `ca_name` varchar(255) NOT NULL, -- 품목명 : 이동변기
  `it_name` varchar(255) NOT NULL, -- 제품명 : APT-101
  `it_code` varchar(255) NOT NULL, -- 품목 코드
  `it_barcode` varchar(255) NOT NULL DEFAULT '', -- 바코드
  `it_qty` int(11) NOT NULL DEFAULT 1, -- 개수
  `it_date` longtext, -- 판매계약일(제공일자) or 대여계약기간(gubun == 01)
  `it_price` int(11) NOT NULL DEFAULT 0, -- 급여가
  `it_price_pen` int(11) NOT NULL DEFAULT 0, -- 수급자부담액
  `it_price_ent` int(11) NOT NULL DEFAULT 0, -- 공단부담액
  PRIMARY KEY (`it_id`),
  UNIQUE KEY `index1` (`dc_id`, `it_id`),
  KEY `index2` (`dc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eform_document_content`
--

-- 계약서 작성 내용 테이블

DROP TABLE IF EXISTS `eform_document_content`;
CREATE TABLE IF NOT EXISTS `eform_document_content` (
  `dc_id` binary(16) NOT NULL, -- 문서 아이디, FK
  `ct_id` varchar(255) NOT NULL, -- 문서 내 폼 ID
  `ct_content` longtext, -- 폼 작성 내용 todo: 사실 체크박스랑 사인밖에 없긴 한데...
  UNIQUE KEY `index1` (`dc_id`, `ct_id`),
  KEY `index2` (`dc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eform_document_log`
--

-- 계약서 로그 테이블 → 감사추적인증서

DROP TABLE IF EXISTS `eform_document_log`;
CREATE TABLE IF NOT EXISTS `eform_document_log` (
  `dl_id` int(11) NOT NULL auto_increment, -- 로그 ID, (PRI, AI)
  `dc_id` binary(16) NOT NULL, -- 문서 ID, FK
  `dl_log` text NOT NULL, -- 로그 내용
  `dl_ip` varchar(255) NOT NULL default '', -- 로그 작성 IP
  `dl_browser` text NOT NULL, -- 로그 작성 브라우저 정보
  `dl_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', -- 로그 작성 시각
  PRIMARY KEY (`dl_id`),
  KEY `index1` (`dc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
