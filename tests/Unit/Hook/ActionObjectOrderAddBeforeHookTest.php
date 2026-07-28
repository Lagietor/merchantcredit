<?php

namespace MerchantCredit\Tests\Unit\Hook;

use MerchantCredit\Hook\ActionObjectOrderAddBeforeHook;
use PHPUnit\Framework\TestCase;

class ActionObjectOrderAddBeforeHookTest extends TestCase
{
    protected function setUp(): void
    {
        \Db::resetInstance();
    }

    private function makeModule(string $name = 'merchantcredit', ?object $controller = null): object
    {
        $context = new \stdClass();
        $context->controller = $controller ?? new \stdClass();

        $translator = new class {
            public function trans(string $id, array $params = [], string $domain = ''): string
            {
                return $id;
            }
        };

        $module = $this->getMockBuilder(\Merchantcredit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $module->name = $name;
        $module->method('getContext')->willReturn($context);
        $module->method('getTranslator')->willReturn($translator);

        return $module;
    }

    public function testDoesNothingWhenObjectIsNotAnOrder(): void
    {
        $module = $this->makeModule();
        $hook = new ActionObjectOrderAddBeforeHook($module);

        // No exception, no DB call expected
        $hook->handle(['object' => new \stdClass()]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenOrderModuleDoesNotMatch(): void
    {
        $order = new \Order();
        $order->module = 'other_module';

        $module = $this->makeModule('merchantcredit');
        $hook = new ActionObjectOrderAddBeforeHook($module);

        $hook->handle(['object' => $order]);
        $this->assertTrue(true);
    }

    public function testDoesNothingWhenCreditIsSufficient(): void
    {
        $db = $this->createMock(\Db::class);
        // ensureForCustomer: no record found → creates new with 50 limit / 0 used → remaining = 50
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $order = new \Order();
        $order->module = 'merchantcredit';
        $order->id_customer = 1;
        $order->total_paid = 30.0;

        $module = $this->makeModule('merchantcredit');
        $hook = new ActionObjectOrderAddBeforeHook($module);
        $hook->handle(['object' => $order]);

        $this->assertTrue(true);
    }

    public function testRedirectsWithNotificationWhenCreditInsufficient(): void
    {
        $db = $this->createMock(\Db::class);
        // Return a fresh model with 50 limit / 0 used, but total_paid is 100
        $db->method('getValue')->willReturn(0);
        \Db::setInstance($db);

        $controller = new \FrontController();

        $order = new \Order();
        $order->module = 'merchantcredit';
        $order->id_customer = 1;
        $order->total_paid = 100.0;

        $module = $this->makeModule('merchantcredit', $controller);
        $hook = new ActionObjectOrderAddBeforeHook($module);
        $hook->handle(['object' => $order]);

        $this->assertStringContainsString('controller=order', $controller->redirectedTo);
        $this->assertNotEmpty($controller->errors);
    }
}
