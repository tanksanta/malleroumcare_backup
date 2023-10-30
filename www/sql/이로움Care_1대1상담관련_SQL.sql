/* ****************************************************************************************************************** */
/*

//
// 이로움ON 1:1 상담관련 변경사항 SQL
//

//
// 작성자 : 박서원
// 작성일 : 23.10.23
// 목적 : 해당 파일은 이로움ON 1:1 상당부분 연동과 내부 로직 처리를 위한부분으로 
//         본 파일에 포함된 Git가 업데이트 되기 전 상용(운영) 서버에서 반드시 먼저 실행되어야 한다.
//		   기존 1:1매칭 서비스 시넝 사업소에 대한 데이터 컨버전 내용 추가.
//

*/
/* ****************************************************************************************************************** */
/* ****************************************************************************************************************** */

-- 이로움Care DB
-- 이로움Care DB 회원 타입 정의 관련 컬럼 추가
ALTER TABLE `g5_member`
	ADD COLUMN `mb_giup_matching` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '이로움ON 매칭서비스 사용 여부 확인.' AFTER `mb_giup_manager_tel`;




-- 이로움Care 이로움ON 1:1 매칭 서비스 신청(동의) 사업소 메뉴 활성화 업데이트
-- 상용 신청 데이터( 엑셀 파일 기준 23.10.30 11시 50분)
-- 해당 SQL 실행시 "사업소 운영관리" 메뉴에 "수급자 상당괌리" 메뉴가 노출됨.
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rldnddldu';      -- 245-80-02511    고은손 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'tpwls7841';      -- 603-10-87372    영신의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sejin57';        -- 602-14-78693    세진의료기상사
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'naeun9526';      -- 827-94-01679    나은재가복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kyt5213';        -- 288-06-00484    가람의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'skt2699';        -- 197-27-00427    형제의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'modoocare';      -- 883-86-02085    주식회사모두케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ehdtla2784';     -- 738-52-00462    동심의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ever999';        -- 122-23-56424    노노메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'handica513';     -- 122-81-95621    (주)핸디케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hiba123';        -- 416-13-15784    보석의료기(성가롤로병원앞)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'srimk';          -- 103-80-02333    가좌의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bbareuncare';    -- 601-03-24126    빠른케어북부 복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sky5852080';     -- 110-02-79415    백세플러스의료기 
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'designyh';       -- 409-80-32583    가온복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'eoneys';         -- 599-20-00018    이원의료기 양산점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hansin8182';     -- 608-03-59816    한신의료보조기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'Greyscale';      -- 445-81-02442    (주)그레이스케일
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mosida';         -- 704-87-00670    모시다복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'atomy15895089';  -- 608-06-26223    대도의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yjk08088';       -- 153-80-02740    더편한복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ucmedicare';     -- 332-17-00244    동서의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'joeunm1117';     -- 402-80-52948    조은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'suck9315';       -- 608-06-21820    정우의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pjj3452';        -- 615-02-36361    단골약국.단골의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'djhealth4658';   -- 311-03-98725    당진건강의료기(건강의료기스포츠사)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mmimya';         -- 611-04-95358    금강의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lee7983';        -- 613-20-37131    고려의료기 산청
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'storm0700';      -- 226-12-08612    나눔의료기(강릉)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jinji0kr';       -- 270-37-00814    지엔(GN)경남
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pnz1010';        -- 650-80-00369    온누리케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kcmedis';        -- 774-80-02187    케이씨복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hongplaza';      -- 511-07-95971    이원의료기문경점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ssiboro3';       -- 987-65-4444443333333    조용호
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dodrim';         -- 137-19-66359    두드림노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dlskud315';      -- 657-22-01108    예사랑복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'chajy7893';      -- 623-80-02894    사나래복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'chg6127';        -- 235-23-00643    참사랑 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'tabyjang';       -- 192-05-02393    장한의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nissi1201';      -- 550-06-01281    건강한의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ltgv0001';       -- 842-66-00021    씨엘메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ytmdgml';        -- 408-10-83714    백두의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'godluck9';       -- 303-07-84607    한방의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ysy200';         -- 539-04-02315    더조은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '5261116';        -- 535-80-02700    미메디
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jang3571';       -- 140-57-00506    예천의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hd0411';         -- 420-03-00170    남서울복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sinis';          -- 890-98-01340    광양의료기복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gagahoho365';    -- 617-81-88666    가가호호시니어비전
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'db1004';         -- 165-05-01863    동백의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ssrain790';      -- 831-15-02143    우리의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rw002';          -- 760-80-02456    루웰복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'corezen';        -- 608-23-88755    약손의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kss9764';        -- 409-01-83243    삼보메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bumosumgim';     -- 136-80-01431    부모섬김복지센터복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '4760700055';     -- 476-07-00055    효진복지의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'myeongsin';      -- 160-43-00347    명신
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gyeyangmedi';    -- 537-13-00370    계양의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '7130702141';     -- 713-07-02141    태성케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'chanjupapa';     -- 128-37-40418    장애인복지협회의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jk2175';         -- 110-19-26503    실버케어코리아
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'choitulip';      -- 364-64-00551    보훈의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nanumcare';      -- 565-80-01570    나눔복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'goldensenior';   -- 636-76-00288    골든시니어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hong041679';     -- 348-06-01392    JJ메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'id3695368';      -- 218-80-03177    동화복지용구 의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sjl4455';        -- 513-09-32734    명성의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'spintec';        -- 324-86-01874    주식회사 스핀택
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'a237374';        -- 605-23-82499    몸에좋은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rcw0106';        -- 163-40-00670    대구)하나메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bluerain9697';   -- 622-32-01004    한빛의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sd114';          -- 512-05-56899    한일의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sunglimmedical'; -- 621-03-58085    성림메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jangsoo88';      -- 342-70-00440    장수메디케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'medivice';       -- 119-31-24241    메디바이스 의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dl4945';         -- 264-21-01802    삼성의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gaincare';       -- 766-06-01343    가인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hyeminseo2019';  -- 745-22-01803    혜민서의료기복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hyolim0584';     -- 383-60-00724    복지용구 효림
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'thdgustjq';      -- 464-03-02595    이원의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'silvers10';      -- 257-85-02334    케어네이션 동안센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'splaza';         -- 615-15-25355    시니어프라자
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'roimessage03';   -- 798-88-02821    주식회사 로이
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jea1139';        -- 318-15-00832    서부산의료기보청기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '8048502222';     -- 804-85-02222    라파엘통합재가센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '6020774229';     -- 602-07-74229    동인메디텍
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'singy5108';      -- 213-39-92542    양산복지의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '1688001461';     -- 168-80-01461    행복한의료기복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'now6255';        -- 443-80-01912    영진의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pp2812';         -- 606-27-73235    해오름메디칼(22.5~)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '01091899307';    -- 113-18-24434    예복유통
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lcs3837';        -- 337-15-00643    산청복지의료기센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bis3129';        -- 346-05-02011    비슬복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'runnerkyung';    -- 838-02-00506    설악의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sk2293';         -- 613-12-27434    한일복지용구판매센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'roes3549';       -- 160-80-01579    샘물 복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rnawer';         -- 290-50-00160    효자의료기복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'core30000';      -- 118-13-14668    일산 복지용구 의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kktmdns';        -- 320-15-00742    기쁨복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'shbareun';       -- 370-87-01331    빠른케어시흥
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'qaz6637';        -- 777-72-00505    정메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lbj1690';        -- 308-86-00757    주식회사착한
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'whd0300';        -- 405-80-00212    대안노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kmed1';          -- 215-17-93429    케이메드
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wnddkdv';        -- 428-80-00520    한솔의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'fatima8881';     -- 502-11-53182    파티마의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'k006140';        -- 122-32-34422    (ACE)강동재가복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gahomedical';    -- 441-80-02725    가호메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hnr1271';        -- 385-39-01098    선한inc
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hst1997';        -- 307-07-55856    365행복케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hekkk2000';      -- 310-80-17074    해드림재가센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hongyeol33';     -- 344-32-00269    일등의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'qaz0869';        -- 140-08-17047    하늘높이복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'luxurymedical';  -- 395-02-01221    명품의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'smh2008';        -- 113-20-67775    건강소망복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ansoni226';      -- 512-07-39818    봉화종합의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'noble01298';     -- 548-85-01298    노블카운티의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mhvillain00';    -- 339-80-01802    mh엄마손실버복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'agh56738';       -- 167-11-00240    수원의료기다님길복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sinbi0012';      -- 550-06-02446    신비의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bmk6448';        -- 309-07-30943    보은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wellcare1';      -- 760-81-00493    주식회사웰케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kyy536800';      -- 326-05-01963    은혜노인의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sarang88i';      -- 474-80-02012    조은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '36588gp';        -- 433-28-01576    가평의료기복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'cmlee6805';      -- 620-11-36986    건강의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'daumcare';       -- 422-42-00078    다움복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'drwell119';      -- 130-45-82755    닥터웰의료기백화점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hyo0506';        -- 308-80-44544    효드림케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '1544mj';         -- 836-59-00479    가장좋은 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'snrk9949';       -- 610-20-05678    누가의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jsh60490';       -- 342-80-00380    사랑나눔재가복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'safe0615';       -- 409-23-68230    세이프헬스케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lalamedi';       -- 311-70-01125    광명중앙의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ju794602';       -- 482-71-00143    장생의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hsl712';         -- 274-21-01847    송파한사랑재가센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'marelin1';       -- 129-80-46255    우리사랑복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'csm1005';        -- 742-73-00243    동행헬스케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '5311900087';     -- 531-19-00087    늘찬복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jesus2121';      -- 409-17-86085    임마누엘의료기산업
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dream';          -- 415-81-28780    주식회사 드림의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '0302forest';     -- 875-43-00814    커뮤니티숲-정관의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'km148012';       -- 390-80-02368    누리의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ljc4120';        -- 687-80-02622    조은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dongbucare';     -- 123-35-32092    동부케어군포종합복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yuhan1619';      -- 588-14-01563    유한헬스케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gigamekin';      -- 368-67-00461    행복케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'xjwlsdl81';      -- 678-80-02685    호남의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hansmedi';       -- 673-80-01658    한스메디
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pro4322';        -- 735-80-02616    편한의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'goguiinc';       -- 519-81-01462    (주)고귀 의료기기·보청기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'diakdrhkd';      -- 542-80-00146    가연복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lcj4476';        -- 506-06-27907    동산보청기의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gsl0142';        -- 242-80-01245    고성사랑재가복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kjsinchang';     -- 612-21-56409    거제신창의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rambada2';       -- 651-06-00116    동행복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'prigids';        -- 142-80-39612    연원메디컬
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'utherico';       -- 398-86-02535    유더리코(주)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lsg1686';        -- 143-82-74367    성신복지용구의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sopoong';        -- 574-86-02105    협동조합소풍
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wjdwhdrkr1';     -- 596-55-00438    (수)복지용구의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'simba';          -- 174-11-00025    신평화의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'cgh5393';        -- 273-80-01443    동원의료기(재가장기요양기관 복지용구제공사업소)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'myoungbocare';   -- 524-80-00075    명보재가노인요양복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'firstwell';      -- 244-81-02780    주)퍼스트웰
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'allehalleh';     -- 201-27-05959    노인요양의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wjdrudgml68';    -- 869-25-01454    그린의료기 장흥
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'careshild';      -- 167-11-01157    케어쉴드 노인복지용구전문점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lksolutioncare'; -- 825-54-00611    다산가람복지용구의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wow0514';        -- 782-74-00520    세주의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'fif9694';        -- 182-16-01466    100세의료기 나주점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kdhemail';       -- 253-69-00294    성지의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yes5254';        -- 412-04-46911    한솔의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '2re222';         -- 521-01-02007    맘편한의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mireajae';       -- 502-31-38781    길메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '4082063532';     -- 408-20-63532    이원건강의료기 전북대병원점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'eone7061';       -- 720-69-00095    이원건강의료기 전북대병원점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'twoboy78';       -- 287-80-02887    대경메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'skysas123';      -- 699-80-02284    온사랑케어복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jjh';            -- 711-80-02793    전주사랑복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'donggyeong100';  -- 692-80-02395    동경의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'baekse';         -- 222-14-82439    백세의료기기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nga1201';        -- 729-80-01494    남해독일보청기 재솔복지용구 의료기(복지용구)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'DNFL5215857';    -- 276-52-00208    우리복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'firstcare';      -- 818-20-01733    퍼스트케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gana1009';       -- 243-80-00254    가나복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'thkc_platform';  -- 463-46-3532543  플랫폼팀
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'keukdong1';      -- 360-80-02348    손잡이 복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dlwndjs0';       -- 524-74-00133    다원의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lmk731228';      -- 445-80-02275    동부의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rodem8799';      -- 192-45-00483    로뎀복지센터(복지용구)
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jerry3684';      -- 223-81-19760    서원이엔엠
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bestmedi';       -- 509-07-13132    베스트건강메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '63youngja';      -- 739-80-01680    금빛행복나라
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'boksun7503';     -- 370-79-00394    금아의료기 양산
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'forrest3';       -- 760-80-02607    봄봄메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bsm3991';        -- 221-17-11117    백석의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'koinsad';        -- 114-81-65790    (주)코인스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nj201006';       -- 314-80-41334    대전복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kimiroo';        -- 598-12-02298    세종중앙의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kfgcare';        -- 812-16-01043    믿음노인요양센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gmsilver';       -- 308-81-45991    (주)굿모닝실버
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'e1medical';      -- 545-06-01426    이원건강의료기 화성점
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kjk960';         -- 121-19-59120    (H)하이케어서비스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nooriyecare';    -- 761-19-02217    누리예케어복지용구의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ryu8524';        -- 848-80-02116    가람재가노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '6081464955';     -- 608-14-64955    해피케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'minsu189';       -- 422-40-00322    서울보청기 의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dsmedicare';     -- 810-81-00615    주식회사 동산메디케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'shs0871';        -- 786-10-02095    한마음복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pjg77777';       -- 112-32-39245    실로암복지용구사무소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'joabokji';       -- 178-15-01437    조아복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'joabokg';        -- 438-26-00544    조아복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = '5658002716';     -- 565-80-02716    해피한복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'shinilsc';       -- 601-88-01678    신일실버케어 주식회사
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jmrca99';        -- 210-13-55567    현대의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'rlawlghf13';     -- 799-80-02717    착한의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jazzsim9';       -- 680-80-00583    우진복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kjkw';           -- 367-19-01879    가정의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jhlhr17';        -- 418-07-75579    실버벨스 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'flower33';       -- 117-12-90652    은나래복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yga4416';        -- 177-80-00748    와이지올케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wellifeshop';    -- 637-88-01056    주식회사 서연
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bbc3113';        -- 293-87-01015    (주)ili사회서비스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'singy1232';      -- 240-27-01244    지원의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'khy7005';        -- 329-08-01532    백세의료기기 
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kgg1014';        -- 397-80-01943    건강의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gaga14';         -- 204-12-41737    가가호호복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hmc9708';        -- 732-31-00019    현산메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'smileman00';     -- 534-17-00078    스마일복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bumolove';       -- 558-82-68261    부모사랑노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'khlifeshop00';   -- 216-18-13217    건강생활점/케이에이치라이프숍
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ykc1072';        -- 407-01-79423    백제의료기기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sgkeh1492';      -- 776-07-02647    청명복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'newdaymedical';  -- 577-80-02058    뉴데이복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'pittysong';      -- 128-92-89351    조은간호요양센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'happyksw';       -- 621-22-91902    사람과세상
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'misoo8';         -- 311-38-01106    주문진의료기보청기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sulmunde9';      -- 307-80-28589    설문대 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dymdc';          -- 606-86-38104    주식회사 동양엠디씨
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lch4743';        -- 228-09-15050    좋은의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sindong90';      -- 244-44-00453    영주의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'okjungcare';     -- 109-17-08286    옥정의료기복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gh4469';         -- 578-01-02283    굿헬스의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kiki7323';       -- 513-88-02180    (주)호정재가복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'happy1432';      -- 559-32-00463    행복플러스복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'duri1245';       -- 579-80-00395    마음드리 재가복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'uppage';         -- 612-08-65312    경남미래엠텍
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'tys3088';        -- 128-36-04491    땡큐시니어건강의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ssn645';         -- 124-48-04191    인메디케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hs3861';         -- 225-40-00930    9988복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mpk6441';        -- 558-80-02358    실버세상복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hana7295';       -- 137-20-93978    제일하나의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nowon';          -- 315-14-63239    노원복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sur1027';        -- 145-80-02939    가인홈케어 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'medcare6673';    -- 314-80-46926    드림케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mook4055';       -- 574-27-00793    새소망의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kyure21';        -- 521-11-01477    미노메딕스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'goodluck0303';   -- 121-72-00018    남사헬스케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yeinmedi';       -- 804-05-02045    예인의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'yikwak2';        -- 105-87-47432    (주)이씨파트너스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'lyna042800';     -- 226-80-18991    안심케어 복지용구 사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hyohappy777';    -- 663-90-01892    효행복 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gahd1202';       -- 552-33-00722    (A)관악현대재가복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ysm900';         -- 617-80-21799    행복노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'k2s1227';        -- 227-02-95014    대명의료기상사
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kymedi';         -- 138-13-16563    관양의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'woori7795';      -- 852-21-01666    횡성의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jian';           -- 148-30-00105    A+지엔케어복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gmlakd07';       -- 578-42-00425    희망의료기센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'good171113';     -- 365-88-00832    (주)더좋은환경, 더좋은복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sjde11';         -- 677-80-02678    누가복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ora3682';        -- 798-12-01604    오라의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'wooricare001';   -- 173-04-02546    우리케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dor11';          -- 420-80-02771    안산의료기복지용구센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sungmo2664';     -- 838-05-01563    성모헬스케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'foglee';         -- 538-30-00967    경북복지용구사업소
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'dawonmed';       -- 209-44-90651    다원의료기산업
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'nsbs0822';       -- 658-88-00146    엔에스비에스복지용구서비스
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ojh4417';        -- 824-02-00619    드림의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bohunmedic';     -- 212-03-41847    보훈의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'silvernine2';    -- 401-80-18646    두손모아 복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'serve';          -- 137-80-42380    섬김복지용구지원센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'say84xx';        -- 219-80-03016    한국의료기기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mrn';            -- 635-80-02581    모래내복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'vygusdyd';       -- 799-45-00964    인생복지
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gmbokji';        -- 859-80-02606    광명복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sjasjackck';     -- 846-08-01755    효진재가노인복지센터
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'kdh9394';        -- 570-80-02343    참사랑복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'mhjeon';         -- 222-22-22233    전명희
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hanyangmedi';    -- 460-37-00663    보건한양의료기
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ektha4342';      -- 651-80-02467    복지나라의료기(신) / 광주
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'eodyd0248';      -- 246-20-6966666666666    관리자
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'gssscare';       -- 530-80-02200    거성실버케어
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'cho7150';        -- 101-21-95751    A+조은시니어복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'ksk06188';       -- 869-24-00706    어행세
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'inyong0606';     -- 463-80-02254    이음복지용구
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'bangju';         -- 782-80-01710    방주메디칼
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'jhlee';          -- 657-56-2152132222222    이진희
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sylee';          -- 232-21-56215    훌라테스트
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'hula1202';       -- 321-64-51984    훌라테스트
UPDATE g5_member SET mb_giup_matching = 'Y' WHERE mb_id = 'sjbaek';         -- 111-22-445667831    백승정