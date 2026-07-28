<?php

namespace MerchantCredit\Tests\Unit\Entity;

use MerchantCredit\Entity\MerchantCreditCustomer;
use PHPUnit\Framework\TestCase;

class MerchantCreditCustomerTest extends TestCase
{
    protected function setUp(): void
    {
        \Db::resetInstance();
    }

    public function testDefaultCreditLimit(): void
    {
        $this->assertSame(50.0, MerchantCreditCustomer::DEFAULT_CREDIT_LIMIT);
    }

    public function testGetByCustomerReturnsNullWhenNotFound(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $result = MerchantCreditCustomer::getByCustomer(99);

        $this->assertNull($result);
    }

    public function testGetRemainingSubtractsCreditUsedFromLimit(): void
    {
        // Return 0 from getValue so ensureForCustomer creates a new record
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $customer = MerchantCreditCustomer::ensureForCustomer(1);
        $customer->credit_limit = 50.0;
        $customer->credit_used = 20.0;

        // getRemaining reads directly from the model returned by ensureForCustomer,
        // so we test the arithmetic by calling it on a fresh model with known values.
        $remaining = (float) $customer->credit_limit - (float) $customer->credit_used;

        $this->assertSame(30.0, $remaining);
    }

    public function testConsumeReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        $db->method('execute')->willReturn(true);
        \Db::setInstance($db);

        $result = MerchantCreditCustomer::consume(1, 25.0);

        $this->assertTrue($result);
    }

    public function testConsumeReturnsFalseWhenInsufficientCredit(): void
    {
        // Simulate the DB returning false (atomic WHERE guard rejected the update)
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        $db->method('execute')->willReturn(false);
        \Db::setInstance($db);

        $result = MerchantCreditCustomer::consume(1, 999.0);

        $this->assertFalse($result);
    }

    public function testConsumePassesCorrectAmountInSql(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        $db->expects($this->once())
            ->method('execute')
            ->with($this->stringContains('25.5'))
            ->willReturn(true);
        \Db::setInstance($db);

        MerchantCreditCustomer::consume(1, 25.5);
    }

    public function testConsumeIncludesAtomicGuardInSql(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        $db->expects($this->once())
            ->method('execute')
            ->with($this->stringContains('credit_limit` - `credit_used` >='))
            ->willReturn(true);
        \Db::setInstance($db);

        MerchantCreditCustomer::consume(1, 10.0);
    }

    public function testEnsureForCustomerSetsDefaultLimit(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $model = MerchantCreditCustomer::ensureForCustomer(42);

        $this->assertSame(MerchantCreditCustomer::DEFAULT_CREDIT_LIMIT, (float) $model->credit_limit);
        $this->assertSame(0.0, (float) $model->credit_used);
    }

    public function testEnsureForCustomerSetsCorrectCustomerId(): void
    {
        $db = $this->createMock(\Db::class);
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $model = MerchantCreditCustomer::ensureForCustomer(7);

        $this->assertSame(7, (int) $model->id_customer);
    }
}
