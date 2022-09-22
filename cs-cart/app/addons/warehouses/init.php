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

use Tygh\Addons\Warehouses\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'update_product_post',
    'get_product_data_post',
    'get_products',
    'get_products_post',
    'update_product_amount',
    'update_product_amount_pre',
    'delete_product_post',
    'get_store_locations_before_select',
    'check_amount_in_stock_before_check',
    'gather_additional_products_data_pre',
    'gather_additional_products_data_post',
    'get_store_locations_for_shipping_before_select',
    'delete_destinations_post',
    'store_locator_delete_store_location_post',
    'store_locator_get_store_location_post',
    'store_locator_update_store_location_before_update',
    'store_locator_update_store_location_post',
    'render_block_pre',
    'ult_delete_company',
    'tools_change_status',
    'commerceml_product_importer_import_pre',
    'commerceml_product_convertor_convert',
    ['warehouses_manager_remove_warehouse', '', 'commerceml'],
    'ult_update_share_object',
    'ult_unshare_object',
    'get_filters_products_count_pre',
    'load_products_extra_data_post',
    'product_variations_product_repository_find_active_and_more_popular_product_id',
    'get_products_pre',
    'update_product_notifications_pre',
    'get_product_subscribers',
    'update_product_subscriber_pre',
    'send_product_notifications_before_fetch_subscriptions',
    'master_products_actualize_master_product_quantity_pre',
    'master_products_get_best_product_offer_pre',
    'delete_destinations',
    'allow_place_order_post',
    'rma_recalculate_order',
    'master_products_reindex_storefront_offers_count',
    'master_products_reindex_storefront_min_price',
    'change_order_status_before_update_product_amount'
);
