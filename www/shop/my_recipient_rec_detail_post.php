<?php
include_once("./_common.php");

# 회원검사
if(!$member["mb_id"] || !$member['mb_entId'])
  json_response(400, "사업소 회원만 접근할 수 있습니다.");

$pen_id = get_search_string($_POST['penId']);

if(!$pen_id)
  json_response(400, "정상적이지 않은 접근입니다.");

$radio_button_member = [
  'nutrition',
  'meal',
  'digestion',
  'oral',
  'pee',
  'feces',
  'walking',
  'dementia',
  'eyesight',
  'hearing',
  'kidney_sub',
  'physical1',
  'physical2',
  'physical3',
  'physical4',
  'physical5',
  'physical6',
  'physical7',
  'daily1',
  'daily2',
  'daily3',
  'daily4',
  'daily5',
  'daily6',
  'daily7',
  'inst1',
  'inst2',
  'inst3',
  'inst4',
  'inst5',
  'inst6',
  'inst7',
  'walking_disorder_sub1',
  'walking_disorder_sub2',
  'nurse_nutrition_sub',
  'communication_communication',
  'communication_pronounce',
  'family_children',
  'family_helper',
  'family_helper_age',
  'family_helper_relation',
  'family_helper_economy',
  'family_helper_burden',
  'family_hosptial_regularly',
  'family_hospital_medicine',
  'family_religion',
  'living_heating',
  'living_ventilation',
  'living_threshold',
  'living_stairs_inner',
  'living_stairs_out',
  'living_toilet',
  'living_western',
  'living_hot_water',
  'living_bathtub',
  'living_basin',
  'living_kitchen'
];

$multi_check_member = [
  'walking_sub',
  'dementia_sub',
  'chronic',
  'circulatory',
  'nervous',
  'musculoskeletal',
  'mental',
  'breath',
  'kidney',
  'other',
  'exercise',
  'joint',
  'walking_disorder',
  'nurse_breath',
  'nurse_nutrition',
  'nurse_excretion',
  'nurse_wound',
  'nurse_bedsore_step',
  'nurse_bedsore_part',
  'nurse_ache',
  'nurse_ache_cancer',
  'nurse_ache_normal',
  'observe',
  'observe_orientation',
  'observe_memory',
  'observe_wender',
  'observe_hallucination',
  'observe_inappropriate',
  'observe_violent',
  'observe_eating',
  'observe_unrest',
  'observe_depressed',
  'family_inmate',
  'family_resource',
  'family_resource_sub',
  'welfare',
  'welfare_sub',
  'etc_daily',
  'etc_training',
  'etc_emotional',
  'etc_family_helper_mitigate'
];

$text_member = [
  'meal_etc',
  'digestion_etc',
  'walking_etc',
  'dementia_etc',
  'chronic_etc',
  'circulatory_etc',
  'nervous_etc',
  'musculoskeletal_etc',
  'mental_etc',
  'breath_etc',
  'kidney_etc',
  'other_etc1',
  'other_etc2',
  'body_content',
  'rehabilitation_content',
  'nurse_breath_etc',
  'nurse_nutrition_etc1',
  'nurse_nutrition_etc2',
  'nurse_wound_etc',
  'nurse_bedsore_part_etc',
  'nurse_ache_cancer_etc',
  'nurse_ache_normal_etc',
  'nurse_ache_etc',
  'nurse_content',
  'observe_eating_etc',
  'observe_content',
  'communication_content',
  'family_inmate_etc',
  'family_helper_relation_etc',
  'family_hospital_name',
  'family_hospital_tel',
  'family_hospital_medicine_day',
  'family_hospital_medicine_week',
  'family_hospital_medicine_amount',
  'family_religion_etc',
  'family_content',
  'living_content',
  'welfare_content',
  'etc_daily_etc',
  'etc_training_etc',
  'etc_emotional_etc',
  'etc_content',
  'total_review'
];

$number_member = [
  'family_children_son',
  'family_children_daughter'
];

$set = [];

foreach($radio_button_member as $key) {
  $val = get_search_string($_POST[$key]);
  $set[] = " $key = '$val' ";
}

foreach($multi_check_member as $key) {
  $val = $_POST[$key];
  if($val) {
    $val = sql_real_escape_string(implode(',', $val));
    $set[] = " $key = '$val' ";
  }
}

foreach($text_member as $key) {
  $val = sql_real_escape_string($_POST[$key]);
  $set[] = " $key = '$val' ";
}

foreach($number_member as $key) {
  $val = intval($_POST[$key]);
  $set[] = " $key = '$val' ";
}

$sql_set = implode(' , ', $set);

if($rd_id = get_search_string($_POST['rd_id'])) {
  //update
  $result = sql_query("
    UPDATE
      recipient_rec_detail
    SET
      {$sql_set},
      updated_at = NOW()
    WHERE
      rd_id = '{$rd_id}' and
      penId = '{$pen_id}' and
      mb_id = '{$member['mb_id']}'
  ");
} else {
  //insert
  $result = sql_query("
    INSERT INTO
      recipient_rec_detail
    SET
      penId = '{$pen_id}',
      mb_id = '{$member['mb_id']}',
      {$sql_set},
      created_at = NOW(),
      updated_at = NOW()
  ");
}

if(!$result) json_response(500, 'DB 오류 발생');

json_response(200, 'OK');
?>
