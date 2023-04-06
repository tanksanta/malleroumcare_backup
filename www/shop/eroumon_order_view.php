<?php
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = EroumON_Order Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : /www/shop/eroumon_order_view.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 발생한 주문건의 상세 정보를 확인하기 위한 페이지 */
    /*                해당 페이지에서 주문에 대한 승인/발려와 내부 1.0에 Order를 진행 하며, 오더 주문거의 STEP 단계별 진행 프로세스가 스크롤 다운 형식으로 보여짐.*/
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');

    if(!$member['mb_id'])
        alert('먼저 로그인하세요.',G5_URL.'/bbs/login.php');

    @include_once(G5_LIB_PATH.'/apms.thema.lib.php');
    @include_once($order_skin_path.'/config.skin.php');

    $g5['title'] = '수급자 주문상세';

    include_once('./_head.php');


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    $_od = sql_fetch("  SELECT * FROM g5_shop_order_api WHERE order_send_id = '" . $_GET['order_send_id'] . "' AND mb_id = '" . $member['mb_id'] . "' ");
    $_ct = sql_query("  SELECT API.*, ITEM.it_img1
                        FROM g5_shop_cart_api API
                        LEFT JOIN g5_shop_item ITEM ON ITEM.it_id = API.it_id
                        WHERE order_send_id = '" . $_GET['order_send_id'] . "' AND mb_id = '" . $member['mb_id'] . "'
                        ORDER BY ct_id DESC 
    
    ");

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    if ( !$_od['order_send_id'] || ( $_ct->num_rows == 0 ) ) {
        alert("[이로움ON]의 수급자 주문 데이터를 확인할 수 없습니다.", G5_SHOP_URL . "/eroumon_order_list.php");
    }


    // 23.03.08 : 서원 - 한 페이지에서 setp별 프로세스바와 setp값에 따른 화면 분리를 위한 값.
    //                      해당 switch문 수정 시 페이지 내부 $_setp 사용 부분에 대한 변경 처리 필요(!!중요!!)
    $_setp = 0;
    switch( $_od['od_status'] ) {
        case '승인대기': $_setp = 1;  break;
        case '주문처리': $_setp = 2;  break;
        case '결제완료': $_setp = 3;  break;
        case '주문완료': $_setp = 4;  break;
        case '출고완료': $_setp = 5;  break;
        case '작성완료': $_setp = 6;  break;
        case '서명완료': $_setp = 7;  break;    
        case '주문취소': $_setp = 10;  break;                    
        default : $_setp = 8; break;
    }
    
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 서브 SQL 처리 부분 시작 ( 페이지 내부에서 상태값에 따른 표현을 모두 진행 함에 따라 상단 SQL 처리로 처리 불가능. )
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    $_order_ct = [];
    if( $_setp >=4 ){
        $_order_ct = sql_query("    SELECT CT.*, IT.gubun, IT.prodNm, IT.ProdPayCode
                                    FROM g5_shop_cart_api API
                                    RIGHT JOIN g5_shop_cart CT ON CT.ct_id = API.ct_sync_ctid
                                    RIGHT JOIN g5_shop_item IT ON IT.it_id = CT.it_id
                                    WHERE API.order_send_id = '" . $_GET['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "'
                                    ORDER BY CT.ct_id DESC 
        ");
    }
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 서브 SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    //인증서 업로드 추가 영역
    $mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";
    if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])){ $mobile_yn = "Mobile"; }else{ $mobile_yn = "Pc"; }

    $is_file = false;
    if($member["cert_data_ref"] != ""){
        $cert_data_ref =  explode("|",$member["cert_data_ref"]);
        $cert_info = "사용자명:".base64_decode($cert_data_ref[1])." | 만료일자:".base64_decode($cert_data_ref[2]);
        $upload_dir = G5_DATA_PATH."/file/member/tilko/";
        $file_name = base64_encode($cert_data_ref[0]);
        if(file_exists($upload_dir.$file_name.".enc") || file_exists($upload_dir.$file_name.".txt")){
            $is_file = true;
        }
    }
    //인증서 업로드 추가 영역 끝


    // 수급자 WMDS 등록여부 확인 체크
    $_pen = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
        'usrId' => $member['mb_id'],
        'entId' => $member['mb_entId'],
        'penLtmNum' => 'L'.$_od['od_penLtmNum']
    ));

    $sale_ids = $rent_ids = [];

    if( $_pen['errorYN'] == "N"  ) {
        // 수급자 <--> 사업소 WMDS 연결 등록 여부 확인.
        if( is_array($_pen['data'][0]) && ($_pen['data'][0]['penId']) ) {
            $sale_ids = [];
            // $sale_product_name0="미분류"; $sale_product_id0="ITM2021021300001";
            $sale_product_name1="경사로(실내용)"; $sale_product_id1="ITM2021010800001";
            $sale_product_name2="욕창예방매트리스"; $sale_product_id2="ITM2020092200020";
            $sale_product_name3="요실금팬티"; $sale_product_id3="ITM2020092200011";
            $sale_product_name4="자세변환용구"; $sale_product_id4="ITM2020092200010";
            $sale_product_name5="욕창예방방석"; $sale_product_id5="ITM2020092200009";
            $sale_product_name6="지팡이"; $sale_product_id6="ITM2020092200008";
            $sale_product_name7="간이변기"; $sale_product_id7="ITM2020092200007";
            $sale_product_name8="미끄럼방지용품(매트)"; $sale_product_id8="ITM2020092200006";
            $sale_product_name9="미끄럼방지용품(양말)"; $sale_product_id9="ITM2020092200005";
            $sale_product_name10="안전손잡이"; $sale_product_id10="ITM2020092200004";
            $sale_product_name11="성인용보행기"; $sale_product_id11="ITM2020092200003";
            $sale_product_name12="목욕의자"; $sale_product_id12="ITM2020092200002";
            $sale_product_name13="이동변기"; $sale_product_id13="ITM2020092200001";
            for($i=1; $i<14; $i++) { $sale_ids[${'sale_product_name'. $i}] = ${'sale_product_id'.$i}; }
            
            $rent_ids = [];
            $rental_product_name0="욕창예방매트리스"; $rental_product_id0="ITM2020092200019";
            $rental_product_name1="경사로(실외용)"; $rental_product_id1="ITM2020092200018";
            $rental_product_name2="배회감지기"; $rental_product_id2="ITM2020092200017";
            $rental_product_name3="목욕리프트"; $rental_product_id3="ITM2020092200016";
            $rental_product_name4="이동욕조"; $rental_product_id4="ITM2020092200015";
            $rental_product_name5="수동침대"; $rental_product_id5="ITM2020092200014";
            $rental_product_name6="전동침대"; $rental_product_id6="ITM2020092200013";
            $rental_product_name7="수동휠체어"; $rental_product_id7="ITM2020092200012";
            for($i=0; $i<8; $i++) { $rent_ids[${'rental_product_name'. $i}] = ${'rental_product_id'.$i}; }
        } else {
            // 주문처리 로그.
            if( $_setp == 1 ) { 
                api_log_write($_od['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 사업소 기존 등록 수급자 데이터 없음."); 
            }
        }
    } else {
        // 주문처리 로그.
        if( $_setp == 1 ) { 
            api_log_write($_od['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 사업소에 등록되어있는 수급자 조회 에러."); 
        }
    }


    // 23.03.16 - 서원 : 출고 완료시 해당 주문건에 대한 전자계약서 작성 프로세스 진행.
    //                      계약서에 필요한 정보를 WMDS와 Order 테이블에서 받아와 계약서 테이블에 저장.
    $eform = [];
    if( $_setp == 5 ) { 

        // 계약서 존재 유무 확인
        $eform = sql_fetch("SELECT dc_id, hex(dc_id) as uuid FROM `eform_document` WHERE od_id = '{$_od['od_sync_odid']}'");

        if( !$eform['dc_id'] ) { // 전자계약서가 없을 경우
            $dcId = sql_fetch("SELECT REPLACE(UUID(),'-','') as uuid")["uuid"];

            // 문서 제목 생성
            $subject = $member["mb_entNm"]."_".str_replace('-', '', $member["mb_giup_bnum"])."_".$_pen['data'][0]["penNm"].substr($_pen['data'][0]["penLtmNum"], 0, 6)."_".date("Ymd")."_";
            $subject_count_postfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `dc_subject` LIKE '{$subject}%'")["cnt"];
            $subject_count_postfix = str_pad($subject_count_postfix + 1, 3, '0', STR_PAD_LEFT); // zerofill
            $subject .= $subject_count_postfix;

            $_add_sql = "";

            // 수급자 주소 정보
            $_add_sql .= ("
                `penZip` = '" . ($_od['od_penZip1'].$_od['od_penZip2']) . "',
                `penAddr` = '" . ($_od['od_penAddr']) . "',
                `penAddrDtl` = '" . ($_od['od_penAddr2']) . "'
            ");


            // 구매자가 수급자 본인이 아닌경우 대리인의 주소 및 개인정보를 계약서에 넣기 위해 데이터 입력.
            if( $_od['relation_code'] != '0' ) {
                $_add_sql .= ("
                    ,

                    `applicantNm` = '{$_od['od_b_name']}',
                    `applicantRelation` = '1',
                    `applicantBirth` = '{$_od['od_birth']}',
                    `applicantAddr` = '" . $_od['od_addr'] . " " . $_od['od_addr2'] . "',
                    `applicantTel` = '{$_od['od_b_hp']}',

                    `contract_sign_type` = '1',
                    `contract_sign_name` = '{$_od['od_b_name']}',
                    `contract_tel` = '{$_od['od_b_hp']}',
                    `contract_addr` = '" . $_od['od_addr'] . " " . $_od['od_addr2'] . "',
                    `contract_sign_relation` = '{$_od['relation_code']}'

                ");
            }

            sql_query(" INSERT INTO `eform_document` 
                        SET `dc_id` = UNHEX('$dcId'),
                            `dc_status`     = '11',
                            `dc_subject`    = '{$subject}',
                            `od_id`         = '{$_od['od_sync_odid']}',
                            `do_date`       = NOW(),
                            `dc_sign_send_datetime` = '0000-00-00 00:00:00',
                            `dc_datetime`   = NOW(),
                            `dc_type`       = '1',
                            
                            `entId`         = '{$member["mb_entId"]}',
                            `entNm`         = '{$member["mb_entNm"]}',
                            `entCrn`        = '{$member["mb_giup_bnum"]}',
                            `entNum`        = '{$member["mb_ent_num"]}',
                            `entMail`       = '{$member["mb_email"]}',
                            `entCeoNm`      = '{$member["mb_giup_boss_name"]}',
                            `entConAcc01`   = '{$member["mb_entConAcc01"]}',
                            `entConAcc02`   = '{$member["mb_entConAcc02"]}',

                            `penId`         = '{$_pen['data'][0]['penId']}',
                            `penNm`         = '{$_pen['data'][0]['penNm']}',
                            `penConNum`     = '{$_od['od_penConPnum']}',
                            `penBirth`      = '{$_pen['data'][0]['penBirth']}',
                            `penLtmNum`     = '{$_pen['data'][0]['penLtmNum']}',
                            `penRecGraCd`   = '{$_pen['data'][0]['penRecGraCd']}',
                            `penRecGraNm`   = '{$_pen['data'][0]['penRecGraNm']}',
                            `penRecTypeCd`  = '01',
                            `penRecTypeTxt` = '{$_pen['data'][0]['penRecTypeTxt']}',
                            `penTypeCd`     = '{$_pen['data'][0]['penTypeCd']}',
                            `penTypeNm`     = '{$_pen['data'][0]['penTypeNm']}',
                            `penExpiDtm`    = '{$_pen['data'][0]['penExpiDtm']}',
                            `penJumin`      = '" . mb_substr(preg_replace("/[^0-9]*/s", "", $_pen['data'][0]['penBirth']),2,6) . "',

                            {$_add_sql}
                            
            ");


            // 주문정보에서 수급자랑 WMDS와 연결되는 "ordId"값 확인.
            $tmp_od = sql_fetch(" SELECT OD.od_id, OD.ordId FROM g5_shop_order_api API RIGHT JOIN g5_shop_order OD ON OD.od_id = API.od_sync_odid WHERE API.order_send_id = '" . $_GET['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "' ");


            // 계약서 품목별 초기값 가져오기
            $tmp_res = get_eroumcare(EROUMCARE_API_EFORM_SELECT_INITIAL_STATE_LIST, array(
                'penOrdId' => $tmp_od["ordId"]
            ));
            //var_dump($tmp_res);

            $_barCode = [];
            while($tmp_row = sql_fetch_array($_order_ct)) {
                //var_dump($tmp_row);
                if( $tmp_row['ct_barcode'] ) {
                    $_arraybarcode = json_decode( $tmp_row['ct_barcode'], true);            
                    foreach ($_arraybarcode as $key => $val) { 
                        $_barCode[$tmp_row['ProdPayCode']][] = $val; 
                    }
                }
            }
            //var_dump($_order_ct);
            //var_dump($_barCode);
            //var_dump($tmp_res);

            if( $tmp_res["data"] && is_array($tmp_res["data"]) ) {
                foreach($tmp_res["data"] as $it) {
                    $priceEnt = intval($it["prodPrice"]) - intval($it["penPrice"]);
                            
            //var_dump($it);
                    // 비급여 품목은 계약서에서 제외
                    if ($it['gubun'] != '02') {
                        //echo">>";
                        $_bc = array_shift( $_barCode[$it["prodPayCode"]] );
                        //echo"<<";
                        sql_query("INSERT INTO `eform_document_item` SET
                                    `dc_id` = UNHEX('$dcId'),
                                    `gubun` = '{$it["gubun"]}',
                                    `ca_name` = '{$it["itemNm"]}',
                                    `it_name` = '{$it["prodNm"]}',
                                    `it_code` = '{$it["prodPayCode"]}',
                                    `it_barcode` = '{$_bc}',
                                    `it_qty` = '1',
                                    `it_date` = '{$it["contractDate"]}',
                                    `it_price` = '{$it["prodPrice"]}',
                                    `it_price_pen` = '{$it["penPrice"]}',
                                    `it_price_ent` = '$priceEnt'
                        ");
                    }
                }
            }
            // -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
        }
    }
    
    $eform = []; 
    if( $_setp >= 5 ) { $eform = sql_fetch(" SELECT *, hex(dc_id) as uuid  FROM `eform_document` WHERE od_id = '{$_od['od_sync_odid']}'"); }

    // 주문확인 로그.
    if( $_setp == 1 ) { 
        api_log_write($_od['order_send_id'],$member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 수급자 주문 확인."); 
    }
?>

    <style>
        hr.title { border: none; border-top: 2px solid #333; color: #fff; background-color: #fff; height: 1px; width: 100%; }
        hr.body { border: none; border-top: 2px dotted #333; color: #fff; background-color: #fff; height: 1px; width: 100%; }

        .ord_info > li { line-height: 30px; }
        ._bottom_radius { border-radius: 10px; cursor : pointer; }
        ._bottom_radius_submit { width:150px; height:45px; padding: 0px; background: #c3c3c3; text-align:center; border-radius: 10px; }
        .select_reject_msg { margin: 0px 10px; display: inline-block; padding: 5px; border: 1px solid #d7d7d7; border-radius: 3px; font-size: 14px; float: left; width:90%;}

        #pen_inquiry_txt { color:red; font-size:16px; }
        #order_processing_txt { display: none; }

        /* 팝업 */
        #item_popup_box, #reject_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:999; background: rgba(0, 0, 0, 0.8); }
        #item_popup_box iframe { width:1000px; height:700px; max-height: 90%; position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%); background: white; }

        #reject_popup_box .visual { width:500px; height:250px; max-height: 80%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; }
        #reject_popup_box .visual .headerTitle { background: #111111; padding: 10px 30px 10px 30px; color:white; display: flex; justify-content: space-between; }
        #reject_popup_box .visual .headerTitle h5 { font-size: 20px; }
        #reject_popup_box .visual .headerTitle .cancel i { color: white; }
        #reject_popup_box .visual .headerTitle .cancel i:hover { color: #93D500; }
        #reject_popup_box .visual .contentsWrap { text-align:center; padding: 25px 40px; }
        #reject_popup_box .visual .contentsWrap select { text-align:center; margin-bottom: 25px; padding: 10px 0px; font-size:20px;}

        .popup_box_close { position:absolute; top:15px; right: 15px; color: white; font-size: 2.5em; cursor:pointer; }

        @media (max-width: 1020px) {
            #item_popup_box iframe { width: 100%; height: 90%; max-height:100%; transform: none; top: auto; left: 0px; bottom:0px; }
        }

        *{margin: 0;padding: 0;box-sizing: border-box;}
        li{list-style: none;}      
        img{display: block;}

        /* 상세 페이지 상단 프로세스 바~ */
        .thkc_Pro_container { margin: 0 5%; }
        .thkc_pro_box { background: #FFFFFF; border-radius: 30px; padding: 5px 10px; display: flex; justify-content: center; text-align: center; flex-grow: 1; }
        .thkc_pro_now { color:#fff; background: #F08606; font-weight: bold; font-size: 14px; }
        .thkc_pro_rest { color: #333; border: 1px solid #ddd; }
        .thkc_progressBar { font-size: 12px; color: #F08606; display: flex; justify-content: space-between; }
        .thkc_pro_finish { border: 1px solid #F08606; }
        .thkc_pro_arrow{ height: 12px; }

        .thkc_pro_line_01 { display: flex; align-items: center; color: #000; margin: 10px 0; }
        .thkc_pro_line_02 { display: flex; align-items: center; color: #000; margin: 8px 0; }
        .thkc_pro_line_01::before { content: ""; width: 50px; background: #F08606; height: 1px; }
        .thkc_pro_line_02::before { content: ""; width: 50px; background: #ddd; height: 1px;  }

        <?php if($_setp != 10) { ?>
        .thkc_progressBar { color: #F08606; }
        .thkc_pro_finish { border: 1px solid #F08606; }
        .thkc_pro_line_01::before { background: #F08606; }
        .thkc_pro_arrow{ background-image: url("/img/thkc_icon_pro_arrow.png");}  
        <?php } else { ?>
        .thkc_progressBar { color: #c6c6c6; }
        .thkc_pro_finish { border: 1px solid #c6c6c6; }
        .thkc_pro_line_01::before { background: #c6c6c6; }

        <?php } ?>

        
        @media (max-width: 767px){
            .thkc_Pro_container{width: 100%; padding: 0 10px;}
            .thkc_pro_line_01 {margin: 20px 0;}
            .thkc_pro_line_01,.thkc_pro_line_02::before{width: 10px;} 
        }

        /* 로딩 팝업 */
        #loading { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
        #loading .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        #loading .loading_modal p { padding: 0; font-size: 16px; }
        #loading .loading_modal img { display: block; margin: 20px auto; }
        #loading .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }

        /* 인증서 비번 팝업 - 인증서 업로드 추가 */
        #cert_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background: rgba(0, 0, 0, 0.5); }
        #cert_popup_box iframe { width:322px; height:307px; max-height: 80%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; }

        #cert_guide_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background: rgba(0, 0, 0, 0.5); }
        #cert_guide_popup_box iframe { width:850px; height:750px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; }

        #cert_ent_num_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background: rgba(0, 0, 0, 0.5); }
        #cert_ent_num_popup_box iframe { width:300px; height:305.33px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; }

        #view_list {
                    position: absolute;
                    color: #333;
                    font-weight: normal;
                    font-size: 14px;
                    line-height: 20px;
                    height: 60px;
                    padding: 5px 36px;
                    border-radius: 3px;
                    vertical-align: middle;
                    background-color: #fff;
                    color: #000;
                    border: 1px solid #000;
                    cursor: pointer;
                    right: 175px;
                    top: 25px;
        }
        #view_link {
                    position: absolute;
                    color: #333;
                    font-weight: normal;
                    font-size: 14px;
                    line-height: 20px;
                    height: 60px;
                    padding: 5px 36px;
                    border-radius: 3px;
                    vertical-align: middle;
                    background-color: #000;
                    color: #fff;
                    border: none;
                    cursor: pointer;
                    right: 0px;
                    top: 25px;
        }
    </style>


    <section class="wrap">
        <div class="sub_section_tit">수급자 주문상세</div>
        <button type="button" class="" id="view_link" Onclick="window.open('https://eroum.co.kr/partners/login','_blank'); ">이로움ON 맴버스<br />바로가기</button>
        <button type="button" class="" id="view_list" Onclick="location.href = '/shop/eroumon_order_list.php'; ">목록</button>
    </section>

    <hr class="title" />

    <div id="order_processing_ProBar">
    <div class="thkc_Pro_container">
        <ul class="thkc_progressBar">
             
            <li class="thkc_pro_box thkc_pro<?=( ($_setp==1)?("_now"):( ($_setp>1)?("_finish"):("_rest") ) );?>">승인대기</li>
            <li class="thkc_pro_line<?=( ($_setp>1)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==2)?("_now"):( ($_setp>2)?("_finish"):("_rest") ) );?>">주문처리</li>
            <li class="thkc_pro_line<?=( ($_setp>2)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==3)?("_now"):( ($_setp>3)?("_finish"):("_rest") ) );?>">결제완료</li>
            <li class="thkc_pro_line<?=( ($_setp>3)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==4)?("_now"):( ($_setp>4)?("_finish"):("_rest") ) );?>">주문완료</li>
            <li class="thkc_pro_line<?=( ($_setp>4)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==5)?("_now"):( ($_setp>5)?("_finish"):("_rest") ) );?>">출고완료</li>
            <li class="thkc_pro_line<?=( ($_setp>5)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==6)?("_now"):( ($_setp>6)?("_finish"):("_rest") ) );?>">계약서작성완료</li>
            <li class="thkc_pro_line<?=( ($_setp>6)?("_01 thkc_pro_arrow"):("_02") )?>"></li>

            <li class="thkc_pro_box thkc_pro<?=( ($_setp==7)?("_now"):( ($_setp>7)?("_finish"):("_rest") ) );?>">계약서서명완료</li>
        </ul>
    </div>
    </div>

    <div style="padding: 15px 0px;"></div>

    <section class="wrap">
        <div class="sub_section_tit" style="font-size: 20px;">수급자 주문정보</div>
    </section>
    

    <form name="ord_info" id="ord_info" method="POST" onsubmit="return;">
		
        <div class="ord_info" style="padding:0 20px;">
            <li><strong>주문번호</strong> : <?=$_od['order_send_id']?></li>
            <li><strong>주문일</strong>  : <?=$_od['od_time']?></li>
            <li><strong>구매인</strong>  : <?=$_od['od_b_name']?> / <?=$_od['od_b_hp']?></li>
            <li><strong>주문상태</strong>  : <?=$_od['od_status']?></li>
            <li><strong>배송지</strong>  : 우)<?=$_od['od_b_zip1']?><?=$_od['od_b_zip2']?> / <?=$_od['od_b_addr1']?> <?=$_od['od_b_addr2']?> <?=$_od['od_b_addr3']?></li>
            <li><strong>배송메시지</strong>  : <?=$_od['od_memo']?></li>
			<li><strong>수령인</strong>  : <?=$_od['od_b_name2']?> / <?=$_od['od_b_tel']?></li>
            <li>
                <strong>수급자</strong>  : <?=$_od['od_penNm']?> / L<?=$_od['od_penLtmNum']?>
                <?php if( $_setp != 10 ) { ?>
                <input type="bottom" value="요양정보 간편조회" class="search_rep_info _bottom_radius" id="_submit" style="width:150px; height:35px; padding: 0px; background: #e5e5e5; border: 2px solid #333; text-align:center; color:#000; font-size:16px;"<?=( $_od['od_status'] != "승인대기" )?" disabled":""?>>
                <?php if( $_od['od_status'] == "승인대기" ) { ?>
                    <span id="pen_inquiry_txt"> &nbsp; ※ 주문 처리 전에, 반드시 요양정보조회를 해주시기 바랍니다.</span>
                <?php } } ?>
            </li>
            
            <input type="hidden" id="order_send_id" name="order_send_id" value="<?=$_od['order_send_id'];?>">
            <input type="hidden" id="_inquiry_ok" name="_inquiry_ok" value="">

            <input type="hidden" id="_inquiry_penNm" name="penNm" value="<?=$_od['od_penNm']?>">
            <input type="hidden" id="_inquiry_penLtmNum" name="penLtmNum" value="<?=$_od['od_penLtmNum']?>">

            <input type="hidden" name="penNm_parent" value="<?=$_od['od_penNm']?>">
            <input type="hidden" name="penLtmNum_parent" value="<?="L".$_od['od_penLtmNum']?>">
            

        </div>

        <div style="padding: 15px 0px;"></div>

        <div class="list_box">
            <table id="table_list">
            <thead>
                <tr>
                    <th>상품 정보</th>
                    <th style="width:28%;">기타</th>
                </tr>
            </thead>
            <tbody>
            <?php
                for($i=0; $row=sql_fetch_array($_ct); $i++) {
                    $bg = 'bg'.($i%2);
            ?>    
                <tr data-ctid="" class="<?=$bg?>">              
                    <td style="text-align:left;">
                        <div style="float:left; padding:5px 20px 5px 25px;">
                            <img src="/data/item/<?=$row['it_img1'];?>" style="width:70px;">
                        </div>
                        <div style="padding-top:<?=($row['io_id'] != "")?"7":"18";?>px;">
                            <p><?=$row['it_name'];?></p>
							<?=($row['io_id'] != "")?"<p>옵션 : ".$row['io_id']."</p>":"";?>
                            <p>수량 : <?=number_format($row['ct_qty']);?>개</p>
                        </div>

                        <input type="hidden" name="ct_id[]" value="<?=$row['ct_id'];?>">
                        <input type="hidden" name="it_id[]" value="<?=$row['it_id'];?>">
                        <input type="hidden" name="ProdPayCode[]" value="<?=$row['ProdPayCode'];?>">

                    </td>
                    <td style="text-align:center;">
                        <?php if( $_od['od_status'] == "승인대기" ) { ?>
                        <p class="reject_<?=$row['ct_id'];?>"><?=$row['ct_status'];?></p>
                        <select name='reject_msg[]' id='reject_msg' class='select_reject_msg select_reject_msg_<?=$row['ct_id'];?>' OnChange="status_change('<?=$row['ct_id'];?>',this.value);">
                            <option value=''>반려 사유 선택</option>
                            <option value='제품품절'<?=($row['ct_memo']=="제품품절")?" selected":"";?>>제품품절</option>
                            <option value='자격미달'<?=($row['ct_memo']=="자격미달")?" selected":"";?>>자격미달</option>
                            <option value='잔액부족'<?=($row['ct_memo']=="잔액부족")?" selected":"";?>>잔액부족</option>
                            <?php
                                // 23.03.08 : 서원 - 주석용
                                // 해당 항목 변경 시 아래 하단 부분의 일괄작업을 위한 팝업부분의 항목도 같이 변경 해야함.
                            ?>
                        </select>
                        <?php } else { ?>
                            <p class="reject_<?=$row['ct_id'];?>"><?=$row['ct_status'];?></p>
                            <?php
                                if( $row['ct_status'] == "반려" ) {
                                    echo("<p>사유 : " . $row['ct_memo'] . "</p>");
                                }
                            ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php
                }
            ?>
            </tbody>
            </table>
        </div>

    </form>
    <span id="order_processing_txt"> &nbsp; ※ ‘반려 사유 선택’ 또는 ‘일괄 반려’후에도, 반드시 “주문처리” 버튼을 눌러 주시기 바랍니다.</span>
    
    <?php if( $_od['od_status'] == "승인대기" ) { ?>
        <div style="padding: 15px 0px 50px 0px; text-align:right;">
            <input type="bottom" value="일괄반려 사유선택" class="btn_submit _bottom_radius" id="reject_all" style="width:150px; height:30px; padding: 0px; background: #fff; text-align:center; color:#000; border: 1px solid #000;">
        </div>

        <div style="padding: 50px 0px 15px 0px; text-align:center;">
            <input type="bottom" value="주문처리" class="btn_submit _bottom_radius_submit" id="order_processing" style="" disabled>
        </div>
    <?php } else { ?>
        <?php if( $_setp == 10 ) { ?>
        <div id="order_from_txt_cancel" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold; ">
            ※ 주문이 취소 되었습니다.
        </div>
        <?php } ?>
        <div style="padding: 100px 0px 15px 0px; text-align:center;"> </div>
    <?php } ?>

    <hr class="body" />

    <div id="order_from_ProBar"></div>

    <section class="wrap">
        <div class="sub_section_tit" style="font-size: 20px;">
            이로움 주문정보
        </div>
    </section>


    <?php if( ($_setp != 10) && ($_setp >= 4) ) { ?>

    <div style="height:60px; ">
        <span style="font-size: 14px;">주문 번호: <?=$_od['od_sync_odid']?></span>
        <input type="bottom" value="주문 상세보기" class="_bottom_radius" id="" onclick="window.open('/shop/orderinquiryview.php?od_id=<?=$_od['od_sync_odid']?>', '_blank');" style="width:150px; height:35px; padding: 0px; background: #e5e5e5; border: 2px solid #333; text-align:center; color:#000; font-size:16px;">
    </div>

    <div class="list_box">
        <table id="table_list">
        <thead>
            <tr>
                <th>NO</th>
                <th>상품명</th>
                <th>옵션명</th>
                <th>수량</th>
                <th style="width:28%;">상태</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                //mysqli_data_seek($_ct,0); 
                for($i=0; $row=sql_fetch_array($_order_ct); $i++) { 
            ?>
            <tr>
                <td><?=($i+1)?></td>
                <td><?=($row['it_name'])?></td>
                <td><?=($row['ct_option'])?></td>
                <td style=" text-align:center;"><?=($row['ct_qty'])?></td>
                <td style="width:28%; text-align:center;">
                
                <?php                    
                    switch ($row['ct_status']) {
                        case '보유재고등록': $ct_status_text="보유재고등록"; break;
                        case '재고소진': $ct_status_text="재고소진"; break;
                        case '작성': $ct_status_text="작성"; break;
                        case '주문무효': $ct_status_text="주문무효"; break;
                        case '취소': $ct_status_text="주문취소"; break;
                        case '주문': $ct_status_text="주문접수"; break;
                        case '입금': $ct_status_text="입금완료"; break;
                        case '준비': $ct_status_text="상품준비"; break;
                        case '출고준비': $ct_status_text="출고준비"; break;
                        case '배송': $ct_status_text="출고완료"; break;
                        case '완료': $ct_status_text="배송완료"; break;
                    }
                ?>
                <?=($ct_status_text)?>
                
                </td>
            </tr>
            <?php } ?>
        <tbody>
        </table>
    </div>

    <?php } ?>


    <div id="order_from_txt1" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold;">
        현재 주문된 내역이 없습니다. 구매가 결제 후, 주문할 수 있습니다.
    </div>
    
    <div id="order_from_txt2" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold; display: none; ">
        구매자 결제가 완료 되었습니다. 상품을 주문하시기 바랍니다.
    </div>

    <?php if( $_setp == 10 ) { ?>
    <div id="order_from_txt_cancel" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold; ">
        ※ 주문이 취소 되었습니다.
    </div>
    <?php } ?>

    <div style="padding: 100px 0px 15px 0px; text-align:center;">
        <input type="bottom" value="주문하기" class="btn_submit _bottom_radius_submit" id="order_from" style="" disabled>
    </div>



    <hr class="body" />

    <div id="order_contract_ProBar"></div>

    <section class="wrap">
        <div class="sub_section_tit" style="font-size: 20px;">이로움 계약서 정보</div>
    </section>


    <?php if( ($_setp != 10) && ($_setp >= 6) ) { ?>

    <div style="height:60px; ">
        <span style="font-size: 14px;">계약서 번호 :<?=$eform['uuid']?></span>
        <input type="bottom" value="계약 상세보기" class="_bottom_radius" id="" onclick="mds_download('<?=$eform['uuid']?>',1);" style="width:150px; height:35px; padding: 0px; background: #e5e5e5; border: 2px solid #333; text-align:center; color:#000; font-size:16px;">
    </div>

    <div class="list_box">
        <table id="table_list">
        <thead>
            <tr>
                <th>계약서 번호</th>
                <th>수급자 정보</th>
                <th>상품정보</th>
                <th>작성일</th>
                <th style="">진행상태</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?=$eform['uuid']?></td>
                <td>
                    <p>수급자명: <?=$eform['penNm']?></p>
                    <p>요양인정번호: <?=$eform['penLtmNum']?></p>
                </td>
                <td>
                    <?php
                        mysqli_data_seek($_order_ct,0); 
                        $row=sql_fetch_array($_order_ct);
                        echo($row['it_name']);
                        if( ($_order_ct->num_rows-1) != 0 ){
                            echo("외 ".($_order_ct->num_rows-1)."건");
                        }
                    ?>
                </td>
                <td style=" text-align:center;"><?=$eform['dc_datetime']?></td>
                <td style="text-align:center;">
                    <?php
                        if( $eform['dc_status'] == "4" ){ echo("서명요청"); }
                        else if( $eform['dc_status'] == "2" ){ echo("서명완료"); }
                        else if( $eform['dc_status'] == "3" ){ echo("서명완료"); }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php } ?>


    <div id="order_contract_txt1" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold;">
        주문한 상품이 출고완료 된 후에 계약서를 작성할 수 있습니다.
    </div>
    
    <div id="order_contract_txt2" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold; display: none; ">
        출고가 완료되었습니다. 계약을 진행하시기 바랍니다.
    </div>

    <?php if( $_setp == 10 ) { ?>
    <div id="order_contract_txt_cancel" style="padding: 50px 0px 15px 0px; text-align:center; font-weight: bold; ">
        ※ 주문이 취소 되었습니다.
    </div>
    <?php } ?>

    <div style="padding: 50px 0px 15px 0px; text-align:center;">
        <input type="bottom" value="계약하기" class="btn_submit _bottom_radius_submit" id="order_contract" style="" disabled>
    </div>


    <div id="item_popup_box">
        <div class="popup_box_close"><i class="fa fa-times"></i></div>
        <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
    </div>


    <div id="reject_popup_box">
        <div class="visual">
        <div class="visalWrap">

            <!-- title -->
            <div class="headerTitle">
                <h5>일괄 반려</h5>
                <div class="popup_box_close" onclick="$('#reject_popup_box').hide();"><i class="fa fa-times"></i></div>
            </div>

            <!-- contents -->
            <div class="contentsWrap">

                <select name='reject_all_msg' id='reject_all_msg' class='select_reject_msg'>
                    <option value=''>반려 사유 선택</option>
                    <option value='제품품절'>제품품절</option>
                    <option value='자격미달'>자격미달</option>
                    <option value='잔액부족'>잔액부족</option>
                </select>

                <input type="bottom" value="취소" onclick="$('#reject_popup_box').hide();" class="btn_submit" id="cancel" style="text-align:center; color: #000; background: #fff; border: 1px solid #000;" >
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <input type="bottom" value="저장" onclick="reject_all_apply_confirm( $('#reject_all_msg').val() );" class="btn_submit" id="save" style="text-align:center; background: #000;" >

            </div>


        </div>
        </div>
    </div>




    <!-- 로딩 -->
    <div id="loading" style="display: none;">
    <div class="loading_modal">
        <p>처리중 입니다.</p>
        <p>잠시만 기다려주세요.</p>
        <img src="/shop/img/loading.gif" alt="loading">
    </div>
    </div>


    <!-- 인증서 업로드 추가 영역 -->
    <div id="cert_ent_num_popup_box">
    <iframe name="cert_ent_num_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>
    </div>

    <div id="cert_popup_box">
    <iframe name="cert_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>
    </div>

    <div id="cert_guide_popup_box">
    <iframe name="cert_guide_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>
    </div>

    <iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>

    <div class="btn_so_sch" id="btn_so_sch"></div>


    <?php if( ( ($_setp != 10) && ($_setp == 5) ) && ($eform['uuid']) && ($eform['dc_status'] == "11" ) ) { ?>
    <iframe name="simple_eform_iframe" id="simple_eform_iframe" src="/shop/simple_eform_new.php?dc_id=<?=$eform['uuid']?>" scrolling="no" frameborder="0" allowTransparency="false" style="width:100%; height:0px;"></iframe>
    <?php } ?>

    <?php //include_once('./popup_sign_send.php');
    ?>


    <script>
    $(function() { });


    $('.btn_so_sch').click(function(e) {

	<?php 
		if($member["cert_reg_sts"] != "Y") {//등록 안되어 있음
			if($mobile_yn == 'Pc') {
	?>
			//공인인증서 등록 안내 및 등록 버튼 팝업 알림으로 교체 될 영역	
			cert_guide();
			return;
	<?php 
			} else {
	?>
		alert("컴퓨터에서 공인인증서를 등록 후 이용이 가능한 서비스 입니다.");
		return;
	<?php	}
		} else { //등록 되어 있음
			if(!$is_file){ 
	?>
		tilko_call('1');
	<?php 
			} 
		}
	?>
      var pen_info = <?=json_encode($pen);?>;
      console.log("pen_info : ", pen_info);

      var str_rn = "<?=$_od['od_penNm']?>"; //$("input[name='penNm']")[0].value;
      var str_id = "<?="L".$_od['od_penLtmNum']?>";  //$("input[name='penLtmNum']")[0].value;
      var btn_update = document.getElementById('btn_so_sch');
      btn_update.disabled = true;
      $.ajax('ajax.recipient.inquiry.php', {
          type: 'POST',  // http method
          data: { id : str_id.replace('L',''),rn : str_rn },  // data to submit
          success: function (data, status, xhr) {
              if(data['message'] == 'undefined'){
                alert("다시 조회해주시기 바랍니다.");
                btn_update.disabled = true;
                return false;
              }
              alert(data['message']); // 조회가 완료되었습니다.              
              ct_history_list = data['data']; // 계약 이력 삽입용
              console.log("data : ", data);

              let sale_ll = [];
              let rent_ll = [];
              let rep_list = data['data']['recipientContractDetail']['Result'];
              ct_history_list = data['data'];
              
              let rep_info = rep_list['ds_welToolTgtList'][0];
              console.log("rep_info : ", rep_info);
              
              let applydtm = '';
              for(var ind = 0; ind < rep_list['ds_toolPayLmtList'].length; ind++){
                var appst = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(6,2));
                var apped = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(6,2));
                var today = new Date();
                if(today < apped && today > appst){
                  applydtm = appst.toISOString().split('T')[0]+' ~ '+apped.toISOString().split('T')[0];
                  break;
                }
                if(ind == rep_list['ds_toolPayLmtList'].length-1){
                  applydtm = rep_list['ds_toolPayLmtList'][0]['APDT_FR_DT']+' ~ '+rep_list['ds_toolPayLmtList'][0]['APDT_TO_DT'];
                }
              }

              let penPayRate = rep_info['SBA_CD'] == '일반' ? '15%': rep_info['SBA_CD'] == '기초' ? '0%':
              (rep_info['SBA_CD'].split('(')[1].substr(0, rep_info['SBA_CD'].split('(')[1].length-1));
              
              let pd_list = JSON.parse(data['data']['recipientToolList'])['Result'];
              let pd_keys = ['ds_payPsblLnd1','ds_payPsblLnd2','ds_payPsbl1','ds_payPsbl2'];
              for(var i = 0; i < Object.keys(pd_list).length; i++){
                let pd_type = pd_keys[i].substr(0, pd_keys[i].length-1) == 'ds_payPsbl'?'sale':'rent';             
                for(var ind = 0; ind < pd_list[pd_keys[i]].length; ind++){
                    let pd_name = pd_list[pd_keys[i]][ind]['WIM_ITM_CD'].replace(' ','');
                    eval(pd_type + '_ll')[pd_name] = pd_keys[i].substr(pd_keys[i].length-1, 1) == '2'?0:1;   
                }
              }
              
              var sale_ids = <?= json_encode($sale_ids);?>              
              var rent_ids = <?= json_encode($rent_ids);?>

			        var itemList=[];

              for(var ind = 0; ind < Object.keys(sale_ll).length; ind++){
                  if(Object.values(sale_ll)[ind] == 1){
                    if(Object.keys(sale_ll)[ind] == '미끄럼방지용품'){
                      itemList.push("<?=$sale_product_id8?>");
                      itemList.push("<?=$sale_product_id9?>");
                      
                    } else {
                      itemList.push(sale_ids[Object.keys(sale_ll)[ind]]);
                    }				            
                  }
              }

              for(var idx = 0; idx < Object.keys(rent_ll).length; idx++){
                  if(Object.values(rent_ll)[idx] == 1)
				            itemList.push(rent_ids[Object.keys(rent_ll)[idx]]);
              }

			  //let tpenExpiStDtm = rep_info['RCGT_EDA_FR_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(4,2)+'    -'+rep_    info['RCGT_EDA_FR_DT'].substr(6,2);

			  // UPDATE DB STR
        // for문을 돌려야겠지만, 현재는 각 field가 존재하는 지 확인만. 
        // 수급자 정보를 불러올 때, 적용기간 마감일을 penAppStDtm3->penAppStDtm2->penAppStDtm1 순서로
        // 존재여부를 확인해서 return 하기 때문에 3부터 가장 미래의 적용일을 넣어야 한다.
        var penAppStDtm1 = penAppEdDtm1 = penAppStDtm2 = penAppEdDtm2 = penAppStDtm3 = penAppEdDtm3 = "";
        if ( rep_list['ds_toolPayLmtList'].length > 0 ) 
		  	  {
		        	penAppStDtm3 = rep_list['ds_toolPayLmtList'][0]['APDT_FR_DT'];
		        	penAppEdDtm3 = rep_list['ds_toolPayLmtList'][0]['APDT_TO_DT'];
		  	  }
		  	  if ( rep_list['ds_toolPayLmtList'].length > 1 ) 
		  	  {
		        	penAppStDtm2 = rep_list['ds_toolPayLmtList'][1]['APDT_FR_DT'];
		        	penAppEdDtm2 = rep_list['ds_toolPayLmtList'][1]['APDT_TO_DT'];
		  	  }
		  	  if ( rep_list['ds_toolPayLmtList'].length > 2 ) 
		  	  {
		        	penAppStDtm1 = rep_list['ds_toolPayLmtList'][2]['APDT_FR_DT'];
		        	penAppEdDtm1 = rep_list['ds_toolPayLmtList'][2]['APDT_TO_DT'];
		  	  }

          var penTypeCd = '';
          if(rep_info['REDUCE_NM'].substr(0, 2) == '일반' || rep_info['REDUCE_NM'].substr(0, 2) == '의료' || rep_info['REDUCE_NM'].substr(0, 2) == '기초'){ //일반의료기초
            penTypeCd = rep_info['REDUCE_NM'].substr(0, 2) == '일반'? '00' : rep_info['REDUCE_NM'].substr(0, 2) == '의료'? '03' : '04';
          } else { //감경
            penTypeCd = rep_info['SBA_CD'].substr(3, 1) == '6'? '02' : '01';
          }
          
          var pen_gender = "<?=$_pen['data'][0]['penGender']?>" == "" ?"-" :"<?=$_pen['data'][0]['penGender']?>";
		      var sendData = {
		        penId : "<?=$_pen['data'][0]['penId'] ?>",
		        penNm : "<?=$_pen['data'][0]['penNm']?>",
		        penLtmNum : "<?=$_pen['data'][0]['penLtmNum'] ?>",
		        penRecGraCd : '0'+rep_info['LTC_RCGT_GRADE_CD'],
		        penGender : pen_gender,
		        penBirth : rep_info['BDAY'].substr(0,4)+'-'+rep_info['BDAY'].substr(4,2)+'-'+rep_info['BDAY'].substr(6,2),
		        penJumin : rep_info['BDAY'].substr(2,6),
		        penTypeCd : penTypeCd,
		        penConNum : "<?=$_pen['data'][0]['penConNum']?>", 
		        penConPnum : "<?=$_pen['data'][0]['penConPnum']?>",
		        penExpiStDtm : rep_info['RCGT_EDA_FR_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(6,2),
		        penExpiEdDtm : rep_info['RCGT_EDA_TO_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(6,2),
		        penRecDtm : "0000-00-00",
		        penAppDtm : "0000-00-00",
		        penAppStDtm1 : penAppStDtm1,
		        penAppEdDtm1 : penAppEdDtm1,
		        penAppStDtm2 : penAppStDtm2,
		        penAppEdDtm2 : penAppEdDtm2,
		        penAppStDtm3 : penAppStDtm3,
		        penAppEdDtm3 : penAppEdDtm3
		      }
          
          $.post('./ajax.inquiry_log.php', {
            data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : status,occur_page : "eroumon_order_view.php" }
          }, 'json')
          .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert("로그 저장에 실패했습니다!");
          });
          
		      $.post('./ajax.my.recipient.update.php', sendData, 'json')
		      .done(function(result) {
		        var data = result.data;
		  		  
		  
		        $.post('./ajax.my.recipient.setItem.php', {
		          penId: "<?=$_pen['data'][0]['penId'] ?>",
		          itemList: itemList
		        }, 'json')
		        .done(function(result) {
		          if(result.errorYN == "Y") {
		            alert(result.message);
		          } else {
		            alert('완료되었습니다');
					order_processing_able();
		            window.location.reload(true);
		          }
		        })
		        .fail(function($xhr) {
		          var data = $xhr.responseJSON;
		          alert(data && data.message);
		        }); 

            if(ct_history_list.length != 0){ // 계약이력 삽입
              let penPurchaseHist = <?=json_encode($recent_result)?>;

              if(penPurchaseHist == null){
                $.post('./ajax.my.recipient.hist.php', {
                  data: ct_history_list,
                  status: true
                }, 'json')
                .fail(function($xhr) {
                  var data = $xhr.responseJSON;
                  alert("계약정보 업데이트에 실패했습니다!");
                })

              } else if(ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].length > penPurchaseHist['cnt']){
                ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'] = ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].slice(penPurchaseHist['cnt'], ct_history_list.length);

                // TODO : pen_purchase_hist update 만들기
                // 이로움 DB에 계약정보 insert
                $.post('./ajax.my.recipient.hist.php', {
                  data: ct_history_list,
                  status: true
                }, 'json')
                .fail(function($xhr) {
                  var data = $xhr.responseJSON;
                  alert("계약정보 업데이트에 실패했습니다!");
                })
              }
            }

		      })
		      .fail(function($xhr) {
		        var data = $xhr.responseJSON;
		        alert(data && data.message);
		      });
			   

			  // UPDATE DB END

              btn_update.disabled = false;
          },
          error: function (jqXhr, textStatus, errorMessage) {
              var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
              //alert(errMSG);
              //인증서 업로드 추가 영역 
				if(errMSG == "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다." ){
					alert(errMSG);
					$.post('./ajax.inquiry_log.php', {
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : "fail",occur_page : "eroumon_order_view.php",err_msg:errMSG }
					}, 'json')
					.fail(function($xhr) {
					  var data = $xhr.responseJSON;
					  alert("로그 저장에 실패했습니다!");
					});
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "3"){
					alert("등록된 인증서가 사용 기간이 만료 되었습니다.<?=($mobile_yn == 'Mobile')?' 컴퓨터에서':'';?> 공인인증서를 재등록 해 주세요.");
					<?php if($mobile_yn == 'Pc'){?>tilko_call('1');<?php }?>
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "1"){
					alert("등록된 인증서가 없습니다.<?=($mobile_yn == 'Mobile')?' 컴퓨터에서':'';?> 공인인증서를 등록 해 주세요.");
					<?php if($mobile_yn == 'Pc'){?>tilko_call('1');<?php }?>
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "2"){
					<?php //if($mobile_yn == "Mobile"){?>
					pwd_insert();//모바일에서 로그인 시 레이어 팝업 노출
					<?php //}else{?>
					//tilko_call('2');
					<?php //}?>
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "4"){
					alert(errMSG);
					if(errMSG.indexOf("비밀번호") !== -1 || errMSG.indexOf("암호") !== -1){
						//tilko_call('2');
						pwd_insert();
					}
					$.post('./ajax.inquiry_log.php', {
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : "fail",occur_page : "eroumon_order_view.php",err_msg:errMSG }
					}, 'json')
					.fail(function($xhr) {
					  var data = $xhr.responseJSON;
					  alert("로그 저장에 실패했습니다!");
					});
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "5"){
					ent_num_insert();
				}
				// 인증서 업로드 추가 영역 끝
			  btn_update.disabled = false;
              return false;
          }
      });


    });



    $('#item_popup_box').click(function() { $('body').removeClass('modal-open'); $('#item_popup_box').hide(); });
    $('#reject_popup_box i').click(function() { $('body').removeClass('modal-open'); $('#reject_popup_box').hide(); });
    
    $('#cert_popup_box').click(function() { 
        $('body').removeClass('modal-open'); 
        $('#cert_popup_box').hide(); 
        $('#item_popup_box iframe').contents().find('#item_popup_button').show();
    });

    $('#cert_guide_popup_box').click(function() { $('body').removeClass('modal-open'); $('#cert_guide_popup_box').hide(); });
    $('#cert_ent_num_popup_box').click(function() { $('body').removeClass('modal-open'); $('#cert_ent_num_popup_box').hide(); });

	function order_processing_able(){
		if( $('#item_popup_box iframe').contents().find('.head').length) {
                $('#_inquiry_ok').val("Y");
                $('#pen_inquiry_txt').hide();
                $('#order_processing_txt').show();
				$('#order_processing').css('backgroundColor', '#333');
				$('#order_processing').attr('disabled', false);
        }
	}
    $('.search_rep_info').click(function() {
        $('#loading').show();
        var penNm = $('#ord_info #_inquiry_penNm').val();
        var penLtmNum = $('#ord_info #_inquiry_penLtmNum').val();
        var url = 'pop.eroumon_recipient_info.php?odid=<?=$_od['order_send_id']?>&penNm='+penNm+'&penLtmNum='+penLtmNum;
/*
        if( penLtmNum.length != 10 ) {
            $('#loading').hide();
            alert("[이로움ON 맴버스]로 부터 받은 수급자 정보에 오류가 있습니다.");
            return;
        }
*/
        $('#item_popup_box iframe').attr('src', url);
        $("#item_popup_box iframe").load(function () {
            $('#loading').hide();
            $('body').addClass('modal-open');
            $('#item_popup_box').show();
            
            
            
            <?php if( ($_pen["data"][0]["modifyDtm"])&&(mb_substr($_pen["data"][0]["modifyDtm"],0,8) != date("Ymd")) ) { ?>
            if(!$('#item_popup_box iframe').contents().find('#item_popup_button').length) {
                $('#item_popup_box iframe').contents().find('.head').append(" <div id=\"item_popup_button\"><span style='left:-50px;top:65px;color:red;position:relative;'>요양정보 업데이트를 해주셔야 주문처리가 가능합니다.</span><button type=\"button\" class=\"btn_so_sch\" id=\"btn_so_sch\" Onclick=\"parent.$('.btn_so_sch').trigger('click'); loading(); $('#item_popup_button').hide();\">요양정보 업데이트</button></div> ");
				$('#item_popup_box iframe').contents().find('.head-title').append("<span class = 'rep_common'>"+penNm+"("+penLtmNum+")</span><span>님의 요양정보</span> ");
                $('#item_popup_box iframe').contents().find('#btn_so_sch').css({ 'float': 'right', 'position': 'relative', 'display': 'inline-block', 'color': '#333', 'font-weight': 'normal', 'font-size': '14px', 'line-height': '20px', 'height': '60px', 'padding': '5px 36px', 'border-radius': '3px', 'vertical-align': 'middle', 'background-color': '#ee8102', 'color': '#fff', 'border': 'none', 'margin': '10px 0', 'cursor': 'pointer' });
            }
            <?php } ?>
        });

    });

    
    $('#reject_all').click(function() {
        $('body').addClass('modal-open');
        $('#reject_popup_box').show();
    });


    $('#order_processing').click(function() {
        if( !confirm("'주문처리'를 진행 하시겠습니까?") ) { return; }
        $('#loading').show();

        if( $('#_inquiry_ok').val() && ($('#_inquiry_ok').val()==="Y") ) {
            
            //alert( $('#ord_info #order_send_id').val() );
            var _data = [];            
            $("#ord_info input[name='ct_id[]']").each(function(idx){    

                /*
                alert( $("input[name='ct_id[]']:eq("+idx+")").val() );
                alert( $("input[name='it_id[]']:eq("+idx+")").val() );
                alert( $("input[name='ProdPayCode[]']:eq("+idx+")").val() );
                alert( $("select[name='reject_msg[]']:eq("+idx+")").val() );
                */

                _data[idx] = {
                    "ct_id" : $("#ord_info input[name='ct_id[]']:eq("+idx+")").val(),
                    "it_id" : $("#ord_info input[name='it_id[]']:eq("+idx+")").val(),
                    "ProdPayCode" : $("#ord_info input[name='ProdPayCode[]']:eq("+idx+")").val(),
                    "reject_msg" : $("#ord_info select[name='reject_msg[]']:eq("+idx+")").val()
                };
            });

            // ajax 처리 시작
            $.ajax({
                url: '/shop/ajax.eroumon_order_processing.php', type: 'POST', dataType: 'json', 
                data: {
                    "order_send_id": $('#ord_info #order_send_id').val(),
                    "data": _data
                },
                success: function(data) {
                    if( data.YN === "N" ) { 
                        alert(data.YN_msg);
                        $('#loading').hide();
                    } else {
                        
                        // GET 방식으로 서버에 HTTP Request를 보냄.
                        $.get( "/api/v1_order_resend.php", { 'API_Div' : 'order_ent_response', 'order_send_id' : $('#ord_info #order_send_id').val() } ); // 서버가 필요한 정보를 같이 보냄.
                        <?php
                            // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
                            // 23.03.22 : 서원
                            // 주요주석 : g5_shop_order_api 테이블의 'od_status'컬럼의 상태값은 위 '/api/v1_order_resend.php' API에서 처리함.
                            //              로직적으로는 처리가 끝났으나 해당 API에서 상태값을 변경 못할 경우가 발생 할 수도 있음.
                            // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
                        ?>
                        alert("[주문처리] 완료 되었습니다.");                        
                        location.reload();

                    }
                },
                error: function(e) {}
            });
            // ajax 처리 종료

        } else {
            alert("요양정보조회 확인 후 '주문처리' 가능 합니다.");
            location.reload();
            return;
        }

        return;
    });



    $('#order_from').click(function() {
        if( !confirm("'주문하기'를 진행 하시겠습니까?") ) { return; }
        $('#loading').show();

        $.ajax({
            url: '/shop/ajax.eroumon_order_insert.php', type: 'POST', dataType: 'json',
            data: {
                "order_send_id": $('#ord_info #order_send_id').val()
            },
            success: function(data) {
                if( data.YN === "N" ) { 
                    alert(data.YN_msg);
                    $('#loading').hide();
                    if( data.YN_reload === "1" ){ window.location.reload(true); }
                } else {
                    alert("[주문하기] 완료 되었습니다.");
                    location.reload();
                }
            },
            error: function(e) {}
        });

        return;
    });


    $('#order_contract').click(function() {
        if( !confirm("'계약서 작성&서명요청'을 진행 하시겠습니까?") ) { return; }
        $('#loading').show();

        <?php if( $_od['relation_code'] != '0' ) { ?>
        if( $('#simple_eform_iframe').contents().find('#contract_sign_type').is(':checked') != true ){            
            $('#simple_eform_iframe').contents().find('#contract_sign_type').prop("checked",true);
        }
        <?php } ?>

        var _barcode_wr = $('#simple_eform_iframe').contents().find('.it_barcode_wr');
        _barcode_wr.each(function() {
            var it_barcode = [];
            $(this).find('.it_barcode').each(function() { it_barcode.push($(this).val()); });
            $(this).parent().find('input[name="it_barcode[]"]').val(it_barcode.join(String.fromCharCode(30)));
        });


        // 대여제품 계약기간 값 적용
        var it_date_wr = $('#simple_eform_iframe').contents().find('.it_date_wr');
        it_date_wr.each(function() {
            var from = $(this).find('input[data-range="from"]').val();
            var to = $(this).find('input[data-range="to"]').val();
            if(from && to) { $(this).find('input[name="it_date[]"]').val(from + '-' + to); }
        });


        var $form = $('#simple_eform_iframe').contents().find('#form_simple_eform');
        var formdata = $form.serialize();


        $.post('/shop/ajax.simple_eform_new.php', formdata, 'json')
        .done(function(result) {

            $.post('/shop/ajax.eform_mds_api.php', {
                div : "new_doc"
                ,mb_entId1 : '<?=$eform['entId']?>'
                ,dc_id1 : '<?=$eform['uuid']?>'
                ,title : '<?=$eform['dc_subject']?>'

                <?php if( $_od['relation_code'] == '0' ) { ?>
                ,pen_sign : "1"
                ,pen_send : "KAKAO"
                ,pen_send_tel : "<?=preg_replace("/[^0-9]*/s", "", $eform['penConNum']);?>"
                ,name1 : "<?=$eform['penNm']?>"
                <?php } else { ?>
                ,contract_sign : "1"
                ,contract_send : "KAKAO"
                ,contract_send_tel : "<?=preg_replace("/[^0-9]*/s", "", $eform['contract_tel']);?>"
                ,name2 : "<?=$eform['contract_sign_name']?>"
                <?php } ?>

            }, 'json')
            .done(function(data) {

                if(data.api_stat != "1"){
                    alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
                    $('#loading').hide();
                    return false;				
                }
                
                if(data.url != "url생성실패"){

                    // GET 방식으로 서버에 HTTP Request를 보냄.
                    $.get( "/shop/ajax.eroumon_eform_sign.php", { 'type':'SendOK', 'dcid' : '<?=$eform['uuid']?>' } ); // 서버가 필요한 정보를 같이 보냄.

                    alert("[계약서 작성완료 및 서명요청]이 완료 되었습니다.");                    
                    location.reload();
                }else{
                    alert(res.url);//url 생성실패 알림
                    $('#loading').hide();
                }
            })
            .fail(function($xhr) {
                var data = $xhr.responseJSON;
                alert(data && data.message);
                $('#loading').hide();
            });	
            
        })
        .fail(function ($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
            $('#loading').hide();
        })

        return;
    });


	// 계약서,감사추적인증서 보기 
	function mds_download(dc_id,gubun) {//1:계약서,2:감사추적인증서
 		$.post('/shop/ajax.eform_mds_api.php', {
			dc_id:dc_id,
			gubun:gubun,
			div:'view_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}
			if(data.url != "url생성실패"){				
				window.open(data.url, "PopupDoc", "width=1000,height=1000");
			}else{
				alert(data.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});	
	}

    // 주문 상품의 상태값만 우선 저장 처리
    function status_change( ctid, val ){
        
        $.ajax({
            url: '/shop/ajax.eroumon_order_ctstatus_change.php', type: 'POST', dataType: 'json',
            data: {
                "order_send_id": $('#ord_info #order_send_id').val(), "ctid": ctid, "txt": val
            },
            success: function(data) {
                //location.reload();
                if( data.YN === "Y" ) { 
                    if( !val ) {
                        //alert( "승인 처리 되었습니다." );
                        $(".reject_"+ctid).text("승인");                        
                    } else {
                        //alert( "반려 처리 되었습니다.\n사유: " + val );                        
                        $(".reject_"+ctid).text("반려");
                    }
                    $(".select_reject_msg_"+ctid).val(val).prop("selected",true);
                }
            },
            error: function(e) {}
        });
    }


    // 일괄반려 팝업에 대한 저장 버튼 프로세스
    function reject_all_apply_confirm( selected ) {

        if( !selected ) { alert("선택된 반려 사유가 없습니다.\n\n반려 사유를 선택하세요."); return; }
        if( !confirm("수급자 주문의 전체 상품에 위 내용 적용 하시겠습니까?") ) { return; }

        $("#ord_info input[name='ct_id[]']").each(function(idx){
            $("select[name='reject_msg[]']:eq("+idx+")").val( selected ).prop("selected", true);

            $.ajax({
                url: '/shop/ajax.eroumon_order_ctstatus_change.php', type: 'POST', dataType: 'json',
                data: {
                    "order_send_id": $('#ord_info #order_send_id').val(),
                    "ctid": $("#ord_info input[name='ct_id[]']:eq("+idx+")").val(),
                    "txt": selected
                },
                success: function(data) {
                    location.reload();
                }
            });

        });

        $('body').removeClass('modal-open'); 
        $('#reject_popup_box').hide();

    }

    function redirect_item(href) {
      location.href = href;
    }


	function tilko_call(a=1){
		$("#tilko").attr("src","/tilko_test.php?option="+a);
	}
	
	function tilko_download(){
		//alert("공인인증서 전송 프로그램 설치가 필요합니다. 설치 파일을 다운로드 합니다.");
		$("#tilko").attr("src","/Resources/setup.exe");
	}
	function cert_guide(){// 공인인증서 등록 절차 가이드 창 오픈
		var url = "/shop/pop.cert_guide.php";
		$('#cert_guide_popup_box iframe').attr('src', url);
		$('body').addClass('modal-open');
		$('#cert_guide_popup_box').show();
	}
		
	function pwd_insert(){// 공인인증서 비밀번호 입력 창 오픈
		var url = "/shop/pop.certmobilelogin.php";
		$('#cert_popup_box iframe').attr('src', url);
		$('body').addClass('modal-open');
		$('#cert_popup_box').show();
	}

	function ent_num_insert(){// 장기요양기관번호 입력 창 오픈
		var url = "/shop/pop.ent_num.php";
		$('#cert_ent_num_popup_box iframe').attr('src', url);
		$('body').addClass('modal-open');
		$('#cert_ent_num_popup_box').show();
	}
	function cert_pwd(pwd){
		var params = {
				  mode      : 'pwd'
				, Pwd       : pwd
			}
			$.ajax({
				type : "POST",            // HTTP method type(GET, POST) 형식이다.
				url : "/ajax.tilko.php",      // 컨트롤러에서 대기중인 URL 주소이다.
				data : params, 
				dataType: 'json',// Json 형식의 데이터이다.
				success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
					//location.reload();
                    $('.btn_so_sch').trigger('click');
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					pwd_insert();
				}
			});
	}


    <?php if( mb_substr($_pen["data"][0]["modifyDtm"],0,8) == date("Ymd") ) { ?>
        $('#order_processing_txt').show();
        $('#order_processing').css('backgroundColor', '#333');
        $('#order_processing').attr('disabled', false);
        $('#_inquiry_ok').val('Y');
    <?php } ?>


    <?php if( $_setp == 3 ) { ?>
        // 결제완료 처리 부분
        $('#order_from').css('backgroundColor', '#333');
        $('#order_from').attr('disabled', false);        
        $('#order_from_txt1').hide();        
        $('#order_from_txt2').show();
    <?php } ?>


    <?php if( $_setp == 5 ) { ?>
        // 출고완료 처리 부분        
        $("#simple_eform_iframe").load(function () {
            $('#order_contract').css('backgroundColor', '#333');
            $('#order_contract').attr('disabled', false);        
            $('#order_contract_txt1').hide();        
            $('#order_contract_txt2').show();
        });
    <?php } ?>

    <?php if( ($_setp >= 2) ) { ?>
        $('#order_processing_txt').hide();
    <?php } ?>

    <?php if( ($_setp != 10) && ($_setp >= 3) ) { ?>
        // 결제완료 이후~
        $("#order_processing_ProBar .thkc_Pro_container").clone().appendTo("#order_from_ProBar");
    <?php } ?>


    <?php if( $_setp >=4 ) { ?>
        // 주문완료 이후~
        $('#order_from').hide(); 
        $('#order_from_txt1').hide();        
        $('#order_from_txt2').hide();
    <?php } ?>

  
    <?php if( ($_setp != 10) && ($_setp >= 5) ) { ?>
        // 출고완료 이후~
        $("#order_processing_ProBar .thkc_Pro_container").clone().appendTo("#order_contract_ProBar");
    <?php } ?>  


    <?php if( $_setp >=6 ) { ?>
        // 계약서 작성완료 이후~
        $('#order_contract').hide(); 
        $('#order_contract_txt1').hide();        
        $('#order_contract_txt2').hide();
    <?php } ?>


    $(document).ready(function () {
        $('html, body').animate({

        <?php if( in_array($_od['od_status'], ['결제완료', '주문완료']) ) { ?>
            // 결제완료 이후 이로움1.0 주문완료까지 페이지 진입시 해당 화면으로 스크롤 됨.
            scrollTop: ($('#order_from_ProBar').offset().top-200)
        <?php } ?>

        <?php if( in_array($_od['od_status'], ['출고완료', '작성완료']) ) { ?>
            // 출고완료 이후 이로움1.0 계약서관련 처리가 완료될때까지 페이지 진입시 해당 화면으로 스크롤 됨.
            scrollTop: ($('#order_contract_ProBar').offset().top-200)
        <?php } ?>

        }, 'slow');

        <?php if( in_array($_od['od_status'], ['출고완료', '작성완료']) ) { ?>
        $("#simple_eform_iframe").on("load", function() {
            $('html, body').animate({
                // 출고완료 이후 이로움1.0 계약서관련 처리가 완료될때까지 페이지 진입시 해당 화면으로 스크롤 됨.
                scrollTop: ($('#order_contract_ProBar').offset().top-200)            
            }, 'slow');
        });
        <?php } ?>
    }); 

    </script>

<?php
    
    @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
    include_once('./_tail.php');
?>
