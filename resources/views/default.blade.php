<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
     viewBox="0 0 {{ $width }} {{ $height }}" width="{{ $width }}" height="{{ $height }}">

  <rect width="{{ $width }}" height="{{ $height }}" fill="white"/>

  {{-- ═══ Header Bar ═══ --}}
  <rect x="0" y="0" width="{{ $width }}" height="8" fill="{{ $colors['brand'] }}"/>

  {{-- ═══ Logo ═══ --}}
  @if($logo)
  <image x="{{ $logo['x'] }}" y="{{ $logo['y'] }}" width="{{ $logo['w'] }}" height="{{ $logo['h'] }}"
         href="{{ $logo['uri'] }}" preserveAspectRatio="xMaxYMid meet"/>
  @endif

  {{-- ═══ Title & Invoice Number ═══ --}}
  <text x="{{ $header['title']['x'] }}" y="{{ $header['title']['y'] }}"
        font-family="sans-serif" font-size="22" font-weight="bold"
        fill="{{ $colors['black'] }}">{{ $header['title']['text'] }}</text>

  @if($header['number'])
  <text x="{{ $header['number']['x'] }}" y="{{ $header['number']['y'] }}"
        font-family="sans-serif" font-size="9"
        fill="{{ $colors['grey'] }}">{{ $header['number']['text'] }}</text>
  @endif

  {{-- ═══ Metadata (Date · Due Date · Payment Method · Tax IDs…) ═══ --}}
  <line x1="{{ $margin }}" y1="{{ $meta['ruleY'] }}" x2="{{ $right }}" y2="{{ $meta['ruleY'] }}"
        stroke="{{ $colors['rule'] }}" stroke-width="0.5"/>

  @foreach($meta['rows'] as $row)
  <text x="{{ $meta['labelX'] }}" y="{{ $row['y'] }}"
        font-family="sans-serif" font-size="8" fill="{{ $colors['grey'] }}">{{ $row['label'] }}</text>
  <text x="{{ $meta['valueX'] }}" y="{{ $row['y'] }}"
        font-family="sans-serif" font-size="8" fill="{{ $colors['black'] }}">{{ $row['value'] }}</text>
  @endforeach

  {{-- ═══ Seller & Buyer ═══ --}}
  <line x1="{{ $margin }}" y1="{{ $parties['ruleY'] }}" x2="{{ $right }}" y2="{{ $parties['ruleY'] }}"
        stroke="{{ $colors['rule'] }}" stroke-width="0.5"/>

  @foreach($parties['seller'] as $line)
  <text x="{{ $parties['sellerX'] }}" y="{{ $line['y'] }}"
        font-family="sans-serif" font-size="{{ $line['bold'] ? 9 : 8 }}"
        @if($line['bold']) font-weight="bold" @endif
        fill="{{ $line['bold'] ? $colors['black'] : $colors['grey'] }}">{{ $line['text'] }}</text>
  @endforeach

  @if($parties['buyerLabelY'])
  <text x="{{ $parties['buyerX'] }}" y="{{ $parties['buyerLabelY'] }}"
        font-family="sans-serif" font-size="8" fill="{{ $colors['grey'] }}">Bill to</text>
  @foreach($parties['buyer'] as $line)
  <text x="{{ $parties['buyerX'] }}" y="{{ $line['y'] }}"
        font-family="sans-serif" font-size="{{ $line['bold'] ? 9 : 8 }}"
        @if($line['bold']) font-weight="bold" @endif
        fill="{{ $line['bold'] ? $colors['black'] : $colors['grey'] }}">{{ $line['text'] }}</text>
  @endforeach
  @endif

  {{-- ═══ Amount Paid / Due / Refunded ═══ --}}
  @if($summaryLine)
  <text x="{{ $summaryLine['x'] }}" y="{{ $summaryLine['y'] }}"
        font-family="sans-serif" font-size="12" font-weight="bold"
        fill="{{ $colors['black'] }}">{{ $summaryLine['text'] }}</text>
  @endif

  {{-- ═══ Items Table ═══ --}}
  <line x1="{{ $margin }}" y1="{{ $table['ruleY'] }}" x2="{{ $right }}" y2="{{ $table['ruleY'] }}"
        stroke="{{ $colors['rule'] }}" stroke-width="0.5"/>

  {{-- Table Header --}}
  <text x="{{ $margin }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}">DESCRIPTION</text>
  <text x="{{ $table['layout']['qty_cx'] }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}" text-anchor="middle">QTY</text>
  <text x="{{ $table['layout']['up_rx'] }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}" text-anchor="end">UNIT PRICE</text>
  @if($table['hasDiscount'] && $table['layout']['disc_cx'])
  <text x="{{ $table['layout']['disc_cx'] }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}" text-anchor="middle">DISCOUNT</text>
  @endif
  @if($table['hasTax'] && $table['layout']['tax_cx'])
  <text x="{{ $table['layout']['tax_cx'] }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}" text-anchor="middle">TAX</text>
  @endif
  <text x="{{ $right }}" y="{{ $table['headerY'] }}"
        font-family="sans-serif" font-size="7" font-weight="bold"
        fill="{{ $colors['grey'] }}" text-anchor="end">AMOUNT</text>

  <line x1="{{ $margin }}" y1="{{ $table['dividerY'] }}" x2="{{ $right }}" y2="{{ $table['dividerY'] }}"
        stroke="{{ $colors['black'] }}" stroke-width="0.5"/>

  {{-- Table Rows --}}
  @foreach($table['items'] as $item)

  <text x="{{ $margin }}" y="{{ $item['name']['y'] }}"
        font-family="sans-serif" font-size="9" font-weight="bold"
        fill="{{ $colors['black'] }}">{{ $item['name']['text'] }}</text>

  @if($item['desc'])
  <text x="{{ $margin }}" y="{{ $item['desc']['y'] }}"
        font-family="sans-serif" font-size="8"
        fill="{{ $colors['grey'] }}">{{ $item['desc']['text'] }}</text>
  @endif

  @if($item['extra'])
  <text x="{{ $margin }}" y="{{ $item['extra']['y'] }}"
        font-family="sans-serif" font-size="7" font-style="italic"
        fill="{{ $colors['light'] }}">{{ $item['extra']['text'] }}</text>
  @endif

  <text x="{{ $table['layout']['qty_cx'] }}" y="{{ $item['qty']['y'] }}"
        font-family="sans-serif" font-size="9"
        fill="{{ $colors['black'] }}" text-anchor="middle">{{ $item['qty']['text'] }}</text>

  <text x="{{ $table['layout']['up_rx'] }}" y="{{ $item['price']['y'] }}"
        font-family="sans-serif" font-size="9"
        fill="{{ $colors['black'] }}" text-anchor="end">{{ $item['price']['text'] }}</text>

  @if($table['hasDiscount'] && $table['layout']['disc_cx'])
  @foreach($item['discounts'] as $disc)
  <text x="{{ $table['layout']['disc_cx'] }}" y="{{ $disc['y'] }}"
        font-family="sans-serif" font-size="8"
        fill="{{ $colors['black'] }}" text-anchor="middle">{{ $disc['text'] }}</text>
  @endforeach
  @endif

  @if($table['hasTax'] && $table['layout']['tax_cx'] && $item['tax'])
  <text x="{{ $table['layout']['tax_cx'] }}" y="{{ $item['tax']['y'] }}"
        font-family="sans-serif" font-size="9"
        fill="{{ $colors['black'] }}" text-anchor="middle">{{ $item['tax']['text'] }}</text>
  @endif

  <text x="{{ $right }}" y="{{ $item['amount']['y'] }}"
        font-family="sans-serif" font-size="9"
        fill="{{ $colors['black'] }}" text-anchor="end">{{ $item['amount']['text'] }}</text>

  <line x1="{{ $margin }}" y1="{{ $item['ruleY'] }}" x2="{{ $right }}" y2="{{ $item['ruleY'] }}"
        stroke="{{ $colors['rule'] }}" stroke-width="0.5"/>

  @endforeach

  {{-- ═══ Summary Section ═══ --}}
  @foreach($summarySection['rows'] as $row)
  @if($row['lineY'] !== null)
  <line x1="{{ $summarySection['labelX'] }}" y1="{{ $row['lineY'] }}"
        x2="{{ $summarySection['valueX'] }}" y2="{{ $row['lineY'] }}"
        stroke="{{ $colors['black'] }}" stroke-width="0.5"/>
  @endif
  <text x="{{ $summarySection['labelX'] }}" y="{{ $row['y'] }}"
        font-family="sans-serif" font-size="{{ $row['bold'] ? 10 : 9 }}"
        font-weight="{{ $row['bold'] ? 'bold' : 'normal' }}"
        fill="{{ $colors['black'] }}" text-anchor="end">{{ $row['label'] }}</text>
  <text x="{{ $summarySection['valueX'] }}" y="{{ $row['y'] }}"
        font-family="sans-serif" font-size="{{ $row['bold'] ? 10 : 9 }}"
        font-weight="{{ $row['bold'] ? 'bold' : 'normal' }}"
        fill="{{ $colors['black'] }}" text-anchor="end">{{ $row['value'] }}</text>
  @endforeach

  {{-- ═══ Notes ═══ --}}
  @if($notes)
  <text x="{{ $notes['x'] }}" y="{{ $notes['y'] }}"
        font-family="sans-serif" font-size="8"
        fill="{{ $colors['grey'] }}">{{ $notes['text'] }}</text>
  @endif

  {{-- ═══ Refund Instructions ═══ --}}
  @if($refund)
  <line x1="{{ $margin }}" y1="{{ $refund['ruleY'] }}" x2="{{ $right }}" y2="{{ $refund['ruleY'] }}"
        stroke="{{ $colors['rule'] }}" stroke-width="0.5"/>
  <text x="{{ $refund['x'] }}" y="{{ $refund['titleY'] }}"
        font-family="sans-serif" font-size="9" font-weight="bold"
        fill="{{ $colors['black'] }}">Refund instructions</text>
  @foreach($refund['lines'] as $rline)
  <text x="{{ $refund['x'] }}" y="{{ $rline['y'] }}"
        font-family="sans-serif" font-size="8"
        fill="{{ $colors['grey'] }}">{{ $rline['text'] }}</text>
  @endforeach
  @endif

</svg>