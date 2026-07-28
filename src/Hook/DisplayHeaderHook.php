<?php

namespace MerchantCredit\Hook;

use Merchantcredit as MerchantCreditModule;

class DisplayHeaderHook
{
    private MerchantCreditModule $module;

    public function __construct(MerchantCreditModule $module)
    {
        $this->module = $module;
    }

    public function handle(): void
    {
        $controller = $this->module->getContext()->controller;
        if ($controller === null || $controller->php_self !== 'order') {
            return;
        }

        $controller->registerJavascript(
            'merchantcredit-checkout-guard',
            'modules/' . $this->module->name . '/views/js/front/checkout-guard.js',
            ['position' => 'bottom', 'priority' => 200]
        );
    }
}
