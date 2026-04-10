<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Table extends Component
{
    public float $tableStartY;
    public float $tableX;
    public float $tableWidth;

    public function __construct(
        public array  $columns           = [],
        public array  $widths            = [],
        public array  $alignments        = [],
        public string $headerBackground  = 'transparent',
        public string $headerColor       = '#555555',
        public float  $headerSize        = 9,
        public float  $rowHeight         = 26,
        public float  $cellPadding       = 8,
    ) {
        $ctx = LayoutContext::getInstance();

        $this->tableStartY = $ctx->y;
        $this->tableX      = $ctx->x;
        $this->tableWidth  = $ctx->contentWidth;

        // beginTable() internally calls advanceY($rowHeight) to claim the header row
        $ctx->beginTable($widths, $rowHeight);
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.table');
    }
}