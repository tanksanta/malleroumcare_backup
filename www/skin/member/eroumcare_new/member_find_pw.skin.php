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
  /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
  /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
  /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
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
  /* // 파일명 :  \www\skin\member\eroumcare_new\member_find_pw.skin.php */
  /* // 파일 설명 : 신규파일 - 비밀번호찾기(스킨파일) */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">

<style>
	#captcha #captcha_img { float: left; }
	#captcha #captcha_key { width: 40%; }
</style>

    <!--  로그인 페이지 -->
    <div class="thkc_loginWrap">
    </div>
    <div class="thkc_idfindMeWrap">
    <section class="thkc_findMeWrap thkc_container_03">
        <h2 class="thkc_titleTop">비밀번호 찾기</h2>
        <!-- title 아이디찾기-->
        <div class="tabTitleWrap">
            <div class="tabTitle">
                <div class="active">
                    <a href="#"><span>휴대전화로 찾기</span></a>
                </div>
                <div>
                    <a href="#"><span>이메일로 찾기</span></a>
                </div>
            </div>            
        </div>
        <!-- tap Wrap -->
        <div class="thkc_tableTapWrap">
            <!--Tap1 패스와드찾기 [휴대폰 찾기] -->
            <div class="thkc_tableWrap modeSMS">
                <div class="tableTxt01">아이디와 사업자등록번호, 휴대전화번호를 입력하세요.</div>
                <div class="table-box m30">
                    <div class="tit">아이디</div>
                    <div class="thkc_cont">
                        <div>
                            <label for="sms_id" class="thkc_blind">아이디</label>
                            <input class="thkc_input _error_input_inner" id="sms_id" placeholder="아이디" value="" type="text" autocomplete="off" />								
                        </div>
                        <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                    </div>
                </div>
                <div class="table-box">
                    <div class="tit">사업자등록번호</div>
                    <div class="thkc_cont">
                        <div>
                            <label for="sms_bnum" class="thkc_blind">사업자등록번호</label>
                            <input
                                class="thkc_input _error_input_inner numOnly" id="sms_bnum" placeholder="사업자등록번호" value="" maxlength="10" type="text" autocomplete="off" />
								<div class="error-txt error_sms_bnum"></div>
                        </div>
                        <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                    </div>
                </div>            
                
                <div class="table-box tel-num">
                    <div class="tit">휴대전화번호</div>
                    <div class="thkc_cont">
                        <div class="">
                            <div class="flex-box">
                                <label for="hp1" class="thkc_blind">휴대전화번호</label>
                                <select class="thkc_input idpwInput" id="hp1">
                                    <option value="010">010</option>
                                    <option value="011">011</option>
                                    <option value="016">016</option>
                                    <option value="017">017</option>
                                    <option value="018">018</option>
                                    <option value="019">019</option>
                                </select>
                                <input class="thkc_input idpwInput numOnly" id="hp2" maxlength="4" value="" type="text" autocomplete="off" />
                                <input class="thkc_input idpwInput numOnly" id="hp3" maxlength="4" value="" type="text" autocomplete="off" />
                                <div class="_flex-box thkc_ml_02">                        
                                    <a href="#none" class="thkc_btn_bbs" onclick="SendSMS('new')" id="">인증번호 발송</a>                          
                                </div>
                            </div>                        
                            
                        </div>
                        <div class="error-txt error"></div>                   
                    </div>
                </div>
                
                <div class="table-box">
					<div class="cert_num" style="display: none;">

						<div class="tit">인증번호입력</div>
						<div class="thkc_cont">                       
							<div class="flex-box">
								<label for="cert" class="thkc_blind">인증번호입력</label>
								<input class="thkc_input idpwInput numOnly"  id="cert" placeholder="인증번호 입력" value="" type="text" autocomplete="off" />
								<input type="hidden" id="cert_tmp_num" name="cert_tmp_num" value="">
								<input type="hidden" id="timeout" name="timeout" value="">
								<div class="flex-box thkc_ml_02">                        
									<a href="#none" class="thkc_btn_bbs" onclick="SendSMSck( $('#cert').val() )" id="">인증번호 확인</a>                          
								</div>
							</div>                         
							<div class="error-txt error time">남은시간 : 0분 0초</div>                 
						</div>

						<div class="find_pw" style="display: none;"><br>
							<p class="tableTxt01"> 신규 비밀번호를 입력하세요.</p>
							<div class="table-boxPwWrap">
								<div class="table-boxPw">
									<div class="tit">신규비밀번호</div>
									<div class="thkc_cont">
										<div>
											<label for="password" class="thkc_blind">신규비밀번호</label>
											<input class="thkc_input" id="password" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value=""  maxlength="12" type="password" autocomplete="off" />
										</div>
										<div class="error-txt error"></div>
									</div>
								</div>
								<div class="table-boxPw">
									<div class="tit">비밀번호확인</div>
									<div class="thkc_cont">
										<div>
											<label for="password2" class="thkc_blind">비밀번호확인</label>
											<input class="thkc_input"  id="password2" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value=""  maxlength="12" type="password" autocomplete="off" />
										</div>
									</div>
								</div>
								<div class="thkc_btn_bbsPw">                        
									<a href="#none" class="thkc_btn_bbs change" onclick="SendSMS_resetPW()" id="">비밀번호 변경</a>                          
								</div>
							</div>
						</div>
						
					</div>
                </div>
            </div>

            <!-- Tap2 패스워드 찾기 [이메일로 찾기] -->
            <div class="thkc_tableWrap modeMAIL">
                <div class="tableTxt01">아이디와 사업자등록번호, 이메일 주소를 입력하세요.</div>
                <div class="table-box m30">
                    <div class="tit">아이디</div>
                    <div class="thkc_cont">
                        <div>
                            <label for="mail_id" class="thkc_blind">아이디</label>
                            <input class="thkc_input _error_input_inner" id="mail_id" placeholder="아이디" value="" type="text" autocomplete="off" />
                        </div>
                        <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                    </div>
                </div>
                
                <div class="table-box">
                    <div class="tit">사업자등록번호</div>
                    <div class="thkc_cont">
                        <div>
                            <label for="mail_bnum" class="thkc_blind">사업자등록번호</label>
                            <input class="thkc_input _error_input_inner numOnly" id="mail_bnum" placeholder="사업자등록번호" value="" maxlength="10" type="text" autocomplete="off" />
							<div class="error-txt error_mail_bnum"></div>
                        </div>
                        <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                    </div>
                </div>           
                
                <div class="table-box">
                    <div class="tit">이메일주소</div>
                    <div class="thkc_cont">                       
                            <div class="flex-box">
                                <label for="email" class="thkc_blind">이메일주소</label>
                                <input class="thkc_input idpwInput"  id="email" placeholder="" value="" type="text" autocomplete="off" />
                                <div class="flex-box thkc_ml_02">                        
                                    <a href="#none" class="thkc_btn_bbs" onclick="SendMAIL()" id="">비밀번호 발송</a>                          
                                </div>
                            </div>                         
                        <div class="error-txt error"></div>                   
                    </div>
                </div>
				<br />
                <div class="table-box secureTxt">
					<?php echo captcha_html(); ?>
                </div>               
            </div>
        </div>
        <div class="thkc_btnWrap">
            <a href="<?=G5_BBS_URL?>/login.php"><button class="btn_submit_01">로그인으로 이동 </button></a><br>
            <a href="<?=G5_URL?>" class="text_under">메인으로</a>         
        </div>
        <div class="tableTxt01">
            휴대폰번호가 변경되었거나 직접 찾기가 힘든 경우<br>고객센터(02-861-9084)로 문의 바랍니다.
        </div>

        
    </section>
    </div>     

	<script>
		// 숫자만 입력!!
		$('.numOnly').on('keyup blur', function() {
			var num = $(this).val();
			num.trim();
			this.value = only_num(num) ;
		});

        // 사업자번호 입력 유효성 체크
        $('#sms_bnum, #mail_bnum').on('keyup blur', function() {
            if( $(this).val().length == 10 ) {
                var _ck = checkCorporateRegiNumber( $(this).val() );
                if( !_ck ){
                    $('.error_' + $(this).attr('id') ).html("사업자등록번호를 정확하게 입력해주세요.");
                    $('.error_' + $(this).attr('id') ).css( "color", "#d44747" );
                } else  {
                    $('.error_' + $(this).attr('id') ).html("");
                }

                /* 23.05.23 - 사업자번호 하이픈 추가 */
                $(this).val( auto_saup_hypen( $(this).val() ) );
            }

        });

		// 비밀번호 찾기 - 메일발송
		function SendMAIL(){

			if( !$(".modeMAIL #mail_id").val() ) {
				alert( "아이디를 입력하세요." ); return;
			} else if( !$(".modeMAIL #mail_bnum").val() ) {
				alert( "사업자등록번호를 입력하세요." ); return;
			} else if( !$(".modeMAIL #email").val() ) {
				alert( "메일주소를 입력하세요." ); return;
			}

			<?php echo chk_captcha_js();  ?>

			$.ajax({
				url: '<?=G5_BBS_URL?>/ajax.member_findInfo.php', type: 'POST', dataType: 'json',
				data: {
					mode:"findPW_MAIL",
					id:$(".modeMAIL #mail_id").val(),
					bnum:$(".modeMAIL #mail_bnum").val(),
					mail: $(".modeMAIL #email").val()
				},
				success: function(data) {
					if( data.YN === "Y" ) {					
						$(".modeMAIL #mailid").attr("disabled", true);
						$(".modeMAIL #mail_bnum").attr("disabled", true);
						$(".modeMAIL #email").attr("disabled", true);

						$(".modeMAIL .thkc_btn_bbs").hide();
						$(".modeMAIL #captcha").hide();

						alert( "비밀번호 찾기 메일을 전송하였습니다.\n 메일함을 확인해 주세요." ); return; 
					} else {
						alert(data.YN_msg);
						return;
					}
				},
				error: function(e) {}
			});
			
		}


		// 비밀번호 찾기 - 인증 번호 발송
		let intervalId ="";
		function SendSMS(mod){

			if( !$(".modeSMS #sms_id").val() ) {
				alert( "아이디를 입력하세요." ); return;
			} else if( !$(".modeSMS #sms_bnum").val() ) {
				alert( "사업자등록번호를 입력하세요." ); return;
			} else if( (!$(".modeSMS #hp2").val()) || (!$(".modeSMS #hp3").val()) ) {
				alert( "휴대폰번호를 입력하세요." ); return;
			}

			if( mod === "re" ) {
				var _time = $(".modeSMS #timeout").val();
				if( _time>150 ) { alert( "요청 후 30초가 지나야 재요청이 가능합니다." ); return; }
				clearInterval(intervalId);
			}

			$.ajax({
				url: '<?=G5_BBS_URL?>/ajax.member_findInfo.php', type: 'POST', dataType: 'json',
				data: {
					mode:"findPW_SMS",
					id:$(".modeSMS #sms_id").val(),
					bnum:$(".modeSMS #sms_bnum").val(),
					hp: $(".modeSMS #hp1").val() + "-" + $(".modeSMS #hp2").val() + "-" + $(".modeSMS #hp3").val()
				},
				success: function(data) {

					if( data.YN === "Y" ) { 
						$(".modeSMS #sms_id").attr("disabled", true);
						$(".modeSMS #sms_bnum").attr("disabled", true);
						$(".modeSMS #hp1, .modeSMS #hp2, .modeSMS #hp3").attr("disabled", true);

						$(".modeSMS .tel-num .thkc_btn_bbs").text("인증번호 재요청");
						$(".modeSMS .tel-num .thkc_btn_bbs").css("font-size","12px");
						$(".modeSMS .tel-num .thkc_btn_bbs").attr("onclick", "SendSMS('re')");

						let count = 180;
						$(".modeSMS .time").html( calcTime(count) );
						$(".modeSMS #timeout").val( parseInt(count) );

						intervalId = setInterval(function() { count--;
							if( count>0 ) { $(".modeSMS .time").html( calcTime(count) ); } else { $(".modeSMS .time").html( "" ); }
							if( (count <= 0) ) { clearInterval(intervalId); }
							$(".modeSMS #timeout").val( parseInt(count) );
						}, 1000)

						$(".modeSMS .cert_num").show();
						$(".modeSMS #cert_tmp_num").val(data.CertNum);

						alert( "인증번호를 전송하였습니다.\n 인증번호는 3분간 유효합니다." ); return; 
					} else {
						alert(data.YN_msg);
						return;
					}

				},
				error: function(e) {}
			});
		}
		





		// 비밀번호 찾기 - 인증번호 체크
		function SendSMSck(cert){
			if( !cert  ) { 
				alert( "SMS로 전송된 인증문자 4자리를 입력하세요." ); return; 
			} else if( cert != $(".modeSMS #cert_tmp_num").val() ) { 
				alert( "입력된 인증번호가 일치하지 않습니다." ); return; 
			}

			clearInterval(intervalId);
			$(".modeSMS .time").html("");
			$(".modeSMS .thkc_btn_bbs").hide();
			$(".modeSMS #cert").attr("disabled", true);

			$(".modeSMS .find_pw").show();
			$(".modeSMS .change").show();

		}





		// 비밀번호 찾기 - 인증번호 체크
		function SendSMS_resetPW() {
			
			if( !$(".modeSMS #password").val() ) {
				alert( "신규 비밀번호를 입력하세요." ); return;
			} else if( !$(".modeSMS #password2").val() ) {
				alert( "비밀번호 확인을 입력하세요." ); return;
			} 

			if( check_pw($(".modeSMS #password").val()) ) {
				alert( check_pw($(".modeSMS #password").val()) ); return;
			}

			if( $(".modeSMS #password").val() != $(".modeSMS #password2").val() ) {				
				alert( "비밀번호가 일치하지 않습니다. 다시 입력 해주세요." );
				
				$(".modeSMS #password, .modeSMS #password2").val("");
				$(".modeSMS #password").focus();

				return;
			}

			$.ajax({
				url: '<?=G5_BBS_URL?>/ajax.member_findInfo.php', type: 'POST', dataType: 'json',
				data: {
					mode:"findPW_PWRESET",
					id:$(".modeSMS #sms_id").val(),
					bnum:$(".modeSMS #sms_bnum").val(),
					hp: $(".modeSMS #hp1").val() + "-" + $(".modeSMS #hp2").val() + "-" + $(".modeSMS #hp3").val(),
					pw1:$(".modeSMS #password").val(),
					pw2:$(".modeSMS #password2").val()
				},
				success: function(data) {
					if( data.YN === "Y" ) { 						
						alert("비밀번호가 정상적으로 변경되었습니다.\n로그인페이지로 이동합니다.");
						location.href = "<?=G5_BBS_URL;?>/login.php";
					} else {
						alert(data.YN_msg);
						//location.reload();
					}

				},
				error: function(e) {}
			});
		}


        // 비밀번호 유효성 검증
        function check_pw(pw) {
            var pw = pw;
            var num = pw.search(/[0-9]/g);
            var eng = pw.search(/[a-z]/ig);

            if(pw.length < 8 || pw.length > 12) {
                return "8자리 ~ 12자리 이내로 입력해주세요.";
            } else if(pw.search(/\s/) != -1) {
                return "비밀번호는 공백 없이 입력해주세요.";
            } else if(num < 0 || eng < 0 ) {
                return "영문,숫자를 혼합하여 입력해주세요.";
            } else {
                return false;
            }
        }


		// 남은시간 출력 계산 부분
		function calcTime(time, type='') {    
			let setHour;    
			let setMin = 0;    
			let setSec = 0;    
			let setText = "";    
			let setArray = []    
			time = parseInt(time);    
			const cutMin = Math.floor(time / 60);    

			if (cutMin >= 60) {        
				setHour = Math.floor(cutMin / 60);        
				setMin = cutMin % 60;    
			} else {        
				setMin = 0;        
				setMin = cutMin;    
			}    

			setSec = time % 60;    
			if (typeof setHour !== "undefined") {        
				if (setHour < 10) {            
					setText = "0" + setHour + "시간 ";        
				} else {            
					setText = setHour + "시간 ";        
				}    
			} else {        
				setText = "";    
			}    

			if (setMin < 10) {        
				setText += "0" + setMin + "분 ";    
			} else {        
				setText += setMin + "분 ";    
			}    

			if (setSec < 10) {        
				setText += "0" + setSec + "초 ";      
			} else {        
				setText += setSec + "초 ";     
			}    

			setArray.hour = setHour;    
			setArray.minute = setMin;    
			setArray.second = setSec;    

			if(type === '') {        
				return "남은시간 : " + setText;    
			} else {        
				return setArray;    
			}
		}
	</script>