{if $a.supplier}
    <div class="addons-addon-supplier">
        <a href="{"addons.manage&supplier={$a.supplier}"|fn_url}" class="addons-addon-supplier__name row-status">
            {$a.supplier}
        </a>
        {if $a.identified || $a.is_core_addon}
            {include_ext file="common/icon.tpl"
                class="icon-ok addons-addon-supplier__identified addons-addon-supplier__identified--`$a.status|lower`"
                title=__("verified_developer")
            }
        {/if}
        {if $a.personal_review}
            {include_ext file="common/icon.tpl"
                class="icon-comment addons-addon-supplier__has-admin-review"
                title=__("addon_has_admin_review")
            }
        {/if}
    </div>
{/if}
