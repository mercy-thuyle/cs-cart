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

use Tygh\Addons\MasterProducts\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    // general products list management
    'get_products_pre',
    'get_products',
    'get_products_post',
    'get_product_data',
    'get_product_data_post',
    'pre_add_to_cart',
    'gather_additional_products_data_params',
    'gather_additional_products_data_post',
    'load_products_extra_data_pre',
    'load_products_extra_data',
    // administration panel products management
    'company_products_check',
    'is_product_company_condition_required_post',
    // product update routine
    'update_product_post',
    'update_product_categories_pre',
    'update_product_categories_post',
    'update_product_amount_post',
    'add_global_option_link_post',
    'delete_global_option_link_post',
    'update_product_features_value_post',
    'clone_product_data',
    'variation_group_create_products_by_combinations_item',
    'variation_sync_flush_sync_events',
    'update_image_pairs',
    'delete_image_pair',
    'update_product_popularity',
    'update_product_tab_post',
    // master and vendor products data actualization on products removal/disable
    'delete_product_pre',
    'delete_product_post',
    'tools_change_status',
    // attachments module
    'attachments_check_permission_post',
    // cart
    'check_add_to_cart_post',
    // storefront rest API
    'storefront_rest_api_gather_additional_products_data_pre',
    // other
    'product_type_create_by_product',
    ['get_route', 1950],
    ['url_pre', 1450],
    ['get_attachments_pre', 500],
    ['get_discussion_pre', 500],
    ['master_products_create_vendor_product', '', 'product_variations'],
    ['master_products_actualize_master_product_quantity', '', 'product_variations'],
    'seo_get_schema_org_markup_items_post',
    'after_options_calculation',
    'discussion_is_user_eligible_to_write_review_for_product_post',
    'create_seo_name_pre',
    'change_company_status_before_mail',
    'storefront_repository_save_post',
    'settings_update_value_by_id_post',
    ['master_products_reindex_storefront_offers_count', '', 'vendor_debt_payout'],
    'product_reviews_find_pre',
    'product_reviews_is_user_eligible_to_write_product_review',
    'product_reviews_create_pre',
    'variation_group_save_group',
    'update_product_pre',
    'product_bundle_service_get_bundles',
    'pre_add_to_wishlist',
    'products_form_product_list_params_post',
    'generate_filter_field_params',
    'get_current_filters_after_variants_select_query'
);
