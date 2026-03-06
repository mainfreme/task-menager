<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UserCreationException extends \RuntimeException
{
    public function __construct(string $message = 'Failed to create User from API data', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
