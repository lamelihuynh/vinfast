<?php

final class CartController
{
    public function index(): void
    {
        $this->requireCustomer('cart');
        SEO::set('Giỏ hàng');
        View::render('frontend/cart/index', ['cart' => CartService::summary((int)Auth::id())]);
    }

    public function add(): void
    {
        $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $this->mutate($id > 0 ? 'products/detail/' . $id : 'cart', static function (int $userId) use ($id): void {
            CartService::add($userId, (string)$id, (string)($_POST['variant_id'] ?? ''), self::integer('quantity'));
        }, 'Đã thêm phụ kiện vào giỏ.');
    }

    public function update(): void
    {
        $this->mutate('cart', static fn(int $id) => CartService::edit($id, self::integer('item_id'), self::integer('quantity')),
            'Đã cập nhật giỏ hàng.');
    }

    public function remove(): void
    {
        $this->mutate('cart', static fn(int $id) => CartService::edit($id, self::integer('item_id'), null),
            'Đã xoá món khỏi giỏ.');
    }

    private function requireCustomer(string $returnTo): void
    {
        if (!Auth::check()) {
            $_SESSION['return_to'] = $returnTo;
            Auth::requireLogin();
        }
    }

    private function mutate(string $returnTo, callable $fn, string $message): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            return;
        }
        $this->requireCustomer($returnTo);
        Auth::verifyCsrf();
        try {
            $fn((int)Auth::id());
            $_SESSION['flash'] = $message;
            $returnTo = 'cart';
        } catch (InvalidArgumentException | OutOfStockException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
        } catch (Throwable $e) {
            error_log('Cart mutation failed: ' . get_class($e));
            $_SESSION['errors'] = ['Không thể cập nhật giỏ lúc này. Vui lòng thử lại.'];
        }
        header('Location: ' . BASE_URL . $returnTo, true, 303);
    }

    private static function integer(string $field): int
    {
        $value = filter_var($_POST[$field] ?? null, FILTER_VALIDATE_INT);
        if ($value === false) {
            throw new InvalidArgumentException('Dữ liệu giỏ hàng không hợp lệ.');
        }
        return $value;
    }
}
