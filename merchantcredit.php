<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MerchantCredit\Entity\MerchantCreditCustomer;
use MerchantCredit\Hook\CustomerFormHooks;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Component\Form\FormBuilderInterface;

class Merchantcredit extends PaymentModule
{
    const MODULE_ADMIN_CONTROLLER = 'AdminMerchantCreditConfiguration';

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
            && $this->registerHook('actionObjectOrderAddBefore')
            && $this->registerHook('actionCustomerFormBuilderModifier')
            && $this->registerHook('actionAfterUpdateCustomerFormHandler')
            && $this->registerHook('actionAfterCreateCustomerFormHandler')
        ;
    }

    public function uninstall(): bool
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
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
        $remaining = MerchantCreditCustomer::getRemaining($idCustomer);
        $hasEnoughCredit = $remaining >= $total;

        $locale = $this->context->getCurrentLocale();
        $currencyIso = $this->context->currency->iso_code;

        $this->smarty->assign([
            'remaining_formatted' => $locale->formatPrice($remaining, $currencyIso),
            'total_formatted'     => $locale->formatPrice($total, $currencyIso),
            'has_enough_credit'   => $hasEnoughCredit,
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

    public function hookActionObjectOrderAddBefore(array $params): void
    {
        /** @var Order $order */
        $order = $params['object'] ?? null;
        if (!$order instanceof Order || $order->module !== $this->name) {
            return;
        }

        $idCustomer = (int) $order->id_customer;
        $total = (float) $order->total_paid;

        if (MerchantCreditCustomer::getRemaining($idCustomer) >= $total) {
            return;
        }

        $controller = $this->context->controller;
        if ($controller instanceof FrontController) {
            $controller->errors[] = $this->trans(
                'Insufficient merchant credit for this order.',
                [],
                'Modules.Merchantcredit.Shop'
            );
            $controller->redirectWithNotifications('index.php?controller=order&step=1');

            return;
        }

        Tools::redirect('index.php?controller=order&step=1');
    }

    public function hookActionCustomerFormBuilderModifier(array $params): void
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'] ?? null;
        if (!$formBuilder instanceof FormBuilderInterface) {
            return;
        }

        $idCustomer = isset($params['id']) ? (int) $params['id'] : null;

        $hooks = new CustomerFormHooks();
        $hooks->addFieldToFormBuilder($formBuilder);
        $hooks->fillFieldValue($formBuilder, $idCustomer);
    }

    public function hookActionAfterUpdateCustomerFormHandler(array $params): void
    {
        $idCustomer = isset($params['id']) ? (int) $params['id'] : 0;
        if ($idCustomer <= 0) {
            return;
        }

        (new CustomerFormHooks())->saveFieldValue($idCustomer);
    }

    public function hookActionAfterCreateCustomerFormHandler(array $params): void
    {
        $idCustomer = isset($params['id']) ? (int) $params['id'] : 0;
        if ($idCustomer <= 0) {
            return;
        }

        (new CustomerFormHooks())->saveFieldValue($idCustomer);
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }
}
