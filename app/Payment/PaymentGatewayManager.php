<?php

namespace App\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function register(string $method, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$method] = $gateway;
    }

    public function process(Order $order, string $method, array $data): PaymentResult
    {
        $gateway = $this->gateways[$method] ?? null;

        if (! $gateway) {
            throw new InvalidArgumentException("Unsupported payment method: {$method}.");
        }

        return $gateway->process($order, $data);
    }

    /** @return array<int, string> */
    public function getAvailableMethods(): array
    {
        return array_keys($this->gateways);
    }
}
