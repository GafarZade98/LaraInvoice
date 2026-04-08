<?php

namespace GafarZade98\LaraInvoice\Contracts;

use GafarZade98\LaraInvoice\Invoice;

interface TemplateInterface
{
    /**
     * Render the invoice and return a valid SVG string.
     */
    public function render(Invoice $invoice): string;
}