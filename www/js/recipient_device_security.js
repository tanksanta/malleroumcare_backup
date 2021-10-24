function check_device_security_val() {
    var auth = $('.device_security').val()
    if (auth == "N" || auth == "D") {
      $("#popup_recipient > div").html(`<iframe src='my_recipient_security.php'>`);
      $("#popup_recipient iframe").addClass('security');
      $("#popup_recipient iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient").show();
      });
      return false;
    }
    else if (auth == "W") {
      alert("기기승인 대기중입니다. 관리자에게 문의하세요.");
      return false;
    }
    return true;
}