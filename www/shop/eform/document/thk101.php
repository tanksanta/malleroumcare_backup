<div id="thk101" class="a4">
  <div class="thk101">
    <h1 class="thk101-h1">장기요양기관 입소·이용신청서([✓] 신규신청 [ &nbsp;]갱신 [ &nbsp;]변경 [ &nbsp;]해지)</h1>
    <div class="thk101-head-table-desc">※［ ］에는 해당되는 곳에 ✓표를 합니다.</div>
    <table class="thk101-head-table">
      <colgroup>
        <col style="width: 33.3%;">
        <col style="width: 33.3%;">
        <col style="width: 33.3%;">
      </colgroup>
      <tr>
        <td><div class="td-header">접수번호</div></td>
        <td><div class="td-header">접수일자</div></td>
        <td><div class="td-header">처리기간</div>7일이내</td>
      </tr>
    </table>
    <table class="app-table">
      <colgroup>
        <col style="width: 10%">
        <col style="width: 10%">
        <col style="width: 25%">
        <col style="width: 10%">
        <col style="width: 20%">
        <col style="width: 10%">
        <col style="width: 15%">
      </colgroup>
      <tbody>
        <tr>
          <th scope="col" rowspan="3">신청인</th>
          <th scope="col">성명</th>
          <td><?=$eform['penNm']?></td>
          <th scope="col">생년월일</th>
          <td><?=$eform['penBirth']?></td>
          <th scope="col">수급자와의<br>관계</th>
          <td>본인</td>
        </tr>
        <tr>
          <th scope="col">주소</th>
          <td colspan="5">(<?=$eform['penZip']?>) <?=$eform['penAddr']?> <?=$eform['penAddrDtl']?></td>
        </tr>
        <tr>
          <th scope="col">전화번호</th>
          <td colspan="5" style="position: relative;"><?=$eform['penConNum']?><div style="position: absolute; bottom: 0; right: 0; padding: 2px 4px;">(휴대전화: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div></td>
        </tr>
      </tbody>
    </table>
    <table class="app-table">
      <colgroup>
        <col style="width: 10%">
        <col style="width: 10%">
        <col style="width: 35%">
        <col style="width: 10%">
        <col style="width: 35%">
      </colgroup>
      <tbody>
        <tr>
          <th scope="col" rowspan="6">수급자</th>
          <th scope="col">성명</th>
          <td><?=$eform['penNm']?></td>
          <th scope="col">주민등록번호</th>
          <td><?=substr($eform['penJumin'], 0, 6)?>-<?=substr($eform['penJumin'], 6)?></td>
        </tr>
        <tr>
          <th scope="col">장기요양<br>등급</th>
          <td><?=$eform['penRecGraNm']?></td>
          <th scope="col">장기요양인정번호</th>
          <td><?=$eform['penLtmNum']?></td>
        </tr>
        <tr>
          <th scope="col">주소</th>
          <td colspan="3">(<?=$eform['penZip']?>) <?=$eform['penAddr']?> <?=$eform['penAddrDtl']?></td>
        </tr>
        <tr>
          <th scope="col">전화번호</th>
          <td colspan="3" style="position: relative;"><?=$eform['penConNum']?><div style="position: absolute; bottom: 0; right: 0; padding: 2px 4px;">(휴대전화: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div></td>
        </tr>
        <tr>
          <th scope="col">입소·이용 희망<br>장기요양기관</th>
          <td colspan="3"><?=$eform['entNm']?></td>
        </tr>
        <tr>
          <th scope="col">구분</th>
          <td colspan="3">[✓] &nbsp;「의료급여법」 &nbsp;제3조제1항제1호에 따른 의료급여를 받는 사람<br>[ &nbsp;] &nbsp;「의료급여법」 &nbsp;제3조제1항제1호 외의 규정에 따른 의료급여를 받는 사람</td>
        </tr>
      </tbody>
    </table>
    <table class="sign-table">
      <colgroup>
        <col style="width: 40%;">
        <col style="width: 30%;">
        <col style="width: 15%;">
        <col style="width: 15%;">
      </colgroup>
      <tbody>
        <tr>
          <td colspan="4" class="center">「노인장기요양보험법 시행규칙」 제13조에 따라 장기요양기관 입소·이용을 위와 같이 신청합니다.</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
          <td colspan="2" class="center" style="font-size: 16px;"><?=date('Y년 m월 d일', strtotime($eform['dc_datetime']))?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <th scope="col" class="right">신청인:</th>
          <td style="font-size: 16px;"><?=$eform['penNm']?></td>
          <td class="sign-desc sign-form" data-id="sign_101_1">(서명 또는 인)</td>
        </tr>
        <tr>
          <td colspan="4" class="center"><p style="display: inline-block; margin: 0; padding: 0; text-align: left;">※ 신청인이 수급자 본인·가족, 사회복지전담공무원, 특별자치시장·특별자치도지사·시장·군수·구청장이 지정<br> &nbsp; &nbsp; 한 자 외의 이해관계인인 경우에는 수급자의 동의를 받아야 합니다.</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <th scope="col" class="right">수급자(또는 보호자):</th>
          <td style="font-size: 16px;"><?=$eform['penNm']?></td>
          <td class="sign-desc sign-form" data-id="sign_101_2">(서명 또는 인)</td>
        </tr>
        <tr>
          <td colspan="2" class="center td-to" style="font-weight: bold; font-size: 20px;">○○ 특별자치시장 · 특별자치도지사 · 시장 · 군수 · 구청장</td>
          <td colspan="2" class="center td-to" style="font-size: 16px;">귀하</td>
        </tr>
      </tbody>
    </table>
    <table class="table-doc">
      <tbody>
        <tr>
          <th scope="col">신청인<br>제출서류</th>
          <td>장기요양인정서 사본</td>
          <td rowspan="2" class="center"><p>수수료</p><p>없음</p></td>
        </tr>
        <tr>
          <th scope="col">담당 공무원<br>확인사항</th>
          <td><ol><li>주민등록표 등 · 초본</li><li>「의료급여법」 &nbsp;제3조제1항제1호에 따른 의료급여를 받는 사람의 경우 의료급여수급자 증명서,<br>「의료급여법」 &nbsp;제3조제1항제1호 외의 규정에 따른 의료급여를 받는 사람의 경우 의료보호증</li></ol></td>
        </tr>
      </tbody>
    </table>
    <table class="sign-table">
      <colgroup>
        <col style="width: 40%">
        <col style="width: 30%">
        <col style="width: 15%">
        <col style="width: 15%">
      </colgroup>
      <thead>
        <tr>
          <th scope="row" class="th-sign-head" colspan="4">행정정보 공동이용 동의서</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="4"><p style="margin: 0; padding: 0; text-indent: 12px;">본인은 이 건 업무처리와 관련하여 담당 공무원이 「전자정부법」 제36조제1항에 따른 행정정보의 공동이용 및 사회복지통합전산망을 통하여 위의 담당 공무원 확인사항을 확인하는 것에 동의합니다. &nbsp; &nbsp; &nbsp; &nbsp; *동의하지 아니하거나 확인이 되지 아니하는 경우에는 신청인이 직접 관련 서류를 제출하여야 합니다.</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <th scope="col" class="center">신청인</th>
          <td style="font-size: 16px;"><?=$eform['penNm']?></td>
          <td class="sign-desc sign-form" data-id="sign_101_3">(서명 또는 인)</td>
        </tr>
      </tbody>
    </table>
    <table class="sign-table">
      <colgroup>
        <col style="width: 19%">
        <col style="width: 8%">
        <col style="width: 19%">
        <col style="width: 8%">
        <col style="width: 19%">
        <col style="width: 8%">
        <col style="width: 19%">
      </colgroup>
      <thead>
        <tr>
          <th scope="row" class="th-sign-head" colspan="7">처 리 절 차</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="flow-box"><div><p>신청서 작성</p></div></td>
          <td class="right-arrow"></td>
          <td class="flow-box"><div><p>접수 및 확인</p></div></td>
          <td class="right-arrow"></td>
          <td class="flow-box"><div><p>장기요양기관에<br>의뢰서 송부</p></div></td>
          <td class="right-arrow"></td>
          <td class="flow-box"><div><p>통지</p></div></td>
        </tr>
        <tr class="no-padding">
          <td class="center">신청인</td>
          <td></td>
          <td class="center">처리기관<br>(특별자치시·도, 시·군·구)</td>
          <td></td>
          <td class="center">처리기관<br>(특별자치시·도, 시·군·구)</td>
          <td></td>
          <td class="center">신청인</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
