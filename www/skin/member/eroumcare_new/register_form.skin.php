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
                        <input class="thkc_input numOnly" id="mb_giup_bnum" name="mb_giup_bnum" placeholder="숫자만 입력 " maxlength="10" value="" type="text"  autocomplete="off" />
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
                            
                        <div>
                                <h4>전자상거래(인터넷사이버몰) 표준약관</h4><br>

                                <h4>제1조(목적)</h4>

                                <p>이 약관은 (주)티에이치케이컴퍼니 회사(전자상거래 사업자)가 운영하는 이로움 몰(이하 “몰”이라 한다)에서 제공하는 인터넷 관련 서비스(이하 “서비스”라
                                    한다)를 이용함에 있어 사이버 몰과 이용자의 권리·의무 및 책임사항을 규정함을 목적으로 합니다.

                                    ※「PC통신, 무선 등을 이용하는 전자상거래에 대해서도 그 성질에 반하지 않는 한 이 약관을 준용합니다.」</p><br>

                                <h4>제2조(정의)</h4>

                                <p>① “몰”이란 (주)티에이치케이컴퍼니 회사가 재화 또는 용역(이하 “재화 등” 이라 함)을 이용자에게 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여
                                    재화 등을 거래할 수 있도록 설정한 가상의 영업장을 말하며, 아울러 사이버몰을 운영하는 사업자의 의미로도 사용합니다.<br>

                                    ② “이용자”란 “몰”에 접속하여 이 약관에 따라 “몰”이 제공하는 서비스를 받는 회원 및 비회원을 말합니다.<br>

                                    ③ ‘회원’이라 함은 “몰”에 회원등록을 한 자로서, 계속적으로 “몰”이 제공하는 서비스를 이용할 수 있는 자를 말합니다.<br>

                                    ④ ‘비회원’이라 함은 회원에 가입하지 않고 “몰”이 제공하는 서비스를 이용하는 자를 말합니다.</p><br>

                                <h4>제3조 (약관 등의 명시와 설명 및 개정)</h4>

                                <p>① “몰”은 이 약관의 내용과 상호 및 대표자 성명, 영업소 소재지 주소(소비자의 불만을 처리할 수 있는 곳의 주소를 포함),
                                    전화번호·모사전송번호·전자우편주소, 사업자등록번호, 통신판매업 신고번호, 개인정보관리책임자 등을 이용자가 쉽게 알 수 있도록 (주)티에이치케이컴퍼니
                                    사이버몰의 초기 서비스화면(전면)에 게시합니다. 다만, 약관의 내용은 이용자가 연결화면을 통하여 볼 수 있도록 할 수 있습니다.</p>

                                <p>② “몰은 이용자가 약관에 동의하기에 앞서 약관에 정하여져 있는 내용 중 청약철회·배송책임·환불조건 등과 같은 중요한 내용을 이용자가 이해할 수 있도록
                                    별도의 연결화면 또는 팝업화면 등을 제공하여 이용자의 확인을 구하여야 합니다.</p>

                                <p>③ “몰”은 「전자상거래 등에서의 소비자보호에 관한 법률」, 「약관의 규제에 관한 법률」, 「전자문서 및 전자거래기본법」, 「전자금융거래법」,
                                    「전자서명법」, 「정보통신망 이용촉진 및 정보보호 등에 관한 법률」, 「방문판매 등에 관한 법률」, 「소비자기본법」 등 관련 법을 위배하지 않는
                                    범위에서 이 약관을 개정할 수 있습니다.</p>

                                <p>④ “몰”이 약관을 개정할 경우에는 적용일자 및 개정사유를 명시하여 현행약관과 함께 몰의 초기화면에 그 적용일자 7일 이전부터 적용일자 전일까지
                                    공지합니다.
                                    다만, 이용자에게 불리하게 약관내용을 변경하는 경우에는 최소한 30일 이상의 사전 유예기간을 두고 공지합니다. 이 경우 "몰“은 개정 전 내용과 개정
                                    후 내용을 명확하게 비교하여 이용자가 알기 쉽도록 표시합니다.</p>

                                <p>⑤ “몰”이 약관을 개정할 경우에는 그 개정약관은 그 적용일자 이후에 체결되는 계약에만 적용되고 그 이전에 이미 체결된 계약에 대해서는 개정 전의
                                    약관조항이 그대로 적용됩니다. 다만 이미 계약을 체결한 이용자가 개정약관 조항의 적용을 받기를 원하는 뜻을 제3항에 의한 개정약관의 공지기간 내에
                                    “몰”에 송신하여 “몰”의 동의를 받은 경우에는 개정약관 조항이 적용됩니다.</p>

                                <p>⑥ 이 약관에서 정하지 아니한 사항과 이 약관의 해석에 관하여는 전자상거래 등에서의 소비자보호에 관한 법률, 약관의 규제 등에 관한 법률,
                                    공정거래위원회가 정하는 「전자상거래 등에서의 소비자 보호지침」 및 관계법령 또는 상관례에 따릅니다.</p>
                                <br>
                                <h4>제4조(서비스의 제공 및 변경)</h4>

                                <p>① “몰”은 다음과 같은 업무를 수행합니다.</p>

                                <p>1. 재화 또는 용역에 대한 정보 제공 및 구매계약의 체결<br>
                                    2. 구매계약이 체결된 재화 또는 용역의 배송br>
                                    3. 기타 “몰”이 정하는 업무</p>

                                <p>② “몰”은 재화 또는 용역의 품절 또는 기술적 사양의 변경 등의 경우에는 장차 체결되는 계약에 의해 제공할 재화 또는 용역의 내용을 변경할 수
                                    있습니다. 이 경우에는 변경된 재화 또는 용역의 내용 및 제공일자를 명시하여 현재의 재화 또는 용역의 내용을 게시한 곳에 즉시 공지합니다.<br>

                                    ③ “몰”이 제공하기로 이용자와 계약을 체결한 서비스의 내용을 재화 등의 품절 또는 기술적 사양의 변경 등의 사유로 변경할 경우에는 그 사유를
                                    이용자에게 통지 가능한 주소로 즉시 통지합니다.<br>

                                    ④ 전항의 경우 “몰”은 이로 인하여 이용자가 입은 손해를 배상합니다. 다만, “몰”이 고의 또는 과실이 없음을 입증하는 경우에는 그러하지
                                    아니합니다.<br>
                                </p> <br>
                                <h4>제5조(서비스의 중단)</h4>

                                <p>① “몰”은 컴퓨터 등 정보통신설비의 보수점검·교체 및 고장, 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수
                                    있습니다.<br>

                                    ② “몰”은 제1항의 사유로 서비스의 제공이 일시적으로 중단됨으로 인하여 이용자 또는 제3자가 입은 손해에 대하여 배상합니다. 단, “몰”이 고의 또는
                                    과실이 없음을 입증하는 경우에는 그러하지 아니합니다.<br>

                                    ③ 사업종목의 전환, 사업의 포기, 업체 간의 통합 등의 이유로 서비스를 제공할 수 없게 되는 경우에는 “몰”은 제8조에 정한 방법으로 이용자에게
                                    통지하고 당초 “몰”에서 제시한 조건에 따라 소비자에게 보상합니다. 다만, “몰”이 보상기준 등을 고지하지 아니한 경우에는 이용자들의 마일리지 또는
                                    적립금 등을 “몰”에서 통용되는 통화가치에 상응하는 현물 또는 현금으로 이용자에게 지급합니다.<br>
                                </p>
                                <br>
                                <h4>제6조(회원가입)</h4>

                                <p>① 이용자는 “몰”이 정한 가입 양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다.<br>

                                    ② “몰”은 제1항과 같이 회원으로 가입할 것을 신청한 이용자 중 다음 각 호에 해당하지 않는 한 회원으로 등록합니다.</p>

                                <p>1. 가입신청자가 이 약관 제7조제3항에 의하여 이전에 회원자격을 상실한 적이 있는 경우, 다만 제7조제3항에 의한 회원자격 상실 후 3년이 경과한
                                    자로서 “몰”의 회원재가입 승낙을 얻은 경우에는 예외로 한다.<br>
                                    2. 등록 내용에 허위, 기재누락, 오기가 있는 경우<br>
                                    3. 기타 회원으로 등록하는 것이 “몰”의 기술상 현저히 지장이 있다고 판단되는 경우<br></p>

                                <p>③ 회원가입계약의 성립 시기는 “몰”의 승낙이 회원에게 도달한 시점으로 합니다.<br>

                                    ④ 회원은 회원가입 시 등록한 사항에 변경이 있는 경우, 상당한 기간 이내에 “몰”에 대하여 회원정보 수정 등의 방법으로 그 변경사항을 알려야 합니다.
                                </p>
                                <br>
                                <h4>제7조(회원 탈퇴 및 자격 상실 등)</h4>

                                <p>① 회원은 “몰”에 언제든지 탈퇴를 요청할 수 있으며 “몰”은 즉시 회원탈퇴를 처리합니다.<br>

                                    ② 회원이 다음 각 호의 사유에 해당하는 경우, “몰”은 회원자격을 제한 및 정지시킬 수 있습니다.<br>

                                    1. 가입 신청 시에 허위 내용을 등록한 경우<br>
                                    2. “몰”을 이용하여 구입한 재화 등의 대금, 기타 “몰”이용에 관련하여 회원이 부담하는 채무를 기일에 지급하지 않는 경우<br>
                                    3. 다른 사람의 “몰” 이용을 방해하거나 그 정보를 도용하는 등 전자상거래 질서를 위협하는 경우<br>
                                    4. “몰”을 이용하여 법령 또는 이 약관이 금지하거나 공서양속에 반하는 행위를 하는 경우<br>

                                    ③ “몰”이 회원 자격을 제한·정지 시킨 후, 동일한 행위가 2회 이상 반복되거나 30일 이내에 그 사유가 시정되지 아니하는 경우 “몰”은 회원자격을
                                    상실시킬 수 있습니다.<br>

                                    ④ “몰”이 회원자격을 상실시키는 경우에는 회원등록을 말소합니다. 이 경우 회원에게 이를 통지하고, 회원등록 말소 전에 최소한 30일 이상의 기간을
                                    정하여 소명할 기회를 부여합니다.</p>
                                <br>
                                <h4>제8조(회원에 대한 통지)</h4>

                                <p>① “몰”이 회원에 대한 통지를 하는 경우, 회원이 “몰”과 미리 약정하여 지정한 전자우편 주소로 할 수 있습니다.<br>

                                    ② “몰”은 불특정다수 회원에 대한 통지의 경우 1주일이상 “몰” 게시판에 게시함으로서 개별 통지에 갈음할 수 있습니다. 다만, 회원 본인의 거래와
                                    관련하여 중대한 영향을 미치는 사항에 대하여는 개별통지를 합니다.</p>
                                <br>
                                <h4>제9조(구매신청)</h4>

                                <p>① “몰”이용자는 “몰”상에서 다음 또는 이와 유사한 방법에 의하여 구매를 신청하며, “몰”은 이용자가 구매신청을 함에 있어서 다음의 각 내용을 알기
                                    쉽게 제공하여야 합니다.<br><br>

                                    1. 재화등의 검색 및 선택<br>
                                    2. 성명, 주소, 전화번호, 전자우편주소(또는 이동전화번호) 등의 입력<br>
                                    3. 약관내용, 청약철회권이 제한되는 서비스, 배송료·설치비 등의 비용부담과 관련한 내용에 대한 확인<br>
                                    4. 이 약관에 동의하고 위 3.호의 사항을 확인하거나 거부하는 표시<br>
                                    5. 재화등의 구매신청 및 이에 관한 확인 또는 “몰”의 확인에 대한 동의<br>
                                    6. 결제방법의 선택<br><br>

                                    ② “몰”이 제3자에게 구매자 개인정보를 제공·위탁할 필요가 있는 경우 실제 구매신청 시 구매자의 동의를 받아야 하며, 회원가입 시 미리 포괄적으로
                                    동의를 받지 않습니다. 이 때 “몰”은 제공되는 개인정보 항목, 제공받는 자, 제공받는 자의 개인정보 이용 목적 및 보유·이용 기간 등을 구매자에게
                                    명시하여야 합니다.
                                    다만 「정보통신망이용촉진 및 정보보호 등에 관한 법률」 제25조 제1항에 의한 개인정보 취급위탁의 경우 등 관련 법령에 달리 정함이 있는 경우에는 그에
                                    따릅니다.</p>
                                <br>
                                <h4>제10조 (계약의 성립)</h4>

                                <p>① “몰”은 제9조와 같은 구매신청에 대하여 다음 각 호에 해당하면 승낙하지 않을 수 있습니다. 다만, 미성년자와 계약을 체결하는 경우에는 법정대리인의
                                    동의를 얻지 못하면 미성년자 본인 또는 법정대리인이 계약을 취소할 수 있다는 내용을 고지하여야 합니다.<br>

                                    1. 신청 내용에 허위, 기재누락, 오기가 있는 경우<br>
                                    2. 미성년자가 담배, 주류 등 청소년보호법에서 금지하는 재화 및 용역을 구매하는 경우<br>
                                    3. 기타 구매신청에 승낙하는 것이 “몰” 기술상 현저히 지장이 있다고 판단하는 경우<br>

                                    ② “몰”의 승낙이 제12조제1항의 수신확인통지형태로 이용자에게 도달한 시점에 계약이 성립한 것으로 봅니다.<br>

                                    ③ “몰”의 승낙의 의사표시에는 이용자의 구매 신청에 대한 확인 및 판매가능 여부, 구매신청의 정정 취소 등에 관한 정보 등을 포함하여야 합니다.
                                </p>
                                <br>
                                <h4>제11조(지급방법)</h4>
                                <p>“몰”에서 구매한 재화 또는 용역에 대한 대금지급방법은 다음 각 호의 방법중 가용한 방법으로 할 수 있습니다. 단, “몰”은 이용자의 지급방법에 대하여
                                    재화 등의 대금에 어떠한 명목의 수수료도 추가하여 징수할 수 없습니다.
                                    <br><br>
                                    1. 폰뱅킹, 인터넷뱅킹, 메일 뱅킹 등의 각종 계좌이체<br>
                                    2. 선불카드, 직불카드, 신용카드 등의 각종 카드 결제<br>
                                    3. 온라인무통장입금<br>
                                    4. 전자화폐에 의한 결제<br>
                                    5. 수령 시 대금지급<br>
                                    6. 마일리지 등 “몰”이 지급한 포인트에 의한 결제<br>
                                    7. “몰”과 계약을 맺었거나 “몰”이 인정한 상품권에 의한 결제<br>
                                    8. 기타 전자적 지급 방법에 의한 대금 지급 등
                                </p>
                                <br>
                                <h4>제12조(수신확인통지·구매신청 변경 및 취소)</h4>

                                <p>① “몰”은 이용자의 구매신청이 있는 경우 이용자에게 수신확인통지를 합니다.<br>

                                    ② 수신확인통지를 받은 이용자는 의사표시의 불일치 등이 있는 경우에는 수신확인통지를 받은 후 즉시 구매신청 변경 및 취소를 요청할 수 있고 “몰”은
                                    배송 전에 이용자의 요청이 있는 경우에는 지체 없이 그 요청에 따라 처리하여야 합니다. 다만 이미 대금을 지불한 경우에는 제15조의 청약철회 등에 관한
                                    규정에 따릅니다.
                                </p>
                                <br>
                                <h4>제13조(재화 등의 공급)</h4>

                                <p>① “몰”은 이용자와 재화 등의 공급시기에 관하여 별도의 약정이 없는 이상, 이용자가 청약을 한 날부터 7일 이내에 재화 등을 배송할 수 있도록
                                    주문제작, 포장 등 기타의 필요한 조치를 취합니다. 다만, “몰”이 이미 재화 등의 대금의 전부 또는 일부를 받은 경우에는 대금의 전부 또는 일부를
                                    받은 날부터 3영업일 이내에 조치를 취합니다. 이때 “몰”은 이용자가 재화 등의 공급 절차 및 진행 사항을 확인할 수 있도록 적절한 조치를
                                    합니다.<br>

                                    ② “몰”은 이용자가 구매한 재화에 대해 배송수단, 수단별 배송비용 부담자, 수단별 배송기간 등을 명시합니다. 만약 “몰”이 약정 배송기간을 초과한
                                    경우에는 그로 인한 이용자의 손해를 배상하여야 합니다. 다만 “몰”이 고의·과실이 없음을 입증한 경우에는 그러하지 아니합니다.<br>
                                </p><br>
                                <h4>제14조(환급)</h4>
                                <p>“몰”은 이용자가 구매신청한 재화 등이 품절 등의 사유로 인도 또는 제공을 할 수 없을 때에는 지체 없이 그 사유를 이용자에게 통지하고 사전에 재화 등의
                                    대금을 받은 경우에는 대금을 받은 날부터 3영업일 이내에 환급하거나 환급에 필요한 조치를 취합니다.<br>
                                </p><br>
                                <h4>제15조(청약철회 등)</h4>

                                <p> ① “몰”과 재화등의 구매에 관한 계약을 체결한 이용자는 「전자상거래 등에서의 소비자보호에 관한 법률」 제13조 제2항에 따른 계약내용에 관한 서면을
                                    받은 날(그 서면을 받은 때보다 재화 등의 공급이 늦게 이루어진 경우에는 재화 등을 공급받거나 재화 등의 공급이 시작된 날을 말합니다)부터 7일
                                    이내에는 청약의 철회를 할 수 있습니다.<br>
                                    다만, 청약철회에 관하여 「전자상거래 등에서의 소비자보호에 관한 법률」에 달리 정함이 있는 경우에는 동 법 규정에 따릅니다.<br>

                                    ② 이용자는 재화 등을 배송 받은 경우 다음 각 호의 1에 해당하는 경우에는 반품 및 교환을 할 수 없습니다.

                                    1. 이용자에게 책임 있는 사유로 재화 등이 멸실 또는 훼손된 경우(다만, 재화 등의 내용을 확인하기 위하여 포장 등을 훼손한 경우에는 청약철회를 할
                                    수 있습니다)<br>
                                    2. 이용자의 사용 또는 일부 소비에 의하여 재화 등의 가치가 현저히 감소한 경우<br>
                                    3. 시간의 경과에 의하여 재판매가 곤란할 정도로 재화등의 가치가 현저히 감소한 경우<br>
                                    4. 같은 성능을 지닌 재화 등으로 복제가 가능한 경우 그 원본인 재화 등의 포장을 훼손한 경우<br>

                                    ③ 제2항제2호 내지 제4호의 경우에 “몰”이 사전에 청약철회 등이 제한되는 사실을 소비자가 쉽게 알 수 있는 곳에 명기하거나 시용상품을 제공하는 등의
                                    조치를 하지 않았다면 이용자의 청약철회 등이 제한되지 않습니다.<br>

                                    ④ 이용자는 제1항 및 제2항의 규정에 불구하고 재화 등의 내용이 표시·광고 내용과 다르거나 계약내용과 다르게 이행된 때에는 당해 재화 등을 공급받은
                                    날부터 3월 이내, 그 사실을 안 날 또는 알 수 있었던 날부터 30일 이내에 청약철회 등을 할 수 있습니다.<br></p>
                                <br>
                                <h4>제16조(청약철회 등의 효과)</h4>

                                <p>① “몰”은 이용자로부터 재화 등을 반환받은 경우 3영업일 이내에 이미 지급받은 재화 등의 대금을 환급합니다. 이 경우 “몰”이 이용자에게 재화등의
                                    환급을 지연한때에는 그 지연기간에 대하여 「전자상거래 등에서의 소비자보호에 관한 법률 시행령」제21조의2에서 정하는 지연이자율을 곱하여 산정한
                                    지연이자를 지급합니다.<br>

                                    ② “몰”은 위 대금을 환급함에 있어서 이용자가 신용카드 또는 전자화폐 등의 결제수단으로 재화 등의 대금을 지급한 때에는 지체 없이 당해 결제수단을
                                    제공한 사업자로 하여금 재화 등의 대금의 청구를 정지 또는 취소하도록 요청합니다.<br>

                                    ③ 청약철회 등의 경우 공급받은 재화 등의 반환에 필요한 비용은 이용자가 부담합니다. “몰”은 이용자에게 청약철회 등을 이유로 위약금 또는 손해배상을
                                    청구하지 않습니다. 다만 재화 등의 내용이 표시·광고 내용과 다르거나 계약내용과 다르게 이행되어 청약철회 등을 하는 경우 재화 등의 반환에 필요한
                                    비용은 “몰”이 부담합니다.<br>

                                    ④ 이용자가 재화 등을 제공받을 때 발송비를 부담한 경우에 “몰”은 청약철회 시 그 비용을 누가 부담하는지를 이용자가 알기 쉽도록 명확하게
                                    표시합니다.<br>
                                </p><br>
                                <h4>제17조(개인정보보호)</h4>

                                <p> ① “몰”은 이용자의 개인정보 수집시 서비스제공을 위하여 필요한 범위에서 최소한의 개인정보를 수집합니다.<br>

                                    ② “몰”은 회원가입시 구매계약이행에 필요한 정보를 미리 수집하지 않습니다. 다만, 관련 법령상 의무이행을 위하여 구매계약 이전에 본인확인이 필요한
                                    경우로서 최소한의 특정 개인정보를 수집하는 경우에는 그러하지 아니합니다.<br>

                                    ③ “몰”은 이용자의 개인정보를 수집·이용하는 때에는 당해 이용자에게 그 목적을 고지하고 동의를 받습니다.<br>

                                    ④ “몰”은 수집된 개인정보를 목적외의 용도로 이용할 수 없으며, 새로운 이용목적이 발생한 경우 또는 제3자에게 제공하는 경우에는 이용·제공단계에서
                                    당해 이용자에게 그 목적을 고지하고 동의를 받습니다. 다만, 관련 법령에 달리 정함이 있는 경우에는 예외로 합니다.<br>

                                    ⑤ “몰”이 제3항과 제4항에 의해 이용자의 동의를 받아야 하는 경우에는 개인정보관리 책임자의 신원(소속, 성명 및 전화번호, 기타 연락처), 정보의
                                    수집목적 및 이용목적, 제3자에 대한 정보제공 관련사항(제공받은자, 제공목적 및 제공할 정보의 내용) 등 「정보통신망 이용촉진 및 정보보호 등에 관한
                                    법률」 제22조제2항이 규정한 사항을 미리 명시하거나 고지해야 하며 이용자는 언제든지 이 동의를 철회할 수 있습니다.<br>

                                    ⑥ 이용자는 언제든지 “몰”이 가지고 있는 자신의 개인정보에 대해 열람 및 오류정정을 요구할 수 있으며 “몰”은 이에 대해 지체 없이 필요한 조치를
                                    취할 의무를 집니다. 이용자가 오류의 정정을 요구한 경우에는 “몰”은 그 오류를 정정할 때까지 당해 개인정보를 이용하지 않습니다.<br>

                                    ⑦ “몰”은 개인정보 보호를 위하여 이용자의 개인정보를 취급하는 자를 최소한으로 제한하여야 하며 신용카드, 은행계좌 등을 포함한 이용자의 개인정보의
                                    분실, 도난, 유출, 동의 없는 제3자 제공, 변조 등으로 인한 이용자의 손해에 대하여 모든 책임을 집니다.<br>

                                    ⑧ “몰” 또는 그로부터 개인정보를 제공받은 제3자는 개인정보의 수집목적 또는 제공받은 목적을 달성한 때에는 당해 개인정보를 지체 없이
                                    파기합니다.<br>

                                    ⑨ “몰”은 개인정보의 수집·이용·제공에 관한 동의란을 미리 선택한 것으로 설정해두지 않습니다. 또한 개인정보의 수집·이용·제공에 관한 이용자의
                                    동의거절시 제한되는 서비스를 구체적으로 명시하고, 필수수집항목이 아닌 개인정보의 수집·이용·제공에 관한 이용자의 동의 거절을 이유로 회원가입 등 서비스
                                    제공을 제한하거나 거절하지 않습니다.<br>
                                </p><br>
                                <h4>제18조(“몰“의 의무)</h4>

                                <p>① “몰”은 법령과 이 약관이 금지하거나 공서양속에 반하는 행위를 하지 않으며 이 약관이 정하는 바에 따라 지속적이고, 안정적으로 재화·용역을 제공하는데
                                    최선을 다하여야 합니다.<br>

                                    ② “몰”은 이용자가 안전하게 인터넷 서비스를 이용할 수 있도록 이용자의 개인정보(신용정보 포함)보호를 위한 보안 시스템을 갖추어야 합니다.<br>

                                    ③ “몰”이 상품이나 용역에 대하여 「표시·광고의 공정화에 관한 법률」 제3조 소정의 부당한 표시·광고행위를 함으로써 이용자가 손해를 입은 때에는 이를
                                    배상할 책임을 집니다.<br>

                                    ④ “몰”은 이용자가 원하지 않는 영리목적의 광고성 전자우편을 발송하지 않습니다.<br>
                                </p><br>
                                <h4> 제19조(회원의 ID 및 비밀번호에 대한 의무)</h4>

                                <p>① 제17조의 경우를 제외한 ID와 비밀번호에 관한 관리책임은 회원에게 있습니다.<br>

                                    ② 회원은 자신의 ID 및 비밀번호를 제3자에게 이용하게 해서는 안됩니다.<br>

                                    ③ 회원이 자신의 ID 및 비밀번호를 도난당하거나 제3자가 사용하고 있음을 인지한 경우에는 바로 “몰”에 통보하고 “몰”의 안내가 있는 경우에는 그에
                                    따라야 합니다.<br>
                                </p><br>
                                <h4>제20조(이용자의 의무)</h4>
                                <p>이용자는 다음 행위를 하여서는 안 됩니다.<br><br>

                                    1. 신청 또는 변경시 허위 내용의 등록<br>
                                    2. 타인의 정보 도용<br>
                                    3. “몰”에 게시된 정보의 변경<br>
                                    4. “몰”이 정한 정보 이외의 정보(컴퓨터 프로그램 등) 등의 송신 또는 게시<br>
                                    5. “몰” 기타 제3자의 저작권 등 지적재산권에 대한 침해<br>
                                    6. “몰” 기타 제3자의 명예를 손상시키거나 업무를 방해하는 행위<br>
                                    7. 외설 또는 폭력적인 메시지, 화상, 음성, 기타 공서양속에 반하는 정보를 몰에 공개 또는 게시하는 행위<br>
                                </p><br>
                                <h4>제21조(연결“몰”과 피연결“몰” 간의 관계)</h4>

                                <p>① 상위 “몰”과 하위 “몰”이 하이퍼링크(예: 하이퍼링크의 대상에는 문자, 그림 및 동화상 등이 포함됨)방식 등으로 연결된 경우, 전자를 연결
                                    “몰”(웹 사이트)이라고 하고 후자를 피연결 “몰”(웹사이트)이라고 합니다.<br>

                                    ② 연결“몰”은 피연결“몰”이 독자적으로 제공하는 재화 등에 의하여 이용자와 행하는 거래에 대해서 보증 책임을 지지 않는다는 뜻을 연결“몰”의 초기화면
                                    또는 연결되는 시점의 팝업화면으로 명시한 경우에는 그 거래에 대한 보증 책임을 지지 않습니다.<br>
                                </p><br>
                                <h4>제22조(저작권의 귀속 및 이용제한)</h4>

                                <p>① “몰“이 작성한 저작물에 대한 저작권 기타 지적재산권은 ”몰“에 귀속합니다.<br>

                                    ② 이용자는 “몰”을 이용함으로써 얻은 정보 중 “몰”에게 지적재산권이 귀속된 정보를 “몰”의 사전 승낙 없이 복제, 송신, 출판, 배포, 방송 기타
                                    방법에 의하여 영리목적으로 이용하거나 제3자에게 이용하게 하여서는 안됩니다.<br>

                                    ③ “몰”은 약정에 따라 이용자에게 귀속된 저작권을 사용하는 경우 당해 이용자에게 통보하여야 합니다.<br>
                                </p><br>
                                <h4>제23조(분쟁해결)</h4>

                                <p>① “몰”은 이용자가 제기하는 정당한 의견이나 불만을 반영하고 그 피해를 보상처리하기 위하여 피해보상처리기구를 설치·운영합니다.<br>

                                    ② “몰”은 이용자로부터 제출되는 불만사항 및 의견은 우선적으로 그 사항을 처리합니다. 다만, 신속한 처리가 곤란한 경우에는 이용자에게 그 사유와
                                    처리일정을 즉시 통보해 드립니다.<br>

                                    ③ “몰”과 이용자 간에 발생한 전자상거래 분쟁과 관련하여 이용자의 피해구제신청이 있는 경우에는 공정거래위원회 또는 시·도지사가 의뢰하는 분쟁조정기관의
                                    조정에 따를 수 있습니다.<br>
                                </p><br>
                                <h4>제24조(재판권 및 준거법)</h4>

                                <p>① “몰”과 이용자 간에 발생한 전자상거래 분쟁에 관한 소송은 제소 당시의 이용자의 주소에 의하고, 주소가 없는 경우에는 거소를 관할하는 지방법원의
                                    전속관할로 합니다. 다만, 제소 당시 이용자의 주소 또는 거소가 분명하지 않거나 외국 거주자의 경우에는 민사소송법상의 관할법원에 제기합니다.<br>

                                    ② “몰”과 이용자 간에 제기된 전자상거래 소송에는 한국법을 적용합니다.<br>
                                </p>
                            </div>
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
                        <div>
                                <h4>(주)티에이치케이컴퍼니 개인정보 취급방침</h4><br>

                                <P>저희 (주)티에이치케이컴퍼니는 고객의 개인정보를 소중하게 생각합니다.</P><br>

                                <P>(주)티에이치케이컴퍼니(이하 ‘회사’)는 복지용구 플랫폼 업무관리프로그램 e-roumcare(이하 '서비스') 이용신청자(이하 '이용자')의 개인정보
                                    관리와 관련해 정보통신망 이용촉진 및 정보보호 등에 관한 법률, 개인정보보호법, 전기통신사업법 등 관련 법령상의 개인정보보호규정 및 방송통신위원회가
                                    제정한 개인정보보호지침을 준수합니다.</P><br>

                                <P>본 개인정보 취급방침은 회사에서 제공하는 서비스에 적용되며, 다음과 같은 내용을 담고 있습니다.</P><br>

                                <P>1. 수집하는 개인정보의 항목 및 수집방법
                                    2. 개인정보 수집 및 이용목적<br>
                                    3. 개인정보 보유 및 이용기간<br>
                                    4. 개인정보 파기 절차 및 방법<br>
                                    5. 이용자 및 법정대리인의 권리와 그 행사방법<br>
                                    6. 개인정보 자동수집장치의 설치/운영 및 거부에 관한 사항<br>
                                    7. 개인정보보호를 위한 기술적/관리적 대책<br>
                                    8. 개인정보관리 책임자<br>
                                    9. 고지의 의무</P><br>

                                <P>1. 수집하는 개인정보의 항목 및 수집방법</P><br>
                                <P>가. 수집하는 개인정보의 항목</P>
                                <P>회사는 원활한 고객상담 및 서비스 제공을 위해 서비스 이용신청 시 아래와 같은 최소한의 개인정보를 필수항목으로 수집하고 있습니다.<br>
                                    - 신청자 정보 : 이름, 연락처, 이메일<br>
                                    - 로그인 정보 : 아이디, 패스워드(암호화), 아이피<br>
                                    - 사업자 정보 : 사업자(상호)명, 사업자번호, 대표자명, 주소, 사업종류, 개업일, 계좌번호</P><br>

                                <P>나. 회사는 다음과 같은 방법으로 개인정보를 수집합니다.</P>
                                <P>- 홈페이지, 서면양식, 팩스, 전화, 고객 상담</P><br>

                                <P>다. 개인정보 수집에 대한 동의</P>
                                <P>회사는 이용자의 개인정보 수집에 대한 동의를 받고 있습니다. 이용자의 개인정보 수집과 관련해 서비스이용신청 시 이용약관에 대하여 ‘동의합니다’ 버튼을
                                    클릭함으로써 동의한 것으로 간주합니다. 단, 아래와 같은 경우엔 이용자의 사전 동의 없이 개인정보를 수집할 수 있습니다.</P>
                                <P>- 약관에 근거한 서비스제공을 위해 필요한 경우
                                    - 유료서비스의 요금정산을 위하여 필요한 경우
                                    - 다른 법령에 특별한 규정이 있는 경우</P><br>

                                <P>라. 개인정보의 공유 및 제공</P>
                                <P>회사는 이용자의 개인정보를 “2. 개인정보 수집 및 이용목적” 고지한 범위 내에서 사용하며, 이용자의 사전 동의 없이는 동 범위를 초과하여 이용하지
                                    않으며, 회사는 원칙적으로 이용자의 개인정보를 제3자 등 외부에 공개하지 않습니다. 단, 아래의 경우에는 예외로 합니다.</P>
                                <P>- 법령 규정에 의거한경우
                                    - 수사 등의 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</P><br>

                                <P>2. 개인정보 수집 및 이용목적</P><br>
                                <P>회사의 수집한 개인정보는 다음의 목적을 위해 활용합니다. 이용자가 제공한 모든 정보는 아래의 목적 외에는 사용하지 않으며, 이용목적이 변경될 시에는
                                    이용자의 사전 동의를 구합니다.</P>

                                <P>가. 서비스 제공에 관한 계약 이행 및 요금정산
                                    회사를 통한 각종 콘텐츠 및 웹서비스 제공, 이용료 결제, 요금 청구, 금융거래 본인인증 및 금융서비스, 요금 추심 등</P><br>

                                <P>나. 이용자 관리<br>
                                    서비스 이용에 따른 본인확인 및 개인식별, 불량회원의 부정이용방지와 비인가사용방지, 가입의사 확인, 연령확인(14세 미만은 원칙적으로 가입 금지),
                                    불만처리 등 각종 고객사항 처리, 민원전달 등</P><br>

                                <P>다. 신규 서비스 개발 및 마케팅, 광고에의 활용<br>
                                    신규 서비스 개발 및 맞춤서비스 제공, 통계학적 특성에 따른 서비스제공 및 광고 게재, 서비스의 유효성 확인, 접속 빈도 및 구매통계 파악, 회원의
                                    각종 서비스 이용에 관한 분석통계</P><br>


                                <P>3. 개인정보 보유 및 이용기간<br><br>
                                    회사는 이용자의 가입일로부터 서비스를 제공하는 기간 동안 이용자의 개인정보를 보유 및 이용하게 됩니다. 이용자의 개인정보는 원칙적으로 개인정보의 수집
                                    및 이용목적이 달성된 경우, 이용자가 회원 탈퇴를 요구하거나 개인정보 수집 및 이용에 대한 동의를 철회하는 경우 지체 없이 파기합니다.</P>

                                <P>4. 개인정보 파기 절차 및 방법</P><br>
                                <P>가. 개인정보 파기 절차<br>
                                    이용자가 서비스이용 등을 위해 입력한 정보는 목적이 달성된 후 별도의 DB로 옮겨져(종이의 경우 별도의 서류함) 내부방침 및 기타 관련법령에 의한
                                    정보보호 사유에 따라(보유 및 이용기간참조)일정 기간 저장된 후 파기됩니다. 동 개인정보는 관련 법률에 의한 특수한 경우 이외의 다른 목적으로는
                                    이용되지 않습니다.</P><br>
                                <br>
                                <p>나. 파기 방법<br>
                                    - 전자적 파일형태: 재생불가능한 기술적 방법으로 파기 및 파기여부확인 (컴퓨터 등의 불용처분, 매각시 원칙적으로 저장물(HDD, CD, DVD,
                                    USB 등 모든 저장매체)은 물리적으로 사용불가능하게 파기)<br>
                                    - 출력물: 폐·휴지 수집업체 출력물 원형 매각금지, 원형으로 매각할 경우 제지공장 용해작업 현장 확인, 직접 파쇄 조치 후 매각(분쇄 또는 소각처리)
                                </p>
                                <br>
                                <p>5. 이용자 및 법정대리인의 권리와 그 행사방법<br>
                                    - 이용자 및 법정대리인은 언제든지 등록되어 있는 자신의 개인정보를 조회하거나 수정할 수 있으며, 회사의 개인정보 처리에 동의하지 않는 경우 동의를
                                    거부하거나 서비스이용취소(회원탈퇴)를 요청하실 수 있습니다. 다만, 그러한 경우 서비스 일부 또는 전부 이용이 어려울 수 있습니다.<br>
                                    - 이용자의 개인정보 조회 및 수정을 위해서는 ‘기초자료’에서 직접 열람 또는 수정하시거나 회사로 직접 연락주하시어 안내를 받으시기 바랍니다.
                                    개인정보관리책임자에게 전화 또는 서면으로 연락하시면 지체 없이 조치하겠습니다.<br>
                                    - 회사는 이용자 혹은 법정대리인의 요청에 의해 해지 또는 삭제된 개인정보를 “3. 개인정보 보유 및 이용기간”에 명시한 바에 따라 처리하고, 그 외의
                                    용도로 열람 또는 이용할 수 없도록 관리하고 있습니다.<br>
                                </p>
                                <br>
                                <p>6. 개인정보보호를 위한 기술적/관리적 대책
                                    가. 개인정보 암호화
                                    이용자의 개인정보는 비밀번호에 의해 보호되며, 중요한 데이터는 암호화하거나 파일 잠금 기능을 사용하는 등의 별도 보안 기능을 통해 보호하고
                                    있습니다.<br>
                                </p><br>
                                <p>나. 해킹 등에 대한 기술적 대책<br>
                                    해킹이나 바이러스 등에 의해 개인정보가 유출되거나 훼손되는 것을 막기 위해 외부로부터의 접근이 통제된 구역에 시스템을 설치하고 침입차단, 백신프로그램의
                                    주기적인 업데이트를 통해 관리하고 있습니다.<br>
                                </p><br>
                                <p>다. 개인정보처리 시스템 접근 제한<br>
                                    개인정보를 취급할 수 있는 담당자를 한정시켜 접근 권한의 부여, 변경, 말소 등에 관한 기준을 수립하고 비밀번호의 생성방법, 변경 주기 등을 운영하며
                                    필요한 조치를 다하고 있습니다.<br>
                                </p><br>
                                <p>라. 개인 아이디와 비밀번호 관리<br>
                                    이용자가 사용하는 계정은 원칙적으로 이용자만 사용하도록 정해져 있습니다. 회사는 이용자의 개인적 부주의에 의한 개인정보 유출 또는 기본적인 인터넷의
                                    위험성 때문에 발생하는 문제들에 대해서는 책임을 지지 않습니다. 공용PC에서의 로그인 사용 시 주의를 기울이거나 비밀번호를 수시로 변경하는 등
                                    비밀번호에 대한 보안의식을 갖고서 개인정보 유출 방지에 각별한 주의를 기울여주시길 바랍니다.<br>
                                </p><br>
                                <p>7. 개인정보관리 책임자<br>
                                    개인정보관리 책임자 : 신종호<br>
                                    기타 개인정보침해에 대한 신고, 상담 시 아래의 기관을 이용하시기 바랍니다.<br>
                                    - 개인분쟁조정위원회(1336)<br>
                                    - 정보보호 마크인증위원회(02-580-0533)<br>
                                    - 대검찰청 인터넷범죄수사센터(02-3480-3600)<br>
                                    - 경찰청 사이버테러대응센터(02-392-0330)<br>
                                </p><br>
                                <p>8. 고지의 의무<br>
                                    이 개인정보취급방침은 게시 즉시 적용되며, 법령, 정책, 보안기술의 변경에 따라 내용의 추가, 삭제, 수정이 있을 시엔 변경사항의 실행일 7일 이전에
                                    고지할 것입니다.<br>
                                </p>
                            </div>
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
        $('#mb_giup_bnum').on('keyup blur', function() {
            if( $(this).val().length == 10 ) {
                var _ck = checkCorporateRegiNumber( $(this).val() );
                if( !_ck ){
                    $('.errorBNUM').html("사업자등록번호를 정확하게 입력해주세요.");
                    $('.errorBNUM').css( "color", "#d44747" );
                } else  {
                    $('.errorBNUM').html("");
                }
            }
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

        // 사업자번호 유효성 검증
        function checkCorporateRegiNumber(number) {
            var numberMap = number.replace(/-/gi, '').split('').map(function (d) {
                return parseInt(d, 10);
            });

            if(numberMap.length == 10) {
                var keyArr = [1, 3, 7, 1, 3, 7, 1, 3, 5];
                var chk = 0;

                keyArr.forEach(function(d, i) {
                    chk += d * numberMap[i];
                });

                chk += parseInt((keyArr[8] * numberMap[8])/ 10, 10);
                console.log(chk);
                return Math.floor(numberMap[9]) === ( (10 - (chk % 10) ) % 10);
            }

            return false;
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