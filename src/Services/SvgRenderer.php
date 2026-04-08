<?php

namespace GafarZade98\LaraInvoice\Services;

use GafarZade98\LaraInvoice\Invoice;

class SvgRenderer
{
    public function render(Invoice $invoice): string
    {
        return $invoice->getTemplate()->render($invoice);
    }
}