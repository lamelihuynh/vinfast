-- =============================================================================
-- VinFast hybrid DB — PostgreSQL indexes (baseline). Chủ file: T2 (Indexing).
-- Chạy sau 001_schema.sql. Index đã có sẵn nhờ PK/UNIQUE ở 001 KHÔNG lặp lại ở đây:
--   inventory(product_id, variant_id), carts(user_id), cart_items(cart_id, product_id, variant_id),
--   wishlist_items(wishlist_id, product_id, variant_id), product_reviews(user_id, product_id),
--   page_assets(page_type, asset_key), lower(users.email).
-- T2 thêm/bớt index thí nghiệm trong bench/t2/, không sửa baseline khi chưa đo.
-- =============================================================================

BEGIN;

-- FK lookup (PG không tự tạo index cho cột FK)
CREATE INDEX idx_faqs_topic            ON faqs (topic_id, sort_order);
CREATE INDEX idx_comments_news         ON comments (news_id, is_approved, created_at DESC);
CREATE INDEX idx_comments_user         ON comments (user_id);
CREATE INDEX idx_order_items_order     ON order_items (order_id);
CREATE INDEX idx_wishlist_items_product ON wishlist_items (product_id);  -- "ai đang thích SP này"
CREATE INDEX idx_test_drives_user      ON test_drives (user_id) WHERE user_id IS NOT NULL;

-- Lịch sử đơn của user (T3) + admin lọc đơn
CREATE INDEX idx_orders_user_created   ON orders (user_id, created_at DESC);
CREATE INDEX idx_orders_status_created ON orders (status, created_at DESC);
CREATE INDEX idx_orders_created        ON orders (created_at);                -- dashboard theo tháng
CREATE INDEX idx_orders_payment_status ON orders (payment_status);

-- Dashboard: sản phẩm bán chạy
CREATE INDEX idx_order_items_product   ON order_items (product_id, variant_id);

-- Review sản phẩm: trang chi tiết + hàng chờ duyệt
CREATE INDEX idx_reviews_product_approved ON product_reviews (product_id, created_at DESC) WHERE is_approved;
CREATE INDEX idx_reviews_pending          ON product_reviews (created_at) WHERE NOT is_approved;

-- Admin: liên hệ / lái thử theo trạng thái
CREATE INDEX idx_contacts_status_created    ON contacts (status, created_at DESC);
CREATE INDEX idx_test_drives_status_created ON test_drives (status, created_at DESC);

-- Tin tức: danh sách theo mục + trạng thái, xem nhiều
CREATE INDEX idx_news_state_catalog_created ON news (news_state, catalog, created_at DESC);
CREATE INDEX idx_news_views                 ON news (views DESC);

COMMIT;
