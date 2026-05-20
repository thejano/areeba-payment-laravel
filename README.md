# Areeba Payment Laravel Package

![Areeba Payment](https://img.shields.io/badge/laravel-areeba-blue.svg)

A Laravel package for integrating with Areeba Payment Gateway.

The package supports two Areeba products:

- **IXOPAY** (default) — the transaction-based gateway hosted at `gateway.areebapayment.com`.
- **MPGS / ePayment** — Areeba's hosted-checkout product on `epayment.areeba.com`, backed by MasterCard Payment Gateway Services. See [Using the MPGS / ePayment Driver](#using-the-mpgs--epayment-driver) below.

## Payment Flow

This flow requires you to redirect the end-user to the payment page as advised in the `redirectUrl` response.

1. Initiate the payment with the appropriate API call.
2. Upon success, the Gateway responds with a result containing `returnType` as `REDIRECT` and the URL in `redirectUrl`.
3. You redirect the user to the given URL (usually via a `Location` header).
4. The user completes the payment process on the payment page.
5. The Gateway sends an asynchronous status notification to the URL provided in the initial API call.
6. The user will be redirected to the `successUrl`, `errorUrl`, or `cancelUrl` based on the transaction status. The URL will contain the `transactionId` as a query parameter: `url?transactionId={{$transactionId}}`.

## Installation

You can install the package via Composer:

```sh
composer require thejano/areeba-payment-laravel
```

## Configuration

Publish the configuration file:

```sh
php artisan vendor:publish --provider="TheJano\AreebaPayment\Providers\AreebaPaymentServiceProvider"
```

This will create a `config/areeba.php` file.

Set your environment variables in `.env`:

```ini
AREEBA_API_KEY=your_api_key
AREEBA_USERNAME=your_username
AREEBA_PASSWORD=your_password
AREEBA_BASE_URL=https://gateway.areebapayment.com/api/v3
AREEBA_LANGUAGE=en
AREEBA_TRANSACTION_PREFIX=MYAPP-
AREEBA_SUCCESS_REDIRECT_URL=https://yourapp.com/payment/success
AREEBA_ERROR_REDIRECT_URL=https://yourapp.com/payment/error
AREEBA_CANCEL_REDIRECT_URL=https://yourapp.com/payment/cancel
AREEBA_CALLBACK_REDIRECT_URL=https://yourapp.com/payment/callback
```

## Usage

### Using the Service Class for Payment Initiation

```php
use TheJano\AreebaPayment\Services\AreebaPayment;

$paymentData = AreebaPayment::make()->initiatePayment('TXN123456', '100.00', 'John Doe');

$paymentUrl = $paymentData->redirectUrl;

return redirect($paymentUrl);
```

### Using the Facade

```php
use TheJano\AreebaPayment\Facades\AreebaPayment;

$paymentData = AreebaPayment::initiatePayment('TXN123456', '100.00', 'John Doe');

$paymentUrl = $paymentData->redirectUrl;

return redirect($paymentUrl);
```

## Request Response Data Properties

The `AreebaPaymentRequestData` contains the following properties:

- `success` (bool) - Indicates if the request was successful.
- `uuid` (string|null) - Unique identifier for the transaction.
- `purchaseId` (string|null) - The purchase reference ID.
- `returnType` (string|null) - Type of return response (e.g., `REDIRECT`).
- `redirectUrl` (string|null) - URL where the user should be redirected to complete payment.
- `paymentMethod` (string|null) - Payment method used by the user.
- `errorMessage` (string|null) - Error message in case of failure.
- `errorCode` (int|null) - Error code if the transaction failed.


Based on the transaction status, the user will be redirected to the appropriate URL with `?transactionId={{$transactionId}}` appended.

------ 

## Checking Payment Status

```php
use TheJano\AreebaPayment\Facades\AreebaPayment;

$checkResponse = AreebaPayment::checkPaymentStatus('TXN123456');
```

This will return a JSON response including `transactionStatus` with possible values:

- `SUCCESS` - Transaction was successful.
- `REDIRECT` - Transaction has not been processed yet.
- `ERROR` - Transaction failed.


## Using the MPGS / ePayment Driver

Areeba also offers a hosted-checkout product (MPGS / ePayment) on `https://epayment.areeba.com`. It uses a different API than the IXOPAY gateway above: you create a checkout `session`, redirect the user to a hosted page (or embed `checkout.js`), and later query the `order` resource for the final payment status.

### Selecting the Driver

Set `AREEBA_DRIVER=mpgs` in your `.env` to make `app(PaymentGateway::class)` resolve to the MPGS driver. Leaving it unset keeps the default IXOPAY behavior unchanged.

```ini
AREEBA_DRIVER=mpgs
```

Alternatively, you can call the MPGS service directly without changing the default driver — the IXOPAY service remains available either way.

### MPGS Environment Variables

The MPGS credentials are namespaced under `AREEBA_MPGS_*` so they do not collide with the IXOPAY keys:

```ini
AREEBA_MPGS_BASE_URL=https://epayment.areeba.com
AREEBA_MPGS_API_VERSION=100
AREEBA_MPGS_MERCHANT_ID=your_merchant_id
AREEBA_MPGS_USERNAME=merchant.your_merchant_id
AREEBA_MPGS_PASSWORD=your_password
AREEBA_MPGS_CURRENCY=USD
AREEBA_MPGS_CHECKOUT_JS_URL=https://epayment.areeba.com/static/checkout/checkout.min.js
AREEBA_MPGS_CHECKOUT_VERSION=1.0.0
AREEBA_MPGS_SUCCESS_REDIRECT_URL=https://yourapp.com/payment/success
AREEBA_MPGS_ERROR_REDIRECT_URL=https://yourapp.com/payment/error
AREEBA_MPGS_CANCEL_REDIRECT_URL=https://yourapp.com/payment/cancel
AREEBA_MPGS_CALLBACK_REDIRECT_URL=https://yourapp.com/payment/callback
```

### Usage

```php
use TheJano\AreebaPayment\Services\AreebaMpgsPayment;

$paymentData = AreebaMpgsPayment::make()->initiatePayment('ORDER-123', '100.00', 'John Doe');

// $paymentData->purchaseId  → MPGS session id (use it with checkout.js)
// $paymentData->redirectUrl → hosted-checkout URL (use it for a plain redirect flow)
return redirect($paymentData->redirectUrl);
```

Or via the facade:

```php
use TheJano\AreebaPayment\Facades\AreebaMpgsPayment;

$paymentData = AreebaMpgsPayment::initiatePayment('ORDER-123', '100.00', 'John Doe');
```

### Getting Just the Session ID or Payment Link

If you don't need the full `AreebaPaymentRequestData` object, two convenience methods return the value you want as a plain string (or `null` if the gateway call fails — the failure is already logged via Laravel's logger):

```php
use TheJano\AreebaPayment\Facades\AreebaMpgsPayment;

// Hosted-checkout redirect URL
$paymentLink = AreebaMpgsPayment::getPaymentLink('ORDER-123', '100.00', 'John Doe');
if ($paymentLink === null) {
    abort(502, 'Payment gateway unavailable');
}
return redirect($paymentLink);

// MPGS session ID (for embedded checkout.js)
$sessionId = AreebaMpgsPayment::getSessionId('ORDER-123', '100.00', 'John Doe');
return view('checkout', ['sessionId' => $sessionId]);
```

Both methods accept the same arguments as `initiatePayment()` and call it internally — they are pure projections of its result, so there is no extra network round-trip. Use `initiatePayment()` directly when you also need the error message or other fields on failure.

### Embedded Checkout vs. Redirect

The `purchaseId` returned from `initiatePayment` is the MPGS `session.id`. With it you can either:

1. **Redirect** the user to `$paymentData->redirectUrl` (the hosted checkout page), or
2. **Embed** `checkout.js` on your own page and call:

   ```html
   <script src="https://epayment.areeba.com/static/checkout/checkout.min.js"></script>
   <script>
     Checkout.configure({ session: { id: '<?= $paymentData->purchaseId ?>' } });
     Checkout.showPaymentPage();
   </script>
   ```

### Checking Payment Status

```php
use TheJano\AreebaPayment\Facades\AreebaMpgsPayment;

$response = AreebaMpgsPayment::checkPaymentStatus('ORDER-123');
// Inspect $response['status'] — values include CAPTURED, AUTHORIZED, FAILED, DECLINED, EXPIRED, CANCELLED.
```

`checkPaymentStatus` returns the raw decoded JSON from MPGS so your application can map gateway-specific statuses to its own payment states.

## License

This package is open-source and licensed under the [MIT License](LICENSE).

## API Documentation

For more details, visit the official API documentation:

[https://www.areeba.com/projects/areeba_gateway/integration](https://www.areeba.com/projects/areeba_gateway/integration)