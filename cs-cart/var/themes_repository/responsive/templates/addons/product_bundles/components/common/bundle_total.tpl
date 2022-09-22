{*
    Import
    ---
    $bundle
*}

{if $bundle.total_price}

    <div class="ty-product-bundles-bundle-form__total">
        <div class="ty-product-bundles-bundle-form__total-inner">
            {* Total title *}
            <strong class="ty-product-bundles-bundle-form__total-title ty-subheader">
                {__("product_bundles.price_for_all")}
            </strong>

            {* If auth user or show price for anonymous shopping *}
            {if $auth.user_id || $settings.Checkout.allow_anonymous_shopping !== "hide_price_and_add_to_cart"}

                {* Prices *}
                <div class="ty-product-bundles-bundle-form__price"
                    id="product_bundles_bundle_total_price_{$bundle.bundle_id}">
                    
                    <div class="ty-product-bundles-bundle-form__price-discount">
                        <span class="ty-product-bundles-bundle-form__price-discount-title">
                            {__("product_bundles.order_discount")}:
                        </span>
                        <span class="ty-product-bundles-bundle-form__price-discount-price">
                            {include file="common/price.tpl"
                                value=($bundle.total_price - $bundle.discounted_price)
                            }
                        </span>
                    </div>
                    <span class="ty-product-bundles-bundle-form__price-old ty-strike">
                        {include file="common/price.tpl"
                            value=$bundle.total_price
                        }
                    </span>
                    <span class="ty-product-bundles-bundle-form__price-new">
                        {include file="common/price.tpl"
                            value=$bundle.discounted_price
                        }
                    </span>
                <!--product_bundles_bundle_total_price_{$bundle.bundle_id}--></div>

                {* Add all to cart button *}
                {if $auth.user_id || $settings.Checkout.allow_anonymous_shopping !== "hide_add_to_cart_button"}
                    <div class="ty-product-bundles-bundle-form__submit" id="wrap_chain_button_{$bundle.bundle_id}">
                        {include file="buttons/button.tpl"
                            but_text=__("product_bundles.add_all_to_cart")
                            but_id="bundle_button_`$bundle.bundle_id`"
                            but_meta="ty-btn__secondary cm-dialog-closer"
                            but_name="dispatch[checkout.add]"
                            but_role="action"
                        }
                    </div>
                {/if}
            {else}
                {* Sign in to view price button *}
                <p>{__("product_bundles.sign_in_to_view_price")}</p>
            {/if}
        </div>
    </div>
{/if}
