<?php
$sub_menu = '400100';
include_once('./_common.php');

$w = $_POST['w'];

$auth_check = auth_check($auth[$sub_menu], $w ? 'w' : 'r', true);
if($auth_check)
  json_response(400, $auth_check);

$use_warehouse_where_sql = get_use_warehouse_where_sql();

if($w === 'w') {
  // 작성
  $wh_name = clean_xss_tags($_POST['wh_name']);
  $wh_address = clean_xss_tags($_POST['wh_address']);
  $wh_phone = clean_xss_tags($_POST['wh_phone']);

  if(!$wh_name)
    json_response(400, '창고이름을 입력해주세요.');
  
  $sql = " insert into warehouse set wh_name = '{$wh_name}', wh_address = '{$wh_address}', wh_phone = '{$wh_phone}' ";
  $result = sql_query($sql);

  if(!$result)
    json_response(500, '창고를 추가할 수 없습니다. 창고이름이 중복되었는지 확인해주세요.');
}

else if($w === 'u') {
  // 수정
  $wh_id_arr = $_POST['wh_id'];
  $wh_name_arr = $_POST['wh_name'];
  $wh_address_arr = $_POST['wh_address'];
  $wh_phone_arr = $_POST['wh_phone'];
  $wh_use_yn_arr = $_POST['wh_use_yn'];

  if(!$wh_id_arr || !is_array($wh_id_arr))
    json_response(400, '유효하지 않은 요청입니다.');
  
  for($i = 0; $i < count($wh_id_arr); $i++) {
    $wh_id = get_search_string($wh_id_arr[$i]);
    $wh_name = clean_xss_tags($wh_name_arr[$i]);
    $wh_address = clean_xss_tags($wh_address_arr[$i]);
    $wh_phone = clean_xss_tags($wh_phone_arr[$i]);
    $wh_use_yn = clean_xss_tags($wh_use_yn_arr[$i]);

    $sql = " select * from warehouse where wh_id = '$wh_id' ";
    $wh = sql_fetch($sql);

    if(!$wh['wh_id']) continue;

    if($wh_use_yn && $wh['wh_use_yn'] !== $wh_use_yn) {
      $sql = " update warehouse set wh_use_yn = '$wh_use_yn' where wh_id = '$wh_id' ";
      $result = sql_query($sql);

      if(!$result)
        json_response(500, '창고 사용여부를 수정할 수 없습니다.');
    }

    if(!$wh_id || !$wh_name) continue;

    if(($wh['wh_name'] !== $wh_name) || ($wh['wh_address'] !== $wh_address) || ($wh['wh_phone'] !== $wh_phone)) {
      $sql = "
        update
          warehouse w
        left join
          warehouse_stock s ON w.wh_name = s.wh_name
        set
          w.wh_name = '$wh_name',
          s.wh_name = '$wh_name',
          w.wh_address = '$wh_address',
          w.wh_phone = '$wh_phone'
        where
          wh_id = '$wh_id'
      ";
      $result = sql_query($sql);

      if(!$result)
        json_response(500, '창고이름을 수정할 수 없습니다. 창고이름이 중복되었는지 확인해주세요.');
    }
  }
}

else if($w === 'd') {
  // 삭제
  $wh_name = get_search_string($_POST['wh_name']);
  if(!$wh_name)
    json_response(400, '유효하지 않은 요청입니다.');
  
  $sql = " select (sum(ws_qty) - sum(ws_scheduled_qty)) as total from warehouse_stock where wh_name = '$wh_name' and ws_del_yn = 'N' {$use_warehouse_where_sql} ";
  $result = sql_fetch($sql);

  if($result['total'] > 0)
    json_response(500, '재고가 존재하는 창고는 삭제할 수 없습니다.');
  
  $sql = " delete from warehouse where wh_name = '$wh_name' ";
  $result = sql_query($sql);

  if(!$result)
    json_response(500, 'DB 서버 오류로 창고를 삭제할 수 없습니다.');
}

$sql = " select * from warehouse order by wh_id asc ";
$result = sql_query($sql);

$ret = '';
while($row = sql_fetch_array($result)) {
  $sql = " select (sum(ws_qty) - sum(ws_scheduled_qty)) as total from warehouse_stock where wh_name = '{$row['wh_name']}' and ws_del_yn = 'N' {$use_warehouse_where_sql} ";
  $total = sql_fetch($sql);

  $ret .= '
    <tr>
      <td>
        <input type="text" name="wh_name[]" class="frm_input" value="'.$row['wh_name'].'" data-id="'.$row['wh_id'].'">
      </td>
      <td>
        <input type="text" name="wh_address[]" class="frm_input" value="'.$row['wh_address'].'" data-id="'.$row['wh_id'].'">
      </td>
      <td>
        <input type="text" name="wh_phone[]" class="frm_input" value="'.$row['wh_phone'].'" data-id="'.$row['wh_id'].'">
      </td>
      <td>
        '.($total['total'] ?? 0).'개
      </td>
      <td>
        '.($total['total'] ? '재고소진 후 삭제 가능' : '<button type="button" class="btn_wh_del btn_frmline" data-name="'.$row['wh_name'].'">삭제</button>').'
      </td>
      <td>
        <label>
          <input type="radio" name="wh_use_yn_'.$row['wh_id'].'" value="Y" '.get_checked($row['wh_use_yn'], 'Y').'>
          사용
        </label>
        <label>
          <input type="radio" name="wh_use_yn_'.$row['wh_id'].'" value="N" '.get_checked($row['wh_use_yn'], 'N').'>
          미사용
        </label>
      </td>
    </tr>
  ';
}

json_response(200, 'OK', $ret);
