<?php
include_once("./_common.php");
if($_POST['page']) {
  $page=$_POST['page'];
} else {
  $page=1;
}
$sql = "select count(*) as count from `g5_rental_log` where `stoId` = '{$stoId}'";
$row = sql_fetch($sql);
# 페이징
$totalCnt = $row['count'];
$pageNum = $page; # 페이지 번호
$listCnt = 5; # 리스트 갯수 default 10
$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지 
$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
$total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
if ($b_end_page > $total_page){ 
  $b_end_page = $total_page;
}
$total_block = ceil($total_page/$b_pageNum_listCnt);
?>
<?php if($pageNum >$b_pageNum_listCnt){ ?><a href="javascript:open_log(null,'<?=$_POST['stoId']?>','log_<?=$_POST['stoId']?>','1','page_<?=$_POST['stoId']?>','2','<?=$_POST['barcode']?>')"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
<?php if($block > 1){ ?><a href="javascript:open_log(null,'<?=$_POST['stoId']?>','log_<?=$_POST['stoId']?>','<?=($b_start_page-1)?>','page_<?=$_POST['stoId']?>','2','<?=$_POST['barcode']?>')"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
<?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="javascript:open_log(null,'<?=$_POST['stoId']?>','log_<?=$_POST['stoId']?>','<?=$j?>','page_<?=$_POST['stoId']?>','2','<?=$_POST['barcode']?>')"><?=$j?></a><?php } ?>
<?php if($block < $total_block){ ?><a href="javascript:open_log(null,'<?=$_POST['stoId']?>','log_<?=$_POST['stoId']?>','<?=($b_end_page+1)?>','page_<?=$_POST['stoId']?>','2','<?=$_POST['barcode']?>')"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
<?php if($block < $total_block){ ?><a href="javascript:open_log(null,'<?=$_POST['stoId']?>','log_<?=$_POST['stoId']?>','<?=$total_page?>','page_<?=$_POST['stoId']?>','2','<?=$_POST['barcode']?>')"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
