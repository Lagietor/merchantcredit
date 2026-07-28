<?php

namespace MerchantCredit\Hook;

use Cart;
use MerchantCredit\Entity\MerchantCreditCustomer;
use Merchantcredit as MerchantCreditModule;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PaymentOptionsHook
{
    private MerchantCreditModule $module;

    public function __construct(MerchantCreditModule $module)
    {
        $this->module = $module;
    }

    public function handle(array $params): array
    {
        if (!$this->module->active) {
            return [];
        }

        /** @var Cart|null $cart */
        $cart = $params['cart'] ?? null;
        if (!$cart instanceof Cart) {
            return [];
        }

        $idCustomer = (int) $cart->id_customer;
        if ($idCustomer === 0) {
            return [];
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $remaining = MerchantCreditCustomer::getRemaining($idCustomer);
        $hasEnoughCredit = $remaining >= $total;

        $context = $this->module->getContext();
        $locale = $context->getCurrentLocale();
        $currencyIso = $context->currency->iso_code;

        $context->smarty->assign([
            'remaining_formatted' => $locale->formatPrice($remaining, $currencyIso),
            'total_formatted'     => $locale->formatPrice($total, $currencyIso),
            'has_enough_credit'   => $hasEnoughCredit,
        ]);

        $option = new PaymentOption();
        $option->setModuleName($this->module->name)
            ->setCallToActionText(
                $this->module->getTranslator()->trans(
                    'Pay with merchant credit (%remaining% remaining)',
                    ['%remaining%' => $locale->formatPrice($remaining, $currencyIso)],
                    'Modules.Merchantcredit.Front'
                )
            )
            ->setAction($context->link->getModuleLink($this->module->name, 'validation', [], true))
            ->setAdditionalInformation(
                $this->module->fetch('module:merchantcredit/views/templates/hook/payment_infos.tpl')
            );

        return [$option];
    }
}
