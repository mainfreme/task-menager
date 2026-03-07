<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Company;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Phone;
use App\Domain\ValueObject\Username;
use App\Domain\ValueObject\Website;

final class User
{
    private ?int $id;

    private string $name;

    private Username $username;

    private Email $email;

    private ?Address $address;

    private ?Phone $phone;

    private ?Website $website;

    private ?Company $company;

    private string $passwordHash;

    private ?\DateTimeImmutable $createdAt = null;

    private function __construct(
        ?int $id,
        string $name,
        Username $username,
        Email $email,
        ?Address $address,
        ?Phone $phone,
        ?Website $website,
        ?Company $company,
        string $passwordHash,
        ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->address = $address;
        $this->phone = $phone;
        $this->website = $website;
        $this->company = $company;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $name,
        Username $username,
        Email $email,
        ?Address $address,
        ?Phone $phone,
        ?Website $website,
        ?Company $company,
        string $passwordHash,
    ): self {
        return new self(
            id: null,
            name: $name,
            username: $username,
            email: $email,
            address: $address,
            phone: $phone,
            website: $website,
            company: $company,
            passwordHash: $passwordHash,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        Username $username,
        Email $email,
        ?Address $address,
        ?Phone $phone,
        ?Website $website,
        ?Company $company,
        string $passwordHash,
        ?\DateTimeImmutable $createdAt = null,
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
            passwordHash: $passwordHash,
            createdAt: $createdAt,
        );
    }

    public function getId(): ?int
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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array{id: ?int, name: string, username: Username, email: Email, address: ?Address, phone: ?Phone, website: ?Website, company: ?Company, passwordHash: string}
     */
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
            'passwordHash' => $this->passwordHash,
        ];
    }
}
