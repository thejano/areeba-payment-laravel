# Areeba Payment Laravel Package

![Areeba Payment](https://img.shields.io/badge/laravel-areeba-blue.svg)

A Laravel package for integrating with Areeba Payment Gateway.

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


## License

This package is open-source and licensed under the [MIT License](LICENSE).

## API Documentation

For more details, visit the official API documentation:

[https://www.areeba.com/projects/areeba_gateway/integration](https://www.areeba.com/projects/areeba_gateway/integration)