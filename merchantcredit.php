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
use MerchantCredit\Config\MerchantCreditConfig;
use AdminController;
use Configuration;
use Tools;

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

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submit_merchantcredit')) {
            $newLimit = (float) Tools::getValue(MerchantCreditConfig::KEY_DEFAULT_LIMIT);
            if ($newLimit < 0) {
                $output .= $this->displayError(
                    $this->trans('Default credit limit must be 0 or greater.', [], 'Modules.Merchantcredit.Admin')
                );
            } else {
                Configuration::updateValue(MerchantCreditConfig::KEY_DEFAULT_LIMIT, $newLimit);
                $output .= $this->displayConfirmation(
                    $this->trans('Settings saved.', [], 'Modules.Merchantcredit.Admin')
                );
            }
        }

        $this->context->smarty->assign([
            'merchantcredit_default_limit'     => Configuration::get(MerchantCreditConfig::KEY_DEFAULT_LIMIT, null, null, null, MerchantCreditConfig::DEFAULT_LIMIT),
            'merchantcredit_key_default_limit' => MerchantCreditConfig::KEY_DEFAULT_LIMIT,
            'merchantcredit_submit_name'       => 'submit_merchantcredit',
            'merchantcredit_action_url'        => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $output . $this->fetch('module:merchantcredit/views/templates/admin/config.tpl');
    }

    public function install(): bool
    {
        include __DIR__ . '/sql/install.php';

        Configuration::updateValue(MerchantCreditConfig::KEY_DEFAULT_LIMIT, MerchantCreditConfig::DEFAULT_LIMIT);

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

        Configuration::deleteByName(MerchantCreditConfig::KEY_DEFAULT_LIMIT);

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
        (new CustomerFormBuilderModifierHook($this))->handle($params);
    }

    public function hookActionAfterUpdateCustomerFormHandler(array $params): void
    {
        (new AfterUpdateCustomerFormHandlerHook($this))->handle($params);
    }

    public function hookActionAfterCreateCustomerFormHandler(array $params): void
    {
        (new AfterCreateCustomerFormHandlerHook($this))->handle($params);
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }
}
