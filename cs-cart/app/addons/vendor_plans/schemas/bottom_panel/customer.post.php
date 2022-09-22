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

$schema['companies.apply_for_vendor'] = [
    'from' => [
        'dispatch' => 'companies.apply_for_vendor',
    ],
    'to_admin' => [
        'dispatch' => 'vendor_plans.manage'
    ]
];

$schema['companies.vendor_plans'] = [
    'from' => [
        'dispatch' => 'companies.vendor_plans',
    ],
    'to_admin' => [
        'dispatch'  => 'vendor_plans.manage',
    ]
];

return $schema;