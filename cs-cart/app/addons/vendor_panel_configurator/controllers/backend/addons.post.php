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

use Tygh\Addons\VendorPanelConfigurator\ServiceProvider;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        $mode === 'update'
        && isset($_REQUEST['addon'])
        && $_REQUEST['addon'] === 'vendor_panel_configurator'
    ) {
        $settings_service = ServiceProvider::getSettingsService();
        if (isset($_REQUEST['product_fields_configuration'])) {
            $settings_service->updateProductFieldsConfiguration($_REQUEST['product_fields_configuration']);
        }
        if (isset($_REQUEST['product_tabs_configuration'])) {
            $settings_service->updateProductTabsConfiguration($_REQUEST['product_tabs_configuration']);
        }
        if (isset($_REQUEST['vendor_panel'])) {
            $settings_service->updateVendorPanelStyleConfiguration($_REQUEST['vendor_panel']);
        }
    }

    return [CONTROLLER_STATUS_OK];
}

if (
    $mode === 'update'
    && isset($_REQUEST['addon'])
    && $_REQUEST['addon'] === 'vendor_panel_configurator'
) {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    $view->assign('product_page_configuration', ServiceProvider::getSettingsService()->getProductPageConfiguration());
    $view->assign('vendor_panel', ServiceProvider::getSettingsService()->getVendorPanelStyle());

    return [CONTROLLER_STATUS_OK];
}
