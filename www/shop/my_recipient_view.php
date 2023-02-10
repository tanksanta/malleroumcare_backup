<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

include_once("./_head.php");

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
	if(file_exists($upload_dir.$file_name.".enc")){
		$is_file = true;
	}
}
//인증서 업로드 추가 영역 끝
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

            <button type="button" class="btn_so_sch" id="btn_so_sch">요양정보 </br>업데이트</button>
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
					$(".btn_so_sch").trigger("click");
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
  let ct_history_list = [];
  // 품목찾기 팝업
  $('#item_popup_box').click(function() {
    $('body').removeClass('modal-open');
    $('#item_popup_box').hide();
  });
  $('.btn_so_sch').click(function(e) {
    //var url = 'pop.recipient_info.php?id=<?=$pen['penId']?>&penNm=<?=$pen['penNm']?>&penLtmNum=<?=$pen['penLtmNum']?>';
    //var url = 'pop_recipient.php';
    //$('#item_popup_box iframe').attr('src', url);
    //$('body').addClass('modal-open');
    //$('#item_popup_box').hide();

      var pen_info = <?=json_encode($pen);?>;
      console.log("pen_info : ", pen_info);

      var str_rn = "<?=$pen['penNm'] ?>"; //$("input[name='penNm']")[0].value;
      var str_id = "<?=$pen['penLtmNum'] ?>";  //$("input[name='penLtmNum']")[0].value;
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
		  
		  
		        $.post('./ajax.my.recipient.setItem.php', {
		          penId: "<?=$pen['penId'] ?>",
		          itemList: itemList
		        }, 'json')
		        .done(function(result) {
		          if(result.errorYN == "Y") {
		            alert(result.message);
		          } else {
		            alert('완료되었습니다');
		            window.location.href = "./my_recipient_view.php?id="+data.penId;
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
@media (max-width: 480px){
    .btn_so_sch { height: fit-content; font-size: small; }
    .btn_so_edit { height: fit-content; font-size: small; }
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

  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      장바구니
      <?php
      $cart_count = get_carts_by_recipient($pen['penId']);
      echo " : {$cart_count}개"
      ?>
    </div>
    <div class="cart_btn_wrap r_btn_wrap">
      <a class="c_btn" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id=10')?>">장바구니 상품 추가하기</a>
      <a class="c_btn primary" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/cart.php')?>"><?=$pen['penNm']?>님 장바구니 바로가기</a>
    </div>
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
      <a href="<?=G5_SHOP_URL."/my_recipient_rec_form.php?id={$pen['penId']}"?>" class="b_btn">신규등록</a>
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

<script>
  function redirect_item(href) {
      location.href = href;
    }
  
$(function() {

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

<?php include_once("./_tail.php"); ?>
