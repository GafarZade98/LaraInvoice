<?php

namespace GafarZade98\LaraInvoice\Enums;

enum DiscountType: string
{
    case Fixed      = 'fixed';
    case Percentage = 'percentage';
}