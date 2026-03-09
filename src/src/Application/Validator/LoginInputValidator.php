<?php

declare(strict_types=1);

namespace App\Application\Validator;

use App\Domain\Exception\TaskValidationException;
use App\Domain\ValueObject\Email;

final class LoginInputValidator
{
    /**
     * @param array<string, mixed> $input
     *
     * @throws TaskValidationException
     */
    public function validate(array $input): void
    {
        $violations = [];

        $this->validateEmail($input['email'] ?? '', $violations);
        $this->validatePassword($input['password'] ?? '', $violations);

        if (!empty($violations)) {
            throw new TaskValidationException($violations);
        }
    }

    /**
     * @param array<string> $violations
     */
    private function validateEmail(mixed $email, array &$violations): void
    {
        try {
            Email::fromString($email);
        } catch (\InvalidArgumentException $e) {
            $violations[] = $e->getMessage();
        }
    }

    /**
     * @param array<string> $violations
     */
    private function validatePassword(mixed $password, array &$violations): void
    {
        if (!is_string($password) || '' === trim($password)) {
            $violations[] = 'Password cannot be empty.';
        }
    }
}
