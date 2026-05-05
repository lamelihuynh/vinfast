<?php

class CheckoutViewHelper
{
    public static function prepare(array $product, array $selectedColor, array $provinces, array $showrooms, array $formData, array $switchProducts): array
    {
        $productId = (int)($product['id'] ?? 0);
        $productName = (string)($product['name'] ?? 'VinFast');
        $productSlug = trim((string)($product['slug'] ?? ''));
        $priceText = number_format((float)($product['price'] ?? 0), 0, ',', '.');

        $extractProductFamily = static function (string $text): string {
            $value = strtolower(trim($text));
            if ($value === '') {
                return '';
            }

            if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $value, $familyMatch)) {
                return 'vf' . $familyMatch[1];
            }

            $normalized = preg_replace('/[^a-z0-9]+/i', '-', $value);
            $normalized = trim((string)$normalized, '-');
            if ($normalized === '') {
                return '';
            }

            if (strpos($normalized, 'vinfast-') === 0) {
                $normalized = substr($normalized, 8);
            }

            $normalized = trim((string)$normalized, '-');
            if ($normalized === '') {
                return '';
            }

            $parts = explode('-', $normalized);
            $family = strtolower(trim((string)($parts[0] ?? '')));
            if (!preg_match('/^[a-z0-9]+$/', $family)) {
                return '';
            }

            return $family;
        };

        $productFamily = '';
        if (!empty($product['slug'])) {
            $productFamily = $extractProductFamily((string)$product['slug']);
        }
        if ($productFamily === '' && !empty($product['name'])) {
            $productFamily = $extractProductFamily((string)$product['name']);
        }

        $resolveImageUrl = static function (string $imgRel, string $preferredSlug = '') use ($productFamily): string {
            $imgRel = trim($imgRel);
            if ($imgRel === '') {
                return '';
            }

            $preferredSlug = strtolower(trim($preferredSlug));
            if ($preferredSlug === '') {
                $preferredSlug = '';
            }

            if (preg_match('/^https?:\/\//i', $imgRel)) {
                return $imgRel;
            }

            $imgRel = ltrim($imgRel, '/');

            if (strpos($imgRel, '/') !== false) {
                $fullPath = ROOT . '/public/images/' . $imgRel;
                if (is_file($fullPath)) {
                    return BASE_URL . 'public/images/' . $imgRel;
                }

                $basename = basename($imgRel);
                $fixCandidates = [];
                if ($preferredSlug !== '') {
                    $fixCandidates[] = 'uploads/products/' . $preferredSlug . '/' . $basename;
                }
                if ($productFamily !== '') {
                    $fixCandidates[] = 'uploads/products/' . $productFamily . '/' . $basename;
                }

                foreach ($fixCandidates as $candidate) {
                    $candidatePath = ROOT . '/public/images/' . $candidate;
                    if (is_file($candidatePath)) {
                        return BASE_URL . 'public/images/' . $candidate;
                    }
                }

                return BASE_URL . 'public/images/' . $imgRel;
            }

            $basename = basename($imgRel);
            $candidates = [
                $preferredSlug !== '' ? 'uploads/products/' . $preferredSlug . '/' . $basename : '',
                $productFamily !== '' ? 'uploads/products/' . $productFamily . '/' . $basename : '',
                'uploads/products/' . $imgRel,
                'products/' . $imgRel,
                $imgRel,
            ];

            foreach ($candidates as $candidate) {
                if ($candidate === '') {
                    continue;
                }
                $fullPath = ROOT . '/public/images/' . $candidate;
                if (is_file($fullPath)) {
                    return BASE_URL . 'public/images/' . $candidate;
                }
            }

            if ($preferredSlug !== '') {
                $slugPriorityPath = ROOT . '/public/images/uploads/products/' . $preferredSlug . '/' . $basename;
                if (is_file($slugPriorityPath)) {
                    $match = str_replace(ROOT . '/public/images/', '', $slugPriorityPath);
                    $match = str_replace('\\', '/', $match);
                    return BASE_URL . 'public/images/' . $match;
                }
            }

            if ($productFamily !== '') {
                $priorityPath = ROOT . '/public/images/uploads/products/' . $productFamily . '/' . $basename;
                if (is_file($priorityPath)) {
                    $match = str_replace(ROOT . '/public/images/', '', $priorityPath);
                    $match = str_replace('\\', '/', $match);
                    return BASE_URL . 'public/images/' . $match;
                }
            }

            return BASE_URL . 'public/images/products/' . $imgRel;
        };

        $specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
        $images = is_array($product['images'] ?? null) ? $product['images'] : [];
        $rawExteriorColors = is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [];
        $rawInteriorColors = is_array($specs['interior_colors'] ?? null) ? $specs['interior_colors'] : [];
        $rawVariants = is_array($specs['variants'] ?? null) ? $specs['variants'] : [];

        $variantChoices = [];
        foreach ($rawVariants as $row) {
            if (!is_array($row)) {
                continue;
            }

            $variantName = trim((string)($row['name'] ?? ''));
            $variantPrice = (float)($row['price'] ?? 0);
            if ($variantName === '') {
                continue;
            }

            if ($variantPrice <= 0) {
                $variantPrice = (float)($product['price'] ?? 0);
            }

            $variantChoices[] = [
                'name' => $variantName,
                'price' => $variantPrice,
            ];
        }

        if (empty($variantChoices)) {
            $variantChoices[] = [
                'name' => $productName,
                'price' => (float)($product['price'] ?? 0),
            ];
        }

        $colorChoices = [];
        foreach ($rawExteriorColors as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? '')));
            $name = trim((string)($row['name'] ?? ''));
            $hex = trim((string)($row['hex'] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $colorChoices[] = [
                'code' => $code,
                'name' => $name,
                'hex' => $hex,
                'image' => trim((string)($row['image'] ?? '')),
                'surcharge' => max(0, (int)($row['surcharge'] ?? 0)),
            ];
        }

        $colorCode = trim((string)($selectedColor['code'] ?? ''));
        $colorName = trim((string)($selectedColor['name'] ?? ''));
        foreach ($colorChoices as &$colorChoice) {
            $colorImage = '';
            if (!empty($colorChoice['image'])) {
                $colorImage = $resolveImageUrl((string)$colorChoice['image'], $productSlug);
            }

            if ($colorImage === '') {
                $targetCode = strtoupper((string)($colorChoice['code'] ?? ''));
                foreach ($images as $imgRel) {
                    if (!is_string($imgRel) || trim($imgRel) === '') {
                        continue;
                    }

                    $imgPath = parse_url((string)$imgRel, PHP_URL_PATH);
                    $imgFilename = is_string($imgPath) ? basename($imgPath) : basename((string)$imgRel);
                    $imgBasename = strtoupper((string)pathinfo($imgFilename, PATHINFO_FILENAME));
                    if ($imgBasename !== '' && $imgBasename === $targetCode) {
                        $colorImage = $resolveImageUrl((string)$imgRel, $productSlug);
                        break;
                    }
                }
            }

            $colorChoice['imageUrl'] = $colorImage;
        }
        unset($colorChoice);

        $interiorChoices = [];
        foreach ($rawInteriorColors as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? $row['name'] ?? '')));
            $name = trim((string)($row['name'] ?? ''));
            $hex = trim((string)($row['hex'] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $interiorChoices[] = [
                'code' => $code,
                'name' => $name,
                'hex' => $hex,
            ];
        }

        if ($colorCode === '' && !empty($colorChoices)) {
            $colorCode = (string)$colorChoices[0]['code'];
            $colorName = (string)$colorChoices[0]['name'];
        }

        $mainImage = BASE_URL . 'public/images/products/default.jpg';
        if (!empty($images) && is_string($images[0])) {
            $resolved = $resolveImageUrl((string)$images[0], $productSlug);
            if ($resolved !== '') {
                $mainImage = $resolved;
            }
        }

        foreach ($colorChoices as $colorChoice) {
            if (strtoupper((string)$colorChoice['code']) !== strtoupper($colorCode)) {
                continue;
            }
            if (!empty($colorChoice['imageUrl'])) {
                $mainImage = (string)$colorChoice['imageUrl'];
            }
            break;
        }

        $powerText = trim((string)($specs['power'] ?? ($specs['motor_power'] ?? 'N/A')));
        $rangeText = trim((string)($specs['range'] ?? ($specs['driving_range'] ?? 'N/A')));
        $wheelbaseText = trim((string)($specs['wheelbase'] ?? ($specs['wheel_base'] ?? 'N/A')));

        $switchProductsUi = [];
        foreach ($switchProducts as $item) {
            if (!is_array($item)) {
                continue;
            }

            $modelKey = strtolower(trim((string)($item['model_key'] ?? '')));
            if ($modelKey === '') {
                $fallbackSeed = (string)($item['slug'] ?? $item['name'] ?? '');
                $modelKey = $extractProductFamily($fallbackSeed);
            }

            $switchProductsUi[] = [
                'id' => (int)($item['id'] ?? 0),
                'name' => (string)($item['name'] ?? 'VinFast'),
                'slug' => (string)($item['slug'] ?? ''),
                'modelKey' => $modelKey,
                'priceRaw' => (float)($item['price'] ?? 0),
                'priceText' => number_format((float)($item['price'] ?? 0), 0, ',', '.') . ' VNĐ',
                'imageUrl' => $resolveImageUrl((string)($item['image'] ?? ''), (string)($item['slug'] ?? '')),
                'isCurrent' => (bool)($item['is_current'] ?? false),
            ];
        }

        if (empty($switchProductsUi)) {
            $switchProductsUi[] = [
                'id' => $productId,
                'name' => $productName,
                'slug' => (string)($product['slug'] ?? ''),
                'modelKey' => $extractProductFamily((string)($product['slug'] ?? $productName)),
                'priceRaw' => (float)($product['price'] ?? 0),
                'priceText' => $priceText . ' VNĐ',
                'imageUrl' => $mainImage,
                'isCurrent' => true,
            ];
        }

        $modelGroups = [];
        foreach ($switchProductsUi as $item) {
            $key = trim((string)($item['modelKey'] ?? ''));
            if ($key === '') {
                $key = 'product-' . (int)($item['id'] ?? 0);
            }

            if (!isset($modelGroups[$key])) {
                $modelGroups[$key] = [
                    'key' => $key,
                    'representative' => $item,
                    'items' => [],
                ];
            }

            $modelGroups[$key]['items'][] = $item;
            if (!empty($item['isCurrent']) || (int)($item['id'] ?? 0) === $productId) {
                $modelGroups[$key]['representative'] = $item;
            }
        }

        $currentModelKey = '';
        foreach ($modelGroups as $groupKey => $group) {
            foreach ($group['items'] as $modelItem) {
                if (!empty($modelItem['isCurrent']) || (int)($modelItem['id'] ?? 0) === $productId) {
                    $currentModelKey = (string)$groupKey;
                    break 2;
                }
            }
        }

        if ($currentModelKey === '' && !empty($modelGroups)) {
            $currentModelKey = (string)array_key_first($modelGroups);
        }

        $displayModels = [];
        if ($currentModelKey !== '' && isset($modelGroups[$currentModelKey])) {
            $displayModels[] = $modelGroups[$currentModelKey]['representative'];
        }
        foreach ($modelGroups as $groupKey => $group) {
            if ($groupKey === $currentModelKey) {
                continue;
            }
            $displayModels[] = $group['representative'];
        }

        $variantSwitchChoices = [];
        if ($currentModelKey !== '' && isset($modelGroups[$currentModelKey])) {
            foreach ($modelGroups[$currentModelKey]['items'] as $modelItem) {
                $variantSwitchChoices[] = [
                    'productId' => (int)($modelItem['id'] ?? 0),
                    'name' => (string)($modelItem['name'] ?? 'VinFast'),
                    'price' => (float)($modelItem['priceRaw'] ?? 0),
                    'priceText' => (string)($modelItem['priceText'] ?? '0 VNĐ'),
                    'isCurrent' => !empty($modelItem['isCurrent']) || (int)($modelItem['id'] ?? 0) === $productId,
                ];
            }
        }

        $formData = array_merge([
            'owner_type' => 'ca-nhan',
            'full_name' => '',
            'phone' => '',
            'cccd' => '',
            'email' => '',
            'province' => '',
            'showroom' => '',
            'salesperson' => '',
            'voucher' => '',
            'pay_method' => 'card-intl',
            'agree_terms' => '',
            'variant_name' => '',
            'interior_code' => '',
            'step' => 1,
        ], $formData);

        $selectedVariantName = trim((string)$formData['variant_name']);
        if ($selectedVariantName === '') {
            $selectedVariantName = (string)($variantChoices[0]['name'] ?? $productName);
        }

        $depositAmount = max(0, (int)($specs['deposit_amount'] ?? 15000000));
        $depositNonRefundable = !empty($specs['deposit_non_refundable']) ? 1 : 0;
        $selectedColorSurcharge = max(0, (int)($selectedColor['surcharge'] ?? 0));

        $selectedVariant = $variantChoices[0];
        foreach ($variantChoices as $variantRow) {
            if (strcasecmp((string)($variantRow['name'] ?? ''), $selectedVariantName) === 0) {
                $selectedVariant = $variantRow;
                break;
            }
        }

        $selectedInteriorCode = strtoupper(trim((string)$formData['interior_code']));
        $selectedInteriorName = '';
        if (empty($interiorChoices)) {
            $selectedInteriorCode = '';
        } elseif ($selectedInteriorCode === '') {
            $selectedInteriorCode = (string)$interiorChoices[0]['code'];
        }

        foreach ($interiorChoices as $interiorRow) {
            if (strtoupper((string)$interiorRow['code']) !== $selectedInteriorCode) {
                continue;
            }
            $selectedInteriorName = (string)$interiorRow['name'];
            break;
        }

        if ($selectedInteriorName === '' && !empty($interiorChoices)) {
            $selectedInteriorName = (string)$interiorChoices[0]['name'];
        }

        $currentStep = max(1, min(3, (int)$formData['step']));
        $checkoutFormId = 'vfCheckoutForm';

        return compact(
            'productId',
            'productName',
            'productSlug',
            'priceText',
            'productFamily',
            'colorCode',
            'colorName',
            'specs',
            'images',
            'rawExteriorColors',
            'rawInteriorColors',
            'rawVariants',
            'variantChoices',
            'colorChoices',
            'interiorChoices',
            'mainImage',
            'powerText',
            'rangeText',
            'wheelbaseText',
            'switchProductsUi',
            'modelGroups',
            'currentModelKey',
            'displayModels',
            'variantSwitchChoices',
            'formData',
            'selectedVariantName',
            'selectedVariant',
            'depositAmount',
            'depositNonRefundable',
            'selectedColorSurcharge',
            'selectedInteriorCode',
            'selectedInteriorName',
            'currentStep',
            'checkoutFormId',
            'provinces',
            'showrooms',
            'selectedColor'
        );
    }
}
