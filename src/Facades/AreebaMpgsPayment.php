<?php

namespace TheJano\AreebaPayment\Facades;

use Illuminate\Support\Facades\Facade;

class AreebaMpgsPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \TheJano\AreebaPayment\Services\AreebaMpgsPayment::class;
    }
}
