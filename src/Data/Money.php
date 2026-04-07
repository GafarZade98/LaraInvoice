<?php

namespace GafarZade98\LaraInvoice\Data;

class Money
{
    private string $currency = 'USD';
    private string $symbol = '$';
    private int $decimals = 2;
    private float $subtotal = 0;
    private float $total = 0;
    private float $paid = 0;
    private float $due = 0;

    public static function make(): static
    {
        return new static();
    }

    public function currency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function symbol(string $symbol): static
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function subtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function total(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function paid(float $paid): static
    {
        $this->paid = $paid;
        return $this;
    }

    public function due(float $due): static
    {
        $this->due = $due;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getPaid(): float
    {
        return $this->paid;
    }

    public function getDue(): float
    {
        return $this->due;
    }


    public function format(float $amount): string
    {
        $negative = $amount < 0;
        $formatted = number_format(abs($amount), $this->decimals, '.', ',');

        return ($negative ? '-' : '') . $this->symbol . $formatted;
    }
}