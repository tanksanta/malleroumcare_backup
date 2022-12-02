<?php
$sub_menu = "200100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
if ($w == '')
{
    // $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = $config['cf_register_level'];
    $html_title = '추가';
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    $required_mb_id = 'readonly';
    $required_mb_password = '';
    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_fax'] = get_text($mb['mb_fax']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 본인확인방법
switch($mb['mb_certify']) {
    case 'hp':
        $mb_certify_case = '휴대폰';
        $mb_certify_val = 'hp';
        break;
    case 'ipin':
        $mb_certify_case = '아이핀';
        $mb_certify_val = 'ipin';
        break;
    case 'admin':
        $mb_certify_case = '관리자 수정';
        $mb_certify_val = 'admin';
        break;
    default:
        $mb_certify_case = '';
        $mb_certify_val = 'admin';
        break;
}

// 본인확인
$mb_certify_yes  =  $mb['mb_certify'] ? 'checked="checked"' : '';
$mb_certify_no   = !$mb['mb_certify'] ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  $mb['mb_adult']      ? 'checked="checked"' : '';
$mb_adult_no        = !$mb['mb_adult']      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  $mb['mb_mailling']   ? 'checked="checked"' : '';
$mb_mailling_no     = !$mb['mb_mailling']   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  $mb['mb_sms']        ? 'checked="checked"' : '';
$mb_sms_no          = !$mb['mb_sms']        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';

// 기업 멤버 가입 유형
$mb_giup_type_0        =  $mb['mb_giup_type'] == '0'       ? 'checked="checked"' : '';
$mb_giup_type_1        =  $mb['mb_giup_type'] == '1'       ? 'checked="checked"' : '';
$mb_giup_type_2         = $mb['mb_giup_type'] == '2'       ? 'checked="checked"' : '';

// 파트너 인증 여부
$mb_partner_auth_y        =  $mb['mb_partner_auth']       ? 'checked="checked"' : '';
$mb_partner_auth_n         = !$mb['mb_partner_auth']       ? 'checked="checked"' : '';

// 주문가능 여부
$mb_order_approve_y        =  $mb['mb_order_approve'] == 1       ? 'checked="checked"' : '';
$mb_order_approve_n         = $mb['mb_order_approve'] == 0       ? 'checked="checked"' : '';

// 딜러 여부
$mb_dealer_y        =  $mb['mb_dealer']       ? 'checked="checked"' : '';
$mb_dealer_n         = !$mb['mb_dealer']       ? 'checked="checked"' : '';

// 거래명세서 전송방법
$mb_transaction_e        =  $mb['send_transaction'] == 'A' || $mb['send_transaction'] == 'E'       ? 'checked="checked"' : '';
$mb_transaction_f         = $mb['send_transaction'] == 'A' || $mb['send_transaction'] == 'F'       ? 'checked="checked"' : '';
$send_transaction_e     = $mb['send_transaction_e'] ?: $mb['mb_email'];
$send_transaction_f     = $mb['send_transaction_f'] ?: $mb['mb_fax'];

// 파트너 자동 계약 갱신 여부
$mb_partner_date_auto_y         =  $mb['mb_partner_date_auto']       ? 'checked="checked"' : '';
$mb_partner_date_auto_n         = !$mb['mb_partner_date_auto']       ? 'checked="checked"' : '';

if (isset($mb['mb_certify'])) {
    // 날짜시간형이라면 drop 시킴
    if (preg_match("/-/", $mb['mb_certify'])) {
        sql_query(" ALTER TABLE `{$g5['member_table']}` DROP `mb_certify` ", false);
    }
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_certify` TINYINT(4) NOT NULL DEFAULT '0' AFTER `mb_hp` ", false);
}

if(isset($mb['mb_adult'])) {
    sql_query(" ALTER TABLE `{$g5['member_table']}` CHANGE `mb_adult` `mb_adult` TINYINT(4) NOT NULL DEFAULT '0' ", false);
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_adult` TINYINT NOT NULL DEFAULT '0' AFTER `mb_certify` ", false);
}

// 지번주소 필드추가
if(!isset($mb['mb_addr_jibeon'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr_jibeon` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 건물명필드추가
if(!isset($mb['mb_addr3'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 중복가입 확인필드 추가
if(!isset($mb['mb_dupinfo'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_dupinfo` varchar(255) NOT NULL DEFAULT '' AFTER `mb_adult` ", false);
}

// 이메일인증 체크 필드추가
if(!isset($mb['mb_email_certify2'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_email_certify2` varchar(255) NOT NULL DEFAULT '' AFTER `mb_email_certify` ", false);
}

if ($mb['mb_intercept_date']) $g5['title'] = "차단된 ";
else $g5['title'] .= "";
$g5['title'] .= '회원 '.$html_title;
include_once('./admin.head.php');

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<style>
label {
    margin-right:10px;
}
.accept {
    cursor:pointer;
}
</style>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<form name="fmember" id="fmember" action="./member_form_update.php" onsubmit="return fmember_submit();" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">기본정보</h2>
            </div>
        </th>
    </tr>
      <tr>
        <th scope="row"><label for="mb_manager">영업담당자<?php echo $sound_only ?></label></th>
        <td>
        <select name="mb_manager" id="mb_manager">
            <?php
                //$sql_m="select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '200100'";
                $sql_m = " SELECT mb_name, mb_id FROM g5_member WHERE mb_level = 9 ORDER BY mb_name ASC ";
                $result_m = sql_query($sql_m);
                echo '<option value="">영업 담당자를 선택하세요.</option>';
                for ($k=0; $row_m=sql_fetch_array($result_m); $k++){
                    $selected="";
                    if($mb['mb_manager']==$row_m['mb_id']) $selected="selected";
                    echo '<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
                }
            ?>
        </select>
        </td>
      </tr>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?php echo $sound_only ?></label></th>
        <td>
            <?php
            $cs_result = sql_fetch("SELECT count(mb_no) as cnt FROM g5_member WHERE mb_id LIKE 'CS". substr(date("Ymd"), 2, 6) ."%'");
            $cs_cnt = sprintf('%02d', ($cs_result['cnt'] +1));
            $show_mb_id = $mb['mb_id'] ? $mb['mb_id'] : 'CS' . substr(date("Ymd"), 2, 6) . $cs_cnt;
            ?>
            <input type="text" name="mb_id" value="<?php echo $show_mb_id ?>" id="mb_id" <?php echo $required_mb_id ?> class="frm_input <?php echo $required_mb_id_class ?>" size="15" minlength="3" maxlength="20">
            <?php if ($w=='u'){ ?><a href="./boardgroupmember_form.php?mb_id=<?php echo $mb['mb_id'] ?>">접근가능그룹보기</a>, <?php } ?>
            <br/>
            <input type="checkbox" value="1" id="mb_temp" name="mb_temp" <?php echo $mb['mb_temp'] ? "checked" : ""; ?> >
            <label for="mb_temp">임시계정 (아이디 및 비밀번호를 지정할 수 없습니다.)</label>
        </td>
        <th scope="row"><label for="mb_password">비밀번호<?php echo $sound_only ?></label></th>
        <td><input type="password" name="mb_password" id="mb_password" <?php echo $required_mb_password ?> class="frm_input <?php echo $required_mb_password ?>" size="15" maxlength="20"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_name">이름<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" class="frm_input" size="15" maxlength="20"></td>
        <th scope="row"><label for="mb_nick">닉네임</label></th>
        <td><input type="text" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" class=" frm_input" size="15" maxlength="20"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_level">회원 권한</label></th>
        <td><?php echo get_member_level_select('mb_level', 1, $member['mb_level'], $mb['mb_level']) ?></td>
        <th scope="row">포인트</th>
        <td><a href="./point_list.php?sfl=mb_id&amp;stx=<?php echo $mb['mb_id'] ?>" target="_blank"><?php echo number_format($mb['mb_point']) ?></a> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_level">회원 등급</label></th>
        <td colspan="3">
            <select id="mb_grade" name="mb_grade">
                <?php echo option_selected(0, $mb['mb_grade'], $default['de_it_grade0_name'] . ' (적립:' . $default['de_it_grade0_discount'] . '%)'); ?>
                <?php echo option_selected(1, $mb['mb_grade'], $default['de_it_grade1_name'] . ' (적립:' . $default['de_it_grade1_discount'] . '%)'); ?>
                <?php echo option_selected(2, $mb['mb_grade'], $default['de_it_grade2_name'] . ' (적립:' . $default['de_it_grade2_discount'] . '%)'); ?>
                <?php echo option_selected(3, $mb['mb_grade'], $default['de_it_grade3_name'] . ' (적립:' . $default['de_it_grade3_discount'] . '%)'); ?>
            </select>
        </td>
    </tr>
  <?php if($mb['as_date']) { ?>
    <tr>
      <th scope="row"><label for="mb_level">이용 기간</label></th>
      <td colspan="3">
        <?php echo date("Y년 m월 d일 H시 i분 s초", $mb['as_date']);?>까지
        :
        ± <input type="text" name="as_date_plus" value="" id="as_date_plus" maxlength="20" class="frm_input" size="4"> 일 증감하기
        &nbsp;
        <label><input type="checkbox" value="1" name="as_leave" id="as_leave"> 멤버쉽 해제하기(※주의! 체크시 이용기간이 초기화됨)</label>
      </td>
    </tr>
  <?php } ?>
    <tr>
        <th scope="row"><label for="mb_hp">휴대폰번호</label></th>
        <td>
        <?php $mb_hp =explode('-',$mb['mb_hp']); ?>
        <input type="text" name="mb_hp1" value="<?=$mb_hp[0]?>" id="mb_hp1" class="frm_input"size="15" maxlength="3">
        <input type="text" name="mb_hp2" value="<?=$mb_hp[1]?>" id="mb_hp2" class="frm_input" size="15" maxlength="4">
        <input type="text" name="mb_hp3" value="<?=$mb_hp[2]?>" id="mb_hp3" class="frm_input" size="15" maxlength="4">
        
        </td>
        <th scope="row"><label for="mb_tel">전화번호</label></th>
        <td>
        <select name="mb_tel1" id="mb_tel1" class="form-control input-sm number_box1">
            <?php $mb_giup_btel =explode('-',$mb['mb_giup_btel']); ?>
            <option value="02" <?=($mb_giup_btel[0] =="02")? "selected": "" ; ?> >02</option>
            <option value="010" <?=($mb_giup_btel[0] =="010")? "selected": "" ; ?>>010</option>
            <option value="031" <?=($mb_giup_btel[0] =="031")? "selected": "" ; ?>>031</option>
            <option value="032" <?=($mb_giup_btel[0] =="032")? "selected": "" ; ?>>032</option>
            <option value="033" <?=($mb_giup_btel[0] =="033")? "selected": "" ; ?>>033</option>
            <option value="041" <?=($mb_giup_btel[0] =="041")? "selected": "" ; ?>>041</option>
            <option value="042" <?=($mb_giup_btel[0] =="042")? "selected": "" ; ?>>042</option>
            <option value="043" <?=($mb_giup_btel[0] =="043")? "selected": "" ; ?>>043</option>
            <option value="044" <?=($mb_giup_btel[0] =="044")? "selected": "" ; ?>>044</option>
            <option value="051" <?=($mb_giup_btel[0] =="051")? "selected": "" ; ?>>051</option>
            <option value="052" <?=($mb_giup_btel[0] =="052")? "selected": "" ; ?>>052</option>
            <option value="053" <?=($mb_giup_btel[0] =="053")? "selected": "" ; ?>>053</option>
            <option value="054" <?=($mb_giup_btel[0] =="054")? "selected": "" ; ?>>054</option>
            <option value="055" <?=($mb_giup_btel[0] =="055")? "selected": "" ; ?>>055</option>
            <option value="061" <?=($mb_giup_btel[0] =="061")? "selected": "" ; ?>>061</option>
            <option value="062" <?=($mb_giup_btel[0] =="062")? "selected": "" ; ?>>062</option>
            <option value="063" <?=($mb_giup_btel[0] =="063")? "selected": "" ; ?>>063</option>
            <option value="064" <?=($mb_giup_btel[0] =="064")? "selected": "" ; ?>>064</option>
            <option value="070" <?=($mb_giup_btel[0] =="070")? "selected": "" ; ?>>070</option>
        </select>
        <input type="text" name="mb_tel2" value="<?php echo $mb_giup_btel[1] ?>" id="mb_tel2" class="frm_input" size="15" maxlength="4">
        <input type="text" name="mb_tel3" value="<?php echo $mb_giup_btel[2] ?>" id="mb_tel3" class="frm_input" size="15" maxlength="4">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_fax">팩스번호</label></th>
        <td colspan="3">
        <?php $mb_fax =explode('-',$mb['mb_fax']); ?>
        <input type="text" name="mb_fax1" value="<?php echo $mb_fax[0] ?>" id="mb_fax1" class="frm_input" size="15"  maxlength="4">
        <input type="text" name="mb_fax2" value="<?php echo $mb_fax[1] ?>" id="mb_fax2" class="frm_input" size="15"  maxlength="4">
        <input type="text" name="mb_fax3" value="<?php echo $mb_fax[2] ?>" id="mb_fax3" class="frm_input" size="15"  maxlength="4">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="reg_mb_email">이메일<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" id="reg_mb_email" maxlength="100" required class="required frm_input email" size="30"></td>
        <!-- <th scope="row"><label for="mb_homepage">홈페이지</label></th>
        <td><input type="text" name="mb_homepage" value="<?php echo $mb['mb_homepage'] ?>" id="mb_homepage" class="frm_input" maxlength="255" size="15"></td> -->
    </tr>
    <tr>
        <th scope="row">회원유형</th>
        <td colspan="3">
            <div class="flex-row">
                <select class="frm_input" name="mb_type" onchange="togglePartnerType(this)">
                    <option value="default" <?php echo $mb['mb_type'] == 'default' ? 'selected' : ''; ?>>일반사업소</option>
                    <option value="normal" <?php echo $mb['mb_type'] == 'normal' ? 'selected' : ''; ?>>일반회원</option>
                    <option value="partner" <?php echo $mb['mb_type'] == 'partner' ? 'selected' : ''; ?>>파트너(직배송, 설치, 소독)</option>
                    <option value="center" <?php echo $mb['mb_type'] == 'center' ? 'selected' : ''; ?>>방문급여센터</option>
                </select>
                <div class="partner-type-wrapper" style="margin-left: 10px; <?php echo $mb['mb_type'] != 'partner' ? 'display: none;' : '' ?>">
                  <label><input type="checkbox" name="mb_partner_type[]" value="직배송" <?php echo strpos($mb['mb_partner_type'], '직배송') !== false ? 'checked' : '' ?>>직배송</label>
                  <label><input type="checkbox" name="mb_partner_type[]" value="설치" <?php echo strpos($mb['mb_partner_type'], '설치') !== false ? 'checked' : '' ?>>설치</label>
                  <label><input type="checkbox" name="mb_partner_type[]" value="물품공급" <?php echo strpos($mb['mb_partner_type'], '물품공급') !== false ? 'checked' : '' ?>>물품공급</label>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">주문</th>
        <td colspan="3">
            <input type="radio" name="mb_order_approve" value="0" id="mb_order_approve_n" <?php echo $mb_order_approve_n; ?>>
            <label for="mb_order_approve_n">주문정지</label>
            <input type="radio" name="mb_order_approve" value="1" id="mb_order_approve_y" <?php echo $mb_order_approve_y; ?>>
            <label for="mb_order_approve_y">주문가능</label>
        </td>
    </tr>
    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">사업자 정보</h2>
            </div>
        </th>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_giup_bname">기업명</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_bname" value="<?php echo $mb['mb_giup_bname'] ?>" id="mb_giup_bname" class="frm_input" size="30" maxlength="20">
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_giup_boss_name">대표자명</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_boss_name" value="<?php echo $mb['mb_giup_boss_name'] ?>" id="mb_giup_boss_name" class="frm_input" size="30" maxlength="20">
        </td>
    </tr>
    <!-- <tr>
        <th scope="row">
            <label for="mb_giup_btel">연락처</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_btel" value="<?php echo $mb['mb_giup_btel'] ?>" id="mb_giup_btel" class="frm_input" size="30" maxlength="20">
        </td>
    </tr> -->
    <tr>
        <th scope="row">
            <label for="mb_giup_bnum">사업자번호</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_bnum" value="<?php echo $mb['mb_giup_bnum'] ?>" id="mb_giup_bnum" class="frm_input" size="30" maxlength="20">
            <!--<label><button type="button" id="mb_giup_bnum_check" class="btn btn-black btn-sm" onclick="check_giup_bnum();">중복확인</button></label>-->
            <!-- *관리자 권한으로 사업자번호가 중복되어도 입력이 가능합니다. -->
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_ent_num">장기요양기관번호</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_ent_num" value="<?php echo $mb['mb_ent_num'] ?>" id="mb_ent_num" class="frm_input" size="30" maxlength="20">
        </td>
    </tr>
    <!-- <tr>
        <th scope="row">
            <label for="mb_giup_sbnum">종사업자번호</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_sbnum" value="<?php echo $mb['mb_giup_sbnum'] ?>" id="mb_giup_sbnum" class="frm_input" size="30" maxlength="20">
            <label><button type="button" id="mb_giup_sbnum_check" class="btn btn-black btn-sm" onclick="check_giup_sbnum();">중복확인</button></label>
            *종사업자 정보는 필수 입력 사항이 아닙니다. 관리자 권한으로 종사업자번호가 중복되어도 입력이 가능합니다.
        </td>
    </tr> -->
    <!-- <tr>
        <th scope="row">
            <label for="mb_giup_sbnum_explain">종사업자 관련내용</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_sbnum_explain" value="<?php echo $mb['mb_giup_sbnum_explain'] ?>" id="mb_giup_sbnum_explain" class="frm_input" size="30" maxlength="20">
        </td>
    </tr> -->
    <tr>
        <th scope="row">
            <label for="mb_giup_buptae">업태</label>
        </th>
        <td>
            <input type="text" name="mb_giup_buptae" value="<?php echo $mb['mb_giup_buptae'] ?>" id="mb_giup_buptae" class="frm_input" size="30" maxlength="20">
        </td>
        <th scope="row">
            <label for="mb_giup_bupjong">업종</label>
        </th>
        <td>
            <input type="text" name="mb_giup_bupjong" value="<?php echo $mb['mb_giup_bupjong'] ?>" id="mb_giup_bupjong" class="frm_input" size="30" maxlength="20">
        </td>
    </tr>
        
    <tr>
        <th scope="row">
            <label for="mb_giup_manager_name">담당자명</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_manager_name" value="<?php echo $mb['mb_giup_manager_name'] ?>" id="mb_giup_manager_name" class="frm_input" size="30" maxlength="20">
        </td>
    </tr>
    <!-- <tr>
        <th scope="row">
            <label for="mb_giup_manager_tel">담당자연락처</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_manager_tel" value="<?php echo $mb['mb_giup_manager_tel'] ?>" id="mb_giup_manager_tel" class="frm_input" size="30" maxlength="20">
        </td>
    </tr> -->
    <tr>
        <th scope="row">주소</th>
        <td colspan="3" class="td_addr_line">
            <label for="mb_giup_zip" class="sound_only">우편번호</label>
            <input type="text" name="mb_giup_zip" value="<?php echo $mb['mb_giup_zip1'].$mb['mb_giup_zip2']; ?>" id="mb_giup_zip" class="frm_input readonly" size="5" maxlength="6">
            <button type="button" class="btn_frmline" onclick="win_zip('fmember', 'mb_giup_zip', 'mb_giup_addr1', 'mb_giup_addr2', 'mb_giup_addr3', 'mb_giup_addr_jibeon');">주소 검색</button><br>
            <input type="text" name="mb_giup_addr1" value="<?php echo $mb['mb_giup_addr1'] ?>" id="mb_giup_addr1" class="frm_input readonly" size="60">
            <label for="mb_giup_addr1">기본주소</label><br>
            <input type="text" name="mb_giup_addr2" value="<?php echo $mb['mb_giup_addr2'] ?>" id="mb_giup_addr2" class="frm_input" size="60">
            <label for="mb_giup_addr2">상세주소</label>
            <br>
            <input type="text" name="mb_giup_addr3" value="<?php echo $mb['mb_giup_addr3'] ?>" id="mb_giup_addr3" class="frm_input" size="60">
            <label for="mb_giup_addr3">참고항목</label>
            <input type="hidden" name="mb_giup_addr_jibeon" value="<?php echo $mb['mb_giup_addr_jibeon']; ?>"><br>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_giup_file1">사업자등록증</label>
        </th>
        <td colspan="3" class="mb_giup_file1">
            <input type="file" name="crnFile" accept=".gif, .jpg, .png, .pdf" class="input-sm " id="mb_giup_file1">
            <?php if($mb['crnFile']){ ?>
              <a href="<?=G5_BBS_URL?>/view_image.php?fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/file/member/license/{$mb['crnFile']}"))?>" target="_blank" class="view_image">
                <img style="width:100px; height:100px;" src="<?=G5_DATA_URL?>/file/member/license/<?=$mb['crnFile']?>" alt="" onerror="this.src='/shop/img/no_image.gif';">
              </a>
              <a href="<?=G5_DATA_URL."/file/member/license/{$mb['crnFile']}"?>" target="_blank" class="btn btn_submit">파일확인</a>
            <?php }?>
        </td>
    </tr>
    <?php if($w){ ?>
    <tr>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_cnt">사업자직인 (계약서 날인)</label>
        </th>
        <td colspan="3" class="mb_giup_file2">
            <input type="file" name="sealFile" accept=".gif, .jpg, .png, .pdf" class="input-sm " id="mb_giup_file2">
            <?php if($mb['sealFile']){ ?>
              <a href="<?=G5_BBS_URL?>/view_image.php?fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/file/member/stamp/{$mb['sealFile']}"))?>" target="_blank" class="view_image">
                <img style="max-width:100px; max-height:100px;" src="<?=G5_DATA_URL?>/file/member/stamp/<?=$mb['sealFile']?>" alt="">
              </a>
            <?php }?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row">
            <label for="mb_giup_tax_email">이메일(세금계산서 수신용)</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_giup_tax_email" value="<?php echo $mb['mb_giup_tax_email'] ?>" id="mb_giup_tax_email" class="frm_input" size="30" maxlength="30">
        </td>
    </tr>
    <tr>
        <?php
        if ($w != 'u') {
            // $mb_thezone_placeholder = "등록 시 자동으로 발급됩니다.";
        }
        ?>
        <th scope="row">고객(거래처)코드</th>
        <td colspan="3">
            <input type="text" name="mb_thezone" value="<?php echo $mb['mb_thezone'] ?>" id="mb_thezone" class="frm_input" size="30" maxlength="50" placeholder="<?php echo $mb_thezone_placeholder ?>">
        </td>
    </tr>
    
    <tr>
        <th scope="row">딜러 여부</th>
        <td colspan="3">
            <input type="radio" name="mb_dealer" value="0" id="mb_dealer_n" <?php echo $mb_dealer_n; ?>>
            <label for="mb_dealer_n">딜러아님</label>
            <input type="radio" name="mb_dealer" value="1" id="mb_dealer_y" <?php echo $mb_dealer_y; ?>>
            <label for="mb_dealer_y">딜러회원</label>
        </td>
    </tr>
    <tr>
        <th scope="row">거래내역 전송방법</th>
        <td colspan="3">
            <input type="checkbox" name="mb_transaction_e" value="mb_transaction_e" id="mb_transaction_e" <?php echo $mb_transaction_e; ?>>
            이메일 <input type="text" name="send_transaction_e" value="<?php echo $send_transaction_e ?>" id="send_transaction_e" class="frm_input" size="30" maxlength="50">
            <input type="checkbox" name="mb_transaction_f" value="mb_transaction_f" id="mb_transaction_f" <?php echo $mb_transaction_f; ?>>
            팩스 <input type="text" name="send_transaction_f" value="<?php echo $send_transaction_f ?>" id="send_transaction_f" class="frm_input" size="30" maxlength="50">
        </td>
    </tr>
     <tr>
        <th scope="row">본인확인방법</th>
        <td colspan="3">
            <input type="radio" name="mb_certify_case" value="ipin" id="mb_certify_ipin" <?php if($mb['mb_certify'] == 'ipin') echo 'checked="checked"'; ?>>
            <label for="mb_certify_ipin">아이핀</label>
            <input type="radio" name="mb_certify_case" value="hp" id="mb_certify_hp" <?php if($mb['mb_certify'] == 'hp') echo 'checked="checked"'; ?>>
            <label for="mb_certify_hp">휴대폰</label>
        </td>
    </tr>
     <tr>
        <th scope="row">본인확인</th>
        <td>
            <input type="radio" name="mb_certify" value="1" id="mb_certify_yes" <?php echo $mb_certify_yes; ?>>
            <label for="mb_certify_yes">예</label>
            <input type="radio" name="mb_certify" value="" id="mb_certify_no" <?php echo $mb_certify_no; ?>>
            <label for="mb_certify_no">아니오</label>
        </td>
        <th scope="row">성인인증</th>
        <td>
            <input type="radio" name="mb_adult" value="1" id="mb_adult_yes" <?php echo $mb_adult_yes; ?>>
            <label for="mb_adult_yes">예</label>
            <input type="radio" name="mb_adult" value="0" id="mb_adult_no" <?php echo $mb_adult_no; ?>>
            <label for="mb_adult_no">아니오</label>
        </td>
    </tr>
    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">배송지 주소</h2>
            </div>
        </th>
    </tr>
    <tr>
        <th scope="row">배송지명</th>
        <td colspan="3">
            <label for="mb_addr_name" class="sound_only">배송지명</label>
            <input type="text" name="mb_addr_name" value="<?php echo $mb['mb_addr_name'] ?: $mb['mb_name']; ?>" id="mb_addr_name" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row">연락처</th>
        <td colspan="3">
            <label for="mb_addr_tel" class="sound_only">연락처</label>
            <input type="text" name="mb_addr_tel" value="<?php echo $mb['mb_addr_tel'] ?: $mb['mb_tel']; ?>" id="mb_addr_tel" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row">주소</th>
        <td colspan="3" class="td_addr_line">
            <label for="mb_zip" class="sound_only">우편번호</label>
            <input type="text" name="mb_zip" value="<?php echo $mb['mb_zip1'].$mb['mb_zip2']; ?>" id="mb_zip" class="frm_input readonly" size="5" maxlength="6">
            <button type="button" class="btn_frmline" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button><br>
            <input type="text" name="mb_addr1" value="<?php echo $mb['mb_addr1'] ?>" id="mb_addr1" class="frm_input readonly" size="60">
            <label for="mb_addr1">기본주소</label><br>
            <input type="text" name="mb_addr2" value="<?php echo $mb['mb_addr2'] ?>" id="mb_addr2" class="frm_input" size="60">
            <label for="mb_addr2">상세주소</label>
            <br>
            <input type="text" name="mb_addr3" value="<?php echo $mb['mb_addr3'] ?>" id="mb_addr3" class="frm_input" size="60">
            <label for="mb_addr3">참고항목</label>
            <input type="hidden" name="mb_addr_jibeon" value="<?php echo $mb['mb_addr_jibeon']; ?>"><br>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_icon">회원아이콘</label></th>
        <td colspan="3">
            <?php echo help('이미지 크기는 <strong>넓이 '.$config['cf_member_icon_width'].'픽셀 높이 '.$config['cf_member_icon_height'].'픽셀</strong>로 해주세요.') ?>
            <input type="file" name="mb_icon" id="mb_icon">
            <?php
            $mb_dir = substr($mb['mb_id'],0,2);
            $icon_file = G5_DATA_PATH.'/member/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
            if (file_exists($icon_file)) {
                $icon_url = G5_DATA_URL.'/member/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
                echo '<img src="'.$icon_url.'" alt="">';
                echo '<input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1">삭제';
            }
            ?>
        </td>
    </tr>
<tr>
        <th scope="row"><label for="mb_img">회원이미지</label></th>
        <td colspan="3">
            <?php echo help('이미지 크기는 <strong>넓이 '.$config['cf_member_img_width'].'픽셀 높이 '.$config['cf_member_img_height'].'픽셀</strong>로 해주세요.') ?>
            <input type="file" name="mb_img" id="mb_img">
            <?php
            $mb_dir = substr($mb['mb_id'],0,2);
            $icon_file = G5_DATA_PATH.'/member_image/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
            if (file_exists($icon_file)) {
                $icon_url = G5_DATA_URL.'/member_image/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
                echo '<img src="'.$icon_url.'" alt="">';
                echo '<input type="checkbox" id="del_mb_img" name="del_mb_img" value="1">삭제';
            }
            ?>
        </td>
    </tr>
<tr>
        <th scope="row">메일 수신</th>
        <td>
            <input type="radio" name="mb_mailling" value="1" id="mb_mailling_yes" <?php echo $mb_mailling_yes; ?>>
            <label for="mb_mailling_yes">예</label>
            <input type="radio" name="mb_mailling" value="0" id="mb_mailling_no" <?php echo $mb_mailling_no; ?>>
            <label for="mb_mailling_no">아니오</label>
        </td>
        <th scope="row"><label for="mb_sms_yes">SMS 수신</label></th>
        <td>
            <input type="radio" name="mb_sms" value="1" id="mb_sms_yes" <?php echo $mb_sms_yes; ?>>
            <label for="mb_sms_yes">예</label>
            <input type="radio" name="mb_sms" value="0" id="mb_sms_no" <?php echo $mb_sms_no; ?>>
            <label for="mb_sms_no">아니오</label>
        </td>
    </tr>
<tr>
        <th scope="row">정보 공개</th>
        <td colspan="3">
            <input type="radio" name="mb_open" value="1" id="mb_open_yes" <?php echo $mb_open_yes; ?>>
            <label for="mb_open_yes">예</label>
            <input type="radio" name="mb_open" value="0" id="mb_open_no" <?php echo $mb_open_no; ?>>
            <label for="mb_open_no">아니오</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_signature">서명</label></th>
        <td colspan="3"><textarea  name="mb_signature" id="mb_signature"><?php echo $mb['mb_signature'] ?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_profile">자기 소개</label></th>
        <td colspan="3"><textarea name="mb_profile" id="mb_profile"><?php echo $mb['mb_profile'] ?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_memo">메모</label></th>
        <td colspan="3"><textarea name="mb_memo" id="mb_memo"><?php echo $mb['mb_memo'] ?></textarea></td>
    </tr>

    <?php if ($w == 'u') { ?>
    <tr>
        <th scope="row">회원가입일</th>
        <td><?php echo $mb['mb_datetime'] ?></td>
        <th scope="row">최근접속일</th>
        <td><?php echo $mb['mb_today_login'] ?></td>
    </tr>
    <tr>
        <th scope="row">IP</th>
        <td colspan="3"><?php echo $mb['mb_ip'] ?></td>
    </tr>
    <?php if ($config['cf_use_email_certify']) { ?>
    <tr>
        <th scope="row">인증일시</th>
        <td colspan="3">
            <?php if ($mb['mb_email_certify'] == '0000-00-00 00:00:00') { ?>
            <?php echo help('회원님이 메일을 수신할 수 없는 경우 등에 직접 인증처리를 하실 수 있습니다.') ?>
            <input type="checkbox" name="passive_certify" id="passive_certify">
            <label for="passive_certify">수동인증</label>
            <?php } else { ?>
            <?php echo $mb['mb_email_certify'] ?>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
    <?php } ?>

    <?php if ($config['cf_use_recommend']) { // 추천인 사용 ?>
    <tr>
        <th scope="row">추천인</th>
        <td colspan="3"><?php echo ($mb['mb_recommend'] ? get_text($mb['mb_recommend']) : '없음'); // 081022 : CSRF 보안 결함으로 인한 코드 수정 ?></td>
    </tr>
    <?php } ?>

    <tr>
        <th scope="row"><label for="mb_leave_date">탈퇴일자</label></th>
        <td>
            <input type="text" name="mb_leave_date" value="<?php echo $mb['mb_leave_date'] ?>" id="mb_leave_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_leave_date_set_today" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) {
this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }">
            <label for="mb_leave_date_set_today">탈퇴일을 오늘로 지정</label>
        </td>
        <th scope="row">접근차단일자</th>
        <td>
            <input type="text" name="mb_intercept_date" value="<?php echo $mb['mb_intercept_date'] ?>" id="mb_intercept_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_intercept_date_set_today" onclick="if
(this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else {
this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }">
            <label for="mb_intercept_date_set_today">접근차단일을 오늘로 지정</label>
        </td>
    </tr>


    <?php
    //소셜계정이 있다면
    if(function_exists('social_login_link_account') && $mb['mb_id'] ){
        if( $my_social_accounts = social_login_link_account($mb['mb_id'], false, 'get_data') ){ ?>

    <tr>
    <th>소셜계정목록</th>
    <td colspan="3">
        <ul class="social_link_box">
            <li class="social_login_container">
                <h4>연결된 소셜 계정 목록</h4>
                <?php foreach($my_social_accounts as $account){     //반복문
                    if( empty($account) ) continue;

                    $provider = strtolower($account['provider']);
                    $provider_name = social_get_provider_service_name($provider);
                ?>
                <div class="account_provider" data-mpno="social_<?php echo $account['mp_no'];?>" >
                    <div class="sns-wrap-32 sns-wrap-over">
                        <span class="sns-icon sns-<?php echo $provider; ?>" title="<?php echo $provider_name; ?>">
                            <span class="ico"></span>
                            <span class="txt"><?php echo $provider_name; ?></span>
                        </span>

                        <span class="provider_name"><?php echo $provider_name;   //서비스이름?> ( <?php echo $account['displayname']; ?> )</span>
                        <span class="account_hidden" style="display:none"><?php echo $account['mb_id']; ?></span>
                    </div>
                    <div class="btn_info"><a href="<?php echo G5_SOCIAL_LOGIN_URL.'/unlink.php?mp_no='.$account['mp_no'] ?>" class="social_unlink" data-provider="<?php echo $account['mp_no'];?>" >연동해제</a> <span class="sound_only"><?php echo substr($account['mp_register_day'], 2, 14); ?></span></div>
                </div>
                <?php } //end foreach ?>
            </li>
        </ul>
        <script>
        jQuery(function($){
            $(".account_provider").on("click", ".social_unlink", function(e){
                e.preventDefault();

                if (!confirm('정말 이 계정 연결을 삭제하시겠습니까?')) {
                    return false;
                }

                var ajax_url = "<?php echo G5_SOCIAL_LOGIN_URL.'/unlink.php' ?>";
                var mb_id = '',
                    mp_no = $(this).attr("data-provider"),
                    $mp_el = $(this).parents(".account_provider");

                    mb_id = $mp_el.find(".account_hidden").text();

                if( ! mp_no ){
                    alert('잘못된 요청! mp_no 값이 없습니다.');
                    return;
                }

                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {
                        'mp_no': mp_no,
                        'mb_id': mb_id
                    },
                    dataType: 'json',
                    async: false,
                    success: function(data, textStatus) {
                        if (data.error) {
                            alert(data.error);
                            return false;
                        } else {
                            alert("연결이 해제 되었습니다.");
                            $mp_el.fadeOut("normal", function() {
                                $(this).remove();
                            });
                        }
                    }
                });

                return;
            });
        });
        </script>

    </td>
    </tr>

    <?php
        }   //end if
    }   //end if
    ?>
    <tr>
        <th scope="row">기업멤버유형</th>
        <td colspan="3">
            <input type="radio" name="mb_giup_type" value="0" id="mb_giup_type_0" <?php echo $mb_giup_type_0; ?>>
            <label for="mb_giup_type_0">기업아님</label>
            <input type="radio" name="mb_giup_type" value="1" id="mb_giup_type_1" <?php echo $mb_giup_type_1; ?>>
            <label for="mb_giup_type_1">구매목적</label>
            <input type="radio" name="mb_giup_type" value="2" id="mb_giup_type_2" <?php echo $mb_giup_type_2; ?>>
            <label for="mb_giup_type_2">납품/판매목적</label>
        </td>
    </tr>
<tr>
        <th scope="row">
            <label for="mb_giup_manager_name">담당자</label>
        </th>
        <td colspan="3">
            <style>
            .mm_form {
              padding: 10px 0;
            }
            .mm_form input {
              margin-right: 5px;
            }
            .manager_list {
                padding:0;
                width:100%;
            }
            .manager_list th {
                text-align:center;
            }
            #manager_list_body input[type="text"],
            #manager_list_body input[type="password"] { width: 100%; }
            </style>
            <div class="tbl_head02 tbl_wrap manager_list">
              <div class="mm_form">
                <input type="text" id="mm_id" class="frm_input" size="20" maxlength="20" placeholder="아이디">
                <input type="password" id="mm_pw" class="frm_input" size="20" maxlength="20" placeholder="비밀번호">
                <input type="text" id="mm_name" class="frm_input" size="20" maxlength="20" placeholder="이름">
				<input type="text" id="mm_tel" class="frm_input" size="20" maxlength="20" placeholder="연락처">
                <input type="text" id="mm_email" class="frm_input" size="30" maxlength="50" placeholder="이메일주소">
                <input type="text" id="mm_memo" class="frm_input" size="30" maxlength="50" placeholder="메모">
                <button type="button" id="add_manager" class="btn_submit btn">담당자 추가</button>
              </div>
              <table>
                <caption>담당자 목록</caption>
                <thead>
                  <tr>
                    <th scope="col">아이디</th>
                    <th scope="col">비밀번호</th>
                    <th scope="col">이름</th>
					<th scope="col">연락처</th>
                    <th scope="col">이메일주소</th>
                    <th scope="col">메모</th>
                    <th scope="col" style="width: 100px;">정보수정</th>
                    <th scope="col" style="width: 100px;">담당자삭제</th>
                  </tr>
                </thead>
                <tbody id="manager_list_body">
                  <?php
                  $mm_sql = "
                    SELECT * FROM
                      {$g5["member_table"]}
                    WHERE
                      mb_type = 'manager' and
                      mb_manager = '{$mb['mb_id']}'
                  ";
                  $mm_result = sql_query($mm_sql);

                  while($mm = sql_fetch_array($mm_result)) {
                  ?>
                  <tr>
                    <td>
                      <input type="hidden" class="mm_id" value="<?=$mm['mb_id']?>">
                      <?=$mm['mb_id']?>
                    </td>
                    <td><input type="password" class="frm_input mm_pw" placeholder="비밀번호"></td>
                    <td><input type="text" class="frm_input mm_name" placeholder="이름" value="<?=$mm['mb_name']?>"></td>
					<td><input type="text" class="frm_input mm_tel" placeholder="연락처" value="<?=$mm['mb_tel']?>"></td>
                    <td><input type="text" class="frm_input mm_email" placeholder="이메일" value="<?=$mm['mb_email']?>"></td>
                    <td><input type="text" class="frm_input mm_memo" placeholder="메모" value="<?=$mm['mb_memo']?>"></td>
                    <td class="td_center"><button type="button" class="btn_submit btn btn_mm_edit" data-id="<?=$mm['mb_id']?>">수정하기</button></td>
                    <td class="td_center"><button type="button" class="btn_submit btn btn_mm_delete" data-id="<?=$mm['mb_id']?>">삭제</button></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
          </div>
        </td>
    </tr> 

    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">파트너몰 정보</h2>
            </div>
        </th>
    </tr>
    <tr>
        <th scope="row">파트너 승인 여부</th>
        <td colspan="3">
            <input type="radio" name="mb_partner_auth" value="0" id="mb_partner_auth_n" <?php echo $mb_partner_auth_n; ?>>
            <label for="mb_partner_auth_n">미승인</label>
            <input type="radio" name="mb_partner_auth" value="1" id="mb_partner_auth_y" <?php echo $mb_partner_auth_y; ?>>
            <label for="mb_partner_auth_y">승인</label>
        </td>
    </tr>
<!--    <tr>
        <th scope="row">파트너결제 결제기간</th>
        <td colspan="3">
            <select name="mb_partner_date_pay_date">
                <option value="0">선택해주세요</option>
                <option value="1" <?php /*echo $mb['mb_partner_date_pay_date'] == '1' ? 'selected' : ''; */?>>1개월</option>
                <option value="2" <?php /*echo $mb['mb_partner_date_pay_date'] == '2' ? 'selected' : ''; */?>>2개월</option>
                <option value="3" <?php /*echo $mb['mb_partner_date_pay_date'] == '3' ? 'selected' : ''; */?>>3개월</option>
                <option value="4" <?php /*echo $mb['mb_partner_date_pay_date'] == '4' ? 'selected' : ''; */?>>4개월</option>
                <option value="5" <?php /*echo $mb['mb_partner_date_pay_date'] == '5' ? 'selected' : ''; */?>>5개월</option>
                <option value="6" <?php /*echo $mb['mb_partner_date_pay_date'] == '6' ? 'selected' : ''; */?>>6개월</option>
            </select>
        </td>
    </tr>-->
    <tr>
        <th scope="row">
            <label for="mb_partner_date">계약 종료일</label>
        </th>
        <td colspan="3">
            <input type="text" name="mb_partner_date" value="<?php echo $mb['mb_partner_date'] ?>" id="mb_partner_date" class="frm_input datepicker" size="30" maxlength="20">
        </td>
    </tr>
<!--    <tr>
        <th scope="row">파트너 자동계약갱신 여부</th>
        <td colspan="3">
            <input type="radio" name="mb_partner_date_auto" value="0" id="mb_partner_date_auto_n" <?php /*echo $mb_partner_date_auto_n; */?>>
            <label for="mb_partner_date_auto_n">미사용</label>
            <input type="radio" name="mb_partner_date_auto" value="1" id="mb_partner_date_auto_y" <?php /*echo $mb_partner_date_auto_y; */?>>
            <label for="mb_partner_date_auto_y">사용</label>

            <select name="mb_partner_date_auto_extend_date">
                <option value="0">선택해주세요</option>
                <option value="3" <?php /*echo $mb['mb_partner_date_auto_extend_date'] == '3' ? 'selected' : ''; */?>>3개월</option>
                <option value="6" <?php /*echo $mb['mb_partner_date_auto_extend_date'] == '6' ? 'selected' : ''; */?>>6개월</option>
                <option value="12" <?php /*echo $mb['mb_partner_date_auto_extend_date'] == '12' ? 'selected' : ''; */?>>1년</option>
                <option value="24" <?php /*echo $mb['mb_partner_date_auto_extend_date'] == '24' ? 'selected' : ''; */?>>2년</option>
                <option value="36" <?php /*echo $mb['mb_partner_date_auto_extend_date'] == '36' ? 'selected' : ''; */?>>3년</option>
            </select>
        </td>
    </tr>-->
<!--    <tr>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_price">자동계약갱신 구매금액</label>
        </th>
        <td>
            <input type="text" name="mb_partner_date_auto_buy_price" value="<?php /*echo $mb['mb_partner_date_auto_buy_price'] */?>" id="mb_partner_date_auto_buy_price" class="frm_input" size="20" maxlength="20">원
        </td>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_cnt">자동계약갱신 구매횟수</label>
        </th>
        <td>
            <input type="text" name="mb_partner_date_auto_buy_cnt" value="<?php /*echo $mb['mb_partner_date_auto_buy_cnt'] */?>" id="mb_partner_date_auto_buy_cnt" class="frm_input" size="20" maxlength="20">번
        </td>
    </tr>-->
<!--    <tr>
        <th scope="row">
            <label for="mb_partner_pay_type">결제방법</label>
        </th>
        <td colspan="3">
            <input type="radio" name="mb_partner_pay_type" value="0" id="mb_partner_pay_type_0" <?php /*echo $mb['mb_partner_pay_type'] == 0 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_0">수시</label>
            <input type="radio" name="mb_partner_pay_type" value="1" id="mb_partner_pay_type_1" <?php /*echo $mb['mb_partner_pay_type'] == 1 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_1">일주일</label>
            <input type="radio" name="mb_partner_pay_type" value="2" id="mb_partner_pay_type_2" <?php /*echo $mb['mb_partner_pay_type'] == 2 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_2">월말</label>
            <input type="radio" name="mb_partner_pay_type" value="3" id="mb_partner_pay_type_3" <?php /*echo $mb['mb_partner_pay_type'] == 3 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_3">익월10일</label>
            <input type="radio" name="mb_partner_pay_type" value="4" id="mb_partner_pay_type_4" <?php /*echo $mb['mb_partner_pay_type'] == 4 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_4">익월말</label>
            <input type="radio" name="mb_partner_pay_type" value="5" id="mb_partner_pay_type_5" <?php /*echo $mb['mb_partner_pay_type'] == 5 ? ' checked ' : ''; */?>>
            <label for="mb_partner_pay_type_5">익월말</label>
        </td>
    </tr>-->
    <tr>
        <th scope="row"><label for="mb_partner_remark">비고</label></th>
        <td colspan="3"><textarea  name="mb_partner_remark" id="mb_partner_remark"><?php echo get_text($mb['mb_partner_remark']); ?></textarea></td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_cnt">계약서 파일1</label>
        </th>
        <td colspan="3">
            <input type="file" name="mb_partner_file1">
            <?php
            $bimg_str = "";
            $bimg = G5_DATA_PATH."/member_partner/{$mb['mb_partner_file1']}";
            if (file_exists($bimg) && $mb['mb_partner_file1']) {

                echo '<input type="checkbox" name="mb_partner_file1_del" value="1" id="mb_partner_file1_del"> <label for="mb_partner_file1_del">삭제</label>';
                $bimg_str = '<a target="_blank" href="'.G5_DATA_URL.'/member_partner/'.$mb['mb_partner_file1'].'">'.$mb['mb_partner_file1'].'</a>';
            }
            if ($bimg_str) {
                echo '<div class="banner_or_img">';
                echo $bimg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_cnt">계약서 파일2</label>
        </th>
        <td colspan="3">
            <input type="file" name="mb_partner_file2">
            <?php
            $bimg_str = "";
            $bimg = G5_DATA_PATH."/member_partner/{$mb['mb_partner_file2']}";
            if (file_exists($bimg) && $mb['mb_partner_file2']) {

                echo '<input type="checkbox" name="mb_partner_file2_del" value="1" id="mb_partner_file2_del"> <label for="mb_partner_file2_del">삭제</label>';
                $bimg_str = '<a target="_blank" href="'.G5_DATA_URL.'/member_partner/'.$mb['mb_partner_file2'].'">'.$mb['mb_partner_file2'].'</a>';
            }
            if ($bimg_str) {
                echo '<div class="banner_or_img">';
                echo $bimg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="mb_partner_date_auto_buy_cnt">계약서 파일3</label>
        </th>
        <td colspan="3">
            <input type="file" name="mb_partner_file3">
            <?php
            $bimg_str = "";
            $bimg = G5_DATA_PATH."/member_partner/{$mb['mb_partner_file3']}";
            if (file_exists($bimg) && $mb['mb_partner_file3']) {

                echo '<input type="checkbox" name="mb_partner_file3_del" value="1" id="mb_partner_file3_del"> <label for="mb_partner_file3_del">삭제</label>';
                $bimg_str = '<a target="_blank" href="'.G5_DATA_URL.'/member_partner/'.$mb['mb_partner_file3'].'">'.$mb['mb_partner_file3'].'</a>';
            }
            if ($bimg_str) {
                echo '<div class="banner_or_img">';
                echo $bimg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">여분 필드</h2>
            </div>
        </th>
    </tr>
    <?php for ($i=1; $i<=10; $i++) { ?>
    <tr>
        <th scope="row"><label for="mb_<?php echo $i ?>">여분 필드 <?php echo $i ?></label></th>
        <td colspan="3"><input type="text" name="mb_<?php echo $i ?>" value="<?php echo $mb['mb_'.$i] ?>" id="mb_<?php echo $i ?>" class="frm_input" size="30" maxlength="255"></td>
    </tr>
    <?php } ?>
    <tr>
        <th colspan="4">
            <div style="padding: 20px 20px;background-color: #f1f1f1;">
                <h2 style="margin:0;padding:0;">기업 정보</h2>
            </div>
        </th>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./member_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <!-- <input type="submit" value="확인" class="btn_submit btn" accesskey='s'> -->
    <input type="button" onclick="fmember_submit()" id="btn_submit" value="확인" class="btn_submit btn" accesskey='s'>
    <?php
    $res = api_post_call(EROUMCARE_API_ENT_ACCOUNT, array(
      'usrId' => $mb['mb_id']
    ));

    if($res['data']['entConfirmCd'] == "02") {
      echo '<input type="button" value="승인" class="btn btn_02 accept" id="accept">';
    }
    ?>
</div>
</form>

<script>
$("#accept").click(function() {
  $.post('member_accept.php', {
    usrId: '<?=$mb['mb_id']?>',
    entId: '<?=$mb['mb_entId']?>'
  }, 'json')
  .done(function() {
    alert('승인되었습니다.');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
});

function fmember_submit()
{   
     var f = document.getElementById("fmember");
  // 회원아이디 검사
  if (f.w.value == "") {
    var msg = reg_mb_id_check();
    if (msg) {
      alert(msg);
      f.mb_id.select();
      return false;
    }
  }
    if(!f.mb_giup_bname.value){
        /*alert('기업명을 입력하세요.');
        f.mb_giup_bname.focus();
        return false;*/
    }

    var mb_hp = $("#mb_hp1").val() + "-" + $("#mb_hp2").val() + "-" + $("#mb_hp3").val();
    // if(!$("#mb_hp1").val()){
    //     alert('휴대폰번호를 입력해주세요.');
    //     $("#mb_hp1").focus();
    //     return false;
    // }
    // if(!$("#mb_hp2").val()){
    //     alert('휴대폰번호를 입력해주세요');
    //     $("#mb_hp2").focus();
    //     return false;
    // }
    // if(!$("#mb_hp3").val()){
    //     alert('휴대폰번호를 입력해주세요');
    //     $("#mb_hp3").focus();
    //     return false;
    // }

    var mb_tel = $("#mb_tel1").val() + "-" + $("#mb_tel2").val() + "-" + $("#mb_tel3").val();
    // if(!$("#mb_tel1").val()){
    //     alert('전화번호를 입력해주세요.');
    //     $("#mb_tel1").focus();
    //     return false;
    // }
    // if(!$("#mb_tel2").val()){
    //     alert('전화번호를 입력해주세요');
    //     $("#mb_tel2").focus();
    //     return false;
    // }
    // if(!$("#mb_tel3").val()){
    //     alert('전화번호를 입력해주세요');
    //     $("#mb_tel3").focus();
    //     return false;
    // }
    //mb_fax
    var mb_fax = $("#mb_fax1").val() + "-" + $("#mb_fax2").val() + "-" + $("#mb_fax3").val();
    // if(!$("#mb_fax1").val()){
    //     alert('팩스를 입력해주세요.');
    //     $("#mb_fax1").focus();
    //     return false;
    // }
    // if(!$("#mb_fax2").val()){
    //     alert('팩스를 입력해주세요.');
    //     $("#mb_fax2").focus();
    //     return false;
    // }
    // if(!$("#mb_fax3").val()){
    //     alert('팩스를 입력해주세요.');
    //     $("#mb_fax3").focus();
    //     return false;
    // }
    // E-mail 검사
  if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
    var msg = reg_mb_email_check();
    if (msg) {
      alert(msg);
      f.reg_mb_email.select();
      return false;
    }
  }
    // if(!f.mb_giup_bnum.value){
    //     alert('사업자 번호를 입력하세요.');
    //     f.mb_giup_bnum.focus();
    //     return false;
    // }
    // if(!f.mb_giup_boss_name.value){
    //     alert('대표자명을 입력하세요.');
    //     f.mb_giup_boss_name.focus();
    //     return false;
    // }
    // if(!f.mb_giup_bupjong.value){
    //     alert('업종을 입력하세요.');
    //     f.mb_giup_bupjong.focus();
    //     return false;
    // }
    // if(!f.mb_giup_buptae.value){
    //     alert('업태를 입력하세요.');
    //     f.mb_giup_buptae.focus();
    //     return false;
    // }
    // if(!f.mb_giup_manager_name.value){
    //     alert('담당자명을 입력하세요.');
    //     f.mb_giup_manager_name.focus();
    //     return false;
    // }

    // if(!f.mb_giup_zip.value){
    //     alert('(사업자정보) 우편번호를 입력하세요');
    //     f.mb_giup_zip.focus();
    //     return false;
    // }
    // if(!f.mb_giup_addr1.value){
    //     alert('(사업자정보) 주소를 입력하세요');
    //     f.mb_giup_addr1.focus();
    //     return false;
    // }
    // if(!f.mb_giup_addr2.value&&!f.mb_giup_addr3.value){
    //     alert('(사업자정보) 주소상세를 입력하세요');
    //     f.mb_giup_addr2.focus();
    //     return false;
    // }

    // if(!f.mb_zip.value){
    //     alert('(배송지 주소) 우편번호를 입력하세요');
    //     f.mb_zip.focus();
    //     return false;
    // }
    // if(!f.mb_addr1.value){
    //     alert('(배송지 주소) 주소를 입력하세요');
    //     f.mb_addr1.focus();
    //     return false;
    // }
    // if(!f.mb_addr2.value&&!f.mb_addr3.value){
    //     alert('(배송지 주소) 주소상세를 입력하세요');
    //     f.mb_addr2.focus();
    //     return false;
    // }
    // var msg = reg_mb_hp_check();
  // if (msg) {
  //   alert(msg);
  //   f.reg_mb_hp.select();
  //   return false;
  // }

    // if (f.mb_name.value.length < 1) {
    //     alert("관리자 이름을 입력하십시오.");
    //     f.mb_name.focus();
    //     return false;
    // }

    // if (!f.mb_sex.value) {
    //     alert("성별을 선택해주세요");
    //     f.mb_sex.focus();
    //     return false;
    // }
    
    // var mb_birth = $("#year").val() + $("#month").val() + $("#day").val();
    var mb_tel = $("#mb_tel1").val() + "-" + $("#mb_tel2").val() + "-" + $("#mb_tel3").val();

    // if(!$("#year").val()){
    //     alert('연도를 선택해주세요');
    //     $("#year").focus();
    //     return false;
    // }
    // if(!$("#month").val()){
    //     alert('월 선택해주세요');
    //     $("#month").focus();
    //     return false;
    // }
    // if(!$("#day").val()){
    //     alert('일을 선택해주세요');
    //     $("#day").focus();
    //     return false;
    // }


    //체크 끝


    //통신
    var sendData = new FormData();
    var sendData2 = new FormData();
    sendData.append("usrId", $("#mb_id").val());//아이디
    if($("#mb_password").val()){
        sendData.append("usrPw", $("#mb_password").val());//비밀번호
    }
    sendData.append("entNm", $("#mb_giup_bname").val()); //사업체명
    sendData.append("usrPnum", mb_hp);//관리자 휴대폰번호
    sendData.append("entPnum", mb_tel); //사업소 전화번호
    sendData.append("entFax", mb_fax); //사업소 팩스
    sendData.append("usrMail", $("#reg_mb_email").val());//메일

    <?php if($w){ ?> 
            sendData.append("entId", "<?=$mb['mb_entId']?>");
    <?php } ?>
    <?php if($w){ ?> 
        sendData.append("entUsrId", $("#mb_id").val() );//entUsrId
    <?php } ?>
    
    sendData.append("entCrn", $("#mb_giup_bnum").val()); //사업자 등록번호
    sendData.append("entCeoNm", $("#mb_giup_boss_name").val()); //사업소 대표
    sendData.append("entBusiType",$("#mb_giup_bupjong").val()); //사업소 업종
    sendData.append("entBusiCondition",$("#mb_giup_buptae").val()); //사업소 업태
    sendData.append("entZip", $("#mb_giup_zip").val());  //사업소 우편번호
    sendData.append("entAddr", $("#mb_giup_addr1").val()); //사업소 주소
    sendData.append("entAddrDetail",$("#mb_giup_addr2").val() + $("#mb_giup_addr3").val() ); //사업소 주소 상세
    sendData.append("entTaxCharger",$("#mb_giup_manager_name").val()); //담당자
    sendData.append("entConAcco1",$("#mb_entConAcc01").val()); //특약사항1
    sendData.append("entConAcco2",$("#mb_entConAcc02").val()); //특약사항2

    sendData.append("usrZip", $("#mb_zip").val()); //관리자 우편번호
    sendData.append("usrAddr", $("#mb_addr1").val());//관리자 주소
    sendData.append("usrAddrDetail", $("#mb_addr2").val())+$("#mb_addr3").val();//관리자 주소 상세
    sendData.append("entMail", $("#mb_giup_tax_email").val());//메일

    // sendData.append("usrNm", $("#mb_name").val()); //관리자이름
    // sendData.append("usrBirth", mb_birth);//생년월일
    // sendData.append("usrGender", $("#mb_sex").val());//성별
    // sendData.append("entBusiNum",$("#mb_giup_sbnum").val()); //종사업장번호

    <?php if($w){ ?>
    //직인파일
    var imgFileItem2 = $(".mb_giup_file2 input[type='file']");
    for(var i = 0; i < imgFileItem2.length; i++){
        if($(imgFileItem2[i])[0].files[0]){
            if($(imgFileItem2[i])[0].files[0].size > 1024 * 1024 * 2){
                alert('사업자직인 (계약서 날인) : 2MB 이하 파일만 등록할 수 있습니다.\n\n' + '현재파일 용량 : ' + (Math.round($(imgFileItem2[i])[0].files[0].size / 1024 / 1024 * 100) / 100) + 'MB');
                return false;
            }
            sendData.append("sealFile", $(imgFileItem2[i])[0].files[0]);
            sendData2.append("sealFile", $(imgFileItem2[i])[0].files[0]);
        }
    }
    <?php } ?>
    //사업자등록증
    var flag ='<?=$mb['crnFile']?>';
    var imgFileItem1 = $(".mb_giup_file1 input[type='file']");
    for(var i = 0; i < imgFileItem1.length; i++) {
        if(!flag) {
            if(!$(imgFileItem1[i])[0].files[0]){
              continue;
            }
            if($(imgFileItem1[i])[0].files[0].size > 1024 * 1024 * 2){
                alert('사업자등록증 : 2MB 이하 파일만 등록할 수 있습니다.\n\n' + '현재파일 용량 : ' + (Math.round($(imgFileItem1[i])[0].files[0].size / 1024 / 1024 * 100) / 100) + 'MB');
                return false;
            }
        }
        if($(imgFileItem1[i])[0].files[0]){
            sendData.append("crnFile", $(imgFileItem1[i])[0].files[0]);
        }
    }

    // for (let value of sendData.values()) {
    //     console.log(value);
    // }
    // return false;

    <?php
    if(!$w) {
      $api_url = "https://system.eroumcare.com:9901/api/ent/insert";
    } else {
      $api_url = "https://system.eroumcare.com:9901/api/ent/update";
    }
    ?>
    var info = "<?php echo $w==''?'회원가입 하시겠습니까?':'수정 하시겠습니까?'; ?>";
    if (confirm(info)) {
      if(!sendData.get('entId')) {
        return f.submit();
      }

      f.submit();
    }
    return false;

    if (!f.mb_icon.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_icon.value) {
      alert('아이콘은 이미지 파일만 가능합니다.');
      return false;
    }

    if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
      alert('회원이미지는 이미지 파일만 가능합니다.');
      return false;
    }

    /*if ($('input[name="mb_giup_type"]:checked').val() > 0) {
        if (!$('#mb_giup_bname').val()) {
      alert("기업명을 입력하십시오.");
      return false;
        }
        if (!$('#mb_giup_boss_name').val()) {
      alert("대표자명을 입력하십시오.");
      return false;
        }
        if (!$('#mb_giup_zip').val()) {
      alert("주소를 입력하십시오.");
      return false;
        }
        if (!$('#mb_giup_tax_email').val()) {
      alert("세금계산서 이메일을 입력하십시오.");
      return false;
    }
    }*/

    return true;
}
</script>
<script>
function generate_password(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}
$(function() {
    $(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+10y"
    });

    $('#mb_temp').on("click", function() {
        <?php if ($w === 'u') { ?>
            return false;
        <?php } ?>
        if($(this).is(":checked") == true){
            $('#mb_id').val('<?php echo $show_mb_id; ?>');
            $('#mb_password').val(generate_password(16));
            $("#mb_id").attr("readonly", true);
            $('#mb_password').attr("readonly", true);
        } else {
            $('#mb_password').val('');
            $("#mb_id").attr("readonly", false);
            $('#mb_password').attr("readonly", false);
        }
    });
    <?php if ($w === 'u') { ?>
    $("#mb_temp").bind("click",false);
        <?php if ($mb['mb_temp']) { ?>
            $("#mb_id").attr("readonly", true);
            $('#mb_password').attr("readonly", true);
        <?php } ?>
    <?php } ?>

    $('#add_manager').on("click", function() {
      $.post('ajax.member_manager.php', {
        mb_id: '<?=$mb['mb_id']?>',
        mm_id: $('#mm_id').val(),
        mm_pw: $('#mm_pw').val(),
        mm_name: $('#mm_name').val(),
		mm_tel: $('#mm_tel').val(),
        mm_email: $('#mm_email').val(),
        mm_memo: $('#mm_memo').val()
      }, 'json')
      .done(function() {
        alert('담당자 등록이 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    });

    $('.btn_mm_edit').on('click', function() {
      $tr = $(this).closest('tr');
      $.post('ajax.member_manager.php', {
        w: 'u',
        mb_id: '<?=$mb['mb_id']?>',
        mm_id: $tr.find('.mm_id').val(),
        mm_pw: $tr.find('.mm_pw').val(),
        mm_name: $tr.find('.mm_name').val(),
		mm_tel: $tr.find('.mm_tel').val(),
        mm_email: $tr.find('.mm_email').val(),
        mm_memo: $tr.find('.mm_memo').val()
      }, 'json')
      .done(function() {
        alert('담당자 수정이 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    });

    $('.btn_mm_delete').on('click', function() {
      $tr = $(this).closest('tr');
      $.post('ajax.member_manager.php', {
        w: 'd',
        mb_id: '<?=$mb['mb_id']?>',
        mm_id: $tr.find('.mm_id').val()
      }, 'json')
      .done(function() {
        alert('담당자 삭제가 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    });

    $(document).on("click", '.delete_manager', function() {
      $(this).closest('tr').remove();
    });

    $('#mb_hp').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('#mb_tel').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('#mb_fax').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('#mb_giup_btel').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('#mb_giup_bnum').on('keyup', function(){
      var num = $('#mb_giup_bnum').val();
      num.trim();
      this.value = auto_saup_hypen(num) ;
    });

    $('input[name="mm_tel[]"]').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('input[name="mm_hp[]"]').on('keyup', function(){
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });
});

function check_giup_bnum() {
    var msg = reg_mb_giup_bnum_check();
    if (msg) {
        alert(msg);
    } else {
        alert("사용 가능한 사업자번호입니다.")
    }
}

function check_giup_sbnum() {
    var msg = reg_mb_giup_sbnum_check();
    if (msg) {
        alert(msg);
    } else {
        alert("사용 가능한 종사업자번호입니다.")
    }
}

function togglePartnerType(x) {
  $('.partner-type-wrapper').hide();

  if ($(x).val() === 'partner') {
    $('.partner-type-wrapper').show();
  }
}

</script>

<?php
include_once('./admin.tail.php');
?>
