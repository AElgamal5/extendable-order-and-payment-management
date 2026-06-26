<?php

namespace Tests\Unit\Payment;

use App\Payment\Gateways\CreditCardGateway;
use App\Payment\Gateways\PayPalGateway;
use PHPUnit\Framework\TestCase;

class GatewaySupportsTest extends TestCase
{
    public function test_credit_card_gateway_supports_credit_card_only(): void
    {
        $gateway = new CreditCardGateway([]);

        $this->assertTrue($gateway->supports('credit_card'));
        $this->assertFalse($gateway->supports('paypal'));
        $this->assertFalse($gateway->supports('stripe'));
    }

    public function test_paypal_gateway_supports_paypal_only(): void
    {
        $gateway = new PayPalGateway([]);

        $this->assertTrue($gateway->supports('paypal'));
        $this->assertFalse($gateway->supports('credit_card'));
        $this->assertFalse($gateway->supports('stripe'));
    }

    public function test_credit_card_gateway_rejects_unknown_methods(): void
    {
        $gateway = new CreditCardGateway([]);

        $this->assertFalse($gateway->supports('bitcoin'));
        $this->assertFalse($gateway->supports(''));
    }

    public function test_paypal_gateway_rejects_unknown_methods(): void
    {
        $gateway = new PayPalGateway([]);

        $this->assertFalse($gateway->supports('bitcoin'));
        $this->assertFalse($gateway->supports(''));
    }
}
