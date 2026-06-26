<?php

namespace Tests\Feature\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\User;
use App\Payment\Gateways\CreditCardGateway;
use App\Payment\Gateways\PayPalGateway;
use App\Payment\PaymentGatewayManager;
use App\Payment\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $user = User::factory()->create();

        return Order::factory()->for($user)->create();
    }

    public function test_credit_card_gateway_supports_credit_card_only(): void
    {
        $gateway = new CreditCardGateway([]);

        $this->assertTrue($gateway->supports('credit_card'));
        $this->assertFalse($gateway->supports('paypal'));
    }

    public function test_credit_card_gateway_process_returns_success(): void
    {
        $gateway = new CreditCardGateway([]);
        $order = $this->makeOrder();

        $result = $gateway->process($order, []);

        $this->assertInstanceOf(PaymentResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals('credit_card', $result->gatewayUsed);
        $this->assertStringStartsWith('cc_', $result->transactionId);
    }

    public function test_paypal_gateway_supports_paypal_only(): void
    {
        $gateway = new PayPalGateway([]);

        $this->assertTrue($gateway->supports('paypal'));
        $this->assertFalse($gateway->supports('credit_card'));
    }

    public function test_paypal_gateway_process_returns_success(): void
    {
        $gateway = new PayPalGateway([]);
        $order = $this->makeOrder();

        $result = $gateway->process($order, []);

        $this->assertInstanceOf(PaymentResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals('paypal', $result->gatewayUsed);
        $this->assertStringStartsWith('pp_', $result->transactionId);
    }

    public function test_gateway_manager_returns_failed_result(): void
    {
        $manager = app(PaymentGatewayManager::class);

        $failingGateway = new class implements PaymentGatewayInterface
        {
            public function process(Order $order, array $data): PaymentResult
            {
                return new PaymentResult(
                    success: false,
                    transactionId: 'failed_txn',
                    message: 'Insufficient funds.',
                    gatewayUsed: 'failing_gateway',
                );
            }

            public function supports(string $method): bool
            {
                return $method === 'failing_gateway';
            }
        };

        $manager->register('failing_gateway', $failingGateway);

        $result = $manager->process($this->makeOrder(), 'failing_gateway', []);

        $this->assertFalse($result->success);
        $this->assertEquals('Insufficient funds.', $result->message);
    }
}
