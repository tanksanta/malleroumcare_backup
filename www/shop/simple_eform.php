<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default' || !$member['mb_entId'])
  alert('사업소 회원만 접근할 수 있습니다.');

$g5['title'] = '계약서 작성';
include_once("./_head.php");

$dc_id = clean_xss_tags($_GET['dc_id']);
if($dc_id) {
  $dc = sql_fetch("
  SELECT HEX(`dc_id`) as uuid, e.*
  FROM `eform_document` as e
  WHERE dc_id = UNHEX('$dc_id') and entId = '{$member['mb_entId']}' and dc_status = '11' ");

  if(!$dc['uuid'])
    unset($dc);

  // 보호자 정보 가져오기(장기요양입소이용신청서용)
  $pen = get_recipient($dc['penId']);
  $pros = get_pros_by_recipient($dc['penId']);
  if($pen['penProNm']) {
    array_unshift($pros, [
        'pro_name' => $pen['penProNm'],
        'pro_type' => $pen['penProTypeCd'],
        'pro_rel_type' => $pen['penProRel'],
        'pro_rel' => $pen['penProRelEtc'],
        'pro_birth' => $pen['penProBirth'],
        'pro_hp' => $pen['penProConNum'],
        'pro_tel' => $pen['penProConPnum'],
        'pro_zip' => $pen['penProZip'],
        'pro_addr1' => $pen['penProAddr'],
        'pro_addr2' => $pen['penProAddrDtl']
    ]);
  }
}

// 이전에 저장했던 간편계약서 삭제
/*$sql = "
  select hex(dc_id) as uuid
  from eform_document
  where dc_status = '10' and entId = '{$member['mb_entId']}'
";
$result = sql_query($sql);
while($row = sql_fetch_array($result)) {
  $dc_id = $row['uuid'];

  $sql = " DELETE FROM eform_document_item WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
  $sql = " DELETE FROM eform_document_log WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
  $sql = " DELETE FROM eform_document WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
}*/

/**
* 기존에 있던  eform_document 테이블을 재사용하기 위한 작업
* 새로이 필요한 컬럼(applicantCd)이 존재하는지 확인 후, 없으면 새로 추가하는 작업 진행
*/
$sql_check = "
  show columns from eform_document where field in ('applicantCd');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){

  $append_col = "alter table eform_document ".
                "add column applicantCd varchar(255) after penAddrDtl";
  sql_query($append_col);
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_eform.css?v=1128">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
add_javascript('<script src="'.G5_JS_URL.'/ckeditor/ckeditor.js"></script>');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

?>

<section class="wrap">
  <div class="sub_section_tit">
    간편 계약서 작성
    <div class="r_btn_area">
      <a href="/shop/electronic_manage.php" class="btn eroumcare_btn2">계약서 목록보기</a>
    </div>
    <div style="clear: both;"></div>
  </div>
  <div class="inner">
  <!-- hula1202_1637198335.gif -->
    <?php if(!$member['sealFile']) { ?>
    <div class="se_seal_wr">
      <form action="ajax.member.seal_upload.php" method="POST" id="form_seal" onsubmit="return false;" onkeydown="if(event.keyCode==13) return false;">
        <div class="se_seal_desc">
          등록된 사업소 직인이 없습니다.<br>
          계약서 작성을 위해선 직인 이미지를 등록해주세요.
        </div>
        <button type="button" class="btn_se_seal">직인 이미지 업로드</button>
        <br>
        <label><input type="checkbox" name="chk_se_seal_self" id="chk_se_seal_self" <?php echo $member['sealFile_self']?'checked':''; ?>> 직접날인</label>
      </form>
    </div>
    <?php } ?>
    <form id="form_simple_eform" method="POST" class="form-horizontal" autocomplete="off" onsubmit="return false;" onkeydown="if(event.keyCode==13) return false;">
      <input type="hidden" name="w" value="<?php if($dc) echo 'u'; ?>">
      <input type="hidden" name="dc_id" value="<?php if($dc) echo $dc['uuid']; ?>">
      <input type="hidden" name="penRecTypeCd" id="penRecTypeCd" value="<?php if(!$dc) echo '02'; if($dc) echo $dc['penRecTypeCd']; ?>">
      <input type="hidden" name="penRecTypeTxt" id="penRecTypeTxt" value="<?php if($dc) echo $dc['penRecTypeTxt']; ?>">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="radio_wr" style="margin-top: -10px; margin-bottom: 10px; font-size: 14px;">
            <label class="radio-inline">
              <input type="radio" name="pen_type" id="pen_type_1" value="1" <?php if($dc['penId'] || !$dc) echo 'checked' ?>> 수급자 선택
            </label>
            <label class="radio-inline">
              <input type="radio" name="pen_type" id="pen_type_0" value="0" <?php if($dc && !$dc['penId']) echo 'checked' ?>> 수급자 등록
            </label>
          </div>
          <div class="form-group">
            <label for="penNm" class="col-md-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-md-3 col-pen-nm" style="max-width: unset;">
              <img style="display: none;" src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
              <input type="hidden" name="penZip" id="penZip" value="<?php if($dc) echo $dc['penZip']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penZip']}\""; ?>>
              <input type="hidden" name="penAddr" id="penAddr" value="<?php if($dc) echo $dc['penAddr']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penAddr']}\""; ?>>
              <input type="hidden" name="penAddrDtl" id="penAddrDtl" value="<?php if($dc) echo $dc['penAddrDtl']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penAddrDtl']}\""; ?>>
              <input type="hidden" name="penId" id="penId" value="<?php if($dc) echo $dc['penId']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penId']}\""; ?>>
              <input type="text" name="penNm" id="penNm" class="form-control input-sm pen_id_flexdatalist" value="<?php if($dc) echo $dc['penNm']; ?>" placeholder="수급자명" <?php if($dc) echo "data-orig=\"{$dc['penNm']}\""; ?>>
            </div>
            <button type="button" id="btn_pen">수급자 목록</button>
          </div>
          <div class="form-group">
            <label for="penLtmNum" class="col-md-2 control-label">
              <strong>요양인정번호</strong>
            </label>
            <div class="col-md-3">
              L <input type="text" name="penLtmNum" id="penLtmNum" class="form-control input-sm" value="<?php if($dc) echo preg_replace('/L([0-9]{10})/', '${1}', $dc['penLtmNum']); ?>" placeholder="10자리 입력" readonly <?php if($dc) echo "data-orig=\"" . preg_replace('/L([0-9]{10})/', '${1}', $dc['penLtmNum']) . "\""; ?>>
            </div>
            <label for="penGender" class="col-md-2 control-label">
              <strong>휴대폰번호</strong>
            </label>
            <div class="col-md-3">
              <input type="text" maxlength="11" oninput="max_length_check(this)" name="penConNum" id="penConNum" class="form-control input-sm" value="<?php if($dc) echo $dc['penConNum']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penConNum']}\""; ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="penRecGraCd" class="col-md-2 control-label">
              <strong>인정등급</strong>
            </label>
            <div class="col-md-3">
              <select name="penRecGraCd" id="penRecGraCd" class="form-control input-sm" <?php if($dc) echo "data-orig=\"{$dc['penRecGraCd']}\""; ?>>
                <option value="00" <?php if($dc) echo get_selected($dc['penRecGraCd'], '00'); ?>>등급외</option>
                <option value="01" <?php if($dc) echo get_selected($dc['penRecGraCd'], '01'); ?>>1등급</option>
                <option value="02" <?php if($dc) echo get_selected($dc['penRecGraCd'], '02'); ?>>2등급</option>
                <option value="03" <?php if($dc) echo get_selected($dc['penRecGraCd'], '03'); ?>>3등급</option>
                <option value="04" <?php if($dc) echo get_selected($dc['penRecGraCd'], '04'); ?>>4등급</option>
                <option value="05" <?php if($dc) echo get_selected($dc['penRecGraCd'], '05'); ?>>5등급</option>
                <option value="06" <?php if($dc) echo get_selected($dc['penRecGraCd'], '06'); ?>>6등급</option>
              </select>
            </div>
            <label for="penTypeCd" class="col-md-2 control-label">
              <strong>본인부담금율</strong>
            </label>
            <div class="col-md-3">
              <select name="penTypeCd" id="penTypeCd" class="form-control input-sm" <?php if($dc) echo "data-orig=\"{$dc['penTypeCd']}\""; ?>>
                <option value="00" <?php if($dc) echo get_selected($dc['penTypeCd'], '00'); ?>>일반 15%</option>
                <option value="01" <?php if($dc) echo get_selected($dc['penTypeCd'], '01'); ?>>감경 9%</option>
                <option value="02" <?php if($dc) echo get_selected($dc['penTypeCd'], '02'); ?>>감경 6%</option>
                <option value="03" <?php if($dc) echo get_selected($dc['penTypeCd'], '03'); ?>>의료 6%</option>
                <option value="04" <?php if($dc) echo get_selected($dc['penTypeCd'], '04'); ?>>기초 0%</option>
              </select>
            </div>
            <label for="applicantCd" class="col-md-4 control-label">
              <strong id="applicantCd_subj"  <?php if(!$dc['applicantCd']) echo 'style="display: none"'; ?>>장기요양입소이용신청서 신청인</strong>
            </label>
            <div class="col-md-3" id="select_applicantCd" >
              <select name="applicantCd" id="applicantCd" class="form-control input-sm" <?php if(!$dc['applicantCd']) echo 'style="display: none"'; ?>>
                <option value="00" <?php if($dc) echo get_selected($dc['applicantCd'], '00'); ?>>본인</option>
                <?php if($dc) { // 계약서 수정인 경우 ?>
                  <?php for($i = 0; $i < count($pros); $i++){ ?>
                    <?php if(!empty($pros[$i]['pro_name'])&&!empty($pros[$i]['pro_hp'])&&!empty($pros[$i]['pro_addr1'])&&!empty($pros[$i]['pro_zip'])&&!empty($pros[$i]['pro_birth'])&&$pros[$i]['pro_type']!='02'){ // 정보가 완전하고 요양보호사가 아닌 경우 ?>
                        <option value=<?php echo "\"".$pros[$i]['pro_name']."_".$pros[$i]['pro_birth']."\" ".get_selected(explode('_', $dc['applicantCd'])[0], $pros[$i]['pro_name']);  ?> >보호자<?php echo "(".$pros[$i]['pro_name'].") ";  ?></option>
                 <?php  } } } ?>
                <option value="02" <?php if($dc) echo get_selected($dc['applicantCd'], '02'); ?>>공란</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="penExpiStDtm" class="col-md-2 control-label">
              <strong>유효기간</strong>
            </label>
            <div class="col-md-5">
              <input type="text" name="penExpiStDtm" id="penExpiStDtm" class="datepicker form-control input-sm" value="<?php if($dc) echo explode(' ~ ', $dc['penExpiDtm'])[0]; ?>" <?php if($dc) echo "data-orig=\"" . explode(' ~ ', $dc['penExpiDtm'])[0] . "\""; ?>> ~ <input type="text" name="penExpiEdDtm" id="penExpiEdDtm" class="datepicker form-control input-sm" value="<?php if($dc) echo explode(' ~ ', $dc['penExpiDtm'])[1]; ?>" <?php if($dc) echo "data-orig=\"" . explode(' ~ ', $dc['penExpiDtm'])[1] . "\""; ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="penJumin" class="col-md-2 control-label">
              <strong>주민번호(앞자리)</strong>
            </label>
            <div class="col-md-3">
              <input type="text" maxlength="6" oninput="max_length_check(this)"  name="penJumin" id="penJumin" class="form-control input-sm" value="<?php if($dc) echo $dc['penJumin']; ?>" <?php if($dc) echo "data-orig=\"{$dc['penJumin']}\""; ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="penJumin" class="col-md-2 control-label">
              <strong>계약서 날인</strong>
            </label>
            <div class="col-md-5">
              <div class="radio_wr">
                <label class="radio-inline">
                  <input type="radio" name="contract_sign_type" id="contract_sign_type" value="0" <?php if($dc['contract_sign_type'] == 0) echo 'checked' ?>> 수급자
                </label>
                <label class="radio-inline">
                  <input type="radio" name="contract_sign_type" id="contract_sign_type" value="1" <?php if($dc['contract_sign_type'] > 0) echo 'checked' ?>> 대리인
                </label>
                <input style="margin:0px 5px;" type="text" name="contract_sign_name" id="contract_sign_name" class="form-control input-sm" value="<?php if($dc['contract_sign_type'] > 0) echo $dc['contract_sign_name']; ?>" <?php if($dc['contract_sign_type'] == 0) echo 'disabled' ?> >
                <select name="contract_sign_relation" id="contract_sign_relation" class="form-control input-sm" <?php if($dc['contract_sign_type'] == 0) echo 'disabled' ?>>
                  <option value="1" <?php if($dc) echo get_selected($dc['contract_sign_relation'], '1'); ?>>가족</option>
                  <option value="2" <?php if($dc) echo get_selected($dc['contract_sign_relation'], '2'); ?>>친족</option>
                  <option value="3" <?php if($dc) echo get_selected($dc['contract_sign_relation'], '3'); ?>>기타</option>
              </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="penJumin" class="col-md-2 control-label">
              <strong>확인 방법</strong>
            </label>
            <div class="col-md-5">
              <div class="radio_wr">
                <label class="radio-inline">
                  <input type="radio" name="penRecTypeCd_radio" class="penRecTypeCd_radio penRecTypeCd02" value="02" <?php if(!$dc || $dc['penRecTypeCd'] == '02') echo 'checked' ?>> 방문
                </label>
                <label class="radio-inline">
                  <input type="radio" name="penRecTypeCd_radio" class="penRecTypeCd_radio penRecTypeCd01" value="01" <?php if($dc['penRecTypeCd'] == '01') echo 'checked' ?>> 유선
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="se_btn_wr">
          <button type="submit" id="btn_se_submit" class="btn_se_submit">
            <img src="<?=THEMA_URL?>/assets/img/icon_contract.png" alt="">
            계약서 생성
          </button>
        </div>
      </div>
      <div id="se_body_wr" class="flex space-between <?php if($dc) echo 'active' ;?>" >
        <div class="se_item_wr">
          <div class="se_sch_wr">
            <div class="flex space-between align-items">
              <div class="se_sch_hd">상품정보</div>
              <button type="button" class="btn_se_sch" id="btn_se_sch">상품검색</button>
            </div>
            <div class="ipt_se_sch_wr">
              <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
              <input type="text" id="ipt_se_sch" class="ipt_se_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
            </div>
            <div class="se_sch_pop">
              <p>상품명을 입력 후 간편하게 추가할 수 있습니다.<br> 상품명 일부만 입력해도 자동완성됩니다.</p>
              <!-- <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
              <p><button type="button" class="btn_se_sch" id="btn_se_sch">상품검색</button></p> -->
            </div>
          </div>
          
            <div class="no_item_info">
	        	<img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
        	<p>상품을 검색한 후 추가해주세요.</p>
	        	<!-- <p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p> -->
	        </div>
          
          <div class="se_item_list_hd">추가 된 상품 목록</div>
          <div class="se_item_hd">판매품목</div>
          <ul id="buy_list" class="se_item_list">
            <?php
            if($dc) {
              $sql = "
                SELECT
                 i.*,
                 x.it_img1 as it_img,
                 x.it_id as id,
                 count(*) as qty
                FROM
                  eform_document_item i
                LEFT JOIN
                  g5_shop_item x ON x.it_id = (
                    select it_id
                    from g5_shop_item
                    where
                      ProdPayCode = i.it_code and
                      (
                        ( i.gubun = '00' and ca_id like '10%' ) or
                        ( i.gubun = '01' and ca_id like '20%' )
                      )
                    limit 1
                  )
                WHERE
                  i.gubun = '00' and
                  dc_id = UNHEX('$dc_id')
                GROUP BY
                  i.it_code
                ORDER BY
                  i.it_id ASC
              ";

              $result = sql_query($sql, true);

              while($row = sql_fetch_array($result)) {
                $sql = "
                  SELECT it_barcode
                  FROM eform_document_item
                  WHERE
                    gubun = '00' and
                    dc_id = UNHEX('$dc_id') and
                    it_code = '{$row['it_code']}'
                  ORDER BY
                    it_id ASC
                ";

                $result_barcode = sql_query($sql);
                $barcodes = [];
                while($barcode = sql_fetch_array($result_barcode)) {
                  $barcodes[] = $barcode['it_barcode'];
                }
            ?>
            <li class="list item" data-code="<?=$row['id']?>" data-uid="<?=$row['it_id']?>">
              <input type="hidden" name="it_id[]" value="<?=$row['id']?>">
              <input type="hidden" name="it_gubun[]" value="판매">
              <div class="it_info">
                <img class="it_img" src="/data/item/<?=$row['it_img']?>" onerror="this.src='/img/no_img.png';">
                <p class="it_cate"><?=$row['ca_name']?></p>
                <p class="it_name"><?=$row['it_name']?> (판매)</p>
                <p class="it_price">급여가 : <?=number_format($row['it_price'])?>원</p>
              </div>
              <div class="it_btn_wr flex align-items space-between">
                <div class="it_qty">
                  <div class="input-group">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                    </div>
                    <input type="text" name="it_qty[]" value="<?=$row['qty']?>" class="form-control input-sm">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn_del_item">삭제</button>
              </div>
              <div class="it_ipt_wr">
                <div class="flex">
                  <div class="it_ipt_hd">판매계약일</div>
                  <div class="it_ipt">
                    <input type="text" name="it_date[]" class="datepicker inline" value="<?=$row['it_date']?>">
                  </div>
                </div>
                <div class="flex">
                  <div class="it_ipt_hd">바코드</div>
                  <input type="hidden" name="it_barcode[]">
                  <div class="it_barcode_wr it_ipt">
                    <?php
                    $inserted_count = 0;
                    for($x = 0; $x < $row['qty']; $x++) {
                      $barcode = $barcodes[$x];
                      if($barcode) $inserted_count++;
                      echo '<input type="hidden" class="it_barcode barcode_input" maxlength="12" value="' . $barcode . '">';
                    }
                    echo '<a class="prodBarNumCntBtn open_input_barcode">바코드 ('.$inserted_count.'/'.$row['qty'].')</a>';
                    ?>
                    <p>바코드 미입력 시 계약서 작성 후 이로움에 주문이 가능합니다.</p>
                  </div>
                </div>
              </div>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <div class="se_item_hd">대여품목</div>
          <ul id="rent_list" class="se_item_list">
          <?php
            if($dc) {
              $sql = "
                SELECT
                 i.*,
                 x.it_img1 as it_img,
                 x.it_id as id,
                 x.it_rental_price,
                 count(*) as qty
                FROM
                  eform_document_item i
                LEFT JOIN
                  g5_shop_item x ON x.it_id = (
                    select it_id
                    from g5_shop_item
                    where
                      ProdPayCode = i.it_code and
                      (
                        ( i.gubun = '00' and ca_id like '10%' ) or
                        ( i.gubun = '01' and ca_id like '20%' )
                      )
                    limit 1
                  )
                WHERE
                  i.gubun = '01' and
                  dc_id = UNHEX('$dc_id')
                GROUP BY
                  i.it_code
                ORDER BY
                  i.it_id ASC
              ";

              $result = sql_query($sql, true);

              while($row = sql_fetch_array($result)) {
                $sql = "
                  SELECT it_barcode
                  FROM eform_document_item
                  WHERE
                    gubun = '01' and
                    dc_id = UNHEX('$dc_id') and
                    it_code = '{$row['it_code']}'
                  ORDER BY
                    it_id ASC
                ";

                $result_barcode = sql_query($sql);
                $barcodes = [];
                while($barcode = sql_fetch_array($result_barcode)) {
                  $barcodes[] = $barcode['it_barcode'];
                }
            ?>
            <li class="list item" data-code="<?=$row['id']?>" data-uid="<?=$row['it_id']?>">
              <input type="hidden" name="it_id[]" value="<?=$row['id']?>">
              <input type="hidden" name="it_gubun[]" value="대여">
              <div class="it_info">
                <img class="it_img" src="/data/item/<?=$row['it_img']?>" onerror="this.src='/img/no_img.png';">
                <p class="it_cate"><?=$row['ca_name']?></p>
                <p class="it_name"><?=$row['it_name']?> (대여)</p>
                <p class="it_price">월 대여가 : <?=number_format($row['it_rental_price'])?>원</p>
              </div>
              <div class="it_btn_wr flex align-items space-between">
                <div class="it_qty">
                  <div class="input-group">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                    </div>
                    <input type="text" name="it_qty[]" value="<?=$row['qty']?>" class="form-control input-sm">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn_del_item">삭제</button>
              </div>
              <div class="it_ipt_wr">
                <div class="flex">
                  <div class="it_ipt_hd">계약기간</div>
                  <div class="it_date_wr it_ipt">
                    <input type="hidden" name="it_date[]">
                    <?php
                    $str_date = substr($row['it_date'], 0, 10);
                    $end_date = substr($row['it_date'], 11, 10);
                    ?>
                    <input type="text" class="datepicker inline" data-range="from" value="<?=$str_date?>"> ~ <input type="text" class="datepicker inline" data-range="to" value="<?=$end_date?>">
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 6)">6개월</button>
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 12)">1년</button>
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 24)">2년</button>
                  </div>
                </div>
                <div class="flex">
                  <div class="it_ipt_hd">바코드</div>
                  <input type="hidden" name="it_barcode[]">
                  <div class="it_barcode_wr it_ipt">
                    <?php
                    $inserted_count = 0;
                    for($x = 0; $x < $row['qty']; $x++) {
                      $barcode = $barcodes[$x];
                      if($barcode) $inserted_count++;
                      echo '<input type="hidden" class="it_barcode barcode_input" maxlength="12" value="' . $barcode . '">';
                    }
                    echo '<a class="prodBarNumCntBtn open_input_barcode">바코드 ('.$inserted_count.'/'.$row['qty'].')</a>';
                    ?>
                    <p>바코드 미입력 시 계약서 작성 후 이로움에 주문이 가능합니다.</p>
                  </div>
                </div>
              </div>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <div class="se_conacc">
            <div class="se_conacc_hd">계약서의 특약사항 내용</div>
            <textarea name="entConAcc01" id="entConAcc01"><?php if($dc) echo $dc['entConAcc01']; else echo nl2br($member['mb_entConAcc01']); ?></textarea>
            <label class="se_save_conacc_wr" for="chk_save_conacc">
              <input type="checkbox" name="save_conacc" id="chk_save_conacc" value="1">
              작성 된 특약사항 내 정보에 저장하기
            </label>
          </div>
          <!--<button type="button" id="btn_se_save" onclick="save_eform();">저장</button>-->
        </div>
        <div class="se_preview_wr">
          <div class="se_preview_hd_wr">
            <div class="se_preview_hd">공급계약서 미리보기</div>
            <button type="button" id="btn_zoom">확대 100%</button>
            <button type="button" id="btn_refresh" onclick="save_eform();">새로고침</button>
          </div>
          <div id="se_preview" class="se_preview">
            <?php if($dc) { ?>
            <iframe src="/shop/eform/renderEform.php?preview=1&dc_id=<?=$dc['uuid']?>" frameborder="0"></iframe>
            <?php } else { ?>
            <div class="empty">품목선택 시 생성됩니다.</div>
            <?php } ?>
          </div>
        </div>
      </div>
	  <div id="list_wrap" class="list_box"></div>
    </form>
  </div>
</section>

<div id="popup_box">
    <div class="popup_box_close">
        <i class="fa fa-times"></i>
    </div>
    <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<style>
#barcode_popup_iframe {
    display: none;
    position: fixed;
    width: 100%;
    height: 100%;
    right: 0;
    top: 0;
    z-index:9999;
}

</style>
<iframe name="barcode_popup_iframe" id="barcode_popup_iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
<script>
function sendBarcode(text){
    $('#barcode_popup_iframe')[0].contentWindow.sendBarcode(text);
}
</script>
<form name="barcode_popup_form" class="hidden" id="barcode_popup_form">
	<input type=text name="it_id" value="">
    <input type=text name="uid" value="">
    <input type=text name="option_name" value="">
	<input type=text name="barcodes" value="">
	<input type="button" name="button1" value="전 송">
</form>

<script>
var stock_table = [];
var pen = null;

//바코드입력 함수
$(document).on("click", "a.open_input_barcode", function(){
  var it_id = $(this).closest('.item').data('code');
  var barcode_nodes = $(this).closest('.item').find('.barcode_input');
  var barcodes = [];
  var is_mobile = navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1;

  var uid = $(this).closest('.item').data('uid');
  var option_name = '';

  $(barcode_nodes).each(function(i, item) {
    barcodes.push($(item).val());
  });

  window.name = "barcode_parent";
  var url = "./popup.order_barcode_form.php";
  var open_win;

  if(is_mobile) {
    $('#barcode_popup_iframe').show();
  } else {
    open_win = window.open("", "barcode_child", "width=683, height=800, resizable = no, scrollbars = no");
  }

  $('#barcode_popup_form').attr('target', is_mobile ? 'barcode_popup_iframe' : 'barcode_child');

  $('#barcode_popup_form').attr('action', url);
  $('#barcode_popup_form').attr('method', 'post');
  $('#barcode_popup_form input[name="it_id"]').val(it_id);
  $('#barcode_popup_form input[name="uid"]').val(uid);
  $('#barcode_popup_form input[name="option_name"]').val(option_name);
  $('#barcode_popup_form input[name="barcodes"]').val(barcodes.join('|'));
  $('#barcode_popup_form').submit();
});

$(document).on('change, click', '.penRecTypeCd_radio', function() {
  var val = $(this).val();

  $('#penRecTypeCd').val(val).change();
});

$(document).on('click', '.btn_del_eform', function(e) {
  e.preventDefault();

  if(!confirm('정말 삭제하시겠습니까?'))
    return;

  $.post('ajax.simple_eform.php', {
    w: 'd',
    dc_id: $(this).data('id')
  }, 'json')
  .done(function() {
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
});
</script>

<script>
// 품목 없는지 체크
function check_no_item() {
  var total = 0;
  $('.se_item_list').each(function() {
    var selected = $(this).find('li').length;
    if(selected == 0) {
      $(this).prev('.se_item_hd').hide();
    } else {
      $(this).prev('.se_item_hd').show();
    }
    total += selected;
  });

  if(total == 0) {
    $('.no_item_info').show();
    $('.se_item_list_hd').hide();
    $('.btn_se_submit').removeClass('active');
  } else {
    $('.no_item_info').hide();
    $('.se_item_list_hd').show();
    var dc_id = $('input[name="dc_id"]').val();
    if(dc_id)
      $('.btn_se_submit').addClass('active');
  }
}

// 품목 선택
function select_item(obj, qty) {

  if(!qty) qty = 1;

  $('body').removeClass('modal-open');
  $('#popup_box').hide();

  var $li = $('<li class="list item" data-code="' + obj.it_id + '" data-uid="' + Date.now().toString(36) + Math.random().toString(36).substr(2) + '">')
    .append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">')
    .append('<input type="hidden" name="it_gubun[]" value="' + obj.gubun + '">');
  
  var $it_info = $('<div class="it_info">')
    .append(
      '<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">',
      '<p class="it_cate">' + obj.ca_name + '</p>',
      '<p class="it_name">' + obj.it_name + ' (' + obj.gubun + ')' + '</p>'
      );
  if(obj.gubun == '대여') {
    $it_info.append('<p class="it_price">월 대여가 : ' + parseInt(obj.it_rental_price).toLocaleString('en-US') + '원</p>'); 
  } else {
    $it_info.append('<p class="it_price">급여가 : ' + parseInt(obj.it_cust_price).toLocaleString('en-US') + '원</p>'); 
  }
  $li.append($it_info);

  $li.append('\
    <div class="it_btn_wr flex align-items space-between">\
      <div class="it_qty">\
        <div class="input-group">\
        <div class="input-group-btn">\
          <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>\
        </div>\
        <input type="text" name="it_qty[]" value="1" class="form-control input-sm">\
        <div class="input-group-btn">\
          <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>\
        </div>\
        </div>\
      </div>\
      <button type="button" class="btn_del_item">삭제</button>\
    </div>\
  ');

  var $it_ipt = $('<div class="it_ipt_wr">');
  if(obj.gubun == '대여') {
    var id = obj.it_id + Date.now();
    $it_ipt.append('\
      <div class="flex">\
        <div class="it_ipt_hd">계약기간</div>\
        <div class="it_date_wr it_ipt">\
          <input type="hidden" name="it_date[]">\
          <input type="text" class="datepicker inline" data-range="from"> ~ <input type="text" class="datepicker inline" data-range="to">\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 6)">6개월</button>\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 12)">1년</button>\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 24)">2년</button>\
        </div>\
      </div>\
    ');

    set_rent_date($it_ipt, 6);
  } else {
    $it_ipt.append('\
      <div class="flex">\
        <div class="it_ipt_hd">판매계약일</div>\
        <div class="it_ipt">\
        <input type="text" name="it_date[]" class="datepicker inline" value="' + format_date(new Date()) + '">\
        </div>\
      </div>\
    ');
  }
  $it_ipt.find('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

  var barcode_html = '';
  for(var i = 0; i < qty; i++) {
    barcode_html += '<input type="hidden" class="it_barcode barcode_input" maxlength="12">';
  }

  $it_ipt.append('\
    <div class="flex">\
        <div class="it_ipt_hd">바코드</div>\
        <input type="hidden" name="it_barcode[]">\
        <div class="it_barcode_wr it_ipt">\
        <a class="prodBarNumCntBtn open_input_barcode">바코드 (0/' + qty + ')</a>\
        '+ barcode_html + '\
        <p>바코드 미입력 시 계약서 작성 후 이로움에 주문이 가능합니다.</p>\
        </div>\
    </div>\
  ');
  $li.append($it_ipt);

  if(obj.gubun == '대여') {
    $('#rent_list').append($li);
  } else {
    $('#buy_list').append($li);
  }

  $('#ipt_se_sch').val('').next().focus();

  get_stock_data(obj.it_id);
  check_no_item();
  save_eform();
}

// 바코드 최대길이 체크
function max_length_check(object){
  object.value = object.value.replace(/[^0-9]/g,'');
  if (object.value.length > object.maxLength) {
    object.value = object.value.slice(0, object.maxLength);
  }
}

function format_date(date) {
  var year = date.getFullYear();
  var month = date.getMonth() + 1;
  var day = date.getDate();

  month = (month < 10) ? "0" + month : month;
  day = (day < 10) ? "0" + day : day;

  return year + "-" + month + "-" + day;
}

function set_rent_date($parent, months) {
  var date = new Date();
  var from = format_date(date);

  date.setMonth(date.getMonth() + months);
  date.setDate(date.getDate() - 1);

  var to = format_date(date);

  $parent.find('input[data-range="from"]').val(from);
  $parent.find('input[data-range="to"]').val(to);
}

// 계약서 저장
var loading = false;
function save_eform() {
  if(loading) return;

  if($('.pen_id_flexdatalist').val() !== $('.pen_id_flexdatalist').next().val())
    $('.pen_id_flexdatalist').val($('.pen_id_flexdatalist').next().val());

  // 바코드 값 적용
  $('.it_barcode_wr').each(function() {
    var it_barcode = [];
    $(this).find('.it_barcode').each(function() {
      it_barcode.push($(this).val());
    });

    $(this).parent().find('input[name="it_barcode[]"]').val(it_barcode.join(String.fromCharCode(30)));
  });

  // 대여제품 계약기간 값 적용
  $('.it_date_wr').each(function() {
    var from = $(this).find('input[data-range="from"]').val();
    var to = $(this).find('input[data-range="to"]').val();

    if(from && to) {
      $(this).find('input[name="it_date[]"]').val(from + '-' + to);
    }
  });

  // 특약사항 값 적용
  var data = CKEDITOR.instances.entConAcc01.getData();
  $('#entConAcc01').val(data);

  loading = true;
  var $form = $('#form_simple_eform');
  var formdata = $form.serialize();
  if ($('#chk_se_seal_self').is(":checked")) {
    formdata += "&sealFile_self=true";
  }
  else {
    formdata += "&sealFile_self=false";
  }

  $.post('ajax.simple_eform.php', formdata, 'json')
    .done(function(result) {
      var dc_id = result.data;

      var w = $('input[name="w"]').val();
      if(w === 'w') {
        window.location.href = '/shop/electronic_manage.php?dc_id=' + dc_id;
      }

      $('input[name="w"]').val('u');
      $('input[name="dc_id"]').val(dc_id);

      var preview_url = '/shop/eform/renderEform.php?preview=1&dc_id=' + dc_id;
      $('#se_preview').empty().append($('<iframe>').attr('src', preview_url).attr('frameborder', 0));
      check_no_item();
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading = false;
    });
}

let pen_info = [];
// 계약서 작성
$('#btn_se_submit').on('click', function() {
  // if(!!pen_info){
  //     if(!(!!pen_info['penZip'])&&!(!!pen_info['penAddr'])) {
  //       if(confirm("수급자 정보가 완전하지 않습니다.\n수급자 정보 수정을 진행하시겠습니까?") == true){ // 등록되지 않은 수급자이기 때문에 보호자 정보 없음
  //         window.location.href = './my_recipient_update.php?id='+pen_info['penId'];
  //         return false;
  //       } else {
  //         alert("수급자 선택을 초기화합니다.");
  //         window.location.href = './simple_eform.php';
  //         return false;
  //       }
  //     }
  // }

  if(loading) {
    alert('계약서 저장 중입니다. 잠시 기다려주세요.');
    return false;
  }

  var pen_type = $('input[name="pen_type"]:checked').val();
  var dc_id = $('input[name="dc_id"]').val();

  if(!dc_id)
      return alert('먼저 품목 선택 후 저장을 해주세요.');

  var contract_type = $('input[name="contract_sign_type"]:checked').val();
  if (contract_type == 1) {
    if ($('#contract_sign_name').val().length == 0) {
      return alert('대리인 이름을 입력하세요.');
    }
  }
  
  if($('.pen_id_flexdatalist').val() !== $('.pen_id_flexdatalist').next().val())
    $('.pen_id_flexdatalist').val($('.pen_id_flexdatalist').next().val());
  
  var changed = false;
  if(pen_type == 1) {
    $('.panel .form-group input, .panel .form-group select').each(function() {
      if($(this).attr('id') != 'penNm-flexdatalist') {
        if( $(this).val() != $(this).data('orig') && $(this).val() ) {
          changed = true;
        }
      }
    });
  }

  if(changed && confirm('변경된 수급자 정보가 있습니다. 변경된 정보로 수급자 정보를 변경하시겠습니까? ')) {
    $.post('ajax.recipient.update.php', {
      penId: $('#penId').val(),
      penNm: $('#penNm').val(),
      penConNum: $('#penConNum').val(),
      penRecGraCd: $('#penRecGraCd').val(),
      penTypeCd: $('#penTypeCd').val(),
      penExpiStDtm: $('#penExpiStDtm').val(),
      penExpiEdDtm: $('#penExpiEdDtm').val(),
      penJumin: $('#penJumin').val()
    }, 'json')
    .done(function() {
      $('input[name="w"]').val('w');
      save_eform();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
  } else {
    $('input[name="w"]').val('w');
    save_eform();
  }
});

// 바코드 필드 개수 업데이트
function update_barcode_field() {
  $('#buy_list').each(function() {
    $(this).find('li').each(function(key) {
      var it_id = $(this).find('input[name="it_id[]"]').val();

      // 상품 개수
      var it_qty = parseInt($(this).find('input[name="it_qty[]"]').val());

      // 재고 정보
      var stock = stock_table[it_id];
      var sel_type = '0';
      var sel_count = 1;
      
      if(stock && stock.length > 0) {
        if($(this).find('.sel_barcode_wr').length == 0) {
          $('<div class="sel_barcode_wr">').insertBefore($(this).find('.prodBarNumCntBtn'));
        }
        var $sel_barcode_wr = $(this).find('.sel_barcode_wr');

        if($sel_barcode_wr.find('input[type="radio"]:checked').val() > 0) {
          sel_type = $sel_barcode_wr.find('input[type="radio"]:checked').val();
        }

        if($sel_barcode_wr.find('.sel_stock_count').val() > 1) {
          sel_count = parseInt($sel_barcode_wr.find('.sel_stock_count').val());
        }
        if(sel_count > it_qty)
          sel_count = it_qty;

        $sel_barcode_wr.html('<p>해당 상품은 보유재고가 ' + stock.length + '개 있습니다.</p>');
        $sel_barcode_wr.append('\
          <label class="radio-inline">\
            <input type="radio" name="barcode_' + key + '_type" value="0"' + (sel_type == '0' ? ' checked' : '') + '> 직접입력\
          </label>\
          <label class="radio-inline">\
            <input type="radio" name="barcode_' + key + '_type" value="1"' + (sel_type == '1' ? ' checked' : '') + '> 보유재고선택\
          </label>\
        ');
        var $sel_stock_count = $('<select class="sel_stock_count">');
        for(var i = 1; i <= (it_qty > stock.length ? stock.length : it_qty); i++) {
          $sel_stock_count.append('<option value="' + i + '"' + (i == sel_count ? ' selected' : '') + '>' + i + '개</option>');
        }
        $sel_barcode_wr.append($sel_stock_count);
      }

      // 먼저 기존에 입력된 바코드값 저장
      var barcodes = [];
      var $barcode = $(this).find('.it_barcode');
      $barcode.each(function() {
        barcodes.push($(this).val() || '');
      });

      var $barcode_wr = $(this).find('.it_barcode_wr');
      $barcode_wr.find('.it_barcode').remove();
      var inserted_count = 0;
      var barcode_count = sel_type == '1' ? it_qty - sel_count : it_qty;

      if(barcode_count == 0)
        $barcode_wr.find('.prodBarNumCntBtn').hide();
      else
        $barcode_wr.find('.prodBarNumCntBtn').show();
      
      if(sel_type == '1') {
        for(var i = 0; i < sel_count; i++) {
          var selected = '';
          for(var x = 0; x < barcodes.length; x++) {
            var barcode = '';
            for(var y = 0; y < stock.length; y++) {
              if(stock[y]['prodBarNum'] == barcodes[x]) {
                barcode = barcodes[x];
                break;
              }
            }
            if(barcode != '') {
              barcodes.splice(x, 1);
              selected = barcode;
              break;
            }
          }

          var $sel_barcode = $('<select class="it_barcode">');
          $sel_barcode.append('<option value="">바코드 선택</option>');
          for(var s = 0; s < stock.length; s++) {
            $sel_barcode.append('<option value="' + stock[s]['prodBarNum'] + '"' + (selected == stock[s]['prodBarNum'] ? ' selected' : '') + '>' + stock[s]['prodBarNum'] + '</option>')
          }
          $barcode_wr.find('.prodBarNumCntBtn').before($sel_barcode);
        }
      }

      for(var i = 0; i < barcode_count; i++) {
        var val = barcodes.shift() || '';
        $barcode_wr.append('<input type="hidden" class="it_barcode barcode_input" maxlength="12" value="' + val + '">');
        if(val != '') {
          inserted_count++;
        }
      }
      $barcode_wr.find('.prodBarNumCntBtn').text('바코드 (' + inserted_count + '/' + barcode_count + ')');
    });
  });
  $('#rent_list').each(function() {
    $(this).find('li').each(function(key) {
      var it_id = $(this).find('input[name="it_id[]"]').val();

      // 상품 개수
      var it_qty = parseInt($(this).find('input[name="it_qty[]"]').val());

      // 재고 정보
      var stock = stock_table[it_id];
      var sel_type = '0';
      var sel_count = 1;
      
      if(stock && stock.length > 0) {
        if($(this).find('.sel_barcode_wr').length == 0) {
          $('<div class="sel_barcode_wr">').insertBefore($(this).find('.prodBarNumCntBtn'));
        }
        var $sel_barcode_wr = $(this).find('.sel_barcode_wr');

        if($sel_barcode_wr.find('input[type="radio"]:checked').val() > 0) {
          sel_type = $sel_barcode_wr.find('input[type="radio"]:checked').val();
        }

        if($sel_barcode_wr.find('.sel_stock_count').val() > 1) {
          sel_count = parseInt($sel_barcode_wr.find('.sel_stock_count').val());
        }
        if(sel_count > it_qty)
          sel_count = it_qty;

        $sel_barcode_wr.html('<p>해당 상품은 보유재고가 ' + stock.length + '개 있습니다.</p>');
        $sel_barcode_wr.append('\
          <label class="radio-inline">\
            <input type="radio" name="barcode_' + key + '000' + '_type" value="0"' + (sel_type == '0' ? ' checked' : '') + '> 직접입력\
          </label>\
          <label class="radio-inline">\
            <input type="radio" name="barcode_' + key + '000' + '_type" value="1"' + (sel_type == '1' ? ' checked' : '') + '> 보유재고선택\
          </label>\
        ');
        var $sel_stock_count = $('<select class="sel_stock_count">');
        for(var i = 1; i <= (it_qty > stock.length ? stock.length : it_qty); i++) {
          $sel_stock_count.append('<option value="' + i + '"' + (i == sel_count ? ' selected' : '') + '>' + i + '개</option>');
        }
        $sel_barcode_wr.append($sel_stock_count);
      }

      // 먼저 기존에 입력된 바코드값 저장
      var barcodes = [];
      var $barcode = $(this).find('.it_barcode');
      $barcode.each(function() {
        barcodes.push($(this).val() || '');
      });

      var $barcode_wr = $(this).find('.it_barcode_wr');
      $barcode_wr.find('.it_barcode').remove();
      var inserted_count = 0;
      var barcode_count = sel_type == '1' ? it_qty - sel_count : it_qty;

      if(barcode_count == 0)
        $barcode_wr.find('.prodBarNumCntBtn').hide();
      else
        $barcode_wr.find('.prodBarNumCntBtn').show();
      
      if(sel_type == '1') {
        for(var i = 0; i < sel_count; i++) {
          var selected = '';
          for(var x = 0; x < barcodes.length; x++) {
            var barcode = '';
            for(var y = 0; y < stock.length; y++) {
              if(stock[y]['prodBarNum'] == barcodes[x]) {
                barcode = barcodes[x];
                break;
              }
            }
            if(barcode != '') {
              barcodes.splice(x, 1);
              selected = barcode;
              break;
            }
          }

          var $sel_barcode = $('<select class="it_barcode">');
          $sel_barcode.append('<option value="">바코드 선택</option>');
          for(var s = 0; s < stock.length; s++) {
            $sel_barcode.append('<option value="' + stock[s]['prodBarNum'] + '"' + (selected == stock[s]['prodBarNum'] ? ' selected' : '') + '>' + stock[s]['prodBarNum'] + '</option>')
          }
          $barcode_wr.find('.prodBarNumCntBtn').before($sel_barcode);
        }
      }

      for(var i = 0; i < barcode_count; i++) {
        var val = barcodes.shift() || '';
        $barcode_wr.append('<input type="hidden" class="it_barcode barcode_input" maxlength="12" value="' + val + '">');
        if(val != '') {
          inserted_count++;
        }
      }
      $barcode_wr.find('.prodBarNumCntBtn').text('바코드 (' + inserted_count + '/' + barcode_count + ')');
    });
  });
}

// datepicker
// $('.birthpicker').datepicker({ changeMonth: true, changeYear: true, yearRange: 'c-120:c+0', maxDate: '+0d', dateFormat: 'yy.mm.dd' });
$('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

// 수급자 검색
var pen_id_flexdata = null;
function toggle_pen_id_flexdatalist(on) {
  if(on) {
    if(pen_id_flexdata) return;
    pen_id_flexdata = $('.pen_id_flexdatalist').flexdatalist({
      minLength: 1,
      url: 'ajax.get_pen_id.php',
      cache: false, // cache
      searchContain: true, // %검색어%
      noResultsText: '"{keyword}"으로 등록된 수급자가 없습니다. 수급자정보를 직접 입력 하시고 계약서 작성 시 자동으로 등록됩니다.',
      visibleCallback: function($li, item, options) {
        var $item = {};
        $item = $('<span>')
          .html(item.penNm);

        $item.appendTo($li);

        $item = $('<span>')
          .html(" (" + ( item.penAge > 0 ? item.penAge + '/' : '' ) + ( item.penGender ? item.penGender + '/' : '' ) + ( item.penLtmNum ? item.penLtmNum : '' ) + ")");

        $item.appendTo($li);

        return $li;
      },
      searchIn: ["penNm"],
      focusFirstResult: true,
    })
    .on("select:flexdatalist", function(event, obj, options) {
      select_recipient(obj);
      $('#penNm-flexdatalist').change();
    });
  } else {
    if(!pen_id_flexdata) return;
    $('.pen_id_flexdatalist').flexdatalist('destroy');
    pen_id_flexdata = null;
  }
}
// 수급자 선택
function select_recipient(obj) {
  pen_info = obj;
  update_pen(obj);

  // 본인부담금율이 '기초0%' 혹은 '의료6%'인 경우
  if($('#penTypeCd').val() == '03' || $('#penTypeCd').val() == '04'){

    // 보호자 정보를 가져와서 장기요양입소이용신청서 신청인을 선택 할 수 있도록 한다.
    $.post('ajax.recipient.get_pros.php', {
        penId: obj['penId'],
        page: "eform"
      }, 'json')
      .done(function(result) {
        $('#select_applicantCd').empty();
        var prosArr = result.data;
        var select = document.getElementById('select_applicantCd');
        if(prosArr.length == 0){
            var row = `<select name="applicantCd" id="applicantCd" class="form-control input-sm">
                        <option value="00" <?php if($dc) echo get_selected($dc['applicantCd'], '00'); ?>>본인</option>
                        <option value="02" <?php if($dc) echo get_selected($dc['applicantCd'], '02'); ?>>공란</option>
                      </select>`;
            select.innerHTML += row;
        } else {
            var row = `<select name="applicantCd" id="applicantCd" class="form-control input-sm">
                        <option value="00" <?php if($dc) echo get_selected($dc['applicantCd'], '00'); ?>>본인</option>`;
            for(var i =0; i < prosArr.length; i++){
                //전체 보호자를 불러와서 가장 첫번째 보호자 출력(모든 정보가 입력되어 있지 않으면 다음 보호자 출력, 모든 보호자의 정보가 완전하지 않으면 출력하지 않음)
                if(!!prosArr[i]['pro_name']&&!!prosArr[i]['pro_hp']&&!!prosArr[i]['pro_addr1']&&!!prosArr[i]['pro_zip']&&!!prosArr[i]['pro_birth']&&!!prosArr[i]['pro_name']){
                    if(prosArr[i]['pro_type']!='02'){ //요양보호사는 건너뛴다다
                       var applicant_cd_name = "<?=explode('_',$dc['applicantCd'])[0];?>";
                       var selected_value = applicant_cd_name == prosArr[i]['pro_name'] ? ' selected="selected"':"";
                        row += `
                            <option value="${prosArr[i]['pro_name']}_${prosArr[i]['pro_birth']}"${selected_value}>보호자(${prosArr[i]['pro_name']})</option>`;
                        // break; // break 하면 조건에 맞는 가장 최초의 보호자만 출력
                    }
                }
            }
            row += `
                        <option value="02" <?php if($dc) echo get_selected($dc['applicantCd'], '02'); ?>>공란</option>
                    </select>`;
            select.innerHTML += row;
        }
        console.log("result_pros : ",result.data);
      })
      .fail(function ($xhr) {
				//등록되지 않은 신규 수급자이면 진행여부를 한번 더 묻는다
        if(confirm("등록되지 않은 수급자입니다. 계속 진행하시겠습니까?") == true){ // 등록되지 않은 수급자이기 때문에 보호자 정보 없음
            var row = `<select name="applicantCd" id="applicantCd" class="form-control input-sm">
                        <option value="00" <?php if($dc) echo get_selected($dc['applicantCd'], '00'); ?>>본인</option>
                        <option value="02" <?php if($dc) echo get_selected($dc['applicantCd'], '02'); ?>>공란</option>
                      </select>`;
            select.innerHTML += row;
        }
        else{
           alert("수급자 관리 페이지로 이동합니다.");
           window.location.href = './my_recipient_list.php';
        }
      });

      $('#applicantCd').show();
      $('#applicantCd_subj').show();
  }

  obj['applicantCd'] = $('#applicantCd').val();

  $('.panel-body .form-group').show();
  $('#penLtmNum').prop('disabled', false).prop('readonly', true);
  $('#penConNum').prop('disabled', false);
  $('#penRecGraCd').prop('disabled', false);
  $('#penTypeCd').prop('disabled', false);
  //$('#penBirth').val(obj.penBirth).prop('disabled', false);
  $('#penExpiStDtm').prop('disabled', false);
  $('#penExpiEdDtm').prop('disabled', false);
  $('#penJumin').prop('disabled', false);
  $('#se_body_wr').show();
  $('#se_body_wr').addClass('active');

  $('#list_wrap').hide();
}

function update_pen(obj) {
  obj = obj || null;
  pen = obj;

  if(pen == null) {
    $('#penId').val('').data('orig', '').change();
    $('#penZip').val('').data('orig', '').change();
    $('#penAddr').val('').data('orig', '').change();
    $('#penAddrDtl').val('').data('orig', '').change();
    $('#penNm').val('').data('orig', '').change();
    $('#penLtmNum').val('').data('orig', '').change();
    $('#penConNum').val('').data('orig', '').change();
    $('#penRecGraCd').val('').data('orig', '').change();
    $('#penTypeCd').val('').data('orig', '').change();
    $('#applicantCd').val('').data('orig', '').change(); // 장기요양입소이용신청서를 위해 추가
    //$('#penBirth').val(obj.penBirth).prop('disabled', false);
    $('#penExpiStDtm').val('').data('orig', '').change();
    $('#penExpiEdDtm').val('').data('orig', '').change();
    $('#penJumin').val('').data('orig', '').change();
    $('#penExpiEdDtm').val('').data('orig', '').change();
    $('#penRecTypeCd').val('02').data('orig', '02').change();
    $('#penRecTypeTxt').val('');
  } else {
    $('#penId').val(obj.penId).data('orig', obj.penId).change();
    $('#penZip').val(obj.penZip).data('orig', obj.penZip).change();
    $('#penAddr').val(obj.penAddr).data('orig', obj.penAddr).change();
    $('#penAddrDtl').val(obj.penAddrDtl).data('orig', obj.penAddrDtl).change();
    $('#penNm').val(obj.penNm).data('orig', obj.penNm).change();
    var penLtmNum = obj.penLtmNumRaw.replace(/L([0-9]{10})/, '$1');
    $('#penLtmNum').val(penLtmNum).data('orig', penLtmNum).change();
    $('#penConNum').val(obj.penConNum).data('orig', obj.penConNum).change();
    var penRecGraCd = obj.penRecGraCd ? obj.penRecGraCd : '00';
    $('#penRecGraCd').val(penRecGraCd).data('orig', penRecGraCd).change();
    var penTypeCd = obj.penTypeCd ? obj.penTypeCd : '00';
    $('#penTypeCd').val(penTypeCd).data('orig', penTypeCd).change();
    var applicantCd = obj.applicantCd ? obj.applicantCd : '00'; // 장기요양입소이용신청서를 위해 추가
    $('#applicantCd').val(applicantCd).data('orig', applicantCd).change(); // 장기요양입소이용신청서를 위해 추가
    //$('#penBirth').val(obj.penBirth).prop('disabled', false);
    $('#penExpiStDtm').val(obj.penExpiStDtm).data('orig', obj.penExpiStDtm).change();
    $('#penExpiEdDtm').val(obj.penExpiEdDtm).data('orig', obj.penExpiEdDtm).change();
    $('#penJumin').val(obj.penJumin).data('orig', obj.penJumin).change();
    $('#penRecTypeCd').val(obj.penRecTypeCd).data('orig', obj.penRecTypeCd).change(); // 값을 들고오지 않음 -> ajax.get_pen_id.php 에서 추가
    if(obj.penRecTypeCd == '01') {
      $('input[name="penRecTypeCd_radio"]').val(1).prop('checked', true);
      $('input[name="penRecTypeCd_radio"]').val(1).prop('checked', false);
    } else {
      $('input[name="penRecTypeCd_radio"]').val(1).prop('checked', false);
      $('input[name="penRecTypeCd_radio"]').val(1).prop('checked', true);
    }
    if(obj.penRecTypeTxt) $('#penRecTypeTxt').val(obj.penRecTypeTxt); // 값을 들고오지 않음 -> ajax.get_pen_id.php 에서 추가
  }
}

// 수급자 목록
$('#btn_pen').click(function() {
  var url = 'pop_recipient.php';

  $('#popup_box iframe').attr('src', url);
  $('body').addClass('modal-open');
  $('#popup_box').show();
});
function selected_recipient(result) {

  $('body').removeClass('modal-open');
  $('#popup_box').hide();

  result = result.split('|');

  var penExpiDtm = result[11].split(' ~ ');
  var penExpiStDtm = penExpiDtm[0] ? penExpiDtm[0] : '';
  var penExpiEdDtm = penExpiDtm[1] ? penExpiDtm[1] : '';

  var pen = {
    penId: result[1],
    penNm: result[3],
    penLtmNumRaw: result[4],
    penConNum: result[20],
    penRecGraCd: result[5],
    penTypeCd: result[7],
    penExpiStDtm: penExpiStDtm,
    penExpiEdDtm: penExpiEdDtm,
    penJumin: result[33].substring(0, 6),
    penZip: result[26],
    penAddr: result[18],
    penAddrDtl: result[19],
    penRecTypeCd: result[34],
    penRecTypeTxt: result[35],
  };

  select_recipient(pen);
}

// 품목찾기
$('#popup_box').click(function() {
  $('body').removeClass('modal-open');
  $('#popup_box').hide();
});
$('#btn_se_sch').click(function() {
  var url = 'pop.item.select.php?no_option=nonReimbursement';

  $('#popup_box iframe').attr('src', url);
  $('body').addClass('modal-open');
  $('#popup_box').show();
});

// 품목 검색
$('#ipt_se_sch').flexdatalist({
  minLength: 1,
  url: 'ajax.get_item.php?eform=1',
  cache: false, // cache
  searchContain: true, // %검색어%
  noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
  selectionRequired: true,
  focusFirstResult: true,
  searchIn: ["it_name","it_model","it_id", "it_name_no_space"],
  visibleCallback: function($li, item, options) {
    var $item = {};
    $item = $('<span>')
      .html("[" + item.gubun + "] " + item.it_name + " (" + number_format(item.it_cust_price) + "원)");

    $item.appendTo($li);
    return $li;
  },
}).on("select:flexdatalist", function(event, obj, options) {
  select_item(obj);
});

// 상품수량변경
$(document).on('click', '.it_qty button', function() {
  var mode = $(this).text();
  var this_qty;
  var $it_qty = $(this).closest('.it_qty').find('input[name="it_qty[]"]');

  switch(mode) {
    case '증가':
      this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) + 1;
      $it_qty.val(this_qty);
      break;
    case '감소':
      this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) - 1;
      if(this_qty < 1) this_qty = 1
      $it_qty.val(this_qty);
      break;
  }
  update_barcode_field();
});

$(document).on('change paste keyup', 'input[name="it_qty[]"]', function() {
  var val = parseInt($(this).val());
  if( isNaN(val) == false ) {

    if( val < 1 )
      $(this).val(1);

    update_barcode_field();

  } else {

      if ( $(this).val().replace(/[0-9]/g, '').length > 0 ) {
          alert('수량은 숫자만 입력해 주십시오.');
          $(this).val( 1 );
      }
      else {
          alert('수량이 입력되지 않았습니다.');
          $(this).val( 1 );
      }
  }


});

// 품목 삭제
$(document).on('click', '.btn_del_item', function() {
  $(this).closest('li').remove();
  check_no_item();
  save_eform();
});

// 상품검색 팝업
$(document).on('focus', '.ipt_se_sch', function() {
  $('.se_sch_pop').show();
});
$(document).on('click', function(e) {
  if($(e.target).closest('.se_sch_wr').length > 0) 
    return;

  $('.se_sch_pop').hide();
});

// 직인 업로드
var loading_seal = false;
$('.btn_se_seal').click(function() {
  var $form = $(this).closest('form');

  $form.find('input[name="sealFile"]').remove();
  $('<input type="file" name="sealFile" accept=".gif, .jpg, .png" style="width: 0; height: 0; overflow: hidden;">').appendTo($form).click();
});
$(document).on('change', 'input[name="sealFile"]', function(e) {
  var $form = $(this).closest('form');

  if(loading_seal)
    return alert('직인 이미지를 업로드 중입니다.');
  
  loading_seal = true;

  var formData = new FormData();
  formData.append('sealFile', this.files[0]);

  $.ajax({
    type: 'POST',
    url: $form.attr('action'),
    processData: false,
    contentType: false,
    data: formData,
    dataType: 'json'
  })
  .done(function() {
    alert('직인 이미지를 업로드했습니다.');
    $('.se_seal_wr').remove();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  })
  .always(function() {
    loading_seal = false;
  });
});

// 신규수급자 or 기존수급자 선택
function check_pen_type() {
  var pen_type = $('input[name="pen_type"]:checked').val();

  if(pen_type == '1') {
    // 기존수급자
    $('.pen_id_flexdatalist').addClass('active').attr('placeholder', '수급자명 검색');
    $('.col-pen-nm img').show();
    $("#btn_pen").show();
    //$('.panel-body .form-group').hide();
    $('#penLtmNum').prop('disabled', true);
    $('#penConNum').prop('disabled', true);
    $('#penRecGraCd').prop('disabled', true);
    $('#penTypeCd').prop('disabled', true);
    //$('#penBirth').val('').prop('disabled', true);
    $('#penExpiStDtm').prop('disabled', true);
    $('#penExpiEdDtm').prop('disabled', true);
    $('#penJumin').prop('disabled', true);
    toggle_pen_id_flexdatalist(true);
  } else {
    // 신규수급자
    $('.pen_id_flexdatalist').removeClass('active').attr('placeholder', '수급자명');;
    $('.col-pen-nm img').hide();
    $("#btn_pen").hide();
    $('.panel-body .form-group').show();
    $('#penLtmNum').val('').prop('disabled', false).prop('readonly', false);
    $('#penConNum').prop('disabled', false);
    $('#penRecGraCd').prop('disabled', false);
    $('#penTypeCd').prop('disabled', false).change();
    //$('#penBirth').prop('disabled', false);
    $('#penExpiStDtm').prop('disabled', false);
    $('#penExpiEdDtm').prop('disabled', false);
    $('#penJumin').prop('disabled', false);
    toggle_pen_id_flexdatalist(false);
  }
}
$('input[name="pen_type"]').change(function() {
  update_pen(null);
  check_pen_type();
});

//계약서 날인
$('input[name="contract_sign_type"]').change(function() {
  var type = $('input[name="contract_sign_type"]:checked').val();

  if (type == 0) {
    //수급자
    $('#contract_sign_name').prop('disabled', true);
    $('#contract_sign_relation').prop('disabled', true);
  }
  else {
    //대리인
    $('#contract_sign_name').prop('disabled', false);
    $('#contract_sign_relation').prop('disabled', false);
  }
});

// 요양인정번호 입력
var penLtmNum_timer = null;
$('#penLtmNum').on('change paste keyup input', function() {
  if(penLtmNum_timer) clearTimeout(penLtmNum_timer);

  var $this = $(this);
  var penLtmNum = $(this).val();

  penLtmNum = penLtmNum.substring(0, 10);
  $this.val(penLtmNum);

  penLtmNum_timer = setTimeout(function() {
    var pattern = /[0-9]{10}/;

    if(pattern.test(penLtmNum)) {
      check_recipient();
      $('#se_body_wr').show();
	  $('#se_body_wr').addClass('active');
      // 처음 팝업
      $('.se_sch_pop').show();
      check_no_item();
    }
  }, 300);
});

// 확대/축소
var zoom_step = 0;
$('#btn_zoom').click(function() {
  switch(zoom_step) {
    case 0:
      $('#btn_zoom').text('확대 150%');
      break;
    case 1:
      $('#btn_zoom').text('확대 200%');
      break;
    case 2:
      $('#btn_zoom').text('확대 100%');
      break;
  }
  try {
    $('#se_preview').find('iframe')[0].contentWindow.zoomStep(zoom_step);
    zoom_step = (zoom_step + 1) % 3;
  } catch(ex) {
    // do nothing;
    console.log(ex);
  }
});

function check_recipient() {
  var pen_type = $('input[name="pen_type"]:checked').val();
  if(pen_type == '1') {
    // 기존 수급자
    return;
  }

  var pen_ltm_num = $('#penLtmNum').val();
  if(pen_ltm_num)
    pen_ltm_num = 'L' + pen_ltm_num;

  $.post('ajax.get_recipient.php', {
    pen_ltm_num: pen_ltm_num
  }, 'json')
  .done(function(result) {
    var data = result.data;
    var penExpiDtm = data['penExpiDtm'].split(' ~ ');
    data['penLtmNumRaw'] = pen_ltm_num;
    data['penExpiStDtm'] = penExpiDtm[0] ? penExpiDtm[0] : '';
    data['penExpiEdDtm'] = penExpiDtm[1] ? penExpiDtm[1] : '';
    alert(data['penNm'] + '(' + pen_ltm_num + ')로 등록된 수급자가 있습니다.');
    $('#pen_type_1').prop('checked', true).change();
    select_recipient(data);
  })
}

// 재고 조회
function get_stock_data(it_id) {
  if(stock_table[it_id]) {
    update_barcode_field();
    return;
  }

  $.post('ajax.stock.get.php', {
    it_id: it_id
  }, 'json')
  .done(function(result) {
    var data = result.data;
    stock_table[it_id] = data;
    update_barcode_field();
  });
}

// 재고 선택
$(document).on('change', '.sel_barcode_wr input[type="radio"], .sel_barcode_wr .sel_stock_count', function() {
  update_barcode_field();
});

// 핸드폰 번호 입력창 선택시 - 지우기
$('#penConNum').on('focus', function() {
  var $this = $(this);
  var ms_pen_hp = $(this).val();
  $(this).val(ms_pen_hp.replace(/-/g, ''));
});

// 핸드폰 번호 입력창 포커스 아웃시 10자리 되면 번호에 - 넣고 상품입력창 보여주기
$('#penConNum').on('blur', function() {
  var $this = $(this);
  var ms_pen_hp = $(this).val();
  $(this).val(ms_pen_hp.replace(/-/g, ''));
  ms_pen_hp = $(this).val();

  if (ms_pen_hp.length > 9) {
    check_pen_input(ms_pen_hp);
  }
  save_eform(); /* 수급자 연락처 미입력으로 인한 사후 연락처입력 후 포커스 아웃 시 계약서 새로고침 */
});

// 핸드폰 번호 입력 체크
$('#penConNum').on('change paste keyup input', function() {
  var $this = $(this);
  var penConNum = $(this).val();
  $(this).val(penConNum.replace(/-/g, ''));
  penConNum = $(this).val();
  if (penConNum.length > 10) {
    check_pen_input(penConNum);
  }
});

function check_pen_input(penConNum) {
  var hp_pattern = /01[016789]-[^0][0-9]{2,3}-[0-9]{3,4}/;
  penConNum = penConNum.replace(/[^0-9]/g, '').replace(/(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/, "$1-$2-$3");
  $('#penConNum').val(penConNum);
}

// 수급자 정보 변경되었는지 체크
var first_completed = true;
$(document).on('input change keyup paste', '.panel .form-group input, .panel .form-group select', function() {
  var pen_type = $('input[name="pen_type"]:checked').val();

  if(pen_type == 1 && ( $(this).val() != $(this).data('orig') && $(this).val() )) {
    $(this).css('border', '1px solid #ef8505');
  } else {
    $(this).css('border', '1px solid #ccc');
  }

  if($(this).attr('id') == 'penNm-flexdatalist') {
    if($(this).val() != $('#penNm').data('orig') && $(this).val()) {
      $(this).css('border', '1px solid #ef8505');
    } else {
      $(this).css('border', '1px solid #ccc');
    }
  }

  <?php if($ms_id && !$ms) { ?>
    if(!first_completed && check_input_completed()) {
      $('#btn_se_submit').addClass('active');
      first_completed = true;
      save_eform();
    }
  <?php } ?>
});

// 입력 필드 전부 입력되었는지 체크
function check_input_completed() {

  // 수급자명
  if($('#penNm').val() == '')
    return false;
  
  // 요양인정번호
  if(!$('#penLtmNum').val().match(/^[0-9]{10}/))
    return false;
  
  // 휴대폰번호
  if(!$('#penConNum').val().match(/^[0-9]{3}-[0-9]{3,4}-[0-9]{4}/))
    return false;
  
  // 인정등급
  if(!$('#penRecGraCd').val().match(/^0[0-5]/))
    return false;
  
  // 본인부담율
  if(!$('#penTypeCd').val().match(/^0[0-4]/))
    return false;
  
  // 유효기간 (시작일)
  if(!$('#penExpiStDtm').val().match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}/))
    return false;
  
  // 유효기간 (종료일)
  if(!$('#penExpiEdDtm').val().match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}/))
    return false;
  
  // 주민번호(앞자리)
  if(!$('#penJumin').val().match(/^[0-9]{6}/))
    return false;
  
  return true;
}

// 보유재고관리에서 넘어온 경우 상품 바코드 선택
function select_barcode(barcode) {
  setTimeout(function() {
	  $('input:radio[name="barcode_0_type"]:radio[value="1"]').prop('checked', true);
	  $('.it_barcode').val(barcode).prop("selected", true);
	  update_barcode_field();
  }, 2000);
}

if($('input[name="pen_type"]:checked').val() == 1) {
  toggle_pen_id_flexdatalist(true);
  $('.pen_id_flexdatalist').addClass('active').attr('placeholder', '수급자명 검색');
  $('.col-pen-nm img').show();
} else {
  $('.pen_id_flexdatalist').removeClass('active').attr('placeholder', '수급자명');
  $('.col-pen-nm img').hide();
  $('#btn_pen').hide();
}
check_no_item();

// 처음 팝업
$('.se_sch_pop').show();

CKEDITOR.replace( 'entConAcc01', {
  removePlugins: 'link'
});

<?php
if(!$dc) {
  echo 'check_pen_type();';
}

$ms_id = get_search_string($_GET['ms_id']);
if($ms_id) {
  $ms = sql_fetch(" select * from recipient_item_msg where ms_id = '$ms_id' ");
  if($ms) {
    if($ms['ms_pen_id']) {
      $pen = get_recipient($ms['ms_pen_id']);
      $pen['penLtmNumRaw'] = $pen['penLtmNum'];
      $penExpiDtm = explode(' ~ ', $pen['penExpiDtm']);
      $pen['penExpiStDtm'] = $penExpiDtm[0] ?: '';
      $pen['penExpiEdDtm'] = $penExpiDtm[1] ?: '';
      $pen['penJumin'] = substr($pen['penJumin'], 0, 6);

      echo 'select_recipient(' . json_encode($pen) . ');'.PHP_EOL;
    } else {
      echo "$('#pen_type_0').prop('checked', true);".PHP_EOL;
      echo 'check_pen_type();'.PHP_EOL;
      echo "$('#penNm').val('{$ms['ms_pen_nm']}');".PHP_EOL;
      echo "$('#penConNum').val('{$ms['ms_pen_hp']}');".PHP_EOL;
    }

    $result = sql_query(" select * from recipient_item_msg_item where ms_id = '$ms_id' ");
    while($row = sql_fetch_array($result)) {
      $it = sql_fetch("
        SELECT
          it_id,
          it_name,
          it_model,
          it_price,
          it_price_dealer2,
          it_cust_price,
          it_rental_price,
          ca_id,
          ( select ca_name from g5_shop_category where ca_id = left(a.ca_id, 4) ) as ca_name,
          it_img1 as it_img,
          it_delivery_cnt,
          it_sc_type,
          it_sale_cnt,
          it_sale_cnt_02,
          it_sale_cnt_03,
          it_sale_cnt_04,
          it_sale_cnt_05,
          it_sale_percent,
          it_sale_percent_02,
          it_sale_percent_03,
          it_sale_percent_04,
          it_sale_percent_05,
          it_sale_percent_great,
          it_sale_percent_great_02,
          it_sale_percent_great_03,
          it_sale_percent_great_04,
          it_sale_percent_great_05,
          it_type1,
          it_type2,
          it_type3,
          it_type4,
          it_type5,
          it_type6,
          it_type7,
          it_type8,
          it_type9,
          it_type10,
          it_expected_warehousing_date
        FROM
          {$g5['g5_shop_item_table']} a
        WHERE
          a.it_id = '{$row['it_id']}'
      ");

      $gubun = $cate_gubun_table[substr($it["ca_id"], 0, 2)];
      $gubun_text = '판매';
      if($gubun == '01') $gubun_text = '대여';
      else if($gubun == '02') $gubun_text = '비급여';
    
      $it['gubun'] = $gubun_text;

      echo 'select_item(' . json_encode($it) . ');'.PHP_EOL;
    }

    echo 'first_completed = false;'.PHP_EOL;
  }
}

//보유재고관리에서 넘어온 경우
if($_POST['penId_r']){
  $penId = explode('|', $_POST['penId_r'])[1];
  $pen = get_recipient($penId);
  $pen['penLtmNumRaw'] = $pen['penLtmNum'];
  $penExpiDtm = explode(' ~ ', $pen['penExpiDtm']);
  $pen['penExpiStDtm'] = $penExpiDtm[0] ?: '';
  $pen['penExpiEdDtm'] = $penExpiDtm[1] ?: '';
  $pen['penJumin'] = substr($pen['penJumin'], 0, 6);

  echo 'select_recipient(' . json_encode($pen) . ');'.PHP_EOL;

  if ($_POST['it_id']) {
    $it_id = $_POST['it_id'][0];

    $it = sql_fetch("
      SELECT
        it_id,
        it_name,
        it_model,
        it_price,
        it_price_dealer2,
        it_cust_price,
        it_rental_price,
        ca_id,
        ( select ca_name from g5_shop_category where ca_id = left(a.ca_id, 4) ) as ca_name,
        it_img1 as it_img,
        it_delivery_cnt,
        it_sc_type,
        it_sale_cnt,
        it_sale_cnt_02,
        it_sale_cnt_03,
        it_sale_cnt_04,
        it_sale_cnt_05,
        it_sale_percent,
        it_sale_percent_02,
        it_sale_percent_03,
        it_sale_percent_04,
        it_sale_percent_05,
        it_sale_percent_great,
        it_sale_percent_great_02,
        it_sale_percent_great_03,
        it_sale_percent_great_04,
        it_sale_percent_great_05,
        it_type1,
        it_type2,
        it_type3,
        it_type4,
        it_type5,
        it_type6,
        it_type7,
        it_type8,
        it_type9,
        it_type10,
        it_expected_warehousing_date
      FROM
        {$g5['g5_shop_item_table']} a
      WHERE
        a.it_id = '{$it_id}'
    ");

    $gubun = $cate_gubun_table[substr($it["ca_id"], 0, 2)];
    $gubun_text = '판매';
    if($gubun == '01') $gubun_text = '대여';
    else if($gubun == '02') $gubun_text = '비급여';

    $it['gubun'] = $gubun_text;

    echo 'select_item(' . json_encode($it) . ');'.PHP_EOL;
    echo 'select_barcode(' . json_encode($_POST['barcode_r']) . ');'.PHP_EOL;
  
  }

}

?>

$('input[name="it_id[]"]').each(function() {
  get_stock_data($(this).val());
});

$(function() {
  <?php if($dc_id == ""){?>
  $('#se_body_wr').hide();
  <?php }?>
  var dc_id = 'dc_id=<?=$dc_id?>';
  search(dc_id);

  function search(queryString) {
    if(!queryString) queryString = '';
    var params = $('#form_search').serialize();
    var $listWrap = $('#list_wrap');

    $.ajax({
      method: 'GET',
      url: '<?=G5_SHOP_URL?>/eform/ajax.eform.list.php?' + queryString,
      data: params,
      beforeSend: function() {
        $listWrap.html('<div style="text-align:center;"><img src="<?=G5_URL?>/img/loading-modal.gif"></div>');
      }
    })
    .done(function(data) {
      $listWrap.html(data);
      // 페이지네이션 처리
      $('#list_wrap .pagination a').on('click', function(e) {
        e.preventDefault();
        var params = $(this).attr('href').replace('?', '');
        search(params);
      });
    })
    .fail(function() {
      $listWrap.html('');
    });
  }
})

</script>

<?php include_once("./_tail.php"); ?>
