// =============================================================================
// VinFast hybrid DB — MongoDB indexes (baseline). Chủ file: T2 (Indexing).
// Chạy sau 001_collections.js:  mongosh "$MONGO_URI" --file database/mongo/002_indexes.js
// createIndex trùng tên + trùng key là no-op, nên chạy lại an toàn.
// =============================================================================

const dbName = (typeof process !== 'undefined' && process.env.MONGO_DB) || 'vinfast';
const target = db.getSiblingDB(dbName);

// categories
target.categories.createIndex({ categoryID: 1 }, { unique: true, name: 'uq_categoryID' });
target.categories.createIndex({ slug: 1 }, { unique: true, name: 'uq_category_slug' });

// products — khóa định danh
target.products.createIndex({ productID: 1 }, { unique: true, name: 'uq_productID' });
target.products.createIndex({ slug: 1 }, { unique: true, name: 'uq_product_slug' });
// variantId duy nhất toàn collection (khớp inventory.variant_id). Multikey + unique:
// không cho 2 product dùng chung variantId.
target.products.createIndex({ 'variants.variantId': 1 }, { unique: true, name: 'uq_variantId' });

// products — listing /products (quy tắc ESR: Equality, Sort, Range)
target.products.createIndex({ categoryID: 1, isActive: 1, createdAt: -1 }, { name: 'idx_cat_active_created' }); // thứ tự mặc định
target.products.createIndex({ categoryID: 1, isActive: 1, basePrice: 1 }, { name: 'idx_cat_active_price' });    // lọc/sắp giá
target.products.createIndex({ isActive: 1, totalSold: -1 }, { name: 'idx_active_bestseller' });                  // home "bán chạy"
target.products.createIndex({ 'attributes.rangeKm': 1 }, { name: 'idx_rangeKm', sparse: true });                 // lọc quãng đường

// products — tìm kiếm toàn văn (default_language none: không stem tiếng Việt)
target.products.createIndex(
  { name: 'text', description: 'text' },
  { name: 'txt_name_description', weights: { name: 10, description: 1 }, default_language: 'none' },
);

// userActivityLogs
target.userActivityLogs.createIndex({ userId: 1, timestamp: -1 }, { name: 'idx_user_time' });
target.userActivityLogs.createIndex({ actionType: 1, timestamp: -1 }, { name: 'idx_action_time' });
target.userActivityLogs.createIndex(
  { 'metadata.keyword': 1, timestamp: -1 },
  { name: 'idx_search_keyword', partialFilterExpression: { actionType: 'search' } },
);
// Tùy chọn: tự xóa log sau 180 ngày. Bật khi cả nhóm đồng ý (ảnh hưởng số liệu T3).
// target.userActivityLogs.createIndex({ timestamp: 1 }, { name: 'ttl_180d', expireAfterSeconds: 15552000 });

print('002_indexes.js done');
