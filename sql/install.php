<?php

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'merchantcredit_customer` (
    `id_merchantcredit_customer` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_customer` INT(11) UNSIGNED NOT NULL,
    `credit_limit` DECIMAL(20, 6) NOT NULL DEFAULT \'50.000000\',
    `credit_used` DECIMAL(20, 6) NOT NULL DEFAULT \'0.000000\',
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_merchantcredit_customer`),
    UNIQUE KEY `idx_id_customer` (`id_customer`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
