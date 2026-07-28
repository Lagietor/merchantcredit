<?php

namespace MerchantCredit\Hook;

use Configuration;
use MerchantCredit\Entity\MerchantCreditCustomer;
use Merchantcredit as MerchantCreditModule;
use Order;

class ActionOrderStatusUpdateHook
{
    private MerchantCreditModule $module;

    public function __construct(MerchantCreditModule $module)
    {
        $this->module = $module;
    }

    public function handle(array $params): void
    {
        $newOrderStatus = $params['newOrderStatus'] ?? null;
        $idOrder = isset($params['id_order']) ? (int) $params['id_order'] : 0;

        if ($idOrder <= 0 || $newOrderStatus === null) {
            return;
        }

        if ((int) $newOrderStatus->id !== (int) Configuration::get('PS_OS_CANCELED')) {
            return;
        }

        $order = new Order($idOrder);
        if ($order->module !== $this->module->name) {
            return;
        }

        MerchantCreditCustomer::refund(
            (int) $order->id_customer,
            (float) $order->total_paid
        );
    }
}
