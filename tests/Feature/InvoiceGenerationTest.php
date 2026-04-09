<?php

namespace GafarZade98\LaraInvoice\Tests\Feature;

use GafarZade98\LaraInvoice\Data\Address;
use GafarZade98\LaraInvoice\Data\Buyer;
use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Enums\DiscountType;
use GafarZade98\LaraInvoice\Enums\InvoiceStatus;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Data\PaymentMethod;
use GafarZade98\LaraInvoice\Data\Seller;
use GafarZade98\LaraInvoice\Data\Tax;
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    private function buildSampleInvoice(): Invoice
    {
        // subtotal: 1*800 + 2*200 = 1200
        // tax: VAT 11% of 1200 = 132  →  total = 1332
        return Invoice::make()
            ->number('INV-00042')
            ->status(InvoiceStatus::Pending)
            ->date('2026-04-08')
            ->dueDate('2026-04-22')
            ->notes('Payment via bank transfer. Please include invoice number in the reference.')
            ->brandColor('#4f46e5')
            ->symbol('$')
            ->paid(0)
            ->due(1332.00)
            ->seller(
                Seller::make()
                    ->name('Acme Software LLC')
                    ->email('billing@acme.io')
                    ->address('123 Tech Street, San Francisco, CA 94105')
                    ->phone('+1 (415) 555-0100')
                    ->customFields(['VAT ID' => 'US123456789'])
            )
            ->buyer(
                Buyer::make()
                    ->name('John Doe')
                    ->email('john@example.com')
                    ->address('456 Market Ave, New York, NY 10001')
                    ->phone('+1 (212) 555-0199')
            )
            ->addTax(Tax::make()->type('VAT')->rate(11)->id('VAT-2026-001'))
            ->paymentMethod(PaymentMethod::make()->label('Bank Transfer'))
            ->addItem(
                InvoiceItem::make()
                    ->name('Laravel Package Development')
                    ->description('Custom LaraInvoice SVG/PDF package')
                    ->quantity(1)
                    ->unitPrice(800.00)
            )
            ->addItem(
                InvoiceItem::make()
                    ->name('API Integration')
                    ->description('REST API wiring and authentication layer')
                    ->extraDescription('Includes Postman collection')
                    ->quantity(2)
                    ->unitPrice(200.00)
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_valid_svg_output(): void
    {
        $svg = $this->buildSampleInvoice()->toSvg();

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('INV-00042', $svg);
        $this->assertStringContainsString('Acme Software LLC', $svg);
        $this->assertStringContainsString('John Doe', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_svg_to_disk_and_is_viewable(): void
    {
        $path = $this->outputPath('sample-invoice.svg');

        $svg = $this->buildSampleInvoice()->toSvg();
        file_put_contents($path, $svg);

        $this->assertFileExists($path);
        $this->assertGreaterThan(1000, filesize($path));

        fwrite(STDOUT, "\n  SVG saved → {$path}\n");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_paid_invoice_as_receipt(): void
    {
        // subtotal: 1*500 = 500, no tax → total = 500
        $svg = Invoice::make()
            ->number('RCP-0001')
            ->status(InvoiceStatus::Paid)
            ->date('2026-04-01')
            ->symbol('$')
            ->paid(500)
            ->seller(Seller::make()->name('Acme LLC'))
            ->buyer(Buyer::make()->name('Jane Smith'))
            ->addItem(InvoiceItem::make()->name('Consulting')->quantity(1)->unitPrice(500))
            ->toSvg();

        $this->assertStringContainsString('Receipt', $svg);
        $this->assertStringContainsString('Amount paid', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_paid_invoice_svg(): void
    {
        $path = $this->outputPath('receipt.svg');

        $svg = Invoice::make()
            ->number('RCP-0001')
            ->status(InvoiceStatus::Paid)
            ->date('2026-04-01')
            ->brandColor('#16a34a')
            ->symbol('$')
            ->paid(500)
            ->seller(Seller::make()->name('Acme LLC')->email('billing@acme.io')->address('San Francisco, CA'))
            ->buyer(Buyer::make()->name('Jane Smith')->email('jane@example.com'))
            ->addItem(InvoiceItem::make()->name('Consulting')->description('1-hour strategy session')->quantity(1)->unitPrice(500))
            ->toSvg();

        file_put_contents($path, $svg);
        $this->assertFileExists($path);

        fwrite(STDOUT, "\n  SVG saved → {$path}\n");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_refunded_invoice(): void
    {
        $svg = Invoice::make()
            ->number('REF-0010')
            ->status(InvoiceStatus::Refunded)
            ->date('2026-04-05')
            ->symbol('$')
            ->paid(300)
            ->seller(Seller::make()->name('Acme LLC'))
            ->buyer(Buyer::make()->name('Bob Builder'))
            ->addItem(InvoiceItem::make()->name('Plugin License')->quantity(1)->unitPrice(300))
            ->toSvg();

        $this->assertStringContainsString('Refund', $svg);
        $this->assertStringContainsString('Refund instructions', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_multiple_discounts_in_summary(): void
    {
        // subtotal=1000, 10% disc=100 + fixed $35=35 → total=865
        $svg = Invoice::make()
            ->number('INV-0099')
            ->status(InvoiceStatus::Pending)
            ->symbol('$')
            ->due(865)
            ->seller(Seller::make()->name('Acme LLC'))
            ->buyer(Buyer::make()->name('Buyer Co'))
            ->addDiscount(Discount::make()->name('Loyalty Discount')->type(DiscountType::Percentage)->value(10))
            ->addDiscount(Discount::make()->name('Promo Code')->type(DiscountType::Fixed)->value(35))
            ->addItem(InvoiceItem::make()->name('Service')->quantity(1)->unitPrice(1000))
            ->toSvg();

        $this->assertStringContainsString('Loyalty Discount', $svg);
        $this->assertStringContainsString('Promo Code', $svg);
        $this->assertStringContainsString('10%', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_multiple_taxes_in_summary_and_item_column(): void
    {
        // subtotal=1000, VAT 15%=150 + WHT 3%=30 → total=1180
        $svg = Invoice::make()
            ->number('INV-0100')
            ->status(InvoiceStatus::Pending)
            ->symbol('$')
            ->due(1180)
            ->addTax(Tax::make()->type('VAT')->rate(15))
            ->addTax(Tax::make()->type('WHT')->rate(3))
            ->seller(Seller::make()->name('Acme LLC'))
            ->buyer(Buyer::make()->name('Buyer Co'))
            ->addItem(InvoiceItem::make()->name('Hosting')->quantity(1)->unitPrice(1000))
            ->toSvg();

        $this->assertStringContainsString('VAT', $svg);
        $this->assertStringContainsString('WHT', $svg);
        $this->assertStringContainsString('15%', $svg);
        $this->assertStringContainsString('3%', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_supports_per_item_taxes_and_discounts(): void
    {
        $svg = Invoice::make()
            ->number('INV-0101')
            ->status(InvoiceStatus::Pending)
            ->symbol('$')
            ->seller(Seller::make()->name('Acme LLC'))
            ->buyer(Buyer::make()->name('Buyer Co'))
            ->addItem(
                InvoiceItem::make()
                    ->name('Software License')
                    ->quantity(1)
                    ->unitPrice(500)
                    ->addTax(Tax::make()->type('VAT')->rate(10)->amount(50))
                    ->addDiscount(Discount::make()->name('Beta Discount')->type(DiscountType::Fixed)->value(10))
            )
            ->toSvg();

        $this->assertStringContainsString('Software License', $svg);
        $this->assertStringContainsString('10%', $svg);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_full_featured_invoice(): void
    {
        $path = $this->outputPath('full-invoice.svg');

        $svg = Invoice::make()
            ->number('INV-2026-007')
            ->status(InvoiceStatus::Paid)
            ->date('2026-04-08')
            ->dueDate('2026-05-08')
            ->brandColor('#dc2626')
            ->notes('Net 30 terms. Late payments subject to 1.5% monthly interest.')
            ->symbol('$')
            ->addTax(Tax::make()->type('VAT')->rate(5)->id('TX-9900'))
            ->addDiscount(Discount::make()->name('Early Bird')->type(DiscountType::Percentage)->value(15))
            ->addDiscount(Discount::make()->name('Elvin')->type(DiscountType::Percentage)->value(20))
            ->groupDiscountsAs('Total Discount')
            ->groupTaxesAs('Total Tax')
            ->paymentMethod(PaymentMethod::make()->label('Wire Transfer'))
            ->seller(
                Seller::make()
                    ->name('Red Studio Inc.')
                    ->email('hello@redstudio.dev')
                    ->address(Address::make()->country('Ireland')->address('123 Main St, Dublin 2')->city('Dublin'))
                    ->phone('+1 (512) 555-0177')
            )
            ->buyer(
                Buyer::make()
                    ->name('Global Ventures Ltd')
                    ->email('ap@globalventures.com')
                    ->address('1 Corporate Plaza, Chicago, IL 60601')
                    ->customFields(['PO Number' => 'PO-2026-555'])
            )
            ->addItem(
                InvoiceItem::make()
                    ->name('UX Design — Phase 1')
                    ->description('Wireframes, user flows, and prototype')
                    ->quantity(1)
                    ->unitPrice(1500.00)
                    ->addDiscount(Discount::make()->name('Salam')->type(DiscountType::Percentage)->value(50))
            )
            ->addItem(
                InvoiceItem::make()
                    ->name('Frontend Development')
                    ->description('React + Tailwind implementation')
                    ->extraDescription('Figma handoff + 2 revision rounds included')
                    ->quantity(3)
                    ->unitPrice(400.00)
            )
            ->addItem(
                InvoiceItem::make()
                    ->name('QA Testing')
                    ->description('Cross-browser + mobile testing')
                    ->quantity(1)
                    ->unitPrice(-300.00)
            )
            ->toSvg();

        file_put_contents($path, $svg);
        $this->assertFileExists($path);

        fwrite(STDOUT, "\n  SVG saved → {$path}\n");
    }
}