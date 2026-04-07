<?php

namespace GafarZade98\LaraInvoice\Data;

class Tax
{
    private float $rate = 0;
    private float $amount = 0;
    private string $type = 'VAT';
    private ?string $id = null;

    public static function make(): static
    {
        return new static();
    }

    public function rate(float $rate): static
    {
        $this->rate = $rate;
        return $this;
    }

    public function amount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function id(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}