<?php

use TheJano\AreebaPayment\Data\AreebaPaymentRequestData;

it('hydrates properties from the response array', function () {
    $data = new AreebaPaymentRequestData([
        'success'      => true,
        'uuid'         => 'uuid-1',
        'purchaseId'   => 'session-1',
        'returnType'   => 'REDIRECT',
        'redirectUrl'  => 'https://shop.test/pay',
        'paymentMethod' => 'CARD',
        'errorMessage' => null,
        'errorCode'    => null,
    ]);

    expect($data->success)->toBeTrue()
        ->and($data->uuid)->toBe('uuid-1')
        ->and($data->purchaseId)->toBe('session-1')
        ->and($data->getPaymentUrl())->toBe('https://shop.test/pay');
});

it('applies safe defaults for missing keys', function () {
    $data = new AreebaPaymentRequestData([]);

    expect($data->success)->toBeFalse()
        ->and($data->uuid)->toBeNull()
        ->and($data->redirectUrl)->toBeNull()
        ->and($data->errorCode)->toBeNull();
});

it('casts to an array and to json', function () {
    $data = new AreebaPaymentRequestData(['success' => true, 'uuid' => 'uuid-1']);

    $array = $data->toArray();

    expect($array)->toBeArray()
        ->and($array['success'])->toBeTrue()
        ->and($array['uuid'])->toBe('uuid-1');

    expect(json_decode($data->toJson(), true))->toMatchArray([
        'success' => true,
        'uuid'    => 'uuid-1',
    ]);
});
