<?php
include_once('./_common.php');

if (!$is_member)
    goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_SHOP_URL.'/wishlist.php'));

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/wishlist.php');
    return;
}

$list = array();
//$sql  = " select a.wi_id, a.wi_time, b.* from {$g5['g5_shop_wish_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id ) ";
//$sql .= " where a.mb_id = '{$member['mb_id']}' ";
$sql = " SELECT a.* FROM {$g5["g5_shop_item_table"]} a ";
$sql .= " WHERE 1=1 ";

$prodList = [];
$prodPpcRegDtmList = [];
$prodPpcIdList = [];
if($member["mb_entId"]){
	$sendData = [];
	$sendData["entId"] = $member["mb_entId"];
	
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/prod/selectPpcList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$resData = $res["data"];
	for($i = 0; $i < count($resData); $i++){
		if($resData[$i]["prodId"]){
			array_push($prodList, "'{$resData[$i]["prodId"]}'");
			$prodPpcRegDtmList[$resData[$i]["prodId"]] = $resData[$i]["ppcRegDtm"];
			$prodPpcIdList[$resData[$i]["prodId"]] = $resData[$i]["ppcId"];
		}
	}
}

$prodList = implode(",", $prodList);
$sql .= ($prodList) ? " AND a.it_id IN ( {$prodList} ) " : " AND a.it_id = '0' ";
//$sql .= " order by a.wi_id desc ";
$sql .= " ORDER BY FIELD (a.it_id, {$prodList}) ";
$result = sql_query($sql);
for ($i=0; $row = sql_fetch_array($result); $i++) {

	$list[$i] = $row;

	$list[$i]['out_cd'] = '';
	$sql = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' ";
	$tmp = sql_fetch($sql);
	if($tmp['cnt'])
		$list[$i]['out_cd'] = 'no';

	$list[$i]['price'] = get_price($row);

	if ($row['it_tel_inq']) $list[$i]['out_cd'] = 'tel';

	$list[$i]['is_soldout'] = is_soldout($row['it_id']);
}

// Page ID
$pid = ($pid) ? $pid : 'wishlist';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

// 스킨 체크
list($member_skin_path, $member_skin_url) = apms_skin_thema('member', $member_skin_path, $member_skin_url); 

// 설정값 불러오기
$is_wishlist_sub = false;
@include_once($member_skin_path.'/config.skin.php');

$g5['title'] = $member['mb_nick'].'님의 위시리스트';

if($is_wishlist_sub) {
	include_once(G5_PATH.'/head.sub.php');
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
	include_once('./_head.php');
}

$skin_path = $member_skin_path;
$skin_url = $member_skin_url;

// 스킨설정
$wset = (G5_IS_MOBILE) ? apms_skin_set('member_mobile') : apms_skin_set('member');

$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=member&amp;ts='.urlencode(THEMA);
}

include_once($skin_path.'/wishlist.skin.php');

if($is_wishlist_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>