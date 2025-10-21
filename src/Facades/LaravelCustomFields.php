<?php

namespace Xve\LaravelCustomFields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Xve\LaravelCustomFields\LaravelCustomFields
 */
class LaravelCustomFields extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Xve\LaravelCustomFields\LaravelCustomFields::class;
    }
}
