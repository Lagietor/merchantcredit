<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MerchantCredit\Repository\CreditRepository;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Merchantcredit extends PaymentModule
{
    const MODULE_ADMIN_CONTROLLER = 'AdminMerchantCreditConfiguration';
    const DEFAULT_CREDIT_LIMIT = 50.0;

    public $controllers = ['validation'];

    public function __construct()
    {
        $this->name = 'merchantcredit';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Pablo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Merchant Credit', [], 'Modules.Merchantcredit.Admin');
        $this->description = $this->trans('Adds a "Merchant Credit" payment method allowing customers to pay using credit granted by the merchant.', [], 'Modules.Merchantcredit.Admin');

        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
        ;
    }

    public function uninstall(): bool
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function getCreditRepository(): CreditRepository
    {
        return new CreditRepository(Db::getInstance(), self::DEFAULT_CREDIT_LIMIT);
    }

    public function hookPaymentOptions(array $params): array
    {
        if (!$this->active) {
            return [];
        }

        /** @var Cart $cart */
        $cart = $params['cart'];
        $idCustomer = (int) $cart->id_customer;

        if ($idCustomer === 0) {
            return [];
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $remaining = $this->getCreditRepository()->getRemaining($idCustomer);

        if ($remaining < $total) {
            return [];
        }

        $locale = $this->context->getCurrentLocale();
        $currencyIso = $this->context->currency->iso_code;

        $this->smarty->assign([
            'remaining_formatted' => $locale->formatPrice($remaining, $currencyIso),
            'total_formatted'     => $locale->formatPrice($total, $currencyIso),
        ]);

        $option = new PaymentOption();
        $option->setModuleName($this->name)
               ->setCallToActionText(
                   $this->trans(
                       'Pay with merchant credit (%remaining% remaining)',
                       ['%remaining%' => $locale->formatPrice($remaining, $currencyIso)],
                       'Modules.Merchantcredit.Front'
                   )
               )
               ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
               ->setAdditionalInformation(
                   $this->fetch('module:merchantcredit/views/templates/hook/payment_infos.tpl')
               );

        return [$option];
    }

    public function hookPaymentReturn(): void
    {
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }
}
