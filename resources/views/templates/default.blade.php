{{--
    Default invoice template — component-based, renders to pure SVG.
    $invoice       \GafarZade98\LaraInvoice\Invoice
    $hasTax        bool   — injected by DefaultTemplate::layout()
    $hasDiscount   bool   — injected by DefaultTemplate::layout()
    $columns       array  — table column labels
    $widths        array  — table column widths (%)
    $itemDiscounts array  — pre-computed per-item discount totals (indexed by item position)
--}}
<x-invoice::page size="A4" :margin="40">

    {{-- ── Header: title + invoice meta LEFT  |  logo RIGHT ── --}}
    <x-invoice::row>

        <x-invoice::col width="58%">
            <x-invoice::text :size="26" :bold="true" color="{{ $invoice->getBrandColor() }}" :mb="12">
                {{ $invoice->resolveTitle() }}
            </x-invoice::text>
            @if($invoice->getNumber())
                <x-invoice::key-value label="Invoice number" :value="$invoice->getNumber()" :size="9" color="#444444" label-color="#888888"/>
            @endif
            @if($invoice->getPaymentMethod())
                <x-invoice::key-value label="Payment method" :value="$invoice->getPaymentMethod()->getLabel()" :size="9" color="#444444" label-color="#888888"/>
            @endif
            @if($invoice->getDate())
                <x-invoice::key-value
                    label="Date of issue"
                    :value="$invoice->getDate()->format(config('invoice.date_format', 'F j, Y'))"
                    :size="9" color="#444444" label-color="#888888"/>
            @endif
            @if($invoice->getDueDate())
                <x-invoice::key-value
                    label="Date due"
                    :value="$invoice->getDueDate()->format(config('invoice.date_format', 'F j, Y'))"
                    :size="9" color="#444444" label-color="#888888"/>
            @endif
        </x-invoice::col>

        <x-invoice::col width="42%" align="right">
            @if($invoice->getLogo())
                <x-invoice::image :src="$invoice->getLogo()" :height="48" :mb="0"/>
            @endif
        </x-invoice::col>

    </x-invoice::row>

    <x-invoice::spacer :height="24"/>
    <x-invoice::divider color="#e0e0e0"/>
    <x-invoice::spacer :height="20"/>

    {{-- ── Seller LEFT  |  Buyer RIGHT ── --}}
    <x-invoice::row>

        <x-invoice::col width="50%">
            <x-invoice::text :size="7.5" :bold="true" color="#aaaaaa" transform="uppercase" :mb="6">
                From
            </x-invoice::text>
            @if($invoice->getSeller()?->getName())
                <x-invoice::text :size="10" :bold="true" color="#333333">
                    {{ $invoice->getSeller()->getName() }}
                </x-invoice::text>
            @endif
            @foreach($invoice->getSeller()?->getAddress()?->toLines() ?? [] as $line)
                <x-invoice::text :size="8.5" color="#666666" :mb="1">{{ $line }}</x-invoice::text>
            @endforeach
            @if($invoice->getSeller()?->getEmail())
                <x-invoice::text :size="8.5" color="#666666" :mb="1">{{ $invoice->getSeller()->getEmail() }}</x-invoice::text>
            @endif
        </x-invoice::col>

        <x-invoice::col width="50%">
            <x-invoice::text :size="7.5" :bold="true" color="#aaaaaa" transform="uppercase" :mb="6">
                Bill To
            </x-invoice::text>
            @if($invoice->getBuyer()?->getName())
                <x-invoice::text :size="10" :bold="true" color="#333333">
                    {{ $invoice->getBuyer()->getName() }}
                </x-invoice::text>
            @endif
            @foreach($invoice->getBuyer()?->getAddress()?->toLines() ?? [] as $line)
                <x-invoice::text :size="8.5" color="#666666" :mb="1">{{ $line }}</x-invoice::text>
            @endforeach
            @if($invoice->getBuyer()?->getEmail())
                <x-invoice::text :size="8.5" color="#666666" :mb="1">{{ $invoice->getBuyer()->getEmail() }}</x-invoice::text>
            @endif
            @foreach($invoice->getBuyer()?->getCustomFields() ?? [] as $label => $value)
                <x-invoice::text :size="8.5" color="#666666" :mb="1">{{ $label }}: {{ $value }}</x-invoice::text>
            @endforeach
        </x-invoice::col>

    </x-invoice::row>

    <x-invoice::spacer :height="20"/>
    <x-invoice::divider color="#e0e0e0"/>
    <x-invoice::spacer :height="14"/>

    {{-- ── Amount due / paid / refunded sentence ── --}}
    @php
        $amountSentence = $invoice->formatMoney($invoice->getTotal());
        if ($invoice->getStatus()?->isRefunded()) {
            $amountSentence .= ' refunded';
        } elseif ($invoice->getStatus()?->value === 'paid') {
            $amountSentence .= ' paid';
        } else {
            $amountSentence .= ' due';
            if ($invoice->getDueDate()) {
                $amountSentence .= ' on ' . $invoice->getDueDate()->format(config('invoice.date_format', 'F j, Y'));
            }
        }
    @endphp
    <x-invoice::text :size="13" :bold="true" color="#222222" :mb="0">
        {{ $amountSentence }}
    </x-invoice::text>

    <x-invoice::spacer :height="20"/>

    {{-- ── Items table ── --}}
    <x-invoice::table
        :columns="$columns"
        :widths="$widths"
        :alignments="$alignments"
        header-background="transparent"
        header-color="#555555"
        :header-size="9"
        :row-height="$rowHeight"
        :cell-padding="8"
    >
        @foreach($invoice->getItems() as $idx => $item)
            <x-invoice::table-row :striped="$loop->even">

                <x-invoice::cell :sub="$item->getDescription() ?: null">{{ $item->getName() }}</x-invoice::cell>

                <x-invoice::cell align="center">{{ $item->getQuantity() % 1 === 0.0 ? number_format($item->getQuantity(), 0) : $item->getQuantity() }}</x-invoice::cell>

                <x-invoice::cell align="right">{{ $invoice->formatMoney($item->getUnitPrice()) }}</x-invoice::cell>

                @if($hasItemDiscount)
                    <x-invoice::cell align="right" color="#cc3333">{{ $itemDiscounts[$idx] > 0 ? '-' . $invoice->formatMoney($itemDiscounts[$idx]) : '' }}</x-invoice::cell>
                @endif

                @if($hasItemTax)
                    @php
                        $rowTaxLabel = collect(array_merge($invoice->getTaxes(), $item->getTaxes()))
                            ->map(fn($t) => $t->getRate() . '%')
                            ->unique()
                            ->join('+');
                    @endphp
                    <x-invoice::cell align="right" color="#557799">{{ $rowTaxLabel }}</x-invoice::cell>
                @endif

                <x-invoice::cell align="right" :bold="true">{{ $invoice->formatMoney($item->getTotal()) }}</x-invoice::cell>

            </x-invoice::table-row>
        @endforeach
    </x-invoice::table>

    <x-invoice::spacer :height="18"/>

    {{-- ── Summary ── --}}
    <x-invoice::row>
        <x-invoice::col width="55%"/>
        <x-invoice::col width="45%">

            <x-invoice::key-value label="Subtotal"
                :value="$invoice->formatMoney($invoice->getSubtotal())"
                :size="9" color="#444444"/>

            @if($invoice->getTotalItemDiscounts() > 0)
                <x-invoice::key-value label="Item Discounts"
                    :value="'-' . $invoice->formatMoney($invoice->getTotalItemDiscounts())"
                    :size="9" color="#cc3333"/>
            @endif

            @if($invoice->getGroupDiscountsAs())
                <x-invoice::key-value :label="$invoice->getGroupDiscountsAs()"
                    :value="'-' . $invoice->formatMoney($invoice->getTotalDiscount())"
                    :size="9" color="#cc3333"/>
            @else
                @foreach($invoice->getDiscounts() as $d)
                    <x-invoice::key-value
                        :label="$d->getName() . ($d->isPercentage() ? ' (' . $d->getRate() . '%)' : '')"
                        :value="'-' . $invoice->formatMoney($invoice->getTotalDiscountFor($d))"
                        :size="9" color="#cc3333"/>
                @endforeach
            @endif

            @if($hasTax)
                <x-invoice::key-value label="Tax Base"
                    :value="$invoice->formatMoney($invoice->getTaxBase())"
                    :size="9" color="#777777"/>
                @if($invoice->getGroupTaxesAs())
                    <x-invoice::key-value :label="$invoice->getGroupTaxesAs()"
                        :value="$invoice->formatMoney($invoice->getTotalTax())"
                        :size="9" color="#557799"/>
                @else
                    @foreach($invoice->getTaxes() as $t)
                        <x-invoice::key-value
                            :label="$t->getType() . ($t->getRate() > 0 ? ' (' . $t->getRate() . '%)' : '')"
                            :value="$invoice->formatMoney($invoice->getTotalTaxFor($t))"
                            :size="9" color="#557799"/>
                    @endforeach
                @endif
            @endif

            <x-invoice::divider color="#333333" :stroke-width="0.75" :mb="6"/>

            <x-invoice::key-value label="Total"
                :value="$invoice->formatMoney($invoice->getTotal())"
                :size="12" :bold="true" color="#222222"/>

            @if($invoice->getStatus()?->isRefunded())
                <x-invoice::key-value label="Total refunded"
                    :value="$invoice->formatMoney($invoice->getPaid())"
                    :size="9" color="#cc3333" :mb="2"/>
            @endif

        </x-invoice::col>
    </x-invoice::row>

    {{-- ── Notes ── --}}
    @if($invoice->getNotes())
        <x-invoice::spacer :height="32"/>
        <x-invoice::divider color="#eeeeee"/>
        <x-invoice::spacer :height="10"/>
        <x-invoice::text :size="8" color="#999999">{{ $invoice->getNotes() }}</x-invoice::text>
    @endif

    {{-- ── Refund instructions ── --}}
    @if($invoice->getStatus()?->isRefunded())
        <x-invoice::spacer :height="24"/>
        <x-invoice::divider color="#e0e0e0"/>
        <x-invoice::spacer :height="12"/>
        <x-invoice::text :size="9" :bold="true" color="#333333" :mb="4">Refund instructions</x-invoice::text>
        <x-invoice::text :size="8.5" color="#666666">
            Your refund has been processed. Please allow 5–10 business days for the funds to appear in your account.
            Contact us if you have any questions regarding this refund.
        </x-invoice::text>
    @endif

</x-invoice::page>