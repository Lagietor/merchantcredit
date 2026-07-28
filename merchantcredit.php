<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MerchantCredit\Hook\ActionObjectOrderAddBeforeHook;
use MerchantCredit\Hook\AfterCreateCustomerFormHandlerHook;
use MerchantCredit\Hook\AfterUpdateCustomerFormHandlerHook;
use MerchantCredit\Hook\CustomerFormBuilderModifierHook;
use MerchantCredit\Hook\DisplayHeaderHook;
use MerchantCredit\Hook\PaymentOptionsHook;

class Merchantcredit extends PaymentModule
{
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
        include __DIR__ . '/sql/install.php';

        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionObjectOrderAddBefore')
            && $this->registerHook('actionCustomerFormBuilderModifier')
            && $this->registerHook('actionAfterUpdateCustomerFormHandler')
            && $this->registerHook('actionAfterCreateCustomerFormHandler')
        ;
    }

    public function uninstall(): bool
    {
        include __DIR__ . '/sql/uninstall.php';

        return parent::uninstall();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function hookPaymentOptions(array $params): array
    {
        return (new PaymentOptionsHook($this))->handle($params);
    }

    public function hookDisplayHeader(): void
    {
        (new DisplayHeaderHook($this))->handle();
    }

    public function hookActionObjectOrderAddBefore(array $params): void
    {
        (new ActionObjectOrderAddBeforeHook($this))->handle($params);
    }

    public function hookActionCustomerFormBuilderModifier(array $params): void
    {
        (new CustomerFormBuilderModifierHook())->handle($params);
    }

    public function hookActionAfterUpdateCustomerFormHandler(array $params): void
    {
        (new AfterUpdateCustomerFormHandlerHook())->handle($params);
    }

    public function hookActionAfterCreateCustomerFormHandler(array $params): void
    {
        (new AfterCreateCustomerFormHandlerHook())->handle($params);
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }
}
