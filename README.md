# Extendable Order and Payment Management API

A Laravel-based REST API for managing orders and payments with a pluggable payment gateway architecture using the Strategy pattern.

## Features

- JWT authentication (register, login, logout, refresh)
- Order CRUD with item management and automatic total calculation
- Payment processing with pluggable gateway architecture
- Strategy pattern for adding new payment gateways with zero controller changes
- Consistent JSON error responses
- 44 tests / 119 assertions

## Requirements

- PHP 8.3+
- Composer
- MySQL 8.0+ (or SQLite for testing)
- Laravel 13

## Setup

```bash
# Clone and install dependencies
composer install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Configure database in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# Then create the database and run migrations
php artisan migrate

# JWT
php artisan jwt:secret
```

## Running Tests

```bash
php artisan test --compact
```

## Architecture

```
app/
├── Actions/                   # Single-purpose action classes (business logic)
│   ├── Auth/                  # RegisterUser, LoginUser, LogoutUser, RefreshToken
│   ├── Orders/                # CreateOrder, UpdateOrder, DeleteOrder, ListOrders
│   └── Payments/              # ProcessPayment, ListPayments
├── Contracts/                 # Interfaces
│   └── PaymentGatewayInterface.php
├── Exceptions/                # Custom exceptions with JSON render methods
│   ├── OrderHasPaymentsException
│   ├── OrderNotConfirmedException
│   └── PaymentFailedException
├── Http/
│   ├── Controllers/Api/       # Thin controllers (validate → delegate to Action → respond)
│   ├── Requests/              # Form request validation classes
│   └── Resources/             # API resource transformers
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   └── User.php
└── Payment/                   # Strategy pattern implementation
    ├── Gateways/
    │   ├── CreditCardGateway.php
    │   └── PayPalGateway.php
    ├── PaymentGatewayManager.php
    └── PaymentResult.php
```

## API Endpoints

All endpoints except `register` and `login` require `Authorization: Bearer {token}` header.

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login and receive JWT |
| GET | `/api/auth/me` | Get authenticated user |
| POST | `/api/auth/logout` | Invalidate current token |
| POST | `/api/auth/refresh` | Refresh JWT token |

**POST /api/auth/register**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully.",
  "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
  "token": "eyJ0eXAiOiJKV1Qi..."
}
```

**POST /api/auth/login**

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful.",
  "token": "eyJ0eXAiOiJKV1Qi..."
}
```

**Error (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": { "email": ["The provided credentials are incorrect."] }
}
```

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders?status=` | List orders (optionally filter by status) |
| POST | `/api/orders` | Create order with items |
| GET | `/api/orders/{id}` | Show order details |
| PUT | `/api/orders/{id}` | Update order items |
| DELETE | `/api/orders/{id}` | Delete order (fails if payments exist) |

**POST /api/orders**

```json
{
  "items": [
    { "product_name": "Widget", "quantity": 2, "unit_price": 9.99 },
    { "product_name": "Gadget", "quantity": 1, "unit_price": 19.99 }
  ]
}
```

**Response (201):**
```json
{
  "message": "Order created successfully.",
  "order": {
    "id": 1,
    "user_id": 1,
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "status": "pending",
    "total": "39.97",
    "items": [
      { "id": 1, "product_name": "Widget", "quantity": 2, "unit_price": "9.99", "subtotal": "19.98" },
      { "id": 2, "product_name": "Gadget", "quantity": 1, "unit_price": "19.99", "subtotal": "19.99" }
    ],
    "created_at": "2026-06-26T12:00:00.000000Z",
    "updated_at": "2026-06-26T12:00:00.000000Z"
  }
}
```

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/payments` | Process payment for an order |
| GET | `/api/payments?order_id=` | List payments (optionally filter by order) |
| GET | `/api/payments/{id}` | Show payment details |

**POST /api/payments**

```json
{
  "order_id": 1,
  "method": "credit_card"
}
```

**Response (201):**
```json
{
  "message": "Payment processed successfully.",
  "payment": {
    "id": 1,
    "order_id": 1,
    "payment_id": "cc_550e8400-e29b-41d4-a716-446655440000",
    "method": "credit_card",
    "status": "successful",
    "transaction_id": "cc_550e8400-e29b-41d4-a716-446655440000",
    "created_at": "2026-06-26T12:00:00.000000Z",
    "updated_at": "2026-06-26T12:00:00.000000Z"
  }
}
```

### Business Rules

| Rule | Behavior |
|------|----------|
| Payment requires `confirmed` order | Returns `409 Conflict` |
| Delete blocked if payments exist | Returns `409 Conflict` |
| Successful payment sets order to `paid` | Order status auto-updated |
| Order total auto-calculated | Sum of `item.qty × item.unit_price` |

## Adding a New Payment Gateway

The system uses the Strategy pattern. Adding a new gateway requires only **2 steps**:

### Step 1: Create the gateway class

```php
<?php

namespace App\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Payment\PaymentResult;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function process(Order $order, array $data): PaymentResult
    {
        // Call Stripe API here
        return new PaymentResult(
            success: true,
            transactionId: 'stripe_' . str()->uuid(),
            message: 'Stripe payment processed successfully.',
            gatewayUsed: 'stripe',
        );
    }

    public function supports(string $method): bool
    {
        return $method === 'stripe';
    }
}
```

### Step 2: Register in `config/payment.php`

```php
'gateways' => [
    'credit_card' => CreditCardGateway::class,
    'paypal'      => PayPalGateway::class,
    'stripe'      => StripeGateway::class,   // <-- add this
],

'credentials' => [
    'credit_card' => ['merchant_id' => env('CC_MERCHANT_ID'), 'api_key' => env('CC_API_KEY')],
    'paypal'      => ['client_id' => env('PAYPAL_CLIENT_ID'), 'client_secret' => env('PAYPAL_CLIENT_SECRET')],
    'stripe'      => ['secret_key' => env('STRIPE_SECRET_KEY')],  // <-- add this
],
```

**No controllers, no enums, no routes, no validation files to modify.** The available payment methods are auto-discovered from the config, and validation dynamically picks them up via `PaymentGatewayManager::getAvailableMethods()`.

## Assumptions

- Orders are created by authenticated users; `customer_name` and `customer_email` are auto-populated from the authenticated user as a historical snapshot
- Payment gateways are simulated — no real API calls are made
- Token blacklisting is enabled by default
- Pagination defaults to 15 items per page
- The `refresh` endpoint invalidates the old token and issues a new one
