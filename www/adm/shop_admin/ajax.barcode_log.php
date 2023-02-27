<?php
include_once('./_common.php');
$data=date("Y-m-d H:i:s");

if( !$_POST['prods'] ) {
    // 상품이 없을 경우 프로세스를 진행하지 않는다.
    exit();
}

$sql_string = [];
for($i=0; $i<count($_POST['prods']); $i++) {
    // 주문무효 또는 주문취소 상품에 대해서는 더이상 바코드 로그를 생성하지 않음
    //$sql_find = "SELECT ct_status FROM g5_shop_cart WHERE LOCATE('".$_POST['prods'][$i]['stoId']."', stoId) > 0 ;"; // 주문 상태 확인

    // 22.11.16 : 서원 - SQL 데이터 전체 검색에 대한 조건 추가
    $sql_find = "SELECT it_name, ct_status FROM g5_shop_cart WHERE od_id = '{$_POST['od_id']}' and LOCATE('".$_POST['prods'][$i]['stoId']."', stoId) > 0 ;"; // 주문 상태 확인
    $result_find = sql_fetch($sql_find);
        
    
    if($result_find['ct_status'] == '주문무효' || $result_find['ct_status'] == '취소'){ continue; }


    // 23.01.16 : 서원 - 상품 이름을 g5_shop_cart에서 가져오는 것으로 변경 하고 SQL쿼리문 제거.
    //$sql_it = "select it_name from g5_shop_item where it_id ='".$_POST['prods'][$i]['prodId']."'";
    //$result_it = sql_fetch($sql_it);


    $option = "";
    if($_POST['prods'][$i]['prodColor']) $option .= "(".$_POST['prods'][$i]['prodColor'].")";
    if($_POST['prods'][$i]['prodSize']) $option .= "(".$_POST['prods'][$i]['prodSize'].")";

    $sql = " SELECT barcode FROM g5_barcode_log WHERE od_id = '{$_POST['od_id']}' and stoId = '{$_POST['prods'][$i]['stoId']}' ORDER BY b_num DESC LIMIT 1 ";
    $last_barcode = sql_fetch($sql);

    if($last_barcode['barcode'] != $_POST['prods'][$i]['prodBarNum']) {

        if($_POST['prods'][$i]['prodBarNum']) {
            $content = $_POST['type']?"파트너 바코드입력 : ".$result_find['it_name'].$option."[ ".$_POST['prods'][$i]['prodBarNum']." ]":"바코드입력 : ".$result_find['it_name'].$option."[ ".$_POST['prods'][$i]['prodBarNum']." ]";
            $sql_string[] = ('INSERT INTO `g5_barcode_log`(`od_id`, `mb_id`, `stoId`, `barcode`, `b_content`, `b_date`) VALUES ("'.$_POST['od_id'].'","'.$_POST['mb_id'].'","'.$_POST['prods'][$i]['stoId'].'","'.$_POST['prods'][$i]['prodBarNum'].'","'.$content.'","'.$data.'")');
        } else {
            $content = $_POST['type']?"파트너 바코드제거 : ".$result_find['it_name'].$option."[ ".$last_barcode['barcode']." ]":"바코드제거 : ".$result_find['it_name'].$option."[ ".$last_barcode['barcode']." ]";
            $sql_string[] = ('INSERT INTO `g5_barcode_log`(`od_id`, `mb_id`, `stoId`, `barcode`, `b_content`, `b_date`) VALUES ("'.$_POST['od_id'].'","'.$_POST['mb_id'].'","'.$_POST['prods'][$i]['stoId'].'","'.$_POST['prods'][$i]['prodBarNum'].'","'.$content.'","'.$data.'")');
        }
    }


}

if( COUNT($sql_string) ) {

    // 23.01.16 : 서원 - 트랜잭션 시작
    sql_query("START TRANSACTION");

    try {   

        foreach($sql_string as $sql) { sql_query($sql); }  

        // 23.01.16 : 서원 - 트랜잭션 커밋
        sql_query("COMMIT");

    } catch (Exception $e) {
        // 23.01.16 : 서원 - 트랜잭션 롤백
        sql_query("ROLLBACK");
    }    
}

?>