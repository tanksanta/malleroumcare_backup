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


            <input type="hidden" id="mbno" name="" value="<?=$member['mb_no'];?>">
            <input type="hidden" id="mbid" name="" value="<?=$member['mb_id'];?>">
            <input type="hidden" id="mode" name="" value="<?=$_GET['STEP']?$_GET['STEP']:"stop01";?>">

            
            <section class="thkc_section">
                <!-- 회원정보 수정 -->
                <div class="thkc_memberModifyWrap">
                    <h3>회원 정보 수정</h3>
                    <ul>
                        <li><a href="javascript:void(0);" class="active"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify01.svg" alt=""><p>사업자 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop02"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify02.svg" alt=""><p>계정 관리</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop03"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify03.svg" alt=""><p>배송지 정보</p></a></li>
                        <li><a href="<?=G5_BBS_URL?>/member_info_newform.php?STEP=stop04"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify04.svg" alt=""><p>서비스 정보</p></a></li>
                        <li><a href="#"><img src="<?=G5_IMG_URL?>/new_common/thkc_icon_modify05.svg" alt=""><p>환경 설정</p></a></li>
                    </ul>
                </div>
                <!-- 회원정보 수정 내용 -->
                <div class="thkc_joinWrap">
                    <!-- title 사업자정보-->
                    <div class="joinTitle">
                        <div class="boxLeft">사업자 정보</div>
                    </div>
                    <!-- table 사업자 정보 -->
                    <div class="thkc_tableWrap">
                        <div class="table-box m30">
                            <div class="tit">사업자등록번호</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="bnum" class="thkc_blind">사업자등록번호</label>
                                    <input class="thkc_input" id="bnum" placeholder="000-00-00000" value="<?=$member['mb_giup_bnum']?>" type="text" disabled />
                                </div>
                                <!-- <div class="error-txt">담당자 이름을 입력해주세요.</div> -->
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit"><?php if( $member['mb_type'] != "partner" ) { ?>사업소 <?php } else { ?>파트너 <?php } ?>분류</div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc">
                                    <?php if( $member['mb_type'] != "partner" ) { ?>
                                    <input id="mb_default_type2" value="복지용구사업소" type="checkbox"<?=( (strpos($member['mb_default_type'],"복지용구사업소")!==false)?" checked":"" );?> /><span class="thkc_ml_01">복지용구사업소</span>
                                    <input id="mb_default_type1" value="의료기기상" type="checkbox"<?=( (strpos($member['mb_default_type'],"의료기기상")!==false)?" checked":"" );?> /><span class="thkc_ml_01">의료기기상</span>
                                    <input id="mb_default_type3" value="복지센터" type="checkbox"<?=( (strpos($member['mb_default_type'],"복지센터")!==false)?" checked":"" );?> /><span class="thkc_ml_01">복지센터</span>
                                    <?php } else { ?>
                                    <input id="mb_partner_type2" value="직배송" type="checkbox"<?=( (strpos($member['mb_partner_type'],"직배송")!==false)?" checked":"" );?> /><span class="thkc_ml_01">직배송파트너</span>
                                    <input id="mb_partner_type1" value="설치(소독)" type="checkbox"<?=( (strpos($member['mb_partner_type'],"설치(소독)")!==false)?" checked":"" );?> /><span class="thkc_ml_01">설치(소독)파트너</span>
                                    <input id="mb_partner_type3" value="물품공급" type="checkbox"<?=( (strpos($member['mb_partner_type'],"물품공급")!==false)?" checked":"" );?> /><span class="thkc_ml_01">물품공급파트터</span>
                                    <?php } ?>
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">상호명</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="bname" class="thkc_blind">상호명</label>
                                    <input class="thkc_input" id="bname" placeholder="티에이치케이사업소" value="<?=$member['mb_giup_bname']?>" type="text" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">대표자명</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="boss_name" class="thkc_blind">대표자명</label>
                                    <input class="thkc_input" id="boss_name" placeholder="홍길동" value="<?=$member['mb_giup_boss_name']?>" type="text" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">업태</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="buptae" class="thkc_blind">업태</label>
                                    <input class="thkc_input" id="buptae" placeholder="제조 및 판매" value="<?=$member['mb_giup_buptae']?>" type="text" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box">
                            <div class="tit">종목</div>
                            <div class="thkc_cont">
                                <div>
                                    <label for="bupjong" class="thkc_blind">종목</label>
                                    <input class="thkc_input" id="bupjong" placeholder="의수 의족 보조기 복지용품" value="<?=$member['mb_giup_bupjong']?>" type="text" />
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box tel-num">
                            <div class="tit">사업소 전화번호</div><?php $mb_tel =explode('-',$member['mb_tel']); ?>
                            <div class="thkc_cont">
                                <div class="flex-box">
                                    <div class="flex-box">
                                        <label for="tel1" class="thkc_blind">사업소 전화번호</label>
                                        <select class="thkc_input" id="tel1">
                                            <option value="010"<?=($mb_tel[0]=="010")?" selected":"";?>>010</option>
                                            <option value="011"<?=($mb_tel[0]=="011")?" selected":"";?>>011</option>
                                            <option value="016"<?=($mb_tel[0]=="016")?" selected":"";?>>016</option>
                                            <option value="017"<?=($mb_tel[0]=="017")?" selected":"";?>>017</option>
                                            <option value="018"<?=($mb_tel[0]=="018")?" selected":"";?>>018</option>
                                            <option value="019"<?=($mb_tel[0]=="019")?" selected":"";?>>019</option>
                                        </select> &nbsp;-
                                        <input class="thkc_input numOnly" placeholder="0001" id="tel2" name="" maxlength="4" value="<?=$mb_tel[1]?>" type="text" /> &nbsp;-
                                        <input class="thkc_input numOnly" placeholder="0002" id="tel3" name="" maxlength="4" value="<?=$mb_tel[2]?>" type="text" />
                                    </div>
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <div class="table-box tel-num">
                            <div class="tit">사업소 팩스번호</div><?php $mb_fax =explode('-',$member['mb_fax']); ?>
                            <div class="thkc_cont">
                                <div class="flex-box">
                                    <div class="flex-box">
                                        <label for="fax1" class="thkc_blind">사업소 팩스번호</label>
                                        <select class="thkc_input" id="fax1">
                                            <option value="02"<?=($mb_fax[0]=="02")?" selected":"";?>>02</option>
                                            <option value="031"<?=($mb_fax[0]=="031")?" selected":"";?>>031</option>
                                            <option value="032"<?=($mb_fax[0]=="032")?" selected":"";?>>032</option>
                                            <option value="033"<?=($mb_fax[0]=="033")?" selected":"";?>>033</option>
                                            <option value="041"<?=($mb_fax[0]=="041")?" selected":"";?>>041</option>
                                            <option value="042"<?=($mb_fax[0]=="042")?" selected":"";?>>042</option>
                                            <option value="043"<?=($mb_fax[0]=="043")?" selected":"";?>>043</option>
                                            <option value="044"<?=($mb_fax[0]=="044")?" selected":"";?>>044</option>
                                            <option value="051"<?=($mb_fax[0]=="051")?" selected":"";?>>051</option>
                                            <option value="052"<?=($mb_fax[0]=="052")?" selected":"";?>>052</option>
                                            <option value="053"<?=($mb_fax[0]=="053")?" selected":"";?>>053</option>
                                            <option value="054"<?=($mb_fax[0]=="054")?" selected":"";?>>054</option>
                                            <option value="055"<?=($mb_fax[0]=="055")?" selected":"";?>>055</option>
                                            <option value="061"<?=($mb_fax[0]=="061")?" selected":"";?>>061</option>
                                            <option value="062"<?=($mb_fax[0]=="062")?" selected":"";?>>062</option>
                                            <option value="063"<?=($mb_fax[0]=="063")?" selected":"";?>>063</option>
                                            <option value="064"<?=($mb_fax[0]=="064")?" selected":"";?>>064</option>
                                            <option value="0502"<?=($mb_fax[0]=="0502")?" selected":"";?>>0502</option>
                                            <option value="0503"<?=($mb_fax[0]=="0503")?" selected":"";?>>0503</option>
                                            <option value="0504"<?=($mb_fax[0]=="0504")?" selected":"";?>>0504</option>
                                            <option value="0505"<?=($mb_fax[0]=="0505")?" selected":"";?>>0505</option>
                                            <option value="0506"<?=($mb_fax[0]=="0506")?" selected":"";?>>0506</option>
                                            <option value="0507"<?=($mb_fax[0]=="0507")?" selected":"";?>>0507</option>
                                            <option value="0508"<?=($mb_fax[0]=="0508")?" selected":"";?>>0508</option>
                                            <option value="070"<?=($mb_fax[0]=="070")?" selected":"";?>>070</option>
                                            <option value="010"<?=($mb_fax[0]=="010")?" selected":"";?>>010</option>
                                            <option value="011"<?=($mb_fax[0]=="011")?" selected":"";?>>011</option>
                                            <option value="016"<?=($mb_fax[0]=="016")?" selected":"";?>>016</option>
                                            <option value="017"<?=($mb_fax[0]=="017")?" selected":"";?>>017</option>
                                            <option value="018"<?=($mb_fax[0]=="018")?" selected":"";?>>018</option>
                                            <option value="019"<?=($mb_fax[0]=="019")?" selected":"";?>>019</option>
                                        </select> &nbsp;-
                                        <input class="thkc_input numOnly" id="fax2" placeholder="9999" name="" maxlength="4" fw-filter="" fw-label="팩스번호" fw-alone="N" fw-msg="" value="<?=$mb_fax[1]?>" type="text" /> &nbsp;- 
                                        <input class="thkc_input numOnly" id="fax3" placeholder="9999" name="" maxlength="4" fw-filter="" fw-label="팩스번호" fw-alone="N" fw-msg="" value="<?=$mb_fax[2]?>" type="text" />
                                    </div>
                                </div>
                                <div class="error-txt error"></div>
                            </div>
                        </div>
                        <!-- 사업장 주소 비활성화 -->
                        <form class="form-horizontal register-form" role="form" id="addr_info" name="addr_info" onsubmit="return false">
                        <div class="table-box address ">
                            <div class="tit">사업장 주소</div>
                            <div class="thkc_cont">
                                <div class="thkc_dfc_m">
                                    <div class="flex-box thkc_dfc">
                                        <label for="zip" class="thkc_blind">주소 찾기</label>
                                        <input class="thkc_input" id="addr_zip" name="addr_zip" placeholder="22850" readonly="readonly" maxlength="14" value="<?=$member['mb_giup_zip1'].$member['mb_giup_zip2']?>" type="text" />
                                        <a href="javascript:void(0);" class="thkc_btn_bbs win_zip_find" onclick="win_zip('addr_info', 'addr_zip', 'addr1', 'addr2', 'addr3', 'jibeon');" id="">주소 찾기</a>
                                    </div>
                                </div>
                                <div title="기본주소">
                                    <label for="addr1" class="thkc_blind">기본주소</label>
                                    <input class="thkc_input" id="addr1" name="addr1" placeholder="경기도 화성시 동탄영천로192-39길" readonly="readonly" value="<?=$member['mb_giup_addr1'];?>" type="text" />
                                </div>
                                <div title="나머지주소">
                                    <label for="addr2" class="thkc_blind">나머지주소</label>
                                    <input class="thkc_input" id="addr2" name="addr2" placeholder="3층" value="<?=$member['mb_giup_addr2'];?>" type="text" />
                                </div>
                            </div>

                            <input type="hidden" id="addr3" name="addr3" value="">
                            <input type="hidden" id="jibeon" name="jibeon" value="">
                        </div>
                        </form>
                        <!-- <div class="table-box pt-30">
                            <div class="tit">사업자등록증 첨부
                            </div>
                            <div class="thkc_cont">
                                <div class="flex-box thkc_dfc_02">                        
                                    <a href="javascript:void(0);" class="thkc_btn_bbs" onclick="" id="">파일선택</a>
                                    <span>선택 된 파일 없음</span>
                                </div>
                                <div class="thkc_cont_txt">*파일유형은 pdf, png, jpg, gif 용량은 10Mbyte 이하만 등록가능합니다.</div>
                                <div class="error-txt error"></div>
                            </div>
                        </div> -->
                        <!-- 버튼 -->
                        <div class="thkc_btnWrap thkc_mtb_01">
                            <a href="javascript:void(0);"><button class="btn_submit_01" onclick="SAVE_MEMBER()">저장</button></a><br>
                        </div>
                    </div>
                    <!-- 회원정보 수정 내용 end -->
                </div>
            </section>



            <script>
                // 숫자만 입력!!
                $('.numOnly').on('keyup', function() {
                    var num = $(this).val();
                    num.trim();
                    this.value = only_num(num) ;
                });

                function SAVE_MEMBER() {
                    setTimeout(() => document.body.classList.remove("stop-scroll"), 1000);
                    if(!confirm("회원정보를 변경 하시겠습니까?")) { return; }


                    var partner_type = default_type = "";
                    if( $("#mb_default_type1").length ) {

                        if( $('input:checkbox[id="mb_default_type1"]').is(':checked') ) { default_type += $("#mb_default_type1").val() + "|"; }
                        if( $('input:checkbox[id="mb_default_type2"]').is(':checked') ) { default_type += $("#mb_default_type2").val() + "|"; }
                        if( $('input:checkbox[id="mb_default_type3"]').is(':checked') ) { default_type += $("#mb_default_type3").val() + "|"; }

                    } else if( $("#mb_partner_type1").length ) {

                        if( $('input:checkbox[id="mb_partner_type1"]').is(':checked') ) { partner_type += $("#mb_partner_type1").val() + "|"; }
                        if( $('input:checkbox[id="mb_partner_type2"]').is(':checked') ) { partner_type += $("#mb_partner_type2").val() + "|"; }
                        if( $('input:checkbox[id="mb_partner_type3"]').is(':checked') ) { partner_type += $("#mb_partner_type3").val() + "|"; }

                    }
                    
                    if( !$("#tel2").val() || !$("#tel3").val() ) {
                        alert('사업소 연락처를 입력하세요.'); return false;
                    }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_update.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": $("#mode").val(),
                            "mbno": $("#mbno").val(),
                            "mbid": $("#mbid").val(),

                            "default_type" : default_type,
                            "partner_type" : partner_type,

                            "bname": $("#bname").val(),
                            "name": $("#name").val(),

                            "boss_name": $("#boss_name").val(),
                            "buptae": $("#buptae").val(),
                            "bupjong": $("#bupjong").val(),

                            "tel": $("#tel1").val() + "-" + $("#tel2").val() + "-" + $("#tel3").val(),
                            "fax": $("#fax1").val() + "-" + $("#fax2").val() + "-" + $("#fax3").val(),

                            "addr_zip": $("#addr_zip").val(),
                            "addr1": $("#addr1").val(),
                            "addr2": $("#addr2").val()
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