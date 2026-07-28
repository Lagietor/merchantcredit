<?php

namespace MerchantCredit\Hook;

use MerchantCredit\Entity\MerchantCreditCustomer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerFormBuilderModifierHook
{
    const FIELD_NAME = 'merchantcredit_limit';

    public function handle(array $params): void
    {
        $formBuilder = $params['form_builder'] ?? null;
        if (!$formBuilder instanceof FormBuilderInterface) {
            return;
        }

        $idCustomer = isset($params['id']) ? (int) $params['id'] : null;

        $formBuilder->add(self::FIELD_NAME, NumberType::class, [
            'label' => 'Merchant credit limit',
            'required' => false,
            'scale' => 2,
            'help' => 'Maximum amount this customer can spend using the merchant credit payment method.',
            'constraints' => [
                new NotBlank(),
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
