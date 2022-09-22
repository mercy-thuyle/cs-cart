{if "MULTIVENDOR"|fn_allowed_for && $auth.user_type === "UserTypes::VENDOR"|enum}
    {if fn_check_view_permissions("companies.update", "GET")}  
        <li><a href="{"companies.update&company_id=`$runtime.company_id`"|fn_url}">{__("vendor_panel_configurator.seller_info")}</a></li>
    {/if}
{/if}