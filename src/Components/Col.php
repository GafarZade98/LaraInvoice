<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use GafarZade98\LaraInvoice\Layout\LayoutContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Col extends Component
{
    /** @var array{x: float, width: float, startY: float} */
    public array $colInfo;

    public function __construct(
        public string $width = '100%',
        public string $align = 'left',
    ) {
        // Parse the percentage value (e.g. '50%' → 50.0)
        $pct = (float) rtrim($width, '%');

        $this->colInfo = LayoutContext::getInstance()->beginCol($pct, $align);
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.col');
    }
}