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

use Tygh\Registry;
use Tygh\Models\VendorPlan;

if (!fn_allowed_for('MULTIVENDOR')) {
    return $schema;
}

$schema['vendor_plan_info'] = array(
    'content' => array(
        'vendor_plans' => array(
            'type' => 'function',
            'function' => array(array('\Tygh\Models\VendorPlan', 'getAvailablePlans')),
        ),
    ),
    'templates' => array(
        'addons/vendor_plans/blocks/vendor_plan_info.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => array(
        'request_handlers' => array('plan_id'),
        'update_handlers' => array(
            'vendor_plans', 'vendor_plan_descriptions'
        )
    )
);

return $schema;
