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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Addons\VendorPrivileges\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'usergroup_types_get_list',
    'usergroup_types_get_map_user_type',
    'get_privileges_post',
    'check_editable_permissions_post',
    'check_can_usergroup_have_privileges_post',
    'change_company_status_before_mail',
    'update_profile',
    'get_payment_usergroups',
    'define_usergroups',
    'mve_check_permission_order_management',
    'update_company',
    'vendor_plan_update',
    'api_check_access',
    'delete_usergroups_pre',
    'update_usergroup_pre'
);
