{if $addons.social_buttons.pinterest_enable == "Y" && $provider_settings.pinterest.data}
{hook name="social_buttons:pinterest"}
{/hook}
<span class="pinterest__wrapper">
    <a href="//pinterest.com/pin/create/button/?url={$provider_settings.pinterest.data.url nofilter}&amp;media={$provider_settings.pinterest.data.media nofilter}&amp;description={$provider_settings.pinterest.data.description nofilter}" {$provider_settings.pinterest.data.params nofilter}><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_{$addons.social_buttons.pinterest_size}.png" alt="Pinterest"></a>
</span>
<script
    {$script_attrs|render_tag_attrs nofilter}
>
    (function(_, $) {
        var event_suffix = 'pinterest';

        _.deferred_scripts.push({
            src: '//assets.pinterest.com/js/pinit.js',
            event_suffix: event_suffix
        });

        $.ceEvent('on', 'ce.lazy_script_load_' + event_suffix, function () {
            if (window.PinUtils) {
                window.PinUtils.build();
                return;
            }
        });
    }(Tygh, Tygh.$));
</script>
{/if}
