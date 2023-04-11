<?php
$sub_menu = '400300';
include_once('./_common.php');
//테이블생성
$query = "SHOW tables LIKE 'g5_shop_item_entprice_log'";//업로드로그 테이블 확인 
$wzres = sql_num_rows( sql_query($query) );

if($wzres < 1) {
	sql_query("CREATE TABLE `g5_shop_item_entprice_log` (
	  `idx` int(11) NOT NULL AUTO_INCREMENT,
	  `mb_id` varchar(50) NOT NULL DEFAULT '' COMMENT '업로드멤버ID',
	  `mb_name` varchar(100) DEFAULT '' COMMENT '업로드멤버이름',
	  `up_start_time` datetime DEFAULT NULL COMMENT '업로드시작시간',
	  `up_end_time` datetime DEFAULT NULL COMMENT '업로드종료시간',
	  PRIMARY KEY (`idx`),
	  KEY `member_id` (`mb_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	");
}


auth_check($auth[$sub_menu], "w");

$g5['title'] = '상품가격관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');


$sql = "SELECT * FROM `g5_shop_item_entprice_log` ORDER BY up_end_time DESC LIMIT 0,1";
$row22 = sql_fetch($sql);

$ent_sql = '';
$item_sql = '';

$stx = get_search_string($stx);
if($sfl && $stx) {
    if($sfl == 'mb_name') {
        $ent_sql = " and mb_name like '%$stx%' ";
    }
    else if($sfl == 'it_name') {
        $item_sql = " and it_name like '%$stx%' ";
    }
}

// 사업소 가져오기
$sql = "
    SELECT * FROM g5_member
    WHERE mb_manager = '{$member['mb_id']}'
    $ent_sql
    ORDER BY mb_id ASC
";
$result = sql_query($sql);

$ents = [];
while($ent = sql_fetch_array($result)) {
    $ents[] = $ent;
}

// 상품 가져오기
$sql_common = "
    FROM g5_shop_item
    WHERE prodSupYn = 'Y'
    $item_sql
";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];;
$rows = $page_rows;

$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$qstr .= "&sfl={$sfl}&stx={$stx}";

$sql = "
    SELECT *
    $sql_common
    ORDER BY it_id ASC
    limit $from_record, $rows
";
$result = sql_query($sql);

$items = [];
while($item = sql_fetch_array($result)) {
    $entprice = sql_query("
        SELECT * FROM g5_shop_item_entprice
        WHERE it_id = '{$item['it_id']}'
    ");
    $item['entprice'] = [];
    while($ep = sql_fetch_array($entprice)) {
        $item['entprice'][$ep['mb_id']] = $ep['it_price'];
    }

    $items[] = $item;
}

add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<style>
#upload_wrap { display: none; }
.popModal #upload_wrap { display: block; }
.popModal .popModal_content { margin: 0 !important; }
.popModal .form-group { margin-bottom: 15px; }
.popModal label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: 700; }
.popModal input[type=file] { display: block; }
.popModal .help-block { padding: 0; display: block; margin-top: 5px; margin-bottom: 10px; color: #737373; }
</style>

<div class="local_ov01 local_ov">
    <p>상품가격을 입력하지 않은 경우 기본으로 설정된 가격으로 적용됩니다.</p>
    <form name="flist" class="local_sch">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="save_stx" value="">

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="it_name" <?=get_selected($sfl, 'it_name')?>>상품명</option>
        <option value="mb_name" <?=get_selected($sfl, 'mb_name')?>>사업소</option>
    </select>

    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?=get_text($stx)?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">

    </form>
    <div class="right">
        <?php if($row22["mb_id"] != ""){?>
		<div style="color:red; font-weight:bold;float:left;margin-top:6px;margin-right:20px">마지막 업로드 완료 : <?=$row22["up_end_time"]?> / <?=$row22["mb_name"]?> (<?=$row22["mb_id"]?>)</div>
		<?php }?>
		<button id="exceldownload">엑셀다운로드</button>
        <button id="excelupload">엑셀업로드</button>
    </div>
</div>

<div id="upload_wrap">
  <form id="form_excel_upload" style="font-size: 14px;">
    <div class="form-group">
      <label for="datafile">엑셀 업로드</label>
      <input type="file" name="datafile" id="datafile">
      <p class="help-block">
        다운로드 받으신 엑셀을 작성하신 후 업로드하시면 적용됩니다.
      </p>
    </div>
    <button type="submit" class="btn btn-primary">업로드</button>
  </form>
</div>

<form action="itempriceupdate.php" method="POST" autocomplete="off">
<div class="tbl_head01 tbl_wrap fixed_table_wr">
    <table>
        <thead>
            <tr>
                <th class="fixed">상품명</th>
                <th>급여가</th>
                <th>공급가</th>
                <?php foreach($ents as $ent) { ?>
                <th><?=$ent['mb_name']?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $it) { ?>
            <tr>
                <th class="fixed">
                    <?=$it['it_name']?>
                </th>
                <td class="td_numsum">
                    <?=number_format($it['it_cust_price'])?>
                </td>
                <td class="td_numsum">
                    <?=number_format($it['it_price'])?>
                </td>
                <?php foreach($ents as $ent) { ?>
                <td>
                    <input type="text" name="price[<?=$it['it_id']?>][<?=$ent['mb_id']?>]" value="<?=$it['entprice'][$ent['mb_id']]?>" class="frm_input sit_amt" size="7">
                </td>
                <?php } ?>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<div class="btn_fixed_top">
    <input type="submit" value="저장" class="btn btn_01">
    <input type="button" value="취소" onclick="window.location.href='itemlist.php'" class="btn btn_02">
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&page="); ?>

<script>
$("#exceldownload").click(function() {
    $(location).attr('href',"./itempriceexcel.php");
});

// 택배정보 일괄 업로드
$('#excelupload').click(function() {
  $(this).popModal({
    html: $('#form_excel_upload'),
    placement: 'bottomRight',
    showCloseBut: false
  });
});
$('#form_excel_upload').submit(function(e) {
  e.preventDefault();

  var fd = new FormData(document.getElementById("form_excel_upload"));
  $.ajax({
      url: 'ajax.itemprice.excel.upload.php',
      type: 'POST',
      data: fd,
      cache: false,
      processData: false,
      contentType: false,
      dataType: 'json'
    })
    .done(function() {
      alert('업로드가 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      if($xhr.status == '200'){
          alert('업로드가 완료되었습니다.');
      } else {
          alert('업로드 실패');
      }
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
