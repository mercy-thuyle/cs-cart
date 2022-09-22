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

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Registry;
use Tygh\VendorPayouts;
use Tygh\Enum\VendorStatuses;
use Tygh\Enum\YesNo;

/** @var string $mode */

if ($mode == 'index') {
    $vendor_id = Registry::get('runtime.company_id');
    if (!$vendor_id || !defined('AJAX_REQUEST')) {
        return [CONTROLLER_STATUS_OK];
    }

    fn_vendor_debt_payout_check_vendor_debt($vendor_id);

    list($debt_alert, $is_block_alert) = fn_vendor_debt_payout_get_dashboard_debt_alert($vendor_id);

    if (empty($debt_alert)) {
        return [CONTROLLER_STATUS_OK];
    }

    Tygh::$app['view']->assign([
        'dashboard_alert' => $debt_alert,
        'is_block_alert'  => $is_block_alert,
    ]);
}