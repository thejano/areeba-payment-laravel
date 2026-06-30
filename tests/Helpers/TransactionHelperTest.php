<?php

use TheJano\AreebaPayment\Helpers\TransactionHelper;

it('cleans an existing prefix from the transaction id', function () {
    expect(TransactionHelper::cleanPrefix('TEST-ORDER-1'))->toBe('ORDER-1')
        ->and(TransactionHelper::cleanPrefix('ORDER-1'))->toBe('ORDER-1');
});

it('builds the full transaction id with a single prefix', function () {
    expect(TransactionHelper::getFullTransactionId('ORDER-1'))->toBe('TEST-ORDER-1')
        ->and(TransactionHelper::getFullTransactionId('TEST-ORDER-1'))->toBe('TEST-ORDER-1');
});
