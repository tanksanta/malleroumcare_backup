<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$title = '주문 중인 사업소 목록';
include_once('./pop.head.php');

$it_ids = $_GET['it_id'] ?: [];

?>
<style>
.pop_order_add_item_table tbody td { padding: 10px; }
</style>

<div id="pop_order_add" class="admin_popup admin_popup_padding">
    <h4 class="h4_header">사업소 입고예정일 알림</h4>
    <table class="pop_order_add_item_table">
        <colgroup>
            <col />
            <col width="200px" />
            <col width="160px" />
        </colgroup>
        <thead>
            <tr>
                <th>상품명</th>
                <th>해당 상품 주문사업소</th>
                <th>리스트다운로드</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach($it_ids as $it_id) {
                $it_id = get_search_string($it_id);
                $it = sql_fetch(" select * from g5_shop_item where it_id = '$it_id' ");
                $sql = "
                select
                    count(*) as cnt
                from
                    (
                    select
                        mb_id
                    from
                        g5_shop_cart
                    where
                        it_id = '$it_id' and
                        ct_status in ('준비', '출고준비')
                    group by
                        mb_id
                    ) u
                ";
                $cnt = sql_fetch($sql);
                $cnt = $cnt['cnt'] ?: 0;
                if($cnt > 0) {
                    $i++;
            ?>
            <tr>
                <td><?=$it['it_name']?></td>
                <td class="no"><?=$cnt?>개 사업소</td>
                <td class="no"><a href="itemorderentexcel.php?it_id=<?=$it_id?>" class="shbtn small">사업소 리스트 다운로드</a></td>
            </tr>
            <?php
                }
            }

            if($i == 0) {
                echo '<tr><td colspan="3" class="no">주문 중인 사업소가 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>