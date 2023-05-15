<?php
$sub_menu = '100200';
include_once('./_common.php');

$chk_sql = "SHOW COLUMNS FROM g5_auth WHERE `Field` = 'entry_menu';";//최초진입 페이지를 위한 컬럼이 생성되어 있는지 확인
$chk_res = sql_fetch( $chk_sql );
if(!$chk_res['Field']) {
    sql_query("alter table g5_auth 
    add column if not exists entry_menu set('y') default null comment '최초진입메뉴', 
    add column if not exists entry_link varchar(255) default null comment '최초진입메뉴 링크';", true);
}

$g5['title'] = '관리권한설정';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);

auth_check($auth[$sub_menu], "r");

$type = $_GET['type']? $_GET['type'] : "group";
$couponlist_page_rows = $page_rows?$page_rows:$_COOKIE['couponlist_page_rows'];

$sql_common = "
  from g5_member m 
  left join (select mb_id, count(*) as cnt from g5_auth group by mb_id) ac on ac.mb_id = m.mb_id
  left join (select mb_id, au_menu from g5_auth where entry_menu is not null) am on am.mb_id=m.mb_id
";

$sql_group = "";

$sql_select = "
    select m.mb_level, m.mb_id, m.mb_name, m.mb_nick, ac.cnt, au_menu
";

$colspan = 6;


$sql_search = ' where (1) and m.mb_level in (9,10) ';

// 검색어 검색
if ($search) {
  $sql_search .= " and ( m.mb_id like '%{$search}%' or m.mb_name like '%{$search}%') ";
}

if (!$sst) {
    $sst  = "mb_level";
    $sod = "desc";
}

// sst : 정렬 어떤걸로(생성일자), sod : 오름차순/내림차순(내림차순)
$sql_order = " order by {$sst} {$sod} ";

$sql = "
  {$sql_select}
  {$sql_common}
  {$sql_search}
  {$sql_group}
  {$sql_order}
";
$sql_m = "
  {$sql_select}
  {$sql_common}
   where (1) and m.mb_level in (9,10) 
  {$sql_group}
  {$sql_order}
";

$result = sql_query($sql, true);
$result_m = sql_query($sql_m, true);

?>
<link href="css/switcher.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT" crossorigin="anonymous"></script>
<script src="js/jquery.switcher.js"></script>
<style>



/* onoff 체크박스 */
.ui-switcher {
  background-color: #bdc1c2;
  display: inline-block;
  height: 20px;
  width: 48px;
  border-radius: 10px;
  box-sizing: border-box;
  vertical-align: middle;
  position: relative;
  cursor: pointer;
  transition: border-color 0.25s;
  margin: -2px 4px 0 0;
  box-shadow: inset 1px 1px 1px rgba(0, 0, 0, 0.15);
}

.ui-switcher:before {
  font-family: sans-serif;
  font-size: 10px;
  font-weight: 400;
  color: #ffffff;
  line-height: 1;
  display: inline-block;
  position: absolute;
  top: 6px;
  height: 12px;
  width: 20px;
  text-align: center;
}

.ui-switcher:after {
  background-color: #ffffff;
  content: '\0020';
  display: inline-block;
  position: absolute;
  top: 2px;
  height: 16px;
  width: 16px;
  border-radius: 50%;
  transition: left 0.25s;
}

.ui-switcher[aria-checked=false]:before {
    content: 'OFF';
    right: 7px;
}
.ui-switcher[aria-checked=false]:after {
    left: 2px;
}

.ui-switcher[aria-checked=true] {
    background-color: #25ba9a;
}
.ui-switcher[aria-checked=true]:before {
    content: 'ON';
    left: 7px;
}
.ui-switcher[aria-checked=true]:after {
    left: 30px;
}

/* 검색폼수정 */
.new_form {
    border:1px solid #ddd;
    box-sizing: border-box;
    margin:30px 20px 30px 20px;
    background-color: #f8f8fa;
    min-width:500px;
}
</style>

<div id="manager_list" style="width: 50%; height: 100%; float: left; padding: 0 1%; border-right: 1px solid #e3e3e3;">
  <h2>관리자리스트</h2>

  <!-- 검색폼 -->
  <form name="frmauthlist" id="frmauthlist">
      <div class="new_form">
          <table class="new_form_table" id="search_detail_table">
              <tr>
                  <th>관리자 회원 검색</th>
                  <td>
                      <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;" placeholder="회원ID 또는 이름으로 검색">
                      <input type="hidden" id="search_yn" name="search_yn" value="searching">
                      <button class="newbutton" type="submit" id="search_mb_btn"><span>검색</span></button>
                  </td>
              </tr>
          </table>
      </div>
      <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
      <input type="hidden" name="stx" value="<?php echo $stx; ?>">
      <input type="hidden" name="type" value="<?php echo $type; ?>">
  </form>

  <form name="fauthlist" id="fauthlist">
      <div class="tbl_head01 tbl_wrap">
          <table>
          <caption><?php echo $g5['title']; ?></caption>
          <thead>
          <tr>
              <th scope="col">회원 ID</th>
              <th scope="col">이름</th>
              <th scope="col">닉네임</th>
              <th scope="col">회원권한</th>
              <th scope="col">권한메뉴 수</th>
              <th scope="col">최초진입메뉴</th>
          </tr>
          </thead>
          <tbody>
          <?php
          for ($i=0; $row=sql_fetch_array($result); $i++) {
              $bg = 'bg'.($i%2);
          ?>

          <tr class="<?php echo $bg; ?>" style="cursor: pointer;" onclick="get_auth(this)" id="<?php echo $row['mb_id']; ?>">
              <td class="td_sendcost_add"><?php echo $row['mb_id']; ?></td> <!-- 회원 ID -->
              <td class="td_send"><?php echo $row['mb_name']; ?></td> <!-- 이름 -->
              <td class="td_stat"><?=$row['mb_nick']?></td> <!-- 닉네임 -->
              <td class="td_numsmall"><?=$row['mb_level']?></td> <!-- 회원권한 -->
              <td class="td_numsmall"><?=$row['cnt']?></td> <!-- 권한메뉴 수 -->
              <td class="td_send"><?=$row['au_menu']?:"/adm/"?></td> <!-- 최초진입메뉴 -->
          </tr>

          <?php
          }

          if ($i == 0)
              echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
          ?>
          </tbody>
          </table>
      </div>
  </form>

</div>

<div id="auth_list" style="width: 50%; float: left; padding: 0 1%;">
  <h2>관리자리스트</h2>

  <!-- 검색폼 -->
  <form name="fmanagerlist" id="fmanagerlist">
      <div class="new_form">
          <table class="new_form_table" id="search_detail_table">
              <tr>
                  <th>권한 복사하기</th>
                  <td>
                      <select name="sel_field" id="sel_field" style="width: 250px;" disabled>
                        <option value="none"></option>
                        <?php for ($i=0; $row=sql_fetch_array($result_m); $i++) { ?>
                          <option value="<?=$row['mb_id']?>"><?=$row['mb_name'];?></option>
                        <?php } ?>
                      </select>
                      <input type="hidden" id="search_yn" name="search_yn" value="searching">
                      <button class="newbutton" id="search_au_btn" type="button" disabled><span>권한 복사하여 불러오기</span></button>
                  </td>
              </tr>
              <tr>
                  <th>등록설정</th>
                  <td>
                      <div>
                          <input type="checkbox" name="reg_setting[]" id="reg_r" value="r" title="" checked disabled><label for="reg_r">읽기</label>
                          <input type="checkbox" name="reg_setting[]" id="reg_w" value="w" title="" checked disabled><label for="reg_w">쓰기</label>
                          <input type="checkbox" name="reg_setting[]" id="reg_d" value="d" title="" checked disabled><label for="reg_d">삭제</label>
                          ※ 등록 클릭 시 체크된 권한으로 등록됩니다.
                      </div>
                  </td>
              </tr>
          </table>
      </div>
      <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
      <input type="hidden" name="stx" value="<?php echo $stx; ?>">
      <input type="hidden" name="type" value="<?php echo $type; ?>">
  </form>

  <form name="fauthlist" id="fauthlist">
      <div class="tbl_head01 tbl_wrap">
          <table>
          <caption><?php echo $g5['title']; ?></caption>
          <thead>
          <tr>
              <th scope="col">번호</th>
              <th scope="col">페이지 ID</th>
              <th scope="col">페이지이름</th>
              <th scope="col">권한설정</th>
              <th scope="col">최초진입메뉴로 등록</th>
              <th scope="col">비고</th>
          </tr>
          </thead>
          <tbody id="auth_table">
          <?php
          for ($i=0; $row=sql_fetch_array($result_menu); $i++) {
              $bg = 'bg'.($i%2);
          ?>

          <tr class="<?php echo $bg; ?>">
              <td class="td_alignc" style="width: 15px;"><?php echo $row['mb_id']; ?></td> <!-- 번호 -->
              <td class="td_categorysmall"><?php echo $row['mb_name']; ?></td> <!-- 페이지 ID -->
              <td class="td_confirm"><?=$row['mb_nick']?></td> <!-- 페이지이름 -->
              <td class="td_imgline"><?=$row['mb_level']?></td> <!-- 권한설정 -->
              <td class="td_stat"><?=$row['cnt']?></td> <!-- 최초진입메뉴로 등록 -->
              <td class="td_stat"><?=$row['cp_subject']?></td> <!-- 비고 -->
          </tr>

          <?php
          }

          if ($i == 0)
              echo '<tr><td colspan="'.$colspan.'" class="empty_table">회원을 선택해주세요.</td></tr>';
          ?>
          </tbody>
          </table>
      </div>
  </form>

</div>

<script>
var auth_loading = false;
var sel_mb_id = "";
var menu_link_list = [];

$(function() {
    var a_menu = <?=json_encode($menu)?>;
    console.log(a_menu);

    $(document).on( "click", "#btn_delete", function(){
        $.ajax('ajax.auth_menu.php', {
            type: 'POST',  // http method
            data: { id : sel_mb_id, menu : [$(this)[0].dataset.id], status : 'd'},  // data to submit
            success: function (data) {
              alert("권한 삭제가 완료되었습니다.");
              window.location.reload();
            },
            error: function (jqXhr, textStatus, errorMessage) {
                var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
                console.log("err:", jqXhr);
                alert(errMSG);
            }
        });
    });

    $(document).on( "click", "#btn_write", function(){
        var checked_auth = [];
        if($('#reg_r').is(':checked')){checked_auth.push("r")}
        if($('#reg_w').is(':checked')){checked_auth.push("w")}
        if($('#reg_d').is(':checked')){checked_auth.push("d")}

        $.ajax('ajax.auth_menu.php', {
            type: 'POST',  // http method
            data: { id : sel_mb_id, menu : [$(this)[0].dataset.id], status : 'w', auth:checked_auth.join() },  // data to submit
            success: function (data) {
              alert("권한 등록이 완료되었습니다.");
              window.location.reload();
            },
            error: function (jqXhr, textStatus, errorMessage) {
                var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
                console.log("err:", jqXhr);
                alert(errMSG);
            }
        });
    });

    $(document).on( "click", ".ui-switcher", function(){
        var checked_auth = [];
        if($(this).children('#entey_switch').is(':checked')){
              $(this).children('#entey_switch').prop("checked", false);
              $(this)[0].ariaChecked = false;

              var menu_id = $(this).children('#entey_switch')[0].dataset.id;
              $.ajax('ajax.auth_menu.php', {
                  type: 'POST',  // http method
                  data: { id : sel_mb_id, menu : [menu_id], status : 'ed' },  // data to submit
                  success: function (data) {
                    alert("변경이 완료되었습니다.");
                  },
                  error: function (jqXhr, textStatus, errorMessage) {
                      var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
                      console.log("err:", jqXhr);
                      alert(errMSG);
                  }
              });
        } else {
          if (!confirm("해당 페이지를 최초진입메뉴로 등록하시겠습니까?")) {
              // 취소 클릭 시
              return false;
          } else {
              // 확인(예) 버튼 클릭 시 이벤트
              $("input[name=chk]").prop("checked", false);
              $(".ui-switcher").each(function () {
                  $(this)[0].ariaChecked = false;
              });
              $(this).children('#entey_switch').prop("checked", true);
              $(this)[0].ariaChecked = true;

              var menu_id = $(this).children('#entey_switch')[0].dataset.id;
              $.ajax('ajax.auth_menu.php', {
                  type: 'POST',  // http method
                  data: { id : sel_mb_id, menu : [menu_id], status : 'eu' },  // data to submit
                  success: function (data) {
                    alert("변경이 완료되었습니다.");
                  },
                  error: function (jqXhr, textStatus, errorMessage) {
                      var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
                      console.log("err:", jqXhr);
                      alert(errMSG);
                  }
              });
          }
        }
    });

    $(document).on( "click", "#search_au_btn", function(){
        $.ajax('ajax.auth_menu.php', {
          type: 'POST',  // http method
          data: { id : $('#sel_field').val(), menu : <?=json_encode($auth_menu)?>, status : 'c', auth:sel_mb_id },  // data to submit
          success: function (data) {
              auth_loading = true;
              var auth_data = data.message;
              $('#auth_table').empty();

              document.getElementById('sel_field').disabled = false;
              document.getElementById('search_au_btn').disabled = false;
              document.getElementById('reg_r').disabled = false;
              document.getElementById('reg_w').disabled = false;
              document.getElementById('reg_d').disabled = false;

              var auth_menu = <?=json_encode(array_keys($auth_menu))?>;

              for(var i = 0; i < auth_menu.length; i++){
                  var bg = 'bg'+(i%2);
                  var menu_cd = (' '+auth_menu[i]).replace(/\s/g,'');
                  var m_info = auth_data[menu_cd];

                  if(m_info) {
                    var innerHtml = '';
                    innerHtml += '<tr class="'+bg+'">';
                    innerHtml += '<td class="td_alignc" style="width: 15px;">'+(i+1)+'</td> <!-- 번호 -->';
                    innerHtml += '<td class="td_categorysmall">'+menu_cd+'</td> <!-- 페이지 ID -->';
                    innerHtml += '<td class="td_confirm">'+m_info.page_name+'</td> <!-- 페이지이름 -->';
                    innerHtml += '<td class="td_imgline">'+m_info.au_auth+'</td> <!-- 권한설정 -->';
                    if(m_info.reg=='y') {
                        var is_checked = m_info.entry_menu == 'y' ? " checked " : "";
                        var is_aria_checked = m_info.entry_menu == 'y' ? "true" : "false";

                        innerHtml += '<td class="td_stat">';
                        innerHtml += '<div class="form-check form-check-inline">';
                        innerHtml += '<div class="ui-switcher" aria-checked="'+is_aria_checked+'">';
                        innerHtml += '<input class="form-check-input" type="checkbox" id="entey_switch" name="chk" style="display:none" data-id="'+menu_cd+'"' + is_checked + '></div>';
                        innerHtml += '</td> <!-- 최초진입메뉴로 등록 -->';
                    } else {
                        innerHtml += '<td class="td_stat"></td> <!-- 최초진입메뉴로 등록 -->';
                    }
                    var a_tag = m_info.reg=='y'?'<a style="cursor: pointer;color: red;font-weight: bold;" id="btn_delete" data-id="'+menu_cd+'">삭제</a>':'<a style="cursor: pointer; font-weight: bold;" id="btn_write" data-id="'+menu_cd+'">등록</a>';
                    innerHtml += '<td class="td_stat">'+a_tag+'</td> <!-- 비고 -->';
                    innerHtml += '</tr>';

                    $('#auth_table:last').append(innerHtml);
                  }
              }
          },
          error: function (jqXhr, textStatus, errorMessage) {
              var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
              console.log("err:", jqXhr);
              alert(errMSG);
          }
      });
    });
});

function get_auth(f)
{
    sel_mb_id = f.id;
    $.ajax('ajax.auth_menu.php', {
          type: 'POST',  // http method
          data: { id : f.id, menu : <?=json_encode($auth_menu)?>, status : 's' },  // data to submit
          success: function (data) {
              auth_loading = true;
              var auth_data = data.message;
              $('#auth_table').empty();

              document.getElementById('sel_field').disabled = false;
              document.getElementById('search_au_btn').disabled = false;
              document.getElementById('reg_r').disabled = false;
              document.getElementById('reg_w').disabled = false;
              document.getElementById('reg_d').disabled = false;

              var auth_menu = <?=json_encode(array_keys($auth_menu))?>;

              for(var i = 0; i < auth_menu.length; i++){
                  var bg = 'bg'+(i%2);
                  var menu_cd = (' '+auth_menu[i]).replace(/\s/g,'');
                  var m_info = auth_data[menu_cd];

                  if(m_info) {
                    var innerHtml = '';
                    innerHtml += '<tr class="'+bg+'">';
                    innerHtml += '<td class="td_alignc" style="width: 15px;">'+(i+1)+'</td> <!-- 번호 -->';
                    innerHtml += '<td class="td_categorysmall">'+menu_cd+'</td> <!-- 페이지 ID -->';
                    innerHtml += '<td class="td_confirm">'+m_info.page_name+'</td> <!-- 페이지이름 -->';
                    innerHtml += '<td class="td_imgline">'+m_info.au_auth+'</td> <!-- 권한설정 -->';
                    if(m_info.reg=='y') {
                        var is_checked = m_info.entry_menu == 'y' ? " checked " : "";
                        var is_aria_checked = m_info.entry_menu == 'y' ? "true" : "false";

                        innerHtml += '<td class="td_stat">';
                        innerHtml += '<div class="form-check form-check-inline">';
                        innerHtml += '<div class="ui-switcher" aria-checked="'+is_aria_checked+'">';
                        innerHtml += '<input class="form-check-input" type="checkbox" id="entey_switch" name="chk" style="display:none" data-id="'+menu_cd+'"' + is_checked + '></div>';
                        innerHtml += '</td> <!-- 최초진입메뉴로 등록 -->';
                    } else {
                        innerHtml += '<td class="td_stat"></td> <!-- 최초진입메뉴로 등록 -->';
                    }
                    var a_tag = m_info.reg=='y'?'<a style="cursor: pointer;color: red;font-weight: bold;" id="btn_delete" data-id="'+menu_cd+'">삭제</a>':'<a style="cursor: pointer; font-weight: bold;" id="btn_write" data-id="'+menu_cd+'">등록</a>';
                    innerHtml += '<td class="td_stat">'+a_tag+'</td> <!-- 비고 -->';
                    innerHtml += '</tr>';

                    $('#auth_table:last').append(innerHtml);
                  }
              }
              // border 바꾸기 auth_list
              var auth_l = document.getElementById("auth_list");
              var manager_l = document.getElementById("manager_list");
              auth_l.style.borderLeft  = "1px solid #e3e3e3";
              manager_l.style.border = "none";
          },
          error: function (jqXhr, textStatus, errorMessage) {
              var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "오류가 발생하였습니다. 다시 시도해주세요.":jqXhr['responseJSON']['message'];
              console.log("err:", errMSG);
              alert(errMSG);
          }
      });


    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
