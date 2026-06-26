<?php

namespace App\Payment;

readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public string $message,
        public string $gatewayUsed,
    ) {}
}
