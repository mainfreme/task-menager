<?php

declare(strict_types=1);

namespace App\Application\Validator;

use App\Domain\Exception\TaskValidationException;

final class TaskInputValidator
{
    /**
     * @param array<string, mixed> $input
     *
     * @throws TaskValidationException
     */
    public function validateCreate(array $input): void
    {
        $violations = [];

        $this->validateName($input['name'] ?? '', $violations);
        $this->validateDescription($input['description'] ?? null, $violations);
        $this->validateAssignedUserId($input['assignedUserId'] ?? null, $violations, required: true);

        if (!empty($violations)) {
            throw new TaskValidationException($violations);
        }
    }

    /**
     * @param array<string, mixed> $input
     *
     * @throws TaskValidationException
     */
    public function validateUpdate(array $input): void
    {
        $violations = [];

        $this->validateName($input['name'] ?? '', $violations);
        $this->validateDescription($input['description'] ?? null, $violations);

        if (array_key_exists('assignedUserId', $input) && null !== $input['assignedUserId']) {
            $this->validateAssignedUserId($input['assignedUserId'], $violations, required: false);
        }

        if (!empty($violations)) {
            throw new TaskValidationException($violations);
        }
    }

    /**
     * @param array<string> $violations
     */
    private function validateName(string $name, array &$violations): void
    {
        $trimmed = trim($name);

        if ('' === $trimmed) {
            $violations[] = 'Name cannot be empty.';

            return;
        }

        if (mb_strlen($trimmed) > 255) {
            $violations[] = 'Name cannot exceed 255 characters.';
        }
    }

    /**
     * @param array<string> $violations
     */
    private function validateDescription(?string $description, array &$violations): void
    {
        if (null === $description) {
            return;
        }

        if (mb_strlen($description) > 1000) {
            $violations[] = 'Description cannot exceed 1000 characters.';
        }
    }

    /**
     * @param array<string> $violations
     */
    private function validateAssignedUserId(mixed $userId, array &$violations, bool $required): void
    {
        if (null === $userId) {
            if ($required) {
                $violations[] = 'Assigned user ID is required.';
            }

            return;
        }

        if (!is_int($userId) || $userId <= 0) {
            $violations[] = 'Assigned user ID must be a positive integer.';
        }
    }
}
