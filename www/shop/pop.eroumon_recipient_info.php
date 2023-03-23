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
    /* // 파일명 : /www/shop/pop.eroumon_recipient_info.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 받은 수급자에 대한 정보를 처리 하는 부분. */
    /*                "주문처리" 버튼을 눌르면 발생하는 이벤트에서 해당 페이지가 작동하며, 수급자 정보를 확인하여, 신규 수급자 등록 또는 기존 수급자를 가려낸다. */
    /*                 사업소 entId와 수급자 장기요양 번호로 WMDS에 매칭되어 있는 정보를 확인하고, 없을 경우 내부적으로 신규 등록 절차를 진행 한다. */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');

if ($member['mb_type'] !== 'default') {
    alert('사업소 회원만 접근 가능합니다.',G5_URL.'/bbs/login.php');
}



// -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==
// 
// -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==
// 수급자 WMDS 등록여부 확인 체크
$_penId = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penLtmNum' => 'L'.$_GET['penLtmNum']
));

$_PenNew = false;
if( $_penId['errorYN'] == "N"  ) {
    // API 조회 성공

    // 수급자 <--> 사업소 WMDS 연결 등록 여부 확인.
    if( is_array($_penId['data'][0])
        && ($_penId['data'][0]['penId']) 
        && ($_penId['data'][0]['penLtmNum']=="L".$_GET['penLtmNum']) 
        && ($_penId['data'][0]['penNm']==$_GET['penNm']) 
    ) {
        // 사업소와 수급자의 기존 연결 penId가 존재 한다면...
        header( "Location: /shop/pop.recipient_info.php?id=" . $_penId['data'][0]['penId'] . "&penNm=" . $_penId['data'][0]['penNm'] . "&penLtmNum=" . $_penId['data'][0]['penLtmNum'] );

    } else {
        // 사업소와 수급자가 처음 연결 된다면...
        $_PenNew = true;

        // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
        // SQL 처리 부분 시작
        // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

        $_od = sql_fetch("  SELECT * FROM `g5_shop_order_api` 
                            WHERE `order_send_id` = '" . $_GET['odid'] . "' 
                            AND `mb_id` = '" . $member['mb_id'] . "'
                            AND `od_penNm` = '" . $_GET['penNm'] . "'
                            AND `od_penLtmNum` = '" . $_GET['penLtmNum'] . "'

        ");

        // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
        // SQL 처리 부분 종료
        // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    }

} else {
    // API 조회 실패

}

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

// -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==
//
// -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==

?>

    <style>
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

    </style>


<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

<input type="hidden" name="penName" id="penName" value="<?=$_GET['penNm']?>">
<input type="hidden" name="penNum" id="penNum" value="<?=$_GET['penLtmNum']?>">




<form class="form-horizontal register-form">
    <input type="hidden" name="BDay" 					value="">
    <input type="hidden" name="SbaCd" 					value="">
    <input type="hidden" name="pro_rel" 				value="">
    <input type="hidden" name="pro_name" 				value="">
    <input type="hidden" name="pro_email" 				value="">
    <input type="hidden" name="pro_hp" 					value="<?=$_od['od_b_hp'];?>">
    <input type="hidden" name="pro_tel" 				value="">
    <input type="hidden" name="pro_zip" 				value="<?=$_od['od_zip1'].$_od['od_zip2'];?>">
    <input type="hidden" name="pro_addr1" 				value="<?=$_od['od_addr'];?>">
    <input type="hidden" name="pro_addr2" 				value="<?=$_od['od_addr2'];?>">
    <input type="hidden" name="tutorial" 				value="">
    <input type="hidden" name="penNm" 					value="">
    <input type="hidden" name="penLtmNum" 				value="">
    <input type="hidden" name="penConNum" 				value="<?=$_od['od_penConPnum'];?>">
    <input type="hidden" name="penConPnum" 				value="<?=$_od['od_penConNum'];?>">
    <input type="hidden" name="penExpiStDtm" 			value="">
    <input type="hidden" name="penExpiEdDtm" 			value="">
    <input type="hidden" name="penZip" 					value="<?=$_od['od_penZip1'].$_od['od_penZip2'];?>">
    <input type="hidden" name="penAddr" 				value="<?=$_od['od_penAddr'];?>">
    <input type="hidden" name="penAddrDtl" 				value="<?=$_od['od_penAddr2'];?>">
    <input type="hidden" name="penProNm" 				value="<?=$_od['od_b_name'];?>">
    <input type="hidden" name="penProConNum" 			value="<?=$_od['od_b_tel'];?>">
    <input type="hidden" name="penProConPnum" 			value="<?=$_od['od_b_hp'];?>">
    <input type="hidden" name="penProEmail" 			value="">
    <input type="hidden" name="penProRelEtc" 			value="">
    <input type="hidden" name="penProZip" 				value="">
    <input type="hidden" name="penProAddr" 				value="">
    <input type="hidden" name="penProAddrDtl" 			value="">
    <input type="hidden" name="penRecTypeTxt" 			value="">
    <input type="hidden" name="penRemark" 				value="이로움ON">
    <input type="hidden" name="entUsrId" 				value="<?=$member['mb_entId'];?>">
    <input type="hidden" name="penRecGraCd" 			value="">
    <input type="hidden" name="penTypeCd" 				value="">
    <input type="hidden" name="penPayRate" 				value="">
    <input type="hidden" name="penApplyStDtm" 			value="">
    <input type="hidden" name="penApplyEdDtm" 			value="">
    <input type="hidden" name="penSpare"         		value="">
    <input type="hidden" name="pro_type"         		value="">
    <input type="hidden" name="penGender"         		value="<?=( $_od['od_penGender']?($_od['od_penGender']):("남") );?>">
    <input type="hidden" name="penProTypeCd"         	value="<?=( ($_od['relation_code']=='0')?("00"):("0".$_od['relation_code']) );?>">
    <input type="hidden" name="penCnmTypeCd"         	value="">
    <input type="hidden" name="caCenYn "         		value="">
    <input type="hidden" name="penProBirth1" 			value="<?=mb_substr($_od['od_birth'],0,4);?>">
    <input type="hidden" name="penProBirth2" 			value="<?=mb_substr($_od['od_birth'],4,2);?>">
    <input type="hidden" name="penProBirth3" 			value="<?=mb_substr($_od['od_birth'],6,2);?>">
    <input type="hidden" name="pro_birth1" 				value="">
    <input type="hidden" name="pro_birth2" 				value="">
    <input type="hidden" name="pro_birth3" 				value="">
    <input type="hidden" name="pro_rel_type"			value="">
    <input type="hidden" name="penProRel" 				value="<?=$_od['relation_code'];?>">
    <input type="hidden" name="penRecTypeCd"			value="01">

    <?php $i=0; foreach ($sale_ids as $key => $val) { $i++; ?>
    <input type="hidden" name="<?=$val?>" id="sale_product_id<?=$i?>" value="<?=$val?>" class="_check">
    <?php } ?>

    <?php $i=0; foreach ($rent_ids as $key => $val) { $i++; ?>
    <input type="hidden" name="<?=$val?>" id="rental_product_id<?=$i?>" value="<?=$val?>" class="_check">
    <?php } ?>

</form>


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

    <iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false" height="0" width="0"></iframe>

    <!-- 로딩 -->
    <div id="loading" style="display: none;">
    <div class="loading_modal">
        <p>수급자 정보를 확인중 입니다.</p>
        <p>잠시만 기다려주세요.</p>
        <img src="/shop/img/loading.gif" alt="loading">
    </div>
    </div>



<script>

    <?php
        if( $_PenNew ){ echo(" $('#loading').show(); "); }
    ?>

    var name = $("#penName").val();
    var num = $("#penNum").val();

    var penNm = '';
    var penNm_list = [];

    var rep_raw = [];
    var rep_info = [];

    var penPayRate ="";

    $.ajax('ajax.recipient.inquiry.php', {
    type: 'POST',  // http method
    data: { id : num,rn : name },  // data to submit
    success: function (data, status, xhr) {

        let sale_ll = [];
        let rent_ll = [];

        rep_raw = data['data'];
        let rep_list = data['data']['recipientContractDetail']['Result'];                
        rep_info = rep_list['ds_welToolTgtList'][0];

        penPayRate = rep_info['REDUCE_NM'] == '일반' ? '15%' : 
                        rep_info['REDUCE_NM'] == '기초' ? '0%' : 
                        rep_info['REDUCE_NM'] == '의료급여' ? '6%' : 
                        (rep_info['SBA_CD'].split('(')[1].substr(0, rep_info['SBA_CD'].split('(')[1].length-1));

        let rem_amount = 1600000;
        let today = new Date();
        var st_date, ed_date;
        if(rep_list['ds_toolPayLmtList'] != null && rep_list['ds_toolPayLmtList'].length>0){
            for(var i =0; i< rep_list['ds_toolPayLmtList'].length;i++){                    
                st_date = new Date(setDate(rep_list['ds_toolPayLmtList'][i]['APDT_FR_DT']));
                ed_date = new Date(setDate(rep_list['ds_toolPayLmtList'][i]['APDT_TO_DT']));
                if(st_date < today && ed_date > today){
                    rem_amount = rep_list['ds_toolPayLmtList'][i]['REMN_AMT']; break;
                }
            }
        }

        let applydtm = '';
        var appst, apped;
        for(var ind = 0; ind < rep_list['ds_toolPayLmtList'].length; ind++){
            var appst = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(6,2));
            var apped = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(6,2));

            if(today < apped && today > appst){
                applydtm = appst.toISOString().split('T')[0]+' ~ '+apped.toISOString().split('T')[0];
                break;
            }

            if(ind == rep_list['ds_toolPayLmtList'].length-1){
                applydtm = rep_list['ds_toolPayLmtList'][0]['APDT_FR_DT']+' ~ '+rep_list['ds_toolPayLmtList'][0]['APDT_TO_DT'];
            }
        }

        let pd_list = JSON.parse(rep_raw['recipientToolList'])['Result'];
        let pd_keys = ['ds_payPsblLnd1','ds_payPsblLnd2','ds_payPsbl1','ds_payPsbl2'];

        for(var i = 0; i < Object.keys(pd_list).length; i++){
            let pd_type = pd_keys[i].substr(0, pd_keys[i].length-1) == 'ds_payPsbl'?'sale':'rent';             
            for(var ind = 0; ind < pd_list[pd_keys[i]].length; ind++){
                let pd_name = pd_list[pd_keys[i]][ind]['WIM_ITM_CD'].replace(' ','');
                eval(pd_type + '_ll')[pd_name] = pd_keys[i].substr(pd_keys[i].length-1, 1) == '2'?0:1;   
            }
        }

        $.post('./ajax.inquiry_log.php', { data: { ent_id : "<?=$member['mb_id']?>", ent_nm : "<?=$member['mb_name']?>", pen_id : num, pen_nm : name, esultMsg : status,occur_page : "pop.eroumon_recipient_info.php" } }, 'json')
        .fail(function($xhr) { var data = $xhr.responseJSON; });

        $.post('./ajax.my.recipient.hist.php', { data: rep_raw, status: false }, 'json')
        .fail(function($xhr) { var data = $xhr.responseJSON; alert("계약정보 업데이트에 실패했습니다!"); });

        $.ajax({
            type: 'POST',
            url: './ajax.macro_request.php',
            data: {
                status: "U",
                mb_id: "<?=$member['mb_id'];?>",
                name: name,
                num: "<?=$_GET['penLtmNum'];?>",
                birth: setDate(rep_info['BDAY']),
                grade: rep_info['LTC_RCGT_GRADE_CD']+"등급",
                type: rep_info['REDUCE_NM'],
                percent: penPayRate,
                penApplyDtm: st_date.toISOString().split('T')[0]+' ~ '+ed_date.toISOString().split('T')[0],
                penExpiDtm: rep_info['RCGT_EDA_DT'],
                rem_amount: rem_amount,
                item_data:  JSON.parse(rep_raw['recipientPurchaseRecord'])
            },
            dataType: 'json'
        })
        .done(function(result) {


            $(".register-form input[name='BDay']").val(rep_info['BDAY']);
            $(".register-form input[name='SbaCd']").val(rep_info['SBA_CD']);
            $(".register-form input[name='tutorial']").val("0");
            $(".register-form input[name='penNm']").val(rep_info['FNM']);
            $(".register-form input[name='penLtmNum']").val(rep_info['LTC_MGMT_NO']);
            //$(".register-form input[name='penConNum']").val('');
            //$(".register-form input[name='penConPnum']").val('');
            $(".register-form input[name='penExpiStDtm']").val(rep_info['RCGT_EDA_FR_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(6,2));
            $(".register-form input[name='penExpiEdDtm']").val(rep_info['RCGT_EDA_TO_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(6,2));
            //$(".register-form input[name='penZip']").val('');
            //$(".register-form input[name='penAddr']").val('');
            //$(".register-form input[name='penAddrDtl']").val('');
            //$(".register-form input[name='penProNm']").val('');
            //$(".register-form input[name='penProConNum']").val('');
            //$(".register-form input[name='penProConPnum']").val('');
            //$(".register-form input[name='penProEmail']").val('');
            //$(".register-form input[name='penProRelEtc']").val('');
            //$(".register-form input[name='penProZip']").val('');
            //$(".register-form input[name='penProAddr']").val('');
            //$(".register-form input[name='penProAddrDtl']").val('');
            //$(".register-form input[name='penRecTypeTxt']").val('');
            //$(".register-form input[name='penRemark']").val('');
            //$(".register-form input[name='entUsrId']").val('');
            $(".register-form input[name='penRecGraCd']").val(rep_info['LTC_RCGT_GRADE_CD']);
            $(".register-form input[name='penTypeCd']").val(rep_info['REDUCE_NM']);
            $(".register-form input[name='penPayRate']").val(penPayRate);
            $(".register-form input[name='penApplyStDtm']").val(applydtm.split(' ~ ')[0]);
            $(".register-form input[name='penApplyEdDtm']").val(applydtm.split(' ~ ')[1]);
            //$(".register-form input[name='penSpare']").val('');
            $(".register-form input[name='pro_type']").val('01');
            //$(".register-form input[name='penGender']").val('<?=$_od['od_penGender']?>');
            //$(".register-form input[name='penProTypeCd']").val('99');
            $(".register-form input[name='penCnmTypeCd']").val('00');
            //$(".register-form input[name='caCenYn']").val('');
            //$(".register-form input[name='penProBirth1']").val('');
            //$(".register-form input[name='penProBirth2']").val('');
            //$(".register-form input[name='penProBirth3']").val('');
            //$(".register-form input[name='pro_birth1']").val('');
            //$(".register-form input[name='pro_birth2']").val('');
            //$(".register-form input[name='pro_birth3']").val('');
            //$(".register-form input[name='pro_rel_type']").val('');
            //$(".register-form input[name='penProRel']").val('');
            //$(".register-form input[name='penRecTypeCd']").val('');

            
            var sale_ids = <?= json_encode($sale_ids);?>              
            var rent_ids = <?= json_encode($rent_ids);?>

            for(var ind = 0; ind < Object.keys(sale_ll).length; ind++){
                if(Object.keys(sale_ll)[ind] == '미끄럼방지용품'){
                    $("input[name='"+sale_ids['미끄럼방지용품(양말)']+"']").removeClass( '_check' );
                    $("input[name='"+sale_ids['미끄럼방지용품(매트)']+"']").removeClass( '_check' );
                } else {
                    $("input[name='"+sale_ids[Object.keys(sale_ll)[ind]]+"']").removeClass( '_check' );
                }
            }

            for(var idx = 0; idx < Object.keys(rent_ll).length; idx++){
                $("input[name='"+rent_ids[Object.keys(rent_ll)[idx]]+"']").removeClass( '_check' );
            }

            $("._check").val(""); // class가 "_check"인 요소의 자식 요소를 모두 삭제함.

            recipient_write();

            alert(data['message']);
            //window.location.reload(true);

        })
        .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
        });
        
    },
    error: function (jqXhr, textStatus, errorMessage) {
        var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
        $('#loading').hide();
        //alert(errMSG);
        //인증서 업로드 추가 영역 
        if(errMSG == "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다." ) {
            
            alert(errMSG);
            $.post('./ajax.inquiry_log.php', {
                data: { ent_id : "<?=$member['mb_id']?>",
                        ent_nm : "<?=$member['mb_name']?>",
                        pen_id : num,
                        pen_nm : name,
                        resultMsg : "fail",
                        occur_page : "pop.eroumon_recipient_info.php",
                        err_msg:errMSG
                }
            }, 'json')
            .fail(function($xhr) { var data = $xhr.responseJSON; });
            parent.$('#item_popup_box').click();

        }else if(jqXhr['responseJSON']["data"]['err_code'] == "3"){
            alert("등록된 인증서가 사용 기간이 만료 되었습니다.<?=($mobile_yn == 'Mobile')?' 컴퓨터에서':'';?> 공인인증서를 재등록 해 주세요.");
            parent.$('#item_popup_box').click();
            <?php if($mobile_yn == 'Pc'){?>tilko_call('1');<?php }?>
        }else if(jqXhr['responseJSON']["data"]['err_code'] == "1"){
            alert("등록된 인증서가 없습니다.<?=($mobile_yn == 'Mobile')?' 컴퓨터에서':'';?> 공인인증서를 등록 해 주세요.");
            parent.$('#item_popup_box').click();
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
                data: { ent_id : "<?=$member['mb_id']?>",
                    ent_nm : "<?=$member['mb_name']?>",
                    pen_id : num,
                    pen_nm : name,
                    resultMsg : "fail",
                    occur_page : "pop.eroumon_recipient_info.php",
                    err_msg:errMSG
                }
            }, 'json')
            .fail(function($xhr) {
                var data = $xhr.responseJSON;
                alert("로그 저장에 실패했습니다!");
            });
            parent.$('#item_popup_box').click();
        }else if(jqXhr['responseJSON']["data"]['err_code'] == "5"){
            ent_num_insert();
        }
        
        // 인증서 업로드 추가 영역 끝
        return false;
    }
    });


    $('#cert_popup_box').click(function() { $('body').removeClass('modal-open'); $('#cert_popup_box').hide(); });
    $('#cert_guide_popup_box').click(function() { $('body').removeClass('modal-open'); $('#cert_guide_popup_box').hide(); });
    $('#cert_ent_num_popup_box').click(function() { $('body').removeClass('modal-open'); $('#cert_ent_num_popup_box').hide(); });


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
					location.reload();
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					pwd_insert();
				}
			});
	}




    function setDate(str_date){ return str_date.substr(0,4)+'-'+str_date.substr(4,2)+'-'+str_date.substr(6,2);}
	function makeComma(str) {str = String(str);return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');}




    // 수급자 등록 함수.
    function recipient_write() {

        var penLtmNum = $("#penLtmNum").val();
        var penJumin =  $("input[name='BDay']").val().substr(2, 6);
        var penBirth = $("input[name='BDay']").val().substr(0,4)+'-'+$("input[name='BDay']").val().substr(4,2)+'-'+$("input[name='BDay']").val().substr(6,2);

        var pentype = $("input[name='SbaCd']").val();
        var penTypeCd = ''; //코드 일반15:00/감경9:01/감경6:02/의료6:03/기초0:04;
        var penTypeNm = ''; //형식 일반 15%, 감경 9%, 기초 0%
        if(pentype.substr(0, 2) == '일반' || pentype.substr(0, 2) == '의료' || pentype.substr(0, 2) == '기초'){ //일반의료기초
        var percnt = pentype.substr(0, 2) == '일반'? ' 15%' : pentype.substr(0, 2) == '의료'? ' 6%' : ' 0%';

        penTypeNm = pentype.substr(0, 2) + percnt;
        penTypeCd = pentype.substr(0, 2) == '일반'? '00' : pentype.substr(0, 2) == '의료'? '03' : '04';
        } else { //감경
        penTypeNm = pentype.replace('(',' ').replace(')','');
        penTypeCd = pentype.substr(3, 1) == '6'? '02' : '01';
        }

        var recgrd = $("input[name='penRecGraCd']").val().replace(/[^0-9]/g, '') == '' ? '0' : $("input[name='penRecGraCd']").val().replace(/[^0-9]/g, '');
        var penRecGraNm = $("input[name='penRecGraCd']").val();
        var penRecGraCd = '0'+recgrd;

        var penSpare = $("input[name='penSpare']").val();
        if(penSpare != '1') {

            if ($('#penLtmNumResultVal').val() == 0) {
                alert('이미 등록된 수급자 입니다.');  
                $(penLtmNum).focus(); 
                return false;
            }
        }

        var penProBirth = $(".register-form input[name='penProBirth1']").val()+'-'
        + $(".register-form input[name='penProBirth2']").val()+'-'
        + $(".register-form input[name='penProBirth3']").val();

        if(penBirth.length !== 10){ penBirth = ''; }
        if(penProBirth.length !== 10){ penProBirth = ''; }

        var pros = [];
        $('.panel_pro_add').each(function() {
            var pro_birth = [$(this).find('input[name="pro_birth1"]').val(), $(this).find('input[name="pro_birth2"]').val(), $(this).find('input[name="pro_birth3"]').val()].join('-');
            if(pro_birth.length != 10) pro_birth = '';

            pros.push({
                pro_type: $(this).find('input[name="pro_type"]').val(),
                pro_rel_type: $(this).find('input[name="pro_rel_type"]').val(),
                pro_rel: $(this).find('input[name="pro_rel"]').val(),
                pro_name: $(this).find('input[name="pro_name"]').val(),
                pro_birth: '<?=$_od['od_birth']?>',
                pro_email: $(this).find('input[name="pro_email"]').val(),
                pro_hp: $(this).find('input[name="pro_hp"]').val(),
                pro_tel: $(this).find('input[name="pro_tel"]').val(),
                pro_zip: $(this).find('input[name="pro_zip"]').val(),
                pro_addr1: $(this).find('input[name="pro_addr1"]').val(),
                pro_addr2: $(this).find('input[name="pro_addr2"]').val()
            });
        });

        loading = true;

        $.post('./ajax.my.recipient.write.php', {
        tutorial : $(".register-form input[name='tutorial']").val(),
        penNm : $(".register-form input[name='penNm']").val(),
        penLtmNum : $(".register-form input[name='penLtmNum']").val(),
        penRecGraCd: penRecGraCd,
        penRecGraNm: penRecGraNm,
        penGender : $(".register-form input[name='penGender']").val(),
        penBirth : penBirth,
        penJumin : penJumin,
        penTypeCd : penTypeCd,
        penTypeNm: penTypeNm,
        penConNum : $(".register-form input[name='penConNum']").val(),
        penConPnum : $(".register-form input[name='penConPnum']").val(),
        penExpiStDtm : $(".register-form input[name='penExpiStDtm']").val(),
        penExpiEdDtm : $(".register-form input[name='penExpiEdDtm']").val(),
        penAppStDtm1 : $(".register-form input[name='penApplyStDtm']").val(),
        penAppEdDtm1 : $(".register-form input[name='penApplyEdDtm']").val(),
        penAppStDtm2 : $(".register-form input[name='penApplyStDtm']").val(),
        penAppEdDtm2 : $(".register-form input[name='penApplyEdDtm']").val(),
        penAppStDtm3 : $(".register-form input[name='penApplyStDtm']").val(),
        penAppEdDtm3 : $(".register-form input[name='penApplyEdDtm']").val(),
        penRecDtm : "0000-00-00",
        penAppDtm : "0000-00-00",
        penZip : $(".register-form input[name='penZip']").val(),
        penAddr : $(".register-form input[name='penAddr']").val(),
        penAddrDtl : $(".register-form input[name='penAddrDtl']").val(),
        penProTypeCd : $('.register-form input[name="penProTypeCd"]').val(),
        penProNm : $(".register-form input[name='penProNm']").val(),
        penProBirth : penProBirth,
        penProRel : $(".register-form input[name='penProRel']").val(),
        penProConNum : $(".register-form input[name='penProConNum']").val(),
        penProConPnum : $(".register-form input[name='penProConPnum']").val(),
        penProEmail : $(".register-form input[name='penProEmail']").val(),
        penProRelEtc : $(".register-form input[name='penProRelEtc']").val(),
        penProZip : $(".register-form input[name='penProZip']").val(),
        penProAddr : $(".register-form input[name='penProAddr']").val(),
        penProAddrDtl : $(".register-form input[name='penProAddrDtl']").val(),
        penCnmTypeCd : $(".register-form input[name='penCnmTypeCd']").val(),
        penRecTypeCd : $(".register-form input[name='penRecTypeCd']").val(),
        penRecTypeTxt : $(".register-form input[name='penRecTypeTxt']").val(),
        penRemark : $(".register-form input[name='penRemark']").val(),
        entUsrId : $(".register-form input[name='entUsrId']").val(),
        caCenYn : $(".register-form input[name='caCenYn']").val(),
        penSpare: penSpare,
        pros: pros,
        page: "<?=get_text($_GET['page'])?>",
        uuid: "<?=get_text($_GET['uuid'])?>"
        }, 'json')
        .done(function(result) {      
        // macro_request 상태 업데이트
        $.post('./ajax.macro_update.php', {
            mb_id : "<?=$member['mb_id']?>",
            recipient_name : $("input[name='penNm']").val(),
            recipient_num : "<?=$_GET['penLtmNum'];?>"
        }, 'json')
        .done(function(result) {
            console.log(result);
        })
        .fail(function($xhr) {
            var data = $xhr.responseJSON;
            if(data['message'] == "no data"){
            $.ajax({
                type: 'POST',
                url: './ajax.macro_request.php',
                data: {
                    mb_id: "<?=$member['mb_id']?>",
                    name: $(".register-form input[name='penNm']").val(),
                    num: "<?=$_GET['penLtmNum'];?>",
                    birth: penBirth,
                    grade: $(".register-form input[name='penRecGraCd']").val(),
                    type: $(".register-form input[name='penTypeCd']").val(),
                    percent: $(".register-form input[name='penPayRate']").val(),
                    penApplyDtm: $(".register-form input[name='penApplyStDtm']").val()+' ~ '+$(".register-form input[name='penApplyEdDtm']").val(),
                    penExpiDtm: $(".register-form input[name='penExpiStDtm']").val()+' ~ '+$(".register-form input[name='penExpiEdDtm']").val(),
                    rem_amount: rep_info['LIMIT_AMT'],
                    item_data:  JSON.parse(rep_raw['recipientPurchaseRecord'])
                },
                dataType: 'json'
            })
            .done(function(result) {
                // 이로움 DB에 계약정보 insert
                $.post('./ajax.my.recipient.hist.php', { data: rep_raw, status: true, penLtmNum: "L<?=$_GET['penLtmNum']?>" }, 'json')
                .fail(function($xhr) {
                    var data = $xhr.responseJSON;
                    alert("계약정보 업데이트에 실패했습니다!");
                })
                .always(function() {
                    loading = false;
                });
            });
            } else {
            alert("상태 업데이트에 실패했습니다!");
            }
        })
        .always(function() {
            loading = false;
        });

        var data = result.data;

        if(data.isSpare) {
            //return window.location.href = "./my_recipient_view.php?id="+data.penId;
            //window.location.reload(true);
        }

        var itemList=[];
        //판매품목 값 넣기
        for(var i=1; i<14; i++) {
            var $sale_product_id = $('#sale_product_id'+i);
            if($sale_product_id.val()) { itemList.push($sale_product_id.val()); }
        }
        //대여품목 값 넣기
        for(var i=0; i<8; i++) {
            var $rental_product_id = $('#rental_product_id'+i);
            if($rental_product_id.val()) { itemList.push($rental_product_id.val()); }
        }

        $.post('./ajax.my.recipient.setItem.php', {
            penId: data.penId,
            itemList: itemList
        }, 'json')
        .done(function(result) {
            if(result.errorYN == "Y") {
                alert(result.message);
            } else {
                alert('완료되었습니다');
                window.location.reload(true);
            }
        })
        .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
        })
        .always(function() {
            loading = false;
        });

        })
        .fail(function($xhr) {
        loading = false;
        var data = $xhr.responseJSON;
        alert(data && data.message);
        });

    }
</script>