<?php

namespace GafarZade98\LaraInvoice\Data;

class Buyer
{
    private string   $name    = '';
    private string   $email   = '';
    private string   $phone   = '';
    private ?Address $address = null;
    private array    $customFields = [];

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

    public function phone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    /** Accept a full Address object or a plain string (treated as the street line). */
    public function address(string|Address $address): static
    {
        $this->address = $address instanceof Address
            ? $address
            : Address::make()->address($address);

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

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getCustomField(string $key): ?string
    {
        return $this->customFields[$key] ?? null;
    }
}