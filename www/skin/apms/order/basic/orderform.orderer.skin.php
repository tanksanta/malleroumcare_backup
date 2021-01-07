<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
    $(function() {
        $('#od_tel').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_hp').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_b_tel').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_b_hp').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
    });
</script>

<script type="text/javascript">
function selected_recipient($penId) {
	<?php $re = sql_fetch(" select * from {$g5['recipient_table']} where penId = '$penId' ");  ?>
	// document.getElementById("penNm").value=$re['penNm'];
	// document.getElementById("penExpiDtm").value=$re['penExpiDtm'];
	// document.getElementById("penAppEdDtm").value=$re['penAppEdDtm'];
	// document.getElementById("penConNum").value=$re['penConNum'];
	// document.getElementById("penAddr").value=$re['penAddr'];
	// document.getElementById("penTypeNm").value=$re['penTypeNm'];
	// document.getElementById("penMoney").value=$re['penMoney'];

	document.getElementById("penNm").value=$penId;
	document.getElementById("penExpiDtm").value=$penId;
	document.getElementById("penAppEdDtm").value=$penId;
	document.getElementById("penConNum").value=$penId;
	document.getElementById("penAddr").value=$penId;
	document.getElementById("penTypeNm").value=$penId;
	document.getElementById("penMoney").value=$penId;
	
}</script>

<?php if(!$is_orderform) { //주문서가 필요없는 주문일 때 ?>

    <section id="sod_frm_orderer" style="margin-bottom:0px;">
        <div class="panel panel-default">
            <div class="panel-heading"><strong> 결제하시는 분</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label"><b>아이디</b></label>
                    <label class="col-sm-3 control-label" style="text-align:left;">
                        <b><?php echo $member['mb_id'];?></b>
                    </label>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_name" value="<?php echo get_text($member['mb_name']); ?>" id="od_name" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_tel"><b>연락처</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_tel" value="<?php echo ($member['mb_hp']) ? get_text($member['mb_hp']) : get_text($member['mb_tel']); ?>" id="od_tel" required class="form-control input-sm" maxlength="13">
                        <span class="fa fa-phone form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="od_memo"><b>메모</b></label>
                    <div class="col-sm-8">
                        <textarea name="od_memo" rows=3 id="od_memo" class="form-control input-sm"></textarea>
                    </div>
                </div>
                <input type="hidden" name="od_email" value="<?php echo $member['mb_email']; ?>">
                <input type="hidden" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>">
                <input type="hidden" name="od_b_name" value="<?php echo get_text($member['mb_name']); ?>">
                <input type="hidden" name="od_b_tel" value="<?php echo get_text($member['mb_tel']); ?>">
                <input type="hidden" name="od_b_hp" value="<?php echo get_text($member['mb_hp']); ?>">
                <input type="hidden" name="od_b_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2']; ?>">
                <input type="hidden" name="od_b_addr1" value="<?php echo get_text($member['mb_addr1']); ?>">
                <input type="hidden" name="od_b_addr2" value="<?php echo get_text($member['mb_addr2']); ?>">
                <input type="hidden" name="od_b_addr3" value="<?php echo get_text($member['mb_addr3']); ?>">
                <input type="hidden" name="od_b_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">

            </div>
        </div>
    </section>

<?php } else { ?>

    <?php if($is_guest_order) { // 비회원 주문일 때 ?>
        <!-- 주문하시는 분 입력 시작 { -->
        <section id="sod_frm_agree" style="margin-bottom:0px;">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong> 개인정보처리방침안내</strong>
                </div>
                <div class="panel-body">
                    비회원으로 주문하시는 경우 포인트는 지급하지 않습니다.
                </div>
                <table class="table">
                    <colgroup>
                        <col width="30%">
                        <col width="30%">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>목적</th>
                        <th>항목</th>
                        <th>보유기간</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>이용자 식별 및 본인 확인</td>
                        <td>이름, 비밀번호</td>
                        <td>5년(전자상거래등에서의 소비자보호에 관한 법률)</td>
                    </tr>
                    <tr>
                        <td>배송 및 CS대응을 위한 이용자 식별</td>
                        <td>주소, 연락처(이메일, 휴대전화번호)</td>
                        <td>5년(전자상거래등에서의 소비자보호에 관한 법률)</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="row row-15">
                <div class="consent_wrap">
                    <p>*비회원으로 주문시 경우 개인정보 동의 필수</p>
                    <div class="btn_consent">
                        <div data-toggle="buttons">
                            <label class="btn btn-green btn-sm btn-block">
                                <input type="checkbox" name="agree" value="1" id="agree" autocomplete="off">
                                <i class="fa fa-check"></i>
                                개인정보처리방침안내에 동의합니다.
                            </label>
                        </div>
                        <div class="h10"></div>
                    </div>
                </div>
                <div class="login_wrap">
                    <p>*로그인 후 주문 시 회원 혜택 제공</p>
                    <div class="btn_login">
                        <a href="<?php echo $order_login_url;?>" class="btn btn-lightgray btn-sm btn-block">
                            <i class="fa fa-sign-in"></i>
                            로그인/회원가입
                        </a>
                        <div class="h10"></div>
                    </div>
                </div>
            </div>
            <div class="h10"></div>
        </section>
    <?php } ?>

    <!-- 주문하시는 분 입력 시작 { -->
    <section id="sod_frm_orderer" style="margin-bottom:0px;">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>  주문하시는 분</strong></div>
            <div class="panel-body">
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_name" value="<?php echo get_text($member['mb_name']); ?>" id="od_name" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
                <?php if (!$is_member) { // 비회원이면 ?>
                    <div class="form-group has-feedback">
                        <label class="col-sm-2 control-label" for="od_pwd"><b>비밀번호</b><strong class="sound_only">필수</strong></label>
                        <div class="col-sm-3">
                            <input type="password" name="od_pwd" id="od_pwd" required class="form-control input-sm" maxlength="20">
                            <span class="fa fa-lock form-control-feedback"></span>
                        </div>
                        <div class="col-sm-7">
                            <span class="help-block">영,숫자 3~20자 (주문서 조회시 필요)</span>
                        </div>
                    </div>          
                <?php } ?>          
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_tel"><b>전화번호</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_tel" value="<?php echo get_text($member['mb_tel']); ?>" id="od_tel" required class="form-control input-sm" maxlength="13">
                        <span class="fa fa-phone form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_hp"><b>핸드폰</b></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>" id="od_hp" class="form-control input-sm" maxlength="13">
                        <span class="fa fa-mobile form-control-feedback"></span>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label"><b>주소</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-8">
                        <label for="od_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                        <label>
                            <input type="text" name="od_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" id="od_zip" required class="form-control input-sm" size="6" maxlength="6">
                        </label>
                        <label>
                            <button type="button" class="btn btn-black btn-sm" style="margin-top:0px;" onclick="win_zip('forderform', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button>
                        </label>

                        <div class="addr-line">
                            <label class="sound_only" for="od_addr1">기본주소<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="od_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="od_addr1" required class="form-control input-sm" size="60" placeholder="기본주소">
                        </div>

                        <div class="addr-line">
                            <label class="sound_only" for="od_addr2">상세주소</label>
                            <input type="text" name="od_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="od_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
                        </div>

                        <label class="sound_only" for="od_addr3">참고항목</label>
                        <input type="text" name="od_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="od_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
                        <input type="hidden" name="od_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']) ?>">
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_email"><b>E-mail</b><strong class="sound_only"> 필수</strong></label>
                    <div class="col-sm-5">
                        <input type="text" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="form-control input-sm email" size="35" maxlength="100">
                        <span class="fa fa-envelope form-control-feedback"></span>
                    </div>
                </div>

                <?php if ($default['de_hope_date_use']) { // 배송희망일 사용 ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="od_hope_date"><b>희망배송일</b></label>
                        <!--
                            <div class="col-sm-3">
                                <select name="od_hope_date" id="od_hope_date" class="form-control input-sm>
                                    <option value="">선택하십시오.</option>
                                    <?php
                                        for ($i=0; $i<7; $i++) {
                                            $sdate = date("Y-m-d", time()+86400*($default['de_hope_date_after']+$i));
                                            echo '<option value="'.$sdate.'">'.$sdate.' ('.get_yoil($sdate).')</option>'.PHP_EOL;
                                        }
                                    ?>
                                </select>
                            </div>
                        -->
                        <div class="col-sm-7">
                            <span class="form-inline">
                                <input type="text" name="od_hope_date" value="" id="od_hope_date" required class="form-control input-sm" size="11" maxlength="10" readonly="readonly">
                            </span> 
                            이후로 배송 바랍니다.
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <!-- } 주문하시는 분 입력 끝 -->
    
    <!-- 수급자 입력 시작 -->
    <section>
    <div class="point_box" id="recipient_box">
        <div class="top_area">
            <p>수급자 정보</p>
            <p>주문 시 수급자정보가 없는 경우 사업소에서 주문한 것으로 판단하여 재고로 등록됩니다.</p>
            <a href="<?php echo G5_SHOP_URL;?>/pop_recipient.php" id="order_recipient">내 수급자 조회</a>
		</div>

			
			<!-- <script> if (document.getElementById("penNm").value != "") { -->
				<!-- 수급자 정보가 있으면 아래 내용이 보여집니다. -->
				<div class="point_desc">
					<ul>
						<li>
							<p>수급자</p>
							<p><input id="penNm"></p>
						</li>
						<li>
							<p>인정등급</p>
							<p><input type="penTypeNm" id="penTypeNm"></p>
						</li>
						<li>
							<p>유효기간</p>
							<p><input type="penExpiDtm" id="penExpiDtm"></p>
						</li>
						<li>
							<p>적용기간</p>
							<p><input type="penAppEdDtm" id="penAppEdDtm"></p>
						</li>
						<li>
							<p>전화번호</p>
							<p><input type="penConNum" id="penConNum"></p>
						</li>
						<li>
							<p>주소</p>
							<p><input type="penAddr" id="penAddr"></p>
						</li>
						<li>
							<p>한도금액</p>
							<p><input type="penMoney" id="penMoney"></p>
						</li>
					</ul>
					<a href="#">삭제</a>
				</div>
			<!--   } else {  -->
                 <!-- 수급자 정보가 없는 경우 표시  -->
                 <!-- <div class="block-box"> -->
                     <!-- <p>입력된 수급자 정보가 없습니다. </p>  -->
                 <!-- </div> -->
              <!-- } </script> -->
        </div>
        
	</div>
	</section>
    
    
    <!-- 수급자 입력 끝 -->

    <!-- 받으시는 분 입력 시작 { -->
    <section id="sod_frm_taker">
        <div class="panel panel-default">
            <div class="panel-heading"><strong> 받으시는 분</strong></div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="col-sm-2 control-label"><b>배송지선택</b></label>
                    <div class="col-sm-10 radio-line">
                        <?php if($is_member) { ?>
                            <label>
                                <input type="radio" name="ad_sel_addr" value="same" id="ad_sel_addr_same">
                                주문자와 동일
                            </label>
                            <label>
                                <input type="radio" name="ad_sel_addr" value="" id="">
                                수급자와 동일
                            </label>
                            <?php if($addr_default) { ?>
                                <label>
                                    <input type="radio" name="ad_sel_addr" value="<?php echo get_text($addr_default);?>" id="ad_sel_addr_def">
                                    기본배송지
                                </label>
                            <?php } ?>

                            <?php for($i=0; $i < count($addr_sel); $i++) { ?>
                                <label>
                                    <input type="radio" name="ad_sel_addr" value="<?php echo get_text($addr_sel[$i]['addr']);?>" id="ad_sel_addr_<?php echo $i+1;?>">
                                    최근배송지<?php echo ($addr_sel[$i]['name']) ? '('.get_text($addr_sel[$i]['name']).')' : '';?>
                                </label>
                            <?php } ?>
                            <label>
                                <input type="radio" name="ad_sel_addr" value="new" id="od_sel_addr_new">
                                신규배송지
                            </label>
                            <span>
                                <a href="<?php echo G5_SHOP_URL;?>/orderaddress.php" id="order_address" class="btn btn-black btn-sm">배송지목록</a>
                            </span>
                        <?php } else { ?>
                            <label>
                                <input type="checkbox" name="ad_sel_addr" value="same" id="ad_sel_addr_same">
                                주문자와 동일
                            </label>
                        <?php } ?>
                    </div>
                </div>
                <?php if($is_member) { ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="ad_subject"><b>배송지명</b></label>
                        <div class="col-sm-3">
                            <input type="text" name="ad_subject" id="ad_subject" class="form-control input-sm" maxlength="20">
                        </div>
                        <div class="col-sm-7 radio-line">
                            <label>
                                <input type="checkbox" name="ad_default" id="ad_default" value="1">
                                기본배송지로 설정
                            </label>
                        </div>
                    </div>
                <?php } ?>

                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_b_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_b_name" id="od_b_name" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_b_tel"><b>전화번호</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_b_tel" id="od_b_tel" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-phone form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_b_hp"><b>핸드폰</b></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_b_hp" id="od_b_hp" class="form-control input-sm" maxlength="20">
                        <span class="fa fa-mobile form-control-feedback"></span>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label"><b>주소</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-8">
                        <label for="od_b_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                        <label>
                            <input type="text" name="od_b_zip" id="od_b_zip" required class="form-control input-sm" size="6" maxlength="6">
                        </label>
                        <label>
                            <button type="button" class="btn btn-black btn-sm" style="margin-top:0px;" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button>
                        </label>

                        <div class="addr-line">
                            <label class="sound_only" for="od_b_addr1">기본주소<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="od_b_addr1" id="od_b_addr1" required class="form-control input-sm" size="60" placeholder="기본주소">
                        </div>

                        <div class="addr-line">
                            <label class="sound_only" for="od_b_addr2">상세주소</label>
                            <input type="text" name="od_b_addr2" id="od_b_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
                        </div>

                        <label class="sound_only" for="od_b_addr3">참고항목</label>
                        <input type="text" name="od_b_addr3" id="od_b_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
                        <input type="hidden" name="od_b_addr_jibeon" value="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="od_memo"><b>전하실말씀</b></label>
                    <div class="col-sm-8">
                        <textarea name="od_memo" rows=3 id="od_memo" class="form-control input-sm"></textarea>
                    </div>
                </div>
                
                <style>
                #od_delivery_type {
                    font-size: 12px;
                    color: #555;
                    appearance: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    padding: 2px 25px 0px 3px;
                    background: #ffffff url(/adm/shop_admin/img/admin_select_n.gif) no-repeat right 8px center;
                    border: 1px solid #dbdde2;
                    border-radius: 0px;
                    width: 100px;
                    height: 28px;
                    padding: 0px 13px;
                    vertical-align: middle;
                    margin-top:5px;
                }
                .quick_explain {
                    vertical-align:middle;
                    margin-left:5px;
                    display:none;
                }
                </style>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="od_memo"><b>배송방법</b></label>
                    <div class="col-sm-8">
                        <select name="od_delivery_type" id="od_delivery_type" style="width: 150px;">
                            <?php
                            foreach($delivery_types as $type) {
                                // if ( $type['user-order'] != true ) continue;
                                if ( !$default['de_delivery_type_' . $type['val']] ) continue;
                            ?>
                                <option value="<?php echo $type['val']; ?>" <?php echo $type['val'] == $od['od_delivery_type'] ? 'selected' : ''; ?> data-type="<?php echo $type['type']; ?>"><?php echo $type['name']; ?></option>
                            <?php } ?>
                        </select>
                        <span class="quick_explain">
                            담당자와 상담 후 선택해 주시기 바랍니다. (고객센터 : 02-2267-8080)
                        </span>
                        <script type="text/javascript">
                        $(function() {
                            // 수급자목록
                            $("#order_recipient").on("click", function() {
                                var url = this.href;
                                window.open(url, "win_address", "left=100,top=100,width=800,height=600,scrollbars=1");
                                return false;
                            });
                            
                            $('#od_delivery_type').change(function() {
                                var val = $(this).val();
                                
                                if ( val === 'quick2' ) {
                                    $('.quick_explain').show();
                                }else{
                                    $('.quick_explain').hide();
                                }
                            });
                        });
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php if ($ct_sc_method_sel) { ?>
    <script>
        $(window).load(function () {
            $('#od_delivery_type').val('<?php echo $ct_sc_method_sel ?>');
            $('#od_delivery_type').trigger('change');
        })
        
    </script>
    <?php } ?>

    <!-- } 받으시는 분 입력 끝 -->
<?php } ?>