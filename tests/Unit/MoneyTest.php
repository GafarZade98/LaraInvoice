<?php

namespace GafarZade98\LaraInvoice\Tests\Unit;

use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Tests\TestCase;

class MoneyTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_with_symbol(): void
    {
        $invoice = Invoice::make()->symbol('$')->decimals(2);

        $this->assertSame('$1,200.00', $invoice->formatMoney(1200.0));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_with_currency_code_when_no_symbol(): void
    {
        $invoice = Invoice::make()->currency('USD')->decimals(2);

        $this->assertSame('USD 99.50', $invoice->formatMoney(99.5));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_negative_amounts(): void
    {
        $invoice = Invoice::make()->symbol('$');

        $this->assertSame('-$50.00', $invoice->formatMoney(-50.0));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_respects_decimal_places(): void
    {
        $invoice = Invoice::make()->symbol('€')->decimals(0);

        $this->assertSame('€1,500', $invoice->formatMoney(1500.0));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_without_prefix_when_no_currency_set(): void
    {
        $invoice = Invoice::make();

        $this->assertSame('1,000.00', $invoice->formatMoney(1000.0));
    }
}