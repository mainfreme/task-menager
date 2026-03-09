<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class TaskValidationException extends \DomainException
{
    /** @var string[] */
    private array $violations;

    /** @param string[] $violations */
    public function __construct(array $violations)
    {
        $this->violations = $violations;
        parent::__construct(implode(' ', $violations));
    }

    /** @return string[] */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
