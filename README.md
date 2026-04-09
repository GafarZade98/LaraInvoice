# LaraInvoice

A Laravel package for generating professional invoices as SVG and PDF documents.

---

## Requirements

- PHP 8.2+
- Laravel 10 / 11 / 12
- One of the following for PDF conversion: `cairosvg`, `inkscape`, or `rsvg-convert`

---

## Installation

```bash
composer require gafarzade98/invoice
```

Publish the config file:

```bash
php artisan vendor:publish --tag=invoice-config
```

Optionally publish the default Blade template to customise it:

```bash
php artisan vendor:publish --tag=invoice-views
```

---

## Quick Start

```php
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Data\Seller;
use GafarZade98\LaraInvoice\Data\Buyer;
use GafarZade98\LaraInvoice\Data\InvoiceItem;
use GafarZade98\LaraInvoice\Enums\InvoiceStatus;

$invoice = Invoice::make()
    ->number('INV-00042')
    ->status(InvoiceStatus::Pending)
    ->date('2025-01-15')
    ->dueDate('2025-01-30')
    ->symbol('$')
    ->seller(Seller::make()->name('Acme LLC')->email('billing@acme.com'))
    ->buyer(Buyer::make()->name('John Doe')->email('john@example.com'))
    ->addItem(
        InvoiceItem::make()
            ->name('Web Development')
            ->description('Frontend + Backend')
            ->quantity(1)
            ->unitPrice(2500.00)
    );

// Download as PDF
return $invoice->downloadPdf();
```

---

## Invoice Status

The `InvoiceStatus` enum determines the document title and type automatically:

| Status | Document Title |
|--------|---------------|
| `Paid` | Receipt |
| `Pending` | Invoice |
| `Refunded` | Refund |
| `Partial` | Refund |
| `Disputed` | Refund |

```php
use GafarZade98\LaraInvoice\Enums\InvoiceStatus;

Invoice::make()->status(InvoiceStatus::Paid);
Invoice::make()->status(InvoiceStatus::Pending);
Invoice::make()->status(InvoiceStatus::Refunded);
```

You can override the auto-resolved title with `->title('Custom Title')`.

---

## Seller & Buyer

Both `Seller` and `Buyer` accept the same fields:

```php
use GafarZade98\LaraInvoice\Data\Seller;
use GafarZade98\LaraInvoice\Data\Buyer;
use GafarZade98\LaraInvoice\Data\Address;

Seller::make()
    ->name('Acme Inc.')
    ->email('billing@acme.com')
    ->phone('+1 555 000 0000')
    ->address(
        Address::make()
            ->address('123 Main Street')
            ->city('San Francisco')
            ->state('CA')
            ->postcode('94105')
            ->country('United States')
    )
    ->customFields(['VAT No.' => 'US123456789']);

Buyer::make()
    ->name('Jane Smith')
    ->email('jane@example.com')
    ->address('456 Oak Avenue, New York, NY 10001');
```

`address()` accepts either a plain string or an `Address` object.

---

## Invoice Items

```php
use GafarZade98\LaraInvoice\Data\InvoiceItem;

InvoiceItem::make()
    ->name('Consulting')
    ->description('Architecture review')
    ->extraDescription('April 2025')   // small italic line
    ->quantity(5)
    ->unitPrice(150.00);
```

The item total is calculated automatically: `(quantity × unitPrice) − item discounts + item taxes`.

### Per-Item Taxes and Discounts

```php
use GafarZade98\LaraInvoice\Data\Tax;
use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Enums\DiscountType;

InvoiceItem::make()
    ->name('Software License')
    ->quantity(1)
    ->unitPrice(500.00)
    ->addTax(Tax::make()->type('VAT')->rate(20))
    ->addDiscount(
        Discount::make()
            ->name('Beta Discount')
            ->type(DiscountType::Fixed)
            ->value(25.00)
    );
```

---

## Taxes

Add percentage-based or fixed-amount taxes to the invoice:

```php
use GafarZade98\LaraInvoice\Data\Tax;

// Percentage tax
Invoice::make()->addTax(Tax::make()->type('VAT')->rate(20));

// Fixed amount tax
Invoice::make()->addTax(Tax::make()->type('Service Fee')->amount(15.00));

// Multiple taxes
Invoice::make()->taxes([
    Tax::make()->type('VAT')->rate(20),
    Tax::make()->type('Municipal')->rate(2),
]);
```

The tax base is: `subtotal − item discounts − invoice discounts`.

Collapse all taxes into a single summary line:

```php
Invoice::make()->groupTaxesAs('Total Tax (22%)');
```

---

## Discounts

```php
use GafarZade98\LaraInvoice\Data\Discount;
use GafarZade98\LaraInvoice\Enums\DiscountType;

// Percentage discount
Invoice::make()->addDiscount(
    Discount::make()
        ->name('Early Bird')
        ->type(DiscountType::Percentage)
        ->value(10)   // 10%
);

// Fixed discount
Invoice::make()->addDiscount(
    Discount::make()
        ->name('Promo Code')
        ->type(DiscountType::Fixed)
        ->value(50.00)
);

// Collapse all discounts into one line
Invoice::make()->groupDiscountsAs('Total Discount');
```

---

## Currency

```php
// Symbol only
Invoice::make()->symbol('$');

// Currency code only
Invoice::make()->currency('USD');

// Both (symbol takes precedence for display)
Invoice::make()->currency('USD')->symbol('$')->decimals(2);
```

---

## Payment Method

```php
use GafarZade98\LaraInvoice\Data\PaymentMethod;

// Credit card
Invoice::make()->paymentMethod(
    PaymentMethod::make()
        ->type('Credit Card')
        ->brand('Visa')
        ->last4('4242')
    // auto-displays as: "Visa •••• 4242"
);

// Custom label
Invoice::make()->paymentMethod(
    PaymentMethod::make()
        ->type('Bank Transfer')
        ->label('IBAN: AZ00BRES...')
);
```

---

## Appearance

```php
Invoice::make()
    ->logo('/absolute/path/to/logo.png')
    ->brandColor('#4F46E5');
```

Set defaults in `config/invoice.php` to avoid repeating these on every invoice.

---

## Additional Fields

```php
Invoice::make()
    ->notes('Payment is due within 30 days. Thank you for your business.')
    ->customFields([
        'Project' => 'Website Redesign',
        'PO No.'  => 'PO-2025-001',
    ])
    ->paid(500.00)   // amount already paid
    ->due(2000.00);  // remaining amount due
```

---

## Output

### HTTP Response

```php
// Download as SVG
return $invoice->downloadSvg();

// Download as PDF
return $invoice->downloadPdf();

// Stream PDF inline in browser
return $invoice->streamPdf();

// Custom filename
return $invoice->downloadPdf('project-invoice.pdf');
```

Filenames are auto-generated based on status and number:
- `invoice-INV-00042.pdf`
- `receipt-INV-00042.pdf`
- `refund-INV-00042.pdf`

### Save to Storage

```php
$invoice->saveSvg('local', 'invoices/inv-00042.svg');
$invoice->savePdf('s3', 'invoices/inv-00042.pdf');
```

### Raw String

```php
$svg = $invoice->toSvg();  // SVG string
$pdf = $invoice->toPdf();  // PDF binary
```

---

## Custom Templates

Extend `AbstractTemplate` to use your own Blade view:

```php
use GafarZade98\LaraInvoice\Templates\AbstractTemplate;

class MinimalTemplate extends AbstractTemplate
{
    protected function view(): string
    {
        return 'my-app::minimal-invoice';
    }
}
```

Register the view in your service provider:

```php
$this->loadViewsFrom(__DIR__ . '/../resources/views', 'my-app');
```

Use it on an invoice:

```php
Invoice::make()->template(new MinimalTemplate())->...
```

Or set it as the default in `config/invoice.php`:

```php
'template' => MinimalTemplate::class,
```

### Custom Layout Data

Override `layout()` to pass additional data to your view:

```php
class MinimalTemplate extends AbstractTemplate
{
    protected function view(): string
    {
        return 'my-app::minimal-invoice';
    }

    protected function layout(Invoice $invoice): array
    {
        return array_merge(parent::layout($invoice), [
            'footer_text' => 'Thank you!',
        ]);
    }
}
```

### Implementing from Scratch

For full control, implement `TemplateInterface` directly:

```php
use GafarZade98\LaraInvoice\Contracts\TemplateInterface;
use GafarZade98\LaraInvoice\Invoice;

class HtmlTemplate implements TemplateInterface
{
    public function render(Invoice $invoice): string
    {
        return '<html>...your own renderer...</html>';
    }
}
```

---

## Configuration

`config/invoice.php`:

```php
return [
    // Default template class
    'template'    => \GafarZade98\LaraInvoice\Templates\DefaultTemplate::class,

    // Brand color for the header bar (hex)
    'brand_color' => env('INVOICE_BRAND_COLOR', '#008080'),

    // Absolute path to a default logo file
    'logo'        => null,

    // Carbon date format
    'date_format' => 'F j, Y',

    // Default seller details (loaded from .env)
    'seller' => [
        'name'    => env('INVOICE_SELLER_NAME', ''),
        'email'   => env('INVOICE_SELLER_EMAIL', ''),
        'phone'   => env('INVOICE_SELLER_PHONE', ''),
        'vat'     => env('INVOICE_SELLER_VAT', ''),
        'address' => [
            'address'  => env('INVOICE_SELLER_ADDRESS', ''),
            'city'     => env('INVOICE_SELLER_CITY', ''),
            'state'    => env('INVOICE_SELLER_STATE', ''),
            'country'  => env('INVOICE_SELLER_COUNTRY', ''),
            'postcode' => env('INVOICE_SELLER_POSTCODE', ''),
        ],
    ],
];
```

---

## Full Example

```php
use GafarZade98\LaraInvoice\Invoice;
use GafarZade98\LaraInvoice\Data\{Seller, Buyer, Address, InvoiceItem, Tax, Discount, PaymentMethod};
use GafarZade98\LaraInvoice\Enums\{InvoiceStatus, DiscountType};

return Invoice::make()
    ->number('INV-00100')
    ->status(InvoiceStatus::Pending)
    ->date('2025-04-01')
    ->dueDate('2025-04-30')
    ->symbol('$')
    ->brandColor('#4F46E5')
    ->logo('/path/to/logo.png')
    ->seller(
        Seller::make()
            ->name('Acme Inc.')
            ->email('billing@acme.com')
            ->address(
                Address::make()
                    ->address('123 Main Street')
                    ->city('San Francisco')
                    ->state('CA')
                    ->postcode('94105')
                    ->country('United States')
            )
    )
    ->buyer(
        Buyer::make()
            ->name('Jane Smith')
            ->email('jane@example.com')
            ->address('456 Oak Ave, New York, NY 10001')
    )
    ->addItem(
        InvoiceItem::make()
            ->name('Web Development')
            ->description('Full-stack implementation')
            ->quantity(10)
            ->unitPrice(200.00)
    )
    ->addItem(
        InvoiceItem::make()
            ->name('Hosting (Annual)')
            ->quantity(1)
            ->unitPrice(120.00)
    )
    ->addDiscount(
        Discount::make()
            ->name('Loyalty Discount')
            ->type(DiscountType::Percentage)
            ->value(5)
    )
    ->addTax(Tax::make()->type('VAT')->rate(20))
    ->paymentMethod(
        PaymentMethod::make()->type('Credit Card')->brand('Visa')->last4('4242')
    )
    ->notes('Payment is due within 30 days.')
    ->downloadPdf();
```

---

## License

MIT — [Gafar Zade](mailto:qafarzade98@gmail.com)