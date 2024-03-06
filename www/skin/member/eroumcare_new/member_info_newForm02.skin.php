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
	$query = "SHOW COLUMNS FROM g5_member WHERE `Field` = 'manager_auth_order';";//업데이트멤버 없을 시 추가
	$wzres = sql_fetch( $query );
	if(!$wzres['Field']) {
		sql_query("ALTER TABLE `g5_member`
		ADD `manager_auth_order` tinyint(2) NULL DEFAULT '0' COMMENT '직원주문권한' AFTER mb_manager", true);
	}
	/* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 :  \www\skin\member\eroumcare_new\member_info_newForm02.skin.php */
    /* // 파일 설명 : 신규파일 - 회원정보 변경 > 직원계정관리 파일 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
	$query = "SHOW COLUMNS FROM g5_member WHERE `Field` = 'mb_viewType';";//직원 판매가 모드 없을 시 추가
	$wzres = sql_fetch( $query );
	if(!$wzres['Field']) {
		sql_query("ALTER TABLE `g5_member`
		ADD `mb_viewType` tinyint(2) NULL DEFAULT '0' COMMENT '직원 판매가 모드 0:노출,1:비노출 ' AFTER manager_auth_order", true);
	}

    $mm_result = sql_query(" SELECT * FROM g5_member WHERE mb_type = 'manager' AND mb_manager = '{$member['mb_id']}'");


?>
<style type="text/css">
	@media (max-width: 767px){
		.f_s14 {
			font-size: 12px;
		}
	}
</style>
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
						<div class="thkc_btnWrap_03">
                            <button class="on" id='btn_leave1' style="margin-top:15px;">회원탈퇴 신청</button>
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
						<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
						<div class="table-box table-box_02"><div class="tit tit02">사업소판매가 확인 권한</div><div class="thkc_cont thkc_cont02"><div><?=($mm['mb_viewType'] == '0')?"판매가 확인 가능":"판매가 확인 불가";?></div></div></div>
						<div class="table-box table-box_02"><div class="tit tit02">주문권한</div><div class="thkc_cont thkc_cont02"><div><?=($mm['manager_auth_order'] == 0)?"주문불가":"주문가능";?></div></div></div>						
						<?php }?>
                        <div class="thkc_btnWrap_03"><button onclick="manager_del('<?=$mm['mb_no']?>')">삭제</button><button class="on" onclick="manager_modify('<?=$mm['mb_no']?>')">정보수정</button></div>
                    </div>

                    <input type="hidden" id="mb_id" name="" value="<?=$mm['mb_id']?>">
                    <input type="hidden" id="mb_name" name="" value="<?=$mm['mb_name']?>">
                    <input type="hidden" id="mm_tel" name="" value="<?=$mm['mb_tel']?>">
                    <input type="hidden" id="mm_email" name="" value="<?=$mm['mb_email']?>">
                    <input type="hidden" id="mm_memo" name="" value="<?=$mm['mb_memo']?>">
					<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
					<input type="hidden" id="mm_auth_order" name="" value="<?=$mm['manager_auth_order']?>">
					<input type="hidden" id="mm_viewType" name="" value="<?=$mm['mb_viewType']?>">
					<?php }?>

                </div>
                <?php } ?>

                <!-- 버튼 -->
                <div class="thkc_btnWrap thkc_mtb_01">
                    <button class="btn_submit_02">직원 신규 등록 +</button><br>
                </div>

                <!-- 회원정보 직원계정 추가 팝업 -->
                <div class="thkc_popUpWrap" id="member_add">
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
								<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
								<!-- 판매가 노출  -->
                                <div class="table-box table-box_02" style="border-bottom: 0px;">
                                    <div class="tit03 bbs-pd_01">판매가 노출</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="thkc_dfc">
                                            <label for="mb_viewType" class="thkc_blind">판매가 노출</label><input class="thkc_input" type="checkbox" id="mb_viewType" name="mb_viewType" value='1' onClick="click_ck();">
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
								<div class="thkc_btnWrap_03" style="justify-content: flex-start; width:100%;border-bottom: 1px solid #ddd;padding-bottom:15px;margin-top:-5px;">
                                    * 체크박스 활성화 시, 직원 계정에서도 급여가와 판매가 모두 확인할 수 있습니다.<br>
									* 직원에게 급여가만 노출하고 싶으신 경우, 체크박스를 비활성화 해주세요.<br>
									* 급여가만 노출되는 계정은 주문 및 장바구니,주문/배송 상세 확인이 불가합니다.
                                </div>
								<!-- 주문  -->
                                <div class="table-box table-box_02">
                                    <div class="tit03 bbs-pd_01">주문가능</div>
                                    <div class="thkc_cont bbs-pd_01">
                                        <div class="thkc_dfc">
                                            <label for="manager_auth_order" class="thkc_blind">주문가능</label><input class="thkc_input" type="checkbox" id="manager_auth_order" name="manager_auth_order" value='1'>
                                        </div>
                                        <div class="error-txt error"></div>
                                    </div>
                                </div>
								
								<?php }?>
                                <div class="thkc_btnWrap_03">
                                    <button class="cancel">취소</button>
                                    <button class="on">등록하기</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
			<!-- 회원정보 탈퇴 추가 모달 -->
<div class="thkc_popUpWrap " id="leave">
	<div class="thkc_popWrap">
		<div class="thkc_close">
			<i class="fa-solid fa-xmark"></i>
		</div>
		<div class="thkc_joinWrap">
			<div class="joinTitle">
				<div class="boxLeft">회원탈퇴 신청</div>
			</div>
			<div class="thkc_tableWrap thkc_bbs-more">
				<div class="table-box table-box_02">
					<div class="tit03 bbs-pd_01">아이디
					</div>
					<div class="thkc_cont bbs-pd_01">
						<label for="id" class="thkc_blind">아이디</label>
						<input s class="thkc_input" id="id" placeholder="test1234" value="<?=$member['mb_id']?>" type="text" disabled/>
					</div>
				</div>
				<div class="table-box table-box_02">
					<div class="tit03 bbs-pd_01">비밀번호
					</div>
					<div class="thkc_cont bbs-pd_01">
						<label for="password" class="thkc_blind">비밀번호</label>
						<input class="thkc_input" id="mb_password2" name="mb_password2" placeholder="비밀번호를 입력하세요" value="" type="password" />
						<div class="error-txt error"></div>
					</div>
				</div>
				<div class="table-box table-box_02">
					<div class="tit03 bbs-pd_01">사업자<br>등록번호</div>
					<div class="thkc_cont bbs-pd_01">
						<div>
							<label for="bnum" class="thkc_blind">사업자등록번호</label>
							<input class="thkc_input" id="bnum" name="bnum" placeholder="사업자등록번호를 입력하세요." value="" type="text" />
						</div>
						<div class="error-txt error"></div>
					</div>
				</div>
				<div class="table-box table-box_02">
					<div class="tit03 bbs-pd_01">탈퇴사유</div>
					<div class="thkc_cont bbs-pd_01">
						<form action="">
							<label for="thkc_textarea" class="thkc_blind">탈퇴사유</label>
							<textarea class="thkc_textarea" name="leave_resn" id="leave_resn" cols="50" rows="7" maxlength="500" placeholder="탈퇴 사유를 적어주세요" style="padding:10px;resize: none;"></textarea>
						</form><p class="f_s14 d-flex justify-content-end" id="counter" style="text-align:right;">0/500</p>
						<div class="error-txt error"></div>
					</div>
				</div>
				<div class="table-box table-box_02" style="padding: 16px;">
					<div class="f_s14">
						<span class="f_color01">! 아래와 같은 상황엔 탈퇴가 불가합니다.</span>
						<ul class="">
							<li>- 진행 중인 주문, 계약 등이 있는 경우</li>
							<li>- 상담완료가 되지 않은 상담이 있는 경우</li>
							<li>- 미수금이 있는 경우</li>
						</ul>
					</div>
				</div>
				<div class="thkc_btnWrap_03">
					<button class="cancel">취소</button>
					<button class="on" id="btn_leave2">탈퇴 신청하기</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- 회원정보 신청 완료 추가 모달 -->
<div class="thkc_popUpWrap" style="width: 400px;" id="leave_request_complete">
	<div class="thkc_popWrap">
		<div class="thkc_close logout2">
		<i class="fa-solid fa-xmark"></i>
		</div>

		<div class="_thkc_joinWrap">
			<div class="_thkc_tableWrap thkc_bbs-more">
				<div class="table-box table-box_02" style="padding: 20px; ">
					<p class="f_bold" style="font-size:20px">탈퇴 신청이 완료되었습니다.</p>
					<p>&nbsp;</p>

					<p>이후 이로움 관리자의 ‘승인’ 절차에 의해
					탈퇴 처리가 진행될 예정입니다.</p>
					<p>&nbsp;</p>
					<p>영업일 기준 7일 정도 소요되며,</p>
					<p>카카오톡으로 결과를 안내드립니다.</p>
				</div>
				<div class="thkc_btnWrap_03">
					<button class="cancel on logout2">확인</button>
				</div>
			</div>
		</div>
	</div>
</div>

            <script>
			$('#leave_resn').keyup(function (e){
				var content = $(this).val();
				$('#counter').html(content.length+"/500");    //글자수 실시간 카운팅    
				if (content.length > 500){        
					alert("최대 500자까지 입력 가능합니다.");        
					$(this).val(content.substring(0, 501));
					$('#counter').html("500/500");    
				}
			});
                // 담당자 추가
                function manager_add(){
                    <?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
					var manager_auth_order = ($(".thkc_popUpWrap #manager_auth_order").is(':checked'))?"1":"0";
					var mb_viewType = ($(".thkc_popUpWrap #mb_viewType").is(':checked'))?"0":"1";
					<?php }?>
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
							<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
							"manager_auth_order":  manager_auth_order,
							"mb_viewType":  mb_viewType,
							<?php }?>
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

				//판매가 노출 체크 확인
				function click_ck(){
					if($("#mb_viewType").is(':checked')){//판매가 노출 체크 상태
						$("#manager_auth_order").prop('disabled',false);//주문가능 disabled 해제
					}else{//판매가 노출 체크 해제 상태
						$("#manager_auth_order").prop('checked',false).prop('disabled',true);//주문가능 체크 해제
					}
				}
                // 담당자 정보 변경
                function manager_modify(_no){
                        
                    $("#member_add #mm_id").val( $(".manager_" + _no + " #mb_id").val() );
                    $("#member_add #mm_id").attr("disabled", true); 

                    $("#member_add #mm_name").val( $(".manager_" + _no + " #mb_name").val() );
                    $("#member_add #mm_hp").val( $(".manager_" + _no + " #mm_tel").val() );
                    $("#member_add #mm_email").val( $(".manager_" + _no + " #mm_email").val() );
                    $("#member_add #mm_memo").val( $(".manager_" + _no + " #mm_memo").val() );
					<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
					if($(".manager_" + _no + " #mm_auth_order").val() == "0"){
						$("#member_add #manager_auth_order").prop('checked',false);
					}else{
						$("#member_add #manager_auth_order").prop('checked',true);
					}
					if($(".manager_" + _no + " #mm_viewType").val() == "1"){
						$("#member_add #mb_viewType").prop('checked',false);
					}else{
						$("#member_add #mb_viewType").prop('checked',true);
					}
					<?php }?>
                    $("#member_add .boxLeft").text("직원정보 수정");
                    $("#member_add .boxRright").hide();
                    
                    $('#member_add .thkc_btnWrap_03 .on').text("변경하기"); 
                    $("#member_add .thkc_btnWrap_03 .on").attr("onclick", "confirm_modify('" + _no + "')");
                    
                    $("#member_add").css("display", "flex").hide().fadeIn();
                    $(".thkc_popOverlay").show();
                    
                    document.body.classList.add("stop-scroll");                    
                }
                function confirm_modify( no ) {
                    <?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
					var manager_auth_order = ($(".thkc_popUpWrap #manager_auth_order").is(':checked'))?"1":"0";
					var mb_viewType = ($(".thkc_popUpWrap #mb_viewType").is(':checked'))?"0":"1";
					<?php }?>
					if(!confirm("직원 정보를 변경 하시겠습니까?")) { return; }

                    if( !ck_input( 'u' ) ) { return; }

                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_manager.php', type: 'POST', dataType: 'json',
                        data: {
                            "w": "u",
                            "mm_id": $("#member_add #mm_id").val(),
                            "mm_pw":  $("#member_add #mm_password").val(),
                            "mm_name":  $("#member_add #mm_name").val(),
                            "mm_tel":  $("#member_add #mm_hp").val(),
                            "mm_email":  $("#member_add #mm_email").val(),
							<?php if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출?>
							"manager_auth_order":  manager_auth_order,
							"mb_viewType":  mb_viewType,
							<?php }?>
                            "mm_memo":  $("#member_add #mm_memo").val()
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
				//회원 탈퇴 신청 클릭 시
				$("#btn_leave1").click(function () {
                    $.ajax({
                        url: '<?=G5_BBS_URL?>/ajax.member_leave.php', type: 'POST', dataType: 'json',
                        data: { 
                            "mode": "check"                        
                        },
                        success: function(data) {
                            if( data.YN === "Y" ) {//매칭된 상담조회, 구매 내역 조회 후 처리
                                $("#leave").css("display", "flex").hide().fadeIn();
								$(".thkc_popOverlay").show();
								$(".thkc_popUpWrap").attr("disabled", false);
                            } else {
                                alert(data.msg);
                            }
                        },
                        error: function(e) {}
                    });
                });
				//탈퇴 신청하기 클릭 시
				$("#btn_leave2").click(function () {
                    var leave_ok = "ok";
					if($("#mb_password2").val() == ""){
						alert("비밀번호를 입력해 주세요.");
						$("#mb_password2").focus();
						return false;
					}
					if($("#bnum").val() == ""){
						alert("사업자등록번호를 입력해 주세요.");
						$("#bnum").focus();
						return false;
					}
					if($("#leave_resn").val() == ""){
						alert("탈퇴 사유를 입력해 주세요.");
						$("#leave_resn").focus();
						return false;
					}

					if(leave_ok == "ok"){					
						$.ajax({
							url: '<?=G5_BBS_URL?>/ajax.member_leave.php', type: 'POST', dataType: 'json',
							data: { 
								"mode": "request",
								"leave_resn":$("#leave_resn").val(),
								"mb_password2":$("#mb_password2").val(),
								"bnum":$("#bnum").val(),
							},
							success: function(data) {
								if( data.YN === "Y" ) {//탈퇴 신청 처리 완료. 로그아웃 처리
									//탈퇴신청 완료 팝업 오픈
									$("#leave").hide();
									$("#leave_request_complete").css("display", "flex").show();
									$(".thkc_popUpWrap").attr("disabled", false);
								} else {
									alert(data.msg);
									if(data.YN === "N"){//비밀번호 오류 시
										$("#mb_password2").val("");
										$("#mb_password2").focus();
									}else{//사업자등록번호 오류 시
										$("#bnum").val("");
										$("#bnum").focus();
									}
								}
							},
							error: function(e) {}
						});
					}
                });

				//탈퇴 신청완료 확인,닫기 클릭 시 로그아웃 처리
				$(".logout2").click(function () {
					location.href="/bbs/logout.php";
				});
                
				
				$(".thkc_btnWrap .btn_submit_02").click(function () {
                    $("#member_add .boxLeft").text("직원신규 등록");
                    $("#member_add input").val("");

                    $("#member_add #mm_id").attr("disabled", false);
                    $("#member_add .boxRright").show();

                    $('#member_add .thkc_btnWrap_03 .on').text("등록하기");                    
                    $('#member_add .thkc_btnWrap_03 .on').attr("onclick", "manager_add()");
                });
                

                $('.thkc_popUpWrap .thkc_joinWrap .thkc_tableWrap .thkc_btnWrap_03 .cancel,.cancel').click(function () {
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
