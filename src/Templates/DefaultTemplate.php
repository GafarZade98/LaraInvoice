<?php

namespace GafarZade98\LaraInvoice\Templates;

use GafarZade98\LaraInvoice\Contracts\TemplateInterface;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Invoice;

/**
 * Default A4 SVG template.
 *
 * Coordinate system: pt units (1 pt = 1/72 inch), A4 width = 595 pt.
 * Height is dynamic — grows with item count, minimum 842 pt (A4).
 */
class DefaultTemplate implements TemplateInterface
{
    // ------------------------------------------------------------------
    // Layout constants
    // ------------------------------------------------------------------

    private const W          = 595;   // page width
    private const MARGIN     = 40;    // left/right margin
    private const CONTENT_W  = 515;   // W - 2*MARGIN
    private const RIGHT      = 555;   // MARGIN + CONTENT_W

    // Item table column X positions
    // Description | Qty | Unit Price | Tax% | Amount
    private const COL_DESC      = self::MARGIN;      // x=40
    private const COL_DESC_W    = 195;
    private const COL_QTY       = 235;               // x=235
    private const COL_QTY_W     = 45;
    private const COL_UPRICE    = 280;               // x=280
    private const COL_UPRICE_W  = 85;
    private const COL_TAX       = 365;               // x=365
    private const COL_TAX_W     = 55;
    private const COL_AMT       = 420;               // x=420
    private const COL_AMT_W     = 135;               // extends to RIGHT (555)

    // Summary section right-side column positions
    private const SUM_LABEL_X   = 370;               // right-align label here
    private const SUM_VALUE_X   = self::RIGHT;       // right-align value here

    // Colors
    private const COLOR_GREY    = '#666666';
    private const COLOR_LIGHT   = '#999999';
    private const COLOR_RULE    = '#e0e0e0';
    private const COLOR_BLACK   = '#1a1a1a';

    // ------------------------------------------------------------------
    // State
    // ------------------------------------------------------------------

    private float $y = 0;
    private array $els = [];

    // ------------------------------------------------------------------
    // TemplateInterface
    // ------------------------------------------------------------------

    public function render(Invoice $invoice): string
    {
        $this->y   = 0;
        $this->els = [];

        $this->drawHeaderBar($invoice);
        $this->drawTitleArea($invoice);
        $this->drawRule();
        $this->drawMetadata($invoice);
        $this->drawRule();
        $this->drawParties($invoice);
        $this->drawSummaryLine($invoice);
        $this->drawItemsTable($invoice);
        $this->drawSummarySection($invoice);

        if ($invoice->getNotes()) {
            $this->drawNotes($invoice);
        }

        if ($this->isRefunded($invoice)) {
            $this->drawRefundInstructions($invoice);
        }

        $height = max(842, $this->y + 60);

        return $this->buildSvg($height);
    }

    // ------------------------------------------------------------------
    // Section renderers
    // ------------------------------------------------------------------

    private function drawHeaderBar(Invoice $invoice): void
    {
        $color = htmlspecialchars($invoice->getBrandColor(), ENT_XML1);
        $this->el("<rect x=\"0\" y=\"0\" width=\"" . self::W . "\" height=\"8\" fill=\"{$color}\"/>");
        $this->y = 8;
    }

    private function drawTitleArea(Invoice $invoice): void
    {
        $this->y += 20;

        $logoUri = $invoice->logoDataUri();
        if ($logoUri) {
            $logoX = self::RIGHT - 130;
            $this->el("<image x=\"{$logoX}\" y=\"{$this->y}\" width=\"130\" height=\"50\" href=\"" . htmlspecialchars($logoUri, ENT_XML1) . "\" preserveAspectRatio=\"xMaxYMid meet\"/>");
        }

        $titleY = $this->y + 32;
        $title  = htmlspecialchars($invoice->resolveTitle(), ENT_XML1);
        $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$titleY}\" font-family=\"sans-serif\" font-size=\"22\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">{$title}</text>");

        if ($invoice->getNumber()) {
            $numY = $titleY + 16;
            $num  = htmlspecialchars($invoice->getNumber(), ENT_XML1);
            $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$numY}\" font-family=\"sans-serif\" font-size=\"9\" fill=\"" . self::COLOR_GREY . "\">{$num}</text>");
        }

        $this->y += 58;
    }

    private function drawMetadata(Invoice $invoice): void
    {
        $this->y += 14;

        $rows = [];

        if ($invoice->getDate()) {
            $rows[] = ['Date', $invoice->getDate()->format('F j, Y')];
        }

        if ($invoice->getDueDate()) {
            $rows[] = ['Due date', $invoice->getDueDate()->format('F j, Y')];
        }

        if ($pm = $invoice->getPaymentMethod()) {
            $rows[] = ['Payment method', $pm->getLabel()];
        }

        foreach ($invoice->getTaxes() as $tax) {
            if ($tax->getId()) {
                $rows[] = [$tax->getType(), $tax->getId()];
            }
        }

        foreach ($invoice->getCustomFields() as $key => $value) {
            $rows[] = [ucwords(str_replace('_', ' ', $key)), (string) $value];
        }

        $labelX = self::MARGIN;
        $valueX = self::MARGIN + 130;

        foreach ($rows as [$label, $value]) {
            $ly = $this->y + 9;
            $l  = htmlspecialchars($label, ENT_XML1);
            $v  = htmlspecialchars($value, ENT_XML1);
            $this->el("<text x=\"{$labelX}\" y=\"{$ly}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$l}</text>");
            $this->el("<text x=\"{$valueX}\" y=\"{$ly}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_BLACK . "\">{$v}</text>");
            $this->y += 15;
        }

        $this->y += 6;
    }

    private function drawParties(Invoice $invoice): void
    {
        $this->y += 14;
        $startY    = $this->y;
        $leftX     = self::MARGIN;
        $rightX    = self::MARGIN + 265;

        // --- Seller (left) ---
        if ($seller = $invoice->getSeller()) {
            $this->y = $startY;

            if ($seller->getName()) {
                $v = htmlspecialchars($seller->getName(), ENT_XML1);
                $this->el("<text x=\"{$leftX}\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"9\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">{$v}</text>");
                $this->y += 13;
            }

            foreach (array_merge(
                $seller->getAddress()?->toLines() ?? [],
                array_filter([$seller->getPhone(), $seller->getEmail()])
            ) as $line) {
                $v = htmlspecialchars($line, ENT_XML1);
                $this->el("<text x=\"{$leftX}\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$v}</text>");
                $this->y += 12;
            }

            foreach ($seller->getCustomFields() as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $v     = htmlspecialchars("{$label}: {$value}", ENT_XML1);
                $this->el("<text x=\"{$leftX}\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$v}</text>");
                $this->y += 12;
            }
        }

        $leftBottomY = $this->y;

        // --- Buyer (right) ---
        if ($buyer = $invoice->getBuyer()) {
            $rightY = $startY;

            $this->el("<text x=\"{$rightX}\" y=\"{$rightY}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">Bill to</text>");
            $rightY += 13;

            if ($buyer->getName()) {
                $v = htmlspecialchars($buyer->getName(), ENT_XML1);
                $this->el("<text x=\"{$rightX}\" y=\"{$rightY}\" font-family=\"sans-serif\" font-size=\"9\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">{$v}</text>");
                $rightY += 13;
            }

            foreach (array_merge(
                $buyer->getAddress()?->toLines() ?? [],
                array_filter([$buyer->getPhone(), $buyer->getEmail()])
            ) as $line) {
                $v = htmlspecialchars($line, ENT_XML1);
                $this->el("<text x=\"{$rightX}\" y=\"{$rightY}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$v}</text>");
                $rightY += 12;
            }

            foreach ($buyer->getCustomFields() as $key => $value) {
                $label  = ucwords(str_replace('_', ' ', $key));
                $v      = htmlspecialchars("{$label}: {$value}", ENT_XML1);
                $this->el("<text x=\"{$rightX}\" y=\"{$rightY}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$v}</text>");
                $rightY += 12;
            }

            $leftBottomY = max($leftBottomY, $rightY);
        }

        $this->y = $leftBottomY + 10;
    }

    private function drawSummaryLine(Invoice $invoice): void
    {
        if (!$invoice->getTotal() && !$invoice->getSubtotal()) {
            return;
        }

        $this->y += 6;

        $amount = $invoice->formatMoney($invoice->getTotal());
        $date   = $invoice->getDate()?->format('F j, Y') ?? '';
        $status = strtolower((string) $invoice->getStatus());

        $label = match (true) {
            $this->isRefunded($invoice) => "{$amount} refunded" . ($date ? " on {$date}" : ''),
            $status === 'pending'        => "{$amount} due"      . ($date ? " on {$date}" : ''),
            default                      => "{$amount} paid"     . ($date ? " on {$date}" : ''),
        };

        $label = htmlspecialchars($label, ENT_XML1);
        $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"12\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">{$label}</text>");
        $this->y += 20;
    }

    private function drawItemsTable(Invoice $invoice): void
    {
        $this->drawRule();
        $this->y += 8;

        $hasTax         = !empty($invoice->getTaxes()) || $this->anyItemHasTax($invoice);
        $hasItemDiscount = $this->anyItemHasDiscount($invoice);

        // Column layout — positions shift when optional columns are added
        // qty_cx: center X of Qty | up_rx: right X of Unit Price
        // disc_cx: center X of Discount (null = hidden) | tax_cx: center X of Tax (null = hidden)
        $layout = $this->buildColumnLayout($hasTax, $hasItemDiscount);

        // Table header
        $hy = $this->y + 8;
        $this->tableHeaderText(self::MARGIN, $hy, 'Description', 'start');
        $this->tableHeaderText($layout['qty_cx'], $hy, 'Qty', 'middle');
        $this->tableHeaderText($layout['up_rx'], $hy, 'Unit Price', 'end');

        if ($hasItemDiscount) {
            $this->tableHeaderText($layout['disc_cx'], $hy, 'Discount', 'middle');
        }
        if ($hasTax) {
            $this->tableHeaderText($layout['tax_cx'], $hy, 'Tax', 'middle');
        }

        $this->tableHeaderText(self::RIGHT, $hy, 'Amount', 'end');
        $this->y += 18;

        $this->el("<line x1=\"" . self::MARGIN . "\" y1=\"{$this->y}\" x2=\"" . self::RIGHT . "\" y2=\"{$this->y}\" stroke=\"" . self::COLOR_BLACK . "\" stroke-width=\"0.5\"/>");
        $this->y += 8;

        foreach ($invoice->getItems() as $item) {
            $this->drawItemRow($item, $invoice, $hasTax, $hasItemDiscount, $layout);
        }

        $this->y += 4;
    }

    /** @return array{qty_cx:float,up_rx:float,disc_cx:float|null,tax_cx:float|null} */
    private function buildColumnLayout(bool $hasTax, bool $hasDisc): array
    {
        if ($hasDisc && $hasTax) {
            // Desc:40-185(145) | Qty:185-225(40) | UPrice:225-290(65) | Disc:290-385(95) | Tax:385-415(30) | Amt:415-555(140)
            return ['qty_cx' => 205.0, 'up_rx' => 290.0, 'disc_cx' => 337.5, 'tax_cx' => 400.0];
        }
        if ($hasDisc) {
            // Desc:40-185(145) | Qty:185-225(40) | UPrice:225-295(70) | Disc:295-410(115) | Amt:410-555(145)
            return ['qty_cx' => 205.0, 'up_rx' => 295.0, 'disc_cx' => 352.5, 'tax_cx' => null];
        }
        if ($hasTax) {
            return ['qty_cx' => 257.5, 'up_rx' => 365.0, 'disc_cx' => null, 'tax_cx' => 392.5];
        }
        return ['qty_cx' => 257.5, 'up_rx' => 365.0, 'disc_cx' => null, 'tax_cx' => null];
    }

    private function drawItemRow(InvoiceItem $item, Invoice $invoice, bool $hasTax, bool $hasItemDiscount, array $layout): void
    {
        $rowStartY = $this->y;
        $nameY     = $this->y + 10;

        // Name
        $name = htmlspecialchars($item->getName() ?: $item->getDescription(), ENT_XML1);
        $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$nameY}\" font-family=\"sans-serif\" font-size=\"9\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">{$name}</text>");

        $descY = $nameY + 11;
        if ($item->getDescription() && $item->getName()) {
            $desc = htmlspecialchars($item->getDescription(), ENT_XML1);
            $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$descY}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$desc}</text>");
            $descY += 11;
        }

        if ($item->getExtraDescription()) {
            $extra = htmlspecialchars($item->getExtraDescription(), ENT_XML1);
            $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$descY}\" font-family=\"sans-serif\" font-size=\"7\" fill=\"" . self::COLOR_LIGHT . "\" font-style=\"italic\">{$extra}</text>");
        }

        // Qty
        $qty = $item->getQuantity() == (int) $item->getQuantity()
            ? (string) (int) $item->getQuantity()
            : (string) $item->getQuantity();
        $this->el("<text x=\"{$layout['qty_cx']}\" y=\"{$nameY}\" font-family=\"sans-serif\" font-size=\"9\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"middle\">{$qty}</text>");

        // Unit price
        $up = htmlspecialchars($invoice->formatMoney($item->getUnitPrice()), ENT_XML1);
        $this->el("<text x=\"{$layout['up_rx']}\" y=\"{$nameY}\" font-family=\"sans-serif\" font-size=\"9\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"end\">{$up}</text>");

        // Discount column
        if ($hasItemDiscount && $layout['disc_cx'] !== null) {
            $discY = $nameY;
            foreach ($item->getDiscounts() as $disc) {
                $val  = $disc->isPercentage()
                    ? "-{$disc->getRate()}%"
                    : $invoice->formatMoney(-$disc->getAmount());
                $dTxt = htmlspecialchars("{$disc->getName()} {$val}", ENT_XML1);
                $this->el("<text x=\"{$layout['disc_cx']}\" y=\"{$discY}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"middle\">{$dTxt}</text>");
                $discY += 11;
            }
        }

        // Tax column — item taxes or fallback to invoice-level taxes
        if ($hasTax && $layout['tax_cx'] !== null) {
            $taxes = !empty($item->getTaxes()) ? $item->getTaxes() : $invoice->getTaxes();
            if (!empty($taxes)) {
                $rates = array_map(fn($t) => $t->getRate() . '%', $taxes);
                $txTxt = htmlspecialchars(implode(', ', $rates), ENT_XML1);
                $this->el("<text x=\"{$layout['tax_cx']}\" y=\"{$nameY}\" font-family=\"sans-serif\" font-size=\"9\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"middle\">{$txTxt}</text>");
            }
        }

        // Total amount
        $amt = htmlspecialchars($invoice->formatMoney($item->getTotal()), ENT_XML1);
        $this->el("<text x=\"" . self::RIGHT . "\" y=\"{$nameY}\" font-family=\"sans-serif\" font-size=\"9\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"end\">{$amt}</text>");

        $rowHeight = 10 + 8;
        if ($item->getDescription() && $item->getName()) {
            $rowHeight += 11;
        }
        if ($item->getExtraDescription()) {
            $rowHeight += 11;
        }

        $this->y = $rowStartY + $rowHeight;

        $this->el("<line x1=\"" . self::MARGIN . "\" y1=\"{$this->y}\" x2=\"" . self::RIGHT . "\" y2=\"{$this->y}\" stroke=\"" . self::COLOR_RULE . "\" stroke-width=\"0.5\"/>");
        $this->y += 4;
    }

    private function drawSummarySection(Invoice $invoice): void
    {
        if (!$invoice->getTotal() && !$invoice->getSubtotal()) {
            return;
        }

        $this->y += 8;

        $rows = [];

        $rows[] = ['Subtotal', $invoice->formatMoney($invoice->getSubtotal()), false];

        // Item-level discounts aggregate
        if (($itemDisc = $invoice->getTotalItemDiscounts()) > 0) {
            $rows[] = ['Item Discounts', $invoice->formatMoney(-$itemDisc), false];
        }

        // Invoice-level discounts — grouped into one line or listed separately
        if ($groupLabel = $invoice->getGroupDiscountsAs()) {
            if ($invoice->getTotalDiscount() > 0) {
                $totalRate = $invoice->getTotalDiscountRate();
                $label     = $totalRate > 0 ? "{$groupLabel} ({$totalRate}%)" : $groupLabel;
                $rows[] = [$label, $invoice->formatMoney(-$invoice->getTotalDiscount()), false];
            }
        } else {
            foreach ($invoice->getDiscounts() as $disc) {
                $label = $disc->getName();
                if ($disc->isPercentage()) {
                    $label .= " ({$disc->getRate()}%)";
                }
                $rows[] = [$label, $invoice->formatMoney(-$invoice->getTotalDiscountFor($disc)), false];
            }
        }

        // Total excluding tax — shown only when taxes exist, so user knows the tax base
        $hasTaxLines = !empty($invoice->getTaxes());
        if ($hasTaxLines) {
            $rows[] = ['Total excl. tax', $invoice->formatMoney($invoice->getTaxBase()), false];
        }

        // Taxes — grouped into one line or listed separately
        if ($groupLabel = $invoice->getGroupTaxesAs()) {
            if ($invoice->getTotalTax() > 0) {
                $totalRate = $invoice->getTotalTaxRate();
                $label     = $totalRate > 0 ? "{$groupLabel} ({$totalRate}%)" : $groupLabel;
                $rows[] = [$label, $invoice->formatMoney($invoice->getTotalTax()), false];
            }
        } else {
            foreach ($invoice->getTaxes() as $tax) {
                $label = $tax->getType();
                if ($tax->getRate()) {
                    $label .= " ({$tax->getRate()}%)";
                }
                $rows[] = [$label, $invoice->formatMoney($invoice->getTotalTaxFor($tax)), false];
            }
        }

        $rows[] = ['Total', $invoice->formatMoney($invoice->getTotal()), false];

        $status = strtolower((string) $invoice->getStatus());
        if ($status === 'pending') {
            $rows[] = ['Amount due', $invoice->formatMoney($invoice->getDue()), true];
        } else {
            $rows[] = ['Amount paid', $invoice->formatMoney($invoice->getPaid()), true];
        }

        if ($this->isRefunded($invoice) && $invoice->getPaid() > 0) {
            $rows[] = ['Total refunded', $invoice->formatMoney($invoice->getPaid()), true];
        }

        foreach ($rows as [$label, $value, $bold]) {
            $ly    = $this->y + 10;
            $lEsc  = htmlspecialchars($label, ENT_XML1);
            $vEsc  = htmlspecialchars($value, ENT_XML1);
            $fw    = $bold ? 'bold' : 'normal';
            $fSize = $bold ? '10' : '9';

            $this->el("<text x=\"" . self::SUM_LABEL_X . "\" y=\"{$ly}\" font-family=\"sans-serif\" font-size=\"{$fSize}\" font-weight=\"{$fw}\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"end\">{$lEsc}</text>");
            $this->el("<text x=\"" . self::SUM_VALUE_X . "\" y=\"{$ly}\" font-family=\"sans-serif\" font-size=\"{$fSize}\" font-weight=\"{$fw}\" fill=\"" . self::COLOR_BLACK . "\" text-anchor=\"end\">{$vEsc}</text>");

            if ($bold) {
                $lineY = $this->y;
                $this->els[] = "<line x1=\"" . self::SUM_LABEL_X . "\" y1=\"{$lineY}\" x2=\"" . self::SUM_VALUE_X . "\" y2=\"{$lineY}\" stroke=\"" . self::COLOR_BLACK . "\" stroke-width=\"0.5\"/>";
            }

            $this->y += 18;
        }
    }

    private function drawNotes(Invoice $invoice): void
    {
        $this->y += 14;
        $notes = htmlspecialchars($invoice->getNotes(), ENT_XML1);
        $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">Notes: {$notes}</text>");
        $this->y += 16;
    }

    private function drawRefundInstructions(Invoice $invoice): void
    {
        $this->y += 20;

        $this->el("<line x1=\"" . self::MARGIN . "\" y1=\"{$this->y}\" x2=\"" . self::RIGHT . "\" y2=\"{$this->y}\" stroke=\"" . self::COLOR_RULE . "\" stroke-width=\"0.5\"/>");
        $this->y += 12;

        $sellerName = htmlspecialchars($invoice->getSeller()?->getName() ?? 'the issuer', ENT_XML1);

        $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"9\" font-weight=\"bold\" fill=\"" . self::COLOR_BLACK . "\">Refund instructions</text>");
        $this->y += 14;

        $lines = [
            "Your refund has been issued by {$sellerName}.",
            'It may take about 5 to 10 business days to appear on your statement.',
            'If it takes longer, please contact your bank for assistance.',
        ];

        foreach ($lines as $line) {
            $l = htmlspecialchars($line, ENT_XML1);
            $this->el("<text x=\"" . self::MARGIN . "\" y=\"{$this->y}\" font-family=\"sans-serif\" font-size=\"8\" fill=\"" . self::COLOR_GREY . "\">{$l}</text>");
            $this->y += 12;
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function drawRule(): void
    {
        $this->el("<line x1=\"" . self::MARGIN . "\" y1=\"{$this->y}\" x2=\"" . self::RIGHT . "\" y2=\"{$this->y}\" stroke=\"" . self::COLOR_RULE . "\" stroke-width=\"0.5\"/>");
        $this->y += 1;
    }

    private function tableHeaderText(float $x, float $y, string $text, string $anchor): void
    {
        $t = htmlspecialchars($text, ENT_XML1);
        $this->el("<text x=\"{$x}\" y=\"{$y}\" font-family=\"sans-serif\" font-size=\"7\" font-weight=\"bold\" fill=\"" . self::COLOR_GREY . "\" text-anchor=\"{$anchor}\" text-transform=\"uppercase\">{$t}</text>");
    }

    private function el(string $element): void
    {
        $this->els[] = $element;
    }

    private function buildSvg(float $height): string
    {
        $h    = (int) ceil($height);
        $body = implode("\n  ", $this->els);

        return <<<SVG
            <?xml version="1.0" encoding="UTF-8"?>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                 viewBox="0 0 595 {$h}" width="595" height="{$h}">
              <rect width="595" height="{$h}" fill="white"/>
              {$body}
            </svg>
            SVG;
    }

    private function isRefunded(Invoice $invoice): bool
    {
        return in_array(strtolower((string) $invoice->getStatus()), [
            'refunded', 'partial', 'disputed',
        ]);
    }

    private function anyItemHasTax(Invoice $invoice): bool
    {
        foreach ($invoice->getItems() as $item) {
            if (!empty($item->getTaxes())) {
                return true;
            }
        }
        return false;
    }

    private function anyItemHasDiscount(Invoice $invoice): bool
    {
        foreach ($invoice->getItems() as $item) {
            if (!empty($item->getDiscounts())) {
                return true;
            }
        }
        return false;
    }
}