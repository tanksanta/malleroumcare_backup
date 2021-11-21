<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function get_center_member_info_text($cm) {
    $info = [];
    $temp = '';
    if($cm['cm_sex'] == 1) {
      $temp .= '남';
    } else if($cm['cm_sex'] == 2) {
      $temp .= '여';
    }
    if($cm['cm_birth']) {
      $birth = new DateTime($cm['cm_birth']);
      $now = new DateTime();
      $interval = $now->diff($birth);
      $temp .= '(만 ' . $interval->y . '세)';
    }
    if($temp)
      $info[] = $temp;
    
    if($cm['cm_cont'] == 1) {
      $info[] = '정규직';
    } else if($cm['cm_cont'] == 2) {
      $info[] = '계약직';
    }
  
    if($cm['cm_type'] == 1) {
      $info[] = '일반직원';
    } else if($cm['cm_type'] == 2) {
      $info[] = '요양보호사';
    }

    return implode(' / ', $info);
}
