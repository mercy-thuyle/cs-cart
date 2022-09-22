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

namespace Tygh\Addons\StripeConnect;

use Tygh\Enum\VendorPayoutApprovalStatuses;
use Tygh\Enum\VendorPayoutTypes;
use Tygh\VendorPayouts;

class PayoutsManager
{
    /** @var int $company_id */
    protected $company_id;

    /** @var \Tygh\VendorPayouts $manager */
    protected $manager;

    /**
     * PayoutsManager constructor.
     *
     * @param int $company_id Vendor ID to instantate payouts manager for
     */
    public function __construct($company_id)
    {
        $this->company_id = $company_id;
        $this->manager = VendorPayouts::instance(array('vendor' => $company_id));
    }

    /**
     * Creates withdrawal when an order is paid.
     *
     * @param float $amount   Withdrawal amount
     * @param int   $order_id Order ID
     *
     * @return int Withdrawal ID
     */
    public function createWithdrawal($amount, $order_id)
    {
        $params = array(
            'company_id'      => $this->company_id,
            'payout_type'     => VendorPayoutTypes::WITHDRAWAL,
            'approval_status' => VendorPayoutApprovalStatuses::COMPLETED,
            'payout_amount'   => $amount,
            'comments'        => __('stripe_connect.withdrawal_for_the_order', array(
                '[order_id]' => $order_id,
            )),
        );

        return $this->manager->update($params);
    }

    /**
     * Marks all pending payouts of vendor accepted.
     */
    public function acceptPayouts()
    {
        $pending_payouts = $this->getPendingPayouts();

        foreach ($pending_payouts as $payout_data) {
            $this->manager->update(array(
                'approval_status' => VendorPayoutApprovalStatuses::COMPLETED,
            ), $payout_data['payout_id']);
        }
    }

    /**
     * Gets all vendor pending payouts.
     *
     * @return array
     */
    protected function getPendingPayouts()
    {
        $pending_payouts = $this->manager->getSimple(array(
            'payout_type'     => VendorPayoutTypes::PAYOUT,
            'approval_status' => VendorPayoutApprovalStatuses::PENDING,
        ));

        return $pending_payouts;
    }

    /**
     * Gets order commission value.
     *
     * @param int $order_id Order ID
     *
     * @return float Commission value
     */
    public function getOrderFee($order_id)
    {
        $commission = $this->manager->getSimple(array(
            'order_id'    => $order_id,
            'payout_type' => VendorPayoutTypes::ORDER_PLACED,
        ));

        if (!$commission) {
            return 0;
        }

        $commission = reset($commission);

        $fee = $commission['commission_type'] == 'P' ? $commission['commission_amount'] : $commission['commission'];

        return $fee;
    }

    /**
     * Gets total amount of pending vendor payouts.
     *
     * @return float
     */
    public function getPendingPayoutsFee()
    {
        $fee = 0;

        list($balance,) = $this->manager->getBalance();

        $pending_payouts = $this->getPendingPayouts();

        if ($pending_payouts) {
            if ($balance < 0) {
                $fee = abs($balance);
            } else {
                $fee = abs(array_sum(array_column($pending_payouts, 'payout_amount')));
            }
        }

        return $fee;
    }
}
