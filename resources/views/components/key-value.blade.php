@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
use GafarZade98\LaraInvoice\Layout\FontMetrics;
$ctx  = LayoutContext::getInstance();
$lh   = FontMetrics::lineHeight($size);
$y    = $ctx->y + $lh * 0.78;
$endX = $ctx->x + $ctx->contentWidth;
@endphp
<text
  x="{{ number_format($ctx->x, 3, '.', '') }}"
  y="{{ number_format($y, 3, '.', '') }}"
  font-size="{{ $size }}"
  fill="{{ $labelColor }}"
>{{ htmlspecialchars($label, ENT_XML1, 'UTF-8') }}</text>
<text
  x="{{ number_format($endX, 3, '.', '') }}"
  y="{{ number_format($y, 3, '.', '') }}"
  font-size="{{ $size }}"
  fill="{{ $color }}"
  font-weight="{{ $bold ? 'bold' : 'normal' }}"
  text-anchor="end"
>{{ htmlspecialchars($value, ENT_XML1, 'UTF-8') }}</text>
@php $ctx->advanceY($lh + $mb); @endphp