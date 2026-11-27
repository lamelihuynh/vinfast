-- =============================================================================
-- bench/t1/core/schema_bench.sql — Mô hình SQL chuẩn hóa tương đương Mongo Catalog (T1-18)
-- Chủ file: T1 (Data Storage). Chia sẻ tên bảng/cột cho T2 thử nghiệm index.
--
-- Lưu ý: Chỉ nạp vào DB `vinfast_bench`, KHÔNG đặt trong `database/postgres/`
--        vì ứng dụng web thực tế đọc catalog từ MongoDB.
--
-- Quy ước lưu thuộc tính EAV (`bench.product_attributes`):
--   * Số (int/float) -> lưu ở `value_num` (và chuỗi ở `value_text`).
--   * Boolean        -> `val_type = 'bool'`, `value_text = '1'|'0'`.
--   * Mảng (`list`, ví dụ `compatible_models`) -> `val_type = 'list'`, lưu JSON mảng
--     trong `value_text` để giữ khóa chính `(product_id, attr_key)` và khôi phục
--     chính xác 100% mảng gốc khi so sánh đối chiếu ở Thí nghiệm A.
-- =============================================================================

BEGIN;

DROP SCHEMA IF EXISTS bench CASCADE;
CREATE SCHEMA bench;

CREATE TABLE bench.category_attributes (
  category_id    INTEGER      NOT NULL,
  attr_key       VARCHAR(64)  NOT NULL,
  label          VARCHAR(120) NOT NULL,
  attr_type      VARCHAR(20)  NOT NULL,
  unit           VARCHAR(32),
  is_required    BOOLEAN      NOT NULL DEFAULT FALSE,
  is_filterable  BOOLEAN      NOT NULL DEFAULT FALSE,
  options        JSONB,
  PRIMARY KEY (category_id, attr_key)
);

CREATE TABLE bench.products_sql (
  product_id     VARCHAR(32)   PRIMARY KEY,
  category_id    INTEGER       NOT NULL,
  name           VARCHAR(200)  NOT NULL,
  slug           VARCHAR(200)  NOT NULL UNIQUE,
  description    TEXT,
  base_price     NUMERIC(15,0) NOT NULL CHECK (base_price >= 0),
  images         JSONB         NOT NULL DEFAULT '[]'::jsonb,
  attrs          JSONB         NOT NULL DEFAULT '{}'::jsonb, -- phục vụ biến thể pg_jsonb ở Thí nghiệm B
  is_active      BOOLEAN       NOT NULL DEFAULT TRUE,
  avg_rating     NUMERIC(3,2)  NOT NULL DEFAULT 0,
  total_reviews  INTEGER       NOT NULL DEFAULT 0,
  total_sold     INTEGER       NOT NULL DEFAULT 0,
  created_at     TIMESTAMPTZ   NOT NULL DEFAULT now(),
  updated_at     TIMESTAMPTZ
);

CREATE INDEX idx_bench_products_category ON bench.products_sql (category_id, is_active);

CREATE TABLE bench.product_variants (
  variant_id     VARCHAR(64)   PRIMARY KEY,
  product_id     VARCHAR(32)   NOT NULL REFERENCES bench.products_sql (product_id) ON DELETE CASCADE,
  name           VARCHAR(120)  NOT NULL,
  price          NUMERIC(15,0) NOT NULL CHECK (price >= 0),
  is_active      BOOLEAN       NOT NULL DEFAULT TRUE,
  attributes     JSONB         NOT NULL DEFAULT '{}'::jsonb,
  sort_order     SMALLINT      NOT NULL DEFAULT 0
);

CREATE INDEX idx_bench_variants_product ON bench.product_variants (product_id, sort_order);

CREATE TABLE bench.variant_colors (
  variant_id     VARCHAR(64)   NOT NULL REFERENCES bench.product_variants (variant_id) ON DELETE CASCADE,
  code           VARCHAR(40)   NOT NULL,
  name           VARCHAR(120)  NOT NULL,
  hex            VARCHAR(16),
  image_path     VARCHAR(300),
  surcharge      NUMERIC(15,0) NOT NULL DEFAULT 0 CHECK (surcharge >= 0),
  sort_order     SMALLINT      NOT NULL DEFAULT 0,
  is_default     BOOLEAN       NOT NULL DEFAULT FALSE,
  PRIMARY KEY (variant_id, code)
);

CREATE TABLE bench.product_attributes (
  product_id     VARCHAR(32)      NOT NULL REFERENCES bench.products_sql (product_id) ON DELETE CASCADE,
  attr_key       VARCHAR(64)      NOT NULL,
  val_type       VARCHAR(16)      NOT NULL DEFAULT 'str',
  value_text     TEXT,
  value_num      DOUBLE PRECISION,
  PRIMARY KEY (product_id, attr_key)
);

CREATE INDEX idx_bench_eav_key_num ON bench.product_attributes (attr_key, value_num) WHERE value_num IS NOT NULL;

COMMIT;
