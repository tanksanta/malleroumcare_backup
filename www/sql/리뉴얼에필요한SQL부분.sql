/* ****************************************************************************************************************** */
/*

//
// 리뉴얼 DB관련 작업 매뉴얼!!
//

//
// "ALTER TABLE" 명령을 사용하지 않는관계로 리뉴얼시 해당 파일의 내용을 확인 후 진행 해야 한다.
//

* 이로움 리뉴얼 설정 관련 사항!!

	-- 신규 SQL 쿼리 실행 필요.

	-- 환경설정 > 회원가입 설정 
					"eroumcare_new" 스킨 선택 ( 모바일 동일 )
				> 여분필드 기본 설정
					여분필드2 값 : "/img/new_common/favicon.svg" (값수정)

	-- 게시판 관리 > 게시판 추가
		> "care_files" 게시판 추가 및 동일명 스킨 선택 (권한 및 설정 셋팅)
			-- 사업소 운영 자료실
		> "care_news" 게시판 추가 및 동일명 스킨 선택 (권한 및 설정 셋팅)
			-- 복지용구 뉴스


	-- 쇼핑몰현황/기타 > 배너관리
							사업소용 및 파트너용 배너 데이터 추가!! (기본셋팅 필요)

	-- 회원관리 > 추천상품
					추천상품 선택 및 추천 게시물 링크 데이터 입력 필요. (기본셋팅 필요)

	-- 테마관리 > 기본설정 
					"eroumcare_new" 테마 선택 / 컬러셋 "eroumcare" 선택 (PC,모바일 동일)

	-- 테마관리 > 도메인별테마관리
					도메인 : mall.eroumcare.com	테마폴더명 : "eroumcare_new" (값수정)
					도메인 : eroumcare.com		테마폴더명 : "eroumcare_new" (값수정)
					도메인 : localhost			테마폴더명 : "eroumcare_new" (값수정)

	-- 파일업로드 필요!!
		-- 경로 : /www/data/file/
		-- 6개 파일 업로드 (기존 버전관리 파일에서 파일 제외 처리)
			 - THKC(eroumcare)_가이드_복지용구.pdf
			 - THKC(eroumcare)_가이드_의료기상.pdf
			 - THKC(eroumcare)_가이드_재가센터.pdf
			 - THKC(eroumcare)_사업자등록증.pdf
			 - THKC(eroumcare)_카달로그.pdf
			 - THKC(eroumcare)_통장사본.jpg
*/
/* ****************************************************************************************************************** */

-- 배너 사용여부 관련 컬러 추가
ALTER TABLE `g5_shop_banner`
	ADD COLUMN `bn_status` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '사용여부' AFTER `bn_bgcolor`;


-- 회원 타입 정의 관련 컬럼 추가
ALTER TABLE `g5_member`
	ADD COLUMN `mb_default_type` VARCHAR(100) NULL DEFAULT NULL COMMENT '사업소 유형( (복지용구|의로기기상|복지센터)' AFTER `mb_type`;


-- 회원 타입 정의 관련 컬럼 추가
ALTER TABLE `g5_member`
	ADD COLUMN `mb_addr_more` TEXT NULL DEFAULT NULL COMMENT '추가 배송지주소(json형식)' AFTER `mb_addr_jibeon`;


-- 회원 타입 정의 관련 컬럼 추가
ALTER TABLE `g5_member`
	ADD COLUMN `mb_addr_title` VARCHAR(50) NULL DEFAULT NULL COMMENT '배송지명(기존 addr_name는 배송지 수령인으로 사용중.)' AFTER `mb_zip2`;


-- 추천상품관련 테이블 추가
CREATE TABLE `g5_shop_recommended` (
	`sq` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT '일련번호',
	`product1` VARCHAR(50) NOT NULL COMMENT '상품1번' COLLATE 'utf8_general_ci',
	`product1_hit` INT(11) NOT NULL DEFAULT '0' COMMENT '상품1번의 클릭 수',
	`product2` VARCHAR(50) NOT NULL COMMENT '상품2번' COLLATE 'utf8_general_ci',
	`product2_hit` INT(11) NOT NULL DEFAULT '0' COMMENT '상품2번의 클릭 수',
	`product3` VARCHAR(50) NOT NULL COMMENT '상품3번' COLLATE 'utf8_general_ci',
	`product3_hit` INT(11) NOT NULL DEFAULT '0' COMMENT '상품3번의 클릭 수',
	`recommended_url` VARCHAR(255) NULL DEFAULT NULL COMMENT '추천게시물 URL' COLLATE 'utf8_general_ci',
	`mb_id` VARCHAR(50) NOT NULL COMMENT '등록 관리자' COLLATE 'utf8_general_ci',
	`reg_dt` DATETIME NOT NULL DEFAULT current_timestamp() COMMENT '등록 일자',
	PRIMARY KEY (`sq`) USING BTREE
)
COMMENT='추천상품'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

-- 사업소 운영 자료실 :: 게시판 기본 셋팅 값 업데이트 (게시판 생성후!!!!)
UPDATE `g5_board` SET `bo_table`='care_files', `gr_id`='shop', `bo_subject`='사업소 운영 자료실', `bo_mobile_subject`='사업소 운영 자료실', `bo_device`='both', `bo_admin`='', `bo_list_level`=2, `bo_read_level`=3, `bo_write_level`=9, `bo_reply_level`=10, `bo_comment_level`=10, `bo_upload_level`=9, `bo_download_level`=3, `bo_html_level`=9, `bo_link_level`=9, `bo_count_delete`=1, `bo_count_modify`=1, `bo_read_point`=0, `bo_write_point`=0, `bo_comment_point`=0, `bo_download_point`=0, `bo_use_category`=1, `bo_category_list`='양식 | 매뉴얼 | 카달로그 컨텐츠 다운로드 | 복지용구 공단평가', `bo_use_sideview`=0, `bo_use_file_content`=0, `bo_use_secret`=0, `bo_use_dhtml_editor`=1, `bo_use_rss_view`=0, `bo_use_good`=0, `bo_use_nogood`=0, `bo_use_name`=0, `bo_use_signature`=0, `bo_use_ip_view`=0, `bo_use_list_view`=0, `bo_use_list_file`=0, `bo_use_list_content`=0, `bo_table_width`=100, `bo_subject_len`=60, `bo_mobile_subject_len`=30, `bo_page_rows`=15, `bo_mobile_page_rows`=15, `bo_new`=24, `bo_hot`=100, `bo_image_width`=600, `bo_skin`='care_files', `bo_mobile_skin`='care_files', `bo_include_head`='_head.php', `bo_include_tail`='_tail.php', `bo_content_head`='', `bo_mobile_content_head`='', `bo_content_tail`='', `bo_mobile_content_tail`='', `bo_insert_content`='', `bo_gallery_cols`=4, `bo_gallery_width`=202, `bo_gallery_height`=150, `bo_mobile_gallery_width`=125, `bo_mobile_gallery_height`=100, `bo_upload_size`=1048576, `bo_reply_order`=1, `bo_use_search`=2, `bo_order`=0, `bo_count_write`=1, `bo_count_comment`=0, `bo_write_min`=0, `bo_write_max`=0, `bo_comment_min`=0, `bo_comment_max`=0, `bo_notice`='', `bo_upload_count`=2, `bo_use_email`=0, `bo_use_cert`='', `bo_use_sns`=0, `bo_use_captcha`=0, `bo_sort_field`='', `bo_1_subj`='', `bo_2_subj`='', `bo_3_subj`='', `bo_4_subj`='', `bo_5_subj`='', `bo_6_subj`='', `bo_7_subj`='', `bo_8_subj`='', `bo_9_subj`='', `bo_10_subj`='', `bo_1`='', `bo_2`='', `bo_3`='', `bo_4`='', `bo_5`='', `bo_6`='', `bo_7`='', `bo_8`='', `bo_9`='', `bo_10`='', `as_title`='', `as_desc`='', `as_icon`='', `as_mobile_icon`='', `as_main`='', `as_link`='', `as_target`='', `as_line`='', `as_sp`=0, `as_show`=0, `as_order`=0, `as_menu`=0, `as_menu_show`=0, `as_grade`=3, `as_equal`=0, `as_wide`=0, `as_partner`=0, `as_autoplay`=0, `as_torrent`=0, `as_shingo`=0, `as_level`=0, `as_lucky`=0, `as_good`=0, `as_save`=0, `as_code`=0, `as_exif`=0, `as_loc`=0, `as_new`=0, `as_notice`=0, `as_search`=3, `as_lightbox`=0, `as_rev_cmt`=0, `as_best_cmt`=0, `as_rank_cmt`=0, `as_purifier`=0, `as_resize`=0, `as_resize_kb`=0, `as_min`=0, `as_max`=0, `as_comment_rows`=0, `as_comment_mobile_rows`=0, `as_editor`='smarteditor2', `as_mobile_editor`='', `as_set`='', `as_mobile_set`='' WHERE `bo_table`='care_files';

-- 복지용구 뉴스 :: 게시판 기본 셋팅 값 업데이트 (게시판 생성후!!!!)
UPDATE `g5_board` SET `bo_table`='care_news', `gr_id`='shop', `bo_subject`='복지용구 뉴스', `bo_mobile_subject`='복지용구 뉴스', `bo_device`='both', `bo_admin`='', `bo_list_level`=2, `bo_read_level`=3, `bo_write_level`=9, `bo_reply_level`=10, `bo_comment_level`=10, `bo_upload_level`=9, `bo_download_level`=3, `bo_html_level`=9, `bo_link_level`=9, `bo_count_delete`=1, `bo_count_modify`=1, `bo_read_point`=0, `bo_write_point`=0, `bo_comment_point`=0, `bo_download_point`=0, `bo_use_category`=0, `bo_category_list`='', `bo_use_sideview`=0, `bo_use_file_content`=0, `bo_use_secret`=0, `bo_use_dhtml_editor`=1, `bo_use_rss_view`=0, `bo_use_good`=0, `bo_use_nogood`=0, `bo_use_name`=0, `bo_use_signature`=0, `bo_use_ip_view`=0, `bo_use_list_view`=0, `bo_use_list_file`=0, `bo_use_list_content`=0, `bo_table_width`=100, `bo_subject_len`=60, `bo_mobile_subject_len`=30, `bo_page_rows`=15, `bo_mobile_page_rows`=15, `bo_new`=24, `bo_hot`=100, `bo_image_width`=600, `bo_skin`='care_news', `bo_mobile_skin`='care_news', `bo_include_head`='_head.php', `bo_include_tail`='_tail.php', `bo_content_head`='', `bo_mobile_content_head`='', `bo_content_tail`='', `bo_mobile_content_tail`='', `bo_insert_content`='', `bo_gallery_cols`=4, `bo_gallery_width`=202, `bo_gallery_height`=150, `bo_mobile_gallery_width`=125, `bo_mobile_gallery_height`=100, `bo_upload_size`=1048576, `bo_reply_order`=1, `bo_use_search`=2, `bo_order`=0, `bo_count_write`=1, `bo_count_comment`=0, `bo_write_min`=0, `bo_write_max`=0, `bo_comment_min`=0, `bo_comment_max`=0, `bo_notice`='', `bo_upload_count`=2, `bo_use_email`=0, `bo_use_cert`='', `bo_use_sns`=0, `bo_use_captcha`=0, `bo_sort_field`='', `bo_1_subj`='', `bo_2_subj`='', `bo_3_subj`='', `bo_4_subj`='', `bo_5_subj`='', `bo_6_subj`='', `bo_7_subj`='', `bo_8_subj`='', `bo_9_subj`='', `bo_10_subj`='', `bo_1`='', `bo_2`='', `bo_3`='', `bo_4`='', `bo_5`='', `bo_6`='', `bo_7`='', `bo_8`='', `bo_9`='', `bo_10`='', `as_title`='', `as_desc`='', `as_icon`='', `as_mobile_icon`='', `as_main`='', `as_link`='', `as_target`='', `as_line`='', `as_sp`=0, `as_show`=0, `as_order`=0, `as_menu`=0, `as_menu_show`=0, `as_grade`=3, `as_equal`=0, `as_wide`=0, `as_partner`=0, `as_autoplay`=0, `as_torrent`=0, `as_shingo`=0, `as_level`=0, `as_lucky`=0, `as_good`=0, `as_save`=0, `as_code`=0, `as_exif`=0, `as_loc`=0, `as_new`=0, `as_notice`=0, `as_search`=3, `as_lightbox`=0, `as_rev_cmt`=0, `as_best_cmt`=0, `as_rank_cmt`=0, `as_purifier`=0, `as_resize`=0, `as_resize_kb`=0, `as_min`=0, `as_max`=0, `as_comment_rows`=0, `as_comment_mobile_rows`=0, `as_editor`='smarteditor2', `as_mobile_editor`='', `as_set`='', `as_mobile_set`='' WHERE `bo_table`='care_news';

-- 바비콘 경로 변경 업데이트
UPDATE `malleroumcare`.`g5_config` SET `cf_2`='/img/new_common/favicon.svg' WHERE  `cf_theme`='basic' AND `cf_admin`='admin'