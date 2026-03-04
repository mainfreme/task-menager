<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use DateTimeImmutable;
use App\Domain\ValueObject\Username;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Phone;
use App\Domain\ValueObject\Website;
use App\Domain\ValueObject\Company;

final class User
{
    private function __construct(
        private int $id,
        private string $name,
        private Username $username,
        private Email $email,
        private Address $address,
        private Phone $phone,
        private Website $website,
        private Company $company,
        private ?DateTimeImmutable $createdAt = null,
    ) {
    }

    public static function create(
        int $id,
        string $name,
        Username $username,
        Email $email,
        Address $address,
        Phone $phone,
        Website $website,
        Company $company,
    ): self {
        return new self(
            id: $id,
            name: $name,
            username: $username,
            email: $email,
            address: $address,
            phone: $phone,
            website: $website,
            company: $company,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function fromJsonPlaceholder(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            address: $data['address'],
            phone: $data['phone'],
            website: $data['website'],
            company: $data['company'],
            createdAt: new DateTimeImmutable(),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }


    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'website' => $this->website,
            'company' => $this->company,
        ];
    }
}
