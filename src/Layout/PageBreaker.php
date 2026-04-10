<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Layout;

final class PageBreaker
{
    /**
     * Determine whether content of $requiredHeight would overflow the current page.
     *
     * The usable bottom boundary is pageHeight − margin (because pageHeight grows
     * as content renders; we compare against the configured page bottom = pageHeight - margin).
     */
    public function shouldBreak(LayoutContext $context, float $requiredHeight): bool
    {
        return ($context->y + $requiredHeight) > ($context->pageHeight - $context->margin);
    }
}