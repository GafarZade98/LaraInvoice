<?php

namespace GafarZade98\LaraInvoice\Data;

class Buyer
{
    private string $name = '';
    private string $email = '';
    private string $taxNumber = '';

    private string $country = '';
    private string $postalCode = '';
    private string $addressLine = '';

    private array $customFields = [];

    public static function make(): static
    {
        return new static();
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function email(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function country(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function postalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function addressLine(string $addressLine): static
    {
        $this->addressLine = $addressLine;
        return $this;
    }

    public function taxNumber(string $taxNumber): static
    {
        $this->taxNumber = $taxNumber;
        return $this;
    }

    public function customFields(array $customFields): static
    {
        $this->customFields = $customFields;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getAddressLine(): string
    {
        return $this->addressLine;
    }

    public function getTaxNumber(): string
    {
        return $this->taxNumber;
    }

    public function getCustomField($key): string
    {
        return $this->customFields[$key] ?? '';
    }
}