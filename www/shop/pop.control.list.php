<?php
include_once('./_common.php');

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_eform.css?v=1128">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
add_javascript('<script src="'.G5_JS_URL.'/ckeditor/ckeditor.js"></script>');
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$period = date("Y-m-d");
?>

<html>
<head>
<!-- fontawesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=2,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<title>제품관리대장</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <script src="<?php echo G5_JS_URL ?>/jquery.fileDownload.js"></script>
<?php include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php'); ?>
</head>
<style>
pop_add_item {
    width: 600px;
}
#popup_buttom,
#pop_add_item .content .addoptionbuttons {
    clear:both;
    position:fixed;
    left:0;
    bottom:0;
    width:100%;
    background-color:#333333;
    height:50px;
    font-size:large;
    font-weight:bold;
}
#popup_buttom input[type="submit"],
#pop_add_item .content .addoptionbuttons input[type="submit"] {
    display:block;
    float:left;
    width:50%;
    height:100%;
    color:white;
    background:transparent;
    text-align:center;
    line-height: 50px;
    cursor:pointer;
    font-size:large;
    font-weight:bold;
}
#popup_buttom .addoptionbuttons a,
#pop_add_item .content .addoptionbuttons a {
    display:block;
    float:left;
    width:50%;
    height:100%;
    color:black;
    background-color:#a5a5a5;
    text-align:center;
}
#popup_buttom .addoptionbuttons a:hover,
#pop_add_item .content .addoptionbuttons a:hover {
    text-decoration: none;
}
#popup_buttom .addoptionbuttons a img,
#pop_add_item .content .addoptionbuttons a img {
    vertical-align:middle;
    margin-top: -3px;
}
#popup_buttom .addoptionbuttons:after,
#pop_add_item .content .addoptionbuttons:after {
    clear:both;
    display:block;
    content: '';
}
.title {
    text-align: center;
    margin: 5% 0;
    font-size: xx-large;
}

.content_option {
    margin: 0 10%;
    padding: 3% 0;
}

.content_option label {
    font-size: x-large;
    font-weight: bold;
}

#pop_add_item .popadditemsearch input[type="text"],
#pop_add_item .popadditemsearch select {
    min-width: 30px;
    width: 30%;
    min-height: 20px;
    height: 10%;
    margin: 3% 0 8% 0;
    font-size: large;
}

.content_option input, .content_option select {
    height: 5%;
}

.connect {
    font-size: large;
    font-weight: bold;
    min-height: 20px;
    height: 10%;
    vertical-align: center;
}

.ui-datepicker {
    margin-left: 0px;
    z-index: 1000;
}
.content_excel table {
    width:95%;border-collapse:collapse;border-spacing:0;
    margin: auto;
}
.content_excel th {
    border: 1px solid black;
    text-align: center;
    background: #eee;
}
.content_excel td {
    border: 1px solid black;
    text-align: center;
}
.no-border {
    border: none !important;
}

.content_excel select {
    border-style: none;
}
.export-button {
    text-align: center;
    margin-top: 20px;
}

.export-button button {
    border: 1px #333 solid;
    padding: 10px 50px;
    cursor:pointer;
}
</style>
<div class="headerTitle" style="width: auto; height: 5%; text-align: right; padding: 20px 10px; display: none;">
    <a href="#" class="cancel" onclick="$('.order_add_close').click();"><i class="fa-sharp fa-solid fa-xmark fa-2xl"></i></a>
</div>
<div id="pop_add_item" class="admin_popup">
    <h1 id="ctrlListTitle" class="title">제품관리대장 옵션 설정</h1>
    <div class="content_option">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="<?=$action?>" onsubmit="return formcheck(this);" method="get" autocomplete="off">
            <input type="hidden" name="no_option" value="<?=$no_option?>">
            <input type="hidden" name="ca_id" value="<?=$ca_id?>">

            <label for="period">기간설정</label></br>
          <input type="text" name="fr_date" value="<?php echo $period==''?'0000-00-00':$period; ?>" id="fr_date" class="date datepicker"><span class="connect"> ~ </span>
            <input type="text" name="to_date" value="<?php echo $period==''?'0000-00-00':$period;; ?>" id="to_date" class="date datepicker">

          <label for="type" class="type"></br>제품분류 설정</label></br>
            <select name="type" type="type" id="gubun">
                <option value="all" <?php echo get_selected($type, 'all'); ?>>전체</option>
                <option value="00" <?php echo get_selected($type, '00'); ?>>판매상품만</option>
                <option value="01" <?php echo get_selected($type, '01'); ?>>대여상품만</option>
            </select>

        </form>
    </div>
    <div id="popup_buttom">
        <div class="addoptionbuttons">
            <input type="submit" value="확인" id="order_add_open"/>
            <a href='#' class="order_add_close">
                취소
            </a>
        </div>
    </div>
</div>
<div id="pop_ctrl_list" class="admin_popup" style="display: none; padding-bottom: 3%;">
    <h1 id="ctrlListTitle" class="title">제품관리대장</h1>
    <div class="content_excel">
      <table id="prodCtrlListTable">
        <tr>
          <td colspan="4" class="no-border left" id="entId">사업소명</td>
          <td colspan="5" class="no-border right" id="search_period">조회날짜</td>
        </tr>
        <tr>
          <th style="width: 3%;">연번</th>
          <th style="width: 10%;">일자</th>
          <th style="width: 6%;">수급자명</th>
          <th style="width: 10%;">품목명</th>
          <th style="width: 15%;">제품명</th>
          <th style="width: 18%;">제품코드-상품바코드</th>
          <th style="width: 5%;">구분</th>
          <th style="width: 13%;">대여기간</th>
          <th style="width: 10%;">배송구분</th>
        </tr>
        <tbody id="excel_body">
        <tr id="no_contents">
          <td colspan="9" >조건에 일치하는 기록이 없습니다.</td>
        </tr>
        </tbody>
      </table>
    </div>
    <div class="export-button">
        <button type="submit" id="excel-btn"><span>엑셀 다운로드</span></button>
    </div>
</div>

<div id="div_load_image" style="position:absolute; top:0; left:0;width:100%;height:100%; z-index:9999; background:#fefefe; opacity:0.8; margin:auto; padding:0; text-align:center; vertical-align: middle;">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif" style="display:block; width:100px; height:100px; margin: 150px auto;">
</div>

<script>
$(function() {
  var list_data = "";
  $("#div_load_image").hide();

  // 최초 창 크기 설정
  parent.document.getElementById("content").style.width = "30%";

  console.log($('#popup_order_add', parent.document));
  
  // datepicker 설정
  $('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

  // 취소 버튼
  $(document).on("click", ".order_add_close", function (e) {
      e.preventDefault();

      $('#popup_order_add', parent.document).hide();
      $('#hd', parent.document).css('z-index', 10);
  });

  // 확인 버튼
  $(document).on("click", "#order_add_open", function (e) {
      e.preventDefault();

      const to_date = $('#to_date').val();
      const fr_date = $('#fr_date').val();
      const gubun_sel = document.getElementById('gubun');
      const gubun = gubun_sel.options[gubun_sel.selectedIndex].value;

      $('#entId').text("<?=$member['mb_name']?>");
      $('#entId').val("<?=$member['mb_id']?>");
      $('#search_period').text(fr_date+" ~ "+to_date);
      $('#search_period').val(fr_date+" ~ "+to_date);

      parent.document.getElementById("content").style.height = "70%";
      parent.document.getElementById("content").style.width = "80%";
      $('#pop_add_item').hide();
      $('#pop_ctrl_list').show();
      $('.headerTitle').show();

      $("#div_load_image").show();
      $.ajax({
			url : './ajax.prod_control_list.php',
			type : 'POST',
			cache : false,
			data: {
            "to_date": to_date,
            "fr_date": fr_date,
            "gubun": gubun
      },
			success : function(data) {
          list_data = JSON.parse(data.message);
          if(list_data.length == 0) { // 검색에 해당하는 결과가 없으면
              $("#div_load_image").hide();
              return false;
          }

          const no_info = document.getElementById("no_contents");
          no_info.remove(); // 기록이 없다는 안내 삭제

          var index = 0;
          var temp_gubun = "";
          var temp_period = "";
          for(var j=0; j<list_data.length; j++) {
              for (var i = 0; i < list_data[j].length; i++) {
                  for (var ind = list_data[j][i].length - 1; ind > -1; ind--) {
                      console.log(list_data[j][i][ind]);
                      var period = list_data[j][i][ind]['strdate']?list_data[j][i][ind]['strdate'] +'~'+list_data[j][i][ind]['enddate']:"-";

                      index += 1;
                      var innerHtml = "";
                      innerHtml += '<tr>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + index + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + list_data[j][i][ind]['modifyDtm'] + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + list_data[j][i][ind]['penNm'] + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + list_data[j][i][ind]['itemNm'] + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + list_data[j][i][ind]['prodNm'] + '</td>';
                      // innerHtml += '<td>' + list_data[j][i][ind]['ren_person'] + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>"+list_data[j][i][ind]['prodId']+'-'+list_data[j][i][ind]['prodBarNum']+'</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + list_data[j][i][ind]['deli_stat'] + '</td>';
                      innerHtml += "<td style=mso-number-format:'\@'>" + period + '</td>';
                      innerHtml += '<td><select id="deli_type' + index + '" name="sfl" type="type" style="width: 100%;">';
                      innerHtml += '<option value="기관배송">기관배송(기본값)</option>';
                      innerHtml += '<option value="공급업체배송">공급업체배송</option>';
                      innerHtml += '<option value="소독업체배송">소독업체배송</option>';
                      innerHtml += '<option value="택배">택배</option>';
                      innerHtml += '<option value="내방">내방</option>';
                      innerHtml += '</select></td>';
                      innerHtml += '</tr>';
                      console.log(innerHtml);

                      $("#prodCtrlListTable > tbody:last").append(innerHtml);
                  }
              }
          }




        console.log(JSON.parse(data.message));
// console.log(data.message);
        $("#div_load_image").hide();
        return false;
			},
			error : function($xhr) {
        var data = $xhr.responseJSON;
				// console.log(JSON.parse(data.message));
console.log(JSON.parse(data.message));
        $("#div_load_image").hide();
				return false;
			}
		});




      return false;
  });

  // 엑셀 다운로드 버튼
  $(document).on("click", "#excel-btn", function (e) {
      e.preventDefault();

      var tr_list = [];

      var rows = document.getElementById("prodCtrlListTable").getElementsByTagName("tr");
      console.log(rows.length);	// tbody tr 개수 = 2

      // tr만큼 루프돌면서 컬럼값 접근
      for( var r=2; r<rows.length; r++ ){
        var cells = rows[r].getElementsByTagName("td");
        var td_list = [];
        for(var c=0; c<cells.length; c++){
            var deli_type = document.getElementById("deli_type"+(r-1));
            if(c==8) td_list.push(deli_type.options[deli_type.selectedIndex].value);
            else if(c==5) {
                td_list.push(cells[c].innerText.split('-')[0]);
                td_list.push(" "+cells[c].innerText.split('-')[1]);
            }
            else td_list.push(cells[c].innerText);
        }
        tr_list.push(td_list);
      }

      console.log(tr_list);

      if(tr_list.length == 0) { // 검색에 해당하는 결과가 없으면
          alert("조건과 일치하는 데이터가 없습니다.");
          return false;
      }

      $.fileDownload('./ctrl_list_excel.php', {
        httpMethod: "POST",
        data: {
          list_data: tr_list,
          search_period: $('#search_period').val()
        }
      });

      var index = 0;
      for(var i=0; i<list_data.length; i++){
          for(var ind=0; ind<list_data[i].length; ind++) {
              console.log(list_data[i][ind]);
          }
      }
  });
});
</script>

</body>
</html>