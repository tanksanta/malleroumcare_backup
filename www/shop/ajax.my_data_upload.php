<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$file = $_FILES['datafile']['tmp_name'];
if(!$file)
  json_response(400, '파일을 선택해주세요.');

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$num_rows = $sheet->getHighestRow();

$data = [];
for($i = 3; $i < $num_rows; $i += 2) {
  $cell_name = $sheet->getCell('B'.$i)->getValue();
  $cell_name = explode("\n ", $cell_name);
  $pen_nm = $cell_name[0];
  $pen_type = $cell_name[1];

  $pen_jumin = $sheet->getCell('C'.$i)->getValue();
  $pen_ltm_num = $sheet->getCell('C'.($i + 1))->getValue();

  $cell_item = $sheet->getCell('D'.$i)->getValue();
  $cell_item = explode("/", $cell_item);
  $ca_name = $cell_item[0];
  $it_name = $cell_item[1];

  $cell_code = $sheet->getCell('D'.($i + 1))->getValue();
  $cell_code = explode("-", $cell_code);
  $it_code = $cell_code[0];
  $it_barcode = $cell_code[1];

  $cell_gubun = $sheet->getCell('E'.$i)->getValue();
  if($cell_gubun == '판매')
    $gubun = '00';
  else if($cell_gubun == '대여')
    $gubun = '01'; 
  
  $contract_date = $sheet->getCell('F'.$i)->getValue();

  $cell_date = $sheet->getCell('F'.($i + 1))->getValue();
  if($cell_gubun == '대여') {
    $cell_date = explode('~', $cell_date);
    $sale_date = date('Y-m-d', strtotime($cell_date[0]));
    $rent_date = date('Y-m-d', strtotime($cell_date[1]));
  } else {
    $sale_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($cell_date));
    $rent_date = '0000-00-00';
  }

  $data[] = array(
    'pen_nm' => $pen_nm,
    'pen_type' => $pen_type,
    'pen_jumin' => $pen_jumin,
    'pen_ltm_num' => $pen_ltm_num,

    'ca_name' => $ca_name,
    'it_name' => $it_name,
    'it_code' => $it_code,
    'it_barcode' => $it_barcode,

    'gubun' => $gubun,

    'contract_date' => $contract_date,
    'sale_date' => $sale_date,
    'rent_date' => $rent_date
  );
}

$stock_insert = [];
$stock_update = [];
$rental_data_table = [];
foreach($data as $row) {
  # 1. 업로드 테이블 중복 조회
  $where_check = " mb_id = '{$member['mb_id']}' and sd_it_code = '{$row['it_code']}' and sd_it_barcode = '{$row['it_barcode']}' ";
  if($row['gubun'] == '01') // 대여인 경우
    $where_check .= " and sd_sale_date = '{$row['sale_date']}' and sd_rent_date = '{$row['rent_date']}' ";

  $check_result = sql_fetch(" SELECT sd_id, sd_status FROM stock_data_upload WHERE {$where_check} ");
  if($check_result['sd_id']) {
    if($check_result['sd_status'] == 1) {
      // 이미 업로드된 자료 & 매칭완료 상태면 건너뜀
      continue;
    } else {
      // 이미 업로드된 자료지만 매칭대기 상태면 기존 자료 삭제함
      sql_query(" DELETE FROM stock_data_upload WHERE sd_id = '{$check_result['sd_id']}' ");
    }
  }
  
  $status = 0; // 0: 매칭대기, 1: 매칭완료
  
  # 2. 제품코드 매칭 체크
  $ca_search = '10';
  if($row['gubun'] == '01') $ca_search = '20';
  $code_result = sql_fetch(" SELECT it_id FROM g5_shop_item WHERE ProdPayCode = '{$row['it_code']}' AND ca_id like '{$ca_search}%'");
  if($it_id = $code_result['it_id'])
    $status = 1;
  
  # 3. 수급자 매칭 조회
  if($status == 1) {
    $pen_result = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
      'usrId' => $member['mb_id'],
      'entId' => $member['mb_entId'],
      'penLtmNum' => $row['pen_ltm_num']
    ));
    if($pen_result['errorYN'] != 'N')
      json_response(500, '서버 오류가 발생했습니다.');
    $pen_result = $pen_result['data'] ? $pen_result['data'][0] : null;

    if(!$pen_result || $pen_result['penLtmNum'] != $row['pen_ltm_num'])
      $status = 0;
  }
  
  # 4. 업로드 테이블 INSERT
  $timestamp = time();
  $datetime = date('Y-m-d H:i:s', $timestamp);

  $sql = "
    INSERT INTO
      stock_data_upload
    SET
      mb_id = '{$member['mb_id']}',
      sd_status = '{$status}',
      sd_gubun = '{$row['gubun']}',
      sd_pen_nm = '{$row['pen_nm']}',
      sd_pen_type = '{$row['pen_type']}',
      sd_pen_ltm_num = '{$row['pen_ltm_num']}',
      sd_pen_jumin = '{$row['pen_jumin']}',
      sd_ca_name = '{$row['ca_name']}',
      sd_it_name = '{$row['it_name']}',
      sd_it_code = '{$row['it_code']}',
      sd_it_barcode = '{$row['it_barcode']}',
      sd_contract_date = '{$row['contract_date']}',
      sd_sale_date = '{$row['sale_date']}',
      sd_rent_date = '{$row['rent_date']}',
      created_at = '{$datetime}',
      updated_at = '{$datetime}'
  ";

  $result = sql_query($sql);
  if(!$result)
    json_response(500, 'DB 서버가 응답하지 않습니다.');
  
  if($status == 1) {
    // 매칭완료인 경우

    if($row['gubun'] == '00') {
      # 판매.

      // 재고에 있는지 조회
      $stock_result = get_stock($it_id, $row['it_barcode']);
      if($stock_result) {
        // 재고에 있으면
        $stock = $stock_result[0];
        // 재고가 판매완료 상태가 아니면 판매완료로 업데이트
        $stock['stateCd'] != '02';
        $stock_update[] = array(
          'stoId' => $stock['stoId'],
          'prodBarNum' => $row['it_barcode'],
          'stateCd' => '02'
        );
      } else {
        // 재고에 없으면
        // 보유재고에 판매완료로 등록
        $stock_insert[] = array(
          'prodId' => $it_id,
          'prodBarNum' => $row['it_barcode'],
          'stateCd' => '02'
        );
      }
    } else {
      # 대여.

      // 재고상태: 대여가능
      $stateCd = '01';

      // 대여 끝났는지 체크
      $rent_time = strtotime($row['rent_date']);
      if($timestamp < $rent_time) {
        $stateCd = '02'; // 대여중
      }

      // rental_data_table에 입력해둠 (나중에 재고 업데이트/등록 후 대여로그 작성하기 위해)
      $rental_data_table["{$it_id}-{$row['it_barcode']}"] = array(
        'strdate' => $row['sale_date'],
        'enddate' => $row['rent_date'],
        'ren_person' => $row['pen_nm']
      );

      // 재고에 있는지 조회
      $stock_result = get_stock($it_id, $row['it_barcode']);
      if($stock_result) {
        // 재고가 있으면
        $stock = $stock_result[0];
        // 최초계약일 조회
        $initial_contract_date = $stock['initialContractDate'];
        $stock_data = array(
          'stoId' => $stock['stoId'],
          'prodId' => $it_id,
          'prodBarNum' => $row['it_barcode'],
          'stateCd' => $stateCd
        );

        // 최초계약일이 없거나, 최초계약일보다 sale_date가 더 이전이면 최초계약일 업데이트
        if(!$initial_contract_date || strtotime($initial_contract_date) > strtotime($row['sale_date'])) {
          $stock_data['initialContractDate'] = date('Y-m-d H:i:s', strtotime($row['sale_date']));
        }
        $stock_update[] = $stock_data;
      } else {
        // 재고에 없으면
        // 보유재고에 등록
        $stock_insert[] = array(
          'prodId' => $it_id,
          'prodBarNum' => $row['it_barcode'],
          'stateCd' => $stateCd,
          'initialContractDate' => date('Y-m-d H:i:s', strtotime($row['sale_date']))
        );
      }
    }
  }
}

// 재고 insert
if($stock_insert) {
  $insert_result = api_post_call(EROUMCARE_API_STOCK_INSERT, array(
    'usrId' => $member["mb_id"],
    'entId' => $member["mb_entId"],
    'prods' => $stock_insert
  ));
  if($insert_result['errorYN'] != 'N') {
    json_response(500, $insert_result['message']);
  }

  // 대여로그 작성
  foreach($insert_result['data'] as $row) {
    $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
    if(!$rental_data) continue;

    $rental_log_id = "rental_log".round(microtime(true)).rand();
    $dis_total_date = G5_TIME_YMDHIS;

    sql_query("
      INSERT INTO
        g5_rental_log
      SET
        rental_log_Id = '{$rental_log_id}',
        stoId = '{$row['stoId']}',
        ordId = '',
        strdate = '{$rental_data['strdate']}',
        enddate = '{$rental_data['enddate']}',
        dis_total_date = '{$dis_total_date}',
        ren_person = '{$rental_data['ren_person']}',
        rental_log_division = '2'
    ");
  }
}

// 재고 update
if($stock_update) {
  $update_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
    'usrId' => $member["mb_id"],
    'entId' => $member["mb_entId"],
    'prods' => $stock_update
  ));
  if($update_result['errorYN'] != 'N') {
    json_response(500, $update_result['message']);
  }

  // 대여로그 작성
  foreach($stock_update as $row) {
    $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
    if(!$rental_data) continue;

    // 이미 같은 기간동안의 대여로그가 작성되어있는지 검색
    $check_result = sql_fetch("
      SELECT
        rental_log_Id
      FROM
        g5_rental_log
      WHERE
        stoId = '{$row['stoId']}' and
        strdate = '{$rental_data['strdate']}' and
        enddate = '{$rental_data['enddate']}' and
        rental_log_division = '2'
    ");

    // 이미 작성된 로그면 건너뜀
    if($check_result['rental_log_Id']) continue;

    $rental_log_id = "rental_log".round(microtime(true)).rand();
    $dis_total_date = G5_TIME_YMDHIS;

    sql_query("
      INSERT INTO
        g5_rental_log
      SET
        rental_log_Id = '{$rental_log_id}',
        stoId = '{$row['stoId']}',
        ordId = '',
        strdate = '{$rental_data['strdate']}',
        enddate = '{$rental_data['enddate']}',
        dis_total_date = '{$dis_total_date}',
        ren_person = '{$rental_data['ren_person']}',
        rental_log_division = '2'
    ");
  }
}

json_response(200, 'OK');
?>
