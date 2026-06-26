<?php

namespace Tests\Feature\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\User;
use App\Payment\PaymentGatewayManager;
use App\Payment\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_and_process_gateway(): void
    {
        $manager = app(PaymentGatewayManager::class);

        $gateway = new class implements PaymentGatewayInterface
        {
            public function process(Order $order, array $data): PaymentResult
            {
                return new PaymentResult(
                    success: true,
                    transactionId: 'test_txn_123',
                    message: 'Test payment processed.',
                    gatewayUsed: 'test_gateway',
                );
            }

            public function supports(string $method): bool
            {
                return $method === 'test_gateway';
            }
        };

        $manager->register('test_gateway', $gateway);

        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $result = $manager->process($order, 'test_gateway', []);

        $this->assertTrue($result->success);
        $this->assertEquals('test_txn_123', $result->transactionId);
    }

    public function test_throws_exception_for_unsupported_method(): void
    {
        $manager = app(PaymentGatewayManager::class);
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->expectException(\InvalidArgumentException::class);

        $manager->process($order, 'nonexistent', []);
    }

    public function test_can_get_available_methods(): void
    {
        $manager = app(PaymentGatewayManager::class);

        $methods = $manager->getAvailableMethods();

        $this->assertContains('credit_card', $methods);
        $this->assertContains('paypal', $methods);
    }

    public function test_config_based_payment_processing(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->confirmed()->create();

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'paypal',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201);
        $this->assertEquals('paypal', $response->json('payment.method'));
    }

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        return [$user, $token];
    }
}
