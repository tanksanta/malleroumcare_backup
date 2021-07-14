<?php
include_once('./_common.php');

if($_POST['page']) {
  $page=$_POST['page'];
} else {
  $page=1;
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
    strdate DESC
  limit
    {$load}, 5
";
$result = sql_query($sql);

$sql = "select count(*) as count from `g5_rental_log` where `stoId` = '{$stoId}'";
$row = sql_fetch($sql);
# 페이징
$totalCnt = $row['count'];

if(!$totalCnt) {
  echo '<tr style=""><td colspan="4"><br><br><br>자료가 없습니다</td></tr>';
}
$list="";
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $number = $totalCnt-(($page-1)*5)-$i;
  if($row['strdate']){
    $strdate = date('y/m/d', strtotime($row['strdate']));
    $enddate = date('y/m/d', strtotime($row['enddate']));
    $date = $strdate.'~'.$enddate;
  } else {
    $date="미등록";
  }
  if($row['rental_log_division']=="1") {
    $content=$row['dis_detail'];
    if($row['dis_file']) {
      $url=G5_URL.'/data/file/disinfection/'.$row['dis_file'];
      $document='<a href="'.$url.'" download target="_blank">소독 확인서</a>';
    } else {
      $document='';
    }
  } else {
    $od_id = sql_fetch("select od_id from g5_shop_order where ordId = '{$row['ordId']}'")['od_id'];
    $content=$row['ren_person'].' 대여';
    $document='<a href="'.G5_SHOP_URL.'/eform/downloadEform.php?od_id='.$od_id.'">계약서</a>';
  }
  $list = $list.'<tr>
    <td>'.$number.'</td>
      <td>'.$content.'</td>
      <td>'.$date.'</td>
      <td>'.$document.'</td>
    </tr>';
  }
  echo $list;
?>
