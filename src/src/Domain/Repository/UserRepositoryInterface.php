<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\User\User;

interface UserRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;
}
