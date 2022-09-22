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

fn_register_hooks(
    'get_products',
    'get_categories_pre',
    'change_order_status',
    'check_company_permissions',
    'delete_product_pre',
    'delete_category_pre',
    'update_product_pre',
    'dispatch_before_display',
    'get_order_info',
    'catalog_mode_pre_add_to_cart',
    'promotion_apply_pre',
    'change_company_status_before_mail',
    'vendor_payouts_update_post',
    'get_companies_pre',
    'get_companies',
    'get_products_before_select',
    'login_user_post',
    'dashboard_get_vendor_activities_post',
    'dispatch_before_send_response',
    'get_product_data_pre',
    'pre_get_cart_product_data',
    'init_templater_post',
    'get_product_data_post',
    'get_checkout_settings_post',
    'get_available_company_statuses_post',
    'smarty_component_configurable_page_field_before_output',
    'smarty_component_configurable_page_section_before_output',
    'allow_place_order_post'
);
