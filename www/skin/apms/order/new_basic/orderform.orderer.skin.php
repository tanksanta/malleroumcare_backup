<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//수급자 정보 필드추가
sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
					ADD `od_penId` varchar(20) NOT NULL DEFAULT '' AFTER `od_addr_jibeon`,
                    ADD `od_penNm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penId`,
                    ADD `od_penTypeNm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penNm`,
                    ADD `od_penExpiDtm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penTypeNm`,
                    ADD `od_penAppEdDtm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penExpiDtm`,
                    ADD `od_penConPnum` varchar(20) NOT NULL DEFAULT '' AFTER `od_penAppEdDtm`,
                    ADD `od_penConNum` varchar(20) NOT NULL DEFAULT '' AFTER `od_penConPnum`,
					ADD `od_penzip1` char(3) NOT NULL DEFAULT '' AFTER `od_penConNum`,
                    ADD `od_penzip2` char(3) NOT NULL DEFAULT '' AFTER `od_penzip1`
                    ADD `od_penAddr` varchar(100) NOT NULL DEFAULT '' AFTER `od_penzip2` ", false);

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

	<?php
	$member['mb_name'] = '홍길동';
	$member['mb_tel'] = '02-1234-45678';
	$member['mb_hp'] = '02-1234-45678';
	$member['mb_zip1'] = '06035';
	$member['mb_addr1'] = '서울 강남구 가로수길 5';
	$member['mb_email'] = 'test@test.co.kr';
	?>

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

	<div id="order_recipientBox">
		<div>
			
			<iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php"></iframe>
			
		</div>
	</div>

	<style>
		
		#order_recipientBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		#order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		#order_recipientBox iframe { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }
		
		#order_recipient { background-color: #333 !important; color: #FFF !important; }
		#recipient_del { background-color: #DC3333 !important; color: #FFF !important; }
		
		.panel .top_area{position:relative;}
		/*.panel .top_area p:first-child{font-weight:bold;color:#ed9947;}*/
		.panel .top_area p:nth-child(2){font-size:12px;color:#999;margin:0;}
		.panel .top_area a{position:absolute;top:5px; right:0px;border:1px solid #ddd;padding: 10px 15px;display:inline-block;text-align:center;}
		.panel .top_area a:hover{background: #f5f5f5;color:#333;}
		
		@media (max-width : 750px){
			#order_recipientBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
			#order_recipient { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #999 !important; background-color: #999 !important; top: 0; right: 0; }
			#recipient_del { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #DC3333 !important; background-color: rgba(0, 0, 0, 0) !important; top: 0; right: 0; color: #DC3333 !important; margin-right: 100px !important; }
		}
	</style>
    <section id="sod_frm_recipient_orderer" style="margin-bottom:0px;">
		<input type="hidden" name="penId" id="penId">
		<input type="hidden" name="searchUsrId" id="searchUsrId" value="123456789">
		<input type="hidden" name="shoBasSeq" id="shoBasSeq" value="12">
		<input type="hidden" name="prodBarNum" id="prodBarNum" value="">
		<input type="hidden" name="ordNm" id="ordNm" value="김예비">
		<input type="hidden" name="ordCont" id="ordCont" value="010-2551-8080">
		<input type="hidden" name="ordZip" id="ordZip" value="46241">
		<input type="hidden" name="ordAddr" id="ordAddr" value="부산 금정구 부산대학로63번길 2">
		<input type="hidden" name="ordAddrDtl" id="ordAddrDtl" value="(장전동) 1">
		<input type="hidden" name="ordMemo" id="ordMemo" value="">
		<input type="hidden" name="payMehCd" id="payMehCd" value="00">

        <div class="panel panel-default" style="border:1px solid #ed9947;">
			<div class="panel-heading">
				<div class="top_area">
					<p class="black bold">수급자 정보</p>
					<p>주문 시 수급자정보가 없는 경우 사업소에서 주문한 것으로 판단하여 재고로 등록됩니다.</p>
					<a id="recipient_del" style="margin-right:130px;" class="bg-red">삭제</a>
					<a href="#" id="order_recipient" class="bg-black">내 수급자 조회</a>
				</div>
			</div>
            <div class="panel-body">

			<div id="Yrecipient" class="none">
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label"><b>수급자</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penNm" id="penNm" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>인정등급</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penTypeNm" id="penTypeNm" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>유효기간</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penExpiDtm" id="penExpiDtm" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>적용기간</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penAppEdDtm" id="penAppEdDtm" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>전화번호</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penConPnum" id="penConPnum" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>휴대전화</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penConNum" id="penConNum" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>주소</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penAddr" id="penAddr" class="form-control input-sm" readonly>
						<input type="hidden" name="penzip" id="penzip" value="">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
				<!--div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>한도금액</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="penMoney" id="penMoney" class="form-control input-sm" readonly>
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div-->
			</div>
			<div id="Nrecipient" class="block">
				<p class="bold">입력된 수급자 정보가 없습니다. </p>
			</div>

            </div>
        </div>
    </section>

	<script>

		function selected_recipient($penId) {
			<?php $re = sql_fetch(" select * from {$g5['recipient_table']} where penId = '$penId' ");  ?>
			// document.getElementById("penNm").value=$re['penNm'];
			// document.getElementById("penExpiDtm").value=$re['penExpiDtm'];
			// document.getElementById("penAppEdDtm").value=$re['penAppEdDtm'];
			// document.getElementById("penConNum").value=$re['penConNum'];
			// document.getElementById("penAddr").value=$re['penAddr'];
			// document.getElementById("penTypeNm").value=$re['penTypeNm'];
			// document.getElementById("penMoney").value=$re['penMoney'];

			var recipient = $penId.split("|");
			var list = {
				"rn":recipient[0],
				"penId":recipient[1],			//PENID_19210105000002
				"entId":recipient[2],			//ENT2020070900001
				"penNm":recipient[3],			//심재성
				"penLtmNum":recipient[4],		//L2147483647
				"penRecGraCd":recipient[5],		//01
				"penRecGraNm":recipient[6],		//1등급
				"penTypeCd":recipient[7],		//00
				"penTypeNm":recipient[8],		//일반 15%
				"penExpiStDtm":recipient[9],	//2020-01-01
				"penExpiEdDtm":recipient[10],	//2020-12-31
				"penExpiDtm":recipient[11],		//2020-01-01 ~ 2020-12-31
				"penExpiRemDay":recipient[12],	//375
				"penGender":recipient[13],		//00
				"penGenderNm":recipient[14],	//남
				"penBirth":recipient[15],		//19800101
				"penAge":recipient[16],			//41
				"penAppEdDtm":recipient[17],	//2020-12-31
				"penAddr":recipient[18],		//부산 금정구 부산대학로 63번길 2
				"penAddrDtl":recipient[19],		//10
				"penConNum":recipient[20],		//010-2631-3284
				"penConPnum":recipient[21],		//051-780-8157
				"penProNm":recipient[22],
				"usrId":recipient[23],			//1000203000
				"appCd":recipient[24],			//01
				"appCdNm":recipient[25],		//등록완료
				"caCenYn":recipient[26],		//N
				"regDtm":recipient[27],			//1609811513000
				"regDt":recipient[28],			//2021.01.05
				"ordLendEndDtm":recipient[29],
				"ordLendRemDay":recipient[30],
				"usrNm":recipient[31],			//홍길동
				"penAppRemDay":recipient[32],	//-10
				"penMoney":recipient[33]		//800,000원
			};

			$('#Yrecipient').removeClass('none');
			$('#Yrecipient').addClass('block');

			$('#Nrecipient').removeClass('block');
			$('#Nrecipient').addClass('none');

			document.getElementById("penId").value=list['penId'];				//penId
			document.getElementById("penNm").value=list['penNm'];				//수급자명
			document.getElementById("penTypeNm").value=list['penTypeNm'];		//인정등급
			document.getElementById("penExpiDtm").value=list['penExpiDtm'];		//유효기간
			document.getElementById("penAppEdDtm").value=list['penAppEdDtm'];	//적용기간
			document.getElementById("penConNum").value=list['penConNum'];		//휴대전화
			document.getElementById("penConPnum").value=list['penConPnum'];		//전화번호
			document.getElementById("penAddr").value=list['penAddr'];			//주소
			/*document.getElementById("penMoney").value=list['penMoney'];			//한도금액*/

		}

		$(function() {
			$("#recipient_del").on("click", function() {

				$('#Yrecipient').removeClass('block');
				$('#Yrecipient').addClass('none');

				$('#Nrecipient').removeClass('none');
				$('#Nrecipient').addClass('block');

				$('#penId').val('');
				$('#penNm').val('');
				$('#penTypeNm').val('');
				$('#penExpiDtm').val('');
				$('#penAppEdDtm').val('');
				$('#penConPnum').val('');
				$('#penConNum').val('');
				$('#penAddr').val('');
				$('#penMoney').val('');
			});
		});
	</script>




    <!-- 수급자 입력 시작 -->
    <!--section>
		<div class="point_box" id="recipient_box">
			<div class="top_area">
				<p>수급자 정보</p>
				<p>주문 시 수급자정보가 없는 경우 사업소에서 주문한 것으로 판단하여 재고로 등록됩니다.</p>
				<a id="recipient_del" style="margin-right:130px;">삭제</a>
				<a href="<?php echo G5_SHOP_URL;?>/pop_recipient.php" id="order_recipient">내 수급자 조회</a>
			</div>

			<input type="hidden" name="penId" id="penId">
			<input type="hidden" name="searchUsrId" id="searchUsrId" value="123456789">
			<input type="hidden" name="shoBasSeq" id="shoBasSeq" value="12">
			<input type="hidden" name="prodBarNum" id="prodBarNum" value="">
			<input type="hidden" name="ordNm" id="ordNm" value="김예비">
			<input type="hidden" name="ordCont" id="ordCont" value="010-2551-8080">
			<input type="hidden" name="ordZip" id="ordZip" value="46241">
			<input type="hidden" name="ordAddr" id="ordAddr" value="부산 금정구 부산대학로63번길 2">
			<input type="hidden" name="ordAddrDtl" id="ordAddrDtl" value="(장전동) 1">
			<input type="hidden" name="ordMemo" id="ordMemo" value="">
			<input type="hidden" name="payMehCd" id="payMehCd" value="00">

			<div class="point_desc">
				<ul>
					<li>
						<p>수급자</p>
						<p><input name="penNm" id="penNm" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>인정등급</p>
						<p><input name="penTypeNm" id="penTypeNm" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>유효기간</p>
						<p><input name="penExpiDtm" id="penExpiDtm" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>적용기간</p>
						<p><input name="penAppEdDtm" id="penAppEdDtm" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>전화번호</p>
						<p><input name="penConPnum" id="penConPnum" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>휴대전화</p>
						<p><input name="penConNum" id="penConNum" class="form-control input-sm" readonly></p>
					</li>
					<li>
						<p>주소</p>
						<p><input name="penAddr" id="penAddr" class="form-control input-sm" readonly></p>
					</li>
				</ul>
			</div>

			<script>
			$(function() {
				$("#recipient_del").on("click", function() {
					$('#penNm').val('');
					$('#penTypeNm').val('');
					$('#penExpiDtm').val('');
					$('#penAppEdDtm').val('');
					$('#penConPnum').val('');
					$('#penConNum').val('');
					$('#penAddr').val('');
					$('#penMoney').val('');
				});

			});
			</script>
		</div>
	</section-->


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
                                <input type="radio" name="ad_sel_addr" value="recipient" id="ad_sel_addr_recipient">
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
								$("#order_recipientBox").hide();
								$("#order_recipientBox").css("opacity", 1);
                            $("#order_recipient").on("click", function(e){
                                e.preventDefault();
								
									$("#order_recipientBox").show();
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