<?php

namespace GafarZade98\LaraInvoice\Enums;

enum InvoiceStatus: string
{
    case Paid     = 'paid';
    case Pending  = 'pending';
    case Refunded = 'refunded';
    case PartialRefund = 'partial_refund';
    case Disputed = 'disputed';

    public function isRefunded(): bool
    {
        return match ($this) {
            self::Refunded, self::PartialRefund, self::Disputed => true,
            default                                       => false,
        };
    }
}