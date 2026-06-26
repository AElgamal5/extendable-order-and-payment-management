<?php

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertTrue(OrderStatus::tryFrom('pending') !== null);
        $this->assertTrue(OrderStatus::tryFrom('confirmed') !== null);
        $this->assertTrue(OrderStatus::tryFrom('paid') !== null);
        $this->assertTrue(OrderStatus::tryFrom('cancelled') !== null);
    }

    public function test_values_are_strings(): void
    {
        $this->assertEquals('pending', OrderStatus::Pending->value);
        $this->assertEquals('confirmed', OrderStatus::Confirmed->value);
        $this->assertEquals('paid', OrderStatus::Paid->value);
        $this->assertEquals('cancelled', OrderStatus::Cancelled->value);
    }

    public function test_rejects_invalid_status(): void
    {
        $this->assertNull(OrderStatus::tryFrom('invalid_status'));
        $this->assertNull(OrderStatus::tryFrom(''));
    }
}
