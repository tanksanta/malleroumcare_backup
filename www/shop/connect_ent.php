<?php
include_once('./_common.php');

if($member['mb_type'] !== 'normal') {
  alert('일반회원만 사용할 수 있습니다.');
}

$ent_mb_id = get_search_string($ent_mb_id);

if(!$ent_mb_id)
  alert('유효하지 않은 요청입니다.');

if(!$redirect)
  $redirect = G5_URL;

$pen_ent = get_pen_ent_by_pen_mb_id($member['mb_id'], $ent_mb_id);

if(!$pen_ent)
  alert('해당 사업소와 연결되어있지 않습니다.');

$entId = $pen_ent['entId'];
$penId = $pen_ent['penId'];

set_session('ss_ent_mb_id', $ent_mb_id);
set_session('ss_ent_id', $entId);
set_session('ss_pen_id', $penId);

goto_url($redirect);
?>
