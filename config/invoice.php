<?php

use GafarZade98\LaraInvoice\Templates\DefaultTemplate;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    | The template class used to render the SVG. Must implement TemplateInterface.
    | Override at runtime: Invoice::make()->template(new MyTemplate())
    */
    'template' => DefaultTemplate::class,

    /*
    |--------------------------------------------------------------------------
    | Brand Color
    |--------------------------------------------------------------------------
    | Hex color used for the header bar. Override: ->brandColor('#ff6600')
    */
    'brand_color' => env('INVOICE_BRAND_COLOR', '#008080'),

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    | Absolute path to the logo file. Embedded as base64 in the SVG.
    | Override: ->logo(public_path('logo.png'))
    */
    'logo' => null,

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    */
    'date_format' => 'F j, Y',

    /*
    |--------------------------------------------------------------------------
    | Serial Number
    |--------------------------------------------------------------------------
    | Default series prefix for auto-generated invoice numbers.
    | Pattern: {SERIES}-{SEQUENCE}  e.g. INV-00001
    */
    'serial_series'    => 'D4188AD9',
    'serial_sequence'  => 1,
    'serial_pad'       => 5,    // zero-pad sequence to this length

    /*
    |--------------------------------------------------------------------------
    | Default Seller
    |--------------------------------------------------------------------------
    */
    'seller' => [
        'name'  => env('INVOICE_SELLER_NAME', ''),
        'email' => env('INVOICE_SELLER_EMAIL', ''),
        'phone' => env('INVOICE_SELLER_PHONE', ''),
        'vat'   => env('INVOICE_SELLER_VAT', ''),

        'address' => [
            'address'  => env('INVOICE_SELLER_ADDRESS', ''),
            'city'     => env('INVOICE_SELLER_CITY', ''),
            'state'    => env('INVOICE_SELLER_STATE', ''),
            'country'  => env('INVOICE_SELLER_COUNTRY', ''),
            'postcode' => env('INVOICE_SELLER_POSTCODE', ''),
        ],
    ],
];