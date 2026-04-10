<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Spacer extends Component
{
    public function __construct(
        public float $height = 20,
    ) {}

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.spacer');
    }
}