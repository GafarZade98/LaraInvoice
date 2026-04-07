<?php

namespace GafarZade98\LaraInvoice\Data;

class InvoiceItem
{
    private string $description = '';
    private string $note = '';
    private string $extraDescription = '';
    private float $quantity = 1;
    private float $unitPrice = 0;
    private float $taxRate = 0;

    public static function make(): static
    {
        return new static();
    }

    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function extraDescription(string $extraDescription): static
    {
        $this->extraDescription = $extraDescription;
        return $this;
    }

    public function quantity(float $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function unitPrice(float $price): static
    {
        $this->unitPrice = $price;
        return $this;
    }

    public function taxRate(float $taxRate): static
    {
        $this->unitPrice = $taxRate;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getTotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }
}