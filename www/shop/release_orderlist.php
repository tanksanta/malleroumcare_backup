<?php

		include_once("./_common.php");
		$g5["title"] = "주문 내역 바코드 수정";
		$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
		$od = sql_fetch($sql);
		$prodList = [];
		$prodListCnt = 0;
		$deliveryTotalCnt = 0;
		if($member['mb_level']< 9){alert("이용권한이 없습니다.");}
		$sub_menu = '400402';

 ?>
 <!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>출고목록</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
	<link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">

	<style>
		* { margin: 0; padding: 0; position: relative; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); }
		html, body { width: 100%; float: left; font-family: "Noto Sans KR", sans-serif; }
		body { padding-top: 60px; }
		a { text-decoration: none; color: inherit; }
		ul, li { list-style: none; }

		/* 고정 상단 */
		#popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
		#popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
		#popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
		#popupHeaderTopWrap > .close { float: right; }
		#popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

		/* 정렬 */
		#listSortWrap { width: 100%; height: 60px; line-height: 59px; float: left; border-bottom: 1px solid #DFDFDF; padding: 0 20px; }
		#listSortWrap > input[type="checkbox"] { display: none; }
		#listSortWrap > label { display: inline-block; cursor: pointer; }
		#listSortWrap > label > .icon { display: inline-block; width: 14px; height: 14px; border: 1px solid #666; vertical-align: middle; top: -1px; margin-right: 5px; }
		#listSortWrap > label > .icon > i { position: absolute; left: 50%; top: 50%; margin-left: -6px; margin-top: -6px; font-size: 12px; color: #DC3333; opacity: 0; }
		#listSortWrap > label > .label { display: inline-block; font-size: 14px; color: #666; }
		#listSortWrap > input[type="checkbox"]:checked + label > .icon > i { opacity: 1; }
		
		/* 데이터목록 */
		#listDataWrap { width: 100%; float: left; }
		#listDataWrap > ul { width: 100%; float: left; padding: 25px 20px; border-bottom: 1px solid #E6E6E6; }
		#listDataWrap > ul.type1 { display: none; }
		#listDataWrap > ul > li { width: 100%; float: left; }
		#listDataWrap > ul > li.mainInfo { padding-right: 110px; }
		#listDataWrap > ul > li.mainInfo > p { width: 100%; float: left; }
		#listDataWrap > ul > li.mainInfo > .name { font-size: 17px; font-weight: bold; color: #000; }
		#listDataWrap > ul > li.mainInfo > .name > span { float: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
		#listDataWrap > ul > li.mainInfo > .name > span.delivery { color: #FF690F; padding-left: 5px; }
		#listDataWrap > ul > li.mainInfo > .cnt { font-size: 13px; color: #999; margin-top: 2px; }
		#listDataWrap > ul > li.mainInfo > .date { font-size: 13px; color: #999; margin-top: 20px; }
		#listDataWrap > ul > li.mainInfo > .status { position: absolute; width: 70px; height: 100%; top: 0; right: 0; display: table; table-layout: fixed; border-radius: 5px; background-color: #CCC; }
		#listDataWrap > ul > li.mainInfo > .status > span { width: 100%; height: 100%; display: table-cell; vertical-align: middle; font-size: 16px; color: #FFF; text-align: center; font-weight: bold; line-height: 19px; }
		#listDataWrap > ul > li.mainInfo > .status.type1{ background-color: #79AD14; } /* 입금완료 */
		#listDataWrap > ul > li.mainInfo > .status.type2{ background-color: #36830E; } /* 상품준비 */
		#listDataWrap > ul > li.mainInfo > .status.type3{ background-color: #36A6DE; } /* 출고준비 */
		#listDataWrap > ul > li.mainInfo > .status.type4{ background-color: #28759C; } /* 출고완료 */
		#listDataWrap > ul > li.mainInfo > .status.type5{ background-color: #372573; } /* 배송완료 */
		#listDataWrap > ul > li.mainInfo > .status.type6{ background-color: #646464; } /* 주문취소 */
		#listDataWrap > ul > li.mainInfo > .status.type7{ background-color: #2E427E; } /* 주문무효 */
		#listDataWrap > ul > li.barInfo { height: 50px; line-height: 48px; border: 1px solid #DEDEDE; border-radius: 5px; text-align: center; margin-top: 15px; }
		#listDataWrap > ul > li.barInfo > .cnt { color: #666; font-weight: bold; font-size: 16px; }
		#listDataWrap > ul > li.barInfo > .label { position: absolute; height: 100%; right: 15px; top: 0; font-size: 12px; color: #FF690F; font-weight: bold; }
		#listDataWrap > ul > li.barInfo.active { border-color: #FF690F; }
		#listDataWrap > ul > li.barInfo.active > .cnt { color: #FF690F; }
		#listDataWrap > ul > li.barInfo.disable { border-color: #B8B8B8; background-color: #B8B8B8; }
		#listDataWrap > ul > li.barInfo.disable > .cnt { color: #FFF; }
	</style>
</head>
 
 <body>
 
	<!-- 고정 상단 -->
	<div id="popupHeaderTopWrap">
		<div class="title">출고리스트</div>
		<div class="close">
			<a href="javascript:history.back();">
				&times;
			</a>
		</div>
	</div>
 	
 	<!-- 정렬 -->
 	<div id="listSortWrap">
 		<input type="checkbox" id="cf_flag" checked>
 		<label for="cf_flag">
 			<span class="icon">
 				<i class="fa fa-check"></i>
 			</span>
 			<span class="label">미완성 바코드 작성만 보기</span>
 		</label>
 	</div>
 	
 	<!-- 데이터 목록 -->
 	<div id="listDataWrap">
 	</div>
 	
 	<input type="hidden" value="1" id="page">

<script>
	/* 210317 아이템 이름 넓이 조정 */
	function itNameSizeSetting(){
		var item = $("#listDataWrap > ul");
		for(var i = 0; i < item.length; i++){
			$(item[i]).find(".mainInfo > .name > .it_name").css("width", "");
			var wrapWidth = $(item[i]).find(".mainInfo > .name").outerWidth();
			var deliveryCntWidth = $(item[i]).find(".mainInfo > .name > .delivery").outerWidth();
			var itNameWidth = $(item[i]).find(".mainInfo > .name > .it_name").outerWidth();
			
			if(wrapWidth < (deliveryCntWidth + itNameWidth)){
				itNameWidth = wrapWidth - deliveryCntWidth - 2;
				
				$(item[i]).find(".mainInfo > .name > .it_name").css("width", itNameWidth + "px");
			}
			
			var wrapHeight = $(item[i]).find(".mainInfo").outerHeight();
			$(item[i]).find(".mainInfo > .status").css("height", wrapHeight + "px")
		}
	}
	itNameSizeSetting();
	
    var od_status = '';
    var od_step = "";
    var page= parseInt(document.getElementById('page').value);
    var loading = false;
    var end = false;
    var sel_field = 'od_id';
    var sub_menu = '400402';
    var last_step = '완료';
    var sel_date_field = 'od_time';

    var formdata= {};
    formdata['fr_date'] = "";
    formdata['last_step'] = "";
    formdata['od_important'] = "";
    formdata['od_release'] = "";
    formdata['od_status'] = "";
    formdata['od_step'] = "";
    formdata['search'] = "";
    formdata['sel_date_field'] = "od_time";
    formdata['sel_field'] = "od_id";
    formdata['sub_menu'] = "400402";
    formdata['to_date'] = "";
    doSearch();


    //리스트 불러오기 ajax
    function doSearch(){
        formdata['cf']=document.getElementById('cf_flag').checked;
        formdata['page']=parseInt(document.getElementById('page').value);
        $.ajax({
            method: "POST",
            url: "<?=G5_URL?>/adm/shop_admin/ajax.release_orderlist.php",
            data: formdata,
        })
        .done(function(result){
            if(result.data){
					var html = "";
				
					$.each(result.data, function(key, row){
						html += '<ul class="' + row.complate_flag + ' ' + row.complate_flag2 + '">';
						html += '<li class="mainInfo">';
						html += '<p class="name">';
						html += '<span class="it_name">' + row.it_name + '</span>';
						html += '<span class="delivery">(배송 : ' + row.delivery_cnt + '개)</span>';
						html += '</p>';
						html += '<p class="cnt">' + row.cnt_detail + '</p>';
						html += '<p class="date">' + row.date + ' / ' + row.od_name + '</p>';
						html += '<p class="status ' + row.od_status_class + '">';
						html += '<span>' + row.od_status_name + '</span>';
						html += '</p>';
						html += '</li>';
						html += '<li class="barInfo barcode_box ' + row.od_barcode_class + '" data-id="' + row.od_id + '">';
						html += '<span class="cnt">' + row.od_barcode_name + '</span>';
						if(row.edit_status){
							html += '<span class="label">작업중</span>';
						}
						html += '</li>';
						html += '</ul>';
					});
				
					$("#listDataWrap").append(html);
					cf_flag();
				
                document.getElementById("page").value = parseInt(document.getElementById("page").value) + 1;
            }else{
                // alert('마지막 페이지입니다.');
            }
        })
        .fail(function() {
            console.log("ajax error");
        })
        .always(function() {
            loading = false;
        });
    }


    $( document ).ready(function() {
		
		$(window).resize(function(){
			itNameSizeSetting();
		});
        
        $(window).scroll(function() {
            // alert($(window).scrollTop()%100)
            if($(window).scrollTop()%100){
                doSearch();
            }
            // alert($(document).height() - $(window).height());
            // alert($(document).height() - $(window).height()-100);
            // if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            //     doSearch();
            // }
        });
		
    });


    // $( document ).ready(function() {
    //     $(document).scroll(function(){
    //         var max_height = $(document).height();
    //         var now_height = $(window).scrollTop() + $(window).height();
    //         //끝에 닿기전에 미리 함수실행
    //         if((max_height <= now_height + 100) && _temp == 1)
    //         {
    //             doSearch();
    //         }
    //     });
    // });
    //미완료 바코드 작성만보기버튼
    function cf_flag(){ 
        if(document.getElementById('cf_flag').checked){
            $("#listDataWrap > ul.cf").addClass("type1");
            $("#listDataWrap > ul.cf").removeClass("type2");
            
        }else{
            $("#listDataWrap > ul.cf").addClass("type2");
            $("#listDataWrap > ul.cf").removeClass("type1");
        }
		
		itNameSizeSetting();
    }
	
	$("#cf_flag").change(function(){
		cf_flag();
	});

    //바코드 버튼 클릭
    $(document).on("click", ".barcode_box", function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		
		$.ajax({
			url : "/shop/ajax.release_orderview.check.php",
			type : "POST",
			data : {
				od_id : id
			},
			success : function(result){
				if(result.error == "Y"){
					if(confirm("작업중입니다. 무시하고 진행 시 이전 작업자는 작업이 종료됩니다. 무시하시겠습니까?")){
						location.href="<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form.php?od_id="+ id+"&new=1";
					}
				} else {
					location.href="<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form.php?od_id="+ id+"&new=1";
				}
			}
		});
	});

    //x 버튼 이동
    // function x_btn(){
    //     history.pushState(null, null, "#noback");

    //         $(window).bind("hashchange", function(){

    //             history.pushState(null, null, "#noback");

    //             alert(1);

    //         });
    // }
</script>
 </body>
 </html>