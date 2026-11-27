# T4 — Giỏ hàng, đặt đơn và mã giảm giá

Ở ứng dụng web, PostgreSQL là nơi ghi đơn, chi tiết, tồn kho, mã đã dùng và giỏ hàng. Một lỗi trong bất kỳ bước nào làm rollback toàn bộ. MongoDB cung cấp giá và phiên bản sản phẩm trước khi bắt đầu transaction; ghi log hành vi và cập nhật `totalSold` diễn ra sau commit. Số tiền được tính lại ở máy chủ. Mỗi mã chỉ dùng một lần mỗi tài khoản, theo khóa duy nhất trong schema. Bấm gửi lại cùng một biểu mẫu không tạo thêm đơn.

## Chạy và demo

Trong thư mục repo:

```powershell
docker compose up -d --build
docker compose ps
docker compose cp database/postgres/005_order_idempotency.sql postgres:/tmp/t4_migration.sql
docker compose exec -T postgres psql -U vinfast -d vinfast -v ON_ERROR_STOP=1 -f /tmp/t4_migration.sql
docker compose cp bench/t4/demo_coupon.sql postgres:/tmp/t4_coupon.sql
docker compose exec -T postgres psql -U vinfast -d vinfast -v ON_ERROR_STOP=1 -f /tmp/t4_coupon.sql
```

Migration an toàn khi chạy lại và tự áp dụng trong lần chạy seed. Không cần xoá volume để cập nhật cơ sở dữ liệu sẵn có. Nếu cổng MongoDB 27018 đang bị bản benchmark trước dùng, đổi `MONGO_HOST_PORT` trong `.env` hoặc dừng đúng stack benchmark trước khi khởi động web.

1. Đăng nhập bằng tài khoản demo (xem README gốc). Vào `http://localhost:8080/products?type=accessory` và thêm hai phụ kiện còn hàng.
2. Mở **Giỏ hàng** ở menu tài khoản. Sửa số lượng nếu muốn rồi chọn **Tiến hành đặt hàng**.
3. Nhập thông tin nhận hàng, mã `T4DEMO10`, bấm **Xem trước**. Nút này chỉ đọc, chưa ghi đơn hay trừ lượt.
4. Bấm **Đặt hàng**. Trang chi tiết hiển thị tạm tính, tiền giảm và tổng. Đơn ở trạng thái chưa thanh toán; không có cổng thanh toán thật trong thay đổi T4.
5. Dùng SQL dưới đây để chụp đơn, dòng chi tiết và lượt dùng mã. Nếu muốn thử một lần nữa với mã đó, dùng tài khoản khác: cùng tài khoản sẽ nhận thông báo đã dùng mã, đơn không được tạo.
6. Với xe, chạy luồng đặt cọc hiện có, chọn phiên bản còn tồn kho. Đơn vẫn dùng tiền cọc của xe và quy trình xác nhận hiện có; mã giảm giá chỉ áp dụng cho checkout phụ kiện.

```powershell
docker compose cp bench/t4/verify.sql postgres:/tmp/t4_verify.sql
docker compose exec -T postgres psql -U vinfast -d vinfast -v ON_ERROR_STOP=1 -f /tmp/t4_verify.sql
```

## Kiểm tra rollback

Tạo một database riêng ở lần đầu. File schema gốc chỉ chạy trên database kiểm thử mới tạo:

```powershell
docker compose exec -T postgres createdb -U vinfast vinfast_t4_test
docker compose cp database/postgres postgres:/tmp/t4_schema
docker compose exec -T postgres psql -U vinfast -d vinfast_t4_test -v ON_ERROR_STOP=1 -f /tmp/t4_schema/001_schema.sql -f /tmp/t4_schema/002_indexes.sql -f /tmp/t4_schema/004_coupons.sql -f /tmp/t4_schema/005_order_idempotency.sql
docker compose exec -T -e APP_ENV=bench -e PG_DB=vinfast_t4_test app php bench/t4/smoke.php
```

Smoke dùng dữ liệu giả lập danh mục và PostgreSQL thật. Nó kiểm tra bốn điểm rollback, thiếu hàng ở món thứ hai, gửi lại đơn, mã hết lượt, dùng lại mã, mã hết hạn và đặt cọc đúng phiên bản. Kết quả demo trên trình duyệt với danh mục MongoDB thật được kiểm tra theo các bước trên.

`docker compose exec -T app php bench/t4/reconcile_sold.php PRODUCT_ID` tính lại bộ đếm MongoDB cho một sản phẩm từ các đơn chưa hủy. Thực hiện trên local khi tạm dừng ghi đơn để tránh ghi đè cập nhật đồng thời. Đối soát không phải transaction xuyên hai DB.

Benchmark 10.000 yêu cầu ở báo cáo là phép đo riêng; smoke trên repo này kiểm tra đúng/sai, không sinh số liệu hiệu năng mới. Đích PR của nhóm: `develop-hybrid`.
