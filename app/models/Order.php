<?php

/**
 * app/models/Order.php — Deposit / Purchase Order Model
 * Tables (PostgreSQL): orders + order_items
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController, UserController, OrderAdminController
 *
 * orders:      id, user_id, type(deposit|purchase), status, payment_status, subtotal_amount,
 *              discount_amount, total_amount, coupon_id, note(JSONB), created_at, updated_at
 * order_items: product/variant là id logic sang Mongo + snapshot tên/giá.
 *
 * Để view cũ không phải sửa, mọi SELECT trả thêm các khóa cũ từ item ĐẦU TIÊN của đơn:
 *   product_id, product_name, price (đơn đặt cọc luôn có đúng 1 item).
 */
class Order
{
    private const ALLOWED_STATUSES = ['pending', 'confirmed', 'cancelled', 'done'];

    private const ALLOWED_PAYMENT_STATUSES = ['unpaid', 'pending_verify', 'paid', 'failed', 'refunded'];

    private const STATUS_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['done', 'cancelled'],
        'done' => [],
        'cancelled' => [],
    ];

    /** Item đầu tiên của đơn: thay cho JOIN products cũ (products giờ ở MongoDB). */
    private const FIRST_ITEM_JOIN = "LEFT JOIN LATERAL (
            SELECT oi.product_id, oi.variant_id, oi.product_name_snapshot AS product_name, oi.unit_price AS price
            FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1
        ) p ON TRUE";

    private const ORDER_COLUMNS = "o.id, o.user_id, o.type, o.status, o.payment_status, o.subtotal_amount,
        o.discount_amount, o.total_amount, o.coupon_id, o.note::text AS note,
        to_char(o.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
        to_char(o.updated_at, 'YYYY-MM-DD HH24:MI:SS') AS updated_at,
        p.product_id, p.variant_id, p.product_name, p.price";

    private const SQL_BASE = "SELECT " . self::ORDER_COLUMNS . ", u.name AS user_name, u.email
        FROM orders o
        " . self::FIRST_ITEM_JOIN . "
        JOIN users u ON o.user_id = u.id";

    /**
     * Tạo đơn 1 sản phẩm (luồng đặt cọc cũ). Ghi orders + order_items trong 1 transaction.
     * Số tiền của đơn đặt cọc = deposit_base_amount / deposit_amount trong note.
     * (OrderService::place của T4 sẽ thay hàm này cho luồng checkout giỏ hàng.)
     */
    public static function create($userId, $productId, $type = 'deposit', $note = null)
    {
        $product = Product::getByIdAdmin((int)$productId);
        if (!$product) {
            return false;
        }

        $noteArr = self::decodeOrderNote($note);
        if ($noteArr === [] && trim((string)$note) !== '') {
            $noteArr = ['text' => (string)$note];
        }

        $paymentStatus = (string)($noteArr['payment_status'] ?? 'pending_verify');
        if (!in_array($paymentStatus, self::ALLOWED_PAYMENT_STATUSES, true)) {
            $paymentStatus = 'pending_verify';
        }

        $amount = max(0, (float)($noteArr['deposit_base_amount'] ?? $noteArr['deposit_amount'] ?? $product['price'] ?? 0));
        $variantId = (string)$product['default_variant_id'];
        $variantName = trim((string)($noteArr['variant_name'] ?? '')) ?: 'Tiêu chuẩn';
        $colorCode = trim((string)($noteArr['color']['code'] ?? '')) ?: null;
        $colorName = trim((string)($noteArr['color']['name'] ?? '')) ?: null;

        try {
            return Db::transaction(static function (PDO $pdo) use ($userId, $type, $paymentStatus, $amount, $noteArr, $product, $variantId, $variantName, $colorCode, $colorName): int {
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, type, status, payment_status, subtotal_amount, total_amount, note)
                    VALUES (?, ?, 'pending', ?, ?, ?, CAST(? AS jsonb)) RETURNING id");
                $stmt->execute([(int)$userId, $type === 'purchase' ? 'purchase' : 'deposit', $paymentStatus, $amount, $amount, self::encodeOrderNote($noteArr)]);
                $orderId = (int)$stmt->fetchColumn();

                $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id, product_name_snapshot, variant_name_snapshot, color_code, color_name_snapshot, unit_price, quantity)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
                $item->execute([$orderId, (string)$product['id'], $variantId, (string)$product['name'], $variantName, $colorCode, $colorName, $amount]);

                return $orderId;
            });
        } catch (Throwable $e) {
            error_log('Order::create: ' . $e->getMessage());
            return false;
        }
    }

    public static function getByUserId($userId, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $stmt = $pdo->prepare("\n            SELECT " . self::ORDER_COLUMNS . "\n            FROM orders o \n            " . self::FIRST_ITEM_JOIN . "\n            WHERE o.user_id = :user_id \n            ORDER BY o.created_at DESC\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countByUserId($userId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function getById($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("\n            " . self::SQL_BASE . "\n            WHERE o.id = ?\n        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll($page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $stmt = $pdo->prepare("\n            " . self::SQL_BASE . "\n            ORDER BY o.created_at DESC \n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM orders");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function updateStatus($id, $status)
    {
        global $pdo;
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getByStatus($status, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $sql = self::SQL_BASE . "
            WHERE o.status = :status
            ORDER BY o.created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAdminList(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        global $pdo;

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        [$where, $params] = self::buildAdminFilters($filters);

        $sql = self::SQL_BASE;
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY o.created_at DESC, o.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAdminList(array $filters = []): int
    {
        global $pdo;

        [$where, $params] = self::buildAdminFilters($filters);

        $sql = "SELECT COUNT(*) AS cnt
                FROM orders o
                " . self::FIRST_ITEM_JOIN . "
                JOIN users u ON o.user_id = u.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function countAdminPaymentSummary(array $filters = []): array
    {
        global $pdo;

        [$where, $params] = self::buildAdminFilters($filters);
        $paymentStatusSql = self::paymentStatusSql();

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN {$paymentStatusSql} = 'unpaid' THEN 1 ELSE 0 END) AS unpaid,
                    SUM(CASE WHEN {$paymentStatusSql} = 'pending_verify' THEN 1 ELSE 0 END) AS pending_verify,
                    SUM(CASE WHEN {$paymentStatusSql} = 'paid' THEN 1 ELSE 0 END) AS paid,
                    SUM(CASE WHEN {$paymentStatusSql} = 'failed' THEN 1 ELSE 0 END) AS failed,
                    SUM(CASE WHEN {$paymentStatusSql} = 'refunded' THEN 1 ELSE 0 END) AS refunded
                FROM orders o
                " . self::FIRST_ITEM_JOIN . "
                JOIN users u ON o.user_id = u.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'all' => (int)($row['total'] ?? 0),
            'unpaid' => (int)($row['unpaid'] ?? 0),
            'pending_verify' => (int)($row['pending_verify'] ?? 0),
            'paid' => (int)($row['paid'] ?? 0),
            'failed' => (int)($row['failed'] ?? 0),
            'refunded' => (int)($row['refunded'] ?? 0),
        ];
    }

    public static function validStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    public static function allowedNextStatuses(string $status): array
    {
        $status = trim($status);
        return self::STATUS_TRANSITIONS[$status] ?? [];
    }

    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        $fromStatus = trim($fromStatus);
        $toStatus = trim($toStatus);

        if (!in_array($fromStatus, self::ALLOWED_STATUSES, true) || !in_array($toStatus, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        if ($fromStatus === $toStatus) {
            return true;
        }

        return in_array($toStatus, self::allowedNextStatuses($fromStatus), true);
    }

    public static function validPaymentStatuses(): array
    {
        return self::ALLOWED_PAYMENT_STATUSES;
    }

    /** Nhận note JSON kiểu cũ; đơn mới lưu payment_status ở cột riêng (xem getPaymentStatusById). */
    public static function getPaymentStatusFromNote($note): string
    {
        $decoded = self::decodeOrderNote($note);
        $status = trim((string)($decoded['payment_status'] ?? 'pending_verify'));
        if (!in_array($status, self::ALLOWED_PAYMENT_STATUSES, true)) {
            return 'pending_verify';
        }

        return $status;
    }

    public static function getPaymentStatusById(int $orderId): string
    {
        $order = self::getById($orderId);
        if (!$order) {
            return 'pending_verify';
        }

        return in_array($order['payment_status'] ?? '', self::ALLOWED_PAYMENT_STATUSES, true)
            ? (string)$order['payment_status']
            : self::getPaymentStatusFromNote($order['note'] ?? null);
    }

    public static function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        global $pdo;

        $paymentStatus = trim($paymentStatus);
        if (!in_array($paymentStatus, self::ALLOWED_PAYMENT_STATUSES, true)) {
            return false;
        }

        $order = self::getById($orderId);
        if (!$order) {
            return false;
        }

        $note = self::decodeOrderNote($order['note'] ?? null);
        $note['payment_status'] = $paymentStatus;
        $note['payment_updated_at'] = date('c');

        if ($paymentStatus === 'paid' && empty($note['payment_verified_at'])) {
            $note['payment_verified_at'] = date('c');
        }

        if ($paymentStatus !== 'paid' && $paymentStatus !== 'refunded' && isset($note['payment_verified_at'])) {
            unset($note['payment_verified_at']);
        }

        $json = self::encodeOrderNote($note);
        $stmt = $pdo->prepare('UPDATE orders SET payment_status = ?, note = CAST(? AS jsonb) WHERE id = ?');
        return $stmt->execute([$paymentStatus, $json, $orderId]);
    }

    private static function decodeOrderNote($note): array
    {
        if (is_array($note)) {
            return $note;
        }

        $raw = trim((string)$note);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function encodeOrderNote(array $note): string
    {
        $json = json_encode($note, JSON_UNESCAPED_UNICODE);
        return $json !== false ? $json : '{}';
    }

    private static function paymentStatusSql(): string
    {
        return 'o.payment_status';
    }

    private static function buildAdminFilters(array $filters = []): array
    {
        $where = [];
        $params = [];

        $status = trim((string)($filters['status'] ?? 'all'));
        if ($status !== '' && $status !== 'all' && in_array($status, self::ALLOWED_STATUSES, true)) {
            $where[] = 'o.status = :status';
            $params[':status'] = $status;
        }

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(u.name ILIKE :q1 OR u.email ILIKE :q2 OR p.product_name ILIKE :q3 OR o.id::text LIKE :q4)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
            $params[':q4'] = '%' . $q . '%';
        }

        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        if ($dateFrom !== '' && self::isValidDate($dateFrom)) {
            $where[] = 'o.created_at >= CAST(:date_from AS date)';
            $params[':date_from'] = $dateFrom;
        }

        $dateTo = trim((string)($filters['date_to'] ?? ''));
        if ($dateTo !== '' && self::isValidDate($dateTo)) {
            $where[] = "o.created_at < CAST(:date_to AS date) + interval '1 day'";
            $params[':date_to'] = $dateTo;
        }

        return [$where, $params];
    }

    private static function isValidDate(string $dateStr): bool
    {
        $dateStr = trim($dateStr);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr) !== 1) {
            return false;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $dateStr);
        return $dt !== false && $dt->format('Y-m-d') === $dateStr;
    }

    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
}
