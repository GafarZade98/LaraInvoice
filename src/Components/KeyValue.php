<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class KeyValue extends Component
{
    public function __construct(
        public string $label      = '',
        public string $value      = '',
        public float  $size       = 9,
        public bool   $bold       = false,
        public string $color      = '#333333',
        public string $labelColor = '',
        public float  $mb         = 3,
    ) {
        if ($this->labelColor === '') {
            $this->labelColor = $this->color;
        }
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.key-value');
    }
}