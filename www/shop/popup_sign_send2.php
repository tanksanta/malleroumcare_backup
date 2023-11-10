<?php $pen_tel = ($pen["penConNum"] != "")?$pen["penConNum"]:$pen["penConPnum"];
$pen_tel = str_replace("-","",$pen_tel);
$pen_tel = str_replace(" ","",$pen_tel);
$pen_tel = str_replace(".","",$pen_tel);
?>
<style>
  @media (min-width: 10px) and (max-width: 1022px) {
	  .popup_box_bottom {
			width:100%;
			top:91%; left:0%;margin-left:0px;
		}
/*		.popup_box_con {
			position: relative;
			width:412px;
			margin-left:206px;
			left: 50%;
			top: 50%;
		}
*/
	}
	@media (min-width: 1023px){
	  .popup_box_bottom {
			width:1000px;
			top:80%; left:50%;margin-left:-500px;
		}
/*		.popup_box_con {
			position: relative;
			width:412px;
			margin-left:206px;
			left: 50%;
			top: 50%;
		}
*/
	}
	.popup_box_bottom {
			z-index:99999999; background:#ededed; position:relative; height:82px; padding:10px 20px;
		}

	.se_sch_hd2 {
		font-size: 18px;
		font-weight: bold;
		margin-right: 20px;
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
    <div id="" class="popup_box_con" style="height:250px;margin-top:-125px;">
		<div class="form-group">
            <div class="se_sch_hd2" >서명요청</div>
        </div>
		<div id="" style="float:left;width:100%;">
			<table style="width:357px">
			<tr>
				<th width="90">수급자 관계</th>
				<th width="110">서명방식</th>
				<th>전화번호</th>
			</tr>
			<tr id="pen_row">
				<td height="65"><span id="relation" ></span></td>
				<td><select name="pen_send" id="pen_send" class="form-control input-sm">
					<option value="SECURE_LINK" >웹페이지</option>
					<option value="KAKAO" >카카오톡</option>
				  </select></td>
				<td><input type="text" name="pen_send_tel" id="pen_send_tel" class="form-control input-sm" placeholder="수급자 전화번호 입력" pattern="[0-9]+" oninput="this.value = this.value.replaceAll(/\D/g, '')"><input type="hidden" name="name1" id="name1" value=""></td>
			</tr>			
			</table>
      </div>
	
	  <div id="" class="" style="float:left;width:100%;padding:10px 0px;">
		서명 요청을 진행 하시겠습니까?
	  </div>
	  <div style="text-align:right;bottom:0px;float:left;width:100%;">
			 <button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button> <button type="button" id="btn-sign-submit" class="btn btn-black btn-sm" style="background:black;" onClick="send_sign()">진행하기</button>
		</div>
	</div>
	
</div>
</form>

<div id="popup_box8" class="popup_box2 list_box">
    <div id="" class="popup_box_con" style="height:230px;margin-top:-115px;">
		<div class="form-group">
            <div class="se_sch_hd2">서명 진행상황 확인</div>
        </div>
		<div id="" style="float:left;width:100%;">
			<table style="width:357px">
			<tr >
				<th width="100">수급자 관계</th>
				<th>방식</th>
				<th>상태</th>
				<th>요청날짜</th>
			</tr>
			<tr id="row1">
				<td><span id="relation1"></span></td>
				<td align="center"><span id="gubun1">카카오톡</span></td>
				<td align="center"><span id="stat1">진행중</span></td>
				<td align="center"><span id="sign_date1">-</span></td>
			</tr>			
			</table>
		</div>
		<div id="" class="" style="float:left;width:100%;padding:10px 0px;">
		</div>
		<div style="text-align:right;bottom:0px;float:left;width:100%;">
			<button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button>&nbsp;<button type="button" class="btn btn-black btn-sm " style="margin-right:15px;background:#ff004d;" onClick="sign_cancel()">서명요청 취소</button>&nbsp;<button type="button" class="btn btn-black btn-sm " style="background:green;" onClick="return sign_resend()">서명요청 재전송</button><input type="hidden" name="doc_id" id="doc_id">
		</div>
	</div>	
</div>
<div id="popup_box9" class="popup_box2 list_box">
    <div id="" class="popup_box_con" style="height:300px;margin-top:-150px;">
		<div class="form-group">
            <div class="se_sch_hd2">서명 거절 사유 보기</div>
        </div>
		<div id="" style="float:left;width:100%;">
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
		<div id="" class="" style="float:left;width:100%;padding:10px 0px;">
		</div>
		<div style="text-align:right;bottom:0px;float:left;width:100%;">
			<button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:15px;">돌아가기</button>&nbsp;<button type="button" class="btn btn-black btn-sm " style="background:#ff004d;" onClick="dc_del()">급여제공기록 삭제</button>
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
		$("#doc_id").val("");
		$('body').removeClass('modal-open');
		$('#popup_box7').hide();
		$('#popup_box8').hide();
		$('#popup_box9').hide();
	});
		// 서명 진행 팝업 
	function open_send_sign(rh_id,relation,relation_nm,pen_guardian_nm) {
		$('#sign_send_form')[0].reset();
		$("#dc_id1").val(rh_id);//문서번호
		$("#name1").val(pen_guardian_nm);//서명자이름
		var relation_text = "";
		switch(relation){
			case 1 : relation_text = "가족";$("#pen_send_tel").val(); break;
			case 2 : relation_text = "친족";$("#pen_send_tel").val(); break;
			case 3 : relation_text = "기타-"+pen_guardian_nm+"<br>("+relation_nm+")";$("#pen_send_tel").val(); break;
			default : relation_text = "본인";$("#pen_send_tel").val("<?=$pen_tel?>"); break;
		}
		$("#relation").html(relation_text);

		/*
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
			if(data.applicantRelation == '3' || (data.applicantRelation == '4' && data.contract_sign_relation == '3')){// 신청인이 기타 이거나 대리인이 기타일 경우						
				$("#applicant_sign").attr("disabled",false);
				$("#applicant_sign").attr("checked",true);
				$("#applicant_send").attr("disabled",false);
				$("#applicant_send_tel").attr("disabled",false);
				$("#applicant_send_tel").val(data.pen_guardian_tel);
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
		});	*/	
		$('body').addClass('modal-open');
		$('#popup_box7').show();
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
			var pen_tel = true;
			if($("#pen_send_tel").val() == ""){
				alert("전화번호를 입력해주세요.");
				pen_tel = false;
			}else if(!$.isNumeric($("#pen_send_tel").val())){
				alert("전화번호를 숫자만 입력해주세요.");
				pen_tel = false;
			}else if($("#pen_send_tel").val().length < 10 || $("#pen_send_tel").val().length > 11){
				alert("전화번호를 10자 이상, 11자 이하로 입력해주세요.");
				pen_tel = false;
			}
			if(pen_tel == false){
				$("#pen_send_tel").focus();
				return false;
			}
		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_rent_mds_api.php', {
			div : "new_doc"
			,dc_id1 : $("#dc_id1").val()
			,pen_send : $("#pen_send").val()
			,pen_send_tel : $("#pen_send_tel").val()
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
				//location.reload();
				rent_efrom_hist_open()//급여제공기록 관리 팝업 리로드
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
	function open_sign_stat(relation_html,rh_id,doc_id) {
		$("#relation1").html(relation_html);
		$("#doc_id").val(doc_id);
	
		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_rent_mds_api.php', {
			doc_id:doc_id,
			dc_id:rh_id,
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

				$("#row1").css("background","#ffffff");
				if(data.stat1 == "진행중"){
					sign_bt1 = (data.gubun1 == "웹페이지")?'<button type="button" class="btn btn-sm btn-black" style="background:green;padding:5px;" onClick="sign_doc(\''+doc_id+'\',\''+data.part_id1+'\',\''+rh_id+'\')">진행중</button>':data.stat1;
				}else{
					sign_bt1 = data.stat1;
				}
				$("#stat1").html(sign_bt1);
				$("#gubun1").text(data.gubun1);
				$("#sign_date1").text(data.sign_date1);
			if(data.stat1 != "진행중"){//서명완료 시 웹훅 미 도착 시
				alert("서명이 완료 된 계약 입니다.");
				loading_onoff('off');
				rent_efrom_hist_open()//급여제공기록 관리 팝업 리로드
			}else{
				loading_onoff('off');
				$('body').addClass('modal-open');
				$('#popup_box8').show();
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  loading_onoff('off');
		  alert(data && data.message);		  
		});	
		
		loading_onoff('off');
	}

	// 거절 사유 보기
	function open_rejection_view(rh_id,doc_id) {
		$("#doc_id").val(doc_id);
		$("#rejection_date").text('');
		$("#rejection_msg").text('');
		$("#rejection_member").text('');
		
 		$.ajaxSetup({
			async:true,
			beforeSend: loading_onoff('on')
			});
		$.post('ajax.eform_rent_mds_api.php', {
			doc_id:doc_id,
			div:'rejection_view'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				loading_onoff('off');
				return false;				
			}
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
		
		$.post('ajax.eform_rent_mds_api.php', {
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
				var PopupDoc = window.open('', "PopupDoc", "width=1200,height=900");
				PopupDoc.location = data.url;
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
	function mds_download(doc_id) {//1:계약서
 		
		$.post('ajax.eform_rent_mds_api.php', {
			doc_id:doc_id,
			div:'view_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				loading_onoff('off');
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.1");
				return false;				
			}
			if(data.url != "url생성실패"){				
				loading_onoff('off');
				var PopupDoc = window.open('', "PopupDoc", "width=1000,height=1000");
				PopupDoc.location = data.url;
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
	function sign_cancel(){
		var params = {
				div : "sign_cancel"
				,doc_id : $("#doc_id").val()
			};
		if(confirm("서명요청을 취소 하시겠습니까?")){			
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_rent_mds_api.php",      
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
						//history.replaceState({}, null, location.pathname);
						$('body').removeClass('modal-open');
						$('#popup_box8').hide();
						//location.reload();
						rent_efrom_hist_open()//급여제공기록 관리 팝업 리로드		
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

	// 거절 계약서 삭제
	function dc_del(){
		var params = {
				div : "dc_del"
				,doc_id : $("#doc_id").val()
			};
		if(confirm("급여제공기록지를 삭제하시겠습니까?")){
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_rent_mds_api.php",      
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
						alert("급여제공기록지가 삭제되었습니다.");
						//history.replaceState({}, null, location.pathname);
						$('body').removeClass('modal-open');
						$('#popup_box9').hide();
						//location.reload();
						rent_efrom_hist_open()//급여제공기록 관리 팝업 리로드	
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
		if($("#gubun1").text() != "카카오톡"){
			alert("서명 방식이 '카카오톡'일 경우만 서명요청 재전송이 가능합니다.");
			return false;
		}
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
				url : "ajax.eform_rent_mds_api.php",      
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
			/*
			$.ajaxSetup({
				async:true,
				beforeSend: loading_onoff('on')
			});
			$.ajax({
				type : "POST",            
				url : "ajax.eform_rent_mds_api.php",      
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
			*/
		}
	}

</script>