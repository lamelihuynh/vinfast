-- VinFast Website — Full Real Data Dump (Updated April 2026)
-- Cập nhật bổ sung Power và Acceleration cho hệ thống quản lý

-- ==========================================================
-- 1. DỮ LIỆU SẢN PHẨM (PRODUCTS)
-- ==========================================================

INSERT IGNORE INTO products (category_id, name, slug, description, price, specs, images, is_active)
VALUES
-- NHÓM XE MÁY ĐIỆN (Category 1)
(1, 'VinFast Evo', 'vinfast-evo', 'Bền bỉ mọi hành trình với thiết kế trẻ trung.', 25600000, 
 '{"category": "Xe máy điện", "power": "1500 W", "range": "85-165 km", "acceleration": "N/A", "battery": "LFP 1.5 kWh", "max_speed": "70 km/h"}', '[]', 1),

(1, 'VinFast Evo Grand', 'vinfast-evo-grand', 'Quãng đường di chuyển lên tới 262km và cốp rộng 35L.', 22800000, 
 '{"category": "Xe máy điện", "power": "2250 W", "range": "134-262 km", "acceleration": "N/A", "battery": "LFP 2.4 kWh", "max_speed": "70 km/h"}', '[]', 1),

(1, 'VinFast Evo Grand Lite', 'vinfast-evo-grand-lite', 'Mẫu xe tối ưu cho học sinh với tốc độ giới hạn 48km/h.', 18900000, 
 '{"category": "Xe máy điện", "power": "1900 W", "range": "70-198 km", "acceleration": "N/A", "battery": "LFP 2.4 kWh", "max_speed": "48 km/h"}', '[]', 1),

(1, 'VinFast EVO LITE NEO', 'vinfast-evo-lite-neo', 'Lựa chọn chuẩn Gen Z với hệ thống ắc quy 1.26 kWh.', 14400000, 
 '{"category": "Xe máy điện", "power": "1200 W", "range": "70 km", "acceleration": "N/A", "battery": "Lead-acid 1.26 kWh", "max_speed": "49 km/h"}', '[]', 1),

(1, 'VinFast Flazz', 'vinfast-flazz', 'Mẫu xe siêu nhẹ, linh hoạt cho đô thị.', 16900000, 
 '{"category": "Xe máy điện", "power": "600 W", "range": "70-135 km", "acceleration": "N/A", "battery": "LFP 1.2 kWh", "max_speed": "39 km/h"}', '[]', 1),

(1, 'VinFast ZGoo', 'vinfast-zgoo', 'Xe điện thời trang với đèn pha LED Projector.', 15900000, 
 '{"category": "Xe máy điện", "power": "600 W", "range": "70 km", "acceleration": "N/A", "battery": "LFP 1.2 kWh", "max_speed": "39 km/h"}', '[]', 1),

(1, 'VinFast AMIO', 'vinfast-amio', 'Xe máy điện nhỏ gọn, giá tốt nhất.', 13900000, 
 '{"category": "Xe máy điện", "power": "350 W", "range": "65 km", "acceleration": "<16s (0-50km/h)", "battery": "LFP 1.024 kWh", "max_speed": "30 km/h"}', '[]', 1),

(1, 'VinFast FELIZ 2025', 'vinfast-feliz-2025', 'Dòng xe trung cấp mạnh mẽ, cốp xe cực đại 34L.', 26900000, 
 '{"category": "Xe máy điện", "power": "1800 W", "range": "134-262 km", "acceleration": "<16s (0-50km/h)", "battery": "LFP 2.4 kWh", "max_speed": "70 km/h"}', '[]', 1),

(1, 'VinFast FELIZ II', 'vinfast-feliz-ii', 'Phiên bản nâng cấp công suất tối đa 3000W.', 30500000, 
 '{"category": "Xe máy điện", "power": "1800 W", "range": "82-156 km", "acceleration": "15s (0-50km/h)", "battery": "LFP 1.5 kWh", "max_speed": "70 km/h"}', '[]', 1),

(1, 'VinFast Vero X', 'vinfast-vero-x', 'Xe điện thể thao với khóa thông minh Smart Key.', 34900000, 
 '{"category": "Xe máy điện", "power": "1500 W", "range": "134-262 km", "acceleration": "<15s (0-50km/h)", "battery": "LFP 2.4 kWh", "max_speed": "70 km/h"}', '[]', 1),

(1, 'VinFast Viper', 'vinfast-viper', 'Xe máy điện cao cấp với phanh đĩa/cơ an toàn.', 45500000, 
 '{"category": "Xe máy điện", "power": "1800 W", "range": "82-156 km", "acceleration": "15s (0-50km/h)", "battery": "LFP 1.5 kWh", "max_speed": "70 km/h"}', '[]', 1),

-- NHÓM Ô TÔ ĐIỆN (Category 2)
(2, 'VinFast VF 3 Eco', 'vinfast-vf3-eco', 'SUV mini sành điệu, nhỏ gọn và linh hoạt.', 302000000, 
 '{"category": "SUV phân khúc Mini", "power": "30 kW", "range": "215 km (NEDC)", "acceleration": "N/A", "battery": "18.64 kWh", "top_speed": "100 km/h"}', '[]', 1),

(2, 'VinFast VF 3 Plus', 'vinfast-vf3-plus', 'Phiên bản cao cấp của VF 3 tích hợp Android Auto.', 315000000, 
 '{"category": "SUV phân khúc Mini", "power": "30 kW", "range": "215 km (NEDC)", "acceleration": "N/A", "battery": "18.64 kWh", "top_speed": "100 km/h"}', '[]', 1),

(2, 'VinFast VF 5 Plus', 'vinfast-vf5-plus', 'SUV hạng A lý tưởng với 6 túi khí an toàn.', 529000000, 
 '{"category": "SUV phân khúc A", "power": "100 kW", "range": "326 km (NEDC)", "acceleration": "12s (0-100km/h)", "battery": "37.23 kWh"}', '[]', 1),

(2, 'VinFast VF 6 Eco', 'vinfast-vf6-eco', 'Sự lựa chọn hoàn hảo cho gia đình trẻ.', 689000000, 
 '{"category": "SUV phân khúc B", "power": "130 kW", "range": "485 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '[]', 1),

(2, 'VinFast VF 6 Plus', 'vinfast-vf6-plus', 'Hiệu năng cao với công suất 201 hp.', 745000000, 
 '{"category": "SUV phân khúc B", "power": "150 kW", "range": "460 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '[]', 1),

(2, 'VinFast VF MPV 7', 'vinfast-vf-mpv7', 'Dòng xe gia đình 7 chỗ trọn vẹn mọi hành trình.', 819000000, 
 '{"category": "MPV", "power": "150 kW", "range": "450 km (NEDC)", "acceleration": "N/A", "battery": "60.13 kWh"}', '[]', 1),

(2, 'VinFast VF 7 Eco', 'vinfast-vf7-eco', 'SUV hạng C với thiết kế tương lai.', 789000000, 
 '{"category": "SUV phân khúc C", "power": "130 kW", "range": "440 km (NEDC)", "acceleration": "N/A", "battery": "59.6 kWh"}', '[]', 1),

(2, 'VinFast VF 7 Plus', 'vinfast-vf7-plus', 'Sức mạnh vượt trội công suất 150 kW.', 889000000, 
 '{"category": "SUV phân khúc C", "power": "150 kW", "range": "N/A", "acceleration": "N/A", "battery": "70 kWh"}', '[]', 1),

(2, 'VinFast VF 8 Eco', 'vinfast-vf8-eco', 'SUV hạng D đẳng cấp, quãng đường di chuyển xa.', 1019000000, 
 '{"category": "SUV phân khúc D", "power": "150 kW", "range": "562 km (NEDC)", "acceleration": "11.8s (0-100km/h)", "battery": "87.7 kWh"}', '[]', 1),

(2, 'VinFast VF 8 Plus', 'vinfast-vf8-plus', 'SUV mạnh mẽ 402 hp, dẫn động AWD.', 1199000000, 
 '{"category": "SUV phân khúc D", "power": "300 kW", "range": "457 km (WLTP)", "acceleration": "5.58s (0-100km/h)", "battery": "87.7 kWh"}', '[]', 1),

(2, 'VinFast VF 9 Eco', 'vinfast-vf9-eco', 'SUV hạng E siêu sang, pin CATL 123 kWh.', 1499000000, 
 '{"category": "SUV phân khúc E", "power": "300 kW", "range": "626 km (WLTP)", "acceleration": "N/A", "battery": "123 kWh"}', '[]', 1),

(2, 'VinFast VF 9 Plus', 'vinfast-vf9-plus', 'Đỉnh cao SUV điện với massage ghế và dẫn động AWD.', 1699000000, 
 '{"category": "SUV phân khúc E", "power": "300 kW", "range": "602 km (WLTP)", "acceleration": "N/A", "battery": "123 kWh"}', '[]', 1);

-- ==========================================================
-- 2. DỮ LIỆU CÂU HỎI THƯỜNG GẶP (FAQS)
-- ==========================================================

INSERT IGNORE INTO faqs (question, answer, sort_order) VALUES
('Chính sách bảo hành ô tô điện VinFast như thế nào?', 'Bảo hành xe mới từ 7-10 năm hoặc 160.000-200.000 km tùy dòng xe. Pin cao áp cũng được bảo hành tương đương.', 1),
('Làm sao để tìm trạm sạc công cộng?', 'Khách hàng có thể tìm kiếm, đặt chỗ và thanh toán tại hơn 150.000 cổng sạc toàn quốc qua ứng dụng VinFast.', 2);