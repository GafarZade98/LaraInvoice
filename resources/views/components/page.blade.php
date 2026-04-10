@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx     = LayoutContext::getInstance();
$content = (string) $slot;
$h       = max(842.0, $ctx->pageHeight);
@endphp
<svg xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink"
     width="{{ $ctx->pageWidth }}"
     height="{{ $h }}"
     viewBox="0 0 {{ $ctx->pageWidth }} {{ $h }}"
     font-family="Helvetica Neue, Helvetica, Arial, sans-serif">
  <rect width="{{ $ctx->pageWidth }}" height="{{ $h }}" fill="white"/>
  {!! $content !!}
</svg>