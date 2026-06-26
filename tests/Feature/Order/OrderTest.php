<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        return [$user, $token];
    }

    public function test_can_create_order(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 2, 'unit_price' => 9.99],
                ['product_name' => 'Gadget', 'quantity' => 1, 'unit_price' => 19.99],
            ],
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => ['id', 'customer_name', 'customer_email', 'status', 'total', 'items'],
            ]);

        $this->assertDatabaseHas('orders', ['total' => 39.97]);
    }

    public function test_can_list_orders(): void
    {
        [, $token] = $this->actingAsUser();
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_filter_orders_by_status(): void
    {
        [, $token] = $this->actingAsUser();
        Order::factory()->confirmed()->create();
        Order::factory()->cancelled()->create();

        $response = $this->getJson('/api/orders?status=confirmed', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_show_order(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->confirmed()->create();

        $response = $this->getJson("/api/orders/{$order->id}", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'customer_name', 'status', 'items']]);
    }

    public function test_can_update_order(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->create();

        $response = $this->putJson("/api/orders/{$order->id}", [
            'items' => [
                ['product_name' => 'New Item', 'quantity' => 3, 'unit_price' => 5.00],
            ],
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('order.total', '15.00');
    }

    public function test_can_delete_order_without_payments(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->create();

        $response = $this->deleteJson("/api/orders/{$order->id}", [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $this->assertModelMissing($order);
    }

    public function test_cannot_delete_order_with_payments(): void
    {
        [, $token] = $this->actingAsUser();
        $order = Order::factory()->create();
        Payment::factory()->for($order)->create();

        $response = $this->deleteJson("/api/orders/{$order->id}", [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(409);
        $this->assertModelExists($order);
    }

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_cannot_create_order_without_authentication(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [['product_name' => 'Item', 'quantity' => 1, 'unit_price' => 10.00]],
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_create_order_with_empty_items(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->postJson('/api/orders', ['items' => []], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    public function test_cannot_create_order_without_items_field(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->postJson('/api/orders', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    public function test_cannot_show_nonexistent_order(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->getJson('/api/orders/99999', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_nonexistent_order(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->deleteJson('/api/orders/99999', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(404);
    }
}
