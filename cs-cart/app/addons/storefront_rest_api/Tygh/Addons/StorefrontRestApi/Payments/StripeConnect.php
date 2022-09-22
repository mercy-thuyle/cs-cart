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

namespace Tygh\Addons\StorefrontRestApi\Payments;

use Tygh\Addons\StripeConnect\ServiceProvider;
use Tygh\Common\OperationResult;
use Exception;

class StripeConnect implements IDirectPayment, IConfigurablePayment
{
    /** @var array<string|array> $order_info */
    protected $order_info;

    /** @var array<string|array> $auth_info */
    protected $auth_info;

    /** @var array<string|array> $payment_info */
    protected $payment_info;

    /** @inheritDoc */
    public function pay(array $request)
    {
        $result = new OperationResult(false);

        if (!isset($request['payment_method_id']) && !isset($request['token'])) {
            $result->setErrors([
                __('api_required_field', [
                    '[field]' => 'payment_method_id',
                ]),
                __('api_required_field', [
                    '[field]' => 'token',
                ])
            ]);

            return $result;
        }

        $order_id = (int) $this->order_info['order_id'];
        $payment_id = (int) $this->payment_info['payment_id'];
        $total = (float) $this->order_info['total'];

        if (isset($request['payment_method_id'])) {
            $processor = ServiceProvider::getProcessorFactory()->getByPaymentId($payment_id);

            $confirmation_result = new OperationResult(false);
            try {
                $confirmation_result = $processor->getPaymentConfirmationDetails($request['payment_method_id'], $total);
            } catch (Exception $e) {
                $result->setErrors([
                    __('stripe_connect.payment_intent_error', [
                        '[payment_id]' => $payment_id,
                        '[error]' => $e->getMessage(),
                    ])
                ]);
            }

            if ($confirmation_result->isSuccess()) {
                fn_update_order_payment_info($order_id, [
                    'stripe_connect.payment_intent_id' => $confirmation_result->getData('payment_intent_id'),
                ]);
            } else {
                $result->setErrors([__('text_order_placed_error')]);
            }
        }

        if (isset($request['token'])) {
            fn_update_order_payment_info($order_id, [
                'stripe_connect.token' => $request['token'],
            ]);
        }

        $result->setSuccess(fn_start_payment($order_id));

        return $result;
    }

    /** @inheritDoc */
    public function setOrderInfo(array $order_info)
    {
        $this->order_info = $order_info;

        return $this;
    }

    /** @inheritDoc */
    public function setAuthInfo(array $auth_info)
    {
        $this->auth_info = $auth_info;

        return $this;
    }

    /** @inheritDoc */
    public function setPaymentInfo(array $payment_info)
    {
        $this->payment_info = $payment_info;

        return $this;
    }

    /** @inheritDoc */
    public function getProcessorParameters(array $payment_method)
    {
        return [
            'publishable_key'   => $payment_method['processor_params']['publishable_key'],
            'client_id'         => $payment_method['processor_params']['client_id'],
            'currency'          => $payment_method['processor_params']['currency'],
            'is_stripe_connect' => $payment_method['processor_params']['is_stripe_connect'],
            'payment_type'      => $payment_method['processor_params']['payment_type'],
        ];
    }
}
