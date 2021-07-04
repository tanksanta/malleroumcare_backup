<?php
include_once('./_common.php');

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

$data = $_POST;

if ($data['act'] == 'log_del') {
  $sql = "UPDATE
            recipient_grade_log
          SET
            del_yn = 'Y',
            deleted_at = CURRENT_TIMESTAMP(),
            deleted_by = '{$member['mb_id']}'
          WHERE
            seq = '{$data['seq']}' AND pen_id = '{$data['penId']}' ";
  $row = sql_query($sql);
} else {
  $sql = "INSERT INTO
            recipient_grade_log
          SET
            pen_id = '{$data['penId']}',
            pen_rec_gra_cd = '{$data['penRecGraCd']}',
            pen_rec_gra_nm = '{$data['penRecGraNm']}',
            pen_type_cd = '{$data['penTypeCd']}',
            pen_type_nm = '{$data['penTypeNm']}',
            pen_gra_edit_dtm = '{$data['penGraEditDtm']}',
            created_by = '{$member['mb_id']}' ";
  $row = sql_query($sql);
}

if ($row)
  json_response(200, 'OK');
else
  json_response(500, 'LOGGING ERROR');
?>
