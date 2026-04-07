<?php

namespace GafarZade98\LaraInvoice\Data;

class Seller
{
    private string $name = '';
    private string $email = '';
    private string $phone = '';
    private string $taxNumber = '';

    private string $country = '';
    private string $city = '';
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

    public function city(string $city): static
    {
        $this->city = $city;
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

    public function phone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function taxNumber(string $taxNumber): static
    {
        $this->taxNumber = $taxNumber;
        return $this;
    }

    public function customFields(string ...$customFields): static
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

    public function getPhone(): string
    {
        return $this->phone;
    }
    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
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