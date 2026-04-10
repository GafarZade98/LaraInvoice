<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Divider extends Component
{
    public function __construct(
        public string $color       = '#dddddd',
        public float  $strokeWidth = 0.5,
        public string $style       = 'solid',
        public float  $mb          = 0,
    ) {}

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.divider');
    }
}