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

use Tygh\Addons\VendorDataPremoderation\ServiceProvider;
use Tygh\Addons\VendorDataPremoderation\State;
use Tygh\Enum\Addons\VendorDataPremoderation\PremoderationStatuses;
use Tygh\Enum\Addons\VendorDataPremoderation\ProductStatuses;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\ProfileFieldTypes;
use Tygh\Enum\ReceiverSearchMethods;
use Tygh\Enum\UserTypes;
use Tygh\Enum\VendorStatuses;
use Tygh\Enum\YesNo;
use Tygh\Notifications\Receivers\SearchCondition;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

require_once __DIR__ . '/hooks.functions.php';

/**
 * Changes the approval status of products.
 *
 * @param int|int[] $product_ids Product identifiers
 * @param string    $status      Approval status
 * @param string    $reason      Moderation reason
 *
 * @return bool
 *
 * @deprecated since 4.11.1. Use specific approval methods instead.
 *
 * @see        fn_vendor_data_premoderation_approve_products
 * @see        fn_vendor_data_premoderation_disapprove_products
 * @see        fn_vendor_data_premoderation_request_approval_for_products
 */
function fn_change_approval_status($product_ids, $status, $reason = '')
{
    $product_ids = (array) $product_ids;

    /**
     * Changes the values in the array of product identifiers before the approval status of those products is changed.
     *
     * @param int[]  $product_ids Product identifiers
     * @param string $status      Approval status
     *
     * @deprecated since 4.11.1. Use the following hooks instead:
     *             vendor_data_premoderation_approve_products_pre,
     *             vendor_data_premoderation_disapprove_products,
     *             vendor_data_premoderation_request_approval_for_products
     */
    fn_set_hook('change_approval_status_pre', $product_ids, $status);

    switch ($status) {
        case PremoderationStatuses::APPROVED:
            return fn_vendor_data_premoderation_approve_products($product_ids, true);
        case PremoderationStatuses::DISAPPROVED:
            return fn_vendor_data_premoderation_disapprove_products($product_ids, true, $reason);
        default:
            return fn_vendor_data_premoderation_request_approval_for_products($product_ids, true);
    }
}

/**
 * Approves products.
 *
 * @param int[] $product_ids    Approved product IDs
 * @param bool  $update_product Whether to update the product data.
 *                              When set to false, only the premoderation data will be updated
 *
 * @return bool
 */
function fn_vendor_data_premoderation_approve_products(array $product_ids, $update_product = true)
{
    $status = PremoderationStatuses::APPROVED;

    /**
     * Changes the values in the array of product identifiers before the approval status of those products is changed.
     *
     * @param int[]  $product_ids Product identifiers
     * @param string $status      Approval status
     *
     * @deprecated since 4.11.1. Use the following hooks instead:
     *             vendor_data_premoderation_approve_products_pre,
     *             vendor_data_premoderation_disapprove_products,
     *             vendor_data_premoderation_request_approval_for_products
     */
    fn_set_hook('change_approval_status_pre', $product_ids, $status);

    /**
     * Executes before approving products, allows you to change the list of approved product IDs.
     *
     * @param int[] $product_ids    Approved product IDs
     * @param bool  $update_product Whether to update the product data.
     *                              When set to false, only the premoderation data will be updated
     */
    fn_set_hook('vendor_data_premoderation_approve_products_pre', $product_ids, $update_product);

    $current_product_statuses = fn_vendor_data_premoderation_get_current_product_statuses($product_ids);
    $updated_product_ids = [];

    foreach ($current_product_statuses as $product_id => $status) {
        if ($status !== ProductStatuses::REQUIRES_APPROVAL
            && $status !== ProductStatuses::DISAPPROVED
        ) {
            continue;
        }

        $updated_product_ids[] = $product_id;

        $current_premoderation = fn_vendor_data_premoderation_get_premoderation($product_id);
        $current_premoderation = reset($current_premoderation);

        $original_status = $current_premoderation
            ? $current_premoderation['original_status']
            : ProductStatuses::ACTIVE;

        fn_vendor_data_premoderation_update_premoderation($product_id, $original_status, '', '');

        if ($update_product) {
            $params = [
                'id'                                => $product_id,
                'id_name'                           => 'product_id',
                'status'                            => $original_status,
                'table'                             => 'products',
                'is_status_updated_during_approval' => true,
            ];
            fn_tools_update_status($params);
        }
    }

    if ($updated_product_ids) {
        /** @var \Tygh\Notifications\EventDispatcher $event_dispatcher */
        $event_dispatcher = Tygh::$app['event.dispatcher'];

        $products_companies = fn_get_company_ids_by_product_ids($updated_product_ids);
        foreach ($products_companies as $company_id => $company_product_ids) {
            $event_dispatcher->dispatch('vendor_data_premoderation.product_status.approved', [
                'company_id'    => $company_id,
                'to_company_id' => $company_id,
                'product_ids'   => $company_product_ids,
            ]);
        }
    }

    return true;
}

/**
 * Disapproves products.
 *
 * @param int[]  $product_ids    Disapproved product IDs
 * @param bool   $update_product Whether to update the product data.
 *                               When set to false, only the premoderation data will be updated
 * @param string $reason         Disapproval reason
 *
 * @return bool
 */
function fn_vendor_data_premoderation_disapprove_products(array $product_ids, $update_product = true, $reason = '')
{
    $status = PremoderationStatuses::DISAPPROVED;

    /**
     * Changes the values in the array of product identifiers before the approval status of those products is changed.
     *
     * @param int[]  $product_ids Product identifiers
     * @param string $status      Approval status
     *
     * @deprecated since 4.11.1. Use the following hooks instead:
     *             vendor_data_premoderation_approve_products_pre,
     *             vendor_data_premoderation_disapprove_products,
     *             vendor_data_premoderation_request_approval_for_products
     */
    fn_set_hook('change_approval_status_pre', $product_ids, $status);

    /**
     * Executes before disapproving products, allows you to change the list of disapproved product IDs.
     *
     * @param int[]  $product_ids    Disapproved product IDs
     * @param bool   $update_product Whether to update the product data.
     *                               When set to false, only the premoderation data will be updated
     * @param string $reason         Disapproval reason
     */
    fn_set_hook('vendor_data_premoderation_disapprove_products_pre', $product_ids, $update_product, $reason);

    $current_product_statuses = fn_vendor_data_premoderation_get_current_product_statuses($product_ids);
    $updated_product_ids = [];

    foreach ($current_product_statuses as $product_id => $status) {
        $original_status = $status;

        $update_premoderation = true;

        if ($status === ProductStatuses::DISAPPROVED
            || $status === ProductStatuses::REQUIRES_APPROVAL
        ) {
            $current_premoderation = fn_vendor_data_premoderation_get_premoderation([$product_id]);
            $current_premoderation = reset($current_premoderation);

            $original_status = $current_premoderation
                ? $current_premoderation['original_status']
                : ProductStatuses::ACTIVE;

            $original_reason = $current_premoderation
                ? $current_premoderation['reason']
                : '';

            $is_reason_changed = $reason !== $original_reason;
            $is_product_disapproved = $status === ProductStatuses::REQUIRES_APPROVAL;

            $update_premoderation = !$current_premoderation || $is_reason_changed || $is_product_disapproved;
        }

        if ($update_premoderation) {
            $updated_product_ids[] = $product_id;
            fn_vendor_data_premoderation_update_premoderation($product_id, $original_status, $reason);
        }

        if ($update_product) {
            db_query('UPDATE ?:products SET status = ?s WHERE product_id = ?i', ProductStatuses::DISAPPROVED, $product_id);
        }
    }

    if ($updated_product_ids) {
        /** @var \Tygh\Notifications\EventDispatcher $event_dispatcher */
        $event_dispatcher = Tygh::$app['event.dispatcher'];

        $products_companies = fn_get_company_ids_by_product_ids($updated_product_ids);
        foreach ($products_companies as $company_id => $company_product_ids) {
            $event_dispatcher->dispatch('vendor_data_premoderation.product_status.disapproved', [
                'company_id'    => $company_id,
                'to_company_id' => $company_id,
                'product_ids'   => $company_product_ids,
                'reason'        => $reason
            ]);
        }
    }

    return true;
}

/**
 * Requests approval for products.
 *
 * @param int[] $product_ids         Pending product IDs
 * @param bool  $update_product      Whether to update the product data.
 *                                   When set to false, only the premoderation data will be updated
 * @param bool  $save_products_state Whether to save products state
 *
 * @return bool
 */
function fn_vendor_data_premoderation_request_approval_for_products(array $product_ids, $update_product = true, $save_products_state = false)
{
    $status = PremoderationStatuses::PENDING;

    /**
     * Changes the values in the array of product identifiers before the approval status of those products is changed.
     *
     * @param int[]  $product_ids Product identifiers
     * @param string $status      Approval status
     *
     * @deprecated since 4.11.1. Use the following hooks instead:
     *             vendor_data_premoderation_approve_products_pre,
     *             vendor_data_premoderation_disapprove_products,
     *             vendor_data_premoderation_request_approval_for_products
     */
    fn_set_hook('change_approval_status_pre', $product_ids, $status);

    /**
     * Executes before requesting products approval, allows you to change the list of moderated product IDs.
     *
     * @param int[] $product_ids     Pending product IDs
     * @param bool  $update_product  Whether to update the product data.
     *                               When set to false, only the premoderation data will be updated
     */
    fn_set_hook('vendor_data_premoderation_request_approval_for_products_pre', $product_ids, $update_product);

    $current_product_statuses = fn_vendor_data_premoderation_get_current_product_statuses($product_ids);

    foreach ($current_product_statuses as $product_id => $status) {
        $original_status = $status;
        $reason = '';
        $product_state = null;

        if ($save_products_state) {
            $product_state = serialize(fn_vendor_data_premoderation_get_product_state($product_id)->toArray());
        }

        if ($status === ProductStatuses::REQUIRES_APPROVAL ||
            $status === ProductStatuses::DISAPPROVED
        ) {
            $current_premoderation = fn_vendor_data_premoderation_get_premoderation([$product_id]);
            $current_premoderation = reset($current_premoderation);

            $original_status = $current_premoderation
                ? $current_premoderation['original_status']
                : ProductStatuses::ACTIVE;

            $reason = $current_premoderation
                ? $current_premoderation['reason']
                : '';

            $product_state = !empty($current_premoderation['initial_product_state'])
                ? $current_premoderation['initial_product_state']
                : $product_state;
        }

        fn_vendor_data_premoderation_update_premoderation($product_id, $original_status, $reason, $product_state);

        if ($update_product) {
            db_query('UPDATE ?:products SET status = ?s WHERE product_id = ?i', ProductStatuses::REQUIRES_APPROVAL, $product_id);
        }
    }

    return true;
}

/**
 * Gets current product statuses.
 *
 * @param int[] $product_ids
 *
 * @return string[]
 */
function fn_vendor_data_premoderation_get_current_product_statuses(array $product_ids)
{
    $current_product_statuses = db_get_hash_single_array(
        'SELECT product_id, status FROM ?:products WHERE ?w',
        ['product_id', 'status'],
        [
            'product_id' => $product_ids,
        ]
    );

    return $current_product_statuses;
}

/**
 * Checks whether product data was changed and its validatation is required.
 *
 * @param State $initial_state
 * @param State $resulting_state
 *
 * @return bool
 */
function fn_vendor_data_premoderation_is_product_changed(State $initial_state, State $resulting_state)
{
    $detector = ServiceProvider::getProductComparator();
    $diff = $detector->compare($initial_state, $resulting_state);

    return $diff->hasChanges();
}

/**
 * Gets products premoderation details.
 *
 * @param int|int[] $product_ids
 *
 * @return array
 */
function fn_vendor_data_premoderation_get_premoderation($product_ids)
{
    $product_ids = (array) $product_ids;

    return db_get_hash_array(
        'SELECT * FROM ?:premoderation_products WHERE ?w',
        'product_id',
        [
            'product_id' => $product_ids,
        ]
    );
}

/**
 * Updates product premoderation details.
 *
 * @param int         $product_id            Product ID
 * @param string      $original_status       Original status, 1 letter
 * @param string|null $reason                Reason
 * @param string|null $initial_product_state Serialized initial product state
 */
function fn_vendor_data_premoderation_update_premoderation($product_id, $original_status, $reason = '', $initial_product_state = null)
{
    $data = [
        'product_id'        => $product_id,
        'updated_timestamp' => TIME,
    ];

    if (!empty($original_status)) {
        $data['original_status'] = $original_status;
    }

    if (isset($reason)) {
        $data['reason'] = $reason;
    }

    if (isset($initial_product_state)) {
        $data['initial_product_state'] = $initial_product_state;
        $data['initial_timestamp'] = TIME;
    }


    db_replace_into('premoderation_products', $data);
}

/**
 * Deletes product premoderation details.
 *
 * @param int|int[] $product_ids
 */
function fn_vendor_data_premoderation_delete_premoderation($product_ids)
{
    $product_ids = (array) $product_ids;

    db_query('DELETE FROM ?:premoderation_products WHERE ?w',
        [
            'product_id' => $product_ids,
        ]
    );
}

/**
 * Checks whether a product changed by a company requires prior approval.
 *
 * @param array<string, string> $company_data           Company data
 * @param bool                  $is_created             Whether a product is created
 * @param string                $current_product_status Current product status
 *
 * @return bool
 */
function fn_vendor_data_premoderation_product_requires_approval(array $company_data, $is_created = false, $current_product_status = null)
{
    if ($current_product_status === ProductStatuses::DISAPPROVED) {
        return true;
    }

    static $create_premoderation_mode = null;
    if ($create_premoderation_mode === null) {
        $create_premoderation_mode = Registry::get('addons.vendor_data_premoderation.products_prior_approval');
    }

    static $update_premoderation_mode = null;
    if ($update_premoderation_mode === null) {
        $update_premoderation_mode = Registry::get('addons.vendor_data_premoderation.products_updates_approval');
    }

    $is_updated = !$is_created;

    $is_custom_create_premoderation_required = $is_created
        && $create_premoderation_mode === 'custom'
        && YesNo::toBool($company_data['pre_moderation']);
    $is_custom_update_premoderation_required = $is_updated
        && $update_premoderation_mode === 'custom'
        && YesNo::toBool($company_data['pre_moderation_edit']);

    if ($is_created && ($create_premoderation_mode === 'all' || $is_custom_create_premoderation_required)) {
        return true;
    }

    if ($is_updated && ($update_premoderation_mode === 'all' || $is_custom_update_premoderation_required)) {
        return true;
    }

    return false;
}

/**
 * Gets product state.
 *
 * @param int $product_id
 *
 * @return \Tygh\Addons\VendorDataPremoderation\State
 */
function fn_vendor_data_premoderation_get_product_state($product_id)
{
    return ServiceProvider::getProductStateFactory()->getState($product_id);
}

/**
 * Shows warning notification if add-on disabled.
 *
 * @internal
 */
function fn_vendor_data_premoderation_display_notification_for_deleted_statuses()
{
    if (Registry::get('addons.vendor_data_premoderation.status') === ObjectStatuses::DISABLED) {
        return;
    }

    fn_set_notification('W', __('warning'), __('vendor_data_premoderation.notification_for_deleted_statuses'));
}

/**
 * Provides help text for the add-on configuration page.
 *
 * @return string
 *
 * @internal
 */
function fn_vendor_data_premoderation_get_approval_info_text()
{
    return '<div class="well well-small help-block">' . __('vendor_data_premoderation.approval_info_text') . '</div>';
}

/**
 * Compares original company data and new company data.
 *
 * @param array<string, string> $company_data      New company data
 * @param array<string, string> $orig_company_data Original company data
 *
 * @psalm-param array{
 *   fields?: array<int, string>
 * } $company_data
 *
 * @psalm-param array{
 *   fields?: array<int, string>
 * } $orig_company_data
 *
 * @return array<string, string>
 */
function fn_vendor_data_premoderation_diff_company_data(array $company_data, array $orig_company_data)
{
    $check_fields = [
        'company_description',
        'terms'
    ];

    foreach ($check_fields as $field) {
        if (!isset($company_data[$field], $orig_company_data[$field])) {
            continue;
        }
        $company_data[$field] = preg_replace('/\r\n|\r|\n/', '', $company_data[$field]);
        $orig_company_data[$field] = preg_replace('/\r\n|\r|\n/', '', $orig_company_data[$field]);
    }

    if (isset($company_data['fields'])) {
        foreach ($company_data['fields'] as $field_id => &$field_data) {
            if (fn_get_profile_field_type($field_id) === ProfileFieldTypes::FILE) {
                unset($company_data['fields'][$field_id]);
            } elseif (!empty($field_data) && fn_get_profile_field_type($field_id) === ProfileFieldTypes::DATE) {
                $field_data = fn_parse_date($field_data);
            } elseif (
                !isset($orig_company_data['fields'][$field_id])
                && (
                    empty($field_data)
                    || (fn_get_profile_field_type($field_id) === ProfileFieldTypes::CHECKBOX && $field_data === YesNo::NO)
                )
            ) {
                unset($company_data['fields'][$field_id]);
            }
        }
        unset($field_data);

        $files = fn_filter_uploaded_data('profile_fields'); // FIXME: dirty comparison
        if (!empty($files)) {
            return $files;
        }

        if (isset($orig_company_data['fields'])) {
            $result = array_diff_assoc($company_data['fields'], $orig_company_data['fields']);
            if (!empty($result)) {
                return $result;
            }
            unset($company_data['fields'], $orig_company_data['fields']);
        }
    }

    $company_data_diff = fn_array_diff_assoc_recursive($company_data, $orig_company_data);

    /**
     * Executes when determining whether company data was changed or not after diff between original and new company data is calculated,
     * allows you to modify the diff content.
     *
     * @param array<string, string> $company_data       New company data
     * @param array<string, string> $orig_company_data  Original company data
     * @param array<string, string> $company_data_diff  Diff between original and new company data
     */
    fn_set_hook('vendor_data_premoderation_diff_company_data_post', $company_data, $orig_company_data, $company_data_diff);

    return $company_data_diff;
}

function fn_vendor_data_premoderation_install()
{
    fn_update_notification_receiver_search_conditions(
        'group',
        'vendor_data_premoderation',
        UserTypes::VENDOR,
        [
            new SearchCondition(ReceiverSearchMethods::VENDOR_OWNER, ReceiverSearchMethods::VENDOR_OWNER),
        ]
    );
}

function fn_vendor_data_premoderation_uninstall()
{
    fn_update_notification_receiver_search_conditions(
        'group',
        'vendor_data_premoderation',
        UserTypes::VENDOR,
        []
    );

    fn_vendor_data_premoderation_display_notification_for_deleted_statuses();
}

/**
 * Checks if product status can be changed
 *
 * @param string $status          New product status
 * @param string $original_status Original product status
 *
 * @return bool
 */
function fn_vendor_data_premoderation_is_product_status_can_be_changed($status, $original_status)
{
    if (
        (
            in_array($original_status, [ProductStatuses::REQUIRES_APPROVAL, ProductStatuses::DISAPPROVED])
            || $status === ProductStatuses::DISAPPROVED
        )
        && $original_status !== $status
    ) {
        return false;
    }

    return true;
}

/**
 * Checks if vendor status can be changed
 *
 * @param string $status          New vendor status
 * @param string $original_status Original vendor status
 *
 * @return bool
 */
function fn_vendor_data_premoderation_is_vendor_status_can_be_changed($status, $original_status)
{
    if ($original_status === VendorStatuses::PENDING && $original_status !== $status) {
        return false;
    }

    return true;
}

/**
 * Gets status for new vendor by setting vendors_prior_approval.
 *
 * @return string
 */
function fn_vendor_data_premoderation_get_status_for_new_vendor()
{
    $vendors_prior_approval = Registry::get('addons.vendor_data_premoderation.vendors_prior_approval');

    return $vendors_prior_approval === 'none' ? VendorStatuses::ACTIVE : VendorStatuses::PENDING;
}

/**
 * Provides help text for the add-on configuration page.
 *
 * @return string
 *
 * @internal
 */
function fn_vendor_data_premoderation_get_preventing_registration_info_text()
{
    $url = fn_url('settings.manage?section_id=Vendors&highlight=apply_for_vendor');
    $info_text = __(
        'vendor_data_premoderation.preventing_registration_info_text',
        [
            '[url]' => $url,
        ]
    );

    return '<div class="well well-small help-block">' . $info_text . '</div>';
}

/**
 * Start product premoderation
 *
 * @param array<array-key, string> $product_data Product data
 * @param int                      $product_id   Product identifier
 *
 * @return void
 */
function fn_vendor_data_premoderation_start_product_premoderation(array &$product_data, $product_id)
{
    // remove previously stored initial product state
    Registry::del('vendor_data_premoderation.initial_product_state');
    // indicate that product update is performed
    Registry::set('vendor_data_premoderation.is_updating_product', true, true);

    if (!$product_id) {
        return;
    }

    $new_status = null;
    if (isset($product_data['status'])) {
        $new_status = $product_data['status'];
    }

    if (!UserTypes::isVendor(Tygh::$app['session']['auth']['user_type'])) {
        // Admin actions: disapprove products
        if ($new_status === ProductStatuses::DISAPPROVED && isset($product_data['premoderation_reason'])) {
            fn_vendor_data_premoderation_disapprove_products([$product_id], false, $product_data['premoderation_reason']);
        }
        return;
    }

    $company_data = fn_get_runtime_company_id() ? Registry::get('runtime.company_data') : fn_get_company_data(Tygh::$app['session']['auth']['company_id']);
    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval($company_data, false, $current_status);

    // get initial state only when the product is updated and updates premoderation is required by the company settings
    if ($requires_premoderation) {
        $initial_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
        Registry::set('vendor_data_premoderation.initial_product_state', $initial_product_state, true);
    }

    if (!$new_status || fn_vendor_data_premoderation_is_product_status_can_be_changed($new_status, $current_status)) {
        return;
    }

    unset($product_data['status']);
}

/**
 * End product premoderation
 *
 * @param int  $product_id Product identifier
 * @param bool $is_created Flag determines if product was created (true) or just updated (false).
 *
 * @return void
 */
function fn_vendor_data_premoderation_end_product_premoderation($product_id, $is_created)
{
    // reset product update indicator
    Registry::del('vendor_data_premoderation.is_updating_product');

    if (!$product_id) {
        return;
    }

    if (!UserTypes::isVendor(Tygh::$app['session']['auth']['user_type'])) {
        return;
    }

    $company_data = fn_get_runtime_company_id() ? Registry::get('runtime.company_data') : fn_get_company_data(Tygh::$app['session']['auth']['company_id']);

    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval($company_data, $is_created, $current_status);
    if (!$is_created && $requires_premoderation) {
        $initial_product_state = Registry::ifGet('vendor_data_premoderation.initial_product_state', null);
        $resulting_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
        $requires_premoderation = fn_vendor_data_premoderation_is_product_changed($initial_product_state, $resulting_product_state);

        if (
            $requires_premoderation
            && $current_status !== ProductStatuses::REQUIRES_APPROVAL
            && $current_status !== ProductStatuses::DISAPPROVED
        ) {
            fn_vendor_data_premoderation_update_premoderation(
                $product_id,
                '',
                null,
                serialize($initial_product_state->toArray())
            );
        }
    }

    if (!$requires_premoderation) {
        return;
    }

    fn_vendor_data_premoderation_request_approval_for_products([$product_id], true);
}
