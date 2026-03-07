<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UserNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('User with id "%d" not found.', $id));
    }
}
