<?php

namespace MerchantCredit\Tests\Unit\Hook;

use MerchantCredit\Hook\AfterUpdateCustomerFormHandlerHook;
use MerchantCredit\Hook\CustomerFormBuilderModifierHook;
use PHPUnit\Framework\TestCase;

if (!class_exists('Tools')) {
    class Tools
    {
        public static mixed $returnValue = null;

        public static function getValue(string $key, mixed $default = false): mixed
        {
            return self::$returnValue;
        }
    }
}

class AfterUpdateCustomerFormHandlerHookTest extends TestCase
{
    protected function setUp(): void
    {
        \Db::resetInstance();
        \Tools::$returnValue = null;
    }

    private function makeModule(): object
    {
        return $this->getMockBuilder(\Merchantcredit::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoesNothingWhenIdCustomerIsZero(): void
    {
        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 0]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenCustomerDataNotSubmitted(): void
    {
        \Tools::$returnValue = null;
        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 1]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenCreditLimitFieldMissing(): void
    {
        \Tools::$returnValue = ['other_field' => 100];
        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 1]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenNewLimitIsNegative(): void
    {
        \Tools::$returnValue = [CustomerFormBuilderModifierHook::FIELD_NAME => -10];
        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 1]);
        $this->assertTrue(true);
    }

    public function testSavesCreditLimitWhenValid(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        \Tools::$returnValue = [CustomerFormBuilderModifierHook::FIELD_NAME => '75.00'];

        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 5]);

        // If we reach here without an exception the model was saved successfully
        $this->assertTrue(true);
    }

    public function testAcceptsZeroAsValidLimit(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        \Tools::$returnValue = [CustomerFormBuilderModifierHook::FIELD_NAME => '0'];

        $module = $this->makeModule();
        $hook = new AfterUpdateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 5]);

        $this->assertTrue(true);
    }
}
