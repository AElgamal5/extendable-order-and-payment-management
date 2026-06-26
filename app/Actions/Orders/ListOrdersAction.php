<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class ListOrdersAction
{
    public function handle(?string $status = null): LengthAwarePaginator
    {
        return Order::query()
            ->when($status && OrderStatus::tryFrom($status), fn ($q) => $q->where('status', $status))
            ->with('items')
            ->latest()
            ->paginate(15);
    }
}
