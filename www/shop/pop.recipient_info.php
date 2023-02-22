<?php
include_once('./_common.php');

$page_type = $_GET['page'];

if ($member['mb_type'] !== 'default') {
    alert("사업소 회원만 접근 가능합니다.");
}

$action = 'pop.recipient_info.php';

// TODO : 수급자 팝업창 만들기

$res = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_GET['id']
  ));

$penToolList = [];
$penToolRefCnt = [];

$res_items = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST, array(
    'penId' => $_GET['id']
));

if($res_items['data'])
    $penToolList = $res_items["data"];

$used_period = ['이동변기01'=>'5','목욕의자01'=>'5','성인용보행기01'=>'5','지팡이01'=>'2','욕창예방매트리스01'=>'3',
'욕창예방방석01'=>'3','경사로(실내용)01'=>'2'
];

/*
$used_period = ['이동변기01'=>'5','목욕의자01'=>'5','성인용보행기01'=>'5','지팡이01'=>'2','욕창예방매트리스01'=>'3',
'욕창예방방석01'=>'3','경사로(실내용)01'=>'2','전동침대00'=>'10','수동침대00'=>'10','수동휠체어00'=>'5',
'욕창예방매트리스00'=>'3','경사로(실외용)00'=>'8','이동욕조00'=>'5','배회감지기00'=>'5','목욕리프트00'=>'3'
];
*/

$key_list = ['이동변기01'=>'1','목욕의자01'=>'1','안전손잡이01'=>'10','미끄럼방지용품(매트)01'=>'5','미끄럼방지용품(양말)01'=>'6','간이변기01'=>'2',
'지팡이01'=>'1','욕창예방매트리스01'=>'1','욕창예방방석01'=>'1','자세변환용구01'=>'5','성인용보행기01'=>'2','요실금팬티01'=>'4','경사로(실내용)01'=>'6','수동휠체어00'=>'1',
'전동침대00'=>'1','욕창예방매트리스00'=>'1','이동욕조00'=>'1','목욕리프트00'=>'1','배회감지기00'=>'1','경사로(실외용)00'=>'1','수동침대00'=>'1'];

$item_list = [];
for($ind = 0; $ind < count($penToolList) ; $ind++ ){
    $gubun = $penToolList[$ind]['gubun'] == '00'?'01':'00';
    $item_list[] = $penToolList[$ind]['itemNm'].$gubun;
}

for ($idx = 0; $idx < count($key_list) ; $idx++ ){
    if(in_array(array_keys($key_list)[$idx], $item_list)){
        $penToolRefCnt[array_keys($key_list)[$idx]] = array_values($key_list)[$idx];
    } else {
        $penToolRefCnt[array_keys($key_list)[$idx]] = -1;
    }
}

// $sql_chk_period = "select ENT_ID, PEN_NM,PEN_LTM_NUM,ORD_DTM,ITEM_NM,count(item_NM) as cnt from pen_purchase_hist
// where ITEM_NM not in ('안전손잡이','미끄럼 방지용품','간이변기','자세변환용구','요실금팬티','') and curdate() < DATE_ADD(ORD_DTM, INTERVAL 5 YEAR)
// and ent_id = 'ENT2022052400001' and PEN_LTM_NUM = 'L0011602141' group by ITEM_NM;";

$sql = "select * from pen_purchase_hist where ENT_ID = '".$member['mb_entId']."' and PEN_NM = '".$res['data'][0]['penNm']."' and PEN_LTM_NUM  = '".$res['data'][0]['penLtmNum']."' and (CURRENT_TIMESTAMP between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM);";
$ct_result = sql_query($sql);
$ct_list = [];
$ct_count = [];
while ($res_item = sql_fetch_array($ct_result)) {
    $res_item['ITEM_NM'] = str_replace(' ','',$res_item['ITEM_NM']);
    $ct_list[] = $res_item;
    $paycode = $res_item['PROD_PAY_CODE']==1?'1':'0';
    if($ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode]){
        $ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] += 1;
    } else {
        $ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] = 1;
    }    
}

if(substr($_GET['penLtmNum'],0,2)=='LL'){
    $_GET['penLtmNum'] = substr($_GET['penLtmNum'], 1);
}

$arr_hist = [];
$sql_hist = "select * from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = '{$_GET['penLtmNum']}';";
$res_hist = sql_query($sql_hist);
while ($hist_result = sql_fetch_array($res_hist)) {
    $arr_hist[] = $hist_result;
}

$arr_period = [];
$sql_period = "
(select PEN_NM, PROD_PAY_CODE, replace(ITEM_NM,' ','') as ITEM_NM, PROD_BAR_NUM, PROD_NM, ORD_DTM, PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = '{$_GET['penLtmNum']}' 
and ITEM_NM in ('성인용보행기','경사로(실내용)') order by ITEM_NM desc)
UNION
(select PEN_NM, PROD_PAY_CODE, replace(ITEM_NM,' ','') as ITEM_NM, PROD_BAR_NUM, PROD_NM, max(ORD_DTM) as ORD_DTM, PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = '{$_GET['penLtmNum']}'
and ITEM_NM not in ('안전손잡이','미끄럼 방지용품','간이변기','자세변환용구','요실금팬티','성인용보행기','경사로(실내용)') group by ITEM_NM order by ITEM_NM desc);
";

$period_result = sql_query($sql_period);
while ($res_item_period = sql_fetch_array($period_result)) {
    if($res_item_period['PROD_PAY_CODE'] == '1'){
        $res_item_period['items'] = $res_item_period['ITEM_NM'].'01';
        $res_item_period['period'] = $used_period[$res_item_period['items']];
    } else {
        $res_item_period['items'] = $res_item_period['ITEM_NM'].'00';
        $res_item_period['period'] = $used_period[$res_item_period['items']];
    }
    $arr_period[] = $res_item_period;
}

$arr_category = [];
$sql_cate = "select ca_id, if(ca_id='10d0','경사로(실내용)',if(ca_id='2020','경사로(실외용)',ca_name)) as ca_name from g5_shop_category where char_length(ca_id) = 4 and left(ca_id,2) in ('10','20');";
$cate_result = sql_query($sql_cate);
while ($res_cate = sql_fetch_array($cate_result)) {
    $arr_category[$res_cate['ca_name']] = $res_cate['ca_id'];
}

//인증서 업로드 추가 영역
$mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";

if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])){
	$mobile_yn = "Mobile";
}else{
	$mobile_yn = "Pc";
}
$is_file = false;
if($member["cert_data_ref"] != ""){
	$cert_data_ref =  explode("|",$member["cert_data_ref"]);
	$cert_info = "사용자명:".base64_decode($cert_data_ref[1])." | 만료일자:".base64_decode($cert_data_ref[2]);
	$upload_dir = $_SERVER['DOCUMENT_ROOT']."/data/file/member/tilko/";
	$file_name = base64_encode($cert_data_ref[0]);
	if(file_exists($upload_dir.$file_name.".enc") || file_exists($upload_dir.$file_name.".txt")){
		$is_file = true;
	}
}
//인증서 업로드 추가 영역 끝
?>

<html>
<head>
    <meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=2,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
    <title>요양정보</title>
    <link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
    <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
</head>
<style>
.admin_popup {
    align-content: center; margin: auto; padding-left: 10%; padding-right: 10%;
}
.head-title { font-size: x-large;}
.admin_popup span {font-weight: bold;}
.separator p .line {
    content: ' ';width:100%; background: #A6A6A6;
    height:3px;margin: auto; display:inline-block;
    margin-top: 0.5%; margin-bottom: 0.5%;
}
.head {width: 100%; height: 30%; padding-top: 6%;}
.head .rep_amount {
    width: 44%; height: 50%; margin: auto;
    float: left; border: #ddd 2px solid;
    background-color: #f5f5f5; text-align: center; vertical-align: middle;
}
.head .rep_amount p {font-size: large;}
.head .rep_info {
    width: 54%; height: 50%; float: right; border: #ddd 1px solid;
}
.head .rep_info table {
    width: 100%; height: 100%; border: 1px solid #ddd; border-collapse: collapse;
}
.head .rep_info td {
    font-weight: normal; border: 1px solid #ddd; text-align: center;
}
.sub_title {font-weight: bold; font-size: large; padding-top: 1%;}
.contents {width: 100%; padding: unset;}
.contents table {
    width: 100%; height: fit-content; border-collapse: collapse; margin-bottom: 3%;
}
.contents thead {background-color: #F2F2F2;}
.contents th {
    border: 1px solid #ddd;  border-left-style:none; border-right-style:none; text-align: center;
    font-size: medium; font-weight: bold; padding: 0.8% 0%;
}
.contents thead {font-weight: normal;}
.contents td {
    font-weight: normal;
    border: 1px solid #ddd;
    text-align: center;
    font-size: medium;
    padding: 0.5% 0%;
}
.normal-row td{padding: 0.8% 0%;}
.table_contract tr td:first-child{border-left-style:none;}
.table_contract tr td:nth-child(5){border-right-style:none;}
.contents tbody tr td:first-child{border-left-style:none;}
.contents tbody tr td:nth-child(4){border-right-style:none;}

@media (max-width: 480px) {
    .head-title {font-size: large;}
    .head .rep_amount p {font-size: medium;}
    .sub_title {font-weight: bold; font-size: medium;}
    .head .rep_amount {border: 1px; height: 60%;}
    .head .rep_info {border: 0.5px; height: 60%;}
    .head .rep_info table {border: 0.5px;}
    .head .rep_info td {font-size: small;}
    .contents th {font-size: small;}
    .contents td {font-size: small;}
}

@media (max-width: 400px) {
    .head-title {font-size: medium;}
    .head .rep_info td {font-size: x-small;}
    .contents th {font-size: x-small;}
    .contents td {font-size: x-small;}
}

/* 인증서 비번 팝업 - 인증서 업로드 추가 */
#cert_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.5);
}
#cert_popup_box iframe {
  width:322px;
  height:307px;
  max-height: 80%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}

#cert_guide_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.5);
}
#cert_guide_popup_box iframe {
  width:850px;
  height:750px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}
#cert_ent_num_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.5);
}
#cert_ent_num_popup_box iframe {
  width:300px;
  height:305.33px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}
</style>

<div id="pop_add_item" class="admin_popup">
    <div class="head">
        <p class="head-title"><!-- <span class = "rep_common"><?php echo "홍길동(L1234567890)";?></span><span>님의 요양정보</span> --></p>
        <div class="rep_amount">
            <p style="color: #ee0000;"><span>급여 잔액 : </span><span class = "rem_amount">1,600,000원</span></p>
            <p class ="used_amount">사용 금액 : 0원</p>
        </div>
        <div class="rep_info">
            <table>
              <colgroup>
                <col width="28%"/>
                <col width="22%"/>
                <col width="28%"/>
                <col width="22%"/>
              </colgroup>
              <tr>
                <td colspan="1"><span>인정등급</span></td>
                <td colspan="1" class = "penRecGraNm"> - 등급</td>
                <td colspan="1"><span>본인부담율</span></td>
                <td colspan="1" class = "penTypeNm"> - %</td>
              </tr>
              <tr>
                <td colspan="1"><span>인정유효기간</span></td>
                <td colspan="3" class = "penExpiDtm"> ~ </td>
              </tr>
              <tr>
                <td colspan="1"><span>적용기간</span></td>
                <td colspan="3" class = "penAppDtm"> ~ </td>
              </tr>
            </table>			
        </div>
	<?php if($page_type == "search"){?>
		<input type="button" value="요양정보업데이트" id="pen_info_update" style="float:right;margin-top:5px;">
	<?php }?>
    </div>
    <div class="separator">
        <p><span class="line"> </span> </p>
    </div>
    <div class="contents">
        <p class="sub_title" > 판매 급여 품목</p>
        <table>
            <colgroup>
              <col width="10%"/>
              <col width="20%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="5%"/>
              <col width="5%"/>
              <col width="5%"/>
              <col width="15%"/>
            </colgroup>
            <thead>
                <tr>
                    <th colspan="1">No</th>
                    <th colspan="5">품목명</th>
                    <th colspan="3">계약완료</th>
                    <th colspan="1">판매가능</th>
                </tr>
            </thead>
            <tbody id = "table_sale">
                <tr>
                    <td colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                        조회된 구매 품목이 없습니다.
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="separator">
          <p><span class="line"> </span> </p>
        </div>


        <p class="sub_title" > 대여 급여 품목</p>
        <table>
            <colgroup>
              <col width="10%"/>
              <col width="20%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="10%"/>
              <col width="5%"/>
              <col width="5%"/>
              <col width="5%"/>
              <col width="15%"/>
            </colgroup>
            <thead>
                <tr>
                    <th colspan="1">No</th>
                    <th colspan="5">품목명</th>
                    <th colspan="3">계약완료</th>
                    <th colspan="1">대여가능</th>
                </tr>
            </thead>
            <tbody id = "table_rental">
                <tr>
                    <td colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                        조회된 대여 품목이 없습니다.
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="separator">
          <p><span class="line"> </span> </p>
        </div>

        <p class="sub_title" id = "table_contract_subtitle"> 계약 상세 내역</p>
        <table style="width: 100%; height: fit-content; margin-bottom: 5%;" id = "table_contract_main">
            <colgroup>
              <col width="10%"/>
              <col width="30%"/>
              <col width="30%"/>
              <col width="15%"/>
              <col width="15%"/>
            </colgroup>
            <thead>
                <tr>
                    <th colspan="1">No</th>
                    <th colspan="1">품목명</th>
                    <th colspan="1">제품명</th>
                    <th colspan="1">계약일<br/>/ 대여기간</th>
                    <th colspan="1">금액</th>
                </tr>
            </thead>
            <tbody id = "table_contract">
                <tr>
                    <td colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                        조회된 계약 내역이 없습니다.
                    </td>
                </tr>
            </tbody>
        </table>

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

<iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false" height="0" width="0"></iframe>
<script type="text/javascript">
	$( document ).ready(function() {
		<?php if($member["cert_reg_sts"] != "Y"){//등록 안되어 있음
			if($mobile_yn == 'Pc'){?>
		//공인인증서 등록 안내 및 등록 버튼 팝업 알림으로 교체 될 영역	
			cert_guide();
		<?php }else{?>
		alert("컴퓨터에서 공인인증서를 등록 후 이용이 가능한 서비스 입니다.");
		<?php }
		}else{//등록 되어 있음
			if(!$is_file){
	?>		tilko_call('1');
	<?php	}
		}?>
		
		$('#cert_popup_box').click(function() {
		  $('body').removeClass('modal-open');
		  $('#cert_popup_box').hide();
		});
		$('#cert_guide_popup_box').click(function() {
		  $('body').removeClass('modal-open');
		  $('#cert_guide_popup_box').hide();
		});
		$('#cert_ent_num_popup_box').click(function() {
		  $('body').removeClass('modal-open');
		  $('#cert_ent_num_popup_box').hide();
		});
	});
	
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
</script>
<!-- 인증서 업로드 추가 영역 끝-->
</body>
<script src="http://cdn.jquerytools.org/1.2.5/jquery.tools.min.js"></script>
<script>
    $(function () {
        $('#table_contract_subtitle').hide();
        $('#table_contract_main').hide();

        loading();

        let prod_period = <?=json_encode($arr_period);?>;
        let cnt_period = [];
        for(var i = 0; i < prod_period.length; i++){
            var deadline_date = new Date(prod_period[i]['ORD_DTM']);
            deadline_date.setDate(deadline_date.getDate() - 1);
            deadline_date.setFullYear(deadline_date.getFullYear() + Number(prod_period[i]['period']));

            var paycode = prod_period[i]['PROD_PAY_CODE'] == '1' ?'01':'00';
            if(deadline_date > new Date()){
                cnt_period[prod_period[i]['ITEM_NM']+paycode] = cnt_period[prod_period[i]['ITEM_NM']+paycode]==null? 1: cnt_period[prod_period[i]['ITEM_NM']+paycode]+1;
                prod_period[i]['deadline_date'] = deadline_date+" in period"; // 연한 내에 있으면
            }
        }

        let penLtmNum_parent;
        let penNm_parent;
        let page_type = "<?=$page_type?>";

        var hist_arr = <?=json_encode($arr_hist);?>;
        var used_period = <?=json_encode($used_period);?>;
        // 요양정보 간편조회 페이지에서 호출한 경우 => 직접 api에서 데이터 받아와서 뿌림
        if(page_type == 'search'){ 
            var head_title = `<span class = "rep_common"><?php echo "홍길동(L1234567890)";?></span><span>님의 요양정보</span> <span id="search_date">(조회 : 0000-00-00)</span>`;
            $(".head-title").append(head_title);

            penLtmNum_parent = "<?=str_replace('L','',$_GET['penLtmNum'])?>";
            penNm_parent = "<?=$_GET['penNm']?>";
            $(".rep_common").text(penNm_parent+"(L"+penLtmNum_parent+")");
			/* 저장 데이터 불러옴으로 업데이트 안함
            $.post('ajax.macro_update.php', {
                mb_id: '<?=$member['mb_id']?>',
                recipient_name: penNm_parent,
                recipient_num: penLtmNum_parent,
                status: "search"
            }, 'json');
            */
            var add_contract_list = [];
            $.ajax('ajax.recipient.inquiry2.php', {
                type: 'POST',  // http method
                data: { id : penLtmNum_parent,rn : penNm_parent },  // data to submit
                success: function (data, status, xhr) {

                    let rep_list_api = data['data']['recipientContractDetail']['Result'];                
                    let rep_info_api = rep_list_api['ds_welToolTgtList'][0];
                    if(rep_info_api['REDUCE_NM'] == '감경'){ //REDUCE_NM가 대상자 구분, 감경은 SBA_CD를 이용하여 본인부담율을 가져오기
                        let penPayRate_api = rep_info_api['SBA_CD'].replace('(', ' ').replace(')', '');
                    } else {
                        let penPayRate_api = rep_info_api['REDUCE_NM'] == '일반' ? '일반 15%': rep_info_api['REDUCE_NM'] == '기초' ? '기초 0%'
                                                                : rep_info_api['SBA_CD'];
                    }
                    
                    let penPayRate_api = '';
                    if(rep_info_api['REDUCE_NM'] != '감경'){ //REDUCE_NM가 대상자 구분, 감경은 SBA_CD를 이용하여 본인부담율을 가져오기
                        penPayRate_api = rep_info_api['REDUCE_NM'] == '일반' ? '일반 15%': rep_info_api['REDUCE_NM'] == '기초' ? '기초 0%'
                                                                : rep_info_api['SBA_CD'];
                    } else {
                        penPayRate_api = rep_info_api['SBA_CD'].replace('(', ' ').replace(')', '');
                    }

                    /*
					for(var ind = 0; ind < rep_list_api['ds_toolPayLmtList'].length; ind++){
                        var appst = new Date(rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(0,4)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(4,2)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(6,2));
                        var apped = new Date(rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(0,4)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(4,2)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(6,2));
                        var today = new Date();
                        if(today < apped && today > appst){
                            applydtm = appst.toISOString().split('T')[0]+' ~ '+apped.toISOString().split('T')[0];
                            break;
                        }
                        if(ind == rep_list_api['ds_toolPayLmtList'].length-1){
                            applydtm = rep_list_api['ds_toolPayLmtList'][0]['APDT_FR_DT']+' ~ '+rep_list_api['ds_toolPayLmtList'][0]['APDT_TO_DT'];
                        }
                    }*///API 조회시 주석 해재
					applydtm = rep_info_api['applydtm'];//API 조회시 주석 처리
                    $(".penRecGraNm").text(rep_info_api['LTC_RCGT_GRADE_CD']+"등급");//인정등급
                    $(".penTypeNm").text(penPayRate_api);//본인부담율
                    $(".penExpiDtm").text(rep_info_api['RCGT_EDA_DT']);//인정유효기간
                    $(".penAppDtm").text(applydtm);//적용기간
					$("#search_date").text("(조회 : "+rep_info_api['UPDATE']+")");//조회날짜
                    $(".rem_amount").text(makeComma(rep_info_api['REMN_AMT'])+'원');//잔액
                    $(".used_amount").text('사용 금액 : '+makeComma(rep_info_api['USE_AMT'])+'원');//사용금액
					
					var contract_list = data['data']['recipientContractHistory']['Result']['ds_result'] == null ?[] :data['data']['recipientContractHistory']['Result']['ds_result'];
                    var contract_cnt = [];

                    if(contract_list == null || contract_list == []){
                        $(".rem_amount").text(makeComma('1600000')+'원');
                        $(".used_amount").text('사용 금액 : 0원');
                    } else {
                        /*
						for(var idx = 0; idx < rep_list_api['ds_toolPayLmtList'].length; idx++){
                            if((rep_list_api['ds_toolPayLmtList'][idx]['APDT_FR_DT'].replace(' ','') == applydtm.split('~')[0].replaceAll('-','').replace(' ','')) && (rep_list_api['ds_toolPayLmtList'][idx]['APDT_TO_DT'].replace(' ','') == applydtm.split('~')[1].replace(/-/gi, "").replace(' ',''))){
                                $(".rem_amount").text(makeComma(rep_list_api['ds_toolPayLmtList'][idx]['REMN_AMT'])+'원');
                                $(".used_amount").text('사용 금액 : '+makeComma(rep_list_api['ds_toolPayLmtList'][idx]['USE_AMT'])+'원');
                                break;
                            }
                        }
						*/
                        for(var i = 0; i < contract_list.length; i++){
                            var paycode = contract_list[i]['WLR_MTHD_CD'] == '판매'?'01':'00';
                            
							if(contract_cnt[contract_list[i]['PROD_NM']+paycode] == null)
                                contract_cnt[contract_list[i]['PROD_NM']+paycode] = 1;
                            else 
                                contract_cnt[contract_list[i]['PROD_NM']+paycode] += 1;
							
							//alert(contract_list[i]['PROD_NM']+paycode+":"+contract_cnt[contract_list[i]['PROD_NM']+paycode]);
                        }
                    }

                    let tool_list_api = JSON.parse(data['data']['recipientToolList'])['Result'];
                    $('#table_rental').empty();
                    $('#table_sale').empty();
					

                    let sale_y = tool_list_api['ds_payPsbl1'];
                    let sale_n = tool_list_api['ds_payPsbl2'];
                    let rent_y = tool_list_api['ds_payPsblLnd1'];
                    let rent_n = tool_list_api['ds_payPsblLnd2'];
                    let tool_list_cnt = <?=json_encode($key_list);?>;

                    
                    var index = 1;
                    for(var i = 0; i < sale_y.length+sale_n.length; i++){
                        if(i > sale_y.length-1){
                            var row = `<tr id="${'gumae'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${sale_n[i-(sale_y.length)]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                        <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                                    </tr>`;
                            
                        } else {  
                            var used_item = used_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01'] == null ?0:Number(used_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var item_period = cnt_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01'] == null ?0:Number(cnt_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var cnt = contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] == null ?0 : Number(contract_cnt[sale_y[i]['WIM_ITM_CD']+'01']);
                            item_period = item_period==0?0:item_period-cnt;
                            var Sellable = sale_y[i]['WIM_ITM_CD'].replace(' ', '') == '미끄럼방지용품'? 11 :Number(tool_list_cnt[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var gumae_cnt = Sellable-cnt-item_period;
                            cnt = cnt + item_period;

                            var hist_ctr_arr = [];
                            if(used_item && item_period) {
                                for(var ii = 0; ii < hist_arr.length; ii++) {
                                    if(hist_arr[ii]['ITEM_NM'].replace(' ', '') == sale_y[i]['WIM_ITM_CD'].replace(' ', '')){
                                        var prev_date = new Date(hist_arr[ii]['ORD_DTM']);
                                        var cal_date = new Date(prev_date.setFullYear(prev_date.getFullYear() + Number(used_item)));
                                        var now = new Date();
                                        if(cal_date > now){
                                            hist_ctr_arr.push(hist_arr[ii]);
                                        }
                                    }
                                }
								
                                contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] = contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] == null ?item_period+'+' :contract_cnt[sale_y[i]['WIM_ITM_CD']+'01']+item_period+'+';
                            }

                            if(contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] != null) { 
                                var row = `<tr id="${'gumae'+index}" class="normal-row">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${sale_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3"><a href="#" class="gumae-toggler" data-prod-contract-gumae=${index}>${cnt}개 ▼</a></td>
                                                <td colspan="1">${gumae_cnt}개</td>
                                            </tr>
                                            <tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5"><span>제품명</span></td>
                                                <td colspan="3"><span>계약일</span></td>
                                                <td colspan="1" ><span>급여가</span></td>
                                            </tr>`;
                                for(var ind = 0; ind < contract_list.length; ind++){
                                    if(contract_list[ind]['PROD_NM'] != sale_y[i]['WIM_ITM_CD']) continue;
                                    if(contract_list[ind]['WLR_MTHD_CD'] == '대여') continue;
                                    row += `<tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5">${contract_list[ind]['MGDS_NM']}</td>
                                                <td colspan="3">${contract_list[ind]['POF_FR_DT'].split('~')[0]}</td>
                                                <td colspan="1">${makeComma(contract_list[ind]['TOT_AMT'])}</td>
                                            </tr>`;
                                }

                                if(hist_ctr_arr != []){
                                    for(var ind = 0; ind < hist_ctr_arr.length; ind++){
                                        if(hist_ctr_arr[ind]['ITEM_NM'].replace(' ', '') != sale_y[i]['WIM_ITM_CD'].replace(' ', '')) continue;
                                        row += `<tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                    <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                    <td colspan="5">${hist_ctr_arr[ind]['PROD_NM']}</td>
                                                    <td colspan="3">${hist_ctr_arr[ind]['ORD_DTM']}</td>
                                                    <td colspan="1">${makeComma(hist_ctr_arr[ind]['TOTAL_PRICE'])}</td>
                                                </tr>`;
                                        add_contract_list.push(hist_ctr_arr[ind]);
                                    }  
                                }
                            } else {
                                var row = `<tr id="${'gumae'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${sale_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1">${gumae_cnt}개</td>
                                    </tr>`;
                            }                            
                        }   
                        index++;       
                        $("#table_sale").append(row);
                    }

                    
                    var index = 1;
                    for(var i = 0; i < rent_y.length+rent_n.length; i++){
                        if(i > rent_y.length-1){
                            var row = `<tr id="${'daeyeo'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${rent_n[i-(rent_y.length)]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                        <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                                    </tr>`;                            
                        } else {   
                            var item_period = cnt_period[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'] == null ?0:Number(cnt_period[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00']);
                            var cnt = 0;
                            if(contract_cnt[rent_y[i]['WIM_ITM_CD']+'00'] != null) { 
                                cnt = contract_cnt[rent_y[i]['WIM_ITM_CD']+'00']; 
                                item_period = item_period==0?0:item_period-cnt;                                
                                var tmp_cnt = Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period < 0 ? 0 : Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period;
                                var row = `<tr id="${'daeyeo'+index}" class="normal-row">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${rent_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3"><a href="#" class="daeyeo-toggler" data-prod-contract-daeyeo=${index}>${cnt}개 ▼</a></td>
                                                <td colspan="1">${tmp_cnt}개</td>
                                            </tr>
                                            <tr id="${'daeyeo'+index}" class="${'contract-daeyeo'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5"><span>제품명</span></td>
                                                <td colspan="3"><span>대여기간</span></td>
                                                <td colspan="1" ><span>급여가</span></td>
                                            </tr>`;
                                for(var ind = 0; ind < contract_list.length; ind++){
                                    if(contract_list[ind]['PROD_NM'].replace(' ', '') != rent_y[i]['WIM_ITM_CD'].replace(' ', '')) continue;                                    
                                    if(contract_list[ind]['WLR_MTHD_CD'] == '판매') continue;
                                    row += `<tr id="${'daeyeo'+index}" class="${'contract-daeyeo'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5">${contract_list[ind]['MGDS_NM']}</td>
                                                <td colspan="3">${contract_list[ind]['POF_FR_DT'].split('~')[0]}</br>~${contract_list[ind]['POF_FR_DT'].split('~')[1]}</td>
                                                <td colspan="1">${makeComma(contract_list[ind]['TOT_AMT'])}</td>
                                            </tr>`;
                                } 
                            } else {
                                var row = `<tr id="${'daeyeo'+index}">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${rent_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3">${cnt}개</td>
                                                <td colspan="1">${Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period}개</td>
                                            </tr>`;
                            }
                        }                                                         
                        index++; 
                        $("#table_rental").append(row);                     
                    }

                    $('#table_contract').empty();
                    buildTable_api(contract_list);
                    buildTable_api(add_contract_list, 'add');
                    /* 저장 데이터 불러오는 내용이라 로그 안남김                   
                    $.post('./ajax.inquiry_log.php', {
                        data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : penLtmNum_parent,pen_nm : penNm_parent,resultMsg : status,occur_page : "pop.recipient_info.php" }
                    }, 'json')
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert("로그 저장에 실패했습니다!");
                    });
					*/
                },
                error: function (jqXhr, textStatus, errorMessage) {
                    var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
      
                    //인증서 업로드 추가 영역 
				if(errMSG == "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다." ){
					alert(errMSG);
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
					
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "5"){
					ent_num_insert();
				}
				// 인증서 업로드 추가 영역 끝
					return false;
                }
            });
        }else{ // 요양정보조회 버튼을 통해 호출한 경우 => DB에서 데이터 받아와서 뿌림
            penLtmNum_parent = parent.document.all["penLtmNum_parent"].value;
            penNm_parent = parent.document.all["penNm_parent"].value;

            let rep_list = <?=json_encode($res);?>;
            let rep_detail = [];
            let contract = [];
            for(var i = 0; i < rep_list['data'].length; i++){
                if(rep_list['data'][i]['penNm'] == penNm_parent && rep_list['data'][i]['penLtmNum'] == penLtmNum_parent){
                    rep_detail = rep_list['data'][i];
                    break;
                }
            }

            $(".rep_common").text(penNm_parent+"("+penLtmNum_parent+")");
            if(rep_detail['penRecGraNm'] == null){
                if(rep_detail['penRecGraCd'] == '06') {
                    $(".penRecGraNm").text("6등급");
                } else {
                    $(".penRecGraNm").text("-");
                }
            } else {
                $(".penRecGraNm").text(rep_detail['penRecGraNm']);
            }
            $(".penTypeNm").text(rep_detail['penTypeNm']);
            $(".penExpiDtm").text(rep_detail['penExpiDtm']);
            var penAppEdDtm = rep_detail['penAppEdDtm'];

            if(rep_detail['penAppEdDtm'] != null){
                var appED = new Date(penAppEdDtm.substr(0,4)+'-'+penAppEdDtm.substr(4,2)+'-'+penAppEdDtm.substr(6,2));
                var appST = new Date(appED);
                appST.setDate(appST.getDate() + 1);
                appST.setFullYear(appST.getFullYear() - 1);
                var today = new Date();

                if(appST < today && today < appED){
                    $(".penAppDtm").text(appST.toISOString().split('T')[0]+' ~ '+appED.toISOString().split('T')[0]);
                } else if(appST > today){
                    while(appST > today){
                        appST.setFullYear(appST.getFullYear() - 1);
                        appED.setFullYear(appED.getFullYear() - 1);
                    }        
                }

                $(".penAppDtm").text(appST.toISOString().split('T')[0]+' ~ '+appED.toISOString().split('T')[0]);
            } else {
                $(".penAppDtm").text("null");
            }
            
            let ct_list = <?=json_encode($ct_list)?>;
            
            if(ct_list.length > 0){
                $(".rem_amount").text(makeComma(ct_list[0]['PEN_BUDGET'])+'원');
                $(".used_amount").text('사용 금액 : '+makeComma(1600000-ct_list[0]['PEN_BUDGET'])+'원');
            } else {
                $(".rem_amount").text(makeComma('1600000')+'원');
                $(".used_amount").text('사용 금액 : 0원');
            }
            
            $('#table_sale').empty();
            $('#table_rental').empty();


            var sale_index = 1;
            var rent_index = 1;
            var penToolRefCnt = <?=json_encode($penToolRefCnt)?>;
            var ct_count = <?=json_encode($ct_count)?>;

            let arr_category = <?=json_encode($arr_category)?>;
            let cate_href = "";

            var add_ct_list = [];

            for(var i = 0; i < Object.keys(penToolRefCnt).length; i++){
                cate_href = "<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$_GET['id'].'&redirect='?>";
                if(Object.keys(penToolRefCnt)[i] == '미끄럼방지용품(매트)01') {continue;}
                var item_nm = Object.keys(penToolRefCnt)[i] == '미끄럼방지용품(양말)01'?'미끄럼방지용품01':Object.keys(penToolRefCnt)[i];
                var item_period = cnt_period[Object.keys(penToolRefCnt)[i]] == null ?0:Number(cnt_period[Object.keys(penToolRefCnt)[i]]);
                var cnt = ct_count[item_nm] == null ?0 :Number(ct_count[item_nm]);
                item_period = item_period==0?0:item_period-cnt;
                var Sellable = Object.keys(penToolRefCnt)[i] == '미끄럼방지용품(양말)01'? 11: Number(Object.values(penToolRefCnt)[i]);
                
                if(Object.keys(penToolRefCnt)[i] == '미끄럼방지용품(양말)01' || Object.keys(penToolRefCnt)[i] == '미끄럼방지용품(매트)01'){
                    //cate_href = '/shop/list.php?ca_id=10&ca_sub%5B%5D=70';
                    // cate_href = '/shop/connect_recipient.php?pen_id=<?=$_GET['id']?>&redirect='+encodeURI('/shop/list.php?ca_id=10&ca_sub%5B%5D=70');
                    cate_href = "<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$_GET['id'].'&redirect='?>"+encodeURIComponent('/shop/list.php?ca_id=10&ca_sub%5B%5D=70&ca_sub%5B%5D=80');
                } else {
                    //cate_href = '/shop/list.php?ca_id='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(0,2)+'&ca_sub%5B%5D='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(2,2);
                    // cate_href = '/shop/connect_recipient.php?pen_id=<?=$_GET['id']?>&redirect='+encodeURI('/shop/list.php?ca_id='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(0,2)+'&ca_sub%5B%5D='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(2,2));
                    cate_href = "<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$_GET['id'].'&redirect='?>"+encodeURIComponent('/shop/list.php?ca_id='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(0,2)+'&ca_sub%5B%5D='+arr_category[item_nm.substr(0, item_nm.length-2)].substr(2,2));
                }

                if(Object.keys(penToolRefCnt)[i].substr(-2,2) == '00'){ //대여
                    if(Object.values(penToolRefCnt)[i] == -1){ //사용불가 제품일 경우
                        var row = `<tr id="${'daeyeo'+rent_index}">
                                <td colspan="1">${rent_index}</td>
                                <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                            </tr>`;
                    } else {
                        if(ct_count[Object.keys(penToolRefCnt)[i]] == null) { // 해당 적용기간 내 계약이 없는 경우
                            // 판매 가능이 클릭 가능한 코드
                            var row = `<tr id="${'daeyeo'+rent_index}">
                                        <td colspan="1">${rent_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1" ><a href="#" class = "test" id="${cate_href}">${Sellable-cnt-item_period}개</a></td>
                                    </tr>`;

                            // 판매가능이 클릭 불가한 코드
                            /*
                            var row = `<tr id="${'daeyeo'+rent_index}">
                                        <td colspan="1">${rent_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1" >${Sellable-cnt-item_period}개</td>
                                    </tr>`;
                            */
                        } else { // 해당 적용기간 내 계약이 있는 경우
                            // 판매 가능이 클릭 가능한 코드
                            var row = `<tr id="${'daeyeo'+rent_index}" class="normal-row">
                                        <td colspan="1">${rent_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3"><a href="#" class="daeyeo-toggler" data-prod-contract-daeyeo=${rent_index}>${cnt}개 ▼</a></td>
                                        <td colspan="1" ><a href="#" class = "test" id="${cate_href}">${Sellable-cnt-item_period}개</a></td>
                                    </tr>
                                    <tr id="${'daeyeo'+rent_index}" class="${'contract-daeyeo'+rent_index}" style="display:none;">
                                        <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                        <td colspan="5"><span>제품명</span></td>
                                        <td colspan="3"><span>계약일</span></td>
                                        <td colspan="1" ><span>급여가</span></td>
                                    </tr>`;
                                    
                            // 판매 가능이 클릭 불가한 코드
                            /*
                            var row = `<tr id="${'daeyeo'+rent_index}" class="normal-row">
                                        <td colspan="1">${rent_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3"><a href="#" class="daeyeo-toggler" data-prod-contract-daeyeo=${rent_index}>${cnt}개 ▼</a></td>
                                        <td colspan="1" >${Sellable-cnt-item_period}개</td>
                                    </tr>
                                    <tr id="${'daeyeo'+rent_index}" class="${'contract-daeyeo'+rent_index}" style="display:none;">
                                        <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                        <td colspan="5"><span>제품명</span></td>
                                        <td colspan="3"><span>대여기간</span></td>
                                        <td colspan="1" ><span>급여가</span></td>
                                    </tr>`;
                            */

                            for(var ind = 0; ind < ct_list.length; ind++){
                                if(ct_list[ind]['ITEM_NM'] != Object.keys(penToolRefCnt)[i].substr(0,Object.keys(penToolRefCnt)[i].length-2)) continue;
                                row += `<tr id="${'daeyeo'+rent_index}" class="${'contract-daeyeo'+rent_index}" style="display:none;">
                                            <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                            <td colspan="5">${ct_list[ind]['PROD_NM']}</td>
                                            <td colspan="3">${ct_list[ind]['ORD_STR_DTM']}</br> ~ ${ct_list[ind]['ORD_END_DTM']}</td>
                                            <td colspan="1">${makeComma(ct_list[ind]['TOTAL_PRICE'])}</td>
                                        </tr>`;
                            }
                        }
                    }
                    rent_index++;
                    $("#table_rental").append(row);
                } else { //판매
                    var used_item = used_period[Object.keys(penToolRefCnt)[i]] == null ?0:Number(used_period[Object.keys(penToolRefCnt)[i]]);

                    if(Object.values(penToolRefCnt)[i] == -1){ //사용불가 제품일 경우
                        var row = `<tr id="${'gumae'+sale_index}">
                                <td colspan="1">${sale_index}</td>
                                <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                            </tr>`;
                    } else {
                        var gumae_cnt = Sellable-cnt-item_period;
                        var hist_ctr_arr = [];
                        if(used_item && item_period) {
                            for(var ii = 0; ii < hist_arr.length; ii++) {
                                if(hist_arr[ii]['ITEM_NM'].replace(' ', '') == item_nm.substr(0,item_nm.length-2)){
                                    var prev_date = new Date(hist_arr[ii]['ORD_DTM']);
                                    var cal_date = new Date(prev_date.setFullYear(prev_date.getFullYear() + Number(used_item)));
                                    var now = new Date();
                                    if(cal_date > now){
                                        hist_ctr_arr.push(hist_arr[ii]);
                                    }
                                }
                            }
                            ct_count[item_nm] = ct_count[item_nm] == null ?item_period+'+' :ct_count[item_nm]+item_period+'+';
                            cnt = cnt + item_period;
                        }

                        if(ct_count[item_nm] == null) { // 해당 적용기간 내 계약이 없는 경우
                            // 구매 가능이 클릭 가능한 코드
                            var row = `<tr id="${'gumae'+sale_index}">
                                        <td colspan="1">${sale_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1" ><a href="#" class = "test" id="${cate_href}">${gumae_cnt}개</a></td>
                                    </tr>`;
                                    
                            // 구매 가능이 클릭 불가한 코드
                            /*
                            var row = `<tr id="${'gumae'+sale_index}">
                                        <td colspan="1">${sale_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1" >${Sellable-cnt-item_period}개</td>
                                    </tr>`;
                            */
                        } else { // 해당 적용기간 내 계약이 있는 경우
                            // 구매 가능이 클릭 가능한 코드
                            var row = `<tr id="${'gumae'+sale_index}" class="normal-row">
                                        <td colspan="1">${sale_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3"><a href="#" class="gumae-toggler" data-prod-contract-gumae=${sale_index}>${cnt}개 ▼</a></td>
                                        <td colspan="1" ><a href="#" class = "test" id="${cate_href}">${gumae_cnt}개</a></td>
                                    </tr>
                                    <tr id="${'gumae'+sale_index}" class="${'contract-gumae'+sale_index}" style="display:none;">
                                        <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                        <td colspan="5"><span>제품명</span></td>
                                        <td colspan="3"><span>계약일</span></td>
                                        <td colspan="1"><span>급여가</span></td>
                                    </tr>`;
                            
                            // 구매 가능이 클릭 불가한 코드
                            /*
                            var row = `<tr id="${'gumae'+sale_index}" class="normal-row">
                                        <td colspan="1">${sale_index}</td>
                                        <td colspan="5">${item_nm.substr(0,item_nm.length-2)}</td>
                                        <td colspan="3"><a href="#" class="gumae-toggler" data-prod-contract-gumae=${sale_index}>${cnt}개 ▼</a></td>
                                        <td colspan="1" >${Sellable-cnt-item_period}개</td>
                                    </tr>
                                    <tr id="${'gumae'+sale_index}" class="${'contract-gumae'+sale_index}" style="display:none;">
                                        <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                        <td colspan="5"><span>제품명</span></td>
                                        <td colspan="3"><span>계약일</span></td>
                                        <td colspan="1"><span>급여가</span></td>
                                    </tr>`;
                            */

                            for(var ind = 0; ind < ct_list.length; ind++){
                                if(ct_list[ind]['ITEM_NM'].replace(' ', '') != item_nm.substr(0,item_nm.length-2)) continue;
                                row += `<tr id="${'gumae'+sale_index}" class="${'contract-gumae'+sale_index}" style="display:none;">
                                            <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                            <td colspan="5">${ct_list[ind]['PROD_NM']}</td>
                                            <td colspan="3">${ct_list[ind]['ORD_DTM']}</td>
                                            <td colspan="1">${makeComma(ct_list[ind]['TOTAL_PRICE'])}</td>
                                        </tr>`;
                            }

                            if(hist_ctr_arr != []){
                                for(var ind = 0; ind < hist_ctr_arr.length; ind++){
                                    if(hist_ctr_arr[ind]['ITEM_NM'].replace(' ', '') != item_nm.substr(0,item_nm.length-2)) continue;
                                    row += `<tr id="${'gumae'+sale_index}" class="${'contract-gumae'+sale_index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5">${hist_ctr_arr[ind]['PROD_NM']}</td>
                                                <td colspan="3">${hist_ctr_arr[ind]['ORD_DTM']}</td>
                                                <td colspan="1">${makeComma(hist_ctr_arr[ind]['TOTAL_PRICE'])}</td>
                                            </tr>`;
                                    add_ct_list.push(hist_ctr_arr[ind]);
                                }  
                            }
                        }
                    }
                    sale_index++;
                    $("#table_sale").append(row);
                }
            }            

            if(ct_list){
                $('#table_contract').empty();
                buildTable(ct_list);
                buildTable(add_ct_list, 'add');
            }
        }
        
		//요양정보업데이트 이벤트
		$(document).on("click", "#pen_info_update", function (){
			loading();
			var head_title = `<span class = "rep_common"><?php echo "홍길동(L1234567890)";?></span><span>님의 요양정보</span>`;
            $(".head-title").html('');
			$(".head-title").append(head_title);

            penLtmNum_parent = "<?=str_replace('L','',$_GET['penLtmNum'])?>";
            penNm_parent = "<?=$_GET['penNm']?>";
            $(".rep_common").text(penNm_parent+"(L"+penLtmNum_parent+")");
      
            
            var add_contract_list = [];
            $.ajax('ajax.recipient.inquiry.php', {
                type: 'POST',  // http method
                data: { id : penLtmNum_parent,rn : penNm_parent },  // data to submit
                success: function (data, status, xhr) {

                    let rep_list_api = data['data']['recipientContractDetail']['Result'];                
                    let rep_info_api = rep_list_api['ds_welToolTgtList'][0];
                    
					let today = new Date();
	
					if(rep_info_api['REDUCE_NM'] == '감경'){ //REDUCE_NM가 대상자 구분, 감경은 SBA_CD를 이용하여 본인부담율을 가져오기
                        let penPayRate_api = rep_info_api['SBA_CD'].replace('(', ' ').replace(')', '');
                    } else {
                        let penPayRate_api = rep_info_api['REDUCE_NM'] == '일반' ? '일반 15%': rep_info_api['REDUCE_NM'] == '기초' ? '기초 0%'
                                                                : rep_info_api['SBA_CD'];
                    }
                    
                    let penPayRate_api = '';
                    if(rep_info_api['REDUCE_NM'] != '감경'){ //REDUCE_NM가 대상자 구분, 감경은 SBA_CD를 이용하여 본인부담율을 가져오기
                        penPayRate_api = rep_info_api['REDUCE_NM'] == '일반' ? '일반 15%': rep_info_api['REDUCE_NM'] == '기초' ? '기초 0%'
                                                                : rep_info_api['SBA_CD'];
                    } else {
                        penPayRate_api = rep_info_api['SBA_CD'].replace('(', ' ').replace(')', '');
                    }

                    for(var ind = 0; ind < rep_list_api['ds_toolPayLmtList'].length; ind++){
                        var appst = new Date(rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(0,4)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(4,2)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(6,2));
                        var apped = new Date(rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(0,4)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(4,2)+'-'+rep_list_api['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(6,2));
                        if(today < apped && today > appst){
                            applydtm = appst.toISOString().split('T')[0]+' ~ '+apped.toISOString().split('T')[0];
                            break;
                        }
                        if(ind == rep_list_api['ds_toolPayLmtList'].length-1){
                            applydtm = rep_list_api['ds_toolPayLmtList'][0]['APDT_FR_DT']+' ~ '+rep_list_api['ds_toolPayLmtList'][0]['APDT_TO_DT'];
                        }
                    }

                    $(".penRecGraNm").text(rep_info_api['LTC_RCGT_GRADE_CD']+"등급");
                    $(".penTypeNm").text(penPayRate_api);
                    $(".penExpiDtm").text(rep_info_api['RCGT_EDA_DT']);
                    $(".penAppDtm").text(applydtm);

                    var contract_list = data['data']['recipientContractHistory']['Result']['ds_result'] == null ?[] :data['data']['recipientContractHistory']['Result']['ds_result'];
                    var contract_cnt = [];
					let rem_amount = 1600000;
                    if(contract_list == null || contract_list == []){
                        $(".rem_amount").text(makeComma('1600000')+'원');
                        $(".used_amount").text('사용 금액 : 0원');
                    } else {
                        for(var idx = 0; idx < rep_list_api['ds_toolPayLmtList'].length; idx++){
                            if((rep_list_api['ds_toolPayLmtList'][idx]['APDT_FR_DT'].replace(' ','') == applydtm.split('~')[0].replaceAll('-','').replace(' ','')) && (rep_list_api['ds_toolPayLmtList'][idx]['APDT_TO_DT'].replace(' ','') == applydtm.split('~')[1].replace(/-/gi, "").replace(' ',''))){
                                $(".rem_amount").text(makeComma(rep_list_api['ds_toolPayLmtList'][idx]['REMN_AMT'])+'원');
								rem_amount = rep_list_api['ds_toolPayLmtList'][idx]['REMN_AMT'];
                                $(".used_amount").text('사용 금액 : '+makeComma(rep_list_api['ds_toolPayLmtList'][idx]['USE_AMT'])+'원');
                                break;
                            }
                        }

                        for(var i = 0; i < contract_list.length; i++){
                            var paycode = contract_list[i]['WLR_MTHD_CD'] == '판매'?'01':'00';
                            if(contract_cnt[contract_list[i]['PROD_NM']+paycode] == null)
                                contract_cnt[contract_list[i]['PROD_NM']+paycode] = 1;
                            else 
                                contract_cnt[contract_list[i]['PROD_NM']+paycode] += 1;
                        }
                    }

                    let tool_list_api = JSON.parse(data['data']['recipientToolList'])['Result'];
                    $('#table_rental').empty();
                    $('#table_sale').empty();

                    let sale_y = tool_list_api['ds_payPsbl1'];
                    let sale_n = tool_list_api['ds_payPsbl2'];
                    let rent_y = tool_list_api['ds_payPsblLnd1'];
                    let rent_n = tool_list_api['ds_payPsblLnd2'];
                    let tool_list_cnt = <?=json_encode($key_list);?>;

                    
                    var index = 1;
                    for(var i = 0; i < sale_y.length+sale_n.length; i++){
                        if(i > sale_y.length-1){
                            var row = `<tr id="${'gumae'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${sale_n[i-(sale_y.length)]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                        <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                                    </tr>`;
                            
                        } else {  
                            var used_item = used_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01'] == null ?0:Number(used_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var item_period = cnt_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01'] == null ?0:Number(cnt_period[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var cnt = contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] == null ?0 : Number(contract_cnt[sale_y[i]['WIM_ITM_CD']+'01']);
                            item_period = item_period==0?0:item_period-cnt;
                            var Sellable = sale_y[i]['WIM_ITM_CD'].replace(' ', '') == '미끄럼방지용품'? 11 :Number(tool_list_cnt[sale_y[i]['WIM_ITM_CD'].replace(' ', '')+'01']);
                            var gumae_cnt = Sellable-cnt-item_period;
                            cnt = cnt + item_period;

                            var hist_ctr_arr = [];
                            if(used_item && item_period) {
                                for(var ii = 0; ii < hist_arr.length; ii++) {
                                    if(hist_arr[ii]['ITEM_NM'].replace(' ', '') == sale_y[i]['WIM_ITM_CD'].replace(' ', '')){
                                        var prev_date = new Date(hist_arr[ii]['ORD_DTM']);
                                        var cal_date = new Date(prev_date.setFullYear(prev_date.getFullYear() + Number(used_item)));
                                        var now = new Date();
                                        if(cal_date > now){
                                            hist_ctr_arr.push(hist_arr[ii]);
                                        }
                                    }
                                }
                                contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] = contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] == null ?item_period+'+' :contract_cnt[sale_y[i]['WIM_ITM_CD']+'01']+item_period+'+';
                            }

                            if(contract_cnt[sale_y[i]['WIM_ITM_CD']+'01'] != null) { 
                                var row = `<tr id="${'gumae'+index}" class="normal-row">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${sale_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3"><a href="#" class="gumae-toggler" data-prod-contract-gumae=${index}>${cnt}개 ▼</a></td>
                                                <td colspan="1">${gumae_cnt}개</td>
                                            </tr>
                                            <tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5"><span>제품명</span></td>
                                                <td colspan="3"><span>계약일</span></td>
                                                <td colspan="1" ><span>급여가</span></td>
                                            </tr>`;
                                for(var ind = 0; ind < contract_list.length; ind++){
                                    if(contract_list[ind]['PROD_NM'] != sale_y[i]['WIM_ITM_CD']) continue;
                                    if(contract_list[ind]['WLR_MTHD_CD'] == '대여') continue;
                                    row += `<tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5">${contract_list[ind]['MGDS_NM']}</td>
                                                <td colspan="3">${contract_list[ind]['POF_FR_DT'].split('~')[0]}</td>
                                                <td colspan="1">${makeComma(contract_list[ind]['TOT_AMT'])}</td>
                                            </tr>`;
                                }

                                if(hist_ctr_arr != []){
                                    for(var ind = 0; ind < hist_ctr_arr.length; ind++){
                                        if(hist_ctr_arr[ind]['ITEM_NM'].replace(' ', '') != sale_y[i]['WIM_ITM_CD'].replace(' ', '')) continue;
                                        row += `<tr id="${'gumae'+index}" class="${'contract-gumae'+index}" style="display:none;">
                                                    <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                    <td colspan="5">${hist_ctr_arr[ind]['PROD_NM']}</td>
                                                    <td colspan="3">${hist_ctr_arr[ind]['ORD_DTM']}</td>
                                                    <td colspan="1">${makeComma(hist_ctr_arr[ind]['TOTAL_PRICE'])}</td>
                                                </tr>`;
                                        add_contract_list.push(hist_ctr_arr[ind]);
                                    }  
                                }
                            } else {
                                var row = `<tr id="${'gumae'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${sale_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3">${cnt}개</td>
                                        <td colspan="1">${gumae_cnt}개</td>
                                    </tr>`;
                            }                            
                        }   
                        index++;       
                        $("#table_sale").append(row);
                    }

                    
                    var index = 1;
                    for(var i = 0; i < rent_y.length+rent_n.length; i++){
                        if(i > rent_y.length-1){
                            var row = `<tr id="${'daeyeo'+index}">
                                        <td colspan="1">${i+1}</td>
                                        <td colspan="5">${rent_n[i-(rent_y.length)]['WIM_ITM_CD'].replace(' ', '')}</td>
                                        <td colspan="3" style = "background-color: #f5f5f5;">해당없음</td>
                                        <td colspan="1" style = "background-color: #f5f5f5;">해당없음</td>
                                    </tr>`;                            
                        } else {   
                            var item_period = cnt_period[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'] == null ?0:Number(cnt_period[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00']);
                            var cnt = 0;
                            if(contract_cnt[rent_y[i]['WIM_ITM_CD']+'00'] != null) { 
                                cnt = contract_cnt[rent_y[i]['WIM_ITM_CD']+'00']; 
                                item_period = item_period==0?0:item_period-cnt;                                
                                var tmp_cnt = Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period < 0 ? 0 : Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period;
                                var row = `<tr id="${'daeyeo'+index}" class="normal-row">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${rent_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3"><a href="#" class="daeyeo-toggler" data-prod-contract-daeyeo=${index}>${cnt}개 ▼</a></td>
                                                <td colspan="1">${tmp_cnt}개</td>
                                            </tr>
                                            <tr id="${'daeyeo'+index}" class="${'contract-daeyeo'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5"><span>제품명</span></td>
                                                <td colspan="3"><span>대여기간</span></td>
                                                <td colspan="1" ><span>급여가</span></td>
                                            </tr>`;
                                for(var ind = 0; ind < contract_list.length; ind++){
                                    if(contract_list[ind]['PROD_NM'].replace(' ', '') != rent_y[i]['WIM_ITM_CD'].replace(' ', '')) continue;                                    
                                    if(contract_list[ind]['WLR_MTHD_CD'] == '판매') continue;
                                    row += `<tr id="${'daeyeo'+index}" class="${'contract-daeyeo'+index}" style="display:none;">
                                                <td colspan="1" style="border-top-style: none; border-bottom-style: none;"></td>
                                                <td colspan="5">${contract_list[ind]['MGDS_NM']}</td>
                                                <td colspan="3">${contract_list[ind]['POF_FR_DT'].split('~')[0]}</br>~${contract_list[ind]['POF_FR_DT'].split('~')[1]}</td>
                                                <td colspan="1">${makeComma(contract_list[ind]['TOT_AMT'])}</td>
                                            </tr>`;
                                } 
                            } else {
                                var row = `<tr id="${'daeyeo'+index}">
                                                <td colspan="1">${i+1}</td>
                                                <td colspan="5">${rent_y[i]['WIM_ITM_CD'].replace(' ', '')}</td>
                                                <td colspan="3">${cnt}개</td>
                                                <td colspan="1">${Number(tool_list_cnt[rent_y[i]['WIM_ITM_CD'].replace(' ', '')+'00'])-cnt-item_period}개</td>
                                            </tr>`;
                            }
                        }                                                         
                        index++; 
                        $("#table_rental").append(row);                     
                    }

                    $('#table_contract').empty();
                    buildTable_api(contract_list);
                    buildTable_api(add_contract_list, 'add');
                    let penPayRate2 = rep_info_api['REDUCE_NM'] == '일반' ? '15%': rep_info_api['REDUCE_NM'] == '기초' ? '0%' : rep_info_api['REDUCE_NM'] == '의료급여' ? '6%'
                                                              : (rep_info_api['SBA_CD'].split('(')[1].substr(0, rep_info_api['SBA_CD'].split('(')[1].length-1));
					$.post('ajax.macro_update.php', {
						mb_id: '<?=$member['mb_id']?>',
						recipient_name: penNm_parent,
						recipient_num: penLtmNum_parent,
						status: "search",
                        birth: setDate(rep_info_api['BDAY']),
                        grade: rep_info_api['LTC_RCGT_GRADE_CD']+"등급",
                        type: rep_info_api['REDUCE_NM'],
                        percent: penPayRate2,
                        penApplyDtm: applydtm,
                        penExpiDtm: rep_info_api['RCGT_EDA_DT'],
                        rem_amount: rem_amount,
                        item_data:  JSON.parse(data['data']['recipientPurchaseRecord'])
					}, 'json');
                    
					$.post('./ajax.my.recipient.hist.php', {
					  data: data['data'],
					  status: false
					}, 'json')
					.fail(function($xhr) {
					  var data = $xhr.responseJSON;
					  alert("계약정보 업데이트에 실패했습니다!");
					});
					
					$.post('./ajax.inquiry_log.php', {
                        data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : penLtmNum_parent,pen_nm : penNm_parent,resultMsg : status,occur_page : "pop.recipient_info.php" }
                    }, 'json')
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert("로그 저장에 실패했습니다!");
                    });
                },
                error: function (jqXhr, textStatus, errorMessage) {
                    var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
                    //alert(errMSG);
                    //인증서 업로드 추가 영역 
				if(errMSG == "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다." ){
					alert(errMSG);
					$.post('./ajax.inquiry_log.php', {
                        data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : penLtmNum_parent,pen_nm : penNm_parent,resultMsg : "fail",occur_page : "pop.recipient_info.php",err_msg:errMSG }
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
                        data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : penLtmNum_parent,pen_nm : penNm_parent,resultMsg : "fail",occur_page : "pop.recipient_info.php",err_msg:errMSG }
                    }, 'json')
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert("로그 저장에 실패했습니다!");
                    });
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "5"){
					ent_num_insert();
				}
				// 인증서 업로드 추가 영역 끝
					return false;
                }
            });
		});


        // 판매급여품목 클릭이벤트
        $('#table_sale').on('click', '.gumae-toggler', function(e){
            var innerTxt = ($(this).context.innerText).replace('▼','').replace('▲','');
            e.preventDefault();
            if ($('#'+e.target.parentElement.parentElement.id).hasClass('gumae-open')) {
                $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: none;');
                $(this).context.innerText = innerTxt+' ▼';
            } else {
                $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: 2px solid #3F6EC2; background:#ECF1F9;');
                $(this).context.innerText = innerTxt+' ▲';
            }
            $('#'+e.target.parentElement.parentElement.id).toggleClass("gumae-open");
            $('.contract-gumae'+$(this).attr('data-prod-contract-gumae')).map((idx, child) => {
                if ($(child).hasClass('gumae-open')) {
                    $(child).toggleClass("gumae-open");
                    $(child).attr('style', 'border: none;display:none;');
                } else {
                    $(child).toggleClass("gumae-open");
                    if (idx != $('.contract-gumae'+$(this).attr('data-prod-contract-gumae')).length-1) {
                        $(child).attr('style', 'border: 2px solid #3F6EC2;border-bottom:none;border-top:none;display:table-row;');
                    } else {
                        $(child).attr('style', 'border: 2px solid #3F6EC2;border-top:none;display:table-row;');
                    }
                }
            });
        });

        // 대여급여품목 클릭이벤트
        $('#table_rental').on('click', '.daeyeo-toggler', function(e){
            e.preventDefault();
            if ($('#'+e.target.parentElement.parentElement.id).hasClass('daeyeo-open')) {
                $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: none;');
            } else {
                $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: 2px solid #3F6EC2; background:#ECF1F9;');
            }
            $('#'+e.target.parentElement.parentElement.id).toggleClass("daeyeo-open");
            $('.contract-daeyeo'+$(this).attr('data-prod-contract-daeyeo')).map((idx, child) => {
                if ($(child).hasClass('daeyeo-open')) {
                    $(child).toggleClass("daeyeo-open");
                    $(child).attr('style', 'border: none;display:none;');
                } else {
                    $(child).toggleClass("daeyeo-open");
                    if (idx != $('.contract-daeyeo'+$(this).attr('data-prod-contract-daeyeo')).length-1) {
                        $(child).attr('style', 'border: 2px solid #3F6EC2;border-bottom:none;border-top:none;display:table-row;');
                    } else {
                        $(child).attr('style', 'border: 2px solid #3F6EC2;border-top:none;display:table-row;');
                    }
                }
            });
        });
    });

    $('#table_sale').on('click', '.test', function(e){
        parent.redirect_item($(this).prop("id"));
    });
    $('#table_rental').on('click', '.test', function(e){
        parent.redirect_item($(this).prop("id"));
    });

    function buildTable(data, option='', index = '') {        
        var table = document.getElementById('table_contract');
        if(data.length == 0 && option != 'add'){
            var row = `<tr>
                            <td  colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                            조회된 계약 정보가 없습니다.
                            </td>
                        </tr>`;
            table.innerHTML += row;
        } else {
            for (var i=0; i < data.length; i++) {
                var index = option == 'add' ?'-' :i+1;
                var dtm = data[i]['ORD_STATUS'] == '판매'? data[i]['ORD_STR_DTM']: data[i]['ORD_STR_DTM']+'~</br>'+data[i]['ORD_END_DTM'];
                var row = `<tr>
                            <td colspan="1">${index}</td>
                            <td colspan="1">${data[i]['ITEM_NM']}</td>
                            <td colspan="1">${data[i]['PROD_NM']}</td>
                            <td colspan="1">${dtm}</td>
                            <td colspan="1" style="border-right-style:none;">${makeComma(data[i]['TOTAL_PRICE'])}</td>
                        </tr>`;
                table.innerHTML += row;
            }
        }        
    }

    function buildTable_api(data, option='', index = '') {
        var table = document.getElementById('table_contract');
        if(data.length == 0 && option != 'add'){
            var row = `<tr>
                            <td  colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                            조회된 계약 정보가 없습니다.
                            </td>
                        </tr>`;
            table.innerHTML += row;
        } else {
            for (var i=0; i < data.length; i++) {
                var index = option == 'add' ?'-' :i+1;
                var dtm = '';
                if(option == 'add') {
                    var row = `<tr>
                            <td colspan="1">${index}</td>
                            <td colspan="1">${data[i]['ITEM_NM'].replace(' ', '')}</td>
                            <td colspan="1">${data[i]['PROD_NM']}</td>
                            <td colspan="1">${data[i]['ORD_DTM']}
                            <td colspan="1" style="border-right-style:none;">${makeComma(data[i]['TOTAL_PRICE'])}</td>
                        </tr>`;
                } else {
                    if(data[i]['WLR_MTHD_CD'] == '판매'){
                        dtm = `<td colspan="1">${data[i]['POF_FR_DT'].split('~')[0]}`;
                    } else {
                        dtm = `<td colspan="1">${data[i]['POF_FR_DT'].split('~')[0]}</br>~${data[i]['POF_FR_DT'].split('~')[1]}</td>`;
                    }
                    var row = `<tr>
                            <td colspan="1">${index}</td>
                            <td colspan="1">${data[i]['PROD_NM']}</td>
                            <td colspan="1">${data[i]['MGDS_NM']}</td>`+
                            dtm
                            +`<td colspan="1" style="border-right-style:none;">${makeComma(data[i]['TOT_AMT'])}</td>
                        </tr>`;
                }
                
                table.innerHTML += row;
            }
        }        
    }
    
    function toStringByFormatting(source, delimiter = '-') {
        const year = source.getFullYear();
        const month = leftPad(source.getMonth() + 1);
        const day = leftPad(source.getDate());

        return [year, month, day].join(delimiter);
    }

    function setDate(str_date){ return str_date.substr(0,4)+'-'+str_date.substr(4,2)+'-'+str_date.substr(6,2);}

	function makeComma(str) {str = String(str);return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');}

    function loading() {
        document.documentElement.style.overflowY = "hidden";
        LoadingWithMask('../thema/eroumcare/assets/loading3.gif');
        setTimeout("closeLoadingWithMask()", 1000);
    }

    function LoadingWithMask(gif) {
        //화면의 높이와 너비를 구합니다.
        var maskHeight = window.document.body.clientHeight;
        var maskWidth = window.document.body.clientWidth;
        //화면에 출력할 마스크를 설정해줍니다.
        var mask = "<div id='mask_loading' style='position:absolute; z-index:9000; background-color:#000000; left:0; top:0; display: flex; justify-content: center; align-items: center;'><img id ='loadingImg' src='"+ gif + "' style='position: absolute; display: block; margin: auto;'/></div>";
        //화면에 레이어 추가
        $('body').append(mask)
        //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채웁니다.
        $('#mask_loading').css({'width' : maskWidth,'height': maskHeight,'opacity' : '0.5'}); 
        //마스크 표시
        $('#mask_loading').show();
        //로딩중 이미지 표시
        // $('#loadingImg').appendTo(loadingImg);
        // $('#loadingImg').show();
    }

    function noData() {
        //화면의 높이와 너비를 구합니다.
        var maskHeight = window.document.body.clientHeight;
        var maskWidth = window.document.body.clientWidth;
        //화면에 출력할 마스크를 설정해줍니다.
        var mask = "<div id='mask_loading' style='position:absolute; z-index:9000; background-color:#000000; left:0; top:0; display: flex; justify-content: center; align-items: center;'>요양정보 업데이트가 필요합니다.</div>";
        //화면에 레이어 추가
        $('body').append(mask)
        //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채웁니다.
        $('#mask_loading').css({'width' : maskWidth,'height': maskHeight,'opacity' : '0.5'}); 
        //마스크 표시
        $('#mask_loading').show();
        //로딩중 이미지 표시
        // $('#loadingImg').appendTo(loadingImg);
        // $('#loadingImg').show();
    }

    function closeLoadingWithMask() {
        document.documentElement.style.overflowY = "auto";
        $('#mask_loading, #loadingImg').hide();
        $('#mask_loading, #loadingImg').empty(); 
    }

</script>
</html>
