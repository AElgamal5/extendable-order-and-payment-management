<?php

use App\Payment\Gateways\CreditCardGateway;
use App\Payment\Gateways\PayPalGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Method
    |--------------------------------------------------------------------------
    |
    | This is the default payment method used when none is specified.
    |
    */

    'default' => env('PAYMENT_DEFAULT', 'credit_card'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Map payment method names to their gateway classes.
    | Add new gateways here to extend the system.
    |
    */

    'gateways' => [
        'credit_card' => CreditCardGateway::class,
        'paypal' => PayPalGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway Credentials
    |--------------------------------------------------------------------------
    |
    | API keys and secrets for each gateway.
    |
    */

    'credentials' => [
        'credit_card' => [
            'merchant_id' => env('CC_MERCHANT_ID'),
            'api_key' => env('CC_API_KEY'),
        ],

        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
        ],
    ],
];
