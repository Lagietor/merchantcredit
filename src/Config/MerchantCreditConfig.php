<?php

namespace MerchantCredit\Config;

use Configuration;

class MerchantCreditConfig
{
    const KEY_DEFAULT_LIMIT = 'MERCHANTCREDIT_DEFAULT_LIMIT';
    const DEFAULT_LIMIT = 50.0;

    public static function getDefaultLimit(): float
    {
        return (float) Configuration::get(self::KEY_DEFAULT_LIMIT, null, null, null, self::DEFAULT_LIMIT);
    }
}
