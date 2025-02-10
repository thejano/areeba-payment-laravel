<?php

namespace TheJano\AreebaPayment\Facades;

use Illuminate\Support\Facades\Facade;

class AreebaPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \TheJano\AreebaPayment\Services\AreebaPayment::class;
    }
}