{if !$product.company_id && $show_view_offers_btn && !$is_allow_add_common_products_to_cart_list && (!$details_page || $quick_view)}
    {capture name="add_to_cart_`$obj_id`"}
        <div class="cm-reload-{$obj_prefix}{$obj_id} ty-product-offers-btn" id="view_product_offers_btn_{$obj_prefix}{$obj_id}">
            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="ty-btn__offers ty-btn__primary ty-btn__big ty-btn__add-to-cart ty-btn">{__("master_products.view_product_offers")}</a>
        <!--view_product_offers_btn_{$obj_prefix}{$obj_id}--></div>
    {/capture}
    {if $no_capture}
        {assign var="capture_name" value="add_to_cart_`$obj_id`"}
        {$smarty.capture.$capture_name nofilter}
    {/if}
{/if}