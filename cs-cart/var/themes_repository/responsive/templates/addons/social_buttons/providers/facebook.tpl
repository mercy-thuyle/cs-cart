{hook name="social_buttons:facebook"}
{/hook}

{if $addons.social_buttons.facebook_enable == "Y" && $provider_settings.facebook.data}
<div id="fb-root"></div>

<div class="fb-like" {$provider_settings.facebook.data nofilter}></div>
<script
    class="cm-ajax-force"
    {$script_attrs|render_tag_attrs nofilter}
>
    (function(_, $) {
        var event_suffix = 'facebook';

        _.deferred_scripts.push({
            src: '//connect.facebook.net/{$addons.social_buttons.facebook_lang}/all.js#xfbml=1&appId={$addons.social_buttons.facebook_app_id}',
            event_suffix: event_suffix
        });

        $.ceEvent('on', 'ce.lazy_script_load_' + event_suffix, function () {
            if ($(".fb-like").length > 0) {
                if (typeof (FB) != 'undefined') {
                    FB.init({ status: true, cookie: true, xfbml: true });
                }
            }
        }); 
    }(Tygh, Tygh.$));
</script>
{/if}
