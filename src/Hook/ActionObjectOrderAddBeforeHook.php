<?php

namespace MerchantCredit\Hook;

use FrontController;
use MerchantCredit\Entity\MerchantCreditCustomer;
use Merchantcredit as MerchantCreditModule;
use Order;
use Tools;

class ActionObjectOrderAddBeforeHook
{
    private MerchantCreditModule $module;

    public function __construct(MerchantCreditModule $module)
    {
        $this->module = $module;
    }

    public function handle(array $params): void
    {
        /** @var Order|null $order */
        $order = $params['object'] ?? null;
        if (!$order instanceof Order || $order->module !== $this->module->name) {
            return;
        }

        $idCustomer = (int) $order->id_customer;
        $total = (float) $order->total_paid;

        if (MerchantCreditCustomer::getRemaining($idCustomer) >= $total) {
            return;
        }

        $controller = $this->module->getContext()->controller;
        if ($controller instanceof FrontController) {
            $controller->errors[] = $this->module->getTranslator()->trans(
                'Insufficient merchant credit for this order.',
                [],
                'Modules.Merchantcredit.Shop'
            );
            $controller->redirectWithNotifications('index.php?controller=order&step=1');

            return;
        }

        Tools::redirect('index.php?controller=order&step=1');
    }
}
