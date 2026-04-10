@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx          = LayoutContext::getInstance();
$imgY         = $ctx->y;
$displayWidth = $width ?? ($height * 3.0);
$imgX         = $ctx->colAlign === 'right'
    ? $ctx->x + $ctx->contentWidth - $displayWidth
    : $ctx->x;
@endphp
@if($uri)
<image
  x="{{ number_format($imgX, 3, '.', '') }}"
  y="{{ number_format($imgY, 3, '.', '') }}"
  width="{{ $displayWidth }}"
  height="{{ $height }}"
  xlink:href="{{ $uri }}"
  href="{{ $uri }}"
  preserveAspectRatio="xMinYMin meet"
/>
@endif
@php $ctx->advanceY($height + $mb); @endphp