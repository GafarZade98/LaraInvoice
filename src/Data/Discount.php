<?php

namespace GafarZade98\LaraInvoice\Data;

use GafarZade98\LaraInvoice\Enums\DiscountType;

class Discount
{
    private string $name = 'Discount';
    private DiscountType $type = DiscountType::Fixed;
    private float $value = 0;

    public static function make(): static
    {
        return new static();
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function type(DiscountType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function value(float $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): DiscountType
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->value;
    }

    public function getRate(): float
    {
        return $this->value;
    }

    public function isPercentage(): bool
    {
        return $this->type === DiscountType::Percentage;
    }
}