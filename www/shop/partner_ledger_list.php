<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$g5['title'] = "파트너 거래처원장";
include_once("./_head.php");


?>

<section class="wrap">
  <div class="sub_section_tit">거래처원장</div>
  <form method="get">
    <div class="search_box">
       <div class="search_date">
      	
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="searchtype">
        <option >사업소명</option>
        <option >품목명</option>
        <option >주문번호</option>
      </select>
      <div class="input_search">
        <input name="search" value="<?=$_GET["search"]?>" type="text">
        <button type="submit"></button>
      </div>
    </div>
  </form>
  <div class="inner">
    <div class="list_box">
      <div class="subtit">
        검색 기간 내 구매액 : 9,900,000원 <span>(공급가 : 9,000,000원, VAT : 900,000원)</span>

        <div class="r_area">
          <a href="#" class="btn_green_box">엑셀다운로드</a>
          <a href="partner_ledger_manage.php" class="btn_gray_box">수금등록</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>일시</th>
              <th>주문번호</th>
              <th>사업소</th>
              <th>품목명</th>
              <th>수량</th>
              <th>공급가액</th>
              <th>부가세</th>
              <th>합계</th>
              <th>수금</th>
              <th>잔액</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text_r">-</td>
              <td class="text_c" colspan="6">이월잔액</td>
              <td class="text_r">-</td>
              <td class="text_r">-</td>
              <td class="text_r">2,100,000원</td>
            </tr>
            <tr>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">1234</td>
              <td class="text_c">ABC</td>
              <td>123(option)</td>
              <td class="text_c">1</td>
              <td class="text_r">10,000원</td>
              <td class="text_r">1,000원</td>
              <td class="text_r">11,000원</td>
              <td class="text_r">-</td>
              <td class="text_r">2,111,000원</td>
            </tr>
            <tr>
              <td class="text_r">2021-02-02</td>
              <td class="text_c" colspan="6">입금(메모)</td>
              <td class="text_r">-</td>
              <td class="text_r">-</td>
              <td class="text_r">2,100,000원</td>
            </tr>
            <tr>
              <td class="text_r">2021-02-02</td>
              <td class="text_c" colspan="6">출금(메모)</td>
              <td class="text_r">-</td>
              <td class="text_r">-</td>
              <td class="text_r">2,100,000원</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">  
          <?php echo apms_paging(5, $page, $total_page, '?page='); ?>
        </ul>
      </div>
    </div>
  </div>
</section>



<?php
include_once('./_tail.php');
?>
