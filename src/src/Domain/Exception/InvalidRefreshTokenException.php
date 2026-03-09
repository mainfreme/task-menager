<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRefreshTokenException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Refresh token is invalid or expired.');
    }
}
