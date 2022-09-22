{script src="js/addons/stripe/checkout.js" cookie-name="stripe"}

<script>
    (function (_) {
        _.deferred_scripts.push({
            src: 'js/addons/stripe/views/instant_payment.js',
        });

        _.tr({
                'stripe.online_payment': '{__("stripe.online_payment")|escape:javascript}',
                "stripe.stripe_cookie_title": '{__("stripe.stripe_cookie_title", ['skip_live_editor' => true])|escape:"javascript"}',
                "stripe.stripe_cookie_description": '{__("stripe.stripe_cookie_description", ['skip_live_editor' => true])|escape:"javascript"}',
            });
    })(Tygh);
</script>
