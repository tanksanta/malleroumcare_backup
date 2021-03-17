<?php
if(!$member["mb_id"]){alert('로그인을 해주세요',G5_BBS_URL.'/login.php?url=%2F');};
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

	# 210223 수급자여부
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = 1;
	$sendData["appCd"] = "01";

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$recipientTotalCnt = $res["total"];

?>


<!-- // 20200306성훈추가 재고바코드 배열빼기 -->
<script>
var renew_num="";
var renew_array=[];
var renew_array2=[];
var array_box=[];
</script>
<!-- // 20200306성훈추가 재고바코드 배열빼기 -->

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


<style>
	.bsk-tbl .well li { width: 100%; float: left; }
</style>



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



    <!-- 비회원주문 -->
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
    <!-- 비회원주문 -->





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
                <?php }  ?>


            </div>
        </div>
    </section>
    <!-- } 주문하시는 분 입력 끝 -->



    <!-- 수급자 정보 iframe창 -->
	<?php if($itemPenIdStatus){ ?>
	<div id="order_recipientBox">
		<div>
			<iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php"></iframe>
		</div>
	</div>
	<div id="order_submitCheckBox">
		<div>
			<div>
				<div class="title">기타 협약사항1</div>
				<textarea class="form-control input-sm" name="entConAcc01" id="entConAcc01"><?=$member["mb_entConAcc01"]?></textarea>

				<div class="title">기타 협약사항2</div>
				<textarea class="form-control input-sm" name="entConAcc02" id="entConAcc02"><?=$member["mb_entConAcc02"]?></textarea>

				<button type="button" onclick="forderform_check(this.form);">수급자 주문하기</button>
				<button type="button" onclick="order_submitCheckBox_hide();">취소</button>
			</div>
		</div>
	</div>
	<?php } ?>
    <style>
		#order_recipientBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		#order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		#order_recipientBox iframe { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }

		#order_submitCheckBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		#order_submitCheckBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		#order_submitCheckBox > div > div { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; padding: 30px; text-align: center; overflow: auto; }
		#order_submitCheckBox > div > div > .title { width: 100%; float: left; font-size: 16px; font-weight: bold; margin-bottom: 5px; text-align: left; }
		#order_submitCheckBox > div > div > textarea { height: 100px; margin-bottom: 20px; resize: vertical; }
		#order_submitCheckBox > div > div > button { width: 150px; height: 45px; background-color: #333; border: 0; color: #FFF; border-radius: 0; font-size: 18px; font-weight: bold; }

		#order_recipient { background-color: #333 !important; color: #FFF !important; }
		#recipient_del { background-color: #DC3333 !important; color: #FFF !important; }

		.panel .top_area{position:relative;}
		/*.panel .top_area p:first-child{font-weight:bold;color:#ed9947;}*/
		.panel .top_area p:nth-child(2){font-size:12px;color:#999;margin:0;}
		.panel .top_area a{position:absolute;top:5px; right:0px;border:1px solid #ddd;padding: 10px 15px;display:inline-block;text-align:center;}
		.panel .top_area a:hover{background: #f5f5f5;color:#333;}

		#sod_frm_stock_status { margin-bottom: 50px; }
		#sod_frm_stock_status input[type="checkbox"] { vertical-align: middle; margin-right: 5px; margin-top: 0; top: -2px; position: relative; }
		#sod_frm_stock_status label { cursor: pointer; }

		.dateBtn { width: 50px; height: 30px; line-height: 28px; float: left; margin-left: 10px; background-color: #EEE; font-size: 12px; color: #333 !important; font-weight: bold; text-align: center; border: 1px solid #DDD; }

		@media (max-width : 750px){
			#order_recipientBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
			#order_submitCheckBox > div > div { width: 100%; height: 100%; left: 0; margin-left: 0; }
			#order_recipient { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #999 !important; background-color: #999 !important; top: 0; right: 0; }
			#recipient_del { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #DC3333 !important; background-color: rgba(0, 0, 0, 0) !important; top: 0; right: 0; color: #DC3333 !important; margin-right: 100px !important; }
		}
	</style>
    <!-- 수급자 정보 iframe창 -->







<!-- 수급자정보 박스 -->
    <section id="sod_frm_recipient_orderer" style="margin-bottom:0px;">
		<input type="hidden" name="penId" id="penId">
		<input type="hidden" name="penTypeCd" id="penTypeCd">
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
                    <label class="col-sm-2 control-label" for="od_name"><b>휴대폰</b><strong class="sound_only">필수</strong></label>
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
<!-- 수급자정보 박스 -->


<!-- 보유재고등록 -->
<section id="sod_frm_stock_status">
    	<p>
    		<label>
    			<input type="checkbox" name="od_stock_insert_yn" id="od_stock_insert_yn">
    			<b>보유 재고 등록</b>
    		</label>
    	</p>

    	<p>
    		<span>- 선택 시 상품배송이 되지 않습니다. 보유 재고 등록시에만 선택하세요.</span><br>
    		<span>- 보유 재고 등록시 바코드 정보를 모두 입력해야 등록이 가능합니다.</span><br>
    		<span>- 수급자를 선택하시면 보유 재고로 등록이 불가능합니다.</span>
    	</p>
    </section>
<!-- 보유재고등록 -->











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


            // 수급자 정보 iframe 에서 넘긴값 받기
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

            //수급자 정보 컨트롤
			$('#Yrecipient').removeClass('none');
			$('#Yrecipient').addClass('block');

			$('#Nrecipient').removeClass('block');
			$('#Nrecipient').addClass('none');

            //수급자 정보 동기화
			document.getElementById("penId").value=list['penId'];				//penId
			document.getElementById("penNm").value=list['penNm'];				//수급자명
			document.getElementById("penTypeNm").value=list['penTypeNm'];		//인정등급
			document.getElementById("penExpiDtm").value=list['penExpiDtm'];		//유효기간
			document.getElementById("penAppEdDtm").value=list['penAppEdDtm'];	//적용기간
			document.getElementById("penConNum").value=list['penConNum'];		//휴대전화
			document.getElementById("penConPnum").value=list['penConPnum'];		//전화번호
			document.getElementById("penAddr").value=list['penAddr'];			//주소
			document.getElementById("penTypeCd").value=list['penTypeCd'];			//주소
			/*document.getElementById("penMoney").value=list['penMoney'];			//한도금액*/


			var optionCntList = <?=json_encode($optionCntList)?>;
			var optionBarList = <?=json_encode($optionBarList)?>;
			var prodItemList = $("#sod_list tr.item");
            console.log(prodItemList);
			$.each(prodItemList, function(key, itemDom){
				var code = $(itemDom).attr("data-code");
				var itemList = $(itemDom).find(".well li");
				var discountCnt = 0;
				var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
				var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

				$(itemDom).find(".barList").find("input").attr("type", "text");

				$.each(itemList, function(subKey, subDom){
//					if($(itemDom).attr("data-sup") == "Y"){
						var dataBarCnt = Number($(subDom).attr("data-bar-cnt"));
						var dataStockCnt = Number($(subDom).attr("data-stock-cnt"));
						var optionCnt = (dataStockCnt <= dataBarCnt) ? dataStockCnt : dataBarCnt;
						var html = "";

						for(var i = 0; i < optionCnt; i++){
							html += "<option value='" + (i + 1) + "'>" + (i + 1) + "개</option>";
						}

						optionCnt = (optionCnt) ? optionCnt : 0;

						$(subDom).css("position", "relative");
						if(html){
							$(subDom).append("<div id='renew_num_v' class='recipientBox' style='float: right; display: <?=($rentalItemCnt) ? "none" : "block"?>;' data-code='" + subKey + "'><label><input type='radio' name='" + code + "Sup" + subKey + "' style='margin-top: 0;' data-type='use' checked> 재고소진 : </label> <select style='margin-top: -3px;'>" + html + "</select> <label><input type='radio' name='" + code + "Sup" + subKey + "' style='margin-top: 0; margin-left: 10px;' data-type='new'> 신규주문</label></div>");
						} else {
							$(subDom).append("<div id='renew_num_v'class='recipientBox' style='float: right; display: none;' data-code='" + subKey + "'><input type='radio' name='" + code + "Sup" + subKey + "' style='margin-top: 0; margin-left: 10px;' data-type='new' checked> 신규주문</label></div>");
						}

						$(subDom).find(".recipientBox select").val(optionCnt);

						var item = $(itemDom).find(".prodBarSelectBox" + subKey);

						//20210306성훈추가 - 바코드허용개수
						renew_num = $(itemDom).find(".prodBarSelectBox" + subKey).length;

						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");
							//20210306 성훈수정(아래줄 id 추가)
                            var html = '<select id="prodBarSelectBox_renew'+i+'" class="form-control input-sm prodBarSelectBox' + subKey + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							$.each(optionBarList[code][subKey], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
							html += '</select>';

							$(item[i]).after(html);

							$(item[i]).remove();
						}

						//20210306 성훈 추가
						renew_array = optionBarList[code][subKey];
						renew_array2 = optionBarList[code][subKey];


						discountCnt += optionCnt;

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						var stockCntItemCnt = Number($(subDom).find(".recipientBox select").val());
						stockCntItemCnt = (stockCntItemCnt) ? stockCntItemCnt : 0;
						$(stockCntItem[subKey]).val(stockCntItemCnt);
//					}
				});

				$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
				$(itemDom).find(".price").text(number_format((cnt - discountCnt) * price) + "원");


			});

			var it_price = $("input[name^=it_price]");
			var it_discount = $("input[name^=it_discount]");
			var totalPrice = 0;

			$.each(it_price, function(key, dom){
				if($(dom).closest("tr.item").attr("data-sup") == "Y"){
					totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
				}
			});

			if(!totalPrice){
				$("input[name='od_send_cost']").val(0);
				$(".delivery_cost_display > strong").text("0 원");
			} else {
				$("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
				$(".delivery_cost_display > strong").text(number_format($("input[name='od_send_cost_org']").val()) + " 원");
			}

			$("input[name=od_price]").val(totalPrice);
			$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
			calculate_order_price();
			$("#ad_sel_addr_recipient").parent().show();

			$("#display_pay_button > input").val("수급자 주문하기");
			$("#show_pay_btn > input").val("수급자 주문하기");
			$(".stockCntStatusDom").show();
			$("#sod_frm_taker").show();
			$("#sod_frm_pay").show();
			$(".ordLendFrm").show();

			var item = $(".ordLendDtmInput");
			for(var i = 0; i < item.length; i++){
				$(item[i]).val($(item[i]).attr("data-default"));
			}

			$("#od_stock_insert_yn").prop("checked", false);
			$("#sod_frm_stock_status").hide();
			$(".barList input").val("");
		}

		$(function() {
			$("#display_pay_button > input").val("재고 주문하기");
			$("#show_pay_btn > input").val("재고 주문하기");

			$("#recipient_del").on("click", function() {

				$(".recipientBox").remove();

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

				$("#ad_sel_addr_recipient").parent().hide();

				var optionCntList = <?=json_encode($optionCntList)?>;
				var optionBarList = <?=json_encode($optionBarList)?>;
				var prodItemList = $("#sod_list tr.item");

				$.each(prodItemList, function(key, itemDom){
					var code = $(itemDom).attr("data-code");            //아이템 넘버
					var itemList = $(itemDom).find(".well li");         //바코드개수
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					$(itemDom).find(".barList").find("input").attr("type", "hidden");   //개수만큼 넣기

					$.each(itemList, function(subKey, subDom){
							var item = $(itemDom).find(".prodBarSelectBox" + subKey);  //셀력트박스 찾기
							for(var i = 0; i < item.length; i++){
								var name = $(item[i]).attr("name");
								var dataCode = $(item[i]).attr("data-code");
								var dataThisCode = $(item[i]).attr("data-this-code");
								var dataName = $(item[i]).attr("data-name");

								$(item[i]).after('<input type="hidden" class="form-control input-sm prodBarSelectBox prodBarSelectBox' + subKey + '" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">');

								$(item[i]).remove();
							}

							var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
							$(stockCntItem[subKey]).val(0);
					});

					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price").text(number_format((cnt - discountCnt) * price) + "원");
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest("tr.item").attr("data-sup") == "Y"){
						totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
					}
				});

				if(!totalPrice){
					$("input[name='od_send_cost']").val(0);
					$(".delivery_cost_display > strong").text("0 원");
				} else {
					$("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
					$(".delivery_cost_display > strong").text(number_format($("input[name='od_send_cost_org']").val()) + " 원");
				}

				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();

				$("#display_pay_button > input").val("재고 주문하기");
				$("#show_pay_btn > input").val("재고 주문하기");
				$(".stockCntStatusDom").hide();
				$(".ordLendFrm").hide();
				$(".ordLendDtmInput").val("");

				$("#od_stock_insert_yn").prop("checked", false);
				$("#sod_frm_stock_status").show();
				$(".barList input").val("");
			});

			$("#od_stock_insert_yn").change(function(){
				var status = $(this).prop("checked");



				var prodItemList = $("#sod_list tr.item");
				$(".barList input").val("");

				if(status){
					$("#sod_frm_taker").hide();
					$("#sod_frm_pay").hide();
					$(".barList input[type='hidden']").attr("type", "text");
				} else {
					$("#sod_frm_taker").show();
					$("#sod_frm_pay").show();
					$(".barList input[type='text']").attr("type", "hidden");
				}

				$.each(prodItemList, function(key, itemDom){
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					if(status){
						price = 0;
						$("input[name='it_discount[" + key + "]']").val(price);
					} else {
						$("input[name='it_discount[" + key + "]']").val(0);
					}

					$("input[name='it_price[" + key + "]']").val(price);
					$(itemDom).find(".price").text(number_format(price) + "원");
				});

				if(status){
					$("input[name='od_send_cost']").val(0);
					$(".delivery_cost_display > strong").text("0 원");
				} else {
					$("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
					$(".delivery_cost_display > strong").text(number_format($("input[name='od_send_cost_org']").val()) + " 원");
				}

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest("tr.item").attr("data-sup") == "Y"){
						totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
					}
				});

                //20210307
				if(status){
					$("#forderform_check_btn").val("보유재고등록");
				}else{
					$("#forderform_check_btn").val("재고 주문하기");
				}

				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();
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

    <!-- 주문상품 정보 시작 -->
	<div class="table-responsive order-item">
		<table id="sod_list" class="div-table table bg-white bsk-tbl">
		<tbody>
		<tr class="<?php echo $head_class;?>">
			<th scope="col"><span>이미지</span></th>
			<th scope="col"><span>상품명</span></th>
			<th scope="col"><span>총수량</span></th>
			<th scope="col"><span>판매가</span></th>
			<th scope="col"><span>할인가</span></th>
			<th scope="col"><span>소계</span></th>
			<th scope="col"><span class="last">배송비</span></th>
			<th scope="col"><span>바코드</span></th>
		</tr>
		<?php for($i=0; $i < count($item); $i++) { ?>
			<tr class="item" data-code="<?=$item[$i]["it_id"]?>" data-sup="<?=$item[$i]["prodSupYn"]?>">
				<td class="text-center" style="vertical-align: middle;">
					<div class="item-img">
						<img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';" style="width: 70px; height: 70px;">
						<div class="item-type"><?php echo $item[$i]['pt_it']; ?></div>
					</div>
				</td>
				<td style="vertical-align: middle; width: 600px;">
					<input type="hidden" name="it_id[<?php echo $i; ?>]"    value="<?php echo $item[$i]['hidden_it_id']; ?>" class="it_id_class">
					<input type="hidden" name="it_name[<?php echo $i; ?>]"  value="<?php echo $item[$i]['hidden_it_name']; ?>">
					<input type="hidden" name="it_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_sell_price']; ?>">
					<input type="hidden" name="it_discount[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_sell_discount']; ?>">
					<input type="hidden" name="cp_id[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_cp_id']; ?>">
					<input type="hidden" name="cp_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_cp_price']; ?>">
					<input type="hidden" name="ct_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['ct_price']; ?>">
					<input type="hidden" name="it_qty[<?php echo $i; ?>]" value="<?php echo $item[$i]['qty']; ?>">
					<?php for($ii = 0; $ii < count($item[$i]["it_optionList"]); $ii++){ ?>
						<input type="hidden" class="it_option_stock_cnt" name="it_option_stock_cnt_<?=$item[$i]["it_optionList"][$ii]["id"]?>" value="0">
					<?php } ?>
					<?php if($default['de_tax_flag_use']) { ?>
						<input type="hidden" name="it_notax[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_it_notax']; ?>">
					<?php } ?>
					<b>
						<?php echo $item[$i]['it_name']; ?>
					</b>
					<?php if($item[$i]["prodSupYn"] == "N"){ ?>
						<b style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #DC3333;">비유통</b>
					<?php } else { ?>
						<b style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #3366CC;">유통</b>
					<?php } ?>

					<?php if(substr($item[$i]["ca_id"], 0, 2) == 20){ ?>
						<b style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #FFA500;">대여</b>
					<?php } ?>

					<?php if($item[$i]['it_options']) { ?>
						<div class="well well-sm" style="width: 100%; float: left;"><?php echo $item[$i]['it_options'];?></div>
					<?php } ?>
				</td>
				<td class="text-center" style="vertical-align: middle;"><?php echo $item[$i]['qty']; ?></td>
				<td class="text-right" style="vertical-align: middle;"><?php echo $item[$i]['ct_price']; ?></td>
				<td class="text-right" style="vertical-align: middle;"><?php echo $item[$i]['ct_discount']; ?></td>
				<td class="text-right" style="vertical-align: middle;"><b class="price"><?php echo $item[$i]['total_price']; ?></b></td>
				<td class="text-center delivery_cost_display_name" style="vertical-align: middle;"><?php echo $item[$i]['ct_send_cost']; ?></td>
				<td style="width: 120px; vertical-align: middle;" class="barList">
				<?php
					for($ii = 0; $ii < count($item[$i]["it_optionList"]); $ii++){
						for($iii = 0; $iii < $item[$i]["it_optionList"][$ii]["qty"]; $iii++){
				?>
						<?php if($optionCntList[$item[$i]["it_id"]][$ii] > $iii){ ?>
							<input type="hidden" class="form-control input-sm prodStockBarBox<?=$ii?> prodBarSelectBox prodBarSelectBox<?=$ii?>" style="margin-bottom: 5px;" data-code="<?=$ii?>" data-this-code="<?=$iii?>" data-name="<?=$postProdBarNumCnt?>" name="prodBarNum_<?=$postProdBarNumCnt?>">
						<?php } else { ?>
						<?php
							if($rentalItemCnt){
								$itemPenIdStatus = false;
							}
						?>
							<input type="hidden" class="form-control input-sm prodStockBarBox<?=$ii?>" value="" style="margin-bottom: 5px;" data-code="<?=$ii?>" data-this-code="<?=$iii?>" data-name="<?=$postProdBarNumCnt?>"  name="prodBarNum_<?=$postProdBarNumCnt?>">
						<?php } ?>
				<?php
						$postProdBarNumCnt++; }
					}
				?>
				<?php for($ii = 0; $ii < $item[$i]["qty"]; $ii++){ ?>

				<?php  } ?>
				</td>
			</tr>
			<?php if(substr($item[$i]["ca_id"], 0, 2) == 20){ ?>
				<tr class="tr-line">
					<td class="text-center" style="vertical-align: middle;"><span style="font-weight: bold; font-size: 12px;">대여금액(월)</span></td>
					<td colspan="7"><?=number_format($item[$i]["it_rental_price"])?>원</td>
				</tr>
				<tr class="tr-line ordLendFrm" style="display: none;">
					<td class="text-center" style="vertical-align: middle;"><span style="font-weight: bold; font-size: 12px;">대여기간</span></td>
					<td colspan="7">
						<input type="text" class="form-control input-sm ordLendDtmInput ordLendStartDtm dateonly" name="ordLendStartDtm_<?=$item[$i]["ct_id"]?>" style="width: 120px; float: left;" data-default="<?=date("Y-m-d")?>">
						<span style="width: 30px; height: 30px; line-height: 30px; float: left; text-align: center;">~</span>
						<input type="text" class="form-control input-sm ordLendDtmInput ordLendEndDtm" name="ordLendEndDtm_<?=$item[$i]["ct_id"]?>" style="width: 120px; float: left;" data-default="<?=date("Y-m-d", strtotime("+ 364 days"))?>" readonly>
						<a href="#" class="dateBtn" data-month="6">6개월</a>
						<a href="#" class="dateBtn" data-month="12">1년</a>
						<a href="#" class="dateBtn" data-month="24">2년</a>
					</td>
				</tr>
			<?php } ?>
			<tr class="tr-line">
				<td class="text-center" style="vertical-align: middle;"><span style="font-weight: bold; font-size: 12px;">요청사항</span></td>
				<td colspan="7">
					<input type="text" class="form-control input-sm" placeholder="추가 구매사항이나 상품관련 요청사항을 입력하세요." name="prodMemo_<?=$item[$i]["ct_id"]?>">
				</td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</div>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<?php if ($goods_count) $goods .= ' 외 '.$goods_count.'건'; ?>
	<script type="text/javascript">
		$(function(){

			$.datepicker.setDefaults({
				dateFormat : 'yy-mm-dd',
				prevText: '이전달',
				nextText: '다음달',
				monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
				monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
				dayNames: ["일", "월", "화", "수", "목", "금", "토"],
				dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
				dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
				showMonthAfterYear: true,
				changeMonth: true,
				changeYear: true
			});

			$(".dateonly").datepicker({
				onSelect : function(dateText){
					if($(this).hasClass("ordLendStartDtm")){
						var dateList = $(this).val().split("-");
						var date = new Date(dateList[0], dateList[1], dateList[2]);

						date.setDate(date.getDate() + 364);

						var year = date.getFullYear();
						var month = date.getMonth();
						var day = date.getDate();

						month = (month < 10) ? "0" + month : month;
						day = (day < 10) ? "0" + day : day;

						$(this).parent("td").find(".ordLendEndDtm").val(year + "-" + month + "-" + day);
					}
				}
			});

			$(".dateBtn").click(function(e){
				e.preventDefault();

				var month = Number($(this).attr("data-month"));
				var dateList = $(this).parent("td").find(".ordLendStartDtm").val().split("-");
				var date = new Date(dateList[0], dateList[1], dateList[2]);

				date.setMonth(date.getMonth() + month);
				date.setDate(date.getDate() - 1);

				var year = date.getFullYear();
				var month = date.getMonth();
				var day = date.getDate();

				month = (month < 10) ? "0" + month : month;
				day = (day < 10) ? "0" + day : day;

				$(this).parent("td").find(".ordLendEndDtm").val(year + "-" + month + "-" + day);
			});

			var optionCntList = <?=json_encode($optionCntList)?>; //아이템정보
			var optionBarList = <?=json_encode($optionBarList)?>; //바코드저오
			var prodItemList = $("#sod_list tr.item");            //아이템정보2
			$.each(prodItemList, function(key, itemDom){
				var code = $(itemDom).attr("data-code");
				var itemList = $(itemDom).find(".well li");

				$.each(itemList, function(subKey, subDom){
					var html = optionCntList[code][subKey];

					$(subDom).attr("data-bar-cnt", $(itemDom).find(".prodStockBarBox" + subKey).length);
					$(subDom).attr("data-stock-cnt", html);
					if(html){
						$(subDom).append(" <span class='stockCntStatusDom' style='opacity: 0.7; display: none;'>(보유 재고 : " + html + "개)</span>");
					}
				});
			});




			// //성훈20210306 바코드 동기화처리
			// $(document).on("click", ".prodBarSelectBox", function(){
			// 	var this_v=this; 						//this 정의
			// 	var this_v_v=this.value;
			// 	console.log(this_v.value);  //선택된 값
			// 	this_v.options.length=0;		//옵션 값 초기화
			// 	renew_array=renew_array2;		//renew_array박스(목록 초기화)
			// 	array_box=[];								//뺄 넣을 배열
			// 	var select_num = $("#renew_num_v option:selected").val();//재고소진 개수

			// 	//선택된 값 불러와서 뺄 배열에 넣기
			// 	for(var i=0; i<select_num; i++){
			// 			array_box.push(eval("document.getElementById('prodBarSelectBox_renew"+i+"').value"));
			// 	}

			// 	//기존배열 - 선택된값
			// 	for (var i = 0; i<array_box.length; i++) {
			// 	    var arrlen = renew_array.length;
			// 	    for (var j = 0; j<arrlen; j++) {
			// 	        if (array_box[i] == renew_array[j]) {
			// 	            renew_array = renew_array.slice(0, j).concat(renew_array.slice(j+1, arrlen));
			// 	        }
			// 	    }
			// 	}


			// 	$(this_v).append('<option>재고 바코드</option');//재고 바코드 추가

			// 	//기존 배열 -선택된 값 집어넣기
			// 	$.each(renew_array, function(key, value){
			// 		var selected="";
			// 		// console.log(this_v.value);
			// 		if(this_v_v == value){ selected = "selected"; }
			// 		$(this_v).append('<option value="' + value + '" '+selected+'>' + value + '</option');
			// 	});

			// });




			$(document).on("change", ".prodBarSelectBox", function(){
                var this_a=this;
                var this_v = $(this).val();
                var flag=false;
				if($(this).val()){
					var code = $(this).attr("data-code");
					var item = $(this).closest("tr").find(".prodBarSelectBox" + code);

                    var sendData2=[];
                    var prodsData = [];
                    var prodsSendData = [];
             
                    var it_id_class = $(this).closest("tr");
					prodsData["prodId"] = it_id_class.attr('data-code');
                    console.log(prodsData["prodId"]);
                    sendData2 = {
                        usrId : "<?=$member["mb_id"]?>",
                        prodId : prodsData["prodId"]
                    };
                    $.ajax({
                        url : "./ajax.stock.selectbarnumlist.php",
                        type : "POST",
                        async : false,
                        data : sendData2,
                        success : function(result){
                            result = JSON.parse(result);
                            console.log(result.data[0].prodBarNumList);

                            for(var i =0; i < result.data[0].prodBarNumList.length; i ++){
                                if(result.data[0].prodBarNumList[i] == this_v){
                                    alert("이미 등록된 바코드입니다.");
                                    $(this_a).val("");
                                    flag=true;
                                    return false;
                                }
                            }
                            
                        }
                    });
                    if(flag){ return false;}
					for(var i = 0; i < item.length; i++){
						if($(this).attr("data-this-code") != $(item[i]).attr("data-this-code")){
							if($(this).val() == $(item[i]).val()){
								alert("바코드는 중복선택하실 수 없습니다.");
								$(this).val("");
								return false;
							}
						}
					}
				}
			});

			$(document).on("change", ".recipientBox select", function(){
				if($(this).parent(".recipientBox").find("input[type='radio']:checked").attr("data-type") != "use"){
					return false;
				}

				var code = $(this).closest(".recipientBox").attr("data-code");
				var val = $(this).val();
				var item = $(this).closest("tr.item").find(".prodBarSelectBox" + code);
				var it_id = $(this).closest("tr.item").attr("data-code");

				for(var i = 0; i < item.length; i++){
					var name = $(item[i]).attr("name");
					var dataCode = $(item[i]).attr("data-code");
					var dataThisCode = $(item[i]).attr("data-this-code");
					var dataName = $(item[i]).attr("data-name");
					var html = "";

					if(i < val){
                        var html = '<select id="prodBarSelectBox_renew'+i+'" class="form-control input-sm prodBarSelectBox' + subKey + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							$.each(optionBarList[code][subKey], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
						html += '</select>';
					} else {
						html += '<input type="text" class="form-control input-sm prodBarSelectBox' + code + '" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">';
					}

					$(item[i]).after(html);
					$(item[i]).remove();
				}

				$.each(prodItemList, function(key, itemDom){
					var code = $(itemDom).attr("data-code");
					var itemList = $(itemDom).find(".well li");
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					$.each(itemList, function(subKey, subDom){
						if($(itemDom).attr("data-sup") == "Y"){
							var checkedType = $(subDom).find(".recipientBox input[type='radio']:checked").attr("data-type");

							if(checkedType == "use"){
								discountCnt += Number($(subDom).find(".recipientBox select").val());
							}
						}

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						if(checkedType == "use"){
							var stockCntItemCnt = Number($(subDom).find(".recipientBox select").val());
							stockCntItemCnt = (stockCntItemCnt) ? stockCntItemCnt : 0;
							$(stockCntItem[subKey]).val(stockCntItemCnt);
						} else {
							$(stockCntItem[subKey]).val(0);
						}
					});

					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price").text(number_format((cnt - discountCnt) * price) + "원");
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest("tr.item").attr("data-sup") == "Y"){
						totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
					}
				});

				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();
			});

			$(document).on("change", ".recipientBox input[type='radio']", function(){
				var code = $(this).closest(".recipientBox").attr("data-code");
				var parent = $(this).closest("tr.item");
				var type = $(this).attr("data-type");
				var item = $(parent).find(".prodBarSelectBox" + code);
				var it_id = $(parent).attr("data-code");

				switch(type){
					case "new" :
						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");

							$(item[i]).after('<input type="text" class="form-control input-sm prodBarSelectBox' + code + '" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">');

							$(item[i]).remove();
						}
						break;
					case "use" :
						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");

							//20210306 성훈수정(아래줄 id 추가)
							var html = '<select id="prodBarSelectBox_renew'+i+'" class="form-control input-sm prodBarSelectBox prodBarSelectBox' + code + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							$.each(optionBarList[it_id][code], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
							html += '</select>';

							$(item[i]).after(html);

							$(item[i]).remove();
						}

						$(this).closest(".recipientBox").find("select").val(item.length);
						break;
				}

				$.each(prodItemList, function(key, itemDom){
					var code = $(itemDom).attr("data-code");
					var itemList = $(itemDom).find(".well li");
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					$.each(itemList, function(subKey, subDom){
						if($(itemDom).attr("data-sup") == "Y"){
							var checkedType = $(subDom).find(".recipientBox input[type='radio']:checked").attr("data-type");

							if(checkedType == "use"){
								discountCnt += Number($(subDom).find(".recipientBox select").val());
							}
						}

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						if(checkedType == "use"){
							$(stockCntItem[subKey]).val(Number($(subDom).find(".recipientBox select").val()));
						} else {
							$(stockCntItem[subKey]).val(0);
						}
					});

					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price").text(number_format((cnt - discountCnt) * price) + "원");
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest("tr.item").attr("data-sup") == "Y"){
						totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
					}
				});

				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();
			});

		})
	</script>
	 <!-- 주문상품 정보 끝 -->

	<!-- 주문상품 합계 시작 -->
	<div class="well">
		<div class="row">
			<div class="col-xs-6">주문금액</div>
			<div class="col-xs-6 text-right">
				<strong id="printTotalCellPrice"><?php echo number_format($tot_sell_price); ?> 원</strong>
			</div>
			<div class="col-xs-6">할인금액</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($tot_sell_discount); ?> 원</strong>
			</div>
			<?php if($it_cp_count > 0) { ?>
				<div class="col-xs-6">쿠폰할인</div>
				<div class="col-xs-6 text-right">
					<strong id="ct_tot_coupon">0 원</strong>
				</div>
			<?php } ?>
			<div class="col-xs-6 delivery_cost_display">배송비</div>
			<div class="col-xs-6 text-right delivery_cost_display">
				<strong><?php echo number_format($send_cost); ?> 원</strong>
			</div>
		</div>

		<div class="row">
			<?php $tot_price = $tot_sell_price - $tot_sell_discount + $send_cost; // 총계 = 주문상품금액합계 - 묶음할인금액합계 + 배송비 ?>
			<div class="col-xs-6 red od_tot_price"> <b>합계금액</b></div>
			<div class="col-xs-6 text-right red od_tot_price">
				<strong id="ct_tot_price" class="print_price"><?php echo number_format($tot_price); ?> 원</strong>
			</div>
		</div>
	</div>
   <!-- 주문상품 합계 끝 -->

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
                            <label style="display: none;">
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
								$("#order_submitCheckBox").hide();
								$("#order_submitCheckBox").css("opacity", 1);

								$("#order_recipientBox").hide();
								$("#order_recipientBox").css("opacity", 1);
                            $("#order_recipient").on("click", function(e){
                                e.preventDefault();

								<?php if($itemPenIdStatus){ ?>
									$("#order_recipientBox").show();
								<?php } else { ?>
									<?php if($rentalItemCnt){ ?>
										<?php if($orderItemCnt){ ?>
											alert("판매/대여 상품 동시 주문 시 재고 주문이 포함된 경우 수급자 선택이 불가능합니다.");
										<?php } else { ?>
											alert("대여상품은 재고 확보 후 수급자 계약이 가능합니다.");
										<?php } ?>
									<?php } ?>
								<?php } ?>
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


<script>
<?php if($_GET['penId_r']){ //보유재고 관리에서 넘어오면 실행 ?>
        $(document).ready(function() { 
            selected_recipient('<?=$_GET['penId_r']?>');
            $('.prodBarSelectBox0 option[value="<?=$_GET['barcode_r']?>"]').attr('selected', 'selected');
        });
<?php } ?>
</script>
       