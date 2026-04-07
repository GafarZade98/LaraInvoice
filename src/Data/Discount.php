<?php

namespace GafarZade98\LaraInvoice\Data;

class Discount
{
    private string $name = 'Discount';
    private string $type = 'percentage';
    private float $value = 0;
    private float $amount = 0;

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

    public function amount(float $amount): static
    {
        $this->amount = $amount;
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }
}