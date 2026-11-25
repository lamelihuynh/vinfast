-- bench/t3/admin_orders.sql — T3-06: /admin/orders before vs after
--   docker compose exec -T postgres psql -U vinfast -d vinfast_bench -f /dev/stdin < bench/t3/admin_orders.sql
-- "old" is the pre-T3-06 SQL, kept here verbatim so the two plans can be compared on the same data.
-- Old page = 3 queries (countAdminList + getAdminList + countAdminPaymentSummary); new page = 2.

\set q '%vf%'
\set q2 '%Thảm sàn 3D VF 9 Signature%'

-- Warm the cache so neither side pays for disk reads.
SELECT COUNT(*) FROM orders o JOIN order_items oi ON oi.order_id = o.id JOIN users u ON u.id = o.user_id;

\echo '== 1. Correctness: matching only the first item misses orders found via later items =='
SELECT
  (SELECT COUNT(*) FROM orders o
     LEFT JOIN LATERAL (SELECT oi.product_name_snapshot AS product_name FROM order_items oi
                        WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1) p ON TRUE
     JOIN users u ON o.user_id = u.id
    WHERE p.product_name ILIKE :'q2') AS old_first_item,
  (SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id
    WHERE EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id
                  AND oi.product_name_snapshot ILIKE :'q2')) AS new_any_item;

\echo '== 2a. OLD count (countAdminList / summary shape): LATERAL first item + ILIKE on it =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT COUNT(*) AS total,
       SUM(CASE WHEN o.payment_status = 'paid' THEN 1 ELSE 0 END) AS paid
FROM orders o
LEFT JOIN LATERAL (SELECT oi.product_name_snapshot AS product_name FROM order_items oi
                   WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1) p ON TRUE
JOIN users u ON o.user_id = u.id
WHERE (u.name ILIKE :'q' OR u.email ILIKE :'q' OR p.product_name ILIKE :'q' OR o.id::text LIKE :'q');

\echo '== 2b. NEW summary (countAdminPaymentSummary): EXISTS over all items, FILTER, no LATERAL =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT COUNT(*) AS total,
       COUNT(*) FILTER (WHERE o.payment_status = 'paid') AS paid
FROM orders o
JOIN users u ON o.user_id = u.id
WHERE (u.name ILIKE :'q' OR u.email ILIKE :'q'
       OR EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.product_name_snapshot ILIKE :'q')
       OR o.id::text LIKE :'q');

\echo '== 3a. OLD list page (getAdminList, page 1) =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id, o.status, o.total_amount, p.product_name, u.name
FROM orders o
LEFT JOIN LATERAL (SELECT oi.product_name_snapshot AS product_name FROM order_items oi
                   WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1) p ON TRUE
JOIN users u ON o.user_id = u.id
WHERE (u.name ILIKE :'q' OR u.email ILIKE :'q' OR p.product_name ILIKE :'q' OR o.id::text LIKE :'q')
ORDER BY o.created_at DESC, o.id DESC LIMIT 5 OFFSET 0;

\echo '== 3b. NEW list page: EXISTS filter + item_count in the LATERAL =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id, o.status, o.total_amount, p.product_name, p.item_count, u.name
FROM orders o
LEFT JOIN LATERAL (SELECT oi.product_name_snapshot AS product_name, COUNT(*) OVER() AS item_count
                   FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1) p ON TRUE
JOIN users u ON o.user_id = u.id
WHERE (u.name ILIKE :'q' OR u.email ILIKE :'q'
       OR EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.product_name_snapshot ILIKE :'q')
       OR o.id::text LIKE :'q')
ORDER BY o.created_at DESC, o.id DESC LIMIT 5 OFFSET 0;

\echo '== 4. No search term: the common case of opening the page =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT COUNT(*) FROM orders o
LEFT JOIN LATERAL (SELECT oi.product_name_snapshot FROM order_items oi
                   WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1) p ON TRUE
JOIN users u ON o.user_id = u.id;
EXPLAIN (ANALYZE, BUFFERS)
SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id;
