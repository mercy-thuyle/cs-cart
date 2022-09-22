{*
    Import
    ---
    $bundles
    $obj_id
    $show_block_header

    Global
    ---
    $show_block_header

    Local
    ---
    $bundle
*}

{if $bundles}
    {$show_block_header = $show_block_header|default:false}
    {$enable_padding = $enable_padding|default:true}

    <div class="ty-product-bundles-product-bundles {if $enable_padding}ty-product-bundles-product-bundles--padding{/if}">
        {if $show_block_header}
            <div class="ty-product-bundles-product-bundles__header">
                {__("product_bundles.product_bundles")}
            </div>
        {/if}
        <div class="ty-product-bundles-product-bundles__body">
            {foreach $bundles as $bundle}
                {include file="addons/product_bundles/components/common/bundle_form.tpl"
                    bundle=$bundle
                    obj_id=$obj_id
                }
            {/foreach}
        </div>
    </div>
{/if}
