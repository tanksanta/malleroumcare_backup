<?php

	include_once("./_common.php");
	include_once("./_head.php");

	# 회원검사
	if(!$member["mb_id"]){
		alert("접근 권한이 없습니다.");
		return false;
	}

?>

	<script src="//spi.maps.daum.net/imap/map_js_init/postcode.v2.js"></script>
	<script src="<?=G5_JS_URL?>/jquery.register_form.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<style>
		#ui-datepicker-div { z-index: 999 !important; }
	</style>
	
	<form class="form-horizontal register-form">
		<div class="panel panel-default">
			<div class="panel-heading"><strong>기본정보</strong></div>
			<div class="panel-body">
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>수급자명</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>생년월일</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm" dateonly>
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>장기요양인정번호</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>인정등급</b>
					</label>
					<div class="col-sm-3">
						<select class="form-control input-sm">
							<option value="00">등급외</option>
							<option value="01">1등급</option>
							<option value="02">2등급</option>
							<option value="03">3등급</option>
							<option value="04">4등급</option>
							<option value="05">5등급</option>
						</select>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>유효기간(시작일)</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm" dateonly>
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>유효기간(종료일)</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm" dateonly>
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>본인부담금율</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>성별</b>
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
						<b>휴대전화</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
						<i class="fa fa-check form-control-feedback"></i>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>일반전화</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback" style="margin-bottom: 0;">
					<label class="col-sm-2 control-label">
						<b>주소</b>
					</label>
					
					<div class="col-sm-8">
						<label for="reg_mb_zip" class="sound_only">우편번호</label>
						<label>
							<input type="text" name="mb_zip" class="form-control input-sm" size="6" maxlength="6" readonly>
						</label>
						<label>
							<button type="button" class="btn btn-black btn-sm win_zip_find" style="margin-top:0px;">주소 검색</button>
						</label>

						<div class="addr-line" style="margin-bottom: 5px;">
							<label class="sound_only">기본주소</label>
							<input type="text" name="mb_addr2" class="form-control input-sm" placeholder="기본주소" readonly>
						</div>

						<div class="addr-line">
							<label class="sound_only">상세주소</label>
							<input type="text" name="mb_addr2" class="form-control input-sm" placeholder="상세주소">
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
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>생년월일</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm" dateonly>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>관계</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>이메일</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>휴대전화</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>일반전화</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>주소</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>담당직원정보</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading"><strong>장기요양급여 제공기록지</strong></div>
			<div class="panel-body">
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>확인자</b>
					</label>
					<div class="col-sm-3">
						<label class="checkbox-inline">
							<input type="radio" name="penCnmTypeCd" value="PEN00004" style="vertical-align: middle; margin: 0 5px 0 0;" checked>수급자
						</label>
						
						<label class="checkbox-inline">
							<input type="radio" name="penCnmTypeCd" value="PEN00004" style="vertical-align: middle; margin: 0 5px 0 0;">확인자
						</label>
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>수령방법</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
				
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label">
						<b>특이사항</b>
					</label>
					<div class="col-sm-3">
						<input type="text" name="mb_name" class="form-control input-sm">
					</div>
				</div>
			</div>
		</div>
	
		<div class="text-center" style="margin-top: 30px;">
			<button type="button" id="btn_submit" class="btn btn-color">등록</button>
			<a href="./my.recipient.list.php" class="btn btn-black" role="button">취소</a>
		</div>
	</form>
	
	<script type="text/javascript">
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
				changeYear: true
			});
			
			$("input:text[dateonly]").datepicker();
			
		})
	</script>

<?php include_once("./_tail.php"); ?>