/* ****************************************************************************************************************** */
/*

//
// 이로움API - WMDS 데이터 추가건.
//           - 노션 타이틀 : "상품등록시 카테고리 설정 오류 발생"
//

//
// 작성자 : 박서원
// 작성일 : 23.10.30
// 목적 : 카테고리 데이터가 FT단에서 임의 추가된에 따라 상품등 록시 카테고리 정보를 WMDS에서 확인하는데,
//         해당 데이터가 존재 하지 않음으로 에러가 발생함.
//		   관려하여 기능구현은 완료 되었으며, 기존 등록된 카테고리중 누락분에 대한 SQL 쿼리문 추가로 실행햐야함.
//

//
//          본 SQL 실행은 [ use wmdsdb ]에서 실행해야함.
//
*/
/* ****************************************************************************************************************** */
/* ****************************************************************************************************************** */


INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800007',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '욕창예방방석';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800008',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '자세변환용구';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800009',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '침대매트리스/시트';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800010',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '성인용기저귀/매트';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800011',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '보호용품';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800012',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '측정용품';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800013',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '일상용품';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800014',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '이동변기';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800015',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '목욕의자';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800016',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '기타';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800017',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '석션기';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800018',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '프로모션(비급여)';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800019',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '미끄럼방지양말';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2021061800020',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '건강식품';
INSERT INTO `pro100`	SET `ITEM_ID` = 'ITM2022010800001',		`ITEM_TYPE_CD`='02',		`USE_CD` = '01', 	`USE_YEAR`=1,	`USE_NUM`=1,	`REG_DTM`=NOW(), 	`REG_USR_ID`='admin', 	`REG_USR_IP`='127.0.0.1',	`ITEM_NM` = '프로모션';
