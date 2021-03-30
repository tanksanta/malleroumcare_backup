<?php

	include_once("./_common.php");
	$g5["title"] = "주문 내역 바코드 수정";
	// include_once(G5_ADMIN_PATH."/admin.head.php");

	//보유재고 리스트 보유재고 api 통신
	$sendLength = 5;
	$orderStoIdList = [];
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["prodId"] = $_GET['prodId'];
			
	if($_GET['stoId']){
		$sendData["stoId"] = $_GET['stoId'];
		array_push($orderStoIdList, $_GET['stoId']);
	}

	if($_GET["od_id"]){
		$stoIdList = sql_fetch("SELECT stoId FROM g5_shop_order WHERE od_id = '{$_GET["od_id"]}'")["stoId"];
		$stoIdList = explode(",", $stoIdList);
		foreach($stoIdList as $stoId){
			array_push($orderStoIdList, $stoId);
		}
	}

	// 01: 재고(대여가능) 02: 재고소진(대여중) 03: AS신청 04: 반품 05: 기타 06: 재고대기 07: 주문대기 08: 소독중 09: 대여종료
	$sendData["stateCd"] =['01','02','08','09'];
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectDetailList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);
	$list = [];
	if($res["data"]){
		$list = $res["data"];
	}

	$it = sql_fetch("SELECT * FROM g5_shop_item WHERE it_id = '{$_GET["prodId"]}'");

?>
<!DOCTYPE html>
 <html lang="ko">
 <head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>출고정보</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
	<link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">

	<style>
		* { margin: 0; padding: 0; position: relative; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
		html, body { width: 100%; float: left; font-family: "Noto Sans KR", sans-serif; }
		body { padding-top: 60px; }
		<?php if($it["prodSupYn"] == "N"){ ?>
		body { padding-bottom: 70px; }
		<?php } ?>
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
		.imfomation_box > a { width: 100%; float: left; cursor: default; }
		.imfomation_box > a > li { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DDD; }
		.imfomation_box a .li_box{ width:100%;  height:auto;text-align:center;}
		.imfomation_box a .li_box .li_box_line1{ width: 100%;  height:auto; margin:auto; float:left;color:#000; }
		.imfomation_box a .li_box .li_box_line1 .p1{ width:100%; float:left; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
		.imfomation_box a .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span1{ width: 100%; float: left; font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
		.imfomation_box a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
		.imfomation_box a .li_box .li_box_line1 .p1 .span3{ width: 100%; float: left; font-size: 14px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; color: #666; }
		.imfomation_box a .li_box .li_box_line1 .cartProdMemo { width: 100%; float: left; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
		/* display:none; */
		.imfomation_box a .li_box .folding_box{text-align: center; vertical-align:middle;width:100%; padding-top: 20px; display:none; float: left; box-sizing: border-box; }
		.imfomation_box a .li_box .folding_box > span { width: 100%; float: left; }
		.imfomation_box a .li_box .folding_box > .inputbox { width: 100%; float: left; position: relative; padding: 0; }
		.imfomation_box a .li_box .folding_box > .inputbox > li { width: 100%; float: left; position: relative; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input { width: 100%; height: 50px; float: left; padding-right: 85px; box-sizing: border-box; padding-left: 20px; font-size: 17px; border: 1px solid #E4E4E4; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > i { position: absolute; right: 55px; top: 17px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
		.imfomation_box a .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }

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
		<div class="title"><?=($it["prodSupYn"] == "N") ? "바코드 수정 입력" : "바코드정보"?></div>
		<div class="close">
			<a href="#" class="popupCloseBtn">
				&times;
			</a>
		</div>
	</div>
   <!-- 상품목록 -->

	<form id="submitForm">
		<input type="hidden" name="od_id" value="">
		<input type="hidden" name="update_type" value="popup">
		<ul class="imfomation_box" id="imfomation_box">
		<?php for($i = 0; $i < count($list); $i++){
			if(!in_array($list[$i]["stoId"], $orderStoIdList)){
				continue;
			}
		?>
			<a href="javascript:void(0)">
				<li class="li_box">
					<div class="li_box_line1">
						<p class="p1">
							<span class="span1">
								<?=$list[$i]['prodNm']?> <?php if($list[$i]['prodColor']||$list[$i]['prodSize']){ echo $list[$i]['prodColor'].'/'.$list[$i]['prodSize']; }else{ echo "(옵션 없음)"; } ?>
							</span>
							<span class="span3">
								<?php if($list[$i]['prodBarNum']){ ?>
								현재 바코드 : (<?=$list[$i]['prodBarNum']?>)
								<?php } ?>
							</span>
							<span class="span2">
								<!-- <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt=""> -->
								<!-- <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt=""> -->
							</span>
						</p>
					</div>
					<?php if($it["prodSupYn"] == "N"){ ?>
					<div class="folding_box">
						<ul class="inputbox">
								<li>
									<input type="number" maxlength="12" oninput="maxLengthCheck(this)" value="" class="notall frm_input  required " placeholder="수정하실 바코드를 입력하세요.">
									<i class="fa fa-check"></i>
									<!-- <img src="<?php echo G5_IMG_URL?>/bacod_img.png" class="nativePopupOpenBtn" data-code="<?=$b?>"> -->
								</li>
						</ul>
					</div>
					<?php } ?>
				</li>
			</a>
		<?php } ?>
		</ul>
	</form>
	</div>

	<?php if($it["prodSupYn"] == "N"){ ?>
	<!-- 고정 하단 -->
	<div id="popupFooterBtnWrap">
		<button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
		<button type="button" class="cancelbtn popupCloseBtn" onclick="closePopup();">취소</button>
	</div>
	<?php } ?>
<?php

if(!$member['mb_id']){alert('접근이 불가합니다.');}
//접속시 db- >id 부과
sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");

?>

<script type="text/javascript">


        //maxnum 지정
        function maxLengthCheck(object){
            if (object.value.length > object.maxLength){
            object.value = object.value.slice(0, object.maxLength);
            }
        }

		foldingBoxSetting();
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

		//재고 바코드 바꾸기
        $("#prodBarNumSaveBtn").click(function() {
			var stoldList = [];
			var count=0;
			var stoIdData = <?=json_encode($orderStoIdList)?>;
			var barcode_v = $(".notall");

			for(var i = 0; i < stoIdData.length; i++){
				var sendData = {
					stoId : stoIdData[i]
				}

				$.ajax({
					url : "https://eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
					type : "POST",
					dataType : "json",
					contentType : "application/json; charset=utf-8;",
					async : false,
					data : JSON.stringify(sendData),
					success : function(res){
						if(res.data){
							stoldList = res.data;
						}
						var prodsList = {};

						// if(barcode_v.length !== 12){
						// 	alert('바코드는 12자리를 입력하셔야합니다.'); return false;
						// }
						$.each(stoldList, function(key, value){
							prodsList[key] = {
								stoId : value.stoId,
								prodColor : value.prodColor,
								prodSize : value.prodSize,
								prodBarNum : $(barcode_v[i]).val(),
								prodManuDate : value.prodManuDate,
								stateCd : value.stateCd,
								stoMemo : (value.stoMemo) ? value.stoMemo : ""
							}
						});
						var sendData = {
							usrId : "<?=$member["mb_id"]?>",
							prods : prodsList
						}
						$.ajax({
							url : "./samhwa_orderform_stock_update.php",
							type : "POST",
							async : false,
							data : sendData,
							success : function(result){
								result = JSON.parse(result);
								if(result.errorYN == "Y"){
									alert(result.message);
									return false;
								}
							}
						});
					}
				});
			}

			alert("저장이 완료되었습니다.");

			$("#popupProdBarNumInfoBox", parent.document).hide();
			$("#popupProdBarNumInfoBox", parent.document).find("iframe").remove();
        });

	$(".notall").keyup(function(){
			$(this).removeClass("active");
			$(this).parent().find("i").removeClass("active");

			var length = $(this).val().length;
			if(length < 12 && length){
				$(this).addClass("active");
			}

			if(length == 12){
				$(this).parent().find("i").addClass("active");
			}
	});

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
		<?php if(is_mobile()){ ?>
			history.back();
		<?php }else{ ?>
			opener.location.reload();
			window.close();
		<?php }?>
    }

	$(".popupCloseBtn").click(function(e){
		e.preventDefault();

		$("#popupProdBarNumInfoBox", parent.document).hide();
		$("#popupProdBarNumInfoBox", parent.document).find("iframe").remove();
	});
</script>
    <!-- <hr color="#dddddd" size="1"> -->
 </body>
