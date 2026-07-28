<?php

namespace MerchantCredit\Hook;

use MerchantCredit\Entity\MerchantCreditCustomer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerFormHooks
{
    const FIELD_NAME = 'merchantcredit_limit';

    public function addFieldToFormBuilder(FormBuilderInterface $formBuilder): void
    {
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
    }

    public function fillFieldValue(FormBuilderInterface $formBuilder, ?int $idCustomer): void
    {
        if ($idCustomer === null || $idCustomer <= 0) {
            $formBuilder->get(self::FIELD_NAME)->setData(MerchantCreditCustomer::DEFAULT_CREDIT_LIMIT);

            return;
        }

        $model = MerchantCreditCustomer::ensureForCustomer($idCustomer);
        $formBuilder->get(self::FIELD_NAME)->setData((float) $model->credit_limit);
    }

    public function saveFieldValue(int $idCustomer): void
    {
        $submitted = \Tools::getValue('customer');
        if (!is_array($submitted) || !isset($submitted[self::FIELD_NAME])) {
            return;
        }

        $newLimit = (float) $submitted[self::FIELD_NAME];
        if ($newLimit < 0) {
            return;
        }

        $model = MerchantCreditCustomer::ensureForCustomer($idCustomer);
        $model->credit_limit = $newLimit;
        $model->save();
    }
}
