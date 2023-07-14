<style>
  @media (min-width: 10px) and (max-width: 1022px) {
	  .popup_box_bottom {
			width:100%;
			top:91%; left:0%;margin-left:0px;
		}
		.popup_box_con {
			position: relative;
			width:412px;
			margin-left:206px;
			left: 50%;
			top: 50%;
		}
	}
	@media (min-width: 1023px){
	  .popup_box_bottom {
			width:1000px;
			top:80%; left:50%;margin-left:-500px;
		}
		.popup_box_con {
			position: relative;
			width:412px;
			margin-left:206px;
			left: 50%;
			top: 50%;
		}
	}
	.popup_box_bottom {
			z-index:99999999; background:#ededed; position:relative; height:82px; padding:10px 20px;
		}

	.popup_box2 {
		display: none;
		position: fixed;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		z-index: 9999;
		background: rgba(0, 0, 0, 0.8);		
	}
	.popup_box_con {
		padding:20px 10px;
		position: relative;
		background: #ffffff;
		z-index: 99999;
		margin-left:-206px;
	}
	.se_sch_hd2 {
		font-size: 18px;
		font-weight: bold;
		margin-right: 20px;
	}
	.form-group {
		margin-right: -15px;
		margin-left: -15px;
	}
	#loading {
      display: none;
      background-color: rgba(0,0,0,0.7);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
	  z-index : 9999999999999999 !important;
    }

    #loading > div {
      position: relative;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
	  
    }

    #loading img {
      top: 50%;
      left: 50%;
	  margin-left : -75px; 
	  width: 150px;
	  position: relative;
    }

    #loading p {
      color: #fff;
      position: relative;
      top: -25px;
    }
</style>
<div id="loading" style="display: none">
  <div>
    <img src="../adm/shop_admin/img/ajax-loading.gif" class="img-responsive">
    <p>잠시만 기다려주세요...</p>
  </div>
</div>
<form method="post" action="" name="sign_send_form" id="sign_send_form">
<input type="hidden" name="div" id="div" value="new_doc">
<input type="hidden" name="dc_id1" id="dc_id1" value="">
<input type="hidden" name="mb_entId1" id="mb_entId1" value="<?=$member['mb_entId']?>">
<input type="hidden" name="title" id="title" value="">
<div id="popup_box7" class="popup_box2 list_box">
    <div id="" class="popup_box_con" style="height:340px;margin-top:-170px;">
		<div class="form-group">
            <div class="se_sch_hd2" style="margin-left:30px;">서명진행 ( 수급자 : <span id="sign_penNm"></span> )</div>
        </div>
		<div id="" style="float:left;width:100%;margin-left:15px;">
			<table style="width:357px">
			<tr>
				<th width="80">서명대상자</th>
				<th>서명방식</th>
				<th>전화번호</th>
			</tr>
			<tr id="pen_row">
				<td><label><input type="checkbox" name="pen_sign" id="pen_sign" >&nbsp;수급자</label></td>
				<td><select name="pen_send" id="pen_send" class="form-control input-sm">
					<option value="SECURE_LINK" >웹페이지</option>
					<option value="KAKAO" >카카오톡</option>
				  </select></td>
				<td><input type="text" name="pen_send_tel" id="pen_send_tel" class="form-control input-sm" placeholder="수급자 전화번호 입력" pattern="[0-9]+" oninput="this.value = this.value.replaceAll(/\D/g, '')"><input type="hidden" name="name1" id="name1" value=""></td>
			</tr>
			<tr id="contract_row">
				<td><label><input type="checkbox"  name="contract_sign" id="contract_sign" >&nbsp;대리인</label></td>
				<td><select name="contract_send" id="contract_send" class="form-control input-sm">
					<option value="SECURE_LINK" >웹페이지</option>
					<option value="KAKAO" >카카오톡</option>
				  </select></td>
				<td><input type="text" name="contract_send_tel" id="contract_send_tel" class="form-control input-sm" placeholder="대리인 전화번호 입력" pattern="[0-9]+" oninput="this.value = this.value.replaceAll(/\D/g, '')"><input type="hidden" name="name2" id="name2" value=""></td>
			</tr>
			<tr id="applicant_row"  style="display:none;">
				<td><label><input type="checkbox"  name="applicant_sign" id="applicant_sign" >&nbsp;신청인</label></td>
				<td><select name="applicant_send" id="applicant_send" class="form-control input-sm">
					<option value="SECURE_LINK" >웹페이지</option>
					<option value="KAKAO" >카카오톡</option>
				  </select></td>
				<td><input type="text" name="applicant_send_tel" id="applicant_send_tel" class="form-control input-sm" placeholder="신청인 전화번호 입력" pattern="[0-9]+" oninput="this.value = this.value.replaceAll(/\D/g, '')"><input type="hidden" name="name3" id="name3" value=""></td>
			</tr>
			
			</table>
      </div>
	
	  <div id="" class="" style="float:left;width:100%;padding:10px 15px;">
		서명 요청을 진행 하시겠습니까?
	  </div>
	  <div style="text-align:right;bottom:0px;float:left;width:100%;">
			 <button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button> <button type="button" id="btn-sign-submit" class="btn btn-black btn-sm" style="margin-right:15px;background:black;" onClick="send_sign()">진행하기</button>
		</div>
	</div>
	
</div>
</form>

<div id="popup_box8" class="popup_box2 list_box">
    <div id="" class="popup_box_con" style="height:300px;margin-top:-150px;">
		<div class="form-group">
            <div class="se_sch_hd2" style="margin-left:30px;">서명 진행상황 확인</div>
        </div>
		<div id="" style="float:left;width:100%;margin-left:15px;">
			<table style="width:357px">
			<tr >
				<th>대상자</th>
				<th>방식</th>
				<th>상태</th>
				<th style="width:151px;">날짜</th>
			</tr>
			<tr id="row1">
				<td>수급자</td>
				<td align="center"><span id="gubun1">-</span></td>
				<td align="center"><span id="stat1">대상아님</span></td>
				<td align="center"><span id="sign_date1">-</span></td>
			</tr>
			<tr id="row2">
				<td align="center">대리인</td>
				<td align="center"><span id="gubun2">-</span></td>
				<td align="center"><span id="stat2">대상아님</span></td>
				<td align="center"><span id="sign_date2">-</span></td>
			</tr>
			<tr id="row3" style="display:none;">
				<td>신청인</td>
				<td align="center"><span id="gubun3">-</span></td>
				<td align="center"><span id="stat3">대상아님</span></td>
				<td align="center"><span id="sign_date3">-</span></td>
			</tr>			
			</table>
		</div>
		<div id="" class="" style="float:left;width:100%;padding:10px 15px;">
		</div>
		<div style="text-align:right;bottom:0px;float:left;width:100%;">
			<button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button>&nbsp;<button type="button" class="btn btn-black btn-sm " style="margin-right:15px;background:green;" onClick="sign_resend()">서명요청 재전송</button><input type="hidden" name="doc_id" id="doc_id">
		</div>
	</div>	
</div>
<div id="popup_box9" class="popup_box2 list_box">
    <div id="" class="popup_box_con" style="height:300px;margin-top:-150px;">
		<div class="form-group">
            <div class="se_sch_hd2" style="margin-left:30px;">서명 거절 사유 보기</div>
        </div>
		<div id="" style="float:left;width:100%;margin-left:15px;">
			<table style="width:357px">
			<tr >
				<th>대상자</th>
				<td style="border-top:1px solid #ddd;"><span id="rejection_member"></span></td>
				<th>상태</th>
				<td style="border-top:1px solid #ddd;"><span id="rejection_date"></span></td>
			</tr>

			<tr height="120px;">
				<th>거절 사유</th>
				<td colspan="3"><span id="rejection_msg"></span></td>
			</tr>
			</table>
		</div>
		<div id="" class="" style="float:left;width:100%;padding:10px 15px;">
		</div>
		<div style="text-align:right;bottom:0px;float:left;width:100%;">
			<button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button>
		</div>
	</div>	
</div>
<div id="iframe_wrap" class="" style="display:none;text-align:right;position:fixed;top:100px;z-index:999999;width:1105px;background-color:#ffffff">
<span class="" style="right:0px;cursor:pointer" onClick="javascript:$('#iframe_wrap').css('display','none');$('#view_doc').attr('src','');$('body').removeClass('modal-open');">X 닫기</span>
	<iframe src="" id="view_doc" 
 style="width:100%; height:700px;border:0px;">

</iframe>
</div>

<script>
	$('.btn_close').click(function() {			
		$('body').removeClass('modal-open');
		$('#popup_box7').hide();
		$('#popup_box8').hide();
		$('#popup_box9').hide();
	});
		// 서명 진행 팝업 
	function open_send_sign(dc_id) {
		$('#sign_send_form')[0].reset();
		$("#dc_id1").val(dc_id);//문서번호
		$("#applicant_sign").attr("checked",false);
		$("#contract_sign").attr("checked",false);
		$("#pen_sign").attr("checked",false);
		$("#sign_send_form").find("input,textarea,select").prop("disabled",true);
		$.post('/shop/eform/ajax.eform.info.php', {
			dc_id:dc_id
		}, 'json')
		.done(function(data) {
			$("#dc_id1").val(dc_id).prop("disabled",false);
			$("#sign_penNm").text(data.penNm);
			$("#title").val(data.dc_subject).prop("disabled",false);//계약서타이틀
			$("#name1").val(data.penNm).prop("disabled",false);//수급자
			$("#name2").val(data.contract_sign_name).prop("disabled",false);//대리인
			$("#name3").val(data.applicantNm).prop("disabled",false);//신청자
			$("#div").val("new_doc").prop("disabled",false);//신청자
			$("#sign_penNm").text(data.penNm);
			if(data.applicantRelation != '0' && data.applicantRelation != '' && data.applicantRelation != '4'){// 신청인이 있을 경우,신청인이 대리인이 아닐경우						
				//$("#applicant_sign").attr("disabled",false);
				//$("#applicant_sign").attr("checked",true);
				//$("#applicant_send").attr("disabled",false);
				//$("#applicant_send_tel").attr("disabled",false);
				//$("#applicant_send_tel").val(data.applicantTel);
			}
			if(data.contract_sign_type == '1'){//대리인이 있을경우
				$("#contract_sign").attr("disabled",false);
				$("#contract_sign").attr("checked",true);
				$("#contract_send").attr("disabled",false);
				$("#contract_send_tel").attr("disabled",false);
				$("#contract_send_tel").val(data.contract_tel);
			}else{//본인인 경우
				$("#pen_sign").attr("disabled",false);
				$("#pen_sign").attr("checked",true);
				$("#pen_send").attr("disabled",false);
				$("#pen_send_tel").attr("disabled",false);
				$("#pen_send_tel").val(data.penConNum);
			}
			$('body').addClass('modal-open');
			$('#popup_box7').show();
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});		
	}
	
	function loading_onoff(a){
		if(a == "on" ){
			$('body').css('overflow-y', 'hidden');
			$('#loading').show();
		}else{
			$('body').css('overflow-y', 'scroll');
			$('#loading').hide(); 
		}
	}
	//서명진행 요청
	function send_sign(){
		if($("#applicant_sign").is(':checked') == false && $("#contract_sign").is(':checked') == false && $("#pen_sign").is(':checked') == false){//요청자 선택이 안되었을때
			alert("서명 대상자가 선택 되지 않았습니다.\n서명 대상자를 선택 해 주세요.");
			return false;
		}		
		if($("#pen_sign").is(':checked') == true){//신청인이 선택 되었을 경우
			var pen_tel = true;
			if($("#pen_send_tel").val() == ""){
				alert("수급자 전화번호를 입력해주세요.");
				pen_tel = false;
			}else if(!$.isNumeric($("#pen_send_tel").val())){
				alert("수급자 전화번호를 숫자만 입력해주세요.");
				pen_tel = false;
			}else if($("#pen_send_tel").val().length < 10){
				alert("수급자 전화번호를 10자 이상 입력해주세요.");
				pen_tel = false;
			}
			if(pen_tel == false){
				$("#pen_send_tel").focus();
				return false;
			}
		}
		
		if($("#contract_sign").is(':checked') == true){//신청인이 선택 되었을 경우
			var contract_tel = true;
			if($("#contract_send_tel").val() == ""){
				alert("대리인 전화번호를 입력해주세요.");
				contract_tel = false;
			}else if(!$.isNumeric($("#contract_send_tel").val())){
				alert("대리인 전화번호를 숫자만 입력해주세요.");
				contract_tel = false;
			}else if($("#contract_send_tel").val().length < 10){
				alert("대리인 전화번호를 10자 이상 입력해주세요.");
				contract_tel = false;
			}
			if(contract_tel == false){
				$("#contract_send_tel").focus();
				return false;
			}
		}
		if($("#applicant_sign").is(':checked') == true && $("#applicant_send_tel").val() == ""){//신청인이 선택 되었을 경우
			alert("신청인 전화번호를 입력해주세요.");
			$("#applicant_send_tel").focus();
			return false;
		}
		if($("#pen_send_tel").val() != ""){
			if($("#applicant_send_tel").val() != "" && $("#pen_send_tel").val() == $("#applicant_send_tel").val()){
				alert("수급자와 신청인의 전화번호를 다르게 입력해 주세요.");
				$("#applicant_send_tel").val("");
				$("#applicant_send_tel").focus();
				return false;
			}
		}
		if($("#contract_send_tel").val() != ""){
			if($("#applicant_send_tel").val() != "" && $("#contract_send_tel").val() == $("#applicant_send_tel").val()){
				alert("대리인과 신청인의 전화번호를 다르게 입력해 주세요.");
				$("#applicant_send_tel").val("");
				$("#applicant_send_tel").focus();
				return false;
			}
		}
		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_mds_api.php', {
			div : "new_doc"
			,mb_entId1 : $("#mb_entId1").val()
			,dc_id1 : $("#dc_id1").val()
			,title : $("#title").val()
			,applicant_sign : ($("#applicant_sign").is(":checked"))?"1": ""
			,contract_sign : ($("#contract_sign").is(":checked"))?"1": ""
			,pen_sign : ($("#pen_sign").is(":checked"))?"1": ""
			,pen_send : ($("#pen_sign").is(":checked"))?$("#pen_send").val(): ""
			,applicant_send : ($("#applicant_sign").is(":checked"))?$("#applicant_send").val(): ""
			,contract_send : ($("#contract_sign").is(":checked"))?$("#contract_send").val(): ""
			,pen_send_tel : ($("#pen_sign").is(":checked"))?$("#pen_send_tel").val(): ""
			,applicant_send_tel : ($("#applicant_sign").is(":checked"))?$("#applicant_send_tel").val(): ""
			,contract_send_tel : ($("#contract_sign").is(":checked"))?$("#contract_send_tel").val(): ""
			,name1 : ($("#pen_sign").is(":checked"))?$("#name1").val(): ""
			,name2 : ($("#contract_sign").is(":checked"))?$("#name2").val(): ""
			,name3 : ($("#applicant_sign").is(":checked"))?$("#name3").val(): ""
		}, 'json')
		.done(function(data) {
			if(data.api_stat != "1"){
				loading_onoff('off');
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}
			
			if(data.url != "url생성실패"){
				loading_onoff('off');
				$('body').removeClass('modal-open');
				$('#popup_box7').hide();
				location.reload();
			}else{
				alert(res.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  loading_onoff('off');
		  alert(data && data.message);
		});	
		
		
	}

	// 서명 진행 상황 확인 시 
	function open_sign_stat(dc_id) {
		for(var i=1; i<4; i++){
			$("#row"+i).css("background","#eeeeee");
			$("#gubun"+i).text("-");
			$("#stat"+i).html("대상아님");
			$("#sign_date"+i).text("-");

		}
		var name1 = "";
		var name2 = "";
		var name3 = "";
		
 		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_mds_api.php', {
			dc_id:dc_id,
			div:'sign_stat'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				loading_onoff('off');
				return false;				
			}
			//alert(data.gubun2);
			//alert(JSON.stringify(data));
			var sign_bt1 = "";
			var sign_bt2 = "";
			var sign_bt3 = "";
			
			if(data.applicantRelation != '0' && data.applicantRelation != '' && data.applicantRelation != '4'){// 신청인이 있을 경우,신청인이 대리인이 아닐경우						
				//$("#row3").css("background","#ffffff");
				if(data.stat3 == "진행중"){
					sign_bt3 = (data.gubun3 == "웹페이지")?'<button type="button" class="btn btn-sm btn-black" style="background:green;padding:5px;" onClick="sign_doc(\''+data.doc_id+'\',\''+data.part_id3+'\',\''+dc_id+'\')">진행중</button>':data.stat3;
				}else{
					sign_bt3 = data.stat3;
				}
				$("#stat3").html(sign_bt3);
				$("#gubun3").text(data.gubun3);
				$("#sign_date3").text(data.sign_date3);
			}
			if(data.contract_sign_type == '1'){//대리인이 있을경우
				$("#row2").css("background","#ffffff");
				if(data.stat2 == "진행중"){
					sign_bt2 = (data.gubun2 == "웹페이지")?'<button type="button" class="btn btn-sm btn-black" style="background:green;padding:5px;" onClick="sign_doc(\''+data.doc_id+'\',\''+data.part_id2+'\',\''+dc_id+'\')">진행중</button>':data.stat2;
				}else{
					sign_bt2 = data.stat2;
				}
				$("#stat2").html(sign_bt2);
				$("#gubun2").text(data.gubun2);
				$("#sign_date2").text(data.sign_date2);
			}else{//본인인 경우
				$("#row1").css("background","#ffffff");
				if(data.stat1 == "진행중"){
					sign_bt1 = (data.gubun1 == "웹페이지")?'<button type="button" class="btn btn-sm btn-black" style="background:green;padding:5px;" onClick="sign_doc(\''+data.doc_id+'\',\''+data.part_id1+'\',\''+dc_id+'\')">진행중</button>':data.stat1;
				}else{
					sign_bt1 = data.stat1;
				}
				$("#stat1").html(sign_bt1);
				$("#gubun1").text(data.gubun1);
				$("#sign_date1").text(data.sign_date1);
			}
			loading_onoff('off');
			$("#doc_id").val(data.doc_id);
			$('body').addClass('modal-open');
			$('#popup_box8').show();
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  loading_onoff('off');
		  alert(data && data.message);		  
		});	
	}

	// 거절 사유 보기
	function open_rejection_view(dc_id) {
		$("#rejection_date").text('');
		$("#rejection_msg").text('');
		$("#rejection_member").text('');

 		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_mds_api.php', {
			dc_id:dc_id,
			div:'rejection_view'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				loading_onoff('off');
				return false;				
			}
			//alert(data.gubun2);
			//alert(JSON.stringify(data));
			$("#rejection_date").text(data.date);
			$("#rejection_msg").text(data.msg);
			$("#rejection_member").text(data.member);
			loading_onoff('off');
			$('body').addClass('modal-open');
			$('#popup_box9').show();
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  loading_onoff('off');
		  alert(data && data.message);
		});	
	}
	// 서명 진행 
	function sign_doc(doc_id,part_id) {
		$.post('ajax.eform_mds_api.php', {
			doc_id:doc_id,
			part_id:part_id,
			div:'sign_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				loading_onoff('off');
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");				
				return false;				
			}
			if(data.url != "url생성실패"){				
				//$("#view_doc").attr("src",data.url);
				//$('#iframe_wrap').fadeIn( 'slow' );
				loading_onoff('off');
				window.open(data.url, "PopupDoc", "width=1200,height=900");
			}else{
				alert(data.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});	
	}

	// 계약서,감사추적인증서 보기 
	function mds_download(dc_id,gubun) {//1:계약서,2:감사추적인증서
 		$.post('ajax.eform_mds_api.php', {
			dc_id:dc_id,
			gubun:gubun,
			div:'view_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				loading_onoff('off');
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}
			if(data.url != "url생성실패"){				
				loading_onoff('off');
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

	// 요청취소 
	function sign_cancel(dc_id){
		var params = {
				div : "sign_cancel"
				,dc_id : dc_id
			};
		if(confirm("서명요청을 취소 하시겠습니까?")){
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_mds_api.php",      
				data : params, 
				dataType:"json",
				success : function(res){ 
					//alert(JSON.stringify(res));
					if(res.api_stat != "1"){
						loading_onoff('off');
						alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
						return false;				
					}
					if(res.url != "url생성실패"){				
						loading_onoff('off');
						alert("서명이 취소 되었습니다.");
						history.replaceState({}, null, location.pathname);
						location.reload();			
					}else{
						alert(res.url);//계약서 생성 실패 알림
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown){ 
					loading_onoff('off');
					alert("통신 실패.")
				}
			});
		}
	}

	// 거절 계약서 초기화 
	function dc_reset(dc_id){
		var params = {
				div : "dc_reset"
				,dc_id : dc_id
			};
		if(confirm("계약서 상태를 초기화 하시겠습니까?")){
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_mds_api.php",      
				data : params, 
				dataType:"json",
				success : function(res){ 
					if(res.api_stat != "1"){
						loading_onoff('off');
						alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
						return false;				
					}
					if(res.url != "url생성실패"){				
						loading_onoff('off');
						alert("계약서 상태가 초기화 되었습니다.");
						history.replaceState({}, null, location.pathname);
						location.reload();	
					}else{
						alert(res.url);//계약서 생성 실패 알림
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown){ 
					alert("통신 실패.")
				}
			});
		}
	}

	// 서명요청 재전송
	function sign_resend(){
		var params = {
				div : "sign_resend"
				,doc_id : $("#doc_id").val()
			};
		if(confirm("서명요청을 재전송 하시겠습니까?")){
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_mds_api.php",      
				data : params, 
				dataType:"json",
				success : function(res){ 
					if(res.api_stat != "1"){
						loading_onoff('off');
						alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
						return false;				
					}
					if(res.url != "url생성실패"){				
						loading_onoff('off');
						alert("재전송이 완료 되었습니다.");
					}else{
						loading_onoff('off');
						alert(res.url);//계약서 생성 실패 알림
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown){ 
					loading_onoff('off');
					alert("통신 실패.")
				}
			});
		}
	}
	//계약서 전송
	function resend_doc(dc_id){//계약서 재전송
		var params = {
				div : "resend_doc"
				,dc_id : dc_id
			};
		if(confirm("계약서를 전송 하시겠습니까?")){
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_mds_api.php",      
				data : params, 
				dataType:"json",
				success : function(res){ 
					if(res.api_stat != "1"){
						loading_onoff('off');
						alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
						return false;				
					}
					loading_onoff('off');
					alert("계약서 전송이 완료 되었습니다.");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown){ 
					loading_onoff('off');
					alert("통신 실패.")
				}
			});
		}
	}

</script>
