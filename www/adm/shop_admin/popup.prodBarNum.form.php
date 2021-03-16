<?php

	include_once("./_common.php");

	$g5["title"] = "주문 내역 바코드 수정";
	// include_once(G5_ADMIN_PATH."/admin.head.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$prodListCnt2 = 0;
	$deliveryTotalCnt = 0;

	if (!$od['od_id']) {
		alert("해당 주문번호로 주문서가 존재하지 않습니다.");
	} else {
		if($od["ordId"]){
			$sendData = [];
			$sendData["penOrdId"] = $od["ordId"];
			$sendData["uuid"] = $od["uuid"];

			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_PORT, 9001);
			curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/order/selectList");
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$res = curl_exec($oCurl);
			curl_close($oCurl);

			$result = json_decode($res, true);
			$result = $result["data"];

			if($result){
				foreach($result as $data){
					$thisProductData = [];

					$thisProductData["prodId"] = $data["prodId"];
					$thisProductData["prodColor"] = $data["prodColor"];
					$thisProductData["stoId"] = $data["stoId"];
					$thisProductData["prodBarNum"] = $data["prodBarNum"];
					$thisProductData["penStaSeq"] = $data["penStaSeq"];
					array_push($prodList, $thisProductData);
				}
			}
		} else {
			$stoIdData = $od["stoId"];
			$stoIdData = explode(",", $stoIdData);
			$stoIdDataList = [];
			foreach($stoIdData as $data){
				array_push($stoIdDataList, $data);
			}
			$stoIdData = implode("|", $stoIdDataList);
		}
	}

	// 상품목록
	$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
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
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					b.it_img1
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  group by a.it_id, a.ct_uid
			  order by a.ct_id ";

	$result = sql_query($sql);

	$carts = array();
	$cate_counts = array();

	for($i=0; $row=sql_fetch_array($result); $i++) {

		$cate_counts[$row['ct_status']] += 1;

		// 상품의 옵션정보
		$sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_stock_qty, ct_barcode, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, ct_sendcost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, ct_uid
						, ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
					from {$g5['g5_shop_cart_table']} MT
					where od_id = '{$od['od_id']}'
						and it_id = '{$row['it_id']}'
						and ct_uid = '{$row['ct_uid']}'
					order by io_type asc, ct_id asc ";
		$res = sql_query($sql);

		$row['options_span'] = sql_num_rows($res);

		$row['options'] = array();
		for($k=0; $opt=sql_fetch_array($res); $k++) {

			$opt_price = 0;

			if($opt['io_type'])
				$opt_price = $opt['io_price'];
			else
				$opt_price = $opt['ct_price'] + $opt['io_price'];

			$opt["opt_price"] = $opt_price;

			// 소계
			$opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
			$opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

			if($opt["prodSupYn"] == "Y"){
				$opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
			}

			$row['options'][] = $opt;
		}


		// 합계금액 계산
		$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
						SUM(ct_qty) as qty,
						SUM(ct_discount) as discount,
						SUM(ct_send_cost) as sendcost
					from {$g5['g5_shop_cart_table']}
					where it_id = '{$row['it_id']}'
						and od_id = '{$od['od_id']}'
						and ct_uid = '{$row['ct_uid']}'";
		$sum = sql_fetch($sql);

		$row['sum'] = $sum;

		$carts[] = $row;
	}

?>
<!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Document</title>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
 </head>
 <body>
	

<style>
    @font-face { font-family: 'NanumBarunGothic';
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot');
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot') format('embedded-opentype'),
    url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.woff') format('woff');}
    body { margin:0px; padding:0px; font-family: 'NanumBarunGothic', 'serif';}
    ul{list-style:none;}
    a { text-decoration:none } 
    .section1{ position:relative; width:100%; padding:0px;}
    .head{ position:relative; width:100%; background-color:#333333;height:60px; line-height:60px;color: #f1f1f1;}
    .head .p1{  float:left; margin-left:20px; font-size:30px; }
    .head .headsp{ margin-left:20px;font-size:15px;}
    .head .xbtn{ float:right;  margin-right:20px;color: #f1f1f1; font-size:40px; line-height:60px;}
    
    .naming_box{position:relative; width:100%; color: #333333; font-size:19px;height:70px;}
    .naming_box .sp1{ position:relative; left:20px; line-height:70px;}

    .imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto; }
    .imfomation_box a .li_box{ width:100%;  height:auto;text-align:center;}
    .imfomation_box a .li_box .li_box_line1{ width: 100%;  height:auto; margin:auto; float:left;color:#000;  border-top: 1px solid #dddddd;}
    .imfomation_box a .li_box .li_box_line1 .p1{ width:100%; height:70px%;  margin:auto; float:left; color:#000;line-height:70px; text-align:left;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span1{ width:400px; font-size:22px; margin-left:20px; float:left; overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:17px; float:right;margin-right:20px;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 img{ width: 15px;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    /* display:none; */
    .imfomation_box a .li_box .folding_box{text-align: center; vertical-align:middle;width:100%;margin-left:20px; display:none;}
    
    .imfomation_box a .li_box .folding_box .span{margin-left :20px;width:90%;}
    .imfomation_box a .li_box .folding_box .all{margin-bottom:5px;padding-left :20px;font-size:20px;text-align:left;float:left;height:50px;width:55%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px}
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:20px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:50px;}
    .imfomation_box a .li_box .folding_box .barNumGuideOpenBtn{float:left;margin-left:5px;width:37px; height:37px; padding-top:4px;}
    .imfomation_box a .li_box .folding_box .notall{margin-bottom:5px;font-size:20px;text-align:left;float:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;}
    .imfomation_box a .li_box .folding_box img{float:left;}

    /* .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:10px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } */
    /* .imfomation_box a .li_box .li_box_line1 .p1 .span1_2{ width:auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } */
    .bottom{ position:fixed; width:100%; height:70px;background-color:#000; bottom:0px; font-size:20px;max-width: 100%;}
    .bottom .savebtn{ float:left;width:75%; height:70px; background-color:#000; border:0px; color:#fff; font-size:20px;}
    .bottom .cancelbtn{ float:right; width:24%; height:70px;border:0px; color:#666666; background-color:#dddddd;font-size:20px;}

    /* 팝업 */
    #popup { display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, .7);z-index: 1; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);}
    #popup.hide {display: none;}
    #popup.multiple-filter { backdrop-filter: blur(4px) grayscale(90%); -webkit-backdrop-filter: blur(4px) grayscale(90%);}
    #popup .content { padding: 20px; background: #fff; border-radius: 5px; box-shadow: 1px 1px 3px rgba(0, 0, 0, .3); max-width:90%;}
    #popup .content { max-width:90%;}
    #popup .closepop {height: 2.5em; cursor: pointer; color:#fff; background-color:#000; border-radius:6px;}

    @media screen and (max-width: 4000px){
    }
    @media screen and (max-width: 1200px){
    }
    @media screen and (max-width: 1000px){
    }
    @media screen and (max-width: 900px){
    }
    @media screen and (max-width: 630px){
    }
    @media screen and (max-width: 500px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ width:250px;}
    }
}
 </style>

<?php

if(!$member['mb_id']){alert('접근이 불가합니다.');}
//접속시 db- >id 부과
sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");

?>
<script>
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
<section class="section1">
    <div class="head">
        <b class="p1">바코드입력 
        </b>
        <span  class="headsp">
                <?php if($od['od_edit_member']){ echo "(수정중 : ".$od['od_edit_member'].")";}?>
            </span>
        <a href="javascript:history.back();"><span class="xbtn">&times;</span></a>
    </div>

    <div class="naming_box">
        <span class="sp1">
            <span class="sp1_1">[재고]<?=$od_id?></span>
            <span class="sp1_2">(배송:<?=count($carts)+1?>개)</span>
        </span>
    </div>



    <ul class="imfomation_box" id="imfomation_box">
    <?php 
        for($i = 0; $i < count($carts); $i++){ 
            $options = $carts[$i]["options"];
            for($k = 0; $k < count($options); $k++){
    ?>
                <a href="javascript:void(0)">
                    <li class="li_box">
                        <div class="li_box_line1"   onclick="openCloseToc(this)">
                            <p class="p1">
                                <span class="span1">
                                    <!-- 상품명 -->
                                    <?=stripslashes($carts[$i]["it_name"])?>
                                    <!-- 옵션 -->
                                    <?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
                                            (<?=$options[$k]["ct_option"]?>)
                                    <?php } ?>
                                </span>
                                <span class="span2">
                                    <?php 
                                        $add_class="";
                                        for($b = 0; $b< $options[$k]["ct_qty"]; $b++){ 
                                            $add_class=$add_class.' '.$stoIdDataList[$prodListCnt2].'_v';
                                            $prodListCnt2++; 
                                        } 
                                    ?>
                                    <span class="<?=$add_class?>">0</span>/<?=$options[$k]["ct_qty"]?>
                                    <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt="">
                                    <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt="">
                                </span>
                            </p>
                        </div>

                        <div class="folding_box">
                                <span>
                                <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
                                <button type="button" class="barNumCustomSubmitBtn">등록</button>
                                <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
                                </span>
                                <div class="inputbox">
                                    <?php for($b = 0; $b< $options[$k]["ct_qty"]; $b++){ ?>
                                    <span class="">
                                    <input type="text" value="<?=$prodList[$b]["prodBarNum"]?>"class="notall frm_input required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>">
                                    </span>
                                    <img src="<?php echo G5_IMG_URL?>/bacod_img.png" alt="" onclick=“window.webkit.messageHandlers.openBarcode.postMessage(‘3’);>
                                    <?php $prodListCnt++; } ?>
                                </div>
                        </div>

                    </li>
                    
                </a>
                <?php
                }   
            }
            ?>
    <!-- 라인 -->
</ul>

<!-- <p class="bottom_line"></p> -->
<div class="bottom">
    <button class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button class="cancelbtn" onclick="member_cancel()">취소</button>
</div>

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
<!-- 팝업 -->
</section>


	<script type="text/javascript">
		$(function(){
			
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

					for(var i = 0; i < target.length; i++){
                        
                        $(target[i]).val(barList[i]);
                        if(barList[i].length!==12){
                            alert('바코드는 12자리 입력이 되어야합니다.'); 
                            target[i].focus();
                            return false;
                        }
					}
				}
			});




			$(".barNumGuideBox .closeBtn").click(function(){
				$(this).closest(".barNumGuideBox").hide();
			});

			$(".barNumGuideOpenBtn").click(function(){
				$(this).next().toggle();
			});



			var stoldList = [];
            var count=0;
			var stoIdData = "<?=$stoIdData?>";
			if(stoIdData){
				var sendData = {
					stoId : stoIdData
				}

				$.ajax({
					url : "https://eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
					type : "POST",
					dataType : "json",
					contentType : "application/json; charset=utf-8;",
					data : JSON.stringify(sendData),
					success : function(res){
                        console.log(res);
                        //here2
						$.each(res.data, function(key, value){
							$("." + value.stoId).val(value.prodBarNum);
                            if(value.prodBarNum){

                                // setTimeout(function(){
							    var number=$("." + value.stoId+"_v").html();
                                var number_v=parseInt(number)+1
							    $("." + value.stoId+"_v").html(number_v);
                                count++;
                                // },1000)
                            }
						});
						if(res.data){
							stoldList = res.data;
						}
					}
				});
			}
			
			$("#prodBarNumSaveBtn").click(function() {
				var ordId = "<?=$od["ordId"]?>";
				var changeStatus = true;
				var insertBarCnt = 0;

				if(ordId){
					var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
					$.each(productList, function(key, value){
						var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
						var prodBarNum = "";

						for(var i = 0; i < prodBarNumItem.length; i++){
							prodBarNum += (prodBarNum) ? "," : "";
							prodBarNum += $(prodBarNumItem[i]).val();
							
							if($(prodBarNumItem[i]).val()){
								insertBarCnt++;
							}
						}

						productList[key]["prodBarNum"] = prodBarNum;
					});

					var sendData = {
						usrId : "<?=$od["mb_id"]?>",
						penOrdId : "<?=$od["ordId"]?>",
						delGbnCd : "",
						ordWayNum : "",
						delSerCd : "",
						ordNm : $("#od_b_name").val(),
						ordCont : $("#od_b_hp").val(),
						ordMeno : $("#od_memo").val(),
						ordZip : $("#od_b_zip").val(),
						ordAddr : $("#od_b_addr1").val(),
						ordAddrDtl : $("#od_b_addr2").val(),
						eformYn : "<?=$od["eformYn"]?>",
						staOrdCd : "<?=$od["staOrdCd"]?>",
						lgsStoId : "",
						prods : productList
					}

					$.ajax({
						url : "./samhwa_orderform_order_update.php",
						type : "POST",
						async : false,
						data : sendData,
						success : function(result){
                            console.log(result);
							result = JSON.parse(result);
							if(result.errorYN == "Y"){
								alert(result.message);
							} else {
								alert("저장이 완료되었습니다.");
								
								$.ajax({
									url : "/shop/ajax.order.prodBarNum.cnt.php",
									type : "POST",
									async : false,
									data : {
										od_id : "<?=$od_id?>",
										cnt : insertBarCnt
									}
								});
                                member_cancel();
							}
						}
					});
				} else {
					var prodsList = {};

					$.each(stoldList, function(key, value){
						prodsList[key] = {
							stoId : value.stoId,
							prodColor : value.prodColor,
							prodSize : value.prodSize,
							prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
							prodManuDate : value.prodManuDate,
							stateCd : value.stateCd,
							stoMemo : (value.stoMemo) ? value.stoMemo : ""
						}
						
						if($("." + value.stoId).val()){
							insertBarCnt++;
						}
					});

					var sendData = {
						usrId : "<?=$od["mb_id"]?>",
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
							} else {
								alert("저장이 완료되었습니다.");
								
								$.ajax({
									url : "/shop/ajax.order.prodBarNum.cnt.php",
									type : "POST",
									async : false,
									data : {
										od_id : "<?=$od_id?>",
										cnt : insertBarCnt
									}
								});
                                
                                <?php if($_GET['new']){ ?>
                                    history.back();
                                <?php }else{ ?>
                                    opener.location.reload();
                                    window.close();
                                <?php }?>
							}
						}
					});
				}
			});
			
		})


        function member_cancel(){
            $.ajax({
                url : "/shop/ajax.order.prodBarNum.cnt.php",
                type : "POST",
                async : false,
                data : {
                    od_id : "<?=$od_id?>",
                    cancel : "y"
                },
                success : function(result){
                    <?php if($_GET['new']){ ?>
                        history.back();
                    <?php }else{ ?>
                        opener.location.reload();
                        window.close();
                    <?php }?>
                }
                });
        }
	</script>
    <!-- <hr color="#dddddd" size="1"> -->
 </body>