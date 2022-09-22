{if $product.master_product_id || !$product.company_id}
    {$compare_product_id = $product.best_product_offer_id scope=parent}
{/if}