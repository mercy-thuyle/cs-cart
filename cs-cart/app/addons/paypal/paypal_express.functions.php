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
use Tygh\Http;
use Tygh\Languages\Languages;
use Tygh\Registry;

/**
 * Completes payment initiated in PayPal.
 *
 * @param string                    $token                         Payment token
 * @param array<string, string>     $processor_data                Payment method settings
 * @param array<string, int|string> $order_info                    Order info
 * @param bool                      $is_checkout_redirect_required Whether customer should be redirected to checkout after order placement
 *
 * @psalm-param array{
 *   order_id: int
 * } $order_info
 */
function fn_paypal_complete_checkout($token, array $processor_data, array $order_info, $is_checkout_redirect_required = true)
{
    $pp_response = [
        'order_status' => 'F'
    ];

    $paypal_checkout_details = fn_paypal_get_express_checkout_details($processor_data, $token);
    if (fn_paypal_ack_success($paypal_checkout_details)) {
        $result = fn_paypal_do_express_checkout($processor_data, $paypal_checkout_details, $order_info);
        if (fn_paypal_ack_success($result)) {
            $status = $result['PAYMENTINFO_0_PAYMENTSTATUS'];
            $pp_response['transaction_id'] = $result['PAYMENTINFO_0_TRANSACTIONID'];

            $reason_text = 'Declined ';
            if ($status === 'Completed' || $status === 'Processed') {
                $pp_response['order_status'] = 'O';
                $reason_text = 'Accepted, awaiting ipn for processing ';
            } elseif ($status === 'Pending') {
                $pp_response['order_status'] = 'O';
                $reason_text = 'Pending ';
            }

            $reason_text = fn_paypal_process_add_fields($result, $reason_text);

            if (!empty($result['L_ERRORCODE0'])) {
                $reason_text .= ', ' . fn_paypal_get_error($result);
            }
        } else {
            $reason_text = fn_paypal_get_error($result);
        }
    } else {
        $reason_text = fn_paypal_get_error($paypal_checkout_details);
    }

    $pp_response['reason_text'] = $reason_text;

    if (!fn_check_payment_script($processor_data['processor_script'], $order_info['order_id'])) {
        return;
    }

    unset(Tygh::$app['session']['pp_express_details']);
    fn_finish_payment($order_info['order_id'], $pp_response);

    if (!$is_checkout_redirect_required) {
        return;
    }

    fn_order_placement_routines('route', $order_info['order_id'], false);
}

function fn_paypal_ack_success($paypal_checkout_details)
{
    return !empty($paypal_checkout_details['ACK']) && ($paypal_checkout_details['ACK'] == 'Success' || $paypal_checkout_details['ACK'] == 'SuccessWithWarning');
}

function fn_paypal_user_login($checkout_details)
{
    $s_firstname = $s_lastname = '';
    if (!empty($checkout_details['SHIPTONAME'])) {
        $name = explode(' ', $checkout_details['SHIPTONAME']);
        $s_firstname = $name[0];
        unset($name[0]);
        $s_lastname = (!empty($name[1]))? implode(' ', $name) : '';
    }

    $s_state = !empty($checkout_details['SHIPTOSTATE']) ? $checkout_details['SHIPTOSTATE'] : '';
    $s_state_codes = db_get_hash_array("SELECT ?:states.code, lang_code FROM ?:states LEFT JOIN ?:state_descriptions ON ?:state_descriptions.state_id = ?:states.state_id WHERE ?:states.country_code = ?s AND ?:state_descriptions.state = ?s", 'lang_code',  $checkout_details['SHIPTOCOUNTRYCODE'], $s_state);

    if (!empty($s_state_codes[CART_LANGUAGE])) {
        $s_state = $s_state_codes[CART_LANGUAGE]['code'];
    } elseif (!empty($s_state_codes)) {
        $s_state = array_pop($s_state_codes);
        $s_state = $s_state['code'];
    }

    $address = array (
        's_firstname' => $s_firstname,
        's_lastname' => $s_lastname,
        's_address' => $checkout_details['SHIPTOSTREET'],
        's_address_2' => !empty($checkout_details['SHIPTOSTREET2']) ? $checkout_details['SHIPTOSTREET2'] : '',
        's_city' => $checkout_details['SHIPTOCITY'],
        //'s_county' => $checkout_details['SHIPTOCOUNTRYNAME'],
        's_state' => $s_state,
        's_country' => $checkout_details['SHIPTOCOUNTRYCODE'],
        's_zipcode' => $checkout_details['SHIPTOZIP']
    );

    Tygh::$app['session']['auth'] = empty(Tygh::$app['session']['auth']) ? array() : Tygh::$app['session']['auth'];
    $auth = & Tygh::$app['session']['auth'];

    // Update profile info if customer is registered user
    if (!empty($auth['user_id']) && $auth['area'] == 'C') {
        foreach ($address as $k => $v) {
            Tygh::$app['session']['cart']['user_data'][$k] = $v;
        }

        $profile_id = !empty(Tygh::$app['session']['cart']['profile_id']) ? Tygh::$app['session']['cart']['profile_id'] : db_get_field("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND profile_type='P'", $auth['user_id']);
        db_query('UPDATE ?:user_profiles SET ?u WHERE profile_id = ?i', Tygh::$app['session']['cart']['user_data'], $profile_id);

        // Or jyst update info in the cart
    } else {
        // fill customer info
        Tygh::$app['session']['cart']['user_data'] = array(
            'firstname' => $checkout_details['FIRSTNAME'],
            'lastname' => $checkout_details['LASTNAME'],
            'email' => $checkout_details['EMAIL'],
            'company' => '',
            'phone' => '',
            'fax' => '',
        );

        foreach ($address as $k => $v) {
            Tygh::$app['session']['cart']['user_data'][$k] = $v;
            Tygh::$app['session']['cart']['user_data']['b_' . substr($k, 2)] = $v;
        }
    }

    return true;
}

function fn_paypal_build_request($processor_data, &$request, &$post_url, &$cert_file)
{
    $request = array_merge($request, array(
        'USER' => $processor_data['processor_params']['username'],
        'PWD' => $processor_data['processor_params']['password'],
        'VERSION' => 106,
    ));

    if (!empty($processor_data['processor_params']['authentication_method']) && $processor_data['processor_params']['authentication_method'] == 'signature') {
        $request['SIGNATURE'] = $processor_data['processor_params']['signature'];
        $url_prefix = '-3t';
        $cert_file = '';
    } else {
        $url_prefix = '';
        $cert_file = Registry::get('config.dir.certificates') . (isset($processor_data['processor_params']['certificate_filename']) ? $processor_data['processor_params']['certificate_filename'] : '');
    }

    if ($processor_data['processor_params']['mode'] == 'live') {
        $post_url = "https://api$url_prefix.paypal.com:443/nvp";
    } else {
        $post_url = "https://api$url_prefix.sandbox.paypal.com:443/nvp";
    }

    return true;
}

function fn_paypal_get_express_checkout_details($processor_data, $token)
{
    $request = array(
        'TOKEN' => $token,
        'METHOD' => 'GetExpressCheckoutDetails'
    );

    fn_paypal_build_request($processor_data, $request, $post_url, $cert_file);

    return fn_paypal_request($request, $post_url, $cert_file);
}

function fn_paypal_do_express_checkout($processor_data, $paypal_checkout_details, $order_info)
{
    $currency = fn_paypal_get_valid_currency($processor_data['processor_params']['currency']);
    $pp_order_id = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_info['order_id'] . '_' . $order_info['repaid']) : $order_info['order_id']);
    $total = $order_info['total'];

    if ($currency['code'] != CART_PRIMARY_CURRENCY) {
        $total = fn_format_price_by_currency($total, CART_PRIMARY_CURRENCY, $currency['code']);
    }

    $request = [
        'PAYERID'                        => $paypal_checkout_details['PAYERID'],
        'TOKEN'                          => $paypal_checkout_details['TOKEN'],
        'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
        'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency['code'],
        'PAYMENTREQUEST_0_AMT'           => $total,
        'METHOD'                         => 'DoExpressCheckoutPayment',
        'PAYMENTREQUEST_0_INVNUM'        => $pp_order_id,
        'BUTTONSOURCE'                   => 'ST_ShoppingCart_EC_US',
        'PAYMENTREQUEST_0_CUSTOM'        => $order_info['order_id'],
        'PAYMENTREQUEST_0_NOTIFYURL'     => fn_url("payment_notification.paypal_ipn", AREA, 'current')
    ];

    fn_paypal_build_request($processor_data, $request, $post_url, $cert_file);

    $order_details = fn_paypal_build_details($order_info, $processor_data);
    $request = array_merge($request, $order_details);

    $result = fn_paypal_request($request, $post_url, $cert_file);
    if (isset($result['L_ERRORCODE0']) && $result['L_ERRORCODE0'] == 10486 && (!isset($order_info['payment_info']['attempts_number']) || $order_info['payment_info']['attempts_number'] < 2)) {
        //According paypal documetation we should make two attempt and redirect customer back to paypal.
        $count = isset($order_info['payment_info']['attempts_number']) ? $order_info['payment_info']['attempts_number'] : 0;
        $count ++;
        fn_update_order_payment_info($order_info['order_id'], array('attempts_number' => $count));
        fn_paypal_payment_form($processor_data, $paypal_checkout_details['TOKEN']);
    }
    return $result;
}

/**
 * Prepares payment form for PayPal Express Checkout.
 *
 * @param array  $processor_data Order payment method info
 * @param string $token          Express Checkout token
 * @param bool   $return         Whether to return payment link info
 *
 * @return array|null If <i>$return</i> is true, an array with payment URL and request parameters will be returned.
 *                    Payment form will be created and displayed otherwise
 */
function fn_paypal_payment_form($processor_data, $token, $return = false)
{
    if ($processor_data['processor_params']['mode'] == 'live') {
        $host = 'https://www.paypal.com';
    } else {
        $host = 'https://www.sandbox.paypal.com';
    }

    $post_data = array(
        'cmd' => '_express-checkout',
        'token' => $token,
    );

    $submit_url = "$host/webscr";

    if ($return) {
        return array(
            'url' => $submit_url,
            'request' => $post_data,
        );
    }

    fn_create_payment_form($submit_url, $post_data, 'Paypal Express');
}

function fn_paypal_request($request, $post_url, $cert_file)
{
    $extra = array(
        'headers' => array(
            'Connection: close'
        ),
        'ssl_cert' => $cert_file
    );

    $response = Http::post($post_url, $request, $extra);

    if (!empty($response)) {
        parse_str($response, $result);

    } else {
        $result['ERROR'] = Http::getError();
    }

    return $result;
}

/**
 * Builds payment request details for the NVP API.
 *
 * @param array<string, int|float|string>      $data           Order info
 * @param array<string, array<string, string>> $processor_data Payment method data
 *
 * @return array<string, int|float|string>
 */
function fn_paypal_build_details(array $data, array $processor_data)
{
    $currency = fn_paypal_get_valid_currency($processor_data['processor_params']['currency']);

    if ($currency['code'] === CART_PRIMARY_CURRENCY) {
        $details = [];
        $shipping_data = [];

        if (
            !empty($processor_data['processor_params']['send_adress'])
            && YesNo::toBool($processor_data['processor_params']['send_adress'])
        ) {
            $shipping_data = fn_paypal_get_shipping_data($data);
        }
        $order_data = fn_paypal_get_order_data($data);

        return array_merge($details, $shipping_data, $order_data);
    }

    $total = fn_format_price_by_currency((float) $data['total'], CART_PRIMARY_CURRENCY, $currency['code']);

    return [
        'L_PAYMENTREQUEST_0_NAME0'     => __('total_product_cost'),
        'L_PAYMENTREQUEST_0_NUMBER0'   => 'ORDER_ID_' . (isset($data['order_id']) ? $data['order_id'] : 'NEW'),
        'L_PAYMENTREQUEST_0_DESC0'     => '',
        'L_PAYMENTREQUEST_0_QTY0'      => 1,
        'L_PAYMENTREQUEST_0_AMT0'      => $total,
        'PAYMENTREQUEST_0_ITEMAMT'     => $total,
        'PAYMENTREQUEST_0_TAXAMT'      => 0,
        'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
        'PAYMENTREQUEST_0_AMT'         => $total
    ];
}

function fn_paypal_get_shipping_data($data)
{
    $shipping_data = array();
    if (!empty($data) && !empty($data['s_address']) && !empty($data['s_city'])) {
        $shipping_data['ADDROVERRIDE'] = 1;
        //We should made these checking because $data can contain only s_state, s_country, s_zipcode if we calculate shipping on checkout page.
        $ship_name = '';
        if (!empty($data['s_firstname'])) {
            $ship_name = $data['s_firstname'];
        } elseif (!empty($data['firstname'])) {
            $ship_name = $data['firstname'];
        }
        if (!empty($data['s_lastname'])) {
            $ship_name .= ' ' . $data['s_lastname'];
        } elseif (!empty($data['lastname'])) {
            $ship_name .= ' ' . $data['lastname'];
        }
        if (!empty($ship_name)) {
            $shipping_data['PAYMENTREQUEST_0_SHIPTONAME'] = $ship_name;
        }
        if (!empty($data['s_address'])) {
            $shipping_data['PAYMENTREQUEST_0_SHIPTOSTREET'] = $data['s_address'];
        }
        if (!empty($data['s_address_2'])) {
            $shipping_data['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $data['s_address_2'];
        }
        if (!empty($data['s_city'])) {
            $shipping_data['PAYMENTREQUEST_0_SHIPTOCITY'] = $data['s_city'];
        }
        $shipping_data['PAYMENTREQUEST_0_SHIPTOSTATE'] = $data['s_state'];
        $shipping_data['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $data['s_country'];
        $shipping_data['PAYMENTREQUEST_0_SHIPTOZIP'] = $data['s_zipcode'];
    }

    return $shipping_data;
}

function fn_paypal_get_order_data($data)
{
    $order_data = [];
    $product_index = 0;

    foreach ($data['products'] as $product) {
        if ($product['price'] != 0) {
            $order_data['L_PAYMENTREQUEST_0_NAME' . $product_index] = $product['product'];
            $order_data['L_PAYMENTREQUEST_0_NUMBER' . $product_index] = $product['product_code'];
            $order_data['L_PAYMENTREQUEST_0_DESC' . $product_index] = fn_paypal_substr(fn_paypal_get_product_option($product));
            $order_data['L_PAYMENTREQUEST_0_QTY' . $product_index] = $product['amount'];
            $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] = $product['price'];

            $product_index++;
        }
    }

    if (!empty($data['payment_surcharge'])) {
        $order_data['L_PAYMENTREQUEST_0_NAME' . $product_index] = __('surcharge');
        $order_data['L_PAYMENTREQUEST_0_QTY' . $product_index] = 1;
        $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] = $data['payment_surcharge'];
        $data['subtotal'] += $data['payment_surcharge'];

        $product_index++;
    }

    $sum_taxes = fn_paypal_sum_taxes($data);
    $order_data['PAYMENTREQUEST_0_ITEMAMT'] = empty($data['subtotal_discount']) ? $data['subtotal'] : $data['subtotal'] - $data['subtotal_discount'];
    $order_data['PAYMENTREQUEST_0_TAXAMT'] = array_sum($sum_taxes);
    $order_data['PAYMENTREQUEST_0_SHIPPINGAMT'] = $data['shipping_cost'];
    $order_data['PAYMENTREQUEST_0_AMT'] = $data['total'];

    fn_paypal_apply_discount($data, $order_data, $product_index);

    fn_set_hook('paypal_express_get_order_data', $data, $order_data, $product_index);

    return $order_data;
}

function fn_paypal_substr($str, $maxlen = MAX_PAYPAL_DESCR_LENGTH)
{
    $str = fn_substr($str, 0, $maxlen);
    if (strlen($str) > $maxlen) {
        $str = fn_substr($str, 0, $maxlen / 2);
    }

    return (string) $str;
}

function fn_paypal_sum_taxes($order_info)
{
    $sum_taxes = ['P' => 0, 'S' => 0, 'O' => 0, 'PS' => 0];
    if (empty($order_info['taxes'])) {
        return $sum_taxes;
    }

    $is_surcharge_tax_included = YesNo::toBool(Registry::get('settings.Appearance.cart_prices_w_taxes'));

    foreach ($order_info['taxes'] as $tax) {
        if (YesNo::toBool($tax['price_includes_tax'])) {
            continue;
        }

        foreach ($tax['applies'] as $key => $value) {
            if (strpos($key, 'P_') !== false) {
                $sum_taxes['P'] += $value;
            } elseif (strpos($key, 'PS_') !== false) {
                if (!$is_surcharge_tax_included) {
                    $sum_taxes['PS'] += $value;
                }
            } elseif (strpos($key, 'S_') !== false) {
                $sum_taxes['S'] += $value;
            } elseif (!is_array($value)) {
                $sum_taxes['O'] += $value;
            }
        }
    }

    return $sum_taxes;
}

function fn_paypal_apply_discount($data, &$order_data, &$product_index)
{
    $discount_applied = false;
    if (!fn_is_empty(floatval($data['subtotal_discount']))) {
        $order_data['L_PAYMENTREQUEST_0_NAME' . $product_index] = __('discount');
        $order_data['L_PAYMENTREQUEST_0_QTY' . $product_index] = 1;
        $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] = -$data['subtotal_discount'];
        $discount_applied = true;
    }

    $product_index++;

    fn_set_hook('paypal_apply_discount_post', $data, $order_data, $product_index, $discount_applied);
}

function fn_paypal_get_product_option($product)
{
    $options = array();
    if (!empty($product['extra']['product_options'])) {
        foreach ($product['extra']['product_options'] as $option_id => $variant_id) {
            $option = fn_get_product_option_data($option_id, $product['product_id']);

            if (!empty($option)) {
                if ($option['option_type'] == 'F') {
                    if (!empty($product['extra']['custom_files'][$option_id])) {
                        $files = array();
                        foreach ($product['extra']['custom_files'][$option_id] as $file) {
                            $files[] = $file['name'];
                        }
                        $options[] = $option['option_name'] . ': ' . implode(',', $files);
                    }
                } elseif ($option['option_type'] == 'C') {
                    if (!empty($option['variants'][$variant_id])) {
                        $options[] = $option['option_name'];
                    }

                } elseif (empty($option['variants'])) {
                    if (!empty($variant_id)) {
                        $options[] = $option['option_name'] . ': ' . $variant_id;
                    }
                } else {
                    $options[] = $option['option_name'] . ': ' . $option['variants'][$variant_id]['variant_name'];
                }
            }
        }
    }

    return implode(", ", $options);
}

function fn_paypal_process_add_fields($result, $reason_text)
{
    $fields = array();
    //'ExchangeRate', 'GrossAmount','SettleAmount'
    $result_fields = array(
        'FEEAMT' => 'FeeAmount',
        'TAXAMT' => 'TaxAmount',
        'TRANSACTIONTYPE' => 'TransactionType',
        'PAYMENTTYPE' => 'PaymentType'
    );

    foreach ($result_fields as $field_id => $field_name) {
        $field = 'PAYMENTINFO_0_' . $field_id;
        if (isset($result[$field]) && strlen($result[$field]) > 0) {
            $fields[] = $field_name . ': ' . $result[$field];
        }
    }

    if (!empty($fields)) {
        $reason_text .= '(' . implode(', ', $fields) . ')';
    }

    return $reason_text;
}

/**
 * Extracts errors from token creation response.
 *
 * @param array  $result            Token creation response from PayPal
 * @param bool   $show_notification Whether to show error notification
 * @param string $return_type       How to format function return value:
 *                                  When set to 'text', "(error_code) error_message" string will be returned.
 *                                  When set to array, array(error_code => error_message) will be returned
 *
 * @return array|string Error message in desired format
 */
function fn_paypal_get_error($result, $show_notification = true, $return_type = 'text')
{
    $return_error = $return_type == 'text' ? '' : array();

    if (!empty($result['L_ERRORCODE0'])) {
        $error_text = $result['L_SHORTMESSAGE0'] . ': ' . $result['L_LONGMESSAGE0'];

        if ($show_notification) {
            fn_set_notification('E', __('Error') . ' ' . $result['L_ERRORCODE0'], $error_text);
        }

        if ($return_type == 'text') {
            $return_error = '(' . $result['L_ERRORCODE0'] . ') ' . $error_text;
        } else {
            $return_error[$result['L_ERRORCODE0']] = $error_text;
        }

    }

    return $return_error;
}

/**
 * Set details of Paypal Express Checkout action
 *
 * @param int    $payment_id Payment identifier
 * @param int    $order_id   Order identifier
 * @param array  $order_info Order data
 * @param array  $cart       Cart data
 * @param string $area       One-letter area code
 *
 * @return array Paypal response
 */
function fn_paypal_set_express_checkout($payment_id, $order_id = 0, $order_info = [], $cart = [], $area = AREA)
{
    $processor_data = fn_get_payment_method_data($payment_id);
    $currency = fn_paypal_get_valid_currency($processor_data['processor_params']['currency']);

    if (!empty($order_id)) {
        $return_url = fn_url("payment_notification.notify?payment=paypal_express&order_id={$order_id}", $area, 'current');
        $cancel_url = fn_url("payment_notification.cancel?payment=paypal_express&order_id={$order_id}", $area, 'current');
    } else {
        $return_url = fn_url("paypal_express.express_return?payment_id={$payment_id}", $area, 'current');
        $cancel_url = fn_url('checkout.cart', $area, 'current');
    }
    if (isset($order_info['extra']['return_url_params'])) {
        foreach ((array) $order_info['extra']['return_url_params'] as $param_name => $param_value) {
            $return_url = fn_link_attach($return_url, "{$param_name}={$param_value}");
        }
    }
    if (isset($order_info['extra']['cancel_url_params'])) {
        foreach ((array) $order_info['extra']['cancel_url_params'] as $param_name => $param_value) {
            $cancel_url = fn_link_attach($cancel_url, "{$param_name}={$param_value}");
        }
    }

    $request = [
        'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
        'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency['code'],
        'LOCALECODE'                     => Languages::getLocaleByLanguageCode(CART_LANGUAGE),
        'RETURNURL'                      => $return_url,
        'CANCELURL'                      => $cancel_url,
        'METHOD'                         => 'SetExpressCheckout',
        'SOLUTIONTYPE'                   => 'Sole',
    ];

    if (isset(Tygh::$app['session']['paypal_token'])) {
        $request['IDENTITYACCESSTOKEN'] = Tygh::$app['session']['paypal_token'];
    }

    $paypal_settings = fn_get_paypal_settings();

    if (!empty($paypal_settings) && !empty($paypal_settings['main_pair']['detailed'])) {
        $image_path_type = YesNo::toBool(Registry::get('settings.Security.secure_storefront'))
            ? 'https_image_path'
            : 'http_image_path';
        $request['LOGOIMG'] = !empty($paypal_settings['main_pair']['detailed'][$image_path_type])
            ? $paypal_settings['main_pair']['detailed'][$image_path_type]
            : $paypal_settings['main_pair']['detailed']['image_path'];
        list($request['LOGOIMG'],) = explode('?', $request['LOGOIMG']);
    }

    fn_paypal_build_request($processor_data, $request, $post_url, $cert_file);

    if (!$order_info && $order_id) {
        $order_info = fn_get_order_info($order_id);
    }
    if (!$order_info && $cart) {
        $order_info = $cart;
    }

    $order_details = fn_paypal_build_details((array) $order_info, $processor_data);
    $request = array_merge($request, $order_details);

    return fn_paypal_request($request, $post_url, $cert_file);
}
