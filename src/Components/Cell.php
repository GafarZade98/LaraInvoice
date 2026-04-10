<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Cell extends Component
{
    public float $cellX;
    public float $cellWidth;

    public function __construct(
        public string  $align   = 'left',
        public bool    $bold    = false,
        public float   $size    = 9,
        public string  $color   = '#333333',
        public float   $padding = 8,
        public ?string $sub     = null,
    ) {
        $ctx = LayoutContext::getInstance();
        $col = $ctx->nextTableCol();

        $this->cellX     = $col['x']     ?? $ctx->x;
        $this->cellWidth = $col['width'] ?? $ctx->contentWidth;
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.cell');
    }
}