<?php
$productsForModal = $productsForModal ?? [];
$old = is_array($old ?? null) ? $old : [];
$productModalJsVersion = AssetHelper::getVersion('public/js/admin/product-modal.js');
?>
<script id="productModalData" type="application/json">
    <?= json_encode($productsForModal ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>
<script>
    window.VF_PRODUCT_MODAL_OLD = <?= json_encode($old ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.VF_PRODUCT_MODAL_BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.VF_ADMIN_URL = <?= json_encode(ADMIN_URL, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Delete product modal handler
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
        const deleteForm = document.getElementById('deleteProductForm');
        const deleteProductNameSpan = document.getElementById('deleteProductName');

        document.querySelectorAll('.btn-delete-product').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.id;
                const productName = btn.dataset.name;

                deleteProductNameSpan.textContent = productName;
                deleteForm.action = window.VF_ADMIN_URL + 'products/delete/' + productId;
                deleteModal.show();
            });
        });
    });
</script>
<script src="<?= BASE_URL ?>public/js/admin/product-modal.js?v=<?= htmlspecialchars($productModalJsVersion) ?>"></script>