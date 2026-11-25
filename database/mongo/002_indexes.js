// =============================================================================
// VinFast hybrid DB — MongoDB indexes (baseline). Chủ file: T2 (Indexing).
// Chạy sau 001_collections.js:  mongosh "$MONGO_URI" --file database/mongo/002_indexes.js
// createIndex trùng tên + trùng key là no-op, nên chạy lại an toàn.
// =============================================================================

const dbName = (typeof process !== 'undefined' && process.env.MONGO_DB) || 'vinfast';
const target = db.getSiblingDB(dbName);

function dropIndexIfExists(col, name) {
  if (col.getIndexes().some((idx) => idx.name === name)) {
    col.dropIndex(name);
  }
}

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
// T2-24 (Phương án A): thêm tie-breaker productID: 1 vào cuối index để khớp hoàn toàn
// Product::catalogSort() ({createdAt: -1, productID: 1} và {basePrice: 1, productID: 1}),
// loại bỏ stage blocking SORT trong RAM cho Q4 (mặc định), Q1 (price_asc) và navbar getByCategory().
// Xóa 2 index cũ thiếu productID để không giữ index thừa gây write amplification.
// Ghi chú price_desc ({basePrice: -1, productID: 1}): đọc ngược idx_cat_active_price_pid cho
// {basePrice: -1, productID: -1}; giữ 1 index để giảm chi phí ghi vì price_asc là luồng chính Q1.
dropIndexIfExists(target.products, 'idx_cat_active_created');
dropIndexIfExists(target.products, 'idx_cat_active_price');
target.products.createIndex({ categoryID: 1, isActive: 1, createdAt: -1, productID: 1 }, { name: 'idx_cat_active_created_pid' }); // thứ tự mặc định
target.products.createIndex({ categoryID: 1, isActive: 1, basePrice: 1, productID: 1 }, { name: 'idx_cat_active_price_pid' });    // lọc/sắp giá
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
