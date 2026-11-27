<?php

/**
 * Alias cho route số nhiều `/admin/categories/*` (do `admin.php` chỉ cắt hậu tố `s` đơn lẻ).
 */
require_once __DIR__ . '/CategoryAdminController.php';

class CategoriesAdminController extends CategoryAdminController {}
