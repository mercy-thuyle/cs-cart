{if $items|sizeof > 1}

{$is_submenu = $is_submenu|default:false}

{if $style == "graphic"}
<div class="btn-group {$class}" {if $select_container_id}id="{$select_container_id}"{/if}>
    <a class="btn btn-text dropdown-toggle " id="sw_select_{$selected_id}_wrap_{$suffix}" data-toggle="dropdown">
        {if $display_icons}
            {$icon_class=$items.$selected_id.icon_class|default:"flag flag-{$items.$selected_id.country_code|lower}"}
            {if $icon_class}
                {include_ext file="common/icon.tpl"
                    class=$icon_class
                    data=[
                        "data-ca-target-id" => "sw_select_`$selected_id`_wrap_`$suffix`"
                    ]
                }
            {/if}
        {/if}
            {$items.$selected_id.$key_name}{if $items.$selected_id.symbol}&nbsp;({$items.$selected_id.symbol nofilter})
        {/if}
        <span class="caret"></span>
    </a>
        {if $key_name == "company"}
            <input id="filter" class="input-text cm-filter" type="text" style="width: 85%"/>
        {/if}
        <ul class="dropdown-menu cm-select-list {if $display_icons}popup-icons{/if}">
            {foreach $items as $id => $item}
                <li>
                    <a name="{$id}"
                       href="{"`$link_tpl``$id`"|fn_url}"
                       {if $target_id}
                           class="cm-ajax"
                           data-ca-target-id="{$target_id}"
                       {/if}
                    >
                        {if $display_icons}
                            {$icon_class=$item.icon_class|default:"flag flag-{$item.country_code|lower}"}
                            {if $icon_class}
                                {include_ext file="common/icon.tpl" class=$icon_class}
                            {/if}
                        {/if}
                        {$item.$key_name}{if $item.symbol}&nbsp;({$item.symbol nofilter}){/if}
                    </a>
                </li>
            {/foreach}
            {if $extra}{$extra nofilter}{/if}
        </ul>
</div>
{elseif $style == "dropdown"}
    <li class="{if $is_submenu}dropdown-submenu{else}dropdown dropdown-top-menu-item{/if} {$class}" {if $select_container_id}id="{$select_container_id}"{/if}>
        <a class="{if $is_submenu}dropdown-submenu__link{else}dropdown-toggle{/if} cm-combination"
           data-toggle="dropdown"
           id="sw_select_{$selected_id}_wrap_{$suffix}"
           {if $disable_dropdown_processing}data-disable-dropdown-processing="true"{/if}
        >
            {if $plain_name}
                {$plain_name nofilter}
            {else}
                {if $key_selected}
                    {if $is_submenu}
                        {if $items.$selected_id.name}
                            {$items.$selected_id.name}
                        {elseif $items.$selected_id.description}
                            {$items.$selected_id.description}
                        {/if}
                        {if $items.$selected_id.symbol}&nbsp;({$items.$selected_id.symbol nofilter}){/if}
                    {else}
                        {if $items.$selected_id.symbol}
                            {$items.$selected_id.symbol nofilter}
                        {else}
                            {$items.$selected_id.$key_selected|upper nofilter}
                        {/if}
                    {/if}
                {else}
                    {$items.$selected_id.$key_name nofilter}
                {/if}
            {/if}

            {if !$is_submenu}
                <b class="caret"></b>
            {/if}
        </a>
        <ul class="dropdown-menu cm-select-list pull-right">
            {foreach $items as $id => $item}

                {* Link and suffix with the same identifier. Example: UI and content languages *}
                {$link = "`$link_tpl``$id`"|fn_url}
                {if $link_suffix}
                    {$link = $link|fn_link_attach:"`$link_suffix``$id`"}
                {/if}

                <li {if $id == $selected_id}class="active"{/if}>
                    <a name="{$id}" href="{$link}">
                        {if $display_icons}
                            {$icon_class=$item.icon_class|default:"flag flag-{$item.country_code|lower}"}
                            {if $icon_class}
                                {include_ext file="common/icon.tpl" class=$icon_class}
                            {/if}
                        {/if}
                        {$item.$key_name}{if $item.symbol}&nbsp;({$item.symbol nofilter}){/if}
                    </a>
                </li>
            {/foreach}
        </ul>
    </li>
{elseif $style == "field"}
<div class="cm-popup-box btn-group {if $class}{$class}{/if}">
    {if !$selected_key}
        {$selected_key = $items|key}
    {/if}
    {if !$selected_name}
        {$selected_name = $items[$selected_key]}
    {/if}
    <input type="hidden"
           name="{$select_container_name}"
           {if $select_container_id}
               id="{$select_container_id}"
           {/if}
           value="{$selected_key}"
    />
    <a id="sw_{$select_container_name}" class="dropdown-toggle btn-text btn {if $text_wrap}dropdown-toggle--text-wrap{/if}" data-toggle="dropdown">
    {$selected_name}
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu cm-select">
        {foreach $items as $key => $value}
            <li {if $selected_key == $key}class="disabled"{/if}>
                <a class="{if $selected_key == $key}active{/if} cm-select-option {if $text_wrap}dropdown--text-wrap{/if}"
                   data-ca-list-item="{$key}" title="{$value}"
                >{$value nofilter}</a></li>
        {/foreach}
    </ul>
</div>
{elseif $style === "accordion"}
{$is_active_menu_class = ($plain_name === $selected_tab) ? "active" : ""}

<li class="accordion-group  nav__header-main-menu-item {$is_active_menu_class} {$class}">
    <a href="#{$plain_name|lower}" 
        class="nav__menu-item nav__menu-item--accordion nav__header-main-menu-item {$is_active_menu_class}"
        data-toggle="collapse"  
    >
        {$plain_name nofilter}
    </a>
    <ul class="collapse nav__header-main-menu-submenu {$is_active_menu_class}{if $is_active_menu_class === 'active' } in{/if}"
        id="{$plain_name|lower}"
    >
        {foreach $items as $id => $item}
            {$is_active_submenu_class = ($id === $selected_id) ? "active" : ""}

            {$link = "`$link_tpl``$id`"|fn_url}
            {$link = ($link_suffix) ? ($link|fn_link_attach:"`$link_suffix``$id`") : $link}

            <li class="{$id} accordion-group nav__header-main-menu-subitem {$is_active_submenu_class}">
                <a class="nav__menu-subitem {$is_active_submenu_class}" name="{$id}" href="{$link}">
                    {if $display_icons}
                        {$icon_class=$item.icon_class|default:"flag flag-{$item.country_code|lower}"}
                        {if $icon_class}
                            {include_ext file="common/icon.tpl" class=$icon_class}
                        {/if}
                    {/if}
                    {$item.$key_name}{if $item.symbol}&nbsp;({$item.symbol nofilter}){/if}
                </a>
            </li>
        {/foreach}
    </ul>
</li>
{/if}

{/if}
