/* ****************************************************************************************************************** */
/*

//
// 이로움ON 1:1 상담관련 변경사항 SQL
//

//
// 작성자 : 박서원
// 작성일 : 23.10.30
// 목적 : 이로움ON 데이터베이스의 데이터 추출용으로 이로움Care에서는 직접적으로 SQL 쿼리를 생성해서 작업하지 않기 위해 만든 프로시저.
//

*/
/* ****************************************************************************************************************** */
/* ****************************************************************************************************************** */

-- 아래 2개 CREATE 쿼리는 이로움 ON DB에서 실행 해야함.
-- 아래 2개 CREATE 쿼리는 이로움 ON DB에서 실행 해야함.
-- 아래 2개 CREATE 쿼리는 이로움 ON DB에서 실행 해야함.

-- 이로움ON DB
-- 이로움ON DB 1:1 상담데이터 사업자 번호 기준 조회 및 검색 처리 부분.
CREATE DEFINER=`eroumcare`@`%` PROCEDURE `PROC_EROUMCARE_CONSLT`(
	IN `p_ModeType` VARCHAR(5),
	IN `p_BRNO` VARCHAR(15),
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATETIME,
	IN `p_Offset` INT,
	IN `p_Limit` INT,
	IN `p_Search` VARCHAR(250)
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	/*
		작성자 : 박서원
		생성일자 : 23.09.14
		
		사용목적 : 이로움Care에서 1:1상담 처리를 위한 프로시저
			- 이로움Care에서는 직접적으로 이로움ON DataBase에 SQL쿼리문을 실행 하지 않는다.
			
		변수설명 : 
		   - p_ModeType	= 프로시저 내부 동작 타입
			- p_BRNO 		= 사업자 번호
			- p_StartDate	=	검색 시작 일자
			- p_EndDate		=	검색 종료 일자 + 시:분:초
			- p_Search		= 검색 조건 ( SQL 구문 처리 )
			- p_Offset		=	페이지 포인터
			- p_Limit		=	리스트 수량
	*/

	SET @p_BRNO = p_BRNO;
	SET @p_StartDate = p_StartDate;
	SET @p_EndDate = p_EndDate;    
	SET @p_Search = p_Search;
	SET @p_Offset = p_Offset;
	SET @p_Limit = p_Limit;
	
	
	IF p_ModeType = 'cnt' THEN
	
		SET @sql = CONCAT(
		    'SELECT 
		        COUNT(*) AS cnt
		    FROM BPLC
		        LEFT JOIN MBR_CONSLT_RESULT MCR ON MCR.BPLC_UNIQUE_ID = BPLC.UNIQUE_ID
		        LEFT JOIN MBR_CONSLT AS MC ON MC.CONSLT_NO = MCR.CONSLT_NO
		    WHERE (BPLC.BRNO = ?)
		        AND (MCR.REG_DT BETWEEN ? AND ?)'
		);
		
		IF (@p_Search IS NOT NULL AND @p_Search <> '' ) THEN
		    SET @sql = CONCAT(@sql, @p_Search);
		END IF;
		
		SET @sql = CONCAT(@sql, '; ');
		
		PREPARE stmt FROM @sql;
		EXECUTE stmt USING @p_BRNO, @p_StartDate, @p_EndDate;
		DEALLOCATE PREPARE stmt;
		
	ELSEIF p_ModeType = 'list' THEN
	
		SET @sql = CONCAT(
			'SELECT


				MC.CUR_CONSLT_RESULT_NO,
				MC.CONSLT_STTUS AS MC_ST,			
				MC.MBR_NM,
				MC.GENDER,
				(
					CASE 
						WHEN MC.GENDER=''M'' THEN ''남성''
						WHEN MC.GENDER=''W'' THEN ''여성''
						ELSE ''미정'' END
				) AS ''Hangeul_GENDER'',
				
				MC.MBR_TELNO,
				MC.BRDT,
				MC.ZIP,
				MC.ADDR,
				MC.DADDR,
				MC.RGTR,
				MC.REG_DT AS ''MC_REG_DT'',
				
				MCR.BPLC_CONSLT_NO,
				MCR.CONSLT_STTUS AS MCR_ST,
				MCR.REG_DT AS ''MCR_REG_DT'',
				MCR.CONSLT_DT,
				(
					CASE
						WHEN MCR.CONSLT_STTUS=''CS01'' THEN ''상담 신청 접수''
						WHEN MCR.CONSLT_STTUS=''CS02'' THEN ''상담 신청 접수''
						WHEN MCR.CONSLT_STTUS=''CS03'' THEN ''상담 취소''
						WHEN MCR.CONSLT_STTUS=''CS04'' THEN ''상담 취소''
						WHEN MCR.CONSLT_STTUS=''CS05'' THEN ''상담 진행 중''
						WHEN MCR.CONSLT_STTUS=''CS06'' THEN ''상담 완료''
						WHEN MCR.CONSLT_STTUS=''CS07'' THEN ''상담 신청 접수''
						WHEN MCR.CONSLT_STTUS=''CS08'' THEN ''상담 신청 접수''
						WHEN MCR.CONSLT_STTUS=''CS09'' THEN ''상담 취소''
					ELSE ''미정'' END
				) AS ''Hangeul_CONSLT_STTUS''
				
			FROM BPLC AS B
				LEFT JOIN MBR_CONSLT_RESULT AS MCR ON MCR.BPLC_UNIQUE_ID = B.UNIQUE_ID
				LEFT JOIN MBR_CONSLT AS MC ON MC.CONSLT_NO = MCR.CONSLT_NO
				
			WHERE (B.BRNO = ?)
				AND (MCR.REG_DT BETWEEN ? AND ?)
				
		');
   			
		IF (@p_Search IS NOT NULL AND @p_Search <> '' ) THEN		
			SET @sql = CONCAT(@sql, @p_Search);
		END IF;		
		
		SET @sql = CONCAT(@sql, ' ORDER BY MCR.REG_DT DESC LIMIT ?, ?;');
		
		PREPARE stmt FROM @sql;
		EXECUTE stmt USING @p_BRNO, @p_StartDate, @p_EndDate, @p_Offset, @p_Limit;
		DEALLOCATE PREPARE stmt;

	
	ELSEIF p_ModeType = 'view' THEN
	
		SELECT
			/*
			-- View 페이지를 위한 키값
			-- 상담진행상태
			-- 성명
			-- 성별
			-- 연락처
			-- 생년월일
			-- 거주지주소
			-- 상담신청일		
			*/
		
			MC.CONSLT_STTUS AS MC_ST,
			MC.CUR_CONSLT_RESULT_NO,
			MC.MBR_NM,
			MC.REG_ID,
			MC.GENDER,
			(
				CASE 
					WHEN MC.GENDER='M' THEN '남성'
					WHEN MC.GENDER='W' THEN '여성'
					ELSE '미정' END
			) AS 'Hangeul_GENDER',
			MC.MBR_TELNO,      	
			MC.BRDT,
			MC.ZIP, MC.ADDR, MC.DADDR,
			MC.REG_DT AS 'MC_REG_DT',
			MC.RGTR,
			MC.RECIPIENTS_NO,
			MC.RCPER_RCOGN_NO,
			MC.RELATION_CD,
			(
				CASE
					WHEN MC.RELATION_CD='001' THEN '배우자'
					WHEN MC.RELATION_CD='002' THEN '자녀'
					WHEN MC.RELATION_CD='003' THEN '부모'
					WHEN MC.RELATION_CD='004' THEN '형제'
					WHEN MC.RELATION_CD='005' THEN '자손'
					WHEN MC.RELATION_CD='006' THEN '자부'
					WHEN MC.RELATION_CD='007' THEN '본인'
					WHEN MC.RELATION_CD='100' THEN '기타(친척등)'
					ELSE '미정' END
			) AS 'Hangeul_RELATION_CD',
			MC.PREV_PATH,
			(
				CASE 
					WHEN MC.PREV_PATH='simpleSearch' THEN '요양정보상담'
					WHEN MC.PREV_PATH='test' THEN '인정등급상담'
					ELSE '미정' END
			) AS 'Hangeul_PREV_PATH',
			MC.CANCL_RESN,
						
			MCR.REG_DT AS 'MCR_REG_DT',
			MCR.CONSLT_DTLS,
			MCR.CONSLT_DT,			
			MCR.BPLC_CONSLT_NO,
			MCR.CONSLT_NO,
			MCR.CONSLT_STTUS AS MCR_ST,
			(
				CASE
					WHEN MCR.CONSLT_STTUS='CS01' THEN '상담 신청 접수'
					WHEN MCR.CONSLT_STTUS='CS02' THEN '상담 신청 접수'
					WHEN MCR.CONSLT_STTUS='CS03' THEN '상담 취소'
					WHEN MCR.CONSLT_STTUS='CS04' THEN '상담 취소'
					WHEN MCR.CONSLT_STTUS='CS05' THEN '상담 진행 중'
					WHEN MCR.CONSLT_STTUS='CS06' THEN '상담 완료'
					WHEN MCR.CONSLT_STTUS='CS07' THEN '상담 신청 접수'
					WHEN MCR.CONSLT_STTUS='CS08' THEN '상담 신청 접수'
					WHEN MCR.CONSLT_STTUS='CS09' THEN '상담 취소'
					ELSE '미정' END
			) AS 'Hangeul_CONSLT_STTUS',
			MCR.BPLC_ID
						
		FROM BPLC AS B
			LEFT JOIN MBR_CONSLT_RESULT AS MCR ON MCR.BPLC_UNIQUE_ID = B.UNIQUE_ID
			LEFT JOIN MBR_CONSLT AS MC ON MC.CONSLT_NO = MCR.CONSLT_NO
		
		WHERE (B.BRNO = p_BRNO)
			AND (MCR.BPLC_CONSLT_NO = p_Search);

		
	ELSE
	
		SELECT 'Invalid modetype' AS result;
		
	END IF;	
	
	    
END






-- 이로움ON DB 1:1상담 데이터 상태값 변경 및 데이터 저장 프로시저
CREATE DEFINER=`eroumcare`@`%` PROCEDURE `PROC_EROUMCARE_CONSLT_UPDATE`(
	IN `p_ModeType` VARCHAR(5),
	IN `p_MC_cNO` INT,
	IN `p_MCR_cNO` INT,
	IN `p_Val_STTUS_CD` CHAR(4),
	IN `p_Val_TEXT` TEXT,
	IN `p_Val_ID` VARCHAR(100)
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	
	/*
		작성자 : 박서원
		생성일자 : 23.09.14
		
		** 설명 : 
		
			1. MBR: 사용자 입력, MNGR: 관리자 입력. BPLC: 사업소 입력
			2. MBR_CONSLT > CONSLT_NO key값
			3. MBR_CONSLT_RESULT > BPLC_CONSLT_NO key값
			4. 변경된 상태 코드 값
			5. 사업소입력외 NULL
			6. MBR(사용자아이디) 또는 MNGR(관리자아이디) 또는 BPLC(사업소아이디)의 아이디값
			
	*/
	
	
   DECLARE EXIT HANDLER FOR SQLEXCEPTION
   BEGIN
      ROLLBACK;
      -- 롤백 에러 처리 부분.
      
   END;
   
   START TRANSACTION; -- 트랜잭션 시작
   
		/*
			작성자 : 박서원
			생성일자 : 23.09.18
			
			사용목적 :
				- 
				
			변수설명 :
				- p_ModeType        = MBR: 사용자 입력, MNGR: 관리자 입력. BPLC: 사업소 입력
				- p_MC_cNO          = MBR_CONSLT > CONSLT_NO key값
				- p_MCR_cNO         = MBR_CONSLT_RESULT > BPLC_CONSLT_NO key값
				- p_Val_STTUS_CD    = MBR_CONSLT_RESULT > CONSLT_STTUS의 값 | 변경된 상태 코드 값
				- p_Val_TEXT        = 상담 내용 ( 없을 경우 NULL )
				- p_Val_ID          = 사업소 아이디
		*/
	
		/*
		IF p_Val_STTUS_CD = 'CS01' THEN SET @Txt_ConsltSttus = '상담 신청 접수';
		ELSEIF p_Val_STTUS_CD = 'CS02' THEN SET @Txt_ConsltSttus = '상담 기관 배정 완료';
		ELSEIF p_Val_STTUS_CD = 'CS03' THEN SET @Txt_ConsltSttus = '상담 취소 (상담자)';
		ELSEIF p_Val_STTUS_CD = 'CS09' THEN SET @Txt_ConsltSttus = '상담 취소 (THKC)';
		ELSEIF p_Val_STTUS_CD = 'CS04' THEN SET @Txt_ConsltSttus = '상담 취소 (상담기관)';
		ELSEIF p_Val_STTUS_CD = 'CS05' THEN SET @Txt_ConsltSttus = '상담 진행 중';
		ELSEIF p_Val_STTUS_CD = 'CS06' THEN SET @Txt_ConsltSttus = '상담 완료';
		ELSEIF p_Val_STTUS_CD = 'CS07' THEN SET @Txt_ConsltSttus = '재상담 신청 접수';
		ELSEIF p_Val_STTUS_CD = 'CS08' THEN SET @Txt_ConsltSttus = '상담 기관 재배정 완료';
		END IF;
		*/
		
		
		IF p_Val_STTUS_CD = 'CS03' THEN SET @Txt_ConsltSttus = '상담 취소(상담자) 상태';
		ELSEIF p_Val_STTUS_CD = 'CS04' THEN SET @Txt_ConsltSttus = '상담 취소(상담기관) 상태';
		ELSEIF p_Val_STTUS_CD = 'CS05' THEN SET @Txt_ConsltSttus = '진행 상태';
		ELSEIF p_Val_STTUS_CD = 'CS06' THEN SET @Txt_ConsltSttus = '완료 상태';
		END IF;
		
		
		IF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'BPLC') AND (p_MC_cNO IS NOT NULL) AND (p_MCR_cNO IS NOT NULL) ) THEN
		
			SET @MCsql = CONCAT('UPDATE `MBR_CONSLT` SET `CONSLT_STTUS`= ?');
			SET @MCRsql = CONCAT('UPDATE `MBR_CONSLT_RESULT` SET `CONSLT_STTUS`= ?');
			
			IF (p_Val_STTUS_CD = 'CS06') THEN 
				SET @MCRsql = CONCAT(@MCRsql, ', CONSLT_DTLS = ?, CONSLT_DT = NOW()');
				
			ELSEIF (p_Val_STTUS_CD = 'CS03') THEN 
				SET @MCsql = CONCAT(@MCsql, ', CANCL_RESN = ?, CANCL_DT = NOW()');
				
			END IF;
			
			SET @MCsql = CONCAT(@MCsql, ' WHERE `CONSLT_NO`= ?;');
			SET @MCRsql = CONCAT(@MCRsql, ' WHERE `BPLC_CONSLT_NO`= ? AND `CONSLT_NO`= ?;');
			
			
			-- SELECT @MCsql;
			-- CS04: PDATE `MBR_CONSLT` SET `CONSLT_STTUS`= ?, CANCL_RESN = ?, CANCL_DT = NOW() WHERE `CONSLT_NO`= ?;
			-- CS06: UPDATE `MBR_CONSLT` SET `CONSLT_STTUS`= ? WHERE `CONSLT_NO`= ?;
			
			-- SELECT @MCRsql;
			-- CS04: UPDATE `MBR_CONSLT_RESULT` SET `CONSLT_STTUS`= ? WHERE `BPLC_CONSLT_NO`= ? AND `CONSLT_NO`= ?;
			-- CS06: UPDATE `MBR_CONSLT_RESULT` SET `CONSLT_STTUS`= ?, CONSLT_DTLS = ?, CONSLT_DT = NOW() WHERE `BPLC_CONSLT_NO`= ? AND `CONSLT_NO`= ?;
			
			
			PREPARE MCtmp FROM @MCsql;
			PREPARE MCRtmp FROM @MCRsql;
			
			IF (p_Val_STTUS_CD = 'CS06') THEN -- 사업소 상담완료
				EXECUTE MCtmp USING  p_Val_STTUS_CD, p_MC_cNO;
				EXECUTE MCRtmp USING p_Val_STTUS_CD, p_Val_TEXT, p_MCR_cNO, p_MC_cNO;
				
			ELSEIF (p_Val_STTUS_CD = 'CS03') THEN -- 사업소 상담취소
				EXECUTE MCtmp USING  p_Val_STTUS_CD, p_Val_TEXT, p_MC_cNO;
				EXECUTE MCRtmp USING  p_Val_STTUS_CD, p_MCR_cNO, p_MC_cNO;
				
			ELSE -- 기타 사업소 상태 업데이트
				EXECUTE MCtmp USING p_Val_STTUS_CD, p_MC_cNO;
				EXECUTE MCRtmp USING p_Val_STTUS_CD, p_MCR_cNO, p_MC_cNO;
				
			END IF;
			
			DEALLOCATE PREPARE MCtmp;
			DEALLOCATE PREPARE MCRtmp;
			
		END IF;
		
		
		
		-- === === === === === === === === === === === === === === === === === === === === === === === ===
		-- STTUS 값 변경에 따른 로그기록 사입
		-- === === === === === === === === === === === === === === === === === === === === === === === ===
		/*
		IF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'MBR') ) THEN	
			SELECT MBR.UNIQUE_ID, MBR.MBR_ID, MBR.MBR_NM 
			INTO @select_MBR_uID, @select_MBR_ID, @select_MBR_NM 
			FROM MBR 
			WHERE MBR.MBR_ID = p_Val_ID;
			
		ELSEIF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'MNGR') AND (p_MC_cNO IS NOT NULL) AND (p_MCR_cNO IS NOT NULL) ) THEN
			SELECT MNGR.UNIQUE_ID, MNGR.MNGR_ID, MNGR.MNGR_NM 
			INTO @select_MNGR_uID, @select_MNGR_ID, @select_MNGR_NM 
			FROM MNGR 
			WHERE MNGR.MNGR_ID = p_Val_ID;			
			
			SELECT MCR.CONSLT_STTUS, MCR.BPLC_UNIQUE_ID, MCR.BPLC_NM 
			INTO @select_MCRcSTTUS, @select_BPLC_uID, @select_BPLC_NM
			FROM MBR_CONSLT_RESULT AS MCR
				LEFT JOIN MBR_CONSLT AS MC ON MC.CONSLT_NO = MCR.CONSLT_NO
			WHERE (BPLC_CONSLT_NO = p_MCR_cNO) AND (MC.CONSLT_NO = p_MC_cNO);
			
		ELSE
		*/
		
		IF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'BPLC') AND (p_MC_cNO IS NOT NULL) AND (p_MCR_cNO IS NOT NULL) ) THEN
			SELECT MCR.CONSLT_STTUS, B.UNIQUE_ID, B.BPLC_ID, B.BPLC_NM
			INTO @select_MCRcSTTUS, @select_BPLC_uID, @select_BPLC_ID, @select_BPLC_NM 
			FROM MBR_CONSLT_RESULT AS MCR
				LEFT JOIN MBR_CONSLT AS MC ON MC.CONSLT_NO = MCR.CONSLT_NO
				LEFT JOIN BPLC AS B ON B.UNIQUE_ID = MCR.BPLC_UNIQUE_ID
			WHERE (BPLC_CONSLT_NO = p_MCR_cNO) AND (MC.CONSLT_NO = p_MC_cNO);
			
		END IF;
		
		
		SET @TextRESN  = CONCAT(@Txt_ConsltSttus,'로 변경되었습니다.');		
		SET @InsertSQL = CONCAT(' INSERT `MBR_CONSLT_CHG_HIST` SET `CONSLT_NO` = ?, `CONSLT_STTUS_CHG` = ?, `RESN` = ?, `REG_DT` = NOW()');
				
		/*
		IF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'MBR') ) THEN
			SET @InsertSQL = CONCAT(@InsertSQL, ' 
					,`MBR_UNIQUE_ID` = ?
					,`MBR_ID` = ?
					,`MBR_NM` = ?
			'); PREPARE InsertSql FROM @InsertSQL;
			
			EXECUTE InsertSql USING 
											p_MC_cNO
											, p_Val_STTUS_CD
											, @TextRESN
											
											, @select_MBR_uID
											, @select_MBR_ID
											, @select_MBR_NM
			;
			
		ELSEIF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'MNGR') ) THEN
			SET @InsertSQL = CONCAT(@InsertSQL, ' 
					,`BPLC_CONSLT_NO` = ?
					,`BPLC_CONSLT_STTUS_CHG` = ?
					,`CONSLT_BPLC_UNIQUE_ID` = ?
					,`CONSLT_BPLC_NM` = ?
					,`MNGR_UNIQUE_ID` = ?
					,`MNGR_ID` = ?
					,`MNGR_NM` = ?
			'); PREPARE InsertSql FROM @InsertSQL;
			
			EXECUTE InsertSql USING 
											p_MC_cNO
											, p_Val_STTUS_CD
											, @TextRESN
											
											, p_MCR_cNO
											, @select_MCRcSTTUS
											, @select_BPLC_uID
											, @select_BPLC_NM											
											, @select_MNGR_uID
											, @select_MNGR_ID
											, @select_MNGR_NM
			;
			
		ELSE
		*/
		
		IF ( (p_ModeType IS NOT NULL) AND (p_ModeType = 'BPLC') ) THEN
			SET @InsertSQL = CONCAT(@InsertSQL, ' 
					,`BPLC_CONSLT_NO` = ?
					,`BPLC_CONSLT_STTUS_CHG` = ?
					,`CONSLT_BPLC_UNIQUE_ID` = ?
					,`CONSLT_BPLC_NM` = ?
					,`BPLC_UNIQUE_ID` = ?
					,`BPLC_ID` = ?
					,`BPLC_NM` = ?
			'); PREPARE InsertSql FROM @InsertSQL;
			
			EXECUTE InsertSql USING 
											p_MC_cNO
											, p_Val_STTUS_CD
											, @TextRESN
											
											, p_MCR_cNO
											, @select_MCRcSTTUS											
											, @select_BPLC_uID
											, @select_BPLC_NM
											, @select_BPLC_uID
											, @select_BPLC_ID
											, @select_BPLC_NM
			;
				
		END IF;
		
		DEALLOCATE PREPARE InsertSql;
		
		
	COMMIT; -- 트랜잭션 완료
	

END

