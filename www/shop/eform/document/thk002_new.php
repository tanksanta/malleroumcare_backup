<div id="thk002" class="a4">
  <div class="thk002">
    <h1 class="thk002_h1">장기요양급여 제공기록지(복지용구)</h1>
    <div class="desc">
      ※ 아래의 유의사항을 읽고 작성하여 주시기 바라며, [ ]에는 해당되는 곳에
      ✓표를 합니다.
    </div>
    <table class="thk002_table">
      <colgroup>
        <col style="width: 25%" />
        <col style="width: 25%" />
        <col style="width: 25%" />
        <col style="width: 25%" />
      </colgroup>
      <tr>
        <th scope="row">수급자 성명</th>
        <th scope="row">생년월일</th>
        <th scope="row">장기요양등급</th>
        <th scope="row">장기요양인정번호</th>
      </tr>
      <tr>
        <td><?=$eform['penNm']?></td>
        <td><?=$eform['penBirth']?></td>
        <td><?=$eform['penRecGraNm']?></td>
        <td><?=$eform['penLtmNum']?></td>
      </tr>
      <tr>
        <th scope="row" colspan="2">장기요양기관명</th>
        <th scope="row" colspan="2">장기요양기관기호</th>
      </tr>
      <tr>
        <td colspan="2"><?=$eform['entNm']?></td>
        <td colspan="2"><?=$eform['entNum']?></td>
      </tr>
    </table>
    <table class="item_table">
      <colgroup>
        <col style="width: 16%" />
        <col style="width: 25%" />
        <col style="width: 15%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col style="width: 8%" />
        <col style="width: 8%" />
        <col style="width: 8%" />
      </colgroup>
      <thead>
        <tr>
          <th class="head" colspan="8">[ ✓ ]구입 [ &nbsp; ]대여</th>
        </tr>
        <tr>
          <th scope="row" rowspan="2">품목명</th>
          <th scope="row" rowspan="2">제품명</th>
          <th scope="row" rowspan="2">복지용구 표준코드</th>
          <th scope="row" rowspan="2">급여비용</th>
          <th scope="row" rowspan="2">판매일</th>
          <th scope="row" colspan="3">급여비 내역(원)</th>
        </tr>
        <tr>
          <th scope="row">총액</th>
          <th scope="row">본인<br>부담금</th>
          <th scope="row">공단<br>부담액</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 0;
        foreach($buy as $item) {
        ?>
        <tr>
          <td><?=$item['ca_name']?></td>
          <td><?=$item['it_name']?></td>
          <td><?=$item['it_code']?><?php if($item['it_barcode']) echo "- {$item['it_barcode']}"; ?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td><?=$item['it_date']?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td class="right"><?=number_format($item['it_price_pen'])?></td>
          <td class="right"><?=number_format($item['it_price_ent'])?></td>
        </tr>
        <?php
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
          <td></td>
          <td class="right"></td>
          <td class="right"></td>
          <td class="right"></td>
        </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
    <table class="item_table">
      <colgroup>
        <col style="width: 16%" />
        <col style="width: 25%" />
        <col style="width: 15%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col style="width: 8%" />
        <col style="width: 8%" />
        <col style="width: 8%" />
      </colgroup>
      <thead>
        <tr>
          <th class="head" colspan="8">[ &nbsp; ]구입 [ ✓ ]대여</th>
        </tr>
        <tr>
          <th scope="row" rowspan="2">품목명</th>
          <th scope="row" rowspan="2">제품명</th>
          <th scope="row" rowspan="2">복지용구 표준코드</th>          
          <th scope="row" rowspan="2">급여비용</th>
          <th scope="row" rowspan="2">대여기간</th>
          <th scope="row" colspan="3">급여비 내역(원)</th>
        </tr>
        <tr>
          <th scope="row">총액</th>
          <th scope="row">본인<br>부담금</th>
          <th scope="row">공단<br>부담액</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 0;
        foreach($rent as $item) {
        ?>
        <tr>
          <td><?=$item['ca_name']?></td>
          <td><?=$item['it_name']?></td>
          <td><?=$item['it_code']?><?php if($item['it_barcode']) echo "- {$item['it_barcode']}"; ?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td><?=$item['it_date']?></td>
          <td class="right"><?=number_format($item['it_price'])?></td>
          <td class="right"><?=number_format($item['it_price_pen'])?></td>
          <td class="right"><?=number_format($item['it_price_ent'])?></td>
        </tr>
        <?php
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
            <td class="right"></td>
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
    <table class="sign_table">
      <colgroup>
        <col style="width: 15%" />
        <col style="width: 10%" />
        <col style="width: 25%" />
        <col style="width: 20%" />
        <col style="width: 15%" />
        <col style="width: 15%" />
      </colgroup>
      <tr>
        <th>특이사항</th>
        <td colspan="5"><br /><br /><br /><br /></td>
      </tr>
      <tr>
        <th scope="col" rowspan="3">확인자</th>
        <th scope="col">사업소<br />담당자</th>
        <td colspan="2" style="border-right: 0"></td>
        <td style="border-left: 0;border-right: 0"><?=$eform['entCeoNm']?></td>
        <td class="seal-form" data-id="seal_002_1" style="border-left: 0;color: #999;font-size: 14px;text-align: right">( 서명 또는 인 )<?php if($preview || $download) {?>
	<div style="position:absolute; top:0px; right:35px;z-index:-1;">
		<img src="/data/file/member/stamp/<?=$member["sealFile"]; ?>" style="border:0px; width:60px;">
	</div>
	<?php }?></td>
      </tr>
      <tr>
        <th scope="col" rowspan="2">수급자<br />또는<br />보호자</th>
        <td colspan="2" style="border-right: 0"></td>
        <td style="border-left: 0;border-right: 0"><?php echo $eform['contract_sign_type'] == 0 ? $eform['penNm'] : $eform['contract_sign_name']; ?></td>
        <td class="sign-form" data-id="sign_002_1" style="border-left: 0;color: #999;font-size: 14px;text-align: right">( 서명 또는 인 )</td>
      </tr>
      <tr>
        <td colspan="4">
          수급자와의 관계 : [ <?php echo $eform['contract_sign_type'] == 0 ? '✓' : '&nbsp;';?> ]본인 [ <?php echo $eform['contract_sign_type'] == 1 ? '✓' : '&nbsp;';?> ]가족 [ <?php echo $eform['contract_sign_type'] == 2 ? '✓' : '&nbsp;';?> ]친족 [ <?php echo $eform['contract_sign_type'] == 3 ? '✓' : '&nbsp;';?> ]기타 ( &nbsp; )
        </td>
      </tr>
      <tr>
        <th scope="col" colspan="2">확인일시</th>
        <td><?=date('Y년 m월 d일', strtotime($eform['do_date']))?></td>
        <th scope="col">확인방법</th>
        <?php if($eform['penRecTypeCd'] == '01') { ?>
        <td colspan="2">[ &nbsp; ]방문 [ ✓ ]유선 <?php echo $eform['penRecTypeTxt']; ?></td>
        <?php } else { ?>
        <td colspan="2">[ ✓ ]방문 [ &nbsp; ]유선</td>
        <?php } ?>
      </tr>
    </table>
    <div class="notice_div">유의사항</div>
    <ul class="thk002_article" style="list-style-type: '*'"">
      <li>
        장기요양급여 제공기록지(복지용구)는 구입제품은 제공할 때, 대여제품은
        최초 제공할 때와 매월 기록합니다.
      </li>
      <li>급여비 내역의 총액은 제품가격(월대여료)의 합계를 기록하고 공단 부담액은 공단에 청구한 급여비용을 기록합니다. 가격에서 10원미만의 금액은 절사합니다.</li>
      <li>의료기관 입원, 시설 입소, 복지용구 점검 사항 등은 특이사항에 기록합니다.</li>
    </ul>
  </div>
</div>
