<?php

namespace GafarZade98\LaraInvoice\Tests\Unit;

use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Data\Tax;
use GafarZade98\LaraInvoice\Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_total_from_quantity_and_unit_price(): void
    {
        $item = InvoiceItem::make()->quantity(3)->unitPrice(100.00);

        $this->assertSame(300.0, $item->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_subtracts_multiple_discounts_from_total(): void
    {
        $item = InvoiceItem::make()
            ->quantity(1)
            ->unitPrice(200.00)
            ->addDiscount(Discount::make()->value(20.00))
            ->addDiscount(Discount::make()->value(10.00));

        $this->assertSame(170.0, $item->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_adds_multiple_taxes_to_total(): void
    {
        $item = InvoiceItem::make()
            ->quantity(1)
            ->unitPrice(100.00)
            ->addTax(Tax::make()->rate(10)->amount(10.00))
            ->addTax(Tax::make()->rate(5)->amount(5.00));

        $this->assertSame(115.0, $item->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_both_discount_and_tax_together(): void
    {
        $item = InvoiceItem::make()
            ->quantity(2)
            ->unitPrice(100.00)
            ->addDiscount(Discount::make()->value(30.00))
            ->addTax(Tax::make()->rate(10)->amount(17.00));

        // (2 × 100) - 30 + 17 = 187
        $this->assertSame(187.0, $item->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_uses_explicit_total_when_set(): void
    {
        $item = InvoiceItem::make()->quantity(5)->unitPrice(100.00)->total(999.99);

        $this->assertSame(999.99, $item->getTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_all_taxes_and_discounts(): void
    {
        $item = InvoiceItem::make()
            ->addTax(Tax::make()->rate(10)->amount(10))
            ->addTax(Tax::make()->rate(5)->amount(5))
            ->addDiscount(Discount::make()->value(20))
            ->addDiscount(Discount::make()->value(30));

        $this->assertCount(2, $item->getTaxes());
        $this->assertCount(2, $item->getDiscounts());
    }
}