<?php

namespace Tests\Unit\Actions\Orders;

use App\Actions\Orders\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UpdateOrderStatusActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_updates_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $action = new UpdateOrderStatusAction;
        $result = $action->handle($order, OrderStatus::Confirmed);

        $this->assertTrue($result->status === OrderStatus::Confirmed);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_can_update_to_any_valid_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $action = new UpdateOrderStatusAction;

        $result = $action->handle($order, OrderStatus::Cancelled);
        $this->assertTrue($result->status === OrderStatus::Cancelled);

        $result = $action->handle($order, OrderStatus::Paid);
        $this->assertTrue($result->status === OrderStatus::Paid);
    }
}
