<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Rect extends Component
{
    public function __construct(
        public string $background    = 'transparent',
        public string $border        = 'none',
        public float  $borderRadius  = 0,
        public float  $padding       = 0,
        public float  $opacity       = 1.0,
    ) {}

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.rect');
    }
}