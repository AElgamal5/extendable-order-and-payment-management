<?php

namespace Tests\Feature\Payment;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        return [$user, $token];
    }

    public function test_can_process_payment_on_confirmed_order(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->confirmed()->create();

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'credit_card',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'payment' => ['id', 'order_id', 'method', 'status', 'transaction_id'],
            ]);
    }

    public function test_cannot_process_payment_on_non_confirmed_order(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'credit_card',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_process_payment_with_invalid_method(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->confirmed()->create();

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'bitcoin',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_payments(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->getJson('/api/payments', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_show_payment(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->confirmed()->create();

        $storeResponse = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'credit_card',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $paymentId = $storeResponse->json('payment.id');

        $response = $this->getJson("/api/payments/{$paymentId}", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'order_id', 'method', 'status']]);
    }
}
