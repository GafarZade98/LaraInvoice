<?php

namespace GafarZade98\LaraInvoice\Data;

class InvoiceItem
{
    private string $name = '';
    private string $description = '';
    private string $extraDescription = '';
    private float $quantity = 1;
    private float $unitPrice = 0;
    private array $taxes = [];
    private array $discounts = [];
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

    public function addTax(Tax $tax): static
    {
        $this->taxes[] = $tax;
        return $this;
    }

    public function taxes(array $taxes): static
    {
        $this->taxes = $taxes;
        return $this;
    }

    public function addDiscount(Discount $discount): static
    {
        $this->discounts[] = $discount;
        return $this;
    }

    public function discounts(array $discounts): static
    {
        $this->discounts = $discounts;
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

    /** @return Tax[] */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    /** @return Discount[] */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    public function getTotal(): float
    {
        if ($this->total !== null) {
            return $this->total;
        }

        $lineTotal      = $this->quantity * $this->unitPrice;
        $discountedBase = $lineTotal;

        foreach ($this->discounts as $discount) {
            $discountedBase -= $discount->isPercentage()
                ? $lineTotal * ($discount->getRate() / 100)
                : $discount->getAmount();
        }

        $taxTotal = 0.0;
        foreach ($this->taxes as $tax) {
            $taxTotal += $tax->getRate() > 0
                ? $discountedBase * ($tax->getRate() / 100)
                : $tax->getAmount();
        }

        return $discountedBase + $taxTotal;
    }
}