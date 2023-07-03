function check_all(f)
{
    var chk = document.getElementsByName("chk[]");

    for (i=0; i<chk.length; i++)
        chk[i].checked = f.chkall.checked;
}

function btn_check(f, act)
{
    if (act == "update") // 선택수정
    {
        f.action = list_update_php;
        str = "수정";
    }
    else if (act == "delete") // 선택삭제
    {
        f.action = list_delete_php;
        str = "삭제";
    }
    else
        return;

    var chk = document.getElementsByName("chk[]");
    var bchk = false;

    for (i=0; i<chk.length; i++)
    {
        if (chk[i].checked)
            bchk = true;
    }

    if (!bchk)
    {
        alert(str + "할 자료를 하나 이상 선택하세요.");
        return;
    }

    if (act == "delete")
    {
        if (!confirm("선택한 자료를 정말 삭제 하시겠습니까?"))
            return;
    }

    f.submit();
}

function is_checked(elements_name)
{
    var checked = false;
    var chk = document.getElementsByName(elements_name);
    for (var i=0; i<chk.length; i++) {
        if (chk[i].checked) {
            checked = true;
        }
    }
    return checked;
}

function delete_confirm(el)
{
    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        var token = get_ajax_token();
        var href = el.href.replace(/&token=.+$/g, "");
        if(!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }
        el.href = href+"&token="+token;
        return true;
    } else {
        return false;
    }
}

function delete_confirm2(msg)
{
    if(confirm(msg))
        return true;
    else
        return false;
}

function get_ajax_token()
{
    var token = "";

    $.ajax({
        type: "POST",
        url: g5_admin_url+"/ajax.token.php",
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            if(data.error) {
                alert(data.error);
                if(data.url)
                    document.location.href = data.url;

                return false;
            }

            token = data.token;
        }
    });

    return token;
}

$(function() {
    $(document).on("click", "form input:submit, form button:submit", function() {
        var f = this.form;
        var token = get_ajax_token();

        if(!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }

        var $f = $(f);

        if(typeof f.token === "undefined")
            $f.prepend('<input type="hidden" name="token" value="">');

        $f.find("input[name=token]").val(token);

        return true;
    });

    $.ajax({
        method: "GET",
        url: "/adm/ajax.alarm.count.php",
    }).done(function(data) {

        var bbs_count = 0;
        // 1:1문의
        if(data.qa.total_count) {
            $('.gnb_2da_qa').append('<span class="board_cnt">' + data.qa.total_count + '</span>');
            bbs_count += data.qa.total_count;
        }

        // 게시판
        if ( data.bbs.total_count ) {
            // bbs_count += data.bbs.total_count;
        }
        if(bbs_count > 0) {
            $('.gnb_1da_board').append('<span class="board_cnt">' + bbs_count + '</span>');
        }

        // $('.get_board_cnt').each(function(index, item) {
        //     var bo_table = $(item).data('bo-table');
        //     var value = data.bbs.data[bo_table] || 0;

        //     if ( value === 0 ) return true;

        //     $(item).append('<span class="board_cnt">' + value + '</span>');
        // });

        var shop_count = 0;
        // 상품문의
        if ( data.shop_qa.total_count ) {
            $('.gnb_2da_itemqa').append('<span class="board_cnt">' + data.shop_qa.total_count + '</span>');
            shop_count += data.shop_qa.total_count;
        }

        // 사용후기
        if ( data.shop_use.total_count ) {
            $('.gnb_2da_itemuse').append('<span class="board_cnt">' + data.shop_use.total_count + '</span>');
            shop_count += data.shop_use.total_count;
        }

        // 쇼핑몰 관리
        if (shop_count > 0) {
            $('.gnb_1da_shop').append('<span class="board_cnt">' + shop_count + '</span>');
        }
    });

});

$.fn.serializeObject = function() {
    "use strict"
    var result = {}
    var extend = function(i, element) {
      var node = result[element.name]
      if ("undefined" !== typeof node && node !== null) {
        if ($.isArray(node)) {
          node.push(element.value)
        } else {
          result[element.name] = [node, element.value]
        }
      } else {
        result[element.name] = element.value
      }
    }
  
    $.each(this.serializeArray(), extend)
    return result
}

function change_step(od_id, step, api) {
    console.log(od_id);
    console.log(step);
    console.log(api);
    $.ajax({
        method: "POST",
        url: "./ajax.order.step.php",
        data: {
            'step': step,
            'od_id[]': od_id,
            'api': api
        },
    }).done(function (data) {
        console.log(data);
        if (data == 'success') {
            alert('상태가 변경되었습니다.');
            if (step == "준비") {
                location.reload();
            } else {
                $("#" + step).click();
            }
        } else {
            alert(data);
            location.reload();
        }
    });
}

function change_step_for_purchase_order(od_id, step, api) {
  console.log(od_id);
  console.log(step);
  console.log(api);
  $.ajax({
    method: "POST",
    url: "./ajax.purchase.order.step.php",
    data: {
      'step': step,
      'od_id[]': od_id,
      'api': api
    },
  }).done(function (data) {
    console.log(data);
    if (data == 'success') {
      alert('상태가 변경되었습니다.');
      if (step == "준비") {
        location.reload();
      } else {
        $("#" + step).click();
      }
    } else {
      alert(data);
    }
  });
}

function clear_form(obj) {
    $(obj).find("input, select").each(function(e){

        var tagtype = "";

        if($(this).prop("tagName") == "select" || $(this).prop("tagName") == "SELECT"){
            tagtype = "select";
        }else{
            tagtype = "input";
        }

        if(tagtype == "input"){
            if($(this).attr("type") == "checkbox" || $(this).attr("type") == "CHECKBOX" || $(this).attr("type") == "radio" || $(this).attr("type") == "RADIO"){
                $(this).prop("checked",false);
            }else if($(this).attr("type") == "text"){
                $(this).val("");
            }
        }else{

            var name = $(this).attr("name");
            $("select[name='"+name+"'] option").each(function(){
                $(this).attr("selected","");
            });
            $("select[name='"+name+"'] option:eq(0)").attr("selected","selected");
        }
    });
}