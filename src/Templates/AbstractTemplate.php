<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Templates;

use GafarZade98\LaraInvoice\Contracts\TemplateInterface;
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Layout\LayoutContext;

/**
 * Base class for component-based invoice templates.
 *
 * The template OWNS the LayoutContext — it initialises it before any
 * Blade rendering occurs, so custom templates can override makeLayoutContext()
 * to use different page dimensions, margins, or a LayoutContext subclass.
 *
 * Example:
 *
 *   class WideTemplate extends AbstractTemplate
 *   {
 *       protected function view(): string { return 'my-app::wide'; }
 *
 *       protected function makeLayoutContext(): LayoutContext
 *       {
 *           return LayoutContext::make(842, 30); // A4 landscape
 *       }
 *   }
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /** The Blade view to render, e.g. 'larainvoice::templates.default' */
    abstract protected function view(): string;

    /**
     * Create (and register as singleton) the LayoutContext for this render pass.
     * Override to use custom page size, margins, or a LayoutContext subclass.
     */
    protected function makeLayoutContext(): LayoutContext
    {
        return LayoutContext::make(); // A4, 40 pt margin
    }

    /**
     * Data passed to the Blade view.
     * Override to add extra variables alongside $invoice.
     */
    protected function layout(Invoice $invoice): array
    {
        return ['invoice' => $invoice];
    }

    final public function render(Invoice $invoice): string
    {
        // Template initialises the context BEFORE any child component
        // constructor runs during Blade rendering.
        $this->makeLayoutContext();

        $svg = view($this->view(), $this->layout($invoice))->render();

        return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $svg;
    }
}