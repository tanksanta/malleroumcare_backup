<?php
include_once('./_common.php');

if(! function_exists('column_char')) {
    function column_char($i) {
        return chr( 65 + $i );
    }
}

function price_kor($total_price){
    $price=$total_price;
    $trans_kor=array("","일","이","삼","사","오","육","칠","팔","구");
    $price_unit=array("","십","백","천","만","십","백","천","억","십","백","천","조","십","백","천");
    $valuecode=array("","만","억","조");
    $value=strlen($price);
    $k=0;
    for($i=$value;$i>0;$i--){
        $vv="";
        $vc=substr($price,$k,1);
        $vt=$trans_kor[$vc]; 
        $k++;

        if($i%5 ==0){
            $vv=$valuecode[$i/5];
        }else{
            if($vc)
        { 
            $vv=$price_unit[$i-1];}
        }
    $vr=$vr.$vt.$vv;
    }
    return $vr;
}

//select * from g5_shop_cart where od_id = '2021102117104135';
//단가 : ct_price, 수량 : ct_qty
// $od_ids = ['2021102116072366', '2021102114591515', '2021102116005376', '2021102118084479'];
$od_ids = explode(",", $_GET["od_id"]);
$ct_id = $_GET["ct_id"];

$rows = [];
$mb;
$total_qty = 0; //총 수량
$total_basic_price = 0; //총 공급가
$total_tax_price = 0; //총 부가세
$total_ct_price_stotal = 0; //총 합계
    
foreach($od_ids as $od_id) { 
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' AND od_del_yn = 'N' ";
    $od = sql_fetch($sql);
    $prodList = [];
    $prodListCnt = 0;
    $deliveryTotalCnt = 0;
    $delivery_insert=0;
    if (!$od['od_id']) {
      alert("해당 주문번호로 주문서가 존재하지 않습니다.");
    } else {
      if($od["ordId"]) {
        $sendData = [];
        $sendData["penOrdId"] = $od["ordId"];
        $sendData["uuid"] = $od["uuid"];
    
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, 9901);
        curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/order/selectList");
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $res = curl_exec($oCurl);
        curl_close($oCurl);
    
        $result = json_decode($res, true);
        $result = $result["data"];
    
        if($result) {
          foreach($result as $data) {
            $thisProductData = [];
            $thisProductData["prodId"] = $data["prodId"];
            $thisProductData["prodColor"] = $data["prodColor"];
            $thisProductData["stoId"] = $data["stoId"];
            $thisProductData["prodBarNum"] = $data["prodBarNum"];
            $thisProductData["penStaSeq"] = $data["penStaSeq"];
            array_push($prodList, $thisProductData);
          }
        }
      } else {
        $sto_imsi="";
        $sql_ct = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
        $result_ct = sql_query($sql_ct);
        //배송정보
    
        while($row_ct = sql_fetch_array($result_ct)) {
          $sto_imsi .=$row_ct['stoId'];
    
          //배송정보
          if($row_ct['ct_combine_ct_id']||$row_ct['ct_delivery_num']){
            $delivery_insert++;
          }
        }
        $stoIdDataList = explode('|',$sto_imsi);
        $stoIdDataList=array_filter($stoIdDataList);
        $stoIdData = implode("|", $stoIdDataList);
      }
    }
    $mb = get_member($od['mb_id']);
    $od_status = get_step($od['od_status']);
    $pay_status = get_pay_step($od['od_pay_state']);
    
    $od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";
    
    $where = " a.od_id = '$od_id' ";
    if ($ct_id) {
        $where .= " AND a.ct_id = '{$ct_id}' ";
    }
    // 상품목록
    $sql = "
      select
        a.ct_id,
        a.it_id,
        a.it_name,
        a.cp_price,
        a.ct_notax,
        a.ct_send_cost,
        a.ct_sendcost,
        a.it_sc_type,
        a.pt_it,
        a.pt_id,
        b.ca_id,
        b.ca_id2,
        b.ca_id3,
        b.pt_msg1,
        b.pt_msg2,
        b.pt_msg3,
        a.ct_status,
        b.it_model,
        b.it_outsourcing_use,
        b.it_outsourcing_company,
        b.it_outsourcing_manager,
        b.it_outsourcing_email,
        b.it_outsourcing_option,
        b.it_outsourcing_option2,
        b.it_outsourcing_option3,
        b.it_outsourcing_option4,
        b.it_outsourcing_option5,
        a.pt_old_name,
        a.pt_old_opt,
        a.ct_uid,
        a.prodMemo,
        a.prodSupYn,
        a.ct_qty,
        a.ct_stock_qty,
        b.it_img1,
        a.ordLendStrDtm,
        a.ordLendEndDtm,
        a.ct_combine_ct_id,
        a.ct_delivery_company,
        a.ct_delivery_num
      from
        {$g5['g5_shop_cart_table']} a
      left join
        {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
      where
        {$where}
      group by
        a.it_id, a.ct_uid
      order by
        a.ct_id
    ";
    
    $result = sql_query($sql);
    
    $carts = array();
    $cate_counts = array();
    
    for($i=0; $row=sql_fetch_array($result); $i++) {
    
      $cate_counts[$row['ct_status']] += 1;
    
      // 상품의 옵션정보
      $sql = "
        select
          MT.*,
          b.prodSupYn,
          b.it_taxInfo,
          b.it_type3
        from
          {$g5['g5_shop_cart_table']} MT
          left join
            {$g5['g5_shop_item_table']} b on ( MT.it_id = b.it_id )
        where
          MT.od_id = '{$od['od_id']}' and
          MT.it_id = '{$row['it_id']}' and
          MT.ct_uid = '{$row['ct_uid']}'
        order by
          MT.io_type asc, MT.ct_id asc
      ";
      $res = sql_query($sql);
    
      $row['options_span'] = sql_num_rows($res);
    
      $row['options'] = array();
      for($k=0; $opt=sql_fetch_array($res); $k++) {
    
        $opt_price = 0;
    
        if($opt['io_type'])
          $opt_price = $opt['io_price'];
        else
          $opt_price = $opt['ct_price'] + $opt['io_price'];
    
        $opt["opt_price"] = $opt_price;
    
        // 소계
        $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
        if($opt["prodSupYn"] == "Y") {
          $opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
        }
        // 단가 역산
        $opt["opt_price"] = $opt['ct_price_stotal'] ? @round($opt['ct_price_stotal'] / ($opt["ct_qty"] - $opt["ct_stock_qty"])) : 0;
    
        // 공급가액
        $opt["basic_price"] = $opt['ct_price_stotal'];
        // 부가세
        $opt["tax_price"] = 0;
        if($opt['it_taxInfo'] != "영세" ) {
          // 공급가액
          $opt["basic_price"] = round($opt['ct_price_stotal'] / 1.1);
          // 부가세
          $opt["tax_price"] = round($opt['ct_price_stotal'] / 11);
        }
    
        $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];
    
        $row['options'][] = $opt;
      }
    
    
      // 합계금액 계산
      $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
                      SUM(ct_qty) as qty,
                      SUM(ct_discount) as discount,
                      SUM(ct_send_cost) as sendcost
                  from {$g5['g5_shop_cart_table']}
                  where it_id = '{$row['it_id']}'
                      and od_id = '{$od_id}'
                      and ct_uid = '{$row['ct_uid']}'";
      $sum = sql_fetch($sql);
    
      $row['sum'] = $sum;
    
      $carts[] = $row;
    }
    
    // 배송정보 로그
    $logs = get_delivery_log($od_id);
    
    foreach($carts as $cart) {
    
        //바코드 정보
        $stoIdDataList = explode('|',$sto_imsi);
        $stoIdDataList = array_filter($stoIdDataList);
        $stoIdData = implode("|", $stoIdDataList);
    
        $barcode=[];
        $sendData["stoId"] = $stoIdData;
        $oCurl = curl_init();
        $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
        $result_again = $res;
        $result_again =$result_again['data'];
    
        for($k=0; $k < count($result_again); $k++) {
          if($result_again[$k]['prodBarNum']) {
            array_push($barcode,$result_again[$k]['prodBarNum']);
          }
        }
        asort($barcode);
        $barcode2=[];
        $y = 0;  
        foreach($barcode as $key=>$val)  
        {  
          $new_key = $y;  
          $barcode2[$new_key] = $val;  
          $y++;  
        }
        $barcode_string="";
        if (!is_benefit_item($it)) {
          for ($y=0; $y<count($barcode2); $y++) {
              #처음
              if ($y==0) {
                  $barcode_string .= $barcode2[$y];
                  continue;
              }
              #현재 바코드 -1이 전바코드와 같지않음
              if (intval($barcode2[$y])-1 !== intval($barcode2[$y-1])) {
                  $barcode_string .= ",".$barcode2[$y];
              }
              #현재 바코드 -1이 전바코드와 같음
              if (intval($barcode2[$y])-1 == intval($barcode2[$y-1])) {
                  //다음번이 연속되지 않을 경우
                  if (intval($barcode2[$y])+1 !== intval($barcode2[$y+1])) {
                      $barcode_string .= "-".$barcode2[$y];
                  }
              }
          }
          $barcode_string .= " ";
        }
    
        $options = $cart['options'];
        foreach($options as $option) {
            $it_name = $cart['it_name'];
            if ($it_name != $option['ct_option']) {
                $it_name .= "[".$option['ct_option']."]";
            }
    
            $delivery_info = "";
            $delivery_num = "";
            $delivery_company = "";
            if($cart['ct_combine_ct_id']){
                $sql_ctd ="select `ct_delivery_company`,`ct_delivery_num` from `g5_shop_cart` where `ct_id` = '".$cart['ct_combine_ct_id']."'";
                $result_ctd = sql_fetch($sql_ctd);
                $delivery_company = $result_ctd['ct_delivery_company'];
                $delivery_num = $result_ctd['ct_delivery_num'];
            }
            else {
                if($cart['ct_delivery_num']){
                    $delivery_company = $cart['ct_delivery_company'];
                    $delivery_num = $cart['ct_delivery_num'];
                }
            }
    
            if (strlen($delivery_num)) {
                foreach($delivery_companys as $data){ 
                    if($delivery_company == $data["val"] ){
                        $delivery_company = $data["name"];
                    }
                }
                $delivery_info = "(".$delivery_company.")\n".$delivery_num;
            }
            $date = $option['ct_ex_date'];
            $arr = array($option['ct_ex_date'], $it_name, $option['ct_qty'], $option['opt_price'], $option['basic_price'], $option['tax_price'], $option['ct_price_stotal'], $mb['mb_name'], $barcode_string, $delivery_info);
            array_push($rows, $arr);
    
            $total_qty += $option['ct_qty'];
            $total_basic_price += $option['basic_price'];
            $total_tax_price += $option['tax_price'];
            $total_ct_price_stotal += $option['ct_price_stotal'];
        }
    }
    // 배송비
    $od['od_send_cost'] += $od['od_send_cost2'];
    if ($od['od_send_cost'] > 0) {
        $arr = array($date, "^배송비", '1', $od['od_send_cost'], (int)($od['od_send_cost'] / 1.1), ($od['od_send_cost'] - (int)($od['od_send_cost'] / 1.1)), $od['od_send_cost'], $mb['mb_name'], $od_id, "");
        array_push($rows, $arr);
    }
    // 매출할인
    if ($od['od_sales_discount'] > 0) {
        $arr = array($date, "^매출할인", '1', -($od['od_sales_discount']), -((int)($od['od_sales_discount'] / 1.1)), -($od['od_sales_discount'] - (int)($od['od_sales_discount'] / 1.1)), -($od['od_sales_discount']), $mb['mb_name'], $od_id, "");
        array_push($rows, $arr);
    }
    // 쿠폰할인
    $coupon_price = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];
    if ($coupon_price > 0) {
        $arr = array($date, "^쿠폰할인", '1', -($coupon_price), -((int)($coupon_price / 1.1)), -($coupon_price - (int)($coupon_price / 1.1)), -($coupon_price), $mb['mb_name'], $od_id, "");
        array_push($rows, $arr);
    }
}

// $data = array_merge([$headers], $rows, [$totals], [$dates]);

include_once(G5_LIB_PATH.'/PHPExcel.php');
$excel = new PHPExcel();
$widths = [1, 8, 15, 6, 10, 8, 8, 10, 12, 10, 12];
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);

$allborders_thin = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
);
$allborders_medium = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM
        )
    )
);
$allborders_none = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_NONE,
            'color' => array('rgb' => 'FFFFFF')
        )
    )
);
$outline_medium = array(
    'borders' => array(
        'outline' => array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM
        )
    )
);

$styleArray = array(
  'font' => array(
    'size' => 7,
    'name' => 'Arial'
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  ),
  'borders' => array(
    'allborders' => array(
        'style' => PHPExcel_Style_Border::BORDER_NONE
    )
)
);
$excel->getDefaultStyle()->applyFromArray($styleArray);
// $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(30);

// 폰트&볼드처리
$excel->getActiveSheet()->getStyle('B3:E5')->getFont()->setSize(8);
$excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('H1:H5')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('J1:J3')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('B7:K7')->getFont()->setBold(true);

// 텍스트 가운데 정렬
$excel->getActiveSheet()->getStyle('B1:H5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle('J1:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle('A9:K9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//회사정보
$excel->getActiveSheet()->mergeCells('B1:E2');
$excel->getActiveSheet()->setCellValue("B1", "거래명세서");
$excel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20);
$excel->getActiveSheet()->mergeCells('G1:G5');
$excel->getActiveSheet()->setCellValue("G1", "공\n급\n자");
$excel->getActiveSheet()->mergeCells('H1:H2');
$excel->getActiveSheet()->setCellValue("H1", "일자");
$excel->getActiveSheet()->mergeCells('I1:I2');
$excel->getActiveSheet()->setCellValue("I1", date("Y/m/d"));
$excel->getActiveSheet()->mergeCells('J1:J2');
$excel->getActiveSheet()->setCellValue("J1", "TEL");
$excel->getActiveSheet()->mergeCells('K1:K2');
$excel->getActiveSheet()->setCellValue("K1", "051-643-1300");

$companyInfo = "{$mb['mb_name']}\n{$mb['mb_addr1']} {$mb['mb_addr2']}\n{$mb['mb_tel']}";
$excel->getActiveSheet()->mergeCells('B3:E5');
$excel->getActiveSheet()->setCellValue("B3", $companyInfo);

$excel->getActiveSheet()->setCellValue("H3", "사업자등록번호");
$excel->getActiveSheet()->setCellValue("I3", "617-85-14330");
$excel->getActiveSheet()->setCellValue("J3", "이름");
$excel->getActiveSheet()->setCellValue("K3", "신종호");
$excel->getActiveSheet()->setCellValue("H4", "상호");
$excel->getActiveSheet()->mergeCells('I4:K4');
$excel->getActiveSheet()->setCellValue("I4", "이로움(THKC)");
$excel->getActiveSheet()->setCellValue("H5", "주소");
$excel->getActiveSheet()->mergeCells('I5:K5');
$excel->getActiveSheet()->setCellValue("I5", "인천 서구 정서진8로5, 403동");
$excel->getActiveSheet()->mergeCells('B7:K7');
$price_num = number_format($total_ct_price_stotal);
$price_kor = price_kor($total_ct_price_stotal);
$excel->getActiveSheet()->setCellValue("B7", "금액 : {$price_kor}원 정                                          (￦{$price_num})");
$excel->getActiveSheet()->getStyle('B7')->getFont()->setSize(12);

$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('../../img/shinjongho.tif');
// $objDrawing->setPath('./img/shinjongho.tif');
$objDrawing->setCoordinates('K3');
$objDrawing->setOffsetX(10); 
// $objDrawing->setOffsetY(-20);
$objDrawing->setWidth(52); 
$objDrawing->setHeight(52); 
$objDrawing->setWorksheet($excel->getActiveSheet());

// 주문 상품 내역
$headers = ['일자', '품명[옵션]', '수량', '단가(Vat포함)', '공급가액', '부가세', '합계', '성명', '바코드', '송장번호'];
$excel->getActiveSheet()
        ->getStyle("B9:K9")
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('00F2F2F2');
$data = array_merge([$headers], $rows);
$excel->getActiveSheet()->fromArray($data, null, 'B9');
$form_ends = 9 + count($rows); // 데이터 마지막 행의 숫자

// footer
$footers = [
    ['수량', $total_qty, '공급가', $total_basic_price, '부가세', $total_tax_price, '인수', '', '(인)'],
    // ['전잔액', '10000', '후잔액', '900000', '총합계', $total_ct_price_stotal],
    ['', '', '', '', '총합계', $total_ct_price_stotal],
    ['비고'],
    ['계좌번호 : KEB하나은행 ((주)티에이치케이컴퍼니) 630008886056']
];
$footer_start = $form_ends+2;
$footer_start_1 = $footer_start + 1;

$excel->getActiveSheet()->fromArray($footers, null, "B{$footer_start}");
$footer_end = $excel->getActiveSheet()->getHighestRow();
$footer_end_1 = $footer_end - 1;

$excel->getActiveSheet()->mergeCells("H{$footer_start}:I{$footer_start_1}");
$excel->getActiveSheet()->mergeCells("J{$footer_start}:K{$footer_start_1}");
$excel->getActiveSheet()->mergeCells("B{$footer_end}:K{$footer_end}");
$excel->getActiveSheet()->mergeCells("C{$footer_end_1}:K{$footer_end_1}");
$excel->getActiveSheet()->getStyle("B{$footer_start}:K{$footer_end}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle("C{$footer_start}:C{$footer_start_1}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->getActiveSheet()->getStyle("E{$footer_start}:E{$footer_start_1}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->getActiveSheet()->getStyle("G{$footer_start}:G{$footer_start_1}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->getActiveSheet()->getStyle("B{$footer_start}:B{$footer_end}")->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle("D{$footer_start}:D{$footer_start_1}")->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle("F{$footer_start}:F{$footer_start_1}")->getFont()->setBold(true);

// number format 처리
$excel->getActiveSheet()->getStyle("C{$footer_start}:C{$footer_start_1}")->getNumberFormat()->setFormatCode('#,##0_-');
$excel->getActiveSheet()->getStyle("E10:H{$footer_end_1}")->getNumberFormat()->setFormatCode('#,##0_-');

// 테두리 처리
$excel->getActiveSheet()->getStyle('A1')->applyFromArray($allborders_none);
$excel->getActiveSheet()->getStyle('B3:E5')->applyFromArray($allborders_thin);
$excel->getActiveSheet()->getStyle("B9:K{$form_ends}")->applyFromArray($allborders_thin);
$excel->getActiveSheet()->getStyle('G1:K5')->applyFromArray($allborders_medium);
$excel->getActiveSheet()->getStyle('B7:K7')->applyFromArray($allborders_medium);
$excel->getActiveSheet()->getStyle("B{$footer_start}:K{$footer_end}")->applyFromArray($allborders_thin);
$excel->getActiveSheet()->getStyle("B{$footer_start}:K{$footer_end}")->applyFromArray($outline_medium);

// 기본 셀높이 20으로 설정
for ($i=0; $i < $footer_end; $i++) {
    $row = $i+1;
    $excel->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(20);
}

// 상품항목 셀높이 자동으로 설정
for ($i=9; $i < $form_ends; $i++) {
    $row = $i+1;
    $excel->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(-1);
}
$excel->getActiveSheet()->getStyle("B1:K{$form_ends}")->getAlignment()->setWrapText(true);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래명세서.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
