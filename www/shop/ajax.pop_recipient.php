<?php
include_once("./_common.php");

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$page = $_GET["page"] ?? 1;
$ca_id_arr = array_filter(explode('|', $_GET['ca_id']));

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["appCd"] = "01";
$sendData["pageNum"] = $page;
$sendData["pageSize"] = 10;

if($_GET["penNm"]){
  $sendData["penNm"] = $_GET["penNm"];
}

if($_GET["penTypeCd"]&&$_GET["penTypeCd"]!=="수급자구분"){
  $sendData["penTypeCd"] = $_GET["penTypeCd"];
}

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/recipient/selectList");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);

$list = [];
foreach($res['data'] as $data) {
  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
  $is_incomplete = false;
  foreach($checklist as $check) {
    if(!$data[$check])
      $is_incomplete = true;
  }
  if(!in_array($data['penGender'], ['남', '여']))
    $is_incomplete = true;
  if($data['penTypeCd'] == '04' && !$data['penJumin'])
    $is_incomplete = true;
  if($data['penExpiDtm']) {
    // 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
    $expired_dtm = substr($data['penExpiDtm'], -10);
    if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
      $data['penExpiDtm'] = '';
      $is_incomplete = true;
    }
  }

  $data['incomplete'] = $is_incomplete;

  $list[] = $data;
}

$ret = '';

if($list) {
  foreach($list as $data) {
    $warning = [];
    if(is_array($ca_id_arr)) {
      foreach($ca_id_arr as $ca_id) {
        $limit = get_pen_category_limit($data["penLtmNum"], $ca_id);
        if($limit) {
          $cur = intval($limit['num']) - intval($limit['current']);
          if($cur <= 0) {
            // 구매불가능
            $warning_text = "\"{$limit['ca_name']}\" 구매가능 개수가 초과되었습니다.";
            if(!in_array($warning_text, $warning))
              $warning[] = $warning_text;
          }
        }
      }
    }
    $grade_year_info = get_recipient_grade_per_year($data['penId']);
    $recipient = $data["rn"]."|".$data["penId"]."|".$data["entId"]."|".$data["penNm"]."|".$data["penLtmNum"]."|".$data["penRecGraCd"]."|".$data["penRecGraNm"]."|".$data["penTypeCd"]."|".$data["penTypeNm"]."|".$data["penExpiStDtm"]."|".$data["penExpiEdDtm"]."|".$data["penExpiDtm"]."|".$data["penExpiRemDay"]."|".$data["penGender"]."|".$data["penGenderNm"]."|".$data["penBirth"]."|".$data["penAge"]."|".$data["penAppEdDtm"]."|".$data["penAddr"]."|".$data["penAddrDtl"]."|".$data["penConNum"]."|".$data["penConPnum"]."|".$data["penProNm"]."|".$data["usrId"]."|".$data["appCd"]."|".$data["appCdNm"]."|".$data["caCenYn"]."|".$data["regDtm"]."|".$data["regDt"]."|".$data["ordLendEndDtm"]."|".$data["ordLendRemDay"]."|".$data["usrNm"]."|".$data["penAppRemDay"]."|800,000원";
    
    $ret .= '
    <li>
      <form class="form_recipient" autocomplete="off">
        <input type="hidden" name="penId" value="'.$data['penId'].'">
        <input type="hidden" name="penNm" value="'.$data['penNm'].'">
        <input type="hidden" name="penLtmNum" value="'.$data['penLtmNum'].'">
        <table>
          <tr>
            <td>수급자명</td>
            <td>
              '.$data["penNm"];
              if($data['incomplete']) {
                $ret .= '<span class="notice_incom"><img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png"> 필수정보 입력 후 선택가능</span>';
              }
            $ret .= '
            </td>
          </tr>
          <tr>
            <td>성별</td>
            <td>
              ';
              if(in_array($data["penGender"], ['남', '여'])) {
                $ret .= $data["penGender"];
                $ret .= '<input type="hidden" name="penGender" value="'.$data["penGender"].'">';
              } else {
                $ret .= '
                  <label class="checkbox-inline">
                    <input type="radio" name="penGender" value="남" style="vertical-align: middle; margin: 0 5px 0 0;">남
                  </label>
                  <label class="checkbox-inline">
                    <input type="radio" name="penGender" value="여" style="vertical-align: middle; margin: 0 5px 0 0;">여
                  </label>
                ';
              }
          $ret .= '
            </td>
          </tr>
          <tr>
            <td>장기요양번호</td>
            <td>' . ($data["penLtmNum"] ? $data["penLtmNum"] : '-') . '</td>
          </tr>
          <tr>
            <td>인정등급</td>
            <td>
              ';
              if($data["penRecGraNm"]) {
                $ret .= $data["penRecGraNm"];
                $ret .= '<input type="hidden" name="penRecGraCd" value="'.$data["penRecGraCd"].'">';
              } else {
                $ret .= '
                  <select name="penRecGraCd">
                    <option value="00">등급외</option>
                    <option value="01">1등급</option>
                    <option value="02">2등급</option>
                    <option value="03">3등급</option>
                    <option value="04">4등급</option>
                    <option value="05">5등급</option>
                  </select>
                ';
              }
          $ret .= '
            </td>
          </tr>
          <tr>
            <td>본인부담금율</td>
            <td>';
              if($data['penTypeNm']) {
                $ret .= $data["penTypeNm"];
                $ret .= '<input type="hidden" name="penTypeCd" value="'.$data["penTypeCd"].'">';
              } else {
                $ret .= '
                <select name="penTypeCd">
                  <option value="00">일반 15%</option>
                  <option value="01">감경 9%</option>
                  <option value="02">감경 6%</option>
                  <option value="03">의료 6%</option>
                  <option value="04">기초 0%</option>
                </select>
                ';
              }
            $ret .= '
            </td>
          </tr>
          <tr>
            <td>유효기간</td>
            <td>
            ';
              if($data["penExpiDtm"]) {
                $penExpiDtm = explode(' ~ ', $data["penExpiDtm"]);
                $ret .= $data["penExpiDtm"];
                $ret .= '<input type="hidden" name="penExpiStDtm" value="'.$penExpiDtm[0].'">';
                $ret .= '<input type="hidden" name="penExpiEdDtm" value="'.$penExpiDtm[1].'">';
              } else {
                $ret .= '
                  <input type="text" name="penExpiStDtm" class="datepicker">
                  ~
                  <input type="text" name="penExpiEdDtm" class="datepicker">
                ';
              }
            $ret .= '
            </td>
          </tr>
          <tr>
            <td>생년월일</td>
            <td>';
              if($data["penBirth"] && !($data['penTypeCd'] == '04' && !$data['penJumin'])) {
                $penBirth = preg_replace("/[^0-9]/", "", $data["penBirth"]);
                $penBirth = DateTime::createFromFormat('Ymd', $penBirth);
                $penBirth = $penBirth->format('Y-m-d');

                $ret .= $penBirth;
                $ret .= '<input type="hidden" name="penBirth" value="'.$penBirth.'">';
                $ret .= '<input type="hidden" name="penJumin" value="'.$data["penJumin"].'">';
              } else {
                $ret .= '<input type="text" name="penBirth" class="datepicker">';
              }
            $ret .= '
            </td>
          </tr>
          <tr>
            <td>연 사용금액</td>
            <td>' . number_format($grade_year_info['sum_price']) .' 원</td>
          </tr>';
          foreach($warning as $warning_text) {
          $ret .= '
          <tr>
            <td colspan="2" style="color: red">' . $warning_text . '</td>
          </tr>
          ';
          }
        $ret .= '
        </table>
        ';
        if($warning) {
        $ret .= '<div class="warning">구매가능초과</div>';
        } else if($grade_year_info['sum_price'] > 1600000) {
        $ret .= '<div class="warning">사용금액초과</div>';
        } else {
        $ret .= '<a href="javascript:void(0)" class="sel_address" data-target="'.$recipient.'" data-incomplete="'.($data['incomplete'] ? 'true' : 'false').'" title="선택">선택</a>';
        }
      $ret .= '
      </form>
    </li>';
      }

      $is_last = false;
} else {
  $ret = '
<div class="empty_list">
  수급자가 없습니다.
</div>
';
$is_last = true;
}

json_response(200, 'OK', array(
  'is_last' => $is_last,
  'html' => $ret
));