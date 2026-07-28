<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Merchantcredit extends PaymentModule
{
    const MODULE_ADMIN_CONTROLLER = 'AdminMerchantCreditConfiguration';
    const DEFAULT_CREDIT_LIMIT = 50.0;

    public function __construct()
    {
        $this->name = 'merchantcredit';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Pablo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Kredyt Sprzedawcy', [], 'Modules.Merchantcredit.Admin');
        $this->description = $this->trans('Dodaje metodę płatności "Kredyt Sprzedawcy" umożliwiającą zakup na kredyt przyznany przez sprzedawcę.', [], 'Modules.Merchantcredit.Admin');

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

    public function hookPaymentOptions(): array
    {
        if (!$this->active) {
            return [];
        }

        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($this->trans('Zapłać kredytem sprzedawcy', [], 'Modules.Merchantcredit.Front'))
               ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

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
