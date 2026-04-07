<?php

namespace GafarZade98\LaraInvoice\Data;

class Seller
{
    private string $name = '';
    private string $email = '';
    private string $address = '';
    private string $phone = '';
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

    public function address(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function phone(string $phone): static
    {
        $this->phone = $phone;
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

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhone(): string
    {
        return $this->phone;
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