<?php

namespace MerchantCredit\Hook;

use MerchantCredit\Entity\MerchantCreditCustomer;
use Tools;

class AfterCreateCustomerFormHandlerHook
{
    public function handle(array $params): void
    {
        $idCustomer = isset($params['id']) ? (int) $params['id'] : 0;
        if ($idCustomer <= 0) {
            return;
        }

        $submitted = Tools::getValue('customer');
        if (!is_array($submitted) || !isset($submitted[CustomerFormBuilderModifierHook::FIELD_NAME])) {
            return;
        }

        $newLimit = (float) $submitted[CustomerFormBuilderModifierHook::FIELD_NAME];
        if ($newLimit < 0) {
            return;
        }

        $model = MerchantCreditCustomer::ensureForCustomer($idCustomer);
        $model->credit_limit = $newLimit;
        $model->save();
    }
}
