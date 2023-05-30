<?php
include_once('./_common.php');

if($_POST['page']) {
  $page = $_POST['page'];
} else {
  $page = 1;
}
$load=($page-1)*5;
$sql = "
  select
    *
  from
    g5_rental_log
  where
    stoId = '{$stoId}'
  order by
    strdate DESC,dis_total_date DESC
  limit
    {$load}, 5
";
$result = sql_query($sql);

$sql = "select count(*) as count from `g5_rental_log` where `stoId` = '{$stoId}'";
$row = sql_fetch($sql);
# 페이징
$totalCnt = $row['count'];

if(!$totalCnt) {
  echo '<tr style=""><td colspan="5"><br><br><br>자료가 없습니다</td></tr>';
}
$list="";
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $number = $totalCnt - (($page - 1) * 5) - $i;
  if($row['strdate']) {
    $strdate = date('y/m/d', strtotime($row['strdate']));
    $enddate = date('y/m/d', strtotime($row['enddate']));
    $date = $strdate.'~'.$enddate;
	//추가
	$strdate2 = date('Y-m-d', strtotime($row['strdate']));
    $enddate2 = date('Y-m-d', strtotime($row['enddate']));
    $date2 = $strdate2.'-'.$enddate2;
	//추가끝
  } else {
    $date="미등록";
  }
  if($row['rental_log_division'] == "1") {//소독
    $division = "소독";//추가
	$content = $row['dis_detail'];
    if($row['dis_file']) {
      $url = G5_URL.'/data/file/disinfection/'.$row['dis_file'];
      $document = '<a href="'.$url.'" download target="_blank">소독 확인서</a>';
    } else {
      $document = '';
    }
  } else {
    $division = "대여";//추가
	if($row['ren_eformUrl'] != "cancel"){
		$content=$row['ren_person'].' 대여';
	}else{
		$content=$row['ren_person'];
	}
    if($row['ordId']) {
      $od_id = sql_fetch("select od_id from g5_shop_order where ordId = '{$row['ordId']}'")['od_id'];
      $document = '<a href="'.G5_SHOP_URL.'/eform/downloadEform.php?od_id='.$od_id.'">계약서</a>';
    } else {
		//추가
		$sql = "SELECT a.penId,a.penNm,HEX(a.dc_id) AS UUID,dc_sign_send_datetime FROM `eform_document` AS a INNER JOIN `eform_document_item` AS b ON a.dc_id = b.dc_id WHERE b.it_barcode='".$_POST['barcode']."' AND a.dc_status='3' AND b.it_date='".$date2."'";
		$rows2 = sql_fetch($sql);
		if($rows2['UUID']) {
			if($rows2['dc_sign_send_datetime'] == "0000-00-00 00:00:00"){
				$document = '<a href="'.G5_SHOP_URL.'/eform/downloadEform.php?dc_id='.$rows2['UUID'].'">계약서</a>';
			}else{
				$document = '<a href="javascript:;" onClick="mds_download(\''.$rows2["UUID"].'\',\'1\')">계약서</a>';
			}
		}else{
			$document = '';
		}
		//추가끝
		//$document = '';
    }
  }
  //$division 추가
  $list = $list.'<tr>
    <td>'.$number.'</td>
      <td>'.$content.'</td>
	  <td>'.$division.'</td>
      <td>'.$date.'</td>
      <td>'.$document.'</td>
    </tr>';
  }
  echo $list;
?>
