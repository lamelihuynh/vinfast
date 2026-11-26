# Seed & dữ liệu benchmark

| File | Vai trò |
|---|---|
| `seed.php` | Tạo schema PG (001/002/004) nếu DB trống, seed dữ liệu cũ (`legacy_data.json`, `legacy_content.php`) + catalog phụ kiện (`accessories.php`) |
| `generate.php` | Sinh dữ liệu benchmark **lên trên** seed cơ bản khi có `--size` |

## Lệnh

```bash
# Seed cơ bản (giống trước đây; DB đã có dữ liệu thì bỏ qua)
docker compose run --rm seed php database/seed/seed.php
docker compose run --rm seed php database/seed/seed.php --force          # xóa sạch rồi seed lại

# Seed cơ bản + dữ liệu benchmark (nên luôn kèm --force)
docker compose run --rm seed php database/seed/seed.php --force --size=small
docker compose run --rm seed php database/seed/seed.php --force --size=10k
docker compose run --rm seed php database/seed/seed.php --force --size=100k
```

Muốn chạy trên DB riêng để không đụng DB dev mà mọi người đang dùng:

```bash
docker compose exec -T postgres createdb -U vinfast vinfast_bench
docker compose run --rm -e MONGO_DB=vinfast_bench mongo-init
docker compose run --rm -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench seed php database/seed/seed.php --force --size=10k
# dọn dẹp
docker compose exec -T postgres dropdb -U vinfast vinfast_bench
docker compose exec -T mongo mongosh vinfast_bench --quiet --eval 'db.dropDatabase()'
```

Không dùng `--force` mà DB đã có dữ liệu thì cả seed lẫn generate đều bỏ qua (in ra thông báo).

## Kích thước (số đo thực tế, tổng sau khi cộng seed cơ bản)

N = số sản phẩm. Quy tắc: users ≈ N/5 (tối thiểu 20), orders ≈ N (small: 50), order_items ≈ 1.5 × orders,
product_reviews ≈ N/2, wishlist_items ≈ N/2, giỏ hàng cho 10% user (1–3 món), userActivityLogs ≈ 2N, 20 coupon.

| Bảng / collection | small | 10k | 100k |
|---|---:|---:|---:|
| Mongo `products` | 100 | 10 000 | 100 000 |
| variants = PG `inventory` | 293 | 27 131 | 274 750 |
| `users` | 20 | 2 000 | 20 000 |
| `orders` | 50 | 10 000 | 100 000 |
| `order_items` | 78 | 15 082 | 151 263 |
| `product_reviews` | 50 | 5 000 | 50 000 |
| `wishlists` / `wishlist_items` | 17 / 50 | 1 783 / 5 000 | 17 596 / 50 000 |
| `carts` / `cart_items` | 2 / 5 | 200 / 419 | 2 000 / 4 065 |
| `coupons` / `coupon_redemptions` | 20 / 8 | 20 / 1 641 | 20 / 16 242 |
| Mongo `userActivityLogs` | 200 | 20 000 | 200 000 |
| Thời gian sinh (generate) | 0.1 s | ~5 s | ~48 s |
| Tổng lệnh `seed.php --force --size=…` | ~5 s | ~10 s | ~52 s |
| Dung lượng | | | PG ~176 MB, Mongo ~190 MB data |

Đo trên Docker Desktop (Windows 11, WSL2). Mỗi bước in ra số dòng, thời gian và RAM đỉnh.

### Dữ liệu sinh ra trông như thế nào

- **Sản phẩm**: ~25% xe (category 1 xe máy điện / 2 ô tô điện, lấy mẫu từ 16 xe cũ: ảnh, màu, thông số;
  1–3 phiên bản Eco/Plus/Lux hoặc Tiêu chuẩn/Cao cấp/Đặc biệt, có `range`, `rangeKm`, `deposit_amount`)
  và ~75% phụ kiện theo 3 category phụ kiện của `accessories.php` (Pin & Sạc: tùy chọn; Thời trang: màu × size;
  Phụ kiện xe: dòng xe × màu, `compatible_models`). Tên/slug duy nhất, `createdAt` rải 18 tháng, ~3% ẩn.
- **Tồn kho**: 1 dòng / variant, stock 0–200 (~8% hết hàng), `reserved` = số lượng của đơn `pending`.
- **Đơn hàng**: 12 tháng gần nhất, số đơn tăng dần theo tháng. ~30% đặt cọc (1 xe, total = tiền cọc),
  ~70% mua phụ kiện (1–4 món). Trạng thái theo tuổi đơn (đơn cũ phần lớn `done`), ~8% `cancelled`;
  payment_status khớp trạng thái + phương thức (COD chưa trả đến khi `done`). Snapshot tên/giá khớp Mongo.
- **Coupon**: 20 mã (đang chạy, hết hạn, tạm khóa, chưa bắt đầu, vài mã đã hết quota). Redemption tuân thủ
  khung giờ, `min_order_amount`, quota, UNIQUE(coupon, user); `used_count` = số redemption;
  `orders.discount_amount`, `coupon_id`, `total_amount` nhất quán.
- **Review**: rating lệch về 4–5 sao, ~60% từ người đã mua, ~90% đã duyệt. Mongo `avgRating`, `totalReviews`,
  `reviews[]` (5 review đã duyệt mới nhất) và `totalSold` (tổng số lượng của đơn không hủy) được tính lại từ PG.
- **userActivityLogs**: 1 log `place_order`/`deposit` (+ `apply_coupon`) cho mỗi đơn, phần còn lại là
  `view_product`, `search` (từ khóa tiếng Việt như "vf 3", "sạc treo tường", "thảm lót sàn vf 8"…),
  `add_to_cart`, `add_to_wishlist`, `remove_from_*`; 25% là khách chưa đăng nhập (`userId: null`).

### Tất định

`mt_srand(20261121)`: cùng `--size` cho ra cùng id, tên, giá, quan hệ, nội dung. Mốc thời gian tính lùi từ
00:00 ngày chạy, nên chạy ở ngày khác thì ngày tháng dịch theo, còn lại giống hệt.

## Tài khoản test

| Email | Mật khẩu | Ghi chú |
|---|---|---|
| `admin@vinfast.vn` | `Admin@123` | admin (id 1) |
| `member@vinfast.vn` | `Member@123` | member (id 2) |
| `<tên>.<họ><id>@example.com` | `User@123` | mọi user sinh ra, ví dụ `SELECT email FROM users WHERE id = 3` |
