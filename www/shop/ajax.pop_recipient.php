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
    
    if($data["penBirth"]) {
      $penBirth = preg_replace("/[^0-9]/", "", $data["penBirth"]);
      $penBirth = DateTime::createFromFormat('Ymd', $penBirth);
      $penBirth = $penBirth->format('Y-m-d');
    }

    if($data["penExpiDtm"]) {
      $penExpiDtm = explode(' ~ ', $data["penExpiDtm"]);
    } else {
      $penExpiDtm = [];
    }

    # 수급자 취급가능 제품
    $data2 = [];
    $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST, array(
      'penId' => $data['penId']
    ));

    if($res['data'])
      $data2 = $res["data"];

    $ret .= '
    <li>
      <form class="form_recipient" autocomplete="off">
        <input type="hidden" name="penId" value="'.$data['penId'].'">
        <input type="hidden" name="penNm" value="'.$data['penNm'].'">
        <input type="hidden" name="penLtmNum" value="'.$data['penLtmNum'].'">
    ';
    if(!$data['incomplete']) {
      $ret .= '
        <table class="tbl_static">
          <tr>
            <td>수급자명</td>
            <td>'.$data["penNm"].'</td>
          </tr>
          <tr>
            <td>성별</td>
            <td>'.$data["penGender"].'</td>
          </tr>
          <tr>
            <td>장기요양번호</td>
            <td>'.($data["penLtmNum"] ? $data["penLtmNum"] : '-').'</td>
          </tr>
          <tr>
            <td>인정등급</td>
            <td>'.$data["penRecGraNm"].'</td>
          </tr>
          <tr>
            <td>본인부담금율</td>
            <td>'.$data["penTypeNm"].'</td>
          </tr>
          <tr>
            <td>유효기간</td>
            <td>'.$data["penExpiDtm"].'</td>
          </tr>
          <tr>
            <td>생년월일</td>
            <td>'.$penBirth.'</td>
          </tr>
          <tr>
            <td>연사용금액</td>
            <td>'.number_format($grade_year_info['sum_price']).'</td>
          </tr>
      ';

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
    }

    $ret .= '
        <input type="hidden" name="incomplete" value="'.$data['incomplete'].'">
        <table class="tbl_edit" style="'.($data['incomplete'] ? '' : 'display: none;').'">
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
              <label>
                <input type="radio" name="penGender" value="남" '.get_checked($data['penGender'], '남').' style="vertical-align: middle; margin: 0 5px 0 0;">남
              </label>
              <label>
                <input type="radio" name="penGender" value="여" '.get_checked($data['penGender'], '여').' style="vertical-align: middle; margin: 0 5px 0 0;">여
              </label>
            </td>
          </tr>
          <tr class="edit_only">
            <td>휴대폰</td>
            <td>
              <input type="text" name="penConNum" value="'.$data['penConNum'].'">
            </td>
          </tr>
          <tr class="edit_only">
            <td>일반전화</td>
            <td>
              <input type="text" name="penConPnum" value="'.$data['penConPnum'].'">
            </td>
          </tr>
          <tr class="edit_only">
            <td>주소</td>
            <td>
              <label>
                <input type="text" name="penZip" value="'.$data["penZip"].'" class="penZip" style="width: 50px;" maxlength="6" readonly>
              </label>
              <label>
                <button type="button" class="btn btn-black btn-sm" onclick="zipPopupOpen(this);" style="margin-top:0px;">주소 검색</button>
              </label>
    
              <div style="margin: 5px 0;">
                <input type="text" name="penAddr" value="'.$data["penAddr"].'" class="penAddr" style="width: 100%;" placeholder="기본주소" readonly>
              </div>
    
              <div class="addr-line">
                <input type="text" name="penAddrDtl" value="'.$data["penAddrDtl"].'" class="" style="width: 100%;" placeholder="상세주소">
              </div>
            </td>
          </tr>
          <tr>
            <td>장기요양번호</td>
            <td>' . ($data["penLtmNum"] ? $data["penLtmNum"] : '-') . '</td>
          </tr>
          <tr>
            <td>인정등급</td>
            <td>
              <select name="penRecGraCd">
                <option value="00" '.get_selected($data['penRecGraCd'], '00').'>등급외</option>
                <option value="01" '.get_selected($data['penRecGraCd'], '01').'>1등급</option>
                <option value="02" '.get_selected($data['penRecGraCd'], '02').'>2등급</option>
                <option value="03" '.get_selected($data['penRecGraCd'], '03').'>3등급</option>
                <option value="04" '.get_selected($data['penRecGraCd'], '04').'>4등급</option>
                <option value="05" '.get_selected($data['penRecGraCd'], '05').'>5등급</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>본인부담금율</td>
            <td>
              <select name="penTypeCd">
                <option value="00" '.get_selected($data['penTypeCd'], '00').'>일반 15%</option>
                <option value="01" '.get_selected($data['penTypeCd'], '01').'>감경 9%</option>
                <option value="02" '.get_selected($data['penTypeCd'], '02').'>감경 6%</option>
                <option value="03" '.get_selected($data['penTypeCd'], '03').'>의료 6%</option>
                <option value="04" '.get_selected($data['penTypeCd'], '04').'>기초 0%</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>유효기간</td>
            <td>
              <input type="text" name="penExpiStDtm" value="'.$penExpiDtm[0].'" class="datepicker">
              ~
              <input type="text" name="penExpiEdDtm" value="'.$penExpiDtm[1].'" class="datepicker">
            </td>
          </tr>
          <tr>
            <td>생년월일</td>
            <td>
              <input type="text" name="penBirth" value="'.$penBirth.'" class="datepicker">
            </td>
          </tr>
          <tr>
            <td>주민번호(앞자리)</td>
            <td>
              <input type="text" name="penJumin" value="'.$data['penJumin'].'">
            </td>
          </tr>
          <tr class="edit_only">
            <td>판매가능품목</td>
            <td>
    ';
    foreach($sale_product_table as $id => $name) {
      $checked = array_search($id, array_column($data2, 'itemId')) !== false;
      $ret .= '
              <label class="checkbox-inline">
                <input type="checkbox" class="chk_sale_product chk_sale_product_child" '.get_checked($checked, true).' name="'.$id.'" value="'.$id.'">'.$name.'
              </label>
      ';
    }
    $ret .= '
            </td>
          </tr>
          <tr class="edit_only">
            <td>대여가능품목</td>
            <td>
    ';
    foreach($rental_product_table as $id => $name) {
      $checked = array_search($id, array_column($data2, 'itemId')) !== false;
      $ret .= '
              <label class="checkbox-inline">
                <input type="checkbox" class="chk_sale_product chk_sale_product_child" '.get_checked($checked, true).' name="'.$id.'" value="'.$id.'">'.$name.'
              </label>
      ';
    }
    $ret .= '
            </td>
          </tr>
          <tr>
            <td>연 사용금액</td>
            <td>' . number_format($grade_year_info['sum_price']) .' 원</td>
          </tr>
    ';
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
      $ret .= '<a href="javascript:void(0)" class="sel_address" data-target="'.$recipient.'" data-edit="'.($data['incomplete'] ? '1' : '0').'" title="선택">선택</a>';
      if(!$data['incomplete'])
        $ret .= '<a href="javascript:void(0)" class="btn_edit" title="선택">정보수정</a>';
    }
    $ret .= '
      </form>
    </li>
    ';
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
