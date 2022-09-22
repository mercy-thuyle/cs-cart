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

use Tygh\Enum\YesNo;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

$schema = [
    'top'     => [],
    'central' => [
        'orders'    => [
            'position' => 100,
            'items'    => [
                'view_orders' => [
                    'href'       => 'orders.manage',
                    'alt'        => 'order_management',
                    'position'   => 100,
                    'root_title' => __('orders'),
                ],
            ],
        ],
        'products'  => [
            'position' => 200,
            'title'    => __('products_menu_title'),
            'items'    => [
                'products' => [
                    'href'       => 'products.manage',
                    'alt'        => 'product_options.inventory,product_options.exceptions,products.update,products.m_update,products.add',
                    'position'   => 100,
                ],
                'features' => [
                    'href'       => 'product_features.manage',
                    'position'   => 200,
                ],
            ],
        ],
        'marketing' => [
            'position' => 300,
            'items'    => [
                'promotions' => [
                    'href'       => 'promotions.manage',
                    'position'   => 100,
                ],
            ],
        ],
        'vendors'   => [
            'position' => 400,
            'items'    => [
                'vendor_accounting' => [
                    'href'       => 'companies.balance',
                    'position'   => 100,
                ],
            ],
        ],
        'settings'  => [
            'position' => 500,
            'items'    => [
                'payment_methods'  => [
                    'href'        => 'payments.manage',
                    'position'    => 100,
                    'description' => 'vendor_panel_configurator.payment_methods.description',
                ],
                'shipping_methods' => [
                    'href'       => 'shippings.manage',
                    'position'   => 200,
                ],
                'themes'           => [
                    'href'        => 'themes.manage',
                    'position'    => 300,
                    'title'       => __('vendor_panel_configurator.theme_styles'),
                    'root_title'  => __('vendor_panel_configurator.theme_styles'),
                    'description' => 'vendor_panel_configurator.theme_styles.description',
                ],
                'layouts'          => [
                    'href'        => 'block_manager.manage',
                    'position'    => 400,
                    'title'       => __('vendor_panel_configurator.theme_layouts'),
                    'root_title'  => __('vendor_panel_configurator.theme_layouts'),
                    'description' => 'vendor_panel_configurator.theme_layouts.description',
                ],
                'files'            => [
                    'href'        => 'file_editor.manage',
                    'position'    => 500,
                ],
                'sync_data'        => [
                    'href'              => 'sync_data.manage',
                    'position'          => 600,
                    'depends_on_scheme' => true
                ]
            ],
        ],
    ],
];

if (Registry::get('settings.Vendors.allow_vendor_manage_features') !== YesNo::YES) {
    unset($schema['central']['products']['items']['features']);
}

return $schema;
