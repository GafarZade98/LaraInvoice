<?php

namespace GafarZade98\LaraInvoice\Templates;

use GafarZade98\LaraInvoice\Contracts\TemplateInterface;
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\View\LayoutBuilder;

/**
 * Base class for Blade-based invoice templates.
 *
 * Extend this class, override view() to point to your own Blade file.
 * Override layout() if you need to customise the data passed to the view.
 *
 * Example:
 *
 *   class CompactTemplate extends AbstractTemplate
 *   {
 *       protected function view(): string
 *       {
 *           return 'my-app::compact-invoice';
 *       }
 *   }
 *
 *   Invoice::make()->template(new CompactTemplate())->...
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * The Blade view name to render.
     * e.g. 'larainvoice::default' or 'my-app::invoice'
     */
    abstract protected function view(): string;

    /**
     * Build the data array passed to the Blade view.
     * Override to add, remove, or modify values.
     */
    protected function layout(Invoice $invoice): array
    {
        return LayoutBuilder::build($invoice);
    }

    final public function render(Invoice $invoice): string
    {
        $svg = view($this->view(), $this->layout($invoice))->render();

        return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $svg;
    }
}