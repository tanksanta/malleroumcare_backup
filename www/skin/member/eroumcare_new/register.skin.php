<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($header_skin)
	include_once('./header.php');
?>

	<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">


    <!-- 회원가입 -->
    <div class="thkc_loginWrap">

    </div>
    <div class="thkc_memberWrapBg">
        <section class="thkc_joinWrap thkc_container_03">
            <p class="thkc_titleTop">신규 회원가입</p> 
            <p class="thkc_titleTabJoin">회원구분</p> 

            <!-- 회원구분 Tap -->
            <div class="thkc_tabJoin">
                <div class="thkc_tabUl">
                    <li class="active" onclick="$('#join_mb_type').val('default')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="31" height="36" fill="none">
                            <g class="svg_01" fill="#FF9015" clip-path="url(#a)">
                                <path d="m14.159 17.255.743 1.863h.69l.678-1.689-1.082-3.384-1.03 3.21Zm4.501-4.766-2.646 6.629H26.85V16.84c-.274-2.236-3.848-4.417-7.077-5.24a6.64 6.64 0 0 1-1.106.889h-.006Z" />
                                <path d="M15.253 12.994c3.282 0 5.958-2.729 5.958-6.095 0-3.365-2.67-6.094-5.958-6.094-3.288 0-5.97 2.729-5.97 6.094 0 3.366 2.67 6.095 5.97 6.095Zm14.593 20.308H.654a.412.412 0 0 0-.404.415v.45c0 .56.446 1.028 1.005 1.028h28.002c.547 0 .993-.469.993-1.028v-.45a.412.412 0 0 0-.404-.415Z" />
                                <path d="M11.84 12.489a6.609 6.609 0 0 1-1.106-.89c-3.235.824-6.791 3.005-7.077 5.241v2.278h10.835l-2.646-6.63h-.006Zm16.323 7.921c0-.493-.393-.89-.863-.89H3.212a.882.882 0 0 0-.874.89v12.495h25.826V20.41Zm-12.904 8.342c-1.267 0-2.295-1.051-2.295-2.344 0-1.292 1.028-2.344 2.295-2.344s2.283 1.052 2.283 2.344c0 1.293-1.017 2.344-2.283 2.344Z" />
                            </g>
                        </svg>
                        <h4>사업소 회원</h4>
                    </li>
                    <li class="bb" onclick="$('#join_mb_type').val('partner')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="51" height="28" fill="none">
                            <g class="svg_01" fill="#FF9015" clip-path="url(#a)">
                                <path d="M42.18 10.353c2.83 0 5.145-2.314 5.145-5.176S45.01 0 42.18 0c-2.83 0-5.13 2.315-5.13 5.177 0 2.862 2.292 5.176 5.13 5.176Zm-32.884 0c2.83 0 5.13-2.314 5.13-5.176S12.133 0 9.296 0 4.167 2.315 4.167 5.177c0 2.862 2.291 5.176 5.13 5.176Z" />
                                <path d="m30.732 24.924 8.308-.864a2.442 2.442 0 0 0 2.085-1.601l3.005-7.991c-.975 3.107-1.562 5.422-2.68 8.498-.594 1.665-2.275 1.649-3.614 1.807v.968h10.916V14.468a5.5 5.5 0 0 0-2.973-4.9 5.619 5.619 0 0 1-3.6 1.3c-.871 0-1.704-.19-2.449-.562a5.515 5.515 0 0 0-1.894 4.162v2.703l-.762 2.053-6.968 1.736c-3.234.278-2.624 4.234.626 3.948v.016ZM50.82 26.28H.68V28h50.14v-1.72Zm-37.171-1.49c-1.324-.16-3.02-.143-3.615-1.808-1.094-3.076-1.705-5.39-2.68-8.498l3.005 7.99a2.453 2.453 0 0 0 2.085 1.602l8.323.864c3.235.277 3.861-3.67.61-3.948l-6.967-1.736-.761-2.053v-2.704c0-1.649-.73-3.139-1.895-4.162-.73.373-1.562.563-2.45.563a5.619 5.619 0 0 1-3.599-1.3 5.544 5.544 0 0 0-2.973 4.9v11.272H13.65v-.983Z" />
                            </g>
                        </svg>
                        <h4>파트너 회원</h4>
                    </li>
                </div>
            </div>			
  			<input type="hidden" name="mb_type" id="join_mb_type" value="default">

            <!-- 회원구분 -->
            <div class="thkc_JoinConent">
                <h5>*사업소 유형 (중복 선택가능)</h5>
                <p class="field check">
                    <input type="checkbox" id="join_a" name="default" value="복지용구사업소" checked class="blind">
                    <input type="checkbox" id="join_b" name="default" value="의료기기상" class="blind">
                    <input type="checkbox" id="join_c" name="default" value="복지센터" class="blind">

                    <label for="join_a" class="btn-label join_a">복지용구사업소</label>
                    <label for="join_b" class="btn-label join_b">의료기기상</label>
                    <label for="join_c" class="btn-label join_c">복지센터</label>
                </p>
            </div>
            <div class="thkc_JoinConent">
                <h5>*파트너 유형 (중복 선택가능)</h5>
                <p class="field check">
                    <input type="checkbox" id="join_d" name="partner" value="직배송파트너" checked class="blind">
                    <input type="checkbox" id="join_e" name="partner" value="설치(소독)파트너" class="blind">
                    <input type="checkbox" id="join_f" name="partner" value="물품공급파트너" class="blind">

                    <label for="join_d" class="btn-label join_d">직배송파트너</label>
                    <label for="join_e" class="btn-label join_e">설치(소독)파트너</label>
                    <label for="join_f" class="btn-label join_f">물품공급파트너</label>
                </p>
            </div>
            <p class="thkc_btnWrap_02">
            <div class="thkc_btnWrap">
                <a href="#" onclick="JoinNext();"><button class="btn_submit_01">다음 단계로</button></a>
                <br>
                <a href="<?=G5_URL?>" class="text_under">메인으로</a>
            </div>
            </p>

        </section>
    </div>

	<script>
		function JoinNext() {
			var mb_type = $('#join_mb_type').val();

			if( mb_type === "default" ) { 
				var default_type = "";
				$("input:checkbox[name='default']:checked").each(function(){ default_type += $(this).val() + "|"; });
				if( !default_type ) { alert("사업소 유형을 선택하세요."); return; }
				location.href = "<?=G5_BBS_URL;?>/register_form.php?type="+mb_type+"&category="+default_type;
			}
			else if( mb_type === "partner" ) {
				var partner_type = "";
				$("input:checkbox[name='partner']:checked").each(function(){ partner_type += $(this).val() + "|"; });
				if( !partner_type ) { alert("파트너 유형을 선택하세요."); return; }
				location.href = "<?=G5_BBS_URL;?>/register_form.php?type="+mb_type+"&category="+partner_type;
			}
		 	else { 
				if( !mb_type ) { alert("회원 구분을 선택 해주세요."); location.reload(); return; }
			}
		}
	</script>