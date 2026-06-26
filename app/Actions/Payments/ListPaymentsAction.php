<?php

namespace App\Actions\Payments;

use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPaymentsAction
{
    public function handle(?int $orderId = null): LengthAwarePaginator
    {
        return Payment::query()
            ->when($orderId, fn ($q) => $q->where('order_id', $orderId))
            ->with('order')
            ->latest()
            ->paginate(15);
    }
}
