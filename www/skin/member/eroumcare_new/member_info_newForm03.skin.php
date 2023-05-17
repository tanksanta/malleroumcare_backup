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
                                                <a href="javascript:void(0);" class="thkc_btn_bbs win_zip_find" onclick="win_zip('addr_info', 'addr_zip', 'addr1', 'addr2', 'addr3', 'jibeon'); $('#daum_juso_pageaddr_zip').css('width','110%');" id="">주소 찾기</a>
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
                });

                
                $('.thkc_joinWrap .thkc_tableWrap .thkc_btnWrap_03 .cancel').click(function () {
                    $(".thkc_popUpWrap").hide();
                    $(".thkc_popOverlay").hide();
                    document.body.classList.remove("stop-scroll");
                });
                

                function addr_more_add() {
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

            </script>