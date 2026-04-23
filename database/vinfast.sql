-- VinFast Website — Full Real Data Dump (Updated April 2026)
-- Cập nhật bổ sung Power và Acceleration cho hệ thống quản lý

-- ==========================================================
-- 1. DỮ LIỆU SẢN PHẨM (PRODUCTS)
-- ==========================================================

INSERT IGNORE INTO products (category_id, name, slug, description, price, specs, images, is_active)
VALUES
-- NHÓM XE MÁY ĐIỆN (Category 1)
(1, 'VinFast Evo', 'vinfast-evo', 'Bền bỉ mọi hành trình với thiết kế trẻ trung.', 25600000, 
 '{"category": "Xe máy điện", "power": "1500 W", "range": "85-165 km", "acceleration": "N/A", "battery": "LFP 1.5 kWh", "max_speed": "70 km/h"}', '["/uploads/products/vinfast-evo/bau1.png", "/uploads/products/vinfast-evo/gnv1.png", "/uploads/products/vinfast-evo/req1.png", "/uploads/products/vinfast-evo/whr1.png"]', 1),

(1, 'VinFast Flazz', 'vinfast-flazz', 'Mẫu xe siêu nhẹ, linh hoạt cho đô thị.', 16900000, 
 '{"category": "Xe máy điện", "power": "600 W", "range": "70-135 km", "acceleration": "N/A", "battery": "LFP 1.2 kWh", "max_speed": "39 km/h"}', '["/uploads/products/vinfast-flazz/bau.webp", "/uploads/products/vinfast-flazz/gnq.webp", "/uploads/products/vinfast-flazz/req.webp", "/uploads/products/vinfast-flazz/whr.webp"]', 1),

(1, 'VinFast ZGoo', 'vinfast-zgoo', 'Xe điện thời trang với đèn pha LED Projector.', 15900000, 
 '{"category": "Xe máy điện", "power": "600 W", "range": "70 km", "acceleration": "N/A", "battery": "LFP 1.2 kWh", "max_speed": "39 km/h"}', '["/uploads/products/vinfast-zgoo/bau.webp", "/uploads/products/vinfast-zgoo/gnv.webp", "/uploads/products/vinfast-zgoo/req.webp", "/uploads/products/vinfast-zgoo/whr.webp"]', 1),

(1, 'VinFast AMIO', 'vinfast-amio', 'Xe máy điện nhỏ gọn, giá tốt nhất.', 13900000, 
 '{"category": "Xe máy điện", "power": "350 W", "range": "65 km", "acceleration": "<16s (0-50km/h)", "battery": "LFP 1.024 kWh", "max_speed": "30 km/h"}', '["/uploads/products/vinfast-amio/bau.png", "/uploads/products/vinfast-amio/buw.png", "/uploads/products/vinfast-amio/grc.png", "/uploads/products/vinfast-amio/req.png", "/uploads/products/vinfast-amio/whr.png"]', 1),

(1, 'VinFast FELIZ 2025', 'vinfast-feliz-2025', 'Dòng xe trung cấp mạnh mẽ, cốp xe cực đại 34L.', 26900000, 
 '{"category": "Xe máy điện", "power": "1800 W", "range": "134-262 km", "acceleration": "<16s (0-50km/h)", "battery": "LFP 2.4 kWh", "max_speed": "70 km/h"}', '["/uploads/products/vinfast-feliz-2025/bau.png", "/uploads/products/vinfast-feliz-2025/gnq.png", "/uploads/products/vinfast-feliz-2025/gnv.png", "/uploads/products/vinfast-feliz-2025/whr.png", "/uploads/products/vinfast-feliz-2025/yes.png"]', 1),

(1, 'VinFast Viper', 'vinfast-viper', 'Xe máy điện cao cấp với phanh đĩa/cơ an toàn.', 45500000, 
 '{"category": "Xe máy điện", "power": "1800 W", "range": "82-156 km", "acceleration": "15s (0-50km/h)", "battery": "LFP 1.5 kWh", "max_speed": "70 km/h"}', '["/uploads/products/vinfast-viper/bau.png", "/uploads/products/vinfast-viper/grc.png", "/uploads/products/vinfast-viper/req.png", "/uploads/products/vinfast-viper/whr.png", "/uploads/products/vinfast-viper/yes.png"]', 1),

-- NHÓM Ô TÔ ĐIỆN (Category 2)
(2, 'VinFast VF 3 Eco', 'vinfast-vf3-eco', 'SUV mini sành điệu, nhỏ gọn và linh hoạt.', 302000000, 
 '{"category": "SUV phân khúc Mini", "power": "30 kW", "range": "215 km (NEDC)", "acceleration": "N/A", "battery": "18.64 kWh", "top_speed": "100 km/h"}', '["/uploads/products/vinfast-vf3-eco/181u.webp", "/uploads/products/vinfast-vf3-eco/181y.webp", "/uploads/products/vinfast-vf3-eco/1821.webp", "/uploads/products/vinfast-vf3-eco/ce18.webp", "/uploads/products/vinfast-vf3-eco/ce1v.webp", "/uploads/products/vinfast-vf3-eco/ce1w.webp", "/uploads/products/vinfast-vf3-eco/ce2q.webp"]', 1),

(2, 'VinFast VF 3 Plus', 'vinfast-vf3-plus', 'Phiên bản cao cấp của VF 3 tích hợp Android Auto.', 315000000, 
 '{"category": "SUV phân khúc Mini", "power": "30 kW", "range": "215 km (NEDC)", "acceleration": "N/A", "battery": "18.64 kWh", "top_speed": "100 km/h"}', '["/uploads/products/vinfast-vf3-plus/181u.webp", "/uploads/products/vinfast-vf3-plus/181y.webp", "/uploads/products/vinfast-vf3-plus/1821.webp", "/uploads/products/vinfast-vf3-plus/ce18.webp", "/uploads/products/vinfast-vf3-plus/ce1v.webp", "/uploads/products/vinfast-vf3-plus/ce1w.webp", "/uploads/products/vinfast-vf3-plus/ce2q.webp"]', 1),

(2, 'VinFast VF 6 Eco', 'vinfast-vf6-eco', 'Sự lựa chọn hoàn hảo cho gia đình trẻ.', 689000000, 
 '{"category": "SUV phân khúc B", "power": "130 kW", "range": "485 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '["/uploads/products/vinfast-vf6-eco/ce11.webp", "/uploads/products/vinfast-vf6-eco/ce18.webp", "/uploads/products/vinfast-vf6-eco/ce1v.webp", "/uploads/products/vinfast-vf6-eco/ce1w.webp", "/uploads/products/vinfast-vf6-eco/ce2q.webp"]', 1),

(2, 'VinFast VF 6 Plus', 'vinfast-vf6-plus', 'Hiệu năng cao với công suất 201 hp.', 745000000, 
 '{"category": "SUV phân khúc B", "power": "150 kW", "range": "460 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '["/uploads/products/vinfast-vf6-plus/ce11.webp", "/uploads/products/vinfast-vf6-plus/ce18.webp", "/uploads/products/vinfast-vf6-plus/ce1v.webp", "/uploads/products/vinfast-vf6-plus/ce1w.webp", "/uploads/products/vinfast-vf6-plus/ce2q.webp"]', 1),

(2, 'VinFast VF 7 Eco', 'vinfast-vf7-eco', 'SUV hạng C với thiết kế tương lai.', 789000000, 
 '{"category": "SUV phân khúc C", "power": "130 kW", "range": "440 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '["/uploads/products/vinfast-vf7-eco/ce11.webp", "/uploads/products/vinfast-vf7-eco/ce18.webp", "/uploads/products/vinfast-vf7-eco/ce1v.webp", "/uploads/products/vinfast-vf7-eco/ce1w.webp", "/uploads/products/vinfast-vf7-eco/ce2q.webp"]', 1),

(2, 'VinFast VF 7 Plus', 'vinfast-vf7-plus', 'Sức mạnh vượt trội công suất 150 kW.', 889000000, 
 '{"category": "SUV phân khúc C", "power": "150 kW", "range": "N/A", "acceleration": "N/A", "battery": "70 kWh"}', '["/uploads/products/vinfast-vf7-plus/ce11.webp", "/uploads/products/vinfast-vf7-plus/ce18.webp", "/uploads/products/vinfast-vf7-plus/ce1v.webp", "/uploads/products/vinfast-vf7-plus/ce1w.webp", "/uploads/products/vinfast-vf7-plus/ce2q.webp"]', 1),

(2, 'VinFast VF 8 Eco', 'vinfast-vf8-eco', 'SUV hạng D đẳng cấp, quãng đường di chuyển xa.', 1019000000, 
 '{"category": "SUV phân khúc D", "power": "150 kW", "range": "562 km (NEDC)", "acceleration": "11.8s (0-100km/h)", "battery": "87.7 kWh"}', '["/uploads/products/vinfast-vf8-eco/171v.webp", "/uploads/products/vinfast-vf8-eco/1v18.webp", "/uploads/products/vinfast-vf8-eco/ce11.webp", "/uploads/products/vinfast-vf8-eco/ce18.webp", "/uploads/products/vinfast-vf8-eco/ce1m.webp", "/uploads/products/vinfast-vf8-eco/ce22.webp"]', 1),

(2, 'VinFast VF 8 Plus', 'vinfast-vf8-plus', 'SUV mạnh mẽ 402 hp, dẫn động AWD.', 1199000000, 
 '{"category": "SUV phân khúc D", "power": "300 kW", "range": "457 km (WLTP)", "acceleration": "5.58s (0-100km/h)", "battery": "87.7 kWh"}', '["/uploads/products/vinfast-vf8-plus/171v.webp", "/uploads/products/vinfast-vf8-plus/1v18.webp", "/uploads/products/vinfast-vf8-plus/ce11.webp", "/uploads/products/vinfast-vf8-plus/ce18.webp", "/uploads/products/vinfast-vf8-plus/ce1m.webp", "/uploads/products/vinfast-vf8-plus/ce22.webp"]', 1),

(2, 'VinFast VF 9 Eco', 'vinfast-vf9-eco', 'SUV hạng E siêu sang, pin CATL 123 kWh.', 1499000000, 
 '{"category": "SUV phân khúc E", "power": "300 kW", "range": "626 km (WLTP)", "acceleration": "N/A", "battery": "123 kWh"}', '["/uploads/products/vinfast-vf9-eco/ce11.webp", "/uploads/products/vinfast-vf9-eco/ce17.webp", "/uploads/products/vinfast-vf9-eco/ce18.webp", "/uploads/products/vinfast-vf9-eco/ce1m.webp", "/uploads/products/vinfast-vf9-eco/ce1v.webp", "/uploads/products/vinfast-vf9-eco/ce1w.webp", "/uploads/products/vinfast-vf9-eco/ce22.webp"]', 1),

(2, 'VinFast VF 9 Plus', 'vinfast-vf9-plus', 'Đỉnh cao SUV điện với massage ghế và dẫn động AWD.', 1699000000, 
 '{"category": "SUV phân khúc E", "power": "300 kW", "range": "602 km (WLTP)", "acceleration": "N/A", "battery": "123 kWh"}', '["/uploads/products/vinfast-vf9-plus/ce11.webp", "/uploads/products/vinfast-vf9-plus/ce17.webp", "/uploads/products/vinfast-vf9-plus/ce18.webp", "/uploads/products/vinfast-vf9-plus/ce1m.webp", "/uploads/products/vinfast-vf9-plus/ce1v.webp", "/uploads/products/vinfast-vf9-plus/ce1w.webp", "/uploads/products/vinfast-vf9-plus/ce22.webp"]', 1);

-- ==========================================================
-- 2. DỮ LIỆU CÂU HỎI THƯỜNG GẶP (FAQS)
-- ==========================================================

INSERT IGNORE INTO faqs (question, answer, sort_order) VALUES
('Chính sách bảo hành ô tô điện VinFast như thế nào?', 'Bảo hành xe mới từ 7-10 năm hoặc 160.000-200.000 km tùy dòng xe. Pin cao áp cũng được bảo hành tương đương.', 1),
('Làm sao để tìm trạm sạc công cộng?', 'Khách hàng có thể tìm kiếm, đặt chỗ và thanh toán tại hơn 150.000 cổng sạc toàn quốc qua ứng dụng VinFast.', 2);