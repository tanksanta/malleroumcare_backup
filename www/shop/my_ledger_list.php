<?php
include_once('./_common.php');

if(!$is_member) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "거래처원장";
include_once("./_head.php");
?>

<section class="wrap">
  <div class="sub_section_tit">거래처원장</div>
  <div class="search_box">
    <div class="search_date">
      <input type="text" name="" value="2021-02-01" id=""/> ~ <input type="text" name="" value="2021-02-01" id=""/> <a href="#">이번달</a> <a href="#">저번달</a>
    </div>
    <select name="searchtype">
      <option >품목명</option>
      <option >주문번호</option>
    </select>
    <div class="input_search">
      <input name="search" value="<?=$_GET["search"]?>" type="text">
      <button type="submit"></button>
    </div>
  </div>
  <div class="inner">
    <div class="list_box">
      <div class="subtit">
        검색 기간 내 구매액 : 9,900,000원 <span>(공급가 : 9,000,000원, VAT : 900,000원)</span>
        <div class="r_area">
          <a href="#" class="btn_green_box">엑셀다운로드</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <tr>
            <th>주문일</th>
            <th>주문번호</th>
            <th>품목명</th>
            <th>수량</th>
            <th>단가(VAT포함)</th>
            <th>공금가액</th>
            <th>부가세</th>
            <th>구매</th>
            <th>수금</th>
            <th>잔액</th>
            <th>수령인</th>
          </tr>
          <tr>
            <td colspan="9">이월잔액</td>
            <td class="text_r">1,000,000원</td>
            <td></td>
          </tr>
          <tr>
            <td class="text_c">2021-01-01</td>
            <td class="text_c">12345</td>
            <td>상품명(옵션명)</td>
            <td class="text_c">2</td>
            <td class="text_r">110,000원</td>
            <td class="text_r">100,000원</td>
            <td class="text_r">10,000원</td>
            <td class="text_r">220,000원</td>
            <td class="text_r">0</td>
            <td class="text_r">1,220,000원</td>
            <td>홍길동</td>
          </tr>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination ">
          <li> </li>
          <li><a href="#">&lt;</a></li>
          <li class="active"><a href="#">1</a></li>
          <li><a href="#">2</a></li>
          <li><a href="#">3</a></li>
          <li><a href="#">&gt;</a></li>
          <li></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php
include_once('./_tail.php');
?>
