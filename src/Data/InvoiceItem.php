<?php

namespace GafarZade98\LaraInvoice\Data;

class InvoiceItem
{
    private string $name = '';
    private string $description = '';
    private string $extraDescription = '';
    private float $quantity = 1;
    private float $unitPrice = 0;
    private ?Tax $tax = null;
    private ?Discount $discount = null;
    private ?float $total = null;

    public static function make(): static
    {
        return new static();
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
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

    public function tax(Tax $tax): static
    {
        $this->tax = $tax;
        return $this;
    }

    public function discount(Discount $discount): static
    {
        $this->discount = $discount;
        return $this;
    }

    public function total(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getExtraDescription(): string
    {
        return $this->extraDescription;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getTax(): ?Tax
    {
        return $this->tax;
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function getTotal(): float
    {
        if ($this->total !== null) {
            return $this->total;
        }

        $base = $this->quantity * $this->unitPrice;

        if ($this->discount) {
            $base -= $this->discount->getAmount();
        }

        if ($this->tax) {
            $base += $this->tax->getAmount();
        }

        return $base;
    }
}