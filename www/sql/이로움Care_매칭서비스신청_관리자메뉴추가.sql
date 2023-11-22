/* ****************************************************************************************************************** */
/*

//
// 이로움ON 1:1 매칭신청 변경사항 SQL
//

//
// 작성자 : 박서원
// 작성일 : 23.10.23
// 목적 : 기존 매칭 서비스 신청 관련 정보를 엑셀에서 저장관리 하던 부분을 DB화 함에 따른 컬럼 추가 불가피.
//         입력 데이터 추가 및 설문지 정보 관련 데이터 저장용.
//		   
//

*/
/* ****************************************************************************************************************** */
/* ****************************************************************************************************************** */

-- 이로움Care DB
-- 이로움Care DB 회원 타입 정의 관련 컬럼 추가

-- 매칭 담당자 이름
ALTER TABLE `g5_member`
	ADD COLUMN `mb_matching_manager_nm` VARCHAR(50) NULL DEFAULT NULL COMMENT '매칭 담당자 이름.' AFTER `mb_giup_matching`;
    
-- 매칭 담당자 연락처
ALTER TABLE `g5_member`
	ADD COLUMN `mb_matching_manager_tel` VARCHAR(20) NULL DEFAULT NULL COMMENT '매칭 담당자 연락처.' AFTER `mb_giup_matching`;
    
-- 매칭 담당자 메일주소
ALTER TABLE `g5_member`
	ADD COLUMN `mb_matching_manager_mail` VARCHAR(200) NULL DEFAULT NULL COMMENT '매칭 담당자 이메일.' AFTER `mb_giup_matching`;

-- 매칭 신청일시(동의일시)
ALTER TABLE `g5_member`
	ADD COLUMN `mb_matching_dt` DATETIME NULL DEFAULT NULL COMMENT '매칭 신청일' AFTER `mb_giup_matching`;

-- 매칭 설문 답변결과
ALTER TABLE `g5_member`
	ADD COLUMN `mb_matching_forms` VARCHAR(255) NULL DEFAULT NULL COMMENT '매칭 설문답변' AFTER `mb_giup_matching`;

-- 추천인코드 (매칭동의시에만 발급)
ALTER TABLE `g5_member`
	ADD COLUMN `mb_referee_cd` VARCHAR(10) NULL DEFAULT NULL COMMENT '추천인 코드' AFTER `mb_account`;
    