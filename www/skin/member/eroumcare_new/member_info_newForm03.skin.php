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
    /* // 파일명 :   */
    /* // 파일 설명 :  */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

?>

            <link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">
            <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
            <style type="text/css">
                .thkc_popUpWrap .thkc_popWrap .thkc_joinWrap #daum_juso_pageaddr_zip {
                    width: 100%;
                    max-height: 280px;
                }
            </style>

            <input type="hidden" id="mbno" name="" value="<?=$member['mb_no'];?>">
            <input type="hidden" id="mbid" name="" value="<?=$member['mb_id'];?>">
            <input type="hidden" id="mode" name="" value="<?=$_GET['STEP'];?>">


            <section class="thkc_section">
                <!-- 팝업 오버뷰 -->
                <div class="thkc_popOverlay"></div>
                <!-- 회원정보수정 (메뉴) -->
                <div class="thkc_memberModifyWrap">
                    <h3>회원 정보 수정</h3>
                    <ul>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop01"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify01.svg" alt=""><p>사업자 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop02"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify02.svg" alt=""><p>계정 관리</p></a></li>
                        <li><a href="javascript:void(0);" class="active"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify03.svg" alt=""><p>배송지 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop04"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify04.svg" alt=""><p>서비스 정보</p></a></li>
                        <li><a href="#"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify05.svg" alt=""><p>환경 설정</p></a></li>
                    </ul>
                </div>
                <!-- 회원정보 계정정보 -->
                <div class="thkc_joinWrap">
                    <!-- title 배송지 정보-->
                    <div class="joinTitle"><div class="boxLeft">기본배송지 정보</div></div>
                    <!-- table 배송지정보 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box table-box_02"><div class="tit tit02">배송지명</div><div class="thkc_cont thkc_cont02"><div><?=$member['mb_addr_title']?$member['mb_addr_title']:"기본배송지"?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">배송지 주소</div><div class="thkc_cont thkc_cont02"><div>(<?=$member['mb_zip1']?><?=$member['mb_zip2']?>) <?=$member['mb_addr1']?> <?=$member['mb_addr2']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">수령인</div><div class="thkc_cont thkc_cont02"><div><?=$member['mb_addr_name']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">연락처</div><div class="thkc_cont thkc_cont02"><div><?=$member['mb_addr_tel']?></div></div></div>
                    </div>
                    <!-- 회원정보 계정정보 end -->
                </div>
                <br><br>

                
                <?php
                    if( $member['mb_addr_more'] ) {
                        
                        $addr_more = json_decode($member['mb_addr_more'],TRUE); $i=0;
                        if( $addr_more != NULL ) {
                        foreach($addr_more as $key => $val) { $i++;
                ?>
                <!-- 회원정보 추가 배송지 1-->
                <div class="thkc_joinWrap addrMore_<?=$i?>">
                    <!-- title 추가 배송지-->
                    <div class="joinTitle"><div class="boxLeft">추가 배송지 <?=$i?></div></div>
                    <!-- table 추가 배송지 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box table-box_02"><div class="tit tit02">배송지명</div><div class="thkc_cont thkc_cont02"><div><?=$val['addr_title']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">배송지 주소</div><div class="thkc_cont thkc_cont02"><div>(<?=$val['addr_zip']?>) <?=$val['addr_1']?><?=$val['addr_2']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">수령인</div><div class="thkc_cont thkc_cont02"><div><?=$val['addr_name']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">연락처</div><div class="thkc_cont thkc_cont02"><div><?=$val['addr_tel']?></div></div></div>
                        <div class="thkc_btnWrap_03">
                            <button onclick="addr_more_default('<?=$key?>','<?=$i?>')">기본배송지로 설정</button>
                            <button onclick="addr_more_del('<?=$key?>','<?=$i?>')">삭제</button>
                            <button class="on" onclick="addr_more_modify('<?=$key?>','<?=$i?>')">정보수정</button>
                        </div>
                    </div>
                    
                    <input type="hidden" id="input_addr_title" name="" value="<?=$val['addr_title']?>">
                    <input type="hidden" id="input_addr_name" name="" value="<?=$val['addr_name']?>">
                    <input type="hidden" id="input_addr_tel" name="" value="<?=$val['addr_tel']?>">
                    <input type="hidden" id="input_addr_zip" name="" value="<?=$val['addr_zip']?>">
                    <input type="hidden" id="input_addr_1" name="" value="<?=$val['addr_1']?>">
                    <input type="hidden" id="input_addr_2" name="" value="<?=$val['addr_2']?>">
                    <!-- 회원정보 계정정보 end -->
                </div>
                <?php } } } ?>


                <!-- 버튼 -->
                <div class="thkc_btnWrap thkc_mtb_01">
                    <button class="btn_submit_02">배송지 신규등록 +</button><br>
                </div>


                <!-- 배송지신규 등록 팝업 -->
                <div class="thkc_popUpWrap">
                    <div class="thkc_popWrap">
                        <div class="thkc_close">
                            <i class="fa-solid fa-xmark"></i>
                        </div>

                        <div class="thkc_joinWrap">
                            <div class="joinTitle">
                                <div class="boxLeft">배송지 신규 등록</div>
                            </div>
                            <!-- table 계정정보 -->
                            <div class="thkc_tableWrap thkc_bbs-more">

                                <form class="form-horizontal register-form" role="form" id="addr_info" name="addr_info" onsubmit="return false">

                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">배송지명</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <label for="id" class="thkc_blind">배송지명</label> <input s class="thkc_input" id="addr_title" placeholder="배송지이름 입력" value="" type="text" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">수령인</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <label for="id" class="thkc_blind">수령인</label> <input s class="thkc_input" id="addr_name" placeholder="수령인이름 입력" value="" type="text" autocomplete="off" />
                                    </div>
                                </div>

                                <div class="table-box table-box_02 tel-num">
                                    <div class="tit03 bbs-pd_01">연락처</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="flex-box">
                                            <div class="flex-box">
                                                <label for="tell" class="thkc_blind">연락처</label>
                                                <input class="thkc_input numOnly" placeholder="010" id="addr_tel1" name="" maxlength="3" value="" type="text" autocomplete="off" />&nbsp;-
                                                <input class="thkc_input numOnly" placeholder="0001" id="addr_tel2" name="" maxlength="4" value="" type="text" autocomplete="off" />&nbsp;-                                                
                                                <input class="thkc_input numOnly" placeholder="0002" id="addr_tel3" name="" maxlength="4" value="" type="text" autocomplete="off" />
                                            </div>
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <!-- 배송지 주소  -->
                                <div class="table-box table-box_02 address ">
                                    <div class="tit03 bbs-pd_01">배송지 주소</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="thkc_dfc_m">
                                            <div class="flex-box thkc_dfc">
                                                <label for="zip" class="thkc_blind">주소 찾기</label>
                                                <input class="thkc_input" id="addr_zip" name="addr_zip" placeholder="22850" readonly="readonly" maxlength="14" value="" type="text" autocomplete="off" />
                                                <a href="javascript:void(0);" class="thkc_btn_bbs win_zip_find" onclick="win_zip2('addr_info', 'addr_zip', 'addr1', 'addr2', 'addr3', 'jibeon'); $('#daum_juso_pageaddr_zip').css('width','100%');" id="">주소 찾기</a>
                                            </div>
                                        </div>
                                        <div title="기본주소">
                                            <label for="addr1" class="thkc_blind">기본주소</label>
                                            <input class="thkc_input" id="addr1" name="addr1" placeholder="경기도 화성시 동탄영천로192-39길" readonly="readonly" value="" type="text" autocomplete="off" />
                                        </div>
                                        <div title="나머지주소">
                                            <label for="addr2" class="thkc_blind">나머지주소</label>
                                            <input class="thkc_input" id="addr2" name="addr2" placeholder="3층" value="" type="text" autocomplete="off" />
                                        </div>
                                    </div>
                                </div>
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">기본주소지</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <input id="default" value="Y" type="checkbox" />
                                        <span>기본주소지를 등록 된 주소로 변경합니다.</span>
                                    </div>
                                </div>
                                <div class="thkc_btnWrap_03">
                                    <button class="cancel">취소</button>
                                    <button class="on">등록하기</button>
                                </div>
                                
                                <input type="hidden" id="addr3" name="addr3" value="" autocomplete="off" />                                
                                <input type="hidden" id="jibeon" name="jibeon" value="" autocomplete="off" />

                            </form>

                            </div>
                        </div>
                    </div>
                </div>

            </section>



            <script>
                // 숫자만 입력!!
                $('.numOnly').on('keyup', function() {
                    var num = $(this).val();
                    num.trim();
                    this.value = only_num(num) ;
                });

                $(".thkc_btnWrap .btn_submit_02").click(function () {
                    $("#addr_info")[0].reset();
                    $("#daum_juso_pageaddr_zip").hide();

                    $(".thkc_popUpWrap .boxLeft").text("배송지 신규 등록");
                    $('.thkc_popUpWrap .thkc_btnWrap_03 .on').text("등록하기"); 
                    $(".thkc_popUpWrap .thkc_btnWrap_03 .on").attr("onclick", "addr_more_add()");
					$(".thkc_popUpWrap").css("display", "flex").hide().fadeIn();
                    $(".thkc_popOverlay").show();
                });

                
                $('.thkc_joinWrap .thkc_tableWrap .thkc_btnWrap_03 .cancel').click(function () {
                    $('.close_daum_juso').trigger('click');
					$(".thkc_popUpWrap").hide();
                    $(".thkc_popOverlay").hide();
                    document.body.classList.remove("stop-scroll");
                });
                

                function addr_more_add() {
					$('.close_daum_juso').trigger('click');
                    if(!confirm("신규 배송지를 등록 하시겠습니까?")) { return; }

                    if( !$(".thkc_popUpWrap #addr_tel1").val() || !$(".thkc_popUpWrap #addr_tel2").val() || !$(".thkc_popUpWrap #addr_tel3").val() ) {
                        alert('연락처를 입력하세요..'); return false;
                    }

                    var _default = "N";
                    if( $('input:checkbox[id="default"]').is(':checked') ) {
                        _default = $('input:checkbox[id="default"]').val();
                    }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "mbset": "add",

                            "addr_title" : $(".thkc_popUpWrap #addr_title").val(),
                            "addr_name" : $(".thkc_popUpWrap #addr_name").val(),
                            "addr_tel" : $(".thkc_popUpWrap #addr_tel1").val() + "-" + $(".thkc_popUpWrap #addr_tel2").val() + "-" + $(".thkc_popUpWrap #addr_tel3").val(),
                            "addr_zip" : $(".thkc_popUpWrap #addr_zip").val(),
                            "addr1" : $(".thkc_popUpWrap #addr1").val(),
                            "addr2" : $(".thkc_popUpWrap #addr2").val(),
                            "addr_default" : _default

                        },
                        success: function(data) {
                            if( data.YN === "Y" ) {
                                alert('배송지 신규등록이 완료 되었습니다.');
                                window.location.reload();
                            } else { alert(data.YN_msg); }
                        },
                        error: function(e) {}
                    });


                }


                function addr_more_del( key, item ) {
                    if(!confirm("[ 배송지명 : " + $(".addrMore_"+item+" #input_addr_title").val() + " ]\n배송지 삭제 하시겠습니까?")) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "mbset": "del", "mbkey": key
                        },
                        success: function(data) {
                            if( data.YN === "Y" ) {
                                alert('배송지 삭제가 완료 되었습니다.');
                                window.location.reload();
                            } else { alert(data.YN_msg); }
                        },
                        error: function(e) {}
                    });

                }



                function addr_more_modify( key, item ) {
                    $("#addr_info")[0].reset();

                    $(".thkc_popUpWrap #addr_title").val( $(".addrMore_"+item+" #input_addr_title").val() );
                    $(".thkc_popUpWrap #addr_name").val( $(".addrMore_"+item+" #input_addr_name").val() );

                    var _tel = $(".addrMore_"+item+" #input_addr_tel").val();
                        _tel = _tel.split('-');

                    $(".thkc_popUpWrap #addr_tel1").val( _tel[0] );
                    $(".thkc_popUpWrap #addr_tel2").val( _tel[1] );
                    $(".thkc_popUpWrap #addr_tel3").val( _tel[2] );

                    $(".thkc_popUpWrap #addr_zip").val( $(".addrMore_"+item+" #input_addr_zip").val() );
                    $(".thkc_popUpWrap #addr1").val( $(".addrMore_"+item+" #input_addr_1").val() );
                    $(".thkc_popUpWrap #addr2").val( $(".addrMore_"+item+" #input_addr_2").val() );

                    $(".thkc_popUpWrap .boxLeft").text("배송지 정보수정");
                    $('.thkc_popUpWrap .thkc_btnWrap_03 .on').text("변경하기"); 
                    $(".thkc_popUpWrap .thkc_btnWrap_03 .on").attr("onclick", "confirm_modify('" + key + "')");

                    $(".thkc_popUpWrap").css("display", "flex").hide().fadeIn();
                    $(".thkc_popOverlay").show();
                }
                function confirm_modify( key ) {
					$('.close_daum_juso').trigger('click');
                    if(!confirm("배송지를 정보를 수정 하시겠습니까?")) { return; }

                    if( !$(".thkc_popUpWrap #addr_tel1").val() || !$(".thkc_popUpWrap #addr_tel2").val() || !$(".thkc_popUpWrap #addr_tel3").val() ) {
                        alert('연락처를 입력하세요..'); return false;
                    }

                    var _default = "N";
                    if( $('input:checkbox[id="default"]').is(':checked') ) {
                        _default = $('input:checkbox[id="default"]').val();
                    }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "mbset": "mod", "mbkey": key,

                            "addr_title" : $(".thkc_popUpWrap #addr_title").val(),
                            "addr_name" : $(".thkc_popUpWrap #addr_name").val(),
                            "addr_tel" : $(".thkc_popUpWrap #addr_tel1").val() + "-" + $(".thkc_popUpWrap #addr_tel2").val() + "-" + $(".thkc_popUpWrap #addr_tel3").val(),
                            "addr_zip" : $(".thkc_popUpWrap #addr_zip").val(),
                            "addr1" : $(".thkc_popUpWrap #addr1").val(),
                            "addr2" : $(".thkc_popUpWrap #addr2").val(),
                            "addr_default" : _default

                        },
                        success: function(data) {
                            if( data.YN === "Y" ) {
                                alert('배송지 정보변경이 완료 되었습니다.');
                                window.location.reload();
                            } else { alert(data.YN_msg); }
                        },
                        error: function(e) {}
                    });

                }



                function addr_more_default( key, item ) {
                    if(!confirm("[ 배송지명 : " + $(".addrMore_"+item+" #input_addr_title").val() + " ]\n기본배송지로 설정 하시겠습니까?")) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "mbset": "def", "mbkey": key
                        },

                        success: function(data) {
                            if( data.YN === "Y" ) {
                                alert('기본배송지 설정이 완료 되었습니다.');
                                window.location.reload();
                            } else { alert(data.YN_msg); }
                        },
                        error: function(e) {}
                    });
                }

				/**
				 * 우편번호 창
				 **/
				var win_zip2 = function (
				  frm_name,
				  frm_zip,
				  frm_addr1,
				  frm_addr2,
				  frm_addr3,
				  frm_jibeon
				) {
				  if(window.innerWidth > 589){
					win_zip('addr_info', 'addr_zip', 'addr1', 'addr2', 'addr3', 'jibeon'); $('#daum_juso_pageaddr_zip').css('width','100%');
					return false;
				  }
				  
				  if (typeof daum === 'undefined') {
					alert(aslang[20]); //다음 우편번호 postcode.v2.js 파일이 로드되지 않았습니다.
					return false;
				  }

				  var zip_case = 0; //0이면 레이어, 1이면 페이지에 끼워 넣기, 2이면 새창

				  var complete_fn = function (data) {
					// 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

					// 각 주소의 노출 규칙에 따라 주소를 조합한다.
					// 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
					var fullAddr = ''; // 최종 주소 변수
					var extraAddr = ''; // 조합형 주소 변수

					// 사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
					/*
						if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
							fullAddr = data.roadAddress;

						} else { // 사용자가 지번 주소를 선택했을 경우(J)
							fullAddr = data.jibunAddress;
						}
						*/

					// 무조건 도로명 선택
					data.userSelectedType = 'R';
					fullAddr = data.roadAddress;

					// 사용자가 선택한 주소가 도로명 타입일때 조합한다.
					if (data.userSelectedType === 'R') {
					  //법정동명이 있을 경우 추가한다.
					  if (data.bname !== '') {
						extraAddr += data.bname;
					  }
					  // 건물명이 있을 경우 추가한다.
					  if (data.buildingName !== '') {
						extraAddr +=
						  extraAddr !== '' ? ', ' + data.buildingName : data.buildingName;
					  }
					  // 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
					  extraAddr = extraAddr !== '' ? ' (' + extraAddr + ')' : '';
					}

					// 우편번호와 주소 정보를 해당 필드에 넣고, 커서를 상세주소 필드로 이동한다.
					var of = document[frm_name];

					of[frm_zip].value = data.zonecode;

					of[frm_addr1].value = fullAddr;
					//of[frm_addr3].value = extraAddr;
					of[frm_addr2].value = extraAddr;
					// of[frm_addr3].value = data.jibunAddress; // 지번주소를 3에 넣는다

					if (of[frm_jibeon] !== undefined) {
					  of[frm_jibeon].value = data.userSelectedType;
					}

					setTimeout(function () {
					  of[frm_addr2].focus();
					}, 100);
				  };				  
					  //iframe을 이용하여 레이어 띄우기
					  var rayer_id = 'daum_juso_rayer' + frm_zip,
						element_layer = document.getElementById(rayer_id);
					  if (element_layer == null) {
						element_layer = document.createElement('div');
						element_layer.setAttribute('id', rayer_id);
						element_layer.style.cssText =
						  'display:none;border:1px solid;position:fixed;width:300px;height:460px;left:50%;margin-left:-150px;top:50%;margin-top:-235px;overflow:hidden;-webkit-overflow-scrolling:touch;z-index:10000';
						element_layer.innerHTML =
						  '<img src="//i1.daumcdn.net/localimg/localimages/07/postcode/320/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" class="close_daum_juso" alt="닫기 버튼">';
						document.body.appendChild(element_layer);
						jQuery('#' + rayer_id)
						  .off('click', '.close_daum_juso')
						  .on('click', '.close_daum_juso', function (e) {
							e.preventDefault();
							jQuery(this).parent().hide();
						  });
					  }

					  new daum.Postcode({
						oncomplete: function (data) {
						  complete_fn(data);
						  // iframe을 넣은 element를 안보이게 한다.
						  element_layer.style.display = 'none';
						},
						maxSuggestItems: g5_is_mobile ? 6 : 10,
						width: '100%',
						height: '100%',
					  }).embed(element_layer);

					  // iframe을 넣은 element를 보이게 한다.
					  element_layer.style.display = 'block';
				};

            </script>
