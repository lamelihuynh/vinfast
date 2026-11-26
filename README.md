# VinFast Electric Vehicle Website 

A full business website for VinFast

> **Chạy trên máy local:** cần Git + Docker Desktop, rồi `git clone` → `git checkout develop-hybrid` → `docker compose up -d --build` → mở http://localhost:8080. Chi tiết ở mục [Cài đặt và chạy trên máy local](#cài-đặt-và-chạy-trên-máy-local).
---
## Cấu trúc thư mục

```
vinfast/
├── index.php                    Entry cho khách (route công khai)
├── admin.php                    Entry cho admin, chặn bằng Auth::requireAdmin()
├── .htaccess                    /admin/* → admin.php, còn lại → index.php
├── Dockerfile                   Image PHP 8.2 + Apache + pdo_pgsql + mongodb
├── docker-compose.yml           postgres, mongo, mongo-init, seed, app
├── docker-compose.bench.yml     k6 + cấu hình PostgreSQL cho benchmark
├── composer.json                mongodb/mongodb
├── .env.example                 Mẫu biến môi trường (copy thành .env)
│
├── config/
│   ├── bootstrap.php            Nạp hằng số, autoload, helper, session cho cả 2 entry
│   ├── database.php             Thông số kết nối PostgreSQL + MongoDB
│   └── locations.php            Danh sách tỉnh/showroom
│
├── app/
│   ├── core/
│   │   ├── Db.php               Db::pg() (PDO), Db::mongo(), Db::transaction()
│   │   └── Exceptions.php       OutOfStockException, …
│   ├── services/
│   │   └── InventoryService.php Tồn kho theo variant (stock / reserved)
│   ├── models/                  Model tĩnh, dùng chung frontend + admin
│   │   ├── Product.php  Category.php           MongoDB (products, categories)
│   │   ├── ActivityLog.php                     MongoDB (userActivityLogs)
│   │   ├── User.php  Order.php  TestDrive.php  PostgreSQL
│   │   ├── News.php  Comment.php  Contact.php  PostgreSQL
│   │   ├── Faq.php  FaqTopic.php  SiteSetting.php
│   │   └── AboutAward.php  PageAsset.php       Nội dung trang About / website
│   ├── controllers/
│   │   ├── frontend/            Home, About, Faq, Contact, Product, Order, News,
│   │   │                        Comment, Auth, User
│   │   └── admin/               Dashboard, Product, Order, User, News, Comment,
│   │                            Contact, Faq, FaqTopic, PageContent, Settings
│   ├── helpers/                 Auth, Validator, Upload, Pagination, SEO, View,
│   │                            AssetHelper, ImageHelper, ProductViewHelper,
│   │                            CheckoutViewHelper
│   └── views/
│       ├── frontend/            layouts, partials, home, about, faq, contact,
│       │                        products, order, news, auth, user
│       └── admin/               layouts, partials, dashboard, products, orders,
│                                users, news, comments, contacts, faq, faq-topic,
│                                pages, settings
│
├── public/
│   ├── css/  js/                frontend/ và admin/
│   ├── libs/srtdash/            Template trang admin
│   └── images/
│       ├── products/            Ảnh xe
│       ├── accessories/         Ảnh phụ kiện (pin-sac, thoi-trang, phu-kien-xe)
│       ├── banners/  news/  logo/  avatars/
│       └── uploads/             File upload lúc chạy (không commit)
│
├── database/
│   ├── postgres/                DDL: 001_schema, 002_indexes, 004_coupons
│   ├── mongo/                   001_collections (validator), 002_indexes
│   ├── docker/mongo-init.sh     Khởi tạo replica set rs0 + chạy script mongo/
│   ├── seed/                    seed.php, generate.php, accessories.php,
│   │                            legacy_content.php, legacy_data.json
│   └── *.sql                    Bản MySQL cũ, chỉ để tham khảo
│
├── bench/                       Kịch bản benchmark từng topic (t1 … t5), results/
│
└── docs/
    ├── hybrid-db-plan.html      Kế hoạch chuyển sang PostgreSQL + MongoDB
    ├── topics/                  Task từng topic: t1-storage, t2-indexing, t3-query,
    │                            t4-transaction, t5-concurrency
    └── flowcharts/
```

---

## Cài đặt và chạy trên máy local

App chạy hoàn toàn trong Docker: PHP 8.2 + Apache, PostgreSQL 16, MongoDB 7. **Không cần cài PHP, MySQL, PostgreSQL hay MongoDB** trên máy.

### 1. Cần có
- [Git](https://git-scm.com/downloads)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows: bật WSL 2 backend khi cài). Mở Docker Desktop và chờ trạng thái *Engine running* trước khi chạy lệnh.
- Khoảng 3 GB dung lượng trống cho image và dữ liệu.

### 2. Clone và chạy

```bash
git clone https://github.com/lamelihuynh/vinfast.git
cd vinfast
git checkout develop-hybrid
docker compose up -d --build
```

Lần đầu mất khoảng 3–5 phút (tải image, cài extension PHP, composer). Lệnh tự làm theo thứ tự:

1. `postgres`, `mongo` khởi động; `mongo` tự khởi tạo replica set `rs0` (cần cho transaction).
2. `mongo-init` tạo collection, validator, index (`database/mongo/*.js`).
3. `seed` tạo schema PostgreSQL (`database/postgres/*.sql`) và nạp dữ liệu mẫu. Chỉ chạy khi DB còn trống, nên các lần `up` sau không ghi đè dữ liệu.
4. `app` chạy website.

Kiểm tra: `docker compose ps` — `postgres`, `mongo`, `app` ở trạng thái *running/healthy*; `mongo-init`, `seed` ở trạng thái *exited (0)*.

| | Địa chỉ |
|---|---|
| Website | http://localhost:8080 |
| Admin | http://localhost:8080/admin/dashboard |

**Tài khoản test** (chỉ dùng local, tạo bởi `database/seed/seed.php`):

| Email | Mật khẩu | Vai trò |
|---|---|---|
| `admin@vinfast.vn` | `Admin@123` | admin |
| `member@vinfast.vn` | `Member@123` | member |

### 3. Dữ liệu mẫu có sẵn
- 16 xe (2 danh mục) và 44 phụ kiện (Pin & Sạc, Thời trang, Phụ kiện xe) lấy từ shop.vinfastauto.com: tên, giá, mã SKU, thông số và ảnh thật (xem đầu file `database/seed/accessories.php`). Tồn kho là số sinh ngẫu nhiên.
- Tin tức, FAQ, trang Giới thiệu, cài đặt website, liên hệ, lái thử: lấy từ DB production cũ (`database/seed/legacy_data.json`; tài khoản người dùng thật đã được loại bỏ).
- 3 đơn đặt cọc mẫu của `member@vinfast.vn`.

Dữ liệu lớn cho benchmark (10k / 100k sản phẩm, đơn hàng, review, log…): xem [database/seed/README.md](database/seed/README.md) và [bench/README.md](bench/README.md).

### 4. Làm việc hằng ngày

| Việc | Lệnh |
|---|---|
| Bật / tắt | `docker compose up -d` / `docker compose stop` |
| Sửa code PHP, view, JS, CSS | Lưu file rồi tải lại trang (thư mục project được mount vào container) |
| Sửa `Dockerfile` hoặc `composer.json` | `docker compose up -d --build` |
| Xem log PHP / Apache | `docker compose logs -f app` |
| Reset dữ liệu về seed ban đầu | `docker compose run --rm seed php database/seed/seed.php --force` |
| Xóa sạch mọi thứ (cả volume DB) | `docker compose down -v` |
| Chạy lệnh PHP trong container | `docker compose exec app php -v` |
| Mở psql | `docker compose exec postgres psql -U vinfast` |
| Mở mongosh | `docker compose exec mongo mongosh vinfast` |

Kết nối DB bằng công cụ trên máy (DBeaver, pgAdmin, Compass…):

| | Kết nối |
|---|---|
| PostgreSQL | host `localhost`, port `5433`, db `vinfast`, user `vinfast`, password `vinfast` |
| MongoDB | `mongodb://localhost:27018/?directConnection=true`, db `vinfast` |

### 5. Lỗi thường gặp

| Hiện tượng | Cách xử lý |
|---|---|
| `Bind for 0.0.0.0:8080 failed: port is already allocated` (hoặc 5433, 27018) | Copy `.env.example` thành `.env`, đổi `APP_PORT` / `PG_HOST_PORT` / `MONGO_HOST_PORT`, rồi `docker compose up -d` |
| `seed` hoặc `mongo-init` báo lỗi, `app` không lên | `docker compose logs seed mongo-init` để xem lỗi; sửa xong chạy `docker compose up -d` lại |
| `mongo` không healthy | Chờ thêm 30 giây (lần đầu khởi tạo replica set). Vẫn lỗi: `docker compose down -v` rồi `up` lại |
| `bash\r: not found` / `$'\r': command not found` | File `.sh` bị đổi sang CRLF. Repo đã có `.gitattributes`; chạy `git rm --cached -r . && git reset --hard` để checkout lại đúng LF |
| Trang trắng hoặc lỗi 500 | `docker compose logs app` |
| Sau khi pull code mới có thay đổi schema (`database/postgres`, `database/mongo`) | `docker compose run --rm mongo-init` rồi reset dữ liệu bằng lệnh `seed ... --force` |

### 6. Cấu trúc liên quan đến DB

```
docker-compose.yml          postgres, mongo, mongo-init, seed, app
docker-compose.bench.yml    k6 + cấu hình PostgreSQL cho benchmark
app/core/Db.php             Db::pg() (PDO), Db::mongo(), Db::transaction()
app/services/               InventoryService (tồn kho, hợp đồng T4↔T5) …
database/postgres/          DDL: 001_schema, 002_indexes, 004_coupons
database/mongo/             001_collections (validator), 002_indexes
database/seed/              seed.php, generate.php, legacy_data.json, accessories.php
bench/                      benchmark từng topic (t1 … t5)
docs/                       kế hoạch hybrid DB và task từng topic
```

- PostgreSQL: người dùng, đơn hàng, giỏ, wishlist, tồn kho, coupon, review, nội dung website.
- MongoDB: `categories` (có `kind` = `vehicle` | `accessory`, thuộc tính động), `products` (phiên bản, màu, 5 review mới nhất), `userActivityLogs`.
- `product_id` / `variant_id` bên PostgreSQL tham chiếu logic (không FK) sang `products.productID` / `variants.variantId` bên Mongo; đơn hàng lưu snapshot tên và giá.

> Cách cài cũ bằng XAMPP + MySQL (`database/schema.sql`, `products.sql`) **không còn dùng** cho nhánh này. Các file đó chỉ giữ lại để tham khảo.
