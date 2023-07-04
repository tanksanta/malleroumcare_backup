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
    /* // 파일명 :  \www\skin\member\eroumcare_new\member_info_newForm02.skin.php */
    /* // 파일 설명 : 신규파일 - 회원정보 변경 > 직원계정관리 파일 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


    $mm_result = sql_query(" SELECT * FROM g5_member WHERE mb_type = 'manager' AND mb_manager = '{$member['mb_id']}'");


?>
            <link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">


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
                        <li><a href="javascript:void(0);" class="active"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify02.svg" alt=""><p>계정 관리</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop03"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify03.svg" alt=""><p>배송지 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop04"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify04.svg" alt=""><p>서비스 정보</p></a></li>
                        <li><a href="#"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify05.svg" alt=""><p>환경 설정</p></a></li>
                    </ul>
                </div>
                <!-- 회원정보 계정정보 -->
                <div class="thkc_joinWrap">
                    <!-- title 계정정보-->
                    <div class="joinTitle">
                        <div class="boxLeft">계정 정보</div>
                        <div class="thkc_btnWrap_03">
                            <button class="save" onclick="SAVE_MEMBER()">정보저장</button>
                        </div>
                    </div>
                    <!-- table 계정정보 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box  m30">
                            <div class="tit">아이디</div>
                            <div class="thkc_cont"><?=$member['mb_id']?></div>
                        </div>
                        <div class="table-box">
                            <div class="tit">비밀번호</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="password" class="thkc_blind">비밀번호</label>
                                    <input class="thkc_input" id="password" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value="" type="password" autocomplete="off" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">비밀번호 확인</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="password2" class="thkc_blind">비밀번호 확인</label>
                                    <input class="thkc_input" id="password2" placeholder="" value="" type="password" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">담당자명</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="name" class="thkc_blind">담당자명</label>
                                    <input class="thkc_input" id="name" placeholder="홍길동" value="<?=$member['mb_giup_manager_name']?>" type="text" autocomplete="off" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box tel-num">
                            <div class="tit">담당자 휴대전화</div><?php $mb_hp =explode('-',$member['mb_hp']); ?>
                            <div class="thkc_cont">
                                <div class="flex-box">
                                    <div class="flex-box">
                                        <label for="tell" class="thkc_blind">담당자 휴대전화</label>
                                        <select class="thkc_input" id="hp1">
                                            <option value="010"<?=($mb_hp[0]=="010")?" selected":"";?>>010</option>
                                            <option value="011"<?=($mb_hp[0]=="011")?" selected":"";?>>011</option>
                                            <option value="016"<?=($mb_hp[0]=="016")?" selected":"";?>>016</option>
                                            <option value="017"<?=($mb_hp[0]=="017")?" selected":"";?>>017</option>
                                            <option value="018"<?=($mb_hp[0]=="018")?" selected":"";?>>018</option>
                                            <option value="019"<?=($mb_hp[0]=="019")?" selected":"";?>>019</option>
                                        </select> &nbsp;-
                                        <input class="thkc_input numOnly" placeholder="1234" id="hp2" name="" maxlength="4" value="<?=$mb_hp[1]?>" type="text" autocomplete="off" /> &nbsp;-
                                        <input class="thkc_input numOnly" placeholder="5678" id="hp3" name="" maxlength="4" value="<?=$mb_hp[2]?>" type="text" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <!-- 이메일  -->
                        <div class="table-box">
                            <div class="tit">담당자 이메일
                            </div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc">
                                    <label for="email" class="thkc_blind">담당자 이메일</label>
                                    <input class="thkc_input" id="email" placeholder="hula2993@naver.com" value="<?=$member['mb_email']?>" type="text" autocomplete="off" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>

                    </div>
                    <!-- 회원정보 계정정보 end -->
                </div>

                <?php for( $i=1 ; $mm = sql_fetch_array($mm_result) ; $i++ ) { ?>
                <!-- 회원정보 직원계정 -->
                <div class="thkc_joinWrap manager_<?=$mm['mb_no']?>">
                    <div class="joinTitle"><div class="boxLeft">직원 계정<?=$i?></div></div>
                    <div class="thkc_tableWrap">
                        <div class="table-box table-box_02"><div class="tit tit02">아이디</div><div class="thkc_cont thkc_cont02"><div><?=$mm['mb_id']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">이름</div><div class="thkc_cont thkc_cont02"><div><?=$mm['mb_name']?></div></div></div>
                        <div class="table-box table-box_02"><div class="tit tit02">최근접속일</div><div class="thkc_cont thkc_cont02"><div><?=$mm['mb_today_login']?></div></div></div>
                        <div class="thkc_btnWrap_03"><button onclick="manager_del('<?=$mm['mb_no']?>')">삭제</button><button class="on" onclick="manager_modify('<?=$mm['mb_no']?>')">정보수정</button></div>
                    </div>

                    <input type="hidden" id="mb_id" name="" value="<?=$mm['mb_id']?>">
                    <input type="hidden" id="mb_name" name="" value="<?=$mm['mb_name']?>">
                    <input type="hidden" id="mm_tel" name="" value="<?=$mm['mb_tel']?>">
                    <input type="hidden" id="mm_email" name="" value="<?=$mm['mb_email']?>">
                    <input type="hidden" id="mm_memo" name="" value="<?=$mm['mb_memo']?>">

                </div>
                <?php } ?>

                <!-- 버튼 -->
                <div class="thkc_btnWrap thkc_mtb_01">
                    <button class="btn_submit_02">직원 신규 등록 +</button><br>
                </div>

                <!-- 회원정보 직원계정 추가 팝업 -->
                <div class="thkc_popUpWrap">
                    <div class="thkc_popWrap">
                        <div class="thkc_close">
                            <i class="fa-solid fa-xmark"></i>
                        </div>

                        <div class="thkc_joinWrap">
                            <div class="joinTitle">
                                <div class="boxLeft">직원신규 등록</div>
                                <div class="boxRright"><span class="important">*</span>직원 정보를 입력하세요!</div></div>
                            <!-- table 계정정보 -->
                            <div class="thkc_tableWrap thkc_bbs-more">
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">아이디</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <label for="id" class="thkc_blind">아이디</label><input class="thkc_input" id="mm_id" placeholder="아이디" value="" type="text" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">비밀번호</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <label for="password" class="thkc_blind">비밀번호</label><input class="thkc_input" id="mm_password" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value="" type="password" autocomplete="off" />
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">이름</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div>
                                            <label for="name" class="thkc_blind">이름</label><input class="thkc_input" id="mm_name" placeholder="홍길동" value="" type="text" autocomplete="off" />
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <div class="table-box table-box_02 tel-num">
                                    <div class="tit03 bbs-pd_01">연락처</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="flex-box">
                                            <div class="flex-box">
                                                <label for="mm_hp" class="thkc_blind">연락처</label>
                                                <input class="thkc_input" placeholder="010-0001-0002" id="mm_hp" name="" maxlength="14" value="" type="text" autocomplete="off" />
                                            </div>
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <!-- 이메일  -->
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">이메일</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="thkc_dfc">
                                            <label for="email" class="thkc_blind">이메일</label><input class="thkc_input" id="mm_email" placeholder="중복되지 않은 이메일주소를 입력" value="" type="text" autocomplete="off" />
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <!-- 메모  -->
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">메모</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="thkc_dfc">
                                            <label for="memo" class="thkc_blind">메모</label><input class="thkc_input" id="mm_memo" placeholder="직급/업무내용 등" value="" type="text" autocomplete="off" />
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
                                <div class="thkc_btnWrap_03">
                                    <button class="cancel">취소</button>
                                    <button class="on">등록하기</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>



            <script>
                // 담당자 추가
                function manager_add(){
                    if(!confirm("신규직원을 등록 하시겠습니까?")) { return; }
                    if( !ck_input( '' ) ) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_manager.php', type: 'POST', dataType: 'json',
                        data: {
                            "w": "",
                            "mm_id": $(".thkc_popUpWrap #mm_id").val(),
                            "mm_pw":  $(".thkc_popUpWrap #mm_password").val(),
                            "mm_name":  $(".thkc_popUpWrap #mm_name").val(),
                            "mm_tel":  $(".thkc_popUpWrap #mm_hp").val(),
                            "mm_email":  $(".thkc_popUpWrap #mm_email").val(),
                            "mm_memo":  $(".thkc_popUpWrap #mm_memo").val()
                        },
                        success: function(data) {
                            
                        },
                        error: function(e) {}
                    })
                    .done(function() {
                        alert('직원 정보 등록이 완료되었습니다.');
                        window.location.reload();
                    })
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert(data && data.message);
                    }); 
                }


                // 담당자 정보 변경
                function manager_modify(_no){
                        
                    $(".thkc_popUpWrap #mm_id").val( $(".manager_" + _no + " #mb_id").val() );
                    $(".thkc_popUpWrap #mm_id").attr("disabled", true); 

                    $(".thkc_popUpWrap #mm_name").val( $(".manager_" + _no + " #mb_name").val() );
                    $(".thkc_popUpWrap #mm_hp").val( $(".manager_" + _no + " #mm_tel").val() );
                    $(".thkc_popUpWrap #mm_email").val( $(".manager_" + _no + " #mm_email").val() );
                    $(".thkc_popUpWrap #mm_memo").val( $(".manager_" + _no + " #mm_memo").val() );

                    $(".thkc_popUpWrap .boxLeft").text("직원정보 수정");
                    $(".thkc_popUpWrap .boxRright").hide();
                    
                    $('.thkc_popUpWrap .thkc_btnWrap_03 .on').text("변경하기"); 
                    $(".thkc_popUpWrap .thkc_btnWrap_03 .on").attr("onclick", "confirm_modify('" + _no + "')");
                    
                    $(".thkc_popUpWrap").css("display", "flex").hide().fadeIn();
                    $(".thkc_popOverlay").show();
                    
                    document.body.classList.add("stop-scroll");                    
                }
                function confirm_modify( no ) {
                    if(!confirm("직원 정보를 변경 하시겠습니까?")) { return; }

                    if( !ck_input( 'u' ) ) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_manager.php', type: 'POST', dataType: 'json',
                        data: {
                            "w": "u",
                            "mm_id": $(".thkc_popUpWrap #mm_id").val(),
                            "mm_pw":  $(".thkc_popUpWrap #mm_password").val(),
                            "mm_name":  $(".thkc_popUpWrap #mm_name").val(),
                            "mm_tel":  $(".thkc_popUpWrap #mm_hp").val(),
                            "mm_email":  $(".thkc_popUpWrap #mm_email").val(),
                            "mm_memo":  $(".thkc_popUpWrap #mm_memo").val()
                        },
                        success: function(data) {
                            
                        },
                        error: function(e) {}
                    })
                    .done(function() {
                        alert('담당자 변경이 완료되었습니다.');
                        window.location.reload();
                    })
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert(data && data.message);
                    }); 
                }


                // 담당자 삭제
                function manager_del( _no ) {
                    if(!confirm("정말 직원을 삭제하시겠습니까? \n삭제하는경우 해당 ID는 다시 사용하지 못합니다")) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_manager.php', type: 'POST', dataType: 'json',
                        data: {
                            "w": "d", "mm_id": $(".manager_" + _no + " #mb_id").val()
                        },
                        success: function(data) {                            
                        },
                        error: function(e) {}
                    })
                    .done(function() {
                        alert('직원 삭제 완료되었습니다.');
                        window.location.reload();
                    })
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert(data && data.message);
                    }); 
                }



                function ck_input( mod ) {

                    if( !mod && !$(".thkc_popUpWrap #mm_id").val() ){ 
                        alert("아이디를 입력하세요."); return false;
                    } else if( !$(".thkc_popUpWrap #mm_password").val() ){
                        alert("비밀번호를 입력하세요."); return false;
                    } else if( !$(".thkc_popUpWrap #mm_name").val() ){
                        alert("이름을 입력하세요."); return false;
                    } else if( !$(".thkc_popUpWrap #mm_hp").val() ){
                        alert("휴대폰번호를 입력하세요."); return false;
                    }

                    var msg = check_pw( $(".thkc_popUpWrap #mm_password").val() );
                    if(msg) { 
                        alert(msg); 
                        return false;
                    }

                    return true;
                }


                $(".thkc_btnWrap .btn_submit_02").click(function () {
                    $(".thkc_popUpWrap .boxLeft").text("직원신규 등록");
                    $(".thkc_popUpWrap input").val("");

                    $(".thkc_popUpWrap #mm_id").attr("disabled", false);
                    $(".thkc_popUpWrap .boxRright").show();

                    $('.thkc_popUpWrap .thkc_btnWrap_03 .on').text("등록하기");                    
                    $('.thkc_popUpWrap .thkc_btnWrap_03 .on').attr("onclick", "manager_add()");
                });
                

                $('.thkc_popUpWrap .thkc_joinWrap .thkc_tableWrap .thkc_btnWrap_03 .cancel').click(function () {
                    $(".thkc_popUpWrap").hide();
                    $(".thkc_popOverlay").hide();
                    document.body.classList.remove("stop-scroll");
                });

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

                // 숫자만 입력!!
                $('.numOnly').on('keyup', function() {
                    var num = $(this).val();
                    num.trim();
                    this.value = only_num(num) ;
                });


                function SAVE_MEMBER() {
                    if(!confirm("회원정보를 변경 하시겠습니까?")) { return; }

                    if( !$("#hp2").val() || !$("#hp3").val() ) {
                        alert('담당자 휴대전화 번호를 입력하세요.'); return false;
                    }

                    var msg = check_pw( $("#password").val() );
                    if(msg) { 
                        alert(msg); 
                        return false;
                    }

                    if( $("#password").val() != $('#password2').val() ) {                        
                        alert("비밀번호가 일치하지 않습니다.");
                        return false;
                    }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "password": $("#password").val(),
                            "name": $("#name").val(),
                            "hp": $("#hp1").val() + "-" + $("#hp2").val() + "-" + $("#hp3").val(),
                            "email": $("#email").val()                            
                        },
                        success: function(data) {
                            if( data.YN === "Y" ) {
                                alert('회원정보를 변경 되었습니다.');
                                window.location.reload();
                            } else {
                                alert(data.YN_msg);
                            }
                        },
                        error: function(e) {}
                    });

                }
            </script>