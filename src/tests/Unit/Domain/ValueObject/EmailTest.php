<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function creates_valid_email(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->assertEquals('user@example.com', $email->getValue());
        $this->assertEquals('user@example.com', $email->toString());
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function rejects_invalid_emails(string $invalidEmail, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        Email::fromString($invalidEmail);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty string' => ['', 'Email nie może być pusty'],
            'missing @' => ['userexample.com', 'Email nie jest prawidłowy'],
            'missing domain' => ['user@', 'Email nie jest prawidłowy'],
            'missing local part' => ['@example.com', 'Email nie jest prawidłowy'],
            'multiple @' => ['user@@example.com', 'Email nie jest prawidłowy'],
            'spaces in email' => ['user @example.com', 'Email nie jest prawidłowy'],
            'invalid domain' => ['user@.com', 'Email nie jest prawidłowy'],
        ];
    }

    #[Test]
    public function two_emails_with_same_value_are_equal(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    #[Test]
    public function two_emails_with_different_values_are_not_equal(): void
    {
        $email1 = Email::fromString('test1@example.com');
        $email2 = Email::fromString('test2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }

    #[Test]
    public function casts_to_string(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->assertEquals('user@example.com', (string) $email);
    }
}
