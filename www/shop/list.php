<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/list.php');
    return;
}

// 상품 정렬 없을 시 커스텀(추천순)으로 지정
if (!$sort) {
	$sort = 'custom';
}

// 상품 리스트에서 다른 필드로 정렬을 하려면 아래의 배열 코드에서 해당 필드를 추가하세요.
if( isset($sort) && ! in_array($sort, array('custom', 'it_sum_qty', 'it_price', 'it_use_avg', 'it_use_cnt', 'it_update_time', 'pt_comment', 'it_type1', 'it_type2', 'it_type3', 'it_type4', 'it_type5')) ){
    $sort='';
}

if(!$ca_sub) $ca_sub = [];
if(!$it_type) $it_type = [];

$sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' and ca_use = '1'  ";
$ca = sql_fetch($sql);

if (!$ca['ca_id'])
    alert('등록된 분류가 없습니다.');

// 테마체크
$at = apms_ca_thema($ca_id, $ca);
if(!defined('THEMA_PATH')) {
	include_once(G5_LIB_PATH.'/apms.thema.lib.php');
}

// 본인인증, 성인인증체크
if(!$is_admin) {
    $msg = shop_member_cert_check($ca_id, 'list');
    if($msg)
        alert($msg, G5_SHOP_URL);
}

$show_main_big_banner = true;

// 리스트 분류
$cate = array();
$cate = apms_item_category_array($ca_id);
$is_cate = (count($cate) > 0) ? true : false;

$thumb_w = $ca['ca_'.MOBILE_.'img_width'];
$thumb_h = $ca['ca_'.MOBILE_.'img_height'];
$list_mods = $ca['ca_'.MOBILE_.'list_mod'];
$list_rows = $ca['ca_'.MOBILE_.'list_row'];

// 스킨설정
$list_skin = $at['list'];

$wset = array();
if($ca['as_'.MOBILE_.'list_set']) {
	$wset = apms_unpack($ca['as_'.MOBILE_.'list_set']);
}

// 데모
if($is_demo) {
	@include($demo_setup_file);
}

// List
$list_skin_path = G5_SKIN_PATH.'/apms/list/'.$list_skin;
$list_skin_url = G5_SKIN_URL.'/apms/list/'.$list_skin;

// 추가설정
$sql_apms_where = $sql_apms_orderby = '';
@include_once($list_skin_path.'/list.head.skin.php');

$order_by = ($sort != "") ? $sort.' '.$sortodr.' ,'.$sql_apms_orderby.' it_order, pt_num desc, it_id desc' : $sql_apms_orderby.' it_order, pt_num desc, it_id desc'; // 상품 출력순서가 있다면
if ( THEMA_KEY == 'partner' ) {
	$where = "it_use_partner = '1'";
}else{
	$where = "it_use = '1'";
}
if(isset($type) && $type) {
	$where .= " and it_type{$type} = '1'";
	$qstr .= '&amp;type='.$type;
}
if(isset($q) && $q) {
	$q = get_search_string($q);
	$trimmed_q = preg_replace("/\s+/", "", $q);
	$where .= " and (REPLACE(`it_name`, ' ', '') like '%$trimmed_q%' or REPLACE(`ProdPayCode`, ' ', '') like '%$trimmed_q%' or REPLACE(`pt_tag`, ' ', '') like '%$trimmed_q%') ";
}
// $where .= " and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%')";
$ca_sub_orderby = '';
$where .= " and ( 1 <> 1 ";
if($ca_sub) {
	$ca_sub_orderby = " case ";
	$ca_sub_idx = 1;
	foreach($ca_sub as $sub) {
		$ca_id_sub = $ca_id.$sub;
		$where .= " or jt.ca_id like '$ca_id_sub%'
		or ca_id2 like '$ca_id_sub%'
		or ca_id3 like '$ca_id_sub%' 
		or ca_id4 like '$ca_id_sub%'
		or ca_id5 like '$ca_id_sub%' 
		or ca_id6 like '$ca_id_sub%'
		or ca_id7 like '$ca_id_sub%' 
		or ca_id8 like '$ca_id_sub%'
		or ca_id9 like '$ca_id_sub%' 
		or ca_id10 like '$ca_id_sub%'
		";
		$ca_sub_orderby .= " when (jt.ca_id like '$ca_id_sub%'
		or ca_id2 like '$ca_id_sub%'
		or ca_id3 like '$ca_id_sub%') then $ca_sub_idx ";
		$ca_sub_idx++;
	}
	$ca_sub_orderby .= " end, ";
} else {
	$where .= " or jt.ca_id like '$ca_id%'
	or ca_id2 like '$ca_id%'
	or ca_id3 like '$ca_id%'
	or ca_id4 like '$ca_id%'
	or ca_id5 like '$ca_id%'
	or ca_id6 like '$ca_id%'
	or ca_id7 like '$ca_id%'
	or ca_id8 like '$ca_id%'
	or ca_id9 like '$ca_id%'
	or ca_id10 like '$ca_id%'
	";
}
$where .= " ) ";

/*$where .= " and ( ca_id like '$ca_id%'
	or ca_id2 like '$ca_id%'
	or ca_id3 like '$ca_id%'
	or ca_id4 like '$ca_id%'
	or ca_id5 like '$ca_id%'
	or ca_id6 like '$ca_id%'
	or ca_id7 like '$ca_id%'
	or ca_id8 like '$ca_id%'
	or ca_id9 like '$ca_id%'
	or ca_id10 like '$ca_id%' ) ";*/
$where .= $sql_apms_where;

/*if(!$_COOKIE["prodSupYn"]){
	setcookie("prodSupYn", "Y", time() + 86400 * 3650, "/");
	$where .= " AND prodSupYn = 'Y'";
}

if($_COOKIE["prodSupYn"] == "Y" || $_COOKIE["prodSupYn"] == "N"){
	$prodSupYn = $_COOKIE["prodSupYn"];
}*/
if(!$prodSupYn) $prodSupYn = 'Y';

if($prodSupYn) {
	//setcookie("prodSupYn", $prodSupYn, time() + 86400 * 3650, "/");

	if($prodSupYn == "Y" || $prodSupYn == "N"){
		$where .= " AND prodSupYn = '{$prodSupYn}'";
	}
}

// 기타설정(태그선택)
if($it_type) {
  $where .= ' and ( 1 <> 1 ';
  foreach($it_type as $type) {
    $where .= ' or it_type'.$type.' = 1 ';
  }
  $where .= ' ) ';
}

// 튜토리얼 상품 안보이게 수정
$where .= " AND it_id NOT IN ('PRO2021072200013', 'PRO2021072200012') ";

// 정렬
$list_sort_href = './list.php?ca_id='.$ca_id.$qstr.'&amp;sort=';

if($sort) $qstr .= '&amp;sort='.$sort;
if($sortodr) $qstr .= '&amp;sortodr='.$sortodr;

// 상위분류
$ca_id_len = strlen($ca_id);
$up_href = '';
if ($ca_id_len > 2) {
	$len1 = $ca_id_len - 2;
	$up_href = './list.php?ca_id='.substr($ca_id,0,$len1).$qstr;
}

$g5['title'] = $ca['ca_name'].' 리스트';
if ($ca['ca_include_head'] && is_include_path_check($ca['ca_include_head']))
    @include_once($ca['ca_include_head']);
else
    include_once(G5_SHOP_PATH.'/_head.php');

// 상단 HTML
echo '<div id="sct_hhtml">'.conv_content($ca['ca_'.MOBILE_.'head_html'], 1).'</div>'.PHP_EOL;

// 상품 리스트
$list = array();

/*if(!$list_mods) $list_mods = 4;
if(!$list_rows) $list_rows = 5;*/
$list_mods = 4;
$list_rows = 3;

// 총몇개 = 한줄에 몇개 * 몇줄
$item_rows = $list_rows * $list_mods;

// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page < 1) $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $item_rows;

// 전체 페이지 계산
$row2 = sql_fetch(" select count(*) as cnt from `{$g5['g5_shop_item_table']}` as jt
INNER JOIN g5_shop_category c ON jt.ca_id = c.ca_id AND c.ca_use='1'
where $where ");

// if selected count is 0 then it means it has searched from the subcategory
// which is dedicated from previous selection 2022.08.03 by Jake
if ( strlen($ca_id) > 2 && $row2['cnt'] == 0 ) {
	alert("현재 선택된 품목은 [$ca[ca_name]] 입니다.\\n전체 항목에서 검색 하시려면 [판매품목]을 클릭 하세요.");
//<script>	
//	location.href = './list.php?ca_id='.substr($ca_id,0,$len1).$qstr;
//</script>	
	$ca_sub=substr($ca_id,3,4);
	$ca_id=substr($ca_id,0,2);
	goto_url("./list.php?ca_id=$ca_id&ca_sub%5B%5D=$ca_sub");
	}

$total_count = $row2['cnt'];
$total_page  = ceil($total_count / $item_rows);

$num = $total_count - ($page - 1) * $item_rows;

// 커스텀 인덱스
if ($sort != 'custom') {
	$list_sql = "select * from `{$g5['g5_shop_item_table']}` as jt
	INNER JOIN g5_shop_category c ON jt.ca_id = c.ca_id AND c.ca_use='1'
	where  $where order by $ca_sub_orderby $order_by limit $from_record, $item_rows";
} else {
	$list_sql = "select *
				 from (select *
					   from `{$g5['g5_shop_item_table']}` a
					   left join (select it_id as temp_it_id, ca_id as temp_ca_id, custom_index from g5_shop_item_custom_index) b
					   on (a.it_id = b.temp_it_id) and  (temp_ca_id = '{$ca_id}')) jt
					   INNER JOIN g5_shop_category c ON jt.ca_id = c.ca_id AND c.ca_use='1' 
				 where $where order by $ca_sub_orderby custom_index is null asc, custom_index asc, pt_num asc, it_id asc limit $from_record, $item_rows
				 ";
}
//print_r2($list_sql);
$result = sql_query($list_sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$thisOptionList = [];

	# 210204 옵션
	$thisOptionSQL = sql_query("
		SELECT io_id
		FROM g5_shop_item_option
		WHERE it_id = '{$row["it_id"]}'
	");
	for($ii = 0; $subRow = sql_fetch_array($thisOptionSQL); $ii++){
		array_push($thisOptionList, $subRow["io_id"]);
	}

	// 사업소별 판매가
	$entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$row['it_id']}' and mb_id = '{$member['mb_id']}' ");

	$list[$i] = $row;
	$list[$i]['href'] = './item.php?it_id='.$row['it_id'].'&amp;ca_id='.$ca_id.$qstr.'&amp;page='.$page;
	$list[$i]['num'] = $num;
	$list[$i]["optionList"] = $thisOptionList;
	$list[$i]['entprice'] = $entprice['it_price'];
	$num--;
}

// 네비게이션
$nav = array();
$len = strlen($ca_id) / 2;
$n=0;
for ($i=1; $i<=$len; $i++) {
	$code = substr($ca_id,0,$i*2);
	$nav[$n]['ca_id'] = $code;

	$row = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$code' ");
	$nav[$n]['name'] = $row['ca_name'];

	if($ca_id === $code) {
		$nav[$n]['on'] = true;
		$nav[$n]['cnt'] = $total_count;
	} else {
		$nav[$n]['on'] = false;
		$nav[$n]['cnt'] = 0;
	}

	$n++;
}

$is_nav = ($n > 0) ? true : false;
$nav_title = $ca['ca_name'];

// 페이징
$write_pages = G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'];
$list_page = $_SERVER['SCRIPT_NAME'].'?ca_id='.$ca_id.$qstr.'&amp;page=';

// Button
$admin_href = $config_href = $write_href = '';
if(USE_PARTNER) {
	if($is_admin) {
		if(IS_PARTNER) {
			$write_href = './myshop.php?mode=item&amp;fn='.$ca['pt_form'];
			$admin_href = './myshop.php?mode=list&amp;sca='.$ca_id;
		} else {
			$write_href = G5_ADMIN_URL.'/shop_admin/itemform.php?fn='.$ca['pt_form'];
			$admin_href = G5_ADMIN_URL.'/shop_admin/itemlist.php?ca_id='.$ca_id;
		}
		$config_href = G5_ADMIN_URL.'/shop_admin/categoryform.php?w=u&amp;ca_id='.$ca_id;
	} else if (IS_PARTNER && $ca['pt_use']) {
		$write_href = './myshop.php?mode=item&amp;fn='.$ca['pt_form'];
		$admin_href = './myshop.php?mode=list&amp;sca='.$ca_id;
	}
} else {
	if($is_admin == 'super') {
		$write_href = G5_ADMIN_URL.'/shop_admin/itemform.php?fn='.$ca['pt_form'];
		$admin_href = G5_ADMIN_URL.'/shop_admin/itemlist.php?ca_id='.$ca_id;
		$config_href = G5_ADMIN_URL.'/shop_admin/categoryform.php?w=u&amp;ca_id='.$ca_id;
	}
}

$rss_href = ($ca_id) ? G5_URL.'/rss/?cid='.urlencode($ca_id) : '';

$lm = ''; // 리스트 모드
$ls = $list_skin; // 리스트 스킨

// 셋업
$setup_href = '';
if (!$ev_id && is_file($list_skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
    $setup_href = './skin.setup.php?skin=list&amp;name='.urlencode($ls).'&amp;ca_id='.urlencode($ca_id);
}

// 스킨
$list_skin_file = $list_skin_path.'/list.skin.php';

if(file_exists($list_skin_file)) {
	include_once($list_skin_file);
} else {
	echo '<p>'.str_replace(G5_PATH.'/', '', $list_skin_file).' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
}

// 하단 HTML
echo '<div id="sct_thtml">'.conv_content($ca['ca_'.MOBILE_.'tail_html'], 1).'</div>'.PHP_EOL;

if ($ca['ca_include_tail'] && is_include_path_check($ca['ca_include_tail']))
    @include_once($ca['ca_include_tail']);
else
    include_once(G5_SHOP_PATH.'/_tail.php');

echo "\n<!-- {$ca['ca_'.MOBILE_.'skin']} -->\n";

?>
