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

use Tygh\Addons\PaypalCommercePlatform\Enum\CaptureStatus;
use Tygh\Enum\OrderStatuses;
use Tygh\Registry;

return [
    CaptureStatus::COMPLETED          => OrderStatuses::PAID,
    CaptureStatus::PENDING            => OrderStatuses::OPEN,
    CaptureStatus::DECLINED           => OrderStatuses::CANCELED,
    CaptureStatus::PARTIALLY_REFUNDED => Registry::get('addons.paypal_commerce_platform.rma_refunded_order_status'),
    CaptureStatus::REFUNDED           => Registry::get('addons.paypal_commerce_platform.rma_refunded_order_status'),
];
