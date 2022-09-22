{if (
        $product.price|floatval
        || $product.zero_price_action === "ProductZeroPriceActions::ALLOW_ADD_TO_CART"|enum
        || ($hide_add_to_cart_button === "YesNo::YES"|enum && $product.zero_price_action === "ProductZeroPriceActions::ASK_TO_ENTER_PRICE"|enum)
    )
    && $product.units_in_product !== "0.000" && $product.unit_name
}
    {$price_per_unit = $product.price|floatval/$product.units_in_product*$product.show_price_per_x_units}

        <div class="ty-price-per-unit {if !$product.price|floatval && !$product.zero_price_action} hidden{/if}">
            {$product.units_in_product|rtrim:"0"|rtrim:"."} {$product.unit_name} ({include file="common/price.tpl" value=$price_per_unit} {__("shipping_per")} {if $product.show_price_per_x_units !== "1.000"}{$product.show_price_per_x_units|rtrim:"0"|rtrim:"."} {/if}{$product.unit_name})
        </div>
{/if}
