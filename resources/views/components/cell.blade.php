@php
use GafarZade98\LaraInvoice\Layout\LayoutContext;
$ctx     = LayoutContext::getInstance();
$rowH    = $ctx->currentTableRowHeight();
$val     = trim(strip_tags((string) $slot));
$subVal  = $sub !== null ? trim($sub) : '';
$hasSub  = $subVal !== '';
$subSize = 8.0;

[$anchor, $tx] = match ($align) {
    'center' => ['middle', $cellX + $cellWidth / 2.0],
    'right'  => ['end',    $cellX + $cellWidth - $padding],
    default  => ['start',  $cellX + $padding],
};

if ($hasSub) {
    $mainLH = $size  * 1.3;
    $subLH  = $subSize * 1.3;
    $topPad = max(0.0, ($rowH - $mainLH - $subLH) / 2.0);
    $ty     = $ctx->y + $topPad + $size * 0.85;
    $subTy  = $ty + $mainLH;
} else {
    $ty = $ctx->y + $rowH * 0.5 + $size * 0.35;
}

$clipId = 'c' . abs(crc32((string) $cellX . ':' . (string) $ctx->y));
@endphp
<clipPath id="{{ $clipId }}">
  <rect x="{{ number_format($cellX, 3, '.', '') }}" y="{{ number_format($ctx->y, 3, '.', '') }}" width="{{ number_format($cellWidth, 3, '.', '') }}" height="{{ number_format($rowH, 3, '.', '') }}"/>
</clipPath>
<text
  x="{{ number_format($tx, 3, '.', '') }}"
  y="{{ number_format($ty, 3, '.', '') }}"
  font-size="{{ $size }}"
  fill="{{ $color }}"
  font-weight="{{ $bold ? 'bold' : 'normal' }}"
  text-anchor="{{ $anchor }}"
  clip-path="url(#{{ $clipId }})"
>{{ htmlspecialchars($val, ENT_XML1, 'UTF-8') }}</text>
@if($hasSub)
<text
  x="{{ number_format($tx, 3, '.', '') }}"
  y="{{ number_format($subTy, 3, '.', '') }}"
  font-size="{{ $subSize }}"
  fill="#888888"
  font-weight="normal"
  text-anchor="{{ $anchor }}"
  clip-path="url(#{{ $clipId }})"
>{{ htmlspecialchars($subVal, ENT_XML1, 'UTF-8') }}</text>
@endif