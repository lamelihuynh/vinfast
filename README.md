# VinFast Electric Vehicle Website 

A full business website for VinFast
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
│   │   │   ├── CartController.php         [Hai Nam]     GET+POST /cart  /cart/placeOrder
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
│   │   ├── Cart.php             [Hai Nam]
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
│   │   │   │   ├── cart.php         [Hai Nam]    cart items
│   │   │   │   └── checkout.php     [Hai Nam]    deposit/test-drive confirmation
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
│   │   │   └── cart.js          [Hai Nam] AJAX add-to-cart
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
app/controllers/frontend/CartController.php
app/controllers/admin/ProductAdminController.php
app/controllers/admin/OrderAdminController.php
app/models/Product.php  Category.php  Cart.php  Order.php
app/views/frontend/products/
app/views/admin/products/
app/views/admin/orders/
public/js/frontend/cart.js
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

## Installation

### Requirements
- PHP >= 7.0 with PDO, PDO_MySQL, GD extensions
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` + `AllowOverride All`
  (or Laragon / XAMPP on Windows)

### Steps
```bash
# 1. Place project in web root
#    XAMPP: C:\xampp\htdocs\vinfast\

# 2. Create DB and import schema // Luu ý : phải tắt mysql của máy trước rồi mới dùng sql của XAMPP, nếu SQL của XAMPP có cài mật khẩu thì mới dùng cờ -p không thì không gắn cờ đó : lệnh chạy bên máy của linh.  VD :
# sudo /Applications/XAMPP/xamppfiles/bin/mysql -u root vinfast_db < database/schema.sql   (do lệnh mysql đã trùng với mysql cài trên máy )

mysql -u root -p -e "CREATE DATABASE vinfast_db;"
mysql -u root -p vinfast_db < database/schema.sql
mysql -u root -p vinfast_db < database/vinfast.sql   # optional sample data

# 3. Edit config/database.php (DB_USER, DB_PASS)
#    Edit config/bootstrap.php (BASE_URL)

# 4. Generate a real admin password hash (run once in PHP):
#    echo password_hash('Admin@123', PASSWORD_BCRYPT);
#    Paste the result into: UPDATE users SET password='...' WHERE email='admin@vinfast.vn';

# 5. Download third-party libs into public/libs/:
#    Srtdash  : https://github.com/puikinsh/srtdash-admin-dashboard
#    TinyMCE  : https://www.tiny.cloud/get-tiny/self-hosted/
#    Swiper   : https://swiperjs.com/get-started
#    Dropzone : https://docs.dropzone.dev/

# 6. Visit: http://localhost/vinfast/
#    Admin:   http://localhost/vinfast/admin/dashboard
```

