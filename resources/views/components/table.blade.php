@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx     = LayoutContext::getInstance();
$colDefs = ($ctx->currentTable())['colDefs'] ?? [];
$hy      = $tableStartY + $rowHeight * 0.66;
@endphp
<rect
  x="{{ number_format($tableX, 3, '.', '') }}"
  y="{{ number_format($tableStartY, 3, '.', '') }}"
  width="{{ number_format($tableWidth, 3, '.', '') }}"
  height="{{ $rowHeight }}"
  fill="{{ $headerBackground }}"
/>
@foreach($columns as $i => $col)
@php
$colDef  = $colDefs[$i] ?? null;
$colX    = $colDef ? $colDef['x']     : $tableX;
$colW    = $colDef ? $colDef['width'] : 0;
$hAlign  = $alignments[$i] ?? 'left';
[$hAnchor, $hx] = match($hAlign) {
    'center' => ['middle', $colX + $colW / 2],
    'right'  => ['end',    $colX + $colW - $cellPadding],
    default  => ['start',  $colX + $cellPadding],
};
@endphp
<text
  x="{{ number_format($hx, 3, '.', '') }}"
  y="{{ number_format($hy, 3, '.', '') }}"
  font-size="{{ $headerSize }}"
  font-weight="bold"
  fill="{{ $headerColor }}"
  text-anchor="{{ $hAnchor }}"
>{{ $col }}</text>
@endforeach
<line
  x1="{{ number_format($tableX, 3, '.', '') }}"
  y1="{{ number_format($tableStartY + $rowHeight, 3, '.', '') }}"
  x2="{{ number_format($tableX + $tableWidth, 3, '.', '') }}"
  y2="{{ number_format($tableStartY + $rowHeight, 3, '.', '') }}"
  stroke="#cccccc" stroke-width="0.5"
/>
{!! $slot !!}
<line
  x1="{{ number_format($tableX, 3, '.', '') }}"
  y1="{{ number_format($ctx->y, 3, '.', '') }}"
  x2="{{ number_format($tableX + $tableWidth, 3, '.', '') }}"
  y2="{{ number_format($ctx->y, 3, '.', '') }}"
  stroke="#cccccc" stroke-width="0.5"
/>
@php $ctx->endTable(); @endphp