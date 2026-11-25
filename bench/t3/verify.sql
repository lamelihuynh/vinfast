-- bench/t3/verify.sql — T3-12: dashboard numbers recomputed by hand, to set beside /admin/dashboard
--   docker compose exec -T postgres psql -U vinfast -d vinfast -f /dev/stdin < bench/t3/verify.sql
-- Deliberately written differently from DashboardService (one plain COUNT per number,
-- GROUP BY without generate_series) so a shared mistake cannot hide in both.
-- The "Sản phẩm" header badge comes from Mongo: db.products.countDocuments({isActive: true}).

\echo '== Summary: header badges and stat cards =='
SELECT 'users'            AS metric, COUNT(*)::numeric AS value FROM users
UNION ALL SELECT 'orders_total',     COUNT(*) FROM orders
UNION ALL SELECT 'orders_pending',   COUNT(*) FROM orders WHERE status = 'pending'
UNION ALL SELECT 'orders_done',      COUNT(*) FROM orders WHERE status = 'done'
UNION ALL SELECT 'deposit_revenue',  COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> 'cancelled'
UNION ALL SELECT 'contacts_total',   COUNT(*) FROM contacts
UNION ALL SELECT 'contacts_unread',  COUNT(*) FROM contacts WHERE status = 'unread'
UNION ALL SELECT 'news_total',       COUNT(*) FROM news
UNION ALL SELECT 'comments_total',   COUNT(*) FROM comments
UNION ALL SELECT 'comments_pending', COUNT(*) FROM comments WHERE NOT is_approved;

\echo '== Monthly trend (last 6 months; a month missing here shows as 0 on the dashboard) =='
SELECT 'T' || EXTRACT(MONTH FROM date_trunc('month', created_at))::int AS label,
       to_char(created_at, 'YYYY-MM')                                 AS ym,
       COUNT(*)                                                       AS orders,
       COALESCE(SUM(total_amount) FILTER (WHERE status <> 'cancelled'), 0) AS revenue
FROM orders
WHERE created_at >= date_trunc('month', now()) - interval '5 months'
GROUP BY 1, 2
ORDER BY ym;

\echo '== Orders by model (top 6 by distinct orders; ties may order differently) =='
SELECT product_id, COUNT(DISTINCT order_id) AS orders
FROM order_items
GROUP BY product_id
ORDER BY orders DESC, product_id
LIMIT 6;

\echo '== Top selling, 30 days, cancelled excluded =='
SELECT oi.product_id, SUM(oi.quantity) AS qty, SUM(oi.unit_price * oi.quantity) AS revenue
FROM orders o, order_items oi
WHERE oi.order_id = o.id AND o.status <> 'cancelled' AND o.created_at >= now() - interval '30 days'
GROUP BY oi.product_id
ORDER BY qty DESC, oi.product_id
LIMIT 10;

\echo '== Low stock (available = stock - reserved <= 5) =='
SELECT product_id, variant_id, stock, reserved, stock - reserved AS available
FROM inventory
WHERE stock - reserved <= 5
ORDER BY available, product_id, variant_id
LIMIT 10;

\echo '== EXPLAIN low stock: the filter is an expression, so no index on stock can serve it =='
EXPLAIN (ANALYZE, BUFFERS, COSTS OFF)
SELECT product_id, variant_id, stock, reserved, stock - reserved AS available
FROM inventory
WHERE stock - reserved <= 5
ORDER BY available, product_id, variant_id
LIMIT 10;
