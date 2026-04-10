<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

final class Image extends Component
{
    public ?string $uri;

    public function __construct(
        public ?string $src    = null,
        public float   $height = 40,
        public ?float  $width  = null,
        public float   $mb     = 0,
    ) {
        $this->uri = null;

        if ($src !== null && file_exists($src)) {
            $mime      = mime_content_type($src) ?: 'image/png';
            $encoded   = base64_encode((string) file_get_contents($src));
            $this->uri = "data:{$mime};base64,{$encoded}";
        }
    }

    public function render(): View|\Closure|string
    {
        return view('larainvoice::components.image');
    }
}