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

            <script src="<?=G5_JS_URL?>/signature_pad.umd.js"></script>

            <style type="text/css">
                .popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.8); }
                .popup_box_con { background: #ffffff; z-index: 99999; height:325px; width: 405px; margin: 20% auto;}

                #sign-pad { position: relative; background-color: #fff; width: 100%; height: 78%; }
                #sign-pad canvas { position: absolute; z-index: 9999; width: 95%; margin: 0px 10px; }
                #sign-back { position: absolute; display: -ms-flexbox; display: flex; -ms-flex-align: center; align-items: center; -ms-flex-pack: center; justify-content: center; text-align: center; color: #aaa; background-color: #f2f2f2; width: 95%; margin: 0px 10px; }

                textarea { width: 100%; height: 10em; border: none; resize: none; }
            </style>
            

            <input type="hidden" id="mbno" name="mbno" value="<?=$member['mb_no'];?>">
            <input type="hidden" id="mbid" name="mbid" value="<?=$member['mb_id'];?>">
            <input type="hidden" id="mode" name="mode" value="<?=$_GET['STEP'];?>">


            <section class="thkc_section">
                <!-- 회원정보수정 (메뉴) -->
                <div class="thkc_memberModifyWrap">
                    <h3>회원 정보 수정</h3>
                    <ul>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop01"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify01.svg" alt=""><p>사업자 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop02"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify02.svg" alt=""><p>계정 관리</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop03"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify03.svg" alt=""><p>배송지 정보</p></a></li>
                        <li><a href="javascript:void(0);" class="active"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify04.svg" alt=""><p>서비스 정보</p></a></li>
                        <li><a href="#"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify05.svg" alt=""><p>환경 설정</p></a></li>
                    </ul>
                </div>
                <!-- 회원정보 서비스 정보 -->
                <div class="thkc_joinWrap">
                    <!-- title 간편계약서 추가정보-->
                    <div class="joinTitle">
                        <div class="boxLeft">간편계약서 추가정보</div>
                        <div class="thkc_btnWrap_03">
                            <button class="save" onclick="SAVE_MEMBER();">정보저장</button>
                        </div>
                    </div>
                    <!-- table 배편계약서 추가정보 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box">
                            <div class="tit">장기요양기관번호</div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc">
                                    <label for="mb_ent_num" class="thkc_blind">장기요양기관번호</label>
                                    <input class="thkc_input numOnly" id="mb_ent_num" placeholder="계약서에 입력될 장기요양기관번호를 입력"  maxlength="12" value="<?=$member["mb_ent_num"]; ?>" type="text" />
                                </div>
                                <div class="thkc_cont_txt">* 계약서에 입력될 장기요양기관번호를 입력</div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">사업자 계좌번호</div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc">
                                    <label for="mb_account" class="thkc_blind">사업자 계좌번호</label>
                                    <input class="thkc_input" id="mb_account" placeholder="급여비용 명세서에 입력 될 사업자 계좌정보 입력하세요.  [예] 신한 000-0000-00000 홍길동" value="<?=$member["mb_account"]; ?>" type="text" />
                                </div>
                                <div class="thkc_cont_txt">* 급여비용 명세서에 입력 될 사업자 계좌정보 입력하세요.  [예] 신한 000-0000-00000 홍길동</div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">사업자 직인정보</div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc_02">
                                    <div class="thkc_dfc_03">
                                        <form action="<?=G5_SHOP_URL?>/ajax.member.seal_upload_new.php" method="POST" id="form_seal" onsubmit="return false;">
                                            <a href="#none" class="thkc_btn_bbs btn_se_seal" id="">파일 선택</a>
                                        </form>
                                        <a href="#none" class="thkc_btn_bbs" id="btn_sign">날인 제작</a>
                                    </div>
                                    <div class="thkc_sign">
                                        
                                    <?php if($member["sealFile"]!="") { ?>
                                        <img id='sealFile_img' src="<?=G5_DATA_URL?>/file/member/stamp/<?=$member["sealFile"]; ?>" style="max-width:100%;max-height:100%;">
                                    <?php } else { ?>
                                        <span id='no_img'>등록 된 이미지가 없습니다.</span>
                                        <img src='' id='sealFile_img' style='max-width:100%;max-height:100%;display:none;'>
                                    <?php } ?>

                                    </div>
                                </div>
                                <div class="thkc_cont_txt">*파일유형은 png, jpg, gif 용량은 3Mbyte 이하만 등록가능합니다.</div>
                                <div class="error-txt error"></div>

                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">계약서 특약사항</div>
                            <div class="thkc_cont">
                                <form action="">
                                    <label for="mb_entConAcc01" class="thkc_blind">계약서 특약사항</label>
                                    <textarea class="thkc_textarea" name="mb_entConAcc01" id="mb_entConAcc01" cols="30" rows="7" placeholder="계약서의 특약사항란에 입력 될 내용을 입력"><?=$member["mb_entConAcc01"]; ?></textarea>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <br><br>

                <!-- 요양정보조회 -->
                <div class="thkc_joinWrap">
                    <!-- title 요양정보조회-->
                    <div class="joinTitle">
                        <div class="boxLeft">요양정보조회</div>
                    </div>
                    <!-- table 요양정보조회 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box m15">
                            <div class="tit">공인인증서</div>
                            <div class="thkc_cont">
                                <?php if($member["cert_data_ref"] != "") { ?>
                                <div>공인인증서가 등록되어있습니다.</div>
                                <?php } else { ?>
                                    공인인증서를 등록하세요.
                                <?php } ?>
                            </div>
                        </div>
                        <div class="thkc_btnWrap_03"><button class="on" onClick="tilko_call('1');">변경/갱신</button></div>
                    </div>
                    <!-- 요양정보조회 end -->
                </div>

            </section>


            <div id="popup_sign" class="popup_box popup_sign" style="">
                <div id="" class="popup_box_con" style="">
                    <div class="form-group"><div class="se_sch_hd" style="margin-left:30px;">서명정보 제작하기</div></div>
                    <div id="sign-pad" style="float:left;width:100%">
                        <canvas id="myCanvas" width="390px" height="220px" style="touch-action: none; top: 10px; left: 0px; width: 390px; height: 220px;"></canvas><div id="sign-back" style="top: 10px; left: 0px; width: 390px; height: 220px;">이곳에 사인해주세요.</div>
                    </div>
                    <div style="text-align:center;bottom:0px;float:left;width:100%;">
                        <button type="button" class="btn btn-black btn-sm btn_close" onClick="clearCanvas()">돌아가기</button> <button type="button" id="btn-sign-submit" class="btn btn-black btn-sm ;" style="background:black;" >등록하기</button>
                    </div>
                </div>
            </div>

            <iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false" height="0" width="0"></iframe>

            <script>

                var origWidth = 390;
                var origHeight = 220;

                $('#popup_sign').hide();

                // 날인정보 입력
                $('#btn_sign').click(function() {        
                    $('body').addClass('modal-open');
                    $('#popup_sign').show();
                });

                $('.btn_close').click(function() {
                    $('body').removeClass('modal-open');
                    $('#popup_sign').hide();
                });

                // 날인 패드
                var canvas = document.getElementById('myCanvas');
                var signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'transparent',
                    minDistance: 5,
                    throttle: 3,
                    minWidth: 4,
                    maxWidth: 5
                });


                // 직인 업로드
                var loading_seal = false;
                $('.btn_se_seal').click(function() {
                var $form = $(this).closest('form');

                $form.find('input[name="sealFile"]').remove();
                    $('<input type="file" name="sealFile" accept=".png, .gif, .jpg" style="width: 0; height: 0; overflow: hidden;">').appendTo($form).click();
                });

                $(document).on('change', 'input[name="sealFile"]', function(e) {
                    var $form = $(this).closest('form');

                    if(loading_seal)
                        return alert('직인 이미지를 업로드 중입니다.');
                    
                    loading_seal = true;

                    var formData = new FormData();
                    formData.append('sealFile', this.files[0]);

                    $.ajax({
                        type: 'POST',
                        url: $form.attr('action'),
                        processData: false,
                        contentType: false,
                        data: formData,
                        dataType: 'json'
                    })
                    .done(function(data) {
                        alert('직인 이미지를 업로드했습니다.');
                        $('.se_seal_wr').remove();
                        $("span").remove("#no_img");
                        $("#sealFile_img").attr("src","<?=G5_DATA_URL?>/file/member/stamp/"+data.sealFile).show();
                        $("#sealFile_chk").prop('checked', true);
                        $("span").remove("#red_text");
                        document.body.classList.remove("modal-open");
                    })
                    .fail(function($xhr) {
                        var data = $xhr.responseJSON;
                        alert(data && data.message);
                    })
                    .always(function() {
                        loading_seal = false;
                    });
                });

                function clearCanvas(){
                    // canvas
                    var cnvs = document.getElementById('myCanvas');
                    // context
                    var ctx = canvas.getContext('2d');

                    // 픽셀 정리
                    ctx.clearRect(0, 0, cnvs.width, cnvs.height);
                    // 컨텍스트 리셋
                    ctx.beginPath();
                }

                $('#btn-sign-submit').click(function(e) {
                    e.preventDefault();

                    if (signaturePad.isEmpty()) {
                        return alert("서명을 입력해주세요.");
                    } else {
                        var dataURL = toResizedDataURL(canvas, origWidth, origHeight);
                        state = dataURL;

                        $.post('<?=G5_SHOP_URL?>/ajax.sign_save.php', {
                            img_data: JSON.stringify(state)
                        }, 'json')
                        .done(function(data) {//저장 성공시 하단 정보 재 호출 ajax 호출 필요			
                            alert(data.msg);
                            if(data.ok == "ok") {
                                $('.se_seal_wr').remove();
                                $("span").remove("#no_img");
                                $("#sealFile_img").attr("src",'<?=G5_DATA_URL?>/file/member/stamp/'+data.sealFile).show();
                                $('#popup_sign').hide();
                                clearCanvas();
                                document.body.classList.remove("modal-open");
                            }
                        })
                        .fail(function($xhr) {
                            var data = $xhr.responseJSON;
                            alert(data && data.message);
                        });
                    }
                });


                function toResizedDataURL(canvas, origWidth, origHeight) {
                    var resizedCanvas = document.createElement('canvas');
                    var resizedContext = resizedCanvas.getContext('2d');

                    resizedCanvas.width = origWidth * 1;
                    resizedCanvas.height = origHeight * 1;

                    var $signBack = $('#sign-back');
                    var ratio = Math.max(window.devicePixelRatio || 1, 1);

                    resizedContext.drawImage(canvas,
                        $signBack.css('left').replace(/[^-\d\.]/g, '') , ($signBack.css('top').replace(/[^-\d\.]/g, '')) ,
                        $signBack.width(), $signBack.height() ,
                        0, 0,
                        origWidth * 1, origHeight * 1
                    );
                    return resizedCanvas.toDataURL();
                }


                function tilko_call(a=1){
                    $("#tilko").attr("src","/tilko_test.php?option="+a);
                }

                // 숫자만 입력!!
                $('.numOnly').on('keyup', function() {
                    var num = $(this).val();
                    num.trim();
                    this.value = only_num(num) ;
                });

                function SAVE_MEMBER() {
                    if(!confirm("회원정보를 변경 하시겠습니까?")) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),
                            "mb_ent_num": $("#mb_ent_num").val(),
                            "mb_account": $("#mb_account").val(),
                            "mb_entConAcc01": $("#mb_entConAcc01").val()                            
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
