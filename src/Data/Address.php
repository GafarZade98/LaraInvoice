<?php

namespace GafarZade98\LaraInvoice\Data;

class Address
{
    private ?string $address  = null;   // street / line 1
    private ?string $city     = null;
    private ?string $state    = null;   // state / county / province
    private ?string $country  = null;
    private ?string $postcode = null;   // zip / postal code

    public static function make(): static
    {
        return new static();
    }

    public function address(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function city(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function state(string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function country(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function postcode(string $postcode): static
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * Returns non-empty rendered lines ready for display in a template.
     *
     * Example output:
     *   ['123 Tech Street', 'San Francisco, CA 94105', 'United States']
     */
    public function toLines(): array
    {
        $lines = [];

        if ($this->address) {
            $lines[] = $this->address;
        }

        $cityLine = implode(', ', array_filter([
            $this->city,
            $this->state,
            $this->postcode,
        ]));

        if ($cityLine) {
            $lines[] = $cityLine;
        }

        if ($this->country) {
            $lines[] = $this->country;
        }

        return $lines;
    }
}