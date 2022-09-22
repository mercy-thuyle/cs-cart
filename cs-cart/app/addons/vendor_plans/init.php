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

use Tygh\Addons\VendorPlans\ServiceProvider;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'get_companies',
    'get_company_data',
    'update_company_pre',
    'update_company',
    'change_company_status_before_mail',
    'delete_category_after',
    'mve_place_order',
    'mve_place_order_post',
    'get_categories',
    'get_categories_after_sql',
    'get_category_data',
    'set_admin_notification',
    'dispatch_before_display',
    'update_product_pre',
    'mve_update_order',
    'rma_update_details_create_payout',
    'process_paypal_ipn_create_payout',
    'vendor_payouts_get_list',
    'vendor_payouts_get_income',
    'vendor_data_premoderation_diff_company_data_post',
    'get_profile_fields_post',
    'vendor_payouts_update',
    'storefront_repository_delete_post',
    'get_products_pre',
    'delete_usergroups',
    'google_sitemap_write_companies_to_sitemap_before_vendor_stores',
    'create_company_admin_post',
    'master_products_create_vendor_product_pre'
);
