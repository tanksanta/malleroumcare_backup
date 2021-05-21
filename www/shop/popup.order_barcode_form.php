<?php

	include_once("./_common.php");

	$g5["title"] = "주문 내역 바코드 수정";

	$prodList = [];
	$prodListCnt = 0;
	$prodListCnt2 = 0;
	$deliveryTotalCnt = 0;

    try {
        $barcodes = explode('|', $barcodes);
    } catch(Exception $e) {
        $barcodes = array('');
    }

	// 상품목록
	$sql = " select 
					b.it_id,
					b.it_name,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					b.it_img1
			  from {$g5['g5_shop_item_table']} as b
			  where b.it_id = '$it_id'";

	$it = sql_fetch($sql);

	$carts = array();
	$cate_counts = array();

	$od_cart_count = 0;
?>
<!DOCTYPE html>
 <html lang="ko">
 <head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>바코드입력</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
	<link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">

	<style>
		* { margin: 0; padding: 0; position: relative; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
		html, body { width: 100%; float: left; font-family: "Noto Sans KR", sans-serif; }
		body { padding-top: 60px; padding-bottom: 70px; background: white; }
		a { text-decoration: none; color: inherit; }
		ul, li { list-style: none; }
		button { border: 0; font-family: "Noto Sans KR", sans-serif; }
		input { font-family: "Noto Sans KR", sans-serif;  }

		/* 고정 상단 */
		#popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
		#popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
		#popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
		#popupHeaderTopWrap > .close { float: right; }
		#popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

		/* 상품기본정보 */
		#itInfoWrap { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DFDFDF; }
		#itInfoWrap > .name { width: 100%; float: left; font-weight: bold; font-size: 17px; }
		#itInfoWrap > .name > .delivery { color: #FF690F; }
		#itInfoWrap > .date { width: 100%; float: left; font-size: 13px; color: #666; }
		#itInfoWrap > .deliveryInfo { width: 100%; float: left; border-radius: 5px; padding: 10px 15px; background-color: #F1F1F1; margin-top: 20px; }
		#itInfoWrap > .deliveryInfo > p { width: 100%; float: left; color: #000; font-size: 13px; }
		#itInfoWrap > .deliveryInfo > p.title { color: #666; font-size: 15px; font-weight: bold; margin-bottom: 10px; }

		/* 팝업 */
		#popup { display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, .7);z-index: 50; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);}
		#popup.hide {display: none;}
		#popup.multiple-filter { backdrop-filter: blur(4px) grayscale(90%); -webkit-backdrop-filter: blur(4px) grayscale(90%);}
		#popup .content { padding: 20px; background: #fff; border-radius: 5px; box-shadow: 1px 1px 3px rgba(0, 0, 0, .3); max-width:90%;}
		#popup .content { max-width:90%; font-size: 14px; }
		#popup .closepop { width: 100%; height: 40px; cursor: pointer; color:#fff; background-color:#000; border-radius:6px; margin-top: 10px; }

		/* 상품목록 */
		#submitForm { width: 100%; float: left; }
		.imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto; float: left; }
		.imfomation_box > a { width: 100%; float: left; }
		.imfomation_box > a > li { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DDD; }
		.imfomation_box a .li_box{ width:100%;  height:auto;text-align:center;}
		.imfomation_box a .li_box .li_box_line1{ width: 100%;  height:auto; margin:auto; float:left;color:#000; }
		.imfomation_box a .li_box .li_box_line1 .p1{ width:100%; float:left; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
		.imfomation_box a .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
		.imfomation_box a .li_box .li_box_line1 .cartProdMemo { width: 100%; float: left; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
		/* display:none; */
		.imfomation_box a .li_box .folding_box{text-align: center; vertical-align:middle;width:100%; padding-top: 20px; display:block; float: left; box-sizing: border-box; }
		.imfomation_box a .li_box .folding_box > span { width: 100%; float: left; }
		.imfomation_box a .li_box .folding_box > .inputbox { width: 100%; float: left; position: relative; padding: 0; }
		.imfomation_box a .li_box .folding_box > .inputbox > li { width: 100%; float: left; position: relative; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input { width: 100%; height: 50px; float: left; padding-right: 85px; box-sizing: border-box; padding-left: 20px; font-size: 17px; border: 1px solid #E4E4E4; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > i { position: absolute; right: 55px; top: 17px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .overlap { position: absolute; right: 55px; top: 15px; z-index: 2; font-size: 14px; color: #DC3333; opacity: 0; font-weight: bold; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .overlap.active { opacity: 1; }

		.imfomation_box a .li_box .folding_box .span{margin-left :20px;width:90%;}
		.imfomation_box a .li_box .folding_box .all{margin-bottom:5px;padding-left :20px;font-size:17px;text-align:left;float:left;height:50px;width:55%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px; box-sizing: border-box; }
		.imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}
		.imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

		.imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

		.imfomation_box a .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:17px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:50px; font-weight: bold; }
		.imfomation_box a .li_box .folding_box .barNumGuideOpenBtn{float:left;margin-left:10px;width:35px; cursor: pointer; top: 8px; }
		.imfomation_box a .li_box .folding_box .notall{
			margin-bottom:5px;font-size:20px;text-align:left;float:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;
			/* background-image : url('<?php echo G5_IMG_URL?>/bacod_img.png');  */
			/* background-position:top right;  */
			/* background-repeat:no-repeat; */


		}
		.imfomation_box a .li_box .deliveryInfoWrap { width: 100%; float: left; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
		.imfomation_box a .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
		.imfomation_box a .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
		.imfomation_box a .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }

		/* 고정 하단 */
		#popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
		#popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
		#popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
		#popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }
	</style>
 </head>

 <body>

 	<!-- 고정 상단 -->
	<div id="popupHeaderTopWrap">
		<div class="title">바코드입력</div>
		<!-- <div class="close">
			<a href="javascript:history.back();">
				&times;
			</a>
		</div> -->
	</div>

	<!-- 상품기본정보 -->
	<div id="itInfoWrap">
		<p class="name">
			[<?=($od["recipient_yn"] == "Y") ? "주문" : "재고"?>] <?php echo $it["it_name"]; ?>
			<span class="delivery">(총 <?php echo count($barcodes); ?>개)</span>
		</p>
	</div>

	<form id="submitForm">
		<input type="hidden" name="it_id" value="<?=$it_id?>">
		<input type="hidden" name="update_type" value="popup">
		<ul class="imfomation_box" id="imfomation_box">
            <a href="javascript:void(0)">
                <li class="li_box">
                    <div class="li_box_line1"   onclick="openCloseToc(this)">
                        <p class="p1">
                            <span class="span1">
                                <?=stripslashes($it["it_name"])?>
                            </span>
                            <span class="span2">
                                <span class="<?=$add_class?>">0</span>/<?php echo count($barcodes); ?>
                            </span>
                        </p>
                        <?php if($prodMemo){ ?>
                        <p class="cartProdMemo"><?=$prodMemo?></p>
                        <?php } ?>
                    </div>

                    <div class="folding_box">
                        <?php if(count($barcodes) >= 2){ ?>
                            <span>
                            <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
                            <button type="button" class="barNumCustomSubmitBtn">등록</button>
                            <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
                            </span>
                        <?php } ?>
                            <ul class="inputbox">
                                <?php for($b = 0; $b< count($barcodes); $b++){ ?>
                                    <li>
                                        <input type="number" maxlength="12" oninput="maxLengthCheck(this)" value="<?php echo $barcodes[$b]; ?>" class="notall frm_input frm_input_<?=$prodListCnt?> required barcode_input" placeholder="바코드를 입력하세요." data-frm-no="<?=$prodListCnt?>" maxlength="12">
                                        <i class="fa fa-check"></i>
                                        <span class="overlap">중복</span>
                                        <!-- <img src="<?php echo G5_IMG_URL?>/bacod_img.png" class="nativePopupOpenBtn" data-code="<?=$b?>"> -->
                                    </li>
                                <?php $prodListCnt++; } ?>
                            </ul>
                    </div>
                </li>
            </a>
		</ul>
	</form>

	<!-- 팝업 -->
	<div id="popup" class="hide">
	  <div class="content">
		<p>
			공통된 문자/숫자를 앞에 부여 후 반복되는 숫자를 입력합니다.<br><br>
			예시) 010101^3,4,5-10- 010101은 공동문자/숫자입니다.<br><br>
			- ^이후는 자동으로 입력하기 위한 내용입니다.<br>
			-    “숫자 입력 후 콤마(,)”를 입력하면 독립 숫자가 입력됩니다.<br>
			- 5-10이라고 입력하면5부터10까지 순차적으로 입력됩니다.<br>
			- 00-20으로 시작 숫자가00인 경우2자리 숫자로 입력됩니다
		</p>
		<button class="closepop" onclick="closePopup()">닫기</button>
	  </div>
	</div>

	<!-- 고정 하단 -->
	<div id="popupFooterBtnWrap">
		<button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
		<button type="button" class="cancelbtn" onclick="do_cancel();">취소</button>
	</div>

<?php

if(!$member['mb_id']){alert('접근이 불가합니다.');}
//접속시 db- >id 부과
sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");

?>

<script type="text/javascript">

	var $opener;
	var is_mobile = navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1;

    var it_id = '<?php echo $it_id; ?>';
    //maxnum 지정
    function maxLengthCheck(object){
        if (object.value.length > object.maxLength){
        object.value = object.value.slice(0, object.maxLength);
        }
    }

	/* 바코드 입력란 설정 */
	function foldingBoxSetting(){
		var item = $(".folding_box");
		for(var i = 0; i < item.length; i++){
			var openStatus = true;
			var notalls = $(item[i]).find(".notall");
			for(var n = 0; n < notalls.length; n++){
				if(!$(notalls[n]).val()){
					openStatus = false;
				}
			}

			if(!openStatus){
				$(item[i]).show();
				$(item[i]).parent().find(".p1 .span2 .up").css("display", "inline-block");
				$(item[i]).parent().find(".p1 .span2 .down").css("display", "none");
			}
		}
	}

	/* 바코드 입력글자 수 체크 */
	function notallLengthCheck(){
		var item = $(".notall");

		$(item).removeClass("active");
		$(".imfomation_box a .li_box .folding_box > .inputbox > li > i").removeClass("active");
		$(".imfomation_box a .li_box .folding_box > .inputbox > li > .overlap").removeClass("active");

		for(var i = 0; i < item.length; i++){
			var length = $(item[i]).val().length;
			if(length < 12 && length){
				$(item[i]).addClass("active");
			}

			if(length == 12){
				$(item[i]).parent().find("i").addClass("active");

				var index = $(item[i]).parent("li").index();
				var prodItem = $(item[i]).closest(".inputbox").find("li");
				for(var ii = 0; ii < prodItem.length; ii++){
					if($(prodItem[ii]).index() != index){
						if($(prodItem[ii]).find(".notall").val() == $(item[i]).val()){
							$(item[i]).parent().find("i").removeClass("active");
							$(item[i]).parent().find(".overlap").addClass("active");
						}
					}
				}
			}
		}
	}

	/* 기종체크 */
	var deviceUserAgent = navigator.userAgent.toLowerCase();
	var device;

	if(deviceUserAgent.indexOf("android") > -1){
		/* android */
		device = "android";
	}

	if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1){
		/* ios */
		device = "ios";
	}

	var sendBarcodeTargetList = [];
    function sendBarcode(text){
		$.ajax({
			url : "/shop/ajax.release_orderview.check.php",
			type : "POST",
			data : {
				od_id : "<?=$od_id?>"
			},
			success : function(result){
				if(result.error == "Y"){
					switch(device){
						case "android" :
							/* android */
							window.EroummallApp.closeBarcode("");
							break;
						case "ios" :
							/* ios */
							window.webkit.messageHandlers.closeBarcode.postMessage("");
							break;
					}
					window.location.href = "/shop/release_orderlist.php";
				} else {
					if(sendBarcodeTargetList[0]){
						var sendBarcodeTarget = $(".frm_input_" + sendBarcodeTargetList[0]);
						$(sendBarcodeTarget).val(text);
						sendBarcodeTargetList = sendBarcodeTargetList.slice(1);
					}
				}

				notallLengthCheck();
			}
		});
    }

	var sendInvoiceTarget;
    function sendInvoiceNum(text){
		$(sendInvoiceTarget).val(text);
    }

    $(function(){
		notallLengthCheck();

		$(".nativeDeliveryPopupOpenBtn").click(function(){
			sendInvoiceTarget = $(this).parent().find("input[type='text']");

			switch(device){
				case "android" :
					/* android */
					window.EroummallApp.openInvoiceNum("");
					break;
				case "ios" :
					/* ios */
					window.webkit.messageHandlers.openInvoiceNum.postMessage("1");
					break;
			}
		});

		$(".notall").keyup(function(){
			notallLengthCheck();
		});

        var stoldList = [];
        var count=0;
        var stoIdData = "<?=$stoIdData?>";
        if(stoIdData){
            var sendData = {
                stoId : stoIdData
            }

            $.ajax({
                url : "https://system.eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
                type : "POST",
                dataType : "json",
                contentType : "application/json; charset=utf-8;",
                data : JSON.stringify(sendData),
                success : function(res){
                    console.log(res);
                    //here2
                    $.each(res.data, function(key, value){
                        $("." + value.stoId).val(value.prodBarNum);
                        //완료된 숫자 세고 집어넣기
                        if(value.prodBarNum){
                            var number=$("." + value.stoId+"_v").html();
                            var number_v=parseInt(number)+1
                            $("." + value.stoId+"_v").html(number_v);
                            count++;
                        }
                    });

                    console.log(res.data);

                    if(res.data){
                        stoldList = res.data;
                    }

					notallLengthCheck();
					foldingBoxSetting();
                }
            });
        } else {
			foldingBoxSetting();
		}

        function hasDuplicates(array) { 
            var valuesSoFar = []; 
            var falseLine=0;
            for (var i = 0; i < array.length; ++i) { 
            var value = array[i]; 
            if (valuesSoFar.indexOf(value) !== -1) { 
                return value; 
            } 
            valuesSoFar.push(value); 
            } 
            return false; 
        }

        // 저장
        $("#prodBarNumSaveBtn").click(function() {
			$opener = is_mobile ? window.parent : (window.opener || window.open('', 'barcode_parent'));

            var parent = $opener.$('.list.item[data-code='+ it_id +']');
            var inputs = $(parent).find('.barcode_input');
            var button = $(parent).find('.open_input_barcode');

            var barcodes = [];

            if (!inputs || !inputs.length) {
                alert('바코드를 등록할 수 없습니다.');
                return;
            }

            var count = 0;

            $('.barcode_input').each(function(i, item) {
                var val = $(item).val();
                $(inputs[i]).val(val);
                barcodes.push(val);

                if (val) count++;
            }).promise().done(function() {
                $(button).html('바코드 (' + count + '/' + inputs.length + ')');

                var dup = hasDuplicates(barcodes);
                if (dup) {
                    alert('중복된 값(' + dup + ')이 있습니다.');
                    return false;
                }

                alert('적용되었습니다.');

				if (is_mobile) {
					$opener.$('#barcode_popup_iframe').hide();
					return;
				}
                window.self.close();
				
            });
        });


         //넘버 검사
         $(".barNumCustomSubmitBtn").click(function(){
            var val = $(this).closest(".folding_box").find(".all").val();
            var target = $(this).closest(".folding_box").find(".notall");
            var barList = [];

            if(val.indexOf("^") == -1){
                alert("내용을 입력해주시길 바랍니다.");
                return false;
            }

            for(var i = 0; i < target.length; i++){
                if(i > 0){
                    if($(target[i]).find("input").val()){
                        if(!confirm("이미 등록된 바코드가 있습니다.\n무시하고 적용하시겠습니까?")){
                            return false;
                        } else {
                            break;
                        }
                    }
                }
            }
            if(val){
                val = val.split("^");
                var first = val[0];
                var secList = val[1].split(",");
                for(var i = 0; i < secList.length; i++){
                    if(secList[i].indexOf("-") == -1){
                        barList.push(first + secList[i]);
                    } else {
                        var secData = secList[i].split("-");
                        var secData0Len = secData[0].length;
                        secData[0] = Number(secData[0]);
                        secData[1] = Number(secData[1]);

                        for(var ii = secData[0]; ii < (secData[1] + 1); ii++){
                            var barData = ii;
                            if(String(barData).length < secData0Len){
                                var iiiCnt = secData0Len - String(barData).length;
                                for(var iii = 0; iii < iiiCnt; iii++){
                                    barData = "0" + barData;
                                }
                            }

                            barList.push(first + barData);
                        }
                    }
                }

					notallLengthCheck();
                for(var i = 0; i < target.length; i++){

                    $(target[i]).val(barList[i]);
                    if(barList[i].length!==12){
                        alert('바코드는 12자리 입력이 되어야합니다.');
                        target[i].focus();
                        return false;
                    }
                }
            }

			 notallLengthCheck();
        });




        $(".barNumGuideBox .closeBtn").click(function(){
            $(this).closest(".barNumGuideBox").hide();
        });

        $(".barNumGuideOpenBtn").click(function(){
            $(this).next().toggle();
        });

		/* 210317 */
		$(".nativePopupOpenBtn").click(function(e){
			var cnt = 0;
			var frm_no = $(this).closest("li").find(".frm_input").attr("data-frm-no");
			var item = $(this).closest("ul").find(".frm_input");
			sendBarcodeTargetList = [];

			for(var i = 0; i < item.length; i++){
				if(!$(item[i]).val() || $(item[i]).attr("data-frm-no") == frm_no){
					sendBarcodeTargetList.push($(item[i]).attr("data-frm-no"));
					cnt++;
				}
			}

			switch(device){
				case "android" :
					/* android */
					window.EroummallApp.openBarcode("" + cnt + "");
					break;
				case "ios" :
					/* ios */
					window.webkit.messageHandlers.openBarcode.postMessage("" + cnt + "");
					break;
			}
		});

    })

    function do_cancel(){
		if (is_mobile) {
			$opener = is_mobile ? window.parent : (window.opener || window.open('', 'barcode_parent'));
			$opener.$('#barcode_popup_iframe').hide();
			return;
		}
        window.close();
    }

    function openCloseToc(click) {
      if($(click).closest('li').children('.folding_box').css("display")=="none"){
            $(click).closest('li').children('.folding_box').css("display", "block");
            $(click).find('.p1 .span2 .up').css("display", "inline-block");
            $(click).find('.p1 .span2 .down').css("display", "none");
      }else{
            $(click).closest('li').children('.folding_box').css("display", "none");
            $(click).find('.p1 .span2 .up').css("display", "none");
            $(click).find('.p1 .span2 .down').css("display", "inline-block");
      }
  }

    // 팝업열기
    function showPopup(multipleFilter) {
    const popup = document.querySelector('#popup');

        if (multipleFilter) {
            popup.classList.add('multiple-filter');
        } else {
            popup.classList.remove('multiple-filter');
        }

        popup.classList.remove('hide');
    }

    // 팝업닫기
    function closePopup() {
        const popup = document.querySelector('#popup');
        popup.classList.add('hide');
    }
</script>
    <!-- <hr color="#dddddd" size="1"> -->
 </body>
