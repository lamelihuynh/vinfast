<?php

/**
 * app/helpers/ProductViewHelper.php — Product display helpers
 * Owner: Hai Nam
 */
class ProductViewHelper
{
    public static function thumbUrl(array $product): string
    {
        $images = is_array($product['images'] ?? null) ? $product['images'] : [];
        $imgRel = !empty($images[0]) && is_string($images[0])
            ? ltrim($images[0], '/')
            : 'products/default.jpg';

        return BASE_URL . 'public/images/' . $imgRel;
    }
}
