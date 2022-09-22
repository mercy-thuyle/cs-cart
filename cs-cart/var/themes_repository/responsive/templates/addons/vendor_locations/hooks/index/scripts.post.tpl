<script>
    (function (_, $) {
        _.vendor_locations = {
            api_key: '{$addons.vendor_locations.api_key|escape:"javascript"}',
            storage_key_geolocation: '{$smarty.const.VENDOR_LOCATIONS_STORAGE_KEY_GEO_LOCATION|escape:"javascript"}',
            storage_key_locality: '{$smarty.const.VENDOR_LOCATIONS_STORAGE_KEY_LOCALITY|escape:"javascript"}',
            customer_geolocation: '{$vendor_locations_geolocation|to_json|escape:"javascript" nofilter}',
            customer_locality: '{$vendor_locations_locality|to_json|escape:"javascript" nofilter}',
            "vendor_locations.google_maps_cookie_title": '{__("vendor_locations.google_maps_cookie_title", ['skip_live_editor' => true])|escape:"javascript"}',
            "vendor_locations.google_maps_cookie_description": '{__("vendor_locations.google_maps_cookie_description", ['skip_live_editor' => true])|escape:"javascript"}',
        };
    })(Tygh, Tygh.$);
</script>
{script src="js/addons/vendor_locations/geocomplete.js" cookie-name="google_maps"}
{script src="js/addons/vendor_locations/geolocate.js" cookie-name="google_maps"}
{script src="js/addons/vendor_locations/geomap.js" cookie-name="google_maps"}
{script src="js/addons/vendor_locations/func.js" cookie-name="google_maps"}
