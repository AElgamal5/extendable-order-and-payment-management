<?php

namespace Tests\Unit\Payment;

use App\Payment\PaymentResult;
use PHPUnit\Framework\TestCase;

class PaymentResultTest extends TestCase
{
    public function test_can_create_successful_result(): void
    {
        $result = new PaymentResult(
            success: true,
            transactionId: 'txn_abc123',
            message: 'Payment processed.',
            gatewayUsed: 'credit_card',
        );

        $this->assertTrue($result->success);
        $this->assertEquals('txn_abc123', $result->transactionId);
        $this->assertEquals('Payment processed.', $result->message);
        $this->assertEquals('credit_card', $result->gatewayUsed);
    }

    public function test_can_create_failed_result(): void
    {
        $result = new PaymentResult(
            success: false,
            transactionId: 'txn_fail',
            message: 'Insufficient funds.',
            gatewayUsed: 'stripe',
        );

        $this->assertFalse($result->success);
        $this->assertEquals('Insufficient funds.', $result->message);
    }

    public function test_result_is_readonly(): void
    {
        $result = new PaymentResult(true, 't1', 'ok', 'gateway');

        $this->assertTrue($result->success);
        $this->assertEquals('t1', $result->transactionId);
    }
}
