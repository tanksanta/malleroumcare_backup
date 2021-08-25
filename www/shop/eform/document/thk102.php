<div id="thk102" class="a4">
  <div class="thk102">
    <table class="thk102-table">
      <colgroup>
        <col style="width: 5%" />
        <col style="width: 7%" />
        <col style="width: 8%" />
        <col style="width: 10%" />
        <col style="width: 5%" />
        <col style="width: 5%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col style="width: 5%" />
        <col style="width: 15%" />
      </colgroup>
      <thead>
        <tr>
          <th scope="row" colspan="12" class="thk102-h1">재가서비스 이용내역서</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="col" rowspan="2">수급자</th>
          <th scope="col" colspan="2">성명</th>
          <td colspan="4" class="center"><?=$eform['penNm']?></td>
          <th scope="col" colspan="2">주민등록번호</th>
          <td colspan="3" class="center"><?=substr($eform['penJumin'], 0, 6)?>-<?=substr($eform['penJumin'], 6)?></td>
        </tr>
        <tr>
          <th scope="col" colspan="2">장기요양등급</th>
          <td colspan="4" class="center"><?=$eform['penRecGraNm']?></td>
          <th scope="col" colspan="2">장기요양인정번호</th>
          <td colspan="3" class="center"><?=$eform['penLtmNum']?></td>
        </tr>
        <tr>
          <th scope="col" rowspan="6"><p>급여</p><p>이용</p><p>신청</p><p>내역</p></th>
          <th scope="col" colspan="2">급여종류</th>
          <td colspan="4" class="center">재가급여</td>
          <th scope="col" colspan="2">이용기간</th>
          <td colspan="3" class="center"><?=$eform['penExpiDtm']?></td>
        </tr>
        <tr>
          <th scope="row" rowspan="2" colspan="2">서비스<br>종류</th>
          <th scope="row" rowspan="2" colspan="2">서비스내용</th>
          <th scope="row" rowspan="2" colspan="2">수가</th>
          <th scope="row" rowspan="2">횟수 / 월</th>
          <th scope="row" rowspan="2">금액 / 월</th>
          <th scope="row" colspan="3">이용희망기관</th>
        </tr>
        <tr>
          <th scope="row" colspan="2">장기요양<br>기관명</th>
          <th scope="row">장기요양<br>기관기호</th>
        </tr>
        <tr class="tr-content">
          <td colspan="2">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="tr-content">
          <td colspan="2">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th scope="col" colspan="4">합 &nbsp;계</th>
          <td colspan="4" class="right">( 원 )</td>
          <td colspan="3" class="slash">&nbsp;</td>
        </tr>
        <tr>
          <th scope="col" rowspan="<?php $length = count($buy) + count($rent); if($length < 15) $length = 15; echo $length + 3; ?>"><p>복지</p><p>용구</p><p>이용</p><p>신청</p><p>내역</p></th>
          <th scope="row" rowspan="2" colspan="2">품목명</th>
          <th scope="row" rowspan="2">제품코드</th>
          <th scope="row" colspan="2">급여방식</th>
          <th scope="row" rowspan="2" colspan="2">대여기간</th>
          <th scope="row" rowspan="2">금 &nbsp;액</th>
          <th scope="row" colspan="3">이용희망기관</th>
        </tr>
        <tr>
          <th scope="row">구입</th>
          <th scope="row">대여</th>
          <th scope="row" colspan="2">장기요양<br>기관명</th>
          <th scope="row">장기요양<br>기관기호</th>
        </tr>
        <?php
        $total_buy_price = 0;
        $count = 0;
        foreach($buy as $item) {
        ?>
        <tr class="tr-content">
          <td colspan="2"><?=$item['ca_name']?></td>
          <td><?=$item['it_code']?></td>
          <td class="center">V(<?=$item['it_qty']?>)</td>
          <td class="center">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td colspan="2"><?=$eform['entNm']?></td>
          <td><?=$eform['entNum']?></td>
        </tr>
        <?php
          $total_buy_price += intval($item['it_price']);
          $count++;
        }

        $total_rent_price = 0;
        foreach($rent as $item) {
        ?>
        <tr class="tr-content">
          <td colspan="2"><?=$item['ca_name']?></td>
          <td><?=$item['it_code']?></td>
          <td class="center">&nbsp;</td>
          <td class="center">V(<?=$item['it_qty']?>)</td>
          <td colspan="2"><?=$item['it_date']?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td colspan="2"><?=$eform['entNm']?></td>
          <td><?=$eform['entNum']?></td>
        </tr>
        <?php
          $total_rent_price += intval($item['it_price']);
          $count++;
        }

        // 최소 15줄은 생성
        for($i = $count; $i < 15; $i++) {
        ?>
        <tr class="tr-content">
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
          <td class="center">&nbsp;</td>
          <td class="center">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td class="right">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <?php
        }
        ?>
        <tr>
          <th scope="col" colspan="3">합 &nbsp;계</th>
          <td colspan="5" class="right"><?=number_format($total_buy_price + $total_rent_price)?> ( 원 )</td>
          <td colspan="3" class="slash">&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
