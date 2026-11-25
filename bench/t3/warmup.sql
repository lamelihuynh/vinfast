-- bench/t3/warmup.sql — T3-25: EXPLAIN, 3 join algorithms, CTE, window function
-- Run on vinfast_bench: the dev DB has only 3 orders, too few for the planner to pick anything but Nested Loop.
--   docker compose exec -T postgres psql -U vinfast -d vinfast_bench -f /dev/stdin < bench/t3/warmup.sql
-- SELECT only, so EXPLAIN ANALYZE is safe to run.

\echo '== 0. Data size =='
SELECT (SELECT COUNT(*) FROM orders) AS orders, (SELECT COUNT(*) FROM order_items) AS order_items;
ANALYZE orders;
ANALYZE order_items;

-- Warm the cache so the first measurement is not paying for disk reads.
SELECT COUNT(*) FROM orders o JOIN order_items oi ON oi.order_id = o.id;

\echo '== 1a. Top 10 best-selling products: chosen by the planner =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT oi.product_id, SUM(oi.quantity) AS qty
FROM orders o JOIN order_items oi ON oi.order_id = o.id
WHERE o.status <> 'cancelled'
GROUP BY oi.product_id ORDER BY qty DESC LIMIT 10;

\echo '== 1b. Hash Join disabled -> usually Merge Join =='
SET enable_hashjoin = off;
EXPLAIN (ANALYZE, BUFFERS)
SELECT oi.product_id, SUM(oi.quantity) AS qty
FROM orders o JOIN order_items oi ON oi.order_id = o.id
WHERE o.status <> 'cancelled'
GROUP BY oi.product_id ORDER BY qty DESC LIMIT 10;

\echo '== 1c. Merge Join also disabled -> only Nested Loop left =='
SET enable_mergejoin = off;
EXPLAIN (ANALYZE, BUFFERS)
SELECT oi.product_id, SUM(oi.quantity) AS qty
FROM orders o JOIN order_items oi ON oi.order_id = o.id
WHERE o.status <> 'cancelled'
GROUP BY oi.product_id ORDER BY qty DESC LIMIT 10;

-- Otherwise the disabled join methods leak into every later measurement.
RESET ALL;

\echo '== 2a. Plain CTE (PG 12+ inlines it into the outer query) =='
EXPLAIN (ANALYZE, BUFFERS)
WITH valid AS (SELECT id FROM orders WHERE status <> 'cancelled')
SELECT COUNT(*) FROM order_items oi JOIN valid v ON v.id = oi.order_id;

\echo '== 2b. CTE MATERIALIZED (computed into a temp result first, then joined) =='
EXPLAIN (ANALYZE, BUFFERS)
WITH valid AS MATERIALIZED (SELECT id FROM orders WHERE status <> 'cancelled')
SELECT COUNT(*) FROM order_items oi JOIN valid v ON v.id = oi.order_id;

\echo '== 3. Window function: top 3 products per month (result) =='
WITH monthly AS (
  SELECT date_trunc('month', o.created_at) AS m, oi.product_id,
         MIN(oi.product_name_snapshot) AS name, SUM(oi.quantity) AS qty
  FROM orders o JOIN order_items oi ON oi.order_id = o.id
  WHERE o.status <> 'cancelled'
  GROUP BY 1, 2)
SELECT to_char(m, 'YYYY-MM') AS ym, name, qty, rn FROM (
  SELECT m, name, qty, ROW_NUMBER() OVER (PARTITION BY m ORDER BY qty DESC, name) AS rn
  FROM monthly) t
WHERE rn <= 3 ORDER BY m, rn;

\echo '== 3b. Check: no month has more than 3 rows (expect 0 rows) =='
WITH monthly AS (
  SELECT date_trunc('month', o.created_at) AS m, oi.product_id, SUM(oi.quantity) AS qty
  FROM orders o JOIN order_items oi ON oi.order_id = o.id
  WHERE o.status <> 'cancelled'
  GROUP BY 1, 2),
ranked AS (
  SELECT m, ROW_NUMBER() OVER (PARTITION BY m ORDER BY qty DESC, product_id) AS rn FROM monthly)
SELECT m, COUNT(*) FROM ranked WHERE rn <= 3 GROUP BY m HAVING COUNT(*) > 3;

\echo '== 3c. Plan of the window function query =='
EXPLAIN (ANALYZE, BUFFERS)
WITH monthly AS (
  SELECT date_trunc('month', o.created_at) AS m, oi.product_id,
         MIN(oi.product_name_snapshot) AS name, SUM(oi.quantity) AS qty
  FROM orders o JOIN order_items oi ON oi.order_id = o.id
  WHERE o.status <> 'cancelled'
  GROUP BY 1, 2)
SELECT to_char(m, 'YYYY-MM') AS ym, name, qty, rn FROM (
  SELECT m, name, qty, ROW_NUMBER() OVER (PARTITION BY m ORDER BY qty DESC, name) AS rn
  FROM monthly) t
WHERE rn <= 3 ORDER BY m, rn;

-- ---------------------------------------------------------------------------
-- T3-05 queries (Order.php), measured on 10k data
-- ---------------------------------------------------------------------------
-- A constant, not a subquery: the planner cannot see a subquery's value, so it
-- misestimates the rows (6 vs 35) and picks Bitmap Heap Scan instead of Index Scan.
SELECT user_id AS uid FROM orders GROUP BY user_id ORDER BY COUNT(*) DESC, user_id LIMIT 1 \gset

\echo '== 4a. T3-05 order list of one user, with COUNT(*) OVER() (expect Index Scan idx_orders_user_created) =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id, o.type, o.status, o.payment_status, o.total_amount, o.created_at,
       COUNT(*) OVER() AS total_rows
FROM orders o
WHERE o.user_id = :uid
ORDER BY o.created_at DESC, o.id DESC
LIMIT 10 OFFSET 0;

\echo '== 4a2. Same query without COUNT(*) OVER(): Limit stops early after 10 rows =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id, o.type, o.status, o.payment_status, o.total_amount, o.created_at
FROM orders o
WHERE o.user_id = :uid
ORDER BY o.created_at DESC, o.id DESC
LIMIT 10 OFFSET 0;

\echo '== 4b. T3-05 items of several orders via = ANY(...) =='
EXPLAIN (ANALYZE, BUFFERS)
SELECT oi.order_id, oi.product_id, oi.variant_id, oi.product_name_snapshot,
       oi.variant_name_snapshot, oi.color_name_snapshot, oi.unit_price, oi.quantity, oi.subtotal
FROM order_items oi
WHERE oi.order_id = ANY(CAST('{1,2,3,4,5,6,7,8,9,10}' AS bigint[]))
ORDER BY oi.order_id, oi.id;

\echo '== 5. T3-23 contact counts per status: a single FILTER query =='
EXPLAIN (ANALYZE)
SELECT COUNT(*)                                   AS "all",
       COUNT(*) FILTER (WHERE status = 'unread')  AS unread,
       COUNT(*) FILTER (WHERE status = 'read')    AS "read",
       COUNT(*) FILTER (WHERE status = 'replied') AS replied
FROM contacts;
