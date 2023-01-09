<div id="thk003" class="a4">
  <div class="thk003">
    <h2 class="thk003_h2">개인정보 수집 · 이용 사전동의서</h2>
    <table class="sign_table">
      <colgroup>
        <col style="width: 10%" />
        <col style="width: 22%" />
        <col style="width: 26%" />
        <col style="width: 20%" />
        <col style="width: 22%" />
      </colgroup>
      <tr>
        <th scope="col" rowspan="4">고지<br />내용</th>
        <th scope="col">수집 · 이용 기관</th>
        <td colspan="3">
          보건복지부, 공공부조 및 사회서비스를 제공하는 국기 및
          지방자치단체(업무 위임/위탁기관 포함)
        </td>
      </tr>
      <tr>
        <th scope="col">수집 · 이용 목적</th>
        <td colspan="3">
          국가 및 지방자치단체가 제공하는 공공부조, 사회서비스 복지대상자 선정
          및 제공의 적정성 확인 조사
        </td>
      </tr>
      <tr>
        <th scope="col">수집 · 이용 항목</th>
        <td colspan="3">
          신청인 기재사항의 개인정보, 주소, 고유식별정보(주민등록번호),
          <br />장기요양급여 자격결정/수혜이력 정보(민감정보(건강정보))
        </td>
      </tr>
      <tr>
        <th scope="col">보유 · 이용 기간</th>
        <td colspan="3">수급자격의 상실시점으로부터 5년</td>
      </tr>
      <tr>
        <th scope="col" rowspan="3">동의<br />사항</th>
        <td colspan="3">
          본인은 「개인정보보호법」 제17조 및 제18조 개인정보의 처리에 관하여
          고지 받았으며, 이를 충분히 이해하고 그 처리에 동의합니다.
        </td>
        <td><label class="checkbox-container">동의함
          <input class="chk-form" id="chk_003_1_y" type="checkbox">
          <span class="checkmark"></span>
          </label><br><label class="checkbox-container">동의하지 않음
          <input class="chk-form" id="chk_003_1_n" type="checkbox">
          <span class="checkmark"></span>
          </label></td>
      </tr>
      <tr>
        <td colspan="3">
          본인은 「개인정보보호법」 제23조 민감정보(건강정보)의 처리에 관하여
          고지 받았으며, 이를 충분히 이해하고 그 처리에 동의합니다.
        </td>
        <td><label class="checkbox-container">동의함
          <input class="chk-form" id="chk_003_2_y" type="checkbox">
          <span class="checkmark"></span>
          </label><br><label class="checkbox-container">동의하지 않음
          <input class="chk-form" id="chk_003_2_n" type="checkbox">
          <span class="checkmark"></td>
      </tr>
      <tr>
        <td colspan="3">
          본인은 「개인정보보호법」 제24조 고유식별정보(주민등록번호)의 처리에
          관하여 고지 받았으며, 이를 충분히 이해하고 그 처리에 동의합니다.
        </td>
        <td><label class="checkbox-container">동의함
          <input class="chk-form" id="chk_003_3_y" type="checkbox">
          <span class="checkmark"></span>
          </label><br><label class="checkbox-container">동의하지 않음
          <input class="chk-form" id="chk_003_3_n" type="checkbox">
          <span class="checkmark"></td>
      </tr>
      <tr class="sign_row">
        <td colspan="4"></td>
        <td>
          <?php if ($is_render !== 'Y') {?>
            <label class="checkbox-container" id="chk_003_all_label">전체동의
              <input class="chk-form" id="chk_003_all" type="checkbox">
              <span class="checkmark"></span>
            </label><br>
          <?php } ?>
          <?=date('Y년 m월 d일', strtotime($eform['do_date']))?>
        </td>
      </tr>
      <tr class="sign_row">
        <td colspan="2"></td>
        <th scope="col" style="text-align: right">위 동의인 성명</th>
        <td><?=$eform['penNm']?></td>
        <td class="<?php echo $eform['contract_sign_type'] == 0 ? 'sign-form' : '' ; ?>" data-id="sign_003_1" style="text-align: center; color: #999; font-size: 14px;">(서명)</td>
      </tr>
      <tr class="sign_row">
        <td colspan="2"></td>
        <th scope="col" style="text-align: right">대리인 성명</th>
        <td><?php echo $eform['contract_sign_type'] > 0 ? $eform['contract_sign_name'] : ''; ?></td>
        <td class="<?php echo $eform['contract_sign_type'] > 0 ? 'sign-form' : '' ; ?>" data-id="sign_003_1" style="text-align: center; color: #999; font-size: 14px;">(서명)</td>
      </tr>
    </table>
    <div class="desc">※대리인이 작성시 아래의 위임장을 작성</div>
    <div style="height: 100px"></div>
    <h2 class="thk003_h2">개인정보 수집 · 이용 사전동의서 위임장</h2>
    <table class="sign_table bottom_table">
      <colgroup>
        <col style="width: 10%" />
        <col style="width: 20%" />
        <col style="width: 25%" />
        <col style="width: 20%" />
        <col style="width: 25%" />
      </colgroup>
      <tr>
        <th scope="col" rowspan="3">대리인</th>
        <th scope="col">성명</th>
        <td colspan="3"><?php echo $eform['contract_sign_type'] > 0 ? $eform['contract_sign_name'] : ''; ?></td>
      </tr>
      <tr>
        <th scope="col">주소</th>
        <td colspan="3"><?php echo $eform['contract_sign_type'] > 0 ? $eform['contract_addr'] : ''; ?></td>
      </tr>
      <tr>
        <th scope="col">위임자와의 관계</th>
        <td><?php if($eform['contract_sign_type']=='1'){
		switch($eform['contract_sign_relation']){
			case "1": echo "가족"; break;
			case "2": echo "친족"; break;
			case "3": echo "기타"; break;
			default: echo "기타";break;
		}			
		}?></td>
        <th scope="col">전화번호</th>
        <td><?php echo $eform['contract_sign_type'] > 0 ? $eform['contract_tel'] : ''; ?></td>
      </tr>
    </table>
    <div class="desc">
      본인은 위 사람에게 「개인정보보호법」 제17조 및 18조 제1항에 따라
      개인정보의 수집 및 이용 관한 일체의 권한을 위임합니다.
    </div>
    <div style="height: 30px"></div>
    <table class="sign_table bottom_table">
      <colgroup>
        <col style="width: 10%" />
        <col style="width: 20%" />
        <col style="width: 25%" />
        <col style="width: 20%" />
        <col style="width: 25%" />
      </colgroup>
      <tr>
        <th scope="col" rowspan="2">위임자</th>
        <th scope="col">성명</th>
        <td><?php echo $eform['contract_sign_type'] > 0 ? $eform['penNm'] : ''; ?></td>
        <th scope="col">장기요양인정번호</th>
        <td><?php echo $eform['contract_sign_type'] > 0 ? $eform['penLtmNum'] : ''; ?></td>
      </tr>
      <tr>
        <th scope="col">주소</th>
        <td colspan="3"><?php echo $eform['contract_sign_type'] > 0 ? "(".$eform['penZip'].") ".$eform['penAddr']." ".$eform['penAddrDtl'] : ''; ?></td>
      </tr>
    </table>
  </div>
</div>
