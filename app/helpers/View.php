<?php
/**
 * app/helpers/View.php — View Renderer
 * Owner: All members (common)
 *
 * Renders a view file inside a layout using output buffering.
 *
 * Usage (frontend):
 *   View::render("frontend/products/index", ["products" => $list, "pg" => $pg]);
 *
 * Usage (admin):
 *   View::render("admin/products/index", ["products" => $list], "admin");
 *
 * Layouts:
 *   "main"  → app/views/frontend/layouts/main.php   (default)
 *   "admin" → app/views/admin/layouts/admin.php
 *   "auth"  → app/views/frontend/layouts/auth.php
 *   "none"  → no layout (raw output)
 */
class View {
    public static function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data, EXTR_SKIP);               // make $products, $pg etc. available
        ob_start();
        include ROOT . "/app/views/{$view}.php"; // render the view fragment
        $content = ob_get_clean();               // $content is used by layouts

        if ($layout === 'none') {
            echo $content;
            return;
        }

        $layoutMap = [
            'main'  => ROOT . '/app/views/frontend/layouts/main.php',
            'admin' => ROOT . '/app/views/admin/layouts/admin.php',
            'auth'  => ROOT . '/app/views/frontend/layouts/auth.php',
        ];
        include $layoutMap[$layout] ?? $layoutMap['main'];
    }
}
