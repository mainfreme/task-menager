<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class AuthTokenDto
{
    public function __construct(
        public readonly string $token,
        public readonly string $refreshToken,
        public readonly \DateTimeImmutable $expiresAt,
    ) {
    }
}
