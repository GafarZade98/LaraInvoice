<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Enums\PaperSize;
use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Page extends Component
{
    public float $pageWidth;

    public function __construct(
        public string $size   = 'A4',
        public float  $margin = 40,
    ) {
        $this->pageWidth = PaperSize::fromString($size)->width();

        LayoutContext::getInstance()->resize($this->pageWidth, $margin);
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.page');
    }
}