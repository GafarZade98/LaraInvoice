<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Templates;

use GafarZade98\LaraInvoice\Invoice;

final class DefaultTemplate extends AbstractTemplate
{
    protected function view(): string
    {
        return 'larainvoice::templates.default';
    }

    protected function layout(Invoice $invoice): array
    {
        $items = $invoice->getItems();

        // Discount column: per-item only; Tax column: invoice-level OR per-item
        $hasItemDiscount = collect($items)->contains(fn ($i) => count($i->getDiscounts()) > 0);
        $hasItemTax      = count($invoice->getTaxes()) > 0
            || collect($items)->contains(fn ($i) => count($i->getTaxes()) > 0);

        // Summary flags (invoice-level OR per-item)
        $hasTax      = $hasItemTax;
        $hasDiscount = count($invoice->getDiscounts()) > 0 || $hasItemDiscount;

        $tableLayout = match ([$hasItemDiscount, $hasItemTax]) {
            [false, false] => [55, 12, 18,          15],
            [true,  false] => [45, 10, 15, 14,      16],
            [false, true]  => [45, 10, 15,      14, 16],
            [true,  true]  => [40, 10, 14, 12, 10,  14],
        };

        $columns    = ['Description', 'Qty', 'Unit Price'];
        $widths     = [$tableLayout[0], $tableLayout[1], $tableLayout[2]];
        $alignments = ['left', 'center', 'right'];
        if ($hasItemDiscount) { $columns[] = 'Discount'; $widths[] = $tableLayout[3];                                      $alignments[] = 'right'; }
        if ($hasItemTax)      { $columns[] = 'Tax';      $widths[] = $hasItemDiscount ? $tableLayout[4] : $tableLayout[3]; $alignments[] = 'right'; }
        $columns[]    = 'Amount';
        $widths[]     = $tableLayout[array_key_last($tableLayout)];
        $alignments[] = 'right';

        $hasDescriptions = collect($items)->contains(fn($i) => $i->getDescription() !== '');
        $rowHeight       = $hasDescriptions ? 44 : 28;

        $itemDiscounts = [];
        foreach ($items as $idx => $item) {
            $lineTotal = $item->getQuantity() * $item->getUnitPrice();
            $disc      = 0.0;
            foreach ($item->getDiscounts() as $d) {
                $disc += $d->isPercentage()
                    ? $lineTotal * ($d->getRate() / 100)
                    : $d->getAmount();
            }
            $itemDiscounts[$idx] = $disc;
        }

        return [
            'invoice'         => $invoice,
            'hasTax'          => $hasTax,
            'hasDiscount'     => $hasDiscount,
            'hasItemTax'      => $hasItemTax,
            'hasItemDiscount' => $hasItemDiscount,
            'columns'         => $columns,
            'widths'          => $widths,
            'alignments'      => $alignments,
            'itemDiscounts'   => $itemDiscounts,
            'rowHeight'       => $rowHeight,
        ];
    }
}