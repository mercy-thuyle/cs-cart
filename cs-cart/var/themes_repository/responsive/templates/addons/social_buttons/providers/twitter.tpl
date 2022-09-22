{if $addons.social_buttons.twitter_enable == "Y" && $provider_settings.twitter.data}
{hook name="social_buttons:twitter"}
{/hook}

<a href="https://twitter.com/share" class="twitter-share-button" {$provider_settings.twitter.data nofilter}>Tweet</a>
<script
    class="cm-ajax-force"
    {$script_attrs|render_tag_attrs nofilter}
>
(function(_, $) {
    var event_suffix = 'twitter';

    _.deferred_scripts.push({
        src: '//platform.twitter.com/widgets.js', 
        event_suffix: event_suffix
    });

    $.ceEvent('on', 'ce.lazy_script_load_' + event_suffix, function () {
        if($(".twitter-share-button").length > 0){
            if (typeof (twttr) != 'undefined') {
                twttr.widgets.load();
            }
        }
    });
}(Tygh, Tygh.$));
</script>
{/if}
