{if $dashboard_alert}
    <div class="alert alert-block {if $is_block_alert}alert-error debt-notification{/if}">
        <div class="debt-notification__text">
            {$dashboard_alert nofilter}
        </div>
    </div>
{/if}