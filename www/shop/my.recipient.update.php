<?php

	include_once("./_common.php");
	include_once("./_head.php");

	# 회원검사
	if(!$member["mb_id"]){
		alert("접근 권한이 없습니다.");
		return false;
	}

	# 수급자정보
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = 1;
	$sendData["penId"] = $_GET["id"];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$data = $res["data"][0];
	if(!$data){
		alert("존재하지 않는 데이터입니다.");
		return false;
	}

	$data["penExpiDtm"] = explode(" ~ ", $data["penExpiDtm"]);


    # 구급자별 품목 조회
    $sendData2 = [];
    $sendData2["penId"] = $data['penId'];
    // $sendData2["gubun"] ;
	$oCurl2 = curl_init();
	curl_setopt($oCurl2, CURLOPT_PORT, 9901);
	curl_setopt($oCurl2, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectItemList");
	curl_setopt($oCurl2, CURLOPT_POST, 1);
	curl_setopt($oCurl2, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl2, CURLOPT_POSTFIELDS, json_encode($sendData2, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl2, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl2, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res2 = curl_exec($oCurl2);
	$res2 = json_decode($res2, true);
	curl_close($oCurl2);

	$data2 = $res2["data"];

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

		@media (max-width : 750px){
			#zipAddrPopupWrap > div > div { width: 100%; height: 100%; left: 0; margin-left: 0; }
		}
        .has-feedback .form-control {
            padding-right: 0px;
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
				</div>


                <div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>주민등록번호</b>
					</label>
					<div class="col-sm-3">
						<input type="number" maxlength="6" oninput="maxLengthCheck(this)" id="penJumin1" name="penJumin1" min="0"  class="form-control input-sm" style="display: inline-block;width:47%;" value="<?=substr($data["penJumin"], 0, 6) ?>" > - 
						<input type="password" maxlength="7" oninput="maxLengthCheck(this)"id="penJumin2" name="penJumin2" min="0" class="form-control input-sm" style="display:inline-block;;width:48%;" value="<?=substr($data["penJumin"], 6, 7) ?>">
						<qi class="fa fa-check form-control-feedback"></qi>
					</div>
				</div>

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>생년월일</b>
					</label>
					<div class="col-sm-3">
                        <select name="penBirth1" id="year" title="년도" class="form-control input-sm year " style="display:inline-block;width:32%;"></select>
                        <select name="penBirth2" id="month" title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
                        <select name="penBirth3" id="day" title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
					</div>
				</div>
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>장기요양인정번호</b>
					</label>
					<div class="col-sm-3">
						<span style="float: left; width: 10px; height: 30px; line-height: 30px; margin-right: 5px;">L</span>
						<input type="number" maxlength="10" oninput="maxLengthCheck(this)"  id="penLtmNum" name="penLtmNum" class="form-control input-sm" style="width: calc(100% - 15px);" value="<?=str_replace("L", "", $data["penLtmNum"])?>" >
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>인정등급</b>
					</label>
					<div class="col-sm-3">
						<select class="form-control input-sm" name="penRecGraCd">
							<option value="00" <?=($data["penRecGraCd"] == "00") ? "selected" : ""?>>등급외</option>
							<option value="01" <?=($data["penRecGraCd"] == "01") ? "selected" : ""?>>1등급</option>
							<option value="02" <?=($data["penRecGraCd"] == "02") ? "selected" : ""?>>2등급</option>
							<option value="03" <?=($data["penRecGraCd"] == "03") ? "selected" : ""?>>3등급</option>
							<option value="04" <?=($data["penRecGraCd"] == "04") ? "selected" : ""?>>4등급</option>
							<option value="05" <?=($data["penRecGraCd"] == "05") ? "selected" : ""?>>5등급</option>
						</select>
					</div>
				</div>

<!-- 
                <div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>휴대폰</b>
					</label>
					<div class="col-sm-3">
                        <select class="form-control input-sm" name="penConNum1" id="penConNum1" style="display:inline-block; width:30%;">
							<option value="010" <?=(substr($data["penConNum"], 0, 3) == "010") ? "selected" : ""?> >010</option>
							<option value="011" <?=(substr($data["penConNum"], 0, 3) == "011") ? "selected" : ""?> >011</option>
							<option value="016" <?=(substr($data["penConNum"], 0, 3) == "016") ? "selected" : ""?> >016</option>
						</select>
						<input type="text" name="penConNum2" class="form-control input-sm" value="<?=substr($data["penConNum"], 3, 4)?>" id="penConNum2" style="display:inline-block; width:30%;">
						<input type="text" name="penConNum3" class="form-control input-sm" value="<?=substr($data["penConNum"], 7, 4)?>" id="penConNum3" style="display:inline-block; width:30%;">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>


				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>일반전화</b>
					</label>
					<div class="col-sm-3">
                        <select class="form-control input-sm" name="penConPnum1" style="display:inline-block; width:30%;" id="penConPnum1" >
                        <option value="02" <?=(substr($data["penConNum"], 0, 2) == "02") ? "selected" : ""?> >02 </option>
                          <option value="031" <?=(substr($data["penConNum"], 0, 3) == "031") ? "selected" : ""?>>031 </option>
                          <option value="032" <?=(substr($data["penConNum"], 0, 3) == "032") ? "selected" : ""?>>032</option>
                          <option value="033" <?=(substr($data["penConNum"], 0, 3) == "033") ? "selected" : ""?>>033 </option>
                          <option value="041" <?=(substr($data["penConNum"], 0, 3) == "041") ? "selected" : ""?>>041 </option>
                          <option value="042" <?=(substr($data["penConNum"], 0, 3) == "042") ? "selected" : ""?>>042 </option>
                          <option value="043" <?=(substr($data["penConNum"], 0, 3) == "043") ? "selected" : ""?>>043 </option>
                          <option value="051" <?=(substr($data["penConNum"], 0, 3) == "051") ? "selected" : ""?>>051 </option>
                          <option value="052" <?=(substr($data["penConNum"], 0, 3) == "052") ? "selected" : ""?>>052 </option>
                          <option value="053" <?=(substr($data["penConNum"], 0, 3) == "053") ? "selected" : ""?>>053 </option>
                          <option value="054" <?=(substr($data["penConNum"], 0, 3) == "054") ? "selected" : ""?>>054 </option>
                          <option value="055" <?=(substr($data["penConNum"], 0, 3) == "055") ? "selected" : ""?>>055 </option>
                          <option value="061" <?=(substr($data["penConNum"], 0, 3) == "061") ? "selected" : ""?>>061 </option>
                          <option value="062" <?=(substr($data["penConNum"], 0, 3) == "062") ? "selected" : ""?>>062 </option>
                          <option value="063" <?=(substr($data["penConNum"], 0, 3) == "063") ? "selected" : ""?>>063 </option>
                          <option value="064" <?=(substr($data["penConNum"], 0, 3) == "064") ? "selected" : ""?>>064 </option>
						</select>
						<input type="text" name="penConPnum2" class="form-control input-sm" style="display:inline-block; width:30%;" id="penConPnum2" >
						<input type="text" name="penConPnum3" class="form-control input-sm" style="display:inline-block; width:30%;" id="penConPnum3" >
					</div>
				</div> -->

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>유효기간(시작일)</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="penExpiStDtm" value="<?=$data["penExpiDtm"][0]?>" class="form-control input-sm" dateonly2>
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>유효기간(종료일)</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="penExpiEdDtm" value="<?=$data["penExpiDtm"][1]?>" class="form-control input-sm" dateonly>
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>본인부담금율</b>
					</label>
					<div class="col-sm-3">
						<select class="form-control input-sm" name="penTypeCd">
							<option value="00" <?=($data["penTypeCd"] == "00") ? "selected" : ""?>>일반 15%</option>
							<option value="01" <?=($data["penTypeCd"] == "01") ? "selected" : ""?>>감경 9%</option>
							<option value="02" <?=($data["penTypeCd"] == "02") ? "selected" : ""?>>감경 6%</option>
							<option value="03" <?=($data["penTypeCd"] == "03") ? "selected" : ""?>>의료 6%</option>
							<option value="04" <?=($data["penTypeCd"] == "04") ? "selected" : ""?>>기초 0%</option>
						</select>
					</div>
				</div>

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
						<input type="text" name="penConNum" value="<?=$data["penConNum"]?>" class="form-control input-sm">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>일반전화</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="penConPnum" value="<?=$data["penConPnum"]?>" class="form-control input-sm">
					</div>
				</div>

				<div class="form-group has-feedback" style="margin-bottom: 0;">
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
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading"><strong>보호자 정보</strong></div>
			<div class="panel-body">
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
						<b>관계</b>
					</label>
					<div class="col-sm-3">
						<select class="form-control input-sm" name="penProRel" style="margin-bottom: 5px;">
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
						<input type="text" name="penProRelEtc" value="<?=$data["penProRelEtc"]?>" class="form-control input-sm" <?=($data["penProRel"] == "11") ? "" : "readonly"?>>
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

				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>담당직원정보</b>
					</label>
					<div class="col-sm-3">
                    <input type="text" name="entUsrId" class="form-control input-sm"  value="<?=$member['mb_giup_boss_name']?>" placeholder="담당직원정보">
						<!-- <select class="form-control input-sm" name="entUsrId">
							<option value="testosw" <?=($data["usrId"] == "testosw") ? "selected" : ""?>>testosw</option>
							<option value="test4" <?=($data["usrId"] == "test4") ? "selected" : ""?>>관리자2</option>
							<option value="poongki" <?=($data["usrId"] == "poongki") ? "selected" : ""?>>백진수</option>
							<option value="123456789" <?=($data["usrId"] == "123456789") ? "selected" : ""?>>사업소대표</option>
							<option value="uxis" <?=($data["usrId"] == "uxis") ? "selected" : ""?>>유시스</option>
							<option value="dsadsa" <?=($data["usrId"] == "dsadsa") ? "selected" : ""?>>테스트사업소2직원</option>
						</select> -->
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

				<!-- <div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>콜센터</b>
					</label>
					<div class="col-sm-3">
						<label class="checkbox-inline">
							<input type="radio" name="caCenYn" value="Y" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["caCenYn"] == "Y") ? "checked" : ""?>>등록
						</label>

						<label class="checkbox-inline">
							<input type="radio" name="caCenYn" value="N" style="vertical-align: middle; margin: 0 5px 0 0;" <?=($data["caCenYn"] == "N") ? "checked" : ""?>>미등록
						</label>
					</div>
				</div> -->
			</div>
		</div>
		<!-- 20210307 성훈작업 -->
		<style media="screen">
		input[type="checkbox"], input[type=checkbox] {margin: 4px 0 0; margin-top: 1px \9;line-height: normal;}
		.col-dealing{ width:80%; text-align: left;}
		.dealing{	margin-left: 0px;}
		</style>

		<div class="panel panel-default">
			<div class="panel-heading"><strong>취급가능 제품</strong></div>
			<div class="panel-body">
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>판매품목</b>
					</label>
						<div class="col-sm-3 col-dealing">
							<?php for($i=1; $i<14; $i++){
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
							?>
							<label class="checkbox-inline dealing" style="margin-left: 0px; width:146px;">
								<input type="checkbox" name="<?=${'sale_product_id'.$i}; ?>" id="<?="sale_product_id".$i; ?>" value="<?=${'sale_product_id'.$i}; ?>" style="" ><?=${'sale_product_name'. $i}; ?>
							</label>
						<?php } ?>
						</div>
				</div>


				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>대여품목</b>
					</label>
						<div class="col-sm-3 col-dealing">
							<?php for($i=0; $i<8; $i++){
								$rental_product_name0="욕창예방매트리스"; $rental_product_id0="ITM2020092200019";
								$rental_product_name1="경사로(실외용)"; $rental_product_id1="ITM2020092200018";
								$rental_product_name2="배회감지기"; $rental_product_id2="ITM2020092200017";
								$rental_product_name3="목욕리프트"; $rental_product_id3="ITM2020092200016";
								$rental_product_name4="이동욕조"; $rental_product_id4="ITM2020092200015";
								$rental_product_name5="수동침대"; $rental_product_id5="ITM2020092200014";
								$rental_product_name6="전동침대"; $rental_product_id6="ITM2020092200013";
								$rental_product_name7="수동휠체어"; $rental_product_id7="ITM2020092200012";
							?>
							<label class="checkbox-inline dealing" style="margin-left: 0px; width:146px;">
								<input type="checkbox" name="<?=${'rental_product_id'. $i}; ?>" id="<?='rental_product_id'.$i; ?>" value="<?=${'rental_product_id'. $i}; ?>" style="" ><?=${'rental_product_name'. $i}; ?>
							</label>
						<?php } ?>
						</div>
				</div>
			</div>
		</div>
<script>

<?php
    for($i=0;$i<count($data2);$i++){
        echo 'document.getElementsByName("'.$data2[$i]['itemId'].'")[0].checked = true;';
    }
?>
</script>

<!-- 20210307 성훈작업 -->
		<div class="text-center" style="margin-top: 30px;">
			<button type="button" id="btn_submit" class="btn btn-color">수정</button>
			<a href="./my.recipient.list.php" class="btn btn-black" role="button">취소</a>
		</div>
	</form>

	<script type="text/javascript">
			var zipPopupDom = document.getElementById("zipAddrPopupIframe");

            $(document).ready(function () {
                setDateBox();
                //생년월일 세팅
                var penJumin1 =  document.getElementById('penJumin1');
                var penProBirth =  document.getElementById('penProBirth');
                var year=penJumin1.value.substring(0,2);
                var month=penJumin1.value.substring(2,4);
                var day=penJumin1.value.substring(4,6);

                var year2=penProBirth.value.substring(0,2);
                var month2=penProBirth.value.substring(2,4);
                var day2=penProBirth.value.substring(4,6);

                if( year < <?=substr(date("Y"),2,2) ?> ){ 
                    year='20'+year; 
                }else {
                        year='19'+year; 
                }

                if( year2 < <?=substr(date("Y"),2,2) ?> ){ 
                    year2='20'+year2; 
                }else {
                        year2='19'+year2; 
                }
                $(".register-form select[name='penBirth1']").val(year);
                $(".register-form select[name='penBirth2']").val(month);
                $(".register-form select[name='penBirth3']").val(day);

                
                $(".register-form select[name='penProBirth1']").val(year2);
                $(".register-form select[name='penProBirth2']").val(month2);
                $(".register-form select[name='penProBirth3']").val(day2);
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
                if(i<10){first_num = 0;}
                $(".month").append("<option value='"+first_num + i + "'>"+first_num + i+"</option>");
                }

                // 일 뿌려주기(1일부터 31일)
                var day;
                $(".day").append("<option value=''>일</option>");
                for (var i = 1; i <= 31; i++) {
				var first_num="";
                if(i<10){first_num = 0;}
                $(".day").append("<option value='" + first_num+i + "'>" +first_num+ i + "</option>");
                }

            }
            //주민번호 체크
            $('#penJumin1').on('keyup', function(){
                if(this.value.length == 6 ){
                    var year=this.value.substring(0,2);
                    var month=this.value.substring(2,4);
                    var day=this.value.substring(4,6);
                    if( year < <?=substr(date("Y"),2,2) ?> ){ 
                        year='20'+year; 
                    }else {
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
                if (object.value.length > object.maxLength){
                object.value = object.value.slice(0, object.maxLength);
                }
            }


			function zipPopupClose(){
				$("#zipAddrPopupWrap").hide();
			}

			function zipPopupOpen(target){
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

			$("input:text[dateonly]").datepicker({});

			$("input:text[dateonly2]").datepicker({
				maxDate : "<?=date("Y-m-d")?>"
			});
			$("#zipAddrPopupWrap").css("opacity", 1);
			$("#zipAddrPopupWrap").hide();

			$(".register-form select[name='penProRel']").change(function(){
				$(".register-form input[name='penProRelEtc']").prop("readonly", true);
				$(".register-form input[name='penProRelEtc']").val("");

				if($(this).val() == "11"){
					$(".register-form input[name='penProRelEtc']").prop("readonly", false);
				}
			});

			$("#btn_submit").click(function(){
				var importantIcon = $(".register-form .form-control-feedback");
				for(var i = 0; i < importantIcon.length; i++){
					var item = $(importantIcon[i]).prev();
					if(!$(item).val()){
						alert("필수값을 입력해주시길 바랍니다.");
						$(item).focus();
						return false;
					}
				}
                var penJumin1 =  document.getElementById('penJumin1');
                var penJumin2 =  document.getElementById('penJumin2');
                var penLtmNum =  document.getElementById('penLtmNum');

                if(!penJumin1.value){  alert('주민번호 앞자리를 입력해주새요.');  $(penJumin1).focus(); return false;}
                if(penJumin1.value.length !== 6){  alert('주민번호 앞자리는 6자리입니다.'); $(penJumin1).focus(); return false;}
                if(!penJumin2.value){  alert('주민번호 뒷자리를 입력해주새요.');  $(penJumin2).focus(); return false;}
                if(penJumin2.value.length !== 7){  alert('주민번호 뒷자리는 7자리입니다.');  $(penJumin2).focus(); return false;}
                if(penLtmNum.value.length !== 10){  alert('장기요양번호는 10자리입니다.');  $(penJumin2).focus(); return false;}

                var penJumin = penJumin1.value+penJumin2.value;
                var penBirth = $(".register-form select[name='penBirth1']").val()+'-'
                + $(".register-form select[name='penBirth2']").val()+'-'
                + $(".register-form select[name='penBirth3']").val();

                var penProBirth = $(".register-form select[name='penProBirth1']").val()+'-'
                + $(".register-form select[name='penProBirth2']").val()+'-'
                + $(".register-form select[name='penProBirth3']").val();

				if(penBirth.length !== 10){ alert("보호자 생년월일을 확인하세요."); return false;}
                if(penProBirth.length !== 10){ penProBirth=""; }

				var sendData = {
					penId : "<?=$data["penId"]?>",
					penNm : $(".register-form input[name='penNm']").val(),
					penLtmNum : "L" + $(".register-form input[name='penLtmNum']").val(),
					penRecGraCd : $(".register-form select[name='penRecGraCd']").val(),
					penGender : $(".register-form input[name='penGender']:checked").val(),
                    penBirth : penBirth,
					penJumin : penJumin,
					penTypeCd : $(".register-form select[name='penTypeCd']").val(),
					penConNum : $(".register-form input[name='penConNum']").val(),
					penConPnum : $(".register-form input[name='penConPnum']").val(),
					penExpiStDtm : $(".register-form input[name='penExpiStDtm']").val(),
					penExpiEdDtm : $(".register-form input[name='penExpiEdDtm']").val(),
					penAppStDtm1 : $(".register-form input[name='penExpiStDtm']").val(),
					penAppEdDtm1 : $(".register-form input[name='penExpiEdDtm']").val(),
					penAppStDtm2 : $(".register-form input[name='penExpiStDtm']").val(),
					penAppEdDtm2 : $(".register-form input[name='penExpiEdDtm']").val(),
					penAppStDtm3 : $(".register-form input[name='penExpiStDtm']").val(),
					penAppEdDtm3 : $(".register-form input[name='penExpiEdDtm']").val(),
					penRecDtm : "0000-00-00",
					penAppDtm : "0000-00-00",
					penZip : $(".register-form input[name='penZip']").val(),
					penAddr : $(".register-form input[name='penAddr']").val(),
					penAddrDtl : $(".register-form input[name='penAddrDtl']").val(),
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
					entId : "<?=$member["mb_entId"]?>",
					entUsrId : $(".register-form input[name='entUsrId']").val(),
					appCd : "<?=$data["appCd"]?>",
					caCenYn : $(".register-form input[name='caCenYn']:checked").val(),
					usrId : "<?=$member["mb_id"]?>",
					delYn : "N"
				}

				$.ajax({
					url : "./ajax.my.recipient.update.php",
					type : "POST",
					async : false,
					data : sendData,
					success : function(result){
						result = JSON.parse(result);
						if(result.errorYN == "Y"){
							alert(result.message);
						} else {
                            //취급품목
                            var sendData2 = {
                                penId : "<?=$data["penId"]?>",
                            }

                            var itemList=[];
                            var sale_product_id="";
                            var rental_product_id="";
                            //판매품목 값 넣기
                            for(var i=1; i<14; i++){
                                eval("sale_product_id = document.getElementById('sale_product_id"+i+"')");
                                if(sale_product_id.checked==true){ itemList.push(sale_product_id.value); }
                            }
                            //대여품목 값 넣기
                            for(var i=0; i<8; i++){
                                eval("rental_product_id = document.getElementById('rental_product_id"+i+"')");
                                if(rental_product_id.checked==true){ itemList.push(rental_product_id.value); }
                            }

                            sendData2['itemList']=itemList;
                            $.ajax({
                                url : "./ajax.my.recipient.setItem.php",
                                type : "POST",
                                async : false,
                                data : sendData2,
                                success : function(result){
                                    result = JSON.parse(result);
                                    if(result.errorYN == "Y"){
                                        alert(result.message);
                                    } else {
                                        alert('완료되었습니다');
                                        window.location.href = "./my.recipient.list.php";
                                    }
                                        }
                            });
						}
					}
				});

			});

		})
	</script>

<?php include_once("./_tail.php"); ?>
