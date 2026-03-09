<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Validator;

use App\Application\Validator\TaskInputValidator;
use App\Domain\Exception\TaskValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskInputValidatorTest extends TestCase
{
    private TaskInputValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TaskInputValidator();
    }

    // ------------------------------------------------------------------
    // validateCreate – happy path
    // ------------------------------------------------------------------

    #[Test]
    public function createPassesWithValidInput(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateCreate([
            'name' => 'Valid task',
            'description' => 'Some description',
            'assignedUserId' => 1,
        ]);
    }

    #[Test]
    public function createPassesWithNullDescription(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateCreate([
            'name' => 'Valid task',
            'assignedUserId' => 5,
        ]);
    }

    // ------------------------------------------------------------------
    // validateCreate – name violations
    // ------------------------------------------------------------------

    #[Test]
    public function createFailsWithEmptyName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $this->validator->validateCreate([
            'name' => '',
            'assignedUserId' => 1,
        ]);
    }

    #[Test]
    public function createFailsWithWhitespaceName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $this->validator->validateCreate([
            'name' => '   ',
            'assignedUserId' => 1,
        ]);
    }

    #[Test]
    public function createFailsWithTooLongName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot exceed 255 characters.');

        $this->validator->validateCreate([
            'name' => str_repeat('a', 256),
            'assignedUserId' => 1,
        ]);
    }

    // ------------------------------------------------------------------
    // validateCreate – description violations
    // ------------------------------------------------------------------

    #[Test]
    public function createFailsWithTooLongDescription(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Description cannot exceed 1000 characters.');

        $this->validator->validateCreate([
            'name' => 'Valid',
            'description' => str_repeat('x', 1001),
            'assignedUserId' => 1,
        ]);
    }

    // ------------------------------------------------------------------
    // validateCreate – assignedUserId violations
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('invalidUserIdProvider')]
    public function createFailsWithInvalidAssignedUserId(mixed $userId): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Assigned user ID must be a positive integer.');

        $this->validator->validateCreate([
            'name' => 'Valid',
            'assignedUserId' => $userId,
        ]);
    }

    #[Test]
    public function createFailsWithMissingAssignedUserId(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Assigned user ID is required.');

        $this->validator->validateCreate([
            'name' => 'Valid',
        ]);
    }

    // ------------------------------------------------------------------
    // validateCreate – multiple violations at once
    // ------------------------------------------------------------------

    #[Test]
    public function createCollectsMultipleViolations(): void
    {
        try {
            $this->validator->validateCreate([
                'name' => '',
                'description' => str_repeat('x', 1001),
                'assignedUserId' => -1,
            ]);
            $this->fail('Expected TaskValidationException');
        } catch (TaskValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(3, $violations);
            $this->assertSame('Name cannot be empty.', $violations[0]);
            $this->assertSame('Description cannot exceed 1000 characters.', $violations[1]);
            $this->assertSame('Assigned user ID must be a positive integer.', $violations[2]);
        }
    }

    // ------------------------------------------------------------------
    // validateUpdate – happy path
    // ------------------------------------------------------------------

    #[Test]
    public function updatePassesWithValidInput(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Updated name',
            'description' => 'Updated desc',
        ]);
    }

    #[Test]
    public function updatePassesWithOptionalAssignedUserId(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Updated name',
            'assignedUserId' => 3,
        ]);
    }

    #[Test]
    public function updatePassesWithNameAtMaxLength(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => str_repeat('a', 255),
        ]);
    }

    #[Test]
    public function updatePassesWithDescriptionAtMaxLength(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'description' => str_repeat('x', 1000),
        ]);
    }

    #[Test]
    public function updatePassesWithEmptyStringDescription(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'description' => '',
        ]);
    }

    #[Test]
    public function updatePassesWithoutAssignedUserIdKey(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'description' => 'Desc',
        ]);
    }

    // ------------------------------------------------------------------
    // validateUpdate – name violations
    // ------------------------------------------------------------------

    #[Test]
    public function updateFailsWithEmptyName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $this->validator->validateUpdate([
            'name' => '',
        ]);
    }

    #[Test]
    public function updateFailsWithWhitespaceName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $this->validator->validateUpdate([
            'name' => '   ',
        ]);
    }

    #[Test]
    public function updateFailsWithTooLongName(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Name cannot exceed 255 characters.');

        $this->validator->validateUpdate([
            'name' => str_repeat('a', 256),
        ]);
    }

    // ------------------------------------------------------------------
    // validateUpdate – description violations
    // ------------------------------------------------------------------

    #[Test]
    public function updateFailsWithTooLongDescription(): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Description cannot exceed 1000 characters.');

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'description' => str_repeat('x', 1001),
        ]);
    }

    // ------------------------------------------------------------------
    // validateUpdate – assignedUserId violations
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('invalidUserIdProvider')]
    public function updateFailsWithInvalidAssignedUserId(mixed $userId): void
    {
        $this->expectException(TaskValidationException::class);
        $this->expectExceptionMessage('Assigned user ID must be a positive integer.');

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'assignedUserId' => $userId,
        ]);
    }

    #[Test]
    public function updateIgnoresNullAssignedUserId(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validateUpdate([
            'name' => 'Valid',
            'assignedUserId' => null,
        ]);
    }

    // ------------------------------------------------------------------
    // validateUpdate – multiple violations at once
    // ------------------------------------------------------------------

    #[Test]
    public function updateCollectsMultipleViolations(): void
    {
        try {
            $this->validator->validateUpdate([
                'name' => '',
                'description' => str_repeat('x', 1001),
                'assignedUserId' => -1,
            ]);
            $this->fail('Expected TaskValidationException');
        } catch (TaskValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(3, $violations);
            $this->assertSame('Name cannot be empty.', $violations[0]);
            $this->assertSame('Description cannot exceed 1000 characters.', $violations[1]);
            $this->assertSame('Assigned user ID must be a positive integer.', $violations[2]);
        }
    }

    // ------------------------------------------------------------------
    // Data providers
    // ------------------------------------------------------------------

    /** @return array<string, array{mixed}> */
    public static function invalidUserIdProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'large negative' => [-100],
        ];
    }
}
