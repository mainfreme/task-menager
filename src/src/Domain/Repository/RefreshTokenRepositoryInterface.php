<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Infrastructure\Persistence\Doctrine\Entity\RefreshTokenEntity;

interface RefreshTokenRepositoryInterface
{
    public function findByToken(string $token): ?RefreshTokenEntity;

    public function save(RefreshTokenEntity $refreshToken): void;

    public function revokeAllForUser(int $userId): void;

    public function flush(): void;
}
