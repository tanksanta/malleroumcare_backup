UPDATE g5_shop_order SET
    od_send_cost = (SELECT SUM(ct_sendcost) FROM g5_shop_cart as c WHERE c.od_id = o.od_id)
    FROM (SELECT * FROM g5_shop_order) as o
    WHERE od_id = o.od_id