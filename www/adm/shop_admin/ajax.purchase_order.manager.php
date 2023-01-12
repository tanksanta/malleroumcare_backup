<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$type = $_POST['type'];
if ($type != 'od_purchase_manager' && $type != 'od_release_manager') {
  $ret = array(
    'result' => 'fail',
    'msg' => '정상적인 접근이 아닙니다.',
  );
  echo json_encode($ret);
  exit;
}

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from purchase_order where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
  $ret = array(
    'result' => 'fail',
    'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
  );
  echo json_encode($ret);
  exit;
}

$sql = " update purchase_order
            set {$type} = '{$mb_id}' ";
$sql .= " where od_id = '{$od_id}' ";
sql_query($sql);


if ($type == 'od_purchase_manager') {
  $type_name = '발주담당자';
}
if ($type == 'od_release_manager') {
  $type_name = '출고담당자';
}

if ($mb_id) {
  $manager_info = get_member($mb_id);

  set_purchase_order_admin_log($od_id, $type_name . ' ' . $manager_info['mb_name'] . '(으)로 변경');
} else {
  set_purchase_order_admin_log($od_id, $type_name . ' 삭제');
}

$ret = array(
  'result' => 'success',
  'msg' => "반영되었습니다.",
);
$json = json_encode($ret);
echo $json;
?>