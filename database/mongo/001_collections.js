// =============================================================================
// VinFast hybrid DB — MongoDB 7 collections + $jsonSchema validator. Chủ file: T1.
// Chạy:  mongosh "$MONGO_URI" --file database/mongo/001_collections.js
// DB lấy từ biến môi trường MONGO_DB (mặc định "vinfast").
// Chạy lại an toàn: collection đã có thì collMod cập nhật validator.
//
// validationLevel "moderate": doc cũ không hợp lệ vẫn sửa được, doc mới phải hợp lệ.
// additionalProperties để mở ở cấp gốc => thêm trường mới không phải sửa validator,
// nhưng attributes / dynamicAttributes vẫn là chỗ dành cho thuộc tính động.
// =============================================================================

const dbName = (typeof process !== 'undefined' && process.env.MONGO_DB) || 'vinfast';
const target = db.getSiblingDB(dbName);

function upsertCollection(name, validator) {
  const exists = target.getCollectionNames().includes(name);
  const opts = { validator, validationLevel: 'moderate', validationAction: 'error' };
  if (exists) {
    target.runCommand({ collMod: name, ...opts });
    print(`collMod  ${dbName}.${name}`);
  } else {
    target.createCollection(name, opts);
    print(`created  ${dbName}.${name}`);
  }
}

const nonEmpty = { bsonType: 'string', minLength: 1 };
const money = { bsonType: ['int', 'long', 'double', 'decimal'], minimum: 0 };
const dateT = { bsonType: 'date' };

// -----------------------------------------------------------------------------
// categories — thuộc tính động khai báo theo category
// -----------------------------------------------------------------------------
upsertCollection('categories', {
  $jsonSchema: {
    bsonType: 'object',
    required: ['categoryID', 'name', 'slug', 'isActive'],
    properties: {
      categoryID: { bsonType: ['int', 'long'], minimum: 1 },   // giữ id số cũ (1, 2)
      name: nonEmpty,
      slug: { bsonType: 'string', pattern: '^[a-z0-9]+(?:-[a-z0-9]+)*$' },
      description: { bsonType: ['string', 'null'] },
      isActive: { bsonType: 'bool' },
      dynamicAttributes: {          // thuộc tính cấp sản phẩm (products.attributes)
        bsonType: 'array',
        items: {
          bsonType: 'object',
          required: ['key', 'label', 'type'],
          properties: {
            key: { bsonType: 'string', pattern: '^[a-zA-Z][a-zA-Z0-9_]*$' },
            label: nonEmpty,
            type: { enum: ['string', 'number', 'boolean', 'enum', 'text'] },
            unit: { bsonType: ['string', 'null'] },
            required: { bsonType: 'bool' },
            filterable: { bsonType: 'bool' },
            options: { bsonType: 'array', items: { bsonType: 'string' } },
          },
        },
      },
      variantAttributes: {          // thuộc tính cấp variant (variants[].attributes)
        bsonType: 'array',
        items: {
          bsonType: 'object',
          required: ['key', 'label', 'type'],
          properties: {
            key: { bsonType: 'string', pattern: '^[a-zA-Z][a-zA-Z0-9_]*$' },
            label: nonEmpty,
            type: { enum: ['string', 'number', 'boolean', 'enum', 'text'] },
            unit: { bsonType: ['string', 'null'] },
            required: { bsonType: 'bool' },
            options: { bsonType: 'array', items: { bsonType: 'string' } },
          },
        },
      },
      createdAt: dateT,
      updatedAt: dateT,
    },
  },
});

// -----------------------------------------------------------------------------
// products — variants + colors + 5 review mới nhất embedded
// -----------------------------------------------------------------------------
const color = {
  bsonType: 'object',
  required: ['colorCode', 'colorName'],
  properties: {
    colorCode: nonEmpty,
    colorName: nonEmpty,
    colorHex: { bsonType: ['string', 'null'], pattern: '^#[0-9A-Fa-f]{3,8}$' },
    imagePath: { bsonType: ['string', 'null'] },
    surcharge: money,
    sortOrder: { bsonType: 'int' },
    isDefault: { bsonType: 'bool' },
  },
};

const variant = {
  bsonType: 'object',
  required: ['variantId', 'name', 'price', 'isActive'],
  properties: {
    variantId: nonEmpty,            // "{productID}-STD", "{productID}-PLUS"... = inventory.variant_id
    name: nonEmpty,
    price: money,
    isActive: { bsonType: 'bool' },
    attributes: { bsonType: 'object' },
    colors: { bsonType: 'array', items: color },
  },
};

const review = {
  bsonType: 'object',
  required: ['reviewId', 'userId', 'rating', 'createdAt'],
  properties: {
    reviewId: { bsonType: ['long', 'int'] },  // = product_reviews.id bên PG
    userId: { bsonType: ['long', 'int'] },    // = users.id bên PG
    userNameSnapshot: { bsonType: 'string' },
    rating: { bsonType: 'int', minimum: 1, maximum: 5 },
    comment: { bsonType: 'string' },
    images: { bsonType: 'array', items: { bsonType: 'string' } },
    createdAt: dateT,
    helpfulCount: { bsonType: 'int', minimum: 0 },
  },
};

upsertCollection('products', {
  $jsonSchema: {
    bsonType: 'object',
    required: ['productID', 'categoryID', 'name', 'slug', 'basePrice', 'isActive', 'variants', 'createdAt'],
    properties: {
      productID: { bsonType: 'string', pattern: '^[0-9]+$' },   // id số cũ dạng chuỗi ("165")
      categoryID: { bsonType: ['int', 'long'] },
      name: nonEmpty,
      slug: { bsonType: 'string', pattern: '^[a-z0-9]+(?:-[a-z0-9]+)*$' },
      description: { bsonType: ['string', 'null'] },
      basePrice: money,              // = giá variant rẻ nhất; dùng để lọc/sắp xếp theo giá
      images: { bsonType: 'array', items: { bsonType: 'string' } },
      isActive: { bsonType: 'bool' },
      attributes: {                  // specs cũ; key theo categories.dynamicAttributes
        bsonType: 'object',
        properties: {
          rangeKm: { bsonType: ['int', 'long', 'double', 'null'], minimum: 0 },
        },
      },
      variants: { bsonType: 'array', minItems: 1, items: variant },
      avgRating: { bsonType: ['double', 'int'], minimum: 0, maximum: 5 },
      totalReviews: { bsonType: ['int', 'long'], minimum: 0 },
      totalSold: { bsonType: ['int', 'long'], minimum: 0 },
      reviews: { bsonType: 'array', maxItems: 5, items: review },
      createdAt: dateT,
      updatedAt: dateT,
    },
  },
});

// -----------------------------------------------------------------------------
// userActivityLogs — log phụ (quyết định 7), ghi best-effort
// -----------------------------------------------------------------------------
upsertCollection('userActivityLogs', {
  $jsonSchema: {
    bsonType: 'object',
    required: ['actionType', 'timestamp'],
    properties: {
      userId: { bsonType: ['long', 'int', 'null'] },     // null = khách chưa đăng nhập
      sessionId: { bsonType: ['string', 'null'] },
      targetId: { bsonType: ['string', 'null'] },        // productID / orderId dạng chuỗi
      actionType: {
        enum: [
          'view_product', 'search', 'add_to_cart', 'remove_from_cart',
          'add_to_wishlist', 'remove_from_wishlist', 'place_order', 'deposit',
          'apply_coupon', 'review',
        ],
      },
      timestamp: dateT,
      metadata: {
        bsonType: 'object',
        properties: {
          source: { bsonType: ['string', 'null'] },
          keyword: { bsonType: ['string', 'null'] },
          device: { bsonType: ['string', 'null'] },
          page: { bsonType: ['string', 'null'] },
          customData: { bsonType: ['object', 'null'] },
        },
      },
    },
  },
});

// -----------------------------------------------------------------------------
// counters — sinh productID / categoryID mới (quyết định 1)
//   db.counters.findOneAndUpdate({_id:'productID'}, {$inc:{seq:1}}, {upsert:true, returnDocument:'after'})
// -----------------------------------------------------------------------------
upsertCollection('counters', {
  $jsonSchema: {
    bsonType: 'object',
    required: ['_id', 'seq'],
    properties: {
      _id: { enum: ['productID', 'categoryID'] },
      seq: { bsonType: ['int', 'long'], minimum: 0 },
    },
  },
});
// Seed/migrate phải đặt seq = max id cũ sau khi import.
for (const id of ['productID', 'categoryID']) {
  target.counters.updateOne({ _id: id }, { $setOnInsert: { seq: 0 } }, { upsert: true });
}

print('001_collections.js done');
