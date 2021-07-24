INSERT INTO
	tutorial (mb_id, t_type, t_state, t_data)
SELECT
    mb_id,
    'recipient_add' as t_type,
    TRUE as t_state,
    null as t_data
FROM g5_member
;

INSERT INTO
	tutorial (mb_id, t_type, t_state, t_data)
SELECT
    mb_id,
    'recipient_order' as t_type,
    TRUE as t_state,
    null as t_data
FROM g5_member
;


INSERT INTO
	tutorial (mb_id, t_type, t_state, t_data)
SELECT
    mb_id,
    'document' as t_type,
    TRUE as t_state,
    null as t_data
FROM g5_member
;


INSERT INTO
	tutorial (mb_id, t_type, t_state, t_data)
SELECT
    mb_id,
    'claim' as t_type,
    TRUE as t_state,
    null as t_data
FROM g5_member
;

