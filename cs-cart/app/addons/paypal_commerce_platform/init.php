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

use Tygh\Addons\PaypalCommercePlatform\ServiceProvider;
use Tygh\Tygh;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    /** @see \fn_paypal_commerce_platform_get_payments() */
    'get_payments',
    /** @see \fn_paypal_commerce_platform_rma_update_details_post() */
    'rma_update_details_post',
    /** @see \fn_paypal_commerce_platform_get_companies() */
    'get_companies',
    /** @see \fn_paypal_commerce_platform_vendor_data_premoderation_diff_company_data_post() */
    'vendor_data_premoderation_diff_company_data_post',
    /** @see \fn_paypal_commerce_platform_update_addon_status_post() */
    'update_addon_status_post',
    /** @see \fn_paypal_commerce_platform_save_log() */
    'save_log'
);
