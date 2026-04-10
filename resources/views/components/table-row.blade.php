@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx = LayoutContext::getInstance();
$bg  = $striped ? '#f9f9f9' : ($background !== 'none' ? $background : null);
@endphp
@if($bg)
<rect
  x="{{ number_format($tableX, 3, '.', '') }}"
  y="{{ number_format($rowStartY, 3, '.', '') }}"
  width="{{ number_format($tableWidth, 3, '.', '') }}"
  height="{{ $rowHeight }}"
  fill="{{ $bg }}"
/>
@endif
{!! $slot !!}
<line
  x1="{{ number_format($tableX, 3, '.', '') }}"
  y1="{{ number_format($rowStartY + $rowHeight, 3, '.', '') }}"
  x2="{{ number_format($tableX + $tableWidth, 3, '.', '') }}"
  y2="{{ number_format($rowStartY + $rowHeight, 3, '.', '') }}"
  stroke="#eeeeee" stroke-width="0.5"
/>
@php
$ctx->y = $rowStartY + $rowHeight;
$ctx->pageHeight = max($ctx->pageHeight, $ctx->y + $ctx->margin);
@endphp