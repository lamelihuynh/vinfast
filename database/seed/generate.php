<?php

/**
 * database/seed/generate.php — Sinh dữ liệu benchmark (Phase 0) lên trên seed cơ bản.
 *
 * Được seed.php gọi khi có --size:
 *   php database/seed/seed.php --force --size=small|10k|100k
 *
 * Mục tiêu (TỔNG sau khi cộng seed cơ bản), N = số sản phẩm:
 *   small : N ≈ 100, 20 users, 50 orders
 *   10k   : N = 10 000      100k : N = 100 000
 *   users ≈ N/5 (≥ 20), orders ≈ N (small: 50), order_items ≈ 1.5 × orders,
 *   product_reviews ≈ N/2, wishlist_items ≈ N/2, giỏ hàng cho ~10% user,
 *   userActivityLogs ≈ 2N, 20 coupon + redemptions.
 *
 * Tất định: mt_srand(GEN_RNG_SEED) — cùng size cho ra cùng id/tên/giá/quan hệ.
 * Riêng mốc thời gian tính lùi từ 00:00 ngày chạy (để dashboard "30 ngày gần nhất" có dữ liệu).
 *
 * User sinh ra: email *@example.com, mật khẩu GEN_USER_PASSWORD (một hash bcrypt dùng chung).
 *
 * Nạp nhanh: PG dùng COPY (PDO::pgsqlCopyFromArray) theo lô, id gán tường minh rồi
 * sync_identity(); Mongo insertMany / bulkWrite lô 1000.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use MongoDB\BSON\UTCDateTime;

const GEN_SIZES = [
    'small' => ['products' => 100, 'users' => 20, 'orders' => 50],
    '10k' => ['products' => 10000, 'users' => 2000, 'orders' => 10000],
    '100k' => ['products' => 100000, 'users' => 20000, 'orders' => 100000],
];
const GEN_RNG_SEED = 20261121;
const GEN_USER_PASSWORD = 'User@123';
// password_hash('User@123', PASSWORD_BCRYPT) tính sẵn: bcrypt 20 000 lần sẽ mất vài phút
const GEN_USER_PASSWORD_HASH = '$2y$10$hkOgq/hSdoStvpXq6cOTwO6RYzQ0qTtUVhReu5nh64m.c2MrITu.2';
const GEN_COPY_BATCH = 5000;
const GEN_MONGO_BATCH = 1000;
const GEN_DAY = 86400;

// ---------------------------------------------------------------------------
// Tiện ích RNG / thời gian / chuỗi
// ---------------------------------------------------------------------------

/** [0, 1) */
function gen_rand(): float
{
    return mt_rand() / (mt_getrandmax() + 1);
}

function gen_chance(float $p): bool
{
    return gen_rand() < $p;
}

function gen_pick(array $a)
{
    return $a[mt_rand(0, count($a) - 1)];
}

/** @param array<string|int,float|int> $weights key => trọng số */
function gen_weighted(array $weights)
{
    $r = gen_rand() * array_sum($weights);
    foreach ($weights as $k => $w) {
        $r -= $w;
        if ($r < 0) {
            return $k;
        }
    }
    return array_key_last($weights);
}

/** Chọn k phần tử khác nhau, giữ thứ tự gốc. */
function gen_subset(array $a, int $k): array
{
    $a = array_values($a);
    $n = count($a);
    $k = max(0, min($k, $n));
    $idx = range(0, $n - 1);
    for ($i = 0; $i < $k; $i++) {
        $j = mt_rand($i, $n - 1);
        [$idx[$i], $idx[$j]] = [$idx[$j], $idx[$i]];
    }
    $chosen = array_slice($idx, 0, $k);
    sort($chosen);
    return array_map(static fn(int $i) => $a[$i], $chosen);
}

/** Xáo trộn (Fisher–Yates trên mt_rand, tất định theo seed). */
function gen_shuffle(array $a): array
{
    $a = array_values($a);
    for ($i = count($a) - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        [$a[$i], $a[$j]] = [$a[$j], $a[$i]];
    }
    return $a;
}

function gen_ucfirst(string $s): string
{
    return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, null, 'UTF-8');
}

/** Thời điểm trong [start, end], mật độ tăng dần theo thời gian (xu hướng tăng trưởng). */
function gen_time(int $start, int $end, float $shape = 0.7): int
{
    return $start + (int)(($end - $start) * (gen_rand() ** $shape));
}

/** n thời điểm đã sắp xếp tăng dần. */
function gen_times(int $n, int $start, int $end, float $shape = 0.7): array
{
    $t = [];
    for ($i = 0; $i < $n; $i++) {
        $t[] = gen_time($start, $end, $shape);
    }
    sort($t);
    return $t;
}

/** Số phần tử có giá trị <= $x trong mảng tăng dần. */
function gen_upper_bound(array $sorted, int $x): int
{
    $lo = 0;
    $hi = count($sorted);
    while ($lo < $hi) {
        $mid = ($lo + $hi) >> 1;
        if ($sorted[$mid] <= $x) {
            $lo = $mid + 1;
        } else {
            $hi = $mid;
        }
    }
    return $lo;
}

function gen_ts(int $ts): string
{
    return date('Y-m-d H:i:sP', $ts);
}

function gen_mdate(int $ts): UTCDateTime
{
    return new UTCDateTime($ts * 1000);
}

/** Bỏ dấu tiếng Việt, chữ thường. */
function gen_ascii(string $text): string
{
    static $map = ['à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e','ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u','ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d'];
    return strtr(mb_strtolower($text, 'UTF-8'), $map);
}

/** "Áo polo VinFast Pro" -> "ao-polo-vinfast-pro" (khớp pattern slug của validator) */
function gen_slug(string $text, int $max = 90): string
{
    $s = trim((string)preg_replace('/[^a-z0-9]+/', '-', gen_ascii($text)), '-');
    $s = rtrim(substr($s, 0, $max), '-');
    return $s !== '' ? $s : 'p';
}

/** Mã variant giống Product::variantCode: "Đỏ / M" -> "DO-M" */
function gen_code(string $text): string
{
    return strtoupper(gen_slug($text, 40));
}

function gen_round(float $v, int $step): int
{
    return max($step, (int)(round($v / $step) * $step));
}

// ---------------------------------------------------------------------------
// Nạp hàng loạt
// ---------------------------------------------------------------------------

/** COPY text format: \N = NULL, escape \ tab xuống dòng. */
function gen_copy(PDO $pdo, string $table, array $fields, array &$rows): int
{
    $n = count($rows);
    if ($n === 0) {
        return 0;
    }
    $lines = [];
    foreach ($rows as $r) {
        $cells = [];
        foreach ($r as $v) {
            if ($v === null) {
                $cells[] = '\N';
            } elseif (is_bool($v)) {
                $cells[] = $v ? 't' : 'f';
            } else {
                $cells[] = strtr((string)$v, ["\\" => "\\\\", "\t" => "\\t", "\n" => "\\n", "\r" => "\\r"]);
            }
        }
        $lines[] = implode("\t", $cells);
    }
    $pdo->pgsqlCopyFromArray($table, $lines, "\t", "\\\\N", implode(',', $fields));
    $rows = [];
    return $n;
}

/** Bộ đệm COPY theo bảng: add() tự flush khi đủ lô. */
final class GenCopyBuffer
{
    private array $rows = [];
    public int $count = 0;

    /** @param GenCopyBuffer[] $parents bảng cha (FK) được flush trước */
    public function __construct(private PDO $pdo, private string $table, private array $fields, private array $parents = [])
    {
    }

    public function add(array $row): void
    {
        $this->rows[] = $row;
        if (count($this->rows) >= GEN_COPY_BATCH) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        foreach ($this->parents as $p) {
            $p->flush();
        }
        $this->count += gen_copy($this->pdo, $this->table, $this->fields, $this->rows);
    }
}

/** Bộ đệm insertMany Mongo. */
final class GenMongoBuffer
{
    private array $docs = [];
    public int $count = 0;

    public function __construct(private MongoDB\Collection $coll)
    {
    }

    public function add(array $doc): void
    {
        $this->docs[] = $doc;
        if (count($this->docs) >= GEN_MONGO_BATCH) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if ($this->docs) {
            $this->coll->insertMany($this->docs, ['ordered' => false]);
            $this->count += count($this->docs);
            $this->docs = [];
        }
    }
}

// ---------------------------------------------------------------------------
// Từ điển dữ liệu tiếng Việt
// ---------------------------------------------------------------------------

function gen_dict(): array
{
    static $d = null;
    if ($d !== null) {
        return $d;
    }
    $d = [
        'family' => ['Nguyễn' => 38, 'Trần' => 11, 'Lê' => 9, 'Phạm' => 7, 'Hoàng' => 5, 'Huỳnh' => 5, 'Phan' => 4, 'Vũ' => 4, 'Võ' => 4,
            'Đặng' => 2, 'Bùi' => 2, 'Đỗ' => 2, 'Hồ' => 2, 'Ngô' => 2, 'Dương' => 1, 'Lý' => 1, 'Trương' => 1, 'Đinh' => 1],
        'middle' => ['Văn', 'Thị', 'Hữu', 'Đức', 'Minh', 'Thanh', 'Ngọc', 'Quang', 'Thu', 'Hoài', 'Gia', 'Bảo', 'Anh', 'Xuân', 'Kim', 'Tuấn'],
        'given' => ['An', 'Bình', 'Châu', 'Dũng', 'Giang', 'Hà', 'Hải', 'Hạnh', 'Hiếu', 'Hoa', 'Hùng', 'Huy', 'Khánh', 'Khoa', 'Lan', 'Linh',
            'Long', 'Mai', 'Minh', 'Nam', 'Ngân', 'Nhung', 'Phong', 'Phúc', 'Quân', 'Quỳnh', 'Sơn', 'Tâm', 'Thảo', 'Trang', 'Trung', 'Tuấn',
            'Vy', 'Yến', 'Đạt', 'Thắng', 'Hương', 'Nhi', 'Khôi', 'Duy'],
        'provinces' => ['Hà Nội' => 26, 'TP. Hồ Chí Minh' => 30, 'Đà Nẵng' => 7, 'Hải Phòng' => 6, 'Cần Thơ' => 4, 'Bình Dương' => 5,
            'Đồng Nai' => 4, 'Quảng Ninh' => 3, 'Khánh Hòa' => 3, 'Nghệ An' => 3, 'Thanh Hóa' => 3, 'Lâm Đồng' => 2],
        'showrooms' => [
            'Hà Nội' => ['VinFast Ocean Park', 'VinFast Times City', 'VinFast Long Biên', 'VinFast Mỹ Đình'],
            'TP. Hồ Chí Minh' => ['VinFast Landmark 81', 'VinFast Thảo Điền', 'VinFast Gò Vấp', 'VinFast Quận 7'],
            'Đà Nẵng' => ['VinFast Đà Nẵng', 'VinFast Ngũ Hành Sơn'],
        ],
        'streets' => ['Nguyễn Trãi', 'Lê Lợi', 'Trần Hưng Đạo', 'Hai Bà Trưng', 'Lý Thường Kiệt', 'Nguyễn Huệ', 'Điện Biên Phủ',
            'Cách Mạng Tháng 8', 'Võ Văn Tần', 'Phạm Văn Đồng', 'Lê Duẩn', 'Nguyễn Văn Linh', 'Hoàng Văn Thụ', 'Quang Trung'],
        'car_editions' => ['Sky', 'Ocean', 'Urban', 'Sport', 'City', 'Explorer', 'Signature', 'Heritage', 'Night', 'Limited',
            'Long Range', 'Premium', 'Family', 'Adventure', 'Pure'],
        'bike_editions' => ['Neo', 'S', 'Pro', 'Lite', 'Max', 'Urban', 'Sport', 'Classic', 'Plus', 'Smart', 'City', 'Youth'],
        'acc_qualifiers' => ['Premium', 'Plus', 'Pro', 'Lite', 'Classic', 'Sport', 'Eco', 'Urban', 'Signature', 'Limited', 'Gen 2',
            'Gen 3', 'Max', 'Mini', 'Comfort', 'Elite', 'Basic', 'Deluxe'],
        'apparel_colors' => ['Đen', 'Trắng', 'Xanh navy', 'Ghi', 'Be', 'Đỏ', 'Xanh rêu', 'Xanh dương', 'Hồng phấn', 'Đen nhám'],
        'car_acc_colors' => ['Đen', 'Be', 'Bạc', 'Nâu', 'Xám'],
        'model_factor' => ['VF 3' => 0.85, 'VF 5' => 0.9, 'VF 6' => 1.0, 'VF 7' => 1.1, 'VF 8' => 1.2, 'VF 9' => 1.3],
        'fallback_colors' => [
            ['colorCode' => 'CE18', 'colorName' => 'Infinity Blanc', 'colorHex' => '#F2F2F0'],
            ['colorCode' => 'CE11', 'colorName' => 'Jet Black', 'colorHex' => '#1B1B1B'],
            ['colorCode' => 'CE1M', 'colorName' => 'Crimson Red', 'colorHex' => '#A51C30'],
            ['colorCode' => 'CE1V', 'colorName' => 'Zenith Grey', 'colorHex' => '#7A7D80'],
            ['colorCode' => 'CE1W', 'colorName' => 'Deep Ocean', 'colorHex' => '#1F3A5F'],
        ],
        'selling_points' => ['Hỗ trợ cập nhật phần mềm từ xa (OTA).', 'Bảo hành pin 8 năm, không giới hạn km.', 'Trợ lý ảo tiếng Việt điều khiển bằng giọng nói.',
            'Gói hỗ trợ ADAS với cảnh báo điểm mù.', 'Chi phí vận hành thấp, sạc tại nhà tiện lợi.', 'Thiết kế bởi studio Pininfarina.',
            'Khung gầm chắc chắn, đạt chuẩn an toàn 5 sao.', 'Ưu đãi miễn phí sạc tại trạm V-GREEN.'],
        'acc_points' => ['Hàng chính hãng VinFast.', 'Đổi trả trong 7 ngày nếu lỗi nhà sản xuất.', 'Giao hàng toàn quốc.', 'Lắp đặt miễn phí tại xưởng dịch vụ.',
            'Thiết kế tối giản, hợp nhiều phong cách.', 'Vật liệu bền, dễ vệ sinh.', 'Phiên bản giới hạn số lượng.'],
        'keywords' => ['vf 3', 'vf3 giá lăn bánh', 'xe điện vinfast', 'vf 5 plus', 'vf 8', 'vf 9 giá', 'vf 6 eco', 'vf 7 plus', 'xe máy điện evo',
            'feliz 2025', 'sạc treo tường', 'bộ sạc di động', 'thảm lót sàn vf 8', 'mũ bảo hiểm', 'áo polo vinfast', 'pin xe máy điện',
            'camera hành trình', 'giá nóc', 'phim cách nhiệt', 'trả góp vf 5', 'xe điện giá rẻ', 'sạc nhanh', 'cáp sạc type 2', 'bọc vô lăng',
            'nước hoa ô tô', 'áo mưa', 'thùng sau xe máy', 'baga xe máy điện', 'pin lfp 3.5 kwh', 'vinfast klara', 'vento s', 'theon s',
            'xe điện cho học sinh', 'xe máy điện đi được bao xa', 'đặt cọc vf 3', 'khuyến mãi vinfast', 'phụ kiện vf 6', 'lót cốp', 'bạt phủ xe',
            'áo khoác gió', 'túi tote', 'mũ lưỡi trai', 'ổ cắm sạc', 'bộ chuyển đổi ccs2', 'vf 3 màu vàng', 'so sánh vf 5 và vf 6'],
        'review_open' => [
            5 => ['Rất hài lòng!', 'Tuyệt vời,', 'Sản phẩm xuất sắc.', 'Đáng đồng tiền bát gạo.', 'Quá ưng ý.'],
            4 => ['Khá tốt.', 'Hài lòng.', 'Sản phẩm ổn,', 'Dùng thích,', 'Tốt trong tầm giá.'],
            3 => ['Tạm được.', 'Bình thường,', 'Chưa thật sự ấn tượng.', 'Dùng được,'],
            2 => ['Hơi thất vọng.', 'Chưa ổn lắm,', 'Không như kỳ vọng.'],
            1 => ['Rất thất vọng.', 'Không hài lòng.', 'Chất lượng kém.'],
        ],
        'review_mid' => [
            'good' => ['giao hàng nhanh', 'đóng gói cẩn thận', 'chất lượng hoàn thiện tốt', 'nhân viên tư vấn nhiệt tình', 'đúng như mô tả',
                'vận hành êm', 'pin dùng lâu', 'thiết kế đẹp', 'lắp đặt dễ dàng', 'giá hợp lý'],
            'bad' => ['giao hàng chậm', 'màu hơi khác ảnh', 'đường may chưa kỹ', 'hướng dẫn sử dụng sơ sài', 'giá hơi cao',
                'phải chờ lắp đặt lâu', 'hộp bị móp', 'size hơi nhỏ'],
        ],
        'review_close' => ['Sẽ ủng hộ tiếp.', 'Recommend cho mọi người.', 'Cho 5 sao.', 'Sẽ cân nhắc mua thêm.', 'Mong shop cải thiện.', '', ''],
    ];
    return $d;
}

function gen_person(): array
{
    $d = gen_dict();
    $family = gen_weighted($d['family']);
    $given = gen_pick($d['given']);
    $middle = gen_pick($d['middle']);
    return [$family . ' ' . $middle . ' ' . $given, gen_slug($given, 20) . '.' . gen_slug($family . $middle, 30)];
}

function gen_phone(): string
{
    return gen_pick(['090', '091', '093', '094', '096', '097', '098', '086', '088', '070', '079', '085']) . str_pad((string)mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
}

function gen_location(): array
{
    $d = gen_dict();
    $province = gen_weighted($d['provinces']);
    $showroom = gen_pick($d['showrooms'][$province] ?? ['VinFast ' . $province]);
    return [$province, $showroom];
}

function gen_review_body(int $rating): string
{
    $d = gen_dict();
    $mood = $rating >= 4 ? 'good' : ($rating <= 2 ? 'bad' : (gen_chance(0.5) ? 'good' : 'bad'));
    $open = gen_pick($d['review_open'][$rating]);
    $mid = gen_pick($d['review_mid'][$mood]) . ', ' . gen_pick($d['review_mid'][$mood]) . '.';
    $parts = [$open, str_ends_with($open, ',') ? $mid : gen_ucfirst($mid)];
    $close = gen_pick($d['review_close']);
    if ($close !== '') {
        $parts[] = $close;
    }
    return implode(' ', $parts);
}

// ---------------------------------------------------------------------------
// Sinh sản phẩm
// ---------------------------------------------------------------------------

/** Tên + slug duy nhất. */
function gen_unique_name(array $candidates, array &$usedNames, array &$usedSlugs, string $fallbackSuffix): array
{
    $candidates[] = end($candidates) . ' ' . $fallbackSuffix;
    foreach ($candidates as $name) {
        $key = mb_strtolower($name, 'UTF-8');
        $slug = gen_slug($name);
        if (!isset($usedNames[$key]) && !isset($usedSlugs[$slug])) {
            $usedNames[$key] = true;
            $usedSlugs[$slug] = true;
            return [$name, $slug];
        }
    }
    throw new RuntimeException('Không sinh được tên duy nhất: ' . end($candidates));
}

/** variants[] cho Mongo + id duy nhất trong sản phẩm. */
function gen_variant_docs(string $pid, array $rows): array
{
    $out = [];
    $used = [];
    foreach ($rows as $r) {
        $base = $pid . '-' . gen_code($r['name']);
        $id = $base;
        for ($n = 2; isset($used[$id]); $n++) {
            $id = $base . '-' . $n;
        }
        $used[$id] = true;
        $out[] = [
            'variantId' => $id,
            'name' => $r['name'],
            'price' => (int)$r['price'],
            'isActive' => true,
            'attributes' => (object)($r['attributes'] ?? []),
            'colors' => $r['colors'] ?? [],
        ];
    }
    return $out;
}

function gen_vehicle(array $tpl, string $pid, array &$usedNames, array &$usedSlugs): array
{
    $d = gen_dict();
    $isCar = $tpl['isCar'];
    $edition = gen_pick($isCar ? $d['car_editions'] : $d['bike_editions']);
    $year = (string)mt_rand(2024, 2027);
    $code = chr(65 + mt_rand(0, 25)) . mt_rand(10, 99);
    [$name, $slug] = gen_unique_name(
        ["{$tpl['family']} {$edition} {$year}", "{$tpl['family']} {$edition} {$year} {$code}"],
        $usedNames, $usedSlugs, $pid
    );

    $base = gen_round($tpl['price'] * (0.9 + 0.25 * gen_rand()), $isCar ? 1000000 : 100000);
    $trims = $isCar ? [['Eco', 1.0], ['Plus', 1.08], ['Lux', 1.16]] : [['Tiêu chuẩn', 1.0], ['Cao cấp', 1.07], ['Đặc biệt', 1.14]];
    $nTrims = (int)gen_weighted([1 => 45, 2 => 35, 3 => 20]);

    $palette = $tpl['colors'] ?: $d['fallback_colors'];
    $colors = gen_subset($palette, mt_rand(min(2, count($palette)), min(6, count($palette))));
    foreach ($colors as $i => &$c) {
        $c['sortOrder'] = $i;
        $c['isDefault'] = $i === 0;
        $c += ['colorHex' => null, 'imagePath' => null, 'surcharge' => 0];
    }
    unset($c);

    $rows = [];
    for ($i = 0; $i < $nTrims; $i++) {
        $rows[] = [
            'name' => $trims[$i][0],
            'price' => gen_round($base * $trims[$i][1], $isCar ? 1000000 : 100000),
            'attributes' => ['trim' => $trims[$i][0]],
            'colors' => $colors,
        ];
    }

    $rangeKm = $tpl['rangeKm'] > 0 ? $tpl['rangeKm'] : ($isCar ? mt_rand(300, 600) : mt_rand(70, 200));
    $rangeKm = (int)round($rangeKm * (0.9 + 0.2 * gen_rand()));
    $deposit = $isCar
        ? ($base < 500000000 ? 15000000 : ($base < 1000000000 ? 20000000 : 50000000))
        : gen_pick([1000000, 2000000, 3000000]);
    $attrs = array_filter([
        'range' => $isCar ? "{$rangeKm} km (NEDC)" : "{$rangeKm} km",
        'rangeKm' => $rangeKm,
        'power' => $tpl['attrs']['power'] ?? null,
        'battery' => $tpl['attrs']['battery'] ?? null,
        'max_speed' => $tpl['attrs']['max_speed'] ?? null,
        'acceleration' => $tpl['attrs']['acceleration'] ?? null,
        'deposit_amount' => $deposit,
        'deposit_non_refundable' => 1,
    ], static fn($v) => $v !== null && $v !== '');

    $desc = sprintf('%s — %s phiên bản %s. Quãng đường tới %d km mỗi lần sạc%s. %s',
        $name, $isCar ? 'Ô tô điện' : 'Xe máy điện', $edition, $rangeKm,
        isset($attrs['battery']) ? ', pin ' . $attrs['battery'] : '', gen_pick($d['selling_points']));

    return [$name, $slug, $tpl['categoryID'], $desc, $tpl['images'], $attrs, $rows, $deposit];
}

function gen_accessory(array $tpl, string $pid, array &$usedNames, array &$usedSlugs): array
{
    $d = gen_dict();
    $q = gen_pick($d['acc_qualifiers']);
    $code = chr(65 + mt_rand(0, 25)) . mt_rand(10, 99);
    [$name, $slug] = gen_unique_name(["{$tpl['name']} {$q}", "{$tpl['name']} {$q} {$code}"], $usedNames, $usedSlugs, $pid);

    $f = 0.8 + 0.5 * gen_rand();
    $attrs = $tpl['attrs'];
    if (isset($attrs['warranty_months']) && $attrs['warranty_months'] > 0) {
        $attrs['warranty_months'] = gen_pick([6, 12, 12, 24, 24, 36]);
    }
    $rows = [];
    switch ($tpl['cat']) {
        case 3: // Pin & Sạc: tùy chọn
            foreach (gen_subset($tpl['variants'], mt_rand(1, count($tpl['variants']))) as $v) {
                $rows[] = ['name' => $v['name'], 'price' => gen_round($v['price'] * $f, 10000), 'attributes' => ['option' => $v['name']]];
            }
            if (gen_chance(0.25)) {
                $extra = gen_pick(['Kèm lắp đặt tại nhà', 'Bảo hành mở rộng 12 tháng', 'Bản quốc tế']);
                $rows[] = ['name' => $extra, 'price' => gen_round($rows[0]['price'] * (1.1 + 0.15 * gen_rand()), 10000), 'attributes' => ['option' => $extra]];
            }
            $attrs['compatible_models'] = gen_subset($attrs['compatible_models'], mt_rand(1, count($attrs['compatible_models'])));
            break;
        case 4: // Thời trang: màu × size
            $price = gen_round($tpl['minPrice'] * $f, 10000);
            $colorPool = array_values(array_unique(array_merge($tpl['colors'], gen_subset($d['apparel_colors'], 3))));
            $colors = gen_subset($colorPool, mt_rand(1, min(3, count($colorPool))));
            if ($tpl['sizes'] === ['Free size']) {
                $sizes = ['Free size'];
            } else {
                $all = ['S', 'M', 'L', 'XL'];
                $len = mt_rand(2, 4);
                $sizes = array_slice($all, mt_rand(0, 4 - $len), $len);
            }
            foreach ($colors as $c) {
                foreach ($sizes as $s) {
                    $rows[] = ['name' => "{$c} / {$s}", 'price' => $price, 'attributes' => ['color' => $c, 'size' => $s]];
                }
            }
            break;
        default: // 5 Phụ kiện xe: theo dòng xe (+ màu)
            $models = gen_subset($attrs['compatible_models'], mt_rand(1, min(5, count($attrs['compatible_models']))));
            $attrs['compatible_models'] = $models;
            $color = gen_chance(0.5) ? gen_pick($tpl['colors'] ?: $d['car_acc_colors']) : null;
            foreach ($models as $m) {
                $mf = $d['model_factor'][$m] ?? 1.0;
                $rows[] = [
                    'name' => $color ? "{$m} / {$color}" : $m,
                    'price' => gen_round($tpl['minPrice'] * $f * $mf, 10000),
                    'attributes' => array_filter(['fit_model' => $m, 'color' => $color]),
                ];
            }
    }
    $desc = $tpl['desc'] . ' ' . gen_pick($d['acc_points']) . ' Dòng ' . $q . '.';
    return [$name, $slug, $tpl['cat'], $desc, $tpl['images'], $attrs, $rows, 0];
}

// ---------------------------------------------------------------------------
// Chương trình chính
// ---------------------------------------------------------------------------

/**
 * Sinh dữ liệu benchmark lên trên seed cơ bản (seed.php phải chạy trước, DB không có dữ liệu sinh).
 * @param callable(string):void $out
 * @return array<string,int> số dòng đã sinh
 */
function generateBenchData(string $size, PDO $pdo, MongoDB\Database $mongo, callable $out): array
{
    if (!isset(GEN_SIZES[$size])) {
        throw new InvalidArgumentException("size không hợp lệ: {$size}");
    }
    ini_set('memory_limit', '3G');
    mt_srand(GEN_RNG_SEED);
    $d = gen_dict();
    $t0 = microtime(true);
    $tPhase = $t0;
    $phase = static function (string $label, int $rows) use ($out, &$tPhase, $t0): void {
        $now = microtime(true);
        $out(sprintf('generate: %-34s %9d  %6.1fs  (tổng %6.1fs, %d MB)', $label, $rows, $now - $tPhase, $now - $t0, memory_get_peak_usage(true) >> 20));
        $tPhase = $now;
    };

    $spec = GEN_SIZES[$size];
    $nProductsTotal = $spec['products'];
    $now = strtotime('today');
    $start18 = $now - 548 * GEN_DAY;
    $start12 = $now - 365 * GEN_DAY;

    $maxId = static fn(string $table): int => (int)$pdo->query("SELECT COALESCE(max(id), 0) FROM {$table}")->fetchColumn();
    $baseUsers = (int)$pdo->query('SELECT count(*) FROM users')->fetchColumn();
    $baseOrders = (int)$pdo->query('SELECT count(*) FROM orders')->fetchColumn();
    if ((int)$pdo->query("SELECT count(*) FROM users WHERE email LIKE '%@example.com'")->fetchColumn() > 0) {
        throw new RuntimeException('DB đã có dữ liệu sinh (user @example.com). Chạy lại với --force.');
    }

    // --- Catalog cơ bản (Mongo) ---------------------------------------------------
    $kindByCat = [];
    foreach ($mongo->selectCollection('categories')->find([], ['projection' => ['categoryID' => 1, 'kind' => 1]]) as $c) {
        $kindByCat[(int)$c['categoryID']] = (string)($c['kind'] ?? 'vehicle');
    }
    $catalogFile = require __DIR__ . '/accessories.php';

    // catalog gọn trong RAM: [pid, name, kind('v'|'a'), ts, deposit, active, vars[[vid, vname, price, colors[[code,name]], stock]]]
    $cat = [];
    $usedNames = [];
    $usedSlugs = [];
    $vehicleTpls = [];
    $maxPid = 0;
    foreach ($mongo->selectCollection('products')->find([], ['sort' => ['productID' => 1]]) as $p) {
        $pid = (string)$p['productID'];
        $maxPid = max($maxPid, (int)$pid);
        $usedNames[mb_strtolower((string)$p['name'], 'UTF-8')] = true;
        $usedSlugs[(string)$p['slug']] = true;
        $isVehicle = ($kindByCat[(int)$p['categoryID']] ?? 'vehicle') === 'vehicle';
        $attrs = (array)($p['attributes'] ?? []);
        $vars = [];
        foreach ($p['variants'] as $v) {
            $vars[] = [(string)$v['variantId'], (string)$v['name'], (int)$v['price'],
                array_map(static fn($c) => [(string)$c['colorCode'], (string)$c['colorName']], (array)($v['colors'] ?? [])), null];
        }
        // seed cơ bản đặt createdAt theo time() -> làm tròn về đầu ngày để kết quả sinh không phụ thuộc giờ chạy
        $baseTs = (int)strtotime('today', $p['createdAt']->toDateTime()->getTimestamp());
        $cat[] = [$pid, (string)$p['name'], $isVehicle ? 'v' : 'a', $baseTs,
            $isVehicle ? (int)($attrs['deposit_amount'] ?? 0) : 0, (bool)$p['isActive'], $vars];
        if ($isVehicle) {
            $colors = [];
            foreach ((array)($p['variants'][0]['colors'] ?? []) as $c) {
                $colors[] = array_intersect_key((array)$c, array_flip(['colorCode', 'colorName', 'colorHex', 'imagePath', 'surcharge']));
            }
            $vehicleTpls[] = [
                'family' => preg_replace('/\s+(Eco|Plus)$/i', '', (string)$p['name']),
                'isCar' => (int)$p['basePrice'] >= 100000000,
                'categoryID' => (int)$p['categoryID'],
                'price' => (int)$p['basePrice'],
                'rangeKm' => (int)($attrs['rangeKm'] ?? 0),
                'attrs' => $attrs,
                'images' => array_values((array)($p['images'] ?? [])),
                'colors' => $colors,
            ];
        }
    }
    $nBaseProducts = count($cat);
    if (!$vehicleTpls) {
        throw new RuntimeException('Không có sản phẩm xe cơ bản để làm mẫu — chạy seed cơ bản trước.');
    }
    $accTpls = [];
    foreach ($catalogFile['products'] as $p) {
        $colors = [];
        $sizes = [];
        foreach ($p['variants'] as $v) {
            if (isset($v['attributes']['color'])) {
                $colors[$v['attributes']['color']] = true;
            }
            if (isset($v['attributes']['size'])) {
                $sizes[$v['attributes']['size']] = true;
            }
        }
        $accTpls[] = $p + [
            'minPrice' => min(array_column($p['variants'], 'price')),
            'colors' => array_keys($colors),
            'sizes' => array_keys($sizes),
        ];
    }
    $baseOrderMax = $maxId('orders');

    $pdo->beginTransaction();

    // --- 1. Users ----------------------------------------------------------------
    $nUsers = max(0, $spec['users'] - $baseUsers);
    $firstUserId = $maxId('users') + 1;
    $userTs = gen_times($nUsers, $start18, $now - GEN_DAY, 0.75);
    if ($nUsers > 0) {
        $userTs[0] = $start18;
    }
    $userName = [];
    $userEmail = [];
    $buf = new GenCopyBuffer($pdo, 'users', ['id', 'name', 'email', 'password', 'role', 'is_locked', 'created_at', 'updated_at']);
    for ($i = 0; $i < $nUsers; $i++) {
        $id = $firstUserId + $i;
        [$name, $local] = gen_person();
        $email = $local . $id . '@example.com';
        $userName[$i] = $name;
        $userEmail[$i] = $email;
        $ts = gen_ts($userTs[$i]);
        $buf->add([$id, $name, $email, GEN_USER_PASSWORD_HASH, 'member', gen_chance(0.01), $ts, $ts]);
    }
    $buf->flush();
    $phase('users', $buf->count);
    /** chỉ số user (0-based) đã tồn tại tại thời điểm $t, thiên về user lâu năm */
    $pickUser = static function (int $t, float $bias = 1.3) use (&$userTs, $nUsers): int {
        $ub = max(1, gen_upper_bound($userTs, $t));
        return min($nUsers - 1, (int)($ub * (gen_rand() ** $bias)));
    };

    // --- 2. Products (Mongo) -----------------------------------------------------
    $nGen = max(0, $nProductsTotal - $nBaseProducts);
    $prodTs = gen_times($nGen, $start18, $now - GEN_DAY, 0.8);
    $productsColl = $mongo->selectCollection('products');
    $mbuf = new GenMongoBuffer($productsColl);
    $nVariants = 0;
    for ($i = 0; $i < $nGen; $i++) {
        $pid = (string)($maxPid + 1 + $i);
        $isVehicle = gen_chance(0.25);
        $g = $isVehicle
            ? gen_vehicle(gen_pick($vehicleTpls), $pid, $usedNames, $usedSlugs)
            : gen_accessory(gen_pick($accTpls), $pid, $usedNames, $usedSlugs);
        [$name, $slug, $catId, $desc, $images, $attrs, $rows, $deposit] = $g;
        $variants = gen_variant_docs($pid, $rows);
        $active = !gen_chance(0.03);
        $ts = $prodTs[$i];
        $mbuf->add([
            'productID' => $pid,
            'categoryID' => $catId,
            'name' => $name,
            'slug' => $slug,
            'description' => $desc,
            'basePrice' => min(array_column($variants, 'price')),
            'images' => $images,
            'isActive' => $active,
            'attributes' => (object)$attrs,
            'variants' => $variants,
            'avgRating' => 0.0,
            'totalReviews' => 0,
            'totalSold' => 0,
            'reviews' => [],
            'createdAt' => gen_mdate($ts),
            'updatedAt' => gen_mdate($ts + mt_rand(0, 30) * GEN_DAY > $now ? $now : $ts + mt_rand(0, 30) * GEN_DAY),
        ]);
        $vars = [];
        foreach ($variants as $v) {
            $stock = gen_chance(0.08) ? 0 : mt_rand(1, 200);
            $vars[] = [$v['variantId'], $v['name'], $v['price'], array_map(static fn($c) => [$c['colorCode'], $c['colorName']], $v['colors']), $stock];
        }
        $nVariants += count($vars);
        $cat[] = [$pid, $name, $isVehicle ? 'v' : 'a', $ts, $deposit, $active, $vars];
    }
    $mbuf->flush();
    unset($usedNames, $usedSlugs);
    $maxPid += $nGen;
    $mongo->selectCollection('counters')->updateOne(['_id' => 'productID'], ['$set' => ['seq' => $maxPid]], ['upsert' => true]);
    $phase("products ({$nVariants} variants)", $mbuf->count);

    // Pool theo thời gian tạo (để đơn/review chỉ trỏ tới sản phẩm đã tồn tại)
    $mkPool = static function (callable $filter) use (&$cat): array {
        $idx = [];
        foreach ($cat as $i => $p) {
            if ($filter($p)) {
                $idx[] = $i;
            }
        }
        usort($idx, static fn($a, $b) => [$cat[$a][3], $a] <=> [$cat[$b][3], $b]);
        return [$idx, array_map(static fn($i) => $cat[$i][3], $idx)];
    };
    $pools = [
        'deposit' => $mkPool(static fn($p) => $p[2] === 'v' && $p[4] > 0),
        'acc' => $mkPool(static fn($p) => $p[2] === 'a'),
        'accActive' => $mkPool(static fn($p) => $p[2] === 'a' && $p[5]),
        'all' => $mkPool(static fn($p) => true),
    ];
    /** chỉ số catalog của 1 sản phẩm thuộc pool, tạo trước $t; $bias > 1 thiên về sản phẩm cũ (bán lâu) */
    $pickProduct = static function (string $pool, int $t, float $bias = 1.0) use (&$pools): int {
        [$idx, $ts] = $pools[$pool];
        $ub = gen_upper_bound($ts, $t);
        if ($ub === 0) {
            $ub = count($idx);
        }
        return $idx[min($ub - 1, (int)($ub * (gen_rand() ** $bias)))];
    };

    // --- 3. Coupons --------------------------------------------------------------
    $nOrders = max(0, $spec['orders'] - $baseOrders);
    $lim = static fn(?float $share): ?int => $share === null ? null : max(3, (int)round($nOrders * $share));
    // code, mô tả, loại, giá trị, trần, đơn tối thiểu, quota (tỉ lệ số đơn), bắt đầu (ngày, âm = quá khứ), số ngày hiệu lực, active
    $couponDefs = [
        ['WELCOME10', 'Giảm 10% cho đơn hàng đầu tiên', 'percent', 10, 300000, 0, null, -365, 400, true],
        ['PHUKIEN100K', 'Giảm 100.000đ cho đơn phụ kiện từ 1 triệu', 'fixed', 100000, null, 1000000, 0.01, -300, 365, true],
        ['SAC500K', 'Giảm 500.000đ cho bộ sạc từ 10 triệu', 'fixed', 500000, null, 10000000, null, -200, 240, true],
        ['THOITRANG20', 'Giảm 20% đồ thời trang VinFast', 'percent', 20, 200000, 300000, 0.005, -180, 60, true],
        ['TET2026', 'Lì xì Tết Bính Ngọ', 'percent', 15, 1000000, 500000, 0.004, -300, 30, true],
        ['BLACKFRIDAY', 'Black Friday giảm 25%', 'percent', 25, 1500000, 1000000, 0.003, -5, 10, true],
        ['FLASH50K', 'Flash sale giảm 50.000đ (số lượng có hạn)', 'fixed', 50000, null, 300000, 0.0005, -120, 7, true],
        ['VIP20', 'Khách hàng thân thiết giảm 20%', 'percent', 20, 2000000, 2000000, null, -365, 730, true],
        ['FREESHIP30K', 'Hỗ trợ phí vận chuyển 30.000đ', 'fixed', 30000, null, 0, null, -365, 730, true],
        ['SUMMER2026', 'Hè rực rỡ giảm 12%', 'percent', 12, 500000, 500000, 0.006, -170, 60, true],
        ['BACKTOSCHOOL', 'Mùa tựu trường giảm 10%', 'percent', 10, 300000, 300000, 0.004, -100, 30, true],
        ['QUOCKHANH29', 'Mừng Quốc khánh giảm 200.000đ', 'fixed', 200000, null, 2000000, 0.003, -82, 7, true],
        ['MIDYEAR15', 'Sale giữa năm giảm 15%', 'percent', 15, 800000, 1000000, 0.005, -150, 20, true],
        ['NEWUSER50K', 'Thành viên mới giảm 50.000đ', 'fixed', 50000, null, 200000, null, -365, 730, true],
        ['APPONLY8', 'Đặt qua ứng dụng giảm 8%', 'percent', 8, 400000, 0, null, -240, 365, true],
        ['SINHNHAT', 'Quà sinh nhật 150.000đ', 'fixed', 150000, null, 1000000, 0.004, -300, 120, true],
        ['WALLBOX1TR', 'Giảm 1 triệu khi mua wallbox', 'fixed', 1000000, null, 15000000, 0.002, -250, 365, true],
        ['OLDCODE5', 'Mã cũ đã hết hạn', 'percent', 5, 100000, 0, 0.01, -420, 30, false],
        ['DISABLED10', 'Mã tạm khóa', 'percent', 10, 500000, 0, null, -60, 120, false],
        ['FUTURE2027', 'Chào năm mới 2027', 'percent', 10, 500000, 500000, null, 20, 60, true],
    ];
    $coupons = [];
    $firstCouponId = $maxId('coupons') + 1;
    $buf = new GenCopyBuffer($pdo, 'coupons', ['id', 'code', 'description', 'discount_type', 'discount_value', 'max_discount', 'min_order_amount',
        'usage_limit', 'used_count', 'per_user_limit', 'starts_at', 'expires_at', 'is_active', 'created_at', 'updated_at']);
    foreach ($couponDefs as $i => [$code, $desc, $type, $value, $maxDisc, $minOrder, $share, $startDay, $days, $active]) {
        $id = $firstCouponId + $i;
        $startTs = $now + $startDay * GEN_DAY;
        $endTs = $startTs + $days * GEN_DAY;
        $limit = $lim($share);
        $coupons[] = compact('id', 'code', 'type', 'value', 'maxDisc', 'minOrder', 'limit', 'startTs', 'endTs', 'active') + ['used' => 0, 'users' => []];
        $created = gen_ts($startTs - 7 * GEN_DAY);
        $buf->add([$id, $code, $desc, $type, $value, $maxDisc, $minOrder, $limit, 0, 1, gen_ts($startTs), gen_ts($endTs), $active, $created, $created]);
    }
    $buf->flush();
    $phase('coupons', $buf->count);

    // --- 4. Orders + items + redemptions ----------------------------------------
    $orderTs = gen_times($nOrders, $start12, $now - 3600, 0.65);
    $firstOrderId = $baseOrderMax + 1;
    $itemId = $maxId('order_items');
    $redId = $maxId('coupon_redemptions');
    $ordersBuf = new GenCopyBuffer($pdo, 'orders', ['id', 'user_id', 'type', 'status', 'payment_status', 'subtotal_amount', 'discount_amount',
        'total_amount', 'coupon_id', 'note', 'created_at', 'updated_at']);
    $itemsBuf = new GenCopyBuffer($pdo, 'order_items', ['id', 'order_id', 'product_id', 'variant_id', 'product_name_snapshot',
        'variant_name_snapshot', 'color_code', 'color_name_snapshot', 'unit_price', 'quantity'], [$ordersBuf]);
    $redBuf = new GenCopyBuffer($pdo, 'coupon_redemptions', ['id', 'coupon_id', 'user_id', 'order_id', 'discount_amount', 'redeemed_at'], [$ordersBuf]);
    $logBuf = new GenMongoBuffer($mongo->selectCollection('userActivityLogs'));
    $reserved = [];           // variantId => qty đang giữ bởi đơn pending
    $bought = [];             // [userIdx, catIdx, ts] cho review
    $statusCount = [];
    $sessionId = static fn(): string => sprintf('sess-%08x%08x', mt_rand(), mt_rand());

    for ($i = 0; $i < $nOrders; $i++) {
        $oid = $firstOrderId + $i;
        $t = $orderTs[$i];
        $u = $pickUser($t);
        $uid = $firstUserId + $u;
        $age = $now - $t;
        $status = $age > 30 * GEN_DAY
            ? gen_weighted(['done' => 84, 'confirmed' => 8, 'cancelled' => 8])
            : ($age > 7 * GEN_DAY
                ? gen_weighted(['done' => 45, 'confirmed' => 32, 'pending' => 15, 'cancelled' => 8])
                : gen_weighted(['pending' => 50, 'confirmed' => 30, 'done' => 12, 'cancelled' => 8]));
        $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
        $isDeposit = gen_chance(0.30);
        [$province, $showroom] = gen_location();
        $items = [];

        if ($isDeposit) {
            $ci = $pickProduct('deposit', $t, 1.2);
            $p = $cat[$ci];
            $v = gen_pick($p[6]);
            $color = $v[3] ? gen_pick($v[3]) : [null, null];
            $amount = $p[4];
            $payMethod = gen_weighted(['card-intl' => 30, 'card-domestic' => 45, 'transfer' => 25]);
            $payStatus = match ($status) {
                'pending' => gen_chance(0.85) ? 'pending_verify' : 'unpaid',
                'cancelled' => gen_chance(0.6) ? 'refunded' : 'failed',
                default => 'paid',
            };
            $items[] = [$ci, $v, $color, $amount, 1];
            $subtotal = $amount;
            $discount = 0;
            $coupon = null;
            $note = [
                'full_name' => $userName[$u], 'phone' => gen_phone(), 'email' => $userEmail[$u],
                'province' => $province, 'showroom' => $showroom, 'pay_method' => $payMethod,
                'color' => ['code' => $color[0] ?? '', 'name' => $color[1] ?? '', 'surcharge' => 0],
                'variant_name' => $v[1],
                'deposit_amount' => $amount, 'deposit_base_amount' => $amount, 'deposit_non_refundable' => 1,
                'payment_status' => $payStatus,
            ];
        } else {
            $k = (int)gen_weighted([1 => 55, 2 => 25, 3 => 12, 4 => 8]);
            $seen = [];
            $subtotal = 0;
            for ($j = 0, $tries = 0; $j < $k && $tries < 20; $tries++) {
                $ci = $pickProduct('acc', $t, 1.2);
                $v = gen_pick($cat[$ci][6]);
                if (isset($seen[$v[0]])) {
                    continue;
                }
                $seen[$v[0]] = true;
                $qty = (int)gen_weighted([1 => 75, 2 => 18, 3 => 7]);
                $items[] = [$ci, $v, [null, null], $v[2], $qty];
                $subtotal += $v[2] * $qty;
                $j++;
            }
            $payMethod = gen_weighted(['cod' => 45, 'transfer' => 25, 'card-domestic' => 20, 'card-intl' => 10]);
            $payStatus = $payMethod === 'cod'
                ? ($status === 'done' ? 'paid' : 'unpaid')
                : match ($status) {
                    'pending' => 'pending_verify',
                    'cancelled' => gen_chance(0.5) ? 'refunded' : 'failed',
                    default => 'paid',
                };
            // Coupon: chỉ đơn không hủy, đúng hạn, đủ giá trị tối thiểu, còn quota, user chưa dùng
            $discount = 0;
            $coupon = null;
            if ($status !== 'cancelled' && gen_chance(0.25)) {
                foreach (gen_shuffle($coupons) as $cand) {
                    $c = &$coupons[$cand['id'] - $firstCouponId];
                    if ($c['active'] && $t >= $c['startTs'] && $t <= $c['endTs'] && $subtotal >= $c['minOrder']
                        && ($c['limit'] === null || $c['used'] < $c['limit']) && !isset($c['users'][$uid])) {
                        $disc = $c['type'] === 'percent'
                            ? min((int)$c['maxDisc'], (int)(floor($subtotal * $c['value'] / 100 / 1000) * 1000))
                            : min((int)$c['value'], $subtotal);
                        if ($disc > 0) {
                            $c['used']++;
                            $c['users'][$uid] = true;
                            $coupon = $c;
                            $discount = $disc;
                            unset($c);
                            break;
                        }
                    }
                    unset($c);
                }
            }
            $note = [
                'full_name' => $userName[$u], 'phone' => gen_phone(), 'email' => $userEmail[$u],
                'address' => mt_rand(1, 399) . ' ' . gen_pick($d['streets']), 'province' => $province,
                'pay_method' => $payMethod, 'shipping_fee' => 0, 'payment_status' => $payStatus,
            ];
            if ($coupon) {
                $note['coupon_code'] = $coupon['code'];
            }
        }

        $total = $subtotal - $discount;
        $updated = $status === 'pending' ? $t : min($now, $t + mt_rand(1, 72) * 3600);
        $ordersBuf->add([$oid, $uid, $isDeposit ? 'deposit' : 'purchase', $status, $payStatus, $subtotal, $discount, $total,
            $coupon['id'] ?? null, json_encode($note, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), gen_ts($t), gen_ts($updated)]);
        foreach ($items as [$ci, $v, $color, $price, $qty]) {
            $itemsBuf->add([++$itemId, $oid, $cat[$ci][0], $v[0], $cat[$ci][1], $v[1], $color[0], $color[1], $price, $qty]);
            if ($status === 'pending') {
                $reserved[$v[0]] = ($reserved[$v[0]] ?? 0) + $qty;
            }
            if ($status !== 'cancelled') {
                $bought[] = [$u, $ci, $t];
            }
        }
        if ($coupon) {
            $redBuf->add([++$redId, $coupon['id'], $uid, $oid, $discount, gen_ts($t)]);
        }

        $sess = $sessionId();
        $device = gen_weighted(['mobile' => 55, 'desktop' => 40, 'tablet' => 5]);
        if ($coupon) {
            $logBuf->add(['userId' => $uid, 'sessionId' => $sess, 'targetId' => (string)$oid, 'actionType' => 'apply_coupon',
                'timestamp' => gen_mdate($t - 60), 'metadata' => ['source' => 'checkout', 'keyword' => null, 'device' => $device, 'page' => '/checkout',
                    'customData' => ['couponCode' => $coupon['code'], 'discount' => $discount]]]);
        }
        $logBuf->add([
            'userId' => $uid, 'sessionId' => $sess, 'targetId' => (string)$oid,
            'actionType' => $isDeposit ? 'deposit' : 'place_order',
            'timestamp' => gen_mdate($t),
            'metadata' => ['source' => 'checkout', 'keyword' => null, 'device' => $device, 'page' => $isDeposit ? '/order/checkout' : '/checkout',
                'customData' => ['orderId' => $oid, 'total' => $total, 'items' => count($items), 'productId' => $cat[$items[0][0]][0]]],
        ]);
    }
    $ordersBuf->flush();
    $itemsBuf->flush();
    $redBuf->flush();
    $logBuf->flush();
    ksort($statusCount);
    $phase('orders (' . http_build_query($statusCount, '', ' ') . ')', $ordersBuf->count);
    $phase('order_items', $itemsBuf->count);
    $phase('coupon_redemptions', $redBuf->count);
    $pdo->exec('UPDATE coupons c SET used_count = r.n FROM (SELECT coupon_id, count(*) n FROM coupon_redemptions GROUP BY coupon_id) r WHERE r.coupon_id = c.id');

    // --- 5. Inventory (sản phẩm sinh) — reserved = hàng của đơn pending ------------
    $buf = new GenCopyBuffer($pdo, 'inventory', ['product_id', 'variant_id', 'stock', 'reserved', 'version', 'updated_at']);
    $invTs = gen_ts($now);
    for ($i = $nBaseProducts, $n = count($cat); $i < $n; $i++) {
        foreach ($cat[$i][6] as $v) {
            $res = $reserved[$v[0]] ?? 0;
            $stock = $v[4] < $res ? $res + mt_rand(0, 20) : $v[4];
            $buf->add([$cat[$i][0], $v[0], $stock, $res, $res > 0 ? 1 : 0, $invTs]);
            unset($reserved[$v[0]]);
        }
    }
    $buf->flush();
    // Sản phẩm cơ bản đã có dòng tồn kho: cộng reserved, nâng stock nếu cần
    $upd = $pdo->prepare('UPDATE inventory SET stock = GREATEST(stock, reserved + ?), reserved = reserved + ?, version = version + 1 WHERE variant_id = ?');
    foreach ($reserved as $vid => $qty) {
        $upd->execute([$qty, $qty, $vid]);
    }
    $phase('inventory', $buf->count);

    // --- 6. Product reviews (UNIQUE user × product) -------------------------------
    $nReviews = intdiv($nProductsTotal, 2);
    $reviews = [];
    $seenReview = [];
    $nBought = count($bought);
    for ($tries = 0; count($reviews) < $nReviews && $tries < $nReviews * 5; $tries++) {
        if ($nBought > 0 && gen_chance(0.6)) {
            [$u, $ci, $tb] = $bought[mt_rand(0, $nBought - 1)];
            $lo = $tb + GEN_DAY;
        } else {
            $ci = $pickProduct('all', $now, 1.0);
            $u = mt_rand(0, $nUsers - 1);
            $lo = max($cat[$ci][3], $userTs[$u]);
        }
        $key = $u . ':' . $ci;
        if (isset($seenReview[$key]) || $lo >= $now) {
            continue;
        }
        $seenReview[$key] = true;
        $rating = (int)gen_weighted([5 => 45, 4 => 30, 3 => 12, 2 => 6, 1 => 7]);
        $reviews[] = [mt_rand($lo, min($now, $lo + 45 * GEN_DAY)), $u, $ci, $rating];
    }
    unset($seenReview);
    usort($reviews, static fn($a, $b) => $a[0] <=> $b[0]);
    $buf = new GenCopyBuffer($pdo, 'product_reviews', ['id', 'user_id', 'product_id', 'rating', 'body', 'helpful_count', 'is_approved', 'created_at']);
    $rid = $maxId('product_reviews');
    foreach ($reviews as [$ts, $u, $ci, $rating]) {
        $approved = ($now - $ts > 3 * GEN_DAY) ? gen_chance(0.9) : gen_chance(0.3);
        $buf->add([++$rid, $firstUserId + $u, $cat[$ci][0], $rating, gen_review_body($rating), (int)(gen_rand() ** 3 * 40), $approved, gen_ts($ts)]);
    }
    $buf->flush();
    unset($reviews);
    $phase('product_reviews', $buf->count);

    // --- 7. Wishlists ------------------------------------------------------------
    $nWish = intdiv($nProductsTotal, 2);
    $wishlistOf = [];
    $wlBuf = new GenCopyBuffer($pdo, 'wishlists', ['id', 'user_id', 'name', 'is_public', 'created_at']);
    $wiBuf = new GenCopyBuffer($pdo, 'wishlist_items', ['id', 'wishlist_id', 'product_id', 'variant_id', 'created_at'], [$wlBuf]);
    $wlId = $maxId('wishlists');
    $wiId = $maxId('wishlist_items');
    $seenWish = [];
    for ($tries = 0; $wiBuf->count + 0 < $nWish && count($seenWish) < $nWish && $tries < $nWish * 5; $tries++) {
        $u = (int)($nUsers * (gen_rand() ** 1.6));      // vài user thích rất nhiều
        $ci = $pickProduct('all', $now, 1.5);
        $vid = gen_chance(0.4) ? gen_pick($cat[$ci][6])[0] : null;
        $key = $u . ':' . $ci . ':' . $vid;
        if (isset($seenWish[$key])) {
            continue;
        }
        $seenWish[$key] = true;
        $lo = max($cat[$ci][3], $userTs[$u]);
        $ts = mt_rand(min($lo, $now - 3600), $now - 3600);
        if (!isset($wishlistOf[$u])) {
            $wishlistOf[$u] = ++$wlId;
            $wlBuf->add([$wlId, $firstUserId + $u, 'Yêu thích', gen_chance(0.1), gen_ts(min($ts, $userTs[$u] + GEN_DAY))]);
        }
        $wiBuf->add([++$wiId, $wishlistOf[$u], $cat[$ci][0], $vid, gen_ts($ts)]);
    }
    $wlBuf->flush();
    $wiBuf->flush();
    unset($seenWish);
    $phase("wishlists ({$wlBuf->count}) / items", $wiBuf->count);

    // --- 8. Carts (~10% user, 1–3 phụ kiện) ----------------------------------------
    $cartUsers = gen_subset(range(0, $nUsers - 1), (int)round($nUsers * 0.1));
    $cBuf = new GenCopyBuffer($pdo, 'carts', ['id', 'user_id', 'created_at', 'updated_at']);
    $ciBuf = new GenCopyBuffer($pdo, 'cart_items', ['id', 'cart_id', 'product_id', 'variant_id', 'color_code', 'quantity', 'unit_price', 'created_at', 'updated_at'], [$cBuf]);
    $cartId = $maxId('carts');
    $cartItemId = $maxId('cart_items');
    foreach ($cartUsers as $u) {
        $ts = max($userTs[$u], $now - mt_rand(0, 30) * GEN_DAY - mt_rand(0, GEN_DAY - 1));
        $cBuf->add([++$cartId, $firstUserId + $u, gen_ts($ts), gen_ts($ts)]);
        $seen = [];
        for ($j = 0, $k = mt_rand(1, 3); $j < $k; $j++) {
            $ci = $pickProduct('accActive', $now, 1.0);
            $v = gen_pick($cat[$ci][6]);
            if (isset($seen[$v[0]])) {
                continue;
            }
            $seen[$v[0]] = true;
            $ciBuf->add([++$cartItemId, $cartId, $cat[$ci][0], $v[0], null, (int)gen_weighted([1 => 80, 2 => 15, 3 => 5]), $v[2], gen_ts($ts), gen_ts($ts)]);
        }
    }
    $cBuf->flush();
    $ciBuf->flush();
    $phase("carts ({$cBuf->count}) / items", $ciBuf->count);

    $pdo->query('SELECT sync_identity()');
    $pdo->commit();
    $phase('postgres commit + sync_identity', 0);

    // --- 9. userActivityLogs còn lại (tổng ≈ 2N) ------------------------------------
    $nLogs = max(0, 2 * $nProductsTotal - $logBuf->count);
    $kwWeights = [];
    foreach ($d['keywords'] as $r => $kw) {
        $kwWeights[$kw] = 1 / ($r + 1);
    }
    $logTs = gen_times($nLogs, $start12, $now - 60, 0.6);
    foreach ($logTs as $t) {
        $action = gen_weighted(['view_product' => 58, 'search' => 22, 'add_to_cart' => 10, 'add_to_wishlist' => 6, 'remove_from_cart' => 2, 'remove_from_wishlist' => 2]);
        $uid = gen_chance(0.75) ? $firstUserId + $pickUser($t, 1.1) : null;
        $device = gen_weighted(['mobile' => 55, 'desktop' => 40, 'tablet' => 5]);
        $meta = ['source' => gen_weighted(['direct' => 35, 'google' => 30, 'facebook' => 18, 'zalo' => 10, 'email' => 7]), 'keyword' => null, 'device' => $device, 'page' => null, 'customData' => null];
        $target = null;
        if ($action === 'search') {
            $kw = gen_weighted($kwWeights);
            $meta['keyword'] = $kw;
            $meta['page'] = '/products?q=' . rawurlencode($kw);
            $meta['customData'] = ['resultCount' => mt_rand(0, 60)];
        } else {
            $ci = $pickProduct(in_array($action, ['add_to_cart', 'remove_from_cart'], true) ? 'acc' : 'all', $t, 1.3);
            $target = $cat[$ci][0];
            $meta['page'] = '/products/detail?id=' . $target;
            if ($action !== 'view_product') {
                $meta['customData'] = ['variantId' => gen_pick($cat[$ci][6])[0]] + ($action === 'add_to_cart' ? ['quantity' => (int)gen_weighted([1 => 85, 2 => 12, 3 => 3])] : []);
            }
        }
        $logBuf->add(['userId' => $uid, 'sessionId' => $sessionId(), 'targetId' => $target, 'actionType' => $action, 'timestamp' => gen_mdate($t), 'metadata' => $meta]);
    }
    $logBuf->flush();
    $phase('userActivityLogs', $logBuf->count);

    // --- 10. Trường denormalized Mongo (tính lại từ PG cho TẤT CẢ sản phẩm) ------------
    $updated = gen_sync_product_stats($pdo, $productsColl);
    $phase('products: totalSold/avgRating/reviews', $updated);

    $counts = [
        'users' => (int)$pdo->query('SELECT count(*) FROM users')->fetchColumn(),
        'products' => $productsColl->countDocuments(),
        'orders' => (int)$pdo->query('SELECT count(*) FROM orders')->fetchColumn(),
        'order_items' => (int)$pdo->query('SELECT count(*) FROM order_items')->fetchColumn(),
        'inventory' => (int)$pdo->query('SELECT count(*) FROM inventory')->fetchColumn(),
        'product_reviews' => (int)$pdo->query('SELECT count(*) FROM product_reviews')->fetchColumn(),
        'wishlist_items' => (int)$pdo->query('SELECT count(*) FROM wishlist_items')->fetchColumn(),
        'cart_items' => (int)$pdo->query('SELECT count(*) FROM cart_items')->fetchColumn(),
        'coupon_redemptions' => (int)$pdo->query('SELECT count(*) FROM coupon_redemptions')->fetchColumn(),
        'userActivityLogs' => $mongo->selectCollection('userActivityLogs')->countDocuments(),
    ];
    $out(sprintf('generate: xong size=%s trong %.1fs — tổng: %s', $size, microtime(true) - $t0,
        implode(', ', array_map(static fn($k, $v) => "{$v} {$k}", array_keys($counts), $counts))));
    $out('generate: user sinh ra đăng nhập bằng <email>@example.com / ' . GEN_USER_PASSWORD);
    return $counts;
}

/**
 * Đồng bộ products.totalSold / avgRating / totalReviews / reviews[] (5 review đã duyệt mới nhất) từ PG.
 * Dùng được độc lập sau khi sửa dữ liệu PG bằng tay.
 */
function gen_sync_product_stats(PDO $pdo, MongoDB\Collection $products): int
{
    $stats = [];
    $q = $pdo->query("SELECT oi.product_id, sum(oi.quantity) AS sold FROM order_items oi JOIN orders o ON o.id = oi.order_id
        WHERE o.status <> 'cancelled' GROUP BY oi.product_id");
    foreach ($q as $r) {
        $stats[$r['product_id']]['totalSold'] = (int)$r['sold'];
    }
    $q = $pdo->query('SELECT product_id, count(*) AS n, round(avg(rating), 2) AS avg FROM product_reviews WHERE is_approved GROUP BY product_id');
    foreach ($q as $r) {
        $stats[$r['product_id']]['totalReviews'] = (int)$r['n'];
        $stats[$r['product_id']]['avgRating'] = (float)$r['avg'];
    }
    $q = $pdo->query('SELECT r.id, r.user_id, r.product_id, r.rating, r.body, r.helpful_count, r.created_at, u.name
        FROM (SELECT *, row_number() OVER (PARTITION BY product_id ORDER BY created_at DESC, id DESC) AS rn
              FROM product_reviews WHERE is_approved) r
        JOIN users u ON u.id = r.user_id
        WHERE r.rn <= 5 ORDER BY r.product_id, r.rn');
    foreach ($q as $r) {
        $stats[$r['product_id']]['reviews'][] = [
            'reviewId' => (int)$r['id'],
            'userId' => (int)$r['user_id'],
            'userNameSnapshot' => (string)$r['name'],
            'rating' => (int)$r['rating'],
            'comment' => (string)$r['body'],
            'images' => [],
            'createdAt' => new UTCDateTime((int)(strtotime((string)$r['created_at']) * 1000)),
            'helpfulCount' => (int)$r['helpful_count'],
        ];
    }

    $ops = [];
    $n = 0;
    foreach ($stats as $pid => $s) {
        $ops[] = ['updateOne' => [['productID' => (string)$pid], ['$set' => [
            'totalSold' => $s['totalSold'] ?? 0,
            'totalReviews' => $s['totalReviews'] ?? 0,
            'avgRating' => $s['avgRating'] ?? 0.0,
            'reviews' => $s['reviews'] ?? [],
        ]]]];
        if (count($ops) >= GEN_MONGO_BATCH) {
            $n += $products->bulkWrite($ops, ['ordered' => false])->getMatchedCount();
            $ops = [];
        }
    }
    if ($ops) {
        $n += $products->bulkWrite($ops, ['ordered' => false])->getMatchedCount();
    }
    return $n;
}

// Chạy trực tiếp: php database/seed/generate.php --size=10k  (DB phải vừa seed cơ bản, chưa có dữ liệu sinh)
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $size = null;
    foreach (array_slice($argv, 1) as $arg) {
        if (strncmp($arg, '--size=', 7) === 0) {
            $size = substr($arg, 7);
        }
    }
    if (!isset(GEN_SIZES[$size])) {
        fwrite(STDERR, 'Cách dùng: php database/seed/seed.php --force --size=' . implode('|', array_keys(GEN_SIZES)) . PHP_EOL
            . '       (hoặc php database/seed/generate.php --size=... trên DB vừa seed cơ bản)' . PHP_EOL);
        exit(2);
    }
    generateBenchData($size, Db::pg(), Db::mongo(), static function (string $m): void {
        fwrite(STDOUT, $m . PHP_EOL);
    });
}
