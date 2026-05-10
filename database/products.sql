-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 10, 2026 at 05:15 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vinfast_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` smallint(6) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Electric Motorbike', 'electric-motorbike'),
(2, 'Electric Car', 'electric-car');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `body` text NOT NULL,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn An', 'nv.an@example.com', '0901234567', 'Tôi muốn tư vấn chi tiết về thủ tục trả góp cho xe VF8.', 'unread', '2026-05-10 09:04:50', '2026-05-10 09:04:50'),
(2, 'Trần Thị Bích', 'tt.bich@example.com', '0912345678', 'Cho hỏi giá lăn bánh của VF3 tại khu vực Hà Nội là bao nhiêu?', 'read', '2026-05-10 09:04:50', '2026-05-10 09:04:50'),
(3, 'Lê Văn Cường', 'lv.cuong@example.com', '0923456789', 'Chính sách bảo hành pin 10 năm của VinFast áp dụng như thế nào?', 'unread', '2026-05-10 09:04:50', '2026-05-10 09:04:50'),
(4, 'Phạm Minh Đức', 'pm.duc@example.com', '0934567890', 'Tôi cần tìm danh sách các trạm sạc nhanh tại khu vực Đà Nẵng.', 'replied', '2026-05-10 09:04:50', '2026-05-10 09:04:50'),
(5, 'Hoàng Anh Em', 'ha.em@example.com', '0945678901', 'Cần nhận báo giá chi tiết và khuyến mãi cho xe máy điện Evo 200.', 'unread', '2026-05-10 09:04:50', '2026-05-10 09:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE `email` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `topic_id`, `question`, `answer`, `sort_order`, `is_active`, `created_at`) VALUES
(2, 2, 'VinFast có những dòng xe nào đang bán?', 'VinFast hiện cung cấp các dòng xe điện từ VF 3 (mini city car) đến VF 9 (SUV 7 chỗ cỡ lớn), phù hợp đa dạng nhu cầu di chuyển cá nhân và gia đình. Ngoài ra còn có xe máy điện Klara, Vento và Ludo.', 1, 0, '2026-05-07 12:31:39'),
(3, 2, 'Pin xe VinFast có bảo hành bao lâu?', 'VinFast bảo hành pin xe điện trong vòng 8 năm hoặc 160.000 km (tùy điều kiện nào đến trước). Chính sách bảo hành áp dụng khi dung lượng pin giảm xuống dưới 70% so với công bố.', 2, 1, '2026-05-07 12:31:39'),
(4, 2, 'Tôi có thể lái thử xe VinFast ở đâu?', 'Bạn có thể đăng ký lái thử miễn phí tại tất cả showroom VinFast trên toàn quốc. Ngoài ra VinFast thường xuyên tổ chức các sự kiện lái thử lưu động tại các trung tâm thương mại lớn.', 3, 1, '2026-05-07 12:31:39'),
(5, 2, 'VinFast có gói thuê pin (Battery Subscription) không?', 'Có. VinFast cung cấp gói thuê pin hàng tháng, giúp giảm chi phí mua xe ban đầu. Khách hàng có thể lựa chọn mua pin hoặc thuê pin tùy theo nhu cầu sử dụng.', 4, 1, '2026-05-07 12:31:39'),
(6, 3, 'Thời gian sạc đầy xe VinFast mất bao lâu?', 'Sạc nhanh DC 60kW: khoảng 35–55 phút từ 20% đến 80%. Sạc nhanh DC 120kW: khoảng 18–25 phút từ 20% đến 80%. Sạc AC tại nhà 7kW: khoảng 6–8 tiếng sạc qua đêm.', 1, 1, '2026-05-07 12:31:39'),
(7, 3, 'Trạm sạc V-Green có ở những đâu?', 'V-Green hiện có hơn 2.300 trạm sạc tại 63 tỉnh thành, bao gồm hầu hết các trung tâm thương mại Vincom, cao tốc, sân bay và bãi đỗ xe công cộng. Tra cứu vị trí trạm sạc gần nhất qua ứng dụng VinFast.', 2, 1, '2026-05-07 12:31:39'),
(8, 3, 'Tôi có thể sạc xe VinFast tại nhà không?', 'Có. VinFast cung cấp bộ sạc AC 7kW lắp đặt tại nhà. Đội kỹ thuật sẽ khảo sát và tư vấn miễn phí. Chi phí lắp đặt tùy thuộc vào hệ thống điện hiện có của gia đình bạn.', 3, 1, '2026-05-07 12:31:39'),
(9, 4, 'Quy trình đặt mua xe VinFast như thế nào?', 'Bước 1: Chọn xe và cấu hình tại website hoặc showroom. Bước 2: Đặt cọc (thường 10–20 triệu đồng tùy dòng xe). Bước 3: Ký hợp đồng và thanh toán. Bước 4: Nhận xe tại showroom hoặc giao tận nơi.', 1, 1, '2026-05-07 12:31:39'),
(10, 4, 'VinFast có hỗ trợ vay ngân hàng không?', 'Có. VinFast hợp tác với nhiều ngân hàng và công ty tài chính như VPBank, Techcombank, VinFast Finance... Lãi suất ưu đãi từ 7,9%/năm, tỷ lệ vay lên đến 80% giá trị xe, thời hạn vay tối đa 84 tháng.', 2, 1, '2026-05-07 12:31:39'),
(11, 4, 'Tôi có thể hủy đơn đặt cọc không?', 'Bạn có thể hủy đặt cọc trong vòng 7 ngày kể từ ngày đặt cọc và được hoàn lại 100% tiền cọc. Sau thời hạn này, chính sách hoàn cọc sẽ tùy theo điều khoản hợp đồng đã ký.', 3, 1, '2026-05-07 12:31:39'),
(12, 4, 'Thời gian chờ giao xe là bao lâu?', 'Thông thường từ 4–8 tuần kể từ khi ký hợp đồng, tùy dòng xe và màu sắc. Đối với các dòng xe sẵn hàng tại kho, thời gian có thể rút ngắn xuống 1–2 tuần.', 4, 1, '2026-05-07 12:31:39'),
(13, 5, 'Chế độ bảo hành xe VinFast như thế nào?', 'Khung gầm & thân xe: 5 năm hoặc 150.000 km. Pin: 8 năm hoặc 160.000 km. Động cơ điện: 5 năm hoặc 150.000 km. Phụ kiện đi kèm: 12 tháng. Tất cả điều kiện theo tài liệu bảo hành chính hãng.', 1, 1, '2026-05-07 12:31:39'),
(14, 5, 'Lịch bảo dưỡng định kỳ của xe VinFast là gì?', 'Xe điện VinFast không cần thay dầu máy, nên lịch bảo dưỡng đơn giản hơn xe xăng. Kiểm tra tổng quát 6 tháng/lần hoặc 10.000 km, bao gồm kiểm tra phanh, lốp, hệ thống điện, làm sạch tiếp điểm sạc.', 2, 1, '2026-05-07 12:31:39'),
(15, 5, 'Tôi có thể đưa xe đến bảo dưỡng ở đâu?', 'VinFast có hơn 300 trung tâm dịch vụ chính hãng tại 63 tỉnh thành. Đặt lịch dễ dàng qua ứng dụng VinFast, hotline 1900 23 23 hay đến trực tiếp showroom/trung tâm dịch vụ gần nhất.', 3, 1, '2026-05-07 12:31:39'),
(16, 6, 'Ứng dụng VinFast có những tính năng gì?', 'Ứng dụng VinFast cho phép: Theo dõi trạng thái pin và phạm vi đi được; Tìm và điều hướng đến trạm sạc gần nhất; Đặt lịch bảo dưỡng; Điều khiển từ xa (khóa/mở cửa, bật điều hòa trước); Xem lịch sử hành trình và tiêu thụ điện.', 1, 1, '2026-05-07 12:31:39'),
(17, 6, 'VinFast có hỗ trợ cập nhật OTA (Over-The-Air) không?', 'Có. Xe VinFast thế hệ mới hỗ trợ cập nhật phần mềm từ xa qua OTA, giúp bổ sung tính năng mới, cải thiện hiệu suất và vá lỗi mà không cần đến trung tâm dịch vụ.', 2, 1, '2026-05-07 12:31:39'),
(18, 6, 'Hệ thống giải trí trên xe VinFast có Android Auto/Apple CarPlay không?', 'Các dòng VF 5 trở lên hỗ trợ cả Android Auto và Apple CarPlay không dây, tích hợp với màn hình cảm ứng trung tâm. Hệ điều hành VinFast OS riêng cũng cung cấp bản đồ, âm nhạc và trợ lý giọng nói tiếng Việt.', 3, 1, '2026-05-07 12:31:39'),
(19, 7, 'Hotline hỗ trợ của VinFast là số nào?', 'Hotline VinFast: 1900 23 23 (miễn phí, hoạt động 24/7). Bạn cũng có thể liên hệ qua email hotro@vinfastauto.com hoặc chat trực tiếp tại website vinfast.vn.', 1, 1, '2026-05-07 12:31:39'),
(20, 7, 'VinFast có hỗ trợ cứu hộ tại chỗ không?', 'Có. Dịch vụ hỗ trợ đường bộ 24/7 hoàn toàn miễn phí trong thời gian bảo hành, bao gồm: cứu hộ kéo xe, hỗ trợ sạc khẩn cấp tại chỗ và hỗ trợ kỹ thuật từ xa.', 2, 1, '2026-05-07 12:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `faq_topics`
--

CREATE TABLE `faq_topics` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon_svg` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faq_topics`
--

INSERT INTO `faq_topics` (`id`, `name`, `slug`, `icon_svg`, `sort_order`, `is_active`, `created_at`) VALUES
(2, 'Sản phẩm xe', 'xe', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M6 28l4-10h28l4 10\"/><rect x=\"3\" y=\"28\" width=\"42\" height=\"10\" rx=\"2\"/><circle cx=\"12\" cy=\"40\" r=\"3\"/><circle cx=\"36\" cy=\"40\" r=\"3\"/><path d=\"M3 33h42\"/></svg>', 1, 1, '2026-05-07 12:31:39'),
(3, 'Sạc & Năng lượng', 'sac', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M24 6v8M24 34v8\"/><path d=\"M6 24h8M34 24h8\"/><circle cx=\"24\" cy=\"24\" r=\"10\"/><path d=\"M20 24l3 3 6-6\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>', 2, 1, '2026-05-07 12:31:39'),
(4, 'Mua xe & Đặt cọc', 'mua', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M8 10h32l-4 20H12L8 10z\"/><circle cx=\"17\" cy=\"42\" r=\"3\"/><circle cx=\"33\" cy=\"42\" r=\"3\"/><path d=\"M3 6h5\"/></svg>', 3, 1, '2026-05-07 12:31:39'),
(5, 'Bảo hành & Bảo dưỡng', 'baodong', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\">\n  <!-- Bánh răng cách điệu -->\n  <path d=\"M24 14v-4M24 38v-4M14 24h-4M38 24h-4M16.9 16.9l-2.8-2.8M33.9 33.9l-2.8-2.8M16.9 31.1l-2.8 2.8M33.9 14.1l-2.8 2.8\" stroke-opacity=\"0.4\"/>\n  <circle cx=\"24\" cy=\"24\" r=\"7\" />\n  <!-- Dấu tích hoàn thành -->\n  <path d=\"M21 24l2 2 4-4\" />\n</svg>\n', 4, 1, '2026-05-07 12:31:39'),
(6, 'Ứng dụng & Công nghệ', 'ungdung', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><rect x=\"12\" y=\"3\" width=\"24\" height=\"42\" rx=\"4\"/><path d=\"M19 7h10\"/><circle cx=\"24\" cy=\"40\" r=\"2\"/></svg>', 5, 1, '2026-05-07 12:31:39'),
(7, 'Hỗ trợ khách hàng', 'hotro', '<svg viewBox=\"0 0 48 48\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M24 4C13 4 4 13 4 24c0 3.5.9 6.8 2.5 9.6L4 44l10.4-2.5C17.2 43.1 20.5 44 24 44c11 0 20-9 20-20S35 4 24 4z\"/><path d=\"M16 20s0-4 8-4 8 4 8 4-4 4-8 4-8 4-8 8 4 8 8 8\"/><path d=\"M24 38v2\"/></svg>', 6, 1, '2026-05-07 12:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(300) NOT NULL,
  `slug` varchar(320) NOT NULL,
  `body` longtext NOT NULL,
  `catalog` enum('Công ty','Ô tô điện','Xe máy điện') DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `news_state` enum('Hiển thị','Ẩn') NOT NULL DEFAULT 'Hiển thị'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `body`, `catalog`, `views`, `created_at`, `news_state`) VALUES
(1, 'VINFAST HỢP TÁC VỚI 14 ĐẠI LÝ XE MÁY ĐIỆN PHILIPPINES, ĐẨY NHANH MỞ RỘNG MẠNG LƯỚI TRÊN TOÀN QUỐC', 'vinfast-hop-tac-voi-14-dai-ly-xe-may-dien-philippines-day-nhanh-mo-rong-mang-luoi-tren-toan-quoc', '<p class=\'mb-4 text-justify leading-relaxed\'>Hà Nội, ngày 18/04/2026 – VinFast công bố ký kết Biên bản ghi nhớ (MOU) hợp tác chiến lược với 14 đối tác đại lý xe máy điện tại Philippines. Thỏa thuận cho thấy khả năng bứt tốc của VinFast trong việc mở rộng mạng lưới bán lẻ, đồng thời tái khẳng định cam kết dài hạn của hãng trong việc thúc đẩy giao thông xanh và phát triển hệ sinh thái xe điện toàn diện tại Philippines.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Để triển khai kế hoạch ra mắt sản phẩm vào tháng 6/2026, VinFast tiếp tục hợp tác với các đại lý phân phối xe máy hàng đầu Philippines bao gồm Wheeltek Motor Sales Corporation; Gentrade International Phils., Inc. (Transcycle); Superbikes Corporation; Motoxpress Sales Corporation; FMN Industrial Corp.; Eduhome Enterprise, Inc.; Keymotors Incorporated; Motorpro by Abenson Ventures Inc. and Aserco; Moto Atelier Inc.; Ciclo Suerte; HG Motorzone; HGC Main Marketing (Motorboy); BLC Cycle Parts Supply; và Auto Ten Trade &amp; Services Corp. Đây đều là những đối tác có mạng lưới cửa hàng rộng khắp, năng lực vận hành tốt, giàu kinh nghiệm trong lĩnh vực kinh doanh xe máy và có định hướng chuyển đổi xanh rõ ràng.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/1/1.jpg\' alt=\'VinFast tiếp tục hợp tác với nhiều đại lý quy mô lớn, thuộc nhóm nhà phân phối xe máy hàng đầu Philippines để triển khai kế hoạch ra mắt sản phẩm vào tháng 6/2026.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>VinFast tiếp tục hợp tác với nhiều đại lý quy mô lớn, thuộc nhóm nhà phân phối xe máy hàng đầu Philippines để triển khai kế hoạch ra mắt sản phẩm vào tháng 6/2026.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast và các đối tác sẽ phối hợp triển khai nhanh hệ thống showroom trên toàn thị trường, hướng tới khai trương đồng loạt nhiều điểm bán ngay khi sản phẩm chính thức ra mắt. Đây sẽ là nền tảng để VinFast tăng tốc mở rộng mạng lưới bán hàng và dịch vụ trên toàn quốc.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Các showroom dự kiến được phát triển tại những khu vực có nhu cầu đi lại cao và tiềm năng chuyển đổi sang phương tiện xanh như Metro Manila, Metro Davao, Rizal, Laguna, Cavite, Batangas, Metro Cebu, Bulacan và nhiều đô thị trọng điểm khác, đồng thời áp dụng thống nhất tiêu chuẩn vận hành và nhận diện toàn cầu của VinFast.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong giai đoạn đầu, các đại lý sẽ phân phối những dòng xe máy điện sử dụng pin đổi linh hoạt, bao gồm Evo, Feliz II, Viper, và các dòng xe mới dự kiến ra mắt trong thời gian tới. Bên cạnh đó, VinFast sẽ tiếp tục nghiên cứu, điều chỉnh và bổ sung thêm các mẫu xe mới để phù hợp hơn với điều kiện hạ tầng cũng như thói quen sử dụng của người tiêu dùng Philippines.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Philippines là một trong những thị trường xe máy lớn tại Đông Nam Á, với nhu cầu di chuyển cá nhân cao và tốc độ đô thị hóa nhanh. Trong bối cảnh chi phí nhiên liệu biến động và xu hướng chuyển đổi sang các giải pháp bền vững ngày càng rõ nét, xe máy điện được đánh giá là một trong những hướng đi tiềm năng, đặc biệt khi đi kèm hệ sinh thái hỗ trợ đồng bộ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast tiên phong định hướng phát triển hệ sinh thái xe máy điện toàn diện tại Philippines, bao gồm mạng lưới đại lý, dịch vụ hậu mãi, giải pháp tài chính và hạ tầng năng lượng. Đáng chú ý, hãng đặt mục tiêu triển khai khoảng 30.000 tủ đổi pin trên toàn quốc trong thời gian tới, thông qua hợp tác với đối tác phát triển hạ tầng, nhằm mang lại trải nghiệm sử dụng thuận tiện và linh hoạt cho người dùng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Võ Thị Cẩm Tú, Giám đốc điều hành VinFast Xe máy điện thị trường quốc tế, cho biết: “Việc hợp tác với 14 đại lý lớn tại Philippines là bước đi quan trọng nhằm nhanh chóng xây dựng nền tảng phân phối và dịch vụ vững chắc ngay từ giai đoạn đầu. Chúng tôi không chỉ mang đến các sản phẩm phù hợp với nhu cầu địa phương, mà còn phát triển hệ sinh thái đồng bộ, từ hạ tầng đổi pin đến hậu mãi, qua đó tạo điều kiện thuận lợi để người tiêu dùng tiếp cận và sử dụng xe máy điện một cách dễ dàng, hiệu quả và bền vững”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đầu năm 2026, VinFast đã công bố kế hoạch mở rộng kinh doanh xe máy điện tại 5 thị trường quốc tế trọng điểm gồm Philippines, Indonesia, Ấn Độ, Thái Lan và Malaysia, như một phần trong chiến lược toàn cầu hóa hệ sinh thái sản phẩm xanh.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong những năm qua, VinFast từng bước xây dựng hiện diện tại các thị trường trọng điểm ở Đông Nam Á, bao gồm Philippines, thông qua danh mục ô tô điện đa dạng và các hợp tác chiến lược trong lĩnh vực hạ tầng và dịch vụ. Việc mở rộng sang mảng xe máy điện tiếp tục hoàn thiện hệ sinh thái di chuyển xanh của hãng tại thị trường nói riêng và khu vực nói chung, đồng thời mở ra thêm lựa chọn linh hoạt cho người tiêu dùng trong quá trình chuyển đổi sang phương tiện giao thông xanh.</p>', 'Công ty', 47, '2026-04-20 08:57:48', 'Hiển thị'),
(2, 'LẮNG NGHE TẬN TÂM - NÂNG TẦM TRẢI NGHIỆM', 'lang-nghe-tan-tam-nang-tam-trai-nghiem-1', '<p class=\'mb-4 text-justify leading-relaxed\'>VinFast tin rằng, sự thấu hiểu bắt đầu từ việc lắng nghe – từng góp ý của khách hàng đều là điều quý giá.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Sau khi các khảo sát chất lượng Dịch vụ Hậu mãi năm 2025 khép lại, 530.000 VPoint tri ân đã “định cư” trong tài khoản VinClub của chủ nhân mới. Đó là những chủ xe tinh tế, phản hồi “nhanh hơn điện” và luôn đồng hành cùng VinFast trong hành trình hoàn thiện dịch vụ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Nếu Quý khách đã từng “lỡ nhịp”, thì cơ hội đã quay trở lại! VinFast chính thức triển khai Khảo sát Dịch vụ Hậu mãi Quý I/2026 – để tiếp tục lắng nghe, thấu hiểu và mang đến trải nghiệm ngày càng tận tâm, trọn vẹn hơn.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/2/1.png\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trân trọng kính mời Quý khách tham gia khảo sát để chia sẻ cảm nhận thực tế về mức độ hài lòng, sự tin tưởng và trải nghiệm dịch vụ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thời gian triển khai: từ 17/04 đến 24/04/2025</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Quà tặng tri ân dành cho khách hàng sở hữu ô tô:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast xin gửi tới 2.000 khách hàng đầu tiên hoàn thành khảo sát, tối đa 50 VPoint/khách như một lời tri ân vì những đóng góp của Quý khách. Điểm thưởng sẽ được cộng vào tài khoản VinClub của khách hàng sau khi kết thúc chương trình và hoàn thành xác thực thông tin.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tham gia khảo sát bằng cách quét QR code trên hình hoặc truy cập link:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>https://forms.office.com/r/27isC4MHPS</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast sẽ triển khai khảo sát định kỳ theo quý để tiếp tục ghi nhận ý kiến khách hàng, không ngừng nâng cao trải nghiệm Dịch vụ Hậu mãi – tận tâm hơn mỗi ngày, trọn vẹn hơn trên từng hành trình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Mọi thắc mắc Quý khách vui lòng liên hệ hotline 1900 23 23 89 hoặc Xưởng dịch vụ VinFast gần nhất để được hỗ trợ.</p>', 'Ô tô điện', 8, '2026-04-17 20:11:59', 'Hiển thị'),
(3, 'LẮNG NGHE TẬN TÂM - NÂNG TẦM TRẢI NGHIỆM', 'lang-nghe-tan-tam-nang-tam-trai-nghiem', '<p class=\'mb-4 text-justify leading-relaxed\'>Mỗi góp ý, mỗi chia sẻ, mỗi trải nghiệm thực tế từ Khách hàng đều là cơ sở để hành trình sử dụng xe ngày càng trọn vẹn hơn.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Với mong muốn không ngừng nâng cao trải nghiệm, VinFast triển khai Chương trình khảo sát chất lượng Dịch vụ Hậu mãi dành cho chủ xe máy điện, để ghi nhận những đánh giá thực tế trong quá trình sử dụng dịch vụ. Mọi phản hồi đều là cơ sở để VinFast không ngừng cải tiến, nâng cao chất lượng và tối ưu trải nghiệm cho khách hàng.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/3/1.png\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trân trọng mời Quý khách tham gia khảo sát để chia sẻ mức độ hài lòng, sự tin tưởng và cảm nhận thực tế.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thời gian triển khai: từ 17/04 đến 24/04/2025</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Quà tặng tri ân dành cho khách hàng sở hữu xe máy điện:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast xin gửi tới 1.500 khách hàng đầu tiên hoàn thành khảo sát, tối đa 50 VPoint/khách như một lời tri ân vì những đóng góp của Quý khách. Điểm thưởng sẽ được cộng vào tài khoản VinClub của khách hàng sau khi kết thúc chương trình và hoàn thành xác thực thông tin.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tham gia khảo sát bằng cách quét QR code trên hình hoặc truy cập link:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>https://forms.office.com/r/TkyJX6WG7D</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast sẽ triển khai khảo sát định kỳ theo quý để tiếp tục ghi nhận ý kiến khách hàng, không ngừng nâng cao trải nghiệm Dịch vụ Hậu mãi – tận tâm hơn mỗi ngày, trọn vẹn hơn trên từng hành trình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Mọi thắc mắc Quý khách vui lòng liên hệ hotline 1900 23 23 89 hoặc Xưởng dịch vụ VinFast gần nhất để được hỗ trợ.</p>', 'Xe máy điện', 28, '2026-04-17 20:14:45', 'Hiển thị'),
(4, 'Tập đoàn Vingroup tự hào đồng hành cùng VTV tại World Cup 2026', 'tap-doan-vingroup-tu-hao-dong-hanh-cung-vtv-tai-world-cup-2026', '<p class=\'mb-4 text-justify leading-relaxed\'>Tập đoàn Vingroup tự hào đồng hành cùng VTV thực hiện sứ mệnh phụng sự khán giả, mang World Cup 2026 đến gần hơn với hàng triệu triệu trái tim Việt Nam.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/4/1.jpeg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Với tinh thần phụng sự cộng đồng và sứ mệnh “Vì một cuộc sống tốt đẹp hơn cho mọi người”, Vingroup mong muốn đưa World Cup 2026 trở thành ngày hội của sự gắn kết, của niềm vui chung và những cảm xúc bùng nổ trên khắp Việt Nam, nơi triệu trái tim Việt cùng chung một nhịp đập đam mê với trái bóng tròn!</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tháng 6 này, hãy cùng VTV và Vingroup chờ đón những trận cầu đỉnh cao và sống trọn từng khoảnh khắc bùng nổ của bóng đá thế giới!</p>', 'Công ty', 8, '2026-04-12 20:20:31', 'Hiển thị'),
(5, 'DOANH SỐ Ô TÔ ĐIỆN VINFAST TĂNG TRƯỞNG BÙNG NỔ TRONG THÁNG 3', 'doanh-so-o-to-dien-vinfast-tang-truong-bung-no-trong-thang-3', '<p class=\'mb-4 text-justify leading-relaxed\'>Hà Nội, ngày 10/04/2026 - VinFast công bố đã bán ra thị trường Việt Nam 27.609 xe ô tô điện các loại trong tháng 3/2026, tăng 127% so với cùng kỳ năm 2025. Kết quả kinh doanh bùng nổ không chỉ khẳng định vị thế dẫn đầu tuyệt đối của VinFast tại thị trường trong nước, mà còn cho thấy xu hướng chuyển đổi sang xe điện đang được người tiêu dùng hưởng ứng mạnh mẽ.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/5/1.jpg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Toàn bộ các dòng ô tô điện VinFast đều ghi nhận mức tăng trưởng vượt bậc về doanh số trong tháng 3/2026. Đặc biệt, riêng trong ngày 28/3, đã có tới 3.520 đơn hàng ô tô điện được VinFast xử lý hoàn tất, sẵn sàng bàn giao cho khách hàng. Đây là kỷ lục kinh doanh trong một ngày cao nhất từ trước đến nay mà một thương hiệu ô tô ghi nhận được tại thị trường Việt Nam.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong đó, Limo Green là mẫu xe ghi nhận doanh số bán cao nhất trong tháng 3, với 6.795 xe bán ra, tăng 276% so với tháng 2, trở thành mẫu xe VinFast được ưa chuộng nhất ở thời điểm hiện tại. Phiên bản dành cho gia đình và khách hàng cá nhân của Limo Green là VF MPV 7 cũng ghi nhận mức tăng trưởng vượt trội, lên tới 116% so với tháng trước, đạt 2.521 xe bán ra. Kết quả trên khẳng định xe điện đang là lựa chọn ưu tiên của tất cả các nhóm khách hàng khi cần mua một chiếc xe 7 chỗ cỡ trung đáp ứng nhu cầu di chuyển của cá nhân, gia đình hay kinh doanh dịch vụ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Các sản phẩm còn lại cũng ghi nhận mức tăng trưởng bùng nổ trong tháng 3/2026. Trong đó, VF 7 đã vươn lên dẫn đầu phân khúc C-SUV toàn thị trường, với 1.732 xe được bàn giao cho khách hàng trong tháng, tăng 115% so với tháng 2.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VF 9, VF 8, VF 6, VF 5 và VF 3 vẫn duy trì sức hút lớn và khẳng định vị thế mẫu xe được ưa chuộng bậc nhất tại các phân khúc tương ứng. Trong đó, VF 6 đạt mức tăng trưởng 202% với 3.152 xe bán ra. VF 5 (bao gồm hai phiên bản dành cho khách hàng cá nhân và kinh doanh dịch vụ) đạt tổng cộng 4.218 xe bán ra, tăng 158%. VF 3 tăng 108%, đạt 4.729 xe bán ra trong tháng 3/2026.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Hai mẫu xe cỡ nhỏ hướng tới nhóm khách hàng kinh doanh dịch vụ chở hàng và chở người trong đô thị - EC Van và Minio Green - cũng ghi nhận mức doanh số tháng cao nhất kể từ khi chính thức bán ra thị trường. Cụ thể, đã có 1.136 xe EC Van và 1.969 xe Minio Green được bàn giao cho khách hàng trong tháng 3/2026.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tính theo lũy kế từ đầu năm, Limo Green hiện là mẫu xe VinFast được ưa chuộng nhất tại thị trường Việt Nam, với tổng cộng 12.471 xe đã được bàn giao, đạt mức trung bình hơn 4.150 xe/tháng. Kế tiếp lần lượt là các mẫu: VF 3 (10.188 xe), VF 5 (8.672 xe, bao gồm Herio Green), VF 6 (6.679 xe), Minio Green (3.809 xe), VF MPV 7 (3.686 xe), VF 7 (3.514 xe)…</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tổng cộng, trong 3 tháng đầu năm 2026, VinFast đã bàn giao ra thị trường 53.684 ô tô điện các loại, khẳng định vị thế là thương hiệu ô tô dẫn đầu thị trường một cách tuyệt đối.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Dương Thị Thu Trang - Phó Tổng giám đốc Kinh doanh ô tô VinFast toàn cầu cho biết: “Trước biến động khó lường của giá xăng dầu, xe điện đang ngày càng chứng minh giá trị và những ưu điểm vượt trội về cả chất lượng, khả năng vận hành và chi phí sử dụng. Sự đón nhận và ủng hộ nồng nhiệt của người tiêu dùng chính là động lực mạnh mẽ để chúng tôi tiếp tục phát triển, nâng cao hơn nữa chất lượng sản phẩm, dịch vụ để mang đến sự hài lòng ngày càng cao hơn cho khách hàng”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Việc các mẫu xe VinFast liên tiếp và đồng loạt dẫn đầu ở hầu hết các phân khúc trên thị trường cho thấy xu hướng chuyển đổi sang xe điện là lựa chọn tất yếu của người dùng trong nước cả về hiệu quả kinh tế, chất lượng và công năng sử dụng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ tại Việt Nam, VinFast cũng ghi nhận kết quả kinh doanh tốt nhất từ trước đến nay tại các thị trường quốc tế trọng điểm như Ấn Độ, Indonesia và Philippines trong tháng 3/2026. Tính đến hiện tại, VinFast đang là 1 trong 2 thương hiệu ô tô điện dẫn đầu thị trường Philippines, nằm trong top 3 tại Indonesia và top 4 tại Ấn Độ, xét về doanh số bán ô tô điện trong tháng 3/2026./.</p>', 'Công ty', 5, '2026-04-10 20:23:54', 'Hiển thị'),
(6, 'VINFAST TRIỂN KHAI CHƯƠNG TRÌNH CHO THUÊ XE GREEN TẠI INDONESIA VÀ PHILIPPINES, HỖ TRỢ TÀI XẾ DỊCH VỤ CHUYỂN ĐỔI XANH', 'vinfast-trien-khai-chuong-trinh-cho-thue-xe-green-tai-indonesia-va-philippines-ho-tro-tai-xe-dich-vu-chuyen-doi-xanh', '<p class=\'mb-4 text-justify leading-relaxed\'>Jakarta/Manila, ngày 08/04/2026 – VinFast công bố mở rộng mô hình tiếp cận xe điện dành cho tài xế kinh doanh dịch vụ tại Indonesia và Philippines, bổ sung lựa chọn thuê xe với nhiều hỗ trợ hấp dẫn. Chương trình hướng tới tạo điều kiện tối đa để tài xế dịch vụ dễ dàng chuyển đổi sang xe điện, qua đó thúc đẩy xanh hóa giao thông và khẳng định cam kết của VinFast với mục tiêu giảm phát thải tại Đông Nam Á.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong khuôn khổ chương trình mới, khách hàng hiện có thêm lựa chọn thuê các mẫu xe điện dòng Green của VinFast thông qua hệ thống đại lý chính hãng tại Indonesia và Philippines, bên cạnh hình thức mua xe truyền thống.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Được triển khai trong bối cảnh giá nhiên liệu toàn cầu biến động mạnh, chương trình cung cấp cho tài xế dịch vụ những mẫu xe điện đáng tin cậy, hiệu quả vận hành cao, cùng với mức đầu tư ban đầu thấp, tạo điều kiện nhanh chóng bắt đầu vận doanh mà không cần gánh toàn bộ chi phí sở hữu phương tiện. Với hình thức thuê, tài xế có thể sớm tiếp cận xe với mức đặt cọc thấp, hợp đồng dài hạn, và giá thuê cố định hấp dẫn, chỉ từ 312.500 IDR/ngày tại Indonesia và từ 1.000 PHP/ngày tại Philippines.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đối với khách hàng mua xe, VinFast cung cấp các gói vay tài chính được thiết kế riêng cho dịch vụ vận tải với phương án thanh toán phù hợp với dòng tiền của tài xế, qua đó giúp giảm áp lực chi phí ban đầu, tối ưu hiệu quả tài chính, và rút ngắn thời gian hoàn vốn.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Hình thức thuê xe, kết hợp cùng hình thức mua xe truyền thống, mang lại sự linh hoạt cao độ, cho phép tài xế và đơn vị vận tải lựa chọn phương án tiếp cận phù hợp với năng lực tài chính và nhu cầu vận hành của mình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Chương trình áp dụng với dải sản phẩm Green của VinFast như Herio Green và Limo Green, vốn được phát triển chuyên biệt cho dịch vụ vận tải tần suất cao, có thể đáp ứng đa dạng nhu cầu từ vận hành cá nhân đến vận chuyển hành khách quy mô lớn. Các mẫu xe Green được tối ưu về độ bền, hiệu suất năng lượng và tổng chi phí sở hữu, đồng thời vẫn đảm bảo sự thoải mái trong quá trình vận hành hàng ngày.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/6/1.jpg\' alt=\'VinFast cung cấp đồng thời hai hình thức mua và thuê xe cho dải sản phẩm Green như Herio Green và Limo Green, giúp tài xế và doanh nghiệp linh hoạt lựa chọn theo nhu cầu và khả năng tài chính.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>VinFast cung cấp đồng thời hai hình thức mua và thuê xe cho dải sản phẩm Green như Herio Green và Limo Green, giúp tài xế và doanh nghiệp linh hoạt lựa chọn theo nhu cầu và khả năng tài chính.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Việc triển khai chương trình sẽ bắt đầu tại các khu vực đô thị quan trọng, bao gồm vùng đô thị Jakarta (Greater Jakarta, Indonesia) và Metro Manila (Philippines), và dự kiến sẽ mở rộng dần trên cả hai thị trường.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ngoài được tiếp cận các mẫu xe điện chuyên dụng, khách hàng là tài xế dịch vụ còn sở hữu lợi thế tới từ hệ sinh thái xe điện toàn diện của VinFast tại Đông Nam Á, bao gồm các chính sách hỗ trợ hấp dẫn như miễn phí sạc tại hệ thống trạm V-Green đến hết tháng 3/2029, góp phần giảm chi phí vận hành và nâng cao hiệu quả kinh doanh.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Dương Thị Thu Trang, Phó Tổng Giám đốc Kinh doanh Ô tô VinFast toàn cầu, cho biết: “Việc mở rộng mô hình tiếp cận xe điện tại Indonesia và Philippines là bước đi tiếp theo của VinFast trong chiến lược thúc đẩy xanh hóa ngành vận tải, lĩnh vực giữ vai trò then chốt trong quá trình chuyển đổi xanh của mỗi quốc gia. Sáng kiến này không chỉ cung cấp phương tiện tạo thu nhập ổn định, mà còn góp phần nâng cao chất lượng sống và giảm phát thải, đồng thời khẳng định cam kết đồng hành của VinFast với cộng đồng tài xế trên hành trình phát triển nghề nghiệp theo hướng xanh và bền vững”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast đang mạnh mẽ xây dựng hệ sinh thái xe điện toàn diện tại các thị trường Đông Nam Á trọng điểm như Indonesia và Philippines, với danh mục sản phẩm đa dạng cho cả khách hàng cá nhân và vận tải dịch vụ, cùng mạng lưới đại lý, hậu mãi và hạ tầng trạm sạc liên tục được mở rộng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Song song đó, Vingroup, công ty mẹ của VinFast, đang triển khai nhiều giải pháp hỗ trợ chuyển đổi xanh, tiêu biểu như chương trình Thu xăng - Đổi điện với ưu đãi bổ sung 3% giá ô tô, áp dụng cộng dồn với các chính sách hiện hành tại Indonesia và Philippines, cùng ưu đãi giảm 10% giá cước dịch vụ di chuyển bằng xe điện Green SM tại Indonesia đến hết ngày 30/4/2026. Thông qua các giải pháp đồng bộ, VinFast từng bước thúc đẩy quá trình xanh hóa giao thông, đồng thời mang lại hiệu quả kinh tế thiết thực cho người dùng và cộng đồng trong khu vực.</p>', 'Ô tô điện', 7, '2026-04-07 20:25:35', 'Hiển thị'),
(7, 'XE MÁY ĐIỆN VINFAST LẬP KỶ LỤC DOANH SỐ CHƯA TỪNG CÓ', 'xe-may-dien-vinfast-lap-ky-luc-doanh-so-chua-tung-co', '<p class=\'mb-4 text-justify leading-relaxed\'>Hà Nội, ngày 03/04/2026 - VinFast đã nhận hơn 135.000 đơn đặt hàng, xuất xưởng ra thị trường hơn 93.000 xe máy điện trong tháng 3/2026, đạt mức doanh số tháng cao nhất từ trước đến nay. Mức tăng cao kỷ lục cho thấy làn sóng chuyển đổi xanh đang tiến triển mạnh mẽ, đồng thời khẳng định vị thế vững chắc và tầm ảnh hưởng ngày càng gia tăng của VinFast trên thị trường xe máy Việt Nam.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/7/1.jpg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tháng 3/2026, VinFast đã nhận được tổng cộng hơn 135.000 đơn đặt hàng từ các đại lý phân phối trên toàn quốc và đã xuất xưởng hơn 93.000 xe. Trong đó, Evo và Feliz là hai dòng sản phẩm được ưa chuộng nhất, với hơn 52.000 xe Evo và hơn 24.000 xe Feliz đến tay khách hàng trong tháng 3. Đây cũng là những mẫu xe bán chạy bậc nhất trên thị trường xe máy (bao gồm cả xe chạy xăng) kể từ đầu năm.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Về xu hướng, VinFast ghi nhận sức mua tăng đều tại các địa phương, trong đó số lượng lớn nhất thuộc về hai thành phố lớn là Hà Nội và TP.HCM. Riêng tại Thủ đô, chỉ trong tháng 3 đã có hơn 20.000 xe máy điện VinFast được giao cho khách hàng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Mặc dù đã nhanh chóng tăng công suất và đạt sản lượng lớn nhất từ trước tới nay, lượng xe máy điện VinFast xuất xưởng trong tháng 3 vẫn chưa đáp ứng đủ nhu cầu bức thiết của thị trường. Để khách hàng không phải chờ đợi lâu, hiện VinFast đã đẩy công suất lên mức tối đa nhằm hoàn tất các đơn hàng và sẵn sàng đáp ứng nhu cầu được dự báo sẽ tiếp tục tăng cao trong thời gian tới.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Hoàng Hà - Tổng Giám đốc Xe máy điện VinFast thị trường Việt Nam cho biết: “Kỷ lục 135.000 đơn đặt hàng chỉ trong tháng 3 minh chứng rõ nét cho nhu cầu bức thiết của người dân đối với loại hình phương tiện di chuyển xanh, nhằm từ bỏ sự phụ thuộc vào nhiên liệu hóa thạch và trực tiếp đóng góp cho môi trường. VinFast sẽ luôn đồng hành, sát cánh cùng quá trình chuyển đổi xanh của người dân và đất nước, với những sản phẩm đẳng cấp, chất lượng cùng hạ tầng trạm sạc, đổi pin rộng khắp và mạng lưới dịch vụ hậu mãi xuất sắc”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Doanh số xe máy điện tăng cao không chỉ xuất phát từ diễn biến khó lường của giá xăng dầu mà còn bởi hiệu quả kinh tế và chất lượng vượt trội đã được thị trường kiểm chứng của các dòng xe máy điện VinFast.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đặc biệt, VinFast đã liên tục ban hành các chính sách có lợi cho khách hàng nhằm chia sẻ khó khăn với người tiêu dùng. Điển hình là Chương trình hỗ trợ khẩn cấp “Thu xăng - Đổi điện” gia hạn tới hết ngày 30/04/2026 - ưu đãi thêm 5% giá xe cho những khách hàng chuyển đổi từ xe xăng sang xe máy điện VinFast; Chương trình “Mãnh liệt vì tương lai Xanh” - hỗ trợ mua xe trả góp với 0 đồng đối ứng, ưu đãi 6% giá xe, hỗ trợ 100% lệ phí trước bạ, miễn phí sạc pin tại các trạm sạc công cộng V-Green đến hết ngày 31/5/2027, miễn phí đổi pin 20 lần/tháng đến hết ngày 30/06/2028...</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bên cạnh đó, để hỗ trợ sinh kế cho các tài xế công nghệ, GSM - đối tác chiến lược của VinFast cũng công bố chương trình miễn phí thuê và đổi pin không giới hạn số lần lên tới 3 năm (đến hết ngày 31/3/2029) cho các bác tài mua xe và đăng ký vận doanh độc quyền trên Xanh SM Platform.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Diễn biến khó lường của giá xăng dầu cộng với những chính sách vì người dùng của hệ sinh thái xanh Vingroup là chất xúc tác đưa quá trình chuyển đổi từ xe xăng sang xe điện lên vòng quay tốc độ mới, góp phần nâng cấp chất lượng sống và mở ra tương lai xanh, sạch hơn tại Việt Nam./.</p>', 'Xe máy điện', 7, '2026-04-02 20:30:33', 'Hiển thị'),
(8, 'VINFAST CHÍNH THỨC NHẬN ĐẶT CỌC VF MPV 7 TẠI ẤN ĐỘ', 'vinfast-chinh-thuc-nhan-dat-coc-vf-mpv-7-tai-an-do', '<p class=\'mb-4 text-justify leading-relaxed\'>Gurugram, 02/04/2026 – VinFast chính thức nhận đặt cọc mẫu xe điện thứ ba của hãng tại Ấn Độ, VF MPV 7 - dòng xe đa dụng 7 chỗ cao cấp dành cho khách hàng cá nhân tại thị trường. Sở hữu không gian rộng rãi, công nghệ thông minh và tính ứng dụng cao, VF MPV 7 sẽ đáp ứng mọi nhu cầu di chuyển hàng ngày với giá trị vượt trội, khẳng định cam kết dài hạn của hãng trong việc phát triển hệ sinh thái di chuyển xanh toàn diện tại Ấn Độ, mang đến các giải pháp giao thông thông minh, hiện đại và bền vững cho mọi gia đình.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/8/1.jpg\' alt=\'VinFast chính thức nhận đặt cọc mẫu xe điện thứ ba tại Ấn Độ – VF MPV 7, dòng xe đa dụng 7 chỗ cao cấp dành cho khách hàng cá nhân.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>VinFast chính thức nhận đặt cọc mẫu xe điện thứ ba tại Ấn Độ – VF MPV 7, dòng xe đa dụng 7 chỗ cao cấp dành cho khách hàng cá nhân.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bắt đầu từ ngày 02/04/2026, VinFast sẽ nhận đặt cọc cho VF MPV 7 thông qua website: https://vinfastauto.in/ và hệ thống 50 đại lý phân phối chính hãng trên toàn quốc, với mức cọc 21.000 rupee. Sự kiện ra mắt và công bố giá bán chính thức của VF MPV 7 sẽ diễn ra vào ngày 15/04/2026.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Là một trong những mẫu xe mới nhất của VinFast tại Ấn Độ, VF MPV 7 được phát triển dựa trên “tỷ lệ vàng” đặc trưng của dòng xe MPV, với bốn bánh đẩy sát về các góc thân xe để tối đa hóa không gian cabin.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/8/2.jpg\' alt=\'VF MPV 7 sở hữu không gian cabin rộng rãi theo cả chiều ngang và dọc, tối ưu cấu hình 7 chỗ với sự thoải mái cho cả ba hàng ghế.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>VF MPV 7 sở hữu không gian cabin rộng rãi theo cả chiều ngang và dọc, tối ưu cấu hình 7 chỗ với sự thoải mái cho cả ba hàng ghế.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Với thông số Dài x Rộng x Cao lần lượt là 4.740 x 1.872 x 1.734 mm và trục cơ sở 2.840 mm, VF MPV 7 tạo ra không gian cabin rộng theo chiều ngang lẫn chiều dọc, tối ưu cho cấu hình 7 chỗ ngồi với độ thoải mái đồng đều ở cả ba hàng ghế, đặc biệt phù hợp với gia đình đông thành viên. Bộ mâm hợp kim 19-inch giúp xe có diện mạo vững chãi, phù hợp với nhu cầu sử dụng gia đình nhưng vẫn đậm chất thể thao.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VF MPV 7 được trang bị bộ pin 60,13 kWh, cho phép di chuyển hơn 500 km sau mỗi lần sạc đầy. Công nghệ sạc nhanh tiên tiến giúp sạc pin xe từ 10% lên 70% chỉ trong khoảng 30 phút, tối ưu sự tiện lợi cho người dùng trong điều kiện vận hành thực tế.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/8/3.jpg\' alt=\'VF MPV 7 được trang bị bộ pin 60,13 kWh, cho phép di chuyển hơn 500 km sau mỗi lần sạc đầy.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>VF MPV 7 được trang bị bộ pin 60,13 kWh, cho phép di chuyển hơn 500 km sau mỗi lần sạc đầy.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Tapan Ghosh, Tổng Giám đốc VinFast Ấn Độ, cho biết: “Việc VF MPV 7 ra mắt tiếp tục đánh dấu bước tiến của VinFast trong nỗ lực chinh phục người tiêu dùng Ấn Độ với những giải pháp di chuyển thuần điện hiện đại, thực dụng và dễ tiếp cận. Chúng tôi tin rằng mẫu xe sẽ góp phần tái định nghĩa chuẩn mực trong phân khúc, trở thành lựa chọn lý tưởng cho các gia đình đang tìm kiếm giải pháp di chuyển xanh mà vẫn đảm bảo tính tiện dụng hàng ngày”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trước khi ra mắt VF MPV 7, vào tháng 9/2025, VinFast đã giới thiệu hai mẫu SUV thuần điện cao cấp VF 6 và VF 7 tại Ấn Độ, cả hai đều đã nhận được nhiều giải thưởng uy tín đầu ngành. Cùng với việc mở rộng danh mục sản phẩm, hãng cũng liên tục củng cố các trụ cột khác trong hệ sinh thái xe điện toàn diện như không ngừng mở rộng hệ thống showroom và hậu mãi, hợp tác với nhiều tổ chức tài chính địa phương.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast cũng áp dụng chế độ bảo hành sản phẩm tốt nhất thị trường, đồng thời triển khai các chính sách hỗ trợ lấy người dùng làm trọng tâm như đảm bảo giá trị mua lại, ưu đãi thu xăng đổi điện, và miễn phí sạc tại hệ thống V-Green đến hết ngày 31/03/2029, qua đó tạo điều kiện tối đa cho khách hàng chuyển đổi sang xe điện./.</p>', 'Ô tô điện', 5, '2026-04-01 20:34:56', 'Hiển thị'),
(9, 'VINGROUP GIA HẠN CHƯƠNG TRÌNH HỖ TRỢ KHẨN CẤP KHÁCH HÀNG CHUYỂN ĐỔI XE XĂNG SANG XE ĐIỆN TỚI 30/04/2026', 'vingroup-gia-han-chuong-trinh-ho-tro-khan-cap-khach-hang-chuyen-doi-xe-xang-sang-xe-dien-toi-30-04-2026', '<p class=\'mb-4 text-justify leading-relaxed\'>Hà Nội, ngày 31/03/2026 - Tập đoàn Vingroup quyết định gia hạn chương trình hỗ trợ khẩn cấp cho khách hàng chuyển đổi sang xe điện tới hết ngày 30/04/2026, nhằm tiếp tục san sẻ áp lực nguồn cung và giá xăng dầu với cộng đồng, qua đó thể hiện trách nhiệm xã hội và tinh thần cống hiến của doanh nghiệp.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/9/1.jpg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trước tình hình chiến sự tại Trung Đông vẫn diễn biến phức tạp, gây ảnh hưởng nghiêm trọng đến người tiêu dùng sử dụng các phương tiện giao thông từ nhiên liệu hóa thạch, Vingroup quyết định gia hạn chương trình hỗ trợ “Thu xăng - Đổi điện” tại 4 thị trường trọng điểm là Việt Nam, Ấn Độ, Indonesia, Philippines; đồng thời hỗ trợ 10% chi phí di chuyển bằng các dịch vụ của GSM tại Việt Nam, Indonesia, Lào.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Theo đó, khách hàng chuyển đổi từ xe xăng sang xe điện VinFast tại Việt Nam, Ấn Độ, Indonesia và Philippines sẽ được hỗ trợ thêm 3% giá ô tô và 5% giá xe máy, áp dụng cộng dồn với các ưu đãi khác đang được triển khai.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Với các khách hàng lựa chọn di chuyển bằng xe điện Xanh SM tại Việt Nam và Green SM tại Indonesia và Lào, cước phí cho tất cả chuyến đi sẽ được ưu đãi 10%.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Dương Thị Thu Trang - Phó Tổng giám đốc Kinh doanh VinFast toàn cầu cho biết: “Chương trình hỗ trợ chuyển đổi từ xe xăng sang xe điện được triển khai khẩn cấp trong tháng 3 đã mang đến giải pháp kịp thời và tạo động lực mạnh mẽ cho hàng trăm nghìn khách hàng lựa chọn xe điện VinFast làm người bạn đồng hành trong những hành trình xanh sắp tới. Việc gia hạn triển khai chương trình sẽ không chỉ mang đến cơ hội chuyển đổi xanh thêm cho nhiều khách hàng, mà còn trực tiếp góp phần giảm lượng tiêu thụ xăng dầu, khẳng định trách nhiệm xã hội và tinh thần cống hiến của Vingroup với cộng đồng”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Xăng dầu tăng giá cộng với chính sách ưu đãi siêu tốt vì khách hàng của Vingroup đã góp phần thúc đẩy doanh số hệ sinh thái xanh. Đặc biệt, ngày 28/03/2026, VinFast đã cán mốc 3.520 đơn hàng ô tô điện/ngày, tương ứng với 2,4 chiếc ô tô điện hoàn tất thủ tục mua bán và sẵn sàng xuất xưởng ra khỏi nhà máy trong 1 phút. Đây là kỷ lục chưa từng có, khẳng định năng lực sản xuất - kinh doanh - vận hành vượt trội của VinFast, đồng thời cho thấy xu hướng chuyển đổi sang xe điện đang diễn ra mạnh mẽ tại Việt Nam và thế giới./.</p>', 'Công ty', 3, '2026-03-31 20:41:14', 'Hiển thị'),
(10, 'VINFAST HOÀN TẤT 3.520 ĐƠN HÀNG Ô TÔ ĐIỆN TRONG NGÀY 28/3', 'vinfast-hoan-tat-3-520-don-hang-o-to-dien-trong-ngay-28-3', '<p class=\'mb-4 text-justify leading-relaxed\'>Ngày 28/03, VinFast Việt Nam hoàn tất 3.520 đơn ô tô được xử lý chỉ trong 1 ngày, tương ứng với 3.520 đơn đặt hàng ô tô điện đã được hoàn tất thủ tục và sẵn sàng xuất xưởng. Số đơn hàng kỷ lục được hoàn tất chỉ trong 1 ngày, phản ánh rõ nét làn sóng chuyển dịch mạnh mẽ sang xe điện tại Việt Nam. Không chỉ cho thấy niềm tin ngày càng lớn từ khách hàng, kết quả này còn khẳng định năng lực sản xuất – vận hành – kinh doanh vượt trội của VinFast</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/10/1.jpeg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Chuyển đổi xanh không còn là xu hướng, mà là lựa chọn tất yếu.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Hãy cùng chung tay ủng hộ xe điện để:<br />\r\n• Giảm phụ thuộc vào xăng dầu<br />\r\n• Giảm phát thải, bảo vệ môi trường<br />\r\n• Hướng tới một tương lai xanh, bền vững hơn</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Mỗi lựa chọn xanh hôm nay là một bước tiến cho ngày mai.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>(*) Thông tin trong bài viết được tổng hợp từ nguồn nội bộ tại thời điểm công bố và có thể được cập nhật, điều chỉnh theo số liệu xác nhận chính thức.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>(**) Hình ảnh chỉ mang tính chất minh hoạ.</p>', 'Công ty', 25, '2026-03-28 20:47:20', 'Hiển thị'),
(11, 'VINFAST THAM GIA TRIỂN LÃM QUỐC TẾ VIETBUILD HÀ NỘI 2026 GIAO THÔNG XANH - DẪN LỐI TƯƠNG LAI', 'vinfast-tham-gia-trien-lam-quoc-te-vietbuild-ha-noi-2026-giao-thong-xanh-dan-loi-tuong-lai', '<p class=\'mb-4 text-justify leading-relaxed\'>Hướng tới xu hướng giao thông xanh và phát triển bền vững, VinFast tham gia Triển lãm Quốc tế VietBuild Hà Nội 2026, mang đến không gian trưng bày và trải nghiệm các dòng ô tô điện và xe máy điện hiện đại, đồng thời tạo cơ hội để khách hàng tham quan trực tiếp chiêm ngưỡng, lái thử và cảm nhận những giá trị của xu hướng di chuyển xanh.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/11/1.jpg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thông tin triển lãm “VinFast - Giao thông xanh”:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thời gian: Từ 26 – 30/3/2026<br />\r\nĐịa điểm: Trung tâm triển lãm Việt Nam VEC, Đông Anh, Hà Nội<br />\r\nKhông gian trưng bày được thiết kế theo hướng mở, hiện đại, kết hợp giữa khu vực trưng bày sản phẩm và trải nghiệm thực tế, mang đến hành trình khám phá liền mạch cho khách tham quan.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Khám phá và trải nghiệm trực tiếp các dòng xe điện VinFast mới nhất</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Triển lãm Quốc tế VIETBUILD là sự kiện thường niên quy mô lớn, quy tụ hàng nghìn doanh nghiệp trong các lĩnh vực xây dựng, vật liệu, giao thông vận tải và công nghệ, đồng thời là nơi giới thiệu các giải pháp hiện đại hướng tới phát triển bền vững.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đồng hành cùng sự kiện, VinFast góp mặt với không gian trưng bày nổi bật, mang đến hành trình khám phá toàn diện hệ sinh thái xe điện – nơi hội tụ đa dạng các dòng ô tô và xe máy điện với thiết kế hiện đại và công nghệ tiên tiến. Đặc biệt, gian hàng lần này gây ấn tượng với những điểm nhấn đáng chú ý:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>EC Van – Mẫu xe tải điện cỡ nhỏ với phiên bản cửa trượt hoàn toàn mới, mang đến giải pháp vận hành tối ưu cho đô thị. Với kích thước gọn gàng, cửa trượt thông minh, dễ dàng thao tác ngay cả trong không gian hẹp, lên xuống hàng nhanh chóng và an toàn, EC Van chính là giải pháp di chuyển xanh thiết thực cho tiểu thương, các hộ kinh doanh.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Xe máy điện Viper hoàn toàn mới – Ấn tượng với thiết kế hiện đại, cá tính, phù hợp với phong cách di chuyển năng động. Xe được trang bị nhiều tính năng thông minh như khóa Smart Key hỗ trợ định vị và tìm xe từ xa, điều khiển bật/tắt xe từ xa để hỗ trợ chống trộm, cùng các trang bị như đèn pha LED Projector, giảm xóc đôi có bình dầu phụ giúp xe vận hành êm ái, ổn định hơn… Xe được trang bị động cơ BLDC Inhub công suất lớn nhất 3.000W, có tốc độ tối đa lên tới 70 km/h.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Lái thử VF MPV 7 – Mẫu xe điện 7 chỗ được thiết kế dành riêng cho gia đình, nổi bật với không gian rộng rãi, 3 hàng ghế thoải mái và khả năng tối ưu khoang nội thất. Khách tham quan sẽ có cơ hội trực tiếp cầm lái để cảm nhận vận hành êm ái, tăng tốc mượt mà từ động cơ điện, cùng hệ thống công nghệ thông minh hỗ trợ người lái.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thông qua trải nghiệm thực tế, người tham gia sẽ có cái nhìn rõ nét hơn về xu hướng di chuyển xanh, đồng thời dễ dàng đánh giá sự phù hợp của xe điện với nhu cầu sử dụng hàng ngày, từ đó đưa ra lựa chọn tối ưu cho bản thân và gia đình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đặt cọc xe ngay tại sự kiện</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong bối cảnh hạ tầng sạc ngày càng hoàn thiện, việc chuyển sang xe điện không chỉ giúp tối ưu chi phí vận hành mà còn góp phần xây dựng hệ sinh thái giao thông xanh, hướng tới một tương lai di chuyển bền vững hơn cho cộng đồng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ dừng lại ở trải nghiệm, sự kiện còn là dịp để khách hàng cân nhắc chuyển đổi từ xe xăng truyền thống sang xe điện – lựa chọn đang dần trở thành xu hướng tất yếu của giao thông xanh và phát triển bền vững. Đây cũng là cơ hội để đặt cọc trực tiếp, nắm bắt những chính sách giá trị cùng nhiều phần quà hấp dẫn chỉ có trong thời gian diễn ra chương trình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Cụ thể, khách hàng sẽ được áp dụng đồng thời nhiều chính sách ưu đãi đặc biệt* như:<br />\r\n- Nhận voucher lên tới 20 triệu đồng từ chương trình “Giờ trái đất” (Áp dụng từ ngày 26 - 30/03/2026)*</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>- Ưu đãi lên tới 240 triệu đồng từ chương trình “Thu xăng - Đổi điện” (Chỉ áp dụng đến hết ngày 31/03/2026)*</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>- Miễn phí 100% lệ phí trước bạ** và sạc pin lên tới 3 năm.*</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>- Cùng nhiều phần quà giá trị dành cho Khách hàng tham gia sự kiện</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Việc đặt cọc ngay tại triển lãm không chỉ giúp giữ trọn mức ưu đãi tốt nhất, mà còn là bước khởi đầu nhanh chóng để sở hữu ô tô điện, tối ưu chi phí vận hành và hòa mình vào nhịp sống xanh, bền vững.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ là trải nghiệm – Mà là “điểm hẹn xanh” đầy cảm hứng</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không dừng lại ở việc trưng bày và lái thử, sự kiện còn mang đến một không gian trải nghiệm sống động và giàu cảm xúc. Chuỗi hoạt động tương tác cùng các tiết mục trình diễn âm nhạc diễn ra xuyên suốt, góp phần khuấy động bầu không khí và tạo nên sức hút nổi bật cho toàn khu vực.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đây không chỉ là nơi để khám phá công nghệ xe điện hiện đại, mà còn là dịp để khách hàng tham quan tận hưởng những khoảnh khắc thư giãn, hòa mình vào không gian thân thiện, trẻ trung và tràn đầy năng lượng. Mỗi trải nghiệm tại sự kiện đều góp phần lan tỏa tinh thần sống xanh một cách gần gũi, nhẹ nhàng nhưng đầy cảm hứng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Cơ hội sở hữu xe điện VinFast với tổng ưu đãi vượt trội cùng những đặc quyền dành riêng cho khách hàng chỉ diễn ra trong sự kiện. Đừng bỏ lỡ!</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Để lại thông tin để được tư vấn lộ trình trải nghiệm tốt nhất và giữ ưu đãi ngay từ bây giờ!</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>(*) Chương trình ưu đãi áp dụng có điều kiện và điều khoản.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>(**) Theo nghị định 51/2025/NĐ-CP.</p>', 'Công ty', 1, '2026-03-25 20:49:56', 'Hiển thị'),
(12, 'VINFAST KHAI TRƯƠNG ĐẠI LÝ 3S CAO CẤP MỚI Ở BENGALURU, CÁN MỐC 50 SHOWROOM TẠI ẤN ĐỘ', 'vinfast-khai-truong-dai-ly-3s-cao-cap-moi-o-bengaluru-can-moc-50-showroom-tai-an-do', '<p class=\'mb-4 text-justify leading-relaxed\'>Bengaluru, ngày 25/03/2025 – VinFast công bố khai trương đại lý 3S mới nhất tại thành phố Bengaluru, đánh dấu showroom thứ 50 của hãng tại Ấn Độ. Cột mốc này thể hiện bước tiến mạnh mẽ của VinFast trong hành trình nhanh chóng mở rộng hiện diện tại Ấn Độ, đồng thời tái khẳng định cam kết đem giải pháp giao thông xanh cao cấp đến gần hơn với người tiêu dùng Ấn Độ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tọa lạc tại vị trí đắc địa trên đường Hosur, khu vực Electronic City, cửa hàng mới của VinFast do nhóm đại lý PPS Motors LLP phát triển dưới sự dẫn dắt của chủ đại lý, ông Raaj Sekar. Được xây dựng theo tiêu chuẩn toàn cầu của VinFast, cơ sở có quy mô hơn 1.000 m2, tích hợp đồng bộ không gian trưng bày và khu vực dịch vụ.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/12/1.jpg\' alt=\'Ông Tapan Ghosh (phải) và ông Raaj Sekar, chủ đại lý PPS Motors LLP, tại lễ khai trương.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>Ông Tapan Ghosh (phải) và ông Raaj Sekar, chủ đại lý PPS Motors LLP, tại lễ khai trương.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Nổi bật trong đó là khu trưng bày rộng 230 m2 với thiết kế hiện đại và sang trọng, mang đến trải nghiệm thương hiệu sống động và trực quan. Tại đây, đội ngũ tư vấn được đào tạo bài bản của VinFast sẽ đảm bảo mọi điểm chạm của khách hàng diễn ra liền mạch và nhất quán, từ tư vấn bán hàng đến dịch vụ hậu mãi.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Theo lộ trình phát triển mạnh mẽ tại Ấn Độ, VinFast đặt mục tiêu thiết lập 75 đại lý trong năm nay tại hơn 60 thành phố trên toàn quốc. Chiến lược mở rộng của hãng không chỉ tập trung vào các đô thị lớn và trung tâm kinh tế trọng điểm, mà còn mở rộng tới các thị trường giàu tiềm năng, qua đó khẳng định cam kết phổ cập các giải pháp di chuyển điện hóa cao cấp trên khắp Ấn Độ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Tapan Ghosh, Tổng Giám đốc VinFast Ấn Độ, cho biết: “VinFast đã đạt cột mốc quan trọng với 50 showroom trên toàn quốc, phản ánh tốc độ mở rộng nhanh chóng và quyết tâm tăng cường hiện diện thương hiệu tại Ấn Độ. Sự kiện khai trương cơ sở 3S trọng điểm tại Bengaluru tiếp tục nối dài đà tăng trưởng của VinFast, mang đến trọn bộ dịch vụ từ bán hàng, dịch vụ sửa chữa tới cung cấp phụ tùng chính hãng, qua đó không chỉ đáp ứng đầy đủ nhu cầu của khách hàng mà còn khẳng định vai trò của VinFast trong việc thúc đẩy hệ sinh thái xe điện tại Ấn Độ”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong quá trình tăng tốc hoạt động tại Ấn Độ, VinFast không ngừng củng cố hệ sinh thái xe điện toàn diện, bao gồm sản xuất, phân phối, hạ tầng sạc, dịch vụ hậu mãi và chuỗi giá trị pin tuần hoàn, đồng thời hợp tác với các ngân hàng và đối tác nhằm mang đến trải nghiệm sở hữu thuận tiện, góp phần thúc đẩy xu hướng di chuyển xanh. Hai mẫu SUV điện cao cấp VinFast VF 6 và VF 7 đều đạt chứng nhận an toàn 5 sao Bharat NCAP, đáp ứng các tiêu chuẩn khắt khe tại Ấn Độ. Bộ đôi sản phẩm tiếp tục củng cố niềm tin khách hàng thông qua gói cứu hộ lưu động và ba năm bảo dưỡng miễn phí, đi kèm chính sách bảo hành đầu ngành lên tới 10 năm/200.000 km tùy mẫu xe.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Song song đó, VinFast thúc đẩy phổ cập xe điện thông qua nhiều sáng kiến lấy khách hàng làm trọng tâm, giúp giảm rào cản và đem lại sự yên tâm trong suốt quá trình sở hữu. Nổi bật là chương trình “Đảm bảo giá trị mua lại” góp phần giải tỏa nỗi lo khấu hao xe điện thông qua việc cam kết mức giá mua lại được xác định trước.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bên cạnh đó, chính sách sạc miễn phí tại mạng lưới trạm sạc V-Green đến hết ngày 31/03/2029 mang lại lợi thế “0 đồng nhiên liệu” cho khách hàng trong thời gian lên tới ba năm. Tập đoàn Vingroup, công ty mẹ của VinFast, gần đây cũng triển khai chương trình “Thu xăng, đổi điện”, ưu đãi thêm 3% trên giá bán đến hết ngày 31/03/2026 nhằm khuyến khích chuyển đổi từ ô tô động cơ đốt trong sang phương tiện điện.</p>', 'Công ty', 2, '2026-03-24 20:55:03', 'Hiển thị');
INSERT INTO `news` (`id`, `title`, `slug`, `body`, `catalog`, `views`, `created_at`, `news_state`) VALUES
(13, 'VINFAST ẤN ĐỘ HỢP TÁC CÙNG NGÂN HÀNG CSB, CUNG CẤP GIẢI PHÁP TÀI CHÍNH XE ĐIỆN TOÀN DIỆN', 'vinfast-an-do-hop-tac-cung-ngan-hang-csb-cung-cap-giai-phap-tai-chinh-xe-dien-toan-dien', '<p class=\'mb-4 text-justify leading-relaxed\'>Gurugram, ngày 23/03/2026 – VinFast công bố ký kết Biên bản ghi nhớ (MOU) với CSB Bank, một trong những ngân hàng tư nhân lâu đời nhất tại Ấn Độ, nhằm cung cấp giải pháp tài trợ mua xe và tài trợ hàng tồn kho cho hệ thống đại lý độc quyền của hãng. Thỏa thuận mang đến bộ giải pháp tín dụng đồng bộ và tiện lợi cho khách hàng tiềm năng của hai mẫu SUV điện cao cấp VF 6 và VF 7, qua đó hỗ trợ chiến lược tăng trưởng của VinFast tại thị trường ô tô lớn thứ ba thế giới.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Theo thỏa thuận, khách hàng có nhu cầu sở hữu xe điện VinFast sẽ được tiếp cận đa dạng giải pháp tài chính bán lẻ từ CSB Bank, bao gồm hỗ trợ vay lên tới 100% chi phí lăn bánh, lãi suất cạnh tranh, phương án trả góp linh hoạt, cùng quy trình phê duyệt và giải ngân nhanh chóng, thuận tiện. Đội ngũ chuyên viên của CSB Bank sẽ trực tiếp hỗ trợ tại các đại lý VinFast để đảm bảo khách hàng có trải nghiệm vay mua xe tiện lợi và liền mạch, qua đó giúp đưa xe điện đến gần hơn với người tiêu dùng.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/13/1.jpg\' alt=\'Ông Tapan Ghosh (trái), Tổng Giám đốc VinFast Ấn Độ, và ông Narendra Dixit, Giám đốc Khối Ngân hàng bán lẻ của CSB Bank tại lễ ký kết.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>Ông Tapan Ghosh (trái), Tổng Giám đốc VinFast Ấn Độ, và ông Narendra Dixit, Giám đốc Khối Ngân hàng bán lẻ của CSB Bank tại lễ ký kết.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Hợp tác này cũng cho phép VinFast tận dụng mạng lưới toàn Ấn Độ cùng hệ thống chi nhánh ngày càng mở rộng của CSB Bank để triển khai các giải pháp tài chính xe điện tại nhiều khu vực khác nhau. Với độ phủ không ngừng được tăng cường trên toàn quốc và định hướng cung cấp giải pháp tài chính linh hoạt, phù hợp từng cá nhân, CSB Bank muốn chung tay thúc đẩy quá trình phổ cập phương tiện giao thông xanh thông qua việc tạo điều kiện tối đa cho khách hàng trên khắp cả nước được tiếp cận gói vay tài chính dễ dàng hơn.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Biên bản ghi nhớ được ký kết bởi ông Tapan Ghosh, Tổng Giám đốc VinFast Ấn Độ, cùng ông Narendra Dixit, Giám đốc Khối Ngân hàng bán lẻ, CSB Bank.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Tapan Ghosh, Tổng Giám đốc VinFast Ấn Độ, chia sẻ: “Việc hợp tác với CSB Bank là bước phát triển tự nhiên trong chiến lược của chúng tôi tại Ấn Độ, nơi khả năng tiếp cận và chi phí hợp lý sẽ đóng vai trò then chốt trong việc tăng cường phổ cập xe điện. Tại những thị trường như Ấn Độ, quá trình chuyển đổi sang xe điện không chỉ phụ thuộc vào sản phẩm, mà còn phụ thuộc vào mức độ đơn giản và thực tiễn trong trải nghiệm sở hữu của khách hàng. Thông qua hợp tác này, chúng tôi hướng tới việc cung cấp các giải pháp tài chính linh hoạt, giúp giảm rào cản tiếp cận, đồng thời tiếp tục xây dựng hệ sinh thái đáng tin cậy và toàn diện. Đây là một trong nhiều bước đi nhằm đảm bảo mang đến trải nghiệm nhất quán, đáng tin cậy và đặt khách hàng làm trọng tâm tại thị trường này”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Narendra Dixit, Giám đốc Khối Ngân hàng bán lẻ, CSB Bank, cho biết: “Phương tiện di chuyển điện không còn là khái niệm của tương lai, mà đang nhanh chóng trở thành lựa chọn phổ biến đối với cả khách hàng cá nhân lẫn doanh nghiệp. Khi tốc độ phổ cập ngày càng tăng, nhu cầu đối với các giải pháp tài chính dễ tiếp cận cũng sẽ ngày càng lớn, nhằm giúp cả khách hàng và đại lý cùng tham gia vào quá trình chuyển đổi này. Quan hệ hợp tác với VinFast là bước tiến theo hướng đó. Bằng việc kết hợp tầm nhìn sản phẩm mạnh mẽ của VinFast trong lĩnh vực xe điện với năng lực tài chính của CSB Bank, chúng tôi hướng tới xây dựng hệ sinh thái vững chắc, hỗ trợ cả khách hàng cá nhân lẫn đại lý thông qua các giải pháp vay mua xe và tài trợ hàng tồn kho phù hợp”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast đang tiếp tục củng cố hệ sinh thái xe điện toàn diện tại thị trường Ấn Độ với nhà máy lắp ráp tại Tamil Nadu; mạng lưới showroom đẳng cấp quốc tế trên toàn quốc, dự kiến tăng gấp đôi quy mô trong năm nay; hệ thống dịch vụ hậu mãi ngày càng được mở rộng; cùng với việc tăng cường hợp tác với nhiều tổ chức tài chính, góp phần nâng cao khả năng tiếp cận xe điện cho khách hàng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Hãng đã giới thiệu hai mẫu SUV điện cao cấp VF 6 và VF 7, đều đạt chuẩn an toàn 5 sao Bharat NCAP, và dự kiến ra mắt thêm nhiều mẫu xe mới trong năm 2026 để đáp ứng nhu cầu chuyển đổi xanh ngày càng đa dạng của người tiêu dùng.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Song song đó, VinFast đưa ra nhiều sáng kiến giúp giảm bớt rào cản tài chính và tâm lý cho khách hàng, như cam kết đảm bảo giá trị bán lại và phương án mua lại minh bạch. Gần đây, hãng gia hạn chương trình miễn phí sạc tại hệ thống trạm sạc V-Green đến hết ngày 31/3/2029. Chương trình “Thu xăng - Đổi điện” cũng được triển khai tại Ấn Độ, Việt Nam, Indonesia và Philippines trong giai đoạn từ 11/3 đến 31/3/2026, mang đến ưu đãi bổ sung 3% cho ô tô điện và 5% cho xe máy điện VinFast dành cho khách hàng chuyển đổi từ phương tiện chạy xăng cũ./.</p>', 'Công ty', 5, '2026-03-22 21:01:03', 'Hiển thị'),
(14, 'VINFAST O2O – VÌ TƯƠNG LAI XANH - SỰ KIỆN TRẢI NGHIỆM LÁI Ô TÔ ĐIỆN, LAN TỎA LỐI SỐNG BỀN VỮNG', 'vinfast-o2o-vi-tuong-lai-xanh-su-kien-trai-nghiem-lai-o-to-dien-lan-toa-loi-song-ben-vung', '<p class=\'mb-4 text-justify leading-relaxed\'>Hưởng ứng chiến dịch Giờ Trái Đất 2026 với thông điệp “Sáng tạo xanh – Tương lai xanh”, VinFast tổ chức sự kiện VinFast O2O – Vì tương lai xanh, mang đến cơ hội để khách hàng trực tiếp trải nghiệm các dòng ô tô điện hiện đại, đồng thời lan tỏa xu hướng di chuyển xanh tại Việt Nam.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/14/1.jpg\' alt=\'\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thông tin sự kiện VinFast O2O – Vì tương lai Xanh:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thời gian: 21 – 22/03/2026<br />\r\nĐịa điểm:<br />\r\nHà Nội: Eden Garden Space - 56BT8 Khu đô thị Văn Quán, Hà Đông<br />\r\nTP. HCM: King Corner - 14 Tạ Hiện, Thạnh Mỹ Lợi, Thủ Đức<br />\r\nKhông gian tổ chức được thiết kế theo mô hình trải nghiệm mở, kết hợp giữa lái thử xe và các hoạt động tương tác, giúp khách hàng dễ dàng tham gia và tận hưởng trọn vẹn chương trình.<br />\r\nTrực tiếp cầm lái dòng sản phẩm ô tô điện VinFast mới nhất</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tại sự kiện, khách hàng sẽ có cơ hội trực tiếp trải nghiệm và lái thử đa dạng các dòng ô tô điện VinFast, từ dải sản phẩm xe cá nhân quen thuộc VF 3, VF 6, VF 7 đến VF 8, VF 9 đến các mẫu xe thương mại dịch vụ (VinFast Green) như Minio Green, Limo Green và VF MPV 7.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ dừng lại ở việc “ngồi thử”, người tham gia sẽ được cảm nhận trọn vẹn từng chuyển động – từ khả năng tăng tốc mượt mà, vận hành êm ái đến hệ thống công nghệ thông minh hỗ trợ người lái. Đây cũng là cơ hội để khách hàng hiểu rõ hơn về những lợi ích thực tế mà xe điện mang lại trong hành trình di chuyển hàng ngày: tiết kiệm chi phí, thân thiện môi trường và phù hợp với nhịp sống hiện đại.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Thông qua trải nghiệm trực tiếp, mỗi khách hàng có thể tự mình đánh giá và đưa ra lựa chọn phù hợp cho nhu cầu di chuyển của bản thân và gia đình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đặt cọc xe ngay tại sự kiện</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ dừng lại ở trải nghiệm, sự kiện là thời điểm để khách hàng đặt cọc trực tiếp và nhận về mức ưu đãi tốt nhất, cùng nhiều phần quà hấp dẫn chỉ có tại chương trình.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Đây được xem là cơ hội hiếm có để chốt ưu đãi ở mức cao nhất, khi các chính sách được tập trung và gia tăng giá trị ngay tại sự kiện. Cụ thể, khách hàng được hưởng những chính sách ưu đãi hấp dẫn* chưa từng có như:</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ưu đãi giá lên tới 10% từ chương trình “Mãnh liệt vì tương lai Xanh”<br />\r\nHỗ trợ thêm 3% từ chương trình “Thu xăng – Đổi điện”<br />\r\nĐặc quyền khi mua xe qua website www.vinfastauto.com (chọn kênh VinFast O2O): Ưu đãi thêm 2% sau khi áp dụng chính sách bán lẻ hiện hành cùng đặc quyền dành riêng cho khách hàng tham gia sự kiện.<br />\r\nViệc đặt cọc ngay trong khuôn khổ sự kiện không chỉ giúp giữ trọn ưu đãi, mà còn là bước đi nhanh chóng để sớm sở hữu ô tô điện và tận hưởng lợi ích tiết kiệm, bền vững.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Không chỉ là lái thử – Mà là một “điểm hẹn xanh” cho cả gia đình</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bên cạnh hoạt động lái thử, sự kiện được ví như một không gian trải nghiệm thu nhỏ, nơi mỗi thành viên đều có thể tìm thấy niềm vui riêng. Từ check-in mini game “Giờ Trái Đất – Check-in Xanh”, thưởng thức trà &amp; cafe miễn phí, đến các khu vực vui chơi giải trí được bố trí thân thiện và gần gũi. Không đơn thuần là khám phá sản phẩm, đây còn là dịp để khách hàng tạm rời nhịp sống bận rộn, tận hưởng những khoảnh khắc thư giãn bên gia đình, đồng thời cảm nhận rõ hơn giá trị của lối sống xanh – bắt đầu từ những trải nghiệm nhẹ nhàng, gần gũi nhất.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Cơ hội sở hữu xe điện VinFast với tổng ưu đãi vượt trội cùng những đặc quyền dành riêng cho khách hàng VinFast O2O chỉ diễn ra trong 2 ngày duy nhất. Đừng bỏ lỡ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Để lại thông tin để được tư vấn lộ trình trải nghiệm tốt nhất và giữ ưu đãi ngay từ bây giờ!</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>(*) Chương trình ưu đãi áp dụng có điều kiện và điều khoản.</p>', 'Ô tô điện', 3, '2026-03-18 21:03:22', 'Hiển thị'),
(15, 'VINFAST TIẾP TỤC GIÀNH GIẢI THƯỞNG TẠI ẤN ĐỘ TỪ JAGRAN HI-TECH VÀ CAR&BIKE AWARDS 2026', 'vinfast-tiep-tuc-gianh-giai-thuong-tai-an-do-tu-jagran-hi-tech-va-car-bike-awards-2026', '<p class=\'mb-4 text-justify leading-relaxed\'>Gurgaon, ngày 17/03/2026 – VinFast tiếp tục được vinh danh tại hai giải thưởng ô tô uy tín tại Ấn Độ, gồm Jagran Hi-Tech Awards 2026 và Car&amp;Bike Awards 2026. Những giải thưởng này cho thấy dấu ấn ngày càng rõ nét của VinFast trong hệ sinh thái xe điện tại Ấn Độ, đồng thời khẳng định cam kết của hãng trong việc mang đến các giải pháp di chuyển bằng điện hiện đại, chất lượng cao, và dễ tiếp cận.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tại Jagran Hi-Tech Awards 2026, VinFast nhận giải “Thương hiệu EV tiên phong của năm”, ghi nhận dấu ấn mạnh mẽ của hãng khi gia nhập thị trường Ấn Độ với tầm nhìn dài hạn xây dựng hệ sinh thái xe điện toàn diện. Ban giám khảo cũng đánh giá cao những khoản đầu tư của VinFast vào sản xuất, phát triển sản phẩm và hợp tác với đối tác nội địa nhằm đẩy nhanh quá trình phổ cập phương tiện di chuyển điện hóa tại Ấn Độ.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/15/1.jpg\' alt=\'Ông Rituraj Singh (thứ hai từ trái sang), Phó Tổng Giám đốc Kinh doanh &amp; Phát triển mạng lưới của VinFast Ấn Độ, nhận giải thưởng từ đại diện ban tổ chức Jagran Hi-tech.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>Ông Rituraj Singh (thứ hai từ trái sang), Phó Tổng Giám đốc Kinh doanh &amp; Phát triển mạng lưới của VinFast Ấn Độ, nhận giải thưởng từ đại diện ban tổ chức Jagran Hi-tech.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trong khi đó, tại Car&amp;Bike Awards 2026, ban giám khảo vinh danh VinFast với danh hiệu “Thương hiệu bứt phá của năm” vì tốc độ mở rộng toàn cầu nhanh chóng cùng cam kết mạnh mẽ đối với tầm nhìn về một tương lai giao thông xanh. Ban giám khảo ấn tượng với dấu ấn ngày càng rõ nét của VinFast trên trường quốc tế, cùng chiến lược phát triển thương hiệu toàn cầu lấy giải pháp di chuyển xanh làm trung tâm.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Các giải thưởng mới nhất nâng tổng số danh hiệu VinFast đạt được tại thị trường Ấn Độ lên hơn 20 chỉ sau hơn một năm hiện diện, cho thấy sự ghi nhận tích cực của giới chuyên môn đối với cả tầm vóc thương hiệu lẫn chất lượng sản phẩm của hãng. Trước đó, VinFast được tạp chí Nanayam Vikatan vinh danh là “Nhà đầu tư của năm” và nhận giải “Tân binh của năm” tại FASTER Awards 2026, trong khi đội ngũ truyền thông của hãng cũng được trao danh hiệu “Đội ngũ truyền thông của năm 2026”. Mẫu VF 7 được vinh danh là “SUV điện của năm” tại BBC TopGear India Awards 2026, còn VF 6 giành giải “Mẫu xe đáng tiền nhất năm” tại Autocar India Awards 2026.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Tapan Ghosh, Tổng Giám đốc VinFast Ấn Độ, chia sẻ: “Chúng tôi rất vinh dự khi được ghi nhận bởi Jagran Hi-Tech Awards và Car&amp;Bike Awards. Hai danh hiệu Thương hiệu EV tiên phong của năm và Thương hiệu bứt phá của năm phản ánh niềm tin ngày càng lớn của giới chuyên gia trong ngành cũng như người tiêu dùng đối với tầm nhìn giao thông xanh của VinFast tại Ấn Độ. Trong thời gian tới, song song với việc mở rộng hiện diện tại thị trường, chúng tôi sẽ luôn kiên định với mục tiêu mang đến các mẫu xe điện hiện đại và chất lượng cao, góp phần xây dựng hệ sinh thái vững chắc hỗ trợ quá trình chuyển dịch sang giao thông bền vững của Ấn Độ”.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/15/2.jpg\' alt=\'Ông Arunodoy Das (giữa), Phó Tổng Giám đốc Kinh doanh của VinFast Ấn Độ, nhận giải thưởng từ ban tổ chức Car&amp;Bike Awards 2026.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>Ông Arunodoy Das (giữa), Phó Tổng Giám đốc Kinh doanh của VinFast Ấn Độ, nhận giải thưởng từ ban tổ chức Car&amp;Bike Awards 2026.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Girish Karkera, Tổng Biên tập Car&amp;Bike, nhận định: “Đối với một thương hiệu ô tô còn mới mẻ, việc xây dựng chỗ đứng vững chắc tại một quốc gia mới, nơi đã có nhiều đối thủ lâu năm, luôn là thử thách lớn. VinFast không chỉ giới thiệu những sản phẩm phù hợp với thị trường Ấn Độ mà còn thực hiện điều đó trong thời gian kỷ lục theo tiêu chuẩn của ngành. Hãng xe cũng gây ấn tượng nhờ có thể tiếp cận đúng nhóm khách hàng mục tiêu bằng cách thức đơn giản nhưng đặc biệt hiệu quả”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ông Arjit Garg, Biên tập viên Jagran Hi-tech, cho biết: “Jagran EVolution Bharat EV Conclave &amp; Awards tôn vinh và ghi nhận hệ sinh thái xe điện đang phát triển nhanh chóng tại Ấn Độ. Giải thưởng năm nay đặc biệt sôi động với nhiều đổi mới và nhiều sản phẩm mới, cũng như sự góp mặt của đông đảo doanh nghiệp toàn cầu. Một ví dụ tiêu biểu là VinFast, đơn vị không chỉ gia nhập thị trường Ấn Độ mà còn cam kết xây dựng nhà máy tại địa phương, thể hiện sự đồng hành rõ nét với tầm nhìn Make-in-India. Vì lý do đó, chúng tôi vinh danh VinFast ở hạng mục Thương hiệu EV tiên phong của năm. Đây là sự ghi nhận xứng đáng dành cho thương hiệu đã mang các mẫu xe điện đẳng cấp thế giới đến Ấn Độ, góp phần tạo việc làm và củng cố hệ sinh thái sản xuất xe điện quốc gia”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast tiếp tục củng cố hệ sinh thái xe điện toàn diện tại thị trường Ấn Độ, trải dải từ sản xuất, bán lẻ, hạ tầng sạc đến dịch vụ hậu mãi. Sau khi ra mắt hai mẫu SUV điện VF 6 và VF 7, đều đạt chứng nhận an toàn 5 sao Bharat NCAP, hãng đã triển khai nhiều chương trình nhằm nâng cao trải nghiệm sở hữu cho khách hàng, bao gồm cam kết đảm bảo giá trị bán lại, phương án mua lại minh bạch, cùng chương trình đổi xe tiên phong cho phép khách hàng đổi phương tiện động cơ đốt trong đang sử dụng để sở hữu mẫu SUV điện VinFast mới.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Nhằm thúc đẩy hơn nữa khả năng tiếp cận phương tiện di chuyển xanh, VinFast công bố thêm nhiều sáng kiến giúp tháo gỡ rào cản tài chính, gia tăng thuận tiện cho người dùng. Mới đây, hãng gia hạn chương trình sạc xe điện miễn phí tại các trạm sạc do V-Green phát triển và vận hành đến hết ngày 31/3/2029. VinFast cũng áp dụng chương trình “Thu xăng - Đổi điện” tại Việt Nam, Ấn Độ, Indonesia và Philippines từ 11/3 đến 31/3/2026, ưu đãi thêm 3% giá ô tô điện và 5% giá xe máy điện VinFast cho khách hàng chuyển đổi từ phương tiện chạy xăng cũ./.</p>', 'Công ty', 0, '2026-03-17 06:12:16', 'Hiển thị'),
(16, 'VINGROUP KHẨN CẤP HỖ TRỢ KHÁCH HÀNG GIẢM ÁP LỰC GIÁ XĂNG DẦU', 'vingroup-khan-cap-ho-tro-khach-hang-giam-ap-luc-gia-xang-dau', '<p class=\'mb-4 text-justify leading-relaxed\'>Trước diễn biến khó lường của giá xăng dầu thế giới, Vingroup công bố khẩn cấp triển khai chương trình “Thu xăng - Đổi điện” đặc biệt tại Việt Nam, Ấn Độ, Indonesia và Philippines. Chương trình lập tức ưu đãi thêm 3% giá ô tô, 5% giá xe máy điện VinFast cho các khách hàng chuyển đổi từ xe xăng cũ, đồng thời giảm 10% giá cước Xanh SM từ ngày 11/03 đến hết ngày 31/03/2026 tùy từng thị trường.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Cụ thể, ngoài các chính sách ưu đãi hấp dẫn đang được triển khai, khách hàng chuyển đổi từ xe xăng cũ sang xe điện VinFast mới trong thời gian diễn ra chương trình sẽ được ưu đãi thêm 3% giá đối với ô tô và 5% giá đối với xe máy. Chương trình áp dụng tại cả 4 thị trường gồm Việt Nam, Ấn Độ, Indonesia và Philippines.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Tiếp nối tinh thần tiên phong của VinFast, Công ty Cổ phần Di chuyển Xanh và Thông minh GSM cũng công bố giảm ngay 10% giá cước các dịch vụ di chuyển bằng xe điện trên nền tảng Xanh SM tại Việt Nam và Green SM tại Indonesia từ ngày 11/03 đến hết ngày 31/03/2026, mang đến cho khách hàng thêm một lựa chọn di chuyển thân thiện và tiết kiệm chi phí.<br />\r\nChương trình có thể được gia hạn tùy vào tình hình chiến sự tại Trung Đông và diễn biến giá xăng dầu trong thời gian tới.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Dương Thị Thu Trang - Phó Tổng giám đốc Kinh doanh VinFast toàn cầu cho biết: “Chương trình Thu xăng - Đổi điện đặc biệt triển khai trong tháng 3 tại 4 thị trường trọng điểm là hành động ứng phó khẩn cấp của VinFast trước biến động địa chính trị đang gây ra ảnh hưởng tiêu cực đến tình hình kinh tế - xã hội của nhiều quốc gia trên thế giới. Với vai trò là một trong những nhà sản xuất tiên phong dẫn dắt cuộc cách mạng xe điện trên toàn cầu, VinFast cùng với các công ty trong hệ sinh thái xanh Vingroup mong muốn chung tay góp phần giảm thiểu tác động của giá xăng dầu đến cuộc sống người dân, giảm ô nhiễm môi trường thông qua những giải pháp di chuyển thông minh, bền vững và kinh tế hơn”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Chương trình “Thu xăng - Đổi điện” đặc biệt sẽ được VinFast triển khai song song và áp dụng cộng dồn với các chính sách ưu đãi hấp dẫn khác tại từng thị trường. Với ưu đãi chồng ưu đãi, Vingroup và các thành viên trong hệ sinh thái mong muốn tạo điều kiện tốt nhất cho khách hàng nhanh chóng chuyển đổi sang xe điện để giảm phụ thuộc vào xăng dầu, góp phần ổn định cuộc sống và kiến tạo môi trường sống văn minh, trong lành hơn.</p>', 'Công ty', 4, '2026-03-10 06:15:29', 'Hiển thị'),
(17, 'VINFAST HỢP TÁC VỚI 6 ĐẠI LÝ XE MÁY ĐIỆN TẠI INDONESIA, TĂNG TỐC MỞ RỘNG THỊ TRƯỜNG TRÊN TOÀN QUỐC', 'vinfast-hop-tac-voi-6-dai-ly-xe-may-dien-tai-indonesia-tang-toc-mo-rong-thi-truong-tren-toan-quoc', '<p class=\'mb-4 text-justify leading-relaxed\'>Jakarta, ngày 06/03/2026 – VinFast công bố ký kết Biên bản ghi nhớ (MOU) chiến lược với 6 đại lý xe máy điện tại Indonesia, đánh dấu bước tiến mới trong quá trình mở rộng mạng lưới phân phối tại một trong những thị trường xe máy lớn nhất khu vực và thế giới. Thỏa thuận tiếp tục khẳng định cam kết lâu dài của hãng trong việc thúc đẩy điện hóa giao thông và xây dựng hệ sinh thái di chuyển xanh toàn diện tại Indonesia.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Để chuẩn bị cho hoạt động ra mắt sản phẩm xe máy điện vào Quý II/2026, VinFast tiếp tục ký kết MOU với 6 đại lý gồm PT. IB Motor, PT. Sentrik, PT. Axara Marani, PT. Sukses Sejati Indonesia, PT. Tangguh Inti Motor, và PT. Kiki Motor Persada. Đây đều là những đối tác uy tín, có kinh nghiệm phân phối xe máy tại các khu vực trọng điểm và chia sẻ định hướng chuyển đổi sang các giải pháp di chuyển xanh.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Theo MOU, VinFast và các đại lý sẽ tích cực phối hợp nhằm triển khai kế hoạch mở showroom tập trung tại các khu vực chiến lược với tiềm năng xanh hóa giao thông cao như Jabodetabek, Tây và Đông Java, cùng Bali. Với mật độ dân cư lớn, tốc độ đô thị hóa nhanh và nhu cầu di chuyển cao, đây là những địa bàn trọng điểm trong chiến lược mở rộng mạng lưới của VinFast tại Indonesia.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Các showroom sẽ tuân thủ tiêu chuẩn triển khai quốc tế của VinFast. Trong giai đoạn đầu, hệ thống sẽ phân phối các dòng xe máy điện ứng dụng mô hình đổi pin như VinFast Flazz, VinFast Evo, VinFast Feliz II và VinFast Viper, đồng thời bổ sung các mẫu xe mới được nghiên cứu và tinh chỉnh phù hợp với điều kiện hạ tầng và thói quen sử dụng của người tiêu dùng Indonesia.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Indonesia sở hữu thị trường xe máy với quy mô lớn bậc nhất thế giới, với doanh số hàng năm đạt hàng triệu xe. Trong bối cảnh tỷ lệ thâm nhập của xe máy điện còn trong giai đoạn đầu và Chính phủ đang đẩy mạnh chuyển đổi năng lượng xanh, thị trường xe máy điện Indonesia được đánh giá bước vào giai đoạn tăng tốc, mở ra cơ hội rõ nét cho những doanh nghiệp theo đuổi chiến lược dài hạn và phát triển hệ sinh thái đồng bộ.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>VinFast là một trong những nhà sản xuất đầu tiên tại thị trường này tiên phong kiến tạo hệ sinh thái hỗ trợ xe máy điện ngay từ giai đoạn chuẩn bị đưa sản phẩm ra thị trường. Bên cạnh mở rộng mạng lưới phân phối, hãng tích cực phối hợp cùng đối tác chiến lược để hoàn thiện hệ thống dịch vụ - hậu mãi, nổi bật là mô hình tủ đổi pin do công ty phát triển hạ tầng sạc toàn cầu V-Green triển khai.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Ngay khi sản phẩm VinFast chính thức ra mắt, khách hàng có thể sử dụng mạng lưới tủ đổi pin đang được thí điểm tại khu vực Jabodetabek, qua đó trực tiếp trải nghiệm giải pháp sử dụng xe máy điện linh hoạt và thuận tiện. Cách tiếp cận đồng bộ giữa sản phẩm, hạ tầng và dịch vụ không chỉ tạo lợi thế cạnh tranh bền vững mà còn góp phần định hình tiêu chuẩn mới cho thị trường.</p>\n\n<figure class=\'my-6 text-center\'><img src=\'http://localhost/vinfast/public/images/news/17/1.jpg\' alt=\'Đại diện VinFast và các đại lý xe máy điện tại Indonesia tại lễ ký kết Biên bản ghi nhớ (MoU) hợp tác phân phối xe máy điện.\' class=\'w-full max-h-[600px] object-cover rounded-lg shadow-sm\'><figcaption class=\'mt-2 text-sm text-gray-500 italic\'>Đại diện VinFast và các đại lý xe máy điện tại Indonesia tại lễ ký kết Biên bản ghi nhớ (MoU) hợp tác phân phối xe máy điện.</figcaption></figure>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Trước đó, VinFast đã công bố chiến lược đưa xe máy điện ra thị trường quốc tế và ký kết hợp tác với các đại lý tại Philippines. Theo kế hoạch, trong năm 2026, VinFast sẽ đẩy mạnh mở rộng kinh doanh xe máy điện tại 5 thị trường quốc tế trọng điểm gồm Philippines, Indonesia, Ấn Độ, Thái Lan và Malaysia.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Bà Võ Thị Cẩm Tú, Giám đốc điều hành VinFast Xe máy điện thị trường quốc tế, cho biết: “Việc tiếp tục mở rộng hợp tác với các đại lý Indonesia thể hiện quyết tâm của VinFast trong việc nhanh chóng xây dựng mạng lưới phân phối và dịch vụ vững chắc tại thị trường này. Chúng tôi không chỉ mang đến sản phẩm chất lượng cao mà còn triển khai hệ sinh thái toàn diện, từ bán hàng, hậu mãi đến hạ tầng sạc và đổi pin, nhằm tạo nền tảng phát triển bền vững và lâu dài cùng các đối tác địa phương”.</p>\n\n<p class=\'mb-4 text-justify leading-relaxed\'>Sau hai năm hiện diện tại Indonesia, VinFast đã giới thiệu danh mục ô tô điện đa dạng từ các mẫu SUV đến dòng xe phục vụ kinh doanh vận tải, đồng thời đưa nhà máy tại Subang vào vận hành. Cùng với đó là quá trình hoàn thiện hệ sinh thái di chuyển xanh, mở rộng mạng lưới đại lý và xưởng dịch vụ, phát triển hạ tầng trạm sạc thông qua hợp tác với V-Green, cũng như hợp tác với các ngân hàng và công ty tài chính lớn. Trong năm 2026, hãng chính thức gia nhập thị trường xe máy điện Indonesia, đánh dấu bước tiến tiếp theo trong chiến lược phát triển và đầu tư bền vững tại quốc gia này. Thông qua các chính sách linh hoạt và cam kết đầu tư dài hạn, VinFast đang từng bước tạo điều kiện để người tiêu dùng Indonesia tiếp cận và tham gia sâu hơn vào xu hướng giao thông xanh toàn cầu./.</p>', 'Công ty', 2, '2026-03-06 06:18:52', 'Hiển thị');

-- --------------------------------------------------------

--
-- Table structure for table `news_img_info`
--

CREATE TABLE `news_img_info` (
  `news_id` int(10) UNSIGNED NOT NULL,
  `img_link` varchar(300) NOT NULL,
  `img_des` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news_img_info`
--

INSERT INTO `news_img_info` (`news_id`, `img_link`, `img_des`) VALUES
(1, 'public/images/news/1/1.jpg', 'VinFast tiếp tục hợp tác với nhiều đại lý quy mô lớn, thuộc nhóm nhà phân phối xe máy hàng đầu Philippines để triển khai kế hoạch ra mắt sản phẩm vào tháng 6/2026.'),
(2, 'public\\images\\news\\2\\1.png', ''),
(3, 'public\\images\\news\\3\\1.png', ''),
(4, 'public\\images\\news\\4\\1.jpeg', ''),
(5, 'public\\images\\news\\5\\1.jpg', ''),
(6, 'public\\images\\news\\6\\1.jpg', 'VinFast cung cấp đồng thời hai hình thức mua và thuê xe cho dải sản phẩm Green như Herio Green và Limo Green, giúp tài xế và doanh nghiệp linh hoạt lựa chọn theo nhu cầu và khả năng tài chính.'),
(7, 'public\\images\\news\\7\\1.jpg', ''),
(8, 'public\\images\\news\\8\\1.jpg', 'VinFast chính thức nhận đặt cọc mẫu xe điện thứ ba tại Ấn Độ – VF MPV 7, dòng xe đa dụng 7 chỗ cao cấp dành cho khách hàng cá nhân.'),
(8, 'public\\images\\news\\8\\2.jpg', 'VF MPV 7 sở hữu không gian cabin rộng rãi theo cả chiều ngang và dọc, tối ưu cấu hình 7 chỗ với sự thoải mái cho cả ba hàng ghế.'),
(8, 'public\\images\\news\\8\\3.jpg', 'VF MPV 7 được trang bị bộ pin 60,13 kWh, cho phép di chuyển hơn 500 km sau mỗi lần sạc đầy.'),
(9, 'public\\images\\news\\9\\1.jpg', ''),
(10, 'public\\images\\news\\10\\1.jpeg', ''),
(11, 'public\\images\\news\\11\\1.jpg', ''),
(12, 'public\\images\\news\\12\\1.jpg', 'Ông Tapan Ghosh (phải) và ông Raaj Sekar, chủ đại lý PPS Motors LLP, tại lễ khai trương.'),
(13, 'public\\images\\news\\13\\1.jpg', 'Ông Tapan Ghosh (trái), Tổng Giám đốc VinFast Ấn Độ, và ông Narendra Dixit, Giám đốc Khối Ngân hàng bán lẻ của CSB Bank tại lễ ký kết.'),
(14, 'public\\images\\news\\14\\1.jpg', ''),
(15, 'public\\images\\news\\15\\1.jpg', 'Ông Rituraj Singh (thứ hai từ trái sang), Phó Tổng Giám đốc Kinh doanh &amp; Phát triển mạng lưới của VinFast Ấn Độ, nhận giải thưởng từ đại diện ban tổ chức Jagran Hi-tech.'),
(15, 'public\\images\\news\\15\\2.jpg', 'Ông Arunodoy Das (giữa), Phó Tổng Giám đốc Kinh doanh của VinFast Ấn Độ, nhận giải thưởng từ ban tổ chức Car&amp;Bike Awards 2026.'),
(16, 'public\\images\\news\\16\\1.png', ''),
(17, 'public\\images\\news\\17\\1.jpg', 'Đại diện VinFast và các đại lý xe máy điện tại Indonesia tại lễ ký kết Biên bản ghi nhớ (MoU) hợp tác phân phối xe máy điện.');

-- --------------------------------------------------------

--
-- Table structure for table `news_tags`
--

CREATE TABLE `news_tags` (
  `news_id` int(10) UNSIGNED NOT NULL,
  `tags` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `type` enum('deposit','test_drive') NOT NULL DEFAULT 'deposit',
  `status` enum('pending','confirmed','cancelled','done') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `type`, `status`, `note`, `created_at`) VALUES
(8, 5, 165, 'deposit', 'done', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"Nam Hải\",\"phone\":\"0367581213\",\"email\":\"namcris07@gmail.com\",\"cccd\":\"123547854125\",\"province\":\"Đồng Tháp\",\"showroom\":\"VinFast Đồng Tháp - Cao Lãnh\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"transfer\",\"color\":{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Evo\",\"interior_code\":\"\",\"deposit_amount\":2000000,\"deposit_base_amount\":2000000,\"deposit_non_refundable\":1,\"payment_status\":\"refunded\",\"payment_updated_at\":\"2026-05-03T05:22:40+02:00\"}', '2026-05-02 20:10:33'),
(9, 5, 165, 'deposit', 'pending', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"Nam Hải\",\"phone\":\"0367581213\",\"email\":\"namcris07@gmail.com\",\"cccd\":\"120325485865\",\"province\":\"Đồng Nai\",\"showroom\":\"VinFast Đồng Nai - Biên Hòa\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"transfer\",\"color\":{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Evo\",\"interior_code\":\"\",\"deposit_amount\":2000000,\"deposit_base_amount\":2000000,\"deposit_non_refundable\":1,\"payment_status\":\"pending_verify\",\"payment_verified_at\":null}', '2026-05-02 20:23:52'),
(10, 5, 165, 'deposit', 'pending', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"Nam Hải\",\"phone\":\"0336587462\",\"email\":\"namcris07@gmail.com\",\"cccd\":\"\",\"province\":\"Đồng Nai\",\"showroom\":\"VinFast Đồng Nai - Biên Hòa\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"card-intl\",\"color\":{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Evo\",\"interior_code\":\"\",\"deposit_amount\":2000000,\"deposit_base_amount\":2000000,\"deposit_non_refundable\":1,\"payment_status\":\"pending_verify\",\"payment_verified_at\":null}', '2026-05-04 03:29:40'),
(11, 5, 165, 'deposit', 'pending', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"Nam Hải\",\"phone\":\"0336587462\",\"email\":\"namcris07@gmail.com\",\"cccd\":\"\",\"province\":\"Đồng Nai\",\"showroom\":\"VinFast Đồng Nai - Biên Hòa\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"transfer\",\"color\":{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Evo\",\"interior_code\":\"\",\"deposit_amount\":2000000,\"deposit_base_amount\":2000000,\"deposit_non_refundable\":1,\"payment_status\":\"pending_verify\",\"payment_verified_at\":null}', '2026-05-04 03:46:11'),
(12, 5, 166, 'deposit', 'pending', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"Nam Hải\",\"phone\":\"0336587462\",\"email\":\"namcris07@gmail.com\",\"cccd\":\"\",\"province\":\"Đồng Nai\",\"showroom\":\"VinFast Đồng Nai - Biên Hòa\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"transfer\",\"color\":{\"code\":\"BAU\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Flazz\",\"interior_code\":\"\",\"deposit_amount\":15000000,\"deposit_base_amount\":15000000,\"deposit_non_refundable\":1,\"payment_status\":\"pending_verify\",\"payment_verified_at\":null}', '2026-05-04 03:46:52'),
(17, 8, 165, 'deposit', 'pending', '{\"owner_type\":\"ca-nhan\",\"full_name\":\"trần\",\"phone\":\"0332786325\",\"email\":\"tran5843iii@gmail.com\",\"cccd\":\"083205003842\",\"province\":\"Hồ Chí Minh\",\"showroom\":\"VinFast HCM - Bình Thạnh\",\"salesperson\":\"\",\"voucher\":\"\",\"pay_method\":\"card-intl\",\"color\":{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"surcharge\":0},\"variant_name\":\"VinFast Evo\",\"interior_code\":\"\",\"deposit_amount\":2000000,\"deposit_base_amount\":2000000,\"deposit_non_refundable\":1,\"payment_status\":\"pending_verify\",\"payment_verified_at\":null}', '2026-05-08 08:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `page_assets`
--

CREATE TABLE `page_assets` (
  `id` int(10) UNSIGNED NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `asset_key` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(10) UNSIGNED DEFAULT 0,
  `mime_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_assets`
--

INSERT INTO `page_assets` (`id`, `page_type`, `asset_key`, `file_path`, `file_size`, `mime_type`, `created_at`) VALUES
(1, 'about', 'hero_image', 'about-page/hero_image.jpg', 3978072, 'image/jpeg', '2026-05-01 19:14:12'),
(10, 'about', 'timeline_2023_main', 'about-page/timeline_2023_main.png', 910964, 'image/jpeg', '2026-05-06 17:46:46'),
(24, 'about', 'timeline_2020_main', 'about-page/timeline_2020_main.jpg', 507365, 'image/jpeg', '2026-05-06 17:31:27'),
(28, 'about', 'timeline_2017_main', 'about-page/timeline_2017_main.webp', 699816, 'image/jpeg', '2026-05-06 17:10:05'),
(32, 'about', 'timeline_2018_main', 'about-page/timeline_2018_main.jpg', 335742, 'image/jpeg', '2026-05-06 17:27:40'),
(34, 'about', 'timeline_2019_main', 'about-page/timeline_2019_main.jpg', 87208, 'image/jpeg', '2026-05-06 17:30:07'),
(36, 'about', 'timeline_2020_secondary', 'about-page/timeline_2020_secondary.png', 110074, 'image/png', '2026-05-06 17:34:28'),
(37, 'about', 'timeline_2021_main', 'about-page/timeline_2021_main.webp', 103456, 'image/webp', '2026-05-06 17:38:04'),
(38, 'about', 'timeline_2022_main', 'about-page/timeline_2022_main.jpg', 370112, 'image/jpeg', '2026-05-06 17:38:56'),
(40, 'about', 'timeline_2024_main', 'about-page/timeline_2024_main.jpg', 469264, 'image/jpeg', '2026-05-06 17:40:37'),
(41, 'about', 'timeline_2026_main', 'about-page/timeline_2026_main.jpg', 727706, 'image/jpeg', '2026-05-06 17:43:12'),
(42, 'about', 'timeline_2025_main', 'about-page/timeline_2025_main.png', 2377787, 'image/png', '2026-05-06 17:44:26');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `specs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specs`)),
  `price` decimal(15,0) NOT NULL DEFAULT 0,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `specs`, `price`, `images`, `is_active`, `created_at`) VALUES
(165, 1, 'VinFast Evo', 'vinfast-evo', 'Bền bỉ mọi hành trình với thiết kế trẻ trung.\r\nSở hữu hệ thống hỗ trợ lái nâng cao ADAS, kết nối thông minh qua ứng dụng VinFast, và chính sách bảo hành pin 10 năm, đây là người bạn đồng hành thông minh trên mọi cung đường.', '{\"range\":\"85-165 km\",\"power\":\"1500 W\",\"acceleration\":\"N\\/A\",\"max_speed\":\"70 km\\/h\",\"battery\":\"LFP 1.5 kWh\",\"deposit_amount\":2000000,\"deposit_non_refundable\":1,\"exterior_colors\":[{\"code\":\"BAU1\",\"name\":\"Jet Black\",\"image\":\"uploads\\/products\\/vinfast-evo\\/bau1.png\",\"hex\":\"#1B1B1B\"},{\"code\":\"GNV1\",\"name\":\"Urban Mint\",\"image\":\"uploads\\/products\\/vinfast-evo\\/gnv1.png\",\"hex\":\"#8B9284\"},{\"code\":\"REQ1\",\"name\":\"Solar Ruby\",\"image\":\"uploads\\/products\\/vinfast-evo\\/req1.png\",\"hex\":\"#CD000C\"},{\"code\":\"WHR1\",\"name\":\"Infinity Blanc\",\"image\":\"uploads\\/products\\/vinfast-evo\\/whr1.png\",\"hex\":\"#E4E4E4\"}]}', 25600000, '[\"uploads\\/products\\/vinfast-evo\\/bau1.png\",\"uploads\\/products\\/vinfast-evo\\/gnv1.png\",\"uploads\\/products\\/vinfast-evo\\/req1.png\",\"uploads\\/products\\/vinfast-evo\\/whr1.png\"]', 1, '2026-04-23 08:50:33'),
(166, 1, 'VinFast Flazz', 'vinfast-flazz', 'Mẫu xe siêu nhẹ, linh hoạt cho đô thị.\r\nSở hữu hệ thống hỗ trợ lái nâng cao ADAS, kết nối thông minh qua ứng dụng VinFast, và chính sách bảo hành pin 10 năm, đây là người bạn đồng hành thông minh trên mọi cung đường.', '{\"range\":\"70-135 km\",\"power\":\"600 W\",\"acceleration\":\"N\\/A\",\"max_speed\":\"39 km\\/h\",\"battery\":\"LFP 1.2 kWh\",\"deposit_amount\":15000000,\"deposit_non_refundable\":1,\"exterior_colors\":[{\"code\":\"BAU\",\"name\":\"Jet Black\",\"image\":\"uploads\\/products\\/vinfast-flazz\\/bau.webp\",\"hex\":\"#1B1B1B\"},{\"code\":\"GNQ\",\"name\":\"Ivy Green\",\"image\":\"uploads\\/products\\/vinfast-flazz\\/gnq.webp\",\"hex\":\"#2A3C32\"},{\"code\":\"REQ\",\"name\":\"Solar Ruby\",\"image\":\"uploads\\/products\\/vinfast-flazz\\/req.webp\",\"hex\":\"#CD000C\"},{\"code\":\"WHR\",\"name\":\"Infinity Blanc\",\"image\":\"uploads\\/products\\/vinfast-flazz\\/whr.webp\",\"hex\":\"#E4E4E4\"}]}', 16900000, '[\"uploads\\/products\\/vinfast-flazz\\/bau.webp\",\"uploads\\/products\\/vinfast-flazz\\/gnq.webp\",\"uploads\\/products\\/vinfast-flazz\\/req.webp\",\"uploads\\/products\\/vinfast-flazz\\/whr.webp\"]', 1, '2026-04-23 08:50:33'),
(167, 1, 'VinFast ZGoo', 'vinfast-zgoo', 'Xe điện thời trang với đèn pha LED Projector.', '{\"range\":\"70 km\",\"power\":\"600 W\",\"acceleration\":\"N\\/A\",\"max_speed\":\"39 km\\/h\",\"battery\":\"LFP 1.2 kWh\",\"deposit_amount\":15000000,\"deposit_non_refundable\":1}', 15900000, '[\"uploads\\/products\\/vinfast-zgoo\\/bau.webp\",\"uploads\\/products\\/vinfast-zgoo\\/gnv.webp\",\"uploads\\/products\\/vinfast-zgoo\\/req.webp\",\"uploads\\/products\\/vinfast-zgoo\\/whr.webp\"]', 1, '2026-04-23 08:50:33'),
(168, 1, 'VinFast AMIO', 'vinfast-amio', 'Xe máy điện nhỏ gọn, giá tốt nhất.', '{\"range\":\"65 km\",\"power\":\"350 W\",\"acceleration\":\"<16s (0-50km\\/h)\",\"max_speed\":\"30 km\\/h\",\"battery\":\"LFP 1.024 kWh\",\"exterior_colors\":[{\"code\":\"WHR\",\"name\":\"Infinity Blanc\",\"image\":\"whr.png\",\"hex\":\"#E4E4E4\"},{\"code\":\"BAU\",\"name\":\"Jet Black\",\"image\":\"bau.png\",\"hex\":\"#1B1B1B\"},{\"code\":\"REQ\",\"name\":\"Solar Ruby\",\"image\":\"req.png\",\"hex\":\"#CD000C\"},{\"code\":\"BUW\",\"name\":\"Urban Mint\",\"image\":\"buw.png\",\"hex\":\"#8B9284\"},{\"code\":\"GRC\",\"name\":\"Zenith Grey\",\"image\":\"grc.png\",\"hex\":\"#898B8F\"}]}', 13900000, '[\"uploads\\/products\\/vinfast-amio\\/bau.png\",\"uploads\\/products\\/vinfast-amio\\/buw.png\",\"uploads\\/products\\/vinfast-amio\\/grc.png\",\"uploads\\/products\\/vinfast-amio\\/req.png\",\"uploads\\/products\\/vinfast-amio\\/whr.png\"]', 1, '2026-04-23 08:50:33'),
(169, 1, 'VinFast FELIZ 2025', 'vinfast-feliz-2025', 'Dòng xe trung cấp mạnh mẽ, cốp xe cực đại 34L.', '{\"range\":\"134-262 km\",\"power\":\"1800 W\",\"acceleration\":\"<16s (0-50km\\/h)\",\"max_speed\":\"70 km\\/h\",\"battery\":\"LFP 2.4 kWh\",\"exterior_colors\":[{\"code\":\"WHR\",\"name\":\"Infinity Blanc\",\"image\":\"uploads\\/products\\/vinfast-feliz-2025\\/whr.png\",\"hex\":\"#E4E4E4\"},{\"code\":\"BAU\",\"name\":\"Jet Black\",\"image\":\"uploads\\/products\\/vinfast-feliz-2025\\/bau.png\",\"hex\":\"#1B1B1B\"},{\"code\":\"GNQ\",\"name\":\"Ivy Green\",\"image\":\"uploads\\/products\\/vinfast-feliz-2025\\/gnq.png\",\"hex\":\"#2A3C32\"},{\"code\":\"GNV\",\"name\":\"Urban Mint\",\"image\":\"uploads\\/products\\/vinfast-feliz-2025\\/gnv.png\",\"hex\":\"#8B9284\"},{\"code\":\"YES\",\"name\":\"Sand Dune\",\"image\":\"uploads\\/products\\/vinfast-feliz-2025\\/yes.png\",\"hex\":\"#C7B299\"}]}', 26900000, '[\"uploads\\/products\\/vinfast-feliz-2025\\/whr.png\",\"uploads\\/products\\/vinfast-feliz-2025\\/bau.png\",\"uploads\\/products\\/vinfast-feliz-2025\\/gnq.png\",\"uploads\\/products\\/vinfast-feliz-2025\\/gnv.png\",\"uploads\\/products\\/vinfast-feliz-2025\\/yes.png\"]', 1, '2026-04-23 08:50:33'),
(170, 1, 'VinFast Viper', 'vinfast-viper', 'Xe máy điện cao cấp với phanh đĩa/cơ an toàn.', '{\"category\": \"Xe máy điện\", \"power\": \"1800 W\", \"range\": \"82-156 km\", \"acceleration\": \"15s (0-50km/h)\", \"battery\": \"LFP 1.5 kWh\", \"max_speed\": \"70 km/h\"}', 45500000, '[\"/uploads/products/vinfast-viper/bau.png\", \"/uploads/products/vinfast-viper/grc.png\", \"/uploads/products/vinfast-viper/req.png\", \"/uploads/products/vinfast-viper/whr.png\", \"/uploads/products/vinfast-viper/yes.png\"]', 1, '2026-04-23 08:50:33'),
(171, 2, 'VinFast VF 3 Eco', 'vinfast-vf3-eco', 'SUV mini sành điệu, nhỏ gọn và linh hoạt.', '{\"range\":\"215 km (NEDC)\",\"power\":\"30 kW\",\"acceleration\":\"N\\/A\",\"battery\":\"18.64 kWh\",\"deposit_amount\":15000000,\"deposit_non_refundable\":1,\"exterior_colors\":[{\"code\":\"CE18\",\"name\":\"Infinity Blanc\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/ce18.webp\",\"hex\":\"#E4E4E4\"},{\"code\":\"181U\",\"name\":\"Summer Yellow Body - Infinity Blanc Roof\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/181u.webp\",\"hex\":\"#E6CE63\",\"surcharge\":8000000},{\"code\":\"181Y\",\"name\":\"Sky Blue - Infinity Blanc Roof\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/181y.webp\",\"hex\":\"#6ECEDE\",\"surcharge\":8000000},{\"code\":\"1821\",\"name\":\"Rose Pink Body - Infinity Blanc Roof\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/1821.webp\",\"hex\":\"#B897A0\",\"surcharge\":8000000},{\"code\":\"CE1V\",\"name\":\"Zenith Grey\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/ce1v.webp\",\"hex\":\"#898B8F\"},{\"code\":\"CE1W\",\"name\":\"Urban Mint\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/ce1w.webp\",\"hex\":\"#8B9284\",\"surcharge\":8000000},{\"code\":\"CE2Q\",\"name\":\"Solar Ruby\",\"image\":\"uploads\\/products\\/vinfast-vf3-eco\\/ce2q.webp\",\"hex\":\"#CD000C\"}]}', 302000000, '[\"uploads\\/products\\/vinfast-vf3-eco\\/ce18.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/181u.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/181y.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/1821.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/ce1v.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/ce1w.webp\",\"uploads\\/products\\/vinfast-vf3-eco\\/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(172, 2, 'VinFast VF 3 Plus', 'vinfast-vf3-plus', 'Phiên bản cao cấp của VF 3 tích hợp Android Auto.', '{\"category\": \"SUV phân khúc Mini\", \"power\": \"30 kW\", \"range\": \"215 km (NEDC)\", \"acceleration\": \"N/A\", \"battery\": \"18.64 kWh\", \"top_speed\": \"100 km/h\"}', 315000000, '[\"/uploads/products/vinfast-vf3-plus/181u.webp\", \"/uploads/products/vinfast-vf3-plus/181y.webp\", \"/uploads/products/vinfast-vf3-plus/1821.webp\", \"/uploads/products/vinfast-vf3-plus/ce18.webp\", \"/uploads/products/vinfast-vf3-plus/ce1v.webp\", \"/uploads/products/vinfast-vf3-plus/ce1w.webp\", \"/uploads/products/vinfast-vf3-plus/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(173, 2, 'VinFast VF 6 Eco', 'vinfast-vf6-eco', 'Sự lựa chọn hoàn hảo cho gia đình trẻ.', '{\"category\": \"SUV phân khúc B\", \"power\": \"130 kW\", \"range\": \"485 km (NEDC)\", \"acceleration\": \"N/A\", \"battery\": \"59.6 kWh\"}', 689000000, '[\"/uploads/products/vinfast-vf6-eco/ce11.webp\", \"/uploads/products/vinfast-vf6-eco/ce18.webp\", \"/uploads/products/vinfast-vf6-eco/ce1v.webp\", \"/uploads/products/vinfast-vf6-eco/ce1w.webp\", \"/uploads/products/vinfast-vf6-eco/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(174, 2, 'VinFast VF 6 Plus', 'vinfast-vf6-plus', 'Hiệu năng cao với công suất 201 hp.', '{\"category\": \"SUV phân khúc B\", \"power\": \"150 kW\", \"range\": \"460 km (NEDC)\", \"acceleration\": \"N/A\", \"battery\": \"59.6 kWh\"}', 745000000, '[\"/uploads/products/vinfast-vf6-plus/ce11.webp\", \"/uploads/products/vinfast-vf6-plus/ce18.webp\", \"/uploads/products/vinfast-vf6-plus/ce1v.webp\", \"/uploads/products/vinfast-vf6-plus/ce1w.webp\", \"/uploads/products/vinfast-vf6-plus/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(175, 2, 'VinFast VF 7 Eco', 'vinfast-vf7-eco', 'SUV hạng C với thiết kế tương lai.', '{\"category\": \"SUV phân khúc C\", \"power\": \"130 kW\", \"range\": \"440 km (NEDC)\", \"acceleration\": \"N/A\", \"battery\": \"59.6 kWh\"}', 789000000, '[\"/uploads/products/vinfast-vf7-eco/ce11.webp\", \"/uploads/products/vinfast-vf7-eco/ce18.webp\", \"/uploads/products/vinfast-vf7-eco/ce1v.webp\", \"/uploads/products/vinfast-vf7-eco/ce1w.webp\", \"/uploads/products/vinfast-vf7-eco/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(176, 2, 'VinFast VF 7 Plus', 'vinfast-vf7-plus', 'Sức mạnh vượt trội công suất 150 kW.', '{\"category\": \"SUV phân khúc C\", \"power\": \"150 kW\", \"range\": \"N/A\", \"acceleration\": \"N/A\", \"battery\": \"70 kWh\"}', 889000000, '[\"/uploads/products/vinfast-vf7-plus/ce11.webp\", \"/uploads/products/vinfast-vf7-plus/ce18.webp\", \"/uploads/products/vinfast-vf7-plus/ce1v.webp\", \"/uploads/products/vinfast-vf7-plus/ce1w.webp\", \"/uploads/products/vinfast-vf7-plus/ce2q.webp\"]', 1, '2026-04-23 08:50:33'),
(177, 2, 'VinFast VF 8 Eco', 'vinfast-vf8-eco', 'SUV hạng D đẳng cấp, quãng đường di chuyển xa.', '{\"category\": \"SUV phân khúc D\", \"power\": \"150 kW\", \"range\": \"562 km (NEDC)\", \"acceleration\": \"11.8s (0-100km/h)\", \"battery\": \"87.7 kWh\"}', 1019000000, '[\"/uploads/products/vinfast-vf8-eco/171v.webp\", \"/uploads/products/vinfast-vf8-eco/1v18.webp\", \"/uploads/products/vinfast-vf8-eco/ce11.webp\", \"/uploads/products/vinfast-vf8-eco/ce18.webp\", \"/uploads/products/vinfast-vf8-eco/ce1m.webp\", \"/uploads/products/vinfast-vf8-eco/ce22.webp\"]', 1, '2026-04-23 08:50:33'),
(178, 2, 'VinFast VF 8 Plus', 'vinfast-vf8-plus', 'SUV mạnh mẽ 402 hp, dẫn động AWD.', '{\"category\": \"SUV phân khúc D\", \"power\": \"300 kW\", \"range\": \"457 km (WLTP)\", \"acceleration\": \"5.58s (0-100km/h)\", \"battery\": \"87.7 kWh\"}', 1199000000, '[\"/uploads/products/vinfast-vf8-plus/171v.webp\", \"/uploads/products/vinfast-vf8-plus/1v18.webp\", \"/uploads/products/vinfast-vf8-plus/ce11.webp\", \"/uploads/products/vinfast-vf8-plus/ce18.webp\", \"/uploads/products/vinfast-vf8-plus/ce1m.webp\", \"/uploads/products/vinfast-vf8-plus/ce22.webp\"]', 1, '2026-04-23 08:50:33'),
(179, 2, 'VinFast VF 9 Eco', 'vinfast-vf9-eco', 'SUV hạng E siêu sang, pin CATL 123 kWh.', '{\"category\": \"SUV phân khúc E\", \"power\": \"300 kW\", \"range\": \"626 km (WLTP)\", \"acceleration\": \"N/A\", \"battery\": \"123 kWh\"}', 1499000000, '[\"/uploads/products/vinfast-vf9-eco/ce11.webp\", \"/uploads/products/vinfast-vf9-eco/ce17.webp\", \"/uploads/products/vinfast-vf9-eco/ce18.webp\", \"/uploads/products/vinfast-vf9-eco/ce1m.webp\", \"/uploads/products/vinfast-vf9-eco/ce1v.webp\", \"/uploads/products/vinfast-vf9-eco/ce1w.webp\", \"/uploads/products/vinfast-vf9-eco/ce22.webp\"]', 1, '2026-04-23 08:50:33'),
(180, 2, 'VinFast VF 9 Plus', 'vinfast-vf9-plus', 'Đỉnh cao SUV điện với massage ghế và dẫn động AWD.', '{\"range\":\"602 km (WLTP)\",\"power\":\"300 kW\",\"acceleration\":\"N\\/A\",\"battery\":\"123 kWh\",\"deposit_amount\":15000000,\"deposit_non_refundable\":1}', 1699000000, '[\"uploads\\/products\\/vinfast-vf9-plus\\/ce11.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce17.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce18.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce1m.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce1v.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce1w.webp\",\"uploads\\/products\\/vinfast-vf9-plus\\/ce22.webp\"]', 1, '2026-04-23 08:50:33');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `key`, `value`) VALUES
(1, 'address', '627-629 Cách Mạng Tháng 8, Phường 15, Quận 10, TP. Hồ Chí Minh'),
(2, 'phone', '1900 23 23 89'),
(3, 'email', 'support.vn@vinfast.com'),
(4, 'tagline', 'Cùng bạn bứt phá mọi giới hạn'),
(5, 'about_timeline_text_2026', 'as'),
(6, 'about_timeline_text_2025', ''),
(7, 'about_timeline_text_2024', ''),
(8, 'about_timeline_text_2023', ''),
(9, 'about_timeline_text_2022', ''),
(10, 'about_timeline_text_2021', ''),
(11, 'about_timeline_text_2020', ''),
(12, 'about_timeline_text_2019', ''),
(13, 'about_timeline_text_2018', 'VinFast gây tiếng vang lớn khi ra mắt hai mẫu xe Lux A2.0 và Lux SA2.0 tại Paris Motor Show (Pháp). Thương hiệu Việt được tổ chức Autobest vinh danh là \"Ngôi sao mới\" của ngành ô tô thế giới.'),
(14, 'about_timeline_text_2017', 'Chính thức khởi công Tổ hợp sản xuất ô tô và xe máy điện tại Hải Phòng vào ngày 2/9. Đây là dự án công nghiệp trọng điểm với tiến độ hoàn thành kỷ lục 21 tháng.'),
(15, 'facebook_url', 'https://facebook.com/VinFastAuto'),
(16, 'about_text', 'VinFast là thương hiệu ô tô Việt Nam tiên PHONG...');

-- --------------------------------------------------------

--
-- Table structure for table `test_drives`
--

CREATE TABLE `test_drives` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `province` varchar(100) NOT NULL,
  `showroom` varchar(200) NOT NULL,
  `preferred_date` date NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','done') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `test_drives`
--

INSERT INTO `test_drives` (`id`, `name`, `email`, `phone`, `product_id`, `province`, `showroom`, `preferred_date`, `note`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn Nam', 'nvnam@example.com', '0987654321', 171, 'Hà Nội', 'VinFast Ocean Park', '2026-05-10', 'Muốn thử cảm giác lái SUV mini trong đô thị.', 'pending', '2026-05-10 09:05:23', '2026-05-10 09:05:23'),
(2, 'Trần Minh Tâm', 'tmtam@example.com', '0976543210', 177, 'TP. Hồ Chí Minh', 'VinFast Landmark 81', '2026-05-12', 'Đang cân nhắc đổi từ xe xăng sang xe điện cho gia đình.', 'confirmed', '2026-05-10 09:05:23', '2026-05-10 09:05:23'),
(3, 'Lê Thu Hà', 'ltha@example.com', '0965432109', 165, 'Đà Nẵng', 'VinFast Đà Nẵng', '2026-05-15', 'Mua xe máy điện cho con đi học, cần thử độ bền.', 'pending', '2026-05-10 09:05:23', '2026-05-10 09:05:23'),
(4, 'Đặng Văn Hùng', 'dvhung@example.com', '0954321098', 179, 'Hải Phòng', 'VinFast Smart City', '2026-05-20', 'Quan tâm dòng SUV hạng E cao cấp nhất.', 'pending', '2026-05-10 09:05:23', '2026-05-10 09:05:23'),
(5, 'Vũ Thị Mai', 'vtmai@example.com', '0943210987', 173, 'Cần Thơ', 'VinFast Ninh Kiều', '2026-05-25', 'Muốn trải nghiệm hệ thống ADAS trên VF6.', 'done', '2026-05-10 09:05:23', '2026-05-10 09:05:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','admin') NOT NULL DEFAULT 'member',
  `avatar` varchar(255) DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `is_locked`, `created_at`) VALUES
(5, 'Nam Hải', 'namcris07@gmail.com', '$2y$10$ZiA59qOno.oxz1PHFWFRNuH3M8dS/xiI92Wm51hqbwbnAcXf3U32e', 'admin', NULL, 0, '2026-04-19 08:47:14'),
(6, 'Admin VinFast', 'admin@vinfast.vn', '$2y$10$ReplaceThisWithARealBcryptHash', 'admin', NULL, 0, '2026-05-02 23:09:53'),
(7, 'Nguyễn Võ Quốc Lâm', 'quoclamnguyenvo@gmail.com', '$2y$10$mx.mjwbsIslrerAnW.71k.FJfDngX/VLOM9U4aG/hODN.QyT.mLzi', 'member', NULL, 0, '2026-05-07 13:16:06'),
(8, 'trần', 'tran5843iii@gmail.com', '$2y$10$ETzdLFFXEVhliLmGICDhPO.tDYVRTFeq92LwFRo1FclD70AIyWGDm', 'admin', NULL, 0, '2026-05-08 08:06:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `faq_topics`
--
ALTER TABLE `faq_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `news_img_info`
--
ALTER TABLE `news_img_info`
  ADD PRIMARY KEY (`news_id`,`img_link`,`img_des`);

--
-- Indexes for table `news_tags`
--
ALTER TABLE `news_tags`
  ADD PRIMARY KEY (`news_id`,`tags`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `page_assets`
--
ALTER TABLE `page_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_page_asset` (`page_type`,`asset_key`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `test_drives`
--
ALTER TABLE `test_drives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `faq_topics`
--
ALTER TABLE `faq_topics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `page_assets`
--
ALTER TABLE `page_assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `test_drives`
--
ALTER TABLE `test_drives`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `faq_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_img_info`
--
ALTER TABLE `news_img_info`
  ADD CONSTRAINT `news_img_info_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_tags`
--
ALTER TABLE `news_tags`
  ADD CONSTRAINT `news_tags_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_drives`
--
ALTER TABLE `test_drives`
  ADD CONSTRAINT `test_drives_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

