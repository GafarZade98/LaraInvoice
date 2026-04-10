@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx     = LayoutContext::getInstance();
$rectX   = $ctx->x;
$rectY   = $ctx->y;
// Inset content area by padding
$oldX    = $ctx->x;
$oldW    = $ctx->contentWidth;
$ctx->x            = $rectX + $padding;
$ctx->contentWidth = $oldW  - 2 * $padding;
$ctx->advanceY($padding);
$content = (string) $slot;
$ctx->advanceY($padding);
$rectH   = $ctx->y - $rectY;
// Restore X/width
$ctx->x            = $oldX;
$ctx->contentWidth = $oldW;
@endphp
@if($background !== 'transparent')
<rect
  x="{{ number_format($rectX, 3, '.', '') }}"
  y="{{ number_format($rectY, 3, '.', '') }}"
  width="{{ number_format($oldW, 3, '.', '') }}"
  height="{{ number_format($rectH, 3, '.', '') }}"
  fill="{{ $background }}"
  rx="{{ $borderRadius }}"
  opacity="{{ $opacity }}"
/>
@endif
{!! $content !!}