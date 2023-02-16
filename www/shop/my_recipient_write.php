
<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

include_once("./_head.php");

# 회원검사
if(!$member["mb_id"]){
  alert("접근 권한이 없습니다.");
  return false;
}

// 튜토리얼 검사
$t_recipient_add = get_tutorial('recipient_add');
if ($t_recipient_add['t_state'] == '1' && $tutorial == 'true') {
  alert('이미 완료한 튜토리얼입니다.\r\n다음단계를 진행하세요.', '/');
}

$sql_check = "
  show columns from macro_request where field in ('rem_amount','penExpiDtm','penApplyDtm','bathingChair','safetyHandGrip','sliveryPreventSocks','safetyPreventSlivery','simpleToilet','cane','cushionPreventMatriss','postureChangeTool','bedsorePreventMatriss','adultWalker','runway','movingToilet','incontinencePanty','mWheelChair','eBed','mBed','lendBedsorePreventionMatriss','portableBath','bathLift','loiteringDetection','lendRunway');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table macro_request ".
                "add column bathingChair varchar(10) default '1' after percent,".
                "add column safetyHandGrip varchar(10) default '10' after percent,".
                "add column sliveryPreventSocks varchar(10) default '6' after percent,".
                "add column safetyPreventSlivery varchar(10) default '5' after percent,".
                "add column simpleToilet varchar(10) default '2' after percent,".
                "add column cane varchar(10) default '1' after percent,".
                "add column cushionPreventMatriss varchar(10) default '1' after percent,".
                "add column postureChangeTool varchar(10) default '5' after percent,".
                "add column bedsorePreventMatriss varchar(10) default '1' after percent,".
                "add column adultWalker varchar(10) default '2' after percent,".
                "add column runway varchar(10) default '6' after percent,".
                "add column movingToilet varchar(10) default '1' after percent,".
                "add column incontinencePanty varchar(10) default '4' after percent,".
                "add column mWheelChair varchar(10) default '1' after percent,".
                "add column eBed varchar(10) default '1' after percent,".
                "add column mBed varchar(10) default '1' after percent,".
                "add column lendBedsorePreventionMatriss varchar(10) default '1' after percent,".
                "add column portableBath varchar(10) default '1' after percent,".
                "add column bathLift varchar(10) default '1' after percent,".
                "add column loiteringDetection varchar(10) default '1' after percent,".
                "add column lendRunway varchar(10) default '1' after percent,".
                "add column rem_amount varchar(30) default '1600000' after percent, ".
                "add column penExpiDtm varchar(30) default null after percent, ".
                "add column penApplyDtm varchar(30) default null after percent";
  sql_query($append_col);
}

$eng_name = ['이동변기01'=>'movingToilet','목욕의자01'=>'bathingChair','안전손잡이01'=>'safetyHandGrip',
'미끄럼방지용품(양말)01'=>'sliveryPreventSocks','경사로(실외용)00'=>'lendRunway','수동침대00'=>'mBed',
'요실금팬티01'=>'incontinencePanty','간이변기01'=>'simpleToilet','전동침대00'=>'eBed','지팡이01'=>'cane',
'욕창예방매트리스00'=>'lendBedsorePreventionMatriss','욕창예방매트리스01'=>'bedsorePreventMatriss',
'이동욕조00'=>'portableBath','목욕리프트00'=>'bathLift','미끄럼방지용품(매트)01'=>'safetyPreventSlivery',
'자세변환용구01'=>'postureChangeTool','성인용보행기01'=>'adultWalker','배회감지기00'=>'loiteringDetection',
'욕창예방방석01'=>'cushionPreventMatriss','경사로(실내용)01'=>'runway','수동휠체어00'=>'mWheelChair'];

$item_code = ['ITM2020092200001'=>'movingToilet','ITM2020092200002'=>'bathingChair','ITM2020092200014'=>'mBed',
'ITM2020092200004'=>'safetyHandGrip','ITM2020092200005'=>'sliveryPreventSocks','ITM2020092200018'=>'lendRunway',
'ITM2020092200011'=>'incontinencePanty','ITM2020092200007'=>'simpleToilet','ITM2020092200008'=>'cane',
'ITM2020092200019'=>'lendBedsorePreventionMatriss','ITM2020092200013'=>'eBed','ITM2020092200012'=>'mWheelChair',
'ITM2020092200015'=>'portableBath','ITM2020092200016'=>'bathLift','ITM2020092200006'=>'safetyPreventSlivery',
'ITM2020092200010'=>'postureChangeTool','ITM2020092200003'=>'adultWalker','ITM2020092200017'=>'loiteringDetection',
'ITM2020092200009'=>'cushionPreventMatriss','ITM2020092200020'=>'bedsorePreventMatriss','ITM2021010800001'=>'runway'];

// macro_request 불러오기
$item_sql = "select * from macro_request where recipient_num = '".$_POST['recipient_num']."';";
$item_res = sql_query($item_sql);

$item_arr = [];
$macro_req = [];
for ($j=0; $row=sql_fetch_array($item_res); $j++){
  $macro_req = $row;
  for ($i=0; $i < sizeof($item_code); $i++){
    $item_arr[array_keys($item_code)[$i]] = $macro_req[array_values($item_code)[$i]];
  }
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

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script src="<?=G5_JS_URL?>/jquery.register_form.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<style>
#ui-datepicker-div { z-index: 999 !important; }

#zipAddrPopupWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#zipAddrPopupWrap > div { position: relative; width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#zipAddrPopupWrap > div > div { position: relative; width: 700px; height: 500px; background-color: #FFF; padding-top: 50px; left: 50%; margin-left: -350px; }
#zipAddrPopupWrap #zipAddrPopupIframe { position: relative; width: 100%; height: 100%; float: left; border: 0; background-color: #FFF; border-top: 1px solid #DDD; }
#zipAddrPopupWrap .closeBtn { position: absolute; font-size: 32px; color: #AAA; top: 10px; right: 10px; cursor: pointer; }

.panel-heading.clear:after { display: block; content: ' '; clear:both; }
.panel-heading .l-heading-wrap { float: left; }
.panel-heading .r-heading-wrap { float: right; }
.panel-heading .r-heading-wrap .checkbox-inline { padding-top: 0; padding-left: 0; }

@media (max-width : 750px){
  #zipAddrPopupWrap > div > div { width: 100%; height: 100%; left: 0; margin-left: 0; }
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.has-feedback .form-control {
  padding-right: 0px;
}

.panel_pro_add {
  position: relative;
  border-top: 1px solid #ddd;
}

.btn_pro_del {
  position: absolute;
  display: block;
  text-align: center;
  font-size: 14px;
  color: #333;
  background: #fff;
  padding: 4px 20px;
  top: 10px;
  right: 10px;
  border: 1px solid #ddd;
  cursor: pointer;
  z-index: 5;
}

.head_title {
  margin : 30px 0px;
  font-size : 30px;
  font-weight : bold;
}

.req_input {
  float: right;
}

#asterisk {
  color: #f00;
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

<div id="zipAddrPopupWrap">
  <div>
    <div>
      <i class="fa fa-times-circle closeBtn" onclick="zipPopupClose();"></i>
      <div id="zipAddrPopupIframe"></div>
    </div>
  </div>
</div>
<p class = "head_title"> 수급자 등록</p>
<form class="form-horizontal register-form">
  <input type="hidden" maxlength="6" name="tutorial" class="form-control input-sm" value="<?php echo $tutorial ? 1 : 0; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><strong>기본정보</strong><strong class="req_input">필수입력</strong><strong class="req_input" id="asterisk">*</strong></div>
    <div class="panel-body">
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>수급자명</b><strong id="asterisk">*</strong>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penNm" value="<?=get_text($_GET['penNm']) ?: ''?>" class="form-control input-sm">
          <i class="fa fa-check form-control-feedback"></i>
        </div>
        <div class="col-sm-3" style="display: flex">
          <label class="checkbox-inline dealing" style="margin-left: 0px; width:146px; padding: 5px 0px;">
            <!-- 예비수급자 체크버튼 -->
            <!-- <input disabled type="checkbox" class="chk_rep_spare" name="penSpare" value="0" >예비수급자 -->
          </label>
        </div>
      </div>

      <div id="panel_ltm">
          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>장기요양인정번호</b><strong id="asterisk">*</strong>
            </label>
            <div class="col-sm-4"  style="display: flex">
              <span style="float: left; width: 10px; height: 30px; line-height: 30px; margin-right: 5px;">L</span>

              <input type="number" maxlength="10" oninput="maxLengthCheck(this)" id="penLtmNum" name="penLtmNum" class="form-control input-sm" style="width: calc(100% - 15px);" value="<?=preg_replace("/[^0-9]*/s", "", get_text($_GET['penLtmNum'])) ?: ''?>">

              <button type="button" id="btn_pen_update" class="btn btn-color btn-sm" style="margin-left: 15px;">요양정보 조회</button>
            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>인정등급</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="text" name="penRecGraCd" value="<?=get_text($_GET['penRecGraCd']) ?: ''?>" class="form-control input-sm">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>대상자구분</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="text" name="penTypeCd" value="<?=get_text($_GET['penTypeCd']) ?: ''?>" class="form-control input-sm">
              <input type="hidden" id="SbaCd" name="SbaCd" value="<?=get_text($_GET['SbaCd']) ?: ''?>">
            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>본인부담률</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="text" name="penPayRate" value="<?=get_text($_GET['penPayRate']) ?: ''?>" class="form-control input-sm">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>유효기간</b>
            </label>
            <div class="col-sm-4">

              <input readonly type="text" name="penExpiStDtm" class="form-control input-sm" dateonly2 style="display: inline-block;width:47%;" value="<?=get_text($_GET['penExpiStDtm']) ?: ''?>"> ~
              <input readonly type="text" name="penExpiEdDtm" class="form-control input-sm" dateonly style="display: inline-block;width:48%;" value="<?=get_text($_GET['penExpiEdDtm']) ?: ''?>">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>적용기간</b>
            </label>
            <div class="col-sm-4">

              <input readonly type="text" name="penApplyStDtm" class="form-control input-sm" dateonly2 style="display: inline-block;width:47%;" value="<?=get_text($_GET['penExpiStDtm']) ?: ''?>"> ~
              <input readonly type="text" name="penApplyEdDtm" class="form-control input-sm" dateonly style="display: inline-block;width:48%;" value="<?=get_text($_GET['penExpiEdDtm']) ?: ''?>">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>생년월일</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="number" maxlength="8" oninput="maxLengthCheck(this)" id="penBirth" name="penBirth" min="0"  class="form-control input-sm" value="<?=get_text(str_replace('.','',$_GET['penBirth']))?>">
              <input type="hidden" maxlength="6" oninput="maxLengthCheck(this)" id="penJumin1" name="penJumin1" min="0"  class="form-control input-sm" value="<?=get_text($_GET['penJumin'])?>">
              <input type="hidden" id="BDay" name="BDay" value="<?=get_text(str_replace('.','',$_GET['penBirth'])) ?: ''?>">

            </div>
          </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>성별</b><strong id="asterisk">*</strong>
        </label>
        <div class="col-sm-3">
          <label class="checkbox-inline">
            <input type="radio" name="penGender" value="남" style="vertical-align: middle; margin: 0 5px 0 0;" checked>남
          </label>

          <label class="checkbox-inline">
            <input type="radio" name="penGender" value="여" style="vertical-align: middle; margin: 0 5px 0 0;">여
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>휴대폰</b><strong id="asterisk">*</strong>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penConNum" value="<?=get_text($_GET['penConNum']) ?: ''?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>일반전화</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penConPnum" value="" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback" style="margin-bottom: 0;">
        <label class="col-sm-2 control-label">
          <b>주소</b>
        </label>

        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호</label>
          <label>
            <input type="text" name="penZip" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
          </label>

          <div class="addr-line" style="margin-bottom: 5px;">
            <label class="sound_only">기본주소</label>
            <input type="text" name="penAddr" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
          </div>

          <div class="addr-line">
            <label class="sound_only">상세주소</label>
            <input type="text" name="penAddrDtl" class="form-control input-sm" placeholder="상세주소">
          </div>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>담당직원정보</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="entUsrId" class="form-control input-sm" value="<?=$member['mb_giup_boss_name']?>" placeholder="담당직원정보">
        </div>
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading clear">
      <div class="l-heading-wrap"><strong>보호자정보</strong></div>
      <div class="r-heading-wrap">
        <button type="button" id="btn_pro_add" class="btn btn-color" style="padding: 4px 20px">추가</button>
      </div>
    </div>
    <div id="panel_pro" class="panel-body">
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>분류</b>
        </label>
        <div class="col-sm-3">
          <label class="checkbox-inline">
            <input type="radio" class="radio_pro_type" name="penProTypeCd" value="01" style="vertical-align: middle; margin: 0 5px 0 0;" checked>일반보호자
          </label>
          <label class="checkbox-inline">
            <input type="radio" class="radio_pro_type" name="penProTypeCd" value="02" style="vertical-align: middle; margin: 0 5px 0 0;">요양보호사
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b class="pro_rel_title">관계</b>
        </label>
        <div class="col-sm-3">
          <select class="form-control input-sm penProRel" name="penProRel" style="margin-bottom: 5px;">
            <option value="00">처</option>
            <option value="01">남편</option>
            <option value="02">자</option>
            <option value="03">자부</option>
            <option value="04">사위</option>
            <option value="05">형제</option>
            <option value="06">자매</option>
            <option value="07">손</option>
            <option value="08">배우자 형제자매</option>
            <option value="09">외손</option>
            <option value="10">부모</option>
            <option value="11">직접입력</option>
          </select>
          <input type="text" name="penProRelEtc" class="penProRelEtc form-control input-sm" readonly>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>보호자명</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProNm" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>생년월일</b>
        </label>
        <div class="col-sm-3">
          <select name="penProBirth1"  title="년도" class="form-control input-sm year"  style="display:inline-block;width:32%;"></select>
          <select name="penProBirth2"  title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
          <select name="penProBirth3"  title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>이메일</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProEmail" class="form-control input-sm">
        </div>
      </div>


      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>휴대폰</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProConNum" value="<?=get_text($_GET['penProConNum']) ?: ''?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>일반전화</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProConPnum" value="" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>주소</b>
        </label>

        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호</label>
          <label>
            <input type="text" name="penProZip" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
          </label>

          <div class="addr-line" style="margin-bottom: 5px;">
            <label class="sound_only">기본주소</label>
            <input type="text" name="penProAddr" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
          </div>

          <div class="addr-line">
            <label class="sound_only">상세주소</label>
            <input type="text" name="penProAddrDtl" class="form-control input-sm" placeholder="상세주소">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading"><strong>장기요양급여 제공기록지</strong></div>
    <div class="panel-body">
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>보호자</b>
        </label>
        <div class="col-sm-3">
          <label class="checkbox-inline">
            <input type="radio" name="penCnmTypeCd" value="00" style="vertical-align: middle; margin: 0 5px 0 0;" checked>수급자
          </label>

          <label class="checkbox-inline">
            <input type="radio" name="penCnmTypeCd" value="01" style="vertical-align: middle; margin: 0 5px 0 0;">보호자
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>수령방법</b>
        </label>
        <div class="col-sm-3">
          <select class="form-control input-sm" style="margin-bottom: 5px;" name="penRecTypeCd">
            <option value="00">방문</option>
            <option value="01">유선</option>
          </select>
          <input type="text" name="penRecTypeTxt" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>특이사항</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penRemark" class="form-control input-sm">
        </div>
      </div>

    </div>
  </div>

  <!-- 20210307 성훈작업 -->
  <style media="screen">
  input[type="checkbox"], input[type=checkbox] {margin: 4px 0 0; margin-top: 1px \9;line-height: normal;}
  .col-dealing{ width:80%; text-align: left;}
  .dealing{  margin-left: 0px;}
  </style>
  <?php $sale_ids = array(); $rent_ids = array();?>

  <div id="panel_product" class="panel panel-default">
    <div class="panel-heading"><strong>취급가능 품목</strong></div>
    <div class="panel-body">
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>판매품목</b>
        </label>
        <div class="col-sm-3 col-dealing">
          <?php
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
          for($i=1; $i<14; $i++) {
              $sale_ids[${'sale_product_name'. $i}] = ${'sale_product_id'.$i};
          ?>
          <label class="checkbox-inline dealing" id="sale" style="margin-left: 0px; width:146px;">
            <input disabled type="checkbox" class="chk_sale_product chk_sale_product_child" name="<?=${'sale_product_id'.$i}; ?>" id="<?="sale_product_id".$i; ?>" value="<?=${'sale_product_id'.$i}; ?>" style="" ><?=${'sale_product_name'. $i}; ?>
          </label>
          <?php } ?>
        </div>
      </div>


      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>대여품목</b>
        </label>
        <div class="col-sm-3 col-dealing">
          <?php
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
          ?>
          <label class="checkbox-inline dealing" id="rental" style="margin-left: 0px; width:146px;">
            <input disabled type="checkbox" class="chk_sale_product chk_sale_product_child" name="<?=${'rental_product_id'. $i}; ?>" id="<?='rental_product_id'.$i; ?>" value="<?=${'rental_product_id'. $i}; ?>" style="" ><?=${'rental_product_name'. $i}; ?>
          </label>
        <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <!-- 20210307 성훈작업 -->
  <div class="text-center" style="margin-top: 30px;">
    <button type="button" id="btn_submit" class="btn btn-color">등록</button>
    <a href="./my_recipient_list.php" class="btn btn-black" role="button">취소</a>
  </div>
</form>

<div id="panel_pro_template" class="panel-body" style="display: none;">
  <button type="button" class="btn_pro_del">삭제</button>
  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>분류</b>
    </label>
    <div class="col-sm-3">
      <label class="checkbox-inline">
        <input type="radio" name="pro_type" value="01" style="vertical-align: middle; margin: 0 5px 0 0;" checked="checked">일반보호자
      </label>

      <label class="checkbox-inline">
        <input type="radio" name="pro_type" value="02" style="vertical-align: middle; margin: 0 5px 0 0;">요양보호사
      </label>
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b class="pro_rel_title">관계</b>
    </label>
    <div class="col-sm-3">
      <select class="form-control input-sm penProRel" name="pro_rel_type" style="margin-bottom: 5px;">
        <option value="00">처</option>
        <option value="01">남편</option>
        <option value="02">자</option>
        <option value="03">자부</option>
        <option value="04">사위</option>
        <option value="05">형제</option>
        <option value="06">자매</option>
        <option value="07">손</option>
        <option value="08">배우자 형제자매</option>
        <option value="09">외손</option>
        <option value="10">부모</option>
        <option value="11">직접입력</option>
      </select>
      <input type="text" name="pro_rel" class="penProRelEtc form-control input-sm" readonly>
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>보호자명</b>
    </label>
    <div class="col-sm-3">
      <input type="text" name="pro_name" class="form-control input-sm">
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>생년월일</b>
    </label>
    <div class="col-sm-3">
      <select name="pro_birth1" title="년도" class="form-control input-sm year"  style="display:inline-block;width:32%;"></select>
      <select name="pro_birth2" title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
      <select name="pro_birth3" title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>이메일</b>
    </label>
    <div class="col-sm-3">
      <input type="text" name="pro_email" class="form-control input-sm">
    </div>
  </div>


  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>휴대폰</b>
    </label>
    <div class="col-sm-3">
      <input type="text" name="pro_hp" value="" class="form-control input-sm">
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>일반전화</b>
    </label>
    <div class="col-sm-3">
      <input type="text" name="pro_tel" value="" class="form-control input-sm">
    </div>
  </div>

  <div class="form-group has-feedback">
    <label class="col-sm-2 control-label">
      <b>주소</b>
    </label>

    <div class="col-sm-8">
      <label for="reg_mb_zip" class="sound_only">우편번호</label>
      <label>
        <input type="text" name="pro_zip" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
      </label>
      <label>
        <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
      </label>

      <div class="addr-line" style="margin-bottom: 5px;">
        <label class="sound_only">기본주소</label>
        <input type="text" name="pro_addr1" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
      </div>

      <div class="addr-line">
        <label class="sound_only">상세주소</label>
        <input type="text" name="pro_addr2" class="form-control input-sm" placeholder="상세주소">
      </div>
    </div>
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
					$("#btn_pen_update").trigger("click");
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					pwd_insert();
				}
			});
	}
</script>
<!-- 인증서 업로드 추가 영역 끝-->
<script type="text/javascript">
let post_data = <?=json_encode($_POST);?>;
if(Object.keys(post_data).length > 0){
  $(".register-form input[name='penNm']").val(post_data['recipient_name']);
  $(".register-form input[name='penLtmNum']").val(post_data['recipient_num']);
  $(".register-form input[name='SbaCd']").val(post_data['type']+' '+post_data['percent']);
  $(".register-form input[name='penRecGraCd']").val(post_data['grade']);
  $(".register-form input[name='penTypeCd']").val(post_data['type']);
  $(".register-form input[name='penPayRate']").val(post_data['percent']);
  $(".register-form input[name='penExpiStDtm']").val(post_data['penExpiDtm'].split(' ~ ')[0]);
  $(".register-form input[name='penExpiEdDtm']").val(post_data['penExpiDtm'].split(' ~ ')[1]);
  $(".register-form input[name='penApplyStDtm']").val(post_data['penApplyDtm'].split(' ~ ')[0]);
  $(".register-form input[name='penApplyEdDtm']").val(post_data['penApplyDtm'].split(' ~ ')[1]);
  $(".register-form input[name='penBirth']").val(post_data['birth'].replaceAll('-', ''));
  $(".register-form input[name='penJumin1']").val(post_data['birth'].replaceAll('-', '').substr(2,6));
  $(".register-form input[name='BDay']").val(post_data['birth'].replaceAll('-', ''));
}

var zipPopupDom = document.getElementById("zipAddrPopupIframe");
var penid="";
      
$(document).ready(function () {
  setDateBox();
  recipientNumCheck($("#penLtmNum").val());

  <?php if(get_text($_GET['penBirth'])) { ?>
  //생년월일 세팅
  var penBirth = "<?=get_text($_GET['penBirth'])?>".split('.');
  var year = penBirth[0];
  var month = penBirth[1];
  var day = penBirth[2];

  $(".register-form select[name='penBirth1']").val(year);
  $(".register-form select[name='penBirth2']").val(month);
  $(".register-form select[name='penBirth3']").val(day);

  <?php } ?>
});


// macro_request에 들어있는 판매/대여 아이템들의 값이 -1이면 판매/대여 불가
let item_arr = <?=json_encode($item_arr)?>;
for(var i = 0; i < Object.keys(item_arr).length; i++){
  if(Object.values(item_arr)[i] > -1){
    $("input[name='"+Object.keys(item_arr)[i]+"']").prop("checked", true);
  }
}


function recipientNumCheck(penLtmNum) {
  var checking = true;
  if (penLtmNum.length == 10) {
    $.post('./ajax.my.recipient.num.check.php', {
      penLtmNum : "L" + penLtmNum
    }, 'json')
    .done(function(result) {
      var ent_pen = result.data.ent_pen;
      if (ent_pen) {
        alert('이미 등록된 수급자 입니다.');
        checking = false;
        return window.location.href = "./my_recipient_view.php?id="+ent_pen['penId'];
      }
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
      checking = false;
      return false;
    });
    return checking;
  }
  return !checking;
}

//생년월일
function setDateBox(parent_element) {
  var dt = new Date();
  var year = "";
  var com_year = dt.getFullYear();

  var parent = $(document);
  if(parent_element) {
    parent = $(parent_element);
  }

  // 발행 뿌려주기
  parent.find(".year").append("<option value=''>년도</option>");

  // 올해 기준으로 -50년부터 +1년을 보여준다.
  for (var y = (com_year - 100); y <= (com_year); y++) {
    parent.find(".year").append("<option value='" + y + "'>" + y + "</option>");
  }

  // 월 뿌려주기(1월부터 12월)
  var month;
  parent.find(".month").append("<option value=''>월</option>");
  for (var i = 1; i <= 12; i++) {
    var first_num="";
    if(i<10){ first_num = 0; }
    parent.find(".month").append("<option value='"+first_num + i + "'>"+first_num + i+"</option>");
  }

  // 일 뿌려주기(1일부터 31일)
  var day;
  parent.find(".day").append("<option value=''>일</option>");
  for (var i = 1; i <= 31; i++) {
    var first_num="";
    if(i<10){ first_num = 0; }
    parent.find(".day").append("<option value='" +first_num+ i + "'>" + first_num+i + "</option>");
  }
}
//주민번호 체크
$('#penJumin1').on('keyup', function() {
  var value = $('#penJumin1').val();
  if(value.length == 6 ) {
      var year= value.substring(0,2);
      var month= value.substring(2,4);
      var day= value.substring(4,6);
      if( year < <?=substr(date("Y"),2,2) ?> ) { 
          year='20'+year; 
      } else {
        year='19'+year; 
      }
      $(".register-form select[name='penBirth1']").val(year);
      $(".register-form select[name='penBirth2']").val(month);
      $(".register-form select[name='penBirth3']").val(day);
      
    }
    // alert(this.value.length);

});
//maxnum 지정
function maxLengthCheck(object){
  if (object.value.length > object.maxLength) {
    object.value = object.value.slice(0, object.maxLength);
  }
}

function zipPopupClose(){
  $("#zipAddrPopupWrap").hide();
}

function zipPopupOpen(target) {
  new daum.Postcode({
    oncomplete: function(data){
      var parent = $(target).closest(".col-sm-8");

      $(parent).find(".penZip").val(data.zonecode);
      $(parent).find(".penAddr").val(data.address);

      zipPopupClose();
    },
    width : "100%",
    height : "100%",
    maxSuggestItems : 5
  }).embed(zipPopupDom);

  $("#zipAddrPopupWrap").show();
}

$(function(){

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
    changeYear: true,
    yearRange : "c-150:c+10"
  });

  $("input:text[dateonly2]").datepicker({
    maxDate : "<?=date("Y-m-d")?>"
  });

  $("input:text[dateonly]").datepicker({});
  $("#zipAddrPopupWrap").css("opacity", 1);
  $("#zipAddrPopupWrap").hide();

  $(document).on('change', 'select.penProRel', function() {
    var $parent = $(this).closest('.panel-body');

    if($(this).val() == '11') {
      $parent.find('.penProRelEtc').prop('readonly', false);
    } else {
      $parent.find('.penProRelEtc').prop('readonly', true);
      $parent.find('.penProRelEtc').val('');
    }
  });

  function onProTypeChange($this) {
    var val = $this.val();

    var $parent = $this.closest('.panel-body');

    if(val == '02') { // 요양보호사
      $parent.find('.pro_rel_title').text('기관');
      $parent.find('.penProRel').hide();
      $parent.find('.penProRelEtc').prop('readonly', false);
    } else {
      $parent.find('.pro_rel_title').text('관계');
      $parent.find('.penProRel').show();
      if($parent.find('.penProRel').val() != '11') {
        $parent.find('.penProRelEtc').prop("readonly", true);
        $parent.find('.penProRelEtc').val('');
      } else {
        $parent.find('.penProRelEtc').prop('readonly', false);
      }
    }
  }
  onProTypeChange($('.register-form input[name="penProTypeCd"]:checked'));
  $('.radio_pro_type').change(function() {
    onProTypeChange($(this));
  });
  $(document).on('change', 'input[name^="pro_type"]', function() {
    onProTypeChange($(this));
  });

  // 예비수급자 체크박스 클릭 여부에 따라 장기요양정보 칸 숨김
  $(document).on('change', '.chk_rep_spare', function() {
    if($('.chk_rep_spare').is(":checked")){
      $('#panel_ltm').hide();
      $('#panel_product').hide();
    } else {
      $('#panel_ltm').show();
      $('#panel_product').show();
    }
  });

  // 등록
  var loading = false;
  $("#btn_submit").click(function() {
    var penLtmNum = $("#penLtmNum").val();

    if(!recipientNumCheck(penLtmNum)){
      return false;
    }
    

    if(loading) {
      alert('등록 중입니다. 잠시만 기다려주세요.');
      return false;
    }

    var importantIcon = $(".register-form .form-control-feedback");
    for(var i = 0; i < importantIcon.length; i++){
      var item = $(importantIcon[i]).prev();
      if(!$(item).val()){
        alert("필수값을 입력해주시길 바랍니다.");
        $(item).focus();
        return false;
      }
    }

    var penJumin =  $(".register-form input[name='BDay']").val().substr(2, 6);
    
    var penBirth = $(".register-form input[name='BDay']").val().substr(0,4)+'-'+$(".register-form input[name='BDay']").val().substr(4,2)+'-'+$(".register-form input[name='BDay']").val().substr(6,2);
    var penLtmNum =  document.getElementById('penLtmNum');

    var pentype = $(".register-form input[name='SbaCd']").val();
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

    var recgrd = $(".register-form input[name='penRecGraCd']").val().replace(/[^0-9]/g, '') == '' ? '0' : $(".register-form input[name='penRecGraCd']").val().replace(/[^0-9]/g, '');
    var penRecGraNm = $(".register-form input[name='penRecGraCd']").val();
    var penRecGraCd = '0'+recgrd;

    var penSpare = $(".register-form input[name='penSpare']:checked").val();
    if(penSpare != '1') {
      if(penLtmNum.value.length !== 10){  alert('장기요양번호는 10자리입니다.');  $(penLtmNum).focus(); return false; }

      if ($('#penLtmNumResultVal').val() == 0) {
        alert('이미 등록된 수급자 입니다.');  
        $(penLtmNum).focus(); 
        return false;
      }
    }

    var penProBirth = $(".register-form select[name='penProBirth1']").val()+'-'
    + $(".register-form select[name='penProBirth2']").val()+'-'
    + $(".register-form select[name='penProBirth3']").val();

    if(penBirth.length !== 10){ penBirth = ''; }
    if(penProBirth.length !== 10){ penProBirth = ''; }

    var pros = [];
    $('.panel_pro_add').each(function() {
      var pro_birth = [$(this).find('select[name^="pro_birth1"]').val(), $(this).find('select[name^="pro_birth2"]').val(), $(this).find('select[name^="pro_birth3"]').val()].join('-');
      if(pro_birth.length != 10) pro_birth = '';

      pros.push({
        pro_type: $(this).find('input[name^="pro_type"]:checked').val(),
        pro_rel_type: $(this).find('select[name^="pro_rel_type"]').val(),
        pro_rel: $(this).find('input[name^="pro_rel"]').val(),
        pro_name: $(this).find('input[name^="pro_name"]').val(),
        pro_birth: pro_birth,
        pro_email: $(this).find('input[name^="pro_email"]').val(),
        pro_hp: $(this).find('input[name^="pro_hp"]').val(),
        pro_tel: $(this).find('input[name^="pro_tel"]').val(),
        pro_zip: $(this).find('input[name^="pro_zip"]').val(),
        pro_addr1: $(this).find('input[name^="pro_addr1"]').val(),
        pro_addr2: $(this).find('input[name^="pro_addr2"]').val()
      });
    });

    loading = true;

    $.post('./ajax.my.recipient.write.php', {
      tutorial : $(".register-form input[name='tutorial']").val(),
      penNm : $(".register-form input[name='penNm']").val(),
      penLtmNum : "L" + $(".register-form input[name='penLtmNum']").val(),
      penRecGraCd: penRecGraCd,
      penRecGraNm: penRecGraNm,
      penGender : $(".register-form input[name='penGender']:checked").val(),
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
      penProTypeCd : $('.register-form input[name="penProTypeCd"]:checked').val(),
      penProNm : $(".register-form input[name='penProNm']").val(),
      penProBirth : penProBirth,
      penProRel : $(".register-form select[name='penProRel']").val(),
      penProConNum : $(".register-form input[name='penProConNum']").val(),
      penProConPnum : $(".register-form input[name='penProConPnum']").val(),
      penProEmail : $(".register-form input[name='penProEmail']").val(),
      penProRelEtc : $(".register-form input[name='penProRelEtc']").val(),
      penProZip : $(".register-form input[name='penProZip']").val(),
      penProAddr : $(".register-form input[name='penProAddr']").val(),
      penProAddrDtl : $(".register-form input[name='penProAddrDtl']").val(),
      penCnmTypeCd : $(".register-form input[name='penCnmTypeCd']:checked").val(),
      penRecTypeCd : $(".register-form select[name='penRecTypeCd']").val(),
      penRecTypeTxt : $(".register-form input[name='penRecTypeTxt']").val(),
      penRemark : $(".register-form input[name='penRemark']").val(),
      entUsrId : $(".register-form input[name='entUsrId']").val(),
      caCenYn : $(".register-form input[name='caCenYn']:checked").val(),
      penSpare: penSpare,
      pros: pros,
      page: "<?=get_text($_GET['page'])?>",
      uuid: "<?=get_text($_GET['uuid'])?>"
    }, 'json')
    .done(function(result) {      
      // macro_request 상태 업데이트
      $.post('./ajax.macro_update.php', {
        mb_id : "<?=$member['mb_id']?>",
        recipient_name : $(".register-form input[name='penNm']").val(),
        recipient_num : $(".register-form input[name='penLtmNum']").val()
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
                  num: $(".register-form input[name='penLtmNum']").val(),
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
            $.post('./ajax.my.recipient.hist.php', {
              data: rep_raw,
              status: true,
              penLtmNum: "<?=$_GET['penLtmNum']?>"
            }, 'json')
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

      if(data.isSpare)
        return window.location.href = "./my_recipient_view.php?id="+data.penId;

      var itemList=[];
      //판매품목 값 넣기
      for(var i=1; i<14; i++) {
        var $sale_product_id = $('#sale_product_id'+i);
        if($sale_product_id.prop('checked')) { itemList.push($sale_product_id.val()); }
      }
      //대여품목 값 넣기
      for(var i=0; i<8; i++) {
        var $rental_product_id = $('#rental_product_id'+i);
        if($rental_product_id.prop('checked')) { itemList.push($rental_product_id.val()); }
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
          window.location.href = "./my_recipient_view.php?id="+data.penId;
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
  });


  let rep_raw;
  let rep_info;
  // 데이터 업데이트(장기요양정보 관련 입력 필드)
  $('#btn_pen_update').click(function() {

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

      var str_rn = $("input[name='penNm']")[0].value;
      var str_id = $("input[name='penLtmNum']")[0].value;
      var btn_update = document.getElementById('btn_pen_update');
      btn_update.disabled = true;

      $.ajax('ajax.recipient.inquiry.php', {
          type: 'POST',  // http method
          data: { id : str_id,rn : str_rn },  // data to submit
          success: function (data, status, xhr) {              
              recipientNumCheck($("#penLtmNum").val());

              alert(data['message']);
              let sale_ll = [];
              let rent_ll = [];
              rep_raw = data['data'];
              let rep_list = data['data']['recipientContractDetail']['Result'];
              
              rep_info = rep_list['ds_welToolTgtList'][0];
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

              let penPayRate = rep_info['REDUCE_NM'] == '일반' ? '15%': rep_info['REDUCE_NM'] == '기초' ? '0%': rep_info['REDUCE_NM'] == '의료급여' ? '6%':
              (rep_info['SBA_CD'].split('(')[1].substr(0, rep_info['SBA_CD'].split('(')[1].length-1));

              $("input[name='penRecGraCd']")[0].value = rep_info['LTC_RCGT_GRADE_CD']+"등급";

              $("input[name='penTypeCd']")[0].value = rep_info['REDUCE_NM'];
              $("input[name='SbaCd']")[0].value = rep_info['SBA_CD'];
              $("input[name='penPayRate']")[0].value = penPayRate;

              $("input[name='penExpiStDtm']")[0].value = rep_info['RCGT_EDA_FR_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_FR_DT'].substr(6,2);
              $("input[name='penExpiEdDtm']")[0].value = rep_info['RCGT_EDA_TO_DT'].substr(0,4)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(4,2)+'-'+rep_info['RCGT_EDA_TO_DT'].substr(6,2);
              $("input[name='penApplyStDtm']")[0].value = applydtm.split(' ~ ')[0];
              $("input[name='penApplyEdDtm']")[0].value = applydtm.split(' ~ ')[1];
              $("input[name='penBirth']")[0].value = rep_info['BDAY'];
              $("input[name='penJumin1']")[0].value = rep_info['BDAY'].substr(2, 6);
              $("input[name='BDay']")[0].value = rep_info['BDAY'];
              
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

              for(var ind = 0; ind < Object.keys(sale_ll).length; ind++){
                  if(Object.keys(sale_ll)[ind] == '미끄럼방지용품'){
                      $("input[name='"+sale_ids['미끄럼방지용품(양말)']+"']")[0].checked = Object.values(sale_ll)[ind];
                      $("input[name='"+sale_ids['미끄럼방지용품(매트)']+"']")[0].checked = Object.values(sale_ll)[ind];
                  } else {
                      $("input[name='"+sale_ids[Object.keys(sale_ll)[ind]]+"']")[0].checked = Object.values(sale_ll)[ind];
                  }
              }

              for(var idx = 0; idx < Object.keys(rent_ll).length; idx++){
                  $("input[name='"+rent_ids[Object.keys(rent_ll)[idx]]+"']")[0].checked = Object.values(rent_ll)[idx];
              }

              /** Insert for saving PEN purchase history record by Jake**/
                    let pen_purchase_hist = rep_list['ds_ctrHistTotalList'];
                    /*
              for ( idx = 0; idx < pen_purchase_hist.length ; idx++)
              {
                console.log( idx, " : ", pen_purchase_hist[idx] );

              }
              */
             
              $.post('./ajax.inquiry_log.php', {
                data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id,pen_nm : str_rn,resultMsg : status,occur_page : "my_recipient_write.php" }
              }, 'json')
              .fail(function($xhr) {
                var data = $xhr.responseJSON;
                alert("로그 저장에 실패했습니다!");
              });

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
				}else if(jqXhr['responseJSON']["data"]['err_code'] == "5"){
					ent_num_insert();
				}
				// 인증서 업로드 추가 영역 끝
              btn_update.disabled = false;
              return false;
          }                    
          
      });

    });

  // 보호자 추가
  var pro_index = 0;
  $('#btn_pro_add').click(function() {
    pro_index++;

    var $panel = $('<div class="panel_pro_add panel-body">');
    $panel.append($('#panel_pro_template').html());
    $panel.find('input,select').each(function() {
      var name = $(this).attr('name');

      $(this).attr('name', name + '[' + pro_index + ']');
    });

    $('#panel_pro').closest('.panel').append($panel);
  });

  $(document).on('click', '.btn_pro_del', function() {
    $(this).closest('.panel-body').remove();
  });

  <?php if ($tutorial === 'true') { ?>
    show_eroumcare_popup({
			title: '수급자 신규등록',
			content: '체험용 수급자 정보로<br/>바로 등록하시겠습니까?',
			activeBtn: {
				text: '홍길동 수급자 자동입력',
        callback: function(e) {
          $(".register-form input[name='penNm']").val('홍길동');
          $(".register-form input[name='penLtmNum']").val('1234567891');
          $(".register-form select[name='penRecGraCd']").val('00');

          $(".register-form input[name='penJumin1']").val('581111');

          var year=$(".register-form input[name='penJumin1']").val().substring(0,2);
          var month=$(".register-form input[name='penJumin1']").val().substring(2,4);
          var day=$(".register-form input[name='penJumin1']").val().substring(4,6);
          if( year < 21 ) { 
            year='20'+year; 
          } else {
            year='19'+year; 
          }
          $(".register-form select[name='penBirth1']").val(year);
          $(".register-form select[name='penBirth2']").val(month);
          $(".register-form select[name='penBirth3']").val(day);

          $(".register-form input[name='penConNum']").val('01012345678');
          $(".register-form input[name='penConPnum']").val('01012345678');

          $(".register-form input[name='penZip']").val(12345);
          $(".register-form input[name='penAddr']").val('튜토리얼 수급자 주소');
          $(".register-form input[name='penAddrDtl']").val('홍길동 상세 주소');

          $(".register-form input[name='penExpiStDtm']").val('2021-01-01');
          $(".register-form input[name='penExpiEdDtm']").val('2029-12-31');

          e.preventDefault();
          e.stopPropagation();

          hide_eroumcare_popup();
        }
			},
			hideBtn: {
				text: '직접등록',
			}
		});
  <?php } ?>

});
</script>

<?php include_once("./_tail.php"); ?>
