<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Username;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UsernameTest extends TestCase
{
    #[Test]
    #[DataProvider('validUsernameProvider')]
    public function createsValidUsername(string $validUsername): void
    {
        $username = Username::fromString($validUsername);

        $this->assertEquals($validUsername, $username->getValue());
    }

    public static function validUsernameProvider(): array
    {
        return [
            'minimum length' => ['abc'],
            'typical username' => ['john_doe'],
            'with numbers' => ['user123'],
            'long username' => ['very_long_username_with_many_characters'],
        ];
    }

    #[Test]
    #[DataProvider('invalidUsernameProvider')]
    public function rejectsInvalidUsernames(string $invalidUsername, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Username::fromString($invalidUsername);
    }

    public static function invalidUsernameProvider(): array
    {
        return [
            'empty string' => ['', 'Nazwa użytkownika nie może być pusta'],
            'one character' => ['a', 'Nazwa użytkownika musi mieć co najmniej 3 znaki'],
            'two characters' => ['ab', 'Nazwa użytkownika musi mieć co najmniej 3 znaki'],
        ];
    }

    #[Test]
    public function twoUsernamesWithSameValueAreEqual(): void
    {
        $username1 = Username::fromString('johndoe');
        $username2 = Username::fromString('johndoe');

        $this->assertTrue($username1->equals($username2));
    }

    #[Test]
    public function twoUsernamesWithDifferentValuesAreNotEqual(): void
    {
        $username1 = Username::fromString('johndoe');
        $username2 = Username::fromString('janedoe');

        $this->assertFalse($username1->equals($username2));
    }
}
