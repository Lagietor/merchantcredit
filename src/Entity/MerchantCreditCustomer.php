<?php

namespace MerchantCredit\Entity;

use Db;
use DbQuery;
use MerchantCredit\Config\MerchantCreditConfig;

class MerchantCreditCustomer extends \ObjectModel
{
    const DEFAULT_CREDIT_LIMIT = 50.0;

    public $id_customer;
    public $credit_limit;
    public $credit_used;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'merchantcredit_customer',
        'primary' => 'id_merchantcredit_customer',
        'multilang' => false,
        'fields' => [
            'id_customer'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'credit_limit' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'credit_used'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'date_add'     => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'     => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getByCustomer(int $idCustomer): ?self
    {
        $query = new DbQuery();
        $query->select('id_merchantcredit_customer')
            ->from(self::$definition['table'])
            ->where('id_customer = ' . $idCustomer);

        $id = (int) Db::getInstance()->getValue($query);

        return $id > 0 ? new self($id) : null;
    }

    public static function ensureForCustomer(int $idCustomer): self
    {
        $model = self::getByCustomer($idCustomer);
        if ($model !== null) {
            return $model;
        }

        $model = new self();
        $model->id_customer = $idCustomer;
        $model->credit_limit = MerchantCreditConfig::getDefaultLimit();
        $model->credit_used = 0;
        $model->save();

        return $model;
    }

    public static function getRemaining(int $idCustomer): float
    {
        $model = self::ensureForCustomer($idCustomer);

        return (float) $model->credit_limit - (float) $model->credit_used;
    }

    public static function consume(int $idCustomer, float $amount): bool
    {
        self::ensureForCustomer($idCustomer);

        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`
             SET `credit_used` = `credit_used` + ' . (float) $amount . ',
                 `date_upd` = "' . pSQL(date('Y-m-d H:i:s')) . '"
             WHERE `id_customer` = ' . $idCustomer . '
               AND `credit_limit` - `credit_used` >= ' . (float) $amount
        );
    }
}
