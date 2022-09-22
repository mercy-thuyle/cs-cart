{if $addons.social_buttons.vkontakte_enable == "Y" && $provider_settings.vkontakte.data && $addons.social_buttons.vkontakte_appid}
{hook name="social_buttons:vkontakte"}
{/hook}
<script
    class="cm-ajax-force"
    {$script_attrs|render_tag_attrs nofilter}
>
    (function(_, $) {
        var event_suffix = 'vk';

        _.deferred_scripts.push({
            src: '//vk.com/js/api/openapi.js', 
            event_suffix: event_suffix
        });

        $.ceEvent('on', 'ce.lazy_script_load_' + event_suffix, function () {
            if (typeof (VK) != 'undefined') {
                VK.init({
                    apiId: '{$addons.social_buttons.vkontakte_appid}',
                    onlyWidgets: true
                });

                VK.Widgets.Like('vk_like', {$provider_settings.vkontakte.data nofilter});
            }
        });
    }(Tygh, Tygh.$));
</script>

<div id="vk_like"></div>
{/if}
