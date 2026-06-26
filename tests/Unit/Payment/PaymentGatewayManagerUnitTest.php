<?php

namespace Tests\Unit\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Payment\PaymentGatewayManager;
use App\Payment\PaymentResult;
use PHPUnit\Framework\TestCase;

class PaymentGatewayManagerUnitTest extends TestCase
{
    public function test_can_register_and_get_available_methods(): void
    {
        $manager = new PaymentGatewayManager;

        $this->assertEmpty($manager->getAvailableMethods());

        $gateway = $this->createMock(PaymentGatewayInterface::class);
        $manager->register('test_method', $gateway);

        $this->assertEquals(['test_method'], $manager->getAvailableMethods());
    }

    public function test_can_register_multiple_methods(): void
    {
        $manager = new PaymentGatewayManager;

        $manager->register('a', $this->createMock(PaymentGatewayInterface::class));
        $manager->register('b', $this->createMock(PaymentGatewayInterface::class));
        $manager->register('c', $this->createMock(PaymentGatewayInterface::class));

        $this->assertCount(3, $manager->getAvailableMethods());
        $this->assertEquals(['a', 'b', 'c'], $manager->getAvailableMethods());
    }

    public function test_throws_exception_for_unregistered_method(): void
    {
        $manager = new PaymentGatewayManager;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment method: nonexistent.');

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->process($order, 'nonexistent', []);
    }

    public function test_registered_gateway_is_called(): void
    {
        $manager = new PaymentGatewayManager;

        $gateway = $this->createMock(PaymentGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('process')
            ->willReturn(new PaymentResult(true, 'txn_1', 'ok', 'test'));

        $manager->register('test', $gateway);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $manager->process($order, 'test', []);

        $this->assertTrue($result->success);
        $this->assertEquals('txn_1', $result->transactionId);
    }
}
