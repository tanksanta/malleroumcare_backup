<div id="thk001_1" class="a4">
  <div class="thk001">
    <h1 class="thk001_h1">복지용구 공급 계약서</h1>
    <table class="thk001_table">
      <colgroup>
        <col style="width: 4%" />
        <col style="width: 4%" />
        <col style="width: 12%" />
        <col style="width: 10%" />
        <col style="width: 8%" />
        <col style="width: 12%" />
        <col style="width: 4%" />
        <col style="width: 16%" />
        <col style="width: 30%" />
      </colgroup>
      <tr>
        <th scope="col" class="bold" rowspan="3">(갑)</th>
        <th scope="col" colspan="2">수급자</th>
        <td colspan="3"><?=$eform['penNm']?></td>
        <th scope="col" class="bold" rowspan="3">(을)</th>
        <th scope="col">장기요양기관명</th>
        <td><?=$eform['entNm']?></td>
      </tr>
      <tr>
        <th scope="col" colspan="2">장기요양인정번호</th>
        <td colspan="3"><?=$eform['penLtmNum']?></td>
        <th scope="col">장기요양기관번호</th>
        <td><?=$eform['entNum']?></td>
      </tr>
      <tr>
        <th scope="col" colspan="2">인정등급</th>
        <td><?=$eform['penRecGraNm']?></td>
        <th scope="col">구분</th>
        <td><?=$eform['penTypeNm']?></td>
        <th scope="col">대표자</th>
        <td><?=$eform['entCeoNm']?></td>
      </tr>
      <tr class="divider">
        <th scope="col" class="bold" colspan="2">대리인</th>
        <td><?=($eform['contract_sign_type']=='1')?$eform['contract_sign_name']:""?></td>
        <th scope="col" colspan="2">수급자와의 관계</th>
        <td colspan="2"><?php if($eform['contract_sign_type']=='1'){
		switch($eform['contract_sign_relation']){
			case "1": echo "가족"; break;
			case "2": echo "친족"; break;
			case "3": echo "기타"; break;
			default: echo "기타";break;
		}			
		}?></td>
        <th scope="col">전화번호</th>
        <td><?=($eform['contract_sign_type']=='1')?$eform['contract_tel']:""?></td>
      </tr>
    </table>
    <ol class="thk001_article">
      <li>
        노인장기요양보험법의 제규정에 따라 수급자(이하 "갑" )와
        장기요양기관(이하 "을" ) 쌍방은 다음 같이 복지용구공급계약을 체결한다.
      </li>
      <li>
        "을" 이 "갑" 에게 공급하는 목적물은 보건복지가족부장관이 고시한
        복지용구로 제한한다.
      </li>
      <li>
        복지용구 구입 · 대여 가격은 보건복지부장관이 정하는 고시가격으로 한다.
        다만, 고시가격이 변동된 때에는 그에 따른다.
      </li>
    </ol>
    <h3 class="thk001_h3">구매 품목</h3>
    <table class="thk001_table item_table">
      <colgroup>
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 6%" />
        <col style="width: 14%" />
        <col style="width: 12%" />
        <col style="width: 12%" />
      </colgroup>
      <thead>
        <tr>
          <th scope="row" rowspan="2">품목명</th>
          <th scope="row" rowspan="2">제품명</th>
          <th scope="row" colspan="2">제품코드</th>
          <th scope="row" rowspan="2">개수</th>
          <th scope="row" rowspan="2">판매계약일</th>
          <th scope="row" rowspan="2">급여가</th>
          <th scope="row" rowspan="2">본인부담금</th>
        </tr>
        <tr>
          <th scope="row">품목코드</th>
          <th scope="row">바코드</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $total_buy_price = 0;
        $total_buy_price_pen = 0;
        $count = 0;
        foreach($buy as $item) {
        ?>
        <tr>
          <td><?=$item['ca_name']?></td>
          <td><?=$item['it_name']?></td>
          <td><?=$item['it_code']?></td>
          <td><?=$item['it_barcode']?></td>
          <td class="center"><?=$item['it_qty']?></td>
          <td><?=$item['it_date']?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td class="right"><?=number_format($item['it_price_pen'])?></td>
        </tr>
        <?php
          $total_buy_price += intval($item['it_price']);
          $total_buy_price_pen += intval($item['it_price_pen']);
          $count++;
        }

        // 최소 15줄은 생성
        for($i = $count; $i < 15; $i++) {
        ?>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td class="center"></td>
          <td></td>
          <td class="right"></td>
          <td class="right"></td>
        </tr>
        <?php
        }
        ?>
        <tr>
          <th scope="col" class="bold" colspan="6">합계</th>
          <td class="right"><?=number_format($total_buy_price)?></td>
          <td class="right"><?=number_format($total_buy_price_pen)?></td>
        </tr>
      </tbody>
    </table>
    <h3 class="thk001_h3">대여 품목</h3>
    <table class="thk001_table item_table">
      <colgroup>
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 14%" />
        <col style="width: 18%" />
        <col style="width: 13%" />
        <col style="width: 13%" />
      </colgroup>
      <thead>
        <tr>
          <th scope="row" rowspan="2">품목명</th>
          <th scope="row" rowspan="2">제품명</th>
          <th scope="row" colspan="2">제품코드</th>
          <th scope="row" rowspan="2">계약기간</th>
          <th scope="row" rowspan="2">급여가</th>
          <th scope="row" rowspan="2">본인부담금</th>
        </tr>
        <tr>
          <th scope="row">품목코드</th>
          <th scope="row">바코드</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total_rent_price = 0;
          $total_rent_price_pen = 0;
          $count = 0;
          foreach($rent as $item) {
        ?>
        <tr>
          <td><?=$item['ca_name']?></td>
          <td><?=$item['it_name']?></td>
          <td><?=$item['it_code']?></td>
          <td><?=$item['it_barcode']?></td>
          <td><?=$item['it_date']?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td class="right"><?=number_format($item['it_price_pen'])?></td>
        </tr>
        <?php
          $total_rent_price += intval($item['it_price']);
          $total_rent_price_pen += intval($item['it_price_pen']);
          $count++;
        }

        // 최소 5줄은 생성
        for($i = $count; $i < 5; $i++) {
        ?>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="right"></td>
            <td class="right"></td>
          </tr>
        <?php
        }
        ?>
        <tr>
          <th scole="col" class="bold" colspan="5">합계</th>
          <td class="right"><?=number_format($total_rent_price)?></td>
          <td class="right"><?=number_format($total_rent_price_pen)?></td>
        </tr>
      </tbody>
    </table>
    <table class="thk001_table item_table">
      <colgroup>
        <col style="width: 74%" />
        <col style="width: 13%" />
        <col style="width: 13%" />
      </colgroup>
      <tr>
        <th scope="col" class="bold">구매/대여 합계</th>
        <td class="right"><?=number_format($total_buy_price + $total_rent_price)?></td>
        <td class="right"><?=number_format($total_buy_price_pen + $total_rent_price_pen)?></td>
      </tr>
    </table>
  </div>
</div>
