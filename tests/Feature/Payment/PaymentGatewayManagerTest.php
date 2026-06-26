<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    use LazilyRefreshDatabase;

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
        $this->assertEquals('paypal', $response->json('data.payment.method'));
    }

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        return [$user, $token];
    }
}
