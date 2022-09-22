{if $addons.social_buttons.yandex_enable == "Y" && $provider_settings.yandex.data}
{hook name="social_buttons:yandex"}
{/hook}
{$provider_settings.yandex.data nofilter}
<script
    class="cm-ajax-force"
    {$script_attrs|render_tag_attrs nofilter}
>
    (function(_, $) {
        var event_suffix = 'yandex';

        _.deferred_scripts.push({
            src: '//yastatic.net/share2/share.js',
            event_suffix: event_suffix
        });

        $.ceEvent('one', 'ce.lazy_script_load_' + event_suffix, function () {
            $('.ya-share2').attr('id', 'ya-share2');
             if (typeof (Ya) != 'undefined') {
                var share = Ya.share2('ya-share2');
            }
        });
    }(Tygh, Tygh.$));
</script>

{/if}
