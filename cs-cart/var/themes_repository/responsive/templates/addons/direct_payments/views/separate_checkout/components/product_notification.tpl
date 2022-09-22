{capture name="buttons"}
    <div class="ty-float-left">
        {include file="buttons/button.tpl" but_text=__("continue_shopping") but_meta="ty-btn__secondary cm-notification-close"}
    </div>
    {if $settings.Checkout.checkout_redirect != "Y"}
        <div class="ty-float-right">
            {include
                file="buttons/proceed_to_checkout.tpl"
                but_href="checkout.checkout&vendor_id={$vendor_id}"
                but_text=__("checkout")
                but_meta="ty-btn__primary cm-notification-close"
            }
        </div>
    {/if}
{/capture}
{capture name="info"}
    <div class="clearfix"></div>
    <hr class="ty-product-notification__divider" />

    <div class="ty-product-notification__total-info clearfix">
        <div class="ty-product-notification__amount ty-float-left"> {__("items_in_cart", [$amount])}</div>
        <div class="ty-product-notification__subtotal ty-float-right">
            {__("cart_subtotal")} {include file="common/price.tpl" value=$display_subtotal}
        </div>
    </div>
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}