<?php

namespace GafarZade98\LaraInvoice\View;

use GafarZade98\LaraInvoice\Enums\InvoiceStatus;
use GafarZade98\LaraInvoice\Invoice;

/**
 * Computes all absolute Y-coordinates and section data from an Invoice.
 * Returns a flat data array ready to be passed to a Blade view.
 */
class LayoutBuilder
{
    private const W           = 595;
    private const MARGIN      = 40;
    private const RIGHT       = 555;
    private const SUM_LABEL_X = 370;

    private const COLOR_BLACK = '#1a1a1a';
    private const COLOR_GREY  = '#666666';
    private const COLOR_LIGHT = '#999999';
    private const COLOR_RULE  = '#e0e0e0';

    private float $y = 0;

    private function __construct(private readonly Invoice $invoice) {}

    public static function build(Invoice $invoice): array
    {
        return (new self($invoice))->compute();
    }

    // ------------------------------------------------------------------
    // Main
    // ------------------------------------------------------------------

    private function compute(): array
    {
        $this->y = 0;

        $colors = [
            'brand' => $this->invoice->getBrandColor(),
            'black' => self::COLOR_BLACK,
            'grey'  => self::COLOR_GREY,
            'light' => self::COLOR_LIGHT,
            'rule'  => self::COLOR_RULE,
        ];

        ['logo' => $logo, 'header' => $header] = $this->header();

        $meta           = $this->meta();
        $parties        = $this->parties();
        $summaryLine    = $this->summaryLine();
        $table          = $this->table();
        $summarySection = $this->summarySection();
        $notes          = $this->notes();
        $refund         = $this->refund();

        $height = (int) ceil(max(842, $this->y + 60));

        return [
            'width'          => self::W,
            'height'         => $height,
            'margin'         => self::MARGIN,
            'right'          => self::RIGHT,
            'colors'         => $colors,
            'logo'           => $logo,
            'header'         => $header,
            'meta'           => $meta,
            'parties'        => $parties,
            'summaryLine'    => $summaryLine,
            'table'          => $table,
            'summarySection' => $summarySection,
            'notes'          => $notes,
            'refund'         => $refund,
        ];
    }

    // ------------------------------------------------------------------
    // Sections
    // ------------------------------------------------------------------

    private function header(): array
    {
        $this->y = 8;
        $this->y += 20;

        $logoUri = $this->invoice->logoDataUri();
        $logo    = $logoUri ? [
            'x'   => self::RIGHT - 130,
            'y'   => $this->y,
            'w'   => 130,
            'h'   => 50,
            'uri' => $logoUri,
        ] : null;

        $titleY  = $this->y + 32;
        $numberY = $titleY + 16;
        $this->y += 58;

        return [
            'logo'   => $logo,
            'header' => [
                'title'  => ['x' => self::MARGIN, 'y' => $titleY,  'text' => $this->invoice->resolveTitle()],
                'number' => $this->invoice->getNumber()
                    ? ['x' => self::MARGIN, 'y' => $numberY, 'text' => $this->invoice->getNumber()]
                    : null,
            ],
        ];
    }

    private function meta(): array
    {
        $ruleY    = $this->y; $this->y += 1;
        $this->y += 14;

        $inv  = $this->invoice;
        $rows = [];

        if ($inv->getDate())    $rows[] = ['label' => 'Date',           'value' => $inv->getDate()->format('F j, Y')];
        if ($inv->getDueDate()) $rows[] = ['label' => 'Due date',       'value' => $inv->getDueDate()->format('F j, Y')];
        if ($pm = $inv->getPaymentMethod()) $rows[] = ['label' => 'Payment method', 'value' => $pm->getLabel()];

        foreach ($inv->getTaxes() as $tax) {
            if ($tax->getId()) $rows[] = ['label' => $tax->getType(), 'value' => $tax->getId()];
        }
        foreach ($inv->getCustomFields() as $k => $v) {
            $rows[] = ['label' => ucwords(str_replace('_', ' ', $k)), 'value' => (string) $v];
        }

        foreach ($rows as &$row) {
            $row['y'] = $this->y + 9;
            $this->y += 15;
        }
        unset($row);
        $this->y += 6;

        return [
            'ruleY'  => $ruleY,
            'labelX' => self::MARGIN,
            'valueX' => self::MARGIN + 130,
            'rows'   => $rows,
        ];
    }

    private function parties(): array
    {
        $ruleY    = $this->y; $this->y += 1;
        $this->y += 14;
        $startY   = $this->y;

        $inv         = $this->invoice;
        $sellerLines = [];
        $sy          = $startY;

        if ($seller = $inv->getSeller()) {
            if ($seller->getName()) {
                $sellerLines[] = ['text' => $seller->getName(), 'bold' => true, 'y' => $sy];
                $sy += 13;
            }
            foreach (array_merge(
                $seller->getAddress()?->toLines() ?? [],
                array_filter([$seller->getPhone(), $seller->getEmail()])
            ) as $line) {
                $sellerLines[] = ['text' => $line, 'bold' => false, 'y' => $sy];
                $sy += 12;
            }
            foreach ($seller->getCustomFields() as $k => $v) {
                $label = ucwords(str_replace('_', ' ', $k));
                $sellerLines[] = ['text' => "$label: $v", 'bold' => false, 'y' => $sy];
                $sy += 12;
            }
            $this->y = $sy;
        }

        $leftBottomY = $this->y;
        $buyerLines  = [];
        $buyerLabelY = null;
        $by          = $startY;

        if ($buyer = $inv->getBuyer()) {
            $buyerLabelY = $by;
            $by += 13;
            if ($buyer->getName()) {
                $buyerLines[] = ['text' => $buyer->getName(), 'bold' => true, 'y' => $by];
                $by += 13;
            }
            foreach (array_merge(
                $buyer->getAddress()?->toLines() ?? [],
                array_filter([$buyer->getPhone(), $buyer->getEmail()])
            ) as $line) {
                $buyerLines[] = ['text' => $line, 'bold' => false, 'y' => $by];
                $by += 12;
            }
            foreach ($buyer->getCustomFields() as $k => $v) {
                $label = ucwords(str_replace('_', ' ', $k));
                $buyerLines[] = ['text' => "$label: $v", 'bold' => false, 'y' => $by];
                $by += 12;
            }
            $leftBottomY = max($leftBottomY, $by);
        }

        $this->y = $leftBottomY + 10;

        return [
            'ruleY'       => $ruleY,
            'sellerX'     => self::MARGIN,
            'buyerX'      => self::MARGIN + 265,
            'seller'      => $sellerLines,
            'buyerLabelY' => $buyerLabelY,
            'buyer'       => $buyerLines,
        ];
    }

    private function summaryLine(): ?array
    {
        $inv = $this->invoice;
        if (!$inv->getTotal() && !$inv->getSubtotal()) {
            return null;
        }

        $this->y += 6;
        $amount   = $inv->formatMoney($inv->getTotal());
        $date     = $inv->getDate()?->format('F j, Y') ?? '';

        $text = match (true) {
            $this->isRefunded()                           => "$amount refunded" . ($date ? " on $date" : ''),
            $this->invoice->getStatus() === InvoiceStatus::Pending => "$amount due" . ($date ? " on $date" : ''),
            default                                       => "$amount paid"     . ($date ? " on $date" : ''),
        };

        $line    = ['x' => self::MARGIN, 'y' => $this->y, 'text' => $text];
        $this->y += 20;

        return $line;
    }

    private function table(): array
    {
        $inv         = $this->invoice;
        $hasTax      = !empty($inv->getTaxes()) || $this->anyItemHasTax();
        $hasDiscount = $this->anyItemHasDiscount();
        $layout      = $this->columnLayout($hasTax, $hasDiscount);

        $ruleY = $this->y; $this->y += 1;
        $this->y += 8;

        $headerY  = $this->y + 8; $this->y += 18;
        $dividerY = $this->y;     $this->y += 8;

        $items = [];
        foreach ($inv->getItems() as $item) {
            $rowStartY = $this->y;
            $nameY     = $this->y + 10;

            $name  = $item->getName() ?: $item->getDescription();
            $desc  = ($item->getDescription() && $item->getName()) ? $item->getDescription() : null;
            $extra = $item->getExtraDescription() ?: null;

            $qty = $item->getQuantity() == (int) $item->getQuantity()
                ? (string) (int) $item->getQuantity()
                : (string) $item->getQuantity();

            $discounts = [];
            if ($hasDiscount && $layout['disc_cx'] !== null) {
                $dy = $nameY;
                foreach ($item->getDiscounts() as $disc) {
                    $val = $disc->isPercentage()
                        ? "-{$disc->getRate()}%"
                        : $inv->formatMoney(-$disc->getAmount());
                    $discounts[] = ['text' => "{$disc->getName()} $val", 'y' => $dy];
                    $dy += 11;
                }
            }

            $tax = null;
            if ($hasTax && $layout['tax_cx'] !== null) {
                $taxes = !empty($item->getTaxes()) ? $item->getTaxes() : $inv->getTaxes();
                if (!empty($taxes)) {
                    $tax = [
                        'text' => implode(', ', array_map(fn ($t) => $t->getRate() . '%', $taxes)),
                        'y'    => $nameY,
                    ];
                }
            }

            $rowHeight = 18 + ($desc ? 11 : 0) + ($extra ? 11 : 0);

            $items[] = [
                'name'      => ['text' => $name,                                    'y' => $nameY],
                'desc'      => $desc  ? ['text' => $desc,  'y' => $nameY + 11]    : null,
                'extra'     => $extra ? ['text' => $extra, 'y' => $desc ? $nameY + 22 : $nameY + 11] : null,
                'qty'       => ['text' => $qty,                                     'y' => $nameY],
                'price'     => ['text' => $inv->formatMoney($item->getUnitPrice()), 'y' => $nameY],
                'discounts' => $discounts,
                'tax'       => $tax,
                'amount'    => ['text' => $inv->formatMoney($item->getTotal()),     'y' => $nameY],
                'ruleY'     => $rowStartY + $rowHeight,
            ];

            $this->y = $rowStartY + $rowHeight + 4;
        }
        $this->y += 4;

        return compact('ruleY', 'headerY', 'dividerY', 'hasTax', 'hasDiscount', 'layout', 'items');
    }

    private function summarySection(): array
    {
        $inv  = $this->invoice;
        $rows = [];

        if (!$inv->getTotal() && !$inv->getSubtotal()) {
            return ['labelX' => self::SUM_LABEL_X, 'valueX' => self::RIGHT, 'rows' => []];
        }

        $this->y += 8;

        $add = function (string $label, string $value, bool $bold) use (&$rows): void {
            $rows[] = [
                'label' => $label,
                'value' => $value,
                'bold'  => $bold,
                'y'     => $this->y + 10,
                'lineY' => $bold ? $this->y : null,
            ];
            $this->y += 18;
        };

        $add('Subtotal', $inv->formatMoney($inv->getSubtotal()), false);

        if (($itemDisc = $inv->getTotalItemDiscounts()) > 0) {
            $add('Item Discounts', $inv->formatMoney(-$itemDisc), false);
        }

        if ($groupLabel = $inv->getGroupDiscountsAs()) {
            if ($inv->getTotalDiscount() > 0) {
                $rate  = $inv->getTotalDiscountRate();
                $label = $rate > 0 ? "$groupLabel ($rate%)" : $groupLabel;
                $add($label, $inv->formatMoney(-$inv->getTotalDiscount()), false);
            }
        } else {
            foreach ($inv->getDiscounts() as $disc) {
                $label = $disc->getName() . ($disc->isPercentage() ? " ({$disc->getRate()}%)" : '');
                $add($label, $inv->formatMoney(-$inv->getTotalDiscountFor($disc)), false);
            }
        }

        if (!empty($inv->getTaxes())) {
            $add('Total excl. tax', $inv->formatMoney($inv->getTaxBase()), false);
        }

        if ($groupLabel = $inv->getGroupTaxesAs()) {
            if ($inv->getTotalTax() > 0) {
                $rate  = $inv->getTotalTaxRate();
                $label = $rate > 0 ? "$groupLabel ($rate%)" : $groupLabel;
                $add($label, $inv->formatMoney($inv->getTotalTax()), false);
            }
        } else {
            foreach ($inv->getTaxes() as $tax) {
                $label = $tax->getType() . ($tax->getRate() ? " ({$tax->getRate()}%)" : '');
                $add($label, $inv->formatMoney($inv->getTotalTaxFor($tax)), false);
            }
        }

        $add('Total', $inv->formatMoney($inv->getTotal()), false);

        if ($inv->getStatus() === InvoiceStatus::Pending) {
            $add('Amount due',  $inv->formatMoney($inv->getDue()),  true);
        } else {
            $add('Amount paid', $inv->formatMoney($inv->getPaid()), true);
        }

        if ($this->isRefunded() && $inv->getPaid() > 0) {
            $add('Total refunded', $inv->formatMoney($inv->getPaid()), true);
        }

        return ['labelX' => self::SUM_LABEL_X, 'valueX' => self::RIGHT, 'rows' => $rows];
    }

    private function notes(): ?array
    {
        if (!$this->invoice->getNotes()) {
            return null;
        }
        $this->y += 14;
        $note     = ['x' => self::MARGIN, 'y' => $this->y, 'text' => 'Notes: ' . $this->invoice->getNotes()];
        $this->y += 16;

        return $note;
    }

    private function refund(): ?array
    {
        if (!$this->isRefunded()) {
            return null;
        }

        $this->y += 20;
        $ruleY    = $this->y; $this->y += 12;
        $titleY   = $this->y; $this->y += 14;

        $sellerName = $this->invoice->getSeller()?->getName() ?? 'the issuer';
        $lines      = [];

        foreach ([
            "Your refund has been issued by $sellerName.",
            'It may take about 5 to 10 business days to appear on your statement.',
            'If it takes longer, please contact your bank for assistance.',
        ] as $line) {
            $lines[] = ['text' => $line, 'y' => $this->y];
            $this->y += 12;
        }

        return ['ruleY' => $ruleY, 'titleY' => $titleY, 'x' => self::MARGIN, 'lines' => $lines];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function isRefunded(): bool
    {
        return $this->invoice->getStatus()?->isRefunded() ?? false;
    }

    private function anyItemHasTax(): bool
    {
        foreach ($this->invoice->getItems() as $item) {
            if (!empty($item->getTaxes())) return true;
        }
        return false;
    }

    private function anyItemHasDiscount(): bool
    {
        foreach ($this->invoice->getItems() as $item) {
            if (!empty($item->getDiscounts())) return true;
        }
        return false;
    }

    private function columnLayout(bool $hasTax, bool $hasDisc): array
    {
        if ($hasDisc && $hasTax) return ['qty_cx' => 205.0, 'up_rx' => 290.0, 'disc_cx' => 337.5, 'tax_cx' => 400.0];
        if ($hasDisc)            return ['qty_cx' => 205.0, 'up_rx' => 295.0, 'disc_cx' => 352.5, 'tax_cx' => null];
        if ($hasTax)             return ['qty_cx' => 257.5, 'up_rx' => 365.0, 'disc_cx' => null,   'tax_cx' => 392.5];
        return                          ['qty_cx' => 257.5, 'up_rx' => 365.0, 'disc_cx' => null,   'tax_cx' => null];
    }
}