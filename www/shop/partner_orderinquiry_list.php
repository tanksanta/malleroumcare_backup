<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "파트너 주문내역";
include_once("./_head.php");


?>

<section class="wrap">
  <div class="sub_section_tit">주문내역</div>
  <form method="get">
    <div class="search_box">
      <label><input type="checkbox" name="" value="" id=""/> 전체</label>, 
      <label><input type="checkbox" name="" value="" id=""/> 상품준비</label>, 
      <label><input type="checkbox" name="" value="" id=""/> 출고준비</label>, 
      <label><input type="checkbox" name="" value="" id=""/> 출고완료</label>, 
      <label><input type="checkbox" name="" value="" id=""/> 배송완료</label><br>
      
      <div class="search_date">
      	<select name="searchtype">
	        <option >주문일</option>
	        <option >출고완료일</option>
	      </select>
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
        목록
        <div class="r_area">
          <!-- <a href="#" class="btn_gray_box">모두확인</a> -->
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr >
              <th>주문일</th>
              <th>주문번호</th>
              <th>사업소</th>
              <th>품목명</th>
              <th>수량</th>
              <th>상태</th>
              <th>위탁내용</th>
              <th>요청사항</th>
              <th>공급가액</th>
              <th>부가세</th>
              <th>출고예정일</th>
              <th>출고완료일</th>
            </tr>
          </thead>
          <tbody>
            <tr onclick="location.href='partner_orderinquiry_view.php'" class="btn_link">
              <td class="text_c">2021-02-02</td>
              <td class="text_c">1234</td>
              <td class="text_c">ABC</td>
              <td>123(option)</td>
              <td class="text_c">1</td>
              <td class="text_c">주문접수</td>
              <td class="text_c">배송</td>
              <td>주문시 입력한 요청사항</td>
              <td class="text_r">10,000원</td>
              <td class="text_r">1,000원</td>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">2021-02-02</td>
            </tr>
            <tr onclick="location.href='partner_orderinquiry_view.php'" class="btn_link">
              <td class="text_c">2021-02-02</td>
              <td class="text_c">1234</td>
              <td class="text_c">ABC</td>
              <td>123(option)</td>
              <td class="text_c">1</td>
              <td class="text_c">주문접수</td>
              <td class="text_c">배송/설치</td>
              <td> </td>
              <td class="text_r">10,000원</td>
              <td class="text_r">1,000원</td>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">2021-02-02</td>
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
