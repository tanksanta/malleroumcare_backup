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
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2022 THKC Co,Ltd.  All rights reserved. */
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
    /* // 파일명 : /www/adm/shop_admin/payment_OnlineBilling.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금청구서 관리를 위한 관리자 리스트 페이지 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once('./_common.php');
auth_check($auth[$sub_menu], 'r');

// 23.01.09 : 서원 - 결제관련 코드값 치환
function txt_pay_ENUM( $val ) {

    $_result = "";
  
    switch($val) {    
  
      case('phone'): $_result = "휴대폰"; break;
      case('card'): $_result = "카드"; break;
      case('bank'): $_result = "계좌이체"; break;
      case('vbank'): $_result = "가상계좌"; break;
      case('easy'): $_result = "간편"; break;
      case('easy_rebill'): $_result = "간편자동"; break;
      case('card_rebill'): $_result = "카드자동"; break;
      case('kakaopay'): $_result = "카카오페이"; break;
      case('naverpay'): $_result = "네이버페이"; break;
      case('payco'): $_result = "페이코"; break;
      case('toss'): $_result = "토스"; break;
      case('easy_card'): $_result = "간편카드"; break;
      case('easy_card_rebill'): $_result = "간편카드자동"; break;
      case('auth'): $_result = "본인인증"; break;
      case('digital_card'): $_result = "디지털카드"; break;
      case('digital_bank'): $_result = "디지털계좌이체"; break;
      case('digital_card_rebill'): $_result = "디지털카드자동"; break;
  
      default : $_result = "-"; break;
    }
  
    return $_result;
}


$g5['title'] = '대금청구서관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$colspan = 11;

?>

<script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>

<style>
.local_sch01 button { width:80px; height: 26px; padding: 0 5px; border: 0; background: #9eacc6; color: #fff; }
.local_sch select { width: 120px;}
</style>

<?php
    $_billing = json_decode( $default['de_paymenet_billing_OnOff'], TRUE );
?>

<div class="local_sch02 local_sch">

        <table style="">
            <tr>
                <td style="width:160px; height: 45px; text-align:center;"><strong>설정</strong></td>
                <td style="padding-left:25px;">
                        <div>
                            <span class="linear_span"> 청구결제 활성화 : </span>
                            <input type="radio" id="radio_onoff" name="OnOff" value="Y"<?=($_billing['OnOff'] == 'Y')?(' checked="checked"'):('');?>><label for="btn_button_on"> On</label>
                            <input type="radio" id="radio_onoff" name="OnOff" value="N"<?=($_billing['OnOff'] == 'N')?(' checked="checked"'):('');?>><label for="btn_button_off"> Off</label>
                        </div>
                        
                        <div>
                            <span class="linear_span"> 청구결제 활성화 기간(매월) : </span>
                            <select name="start_dt" id="select_start_dt"><?php for( $i=1 ; $i<=31 ; $i++ ) { ?><option value="<?=$i;?>" <?=($i==$_billing['start_dt'])?"selected":""?>> <?=$i;?>일 </option><?php } ?></select>
                            ~
                            <select name="end_dt" id="select_end_dt"><?php for( $i=1 ; $i<=31 ; $i++ ) { ?><option value="<?=$i;?>" <?=($i==$_billing['end_dt'])?"selected":""?>> <?=$i;?>일 </option><?php } ?></select>
                        </div>
                    </td>
                <td style=" text-align:center;">
                    <input type="button" value="설정저장" class="btn_submit" id="" onclick="Payment_Set_Billing_Setting();" style="width:100px; height:35px; background: #89a174 !important;">
                </td>
            </tr>
        </table>

</div>


<form name="onlinebilling" id="onlinebilling" method="get" onsubmit="return onlinebilling_submit_function(this);">

    <div class="local_sch01 local_sch">
        <div class="sch_last">
            <table style="">
                <tr>
                    <td style="width:160px; height: 45px; text-align:center;"><strong>검색기간<br />(결제일)</strong></td>
                    <td style="padding-left:25px;">
                        <select name="select_date" id="bn_position">
                            <option value="create_dt" <?=($select_date=="create_dt")?"selected":""?>> 등록일 </option>
                            <option value="pay_confirm_dt" <?=($select_date=="pay_confirm_dt")?"selected":""?>> 결제일 </option>
                        </select>
                        <input type="text" id="fr_date"  name="fr_date" value="<?=$fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                        <input type="text" id="to_date"  name="to_date" value="<?=$to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
                        <button type="button" onclick="javascript:set_date('전체');">전체</button>
                        <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
                        <button type="button" onclick="javascript:set_date('어제');">어제</button>
                        <button type="button" onclick="javascript:set_date('이번주');">일주일</button>
                        <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
                        <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
                    </td>
                </tr>
                <tr>
                    <td style="height: 45px; text-align:center;"><strong>검색조건</strong></td>
                    <td style="padding-left:25px;">

                        <div style="float:left; padding-right:20px;">
                            <strong>카드종류</strong><br />
                            <select name="select_card_company" id="bn_position">
                                <option value="" <?=($select_card_company=="")?"selected":""?>> 전체 </option>  
                                <option value="BC" <?=($select_card_company=="BC")?"selected":""?>> BC </option>
                                <option value="국민" <?=($select_card_company=="국민")?"selected":""?>> 국민 </option>
                                <option value="하나" <?=($select_card_company=="하나")?"selected":""?>> 하나 </option>
                                <option value="삼성" <?=($select_card_company=="삼성")?"selected":""?>> 삼성 </option>
                                <option value="신한" <?=($select_card_company=="신한")?"selected":""?>> 신한 </option>
                                <option value="현대" <?=($select_card_company=="현대")?"selected":""?>> 현대 </option>
                                <option value="롯데" <?=($select_card_company=="롯데")?"selected":""?>> 롯데 </option>
                                <option value="신세계한미" <?=($select_card_company=="신세계한미")?"selected":""?>> 신세계한미 </option>
                                <option value="시티" <?=($select_card_company=="시티")?"selected":""?>> 시티 </option>
                                <option value="농협" <?=($select_card_company=="농협")?"selected":""?>> 농협 </option>
                                <option value="수협" <?=($select_card_company=="수협")?"selected":""?>> 수협 </option>
                                <option value="신협" <?=($select_card_company=="신협")?"selected":""?>> 신협 </option>
                                <option value="우리" <?=($select_card_company=="우리")?"selected":""?>> 우리 </option>
                                <option value="광주" <?=($select_card_company=="광주")?"selected":""?>> 광주 </option>
                                <option value="제주" <?=($select_card_company=="제주")?"selected":""?>> 제주 </option>
                                <option value="전북" <?=($select_card_company=="전북")?"selected":""?>> 전북 </option>
                                <option value="기업" <?=($select_card_company=="기업")?"selected":""?>> 기업 </option>
                                <option value="VISA" <?=($select_card_company=="VISA")?"selected":""?>> VISA </option>
                                <option value="마스터" <?=($select_card_company=="마스터")?"selected":""?>> 마스터 </option>
                                <option value="다이너스" <?=($select_card_company=="다이너스")?"selected":""?>> 다이너스 </option>
                                <option value="AMX" <?=($select_card_company=="AMX")?"selected":""?>> AMX </option>
                                <option value="JCB" <?=($select_card_company=="JCB")?"selected":""?>> JCB </option>
                                <option value="DISCOVER" <?=($select_card_company=="DISCOVER")?"selected":""?>> DISCOVER </option>
                                <option value="우체국" <?=($select_card_company=="우체국")?"selected":""?>> 우체국 </option>
                                <option value="새마을금고" <?=($select_card_company=="새마을금고")?"selected":""?>> 새마을금고 </option>
                                <option value="은련" <?=($select_card_company=="은련")?"selected":""?>> 은련 </option>
                                <option value="카카오뱅크" <?=($select_card_company=="카카오뱅크")?"selected":""?>> 카카오뱅크 </option>
                                <option value="케이뱅크" <?=($select_card_company=="케이뱅크")?"selected":""?>> 케이뱅크 </option>
                                <option value="페이코" <?=($select_card_company=="페이코")?"selected":""?>> 페이코 </option>
                                <option value="저축은행" <?=($select_card_company=="저축은행")?"selected":""?>> 저축은행 </option>
                            </select>
                        </div>

                        <div style="float:left; padding-right:20px;">
                            <strong>거래상태</strong><br />
                            <select name="select_status" id="bn_position">
                                <option value="" <?=($select_status=="")?"selected":""?>> 전체 </option>
                                <option value="미결제" <?=($select_status=="미결제")?"selected":""?>> 미결제 </option>    
                                <option value="결제완료" <?=($select_status=="결제완료")?"selected":""?>> 결제완료 </option>
                                <option value="결제취소" <?=($select_status=="결제취소")?"selected":""?>> 결제취소 </option>
                                <option value="관리자취소" <?=($select_status=="관리자취소")?"selected":""?>> 관리자취소 </option>
                            </select>
                        </div>

                        <div style="float:left; padding-right:20px;">
                            <strong>할부구분</strong><br />
                            <select name="select_card_quota" id="bn_position">
                                <option value="" <?=($select_card_quota=="")?"selected":""?>> 전체 </option>                            
                                <option value="일시불" <?=($select_card_quota=="일시불")?"selected":""?>> 일시불 </option>    
                                <option value="할부" <?=($select_card_quota=="할부")?"selected":""?>> 할부 </option>
                            </select>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td style="height: 45px; text-align:center;"><strong>검색어</strong></td>
                    <td style="padding-left:25px;">
                        거래처명: 
                        <input type="text" name="stx" size="20" value="<?php echo stripslashes($stx); ?>" id="sch_word" class="frm_input" placeholder="">
                        <input type="submit" value="검색" class="btn_submit" id="onlinebilling_submit" style="width:100px; height:26px;">
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>


<div style="padding: 0px 20px; height:40px; margin-bottom: 10px;">
    <input type="button" value="엑셀다운로드" class="btn btn_02" id="ExcelDownload" style="width:100px;height:40px;font-size:12px;cursor:pointer; float:right;">
    <input type="button"  onclick="return excelform('/adm/shop_admin/popup.payment_OnlineBilling_Excel.php');" value="온라인 결제용 대금 청구서 업로드" class="btn btn_03" id="" style="width:250px;height:40px;font-size:14px;cursor:pointer; float:left;">
</div>


<div class="tbl_wrap tbl_head01">
    <table>
    <thead>
    <tr>
        <th scope="col" style="width:40px;">No.</th>
        <th scope="col" style="width:100px;">거래일자</th>
        <th scope="col">거래처명</th>
        <th scope="col" style="width:100px;">거래처코드</th>
        <th scope="col">거래금액</th>
        <th scope="col">거래종류</th>
        <th scope="col">거래상태</th>
        <th scope="col" style="width:70px;">할부구분</th>
        <th scope="col" style="width:100px;">등록일</th>
        <th scope="col" style="min-width:100px;">기타</th>
        <th scope="col" style="width:80px;">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql_common = ("    FROM 
                            payment_billing_list bl
                            LEFT OUTER JOIN
                            payment_api_request par ON par.id = (
                                SELECT MAX(id)
                                FROM payment_api_request par2
                                WHERE par2.bl_id = bl.bl_id
                                ORDER BY par2.create_dt
                                LIMIT 1
                            )
                ");

    // 날짜검색
    if ($fr_date && $to_date) {
        $where[] = "bl.".$select_date . " between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    } else {        
        $where[] = "bl.create_dt between '".date("Y-m-d",strtotime("-90 day", time()))."' and '".date("Y-m-d",strtotime("+1 day", time()))."' ";
    }

    // 검색어(거래처명)
    if ($stx) {
        $where[] = " ( `mb_nm` LIKE '%{$stx}%' OR `mb_bnm` LIKE '%{$stx}%' ) ";
    }

    // 카드사명
    if( $select_card_company ) { 
        $where[] = " ( `card_company` LIKE '{$select_card_company}%' ) ";
    }

    // 결제상태
    if( $select_status ) {

        if( $select_status == "미결제" ) {
            $where[] = " ( pay_confirm_id IS NULL OR pay_confirm_id = '' ) AND ( pay_confirm_receipt_id IS NULL OR pay_confirm_receipt_id = '' ) AND ( billing_status IS NULL OR billing_status = '' ) ";
        }
        else if( $select_status == "결제완료" ) {
            $where[] = " ( `status_locale` = '결제완료' ) ";
        }
        else if( $select_status == "결제취소" ) {
            $where[] = " ( `status_locale` = '결제취소완료' ) ";
        }
        else if( $select_status == "관리자취소" ) {
            $where[] = " ( `billing_yn` = 'N' ) ";
        }
    }
    
    // 할부구분
    if( $select_card_quota ) { 
        
        if( $select_card_quota == "일시불" ) {
            $where[] = " ( `card_quota` = '00' ) ";
        }
        else if( $select_card_quota == "할부" ) {
            $where[] = " ( `card_quota` <> '00' ) ";
        }
    }
    
    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }

    $sql = " SELECT count(bl.bl_id) as cnt
                {$sql_common}
                {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $sql = " SELECT bl.*, par.method_symbol, par.card_company, par.card_quota,  par.status_locale
                {$sql_common}
                {$sql_search}
                ORDER BY bl.pay_confirm_dt DESC, bl.create_dt DESC
                LIMIT {$from_record}, {$rows} ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td scope="col" style="text-align:center;"><?=($i+$from_record+1);?></td>
        <td scope="col" style="text-align:center;"><?=str_replace(" ","<br />",$row['pay_confirm_dt']);?></td>
        <td scope="col"><?=$row['mb_bnm'];?></td>
        <td scope="col"><?=$row['mb_thezone'];?></td>
        <td scope="col" style="text-align:right;"><?=number_format($row['price_total']);?></td>
        <td scope="col" style="text-align:center;"><?=txt_pay_ENUM($row['method_symbol'])?><?=( ($row['method_symbol']=="card")?(" (".$row['card_company'].")"):("-") )?></td>
        <td scope="col" style="text-align:center;"><?=($row['billing_yn']=="Y")?($row['status_locale']):$row['billing_status']?></td>
        <td scope="col" style="text-align:center;"><?=( ($row['method_symbol']=="card")?(($row['card_quota']=="00")?("일시불"):("할부(".$row['card_quota'].")")):("-") )?></td>
        <td scope="col" style="text-align:center;"><?=str_replace(" ","<br />",$row['create_dt']);?></td>
        <td scope="col" style="text-align:center;">
            <?php if( ($row['error_code'])&&(!$row['pay_confirm_dt']) ) { ?>
                <span style="color:#ff0000;">
                <?=$row['error_msg'];?><br /><span style="font-size:8px;">( <?=$row['error_dt'];?> )</span><!-- ( <?=$row['error_code'];?> / <?=$row['error_event'];?> ) -->
                </span>
            <?php } else if( $row['billing_yn']=="N" ) { ?>관리자 취소<?php } ?>
        </td>        
        <td scope="col" style="text-align:center;">
            <a href="#"><input type="button" value=" 자세히 " class="btn_BillingDetail" id="" data-id="<?=$row['bl_id'];?>" style="width:60px; height:26px; cursor: pointer; "></a>
            <?php if( (!$row['pay_confirm_dt']) && $row['billing_yn']=="Y" ) { ?>
            <!--<a href="#" onclick="alert('삭제')"><input type="button" value=" 삭제 " class="btn_BillingDel" id="" style="width:40px; height:26px;"></a> -->
            <?php } ?>
        </td>
    </tr>            
    <?php } ?>
    <?php if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>
</div>


<?php
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
echo $pagelist;
?>


<script type="application/javascript">
$(function() {

    $('.btn_BillingDetail').click(function(e) { 
        var id = $(this).attr("data-id");
        $('#BillingDetail_popup iframe').attr('src', '/adm/shop_admin/popup.payment_OnlineBilling_Detail.php?bl_id=' + id);
        $('#BillingDetail_popup iframe').attr('scrolling', 'auto');
        $('#BillingDetail_popup iframe').attr('frameborder', '0');
        $('#BillingDetail_popup').show(); 
    });

});

function cancelExcelDownload() {
    $('#loading_excel').hide();
}
</script>

<!-- 자세히 팝업 -->
<div id="BillingDetail_popup"><iframe id="BillingDetail_iframe"></iframe></div>

<div id="loading_excel" style="display: none;">
<div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
</div>
</div>

<style>
    /* 팝업 */
    #BillingDetail_popup { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background:rgba(28, 26, 26, 0.5); }
    #BillingDetail_popup iframe { position:absolute; width:1000px; height:700px; max-height: 90%; top: 50%; left: 50%; transform:translate(-50%, -50%); background:white; }
    
    /* 로딩 팝업 */
    #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
    #loading_excel .loading_modal img { display: block; margin: 20px auto; }
    #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
</style>


<script>

$(function(){

    $("#sch_sort").change(function(){ // select #sch_sort의 옵션이 바뀔때
        if($(this).val()=="vi_date"){ // 해당 value 값이 vi_date이면
            $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
        }else{ // 아니라면
            $("#sch_word").datepicker("destroy"); // datepicker 미실행
        }
    });

    if($("#sch_sort option:selected").val()=="vi_date"){ // select #sch_sort 의 옵션중 selected 된것의 값이 vi_date라면
        $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
    }

    $("#ExcelDownload").click(function(){
        Download_Excel();
    });

});

function onlinebilling_submit_function(f)
{
    return true;
}

$(document).ready(function() {

    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

});

function excelform(url){
    var opt = "width=600,height=450,left=10,top=10,menubar=no,location=no,resizable=no,scrollbars=no,status=no";
    window.open(url, "win_excel", opt);
    return false;
}


function Payment_Set_Billing_Setting(){
    if (!confirm("결제 버튼 활성화 설정을 저장하시겠습니까?")) { return; }

    var s_dt = parseInt( $("#select_start_dt option:selected").val() );
    var e_dt = parseInt( $("#select_end_dt option:selected").val() );

    if( s_dt > e_dt ) {
        alert("버튼 활성화 시작일이 종료일 보다 크게 설정할 수 없습니다.");
        return;
    }

    $.ajax({
        url: '/adm/shop_admin/ajax.payment_OnlineBilling_SetUpdate.php',
        type: 'POST',
        data: {
            "mode_set":'setting', 
            "radio_onoff": $("input[id='radio_onoff']:checked").val(),
            "select_start_dt": $("#select_start_dt option:selected").val(),
            "select_end_dt": $("#select_end_dt option:selected").val()
        },
        dataType: 'json',
        success: function(data) {
            location.reload();
        },
        error: function(e) {}
    });

}

function Download_Excel() {
    if (!confirm("엑셀 파일을 다운로드 하시겠습니까?")) { return; }
    
    $('#loading_excel').show();

    var queryString = "<?=urldecode($_SERVER['QUERY_STRING']);?>";
    excel_downloader = $.fileDownload('/adm/shop_admin/ajax.payment_OnlineBilling_Excel.php', {
                httpMethod: "POST",
                data: { mode_set:"ExcelDown", data:queryString }
            })
            .always(function() { $('#loading_excel').hide(); });

/*
    $.ajax({
        url: '/adm/shop_admin/ajax.payment_OnlineBilling_Excel.php',
        type: 'GET',
        data: { mode_set:"ExcelDown", data:queryString },
        dataType: 'json',
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function(data, message, xhr) {
        },
        error: function(e) {}
    });
*/
}
</script>



<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
