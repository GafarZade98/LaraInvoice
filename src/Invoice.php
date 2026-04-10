<?php

namespace GafarZade98\LaraInvoice;

use Carbon\Carbon;
use GafarZade98\LaraInvoice\Contracts\TemplateInterface;
use GafarZade98\LaraInvoice\Data\Buyer;
use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Data\PaymentMethod;
use GafarZade98\LaraInvoice\Data\Seller;
use GafarZade98\LaraInvoice\Data\Tax;
use GafarZade98\LaraInvoice\Enums\InvoiceStatus;
use GafarZade98\LaraInvoice\Renderer\PdfRenderer;
use GafarZade98\LaraInvoice\Renderer\SvgRenderer;
use GafarZade98\LaraInvoice\Templates\DefaultTemplate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class Invoice
{
    private ?string $number = null;
    private ?InvoiceStatus $status = null;
    private ?string $title = null;
    private ?Carbon $date = null;
    private ?Carbon $dueDate = null;
    private ?string $notes = null;
    private array $customFields = [];

    private ?Seller $seller = null;
    private ?Buyer $buyer = null;

    // Money fields
    private ?string $currency = null;
    private ?string $symbol = null;
    private int $decimals = 2;
    private float $paid = 0;
    private float $due = 0;
    private ?PaymentMethod $paymentMethod = null;

    // If set, discounts/taxes are collapsed into one summary line with this label.
    private ?string $groupDiscountsAs = null;
    private ?string $groupTaxesAs = null;

    /** @var Tax[] */
    private array $taxes = [];

    /** @var Discount[] */
    private array $discounts = [];

    private array $items = [];

    private ?string $logo = null;
    private ?string $brandColor = null;

    private ?TemplateInterface $template = null;

    public static function make(): static
    {
        return new static();
    }

    // -------------------------------------------------------------------------
    // Meta
    // -------------------------------------------------------------------------

    public function number(string $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function status(InvoiceStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function date(string|Carbon $date): static
    {
        $this->date = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $this;
    }

    public function dueDate(string|Carbon $dueDate): static
    {
        $this->dueDate = $dueDate instanceof Carbon ? $dueDate : Carbon::parse($dueDate);
        return $this;
    }

    public function notes(string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function customFields(array $customFields): static
    {
        $this->customFields = $customFields;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Parties
    // -------------------------------------------------------------------------

    public function seller(Seller $seller): static
    {
        $this->seller = $seller;
        return $this;
    }

    public function buyer(Buyer $buyer): static
    {
        $this->buyer = $buyer;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Money
    // -------------------------------------------------------------------------

    public function currency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function symbol(string $symbol): static
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function paid(float $paid): static
    {
        $this->paid = $paid;
        return $this;
    }

    public function due(float $due): static
    {
        $this->due = $due;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Payment method
    // -------------------------------------------------------------------------

    public function paymentMethod(PaymentMethod $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Display grouping
    // -------------------------------------------------------------------------

    /**
     * Collapse all discounts into one summary line.
     * Pass a custom label or leave default ('Discount').
     * Call without argument to un-group (show separately).
     */
    public function groupDiscountsAs(string $label = 'Discount'): static
    {
        $this->groupDiscountsAs = $label;
        return $this;
    }

    /**
     * Collapse all taxes into one summary line.
     * Pass a custom label or leave default ('Tax').
     * Call without argument to un-group (show separately).
     */
    public function groupTaxesAs(string $label = 'Tax'): static
    {
        $this->groupTaxesAs = $label;
        return $this;
    }

    public function getGroupDiscountsAs(): ?string
    {
        return $this->groupDiscountsAs;
    }

    public function getGroupTaxesAs(): ?string
    {
        return $this->groupTaxesAs;
    }

    // -------------------------------------------------------------------------
    // Taxes
    // -------------------------------------------------------------------------

    public function addTax(Tax $tax): static
    {
        $this->taxes[] = $tax;
        return $this;
    }

    public function taxes(array $taxes): static
    {
        $this->taxes = $taxes;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Discounts
    // -------------------------------------------------------------------------

    public function addDiscount(Discount $discount): static
    {
        $this->discounts[] = $discount;
        return $this;
    }

    public function discounts(array $discounts): static
    {
        $this->discounts = $discounts;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Items
    // -------------------------------------------------------------------------

    public function addItem(InvoiceItem $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    public function items(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Appearance
    // -------------------------------------------------------------------------

    public function logo(string $path): static
    {
        $this->logo = $path;
        return $this;
    }

    public function brandColor(string $color): static
    {
        $this->brandColor = $color;
        return $this;
    }

    public function template(TemplateInterface $template): static
    {
        $this->template = $template;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getStatus(): ?InvoiceStatus
    {
        return $this->status;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDate(): ?Carbon
    {
        return $this->date;
    }

    public function getDueDate(): ?Carbon
    {
        return $this->dueDate;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function getBuyer(): ?Buyer
    {
        return $this->buyer;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getSubtotal(): float
    {
        return array_sum(
            array_map(fn($item) => $item->getQuantity() * $item->getUnitPrice(), $this->items)
        );
    }

    public function getTotalDiscount(): float
    {
        $base  = $this->getSubtotal() - $this->getTotalItemDiscounts();
        $total = 0.0;

        foreach ($this->discounts as $discount) {
            $total += $discount->isPercentage()
                ? $base * ($discount->getRate() / 100)
                : $discount->getAmount();
        }

        return $total;
    }

    /** The amount taxes are calculated on: subtotal − item discounts − invoice discounts. */
    public function getTaxBase(): float
    {
        return $this->getSubtotal() - $this->getTotalItemDiscounts() - $this->getTotalDiscount();
    }

    public function getTotalTax(): float
    {
        $base  = $this->getTaxBase();
        $total = 0.0;

        foreach ($this->taxes as $tax) {
            $total += $tax->getRate() > 0
                ? $base * ($tax->getRate() / 100)
                : $tax->getAmount();
        }

        return $total;
    }

    public function getTotal(): float
    {
        return $this->getTaxBase() + $this->getTotalTax();
    }

    /** Sum of all item-level discount amounts. */
    public function getTotalItemDiscounts(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $lineTotal = $item->getQuantity() * $item->getUnitPrice();
            foreach ($item->getDiscounts() as $disc) {
                $total += $disc->isPercentage()
                    ? $lineTotal * ($disc->getRate() / 100)
                    : $disc->getAmount();
            }
        }
        return $total;
    }

    /** Sum of all tax rates across rate-based taxes. */
    public function getTotalTaxRate(): float
    {
        $rate = 0.0;
        foreach ($this->taxes as $tax) {
            $rate += $tax->getRate();
        }
        return $rate;
    }

    /** Sum of all percentage rates across percentage-type discounts. */
    public function getTotalDiscountRate(): float
    {
        $rate = 0.0;
        foreach ($this->discounts as $discount) {
            if ($discount->isPercentage()) {
                $rate += $discount->getRate();
            }
        }
        return $rate;
    }

    /** Resolved amount for a single discount (percentage base = subtotal − itemDiscounts). */
    public function getTotalDiscountFor(Discount $discount): float
    {
        $base = $this->getSubtotal() - $this->getTotalItemDiscounts();

        return $discount->isPercentage()
            ? $base * ($discount->getRate() / 100)
            : $discount->getAmount();
    }

    /** Resolved amount for a single tax (rate base = subtotal − itemDiscounts − invoiceDiscount). */
    public function getTotalTaxFor(Tax $tax): float
    {
        $base = $this->getSubtotal() - $this->getTotalItemDiscounts() - $this->getTotalDiscount();

        return $tax->getRate() > 0
            ? $base * ($tax->getRate() / 100)
            : $tax->getAmount();
    }

    public function getPaid(): float
    {
        return $this->paid > 0 ? $this->paid : $this->getTotal();
    }

    public function getDue(): float
    {
        return $this->due > 0 ? $this->due : $this->getTotal();
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /** @return Tax[] */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    /** @return Discount[] */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /** @return InvoiceItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getLogo(): ?string
    {
        return $this->logo ?? config('invoice.logo');
    }

    public function getBrandColor(): string
    {
        return $this->brandColor ?? config('invoice.brand_color', '#008080');
    }

    public function getTemplate(): TemplateInterface
    {
        if ($this->template) {
            return $this->template;
        }

        $templateClass = config('invoice.template', DefaultTemplate::class);

        return new $templateClass();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function resolveTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        return match ($this->status) {
            InvoiceStatus::Paid                                                  => 'Receipt',
            InvoiceStatus::Refunded, InvoiceStatus::PartialRefund, InvoiceStatus::Disputed => 'Refund',
            default                                                              => 'Invoice',
        };
    }

    public function formatMoney(float $amount): string
    {
        $negative  = $amount < 0;
        $formatted = number_format(abs($amount), $this->decimals, '.', ',');
        $prefix    = $this->symbol ?? ($this->currency ? $this->currency . ' ' : '');

        return ($negative ? '-' : '') . $prefix . $formatted;
    }

    public function logoDataUri(): ?string
    {
        $path = $this->getLogo();

        if (!$path || !file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $data = base64_encode(file_get_contents($path));

        return "data:{$mime};base64,{$data}";
    }

    private function getDocumentType(): string
    {
        return match ($this->status) {
            InvoiceStatus::Paid                                                       => 'receipt',
            InvoiceStatus::Refunded, InvoiceStatus::PartialRefund, InvoiceStatus::Disputed => 'refund',
            default                                                                   => 'invoice',
        };
    }

    // -------------------------------------------------------------------------
    // Output
    // -------------------------------------------------------------------------

    public function toSvg(): string
    {
        return (new SvgRenderer())->render($this);
    }

    public function toPdf(): string
    {
        return (new PdfRenderer())->convert($this->toSvg());
    }

    public function saveSvg(string $disk, string $path): void
    {
        Storage::disk($disk)->put($path, $this->toSvg());
    }

    public function savePdf(string $disk, string $path): void
    {
        Storage::disk($disk)->put($path, $this->toPdf());
    }

    public function downloadSvg(?string $filename = null): Response
    {
        $type     = $this->getDocumentType();
        $filename ??= "{$type}-{$this->number}.svg";

        return response($this->toSvg(), 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadPdf(?string $filename = null): Response
    {
        $type     = $this->getDocumentType();
        $filename ??= "{$type}-{$this->number}.pdf";

        return response($this->toPdf(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function streamPdf(?string $filename = null): Response
    {
        $type     = $this->getDocumentType();
        $filename ??= "{$type}-{$this->number}.pdf";

        return response($this->toPdf(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}