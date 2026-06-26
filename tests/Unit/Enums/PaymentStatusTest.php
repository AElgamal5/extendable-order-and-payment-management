<?php

namespace Tests\Unit\Enums;

use App\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertTrue(PaymentStatus::tryFrom('pending') !== null);
        $this->assertTrue(PaymentStatus::tryFrom('successful') !== null);
        $this->assertTrue(PaymentStatus::tryFrom('failed') !== null);
    }

    public function test_values_are_strings(): void
    {
        $this->assertEquals('pending', PaymentStatus::Pending->value);
        $this->assertEquals('successful', PaymentStatus::Successful->value);
        $this->assertEquals('failed', PaymentStatus::Failed->value);
    }

    public function test_rejects_invalid_status(): void
    {
        $this->assertNull(PaymentStatus::tryFrom('invalid'));
        $this->assertNull(PaymentStatus::tryFrom(''));
    }
}
