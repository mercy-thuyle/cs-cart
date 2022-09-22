<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Settings;

defined('BOOTSTRAP') or die('Access denied');

/**
 * The `settings_actions` handler.
 *
 * Action performed:
 *     - Update taxes into debt payout product data.
 *
 * @param string                       $value     New settings value.
 * @param array<string, string>|string $old_value Old settings value.
 * @param Tygh\Settings                $settings  Settings instance.
 *
 * @return void
 */
function fn_settings_actions_addons_vendor_debt_payout_vendor_taxes($value, $old_value, Settings $settings)
{
    $product_id = fn_vendor_debt_payout_get_payout_product();
    /** @var array<string, string> $new_taxes */
    $new_taxes = $settings->unserializeValue($value);
    if (!is_array($new_taxes)) {
        return;
    }
    fn_update_product(
        [
            'product_id' => $product_id,
            'tax_ids'    => array_keys($new_taxes),
        ],
        $product_id
    );
}
