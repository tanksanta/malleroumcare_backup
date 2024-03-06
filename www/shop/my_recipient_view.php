<?php
include_once("./_common.php");
define('_RECIPIENT_', true);
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
include_once("./_head.php");

$query = "SHOW tables LIKE 'eform_rent_hist'";//eform_rent_hist 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("
		CREATE TABLE `eform_rent_hist` (
		  `rh_id` int(11) NOT NULL AUTO_INCREMENT,
		  `entId` varchar(255) NOT NULL COMMENT '장기요양기관ID',
		  `entNm` varchar(255) NOT NULL COMMENT '장기요양기관명',
		  `entNum` varchar(255) NOT NULL COMMENT '장기요양기관번호',
		  `penId` varchar(255) NOT NULL COMMENT '수급자ID',
		  `confirm_date` date NOT NULL COMMENT '확인일시',
		  `create_month` varchar(20) NOT NULL COMMENT '생성월',
		  `entConAcc` varchar(255) DEFAULT NULL COMMENT '특이사항',
		  `penRecTypeCd` varchar(10) NOT NULL DEFAULT '01' COMMENT '확인사항(방문:02,유선:01)',
		  `it_ids` varchar(255) NOT NULL COMMENT '상품id(eform_document_item)',
		  `it_dates` varchar(255) NOT NULL COMMENT '상품별계약기간',
		  `it_end_date` varchar(20) NOT NULL COMMENT '상품중 마지막 계약 종료일',
		  `reg_date` datetime NOT NULL COMMENT '생성일',
		  PRIMARY KEY (`rh_id`),
		  KEY `it_end_date` (`it_end_date`,`entId`,`penId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8
	");
}

$query2 = "SHOW tables LIKE 'eform_document_log2'";//eform_document_log2 테이블 유무 확인
$wzres2 = sql_num_rows( sql_query($query2) );
if($wzres2 < 1) {
	sql_query("
		CREATE TABLE `eform_document_log2` (
  `dl_id` int(11) NOT NULL AUTO_INCREMENT,
  `dc_id` binary(16) NOT NULL,
  `dl_log` text NOT NULL,
  `dl_ip` varchar(255) NOT NULL DEFAULT '',
  `dl_browser` text NOT NULL,
  `dl_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`dl_id`),
  KEY `index1` (`dc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
	");
}

$query3 = "show columns from eform_rent_hist where field in ('rh_status');";//eform_rent_hist 테이블 유무 확인
$wzres3 = sql_num_rows( sql_query($query3) );
if($wzres3 < 1) {
	sql_query("
		alter table eform_rent_hist 
		add column doc_id varchar(255) NULL DEFAULT '' COMMENT '모두싸인문서ID' after rh_id,
		add column rh_status int(11) NULL DEFAULT 11 COMMENT '계약서상태 (0: 삭제, 11: 작성완료, 2: 서명완료, 3: 서명완료(이전계약서), 4:서명요청, 5:서명거절)' after doc_id,
		add column contract_sign_relation tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:본인,1:가족,2:친족,3:기타' after rh_status,
		add column contract_sign_relation_nm varchar(50) NULL DEFAULT '' COMMENT '기타관계' after contract_sign_relation,
		add column pen_guardian_nm varchar(50) NULL DEFAULT '' COMMENT '보호자명' after contract_sign_relation_nm,
		add column dc_sign_send_datetime datetime NOT NULL COMMENT '서명요청일' after reg_date,
		add column dc_sign_datetime datetime NOT NULL COMMENT '서명완료일' after dc_sign_send_datetime		
		;
	");
	sql_query("ALTER TABLE eform_rent_hist ADD INDEX rh_id (rh_id, doc_id,rh_status,reg_date,dc_sign_send_datetime,dc_sign_datetime,entId,penId);");
}

/**
* 기존에 있던  eform_document_item 테이블을 재사용하기 위한 작업
* 새로이 필요한 컬럼(it_rental_price)이 존재하는지 확인 후, 없으면 새로 추가하는 작업 진행
*/
$sql_check2 = "
  show columns from eform_document_item where field in ('it_rental_price');
";
$res_check2 = sql_query($sql_check2);
if(sql_num_rows($res_check2) == 0){

  $append_col2 = "alter table eform_document_item ".
                "add column it_rental_price int(11) NULL DEFAULT NULL COMMENT '월대여가(급여제공기록용)' after it_price";
  sql_query($append_col2);
}


// 수급자 연결 끊음
unset($_SESSION['recipient']);

# 회원검사
if(!$member["mb_id"])
  alert("접근 권한이 없습니다.");

if(!$_GET["id"])
  alert("정상적이지 않은 접근입니다.");

$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_GET['id']
));

if(!$res || $res['errorYN'] == 'Y')
  alert('서버 오류로 수급자 정보를 불러올 수 없습니다.');

$pen = $res['data'][0];
if(!$pen)
  alert('수급자 정보가 존재하지 않습니다.');

// 수급자 취급가능 제품
$items = get_items_by_recipient($pen['penId']);
$products = array(
  '00' => [], /* 판매제품 */
  '01' => [] /* 대여제품 */
);
foreach($items as $val) {
  $products[$val['gubun']][$val['itemId']] = $val['itemNm'];
}

function check_and_print($check, $prefix = '', $postfix = '') {
  if($check) return $prefix.$check.$postfix;
  return '';
}

// 메모 가져오기
$memos = get_memos_by_recipient($pen['penId']);

// 욕구사정기록지 가져오기
$recs = get_recs_by_recipient($pen['penId']);

// 보호자 가져오기
$pros = get_pros_by_recipient($pen['penId']);

// 최근 갱신일 가져오기
$update_date = $pen['modifyDtm'];
//echo $update_date;

// 판매/대여 품목 리스트 정의
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
          for($i=1; $i<14; $i++) {
            $sale_ids[${'sale_product_name'. $i}] = ${'sale_product_id'.$i};
		  }
          $rental_product_name0="욕창예방매트리스"; $rental_product_id0="ITM2020092200019";
          $rental_product_name1="경사로(실외용)"; $rental_product_id1="ITM2020092200018";
          $rental_product_name2="배회감지기"; $rental_product_id2="ITM2020092200017";
          $rental_product_name3="목욕리프트"; $rental_product_id3="ITM2020092200016";
          $rental_product_name4="이동욕조"; $rental_product_id4="ITM2020092200015";
          $rental_product_name5="수동침대"; $rental_product_id5="ITM2020092200014";
          $rental_product_name6="전동침대"; $rental_product_id6="ITM2020092200013";
          $rental_product_name7="수동휠체어"; $rental_product_id7="ITM2020092200012";
          for($i=0; $i<8; $i++) {
            $rent_ids[${'rental_product_name'. $i}] = ${'rental_product_id'.$i};
		} 
// 적용기간 기준일
$pen_gra_apply_txt = '없음';
$pen_gra_apply_result = sql_fetch("
  SELECT
    pen_gra_apply_month,
    pen_gra_apply_day
  FROM
    recipient_grade_log
  WHERE
    pen_id = '{$_GET['id']}' and
    del_yn = 'N'
  ORDER BY
    seq desc
  LIMIT 1
");
$pen_gra_apply_month = $pen_gra_apply_result['pen_gra_apply_month'];
$pen_gra_apply_day = $pen_gra_apply_result['pen_gra_apply_day'];
if($pen_gra_apply_month && $pen_gra_apply_day)
  $pen_gra_apply_txt = "{$pen_gra_apply_month}월 {$pen_gra_apply_day}일";

// 수급자 연결아이디
$pen_ent = get_pen_ent_by_pen_id($pen['penId']);

$sql_recent = "SELECT ent_id, pen_nm, PEN_LTM_NUM, count(*) as cnt from pen_purchase_hist where PEN_LTM_NUM = '{$pen["penLtmNum"]}' and ent_id = '{$member['mb_entId']}' group by ENT_ID, PEN_LTM_NUM;";
$recent_result = sql_fetch($sql_recent);

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
 //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================?>
<script>
	//swal("사용 주의","현재 수급자 조회조건 개선 작업으로 수급자 정보를\n업데이트할 수 없습니다.\n등록된 수급자의 정보가 정확하지 않을 수 있음을\n유의해 주시기 바랍니다.","warning");
	//history.back();
</script>
<?php //=======================================================================================================
?>
<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css?v=210829">
<div class="recipient_view_wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">
      <?=$pen['penNm']?> (<?=substr($pen['penBirth'], 2, 2)?>년생/<?=$pen['penGender']?>)
      <input type="hidden" name="penNm_parent" value=<?=$pen['penNm']?>>
    </div>
    <div class="r_btn_wrap">
      <a class="c_btn" href="./my_recipient_list.php">목록</a>
    </div>
  </div>
  <div class="info_wrap">
    <div style="height: 100%; width: 80%;">
		<div class="row">
          <div class="col-sm-2">· 장기요양인정번호</div>
          <div class="col-sm-10">: <?=$pen['penLtmNum']?> </div>
          <!---<div class="col-sm-10">: <?=substr($pen['penLtmNum'], 0, 6)?>**** (<?=$pen['penRecGraNm']?>/<?=$pen['penTypeNm']?>)</div> --->
          <input type="hidden" name="penLtmNum_parent" value=<?=$pen['penLtmNum']?>>
        </div>
		
        <div class="row">
          <div class="col-sm-2">· 연락처</div>
          <div class="col-sm-10">: <?=$pen['penConNum']?><?=($pen['penConPnum'] ? ", {$pen['penConPnum']}" : "")?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">· 주소</div>
          <div class="col-sm-10">: <?="{$pen['penAddr']} {$pen['penAddrDtl']}"?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">· 보호자</div>
          <?php if($pen['penProTypeCd'] == '00') { // 보호자 없음 ?>
          <div class="col-sm-10">: 없음</div>
          <?php } else { ?>
          <div class="col-sm-10">: <?php if($pen['penProTypeCd'] == '02') { echo '(요양보호사)'; } ?><?=check_and_print($pen_pro_rel_cd[$pen['penProRel']], '(', ')')?><?=$pen['penProNm']?><?=check_and_print(substr($pen['penProBirth'], 2, 2), ', ', '년생')?><?=check_and_print($pen['penProConNum'], ', ')?><?=check_and_print($pen['penProConPNum'], ', ')?><?=check_and_print($pen['penProAddr'], ', ')?><?=check_and_print($pen['penProAddrDtl'], ' ')?></div>
          <?php } ?>
        </div>
        <?php foreach($pros as $pro) { ?>
        <div class="row">
          <div class="col-sm-2">· 보호자</div>
          <div class="col-sm-10">: <?php if($pro['pro_type'] == '02') { echo '(요양보호사)'; } ?><?=check_and_print($pen_pro_rel_cd[$pro['pro_rel_type']], '(', ')')?><?=$pro['pro_name']?><?=check_and_print(substr($pro['pro_birth'], 2, 2), ', ', '년생')?><?=check_and_print($pro['pro_hp'], ', ')?><?=check_and_print($pro['pro_tel'], ', ')?><?=check_and_print($pro['pro_addr1'], ', ')?><?=check_and_print($pro['pro_addr2'], ' ')?></div>
        </div>
        <?php } ?>
        <div class="row">
          <div class="col-sm-2">· 장기요양기록지</div>
          <div class="col-sm-10">: 확인자(<?=$pen_cnm_type_cd[$pen['penCnmTypeCd']]?>), 수령방법(<?=$pen_rec_type_cd[$pen['penRecTypeCd']]?>) <?=$pen['penRecTypeTxt']?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">· 최근 조회일</div>
          <div class="col-sm-10">: <?php echo substr($update_date,0,4).'-'.substr($update_date,4,2).'-'.substr($update_date,6,2)?></div>
        </div>
       <!-----	
        <div class="row">
          <div class="col-sm-2">· 유효기간</div>
          <div class="col-sm-10">: <?=$pen['penExpiDtm']?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">· 적용기간 기준일</div>
          <div class="col-sm-10">: <?=$pen_gra_apply_txt?></div>
        </div>
       ------->	
    </div>
    <div style="height: 100%; width: 20%; float: right; position: absolute; top:15px; right:5px; z-index: 100;">
        <ul>
            <a href="./my_recipient_update.php?id=<?=$pen['penId']?>" class="btn_so_edit">기본정보 수정</a>
<!--  //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================  -->
            <button type="button" class="btn_so_sch" id="btn_so_sch" >요양정보 </br>업데이트</button>
			<!--button type="button" class="btn_so_sch" onClick="return error_btn()"  id="btn_so_sch">요양정보 </br>업데이트</button-->
	  <script>
		function error_btn(){
			//swal("사용 제한","수급자 조회조건 개선으로 간편조회 및\n일부 서비스가 일시 중단되었습니다.\n서비스 재개는 추후 공지를 통해 안내드리겠습니다.","error");
			//return false;
		}
	  </script>
<!--=========================================================================================================== -->
        </ul>
    </div>


<?php //TODO : 수급자 팝업창 만들기 ?>
<!-- =========================================================== -->
	  <!-- 품목찾기 팝업 -->
<div id="item_popup_box">
  <div class="popup_box_close">
    <i class="fa fa-times"></i>
  </div>
  <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
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
					$("#btn_so_sch2").trigger("click");
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					pwd_insert();
				}
			});
	}
</script>
<!-- 인증서 업로드 추가 영역 끝-->
<script>

$(function() {
	$.datepicker.setDefaults({
					dateFormat : 'yy-mm-dd',
					prevText: '이전달',
					nextText: '다음달',
					monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
					monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
					dayNames: ["일", "월", "화", "수", "목", "금", "토"],
					dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
					dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
					showMonthAfterYear: true,
					changeMonth: true,
					changeYear: true
				  });
				$('#penExpiStDtm').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd',maxDate:0  });


  let ct_history_list = [];
  // 품목찾기 팝업
  $('#item_popup_box').click(function() {
    $('body').removeClass('modal-open');
    $('#item_popup_box').hide();
  });
  $('#btn_so_sch').click(function(e) {
    //var url = 'pop.recipient_info.php?id=<?=$pen['penId']?>&penNm=<?=$pen['penNm']?>&penLtmNum=<?=$pen['penLtmNum']?>';
    //var url = 'pop_recipient.php';
    //$('#item_popup_box iframe').attr('src', url);
    //$('body').addClass('modal-open');
    //$('#item_popup_box').hide();	

	//기본정보 입력 레이어 팝업 보이기 추가
	$('body').addClass('modal-open');
	$('#popup_box2').show();
  });
  $('#btn_so_sch2').click(function(e) {
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

      var str_rn = $("#penNm2").val(); //$("input[name='penNm']")[0].value;
      var str_id = "<?=$pen['penLtmNum'] ?>";  //$("input[name='penLtmNum']")[0].value;
	  var str_birth = "<?=str_replace('.','',$pen['penBirth'])?>"
	  var str_cd = $("#penRecGraCd").val();
	  var str_stdtm = $("#penExpiStDtm").val();
	  if(str_rn == ""){
		alert("수급자명을 입력해 주세요.");
		$("#penNm").focus();
		return false;
	  }
	  if(str_cd == ""){
		alert("인정등급을 선택해 주세요.");
		return false;
	  }
	  if(str_stdtm == ""){
		alert("인정유효기간(시작일자)을 선택해 주세요.");
		return false;
	  }
      var btn_update = document.getElementById('btn_so_sch2');
      btn_update.disabled = true;
      $.ajax('ajax.recipient.inquiry.php', {
          type: 'POST',  // http method
          data: { id : str_id.replace('L',''),rn : str_rn, birth : str_birth,cd : str_cd, stdtm : str_stdtm },  // data to submit
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
                var appst = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_FR_DT'].substr(6,2)+" 00:00:00");
                var apped = new Date(rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(0,4)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(4,2)+'-'+rep_list['ds_toolPayLmtList'][ind]['APDT_TO_DT'].substr(6,2)+" 23:59:59");
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
          if(rep_info['REDUCE_NM'] == null && (rep_info['SBA_CD'].substr(0, 2) == '일반' || rep_info['SBA_CD'].substr(0, 2) == '의료' || rep_info['SBA_CD'].substr(0, 2) == '기초') ){
			penTypeCd = rep_info['SBA_CD'].substr(0, 2) == '일반'? '00' : rep_info['SBA_CD'].substr(0, 2) == '의료'? '03' : '04';  
		  }else if(rep_info['REDUCE_NM'].substr(0, 2) == '일반' || rep_info['REDUCE_NM'].substr(0, 2) == '의료' || rep_info['REDUCE_NM'].substr(0, 2) == '기초'){ //일반의료기초
            penTypeCd = rep_info['REDUCE_NM'].substr(0, 2) == '일반'? '00' : rep_info['REDUCE_NM'].substr(0, 2) == '의료'? '03' : '04';
          } else { //감경
            penTypeCd = rep_info['SBA_CD'].substr(3, 1) == '6'? '02' : '01';
          }
          
          var pen_gender = "<?=$pen['penGender']?>" == "" ?"-" :"<?=$pen['penGender']?>";
		      var sendData = {
		        penId : "<?=$pen['penId'] ?>",
		        penNm : "<?=$pen['penNm']?>",
		        penLtmNum : "<?=$pen['penLtmNum'] ?>",
		        penRecGraCd : '0'+rep_info['LTC_RCGT_GRADE_CD'],
		        penGender : pen_gender,
		        penBirth : rep_info['BDAY'].substr(0,4)+'-'+rep_info['BDAY'].substr(4,2)+'-'+rep_info['BDAY'].substr(6,2),
		        penJumin : rep_info['BDAY'].substr(2,6),
		        penTypeCd : penTypeCd,
		        penConNum : "<?=$pen['penConNum']?>", 
		        penConPnum : "<?=$pen['penConPnum']?>",
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
          
/*
          console.log("sendData : ", sendData);
          alert("test");
          return false;
*/


          $.post('./ajax.inquiry_log.php', {
            data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : status,occur_page : "my_recipient_view.php" }
          }, 'json')
          .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert("로그 저장에 실패했습니다!");
          });
          
		      $.post('./ajax.my.recipient.update.php', sendData, 'json')
		      .done(function(result) {
		        var data = result.data;
		  
		        if(data.isSpare)
		          return window.location.href = "./my_recipient_view.php?id"+data.penId;
		  
				if(ct_history_list.length != 0){ // 계약이력 삽입
				  let penPurchaseHist = <?=json_encode($recent_result)?>;
				  //if(penPurchaseHist == null){
					$.ajaxSetup({async:false});
					$.post('./ajax.my.recipient.hist.php', {
					  data: ct_history_list,
					  status: true
					}, 'json')
					.fail(function($xhr) {
					  var data = $xhr.responseJSON;
					  alert("계약정보 업데이트에 실패했습니다!");
					})

				  //} else if(ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].length > penPurchaseHist['cnt']){
					//ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'] = ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].slice(penPurchaseHist['cnt'], ct_history_list.length);

					// TODO : pen_purchase_hist update 만들기
					// 이로움 DB에 계약정보 insert
					//$.post('./ajax.my.recipient.hist.php', {
					//  data: ct_history_list,
					//  status: true
					//}, 'json')
					
					//.fail(function($xhr) {
					//  var data = $xhr.responseJSON;
					//  alert("계약정보 업데이트에 실패했습니다!");
					//})
				  //}
				}
		        
				$.post('./ajax.my.recipient.setItem.php', {
		          penId: "<?=$pen['penId'] ?>",
		          itemList: itemList
		        }, 'json')
		        .done(function(result) {
		          if(result.errorYN == "Y") {
		            alert(result.message);
		          } else {
		            //alert('완료되었습니다');
		            window.location.href = "./my_recipient_view.php?id="+data.penId;
		          }
		        })
		        .fail(function($xhr) {
		          var data = $xhr.responseJSON;
		          alert(data && data.message);
		        }); 
				
            

		      })
		      .fail(function($xhr) {
		        var data = $xhr.responseJSON;
		        alert(data && data.message);
		      });
			   

			  // UPDATE DB END

              btn_update.disabled = false;
          },
          error: function (jqXhr, textStatus, errorMessage) {
              var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자 정보를 정확하게 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
              //alert(errMSG);
              //인증서 업로드 추가 영역 
				if(errMSG == "수급자 정보를 정확하게 확인 후, 조회하시기 바랍니다." ){
					alert(errMSG);
					$.post('./ajax.inquiry_log.php', {
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : "fail",occur_page : "my_recipient_view.php",err_msg:errMSG }
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
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id.replace('L',''),pen_nm : str_rn,resultMsg : "fail",occur_page : "my_recipient_view.php",err_msg:errMSG }
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
});
</script>

<style>
/*테이블 */
.section_wrap table {
    width: 100%;
    height: fit-content;
    border-collapse: collapse;
}

.section_wrap th {
    border: 1px solid #ddd;
    border-left-style: none;
    border-right-style: none;
    text-align: center;
    font-size: medium;
    font-weight: bold;
    padding: 0.8% 0%;
	background-color:#F2F2F2;
}
.section_wrap td {
    font-weight: normal;
    border: 1px solid #ddd;
    text-align: center;
    font-size: medium;
    padding: 0.5% 0%;
}

.btn_so_sch:disabled {
  background-color: #bbb;
}
.btn_so_sch {
  float: right; position: relative; display: inline-block; color: #333; font-weight: normal; font-size: 14px;
      line-height: 20px; height: 60px; padding: 5px 36px; border-radius: 3px;
      vertical-align: middle; background-color: #ee8102; color: #fff; border: none; margin: 10px 0;
}
.btn_so_edit {
  float: right; position: relative; display: inline-block; color: #333; font-weight: normal; font-size: 14px;
      line-height: 20px; height: 30px; padding: 5px 20px; border: 1px solid #ddd; border-radius: 3px;
      background-color: #fff; vertical-align: middle; margin: 10px 0;
}
/* 품목찾기 팝업 */
#item_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.8);
}
#item_popup_box iframe {
  width:1000px;
  height:700px;
  max-height: 80%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}
.popup_box_close {
  position:absolute;
  top:15px;
  right: 15px;
  color: white;
  font-size: 2.5em;
  cursor:pointer;
}

.c_month {
	display:inline;
	margin-right:9px;
}

@media (max-width: 1020px) {
  #item_popup_box iframe {
    width: 100%;
    height: 90%;
    max-height:100%;
    transform: none;
    top: auto;
    left: 0px;
    bottom:0px;
  }
}

@media (max-width: 768px){
	.c_month {
		display:block;
		text-align:right;
		margin-bottom:10px;
	}
}

@media (max-width: 700px){
	.popup_box_con2{
		width:96% !important;
		left:50% !important;
		margin-left:-48% !important;
		height:650px !important;
	}
}

@media (max-width: 480px){
    .btn_so_sch { height: fit-content; font-size: small; }
    .btn_so_edit { height: fit-content; font-size: small; }
	.section_wrap th {
		font-size: small;
	}
	.section_wrap td {
		font-size: small;
	}
	.pop_tail {
		font-size: small;
	}
	.c_btn {
		font-size: small !important;
	}


}

@media (max-width: 400px){
	.section_wrap th {
		font-size: x-small;
	}
	.section_wrap td {
		font-size: x-small;
	}
	.pop_tail {
		font-size: x-small;
	}

	.popup_box_con{
		width:96% !important;
		left:50% !important;
		margin-left:-48% !important;
	}

	.pop_con {
		font-size: small;
	}
}

/* 인증서 비번 팝업 - 인증서 업로드 추가 */
#cert_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:99999;
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

.popup_box2 {
	display: none;
	position: fixed;
	width: 100%;
	height: 100%;
	left: 0;
	top: 0;
	z-index: 9999;
	background: rgba(0, 0, 0, 0.5);		
}

.popup_box_con {
	padding:20px;
	position: relative;
	background: #ffffff;
	z-index: 99999;
	height:620px;
	margin-top:-310px;
	margin-left:-200px;
	width:400px;
	left:50%;
	top:50%;
}

.popup_box_con2 {
	padding:20px;
	position: relative;
	background: #ffffff;
	z-index: 99999;
	height:620px;
	margin-top:-310px;
	margin-left:-350px;
	width:700px;
	left:50%;
	top:50%;
}

#loading_excel {
    display: none;
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.3);
  }
  #loading_excel .loading_modal {
    position: absolute;
    width: 400px;
    padding: 30px 20px;
    background: #fff;
    text-align: center;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  #loading_excel .loading_modal p {
    padding: 0;
    font-size: 16px;
  }
  #loading_excel .loading_modal img {
    display: block;
    margin: 20px auto;
  }
  #loading_excel .loading_modal button {
    padding: 10px 30px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }
</style>
<!-- =========================================================== -->
    <?php
    if($pen_ent) {
      $pen_mb = get_member($pen_ent['pen_mb_id'], 'mb_name');
    ?>
    <div class="row">
      <div class="col-sm-2">· 연결 ID</div>
      <div class="col-sm-10">: <?="{$pen_mb['mb_name']} ({$pen_ent['pen_mb_id']})"?>
      </div>
    </div>
    <?php } ?>
    <div class="tel_btn_wrap">
      <a href="tel:<?=$pen['penConNum'] ?: $pen['penConPNum']?>" class="tel_btn"><i class="fa fa-phone" aria-hidden="true"></i>수급자 전화연결</a>
      <a href="tel:<?=$pen['penProConNum'] ?: $pen['penProConPnum']?>" class="tel_btn"><i class="fa fa-phone" aria-hidden="true"></i>보호자 전화연결</a>
    </div>
  </div>
  
  <iframe id="Example2"
  	title ="iframe Example 2"
	width = "100%" height="600"
	style ="border : radius 10px 10px 10px 10px blue"
	src="pop.recipient_info.php?id=<?=$pen['penId']?>&penNm=<?=$pen['penNm']?>&penLtmNum=<?=$pen['penLtmNum']?>" >
  </iframe>

  <div class="sub_title_wrap">
    <div class="sub_title">
      제공가능 품목
    </div>
    <div class="sub_title_desc">* 카테고리 선택 시 회원이 선택된 상태로 이동합니다.</div>
  </div>
  <div class="section_wrap">
    <div class="item_wrap">
      <div class="item_head">판매품목</div>
      <div class="item_body">
      <?php
      foreach($products['00'] as $id => $name) {
        $ca_id = $sale_product_cate_table[$id];
      ?>
        <a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id='.substr($ca_id, 0, 2).'&ca_sub%5B%5D='.substr($ca_id, 2, 2))?>"><?=$name?></a>
      <?php } ?>
      </div>
    </div>
    <div class="item_wrap">
      <div class="item_head">대여품목</div>
      <div class="item_body">
      <?php
      foreach($products['01'] as $id => $name) {
        $ca_id = $rental_product_cate_table[$id];
      ?>
        <a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id='.substr($ca_id, 0, 2).'&ca_sub%5B%5D='.substr($ca_id, 2, 2))?>"><?=$name?></a>
      <?php } ?>
      </div>
    </div>
  </div>
	<?php if($_SESSION["ss_mb_viewType"]!="1"){?>
  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      장바구니
      <?php
      $cart_count = get_carts_by_recipient($pen['penId']);
      echo " : {$cart_count}개"
      ?>
    </div>
    <div class="cart_btn_wrap r_btn_wrap">

<!--  //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================  -->
	  <a class="c_btn" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id=10')?>">장바구니 상품 추가하기</a>
	  <!--a class="c_btn" href="javascript:;" onClick="return error_btn()">장바구니 상품 추가하기</a-->
	  <a class="c_btn primary" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/cart.php')?>"><?=$pen['penNm']?>님 장바구니 바로가기</a>
      <!--a class="c_btn primary" href="javascript:;" onClick="return error_btn()"><?=$pen['penNm']?>님 장바구니 바로가기</a-->
<!--=========================================================================================================== -->

    </div>
  </div>
	<?php }?>
  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      대여계약 급여제공기록 BETA
    </div>
	<div class="cart_btn_wrap r_btn_wrap">      
      <div class="c_month"><b>생성월 선택</b>
	  <select name="create_month" id="create_month" class="form-control input-sm" style="width:90px;display:inline;" onChange="rent_item_list()">
		<option value="<?=date("Y-m")?>" selected><?=date("Y-m")?></option>
		<option value="<?=date("Y-m",strtotime("-1 month",time()))?>"><?=date("Y-m",strtotime("-1 month",time()))?></option>
		<option value="<?=date("Y-m",strtotime("-2 month",time()))?>"><?=date("Y-m",strtotime("-2 month",time()))?></option>
		<option value="<?=date("Y-m",strtotime("-3 month",time()))?>"><?=date("Y-m",strtotime("-3 month",time()))?></option>
      </select>
	  </div>
	  <a class="c_btn primary" style="background-color:#666666;" href="javascript:rent_efrom_open();">급여제공기록 생성</a>
	  <a class="c_btn primary" href="javascript:rent_efrom_hist_open();">급여제공기록 관리</a>
    </div>
  </div>

  <div class="section_wrap" style="text-align: center; margin-bottom:30px;" id="rental_item_list">

  </div>
<?php
$sql = "SELECT MIN(a.dc_sign_datetime) as start_time FROM `eform_document` AS a 
INNER JOIN `eform_document_item` AS b ON a.dc_id = b.dc_id AND b.it_rental_price IS NOT NULL
WHERE a.dc_status='3'" ;
$row = sql_fetch($sql);
$start_date = substr($row["start_time"],0,10);
if($start_date != ""){
?>
  <div style="margin-top:-20px;">
	<font color="red">※ 대여계약 급여제공기록 서비스는 <b><?=$start_date?></b> 이후 전자서명이 완료된 계약부터 이용 하실 수 있습니다.</font>
  </div>
<?php }?>
  <div style="margin-top:0px;margin-bottom:15px;">
	<font color="#999999">해당 기능은 베타버전입니다. 사용 중에 발생하는 불편 사항이나 개선점은 고객센터에 문의 하시기 바랍니다.</font>
  </div>
  <div class="memo_wrap">
    <div class="sub_title_wrap">
      <div class="sub_title l_title">
        메모
      </div>
    </div>
    <div class="section_wrap grey">
      <div class="sub_section_wrap">
        <textarea name="memo" class="memo" rows="4"></textarea>
        <input type="submit" class="btn_write_memo c_btn primary" value="등록">
      </div>
      <?php foreach($memos as $memo) { ?>
      <div class="memo_row">
        <div class="memo_body">
          <div class="memo_date"><?=date('Y년 m월 d일', strtotime($memo['me_created_at']))?></div>
          <div class="memo_content"><?=nl2br($memo['memo'])?></div>
        </div>
        <div class="memo_btn_wrap">
          <button class="btn_edit_memo c_btn" data-id="<?=$memo['me_id']?>">수정</button>
          <button class="btn_delete_memo c_btn" data-id="<?=$memo['me_id']?>">삭제</button>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="main_btn_wrap">
      <a href="<?=G5_SHOP_URL.'/electronic_manage.php?penId='.$pen['penId']?>" class="primary">전자문서 확인</a>
      <!--a href="<?=G5_SHOP_URL.'/claim_manage.php?penId='.$pen['penId']?>" class="secondary">청구관리</a -->
      <a href="<?=G5_SHOP_URL.'/orderinquiry.php?sel_field=all&search='.$pen['penId']?>">주문내역</a>
    </div>
  </div>
  
  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      욕구사정기록지
    </div>
  </div>
  <div class="section_wrap grey">
    <div class="sub_section_wrap" style="text-align: center">
<!--  //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================  -->      
	  <a href="<?=G5_SHOP_URL."/my_recipient_rec_form.php?id={$pen['penId']}"?>" class="b_btn">신규등록</a>
	  <!--a href="javascript:;"  onClick="return error_btn()" class="b_btn">신규등록</a-->
<!--=========================================================================================================== -->
    </div>
    <?php foreach($recs as $rec) { ?>
    <div class="memo_row">
      <div class="memo_body">
        <div class="memo_date"><?=date('Y년 m월 d일', strtotime($rec['created_at']))?></div>
        <div class="memo_content"><?=nl2br($rec['total_review'])?></div>
      </div>
      <div class="memo_btn_wrap">
		<?php if($rec['type'] == 'simple') { ?>
        <button class="btn_print_rec c_btn primary" data-type="simple" data-id="<?=$rec['recId']?>">인쇄</button>
        <a href="<?=G5_SHOP_URL."/my_recipient_rec_form.php?id={$pen['penId']}&rs_id={$rec['recId']}"?>" class="c_btn" data-id="<?=$rec['recId']?>">수정</a>
        <button class="btn_delete_rec c_btn" data-type="simple" data-id="<?=$rec['recId']?>">삭제</button>
        <?php } else if($rec['type'] == 'detail') { ?>
        <button class="btn_print_rec c_btn primary" data-type="detail" data-id="<?=$rec['recId']?>">인쇄</button>
        <a href="<?=G5_SHOP_URL."/my_recipient_rec_detail_form.php?id={$pen['penId']}&rd_id={$rec['recId']}"?>" class="c_btn" data-id="<?=$rec['recId']?>">수정</a>
        <button class="btn_delete_rec c_btn" data-type="detail" data-id="<?=$rec['recId']?>">삭제</button>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>

  <div id="popup_box3" class="popup_box2">
    <div id="" class="popup_box_con">
		<div style="top:0px;width:100%;">		
		<span style="float:right;cursor:pointer;margin-top:-15px;" onClick="rent_efrom_close();" title="돌아가기" ><i class="fa fa-times"></i></span>
		</div>
		<div class="form-group" style="text-align:center;border-bottom:1px solid #333333;height:25px;">
			<span class="" style="text-align:center;font-weight:bold;font-size:16px;">대여계약 급여제공기록 생성</span>
        </div>
<form method="post" id="download_excel">
		<div class="form-group pop_con" style="margin-top:10px;">
			<input type="hidden" name="mode" value="w">
			<input type="hidden" id="penNm" value="<?=$pen['penNm']?>">
			<input type="hidden" name="penId" value="<?=$_GET['id']?>">
			<input type="hidden" name="it_ids"  id="it_ids" value="">
			<input type="hidden" name="it_dates"  id="it_dates" value="">
			<b>생성월</b><br>
			<input readonly type="text" name="create_month" id='sdate2' class="form-control input-sm" value="<?=date("Y-m")?>" style="margin-bottom:10px;">
			<b>계약기간</b><br>
			<div id="it_list" class="" style="width:100%;height:170px;overflow-y:auto;margin-bottom:10px;">
			
			</div>
			<b>수급자와의 관계</b><br>
			<div style="width:100%;height:70px;">
			<select name="contract_sign_relation" id="contract_sign_relation" class="form-control input-sm" style="width:25%;display:inline;margin-bottom:5px" onChange="relation_change()">
				<option value="0" selected>본인</option>
				<option value="1">가족</option>	
				<option value="2">친족</option>	
				<option value="3">기타</option>	
			</select>
			<input type="text" name="pen_guardian_nm" id="pen_guardian_nm" class="form-control input-sm" style="width:70%;display:inline;margin-left:3%;margin-bottom:5px;" value="<?=$pen['penNm']?>" maxlength="4" placeholder="성명을 입력하세요." readonly>
			<input type="text" name="contract_sign_relation_nm" id="contract_sign_relation_nm" class="form-control input-sm" style="width:70%;display:inline;margin-bottom:10px;margin-left:29%;display:none;" value="" maxlength="7" placeholder="기타관계를 입력하세요.">
			</div>
			<b>특이사항</b><br>
			<textarea name="entConAcc" rows="3" cols="" class="form-control input-sm" placeholder="100자까지 입력 가능합니다." style="resize: none;margin-bottom:10px;" maxlength="100" id="textBox"></textarea>
			<b>확인사항</b>&nbsp;
			<select name="penRecTypeCd" id="penRecTypeCd" class="form-control input-sm" style="width:20%;display:inline;">
				<option value="01" selected>유선</option>
				<option value="02">방문</option>				
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>확인일시</b>&nbsp;
			<input type="text" name="confirm_date" id="confirm_date" class="form-control input-sm" style="width:30%;display:inline;" value="<?=date("Y-m-d")?>" readonly>			
        </div>	
		<div style="margin-top:10px;text-align:center;border-top:1px solid #333333;height:40px;padding-top:10px;">
			<input type="button" value="등록" onclick="return downloadExcel('w')" style="height:30px;line-height:30px !important;padding: 0 13px;vertical-align: top;font-weight: bold;letter-spacing: -1px;background-color: white;border: 1px solid #b5b5b5;color: black !important;">
        </div>
</form>
	</div>	
</div>

<div id="popup_box4" class="popup_box2">
    <div id="" class="popup_box_con2" >
		<form method="post" id='download_excel2'>
			<input type="hidden" name="mode" value="m">
			<input type="hidden" name="rh_id" id="rh_id" value="">
		</form>
		<div style="top:0px;width:100%;">		
		<span style="float:right;cursor:pointer;margin-top:-15px;" onClick="rent_efrom_close();" title="돌아가기" ><i class="fa fa-times"></i></span>
		</div>
		<div class="form-group" style="text-align:center;border-bottom:1px solid #333333;height:40px;">
			<span class="" style="text-align:center;font-weight:bold;font-size:18px;">대여계약 급여제공기록 관리</span>
        </div>

		<div class="form-group section_wrap" id="eform_rent_hist" style="border:0px; padding: 0px; height:460px; overflow-x:auto;border-radius: 0px;">
		<!-- 대여계약 급여제공기록 이력 리스트 -->
        </div>	
		<div style="margin-top:10px;text-align:left;border-top:1px solid #333333;height:40px;padding-top:10px;" class="pop_tail">
			생성된 급여제공기록 정보는 계약 종료 날짜 기준 다음달 1일에 자동 삭제됩니다.(단, 전자서명은 제외)<br>
			<font color="red">※ 계약기간(종료일)이 계약생성일보다 이전인 경우 계약생성일 기준으로 다음달 1일에 삭제됩니다.</font>
        </div>

	</div>	
</div>

<div id="popup_box2" class="popup_box2" >
    <div id="" class="popup_box_con2" style="height: 410px;margin-top: -205px; width: 350px; margin-left: -175px; padding:30px;">
		<form method="post">
			<input type="hidden" name="mode" value="m">
			<input type="hidden" name="rh_id" id="rh_id" value="">
		</form>
		<div style="top:0px;width:100%;">		
		<span style="float:right;cursor:pointer;margin-top:-15px;" onClick="rent_efrom_close();" title="돌아가기" ><i class="fa fa-times"></i></span>
		</div>
		<div class="form-group" style="text-align:left;height:60px;">
			<span style="text-align:left;font-weight:bold;font-size:18px;">요양정보</span><br>
			<span style="text-align:left;font-size:13px;">변경사항이 있을 경우 수정 후 업데이트해 주세요.</span>
        </div>

		<div class="form-group" style="text-align:left;height:30px;">
			<div class="" style="text-align:left;font-size:14px; width:130px;float:left;">요양인정번호</div>
			<div class="" style="text-align:left;font-size:14px;"><?=$pen['penLtmNum']?></div>
        </div>
		
		<div class="form-group" style="text-align:left;height:30px;">
			<span class="" style="text-align:left;font-size:14px; width:130px;float:left;">생년월일</span>
			<div class="" style="text-align:left;font-size:14px;"><?=$pen['penBirth']?></div>
        </div>
		<div class="form-group" style="text-align:left;height:30px;">
			<div class="" style="text-align:left;font-size:14px; width:130px;float:left;">수급자명</div>
			<div class="" style="text-align:left;font-size:14px;"><input type="text" id="penNm2" value="<?=$pen['penNm']?>" style="width:155px;border:1px solid;" class="input-sm"></div>
        </div>
		<div class="form-group" style="text-align:left;height:30px;">
			<span class="" style="text-align:left;font-size:14px; width:130px;float:left;">인정등급</span>
			<div class="" style="text-align:left;font-size:14px;"><select name="penRecGraCd" id="penRecGraCd" class="input-sm" style="width: 155px;border:1px solid;">
					<option value="" >선택</option>
					<option value="01" <?=($pen['penRecGraCd'] == "01")? "selected":"";?>>1등급</option>
					<option value="02" <?=($pen['penRecGraCd'] == "02")? "selected":"";?>>2등급</option>
					<option value="03" <?=($pen['penRecGraCd'] == "03")? "selected":"";?>>3등급</option>
					<option value="04" <?=($pen['penRecGraCd'] == "04")? "selected":"";?>>4등급</option>
					<option value="05" <?=($pen['penRecGraCd'] == "05")? "selected":"";?>>5등급</option>
					<option value="06" <?=($pen['penRecGraCd'] == "06")? "selected":"";?>>6등급</option>
				  </select></div>
        </div>
		<div class="form-group" style="text-align:left;height:30px;">
			<span class="" style="text-align:left;font-size:14px; width:130px;float:left;line-height:17px;">인정유효기간<br><font size="2">(시작일자)</font></span>
			<div class="" style="text-align:left;font-size:14px;"><input type="text" id="penExpiStDtm" name="penExpiStDtm" value="<?=substr($pen['penExpiStDtm'],0,4)."-".substr($pen['penExpiStDtm'],4,2)."-".substr($pen['penExpiStDtm'],6,2)?>" style="width:155px;border:1px solid;" class="input-sm" readonly></div>
        </div>

	
		<div style="margin-top:20px;text-align:center;height:40px;padding-top:10px;" class="pop_tail">
			<input type="button" value="취소" onClick="rent_efrom_close();" style="width:49%;padding: 10px 15px;vertical-align: top;background-color: white;border: 1px solid #ee8102;color: #ee8102 !important;border-radius: 3px;">
			<input type="button" value="업데이트" id="btn_so_sch2" style="width:49%;padding: 10px 15px;vertical-align: top;background-color: #ee8102;border: 1px solid #ee8102; color: #fff; !important;border-radius: 3px;">
        </div>

	</div>	
</div>

  <style>
  .list-more a {
    margin-top:50px;
    font-weight: 400;
    padding: 9px 49px;
    display: inline-block;
    font-size: 14px;
    color: #232323;
    border: 1px solid #ddd;
  }
  </style>

  <div class="list-more">
    <p><a href="javascript:void(0)" id="delete_recipient">수급자 삭제</a></p>
    <p>
      *대여중인 품목이 있는 수급자는 삭제 할 수 없습니다.<br/>
      *삭제된 수급자 정보는 복구 할 수 없습니다.
    </p>
  </div>
</div>

<!-- 인쇄 팝업 -->
<div id="popup_rec">
  <div></div>
</div>
<!-- 엑셀 다운로드 -->
<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>
<?php
$t_recipient_order = get_tutorial('recipient_order');
$t_recipient_add = get_tutorial('recipient_add');
if ($t_recipient_order['t_state'] == '0' && $t_recipient_add['t_data'] == $pen['penId']) {
?>
<script>
show_eroumcare_popup({
  title: '수급자 주문하기',
  content: '수급자 주문을 체험하시겠습니까?<br/>판매품목 1개, 대여품목1개<br/>선택되어 주문을 체험할 수 있습니다.',
  activeBtn: {
    text: '\'<?php echo htmlspecialchars($pen['penNm']); ?>\' 수급자로 주문체험하기',
    href: '/shop/tutorial_order.php'
  },
  hideBtn: {
    text: '다음에',
  }
});

</script>
<?php
} 
?>

<?php include_once('./popup_sign_send2.php');?>


<script>
function relation_change(){
	$("#contract_sign_relation_nm").css("display","none");
	$("#contract_sign_relation_nm").val("");
	$("#pen_guardian_nm").val("");
	$("#pen_guardian_nm").attr("readonly",false);
	if($("#contract_sign_relation").val() == "0"){//본인
		$("#pen_guardian_nm").attr("readonly",true);
		$("#pen_guardian_nm").val($("#penNm").val());
	}else if($("#contract_sign_relation").val() == "3"){//기타
		$("#contract_sign_relation_nm").css("display","block");
	}
}

function redirect_item(href) {
    location.href = href;
}

function rent_efrom_open(){//대여계약 급여제공기록 생성 팝업
	var it_id = [];
	var it_date = [];
	var item = $("input[name='it_id[]']:checked");
    var html_val = "";
	for(var i = 0; i < item.length; i++) {
		it_id.push($(item[i]).val());
		it_date.push($(item[i]).data('date1')+"~"+$(item[i]).data('date2'));
		$(item[i]).data('name')
		if(i != 0){
			html_val += "<br>";
		}	
		html_val += $(item[i]).data('name')+"<br><input type='text' name='' value='"+$(item[i]).data('date1')+"' class='form-control input-sm' style='width:47%;display:inline;' readonly> ~ <input type='text' name='' value='"+$(item[i]).data('date2')+"' class='form-control input-sm' style='width:47%;display:inline;' readonly>";
	}
	
    if(!it_id.length) {
		alert('계약 상품을 선택하세요.');
		return false;
    }

	$("#sdate2").val($("#create_month").val());
	$("#it_list").html(html_val);
	$("#it_ids").val(it_id);
	$("#it_dates").val(it_date);
	$('body').addClass('modal-open');
	$('#popup_box3').show();
}

function rent_efrom_hist_open(){//대여계약 급여제공기록 생성 팝업
	$("#eform_rent_hist").html("");
	$.post('ajax.eform_rent_hist.php', {
	  id: '<?=$_GET["id"]?>',
    }, 'json')
    .done(function(data) {
      if(data.message){
		  alert(data.message);
	  }else{
		  $("#eform_rent_hist").html(data.html); 
		  $('body').addClass('modal-open');
		  $('#popup_box4').show();
	  }
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });	
}

function rent_efrom_close(){
	$('#popup_box2').hide();
	$('#popup_box3').hide();
	$('#popup_box4').hide();
	$('body').removeClass('modal-open');
}

function rent_item_list(){
	var c_month = $("#create_month").val();
	$("#rental_item_list").html("");
	$.post('ajax.rental_item_list.php', {
      c_month: c_month,
	  id: '<?=$_GET["id"]?>',
    }, 'json')
    .done(function(data) {
      if(data.message){
		  alert(data.message);
	  }else{
		  $("#rental_item_list").html(data.html); 
	  }
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
}

function downloadExcel(mode,rh_id1=false) {
    if($("#contract_sign_relation").val() != "0" && mode == "w"){//본인이 아닐경우,생성 시
		if($("#pen_guardian_nm").val() == ""){//보호자 성명이 없을 경우
			alert("성명을 입력하세요.");
			$("#pen_guardian_nm").focus();
			return false;
		}
		if($("#contract_sign_relation_nm").val() == "" && $("#contract_sign_relation").val() == "3"){//기타관계가 없을 경우
			alert("기타관계를 입력하세요.");
			$("#contract_sign_relation_nm").focus();
			return false;
		}
	}	
	
	var href = './rental_item_eform.excel.download.php';
	if(rh_id1 != false){
		$("#rh_id").val(rh_id1);
	}
	var datas = (mode=='w')? $("#download_excel").serialize() : $("#download_excel2").serialize();
    //alert(JSON.stringify(datas));
	var mobile_yn = '<?=$mobile_yn?>';
	if(mobile_yn != "Pc"){
		if(mode=='w'){
			$("#download_excel").attr("action",href).submit();
			rent_item_list();
			rent_efrom_close();
			alert("급여제공기록지가 생성되었습니다.\n급여제공기록 관리에서 전자서명을 진행할 수 있습니다.");
		}else{
			$("#download_excel2").attr("action",href).submit();			
		}
	}else{	
		$('#loading_excel').show();
		EXCEL_DOWNLOADER = $.fileDownload(href, {
		  httpMethod: "POST",
		  data: datas
		})
		.always(function() {
			$('#loading_excel').hide();
			if(mode=='w'){
				rent_item_list();
				rent_efrom_close();
				alert("급여제공기록지가 생성되었습니다.\n급여제공기록 관리에서 전자서명을 진행할 수 있습니다.");
			}
		});
	}
}

function cancelExcelDownload() {
    if (EXCEL_DOWNLOADER != null) {
      EXCEL_DOWNLOADER.abort();
    }
    $('#loading_excel').hide();
}
  
$(function() {
	rent_item_list();//생성월 선택

	$('#textBox').keyup(function (e) {
		let content = $(this).val();		
		// 글자수 제한
		if (content.length > 101) {
			// 100자 부터는 타이핑 되지 않도록
			$(this).val($(this).val().substring(0, 101));
			// 100자 넘으면 알림창 뜨도록
			alert('글자수는 101자까지 입력 가능합니다.');
		};
	});

  $(document).on('click', '#delete_recipient', function() {    
    if(!confirm('삭제된 수급자 정보는 복구 할 수 없습니다.\r\n삭제 하시겠습니까?')) {
      return;
    }

    $.post('ajax.my.recipient.delete.php', {
      id: '<?=$pen['penId']?>',
    }, 'json')
    .done(function() {
      alert('삭제되었습니다.');
      location.href = './my_recipient_list.php';
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
    
  });

  // 메모 작성
  $(document).on('click', '.btn_write_memo', function() {
    var val = $(this).closest('div').find('.memo').val();
    $.post('ajax.my.recipient.memo.php', {
      id: '<?=$pen['penId']?>',
      memo: val
    }, 'json')
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 메모 수정 등록
  $(document).on('click', '.btn_update_memo', function() {
    var val = $(this).closest('div').find('.memo').val();
    var me_id = $(this).data('id');

    $.post('ajax.my.recipient.memo.php', {
      id: '<?=$pen['penId']?>',
      me_id: me_id,
      memo: val
    }, 'json')
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 메모 수정 취소
  $(document).on('click', '.btn_cancel_memo', function() {
    var $row = $(this).closest('.memo_row');

    $row.find('.memo_body').show();
    $row.find('.memo_btn_wrap').show();
    $row.find('.edit_memo_wrap').remove();
  });

  // 메모 수정
  $(document).on('click', '.btn_edit_memo', function() {
    var $row = $(this).closest('.memo_row');
    var val = $row.find('.memo_content').text();
    var me_id = $(this).data('id');

    $row.find('.memo_body').hide();
    $row.find('.memo_btn_wrap').hide();
    $('\
      <div class="edit_memo_wrap sub_section_wrap">\
        <textarea name="memo" class="memo" rows="4">'+val+'</textarea>\
        <input type="submit" class="btn_update_memo c_btn primary" data-id="'+me_id+'" value="등록">\
        <input type="button" class="btn_cancel_memo c_btn" value="취소">\
      </div>\
    ')
    .appendTo($row);
  });

  // 메모 삭제
  $(document).on('click', '.btn_delete_memo', function() {
    var me_id = $(this).data('id');

    if(confirm('메모를 삭제하시겠습니까?')) {
      $.post('ajax.my.recipient.memo.php', {
        id: '<?=$pen['penId']?>',
        me_id: me_id,
        del: true
      }, 'json')
      .done(function() {
        location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    }
  });

  // 욕구사정기록지 삭제
  $(document).on('click', '.btn_delete_rec', function() {
    var recId = $(this).data('id');
    var type = $(this).data('type');

    if(confirm('욕구사정기록지를 삭제하시겠습니까?')) {
      $.post('ajax.my.recipient.rec.delete.php', {
        penId: '<?=$pen['penId']?>',
        recId: rs_id,
        type: type
      }, 'json')
      .done(function() {
        location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    }
  });

  // 욕구사정기록지 인쇄
  $(document).on('click', '.btn_print_rec', function() {
    var recId = $(this).data('id');
    var type = $(this).data('type');

    $("#popup_rec > div").html("<iframe src='my_recipient_rec_print.php?id=<?=$pen['penId']?>&type="+type+"&recId="+recId+"'>");
    $("#popup_rec iframe").removeClass('mini');
    $("#popup_rec iframe").load(function() {
      $("html,body").addClass('modal-open');
      $("#popup_rec").show();
    });
  });
});
</script>
<?php @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');?>
<?php include_once("./_tail.php"); ?>
