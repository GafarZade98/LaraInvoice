@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx   = LayoutContext::getInstance();
$lineY = $ctx->y;
$dash  = match ($style) {
    'dashed' => ' stroke-dasharray="6,3"',
    'dotted' => ' stroke-dasharray="1,3" stroke-linecap="round"',
    default  => '',
};
$ctx->advanceY(1.0 + $mb);
@endphp
<line
  x1="{{ number_format($ctx->x, 3, '.', '') }}"
  y1="{{ number_format($lineY, 3, '.', '') }}"
  x2="{{ number_format($ctx->x + $ctx->contentWidth, 3, '.', '') }}"
  y2="{{ number_format($lineY, 3, '.', '') }}"
  stroke="{{ $color }}"
  stroke-width="{{ $strokeWidth }}"{{ $dash }}
/>