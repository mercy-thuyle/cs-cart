{assign var="show_options_title" value=$show_options_title|default:true}

{if $product_options}
    {foreach from=$product_options item=po}
        {$option_value = $po.value|trim}
        {if isset($option_value) && $option_value|strlen}
            {assign var="has_option" value=true}
            {break}
        {/if}
    {/foreach}

    {if $has_option}
        {if !$no_block}
            <div class="ty-control-group ty-product-options__info clearfix">
            {if $show_options_title}
                <label class="ty-product-options__title">{__("options")}:</label>
            {/if}
        {/if}
            {strip}
            {foreach from=$product_options item=po name=po_opt}
                {if ($po.option_type == "ProductOptionTypes::SELECTBOX"|enum || $po.option_type == "ProductOptionTypes::RADIO_GROUP"|enum) && !$po.value}
                    {continue}
                {/if}

                {if $po.variants}
                    {assign var="var" value=$po.variants[$po.value]}
                {else}
                    {assign var="var" value=$po}
                {/if}

                {capture name="options_content"}
                    {if !$product.extra.custom_files[$po.option_id]}
                        {$var.variant_name|default:$var.value}
                    {/if}

                    {if $product.extra.custom_files[$po.option_id]}
                        {foreach from=$product.extra.custom_files[$po.option_id] item="file" name="po_files"}
                            {assign var="filename" value=$file.name|escape:url}
                            <a class="cm-no-ajax" href="{"orders.get_custom_file?order_id=`$order_info.order_id`&file=`$file.file`&filename=`$filename`"|fn_url}" title="{$file.name}">{$file.name|truncate:"40"}</a>
                            {if !$smarty.foreach.po_files.last}, {/if}
                        {/foreach}
                    {/if}

                    {if $settings.General.display_options_modifiers == "Y"}
                        {if $var.modifier|floatval}
                            &nbsp;({include file="common/modifier.tpl" mod_type=$var.modifier_type mod_value=$var.modifier display_sign=true})
                        {/if}
                    {/if}
                {/capture}

                {if $smarty.capture.options_content|trim != '&nbsp;'}
                    {hook name="options:options_content"}
                        <span class="ty-product-options clearfix">
                            <span class="ty-product-options-name">{$po.option_name}:&nbsp;</span>
                            <span class="ty-product-options-content">
                                {$smarty.capture.options_content nofilter}{if $inline_option};{/if}&nbsp;
                            </span>
                        </span>
                    {/hook}
                {/if}
                {if $fields_prefix}<input type="hidden" name="{$fields_prefix}[{$po.option_id}]" value="{$po.value}" />{/if}
            {/foreach}
            {/strip}
        {if !$no_block}
        </div>
        {/if}
    {/if}
{/if}
