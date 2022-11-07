<?php

use function _\orderBy;

function order_by($data, $select, $order, $group) {
    // mysql로 SELECT
    function group_by($rows, $column_name) {
        $result = [];
        $groups = distinct($rows, $column_name);
        foreach($groups as $group) {
            $result[$group] = where($rows, [$column_name=>$group]);
        }
        return $result;
    }

    function distinct($rows, $column_name) {
        $column_values = [];
        foreach($rows as $row) {
            $column_values[$row[$column_name]] = 1;
        }
        return array_keys($column_values);
    }
    
    function where($rows, $params) {
        $result = [];
        foreach($rows as $row) {
            $row_matched = true;
            foreach($params as $column_name => $column_value) {
                if( !array_key_exists($column_name, $row) 
                || $row[$column_name] != $column_value ) {
                    $row_matched = false;
                    break;
                }
            }
            if( $row_matched ) $result[] = $row;
        }
        return $result;
    }
    return group_by(orderBy($data, $select, $order), $group);
}
?>