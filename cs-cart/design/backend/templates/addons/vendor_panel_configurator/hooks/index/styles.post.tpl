{style src="addons/vendor_panel_configurator/styles.less"}

{if $smarty.const.ACCOUNT_TYPE === "vendor"}
    {include file="addons/vendor_panel_configurator/config.tpl"}

    {style src="addons/vendor_panel_configurator/simple_vendor_panel/index.less"}
    {capture name="styles"}
        @mainColor: {$mainColor};
        @menuSidebarColor: {$menuSidebarColor};
        @menuSidebarBg: {$menuSidebarBg};
    {/capture}
    {style content=$smarty.capture.styles type="less"}
{/if}
