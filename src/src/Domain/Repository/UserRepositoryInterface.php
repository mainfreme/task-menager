<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\User\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;

interface UserRepositoryInterface
{
    /**
     * @return array<User>
     */
    public function findAll(): array;

    public function findById(int $id): ?User;

    public function findByUsername(Username $username): ?User;

    public function findByEmail(Email $email): ?User;

    public function save(User $user): void;

    public function existUserByEmail(Email $email): bool;

    public function flush(): void;
}
