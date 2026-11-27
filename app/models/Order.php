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

    /**
     * First item of the order, replacing the old JOIN on products (now in MongoDB).
     * item_count rides along so lists can show "X + N more" without a second query;
     * the window runs before LIMIT 1, so it counts every item of the order.
     */
    private const FIRST_ITEM_JOIN = "LEFT JOIN LATERAL (
            SELECT oi.product_id, oi.variant_id, oi.product_name_snapshot AS product_name, oi.unit_price AS price,
                   COUNT(*) OVER() AS item_count
            FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1
        ) p ON TRUE";

    private const ORDER_COLUMNS = "o.id, o.user_id, o.type, o.status, o.payment_status, o.subtotal_amount,
        o.discount_amount, o.total_amount, o.coupon_id, o.note::text AS note,
        to_char(o.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
        to_char(o.updated_at, 'YYYY-MM-DD HH24:MI:SS') AS updated_at,
        p.product_id, p.variant_id, p.product_name, p.price, COALESCE(p.item_count, 0) AS item_count";

    private const SQL_BASE = "SELECT " . self::ORDER_COLUMNS . ", u.name AS user_name, u.email
        FROM orders o
        " . self::FIRST_ITEM_JOIN . "
        JOIN users u ON o.user_id = u.id";

    public static function getByUserId($userId, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        // id DESC breaks created_at ties so OFFSET pages never repeat or skip an order.
        // total_rows saves callers a countByUserId round trip; it is missing on pages past the end.
        $stmt = $pdo->prepare("\n            SELECT " . self::ORDER_COLUMNS . ",\n                   COUNT(*) OVER() AS total_rows\n            FROM orders o \n            " . self::FIRST_ITEM_JOIN . "\n            WHERE o.user_id = :user_id \n            ORDER BY o.created_at DESC, o.id DESC\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Items of many orders in one query, so order lists avoid N+1.
     *
     * @param int[] $ids
     * @return array<int, array<int, array>> orderId => items; every requested id is present.
     */
    public static function getItemsByOrderIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $stmt = Db::pg()->prepare(
            "SELECT oi.id, oi.order_id, oi.product_id, oi.variant_id,
                    oi.product_name_snapshot, oi.variant_name_snapshot,
                    oi.color_code, oi.color_name_snapshot,
                    oi.unit_price, oi.quantity, oi.subtotal,
                    oi.product_name_snapshot AS product_name, oi.unit_price AS price
             FROM order_items oi
             WHERE oi.order_id = ANY(CAST(:ids AS bigint[]))
             ORDER BY oi.order_id, oi.id"
        );
        // PDO cannot bind arrays, hence the PG array literal.
        $stmt->bindValue(':ids', '{' . implode(',', $ids) . '}', PDO::PARAM_STR);
        $stmt->execute();

        $grouped = array_fill_keys($ids, []);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int)$row['order_id']][] = $row;
        }

        return $grouped;
    }

    /**
     * One order with all its items, or null unless it belongs to $userId
     * (stops users from reading other orders by editing the id in the URL).
     * Legacy keys product_id/product_name/price mirror the first item for older views.
     */
    public static function getByIdForUser(int $id, int $userId): ?array
    {
        if ($id <= 0 || $userId <= 0) {
            return null;
        }

        $stmt = Db::pg()->prepare(
            "SELECT o.id, o.user_id, o.type, o.status, o.payment_status, o.subtotal_amount,
                    o.discount_amount, o.total_amount, o.coupon_id, o.note::text AS note,
                    to_char(o.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
                    to_char(o.updated_at, 'YYYY-MM-DD HH24:MI:SS') AS updated_at,
                    u.name AS user_name, u.email,
                    COALESCE(json_agg(json_build_object(
                        'id', oi.id,
                        'product_id', oi.product_id, 'variant_id', oi.variant_id,
                        'product_name', oi.product_name_snapshot, 'variant_name', oi.variant_name_snapshot,
                        'color_code', oi.color_code, 'color_name', oi.color_name_snapshot,
                        'unit_price', oi.unit_price, 'quantity', oi.quantity, 'subtotal', oi.subtotal)
                      ORDER BY oi.id) FILTER (WHERE oi.id IS NOT NULL), '[]') AS items
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.id = :id AND o.user_id = :uid
             GROUP BY o.id, u.id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // PDO hands json_agg back as a string.
        $items = json_decode((string)$row['items'], true);
        $row['items'] = is_array($items) ? $items : [];

        $first = $row['items'][0] ?? null;
        $row['product_id'] = $first['product_id'] ?? null;
        $row['variant_id'] = $first['variant_id'] ?? null;
        $row['product_name'] = $first['product_name'] ?? null;
        $row['price'] = $first['unit_price'] ?? null;

        return $row;
    }

    /**
     * One page of a user's orders, presented for the order list views.
     * Normally 2 queries (orders + items); total comes from total_rows, with a COUNT
     * fallback only when the requested page lies past the end and returned nothing.
     *
     * @return array{orders: array, total: int, page: int}
     */
    public static function getUserOrderPage(int $userId, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $rows = self::getByUserId($userId, $page, $perPage);

        if ($rows === [] && $page > 1) {
            $total = self::countByUserId($userId);
            $page = max(1, (int)ceil($total / max(1, $perPage)));
            $rows = $total > 0 ? self::getByUserId($userId, $page, $perPage) : [];
        } else {
            $total = (int)($rows[0]['total_rows'] ?? 0);
        }

        $items = self::getItemsByOrderIds(array_column($rows, 'id'));
        $orders = [];
        foreach ($rows as $row) {
            $orders[] = self::presentForUser($row, $items[(int)$row['id']] ?? []);
        }

        return ['orders' => $orders, 'total' => $total, 'page' => $page];
    }

    /**
     * View model shared by /order and /user/orders. Amount and payment status come from
     * the orders columns: generated orders do not carry them in the legacy note JSON.
     */
    public static function presentForUser(array $row, array $items): array
    {
        $note = self::decodeOrderNote($row['note'] ?? null);
        $id = (int)($row['id'] ?? 0);
        $first = $items[0] ?? [];
        $firstName = (string)($first['product_name_snapshot'] ?? $row['product_name'] ?? '');
        $extra = count($items) - 1;

        $paymentStatus = (string)($row['payment_status'] ?? '');
        if (!in_array($paymentStatus, self::ALLOWED_PAYMENT_STATUSES, true)) {
            $paymentStatus = self::getPaymentStatusFromNote($note);
        }

        return [
            'orderId' => 'VF-' . strtoupper(dechex($id)) . '-' . strtoupper(substr(md5((string)$id), 0, 4)),
            'orderDbId' => $id,
            'type' => (string)($row['type'] ?? 'deposit'),
            'status' => (string)($row['status'] ?? 'pending'),
            'carName' => ($firstName !== '' ? $firstName : '—') . ($extra > 0 ? ' + ' . $extra . ' sản phẩm' : ''),
            'orderDate' => !empty($row['created_at']) ? date('d/m/Y H:i', strtotime((string)$row['created_at'])) : '',
            'customerName' => (string)($note['full_name'] ?? ($row['user_name'] ?? '')),
            'email' => (string)($note['email'] ?? ($row['email'] ?? '')),
            'phone' => (string)($note['phone'] ?? ''),
            'province' => (string)($note['province'] ?? ''),
            'showroom' => (string)($note['showroom'] ?? ''),
            'totalAmount' => (float)($row['total_amount'] ?? 0),
            'depositAmount' => (float)($row['total_amount'] ?? 0),
            'paymentStatus' => $paymentStatus,
            'items' => $items,
        ];
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

    /**
     * Payment-status counts for the admin filter; 'all' doubles as the pagination total,
     * so the order list page needs no separate countAdminList query.
     */
    public static function countAdminPaymentSummary(array $filters = []): array
    {
        global $pdo;

        [$where, $params] = self::buildAdminFilters($filters);

        $sql = "SELECT
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE o.payment_status = 'unpaid') AS unpaid,
                    COUNT(*) FILTER (WHERE o.payment_status = 'pending_verify') AS pending_verify,
                    COUNT(*) FILTER (WHERE o.payment_status = 'paid') AS paid,
                    COUNT(*) FILTER (WHERE o.payment_status = 'failed') AS failed,
                    COUNT(*) FILTER (WHERE o.payment_status = 'refunded') AS refunded
                FROM orders o
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
            // EXISTS over all items: matching only the first item missed accessories further down the order.
            $where[] = '(u.name ILIKE :q1 OR u.email ILIKE :q2
                OR EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.product_name_snapshot ILIKE :q3)
                OR o.id::text LIKE :q4)';
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
