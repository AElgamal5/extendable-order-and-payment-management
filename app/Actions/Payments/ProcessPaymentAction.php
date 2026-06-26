<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Payment\PaymentGatewayManager;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProcessPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    public function handle(int $orderId, string $method): Payment
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== OrderStatus::Confirmed) {
            throw new ConflictHttpException('Payments can only be processed for confirmed orders.');
        }

        $result = $this->gatewayManager->process($order, $method, []);

        $status = $result->success ? PaymentStatus::Successful : PaymentStatus::Failed;

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_id' => $result->transactionId,
            'method' => $method,
            'status' => $status,
            'transaction_id' => $result->transactionId,
            'gateway_response' => [
                'message' => $result->message,
                'gateway' => $result->gatewayUsed,
            ],
        ]);

        if ($result->success) {
            $order->update(['status' => OrderStatus::Paid]);
        }

        return $payment;
    }
}
