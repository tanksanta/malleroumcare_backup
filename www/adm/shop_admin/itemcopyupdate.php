<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

check_admin_token();

// if ($is_admin != "super")
//     alert("최고관리자만 접근 가능합니다.");

if (!trim($it_id))
  alert("복사할 상품코드가 없습니다.");

$t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $new_it_id);
if($t_it_id)
  alert("상품코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");

$row = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$new_it_id' ");
if ($row['cnt'])
  alert('이미 존재하는 상품코드 입니다.');

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' limit 1 ";
$cp = sql_fetch($sql);

// 시스템에 복사할 상품 추가
$it_img_dir = G5_DATA_PATH.'/item';
$gubun = $cate_gubun_table[substr($cp['ca_id'], 0, 2)];
$tax_info = $cp['it_taxInfo'] == '영세' ? '01' : '02';
$prod_color = [];
$prod_size = [];

$opt_subject_arr = explode(',', $cp['it_option_subject']);
$prod_color_idx = -1;
$prod_size_idx = -1;
for($i = 0; $i < count($opt_subject_arr); $i++) {
  if($opt_subject_arr[$i] == '색상') {
    $prod_color_idx = $i;
  } else if($opt_subject_arr[$i] == '사이즈') {
    $prod_size_idx = $i;
  }
}
if($prod_color_idx >= 0 || $prod_size_idx >= 0) {
  $option_query = sql_query("
    select * from {$g5['g5_shop_item_option_table']}
    where it_id = '{$it_id}'
  ");
  while($option = sql_fetch_array($option_query)) {
    $opt_arr = explode(chr(30), $option);
    if($prod_color_idx >= 0 && !in_array($opt_arr[$prod_color_idx], $prod_color)) {
      $prod_color[] = $opt_arr[$prod_color_idx];
    }
    if($prod_size_idx >= 0 && !in_array($opt_arr[$prod_size_idx], $prod_size)) {
      $prod_size[] = $opt_arr[$prod_size_idx];
    }
  }
}
$prod_color = implode('|', $prod_color);
$prod_size = implode('|', $prod_size);

$result = post_formdata(EROUMCARE_API_PROD_INSERT, array(
  'usrId' => $member["mb_id"],
  'entId' => $cp['entId'],
  'prodNm' => $cp['it_name'], // 제품 명
  'prodSym' => $cp['prodSym'], // 재질
  'prodWeig' => $cp['prodWeig'], // 중량
  'prodColor' => $prod_color, // 컬러
  'prodSize' => $prod_size, // 사이즈
  'prodSizeDetail' => $cp['prodSizeDetail'], // 사이즈 상세정보
  'prodDetail' => $cp['it_explan'], // 상세정보
  'prodPayCode' => $cp['ProdPayCode'], // 제품코드
  'prodSupYn' => $cp['prodSupYn'], //  유통 미유통
  'prodSupPrice' => $cp['it_cust_price'], // 공급가격
  'prodOflPrice' => $cp['it_price'], // 판매가격
  'rentalPrice' => $cp['it_rental_price'], // 대여가격(1일)
  'rentalPriceExtn' => $cp['it_rental_price'], //  대여연장가격(1일)
  'prodStateCode' => '03', // 제품 등록상태 (01:등록신청 / 02:수정신청 / 03:등록)
  'supId' => $cp['supId'], //  공급자아이디
  'itemId' => $cp['it_thezone'], //  아이템 아이디
  'subItem' => '', //  서브 아이템
  'gubun' => $gubun, //  00=구매 01=대여
  'taxInfoCd' => $tax_info, //  01=영세 02=과세
  'file1' => $cp['it_img1'] ? new cURLFile($it_img_dir.'/'.$cp['it_img1']) : '',
  'file2' => $cp['it_img2'] ? new cURLFile($it_img_dir.'/'.$cp['it_img2']) : '',
  'file3' => $cp['it_img3'] ? new cURLFile($it_img_dir.'/'.$cp['it_img3']) : '',
  'file4' => $cp['it_img4'] ? new cURLFile($it_img_dir.'/'.$cp['it_img4']) : '',
  'file5' => $cp['it_img5'] ? new cURLFile($it_img_dir.'/'.$cp['it_img5']) : '',
  'file6' => $cp['it_img6'] ? new cURLFile($it_img_dir.'/'.$cp['it_img6']) : '',
  'file7' => $cp['it_img7'] ? new cURLFile($it_img_dir.'/'.$cp['it_img7']) : '',
  'file8' => $cp['it_img8'] ? new cURLFile($it_img_dir.'/'.$cp['it_img8']) : '',
  'file9' => $cp['it_img9'] ? new cURLFile($it_img_dir.'/'.$cp['it_img9']) : '',
  'file10' => $cp['it_img10'] ? new cURLFile($it_img_dir.'/'.$cp['it_img10']) : ''
));

if($result['errorYN'] != 'N' || !$result['data']['prodId'])
  alert('시스템 오류 발생: ' . $result['message']);

$new_it_id = $result['data']['prodId'];

// 상품테이블의 필드가 추가되어도 수정하지 않도록 필드명을 추출하여 insert 퀴리를 생성한다. (상품코드만 새로운것으로 대체)
$sql_common = "";
$fields = sql_field_names($g5['g5_shop_item_table']);
foreach($fields as $fld) {
  if ($fld == 'it_id' || $fld == 'it_sum_qty' || $fld == 'it_hit' || $fld == 'it_use_cnt' || $fld == 'it_use_avg' || $fld == 'it_use' || $fld == 'pt_comment' || $fld == 'pt_qa' || $fld == 'pt_good' || $fld == 'pt_nogood' || $fld == 'pt_num' || $fld == 'pt_end' || $fld == 'pt_reserve' || $fld == 'pt_reserve_use' || $fld == 'it_time' || $fld == 'it_update_time')
    continue;

  $sql_common .= " , $fld = '".addslashes($cp[$fld])."' ";
}

$sql_common .= " , it_time = '".G5_TIME_YMDHIS."' ";
$sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
$sql_common .= " , pt_num = '".G5_SERVER_TIME."' ";

$sql = "
  insert {$g5['g5_shop_item_table']}
  set it_id = '$new_it_id'
  $sql_common
";
sql_query($sql);

// 선택/추가 옵션 copy
$opt_sql = "
  insert ignore into {$g5['g5_shop_item_option_table']} ( io_id, io_type, it_id, io_price, io_stock_qty, io_noti_qty, io_use )
  select io_id, io_type, '$new_it_id', io_price, io_stock_qty, io_noti_qty, io_use
  from {$g5['g5_shop_item_option_table']}
  where it_id = '$it_id'
  order by io_no asc
";
sql_query($opt_sql);

// html 에디터로 첨부된 이미지 파일 복사
$hfn = array('it_explan', 'it_mobile_explan', 'pt_explan', 'pt_mobile_explan');

for($j = 0; $j < count($hfn); $j++) {

  // 값정리
  $fkey = $hfn[$j];
  $fvalue = $cp[$fkey];

  if($fvalue) {
    $matchs = get_editor_image($fvalue, false);

    // 파일의 경로를 얻어 복사
    for($i=0;$i<count($matchs[1]);$i++) {
      $p = parse_url($matchs[1][$i]);
      if(strpos($p['path'], "/data/") != 0)
        $src_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
      else
        $src_path = $p['path'];

      $srcfile = G5_PATH.$src_path;

      if(is_file($srcfile)) {
        $dstfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $srcfile);

        // 파일명에서 기존 상품코드 제거
        $dstfile = str_replace("_".$it_id, "", $dstfile);

        copy($srcfile, $dstfile);

        $newfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $matchs[1][$i]);

        // 파일명에서 기존 상품코드 제거
        $newfile = str_replace("_".$it_id, "", $newfile);

        $fvalue = str_replace($matchs[1][$i], $newfile, $fvalue);
      }
    }

    $sql = " update {$g5['g5_shop_item_table']} set $fkey = '".addslashes($fvalue)."' where it_id = '$new_it_id' ";
    sql_query($sql);
  }
}

// 상품이미지 복사
function copy_directory($src_dir, $dest_dir)
{
  if($src_dir == $dest_dir)
    return false;

  if(!is_dir($src_dir))
    return false;

  if(!is_dir($dest_dir)) {
    @mkdir($dest_dir, G5_DIR_PERMISSION);
    @chmod($dest_dir, G5_DIR_PERMISSION);
  }

  $dir = opendir($src_dir);
  while (false !== ($filename = readdir($dir))) {
    if($filename == "." || $filename == "..")
      continue;

    $files[] = $filename;
  }

  for($i=0; $i<count($files); $i++) {
    $src_file = $src_dir.'/'.$files[$i];
    $dest_file = $dest_dir.'/'.$files[$i];
    if(is_file($src_file)) {
      copy($src_file, $dest_file);
      @chmod($dest_file, G5_FILE_PERMISSION);
    }
  }
}

// 파일복사
$dest_path = G5_DATA_PATH.'/item/'.$new_it_id;
@mkdir($dest_path, G5_DIR_PERMISSION);
@chmod($dest_path, G5_DIR_PERMISSION);
$comma = '';
$sql_img = '';

for($i=1; $i<=10; $i++) {
  $file = G5_DATA_PATH.'/item/'.$cp['it_img'.$i];
  $new_img = '';

  if(is_file($file)) {
    $dstfile = $dest_path.'/'.basename($file);
    copy($file, $dstfile);
    @chmod($dstfile, G5_FILE_PERMISSION);
    $new_img = $new_it_id.'/'.basename($file);
  }

  $sql_img .= $comma." it_img{$i} = '$new_img' ";
  $comma = ',';
}

$sql = "
  update {$g5['g5_shop_item_table']}
  set $sql_img
  where it_id = '$new_it_id'
";
sql_query($sql);

$qstr = "ca_id=$ca_id&amp;sfl=$sfl&amp;sca=$sca&amp;page=$page&amp;stx=".urlencode($stx)."&amp;save_stx=".urlencode($save_stx);

goto_url("itemlist.php?$qstr");
?>
