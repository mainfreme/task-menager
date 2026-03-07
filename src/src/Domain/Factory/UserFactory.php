<?php

declare(strict_types=1);

namespace App\Domain\Factory;

use App\Domain\Exception\UserCreationException;
use App\Domain\Model\User\User;
use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Company;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Phone;
use App\Domain\ValueObject\Username;
use App\Domain\ValueObject\Website;

final class UserFactory implements UserFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws UserCreationException
     */
    public function createFromApiData(array $data): User
    {
        try {
            $this->validateRequiredFields($data);

            $username = Username::fromString($data['username']);
            $email = Email::fromString($data['email']);
            $phone = $this->createPhone($data['phone'] ?? null);
            $website = $this->createWebsite($data['website'] ?? null);
            $address = $this->createAddress($data['address'] ?? null);
            $company = $this->createCompany($data['company'] ?? null);
            $passwordHash = $this->hashImportPassword();

            return User::create(
                name: $data['name'],
                username: $username,
                email: $email,
                address: $address,
                phone: $phone,
                website: $website,
                company: $company,
                passwordHash: $passwordHash,
            );
        } catch (\Throwable $e) {
            throw new UserCreationException(
                sprintf('Failed to create User from API data: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws UserCreationException
     */
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = ['name', 'username', 'email'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new UserCreationException(
                    sprintf('Required field "%s" is missing', $field)
                );
            }
        }

    }

    private function createPhone(mixed $value): ?Phone
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        return Phone::fromString($value);
    }

    private function createWebsite(mixed $value): ?Website
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        return Website::fromString($value);
    }

    private function createAddress(mixed $value): ?Address
    {
        if (!is_array($value)) {
            return null;
        }

        $requiredFields = ['street', 'suite', 'city', 'zipcode', 'geo'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $value) || null === $value[$field] || '' === $value[$field]) {
                return null;
            }
        }

        if (!is_array($value['geo'])
            || !array_key_exists('lat', $value['geo'])
            || !array_key_exists('lng', $value['geo'])
            || null === $value['geo']['lat']
            || null === $value['geo']['lng']
            || '' === $value['geo']['lat']
            || '' === $value['geo']['lng']
        ) {
            return null;
        }

        /* @var array{street: string, suite: string, city: string, zipcode: string, geo: array{lat: string, lng: string}} $value */
        return Address::fromArray($value);
    }

    private function createCompany(mixed $value): ?Company
    {
        if (!is_array($value)) {
            return null;
        }

        $requiredFields = ['name', 'catchPhrase', 'bs'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $value) || null === $value[$field] || '' === $value[$field]) {
                return null;
            }
        }

        /* @var array{name: string, catchPhrase: string, bs: string} $value */
        return Company::fromArray($value);
    }

    private function hashImportPassword(): string
    {
        return password_hash('secret123', PASSWORD_DEFAULT);
    }
}
