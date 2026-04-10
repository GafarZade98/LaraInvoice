<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Renderer;

use GafarZade98\LaraInvoice\Invoice;

final class SvgRenderer
{
    /**
     * Render the invoice via its template and return a valid SVG string.
     */
    public function render(Invoice $invoice): string
    {
        return $invoice->getTemplate()->render($invoice);
    }
}