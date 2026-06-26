<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderNotConfirmedException;
use App\Exceptions\PaymentFailedException;
use App\Models\Order;
use App\Models\Payment;
use App\Payment\PaymentGatewayManager;

class ProcessPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    public function handle(int $orderId, string $method): Payment
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== OrderStatus::Confirmed) {
            throw new OrderNotConfirmedException;
        }

        $result = $this->gatewayManager->process($order, $method, []);

        if (! $result->success) {
            throw new PaymentFailedException($result->message);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_id' => $result->transactionId,
            'method' => $method,
            'status' => PaymentStatus::Successful,
            'transaction_id' => $result->transactionId,
            'gateway_response' => [
                'message' => $result->message,
                'gateway' => $result->gatewayUsed,
            ],
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        return $payment;
    }
}
