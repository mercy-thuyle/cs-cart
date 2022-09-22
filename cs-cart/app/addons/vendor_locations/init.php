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

fn_register_hooks(
    'delete_company',
    'generate_filter_field_params',
    'get_companies',
    'get_company_data_post',
    'get_current_filters_post',
    'get_filters_products_count_post',
    'get_product_filter_fields',
    'get_products',
    'update_company',
    'before_dispatch',
    'storefront_rest_api_get_filter_style_by_product_field_type_post'
);
