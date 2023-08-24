<?php
if($member['mb_id'] && !$w) {
  goto_url('/bbs/member_confirm.php?url=register_form.php');
}
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
  include_once('./header.php');

add_javascript(G5_POSTCODE_JS, 0);

?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

    <!--  로그인 페이지 -->
    <div class="thkc_loginWrap">

    </div>
    
    <form class="form-horizontal register-form" role="form" id="fregisterform" name="fregisterform" action="<?=$register_action_url?>" onsubmit="return fregisterform_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="">
    <input type="hidden" name="mb_type" value="<?=$_GET['type']?>">
    <input type="hidden" name="mb_default_type" value="<?=(($_GET['type']=="default")?$_GET['category']:"")?>">
    <input type="hidden" name="mb_partner_type" value="<?=(($_GET['type']=="partner")?$_GET['category']:"")?>">

    <section class="thkc_joinWrap thkc_container_02">
        <h2 class="thkc_titleTop">신규 회원가입</h2>
        <!-- title 가입정보-->
        <div class="joinTitle">
            <div class="boxLeft">가입정보</div>
            <div class="boxRright">
                <span class="important">*</span>표시는 반드시 입력하셔야 합니다.
            </div>
        </div>
        <!-- table 가입정보 -->
        <div class="thkc_tableWrap">
            <div class="table-box m30">
                <div class="tit">담당자명<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_manager_name" class="thkc_blind">담당자명</label>
                        <input class="thkc_input _error_input_inner" id="mb_giup_manager_name" name="mb_giup_manager_name" placeholder="" value="" type="text" autocomplete="off" />
                    </div>
                    <div class="error-txt errorMGRNM"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">아이디<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="reg_mb_id" class="thkc_blind">아이디</label>
                        <input class="thkc_input autofill" id="reg_mb_id" name="mb_id" placeholder="3글자 이상 영문/숫자로만 입력" value="" type="text"  autocomplete="false" />
                    </div>
                    <div class="error-txt errorID"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">비밀번호<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="field show">
                        <label for="reg_mb_password" class="thkc_blind">비밀번호</label>
                        <input class="thkc_input thkc_i autofill" id="reg_mb_password" name="mb_password" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value="" type="password"  autocomplete="new-password" />
                        <i>
                            <img class="icon icon-eyes-on" src="<?=G5_IMG_URL?>/new_common/icon_input_eye.png">
                            <img class="icon icon-eyes-off" src="<?=G5_IMG_URL?>/new_common/icon_input_slash.png">
                        </i>
                    </div>
                    <div class="error-txt errorPW"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">비밀번호 확인<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="field show">
                        <label for="reg_mb_password_re" class="thkc_blind">비밀번호 확인</label>
                        <input class="thkc_input" id="reg_mb_password_re" name="mb_password_re" placeholder="" value="" type="password"  autocomplete="off" />
                        <i>
                            <img class="icon icon-eyes-on" src="<?=G5_IMG_URL?>/new_common/icon_input_eye.png">
                            <img class="icon icon-eyes-off" src="<?=G5_IMG_URL?>/new_common/icon_input_slash.png">
                        </i>
                    </div>
                    <div class="error-txt errorPWck"></div>
                </div>
            </div>
            <!-- 담당자 이메일 체크 -->
            <div class="table-box">
                <div class="tit">담당자 이메일<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="reg_mb_email" class="thkc_blind">담당자 이메일</label>
                        <input class="thkc_input _error_input_inner" id="reg_mb_email" name="mb_email" placeholder="" value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorMAIL"></div>
                </div>
            </div>

            <div class="table-box tel-num">
                <div class="tit">담당자 휴대전화<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="flex-box">
                        <div class="flex-box">
                            <label for="mb_hp1" class="thkc_blind">담당자 휴대전화</label>
                            <select class="thkc_input" id="mb_hp1" name="mb_hp1">
                                <option value="010">010</option>
                                <option value="011">011</option>
                                <option value="016">016</option>
                                <option value="017">017</option>
                                <option value="018">018</option>
                                <option value="019">019</option>
                            </select> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_hp2" name="mb_hp2" maxlength="4" value="" type="text"  autocomplete="off" /> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_hp3" name="mb_hp3" maxlength="4" value="" type="text"  autocomplete="off" />
                        </div>
                    </div>
                    <div class="error-txt errorHP"></div>
                </div>
            </div>
        </div>

        <!-- title 사업자정보-->
        <div class="joinTitle">
            <div class="boxLeft">사업자 정보</div>
            <div class="boxRright">
                <span class="important">*</span>표시는 반드시 입력하셔야 합니다.
            </div>
        </div>
        <!-- table 사업자 정보 -->
        <div class="thkc_tableWrap">
            <div class="table-box m30">
                <div class="tit">사업자등록번호<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_bnum" class="thkc_blind">사업자등록번호</label>
                        <input class="thkc_input" id="mb_giup_bnum" name="mb_giup_bnum" placeholder="숫자만 입력 " maxlength="12" value="" type="text"  autocomplete="off" />
                        <?php
                            // 23.03.31 : 서원 - 주석용
                            // 				기존 사업자등록번호가 '-'(하이픈)이 입력된 상태 인데... 기획서에는 숫자만 입력 받으라고 되어있음.
                            //				기획서 대로 진행하며, 추후 문제 발생할 경우 입력 필드 조건을 바꿔야 하거나, 기존 입력된 사업자번호 컨버전 필요!! ( 단, 이카운트와 연동 및 다른쪽 사업자번호로 연동되는 부분 문제성 확인 필요. )
                        ?>
                    </div>
                    <div class="error-txt errorBNUM"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">장기요양기관번호<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="thkc_dfc">
                        <label for="mb_ent_num" class="thkc_blind">장기요양기관번호</label>
                        <input class="thkc_input thkc_input50 numOnly" id="mb_ent_num" name="mb_ent_num" placeholder="숫자만 입력 " maxlength="11" value="" type="text"  autocomplete="off" />
                        <input id="mb_ent_num_ck" value="N" type="checkbox" />
                        <span class="thkc_ml_01">기관번호 없음</span>
                    </div>
                    <div class="error-txt errorENTNUM"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">상호명<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_bname" class="thkc_blind">상호명</label>
                        <input class="thkc_input" id="mb_giup_bname" name="mb_giup_bname" placeholder=" " value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorBNAME"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">대표자명<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_boss_name" class="thkc_blind">대표자명</label>
                        <input class="thkc_input" id="mb_giup_boss_name" name="mb_giup_boss_name" placeholder=" " value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorBOSS"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">업태<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_buptae" class="thkc_blind">업태</label>
                        <input class="thkc_input" id="mb_giup_buptae" name="mb_giup_buptae" placeholder=" " value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorUPTAE"></div>
                </div>
            </div>
            <div class="table-box">
                <div class="tit">종목<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div>
                        <label for="mb_giup_bupjong" class="thkc_blind">종목</label>
                        <input class="thkc_input" id="mb_giup_bupjong" name="mb_giup_bupjong" fw-label="업태" fw-msg="" placeholder=" " value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorUPJONG"></div>
                </div>
            </div>
            <div class="table-box tel-num">
                <div class="tit">사업소 전화번호<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="flex-box">
                        <div class="flex-box">
                            <label for="mb_tel1" class="thkc_blind">사업소 전화번호</label>
                            <select class="thkc_input" id="mb_tel1" name="mb_tel1">
                                <option value="010">010</option>
                                <option value="011">011</option>
                                <option value="016">016</option>
                                <option value="017">017</option>
                                <option value="018">018</option>
                                <option value="019">019</option>
                            </select> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_tel2" name="mb_tel2" maxlength="4" value="" type="text"  autocomplete="off" /> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_tel3" name="mb_tel3" maxlength="4" value="" type="text"  autocomplete="off" />
                        </div>
                    </div>
                    <div class="error-txt errorTEL"></div>
                </div>
            </div>
            <div class="table-box tel-num">
                <div class="tit">사업소 팩스번호
                </div>
                <div class="thkc_cont">
                    <div class="flex-box">
                        <div class="flex-box">
                            <label for="mb_fax1" class="thkc_blind">사업소 팩스번호</label>
                            <select class="thkc_input" id="mb_fax1" name="mb_fax1">
                                <option value="02">02</option>
                                <option value="031">031</option>
                                <option value="032">032</option>
                                <option value="033">033</option>
                                <option value="041">041</option>
                                <option value="042">042</option>
                                <option value="043">043</option>
                                <option value="044">044</option>
                                <option value="051">051</option>
                                <option value="052">052</option>
                                <option value="053">053</option>
                                <option value="054">054</option>
                                <option value="055">055</option>
                                <option value="061">061</option>
                                <option value="062">062</option>
                                <option value="063">063</option>
                                <option value="064">064</option>
                                <option value="0502">0502</option>
                                <option value="0503">0503</option>
                                <option value="0504">0504</option>
                                <option value="0505">0505</option>
                                <option value="0506">0506</option>
                                <option value="0507">0507</option>
                                <option value="070">070</option>
                                <option value="010">010</option>
                                <option value="011">011</option>
                                <option value="016">016</option>
                                <option value="017">017</option>
                                <option value="018">018</option>
                                <option value="019">019</option>
                                <option value="0508">0508</option>
                            </select> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_fax2" name="mb_fax2" maxlength="4" fw-filter="" fw-label="팩스번호" fw-alone="N" fw-msg="" value="" type="text"  autocomplete="off" /> &nbsp;-
                            <input class="thkc_input numOnly" id="mb_fax3" name="mb_fax3" maxlength="4" fw-filter="" fw-label="팩스번호" fw-alone="N" fw-msg="" value="" type="text"  autocomplete="off" />
                        </div>
                    </div>
                    <div class="error-txt errorFAX"></div>
                </div>
            </div>
            <!-- 사업장 주소 비활성화 -->
            <div class="table-box address ">
                <div class="tit">사업장 주소<span class="important">*</span></div>
                <div class="thkc_cont">
                    <div class="thkc_dfc_m">
                        <div class="flex-box thkc_dfc">
                            <label for="zip" class="thkc_blind">주소 찾기</label>
                            <input class="thkc_input thkc_bg02_input" id="mb_giup_zip" name="mb_giup_zip" placeholder="우편번호" readonly="readonly" maxlength="14" value="" type="text" disabled />
                            <a href="javascript:void(0);" class="thkc_btn_bbs win_zip_find" onclick="win_zip('fregisterform', 'mb_giup_zip', 'mb_giup_addr1', 'mb_giup_addr2', 'mb_giup_addr3', 'mb_giup_addr_jibeon');" id="">주소 찾기</a>
                        </div>

                        <div class="flex-box thkc_dfc_04">
                            <input id="mb_address_same" name="mb_address_same" value="Copy" type="checkbox" />
                            <span class="thkc_ml_01">기본 배송지로 설정</span>
                        </div>

                    </div>
                    <div title="기본주소">
                        <label for="addr1" class="thkc_blind">기본주소</label>
                        <input class="thkc_input thkc_bg02_input" name="mb_giup_addr1" id="mb_giup_addr1" placeholder="기본주소" readonly="readonly" value="" type="text" disabled />
                    </div>
                    <div title="나머지주소">
                        <label for="addr2" class="thkc_blind">나머지주소</label>
                        <input class="thkc_input" name="mb_giup_addr2" id="mb_giup_addr2" placeholder="나머지주소" value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorBADDR"></div>
                    <input type="hidden" name="mb_giup_addr3" id="mb_giup_addr3" value="">
                    <input type="hidden" name="mb_giup_addr_jibeon" id="mb_giup_addr_jibeon" value="">
                </div>
            </div>
            <!-- 배송지 주소 활성화 -->
            <div class="table-box address pt-30 ">
                <div class="tit">배송지 주소<span class="important">*</span></div>
                <div class="thkc_cont">
                    <div class="thkc_dfc_m">
                        <div class="flex-box thkc_dfc">
                            <label for="zip" class="thkc_blind">주소 찾기</label>
                            <input class="thkc_input thkc_bg02_input" id="mb_zip" name="mb_zip" placeholder="우편번호" readonly="readonly" maxlength="14" value="" type="text" disabled />
                            <a href="javascript:void(0);" class="thkc_btn_bbs win_zip_find" onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');" id="">주소 찾기</a>
                        </div>
                    </div>
                    <div title="기본주소">
                        <label for="addr1" class="thkc_blind">기본주소</label>
                        <input class="thkc_input thkc_bg02_input" id="mb_addr1" name="mb_addr1" placeholder="기본주소" readonly="readonly" value="" type="text" disabled />
                    </div>
                    <div title="나머지주소">
                        <label for="addr2" class="thkc_blind">나머지주소</label>
                        <input class="thkc_input" id="mb_addr2" name="mb_addr2" placeholder="나머지주소" value="" type="text"  autocomplete="off" />
                    </div>
                    <div class="error-txt errorADDR"></div>
                    <input type="hidden" id="mb_addr3" name="mb_addr3" value="">
                    <input type="hidden" id="mb_addr_jibeon" name="mb_addr_jibeon" value="">
                </div>
            </div>
            <div class="table-box pt-30">
                <div class="tit">사업자등록증 첨부<span class="important">*</span>
                </div>
                <div class="thkc_cont">
                    <div class="flex-box thkc_dfc_02">
                        <input type="file" name="crnFile" accept=".gif, .jpg, .png, .pdf" class="thkc_btn_bbs" id="mb_giup_file1">
                    </div>
                    <div class="thkc_cont_txt">*파일유형은 pdf, png, jpg, gif 용량은 10Mbyte 이하만 등록가능합니다.</div>
                    <div class="error-txt errorFILE1"></div>
                </div>
            </div>
        </div>
        <!-- 전체약관 -->
        <div class="table-box">
            <div class="thkc_dfc03">
                <div class="flex-box">
                    <label for="allAgree" class="thkc_blind">전체약관에 모두 동의합니다</label>
                    <input id="allAgree" name="allAgree" value="Y" type="checkbox" />
                    <span class="thkc_agree_title">전체약관에 모두 동의합니다.</span>
                </div>
                <div class="thkc_agree_title_03">서비스 약관을 모두 확인하신 후 동의 해주세요.</div>
            </div>
            <div class="thkc_agreeWrap">
                <div class="thkc_menu">
                    <div class="thkc_dfc03">
                        <div class="flex-box">
                            <label for="agree1" class="thkc_blind">서비스이용약관 동의</label>
                            <input id="agree1" name="agree1" value="a1_Y" type="checkbox" />
                            <span class="thkc_agree_title_02">[필수] 이로움 플랫폼 서비스이용약관 동의</span>
                        </div>
                        <div class="thkc_dfc btn_con"> <a href="#none" class="text_line pl-30">내용보기 </a><img
                                src="<?=G5_IMG_URL?>/new_common/thkc_ico_arrow_next.svg" alt=""></div>

                    </div>
                    <!-- 내용 이용약관-->
                    <div class="thkc_iner_cont">
                        <div action="" class="agreeForm">                            
                            <div><?=$_provision;?></div>
                        </div>
                    </div>
                </div>

                <div class="thkc_menu">
                    <div class="thkc_dfc03">
                        <div class="flex-box">
                            <label for="agree2" class="thkc_blind">개인정보 취급 방침 동의</label>
                            <input id="agree2" name="agree2" value="a2_Y" type="checkbox" />
                            <span class="thkc_agree_title_02">[필수] 개인정보 취급 방침 동의</span>
                        </div>
                        <div class="thkc_dfc btn_con"> <a href="#none" class="text_line pl-30">내용보기 </a><img
                                src="<?=G5_IMG_URL?>/new_common/thkc_ico_arrow_next.svg" alt=""></div>

                    </div>
                    <!-- 내용 개인정보취급-->
                    <div class="thkc_iner_cont">
                        <div action="" class="agreeForm">
                            <div><?=$_privacy;?></div>
                        </div>
                    </div>
                </div>

                <div class="thkc_menu">
                    <div class="thkc_dfc03">
                        <div class="flex-box">
                            <label for="agree3" class="thkc_blind">개인정보 취급 방침 동의</label>
                            <input id="agree3" name="agree3" value="a3_Y" type="checkbox" /><label class="aa"></label>
                            <span class="thkc_agree_title_02">[필수] 개인정보 3자 제공동의</span>
                        </div>
                        <div class="thkc_dfc btn_con"> <a href="#none" class="text_line pl-30">내용보기 </a><img
                                src="<?=G5_IMG_URL?>/new_common/thkc_ico_arrow_next.svg" alt=""></div>
                    </div>
                    <!-- 내용 3자 제공-->
                    <div class="thkc_iner_cont">
                        <div action="" class="agreeForm">
                            <div>
                                목적 : 이로움 서비스에 대한 외부 서비스 제공<br>
                                항목 : 전자문서 관리, 요양정보 정보조회 등<br>
                                보유기간 : 회원탈퇴 시 까지
                            </div>
                    </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="thkc_btnWrap thkc_mtb_01">
            <a href="javascript:void(0);"><button class="btn_submit_01" type="button" accesskey="s" onclick="fregisterform_submit();">동의하고 회원신청</button></a><br>
            <a href="<?=G5_BBS_URL?>/register.php" class="text_under">뒤로이동</a>
        </div>

    </section>
    </form>

    <script>
        //아이디 체크
        var mb_id_check_timer = null;
        $('#reg_mb_id').on('keyup change input', function() {

            if(mb_id_check_timer)
            clearTimeout(mb_id_check_timer);

            var $this = $(this);
            
            mb_id_check_timer = setTimeout(function() {
            if($this.val().length < 3) {
                $('.errorID').html("아이디(은)는 3자 이상 입력하셔야합니다.");
                $('.errorID').css( "color", "#d44747" );
                return false;
            }
            var msg = reg_mb_id_check();
            if(msg) {
                $('.errorID').html("사용 불가능한 아이디 입니다.");
                $('.errorID').css( "color", "#d44747" );
            } else {
                $('.errorID').html("사용 가능한 아이디 입니다.");
                $('.errorID').css( "color", "#4788d4" );
            }
            }, 250);
        });

        //비밀번호 체크
        var mb_pw_check_timer = null;
        $('#reg_mb_password').on('keyup change input blur', function() {

            if(mb_pw_check_timer)
            clearTimeout(mb_pw_check_timer);
            
            var $this = $(this);

            mb_pw_check_timer = setTimeout(function() {
            var msg = check_pw($this.val());
            if(msg) {
                $('.errorPW').html(msg);
                $('.errorPW').css( "color", "#d44747" );
            } else {
                $('.errorPW').html("등록 가능한 비밀번호입니다.");
                $('.errorPW').css( "color", "#4788d4" );
            }
            }, 250);
        });
        
        //비밀번호 확인 체크
        var mb_pw_re_check_timer = null;
        $('#reg_mb_password_re').on('keyup change input blur', function() {

            if(mb_pw_re_check_timer)
            clearTimeout(mb_pw_re_check_timer);
            
            var $this = $(this);

            mb_pw_re_check_timer = setTimeout(function() {
            if($this.val() && $this.val() == $('#reg_mb_password').val()) {
                $('.errorPWck').html("동일하게 입력하셨습니다.");
                $('.errorPWck').css( "color", "#4788d4" );
            } else {
                if( $this.val() && $this.val() != $('#reg_mb_password').val()) {
                    $('.errorPWck').html("비밀번호가 일치하지 않습니다.");
                    $('.errorPWck').css( "color", "#d44747" );
                } else {
                    $('.errorPWck').html("");
                }
            }
            }, 250);
        });

        //이메일 체크
        var mb_email_check_timer = null;
        $('#reg_mb_email').on('keyup change input', function() {

            if(mb_email_check_timer)
            clearTimeout(mb_email_check_timer);

            var $this = $(this);
            
            mb_email_check_timer = setTimeout(function() {
            var msg = reg_mb_email_check();
            if(msg) {
                $('.errorMAIL').html(msg);
                $('.errorMAIL').css( "color", "#d44747" );
            } else {
                $('.errorMAIL').html("사용 가능한 이메일 입니다.");
                $('.errorMAIL').css( "color", "#4788d4" );
            }
            }, 500);
        });

        // 사업자번호 입력 유효성 체크
        var mb_bnum_check_timer = null;
        $('#mb_giup_bnum').on('keyup change input blur', function() {

            /* 23.05.23 - 사업자번호 하이픈 추가 */
            $(this).val( auto_saup_hypen( $(this).val() ) );

            var _ck = checkCorporateRegiNumber( $(this).val() );
            if( !_ck ){
                $('.errorBNUM').html("사업자번호를 정확하게 입력해주세요.");
                $('.errorBNUM').css( "color", "#d44747" );
                return;
            } else  {
                $('.errorBNUM').html("");
            }

            mb_bnum_check_timer = setTimeout(function() {
            var msg = reg_mb_giup_bnum_check();
            if(msg) {
                $('.errorBNUM').html(msg);
                $('.errorBNUM').css( "color", "#d44747" );
            } else {
                $('.errorBNUM').html("가입 가능한 사업자번호 입니다.");
                $('.errorBNUM').css( "color", "#4788d4" );
            }
            }, 500);
            

        });

        // 기본배송지로 설정 체크박스
        $('#mb_address_same').on('click', function() {
            var _ck = $("#mb_address_same:checked").val();            
            if( _ck == "Copy" ) {
                $("input[name='mb_zip']").val( $("input[name='mb_giup_zip']").val() );
                $("input[name='mb_addr1']").val( $("input[name='mb_giup_addr1']").val() );
                $("input[name='mb_addr2']").val( $("input[name='mb_giup_addr2']").val() );
            } else {
                $("input[name='mb_zip']").val("");
                $("input[name='mb_addr1']").val("");
                $("input[name='mb_addr2']").val("");
            }
        });

        // 전체약관 동의 체크 박스
        $('#mb_ent_num_ck').on('click', function() {
            var _ck = $("#mb_ent_num_ck:checked").val(); 
            if( _ck == "N" ) { $('#mb_ent_num').val(''); }
        });

        // 전체약관 동의 체크 박스
        $('#allAgree').on('click', function() {
            var _ck = $("#allAgree:checked").val(); 
            if( _ck == "Y" ) {
                $('#agree1, #agree2, #agree3').prop('checked',true);
            } else {
                $('#agree1, #agree2, #agree3').prop('checked',false);
            }
        });

        // 숫자만 입력!!
        $('.numOnly').on('keyup', function() {
            var num = $(this).val();
            num.trim();
            this.value = only_num(num) ;
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

        
        // submit 최종 폼체크
        function fregisterform_submit() {
            setTimeout(function() { $("body").removeClass("stop-scroll"); }, 500);

            // 담당자명 입력 여부 검사.
            if( !$("input[name='mb_giup_manager_name']").val() ) {
                error_MSG( 'errorMGRNM', '담당자 이름을 입력해주세요.', 'mb_giup_manager_name' );
                return false;
            }

            // 회원아이디 검사
            var msg = reg_mb_id_check();
            if (msg) {
                error_MSG( 'errorID', msg, 'mb_id' );
                return false;
            }

            // 비밀번호 검사
            if( $("input[name='mb_password']").val().length < 6 || $("input[name='mb_password']").val().length > 12) {
                error_MSG( 'errorPW', '영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.', 'mb_password' );
                return false;
            }

            // 비밀번호 확인 검사
            if( $("input[name='mb_password_re']").val().length < 6 || $("input[name='mb_password_re']").val().length > 12) {
                error_MSG( 'errorPWck', '영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.', 'mb_password_re' );
                return false;
            }

            // 비밀번호 공백 여부 검사
            if( $("input[name='mb_password']").val().search(/\s/) != -1) {                
                error_MSG( 'errorPW', '비밀번호는 공백 없이 입력해주세요.', 'mb_password' );
                return false;
            }

            // 비밀번호 동일 여부 검증
            if( $("input[name='mb_password']").val() != $("input[name='mb_password_re']").val() ) {                
                error_MSG( 'errorPWck', '비밀번호가 같지 않습니다.', 'mb_password_re' );
                return false;
            }

            // 비밀번호 영문+숫자 혼합 입력 확인 검증
            var num = $("input[name='mb_password']").val().search(/[0-9]/g);
            var eng = $("input[name='mb_password']").val().search(/[a-z]/ig);
            if(num < 0 || eng < 0 ){                
                error_MSG( 'errorPW', '비밀번호는 영문,숫자를 혼합하여 입력해주세요.', 'mb_password' );
                return false;
            }
            
            // 메일 주소검증
            var msg = reg_mb_email_check();
            if(msg) {               
                error_MSG( 'errorMAIL', msg, 'mb_email' );
                return false;
            }

            // 담당자 휴대전화 번호 입력
            if( !$("input[name='mb_hp2']").val() ) {                
                error_MSG( 'errorPW', '휴대전화 번호를 입력하세요.', 'mb_hp2' );
                return false;
            } else if( !$("input[name='mb_hp3']").val() ) {
                error_MSG( 'errorPW', '휴대전화 번호를 입력하세요.', 'mb_hp3' );
                return false;
            }

            // 사업자등록번호 입력 검증
            if( !$("input[name='mb_giup_bnum']").val() ) {                
                error_MSG( 'errorBNUM', '사업자등록번호를 입력하세요.', 'mb_giup_bnum' );
                return false;
            } else if( $("input[name='mb_giup_bnum']").val() ){
                var _ck = checkCorporateRegiNumber( $("input[name='mb_giup_bnum']").val() );
                if( !_ck ){                   
                    error_MSG( 'errorBNUM', '사업자등록번호를 정확하게 입력해주세요.', 'mb_giup_bnum' );
                    return false;
                }

                var msg = reg_mb_giup_bnum_check();
                if(msg) {
                    error_MSG( 'errorBNUM', msg, 'mb_giup_bnum' );                    
                    return false;
                }  
            }
  

            // 장기요양기관번호 입력 검증
            var _ck = $("#mb_ent_num_ck:checked").val();
            if( (!$("input[name='mb_ent_num']").val() && ( _ck != "N" )) && ($("input[name='mb_ent_num']").val().length < 10) ) {                 
                error_MSG( 'errorBNUM', '장기요양기관번호를 입력하세요.', 'mb_ent_num' );
                return false;
            }

            // 상호명 입력 검증
            if( !$("input[name='mb_giup_bname']").val() ){
                error_MSG( 'errorBNAME', '상호명을 입력하세요.', 'mb_giup_bname' );
                return false;
            }
            
            // 대표자명 입력 검증
            if( !$("input[name='mb_giup_boss_name']").val() ){
                error_MSG( 'errorBOSS', '대표자명을 입력하세요.', 'mb_giup_boss_name' );
                return false;
            }
            
            // 업태 입력 검증
            if( !$("input[name='mb_giup_buptae']").val() ){
                error_MSG( 'errorUPTAE', '업태를 입력하세요.', 'mb_giup_buptae' );
                return false;
            }
            
            // 종목 입력 검증
            if( !$("input[name='mb_giup_bupjong']").val() ){
                error_MSG( 'errorUPJONG', '종목을 입력하세요.', 'mb_giup_bupjong' );
                return false;
            }

            // 사업소 전화번호 입력
            if( !$("input[name='mb_tel2']").val() ) {
                error_MSG( 'errorTEL', '사업소 번호를 입력하세요.', 'mb_tel2' );
                return false;
            } else if( !$("input[name='mb_tel3']").val() ) {
                error_MSG( 'errorTEL', '사업소 번호를 입력하세요.', 'mb_tel3' );
                return false;
            }

            // 사업소 주소 입력
            if( !$("input[name='mb_giup_zip']").val() || !$("input[name='mb_giup_addr1']").val() || !$("input[name='mb_giup_addr2']").val()) {
                error_MSG( 'errorBADDR', '사업소 주소를 입력하세요.', 'mb_tel2' );
                return false;
            }
            
            // 배송지 주소 입력
            if( !$("input[name='mb_zip']").val() || !$("input[name='mb_addr1']").val() || !$("input[name='mb_addr2']").val()) {
                error_MSG( 'errorADDR', '배송지 주소를 입력하세요.', 'mb_tel2' );
                return false;
            } 

            //사업자등록증
            var imgFileItem1 = $("#mb_giup_file1");
            if( !$("#mb_giup_file1").val() || !$("#mb_giup_file1")[0].files ) {
                error_MSG( 'errorFILE1', '사업자등록증을 첨부해주세요.', 'crnFile' );
                return false; 
            } else {
                if( $("#mb_giup_file1").val() ) {
                    var ext = $("#mb_giup_file1").val().split(".").pop().toLowerCase();		    
                    if($.inArray(ext, ["jpg", "jpeg", "png", "gif", "bmp", "pdf"]) == -1) {
                        error_MSG( 'errorFILE1', '첨부파일은 이미지 파일만 등록 가능합니다.', 'crnFile' );
                        $("#mb_giup_file1").val("");
                        return false;
                    }

                    var maxSize = 10 * 1024 * 1024; // 10MB
                    var fileSize = $("#mb_giup_file1")[0].files[0].size;
                    if(fileSize > maxSize){
                        error_MSG( 'errorFILE1', '첨부파일 사이즈는 10MB 이내로 등록 가능합니다.', 'crnFile' );
                        $("#mb_giup_file1").val("");
                        return false;
                    }
                }

            }

                
            $('#agree1, #agree2, #agree3').prop('checked',true);

            if(confirm("회원가입 하시겠습니까?")) {
                $("#mb_zip, #mb_addr1").attr("disabled", false); 
                $("#mb_giup_zip, #mb_giup_addr1").attr("disabled", false); 

                var f = document.getElementById("fregisterform");
                f.submit();                
            }
        
            return false;
        }     
        
        // 입력 필드 에러로 인한 메시지 출력 및 화면 이동
        function error_MSG( error_Class, error_TXT, error_NAME ) {
            $('.'+error_Class).html(error_TXT);
            $('.'+error_Class).css( "color", "#d44747" );
            $("input[name='"+error_NAME+"']").focus();
            $('html, body').animate({ scrollTop: ($("input[name='"+error_NAME+"']").offset().top-100) }, 'slow');
        }
    </script>