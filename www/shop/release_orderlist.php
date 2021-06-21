<?php
include_once("./_common.php");
$g5["title"] = "주문 내역 바코드 수정";
$prodList = [];
$prodListCnt = 0;
$deliveryTotalCnt = 0;
if($member['mb_level']< 9){alert("이용권한이 없습니다.");}
$sub_menu = '400402';
// alert('준비중입니다.',G5_URL);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>출고목록</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="<?=G5_JS_URL?>/cookie.js"></script>
	<link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
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
    #listSortWrap > #listSortChangeBtn { height: 20px; line-height: 20px; float: right; border: 0; border-bottom: 1px solid #666; background-color: #FFF; cursor: pointer; }
    #listSortWrap > #listSortChangeBtn > span { float: left; font-size: 13px; color: #666; font-weight: bold; display: none; }
    #listSortWrap > #listSortChangeBtn > span.active { display: block; }
    
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
    #listDataWrap > ul > li.barInfo { height: 50px; line-height: 48px; border: 1px solid #DEDEDE; border-radius: 5px; text-align: center; margin-top: 15px; cursor: pointer; }
    #listDataWrap > ul > li.barInfo > .cnt { color: #666; font-weight: bold; font-size: 16px; }
    #listDataWrap > ul > li.barInfo > .label { position: absolute; height: 100%; right: 15px; top: 0; font-size: 12px; color: #FF690F; font-weight: bold; }
    #listDataWrap > ul > li.barInfo.active { border-color: #FF690F; }
    #listDataWrap > ul > li.barInfo.active > .cnt { color: #FF690F; }
    #listDataWrap > ul > li.barInfo.disable { border-color: #B8B8B8; background-color: #B8B8B8; }
    #listDataWrap > ul > li.barInfo.disable > .cnt { color: #FFF; }
    
    #search_option{ width: 80px; height: 50px; float: left;  border-radius: 5px; border: 1px solid #E0E0E0; font-size: 14px; text-align:center;}
    #search_text{width:calc(100% - 90px) !important;margin-left:10px;}
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
	
	<!-- 검색 -->
	<div id="listSearchWrap">
    <ul>
      <li>
        <select name="search_option" id="search_option">
          <!-- <option value="">선택하세요</option> -->
          <option value="od_b_name" selected>수화인</option>
          <option value="it_name">상품명</option>
          <option value="od_name">사업소명</option>
        </select>
        <input type="text" name="search_text" id="search_text" placeholder="검색명입력" >
      </li>
    </ul>
    <ul>
    	<li>
        <input type="text" id="search_fr_date" placeholder="시작일자" dateonly>
    	</li>
    	<li style="width: 25px;">
        <span>~</span>
    	</li>
    	<li>
        <input type="text" id="search_to_date" placeholder="종료일자" dateonly>
    	</li>
    	<li style="width: 90px; padding-left: 20px;">
        <button type="button" id="searchSubmitBtn">검색</button>
    	</li>
    </ul>
	</div>
    
  <!-- 정렬 -->
  <div id="listSortWrap">
    <input type="checkbox" id="cf_flag" <?php if($_COOKIE['cf_flag'] == 'true') echo 'checked'; ?>>
    <label for="cf_flag">
      <span class="icon">
        <i class="fa fa-check"></i>
      </span>
      <span class="label">바코드 등록 미완료 만 보기</span>
    </label>

    <input type="checkbox" id="cf_flag2" <?php if($_COOKIE['cf_flag2'] == 'true') echo 'checked'; ?>>
    <label for="cf_flag2">
      <span class="icon">
        <i class="fa fa-check"></i>
      </span>
      <span class="label">내 담당만 보기</span>
    </label>

    <input type="checkbox" id="cf_flag3" <?php if($_COOKIE['cf_flag3'] == 'true') echo 'checked'; ?>>
    <label for="cf_flag3">
      <span class="icon">
        <i class="fa fa-check"></i>
      </span>
      <span class="label">미지정만 보기</span>
    </label>

    <button type="button" id="listSortChangeBtn">
      <span class="active" data-sort="od_time"></span>
      <span data-sort="od_status">상태 정렬↓</span>
    </button>
  </div>
    
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
  function doSearch(){
    if(loading) return;

    formdata["fr_date"] = $("#search_fr_date").val();
    formdata["to_date"] = $("#search_to_date").val();
    formdata["search_option"] = $("#search_option").val();
    formdata["search_text"] = $("#search_text").val();
    formdata["cust_sort"] = $("#listSortChangeBtn").find(".active").attr("data-sort");
    formdata['cf']=document.getElementById('cf_flag').checked;
    // formdata['page']=parseInt(document.getElementById('page').value);
    formdata['page'] = page2;
    page2++;
    
    loading = true;
    $.ajax({
      method: "POST",
      url: "<?=G5_URL?>/adm/shop_admin/ajax.release_orderlist.php",
      data: formdata,
    })
    .done(function(result) {
      if(result.data) {
        var html = "";
        $.each(result.data, function(key, row) {
          html += '<ul>';
          html += '<li class="mainInfo">';
          html += '<p class="name">';
          html += '<span class="it_name">' + row.it_name + '</span>';
          // html += '<span class="delivery">(배송 : ' + row.delivery_cnt + '개)</span>';
          html += '</p>';
          html += '<p class="cnt">' + row.cnt_detail + '개 / ';
          html += '사업소 : ';
          if(row.od_name) {
            html += row.od_name;
          }
          html += '</p>';
          html += '<p class="cnt">'+row.od_id+'</p>';
          html += '<p class="date">' + row.date;
          if(row.od_b_name){
            html += " / " + row.od_b_name;
          }
          html += '</p>';
          if(row.ct_manager){
            html += '<p class="cnt"> 출고 담당자 : ' + row.ct_manager + '</p>';
          }
          html += '<p class="status ' + row.od_status_class + '">';
          html += '<span>' + row.od_status_name + '</span>';
          html += '</p>';
          html += '</li>';
          // html += '<li class="barInfo barcode_box ' + row.od_barcode_class + '" data-id="' + row.od_id + '" data-stock="2" data-it="'+row.ct_it_id+'"  data-option="'+row.ct_option+'" >';
          html += '<li class="barInfo barcode_box ' + row.od_barcode_class + '" data-id="' + row.od_id + '" data-ct-id="'+row.ct_id + '" >';
          html += '<span class="cnt">' + row.od_barcode_name + '</span>';
          if(row.edit_status) {
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
    cf_flag();

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

    $("#listSortChangeBtn").click(function() {
      var item = $(this).find("span");
      var active = $(this).find(".active");
      
      $(item).removeClass("active");
      
      if($(active).next().length){
        $(active).next().addClass("active");
      } else {
        $(item[0]).addClass("active");
      }

      $("#page").val(1);
      $("#listDataWrap").html("");
      doSearch();
    });

    $(window).scroll(function() {
      if((window.innerHeight + window.scrollY) >= document.body.offsetHeight / 2) {
        doSearch();
      }
    });
  });

  //미완료 바코드 작성만보기버튼
  function cf_flag() {
    var show_incompleted_barcode_only = $('#cf_flag').prop('checked');
    var show_mine_only = $('#cf_flag2').prop('checked');
    var show_unselected_only = $('#cf_flag3').prop('checked');

    setCookie("cf_flag", show_incompleted_barcode_only, 1);
    setCookie("cf_flag2", show_mine_only, 1);
    setCookie("cf_flag3", show_unselected_only, 1);

    // 바코드만 등록 미완료만 보기
    if(show_incompleted_barcode_only) {
      formdata['incompleted_barcode'] = 'true';
    } else {
      formdata['incompleted_barcode'] = 'false';
    }

    // 내 담당만 보기
    if(show_mine_only) {
      formdata['ct_manager'] = '<?=$member['mb_id']?>';
    } else {
      formdata['ct_manager'] = '';
    }

    // 미지정만 보기
    if(show_unselected_only) {
      formdata['unselected_only'] = 'true'
    } else {
      formdata['unselected_only'] = 'false';
    }

    $("#listDataWrap").html("");
    page2 = 1;
    doSearch();
  }

  $("#cf_flag").click(function(e) {
    if(loading) {
      e.preventDefault();
      return false;
    }
    cf_flag();
  });
  $("#cf_flag2").click(function(e) {
    if(loading) {
      e.preventDefault();
      return false;
    }
    if($(this).prop('checked'))
      $('#cf_flag3').prop('checked', false);
    cf_flag();
  });
  $("#cf_flag3").click(function(e) {
    if(loading) {
      e.preventDefault();
      return false;
    }
    if($(this).prop('checked'))
      $('#cf_flag2').prop('checked', false);
    cf_flag();
  });

  //바코드 버튼 클릭
  $(document).on("click", ".barcode_box", function(e) {
    e.preventDefault();
    var id = $(this).attr("data-id");
    var ct_id = $(this).attr("data-ct-id");
    var od = $(this).attr("data-od");
    var it = $(this).attr("data-it");
    var stock = $(this).attr("data-stock");
    var option = encodeURIComponent($(this).attr("data-option"));

    $.ajax({
      url : "/shop/ajax.release_orderview.check.php",
      type : "POST",
      data : {
        ct_id : ct_id
      },
      success : function(result) {
        if(result.error == "Y") {
          if(confirm("작업중입니다. 무시하고 진행 시 이전 작업자는 작업이 종료됩니다. 무시하시겠습니까?")) {
            location.href="<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_3.php?od_id="+ id+"&ct_id="+ct_id;
          }
        } else {
          location.href="<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_3.php?od_id="+ id+"&ct_id="+ct_id;
        }
      }
    });
  });
  </script>
</body>
</html>