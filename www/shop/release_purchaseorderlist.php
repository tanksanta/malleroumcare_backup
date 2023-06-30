<?php
include_once("./_common.php");

if (!check_auth($member['mb_id'], '400480', 'w')) {
  alert("이용권한이 없습니다.");
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>관리자 구매발주 관리</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="<?=G5_JS_URL?>/cookie.js"></script>
	<link type="text/css" rel="stylesheet" href="/thema/eroumcare/assetsㅇ/css/font.css">
	<link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">

	<style>
    * { margin: 0; padding: 0; position: relative; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
    html, body { width: 100%; float: left; font-family: "Noto Sans KR", sans-serif; }
    body { padding-top: 60px; }
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
    
    /* 검색 */
    #listSearchWrap { width: 100%; float: left; padding: 20px; padding-bottom: 0; }
    #listSearchWrap > ul { width: 100%; float: left; display: table; table-layout: fixed;margin-bottom:10px; }
    #listSearchWrap > ul > li { display: table-cell; vertical-align: middle; text-align: center; }
    #listSearchWrap > ul > li > input[type="text"] { width: 100%; height: 50px; float: left; text-align: center; border-radius: 5px; border: 1px solid #E0E0E0; font-size: 17px; }
    #listSearchWrap > ul > li > input[type="text"]::placeholder { color: #AAA; }
    #listSearchWrap > ul > li > button { width: 100%; height: 50px; float: left; border-radius: 5px; font-size: 16px; background-color: #333; color: #FFF; font-weight: bold; cursor: pointer; }

    /* 정렬 */
    #listSortWrap { width: 100%; line-height: 59px; float: left; border-bottom: 1px solid #DFDFDF; padding: 20px; }
    #listSortWrap > input[type="checkbox"] { display: none; }
    #listSortWrap > label { height: 20px; line-height: 20px; float: left; cursor: pointer; }
    #listSortWrap > label > .icon { display: inline-block; width: 14px; height: 14px; border: 1px solid #666; vertical-align: middle; top: -1px; margin-right: 5px; }
    #listSortWrap > label > .icon > i { position: absolute; left: 50%; top: 50%; margin-left: -6px; margin-top: -6px; font-size: 12px; color: #DC3333; opacity: 0; }
    #listSortWrap > label > .label { display: inline-block; font-size: 14px; color: #666; margin-right:10px;}
    #listSortWrap > input[type="checkbox"]:checked + label > .icon > i { opacity: 1; }
    
    /* 데이터목록 */
    #listDataWrap { width: 100%; float: left; }
    #listDataWrap > ul { width: 100%; float: left; padding: 25px 20px; border-bottom: 1px solid #E6E6E6; }
    #listDataWrap > ul.type1 { display: none; }
    #listDataWrap > ul > li { width: 100%; float: left; }
    #listDataWrap > ul > li.mainInfo { padding-right: 110px; }
    #listDataWrap > ul > li.mainInfo > p { width: 100%; float: left; }
    #listDataWrap > ul > li.mainInfo > .name { font-size: 17px; font-weight: bold; color: #000; }
    #listDataWrap > ul > li.mainInfo > .name > span{word-break: keep-all}
    /* #listDataWrap > ul > li.mainInfo > .name > span { float: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; } */
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
    #listDataWrap > ul > li.barInfo { height: 50px; line-height: 48px; border: 1px solid #DEDEDE; border-radius: 5px; text-align: center; margin-top: 15px; cursor: pointer; }
    #listDataWrap > ul > li.barInfo > .cnt { color: #666; font-weight: bold; font-size: 16px; }
    #listDataWrap > ul > li.barInfo > .label { position: absolute; height: 100%; right: 15px; top: 0; font-size: 12px; color: #FF690F; font-weight: bold; }
    #listDataWrap > ul > li.barInfo > .bc_warning {
      position: absolute;
      left: 15px;
      top: 9px;
      width: 31px;
      height: 31px;
      font-size: 20px;
      line-height: 30px;
      color: red;
      background: #ffff42;
      font-weight: bold;
      border: 1px solid red;
      border-radius: 100%;
    }
    #listDataWrap > ul > li.barInfo.active { border-color: #FF690F; }
    #listDataWrap > ul > li.barInfo.active > .cnt { color: #FF690F; }
    #listDataWrap > ul > li.barInfo.disable { border-color: #B8B8B8; background-color: #B8B8B8; }
    #listDataWrap > ul > li.barInfo.disable > .cnt { color: #FFF; }
    
    #search_option, #add_search_option{ width: 80px; height: 50px; float: left;  border-radius: 5px; border: 1px solid #E0E0E0; font-size: 14px; text-align:center;}
    #search_text, #add_search_text{width:calc(100% - 90px) !important;margin-left:10px;}

    #manager_option, #ct_status_option{ width: 150px; height: 50px; float: left;  border-radius: 5px; border: 1px solid #E0E0E0; font-size: 14px; text-align:center;}
    #ct_status_option{width: calc(100% - 160px);margin-left:10px;}

    .total_price_wrap { text-align: left !important; font-weight: bold; }
    #total_price { display: inline-block; }

    .nativeDeliveryPopupOpenBtn { display: inline-block; font-weight: bold; float: right; }
    .nativeDeliveryPopupOpenBtn img { display: inline-block; width: 30px; height: 30px; vertical-align: middle; }

    .samhwa_order_list_table_no_item { padding: 50px 0; }
    .samhwa_order_list_table_no_item h1 { font-size: 16px; font-weight: normal; color: #666; text-align: center;  }

    .date_button {
      height: 40px;
      line-height: 38px;
      border: 1px solid #DEDEDE;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      background: transparent;
      padding: 0 20px;
      color: #666;
      font-weight: bold;
    }
    .date_button:hover {
      background-color:#f4f4f4;
    }
	</style>
</head>
 
<body>

	<!-- 고정 상단 -->
	<div id="popupHeaderTopWrap">
    <div class="title">관리자 구매발주 관리</div>
    <div class="close">
    	<a href="<?=G5_URL?>">
        &times;
    	</a>
    </div>
	</div>

	<form name="release_search_form">
	<!-- 검색 -->
	<div id="listSearchWrap" style="border-bottom: 1px solid #dfdfdf; padding-bottom: 20px">
  <ul>
<!--    <li class="total_price_wrap">총 주문금액: <span id="total_price"></span></li>-->
    <?php
    $sql1 = "SELECT count(*) AS cnt FROM purchase_cart WHERE ct_status IN ('발주완료', '출고완료')";
    $sql2 = "SELECT ifnull(sum(ct_qty), 0) AS cnt FROM purchase_cart WHERE ct_status IN ('발주완료', '출고완료')";
    ?>
    <li class="total_price_wrap">입고대기: <?php echo sql_fetch($sql1)['cnt'] ?>건 주문 / <?php echo sql_fetch($sql2)['cnt'] ?>개 상품</li>
    <li>
      <a href="javascript:void(0);" class="nativeDeliveryPopupOpenBtn">
        발주서찾기
        <img src="<?=G5_IMG_URL?>/bacod_img.png">
      </a>
    </li>
  </ul>
  <ul>
      <li>
        <select name="ct_status_option" id="ct_status_option" style="margin-left: 0; width: 100%">
          <option value="발주완료,출고완료,입고완료" <?php echo !$ct_status_option ? 'selected' : ''; ?>>발주완료~입고완료</option>
          <option value="발주대기,발주완료,출고완료,입고완료" <?php echo $ct_status_option == '준비,출고준비,배송,완료' ? 'selected' : ''; ?>>전체</option>
          <option value="발주대기" <?php echo $ct_status_option == '준비' ? 'selected' : ''; ?>>발주대기</option>
        </select>
      </li>
    </ul>
    <ul>
      <li>
        <select name="search_option" id="search_option">
          <option value="it_name,ProdPayCode,od_name,od_id,ct_warehouse" <?php echo !$search_option ? "selected" : ''; ?>>전체</option>
          <option value="it_name" <?php echo $search_option == 'it_name' ? 'selected' : ''; ?>>상품명</option>
          <option value="ProdPayCode" <?php echo $search_option == 'ProdPayCode' ? 'selected' : ''; ?>>제품코드</option>
          <option value="od_name" <?php echo $search_option == 'od_name' ? 'selected' : ''; ?>>공급업체</option>
          <option value="od_id" <?php echo $search_option == 'od_name' ? 'selected' : ''; ?>>발주번호</option>
          <option value="ct_warehouse" <?php echo $search_option == 'od_name' ? 'selected' : ''; ?>>배송지명</option>
        </select>
        <input type="text" name="search_text" id="search_text" placeholder="검색명입력" value="<?php echo $search_text; ?>">
      </li>
    </ul>
    <ul>
      <li style="text-align: left">
        <span style="color:#666">
          <input type="radio" class="sel_date_time" name="sel_date_time" value="od_time" id="od_time" checked>
          <label for="od_time">발주일</label>
        </span>
        <span style="color:#666; margin-left: 10px;">
          <input type="radio" class="sel_date_time" name="sel_date_time" value="ct_delivery_expect_date" id="ct_delivery_expect_date">
          <label for="ct_delivery_expect_date">입고예정일</label>
        </span>
      </li>
    </ul>
    <ul>
      <li style="text-align: left">
        <input type="button" data-value="" class="date_button" value="전체" />
        <input type="button" data-value="<?php echo date('Y-m-d', time()); ?>" class="date_button" value="오늘" />
        <input type="button" data-value="<?php echo date("Y-m-d", strtotime("+1 day", strtotime(date('Y-m-d', time())))); ?>" class="date_button" value="내일" />
      </li>
    </ul>
    <ul>
    	<li>
        <input type="text" id="search_fr_date" name="search_fr_date" placeholder="시작일자" dateonly value="<?php echo $search_fr_date; ?>">
    	</li>
    	<li style="width: 25px;">
        <span>~</span>
    	</li>
    	<li>
        <input type="text" id="search_to_date" name="search_to_date" placeholder="종료일자" dateonly value="<?php echo $search_to_date; ?>">
    	</li>
    	<li style="width: 90px; padding-left: 20px;">
        <button type="button" id="searchSubmitBtn">검색</button>
    	</li>
    </ul>
	</div>

  </form>
    
  <!-- 데이터 목록 -->
  <div id="listDataWrap">
  </div>

  <input type="hidden" value="1" id="page">

  <script>
  var od_status = '';
  var od_step = "";
  var page2 = 1;
  var loading = false;
  var end = false;
  var sel_field = 'od_id';
  var sub_menu = '400402';
  var last_step = '완료';
  var sel_date_field = 'od_time';

  var formdata= {};
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

  // 출처: https://cofs.tistory.com/363 [CofS]

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

  //리스트 불러오기 ajax
  function doSearch(is_invoice_scan) {
    if(loading) return;

    formdata["fr_date"] = $("#search_fr_date").val();
    formdata["to_date"] = $("#search_to_date").val();
    formdata["manager_option"] = $("#manager_option").val();
    formdata["ct_status_option"] = $("#ct_status_option").val();
    formdata["search_option"] = $("#search_option").val();
    formdata["search_text"] = $("#search_text").val();
    formdata["add_search_option"] = $("#add_search_option").val();
    formdata["add_search_text"] = $("#add_search_text").val();
    formdata['page'] = page2;
    page2++;
    
    loading = true;
    $.ajax({
      method: "POST",
      url: "<?=G5_URL?>/adm/shop_admin/ajax.release_purchaseorderlist.php",
      data: formdata,
    })
    .done(function(result) {
      if(result.data) {
        var html = "";
        $('#total_price').text(result.total_price + '원');

        $.each(result.data, function(key, row) {
          html += '<ul>';
          html += '<li class="mainInfo">';
          html += '<p class="name">';
          html += '<span class="it_name">' + row.it_name + '</span>';
          // html += '<span class="delivery">(배송 : ' + row.delivery_cnt + '개)</span>';
          html += '</p>';
          html += '<p class="cnt">' + row.cnt_detail + '개 / ';
          html += '배송지 : ';
          if(row.ct_warehouse) {
            html += row.ct_warehouse;
          }
          html += '</p>';
          html += '<p class="cnt">'+row.od_id+'</p>';
          html += '<p class="date">발주: ' + row.date;
          if(row.od_b_name){
            html += " / " + row.od_b_name;
          }
          if(row.ct_delivery_expect_date.length > 10) {
            html += '<br/><span>입고예정: ' + row.ct_delivery_expect_date + '</span>'
          }
          html += '</p>';
          html += '<p class="cnt"> 공급업체 : ' + row.od_name + '</p>';
          html += '<p class="status ' + row.od_status_class + '">';
          html += '<span>' + row.od_status_name + '</span>';
          html += '</p>';
          html += '</li>';
          // html += '<li class="barInfo barcode_box ' + row.od_barcode_class + '" data-id="' + row.od_id + '" data-stock="2" data-it="'+row.ct_it_id+'"  data-option="'+row.ct_option+'" >';
          html += '<li class="barInfo ' + row.od_barcode_class + '" data-id="' + row.od_id + '" data-ct-id="'+row.ct_id + '" >';
          if (Number(row.bc_warning_count) > 0) {
            html += '<span class="bc_warning">!</span>';
          }
          html += '<span class="cnt">' + row.od_barcode_name + '</span>';
          if (row.edit_status) {
            html += '<span class="label">작업중</span>';
          }
          html += '</li>';
          html += '</ul>';
        });
    
        $("#listDataWrap").append(html);
        itNameSizeSetting();

        $('#page').val(parseInt($('#page').val()) + 1);
      } else {
        // alert('마지막 페이지입니다.');
        if(page2 == 2) {
          $('#listDataWrap').html(result.main);

          if(is_invoice_scan) {
            alert('해당 주문이 없습니다.');
            open_invoice_scan(); // 다시 스캔
          }
        }
      }
    })
    .fail(function() {
      console.log("ajax error");
    })
    .always(function() {
      loading = false;
    });
  }


  $(function() {
    $.datepicker.setDefaults({
      dateFormat : 'yy-mm-dd',
      prevText: '이전달',
      nextText: '다음달',
      monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      dayNames: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
      showMonthAfterYear: true,
      changeMonth: true,
      changeYear: true
    });
    $("input:text[dateonly]").datepicker();
    $("input:text[dateonly]").attr("readonly", "readonly");

    $(window).resize(function(){
      itNameSizeSetting();
    });

    $("#searchSubmitBtn").click(function() {
      $("#page").val(1);
      $("#listDataWrap").html("");
      page2 = 1;
      doSearch();
    });

    $(window).scroll(function() {
      if((window.innerHeight + window.scrollY) >= document.body.offsetHeight / 2) {
        doSearch();
      }
    });

    $("#searchSubmitBtn").trigger('click');
  });

  //바코드 버튼 클릭
  $(document).on("click", ".barInfo", function(e) {
    e.preventDefault();
    var id = $(this).attr("data-id");
    var ct_id = $(this).attr("data-ct-id");
    var od = $(this).attr("data-od");
    var it = $(this).attr("data-it");
    var stock = $(this).attr("data-stock");
    var option = encodeURIComponent($(this).attr("data-option"));

    var search_params = $("form[name='release_search_form']").serialize();

    $.ajax({
      url : "/shop/ajax.release_purchaseorderview.check.php",
      type : "POST",
      data : {
        ct_id : ct_id
      },
      success : function(result) {
        if(result.error == "Y") {
          if(confirm("작업중입니다. 무시하고 진행 시 이전 작업자는 작업이 종료됩니다. 무시하시겠습니까?")) {
            location.href="<?php echo G5_URL?>/adm/shop_admin/popup.release_purchaseorder_view.php?od_id="+ id+"&ct_id="+ct_id + "&" + search_params;
          }
        } else {
          location.href="<?php echo G5_URL?>/adm/shop_admin/popup.release_purchaseorder_view.php?od_id="+ id+"&ct_id="+ct_id + "&" + search_params;
        }
      }
    });
  });

  function sendInvoiceNum(text){
    text = text.slice(0, 12);
    $('#search_text').val(text);
    $('#search_option').val('ProdPayCode');
    $("#page").val(1);
    $("#listDataWrap").html("");
    page2 = 1;
    doSearch(true);
  }

  function open_invoice_scan() {
    /* 기종체크 */
    var deviceUserAgent = navigator.userAgent.toLowerCase();
    var device;

    if(deviceUserAgent.indexOf("android") > -1) {
      /* android */
      device = "android";
    }

    if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1) {
      /* ios */
      device = "ios";
    }

    // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
    if (window.ReactNativeWebView) {
      // WebView가 존재하는 경우에 대한 로직
      const url = `expo://BarCodeOpen/sendInvoiceNum`;
      window.location.href = url;
    } else {
      
      switch(device) {
        case "android" :
          /* android */
          window.EroummallApp.openInvoiceNum("");
          break;
        case "ios" :
          /* ios */
          window.webkit.messageHandlers.openInvoiceNum.postMessage("1");
          break;
      }

    }
  }

  $(".nativeDeliveryPopupOpenBtn").click(function(e) {
    e.preventDefault();

    open_invoice_scan();
  });

  $(".date_button").click(function(e) {
    e.preventDefault();

    var value = $(this).data('value');

    $('#search_fr_date').val(value);
    $('#search_to_date').val(value);
  });


  $(".sel_date_time").change(function(e) {
    sel_date_field = e.target.value;
    formdata['sel_date_field'] = e.target.value;
  });

  // 23.06.14 : input 엔터값 적용
  $(document).on("keyup", "#search_text", function(e) { if (e.key === 'Enter') { $("#searchSubmitBtn").click(); } });
  </script>
</body>
</html>