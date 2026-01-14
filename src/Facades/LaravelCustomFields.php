<?php

namespace Skylence\LaravelCustomFields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Skylence\LaravelCustomFields\LaravelCustomFields
 */
class LaravelCustomFields extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Skylence\LaravelCustomFields\LaravelCustomFields::class;
    }
}
