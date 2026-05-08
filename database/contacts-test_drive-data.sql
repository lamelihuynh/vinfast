-- VinFast Website — Seed Sample Data
-- Dữ liệu mẫu cho Liên hệ và Đăng ký lái thử

USE vinfast_db;

-- 1. Thêm dữ liệu cho bảng contacts (Liên hệ)
INSERT INTO contacts (name, email, phone, message, status) VALUES
('Nguyễn Văn An', 'nv.an@example.com', '0901234567', 'Tôi muốn tư vấn chi tiết về thủ tục trả góp cho xe VF8.', 'unread'),
('Trần Thị Bích', 'tt.bich@example.com', '0912345678', 'Cho hỏi giá lăn bánh của VF3 tại khu vực Hà Nội là bao nhiêu?', 'read'),
('Lê Văn Cường', 'lv.cuong@example.com', '0923456789', 'Chính sách bảo hành pin 10 năm của VinFast áp dụng như thế nào?', 'unread'),
('Phạm Minh Đức', 'pm.duc@example.com', '0934567890', 'Tôi cần tìm danh sách các trạm sạc nhanh tại khu vực Đà Nẵng.', 'replied'),
('Hoàng Anh Em', 'ha.em@example.com', '0945678901', 'Cần nhận báo giá chi tiết và khuyến mãi cho xe máy điện Evo 200.', 'unread');

-- 2. Thêm dữ liệu cho bảng test_drives (Đăng ký lái thử)
-- Sử dụng subquery để lấy đúng ID sản phẩm dựa trên slug, tránh lỗi Foreign Key
INSERT INTO test_drives (name, email, phone, product_id, province, showroom, preferred_date, note, status) VALUES
('Nguyễn Văn Nam', 'nvnam@example.com', '0987654321', (SELECT id FROM products WHERE slug = 'vinfast-vf3-eco' LIMIT 1), 'Hà Nội', 'VinFast Ocean Park', '2026-05-10', 'Muốn thử cảm giác lái SUV mini trong đô thị.', 'pending'),
('Trần Minh Tâm', 'tmtam@example.com', '0976543210', (SELECT id FROM products WHERE slug = 'vinfast-vf8-eco' LIMIT 1), 'TP. Hồ Chí Minh', 'VinFast Landmark 81', '2026-05-12', 'Đang cân nhắc đổi từ xe xăng sang xe điện cho gia đình.', 'confirmed'),
('Lê Thu Hà', 'ltha@example.com', '0965432109', (SELECT id FROM products WHERE slug = 'vinfast-evo' LIMIT 1), 'Đà Nẵng', 'VinFast Đà Nẵng', '2026-05-15', 'Mua xe máy điện cho con đi học, cần thử độ bền.', 'pending'),
('Đặng Văn Hùng', 'dvhung@example.com', '0954321098', (SELECT id FROM products WHERE slug = 'vinfast-vf9-eco' LIMIT 1), 'Hải Phòng', 'VinFast Smart City', '2026-05-20', 'Quan tâm dòng SUV hạng E cao cấp nhất.', 'pending'),
('Vũ Thị Mai', 'vtmai@example.com', '0943210987', (SELECT id FROM products WHERE slug = 'vinfast-vf6-eco' LIMIT 1), 'Cần Thơ', 'VinFast Ninh Kiều', '2026-05-25', 'Muốn trải nghiệm hệ thống ADAS trên VF6.', 'done');
