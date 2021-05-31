<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>감사 추적 인증서</title>
  <link rel="stylesheet" href="../css/default.css">
  <link rel="stylesheet" href="../css/certificate.css">
</head>
<script>
// For WKHTMLTOPDF Substitutions
window.onload = function()
{
    function substitute(key, value)
    {
        var elements = document.getElementsByClassName(key);
        for (var i = 0; elements && i < elements.length; i++)
        {
            elements[i].textContent = value;
        }
    }

    var url = window.location.href.replace(/#$/, ""); // Remove last # if exist
    // default params
    //['page', 'section','sitepage','title','subsection','frompage','subsubsection','isodate','topage','doctitle','sitepages','webpage','time','date'];
    var params = (url.split("?")[1] || "").split("&");
    for (var i = 0; i < params.length; i++)
    {
        var param = params[i].split("=");
        var key = param[0];
        var value = param[1] || '';
        var regex = new RegExp('{' + key + '}', 'g');
        substitute(key, decodeURIComponent(value));
    }
}
</script>
<body>
<div class="a4">
  <div class="head"><div class="head-text">감사 추적 인증서 (제작시간 : <span class="datetime"></span>)</div></div>
  <div style="height: 8mm;"></div>
</div>
</body>
</html>