<?php
$sub_menu = '400405';

include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$sql_search = "";
if ($search != "") {
  if ($sel_field != "") {
    $sql_search .= " and $sel_field like '%$search%' ";
  }
}

$sql_common = "FROM g5_member m
 WHERE m.mb_type = 'partner' AND m.mb_partner_auth = 1 AND m.mb_level='5' AND m.mb_partner_type LIKE '%직배송%'
 AND (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL)		
";


$total_count=0;
$order_by = ($orb == "")?"count1" : $orb;
$sql = "SELECT m.mb_id,m.mb_name,m.mb_thezone, m.mb_hp, m.mb_fax
 ,(SELECT COUNT(ct_id) FROM g5_shop_cart c 
 LEFT JOIN g5_shop_order o ON c.od_id = o.od_id 
 WHERE ct_is_direct_delivery = '1' AND od_del_yn = 'N'  AND ct_status='준비' AND ct_direct_delivery_partner=m.mb_id) AS count1
 ,(SELECT COUNT(ct_id) FROM g5_shop_cart c 
 LEFT JOIN g5_shop_order o ON c.od_id = o.od_id  
 WHERE ct_is_direct_delivery = '1' AND od_del_yn = 'N' AND ct_status='출고준비' AND ct_direct_delivery_partner=m.mb_id) AS count2
 ,(SELECT COUNT(ct_id) FROM g5_shop_cart c 
 LEFT JOIN g5_shop_order o ON c.od_id = o.od_id  
 WHERE ct_is_direct_delivery = '1' AND od_del_yn = 'N' AND ct_status='배송' AND ct_direct_delivery_partner=m.mb_id) AS count3 ". $sql_common . $sql_search."order by ".$order_by." DESC,m.mb_id ASC";
$result = sql_query($sql);
$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;wh_name='.$wh_name.'&amp;stock_type='.$stock_type;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;
$partner_list = array();
while ($row = sql_fetch_array($result)) { 
			$partner_list[] = $row;
			$total_count++;
}
$sql_no = "SELECT 
 COUNT(CASE WHEN ct_status='준비' THEN 1 END) AS count1, 
 COUNT(CASE WHEN ct_status='출고준비' THEN 1 END) AS count2, 
 COUNT(CASE WHEN ct_status='배송' THEN 1 END) AS count3 
 FROM g5_shop_cart c 
 LEFT JOIN g5_shop_order o ON c.od_id = o.od_id
 WHERE ct_is_direct_delivery = '1' AND od_del_yn = 'N' AND ct_status IN ('준비','출고준비','배송') 
 AND c.ct_direct_delivery_partner = ''";
 $row_no = sql_fetch($sql_no);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>입고 현황</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">
  <link rel="stylesheet" href="/skin/admin/new/css/admin.css?ver=211222">
<link rel="stylesheet" href="/skin/admin/new/css/admin.css">
<link rel="stylesheet" href="/adm/css/samhwa_admin.css">
<link rel="stylesheet" href="/css/apms.css">
<link rel="stylesheet" href="/css/level/basic.css">
<link rel="stylesheet" href="/thema/eroumcare/assets/css/samhwa.css">

  <style>

	.bg0 {background:#fff}
	.bg1 {background:#f2f5f9}
	.bg1 td {border-color:#e9e9e9}
	
.new_form2 {
    border: 1px solid #ddd;
    box-sizing: border-box;
	margin: 20px 0px;
    background-color: #f8f8fa;
 }
  </style>
  <?php include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');?>
</head>

<body style="margin-bottom:20px;">

<!-- 고정 상단 -->
<div style="padding: 0 20px;">
 
  <div class="new_form2">
    <form name="flist" style="padding-left:0px;">
	<table class="new_form_table" id="search_detail_table">
      <tr>
        <th>정렬 기준</th>
        <td>
          <input type="radio" name="orb" value="count1" id="count1" <?=($_REQUEST["orb"] == "count1" || $_REQUEST["orb"] == "")?"checked":"";?>> <label for='count1'>상품준비</label>		  
		  <input type="radio" name="orb" value="count2" id="count2" <?=($_REQUEST["orb"] == "count2")?"checked":"";?>> <label for='count2'>출고준비</label>
		  <input type="radio" name="orb" value="count3" id="count3" <?=($_REQUEST["orb"] == "count3")?"checked":"";?>> <label for='count3'>출고완료</label> 

        </td>
      </tr>
	  
      <tr>
        <th>키워드 검색</th>
        <td>
			<select name="sel_field" id="sel_field">
            <option value="m.mb_name" <?php echo get_selected($sel_field, 'm.mb_name'); ?>>파트너명</option>
            <option value="m.mb_id" <?php echo get_selected($sel_field, 'm.mb_id'); ?>>파트너 ID</option>
			<option value="m.mb_thezone" <?php echo get_selected($sel_field, 'm.mb_thezone'); ?>>거래처 코드</option>
			</select>
			<input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
			<input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;width:70px;">
        </td>
      </tr>
	  
    </table>
	</form>
  </div>
  <div class="tbl_head01" style="overflow: auto; height:500px;">
    <table>
      <thead>
        <tr>
		  <th width="40px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">번호</th>
          <th width="90px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">로그인ID</th>		  
          <th style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">파트너명</th>
          <th width="90px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">거래처코드</th>
          <th width="100px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">연락처</th>
          <th width="100px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">팩호번호</th>          
          <th width="60px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">상품준비</th>
		  <th width="60px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">출고준비</th>
		  <th width="60px" style="background-color:#000000;color:#ffffff;position:sticky;top:-1px;">출고완료</th>
        </tr>
      </thead>
      <tbody>
        <?php  $i = 0; $j = 0;
		if($search == ""){ $j = 1?>
		<tr class="<?php echo $bg; ?>" style="text-align:center;">
		  <td colspan="6" style="background-color:#ffff99;cursor:pointer;" onClick="partner_id_send('미등록')"> 
            위탁업체 미등록
          </td>		      
          <td>
            <?=number_format($row_no['count1'])?>
          </td>
		  <td>
            <?=number_format($row_no['count2'])?>
          </td>
		  <td>
            <?=number_format($row_no['count3'])?>
          </td>		  
        </tr>
		<?php }
		foreach($partner_list as $partner) { 
			$mb = get_member($partner['it_purchase_order_partner']);
			$bg = 'bg'.(($i+$j)%2);
			?>
        <tr class="<?php echo $bg; ?>" style="text-align:center;cursor:pointer;" onClick="partner_id_send('<?=$partner['mb_id'] ?>')">
		  <td>
            <?=($total_count-$i)?>
          </td>
		  <td>
            <?=$partner['mb_id'] ?>
          </td>          
          <td>
            <?=$partner['mb_name'] ?>
          </td>
          <td>
            <?=$partner['mb_thezone']?>
          </td>
          <td>
            <?=$partner['mb_hp'] ?>
          </td>
          <td>
            <?=$partner['mb_fax'] ?>
          </td>          
          <td>
            <?=number_format($partner['count1'])?>
          </td>
		  <td>
            <?=number_format($partner['count2'])?>
          </td>
		  <td>
            <?=number_format($partner['count3'])?>
          </td>
		  
        </tr>
      <?php $i++;}
		if (!$total_count)
        echo '<tr><td colspan="9" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
      ?>
      </tbody>
    </table>	
  </div>
</div>
<script>
	function partner_id_send(partner_id){
		parent.partner_id(partner_id); 
	}
</script>
</body>
