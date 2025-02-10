<?php

namespace TheJano\AreebaPayment\Helpers;

use Illuminate\Support\Str;
class TransactionHelper
{
    public static function getFullTransactionId(string $transactionId): string
    {
        $transactionId = self::cleanPrefix($transactionId);
        return config('areeba.transaction_prefix') . $transactionId;
    }

    public static function cleanPrefix(string $transactionId): string
    {
        return Str::of($transactionId)->replace(config('areeba.transaction_prefix'), '');
    }
}