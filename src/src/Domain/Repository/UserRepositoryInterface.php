<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\User\UserAggregate;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;

interface UserRepositoryInterface
{
    /**
     * @return array<UserAggregate>
     */
    public function findAll(): array;

    public function findById(int $id): ?UserAggregate;

    public function findByUsername(Username $username): ?UserAggregate;

    public function findByEmail(Email $email): ?UserAggregate;

    public function save(UserAggregate $user): void;

    public function existUserByEmail(Email $email): bool;

    public function flush(): void;
}
