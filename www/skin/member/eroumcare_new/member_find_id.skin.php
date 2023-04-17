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
  /* // 파일명 :  \www\skin\member\eroumcare_new\member_find_id.skin.php */
  /* // 파일 설명 : 신규파일 - 아이디찾기(스킨파일) */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">

    <!--  로그인 페이지 -->
    <div class="thkc_loginWrap">
    </div>
    <div class="thkc_idfindMeWrap">
        <section class="thkc_findMeWrap thkc_container_03">
            <h2 class="thkc_titleTop">아이디찾기</h2>
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
                <!--Tap1 아이디찾기 [휴대폰 찾기] -->
                <div class="thkc_tableWrap modeSMS">
                    <div class="tableTxt01">사업자 등록번호와 등록된 휴대전화번호를 입력하세요.</div>
                    <div class="table-box m30">
                        <div class="tit">사업자등록번호</div>
                        <div class="thkc_cont">
                            <div>
                                <label for="bnum" class="thkc_blind">사업자등록번호</label>
                                <input class="thkc_input _error_input_inner numOnly" id="bnum" maxlength="10" placeholder="사업자등록번호" value="" type="text" autocomplete="off" />
								<?php
									// 23.03.31 : 서원 - 주석용
									// 				기존 사업자등록번호가 '-'(하이픈)이 입력된 상태 인데... 기획서에는 숫자만 입력 받으라고 되어있음.
									//				기획서 대로 진행하며, 추후 문제 발생할 경우 입력 필드 조건을 바꿔야 하거나, 기존 입력된 사업자번호 컨버전 필요!! ( 단, 이카운트와 연동 및 다른쪽 사업자번호로 연동되는 부분 문제성 확인 필요. )
								?>
                            </div>
                            <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                        </div>
                    </div>

                    <div class="table-box tel-num">
                        <div class="tit">휴대전화번호
                        </div>
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
                                    <input class="thkc_input idpwInput numOnly" id="hp2" name="" maxlength="4" value="" type="text" autocomplete="off"  />
                                    <input class="thkc_input idpwInput numOnly" id="hp3" name="" maxlength="4" value="" type="text" autocomplete="off"  />
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
									<input class="thkc_input idpwInput numOnly" id="cert" placeholder="인증번호 입력" maxlength="4" value="" type="text" autocomplete="off"  />									
    								<input type="hidden" id="cert_tmp_num" name="cert_tmp_num" value="">
									<input type="hidden" id="timeout" name="timeout" value="">
									<div class="flex-box thkc_ml_02">
										<a href="#none" class="thkc_btn_bbs" onclick="SendSMSck( $('#cert').val() )" id="" >인증번호 확인</a>
									</div>
								</div>
								<div class="error-txt error time">남은시간 : 0분 0초</div>
							</div>
						</div>

                        <br>

						<div class="find_id" style="display: none;">
							<div class="thkc_joinWrap flex-box">
								<p class="tableTxt01">위 정보에 일치하는 계정은 아래와 같습니다.</p>
								<span class="thkc_newID">아이디</span>
							</div>
						</div>

                    </div>
                </div>

                <!-- Tap2 아이디찾기 [이메일 찾기] -->
                <div class="thkc_tableWrap modeMAIL">
                    <div class="tableTxt01">사업자 등록번호와 등록된 이메일주소를 입력하세요.</div>
                    <div class="table-box m30">
                        <div class="tit">사업자등록번호 </div>
                        <div class="thkc_cont">
                            <div>
                                <label for="bnum" class="thkc_blind">사업자등록번호</label>
                                <input class="thkc_input _error_input_inner numOnly" id="bnum" maxlength="10" placeholder="사업자등록번호" value="" type="text" autocomplete="off"  />
                            </div>
                            <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                        </div>
                    </div>

                    <div class="table-box">
                        <div class="tit">이메일주소
                        </div>
                        <div class="thkc_cont">
                            <div class="flex-box">
                                <label for="email" class="thkc_blind">이메일주소</label>
                                <input class="thkc_input idpwInput" id="email" placeholder="" value="" type="text" autocomplete="off"  />
                                <div class="flex-box thkc_ml_02">
                                    <a href="#none" class="thkc_btn_bbs" onclick="SendMAIL()" id="">확인 요청</a>
                                </div>
                            </div>
                            <div class="error-txt error"></div>
                        </div>
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
		$('.numOnly').on('keyup', function() {
			var num = $(this).val();
			num.trim();
			this.value = only_num(num) ;
		});


    // 아이디 찾기 - 인증 번호 발송
	let intervalId ="";
    function SendSMS(mod){

		if( !$(".modeSMS #bnum").val() ) {
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
                mode:"findID_SMS",
				bnum:$(".modeSMS #bnum").val(),
				hp: $(".modeSMS #hp1").val() + "-" + $(".modeSMS #hp2").val() + "-" + $(".modeSMS #hp3").val()
            },
            success: function(data) {

                if( data.YN === "Y" ) { 
					$(".modeSMS #bnum").attr("disabled", true);
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
					$(".modeSMS .thkc_newID").html(data.FindID);

					alert( "인증번호를 전송하였습니다.\n 인증번호는 3분간 유효합니다." ); return; 
                } else {
					alert(data.YN_msg);
					return;
				}

            },
            error: function(e) {}
        });
    }
	
	// 아이디 찾기 - 인증번호 체크
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

		$(".modeSMS .find_id").show();
	}

	// 아이디 찾기 - 메일발송
	function SendMAIL(){
		if( !$(".modeMAIL #bnum").val() ) {
			alert( "사업자등록번호를 입력하세요." ); return;
		} else if( !$(".modeMAIL #email").val() ) {
			alert( "메일주소를 입력하세요." ); return;
		}

        $.ajax({
            url: '<?=G5_BBS_URL?>/ajax.member_findInfo.php', type: 'POST', dataType: 'json',
            data: {
                mode:"findID_MAIL",
				bnum:$(".modeMAIL #bnum").val(),
				mail: $(".modeMAIL #email").val()
            },
            success: function(data) {
				if( data.YN === "Y" ) {					
					$(".modeMAIL #bnum").attr("disabled", true);
					$(".modeMAIL #email").attr("disabled", true);
					$(".modeMAIL .thkc_btn_bbs").hide();
					alert( "아이디 정보를 이메일로 전송하였습니다.\n 메일함을 확인해 주세요." ); return; 
				} else {
					alert(data.YN_msg);
					return;
				}
			},
            error: function(e) {}
        });

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