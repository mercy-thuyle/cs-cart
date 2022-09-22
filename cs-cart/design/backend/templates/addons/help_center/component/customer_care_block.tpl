{$service_url="`$config.helpdesk.url|rtrim:'/'`/"}
{if !$auth.helpdesk_user_id || $settings.Upgrade_center.license_number}
<div class="help-center__block">
    <div class="help-center__block-customer-care help-center__block-customer-care--hidden">
        {if $auth.helpdesk_user_id
            && $settings.Upgrade_center.license_number
            && $customer_care_data.subscription.status === "ObjectStatuses::ACTIVE"|enum
            && $customer_care_data.tickets
            && $customer_care_data.tickets|count > 0
        }
            {* 1. Basic state: tickets list *}
            <div class="help-center__block-header flex-vertical-centered flex-space-between flex-wrap">
                <div class="help-center__block-title">{__("help_center.customer_care")}</div>
                {include
                    file="buttons/button.tpl"
                    but_text=__("help_center.customer_care.submit_ticket")
                    but_role="action"
                    but_href="`$service_url`index.php?dispatch=communication.tickets&submit_ticket=Y"
                    but_target="_blank"
                    but_icon="icon-plus"
                }
            </div>
            <div class="help-center__block-content">
                {foreach $customer_care_data.tickets as $ticket_data}
                    <div class="help-center__block-link">
                        <div class="flex-vertical-centered flex-space-between">
                            <a class="wrap-word"
                               href="{$service_url}index.php?dispatch=communication.messages&ticket_id={$ticket_data.ticket_id}"
                               target="_blank"
                            >
                                {$ticket_data.ticket}
                            </a>
                            {if $ticket_data.status === "resolved"}
                                <span class="label label-success">{__("help_center.customer_care.status.resolved")}</span>
                            {else}
                                <span class="label label-warning">{__("help_center.customer_care.status.open")}</span>
                            {/if}
                        </div>
                    </div>
                {/foreach}
                <div class="help-center__block-link">
                    <a class="help-center__block-link-link"
                       href="{$service_url}index.php?dispatch=communication.tickets"
                       target="_blank"
                    >
                        <span class="wrap-word">
                            {__("help_center.customer_care.view_all_tickets")}...
                        </span>
                    </a>
                </div>
            </div>
        {elseif $auth.helpdesk_user_id
            && $settings.Upgrade_center.license_number
            && $customer_care_data.subscription.status === "ObjectStatuses::ACTIVE"|enum
        }
            {* 2. No open tickets state *}
            <div class="help-center__block-content no-items">
                <p>
                    {include_ext file="common/icon.tpl"
                        class="icon-comments-alt cs-icon-xlarge cm-opacity"
                    }
                </p>
                <strong class="text-larger">{__("help_center.customer_care")}</strong>
                <p class="muted">{__("help_center.customer_care.no_support_tickets")}</p>
                <p class="spaced-child">
                    {include
                        file="buttons/button.tpl"
                        but_text=__("help_center.customer_care.submit_ticket")
                        but_role="action"
                        but_href="`$service_url`index.php?dispatch=communication.tickets&submit_ticket=Y"
                        but_target="_blank"
                    }
                    <a href="{$service_url}index.php?dispatch=communication.tickets"
                       target="_blank"
                    >
                        {__("help_center.customer_care.all_tickets")}
                    </a>
                </p>
            </div>
        {elseif $auth.helpdesk_user_id
            && $settings.Upgrade_center.license_number
        }
            {* 3. Subscription has expired state *}
            <div class="help-center__block-content no-items">
                <p>
                    {include_ext file="common/icon.tpl"
                        class="icon-comments-alt cs-icon-xlarge cm-opacity"
                    }
                </p>
                <strong class="text-larger">{__("help_center.customer_care.service_unavailable")}</strong>
                <p>{__("help_center.customer_care.service_unavailable_description")}</p>
                <p class="spaced-child">
                    {include
                        file="buttons/button.tpl"
                        but_text=__("help_center.customer_care.prolong_subscription")
                        but_role="action"
                        but_href="`$service_url`customer-care-subscription.html"
                        but_target="_blank"
                    }
                </p>
            </div>
        {elseif !$auth.helpdesk_user_id}
            {* 4. No auth state *}
            <div class="help-center__block-content well text-center">
                <p>
                    {include_ext file="common/icon.tpl"
                        class="icon-comments-alt cs-icon-xlarge cm-opacity"
                    }
                </p>
                <p>
                    <strong class="text-larger">{__("help_center.customer_care")}</strong>
                </p>
                <p>{__("help_center.customer_care.sign_in_text") nofilter}</p>
                {include file="buttons/helpdesk.tpl"}
            </div>
        {/if}
    </div>
</div>
{/if}
