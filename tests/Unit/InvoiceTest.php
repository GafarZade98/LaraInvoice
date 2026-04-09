<?php

namespace GafarZade98\LaraInvoice\Tests\Unit;

use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Enums\DiscountType;
use GafarZade98\LaraInvoice\Enums\InvoiceStatus;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Data\Tax;
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Tests\TestCase;

class InvoiceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resolves_title_from_status(): void
    {
        $this->assertSame('Receipt', Invoice::make()->status(InvoiceStatus::Paid)->resolveTitle());
        $this->assertSame('Refund',  Invoice::make()->status(InvoiceStatus::Refunded)->resolveTitle());
        $this->assertSame('Refund',  Invoice::make()->status(InvoiceStatus::Partial)->resolveTitle());
        $this->assertSame('Invoice', Invoice::make()->status(InvoiceStatus::Pending)->resolveTitle());
        $this->assertSame('Invoice', Invoice::make()->resolveTitle());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_uses_explicit_title_over_status(): void
    {
        $invoice = Invoice::make()->status(InvoiceStatus::Paid)->title('My Custom Receipt');

        $this->assertSame('My Custom Receipt', $invoice->resolveTitle());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_money_without_currency_set(): void
    {
        $invoice = Invoice::make();

        $this->assertSame('1,500.00', $invoice->formatMoney(1500.0));
        $this->assertSame('-200.00',  $invoice->formatMoney(-200.0));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_parses_date_strings(): void
    {
        $invoice = Invoice::make()->date('2026-04-08')->dueDate('2026-04-22');

        $this->assertSame('April 8, 2026',  $invoice->getDate()->format('F j, Y'));
        $this->assertSame('April 22, 2026', $invoice->getDueDate()->format('F j, Y'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_chains_items_fluently(): void
    {
        $invoice = Invoice::make();

        $this->assertCount(0, $invoice->getItems());

        $invoice->addItem(InvoiceItem::make()->name('A'));
        $invoice->addItem(InvoiceItem::make()->name('B'));

        $this->assertCount(2, $invoice->getItems());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accumulates_taxes_with_addTax(): void
    {
        $invoice = Invoice::make()
            ->addTax(Tax::make()->type('VAT')->rate(10))
            ->addTax(Tax::make()->type('WHT')->rate(5));

        $this->assertCount(2, $invoice->getTaxes());
        $this->assertSame('VAT', $invoice->getTaxes()[0]->getType());
        $this->assertSame('WHT', $invoice->getTaxes()[1]->getType());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accumulates_discounts_with_addDiscount(): void
    {
        $invoice = Invoice::make()
            ->addDiscount(Discount::make()->name('Promo')->value(50))
            ->addDiscount(Discount::make()->name('Loyalty')->value(30));

        $this->assertCount(2, $invoice->getDiscounts());
        $this->assertSame('Promo',   $invoice->getDiscounts()[0]->getName());
        $this->assertSame('Loyalty', $invoice->getDiscounts()[1]->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_default_brand_color(): void
    {
        $this->assertSame('#008080', Invoice::make()->getBrandColor());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_overrides_brand_color(): void
    {
        $this->assertSame('#ff6600', Invoice::make()->brandColor('#ff6600')->getBrandColor());
    }

    // -------------------------------------------------------------------------
    // Computed: subtotal
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_computes_subtotal_as_sum_of_qty_times_unit_price(): void
    {
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(2)->unitPrice(100.0))  // 200
            ->addItem(InvoiceItem::make()->quantity(3)->unitPrice(50.0));  // 150

        $this->assertSame(350.0, $invoice->getSubtotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_subtotal_with_no_items(): void
    {
        $this->assertSame(0.0, Invoice::make()->getSubtotal());
    }

    // -------------------------------------------------------------------------
    // Computed: totalDiscount
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_computes_percentage_discount_against_subtotal(): void
    {
        // subtotal = 1000, 10% = 100
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(10));

        $this->assertSame(100.0, $invoice->getTotalDiscount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_computes_fixed_discount_from_amount(): void
    {
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addDiscount(Discount::make()->type(DiscountType::Fixed)->value(75.0));

        $this->assertSame(75.0, $invoice->getTotalDiscount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sums_multiple_discounts_of_mixed_types(): void
    {
        // subtotal = 1000, 10% = 100 + fixed 35 = 135
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(10))
            ->addDiscount(Discount::make()->type(DiscountType::Fixed)->value(35.0));

        $this->assertSame(135.0, $invoice->getTotalDiscount());
    }

    // -------------------------------------------------------------------------
    // Computed: totalTax
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_tax_rate_to_discounted_subtotal(): void
    {
        // subtotal=1000, discount=100 → base=900, VAT 10%=90
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(10))
            ->addTax(Tax::make()->rate(10));

        $this->assertSame(90.0, $invoice->getTotalTax());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sums_multiple_tax_rates(): void
    {
        // subtotal=1000, no discount, VAT 15%=150 + WHT 3%=30 = 180
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addTax(Tax::make()->rate(15))
            ->addTax(Tax::make()->rate(3));

        $this->assertSame(180.0, $invoice->getTotalTax());
    }

    // -------------------------------------------------------------------------
    // Computed: total
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_computes_total_as_subtotal_minus_discount_plus_tax(): void
    {
        // subtotal=1000, 10% disc=100 → base=900, VAT 10%=90 → total=990
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(1)->unitPrice(1000.0))
            ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(10))
            ->addTax(Tax::make()->rate(10));

        $this->assertSame(990.0, $invoice->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_invoice_discount_on_subtotal_minus_item_discounts(): void
    {
        // subtotal=1000, item discount 20%=200 → net=800
        // invoice discount 10% of 800=80 → tax base=720
        // VAT 10% of 720=72 → total=800-80+72=792
        $invoice = Invoice::make()
            ->addItem(
                InvoiceItem::make()
                    ->quantity(1)
                    ->unitPrice(1000.0)
                    ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(20))
            )
            ->addDiscount(Discount::make()->type(DiscountType::Percentage)->value(10))
            ->addTax(Tax::make()->rate(10));

        $this->assertSame(200.0, $invoice->getTotalItemDiscounts());
        $this->assertSame(80.0,  $invoice->getTotalDiscount());   // 10% of (1000-200)=800
        $this->assertSame(72.0,  $invoice->getTotalTax());        // 10% of (800-80)=720
        $this->assertSame(792.0, $invoice->getTotal());           // 1000-200-80+72
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_computes_total_with_no_discounts_or_taxes(): void
    {
        $invoice = Invoice::make()
            ->addItem(InvoiceItem::make()->quantity(2)->unitPrice(150.0));

        $this->assertSame(300.0, $invoice->getTotal());
    }
}