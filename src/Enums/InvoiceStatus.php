<?php

namespace GafarZade98\LaraInvoice\Enums;

enum InvoiceStatus: string
{
    case Paid     = 'paid';
    case Pending  = 'pending';
    case Refunded = 'refunded';
    case Partial  = 'partial';
    case Disputed = 'disputed';

    public function isRefunded(): bool
    {
        return match ($this) {
            self::Refunded, self::Partial, self::Disputed => true,
            default                                       => false,
        };
    }
}