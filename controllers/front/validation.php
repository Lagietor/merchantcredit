<?php

use MerchantCredit\Entity\MerchantCreditCustomer;

/**
 * @property Merchantcredit $module
 */
class MerchantcreditValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess(): void
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === $this->module->name) {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            exit($this->module->getTranslator()->trans(
                'This payment method is not available.',
                [],
                'Modules.Merchantcredit.Shop'
            ));
        }

        $customer = new Customer((int) $cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $idCustomer = (int) $customer->id;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        if (MerchantCreditCustomer::getRemaining($idCustomer) < $total) {
            $this->errors[] = $this->module->getTranslator()->trans(
                'Insufficient merchant credit for this order.',
                [],
                'Modules.Merchantcredit.Shop'
            );
            $this->redirectWithNotifications('index.php?controller=order&step=1');

            return;
        }

        if (!MerchantCreditCustomer::consume($idCustomer, $total)) {
            $this->errors[] = $this->module->getTranslator()->trans(
                'Insufficient merchant credit for this order.',
                [],
                'Modules.Merchantcredit.Shop'
            );
            $this->redirectWithNotifications('index.php?controller=order&step=1');

            return;
        }

        try {
            $this->module->validateOrder(
                (int) $cart->id,
                (int) Configuration::get('PS_OS_PAYMENT'),
                $total,
                $this->module->displayName,
                null,
                [],
                (int) $this->context->currency->id,
                false,
                $customer->secure_key
            );
        } catch (Exception $e) {
            MerchantCreditCustomer::refund($idCustomer, $total);
            throw $e;
        }

        Tools::redirect(
            'index.php?controller=order-confirmation'
            . '&id_cart=' . (int) $cart->id
            . '&id_module=' . (int) $this->module->id
            . '&id_order=' . (int) $this->module->currentOrder
            . '&key=' . $customer->secure_key
        );
    }
}
