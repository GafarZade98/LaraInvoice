<?php

namespace GafarZade98\LaraInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \GafarZade98\LaraInvoice\Invoice make()
 *
 * @see \GafarZade98\LaraInvoice\Invoice
 */
class Invoice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GafarZade98\LaraInvoice\Invoice::class;
    }
}