<?php

namespace MerchantCredit\Repository;

use Db;
use DbQuery;

class CreditRepository
{
    private const TABLE = 'merchantcredit_customer';

    private Db $db;
    private float $defaultLimit;

    public function __construct(Db $db, float $defaultLimit)
    {
        $this->db = $db;
        $this->defaultLimit = $defaultLimit;
    }

    public function ensureRow(int $idCustomer): void
    {
        $query = new DbQuery();
        $query->select('id_merchantcredit_customer')
              ->from(self::TABLE)
              ->where('id_customer = ' . $idCustomer);

        if ($this->db->getValue($query)) {
            return;
        }

        $this->db->insert(self::TABLE, [
            'id_customer'  => $idCustomer,
            'credit_limit' => $this->defaultLimit,
            'credit_used'  => 0,
            'date_add'     => date('Y-m-d H:i:s'),
            'date_upd'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function getRemaining(int $idCustomer): float
    {
        $this->ensureRow($idCustomer);

        $query = new DbQuery();
        $query->select('credit_limit - credit_used AS remaining')
              ->from(self::TABLE)
              ->where('id_customer = ' . $idCustomer);

        return (float) $this->db->getValue($query);
    }

    public function consume(int $idCustomer, float $amount): bool
    {
        $this->ensureRow($idCustomer);

        return $this->db->execute(
            'UPDATE `' . _DB_PREFIX_ . self::TABLE . '`
             SET `credit_used` = `credit_used` + ' . (float) $amount . ',
                 `date_upd` = "' . pSQL(date('Y-m-d H:i:s')) . '"
             WHERE `id_customer` = ' . $idCustomer . '
               AND `credit_limit` - `credit_used` >= ' . (float) $amount
        );
    }
}
