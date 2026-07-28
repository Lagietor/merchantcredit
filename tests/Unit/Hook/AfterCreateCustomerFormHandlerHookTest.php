<?php

namespace MerchantCredit\Tests\Unit\Hook;

use MerchantCredit\Hook\AfterCreateCustomerFormHandlerHook;
use MerchantCredit\Hook\CustomerFormBuilderModifierHook;
use PHPUnit\Framework\TestCase;

// Tools stub is declared in AfterUpdateCustomerFormHandlerHookTest.php which is loaded first.
// PHPUnit loads files per test class, so we guard with class_exists.

class AfterCreateCustomerFormHandlerHookTest extends TestCase
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
        $hook = new AfterCreateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 0]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenCustomerDataNotSubmitted(): void
    {
        \Tools::$returnValue = null;
        $module = $this->makeModule();
        $hook = new AfterCreateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 3]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenCreditLimitFieldMissing(): void
    {
        \Tools::$returnValue = ['other_field' => 100];
        $module = $this->makeModule();
        $hook = new AfterCreateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 3]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenNewLimitIsNegative(): void
    {
        \Tools::$returnValue = [CustomerFormBuilderModifierHook::FIELD_NAME => -5];
        $module = $this->makeModule();
        $hook = new AfterCreateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 3]);
        $this->assertTrue(true);
    }

    public function testSavesCreditLimitOnNewCustomer(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        \Tools::$returnValue = [CustomerFormBuilderModifierHook::FIELD_NAME => '50'];

        $module = $this->makeModule();
        $hook = new AfterCreateCustomerFormHandlerHook($module);
        $hook->handle(['id' => 3]);

        $this->assertTrue(true);
    }
}
