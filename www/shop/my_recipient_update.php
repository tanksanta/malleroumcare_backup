<?php

include_once("./_common.php");
include_once("./_head.php");

# 회원검사
if(!$member["mb_id"])
  alert("접근 권한이 없습니다.");

if(!$_GET["id"])
  alert("정상적이지 않은 접근입니다.");

# 예비수급자 여부
$is_spare = $_GET['penSpare'] == '1';

# 수급자정보
if($is_spare) {
  $res = api_post_call(EROUMCARE_API_SPARE_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_GET['id']
  ));
} else {
  $res = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_GET['id']
  ));
}

if(!$res || $res['errorYN'] != 'N')
  alert('서버 오류로 수급자 정보를 불러올 수 없습니다.');

$data = $res["data"][0];
if(!$data)
  alert('수급자 정보가 존재하지 않습니다.');

$data["penExpiDtm"] = explode(" ~ ", $data["penExpiDtm"]);

# 수급자 취급가능 제품
$data2 = [];
if(!$is_spare) {
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST, array(
  'penId' => $data['penId']
));

if($res['data'])
  $data2 = $res["data"];
}

# 수급자 연결아이디 (이로움 계정 연결 정보)
$pen_ent = get_pen_ent_by_pen_id($data['penId']);

# 보호자
$pros = get_pros_by_recipient($data['penId']);


$sql_recent = "SELECT ent_id, pen_nm, PEN_LTM_NUM, count(*) as cnt from pen_purchase_hist where PEN_LTM_NUM = '{$data["penLtmNum"]}' and ent_id = '{$member['mb_entId']}' group by ENT_ID, PEN_LTM_NUM;";
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

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
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
<p class = "head_title"> 수급자 정보 수정</p>
<form class="form-horizontal register-form">
  <input type="hidden" value="<?=substr($data['penProBirth'],2,2) ?><?=substr($data['penProBirth'],5,2) ?><?=substr($data['penProBirth'],8,2) ?>" id="penProBirth" >
  <div class="panel panel-default">
    <div class="panel-heading"><strong>기본정보</strong></div>
    <div class="panel-body">
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>수급자명</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penNm" value="<?=$data["penNm"]?>" class="form-control input-sm">
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
              <b>장기요양인정번호</b>
            </label>
            <div class="col-sm-4"  style="display: flex">
              <span style="float: left; width: 10px; height: 30px; line-height: 30px; margin-right: 5px;">L</span>

              <input type="number" maxlength="10" oninput="maxLengthCheck(this)" id="penLtmNum" name="penLtmNum" class="form-control input-sm" style="width: calc(100% - 15px);" value="<?=str_replace("L", "", $data["penLtmNum"])?>">

              <button type="button" id="btn_pen_update" class="btn btn-color btn-sm" style="margin-left: 15px;">요양정보 업데이트</button>
            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>인정등급</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="text" name="penRecGraCd" value="<?=$data["penRecGraNm"]?>" class="form-control input-sm">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>대상자구분</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="text" name="penTypeCd" value="<?=substr($data["penTypeNm"], 0, 6); //php라서 한글하나가 1로 차지 ?>" class="form-control input-sm">
              <input type="hidden" id="SbaCd" name="SbaCd" value="<?=$data["penTypeNm"]?>">
            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>본인부담률</b>
            </label>
            <div class="col-sm-3">
              <input readonly type="text" name="penPayRate" value="<?=substr($data["penTypeNm"], 7);?>" class="form-control input-sm">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>유효기간</b>
            </label>
            <div class="col-sm-4">

              <input readonly type="text" name="penExpiStDtm" class="form-control input-sm" dateonly2 style="display: inline-block;width:47%;" value="<?=$data["penExpiDtm"][0]?>"> ~
              <input readonly type="text" name="penExpiEdDtm" class="form-control input-sm" dateonly style="display: inline-block;width:48%;" value="<?=$data["penExpiDtm"][1]?>">

            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>적용기간</b>
            </label>
            <div class="col-sm-4">

              <input readonly type="text" name="penApplyStDtm" class="form-control input-sm" dateonly2 style="display: inline-block;width:47%;" value="<?php $apped = substr($data["penAppEdDtm"],0,4)."-".substr($data["penAppEdDtm"],4,2)."-".substr($data["penAppEdDtm"],6,2); $timestamp = strtotime($apped." -1 years +1 days"); echo date("Y-m-d", $timestamp);?>"> ~
              <input readonly type="text" name="penApplyEdDtm" class="form-control input-sm" dateonly style="display: inline-block;width:48%;" value="<?=substr($data["penAppEdDtm"],0,4)."-".substr($data["penAppEdDtm"],4,2)."-".substr($data["penAppEdDtm"],6,2)?>">
            </div>
          </div>

          <div class="form-group has-feedback">
            <label class="col-sm-2 control-label">
              <b>생년월일</b>
            </label>
            <div class="col-sm-3">

              <input readonly type="number" maxlength="8" oninput="maxLengthCheck(this)" id="penBirth" name="penBirth" min="0"  class="form-control input-sm" value="<?=get_text(str_replace('.', '', $data['penBirth']))?>">
              <input type="hidden" maxlength="6" oninput="maxLengthCheck(this)" id="penJumin1" name="penJumin1" min="0"  class="form-control input-sm" value="<?=get_text(substr(str_replace('.', '', $data['penBirth']),2))?>">
              <input type="hidden" id="BDay" name="BDay" value="<?=get_text($data['penBirth']) ?: ''?>">

            </div>
          </div>
      </div>
      <!-- ==================================================================== -->

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>성별</b>
        </label>
        <div class="col-sm-3">
          <label class="checkbox-inline">
            <input type="radio" name="penGender" value="남" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penGender"] == "남") ? "checked" : ""?>>남
          </label>

          <label class="checkbox-inline">
            <input type="radio" name="penGender" value="여" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penGender"] == "여") ? "checked" : ""?>>여
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>휴대폰</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penConNum" value="<?=$data["penConNum"]?>" class="form-control input-sm" oninput="onlyNumber()">
        </div>
      </div>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>일반전화</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penConPnum" value="<?=$data["penConPnum"]?>" class="form-control input-sm" oninput="onlyNumber()">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>주소</b>
        </label>

        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호</label>
          <label>
            <input type="text" name="penZip" value="<?=$data["penZip"]?>" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
          </label>

          <div class="addr-line" style="margin-bottom: 5px;">
            <label class="sound_only">기본주소</label>
            <input type="text" name="penAddr" value="<?=$data["penAddr"]?>" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
          </div>

          <div class="addr-line">
            <label class="sound_only">상세주소</label>
            <input type="text" name="penAddrDtl" value="<?=$data["penAddrDtl"]?>" class="form-control input-sm" placeholder="상세주소">
          </div>
        </div>
      </div>
      
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>담당직원정보</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="entUsrId" class="form-control input-sm"  value="<?=$member['mb_giup_boss_name']?>" placeholder="담당직원정보">
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
            <input type="radio" class="radio_pro_type" name="penProTypeCd" value="01" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penProTypeCd"] == "01") ? "checked" : ""?>>일반보호자
          </label>
          <label class="checkbox-inline">
            <input type="radio" class="radio_pro_type" name="penProTypeCd" value="02" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penProTypeCd"] == "02") ? "checked" : ""?>>요양보호사
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b class="pro_rel_title">관계</b>
        </label>
        <div class="col-sm-3">
          <select class="form-control input-sm penProRel" name="penProRel" style="margin-bottom: 5px;">
            <option value="00" <?=($data["penProRel"] == "00") ? "selected" : ""?>>처</option>
            <option value="01" <?=($data["penProRel"] == "01") ? "selected" : ""?>>남편</option>
            <option value="02" <?=($data["penProRel"] == "02") ? "selected" : ""?>>자</option>
            <option value="03" <?=($data["penProRel"] == "03") ? "selected" : ""?>>자부</option>
            <option value="04" <?=($data["penProRel"] == "04") ? "selected" : ""?>>사위</option>
            <option value="05" <?=($data["penProRel"] == "05") ? "selected" : ""?>>형제</option>
            <option value="06" <?=($data["penProRel"] == "06") ? "selected" : ""?>>자매</option>
            <option value="07" <?=($data["penProRel"] == "07") ? "selected" : ""?>>손</option>
            <option value="08" <?=($data["penProRel"] == "08") ? "selected" : ""?>>배우자 형제자매</option>
            <option value="09" <?=($data["penProRel"] == "09") ? "selected" : ""?>>외손</option>
            <option value="10" <?=($data["penProRel"] == "10") ? "selected" : ""?>>부모</option>
            <option value="11" <?=($data["penProRel"] == "11") ? "selected" : ""?>>직접입력</option>
          </select>
          <input type="text" name="penProRelEtc" value="<?=$data["penProRelEtc"]?>" class="penProRelEtc form-control input-sm" <?=($data["penProRel"] == "11") ? "" : "readonly"?>>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>보호자명</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProNm" value="<?=$data["penProNm"]?>" class="form-control input-sm">
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
          <input type="text" name="penProEmail" value="<?=$data["penProEmail"]?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>휴대폰</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProConNum" value="<?=$data["penProConNum"]?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>일반전화</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penProConPnum" value="<?=$data["penProConPnum"]?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>주소</b>
        </label>

        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호</label>
          <label>
            <input type="text" name="penProZip" value="<?=$data["penProZip"]?>" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
          </label>

          <div class="addr-line" style="margin-bottom: 5px;">
            <label class="sound_only">기본주소</label>
            <input type="text" name="penProAddr" value="<?=$data["penProAddr"]?>" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
          </div>

          <div class="addr-line">
            <label class="sound_only">상세주소</label>
            <input type="text" name="penProAddrDtl" value="<?=$data["penProAddrDtl"]?>" class="form-control input-sm" placeholder="상세주소">
          </div>
        </div>
      </div>
    </div>

    <?php foreach($pros as $idx => $pro) { ?>
    <div class="panel_pro_add panel-body">
      <input type="hidden" name="pro_id<?="[$idx]"?>" value="<?=$pro['pro_id']?>">
      <input type="hidden" name="deleted<?="[$idx]"?>" value="0">
      <button type="button" class="btn_pro_del">삭제</button>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>분류</b>
        </label>
        <div class="col-sm-3">
          <label class="checkbox-inline">
            <input type="radio" name="pro_type<?="[$idx]"?>" value="01" style="vertical-align: middle; margin: 0 5px 0 0;" <?=get_checked($pro['pro_type'], '01')?>>일반보호자
          </label>

          <label class="checkbox-inline">
            <input type="radio" name="pro_type<?="[$idx]"?>" value="02" style="vertical-align: middle; margin: 0 5px 0 0;" <?=get_checked($pro['pro_type'], '02')?>>요양보호사
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b class="pro_rel_title">관계</b>
        </label>
        <div class="col-sm-3">
          <select class="form-control input-sm penProRel" name="pro_rel_type<?="[$idx]"?>" style="margin-bottom: 5px;">
            <option value="00" <?=get_selected($pro['pro_rel_type'], '00')?>>처</option>
            <option value="01" <?=get_selected($pro['pro_rel_type'], '01')?>>남편</option>
            <option value="02" <?=get_selected($pro['pro_rel_type'], '02')?>>자</option>
            <option value="03" <?=get_selected($pro['pro_rel_type'], '03')?>>자부</option>
            <option value="04" <?=get_selected($pro['pro_rel_type'], '04')?>>사위</option>
            <option value="05" <?=get_selected($pro['pro_rel_type'], '05')?>>형제</option>
            <option value="06" <?=get_selected($pro['pro_rel_type'], '06')?>>자매</option>
            <option value="07" <?=get_selected($pro['pro_rel_type'], '07')?>>손</option>
            <option value="08" <?=get_selected($pro['pro_rel_type'], '08')?>>배우자 형제자매</option>
            <option value="09" <?=get_selected($pro['pro_rel_type'], '09')?>>외손</option>
            <option value="10" <?=get_selected($pro['pro_rel_type'], '10')?>>부모</option>
            <option value="11" <?=get_selected($pro['pro_rel_type'], '11')?>>직접입력</option>
          </select>
          <input type="text" name="pro_rel<?="[$idx]"?>" class="penProRelEtc form-control input-sm" value="<?=$pro['pro_rel']?>" readonly>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>보호자명</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="pro_name<?="[$idx]"?>" value="<?=$pro['pro_name']?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>생년월일</b>
        </label>
        <div class="col-sm-3">
          <input type="hidden" name="pro_birth<?="[$idx]"?>" value="<?=$pro['pro_birth']?>">
          <select name="pro_birth1<?="[$idx]"?>" title="년도" class="form-control input-sm year" style="display:inline-block;width:32%;"></select>
          <select name="pro_birth2<?="[$idx]"?>" title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
          <select name="pro_birth3<?="[$idx]"?>" title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>이메일</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="pro_email<?="[$idx]"?>" value="<?=$pro['pro_email']?>" class="form-control input-sm">
        </div>
      </div>


      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>휴대폰</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="pro_hp<?="[$idx]"?>" value="<?=$pro['pro_hp']?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>일반전화</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="pro_tel<?="[$idx]"?>" value="<?=$pro['pro_tel']?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>주소</b>
        </label>

        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호</label>
          <label>
            <input type="text" name="pro_zip<?="[$idx]"?>" value="<?=$pro['pro_zip']?>" class="penZip form-control input-sm" size="6" maxlength="6" readonly>
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
          </label>

          <div class="addr-line" style="margin-bottom: 5px;">
            <label class="sound_only">기본주소</label>
            <input type="text" name="pro_addr1<?="[$idx]"?>" value="<?=$pro['pro_addr1']?>" class="penAddr form-control input-sm" placeholder="기본주소" readonly>
          </div>

          <div class="addr-line">
            <label class="sound_only">상세주소</label>
            <input type="text" name="pro_addr2<?="[$idx]"?>" value="<?=$pro['pro_addr2']?>" class="form-control input-sm" placeholder="상세주소">
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
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
            <input type="radio" name="penCnmTypeCd" value="00" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penCnmTypeCd"] == "00") ? "checked" : ""?>>수급자
          </label>

          <label class="checkbox-inline">
            <input type="radio" name="penCnmTypeCd" value="01" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["penCnmTypeCd"] == "01") ? "checked" : ""?>>보호자
          </label>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>수령방법</b>
        </label>
        <div class="col-sm-3">
          <select class="form-control input-sm" style="margin-bottom: 5px;" name="penRecTypeCd">
            <option value="00" <?=($data["penRecTypeCd"] == "00") ? "selected" : ""?>>방문</option>
            <option value="01" <?=($data["penRecTypeCd"] == "01") ? "selected" : ""?>>유선</option>
          </select>
          <input type="text" name="penRecTypeTxt" value="<?=$data["penRecTypeTxt"]?>" class="form-control input-sm">
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label">
          <b>특이사항</b>
        </label>
        <div class="col-sm-3">
          <input type="text" name="penRemark" value="<?=$data["penRemark"]?>" class="form-control input-sm">
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

  <div id="panel_product" class="panel panel-default">
    <div class="panel-heading"><strong>취급가능 품목</strong></div>
    <div class="panel-body">
      <div class="form-group has-feedback sale-product-form">
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
          <label class="checkbox-inline dealing" style="margin-left: 0px; width:146px;">
            <input disabled type="checkbox" class="chk_sale_product chk_sale_product_child" name="<?=${'sale_product_id'.$i}; ?>" id="<?="sale_product_id".$i; ?>" value="<?=${'sale_product_id'.$i}; ?>" style="" ><?=${'sale_product_name'. $i}; ?>
          </label>
          <?php } ?>
        </div>
      </div>


      <div class="form-group has-feedback rental-product-form">
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
          <label class="checkbox-inline dealing" style="margin-left: 0px; width:146px;">
            <input disabled type="checkbox" class="chk_sale_product chk_sale_product_child" name="<?=${'rental_product_id'. $i}; ?>" id="<?='rental_product_id'.$i; ?>" value="<?=${'rental_product_id'. $i}; ?>" style="" ><?=${'rental_product_name'. $i}; ?>
          </label>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <script>
  <?php
  for($i=0;$i<count($data2);$i++) {
    echo 'document.getElementsByName("'.$data2[$i]['itemId'].'")[0].checked = true;';
  }
  ?>
  </script>

  <!-- 20210307 성훈작업 -->
  <div class="text-center" style="margin-top: 30px;">
    <button type="button" id="btn_submit" class="btn btn-color">수정</button>
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
var zipPopupDom = document.getElementById("zipAddrPopupIframe");

$(document).ready(function() {
  setDateBox();
  //생년월일 세팅
  var penBirth = "<?=get_text($data['penBirth'])?>".split('.');
  var penProBirth = document.getElementById('penProBirth');
  var year = penBirth[0];
  var month = penBirth[1];
  var day = penBirth[2];

  var year2 = penProBirth.value.substring(0,2);
  var month2 = penProBirth.value.substring(2,4);
  var day2 = penProBirth.value.substring(4,6);

  if( year2 < <?=substr(date("Y"),2,2) ?> ) { 
    year2='20'+year2; 
  } else {
    year2='19'+year2; 
  }
  $(".register-form select[name='penBirth1']").val(year);
  $(".register-form select[name='penBirth2']").val(month);
  $(".register-form select[name='penBirth3']").val(day);

  
  $(".register-form select[name='penProBirth1']").val(year2);
  $(".register-form select[name='penProBirth2']").val(month2);
  $(".register-form select[name='penProBirth3']").val(day2);

  $('.panel_pro_add').each(function() {
    var pro_birth = $(this).find('input[name^="pro_birth"]').val().split('-');
    $(this).find('select[name^="pro_birth1"]').val(pro_birth[0]);
    $(this).find('select[name^="pro_birth2"]').val(pro_birth[1]);
    $(this).find('select[name^="pro_birth3"]').val(pro_birth[2]);
  });
});

//생년월일
function setDateBox() {
  var dt = new Date();
  var year = "";
  var com_year = dt.getFullYear();

  // 발행 뿌려주기
  $(".year").append("<option value=''>년도</option>");

  // 올해 기준으로 -50년부터 +1년을 보여준다.
  for (var y = (com_year - 100); y <= (com_year); y++) {
    $(".year").append("<option value='" + y + "'>" + y + "</option>");
  }

  // 월 뿌려주기(1월부터 12월)
  var month;
  $(".month").append("<option value=''>월</option>");
  for (var i = 1; i <= 12; i++) {
    var first_num="";
    if(i<10){ first_num = 0; }
    $(".month").append("<option value='"+first_num + i + "'>"+first_num + i+"</option>");
  }

  // 일 뿌려주기(1일부터 31일)
  var day;
  $(".day").append("<option value=''>일</option>");
  for (var i = 1; i <= 31; i++) {
    var first_num = "";
    if(i<10){ first_num = 0; }
    $(".day").append("<option value='" + first_num+i + "'>" +first_num+ i + "</option>");
  }
}

//주민번호 체크
$('#penJumin1').on('keyup', function() {
  if(this.value.length == 6 ) {
    var year=this.value.substring(0,2);
    var month=this.value.substring(2,4);
    var day=this.value.substring(4,6);
    if( year < <?=substr(date("Y"),2,2) ?> ) { 
      year='20'+year; 
    } else {
      year='19'+year; 
    }
    $(".register-form select[name='penBirth1']").val(year);
    $(".register-form select[name='penBirth2']").val(month);
    $(".register-form select[name='penBirth3']").val(day);
  }
});

//maxnum 지정
function maxLengthCheck(object) {
  if (object.value.length > object.maxLength){
    object.value = object.value.slice(0, object.maxLength);
  }
}

function zipPopupClose() {
  $("#zipAddrPopupWrap").hide();
}

function zipPopupOpen(target) {
  new daum.Postcode({
    oncomplete: function(data) {
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

function onlyNumber(){
	const reg = /\D/g;
  event.target.value = event.target.value.replace(reg, "");
}

$(function() {
  let ct_history_list = [];
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

  $("input:text[dateonly]").datepicker({});

  $("input:text[dateonly2]").datepicker({
    maxDate : "<?=date("Y-m-d")?>"
  });
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
  $('.panel_pro_add').each(function() {
    onProTypeChange($(this).find('input[name^="pro_type"]:checked'));
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
  $("#btn_submit").click(function() {
    var importantIcon = $(".register-form .form-control-feedback");
    for(var i = 0; i < importantIcon.length; i++) {
      var item = $(importantIcon[i]).prev();
      if(!$(item).val()) {
        alert("필수값을 입력해주시길 바랍니다.");
        $(item).focus();
        return false;
      }
    }
    
    if(ct_history_list.length != 0){
      let penPurchaseHist = <?=json_encode($recent_result)?>;

      //if(penPurchaseHist == null){
        $.post('./ajax.my.recipient.hist.php', {
          data: ct_history_list,
          status: true
        }, 'json')
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert("계약정보 업데이트에 실패했습니다!");
        })

      //} else if(ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].length > penPurchaseHist['cnt']){
      //  ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'] = ct_history_list['recipientContractDetail']['Result']['ds_ctrHistTotalList'].slice(penPurchaseHist['cnt'], ct_history_list.length);

        // TODO : pen_purchase_hist update 만들기
        // 이로움 DB에 계약정보 insert
      //  $.post('./ajax.my.recipient.hist.php', {
      //    data: ct_history_list,
      //   status: true
      //  }, 'json')
      //  .fail(function($xhr) {
      //    var data = $xhr.responseJSON;
      //    alert("계약정보 업데이트에 실패했습니다!");
      //  })
     // }
    }

    var penJumin =  document.getElementById('penJumin1').value;
    var penLtmNum =  document.getElementById('penLtmNum');
    var penSpare = $(".register-form input[name='penSpare']:checked").val();

    if(penSpare != '1') {
      if(penLtmNum.value.length !== 10){  alert('장기요양번호는 10자리입니다.');  $(penLtmNum).focus(); return false;}
    }
    var penBirth = $(".register-form input[name='penBirth']").val().substr(0,4)+'-'+$(".register-form input[name='penBirth']").val().substr(4,2)+'-'+$(".register-form input[name='penBirth']").val().substr(6,2);

    var penProBirth = $(".register-form select[name='penProBirth1']").val()+'-'
    + $(".register-form select[name='penProBirth2']").val()+'-'
    + $(".register-form select[name='penProBirth3']").val();

    if(penBirth.length !== 10) { penBirth = ''; }
    if(penProBirth.length !== 10) { penProBirth = ''; }

    var pros = [];
    $('.panel_pro_add').each(function() {
      var pro_birth = [$(this).find('select[name^="pro_birth1"]').val(), $(this).find('select[name^="pro_birth2"]').val(), $(this).find('select[name^="pro_birth3"]').val()].join('-');
      if(pro_birth.length != 10) pro_birth = '';

      var pro_data = {
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
      };

      if($(this).find('input[name^="pro_id"]').length > 0) {
        pro_data['pro_id'] = $(this).find('input[name^="pro_id"]').val();
        pro_data['deleted'] = $(this).find('input[name^="deleted"]').val();
      }

      pros.push(pro_data);
    });
	var pentype = $(".register-form input[name='SbaCd']").val();
    var penTypeCd = ''; //코드 일반15:00/감경9:01/감경6:02/의료6:03/기초0:04;
    if(pentype.substr(0, 2) == '일반' || pentype.substr(0, 2) == '의료' || pentype.substr(0, 2) == '기초'){ //일반의료기초
      var percnt = pentype.substr(0, 2) == '일반'? ' 15%' : pentype.substr(0, 2) == '의료'? ' 6%' : ' 0%';
      penTypeCd = pentype.substr(0, 2) == '일반'? '00' : pentype.substr(0, 2) == '의료'? '03' : '04';
    } else { //감경
      penTypeCd = pentype.substr(3, 1) == '6'? '02' : '01';
    }
	var recgrd = $(".register-form input[name='penRecGraCd']").val().replace(/[^0-9]/g, '') == '' ? '0' : $(".register-form input[name='penRecGraCd']").val().replace(/[^0-9]/g, '');
    var penRecGraCd = '0'+recgrd;
    var sendData = {
      penId : "<?=$data["penId"]?>",
      penNm : $(".register-form input[name='penNm']").val(),
      penLtmNum : "L" + $(".register-form input[name='penLtmNum']").val(),
      penRecGraCd : penRecGraCd,
      penGender : $(".register-form input[name='penGender']:checked").val(),
      penBirth : penBirth,
      penJumin : penJumin,
      penTypeCd : penTypeCd,
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
      appCd : "<?=$data["appCd"]?>",
      caCenYn : $(".register-form input[name='caCenYn']:checked").val(),
      delYn : "N",
      isSpare: "<?=get_text($_GET['penSpare'])?>",
      penSpare: penSpare,
      pros: pros
    }

    $.post('./ajax.my.recipient.update.php', sendData, 'json')
    .done(function(result) {
      var data = result.data;

      if(data.isSpare)
        return window.location.href = "./my_recipient_view.php?id"+data.penId;

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
      });
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $("#grade_edit_submit_btn").click(function () {
    var item = $('input[name="penGraEditDtm"]');

    if (!$(item).val()) {
      alert("필수값을 입력해주시길 바랍니다.");
      $(item).focus();
      return false;
    }

    var penSpare = $(".register-form input[name='penSpare']:checked").val();

    var sendData = {
      update_type: "grade_edit",
      act: "log_insert",
      penId: "<?=$data["penId"]?>",
      penRecGraCd: $(".register-form select[name='penRecGraCd']").val(),
      penRecGraNm: $(".register-form select[name='penRecGraCd'] option:selected").text(),
      penTypeCd: $(".register-form select[name='penTypeCd']").val(),
      penTypeNm: $(".register-form select[name='penTypeCd'] option:selected").text(),
      penProTypeCd: $('.register-form input[name="penProTypeCd"]:checked').val(),
      penGraEditDtm: $(".register-form input[name='penGraEditDtm']").val(),
      penGraApplyMonth: $(".register-form select[name='penGraApplyMonth']").val(),
      penGraApplyDay: $(".register-form select[name='penGraApplyDay']").val(),
      delYn: "N",
      isSpare: "<?=get_text($_GET['penSpare'])?>",
      penSpare: penSpare
    };

    $.post('./ajax.my.recipient.update.php', sendData, 'json')
      .done(function (result) {
        var data = result.data;

        $.post('./ajax.my.recipient.grade.log.update.php', sendData, 'json')
          .done(function (result) {
            alert("적용 되었습니다.");
            if (data.isSpare)
              window.location.href = "./my_recipient_view.php?id="+data.penId;
            else
              location.reload();
          })
          .fail(function ($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
          });
        
      })
      .fail(function ($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
  });
  
  $('.grade_edit_del_btn').click(function () {
    if (!confirm("기록을 삭제하시겠습니까?")) {
      return false;
    }
    
    var sendData = {
      act: "log_del",
      penId: "<?=$data["penId"]?>",
      seq: $(this).data('seq'),
    };

    $.post('./ajax.my.recipient.grade.log.update.php', sendData, 'json')
      .done(function (result) {
        alert("기록이 삭제 되었습니다.");
        location.reload();
      })
      .fail(function ($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
  });

  $('#btn_pen_ent_link').click(function() {
    $.post('./ajax.my.recipient.pen.ent.php', {
      pen_mb_id: $('#pen_mb_id').val(),
      penId: "<?=$data["penId"]?>"
    }, 'json')
    .done(function() {
      alert('등록이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $('#btn_pen_ent_del').click(function() {
    if(!confirm('연결을 해지하시겠습니까?'))
      return false;
    
    $.post('./ajax.my.recipient.pen.ent.php', {
      w: 'd',
      pen_mb_id: $('#pen_mb_id').val(),
      penId: "<?=$data["penId"]?>"
    }, 'json')
    .done(function() {
      alert('수급자 아이디 연결이 해지되었습니다.');
      window.location.reload();
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

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
			  alert(data['message']);

              let sale_ll = [];
              let rent_ll = [];
              let rep_list = data['data']['recipientContractDetail']['Result'];
              ct_history_list = data['data'];
              
              let rep_info = rep_list['ds_welToolTgtList'][0];
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

              let penPayRate = rep_info['REDUCE_NM'] == '일반' ? '15%': rep_info['REDUCE_NM'] == '기초' ? '0%': rep_info['REDUCE_NM'] == '의료급여' ? '6%':
			  rep_info['SBA_CD'] == '일반' ? '15%': rep_info['SBA_CD'] == '기초' ? '0%': rep_info['SBA_CD'] == '의료급여' ? '6%':
			  (rep_info['SBA_CD'].split('(')[1].substr(0, rep_info['SBA_CD'].split('(')[1].length-1));
			  rep_info['REDUCE_NM'] = (rep_info['REDUCE_NM'] == null)?rep_info['SBA_CD']:rep_info['REDUCE_NM'];
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
                    
              $.post('./ajax.inquiry_log.php', {
                  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id,pen_nm : str_rn,resultMsg : status,occur_page : "my_recipient_update.php" }
              }, 'json')
              .fail(function($xhr) {
                  var data = $xhr.responseJSON;
                  alert("로그 저장에 실패했습니다!");
              });
			  $.post('./ajax.my.recipient.hist.php', {
					  data: data['data'],
					  status: false
					}, 'json')
					.fail(function($xhr) {
					  var data = $xhr.responseJSON;
					  alert("계약정보 업데이트에 실패했습니다!");
					});
      
              btn_update.disabled = false;
          },
          error: function (jqXhr, textStatus, errorMessage) {
              var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
              //alert(errMSG);
              //인증서 업로드 추가 영역 
				if(errMSG == "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다." ){
					alert(errMSG);
					$.post('./ajax.inquiry_log.php', {
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id,pen_nm : str_rn,resultMsg : "fail",occur_page : "my_recipient_update.php",err_msg:errMSG }
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
					  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : str_id,pen_nm : str_rn,resultMsg : "fail",occur_page : "my_recipient_update.php",err_msg:errMSG }
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

  // 보호자 추가
  var pro_index = $('.panel_pro_add').length;
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
    var $panel = $(this).closest('.panel-body');

    if($panel.find('input[name^="pro_id"]').length > 0) {
      // 기존 보호자
      $panel.find('input[name^="deleted"]').val(1);
      $panel.hide();
    } else {
      $panel.remove();
    }

  });


});
</script>

<?php include_once("./_tail.php"); ?>
