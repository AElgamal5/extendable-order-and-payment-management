<?php

namespace App\Http\Requests\Payments;

use App\Payment\PaymentGatewayManager;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:'.implode(',', $this->gatewayManager->getAvailableMethods())],
        ];
    }
}
