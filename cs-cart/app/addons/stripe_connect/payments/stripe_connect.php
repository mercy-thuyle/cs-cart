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

use Tygh\Addons\StripeConnect\ServiceProvider;
use Tygh\Enum\OrderStatuses;

/** @var array $order_info */
/** @var array $processor_data */

if (!empty($order_info['payment_info']['stripe_connect.payment_intent_id'])) {
    $processor = ServiceProvider::getProcessorFactory()->getByPaymentId(
        $order_info['payment_id'],
        $processor_data['processor_params']
    );

    $pp_response = $processor->chargeWith3DSecure($order_info);

    Tygh::$app['session']['stripe_connect_order_id'] = $order_info['order_id'];
} elseif (!empty($order_info['payment_info']['stripe_connect.token'])) {
    $processor = ServiceProvider::getProcessorFactory()->getByPaymentId(
        $order_info['payment_id'],
        $processor_data['processor_params']
    );

    // phpcs:ignore
    $pp_response = $processor->chargeWithout3DSecure($order_info);
} elseif (defined('AJAX_REQUEST')) {
    //phpcs:ignore
    $pp_response = [
        'order_status' => OrderStatuses::OPEN,
    ];
}
