<?php

namespace MerchantCredit\Hook;

use MerchantCredit\Entity\MerchantCreditCustomer;
use Merchantcredit as MerchantCreditModule;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class CustomerFormBuilderModifierHook
{
    const FIELD_NAME = 'merchantcredit_limit';

    private MerchantCreditModule $module;

    public function __construct(MerchantCreditModule $module)
    {
        $this->module = $module;
    }

    public function handle(array $params): void
    {
        $formBuilder = $params['form_builder'] ?? null;
        if (!$formBuilder instanceof FormBuilderInterface) {
            return;
        }

        $idCustomer = isset($params['id']) ? (int) $params['id'] : null;

        $formBuilder->add(self::FIELD_NAME, NumberType::class, [
            'label' => $this->module->getTranslator()->trans('Merchant credit limit', [], 'Modules.Merchantcredit.Admin'),
            'required' => false,
            'scale' => 2,
            'help' => $this->module->getTranslator()->trans('Maximum amount this customer can spend using the merchant credit payment method.', [], 'Modules.Merchantcredit.Admin'),
            'constraints' => [
                new GreaterThanOrEqual(['value' => 0]),
            ],
            'mapped' => false,
        ]);

        $defaultValue = MerchantCreditCustomer::DEFAULT_CREDIT_LIMIT;
        if ($idCustomer !== null && $idCustomer > 0) {
            $model = MerchantCreditCustomer::ensureForCustomer($idCustomer);
            $defaultValue = (float) $model->credit_limit;
        }

        $formBuilder->get(self::FIELD_NAME)->setData($defaultValue);
    }
}
