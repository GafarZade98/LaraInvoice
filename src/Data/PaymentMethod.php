<?php

namespace GafarZade98\LaraInvoice\Data;

class PaymentMethod
{
    private string $type = '';
    private ?string $brand = null;
    private ?string $last4 = null;
    private ?string $label = null;

    public static function make(): static
    {
        return new static();
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function brand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    public function last4(string $last4): static
    {
        $this->last4 = $last4;
        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function getLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        if ($this->brand && $this->last4) {
            return "{$this->brand} •••• {$this->last4}";
        }

        return $this->type;
    }
}