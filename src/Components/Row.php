<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Row extends Component
{
    public function __construct()
    {
        LayoutContext::getInstance()->beginRow();
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.row');
    }
}