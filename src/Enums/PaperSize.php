<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Enums;

enum PaperSize: string
{
    case A4     = 'A4';
    case Letter = 'Letter';
    case Legal  = 'Legal';

    public function width(): float
    {
        return match ($this) {
            self::A4     => 595.0,
            self::Letter => 612.0,
            self::Legal  => 612.0,
        };
    }

    public function height(): float
    {
        return match ($this) {
            self::A4     => 842.0,
            self::Letter => 792.0,
            self::Legal  => 1008.0,
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }

        return self::A4;
    }
}