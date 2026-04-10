<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Text extends Component
{
    public function __construct(
        public float  $size      = 10,
        public string $color     = '#333333',
        public bool   $bold      = false,
        public string $align     = 'inherit',
        public string $transform = 'none',
        public float  $mb        = 3,
    ) {}

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.text');
    }
}