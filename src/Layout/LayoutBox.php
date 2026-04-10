<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Layout;

final readonly class LayoutBox
{
    public function __construct(
        public float $x,
        public float $y,
        public float $width,
        public float $height,
    ) {}
}