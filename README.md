# VinFast Electric Vehicle Website 

A full business website for VinFast

> **Chạy trên máy local:** cần Git + Docker Desktop, rồi `git clone` → `git checkout develop-hybrid` → `docker compose up -d --build` → mở http://localhost:8080. Chi tiết ở mục [Cài đặt và chạy trên máy local](#cài-đặt-và-chạy-trên-máy-local).
---
## Project Structure

```
vinfast/
├── index.php                    Customer entry point (public routes)
├── admin.php                    Admin entry point — Auth::requireAdmin() gate here
├── .htaccess                    /admin/* → admin.php  |  rest → index.php
│
├── config/
│   ├── bootstrap.php            Loaded by BOTH entry points: constants + DB + helpers + session
│   └── database.php             PDO connection ($pdo global)
│
├── app/
│   ├── controllers/
│   │   ├── frontend/            Customer-facing controllers
│   │   │   ├── AuthController.php         [Common]  login / register / logout
│   │   │   ├── UserController.php         [Common]  profile / password / orders
│   │   │   ├── HomeController.php         [Tang Vu]     GET /
│   │   │   ├── ContactController.php      [Tang Vu]     GET+POST /contact
│   │   │   ├── AboutController.php        [Nhat Linh]   GET /about
│   │   │   ├── FaqController.php          [Nhat Linh]   GET /faq
│   │   │   ├── ProductController.php      [Hai Nam]     GET /products  /products/detail/{id}
│   │   │   ├── NewsController.php         [Nhat Tan]    GET /news  /news/read/{slug}
│   │   │   └── CommentController.php      [Nhat Tan]    POST /comment/post
│   │   │
│   │   └── admin/               Admin-only controllers (all protected by admin.php gate)
│   │       ├── DashboardAdminController.php  [Common]   GET /admin/dashboard
│   │       ├── UserAdminController.php       [Common]   /admin/users  CRUD
│   │       ├── SettingsAdminController.php   [Tang Vu]  /admin/settings
│   │       ├── ContactAdminController.php    [Tang Vu]  /admin/contacts
│   │       ├── FaqAdminController.php        [Nhat Linh]/admin/faq  CRUD
│   │       ├── ProductAdminController.php    [Hai Nam]  /admin/products  CRUD
│   │       ├── OrderAdminController.php      [Hai Nam]  /admin/orders
│   │       ├── NewsAdminController.php       [Nhat Tan] /admin/news  CRUD
│   │       └── CommentAdminController.php    [Nhat Tan] /admin/comments  moderation
│   │
│   ├── models/                  Shared between frontend and admin
│   │   ├── User.php             [Common]
│   │   ├── SiteSetting.php      [Tang Vu]
│   │   ├── Contact.php          [Tang Vu]
│   │   ├── Faq.php              [Nhat Linh]
│   │   ├── Category.php         [Hai Nam]
│   │   ├── Product.php          [Hai Nam]
│   │   ├── Order.php            [Hai Nam]
│   │   ├── News.php             [Nhat Tan]
│   │   └── Comment.php          [Nhat Tan]
│   │
│   ├── views/
│   │   ├── frontend/            Customer-facing views
│   │   │   ├── layouts/
│   │   │   │   ├── main.php     [Common]  public layout (navbar + footer + Bootstrap)
│   │   │   │   └── auth.php     [Common]  minimal centred layout for login/register
│   │   │   ├── partials/
│   │   │   │   ├── navbar.php       [Common]
│   │   │   │   ├── footer.php       [Tang Vu]
│   │   │   │   ├── flash.php        [Common]  success/error messages
│   │   │   │   └── pagination.php   [Common]  reusable page links
│   │   │   ├── home/
│   │   │   │   └── index.php        [Tang Vu]    Swiper banners + featured vehicles + news
│   │   │   ├── contact/
│   │   │   │   └── index.php        [Tang Vu]    contact form
│   │   │   ├── about/
│   │   │   │   └── index.php        [Nhat Linh]  company intro
│   │   │   ├── faq/
│   │   │   │   └── index.php        [Nhat Linh]  accordion FAQ
│   │   │   ├── products/
│   │   │   │   ├── index.php        [Hai Nam]    listing + search + filter
│   │   │   │   ├── detail.php       [Hai Nam]    specs + gallery + booking form
│   │   │   │   ├── checkout.php     [Hai Nam]    deposit/test-drive confirmation
│   │   │   │   └── confirmation.php [Hai Nam]    order confirmation
│   │   │   ├── news/
│   │   │   │   ├── index.php        [Nhat Tan]   article listing
│   │   │   │   └── detail.php       [Nhat Tan]   full article + comments
│   │   │   ├── auth/
│   │   │   │   ├── login.php        [Common]
│   │   │   │   └── register.php     [Common]
│   │   │   └── user/
│   │   │       ├── profile.php      [Common]
│   │   │       └── orders.php       [Common]
│   │   │
│   │   └── admin/               Admin-panel views (Srtdash template)
│   │       ├── layouts/
│   │       │   └── admin.php        [Common]  Srtdash layout (sidebar + topbar)
│   │       ├── partials/
│   │       │   ├── sidebar.php      [Common]  nav links for all sections
│   │       │   ├── topbar.php       [Common]  admin name + sidebar toggle
│   │       │   └── flash.php        [Common]
│   │       ├── dashboard/
│   │       │   └── index.php        [Common]  stats overview
│   │       ├── users/
│   │       │   ├── index.php        [Common]
│   │       │   └── edit.php         [Common]
│   │       ├── settings/
│   │       │   └── index.php        [Tang Vu]   logo/banners/contact info editor
│   │       ├── contacts/
│   │       │   └── index.php        [Tang Vu]   message list + status management
│   │       ├── faq/
│   │       │   ├── index.php        [Nhat Linh]
│   │       │   └── form.php         [Nhat Linh]
│   │       ├── products/
│   │       │   ├── index.php        [Hai Nam]
│   │       │   └── form.php         [Hai Nam]   Dropzone upload
│   │       ├── orders/
│   │       │   └── index.php        [Hai Nam]
│   │       ├── news/
│   │       │   ├── index.php        [Nhat Tan]
│   │       │   └── form.php         [Nhat Tan]  TinyMCE editor + SEO fields
│   │       └── comments/
│   │           └── index.php        [Nhat Tan]  approve/delete moderation
│   │
│   └── helpers/                 Shared utility classes
│       ├── Auth.php             [Common]  session, roles, CSRF
│       ├── Validator.php        [Common]  server-side validation
│       ├── Upload.php           [Common]  image upload handler
│       ├── Pagination.php       [Common]  SQL LIMIT/OFFSET calculator
│       ├── SEO.php              [Common]  <title> and <meta> per page
│       └── View.php             [Common]  render(view, data, layout) via output buffering
│
├── public/
│   ├── css/
│   │   ├── frontend/
│   │   │   ├── global.css       [Common]  brand tokens, shared styles
│   │   │   └── responsive.css   [Common]  media query overrides
│   │   └── admin/
│   │       └── global.css       [Common]  Srtdash overrides, status badge colours
│   │
│   ├── js/
│   │   ├── frontend/
│   │   │   ├── main.js          [Common]  scroll-to-top, lazy load, active nav
│   │   │   ├── validate.js      [Common]  Bootstrap 5 client-side validation
│   │   │   ├── products.js      [Hai Nam] product listing/detail interactions
│   │   │   └── checkout.js      [Hai Nam] checkout/confirmation interactions
│   │   └── admin/
│   │       └── main.js          [Common]  Srtdash sidebar toggle + confirm dialogs
│   │
│   ├── images/
│   │   ├── uploads/             Runtime uploaded files
│   │   ├── logo/                VinFast logo assets
│   │   ├── banners/             [Tang Vu]  hero banners
│   │   ├── products/            [Hai Nam]  vehicle photos
│   │   ├── news/                [Nhat Tan] article thumbnails
│   │   └── avatars/             [Common]   user avatars
│   │
│   └── libs/
│       ├── srtdash/             Admin template (download from GitHub — see README)
│       ├── tinymce/             [Nhat Tan]  WYSIWYG editor for articles
│       ├── swiper/              [Tang Vu]   hero banner carousel
│       └── dropzone/            [Hai Nam]   drag-and-drop image upload
│
├── database/
│   ├── schema.sql               Table structures + seed data (use during development)
│   └── vinfast.sql              Full dump with sample products/FAQs (submit with report)
│
└── docs/
    ├── report.docx              Group report (min 20 pages)
    ├── ERD.png                  Entity-relationship diagram
    └── flowcharts/              Feature flowcharts (draw.io recommended)
```

---

## Team Work Split (No Overlap)

### What each member owns exclusively

**Tang Vu (Member 1)**
```
app/controllers/frontend/HomeController.php
app/controllers/frontend/ContactController.php
app/controllers/admin/SettingsAdminController.php
app/controllers/admin/ContactAdminController.php
app/models/Contact.php
app/models/SiteSetting.php
app/views/frontend/home/
app/views/frontend/contact/
app/views/admin/settings/
app/views/admin/contacts/
public/images/banners/
```

**Nhat Linh (Member 2)**
```
app/controllers/frontend/AboutController.php
app/controllers/frontend/FaqController.php
app/controllers/admin/FaqAdminController.php
app/models/Faq.php
app/views/frontend/about/
app/views/frontend/faq/
app/views/admin/faq/
```

**Hai Nam (Member 3)**
```
app/controllers/frontend/ProductController.php
app/controllers/admin/ProductAdminController.php
app/controllers/admin/OrderAdminController.php
app/models/Product.php  Category.php  Order.php
app/views/frontend/products/
app/views/admin/products/
app/views/admin/orders/
public/js/frontend/products.js
public/js/frontend/checkout.js
public/images/products/
```

**Nhat Tan (Member 4)**
```
app/controllers/frontend/NewsController.php
app/controllers/frontend/CommentController.php
app/controllers/admin/NewsAdminController.php
app/controllers/admin/CommentAdminController.php
app/models/News.php  Comment.php
app/views/frontend/news/
app/views/admin/news/
app/views/admin/comments/
public/images/news/
```

**All members (Common)**
```
index.php  admin.php  .htaccess
config/
app/helpers/
app/models/User.php
app/controllers/frontend/AuthController.php
app/controllers/frontend/UserController.php
app/controllers/admin/DashboardAdminController.php
app/controllers/admin/UserAdminController.php
app/views/frontend/layouts/
app/views/frontend/partials/
app/views/frontend/auth/
app/views/frontend/user/
app/views/admin/layouts/
app/views/admin/partials/
app/views/admin/dashboard/
app/views/admin/users/
public/css/
public/js/frontend/main.js
public/js/frontend/validate.js
public/js/admin/main.js
database/
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
