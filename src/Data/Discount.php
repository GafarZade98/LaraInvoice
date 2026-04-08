<?php

namespace GafarZade98\LaraInvoice\Data;

class Discount
{
    private string $name = 'Discount';
    private string $type = 'fixed';
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

    public function type(string $type): static
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * For percentage discounts: the rate (e.g. 10 = 10%).
     * For fixed discounts: the currency amount.
     * Both read from $value — use ->value() setter for either case.
     */
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
        return $this->type === 'percentage';
    }
}