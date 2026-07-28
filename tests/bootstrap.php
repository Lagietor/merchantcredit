<?php

require_once __DIR__ . '/vendor/autoload.php';

// PS constants
if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '8.0.0');
}
if (!defined('_DB_PREFIX_')) {
    define('_DB_PREFIX_', 'ps_');
}

// ObjectModel stub — includes TYPE_* constants used in $definition arrays
if (!class_exists('ObjectModel')) {
    abstract class ObjectModel
    {
        const TYPE_INT   = 1;
        const TYPE_BOOL  = 2;
        const TYPE_STRING = 3;
        const TYPE_FLOAT = 4;
        const TYPE_DATE  = 5;
        const TYPE_HTML  = 6;
        const TYPE_NOTHING = 7;
        const TYPE_SQL   = 8;

        public $id;

        public function save(): bool
        {
            return true;
        }
    }
}

// Db stub — tests override getInstance() via setInstance()
if (!class_exists('Db')) {
    class Db
    {
        private static ?self $instance = null;

        public static function getInstance(): static
        {
            if (self::$instance === null) {
                self::$instance = new static();
            }
            return self::$instance;
        }

        public static function setInstance(self $instance): void
        {
            self::$instance = $instance;
        }

        public static function resetInstance(): void
        {
            self::$instance = null;
        }

        public function getValue(mixed $query): mixed
        {
            return null;
        }

        public function execute(string $sql): bool
        {
            return false;
        }
    }
}

// DbQuery stub
if (!class_exists('DbQuery')) {
    class DbQuery
    {
        public function select(string $fields): static { return $this; }
        public function from(string $table, ?string $alias = null): static { return $this; }
        public function where(string $where): static { return $this; }
    }
}

// pSQL stub
if (!function_exists('pSQL')) {
    function pSQL(string $string, bool $htmlOK = false): string
    {
        return addslashes($string);
    }
}

// Tools stub — tests set Tools::$returnValue to control getValue() output
if (!class_exists('Tools')) {
    class Tools
    {
        public static mixed $returnValue = null;

        public static function getValue(string $key, mixed $default = false): mixed
        {
            return self::$returnValue;
        }

        public static function redirect(string $url): void {}
    }
}

// Order stub
if (!class_exists('Order')) {
    class Order
    {
        public string $module = '';
        public int $id_customer = 0;
        public float $total_paid = 0.0;
    }
}

// FrontController stub
if (!class_exists('FrontController')) {
    class FrontController
    {
        public array $errors = [];
        public ?string $redirectedTo = null;

        public function redirectWithNotifications(string $url): void
        {
            $this->redirectedTo = $url;
        }
    }
}

// PaymentModule stub
if (!class_exists('PaymentModule')) {
    abstract class PaymentModule
    {
        public string $name = '';
        public bool $active = true;

        public function getContext(): object
        {
            return new stdClass();
        }

        public function getTranslator(): object
        {
            return new class {
                public function trans(string $id, array $params = [], string $domain = ''): string
                {
                    return $id;
                }
            };
        }
    }
}

// Merchantcredit stub — used as mock base in hook tests
if (!class_exists('Merchantcredit')) {
    class Merchantcredit extends PaymentModule {}
}

// Configuration stub — tests set values via Configuration::set()
if (!class_exists('Configuration')) {
    class Configuration
    {
        private static array $values = [];

        public static function set(string $key, mixed $value): void
        {
            self::$values[$key] = $value;
        }

        public static function get(string $key, mixed $idLang = null, mixed $idShopGroup = null, mixed $idShop = null, mixed $default = false): mixed
        {
            return self::$values[$key] ?? $default;
        }

        public static function updateValue(string $key, mixed $value): bool
        {
            self::$values[$key] = $value;
            return true;
        }

        public static function deleteByName(string $key): bool
        {
            unset(self::$values[$key]);
            return true;
        }

        public static function reset(): void
        {
            self::$values = [];
        }
    }
}
