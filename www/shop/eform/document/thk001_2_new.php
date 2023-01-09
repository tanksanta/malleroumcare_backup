<div id="thk001_2" class="a4">
  <div class="thk001">
    <h3 class="thk001_h3">특약사항</h3>
    <div class="thk001_article_div">
      <?=(nl2br($eform['entConAcc01']) ?: '없음')?>
    </div>
    <h3 class="thk001_h3">기타 협약사항</h3>
    <div class="thk001_article_div">
      <ol class="thk001_article">
        <li>
          "을" 은 물품 등의 하자 · 채무불이행 등으로 인한 소비자의 피해에
          대하여는 소비자기본법의 '소비자분쟁해결기준' 에 따라 수리 · 교환 ·
          환급 또는 배상을 하거나, 계약의 해제 · 해지 및 이행 등을 하여야
          한다.
        </li>
        <li>
          "을" 은 "갑" 이 노인요양시설(*)에 입소 중이거나, 의료기관에
          입원하고 있는 경우 복지용구 급여가 제한될 수 있음을 고지하여야
          하며, 만약 "갑" 이 시설에 있는 사실을 숨기고 복지용구를 제공받으면
          그 책임은 "갑" 이 부담한다.
          <br />
          (*) 노인요양시설 : 장기요양기관 및 타법령에 의한
          사회복지시설(사회복지사업법의 규정에 의한 신고를 하지 아니하고
          설치, 운영되는 시설 포함)
        </li>
        <li>
          "갑" 은 타법령에 의해 복지용구와 동일한 품목을 지급받은 경우, 당해
          품목에 대하여 급여가 제한될 수 있다.
        </li>
        <li>
          "갑" 은 대여제품이 불필요하게 되면 계약 종료일 이전이라도 본
          계약을 해지할 수 있다.
        </li>
        <li>
          계약자( "갑" )의 의무
          <ol class="thk001_article hangul">
            <li>
              "을" 의 동의 없이는 대여제품의 사양 변경, 가공 또는 개조를 할
              수 없다.
            </li>
            <li>
              "갑" 은 본 계약에 의한 권리의 전부 또는 일부를 제3자에게
              양도하거나 전대할 수 없다.
            </li>
            <li>
              대여기간 중 거주지를 옮기거나, 입원/입소 또는 사망 등으로
              이용상황의 변경이 생겼을 때에는 즉시 "을" 에게 통지를 하여야
              한다.
            </li>
          </ol>
        </li>
        <li>
          본 계약과 관련하여 쌍방의 이행에 따른 분쟁이 발생한 경우에는 "갑"
          과 "을" 은 상호 원만히 합의하여 해결하고자 노력하여야 하며, 부득이
          해결되지 않을 경우 사법절차에 따라 해결한다.
        </li>
      </ol>
    </div>
    <ul class="thk001_article asterisk">
      <li>
        본 계약에 대하여 계약당사자는 이의 없음을 확인하고 각자 서명, 날인
        후 계약자 쌍방이 본 계약서를 각 1통씩 보관한다.
      </li>
      <li>
        복지용구 공급계약 체결 시 제품의 일련번호(바코드)가 추가/수정하는
        것에 대한 안내를 받았으며, 이에 수급자 본인은 동의한다. (<label class="checkbox-container">동의함
          <input class="chk-form" id="chk_001_1_y" type="checkbox">
          <span class="checkmark"></span>
          </label>)
      </li>
    </ul>
    <div class="thk001_date_div">계약일 : <?=date('Y년 m월 d일', strtotime($eform['do_date']))?></div>
    
	<table class="thk001_table sign_table">
      <colgroup>
        <col style="width: 20%" />
        <col style="width: 10%" />
        <col style="width: 16%" />
        <col style="width: 14%" />
        <col style="width: 10%" />
        <col style="width: 16%" />
        <col style="width: 14%" />
      </colgroup>
      <tbody>
        <tr>
          <th scope="col" class="right">(갑)</th>
          <th scope="col" class="right">수급자 :</th>
          <td class="center"><?=$eform['penNm']?></td>
          <td class="<?php echo $eform['contract_sign_type'] == 0 ? 'sign-form' : '' ; ?> center" data-id="sign_001_1" style="font-size: 14px;">(서명)</td>
          <th scope="col" class="right">대리인 :</td>
          <td class="center"><?php echo $eform['contract_sign_type'] > 0 ? $eform['contract_sign_name'] : '' ; ?></td>
          <td class="<?php echo $eform['contract_sign_type'] > 0 ? 'sign-form' : '' ; ?> center" data-id="sign_001_1" style="font-size: 14px">(서명)</td>
        </tr>
        <tr>
          <th scope="col" class="right">(을)</th>
          <th scope="col" class="right">사업체 :</th>
          <td class="center"><?=$eform['entNm']?></td>
          <td></td>
          <th scope="col" class="right">대표 :</th>
          <td class="center"><?=$eform['entCeoNm']?></td>
          <td class="seal-form center" data-id="seal_001_1" style="font-size: 14px">(서명)<?php if($preview || $download) {?>
	<div style="position:absolute; top:-5px; right:45px;z-index:-1;">
		<img src="/data/file/member/stamp/<?=$member["sealFile"]; ?>" style="border:0px; width:60px;">
	</div>
	<?php }?></td>
        </tr>
        <tr>
          <td colspan="7"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
