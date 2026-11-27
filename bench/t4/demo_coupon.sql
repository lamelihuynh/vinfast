-- Use on the local demo database. Safe to rerun: do not reset a used coupon.
INSERT INTO coupons (code, description, discount_type, discount_value, max_discount,
    min_order_amount, usage_limit, expires_at)
VALUES ('T4DEMO10', 'Mã thử checkout T4', 'percent', 10, 100000, 0, 100, now() + interval '30 days')
ON CONFLICT (upper(code)) DO NOTHING;
SELECT code, usage_limit, used_count, expires_at FROM coupons WHERE upper(code) = 'T4DEMO10';
