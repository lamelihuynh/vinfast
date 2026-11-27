-- PostgreSQL, read-only: verify the new rows from the web demo.
SELECT o.id, o.type, o.payment_status, o.subtotal_amount, o.discount_amount, o.total_amount,
       c.code, r.order_id IS NOT NULL AS redeemed, count(i.id) AS items, o.idempotency_key
FROM orders o
JOIN order_items i ON i.order_id = o.id
LEFT JOIN coupons c ON c.id = o.coupon_id
LEFT JOIN coupon_redemptions r ON r.order_id = o.id
WHERE o.idempotency_key IS NOT NULL
GROUP BY o.id, c.code, r.order_id
ORDER BY o.id DESC LIMIT 10;

SELECT c.code, c.usage_limit, c.used_count, count(r.id) AS stored_redemptions
FROM coupons c LEFT JOIN coupon_redemptions r ON r.coupon_id = c.id
WHERE upper(c.code) = 'T4DEMO10'
GROUP BY c.id;
