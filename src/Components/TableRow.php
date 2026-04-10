<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class TableRow extends Component
{
    public float $rowStartY;
    public float $rowHeight;
    public float $tableX;
    public float $tableWidth;

    public function __construct(
        public bool   $striped    = false,
        public string $background = 'none',
    ) {
        $ctx = LayoutContext::getInstance();

        $this->rowStartY  = $ctx->y;
        $this->rowHeight  = $ctx->currentTableRowHeight();
        $this->tableX     = $ctx->x;
        $this->tableWidth = $ctx->contentWidth;

        $ctx->resetTableColIndex();
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.table-row');
    }
}