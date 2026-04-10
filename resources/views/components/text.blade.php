@php
use GafarZade98\LaraInvoice\Layout\FontMetrics;
use GafarZade98\LaraInvoice\Layout\LayoutContext;

$ctx  = LayoutContext::getInstance();
$raw  = trim(strip_tags((string) $slot));

$raw  = match ($transform) {
    'uppercase'  => mb_strtoupper($raw),
    'lowercase'  => mb_strtolower($raw),
    'capitalize' => mb_convert_case($raw, MB_CASE_TITLE),
    default      => $raw,
};

['lines' => $lines, 'lineHeight' => $lh] = FontMetrics::measure(
    $raw, $ctx->contentWidth, $size, $bold
);

$resolved = $align === 'inherit' ? $ctx->colAlign : $align;

[$anchor, $ax] = match ($resolved) {
    'center' => ['middle', $ctx->x + $ctx->contentWidth / 2.0],
    'right'  => ['end',    $ctx->x + $ctx->contentWidth],
    default  => ['start',  $ctx->x],
};

$baseY = $ctx->y;
@endphp
@foreach($lines as $line)
<text
  x="{{ number_format($ax, 3, '.', '') }}"
  y="{{ number_format($baseY + $lh * ($loop->index + 0.78), 3, '.', '') }}"
  font-size="{{ $size }}"
  fill="{{ $color }}"
  font-weight="{{ $bold ? 'bold' : 'normal' }}"
  text-anchor="{{ $anchor }}"
>{{ htmlspecialchars($line, ENT_XML1, 'UTF-8') }}</text>
@endforeach
@php $ctx->advanceY(count($lines) * $lh + $mb); @endphp